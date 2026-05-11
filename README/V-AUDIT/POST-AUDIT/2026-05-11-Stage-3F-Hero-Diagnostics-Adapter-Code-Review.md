# Stage 3F - Hero Diagnostics Adapter Code Review

Date: 2026-05-11

Status: review/audit only. No Hero Manager integration was performed.

Core principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3F reviews the read-only Hero Diagnostics adapter created in Stage 3E before any Hero Manager integration occurs.

Reviewed file:

`inc/hero/diagnostics.php`

Accepted diagnostic schema:

`active_layers.hero_candidates_stage2d.v1`

Generated JSON source:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

## Files Reviewed

- `inc/hero/diagnostics.php`

No other implementation files were changed during this review stage.

## Read-Only Safety Verdict

Verdict: accepted.

The adapter does not:

- Write to MySQL.
- Update products.
- Update `hero_image`.
- Update `hero_score`.
- Update `hero_override`.
- Update `hero_rejections`.
- Call Python.
- Regenerate JSON.
- Mutate the JSON file.
- Alter candidate ranking.
- Alter current Hero Manager scoring.
- Block manual selection.

The adapter only:

- Locates the generated JSON file.
- Checks whether the file exists.
- Reads and parses JSON.
- Validates schema and payload shape.
- Normalizes image paths.
- Indexes records by normalized `image_path`.
- Returns sanitized display-safe diagnostics.
- Returns safe unavailable statuses.

## Safe Include Behavior Verdict

Verdict: accepted.

`inc/hero/diagnostics.php`:

- Does not echo output at include time.
- Does not execute smoke tests at include time.
- Does not assume a web request context.
- Does not require a database connection.
- Does not create side effects when included.
- Uses safe return objects for ordinary missing or invalid diagnostics cases.

No UI-breaking behavior was found for the ordinary cases reviewed.

## Schema Validation Verdict

Verdict: accepted.

The adapter allowlists:

`active_layers.hero_candidates_stage2d.v1`

The payload loader handles:

- Missing file with `missing_file`.
- Invalid JSON with `invalid_json`.
- Missing or empty schema with `invalid_payload_shape`.
- Unsupported schema with `unsupported_schema`.
- Missing or malformed `records` with `invalid_payload_shape`.

Unsupported schema behavior was reviewed in code but not smoke-tested against the real JSON, because that would require modifying the accepted diagnostic artifact.

Malformed individual records are skipped during indexing when they do not contain a usable string `image_path`.

## JSON Path Resolution Verdict

Verdict: accepted.

`sw_hero_diagnostics_json_path()` resolves the JSON from the adapter file location using:

`dirname(__DIR__, 2)`

From `inc/hero/diagnostics.php`, this resolves to the project root, then appends:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

This avoids fragile current-working-directory assumptions.

Missing file behavior returns `missing_file` safely.

## Path Normalization Verdict

Verdict: accepted.

`sw_normalize_hero_diagnostic_path()` handles:

- Forward slashes.
- Backslashes.
- Leading slashes.
- Query strings.
- Fragments.
- Full local Windows paths.
- Accidental public URLs.
- Project-relative `images/...` paths.

It preserves the full project-relative image path and does not reduce matching to basename only.

This is important because many product images use filenames such as `01.png` and `02.png`.

Smoke-tested examples included:

- `/images/brands/nike/other/600ml_waterbottle.png?v=123#hero`
- `https://example.com/images/brands/nike/other/600ml_waterbottle.png?v=123`
- a Windows-style local path with a cache query during Stage 3E

All known-image variations resolved to the expected diagnostic record.

## Record Indexing Verdict

Verdict: accepted.

`sw_index_hero_diagnostics_by_image_path()`:

- Iterates `payload.records`.
- Requires a string `image_path` before indexing.
- Normalizes each `image_path`.
- Keys records by normalized project-relative path.
- Uses first-record-wins duplicate behavior.
- Collects duplicate normalized paths in a `duplicates` array.
- Does not throw UI-breaking errors for malformed records.

Duplicate behavior is safe for future UI integration planning. If duplicates occur, the summary can report `duplicate_record_warning`.

## Sanitized Output Verdict

Verdict: accepted.

`sw_sanitize_hero_diagnostic_record()` returns display-safe fields only:

- `available`
- `status`
- `schema`
- `product_type`
- `inferred_roi_type`
- `path_classification.confidence`
- `path_classification.reason`
- `roi.specificity`
- `roi.confidence`
- `roi.is_body_region_specific`
- `roi.is_garment_specific`
- `review.needs_manual_review`
- `review.warnings`
- `review.category_specific_warnings`
- warning counts
- `diagnostic_vocabulary`
- advisory score object with `display_score: false`

It does not expose:

- Raw pose landmark coordinates.
- Raw bounding boxes.
- `normalized_tokens`.
- Alpha geometry.
- Full raw JSON records.
- Implementation-only debug fields.

The adapter intentionally keeps `final_advisory_score` inside a score object with `display_score: false`, so first UI integration can hide it by default.

## Garment-Segmentation Claim Verdict

Verdict: accepted.

The adapter does not introduce wording that implies:

- Garment segmentation.
- Garment masks.
- Garment-specific bounding boxes.
- AI-selected hero images.
- Automated selection authority.

The sanitized `roi.is_garment_specific` value is taken from the accepted Stage 2D record. Current Stage 2D records are expected to keep this false because the pipeline identifies body-region evidence, alpha subject bounds, and object bounds, not true garment segmentation.

Future UI wording must continue to use terms such as:

- `body_region_band`
- `alpha_subject_bbox`
- `object_bbox`

It must avoid terms such as `garment bbox` or `AI-selected hero`.

## Failure Behavior Verdict

Verdict: accepted.

Unavailable diagnostic responses are safe and consistent.

Supported statuses include:

- `missing_file`
- `invalid_json`
- `unsupported_schema`
- `invalid_payload_shape`
- `no_record_for_image`

The user-facing message remains:

`Diagnostics unavailable. Manual selection remains available.`

Missing-image lookup failed safely with `no_record_for_image`.

## Summary Output Verdict

Verdict: accepted.

`sw_get_hero_diagnostics_summary()` returns safe metadata:

- `available`
- `status`
- `message`
- `schema`
- `image_count`
- `run_summary`
- `file_modified_time`

It does not expose raw records.

It can report duplicate paths through `duplicate_record_warning` if duplicate normalized paths exist.

## Smoke Tests Run

Syntax check:

```powershell
& 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' -l 'inc\hero\diagnostics.php'
```

Result:

No syntax errors detected.

Summary check:

```powershell
require 'inc/hero/diagnostics.php';
sw_get_hero_diagnostics_summary();
```

Result:

- `available: true`
- `status: ready`
- `schema: active_layers.hero_candidates_stage2d.v1`
- `image_count: 33`

Known image lookup:

```powershell
sw_get_hero_diagnostic_for_image('/images/brands/nike/other/600ml_waterbottle.png?v=123#hero');
```

Result:

- `available: true`
- `product_type: object`
- `roi.specificity: object_bbox`
- `roi.is_garment_specific: false`

Public URL normalization:

```powershell
sw_get_hero_diagnostic_for_image('https://example.com/images/brands/nike/other/600ml_waterbottle.png?v=123');
```

Result:

- `available: true`
- matched the same water bottle diagnostic record

Missing image lookup:

```powershell
sw_get_hero_diagnostic_for_image('images/brands/not-real/missing.png');
```

Result:

- `available: false`
- `status: no_record_for_image`
- message: `Diagnostics unavailable. Manual selection remains available.`

Unsupported schema was not smoke-tested against the real JSON because doing so would require modifying the accepted generated diagnostic artifact. The code path was reviewed and fails safely.

## Defects Found

No blocking defects were found.

## Corrections Made

No code corrections were made during Stage 3F.

## Integration Readiness Verdict

Verdict: accepted for later UI integration planning.

`inc/hero/diagnostics.php` is safe enough to use as the basis for the next planning stage. It should not be integrated into the Hero Manager UI until the integration surface is planned and reviewed.

## Recommended Stage 3G

Recommended next stage:

Stage 3G: Plan minimal read-only integration into `admin/hero-candidates.php`.

Stage 3G should define how diagnostics would be appended to candidate JSON before changing the endpoint. It should remain planning-only or very tightly scoped, and it must preserve the same boundary:

Automation suggests. Manual Hero Manager selections win.
