If a change may affect another system, STOP and ask before proceeding.

# Shared Platform System Contract

## A. System Identity

This workspace controls ONLY shared platform behavior used by multiple systems.

This includes:
- shared permissions and scope logic
- shared routing glue
- shared helpers and platform utilities
- audit and cross-cutting platform behavior
- truly shared CSS/JS only when multiple systems are genuinely affected

## B. Hard Boundaries

Do NOT use this workspace for convenience.

Do NOT modify a single-system UI here if it clearly belongs to:
- Account Manager CRM
- school portal
- candidates
- admin / ops

Do NOT introduce:
- generic shared abstractions for a problem that belongs to one system only
- cross-system side effects without explicit acknowledgement

## C. Safe Extension Rules

Prefer:
- minimal shared changes with clear downstream impact
- isolated helpers and wrappers
- explicit notes when a shared file is being edited

Avoid:
- visual redesign work that belongs to a single workspace
- broad refactors that increase coupling
- hidden behavior changes across multiple roles

If a task can live in one bounded workspace instead:
- stop
- switch to that workspace
- do not continue here

## D. Output Standard

Always:
- list the files touched
- explain which systems are affected
- explain why the change belongs in shared-platform

Never:
- make silent cross-system changes
- assume a shared component is safe to edit without saying so
