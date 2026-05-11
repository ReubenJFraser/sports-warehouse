# Product-First, Axis-Based Data Model  
## Canonical Rationale and Design Principles

---

## 1. Purpose of This README

This document defines the **conceptual foundation** of the Sports Warehouse data model as applied to complex apparel brands (notably Ryderwear).

It exists to explain **why the folder structures and Excel rows are organized the way they are**, and to prevent future refactors that mistakenly simplify or flatten the model in ways that would destroy its long-term value.

This is not a tutorial.  
This is a **design-intent and rationale document**.

---

## 2. The Core Principle: Product-First, Not Retail-First

Most retail websites organize products to optimize **navigation and sales conversion**:

- flattened categories
- duplicated products across multiple menus
- marketing-driven groupings
- ambiguity tolerated for speed

This system deliberately **does not** do that.

Instead, it models **product reality first**, even when that produces deeper or more complex structures.

The guiding rule is:

> **Retail presentation may simplify reality.  
> Data governance must not.**

---

## 3. Why the Structure Appears “Complex”

The folder trees (especially for Ryderwear) often look complex because they are **decomposing reality**, not presenting it.

Each level in the tree represents a **distinct, orthogonal dimension** of the product:

- what the garment *is*
- how it is *constructed*
- how it *fits or shapes the body*
- how it *functions*
- how it *looks*

Flattening these dimensions into a single label (as retail sites do) permanently destroys information that cannot be recovered later.

This project preserves that information.

---

## 4. Axis-Based Decomposition Model

Products are decomposed along **independent axes**.  
These axes are later recombined dynamically via filtering, personalization, and recommendation logic.

The core axes are:

| Axis | Examples | Purpose |
|---|---|---|
| **Garment type** | Sports Bra, Leggings, Shorts, Bodysuit | Core wearable identity |
| **Construction / technique** | Scrunch, Seamless, Ultra_Soft_Fabric | Manufacturing & fit logic |
| **Silhouette modifier** | V, High-Waisted, Mid-Rise, Cross-Waist | Shape & visual effect |
| **Support / function** | Light Support, Shelf Bra, Underwire | Performance characteristics |
| **Style / neckline** | Halter, One-Shoulder, Square Neck, Twist | Visual / design vocabulary |
| **Length / cut** | Cropped, Slight Cropped, 7-8, Mid-Length | Proportions & coverage |

Each axis is **conceptually independent**.

No axis is allowed to silently absorb another.

---

## 5. Why This Enables Personalization (and Not Just Cataloging)

Because product attributes are decomposed rather than flattened, the system enables **true personalization**, not just filtering by category.

A subscriber can eventually express preferences such as:

- prefers seamless construction
- avoids scrunch
- prefers high-waisted silhouettes
- prefers low coverage or high coverage
- prefers certain necklines
- prefers certain garment lengths

These preferences can be:

- explicitly selected (checkboxes, sliders)
- inferred (from user profile data)
- suggested, then manually overridden

This is **not possible** with a retail-flattened data model.

Ryderwear is an ideal proving ground because:
- its catalogue is large
- its design language is consistent
- its variations are meaningful, not cosmetic

---

## 6. Folder Trees as Truthful Product Maps

The folder system is not a navigation system.

It is a **truthful product map**.

Key rules:

- Folders represent **attributes**, not SKUs
- Colour folders are treated as terminal leaves
- Intermediate folders encode product logic, not marketing labels
- Some branches are deeper than others because the products genuinely differ

Inconsistency in folder depth usually reflects **real product variation**, not design error.

---

## 7. Editorial Judgment Is Explicit, Not Hidden

Not every product name can be perfectly derived from folder structure alone.

When judgment is required:
- it is recorded explicitly
- it is surfaced via `itemName_fully_derived = No`
- it is never silently “fixed”

This preserves:
- auditability
- future correction
- AI-assisted refinement later

Ambiguity is treated as data, not as a problem to hide.

---

## 8. Why This Matters Long-Term

This system is intentionally designed to support:

- advanced filtering
- personalization engines
- recommendation systems
- algorithmic analysis
- AI-assisted shopping tools
- future academic or technical work (e.g. algorithms, AI diplomas)

Once product reality is flattened, **none of this is recoverable**.

The complexity here is **front-loaded by design** so that everything built on top of it can be simpler, smarter, and more powerful.

---

## 9. Scope of This Document

This README defines:

- **why** the model exists
- **how** to think about the structure
- **what must not be violated**

It does **not** define:
- Excel column mechanics
- naming derivation rules
- implementation steps

Those are defined in separate canonical documents.

---

## 10. Canonical Status

This document is **authoritative** for conceptual intent.

Any future changes to:
- folder organization
- attribute classification
- axis definitions

must remain consistent with the principles defined here.

This is the mental model against which all implementation decisions are validated.

---
