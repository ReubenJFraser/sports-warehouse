# Collection-by-Collection Execution Model  
## Folder Tree → Canonical Excel Transfer Workflow

---

## 1. Purpose of This README

This document defines the **repeatable execution model** used to translate a product-first folder tree into canonical Excel rows.

It exists to ensure that:

- each collection is processed consistently
- no product items are missed or duplicated
- derivation rules are applied deterministically
- future chat sessions (or other AI tools) can resume work without re-litigation

This is both:
- a **handover document**, and
- a **standing process README**.

---

## 2. High-Level Workflow Overview

The workflow always proceeds in the following order:

1. **Folder tree normalization (structure only)**
2. **Collection-scoped product enumeration**
3. **Axis decomposition and editorial decisions**
4. **Canonical Excel row creation**
5. **Derivation audit**

Each collection is completed **in full** before moving to the next.

---

## 3. Folder Trees as the Primary Source of Truth

The folder tree is the **starting point**, not Excel.

Key principles:

- folders represent **product attributes**, not SKUs
- colour folders are terminal leaves
- only folders *above colour* define product identity
- a single product item may be represented by multiple nested attributes

For execution purposes, we work with:

> **Structure-only trees that stop short of colour**

This allows accurate counting and enumeration of product items.

---

## 4. Structure-Only Trees (No Colour)

For each collection, a **structure-only tree** is produced that:

- includes all folders above colour
- excludes colour folders entirely
- preserves nesting exactly as it exists on disk

This tree answers one critical question:

> **How many distinct product items exist in this collection?**

Example (conceptual):

NKD
+---Scrunch
| +---V
| | +---Leggings
| | +---Shorts
| +---Halter_Bra
| +---Halter_Tank
+---Embody
| +---Sports_Crop
+---Half_Zip_Long_Sleeve_Top


Each terminal branch above colour corresponds to **one Excel row**.

---

## 5. One Collection at a Time

Execution always follows this rule:

> **Finish one collection completely before starting another.**

Reasons:

- prevents scope bleed
- makes auditing tractable
- allows partial rollback
- simplifies AI context loading in new sessions

The NKD collection is the canonical reference implementation.

---

## 6. Transfer to Excel: Canonical Mapping

Once product items are enumerated, each item is transferred to Excel using the rules defined in:

**`11-ItemName-Relevant_Columns—Canonical_Semantics_&_Derivation_Rules.md`**

This document governs:

- which words belong in which columns
- what constitutes “fully derived”
- when `itemName_fully_derived = No` is required
- how variants are populated
- how collections are identified

This document **must be attached** in any new session that continues this work.

---

## 7. Editorial Judgment Is Expected (and Tracked)

Not all product names can be mechanically derived.

When a word or concept:

- does not belong to any existing column, or
- represents a secondary attribute (e.g. Seamless, Scrunch, BBL),

it is placed in the **variant** column.

If any word in the item name is not accounted for by columns:

- `itemName_fully_derived` is set to **No**
- the exception is intentional and auditable

This is not an error state.

---

## 8. Example: NKD as the Reference Pattern

The NKD collection demonstrates the full workflow:

1. Folder tree reorganized into rational, axis-based structure
2. Structure-only tree produced
3. Each product item enumerated
4. Excel rows created with:
   - brand
   - gender
   - item name
   - domain
   - collection
   - subCategory
   - variant(s)
5. Derivation status validated

All subsequent collections are executed **the same way**.

---

## 9. What Gets Attached in a New Chat Session

To resume work cleanly in a new session, the following should be attached:

1. **Structure-only folder tree** for the current collection  
   (e.g. `File_Tree-Ryderwear-Women-Structure_Only.md`)

2. **Canonical derivation rules**  
   (`11-ItemName-Relevant_Columns—Canonical_Semantics_&_Derivation_Rules.md`)

Optionally:
- a short list of already-completed collections
- any known anomalies or editorial notes

---

## 10. What This README Does *Not* Do

This document does not:

- define UI or frontend behavior
- define search or filtering logic
- define AI implementation
- define database schema

It defines **execution discipline only**.

---

## 11. Canonical Status

This README is **authoritative** for the execution model.

Any future work that:
- skips folder-first enumeration
- mixes collections mid-execution
- derives names before enumerating products

is considered out of contract.

This document exists to prevent that drift.

---
