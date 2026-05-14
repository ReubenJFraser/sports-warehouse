# Stage 3AC: Human Override Rationale Storage Design / Migration Plan

Date: 2026-05-14  
Repository: `ReubenJFraser/sports-warehouse`  
Scope: Planning and design only (no implementation changes)

## 1) Purpose

Stage 3AC exists to decide **where override rationale data should live** before any save UI or endpoint logic is built.

Storage design must come first because the same rationale data will be consumed by multiple later surfaces:

- Hero Manager rationale capture UI,
- read/edit flows,
- future reporting and criteria-refinement analysis,
- audit/governance traceability.

If storage is not designed first, later stages risk:

- mixing manual authority state with explanatory state,
- creating migration churn,
- limiting reporting quality,
- or forcing ad-hoc data formats that are hard to evolve.

Governance principle remains unchanged:

- Automation suggests.
- Manual curation decides.

## 2) Storage Problem

Manual hero authority and override rationale are related, but not identical.

- **Manual override** determines which hero image is authoritative for the product card now.
- **Override rationale** explains why a human accepted, kept, or selected a hero that differs from shortlist/criteria guidance.

Therefore:

1. The winner image decision and its authority path should remain clean and deterministic.
2. The rationale should be captured as explanatory evidence and future feedback.

The rationale data should support later analysis such as:

- criteria profile review,
- image-set quality review,
- metadata/category quality review,
- and diagnostic reliability review.

## 3) Existing Data Context (Repository Inspection)

This stage is planning-only, but repository inspection shows likely existing concepts that the rationale design must align with:

- Item hero state is persisted on item fields such as `hero_image`, `hero_ratio`, `hero_orientation`, and `hero_score` in query/render paths.  
- Candidate enumeration reads potential manual override state from `hero_override` by `itemId`.  
- Candidate status also checks rejected candidates via `hero_rejections` by `itemId`.  
- Shortlist contract building includes fields like `active_criteria_profile`, `shortlist_basis`, `recommended_candidates`, `all_candidates`, and `current_hero` context including outside-top-three flags.  
- Criteria profile metadata is exposed by shortlist helpers (`sw_get_hero_criteria_profile_metadata`) and passed into shortlist candidate/context payloads.

Important planning constraint:

- Exact production schema, column names, keys, and indexes for `hero_override` / `hero_rejections` must be verified before migration design in Stage 3AD.
- This document does **not** claim a confirmed database schema.

## 4) Storage Options

### Option A — Extend existing `hero_override` storage

**Advantages**

- Fewer immediate tables.
- Potentially simpler first read path if rationale always follows an override record.
- Might reduce near-term join complexity.

**Disadvantages**

- Blends two concerns: “which hero wins” vs “why it was chosen.”
- Harder to support multiple rationale events over time.
- Schema may become overloaded with mixed authority + narrative fields.

**Risks**

- Future reporting can become constrained by authority-table design decisions.
- Updating rationale may inadvertently affect hero authority logic if code paths are coupled.
- Harder taxonomy evolution for reason codes.

### Option B — Create separate `hero_override_rationale` table

**Advantages**

- Clear separation of concerns.
- Preserves manual authority path while adding explicit explanatory storage.
- Better fit for reporting and criteria-refinement aggregation.
- Easier to evolve reason-code strategy independently.

**Disadvantages**

- Additional read/write path and joins.
- Requires explicit linkage strategy to item and hero context at decision time.

**Risks**

- If linkage fields are under-specified, context reconstruction may be weak.
- Without lifecycle rules (latest vs history), duplicate active records may occur.

### Option C — Create broader `hero_override_audit` / `hero_decision_history` table

**Advantages**

- Strong traceability and historical audit trail.
- Can represent multiple event types over time (selection, rationale edit, re-confirmation).
- Good long-term governance story.

**Disadvantages**

- Higher complexity for a first delivery.
- Requires clear semantics to determine “current active rationale.”
- More fields/process governance needed early.

**Risks**

- Over-design for initial rollout if not scoped tightly.
- Query complexity may slow implementation velocity in Stage 3AD–3AF.

## 5) Recommended Direction

Preferred direction: **Option B** (separate rationale table), with history-capable design patterns inspired by Option C.

Rationale:

- Keep manual hero authority state separate from explanatory rationale state.
- Avoid overloading `hero_override` with mixed responsibilities.
- Preserve ability to store multiple rationale records over time when hero context changes.

Recommended record lifecycle model:

- Support **historical records** (append new rationale entries over time).
- Also support efficient **current/latest lookup** via either:
  - `is_active` flag, or
  - `superseded_at` timestamp, or
  - deterministic “latest by created_at/updated_at” rule with constrained writes.

Practical preference for this project: history + active/latest marker, because it balances showcase traceability with straightforward UI retrieval.

## 6) Proposed Data Fields (Design Candidates Only)

Likely candidate fields for a dedicated rationale table (names to be confirmed in Stage 3AD):

- `rationale_id`
- `itemId`
- `selected_hero_image`
- `current_hero_image`
- `active_criteria_profile`
- `shortlist_basis`
- `current_hero_rank`
- `current_hero_outside_top_three`
- `selected_reason_codes`
- `optional_note`
- `criteria_refinement_signal`
- `image_set_limitation_signal`
- `metadata_issue_signal`
- `diagnostics_issue_signal`
- `created_at`
- `updated_at`
- `created_by` (or available admin identifier)
- `is_active` and/or `superseded_at` (if history-supported)

Design notes:

- Keep snapshot fields that help reconstruct decision context at save time.
- Do not assume all fields are mandatory in first migration.
- Verify available admin identity model before committing to actor fields.

## 7) Reason-Code Storage

Structured checkbox reasons require machine-readable persistence.

### Approach comparison

1. **JSON field of selected reason codes**
   - Pros: flexible, low migration overhead, quick to ship.
   - Cons: weaker relational querying unless DB JSON tooling is used; taxonomy governance is app-level.

2. **Normalized reason-code lookup + join table**
   - Pros: strongest data integrity and analytics, clear taxonomy control.
   - Cons: more tables, more joins, more implementation effort.

3. **Delimited string** (e.g., comma-separated)
   - Pros: simplest initial write.
   - Cons: weak validation and brittle querying; not recommended beyond prototypes.

4. **Hybrid** (JSON for selected codes + optional lookup table for canonical code metadata)
   - Pros: practical implementation speed now, cleaner migration path later.
   - Cons: requires discipline to keep code set stable.

### Recommended practical first implementation

For this TAFE/portfolio project with serious design intent:

- Start with **JSON array of stable reason codes** in rationale records.
- Define a canonical in-code reason taxonomy and version it.
- Keep migration path open to normalized lookup/join tables if reporting complexity grows.

Avoid delimited strings for first implementation.

## 8) Example Saved Record (Illustrative Only)

Booty-shorts scenario conceptual record:

```text
rationale_id: 1042
itemId: 551238
selected_hero_image: "/img/products/adidas/womens/booty-shorts/front-01.jpg"
current_hero_image: "/img/products/adidas/womens/booty-shorts/front-01.jpg"
active_criteria_profile: "body_region_first"
shortlist_basis: "legacy_rank_placeholder"
current_hero_rank: 5
current_hero_outside_top_three: true
selected_reason_codes: [
  "rear_facing_top_rank_unsuitable",
  "side_facing_top_rank_unsuitable",
  "presentation_not_suitable_primary_hero",
  "editorial_override_applied"
]
optional_note: "Top-ranked images satisfy lower-body visibility but are rear/side compositions not suitable for primary PDP hero. Current image gives best available overall presentation."
criteria_refinement_signal: true
image_set_limitation_signal: true
metadata_issue_signal: false
diagnostics_issue_signal: false
created_by: "admin_user_7"
created_at: "2026-05-14T10:22:31Z"
updated_at: "2026-05-14T10:22:31Z"
is_active: true
superseded_at: null
```

This is an example of intended semantics only, not schema or SQL.

## 9) Reporting Implications

The storage model should support future reporting without reworking base tables.

Target report families:

- Most common override reasons overall.
- Override reasons by category.
- Override reasons by active criteria profile.
- Products frequently flagged as image-set limitations.
- Products/categories frequently flagged for metadata review.
- Criteria profiles frequently associated with refinement signals.

Design implications:

- Reason codes must be stable and aggregatable.
- Criteria profile and shortlist context should be snapshotted at save time.
- Signal flags should be query-friendly (boolean or equivalent).
- History support improves trend analysis over time.

## 10) Migration / Implementation Staging

Proposed post-Stage-3AC sequencing:

- **Stage 3AD**: SQL migration design and table creation.
- **Stage 3AE**: server-side save/read handling.
- **Stage 3AF**: Hero Manager collapsed rationale panel UI.
- **Stage 3AG**: saved rationale indicators.
- **Stage 3AH**: rationale review/reporting view.

Each stage should include focused validation and explicit non-goal boundaries.

## 11) Non-Goals (Stage 3AC)

Stage 3AC does **not** implement:

- database migration,
- UI rationale panel,
- save action wiring,
- endpoint changes,
- JavaScript interaction,
- reporting dashboard,
- automatic scoring/ranking changes,
- automatic learning/adaptation from override reasons.

This is a planning/design artifact only.

## 12) Acceptance Criteria (Pre-Implementation Gate)

Before implementation begins, Stage 3AC is complete when:

1. Storage problem is clearly separated from manual authority state.
2. Existing repository data context is documented with uncertainty notes where schema is unverified.
3. At least three storage options are compared with advantages/disadvantages/risks.
4. Recommended direction is explicit and justified.
5. Candidate fields are documented as design-only.
6. Reason-code storage options are compared and a practical recommendation is stated.
7. Illustrative saved record is included (non-SQL).
8. Reporting implications are documented.
9. Staged implementation plan (3AD–3AH) is documented.
10. Non-goals are explicit.
11. No runtime/application behavior is claimed as implemented.

Validation requirements for this stage:

- Documentation-only change.
- Changed files are reported.
- Only the new README file is created.
- No claim of DB migration, runtime verification, or production implementation.
