# 2026-05-27 Inactive Product Review Readiness Filters Plan

## Purpose
Inactive Product Review is a backend/admin preparation workspace for not-public products. It is intended to help administrators inspect and remediate inactive catalog records before publication readiness decisions are made. It is not a frontend-publication page and should not be treated as a mechanism that makes products publicly visible.

## Architectural principle
- Frontend/catalog views should show only products that pass publication rules.
- Admin/backend views should expose incomplete, inactive, draft, and remediation-needed products.
- Inactive does not mean invisible to admin.
- Readiness filters should separate:
  - image preparation readiness,
  - hero review readiness,
  - frontend publication readiness.

## Existing implemented foundation
The following baseline capabilities already exist and should be treated as the implementation foundation for future readiness filtering:

- `status=active|inactive|all` support in Hero Manager (`admin/hero-manager.php`).
- Status-aware shortlist behavior aligned to the same status context (`admin/hero-shortlists.php`).
- Admin sidebar entry **Inactive Product Review** linking to `admin/hero-manager.php?status=inactive` (`admin/_nav.php`).
- Inactive rows are explicitly labeled as not public in the current admin view.
- The page can already display inactive products and their current image / chosen image / thumbnails / hero-image state for review.

## Readiness states to support later
Planned readiness categories for a future allowlisted filter:

### `all`
No readiness-specific filter; retain status-scoped list only.

### `missing_images`
Products where `images`, `chosen_image`, `thumbnails_json`, and `hero_image` are all blank.

### `image_ready_not_frontend_ready`
Products with image data present but still not frontend-ready (for example missing `price`, `description`, `altText`, `ariaText`, and/or still inactive).

### `missing_hero_fields`
Products with base image population present but missing Hero Manager image-selection fields (`chosen_image` and/or `thumbnails_json`).

### `hero_review_ready`
Products with `chosen_image` and `thumbnails_json` populated while `hero_image` is blank, indicating readiness for hero review decisions.

### `frontend_incomplete`
Products missing one or more frontend-publication requirements, such as `price`, `description`, `altText`, `ariaText`, `category`/`categoryName`, or image completeness.

### `publication_ready_but_inactive`
Products that appear to satisfy required frontend fields but remain inactive, requiring a deliberate publication decision.

## Proposed future query parameters
Add a future allowlisted `readiness` parameter:

- `readiness=all`
- `readiness=missing_images`
- `readiness=image_ready_not_frontend_ready`
- `readiness=missing_hero_fields`
- `readiness=hero_review_ready`
- `readiness=frontend_incomplete`
- `readiness=publication_ready_but_inactive`

Readiness should compose with existing status filtering. Examples:

- `hero-manager.php?status=inactive&readiness=missing_images`
- `hero-manager.php?status=inactive&readiness=image_ready_not_frontend_ready`
- `hero-manager.php?status=all&readiness=frontend_incomplete`

## Proposed UI controls
Inside Inactive Product Review, add planned quick links/buttons for:

- All inactive products
- Missing images
- Image-ready, not frontend-ready
- Missing Hero Manager fields
- Ready for hero review
- Frontend incomplete
- Publication-ready but inactive

Controls should preserve `status` and `readiness` query parameters where practical so navigation and recalc/edit round-trips keep user context.

## Proposed readiness badges
Plan compact per-card badges such as:

- Missing images
- Has images
- Missing chosen image
- Missing thumbnails
- Ready for hero review
- Missing price
- Missing description
- Missing alt text
- Missing aria text
- Frontend incomplete
- Image-ready, not public-ready
- Publication candidate
- Inactive (not public)

## Proposed summary counts
Add a read-only summary panel scoped to the current `status` + `readiness` filter:

- total items in scope
- missing all image fields
- rows with images
- rows with chosen_image
- rows with thumbnails_json
- rows with no hero_image
- hero-review-ready
- frontend incomplete
- image-ready but not frontend-ready
- publication-ready but inactive

## Proposed implementation stages

### Stage 1
Add read-only readiness classification helpers in `admin/hero-manager.php` or a small admin helper file.

### Stage 2
Add readiness query parameter parsing and SQL filtering logic.

### Stage 3
Add readiness quick links/buttons and preserve query parameters across recalc/edit navigation paths.

### Stage 4
Add readiness badges on each product card.

### Stage 5
Add readiness summary counts panel.

### Stage 6
Test against current inactive dataset, especially:
- 21 image-ready inactive Ryderwear Batch 2 rows,
- 19 inactive rows missing image fields.

## Relationship to pending missing-image remediation
This plan should not block immediate missing-image remediation work. The missing-image remediation task can proceed next using current DBeaver evidence, while readiness filters remain a planned admin enhancement for a subsequent implementation cycle.

## Non-goals
This plan does **not**:

- implement readiness filters,
- update product records,
- set `is_active`,
- set `featured`,
- populate `images`/`chosen_image`/`thumbnails_json`,
- populate `price`/`description`/`altText`/`ariaText`,
- generate SQL,
- modify ProductDB,
- modify public frontend queries,
- change hero ranking/scoring logic.

## Future acceptance criteria
For the later implementation, acceptance criteria should include:

- Default Hero Manager remains active-only.
- Inactive Product Review opens with `status=inactive`.
- Readiness filters are allowlisted.
- `missing_images` finds the current 19 inactive rows with no image fields.
- `image_ready_not_frontend_ready` finds the current image-ready inactive rows.
- `hero_review_ready` finds rows with `chosen_image` + `thumbnails_json` populated and no `hero_image`.
- No database mutation occurs.
- Public catalog behavior remains unchanged.
