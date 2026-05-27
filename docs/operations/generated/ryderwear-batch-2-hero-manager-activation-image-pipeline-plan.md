# Ryderwear Batch 2 Hero Manager Activation + Image Pipeline Plan

## Scope and constraints
- Diagnosis and implementation planning only.
- No SQL execution, no MySQL mutation, no ProductDB mutation, no runtime/admin/frontend code changes, and no image-file edits.

## Evidence snapshot
- Prior generated SQL updated only `item.images` for the kept-safe Ryderwear Batch 2 set (21 rows), matched on `item.external_item_id`. It did **not** set `chosen_image`, `thumbnails_json`, `hero_image`, or activation flags.  
- SQL summary confirms: 21 kept rows, SQL generated-only, and update-target column `item.images`.  
- Reported DBeaver checks for the 21 rows:
  - `active_rows = 0`
  - `inactive_rows = 21`
  - `rows_with_images = 21`
  - `rows_with_chosen_image = 0`
  - `rows_with_hero_image = 0`
  - `rows_with_thumbnails_json = 0`

## 1) Why the 21 Batch 2 rows do not currently appear in Hero Manager

### Confirmed primary visibility blocker: `is_active = 0`
`admin/hero-manager.php` fetches rows using `WHERE i.is_active = 1`. Inactive rows are excluded before rendering, and the UI shows “No active items found” when none match.  

**Conclusion:** the direct reason these 21 rows do not appear in Hero Manager is the active filter (`is_active = 1`) combined with all 21 rows being inactive.

### Does `featured` affect Hero Manager visibility?
No direct dependency found in Hero Manager query flow. Hero Manager fetch/order logic uses `is_active`, `brand`, and `itemName` and does not filter by `featured`. `featured` appears in catalog sorting relevance logic, not Hero Manager row eligibility.

**Conclusion:** `featured` is not a Hero Manager visibility gate for these rows.

## 2) Why rows would still lack useful Hero Manager images even if activated

### Display source in Hero Manager
Hero Manager display baseline is `COALESCE(i.hero_image, i.chosen_image)`.

If both are blank, the computed/current hero slots have no usable image baseline.

### Recalculation candidate source
Automatic recalculation (`sw_recalc_hero_for_item`) builds candidates from:
1. `chosen_image`
2. `thumbnails_json` (semicolon-split)

If both are empty, candidate list is empty and recalc returns null (cannot compute hero).

### Is `item.images` used directly by Hero Manager candidates?
Not in the recalc/candidate builder paths reviewed:
- `admin/hero-manager.php` recalc candidate collector uses only `chosen_image` + `thumbnails_json`.
- `inc/hero/candidates.php` enumerator uses only `chosen_image` + `thumbnails_json` (plus override/hero status metadata).
- `admin/hero-edit.php` candidate list generation uses only `chosen_image` + `thumbnails_json`.

**Conclusion:** even after activation, these rows remain “image-unready” for Hero Manager workflows unless `chosen_image` and/or `thumbnails_json` are populated from the existing `images` field.

## 3) Fields that should be populated for Hero Manager workflow readiness

Based on current code behavior, the most compatible next data-prep step (review-only plan, no execution here) is:

1. **`chosen_image`**: set to first image path parsed from `images` (first semicolon token, trimmed, non-empty).
2. **`thumbnails_json`**: set to normalized image list derived from `images` (existing app conventions use semicolon-delimited paths in many places despite field name suggesting JSON).
3. **`hero_image`**: leave blank initially so recalculation/manual selection can authoritatively set it.
4. **`is_active`**: do not set until readiness checks pass.
5. **`featured`**: leave unchanged unless a separate frontend publication/sorting decision is made.

## 4) Is activation safe?

Activation should be treated as conditional, not assumed.

Before any activation proposal, verify the 21-row set has required storefront/readiness fields populated (or acceptable defaults defined):
- `itemName`
- `brand`
- `price` (and sale-price policy as applicable)
- category identity (`categoryId` and/or `categoryName` depending query path)
- `description`
- `altText`
- `ariaText`
- `images`
- governance/publishing controls used by your workflows (`is_active`, and any release checklist flags)

Rationale:
- Hero Manager visibility requires `is_active = 1`.
- Catalog/frontend query logic uses broader product fields; activation without metadata readiness risks exposing incomplete cards.

## 5) Recommended staged implementation sequence (review-only)

### Stage A — Build review-only SQL/data-prep plan for image pipeline fields
- Generate **review-only** SQL script (do not execute) to populate for the 21 external IDs:
  - `chosen_image` from first `images` token.
  - `thumbnails_json` from normalized full `images` list.
- Include before/after SELECT previews in that plan.

### Stage B — Build review-only activation plan (conditional)
- Only if required readiness checks pass, generate **review-only** activation SQL plan for `is_active = 1` for the same 21 rows.
- Keep this as a separate script/review section to preserve rollback clarity and change isolation.

### Stage C — Hero selection workflow
- After `chosen_image` + `thumbnails_json` are populated and rows are active, run Hero Manager recalc and/or manual hero selection workflow.
- This is where `hero_image`, `hero_score`, `hero_ratio`, `hero_orientation` become populated via existing tooling.

### Stage D — Verification
- Verify the 21 rows now appear in Hero Manager.
- Verify candidate trays populate and at least one valid image renders per row.
- Verify post-recalc `hero_image` coverage and missing-image counts.

### Stage E — Publication policy decision
- Independently decide whether `featured` or other merchandising controls should change for storefront ranking/relevance.
- Keep this decision separate from Hero Manager activation mechanics.

## 6) Required read-only SQL/data checks before any change

Use read-only checks only (SELECT/file checks). Suggested checklist:

1. **Batch membership / activation state**
   - Count active vs inactive within the 21 external IDs.
2. **Image-field population status**
   - Count non-empty `images`, `chosen_image`, `thumbnails_json`, `hero_image` for the same set.
3. **Readiness field gaps**
   - List rows with missing/blank: `itemName`, `brand`, `price`, `categoryId`/`categoryName`, `description`, `altText`, `ariaText`.
4. **First-image filesystem existence check** (if filesystem available)
   - Parse first token from `images`; verify file exists/readable per path.
5. **All referenced image path existence check** (if feasible)
   - Expand semicolon list; report missing files by item/external ID.
6. **Path normalization sanity checks**
   - Confirm paths are web-relative forms expected by helper/rendering logic (e.g., `images/...`, no malformed prefixes).

## 7) Non-goals (explicit)
This task/report does **not**:
- update `is_active`
- update `chosen_image`
- update `thumbnails_json`
- update `hero_image`
- execute SQL
- modify ProductDB
- modify runtime/admin/frontend code
- modify image files

## Final diagnosis summary
Two independent blockers explain the current result:
1. **Visibility blocker:** all 21 rows are inactive and Hero Manager strictly filters to `is_active = 1`.
2. **Image-pipeline blocker:** Hero Manager workflows derive display/candidates from `hero_image` / `chosen_image` / `thumbnails_json`, while the Batch 2 SQL populated only `item.images`.

Therefore, activation alone is insufficient; a staged data-prep plan to map `images -> chosen_image/thumbnails_json` is required before Hero Manager can be effective for these rows.
