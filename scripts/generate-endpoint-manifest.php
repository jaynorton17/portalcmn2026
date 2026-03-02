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

$pattern = '/add_action\s*\(\s*["\'](?<hook>(?:wp_ajax(?:_nopriv)?|admin_post(?:_nopriv)?)_[^"\']+)["\']\s*,\s*\[\s*\$this\s*,\s*["\'](?<handler>[a-zA-Z0-9_]+)["\']\s*\]\s*\)/m';

$entries = [];
$seen = [];
if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $hook = (string) ($match['hook'] ?? '');
        $handler = (string) ($match['handler'] ?? '');
        if ($hook === '' || $handler === '') {
            continue;
        }

        $isAjax = strpos($hook, 'wp_ajax_') === 0;
        $isAdminPost = strpos($hook, 'admin_post_') === 0;
        if (!$isAjax && !$isAdminPost) {
            continue;
        }

        $nopriv = strpos($hook, 'wp_ajax_nopriv_') === 0 || strpos($hook, 'admin_post_nopriv_') === 0;
        $entryType = $isAjax ? 'ajax' : 'admin_post';

        $dedupeKey = $entryType . '|' . $hook . '|' . $handler;
        if (isset($seen[$dedupeKey])) {
            continue;
        }
        $seen[$dedupeKey] = true;

        $entries[] = [
            'entry_type' => $entryType,
            'hook_name' => $hook,
            'handler_function' => $handler,
            'nopriv' => $nopriv,
        ];
    }
}

usort($entries, static function (array $a, array $b): int {
    $typeCmp = strcmp((string) ($a['entry_type'] ?? ''), (string) ($b['entry_type'] ?? ''));
    if ($typeCmp !== 0) {
        return $typeCmp;
    }
    $hookCmp = strcmp((string) ($a['hook_name'] ?? ''), (string) ($b['hook_name'] ?? ''));
    if ($hookCmp !== 0) {
        return $hookCmp;
    }
    return strcmp((string) ($a['handler_function'] ?? ''), (string) ($b['handler_function'] ?? ''));
});

$payload = [
    'generated_at_utc' => gmdate('c'),
    'source_file' => 'covermenowone-one.php',
    'count' => count($entries),
    'entrypoints' => $entries,
];

$outDir = dirname($outFile);
if (!is_dir($outDir)) {
    if (!mkdir($outDir, 0775, true) && !is_dir($outDir)) {
        fwrite(STDERR, "Unable to create output directory: {$outDir}\n");
        exit(1);
    }
}

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    fwrite(STDERR, "Unable to encode manifest JSON.\n");
    exit(1);
}

if (file_put_contents($outFile, $json . "\n") === false) {
    fwrite(STDERR, "Unable to write manifest: {$outFile}\n");
    exit(1);
}

echo "Generated endpoint manifest: {$outFile}\n";
echo "Entrypoints: " . count($entries) . "\n";
