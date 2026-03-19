If a change may affect another system, STOP and ask before proceeding.

# Candidates System Contract

## A. System Identity

This workspace controls ONLY the candidate-facing and candidate-record experience.

This includes:
- candidate list and candidate detail UX
- candidate profile display
- candidate compliance-facing UI
- candidate availability-facing UI
- candidate-specific styling and interactions

## B. Hard Boundaries

Do NOT modify by default:
- Account Manager CRM UI
- school portal UI
- admin / ops UI
- shared booking lifecycle logic unless explicitly required for a candidate-side task

Do NOT change by default:
- global CSS used by other roles
- shared JS without isolating the change to candidate behavior
- role routing or permissions outside candidate scope

Do NOT introduce:
- candidate UI changes leaking into AM, school, or admin views

## C. Safe Extension Rules

Prefer:
- new candidate-scoped render functions
- candidate-prefixed classes such as `.cmn-candidate-*`
- isolated candidate wrappers around shared helpers

Avoid:
- editing school or AM renderers for convenience
- broad shared runtime edits when the issue is candidate-only
- touching admin/commercial screens during candidate work

If shared files are unavoidable:
- say that clearly before editing
- keep the diff tightly scoped to candidate behavior only

## D. Output Standard

Always:
- list the files touched
- explain why the scope is safe
- call out any shared file edits explicitly

Never:
- make silent cross-system changes
- assume a shared component is safe to edit without saying so
