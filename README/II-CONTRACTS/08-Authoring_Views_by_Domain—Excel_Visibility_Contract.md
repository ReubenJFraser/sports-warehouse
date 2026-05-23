# Authoring Views by Domain — Excel Visibility Contract

## 1. Purpose of This README

This README defines the **authoritative authoring views** for the unified `Items` worksheet.

Its purpose is to resolve a known tension between:

- a **single, governed schema** (required for validation, export, and ingestion), and  
- **domain-specific authoring ergonomics** (required for human usability).

This document defines **which columns are visible or hidden** when authoring items for each product domain, without altering the underlying schema.

This is a **presentation and workflow contract**, not a schema definition.

---

## 2. Scope

This document applies to:

- the Excel `Items` worksheet used as the editorial source of truth
- human authoring, review, and maintenance workflows
- column visibility and working views by domain

This document does **not**:

- define column semantics (see Part A — Column Specification)
- define validation logic (see Part B — Horizontal Projection & Validation Wiring)
- define export or ingestion behavior (see Part C — Export & Ingestion Contract)
- permit column removal, duplication, or domain-specific schemas

---

## 3. Core Principle

The schema is **unified**.  
The authoring view is **domain-specific**.

Column **existence** is governed by Part A.  
Column **visibility** is governed by this document.

Empty cells are not an error.  
Hidden columns are a usability aid.

---

## 4. Core Identity & Orientation Columns (Always First)

Across all domains, the following columns provide human orientation and must appear first when visible:

brand
gender
itemName
product_domain

### 4.1 Rationale

- `brand` establishes primary grouping and context
- `gender` disambiguates audience where applicable before interpretation
- `itemName` identifies the specific product
- `product_domain` governs validation and visibility logic

---

## 5. Unified Column Bands (Schema Reference)

The following bands exist in the unified schema and do not change by domain.

### 5.1 Apparel / Footwear Structural Axis

collection (apparel)
model_family (footwear)

---

### 5.2 Apparel Structure

subCategory
variant
colour

---

### 5.3 Non-wearable Structure

usage_category
usage_subtype

---

### 5.4 Audience & Sizing (Non-image)

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

### 5.7 System & Governance (Non-human)

categoryName
subCategoryParent
categoryID
external_item_id
campaign_or_series
db_itemId
assignment_source

---

## 6. Domain Authoring Views

The following sections define **default column visibility** for human authoring.

Columns not listed as visible for a domain must be **hidden**, not deleted.

---

## 6.1 Apparel (Clothing) — Authoring View

### 6.1.1 Visible Columns

brand
gender
itemName
product_domain
collection
subCategory
variant
colour
ageGroup
sizeType
fitStyle
activityTags
price
salePrice
description
featured
images
thumbnails_json
videos
altText
ariaText
videoAltText
CropAllowed

---

### 6.1.2 Hidden Columns

model_family
usage_category
usage_subtype
categoryName
subCategoryParent
categoryID
external_item_id
campaign_or_series
db_itemId
assignment_source

---

## 6.2 Footwear (Shoes) — Authoring View

### 6.2.1 Visible Columns

brand
gender
itemName
product_domain
model_family
subCategory
colour
ageGroup
sizeType
fitStyle
activityTags
price
salePrice
description
featured
images
thumbnails_json
videos
altText
ariaText
videoAltText
CropAllowed

---

### 6.2.2 Hidden Columns

collection
variant
usage_category
usage_subtype
categoryName
subCategoryParent
categoryID
external_item_id
campaign_or_series
db_itemId
assignment_source

---

## 6.3 Non-wearables (Equipment, Accessories) — Authoring View

### 6.3.1 Visible Columns

brand
itemName
product_domain
usage_category
usage_subtype
ageGroup
activityTags
price
salePrice
description
featured
images
thumbnails_json
videos
altText
ariaText
videoAltText
CropAllowed

---

### 6.3.2 Hidden Columns

gender
collection
model_family
subCategory
variant
colour
sizeType
fitStyle
categoryName
subCategoryParent
categoryID
external_item_id
campaign_or_series
db_itemId
assignment_source

---

## 7. Operating Guidance

- Rows must be filtered by `product_domain` before authoring
- Column visibility must be adjusted to match the active domain
- Hidden columns remain part of the schema and export
- All columns must be unhidden for audit or schema review

Column hiding is a **presentation choice**, not a semantic one.

---

## 8. Non-Goals

This document does not:

- introduce conditional schemas
- allow domain-specific worksheets
- redefine column order in Part A
- override validation or ingestion rules

---

## 9. Guiding Invariants

- One worksheet, one schema
- Domains govern applicability, not column existence
- Visibility solves sparsity; structure solves correctness
- Authoring ergonomics must not compromise governance

This document exists to make the unified schema **usable without being diluted**.











