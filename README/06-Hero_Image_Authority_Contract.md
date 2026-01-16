# Hero Image Authority Contract (Authoritative)

## Scope

This contract governs **all hero image selection, storage, and rendering**
across:

- Admin tools
- Batch / maintenance scripts
- Database state
- Frontend rendering

This document is **normative**.
If code behavior conflicts with this contract, the code is wrong.

---

## Core Authority Principle (Non-Negotiable)

**Hero image authority is singular, explicit, and persistent.**

Once a hero image is written with valid authority,
no downstream layer may reinterpret, replace, recompute, or override it.

---

## Canonical Hero Fields

Hero state exists **only** in the following columns:

- `item.hero_image`
- `item.hero_score`
- `item.hero_ratio`
- `item.hero_orientation`

No parallel hero state is permitted.
No derived hero state is permitted at read time.

---

## Authority Levels (Highest → Lowest)

### 1. Manual Editorial Authority (Terminal)

**Source**
- Admin manual hero selection

**Rules**
- Represents explicit human intent
- Must always override automated selection
- Once written, is final unless changed manually again

**Constraints**
- Must be written explicitly
- Must not be invalidated or recomputed automatically

---

### 2. Admin-Initiated Automated Authority (Subordinate)

**Source**
- Admin bulk rebuild tools

**Rules**
- May assign hero fields algorithmically
- Must explicitly defer to manual editorial authority
- Must skip any item with manual override present

**Constraints**
- Must never overwrite manual selections
- Must not invent fallback authority

---

### 3. Maintenance / Reconciliation Authority (Lowest)

**Source**
- CLI or batch maintenance scripts

**Rules**
- May assign hero fields only when no higher authority exists
- Must explicitly skip manual and admin-approved selections

**Constraints**
- Must never escalate authority
- Must never infer intent

---

### 4. Presentation Layer (Non-Authoritative)

**Source**
- Frontend rendering (`inc/*`)

**Rules**
- Must be strictly read-only
- Must never compute, score, or substitute hero images
- Must render exactly what is stored

**Constraints**
- No writes
- No recomputation
- No conditional hero substitution

---

## Null-Hero Condition (Absence of Authority)

If `item.hero_image` is NULL:

- This represents **absence of authority**
- Presentation layer may apply a **static, deterministic fallback**
- No fallback may be written back to the database
- No fallback may be treated as authority

---

## Determinism Requirement

Given a fixed database state:

- All admin views must render the same hero image
- All frontend views must render the same hero image
- No time-based, viewport-based, or contextual substitution is permitted

---

## Prohibited Behaviors (Explicit)

The following are **never allowed**:

- Frontend hero recomputation
- Shadow hero fields
- Parallel hero selection logic
- Context-specific hero overrides
- Implicit authority escalation
- Writing hero data outside defined write paths

---

## Enforcement Expectation

Code interacting with hero data **must**:

- Know its authority level
- Refuse writes it is not permitted to make
- Fail loudly if authority rules are violated

Silent correction is forbidden.

---

## Golden Rule

> If hero authority is unclear, no write is permitted.

Clarity precedes automation.

