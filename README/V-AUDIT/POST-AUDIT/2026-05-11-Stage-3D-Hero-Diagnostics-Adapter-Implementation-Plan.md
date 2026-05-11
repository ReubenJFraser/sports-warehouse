# Stage 3D - Hero Diagnostics Adapter Implementation Plan

Date: 2026-05-11

Status: implementation planning only. No adapter is implemented in this stage.

Core principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3D defines the first read-only implementation plan for a future Hero Diagnostics adapter. It follows the Stage 3C data-flow audit, which identified `admin/hero-candidates.php` as the safest future attachment point.

This document does not create the adapter, does not integrate diagnostics into the Hero Manager UI, and does not change any PHP, JavaScript, CSS, JSON, Python, database, scoring, override, rejection, authority, or import/update logic.

## Stage 3D Status

- Stage 3D is implementation planning only.
- No adapter is being implemented in this stage.
- No Hero Manager UI integration is being implemented in this stage.
- The future adapter must be read-only.
- The future adapter must not affect manual hero authority.

Accepted diagnostic schema:

`active_layers.hero_candidates_stage2d.v1`

Generated JSON source:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

Accepted future attachment point:

`admin/hero-candidates.php`

Recommended future supporting adapter:

`inc/hero/diagnostics.php`

Stage 3D does not create `inc/hero/diagnostics.php`.

## Implementation Boundary

### Allowed Future Adapter Behavior

A future adapter may:

- Locate the generated JSON file.
- Read the JSON.
- Validate the schema.
- Normalize image paths.
- Index diagnostic records by `image_path`.
- Return sanitized display-safe diagnostics.
- Return safe unavailable statuses.

### Forbidden Future Adapter Behavior

A future adapter must not:

- Write to MySQL.
- Update `hero_image`.
- Update `hero_score`.
- Update `hero_override`.
- Update `hero_rejections`.
- Change scoring.
- Regenerate JSON.
- Call Python.
- Alter candidate ranking.
- Block manual selection.

## Proposed Future File

Likely future file:

`inc/hero/diagnostics.php`

This file should become the narrow read-only boundary between the generated diagnostic JSON and the Hero Manager admin UI.

Stage 3D does not create this file.

## Supported Schema Allowlist

Future adapter code should use a schema allowlist, equivalent to:

```php
const SUPPORTED_HERO_DIAGNOSTIC_SCHEMAS = [
    'active_layers.hero_candidates_stage2d.v1',
];
```

Unsupported schema versions must fail safely. They should not be consumed silently and must not break the admin UI.

## JSON Path Resolution Plan

Expected generated JSON path:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

The adapter should resolve this path from the project root, not from a request URL and not from the current browser path.

The JSON is a generated diagnostic artifact. It is not product source-of-truth. It must not be treated as authority for product records, image selections, rejections, overrides, or scoring.

Possible future path helper:

```php
function sw_hero_diagnostics_json_path(): string
```

This helper should return an absolute filesystem path to the generated JSON.

## Future Adapter Function Plan

The following functions are proposed for the future adapter. They are not implemented in Stage 3D.

### `sw_hero_diagnostics_json_path(): string`

Purpose:

Return the absolute filesystem path to the generated diagnostics JSON.

Expected behavior:

- Resolve from project root.
- Avoid relying on web URLs.
- Keep the generated JSON path centralized.

### `sw_load_hero_diagnostics_payload(): array`

Purpose:

Load and validate the JSON payload.

Return shape:

```php
[
    'available' => true,
    'status' => 'ready',
    'message' => '',
    'schema' => 'active_layers.hero_candidates_stage2d.v1',
    'payload' => [],
]
```

Possible statuses:

- `ready`
- `missing_file`
- `invalid_json`
- `unsupported_schema`
- `invalid_payload_shape`

Expected behavior:

- Check whether the file exists.
- Read the file safely.
- Decode JSON with error checking.
- Validate top-level payload shape.
- Validate schema against the supported allowlist.
- Return safe unavailable status on failure.
- Avoid uncaught exceptions in the admin UI path.

### `sw_normalize_hero_diagnostic_path(string $path): string`

Purpose:

Normalize paths for matching candidate records to diagnostic records.

Rules:

- Replace backslashes with forward slashes.
- Trim leading slashes.
- Remove URL prefix if accidentally supplied.
- Remove query string or cache busting if present.
- Preserve project-relative `images/...` path.
- Do not reduce to basename.

### `sw_index_hero_diagnostics_by_image_path(array $payload): array`

Purpose:

Build a lookup table by normalized `image_path`.

Expected behavior:

- Read records from the payload.
- Normalize each record's `image_path`.
- Store records by normalized project-relative path.
- Handle duplicate paths defensively.
- Preserve only approved fields for UI use, or call the sanitizer before exposing records.

Duplicate handling:

- First record wins.
- Duplicate paths should be recorded as a warning in adapter summary/status.
- UI must not break if duplicates exist.

Possible duplicate status:

`duplicate_record_warning`

### `sw_get_hero_diagnostic_for_image(string $imagePath): array`

Purpose:

Return sanitized diagnostics for one candidate image path.

Expected behavior:

- Normalize the candidate path.
- Look up the matching normalized diagnostic `image_path`.
- Return a sanitized display-safe diagnostic subset.
- Return unavailable `no_record_for_image` safely if no match exists.
- Never throw UI-breaking errors.

### `sw_sanitize_hero_diagnostic_record(array $record): array`

Purpose:

Convert a raw JSON record into a UI-safe record shape.

Rules:

- Do not pass raw records directly to templates.
- Expose only approved display fields.
- Keep raw landmarks, raw bounding boxes, normalized tokens, alpha geometry, and detailed score components hidden from first UI integration.
- Preserve the statement that current ROIs are not garment segmentation.

### `sw_get_hero_diagnostics_summary(): array`

Purpose:

Return safe summary metadata.

Possible summary fields:

- `available`
- `status`
- `schema`
- `image_count`
- `run_summary`
- `file_modified_time` if added later
- `message`

This summary can later support admin diagnostics status, but should not be required for manual hero selection.

## Sanitized Record Shape

Future sanitized record shape:

```json
{
  "available": true,
  "status": "ready",
  "schema": "active_layers.hero_candidates_stage2d.v1",
  "product_type": "sports_bra",
  "inferred_roi_type": "upper_body_garment",
  "path_classification": {
    "confidence": "high",
    "reason": "Classified as sports_bra from clear normalized term match."
  },
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
  "diagnostic_vocabulary": {},
  "score": {
    "final_advisory_score": null,
    "score_scope": "diagnostic_within_category_not_global_rank",
    "display_score": false
  }
}
```

`final_advisory_score` may be included in the sanitized structure, but it should not be displayed by default in the first UI integration.

Recommended first UI approach:

- Show ROI and review badges first.
- Hide advisory score initially.
- If advisory score is displayed later, label it clearly as category-scoped diagnostic information.

## Unavailable Record Shape

Future unavailable shape:

```json
{
  "available": false,
  "status": "no_record_for_image",
  "message": "Diagnostics unavailable. Manual selection remains available."
}
```

Possible unavailable or warning statuses:

- `missing_file`
- `invalid_json`
- `unsupported_schema`
- `invalid_payload_shape`
- `no_record_for_image`
- `duplicate_record_warning`

Unavailable diagnostics must not block manual hero selection.

## Path Matching Rules

Exact future matching rule:

```text
candidate.path
  -> normalize slashes
  -> trim leading slash
  -> remove query string
  -> match exactly against normalized JSON image_path
```

Do not match by basename only.

Reason:

Basename matching risks false positives when different products use the same filename, such as `01.png`.

The adapter may normalize path spelling and URL noise, but the final match should remain a project-relative path match.

## Attachment Plan For Future Stage 3E Or Later

Future plan only:

```text
admin/hero-candidates.php
  -> calls sw_enumerate_scored_candidates()
  -> loads future diagnostics adapter
  -> for each candidate.path, requests sanitized diagnostic record
  -> appends diagnostics object to candidate JSON
  -> js/admin/hero.js renders compact badges
```

Stage 3D does not modify `admin/hero-candidates.php`.

The future adapter should not be loaded from scoring, recalculation, override, rejection, or maintenance-update flows.

## First UI Badge Contract

The first UI integration should receive only compact badge-ready fields.

Recommended first badge fields:

- `diagnostics.available`
- `diagnostics.product_type`
- `diagnostics.roi.specificity`
- `diagnostics.roi.confidence`
- `diagnostics.path_classification.confidence`
- `diagnostics.review.needs_manual_review`
- `diagnostics.review.warning_count`
- `diagnostics.review.category_warning_count`

Do not include these in the first UI pass:

- Raw pose landmarks.
- Raw bounding box coordinates.
- Normalized tokens.
- Alpha geometry.
- Detailed score components.
- Raw JSON records.

## Minimal Test Plan For Future Adapter Implementation

### A. JSON File Exists And Schema Supported

Expected:

- `available: true`
- `status: ready`

### B. JSON File Missing

Expected:

- `available: false`
- `status: missing_file`
- safe message

### C. JSON Invalid

Expected:

- `available: false`
- `status: invalid_json`

### D. Schema Unsupported

Expected:

- `available: false`
- `status: unsupported_schema`

### E. Candidate Path Matches JSON `image_path` Exactly

Expected:

- Sanitized diagnostics returned.

### F. Candidate Path Has Backslashes Or Leading Slash

Expected:

- Normalized match works.

### G. Candidate Path Has Cache-Busting Query String

Expected:

- Query string is stripped.
- Normalized match works.

### H. Candidate Path Has No Diagnostic Record

Expected:

- `available: false`
- `status: no_record_for_image`
- safe message

### I. Duplicate Diagnostic `image_path` Records

Expected:

- First record wins.
- Duplicate warning appears in summary/status.
- UI does not break.

### J. Object Control Record

Expected:

- `roi.specificity: object_bbox`
- `roi.is_garment_specific: false`

### K. Body-Region Record

Expected:

- `roi.specificity: body_region_band`
- `roi.is_garment_specific: false`

## Security And Robustness Notes

Future adapter implementation should:

- Use `json_decode` with error checking.
- Avoid uncaught exceptions in the admin UI path.
- Avoid echoing raw JSON into HTML.
- Escape all diagnostic strings before display later.
- Treat diagnostics as advisory and untrusted generated data.
- Fail closed to unavailable diagnostics, not a broken page.
- Keep manual Hero Manager controls available when diagnostics fail.

## Non-Goals

Stage 3D does not include:

- Adapter implementation.
- UI integration.
- Database writes.
- Scoring changes.
- Automatic hero selection.
- Garment segmentation.
- Python execution from PHP.
- Broad Hero Manager redesign.

Future adapter work must also avoid:

- Changing `hero_override`.
- Changing `hero_rejections`.
- Changing `HeroAuthority`.
- Altering candidate ranking.
- Replacing existing Hero Manager scores.

## Recommended Stage 3E

Recommended next stage:

Implement the read-only Hero Diagnostics adapter only, without UI integration.

If Stage 3E is approved later, it should:

- Create `inc/hero/diagnostics.php`.
- Implement the read-only functions defined in this plan.
- Support only `active_layers.hero_candidates_stage2d.v1`.
- Return sanitized diagnostics and safe unavailable statuses.
- Include a tiny standalone smoke-test or temporary CLI-style check if appropriate.
- Avoid Hero Manager UI changes.
- Avoid database writes.
- Avoid scoring changes.

## Stage 3D Verdict

The first adapter implementation should be narrow, read-only, schema-validated, path-normalized, and fail-safe.

It should make diagnostic evidence available to `admin/hero-candidates.php` later, but it must not participate in scoring, recalculation, manual overrides, rejections, or maintenance updates.
