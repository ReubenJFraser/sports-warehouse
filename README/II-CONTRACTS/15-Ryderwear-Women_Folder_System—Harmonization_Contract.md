# Ryderwear Women Folder System — Harmonization Contract

## Purpose

This document defines the **canonical contract** governing the Ryderwear Women folder system.

Its purpose is to:

- lock the axis model
- define canonical ordering
- prevent NKD ↔ Non-NKD drift
- separate filesystem structure from Excel product naming
- eliminate semantic ambiguity before automation

This document is governance.  
It does not contain audit commentary or execution steps.

---

## Scope

This contract governs:

- the on-disk folder structure for Ryderwear Women
- NKD and Non-NKD structural parity
- canonical axis definitions
- token normalisation
- collection placement rules
- color terminal rules
- product naming constraints (as they relate to structure)

This contract does not govern:

- Excel column derivation logic
- database schema
- SKU assignment
- discount automation
- pricing logic
- frontend rendering
- historical audit notes

---

## Conceptual Roles

### Filesystem

The filesystem is a **semantic branching system**.

It expresses:

- structural attributes
- construction logic
- fabric identity
- rise and cut
- collection grouping
- color endpoints

It does not store product names.

---

### Excel

Excel is the **flattened data authority**.

- Each row represents one product item.
- Axis values become columns.
- Product names are derived strings composed from axis values.

Excel consumes structure.  
It does not define structure.

---

### Database

The database mirrors Excel.

It executes logic but does not define taxonomy.

---

## Canonical Axis Set (Locked)

The following axes are canonical and may appear in the folder structure:

- Construction
- Cut
- Fabric
- Rise
- Support
- Fit
- Length
- Seamless
- Legacy
- __Collection
- Color

No additional axis may be introduced without formal contract amendment.

---

## Canonical Axis Order (Filesystem — Locked)

The folder hierarchy must follow this structure:

<NKD | Non-NKD>
→ <Product Type>
→ <Sub-Type>
→ <Primary Axis>
→ <Secondary Axis>
→ __Collection
→ <Collection Name>
→ <Color>


---

### Definitions

**Product Type**  
High-level apparel grouping:
- Bodysuit
- Bottoms
- Tops
- Accessories
- Bundles

This is not a product name.

---

**Sub-Type**  
The first semantically meaningful structural level:
- Leggings
- Shorts
- Sports_Bra
- Tank
- Tee
- Skirt
- Track_Pants
- Track_Jacket
- etc.

The expressive filesystem begins at Sub-Type.

---

**Primary Axis**  
One of:

- Construction
- Cut
- Fabric
- Rise

Only one primary axis may branch immediately after Sub-Type.

---

**Secondary Axis**

May include:

- Fabric
- Support
- Seamless
- Fit
- Length

Secondary axes must not invert primary logic.

---

**__Collection**

- Must always appear after all structural axes.
- May never appear above Fabric, Construction, Cut, or Rise.
- May not be used to encode structure.

---

**Color**

- Always terminal.
- No folders may exist below color.
- Multi-tone variants may branch under color (e.g. Marl → Black).

---

---

## Axis Dependency & Ordering Rule (Locked)

Axis order is determined by structural dependency, not rarity, frequency, or marketing emphasis.

An axis A must precede axis B if B cannot exist without A.

Axis ordering is therefore governed by ontological containment:

- Fabric may exist without Seamless.
- Seamless may exist without Scrunch.
- Scrunch may exist without Invisible.
- Invisible cannot exist without Scrunch.
- Scrunch cannot exist without its underlying Construction or Fabric platform.

Therefore the canonical containment sequence, when Fabric is the governing primary axis, is:

Fabric  
→ Seamless  
→ Scrunch  
→ Invisible  

Seamless must never appear below Scrunch.

Invisible must never appear above Scrunch.

If Seamless is structurally irrelevant for a branch, it must be omitted entirely rather than represented negatively.

Absence of Seamless implies non-seamless.

Primary axis selection must follow the same rule:

- The most independent structural variable appears first.
- Dependent modifiers branch below it.
- Statistical rarity does not elevate an axis.

Axis inversion is prohibited.

Any reordering of axes must be justified by dependency logic and documented via formal contract amendment.

## Product Naming Rule (Data Layer — Locked)

Product names are not structural primitives.

- They must not appear in the filesystem.
- They are derived from canonical axis values.
- Every word in a product name must correspond to a defined axis value or approved token.

If a product name requires a new structural term, the taxonomy must be updated first.

The filesystem defines vocabulary.  
Excel composes names from that vocabulary.

---

## Token Normalisation Rules

### Underscores

- All multi-word tokens use underscores.
- Hyphens are prohibited in folder names.
- Example: High_Waisted (not High-Waisted).

---

### Seamless

- Seamless must be explicit when structurally meaningful.
- Non-Seamless must not exist as a folder.
- Absence of Seamless implies non-seamless.

---

### Invisible

- Invisible is valid only as a Scrunch modifier.
- It may not appear as an independent construction category.

---

### Ultra_Soft

- Ultra_Soft is canonical.
- Ultra_Soft_Fabric is prohibited.
- Fabric names must not redundantly append “_Fabric”.

---

### Legacy

- Legacy is an axis, not a collection.
- It must not encode structural attributes.
- It must not appear above structural axes.

---

## NKD ↔ Non-NKD Parity Rule

NKD and Non-NKD must represent equivalent structural concepts using:

- the same axis names
- the same ordering logic
- the same token spelling

Divergence is permitted only when:

- a product genuinely lacks an attribute
- the attribute is structurally inapplicable

Intentional divergence must be documented separately.

---

## Structural Debt Register (Locked but Unresolved)

The following areas are recognised but not resolved in this contract:

- Whether Seamless should eventually become a data-layer attribute
- Whether NKD should support full Support axis parity
- Whether certain Legacy patterns should migrate to Fabric
- Whether Fit should expand across NKD Bottoms
- Brand placement considerations inside specific sub-types

These are acknowledged as structural debt.

They remain outside the scope of this harmonisation contract.

---

## Prohibitions

The following are strictly prohibited:

- Product names in the filesystem
- SKU identifiers in folder names
- Collection before structural axes
- Fabric nested under collection
- Color appearing before __Collection
- Multiple primary axes under a single Sub-Type branch
- Ad hoc folder creation without axis classification

Violation indicates contract breach.

---

## Known Gaps and Open Questions

- Some historical NKD paths may not yet conform to canonical order.
- Some Non-NKD legacy structures may require staged migration.
- Collection depth consistency may need refinement during execution.
- Cross-Over vs Crossover token alignment may require final spelling lock.

These gaps do not invalidate the contract.

They require controlled execution.

---

## Guiding Invariants

- Structure precedes naming.
- Vocabulary precedes derivation.
- Filesystem defines semantic authority.
- Excel flattens but does not redefine.
- Parity prevents drift.
- Stability outweighs local optimisation.

This contract must remain stable unless formally amended.


## Current Interpretation Note (2026-05-26)

This harmonization contract remains active. The following clarifications apply for present operations:

- `model_id` is the canonical identity anchor for deterministic product ↔ image mapping (see Contracts 22 and 24).
- Final product/variant branches should not repeat brand/gender/collection/product-type/sub-type tokens already encoded by parent folders.
- Colour/variant folders are terminal or near-terminal before media files; numeric files are acceptable within those governed colour folders.
- Runtime/database image paths may be semantic-path-based, model_id-based, or mapping-based, provided the strategy is deterministic and documented.
- Token-overlap matching is insufficient when it conflicts with structured identity fields (`model_id`, `collection`, `subCategory`, product type).
- Batch 1 remains operationally valid unless superseded by a later reviewed migration; Batch 2 must be convention-planned before any copying or ProductDB/MySQL reconciliation work.
