# Guided Query Construction and Future AI Integration — Two-Step Design Model

## Purpose

This README defines the **two-step approach** to search and query functionality for the Sports Warehouse project.

Its purpose is to:
- document the design intent clearly and early,
- establish guided query construction as the **primary, non-AI foundation**, and
- explicitly defer AI integration as a **future, optional extension**.

This document exists to prevent premature AI coupling and to ensure the search system remains correct, auditable, and usable even if AI is never added.

---

## Scope

This README covers:
- the conceptual design of search and query interaction,
- the separation between deterministic query construction and AI assistance,
- how this system fits into the existing Sports Warehouse search bar,
- the intended future role of AI as an augmentation layer.

This README does **not** cover:
- UI styling or frontend implementation details,
- database indexing or performance optimisation,
- natural-language parsing,
- any current AI model selection or training.

---

## Design Context

The Sports Warehouse project is being developed as part of a **Diploma in Back- and Front-End Website Development**.

Completion of this diploma requires:
- a functional, non-experimental system,
- predictable and explainable behaviour,
- features that can be assessed without reliance on AI.

AI integration is therefore treated as **out of scope for the current qualification**, but intentionally planned so it can be developed later—potentially as part of a future diploma in Algorithms or Artificial Intelligence.

---

## Overview of the Two-Step Model

The search system is designed in **two explicit stages**:

1. **Guided Query Construction (Required, Immediate)**
2. **AI-Assisted Query Interpretation (Optional, Future)**

Only Step 1 is to be implemented during the current Sports Warehouse development phase.

Step 2 is intentionally deferred.

---

## Step 1 — Guided Query Construction (Primary System)

### Description

Guided query construction is a **deterministic, UI-driven search system** in which users assemble valid queries using structured controls rather than free-text guessing.

The system:
- exposes only valid concepts (collections, subCategories, variants, constructions),
- prevents invalid or ambiguous queries,
- makes scope expansion explicit,
- and produces queries that are explainable and auditable.

This step does **not** require AI.

---

### Core Characteristics

- Dropdowns, autocomplete fields, and constrained inputs
- Explicit intent selection (e.g. “Show me all”, “Find”)
- Concept-aware suggestions based on known data
- Confirmation when a choice expands scope (e.g. construction families)
- Clear preview of the resulting query before execution

All behaviour is rule-based and driven by existing schema and filesystem-derived structure.

---

### Relationship to Existing Search Bar

The guided query system is intended to be:
- integrated into the existing Sports Warehouse search bar,
- progressively enhanced rather than replaced,
- usable without changing the underlying data model.

Initially, the search bar may:
- open a guided popup or panel,
- allow users to switch between basic text search and guided mode,
- surface guided suggestions even when typing freely.

---

### Why This Step Comes First

Guided query construction is required because it:
- works without AI,
- enforces data integrity,
- exposes the true structure of the catalogue,
- and creates a stable interface contract.

If this step is incorrect or incomplete, AI integration later would amplify errors rather than reduce them.

---

## Step 2 — AI-Assisted Query Interpretation (Deferred)

### Description

AI assistance is a **future augmentation layer**, not a replacement for guided queries.

When introduced, AI may:
- translate natural language into guided query selections,
- suggest refinements or alternatives,
- auto-complete multi-step queries,
- surface patterns across collections or brands.

AI will operate **on top of** the guided system, never underneath it.

---

### Deferred by Design

AI integration is explicitly deferred because:
- it is not required for the current diploma,
- it introduces assessment and reproducibility risks,
- and it depends on Step 1 being complete and correct.

This README exists to make that deferral **intentional and documented**, not accidental.

---

## Conceptual Roles

- **Guided Query System**
  - Authoritative
  - Deterministic
  - Always available

- **AI Layer (Future)**
  - Assistive
  - Optional
  - Non-authoritative

The system must always be able to explain:
> *“This is what you asked for, and this is why these results were returned.”*

AI must never obscure that explanation.

---

## Known Gaps and Open Questions

- Exact UI layout for guided query controls
- Whether guided queries replace or coexist with free-text search
- How query previews are best displayed to users
- How AI suggestions will be surfaced without overriding user intent

These are intentionally unresolved and deferred.

---

## Guiding Principles and Invariants

- The search system must function correctly **without AI**
- All queries must be valid, explainable, and auditable
- Scope expansion must be explicit, not implicit
- AI, if added, must map to existing concepts rather than invent new ones
- Guided query construction is the foundation; AI is an enhancement

This invariant must be preserved even as the system evolves.
