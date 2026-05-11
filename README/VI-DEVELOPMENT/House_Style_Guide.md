# README House Style Guide

This document defines the **house style** for all README files in this repository.

Its purpose is to ensure that documentation remains consistent, readable, auditable, and safe to evolve over time.

All future README files should conform to these conventions unless explicitly documented otherwise.

---

## 1. Core Principles

All README files must adhere to the following principles:

- Procedural over theoretical  
- Descriptive over prescriptive  
- Explicit over implicit  
- Auditable over clever  
- Stable over fashionable  

READMEs exist to explain **what is actually done**, not what could be done in theory.

---

## 2. Formatting Rules (Non-Negotiable)

### 2.1 Single Markdown Fence Requirement

- **Every README must be written entirely inside a single Markdown code fence**
- No content may appear outside that fence
- The fence itself is the containment boundary for the document

This rule exists to:
- prevent accidental partial copies
- preserve structural integrity
- ensure long-term reliability when READMEs are reused or moved

---

### 2.2 Markdown Inside the Fence

Inside the single fence:

- All content must be valid Markdown
- Headings must use `#`, `##`, `###`
- Lists must use `-` or numbered lists
- Emphasis may use bold or italics

---

### 2.3 SQL, Shell, and Queries (Clarified)

**Executable content is allowed and expected.**

- SQL queries (including DBeaver queries) **may be included**
- Shell commands, configuration snippets, and other procedural statements **may be included**
- These must appear **inline within the same single Markdown fence**

What is prohibited is **not code**, but **multiple or nested fences**.

Examples of acceptable inclusion:
- Inline SQL statements such as `DELETE FROM item`
- Multi-line SQL written as plain text paragraphs
- Step descriptions followed by the exact query used

Examples of prohibited inclusion:
- Multiple fenced blocks inside a README
- Nested triple-backtick blocks
- Breaking executable content outside the single fence

---

### 2.4 Inline Literals

- Inline literals (filenames, table names, column names, queries) may be written inline
- Paths, identifiers, and settings should appear inline rather than in separate fences

---

## 3. Document Structure

All READMEs should follow a predictable structure.

### 3.1 Title

- Use a single top-level `#` heading
- The title should describe the system, workflow, or responsibility area
- Avoid vague titles such as “Notes” or “Misc”

---

### 3.2 Purpose Section

Early in the document, include a section titled:

- “Purpose”  
  or  
- “Purpose of This README”

This section must explain:
- why the document exists
- what problem it solves
- what kind of reader it is written for

---

### 3.3 Scope Section

Every README must explicitly state scope.

Include:
- what the document covers
- what it intentionally does not cover

This prevents false assumptions and scope creep.

---

### 3.4 Conceptual Roles (Where Applicable)

When multiple systems interact, include a section explaining roles.

Examples:
- Excel vs MySQL
- frontend vs admin
- automation vs human override

This section defines **responsibility boundaries**, not implementation details.

---

### 3.5 Procedural Sections

Procedural documentation should:

- be ordered
- be numbered when sequence matters
- describe intent before mechanics
- avoid pseudo-code

Use clear section titles such as:
- “Step 1 — Prepare Data”
- “Step 2 — Import”
- “Verification”

---

### 3.6 Optional Patterns and Alternatives

Optional or advanced patterns should be clearly labeled.

Use language such as:
- “Optional”
- “Advanced”
- “May be introduced later”

Optional patterns must never be mixed into the primary happy path.

---

### 3.7 Known Gaps and Open Questions

All READMEs should include a section acknowledging uncertainty.

This section exists to:
- document what is not yet locked
- prevent accidental assumptions
- signal areas of active evolution

Leaving gaps undocumented is worse than leaving them unresolved.

---

### 3.8 Guiding Principles or Invariants

Where relevant, end with a short section describing:

- guiding principles
- invariants
- non-goals

These act as guardrails for future contributors (human or AI).

---

### 3.9 Handover Address & Operating Mode (Mandatory for Handovers)

Any README intended to function as a **handover between chat sessions** must include, at the very top of the document, a section titled:

**Handover Address & Operating Mode (Mandatory)**

This section must appear **before** Purpose and Scope.

The section must be included verbatim and must not be paraphrased:

> This document is addressed to: **you, ChatGPT, being the new chat session acting as the active operator**.
>
> You are not being asked to review, critique, summarize, or reinterpret this document.  
> You are being asked to treat it as authoritative and resume execution at the stated next step.
>
> This handover replaces all prior conversational context.

Rationale:
- Removes ambiguity about audience (operator vs reviewer)
- Locks execution mode and prohibits re-interpretation
- Ensures clean session-to-session continuity for long-running projects

Handovers that omit this section are considered **non-compliant** and must not be used as execution entry points.

This requirement applies only to READMEs that function as handover documents, not to this House Style Guide itself.

---

## 4. Tone and Language

### 4.1 Declarative, Not Conversational

- Do not address the reader conversationally
- Do not reference chats, prompts, or prior discussion
- The document must stand alone

---

### 4.2 Calm and Precise

- Avoid hype or marketing language
- Avoid defensive or apologetic phrasing
- Prefer neutral, technical clarity

---

## 5. What READMEs Are Not

READMEs in this repository are not:

- tutorials for beginners
- marketing copy
- exhaustive API references
- dumping grounds for raw logs or transcripts

They are **governance documents**.

---

## 6. Evolution Rule

READMEs may evolve, but only deliberately.

When updating a README:
- preserve existing structure where possible
- add sections rather than rewriting history
- document changes in intent, not just mechanics

Consistency across documents matters more than local perfection.

---

## 7. Summary Rule

A README that:

- explains intent
- defines scope
- documents reality
- includes the actual procedures used (including queries)
- respects the single-fence rule
- avoids cleverness

is considered compliant with the house style.

