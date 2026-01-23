# Sports Warehouse — Project Overview & Navigation

## 1. What This Project Is

**Sports Warehouse** is a full-stack e-commerce catalogue and design-systems project developed as part of a formal website development assignment and deliberately extended far beyond the course requirements.

While the original brief specified a limited product catalogue and standard presentation, this project evolved into a **technical showcase** focused on:

- robust data governance
- automated image analysis and ranking
- scalable frontend architecture
- human-override admin tooling
- experimentation with AI-assisted visual enhancement

The site is not a mock-up. It is a functioning system designed to demonstrate *how real-world retail platforms are actually built, maintained, and evolved*.

---

## 2. Core Design Philosophy

This project is guided by four principles:

### 2.1 Automation first, human override always

Wherever possible, the system makes an intelligent default decision — but never locks the human out.

Importantly, automation in Sports Warehouse is **explicitly governed**.  
Automated decisions exist to assist, not to replace, editorial judgment.

### 2.2 Data is editorial, not incidental

Product data is treated as authored content with clear rules, naming conventions, and contracts between systems.

This includes image selection state, which is treated as **persisted editorial data**, not a transient UI concern.

### 2.3 Architecture over features

The value of the project lies more in *how* things are structured than in the sheer number of visible features.

Subsystems are intentionally separated into:
- authority (what may write)
- governance (how humans intervene)
- presentation (what may only read)

### 2.4 Showcase over minimal compliance

The project intentionally exceeds assignment scope to demonstrate independent problem-solving, research, and technical depth.

---

## 3. High-Level System Overview

At a conceptual level, the system consists of five interacting layers:

### 3.1 Product Data Layer

- Canonical product data is authored in **Excel**
- Excel is the editorial source of truth
- Data is imported into MySQL with strict schema alignment
- Fields such as `external_item_id`, `series_slug`, and `assignment_source` exist to support:
  - stable identifiers
  - grouping logic
  - governance between course vs custom items

📄 See: **Excel — Database Contract**

---

### 3.2 Database Layer

- MySQL database backing both frontend and admin
- Designed to support:
  - deterministic product identity
  - image metadata storage
  - hero image persistence
  - explicit admin overrides without data loss
- Schema evolution is intentional and checklist-driven

Hero image state is persisted and authoritative at this layer.
No hero selection or substitution occurs implicitly at read time.

📄 See: **Codex — Architecture Invariants**

---

### 3.3 Image Analysis & Hero Selection System

One of the most distinctive features of the project.

Each product can have multiple images, which are:

- automatically analyzed
- scored and ranked for hero suitability
- written to persisted hero fields

Scoring criteria include (but are not limited to):

- subject position (e.g. headroom)
- orientation
- crop safety
- image completeness

Crucially, hero selection in Sports Warehouse operates under an **explicit authority and governance model**:

- Automated selection produces a default hero
- Human editorial intent may override automation
- Governance constraints may restrict future automation
- No frontend or UI layer may reinterpret hero authority

Automation, override, and governance are deliberately separated to ensure that hero behavior is **deterministic, auditable, and explainable**.

📄 See: **Hero Image Authority Contract**  
📄 See: **Hero Image Governance — Admin Workflow**

---

### 3.4 Frontend Presentation & Navigation Model

The frontend is intentionally implemented as a **single-page, state-driven application**.

Key characteristics:

- There is one physical frontend entry point (`index.php`)
- Navigation does not move between pages
- Content changes are driven by **routing state and filters**
- URL parameters encode meaning, not location

Frontend behavior includes:

- consistent product card layouts
- mixed-aspect image support
- responsive filtering
- sidebar-driven navigation
- brand- and segment-specific content modules

Important clarification:

- Terms like *“men”*, *“women”*, *“kids”*, and *“plus”* represent **catalog segments**, not separate pages
- Filters refine the active segment; they do not redefine it
- Routing semantics are governed by **Codex — Routing Invariants**

The frontend is strictly **non-authoritative** with respect to hero images.
It renders exactly what is stored and never computes alternatives.

📄 See: **Codex — Routing Invariants**

---

### 3.5 Admin & Debug Tooling

The project includes a substantial internal tooling layer, including:

- hero image management interfaces
- image audit tools
- category sync utilities
- environment diagnostics
- controlled batch update scripts

These tools exist because **real systems require observability**.

They are not “nice extras”; they are essential to safely evolving a live site.

#### 3.5.1 Admin Diagnostic Helpers (`/admin/inc/`)

As the admin system evolved beyond simple tooling into an authoritative diagnostic surface, shared read-only logic was extracted into `/admin/inc/`.

This folder contains **admin-only, non-UI helpers** whose purpose is to:

- report system state and catalog health
- provide consistent, authoritative diagnostics
- avoid duplication between admin tools
- preserve strict separation between visibility and enforcement

Examples include:

- `hero-status.php` — canonical read-only hero coverage and state reporting

These helpers:
- perform no writes
- introduce no automation
- exist to ensure observability without triggering enforcement

#### 3.5.2 Admin Evolution Model (Visibility → Enforcement → Automation)

Admin tooling follows a strict progression:

1. **Visibility**
   - Truthful reporting of persisted state
   - No writes, no heuristics
   - Purpose: establish trust

2. **Enforcement**
   - Authority checks and guardrails
   - Still no automation
   - Purpose: prevent invalid writes

3. **Automation**
   - Batch operations and recomputation
   - Introduced only after visibility and enforcement are proven stable

Hero image tooling follows this model explicitly.
Governance precedes automation.

---

## 4. Experimental & Research Components

Some parts of the project are explicitly exploratory.

### 4.1 AI-Generated Shadows for Product Images

A research thread investigates whether **AI or 3D rendering can generate realistic shadows** for transparent PNG product images at scale.

Approaches explored include:

- Blender shadow-catcher pipelines
- AI relighting tools
- SaaS shadow generators

This work is documented separately and is **not production-locked**.

---

## 5. Navigation Guide (Where to Look)

### 5.1 If you want to understand rules and authority

→ **Hero Image Authority Contract**  
→ **Codex — Architecture Invariants**  
→ **Codex — Routing Invariants**

### 5.2 If you want to understand admin behavior

→ **Hero Image Governance — Admin Workflow**  
→ `/admin/hero-*`

### 5.3 If you want to modify or import data

→ **Excel — Database Contract**

### 5.4 If you want to change visuals or layout

→ `/css/`, `/inc/`, `/js/`  
(Check Architecture Invariants first)

### 5.5 If you are an AI agent (Codex)

→ **Codex — Behavioural Rules**

---

## 6. What This Project Is Not

- It is not a minimal tutorial solution
- It is not frozen or “finished”
- It is not optimized for speed over correctness
- It is not designed to hide complexity

Complexity here is intentional, surfaced, and documented.

---

## 7. How to Read This Repository

Recommended reading order:

1. Codex — Behavioural Rules
2. Project Overview & Navigation (this document)
3. Hero Image Authority Contract
4. Hero Image Governance — Admin Workflow
5. Codex — Architecture Invariants
6. Codex — Routing Invariants
7. Excel — Database Contract

This order exists to prevent accidental misuse or incorrect assumptions.

---

## Conclusion

Sports Warehouse is best understood as a **learning-driven, architecture-first retail platform**.

Its distinguishing feature is not visual polish alone, but the care taken to define **authority, governance, and evolution paths** — especially in areas where automation and human judgment intersect.


