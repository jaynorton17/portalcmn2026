# Migration Policy (Strict)

This plugin enforces the following schema safety rules:

1. Normal frontend, AJAX, and template-route requests must not run schema migrations.
2. Version-to-version schema upgrades run only through:
   - `admin_post_cmn_run_upgrade_runner`
   - access policy: **admin-only** (`system.upgrade.run`)
3. Activation behavior is limited to fresh installs:
   - create baseline tables only
   - no upgrade-runner execution
   - no versioned migration chain on activation

## Code Entry Points

- Activation hook: `register_activation_hook(__FILE__, ['CMN_One_Plugin', 'activate'])`
- Upgrade runner route: `add_action('admin_post_cmn_run_upgrade_runner', [$this, 'handle_run_upgrade_runner'])`
- Upgrade runner ability: `system.upgrade.run` (resolved as admin-only)
- Request-time migration gate default: `CMN_ENABLE_REQUEST_SCHEMA_MIGRATIONS` (default off)

## Operational Rule

For upgrades on existing installations, run the explicit upgrade runner endpoint from System Health or via authenticated `admin-post.php` submission. Do not rely on request-time traffic to apply migrations.
