# Column Order & Derived Field Contract — Excel Implementation Phase

## 1. Purpose of This README

This README defines the **authoritative column order and authoring semantics** for the `Items` Excel worksheet during the Excel Implementation Phase.

Its purpose is to:

- lock the **horizontal column order** used for authoring
- resolve ambiguity between **human recognition** and **structural authority**
- formalize the status of **derived fields**, especially `itemName`
- prevent repeated re-litigation of column ordering during validation, export, or ingestion work

This document exists to bridge:

- **Part A — Column Specification** (what columns exist and what they mean), and
- **Part B — Horizontal Projection & Validation Wiring** (how columns are enforced)

This is a **governance contract**, not a suggestion.

---

## 2. Scope

This contract applies to:

- the unified `Items` worksheet in Excel
- human authoring and review workflows
- column ordering decisions during the Excel Implementation Phase

This contract does **not**:

- redefine column semantics (see Part A)
- define validation rules (see Part B)
- define export or ingestion behavior (see Part C)
- define column visibility by domain (see Authoring Views by Domain)

---

## 3. Governing Ordering Principle

Column order must satisfy the following priorities, **in this order**:

1. **Human recognition**  
   What a human needs to see first to understand what they are looking at.

2. **Folder-driving structure**  
   The attributes that define image folders, paths, and visual distinctness.

3. **Descriptive and system metadata**  
   Attributes required for filtering, commerce, governance, or system integrity.

This principle governs all ordering decisions in this document and supersedes aesthetic or convenience-based preferences.

---

## 4. Role of `itemName` (Critical Clarification)

`itemName` is a **derived, human-facing composite label**.

It is:

- constructed from upstream structural columns
- intended for readability, scanning, and sanity-checking
- non-authoritative

`itemName` must **not**:

- drive validation
- participate in image path construction
- be used as a source of truth for mappings or ingestion

Structural columns define reality.  
`itemName` reflects that reality for humans.

---

## 5. Canonical Column Order (Excel Implementation Phase)

The following is the **final, authoritative column order** for the Excel Implementation Phase.

### 5.1 Human Orientation (Always First)

These columns establish immediate human context.

brand
gender
itemName
product_domain

Notes:

- `brand` provides primary grouping and recognition
- `gender` disambiguates audience before interpretation
- `itemName` is readable but derived
- `product_domain` governs validation and authoring views

---

### 5.2 Folder-Driving Structural Inputs

These columns define image folders and visual distinctness.
Applicability is domain-dependent; NULL is valid where not applicable.

Apparel:
- collection
- subCategory
- variant
- colour

Footwear:
- model_family
- subCategory
- colour

Non-wearables:
- usage_category
- usage_subtype

---

### 5.3 Descriptive Taxonomy

These columns provide secondary, human-readable categorization.

categoryName
subCategoryParent

---

### 5.4 Audience & Fit (Non-image)

These columns affect filtering and UX but must not affect image paths.

ageGroup
sizeType
fitStyle
activityTags

---

### 5.5 Commercial & Editorial

price
salePrice
description
featured

---

### 5.6 Media & Accessibility

images
thumbnails_json
videos
altText
ariaText
videoAltText
CropAllowed

---

### 5.7 System & Governance (De-emphasised)

These columns exist for system integrity, not human authoring.

categoryID
external_item_id
campaign_or_series
db_itemId
assignment_source

---

## 6. Implementation Notes

- Columns may be **hidden** by domain during authoring (see Authoring Views by Domain)
- Column order must remain stable even when columns are hidden
- Derived fields may be implemented via formulas or controlled population
- Reordering columns after this point is considered a contract violation

---

## 7. Change Control

Any change to:

- the ordering defined in Section 5
- the role or status of `itemName`
- the grouping of structural vs non-structural fields

requires:

- a documented rationale
- an explicit version increment
- cross-review against Part A and Part B

Ad hoc reordering is not permitted.

---

## 8. Non-Goals

This contract does not attempt to:

- optimize for minimal column count
- avoid NULL values
- collapse domain-specific concepts
- encode visibility or grouping behavior

Those concerns are governed elsewhere.

---

## 9. Guiding Invariants

- One worksheet, one schema
- Structural columns define reality
- Derived columns reflect structure
- Visibility solves sparsity; ordering preserves meaning
- Governance precedes convenience

This contract exists to ensure the Excel worksheet remains **legible, enforceable, and durable** throughout implementation and beyond.

