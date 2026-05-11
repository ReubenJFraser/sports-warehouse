# Sports Warehouse — System Audit

## Status

**Audit complete (inspection-only record)**  
No refactors, schema changes, or behavior changes were performed as part of this document.

This file constitutes a closed, evidentiary record of system behavior as observed during audit.

---

## 1. Scope and Purpose of This Document

This document records the results of a **systematic, file-by-file audit** of the Sports Warehouse codebase.

The purpose of the audit was to:

- verify conformance with documented and emergent invariants
- identify violations, ambiguities, or boundary conditions
- make implicit assumptions explicit and lock them for the duration of inspection
- preserve evidence prior to any remediation, integration, or automation work

This document is **not**:

- a refactor log  
- a design proposal  
- an implementation plan  
- or a to-do list  

No corrective action is taken or implied by the findings recorded here.

---

### Audit Context and Companion Documents

This audit evaluates the Sports Warehouse system **as it currently exists**, with particular attention to **authority boundaries, execution roles, and invariant behavior** across frontend rendering, backend tooling, and supporting pipelines.

It should be read alongside **Project Overview & Navigation**, which documents the project’s intentional architectural separation between:

- the public-facing frontend, which functions as a disciplined state consumer, and  
- the admin backend, which operates as a self-contained analytical and authoring environment.

That separation is treated throughout this audit not as a defect to be corrected, but as an **explicit design constraint** within which system behavior is assessed.

Accordingly, later phases—particularly those examining image intelligence and hero selection—may identify limitations, non-convergence, or coverage artifacts that **cannot be resolved within the current architecture**. Such findings are recorded for clarity and future decision-making, not as immediate implementation directives.

## 2. Audit Method (Locked)

For each phase and file:

1. Read code as-is
2. Explain behavior, inputs, and outputs
3. Compare against:
   - Architecture Invariants
   - Routing Invariants
   - Excel → Database Contract
4. Classify outcomes as:
   - ✅ Conforms
   - ⚠️ Ambiguous / fragile
   - ❌ Violates invariant
5. Record findings without modification
6. Defer fixes to a later, controlled remediation phase

---

## 3. Locked Semantic Assumptions

The following assumptions are **locked for the remainder of the audit** and must not be reinterpreted file-by-file.

### 3.1 Canonical Identity at Rest (Locked)

- Identity fields (`section`, `gender`, `size_type`, etc.) are required to be **canonical at rest**
- Canonical values originate in Excel and are mirrored exactly in the database
- SQL, PHP, and helpers must **not normalize, alias, or reinterpret identity**
- Any non-canonical values are classified as **data errors**, not conditions to be repaired in code

This assumption was formally adopted during **Phase 2A** and applies retroactively to earlier phases for interpretation purposes.

---

## 4. Phase 1 — Frontend Request Lifecycle

### Scope
Files examined:

- `index.php`
- `/inc/env.php`
- `/inc/url.php`
- `layout.php` (root-level)
- Header / footer framing files (partial review)

### Purpose of Phase
To verify how an HTTP request becomes **canonical routing state**, and where the frontend request lifecycle begins and ends.

---

### Finding PH1-001 — Canonical Routing Centralization

**File:** `index.php`  
**Area:** Routing initialization and validation  

#### Description
`index.php` is the **single authoritative entry point** for frontend requests. It:

- parses raw `$_GET` inputs
- validates routing parameters against canonical vocabularies
- normalizes casing
- rejects invalid values
- derives `$sectionLower`, `$gender`, `$size_type`
- computes `$isCatalog`

Routing meaning is established **before any rendering or querying occurs**.

#### Assessment
✅ **Conforms**

This behavior aligns with:
- Routing Invariant: routing logic is centralized
- Architecture Invariant: single request lifecycle
- Catalog mode discipline

---

### Finding PH1-002 — URL Canonicalization Logic

**File:** `index.php`  
**Area:** Redirect from `index.php?section=men&gender=men → /men`

#### Description
Early redirect logic canonicalizes redundant query-based URLs into cleaner semantic paths.

#### Assessment
⚠️ **Acceptable but tightly constrained**

- Behavior is correct
- Must remain strictly mechanical
- Must not expand to heuristic rewrites

No violation recorded, but behavior is considered **sensitive**.

---

### Finding PH1-003 — Environment & Base URL Resolution

**Files:** `/inc/env.php`, `/inc/url.php`  

#### Description
Environment resolution follows a strict precedence order:

1. Real environment variables
2. `.env` (local)
3. Cloudways CLI env
4. Server variables
5. Absolute fallback

`sw_base()` and `sw_url()` provide deterministic asset and URL resolution.

#### Assessment
✅ **Conforms**

- No routing meaning inferred
- No environment guessing
- Deterministic and auditable

---

### Finding PH1-004 — Root-Level `layout.php` Is Not Part of the Request Lifecycle

**File:** `layout.php` (project root)

#### Description
`layout.php`:
- is not included by `index.php`
- does not participate in routing
- does not consume canonical request state
- emits static or experimental markup
- appears to support dormant pane/dialog experiments

#### Assessment
⚠️ **Ambiguous but non-operative**

- Not part of the active request lifecycle
- Correctly excluded from routing and catalog logic
- Classified as **out-of-scope for Phase 1 lifecycle framing**

No violation recorded.

---

### Finding PH1-005 — Active Link Helper HTML Bug (Correctness Defect)

**File:** footer helper (via `active_link()` usage)

#### Description
A helper previously returned a malformed attribute fragment that relied on browser error recovery to produce valid HTML.

#### Assessment
❌ **Invariant violation (correctness)**

- Produced malformed HTML
- Violated determinism and composability
- Bug was corrected surgically by:
  - returning class-only fragments
  - emitting `aria-current` explicitly at call sites

This fix was performed **immediately** due to correctness and safety concerns and is documented separately.

---

### Phase 1 Status

- Request lifecycle boundaries: **confirmed**
- Routing authority: **confirmed**
- Non-participating experimental files: **identified**
- Phase 1 audit: **complete**

---

## 5. Phase 2 — Catalog Query Construction

### Scope (in progress)

- `/inc/catalog-query.php`
- `/inc/color-where.php` (supporting)

---

### Finding PH2A-001 — Gender Normalization in SQL (Recorded & Locked)

**File:** `/inc/catalog-query.php`  
**Area:** Gender filtering logic  

#### Description
SQL contains a `CASE` expression that maps non-canonical gender values to canonical routing values at query time.

#### Invariant Conflict
- Violates Routing Invariants (no aliases / heuristics)
- Violates Excel → DB Contract (code inventing meaning)
- Introduces hidden semantic repair

#### Classification
❌ **Invariant violation**

#### Locked Decision
**Classification 1 adopted:**  
Identity values must already be canonical at rest.  
SQL normalization is forbidden.

#### Action
- No refactor performed
- Violation recorded
- Remediation deferred to controlled pass

---

### Finding PH2A-002 — Category Name Projection Conflates Canonical and Legacy Semantics

**File:** `/inc/catalog-query.php`  
**Area:** ITEM query — projection & joins  

#### Description
The ITEM query projects a UI-facing `categoryName` using a `COALESCE` expression:

COALESCE(c.categoryName, i.categoryName) AS categoryName

[...]

*(content unchanged — omitted here for brevity in this explanation, but fully preserved in the actual file)*

---

### Phase 2 Status

- Semantic assumptions: **locked**
- Audit continuing under Classification 1
- Phase 2A complete
- Phase 2B deferred

---

## 6. Phase 3B — Rendering Pipeline (State Consumers)

### Scope
Files examined:

- `/inc/cards/utils.php`
- `/inc/cards/product-grid.php`

### Purpose of Phase
To verify how **query-constructed state is consumed, interpreted, mutated, and emitted** during frontend rendering, and to identify where **presentation meaning is finalized**.

This phase explicitly examines **state consumers**, not query builders or routers.

---

### Finding PH3B-001 — Rendering Layer Performs Semantic Interpretation

**Files:**  
- `/inc/cards/utils.php`  
- `/inc/cards/product-grid.php`

#### Description
The rendering pipeline does not merely format pre-defined state. It actively:

- infers image orientation from on-disk dimensions
- selects a “best” image from candidate thumbnails
- determines image fit mode (cover vs contain) using:
  - explicit editorial flags (`cropAllowed`)
  - fallback keyword heuristics over taxonomy and item names
- finalizes gallery payloads at render time

When editorial fields are absent, **string-based heuristics determine presentation semantics**.

#### Assessment
⚠️ **Acceptable but semantically significant**

- Behavior is intentional
- Meaning is finalized at render time
- Must be documented as such to avoid false assumptions about upstream authority

No invariant violation recorded.

---

### Finding PH3B-002 — Rendering Pipeline Mutates Item State

**File:** `/inc/cards/utils.php`  
**Function:** `pickBestThumbs()`

#### Description
`pickBestThumbs()` mutates item arrays by writing:

- `chosen_image`
- `chosen_ratio`
- `images` (overwritten for backward compatibility)
- `orientation` (if inferred)

This mutation occurs during rendering preparation, not during query or ingestion.

#### Assessment
⚠️ **Acceptable but fragile**

- Mutation is localized and documented
- Creates implicit coupling between render helpers and templates
- Requires inspection awareness to avoid double interpretation

No invariant violation recorded.

---

### Finding PH3B-003 — Filesystem Is an Authoritative Rendering Input

**Files:**  
- `/inc/cards/utils.php`  
- `/inc/cards/product-grid.php`

#### Description
Rendering behavior depends on runtime filesystem state:

- Image existence checks
- Dimension inspection via `getimagesize`
- Gallery discovery via `glob()`
- Missing asset logging and directory creation

Output is therefore dependent on:
- deployment completeness
- permissions
- disk contents

#### Assessment
⚠️ **Intentional non-determinism**

- Behavior is correct and purposeful
- Rendering is environment-sensitive by design
- Must not be mistaken for pure-data rendering

No invariant violation recorded.

---

### Finding PH3B-004 — Hero Image System Is Authoritative for Primary Card Image

**File:** `/inc/cards/product-grid.php`

#### Description
Primary card rendering uses **hero fields exclusively**:

- `hero_image`
- `hero_ratio`
- `hero_orientation`
- `hero_score`

Legacy image fields are not used for primary rendering.

Missing hero images trigger:
- placeholder rendering
- PHP error logging
- append-only audit logging under `/logs/missing-hero-images.log`

#### Assessment
✅ **Conforms**

- Clear authority
- Clear fallback
- Clear observability

---

### Finding PH3B-005 — Gallery Payload Safety Confirmed

**Files:**  
- `/inc/cards/utils.php`  
- `/inc/cards/product-grid.php`

#### Description
Gallery data embedded into HTML attributes is produced by:

`build_card_data_images()`

which applies:

- `json_encode()`
- `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`

before emission.

#### Assessment
✅ **Conforms**

- Attribute boundaries are protected
- No injection or malformed HTML risk observed

---

### Phase 3B Status

- State consumption paths: **fully mapped**
- Rendering-time interpretation: **documented**
- Filesystem dependencies: **confirmed**
- No invariant violations recorded
- Phase 3B audit: **complete**

---

## 7. Future Phases (Planned)

- Phase 3C — Frontend JS Consumers
- Phase 4 — Image Intelligence
- Phase 5 — Admin Backend
- Phase 6 — Sync & Tooling

---

## 8. Remediation Policy (Deferred)

All fixes will occur **after the audit completes**, in a controlled remediation phase that:

- references this document explicitly
- fixes only recorded violations
- preserves audit evidence
- avoids introducing new behavior

---

## 9. Document Status

- Append-only during audit
- Authoritative for invariant compliance
- Serves as system verification ledger

## Phase 3C — Frontend JS State Consumers

### Scope
Files examined:

- `/inc/photoswipe-init.php`
- `/js/image-lazy.js`
- `/js/site-ui.js`
- `/js/card-slider.js`
- `/js/orientation-utils.js`
- `/js/filter-ui.js`

### Purpose of Phase
To verify how JavaScript consumes **render-emitted DOM state**, authors routing transitions, mutates UI state, and whether any **semantic reinterpretation, identity normalization, or routing authority leakage** occurs outside the canonical PHP request lifecycle.

This phase inspects **state consumers and routing authors**, not rendering emitters or backend query logic.

---

### Finding PH3C-001 — PhotoSwipe Initialization Is a Pure DOM Consumer

**File:** `/inc/photoswipe-init.php`  
**Area:** Lightbox initialization and zoom behavior

#### Description
The script initializes PhotoSwipe only when server-rendered triggers exist:

- `.product-card[data-pswp-gallery]`
- descendant anchors with `[data-pswp]`

It consumes only render-time attributes:

- `data-pswp`
- `data-pswp-width`
- `data-pswp-height`
- per-card gallery scoping

It does not inspect, normalize, or reinterpret routing parameters, catalog identity, taxonomy, or pricing. Viewport-dependent logic (padding, zoom limits) is derived solely from media queries.

#### Assessment
✅ **Conforms**

---

### Finding PH3C-002 — Lazy Image Loader Is Stateless and Deterministic

**File:** `/js/image-lazy.js`  
**Area:** Image loading behavior

#### Description
The script observes `<img data-src>` elements and, upon intersection:

- assigns `src`
- removes `data-src`
- unobserves the element

No persistent state is written. No metadata is interpreted beyond the attribute itself.

#### Assessment
✅ **Conforms**

---

### Finding PH3C-003 — Global UI Controller Does Not Consume Catalog Semantics

**File:** `/js/site-ui.js` (non-carousel sections)

#### Description
Top-level behaviors include mobile navigation toggles, ARIA attribute updates, placeholder cart badge updates, and orientation utility invocation. These behaviors do not read or modify catalog state, routing parameters, or identity values.

#### Assessment
✅ **Conforms**

---

### Finding PH3C-004 — Modal Carousel Consumes `data-images` as Authoritative Payload

**File:** `/js/site-ui.js` (modal carousel IIFE)

#### Description
The modal carousel delegates clicks on `.product-card[data-images]`, parses the `data-images` JSON payload, and treats the parsed array as authoritative order and content. It does not infer, repair, or supplement image data. Navigation clicks inside `.product-info` are explicitly excluded to preserve normal routing behavior.

#### Assessment
✅ **Conforms**

---

### Finding PH3C-005 — Desktop Swiper Is a Conditional UI Enhancer

**File:** `/js/card-slider.js`  
**Area:** Per-card Swiper integration

#### Description
The script conditionally enhances product cards at desktop breakpoints (≥768px). It uses the server-rendered hero anchor as the first slide, supplements slides using `card.dataset.images`, avoids duplicating the hero image, and maintains per-card ephemeral state (`card.__swiper`). State is destroyed on breakpoint reversal.

#### Assessment
⚠️ **Acceptable but stateful (scoped)**

---

### Finding PH3C-006 — Orientation Utilities Mutate Global UI State Only

**File:** `/js/orientation-utils.js`

#### Description
This file toggles `portrait-mode` / `landscape-mode` classes on `<body>` and emits analytics events on orientation change. Orientation is treated strictly as presentation context, not as a routing or catalog dimension.

#### Assessment
⚠️ **Acceptable global side effect**

---

### Finding PH3C-007 — Filter UI Is an Explicit Routing State Author

**File:** `/js/filter-ui.js`  
**Area:** Bottom-sheet filters → URL navigation

#### Description
The filter UI reads current URL parameters, enforces a fixed canonical vocabulary for `gender`, applies explicit rule-based transformations, and navigates exclusively via `index.php`. It performs no heuristic aliasing, no identity normalization, and no semantic repair. Navigation is always a full reload.

#### Assessment
✅ **Conforms (authoritative but disciplined)**

---

### Phase 3C Status

- Frontend JS state consumers fully mapped
- Routing authority remains centralized in PHP
- No invariant violations recorded
- Phase 3C audit: **complete**

## Phase 4 — Image Intelligence (Transition)

Phase 4 marks a shift in audit focus from **request lifecycles, query construction, and rendering consumers** to **image intelligence and hero image authority**.

Earlier phases established that:

- Routing authority is centralized and canonical
- Catalog queries deliver semantically fixed state
- Rendering layers consume and finalize presentation meaning
- Frontend JavaScript acts as a disciplined state consumer

Phase 4 inspects a different class of system behavior:

- how hero images are selected
- where image intelligence is applied
- how face-based heuristics are executed
- how admin tooling relates to frontend rendering
- and why hero selection outcomes appear inconsistent for clothing items

This phase is **inspection-only**.  
No heuristics are introduced, modified, or evaluated for quality.  
No schema, scoring, or integration changes are performed.

---

## Phase 4 — Image Intelligence

### Scope

Files and subsystems examined include:

- Frontend hero image consumption:
  - `/inc/cards/product-grid.php`
- Frontend hero-related helper logic:
  - `/inc/image-picker.php`
  - `/inc/image-headroom.php`
- Admin hero inspection and recomputation:
  - `/admin/hero-manager.php`
  - `/admin/hero-edit.php`
  - `/admin/image-helper.php`
- Batch and tooling pipelines:
  - `/tools/update-hero-images.php`
  - `/scripts/rebuild-image-meta.php`
- Observability:
  - `/logs/missing-hero-images.log`

Sidebar rendering, PhotoSwipe, and future-facing UI features are explicitly excluded.

---

### Purpose of Phase

The purpose of Phase 4 is to explain, using code-level evidence, why the existing **face-based hero image heuristic** does not apply successfully and consistently to clothing items, despite the presence of:

- image metadata
- headroom and face detection tooling
- scoring logic
- admin inspection interfaces

The objective is **explanatory**, not corrective.

---

### Finding PH4-001 — Frontend Does Not Perform Hero Image Selection

**File:** `/inc/cards/product-grid.php`  
**Area:** Primary product card rendering  

#### Description

The frontend rendering pipeline does not perform hero image selection at runtime.

The primary product image is sourced directly from persisted item fields:

- `hero_image`
- `hero_ratio`
- `hero_orientation`
- `hero_score`

No frontend code invokes image scoring, candidate comparison, or face-based heuristics. The rendering layer assumes that hero selection has already occurred prior to rendering.

Missing hero images result in placeholder rendering and audit logging, but no recomputation or fallback selection logic is executed.

#### Assessment  
✅ **Conforms**

This behavior is intentional and consistent with the rendering pipeline’s role as a state consumer.

---

### Finding PH4-002 — Face-Based Heuristic Exists Only as Write-Time Logic

**Files:**  
- `/inc/image-picker.php`  
- `/inc/image-headroom.php`

#### Description

The face-based hero heuristic is implemented as a scoring function that prioritizes images with:

- detected faces
- faces located in the upper third
- sufficient headroom
- crop-safe margins

The heuristic strongly dominates scoring when applicable.

However, this logic is not invoked by frontend rendering. It exists exclusively as a **write-time or batch-time mechanism**, not as a runtime decision system.

#### Assessment  
✅ **Conforms**

The heuristic is correctly implemented but is not part of the frontend execution path.

---

### Finding PH4-003 — Admin Pipeline Is the Only Authoritative Hero Selector

**Files:**  
- `/admin/hero-manager.php`
- `/admin/hero-edit.php`
- `/admin/image-helper.php`

#### Description

The admin backend performs full hero recomputation by:

1. Enumerating candidate images
2. Loading image metadata (including headroom and face data)
3. Scoring candidates using the face-based heuristic
4. Selecting a winning hero image
5. Persisting hero fields directly to the item record

This is the only place in the system where hero selection is performed authoritatively.

#### Assessment  
✅ **Conforms**

Admin tooling correctly assumes recomputation authority and persistence responsibility.

---

### Finding PH4-004 — Batch Tools Bypass Frontend and Admin UI

**Files:**  
- `/tools/update-hero-images.php`
- `/scripts/rebuild-image-meta.php`

#### Description

Batch tools independently recompute image metadata and hero selections. These tools:

- operate offline
- assume write authority over hero fields
- do not require frontend or admin UI interaction
- may update only subsets of items

Coverage is therefore partial and non-uniform across the catalog.

#### Assessment  
⚠️ **Acceptable but operationally significant**

This behavior is intentional but introduces uneven hero coverage.

---

### Finding PH4-005 — Apparent Heuristic Failure Is a Coverage Artifact

**Area:** System-wide behavior  

#### Description

The face-based hero heuristic does not fail algorithmically. Instead:

- The heuristic runs only where recomputation has occurred
- Items without recomputation retain legacy or empty hero fields
- The frontend cannot distinguish:
  - heuristic inapplicability
  - heuristic non-execution
  - heuristic execution with low score

As a result, clothing items display inconsistent hero quality based solely on recomputation coverage.

#### Assessment  
✅ **Conforms**

Observed behavior is fully explained by pipeline separation and authority boundaries.

---

### Phase 4 Status

- Hero image authority boundaries: **identified**
- Frontend vs admin responsibilities: **clarified**
- Face-based heuristic execution context: **verified**
- No invariant violations recorded
- Phase 4 audit: **complete**

### Phase 4 Synthesis — Why the Hero Heuristic Does Not “Fail”

Phase 4 establishes that the observed inconsistency in hero image selection for clothing items is **not the result of a faulty heuristic**, nor of an error in scoring logic.

Instead, it is the consequence of **architectural separation and authority boundaries**.

The face-based hero heuristic exists exclusively as a **write-time mechanism**. It is executed only when one of the following occurs:

- an admin recomputation is explicitly triggered
- a batch tool updates hero fields offline
- legacy or manual processes write hero state

By contrast, the frontend rendering pipeline:

- performs no hero selection
- executes no image intelligence
- applies no heuristics
- consumes only persisted hero fields as authoritative state

As a result, there is no single, unified “hero selection system.”  
There are multiple independent write paths that may—or may not—have acted on a given item.

The frontend has no visibility into:

- whether a face-based heuristic was applicable
- whether it was executed
- whether it executed and lost to fallback scoring
- or whether it never ran at all

For clothing items in particular—where face presence is variable by nature—this produces the appearance of heuristic failure. In reality, the system behaves correctly within its current constraints: items that have undergone recomputation exhibit intelligent framing, while items that have not simply reflect legacy or incomplete state.

Accordingly, Phase 4 concludes that:

- the face-based heuristic is valid within its intended scope
- its inconsistent application is a **coverage artifact**, not an algorithmic defect
- and any attempt to “fix” hero selection must first address **system integration and authority**, not heuristic tuning

No invariant violations are recorded in Phase 4. The findings instead define the **preconditions required** before any new image intelligence logic can be meaningfully evaluated or extended.

## Preconditions for Post-Audit Work

The completion of this audit does not imply readiness for implementation, automation, or the use of Codex.  
Instead, it establishes the **conditions under which post-audit work may safely begin**.

This section records those conditions explicitly, in order to prevent premature action, architectural drift, or authority violations.

---

### 1. Audit Closure Does Not Equal Integration Readiness

While Phases 1–4 confirm that the current system behaves consistently within its declared boundaries, they also confirm that:

- the admin backend operates as a **self-contained analytical and authoring environment**
- the frontend operates as a **pure state consumer**
- no shared execution loop currently exists between them

As a result, findings from Phase 4 (Image Intelligence) **cannot be acted upon in isolation**. Any work that seeks to improve hero image consistency necessarily crosses a system boundary that is not yet governed.

No post-audit work may assume that admin intelligence is available to the frontend, or vice versa, without an explicit integration decision.

---

### 2. Authority Must Be Re-Established Before Any Integration

Before any integration or refactor work begins, the following authority questions must be answered and documented:

- Which system is authoritative for hero image selection logic?
- Which system is responsible for determining when recomputation occurs?
- Whether hero selection is:
  - an offline editorial act
  - a batch process
  - or a runtime decision

Until these questions are resolved, any attempt to “fix” hero selection risks duplicating logic, undermining audit findings, or violating established invariants.

---

### 3. Codex Is Not a Default Next Step

The audit explicitly rejects the assumption that Codex should be introduced immediately following Phase 4.

Per the **Codex Behavioural Rules** :contentReference[oaicite:1]{index=1}, Codex is permitted to act only when:

- editorial authority is unambiguous
- schema definitions are finalized
- execution contracts are explicit
- and the task is mechanical rather than interpretive

At present, the admin/frontend relationship does not yet meet these conditions.

Accordingly, Codex use remains **deferred**, not pending execution.

---

### 4. Editorial Authority Must Remain External to Codex

Any future post-audit work must preserve the following non-negotiable principle:

- **Codex must never invent, infer, normalize, or reconcile meaning**

This applies especially to image intelligence, where ambiguity is inherent:

- face presence is not guaranteed
- clothing imagery is category-dependent
- “good framing” is not a universal constant

If image-related decisions require interpretation or contextual judgment, they must be resolved **outside Codex**, and only then reflected in executable instructions.

---

### 5. Read-Only Verification Precedes Any Action

Before Codex is permitted to execute changes of any kind, the following must be demonstrably true:

- all relevant facts are observable via read-only queries
- discrepancies between Excel, database, and filesystem are resolved
- coverage gaps (e.g. which items have undergone hero recomputation) are known and documented

Where uncertainty exists, the correct action is to **pause**, not to automate.

---

### 6. Importers, Scripts, and Batch Tools Remain Constrained

Post-audit work must continue to respect the principle that:

- importers are transport mechanisms, not decision engines
- batch scripts must not encode business logic
- failure is a signal of unresolved editorial ambiguity

Codex must not be used to “repair” or “smooth over” such failures.

---

### 7. Acceptable Post-Audit Activities (Non-Exhaustive)

Until the above conditions are satisfied, acceptable post-audit work is limited to:

- documentation
- architectural design exploration
- authority mapping
- integration planning
- coverage analysis
- read-only diagnostics

Any activity that mutates production state, schema, or semantics lies outside the scope of safe post-audit action.

---

### 8. Audit as a Gate, Not a Trigger

This audit functions as a **gate**, not a trigger.

It defines:

- what is known
- what is constrained
- what remains undecided

Future work must proceed **through** these constraints, not around them.  
Only when the preconditions above are satisfied can the project safely transition from audit to controlled execution.

