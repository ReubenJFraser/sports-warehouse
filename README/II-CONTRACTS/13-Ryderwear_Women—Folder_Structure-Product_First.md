# Ryderwear Women — Folder Structure Contract (Product-First)

## 1. Purpose of This README

This document defines the **authoritative contract** for the **Ryderwear Women folder structure**.

The folder system is a **product-first, structure-only model** whose purpose is to:

- enumerate real product items deterministically
- encode product reality (construction, cut, rise, fabric, etc.)
- provide a stable, navigable authoring surface
- remain independent of Excel column structure and naming rules

This README exists to **prevent semantic drift** between:
- filesystem structure
- Excel authoring
- future automation (filtering, personalization, AI)

This is a **governance document**, not a guide or suggestion.

---

## 2. Scope

This contract applies to:

- `images/Clothing/Ryderwear/Women/**`
- all Ryderwear Women collections (e.g. NKD)
- all current and future folder trees under this path

This document does **not** define:

- Excel column meanings
- item naming rules
- variant naming conventions
- retail presentation logic
- colour usage in Excel

Those are governed elsewhere.

---

## 3. Core Design Principles

### 3.1 Product-First, Not Retail-First

Folders represent **product reality**, not how products are sold, marketed, or grouped online.

A folder exists because it reflects a **real, differentiating product axis**, such as:

- garment type
- construction style
- construction technique
- cut / neckline
- rise
- length
- fabric family

Folders do **not** exist to:
- simplify browsing
- mirror website navigation
- optimise merchandising

---

### 3.2 Structure-Only Model

The folder tree is **structure-only**.

- Folder names encode *what a product is*
- They do **not** encode:
  - pricing
  - SKUs
  - marketing names
  - Excel row structure

Excel is downstream from the folder system, not the other way around.

---

### 3.3 Deterministic Enumeration

Every **terminal branch above colour** corresponds to **exactly one product item**.

- Enumeration is performed by reading the tree
- Products are never inferred from names alone
- The tree must be sufficient to enumerate products without guesswork

If a product cannot be enumerated deterministically from the tree, the tree is invalid.

---

## 4. Collections

Each Ryderwear collection (e.g. `NKD`) is:

- isolated from other collections
- enumerated independently
- governed by the same structural rules

Collections must **never be mixed** during enumeration or authoring.

---

## 5. Folder Axes and Their Meaning

### 5.1 Garment Type

Top-level garment groupings (e.g. `Bodysuit`, `Bottoms`, `Tops`) exist to:

- anchor product reality
- prevent cross-garment ambiguity
- scope which axes apply downstream

They are **not** retail categories.

---

### 5.2 Construction Axis

The `Construction` folder defines **primary structural identity**.

Examples:
- `V`
- `Pocket`
- `Flared`
- other structural builds

Rules:
- Construction styles live directly under `Construction`
- They represent first-order product identity
- They must be consistent within a garment type

---

### 5.3 Scrunch as a Technique (Not Primary)

`Scrunch` is an **applied construction technique**, not a primary construction style.

Rules:
- Scrunch **never exists on its own**
- Scrunch is always nested under a construction style
- Absence of `Scrunch` implies a non-scrunch version

Example:

Construction
└── V
└── Scrunch


---

### 5.4 Invisible as a Scrunch Variant

`Invisible` has **no standalone meaning**.

Rules:
- `Invisible` exists **only** under `Scrunch`
- It represents an execution variant of scrunch
- There is no “Classic” label — standard scrunch is implied by absence

Valid:

Scrunch
└── Invisible

Invalid:

Construction
└── Invisible

---

### 5.5 Pocket as Construction Style

`Pocket` is treated as a **primary construction style**, not a feature.

Rules:
- `Pocket` lives under `Construction`
- Scrunch may be applied to Pocket
- Pocket must not drift between axes

Example:

Construction
└── Pocket
└── Scrunch


---

## 6. Colour Folders

Colour folders:

- are **not** product-defining
- must never be used to infer product identity
- must not be relied upon for enumeration

For enumeration and governance:
- Colours are ignored
- Structure-only exports remove colour folders

---

## 7. Relationship to Excel (Explicit Separation)

The folder structure:

- does **not** define Excel columns
- does **not** define item names
- does **not** imply Excel derivation logic

Excel rows are **derived from** the folder structure, but are governed by:

- separate semantic contracts
- explicit column rules
- deliberate naming decisions

Folder structure answers:
> “What products exist?”

Excel answers:
> “How are those products represented?”

These systems must never be conflated.

---

## 8. Change Discipline

Any structural change must satisfy all of the following:

- no loss of enumerated products
- no duplication of product reality
- no axis role confusion
- no retroactive reinterpretation

Re-parenting is allowed.  
Silent semantic shifts are not.

---

## 9. Authority

This README is **authoritative** for Ryderwear Women folder structure.

If another document conflicts with this one:
- this document takes precedence **for folder structure**
- the conflict must be resolved explicitly

---


## 10. Current Interpretation Note (2026-05-26)

This historical governance contract remains valid and authoritative for Ryderwear Women folder structure.

For current cross-system interpretation, read together with Contracts 15, 22, 23, and current entrypoint Contract 24.

Clarifications applied now:
- `model_id` is the canonical product identity anchor across ProductDB/DB/image-folder matching.
- Numeric media filenames (`01.png`, `02.png`, etc.) are valid only inside a correctly governed product/colour branch.
- Folder hierarchy is semantic and governed by structural axes; it must not be generated by blindly splitting `model_id` underscores.
- Parent folders carry broad taxonomy and governed axes; lower branches should not redundantly restate already-encoded parent tokens.
- Colour/variant folders remain terminal or near-terminal before media files.

## 11. Status

This document reflects the **post-refactor, stabilized model** for Ryderwear Women.

Further changes require:
- conscious intent
- explicit documentation
- validation against existing product enumeration
