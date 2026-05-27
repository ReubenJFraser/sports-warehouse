# Ryderwear Batch 2 MySQL Image Update Summary (Audited Regeneration)

- Original update-plan rows: **25**
- Rows kept for SQL UPDATE: **21**
- Rows excluded for suspicious mapping review: **4**
- Field updated by SQL: `images` only (matched by `model_id`).
- Gym bag row excluded from SQL.
- Duplicate-collision rows included: 0 (none present in plan).
- SQL was generated only and not executed.

- **Pending regeneration note (2026-05-26):** Local MySQL uses `item.external_item_id` as the physical column carrying ProductDB `model_id` identity values. The Batch 2 UPDATE SQL must be regenerated to target `external_item_id`-based matching before execution.

- **Audit dependency note (2026-05-27):** Historical-origin analysis is limited by currently available repository history and remains unresolved; this does not change the operational requirement to match on `item.external_item_id` for local MySQL compatibility. This task does not regenerate SQL.
