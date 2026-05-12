# Stage 3P - Product List Top-Three UI Plan

Date: 2026-05-12

Status: planning and documentation only. No UI rendering was implemented.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3P plans how the Hero Manager product list or filtered product list should eventually display the top three recommended hero candidates per product.

The UI goal is to let the administrator scan many products quickly without opening every full image set.

Current limitation:

`shortlist_basis: "legacy_rank_placeholder"`

The current shortlist is not yet criteria-aware AI ranking. Any UI must say that honestly.

## Stage 3P Status

- Stage 3P is planning/documentation only.
- No UI rendering is implemented.
- No JavaScript is changed.
- No CSS is changed.
- No endpoint behavior is changed.
- No scoring/ranking formula is changed.
- No database writes are introduced.
- Manual selection remains final.

## Intended Normal Workflow

1. Administrator opens the Hero Manager product list or filtered product list.
2. Each product row or card displays:
   - product identity
   - current selected hero
   - top three recommended candidates
   - current hero status
   - shortlist status or warning if needed
3. Administrator scrolls through products.
4. Administrator only opens the challenge/review view if the top-three shortlist looks wrong, incomplete, or inconsistent with the intended presentation.

The normal product-list UI should be fast, visual, and low-text.

## Challenge Workflow Entry Point

The full candidate set should remain available through a clear challenge path.

Possible action labels:

- Review all images
- Challenge shortlist
- View all candidates
- Open Hero Editor

Recommended safest first label:

`Review all images`

Reason:

It is clear, non-combative, and does not imply that the AI is final authority. `Challenge shortlist` is useful conceptually, but may feel too strong for first UI wording.

The challenge path should eventually show:

- all candidates
- existing ranks
- diagnostics
- warnings
- manual selection controls
- rejection status
- current hero status
- future criteria/profile controls

## Possible UI Surfaces

### A. Existing `hero-manager.php` Product List

Pros:

- Users already review hero state there.
- It already has product rows and candidate panels.
- It is the natural place for a product-list shortlist.

Cons:

- Current page may become heavy if each row fetches shortlist data separately.
- The layout may need careful density control.
- It currently uses the single-item candidate endpoint per expanded panel.

### B. New Dedicated Shortlist/List View

Pros:

- Clean slate for scan-first workflow.
- Can focus entirely on top-three review.
- Easier to design around batch data.

Cons:

- Adds another admin surface.
- Could duplicate Hero Manager responsibilities too early.

### C. Filtered Product-List View

Pros:

- Best fit for operational review.
- Could show only products needing attention.
- Could support category/profile-specific review.

Cons:

- Needs filter design and likely batch endpoint support.

### D. Existing `hero-edit.php` Item-Specific View

Pros:

- Existing manual controls live there.
- Good for challenge/review mode.

Cons:

- Not suitable for scanning many products.
- Should not be the first product-list shortlist surface.

### E. Future Batch Shortlist Endpoint-Driven Screen

Pros:

- Best architectural fit for top-three across many products.
- Avoids one request per product row.
- Supports filtered product-list workflows.

Cons:

- Requires endpoint planning before UI implementation.

## Recommended First UI Surface

Recommended surface:

Plan for the existing `hero-manager.php` product list, but do not implement full product-list UI until a batch shortlist endpoint is planned.

Reason:

The workflow belongs in Hero Manager, but the current opt-in shortlist endpoint is single-item. A true product-list UI needs top-three data across many products, and one request per row could become inefficient.

## Batch Endpoint Consideration

The product-list UI likely needs a batch shortlist endpoint before implementation.

Current endpoint:

`admin/hero-candidates.php?item_id=98&include_shortlist=1`

This is useful for one product, but the product list may need shortlist data for dozens of products.

Calling the single-item endpoint once per product risks:

- slow loading
- many database queries
- difficult error handling
- noisy browser/network behavior
- poor admin UX on filtered lists

Possible future batch endpoints:

- `admin/hero-shortlists.php`
- `admin/hero-candidates.php?include_shortlists=1`

Recommendation:

Plan a batch shortlist endpoint before implementing the product-list UI.

A small single-item UI experiment could happen later, but it would not solve the main product-list workflow.

## Product Row/Card Layout Concept

Possible row layout:

```text
[Product info] [Current Hero] [#1] [#2] [#3] [Status] [Review all images]
```

Each product should show:

- product name
- brand
- item ID
- current hero thumbnail
- recommended #1 thumbnail
- recommended #2 thumbnail
- recommended #3 thumbnail
- shortlist status
- challenge/review action

Current hero should be visually separate from the top three.

Reason:

If `current_hero_outside_top_three` is true, the UI must show that clearly without forcing the current hero into the shortlist.

## Top-Three Thumbnail Treatment

Recommended labels:

- Recommended #1
- Alternative #2
- Alternative #3
- Temporary shortlist
- Ranked by existing Hero Manager score

Visual treatment:

- rank badge in the corner
- subtle outline around #1
- current hero indicator if one of the top three is already selected
- warning marker if diagnostics or criteria profile are missing

Avoid wording:

- AI winner
- AI-selected final hero
- Best image guaranteed
- Criteria-aware winner

The UI should feel confident but not overclaim.

## Current Hero Outside Top Three

If:

`current_hero.current_hero_outside_top_three === true`

Show a neutral warning, such as:

- Current selected hero differs from current shortlist.
- Current hero is outside the temporary top-three shortlist.
- Review recommended.

Do not:

- automatically change the hero
- force the current hero into the top three
- describe the current hero as wrong

This is a review signal, not a replacement command.

## Shortlist Status Display

Potential statuses:

- `ready`
- `partial`
- `unavailable`
- `legacy_placeholder`
- `diagnostics_missing`
- `criteria_profile_missing`

Stage 3N currently returns:

- `ready`
- `partial`
- `unavailable`

and always includes:

`shortlist_basis: "legacy_rank_placeholder"`

Suggested status copy:

- Temporary shortlist using existing Hero Manager rank.
- Criteria-aware ranking not yet active.
- Diagnostics unavailable for some candidates.
- Fewer than three candidates available.
- No hero candidates available.

The legacy-rank limitation should be visible, but not so loud that it overwhelms scanning.

## Diagnostics Display In Product List

Product-list rows should show minimal diagnostic information only.

Possible compact indicators:

- diagnostics available / unavailable
- ROI type: `object_bbox`, `body_region_band`, `alpha_subject_bbox`
- warning count
- manual review flag

Do not show in product-list scan view:

- raw score components
- raw pose details
- raw face details
- raw bounding boxes
- normalized tokens
- `final_advisory_score` by default

Diagnostics should support trust and review, not become the main visual layer.

## Recommendation Reason Display

Recommendation reasons should not be always visible in the product-list scan view.

Recommended approach:

- short labels visible
- detailed `recommendation_reason` in tooltip, disclosure, or challenge view

Reason:

The normal scan view should stay fast and visual. Long explanations belong in challenge/review context.

## Criteria Profile Display

Potential profile display:

- Intended profile: `object_only`
- Intended profile: `body_region_first`
- Intended profile: `product_first`
- Profile pending

Because criteria-aware ranking is not yet implemented, use careful wording:

- Intended profile: `body_region_first`
- Criteria-aware ranking not yet active
- Temporary shortlist basis

Avoid wording that implies the profile actively re-ranked candidates before that is true.

## Administrator Actions

Possible future row-level actions:

- Keep current hero
- Review all images
- Challenge shortlist
- Select #1
- Select #2
- Select #3
- Adjust criteria later

Recommended conservative first UI:

Show the shortlist and a `Review all images` action only.

Do not add one-click `Accept #1` or `Select #2/#3` in the first UI pass.

Reason:

Manual selection currently lives in the existing Hero Editor/candidate review flow. Adding one-click selection would introduce higher authority and write-path risks.

## Visual Hierarchy

Recommended hierarchy:

1. Product identity remains primary.
2. Current hero and top-three thumbnails are visually prominent.
3. Status/warning indicators are visible but calm.
4. Diagnostics are secondary.
5. Legacy-rank limitation is clear but not overwhelming.

The scan view should answer quickly:

- What product is this?
- What is currently selected?
- What are the top three temporary recommendations?
- Does anything need review?

## Empty And Partial States

Suggested behavior:

### No Candidates

Copy:

`No hero candidates available.`

### One Or Two Candidates

Copy:

`Only two candidates available.`

or:

`Fewer than three candidates available.`

### Diagnostics Unavailable

Copy:

`Diagnostics unavailable. Manual review remains available.`

### Current Hero Missing

Copy:

`Current hero not found in candidate set.`

### All Candidates Rejected

Copy:

`All candidates are rejected; review required.`

### Shortlist Endpoint Unavailable

Copy:

`Shortlist unavailable. Open Hero Editor to review images.`

### Invalid Item Data

Copy:

`Unable to load shortlist for this item.`

## Backward Compatibility

The first UI implementation must not break the existing Hero Manager.

Existing manual paths must remain available:

- Hero Editor
- candidate review panel
- manual override controls
- rejection controls

The top-three UI should be additive, not a replacement for existing manual workflows.

## Performance And Endpoint Dependency

Options:

### A. Use Current Single-Item Opt-In Endpoint Per Product

Pros:

- already exists
- simple for a small experiment

Cons:

- inefficient for many products
- many requests
- likely poor fit for filtered product lists

### B. Create Batch Shortlist Endpoint Before Product-List UI

Pros:

- better for product-list performance
- cleaner UI data source
- supports filtered review

Cons:

- requires endpoint planning before UI code

### C. Precompute Or Cache Shortlist Metadata Later

Pros:

- could improve performance at scale

Cons:

- introduces staleness and source-of-truth concerns
- not needed before the contract is proven

Recommended next step:

Plan a batch shortlist endpoint before implementing full product-list UI.

## Accessibility And Usability Considerations

Future UI should ensure:

- thumbnails have useful alt text or accessible labels
- rank badges do not rely only on color
- warning states include text
- buttons and links are keyboard accessible
- small thumbnails remain readable
- current hero and recommendation status are understandable to screen readers
- mobile/tablet admin layout does not become unusable

The UI must not rely on color alone to communicate rank, warning, current hero, or rejected state.

## Non-Goals

Stage 3P does not include:

- UI implementation
- JavaScript changes
- CSS changes
- endpoint changes
- batch endpoint implementation
- one-click hero replacement
- scoring formula changes
- criteria engine
- database writes
- automatic final hero selection
- garment segmentation claims

## Recommended Stage 3Q

Recommended next stage:

Stage 3Q - Plan batch shortlist endpoint for product-list top-three display.

Rationale:

The product-list UI needs shortlist data for many products. The current opt-in endpoint is single-item and is good for contract testing, but not ideal as the product-list data source.

Stage 3Q should remain planning/documentation unless explicitly approved for code.

## Stage 3P Verdict

The best first UI direction is a scan-first Hero Manager product-list row that shows:

- product identity
- current hero
- top-three temporary recommendations
- calm status/warning indicators
- a `Review all images` challenge action

However, full product-list UI implementation should wait until a batch shortlist endpoint is planned.
