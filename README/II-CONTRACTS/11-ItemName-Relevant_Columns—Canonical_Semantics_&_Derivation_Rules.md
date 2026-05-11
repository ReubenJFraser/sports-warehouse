# ItemName-Relevant Columns — Canonical Semantics & Derivation Rules

## 1. Purpose

This README locks down the **authoritative meaning, usage, and interaction** of all columns that are **relevant to the construction of `itemName`** in the Sports Warehouse product database.

Its purpose is to:
- eliminate ambiguity between data fields and derived names
- prevent schema drift as new products are added
- ensure `itemName` remains natural, readable, and non-authoritative
- clearly separate **data truth** from **presentation logic**

This document is a **governance contract**, not a naming style guide.

---

## 2. Scope

This document covers **only** the following columns and their role in `itemName` derivation:

- brand
- collection
- subCategory
- variant
- colour (only by exclusion)
- itemName (derived field)

This document does **not** define:
- image paths
- pricing
- sizing
- navigation menus
- database ingestion mechanics

---

## 3. Core Principle

**itemName is derived, not authoritative.**

The database stores **structured semantic facts**.
`itemName` is a **human-facing synthesis** of those facts.

No decision about schema design, filtering, or grouping may be driven by how a name “sounds”.

---

## 4. Authoritative Column Semantics

### 4.1 brand

- Represents the **commercial brand or seller**
- Examples: Adidas, Nike, Stax, Designer
- Appears in user-facing display **outside** the `itemName` in most contexts

`brand` must **not** be embedded into `itemName` unless required by UI design.

---

### 4.2 collection

A **collection** is any named line, campaign, IP, or event that:

- intentionally groups one or more products
- is marketed as a cohesive unit
- must be preserved to allow grouping across multiple items

Examples:
- Hyperglam
- Powerreact
- Marvel Spider-Man
- UEFA Euro16
- AirLyte
- Nandex

Rules:
- Collections may be **brand-owned**, **licensed IP**, or **event-based**
- Character-level IP (e.g. Marvel Spider-Man) is a valid collection
- Collection names may be multi-word
- Collection names may appear in `itemName`

---

### 4.3 subCategory

`subCategory` represents **what the product physically is**.

It must:
- be a **concrete product noun**
- be suitable for appearing naturally in a product name
- reflect the real-world object or garment

Examples:
- Tracksuit
- Shorts
- Sports Bra
- Leggings
- Ball
- Helmet
- Water Bottle

Rules:
- `subCategory` must **never** contain meta-groupings such as:
  - Equipment
  - Apparel
  - Footwear
- If a term would never appear in a product name, it does not belong in `subCategory`
- Pluralisation must follow natural language:
  - Apparel pluralia tantum may be plural (Shorts, Leggings)
  - Countable objects must be singular (Ball, Bottle)

---

### 4.3.1 subCategory — Singularisation and Plural Conventions

`subCategory` values must follow **canonical noun form rules** to ensure consistency across data entry, derivation, filtering, and future query construction.

The governing principle is:

> **`subCategory` values are singular by default, except where the plural form is the natural or conventional noun for the product type.**

This rule exists to:
- prevent accidental schema divergence caused by grammatical variation
- ensure predictable itemName derivation
- support reliable grouping and guided query construction

---

#### Canonical Singular Forms

The following subCategories are treated as **singular nouns**:

- Sports Bra  
- T-Shirt  
- Tank Top  
- Long Sleeve  
- Crop Top  
- Bralette  
- Tube Top  
- Skirt  
- Track Pants  
- Tracksuit  
- Set  
- One-Piece  
- Bodysuit  
- Helmet  
- Ball  
- Water Bottle  
- Backpack  

These represent **countable or identity-singular objects**, even if sold or worn in pairs or sets.

---

#### Canonical Plural-by-Convention Forms

The following subCategories retain **plural form by convention**, because the plural is the natural noun in product naming and user understanding:

- Leggings  
- Flared Leggings  
- Shorts  
- Booty Shorts  
- Jackets & Hoodies  
- Swimwear  
- Sneakers  
- Soccer Boots  
- Running Shoes  
- Trainers  
- Kid’s Shoes  

These are treated as **pluralia tantum** or conventional plural product nouns and must not be singularised.

---

#### Invariant

- A `subCategory` must always match its canonical noun form exactly.
- Pluralisation must **never** be used to imply quantity.
- Pluralisation must **never** be used for stylistic variation.
- itemName derivation assumes canonical subCategory forms and must not compensate for incorrect noun usage.

Any deviation from these forms constitutes a **data error**, not an editorial choice.

### 4.4 variant

`variant` captures the **most specific meaningful differentiator** that is not already expressed by other columns.

It may represent:
- style systems (3-Stripes)
- model names (Top Glider)
- support levels (Medium Support)
- cuts or silhouettes (Flared, High-Waisted)
- object sub-types when appropriate (Backpack)

Rules:
- `variant` is intentionally flexible
- When column capacity conflicts arise, `variant` absorbs detail before schema expansion
- `variant` may contain compound phrases if that best preserves meaning
- `variant` may appear in `itemName`

`variant` must not be overloaded to encode multiple independent product attributes; when additional attributes are real but intentionally unmodelled, they may appear in `itemName` under the governed exception.

---

### 4.5 colour

- `colour` represents **visual differentiation only**
- It is included in `itemName` **only when**:
  - multiple colourways are intentionally authored
  - colour is meaningful to user selection

Rules:
- `colour = NULL` is valid and common
- Colour absence must not be compensated for elsewhere
- Colour must never be invented to satisfy structure

---

## 5. itemName Construction Rules

### 5.1 Role of itemName

`itemName` is:
- human-readable
- non-authoritative
- allowed to reorder components for linguistic clarity

It is **not**:
- a filesystem path
- a schema driver
- a substitute for missing columns

---

### 5.2 Canonical Derivation Pattern

In general, `itemName` is constructed by combining a small number of **semantically distinct components**, each drawn from a specific column with a well-defined role.

The default conceptual order is:

[Collection] [Variant] [SubCategory]

Where:

- **collection** provides campaign, IP, or line identity (e.g. Hyperglam, Powerreact, Marvel Spider-Man, UEFA Euro16)
- **variant** provides the most specific differentiator not otherwise modelled (e.g. 3-Stripes, Top Glider, Light-Up)
- **subCategory** names the concrete physical product (e.g. Set, Sports Bra, Ball, Trainers)

Examples:

- **Hyperglam 3-Stripes Set**  
  (collection = Hyperglam, variant = 3-Stripes, subCategory = Set)

- **Powerreact Medium Support Sports Bra**  
  (collection = Powerreact, subCategory = Sports Bra; “Medium Support” is a real attribute without a dedicated column and is therefore added manually under the governed exception)

- **UEFA Euro16 Top Glider Ball**  
  (collection = UEFA Euro16, variant = Top Glider, subCategory = Ball)

- **Marvel Spider-Man Light-Up Trainers**  
  (collection = Marvel Spider-Man, variant = Light-Up, subCategory = Trainers)

Rules:
- Not all components are required
- Only one value may occupy the `variant` column
- Additional real-but-unmodelled attributes may appear in `itemName` only under the governed exception described in Section 5.3
- Ordering may be adjusted for natural language
- `itemName` must always read correctly in English

---

### 5.3 Derived by Default, Editorial by Exception

`itemName` is **derived by default** from authoritative columns when all meaningful product characteristics are already expressed structurally.

Examples:
- Powerreact Training Leggings
- Zenvy High-Waisted Leggings
- MotionFlex Seamless Sports Bra

In these cases, `itemName` may be generated mechanically without loss of meaning.

However, `itemName` **may be manually authored** when it communicates **non-canonical or exceptional product characteristics** that are intentionally **not represented as schema dimensions**.

A common case for manual enrichment is when a product attribute is real and stable but no dedicated column exists for it by design; the most common example being support level for Sports Bras (e.g. Medium Support).

Support level is:
- meaningful to users
- intrinsic to the product
- specific to a narrow sub-domain (Sports Bras)

However, no `support_level` column exists, in order to avoid premature schema expansion.

In such cases:
- the attribute must not displace an existing `variant` value
- the attribute must not force new columns
- the attribute may be added manually to `itemName`

Example:
- Powerreact Training Medium Support Sports Bra  
  (`variant = 3-Stripes`, support level expressed editorially)

Other Examples:
- Hyperglam Long Sleeve Crop Top and Full-Length Leggings
- AirLyte Backless Playsuit
- Second Left Seamless Long Sleeve Scoop Crop-Top

These names describe:
- unusual combinations (Long Sleeve Crop Top)
- atypical silhouettes
- descriptive distinctions that are meaningful to users
- characteristics that do not justify new columns

In such cases:
- columns remain minimal and canonical
- `itemName` may add descriptive nuance
- `itemName` must remain consistent with column truth
- descriptive language must not contradict structural data

Manual authorship of `itemName` is therefore **a governed exception**, not a schema failure.

## 5.4 Set Semantics, Composition, and Row Governance

This section defines the **canonical rules governing Sets** as they relate to `itemName`, row structure, and data responsibility.

A Set is treated as a **composite commercial product**, not a physical garment.

---

### 5.4.1 Definition of a Set

A **Set** represents the intentional bundling of two or more **independently wearable products** that are designed to be worn together.

A Set:
- is not itself a garment
- does not introduce new physical attributes
- exists to declare coordinated availability and purchase intent

The physical truth of all garments in a Set remains defined **only** in their individual product rows.

---

### 5.4.2 Mandatory Composition Declaration in itemName

Whenever `subCategory = Set`, the `itemName` must explicitly declare the composition of the Set.

Rules:

- The word **Set** must be immediately followed by a colon (`Set:`)
- The colon introduces a **human-readable list of component items**
- Each component must:
  - be a concrete garment noun
  - already exist (or be able to exist) as its own subCategory
  - be joined using natural language (“and”)

Canonical pattern:

[Collection] [Variant] Set: [Item A] and [Item B]

Examples:
- Hyperglam 3-Stripes Set: Long Sleeve Crop Top and Full-Length Leggings
- Kids Activewear Set: T-Shirt and Shorts

Invalid examples:
- Hyperglam Set  
  (composition missing)
- Hyperglam Set: Performance Outfit  
  (non-canonical nouns)
- Set: Top + Bottom  
  (abstract placeholders)

This rule exists to prevent Sets from becoming semantically opaque.

---

### 5.4.3 Set Row Minimalism Rule

A Set row must be **deliberately minimal**.

A Set row:
- declares that a coordinated bundle exists
- declares which items compose the bundle
- may define pricing or imagery for the bundle as a concept

A Set row must **not**:
- duplicate garment-level attributes
- restate sleeve length, fit, support level, fabric, or usage
- encode attributes that belong to individual items

All garment-specific data remains authoritative **only** in the atomic product rows.

This prevents data duplication and semantic drift.

---

### 5.4.4 Relationship Between Set Rows and Item Rows

Set rows do not replace item rows.

For any Set:
- each component item must exist as its own full product row
- those rows carry complete and authoritative product data
- the Set row references items only compositionally, not structurally

The database therefore distinguishes between:
- **atomic product rows** (physical truth)
- **Set rows** (compositional availability)

No relational enforcement is implied at this stage.

---

### 5.4.5 Ordering Rule for Set Rows

When both individual items and a Set exist:

- individual product rows must appear **before** the Set row
- the Set row must appear **after** all of its component items

This ordering reflects:
- dependency direction (items define reality; sets depend on items)
- safer editorial workflows
- clearer visual scanning of the worksheet

Set rows must never interrupt the sequence of atomic items.

---

### 5.4.6 Invariant

A Set row must never become the primary source of product truth.

If a decision must be made between:
- repeating data in a Set row, or
- requiring users to inspect item rows

the latter is always correct.

Sets declare **coordination**, not **attributes**.

## 5.5 Canonical Bounded Attributes

This section promotes certain previously editorial attributes to **canonical, bounded schema dimensions**.

These attributes are:

- real and stable
- identity-defining or functionally decisive
- applicable only within tightly scoped product subsets
- repeatedly observed across current and incoming product ranges (notably Ryderwear)

They therefore justify dedicated columns **now**, not later.

The attributes defined here are:

- `support_level`
- `sports_bra_type`
- `seamless`

Each attribute is governed by explicit **applicability bounds**, **naming rules**, and **itemName positioning rules**.

### 5.5.0 Shared Applicability Rule for Sports Bra Attributes

The following attributes share an identical applicability bound:

- `support_level`
- `sports_bra_type`

They apply **only when**:

subCategory = Sports Bra


They must be **NULL** for all other subCategories.

This bounded applicability is mandatory and must not be restated per attribute.

### 5.5.1 support_level

#### Definition

`support_level` represents the **functional support intensity** of a Sports Bra.

Allowed values:

- Low
- Medium
- High

#### Applicability

For clarity: this attribute applies only when `subCategory = Sports Bra`.
The authoritative applicability rule is defined in **Section 5.5.0**.

---

#### Semantic Role

Support level is:

- the primary decision criterion for Sports Bras
- intrinsic to the product’s performance
- stable across colourways and variants

It therefore **must not** remain an editorial-only attribute.

---

#### itemName Positioning Rule

When present, `support_level` must appear:

- **immediately before** the term “Sports Bra”
- **after** collection, variant, and usage descriptors

Canonical pattern:

[Collection] [Variant] [Usage] [Support Level] Sports Bra

Examples:

- Powerreact Training **Medium Support Sports Bra**
- Swoosh **High Support Sports Bra**

Invalid forms:

- Sports Bra Medium Support
- Medium Support Powerreact Sports Bra

---

### 5.5.2 sports_bra_type

#### Definition

`sports_bra_type` represents the **structural or stylistic form** of a Sports Bra.

Examples include (non-exhaustive):

- Bandeau
- Halter
- One Shoulder
- V Neck
- Tank Bra
- Scoop
- Knot
- Twist
- Bralette

---

#### Applicability

For clarity: this attribute applies only when `subCategory = Sports Bra`.
The authoritative applicability rule is defined in **Section 5.5.0**.

---

#### Semantic Role

Sports Bra type is:

- identity-defining
- orthogonal to support level
- meaningful to users independently of branding or collection

It therefore warrants its own column rather than overloading `variant`.

---

#### itemName Positioning Rule

When present, `sports_bra_type` must:

- **immediately precede** the term “Sports Bra”
- follow `support_level` if both are present

Canonical pattern:

[Collection] [Variant] [Usage] [Support Level] [Sports Bra Type] Sports Bra

Examples:

- MotionFlex Seamless **Medium Support Halter Sports Bra**
- NKD **Low Support Bandeau Sports Bra**

Invalid forms:

- Sports Bra Halter
- Halter Medium Support Sports Bra

---

### 5.5.3 seamless

#### Definition

`seamless` is a **binary construction attribute** indicating whether a garment is manufactured using seamless construction.

Allowed values:

- Yes
- No

---

#### Applicability Bound

`seamless` applies **only when**:

subCategory IN (Sports Bra, Leggings, Shorts, Tops)

It must be **NULL** for all other subCategories.

---

#### Semantic Role

Seamless construction:

- affects comfort, fit, and performance
- is stable and non-editorial
- recurs across multiple brands and collections

It therefore qualifies as a canonical bounded attribute.

---

#### itemName Positioning Rule

When `seamless = Yes`, the term **“Seamless”** must:

- appear **before** subCategory
- appear **after** support_level and sports_bra_type (if present)

Examples:

- MotionFlex **Seamless Sports Bra**
- Adapt **Seamless Shorts**

Invalid forms:

- Sports Bra Seamless
- Seamless MotionFlex Bra

---

### 5.5.4 Colour Modifiers with Conditional itemName Elevation (Marl, Stonewash)

### Purpose

This section defines how **composite colour modifiers** such as `Marl` and `Stonewash` are treated when constructing `itemName`.

It exists to prevent incorrect elevation of colour-variant detail into product identity, while still allowing accurate naming when a colour modifier is invariant across all variants of a product.

---

### Definition

`Marl` and `Stonewash` are **colour modifiers**, not standalone colours.

They describe composite or treatment-based colour characteristics that require a second colour token to fully specify a sellable colour variant (e.g. Marl Blush, Stonewash Black).

These modifiers therefore belong primarily to **colour identity**, not product identity.

---

### Canonical Rule

A colour modifier such as `Marl` or `Stonewash` **must appear in `itemName` only when it is invariant across all colour variants of the product**.

Specifically:

- If **all** colour variants of a product are Marl or Stonewash variants, the modifier **may** be elevated into `itemName`.
- If a product has a **mixed colour set** (some solid colours, some Marl/Stonewash variants), the modifier **must not** appear in `itemName`.

In mixed sets, the modifier remains expressed **only** through the colour data and must not be generalized into the product name.

---

### Rationale

`itemName` is intended to describe **what is true of the product as a whole**, not what is true of some variants.

Elevating `Marl` or `Stonewash` into `itemName` when only a subset of colourways use that modifier would:

- misdescribe solid-colour variants
- overstate a non-invariant property
- blur the boundary between product identity and variant detail

This rule ensures that `itemName` reflects **product-level invariants only**.

---

### Examples

#### Mixed Colour Set (Most Common)

Colour variants:
- Black
- Navy
- Marl Blush
- Marl Snow Grey

Correct behaviour:
- `itemName` does **not** include “Marl”
- Marl remains part of the colour identity only

#### Uniform Marl Set

Colour variants:
- Marl Blush
- Marl Snow Grey
- Marl Grey

Correct behaviour:
- `itemName` **includes** “Marl”

---

### Relationship to itemName_fully_derived

When a colour modifier such as `Marl` or `Stonewash` is manually elevated into `itemName` under this rule, the column:

`itemName_fully_derived`

must be set to:

No

This flags intentional editorial judgment rather than mechanical derivation.

---

### Invariant

Colour modifiers must never be promoted into product identity unless they apply **universally** across all variants.

When in doubt, prefer **variant-level accuracy** over product-level generalization.

---

## 5.6 Attribute Positioning Contract

This section defines **ordering rules** when multiple attributes appear in a single `itemName`.

Ordering is governed by **semantic dependency**, not column order.

---

### 5.6.1 Apparel (Wearables)

Canonical ordering:

1. Collection
2. Variant
3. Usage (if applicable)
4. Support Level (Sports Bras only)
5. Sports Bra Type (Sports Bras only)
6. Seamless (if applicable)
7. SubCategory

Examples:

- Powerreact Training **Medium Support Seamless Sports Bra**
- Hyperglam 3-Stripes **Set: Long Sleeve Crop Top and Full-Length Leggings**
- Zenvy **High-Waisted Leggings**

---

### 5.6.2 Shoes

Canonical ordering:

1. Collection
2. Variant (model or feature)
3. SubCategory

Examples:

- Marvel Spider-Man **Light-Up Trainers**
- Gel **Kayano 26 Running Shoes**

---

### 5.6.3 Non-Wearables

Canonical ordering:

1. Collection or Event
2. Variant (model)
3. SubCategory (singular noun)

Examples:

- UEFA Euro16 **Top Glider Ball**
- Classic **Skate Helmet**
- 600ml **Water Bottle**

---

## 5.7 Invariants for Canonical Attributes

- An attribute may become canonical **only if its applicability is bounded**
- Attributes must never leak outside their declared subCategory scope
- itemName ordering is semantic, not mechanical
- Columns exist to encode reality, not to satisfy naming
- Editorial freedom decreases as schema maturity increases

Any future attribute must satisfy these same criteria before promotion to canonical status.

## 5.8 Conditional Disambiguators and itemName Governance

This section defines the role of certain columns that may **occasionally appear in `itemName`**, not because they are identity-defining in general, but because they are required to **disambiguate meaning in bounded cases**.

### 5.8.1 Conditional Disambiguators

The following columns are classified as **conditional disambiguators**:

- `ageGroup`
- `gender`

These columns:

- are **not** part of the canonical derivation pattern
- must **not** appear in `itemName` by default
- may appear **only when omission would cause ambiguity**

Their inclusion is therefore:

- **rare**
- **bounded**
- **governed**

---

### 5.8.2 Applicability Rules

`ageGroup` and `gender` may appear in `itemName` **only when all of the following are true**:

- `ageGroup = Kids`
- the product is gender-specific (e.g. Boys or Girls)
- omission would materially reduce clarity for a human reader

Typical valid cases include:

- Boys T-Shirt
- Girls Training Shorts
- Marvel Spider-Man Boys Hoodie

Invalid cases include:

- Men’s Sports Bra
- Women’s Leggings
- Adult products where gender is implied by browsing context

---

### 5.8.3 Positioning Rules

When included, conditional disambiguators must follow these ordering rules:

- appear **after** collection
- appear **before** subCategory
- must not interrupt canonical attribute ordering

Canonical pattern when used:

[Collection] [AgeGroup] [Gender] [Canonical Attributes] [SubCategory]

---

### 5.8.4 itemName_fully_derived Flag (Global Rule)

Whenever **any manual wording** is introduced into `itemName` — including but not limited to:

- conditional disambiguators (`ageGroup`, `gender`)
- descriptive attributes not represented as columns
- exceptional clarifying language

the column:

`itemName_fully_derived`

must be set to:

No

This rule applies globally and is **not limited** to age or gender.

---

### 5.8.5 Governance Rationale

This design ensures that:

- automation remains honest
- exceptions remain visible
- manual intervention is explicitly recorded
- future schema refinement is evidence-driven

The presence of conditional disambiguators in `itemName` signals **intentional editorial judgment**, not schema failure.

## 6. Explicit Exclusions

The following must **never** appear in `itemName`:

- Equipment
- Apparel
- Footwear
- Internal slugs
- Path segments
- NULL placeholders

Any term that fails this test does not belong in an itemName-relevant column.

---

## 7. Navigation vs Data (Critical Boundary)

Some concepts exist **only at the application layer**.

Examples:
- Equipment
- Apparel
- Accessories

These are:
- valid navigation categories
- valid menu items
- valid filters

They are **not** product facts and must not be stored as product data.

The database encodes **what a product is**.
The application decides **how products are grouped for users**.

---

## 8. Guiding Invariants

- itemName must always sound correct to a human
- No column exists solely to satisfy naming
- Schema complexity must be earned by data volume, not hypotheticals
- Derived views may change; stored facts must remain stable
- If a term would embarrass the product name, it does not belong in the schema

---

## 9. Summary

This document locks the following:

- itemName is derived, never authoritative
- collection carries IP, campaign, and event identity
- subCategory is always a concrete product noun
- variant absorbs specific differentiation and model identity
- colour is optional and never forced
- meta-groupings (e.g. Equipment) belong in code, not data

Any future change that violates these principles is a **schema change** and must be documented explicitly.
