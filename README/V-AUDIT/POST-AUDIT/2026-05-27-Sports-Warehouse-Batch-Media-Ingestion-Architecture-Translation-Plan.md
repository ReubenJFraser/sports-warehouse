# 2026-05-27 Sports Warehouse Batch Media Ingestion Architecture Translation Plan

## Purpose
This document translates the Deep Research report (**Building a Reusable Product-Media Ingestion Pipeline for a Custom PHP/MySQL Catalogue**) into a Sports Warehouse-specific architecture plan that can guide implementation sequencing without performing any data or runtime mutations.

## Core architectural decision
Sports Warehouse should adopt the following architecture stance:

- **Keep** the existing `images/brands/...` brand/category folder layouts as delivery/storage structure.
- **Stop** treating folder structure itself as the primary identity truth.
- **Introduce explicit manifests and generated reports** as the stable, reviewable source of product-media mapping truth.
- **Treat destination paths as derived outputs** rendered from metadata + governed translation rules + human review decisions.

In short: folders remain, but they become a governed projection layer rather than the authoritative identity layer.

## Current-state mapping
Deep Research concept -> current Sports Warehouse equivalent and maturity classification.

| Deep Research concept | Sports Warehouse equivalent today | Maturity now | Near-term recommendation | Longer-term recommendation |
|---|---|---|---|---|
| `product_catalogue.csv` | `docs/data/SportWarehouse_ProductDB.csv` plus MySQL `item` row set used by admin/runtime | **Existing equivalent** | Keep as core product source context; export read-only snapshots per batch | Later support DB-backed snapshot/version index |
| `source_asset_inventory.csv` | Ryderwear source/folder audit + mapping worksheets (e.g., source folder mapping, source image copy plan artifacts) | **Partial equivalent** | Standardize single per-batch inventory CSV generated from source scan + ownership signals | Later map to `source_asset` table |
| `asset_mapping_plan.csv` | Ryderwear mapping/adjudication worksheets and batch image update planning CSVs | **Partial equivalent** | Normalize to one canonical mapping-plan manifest with status fields | Later map to `asset_mapping` table |
| `collision_review.csv` | Duplicate collision adjudication report + split-path proposal + approval checklist artifacts | **Existing equivalent (distributed)** | Consolidate into a canonical collision-review manifest/report pair | Later map decisions to `review_decision` table |
| `image_field_update_plan.csv` | `ryderwear-batch-2-mysql-image-update-summary.md` + associated update plan artifacts | **Existing equivalent (report-first)** | Keep generated CSV/MD plan artifacts; no direct execution coupling | Later map to audited update-run records |
| `hero_prep_plan.csv` | `ryderwear-batch-2-hero-manager-field-update-plan.csv` + hero field update summary | **Existing equivalent** | Keep per-batch generated hero prep manifest | Later map to admin readiness job records |
| `publication_gate.csv` | Inactive Product Review readiness-filter planning + missing-image remediation summaries | **Partial equivalent** | Standardize as explicit gate report/CSV per batch | Later map to `publication_gate` table |
| `review_decision` (entity) | Human approval checklist rows, adjudication outcomes, defer statuses in generated docs/CSVs | **Partial equivalent** | Keep as CSV/documentation-first decision ledger | Later migrate to DB table after formats stabilize |

Decision policy for all rows above:
- **CSV/documentation first now** (lower schema churn, faster iteration).
- **Database later** only after two or more brands run through the same manifest schema with stable fields.

## Recommended manifest family
Minimum manifest family for future batches.

| Filename | Purpose | Likely location | Key columns (minimum) | Authoring mode | Later MySQL/admin mapping |
|---|---|---|---|---|---|
| `batch_manifest.csv` | Batch-level metadata, scope, and processing state | `docs/operations/generated/batches/<batch_id>/` | `batch_id`, `brand`, `source_root`, `catalog_snapshot_date`, `status`, `owner`, `notes` | Both | Yes (`batch`) |
| `product_identity_snapshot.csv` | Frozen product identity view used by the batch | `docs/operations/generated/batches/<batch_id>/` | `itemId`, `external_item_id`, `model_id`, `brand`, `subcategory`, `variant`, `is_active` | Generated | Maybe (snapshot index) |
| `source_asset_inventory.csv` | Enumerated source assets and provenance evidence | same batch folder | `asset_id`, `source_path`, `filename`, `checksum_optional`, `detected_brand`, `detected_color`, `source_confidence` | Generated (+ reviewer notes) | Yes (`source_asset`) |
| `asset_mapping_plan.csv` | Proposed product-to-asset mapping before any copy/update | same batch folder | `batch_id`, `itemId`, `model_id`, `source_asset_id`, `proposed_destination_path`, `mapping_confidence`, `mapping_status` | Both | Yes (`asset_mapping`) |
| `destination_collision_report.csv` | Collision groups for duplicate destination ownership | same batch folder | `collision_group_id`, `destination_path`, `candidate_itemId`, `candidate_model_id`, `severity`, `status` | Generated | Yes (`review_decision` link) |
| `suspicious_mapping_report.csv` | Heuristic anomalies requiring manual validation | same batch folder | `itemId`, `model_id`, `reason_code`, `evidence`, `recommended_action`, `status` | Generated (+ reviewer outcome) | Maybe |
| `approval_checklist.csv` | Human approval ledger for collisions/suspicious rows | same batch folder | `decision_id`, `entity_type`, `entity_key`, `proposed_action`, `reviewer`, `decision`, `decision_date`, `rationale` | Both | Yes (`review_decision`) |
| `copy_simulation.csv` | Non-destructive simulated destination operations | same batch folder | `sim_id`, `itemId`, `source_path`, `destination_path`, `op_type`, `collision_after_apply`, `sim_status` | Generated | Maybe (run logs) |
| `image_field_update_plan.csv` | Planned `images`/image-field payload changes by item | same batch folder | `itemId`, `external_item_id`, `planned_images`, `source_manifest_refs`, `approval_state` | Generated (+ review flags) | Later optional |
| `hero_field_prep_plan.csv` | Planned `chosen_image`/`thumbnails_json` prep derived from image plan | same batch folder | `itemId`, `chosen_image_candidate`, `thumbnails_json_candidate`, `derivation_rule_version`, `approval_state` | Generated (+ review flags) | Later optional |
| `publication_gate_report.csv` | Readiness gates from image-ready to publication-candidate | same batch folder | `itemId`, `image_ready`, `hero_ready`, `frontend_required_fields_ready`, `inactive_state`, `gate_status`, `blockers` | Generated | Yes (`publication_gate`) |

## Product identity recommendation
Recommended identifier roles in Sports Warehouse:

- **Current operational identifiers (authoritative now):**
  - `itemId`: primary local row key for admin/runtime operations.
  - `external_item_id`: cross-system ingestion key for update plan alignment.
  - `model_id`: canonical structural product identity and strongest mapping anchor.
- **Bridge identifier:**
  - `db_itemId`: operational reconciliation field for CSV<->DB workflows; keep as compatibility bridge while ingestion tooling is mixed.
- **Future-normalization concepts (do not force immediate migration):**
  - `product_id`: normalized model-level entity key.
  - `variant_id`: normalized purchasable/style-color variant entity key.
  - `product_group_id`: higher-level grouping for family/collection ownership and shared-asset governance.

Guidance:
- Run batches with `itemId` + `external_item_id` + `model_id` today.
- Introduce `product_id` / `variant_id` / `product_group_id` only when multi-brand manifests prove stable and a normalized schema is justified.

## Folder path recommendation
Preserve current tree; harden governance:

1. **Path is derived, not primary identity**
   - Destination path must be rendered from approved metadata and review decisions.
2. **Brand-specific render rules**
   - Keep Ryderwear NKD/non-NKD and cut/fabric conventions as brand-specific translation rule modules.
3. **Path-driving metadata fields**
   - `brand`, `gender`, `category/subCategory`, `collection`, `model_family`, `construction`, `support_level`/cut traits, color token, plus approved split-path override.
4. **Collision prevention**
   - Require deterministic destination pre-check + collision report before copy simulation approval.
5. **When to split folders**
   - Split when two distinct products/variants compete for same destination ownership and evidence supports differentiated identity.
6. **When shared folders are allowed**
   - Allow only with explicit approved shared-ownership rule and manifest traceability (default should be exclusive ownership).
7. **Historical paths**
   - Store prior destination references in manifest history columns (`prior_destination_path`, `superseded_by_decision_id`) instead of implicit memory.

## Ryderwear-specific vs reusable layers

### Ryderwear-specific layers (remain brand rules)
- NKD/non-NKD branch semantics.
- Ryderwear sports-bra subtype and collection naming conventions.
- Ryderwear-specific split-path heuristics discovered in Batch 2 collision groups.

### Reusable multi-brand ingestion layers
- Batch manifest intake.
- Product identity snapshot generation.
- Source inventory scan.
- Mapping plan generation.
- Collision/suspicious detection.
- Human approval checklist gating.
- Copy simulation generation.
- Image-field and hero-prep plan generation.
- Publication gate reporting.

This reusable layer should be identical for Adidas, Nike, Stax, and future Ryderwear batches; only translation-rule modules should vary by brand.

## Admin/backend implications
Planning implications (no implementation in this document):

- **Inactive Product Review** becomes the operational readiness review workspace fed by publication-gate artifacts.
- **Hero Manager** should consume hero prep outputs as staged inputs, separating image ingestion readiness from hero selection workflow.
- **Readiness filters** should align to manifest-generated readiness states (missing_images, hero_review_ready, publication_ready_but_inactive, etc.).
- **Publication gating** should be explicit and auditable per item, not inferred from folder presence.
- **Future admin pages/reports** should prioritize read-only dashboards for manifest state, decision backlog, and blocker counts before any mutation controls are introduced.

## Tooling roadmap

### Stage 1: documentation and manifest standardization
Build:
- Manifest schema contract docs (column definitions + allowed statuses).
- Batch folder conventions under `docs/operations/generated/batches/<batch_id>/`.
- Template CSV headers for all core manifests.

### Stage 2: reusable report generators
Build under `tools/reports/`:
- `generate_product_identity_snapshot.php`
- `generate_source_asset_inventory.php`
- `generate_destination_collision_report.php`
- `generate_suspicious_mapping_report.php`
- `generate_publication_gate_report.php`

### Stage 3: copy simulation and update-plan generators
Build under `tools/importers/` or `tools/reports/`:
- `generate_asset_mapping_plan.php`
- `generate_copy_simulation.php`
- `generate_image_field_update_plan.php`
- `generate_hero_field_prep_plan.php`

### Stage 4: admin readiness dashboard/readiness filters
Plan-only deliverables:
- Read-only readiness summary page.
- Filter links for readiness classes in Inactive Product Review.
- Manifest ingestion status panel (batch + unresolved decisions + blocked rows).

### Stage 5: optional database-backed provenance/review tables
Only after manifest stability proven:
- Introduce optional tables: `batch`, `source_asset`, `asset_mapping`, `review_decision`, `publication_gate`.
- Build synchronization importers from CSV manifests to DB-backed audit tables.

## Database roadmap
Conservative recommendation to avoid premature schema churn:

- **Remain CSV/generated artifacts now:**
  - `source_asset_inventory`, `asset_mapping_plan`, `destination_collision_report`, `approval_checklist`, `copy_simulation`, `publication_gate_report`.
- **Potential later MySQL tables:**
  - `batch`, `source_asset`, `asset_mapping`, `review_decision`, `publication_gate`.
- **Migration trigger criteria:**
  1. At least 2-3 brands complete full cycle on same manifest schema.
  2. Decision/status vocabularies stop changing frequently.
  3. Clear admin reporting need exceeds CSV workflow capability.

Until then, generated artifact discipline provides traceability without risky schema iteration.

## Excel/ProductDB roadmap
ProductDB should remain focused and not become a catch-all workflow database.

- **Keep in ProductDB:** core product attributes, identity-supporting fields, editorial/product content fields.
- **Move/keep in generated manifests:** ingestion provenance, source inventory, collision decisions, copy simulation logs, per-batch approval states.
- **Complete in admin/backend workflows (not Excel):** hero selection lifecycle, publication gating decisions, readiness-review operational statuses.
- **Anti-overload rule:** ProductDB should describe product facts; batch manifests should describe ingestion process state.

## Immediate next-step recommendation
Preferred sequence: **do both in sequence**.

1. **First:** freeze and adopt the initial reusable manifest standard (headers, status vocabulary, directory layout) so current work lands in stable architecture.
2. **Second:** continue Ryderwear Batch 2 copy simulation using that standard, then regenerate existing Ryderwear reports in normalized format.

Reason: immediate continuation without standardization risks another one-off artifact set; pausing indefinitely risks delivery stall. A short standardization-first step followed by resumed Ryderwear simulation is the best balance.

## Non-goals
This plan does **not**:
- update MySQL;
- edit `SportWarehouse_ProductDB.csv`;
- copy/move/rename/delete image files;
- generate executable SQL;
- modify admin/runtime/frontend code;
- activate products;
- set featured flags.

## Acceptance criteria
Architecture translation is ready to guide implementation when all are true:

1. The core architectural decision (folders retained but demoted to derived layout) is accepted.
2. The manifest family and minimum columns are agreed for next batch execution.
3. Identifier role policy (`itemId`/`external_item_id`/`model_id` now; normalized IDs later) is accepted.
4. Collision-review and approval-checklist workflow is mandatory precondition to copy simulation/update planning.
5. Publication-gate report definition is accepted for Inactive Product Review readiness framing.
6. Tooling roadmap stages are approved in sequence with no direct mutation work embedded in Stage 1-2.
7. Database migration remains explicitly optional and gated by multi-brand evidence.
