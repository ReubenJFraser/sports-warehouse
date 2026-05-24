# Model_ID Image Filesystem Identity Contract

## Purpose

This contract clarifies how `model_id` functions as the identity bridge between:

- Excel / `SportWarehouse_ProductDB.csv`
- DBeaver/MySQL product rows
- image folder/file identity
- future generated runtime image paths
- future AI-assisted product similarity/recommendation work

This document is a **clarification bridge** for contracts 13–21 and does **not** replace them.

---

## Relationship to Existing Contracts (13–21)

Contracts 13–21 define folder governance, harmonization, model-vs-variant separation, ProductVariants schema, model_id generation governance, sports-bra identity specialization, and collections metadata.

This contract adds one explicit cross-system statement:

- `model_id` is the strongest canonical product identity signal for deterministic product ↔ image-folder matching.

If any assumption in this document appears to diverge from Contract 19 (`19-Model_ID_Generation_&_Identity_Governance_Contract.md`), the discrepancy must be documented and reconciled; implementers must not silently create a new rule.

---

## Canonical Identity Role of `model_id`

1. `model_id` is the canonical product identity key for image-folder matching and future generated runtime image paths.
2. `model_id` is generated from structured product fields (not manually invented naming).
3. Because `model_id` is deterministic and field-derived, it reduces duplicate product/image identity risk across systems.

Operationally:

- Excel-origin structured attributes generate `model_id`.
- ProductDB rows carry that `model_id` as model identity.
- Image mapping scripts should treat `model_id` as the strongest identity signal.
- Runtime image paths can later be generated deterministically from `model_id`-anchored identity mapping.

---

## Authoritative Generation Method (No New Formula)

This contract does **not** define a new `model_id` formula.

Authoritative formula source:

- Contract 19, Section 2 (`Authoritative Formula (Structured References Required)`).

Current observed Excel formula (captured in full for implementation clarity; this is observational only, and Contract 19 remains the authoritative governance source):

```excel
=LOWER(
TEXTJOIN("_",TRUE,
[@brand],
[@gender],
IF(OR([@collection]="null",[@collection]="NULL"),"",[@collection]),
[@subCategory],
IF(OR([@[model_family]]="null",[@[model_family]]="NULL"),"",[@[model_family]]),
IF(OR([@fabric]="NULL",[@fabric]="null"),"",[@fabric]),
IF(OR([@construction]="NULL",[@construction]="null"),"",[@construction]),
IF(OR([@rise]="NULL",[@rise]="null"),"",[@rise]),
IF(OR([@length]="NULL",[@length]="null"),"",SUBSTITUTE([@length],"-","_")),
IF(OR([@neckline]="NULL",[@neckline]="null"),"",[@neckline]),
IF(OR([@[strap_configuration]]="NULL",[@[strap_configuration]]="null"),"",[@[strap_configuration]]),
IF(OR([@variant]="NULL",[@variant]="null"),"",[@variant]),
IF([@subCategory]="Sports_Bra",
   IF(OR([@[support_level]]="NULL",[@[support_level]]="null"),"",[@[support_level]]),
   ""
),
IF([@scrunchFlag]="Yes","scrunch",""),
IF([@invisibleFlag]="Yes","invisible","")
))
```

Formula/discrepancy clarification:

- The formula above is the full currently observed Excel operational formula (from `LOWER(TEXTJOIN(...))` through all included fields and conditionals), not a Sports_Bra-only clause.
- Contract 19 is still authoritative for governance and expected method.
- Any ordering discrepancy (for example, `variant` vs `support_level` position) must be evaluated against the full observed formula and Contract 19 together.
- If Contract 19 and observed Excel behavior differ, record this as a governance reconciliation question; do not silently resolve by introducing a new undocumented ordering.

---

## Filesystem Identity and Numeric Filenames

Numeric image filenames such as `01.png`, `02.png`, `03.png` are acceptable.

Reason:

- Product identity is supplied by folder/path-level mapping (and potentially future model_id-based runtime folder structure), not by semantic filename text.

Therefore, filename simplicity is compatible with strong identity, provided folder/path mapping remains deterministic and model_id-anchored.

---

## Source/Archive Hierarchy vs Runtime Identity

Both layers are valuable and serve different purposes.

### Rich source/archive hierarchy

- Preserves semantic organization, operational history, and collection/category browsing context.
- Supports human curation and historical folder-system continuity (Contracts 13–16).

### Model_id-based runtime identity

- Optimized for deterministic website path generation and database mapping.
- Better suited to machine-safe, reproducible product ↔ image resolution.

This contract preserves the value of the richer source/archive hierarchy while clarifying that canonical runtime identity should be model_id-centered.

---

## Model-Level vs Variant-Level Boundary

Current state:

- ProductDB rows are model-level identity rows.
- Colour/variant image folders can be variant-level assets.

Implication:

- Variant-level folder/image organization may eventually be formalized in ProductVariants-style structures (Contracts 17–18) while remaining linked to model-level `model_id`.

---

## AI Similarity / Recommendation Positioning

`model_id` can support future AI similarity/recommendation work because it encodes normalized product attributes in a structured token sequence.

However, `model_id` is only one signal. Future AI/recommendation logic may also incorporate:

- taxonomy fields
- product descriptions
- visual metadata
- colour/variant data
- price
- activity/use tags
- user behavior and engagement data
- other structured or learned signals

---

## Governance Rules

1. `model_id` must be unique across ProductDB rows.
2. Duplicate `model_id` values must trigger structured source-data review.
3. Duplicates must not be resolved by arbitrary suffixing unless explicitly approved by governance.
4. Preferred remediation is improving structured source fields (example pattern: distinguishing flared leggings by adding a discriminating construction value such as `Flared`).
5. Any structured-field change that alters `model_id` is an identity-impacting change and must be treated as such in review/audit workflows.
6. Scripts that copy/map image folders must treat `model_id` as the strongest product identity signal.
7. Before generating image paths or database updates, scripts should verify `model_id` uniqueness.

---

## Non-Replacement Statement

This contract is a bridge clarification for identity alignment across data and filesystem systems.

It does **not** replace Contracts 13–21; it should be interpreted together with them, with Contract 19 remaining authoritative for model_id-generation governance.
