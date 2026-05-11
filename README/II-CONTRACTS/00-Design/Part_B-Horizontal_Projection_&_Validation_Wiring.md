# Part B — Horizontal Projection & Validation Wiring  
**Status: v1.0 (Draft → Pending Freeze)**

---

## B.0 Purpose and Relationship to Part A

Part B defines how the **vertically specified columns in Part A** are projected into a **real Excel worksheet**, and how validation, dropdowns, conditional logic, and visual cues are wired so that the Image Path Contract is enforced **before ingestion**.

If Part A defines **what exists and why**, Part B defines **how it is implemented and enforced**.

Part B does not introduce new semantics.  
It operationalizes Part A.

---

## B.1 Horizontal Projection Principle

Although columns were defined vertically in Part A for conceptual clarity, the Excel implementation is strictly horizontal.

The horizontal order **must mirror the dependency order defined in Part A**, left to right.

This ensures:

- early columns constrain later dropdowns
- validation logic can be expressed without circular references
- human editors naturally proceed in the correct semantic order

---

## B.2 Canonical Column Order (Left → Right)

The Excel worksheet must project columns in the following order:

1. **Identity & Governance**
2. **Collection / Model Axis**
3. **Gender & Audience**
4. **Domain-Specific Structure**
   - apparel / footwear structure
   - or non-wearable structure
5. **Media Columns**
6. **Governance / Derived Columns**

No column may appear to the left of a column it depends on.

---

## B.3 Domain-Driven Validation Architecture

### B.3.1 Single Worksheet, Multi-Domain

All product rows live in a **single `Items` worksheet**.

Domain differences are handled by:

- conditional validation
- domain-aware dropdowns
- NULL-permitted columns
- visual encoding

This avoids schema fragmentation while preserving strictness.

---

### B.3.2 Domain Selector as Primary Switch

The `product_domain` column is the **root switch** for all downstream validation.

All conditional logic references `product_domain` either directly or indirectly.

No validation rule may override or bypass the domain declaration.

---

## B.4 Dropdown & Data Validation Strategy

### B.4.1 Central Validation Sheets

All dropdown sources must live in **dedicated validation sheets**, for example:

- `VAL_Brands`
- `VAL_Collections`
- `VAL_Model_Families`
- `VAL_Base_Categories`
- `VAL_Subcategories`
- `VAL_Variants`
- `VAL_Genders`
- `VAL_NonWearable_Usage`

The `Items` sheet must never contain hardcoded validation lists.

---

### B.4.2 Domain-Aware Dropdowns (Same Column, Different Rules)

A single column may present **different dropdown sets** depending on domain.

This is implemented using:

- `INDIRECT`
- `FILTER`
- named ranges
- helper columns (hidden if necessary)

Example patterns:

- `subcategory_type` shows clothing subcategories for apparel rows
- the same column shows footwear types for footwear rows
- shows non-wearable subtypes for non-wearable rows

This preserves a unified schema without semantic ambiguity.

---

### B.4.3 Conditional Allowance of NULL

Columns marked **Optional or Forbidden** in Part A must enforce:

- forced blank (forbidden)
- optional blank (allowed)
- mandatory non-blank (required)

This is achieved using custom validation formulas, not dropdown defaults.

---

## B.5 Gender Applicability Wiring

### B.5.1 Decision Matrix Enforcement

The **Gender Applicability Decision Rules** defined in Part 2 of the Image Path Contract are enforced here.

Validation must consider:

- `product_domain`
- `age_group`
- explicit gender flags
- implicit gender markers (if used)

A helper column such as `gender_required_flag` may be used to simplify logic.

---

### B.5.2 Enforcement Rules

- If gender is required, blank cells must be rejected
- If gender is forbidden, non-blank cells must be rejected
- If gender is optional, both are allowed

No default value may be auto-filled.

---

## B.6 Media Column Wiring

### B.6.1 Images as Canonical Input

The `images` column is the **canonical media source**.

All other media-related columns must be validated **against it**, not independently.

---

### B.6.2 Path Validation

For every path in media columns:

- must begin with `images/brands/`
- must conform to the canonical folder order
- must be filesystem-realizable
- must not mix legacy and canonical prefixes

Validation may be implemented via:

- regex checks
- helper columns
- pre-ingestion scripts

---

### B.6.3 Cross-Column Consistency

- `chosen_image` must exist in `thumbnails_json`
- `hero_image` must exist in `thumbnails_json`
- no media column may reference an image absent from disk

---

## B.7 Visual Encoding (Row Colouring)

### B.7.1 Domain Colour Coding

Rows must be colour-coded by `product_domain` using conditional formatting.

Example scheme (non-prescriptive):

- Apparel → light blue
- Footwear → light green
- Non-wearable → light orange

Colour coding is **informational only** and must not substitute validation.

---

### B.7.2 Validation Status Indicators

Optional derived columns may visually flag:

- pass / fail
- missing required data
- forbidden data present

These indicators must never silently correct data.

---

## B.8 Error Handling Philosophy

Excel must **fail loudly and early**.

Invalid rows must be:

- visually obvious
- blocked from export
- corrected at the source

No downstream system is permitted to “fix” Excel errors.

---

## B.9 Invariants

- Part A defines meaning; Part B enforces it
- Domain drives validation, not convenience
- NULL is meaningful
- One worksheet, one truth
- No silent normalization

---

## B.10 Draft Status

Part B is currently **v1.0 (Draft)**.

It may be revised to incorporate:

- concrete Excel formulas
- named range definitions
- example screenshots

Once validation wiring is implemented and tested, this section may be frozen.

---

# Worked Excel Example — One Row per Domain  
**Applies to:** Image Path Contract & Excel Validation Gate  
**Status:** v1.0 (Illustrative, Non-Executable)

---

## Purpose of This Section

This section provides **worked, end-to-end examples** showing how a **single Excel worksheet** supports three distinct product domains—**Clothing**, **Footwear**, and **Non-Wearable Goods**—using the column model and validation logic defined in Parts A and B.

These examples are **illustrative**, not exhaustive.  
They exist to pressure-test clarity, not to introduce new rules.

Each example represents **one complete row** as it would appear in Excel.

---

## Domain A — Clothing (Wearable, Gender Required)

### Example: Women’s Ryderwear NKD Twist Sports Bra (Marl, Coral)

| Column Group | Column Name | Example Value | Notes |
|-------------|------------|---------------|------|
| Identity & Governance | product_domain | clothing | Drives all downstream validation |
|  | item_name | NKD Twist Sports Bra | Human-readable |
|  | brand | ryderwear | Canonical slug |
| Collection / Model | collection | nkd | Collection takes precedence |
|  | model_family | twist | Subcategory family |
| Gender & Audience | gender | women | **Mandatory** for clothing |
|  | age_group | adults | |
| Clothing Structure | base_category | sports_bra | Appears once only |
|  | subcategory | twist | Does not repeat base category |
|  | variant | marl | Optional but meaningful |
|  | colour | coral | Mandatory when applicable |
| Media | images | images/brands/ryderwear/women/nkd/sports_bra/twist/marl/coral/01.png;… | Canonical order |
|  | thumbnails_json | same set | Must align with images |
| Governance / Derived | path_valid | TRUE | Derived |
|  | validation_status | PASS | Derived |

**Why this passes:**  
All mandatory clothing fields are populated, ordering is correct, no semantic duplication exists, and colour is encoded structurally.

---

## Domain B — Footwear (Wearable, Gender Usually Required)

### Example: Adidas Men’s Campus Sneakers

| Column Group | Column Name | Example Value | Notes |
|-------------|------------|---------------|------|
| Identity & Governance | product_domain | footwear | |
|  | item_name | Campus Sneakers | |
|  | brand | adidas | |
| Collection / Model | collection | campus | Model family acts as collection |
|  | model_family | campus | Explicit footwear model |
| Gender & Audience | gender | men | Required (wearable) |
|  | age_group | adults | |
| Footwear Structure | shoe_type | sneakers | Maps to Shoes category |
|  | variant | NULL | Optional |
|  | colour | NULL | Colour not encoded structurally here |
| Media | images | images/brands/adidas/men/campus_sneakers/01.png;… | Simpler hierarchy |
| Governance / Derived | path_valid | TRUE | |
|  | validation_status | PASS | |

**Why this passes:**  
Footwear follows the same structural principles as clothing, but with fewer meaningful levels; omission is allowed without reordering.

---

## Domain C — Non-Wearable Goods (Unisex Default)

### Example: Nike 600ml Water Bottle

| Column Group | Column Name | Example Value | Notes |
|-------------|------------|---------------|------|
| Identity & Governance | product_domain | non_wearable | |
|  | item_name | 600ml Water Bottle | |
|  | brand | nike | |
| Collection / Model | collection | NULL | Not applicable |
|  | model_family | NULL | |
| Gender & Audience | gender | unisex | **Default / preferred** |
|  | age_group | NULL | Not age-specific |
| Non-Wearable Structure | equipment_type | water_bottle | Functional classification |
|  | usage_context | training | Optional |
| Media | images | images/brands/nike/other/600ml_waterbottle.png | Simplified path |
| Governance / Derived | path_valid | TRUE | |
|  | validation_status | PASS | |

**Why this passes:**  
Non-wearable goods use functional structure, default to `unisex`, and are not forced into apparel-style hierarchy.

---

## Comparative Summary (What This Demonstrates)

| Principle | Clothing | Footwear | Non-Wearable |
|---------|---------|----------|-------------|
| Single Items Sheet | ✓ | ✓ | ✓ |
| Domain-driven validation | ✓ | ✓ | ✓ |
| Gender required | Always | Usually | Sometimes |
| Collection importance | High | Medium | Low |
| Structural depth | High | Medium | Low |
| Order preserved | ✓ | ✓ | ✓ |

---

## Key Takeaways

- One worksheet can safely support multiple domains without ambiguity.
- Complexity varies by domain, but **ordering never changes**.
- Omission is allowed; reordering is not.
- Validation is driven by meaning, not by file layout convenience.

This worked example confirms that **Parts A and B are internally consistent and executable in practice**.

---
