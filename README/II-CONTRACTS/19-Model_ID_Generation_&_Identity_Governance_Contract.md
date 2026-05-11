# Model_ID Generation & Identity Governance Contract

## Purpose

This document defines the authoritative rules governing:

- `model_id` generation
- slug construction logic
- structural population rules
- identity stability and locking behaviour
- attribute inclusion and exclusion logic

---

## Scope

This contract governs:

- The Excel `model_id` formula
- Structured reference requirements
- Column inclusion order
- Conditional token inclusion
- NULL suppression logic
- Identity locking behaviour
- Structural invariants (e.g., collection-level fabric invariance)

This contract does **not** govern:

- Folder tree architecture
- Frontend routing
- Database ingestion logic
- SEO optimisation strategy

Those are governed by separate contracts.

---

## Conceptual Roles

### Excel

- Excel is the **editorial authority**
- Excel generates `model_id`
- Excel controls structural identity
- Excel enforces token population rules

### model_id

- `model_id` is a **structural identifier**
- It is not marketing copy
- It must be stable
- It must be derivable from column inputs
- It must not duplicate invariant structural attributes

---

## Section 1 — Column Participation Order (Canonical)

`model_id` is generated in the following fixed order:

1. `brand`
2. `gender`
3. `collection` (unless NULL)
4. `subCategory`
5. `model_family` (unless NULL)
6. `fabric`
7. `construction`
8. `rise`
9. `length` (hyphens converted to underscores)
10. `sports_bra_type`
11. `support_level`
12. `variant`
13. `scrunchFlag` (conditional)
14. `invisibleFlag` (conditional)

For `Sports_Bra` subCategory rows, `support_level` must not be "null" and must be encoded in slug.

`seamless` must never be encoded in slug as a conditional construction flag.
If `Seamless` forms part of the canonical collection identifier (e.g., `Rib Seamless`), it is encoded through the collection token only.

---

## Section 2 — Authoritative Formula (Structured References Required)

The formula must use full structured references.

The authoritative formula is:

=LOWER(
TEXTJOIN("_",TRUE,
[@[brand]],
[@[gender]],
IF(OR([@[collection]]="null",[@[collection]]="NULL"),"",[@[collection]]),
[@[subCategory]],
IF(OR([@[model_family]]="null",[@[model_family]]="NULL"),"",[@[model_family]]),
IF(OR([@[fabric]]="NULL",[@[fabric]]="null"),"",[@[fabric]]),
IF(OR([@[construction]]="NULL",[@[construction]]="null"),"",[@[construction]]),
IF(OR([@[rise]]="NULL",[@[rise]]="null"),"",[@[rise]]),
IF(OR([@[length]]="NULL",[@[length]]="null"),"",SUBSTITUTE([@[length]],"-","_")),
IF(OR([@[neckline]]="NULL",[@[neckline]]="null"),"",[@[neckline]]),
IF(OR([@[strap_configuration]]="NULL",[@[strap_configuration]]="null"),"",[@[strap_configuration]]),
IF([@[subCategory]]="Sports_Bra",
   IF(OR([@[support_level]]="NULL",[@[support_level]]="null"),"",[@[support_level]]),
   ""
),
IF(OR([@[variant]]="NULL",[@[variant]]="null"),"",[@[variant]]),
IF([@[scrunchFlag]]="Yes","scrunch",""),
IF([@[invisibleFlag]]="Yes","invisible","")
))

### Requirements

- All column references must be fully structured (`[@[column]]`)
- `"null"` must be explicitly suppressed
- No raw column shorthand (`[@column]`) is permitted
- `TEXTJOIN` must use `TRUE` for blank suppression

Failure to use structured references previously caused identity breakage. This is non-negotiable.

---

## Section 3 — Conditional Tokens

### 3.1 scrunchFlag

If `scrunchFlag = "Yes"` → append `scrunch`

If `No` → append nothing.

---

### 3.2 invisibleFlag

If `invisibleFlag = "Yes"` → append `invisible`

If `No` → append nothing.

---

### 3.3 seamless (Column Retained, Slug Excluded)

- `seamless` remains a Yes/No column.
- It is used for filtering, content rendering, and structural metadata.
- It is intentionally excluded from `model_id`.

Rationale:

For Ryderwear, seamless construction is structurally dominant and would create unnecessary slug inflation.

---

## Section 4 — Fabric Invariance Rule

### 4.1 Rule

If fabric is structurally invariant within a collection, it must not be duplicated in the slug.

### Example — Rib Seamless Collection

Collection: `Rib_Seamless`

Product: Rib Scrunch Seamless Shorts  
Slug: `ryderwear_female_rib_seamless_shorts_scrunch`

Correct behaviour:
- `collection` encodes Rib_Seamless
- `fabric` column may contain Ribbed
- `seamless` column may be Yes
- Neither `ribbed` nor `seamless` are redundantly appended beyond collection

This prevents:

- Structural duplication
- Slug inflation
- Identity drift
- Artificial token repetition

### 4.2 Exception — Coordinated Set Identity (Subordinate to 4.3)

Construction features that define coordinated set identity across multiple garment types **may** be encoded in slug even if collection-level.

However:

- This exception **must not** violate the Construction / Rise Non-Redundancy Rule (Section 4.3).
- If `rise` is not `"null"`, construction tokens must avoid anatomical repetition (e.g., avoid `waist`, `waistband`).

#### Correct Application Pattern

- If `rise = "null"` (typical for `Sports_Bra`) → construction may include `rib_waistband` to encode the coordinated set identity feature.
- If `rise = high_waisted` (typical for leggings/shorts) → construction must use `foldover_ribbed` (and not `foldover_ribbed_waistband`).

#### Example — Lift 2.0 Coordinated Set

Sports Bra (rise is null, so waistband token is permitted):

`ryderwear_female_lift_2_0_sports_bra_rib_waistband_light_support`

Shorts / Leggings (rise is encoded, so waistband token is prohibited in construction):

`ryderwear_female_lift_2_0_shorts_bbl_foldover_ribbed_high_waisted`

`ryderwear_female_lift_2_0_leggings_bbl_foldover_ribbed_high_waisted`

---

## 4.3 Construction / Rise Non-Redundancy Rule

### Purpose

This rule prevents anatomical duplication and semantic inflation within `model_id`.

It clarifies the separation between:

- `rise` (silhouette axis)
- `construction` (mechanism axis)

### Rule

Anatomical terms implied by the rise axis must not reappear in construction when rise is populated.

- If `rise = high_waisted`, construction must not contain `waist` or `waistband`.
- Construction must encode mechanism only (e.g., `foldover_ribbed`).

If `rise = "null"`, anatomical construction tokens are permitted when they are materially identity-defining (e.g., `rib_waistband` for Sports_Bra coordinated sets).

### Incorrect Example (Redundant)

`ryderwear_female_sculpt_shorts_foldover_ribbed_waistband_high_waisted_scrunch`

### Correct Example

`ryderwear_female_sculpt_shorts_foldover_ribbed_high_waisted_scrunch`

### Implementation Constraint

Construction dropdown values must avoid anatomical duplication when a corresponding silhouette axis exists.

If `rise = high_waisted`, construction must use:

- `foldover_ribbed`

and not:

- `foldover_ribbed_waistband`

### Invariant

Silhouette terms belong in `rise`.

Mechanism terms belong in `construction`.

Anatomical repetition across axes is prohibited when `rise` is populated.

---

## Section 4.4 — Waistband Encoding Governance

### Purpose

This section formalises waistband encoding rules in order to:

- prevent anatomical duplication  
- prevent material redundancy  
- preserve axis separation between `collection`, `construction`, and `rise`  
- maintain deterministic and minimal slug identity  

This section operates in conjunction with:

- Section 4.1 — Fabric Invariance Rule  
- Section 4.3 — Construction / Rise Non-Redundancy Rule  

---

### Scope

This section governs:

- waistband-related construction tokens  
- interaction between `collection`, `construction`, and `rise`  
- suppression of redundant material terms  

This section does not:

- restrict the introduction of new waistband mechanisms  
- prescribe subCategory-specific behaviour  
- alter the canonical column participation order  

---

### 4.4.1 Axis Separation Principle

Waistband identity may involve three distinct semantic layers:

1. Material identity (e.g., `Rib`)  
2. Mechanism identity (e.g., `Foldover`)  
3. Silhouette identity (e.g., `High_Waisted`)  

Each layer must be encoded in its appropriate column:

- Material identity belongs in `collection` or `fabric`.  
- Mechanism identity belongs in `construction`.  
- Silhouette identity belongs in `rise`.  

Anatomical or material duplication across these layers is prohibited.

---

### 4.4.2 Rise-Populated Constraint

If `rise` is not `"null"`:

- Construction must encode mechanism only.  
- Construction must not include anatomical terms such as `waist` or `waistband`.  

#### Correct

`foldover_ribbed_high_waisted`

#### Incorrect

`rib_waistband_high_waisted`

Silhouette identity must remain exclusively in `rise`.

---

### 4.4.3 Rise-Null Allowance

If `rise = "null"`:

- Construction may encode anatomical waistband identity when materially identity-defining.  

This applies to subCategories where a rise axis does not exist.

#### Example

`rib_waistband`

This does not violate Section 4.3 because no silhouette axis is populated.

---

### 4.4.4 Collection-Level Material Suppression

If a material identity (e.g., `Rib`) is encoded at the `collection` level:

- Construction must not redundantly encode the same material token.  

In such cases, a reduced construction token may be used.

#### Example — Rib Seamless Collection

Collection: `Rib_Seamless`

Correct:

`ryderwear_female_rib_seamless_leggings_waistband_scrunch`

Incorrect:

`ryderwear_female_rib_seamless_leggings_rib_waistband_scrunch`

Material identity must not be duplicated downstream.

---

### 4.4.5 Future-Proofing Constraint

This section governs semantic separation only.

It does not prohibit new waistband mechanism tokens (e.g., `compression_band`, `bonded_band`, `sculpt_band`), provided that:

- Mechanism identity remains in `construction`.  
- Silhouette identity remains in `rise`.  
- Material identity is not redundantly encoded across axes.  

---

### Invariant

Waistband encoding must preserve:

- axis purity  
- token minimality  
- non-redundancy  
- deterministic slug stability  

Material, mechanism, and silhouette must remain structurally distinct.
```

---

## Section 5 — Identity Stability

`model_id` must remain stable once published.

It may only change if:

- A structural column changes
- A collection reclassification occurs
- A taxonomy correction is formally approved

It must not change due to:

- Content rewriting
- Description changes
- SEO optimisation
- Column formatting tweaks

---

## Section 6 — NULL Governance

### 6.1 Explicit NULL Requirement

All structured columns governed by this contract must contain either:

- a valid taxonomy value, or  
- the literal value `"null"`

Blank cells are prohibited.

The absence of a value must never be represented by an empty cell.

---

### 6.2 Meaning of `"null"`

The literal value `"null"` represents:

- deliberate non-participation in slug identity  
- structural invariance within the relevant subCategory  
- intentional suppression of the column in `model_id` generation  

It does not represent missing data.

It represents an explicit structural decision.

---

### 6.3 Slug Suppression Behaviour

When a column contains `"null"`:

- it must be suppressed in the slug using the authoritative IF pattern
- it must not produce `_null_` in output
- suppression must be deterministic and formula-driven

Blank suppression must never be used as a substitute for `"null"`.

---

### 6.4 Baseline Attribute Rule

A column may be set to `"null"` when:

- the attribute is structurally invariant within the subCategory, and  
- it does not differentiate model identity, and  
- its inclusion would introduce verbosity without collision prevention  

Examples include, but are not limited to:

- `length` for Track_Pants where all variants are Full_Length  
- `rise` where waistband construction already defines archetype and no alternate rise exists  
- `model_family` for clothing categories where it does not apply  

This rule must be applied conservatively and consistently.

---

### 6.5 Governance Invariant

The use of `"null"` must:

- increase clarity  
- eliminate ambiguity  
- prevent session-dependent inference  
- preserve identity stability  

Silent omission is prohibited.

Explicit NULL governance is mandatory.

---

## Section 7 — Structural Integrity Constraints

The following constraints are invariant:

- Slug must be lowercase
- Tokens must be underscore-separated
- No double underscores
- No trailing underscores
- No hyphens inside tokens
- Length must convert hyphen to underscore

---

## Section 8 — Known Gaps & Monitoring Points

- Some legacy rows may not yet have seamless correctly populated.
- Some collections may require reclassification under Fabric Invariance logic.
- Existing slugs must be audited for redundant seamless tokens (if any).

---

## Section 9 — Guiding Principles

- Structural clarity over verbosity
- Stability over cleverness
- Governance over convenience
- Invariance must not be duplicated
- Columns may exist without slug participation
- Slug identity must reflect structure, not marketing language

This contract is authoritative for all Ryderwear Women model_id generation.
```
