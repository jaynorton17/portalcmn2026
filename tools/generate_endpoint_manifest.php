#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$pluginFile = $root . '/covermenowone-one.php';
$outFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');

if (!is_file($pluginFile)) {
    fwrite(STDERR, "Plugin file not found: {$pluginFile}\n");
    exit(1);
}

$source = (string) file_get_contents($pluginFile);
if ($source === '') {
    fwrite(STDERR, "Plugin file is empty: {$pluginFile}\n");
    exit(1);
}
$lines = preg_split('/\R/', $source) ?: [];

$lineForOffset = static function (string $text, int $offset): int {
    $safeOffset = max(0, min(strlen($text), $offset));
    return substr_count(substr($text, 0, $safeOffset), "\n") + 1;
};

$parseMethods = static function (string $text) use ($lineForOffset): array {
    $methods = [];
    if (!preg_match_all('/^\s*(public|protected|private)\s+function\s+([a-zA-Z0-9_]+)\s*\(/m', $text, $matches, PREG_OFFSET_CAPTURE)) {
        return $methods;
    }

    $total = count($matches[0]);
    for ($i = 0; $i < $total; $i++) {
        $signatureOffset = (int) $matches[0][$i][1];
        $name = (string) $matches[2][$i][0];
        $nextOffset = ($i + 1 < $total)
            ? (int) $matches[0][$i + 1][1]
            : strlen($text);

        $body = (string) substr($text, $signatureOffset, max(0, $nextOffset - $signatureOffset));
        $startLine = $lineForOffset($text, $signatureOffset);
        $endLine = $lineForOffset($text, max(0, $nextOffset - 1));

        $methods[$name] = [
            'name' => $name,
            'start_line' => $startLine,
            'end_line' => $endLine,
            'body' => $body,
        ];
    }

    return $methods;
};

$normalizeUnique = static function (array $values): array {
    $out = [];
    foreach ($values as $value) {
        $v = trim((string) $value);
        if ($v === '') {
            continue;
        }
        $out[$v] = $v;
    }
    return array_values($out);
};

$parseMethodPolicy = static function (string $body) use ($normalizeUnique): array {
    $policy = [
        'has_guard_wrapper' => strpos($body, 'cmn_endpoint_guard(') !== false,
        'ability_required' => '',
        'nonce_mode' => 'not_applicable',
        'nonce_action' => '',
        'nonce_field' => '',
        'writes_state' => false,
        'entity_binding' => [],
        'rate_limited' => false,
        'token_validation' => false,
        'staff_guard' => false,
        'partner_guard' => false,
    ];

    if (preg_match('/\'ability_required\'\s*=>\s*\'([^\']*)\'/', $body, $m)) {
        $policy['ability_required'] = trim((string) $m[1]);
    }
    if (preg_match('/\'nonce_mode\'\s*=>\s*\'([^\']+)\'/', $body, $m)) {
        $policy['nonce_mode'] = trim((string) $m[1]);
    }
    if (preg_match('/\'nonce_action\'\s*=>\s*\'([^\']+)\'/', $body, $m)) {
        $policy['nonce_action'] = trim((string) $m[1]);
    }
    if (preg_match('/\'nonce_field\'\s*=>\s*\'([^\']+)\'/', $body, $m)) {
        $policy['nonce_field'] = trim((string) $m[1]);
    }
    if (preg_match('/\'writes_state\'\s*=>\s*(true|false)/i', $body, $m)) {
        $policy['writes_state'] = strtolower((string) $m[1]) === 'true';
    }

    if (preg_match('/check_ajax_referer\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]/i', $body, $m)) {
        $policy['nonce_mode'] = 'required';
        if ($policy['nonce_action'] === '') {
            $policy['nonce_action'] = (string) $m[1];
        }
        if ($policy['nonce_field'] === '') {
            $policy['nonce_field'] = (string) $m[2];
        }
    }
    if (preg_match('/wp_verify_nonce\(\s*[^,]+,\s*[\'\"]([^\'\"]+)[\'\"]\s*\)/i', $body, $m)) {
        $policy['nonce_mode'] = 'required';
        if ($policy['nonce_action'] === '') {
            $policy['nonce_action'] = (string) $m[1];
        }
        if ($policy['nonce_field'] === '') {
            $policy['nonce_field'] = 'nonce';
        }
    }

    if (!$policy['writes_state']) {
        $writePatterns = [
            '/->insert\s*\(/',
            '/->update\s*\(/',
            '/->delete\s*\(/',
            '/->replace\s*\(/',
            '/update_post_meta\s*\(/',
            '/delete_post_meta\s*\(/',
            '/add_post_meta\s*\(/',
            '/wp_insert_post\s*\(/',
            '/wp_update_post\s*\(/',
            '/wp_delete_post\s*\(/',
            '/update_option\s*\(/',
            '/delete_option\s*\(/',
            '/add_option\s*\(/',
            '/update_user_meta\s*\(/',
            '/delete_user_meta\s*\(/',
            '/wp_insert_user\s*\(/',
            '/wp_update_user\s*\(/',
            '/set_role\s*\(/',
            '/wp_set_password\s*\(/',
            '/set_transient\s*\(/',
            '/delete_transient\s*\(/',
        ];
        foreach ($writePatterns as $pattern) {
            if (preg_match($pattern, $body)) {
                $policy['writes_state'] = true;
                break;
            }
        }
    }

    $policy['rate_limited'] = (bool) preg_match(
        '/cmn_rate_limit\s*\(|is_livechat_rate_limited\s*\(|enforce_school_partner_admin_action_throttle\s*\(/',
        $body
    );

    $policy['token_validation'] = (bool) preg_match(
        '/hash_offer_response_token|get_livechat_thread_by_token|token_hash|thread_token|offer_token|cmn_validate_automation_runner_token|cmn_set_release_version/',
        $body
    );

    $policy['staff_guard'] = $policy['ability_required'] === 'portal.staff.view'
        || (bool) preg_match('/is_staff_user\s*\(/', $body);
    $policy['partner_guard'] = $policy['ability_required'] === 'partner.admin.mutate'
        || (bool) preg_match('/partner\.admin\.mutate|is_admin_user\s*\(.*\)\s*&&\s*!\$this->is_staff_role/s', $body);

    $entityKeys = [];
    if (preg_match_all('/[\'\"]([a-zA-Z0-9_]+_id)[\'\"]\s*=>/', $body, $m)) {
        $entityKeys = array_merge($entityKeys, $m[1]);
    }
    if (preg_match_all('/\$_(?:POST|GET|REQUEST)\s*\[\s*[\'\"]([a-zA-Z0-9_]+_id)[\'\"]\s*\]/', $body, $m)) {
        $entityKeys = array_merge($entityKeys, $m[1]);
    }
    $policy['entity_binding'] = $normalizeUnique($entityKeys);

    if ($policy['nonce_mode'] === 'required' && $policy['nonce_field'] === '') {
        $policy['nonce_field'] = 'nonce';
    }

    return $policy;
};

$methods = $parseMethods($source);
$methodPolicies = [];
foreach ($methods as $methodName => $methodMeta) {
    $methodPolicies[$methodName] = $parseMethodPolicy((string) $methodMeta['body']);
}

$scheduledHooks = [];
if (preg_match_all('/wp_schedule_event\s*\([^,]+,\s*[^,]+,\s*[\'\"]([^\'\"]+)[\'\"]/s', $source, $m)) {
    foreach ($m[1] as $hookName) {
        $scheduledHooks[(string) $hookName] = true;
    }
}
if (preg_match_all('/wp_schedule_single_event\s*\([^,]+,\s*[\'\"]([^\'\"]+)[\'\"]/s', $source, $m)) {
    foreach ($m[1] as $hookName) {
        $scheduledHooks[(string) $hookName] = true;
    }
}

$entries = [];

$buildEntry = static function (
    string $type,
    string $hook,
    string $handler,
    int $registrationLine,
    bool $nopriv,
    array $methods,
    array $methodPolicies
): array {
    $methodMeta = $methods[$handler] ?? null;
    $policy = $methodPolicies[$handler] ?? [
        'has_guard_wrapper' => false,
        'ability_required' => '',
        'nonce_mode' => 'not_applicable',
        'nonce_action' => '',
        'nonce_field' => '',
        'writes_state' => false,
        'entity_binding' => [],
        'rate_limited' => false,
        'token_validation' => false,
        'staff_guard' => false,
        'partner_guard' => false,
    ];

    return [
        'entrypoint' => $hook,
        'type' => $type,
        'hook' => $hook,
        'registration_line' => $registrationLine,
        'handler' => $handler,
        'handler_line' => $methodMeta ? (int) ($methodMeta['start_line'] ?? 0) : 0,
        'nopriv' => $nopriv,
        'ability_required' => (string) ($policy['ability_required'] ?? ''),
        'nonce_mode' => (string) ($policy['nonce_mode'] ?? 'not_applicable'),
        'nonce_action' => (string) ($policy['nonce_action'] ?? ''),
        'nonce_field' => (string) ($policy['nonce_field'] ?? ''),
        'writes_state' => !empty($policy['writes_state']),
        'entity_binding' => array_values((array) ($policy['entity_binding'] ?? [])),
        'has_guard_wrapper' => !empty($policy['has_guard_wrapper']),
        'rate_limited' => !empty($policy['rate_limited']),
        'token_validation' => !empty($policy['token_validation']),
        'staff_guard' => !empty($policy['staff_guard']),
        'partner_guard' => !empty($policy['partner_guard']),
    ];
};

foreach ($lines as $idx => $line) {
    $lineNo = $idx + 1;
    if (preg_match('/add_action\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*\[\$this\s*,\s*[\'\"]([^\'\"]+)[\'\"]\]/', $line, $m)) {
        $hook = (string) $m[1];
        $handler = (string) $m[2];
        $type = '';
        $nopriv = false;

        if (strpos($hook, 'wp_ajax_nopriv_') === 0) {
            $type = 'ajax';
            $nopriv = true;
        } elseif (strpos($hook, 'wp_ajax_') === 0) {
            $type = 'ajax';
        } elseif (strpos($hook, 'admin_post_nopriv_') === 0) {
            $type = 'admin_post';
            $nopriv = true;
        } elseif (strpos($hook, 'admin_post_') === 0) {
            $type = 'admin_post';
        } elseif ($hook === 'template_redirect') {
            $type = 'template_route';
        } elseif (isset($scheduledHooks[$hook])) {
            $type = 'cron';
        }

        if ($type !== '') {
            $entries[] = $buildEntry($type, $hook, $handler, $lineNo, $nopriv, $methods, $methodPolicies);
        }
        continue;
    }

    if (preg_match('/add_shortcode\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*\[\$this\s*,\s*[\'\"]([^\'\"]+)[\'\"]\]/', $line, $m)) {
        $entries[] = $buildEntry(
            'shortcode',
            (string) $m[1],
            (string) $m[2],
            $lineNo,
            false,
            $methods,
            $methodPolicies
        );
    }
}

foreach ($lines as $idx => $line) {
    $lineNo = $idx + 1;
    if (strpos($line, 'register_rest_route(') === false) {
        continue;
    }
    if (!preg_match('/register_rest_route\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]/', $line, $routeMatch)) {
        continue;
    }

    $namespace = (string) $routeMatch[1];
    $route = (string) $routeMatch[2];
    $block = $line . "\n";
    $cursor = $idx + 1;
    while ($cursor < count($lines)) {
        $block .= $lines[$cursor] . "\n";
        if (strpos($lines[$cursor], ']);') !== false) {
            break;
        }
        $cursor++;
    }

    $handler = '';
    if (preg_match('/\'callback\'\s*=>\s*\[\$this\s*,\s*\'([^\']+)\'\]/', $block, $m)
        || preg_match('/"callback"\s*=>\s*\[\$this\s*,\s*"([^\"]+)"\]/', $block, $m)) {
        $handler = (string) $m[1];
    }
    if ($handler === '') {
        continue;
    }

    $entry = $buildEntry('rest', $namespace . $route, $handler, $lineNo, false, $methods, $methodPolicies);
    if ($entry['nonce_mode'] === 'not_applicable') {
        $entry['nonce_mode'] = 'rest_auth';
    }
    $entries[] = $entry;
}

usort($entries, static function (array $a, array $b): int {
    $lineA = (int) ($a['registration_line'] ?? 0);
    $lineB = (int) ($b['registration_line'] ?? 0);
    if ($lineA === $lineB) {
        return strcmp((string) ($a['hook'] ?? ''), (string) ($b['hook'] ?? ''));
    }
    return $lineA <=> $lineB;
});

$payload = [
    'generated_at_utc' => gmdate('c'),
    'source_file' => 'covermenowone-one.php',
    'entrypoint_count' => count($entries),
    'entrypoints' => $entries,
];

$outDir = dirname($outFile);
if (!is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

$result = file_put_contents($outFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
if ($result === false) {
    fwrite(STDERR, "Failed to write manifest: {$outFile}\n");
    exit(1);
}

echo "Generated endpoint manifest: {$outFile}\n";
echo "Entrypoints: " . count($entries) . "\n";
