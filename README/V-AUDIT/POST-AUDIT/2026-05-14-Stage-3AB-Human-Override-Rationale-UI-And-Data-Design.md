# Stage 3AB: Human Override Rationale UI And Data Design

Date: 2026-05-14  
Repository: `ReubenJFraser/sports-warehouse`  
Scope: Planning and design only (no implementation changes)

## 1) Purpose

Human Override Rationale is needed to preserve two goals at the same time:

1. **Editorial governance and accountability**: manual hero selection remains final, but when manual choice differs from shortlist guidance, the decision should be explainable and reviewable.
2. **Criteria refinement feedback loop**: structured rationale reasons (not only free text) provide consistent signals that can be aggregated to improve ranking logic, criteria profiles, metadata quality, and image guidance.

This design continues the principle:

- Automation suggests.
- Manual curation decides.
- Manual hero authority remains final.
- Structured override rationale becomes analyzable feedback for future improvement.

---

## 2) Product Problem Example (Booty-Shorts Case)

Example scenario (e.g., adidas women’s booty shorts):

- Top-ranked shortlisted images may score well against product-region criteria.
- However, some top-ranked images may be rear-facing or side-facing and unsuitable as primary hero images.
- The currently selected hero may be outside the recommended top-three shortlist.
- Even so, a human editor may determine it is the best available primary image for customer-facing presentation.

The system should allow this decision to be explicitly recorded with structured reasons and an optional note so the override is both **defensible now** and **useful later**.

---

## 3) UI Placement

Proposed placement in Hero Manager:

- Place the rationale control **inside or adjacent to the existing insights area** on each product card.
- Keep rationale UI **collapsed by default** to avoid clutter and preserve scanning speed.
- Allow expansion **per product card only** (local, targeted interaction).
- Show a compact indicator at card level when rationale is required or already saved.

This keeps the feature discoverable while avoiding always-on visual noise across large product queues.

---

## 4) UI States

Define explicit, compact states for each product card:

1. **No rationale needed**  
   Current hero is aligned with shortlist/criteria and no override explanation is required.

2. **Rationale needed**  
   Current hero appears outside top shortlist (or otherwise flagged) and requires human explanation.

3. **Rationale saved**  
   Structured reasons and/or note have been recorded for the current override context.

4. **Criteria review signal**  
   Rationale suggests profile/criteria mismatch or likely criteria tuning need.

5. **Image-set limitation**  
   Rationale indicates no ideal candidate exists within currently available image set.

6. **Metadata/category issue**  
   Rationale indicates possible product metadata/category assignment issue affecting ranking suitability.

States can coexist as primary + secondary signals (e.g., “Rationale saved” plus “Criteria review signal”).

---

## 5) Panel Design

### Collapsed state (default)

- Compact status badge(s) (e.g., “Rationale needed”, “Rationale saved”).
- Single action button:
  - **Record rationale** (if none saved)
  - **View/Edit rationale** (if saved)

### Expanded state (per product)

- Header with current status and context summary.
- Structured checkbox reason groups (multi-select).
- Optional free-text note textarea for nuance.
- Save action (e.g., “Save rationale”).
- Optional last-saved metadata preview (timestamp/user), if available.

Design intent: fast structured capture first, optional detail second.

---

## 6) Structured Checkbox Reasons (Initial Taxonomy)

Initial reason code candidates:

1. Top-ranked image is rear-facing / unsuitable angle.
2. Top-ranked image is side-facing / insufficiently clear.
3. Product is visible but presentation is not suitable for the primary hero image.
4. Product focus conflicts with editorial or brand presentation.
5. Full-body / model presentation preferred.
6. Face / model context needed despite lower product-region score.
7. Current criteria profile is probably wrong.
8. Product or category metadata may be wrong.
9. Diagnostics or ranking appear technically wrong.
10. No ideal image exists in the available image set.
11. Human editorial judgement overrides criteria for this product.

Design notes:

- Reasons should be stored as stable machine-readable codes plus human-readable labels.
- Multi-select should be allowed because real overrides can involve multiple factors.
- Optional note remains important for context not captured by taxonomy.

---

## 7) Saved Data Model Options (Design Only)

### Option A: Extend existing hero override storage
- Add rationale fields directly to existing override records.
- Pros: fewer joins, simpler near-term read path.
- Cons: mixes final manual selection authority with explanatory/audit data; weaker history support; can complicate future reporting.

### Option B: New `hero_override_rationale` table
- Store rationale as separate records linked to item/hero context.
- Pros: separation of concerns; cleaner evolution of reason taxonomy; easier reporting.
- Cons: additional write/read paths.

### Option C: Dedicated rationale audit/history table
- Append-only history records for each rationale save/update event.
- Pros: strong traceability, change history, governance.
- Cons: requires clear “current rationale” resolution and possibly complementary summary view.

### Recommended direction

Prefer **separate rationale storage** (Option B, optionally paired with Option C audit) so that:

- Manual hero selection state remains distinct from explanatory evidence.
- Governance/audit needs are easier to satisfy.
- Reporting and criteria-refinement analysis are not constrained by the selection storage model.

---

## 8) Possible Saved Fields (Design Candidates)

Potential fields to evaluate in Stage 3AC/3AD:

- `itemId`
- `selectedHeroImagePath`
- `activeCriteriaProfile`
- `selectedReasonCodes` (array or normalized relation)
- `optionalNote`
- `createdAt` / `updatedAt` timestamps
- `adminUserId` or equivalent actor identifier (if available)
- `criteriaRefinementFlag` (derived or explicit)
- `imageSetLimitationFlag` (derived or explicit)
- `metadataCategoryIssueFlag` (derived or explicit)

Additional optional fields:

- shortlist snapshot reference (top candidates at decision time)
- hero selection source/version markers
- rationale version number for future taxonomy migrations

---

## 9) Reporting / Future Criteria Refinement

Structured rationale enables later analysis such as:

- Most common override reasons overall.
- Override reasons by product category.
- Override reasons by active criteria profile.
- Items repeatedly flagged for criteria profile review.
- Items/categories with frequent image-set limitation signals.
- Items/categories with frequent metadata/category issue signals.

This can guide:

- criteria profile tuning priorities,
- photo standard guidance to content teams,
- metadata cleanup workflows,
- and quality-control checkpoints in Hero Manager operations.

---

## 10) Implementation Staging (Proposed)

Given risk and cross-cutting impact, implement incrementally:

- **Stage 3AC**: storage/data model design and migration plan.
- **Stage 3AD**: rationale read/write handling (endpoint or PHP flow design/implementation).
- **Stage 3AE**: collapsed rationale panel UI in Hero Manager.
- **Stage 3AF**: product-card rationale/status indicators.
- **Stage 3AG**: reporting outputs for criteria-refinement review.

Each stage should include targeted validation before moving forward.

---

## 11) Non-Goals (Stage 3AB)

This stage explicitly does **not** implement:

- database schema changes,
- UI form implementation,
- save action wiring,
- JavaScript interaction,
- reporting dashboard implementation,
- criteria scoring/ranking algorithm changes,
- automatic learning/adaptation from override reasons.

This is a design artifact only.

---

## 12) Acceptance Criteria (Pre-Implementation)

Before implementation begins, this design stage is complete when:

1. Human Override Rationale purpose and governance intent are documented.
2. UI placement, collapse behavior, and product-card indicator concept are documented.
3. Core UI states are defined.
4. Initial structured reason taxonomy is defined.
5. Data model options are compared and a recommended direction is stated.
6. Candidate saved fields are documented.
7. Reporting/refinement use-cases are documented.
8. Staged implementation path (3AC–3AG) is documented.
9. Non-goals are explicitly documented.
10. No runtime/application code has been modified in this stage.

---

## Validation Summary (This Change)

- Documentation-only change.
- Exactly one new file created:
  - `README/V-AUDIT/POST-AUDIT/2026-05-14-Stage-3AB-Human-Override-Rationale-UI-And-Data-Design.md`
- No claim of implementation is made in this stage.
