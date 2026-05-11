# README — Tiered Consistency Scan Strategy for README Files

## Purpose

This README defines a **deterministic, low-risk strategy** for performing consistency scans across README files when a **canonical rule change** has been introduced (e.g. subCategory noun standardisation).

This document is **not intended for immediate execution**.

Its purpose is to:
- preserve the agreed scanning methodology,
- allow the work to be resumed cleanly in a **future chat session**,
- and prevent ad-hoc or over-eager edits once database authoring is complete.

This README exists as a **procedural placeholder**, not an active task.

---

## Scope

This strategy applies to:
- governance READMEs,
- design contracts,
- naming and semantics documents,
- collection-level documentation.

It does **not** apply to:
- deployment notes,
- environment setup,
- scripts or tooling documentation,
- backend implementation files unless they contain literal examples.

---

## Trigger Conditions

This scan strategy should be used **only when**:

- a canonical rule has changed (e.g. singularisation of `subCategory`),
- the change affects **literal examples**, not logic,
- database authoring is already underway or complete,
- and the risk of silent inconsistency outweighs the cost of review.

This is a **post-authoring hygiene step**, not a design phase activity.

---

## Core Principle

> **Scan only where the probability of literal inconsistency is high.  
Do not scan everything.**

The goal is to:
- correct mismatches,
- preserve intent,
- avoid unnecessary churn.

---

## Tiered Scan Model

All README files are classified into **tiers**, based on likelihood of containing literal `subCategory` examples.

Scanning proceeds **top-down by tier**, and stops when marginal value drops to zero.

---

### Tier 1 — Mandatory Scan

**Definition**  
Files that:
- define naming rules,
- explain semantic derivation,
- or include canonical examples involving `subCategory`.

**Characteristics**
- High probability of literal noun usage
- High impact if inconsistent
- Must be scanned line-by-line

**Typical file types**
- ItemName derivation contracts
- Coverage and disclosure governance
- Category semantics definitions

**Action**
- Update literal examples only
- Do not rewrite conceptual sections
- Preserve structure and numbering

---

### Tier 2 — Likely Scan

**Definition**  
Files that:
- explain authoring views,
- discuss collections or silhouettes,
- or may include illustrative examples.

**Characteristics**
- Medium probability of literal usage
- Lower blast radius
- Examples may appear incidentally

**Action**
- Scan for literals only
- Update examples if found
- Do not introduce new explanations

---

### Tier 3 — Spot-Check Only

**Definition**  
Files that:
- focus on implementation mechanics,
- column ordering,
- ingestion or export logic.

**Characteristics**
- Low probability of semantic literals
- High risk of unnecessary edits if over-scanned

**Action**
- Keyword spot-check only
- No changes unless a literal mismatch is found

---

### Tier 4 — Explicitly Excluded

**Definition**  
Files that:
- contain no examples,
- are operational or procedural only,
- or exist outside authoring semantics.

**Action**
- Do not scan
- Do not edit

---

## Use of an Index

Before scanning begins, an **up-to-date index of README files** must be available.

The index should include:
- full paths,
- filenames,
- and (optionally) brief descriptions.

The index is used to:
- assign each file to a tier,
- justify inclusion or exclusion,
- and document why scanning stopped.

---

## Execution Order

1. Tier 1 files (mandatory)
2. Tier 2 files (likely)
3. Tier 3 files (spot-check)
4. Stop

Scanning **must not** proceed into Tier 4.

---

## Invariants

- No rule changes during scanning
- No conceptual rewrites
- No duplication of canonical lists
- Literal alignment only
- Stop once consistency is achieved

---

## Deferred Status

This README is intentionally written for **future use**.

It should be revisited:
- after database updates are complete,
- after Ryderwear ingestion is finished,
- and before any further large-scale documentation edits.

Until then, it serves as a **locked procedural reference**, not an active instruction.
