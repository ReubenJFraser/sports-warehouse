<!--
Codex note:
- This table is append-only
- Column order is fixed
- Enforcement Status must be one of: Never, No, Maybe, Existing
- Qualifiers belong in Notes, not in the status token
-->

### Adding a New Entry (Rules)

* A file must exist before it can be added
* A visibility source must already exist
* Enforcement Status defaults to `No`
* `Maybe` requires explicit rationale
* `Never` requires boundary justification

### Area Vocabulary (Non-Exhaustive)

* Hero diagnostics
* Environment diagnostics
* Hero editor (batch)
* Hero editor (manual)
* Authority layer
* Tooling
* Infrastructure
* Admin UI
* Admin assets

## Enforcement Candidate Register (Design-Only)

| Area                    | File                                            | Trigger Condition (Observable Only)             | Visibility Source                   | Current Behavior                                       | Enforcement Status | Notes / Boundary Rationale                                                                                                       |
| ----------------------- | ----------------------------------------------- | ----------------------------------------------- | ----------------------------------- | ------------------------------------------------------ | ------------------ | -------------------------------------------------------------------------------------------------------------------------------- |
| Hero diagnostics        | `admin/inc/hero-status.php`                     | `hero_score IS NULL`, missing hero, legacy hero | SQL SELECT (diagnostic aggregation) | Read-only metrics surfaced in admin                    | Never              | Canonical visibility authority. Must remain side-effect free. Enforcement here would collapse visibility/enforcement separation. |
| Environment diagnostics | `admin/inc/environment-status.php`              | Missing env vars, path issues, config mismatch  | Runtime checks + read-only probes   | Diagnostic output only                                 | Never              | Environment inspection only. No catalog authority.                                                                               |
| Hero editor (batch)     | `admin/hero-manager.php`                        | Manual recalc invoked on item                   | `hero-status.php` + item SELECTs    | Manual action; write allowed only if authority permits | Maybe              | Caller only. May invoke authority-guarded mutation but must never contain enforcement logic itself.                              |
| Authority layer         | `HeroAuthority` (invoked by `hero-manager.php`) | Source-scoped write attempt                     | Authority guard                     | Hard fail on deny                                      | Existing           | Minimal, centralized enforcement already accepted. Do not expand casually.                                                       |
| Hero editor (manual)    | `admin/hero-edit.php`                           | Manual override / clear override / reject auto  | UI state + item SELECTs             | Explicit editorial writes with rollback                | No                 | Manual editorial intent is sacrosanct. Technical “badness” ≠ invalid state.                                                      |
| Authority layer         | `hero_override` (via `hero-edit.php`)           | Override insert/delete                          | Explicit user action                | Direct, reversible mutation                            | Existing           | Editorial escape hatch. Must remain available to bypass automation.                                                              |
| Hero diagnostics        | `hero_rejections` (via `hero-edit.php`)         | Rejection recorded                              | Explicit user action                | Logged only; no blocking                               | No                 | Non-authoritative signal. Must not become enforcement input.                                                                     |
| Admin assets            | `admin/image-helper.php`                        | Missing/unreadable image file                   | Filesystem checks                   | Safe rendering / placeholders                          | Never              | Helper layer must remain dumb. No business rules, no authority.                                                                  |
| Tooling                 | `admin/sync-tool.php`                           | Mismatch detected between sources               | Comparison output                   | Diagnostic reporting only                              | Never              | Tooling must never reconcile or mutate state.                                                                                    |
| Infrastructure          | `admin/db-test.php`                             | Connection success/failure                      | Connectivity test                   | Infra status only                                      | Never              | Infrastructure probe. Must remain non-editorial, non-authoritative.                                                              |
| Admin UI                | `admin/index.php`                               | Route resolution                                | Routing logic                       | Dispatch only                                          | Never              | UI shell. Enforcement here would be implicit and unsafe.                                                                         |
| Admin UI                | `admin/_layout.php`                             | Render request                                  | Template composition                | Presentation only                                      | Never              | Layout must not imply enforcement or state validity.                                                                             |
| Admin UI                | `admin/_header.php`                             | Render request                                  | Template composition                | Presentation only                                      | Never              | Presentation boundary only.                                                                                                      |
| Admin UI                | `admin/_nav.php`                                | Render request                                  | Menu logic                          | Navigation only                                        | Never              | Navigation must not gate behavior.                                                                                               |
| Admin assets            | `css/admin/*`                                   | N/A                                             | Static assets                       | Styling only                                           | Never              | Presentation assets are categorically enforcement-ineligible.                                                                    |
| Admin assets            | `js/admin/*`                                    | N/A                                             | Client-side behavior                | UX only                                                | Never              | Client-side code must not enforce catalog authority.                                                                             |

## Phase 7 Lock (Enforcement Readiness)

This register reflects the complete set of enforcement candidates as of Phase 7 closure.

No new enforcement may be introduced unless:

* a new file is added AND
* a new visibility source exists AND
* this register is updated first.

If enforcement is proposed without an updated register entry, the proposal is invalid by definition.

## Phase 7 Status

* Phase: Enforcement Readiness
* Status: CLOSED
* Enforcement Authorized: NO

Outcome:
The system already contains minimal, centralized enforcement. Additional enforcement is not justified at this stage.


