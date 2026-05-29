# Ryderwear Batch 2 Copy Simulation Report

## 1. Summary

- copy_simulation.csv generated.
- Source manifest is v2 JSON: `ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2.json`.
- Destination-path policy was defined in this report/task.
- copy_simulation.csv is a planning artifact only.
- No files were copied.
- Downstream artifacts remain blocked.

## 2. Destination-path policy

- destination_root_scope: images/brands/ryderwear
- proposed_destination_relpath: source_relpath
- proposed_destination_path: images/brands/ryderwear/{source_relpath}
- proposed_destination_filename: original_filename
- policy type: preserve_manifest_source_relpath_under_approved_root

Rationale: The v2 manifest source_root scope is already the repository-local approved Ryderwear image corpus at `images/brands/ryderwear`. This simulation preserves each manifest `source_relpath` under that approved root and does not invent new destination folders.

Path safety controls applied:

- Source paths were resolved only from each v2 manifest `source_root` root_scope plus `source_relpath`.
- Absolute `source_relpath` values were not allowed.
- Path traversal segments were not allowed.
- No folders were scanned.
- No extra rows were inferred.

## 3. Scope

- Products: 2
- ImageAsset rows: 14
- item_id 68 / model_id ryderwear_unisex_gym_bag_accessories
- item_id 96 / model_id ryderwear_female_nkd_shorts_v_scrunch

## 4. Simulation results summary

### copy_action

- no_copy_needed: 14

### collision_status

- same_path: 14

### readiness_status

- ready_no_copy_required: 14

### proposed_destination_exists

- true: 14

### blocked rows

- blocked rows: 0

## 5. Excluded cases

This simulation excludes:

- suspicious-01
- itemId 184
- dec-001 through dec-011
- all deferred cases
- all banner/non_product cases
- products outside the two accepted eligible decisions

## 6. Continued blocks

- Image copying remains blocked.
- SQL/import payloads remain blocked.
- ProductDB updates remain blocked.
- Storefront gallery changes remain blocked.
- Publication remains blocked.

## 7. Next allowed task recommendation

- Create a copy-simulation review gate confirming no copy is needed and deciding whether import/update planning can be considered later.
