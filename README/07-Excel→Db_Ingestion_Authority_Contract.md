```markdown
# Excel → Database Ingestion Authority Contract

## Status

**Inspection / Policy Document — Locked**  
No code changes are implied or authorized by this document.

This contract defines **authority, responsibility, and failure semantics** for all Excel → Database ingestion pathways in the Sports Warehouse system.

---

## 1. Purpose

The purpose of this document is to make explicit the rules that govern **data ingestion authority**, following the completion of the System Audit (Phases 1–4).

This contract exists to:

- prevent semantic drift between Excel, database, and runtime systems
- eliminate silent data repair or normalization during ingestion
- define correct failure behavior when canonical constraints are violated
- establish preconditions for any future ingestion enforcement or automation

This document is **policy-only**. It does not prescribe implementation details.

---

## 2. Scope

This contract applies to **all mechanisms that ingest data from Excel into the database**, including but not limited to:

- CLI import scripts
- admin bulk upload tools
- batch update pipelines
- maintenance utilities

This contract does **not** apply to:

- frontend rendering
- runtime query logic
- presentation-layer normalization
- user-facing input forms

---

## 3. Source of Truth (Locked)

### 3.1 Excel Is the Sole Editorial Authority

- Excel is the **authoritative source of semantic truth** for catalog identity and classification.
- Canonical values originate in Excel and must be **fully resolved prior to ingestion**.
- The database is a **mirror of Excel truth**, not a reconciliation layer.

No downstream system may reinterpret, correct, or infer meaning that was not already explicit in Excel.

---

## 4. Canonical-at-Rest Requirement (Locked)

### 4.1 Definition

“Canonical at rest” means:

- identity values stored in the database **must already be canonical** at the moment they are written
- no code path may rely on runtime normalization or alias resolution

Examples of identity fields governed by this rule include (non-exhaustive):

- `section`
- `gender`
- `size_type`
- category identifiers
- taxonomy flags

---

## 5. Role of Importers (Locked)

### 5.1 Importers Are Transport Mechanisms

Importers:

- move data from Excel into the database
- validate structural correctness
- enforce declared constraints

Importers **must not**:

- normalize values
- map aliases
- infer intent
- repair inconsistencies
- apply heuristics

Importers are **not decision engines**.

---

## 6. Failure Semantics (Locked)

### 6.1 Rejection Over Repair

When ingestion encounters a non-canonical or invalid value:

- the value **must be rejected**
- the failure **must be explicit and observable**
- silent coercion is forbidden

Non-canonical data is classified as a **data error**, not a condition to be corrected in code.

---

### 6.2 Partial Success Is Forbidden by Default

Unless explicitly designed and documented otherwise:

- ingestion failures abort the operation
- partial imports are not permitted
- no “best effort” semantics are allowed

This prevents mixed-authority states from entering the database.

---

## 7. Diagnostics and Observability

Ingestion pathways must be capable of reporting:

- which row failed
- which field failed
- the offending value
- the canonical constraint that was violated

Failures must be **diagnostic**, not silent.

---

## 8. Relationship to Other Contracts

This document is consistent with and subordinate to:

- **Architecture Invariants** — layered authority and determinism
- **Routing Invariants** — canonical identity vocabularies
- **Excel → Database Contract** — editorial ownership
- **Codex Behavioural Rules** — prohibition on semantic invention
- **Hero Image Authority Contract** — centralized write authority

This document extends those principles specifically to **ingestion boundaries**.

---

## 9. Codex Constraints

Codex:

- must obey ingestion authority
- must not repair or normalize ingestion data
- may assist only with **mechanical validation or reporting**, once enforcement is authorized

Codex is explicitly forbidden from acting as an ingestion authority.

---

## 10. Preconditions for Enforcement

No ingestion enforcement may occur unless:

- this contract is accepted as authoritative
- enforcement scope is explicitly declared
- affected ingestion paths are enumerated
- enforcement is logged as post-audit activity

Until those conditions are met, ingestion behavior remains **unchanged**.

---

## 11. Contract Status

- Inspection / policy only
- Append-only
- Authority-clarifying
- Required precursor to any ingestion enforcement

This contract is **locked** until explicitly revised in a future phase.
```

