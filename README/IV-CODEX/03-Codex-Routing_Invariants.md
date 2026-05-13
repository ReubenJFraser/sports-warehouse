```markdown
# Sports Warehouse — Routing Invariants

## Purpose

This README defines the routing invariants for the Sports Warehouse project.

Its purpose is to prevent route drift, broken local paths, duplicated admin segments, hidden URL assumptions, and accidental divergence between local Laragon behaviour and hosted behaviour.

This document is intended for Codex, ChatGPT, and human operators working on PHP routes, admin links, frontend links, JavaScript fetch paths, redirects, asset URLs, and path-sensitive UI behaviour.

The project has moved beyond its initial conservative stabilization phase, but routing remains a high-risk area because small path mistakes can break local testing, admin workflow, endpoint access, and frontend navigation.

Guardrailed implementation velocity is permitted only when route impact is understood and verified.

---

## Scope

This README covers:

- local base-path awareness
- frontend route boundaries
- admin route boundaries
- endpoint route usage
- JavaScript fetch URL construction
- asset path stability
- relative vs root-relative URL rules
- route verification expectations
- guardrails for bounded implementation involving URLs

This README does not cover:

- general architecture boundaries
- database authority
- importer logic
- enforcement candidate governance
- full Codex GitHub workflow mechanics
- visual design decisions

Those responsibilities are governed separately by:

- `README/IV-CODEX/01-Codex-Behavioural_Rules.md`
- `README/IV-CODEX/02-Codex-Architecture_Invariants.md`
- `README/IV-CODEX/04-Codex-GitHub_PR_Workflow.md`
- `admin/ENFORCEMENT_CANDIDATE_REGISTER.md`

---

## Current Routing Mode

The project now operates in guardrailed implementation velocity mode.

This means Codex may implement bounded routing-related changes when:

- the affected route surface is known
- the base path is respected
- the change is local and reviewable
- route behaviour can be tested in the browser
- no global routing convention is changed without explicit approval

This does not authorize route invention.

Routing remains centralized, explicit, and conservative at the boundary level.

Implementation may proceed faster inside known route boundaries, but routing conventions must not drift.

---

## Conceptual Roles

### Local Base Path

The local Laragon project runs under:

`/sports-warehouse-home-page/`

This base path is part of the local runtime contract.

Codex must not assume root deployment.

---

### Frontend Routes

Frontend routes serve public-facing catalogue, product, search, and content behaviour.

Frontend routing must not perform admin actions or assume admin context.

---

### Admin Routes

Admin routes serve internal management, curation, audit, and override workflows.

Admin routes must preserve the `/admin/` path segment unless an explicitly approved route convention says otherwise.

---

### Endpoint Routes

Endpoint routes expose structured data for UI behaviour.

Endpoint consumers must respect the endpoint contract.

Endpoint paths must resolve correctly from the page where they are called.

---

### Asset Routes

Asset routes serve CSS, JavaScript, images, and other static resources.

Asset paths must work in local and hosted contexts.

CSS and JavaScript updates must account for browser caching during local verification.

---

## Invariant 1 — Base Path Must Be Preserved

Local routing must preserve the project base path:

`/sports-warehouse-home-page/`

Codex must not generate paths that assume the site is hosted at domain root unless the current file already uses an approved base-path helper or deployment-aware convention.

Dangerous assumptions include:

- `/admin/...` when local base path is required
- `/css/...` when the project base path is required
- `/js/...` when the project base path is required
- endpoint fetches that ignore `window.BASE_URL` or existing base helpers

If base-path handling is unclear, inspect existing conventions before implementation.

---

## Invariant 2 — Admin Segment Must Not Be Lost

Admin route links must preserve the `admin` segment where required.

Expected admin path shape:

`/sports-warehouse-home-page/admin/...`

Incorrect examples:

`/sports-warehouse-home-page/hero-candidates.php`

`/sports-warehouse-home-page/hero-manager.php`

These incorrectly drop the admin segment.

Codex must browser-test admin links when modifying admin URL logic.

---

## Invariant 3 — Admin Segment Must Not Be Duplicated

Admin route links must not duplicate the `admin` segment.

Incorrect examples:

`/sports-warehouse-home-page/admin/admin/hero-candidates.php`

`/sports-warehouse-home-page/admin/admin/hero-manager.php`

Duplication usually indicates incorrect relative-path resolution from an already-admin page.

When resolving endpoint-provided admin paths, normalize deliberately rather than relying on browser-relative defaults.

---

## Invariant 4 — Route Construction Must Follow Existing Helpers

If existing code uses a base helper such as `BASE_URL`, `window.BASE_URL`, or an equivalent project convention, Codex must follow that convention.

Codex must not introduce a parallel route-construction pattern without explicit approval.

Allowed approach:

- inspect the existing file
- identify how it currently builds URLs
- extend that pattern narrowly

Forbidden approach:

- introduce a new global route helper casually
- hard-code local-only URLs
- mix root-relative and relative paths inconsistently
- infer deployment paths without evidence

---

## Invariant 5 — JavaScript Fetch Paths Must Be Verified

JavaScript fetch calls are route-sensitive.

When Codex modifies or adds a fetch call, it must ensure that the resolved URL works from the page where the script runs.

For admin pages, fetch calls must resolve correctly from an admin route context.

If a fetch path is built using endpoint-provided data, normalize the URL before assigning it to `href` or passing it to `fetch`.

---

## Invariant 6 — Endpoint Consumers Must Respect Endpoint Contracts

UI code must not force an endpoint to return data outside its intended mode.

For Hero Manager shortlist scan mode:

- product-list UI may consume `recommended_candidates`
- product-list UI must not consume `all_candidates`
- full candidate review belongs in challenge mode

Challenge mode route shape:

`admin/hero-candidates.php?item_id=ITEM_ID&include_shortlist=1`

Codex must not change endpoint contracts as a shortcut for UI rendering.

---

## Invariant 7 — Relative URLs Are High Risk in Admin Pages

Relative URLs from admin pages can resolve unexpectedly.

Example risk:

a link written as `hero-candidates.php` from an admin page may resolve differently from a link written as `admin/hero-candidates.php`.

A link written as `admin/hero-candidates.php` from inside an admin page may accidentally produce `/admin/admin/hero-candidates.php` if treated as relative.

Admin route generation must be explicit.

---

## Invariant 8 — Frontend Pretty Routes Must Not Be Broken by Admin Work

Admin implementation must not modify frontend pretty routing unless explicitly in scope.

Frontend route behaviour may include clean public-facing paths.

Admin changes must not alter `.htaccess`, frontend dispatch logic, or catalogue routing without explicit approval.

If a change touches route configuration, inspect frontend route behaviour before modifying it.

---

## Invariant 9 — Asset Paths Must Remain Deployment-Aware

CSS, JavaScript, and image paths must work in the local base-path context.

Codex must not replace project-aware asset paths with root-only paths.

If assets fail after merge, verification must include:

- hard refresh
- direct asset URL check
- browser console check
- network tab inspection where necessary

---

## Invariant 10 — Redirects Must Be Explicit

Redirect behaviour must be explicit and narrow.

Codex must not introduce redirects to compensate for incorrect path generation.

Redirects may hide route errors and make local/hosted behaviour diverge.

If a link resolves incorrectly, fix the link construction rather than adding a redirect workaround.

---

## Invariant 11 — Routing Changes Must Be Auditable

Any route-affecting change must make clear:

- what route is affected
- what page or endpoint consumes it
- whether it is frontend or admin
- whether it relies on `BASE_URL`, `window.BASE_URL`, or another helper
- how it was verified locally

Route changes must not be hidden inside unrelated refactors.

---

## Invariant 12 — Guardrailed Velocity Applies Only to Known Route Surfaces

Codex may implement bounded route-sensitive changes without a separate planning-only stage when:

- the affected route is already known
- no global routing convention is changed
- no `.htaccess` or router-level change is required
- the change is local to a page, endpoint consumer, or UI link
- browser verification is available

Planning is required when:

- route authority is unclear
- pretty URL behaviour is affected
- admin/frontend boundaries are affected
- deployment assumptions are changing
- route configuration files are being modified

---

## Procedural Rule — URL Change Verification

When changing a URL, link, redirect, fetch path, form action, script path, stylesheet path, or image path:

1. identify the source page
2. identify the intended target
3. identify whether the source page is frontend or admin
4. preserve the base path
5. preserve or deliberately omit the admin segment according to target location
6. test the generated URL in the browser
7. check for broken console or network errors when JavaScript is involved

Route behaviour is accepted only after runtime verification.

---

## Procedural Rule — Admin Link Normalization

When an endpoint supplies a route such as:

`admin/hero-candidates.php?item_id=79&include_shortlist=1`

and the consuming page runs under an admin URL, Codex must avoid browser-relative duplication.

Correct normalized result:

`/sports-warehouse-home-page/admin/hero-candidates.php?item_id=79&include_shortlist=1`

Incorrect results:

`/sports-warehouse-home-page/hero-candidates.php?item_id=79&include_shortlist=1`

`/sports-warehouse-home-page/admin/admin/hero-candidates.php?item_id=79&include_shortlist=1`

Normalization must be explicit.

---

## Procedural Rule — JavaScript Route Handling

If JavaScript constructs route URLs:

1. inspect whether `window.BASE_URL` exists
2. use existing project convention where available
3. preserve absolute URLs
4. preserve root-relative URLs
5. normalize known relative admin paths deliberately
6. avoid unsafe string interpolation where endpoint values are inserted into DOM attributes
7. run JavaScript syntax validation when possible
8. browser-test actual links

This rule applies to shortlist previews, admin actions, endpoint fetches, and UI-generated links.

---

## Known Gaps and Open Questions

The project may still require future clarification around:

- whether all admin endpoint URLs should be root-relative or base-helper-generated
- whether endpoint payloads should return normalized URLs or route fragments
- whether route helpers should be centralized further
- whether frontend and admin JavaScript should share a URL utility
- whether hosted deployment path differs from local Laragon base path

These gaps do not authorize route guessing.

They identify areas where explicit review may be needed.

---

## Non-Goals

This README does not authorize:

- global route redesign
- `.htaccess` rewrites without approval
- admin/frontend route blending
- endpoint contract expansion
- redirect workarounds for broken links
- root-deployment assumptions
- local-only hard-coding
- unlimited micro-planning for simple bounded route fixes

---

## Guiding Principles

Routing must remain:

- explicit
- base-path aware
- admin-aware
- deployment-conscious
- browser-verified
- auditable
- boring rather than clever

Guardrailed implementation velocity may increase delivery speed.

It must not weaken route correctness.

If a route-sensitive implementation is bounded and verifiable, implement it.

If route authority is unclear, stop and review the relevant invariant before changing code.
```

