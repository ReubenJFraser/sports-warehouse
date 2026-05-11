# Image Path Contract, Excel Validation Gate & Failure Diagnostics  
Canonical Brand, Collection, and Category Model

## Overall Purpose

This README defines a **single, authoritative framework** for product image paths in the Sports Warehouse system.

It is composed of **three tightly linked parts**, each serving a distinct governance role:

- **Part 1 — Image Path Contract**  
  Defines what a correct image path is, structurally and semantically. This part establishes the canonical model and its invariants.

- **Part 2 — Excel Image Path Validation Gate**  
  Defines how correctness is enforced at the source of truth, before ingestion into the database. This part operationalizes the contract.

- **Part 3 — Common Failure Modes & Diagnostics**  
  Defines how to diagnose and prove the cause when image paths appear correct but fail in practice. This part turns violations into actionable evidence.

All three parts must be read together.  
The contract defines *truth*; the validation gate enforces it; the diagnostics explain what has gone wrong when truth is violated.

This is a governance document, not a tutorial.

---

## Scope

This README applies to:

- All product image paths authored in Excel
- All downstream database representations
- The filesystem under `images/brands/`
- Admin and frontend image rendering

It does not define presentation order, image scoring logic, hero selection logic, or brand marketing terminology.  
It defines **path correctness, structure, and meaning only**.

---

## Authority Chain

The authority chain for image paths is strictly ordered:

Excel → Local MySQL (via DBeaver) → Cloudways MySQL → Filesystem → Admin / Frontend UI

Downstream layers must not silently normalize, repair, or reinterpret paths.

---

# Part 1 — Image Path Contract

## Part 1 Introduction

This part defines the **canonical structure and semantics** of image paths.

It answers the question:  
*What does a correct image path look like, and why?*

---

## Canonical Root Rule (Non-Negotiable)

All product image paths must begin with the canonical root prefix `images/brands/{brand}/`.

Any path that does not begin with `images/brands/` is invalid.

Legacy patterns such as `images/{brand}/`, brandless paths, or mixed prefixes within a single product row are explicitly forbidden.

---

## Ryderwear as the Canonical Complexity Template

Ryderwear is the **reference brand** for image path design.

Ryderwear is used as the template because it has a large and continually expanding set of collections; because each collection contains broadly similar base categories of sportswear; because those categories subdivide into meaningful subcategories and optional variants; and because the resulting complexity is systematic rather than subjective.

Other brands may be structurally simpler, but no brand may exceed the structural complexity represented by Ryderwear. All other brands must be representable as a subset or collapse of the Ryderwear model.

---

## Canonical Folder Order (Structural Invariant)

The canonical folder order is fixed and must be interpreted as the following semantic sequence, in this order:

brand → gender → collection → base category → subcategory → optional variant → colour → files

In concrete terms, a fully expanded path reads as:

`images/brands/{brand}/{gender}/{collection}/{base_category}/{subcategory}/{variant}/{colour}/{files}`

Some levels may be omitted when they are not meaningful for a given product.  
The relative order of levels must never change.  
Omission is permitted; reordering is not.

---

## Semantic Meaning and Rules by Level

### Brand

The brand level uses a lowercase canonical slug with exactly one spelling per brand, for example `ryderwear`.

### Prohibited Brand Values

`other` is not a valid brand.

Any path beginning with:

`images/brands/other/`

is invalid.

Products that do not belong to a known commercial brand must still be assigned a canonical brand slug.

For example, Designer as a **Canonical Brand Fallback**.

Designer products must then declare the designer name as the next path segment.

Examples (valid):

`images/brands/designer/kate_galliano/women/bodysuit/bubblegum/01.png`  
`images/brands/designer/lisa_trujillo/kids/activewear/01.jpg`

### Gender

Gender is a first-class structural axis for **wearable products**.

Valid gender values are:

- `women`
- `men`
- `unisex`

Gender values must be written in singular form and must appear immediately after the brand segment.

For **kids apparel**, gender may be further refined one level deeper as:

- `kids/boys`
- `kids/girls`
- `kids/unisex`

In such cases, `kids` occupies the gender position, and the child-gender qualifier occupies the next level.

Examples (valid):

`images/brands/adidas/kids/boys/marvel-spider_man/tracksuit/`
`images/brands/nike/kids/girls/futura/`

Gender must not be inferred. It must be explicitly encoded where applicable.

### Gender Applicability by Product Domain

Gender participation in the image path is governed by product domain.

#### Wearables (Apparel)

Gender is **mandatory** for all wearable products, including but not limited to:

- sports bras
- leggings
- shorts
- tops
- bodysuits
- tracksuits

All apparel must declare gender explicitly.

---

#### Footwear

Footwear **may be gendered** or **model-driven**, depending on the product.

Rules:

- If the shoe is marketed as gender-specific, the gender segment must be present.
- If the shoe is a true unisex model, `unisex` must be used.
- Gender must not be omitted for shoes unless the model is explicitly unisex.

Examples (valid):

`images/brands/asics/men/running_shoes/kayano_26/`
`images/brands/puma/unisex/training_shoes/rs-x³_spectra/`

---

#### Non-Wearables (Accessories & Equipment)

Non-wearable products **must not encode gender** unless gender materially affects the product.

Examples of non-wearables:

- balls
- water bottles
- helmets
- gloves
- protective gear

For these products, the gender segment is omitted entirely.

Examples (valid):

`images/brands/adidas/UEFA_Euro16-Top_Glider_Ball.png`
`images/brands/nike/600ml_waterbottle.png`
`images/brands/protec-skate_helmet.png`
`images/brands/sting-armaplus-boxing_gloves-T3.png`

Encoding gender for non-wearables without a material basis is prohibited.

### Collection

The collection level represents a first-class organisational unit, for example `nkd`.

Organisation by collection takes precedence over organisation by clothing category. Multiple collections may contain the same categories and subcategories.

### Base Category

Canonical base categories are `leggings`, `shorts`, `sports_bra`, `bodysuits`, and `tops`.

A base category must appear exactly once in a path and must not be repeated at lower levels.

### Subcategory (Type Within Category)

Subcategories refine the type within a base category.

For example, within the NKD collection, `sports_bra` may contain `bandeau`, `knit`, `one_shoulder`, `staples`, and `twist`.

Subcategory names must not repeat the base category name. A path such as `sports_bra/one_shoulder_sports_bra` is explicitly forbidden, because the fact that the item is a sports bra is already encoded earlier in the hierarchy.

### Variant (Optional)

Variants represent structural or textural distinctions rather than colours, such as `marl`, `ribbed`, or `seamless`.

Variants are optional and must be included only when they encode meaningful information.

Variants are brand- and collection-specific and must not be assumed to exist uniformly across brands or across collections within the same brand.

### Colour Cardinality and Product Rows

This contract distinguishes **product identity** from **colour variation**.

Colour is a variant dimension, not a product-defining attribute.

---

#### 1. Product Row Identity

A single product row represents exactly one product identity.

Product identity is defined by the invariant combination of:

- brand
- gender or age group
- collection
- subcategory (product type)
- variant (if present)

A product row MUST NOT be duplicated solely to represent colour differences.

---

#### 2. Colour Variants Within a Row

When a product exists in multiple colour variants, the `images` column MAY contain multiple image roots, encoded as a semicolon-delimited list.

This is permitted only when ALL of the following conditions are met:

- All image roots share the same canonical path up to the colour folder
- The colour folder is the only differing path segment
- Each colour root resolves to its own numbered image set (`01.png`, `02.png`, …)

Multiple roots within a row represent colour siblings of the same product.

---

#### 3. Canonical Interpretation

When multiple colour roots are present, the canonical interpretation is:

product → colour → images


Colour is subordinate to product identity.

Downstream systems MUST NOT infer separate products from colour variation alone.

---

#### 4. Prohibited Mixing

The following MUST NOT be mixed within a single product row:

- different brands
- different genders or age groups
- different collections
- different subcategories
- different variants

If any of the above differ, a separate product row is REQUIRED.

Colour variation is the only permitted reason for multiple image roots within a single row.

---

#### 5. Design Intent

This model exists to support products with many colour variants without:

- duplicating rows
- degrading Excel readability
- introducing ingestion ambiguity

Any alternative model (e.g. one row per colour) constitutes a different authoring contract and MUST be documented explicitly.

## File Naming Rules

Image and video files use two-digit, zero-padded numeric filenames such as `01.png`, `02.png`, and so on.

The numeric order reflects presentation sequence only. Descriptive filenames at the file level are prohibited. File extensions must be lowercase. Mixed media such as mp4 is permitted but must follow the same numbering scheme.

---

## Folder Structure vs Filename Semantics (Clarifying Principle)

All semantic distinction between product images is expressed by **folder structure**, not by filenames.

Image filenames are intentionally generic (e.g. `01.png`, `02.png`).  
They exist only to order images within a resolved image set.

Distinct image sets are defined exclusively by folder paths.

---

### Consequence

Only attributes that require different images may participate in image path construction.

Attributes that affect product meaning but do not change the images shown must remain outside the image path.

---

### Example: `variant` vs `sizeType`

`variant` (e.g. `marl`, `ribbed`, `stonewash`) represents structural or textural differences that alter the physical appearance of a product.

Variants require different images and therefore participate in image path construction.

`sizeType` (e.g. `standard`, `plus`, `tall`) represents a sizing or grading system.

`sizeType` does not alter the visual appearance of the product and therefore does not require different images.

Accordingly:

- `variant` appears as an optional folder level when present
- `sizeType` must not appear as a folder level
- `sizeType` must not be encoded in filenames

This distinction preserves deterministic image selection and prevents semantic overload of image paths.

## Worked Ryderwear Examples (Authoritative)

A Ryderwear NKD twist sports bra with a marl variant and coral colour is represented by a path equivalent to `images/brands/ryderwear/women/nkd/sports_bra/twist/marl/coral/` followed by files named `01.png` through `04.png`.

A Ryderwear NKD twist sports bra without a variant, in cucumber colour, is represented by a path equivalent to `images/brands/ryderwear/women/nkd/sports_bra/twist/cucumber/` followed by files named `01.png` through `06.png`.

In both cases, the path encodes gender, collection, base category, subcategory, optional variant, and colour without repetition or ambiguity.

---

## Rules for Simpler Brands

Brands with less internal complexity may omit variant levels, may have fewer subcategories, and may have minimal or implicit collections.

However, the canonical folder order must always be preserved. Structural simplicity is achieved by omission, not by invention. Parallel brand-specific hierarchies are not permitted.

---

### 7.X Footwear Domain (Shoes)

This subsection defines the **footwear-specific interpretation** of the Image Path Contract.  
It applies to all shoes, including sneakers, running shoes, soccer boots, training shoes, and kids’ shoes.

Footwear is governed by the **same structural principles as apparel**, but with simpler and more brand-dependent hierarchies.

---

#### 7.X.1 Base Category: Shoes

All footwear products use the base category:

- `shoes`

The base category `shoes` must appear **exactly once** in the path and must not be repeated in subcategory or variant names.

Paths such as `shoes/running_shoes_shoes` are explicitly forbidden.

---

#### 7.X.2 Subcategory (Type of Shoe)

Subcategories represent the functional type of footwear and map directly to system categories, for example:

- `sneakers`
- `running_shoes`
- `soccer_boots`
- `training_shoes`
- `kids_shoes`

Rules:

- Subcategories must refine the base category `shoes`
- Subcategory names must not repeat the word `shoes`
- Subcategories must be stable and system-wide, not marketing-driven

---

#### 7.X.3 Collection vs Model Family (Critical Distinction)

For footwear, **collection and model family are not interchangeable** and must be used carefully.

**Collection** applies when the brand explicitly organises footwear under a reusable, cross-category concept  
(e.g. Adidas `3-stripes`, Nike `zenvy`, Puma `rs-x³`).

**Model family** applies when the shoe is defined primarily by a named model rather than a broader collection  
(e.g. Asics `kayano`, Reebok `nano_X3`).

Rules:

- If a brand uses a clear collection concept, it occupies the **collection level**
- If no meaningful collection exists, the collection level may be omitted
- Model names must **not** be invented as collections to pad hierarchy

Example (collection-driven):

`images/brands/adidas/men/3-stripes/shoes/sneakers/...`

Example (model-driven, no collection):

`images/brands/asics/unisex/shoes/running_shoes/kayano/...`

Organisation by collection is preferred **only when the brand itself supports it**.

---

#### 7.X.4 Variant (Optional)

Variants represent structural distinctions that are **not colours**, such as:

- `wide_fit`
- `gore_tex`
- `knit_upper`
- `low_cut`

Rules:

- Variants are optional
- Variants are brand- and collection-specific
- Variants must not encode colour or marketing language
- If present, variants must appear **after subcategory and before colour**

Absence of a variant is not an error condition.

---

#### 7.X.5 Colour (Conditional but First-Class)

Colour is a first-class semantic level for footwear **whenever multiple colourways exist or are expected**.

Rules:

- One folder per colourway
- Colour names must be semantic (e.g. `black`, `pink_and_white`, `court_brown`)
- Colour must not be encoded in filenames or merged with variants
- If only a single colourway exists, the colour level may be omitted

Colour follows variant if a variant exists, otherwise it follows subcategory.

---

#### 7.X.6 File Naming

Footwear files follow the global file naming rules:

- Two-digit, zero-padded numeric filenames (e.g. `01.png`)
- Lowercase extensions
- Mixed media (e.g. `mp4`) permitted if numbered consistently
- Descriptive filenames are forbidden

---

#### 7.X.7 Semantic Duplication Prohibition (Footwear)

A footwear path must not:

- Repeat `shoes` in subcategory or variant names
- Combine model, colour, and variant into a single folder
- Encode category meaning in filenames
- Mix collection logic and model logic in the same path

Meaning must be distributed **across folders**, not compressed into names.

---

#### 7.X.8 Footwear as a Subset of the Canonical Model

Footwear paths are a **strict subset** of the global Image Path Contract.

They may omit collection, variant, or colour levels when not meaningful, but must always preserve:

- Canonical folder order
- Singular gender naming
- Single base category occurrence
- Unambiguous semantic meaning

Structural simplicity is achieved by omission, not by invention.

---

## Ryderwear Women — Axis Dependency Alignment Clause

This section formally synchronizes the Image Path Contract with the Ryderwear Women Harmonization Contract (15-Ryderwear-Women_Folder_System—Harmonization_Contract.md).

For Ryderwear Women products, folder order must respect the Canonical Axis Dependency Rule defined in the Harmonization Contract.

The structural sequence:

brand → gender → collection → base category → subcategory → optional variant → colour → files

remains non-negotiable.

However, within the "optional variant" position, axis ordering must respect semantic dependency, not marketing frequency.

The following rules apply:

1. Fabric may act as a primary structural axis.
2. Construction may act as a primary structural axis.
3. Rise is subordinate to Construction.
4. Scrunch is subordinate to Construction.
5. Invisible is subordinate to Scrunch.
6. Seamless is subordinate to Fabric.
7. No modifier may appear above its semantic parent.
8. Negative encoding (e.g., Non-Scrunch) is prohibited.

Axis ordering is governed by structural dependency, not by product prevalence or sales distribution.

If Axis A logically constrains or contains Axis B, then A must appear above B in the folder hierarchy.

Example (valid):

Fabric  
 └── Ultra_Soft  
     └── Seamless  
         └── Scrunch  
             └── Invisible  

Example (invalid):

Seamless  
 └── Ultra_Soft  

This clause ensures that the Image Path Contract remains structurally consistent with Ryderwear Women harmonization logic without altering the global canonical folder order.

All Ryderwear Women paths must satisfy both contracts simultaneously.

## Part 1 Conclusion

The Image Path Contract exists to encode meaning through structure.  
It prioritizes collection-first organisation, avoids semantic duplication, and ensures paths remain interpretable by both humans and machines.

---

# Part 2 — Excel Image Path Validation Gate

## Part 2 Introduction

This part defines the **strict Excel-side validation rules** that enforce the Image Path Contract at the source of truth.

It answers the question:  
*How do we ensure only valid paths ever enter the database?*

Validation happens **before ingestion**, not after.

---

## Validation Scope

The validation gate applies to all Excel columns containing image or media paths, including `images`, `thumbnails_json`, `chosen_image`, `hero_image`, and any future image-related column.

Validation is row-based and cell-based. Every relevant cell must pass validation independently. A single failing path invalidates the entire row.

---

## Canonical Root Validation

Every image path must begin with `images/brands/`.

Any path beginning with `images/{brand}/`, any brandless path, or any legacy pattern fails validation.

---

## Single-Prefix Consistency Rule

Within a single Excel cell, all image paths must share the same canonical prefix.

Mixed prefixes, even if one path happens to exist on disk, invalidate the row.

---

## Canonical Folder Order Validation

Each image path must unambiguously conform to the canonical semantic order:

brand → gender → collection → base category → subcategory → optional variant → colour → file

Levels may be omitted only if semantically meaningless. Levels may not be reordered. Alternative hierarchies are not permitted.

---

---

## Axis Dependency Validation (Ryderwear Women)

For Ryderwear Women products, Excel validation must also enforce Axis Dependency constraints defined in the Harmonization Contract.

Validation must confirm:

- Seamless never appears above Fabric.
- Invisible never appears above Scrunch.
- Rise never appears above Construction.
- No negative encoding is present.
- Axis ordering reflects structural containment, not naming convenience.

A path may conform to canonical folder order and still fail validation if axis dependency is violated.

Axis dependency is a structural invariant, not a naming preference.

## Gender Validation

The gender segment must be exactly one of `women`, `men`, or `unisex`, must appear immediately after the brand segment, and must not be inferred or omitted where applicable.

---

## Base Category Validation

The base category segment must be one of the canonical system categories and must appear exactly once.

Base category names must not appear in subcategory or variant names. Semantic repetition invalidates the row.

---

## Subcategory and Variant Validation

Subcategories must refine the base category and must not repeat it.

Variants are optional, must represent structural or textural distinctions, must not represent colours, and must appear after subcategory and before colour when present.

Incorrect placement invalidates the row.

---

## Colour Validation

Colour folders must represent semantic colours, must be singular, and must appear after variant if a variant exists or after subcategory otherwise.

Colour information must not be encoded elsewhere in the path.

---

## File Naming Validation

File names must use two-digit, zero-padded numeric format. Extensions must be lowercase. Descriptive filenames are forbidden. Mixed media is permitted if numbering rules are respected.

---

## Filesystem Realizability Check

Every image path must correspond to an existing file on disk under the canonical filesystem root. Case sensitivity must be respected.

Paths that “will exist later” are invalid.

---

## Row-Level Acceptance Criteria

A row is accepted for ingestion only if all image paths in all relevant cells pass every validation rule. Any failure requires correction in Excel before ingestion proceeds.

---

## Part 2 Conclusion

The Excel Validation Gate exists to prevent drift, not to repair it.  
By enforcing correctness at the source of truth, downstream systems remain simple, honest, and auditable.

---

## Overall Conclusion and Invariants

Excel is the source of truth.  
Structure encodes meaning.  
Collection organisation takes precedence over category grouping.  
Truth is preferred over convenience.  
Silent normalization is prohibited.

This combined contract and validation gate exists to make complexity legible, enforceable, and governable over time.

# Part 3 — Common Failure Modes & How to Diagnose Them

## Part 3 Introduction

This part documents the **most common real-world failure modes** observed when image paths appear to be “mostly correct” yet result in broken images, placeholders, incorrect hero selection, or inconsistent admin behavior.

Its purpose is diagnostic, not corrective.

This section answers the question:  
*When something goes wrong despite apparent compliance, where do we look first, and how do we prove the cause?*

All diagnosis must be grounded in evidence. Guessing is explicitly prohibited.

---

## Failure Mode 1 — Legacy Prefix Contamination

### Description

One or more image paths still use a legacy prefix such as `images/{brand}/` instead of the canonical `images/brands/{brand}/`.

This may occur even when:
- Other paths in the same cell are correct
- The file exists on disk
- The frontend appears to load some images correctly

### Symptoms

- Admin thumbnails show placeholders
- Some candidates render while others do not
- Hero images show placeholder SVGs despite non-zero scores
- Mixed behavior within the same product item

### Diagnosis Procedure

1. Inspect the raw Excel cell value.
2. Check every path in the cell, not just the first.
3. Confirm all paths begin with `images/brands/`.
4. Confirm there is no mixture of canonical and legacy prefixes.

If any legacy prefix is present, the row fails validation regardless of filesystem state.

---

## Failure Mode 2 — Mixed-Era Paths Within a Single Row

### Description

A single product row contains a mixture of:
- New canonical paths
- Older pre-contract paths
- Manually edited or partially migrated paths

This often arises when Excel updates were selective or incremental rather than full-row replacements.

### Symptoms

- One candidate image renders correctly while others do not
- The “best” scored candidate loads, but others fall back to placeholders
- Inconsistent behavior between hero image, candidate images, and gallery images

### Diagnosis Procedure

1. Treat the entire row as suspect.
2. Compare `images`, `thumbnails_json`, `chosen_image`, and `hero_image` columns.
3. Verify that all paths across all columns share:
   - the same canonical root
   - the same brand slug
   - the same folder order

If any column lags behind the others, the row is invalid.

---

## Failure Mode 3 — Canonical Order Violation Disguised as a Valid Path

### Description

A path appears syntactically valid but violates the canonical folder order, for example by swapping collection and category, or by embedding category meaning in a subcategory folder.

### Symptoms

- Files exist on disk but do not render in admin
- Admin helper functions fail existence checks
- Hero candidates show generic “historical” scores
- Path looks reasonable to a human but fails programmatic assumptions

### Diagnosis Procedure

1. Decompose the path semantically.
2. Map each folder segment to the canonical sequence:
   brand → gender → collection → base category → subcategory → variant → colour → file
3. Confirm no semantic level is skipped, duplicated, or reordered.
4. Confirm meaning is distributed across folders, not compressed into names.

If semantic mapping is ambiguous, the path is invalid.

---

## Failure Mode 4 — Base Category Duplication

### Description

The base category appears more than once, typically because brand-specific naming was preserved verbatim instead of normalized structurally.

Example pattern: repeating `sports_bra` inside a subcategory name.

### Symptoms

- Admin UI loads candidates but mislabels them
- Filtering logic behaves unpredictably
- AI or heuristic reasoning misclassifies the product type

### Diagnosis Procedure

1. Search for base category names appearing below the base category folder.
2. Confirm that subcategory names refine the base category rather than restating it.
3. If repetition exists, the row fails validation even if images render.

This failure mode often does not break rendering immediately, but it corrupts semantics.

---

## Failure Mode 5 — Variant and Colour Conflation

### Description

A single folder encodes both a structural variant and a colour, for example by combining textural and colour meaning in one name.

### Symptoms

- Filtering by colour or variant becomes impossible
- AI reasoning cannot separate structural vs aesthetic attributes
- Future expansion (new colours or variants) becomes blocked

### Diagnosis Procedure

1. Identify whether a folder name encodes more than one meaning.
2. Confirm that variants represent structure or texture only.
3. Confirm that colours are represented in their own dedicated folder.

If meanings are conflated, the path is structurally invalid even if it renders.

---

## Failure Mode 6 — Filesystem Drift (Path Exists in Excel but Not on Disk)

### Description

The path is structurally correct but the corresponding file is missing, renamed, or case-mismatched on disk.

This often occurs after:
- Manual file moves
- Partial brand refactors
- Windows-based edits followed by Linux deployment

### Symptoms

- Admin renders placeholders
- Direct browser requests to the image URL return 404
- Files appear to exist locally but not on Cloudways

### Diagnosis Procedure

1. Copy the exact image URL from the admin HTML.
2. Open it directly in the browser.
3. If it fails, locate the expected file on disk.
4. Check case sensitivity explicitly.
5. Confirm the file path matches Excel exactly.

Filesystem existence is mandatory, not optional.

---

## Failure Mode 7 — Hero Image Columns Out of Sync

### Description

`chosen_image`, `hero_image`, and `thumbnails_json` encode different path eras or formats for the same product.

This often occurs when:
- Hero logic was updated before image paths were normalized
- One column was updated from Excel while others were not

### Symptoms

- Computed hero score exists but hero image is a placeholder
- Candidate image renders but selected hero does not
- Admin shows conflicting “current hero” signals

### Diagnosis Procedure

1. Compare all image-related columns for the item.
2. Confirm they reference the same canonical path system.
3. Confirm hero selection logic points to a path that exists and is valid under the contract.

Hero logic is only as reliable as the paths it references.

---

## Failure Mode 8 — Admin Helper Fallback Masking Root Cause

### Description

Admin helper functions correctly fall back to placeholder images when paths fail validation or existence checks, but this masks the underlying cause.

### Symptoms

- Everything “looks fine” except images are placeholders
- No visible error messages
- Silent failure encourages repeated misdiagnosis

### Diagnosis Procedure

1. Inspect the rendered HTML for the image element.
2. Identify whether the `src` points to a real product path or a placeholder asset.
3. Trace backward to determine why the helper rejected the original path.
4. Do not treat placeholder rendering as success.

Placeholders are a signal, not a solution.

---

## Part 3 Conclusion

All known failure modes reduce to one of three root causes:

- Structural violation of the Image Path Contract
- Incomplete or inconsistent Excel-side updates
- Filesystem drift relative to Excel truth

Diagnosis must always proceed in this order:

Excel → Database → Filesystem → Admin Rendering

Fixes must always occur at the **earliest failing layer**, never downstream.

This section exists to shorten debugging cycles, eliminate guesswork, and keep governance honest.

---

## Overall Governance Reminder

If an image does not render, the system is not “being fussy.”  
It is telling you something is untrue.

The correct response is to locate the lie, not to silence the signal.



