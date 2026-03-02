#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$manifestFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');
$pluginFile = $root . '/covermenowone-one.php';

if (!is_file($manifestFile)) {
    fwrite(STDERR, "Manifest not found: {$manifestFile}\nRun scripts/generate-endpoint-manifest.php first.\n");
    exit(1);
}
if (!is_file($pluginFile)) {
    fwrite(STDERR, "Plugin file not found: {$pluginFile}\n");
    exit(1);
}

$manifestRaw = (string) file_get_contents($manifestFile);
$manifest = json_decode($manifestRaw, true);
if (!is_array($manifest) || !isset($manifest['entrypoints']) || !is_array($manifest['entrypoints'])) {
    fwrite(STDERR, "Invalid manifest format: {$manifestFile}\n");
    exit(1);
}

$source = (string) file_get_contents($pluginFile);
if ($source === '') {
    fwrite(STDERR, "Plugin file is empty: {$pluginFile}\n");
    exit(1);
}

$parseMethods = static function (string $text): array {
    $methods = [];
    if (!preg_match_all('/^\s*(?:public|protected|private)\s+function\s+([a-zA-Z0-9_]+)\s*\(/m', $text, $matches, PREG_OFFSET_CAPTURE)) {
        return $methods;
    }

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) {
        $methodName = (string) $matches[1][$i][0];
        $startOffset = (int) $matches[0][$i][1];
        $nextOffset = ($i + 1 < $count) ? (int) $matches[0][$i + 1][1] : strlen($text);
        $body = (string) substr($text, $startOffset, max(0, $nextOffset - $startOffset));

        $line = substr_count(substr($text, 0, $startOffset), "\n") + 1;
        $methods[$methodName] = [
            'body' => $body,
            'line' => $line,
        ];
    }
    return $methods;
};

$methods = $parseMethods($source);

$hasWrites = static function (string $body): bool {
    $patterns = [
        '/->insert\s*\(/',
        '/->update\s*\(/',
        '/->delete\s*\(/',
        '/->replace\s*\(/',
        '/update_post_meta\s*\(/',
        '/add_post_meta\s*\(/',
        '/delete_post_meta\s*\(/',
        '/wp_insert_post\s*\(/',
        '/wp_update_post\s*\(/',
        '/wp_delete_post\s*\(/',
        '/update_option\s*\(/',
        '/add_option\s*\(/',
        '/delete_option\s*\(/',
        '/update_user_meta\s*\(/',
        '/add_user_meta\s*\(/',
        '/delete_user_meta\s*\(/',
        '/set_transient\s*\(/',
        '/delete_transient\s*\(/',
        '/wp_set_password\s*\(/',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $body)) {
            return true;
        }
    }
    return false;
};

$hasNonce = static function (string $body): bool {
    if (preg_match('/["\']nonce_mode["\']\s*=>\s*["\']required["\']/', $body)) {
        return true;
    }
    if (preg_match('/check_ajax_referer\s*\(/', $body)) {
        return true;
    }
    if (preg_match('/wp_verify_nonce\s*\(/', $body)) {
        return true;
    }
    if (preg_match('/cmn_require_nonce_or_fail\s*\(/', $body)) {
        return true;
    }
    return false;
};

$getAbility = static function (string $body): string {
    if (preg_match('/["\']ability_required["\']\s*=>\s*["\']([^"\']+)["\']/', $body, $m)) {
        return trim((string) $m[1]);
    }
    return '';
};

$hasRateLimit = static function (string $body): bool {
    return (bool) preg_match('/cmn_rate_limit\s*\(|is_livechat_rate_limited\s*\(|enforce_.*_throttle\s*\(/', $body);
};

$isPublicTokenEndpoint = static function (array $entry, string $body): bool {
    if (empty($entry['nopriv'])) {
        return false;
    }
    $target = strtolower((string) ($entry['hook_name'] ?? '') . ' ' . (string) ($entry['handler_function'] ?? ''));
    if (preg_match('/token|candidate_response|offer_response|livechat|automation_runner|set_release_version/', $target)) {
        return true;
    }
    return (bool) preg_match('/token_hash|hash_offer_response_token|get_livechat_thread_by_token|offer_token|runner_token/', $body);
};

$isStaffOnly = static function (array $entry, string $body): bool {
    $target = strtolower((string) ($entry['hook_name'] ?? '') . ' ' . (string) ($entry['handler_function'] ?? ''));
    if (preg_match('/(^|_)staff_|system_health|cmn_match_|marketing_|partner_programme|run_upgrade_runner|render_staff_/', $target)) {
        return true;
    }
    return false;
};

$isPartnerAdminMutation = static function (array $entry, string $body): bool {
    $target = strtolower((string) ($entry['hook_name'] ?? '') . ' ' . (string) ($entry['handler_function'] ?? ''));
    if (preg_match('/school_partner_admin|partner_admin|partner_programme_(recalculate|adjust|set|create|void|mutate|update|delete)/', $target)) {
        return true;
    }
    return (bool) preg_match('/partner\.admin\.mutate/', $body);
};

$violations = [];
foreach ($manifest['entrypoints'] as $entry) {
    if (!is_array($entry)) {
        continue;
    }

    $entryType = (string) ($entry['entry_type'] ?? '');
    $hook = (string) ($entry['hook_name'] ?? '');
    $handler = (string) ($entry['handler_function'] ?? '');

    if ($entryType === '' || $hook === '' || $handler === '') {
        continue;
    }

    $method = $methods[$handler] ?? null;
    if (!is_array($method)) {
        $violations[] = [
            'rule' => 'missing_handler',
            'entry' => $entryType . ':' . $hook . ' -> ' . $handler,
            'message' => 'Handler function not found in covermenowone-one.php',
        ];
        continue;
    }

    $body = (string) ($method['body'] ?? '');
    $line = (int) ($method['line'] ?? 0);
    $ability = $getAbility($body);
    $writesState = $hasWrites($body);
    $nonceRequired = $hasNonce($body);
    $publicToken = $isPublicTokenEndpoint($entry, $body);
    $rateLimited = $hasRateLimit($body);

    if (in_array($entryType, ['ajax', 'admin_post'], true) && $writesState) {
        $writeAllowed = $nonceRequired || ($publicToken && $rateLimited);
        if (!$writeAllowed) {
            $violations[] = [
                'rule' => 'state_write_missing_nonce_or_token_rate_limit',
                'entry' => $entryType . ':' . $hook . ' -> ' . $handler,
                'line' => $line,
                'message' => 'State-changing endpoint missing nonce OR public-token+rate-limit protection.',
            ];
        }
    }

    if ($isStaffOnly($entry, $body) && $ability !== 'portal.staff.view') {
        $violations[] = [
            'rule' => 'staff_only_missing_portal_staff_view',
            'entry' => $entryType . ':' . $hook . ' -> ' . $handler,
            'line' => $line,
            'message' => 'Staff-only handler must require ability portal.staff.view.',
        ];
    }

    if ($isPartnerAdminMutation($entry, $body) && $ability !== 'partner.admin.mutate') {
        $violations[] = [
            'rule' => 'partner_admin_missing_partner_admin_mutate',
            'entry' => $entryType . ':' . $hook . ' -> ' . $handler,
            'line' => $line,
            'message' => 'Partner-admin mutation must require partner.admin.mutate.',
        ];
    }
}

echo "Endpoint policy validation target: {$manifestFile}\n";
echo "Entrypoints scanned: " . count((array) $manifest['entrypoints']) . "\n";

$violationCount = count($violations);
echo "Violations: {$violationCount}\n";
if ($violationCount === 0) {
    echo "Validation passed.\n";
    exit(0);
}

foreach ($violations as $v) {
    $lineText = isset($v['line']) ? (' @line ' . (int) $v['line']) : '';
    echo sprintf(
        "[%s] %s%s :: %s\n",
        (string) ($v['rule'] ?? 'violation'),
        (string) ($v['entry'] ?? '(unknown)'),
        $lineText,
        (string) ($v['message'] ?? '')
    );
}

exit(1);
