# Heartbeat Performance Verification Checklist

## Prerequisites
- Use a test account with access to school, support, and staff chat areas.
- Open browser DevTools:
  - `Network` tab (Preserve log enabled)
  - `Console` tab
- For debug summaries, enable:
  - In console: `window.cmnPortal = window.cmnPortal || {}; window.cmnPortal.debugHeartbeat = 1;`

## 1) Requests/Minute Before vs After
1. Disable heartbeat (if feature toggle exists in env/config) and load a target page for 60 seconds.
2. In Network tab, filter by `admin-ajax.php` and count requests in 60 seconds.
3. Record baseline requests/minute.
4. Re-enable heartbeat, reload page, repeat the same 60-second capture.
5. Confirm only one repeating heartbeat request pattern is active during normal use.
6. Record post-change requests/minute and compare.

## 2) Pages To Test
- Dashboard (school or staff dashboard with live counters)
- Support ticket thread view
- Booking chat thread view
- Staff lounge thread view

## 3) Functional Regression Checks
- Send a new support message from another session:
  - Confirm message appears without full page refresh.
- Send a booking chat message from another participant:
  - Confirm thread updates and latest message appears once.
- Send a staff lounge message:
  - Confirm staff lounge timeline updates.
- Trigger unread badge changes:
  - Confirm unread badge count matches message reality.

## 4) What To Watch For
- Missed messages (message created but not shown until manual refresh)
- Duplicate message rendering in timelines
- Unread badge mismatch (badge count differs from visible unread state)
- Polling not backing off when tab hidden
- Heartbeat summary showing `unchanged=1` while real deltas exist

## 5) Debug Output Expectation
When `window.cmnPortal.debugHeartbeat = 1` is enabled, console should show summary lines including:
- `next_poll_ms`
- `unchanged`
- `deltas_keys_updated`

Example:
`[CMN Heartbeat] summary { next_poll_ms: 5000, unchanged: 0, deltas_keys_updated: ["support_ticket", "account_manager_badge"] }`
