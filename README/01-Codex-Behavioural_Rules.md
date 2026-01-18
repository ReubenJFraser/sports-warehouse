# Codex — Read Me First (Sports Warehouse Project)

## Non-Negotiable Authority Rules

1. **Excel is the editorial authority**
   - All meaning, identity, and classification decisions originate in Excel.
   - Codex must never invent, infer, normalize, or “fix” data values.
   - If Excel is unclear or incomplete, the correct action is to stop and ask.

2. **The database is an execution mirror**
   - The database reflects Excel exactly.
   - Schema changes must already be defined and approved before execution.
   - Codex must not redesign schema, rename columns, or merge concepts.

3. **Read-only truth precedes action**
   - If a fact is not visible via a read-only SQL query, it is not a fact.
   - Codex must always propose or run SELECT queries before UPDATE or ALTER.
   - No assumptions about production state are allowed.

---

## Identity Rules (Critical)

4. **`external_item_id`**
   - Globally unique per product row.
   - Never shared across multiple rows.
   - Human-curated, slug-style identifiers defined in Excel.
   - Codex must not auto-generate or deduplicate these.

5. **`series_slug`**
   - Groups related products.
   - Non-unique by design.
   - Must come from the canonical list defined in Excel.
   - Codex must not invent new series slugs.

---

## Importer Constraints

6. **Importers are transport only**
   - Importers may move values from Excel → DB.
   - Importers must not make decisions, guesses, or transformations.
   - If an importer needs logic, the logic is wrong.

7. **Importer failure is a signal, not a bug**
   - Failure indicates unresolved editorial ambiguity.
   - Codex must stop and report, not patch around failures.

---

## Schema Change Discipline

8. **Schema changes are sequential**
   - Excel definition → Localhost DB → Production DB.
   - Never skip environments.
   - Never “test” schema changes in production.

9. **All schema changes must be auditable**
   - ALTER statements must be explicit and reversible.
   - Codex must present SQL before execution.

---

## Enforcement Awareness (Admin System)

10. **Enforcement is governed by an explicit register**
   - All enforcement decisions, candidates, and prohibitions are defined in:
     ```
     /admin/ENFORCEMENT_CANDIDATE_REGISTER.md
     ```
   - Codex must treat this register as authoritative when:
     - writing or modifying admin code
     - touching mutation paths
     - proposing guards, checks, or constraints
   - If a file or behavior is not listed in the register, Codex must assume:
     **enforcement is not authorized**.
   - Codex must not introduce enforcement unless:
     - a visibility source already exists, and
     - the register is updated first.

---

## What Codex Is Allowed To Do

- Generate **read-only audit queries**
- Draft **explicit SQL** for review
- Validate **consistency between Excel and DB**
- Flag **ambiguities or contract violations**
- Execute **approved, mechanical changes**

---

## What Codex Must Never Do

- Infer missing data
- Normalize naming on its own
- Merge rows or identities
- Create new series or identifiers
- “Clean up” data without instruction
- Assume production state
- Introduce enforcement outside the register

---

## Golden Rule

> If Codex feels tempted to be clever, it must stop.

This project values **clarity over automation** and **truth over convenience**.

