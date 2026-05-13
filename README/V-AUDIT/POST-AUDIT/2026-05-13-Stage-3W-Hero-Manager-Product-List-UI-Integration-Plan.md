# 2026-05-13 — Stage 3W — Hero Manager Product-List UI Integration Plan

## 1) Stage purpose

Stage 3W defines the **final planning-only blueprint** for integrating the Hero Manager product-list scan surface with the Stage 3U batch shortlist endpoint at `admin/hero-shortlists.php`.

This document intentionally stops at planning. It is the **last planning-only stage before implementation**. The next stage is expected to be a real implementation slice (Stage 3X), unless explicitly redirected by Reuben.

## 2) Current confirmed foundation

Based on the repository state and Stage 3V verification notes:

- `admin/hero-shortlists.php` exists and returns the batch shortlist JSON shape intended for product-list scan usage.
- Stage 3U was merged as PR #5 (batch shortlist endpoint introduction).
- Stage 3V was merged as PR #6 (local verification record).
- Local verification confirms endpoint operation in read-only mode for list scanning.
- `shortlist_basis` remains `legacy_rank_placeholder`.
- `all_candidates` is intentionally excluded from the batch endpoint response.
- `challenge_endpoint` exists per product and points to the full candidate review path.

## 3) Product intent

The intended operational workflow is:

- Automation supplies a compact **top-three shortlist preview** by default.
- A human editor scans many products quickly inside Hero Manager product-list mode.
- The editor opens full candidate detail only when shortlist output appears wrong, incomplete, or risky.
- Manual Hero Manager selection remains the final authority on hero image outcomes.
- Automation suggests; manual curation decides.

## 4) Proposed UI surface

### Existing surface identified

The integration target is the existing Hero Manager list surface in `admin/hero-manager.php` (`.hero-list` with per-item cards/rows). This page already loads persisted hero state and displays active items in a scan-friendly layout.

### Proposed scan-mode row/card additions

For each visible product row/card, add a compact shortlist panel that shows:

- Product identity: `item_id`, item name, brand, and useful section/category context where available.
- Current hero summary (`current_hero`), including whether it sits outside the shortlist.
- Top three `recommended_candidates` preview.
- `shortlist_status`.
- `active_criteria_profile`.
- `shortlist_basis` (explicitly shown to avoid overclaiming).
- Challenge/review action driven by `challenge_endpoint`.

Design emphasis: preserve fast vertical scanning and low cognitive load; avoid turning the product list into a full diagnostics workbench.

## 5) Recommended candidate display

Plan a compact “top candidates” strip for each product:

- Rank badge (`#1`, `#2`, `#3`).
- Thumbnail preview.
- Short image identifier (e.g., basename/short label) when space permits.
- Lightweight diagnostics availability indicator where useful (e.g., available/unavailable signal).
- Minimal reason/confidence text only if already safe and compact from endpoint payload.

Important copy guardrail while `shortlist_basis=legacy_rank_placeholder`:

- Do not present the ranking as fully criteria-aware or AI-optimised.
- Treat shortlist text as preview-level assistive guidance, not authoritative scoring truth.

## 6) Status and exception states

The scan UI should support these states with concise badges/labels:

- `ready`
- `partial`
- `unavailable`
- Current hero outside top three
- Diagnostics unavailable
- No recommended candidates
- Endpoint error
- Loading state
- Empty filter result

Presentation rule: one short status line + subtle badge priority per card; avoid verbose blocks that reduce scan speed.

## 7) Copy and terminology rules

Because shortlist basis is still legacy placeholder logic, terminology must avoid overclaiming.

### Acceptable wording

- “Top candidates”
- “Recommended shortlist”
- “Shortlist preview”
- “Review candidates”
- “Current hero outside shortlist”

### Avoid until criteria-aware ranking exists

- “AI-selected best image”
- “AI-approved hero”
- “Best image”
- “Optimised ranking”

## 8) Data consumed from batch endpoint

Stage 3X UI integration should consume the following fields from `admin/hero-shortlists.php`:

- Root-level: `status`, `shortlist_basis`, `active_scope`, `summary`, `products`
- Per-product identity/context: `item_id`, `item_name`, `brand` (and any available section/category context if later included)
- Shortlist metadata: `active_criteria_profile`, `criteria_profile_metadata` (as needed), `shortlist_status`, `shortlist_basis`
- Candidate preview: `recommended_candidates`, `candidate_count`
- Current hero context: `current_hero`
- Review path: `challenge_endpoint`

## 9) Data deliberately not consumed

Product-list scan mode must **not** consume `all_candidates`.

Full candidate expansion belongs to challenge/review mode through:

- `admin/hero-candidates.php?item_id=ITEM_ID&include_shortlist=1`

This preserves separation between fast list scanning and deep single-product analysis.

## 10) Implementation boundary for next stage

## Stage 3X — Implement first read-only Hero Manager product-list shortlist UI slice

Stage 3X should:

- Add read-only UI consumption of `admin/hero-shortlists.php`.
- Display compact top-three recommended candidates in Hero Manager list rows/cards.
- Show current hero summary and provide challenge/review action using `challenge_endpoint`.
- Preserve manual hero selection authority exactly as-is.
- Avoid changing scoring, ranking logic, diagnostics generation, database schema, or candidate generation behaviour.

## 11) Non-goals

Stage 3W does **not** include any implementation and must not:

- Modify PHP behaviour.
- Modify JavaScript.
- Modify CSS.
- Modify database schema.
- Modify scoring formula.
- Modify ranking formula.
- Modify diagnostics JSON generation.
- Modify Python tooling.
- Change manual hero authority.
- Add override/rejection behaviour.
- Implement UI.

## 12) Acceptance criteria for Stage 3X

The next implementation slice is acceptable when:

1. Hero Manager product list can load batch shortlist data from `admin/hero-shortlists.php`.
2. Each visible product can display up to three recommended candidate thumbnails.
3. Current hero state is visible per product.
4. Products whose current hero is outside top three are visibly flagged.
5. Challenge/review action opens or links to full candidate review (`admin/hero-candidates.php?item_id=ITEM_ID&include_shortlist=1`).
6. Product-list batch usage does not include `all_candidates`.
7. UI copy does not overclaim AI ranking while `shortlist_basis=legacy_rank_placeholder`.
8. Existing manual hero selection workflow still works.
9. Existing endpoint behaviour remains unchanged (read-only contract preserved).

## Repository-grounding notes (Stage 3W inspection)

Inspected and confirmed relevant files for this plan:

- `admin/hero-manager.php` (existing product list scan surface).
- `admin/hero-shortlists.php` (batch shortlist endpoint contract and product payload shape).
- `admin/hero-candidates.php` (single-product challenge/review path).
- `inc/hero/shortlist.php` (shared shortlist contract builder, includes `all_candidates` for single-product usage).
- `inc/hero/diagnostics.php` (diagnostics availability model used inside shortlist enrichment).
- `admin/_nav.php` (Hero Manager admin navigation location).

No separate Hero Manager-specific JS module or CSS module inside `admin/` was required for this planning stage; Hero Manager page currently links shared admin hero stylesheet (`/css/admin/hero.css`) and renders list markup server-side.
