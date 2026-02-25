<?php
/**
 * Minimal state-mapping test for candidate availability CTA rendering.
 *
 * Usage (WP-CLI):
 *   wp eval-file wp-content/plugins/covermenowone-one/tests/candidate_availability_ui_state_test.php
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "This script must run inside WordPress.\n");
    exit(1);
}

$plugin = isset($GLOBALS['cmn_one_plugin']) ? $GLOBALS['cmn_one_plugin'] : null;
if (!is_object($plugin)) {
    fwrite(STDERR, "CMN plugin instance not found in \$GLOBALS['cmn_one_plugin'].\n");
    exit(1);
}

$reflector = new ReflectionClass($plugin);
if (!$reflector->hasMethod('get_candidate_availability_ui_state')) {
    fwrite(STDERR, "Missing helper method: get_candidate_availability_ui_state\n");
    exit(1);
}
$method = $reflector->getMethod('get_candidate_availability_ui_state');
$method->setAccessible(true);

$assert = static function ($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

try {
    $unconfirmed = (array) $method->invokeArgs($plugin, [true, false, false, 'this morning']);
    $confirmed = (array) $method->invokeArgs($plugin, [true, true, false, 'this morning']);
    $blocked = (array) $method->invokeArgs($plugin, [true, false, true, 'this morning']);
    $window_closed = (array) $method->invokeArgs($plugin, [false, false, false, 'this morning']);

    $assert(($unconfirmed['state_key'] ?? '') === 'unconfirmed', 'Unconfirmed state key mismatch.');
    $assert(!empty($unconfirmed['show_primary_action']), 'Unconfirmed state should show primary action.');
    $assert(!empty($unconfirmed['show_unavailable_action']), 'Unconfirmed state should show the not-available secondary action.');

    $assert(($confirmed['state_key'] ?? '') === 'confirmed_available', 'Confirmed state key mismatch.');
    $assert(empty($confirmed['show_primary_action']), 'Confirmed state should hide primary action.');
    $assert(!empty($confirmed['show_confirmed_pill']), 'Confirmed state should show confirmed pill.');
    $assert(!empty($confirmed['show_unavailable_action']), 'Confirmed state should show one change action.');

    $assert(($blocked['state_key'] ?? '') === 'confirmed_not_available', 'Blocked state key mismatch.');
    $assert(empty($blocked['show_primary_action']), 'Blocked state should hide primary action.');
    $assert(!empty($blocked['show_unavailable_action']), 'Blocked state should keep a single change action.');

    $assert(($window_closed['state_key'] ?? '') === 'window_closed', 'Window-closed state key mismatch.');
    $assert(!empty($window_closed['show_primary_action']), 'Window-closed state should keep primary action visible (disabled by render logic).');

    echo wp_json_encode([
        'ok' => true,
        'states_tested' => [
            'unconfirmed' => $unconfirmed['state_key'] ?? '',
            'confirmed' => $confirmed['state_key'] ?? '',
            'blocked' => $blocked['state_key'] ?? '',
            'window_closed' => $window_closed['state_key'] ?? '',
        ],
    ], JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'candidate_availability_ui_state_test failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

exit(0);
