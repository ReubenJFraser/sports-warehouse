# Hero Manager — Operational Model

**Status:** Authoritative operational reference  
**Audience:** Internal (developer, future self, AI assistants)  
**Scope:** How hero images are selected, reviewed, overridden, rejected, and diagnosed in practice  
**Non-goal:** Enforcement, editorial restriction, or autonomous decision-making  

---

## 1. Purpose of This Document

This document defines **how the Hero Manager actually operates** as a system.

It exists to bridge the gap between:

- **Product intent & UX goals**  
  (see: *Hero Manager — Product Intent & UX Goals*)
- **Concrete implementation**  
  (code, database state, admin tooling)

If the Product Intent document answers *“what this system is for”*,  
this document answers:

> **“What happens, step by step, when the system runs and when a human interacts with it?”**

This is an operational description, not a policy document and not a UX mock-up.

---

## 2. System Boundaries and Responsibilities

### 2.1 What the Hero Manager Is Responsible For

The Hero Manager is responsible for:

- Automatically selecting a default hero image
- Persisting hero image state in the database
- Displaying current hero state clearly in admin
- Allowing humans to override automated choices
- Recording disagreement with automation
- Providing diagnostics for missing or incorrect heroes

These responsibilities are **active today** in code and data.

---

### 2.2 What the Hero Manager Is Not Responsible For

The Hero Manager does **not**:

- Enforce editorial quality standards
- Block publishing or visibility of products
- Prevent humans from making poor choices
- Encode business policy or compliance rules
- Operate autonomously without human review

Any feature that removes human discretion is **out of scope** unless the Product Intent document is explicitly revised.

---

## 3. Core Entities and Data Model (Operational View)

### 3.1 Product Item

Operationally, the Hero Manager works at the level of a **single product item**.

Each item has:

- A stable identifier
- A set of associated images (typically 5–8)
- Exactly one hero image at any given time  
  (or none, in error or diagnostic cases)

---

### 3.2 Persisted Hero State (Authoritative)

Hero state is persisted and authoritative in the following database fields:

- `item.hero_image`
- `item.hero_score`
- `item.hero_ratio`
- `item.hero_orientation`

These fields are:

- Written explicitly
- Read consistently
- Never derived at read time

All frontend and admin views must render **exactly** what is stored here.

---

## 4. Automatic Hero Selection (How It Works)

### 4.1 Candidate Image Collection

When automatic hero selection runs:

- All candidate images for the item are collected
- Sources typically include:
  - chosen image
  - thumbnails or image sets
- Candidates are deduplicated by basename

If no candidates are found, automatic selection cannot proceed.

---

### 4.2 Image Analysis Inputs

Each candidate image may have associated analysis data, such as:

- Orientation
- Headroom percentage
- Crop safety
- Face detection count
- Other measurable visual features

This analysis is **input data**, not policy.

---

### 4.3 Scoring and Ranking

For each candidate:

- A score is computed using available analysis inputs
- Candidates are ranked from **most suitable → least suitable**
- The highest-ranked candidate becomes the default hero

Automation therefore:

- proposes
- ranks
- persists

Automation does **not** decide permanently.

---

## 5. Admin Hero Manager UI (Operational Behavior)

### 5.1 What the Admin Sees Today

In the current system, the admin can see:

- The current hero image
- Whether it is automatic or manually overridden
- Hero score, ratio, and orientation
- Rejection count (if any)
- Indicators for missing or invalid images

This reflects **persisted state**, not recomputation.

---

### 5.2 Intended Admin Experience (Operational Target)

Operationally, the Hero Manager UI is intended to allow a human to:

- See **all candidate images** for an item
- See candidates **ranked from best to worst**
- Visually compare candidates
- Understand *why* images rank as they do
- Change the hero with minimal friction

The UI should feel like **curation**, not configuration.

---

## 6. Human Override Flow (Editorial Authority)

### 6.1 Selecting a Different Hero

When a human selects a different image as the hero:

- That image becomes the persisted hero
- The override is written explicitly
- Automation is suppressed for that item

This action represents **editorial judgment**, not correction.

---

### 6.2 Properties of Overrides

Manual overrides are:

- Explicit
- Persistent
- Auditable
- Always respected

Once an override exists, automation must never overwrite it unless the human changes it again.

---

## 7. Rejections and the Feedback Loop

### 7.1 What a Rejection Means

A rejection means:

> “The system’s top-ranked suggestion was not preferred for this item.”

It does **not** mean:

- automation failed
- the image is invalid
- policy was violated

---

### 7.2 Why Rejections Are Recorded

Rejections are recorded to support:

- Pattern detection
- Ranking quality analysis
- Category-level insights
- Future improvement of scoring heuristics

They exist to **learn**, not to restrict.

---

## 8. Diagnostic Role of the Hero Manager

### 8.1 Missing or Incorrect Hero Images

A critical operational role of the Hero Manager is diagnostics.

It must make it possible to determine:

- whether a hero image exists
- whether it was written successfully
- whether it was overridden
- whether image paths are missing or invalid
- whether candidate analysis exists

---

### 8.2 Why Diagnostics Matter

A common real-world question is:

> “Why does this product not show a hero image?”

The Hero Manager is the **primary debugging surface** for answering that question.

Visibility into failure is as important as success.

---

## 9. Recalculation and Maintenance Actions

### 9.1 What Recalculation Does

Recalculation:

- Re-runs automatic selection
- Uses current candidate data
- Respects all manual overrides
- Preserves rejection history

---

### 9.2 Safety Constraints

All recalculation and maintenance actions must:

- Avoid silent overwrites
- Avoid authority escalation
- Avoid inference of intent
- Fail loudly if authority rules are violated

---

## 10. Relationship to Other Documents

- **Product Intent & UX Goals** define *why* the system exists
- **Hero Image Authority Contract** defines *what may write*
- **Codex documents** define *structural invariants*
- **This document** defines *day-to-day operational behavior*

If conflicts arise:

- Product Intent governs UX direction
- Authority Contracts govern write safety
- This document governs implementation behavior

---

## 11. Definition of Operational Success

Operationally, the Hero Manager is successful when:

- Every item displays a hero image or a clear diagnostic reason
- Automation is helpful but corrigible
- Overrides are easy and respected
- Rejections inform improvement
- Admins can understand *why* something happened
- The system feels assistive, not adversarial

---

## 12. Guiding Operational Principle

> **Automation proposes.  
> Humans choose.  
> The system remembers.**

This principle governs all operational decisions in the Hero Manager.

---


