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

### 2.2 Data is editorial, not incidental

Product data is treated as authored content with clear rules, naming conventions, and contracts between systems.

### 2.3 Architecture over features

The value of the project lies more in *how* things are structured than in the sheer number of visible features.

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
  - hero image scoring
  - admin overrides without data loss
- Schema evolution is intentional and checklist-driven

📄 See: **Codex — Architecture Invariants**

---

### 3.3 Image Analysis & Hero Selection System

One of the most distinctive features of the project.

Each product can have multiple images, which are:

- automatically analyzed
- scored and ranked for hero suitability
- presented to an admin user in ranked order

Scoring criteria include (but are not limited to):

- subject position (e.g. headroom)
- orientation
- crop safety
- image completeness

Importantly:

- automated ranking is *advisory*
- a human can override the chosen hero image at any time
- overrides are persistent and explicit

This system exists to explore **how automation and editorial judgment can coexist**, especially when visual quality is subjective.

📄 See: *Hero Selection documentation*

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

### 3.5.1 Admin Diagnostic Helpers (`/admin/inc/`)

As the admin system evolved beyond simple tooling into an authoritative diagnostic surface, shared authoritative,read-only logic was extracted into `/admin/inc/`.

This folder contains **admin-only, non-UI helpers** whose purpose is to:

- report system state and catalog health
- provide consistent, authoritative diagnostics
- avoid duplication between admin tools (e.g. Dashboard and Hero Manager)
- preserve strict separation between visibility and enforcement

Examples include:

- `hero-status.php` — canonical read-only hero coverage and state reporting

These helpers:
- perform no writes
- introduce no automation
- are consumed by multiple admin interfaces
- exist to ensure observability without triggering enforcement

This mirrors the frontend `/inc/` pattern, while remaining strictly admin-scoped.

### 3.5.2 Admin Evolution Model (Visibility → Enforcement → Automation)

Admin tooling in Sports Warehouse is developed under a strict progression model:

1. **Visibility**
   - Admin surfaces report system state truthfully
   - No writes, no heuristics, no automation
   - Purpose: establish trust and observability

2. **Enforcement**
   - Rules and authority checks may be introduced
   - Still no automation
   - Purpose: prevent invalid or unsafe writes

3. **Automation**
   - Batch operations, recomputation, or sync jobs
   - Introduced only after visibility and enforcement are proven stable

This progression is intentional.
Admin systems must never skip directly to automation.

Phase 6A operates entirely in the Visibility stage and is now **closed**.
It established the admin dashboard as a truthful, read-only diagnostic surface.

This model applies equally to hero selection, image auditing, and future admin extensions.

---

## 4. Experimental & Research Components

Some parts of the project are explicitly exploratory.

### 4.1 AI-Generated Shadows for Product Images

A major research thread investigates whether **AI or 3D rendering can generate realistic floor-and-wall shadows** for transparent PNG product images at scale.

Approaches explored include:

- Blender shadow-catcher pipelines
- AI relighting tools
- SaaS shadow generators

The goal is to create a **virtual studio look**:

- consistent lighting
- realistic grounding
- scalable across hundreds of images

This work is documented separately and should be considered **experimental**, not production-locked.

📄 See: *Blender Shadow-Catcher Research*

---

## 5. Navigation Guide (Where to Look)

### 5.1 If you want to understand rules and constraints

→ **Codex — Architecture Invariants**  
→ **Codex — Routing Invariants**

### 5.2 If you want to modify or import data

→ **Excel — Database Contract**  
→ **Importer Design Constraints**

### 5.3 If you want to change visuals or layout

→ `/css/`, `/inc/`, `/js/`  
(Check Architecture Invariants first)

### 5.4 If you want to understand image logic

→ `/tools/`, `/admin/hero-*`, image analysis scripts

### 5.5 If you are an AI agent (Codex)

→ **Codex — Behavioural Rules**

---

## 6. What This Project Is Not

- It is not a minimal tutorial solution
- It is not frozen or “finished”
- It is not optimized for speed of delivery over correctness
- It is not designed to hide complexity

Complexity here is intentional, surfaced, and documented.

---

## 7. How to Read This Repository

Recommended reading order:

1. Codex — Behavioural Rules
2. Project Overview & Navigation (this document)
3. Codex — Architecture Invariants
4. Codex — Routing Invariants
5. Excel — Database Contract
6. Specific subsystem documentation as needed

This order exists to prevent accidental misuse or incorrect assumptions.

---

## Conclusion

Sports Warehouse is best understood as a **learning-driven, architecture-first retail platform** built around a single-page, state-driven navigation model.

Its value lies not only in what it displays, but in how carefully each system is defined, constrained, and allowed to evolve.


