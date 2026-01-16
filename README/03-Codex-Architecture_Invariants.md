# Sports Warehouse — Architecture Invariants

## Purpose (Read First)

This document defines **non-negotiable architecture constraints** for the Sports Warehouse project.

These rules exist to prevent:
- accidental architectural drift
- destructive “helpful” refactors
- agent-introduced breakage
- silent coupling between unrelated systems

If any instruction, tool, or agent suggestion conflicts with this document:

> **This document overrides everything else.**

---

## What This Document Is (and Is Not)

### This document is:

- A hard constraint layer
- A safety rail for humans *and* AI agents
- A definition of what **must not change**

### This document is not:

- A style guide
- A refactor wishlist
- A performance optimization plan
- A suggestion set

---

## Invariant 1 — Filesystem Structure Is Canonical

### Rule

The filesystem structure defines responsibility boundaries.

- Frontend code lives where it lives
- Admin code lives where it lives
- Assets live where they live

**Do not move files “for cleanliness.”**

### Consequences

Routing, asset resolution, admin isolation, and deployment scripts depend on current structure.

### Allowed

- Add new files **within** existing directories
- Add new directories **only when explicitly approved**

### Forbidden

- Moving existing files
- Flattening directories
- Renaming folders for aesthetics

---

## Invariant 2 — Routing Logic Is Centralized

### Rule

Routing is defined in **one place only**.

- PHP routing logic must not be duplicated
- JS must not infer routing rules
- No file may “guess” URLs

### Forbidden

- Shadow routers
- Conditional routing in templates
- URL logic inside components

---

## Invariant 3 — Asset Paths Are Absolute and Stable

### Rule

All asset paths are:

- Explicit
- Predictable
- Stable

No runtime guessing.

### Forbidden

- Dynamic asset path construction
- Relative path hacks
- Environment-dependent path logic

---

## Invariant 4 — Admin and Frontend Are Strictly Separate

### Rule

Admin logic and frontend logic **must not bleed into each other**.

### Admin may:

- Inspect
- Override
- Curate

### Admin may NOT:

- Rewrite frontend logic
- Change rendering behavior directly
- Inject UI logic

### Frontend may NOT:

- Perform admin decisions
- Mutate admin state
- Bypass admin overrides

---

## Invariant 5 — Data Authority Is Layered

This invariant ties directly to the **Excel → DB Contract**.

### Authority order

1. **Excel** — editorial truth
2. **Database** — execution mirror
3. **Code** — behavior only

### Forbidden

- Code inventing missing data
- Database auto-correcting editorial intent
- Silent fallbacks that mask bad data

---

## Invariant 6 — Automation Must Be Reversible

### Rule

Any automation must allow:

- inspection
- override
- rollback

This applies especially to:
- hero image selection
- ranking algorithms
- automated defaults

### Example

Hero image logic may rank images automatically, but:
- the human must see the ranking
- the human may override the choice
- the system must remember the override

---

## Invariant 7 — “Smart” Systems Must Fail Loudly

### Rule

If something cannot be determined reliably:

> **It must fail, not guess.**

### Forbidden

- “Best effort” guesses
- Silent fallbacks
- Heuristic masking

---

## Invariant 8 — AI Agents Are Constrained Actors

### Rule

AI agents (including Codex / ChatGPT):
- Must respect this document
- Must not reinterpret invariants
- Must stop when a constraint is encountered

### Agents may:

- Explain
- Inspect
- Propose

### Agents may NOT:

- Refactor architecture
- Simplify constraints
- “Improve” structure

---

## Invariant 9 — Schema Changes Follow Contract, Not Convenience

### Rule

All schema changes must:
1. Respect the Excel → DB Contract
2. Be explicitly documented
3. Be mechanically verifiable

### Forbidden

- Opportunistic column additions
- “Just one quick field” changes
- Divergence between environments

---

## Invariant 10 — Consistency Beats Cleverness

### Rule

This project prioritizes:
- consistency
- debuggability
- traceability

…over:
- novelty
- abstraction
- theoretical elegance

If a choice exists between:
- a clever solution
- a boring, obvious one

**Choose boring.**

---

## Enforcement Policy

If any invariant is violated:
- Stop work immediately
- Roll back the change
- Re-read this document
- Resume only with explicit justification

---

## Final Statement

These invariants exist because:
- the project is complex
- the system is evolving
- AI assistance is powerful but dangerous

They are not optional.  
They are not “for now.”  
They are **foundational**.

---

## Status

- **Authoritative**
- **Non-negotiable**
- **Codex-enforced**
- **Human-enforced**












