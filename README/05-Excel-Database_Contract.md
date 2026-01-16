# Excel → Database Contract (Authoritative)

## Role definition (non-negotiable)
- Excel is the editorial authority
- Human judgment lives here
- Naming, classification, intent, and exceptions are resolved here
- If Excel and DB disagree, Excel wins
- Database is the execution mirror
- Stores what Excel declares
- Never invents meaning
- Never “fixes” Excel silently
> This single rule would have prevented most of the pain you experienced.

## Column classes (this is the core insight)
- Every column must belong to exactly one class.

### Editorial columns (Excel-owned)
- These are written and reasoned about by humans.

**Contract rule**
- DB may enforce constraints (UNIQUE, NOT NULL)
- DB may NOT generate values
- Importers must be pure writes, never transforms

### System columns (DB-owned)
- These are derived, calculated, or operational.

**Contract rule**
- Excel must not attempt to “manage” these
- Excel may leave them blank or absent
- DB is free to change them

### Hybrid columns (careful zone)
- These exist in Excel but are validated by DB.

**Contract rule**
- Excel chooses value
- DB enforces allowed set / type
- Violations should FAIL loudly

## Required invariants (mechanical checks)
- Before any sync, these must all be true.
- Identity invariants
- external_item_id is:
- non-null
- unique
- immutable once published
- series_slug is:
- non-null
- present in the Series Slug Canonical Definition
- Referential invariants
- brand spelling is consistent
- categoryName is not free-text chaos
- assignment_source ∈ (custom, course)
- If any invariant fails → no sync.

## Schema sync sequence (this is the mechanical part)
- Always in this order. Never skip.
- Step 1 — Excel audit (human)
- Fill all editorial columns
- Resolve all NULLs that are not system-owned
- Decide exceptions explicitly (like course items)
- Step 2 — Schema alignment (DDL)
- Add missing columns to DB
- Match types and constraints
- No data movement yet
- Step 3 — Dry-run validation (SELECT-only)
- Examples:
- SELECT external_item_id, COUNT(*)
- FROM item
- GROUP BY external_item_id
- HAVING COUNT(*) > 1;
- SELECT *
- FROM item
- WHERE external_item_id IS NULL
- AND assignment_source = 'custom';
- If rows appear → stop.
- Step 4 — Controlled UPDATEs
- Brand-by-brand
- Alphabetical
- Logged
- Step 5 — Post-sync snapshot
- SELECT
- COUNT(*) AS total,
- SUM(external_item_id IS NOT NULL) AS ext_ok,
- SUM(series_slug IS NOT NULL) AS series_ok
- FROM item;
- This snapshot becomes the baseline.

## Importers: formal rule (important lesson learned)
- Importers are allowed to do only one thing:
- Copy declared values from Excel into DB.
- They are not allowed to:
- infer slugs
- normalize names
- invent defaults
- “help”
- That earlier failure you identified?

It violated this rule.
- If an importer ever contains logic like:
- if (empty($series_slug)) { guess(); }
- …it is invalid by contract.

## Change protocol (prevents future pain)
- Any future change must answer one question:
- Is this editorial, system, or hybrid?
- Editorial → change Excel first
- System → change code / DB only
- Hybrid → Excel + DB constraint update together
- No exceptions.

## Why this works (and why it matters)
- This contract:
- removes interpretation from sync
- makes Codex viable later (because rules are explicit)
- prevents “chat window drift”
- turns future debugging into diffing, not reasoning
- You are no longer building a site — you’re now operating a data system.
