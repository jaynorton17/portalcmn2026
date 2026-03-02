# CoverMeNow ONE v0.1.25

Release date: 2026-03-02

## Summary

This release finalizes P0 hardening and heartbeat migration work for the portal.

## Included

- Request-time schema migration execution disabled in normal request paths.
- Upgrade runner retained as the only supported migration path (`admin_post_cmn_run_upgrade_runner`).
- Upgrade runner auditing expanded with busy/success/error outcome metadata.
- Portal heartbeat expanded for multi-channel deltas with per-context cursor support.
- Heartbeat response metadata added:
  - `duration_ms`
  - `cache_hit`
  - `channels`
  - `row_counts`
- Frontend heartbeat debug mode added via `CMN_DEBUG_HEARTBEAT` (RPM + meta logging).
- Polling migration continued toward a single heartbeat flow with legacy fallback retained.
- Endpoint policy manifest and validator workflow maintained for release checks.

## Notes

- Plugin header version and `VERSION` constant are both set to `0.1.25`.
- No database `ENUM` columns were introduced; existing status fields remain `VARCHAR`.
