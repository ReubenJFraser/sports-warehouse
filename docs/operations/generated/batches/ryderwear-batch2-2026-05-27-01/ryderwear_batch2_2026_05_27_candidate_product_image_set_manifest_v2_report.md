# Ryderwear Batch 2 candidate product-image-set manifest v2 report

## Manifest generated

- Manifest: `ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2.json`
- Manifest ID: `ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2`
- Batch ID: `ryderwear-batch2-2026-05-27-01`
- Generated at: `2026-05-29T05:48:39Z`
- Product count: 2
- Image row count: 14
- Downstream artifacts remain blocked: yes

## Supersession and identity-model correction

- v1 is superseded by v2 for identity-model reasons.
- v1 duplicated identity values across `product_key`, `external_item_id`, and `model_id`.
- v2 uses the normalized Sports Warehouse identity model.
- Excel/ProductDB `db_itemId` maps to manifest `item_id`.
- `item_id` is the manifest-normalized field name for the same database item identity, not a new identity.
- `model_id` is the canonical Sports Warehouse product/model slug.
- `product_key` is omitted because it duplicated `model_id`.
- `external_item_id` is omitted because in this project it duplicated `model_id`.
- If a true outside-system identifier is needed later, a new clearly named field such as `supplier_item_id`, `supplier_sku`, `platform_item_id`, or `source_system_item_id` should be introduced instead of reusing `external_item_id`.
- Downstream tools must not infer ProductDB identity from `product_key` or `external_item_id`.

## Source files examined

- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/source_root_and_policy_completion_worksheet.md`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/review_decision_gate_report.md`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/human_reviewer_acceptance_record.json`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v1.json`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v1_report.md`
- `docs/architecture/product-image-set-manifest-architecture.md`
- `docs/architecture/product-image-set-manifest-schema.md`
- `docs/architecture/examples/product-image-set-manifest.example.json`
- `docs/architecture/examples/product-image-set-manifest-flat.example.csv`
- `docs/data/SportWarehouse_ProductDB.csv`

## Approved SourceRoot used

- SourceRoot ID: `src_ryderwear_repo_images_brands_ryderwear_v1`
- Root type: `repository_path`
- Root scope: `images/brands/ryderwear`
- Source-root scope is unchanged from v1 and remains limited to accepted eligible Ryderwear Batch 2 decisions.

## item_id lookup results

ProductDB lookup was performed against `docs/data/SportWarehouse_ProductDB.csv` using exact `model_id` matches.

| model_id | ProductDB match count | db_itemId | v2 item_id | identity_resolution_status |
| --- | ---: | ---: | ---: | --- |
| `ryderwear_unisex_gym_bag_accessories` | 1 | 68 | 68 | unique_reliable_model_id_match |
| `ryderwear_female_nkd_shorts_v_scrunch` | 1 | 96 | 96 | unique_reliable_model_id_match |

No ProductDB or database records were modified.

## Included products

- `ryderwear_unisex_gym_bag_accessories`: item_id `68`; 8 image rows from `women/accessories/pilates-gym-bag/vanilla/`.
- `ryderwear_female_nkd_shorts_v_scrunch`: item_id `96`; 6 image rows from `plus-size/women/nkd/high-waisted-scrunch-shorts/mocha/`.

## Excluded cases

- `suspicious-01` / `ryderwear_female_nkd_leggings_v_full_length_scrunch` remains excluded as a banner/non_product case.
- itemId `184` remains excluded as a deferred provenance case.
- `dec-001` through `dec-011` remain excluded as deferred decisions.
- All deferred cases remain excluded.
- All banner/non_product cases remain excluded.
- Products outside the two accepted eligible decisions remain excluded.

## Image inclusion notes

- Image rows, checksums, dimensions, byte sizes, source paths, roles, sequence, image-level approval_status, review_decision_code, provenance notes, and empty delivery arrays are preserved from v1.
- No expected images from the two accepted eligible image plan rows were omitted.
- Image sequence follows the `image_field_update_plan.csv` planned image order for each included product.
- The first planned image for each product is assigned `primary`; additional planned images are assigned `gallery`.
- Delivery arrays are intentionally empty. No destination paths, URLs, copied files, import payloads, storefront gallery entries, or publication artifacts were generated.

## Downstream block

Downstream artifacts remain blocked. This v2 correction does not authorize or generate a CSV mirror, source evidence view, copy simulation, SQL/import payload, ProductDB update, storefront gallery update, image copy, image modification, or publication.
