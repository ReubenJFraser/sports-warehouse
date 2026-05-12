# Stage 3I - Hero Candidates Enriched Endpoint Review

Date: 2026-05-12

Status: review/audit only. No UI badge rendering was implemented.

Core principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3I reviews the enriched `admin/hero-candidates.php` endpoint output before any JavaScript or UI badge rendering is considered.

Stage 3H added read-only diagnostics enrichment to the endpoint by appending `candidate.diagnostics` to each candidate returned by `sw_enumerate_scored_candidates()`.

Accepted diagnostic schema:

`active_layers.hero_candidates_stage2d.v1`

Read-only adapter:

`inc/hero/diagnostics.php`

Generated JSON source:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

## Files Reviewed

- `admin/hero-candidates.php`
- `inc/hero/diagnostics.php` behavior as consumed by the endpoint

No JavaScript, CSS, scoring, ranking, authority, override, rejection, JSON, Python, MySQL, or import/update files were modified in this review stage.

## Endpoint Code Review

`admin/hero-candidates.php` now:

- Requires `inc/hero/diagnostics.php` safely.
- Validates `item_id` before candidate enumeration.
- Calls `sw_enumerate_scored_candidates($pdo, $itemId)` as before.
- Appends diagnostics after candidate enumeration.
- Uses `candidate.path` for diagnostics lookup.
- Uses a guarded loop only when `result.candidates` exists and is an array.
- Uses a by-reference loop safely and calls `unset($candidate)` after the loop.
- Emits enriched JSON through the adapter sanitizer rather than raw diagnostic records.

No scoring, ranking, database writes, manual override, rejection, or authority behavior is introduced by the enrichment loop.

PHP syntax check:

```powershell
& 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' -l 'admin\hero-candidates.php'
```

Result:

No syntax errors detected.

## Test Case 1 - Item 79, Booty Shorts

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=79`

Purpose:

Item with valid candidate records but no matching Stage 2D diagnostics.

Observed result:

- HTTP status: `200`
- Candidate count: `5`
- Candidates array returned normally.
- Existing candidate fields remained present:
  - `basename`
  - `path`
  - `source`
  - `score`
  - `analysis`
  - `status`
  - `rank`
  - `actions`
- New `diagnostics` field was appended.
- First candidate path: `images/brands/adidas/women/3-stripes/booty_shorts/02.png`
- First candidate rank: `1`
- First candidate score: `75`
- `diagnostics.available`: `false`
- `diagnostics.status`: `no_record_for_image`
- `diagnostics.message`: `Diagnostics unavailable. Manual selection remains available.`

Verdict:

Fallback behavior is safe. Missing diagnostics do not break candidate output.

## Test Case 2 - Item 98, 600ml Water Bottle

Request:

`http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=98`

Purpose:

Item with a candidate path that matches a Stage 2D diagnostic record.

Observed result:

- HTTP status: `200`
- Candidate count: `1`
- Matching candidate path found:
  - `images/brands/nike/other/600ml_waterbottle.png`
- Existing candidate fields remained present:
  - `basename`
  - `path`
  - `source`
  - `score`
  - `analysis`
  - `status`
  - `rank`
  - `actions`
- New `diagnostics` field was appended.
- `diagnostics.available`: `true`
- `diagnostics.status`: `ready`
- `diagnostics.schema`: `active_layers.hero_candidates_stage2d.v1`
- `diagnostics.product_type`: `object`
- `diagnostics.roi.specificity`: `object_bbox`
- `diagnostics.roi.is_garment_specific`: `false`
- `diagnostics.score.display_score`: `false`

Diagnostics keys observed:

- `available`
- `status`
- `schema`
- `product_type`
- `inferred_roi_type`
- `path_classification`
- `roi`
- `review`
- `diagnostic_vocabulary`
- `score`

Verdict:

Known image lookup works and returns sanitized diagnostics.

## Test Case 3 - Invalid Item IDs

Requests:

- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php`
- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=0`
- `http://localhost/sports-warehouse-home-page/admin/hero-candidates.php?item_id=not-a-number`

Observed result for all three:

```json
{"error":"Invalid item"}
```

HTTP status:

`200`

Verdict:

Existing invalid-item behavior remains intact. Diagnostics logic does not run for invalid item IDs in a way that causes warnings or errors.

## Additive-Only Verdict

Verdict: accepted.

The endpoint preserves the existing candidate fields and appends only:

`diagnostics`

No existing field was renamed or removed.

Fields preserved in observed output:

- `basename`
- `path`
- `source`
- `score`
- `analysis`
- `status`
- `rank`
- `actions`

## Scoring And Ranking Preservation Verdict

Verdict: accepted.

Diagnostics are appended after `sw_enumerate_scored_candidates()` returns.

The enrichment does not alter:

- Candidate score.
- Rank.
- Sort order.
- Current hero status.
- Manual override status.
- Rejection status.
- Action availability.

The existing Hero Manager score remains separate from the diagnostic `score` object, and the diagnostic score is marked with `display_score: false`.

## Safe Fallback Behavior Verdict

Verdict: accepted.

When no matching diagnostic record exists, each candidate can receive:

```json
{
  "available": false,
  "status": "no_record_for_image",
  "message": "Diagnostics unavailable. Manual selection remains available."
}
```

The endpoint continues returning normal candidate records.

Manual selection remains available.

## Sanitized Diagnostics Verdict

Verdict: accepted.

Observed diagnostics output does not expose:

- Raw pose landmark coordinates.
- Raw bounding boxes.
- `normalized_tokens`.
- Alpha geometry.
- Raw full JSON records.
- Implementation-only debug fields.

The output is limited to the sanitized adapter shape:

- Product type.
- Inferred ROI type.
- Path classification summary.
- ROI summary.
- Review warnings/counts.
- Diagnostic vocabulary.
- Advisory score object with `display_score: false`.

## Garment-Segmentation Boundary Verdict

Verdict: accepted.

Observed output does not imply:

- Garment segmentation.
- Garment masks.
- Garment-specific bounding boxes.
- Automated hero selection authority.
- AI-selected hero image.

The matching object-control record reported:

- `roi.specificity: object_bbox`
- `roi.is_garment_specific: false`

This preserves the Stage 2D boundary: diagnostics can describe body-region evidence, alpha subject bounds, or object bounds, but not true garment segmentation.

## Endpoint Robustness Verdict

Verdict: accepted for later UI badge planning.

Confirmed:

- PHP syntax check passes.
- Endpoint returns valid JSON for valid item IDs.
- Endpoint returns existing invalid-item JSON for invalid item IDs.
- No warnings or notices appeared in the tested responses.
- No database writes were introduced by this endpoint enrichment.
- No UI rendering changes exist.

## Defects Found

No blocking defects were found.

## Corrections Made

No corrections were made during Stage 3I.

## Final Verdict

The enriched `admin/hero-candidates.php` endpoint is safe for later UI badge planning.

The endpoint enrichment is additive-only, read-only, sanitized, and preserves candidate scoring/ranking behavior.

Diagnostics are available to the JSON consumer, but they are not rendered in the UI yet.

## Recommended Stage 3J

Recommended next stage:

Stage 3J: Plan compact diagnostics badge rendering in `js/admin/hero.js`.

Stage 3J should be planning-only unless explicitly approved for code. It should define badge wording, display priority, fallback behavior, and how to avoid implying garment segmentation or automated hero-selection authority.
