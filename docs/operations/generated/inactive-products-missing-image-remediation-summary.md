# Inactive Products Missing-Image Remediation Summary

## Scope
This report covers the 19 inactive products that currently have no `images`, `chosen_image`, `thumbnails_json`, or `hero_image` fields in Hero Manager-facing data.

## Headline counts
- Total inactive missing-image rows: **19**
- By brand:
  - **Adidas: 4**
  - **Ryderwear: 15**
- By recommended action (from `inactive-products-missing-image-remediation-plan.csv`):
  - `review_duplicate_collision`: **12**
  - `review_suspicious_mapping_exclusion`: **1**
  - `recover_from_source_folder`: **2**
  - `needs_new_image_source`: **4**

## Answers to required questions

1. **Are these rows absent from the Ryderwear Batch 2 ready set, duplicate-collision excluded, suspicious-mapping excluded, or never mapped?**
   - **Adidas 4/19**: outside Ryderwear Batch 2 workflow (never mapped in Ryderwear artifacts).
   - **Ryderwear 12/15**: present in copy planning but **excluded due to duplicate destination collision**.
   - **Ryderwear 3/15**: reached copy/update planning and then **flagged by suspicious mapping audit**:
     - `ryderwear_female_nkd_leggings_v_full_length_scrunch` (banner/campaign asset risk)
     - `ryderwear_female_nkd_shorts_v_scrunch` (needs source-folder remap)
     - `ryderwear_female_nkd_tank_top_square_neck` (needs source-folder remap)

2. **Which rows have existing project image files under `images/brands` that could be mapped?**
   - Potentially mappable after adjudication/remap:
     - All 12 duplicate-collision Ryderwear rows (candidate project destinations already identified in prior copy plan, but blocked).
     - 3 suspicious rows also have candidate project paths from prior plan, but require audit-safe remap/review before use.
   - Adidas rows have **no confirmed mapped project path** in current Ryderwear planning artifacts.

3. **Which rows likely need recovery from original desktop/source folders?**
   - `ryderwear_female_nkd_shorts_v_scrunch`
   - `ryderwear_female_nkd_tank_top_square_neck`
   These were explicitly tagged as needing source-folder remap in suspicious mapping outputs.

4. **Which are Adidas and outside Ryderwear Batch 2 copy-plan workflow?**
   - `adidas_female_hyperglam_leggings_3-stripes`
   - `adidas_female_hyperglam_crop_top_3-stripes`
   - `adidas_male_hoodie_poly linear`
   - `adidas_male_track_pants_poly linear`

5. **Which Ryderwear rows were excluded from prior copy/image plan, and why?**
   - 12 Ryderwear rows were excluded by `duplicate-destination-collision` in `ryderwear-source-image-copy-exceptions.csv`.
   - 3 Ryderwear rows were excluded/held by suspicious mapping controls to prevent unsafe mapping:
     - 1 banner/campaign-asset exclusion
     - 2 source-family remap requirements

6. **Safest next remediation sequence**
   1. **Adidas lane (separate workflow):** run Adidas-only source discovery and mapping plan (no Ryderwear artifact reuse assumptions).
   2. **Ryderwear duplicate-collision lane:** resolve collisions model-by-model, assign canonical destination ownership, and regenerate copy plan exceptions.
   3. **Ryderwear suspicious lane:** remap shorts/tank to validated product folders; replace banner/campaign asset candidate for leggings with true product image set.
   4. **Post-remap validation:** regenerate image update and hero-field plans; verify all 19 rows have non-empty image fields in planning artifacts before any DB execution step.

## Adidas rows requiring separate workflow
- itemId 134, 135, 136, 137 (all `needs_new_image_source` under this remediation plan).

## Ryderwear rows requiring mapping/recovery/review
- `review_duplicate_collision`: itemId 138, 153, 154, 157, 158, 160, 162, 163, 166, 174, 176, 184.
- `review_suspicious_mapping_exclusion`: itemId 168.
- `recover_from_source_folder`: itemId 171, 172.

## Change-safety confirmation
- No MySQL changes were made.
- No ProductDB data file rows were edited.
- No Hero Manager code was modified.
- No image files were copied, moved, renamed, or deleted.
- Only two generated reporting artifacts were created:
  - `docs/operations/generated/inactive-products-missing-image-remediation-plan.csv`
  - `docs/operations/generated/inactive-products-missing-image-remediation-summary.md`

## Recommended next task
Create an **Adidas image-source discovery worksheet** plus a **Ryderwear duplicate-collision adjudication worksheet** so both lanes can progress in parallel, then regenerate a unified inactive-image update-ready report for re-audit.
