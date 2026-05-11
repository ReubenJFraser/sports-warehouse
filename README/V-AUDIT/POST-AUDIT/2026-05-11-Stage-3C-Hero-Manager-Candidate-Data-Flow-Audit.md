# Stage 3C - Hero Manager Candidate Data Flow Audit

Date: 2026-05-11

Status: planning and audit only. No diagnostics adapter was implemented in this stage.

Core principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3C reviews the existing Hero Manager candidate data flow so a future read-only diagnostics adapter can attach safely. The planned future adapter is still only a proposal, likely `inc/hero/diagnostics.php`. This stage does not create that file.

Accepted diagnostic schema for future planning:

`active_layers.hero_candidates_stage2d.v1`

Future generated JSON source:

`tools-dev/image-analysis/out/hero_candidates_stage1.json`

## Files Inspected

- `admin/hero-candidates.php`
- `admin/hero-manager.php`
- `admin/hero-edit.php`
- `inc/hero/candidates.php`
- `inc/hero/score.php`
- `inc/hero/authority.php`
- `js/admin/hero.js`
- `css/admin/hero.css`
- `tools/update-hero-images.php`

All requested files were present at the time of this audit.

## Data Flow Summary

The current Hero Manager candidate flow is split across a small read-only candidate endpoint, a candidate enumeration helper, the Hero Manager page shell, and JavaScript rendering.

At a high level:

1. `admin/hero-manager.php` renders each product row and an empty candidate panel.
2. `js/admin/hero.js` opens the panel and requests `admin/hero-candidates.php?item_id=...`.
3. `admin/hero-candidates.php` calls `sw_enumerate_scored_candidates()`.
4. `inc/hero/candidates.php` collects candidate image paths, adds headroom/rejection/current-hero metadata, scores the candidates, ranks them, and returns JSON.
5. `js/admin/hero.js` renders the ranked candidate rows in the open panel.

This makes `admin/hero-candidates.php` the safest first attachment surface for future read-only diagnostic badges, because it is already candidate-focused and returns JSON.

## Candidate Enumeration Owner

Candidate enumeration is owned by:

`inc/hero/candidates.php`

The main function is:

`sw_enumerate_scored_candidates(PDO $pdo, int $itemId): array`

It currently:

- Reads the product row from `item`.
- Collects candidate images from `chosen_image` and `thumbnails_json`.
- Deduplicates candidates by lowercase basename.
- Reads latest manual override data from `hero_override`.
- Reads headroom/image analysis data from `image_headroom` by `image_basename`.
- Reads rejection counts from `hero_rejections`.
- Scores candidates with `sw_score_candidate()`.
- Marks current hero, manual override, and rejected states.
- Sorts candidates by score descending.
- Adds rank and action availability flags.
- Returns JSON-ready candidate records.

This file is a read-oriented candidate assembler. It should remain focused on the current candidate contract unless a future change explicitly decides to enrich records here.

## Candidate Rendering Owner

There are two rendering surfaces:

### Hero Manager Candidate Panel

The candidate panel shell is rendered by:

`admin/hero-manager.php`

The actual candidate rows shown inside the panel are rendered by:

`js/admin/hero.js`

The JavaScript fetches candidate JSON from `admin/hero-candidates.php`, then creates rows using fields such as:

- `rank`
- `score`
- `path`
- `analysis.orientation`
- `analysis.headroom_pct`
- `analysis.face_count`
- `analysis.crop_safe`
- `status.is_current_hero`
- `status.is_manual_override`
- `status.is_rejected`

The JavaScript currently builds image URLs from:

`window.BASE_URL + "/" + candidate.path`

The generated candidate rows use classes such as `.candidate-row`, `.candidate-header`, `.candidate-image`, `.candidate-explain`, and `.candidate-actions`.

### Hero Edit Page Candidate Tiles

`admin/hero-edit.php` also renders candidate image tiles for a single product edit page. This page is closer to manual override and rejection writes, so it should not be the first diagnostics integration target.

## Scoring Owner

Current scoring is not owned by one single formula.

### Candidate Endpoint Scoring

`inc/hero/score.php` defines:

`sw_score_candidate(array $hr): float`

This is used by `inc/hero/candidates.php` for the candidate endpoint.

### Hero Manager Recalculation Scoring

`admin/hero-manager.php` contains its own local `sw_score_candidate()` helper and uses it inside `sw_recalc_hero_for_item()`.

This path can write `hero_image`, `hero_score`, `hero_ratio`, and `hero_orientation` to the `item` table when authority permits.

### Hero Edit Page Scoring

`admin/hero-edit.php` has a local candidate scoring helper used for its per-item candidate tiles.

### Maintenance Script Scoring

`tools/update-hero-images.php` has a separate batch-maintenance scoring path that reads audit data and writes hero fields when authority permits.

### Safety Finding

The diagnostic JSON must not be treated as a scoring replacement. A future adapter should display diagnostics only. It should not alter any current scoring formula, candidate ranking, recalculation behavior, or maintenance update behavior.

## Manual Authority And Override Protection

Manual authority is owned by:

`inc/hero/authority.php`

The key guard is:

`HeroAuthority::canWrite(array $item, string $source)`

Important behavior:

- Manual writes are allowed.
- Non-manual writes cannot replace an existing `hero_image`.
- Admin automation and maintenance writes are permitted only when the item has no hero image.

Manual and rejection writes are handled mainly in:

`admin/hero-edit.php`

It handles POST actions for:

- `save_override`
- `clear_override`
- `reject_auto`

These actions write to:

- `hero_override`
- `hero_rejections`

`admin/hero-manager.php` also has a recalculation path that can write item hero fields, guarded by `HeroAuthority::canWrite()`.

Future diagnostics integration must not weaken or bypass any of these protections.

## Image Path Format Findings

Candidate records currently expose image paths through the `path` field returned by `inc/hero/candidates.php`.

Those paths appear to be database/project-relative paths, usually in the form:

`images/...`

They are not full filesystem paths and not complete public URLs.

At render time, `js/admin/hero.js` turns them into browser URLs by prefixing:

`window.BASE_URL + "/" + candidate.path`

The current candidate enumeration deduplicates and looks up headroom data by basename. A future diagnostics adapter should not rely only on basename matching because the Stage 2D JSON is planned around project-relative `image_path` values.

Recommended future matching behavior:

1. Start from `candidate.path`.
2. Normalize slashes to `/`.
3. Trim leading slashes.
4. Match against project-relative diagnostic `image_path`.
5. Return no diagnostics safely if no exact normalized match exists.

Path mismatch is the main technical risk for a first diagnostics integration.

## Recommended Future Adapter Attachment Point

Recommended first attachment point:

`admin/hero-candidates.php`

Recommended future supporting adapter:

`inc/hero/diagnostics.php`

This keeps the future adapter read-only and narrowly scoped.

Recommended future flow:

1. `admin/hero-candidates.php` calls `sw_enumerate_scored_candidates()`.
2. A future diagnostics adapter loads and validates `active_layers.hero_candidates_stage2d.v1`.
3. For each candidate, the endpoint asks the adapter for a sanitized diagnostic record by normalized `candidate.path`.
4. The endpoint adds a small `diagnostics` object to each candidate record.
5. `js/admin/hero.js` renders compact badges only.

This approach keeps `inc/hero/candidates.php` focused on existing candidate enumeration and avoids coupling the core candidate function directly to a generated diagnostic artifact.

## Attachment Options And Trade-Offs

### Option A - Enrich In `admin/hero-candidates.php`

Pros:

- Safest first integration surface.
- Already read-only and candidate-focused.
- Keeps diagnostics close to the JSON response consumed by the candidate panel.
- Avoids changing core enumeration logic.

Cons:

- The endpoint becomes more than a thin pass-through.
- A future second consumer would need to share or repeat the enrichment call.

### Option B - Enrich In `inc/hero/candidates.php`

Pros:

- Centralizes enriched candidate records.
- Any future consumer of `sw_enumerate_scored_candidates()` could receive diagnostics.

Cons:

- Couples core candidate enumeration to a generated JSON artifact.
- Makes the candidate helper depend on diagnostics availability.
- Increases the risk of diagnostics being mistaken for part of candidate scoring or authority.

### Option C - Attach Directly In `admin/hero-manager.php`

Pros:

- The visible page already owns the candidate panel shell.

Cons:

- The page also contains recalculation/write behavior.
- It is a broader and riskier surface than the candidate JSON endpoint.
- It would be easier to clutter the main manager view too early.

### Option D - Attach First In `admin/hero-edit.php`

Pros:

- The edit page already shows candidate tiles and manual controls.

Cons:

- It is closer to manual override and rejection writes.
- It is not the safest first surface for read-only diagnostics.

## Recommended First UI Surface

The safest first UI surface remains:

`admin/hero-candidates.php` plus the candidate panel rendered by `js/admin/hero.js`.

First display should be compact diagnostic badges only, such as:

- Type
- ROI source or specificity
- ROI confidence
- Path classification confidence
- Manual review flag
- Warning count

The first UI pass should not display raw JSON, raw pose landmarks, full bounding boxes, or detailed score components.

## Proposed Future Data Handoff

Planning-level handoff only:

```text
candidate.path
  -> normalize to project-relative path
  -> ask read-only diagnostics adapter for a matching image_path
  -> receive sanitized display-safe diagnostic subset
  -> attach diagnostics object to candidate JSON
  -> render compact badges in the candidate panel
```

Example future candidate shape:

```json
{
  "path": "images/brands/example/product.png",
  "score": 67.4,
  "analysis": {},
  "status": {},
  "diagnostics": {
    "available": true,
    "product_type": "sports_bra",
    "path_classification_confidence": "high",
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
    }
  }
}
```

Raw diagnostic records should not be passed directly into templates.

## What Must Not Be Disturbed

Future diagnostics work must not change:

- Current scoring formulas.
- Existing candidate ranking behavior.
- `hero_override`.
- `hero_rejections`.
- `HeroAuthority`.
- Manual selection behavior.
- Recalculation rules.
- Database writes.
- Product IDs.
- Image paths stored in the database.
- Existing import/update scripts.
- Maintenance script scoring.

## Risks And Safeguards

### Path Mismatch

Risk: candidate paths may not match JSON `image_path` exactly.

Safeguard: normalize slashes, trim leading slashes, use project-relative paths, and fail safely with no diagnostics if no match exists.

### Stale JSON

Risk: diagnostics may describe an older image set.

Safeguard: expose diagnostic availability and, later, file modified time or generated timestamp. Do not treat diagnostics as source of truth.

### Unsupported Schema

Risk: future JSON versions may change shape.

Safeguard: adapter must validate schema and ignore unsupported versions safely.

### Advisory Scores Misread As Authority

Risk: `final_advisory_score` could be mistaken for current Hero Manager score or universal image quality.

Safeguard: first integration should prefer badges and warnings. If advisory score is shown later, label it as category-scoped diagnostic information.

### Raw Debug Exposure

Risk: raw coordinates, normalized tokens, or detailed components could clutter the admin UI or expose implementation detail.

Safeguard: adapter exposes only sanitized display fields.

### Garment Segmentation Overclaim

Risk: UI wording could imply true garment detection.

Safeguard: use wording such as `body-region band`, `alpha subject bounds`, and `object bounds`. Do not use `garment bbox` or `AI-selected garment`.

### Manual Authority Disruption

Risk: diagnostics could accidentally influence write paths.

Safeguard: keep adapter read-only. Do not call it from write logic. Keep manual override and rejection behavior authoritative.

## Recommended Stage 3D

Recommended next stage:

Design the first read-only adapter implementation plan.

Stage 3D should still avoid implementation unless explicitly approved. It should define the exact future adapter functions, schema allowlist, path normalization rules, sanitized record shape, failure statuses, and minimal test cases before any PHP file is created.

## Stage 3C Verdict

Accepted future attachment point:

`admin/hero-candidates.php`, supported by a future read-only adapter such as `inc/hero/diagnostics.php`.

Accepted first UI target:

Compact diagnostic badges in the existing candidate panel.

Do not attach diagnostics to scoring, recalculation, manual override, rejection, or maintenance update flows.
