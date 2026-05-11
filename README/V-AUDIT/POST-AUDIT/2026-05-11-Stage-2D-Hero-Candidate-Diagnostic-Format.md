# Stage 2D Hero Candidate Diagnostic Format

**Status:** Accepted diagnostic review format  
**Schema:** `active_layers.hero_candidates_stage2d.v1`  
**Scope:** JSON-only local preprocessing output  
**Non-goal:** Hero Manager integration contract

## 1. Status

`active_layers.hero_candidates_stage2d.v1` is accepted as a stable diagnostic review format for local hero-candidate analysis.

It is not yet a Hero Manager integration contract. The JSON is advisory only, and manual Hero Manager selections remain authoritative.

The governing principle remains:

> Automation suggests. Manual Hero Manager selections win.

## 2. Purpose

The JSON exists to support review of hero-image candidate analysis across product categories before any admin integration.

It captures:

- path/text-based product classification
- diagnostic vocabulary
- ROI specificity
- pose, face, and alpha evidence
- general warnings
- category-specific warnings
- manual-review flags
- advisory scoring

The format is useful for comparing failure patterns across upper-body products, sports bras/crops, lower-body products, full-body/set items, and object controls.

## 3. Conceptual Boundary

The current pipeline can identify:

- `body_region_band`
- `alpha_subject_bbox`
- `object_bbox`

The current pipeline does not perform:

- garment segmentation
- garment masks
- garment-specific bounding boxes
- authoritative hero-image selection
- database writes
- Hero Manager UI integration

All current ROI terms must be read as diagnostic evidence, not as proof that the system has isolated the exact garment.

## 4. Key Schema Concepts

### `path_classification_confidence`

Confidence in product-type classification based on normalized path/text terms only.

This is not visual recognition confidence.

### `path_classification_reason`

Human-readable explanation of why the path/text classifier selected a product type.

This may mention selected terms, combined-set phrases, or competing category signals.

### `roi_confidence`

Confidence in the ROI source, not evidence of garment segmentation.

For example, a pose-derived body-region band may be useful, but it is still not a garment-specific mask.

### `roi_specificity`

Describes the kind of ROI evidence used:

- `body_region_band` means a pose/body-region estimate.
- `alpha_subject_bbox` means alpha subject bounds or alpha fallback for non-object products.
- `object_bbox` means an object-product alpha/object bound.
- `face_anchored_band` may be used for face-anchored fallback.
- `full_image_fallback` may be used when no better ROI source exists.

### `roi_is_garment_specific`

This must remain `false` for the current pipeline.

True garment-specific ROI evidence is not implemented.

### `diagnostic_vocabulary`

Canonical product and visual vocabulary matched from normalized path/text terms.

This is used to explain why an image is diagnostically useful, not to define authoritative product taxonomy.

### `category_specific_warnings`

Warnings that are meaningful only in relation to the inferred product type.

Examples include lower-body fallback warnings, sports-bra partial-crop notes, and object pose-not-required notes.

### `final_advisory_score`

Diagnostic score for review only.

It should not be compared globally across product types.

### `score_scope`

`diagnostic_within_category_not_global_rank`

This field explicitly prevents treating scores as universal hero-image rankings.

## 5. Accepted Stage 2E Findings

Stage 2E confirmed that:

- `schema_notes` are present and clear.
- `path_classification_confidence` appears consistently.
- The old ambiguous `classification_confidence` field is not used as a record field.
- `alpha_subject_bbox` is used for non-object alpha subject bounds.
- `object_bbox` remains clean for object controls.
- Diagnostic vocabulary is canonicalized.
- Combined-set classification works for hoodie-and-pants and tracksuit-style paths.
- Competing category signals are preserved, such as `top` in `UEFA_Euro16-Top_Glider_Ball`.
- No field names imply garment masks or true garment segmentation.
- No unknown records remain in the Stage 2D controlled test output.
- No low-confidence records remain in the Stage 2D controlled test output.

## 6. Future Integration Caution

Before any Hero Manager integration, the project still needs:

- a formal consumer contract
- versioning expectations
- a decision about which fields the admin UI should display
- a decision about which fields are trusted, advisory, or hidden
- display wording that avoids overclaiming AI or computer-vision capability

The Hero Manager should not treat this JSON as authority until those decisions are made.

Any future UI should clearly communicate that the pipeline surfaces diagnostic evidence; it does not replace editorial judgment.
