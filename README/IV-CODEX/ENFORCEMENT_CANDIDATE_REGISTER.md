# Enforcement Candidate Register

## Purpose

This README defines the enforcement candidate register for the Sports Warehouse project.

Its purpose is to record which files, areas, and behaviours may contain enforcement logic, which must remain diagnostic only, and which are categorically prohibited from becoming enforcement points.

This document exists to preserve the separation between visibility, authority, manual editorial control, and mutation enforcement.

It is intended for Codex, ChatGPT, and human operators working on admin mutation paths, hero-image workflows, diagnostic tools, authority layers, and automation-assisted curation.

---

## Scope

This README covers:

- enforcement candidate tracking
- enforcement eligibility
- enforcement prohibition
- visibility-source requirements
- authority-boundary protection
- mutation-path discipline
- enforcement readiness rules

This README does not cover:

- general Codex behaviour
- architecture boundaries
- routing boundaries
- GitHub PR workflow
- visual design
- general UI implementation
- non-enforcement JavaScript or CSS behaviour

Those responsibilities are governed separately by:

- `README/IV-CODEX/01-Codex-Behavioural_Rules.md`
- `README/IV-CODEX/02-Codex-Architecture_Invariants.md`
- `README/IV-CODEX/03-Codex-Routing_Invariants.md`
- `README/IV-CODEX/04-Codex-GitHub_PR_Workflow.md`

---

## Current Enforcement Mode

The wider Sports Warehouse project has moved beyond its initial conservative stabilization phase into guardrailed implementation velocity.

That phase shift does not relax enforcement governance.

Enforcement remains a conservative authority area.

Codex may now implement meaningful bounded feature slices in ordinary UI, endpoint-consumption, styling, JavaScript, and documentation work when the relevant architecture and routing boundaries are known.

Codex may not use that increased implementation velocity to introduce new enforcement.

Enforcement logic can block actions, prevent mutation, alter admin behaviour, or change the effective authority model of the system.

For that reason, enforcement remains register-governed, explicitly authorized, and conservative.

---

## Conceptual Roles

### Visibility Source

A visibility source exposes observable state.

Examples include:

- SQL SELECT output
- diagnostic aggregation
- filesystem checks
- runtime configuration checks
- explicit user action state
- authority guard state

Visibility alone does not authorize enforcement.

---

### Enforcement Candidate

An enforcement candidate is a file, layer, or behaviour that could theoretically block, deny, hard-fail, constrain, or prevent an action.

A candidate may be authorized, rejected, deferred, or prohibited.

Candidate status must be recorded in this register.

---

### Enforcement Point

An enforcement point is a location where blocking or denial is actually allowed.

Enforcement points must be explicit.

Hidden enforcement is prohibited.

---

### Manual Editorial Authority

Manual editorial authority is the human-controlled ability to override, correct, reject, or preserve a state.

Manual editorial authority must not be weakened by automation or diagnostics.

---

### Automation

Automation may suggest, score, rank, diagnose, or prepare.

Automation must not become enforcement unless this register explicitly authorizes that change.

---

## Enforcement Status Vocabulary

The `Enforcement Status` column must use only these values:

- `Never`
- `No`
- `Maybe`
- `Existing`

### Never

`Never` means enforcement is categorically prohibited for that file or area.

This usually applies to:

- diagnostics
- layout
- navigation
- styling
- client-side UI helpers
- infrastructure probes
- passive tooling

### No

`No` means enforcement is not currently authorized.

Future authorization would require explicit register revision.

### Maybe

`Maybe` means enforcement may be considered later, but only with explicit rationale and a visibility source.

`Maybe` is not permission to implement enforcement.

### Existing

`Existing` means limited enforcement already exists and is accepted.

Existing enforcement must remain minimal and centralized.

It must not be expanded casually.

---

## Area Vocabulary

Area names should remain stable and descriptive.

Current area vocabulary includes:

- Hero diagnostics
- Environment diagnostics
- Hero editor (batch)
- Hero editor (manual)
- Authority layer
- Tooling
- Infrastructure
- Admin UI
- Admin assets

This vocabulary is not exhaustive.

New areas may be added only when they describe an actual project responsibility boundary.

---

## Adding a New Register Entry

A new entry may be added only when all of the following are true:

1. the file or area exists
2. the relevant behaviour is observable
3. a visibility source exists
4. the current behaviour can be described
5. the enforcement status is assigned explicitly
6. the boundary rationale is documented

Default enforcement status is `No`.

`Maybe` requires explicit rationale.

`Never` requires boundary justification.

`Existing` requires that enforcement already exists and has been accepted.

---

## Enforcement Change Rule

No new enforcement may be introduced unless this register is updated first.

The correct sequence is:

1. identify the proposed enforcement candidate
2. identify the visibility source
3. update this register
4. review the proposed status
5. approve the enforcement boundary
6. implement only the approved enforcement

If enforcement is proposed without an updated register entry, the proposal is invalid.

---

## Enforcement Candidate Register

| Area | File | Trigger Condition (Observable Only) | Visibility Source | Current Behavior | Enforcement Status | Notes / Boundary Rationale |
| --- | --- | --- | --- | --- | --- | --- |
| Hero diagnostics | `admin/inc/hero-status.php` | `hero_score IS NULL`, missing hero, legacy hero | SQL SELECT diagnostic aggregation | Read-only metrics surfaced in admin | Never | Canonical visibility authority. Must remain side-effect free. Enforcement here would collapse visibility and enforcement separation. |
| Environment diagnostics | `admin/inc/environment-status.php` | Missing environment variables, path issues, config mismatch | Runtime checks and read-only probes | Diagnostic output only | Never | Environment inspection only. No catalog authority. |
| Hero editor (batch) | `admin/hero-manager.php` | Manual recalc invoked on item | `hero-status.php` and item SELECTs | Manual action; write allowed only if authority permits | Maybe | Caller only. May invoke authority-guarded mutation but must never contain enforcement logic itself. |
| Authority layer | `HeroAuthority` invoked by `admin/hero-manager.php` | Source-scoped write attempt | Authority guard | Hard fail on deny | Existing | Minimal centralized enforcement already accepted. Do not expand casually. |
| Hero editor (manual) | `admin/hero-edit.php` | Manual override, clear override, reject auto | UI state and item SELECTs | Explicit editorial writes with rollback | No | Manual editorial intent is sacrosanct. Technical badness is not automatically invalid state. |
| Authority layer | `hero_override` via `admin/hero-edit.php` | Override insert or delete | Explicit user action | Direct reversible mutation | Existing | Editorial escape hatch. Must remain available to bypass automation. |
| Hero diagnostics | `hero_rejections` via `admin/hero-edit.php` | Rejection recorded | Explicit user action | Logged only; no blocking | No | Non-authoritative signal. Must not become enforcement input. |
| Admin assets | `admin/image-helper.php` | Missing or unreadable image file | Filesystem checks | Safe rendering and placeholders | Never | Helper layer must remain dumb. No business rules and no authority. |
| Tooling | `admin/sync-tool.php` | Mismatch detected between sources | Comparison output | Diagnostic reporting only | Never | Tooling must never reconcile or mutate state. |
| Infrastructure | `admin/db-test.php` | Connection success or failure | Connectivity test | Infrastructure status only | Never | Infrastructure probe. Must remain non-editorial and non-authoritative. |
| Admin UI | `admin/index.php` | Route resolution | Routing logic | Dispatch only | Never | UI shell. Enforcement here would be implicit and unsafe. |
| Admin UI | `admin/_layout.php` | Render request | Template composition | Presentation only | Never | Layout must not imply enforcement or state validity. |
| Admin UI | `admin/_header.php` | Render request | Template composition | Presentation only | Never | Presentation boundary only. |
| Admin UI | `admin/_nav.php` | Render request | Menu logic | Navigation only | Never | Navigation must not gate behaviour. |
| Admin assets | `css/admin/*` | Not applicable | Static assets | Styling only | Never | Presentation assets are categorically enforcement-ineligible. |
| Admin assets | `js/admin/*` | Not applicable | Client-side behaviour | User experience only | Never | Client-side code must not enforce catalog authority. It may guide, display, warn, or link, but must not become authority enforcement. |

---

## Register Interpretation Rules

### Diagnostics Are Not Enforcement

Diagnostics may expose missing, legacy, incomplete, or risky states.

Diagnostics must not automatically block mutation unless an authorized enforcement point consumes them through a registered authority path.

A diagnostic result is evidence.

It is not enforcement by itself.

---

### UI Is Not Enforcement

Admin UI may show status, warnings, badges, disabled-looking states, help text, or review links.

Those behaviours do not become enforcement unless they block or prevent an action.

Blocking actions from UI alone is prohibited unless explicitly authorized.

---

### Client-Side Code Is Not Authority

JavaScript may improve user experience.

JavaScript may:

- load read-only endpoint data
- display previews
- show warnings
- guide navigation
- normalize links
- support scanning workflows

JavaScript must not:

- enforce catalog authority
- become the only barrier to mutation
- silently block manual editorial intent
- replace server-side authority checks

---

### Manual Overrides Remain Sacrosanct

Manual editorial override exists to preserve human authority over automation.

Technical diagnostics may inform manual decisions.

Technical diagnostics must not automatically invalidate manual editorial choices unless an approved enforcement path exists.

---

## Phase Status

The original enforcement-readiness phase is closed.

Current phase status:

- Phase: Enforcement Readiness
- Status: Closed
- Additional Enforcement Authorized: No
- Existing Centralized Enforcement: Preserved
- New Enforcement: Register update required first

Outcome:

The system already contains minimal centralized enforcement.

Additional enforcement is not justified by default.

---

## Relationship to Guardrailed Implementation Velocity

Guardrailed implementation velocity applies to ordinary bounded implementation work.

Examples include:

- UI display improvements
- read-only endpoint consumption
- layout refinement
- JavaScript rendering safety
- routing-link correction
- documentation updates
- reversible implementation slices

Guardrailed implementation velocity does not apply to new enforcement authority.

Enforcement remains conservative.

This distinction is intentional.

---

## Known Gaps and Open Questions

Known open areas include:

- whether future hero automation should introduce stronger server-side denial paths
- whether rejection signals should remain informational forever
- whether any future bulk action should require additional authority gating
- whether the register should eventually be split by admin subsystem
- whether enforcement status should include a date or decision reference column

These gaps do not authorize implementation.

They identify areas for future explicit governance.

---

## Non-Goals

This register does not authorize:

- broad enforcement expansion
- client-side authority enforcement
- diagnostic-driven blocking
- automation overriding manual editorial authority
- hidden mutation guards
- enforcement in layout or navigation files
- enforcement in CSS or JavaScript
- enforcement as a side effect of implementation velocity

---

## Invariants

The enforcement invariants are:

- visibility must precede enforcement
- enforcement must be registered
- enforcement must be explicit
- diagnostics must remain side-effect free unless registered otherwise
- client-side code must not become authority
- manual editorial override must remain available
- existing centralized enforcement must remain minimal
- implementation velocity must not create enforcement drift

If enforcement is unclear, stop.

If enforcement is not registered, it is not authorized.

