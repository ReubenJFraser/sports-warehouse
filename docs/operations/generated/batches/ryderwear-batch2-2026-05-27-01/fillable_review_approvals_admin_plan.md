# Fillable Review Approvals Admin Plan

## Scope and intent

This document defines a documentation-only implementation plan for evolving the current Review Approvals admin workflow from read-only document viewing to a safe fillable human acceptance form.

This plan does not implement UI, storage writes, or backend behavior changes. It is a design artifact for future small pull requests.

## 1) Current state

- The Review Approvals admin page functions as a workflow dashboard.
- The Review Workflow Document page is an allowlisted read-only viewer.
- The viewer intentionally renders allowlisted workflow documents as escaped preformatted text.
- `human_reviewer_acceptance_worksheet.md` exists as a worksheet template, but it is currently fillable only through manual Markdown editing in a code editor.

Current gap:
- Human reviewers cannot complete acceptance decisions directly in the admin backend UI.

## 2) Proposed future state

Target behavior for the current Ryderwear Batch 2 workflow:
- Add a fillable admin form for the human reviewer acceptance workflow.
- Keep `human_reviewer_acceptance_worksheet.md` as the source template and reference document.
- Persist reviewer input as a separate explicit review record artifact after user submission.
- Do not silently edit or overwrite the worksheet template.

Design principle:
- Separate template from completion record to preserve auditability and reduce accidental source mutation.

## 3) Storage options comparison

### Option A: New JSON review record file

Example filename pattern:
- `human_reviewer_acceptance_record.json`
- Or timestamped variant such as `human_reviewer_acceptance_record.2026-05-28T12-30-00Z.json`

Pros:
- Strong structure for validation and schema evolution.
- Easy field-level parsing in PHP and tests.
- Supports explicit metadata (workflow_id, reviewer identity, submitted_at, version).
- Low ambiguity between template and completed record.

Cons:
- Slightly less hand-editable for non-technical users than Markdown.
- Requires explicit serialization and schema checks.

Safety concerns:
- Must enforce strict allowlisted output filename and folder.
- Must reject arbitrary path input and filename injection.
- Must prevent accidental overwrite unless explicitly versioned policy is defined.

Suitability now:
- High. Best balance of safety, clarity, and implementation simplicity for an initial form workflow.

### Option B: New CSV review record file

Example filename pattern:
- `human_reviewer_acceptance_record.csv`

Pros:
- Familiar artifact format in this repository.
- Easy diff and spreadsheet inspection.

Cons:
- Weaker nested structure for notes and metadata.
- Requires careful escaping for multiline reviewer notes.
- Validation rules are less self-describing than JSON.

Safety concerns:
- CSV injection concerns if exported to spreadsheet tools.
- Must still enforce strict allowlisted filename.
- Multiline fields can increase parser fragility.

Suitability now:
- Medium. Viable, but less robust than JSON for first-pass safe form capture.

### Option C: New Markdown completion record

Example filename pattern:
- `human_reviewer_acceptance_record.md`

Pros:
- Human-readable narrative format.
- Easy side-by-side comparison with worksheet template.

Cons:
- Weak machine validation and fragile parsing.
- Higher risk of formatting drift.
- Harder to ensure strict field completeness and enum validation.

Safety concerns:
- Templating or freeform rendering may blur source template vs record.
- Higher chance of accidental manual edits that break structured checks.

Suitability now:
- Medium-low. Acceptable as a display artifact later, not ideal as primary system-of-record for first implementation.

### Option D: Database/admin tables

Pros:
- Centralized queryability and role-based workflows.
- Natural fit for future multi-reviewer lifecycle and status tracking.

Cons:
- Highest implementation scope and operational risk.
- Requires schema design, migrations, and environment consistency.
- Violates current safety direction to avoid ProductDB or schema changes at this stage.

Safety concerns:
- Database writes expand blast radius.
- Migration and rollback complexity.

Suitability now:
- Low for current stage. Better for later maturity phase after stable file-based workflow and validation gates are proven.

## 4) Recommended storage approach

Recommended first implementation:
- Create a separate explicit JSON review record in the same batch folder only after form submission.

Recommended filename:
- `human_reviewer_acceptance_record.json`

Rationale:
- Safest initial path with strict schema validation.
- Clear separation from existing operational CSV artifacts.
- Easy to audit, diff, and test.

Non-confusion requirement:
- The output filename must be fixed and explicitly allowlisted.
- The new record must not be named or treated as:
  - `source_asset_inventory.csv`
  - `suspicious_mapping_report.csv`
  - `copy_simulation.csv`

Optional later enhancement:
- Emit an additional read-only Markdown summary generated from JSON for human readability, while JSON remains authoritative.

## 5) Proposed admin pages and routes

Minimal route set:

1. `admin/review-approvals.php`
   - Workflow dashboard.
   - Shows batch/workflow cards and current status.
   - Provides actions to view worksheet, fill form, and view saved record when present.

2. `admin/review-approval-form.php`
   - Fillable form UI for one allowlisted workflow id.
   - Handles form render, validation errors, confirmation preview, and submit action.
   - Writes only to approved review-record filename on successful submit.

3. `admin/review-workflow-document.php`
   - Keep existing read-only allowlisted viewer behavior.
   - Continue escaped preformatted document rendering for worksheet/template review.

## 6) Form field mapping from worksheet

Map these fields directly from `human_reviewer_acceptance_worksheet.md`:

- `human_acceptance_status`
  - Allowed values: `accept`, `revise`, `reject`, `defer`
  - Purpose: primary acceptance status for the current review pass.

- `human_final_decision`
  - Allowed values: `accept`, `revise`, `reject`, `defer`
  - Purpose: explicit final decision selected by reviewer, never auto-derived without input.

- `human_reviewer_notes`
  - Allowed values: free text (bounded length, for example 4000 chars).
  - Purpose: rationale, required corrections, escalation notes.

- `approval_date`
  - Allowed values: ISO date string `YYYY-MM-DD`.
  - Purpose: reviewer confirmation date.

- `approved_source_root` (where applicable)
  - Allowed values: from allowlisted known source roots for this workflow context.
  - Purpose: explicit source scope approved by reviewer.

Validation note:
- `human_final_decision` must require explicit reviewer selection even when matching `human_acceptance_status`.

## 7) Guardrails and safety constraints

The future form implementation must enforce all of the following:

- Accept only known allowlisted workflow ids.
- Accept only known allowlisted document ids.
- Write only to a fixed allowlisted review-record filename.
- Reject arbitrary filesystem paths, traversal tokens, and dynamic write targets.
- Never create `source_asset_inventory.csv`.
- Never create `suspicious_mapping_report.csv`.
- Never create `copy_simulation.csv`.
- Do not scan local folders to discover candidate files.
- Do not modify ProductDB, SQL, or database schema.
- Do not modify images, manifests, or public storefront files.
- Do not auto-convert proposed decisions into final decisions without explicit user selection.

Security implementation notes:
- Server-side validation is authoritative.
- UI controls alone are not sufficient.
- Log write attempts and rejected invalid targets for auditability.

## 8) Implementation sequence (small safe PRs)

PR 1: Planning only
- Add this plan document.
- No PHP behavior changes.

PR 2: Read-only structured parse/display
- Parse worksheet structure into a typed internal representation for a single allowlisted workflow.
- Render structured read-only field preview in admin (no writes).

PR 3: Form rendering
- Add `admin/review-approval-form.php` render path with field widgets and static enum options.
- Keep submit disabled or no-op initially.

PR 4: Save draft review record
- Enable POST submit with strict validation.
- Write allowlisted JSON record file only.
- Add basic success and error states.

PR 5: Validation and completion gate
- Add stricter rules for required fields and enum checks.
- Add explicit confirmation step before final write.
- Add guardrail tests for path injection and forbidden artifact creation.

PR 6: Later downstream artifact enablement
- Optional follow-up integration that consumes explicit review record.
- Keep downstream generation blocked unless completion gate passes.

## 9) UI and UX notes

Current action:
- "Open acceptance worksheet"

Proposed split actions:
- "View worksheet" (read-only template viewer)
- "Fill acceptance form" (structured entry UI)
- "View saved acceptance record" (read-only saved submission)

Interaction and styling guidance:
- Clickable actions should be styled as primary/secondary buttons with clear hover/focus states.
- Non-clickable or unavailable actions should be visibly disabled and not appear as links.
- If no saved record exists, "View saved acceptance record" should be disabled with helper text.
- Keep terminology consistent between dashboard, form, and record viewer.

Suggested state labels:
- Not started
- Draft saved
- Submitted
- Needs revision

## Testing recommendations for future implementation

- PHP syntax checks for new or changed PHP files.
- Form submission validation tests for required fields and allowed enums.
- Rejection tests for arbitrary workflow/doc/path input.
- Confirmation tests that blocked downstream artifacts are not created.
- Confirmation tests that saved review record is explicit, separate, and reviewable.

Suggested command examples for future PRs:
- `php -l admin/review-approval-form.php`
- `php -l admin/review-approvals.php`
- Targeted integration tests for POST validation and file write guardrails.

## Non-goals for this plan

- No implementation of form controllers or write logic in this change.
- No edits to existing worksheet content.
- No edits to generated CSV artifacts.
- No ProductDB, SQL, schema, image, manifest, or storefront changes.
