# Ryderwear Source-Folder-to-Product Mapping System

## Purpose
This document defines the corrected, authoritative mapping method for linking Ryderwear ProductDB rows to Ryderwear source image folders.

## Authority and Core Rules

1. The desktop/source folder hierarchy (captured in `docs/operations/generated/source-ryderwear-semantic-image-folders.csv`) is the authoritative source for Ryderwear image sets.
2. Terminal folders containing image files are the source image sets.
3. The colour/variant folder is terminal (or near-terminal) immediately before image files.
4. Product-family identity is derived from the path above the colour folder.
5. ProductDB `model_id` is the product-row identity key for mapping.
6. ProductDB rows should map to actual source image-set folders, not guessed candidate folders.
7. Numbered filenames (for example `01.webp`, `02.webp`) are valid only within their source-folder context.
8. Old candidate/adjudication worksheets are diagnostic history only; if they conflict with source inventory, the source-folder inventory wins.

## Corrected Workflow

The corrected workflow is:

**source folder inventory → source-folder family mapping → reviewer approval → copy/import execution plan → ProductDB/MySQL reconciliation**

This explicitly supersedes the previous candidate-path-first method as the primary approach.

## Inputs Used

- `docs/operations/generated/source-ryderwear-semantic-image-folders.csv`
- `docs/data/SportWarehouse_ProductDB.csv`
- `README/II-CONTRACTS/24-Ryderwear_Image_Folder_Convention_Current.md`
- `docs/operations/generated/ryderwear-contract-24-manual-decision-worksheet.csv`
- `docs/operations/generated/ryderwear-contract-24-first-pass-desk-review.csv`
- `docs/operations/generated/ryderwear-contract-24-planning-queues-summary.md`

## Mapping Logic (Conservative)

1. Read all Ryderwear rows from ProductDB.
2. Read all terminal source image folders from source inventory CSV.
3. Infer source-folder families by treating `likely_colour_folder` as the colour/variant leaf and using the parent path as family.
4. Score candidate families against each ProductDB row with terms from:
   - `model_id`
   - `itemName`
   - `collection`
   - `subCategory`
   - `semantic_display_path`
   - `semantic_axis_values`
   - `product_signal_terms`
5. Prefer alignment to real source folders and source families over historical candidate-path suggestions.
6. Auto-suggest only when semantic alignment is clear; otherwise keep manual.
7. Mark ambiguous/weak mappings as `needs_manual_mapping`.
8. Do not auto-approve any mapping; reviewer fields remain blank.

## Mapping Status Vocabulary

- `likely_source_family_match`
- `multiple_possible_source_families`
- `needs_manual_mapping`
- `no_source_family_found`
- `already_mapped_or_grandfathered`
- `out_of_scope_for_batch_2`

## Generated Worksheet Contract

Generated file:
`docs/operations/generated/ryderwear-source-folder-to-product-mapping-worksheet.csv`

Columns:
- `model_id`
- `itemName`
- `collection`
- `subCategory`
- `queue_status`
- `source_folder_family_candidate`
- `source_colour_folders_available`
- `recommended_display_colour`
- `source_terminal_folder`
- `source_image_files`
- `proposed_project_image_path`
- `mapping_status`
- `mapping_confidence`
- `reviewer_decision`
- `reviewer_notes`

Reviewer control rules:
- `reviewer_decision` and `reviewer_notes` are intentionally blank on generation.
- No approved source field or execution-final field is populated in this worksheet.

## Notes on Historical Artifacts

- `ryderwear-contract-24-manual-decision-worksheet.csv`
- `ryderwear-contract-24-first-pass-desk-review.csv`
- Queue outputs referenced in planning summaries

These remain useful as historical diagnostics and triage context, but are superseded as the primary authority by `source-ryderwear-semantic-image-folders.csv`.
