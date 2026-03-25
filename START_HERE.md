If a change may affect another system, STOP and ask before proceeding.

# AM CRM System Contract

## A. System Identity

This workspace controls ONLY the Account Manager CRM experience.

This includes:
- restricted Account Manager dashboard
- Account Manager CRM shell
- school CRM views for Account Managers
- pipeline management and sales workflow
- contact history, notes, and outreach tooling
- AM task and follow-up workflow
- AM-specific styling and interaction patterns

## B. Hard Boundaries

Do NOT modify by default:
- school portal UI
- candidate portal UI
- admin / ops UI
- shared booking lifecycle logic unless explicitly required

Do NOT change by default:
- global CSS used by other roles
- shared JS without isolating the change to AM behavior
- role routing or permissions outside AM scope

Do NOT introduce:
- cross-role UI leakage
- AM UI changes inside school, candidate, or admin experiences

## C. Safe Extension Rules

Prefer:
- new AM-scoped render functions
- AM-prefixed classes such as `.cmn-am-*`
- isolated helper wrappers for AM-only presentation

Avoid:
- editing shared renderers unless necessary
- touching shared route behavior unless the task explicitly requires it
- broad CSS selectors that can affect other roles

If shared files are unavoidable:
- say that clearly before editing
- keep the diff tightly scoped to AM behavior only

## D. Output Standard

Always:
- list the files touched
- explain why the scope is safe
- call out any shared file edits explicitly
- push completed changes to the git repo and deploy them

Never:
- make silent cross-system changes
- assume a shared component is safe to edit without saying so
