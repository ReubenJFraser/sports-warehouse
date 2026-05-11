# ProductVariants Sheet Schema Contract  
**File Location:** README/II-CONTRACTS/18-ProductVariants_Sheet_Schema_Contract.md  
**Status:** Authoritative  
**Scope:** Excel Data Layer — Variant-Level Storage

---

# 1. Purpose

This contract defines the schema for the ProductVariants worksheet.

ProductVariants stores colour-specific SKU-level data while SportWarehouse_ProductDB remains model-level.

This ensures:

- No multi-value colour fields.
- No model duplication.
- Filesystem alignment.
- Scalability for inventory and pricing.

---

# 2. Structural Relationship

ProductVariants must reference SportWarehouse_ProductDB via:

- product_id (foreign key)

One Product Model may have multiple ProductVariants.

Each ProductVariant belongs to exactly one Product Model.

Relationship type:

Model (1) → (Many) Variants

---

# 3. Required Columns

The ProductVariants sheet must contain the following columns in this order:

1. product_id
2. colour
3. image_path_root
4. thumbnails_json
5. sku_code
6. stock_quantity
7. price_override
8. is_active

---

# 4. Column Definitions

## 4.1 product_id

- Must match an existing product_id in SportWarehouse_ProductDB.
- Required.
- No free-text values permitted outside defined IDs.

---

## 4.2 colour

- Single value only.
- Data Validation:
  Allow → List
  Source → =ColourList
- Required.
- Composite colourways permitted.
- No multi-value cells allowed.

---

## 4.3 image_path_root

- Root folder path to the colour-level image set.
- Single value only.
- Must correspond to filesystem leaf folder.

Example:
images/brands/ryderwear/women/nkd/.../Cool_Mint/

---

## 4.4 thumbnails_json

- JSON string referencing thumbnails.
- Multi-image field.
- Associated only with this colour variant.

---

## 4.5 sku_code

- Reserved for future SKU-level unique identifier.
- Optional at present.
- Must be unique when populated.

---

## 4.6 stock_quantity

- Integer.
- Default blank or 0.
- Not required at present.

---

## 4.7 price_override

- Optional numeric field.
- Allows per-colour pricing.
- Blank = inherit model price.

---

## 4.8 is_active

- Yes/No list.
- Source → =YesNoList
- Default → TRUE
- Determines visibility.

---

# 5. Prohibited Structures

The following are forbidden:

- Multiple colours in one cell.
- Colour values not in ColourList.
- Variant rows without product_id.
- Variant rows without colour.
- Duplicate (product_id + colour) combinations.

The combination:

product_id + colour

Must be unique.

---

# 6. Filesystem Alignment Rule

Each colour leaf folder must correspond to exactly one ProductVariants row.

If a colour folder exists without a corresponding ProductVariant row, the system is out of sync.

---

# 7. Migration Rule

When implementing:

1. Remove colour from SportWarehouse_ProductDB.
2. Introduce product_id column in ProductDB.
3. Create ProductVariants sheet.
4. Populate ProductVariants from filesystem leaf folders.

---

# 8. Future Database Alignment

This structure maps cleanly to relational database schema:

ProductModels table
ProductVariants table
Foreign key constraint
Unique composite index (product_id, colour)

---

# End of Contract
