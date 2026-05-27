# 25 - Batch Media Ingestion Manifest Standard

## Purpose
This contract defines the Stage 1 reusable manifest schema family for batch media ingestion planning and review.

The goal is to freeze a shared standard for manifest headers, status vocabulary, identifier policy, and directory layout before any execution-oriented ingestion automation is introduced.

All manifests defined here are documentation and operational planning artifacts unless explicitly authorized for execution in a later approved stage.

## Batch folder convention
Each ingestion batch uses a `batch_id` and is rooted at:

`docs/operations/generated/batches/<batch_id>/`

Recommended `batch_id` naming convention:

`<brand>-<source_or_wave>-<yyyy-mm-dd>-<sequence>`

Example:

`ryderwear-batch2-2026-05-27-01`

Rules:
- lowercase alphanumeric plus dashes only;
- must be unique per ingestion run context;
- immutable once manifests are created for a review cycle.

## Manifest family
The manifest family is split into four classes:

1. **Source manifests** (input observation snapshots)
   - `batch_manifest.csv`
   - `product_identity_snapshot.csv`
   - `source_asset_inventory.csv`

2. **Generated reports** (systematically derived diagnostics)
   - `destination_collision_report.csv`
   - `suspicious_mapping_report.csv`
   - `publication_gate_report.csv`

3. **Human-review manifests** (explicit review and approval checkpoints)
   - `approval_checklist.csv`

4. **Update-plan manifests** (proposed actions, not execution)
   - `asset_mapping_plan.csv`
   - `copy_simulation.csv`
   - `image_field_update_plan.csv`
   - `hero_field_prep_plan.csv`

Planning/review boundary:
- Stage 1 manifests are non-executing artifacts.
- No importer logic is defined by this contract.
- No direct mutation of filesystem, ProductDB, or MySQL is authorized by the existence of these files alone.

## Identifier policy
Identity fields (`itemId`, `external_item_id`, `model_id`, `db_itemId`) follow existing identity contracts and must be preserved exactly as authoritative references.

Required rules:
- `itemId` and `external_item_id` are product-level identity anchors for mapping and review.
- `model_id` is the image-folder identity translation anchor when available.
- `db_itemId` is captured for traceability to catalog records.
- Manifests must not redefine identity semantics already established by identity governance contracts.
- Destination file paths are derived outputs from identity + role + mapping rules, not primary identity themselves.

## Status vocabulary
Allowed values are standardized as follows.

### batch status
`draft`, `inventory_complete`, `mapping_in_review`, `approved_for_simulation`, `simulation_complete`, `ready_for_update_plan`, `blocked`, `archived`

### identity_status
`matched`, `ambiguous`, `missing_external_id`, `missing_model_id`, `orphan_source_candidate`, `manual_review_required`

### mapping_status
`proposed`, `auto_mapped`, `manual_override`, `needs_review`, `approved`, `rejected`, `blocked_collision`

### approval_status
`pending`, `approved`, `approved_with_conditions`, `rejected`, `deferred`

### simulation_status
`pending`, `simulated_clean`, `simulated_with_collisions`, `simulated_with_missing_source`, `failed_validation`

### plan_status
`draft`, `ready_for_review`, `approved`, `blocked`, `superseded`

### catalogue_status
`ready`, `missing_required_fields`, `inactive`, `not_found`, `needs_review`

### media_status
`not_started`, `mapped`, `collision_detected`, `approved_for_copy`, `copy_simulated`, `blocked`

### hero_status
`not_planned`, `candidate_selected`, `needs_review`, `approved`, `rejected`, `blocked`

### review_status
`pending`, `in_review`, `changes_requested`, `approved`, `rejected`

### visibility_status
`hold`, `eligible_post_publish`, `blocked_by_policy`, `unknown`

## Folder path policy
`proposed_destination_path` and related destination path fields are derived planning outputs.

Rules:
- destination path strings are never the primary identity source;
- path derivation must trace back to manifest identity fields;
- any path collision is resolved through review manifests and approval workflow.

## ProductDB boundary
ProductDB remains the source context for product facts (identity, catalog attributes, status fields).

For Stage 1:
- ProductDB is referenced to populate snapshot manifests only;
- ingestion process state is tracked in manifests, not treated as ProductDB-owned truth;
- no ProductDB schema or data mutation is part of this contract.

## Database boundary
Database-backed ingestion provenance/audit tables are explicitly future optional work.

For Stage 1:
- no required MySQL table creation;
- no required SQL execution;
- manifest files are the only required persistence format for ingestion planning artifacts.

## Non-goals
This contract does **not**:
- implement importer/runtime execution logic;
- perform file copy/move/delete actions;
- activate products or set featured flags;
- modify admin, runtime, or frontend codepaths;
- modify ProductDB/MySQL schemas or data;
- replace existing identity governance contracts.

## Acceptance criteria
- Manifest family and roles are explicitly defined.
- Batch folder convention is fixed at `docs/operations/generated/batches/<batch_id>/`.
- Header templates are available under `docs/operations/templates/batch-media-ingestion/`.
- Standard status vocabulary is documented and reusable.
- Identifier policy and destination-path-derived rule are explicit.
- ProductDB and database boundaries are explicit for Stage 1.
