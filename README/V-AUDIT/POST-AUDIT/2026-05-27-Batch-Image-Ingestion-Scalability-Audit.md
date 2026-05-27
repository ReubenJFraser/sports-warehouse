# 2026-05-27 Batch Image Ingestion Scalability Audit

## Scope and method
This is a repository-level documentation audit of whether Ryderwear Batch 2 outcomes represent a reusable multi-brand batch-ingestion system or a partly brand-specific remediation workflow.

Method:
- Reviewed the listed generated operations artifacts and governance contracts.
- Compared repeatable pipeline primitives vs Ryderwear-specific assumptions.
- Produced planning-only conclusions; no mutation actions were performed.

---

## Fundamental question
Does solving Ryderwear Batch 2 imply future batches will be easy, or does each future batch still require substantial bespoke work?

**Answer:** Ryderwear Batch 2 demonstrates that a **reusable governance-first ingestion framework now exists**, but future batches are only "easy" when identity, source provenance, and destination ownership are already clean. Where those conditions are missing, substantial human adjudication remains required.

---

## Short answer
### What will generalize
- Deterministic identity governance centered on `model_id` and cross-system matching discipline (`model_id` / `external_item_id` context, uniqueness checks, structured-field translation boundaries).
- Planning-first, non-destructive sequencing (inventory -> mapping proposal -> collision detection -> approval -> simulation -> update-plan artifacts).
- Hero-field preparation pattern (`images` to `chosen_image` + `thumbnails_json`) as a reusable readiness bridge for admin workflows.
- Inactive Product Review positioning as admin preparation space prior to publication.

### What will not generalize automatically
- Ryderwear-specific folder semantics (NKD/non-NKD branches, sports-bra subtype vocabulary, collection naming, style-family cut logic).
- Ryderwear-specific collision causes created by source folder legacy and naming overlap.
- Ryderwear source-recovery heuristics and product-family disambiguation terms.
- Any assumption that token overlap implies identity ownership (explicitly non-authoritative under current contracts).

### Conditions required before future batches become routine
1. Standardized source manifest with stable identifiers and explicit provenance.
2. Deterministic product identity matches validated with uniqueness checks.
3. Collision-free approved destination ownership (or approved split paths).
4. Verified source file existence for all mapped assets.
5. Approved copy simulation and image-field/hero-field update plans generated (but not auto-executed).
6. Admin readiness visibility before activation/publication decisions.

---

## Reusable parts
The following workflow components are now reusable across future batches:

1. **Identity matching governance**
   - `model_id` as canonical identity anchor, with structured translation and uniqueness discipline.
   - `external_item_id`-matched execution planning for DB update artifacts.

2. **Image-field to hero-field preparation bridge**
   - Deriving `chosen_image` from first `images` token.
   - Deriving `thumbnails_json` from normalized full image list.
   - Deferring `hero_image` population to Hero Manager recalc/manual curation.

3. **Inactive Product Review workflow pattern**
   - Treating inactive rows as intentionally reviewable in admin.
   - Separation between image-ready, hero-ready, and frontend-ready states.

4. **Collision detection and adjudication pattern**
   - Detect duplicate destination-path ownership conflicts.
   - Group collisions by destination path.
   - Force explicit ownership decisions before copy/update planning.

5. **Split-path proposal pattern**
   - Generate candidate split destinations for ambiguous ownership groups.
   - Attach confidence + manual review markers.

6. **Approval checklist stage**
   - Convert technical proposals into human decision checklists with explicit status and defer states.

7. **Copy simulation before execution**
   - Non-destructive simulation artifacts as mandatory precondition for operations.

8. **Planning-first non-destructive workflow discipline**
   - Document -> review -> approve -> simulate -> stage update plans before any mutation step.

---

## Ryderwear-specific parts
The following elements are Ryderwear-specific and should not be treated as drop-in defaults:

1. **NKD/non-NKD branching semantics** and associated folder-family logic.
2. **Ryderwear sports-bra taxonomy density** (cut/support/neckline/strap/construction overlaps).
3. **Ryderwear folder conventions and collection labels** (`activate`, `lift`, `embody`, `staples`, `sculpt`, etc.).
4. **Ryderwear family terms** (knot/twist/bandeau/one-shoulder/scrunch/underwire-keyhole) that drive split decisions.
5. **Ryderwear-specific duplicate-collision patterns** caused by legacy source/destination naming collisions.
6. **Ryderwear source-folder recovery patterns** for suspicious mappings or missing canonical folders.

---

## Future-batch risk model
### Easy batches
Likely routine when:
- stable external IDs already exist and map cleanly;
- source folders are complete and provenance is explicit;
- one product owns one destination path;
- no ambiguous variant-family overlap.

### Medium batches
Moderate effort when:
- assets exist but folder naming requires governed mapping translation;
- minor collisions or taxonomy normalization decisions are needed;
- some rows require review but ownership mostly deterministic.

### Hard batches
High bespoke effort when:
- duplicate destination ownership is widespread;
- source folders are missing or uncertain;
- variant/model boundaries are unclear;
- source provenance is weak;
- brand taxonomy conflicts with current governed translation rules.

---

## Required future pipeline stages
A general batch image-ingestion pipeline should include these stages in order:

1. batch manifest intake
2. identity verification
3. source-folder inventory
4. proposed destination-path generation
5. collision detection
6. suspicious mapping audit
7. human approval checklist
8. copy simulation
9. image-field update plan
10. hero-field preparation plan
11. inactive admin review
12. frontend readiness gate
13. activation/publication decision

Implementation note: stages 1-10 should remain non-destructive planning artifacts unless explicit execution authorization is granted.

---

## Standardized tooling candidates
The following should be formalized as reusable scripts/reports/pages:

1. batch intake checker
2. image folder inventory generator
3. source-to-product matching report
4. destination collision detector
5. split-path proposal generator
6. approval checklist generator
7. copy simulation generator
8. MySQL image-field update generator (planning artifact generation)
9. Hero Manager field-preparation generator (planning artifact generation)
10. Inactive Product Review readiness filters and summary counters

---

## What should remain human-reviewed
These decisions should remain explicitly human-reviewed rather than fully automated:

1. ambiguous product identity resolution
2. duplicate destination ownership assignment
3. uncertain source provenance acceptance
4. frontend publication readiness sign-off
5. accessibility text quality (alt/aria semantic adequacy)
6. hero-image choice where quality judgment is subjective

---

## Strategic recommendation
### Options considered
1. Finish Ryderwear first, then generalize.
2. Pause Ryderwear now and build full reusable pipeline first.
3. Continue Ryderwear while converting each completed step into reusable pattern documentation and tooling specs.

### Preferred recommendation
**Option 3 (preferred): continue Ryderwear while codifying each step as reusable pattern documentation.**

Rationale:
- The project already has concrete validated artifacts for each major stage.
- Remaining Ryderwear issues expose real edge cases needed to harden reusable tooling.
- Pausing to over-abstract now risks building generic tooling that misses real collision/provenance failure modes.
- Finishing Ryderwear without codification would lose momentum for repeatability.

---

## Acceptance criteria for "future batches are routine"
A future batch qualifies as routine only when all are true:

1. standard source manifest exists
2. all product identities match governed identity rules
3. no unresolved duplicate destination paths remain
4. source image files are verified present/readable
5. generated image paths pass human review
6. copy simulation passes without unresolved exceptions
7. MySQL/ProductDB update plan artifacts are generated but not auto-executed
8. admin readiness state is visible and reviewed before publication

---

## Non-goals
This audit does **not**:
- modify files outside this new audit README;
- update MySQL;
- update ProductDB;
- copy, move, rename, or delete images;
- generate SQL;
- change runtime/admin/frontend code;
- activate products;
- set featured flags.

---

## Final determination
Ryderwear Batch 2 has matured into a **reusable ingestion governance framework plus brand-specific remediation layer**. Future batches will be low effort only when identity, provenance, and ownership are clean at intake; otherwise, the reusable framework will still reduce risk and effort, but human adjudication remains a first-class requirement.
