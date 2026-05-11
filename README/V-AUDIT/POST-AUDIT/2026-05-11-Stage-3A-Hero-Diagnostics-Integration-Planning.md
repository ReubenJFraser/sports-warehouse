# Stage 3A Hero Diagnostics Integration Planning

**Status:** Planning only  
**Accepted diagnostic schema:** `active_layers.hero_candidates_stage2d.v1`  
**Scope:** Future Hero Manager diagnostic consumption planning  
**Non-goal:** Implementation

## 1. Stage 3A Status

Stage 3A is a planning stage only.

No Hero Manager integration is being implemented in this stage.

The Stage 2D JSON format, `active_layers.hero_candidates_stage2d.v1`, is accepted as a stable diagnostic review format. It is not yet a formal Hero Manager integration contract.

Manual Hero Manager selections remain authoritative.

## 2. Core Principle

> Automation suggests. Manual Hero Manager selections win.

Diagnostics may support admin review, but must not automatically replace:

- confirmed hero selections
- `hero_override`
- `hero_rejections`
- `HeroAuthority` / authority logic
- manual editorial decisions

The diagnostic pipeline exists to explain evidence. It does not own hero image authority.

## 3. Future JSON Consumer Decision

The Hero Manager should not wire the raw JSON directly into multiple admin pages.

The preferred future direction is an adapter layer, for example:

- `inc/hero/diagnostics.php`

The adapter should:

- validate the schema version
- load diagnostic records
- expose only approved fields
- normalize lookup behavior
- fail safely when diagnostics are unavailable

This adapter is not created in Stage 3A. This document only defines the planning direction.

## 4. Formal Consumer Contract

Before integration, fields should be grouped by display safety.

### A. Safe to Display as Badges or Summary Fields

These fields are suitable for compact review badges:

- `product_type`
- `path_classification_confidence`
- `roi_specificity`
- `roi_confidence`
- `needs_manual_review`
- `warnings`
- `category_specific_warnings`

### B. Display Only with Explanatory Wording

These fields may be useful, but require context:

- `final_advisory_score`
- `score_scope`
- `face_detected`
- `pose_detected`
- `crop_safety_score`
- `roi_fill_score`
- `image_quality_score`

For example, `final_advisory_score` must not be presented as a universal image-quality rank.

### C. Internal / Debug Only

These fields should remain hidden unless a debug view explicitly needs them:

- pose landmark coordinates
- raw bbox coordinates
- `normalized_tokens`
- `alpha_occupancy`
- `padding_pct`
- `object_fill_ratio`
- detailed score components

### D. Do Not Display as User-Facing Claims

The UI must not display wording that implies:

- garment segmentation
- garment masks
- garment-specific bounding boxes
- automated selection authority
- global score ranking across product types

The current diagnostic pipeline does not implement those capabilities.

## 5. Safe UI Wording

Unsafe wording:

- AI detected garment region
- Garment bounding box
- Best hero image selected by AI
- AI-approved hero image

Safe wording:

- Diagnostic ROI source: body-region band
- Pose-based body-region estimate
- Alpha subject bounds
- Object bounds
- Advisory diagnostic score
- Manual review recommended
- Path classification confidence

The UI must never imply true garment segmentation, because the pipeline does not perform garment segmentation.

## 6. Recommended First Integration Target

The safest first future integration target is:

- `admin/hero-candidates.php`

Reasons:

- it is already candidate-focused
- it is the safest place to display diagnostic context
- it avoids cluttering `hero-manager.php` or `hero-edit.php` too early
- it supports review without changing selection authority

Future integration should start with compact diagnostic badges, not full raw JSON output.

Possible badges:

- Type: `sports_bra`
- ROI: body-region band
- ROI confidence: medium
- Path classification: high
- Review: yes/no
- Warning: pose missing
- Warning: lower-body ROI uncertain

## 7. Score Display Caution

`final_advisory_score` is diagnostic and category-scoped.

It must not be presented as a universal quality score.

If displayed, safe wording would be:

> Advisory score — compare only with similar product/category records.

For first integration, a safer option is to hide `final_advisory_score` and display warning/ROI badges first.

## 8. Fallback and Failure Behaviour

Future integration must fail safely.

If:

- the JSON file is missing
- the JSON is invalid
- the schema version is unsupported
- an image path has no matching diagnostic record
- diagnostic output appears stale

then the Hero Manager should show:

> Diagnostics unavailable. Manual selection remains available.

Diagnostics must never block manual hero selection.

## 9. Schema Versioning Expectations

Future consumers must check the schema before consuming the JSON.

Supported schema for current planning:

- `active_layers.hero_candidates_stage2d.v1`

Unsupported schema versions should be ignored or shown as:

> Unsupported diagnostics schema.

Unsupported diagnostics must not cause UI failure.

## 10. Staleness and Source-of-Truth Caution

The JSON is a generated diagnostic artifact.

It is not the source of truth for:

- product data
- image authority
- manual hero state
- rejections
- overrides

Future integration may display a generation timestamp or file-modified time, but that is not implemented in Stage 3A.

## 11. Recommended Stage 3B Candidate

Recommended next stage:

> Stage 3B: Design a read-only Hero Diagnostics adapter contract.

Stage 3B should still avoid database writes and avoid UI integration until the adapter contract is clear.

Potential Stage 3B deliverables:

- document the adapter interface
- define supported schema versions
- define approved display fields
- define lookup by `image_path`
- define failure behaviour

No adapter should be implemented until this contract is accepted.
