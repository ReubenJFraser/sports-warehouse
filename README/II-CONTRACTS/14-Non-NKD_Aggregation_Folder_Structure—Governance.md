# Non-NKD Aggregation Folder Structure — Governance README

## 1. Purpose

This README defines the **authoritative folder-structure model** for the aggregated **Non-NKD** collection within:


Its purpose is to:

- enable **model-first aggregation** of multiple legacy collections
- preserve **full provenance** for every product
- avoid duplication, ambiguity, or silent data loss
- clearly separate **filesystem structure** from **Excel / data authority**

This document is a **governance contract**, not a tutorial.

---

## 2. Scope

This contract applies to:

- all non-NKD Ryderwear women’s products
- all filesystem folders under `Non-NKD`
- all future migrations from legacy collections (e.g. Activate, Sculpt, Contour)

This document does **not** define:

- Excel column structure
- SKU logic
- pricing, variants, or availability
- frontend navigation or merchandising

---

## 3. Core Design Principle

**Structure first. Provenance second. Never mixed.**

The Non-NKD folder tree is organized by **product reality**, not by marketing collections.

Legacy collections are treated as **origins**, not as structural authorities.

---

## 4. High-Level Model

Non-NKD
└───<Product_Ontology>
    └───__Origin
        └───<Collection_Name>
            └───(product_assets_or_colour_folders)

### Key Idea

- The **folder path above `__Origin`** describes *what the product is*
- The **folder path below `__Origin`** records *where it came from*

---

## 5. Structural Layer (Ontology)

Folders above `__Origin` represent **true product characteristics**, such as:

- Category: `Bottoms`, `Tops`, `Bodysuit`
- Product type: `Leggings`, `Shorts`, `Sports_Bra`
- Construction: `Seamless`, `Scrunch`, `V`, `Pocket`
- Fabric, rise, cut, or length where applicable

Example:

Non-NKD
└───Bottoms
    └───Leggings
        └───Construction
            └───Seamless
                └───Scrunch
                    └───__Origin
                        ├───Activate
                        │   └───(product_assets_or_colour_folders)
                        ├───Contour
                        │   └───(product_assets_or_colour_folders)
                        ├───Sculpt
                        │   └───(product_assets_or_colour_folders)
                        └───Stonewash
                            └───(product_assets_or_colour_folders)

This answers:

> *What kind of product is this?*

---

## 6. Provenance Layer (`__Origin`)

`__Origin` is a **reserved system folder** used exclusively to preserve provenance.

Rules:

- `__Origin` must **only** appear at the leaf level of a complete structural path
- `__Origin` must **never** appear above any structural folder
- No product assets live *outside* `__Origin`

Example:

Scrunch
└───__Origin
    ├───Activate
    ├───Contour
    ├───Sculpt
    └───Stonewash

This answers:

> *Which collections contributed products of this exact type?*

---

## 7. Collection Nodes (Origin Children)

Each legacy collection is represented **only** as a child of `__Origin`.

To repeat the same example as above, but this time in the abstract:

<Product Ontology Node>
└───__Origin
    ├───<Collection A>
    ├───<Collection B>
    ├───<Collection C>
    └───<Collection D>

Rules:

- Collection folders are namespaces, not categories. They **do not define structure**
- Multiple collections may coexist under the same structural node
- A collection may appear in multiple structural locations

---

## 8. Product Assets

All product assets (images, colour folders, etc.) live **inside** the collection folder.

Example:

Scrunch
└───__Origin
    ├───Sculpt
    │   ├───Black
    │   ├───Mocha
    │   └───Vanilla
    └───Contour
        ├───Marl
        └───Navy

This ensures:

- zero ambiguity
- zero duplication
- safe future extraction or re-segmentation

---

## 9. Provenance Guarantees

This model guarantees that:

- no product loses its collection identity
- aggregation is **fully reversible**
- legacy collections can be re-extracted without renaming or reclassification
- Excel remains the **editorial authority**, not the filesystem

---

## 10. Relationship to Excel

The filesystem:

- describes **physical organization**
- does **not** dictate item names
- does **not** define canonical product identity

Excel may:

- reference collection names independently
- ignore collections entirely
- apply different grouping or merchandising logic

Filesystem ≠ data model.

---

## 11. Migration Rules

When migrating a legacy product into `Non-NKD`:

1. Determine the correct **structural path**
2. Place the product under `__Origin/<CollectionName>`
3. Do **not** duplicate assets
4. Do **not** introduce collection folders above `__Origin`

---

## 12. Explicit Non-Goals

This structure is **not** intended to:

- mirror website navigation
- represent customer-facing collections
- enforce SKU or variant logic
- replace Excel governance

---

## 13. Status

This document is **authoritative** for all Non-NKD aggregation work.

Any deviation must be:
- explicitly documented
- reviewed against this contract
- justified on structural grounds, not convenience

---

## 14. Summary

The Non-NKD model achieves:

- model-first organization
- provenance without fragmentation
- scalability equal to NKD
- zero coupling to Excel

It is the **canonical aggregation pattern** for Ryderwear Women.




