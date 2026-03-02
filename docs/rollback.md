# Rollback Runbook

## Scope
This runbook covers immediate rollback controls for:
- heartbeat (`cmn_portal_heartbeat`)
- DB job locks (`cmn_job_locks` path)
- frontend polling behavior
- manual plugin rollback via SFTP/backup restore

## Feature Flags (Runtime)
Flag keys and defaults:
- `cmn_feature_heartbeat` default: `1` (enabled)
- `cmn_feature_heartbeat_shadow` default: `0` (disabled)
- `cmn_feature_db_locks` default: `1` (enabled)

Constant overrides (if defined in `wp-config.php`):
- `CMN_FEATURE_HEARTBEAT`
- `CMN_FEATURE_HEARTBEAT_SHADOW`
- `CMN_FEATURE_DB_LOCKS`

Precedence:
1. Constant value (if defined)
2. Option value (`cmn_feature_*`)
3. Built-in default

## Disable Heartbeat Instantly
No redeploy required.

Option method (preferred, runtime):
```bash
wp option update cmn_feature_heartbeat 0 --path=/home/www/public
```

Constant method (configuration lock):
```php
define('CMN_FEATURE_HEARTBEAT', false);
```

Effect:
- Server returns `403` with `heartbeat_disabled` payload for `cmn_portal_heartbeat`.
- Frontend heartbeat manager disables itself and switches each active module to legacy pollers.

## Disable DB Locks Instantly
No redeploy required.

Option method:
```bash
wp option update cmn_feature_db_locks 0 --path=/home/www/public
```

Constant method:
```php
define('CMN_FEATURE_DB_LOCKS', false);
```

Effect:
- Lock acquisition falls back to transient-based locking path.

## Revert to Legacy Polling Without Redeploy
Full legacy UI polling:
```bash
wp option update cmn_feature_heartbeat 0 --path=/home/www/public
```

Shadow dual-run (legacy UI + heartbeat background):
```bash
wp option update cmn_feature_heartbeat 1 --path=/home/www/public
wp option update cmn_feature_heartbeat_shadow 1 --path=/home/www/public
```

Full cutover mode (heartbeat drives UI):
```bash
wp option update cmn_feature_heartbeat 1 --path=/home/www/public
wp option update cmn_feature_heartbeat_shadow 0 --path=/home/www/public
```

If constants are defined in `wp-config.php`, update/remove constants first because constants override options.

## Manual SFTP Rollback (Previous Plugin Backup)
Target plugin directory:
- `/home/www/public/wp-content/plugins/covermenow-one`

If you have shell access:
1. Move current plugin aside:
```bash
mv /home/www/public/wp-content/plugins/covermenow-one /home/www/public/wp-content/plugins/covermenow-one.rollback.$(date +%Y%m%d%H%M%S)
```
2. Restore backup folder or unzip previous plugin archive into:
- `/home/www/public/wp-content/plugins/covermenow-one`
3. Ensure main file exists:
- `/home/www/public/wp-content/plugins/covermenow-one/covermenowone-one.php`

If using SFTP only:
1. Rename remote folder `covermenow-one` to a timestamped backup.
2. Upload previous known-good `covermenow-one` folder (or extract zip locally and upload folder contents).
3. Confirm folder name is exactly `covermenow-one`.

## Version Pin (Operational)
Pin to a known-good plugin build by keeping versioned backups and restoring by version:
1. Keep release artifacts with version in filename, for example:
- `covermenow-one-v0.1.24.zip`
- `covermenow-one-v0.1.25.zip`
2. Before deploy, record live version:
```bash
grep -n "Version:" /home/www/public/wp-content/plugins/covermenow-one/covermenowone-one.php
```
3. If rollback is required, restore the previously pinned folder/zip (for example `v0.1.24`) and confirm:
```bash
grep -n "Version:" /home/www/public/wp-content/plugins/covermenow-one/covermenowone-one.php
```
4. Keep the replaced folder as `covermenow-one.rollback.YYYYMMDDHHMMSS` until verification is complete.

## Post-Rollback Verification
Run these checks immediately:
1. Load portal and confirm no fatal error.
2. Confirm expected mode:
- heartbeat disabled: no active heartbeat UI updates; legacy polling requests appear.
- shadow mode: legacy UI still updates; heartbeat debug logs still visible.
3. Verify notifications unread count updates correctly.
4. Verify support ticket messages update in realtime.
5. Verify booking chat updates in realtime.
6. Verify staff lounge updates in realtime.
7. Verify account manager unread badge updates.
8. If DB locks disabled, confirm scheduled jobs still run and no hard failures.

## SQL Fallback (If `wp` CLI unavailable)
Use your table prefix instead of `wp_`.

```sql
UPDATE wp_options SET option_value='0' WHERE option_name='cmn_feature_heartbeat';
UPDATE wp_options SET option_value='1' WHERE option_name='cmn_feature_heartbeat_shadow';
UPDATE wp_options SET option_value='0' WHERE option_name='cmn_feature_db_locks';
```

## Rollback Support Already in Code
No additional code changes are required for rollback controls in this release.
Existing support:
- feature-flag evaluation via `is_feature_enabled(...)`
- heartbeat kill switch response (`heartbeat_disabled`)
- frontend runtime fallback to legacy pollers per view/module
