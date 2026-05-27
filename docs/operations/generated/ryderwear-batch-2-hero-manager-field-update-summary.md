# Ryderwear Batch 2 Hero Manager Field Update Summary (Review-Only)

- Total Batch 2 rows considered: **21**
- Rows ready_for_hero_field_update: **21**
- Rows excluded_missing_images: **0**
- Rows requiring review due to existing chosen_image/thumbnails_json: **0**

## Scope confirmation
- This is a planning artifact only; SQL has **not** been executed.
- Proposed SQL updates only `chosen_image` and `thumbnails_json`.
- Proposed SQL does **not** update `images`, `hero_image`, `is_active`, or `featured`.

## Proposed derivation rules
- `proposed_chosen_image` = first semicolon-delimited token from `images`.
- `proposed_thumbnails_json` = full semicolon-delimited `images` value.
- `hero_image` is intentionally left unchanged for later Hero Manager recalculation/manual selection.

## Recommended next step after SQL execution
1. Run Hero Manager recalculation and/or manual hero selection to populate `hero_image` and related hero fields.
2. In a separate review task, decide activation readiness (`is_active`) and merchandising decisions (`featured`).
