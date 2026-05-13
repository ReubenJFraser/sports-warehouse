```markdown
# Codex — Read Me First (Sports Warehouse Project)

## Purpose

This README defines the primary behavioural governance rules for Codex operating within the Sports Warehouse project.

Its purpose is to establish the authority boundaries, workflow discipline, implementation mode, data rules, schema discipline, and enforcement awareness that Codex must follow before modifying project files.

This document is intended for Codex, ChatGPT, and human operators working with Codex-generated changes.

It exists to prevent implementation drift, unapproved inference, uncontrolled automation, stale-branch confusion, and architectural damage while still allowing productive implementation once the relevant boundaries are known.

---

## Scope

This README covers:

- Codex behavioural authority
- Excel and database authority boundaries
- importer constraints
- schema change discipline
- enforcement governance awareness
- Codex workflow discipline
- the current implementation mode for this project
- permitted and prohibited Codex behaviour

This README does not cover:

- detailed architecture boundaries
- routing boundaries
- full GitHub PR mechanics
- enforcement candidate details
- handover procedures between chat sessions

Those responsibilities are governed separately by:

- `README/IV-CODEX/02-Codex-Architecture_Invariants.md`
- `README/IV-CODEX/03-Codex-Routing_Invariants.md`
- `README/IV-CODEX/04-Codex-GitHub_PR_Workflow.md`
- `admin/ENFORCEMENT_CANDIDATE_REGISTER.md`

---

## Current Operating Mode

The Sports Warehouse project has moved beyond its initial conservative stabilization phase.

The earlier conservative phase was appropriate while:

- architecture boundaries were still being discovered
- routing behaviour was still being stabilized
- data authority rules were still being formalized
- enforcement boundaries were still being mapped
- Codex workflow behaviour with GitHub was still unreliable
- uncontrolled automation posed a high risk of damaging the project

That phase produced the core governance rules, architecture invariants, routing invariants, and enforcement register.

Those rules remain authoritative.

However, the active implementation mode is now:

**guardrailed implementation velocity**

This means Codex should produce meaningful bounded implementation slices, not excessive micro-stages or planning-only work unless the risk justifies it.

The project no longer requires one tiny planning step for every small decision.

It does require every implementation slice to remain:

- bounded
- auditable
- reviewed through a GitHub PR
- compliant with architecture and routing invariants
- locally verified after merge

---

## Conservative Mode vs Guardrailed Implementation Mode

### Conservative Stabilization Mode

Conservative stabilization mode is appropriate when:

- architecture is unclear
- routing behaviour is uncertain
- data authority is unresolved
- endpoint contracts are not yet known
- database mutation is involved
- enforcement boundaries are being introduced
- Codex workflow state is unstable
- the risk of unintended damage is high

In this mode, Codex must favour:

- inspection before implementation
- planning before mutation
- small stages
- explicit verification
- stopping when uncertain

---

### Guardrailed Implementation Velocity Mode

Guardrailed implementation velocity mode is appropriate when:

- architecture boundaries are documented
- routing rules are known
- endpoint contracts are established
- data authority is clear
- the relevant workflow has been tested
- implementation risk is bounded
- manual review and local testing remain in place

In this mode, Codex may implement complete bounded feature slices in a single PR.

A bounded feature slice may include related PHP, CSS, JavaScript, and documentation changes when they are part of one coherent implementation objective.

Codex must not split work into unnecessary planning-only stages merely to appear cautious.

---

## Required Workflow Companion

Before any Codex + GitHub implementation work, the workflow defined in `README/IV-CODEX/04-Codex-GitHub_PR_Workflow.md` must be read and followed.

The required operating model is:

- one fresh Codex task
- one bounded implementation objective
- one GitHub Pull Request
- one PR review and merge decision
- one local synchronization cycle
- one runtime verification cycle

Codex is not a continuous long-lived development environment.

If Codex task state, GitHub PR state, and local repository state become unsynchronized, the active workflow is considered unstable and the recovery procedures defined in `README/IV-CODEX/04-Codex-GitHub_PR_Workflow.md` must be followed.

---

## Non-Negotiable Authority Rules

### 1. Excel Is the Editorial Authority

All meaning, identity, and classification decisions originate in Excel.

Codex must not:

- invent data
- infer missing values
- normalize editorial meaning
- silently reinterpret ambiguous source values

If Excel is incomplete or ambiguous, execution must stop until the ambiguity is resolved.

---

### 2. Database Is an Execution Mirror

The database reflects approved editorial truth.

The database must mirror Excel definitions exactly.

Codex must not:

- redesign schema independently
- rename columns without approval
- merge distinct concepts
- reinterpret editorial classifications

Schema design authority does not originate in Codex.

---

### 3. Read-Only Truth Precedes Mutable Action

No mutable action should occur until current state is confirmed through read-only inspection.

Codex must prefer explicit SELECT verification before:

- UPDATE
- DELETE
- INSERT corrections
- ALTER statements

If a fact cannot be demonstrated through read-only inspection, it is not yet an operational fact.

This rule does not prohibit normal bounded implementation work in PHP, CSS, JavaScript, or documentation.

It applies most strongly to database state, production state, data correction, schema changes, enforcement logic, and authority claims.

---

## Identity Rules

### 4. external_item_id

`external_item_id` is:

- globally unique per product row
- human-curated
- editorially defined
- stable across environments

Codex must not:

- auto-generate identifiers
- deduplicate identifiers
- reinterpret identifier meaning

---

### 5. series_slug

`series_slug` exists to group related products.

It is intentionally non-unique.

Values must originate from the approved Excel canonical list.

Codex must not invent new series slugs.

---

## Importer Constraints

### 6. Importers Are Transport Only

Importers exist to move approved values from Excel into the database.

Importers must not:

- infer intent
- guess missing values
- normalize content
- apply editorial decision logic

If an importer requires decision-making logic, the workflow design is incorrect.

---

### 7. Importer Failure Is a Signal

Importer failure indicates unresolved ambiguity or invalid source conditions.

Failure must trigger investigation.

Codex must not patch around importer failure by inventing corrective logic.

---

## Schema Change Discipline

### 8. Schema Changes Are Sequential

Approved schema progression is:

Excel definition → localhost database → production database

Environment progression must not be skipped.

Production must not be used as an experimental schema environment.

---

### 9. Schema Changes Must Be Auditable

Schema mutation must remain explicit and reviewable.

Codex must present proposed SQL before execution.

Mutation history must remain understandable after the fact.

---

## Enforcement Awareness

### 10. Enforcement Is Register-Governed

Enforcement authority is governed by `admin/ENFORCEMENT_CANDIDATE_REGISTER.md`.

When writing or modifying admin mutation paths, Codex must treat that register as authoritative.

If a behaviour is not explicitly governed there, enforcement must be assumed unauthorized.

New enforcement must not be introduced unless:

- a visibility source exists
- the register is updated first
- authorization is explicit

---

## Implementation Discipline

### 11. Bounded Implementation Is Permitted

Codex may implement bounded feature slices when the scope is clear and the relevant boundaries are known.

A bounded implementation slice may include:

- PHP changes
- CSS changes
- JavaScript changes
- documentation updates
- small helper changes
- read-only endpoint consumption
- UI refinement
- local bug fixes within the stated objective

A bounded implementation slice must not include unrelated opportunistic cleanup.

---

### 12. Planning-Only Stages Must Be Justified

Planning-only stages are permitted when:

- architecture is unclear
- endpoint contracts are not yet established
- routing behaviour is uncertain
- data authority is unresolved
- schema or enforcement changes are being considered
- implementation risk is high

Planning-only stages should not be used when the next step is already well understood and implementation risk is bounded.

The default current mode is implementation within guardrails, not indefinite planning.

---

### 13. Implementation Slices Should Be Meaningful

Codex should avoid excessive fragmentation.

A stage should normally deliver a reviewable outcome, such as:

- a working UI slice
- a complete endpoint integration
- a coherent layout refinement
- a documented workflow rule
- a bounded bug fix
- a verified feature improvement

A stage should not exist merely to defer obvious implementation.

---

## Permitted Codex Behaviour

Codex is permitted to:

- generate read-only audit queries
- draft explicit SQL for review
- validate consistency between Excel and database state
- identify ambiguity
- identify contract violations
- implement approved mechanical changes
- perform bounded implementation work within defined governance constraints
- update documentation when it clarifies actual project workflow
- refactor within an explicitly approved scope
- improve UI or code structure when tied to a bounded objective

---

## Prohibited Codex Behaviour

Codex must not:

- infer missing data
- normalize editorial values independently
- merge identities
- invent identifiers
- invent grouping values
- clean data without approval
- assume production state
- introduce unauthorized enforcement
- bypass workflow discipline
- continue operating during unstable workflow state
- broaden scope without approval
- use conservative governance as an excuse for unproductive fragmentation
- use implementation velocity as an excuse for architecture drift

---

## Known Gaps and Open Questions

This README establishes behavioural governance and current operating mode.

It does not fully define all future phase transitions.

The boundary between conservative stabilization mode and guardrailed implementation velocity mode may require future adjustment as the project evolves.

The appropriate size of a bounded implementation slice remains contextual.

The governing expectation is that Codex should increase implementation throughput only where architecture, routing, data authority, and workflow boundaries are already clear.

---

## Guiding Principles

This project prioritizes:

- clarity over automation
- truth over convenience
- explicit authority over inference
- auditability over cleverness
- bounded implementation over uncontrolled iteration
- meaningful delivery over excessive ceremony
- guardrailed velocity over permanent conservatism

If Codex behaviour becomes ambiguous, execution should stop rather than improvise.

If the next implementation step is clear and bounded, Codex should implement rather than prolong planning.
```