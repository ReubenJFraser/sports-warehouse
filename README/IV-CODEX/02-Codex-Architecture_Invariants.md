```markdown
# Sports Warehouse — Architecture Invariants

## Purpose

This README defines the non-negotiable architecture invariants for the Sports Warehouse project.

Its purpose is to prevent architectural drift, accidental coupling, destructive refactors, unstable routing, unsafe asset handling, and agent-introduced breakage.

This document is intended for Codex, ChatGPT, and human operators working on the project structure, admin system, frontend system, routing, asset loading, and automation-supported features.

The project has moved beyond its initial conservative stabilization phase, but these architecture invariants remain authoritative.

Guardrailed implementation velocity is permitted only inside these boundaries.

---

## Scope

This README covers:

- filesystem responsibility boundaries
- routing authority
- asset path stability
- admin and frontend separation
- data authority layering
- automation reversibility
- fail-loud behaviour
- AI agent constraints
- schema change discipline
- architectural decision guardrails
- the relationship between conservative stabilization and guardrailed implementation velocity

This README does not cover:

- detailed Codex GitHub PR workflow
- editorial data authority rules
- importer behaviour
- enforcement candidate rules
- routing implementation details
- handover procedures between chat sessions

Those responsibilities are governed separately by:

- `README/IV-CODEX/01-Codex-Behavioural_Rules.md`
- `README/IV-CODEX/03-Codex-Routing_Invariants.md`
- `README/IV-CODEX/04-Codex-GitHub_PR_Workflow.md`
- `admin/ENFORCEMENT_CANDIDATE_REGISTER.md`

---

## Current Architecture Mode

The Sports Warehouse project has completed the initial conservative architecture-stabilization phase.

That earlier phase was necessary while:

- project boundaries were still being discovered
- admin and frontend responsibilities were being separated
- routing behaviour was being stabilized
- asset path conventions were being clarified
- AI-assisted development risk was high
- automation authority was not yet bounded

The active architecture mode is now:

**guardrailed implementation velocity**

This means implementation may proceed in meaningful bounded slices when the relevant architecture boundaries are already known.

This does not weaken the invariants in this document.

It changes the delivery expectation from excessive caution to productive implementation inside stable constraints.

Codex must not use architecture invariants as an excuse for unproductive fragmentation when the implementation path is already clear.

Codex must also not use implementation velocity as an excuse to alter architecture boundaries.

---

## Conceptual Roles

### Filesystem

The filesystem defines responsibility boundaries.

It is not merely a storage convenience.

Existing directory structure communicates architectural ownership and deployment assumptions.

---

### Routing

Routing defines how requests reach the correct project surface.

Routing must remain centralized and predictable.

Routing must not be inferred ad hoc by templates, components, or JavaScript.

---

### Assets

Assets must resolve predictably across local, hosted, admin, and frontend contexts.

Asset paths are part of the runtime contract.

---

### Admin System

The admin system may inspect, curate, and override.

The admin system must not silently rewrite frontend responsibility or bypass defined authority layers.

---

### Frontend System

The frontend system renders public-facing behaviour.

It must not perform admin decisions or mutate admin authority state.

---

### Automation

Automation may assist, rank, suggest, or prepare.

Automation must remain inspectable, reversible, and subordinate to human authority where manual curation is defined.

---

### AI Agents

Codex and ChatGPT are constrained actors.

They may implement bounded changes inside approved architecture.

They must not reinterpret architecture boundaries.

---

## Invariant 1 — Filesystem Structure Is Canonical

The filesystem structure defines responsibility boundaries.

Existing files and directories must not be moved for aesthetic cleanup.

Allowed actions:

- add new files inside existing directories when scope justifies them
- add new directories only when explicitly approved
- modify files in place when the change belongs to that responsibility area

Forbidden actions:

- moving existing files
- flattening directories
- renaming folders for visual neatness
- relocating admin code into frontend areas
- relocating frontend code into admin areas
- restructuring deployment paths without explicit approval

Guardrailed implementation velocity does not authorize filesystem redesign.

---

## Invariant 2 — Routing Logic Is Centralized

Routing must be defined in one authoritative place or within clearly established route-handling conventions.

Codex must not duplicate routing logic across unrelated files.

Forbidden actions:

- shadow routers
- ad hoc URL inference
- route guessing in JavaScript
- conditional routing hidden inside templates
- page-specific hacks that contradict routing invariants

If routing changes are required, `README/IV-CODEX/03-Codex-Routing_Invariants.md` must be consulted first.

---

## Invariant 3 — Asset Paths Are Explicit and Stable

Asset paths must be:

- explicit
- predictable
- stable across environments
- compatible with the local project base path

The local project runs under:

`/sports-warehouse-home-page/`

Codex must not assume root deployment when the current project context requires base-path awareness.

Forbidden actions:

- relative path hacks
- environment-dependent guessing
- hard-coded shortcuts that break localhost
- duplicating `/admin/`
- dropping required `/admin/`
- constructing asset paths from unverified assumptions

If JavaScript or PHP changes URL construction, the actual local browser path must be tested.

---

## Invariant 4 — Admin and Frontend Are Strictly Separated

Admin logic and frontend logic must remain separate.

Admin may:

- inspect
- curate
- override
- audit
- expose review tools
- support manual authority

Admin may not:

- silently rewrite frontend rendering rules
- bypass governance boundaries
- introduce enforcement without register authority
- mutate public behaviour outside approved workflows

Frontend may not:

- perform admin decisions
- mutate admin state
- bypass admin overrides
- infer editorial authority

Shared helpers are permitted only when they preserve these responsibility boundaries.

---

## Invariant 5 — Data Authority Is Layered

Authority order is:

1. Excel — editorial truth
2. Database — execution mirror
3. Code — behaviour and presentation

Codex must not allow lower layers to invent truth for higher layers.

Forbidden actions:

- code inventing missing editorial data
- database defaults masking missing Excel authority
- frontend fallbacks hiding data contract failures
- admin automation silently normalizing meaning
- inferred classification without source authority

Implementation may improve display or workflow, but not editorial truth.

---

## Invariant 6 — Automation Must Be Inspectable, Reversible, and Subordinate

Automation is allowed when it supports inspection and curation.

Automation must allow:

- inspection
- override
- rollback
- review
- human correction

This applies especially to:

- hero image selection
- ranking algorithms
- shortlist generation
- diagnostic output
- automated defaults

Example:

Hero image logic may rank images automatically, but the human editor must be able to see the ranking, challenge the shortlist, and preserve manual authority.

Automation suggests.

Manual curation decides.

---

## Invariant 7 — Smart Systems Must Fail Loudly

If something cannot be determined reliably, the system must fail clearly rather than guess silently.

Forbidden actions:

- best-effort guesses that mask uncertainty
- silent fallbacks that appear authoritative
- heuristic substitution without visible status
- hiding missing data behind generic defaults

A visible incomplete state is preferred to a false complete state.

---

## Invariant 8 — AI Agents Are Constrained Actors

AI agents, including Codex and ChatGPT, must respect this document.

AI agents may:

- inspect
- explain
- propose
- implement bounded approved changes
- refactor within explicit scope
- improve implementation velocity inside guardrails

AI agents may not:

- reinterpret invariants
- simplify constraints
- move architecture boundaries
- invent routing conventions
- redesign filesystem structure
- introduce hidden coupling
- broaden scope without approval

If a requested implementation conflicts with this document, the conflict must be surfaced before action.

---

## Invariant 9 — Schema Changes Follow Contract, Not Convenience

Schema changes must follow approved editorial and database contract processes.

Schema changes must:

- respect the Excel to database authority chain
- be explicitly documented
- be mechanically verifiable
- be introduced sequentially across environments

Forbidden actions:

- opportunistic column additions
- quick schema fixes for convenience
- divergent localhost and production assumptions
- schema drift introduced by feature implementation

UI or admin implementation does not authorize schema changes unless schema change is explicitly within scope.

---

## Invariant 10 — Consistency Beats Cleverness

This project prioritizes:

- consistency
- debuggability
- traceability
- boring clarity
- maintainable structure

over:

- novelty
- abstraction for its own sake
- clever shortcuts
- invisible magic
- theoretical elegance

If a choice exists between a clever solution and a boring solution that preserves architecture, choose the boring solution.

---

## Invariant 11 — Guardrailed Velocity Is Permitted Inside Stable Boundaries

The project no longer requires ultra-conservative micro-staging for every implementation decision.

Codex may implement meaningful bounded slices when:

- architecture boundaries are known
- routing impact is clear
- affected files belong to the stated responsibility area
- no schema or enforcement authority is changed
- PR review remains human-controlled
- local verification remains required

A bounded implementation slice may touch multiple related files when they are part of one coherent feature.

Example:

A UI slice may reasonably include:

- one PHP template change
- one CSS update
- one JavaScript update

provided the change is coherent, bounded, and reviewable.

---

## Invariant 12 — Planning-Only Architecture Work Must Be Justified

Planning-only work is appropriate when:

- architecture is unclear
- routing impact is unknown
- filesystem changes are proposed
- schema change is under consideration
- enforcement boundaries are being introduced
- automation authority is being changed
- implementation risk is high

Planning-only work is not required when:

- the affected surface is known
- the relevant boundaries are documented
- the implementation is small and coherent
- the change is reversible
- runtime verification is available

The default current mode is bounded implementation within guardrails.

---

## Procedural Rule — When Architecture Is Touched

When a change affects filesystem structure, routing, asset loading, admin/frontend boundaries, or design-system scope:

1. identify the affected invariant
2. inspect the relevant README
3. state whether the change stays inside existing boundaries
4. avoid filesystem/routing redesign unless explicitly approved
5. verify locally after merge

If the change cannot be shown to stay inside existing boundaries, stop and request architectural review.

---

## Procedural Rule — When UI Implementation Is Bounded

For bounded UI implementation, Codex may proceed without a separate planning-only stage when:

1. the target file or page is known
2. the endpoint or data source is already defined
3. the change is read-only or locally reversible
4. no schema change is involved
5. no enforcement authority is introduced
6. no routing convention is altered
7. local browser testing is possible

This rule supports guardrailed implementation velocity.

---

## Known Gaps and Open Questions

The project may still need future clarification around:

- when a helper belongs in `inc/` versus page-local code
- when admin-specific JavaScript should become its own module
- how much UI refinement belongs in one PR
- when a feature slice becomes large enough to require planning first
- how future deployment contexts may alter asset path expectations

These gaps do not authorize guessing.

They identify areas where explicit review may still be needed.

---

## Non-Goals

This document does not authorize:

- architectural redesign
- filesystem reorganization
- routing decentralization
- admin/frontend coupling
- schema convenience changes
- enforcement expansion without register authority
- uncontrolled AI refactoring
- endless planning where implementation is already bounded

---

## Guiding Principles

The architecture rules exist to preserve project coherence while allowing productive development.

The guiding principles are:

- protect responsibility boundaries
- preserve routing clarity
- keep assets predictable
- keep admin and frontend authority separate
- make automation inspectable
- fail loudly when truth is unavailable
- use Codex for bounded implementation, not architectural improvisation
- prefer meaningful delivery over excessive ceremony
- prefer boring consistency over clever abstraction

Architecture invariants remain strict.

Implementation tempo may now increase inside those strict boundaries.
```










