# v0.1.25 Integration Task Checklist

Source plan used: `/home/jaynorton17/covermenowone/Continue CoverMeNow ONE Hardening   Performance Up.txt` (workspace copy; `/mnt/data` was not mounted in this environment).

## Phase 1 — Deploy + Release Safety

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P0-01 | Deploy supports CLI flags and env fallback | `deploy_portal.sh`, `scripts/deploy_plugin.sh` |
| P0-02 | Dry-run prints local/remote version + transfer plan | `deploy_portal.sh`, `scripts/deploy_plugin.sh` |
| P0-03 | Verify mode checks remote version + frontend hash | `deploy_portal.sh`, `scripts/deploy_plugin.sh` |
| P0-04 | Deployment config/docs without secrets | `.env.example`, `deploy.env.example`, `DEPLOY.md`, `docs/release-checklist.md` |

## Phase 2 — Endpoint Security Policy

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P0-05 | Generate endpoint manifest (ajax/admin_post/cron/shortcode/routes) | `scripts/generate_endpoint_manifest.php`, `docs/endpoint-manifest.json` |
| P0-06 | Validate endpoint policies (nonce/ability/nopriv/token/rate-limit) | `scripts/validate_endpoint_policies.php`, `docs/endpoint-policy-allowlist.json` |
| P0-07 | Unified quality command | `Makefile` (`make manifest`, `make validate`, `make check`) |
| P0-08 | Close remaining endpoint policy violations | `covermenowone-one.php` handlers + `cmn_endpoint_guard()` usage |
| P0-09 | Public nopriv endpoint lockdown (token hash/expiry/consume/rate-limit) | `handle_candidate_response`, token helpers, public endpoints in `covermenowone-one.php` |
| P0-10 | No missing/dead registered handlers | `covermenowone-one.php` registration map, `scripts/validate_handler_registrations.php` |

## Phase 3 — Heartbeat Performance Migration

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P1-01 | Heartbeat channels: support/booking/lounge/account manager/presence | `handle_portal_heartbeat()`, channel payload helpers in `covermenowone-one.php` |
| P1-02 | Per-channel cursors + cache (2–5s), keyed by user/channel/context/cursor | `cmn_heartbeat_get_cached_channel_payload()`, heartbeat request parsing helpers |
| P1-03 | Frontend migration: one poll manager heartbeat scheduler + event dispatch | `frontend.js` (`cmnHeartbeatManager`, `cmn:portal-heartbeat`) |
| P1-04 | Legacy fallback retained when heartbeat disabled | `frontend.js` disable fallback + legacy pollers; heartbeat 403 handling |
| P1-05 | One-release deprecation window for legacy endpoints | legacy handlers return `deprecated=true` in `covermenowone-one.php` |

## Phase 4 — Reliability / Migrations

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P1-06 | Request-time schema migrations OFF by default | `CMN_ENABLE_REQUEST_SCHEMA_MIGRATIONS` logic, `maybe_upgrade_schema()` |
| P1-07 | Upgrade runner is explicit migration path | `admin_post_cmn_run_upgrade_runner`, `handle_run_upgrade_runner()` |
| P1-08 | Admin-only System Health button for upgrade runner | System Health render in `covermenowone-one.php` |
| P1-09 | Migration run results persisted + shown in UI | `cmn_last_upgrade_runner_result` option + System Health panel |

## Phase 5 — Indexes / Delta Query Safety

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P1-10 | v74 job locks + hotspot indexes | `migrate_schema_v74_job_locks_and_hot_indexes()` |
| P1-11 | v75 heartbeat hotspot indexes | `migrate_schema_v75_heartbeat_hotspot_indexes()` |
| P1-12 | v76 delta-query indexes + schema_index_added audit | `migrate_schema_v76_delta_query_indexes()`, `maybe_add_delta_query_index()` |
| P1-13 | Idempotent index DDL checks via SHOW INDEX | `table_has_index_for_columns()`, `maybe_add_missing_index()` |

## Phase 6 — Kill Switches / Rollback / Shadow

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P1-14 | Feature flags: heartbeat / db_locks / heartbeat_shadow | `is_feature_enabled()` map, localized flags in `covermenowone-one.php` |
| P1-15 | Heartbeat shadow mode (dual-run) | `frontend.js` heartbeat shadow scheduling + no UI-driving mode |
| P1-16 | Rollback/Version pin runbook | `docs/rollback.md` |

## Phase 7 — Measurement / Perf Proof

| Task ID | Task | Expected Files / Functions |
|---|---|---|
| P1-17 | Server telemetry: `heartbeat_call` / `legacy_poll_call` + duration/query/payload/cache | `cmn_capture_poll_telemetry_start()`, `cmn_log_poll_call_telemetry()`, heartbeat + legacy handlers |
| P1-18 | Frontend one-line debug heartbeat log (`debugHeartbeat=1`) | `frontend.js` (`HB ok next=...`) |
| P1-19 | Runtime contract validation command | `scripts/validate_runtime_guards.php`, `Makefile` |

