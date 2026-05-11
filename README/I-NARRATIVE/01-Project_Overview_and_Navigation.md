# Sports Warehouse — Project Overview & Navigation

## 1. What This Project Is

**Sports Warehouse** is a full-stack e-commerce catalogue and design-systems project developed as part of a formal website development assignment and deliberately extended far beyond the course requirements.

While the original brief specified a limited product catalogue and standard presentation, this project evolved into a **technical showcase** focused on:

- robust data governance
- automated image analysis and ranking
- scalable frontend architecture
- human-in-the-loop admin tooling
- experimentation with AI-assisted visual enhancement

The site is not a mock-up. It is a functioning system designed to demonstrate *how real-world retail platforms are actually built, maintained, and evolved*.

This document provides **system-level orientation and navigation**.
It deliberately avoids defining detailed product intent or UX behavior for individual tools.

Where specific product intent matters—particularly for the Hero Manager—it is defined in a dedicated document.

📄 **Authoritative reference for Hero Manager purpose and UX**  
→ **Hero Manager — Product Intent & UX Goals**

---

## 2. Core Design Philosophy (System-Level)

This project is guided by four **system-level principles**.
They describe *how the platform is built*, not *what individual tools are for*.

### 2.1 Automation first, human override always

Wherever possible, the system produces an intelligent default state — but never locks the human out.

Automation exists to **assist editorial judgment**, not replace it.
Humans remain authoritative where intent and taste matter.

The specific meaning of “automation” and “override” in the context of hero images
is defined in:

📄 **Hero Manager — Product Intent & UX Goals**

---

### 2.2 Data is editorial, not incidental

Product data is treated as authored content with clear rules, naming conventions, and contracts between systems.

This includes image selection state, which is treated as **persisted editorial data**, not a transient UI concern.

How hero image state is interpreted and changed is defined at the product level,
not inferred from infrastructure alone.

---

### 2.3 Architecture over features

The value of the project lies more in *how* things are structured than in the sheer number of visible features.

Subsystems are intentionally separated into:
- **authority** (what may write)
- **visibility** (what may observe)
- **automation** (what may suggest or recompute)
- **presentation** (what may only read)

These separations exist to support clarity and safety.
They do **not** define product goals by themselves.

---

### 2.4 Showcase over minimal compliance

The project intentionally exceeds assignment scope to demonstrate independent problem-solving, research, and technical depth.

This includes building internal tooling to surface problems that real systems encounter,
such as missing data, ambiguous automation outcomes, and editorial disagreement.

---

## 3. High-Level System Overview

At a conceptual level, the system consists of five interacting layers.

This section describes *where things live*, not *why individual tools exist*.

---

### 3.1 Product Data Layer (Excel)

- Canonical product data is authored in **Excel**
- Excel is the editorial source of truth
- Data is imported into MySQL with strict schema alignment
- Fields such as `external_item_id`, `series_slug`, and `assignment_source` support:
  - stable identifiers
  - grouping logic
  - separation between course and custom items

📄 See: **Excel — Database Contract**

---

### 3.2 Database Layer (MySQL)

- MySQL backs both frontend and admin systems
- Designed to support:
  - deterministic product identity
  - image metadata storage
  - persisted hero image state
  - explicit admin overrides without data loss
- Schema evolution is intentional and checklist-driven

Hero image state is **persisted and authoritative** at this layer.
No hero selection or substitution occurs implicitly at read time.

📄 See: **Codex — Architecture Invariants**

---

### 3.3 Image Analysis & Hero Selection Infrastructure

One of the most distinctive technical subsystems in the project.

At an infrastructure level, this system supports:

- automatic analysis of product images
- scoring and ranking for hero suitability
- persistence of selected hero fields

This layer provides **capability**, not **intent**.

The *reason* hero images are ranked, how humans interact with those rankings,
and what constitutes success are defined in:

📄 **Hero Manager — Product Intent & UX Goals**

Authority boundaries for writing hero state are defined separately:

📄 **Hero Image Authority Contract**

---

### 3.4 Frontend Presentation & Navigation Model

The frontend is implemented as a **single-page, state-driven application**.

Key characteristics:

- One physical frontend entry point (`index.php`)
- Navigation driven by routing state and filters
- URL parameters encode meaning, not location

Important clarifications:

- Terms like *“men”*, *“women”*, *“kids”*, and *“plus”* represent **catalog segments**, not pages
- Filters refine a segment; they do not redefine it
- Routing semantics are governed by **Codex — Routing Invariants**

The frontend is strictly **non-authoritative** with respect to hero images.
It renders exactly what is stored and never computes alternatives.

📄 See: **Codex — Routing Invariants**

---

### 3.5 Admin & Diagnostic Tooling

The project includes substantial internal tooling because **real systems require observability**.

Admin tools exist to:
- surface persisted state
- diagnose failures
- allow controlled human intervention
- support safe evolution of automation

They are not policy engines and do not define product goals.

#### 3.5.1 Admin Diagnostic Helpers (`/admin/inc/`)

Shared, read-only helpers provide:

- authoritative state reporting
- consistent diagnostics
- separation between visibility and mutation

These helpers:
- perform no writes
- introduce no automation
- exist to support understanding, not enforcement

---

#### 3.5.2 Admin Evolution Model (Visibility → Guardrails → Automation)

Admin tooling evolves in stages:

1. **Visibility**
   - Truthful reporting of persisted state
   - No writes, no heuristics

2. **Guardrails**
   - Authority checks to prevent invalid writes
   - Still no automation

3. **Automation**
   - Batch operations and recomputation
   - Introduced only after visibility and guardrails are stable

This model supports safety.
It does not redefine product intent.

---

## 4. Experimental & Research Components

Some project components are exploratory by design.

### 4.1 AI-Generated Shadows for Product Images

Research investigates whether AI or 3D rendering can generate realistic shadows for transparent PNG product images at scale.

This work is documented separately and is **not production-locked**.

---

## 5. Navigation Guide (Where to Look)

### 5.1 If you want to understand *why* the Hero Manager exists

→ **Hero Manager — Product Intent & UX Goals** *(authoritative)*

### 5.2 If you want to understand rules and authority

→ **Hero Image Authority Contract**  
→ **Codex — Architecture Invariants**  
→ **Codex — Routing Invariants**

### 5.3 If you want to understand admin tooling

→ `/admin/hero-*`  
→ `/admin/inc/`

### 5.4 If you want to modify or import data

→ **Excel — Database Contract**

### 5.5 If you are an AI agent (Codex)

→ **Codex — Behavioural Rules**

---

## 6. What This Project Is Not

- It is not a minimal tutorial solution
- It is not policy-driven automation
- It is not designed to eliminate human judgment
- It is not optimized for speed over correctness

Complexity here is intentional, surfaced, and documented.

---

## Conclusion

Sports Warehouse is best understood as an **architecture-first retail platform**
designed to explore how automation and human judgment can coexist safely.

This document explains *where things live and how they fit together*.

The purpose, UX goals, and success criteria of the Hero Manager itself
are defined in:

📄 **Hero Manager — Product Intent & UX Goals**


