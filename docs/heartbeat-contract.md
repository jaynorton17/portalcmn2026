# CMN Portal Heartbeat Contract

Endpoint: `wp_ajax_cmn_portal_heartbeat`  
Handler: `CMN_One_Plugin::handle_portal_heartbeat()`

## Request

Required:

- `action=cmn_portal_heartbeat`
- `nonce` (`cmn_portal_heartbeat`)
- `since_event_id` (int)
- `channels[]` (array of channel keys)

Optional context selectors:

- `view_context` (string)
- `candidate_ids[]` (for `live_matches`)
- `support_ticket_ids[]`
- `booking_thread_ids[]`
- `staff_lounge_thread_types[]`
- `include_account_manager_badge` / `include_account_manager_chat_status` (`0|1`)

Optional cursor maps (preferred for delta channels):

- `support_ticket_cursor_map` JSON object: `{ "<ticket_id>": <since_message_id> }`
- `booking_chat_cursor_map` JSON object: `{ "<thread_id>": <since_message_id> }`
- `staff_lounge_cursor_map` JSON object: `{ "<thread_type>": <since_message_id> }`

Legacy fallback cursor fields (still accepted):

- `support_since_message_id`
- `booking_chat_since_message_id`
- `staff_lounge_since_message_id`

## Supported Channels

- `notifications`
- `live_matches` (preserved existing logic)
- `support_ticket` / `support_tickets`
- `booking_chat` / `booking_chats`
- `staff_lounge`
- `account_manager_badge` / `account_manager_chat_status`
- `presence_touch`

## Response

`success: true` with payload:

- `event_id_latest` (int)
- `since_event_id` (int)
- `view_context` (string)
- `channels` (array)
- `deltas` (object keyed by channel)

Delta channels return only new messages where `id > since_message_id`.

Per-entity delta payload includes:

- `since_message_id` (echo of cursor used)
- `latest_message_id`
- `messages` (only new rows)

## Caching + Throttle

- Per-channel micro-cache TTL: `2s` (channel payload cache)
- Cache key dimensions:
  - `user_id`
  - `channel`
  - `context` (entity/view context)
  - `cursor`
- Presence touch coalescing:
  - `presence_touch` is throttled server-side at `>=60s`
  - response includes `throttle_seconds` and `touched` flag

## Example Response Payload

```json
{
  "event_id_latest": 18422,
  "since_event_id": 18390,
  "view_context": "support",
  "channels": [
    "support_ticket",
    "booking_chat",
    "account_manager_badge",
    "presence_touch"
  ],
  "deltas": {
    "support_ticket": {
      "4321": {
        "since_message_id": 18390,
        "latest_message_id": 18411,
        "messages": [
          {
            "id": 18411,
            "sender_type": "admin",
            "sender_name": "Support",
            "message": "Update complete.",
            "attachments": [],
            "created_at": "2026-03-02 11:24:18"
          }
        ],
        "ticket": {
          "id": 4321,
          "status": "open"
        }
      }
    },
    "booking_chat": {
      "887": {
        "since_message_id": 511,
        "latest_message_id": 513,
        "messages": [
          {
            "id": 513,
            "sender_role_type": "school",
            "sender_name": "School",
            "message": "Can you confirm arrival time?",
            "created_at": "2026-03-02 11:24:20",
            "attachments": []
          }
        ],
        "thread": {
          "id": 887,
          "booking_id": 9012,
          "thread_type": "booking_details",
          "status": "active"
        }
      }
    },
    "account_manager_badge": {
      "ticket_id": 4321,
      "unread_count": 2,
      "has_unread": 1,
      "support_url": "/portal/?view=support&ticket=4321"
    },
    "presence_touch": {
      "touched": 1,
      "role": "staff",
      "throttle_seconds": 60,
      "touched_at": 1772441060
    }
  }
}
```
