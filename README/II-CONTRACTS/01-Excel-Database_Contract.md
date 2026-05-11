# Excel → Database Contract (Authoritative)

## Role definition (non-negotiable)
- Excel is the editorial authority
- Human judgment lives here
- Naming, classification, intent, and exceptions are resolved here
- If Excel and DB disagree, Excel wins
- Database is the execution mirror
- Stores what Excel declares
- Never invents meaning
- Never “fixes” Excel silently
> This single rule would have prevented most of the pain you experienced.

## Column classes (this is the core insight)
- Every column must belong to exactly one class.

### Editorial columns (Excel-owned)
- These are written and reasoned about by humans.

**Contract rule**
- DB may enforce constraints (UNIQUE, NOT NULL)
- DB may NOT generate values
- Importers must be pure writes, never transforms

### System columns (DB-owned)
- These are derived, calculated, or operational.

**Contract rule**
- Excel must not attempt to “manage” these
- Excel may leave them blank or absent
- DB is free to change them

### Hybrid columns (careful zone)
- These exist in Excel but are validated by DB.

**Contract rule**
- Excel chooses value
- DB enforces allowed set / type
- Violations should FAIL loudly

## Canonical Axis Alignment (Ryderwear Harmonization Extension)

### Purpose

This section formalizes the relationship between filesystem product axes and Excel column classes.

It exists to ensure that Excel remains the editorial authority while remaining semantically aligned with the Product-First Axis Model and the Ryderwear Women Harmonization Contract.

This is an extension, not a structural rewrite.

---

### Scope

This section governs:

- Canonical axis dependency awareness
- Fabric-primary vs Construction-primary semantics
- Bounded attribute taxonomy (support_level, seamless, sports_bra_type)
- Mapping between filesystem axes and Excel column classes

It does not redefine:

- System column ownership
- Import mechanics
- Database authority boundaries

---

### Axis Dependency Awareness

Excel must encode product structure in a way that respects axis dependency rules defined in the filesystem harmonization contract.

Specifically:

- A dependent attribute must not appear without its parent axis being logically present.
- Example: `invisible_scrunch = true` implies `scrunch = true`.
- Example: `seamless = true` implies a fabric class that supports seamless construction.

Excel may express this through bounded attributes rather than folder hierarchy.

However:

- The semantic dependency must remain true at the data level.
- Excel must not encode contradictory states.

This rule preserves axis purity across Excel and filesystem representations.

---

### Fabric-Primary vs Construction-Primary Semantics

Excel must allow Fabric to function as a primary differentiator where contractually valid.

This includes cases where:

- Fabric determines silhouette behavior.
- Fabric determines seam behavior (e.g., seamless).
- Fabric determines scrunch visibility classification.

Fabric is not globally primary.

It is contextually primary when it structurally defines the product family.

Where Fabric is not primary, Construction or Rise may function as the primary differentiator.

Excel must reflect this distinction via canonical column positioning and derivation logic defined in:

11-ItemName-Relevant_Columns—Canonical_Semantics_&_Derivation_Rules.md

---

### Canonical Bounded Attributes

The following attributes are bounded and must be treated as controlled-value fields:

- support_level
- sports_bra_type
- seamless
- scrunch
- scrunch_visibility
- fabric_class

Rules:

- These fields must use canonical values.
- These values must align with filesystem axis law.
- DB may enforce allowed sets.
- Excel remains editorial authority over selection.

Free-text expansion is prohibited.

These attributes represent semantic axes, not marketing labels.

---

### Filesystem ↔ Excel Mapping Principle

Filesystem folders express product structure hierarchically.

Excel expresses the same structure as columnar bounded attributes.

Mapping rule:

- Filesystem hierarchy defines legal axis combinations.
- Excel encodes those combinations explicitly.
- DB stores them without reinterpretation.

Excel is not a mirror of the filesystem.

Excel is the semantic authority that declares the product identity.

The filesystem is a structural projection of that identity.

Both must agree at the level of axis legality.

---

### Invariant

No Excel row may encode a combination of attributes that would violate the filesystem axis dependency rule.

If such a row exists:

- Sync must halt.
- The row must be corrected editorially.

## Required invariants (mechanical checks)
- Before any sync, these must all be true.
- Identity invariants
- external_item_id is:
- non-null
- unique
- immutable once published
- series_slug is:
- non-null
- present in the Series Slug Canonical Definition
- Referential invariants
- brand spelling is consistent
- categoryName is not free-text chaos
- assignment_source ∈ (custom, course)
- If any invariant fails → no sync.

## Schema sync sequence (this is the mechanical part)
- Always in this order. Never skip.
- Step 1 — Excel audit (human)
- Fill all editorial columns
- Resolve all NULLs that are not system-owned
- Decide exceptions explicitly (like course items)
- Step 2 — Schema alignment (DDL)
- Add missing columns to DB
- Match types and constraints
- No data movement yet
- Step 3 — Dry-run validation (SELECT-only)
- Examples:
- SELECT external_item_id, COUNT(*)
- FROM item
- GROUP BY external_item_id
- HAVING COUNT(*) > 1;
- SELECT *
- FROM item
- WHERE external_item_id IS NULL
- AND assignment_source = 'custom';
- If rows appear → stop.
- Step 4 — Controlled UPDATEs
- Brand-by-brand
- Alphabetical
- Logged
- Step 5 — Post-sync snapshot
- SELECT
- COUNT(*) AS total,
- SUM(external_item_id IS NOT NULL) AS ext_ok,
- SUM(series_slug IS NOT NULL) AS series_ok
- FROM item;
- This snapshot becomes the baseline.

## Importers: formal rule (important lesson learned)
- Importers are allowed to do only one thing:
- Copy declared values from Excel into DB.
- They are not allowed to:
- infer slugs
- normalize names
- invent defaults
- “help”
- That earlier failure you identified?

It violated this rule.
- If an importer ever contains logic like:
- if (empty($series_slug)) { guess(); }
- …it is invalid by contract.

## Change protocol (prevents future pain)
- Any future change must answer one question:
- Is this editorial, system, or hybrid?
- Editorial → change Excel first
- System → change code / DB only
- Hybrid → Excel + DB constraint update together
- No exceptions.

## Why this works (and why it matters)
- This contract:
- removes interpretation from sync
- makes Codex viable later (because rules are explicit)
- prevents “chat window drift”
- turns future debugging into diffing, not reasoning
- You are no longer building a site — you’re now operating a data system.
