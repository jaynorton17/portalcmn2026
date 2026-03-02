#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

function parseArgs(argv) {
  const args = { baseUrl: 'https://covermenow.co.uk/covermenow-one/', maxLinks: 80 };
  for (let i = 2; i < argv.length; i += 1) {
    const a = argv[i];
    if (a === '--baseUrl' && argv[i + 1]) {
      args.baseUrl = argv[++i];
    } else if (a === '--maxLinks' && argv[i + 1]) {
      args.maxLinks = Number(argv[++i]) || args.maxLinks;
    }
  }
  return args;
}

function normalizeUrl(baseUrl, href) {
  if (!href) return null;
  const trimmed = String(href).trim();
  if (!trimmed || trimmed.startsWith('#') || trimmed.startsWith('javascript:') || trimmed.startsWith('mailto:') || trimmed.startsWith('tel:')) {
    return null;
  }
  try {
    return new URL(trimmed, baseUrl).toString();
  } catch (err) {
    return null;
  }
}

function isInternal(baseUrl, candidate) {
  try {
    const b = new URL(baseUrl);
    const c = new URL(candidate);
    return b.origin === c.origin;
  } catch (err) {
    return false;
  }
}

function ts() {
  return new Date().toISOString();
}

async function findFirstVisible(page, selectors) {
  for (const selector of selectors) {
    const loc = page.locator(selector).first();
    if (await loc.count()) {
      try {
        if (await loc.isVisible({ timeout: 1000 })) return loc;
      } catch (err) {
        // continue
      }
    }
  }
  return null;
}

async function loginIfConfigured(page, baseUrl, role, capture) {
  const email = process.env[role.emailEnv];
  const pass = process.env[role.passEnv];
  if (!email || !pass) {
    return { ok: false, skipped: true, reason: 'missing_credentials' };
  }

  const start = Date.now();
  await page.goto(baseUrl, { waitUntil: 'domcontentloaded', timeout: 45000 });

  const emailInput = await findFirstVisible(page, [
    'input[name="log"]',
    'input[name="email"]',
    'input[type="email"]',
    'input[name="username"]',
    '#user_login',
  ]);
  const passInput = await findFirstVisible(page, [
    'input[name="pwd"]',
    'input[name="password"]',
    'input[type="password"]',
    '#user_pass',
  ]);
  const submit = await findFirstVisible(page, [
    'button[type="submit"]',
    'input[type="submit"]',
    'button:has-text("Log in")',
    'button:has-text("Sign in")',
    'button:has-text("Login")',
  ]);

  if (!emailInput || !passInput || !submit) {
    return { ok: false, skipped: false, reason: 'login_form_not_found' };
  }

  await emailInput.fill(email);
  await passInput.fill(pass);
  await Promise.all([
    page.waitForLoadState('networkidle', { timeout: 45000 }).catch(() => null),
    submit.click({ timeout: 10000 }),
  ]);

  const elapsed = Date.now() - start;
  const url = page.url();
  const hasPassword = await page.locator('input[type="password"]').count();
  const looksLoggedIn = !/wp-login\.php|lostpassword/i.test(url) && hasPassword === 0;

  capture.loginTimings.push({
    role: role.name,
    started_at: ts(),
    duration_ms: elapsed,
    final_url: url,
    success: looksLoggedIn,
  });

  return { ok: looksLoggedIn, skipped: false, reason: looksLoggedIn ? 'ok' : 'login_failed', durationMs: elapsed, finalUrl: url };
}

async function collectNavLinks(page, baseUrl) {
  const links = await page.evaluate(() => {
    const preferredSelectors = [
      '.cmn-sidebar a[href]',
      '.cmn-topbar a[href]',
      'aside a[href]',
      'nav a[href]',
      '.cmn-dashboard-card a[href]',
    ];
    const found = [];
    const pushNode = (node) => {
      const href = node.getAttribute('href') || '';
      const text = (node.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 120);
      found.push({ href, text });
    };

    for (const sel of preferredSelectors) {
      document.querySelectorAll(sel).forEach(pushNode);
    }
    if (!found.length) {
      document.querySelectorAll('a[href]').forEach(pushNode);
    }
    return found;
  });

  const seen = new Set();
  const out = [];
  for (const link of links) {
    const target = normalizeUrl(baseUrl, link.href);
    if (!target || !isInternal(baseUrl, target)) continue;
    if (seen.has(target)) continue;
    seen.add(target);
    out.push({ url: target, text: link.text || '(no text)' });
  }
  return out;
}

async function collectSafeButtons(page) {
  return page.evaluate(() => {
    const nodes = Array.from(document.querySelectorAll('button, input[type="button"], input[type="submit"], a[role="button"], .cmn-btn, .cmn-button'));
    return nodes.map((n) => {
      const text = (n.innerText || n.value || n.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ').slice(0, 120);
      return { text };
    }).filter((r) => r.text);
  });
}

function buttonTextIsSafe(text) {
  if (!text) return false;
  const t = text.toLowerCase();
  const deny = ['save', 'submit', 'book now', 'confirm', 'delete', 'remove', 'void', 'approve', 'decline', 'accept', 'send offer', 'pay'];
  if (deny.some((d) => t.includes(d))) return false;
  const allow = ['view', 'open', 'profile', 'support', 'help', 'chat', 'calendar', 'module', 'course', 'learn', 'next', 'back', 'close', 'dashboard', 'explore'];
  return allow.some((a) => t.includes(a));
}

function summarizeFailures(items) {
  return items.filter((i) => i.result === 'FAIL').length;
}

async function runRoleAudit(browser, baseUrl, role, maxLinks) {
  const context = await browser.newContext({ ignoreHTTPSErrors: true });
  const page = await context.newPage();

  const capture = {
    role: role.name,
    started_at: ts(),
    login: null,
    loginTimings: [],
    checks: [],
    consoleErrors: [],
    failingRequests: [],
    slowRequests: [],
  };

  page.on('console', (msg) => {
    const text = msg.text();
    if (/Failed to load resource|403|404|500|Uncaught/i.test(text) || msg.type() === 'error') {
      capture.consoleErrors.push({ at: ts(), type: msg.type(), text });
    }
  });

  page.on('pageerror', (err) => {
    capture.consoleErrors.push({ at: ts(), type: 'pageerror', text: String(err) });
  });

  page.on('response', async (resp) => {
    const status = resp.status();
    const url = resp.url();
    const isCritical = status >= 400 && (/\.js(\?|$)|\.css(\?|$)|admin-ajax\.php|covermenow-one|wp-json/i.test(url));
    if (isCritical) {
      capture.failingRequests.push({ at: ts(), url, status });
    }
  });

  capture.login = await loginIfConfigured(page, baseUrl, role, capture);
  if (!capture.login.ok && !capture.login.skipped) {
    capture.checks.push({
      role: role.name,
      type: 'login',
      page: baseUrl,
      action: 'login',
      result: 'FAIL',
      details: capture.login.reason,
    });
    await context.close();
    return capture;
  }

  if (capture.login.skipped) {
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded', timeout: 45000 });
  }

  const links = await collectNavLinks(page, baseUrl);
  const scopedLinks = links.slice(0, Math.max(1, maxLinks));

  for (const link of scopedLinks) {
    const started = Date.now();
    let result = 'PASS';
    let detail = '';
    let finalUrl = link.url;
    let status = 0;
    try {
      const response = await page.goto(link.url, { waitUntil: 'domcontentloaded', timeout: 45000 });
      status = response ? response.status() : 0;
      finalUrl = page.url();
      if (status === 0) {
        // Playwright can return null response for same-document/hash navigations.
        const probe = await context.request.get(link.url, { failOnStatusCode: false, timeout: 30000 });
        status = probe.status();
      }
      if (!(status === 200 || status === 304)) {
        result = 'FAIL';
        detail = `status_${status}`;
      }
      if (!capture.login.skipped && /wp-login\.php/i.test(finalUrl)) {
        result = 'FAIL';
        detail = detail ? `${detail};unexpected_login_redirect` : 'unexpected_login_redirect';
      }
      const bodyText = await page.locator('body').innerText().catch(() => '');
      if (/fatal error|access denied|there has been a critical error/i.test(bodyText)) {
        result = 'FAIL';
        detail = detail ? `${detail};error_banner` : 'error_banner';
      }
    } catch (err) {
      result = 'FAIL';
      detail = `navigate_error:${String(err).slice(0, 160)}`;
    }

    capture.checks.push({
      role: role.name,
      type: 'link',
      page: link.url,
      action: `navigate:${link.text}`,
      target_url: link.url,
      final_url: finalUrl,
      status,
      duration_ms: Date.now() - started,
      result,
      details: detail,
    });
    await page.waitForTimeout(250);
  }

  const buttons = await collectSafeButtons(page);
  let buttonChecks = 0;
  for (const b of buttons) {
    if (!buttonTextIsSafe(b.text)) continue;
    if (buttonChecks >= 25) break;
    buttonChecks += 1;
    const started = Date.now();
    const before = page.url();
    let result = 'PASS';
    let details = 'no_dom_change_detected';
    try {
      const loc = page.locator(`button:has-text("${b.text}"), a[role="button"]:has-text("${b.text}"), .cmn-btn:has-text("${b.text}"), .cmn-button:has-text("${b.text}")`).first();
      if ((await loc.count()) === 0) continue;
      await loc.click({ timeout: 6000 }).catch(() => null);
      await page.waitForTimeout(1000);
      const after = page.url();
      const dialogVisible = await page.locator('[role="dialog"], .modal, .cmn-modal, [aria-modal="true"]').count();
      if (after !== before) details = `url_changed:${before}=>${after}`;
      if (dialogVisible > 0) details = 'opened_modal';
    } catch (err) {
      result = 'FAIL';
      details = `click_error:${String(err).slice(0, 160)}`;
    }
    capture.checks.push({
      role: role.name,
      type: 'cta',
      page: before,
      action: `click:${b.text}`,
      result,
      duration_ms: Date.now() - started,
      details,
    });
  }

  const requests = await context.storageState();
  capture.cookieCount = Array.isArray(requests.cookies) ? requests.cookies.length : 0;
  capture.finished_at = ts();
  capture.failures = {
    checks_failed: summarizeFailures(capture.checks),
    console_errors: capture.consoleErrors.length,
    failing_requests: capture.failingRequests.length,
  };

  await context.close();
  return capture;
}

function toSummaryMarkdown(report) {
  const lines = [];
  lines.push('# Portal Link/Action Audit Summary');
  lines.push('');
  lines.push(`Generated: ${ts()}`);
  lines.push('');
  lines.push('| Role | Login | Link/CTA Checks | Failed Checks | Console Errors | Failing Requests | Overall |');
  lines.push('|---|---:|---:|---:|---:|---:|---|');
  for (const role of report.roles) {
    const totalChecks = role.checks.length;
    const failedChecks = role.checks.filter((c) => c.result === 'FAIL').length;
    const login = role.login && role.login.ok ? 'PASS' : role.login && role.login.skipped ? 'SKIPPED' : 'FAIL';
    const overall = (failedChecks === 0 && role.consoleErrors.length === 0 && role.failingRequests.length === 0) ? 'PASS' : 'FAIL';
    lines.push(`| ${role.role} | ${login} | ${totalChecks} | ${failedChecks} | ${role.consoleErrors.length} | ${role.failingRequests.length} | ${overall} |`);
  }
  lines.push('');
  lines.push('## Top Failures');
  lines.push('');
  for (const role of report.roles) {
    const failed = role.checks.filter((c) => c.result === 'FAIL').slice(0, 10);
    if (!failed.length && !role.failingRequests.length && !role.consoleErrors.length) {
      lines.push(`- ${role.role}: none`);
      continue;
    }
    lines.push(`- ${role.role}:`);
    for (const item of failed) {
      lines.push(`  - [check] ${item.type} ${item.action} -> ${item.details || 'failed'}`);
    }
    for (const req of role.failingRequests.slice(0, 10)) {
      lines.push(`  - [request] ${req.status} ${req.url}`);
    }
    for (const err of role.consoleErrors.slice(0, 10)) {
      lines.push(`  - [console] ${err.type}: ${err.text}`);
    }
  }
  return `${lines.join('\n')}\n`;
}

async function main() {
  const args = parseArgs(process.argv);
  const baseUrl = args.baseUrl;
  const artifactsDir = path.resolve(process.cwd(), 'artifacts');
  fs.mkdirSync(artifactsDir, { recursive: true });

  const roles = [
    { name: 'candidate', emailEnv: 'CMN_AUDIT_CANDIDATE_EMAIL', passEnv: 'CMN_AUDIT_CANDIDATE_PASS' },
    { name: 'school', emailEnv: 'CMN_AUDIT_SCHOOL_EMAIL', passEnv: 'CMN_AUDIT_SCHOOL_PASS' },
    { name: 'admin', emailEnv: 'CMN_AUDIT_ADMIN_EMAIL', passEnv: 'CMN_AUDIT_ADMIN_PASS' },
    { name: 'guest', emailEnv: '', passEnv: '' },
  ];

  const browser = await chromium.launch({ headless: true });
  const report = {
    baseUrl,
    generated_at: ts(),
    roles: [],
  };

  try {
    for (const role of roles) {
      // guest always runs in unauthenticated mode
      if (role.name === 'guest') {
        process.env.CMN_AUDIT_GUEST_EMAIL = '';
        process.env.CMN_AUDIT_GUEST_PASS = '';
      }
      const capture = await runRoleAudit(browser, baseUrl, role, args.maxLinks);
      report.roles.push(capture);
    }
  } finally {
    await browser.close();
  }

  const reportPath = path.join(artifactsDir, 'link-audit-report.json');
  const summaryPath = path.join(artifactsDir, 'link-audit-summary.md');

  fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
  fs.writeFileSync(summaryPath, toSummaryMarkdown(report));

  const totalFailures = report.roles.reduce((acc, role) => {
    const failedChecks = role.checks.filter((c) => c.result === 'FAIL').length;
    return acc + failedChecks + role.consoleErrors.length + role.failingRequests.length;
  }, 0);

  // eslint-disable-next-line no-console
  console.log(`Wrote: ${reportPath}`);
  // eslint-disable-next-line no-console
  console.log(`Wrote: ${summaryPath}`);
  // eslint-disable-next-line no-console
  console.log(`Total failure signals: ${totalFailures}`);

  if (totalFailures > 0) process.exitCode = 1;
}

main().catch((err) => {
  // eslint-disable-next-line no-console
  console.error(err);
  process.exit(1);
});
