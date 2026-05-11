# Ryderwear Women Folder System — Harmonization Execution Plan

## Purpose

This document defines the **mechanical execution plan** for applying the
Ryderwear Women Harmonization Contract to the existing folder tree.

This document:

- enumerates structural inconsistencies
- defines what must change
- defines what must not change
- sequences the application process

This document does **not** redefine taxonomy.
It applies the already locked contract.

---

## Scope

This execution plan applies to:

- NKD folder structure
- Non-NKD folder structure
- Axis ordering
- Token normalization
- __Collection placement
- Color terminal enforcement

This document does not:

- introduce new axes
- redesign taxonomy
- add new products
- modify Excel
- modify database schema
- resolve structural debt items

---

## Operating Constraints

All changes must be:

- mechanical
- contract-compliant
- axis-preserving
- reversible

No structural debt item may be resolved during execution.

No new semantic branch may be introduced.

---

## Canonical Order to Enforce

All branches must conform to:

<NKD | Non-NKD>
→ <Product Type>
→ <Sub-Type>
→ <Primary Axis>
→ <Secondary Axis>
→ __Collection
→ <Collection Name>
→ <Color>

### Axis Hierarchy Constraints (Locked)

Axes may function as either:

- Primary axes (branching immediately under Sub-Type)
- Secondary axes (modifying a primary axis)

The following constraints apply:

- Construction, Cut, Fabric, and Rise may act as primary axes.
- Seamless, Support, Fit, Length, and Fabric may act as secondary axes.
- A secondary axis must not override or redefine a primary axis.
- Rise may not appear above Construction if Construction is present.
- Seamless must not function as a primary axis.
- Scrunch is a Construction modifier and must appear under Construction.
- Multiple secondary axes may appear sequentially, provided they respect hierarchy constraints.

Absence of an axis implies default state.

### Enforcement Rules

- Only one primary axis may appear directly under Sub-Type.
- Multiple secondary axes may appear sequentially, provided they respect hierarchy rules.
- Fabric may not appear above Construction, Cut, or Rise if those axes exist.
- __Collection may not appear above structural axes.
- Color must always be terminal.
- Multi-tone variants may appear beneath color.

---

## Axis Hierarchy Rules

Axes may function either as:

- Primary axes (branching immediately under Sub-Type)
- Modifier axes (branching under another axis)

The following hierarchy constraints apply:

### Construction

- Construction may act as a primary axis.
- Construction may contain modifier axes.

Allowed modifiers under Construction:
- Scrunch
- Rise
- Support (when structurally encoded)
- Fit (when structurally encoded)

Scrunch is not a standalone primary axis when subordinate to a construction type.

Example:
Construction
 └── V
     └── Scrunch

---

### Rise

- Rise is a modifier axis.
- Rise may appear:
  - directly under Sub-Type (when rise defines the garment)
  - under Construction (when rise modifies a construction type)

Rise must not appear above Construction if Construction is present.

---

### Scrunch

- Scrunch is a construction modifier.
- Scrunch may appear:
  - under Construction
  - under Fabric (when fabric-specific scrunch behaviour exists)

Scrunch must not exist as:
- Non-Scrunch
- an independent top-level product type

Absence of Scrunch implies non-scrunch.

---

### Fabric

- Fabric may function as either:
  - a primary axis (when garment identity is fabric-defined)
  - a secondary axis under Construction or Rise

Fabric must never encode collection identity.

---

### Seamless

- Seamless is a modifier axis.
- It must not function as a primary axis.
- Absence of Seamless implies non-seamless.

## Section A — Global Normalisation Actions

### A1 — Hyphen Removal

Replace:

- High-Waisted → High_Waisted
- V-Dip → V_Dip
- Cross-Over → Cross_Over

All folder names must use underscores.

---

### A2 — Ultra_Soft Normalisation

Replace all occurrences of:

- Ultra_Soft_Fabric

With:

- Ultra_Soft

Fabric names must not append `_Fabric`.

---

### A3 — Non-Seamless Removal

Remove all:

- Non-Seamless

The absence of Seamless implies non-seamless.

---

### A4 — Invisible Constraint

Ensure:

- Invisible appears only beneath Scrunch.
- Invisible does not exist as a primary Construction category.

---

---

## Modifier Encoding Principle (Locked)

This section formalises how construction and secondary modifiers must be encoded in the filesystem.

### Core Rule

Modifiers are encoded **only when present**.

Negative states must never be represented as folders.

Absence of a modifier implies the default state.

---

### Prohibited Pattern

The following pattern is prohibited:

Construction
 ├── Scrunch
 └── Non-Scrunch

Rise
 ├── Scrunch
 └── Non-Scrunch

Fabric
 ├── Seamless
 └── Non-Seamless

Encoding absence as a folder creates artificial symmetry and semantic debt.

Negative modifier folders must not exist.

---

### Correct Pattern

Modifiers appear only when structurally active.

Example:

Construction
 └── V
     ├── Scrunch
     └── __Collection

Rise
 └── High_Waisted
     ├── Scrunch
     └── __Collection

Fabric
 └── Ultra_Soft
     ├── Seamless
     └── __Collection

If a path does not contain the modifier token, the product is implicitly non-modified.

---

### Scrunch (Modifier Encoding)

Scrunch is a subordinate construction modifier.

- It must appear only when present.
- It must never have a sibling Non-Scrunch folder.
- Invisible and Minimal may only appear beneath Scrunch.

Example:

Construction
 └── V
     └── Scrunch
         └── Invisible

---

### Seamless (Modifier Encoding)

Seamless is a structural modifier.

- It must appear only when present.
- Non-Seamless is prohibited.
- Absence of Seamless implies non-seamless.

---

### Rise and Other Secondary Modifiers

Secondary modifiers follow the same principle:

- Encode when present.
- Never encode absence.
- Default state is implicit.

---

### Rationale

Negative folders:

- Inflate tree depth without semantic gain.
- Complicate Excel derivation.
- Encourage symmetry drift.
- Multiply combinatorial branches.

The filesystem is compositional.

Only positive attributes are structurally encoded.

---

### Enforcement Invariant

If a modifier has a negative counterpart in the filesystem,
the structure violates this contract.

This principle is non-negotiable and globally applicable.

---

## Section B — NKD ↔ Non-NKD Parity (Bottoms → Leggings)

### B1 — Axis Alignment

Where Non-NKD uses:

- Construction → Scrunch → Seamless

NKD must represent equivalent structure where applicable.

If NKD lacks Seamless entirely:

- No Seamless branch should be added unless product reality requires it.
- Parity applies only where attribute exists.

---

### B2 — Fabric Placement

Ensure Fabric appears:

- After Construction (if Construction exists)
- As primary axis only where Construction does not apply

Fabric must not precede Construction if both exist.

---

### B3 — Rise Ordering

Rise must appear:

- After Construction
- Before __Collection
- Never above Fabric when Fabric is subordinate

---

## Section C — NKD ↔ Non-NKD Parity (Bottoms → Shorts)

### C1 — Training Branch

Ensure:

- Fit appears only where structurally meaningful.
- Fabric remains subordinate to Construction.

---

### C2 — Legacy Handling

Legacy must:

- Not encode structural attributes.
- Not appear above Construction or Fabric.
- Remain after structural axes.

---

### C3 — Seamless Position

Seamless must:

- Appear as secondary axis.
- Not appear before Construction.

---

## Section D — NKD ↔ Non-NKD Parity (Tops → Sports_Bra)

### D1 — Support Axis

Support may appear as secondary axis.

It must:

- Not precede Construction.
- Not replace Construction.

---

### D2 — Fabric Alignment

Fabric must:

- Remain beneath Construction or Cut.
- Not appear above them.

---

### D3 — Collection Placement

__Collection must always appear:

- After Construction / Cut / Fabric / Rise.
- Never directly under Sub-Type.

---

## Section E — Residual Categories

### E1 — Tops (Non-Sports Bra)

Ensure:

- Cut branches are consistent between NKD and Non-NKD.
- Fabric is subordinate where Cut exists.
- No orphan color folders exist without axis parent.

---

### E2 — Accessories

Accessories may:

- Use minimal axis depth.
- Skip structural axes where inapplicable.

Color must still be terminal.

---

### E3 — Bundles

Bundles may remain shallow unless structural attributes apply.

---

## Structural Debt (Not To Be Resolved)

The following are explicitly excluded from this execution:

- Converting Seamless into data-layer-only attribute
- Expanding NKD Support axis
- Reassigning Legacy to Fabric
- Introducing Fit parity across all branches
- Redesigning Collection depth

These items remain in the Structural Debt Register.

---

## Execution Sequence

1. Apply global token normalization.
2. Correct axis ordering under each Sub-Type.
3. Relocate __Collection where mispositioned.
4. Verify color terminal compliance.
5. Confirm NKD ↔ Non-NKD parity for equivalent branches.
6. Validate no new axes were introduced.
7. Perform final tree audit.

---

## Validation Checklist

- All hyphens removed.
- All tokens canonical.
- No Ultra_Soft_Fabric remains.
- No Non-Seamless folders remain.
- Invisible appears only beneath Scrunch.
- __Collection never precedes structural axes.
- Color always terminal.
- NKD and Non-NKD use identical axis names.

Execution is complete only when this checklist passes.

---

## Completion Criteria

Execution is considered complete when:

- All paths conform to canonical order.
- No contract violations remain.
- Structural debt items remain untouched.
- Folder tree is ready for propagation to Excel alignment.

---

## Guiding Principle

Execution must apply the contract faithfully.

If ambiguity arises:

- Refer to the Harmonization Contract.
- Do not invent structure.
- Do not optimise beyond contract scope.
- Document edge cases for later amendment.

Structure stability takes precedence over local perfection.
