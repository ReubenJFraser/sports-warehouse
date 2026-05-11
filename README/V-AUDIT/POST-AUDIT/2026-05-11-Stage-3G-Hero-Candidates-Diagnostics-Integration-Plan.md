# Stage 3G - Hero Candidates Diagnostics Integration Plan

Date: 2026-05-11

Status: planning only. No endpoint integration is implemented in this stage.

Core principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3G plans the minimal future read-only integration between:

- `admin/hero-candidates.php`
- `inc/hero/diagnostics.php`

The goal is to define how the candidate endpoint can later append sanitized diagnostics to each candidate JSON record without changing scoring, ranking, manual overrides, rejections, authority logic, database writes, or UI rendering.

Accepted diagnostic schema:

`active_layers.hero_candidates_stage2d.v1`

Generated JSON source:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

## Stage 3G Status

- Stage 3G is planning only.
- No endpoint integration is implemented in this stage.
- No UI badges are rendered in this stage.
- No JavaScript changes are made in this stage.
- No database writes are introduced.
- Scoring, ranking, overrides, rejections, and authority logic remain untouched.

## Integration Boundary

The future integration should only:

- Include or require `inc/hero/diagnostics.php` inside `admin/hero-candidates.php`.
- Call `sw_enumerate_scored_candidates()` exactly as before.
- Iterate over the returned candidate records.
- For each `candidate.path`, request a sanitized diagnostic record from `sw_get_hero_diagnostic_for_image()`.
- Append that sanitized record under a new candidate key, probably `diagnostics`.
- Return the enriched candidate JSON.

The future integration must not:

- Alter candidate scoring.
- Alter candidate ranking.
- Alter candidate filtering.
- Alter current hero status.
- Alter manual override status.
- Alter rejection status.
- Write to MySQL.
- Call Python.
- Regenerate JSON.
- Expose raw diagnostic records.

## Proposed Future Candidate JSON Shape

Existing candidate fields should remain unchanged.

Add only a new `diagnostics` key.

Example available diagnostics:

```json
{
  "rank": 1,
  "score": 67.4,
  "path": "images/brands/stax/example/01.png",
  "analysis": {},
  "status": {},
  "actions": {},
  "diagnostics": {
    "available": true,
    "status": "ready",
    "schema": "active_layers.hero_candidates_stage2d.v1",
    "product_type": "sports_bra",
    "inferred_roi_type": "upper_body_garment",
    "path_classification": {
      "confidence": "high",
      "reason": "..."
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
      "category_specific_warnings": [],
      "warning_count": 0,
      "category_warning_count": 0
    },
    "diagnostic_vocabulary": {},
    "score": {
      "final_advisory_score": null,
      "score_scope": "diagnostic_within_category_not_global_rank",
      "display_score": false
    }
  }
}
```

Example unavailable diagnostics:

```json
{
  "diagnostics": {
    "available": false,
    "status": "no_record_for_image",
    "message": "Diagnostics unavailable. Manual selection remains available."
  }
}
```

## Field Preservation Rule

Existing candidate JSON fields must remain backward-compatible.

Future integration must not rename or remove fields used by `js/admin/hero.js`, including:

- `rank`
- `score`
- `path`
- `analysis`
- `status`
- `actions`, if present

Diagnostics should be appended as additive metadata only.

## Future Endpoint Flow

Future flow only:

```text
admin/hero-candidates.php
  -> require inc/hero/candidates.php
  -> require inc/hero/diagnostics.php
  -> call sw_enumerate_scored_candidates($pdo, $itemId)
  -> foreach candidate:
       diagnostics = sw_get_hero_diagnostic_for_image(candidate.path)
       candidate.diagnostics = diagnostics
  -> return JSON
```

Stage 3G does not implement this flow.

## Error And Fallback Behavior

If diagnostics cannot be loaded because of:

- Missing file.
- Invalid JSON.
- Unsupported schema.
- Invalid payload shape.
- No matching record for candidate path.

Then the endpoint should still return candidates normally.

Each affected candidate should receive:

```json
{
  "available": false,
  "status": "no_record_for_image",
  "message": "Diagnostics unavailable. Manual selection remains available."
}
```

Or the equivalent adapter-provided unavailable status.

The endpoint must not fail hard because diagnostics are unavailable.

Manual selection must remain available.

## JavaScript And UI Implications

Stage 3G does not change JavaScript.

In a later UI stage, `js/admin/hero.js` could render compact badges from `candidate.diagnostics`.

Possible future badges:

- Type.
- ROI.
- ROI confidence.
- Path classification confidence.
- Manual review flag.
- Warning count.

No badges should be implemented in Stage 3G.

## Score Display Caution

The first future endpoint integration may include the sanitized `score` object from the adapter, but the UI should not display `final_advisory_score` by default.

Reason:

`final_advisory_score` is diagnostic and category-scoped. It is not a universal Hero Manager ranking score.

The existing Hero Manager score and candidate ranking remain authoritative for the current UI.

## Path Matching Expectations

Future matching should use:

```text
candidate.path
  -> sw_normalize_hero_diagnostic_path()
  -> exact match against normalized JSON image_path
```

Do not match by basename only.

Reason:

Many image files are named `01.png`, `02.png`, and similar. Basename-only matching risks false positives across products.

## Risk Assessment

### Risks

- Diagnostics unavailable.
- `candidate.path` does not match JSON `image_path`.
- Stale JSON.
- Unsupported schema.
- UI misreads diagnostics as authority.
- `final_advisory_score` is confused with the existing Hero Manager score.
- Raw debug fields are accidentally exposed.
- UI wording overclaims garment segmentation.
- Endpoint response shape accidentally breaks existing JavaScript.

### Safeguards

- Add only an append-only `diagnostics` object.
- Use sanitized adapter output only.
- Return `diagnostics.available: false` when diagnostics are unavailable.
- Do not change ranking or scoring.
- Do not write to the database.
- Do not change JavaScript until a later UI stage.
- Use compact badge wording only later.
- Avoid garment-specific language.
- Keep current candidate fields unchanged.

## Minimal Future Implementation Diff Outline

Conceptual diff only:

- Add `require_once` for `inc/hero/diagnostics.php`.
- Keep existing `sw_enumerate_scored_candidates($pdo, $itemId)` call.
- After candidate generation, loop through candidates.
- If `candidate.path` exists, append `candidate.diagnostics = sw_get_hero_diagnostic_for_image(candidate.path)`.
- If `candidate.path` is missing, append adapter-style unavailable diagnostics.
- Return JSON as before.

No implementation code is written in Stage 3G.

## Acceptance Criteria For Future Stage 3H

A future Stage 3H implementation should prove:

- Only `admin/hero-candidates.php` changes.
- Endpoint still returns candidate records normally.
- Existing JavaScript does not break.
- Each candidate has a `diagnostics` key.
- Known image returns `diagnostics.available: true`.
- Missing diagnostic returns `diagnostics.available: false`.
- Candidate score and rank order are unchanged.
- No database writes are introduced.
- No UI rendering changes are made.
- No raw JSON or debug fields are exposed.

## Recommended Stage 3H

Recommended next stage:

Implement minimal read-only diagnostics enrichment in `admin/hero-candidates.php` only, without JavaScript or UI rendering.

Stage 3H should be tightly scoped. It should not render badges yet, should not change scoring or ranking, and should not change any database write path.

## Stage 3G Verdict

The future integration pattern should be append-only:

`candidate.path` -> sanitized adapter lookup -> `candidate.diagnostics`

This keeps diagnostics available for later UI work while preserving the current Hero Manager candidate flow.
