# Product-First Apparel Ontology and Personalization Rationale

## Purpose of This README

This README documents the **foundational rationale** behind the Sports Warehouse apparel data model, with particular reference to the Ryderwear brand.

It exists to explain **why the filesystem, database structure, and naming conventions are intentionally complex**, and why that complexity is necessary to support long-term goals around personalization, subscriptions, and future algorithmic or AI-driven features.

This document is not a handover checklist or an execution guide.  
It is a **conceptual governance document** intended to be re-read by humans or AI operators to understand the design intent that underpins all subsequent technical decisions.

---

## Scope

This README covers:

- The product-first (not retail-first) design philosophy
- The rationale for decomposing apparel into orthogonal axes
- Why filesystem structure mirrors product reality rather than navigation
- How this enables personalization, subscriptions, and future AI work

This README does **not** cover:

- Specific column definitions (covered elsewhere)
- ItemName derivation rules (covered elsewhere)
- Implementation details for UI, filters, or AI systems

---

## Product-First, Not Retail-First

The system is intentionally **product-first**, not retail-first.

Retail websites typically flatten product reality early in order to:

- simplify navigation
- optimise short-term conversion
- reduce implementation cost

This system does the opposite.

It decomposes products into their **true physical, functional, and aesthetic components**, even when that produces a deeper or more complex structure.

This is deliberate.

Retail navigation can always be generated later.  
Lost product reality cannot.

---

## Decomposing Apparel into Orthogonal Axes

Women’s sportswear, in particular, is governed by multiple independent decision axes that are often conflated or hidden in retail systems.

This system preserves them explicitly.

The core axes include (but are not limited to):

- **Garment type**  
  Sports Bra, Leggings, Shorts, Bodysuit  
  → Core wearable identity

- **Construction / technique**  
  Seamless, Scrunch, Ultra_Soft_Fabric  
  → Manufacturing and fit logic

- **Silhouette modifier**  
  V, High-Waisted, Mid_Rise, Cross_Waist  
  → Shape and visual effect on the body

- **Support / function**  
  Light_Support, Shelf_Bra, Underwire  
  → Performance characteristics

- **Style / neckline**  
  Halter, One_Shoulder, Square_Neck, Twist  
  → Visual and design vocabulary

- **Length / cut**  
  Cropped, Slight_Cropped, 7-8, Mid-Length  
  → Skin exposure and proportion

These axes are **orthogonal**.  
They should not be collapsed prematurely into single labels or marketing names.

---

## Why the Filesystem Mirrors Product Reality

The filesystem is not designed to be browsed by customers.

It is designed to:

- reflect how products are actually constructed
- encode compatibility and pairing logic (e.g. Scrunch sets)
- allow deterministic derivation of database rows
- support later automated analysis

Folders represent **product structure**, not menus.

For example:

- Scrunch appears as a construction family
- V appears as a silhouette modifier
- Seamless appears as a construction attribute
- High-Waisted appears as a cut modifier

This allows the system to later answer questions such as:

- “Show me all Scrunch items across collections”
- “Show me V-silhouette bottoms that are Seamless”
- “Complete this set with compatible tops”

without guesswork or heuristics.

---

## Personalization Without Intrusive Questions

A key motivation for this design is **humane personalization**.

Instead of asking users explicit or awkward questions such as:
- “Do you like revealing clothing?”

The system can infer preferences through axis combinations:

- neckline tolerance
- crop vs full length
- rise preference
- sleeve length
- support level

These can be presented visually (sliders, examples, previews) rather than verbally.

Defaults can be suggested based on user-provided context (gender, activity level, preferences), while remaining fully overridable.

This is assistive, not coercive.

---

## Why This Supports Subscriptions

Subscriptions are only viable when they deliver value that:

- cannot be scraped
- cannot be replicated by shallow UI
- improves with use

This system achieves that because the value lies in:

- interpretation, not inventory
- structure, not novelty
- cumulative understanding, not impulse

Subscribers are not paying to “see more items”.  
They are paying to:

- reduce search friction
- avoid regret purchases
- feel understood without being labelled

---

## Preparing for Algorithms and AI (Without Premature Dependence)

The system is intentionally designed **before** any AI is introduced.

This is critical.

Because:

- axes are already explicit
- naming is normalized
- structure is deterministic

Future algorithmic or AI work becomes:

- preference inference
- similarity detection
- cross-brand mapping
- set completion

rather than data cleanup or schema repair.

This makes the dataset suitable for future academic or applied work in algorithms or AI without requiring redesign.

---

## Guiding Principles

- Preserve product reality over navigation convenience
- Keep decision axes independent
- Encode meaning once, reuse everywhere
- Prefer explicit structure to inferred heuristics
- Accept complexity at the data layer to simplify user experience later

This README exists to ensure those principles remain intact as the system evolves.
