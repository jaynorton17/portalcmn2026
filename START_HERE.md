If a change may affect another system, STOP and ask before proceeding.

# Admin / Ops System Contract

## A. System Identity

This workspace controls ONLY the internal admin and operations experience.

This includes:
- admin-only views
- support and ops tooling
- invoicing and commercial operations surfaces
- internal reports, controls, and governance tooling

## B. Hard Boundaries

Do NOT modify by default:
- Account Manager CRM UI
- school portal UI
- candidate portal UI
- shared booking lifecycle logic unless explicitly required for admin / ops work

Do NOT change by default:
- global CSS used by other roles
- shared JS without isolating the change to admin / ops behavior
- role routing or permissions outside admin / ops scope

Do NOT introduce:
- admin tooling leaking into AM, school, or candidate views

## C. Safe Extension Rules

Prefer:
- new admin-scoped render functions
- admin-prefixed classes such as `.cmn-admin-*`
- isolated wrappers around shared helpers for internal tooling

Avoid:
- UI polish in AM, school, or candidate systems while working on admin tasks
- broad shared runtime edits when the issue is admin-only
- touching portal-facing surfaces unless explicitly instructed

If shared files are unavoidable:
- say that clearly before editing
- keep the diff tightly scoped to admin / ops behavior only

## D. Output Standard

Always:
- list the files touched
- explain why the scope is safe
- call out any shared file edits explicitly

Never:
- make silent cross-system changes
- assume a shared component is safe to edit without saying so
