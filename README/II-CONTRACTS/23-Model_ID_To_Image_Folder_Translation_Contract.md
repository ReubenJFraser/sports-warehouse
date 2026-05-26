# Model_ID → Image Folder Translation Contract

## Purpose

This contract clarifies how to translate structured product identity into image-folder organization without weakening canonical identity governance.

It extends Contract 22 by defining how:

- `model_id` (flat canonical identity)
- structured ProductDB fields
- semantic source/archive image hierarchy
- runtime/database image paths

relate to each other.

---

## Scope

This contract governs:

- translation logic from structured product fields to semantic image folders
- folder-boundary rules
- colour/variant folder placement
- governance expectations for scripts that report or generate folder paths

This contract does **not** authorize:

- editing ProductDB rows
- changing DBeaver/MySQL data
- changing runtime code/importers/SQL/PHP/frontend
- copying/renaming/moving/deleting image files unless separately approved as a reviewed migration task

---

## Relationship to Contracts 13–22

- Contract 19 remains authoritative for `model_id` generation/governance.
- Contract 22 remains authoritative for `model_id` as canonical identity bridge across ProductDB, MySQL rows, and image matching.
- Contracts 13–21 remain relevant for folder governance and product/variant structure.

This contract adds a focused clarification:

> Folder boundaries must come from structured product fields and governed product attributes, not from blindly splitting `model_id` at every underscore.

---

## Core Distinction: Identity String vs Folder Hierarchy vs Runtime Paths

### 1) `model_id` (flat canonical identity string)

`model_id` is a deterministic, field-derived identity key used to uniquely identify the product model.

### 2) Semantic image folder hierarchy (source/archive organization)

Semantic folder hierarchy is a human-readable, structured organization layer for source/archive media. It may be generated from the same structured fields behind `model_id`, but it is not required to mirror token boundaries from naive underscore splitting.

### 3) Runtime/database image paths (delivery/mapping layer)

Runtime/database image paths may later be implemented as:

- model_id-based paths,
- semantic-path-based paths,
- or generated mapping paths between semantic source/archive layout and runtime delivery layout.

All three are valid future strategies if governed and deterministic.

---

## Translation Rule: Structured Boundaries, Not Raw Underscore Splitting

Scripts must **not** convert `model_id` to folders by blindly replacing every `_` with `/`.

Why:

Underscores serve two different jobs:

1. component separation in `model_id`, and
2. intra-value word joining inside one component.

Examples of compound field values that may need to remain intact as single governed values:

- `sports_bra`
- `high_waisted`
- `full_length`
- `light_support`
- `elastic_underbust_band`
- `square_neck`
- `straight_back`

These values must not be split into multiple folder levels unless there is an explicit governed rule that defines such decomposition.

---

## Shared Structural Axes and Reusable Folder Levels

Some attributes represent reusable structural axes and may be promoted to shared folder levels (for example `model_family` values such as `v`).

This is intentional when it improves structural consistency across related products.

`v` can therefore be a shared folder level across a family of products, rather than only a trailing identity token.

---

## Required Example Family (Ryderwear NKD Leggings)

Given these `model_id` values:

- `ryderwear_female_nkd_leggings_v_high_waisted_scrunch`
- `ryderwear_female_nkd_leggings_v_full_length_scrunch`
- `ryderwear_female_nkd_leggings_v_full_length_pocket_scrunch`

Shared structured identity:

- `brand`: `ryderwear`
- `gender`: `female`
- `collection`: `nkd`
- `subCategory`: `leggings`
- structural family: `v`

Then branch using structured attributes such as:

- `high_waisted`
- `full_length`
- `pocket`
- `scrunch`

Interpretation rule:

- `v` may be represented as a shared folder level.
- Branch attributes are applied as governed structured levels, not as arbitrary underscore fragments.

---

## Colour/Variant Rule (Mandatory)

Every product item has colour variants.

The **final folder before terminal media files must be the colour/variant folder**.

- Product/model identity levels sit above colour.
- Colour is variant-level.
- Terminal media files can be numeric (`01.png`, `02.png`, `03.png`, etc.).
- Numeric filenames are acceptable because identity meaning is carried by semantic path and/or `model_id` mapping.

Illustrative structure:

```text
Ryderwear/
  Female/
    NKD/
      Leggings/
        V/
          Full_Length/
            Pocket/
              Scrunch/
                Black/
                  01.png
                  02.png
                Navy/
                  01.png
                  02.png
```

---

## Other Product Media (Beyond Images)

Product-specific files besides images (e.g., videos, source edits, working files, notes, metadata exports) may live under the same product/colour hierarchy.

If mixed file types accumulate, governance should define controlled subfolders and/or naming conventions (for example, `images/`, `video/`, `source/`, `notes/`, `metadata/`) to preserve clarity and avoid collisions.

---

## Technical Risks and Trade-offs

### Benefits of deep semantic source/archive paths

- High human readability
- Better curation context
- Strong archival meaning

### Risks of deep semantic paths

- Long path depth can create Windows/path-management friction
- Folder renames can break direct stored-path references if DB paths are tightly bound

### Benefits of model_id-based runtime paths

- Simpler and highly deterministic
- Easier programmatic generation
- Lower path interpretation ambiguity

### Project strategy flexibility

The project may later choose any governed runtime strategy:

1. semantic runtime paths,
2. model_id runtime paths,
3. or mapping from semantic source/archive paths to runtime paths.

This contract preserves both semantic archive value and model_id-centered canonical identity.

---

## Recommended Governance

1. `model_id` remains the canonical flat identity key.
2. Semantic folder hierarchy is generated from structured ProductDB fields and governed attributes, not guessed from raw underscore splitting.
3. Colour is the final variant-level folder before terminal numeric files.
4. Scripts must validate `model_id` uniqueness before path generation/mapping.
5. Scripts should report the structured field path used for each product (for auditability).
6. Scripts must not copy, rename, move, or delete image files unless explicitly executing an approved, reviewed image migration task.

---

## Examples Using Current ProductDB model_id Values

### A) Ryderwear NKD leggings example

- `ryderwear_female_nkd_leggings_v_full_length_pocket_scrunch`
- Structured interpretation: Brand `Ryderwear` → Gender `Female` → Collection `NKD` → SubCategory `Leggings` → Family `V` → Length `Full_Length` → Variant `Pocket` → Flag `Scrunch` → Colour folder → numeric media files.

### B) Ryderwear sports bra example

- `ryderwear_female_nkd_sports_bra_longline_v_neck_halter_light_support_scrunch`
- Structured interpretation keeps compound values intact where governed (e.g., `sports_bra`, `light_support`) and does not split them into arbitrary folder levels unless explicitly defined by field-level translation rules.

### C) Adidas example

- `adidas_female_powerreact_sports_bra_3-stripes`
- Structured interpretation may use Brand/Gender/Collection/SubCategory/Variant axes; runtime delivery may remain semantic-path-based, model_id-based, or mapping-based depending on chosen deployment strategy.

---

## Non-Replacement Statement

This is a clarification contract.

It does not replace Contracts 13–22 and must be interpreted with them, with Contract 19 governing model_id generation and Contract 22 governing model_id’s canonical cross-system identity role.


## Current Interpretation Note (2026-05-26)

This translation contract remains valid and is interpreted as follows:

- Translation must be driven by governed structured fields and axis boundaries, not naive underscore token splitting.
- Parent folders define broad taxonomy; final product/variant branches should avoid redundantly repeating tokens already encoded above.
- Colour/variant folders remain terminal or near-terminal before media files; numeric filenames are valid at that level.
- Runtime path implementation may be semantic-path-based, model_id-based, or mapping-based if deterministic and documented.
- Matching by token overlap is insufficient when it conflicts with model_id, collection, subCategory, or product type constraints.
