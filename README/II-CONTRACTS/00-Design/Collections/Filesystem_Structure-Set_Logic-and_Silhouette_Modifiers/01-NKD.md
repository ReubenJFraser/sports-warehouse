# NKD Collection — Filesystem Structure, Set Logic, and Silhouette Modifiers

## Purpose

This README documents the **authoritative filesystem logic** used to organise the Ryderwear **NKD** collection.

It explains:
- why the NKD folder tree is structured as it is
- how shared nouns are factored out at folder level and re-expanded in product naming
- how set compatibility is encoded structurally
- how construction families (e.g. Scrunch) and silhouette modifiers (e.g. V) interact
- how this structure enables pattern recognition across collections over time

This document describes **filesystem truth and organising intent**, not database schema, naming contracts, or coverage-disclosure rules (those are defined elsewhere).

---

## Scope

This README applies to:

- `images/Clothing/Ryderwear/Womens/NKD/`
- folder-level organisation decisions
- interpretation of folder roles and relationships
- use of the filesystem as a pattern-recognition substrate for AI

It does **not** define:
- `itemName` derivation rules
- `subCategory` selection
- coverage or revealing-garment governance
- colour-naming rules beyond structural behaviour

---

## Core Principle: NKD as a Matching Universe

The NKD collection is treated as a **colour-coherent matching universe**.

All product items within NKD are designed to match **by colour**, regardless of garment type, provided they share the same colour identity.

The filesystem is therefore optimised to:
- keep matchable items spatially close
- make set relationships visible during asset work
- reduce duplication of shared concepts

Classification purity is secondary to **pairing clarity**.

---

## Factored Parent Nouns in the Filesystem

To avoid redundant repetition, shared parent nouns are factored out at folder level.

### Example: Sports Bras

Rather than duplicating “Sports Bra” across multiple sibling folders, NKD uses:

NKD/Sports_Bra/
├── Bandeau
├── Knot
├── One_Shoulder
├── Staples
└── Twist

Here:
- `Sports_Bra` is the shared garment noun
- each child folder represents a **type** of sports bra
- the filesystem is DRY and readable during asset ingestion

Downstream systems must **re-expand** the shared noun when constructing product names.

---

## Construction Families as Set Anchors: Scrunch

### Definition

**Scrunch** is a **construction family**, not a category.

It indicates a shared construction technique designed to:
- modify rear shaping
- visually unify tops and bottoms
- ensure strong set compatibility by colour

Any item under `NKD/Scrunch/` is intended to pair with any other Scrunch item of the same colour.

---

### Scrunch as a Structural Boundary

Scrunch is expressed explicitly at the top level:

NKD/
└── Scrunch/
    ├── Halter_Bra
    ├── Halter_Tank
    ├── Bodysuit
    └── V/
        ├── High-Waisted_Leggings
        ├── Leggings
        ├── Pocket_Leggings
        ├── Pocket_Shorts
        └── Shorts


This structure encodes the invariant rule:

> **Anything Scrunch pairs with anything else Scrunch, by colour.**

The filesystem makes this rule visible without inference.

---

## Silhouette Modifiers: V

### Definition

**V** is a **silhouette modifier**, not a category and not a construction family.

It refers to a **V-shaped waistband geometry** that modifies the garment’s silhouette by:

- angling the front waistband downward into a V
- visually narrowing the waist
- emphasising the waist-to-hip transition
- altering front profile contour without changing garment type

---

### Relationship Between Scrunch and V

- **Scrunch** modifies construction and rear shaping
- **V** modifies waist geometry and front silhouette
- They operate on independent axes and therefore **stack cleanly**

This is why V appears *inside* Scrunch:

NKD/Scrunch/V/…

V does not affect set compatibility; Scrunch does.

---

## Non-Scrunch Alternatives: Embody

Some NKD tops deliberately **blur the line** between sports bras and crop tops while **not** using Scrunch construction.

These are grouped under:

NKD/Embody/
└── Sports_Crop

The purpose of `Embody` is primarily **contrast**:
- it signals “not Scrunch”
- it groups hybrid tops with similar intent
- it preserves set logic clarity

Even a single-item folder is valid when it encodes meaningful distinction.

---

## Layering Logic for Tops

NKD tops are organised according to **how they are worn**, not strict garment taxonomy.

Broadly:
- Sports bras and hybrid tops form the base layer
- Tees provide coverage and comfort
- Half-zip and square-neck tops provide warmth and external layering

Examples:

NKD/Half_Zip_Long_Sleeve_Top
NKD/Square_Neck_Top
NKD/Tee

This reflects real usage patterns rather than abstract categorisation.

---

## Colour as a Set-Matching Key

Within NKD:
- colour identity is the primary matching key
- items across different folders are intended to match if colours align
- filesystem proximity supports this during asset download and review

Colour folders may appear at variable depth and may involve composite colour paths; the organising principle remains set compatibility, not taxonomic purity.

---

## Collections as Evolutionary Snapshots

A collection is **not defined by a single feature**.

Instead, each collection represents a **distribution of features at a point in time**.

For NKD:
- Scrunch is present but not universal
- V silhouettes coexist with non-V designs
- hybrid tops coexist with traditional categories

This indicates a **transitional or exploratory phase** in the brand’s design evolution.

---

## Pattern Recognition and AI Enablement

This filesystem is intentionally structured to support **machine-assisted pattern recognition**, including:

- detecting when features (e.g. Scrunch, V) first appear
- tracking feature diffusion across collections
- inferring relative collection age without explicit dates
- surfacing user-preferred features across the brand
- comparing constructions with analogous styles in other brands

Humans are poor at this kind of large-scale, cross-collection pattern analysis.  
The filesystem, combined with explicit README guidance, makes this tractable for AI.

---

## Invariants

- NKD is a colour-coherent matching universe
- Shared nouns are factored out at folder level and re-expanded downstream
- Scrunch is a construction family and set anchor
- V is a silhouette modifier subordinate to Scrunch
- Folder placement encodes pairing logic intentionally
- Collections are defined by **patterns**, not single traits
- Filesystem structure is optimized for pairing visibility and pattern recognition, not strict taxonomy

This structure is methodical, deliberate, and designed for both human workflow and AI analysis, along with providing the structure for itemName and column organization in the Excel database.
