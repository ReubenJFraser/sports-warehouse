# III — OPERATIONS

## 1. Purpose of This README

This README defines the **operational layer** of the Sports Warehouse project.

Its purpose is to document **what is actually done to move data, images, and state through the system in practice**, once design intent, contracts, and invariants are already established.

Where other folders explain *why the system exists* or *what rules must never be broken*, this folder explains:

> **How the system is operated safely, repeatedly, and audibly in the real world.**

This is an execution-focused layer, not a conceptual one.

---

## 2. Scope

This folder covers:

- Day-to-day operational workflows
- Repeatable procedures used during development and maintenance
- Data movement between tools (Excel, DBeaver, MySQL)
- Image ingestion and correction processes
- Actions that change system state *intentionally and explicitly*

This folder does **not** define:

- Product intent or UX goals
- Authority rules or governance boundaries
- Architectural invariants
- Enforcement logic
- Experimental or speculative approaches

Those concerns live elsewhere and are treated as upstream inputs.

---

## 3. Role of the OPERATIONS Layer in the Documentation Stack

The documentation hierarchy is intentional and directional:

1. **NARRATIVE** explains intent and meaning  
2. **CONTRACTS** lock authority, safety, and boundaries  
3. **OPERATIONS** explains how work is actually carried out  
4. **CODEX** defines invariants that must never be violated  
5. **AUDIT / POST-AUDIT** record what was observed and enforced  

The OPERATIONS layer **assumes all upstream documents are binding**.

Operational procedures must never contradict:
- authority contracts
- routing or architecture invariants
- product intent

If a procedure appears to require breaking a contract, the procedure is wrong.

---

## 4. What “Operational” Means in This Project

In this project, “operational” means:

- Actions that are *performed repeatedly*
- Actions that *touch real data*
- Actions that can *break things if done incorrectly*
- Actions that must be *auditable after the fact*

Examples include:
- importing Excel data into MySQL
- correcting image paths
- rebuilding hero images
- validating ingestion results
- reconciling analysis tables with item state

These actions are **not theoretical** and are **not automated blindly**.

---

## 5. Contents of This Folder

The files in this folder document **established operational workflows**, including:

- Excel → MySQL ingestion procedures
- Image-focused ingestion variants
- Safety notes and verification steps
- Practical constraints discovered through use

Each file describes:
- the exact sequence of actions taken
- the tools involved
- what success looks like
- what failure modes mean

These documents are written to be:
- repeatable
- debuggable
- understandable months later

---

## 6. Relationship to Hero Manager Operations

Hero-related operational behavior is split deliberately:

- **Operational workflows** (how data is prepared and moved) live here
- **Hero Manager behavior** (how the system operates once data exists) is documented in:
  - *Hero Manager — Operational Model* (in the NARRATIVE layer)
  - relevant authority and scoring contracts (in CONTRACTS)

This separation prevents:
- mixing intent with execution
- encoding policy inside procedures
- accidental authority escalation during maintenance

---

## 7. What This Folder Is Not

This folder is not:

- a changelog
- a design notebook
- a brainstorming space
- a policy layer
- an enforcement specification

Only workflows that are **actually used** and **safe to repeat** belong here.

Experimental or one-off actions should not be promoted into this layer until they are proven.

---

## 8. Known Gaps and Evolution

Operational documentation evolves as reality evolves.

Known areas of ongoing refinement include:
- tighter verification steps
- clearer rollback guidance
- improved separation between dry-run and live actions

Gaps should be documented explicitly rather than worked around silently.

---

## 9. Guiding Operational Principles

- Operations execute intent; they do not redefine it
- Authority is respected at all times
- Writes are explicit and auditable
- Silence is failure; visibility is safety
- If an operation feels dangerous, it probably is

---

## 10. Summary

The OPERATIONS folder exists to answer a single practical question:

> **“What do I actually do, step by step, without breaking the system?”**

If a procedure cannot be trusted to answer that question clearly,
it does not belong here.

