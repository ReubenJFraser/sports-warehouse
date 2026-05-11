# Hero Image Authority Contract (Authoritative)

## Status & Relationship to Product Intent

This document defines **authority boundaries and write guarantees** for hero image data.

It exists to **preserve and protect the product intent** defined in:

→ **02-Hero_Manager — Product Intent & UX Goals.md**

This contract **does not define what humans are allowed to choose**.
It defines **who or what may write hero state**, and under what conditions,
so that human editorial intent is never silently overridden or reinterpreted.

If there is any conflict of interpretation:
**Product Intent takes precedence over Authority mechanics.**

---

## Scope

This contract governs **all writes and reads of persisted hero image state**
across:

- Admin tools
- Batch / maintenance scripts
- Database state
- Frontend rendering

This document is **normative with respect to authority boundaries**.

If code behavior violates these authority rules,
the code is wrong — not the editorial choice.

---

## Core Authority Principle (Non-Negotiable)

**Hero image authority is singular, explicit, and persistent.**

Once a hero image is written with valid authority,
no downstream layer may reinterpret, replace, recompute, or override it
without an explicit, higher-authority action.

This principle exists to ensure that
**human editorial decisions remain stable, visible, and respected.**

---

## Canonical Hero Fields

Hero state exists **only** in the following columns:

- `item.hero_image`
- `item.hero_score`
- `item.hero_ratio`
- `item.hero_orientation`

No parallel hero state is permitted.  
No derived hero state is permitted at read time.

These fields represent **persisted editorial state**, not transient UI logic.

---

## Hero Control States (Interpretive Clarification)

Hero behavior is determined **only** by persisted database fields.

At any moment, each item is in **exactly one** hero control state.

These states describe **how hero data came to exist** —
not whether it is “good,” “allowed,” or “approved.”

### State A — Automatic Hero (Computed)

**Definition**  
Hero selected by automation, with no human override.

**Persisted Conditions**
- `hero_override IS NULL`
- `hero_image` written by automation

**Characteristics**
- Automation provides a default suggestion
- `hero_score`, `hero_ratio`, and `hero_orientation` are meaningful
- Rejections may or may not exist

**Authority**
- Subordinate to manual editorial authority
- May be replaced only by explicit recomputation or override

---

### State B — Manual Hero Override (Editorial)

**Definition**  
Hero image explicitly chosen by a human.

**Persisted Conditions**
- `hero_override IS NOT NULL`
- `hero_image` reflects the override

**Characteristics**
- Human intent is authoritative
- Automation is intentionally suppressed
- `hero_score` is historical and informational only

**Authority**
- Terminal
- Must never be overwritten by automation or maintenance processes

This state represents **editorial judgment**, not system correction.

---

### State C — Governance-Constrained Automatic Hero

**Definition**  
Hero selected automatically, but with explicit candidate restrictions
derived from prior human feedback.

**Persisted Conditions**
- `hero_override IS NULL`
- One or more entries exist in `hero_rejections`

**Characteristics**
- Automation remains active
- Candidate pool is explicitly constrained
- Rejections persist across recomputation

**Authority**
- Subordinate to manual editorial authority
- Exists to improve suggestion quality, not to limit choice

---

### Interpretive Rule

Hero control state is determined **only** by persisted fields.

UI presentation, selector behavior, or score values
must never be used to infer authority.

---

## Authority Levels (Highest → Lowest)

Authority levels define **who may write hero state** —
they do **not** define who may choose a hero.

### 1. Manual Editorial Authority (Terminal)

**Source**
- Admin manual hero selection

**Rules**
- Represents explicit human intent
- Must always override automated selection
- Final unless changed manually again

**Constraints**
- Must be written explicitly
- Must never be invalidated automatically

This authority exists to **protect human choice**, not to restrict it.

---

### 2. Admin-Initiated Automated Authority (Subordinate)

**Source**
- Admin bulk rebuild or recomputation tools

**Rules**
- May assign hero fields algorithmically
- Must explicitly defer to manual editorial authority
- Must skip items with overrides present

**Constraints**
- Must never overwrite manual selections
- Must never invent fallback authority

Automation here exists to **assist humans**, not replace them.

---

### 3. Maintenance / Reconciliation Authority (Lowest)

**Source**
- CLI or batch maintenance scripts

**Rules**
- May assign hero fields only when no higher authority exists
- Must skip manual and admin-approved selections

**Constraints**
- Must never escalate authority
- Must never infer intent

This authority exists for **system hygiene only**.

---

### 4. Presentation Layer (Non-Authoritative)

**Source**
- Frontend rendering (`inc/*`)

**Rules**
- Strictly read-only
- Must render exactly what is stored
- Must not compute, score, or substitute heroes

**Constraints**
- No writes
- No recomputation
- No conditional substitution

The UI explains state; it does not decide it.

---

## Null-Hero Condition (Absence of Authority)

If `item.hero_image` is NULL:

- This represents **absence of authority**, not a decision
- Presentation may apply a **static, deterministic fallback**
- No fallback may be written back to the database
- No fallback may be treated as editorial intent

Null state must remain **visible and debuggable**.

---

## Determinism Requirement

Given a fixed database state:

- All admin views render the same hero
- All frontend views render the same hero
- No contextual substitution is permitted

Determinism exists to support **trust, explainability, and debugging**.

---

## Prohibited Behaviors (Explicit)

The following are **never allowed**:

- Frontend hero recomputation
- Shadow hero fields
- Parallel hero selection logic
- Context-specific hero overrides
- Implicit authority escalation
- Writing hero data outside defined paths

---

## Authority Enforcement (Clarified)

Authority enforcement applies **only to write permissions**.

It must **never** be used to:
- block human choice
- restrict editorial judgment
- encode image quality policy
- justify automated dominance

Code interacting with hero data must:
- know its authority level
- refuse unauthorized writes
- fail loudly on authority violations

Silent correction is forbidden.

---

## Golden Rule

> **Authority exists to protect human intent,  
> not to constrain it.**

If hero authority is unclear, no write is permitted —
and the ambiguity must remain visible.

Clarity precedes automation.
