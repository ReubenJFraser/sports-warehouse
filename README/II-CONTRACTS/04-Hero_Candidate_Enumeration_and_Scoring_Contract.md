# Hero Candidate Enumeration & Scoring Contract — Sports Warehouse

## 1. Purpose of This README

This document defines the **authoritative contract** for how hero image candidates are **enumerated, scored, and presented for inspection** within the Sports Warehouse system.

Its purpose is to make the hero selection process:

- transparent
- deterministic
- explainable
- safe with respect to editorial authority

This contract exists to ensure that **automation can explain itself** without ever acquiring the power to decide.

This is a **governance document**, not a tutorial.

---

## 2. Status and Relationship to Other Contracts

This contract is **subordinate** to the following authoritative documents:

- **Hero Image Authority Contract**
- **Path & Environment Contract**
- **Product Intent & Category-Aware Hero Image Selection**

If any interpretation in this document conflicts with those contracts,  
**this document yields unconditionally**.

This contract defines **inspection behavior only**.  
It does not grant, modify, or reinterpret authority.

---

## 3. Scope

This contract governs:

- enumeration of all hero-eligible image candidates for a given item
- read-only scoring and ranking of those candidates
- presentation of candidate metadata to admin tooling
- deterministic ordering and explainability of results

This contract explicitly does **not** govern:

- hero image selection
- hero image persistence
- manual override behavior
- authority enforcement
- frontend rendering behavior

Those concerns are defined elsewhere.

---

## 4. Core Principle (Non-Negotiable)

**Candidate enumeration and scoring are strictly informational.**

They exist to **explain the current state of the system**, not to justify,
enforce, or override editorial decisions.

Scores, ranks, and annotations must never be treated as authority.

---

## 5. Conceptual Roles

### 5.1 Enumeration

Enumeration answers a single question:

> “Which images are eligible to be considered as hero candidates for this item?”

Enumeration must be:
- exhaustive within defined inputs
- deterministic
- repeatable
- free of suppression or filtering based on preference

If an image exists in the candidate pool, it must be surfaced.

---

### 5.2 Scoring

Scoring answers a different question:

> “Given known analytical metadata, how do these candidates compare relative to each other?”

Scoring:
- is advisory only
- is relative, not absolute
- may evolve over time
- must remain explainable

A higher score does **not** imply correctness, approval, or legitimacy.

---

### 5.3 Inspection

Inspection exists to support:
- human understanding
- editorial judgment
- system debugging
- iterative refinement

Inspection must never:
- write hero state
- collapse ambiguity
- hide rejected or overridden images
- infer intent from automation

---

## 6. Canonical Data Inputs

Candidate enumeration and scoring may consult **only persisted data**.

### 6.1 Item-Level Sources

From the `item` table:
- `chosen_image`
- `chosen_ratio`
- `thumbnails_json`
- `hero_image`

No other item fields may be used to infer hero legitimacy.

---

### 6.2 Analytical Metadata

From the `image_headroom` table:
- `image_basename`
- `ratio`
- `headroom_pct`
- `focus_y_pct`
- `crop_safe`
- `face_count`

This data is treated as:
- observational
- fallible
- informational

Missing metadata must not disqualify a candidate.

---

### 6.3 Governance Context

From governance tables:
- `hero_override` (latest entry per item, if any)
- `hero_rejections` (historical, persistent)

These tables provide **context**, not instruction.

---

## 7. Candidate Enumeration Rules

The candidate set is constructed as follows:

1. Include `chosen_image` if present.
2. Include all images referenced in `thumbnails_json`.
3. Normalize candidates by basename to avoid duplication.
4. Preserve first-seen order prior to scoring.

No candidate may be excluded due to:
- low score
- prior rejection
- override status
- category assumptions

---

## 8. Scoring Rules (Informational Only)

Each candidate may be assigned a numeric score derived from:

- presence and count of faces
- headroom safety
- crop safety
- aspect ratio proximity
- other analytical signals explicitly documented in code

Scoring rules must satisfy the following:

- deterministic for a fixed database state
- independent of UI context
- independent of authority state
- stable within a given version of the algorithm

Scores must be treated as **relative signals**, not quality judgments.

---

## 9. Ranking and Determinism

Candidates are ranked by:

1. descending score
2. deterministic tie-breakers (e.g. original enumeration order)

Given a fixed database state:
- enumeration order is stable
- scores are stable
- ranks are stable

Non-deterministic behavior is a defect.

---

## 10. Status Annotations (Non-Authoritative)

Each candidate may be annotated with the following flags:

- `is_current_hero`
- `is_manual_override`
- `is_rejected`
- `rejection_count`

These flags:
- describe persisted state
- do not imply correctness
- do not restrict selection
- do not escalate authority

They exist solely to **explain context**.

---

## 11. Prohibited Behaviors (Explicit)

The following are never permitted within enumeration or scoring logic:

- writing to `item.hero_*` fields
- inserting or modifying `hero_override`
- inserting or modifying `hero_rejections`
- suppressing candidates based on score
- inferring authority from rank or score
- encoding category ideology as hard rules

Violations must be fixed, not justified.

---

## 12. Known Gaps and Open Questions

The following are intentionally left open:

- how scoring weights evolve over time
- how category-aware signals may influence scoring
- how UI presents explanations to humans

These are **product and UX concerns**, not contract violations.

---

## 13. Invariants

The following invariants must always hold:

- Enumeration is complete.
- Scoring is advisory.
- Authority is external.
- Inspection is read-only.
- Determinism is mandatory.
- Ambiguity is allowed to remain visible.

Breaking any invariant is a defect.

---

## 14. Guiding Principle

> **Automation explains what it sees.  
> Humans decide what it means.**

This contract exists to protect that separation.


