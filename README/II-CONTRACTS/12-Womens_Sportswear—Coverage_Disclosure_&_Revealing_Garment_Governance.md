# Women’s Sportswear — Coverage Disclosure & Revealing Garment Governance

## Purpose

This README defines **coverage-disclosure and naming governance rules** for women’s sportswear products.

Its purpose is to ensure that product rows, `subCategory` choices, and `itemName` construction reflect **real consumer decision behaviour**, particularly where garments expose parts of the body that many customers treat as **hard exclusion criteria**.

This document exists to prevent misleading discovery, late-stage rejection, and trust erosion caused by under-disclosed garment coverage.

It is a **governance document**, not a marketing guide.

---

## Scope

This README applies to:

- women’s sportswear and athleisure products
- adult customers only
- garments involving **torso, upper-body, or lower-body exposure**
- brands where revealing construction is a core design feature (e.g. Ryderwear)

This README does **not** apply to:

- children’s apparel
- men’s apparel
- footwear
- accessories
- pricing, imagery, or promotions

---

## Conceptual Boundary

This document governs **disclosure risk**, not aesthetics.

It does not define:
- what is fashionable
- what is attractive
- what should be marketed

It defines only:
- which garment attributes function as **veto axes**
- how those attributes must be surfaced in data and naming
- how to avoid hiding exclusionary characteristics inside generic labels

---

## Core Principle — Coverage Is a Veto Axis

In women’s sportswear, certain attributes behave as **binary exclusion criteria**, not preferences.

The most important of these is:

**Exposed midriff vs covered torso**

This attribute exhibits asymmetric risk:

- Many customers categorically reject garments that expose the midriff
- Far fewer customers categorically reject sleeve length, fabric, or fit
- Discovering that a garment is cropped *after* purchase intent is formed produces disproportionate dissatisfaction, returns, and loss of trust

Therefore:

**Crop vs non-crop is a veto axis.**

Any system that conceals or delays this information is materially misleading.

---

## Age and Life Stage (Behavioural Framing)

Women’s tolerance for garment exposure varies strongly across adulthood.

This variation is influenced by:
- comfort
- activity context (gym vs lifestyle vs travel)
- self-presentation goals
- skin sensitivity and resilience
- recovery, climate, and usage frequency

This document makes **no claims** about value, worth, or attractiveness at any age.

It records only the observed retail truth that:

**Tolerance for exposed torso narrows across adulthood, while preference for functional and versatile coverage increases.**

This behavioural reality directly affects rejection risk and naming obligations.

---

## Revealing Garments — Canonical Classes

For governance purposes, a **revealing garment** is one that:

- exposes body areas many customers treat as context-sensitive
- triggers rapid accept/reject decisions
- produces high dissatisfaction if exposure is discovered late

The following garments are treated as **inherently revealing**.

### Crop Tops

- Expose the midriff by definition
- Function as a hard exclusion axis
- Must be impossible to miss at discovery time

Governance rule:

- `subCategory` must anchor on **Crop Tops**
- Other physical attributes must narrow expectations, not obscure exposure

Example:

subCategory = Crop Tops  
itemName = Long Sleeve Crop Top  
itemName_fully_derived = No

---

### Sports Bras

A **Sports Bra** is revealing by definition.

Characteristics:
- exposes the upper torso and midriff
- removes the visual framing of a top layer
- strongly signals a performance-specific context

Governance implications:

- `subCategory = Sports Bras` is already self-disclosing
- users selecting this category have already consented to high exposure
- additional euphemism or concealment is inappropriate

Further differentiation (support level, construction, structural type) may be added where applicable, but exposure itself requires no additional warning.

---

### Shorts and Compression Reality (Brand-Specific)

In some brands (notably Ryderwear), **all shorts are compression shorts in practice**, even when the word “compression” does not appear in the itemName.

Observed product reality:

- shorts are form-fitting and body-contouring
- they expose thigh shape, hip structure, and glute outline
- they are revealing in effect, even if not explicitly named as such

At present:

- no `compression_level` column exists
- compression is treated as **brand-level truth**, not an item-level differentiator
- exposure risk is managed implicitly through:
  - `subCategory = Shorts`
  - consistent brand behaviour
  - imagery

This approach is acceptable **only while** compression is uniform across the range.

---

## Naming and subCategory Anchoring Rules

### High-Risk Attribute Anchoring

When a garment combines multiple coverage-relevant characteristics:

- the **highest-risk exclusion axis anchors `subCategory`**
- other real, stable characteristics must appear explicitly in `itemName`

For women’s tops:

- crop vs non-crop outranks sleeve length
- therefore crop anchors, sleeves narrow

Correct example:

subCategory = Crop Tops  
itemName = Long Sleeve Crop Top

Incorrect example:

subCategory = Long Sleeve Tops  
itemName = Cropped Top

The incorrect form buries the veto condition and creates misleading discovery.

---

## itemName Authoring Implications

Where coverage-relevant attributes are not represented as schema columns:

- editorial synthesis in `itemName` is permitted
- omission is worse than verbosity
- such cases must set `itemName_fully_derived = No`

This is intentional and auditable.

---

## Forward Schema Notes (Non-Blocking)

Future schema expansion **may** introduce:

- compression_level
- coverage_class
- sleeve_length

However:

- current governance does not depend on these columns
- disclosure obligations apply immediately
- schema expansion is an optimisation, not a prerequisite for honesty

---

## Known Gaps and Constraints

- Compression is currently implicit rather than columnar
- Age is not encoded as a data attribute
- Disclosure relies on category anchoring and naming clarity

These constraints are acknowledged and intentionally accepted.

---

## Invariants

- Coverage-related veto attributes must never be buried
- Revealing garments must be explicit at discovery time
- `subCategory` must anchor on the highest-risk exclusion axis
- `itemName` may contain multiple concrete nouns when required to describe physical reality
- Longer, explicit names are preferred over shorter, misleading ones

This is a **consumer protection and trust rule**, not a marketing preference.



