# Stage 3B Hero Diagnostics Adapter Contract

**Status:** Contract/design stage only  
**Accepted diagnostic schema:** `active_layers.hero_candidates_stage2d.v1`  
**Proposed future adapter:** `inc/hero/diagnostics.php`  
**Non-goal:** Implementation

## 1. Stage 3B Status

Stage 3B is a contract/design stage only.

No adapter implementation is being created in this stage. No Hero Manager UI integration is being performed in this stage.

The future adapter is read-only. It must not write to MySQL, change hero selections, affect `hero_override`, affect `hero_rejections`, bypass authority logic, or alter manual selections.

## 2. Core Adapter Principle

> Automation suggests. Manual Hero Manager selections win.

The adapter exists only to make diagnostic evidence available safely to admin review pages.

It must never become an authority layer.

## 3. Proposed Adapter Name and Location

Recommended future adapter location:

- `inc/hero/diagnostics.php`

Stage 3B does not create this file.

The future adapter should act as a narrow boundary between:

- `tools-dev/image-analysis/out/hero_candidates_stage1.json`
- Hero Manager admin UI surfaces

Raw JSON should not be wired directly into admin templates.

## 4. Supported Schema Version

Initially supported schema:

- `active_layers.hero_candidates_stage2d.v1`

The future adapter should:

- read the `schema` field
- accept only supported versions
- reject or ignore unsupported versions safely
- expose a clear diagnostic status if unsupported

Possible statuses:

- `ready`
- `diagnostics_unavailable`
- `unsupported_schema`
- `missing_file`
- `invalid_json`

## 5. JSON Source Path

Expected generated JSON path:

- `tools-dev/image-analysis/out/hero_candidates_stage1.json`

This path identifies a generated diagnostic artifact. It is not product source-of-truth.

The future adapter must not assume the JSON is always present.

## 6. Read-Only Behaviour

The adapter must not:

- update products
- update hero selections
- update hero overrides
- update hero rejections
- write to the database
- mutate the JSON file
- regenerate the JSON
- call the Python preprocessing script
- change scoring

The adapter may only:

- check whether the JSON exists
- parse the JSON
- validate schema
- expose selected diagnostic records
- expose safe status messages
- return empty diagnostics if unavailable

## 7. Proposed Adapter Responsibilities

### A. Load Diagnostics Payload

Future function example:

```php
loadHeroDiagnosticsPayload(): array
```

Responsibilities:

- locate JSON file
- handle missing file
- decode JSON safely
- validate top-level structure
- validate schema
- return payload plus status

### B. Index Records by Image Path

Future function example:

```php
indexHeroDiagnosticsByImagePath(array $payload): array
```

Responsibilities:

- build associative lookup by project-relative `image_path`
- handle duplicate paths defensively
- preserve only approved record fields for UI use

### C. Retrieve Diagnostic by Image Path

Future function example:

```php
getHeroDiagnosticForImage(string $imagePath): ?array
```

Responsibilities:

- normalize slashes
- match project-relative paths
- return sanitized display-safe diagnostic subset
- return `null` if no match exists

### D. Return Summary Metadata

Future function example:

```php
getHeroDiagnosticsSummary(): array
```

Responsibilities:

- expose schema
- expose `image_count`
- expose `run_summary`
- expose generated or file-modified time if added later
- expose diagnostic availability status

## 8. Approved Display Fields

### Safe Display Fields

The adapter may expose these fields to UI badges or compact summaries:

- `product_type`
- `inferred_roi_type`
- `path_classification_confidence`
- `path_classification_reason`
- `diagnostic_vocabulary`
- `roi_specificity`
- `roi_confidence`
- `roi_is_garment_specific`
- `roi_is_body_region_specific`
- `warnings`
- `category_specific_warnings`
- `needs_manual_review`
- `score_scope`
- `schema_notes`

### Display with Caution

These fields require explanatory wording:

- `final_advisory_score`
- `face_detected`
- `pose_detected`
- `crop_safety_score`
- `roi_fill_score`
- `image_quality_score`

### Internal / Debug Only

These fields should not be exposed in ordinary UI:

- pose landmark coordinates
- raw bounding box coordinates
- `normalized_tokens`
- `alpha_occupancy`
- `padding_pct`
- `object_fill_ratio`
- detailed score components unless explicitly needed

### Do Not Expose as User-Facing Claims

Do not expose anything that implies:

- garment segmentation
- garment masks
- garment-specific bounding boxes
- automated hero selection authority
- global quality ranking across product types

## 9. Sanitized UI Record Shape

Raw JSON records should not be passed directly to templates.

Future adapter output may use a safe shape such as:

```json
{
  "available": true,
  "schema": "active_layers.hero_candidates_stage2d.v1",
  "product_type": "sports_bra",
  "path_classification_confidence": "high",
  "path_classification_reason": "Classified as sports_bra from clear normalized term match.",
  "roi": {
    "specificity": "body_region_band",
    "confidence": "medium",
    "is_body_region_specific": true,
    "is_garment_specific": false
  },
  "review": {
    "needs_manual_review": false,
    "warnings": [],
    "category_specific_warnings": []
  },
  "score": {
    "final_advisory_score": 65.1,
    "score_scope": "diagnostic_within_category_not_global_rank"
  }
}
```

## 10. Failure Behaviour

If diagnostics are unavailable, the adapter should return a safe object such as:

```json
{
  "available": false,
  "status": "missing_file",
  "message": "Diagnostics unavailable. Manual selection remains available."
}
```

Possible statuses:

- `ready`
- `missing_file`
- `invalid_json`
- `unsupported_schema`
- `no_record_for_image`
- `duplicate_record_warning`

The UI must not break or block manual selection if diagnostics are unavailable.

## 11. Versioning Expectations

Future adapter code should use a supported schema allowlist, for example:

```php
const SUPPORTED_HERO_DIAGNOSTIC_SCHEMAS = [
    'active_layers.hero_candidates_stage2d.v1',
];
```

Unsupported schemas should not be consumed silently.

## 12. Staleness Expectations

Generated JSON may become stale if images change.

Stage 3B does not implement staleness detection, but a future adapter may expose:

- JSON file modified time
- schema version
- `image_count`
- diagnostic run summary
- possible stale warning if source image modification times are newer than the JSON

## 13. First UI Integration Target

The first future UI integration target should be:

- `admin/hero-candidates.php`

It should not begin with:

- broad `hero-manager.php` redesign
- `hero-edit.php` rewrite
- database-backed diagnostics
- scoring replacement

Recommended first UI display:

- Type
- ROI source / specificity
- ROI confidence
- Path classification confidence
- Manual review flag
- Warnings count

Use compact badges only at first.

## 14. Non-Goals

Stage 3B and the future adapter contract explicitly exclude:

- database writes
- automatic hero selection
- scoring replacement
- garment segmentation
- raw pose landmark display
- production dependency on Python at page-load time
- UI integration in Stage 3B

## 15. Recommended Stage 3C

Recommended next stage:

> Stage 3C: Review existing Hero Manager candidate data flow and identify where a read-only diagnostics adapter would attach.

Stage 3C should remain planning/audit unless explicitly approved for code.
