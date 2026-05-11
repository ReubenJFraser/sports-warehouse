# Product Model & Variant Separation Contract  
**File Location:** README/II-CONTRACTS/17-Product_Model_and_Variant_Separation_Contract.md  
**Status:** Authoritative  
**Scope:** Excel ↔ Filesystem Semantic Alignment (Colour Normalization Phase)

---

# 1. Purpose

This contract formalizes the structural separation between:

- **Product Model (Parent)**
- **Product Variant (Child / SKU-Level Entity)**

This separation resolves the colour axis conflict and aligns the Excel data model with the filesystem architecture.

---

# 2. Core Principle

Colour is:

- A single-value axis.
- Variant-defining.
- Not multi-valued within a model row.
- Not to be stored as comma-separated values.
- Not to be embedded in Fabric or any other axis.

Each colour represents a distinct purchasable variant.

---

# 3. Definitions

## 3.1 Product Model

A Product Model represents:

- A silhouette
- A structural configuration
- A shared axis configuration
- A conceptual product identity

It does **not** represent a specific colourway.

### Model-Level Axes

Examples:
- brand
- category
- subCategory
- fabric
- construction
- rise
- length
- support_level
- sports_bra_type
- usage_category
- usage_subtype
- scrunch_flag
- invisible_flag
- seamless

A Product Model row must never contain multiple colours.

---

## 3.2 Product Variant

A Product Variant represents:

- A specific colourway of a Product Model
- A purchasable SKU instance

Each Product Variant must reference exactly one Product Model.

### Variant-Level Axes

- product_id (foreign key)
- colour (single value only)
- image_path_root
- thumbnails_json
- stock (future)
- price_override (future)

Each row in the ProductVariants sheet represents one unique colour variant.

---

# 4. Structural Separation

## 4.1 SportWarehouse_ProductDB (Model Table)

- One row per product model.
- No colour column.
- No multi-value fields.
- No image sets.
- No SKU duplication.

---

## 4.2 ProductVariants (New Sheet)

- One row per colour variant.
- Contains:
  - product_id
  - colour
  - image references

Colour must use:

Allow → List
Source → =ColourList


No free-text values permitted.

---

# 5. Filesystem Alignment Rule

Filesystem leaf colour folders represent Product Variants.

Example:

...Ultra_Soft\Cool_Mint
...Ultra_Soft\Cherry_Red


Each leaf colour folder corresponds to:

One ProductVariant row.

Filesystem depth = Variant level  
Model-level structure sits above colour folder.

---

# 6. Prohibited Patterns

The following are explicitly disallowed:

- Multi-value colour cells (e.g., "Black, Blue, Pink")
- Colour embedded inside Fabric axis
- Colour merged into construction
- Repeating entire model rows for each colour
- CSV-style composite storage

---

# 7. Composite Colour Handling

Composite colourways such as:

- Black_and_White
- Musk_Pink_and_Tan

Remain within the Colour axis as single values.

They are not patterns.

Pattern-based identifiers (e.g., Leopard) are future-axis candidates.

---

# 8. Benefits of Separation

This structure enables:

- Clean filtering
- Accurate colour swatch rendering
- Canonical SKU derivation
- Inventory scalability
- URL stability
- AI recommendation readiness
- Proper relational database migration

---

# 9. Migration Implication

Before Excel restructuring:

- ProductDB colour column must be deprecated.
- ProductVariants sheet must be created.
- product_id must be introduced to ProductDB.

Colour enforcement remains bounded via ColourList.

---

# 10. Architectural Status

This contract supersedes any prior debate regarding:

- Multi-value colour storage
- Per-colour row duplication
- Colour-as-model ambiguity

The system is now formally defined as:

**Model-Level Table + Variant-Level Table**

---

# End of Contract
