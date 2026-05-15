# Stage 3AM: Hero Manager Acceptance Review And Next-Step Roadmap

**Date:** 2026-05-15  
**Repository:** ReubenJFraser/sports-warehouse  
**Stage Type:** Documentation-only acceptance review and roadmap checkpoint

## 1) Purpose

This document freezes the current Hero Manager milestone as **accepted-for-now** after Stages 3AF–3AL. The immediate goal is to stop repeated micro-polishing of layout details and redirect implementation energy toward the longer-term roadmap, especially reporting and criteria refinement.

## 2) Current Milestone Summary

The Hero Manager now operates as a practical decision workflow that combines automation and editorial control. In its current accepted state, it includes:

- Automated candidate scoring and ranking.
- Shortlist preview (top candidates) for fast triage.
- Manual hero authority as the final decision layer.
- Expanded candidate review, including broader side-by-side comparison.
- Human Override Rationale recording.
- Structured override reasons (checkbox-based rationale capture).
- Saved editorial judgement with read/edit behavior.
- Criteria-refinement evidence generated through persisted override rationale.
- Expanded candidate comparison via the horizontal review tray.

## 3) Why This Matters

The Hero Manager is no longer just an admin image selector. It now demonstrates:

- Admin UX that supports real decision flow.
- Visual decision support rather than one-click replacement behavior.
- AI-assisted candidate ranking that remains transparent to operators.
- Human governance over final hero selection.
- Structured editorial reasoning captured in-system.
- Future reporting potential from saved rationale patterns.
- Portfolio-quality system thinking across UX, ranking, and governance boundaries.

## 4) Accepted-For-Now Design State

The current design is accepted for now, even though it remains imperfect.

Current acceptance position:

- The layout is usable and supports day-to-day curation.
- The horizontal candidate tray is a major practical improvement.
- The rationale panel works for capture and review/edit cycles.
- The compact saved-rationale state works and reduces visual noise.
- Full candidate review is available when deeper inspection is required.
- Product-level decision-making is visible to admins in one workflow.

## 5) Known Imperfections / Deferred Refinements

The following refinements are valid but **should not block progress**:

- Current hero and computed baseline can still duplicate the same image.
- Top-three candidate cards could eventually show more compact metadata (e.g., score/ratio) when available.
- Candidate browser could later receive arrow controls or more refined carousel behavior.
- Rationale form can still be further polished for clarity and speed.
- Dense product cards may need tuning after broader cross-category testing.
- Orientation/square metadata may need a more systematic display strategy.
- Full candidate diagnostics likely need stronger visual hierarchy.

## 6) Local Test Case: Booty Shorts Item 79

The Adidas Booty Shorts item (Item 79) is a useful milestone example:

- Top-ranked candidates appeared more product-focused by scoring logic.
- Several candidates were rear-facing or side-facing.
- The current hero was outside the shortlist but remained editorially preferable.
- Rationale was saved to explain and preserve the human override decision.
- This validated why human override rationale matters in production workflows.
- This also highlighted the need for future cross-product rationale pattern reporting.

## 7) Current Governance Boundary

**Automation suggests. Manual curation decides.**

Nothing delivered in Stages 3AF–3AL weakened manual hero authority. Automated ranking remains advisory; editorial selection remains authoritative.

## 8) What Should Stop For Now

Development should stop making small Hero Manager layout refinements unless there is a clear bug or a clear workflow blocker.

This checkpoint exists specifically to prevent endless polishing of a single screen and to keep project momentum aligned with roadmap value.

## 9) Recommended Next Roadmap

Recommended next sequence:

- **Stage 3AN:** Rationale Pattern Review / Reporting
- **Stage 3AO:** Test Hero Manager rationale workflow across multiple product categories
- **Stage 3AP:** Criteria-profile refinement based on saved rationale patterns
- **Stage 3AQ:** Candidate metadata improvements in shortlist preview
- **Stage 3AR:** Optional candidate-browser carousel arrows / navigation polish
- **Stage 3AS:** Portfolio evidence capture for Hero Manager milestone

## 10) Recommended Immediate Next Stage

### Stage 3AN: Rationale Pattern Review / Reporting

Saved checkbox reasons become significantly more powerful when they are reviewable across products and categories.

Initial reporting questions should include:

- Which override reasons occur most often?
- Which categories most frequently need human override?
- Which criteria profiles produce the most overrides?
- Which products appear to need better source image sets?
- Which products suggest metadata/category quality issues?
- Which rationale patterns indicate criteria-profile adjustment opportunities?

## 11) Non-Goals

Stage 3AM does **not**:

- Change code.
- Change schema.
- Change Hero Manager UI.
- Implement reports.
- Alter scoring.
- Alter candidate ranking.
- Alter manual hero authority.

## 12) Acceptance Criteria

This stage is complete when the documentation clearly establishes that:

- This is a documentation-only checkpoint.
- The current Hero Manager design is accepted for now.
- Known issues/deferred refinements are recorded.
- The next roadmap is defined.
- Implementation focus should move to reporting/pattern review rather than additional layout polishing.

---

## Validation Notes

- Documentation-only change.
- Only one new file created for Stage 3AM.
- No runtime code modified.
- No browser verification claimed.
