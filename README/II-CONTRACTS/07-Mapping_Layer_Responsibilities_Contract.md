# Mapping Layer Responsibilities Contract

## Purpose

This README defines the **distinct mapping layers** used in the Sports Warehouse system and locks their respective responsibilities.

Its purpose is to prevent semantic drift, role confusion, and improper reuse of worksheets or tables that appear similar but serve fundamentally different functions.

This document exists to answer a single question unambiguously:

Which kind of “mapping” belongs where?

---

## Scope

This contract applies to:

- Excel worksheets used during design, audit, and implementation
- Schema evolution and column introduction
- Category and taxonomy reference data
- Transitional audit artifacts

This contract does **not** define:

- product authoring rules
- image path structure or validation
- database schema design
- ingestion or rendering logic

Those concerns are governed by other contracts.

---

## Core Principle

Not all mappings are the same.

Mappings in this system fall into **three distinct categories**, each with its own purpose, lifecycle, and allowed contents.

These categories must not be collapsed, merged, or repurposed.

---

## Mapping Category 1 — Taxonomy Mapping

### Role

Taxonomy mappings define **business classification relationships** between categories, subcategories, and parent groupings.

They describe how products are grouped conceptually for navigation, filtering, and system categorization.

### Canonical Example

The existing `Mappings` worksheet containing columns such as:

- `categoryName`
- `categoryID`
- `subCategory`
- `subCategoryParent`
- `default_seasonal_context`

### Characteristics

- Row-level business data
- Values vary by row
- Used operationally (dropdowns, category validation, navigation)
- Represents domain taxonomy, not schema intent

### Constraints

- Must not contain schema metadata
- Must not contain column-level behavior flags
- Must not contain image path participation rules
- Must not contain audit or migration annotations

This layer answers:  
**“How are products classified within the business taxonomy?”**

---

## Mapping Category 2 — Schema Mapping

### Role

Schema mappings define **column-level intent and behavior** during schema alignment and implementation.

They map existing columns to their design-time equivalents and document how each column is meant to behave within the system.

This layer is the **authoritative bridge** between audit findings and Part A (Vertical Column Specification).

### Canonical Worksheet

`Schema_Mapping`

### Required Worksheet Structure

The `Schema_Mapping` worksheet **must** contain the following columns and no others unless this contract is versioned:

| Column Name | Meaning |
|------------|---------|
| `source_column` | Column name as it exists in the current worksheet or legacy schema |
| `mapping_action` | One of: `KEEP`, `RENAME`, `SPLIT`, `REMOVE` |
| `target_part_a_column` | Canonical Part A column name, `(split)`, or `(none)` |
| `image_path_role` | One of: `Yes`, `No`, `Conditional` |
| `domain_notes` | Structured clarification where behavior differs by product domain |
| `general_notes` | Human-readable explanation to prevent future misinterpretation |

### Characteristics

- Column-level metadata (not row-level data)
- Values do not vary per product
- Governance and implementation aid only
- Not ingested into databases
- Not authored by merchandisers or editors

### Constraints

- Must not contain product data
- Must not be mixed into ProductDB worksheets
- Must not be repurposed as taxonomy data
- Must not be treated as a source of runtime truth

This layer answers:  
**“What does this column mean, and how should it behave?”**

---

## Mapping Category 3 — Audit Snapshot

### Role

Audit snapshots capture the **current state of reality** for comparison against contracts and design intent.

They exist to document what currently exists, not to define what should exist.

### Canonical Example

An `AUDIT_Columns` worksheet listing:

- existing column names
- actions such as KEEP / RENAME / SPLIT / REMOVE
- neutral observations about legacy structure

### Characteristics

- Observational and forensic
- Transitional by nature
- Used during migration or refactor phases
- May be discarded after implementation

### Constraints

- Must not encode future-facing design decisions
- Must not encode schema intent
- Must not contain authoritative rules
- Must not be treated as a mapping or specification

This layer answers:  
**“What exists right now?”**

---

## Prohibited Collapses

The following are explicitly forbidden:

- Adding schema behavior flags to ProductDB worksheets
- Adding product data to schema mapping sheets
- Adding interpretive or design intent to audit snapshots
- Repurposing taxonomy mappings to describe schema behavior

Each mapping category has a single responsibility.  
Violating these boundaries creates ambiguity and undermines governance.

---

## Lifecycle Relationship Between Mapping Layers

1. **Audit Snapshot** captures current state
2. **Schema Mapping** records intended alignment
3. **Taxonomy Mapping** remains stable business reference
4. Implementation proceeds using contracts as authority

At no point should these layers be merged.

---

## Guiding Invariants

- Product data describes products, not schemas
- Schema intent is column-level, not row-level
- Taxonomy is business classification, not governance logic
- Audits observe reality; contracts define truth
- Separation of concerns is enforced structurally, not by convention

This contract exists to keep mapping responsibilities explicit, auditable, and durable over time.
