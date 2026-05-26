# Ryderwear Women Folder System — Restructure Review & README Draft

## 1. Purpose

This document reviews the current **RYDERWEAR\WOMEN** folder restructure and crystallizes it into a **governance README**.

Goals:
- make the taxonomy **predictable**, **auditable**, and **extensible**
- ensure the structure can drive **navigation**, **filtering**, and later **automation** (including Legacy → Special rules)
- identify remaining **inconsistencies**, **typos**, and **schema drift** before we move to the next chat session

This is not a beauty pass. It is a **semantic and structural correctness** review.

---

## Current Interpretation Note (2026-05-26)

This file remains a historical audit/review artifact. For active convention governance, use Contract 24 as the entrypoint and read Contracts 13, 15, 22, and 23 together.

The remediation suggestions in this audit are not self-executing migration authority and do not override model_id identity governance or deterministic folder-translation rules.

---

## 2. High-Level Structure Assessment

Top-level:
- `Accessories`
- `Bundles`
- `NKD`
- `Non-NKD`

This is a strong and useful partition because it separates:
- curated/high-velocity “NKD” lines vs broader archive/legacy/mixed lines (“Non-NKD”)
- simplifies rule-writing: NKD can have tighter, newer naming; Non-NKD can tolerate legacy exceptions while still being governed

Within NKD and Non-NKD, the product taxonomy is consistently anchored by:
- `Bodysuit`
- `Bottoms` → (`Leggings`, `Shorts`, plus some additions like `Skirt`, `Track_Pants`)
- `Tops` → (`Sports_Bra`, `Tank`, `Tee`, `Track_Jacket`, plus some additions)

This is the correct “retail mental model”: **product type first**.

---

## 3. Core Design Pattern (What You’ve Built)

### 3.1 “Axis-first” navigation
Inside each product type, you route into one of a small set of *axes* that represent real fit/feel distinctions:

Common axes you are using:
- `Construction` (e.g., Scrunch / Invisible / Pocket / Flared / Training)
- `Fabric` (e.g., Ultra_Soft, Ribbed, Mesh, Stonewash, Leopard, Fleece)
- `Rise` (High-Waisted / Mid_Rise / Cross_Over / Cross_Waist_Band)
- `Cut` (Halter, One_Shoulder, Twist, etc.)
- `Length` (for shorts)
- `Fit` (used where relevant; currently appears in Non-NKD Shorts under Fleece)

This is the core architectural win:
- the filesystem is now a **taxonomy**, not a dumping ground
- users (and later the site) can browse based on what actually changes the wearing experience

### 3.2 “__Collection” as the binding layer
You have adopted:
- `__Collection/<CollectionName>/...`

This is excellent governance. It clearly distinguishes:
- **attribute axes** (construction/fabric/rise/cut)
from
- the brand’s **collection identity** (Lift_2.0, Sculpt, Tempo, Contour, Icon, Replay, etc.)

It also supports future automation cleanly:
- matching logic (tops ↔ bottoms) is always done at the `__Collection` level
- Legacy classification is a **collection-level modifier** (or sub-namespace) rather than a random label sprinkled everywhere

### 3.3 Colorway leaves
Colorways mostly live at the leaves:
- the tree tends to terminate in color folders: `Black`, `Chocolate`, `Rosie`, etc.

This is also correct:
- it prevents early “color explosion”
- it lets you layer multiple axes before you split into colorways

---

## 4. What Is Working Very Well

### 4.1 Non-NKD Sculpt is coherent
The Sculpt mapping in Non-NKD is among the strongest parts of the tree:
- Leggings: `Rise → High-Waisted → Scrunch → Minimal → Seamless → __Collection → Sculpt → colors`
- Shorts: multiple routes exist but still reconcile into Sculpt/collection nodes
- Sports bras: Sculpt is expressed under `Cut → Halter → Seamless → Cut → Low_Support → __Collection → Sculpt → Halter_Bra → colors` and also under `Low_Support → Mini_Bra → Seamless → __Collection → Sculpt → colors`

This is the kind of “multi-entry but convergent” taxonomy you want:
- users can arrive from different browsing instincts (cut vs support vs fabric)
- but data still resolves into the same collection/color leaves

### 4.2 Legacy as a sub-namespace is now actionable
You have begun to apply `Legacy/` in several places. This matters because it enables:
- automated “Special” tagging
- automated discount stratification (e.g., 50% baseline for legacy; 60–80% based on mismatch/stock logic)

This is aligned with the larger plan:
- **Legacy** is not just historical—it becomes **commercial logic**.

### 4.3 Tempo handled as “Training” + Fabric/Fit
For the relaxed fleece shorts you created:
- `Shorts → Construction → Non-Scrunch → Training → Fabric → Ultra_Soft → Fleece → Fit → Relaxed → __Collection → Tempo`

This is semantically sound:
- “Relaxed” is a fit qualifier that materially changes the product silhouette
- “Fleece” is a fabric identity that is not interchangeable with typical seamless scrunch ranges
- “Training” functions as the construction/silhouette category (non-scrunch, non-compression)

---

## 5. Critical Issues / Inconsistencies to Fix (Before Locking the README)

These are the main “truth bugs” in the tree.

### 5.1 Fabric naming drift: `Ultra_Soft` vs `Ultra_Soft_Fabric` vs `Ultra_Soft_Fabric`
In NKD Leggings:
- `Fabric → Ultra_Soft_Fabric`
In NKD Shorts and many Non-NKD areas:
- `Fabric → Ultra_Soft`

These must be normalized. Pick ONE canonical token.

Recommendation:
- Use **`Ultra_Soft`** as the canonical folder name.
Reason:
- it already exists widely
- it is shorter and consistent with other fabric tokens (Ribbed, Mesh, Stonewash)
- “_Fabric” suffix is redundant because the axis already says Fabric

If you need to preserve the distinction (rare), handle it as:
- `Fabric → Ultra_Soft → ...`
not:
- `Ultra_Soft_Fabric`

### 5.2 Hyphen/underscore drift: `High-Waisted` vs `High_Waisted`
Examples:
- NKD Leggings Rise: `High_Waisted`
- Non-NKD Leggings Rise: `High-Waisted`

Pick ONE style for the entire tree.

Recommendation:
- Use **underscores** consistently inside tokens: `High_Waisted`, `Mid_Rise`, `Cross_Over`, etc.
Reason:
- file systems, CSVs, and programmatic parsing all behave more predictably
- hyphens sometimes imply minus/dash semantics in tooling contexts

### 5.3 “Ultra-Soft” vs “Ultra_Soft” (typo)
In NKD Tops → Sports_Bra → Cut → Halter:
- `Fabric → Ultra_Soft` is present
But in Non-NKD Sports_Bra Halter:
- `Ultra-Soft` appears.

This must be corrected to the canonical token.

### 5.4 Case drift: `limoncello` should be `Limoncello`
Non-NKD Tops → Slouchy_Off_Shoulder_Top:
- `limoncello` is lowercase

Color tokens must be consistently cased (Title_Case with underscores).

### 5.5 Typos in color tokens: `Cobat_Blue`
Non-NKD Sports_Bra → Legacy → Terry_Towelling:
- `Cobat_Blue` should be `Cobalt_Blue`

Typos are fatal in a taxonomy because they create silent splits.

### 5.6 “Invisible” placement must be treated as scrunch-related (not seam-related)
You already identified this correctly earlier:
- “Invisible” refers to **Invisible Scrunch**, not “invisible seams.”

Current usage is mostly correct (scrunch → invisible), but there are still places where “Invisible_Scrunch” appears as a sibling under Construction without explicit Scrunch parent.

Recommendation (canonical pattern):
- If “Invisible” is about scrunch, enforce:
  - `Scrunch → Invisible`
  - or `Scrunch → Invisible_Scrunch`
But do not place it as:
  - `Seamless → Invisible` unless it is nested under Scrunch
(Seamless can be defaulted later at the data layer, but “Invisible” should never look like it modifies seams.)

### 5.7 “Non-Seamless” terminology (double negative)
This is acceptable as a *system flag* (exception marker), but it must be applied consistently.

If you keep it:
- always pair it as the explicit exception to “Seamless”
- do not create multiple synonyms (Seamed / With_Seams / etc.)

If later you want the human-friendly variant:
- migrate to `With_Seams`
But do not mix now.

---

## 6. Structural Observations and Recommended Invariants

### 6.1 One primary axis per route (avoid “axis stacking” at the same level)
Your structure is mostly good here, but NKD Leggings has places where the sequence becomes confusing:
- `Rise → Cross_Waist_Band → Ultra_Soft_Fabric → Flared`
This reads like the axis order is: Rise → (variant) → Fabric → (construction).
It may be correct, but it should be documented as an allowed special-case.

Recommended invariant:
- A route should look like:
  - Product_Type → Axis → Value → (Axis → Value...) → __Collection → Collection → Color
- If you must embed one axis value inside another axis value (e.g., Cross_Waist_Band as a Rise variant), document it explicitly as a sanctioned compound.

### 6.2 “Brand” axis under Sports_Bra is ambiguous
NKD Tops → Sports_Bra includes:
- `Brand → Embody / Refine`

This may be correct (if those are sub-brands), but if they are collections, they should live under `__Collection` instead.

Recommendation:
- If `Embody` and `Refine` are collections, move them under `__Collection`.
- If they are truly “brand families” distinct from collections, keep `Brand` but define the rule in the README (and ensure it is not used elsewhere inconsistently).

### 6.3 Where “Legacy” belongs (rule)
Right now Legacy appears both as:
- a branch under an axis (e.g., Fabric → Legacy → Leopard)
- a branch under a product line (e.g., Shorts → Construction → Non-Scrunch → Legacy → __Collection → Persist)

This is not wrong, but it must be **governed**.

Recommended invariant:
- Use `Legacy` as a modifier in exactly two allowed ways:
  1) `__Collection → Legacy → <CollectionName>`  (preferred when the collection itself is legacy)
  2) `<Axis> → Legacy → <Value>`                 (allowed when the attribute itself is a legacy print/material)
Document both as legal patterns.

---

## 7. Legacy → Specials Automation (How This Structure Enables It)

Your earlier intent is now implementable because the tree supports two essential queries:

### 7.1 Identify “legacy inventory pools”
- Everything under `Legacy/` is eligible for a baseline special.

Proposed baseline:
- Legacy default: **50% off**

### 7.2 Identify “mismatch penalty” cases (high discount)
The structure can support detection like:
- “Leggings colorway exists but matching top colorway missing within same collection.”

This becomes an **80% off** candidate because:
- the product becomes harder to sell as a set-oriented brand experience

### 7.3 Identify “scarcity bands” (dynamic discount)
Because the leaf nodes are colors, you can count remaining colorways:
- “few colors left” → 70%
- “tops have many colors but bottoms have few (minority matchable set)” → 60%

This is precisely why `__Collection` and color leaves matter:
- it makes set-completeness computable.

---

## 8. Immediate Fix List (Recommended Before Freezing)

These are small but high-leverage:

1) Normalize `Ultra_Soft_Fabric` → `Ultra_Soft` everywhere (or the reverse, but choose one).
2) Normalize `High-Waisted` vs `High_Waisted` (choose underscore).
3) Fix `Ultra-Soft` → `Ultra_Soft`.
4) Fix `limoncello` → `Limoncello`.
5) Fix `Cobat_Blue` → `Cobalt_Blue`.
6) Confirm “Invisible” always attaches to Scrunch (not Seamless).
7) Decide the canonical spelling for:
   - `Cross_Over` vs `Cross_Waist` vs `Cross_Waist_Band` vs `Cross_Waist_Band`
   and apply consistently.

If you do nothing else, do (1)–(5). Those are taxonomy integrity issues.

---

## 9. README Draft (Ready to Commit)

## 9.1 File Name
Suggested:
- `README-Ryderwear-Women-Folder_Taxonomy.md`
or, if following your contracts style:
- `README\II-CONTRACTS\<...>\Ryderwear-Women-File_Tree-Taxonomy_Contract.md`

## 9.2 README Content

### README — Ryderwear Women Folder Taxonomy Contract

#### A) Purpose
This folder tree defines the **canonical taxonomy** for Ryderwear women’s products.  
It exists to ensure consistent classification across:
- product ingestion (Excel/database)
- site navigation (filters/search)
- automation (Legacy specials, set completeness, inventory-driven pricing)

This is a **governance contract**, not a styling guide.

#### B) Top-Level Namespaces
- `Accessories` — non-apparel items
- `Bundles` — curated multi-item products (placeholder namespace)
- `NKD` — high-velocity, current-range items; stricter naming discipline
- `Non-NKD` — broader catalog including legacy ranges and discontinued collections

#### C) Product-Type First
Under NKD and Non-NKD, the first split is always product type:
- `Bodysuit`
- `Bottoms`
  - `Leggings`
  - `Shorts`
  - (plus: `Skirt`, `Track_Pants` where present)
- `Tops`
  - `Sports_Bra`
  - `Tank`
  - `Tee`
  - `Track_Jacket` etc.

This enforces a consistent retail mental model and prevents early attribute sprawl.

#### D) Axis-First Routing
After product type, navigation proceeds through one or more **axes** that represent real fit/feel differences:
- `Construction` (scrunch/non-scrunch, pocket, flared, training, etc.)
- `Fabric` (Ultra_Soft, Ribbed, Mesh, Stonewash, Leopard, Fleece, etc.)
- `Rise` (High_Waisted, Mid_Rise, Cross_Over, etc.)
- `Cut` (Halter, One_Shoulder, Twist, etc.)
- `Length` (shorts length)
- `Fit` (Relaxed, etc., used where silhouette requires it)

Axes may be chained, but only where the meaning remains clear.

#### E) `__Collection` Binding Layer
`__Collection` is the canonical binding point where axis paths converge into a named collection:

`.../<Axis>/<Value>/__Collection/<CollectionName>/<Color>`

This separation ensures:
- attributes are not confused with marketing collection identities
- matching sets can be computed reliably at the collection + color level

#### F) Colorways as Leaves
Color folders live at the leaves wherever possible.  
This prevents early color explosion and preserves navigability.

#### G) Legacy Classification
`Legacy` indicates discontinued or legacy ranges and is used to drive:
- automated “Special” tagging
- discount banding based on set completeness and scarcity

Legal patterns:
1) `__Collection/Legacy/<CollectionName>/...`
2) `<Axis>/Legacy/<Value>/...` (for legacy prints/materials)

#### H) Naming Rules
- Tokens use `Title_Case` with underscores: `High_Waisted`, `Cobalt_Blue`
- Avoid hyphens in canonical names
- Avoid mixed spellings (e.g., `Ultra_Soft` vs `Ultra-Soft`)
- Typos create silent taxonomy splits and must be corrected immediately

#### I) Special / Discount Automation (Legacy)
Baseline policy (subject to later tuning):
- Legacy default: 50% off
- orphaned items with no matching set options: up to 80% off
- scarcity bands (few colorways): 60–70% off

These rules are computed from:
- presence of `Legacy/`
- counts of remaining colorways per `__Collection`
- matching availability across tops/bottoms within a collection

---

## 10. Next Chat Session: Recommended Focus

Once the immediate fix list is applied and this README is committed, the next logical steps are:

1) Define the canonical token set (Fabric list, Rise list, Cut list) and lock it.
2) Implement “Legacy → Special” automation rules against the inventory dataset (Excel first).
3) Apply the same restructure process to the next brand/namespace using this README as the governing contract.

---
