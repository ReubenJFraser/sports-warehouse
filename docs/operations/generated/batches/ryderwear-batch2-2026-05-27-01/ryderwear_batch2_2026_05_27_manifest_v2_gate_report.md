# Ryderwear Batch 2 manifest v2 and flat mirror gate report

## 1. Executive summary

- v2 JSON is now the active canonical candidate manifest.
- v1 is superseded by v2 for identity-model reasons.
- v2 flat CSV is a review/tooling mirror only.
- The v2 JSON remains source truth.
- downstream artifacts remain blocked.

## 2. Current active artifacts

| Field | Value |
|---|---|
| active manifest path | `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2.json` |
| active manifest ID | `ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2` |
| flat mirror path | `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2_flat.csv` |
| v2 report path | `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2_report.md` |
| flat mirror report path | `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2_flat_report.md` |

## 3. Identity-model confirmation

- db_itemId maps to item_id.
- item_id is the normalized manifest field for database item identity.
- model_id is the canonical Sports Warehouse product/model slug.
- product_key is omitted/deprecated.
- external_item_id is omitted/deprecated.
- Downstream tools must not infer ProductDB identity from product_key or external_item_id.

## 4. Manifest contents

- product count: 2
- image row count: 14
- included item_id/model_id pairs:
  - 68 / ryderwear_unisex_gym_bag_accessories
  - 96 / ryderwear_female_nkd_shorts_v_scrunch
- source_root_id: src_ryderwear_repo_images_brands_ryderwear_v1
- root_scope: images/brands/ryderwear

## 5. Flat CSV mirror validation

- CSV is derived from v2 only.
- CSV has one row per ImageAsset.
- CSV has 14 data rows.
- CSV excludes product_key and external_item_id.
- delivery_count is 0 for every row.
- delivery_status is delivery_not_generated for every row.
- CSV must not be edited as source truth.

## 6. Excluded cases

These remain excluded:

- suspicious-01 / ryderwear_female_nkd_leggings_v_full_length_scrunch
- itemId 184
- dec-001 through dec-011
- all deferred cases
- all banner/non_product cases
- products outside the two accepted eligible decisions

## 7. Gate conclusion

manifest_v2_and_flat_mirror_review_gate_passed

This means:

- v2 manifest is ready to be used as the authoritative candidate manifest for later derived review/tooling tasks.
- v2 flat CSV is ready for review/tooling inspection.
- This gate does not approve copy simulation, image copying, SQL/import payloads, ProductDB updates, storefront gallery changes, or publication.
- copy simulation remains blocked pending a later gate.
- downstream artifacts remain blocked.

## 8. Next allowed task recommendation

Prepare a derived source-evidence view from the v2 manifest, if needed, or prepare an admin/backend read-only viewer for the v2 manifest and flat mirror.

copy simulation remains blocked pending a later gate and is not recommended as the next task.
