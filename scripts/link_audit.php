#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Portal/Admin link and route auditor.
 *
 * Read-only by default. Supports role contexts with:
 * - guest (no auth)
 * - cookie_file (Netscape cookie jar)
 * - cookie_header ("name=value; ...")
 * - username/password (WordPress wp-login.php flow)
 *
 * Usage example:
 *   php scripts/link_audit.php \
 *     --base-url=https://covermenow.co.uk \
 *     --auth-config=docs/audits/auth-config.example.json
 */

const CMN_LINK_AUDIT_DEFAULT_ROLES = ['guest', 'admin', 'staff', 'school', 'candidate'];
const CMN_LINK_AUDIT_DEFAULT_RATE_MS = 350;
const CMN_LINK_AUDIT_DEFAULT_TIMEOUT = 20;
const CMN_LINK_AUDIT_DEFAULT_MAX_URLS_PER_ROLE = 400;

function cmn_link_audit_usage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/link_audit.php [options]

Options:
  --base-url=<url>                 Base URL (default: https://covermenow.co.uk)
  --portal-path=<path>             Portal path (default: /covermenow-one/)
  --roles=<csv>                    Roles to audit (default: guest,admin,staff,school,candidate)
  --auth-config=<path>             JSON auth config (optional)
  --resolve-ip=<ip>                Resolve base host to fixed IP for curl requests (optional)
  --output-dir=<path>              Output directory (default: docs/audits)
  --rate-ms=<int>                  Delay between requests in ms (default: 350)
  --timeout=<int>                  HTTP timeout seconds (default: 20)
  --max-urls-per-role=<int>        Max internal URLs checked per role (default: 400)
  --seed-url=<url>                 Extra seed URL (repeatable)
  --help                           Show help

Auth config JSON example:
{
  "admin": { "cookie_file": "/secure/admin.cookies" },
  "staff": { "cookie_header": "wordpress_logged_in_...=..." },
  "school": { "username": "school@example.com", "password": "..." },
  "candidate": { "username": "candidate@example.com", "password": "...", "login_url": "https://.../wp-login.php" }
}
TXT;
    fwrite(STDOUT, $usage . PHP_EOL);
}

function cmn_parse_cli(array $argv): array
{
    $options = [
        'base-url' => 'https://covermenow.co.uk',
        'portal-path' => '/covermenow-one/',
        'roles' => CMN_LINK_AUDIT_DEFAULT_ROLES,
        'auth-config' => null,
        'resolve-ip' => '',
        'output-dir' => dirname(__DIR__) . '/docs/audits',
        'rate-ms' => CMN_LINK_AUDIT_DEFAULT_RATE_MS,
        'timeout' => CMN_LINK_AUDIT_DEFAULT_TIMEOUT,
        'max-urls-per-role' => CMN_LINK_AUDIT_DEFAULT_MAX_URLS_PER_ROLE,
        'seed-url' => [],
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $options['help'] = true;
            continue;
        }
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $eqPos = strpos($arg, '=');
        $key = $eqPos === false ? substr($arg, 2) : substr($arg, 2, $eqPos - 2);
        $value = $eqPos === false ? '1' : substr($arg, $eqPos + 1);

        if ($key === 'seed-url') {
            $options['seed-url'][] = $value;
            continue;
        }
        if (!array_key_exists($key, $options)) {
            continue;
        }

        switch ($key) {
            case 'roles':
                $parsed = array_values(array_filter(array_map('trim', explode(',', $value)), static fn($v) => $v !== ''));
                $options['roles'] = $parsed !== [] ? $parsed : CMN_LINK_AUDIT_DEFAULT_ROLES;
                break;
            case 'rate-ms':
            case 'timeout':
            case 'max-urls-per-role':
                $options[$key] = max(1, (int) $value);
                break;
            default:
                $options[$key] = $value;
                break;
        }
    }

    return $options;
}

function cmn_load_auth_config(?string $path): array
{
    if ($path === null || $path === '') {
        return [];
    }
    if (!is_file($path)) {
        throw new RuntimeException("Auth config not found: {$path}");
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        throw new RuntimeException("Invalid auth config JSON: {$path}");
    }
    return $decoded;
}

function cmn_base_origin(string $baseUrl): string
{
    $parts = parse_url($baseUrl);
    if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
        throw new RuntimeException("Invalid base URL: {$baseUrl}");
    }
    $origin = $parts['scheme'] . '://' . $parts['host'];
    if (isset($parts['port'])) {
        $origin .= ':' . $parts['port'];
    }
    return $origin;
}

function cmn_join_url(string $baseUrl, string $candidate): ?string
{
    $candidate = trim($candidate);
    if ($candidate === '' || $candidate === '#') {
        return null;
    }
    $lower = strtolower($candidate);
    if (
        str_starts_with($lower, 'javascript:') ||
        str_starts_with($lower, 'mailto:') ||
        str_starts_with($lower, 'tel:') ||
        str_starts_with($lower, 'data:')
    ) {
        return null;
    }

    $baseParts = parse_url($baseUrl);
    if (!is_array($baseParts) || !isset($baseParts['scheme'], $baseParts['host'])) {
        return null;
    }
    $scheme = (string) $baseParts['scheme'];
    $host = (string) $baseParts['host'];
    $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
    $origin = $scheme . '://' . $host . $port;

    if (str_starts_with($candidate, '//')) {
        $absolute = $scheme . ':' . $candidate;
    } elseif (preg_match('#^https?://#i', $candidate)) {
        $absolute = $candidate;
    } elseif (str_starts_with($candidate, '/')) {
        $absolute = $origin . $candidate;
    } else {
        $basePath = (string) ($baseParts['path'] ?? '/');
        $dirPath = preg_replace('#/[^/]*$#', '/', $basePath);
        if (!is_string($dirPath) || $dirPath === '') {
            $dirPath = '/';
        }
        $absolute = $origin . $dirPath . $candidate;
    }

    $parts = parse_url($absolute);
    if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
        return null;
    }

    // Remove fragment.
    unset($parts['fragment']);

    $normalized = $parts['scheme'] . '://' . $parts['host'];
    if (isset($parts['port'])) {
        $normalized .= ':' . $parts['port'];
    }
    $normalized .= $parts['path'] ?? '/';
    if (isset($parts['query']) && $parts['query'] !== '') {
        $normalized .= '?' . $parts['query'];
    }
    return $normalized;
}

function cmn_is_internal_url(string $url, string $origin): bool
{
    $a = parse_url($url);
    $b = parse_url($origin);
    if (!is_array($a) || !is_array($b)) {
        return false;
    }
    return strtolower((string) ($a['host'] ?? '')) === strtolower((string) ($b['host'] ?? ''));
}

function cmn_http_request(
    string $url,
    string $method,
    array $context,
    int $timeoutSeconds,
    bool $followRedirects = true,
    array $postFields = []
): array {
    if (!function_exists('curl_init')) {
        return cmn_http_request_via_cli($url, $method, $context, $timeoutSeconds, $followRedirects, $postFields);
    }

    $headers = [];
    if (!empty($context['cookie_header'])) {
        $headers[] = 'Cookie: ' . (string) $context['cookie_header'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return [
            'ok' => false,
            'status' => 0,
            'final_url' => $url,
            'headers' => '',
            'body' => '',
            'error' => 'curl_init failed',
            'content_type' => '',
        ];
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirects);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CMN-Link-Audit/1.0');
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
    if ($method === 'HEAD') {
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
    } elseif ($method === 'POST') {
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
            'headers' => '',
            'body' => '',
            'error' => $error !== '' ? $error : 'request failed',
            'content_type' => (string) ($info['content_type'] ?? ''),
        ];
    }

    $headerSize = (int) ($info['header_size'] ?? 0);
    $headersRaw = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    return [
        'ok' => true,
        'status' => (int) ($info['http_code'] ?? 0),
        'final_url' => (string) ($info['url'] ?? $url),
        'headers' => is_string($headersRaw) ? $headersRaw : '',
        'body' => is_string($body) ? $body : '',
        'error' => $error,
        'content_type' => (string) ($info['content_type'] ?? ''),
    ];
}

function cmn_http_request_via_cli(
    string $url,
    string $method,
    array $context,
    int $timeoutSeconds,
    bool $followRedirects,
    array $postFields = []
): array {
    $headerFile = tempnam(sys_get_temp_dir(), 'cmn_link_hdr_');
    $bodyFile = tempnam(sys_get_temp_dir(), 'cmn_link_body_');
    if ($headerFile === false || $bodyFile === false) {
        return [
            'ok' => false,
            'status' => 0,
            'final_url' => $url,
            'headers' => '',
            'body' => '',
            'error' => 'Failed to allocate temp files for curl CLI fallback',
            'content_type' => '',
        ];
    }

    $parts = [
        'curl',
        '-sS',
        '--connect-timeout',
        '10',
        '--max-time',
        (string) max(1, $timeoutSeconds),
        '-A',
        'CMN-Link-Audit/1.0',
        '-D',
        $headerFile,
        '-o',
        $bodyFile,
    ];
    if ($followRedirects) {
        $parts[] = '-L';
        $parts[] = '--max-redirs';
        $parts[] = '10';
    }
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
        $meta = substr($combined, $metaPos + strlen('__CMN_META__'));
        $meta = trim((string) $meta);
        $partsMeta = explode("\t", $meta);
        if (isset($partsMeta[0])) {
            $status = (int) trim((string) $partsMeta[0]);
        }
        if (isset($partsMeta[1]) && trim((string) $partsMeta[1]) !== '') {
            $finalUrl = trim((string) $partsMeta[1]);
        }
        if (isset($partsMeta[2])) {
            $contentType = trim((string) $partsMeta[2]);
        }
        $beforeMeta = trim((string) substr($combined, 0, $metaPos));
        if ($exitCode !== 0 && $beforeMeta !== '') {
            $errorText = $beforeMeta;
        }
    } elseif ($exitCode !== 0) {
        $errorText = trim($combined);
    }

    $headersRaw = is_file($headerFile) ? (string) file_get_contents($headerFile) : '';
    $bodyRaw = is_file($bodyFile) ? (string) file_get_contents($bodyFile) : '';
    @unlink($headerFile);
    @unlink($bodyFile);

    return [
        'ok' => $exitCode === 0 || $status > 0,
        'status' => $status,
        'final_url' => $finalUrl,
        'headers' => $headersRaw,
        'body' => $bodyRaw,
        'error' => $errorText,
        'content_type' => $contentType,
    ];
}

function cmn_extract_title(string $html): string
{
    if ($html === '') {
        return '';
    }
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $loaded = $doc->loadHTML($html);
    libxml_clear_errors();
    if (!$loaded) {
        return '';
    }
    $nodes = $doc->getElementsByTagName('title');
    if ($nodes->length < 1) {
        return '';
    }
    return trim((string) $nodes->item(0)?->textContent);
}

function cmn_extract_targets_from_html(string $html, string $sourceUrl, string $origin): array
{
    $result = [
        'targets' => [],
        'form_actions' => [],
        'ajax_actions' => [],
        'mixed_content' => [],
    ];
    if ($html === '') {
        return $result;
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $loaded = $doc->loadHTML($html);
    libxml_clear_errors();
    if (!$loaded) {
        return $result;
    }
    $xpath = new DOMXPath($doc);

    $collectNodes = static function (DOMNodeList $nodes, string $attr, string $type) use (&$result, $sourceUrl, $origin): void {
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }
            $value = trim((string) $node->getAttribute($attr));
            if ($value === '') {
                continue;
            }
            if (str_starts_with(strtolower($value), 'http://')) {
                $result['mixed_content'][] = $value;
            }
            $url = cmn_join_url($sourceUrl, $value);
            if ($url === null || !cmn_is_internal_url($url, $origin)) {
                continue;
            }
            $result['targets'][] = [
                'url' => $url,
                'type' => $type,
                'source' => $sourceUrl,
            ];
        }
    };

    $collectNodes($xpath->query('//a[@href]'), 'href', 'anchor');
    $collectNodes($xpath->query('//form[@action]'), 'action', 'form');
    $collectNodes($xpath->query('//script[@src]'), 'src', 'asset_script');
    $collectNodes($xpath->query('//link[@href]'), 'href', 'asset_link');
    $collectNodes($xpath->query('//img[@src]'), 'src', 'asset_image');

    foreach ($xpath->query('//form') as $form) {
        if (!$form instanceof DOMElement) {
            continue;
        }
        $formAction = trim((string) $form->getAttribute('action'));
        $normalizedFormAction = $formAction !== '' ? cmn_join_url($sourceUrl, $formAction) : null;
        foreach ($form->getElementsByTagName('input') as $input) {
            if (!$input instanceof DOMElement) {
                continue;
            }
            $name = strtolower(trim((string) $input->getAttribute('name')));
            if ($name !== 'action') {
                continue;
            }
            $actionValue = trim((string) $input->getAttribute('value'));
            if ($actionValue !== '') {
                $result['form_actions'][] = [
                    'action' => $actionValue,
                    'form_action_url' => $normalizedFormAction ?? '',
                    'source' => $sourceUrl,
                ];
            }
        }
    }

    foreach ($xpath->query('//script') as $scriptNode) {
        if (!$scriptNode instanceof DOMElement) {
            continue;
        }
        $inline = (string) $scriptNode->textContent;
        if ($inline === '') {
            continue;
        }
        if (preg_match_all('/\baction\s*[:=]\s*[\'"]([a-zA-Z0-9_\-:]+)[\'"]/', $inline, $matches)) {
            foreach ($matches[1] as $actionName) {
                $result['ajax_actions'][] = [
                    'action' => (string) $actionName,
                    'source' => $sourceUrl,
                ];
            }
        }
    }

    return $result;
}

function cmn_collect_js_known_routes(string $projectRoot, string $origin, string $baseUrl): array
{
    $routes = [];
    $frontendPath = $projectRoot . '/frontend.js';
    if (!is_file($frontendPath)) {
        return $routes;
    }
    $source = (string) file_get_contents($frontendPath);
    if ($source === '') {
        return $routes;
    }

    // Explicit URL-like literals.
    if (preg_match_all('/([\'"])(\/covermenow-one\/[^\'"]*)\1/', $source, $matches)) {
        foreach ($matches[2] as $rawRoute) {
            $url = cmn_join_url($baseUrl, (string) $rawRoute);
            if ($url !== null && cmn_is_internal_url($url, $origin)) {
                $routes[] = $url;
            }
        }
    }

    // Generic wp-admin/admin-ajax/admin-post routes.
    $common = [
        '/wp-admin/',
        '/wp-admin/admin-ajax.php',
        '/wp-admin/admin-post.php',
        '/covermenow-one/',
    ];
    foreach ($common as $path) {
        $url = cmn_join_url($baseUrl, $path);
        if ($url !== null) {
            $routes[] = $url;
        }
    }

    return array_values(array_unique($routes));
}

function cmn_setup_role_context(
    string $role,
    array $authConfig,
    string $baseUrl,
    int $timeoutSeconds,
    string $resolveHost = '',
    string $resolveIp = ''
): array
{
    $ctx = [
        'role' => $role,
        'available' => $role === 'guest',
        'auth_method' => $role === 'guest' ? 'guest' : 'none',
        'cookie_file' => '',
        'cookie_header' => '',
        'resolve_host' => $resolveHost,
        'resolve_ip' => $resolveIp,
        'note' => '',
    ];

    if ($role === 'guest') {
        return $ctx;
    }

    $cfg = $authConfig[$role] ?? null;
    if (!is_array($cfg)) {
        $ctx['note'] = 'No auth config for role';
        return $ctx;
    }

    if (!empty($cfg['cookie_file']) && is_string($cfg['cookie_file']) && is_file($cfg['cookie_file'])) {
        $ctx['cookie_file'] = $cfg['cookie_file'];
        $ctx['available'] = true;
        $ctx['auth_method'] = 'cookie_file';
        return $ctx;
    }

    if (!empty($cfg['cookie_header']) && is_string($cfg['cookie_header'])) {
        $ctx['cookie_header'] = $cfg['cookie_header'];
        $ctx['available'] = true;
        $ctx['auth_method'] = 'cookie_header';
        return $ctx;
    }

    $username = isset($cfg['username']) && is_string($cfg['username']) ? trim($cfg['username']) : '';
    $password = isset($cfg['password']) && is_string($cfg['password']) ? (string) $cfg['password'] : '';
    if ($username === '' || $password === '') {
        $ctx['note'] = 'No cookie or username/password in auth config';
        return $ctx;
    }

    $loginUrl = isset($cfg['login_url']) && is_string($cfg['login_url']) && $cfg['login_url'] !== ''
        ? $cfg['login_url']
        : rtrim($baseUrl, '/') . '/wp-login.php';
    $redirectTo = isset($cfg['redirect_to']) && is_string($cfg['redirect_to']) && $cfg['redirect_to'] !== ''
        ? $cfg['redirect_to']
        : rtrim($baseUrl, '/') . '/wp-admin/';

    $tempCookie = tempnam(sys_get_temp_dir(), 'cmn_link_audit_cookie_');
    if ($tempCookie === false) {
        $ctx['note'] = 'Failed to allocate temp cookie jar';
        return $ctx;
    }

    // Prime login page for testcookie/session values.
    $prime = cmn_http_request($loginUrl, 'GET', [
        'cookie_file' => $tempCookie,
        'resolve_host' => $resolveHost,
        'resolve_ip' => $resolveIp,
    ], $timeoutSeconds);
    if (!$prime['ok']) {
        @unlink($tempCookie);
        $ctx['note'] = 'Failed to load wp-login.php: ' . $prime['error'];
        return $ctx;
    }

    $loginResp = cmn_http_request($loginUrl, 'POST', [
        'cookie_file' => $tempCookie,
        'resolve_host' => $resolveHost,
        'resolve_ip' => $resolveIp,
    ], $timeoutSeconds, true, [
        'log' => $username,
        'pwd' => $password,
        'wp-submit' => 'Log In',
        'redirect_to' => $redirectTo,
        'testcookie' => '1',
    ]);
    if (!$loginResp['ok']) {
        @unlink($tempCookie);
        $ctx['note'] = 'Login request failed: ' . ($loginResp['error'] !== '' ? (string) $loginResp['error'] : 'unknown');
        return $ctx;
    }

    $finalUrl = (string) ($loginResp['final_url'] ?? '');
    $cookieText = (string) file_get_contents($tempCookie);
    $hasWpLoggedInCookie = stripos($cookieText, 'wordpress_logged_in_') !== false;
    if (!$hasWpLoggedInCookie && stripos($finalUrl, 'wp-admin') === false) {
        @unlink($tempCookie);
        $ctx['note'] = 'Login did not establish authenticated session';
        return $ctx;
    }

    $ctx['cookie_file'] = $tempCookie;
    $ctx['available'] = true;
    $ctx['auth_method'] = 'wp-login';
    return $ctx;
}

function cmn_write_csv(string $path, array $rows): void
{
    $fh = fopen($path, 'wb');
    if ($fh === false) {
        throw new RuntimeException("Failed to open CSV for write: {$path}");
    }
    if ($rows === []) {
        fputcsv($fh, ['role', 'source_url', 'target_url', 'target_type', 'status', 'final_url', 'title', 'unexpected_login_redirect', 'error']);
        fclose($fh);
        return;
    }
    fputcsv($fh, array_keys($rows[0]));
    foreach ($rows as $row) {
        fputcsv($fh, $row);
    }
    fclose($fh);
}

function cmn_compact_preview(string $body, int $max = 120): string
{
    $body = preg_replace('/\s+/', ' ', $body);
    if (!is_string($body)) {
        return '';
    }
    $body = trim($body);
    if (strlen($body) <= $max) {
        return $body;
    }
    return substr($body, 0, $max);
}

function cmn_status_bucket(int $status): string
{
    if ($status >= 500) {
        return '5xx';
    }
    if ($status >= 400) {
        return '4xx';
    }
    if ($status >= 300) {
        return '3xx';
    }
    if ($status >= 200) {
        return '2xx';
    }
    if ($status > 0) {
        return '1xx';
    }
    return '0xx';
}

function cmn_main(array $argv): int
{
    $opts = cmn_parse_cli($argv);
    if ($opts['help']) {
        cmn_link_audit_usage();
        return 0;
    }

    $baseUrl = rtrim((string) $opts['base-url'], '/');
    $resolveIp = trim((string) ($opts['resolve-ip'] ?? ''));
    $baseUrlParts = parse_url($baseUrl);
    $resolveHost = is_array($baseUrlParts) ? (string) ($baseUrlParts['host'] ?? '') : '';
    $portalPath = (string) $opts['portal-path'];
    if (!str_starts_with($portalPath, '/')) {
        $portalPath = '/' . $portalPath;
    }
    $origin = cmn_base_origin($baseUrl);
    $roles = is_array($opts['roles']) ? $opts['roles'] : CMN_LINK_AUDIT_DEFAULT_ROLES;
    $outputDir = (string) $opts['output-dir'];
    $rateMs = (int) $opts['rate-ms'];
    $timeout = (int) $opts['timeout'];
    $maxUrlsPerRole = (int) $opts['max-urls-per-role'];
    $extraSeeds = is_array($opts['seed-url']) ? $opts['seed-url'] : [];

    if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
        throw new RuntimeException("Failed to create output dir: {$outputDir}");
    }

    $authConfig = cmn_load_auth_config(is_string($opts['auth-config']) ? $opts['auth-config'] : null);

    $defaultSeeds = [
        $portalPath,
        $portalPath . '?view=dashboard',
        $portalPath . '?view=support',
        $portalPath . '?view=bookings',
        '/wp-admin/',
        '/wp-admin/admin.php?page=covermenowone-one',
        '/wp-admin/admin.php?page=covermenowone-one&view=system-health',
        '/wp-admin/admin.php?page=covermenowone-one&view=match',
    ];

    foreach ($extraSeeds as $seed) {
        $defaultSeeds[] = (string) $seed;
    }
    $defaultSeeds = array_values(array_unique($defaultSeeds));

    $timestamp = gmdate('Ymd-His');
    $csvPath = rtrim($outputDir, '/') . "/link-audit-{$timestamp}.csv";
    $jsonPath = rtrim($outputDir, '/') . "/link-audit-{$timestamp}.json";
    $summaryPath = rtrim($outputDir, '/') . "/link-audit-{$timestamp}.md";

    $allRows = [];
    $allTargetsByRole = [];
    $allFormActions = [];
    $allAjaxActions = [];
    $mixedContentRows = [];
    $roleNotes = [];
    $tempCookieFiles = [];

    $localJsRoutes = cmn_collect_js_known_routes(dirname(__DIR__), $origin, $baseUrl);

    foreach ($roles as $role) {
        $role = trim((string) $role);
        if ($role === '') {
            continue;
        }
        $ctx = cmn_setup_role_context($role, $authConfig, $baseUrl, $timeout, $resolveHost, $resolveIp);
        if (!empty($ctx['cookie_file']) && str_starts_with((string) $ctx['cookie_file'], sys_get_temp_dir())) {
            $tempCookieFiles[] = (string) $ctx['cookie_file'];
        }
        if (!$ctx['available']) {
            $roleNotes[$role] = $ctx['note'] !== '' ? $ctx['note'] : 'Role auth unavailable';
            continue;
        }

        $seedUrls = [];
        foreach ($defaultSeeds as $seed) {
            $url = cmn_join_url($baseUrl . '/', (string) $seed);
            if ($url !== null && cmn_is_internal_url($url, $origin)) {
                $seedUrls[] = $url;
            }
        }
        foreach ($localJsRoutes as $url) {
            $seedUrls[] = $url;
        }
        $seedUrls = array_values(array_unique($seedUrls));

        $targetMap = [];

        foreach ($seedUrls as $seedUrl) {
            $resp = cmn_http_request($seedUrl, 'GET', $ctx, $timeout);
            $title = ($resp['content_type'] !== '' && str_contains(strtolower($resp['content_type']), 'html'))
                ? cmn_extract_title((string) $resp['body'])
                : '';

            $unexpectedLogin = 0;
            $finalUrl = (string) $resp['final_url'];
            if (str_contains($finalUrl, '/wp-login.php') && !str_contains($seedUrl, '/wp-login.php')) {
                if ($role !== 'guest') {
                    $unexpectedLogin = 1;
                } else {
                    $path = (string) (parse_url($seedUrl, PHP_URL_PATH) ?? '');
                    if (!str_starts_with($path, '/wp-admin')) {
                        $unexpectedLogin = 1;
                    }
                }
            }

            $allRows[] = [
                'role' => $role,
                'source_url' => $seedUrl,
                'target_url' => $seedUrl,
                'target_type' => 'seed',
                'status' => (string) $resp['status'],
                'final_url' => $finalUrl,
                'title' => $title,
                'unexpected_login_redirect' => (string) $unexpectedLogin,
                'error' => (string) $resp['error'],
            ];

            if ($resp['status'] >= 200 && $resp['status'] < 400 && str_contains(strtolower((string) $resp['content_type']), 'html')) {
                $extracted = cmn_extract_targets_from_html((string) $resp['body'], $seedUrl, $origin);
                foreach ($extracted['targets'] as $item) {
                    $k = $item['url'] . '|' . $item['type'] . '|' . $item['source'];
                    $targetMap[$k] = $item;
                }
                foreach ($extracted['form_actions'] as $item) {
                    $allFormActions[$role][] = $item;
                }
                foreach ($extracted['ajax_actions'] as $item) {
                    $allAjaxActions[$role][] = $item;
                }
                foreach ($extracted['mixed_content'] as $mixedUrl) {
                    $mixedContentRows[] = [
                        'role' => $role,
                        'source_url' => $seedUrl,
                        'asset_url' => (string) $mixedUrl,
                    ];
                }
            }

            usleep($rateMs * 1000);
        }

        // Add JS-known routes as synthetic targets (once per role).
        foreach ($localJsRoutes as $jsUrl) {
            $k = $jsUrl . '|js_route|' . $baseUrl . $portalPath;
            if (!isset($targetMap[$k])) {
                $targetMap[$k] = [
                    'url' => $jsUrl,
                    'type' => 'js_route',
                    'source' => $baseUrl . $portalPath,
                ];
            }
        }

        $targets = array_values($targetMap);
        $targets = array_slice($targets, 0, $maxUrlsPerRole);
        $allTargetsByRole[$role] = $targets;

        foreach ($targets as $target) {
            $targetUrl = (string) $target['url'];
            $targetType = (string) $target['type'];
            $sourceUrl = (string) $target['source'];

            $method = str_starts_with($targetType, 'asset_') ? 'HEAD' : 'GET';
            $resp = cmn_http_request($targetUrl, $method, $ctx, $timeout);
            if ($method === 'HEAD' && $resp['status'] === 405) {
                $resp = cmn_http_request($targetUrl, 'GET', $ctx, $timeout);
            }
            $title = '';
            if ($resp['status'] >= 200 && $resp['status'] < 400 && str_contains(strtolower((string) $resp['content_type']), 'html')) {
                $title = cmn_extract_title((string) $resp['body']);
            }

            $unexpectedLogin = 0;
            if (str_contains((string) $resp['final_url'], '/wp-login.php') && !str_contains($targetUrl, '/wp-login.php')) {
                if ($role !== 'guest') {
                    $unexpectedLogin = 1;
                } else {
                    $path = (string) (parse_url($targetUrl, PHP_URL_PATH) ?? '');
                    if (!str_starts_with($path, '/wp-admin')) {
                        $unexpectedLogin = 1;
                    }
                }
            }

            $allRows[] = [
                'role' => $role,
                'source_url' => $sourceUrl,
                'target_url' => $targetUrl,
                'target_type' => $targetType,
                'status' => (string) $resp['status'],
                'final_url' => (string) $resp['final_url'],
                'title' => $title,
                'unexpected_login_redirect' => (string) $unexpectedLogin,
                'error' => (string) $resp['error'],
            ];

            usleep($rateMs * 1000);
        }
    }

    $statusGroups = [];
    $failures = [];
    $redirectPairs = [];
    $brokenAssets = [];
    $unexpectedLogins = [];
    foreach ($allRows as $row) {
        $status = (int) ($row['status'] ?? 0);
        $bucket = cmn_status_bucket($status);
        $statusGroups[$bucket] = ($statusGroups[$bucket] ?? 0) + 1;

        if ($status >= 400 || $status === 0) {
            $failures[] = $row;
        }
        $sourceTarget = (string) ($row['target_url'] ?? '');
        $final = (string) ($row['final_url'] ?? '');
        if ($sourceTarget !== '' && $final !== '' && $sourceTarget !== $final) {
            $pair = $sourceTarget . ' -> ' . $final;
            $redirectPairs[$pair] = ($redirectPairs[$pair] ?? 0) + 1;
        }
        $type = (string) ($row['target_type'] ?? '');
        if (str_starts_with($type, 'asset_') && ($status >= 400 || $status === 0)) {
            $brokenAssets[] = $row;
        }
        if ((int) ($row['unexpected_login_redirect'] ?? 0) === 1) {
            $unexpectedLogins[] = $row;
        }
    }
    arsort($redirectPairs);

    $formActionSummary = [];
    foreach ($allFormActions as $role => $items) {
        foreach ($items as $item) {
            $action = (string) ($item['action'] ?? '');
            if ($action === '') {
                continue;
            }
            $formActionSummary[$action] = ($formActionSummary[$action] ?? 0) + 1;
        }
    }
    ksort($formActionSummary);

    $ajaxActionSummary = [];
    foreach ($allAjaxActions as $role => $items) {
        foreach ($items as $item) {
            $action = (string) ($item['action'] ?? '');
            if ($action === '') {
                continue;
            }
            $ajaxActionSummary[$action] = ($ajaxActionSummary[$action] ?? 0) + 1;
        }
    }
    ksort($ajaxActionSummary);

    cmn_write_csv($csvPath, $allRows);

    $jsonPayload = [
        'generated_at_utc' => gmdate('c'),
        'base_url' => $baseUrl,
        'roles' => $roles,
        'role_notes' => $roleNotes,
        'status_groups' => $statusGroups,
        'total_rows' => count($allRows),
        'rows' => $allRows,
        'mixed_content' => $mixedContentRows,
        'form_actions' => $allFormActions,
        'ajax_actions' => $allAjaxActions,
        'form_action_summary' => $formActionSummary,
        'ajax_action_summary' => $ajaxActionSummary,
        'top_redirect_pairs' => array_slice($redirectPairs, 0, 20, true),
        'broken_assets' => $brokenAssets,
    ];
    file_put_contents($jsonPath, json_encode($jsonPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

    $lines = [];
    $lines[] = '# Link Audit Summary';
    $lines[] = '';
    $lines[] = '- Generated: ' . gmdate('c');
    $lines[] = '- Base URL: ' . $baseUrl;
    $lines[] = '- Roles requested: ' . implode(', ', $roles);
    $lines[] = '- Total links/actions checked: ' . count($allRows);
    $lines[] = '- CSV: `' . basename($csvPath) . '`';
    $lines[] = '- JSON: `' . basename($jsonPath) . '`';
    $lines[] = '';

    if ($roleNotes !== []) {
        $lines[] = '## Role Coverage Gaps';
        foreach ($roleNotes as $role => $note) {
            $lines[] = '- ' . $role . ': ' . $note;
        }
        $lines[] = '';
    }

    $lines[] = '## Status Group Counts';
    foreach ($statusGroups as $bucket => $count) {
        $lines[] = '- ' . $bucket . ': ' . $count;
    }
    $lines[] = '';

    $lines[] = '## Failures by Status';
    if ($failures === []) {
        $lines[] = '- None';
    } else {
        $byStatus = [];
        foreach ($failures as $f) {
            $s = (string) ($f['status'] ?? '0');
            $byStatus[$s] = ($byStatus[$s] ?? 0) + 1;
        }
        ksort($byStatus);
        foreach ($byStatus as $status => $count) {
            $lines[] = '- ' . $status . ': ' . $count;
        }
    }
    $lines[] = '';

    $lines[] = '## Top Redirect Pairs';
    if ($redirectPairs === []) {
        $lines[] = '- None';
    } else {
        $i = 0;
        foreach ($redirectPairs as $pair => $count) {
            $lines[] = '- ' . $count . 'x ' . $pair;
            $i++;
            if ($i >= 10) {
                break;
            }
        }
    }
    $lines[] = '';

    $lines[] = '## Unexpected Login Redirects';
    if ($unexpectedLogins === []) {
        $lines[] = '- None';
    } else {
        foreach (array_slice($unexpectedLogins, 0, 20) as $row) {
            $lines[] = '- [' . $row['role'] . '] ' . $row['target_url'] . ' -> ' . $row['final_url'];
        }
        if (count($unexpectedLogins) > 20) {
            $lines[] = '- ... and ' . (count($unexpectedLogins) - 20) . ' more';
        }
    }
    $lines[] = '';

    $lines[] = '## Broken Asset URLs';
    if ($brokenAssets === []) {
        $lines[] = '- None';
    } else {
        foreach (array_slice($brokenAssets, 0, 20) as $assetRow) {
            $lines[] = '- [' . $assetRow['status'] . '] ' . $assetRow['target_url'] . ' (source: ' . $assetRow['source_url'] . ')';
        }
        if (count($brokenAssets) > 20) {
            $lines[] = '- ... and ' . (count($brokenAssets) - 20) . ' more';
        }
    }
    $lines[] = '';

    $lines[] = '## Mixed Content URLs';
    if ($mixedContentRows === []) {
        $lines[] = '- None';
    } else {
        foreach (array_slice($mixedContentRows, 0, 20) as $mixed) {
            $lines[] = '- [' . $mixed['role'] . '] ' . $mixed['asset_url'] . ' (source: ' . $mixed['source_url'] . ')';
        }
        if (count($mixedContentRows) > 20) {
            $lines[] = '- ... and ' . (count($mixedContentRows) - 20) . ' more';
        }
    }
    $lines[] = '';

    $lines[] = '## UI Action Names Discovered';
    $lines[] = '- Form `action` values: ' . count($formActionSummary);
    foreach (array_slice(array_keys($formActionSummary), 0, 50) as $actionName) {
        $lines[] = '  - ' . $actionName . ' (' . $formActionSummary[$actionName] . ')';
    }
    if (count($formActionSummary) > 50) {
        $lines[] = '  - ... and ' . (count($formActionSummary) - 50) . ' more';
    }
    $lines[] = '- Inline JS action names: ' . count($ajaxActionSummary);
    foreach (array_slice(array_keys($ajaxActionSummary), 0, 50) as $actionName) {
        $lines[] = '  - ' . $actionName . ' (' . $ajaxActionSummary[$actionName] . ')';
    }
    if (count($ajaxActionSummary) > 50) {
        $lines[] = '  - ... and ' . (count($ajaxActionSummary) - 50) . ' more';
    }
    $lines[] = '';

    file_put_contents($summaryPath, implode(PHP_EOL, $lines) . PHP_EOL);

    foreach ($tempCookieFiles as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    fwrite(STDOUT, "Link audit complete.\n");
    fwrite(STDOUT, "CSV: {$csvPath}\n");
    fwrite(STDOUT, "JSON: {$jsonPath}\n");
    fwrite(STDOUT, "Summary: {$summaryPath}\n");
    fwrite(STDOUT, "Rows: " . count($allRows) . "\n");
    fwrite(STDOUT, "Failures: " . count($failures) . "\n");

    return 0;
}

try {
    exit(cmn_main($argv));
} catch (Throwable $e) {
    fwrite(STDERR, '[link_audit] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
