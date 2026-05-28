# Ryderwear Batch 2 Source-Evidence Strategy

## 1) Scope
This document is a **report-only source-evidence strategy** for Ryderwear Batch 2 in the standardized manifest folder:

- `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/`

It defines how source evidence should be gathered and normalized **before** creating `source_asset_inventory.csv` or `copy_simulation.csv`.

## 2) Current blocker summary
`source_asset_inventory.csv`, `suspicious_mapping_report.csv`, and `copy_simulation.csv` are not yet safe to create because:

- all three files are intentionally absent in the current standardized batch state;
- 11 approval checklist decisions remain `pending_human_approval`;
- itemId `184` remains `deferred_source_verification`;
- 3 suspicious/remap cases still require manual review;
- deterministic source-path selection and normalization policy is not frozen;
- deterministic `source_asset_id` policy is not frozen;
- `checksum_sha256`, `bytes`, and `mime_type` capture policy is not yet normalized;
- per-row `provenance_note` policy is not yet normalized.

Because of these blockers, no standardized source inventory or safe simulation basis exists yet.

## 3) Available evidence inventory
Assessment of currently available artifacts relevant to source evidence.

| Artifact | Present | Likely tracking state | Evidence quality for standardized source inventory | Notes |
|---|---|---|---|---|
| `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/product_identity_snapshot.csv` | yes | tracked repo artifact | partial evidence only | identity anchor; does not prove filesystem source asset existence/checksum |
| `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/asset_mapping_plan.csv` | yes | tracked repo artifact | partial evidence only | candidate mapping/destination intent; not source-asset proof |
| `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/destination_collision_report.csv` | yes | tracked repo artifact | partial evidence only | collision ownership risk evidence; not source inventory |
| `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/approval_checklist.csv` | yes | tracked repo artifact | partial evidence only | approval gate evidence; unresolved states block simulation |
| `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/manifest_consistency_and_source_gap_audit.md` | yes | tracked repo artifact | partial evidence only | authoritative gap statement and blocker summary |
| `docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/backfill_summary.md` | yes | tracked repo artifact | partial evidence only | confirms intentionally deferred files and blocker conditions |
| `docs/operations/generated/ryderwear-folder-inventory-local.csv` | no (in this workspace) | local untracked artifact when present | partial evidence only | useful lead list if regenerated; still needs controlled normalization |
| `docs/operations/generated/ryderwear-folder-tree-local.txt` | no (in this workspace) | local untracked artifact when present | partial evidence only | folder topology only; lacks per-file checksum/bytes/mime |
| `docs/operations/generated/source-ryderwear-semantic-image-folders-local.csv` | no (in this workspace) | local untracked artifact when present | partial evidence only | semantic hints; not standardized proof |
| `docs/operations/generated/source-ryderwear-semantic-image-folders-enhanced-local.csv` | no (in this workspace) | local untracked artifact when present | partial evidence only | enhanced semantic hints; still non-canonical |
| `docs/operations/generated/source-ryderwear-terminal-image-folders-local.csv` | no (in this workspace) | local untracked artifact when present | partial evidence only | terminal-folder candidates only |
| `docs/operations/generated/ryderwear-source-image-copy-results-local.csv` | no (in this workspace) | local untracked artifact when present | unsuitable for standardized source inventory | copy-result context is downstream and may encode non-final assumptions |
| `docs/operations/generated/ryderwear-candidate-path-existence-local.csv` | no (in this workspace) | local untracked artifact when present | partial evidence only | existence checks are useful but insufficient without normalized source asset rows |
| `docs/operations/generated/ryderwear-copy-duplicate-destinations-local.csv` | no (in this workspace) | local untracked artifact when present | unsuitable for standardized source inventory | destination duplicate analysis is not source-asset evidence |

Conclusion: current tracked artifacts provide strong planning/review context but **not sufficient canonical source-asset evidence** for standardized `source_asset_inventory.csv`.

## 4) Approved source-root strategy
Recommended source-root approach for Ryderwear Batch 2:

1. **Primary source root policy**
   - Use a single, reviewer-approved, deterministic source root (or explicit allowlisted set of roots) that is documented in batch metadata before inventory generation.
   - Treat source root as immutable for the batch review cycle once approved.

2. **Repository-local images usage**
   - Repository-local `images/brands/...` paths can be used as controlled evidence context **only if** they are explicitly approved as the source corpus for this batch.
   - If they are destination artifacts rather than true source-of-truth acquisition assets, they should not be treated as canonical source evidence.

3. **Previously generated local source-folder reports**
   - Use prior local reports (when present) as discovery aids and candidate-path hints only.
   - Do not promote them directly into canonical source inventory rows without fresh controlled verification.

4. **Fresh controlled scan recommendation**
   - A fresh, controlled, reproducible source-folder scan is needed before source inventory generation.
   - The scan should enumerate files under approved root(s), record normalized relative paths, and capture planned checksum/size/MIME metadata fields.

5. **Disallowed discovery pattern**
   - Do **not** use broad user-profile searches (e.g., sweeping OneDrive/Home/Desktop style discovery) to populate standardized inventory.
   - Only approved batch scope roots should be scanned.

## 5) `source_asset_id` strategy
Candidate strategies and assessment:

1. **Path-based ID only**
   - Pros: simple and deterministic per root.
   - Cons: unstable if path corrections or root migration occur; poor duplicate-content detection.

2. **Checksum-based ID only**
   - Pros: content-stable; detects identical binaries across paths.
   - Cons: requires file hashing availability; cannot uniquely represent intentionally distinct provenance contexts for same binary.

3. **Combined batch/path/hash ID**
   - Pros: stable and auditable; combines provenance context (batch + normalized relative path) with content identity (hash); resilient to ambiguity.
   - Cons: slightly more complex generation rule.

4. **Curated/manual ID**
   - Pros: human-readable/ad hoc flexibility.
   - Cons: non-deterministic, error-prone, weak reproducibility.

**Preferred recommendation:** use a deterministic **combined batch/path/hash rule**.

Suggested pattern:
- canonical input: `<batch_id>|<normalized_source_relative_path>|<checksum_sha256>`
- output: `source_asset_id = sa-<sha256(canonical input)>` (or comparable fixed digest format)

This preserves deterministic reproducibility and traceability while remaining compatible with future de-dup logic.

## 6) Checksum / size / MIME strategy
To be captured in a later controlled evidence pass (not in this task):

- `checksum_sha256`: computed directly from file bytes for each approved source file.
- `bytes`: captured from filesystem stat during the same controlled scan.
- `mime_type`: derived using a consistent detector policy (e.g., magic-bytes first, extension fallback only if needed).

Normalization rules for the future pass:
- run metadata capture in one reproducible scan step over approved roots;
- store paths relative to approved `source_root`;
- avoid recomputing with mixed tools/policies inside one batch;
- document toolchain and run timestamp in batch notes for reproducibility.

## 7) Provenance-note strategy
`provenance_note` should encode evidence lineage and review state in concise machine-readable text.

Recommended minimum content by row type:

1. **Normal rows**
   - source root label/version;
   - scan run identifier/date;
   - mapping basis (e.g., model_id/path semantic match);
   - confidence class.

2. **Split-path collision rows**
   - collision group id;
   - proposed split destination context;
   - linked decision id from `approval_checklist.csv`;
   - reviewer decision state.

3. **Suspicious/remap rows**
   - suspicious reason_code;
   - short evidence pointer (path token mismatch, category mismatch, etc.);
   - required manual action and status.

4. **Deferred rows**
   - explicit defer reason (`deferred_source_verification` etc.);
   - blocking evidence gap;
   - prerequisite condition to clear defer.

## 8) `suspicious_mapping_report.csv` strategy
Define future generation policy as follows.

### 8.1 Reason-code vocabulary
Adopt fixed reason codes (initial set):
- `category_path_mismatch`
- `gender_path_mismatch`
- `collection_token_conflict`
- `model_token_conflict`
- `ambiguous_multi_owner_source`
- `low_confidence_semantic_match`
- `manual_remap_required`
- `source_not_verified`

### 8.2 Evidence column mapping
For each row, `evidence` should include compact structured evidence, such as:
- candidate source relative path;
- compared model/category tokens;
- competing itemIds/model_ids when applicable;
- related collision group / decision id references.

### 8.3 Status values
Use controlled statuses:
- `open_manual_review`
- `approved_remap`
- `rejected_remap`
- `deferred_source_verification`
- `resolved_no_change`

### 8.4 Inclusion criteria
Include rows when any of the following is true:
- mapping confidence is low/unknown;
- collision handling depends on manual semantic adjudication;
- source ownership ambiguity exists;
- source verification is explicitly deferred;
- recommended action differs from current mapping baseline.

### 8.5 Representation of the 3 suspicious/remap rows
Create exactly three `suspicious_mapping_report.csv` rows keyed to the current known suspicious/remap cases, each with:
- `batch_id`, `itemId`, `external_item_id`, `model_id`;
- specific `reason_code` from vocabulary;
- concrete `evidence` payload;
- explicit `recommended_action` (approve remap / reject remap / defer verify);
- initial `status` as `open_manual_review` or `deferred_source_verification` where applicable.

## 9) `copy_simulation.csv` prerequisites
`copy_simulation.csv` may be created only when all prerequisites below are met:

1. **Human approvals complete**
   - all relevant `approval_checklist.csv` rows for simulation scope are no longer `pending_human_approval`.

2. **Source verification complete**
   - deferred source-verification blockers (including itemId 184) are resolved or explicitly excluded from simulation scope.

3. **Suspicious/remap adjudication complete**
   - required suspicious rows are instantiated in `suspicious_mapping_report.csv` and resolved to a non-open state for rows included in simulation.

4. **Source inventory present**
   - canonical `source_asset_inventory.csv` exists with deterministic `source_asset_id`, normalized path, checksum, bytes, and MIME fields.

5. **Collision resolution state aligned**
   - destination collision decisions are reflected in mapping plan status and approved destination paths for all simulated rows.

6. **Scope discipline**
   - simulation excludes unresolved/deferred rows and includes notes for any intentionally excluded entities.

## 10) Recommended next task
Options considered:
- create a controlled source-folder scan script/report;
- normalize `suspicious_mapping_report.csv` first;
- complete human approval checklist decisions;
- create `source_asset_inventory.csv` from existing evidence only.

**Preferred recommendation: complete human approval checklist decisions first.**

Why this is safest next:
- 11 rows are still `pending_human_approval`, which is the largest immediate gate to any downstream trustworthy mapping/simulation decisions.
- unresolved human decisions directly affect split-path ownership and whether source evidence should be interpreted as valid for a given item.
- normalizing suspicious report or source scan outputs before approvals risks rework because ownership/path decisions can still change.

After approvals are completed, run a controlled source-folder scan/report as the immediate follow-on step.

## 11) Non-goals
This strategy explicitly performs **none** of the following:

- no DB changes;
- no ProductDB changes;
- no image file copy/move/rename/delete operations;
- no SQL generation;
- no admin/runtime/frontend/code changes;
- no product activation changes;
- no featured-flag changes;
- no creation of `source_asset_inventory.csv` yet;
- no creation of `copy_simulation.csv` yet.
