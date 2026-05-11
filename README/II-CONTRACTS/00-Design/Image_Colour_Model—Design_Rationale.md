# Image Colour Model — Design Rationale

## 1. Purpose of This Document

This document explains the **reasoning, tradeoffs, and architectural decisions** behind the Image Colour Model used in the Sports Warehouse system.

It exists because the **final Excel authoring representation is intentionally simple**, while the **system behavior it enables is not**.

Without this explanation, the simplicity of the authoring surface would be misleading and prone to accidental regression.

This document is explanatory, not contractual.

---

## 2. The Core Problem This Design Solves

Template brands such as Ryderwear exhibit the following characteristics:

- A single product *style* commonly exists in:
  - six or more colours
  - sometimes a dozen or more
- Each colour has:
  - its own image folder
  - multiple images (e.g. `01.png`–`06.png`)
- Image paths are deeply structured and deterministic

At the same time:

- Humans want to author and reason about **products**, not colour explosions
- Excel is used as a governed authoring surface
- Vertical row explosion (one row per colour) is unacceptable for usability
- The system must avoid inference, guessing, or silent normalization

The design challenge is therefore:

> How do we represent multi-colour products in Excel  
> **without duplicating rows**,  
> **without forcing humans to manage image paths**,  
> and **without weakening governance**?

---

## 3. Models That Were Considered and Rejected

### 3.1 One Row Per Colour (Rejected)

In this model:
- Each colour variant becomes its own row
- Each row maps directly to one image folder

This was rejected because:

- Excel becomes vertically unreadable
- A single screen shows only one or two products
- Authoring becomes repetitive and error-prone
- Humans lose the ability to reason about products as products

This model optimizes for filesystem symmetry at the expense of human cognition.

---

### 3.2 Separate Colours / Images Sheet (Mini-PIM Model)

In this model:
- One sheet defines product styles
- Another sheet defines colour realizations
- Meaning is distributed across multiple coordinated records

This approach is conceptually clean and resembles a lightweight PIM.

However, it was rejected **for the current system phase** because it introduces:

- cross-sheet referential integrity requirements
- distributed truth (no single row declares a full product)
- semantic joins during ingestion
- additional validation and audit complexity

This model is not wrong in principle, but it **shifts complexity into Excel governance**, which contradicts the system’s design goal of keeping Excel readable, declarative, and complete at the row level.

---

### 3.3 Multi-Valued Image Paths in Excel (Partially Accepted, Then Refined)

An intermediate model allowed a single row to contain multiple image-set paths.

While technically feasible, this revealed a key insight:

- Long, verbose, machine-oriented artifacts do not belong in Excel
- Even if derived, they obscure human intent when visible

This led to the refined solution below.

---

## 4. The Final Abstraction: Style vs Colour Realization

The correct abstraction separates **meaning** from **materialization**.

### 4.1 Product Style (Authoritative, Human)

A single Excel row represents a **product style**, defined by:

- brand
- gender
- collection or model family
- subcategory / type
- structural variant (e.g. fabric)

This is how humans think about products.

---

### 4.2 Colour Realizations (Declared, Not Expanded)

Colours are treated as **realizations of a style**, not as separate products.

In Excel, colours are declared as a **comma-delimited list of lowercase slugs**:

black,white,red,yellow,green


This format is:

- compact
- readable
- easily editable
- unambiguous
- deterministic to expand

Humans declare **which colours exist**, not how images are stored.

---

### 4.3 Image Materialization (Mechanical, Downstream)

From the declared structure and colour list, downstream systems deterministically generate:

- image-set folder paths per colour
- file-level image paths
- thumbnail manifests
- hero candidates

This materialization may occur in:

- SQL (e.g. DBeaver views)
- ingestion scripts
- backend application code

This is **pure function**, not judgment.

No inference is permitted.

---

## 5. Why the Excel Output Is Deceptively Simple

The final Excel representation is intentionally minimal:

colour = black,white,red,yellow,green


This simplicity is not accidental — it is the result of careful boundary placement.

Excel expresses:
- **choice**
- **intent**
- **truth**

Downstream systems express:
- **consequences**
- **expansion**
- **mechanical detail**

The simplicity of the Excel cell hides complexity by design.

---

## 6. Governance Principles Preserved

This model preserves all core system invariants:

- Excel remains the source of semantic truth
- One row fully declares one product style
- No downstream system invents meaning
- No image paths are inferred
- All materialization is reproducible and auditable
- Verbosity lives where machines operate, not where humans author

---

## 7. When This Design Might Change

This model should be revisited only if:

- Excel becomes a relational authoring environment
- Cross-sheet referential integrity is enforced
- Product lifecycle complexity increases significantly

Until then, this design represents the best balance between:

- human usability
- system rigor
- long-term stability

---

## 8. Summary

The Image Colour Model deliberately separates:

- **what exists** (declared in Excel)
- from **how it is realized** (generated downstream)

The result is an authoring surface that is:
- readable
- scalable
- governable

Even though the reasoning is complex, the outcome is simple — and that simplicity must be protected.


