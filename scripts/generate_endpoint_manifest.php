#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$pluginFile = $root . '/covermenowone-one.php';
$outputFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');
$overrideFile = $argv[2] ?? ($root . '/docs/endpoint-manifest-overrides.json');

if (!is_file($pluginFile)) {
    fwrite(STDERR, "Plugin file not found: {$pluginFile}\n");
    exit(1);
}

$source = (string) file_get_contents($pluginFile);
if ($source === '') {
    fwrite(STDERR, "Plugin file is empty: {$pluginFile}\n");
    exit(1);
}

/**
 * Override key format: type|hook|handler
 * Example:
 * {
 *   "wp_ajax|wp_ajax_cmn_example|handle_example": { "writes_state": true, "nonce_mode": "required" }
 * }
 */
$overrideMap = [];
if (is_file($overrideFile)) {
    $decoded = json_decode((string) file_get_contents($overrideFile), true);
    if (is_array($decoded)) {
        $overrideMap = $decoded;
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
    if (preg_match('/^function\s*\(/i', $callback)) {
        return '{closure}';
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
            $blocks[$name] = [
                'body' => $body,
                'line' => $line,
            ];
        }
    }

    return $blocks;
};

$functionBlocks = $parseFunctionBlocks($source);

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
        '/do_action\s*\(\s*[\'"]cmn_audit_/',
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
    if (preg_match('/current_user_can\s*\(\s*[\'"]([^\'"]+)[\'"]/', $body, $m)) {
        return 'cap:' . trim((string) $m[1]);
    }
    return null;
};

$buildRow = static function (
    string $type,
    string $hook,
    string $handler,
    int $line,
    bool $nopriv
) use (
    $functionBlocks,
    $detectWritesState,
    $detectNonceMode,
    $detectAbility,
    $overrideMap
): array {
    $body = '';
    if ($handler !== '' && isset($functionBlocks[$handler])) {
        $body = (string) $functionBlocks[$handler]['body'];
    }

    $writesState = $detectWritesState($body);
    $detectedNonceMode = $detectNonceMode($body);
    $detectedAbility = $detectAbility($body);

    $row = [
        'type' => $type,
        'hook' => $hook,
        'handler' => $handler,
        'file' => 'covermenowone-one.php',
        'line' => $line,
        'writes_state' => $writesState,
        'nonce_mode' => $detectedNonceMode,
        'ability_required' => $detectedAbility !== null && $detectedAbility !== '' ? $detectedAbility : null,
        'nopriv' => $nopriv,
    ];

    $overrideKey = $type . '|' . $hook . '|' . $handler;
    if (isset($overrideMap[$overrideKey]) && is_array($overrideMap[$overrideKey])) {
        foreach ($overrideMap[$overrideKey] as $k => $v) {
            if (array_key_exists($k, $row)) {
                $row[$k] = $v;
            }
        }
    }

    return $row;
};

$entries = [];
$seen = [];

$addEntry = static function (array $row) use (&$entries, &$seen): void {
    $key = implode('|', [
        (string) $row['type'],
        (string) $row['hook'],
        (string) $row['handler'],
        (string) $row['line'],
    ]);
    if (isset($seen[$key])) {
        return;
    }
    $seen[$key] = true;
    $entries[] = $row;
};

// 1) add_action hooks (ajax/admin_post/general hooks)
$actionPattern = '/add_action\s*\(\s*(["\'])(?<hook>[^"\']+)\1\s*,\s*(?<callback>\[[^\]]+\]|array\s*\([^\)]*\)|["\'][^"\']+["\']|[a-zA-Z0-9_\\\\:]+)\s*(?:,\s*(?<priority>\d+))?/ms';
$registeredActionHandlers = [];
if (preg_match_all($actionPattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
    foreach ($matches as $match) {
        $hook = (string) ($match['hook'][0] ?? '');
        $callback = (string) ($match['callback'][0] ?? '');
        $offset = (int) ($match[0][1] ?? 0);
        $line = $lineForOffset($offset);
        $handler = $extractHandler($callback);

        if ($hook === '') {
            continue;
        }
        $registeredActionHandlers[$hook] = [
            'handler' => $handler,
            'line' => $line,
        ];

        if (strpos($hook, 'wp_ajax_nopriv_') === 0) {
            $addEntry($buildRow('wp_ajax_nopriv', $hook, $handler, $line, true));
            continue;
        }
        if (strpos($hook, 'wp_ajax_') === 0) {
            $addEntry($buildRow('wp_ajax', $hook, $handler, $line, false));
            continue;
        }
        if (strpos($hook, 'admin_post_nopriv_') === 0) {
            $addEntry($buildRow('admin_post_nopriv', $hook, $handler, $line, true));
            continue;
        }
        if (strpos($hook, 'admin_post_') === 0) {
            $addEntry($buildRow('admin_post', $hook, $handler, $line, false));
            continue;
        }
        if ($hook === 'template_redirect') {
            $addEntry($buildRow('template_route', $hook, $handler, $line, false));
        }
    }
}

// 2) Cron hooks from scheduling calls + associated add_action handler if present
$cronHooks = [];
$cronPattern = '/wp_schedule(?:_single)?_event\s*\(\s*[^,]+,\s*[^,]+,\s*(["\'])(?<hook>[^"\']+)\1/ms';
if (preg_match_all($cronPattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
    foreach ($matches as $match) {
        $hook = (string) ($match['hook'][0] ?? '');
        $offset = (int) ($match[0][1] ?? 0);
        $line = $lineForOffset($offset);
        if ($hook !== '') {
            $cronHooks[$hook] = $line;
        }
    }
}

foreach ($cronHooks as $hook => $line) {
    $handler = '';
    $handlerLine = $line;
    if (isset($registeredActionHandlers[$hook])) {
        $handler = (string) $registeredActionHandlers[$hook]['handler'];
        $handlerLine = (int) $registeredActionHandlers[$hook]['line'];
    }
    $addEntry($buildRow('cron', $hook, $handler, $handlerLine, false));
}

// 3) Shortcodes
$shortcodePattern = '/add_shortcode\s*\(\s*(["\'])(?<tag>[^"\']+)\1\s*,\s*(?<callback>\[[^\]]+\]|array\s*\([^\)]*\)|["\'][^"\']+["\']|[a-zA-Z0-9_\\\\:]+)\s*\)/ms';
if (preg_match_all($shortcodePattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
    foreach ($matches as $match) {
        $tag = (string) ($match['tag'][0] ?? '');
        $callback = (string) ($match['callback'][0] ?? '');
        $offset = (int) ($match[0][1] ?? 0);
        if ($tag === '') {
            continue;
        }
        $line = $lineForOffset($offset);
        $handler = $extractHandler($callback);
        $addEntry($buildRow('shortcode', $tag, $handler, $line, false));
    }
}

// 4) Template routes from rewrite rules
$rewritePattern = '/add_rewrite_rule\s*\(\s*(["\'])(?<route>.*?)\1\s*,\s*(["\'])(?<target>.*?)\3(?:\s*,\s*(["\'])(?<position>top|bottom)\5)?/ms';
if (preg_match_all($rewritePattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
    foreach ($matches as $match) {
        $route = trim((string) ($match['route'][0] ?? ''));
        $target = trim((string) ($match['target'][0] ?? ''));
        $position = trim((string) ($match['position'][0] ?? ''));
        $offset = (int) ($match[0][1] ?? 0);
        if ($route === '') {
            continue;
        }
        $line = $lineForOffset($offset);
        $hook = $route . ' => ' . $target;
        if ($position !== '') {
            $hook .= ' [' . $position . ']';
        }
        // Rewrites are route definitions; execution happens later in template_redirect handlers.
        $addEntry($buildRow('template_route', $hook, '', $line, false));
    }
}

usort($entries, static function (array $a, array $b): int {
    $typeCmp = strcmp((string) $a['type'], (string) $b['type']);
    if ($typeCmp !== 0) {
        return $typeCmp;
    }
    $hookCmp = strcmp((string) $a['hook'], (string) $b['hook']);
    if ($hookCmp !== 0) {
        return $hookCmp;
    }
    return (int) $a['line'] <=> (int) $b['line'];
});

$payload = [
    'generated_at_utc' => gmdate('c'),
    'source_file' => 'covermenowone-one.php',
    'count' => count($entries),
    'entrypoints' => array_values($entries),
];

$outDir = dirname($outputFile);
if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Unable to create output directory: {$outDir}\n");
    exit(1);
}

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    fwrite(STDERR, "Unable to encode endpoint manifest JSON.\n");
    exit(1);
}

if (file_put_contents($outputFile, $json . "\n") === false) {
    fwrite(STDERR, "Unable to write output file: {$outputFile}\n");
    exit(1);
}

echo "Generated endpoint manifest: {$outputFile}\n";
echo "Entrypoints: " . count($entries) . "\n";
