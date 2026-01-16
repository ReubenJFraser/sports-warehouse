# Sports Warehouse — Audit Handover for New Chat Window

## Purpose of This Handover

This handover is written to allow a **clean transition to a new chat window** without loss of context, assumptions, or audit discipline.

The next chat should treat this document as **authoritative context**, not as background prose.

This handover:

- declares the completion state of prior phases
- locks semantic assumptions already adopted
- defines what *not* to redo
- clearly scopes the next productive phase
- explains how to use existing README and AUDIT documents
- references the pruned project tree as the navigation index

---

## Current Audit Status (Authoritative)

### Phase 1 — Frontend Request Lifecycle  
**Status:** ✅ Complete  

- Entry point: `index.php` confirmed as sole routing authority
- Canonicalization rules verified
- Environment and base URL logic verified
- Root-level `layout.php` correctly classified as non-operative
- One correctness defect (HTML attribute bug) identified and fixed
- Findings fully recorded in AUDIT

### Phase 2A — Catalog Query Construction  
**Status:** ✅ Complete  

- Scope covered:
  - `/inc/catalog-query.php`
  - `/inc/color-where.php`
- All semantic risks identified
- Exactly **one invariant violation** recorded and locked:
  - SQL-side gender normalization (Classification 1 adopted)
- No additional hidden normalization or heuristic repair found
- Query structure otherwise clean, deterministic, and well-structured
- One non-blocking semantic ambiguity recorded (category name projection)
- No refactors performed beyond the single correctness fix already documented

**Phase 2A is formally closed.**

---

## Locked Semantic Assumptions (Carry Forward)

The following assumptions are **non-negotiable for all subsequent phases**:

1. **Canonical identity at rest**
   - Identity values (`section`, `gender`, `size_type`, etc.) must already be canonical
   - SQL, PHP, and helpers must not normalize or reinterpret identity

2. **Audit-first discipline**
   - No refactors during audit phases
   - Violations are recorded, not repaired
   - Remediation happens only after the full audit completes

3. **Markdown artifact discipline**
   - All audit text must be emitted as literal Markdown
   - When updating `.md` files, output must be inside a single fenced block:
     ```markdown
     ...
     ```

---

## Documents to Use in the New Chat

### 1. AUDIT Document (Primary)

Path (example):

README/AUDIT/2026-01-14-Architecture_and_Invariants.md


How to use:
- Append new findings only
- Do not rewrite prior findings
- Maintain existing structure and tone
- Treat as an append-only verification ledger

### 2. README / Invariant Files (Reference)

Key files:
- `README.md`
- Routing Invariants
- Architecture Invariants
- Any Excel → DB Contract documentation

How to use:
- As comparison baselines only
- Do not “improve” or reinterpret them during audit
- Deviations are findings, not prompts to edit the README

---

## Project Tree Navigation Aid

Use the **pruned project tree** as the authoritative map for file discovery and scoping.

File:
- `pruned-tree-folders.txt` :contentReference[oaicite:0]{index=0}

Guideline:
- Ignore large irrelevant folders (`images/`, `.venv/`, backups, exports)
- Focus on:
  - `/inc/*`
  - `/admin/*` (later phases)
  - `/scripts`, `/sql`, `/tools` (sync & tooling phase)

The pruned tree should be reattached in the new chat for convenience.

---

## What NOT to Redo in the New Chat

Do **not**:

- Re-audit `index.php` routing
- Re-audit `/inc/header.php`, `/inc/head.php`, `/inc/footer.php` framing logic
- Reopen Phase 2A decisions
- Re-debate Classification 1 vs alternatives
- Refactor or “clean up” code

Those areas are already settled or explicitly deferred.

---

## Recommended Next Phase

### Phase 3B — Rendering Pipeline: State Consumers (Recommended)

Rather than redoing rendering *frames*, proceed to **rendering consumers**, where state is actually used to produce markup.

Suggested starting files:

- `/inc/cards/product-grid.php`
- `/inc/cards/utils.php`
- `/inc/filter-ui.php`
- `/inc/filters/color-facets.php`
- Pagination markup
- Chips, breadcrumbs, and conditional UI elements

Primary questions for this phase:

- Does rendering leak routing or catalog semantics?
- Is presentation deterministic given the same state?
- Are helpers composable and context-free?
- Is UI behavior purely driven by canonical state?

This phase is expected to yield **new signal** without duplicating prior work.

---

## End of Handover

This document is intended to be pasted verbatim or attached as-is.  
It defines the boundary between completed work and the next audit phase.


