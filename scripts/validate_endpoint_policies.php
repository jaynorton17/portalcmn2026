#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$manifestFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');
$allowlistFile = $argv[2] ?? ($root . '/docs/endpoint-policy-allowlist.json');
$pluginFile = $root . '/covermenowone-one.php';

if (!is_file($manifestFile)) {
    fwrite(STDERR, "Manifest not found: {$manifestFile}\n");
    exit(1);
}
if (!is_file($pluginFile)) {
    fwrite(STDERR, "Plugin file not found: {$pluginFile}\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestFile), true);
if (!is_array($manifest) || !isset($manifest['entrypoints']) || !is_array($manifest['entrypoints'])) {
    fwrite(STDERR, "Invalid manifest structure: {$manifestFile}\n");
    exit(1);
}

$source = (string) file_get_contents($pluginFile);
if ($source === '') {
    fwrite(STDERR, "Plugin file is empty: {$pluginFile}\n");
    exit(1);
}

$allowlist = [];
if (is_file($allowlistFile)) {
    $decoded = json_decode((string) file_get_contents($allowlistFile), true);
    if (is_array($decoded)) {
        $allowlist = $decoded;
    }
}

$lineForOffset = static function (int $offset) use ($source): int {
    return substr_count(substr($source, 0, max(0, $offset)), "\n") + 1;
};

$extractHandler = static function (string $callback): string {
    $callback = trim($callback);
    if (preg_match('/^\[\s*\$this\s*,\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*\]$/', $callback, $m)) {
        return (string) $m[1];
    }
    if (preg_match('/^array\s*\(\s*\$this\s*,\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*\)$/i', $callback, $m)) {
        return (string) $m[1];
    }
    if (preg_match('/^\[\s*[\'"]([a-zA-Z0-9_\\\\:]+)[\'"]\s*,\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*\]$/', $callback, $m)) {
        return (string) ($m[1] . '::' . $m[2]);
    }
    if (preg_match('/^array\s*\(\s*[\'"]([a-zA-Z0-9_\\\\:]+)[\'"]\s*,\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*\)$/i', $callback, $m)) {
        return (string) ($m[1] . '::' . $m[2]);
    }
    if (preg_match('/^[\'"]([a-zA-Z0-9_]+)[\'"]$/', $callback, $m)) {
        return (string) $m[1];
    }
    if (preg_match('/^[a-zA-Z0-9_\\\\:]+$/', $callback)) {
        return $callback;
    }
    return '{unknown}';
};

$parseFunctionBlocks = static function (string $text): array {
    $blocks = [];
    if (!preg_match_all('/^\s*(?:public|protected|private)?\s*(?:static\s+)?function\s+([a-zA-Z0-9_]+)\s*\(/m', $text, $matches, PREG_OFFSET_CAPTURE)) {
        return $blocks;
    }

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) {
        $name = (string) $matches[1][$i][0];
        $startOffset = (int) $matches[0][$i][1];
        $endOffset = ($i + 1 < $count) ? (int) $matches[0][$i + 1][1] : strlen($text);
        $body = (string) substr($text, $startOffset, max(0, $endOffset - $startOffset));
        $line = substr_count(substr($text, 0, $startOffset), "\n") + 1;
        if (!isset($blocks[$name])) {
            $blocks[$name] = ['body' => $body, 'line' => $line];
        }
    }
    return $blocks;
};

$functionBlocks = $parseFunctionBlocks($source);

$registrationMap = [];
$actionPattern = '/add_action\s*\(\s*(["\'])(?<hook>[^"\']+)\1\s*,\s*(?<callback>\[[^\]]+\]|array\s*\([^\)]*\)|["\'][^"\']+["\']|[a-zA-Z0-9_\\\\:]+)\s*(?:,\s*(?<priority>\d+))?/ms';
if (preg_match_all($actionPattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
    foreach ($matches as $match) {
        $hook = (string) ($match['hook'][0] ?? '');
        $callback = (string) ($match['callback'][0] ?? '');
        if ($hook === '') {
            continue;
        }
        $handler = $extractHandler($callback);
        $line = $lineForOffset((int) ($match[0][1] ?? 0));
        $registrationMap[$hook . '|' . $handler] = $line;
    }
}

$toBool = static function ($value): bool {
    if (is_bool($value)) {
        return $value;
    }
    if (is_int($value) || is_float($value)) {
        return ((int) $value) !== 0;
    }
    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        return in_array($normalized, ['1', 'true', 'yes', 'y'], true);
    }
    return false;
};

$detectWritesState = static function (string $body): bool {
    if ($body === '') {
        return false;
    }
    $patterns = [
        '/->insert\s*\(/',
        '/->update\s*\(/',
        '/->delete\s*\(/',
        '/->replace\s*\(/',
        '/wp_insert_post\s*\(/',
        '/wp_update_post\s*\(/',
        '/wp_delete_post\s*\(/',
        '/update_option\s*\(/',
        '/add_option\s*\(/',
        '/delete_option\s*\(/',
        '/update_post_meta\s*\(/',
        '/add_post_meta\s*\(/',
        '/delete_post_meta\s*\(/',
        '/update_user_meta\s*\(/',
        '/add_user_meta\s*\(/',
        '/delete_user_meta\s*\(/',
        '/set_transient\s*\(/',
        '/delete_transient\s*\(/',
        '/wp_set_password\s*\(/',
        '/wp_send_json_success\s*\(\s*\[\s*[\'"]saved[\'"]/',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $body)) {
            return true;
        }
    }
    return false;
};

$detectNonceMode = static function (string $body): string {
    if ($body === '') {
        return 'none';
    }
    if (preg_match('/[\'"]nonce_mode[\'"]\s*=>\s*[\'"]required[\'"]/', $body)) {
        return 'required';
    }
    if (preg_match('/check_(?:ajax|admin)_referer\s*\(/', $body)) {
        return 'required';
    }
    if (preg_match('/cmn_require_nonce_or_fail\s*\(/', $body)) {
        return 'required';
    }
    if (preg_match('/[\'"]nonce_mode[\'"]\s*=>\s*[\'"]optional[\'"]/', $body)) {
        return 'optional';
    }
    if (preg_match('/wp_verify_nonce\s*\(/', $body)) {
        return 'optional';
    }
    return 'none';
};

$detectAbility = static function (string $body): ?string {
    if ($body === '') {
        return null;
    }
    if (preg_match('/[\'"]ability_required[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $body, $m)) {
        return trim((string) $m[1]);
    }
    if (preg_match('/cmn_policy_require_ability\s*\(\s*[\'"]([^\'"]+)[\'"]/', $body, $m)) {
        return trim((string) $m[1]);
    }
    if (preg_match('/cmn_user_has_ability\s*\(\s*[\'"]([^\'"]+)[\'"]/', $body, $m)) {
        return trim((string) $m[1]);
    }
    return null;
};

$isStaffEndpoint = static function (string $hook, string $handler): bool {
    $target = strtolower($hook . ' ' . $handler);
    if (preg_match('/(^|_)cmn_staff_|(^|_)staff(_|$)|handle_staff_|render_staff_/', $target)) {
        return true;
    }
    if (preg_match('/cmn_system_health_|cmn_match_|cmn_email_log_resend/', $target)) {
        return true;
    }
    return false;
};

$isPartnerAdminEndpoint = static function (string $hook, string $handler): bool {
    $target = strtolower($hook . ' ' . $handler);
    return (bool) preg_match('/school_partner_admin|partner_admin|partner_programme_(recalculate|set_tier|adjust_days|create_credit|void_credit)/', $target);
};

$isPolicyEndpointType = static function (string $type): bool {
    return in_array($type, ['wp_ajax', 'wp_ajax_nopriv', 'admin_post', 'admin_post_nopriv'], true);
};

$isNoprivWriteAllowlisted = static function (string $type, string $hook, string $handler) use ($allowlist): bool {
    $composite = strtolower($type . '|' . $hook . '|' . $handler);
    $hookKey = strtolower($hook);
    $handlerKey = strtolower($handler);

    $compositeList = array_map('strtolower', (array) ($allowlist['nopriv_write_entries'] ?? []));
    if (in_array($composite, $compositeList, true)) {
        return true;
    }

    $hookList = array_map('strtolower', (array) ($allowlist['nopriv_write_hooks'] ?? []));
    if (in_array($hookKey, $hookList, true)) {
        return true;
    }

    $handlerList = array_map('strtolower', (array) ($allowlist['nopriv_write_handlers'] ?? []));
    if (in_array($handlerKey, $handlerList, true)) {
        return true;
    }

    return false;
};

$isPublicTokenEndpoint = static function (string $type, string $hook, string $handler, string $body): bool {
    if (!in_array($type, ['wp_ajax_nopriv', 'admin_post_nopriv'], true)) {
        return false;
    }

    $target = strtolower($hook . ' ' . $handler);
    if (preg_match('/candidate_response|marketing_runner|thread_|token|unsubscribe|automation_runner|cv_converter/', $target)) {
        return true;
    }

    if ($body !== '' && preg_match('/token|token_hash|offer_token|thread_token|hash_offer_response_token|get_livechat_thread_by_token|validate_.*token/i', $body)) {
        return true;
    }

    return false;
};

$hasRateLimit = static function (string $body): bool {
    if ($body === '') {
        return false;
    }

    if (preg_match('/cmn_rate_limit\s*\(/', $body)) {
        return true;
    }
    if (preg_match('/is_livechat_rate_limited\s*\(/', $body)) {
        return true;
    }
    if (preg_match('/enforce_school_partner_admin_action_throttle\s*\(/', $body)) {
        return true;
    }

    return false;
};

$violations = [];
$entryCount = 0;

foreach ($manifest['entrypoints'] as $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $entryCount++;

    $type = (string) ($entry['type'] ?? $entry['entry_type'] ?? '');
    $hook = (string) ($entry['hook'] ?? $entry['hook_name'] ?? '');
    $handler = (string) ($entry['handler'] ?? $entry['handler_function'] ?? '');
    if ($type === '' || $hook === '') {
        continue;
    }
    if (!$isPolicyEndpointType($type)) {
        continue;
    }

    $nopriv = array_key_exists('nopriv', $entry)
        ? $toBool($entry['nopriv'])
        : (strpos($type, 'nopriv') !== false || strpos($hook, '_nopriv_') !== false);

    $handlerLookup = $handler;
    if (strpos($handlerLookup, '::') !== false) {
        $parts = explode('::', $handlerLookup);
        $handlerLookup = (string) end($parts);
    }

    $body = '';
    $functionLine = 0;
    if ($handlerLookup !== '' && isset($functionBlocks[$handlerLookup])) {
        $body = (string) $functionBlocks[$handlerLookup]['body'];
        $functionLine = (int) ($functionBlocks[$handlerLookup]['line'] ?? 0);
    }

    $file = (string) ($entry['file'] ?? 'covermenowone-one.php');
    $line = (int) ($entry['line'] ?? 0);
    if ($line <= 0) {
        $line = (int) ($registrationMap[$hook . '|' . $handler] ?? $functionLine);
    }

    $writesState = array_key_exists('writes_state', $entry)
        ? $toBool($entry['writes_state'])
        : $detectWritesState($body);

    $nonceMode = strtolower(trim((string) ($entry['nonce_mode'] ?? '')));
    if ($nonceMode === '') {
        $nonceMode = $detectNonceMode($body);
    }
    if (!in_array($nonceMode, ['required', 'optional', 'none'], true)) {
        $nonceMode = 'none';
    }

    $abilityRequired = $entry['ability_required'] ?? null;
    if (($abilityRequired === null || $abilityRequired === '') && $body !== '') {
        $abilityRequired = $detectAbility($body);
    }
    $abilityRequired = is_string($abilityRequired) && $abilityRequired !== '' ? $abilityRequired : null;

    $isNoprivType = in_array($type, ['wp_ajax_nopriv', 'admin_post_nopriv'], true) || $nopriv;
    $isNoprivAllowlisted = ($isNoprivType && $writesState && $isNoprivWriteAllowlisted($type, $hook, $handler));

    if ($writesState && $nonceMode !== 'required') {
        $violations[] = [
            'rule' => 'write_requires_nonce_required',
            'hook' => $hook,
            'file' => $file,
            'line' => $line,
            'message' => "writes_state=true but nonce_mode={$nonceMode}",
        ];
    }

    if ($isNoprivType && $writesState && !$isNoprivWriteAllowlisted($type, $hook, $handler)) {
        $violations[] = [
            'rule' => 'nopriv_write_not_allowlisted',
            'hook' => $hook,
            'file' => $file,
            'line' => $line,
            'message' => 'nopriv writes_state=true and not allowlisted',
        ];
    }

    if ($isStaffEndpoint($hook, $handler) && $abilityRequired !== 'portal.staff.view') {
        $violations[] = [
            'rule' => 'staff_endpoint_missing_staff_ability',
            'hook' => $hook,
            'file' => $file,
            'line' => $line,
            'message' => "ability_required=" . ($abilityRequired ?? 'null'),
        ];
    }

    if ($isPartnerAdminEndpoint($hook, $handler) && $abilityRequired !== 'partner.admin.mutate') {
        $violations[] = [
            'rule' => 'partner_admin_missing_partner_admin_mutate',
            'hook' => $hook,
            'file' => $file,
            'line' => $line,
            'message' => "ability_required=" . ($abilityRequired ?? 'null'),
        ];
    }

    if ($isPublicTokenEndpoint($type, $hook, $handler, $body) && !$hasRateLimit($body)) {
        $violations[] = [
            'rule' => 'public_token_endpoint_missing_rate_limit',
            'hook' => $hook,
            'file' => $file,
            'line' => $line,
            'message' => 'public token endpoint missing rate limiting guard',
        ];
    }
}

echo "Endpoint policy validation file: {$manifestFile}\n";
echo "Entrypoints scanned: {$entryCount}\n";
echo "Violations: " . count($violations) . "\n";

if (!$violations) {
    echo "PASS: endpoint policies validated.\n";
    exit(0);
}

foreach ($violations as $v) {
    echo sprintf(
        "[%s] %s %s:%d :: %s\n",
        (string) $v['rule'],
        (string) $v['hook'],
        (string) $v['file'],
        (int) $v['line'],
        (string) $v['message']
    );
}

exit(1);
