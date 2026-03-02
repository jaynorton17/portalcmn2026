#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const TARGET_URLS = [
  'https://covermenow.co.uk/covermenow-one/',
  'https://covermenow.co.uk/wp-login.php?cmn_admin=1',
  'https://covermenow.co.uk/covermenow-one/?nocache=1',
];

function formatTimestamp(now) {
  const pad = (n) => String(n).padStart(2, '0');
  return `${now.getUTCFullYear()}${pad(now.getUTCMonth() + 1)}${pad(now.getUTCDate())}-${pad(now.getUTCHours())}${pad(now.getUTCMinutes())}${pad(now.getUTCSeconds())}`;
}

function nowIso() {
  return new Date().toISOString();
}

function pickErrorText(item) {
  if (!item) return '';
  if (item.error_text) return String(item.error_text);
  if (item.text) return String(item.text);
  if (item.failure_text) return String(item.failure_text);
  return '';
}

async function run() {
  const startedAt = nowIso();
  const ts = formatTimestamp(new Date());
  const auditsDir = path.resolve(__dirname, '..', 'docs', 'audits');
  fs.mkdirSync(auditsDir, { recursive: true });

  const jsonPath = path.join(auditsDir, `public-browser-sweep-${ts}.json`);
  const mdPath = path.join(auditsDir, `public-browser-sweep-${ts}.md`);

  const report = {
    started_at: startedAt,
    finished_at: null,
    target_urls: TARGET_URLS,
    pages: [],
    totals: {
      console_errors: 0,
      console_warnings: 0,
      page_errors: 0,
      failed_requests: 0,
      plugin_asset_non_200_304: 0,
    },
    top_failures: [],
    pass: true,
  };

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
  });
  const page = await context.newPage();
  const cdp = await context.newCDPSession(page);
  await cdp.send('Network.enable');
  await cdp.send('Network.setCacheDisabled', { cacheDisabled: true });

  const allConsole = [];
  const allPageErrors = [];
  const allFailedRequests = [];
  const allPluginAssetFails = [];

  page.on('console', (msg) => {
    const type = msg.type();
    const text = msg.text();
    const row = {
      at: nowIso(),
      page_url: page.url(),
      type,
      text,
    };
    allConsole.push(row);
  });

  page.on('pageerror', (err) => {
    allPageErrors.push({
      at: nowIso(),
      page_url: page.url(),
      text: String(err),
    });
  });

  page.on('response', (response) => {
    const status = response.status();
    const url = response.url();
    if (status >= 400) {
      const row = {
        at: nowIso(),
        page_url: page.url(),
        url,
        status,
        resource_type: response.request().resourceType(),
      };
      allFailedRequests.push(row);
      if (/\/wp-content\/plugins\/covermenow-one\//i.test(url)) {
        allPluginAssetFails.push({
          ...row,
          error_text: `Plugin asset returned HTTP ${status}`,
        });
      }
    } else if (/\/wp-content\/plugins\/covermenow-one\//i.test(url) && status !== 200 && status !== 304) {
      allPluginAssetFails.push({
        at: nowIso(),
        page_url: page.url(),
        url,
        status,
        resource_type: response.request().resourceType(),
        error_text: `Plugin asset returned HTTP ${status}`,
      });
    }
  });

  page.on('requestfailed', (request) => {
    const row = {
      at: nowIso(),
      page_url: page.url(),
      url: request.url(),
      status: 0,
      resource_type: request.resourceType(),
      error_text: request.failure() ? request.failure().errorText : 'request_failed',
    };
    allFailedRequests.push(row);
    if (/\/wp-content\/plugins\/covermenow-one\//i.test(request.url())) {
      allPluginAssetFails.push(row);
    }
  });

  for (const targetUrl of TARGET_URLS) {
    const pageEntry = {
      target_url: targetUrl,
      started_at: nowIso(),
      ended_at: null,
      final_url: '',
      navigation_status: null,
      hard_refresh_status: null,
      error: null,
    };
    try {
      const firstNav = await page.goto(targetUrl, {
        waitUntil: 'domcontentloaded',
        timeout: 60000,
      });
      pageEntry.navigation_status = firstNav ? firstNav.status() : null;
      const hardRefresh = await page.reload({
        waitUntil: 'networkidle',
        timeout: 60000,
      });
      pageEntry.hard_refresh_status = hardRefresh ? hardRefresh.status() : null;
      pageEntry.final_url = page.url();
      await page.waitForTimeout(1500);
    } catch (err) {
      pageEntry.error = String(err);
      report.pass = false;
    } finally {
      pageEntry.ended_at = nowIso();
      report.pages.push(pageEntry);
    }
  }

  await context.close();
  await browser.close();

  const consoleErrors = allConsole.filter((m) => m.type === 'error');
  const consoleWarnings = allConsole.filter((m) => m.type === 'warning');
  const pluginAssetBadStatus = allPluginAssetFails.filter((r) => r.status !== 200 && r.status !== 304);

  report.totals.console_errors = consoleErrors.length;
  report.totals.console_warnings = consoleWarnings.length;
  report.totals.page_errors = allPageErrors.length;
  report.totals.failed_requests = allFailedRequests.length;
  report.totals.plugin_asset_non_200_304 = pluginAssetBadStatus.length;
  report.finished_at = nowIso();
  report.pass = report.pass
    && report.totals.console_errors === 0
    && report.totals.page_errors === 0
    && report.totals.failed_requests === 0
    && report.totals.plugin_asset_non_200_304 === 0;

  const mergedFailures = [];
  for (const c of consoleErrors) {
    mergedFailures.push({
      kind: 'console_error',
      url: c.page_url || '',
      status: null,
      error_text: c.text,
    });
  }
  for (const p of allPageErrors) {
    mergedFailures.push({
      kind: 'page_error',
      url: p.page_url || '',
      status: null,
      error_text: p.text,
    });
  }
  for (const r of allFailedRequests) {
    mergedFailures.push({
      kind: 'request_failure',
      url: r.url || '',
      status: r.status,
      error_text: r.error_text || `HTTP ${r.status}`,
    });
  }

  report.top_failures = mergedFailures.slice(0, 20);

  fs.writeFileSync(jsonPath, JSON.stringify({
    ...report,
    console_messages: allConsole,
    page_errors: allPageErrors,
    failed_requests: allFailedRequests,
    plugin_asset_failures: allPluginAssetFails,
  }, null, 2));

  const mdLines = [];
  mdLines.push(`# Public Browser Sweep (${ts})`);
  mdLines.push('');
  mdLines.push(`- Started: ${report.started_at}`);
  mdLines.push(`- Finished: ${report.finished_at}`);
  mdLines.push(`- Result: ${report.pass ? 'PASS' : 'FAIL'}`);
  mdLines.push('');
  mdLines.push('## Totals');
  mdLines.push('');
  mdLines.push(`- Console errors: ${report.totals.console_errors}`);
  mdLines.push(`- Console warnings: ${report.totals.console_warnings}`);
  mdLines.push(`- Page errors: ${report.totals.page_errors}`);
  mdLines.push(`- Failed requests (>=400 or requestfailed): ${report.totals.failed_requests}`);
  mdLines.push(`- Plugin asset non-200/304: ${report.totals.plugin_asset_non_200_304}`);
  mdLines.push('');
  mdLines.push('## URL Results');
  mdLines.push('');
  mdLines.push('| URL | Navigation | Hard Refresh | Final URL | Error |');
  mdLines.push('|---|---:|---:|---|---|');
  for (const p of report.pages) {
    mdLines.push(`| ${p.target_url} | ${p.navigation_status ?? ''} | ${p.hard_refresh_status ?? ''} | ${p.final_url || ''} | ${p.error || ''} |`);
  }
  mdLines.push('');
  mdLines.push('## Top 20 Failures');
  mdLines.push('');
  if (report.top_failures.length === 0) {
    mdLines.push('- None');
  } else {
    mdLines.push('| Kind | URL | Status | Error Text |');
    mdLines.push('|---|---|---:|---|');
    for (const f of report.top_failures) {
      const safeError = pickErrorText(f).replace(/\|/g, '\\|').replace(/\n/g, ' ');
      mdLines.push(`| ${f.kind} | ${f.url || ''} | ${f.status ?? ''} | ${safeError} |`);
    }
  }
  mdLines.push('');
  mdLines.push('## Plugin Asset Failures');
  mdLines.push('');
  if (allPluginAssetFails.length === 0) {
    mdLines.push('- None');
  } else {
    mdLines.push('| URL | Status | Resource Type | Error |');
    mdLines.push('|---|---:|---|---|');
    for (const r of allPluginAssetFails) {
      const safeError = pickErrorText(r).replace(/\|/g, '\\|').replace(/\n/g, ' ');
      mdLines.push(`| ${r.url || ''} | ${r.status ?? ''} | ${r.resource_type || ''} | ${safeError} |`);
    }
  }

  fs.writeFileSync(mdPath, `${mdLines.join('\n')}\n`);

  console.log(`RESULT=${report.pass ? 'PASS' : 'FAIL'}`);
  console.log(`JSON=${jsonPath}`);
  console.log(`MD=${mdPath}`);
  console.log(`TOTAL_CONSOLE_ERRORS=${report.totals.console_errors}`);
  console.log(`TOTAL_FAILED_REQUESTS=${report.totals.failed_requests}`);
  console.log(`TOTAL_PLUGIN_ASSET_NON_200_304=${report.totals.plugin_asset_non_200_304}`);
}

run().catch((err) => {
  console.error(`SWEEP_FAILED: ${String(err)}`);
  process.exit(1);
});
