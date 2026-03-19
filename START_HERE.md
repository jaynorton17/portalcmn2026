If a change may affect another system, STOP and ask before proceeding.

# Schools System Contract

## A. System Identity

This workspace controls ONLY the school-facing portal and school operational experience.

This includes:
- school dashboard
- school live matches and candidate cards
- school request and cover workflow
- school-side booking and operational views
- school profile and school-side interaction patterns

## B. Hard Boundaries

Do NOT modify by default:
- Account Manager CRM UI
- candidate portal UI
- admin / ops UI
- shared booking lifecycle logic unless explicitly required for a school-side task

Do NOT change by default:
- global CSS used by other roles
- shared JS without isolating the change to school-facing behavior
- role routing or permissions outside school scope

Do NOT introduce:
- school UI changes leaking into AM, candidate, or admin views

## C. Safe Extension Rules

Prefer:
- new school-scoped render functions
- school-prefixed classes such as `.cmn-school-*`
- isolated school-side wrappers around shared helpers

Avoid:
- editing AM-specific renderers
- broad changes to shared runtime files when the issue is school-only
- cross-role visual cleanups while working on school UX

If shared files are unavoidable:
- say that clearly before editing
- keep the diff tightly scoped to school-facing behavior only

## D. Output Standard

Always:
- list the files touched
- explain why the scope is safe
- call out any shared file edits explicitly

Never:
- make silent cross-system changes
- assume a shared component is safe to edit without saying so
