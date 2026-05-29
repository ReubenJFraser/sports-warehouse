# Ryderwear Batch 2 source-root and policy completion worksheet

## 1. Purpose

This worksheet is documentation-only. It records the remaining human, source-root, and policy decisions required before controlled source-evidence preparation can proceed for Ryderwear Batch 2.

This worksheet is now aligned to the canonical product image set manifest architecture. It does not create, authorize, or unblock any downstream artifact. It does not create source inventory, suspicious mapping, copy simulation, SQL, image-copy, import, admin-view, storefront-view, or publication artifacts.

This worksheet specifically does not create or modify:

- `source_asset_inventory.csv`
- `suspicious_mapping_report.csv`
- `copy_simulation.csv`
- image copy outputs
- SQL/import payloads
- ProductDB records, SQL, schema, runtime code, admin code, frontend code, images, generated batch evidence CSVs, generated manifests, public storefront files, or storefront publication state

Source files examined or used as required alignment references for this worksheet update:

- `source_root_and_policy_completion_worksheet.md`
- `review_decision_gate_report.md`
- `human_reviewer_acceptance_record.json`
- `source_evidence_strategy.md`
- `product-image-set-manifest-architecture.md`
- `product-image-set-manifest-schema.md`
- `product-image-set-manifest.example.json`
- `product-image-set-manifest-flat.example.csv`

## 2. Canonical manifest alignment

This worksheet is aligned to the canonical product image set manifest architecture described by `product-image-set-manifest-architecture.md`, `product-image-set-manifest-schema.md`, `product-image-set-manifest.example.json`, and `product-image-set-manifest-flat.example.csv`.

Canonical alignment statements:

- The canonical JSON manifest is the intended internal source of truth for product image set review, source-root linkage, image identity, approval state, and downstream projection.
- The flat CSV is a review/tooling mirror of the canonical JSON manifest, not the source of truth.
- `source_asset_inventory.csv`, `suspicious_mapping_report.csv`, `copy_simulation.csv`, copy plans, image copy outputs, SQL/import payloads, admin views, storefront galleries, and storefront views are downstream derived outputs.
- Source-root approval enables controlled candidate manifest generation, not uncontrolled copy, import, reconciliation, publication, or storefront gallery changes.
- The likely next generated artifact after this worksheet is completed is a Ryderwear Batch 2 candidate product-image-set manifest, not direct copy simulation.

## 3. Current gate status

The current gate status is taken from `review_decision_gate_report.md` and the saved reviewer record in `human_reviewer_acceptance_record.json`.

- The saved reviewer record is still draft/in-progress because `saved_as` is `draft` and no JSON field records final workflow approval.
- The gate report counts 19 reviewer decisions: 2 `accept_proposed` and 17 `defer_decision`.
- The workflow is partially approved for source-evidence preparation only.
- The workflow is not ready for copy simulation.
- The workflow is not ready for image copying.
- The workflow is not ready for import/reconciliation.
- The workflow is not ready for publication.
- Downstream artifacts remain blocked overall.
- Source evidence preparation is limited to later documentation-level or candidate-manifest preparation for accepted and eligible decisions only, after explicit source-root and manifest policy approval.
- After source-root and policy completion, the conservative next step is a Ryderwear Batch 2 candidate product-image-set manifest from approved source roots and accepted/eligible decisions only, not direct `copy_simulation.csv` generation.

## 4. Source-root decision

Human reviewer or policy owner completion area:

| Field | Human completion value |
|---|---|
| approved_source_root | TBD |
| approved_source_root_type | TBD |
| source_root_owner_or_origin | TBD |
| source_root_scope | TBD |
| source_root_exclusions | TBD |
| reviewer_decision | TBD |
| reviewer_notes | TBD |
| approval_date | TBD |

Required source-root constraints:

- Broad user-folder scans are not approved.
- Controlled allowlisted roots are required.
- Existing local-generated artifacts may be used only as hints unless separately approved.
- Repository-local `images/brands/...` paths may be used as controlled evidence context only if explicitly approved as the source corpus for this batch.
- Destination artifacts must not be treated as canonical source evidence unless the reviewer explicitly approves that treatment.
- The approved source root must be explicit before a Ryderwear Batch 2 candidate product-image-set manifest can be generated.
- The approved source root will become one or more `SourceRoot` entries in the future Ryderwear candidate manifest.
- A future controlled scan, if approved later, must be limited to the recorded source root scope and exclusions.
- An explicit source-root decision does not approve image copying, import payloads, ProductDB updates, or storefront gallery changes.

## 5. Source-root decision options

Allowed values for `reviewer_decision`:

- `approve_source_root`
- `revise_source_root`
- `defer_source_root`
- `reject_source_root`

Decision value meanings:

| Decision value | Meaning |
|---|---|
| `approve_source_root` | The named root, type, owner/origin, scope, and exclusions are explicit enough to support a later controlled candidate manifest task. |
| `revise_source_root` | The source-root proposal needs a narrower, clearer, or otherwise corrected definition before use. |
| `defer_source_root` | No approved source root exists yet; candidate manifest generation and downstream derived outputs remain blocked. |
| `reject_source_root` | The proposed root must not be used for this batch. |

## 6. Existing source-evidence policy decisions to reconcile with manifest model

The following policy completion sections preserve the existing useful source-evidence policy content, but reframe it so `source_asset_inventory.csv` is not treated as the main next source-of-truth artifact. These sections do not approve anything by themselves; they provide a place for human reviewer completion.

### 6.1 Deterministic source_asset_id policy

| Field | Value |
|---|---|
| current proposed approach | Reconcile the prior `source_asset_id` concept with the canonical manifest model. Candidate options are: supersede `source_asset_id` with manifest `image_id`; retain `source_asset_id` only as a technical source inventory alias; or map `source_asset_id` into an `image_id` and `checksum_sha256` strategy. |
| unresolved issue | The worksheet must not automatically decide whether `source_asset_id` is superseded, retained as a technical alias, or mapped into `image_id`. Human manifest policy approval is required before any candidate manifest or later `source_asset_inventory.csv` projection uses this field. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 6.2 Checksum/bytes/MIME capture policy

| Field | Value |
|---|---|
| current proposed approach | Capture `checksum_sha256`, `bytes`, and `mime_type` during one reproducible controlled pass over approved `SourceRoot` entries for rows that are accepted and eligible for the candidate manifest. Use filesystem stat for bytes and a consistent detector policy for MIME, with magic-bytes first and extension fallback only if needed. |
| unresolved issue | The checksum/bytes/MIME normalization policy is deferred in the saved reviewer record. Tooling, detector order, timestamp/run documentation, and consistency rules still require explicit approval before any candidate manifest or derived source evidence view is generated. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 6.3 Provenance_note policy

| Field | Value |
|---|---|
| current proposed approach | Encode source root label/version, `SourceRoot` reference, scan or evidence run identifier/date, mapping basis, confidence class, collision group or suspicious reason where applicable, reviewer decision state, defer reason, and prerequisite condition to clear defer. |
| unresolved issue | The provenance_note policy is deferred in the saved reviewer record. Required row-type vocabulary and minimum provenance content still require explicit approval before manifest rows or downstream evidence views are generated. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 6.4 Suspicious/remap representation policy

| Field | Value |
|---|---|
| current proposed approach | Represent suspicious/remap cases in the candidate manifest as review state and evidence/history only when allowed by manifest policy. Later `suspicious_mapping_report.csv` rows, if approved by later gates, should be derived from manifest review fields and supporting evidence rather than treated as the canonical source of truth. |
| unresolved issue | `suspicious_mapping_report.csv` remains blocked. Accepted suspicious/remap decisions can be considered for candidate manifest inclusion only after source-root and manifest policy approval, and unresolved suspicious/remap cases must remain excluded or separately routed. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 6.5 Accepted-vs-deferred inclusion policy

| Field | Value |
|---|---|
| current proposed approach | Include only accepted and eligible product-item visual evidence decisions in any later candidate product-image-set manifest. Exclude all deferred cases unless they are separately resolved by a human reviewer. |
| unresolved issue | The saved reviewer record has only 2 accepted decisions and 17 deferred decisions. The workflow must avoid silently converting draft or deferred decisions into approved manifest rows, copied assets, import payloads, or storefront gallery entries. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 6.6 Banner/non_product separation policy

| Field | Value |
|---|---|
| current proposed approach | Treat banner/non_product cases outside product-item gallery approval. They may remain as evidence/history, but they should be excluded from product gallery exports by default and may require a separate marketing/banner `SourceRoot` and workflow. |
| unresolved issue | `suspicious-01` is explicitly classified by the reviewer as a banner and non-product item. It must not be included as product-item visual evidence or exported as a product gallery row without a separate human decision and workflow. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

## 7. Canonical manifest policy decisions

The following policy completion sections are required before a Ryderwear Batch 2 candidate product-image-set manifest can be generated. Each subsection is a human-completion area and does not approve anything by itself.

### 7.1 manifest_id and batch_id naming policy

| Field | Value |
|---|---|
| current proposed approach | Use stable manifest identity that includes the Ryderwear Batch 2 batch key and a candidate/draft marker. Keep `batch_id` aligned with `ryderwear-batch2-2026-05-27-01`. |
| unresolved issue | Final naming format, version marker, draft/candidate marker, and collision behavior for re-runs are not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.2 source_root_id naming policy

| Field | Value |
|---|---|
| current proposed approach | Assign each approved root a deterministic `source_root_id` and represent it as a `SourceRoot` entry with root type, owner/origin, scope, and exclusions. |
| unresolved issue | The format for `source_root_id`, handling of multiple roots, root versioning, and root exclusion encoding is not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.3 product_key and item_id mapping policy

| Field | Value |
|---|---|
| current proposed approach | Map each product image set to a stable `product_key` and, where available, the relevant `item_id` or itemId from reviewer/gate artifacts. Preserve slug-only decisions when itemId is not available. |
| unresolved issue | The canonical mapping rules for slug-only suspicious/remap cases, itemId formatting, ProductDB identity linkage, and conflict handling are not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.4 image_id generation policy

| Field | Value |
|---|---|
| current proposed approach | Generate a deterministic `image_id` for each manifest image row from approved source root context, normalized relative path, checksum where available, and product image set context. |
| unresolved issue | The exact `image_id` canonical input, whether it supersedes or maps from `source_asset_id`, and whether checksum is required for candidate rows are not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.5 sequence and role assignment policy

| Field | Value |
|---|---|
| current proposed approach | Record image `sequence` and role assignment, such as primary/gallery/supporting evidence, in the candidate manifest before any downstream gallery or copy projection. |
| unresolved issue | The rules for primary image selection, gallery order, tie-breakers, and unsupported or evidence-only images are not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.6 variant_group policy

| Field | Value |
|---|---|
| current proposed approach | Use `variant_group` to group images by relevant product variation context when needed, such as color, size segment, collection, or other approved merchandising dimension. |
| unresolved issue | The allowed `variant_group` vocabulary, normalization rules, and behavior for unknown or ambiguous variants are not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.7 approval_status and review_decision_code mapping policy

| Field | Value |
|---|---|
| current proposed approach | Map saved reviewer states into manifest `approval_status` and `review_decision_code` fields without converting draft/deferred states into approval. Accepted cases may become candidate-approved or candidate-eligible only if source-root and manifest policy approvals are complete. Deferred cases remain deferred or excluded. |
| unresolved issue | The exact mapping from `accept_proposed`, `defer_decision`, banner/non_product handling, and source/provenance blockers into `approval_status` and `review_decision_code` is not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.8 Delivery asset projection policy

| Field | Value |
|---|---|
| current proposed approach | Treat delivery assets, copied files, import payload paths, admin views, and storefront views as downstream projections from approved manifest rows only. |
| unresolved issue | Projection rules for destination paths, gallery exports, import payloads, copy plans, and storefront visibility are not approved and remain blocked by later gates. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.9 JSON canonical plus CSV mirror policy

| Field | Value |
|---|---|
| current proposed approach | Generate canonical JSON manifest as the internal source of truth and derive flat CSV review/tooling mirrors from that JSON when a later task is approved. |
| unresolved issue | The exact JSON path, CSV mirror path, flattening rules, round-trip constraints, and reviewer edit rules are not approved. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

### 7.10 banner/non_product separation policy

| Field | Value |
|---|---|
| current proposed approach | Preserve banner/non_product rows as evidence/history when needed, exclude them from product gallery exports by default, and route them through a separate marketing/banner `SourceRoot` and workflow if they are to be managed. |
| unresolved issue | The separate banner/non_product workflow, SourceRoot policy, export behavior, and approval mapping are not approved. The known banner/non-product reviewer case must not be treated as product-item visual approval. |
| human decision | TBD |
| reviewer notes | TBD |
| follow_up_required | TBD |
| approval_date | TBD |

## 8. Accepted decisions eligible for candidate manifest preparation

The saved reviewer record and gate report list the following decisions as `accept_proposed`. These decisions are not import approval, copy approval, final publication approval, or storefront gallery approval. They are conditionally eligible for inclusion in a future Ryderwear candidate product-image-set manifest only after source-root and manifest policy approval.

| decision_id/key | itemId if available | evidence basis | reviewer note | product-item visual evidence? | eligible for future candidate product-image-set manifest after source-root/manifest-policy approval? |
|---|---|---|---|---|---|
| suspicious-02 / `ryderwear_unisex_gym_bag_accessories` | Not provided in saved reviewer JSON; slug only | Reviewer states visible product/image evidence supports the proposed decision. Relevant context appears in suspicious/remap scope across the gate and planning artifacts. | Product/image evidence is visible in the Review Approvals form and supports the proposed decision. | Yes, according to reviewer note. | Yes, conditionally, only after this worksheet records approved source-root and manifest policy decisions. |
| suspicious-03 / `ryderwear_female_nkd_shorts_v_scrunch` | Not provided in saved reviewer JSON; slug only | Reviewer states visible product/image evidence supports the proposed decision. Relevant context appears in suspicious/remap scope across the gate and planning artifacts. | Product/image evidence is visible in the Review Approvals form and supports the proposed decision. | Yes, according to reviewer note. | Yes, conditionally, only after this worksheet records approved source-root and manifest policy decisions. |

## 9. Deferred decisions not eligible yet

Deferred cases must not be included as approved source evidence candidates unless separately resolved by a human reviewer with explicit evidence and approval. Deferred cases must not become approved manifest rows, copied assets, import payloads, product gallery exports, storefront gallery entries, or storefront publication inputs unless separately resolved. The groupings below come from the saved reviewer record and gate report.

### 9.1 Image evidence not visible

- `dec-001` / itemId `156`
- `dec-002` / itemId `157`
- `dec-003` / itemId `158`
- `dec-004` / itemId `159`
- `dec-005` / itemId `160`
- `dec-006` / itemId `161`
- `dec-007` / itemId `162`
- `dec-008` / itemId `163`
- `dec-009` / itemId `164`
- `dec-010` / itemId `165`
- `dec-011` / itemId `166`
- itemId `184`
- `approved_source_root_policy`
- `deterministic_source_asset_id_policy`
- `checksum_bytes_mime_normalization_policy`
- `provenance_note_policy`

### 9.2 Banner/non-product workflow review required

- `suspicious-01` / `ryderwear_female_nkd_leggings_v_full_length_scrunch`

### 9.3 Source/provenance evidence required

- itemId `184` remains deferred and requires reviewer-confirmed source provenance evidence for ownership.
- Split destination decisions with deferred image evidence may also require source-root aligned evidence before any source-evidence candidate or manifest row status can be assigned.

### 9.4 Policy/source-root missing

- `approved_source_root_policy`
- `deterministic_source_asset_id_policy`
- `checksum_bytes_mime_normalization_policy`
- `provenance_note_policy`
- Manifest policy decisions for manifest_id, batch_id, source_root_id, product_key, item_id, image_id, sequence, role assignment, variant_group, approval_status, review_decision_code, delivery asset projection, JSON canonical plus CSV mirror, and banner/non_product separation.

### 9.5 Other/manual review required

- `suspicious-01` requires a separate manual pathway as a banner/non_product case.
- Suspicious/remap reporting remains blocked until later gates; this worksheet does not create `suspicious_mapping_report.csv`.

## 10. Banner/non_product handling

The known banner/non_product case is:

- `suspicious-01` / `ryderwear_female_nkd_leggings_v_full_length_scrunch`

The reviewer record states that this is a banner and non-product item rather than a product-item image decision. It must not be treated as product-item visual approval.

Architecture-aligned handling:

- Banner/non_product rows may remain as evidence/history if a later candidate manifest policy permits them.
- Banner/non_product rows must be excluded from product gallery exports by default.
- Banner/non_product rows may require a separate marketing/banner `SourceRoot` and workflow.
- This known banner/non-product reviewer case must not be treated as product-item visual approval.
- This case must not be included in a controlled product-item candidate manifest, copied asset set, import payload, storefront gallery, or storefront publication input unless a separate human decision resolves it under an appropriate banner/non_product workflow.

## 11. ItemId 184 provenance status

itemId `184` remains deferred.

Evidence required before itemId `184` can proceed:

1. Reviewer-confirmed source provenance evidence for ownership.
2. Alignment with an approved source root and future `SourceRoot` entry.
3. Explicit reviewer decision update after evidence is visible and verified.
4. Manifest policy approval for how source/provenance evidence maps into candidate rows.

Visual evidence alone must not approve itemId `184` if source/provenance remains unresolved.

## 12. Candidate manifest and source-evidence readiness checklist

A human reviewer or policy owner must complete this checklist before a follow-up task can generate a Ryderwear Batch 2 candidate product-image-set manifest. A later `source_asset_inventory.csv` or other source evidence view may be derived only after the manifest approach is approved.

- [ ] Approved source root recorded.
- [ ] Source root scope recorded.
- [ ] Source root exclusions recorded.
- [ ] Source root owner or origin recorded.
- [ ] `SourceRoot` entry policy approved.
- [ ] manifest_id and batch_id naming policy approved.
- [ ] source_root_id naming policy approved.
- [ ] product_key and item_id mapping policy approved.
- [ ] image_id generation policy approved.
- [ ] `source_asset_id` reconciliation policy approved.
- [ ] Checksum/bytes/MIME capture policy approved.
- [ ] `provenance_note` policy approved.
- [ ] sequence and role assignment policy approved.
- [ ] variant_group policy approved.
- [ ] approval_status and review_decision_code mapping policy approved.
- [ ] delivery asset projection policy approved.
- [ ] JSON canonical plus flat CSV mirror policy approved.
- [ ] Suspicious/remap inclusion policy approved.
- [ ] Accepted-vs-deferred inclusion policy approved.
- [ ] banner/non_product separation policy approved.
- [ ] Deferred cases excluded or separately resolved.
- [ ] Banner/non_product case excluded or routed separately.
- [ ] The later candidate manifest scope is limited to accepted/eligible decisions and the approved source root.

## 13. Gate recommendation

Conservative gate recommendation:

- `source_asset_inventory.csv` remains blocked until later gates and should be treated as a downstream derived output, not the next canonical source-of-truth artifact.
- A Ryderwear Batch 2 candidate product-image-set manifest remains blocked until this worksheet is completed by a human reviewer or policy owner.
- Once this worksheet is completed, a follow-up task may generate a controlled candidate product-image-set manifest scoped only to accepted/eligible decisions and the approved `SourceRoot` entries.
- Review/tooling CSV mirrors and source evidence views may be derived only after the candidate manifest approach is approved.
- `suspicious_mapping_report.csv` remains blocked until later gates.
- `copy_simulation.csv` remains blocked until later gates.
- Image copy outputs remain blocked until later gates.
- SQL/import payloads remain blocked until later gates.
- Storefront publication and storefront gallery exports remain blocked until later gates.
- This worksheet does not unblock copy simulation, image copying, import, reconciliation, publication, ProductDB changes, admin views, storefront views, or public storefront changes.

## 14. Recommended next Codex task

Do not run any next artifact-generation task until this worksheet is actually completed by a human reviewer or policy owner.

Conservative next-task sequence:

1. Complete this worksheet with human source-root and manifest-policy decisions.
2. Then generate a Ryderwear Batch 2 candidate product-image-set manifest from approved source roots and accepted/eligible decisions only.
3. Then create or derive review/tooling CSV mirrors and source evidence views from the candidate manifest.
4. Only later generate copy simulation from approved manifest rows after the relevant later gates approve that step.

The candidate manifest task must remain scoped to the approved source root, recorded exclusions, approved policy decisions, and accepted eligible decisions only. It must not include deferred cases, banner/non_product cases, unresolved source/provenance cases, copied assets, import payloads, storefront gallery entries, or storefront publication inputs unless a separate human decision resolves them first.
