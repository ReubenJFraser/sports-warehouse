# Part A — Vertical Column Specification (Design-Time Schema)  
**Version: v1.1 (Frozen)**

---

## A.0 Purpose and Status

This section defines the **authoritative column schema** for the unified `Items` worksheet.

It specifies:

- what each column *means*
- which product domains it applies to
- whether it is required, optional, or forbidden by domain
- how values are constrained or validated
- whether the column participates in image path construction

This is a **design artifact**, not an Excel layout.  
Columns are listed vertically for clarity and will be arranged horizontally when implemented.

Status: **v1.1 (Frozen)**  
Changes from v1.0: section ordering locked; explicit ordering rationale added per A.2 section.  
No further structural changes should be made without a version increment.

---

## A.1 Domains (Reference)

All rows belong to exactly one product domain:

- `apparel`
- `footwear`
- `non_wearable`

Domain is declared explicitly and governs validation, applicability, and interpretation rules.

---

## A.2 Column Specification Table

### Legend

- **R** = Required  
- **O** = Optional  
- **F** = Forbidden (must be NULL)  
- **—** = Not applicable / does not participate  

---

### A.2.1 Identity & Governance Columns (All Domains)

**Ordering rationale:**  
These columns establish row identity and domain context and therefore must be resolved before any domain-specific, audience, or structural interpretation can occur.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `product_domain` | Declares governing domain for row | R | R | R | Yes | Dropdown (fixed) | — | Primary control column |
| `item_id` | Internal numeric identifier | R | R | R | Yes | Integer | — | Stable reference |
| `item_name` | Human-readable product name | R | R | R | Yes | Free text | — | Not used in paths |
| `brand` | Canonical brand slug | R | R | R | Yes | Dropdown (brands) | Yes | Lowercase canonical |
| `categoryID` | System category ID | R | R | R | Yes | Dropdown (mappings) | — | Maps to categoryName |
| `categoryName` | System category name | R | R | R | Yes | Derived / locked | — | From mappings |
| `subCategory` | System subcategory | R | R | R | Yes | Dropdown (domain-aware) | — | From mappings |

---

### A.2.2 Collection / Model Axis

**Ordering rationale:**  
These columns define the conceptual product line or model family, which constrains and contextualizes downstream interpretation of gender, structure, and applicability rules.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `collection` | Brand-defined collection | R | O | F | Domain-dependent | Dropdown (brand-aware) | Yes | Ryderwear-style collections |
| `model_family` | Named shoe model line | F | O | F | No | Free text / dropdown | Yes | Footwear only |
| `campaign_or_series` | Marketing or campaign grouping | O | O | O | No | Free text | No | Never used in paths |

Rules:
- Apparel prioritises `collection`
- Footwear uses **either** `collection` **or** `model_family`, never both
- Non-wearables do not use collections by default

---

### A.2.3 Gender & Audience Columns

**Ordering rationale:**  
These columns specify the intended audience and sizing context of a product, within the scope established by brand and collection or model, and **before any structural or image-path–critical classification is applied**.

They describe *who the product is for* and *how it is sized or graded*, not *what the product is structurally*.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `gender` | Intended gender axis | R | O | O / F | Domain-dependent | Dropdown (conditional) | Yes | NULL allowed for non-wearables |
| `age_group` | Audience age group | O | O | O | No | Dropdown (kids/adults) | — | Drives gender requirement |
| `sizeType` | Sizing system or grading context (e.g. standard, plus, tall) | O | O | F | No | Dropdown / enum | — | Item-level metadata; explicitly excluded from image path construction |

**Rules:**
- Apparel: `gender` is **always required**
- Footwear: `gender` is required when explicitly or implicitly gendered
- Non-wearable: `gender` is required only when age-specific or gendered
- `sizeType`:
  - represents sizing or grading context only
  - does **not** affect product structure
  - does **not** affect image selection
  - must **not** appear in image paths or filenames

---

### A.2.4 Clothing / Footwear Structure Columns  
(**Image Path–Critical**)

**Ordering rationale:**  
These columns encode the primary structural meaning that participates directly in image path construction and therefore depend on all prior contextual decisions.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `base_category` | Canonical clothing base | R | F | F | Apparel only | Dropdown (fixed) | Yes | leggings, sports_bra, etc. |
| `subcategory_type` | Type within base or footwear | R | R | O | Domain-dependent | Dropdown (domain-aware) | Yes | e.g. twist, running_shoes |
| `variant` | Structural/textural variant | O | O | F | No | Dropdown (conditional) | Yes | Brand/collection-specific |
| `colour` | Semantic colour name | O | O | O | Conditional | Free text / dropdown | Yes | Omitted if single-colour |

---

### A.2.5 Non-wearable Structure Columns

**Ordering rationale:**  
These columns provide an alternative structural model for non-wearable goods and are separated to avoid conflating apparel-based hierarchy with function-based classification.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `usage_category` | Functional grouping | F | F | R | Yes (non-wearable) | Dropdown | Yes | equipment, accessories, etc. |
| `usage_subtype` | Type within usage | F | F | R | Yes | Dropdown | Yes | helmets, bottles, balls |
| `size_or_capacity` | Size / volume spec | F | F | O | No | Free text | No | Not path-relevant |

---

### A.2.6 Media Columns

**Ordering rationale:**  
These columns represent visual realization of previously defined meaning and must follow all semantic and structural classification.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `images` | Primary media list | R | R | R | Yes | Path list | Yes | Canonical source |
| `thumbnails_json` | Full media set | O | O | O | No | Path list | Yes | Must match contract |
| `chosen_image` | Editor-selected image | O | O | O | No | Single path | Yes | Must exist in thumbnails |
| `hero_image` | Computed or overridden hero | O | O | O | No | Single path | Yes | Read-only in Excel |

---

### A.2.7 Governance / Derived Columns (Optional, Recommended)

**Ordering rationale:**  
These columns are derived from and validate upstream data and therefore must appear last in the dependency chain.

| Column Name | Semantic Meaning | Apparel | Footwear | Non-wearable | Required? | Validation Type | Image Path Role | Notes |
|------------|-----------------|---------|----------|--------------|-----------|-----------------|-----------------|-------|
| `domain_colour_flag` | UI colour coding | — | — | — | No | Derived | — | For Excel formatting |
| `gender_required_flag` | Validation helper | — | — | — | No | Derived | — | Drives warnings |
| `validation_status` | Row health indicator | — | — | — | No | Derived | — | Pass / Fail |

---

## A.3 Design Invariants (Non-Negotiable)

- One worksheet, one schema
- Domains govern applicability, not column existence
- NULL represents truthful absence, not missing data
- No semantic meaning is encoded in filenames
- Image paths are **compiled**, never authored freehand

---

## A.4 Freeze Statement

Part A v1.1 is hereby **frozen**.

No new columns, reordering, or semantic changes may be introduced without a version increment and explicit rationale.

Subsequent work must treat this section as authoritative.

---
