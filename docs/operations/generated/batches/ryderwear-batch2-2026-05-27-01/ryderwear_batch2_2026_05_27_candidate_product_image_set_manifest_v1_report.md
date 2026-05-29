# Ryderwear Batch 2 candidate product-image-set manifest report

## Manifest generated

- Manifest: `ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v1.json`
- Manifest ID: `ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v1`
- Batch ID: `ryderwear-batch2-2026-05-27-01`
- Generated at: `2026-05-29T02:58:40Z`
- Product count: 2
- Image row count: 14
- Downstream artifacts remain blocked: yes

## Source files examined

- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/source_root_and_policy_completion_worksheet.md`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/review_decision_gate_report.md`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/human_reviewer_acceptance_record.json`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/image_field_update_plan.csv`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/destination_collision_report.csv`
- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/publication_gate_report.csv`
- `docs/architecture/product-image-set-manifest-architecture.md`
- `docs/architecture/product-image-set-manifest-schema.md`
- `docs/architecture/examples/product-image-set-manifest.example.json`
- `docs/architecture/examples/product-image-set-manifest-flat.example.csv`

## Approved SourceRoot used

- SourceRoot ID: `src_ryderwear_repo_images_brands_ryderwear_v1`
- Root type: `repository_path`
- Root scope: `images/brands/ryderwear`
- Source-root access was limited to the accepted product paths needed for candidate rows.

## Included products

- `ryderwear_unisex_gym_bag_accessories`: 8 image rows from `women/accessories/pilates-gym-bag/vanilla/`.
- `ryderwear_female_nkd_shorts_v_scrunch`: 6 image rows from `plus-size/women/nkd/high-waisted-scrunch-shorts/mocha/`.

## Excluded cases

- `suspicious-01` / `ryderwear_female_nkd_leggings_v_full_length_scrunch` remains excluded as a banner/non_product case.
- itemId `184` remains excluded as a deferred provenance case.
- `dec-001` through `dec-011` remain excluded as deferred decisions.
- All other deferred cases, banner/non_product cases, unresolved source/provenance cases, and products outside the two accepted/eligible decisions remain excluded.

## Image inclusion notes

- No expected images from the two accepted/eligible image plan rows were omitted.
- Image sequence follows the `image_field_update_plan.csv` planned image order for each included product.
- The first planned image for each product is assigned `primary`; additional planned images are assigned `gallery`.
- Delivery arrays are intentionally empty. No destination paths, URLs, copied files, import payloads, storefront gallery entries, or publication artifacts were generated.
