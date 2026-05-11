# Image Path & Ingestion Governance Contract

## 1. Purpose of This Contract

This document defines the **unified governance framework** for product image paths and their lifecycle from authoring through ingestion in the Sports Warehouse system.

Its purpose is not to restate rules already defined elsewhere, but to **bind them together** into a single, end-to-end contract that prevents drift, ambiguity, and silent normalization across tools, environments, and phases of work.

This is a **governance wrapper**, not a specification rewrite.

---

## 2. Scope

This contract applies to:

- all product image and media paths authored in Excel
- all validation, export, and ingestion steps downstream of Excel
- all systems that read, persist, or render product images (admin, frontend, audit)

This contract does **not** define:

- image scoring or hero selection logic
- UI layout or presentation rules
- migration or cleanup strategies for legacy data
- filesystem synchronization procedures

Those concerns are governed elsewhere.

---

## 3. Composition of the Contract (Authoritative)

This governance contract is composed of **three inseparable parts**, each of which is authoritative within its defined boundary.

### Part A — Column Specification  
**File:** `Part_A-Column_Specification.md`

Defines:

- the complete semantic meaning of each column
- the canonical ordering of columns
- domain-specific applicability (wearables, footwear, non-wearables)
- why column order exists and must not be altered

Part A defines **what the data means**.

---

### Part B — Horizontal Projection & Validation Wiring  
**File:** `Part_B-Horizontal_Projection_&_Validation_Wiring.md`

Defines:

- how Part A is projected into a single Excel worksheet
- how validation is enforced via data validation, dropdowns, and rules
- how domains coexist in one sheet without semantic collision
- row-level acceptance and rejection criteria

Part B defines **how correctness is enforced at the source of truth**.

---

### Part C — Export & Ingestion Contract  
**File:** `Part_C-Export_&_Ingestion_Contract.md`

Defines:

- how Excel data is exported
- how NULLs, empty values, and omissions are interpreted
- how ingestion must behave (reject-on-error, no normalization)
- what downstream systems are explicitly forbidden from doing

Part C defines **how correctness is preserved once Excel ends**.

---

## 4. Inseparability Rule (Critical)

Parts A, B, and C are **not optional modules**.

Any system, workflow, or change proposal that references image paths must comply with **all three parts simultaneously**.

Implementing one part while ignoring or weakening another is a violation of this contract.

---

## 5. Authority & Precedence

The authority chain enforced by this contract is:

1. **Excel** — editorial source of truth  
2. **Export Artifact** — frozen representation of Excel truth  
3. **Local MySQL (DBeaver)** — execution mirror  
4. **Cloudways MySQL** — deployment mirror  
5. **Filesystem** — physical realization  
6. **Admin / Frontend UI** — read-only consumers  

No downstream layer may reinterpret, repair, infer, or normalize data authored upstream.

---

## 6. Prohibited Behaviors (Global)

Across all parts of the system, the following behaviors are explicitly forbidden:

- silent path normalization
- inferring missing folder segments
- guessing gender, collection, or category
- repairing legacy paths at runtime
- rewriting paths during ingestion
- “helpful” fallbacks that conceal invalid data

Errors must surface at the earliest possible boundary and be corrected **upstream**, not downstream.

---

## 7. Change Control & Versioning

Changes to any of the following:

- column meanings
- canonical folder order
- domain rules
- export semantics
- ingestion failure behavior

must be treated as **contract changes**, not refactors.

Such changes require:

- explicit documentation updates
- cross-review of Parts A–C
- version incrementing of the affected documents

Unilateral or implicit changes are not permitted.

---

## 8. Relationship to Other Documentation

This contract sits within:

README/
└── II-CONTRACTS/
└── 00-Design/

It is a **design-time governance artifact**, not an operational or code-level document.

Operational procedures live under **III-OPERATIONS**.  
Audit artifacts live under **V-AUDIT**.  
Implementation details live under **VI-DEVELOPMENT**.

---

## 9. Guiding Principle

Excel defines truth.  
Contracts preserve meaning.  
Validation prevents drift.  
Ingestion must not guess.

This contract exists to make complexity **legible, enforceable, and durable**.

## 10. Image Path Participation Boundary (Invariant)

Not all attributes that belong to the governed item schema participate in image path construction.

This distinction is intentional and foundational.

The governed item schema defines the complete semantic description of a product for cataloging, filtering, pricing, and frontend behavior.

Image paths, by contrast, encode only those attributes that require **distinct image folders on disk**.

These two sets overlap, but they are not equivalent.

---

### Participation Rule

An attribute participates in image path construction **if and only if** it requires a distinct image set.

Attributes that do not require distinct image folders must not appear in image paths, even if they are first-class item data.

Image file distinctness is expressed entirely through **folder structure**.  
Filenames are intentionally generic and non-semantic.

---

### Governance Consequence

Downstream systems must not:

- infer image path segments from item-level metadata
- introduce additional folder levels for non-participating attributes
- encode item semantics in filenames
- compensate for missing or excluded path segments

Violations must be corrected at the source of truth, not normalized during ingestion or rendering.

This invariant applies across all product domains: apparel, footwear, and non-wearable goods.
