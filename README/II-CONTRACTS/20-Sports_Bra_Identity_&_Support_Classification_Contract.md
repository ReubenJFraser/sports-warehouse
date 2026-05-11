# Sports_Bra Identity & Structural Classification Contract (Ryderwear – Female)

## Purpose

This document formalizes how `Sports_Bra` products are defined, classified, and encoded within the Ryderwear Female catalogue.

It exists to:

- eliminate ambiguity in support classification  
- enforce consistent structural identity across multiple axes  
- ensure deterministic slug generation  
- prevent drift between marketing language and database identity  
- define a stable, auditable taxonomy  

This document governs:

- `support_level`
- `neckline`
- `strap_configuration`
- `usage_category`
- `usage_subtype`
- slug encoding requirements
- structural interaction rules
- verification procedures

---

## Scope

### Covered

- Ryderwear Female `Sports_Bra` products only
- Support classification rules
- Structural identity axes for Sports_Bra
- Usage classification guidelines
- Slug encoding requirements
- Construction interaction rules

### Not Covered

- Non-Ryderwear brands
- Non-Sports_Bra subCategories
- Marketing copy strategy
- Frontend filtering logic

---

# Core Structural Rule

For all rows where:

`subCategory = Sports_Bra`

The following are mandatory:

- `support_level`
- `neckline`
- `strap_configuration`

`support_level` must never be `NULL`.

`neckline` and `strap_configuration` may be `NULL` only where explicitly permitted by this document.

All three structural axes must be encoded in the slug.

No exceptions.

---

# Axis 1 — Structural Performance Standard

## Purpose

This section defines the mandatory classification standard for `support_level` within `Sports_Bra`.

The purpose of `support_level` is to provide reliable filtering confidence regarding movement intensity and structural containment.

Support classification is a structural declaration, not a transcription of marketing language.

---

## Mandatory Rule

- `support_level` must not be `"NULL"`
- `support_level` must always be encoded in the slug
- The slug must end with one of:

  - `_low_support`
  - `_light_support`
  - `_medium_support`
  - `_maximum_support`

No exceptions.

---

## Support Level Taxonomy (Locked)

The support taxonomy is fixed at four levels:

- `Low_Support`
- `Light_Support`
- `Medium_Support`
- `Maximum_Support`

No additional categories (e.g., "No_Support") are permitted.

Bandeau styles and other minimal designs must still be classified as `Low_Support`.

## Governing Principle

Support levels must reflect structural performance reality.

Support classification must be conservative, defensible, and consistent across the catalogue.

Where marketing language conflicts with structural stability, structural analysis governs.

---

## Classification Hierarchy (Binding)

Support classification is determined by structural function. Where brand language conflicts with structural reality, structural logic governs.

When determining `support_level`, apply the following evaluation order:

1. Structural containment architecture
2. Strap stabilization geometry
3. Underwire or reinforced binding
4. Band compression integrity
5. Impact intent (low-impact vs training)
6. Brand-declared support level (secondary to structure)

---

## Conservative Assignment Rule

If ambiguity exists between `Low_Support` and `Light_Support`, default to `Low_Support`.

Inflation of support classification for aesthetic or marketing reasons is prohibited.

---

## Integrity Requirement

`support_level` must:

- Never be `NULL`
- Always be encoded in the slug
- Reflect realistic performance capability
- Remain independent from `usage_category` and `usage_subtype`

Support classification exists to preserve consumer trust in filtering outcomes.

---

## Non-Permitted Practices

The following are prohibited:

- Inferring support solely from collection name  
- Inferring support from neckline alone  
- Assigning `Light_Support` to asymmetrical or minimally stabilized constructions  
- Using aspirational marketing language as structural evidence  

---

## Invariant

Every `Sports_Bra` entry must communicate its true structural support capability.

Support level is a performance attribute, not a style descriptor.

---

## Operational Definitions

### Low_Support

`Low_Support` indicates:

- Suitable for minimal or controlled movement  
- Insufficient stabilization for dynamic gym activity  
- Not reliable for sustained vertical motion  
- Appropriate for:
  - Yoga
  - Pilates
  - Stretching
  - Lifestyle wear
  - Controlled strength work with minimal vertical displacement  

Common structural characteristics:

- Strapless construction  
- Single-shoulder construction  
- Thin or asymmetrical strap architecture  
- Minimal band reinforcement  
- Aesthetic-forward design  

Low_Support must not be represented as gym-secure.

---

### Light_Support

`Light_Support` indicates:

- Suitable for controlled gym training  
- Acceptable for:
  - Weight lifting
  - Machines
  - Controlled cardio
  - Strength circuits  
- Not suitable for high-impact running or plyometrics  

Structural requirements:

- Bilateral strap stabilization  
- Adequate band compression  
- Reinforcement beyond purely aesthetic containment  

If classified as `Light_Support`, the product must withstand a standard gym session involving controlled strength training without instability.

---

### Medium_Support

`Medium_Support` indicates:

- Suitable for moderate dynamic movement  
- Reliable containment under repeated vertical motion  
- Reinforced band architecture and/or underwire  
- Stabilizing strap geometry  

---

### Maximum_Support

`Maximum_Support` indicates:

- Designed for high-impact activity  
- Compression-grade containment  
- Reinforced multi-point stabilization  

Currently not identified within the Ryderwear Female catalogue.

---

# Support Decision Matrix — Mechanical Classification Standard

## Purpose

This section formalizes the procedural decision model for assigning `support_level` within `Sports_Bra`.

Support classification must be determined by observable structural stabilization features.

Marketing language, perceived firmness, or descriptive tone must not influence classification.

This matrix replaces intuitive judgment with mechanical evaluation.

---

## Step 1 — Automatic Medium Trigger

Assign `Medium_Support` if **any** of the following structural features are present:

- Underwire
- Structured cup reinforcement with sewn panel separation
- Reinforced multi-panel compression back (wide stabilizing back panel)
- Dual-direction load stabilization (e.g., crossover combined with wide reinforced back panel)

If none of the above conditions are met, proceed to Step 2.

---

## Step 2 — Light Support Qualification

Assign `Light_Support` if at least **two** stabilizing structural features from the list below are present.

### Stabilizing Feature List

1. Wide underbust band or reinforced elastic band
2. Wide straps (non-string, non-minimal)
3. `Crossover_Back` or `Racerback` configuration
4. Reinforced halter (wide neck strap combined with structured underbust band)
5. `Longline` construction
6. Firm compression-grade fabric paneling
7. Adjustable tension hardware (functional, not decorative)

If fewer than two features apply, proceed to Step 3.

---

# Amendment — Medium_Support Threshold Clarification (Multi-Strap Stabilization)

## Purpose

This amendment clarifies the structural threshold for assigning `Medium_Support` within the Ryderwear Female `Sports_Bra` catalogue.

It exists to:

- refine the mechanical trigger for `Medium_Support`
- prevent support inflation while allowing legitimate structural qualification
- recognize reinforced multi-strap stabilization architectures
- preserve consumer trust in filtering outcomes
- maintain conservative but realistic classification standards

This amendment updates the existing Support Decision Matrix in:

Sports_Bra Identity & Structural Classification Contract  
:contentReference[oaicite:0]{index=0}

---

## Scope

### Covered

- Structural qualification criteria for `Medium_Support`
- Multi-strap crossover stabilization configurations
- Reinforced underbust band interaction with strap architecture

### Not Covered

- Neckline taxonomy
- Strap taxonomy expansion
- Usage classification
- Slug order or encoding rules
- Non-Sports_Bra categories

---

# Amendment to Support Decision Matrix

## Revision — Step 1 (Automatic Medium Trigger)

The existing Step 1 remains valid.

The following additional structural condition is now recognized as a valid `Medium_Support` trigger:

### Additional Automatic Medium Trigger Condition

Assign `Medium_Support` if:

- A reinforced multi-strap stabilization architecture is present  
  AND  
- The underbust band is structurally reinforced (wide compression band, not aesthetic trim)  
  AND  
- The strap architecture distributes load across multiple back anchor points beyond a single crossover or racerback configuration  

This condition applies specifically to:

- `Multi_Crossover_Back_Straps`
- Dense multi-anchor strap lattices
- Back geometries that visibly increase tension distribution and vertical containment

This condition does **not** apply to:

- Standard `Crossover_Back`
- Standard `Racerback`
- Thin decorative multi-strap arrangements
- Aesthetic strap layering without functional tension distribution

All three structural criteria must be satisfied.

If any element is ambiguous, default to `Light_Support`.

---

# Clarification — Multi-Strap Distinction

The presence of multiple straps alone does not qualify for `Medium_Support`.

The following must be visibly true:

- Strap width is sufficient to carry load  
- Strap anchor spacing increases upper-back stabilization  
- The band resists vertical displacement under tension  
- The structure suggests repeated dynamic motion containment  

Where multi-strap architecture is primarily aesthetic, classify as `Light_Support`.

---

# Governing Principle (Unchanged)

Support classification must remain:

- Conservative  
- Structurally defensible  
- Independent of marketing language  
- Independent of slug verbosity  

Structural reality governs.

---

# Application to Icon Scoop Neck Sports Bra

Under this amendment, the Icon Scoop Neck Sports Bra qualifies for `Medium_Support` if:

- Multi-crossover strap architecture distributes load across multiple anchor points  
- The elastic underbust band is compression-grade and visibly reinforced  
- Back geometry materially exceeds standard crossover containment  

If these conditions are satisfied, classification as:

`Medium_Support`

is structurally valid and contract-compliant.

If uncertainty remains, default to `Light_Support`.

---

# Invariants

- `support_level` must never be `NULL`
- `support_level` must always be encoded in the slug
- Support classification must remain mechanically derived
- Support inflation for aesthetic or marketing reasons remains prohibited
- Ambiguity defaults downward

This amendment supplements, but does not replace, the original Support Decision Matrix.

Structural integrity and consumer trust remain primary.

---

## Step 3 — Default Low Support

Assign `Low_Support` if:

- Only one stabilizing feature is present
- Strap architecture is thin, narrow, or string-based
- Back structure is minimal
- Underbust band is narrow or aesthetic
- Construction is primarily silhouette-driven rather than containment-driven

---

## Halter Clarification

Halter construction alone does not determine support level.

Apply the following:

- Thin halter with narrow band → `Low_Support`
- Wide halter combined with reinforced band and/or longline → evaluate under Step 2
- Halter may qualify for `Light_Support` but does not qualify for `Medium_Support` unless Step 1 conditions are satisfied

---

## Structural Integrity Rule

Support classification must be derived from:

- load distribution architecture
- band compression integrity
- strap stabilization geometry
- mechanical reinforcement features

Support classification must not be derived from:

- product naming conventions
- marketing descriptions such as “supportive” or “high impact”
- perceived firmness in product imagery

---

## Invariant

Every `Sports_Bra` must be evaluated using this matrix.

If ambiguity exists after application of this matrix, default to the lower support classification.

No manual overrides based on aesthetic interpretation are permitted.

---

# Axis 2 — Neckline (Front Silhouette Axis)

## Definition

`neckline` defines the primary visible front construction and silhouette archetype of the Sports_Bra.

It governs:

- neckline geometry  
- asymmetry  
- bralette vs structured silhouette  
- front shaping  

It does not govern back strap geometry.

---

## Neckline Taxonomy (Locked)

- `Bralette`
- `Halter`
- `Square_Neck`
- `Underwire`
- `Core_Bra`
- `Frame`
- `Keyhole`
- `One_Shoulder`
- `Twist`
- `Low_Neck`
- `Mini_Bra`
- `Knot`
- `Bandeau`
- `Staples`
- `Crop_Top`
- `V_Neck`
- `NULL` (Reserved for exceptional future classification)

---

## Bralette Definition

`Bralette` represents:

- minimal scoop or rounded neckline  
- thin straps  
- everyday silhouette  
- non-aggressive front shaping  
- wearable under clothing or as visible activewear  

Example:

Lift 2.0 Seamless Sports Bra  
→ `neckline = Bralette`

---

# Axis 3 — Strap Configuration (Back Geometry Axis)

## Definition

`strap_configuration` defines the structural geometry of the back strap architecture.

It governs:

- strap crossing  
- strap convergence  
- strap distribution across the upper back  

It does not govern neckline.

---

## Strap Configuration Taxonomy (Locked)

- `Crossover_Back`
- `Racerback`
- `Straight_Back`
- `Multi_Strap`
- `Keyhole_Back`
- `T_Back`
- `Halter_Back`
- `NULL` (Permitted only for strapless constructions)

---

## Bandeau Exception Rule

Where:

`neckline = Bandeau`

Then:

`strap_configuration = NULL`

`NULL` is permitted only for strapless construction.

---

# Structural Independence Rule

The structural axes are orthogonal.

- `support_level` does not imply `neckline`
- `neckline` does not imply `strap_configuration`
- `strap_configuration` does not imply `support_level`

Implicit inference is prohibited.

Each axis must be explicitly populated.

---

# Usage Classification Guideline

## Purpose

`usage_category` and `usage_subtype` define intended activity context.

They do not define structural capability.

They must remain independent from support and structural axes.

---

## Core Rule

Usage classification must be based on:

- realistic motion profile  
- structural support capacity  
- intended activity intensity  
- not marketing positioning  

Usage must not be inferred automatically from `support_level`.

---

## Independence Rule

- `support_level` does not imply `usage_category`
- `usage_category` does not imply `support_level`
- `neckline` and `strap_configuration` do not determine usage

Example:

A `Light_Support` bra may be classified as:

- `Gym / Strength`
- `Yoga / Pilates`
- `Lifestyle / Gym`

Depending on structural suitability.

---

## Default Guidance

Where structure indicates:

- controlled lifting or machine work → `Gym / Strength`
- low-impact studio movement → `Yoga / Pilates`
- high-impact movement → require `Medium_Support` or higher before assigning `Running` or dynamic categories
- fashion-forward minimal structure → may be `Lifestyle`

No new usage categories may be introduced without cross-product applicability.

---

# Slug Encoding Rule (Authoritative)

For Sports_Bra rows, the slug must encode, in order:

1. brand  
2. gender  
3. collection  
4. subCategory  
5. construction features (if applicable)  
6. `neckline`  
7. `strap_configuration`  
8. `support_level`  

Usage fields are not encoded in the slug.

All slug values must:

- be lowercase  
- use underscores  
- reflect normalized taxonomy terms only  

Example:

`ryderwear_female_lift_2_0_sports_bra_bralette_crossover_back_light_support`

Construction features may coexist with structural axes but never replace them.

---

# Construction Interaction Rule

Construction features may coexist with support encoding and structural axes.

Example:

`ryderwear_female_lift_2_0_sports_bra_rib_waistband_bralette_crossover_back_light_support`

Support level remains independent of construction encoding.

Construction never replaces support, neckline, or strap classification.

---

# Scrunch & Invisible Construction Rule

## Purpose

This section defines the semantic distinction between `scrunchFlag` and `invisibleFlag` and eliminates ambiguity regarding their application within `Sports_Bra`.

The two flags represent separate axes and must not be conflated.

---

## scrunchFlag — Feature Presence Axis

`scrunchFlag` answers:

> Does a scrunch or ruche feature exist on the product?

Values:

- `Yes`
- `No`

This flag captures the presence of:

- ruched centre seams
- gathered fabric shaping
- glute scrunch construction
- any visible scrunch or ruche design feature

It does not indicate visibility classification.

---

## invisibleFlag — Construction Visibility Axis

`invisibleFlag` answers:

> Where scrunch construction exists, is the scrunch seam constructed as invisible (hidden construction) or visible gathering?

This axis is only applicable for product categories where scrunch visibility materially affects consumer filtering.

Applicable categories include:

- Leggings
- Shorts
- Bodysuits

Values:

- `Yes` (Invisible construction)
- `No` (Visible gathering)
- `NULL` (Axis not applicable)

---

## Sports_Bra Rule (Mandatory)

For `subCategory = Sports_Bra`:

- `invisibleFlag` MUST be `NULL`
- The presence of a ruche/scrunch feature is captured only by `scrunchFlag`

This rule applies even where `scrunchFlag = Yes`.

The invisible construction axis is not a meaningful consumer filter for Sports_Bra and must not be populated.

---

## Interpretation Standard

- `NULL` means “axis not applicable,” not “unknown.”
- `Yes` or `No` must only be used where the axis is valid for the subCategory.

Incorrect population of `invisibleFlag` for Sports_Bra is prohibited.

---

## Invariant

Scrunch presence and scrunch visibility are distinct concepts.

They must never be encoded into the same axis.

---

# Ryderwear Sports_Bra Master List (Current)

The following Sports_Bra products are governed by this contract.

### NKD Collection

#### Low_Support

- NKD Core Bra  
- NKD Twist Sports Bra  
- NKD Bandeau  
- NKD Low Neck Sports Bra  
- NKD One Shoulder Sports Bra  
- NKD Knot Sports Bra  
- NKD Sweetheart Halter Bra  

#### Light_Support

- NKD Sweetheart Bra  
- NKD Twist Bandeau  
- NKD Halter Shelf Tank  
- NKD Twin Strap Halter Bra  
- NKD Halter Sports Bra  
- NKD Sweetheart T-Back Bra  
- NKD Staples Bra  
- NKD Contrast Halter Bra  
- NKD Sports Bra (Long Line Racerback)  
- NKD Embody Sports Crop  
- NKD Embody Tank Bra  
- NKD Scrunch Halter Bra  

#### Medium_Support

- NKD Underwire Sports Bra (Keyhole)

#### Maximum_Support

- None currently identified in NKD collection

---

### Known Gaps

The following collections require verification for explicit support classification:

- Honeycomb
- Empower
- Replay
- Momentum
- Logo Lux
- Activate
- Terry Towelling

Until verified, `Light_Support` may be provisionally assigned if structure indicates minimal reinforcement and no underwire.

---

# Verification Protocol

When adding or auditing a Sports_Bra:

1. Confirm `subCategory = Sports_Bra`
2. Determine `support_level`
3. Determine `neckline`
4. Determine `strap_configuration`
5. Classify `usage_category` and `usage_subtype`
6. Ensure `support_level ≠ NULL`
7. Ensure slug encodes all required structural axes
8. Confirm no duplicate structural encoding
9. Confirm no marketing-only phrases are used

---

# Invariants

- Support classification is structural, not emotional.
- Every Sports_Bra must communicate its support level.
- Structural identity must be explicit.
- Slug identity must reflect performance reality.
- No null support levels are permitted.
- Usage classification must not override structural logic.
- Implicit inference between axes is prohibited.

This document is binding for all Ryderwear Female `Sports_Bra` entries.


