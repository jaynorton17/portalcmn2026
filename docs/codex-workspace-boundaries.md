# Codex Workspace Boundaries

This repo stays as one deployable plugin. We reduce human error by splitting day-to-day work into separate git worktrees and separate Codex chats.

## Current workspace layout

- `workspace/am-crm`
  Path: `/home/jaynorton17/covermenowone/codex-workspaces/am-crm`
- `workspace/schools`
  Path: `/home/jaynorton17/covermenowone/codex-workspaces/schools`
- `workspace/candidates`
  Path: `/home/jaynorton17/covermenowone/codex-workspaces/candidates`
- `workspace/admin-ops`
  Path: `/home/jaynorton17/covermenowone/codex-workspaces/admin-ops`
- `workspace/shared-platform`
  Path: `/home/jaynorton17/covermenowone/codex-workspaces/shared-platform`

All of those worktrees were created from the current plugin repo state and point back to the same remote:

- Repo root: `/home/jaynorton17/covermenowone/wp-plugin/covermenowone-one`
- Remote: `origin https://github.com/jaynorton17/portalcmn2026.git`

## Why this is safer

- One Codex chat can stay focused on one bounded area.
- Shared deploy logic stays unchanged.
- Shared code is still shared.
- We avoid splitting the product into multiple repos before the code boundaries are mature enough.

## Boundary rules

### Account Manager CRM

Primary responsibility:

- restricted AM dashboard
- AM CRM shell
- AM school detail experience
- AM task and follow-up workflow
- AM-specific CRM styling

Avoid by default:

- school-side portal UX
- candidate-side operational flows
- admin/ops tooling

### Schools

Primary responsibility:

- school dashboard
- school live matches
- bookings visibility from the school side
- school-side request and cover workflow

Avoid by default:

- AM CRM surfaces
- admin reporting
- candidate admin flows

### Candidates

Primary responsibility:

- candidate list/detail UX
- candidate profile display
- candidate compliance-facing UI
- candidate availability-facing UI

Avoid by default:

- AM CRM dashboard
- school portal pages
- admin finance and ops pages

### Admin / Ops

Primary responsibility:

- admin-only views
- support/admin tooling
- invoicing/commercial ops surfaces
- internal controls and reporting

Avoid by default:

- AM UI refinements
- school and candidate day-to-day UX unless the task is explicitly shared

### Shared Platform

Primary responsibility:

- permissions
- shared routing glue
- shared helpers
- audit/scope logic
- cross-cutting CSS/JS only when multiple systems are genuinely affected

Avoid by default:

- visual changes that belong to only one workspace

## Shared file rule

These files often affect more than one workspace and should be treated as shared changes:

- `covermenowone-one.php`
- `frontend.js`
- `frontend.css`
- `frontend-am-crm.css`
- `livechat-widget.js`
- `livechat-widget.css`

If a task in a bounded workspace needs one of those files:

1. Call out that the change touches a shared file.
2. Keep the edit scoped to the intended system branch only.
3. Merge that branch carefully rather than assuming the file belongs to a single workspace.

## Suggested Codex workflow

1. Open the matching worktree path as the chat cwd.
2. Keep the request within that workspace boundary.
3. If the task crosses boundaries, either:
   - switch to the `shared-platform` workspace, or
   - finish one bounded change first and then open the other workspace separately.

## Merge and deploy workflow

1. Work inside the relevant worktree branch.
2. Commit and push from that branch.
3. Merge back intentionally when ready.
4. Keep deploy coming from the real plugin repo state you want live.

## Recreating the worktrees

Use:

```bash
cd /home/jaynorton17/covermenowone/wp-plugin/covermenowone-one
./scripts/setup_codex_workspaces.sh
```

The script is idempotent and will skip worktrees that already exist.
