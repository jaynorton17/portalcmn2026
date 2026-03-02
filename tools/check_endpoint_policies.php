#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$manifestFile = $argv[1] ?? ($root . '/docs/endpoint-manifest.json');

if (!is_file($manifestFile)) {
    fwrite(STDERR, "Manifest not found: {$manifestFile}\nRun tools/generate_endpoint_manifest.php first.\n");
    exit(1);
}

$decoded = json_decode((string) file_get_contents($manifestFile), true);
if (!is_array($decoded) || !isset($decoded['entrypoints']) || !is_array($decoded['entrypoints'])) {
    fwrite(STDERR, "Invalid manifest format: {$manifestFile}\n");
    exit(1);
}

$entrypoints = $decoded['entrypoints'];
$violations = [];

$isStaffOnlyEntrypoint = static function (array $entry): bool {
    $target = strtolower((string) ($entry['hook'] ?? '') . ' ' . (string) ($entry['handler'] ?? ''));
    return (bool) preg_match('/cmn_staff_|system_health|cmn_match_|cmn_marketing_|school_partner_admin|partner_programme|run_upgrade_runner|render_staff_/', $target);
};

$isPublicTokenEndpoint = static function (array $entry): bool {
    $target = strtolower((string) ($entry['hook'] ?? '') . ' ' . (string) ($entry['handler'] ?? ''));
    if (!empty($entry['token_validation'])) {
        return true;
    }
    return (bool) preg_match('/token|candidate_response|livechat|set_release_version|automation_runner/', $target);
};

foreach ($entrypoints as $entry) {
    $type = (string) ($entry['type'] ?? '');
    $hook = (string) ($entry['hook'] ?? '');
    $handler = (string) ($entry['handler'] ?? '');
    $writesState = !empty($entry['writes_state']);
    $nonceMode = (string) ($entry['nonce_mode'] ?? 'not_applicable');
    $ability = (string) ($entry['ability_required'] ?? '');
    $nopriv = !empty($entry['nopriv']);

    $entryRef = $type . ':' . $hook . ' -> ' . $handler;

    $nonceRequiredTypes = ['ajax', 'admin_post', 'rest'];
    $isTokenizedPublicWrite = $writesState
        && $isPublicTokenEndpoint($entry)
        && !empty($entry['token_validation'])
        && !empty($entry['rate_limited']);
    if ($writesState && in_array($type, $nonceRequiredTypes, true) && $nonceMode !== 'required' && !$isTokenizedPublicWrite) {
        $violations[] = [
            'rule' => 'writes_state_requires_nonce',
            'entrypoint' => $entryRef,
            'message' => 'writes_state=true but nonce_mode is not required.',
        ];
    }

    if ($isStaffOnlyEntrypoint($entry)) {
        $allowedStaffAbilities = ['portal.staff.view', 'partner.admin.mutate', 'system.upgrade.run'];
        if (!in_array($ability, $allowedStaffAbilities, true)) {
            $violations[] = [
                'rule' => 'staff_view_requires_staff_ability',
                'entrypoint' => $entryRef,
                'message' => 'Staff-only endpoint missing required ability guard.',
            ];
        }
    }

    if (strpos($hook, 'cmn_school_partner_admin_') !== false && $ability !== 'partner.admin.mutate') {
        $violations[] = [
            'rule' => 'partner_admin_requires_partner_ability',
            'entrypoint' => $entryRef,
            'message' => 'Partner admin endpoint missing partner.admin.mutate ability.',
        ];
    }

    if ($nopriv && $writesState && $isPublicTokenEndpoint($entry)) {
        $hasTokenValidation = !empty($entry['token_validation']);
        $hasRateLimit = !empty($entry['rate_limited']);
        if (!$hasTokenValidation || !$hasRateLimit) {
            $violations[] = [
                'rule' => 'public_token_endpoint_requires_token_and_rate_limit',
                'entrypoint' => $entryRef,
                'message' => 'Public state-changing endpoint missing strict token validation and/or rate limiting.',
            ];
        }
    }
}

$total = count($entrypoints);
$violationCount = count($violations);

echo "Policy check target: {$manifestFile}\n";
echo "Entrypoints scanned: {$total}\n";

echo "Violations: {$violationCount}\n";
if ($violationCount < 1) {
    echo "All policy checks passed.\n";
    exit(0);
}

$maxPrint = 200;
$printed = 0;
foreach ($violations as $violation) {
    $printed++;
    echo sprintf(
        "[%s] %s :: %s\n",
        (string) $violation['rule'],
        (string) $violation['entrypoint'],
        (string) $violation['message']
    );
    if ($printed >= $maxPrint) {
        $remaining = $violationCount - $printed;
        if ($remaining > 0) {
            echo "... {$remaining} additional violations omitted.\n";
        }
        break;
    }
}

exit(1);
