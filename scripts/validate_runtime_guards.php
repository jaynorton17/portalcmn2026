#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$manifestFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');
$frontendFile = $root . '/frontend.js';

if (!is_file($manifestFile)) {
    fwrite(STDERR, "Manifest not found: {$manifestFile}\n");
    exit(1);
}
if (!is_file($frontendFile)) {
    fwrite(STDERR, "Frontend file not found: {$frontendFile}\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestFile), true);
if (!is_array($manifest) || !isset($manifest['entrypoints']) || !is_array($manifest['entrypoints'])) {
    fwrite(STDERR, "Invalid manifest structure: {$manifestFile}\n");
    exit(1);
}

$frontend = (string) file_get_contents($frontendFile);
if ($frontend === '') {
    fwrite(STDERR, "Frontend file is empty: {$frontendFile}\n");
    exit(1);
}

$policyEndpointTypes = ['wp_ajax', 'wp_ajax_nopriv', 'admin_post', 'admin_post_nopriv'];
$policyViolations = [];

foreach ($manifest['entrypoints'] as $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $type = (string) ($entry['type'] ?? '');
    if (!in_array($type, $policyEndpointTypes, true)) {
        continue;
    }
    $writesState = (bool) ($entry['writes_state'] ?? false);
    $nonceMode = strtolower(trim((string) ($entry['nonce_mode'] ?? 'none')));
    if ($writesState && $nonceMode !== 'required') {
        $policyViolations[] = [
            'type' => $type,
            'hook' => (string) ($entry['hook'] ?? ''),
            'file' => (string) ($entry['file'] ?? 'covermenowone-one.php'),
            'line' => (int) ($entry['line'] ?? 0),
            'reason' => 'writes_state=true but nonce_mode!=required',
        ];
    }
}

$requiredFrontendSnippets = [
    'notifications heartbeat gate' => 'if (notificationsApiReady && !notificationsHeartbeatCanDriveUi)',
    'support heartbeat gate' => 'if (!supportHeartbeatCanDriveUi)',
    'staff lounge heartbeat gate' => 'if (!staffLoungeHeartbeatCanDriveUi)',
    'booking chat heartbeat gate' => 'if (!bookingChatHeartbeatCanDriveUi)',
    'account manager active channel gate' => 'if (!accountManagerHeartbeatChannelActive)',
    'heartbeat disabled fallback event' => 'heartbeat_disabled',
    'heartbeat one-line debug' => 'HB ok next=',
];

$frontendViolations = [];
foreach ($requiredFrontendSnippets as $label => $snippet) {
    if (strpos($frontend, $snippet) === false) {
        $frontendViolations[] = $label . ' (missing snippet: ' . $snippet . ')';
    }
}

echo "Runtime guard validation file: {$manifestFile}\n";
echo "Entrypoints scanned: " . count($manifest['entrypoints']) . "\n";
echo "Policy violations: " . count($policyViolations) . "\n";
echo "Frontend guard violations: " . count($frontendViolations) . "\n";

if ($policyViolations || $frontendViolations) {
    echo "FAIL: runtime guard validation failed.\n\n";
    foreach ($policyViolations as $violation) {
        echo "- {$violation['type']} {$violation['hook']} ({$violation['file']}:{$violation['line']}): {$violation['reason']}\n";
    }
    foreach ($frontendViolations as $violation) {
        echo "- frontend.js: {$violation}\n";
    }
    exit(1);
}

echo "PASS: endpoint nonce policy + heartbeat legacy fallback guards validated.\n";

