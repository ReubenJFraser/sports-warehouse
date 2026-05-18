# CSV → MySQL image sync drift inspection (read-only)

Date: 2026-05-18

Scope honored:
- No CSV import.
- No MySQL writes.
- No repair SQL generation.
- No image file edits.
- No Hero Manager / Hero Editor behavior changes.

## Inputs inspected
- `docs/data/SportWarehouse_ProductDB.csv` (committed source CSV)
- `docs/operations/2026-05-18-csv-to-mysql-image-sync-drift-inspection.md` (prior plan)
- code/schema references for image fields in `item` and `hero_override`

## CSV structure and immediate findings

CSV row count: **120**.

Relevant columns confirmed:
- `brand`
- `gender`
- `itemName`
- `images`
- `thumbnails_json`
- `videos`
- `db_itemId`

Observed path-shape drift risk in CSV itself:
- Mix of canonicalized paths and legacy/non-canonical rows.
- `images` starts with `images/brands/` for **54/120** rows.
- Therefore **66/120** rows are not currently in that same canonical form (often blank or legacy shape).

`db_itemId` coverage:
- Blank `db_itemId`: **66** rows.
- Brand distribution for blank IDs:
  - Ryderwear: **62**
  - Adidas: **4**

Interpretation:
- CSV appears to combine current mapped products and future/staging products.
- Ryderwear rows with blank `db_itemId` should be treated as **CSV-only candidates**, not live DB mismatches.

## MySQL field assumptions confirmed in code

The app uses/assumes these live fields:
- `item.hero_image` (primary rendered image in product cards/catalog)
- `item.thumbnails_json` (image candidate list)
- `item.chosen_image` (candidate/legacy chosen pointer)
- `hero_override.chosen_image` (runtime/editor override)

Notable behavior implications:
- Product grid intentionally prioritizes `hero_image` and then pulls gallery candidates from `thumbnails_json`.
- Hero candidate building pulls from `chosen_image`, `thumbnails_json`, and `hero_override.chosen_image`.
- `hero_override.chosen_image` is runtime/editor decision state, not import-source truth.

## Can we prove live MySQL is stale from this run?

**Not yet conclusively** from repository-only inspection, because no live MySQL snapshot was available in this environment.

However, strong indicators support pausing SQL repairs until a CSV↔DB read-only diff is run:
- CSV includes path patterns like `images/brands/adidas/kids/boys/...` and `images/brands/asos/women/...` that may be more canonical than older DB rows/report heuristics.
- Existing integrity suggestions that assume only a missing `/brands/` segment are likely too narrow; CSV indicates deeper folder/file naming variation is possible.

## Safe matching strategy (CSV ↔ MySQL)

Use a two-tier match, read-only first:

1. Primary key: normalized `brand + itemName`
   - `LOWER(TRIM(brand))`
   - itemName normalization similar to existing repo matching strategy:
     - lowercase
     - trim
     - remove non-alphanumeric
     - optional audience token harmonization (`mens/womens/girls/boys`) if needed for tie-breaks

2. Secondary clue only: `db_itemId`
   - use to raise confidence where present and consistent,
   - but do **not** assume direct safety mapping unless verified against live `item.itemId`.

3. Compare media fields for matched rows:
   - CSV `thumbnails_json` (semicolon list) vs MySQL `item.thumbnails_json` (normalize delimiter/whitespace/order before set comparison)
   - CSV `images` folder path vs MySQL `item.chosen_image` / first thumbnail-derived folder for drift classification

4. Classify rows:
   - `shared_match` (safe comparable)
   - `csv_only_future` (e.g., Ryderwear blank `db_itemId`)
   - `mysql_only_legacy`
   - `ambiguous_match` (manual review)

## Field update policy (future implementation)

Fields that can be CSV-driven (after review):
- `item.thumbnails_json`
- potentially a CSV-derived canonical base path feeding `item.images`/supporting fields where still used
- `item.videos` where present and validated

Fields to protect from direct CSV overwrite:
- `item.hero_image` (curated primary render state)
- `item.chosen_image` (selection-state/legacy compatibility)
- `hero_override.chosen_image` (runtime/editor override state)

Reason: CSV has no direct authoritative hero-selection column; hero-related fields represent application/editor decisions.

## Recommendation for next implementation step

Best next step: **local CLI read-only comparison script** (not SQL repair yet).

Why this first:
- It can normalize CSV and DB representations consistently.
- It can explicitly separate `shared` vs `future CSV-only` vs `ambiguous` rows.
- It can produce a review artifact (markdown/csv) that the existing Image Integrity report can later consume.

After that, extend Image Integrity to ingest the reconciliation artifact and gate any SQL generation behind explicit human approval.

## Direct answers requested

1. Does committed CSV appear newer/more canonical than current MySQL image paths?
   - **Likely yes for a substantial subset**, based on explicit `images/brands/...` structures and modern folder taxonomy in many rows; but full proof requires live DB read-only diff.

2. Should Image Integrity suggested SQL remain paused?
   - **Yes. Keep paused** until CSV↔MySQL read-only reconciliation completes.

3. Safest comparison strategy?
   - Normalize `brand + itemName` first, treat `db_itemId` as confidence clue only, then compare normalized thumbnail/image-path sets with ambiguity buckets.

4. Which fields can be updated later vs protected?
   - Updatable candidates: `item.thumbnails_json`, validated media-list fields.
   - Protected: `item.hero_image`, `item.chosen_image`, `hero_override.chosen_image`.

5. Next Codex implementation task?
   - Build a **read-only CLI reconciliation report generator** that outputs per-row drift buckets and confidence before any SQL repair step.
