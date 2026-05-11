# Part C — Export & Ingestion Contract  
**Image Path Contract & Excel Validation Gate**  
**Status:** v1.0 (Frozen on Adoption)

---

## C.1 Purpose of This Contract

This section defines the **non-negotiable rules governing the transition out of Excel** and into downstream systems.

Its purpose is to ensure that **correctness achieved in Excel is preserved exactly**, without mutation, inference, normalization, or repair, during export, ingestion, or deployment.

Part C completes the governance chain:

- Part A defines *meaning*
- Part B enforces *truth at the source*
- **Part C prevents truth from being corrupted downstream**

This is a governance contract, not an implementation suggestion.

---

## C.2 Authority Boundary (Hard Stop)

Excel is the **final authoritative environment** for semantic correctness.

Once data leaves Excel:

- No system may reinterpret meaning
- No system may infer missing structure
- No system may normalize or “fix” values
- No system may introduce defaults not explicitly authored in Excel

Downstream systems are **execution mirrors only**.

---

## C.3 Export Artifact Definition (Excel → File)

### C.3.1 Authoritative Worksheet

- The `items` worksheet is the sole authoritative export source
- Supporting worksheets (e.g. mappings, validation lists) are **not exported**

---

### C.3.2 Export Format

Exports must satisfy all of the following:

- Format: CSV
- Encoding: UTF-8 (no BOM)
- Delimiter: comma
- Quoting: standard CSV rules only
- Line endings: LF

Excel auto-formatting (dates, numbers, trimming) must be disabled or audited.

---

### C.3.3 Column Preservation Rule

- Column order must be preserved exactly as defined in Part B
- No columns may be dropped
- No columns may be reordered
- No columns may be merged or split

Derived or validation columns are included **only if explicitly designated**.

---

## C.4 Serialization Rules (Critical)

### C.4.1 Multi-Value Cells

For cells containing multiple image paths:

- Paths are serialized as a semicolon-delimited list
- No whitespace normalization is permitted
- Order is preserved as authored

Example (illustrative):

`path1.png;path2.png;path3.png`

---

### C.4.2 Path Integrity

- Paths are exported verbatim
- No slug normalization
- No case normalization
- No prefix insertion or removal

What exists in Excel must exist identically in the export artifact.

---

## C.5 NULL & Empty Semantics Contract

This section is **critical**.

### C.5.1 Distinction Rules

The following are **not equivalent**:

- `NULL`
- empty string
- omitted column

Each has distinct semantic meaning and must be preserved.

---

### C.5.2 NULL Authoritativeness

If a cell is NULL in Excel:

- It must remain NULL in CSV
- It must remain NULL in MySQL
- It must not be replaced with defaults
- It must not be inferred downstream

This applies especially to:

- gender
- collection
- variant
- colour
- domain-specific columns

---

### C.5.3 Gender-Specific Enforcement

Gender handling rules defined earlier are enforced as follows:

- If gender **must be present**, NULL is invalid and export must not proceed
- If gender **might be present**, NULL is authoritative
- If `unisex` is authored, it is preserved as-is
- No downstream system may infer gender under any circumstances

---

## C.6 Ingestion Rules (CSV → MySQL)

### C.6.1 Ingestion Mode

Ingestion is **reject-on-error**, not best-effort.

- A single invalid row aborts ingestion
- Partial ingestion is forbidden
- Silent skipping is forbidden

---

### C.6.2 Prohibited Ingestion Behaviors

The following are explicitly forbidden during ingestion:

- Path rewriting
- Slug normalization
- Folder inference
- Category inference
- Gender inference
- Placeholder substitution
- Auto-filling NULLs
- “Legacy compatibility” fixes

If a row does not ingest cleanly, it must be corrected **upstream in Excel**.

---

### C.6.3 Referential Integrity

All referenced entities must already exist:

- brand
- category
- base category
- domain identifiers

Ingestion must not create missing reference records implicitly.

---

## C.7 Environment Parity Rules

### C.7.1 Single Artifact Rule

- Local MySQL and Cloudways MySQL must ingest **the same export artifact**
- No environment-specific transforms are permitted

---

### C.7.2 Filesystem Relationship

- Filesystem existence checks occur **after ingestion**
- Missing files are treated as errors, not warnings
- Case sensitivity is enforced (Linux-compatible)

Filesystem structure must match the Image Path Contract; the database does not adapt to the filesystem.

---

## C.8 Prohibited Downstream Repair (Blacklisted)

The following behaviors are permanently disallowed in:

- PHP
- JavaScript
- SQL
- Admin UI
- Deployment scripts

Including but not limited to:

- Guessing intended folders
- Adding missing path segments
- Inferring collections or models
- Collapsing hierarchy
- Auto-selecting fallback images
- Normalizing “old” paths silently

Visibility of errors is preferred over convenience.

---

## C.9 Failure Handling & Accountability

When ingestion fails:

- The failure must be explicit
- The failing row(s) must be identifiable
- Correction occurs in Excel only
- No hotfixes are applied downstream

This preserves auditability and prevents schema drift.

---

## C.10 Governance Invariants (Reaffirmed)

- Excel is the source of truth
- Meaning is authored, not inferred
- Order encodes semantics
- Omission is allowed; reordering is not
- Downstream systems execute; they do not decide

This contract exists to **lock correctness end-to-end**, not to optimize convenience.

---
