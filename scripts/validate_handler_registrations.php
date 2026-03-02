#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$manifestFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');
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

preg_match_all('/^\s*(?:public|protected|private)?\s*(?:static\s+)?function\s+([a-zA-Z0-9_]+)\s*\(/m', $source, $fnMatches);
$definedFunctions = [];
foreach (($fnMatches[1] ?? []) as $fnName) {
    $definedFunctions[(string) $fnName] = true;
}

$violations = [];
$entryCount = 0;
foreach ($manifest['entrypoints'] as $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $entryCount++;

    $handler = trim((string) ($entry['handler'] ?? ''));
    $type = (string) ($entry['type'] ?? '');
    $hook = (string) ($entry['hook'] ?? '');
    $file = (string) ($entry['file'] ?? 'covermenowone-one.php');
    $line = (int) ($entry['line'] ?? 0);

    if ($handler === '' || $handler === '{unknown}' || $handler === '{closure}') {
        // Unknown/closure callbacks can be valid for generic WP hooks.
        continue;
    }

    if (strpos($handler, '::') !== false) {
        $parts = explode('::', $handler);
        $handler = (string) end($parts);
    }
    $handler = trim($handler);
    if ($handler === '') {
        continue;
    }

    if (!isset($definedFunctions[$handler])) {
        $violations[] = [
            'type' => $type,
            'hook' => $hook,
            'handler' => $handler,
            'file' => $file,
            'line' => $line,
        ];
    }
}

echo "Handler registration validation file: {$manifestFile}\n";
echo "Entrypoints scanned: {$entryCount}\n";
echo "Violations: " . count($violations) . "\n";

if ($violations) {
    echo "FAIL: missing handler definitions detected.\n\n";
    foreach ($violations as $row) {
        echo "- {$row['type']} {$row['hook']} => {$row['handler']} ({$row['file']}:{$row['line']})\n";
    }
    exit(1);
}

echo "PASS: handler registrations resolve to defined function bodies.\n";

