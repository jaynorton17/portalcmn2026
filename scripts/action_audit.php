#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Endpoint action/flow auditor.
 *
 * Static checks:
 * - Uses docs/endpoint-manifest.json
 * - Matches UI-discovered action names (from link audit JSON and frontend.js)
 * - Flags policy mismatches and privilege exposure risks
 *
 * Optional probes (read-only defaults):
 * - Sends missing-nonce calls to selected endpoints
 * - Skips writes_state=true unless --probe-write-endpoints=1
 */

const CMN_ACTION_AUDIT_DEFAULT_RATE_MS = 350;
const CMN_ACTION_AUDIT_DEFAULT_TIMEOUT = 20;

function cmn_action_audit_usage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/action_audit.php [options]

Options:
  --base-url=<url>                    Base URL (default: https://covermenow.co.uk)
  --manifest=<path>                   Endpoint manifest (default: docs/endpoint-manifest.json)
  --plugin-file=<path>                Plugin file (default: covermenowone-one.php)
  --link-audit-json=<path>            Link audit JSON (optional; autodetect latest in docs/audits)
  --output-dir=<path>                 Output directory (default: docs/audits)
  --rate-ms=<int>                     Delay between HTTP probe requests in ms (default: 350)
  --timeout=<int>                     HTTP timeout seconds (default: 20)
  --probe=1                           Enable HTTP probe mode (default: 0)
  --probe-role=<role>                 Role to probe as (default: guest)
  --auth-config=<path>                JSON auth config used by probe role (optional)
  --resolve-ip=<ip>                   Resolve base host to fixed IP for HTTP probes (optional)
  --probe-write-endpoints=1           Allow probing writes_state=true endpoints (default: 0)
  --help                              Show help
TXT;
    fwrite(STDOUT, $usage . PHP_EOL);
}

function cmn_parse_cli(array $argv): array
{
    $root = dirname(__DIR__);
    $opts = [
        'base-url' => 'https://covermenow.co.uk',
        'manifest' => $root . '/docs/endpoint-manifest.json',
        'plugin-file' => $root . '/covermenowone-one.php',
        'link-audit-json' => null,
        'output-dir' => $root . '/docs/audits',
        'rate-ms' => CMN_ACTION_AUDIT_DEFAULT_RATE_MS,
        'timeout' => CMN_ACTION_AUDIT_DEFAULT_TIMEOUT,
        'probe' => false,
        'probe-role' => 'guest',
        'auth-config' => null,
        'resolve-ip' => '',
        'probe-write-endpoints' => false,
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $opts['help'] = true;
            continue;
        }
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $eq = strpos($arg, '=');
        $key = $eq === false ? substr($arg, 2) : substr($arg, 2, $eq - 2);
        $val = $eq === false ? '1' : substr($arg, $eq + 1);

        if (!array_key_exists($key, $opts)) {
            continue;
        }
        if (in_array($key, ['probe', 'probe-write-endpoints'], true)) {
            $opts[$key] = in_array(strtolower($val), ['1', 'true', 'yes', 'y', 'on'], true);
            continue;
        }
        if (in_array($key, ['rate-ms', 'timeout'], true)) {
            $opts[$key] = max(1, (int) $val);
            continue;
        }
        $opts[$key] = $val;
    }
    return $opts;
}

function cmn_find_latest_file(string $dir, string $prefix, string $suffix): ?string
{
    if (!is_dir($dir)) {
        return null;
    }
    $matches = glob(rtrim($dir, '/') . '/' . $prefix . '*' . $suffix) ?: [];
    if ($matches === []) {
        return null;
    }
    rsort($matches);
    return $matches[0] ?? null;
}

function cmn_extract_handler_blocks(string $source): array
{
    $blocks = [];
    if (!preg_match_all('/^\s*(?:public|protected|private)?\s*(?:static\s+)?function\s+([a-zA-Z0-9_]+)\s*\(/m', $source, $matches, PREG_OFFSET_CAPTURE)) {
        return $blocks;
    }
    $total = count($matches[0]);
    for ($i = 0; $i < $total; $i++) {
        $name = (string) $matches[1][$i][0];
        $start = (int) $matches[0][$i][1];
        $end = ($i + 1 < $total) ? (int) $matches[0][$i + 1][1] : strlen($source);
        $line = substr_count(substr($source, 0, $start), "\n") + 1;
        $blocks[$name] = [
            'line' => $line,
            'body' => (string) substr($source, $start, max(0, $end - $start)),
        ];
    }
    return $blocks;
}

function cmn_entry_action_name(array $entry): string
{
    $hook = (string) ($entry['hook'] ?? '');
    $prefixes = ['wp_ajax_nopriv_', 'wp_ajax_', 'admin_post_nopriv_', 'admin_post_'];
    foreach ($prefixes as $prefix) {
        if (str_starts_with($hook, $prefix)) {
            return substr($hook, strlen($prefix));
        }
    }
    return '';
}

function cmn_is_staff_endpoint(array $entry): bool
{
    $haystack = strtolower((string) ($entry['hook'] ?? '') . ' ' . (string) ($entry['handler'] ?? ''));
    if (preg_match('/(^|_)cmn_staff_|(^|_)staff(_|$)|handle_staff_|render_staff_/', $haystack)) {
        return true;
    }
    if (preg_match('/cmn_system_health_|cmn_match_|cmn_email_log_resend/', $haystack)) {
        return true;
    }
    return false;
}

function cmn_is_partner_admin_endpoint(array $entry): bool
{
    $haystack = strtolower((string) ($entry['hook'] ?? '') . ' ' . (string) ($entry['handler'] ?? ''));
    return (bool) preg_match('/school_partner_admin|partner_admin|partner_programme_(recalculate|set_tier|adjust_days|create_credit|void_credit)/', $haystack);
}

function cmn_is_public_token_endpoint(array $entry, string $handlerBody): bool
{
    $type = (string) ($entry['type'] ?? '');
    if (!in_array($type, ['wp_ajax_nopriv', 'admin_post_nopriv'], true)) {
        return false;
    }
    $haystack = strtolower((string) ($entry['hook'] ?? '') . ' ' . (string) ($entry['handler'] ?? '') . ' ' . $handlerBody);
    return (bool) preg_match('/token|candidate_response|marketing_runner|thread_|livechat/', $haystack);
}

function cmn_has_rate_limit(string $handlerBody): bool
{
    return (bool) preg_match('/cmn_rate_limit\s*\(|is_livechat_rate_limited\s*\(|enforce_school_partner_admin_action_throttle\s*\(/', $handlerBody);
}

function cmn_has_token_hash(string $handlerBody): bool
{
    return (bool) preg_match('/token_hash|hash_offer_response_token|hash_hmac|sha256|hash_equals/', $handlerBody);
}

function cmn_has_token_expiry(string $handlerBody): bool
{
    return (bool) preg_match('/expires_at|expiry|expired|token_expires|offer_expires/', $handlerBody);
}

function cmn_has_token_single_use(string $handlerBody): bool
{
    return (bool) preg_match('/consumed_at|token_consumed|consume|single-use|single_use/', $handlerBody);
}

function cmn_http_request(string $url, string $method, array $context, int $timeout, array $postFields = []): array
{
    if (!function_exists('curl_init')) {
        return cmn_http_request_via_cli($url, $method, $context, $timeout, $postFields);
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'status' => 0, 'final_url' => $url, 'body' => '', 'content_type' => '', 'error' => 'curl_init failed'];
    }
    $headers = [];
    if (!empty($context['cookie_header'])) {
        $headers[] = 'Cookie: ' . (string) $context['cookie_header'];
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CMN-Action-Audit/1.0');
    if ($headers !== []) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if (!empty($context['cookie_file'])) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, (string) $context['cookie_file']);
        curl_setopt($ch, CURLOPT_COOKIEJAR, (string) $context['cookie_file']);
    }
    if (!empty($context['resolve_host']) && !empty($context['resolve_ip'])) {
        $urlParts = parse_url($url);
        if (is_array($urlParts) && isset($urlParts['host']) && strtolower((string) $urlParts['host']) === strtolower((string) $context['resolve_host'])) {
            $scheme = strtolower((string) ($urlParts['scheme'] ?? 'https'));
            $port = (int) ($urlParts['port'] ?? ($scheme === 'http' ? 80 : 443));
            curl_setopt($ch, CURLOPT_RESOLVE, [(string) $context['resolve_host'] . ':' . $port . ':' . (string) $context['resolve_ip']]);
        }
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($raw === false) {
        return [
            'ok' => false,
            'status' => (int) ($info['http_code'] ?? 0),
            'final_url' => (string) ($info['url'] ?? $url),
            'body' => '',
            'content_type' => (string) ($info['content_type'] ?? ''),
            'error' => $error !== '' ? $error : 'request failed',
        ];
    }

    $headerSize = (int) ($info['header_size'] ?? 0);
    $body = (string) substr($raw, $headerSize);
    return [
        'ok' => true,
        'status' => (int) ($info['http_code'] ?? 0),
        'final_url' => (string) ($info['url'] ?? $url),
        'body' => $body,
        'content_type' => (string) ($info['content_type'] ?? ''),
        'error' => $error,
    ];
}

function cmn_http_request_via_cli(string $url, string $method, array $context, int $timeout, array $postFields = []): array
{
    $headerFile = tempnam(sys_get_temp_dir(), 'cmn_action_hdr_');
    $bodyFile = tempnam(sys_get_temp_dir(), 'cmn_action_body_');
    if ($headerFile === false || $bodyFile === false) {
        return ['ok' => false, 'status' => 0, 'final_url' => $url, 'body' => '', 'content_type' => '', 'error' => 'temp file allocation failed'];
    }

    $parts = [
        'curl',
        '-sS',
        '-L',
        '--max-redirs',
        '10',
        '--connect-timeout',
        '10',
        '--max-time',
        (string) max(1, $timeout),
        '-A',
        'CMN-Action-Audit/1.0',
        '-D',
        $headerFile,
        '-o',
        $bodyFile,
    ];
    if ($method === 'HEAD') {
        $parts[] = '-I';
    } elseif ($method === 'POST') {
        $parts[] = '-X';
        $parts[] = 'POST';
        if ($postFields !== []) {
            $parts[] = '--data';
            $parts[] = http_build_query($postFields);
        }
    } elseif ($method !== 'GET') {
        $parts[] = '-X';
        $parts[] = $method;
    }
    if (!empty($context['cookie_file'])) {
        $parts[] = '--cookie';
        $parts[] = (string) $context['cookie_file'];
        $parts[] = '--cookie-jar';
        $parts[] = (string) $context['cookie_file'];
    }
    if (!empty($context['cookie_header'])) {
        $parts[] = '-H';
        $parts[] = 'Cookie: ' . (string) $context['cookie_header'];
    }
    if (!empty($context['resolve_host']) && !empty($context['resolve_ip'])) {
        $urlParts = parse_url($url);
        if (is_array($urlParts) && isset($urlParts['host']) && strtolower((string) $urlParts['host']) === strtolower((string) $context['resolve_host'])) {
            $scheme = strtolower((string) ($urlParts['scheme'] ?? 'https'));
            $port = (int) ($urlParts['port'] ?? ($scheme === 'http' ? 80 : 443));
            $parts[] = '--resolve';
            $parts[] = (string) $context['resolve_host'] . ':' . $port . ':' . (string) $context['resolve_ip'];
        }
    }
    $parts[] = '-w';
    $parts[] = "\n__CMN_META__%{http_code}\t%{url_effective}\t%{content_type}";
    $parts[] = $url;

    $cmd = implode(' ', array_map('escapeshellarg', $parts));
    $output = [];
    $exitCode = 0;
    exec($cmd . ' 2>&1', $output, $exitCode);
    $combined = implode("\n", $output);

    $status = 0;
    $finalUrl = $url;
    $contentType = '';
    $errorText = '';

    $metaPos = strrpos($combined, '__CMN_META__');
    if ($metaPos !== false) {
        $meta = trim((string) substr($combined, $metaPos + strlen('__CMN_META__')));
        $metaParts = explode("\t", $meta);
        if (isset($metaParts[0])) {
            $status = (int) trim((string) $metaParts[0]);
        }
        if (isset($metaParts[1]) && trim((string) $metaParts[1]) !== '') {
            $finalUrl = trim((string) $metaParts[1]);
        }
        if (isset($metaParts[2])) {
            $contentType = trim((string) $metaParts[2]);
        }
        $before = trim((string) substr($combined, 0, $metaPos));
        if ($exitCode !== 0 && $before !== '') {
            $errorText = $before;
        }
    } elseif ($exitCode !== 0) {
        $errorText = trim($combined);
    }

    $body = is_file($bodyFile) ? (string) file_get_contents($bodyFile) : '';
    @unlink($headerFile);
    @unlink($bodyFile);

    return [
        'ok' => $exitCode === 0 || $status > 0,
        'status' => $status,
        'final_url' => $finalUrl,
        'body' => $body,
        'content_type' => $contentType,
        'error' => $errorText,
    ];
}

function cmn_setup_probe_context(
    string $role,
    array $authConfig,
    string $baseUrl,
    int $timeout,
    string $resolveHost = '',
    string $resolveIp = ''
): array
{
    $ctx = [
        'available' => $role === 'guest',
        'cookie_file' => '',
        'cookie_header' => '',
        'resolve_host' => $resolveHost,
        'resolve_ip' => $resolveIp,
        'note' => '',
        'auth_method' => $role === 'guest' ? 'guest' : 'none',
    ];
    if ($role === 'guest') {
        return $ctx;
    }
    $cfg = $authConfig[$role] ?? null;
    if (!is_array($cfg)) {
        $ctx['note'] = 'No auth config for probe role';
        return $ctx;
    }
    if (!empty($cfg['cookie_file']) && is_file((string) $cfg['cookie_file'])) {
        $ctx['cookie_file'] = (string) $cfg['cookie_file'];
        $ctx['available'] = true;
        $ctx['auth_method'] = 'cookie_file';
        return $ctx;
    }
    if (!empty($cfg['cookie_header']) && is_string($cfg['cookie_header'])) {
        $ctx['cookie_header'] = (string) $cfg['cookie_header'];
        $ctx['available'] = true;
        $ctx['auth_method'] = 'cookie_header';
        return $ctx;
    }

    $username = isset($cfg['username']) ? trim((string) $cfg['username']) : '';
    $password = isset($cfg['password']) ? (string) $cfg['password'] : '';
    if ($username === '' || $password === '') {
        $ctx['note'] = 'Probe auth missing cookie or username/password';
        return $ctx;
    }
    $loginUrl = isset($cfg['login_url']) && is_string($cfg['login_url']) && $cfg['login_url'] !== ''
        ? (string) $cfg['login_url']
        : rtrim($baseUrl, '/') . '/wp-login.php';
    $redirectTo = isset($cfg['redirect_to']) && is_string($cfg['redirect_to']) && $cfg['redirect_to'] !== ''
        ? (string) $cfg['redirect_to']
        : rtrim($baseUrl, '/') . '/wp-admin/';
    $cookieFile = tempnam(sys_get_temp_dir(), 'cmn_action_audit_cookie_');
    if ($cookieFile === false) {
        $ctx['note'] = 'Failed to create probe cookie jar';
        return $ctx;
    }

    $prime = cmn_http_request($loginUrl, 'GET', [
        'cookie_file' => $cookieFile,
        'resolve_host' => $resolveHost,
        'resolve_ip' => $resolveIp,
    ], $timeout);
    if (!$prime['ok']) {
        @unlink($cookieFile);
        $ctx['note'] = 'Failed to prime wp-login';
        return $ctx;
    }
    $loginResp = cmn_http_request($loginUrl, 'POST', [
        'cookie_file' => $cookieFile,
        'resolve_host' => $resolveHost,
        'resolve_ip' => $resolveIp,
    ], $timeout, [
        'log' => $username,
        'pwd' => $password,
        'wp-submit' => 'Log In',
        'redirect_to' => $redirectTo,
        'testcookie' => '1',
    ]);
    $cookieText = (string) file_get_contents($cookieFile);
    if (!$loginResp['ok'] || stripos($cookieText, 'wordpress_logged_in_') === false) {
        @unlink($cookieFile);
        $ctx['note'] = 'Probe login did not establish session';
        return $ctx;
    }
    $ctx['cookie_file'] = $cookieFile;
    $ctx['available'] = true;
    $ctx['auth_method'] = 'wp-login';
    return $ctx;
}

function cmn_safe_body_preview(string $body, int $max = 160): string
{
    $body = preg_replace('/\s+/', ' ', $body);
    if (!is_string($body)) {
        return '';
    }
    $body = trim($body);
    if ($body === '') {
        return '';
    }
    if (strlen($body) <= $max) {
        return $body;
    }
    return substr($body, 0, $max);
}

function cmn_write_csv(string $path, array $rows): void
{
    $fh = fopen($path, 'wb');
    if ($fh === false) {
        throw new RuntimeException("Failed to open CSV: {$path}");
    }
    if ($rows === []) {
        fputcsv($fh, [
            'type', 'hook', 'action_name', 'handler', 'file', 'line',
            'writes_state', 'nonce_mode', 'ability_required', 'nopriv',
            'reachable_from_ui', 'policy_result', 'risk_level',
            'probe_status', 'probe_result', 'probe_final_url', 'probe_preview'
        ]);
        fclose($fh);
        return;
    }
    fputcsv($fh, array_keys($rows[0]));
    foreach ($rows as $row) {
        fputcsv($fh, $row);
    }
    fclose($fh);
}

function cmn_main(array $argv): int
{
    $opts = cmn_parse_cli($argv);
    if ($opts['help']) {
        cmn_action_audit_usage();
        return 0;
    }

    $baseUrl = rtrim((string) $opts['base-url'], '/');
    $resolveIp = trim((string) ($opts['resolve-ip'] ?? ''));
    $baseUrlParts = parse_url($baseUrl);
    $resolveHost = is_array($baseUrlParts) ? (string) ($baseUrlParts['host'] ?? '') : '';
    $manifestPath = (string) $opts['manifest'];
    $pluginPath = (string) $opts['plugin-file'];
    $outputDir = (string) $opts['output-dir'];
    $rateMs = (int) $opts['rate-ms'];
    $timeout = (int) $opts['timeout'];
    $probeEnabled = (bool) $opts['probe'];
    $probeRole = trim((string) $opts['probe-role']) !== '' ? trim((string) $opts['probe-role']) : 'guest';
    $probeWrites = (bool) $opts['probe-write-endpoints'];

    if (!is_file($manifestPath)) {
        throw new RuntimeException("Manifest not found: {$manifestPath}");
    }
    if (!is_file($pluginPath)) {
        throw new RuntimeException("Plugin file not found: {$pluginPath}");
    }
    if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
        throw new RuntimeException("Failed to create output dir: {$outputDir}");
    }

    $manifest = json_decode((string) file_get_contents($manifestPath), true);
    if (!is_array($manifest) || !isset($manifest['entrypoints']) || !is_array($manifest['entrypoints'])) {
        throw new RuntimeException("Invalid manifest JSON: {$manifestPath}");
    }

    $pluginSource = (string) file_get_contents($pluginPath);
    $functionBlocks = cmn_extract_handler_blocks($pluginSource);

    $linkAuditJsonPath = (string) ($opts['link-audit-json'] ?? '');
    if ($linkAuditJsonPath === '') {
        $auto = cmn_find_latest_file(rtrim($outputDir, '/'), 'link-audit-', '.json');
        if (is_string($auto) && $auto !== '') {
            $linkAuditJsonPath = $auto;
        }
    }
    $uiActionNames = [];
    $uiActionSources = [];
    if ($linkAuditJsonPath !== '' && is_file($linkAuditJsonPath)) {
        $decoded = json_decode((string) file_get_contents($linkAuditJsonPath), true);
        if (is_array($decoded)) {
            $formSummary = (array) ($decoded['form_action_summary'] ?? []);
            foreach ($formSummary as $actionName => $count) {
                $uiActionNames[(string) $actionName] = true;
                $uiActionSources[(string) $actionName][] = 'link_audit_form';
            }
            $ajaxSummary = (array) ($decoded['ajax_action_summary'] ?? []);
            foreach ($ajaxSummary as $actionName => $count) {
                $uiActionNames[(string) $actionName] = true;
                $uiActionSources[(string) $actionName][] = 'link_audit_inline_js';
            }
        }
    }

    // Parse frontend.js for action literals.
    $frontendPath = dirname($pluginPath) . '/frontend.js';
    if (is_file($frontendPath)) {
        $frontendSource = (string) file_get_contents($frontendPath);
        if ($frontendSource !== '' && preg_match_all('/\baction\s*[:=]\s*[\'"]([a-zA-Z0-9_\-:]+)[\'"]/', $frontendSource, $matches)) {
            foreach ($matches[1] as $actionName) {
                $name = (string) $actionName;
                $uiActionNames[$name] = true;
                $uiActionSources[$name][] = 'frontend_js';
            }
        }
    }

    $entries = array_values(array_filter((array) $manifest['entrypoints'], static function ($entry): bool {
        $type = (string) ($entry['type'] ?? '');
        return in_array($type, ['wp_ajax', 'wp_ajax_nopriv', 'admin_post', 'admin_post_nopriv'], true);
    }));

    $probeContext = ['available' => false, 'note' => 'probe disabled'];
    $tempProbeCookie = '';
    if ($probeEnabled) {
        $authConfig = [];
        if (!empty($opts['auth-config']) && is_file((string) $opts['auth-config'])) {
            $decoded = json_decode((string) file_get_contents((string) $opts['auth-config']), true);
            if (is_array($decoded)) {
                $authConfig = $decoded;
            }
        }
        $probeContext = cmn_setup_probe_context($probeRole, $authConfig, $baseUrl, $timeout, $resolveHost, $resolveIp);
        if (!empty($probeContext['cookie_file']) && str_starts_with((string) $probeContext['cookie_file'], sys_get_temp_dir())) {
            $tempProbeCookie = (string) $probeContext['cookie_file'];
        }
    }

    $rows = [];
    $violations = [];
    $probeSkipped = 0;
    $probeAttempted = 0;

    foreach ($entries as $entry) {
        $type = (string) ($entry['type'] ?? '');
        $hook = (string) ($entry['hook'] ?? '');
        $handler = (string) ($entry['handler'] ?? '');
        $line = (int) ($entry['line'] ?? 0);
        $writesState = (bool) ($entry['writes_state'] ?? false);
        $nonceMode = (string) ($entry['nonce_mode'] ?? 'none');
        $ability = (string) ($entry['ability_required'] ?? '');
        $nopriv = (bool) ($entry['nopriv'] ?? false);
        $actionName = cmn_entry_action_name($entry);
        if ($actionName === '') {
            continue;
        }

        $body = '';
        $handlerLine = $line;
        if (isset($functionBlocks[$handler])) {
            $body = (string) $functionBlocks[$handler]['body'];
            $handlerLine = (int) $functionBlocks[$handler]['line'];
        }

        $reachableFromUi = isset($uiActionNames[$actionName]);
        if (!$reachableFromUi) {
            continue;
        }

        $policyIssues = [];
        if ($writesState && $nonceMode !== 'required') {
            $policyIssues[] = 'writes_state_without_required_nonce';
        }
        if (cmn_is_staff_endpoint($entry) && $ability !== 'portal.staff.view') {
            $policyIssues[] = 'staff_endpoint_missing_portal.staff.view';
        }
        if (cmn_is_partner_admin_endpoint($entry) && $ability !== 'partner.admin.mutate') {
            $policyIssues[] = 'partner_endpoint_missing_partner.admin.mutate';
        }
        $isPublicToken = cmn_is_public_token_endpoint($entry, $body);
        $hasRateLimit = cmn_has_rate_limit($body);
        if ($nopriv && $writesState && !$isPublicToken) {
            $policyIssues[] = 'nopriv_write_without_token_policy';
        }
        if ($isPublicToken) {
            if (!cmn_has_token_hash($body)) {
                $policyIssues[] = 'public_token_missing_hash_compare';
            }
            if (!cmn_has_token_expiry($body)) {
                $policyIssues[] = 'public_token_missing_expiry_check';
            }
            if ($writesState && !cmn_has_token_single_use($body)) {
                $policyIssues[] = 'public_token_missing_single_use';
            }
            if (!$hasRateLimit) {
                $policyIssues[] = 'public_token_missing_rate_limit';
            }
        }

        $policyResult = $policyIssues === [] ? 'pass' : implode(';', $policyIssues);
        $risk = 'low';
        if ($policyIssues !== []) {
            $risk = 'medium';
            foreach ($policyIssues as $issue) {
                if (str_contains($issue, 'missing') || str_contains($issue, 'without')) {
                    $risk = 'high';
                    break;
                }
            }
            $violations[] = [
                'hook' => $hook,
                'file' => (string) ($entry['file'] ?? 'covermenowone-one.php'),
                'line' => $handlerLine > 0 ? $handlerLine : $line,
                'issues' => $policyIssues,
            ];
        }

        $probeStatus = 'not_run';
        $probeResult = '';
        $probeFinalUrl = '';
        $probePreview = '';

        if ($probeEnabled) {
            if (!$probeContext['available']) {
                $probeStatus = 'blocked';
                $probeResult = 'auth_unavailable:' . ($probeContext['note'] ?? '');
            } elseif ($writesState && !$probeWrites) {
                $probeStatus = 'skipped';
                $probeResult = 'writes_state=true requires staging or --probe-write-endpoints=1';
                $probeSkipped++;
            } else {
                $probeAttempted++;
                $endpointUrl = '';
                $postData = ['action' => $actionName];
                if (str_starts_with($type, 'wp_ajax')) {
                    $endpointUrl = $baseUrl . '/wp-admin/admin-ajax.php';
                    // Explicitly omit nonce to validate guard response.
                    $postData['cmn_probe'] = 'missing_nonce';
                } else {
                    $endpointUrl = $baseUrl . '/wp-admin/admin-post.php';
                    $postData['cmn_probe'] = 'missing_nonce';
                }
                $resp = cmn_http_request($endpointUrl, 'POST', $probeContext, $timeout, $postData);
                $probeStatus = (string) $resp['status'];
                $probeFinalUrl = (string) $resp['final_url'];
                $bodyPreview = cmn_safe_body_preview((string) $resp['body']);
                $probePreview = $bodyPreview;

                $normalizedBody = strtolower((string) $resp['body']);
                if (str_contains($normalizedBody, 'nonce') || str_contains($normalizedBody, 'access denied') || str_contains($normalizedBody, 'forbidden')) {
                    $probeResult = 'guard_denied_missing_nonce_or_ability';
                } elseif ((int) $resp['status'] === 403 || (int) $resp['status'] === 401) {
                    $probeResult = 'http_denied';
                } elseif ($resp['error'] !== '') {
                    $probeResult = 'error:' . $resp['error'];
                } else {
                    $probeResult = 'response_requires_manual_review';
                }
                usleep($rateMs * 1000);
            }
        }

        $rows[] = [
            'type' => $type,
            'hook' => $hook,
            'action_name' => $actionName,
            'handler' => $handler,
            'file' => (string) ($entry['file'] ?? 'covermenowone-one.php'),
            'line' => (string) ($handlerLine > 0 ? $handlerLine : $line),
            'writes_state' => $writesState ? '1' : '0',
            'nonce_mode' => $nonceMode,
            'ability_required' => $ability,
            'nopriv' => $nopriv ? '1' : '0',
            'reachable_from_ui' => '1',
            'policy_result' => $policyResult,
            'risk_level' => $risk,
            'probe_status' => $probeStatus,
            'probe_result' => $probeResult,
            'probe_final_url' => $probeFinalUrl,
            'probe_preview' => $probePreview,
        ];
    }

    usort($rows, static function (array $a, array $b): int {
        return strcmp((string) $a['hook'], (string) $b['hook']);
    });

    $timestamp = gmdate('Ymd-His');
    $csvPath = rtrim($outputDir, '/') . "/action-audit-{$timestamp}.csv";
    $jsonPath = rtrim($outputDir, '/') . "/action-audit-{$timestamp}.json";
    $summaryPath = rtrim($outputDir, '/') . "/action-audit-{$timestamp}.md";

    cmn_write_csv($csvPath, $rows);

    $jsonPayload = [
        'generated_at_utc' => gmdate('c'),
        'base_url' => $baseUrl,
        'manifest' => $manifestPath,
        'link_audit_json' => $linkAuditJsonPath,
        'ui_action_count' => count($uiActionNames),
        'rows_count' => count($rows),
        'probe_enabled' => $probeEnabled,
        'probe_role' => $probeRole,
        'probe_context' => [
            'available' => (bool) ($probeContext['available'] ?? false),
            'auth_method' => (string) ($probeContext['auth_method'] ?? 'none'),
            'note' => (string) ($probeContext['note'] ?? ''),
            'attempted' => $probeAttempted,
            'skipped' => $probeSkipped,
        ],
        'violations' => $violations,
        'rows' => $rows,
    ];
    file_put_contents($jsonPath, json_encode($jsonPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

    $totalHigh = 0;
    $totalMedium = 0;
    foreach ($rows as $row) {
        if ($row['risk_level'] === 'high') {
            $totalHigh++;
        } elseif ($row['risk_level'] === 'medium') {
            $totalMedium++;
        }
    }

    $lines = [];
    $lines[] = '# Action Audit Summary';
    $lines[] = '';
    $lines[] = '- Generated: ' . gmdate('c');
    $lines[] = '- Base URL: ' . $baseUrl;
    $lines[] = '- Endpoint rows audited (UI-reachable): ' . count($rows);
    $lines[] = '- UI action names discovered: ' . count($uiActionNames);
    $lines[] = '- CSV: `' . basename($csvPath) . '`';
    $lines[] = '- JSON: `' . basename($jsonPath) . '`';
    $lines[] = '';
    $lines[] = '## Risk Totals';
    $lines[] = '- High: ' . $totalHigh;
    $lines[] = '- Medium: ' . $totalMedium;
    $lines[] = '- Low: ' . max(0, count($rows) - $totalHigh - $totalMedium);
    $lines[] = '';
    $lines[] = '## Probe Mode';
    $lines[] = '- Enabled: ' . ($probeEnabled ? 'yes' : 'no');
    $lines[] = '- Role: ' . $probeRole;
    $lines[] = '- Probe context available: ' . ((bool) ($probeContext['available'] ?? false) ? 'yes' : 'no');
    if (!empty($probeContext['note'])) {
        $lines[] = '- Probe note: ' . (string) $probeContext['note'];
    }
    $lines[] = '- Probe attempted: ' . $probeAttempted;
    $lines[] = '- Probe skipped: ' . $probeSkipped;
    $lines[] = '';
    $lines[] = '## Policy Violations';
    if ($violations === []) {
        $lines[] = '- None';
    } else {
        foreach ($violations as $violation) {
            $issueText = implode(', ', (array) ($violation['issues'] ?? []));
            $lines[] = '- ' . $violation['hook'] . ' @ ' . $violation['file'] . ':' . $violation['line'] . ' => ' . $issueText;
        }
    }
    $lines[] = '';

    file_put_contents($summaryPath, implode(PHP_EOL, $lines) . PHP_EOL);

    if ($tempProbeCookie !== '' && is_file($tempProbeCookie)) {
        @unlink($tempProbeCookie);
    }

    fwrite(STDOUT, "Action audit complete.\n");
    fwrite(STDOUT, "CSV: {$csvPath}\n");
    fwrite(STDOUT, "JSON: {$jsonPath}\n");
    fwrite(STDOUT, "Summary: {$summaryPath}\n");
    fwrite(STDOUT, "Rows: " . count($rows) . "\n");
    fwrite(STDOUT, "Violations: " . count($violations) . "\n");

    return 0;
}

try {
    exit(cmn_main($argv));
} catch (Throwable $e) {
    fwrite(STDERR, '[action_audit] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
