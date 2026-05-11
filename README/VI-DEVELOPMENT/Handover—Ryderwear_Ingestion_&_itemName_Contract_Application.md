# Phase Handover — Ryderwear Ingestion & itemName Contract Application

## Handover Address & Operating Mode (Mandatory)

This document is addressed to: **you, ChatGPT, being the new chat session acting as the active operator**.

You are not being asked to review, critique, summarize, or reinterpret this document.  
You are being asked to treat it as authoritative and resume execution at the stated next step.

This handover replaces all prior conversational context.

---

See documents attached.

---

## 1. Project State at Handover

The Sports Warehouse schema has reached a **lock point** for all columns relevant to `itemName`.

All conceptual debate, refinement, and promotion of attributes from editorial to canonical status has been completed.

No further schema interpretation is permitted during execution.

The task in the next session is **pure application of an existing contract**.

---

## 2. Authoritative Contract (Must Be Mounted Before Any Action)

Before taking any step, you must treat the following document as **fully authoritative and binding**:

- `README/II-CONTRACTS/11-ItemName-Relevant_Columns—Canonical_Semantics_&_Derivation_Rules.md`

This document defines, exhaustively and conclusively:

- what constitutes a collection, variant, and subCategory
- when attributes are canonical vs editorial
- bounded applicability rules for:
  - `support_level`
  - `sports_bra_type`
  - `seamless`
- Set semantics, including:
  - mandatory `Set:` colon usage
  - composition declaration rules
  - row minimalism and ordering
- attribute positioning contracts for:
  - apparel
  - shoes
  - non-wearables
- conditional disambiguators:
  - `ageGroup`
  - `gender`
- the global meaning and enforcement of:
  - `itemName_fully_derived`

No deviation, reinterpretation, or simplification of this contract is allowed.

---

## 3. Objective of the Next Phase

The goal of the next phase is to **ingest the Ryderwear product range** into the Sports Warehouse database in a way that:

- strictly applies the `itemName` contract
- exercises the newly locked canonical attributes
- produces clean, reviewable rows for:
  - individual items
  - Sets (where applicable)

This phase is intended to **validate the contract in practice**, not to revise it.

---

## 4. Execution Plan (High-Level)

The next chat session must proceed in the following order, without skipping steps.

### Step 1 — Confirm Schema Surface

Acknowledge and operate against the following column set (partial list, itemName-relevant):

- brand
- gender
- ageGroup
- itemName
- itemName_fully_derived
- collection
- model_family
- subCategory
- variant
- colour
- seamless
- sports_bra_type
- support_level
- usage_category
- usage_subtype

Do **not** invent new columns.

Do **not** collapse canonical attributes back into `variant`.

---

### Step 2 — Generate Ryderwear File Tree

Generate a filesystem tree for the Ryderwear image directory using **Windows PowerShell**, not heuristics.

Authoritative path:

C:\Users\rjfra\OneDrive - TAFE NSW\Cert_IV-Website_Design\Hornsby\Assignments\Sport_Warehouse\images\Clothing\Ryderwear


This is intentionally **outside** the usual Laragon root.

The output must be a readable, documentation-grade tree suitable for inspection and reasoning.

This tree is the **only source of truth** for what product items exist.

---

### Step 3 — Row Construction (Contract-Driven)

Using the file tree:

1. Identify collections (e.g. NKD, Adapt, etc.)
2. Work **one collection at a time**, starting with the largest
3. For each collection:
   - define atomic product rows first
   - define Set rows last (if applicable), per Set governance rules
4. For each row:
   - populate only itemName-relevant columns
   - derive `itemName` strictly from columns
   - flip `itemName_fully_derived` to **No** whenever:
     - manual wording is introduced
     - conditional disambiguators are used
     - editorial clarification is required

Do **not** fill pricing, inventory, or non-relevant metadata.

---

## 5. Operating Constraints (Non-Negotiable)

- You are implementing, not designing
- You are applying a contract, not refining it
- You must surface ambiguity, not resolve it silently
- Any uncertainty must be raised explicitly before proceeding

If a Ryderwear product **cannot** be expressed cleanly under the contract, that is a **finding**, not a failure.

---

## 6. Definition of Done for This Phase

This phase is complete when:

- the Ryderwear file tree has been fully enumerated
- every Ryderwear product has:
  - a compliant row
  - a contract-valid `itemName`
  - a correct `itemName_fully_derived` flag
- all Sets (if present) obey:
  - colon composition rules
  - row ordering rules
  - minimalism rules

Only after this may any future schema discussion resume.

---

## 7. Resume Instruction

**Resume execution at: Step 2 — Generate Ryderwear File Tree.**

Do not restate context.  
Do not summarize the contract.  
Do not reopen design questions.

Proceed.

