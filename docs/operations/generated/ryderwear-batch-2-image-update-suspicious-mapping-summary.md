# Ryderwear Batch 2 Suspicious Mapping Audit Summary

- Original update-plan rows: **25**
- Rows kept for SQL: **21**
- Rows excluded as suspicious/non-runnable: **4**

## Suspicious counts by flag
- `banner_filename`: 1
- `banner_path`: 1
- `collection_page_asset`: 1
- `desktop_mobile_campaign_asset`: 1
- `gym_bag_excluded`: 1
- `plus_size_non_plus_model`: 1
- `semantic_marketing_asset`: 1
- `tank_top_path_mismatch`: 1

## Examples of excluded rows
- `ryderwear_female_nkd_leggings_v_full_length_scrunch` → `banner_path|banner_filename|collection_page_asset|desktop_mobile_campaign_asset|semantic_marketing_asset`
- `ryderwear_female_nkd_shorts_v_scrunch` → `plus_size_non_plus_model`
- `ryderwear_female_nkd_tank_top_square_neck` → `tank_top_path_mismatch`
- `ryderwear_unisex_gym_bag_accessories` → `gym_bag_excluded`

## Execution confirmation
- SQL generation/audit only.
- No SQL execution performed.
- No MySQL/ProductDB/runtime/frontend/import/image file modifications performed.

## Recommended next step
- Manually review excluded mappings in source folder worksheet, remap suspicious rows, regenerate plan, then re-run SQL generation.
