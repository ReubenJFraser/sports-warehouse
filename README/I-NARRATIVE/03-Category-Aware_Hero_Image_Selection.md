# Category-Aware Hero Image Selection  
## Defaults, Overrides, and Collaborative Evolution

**Status:** Authoritative explanatory README  
**Audience:** Internal (developer, future self, AI assistants)  
**Scope:** How product category differences are handled in hero image selection  
**Non-goal:** Hard enforcement, aesthetic ideology, or rigid policy encoding  

---

## 1. Purpose of This Document

This document explains **why and how different product categories require different hero image treatment**, and how the Hero Image system handles those differences **without becoming brittle, simplistic, or ideological**.

It exists to clarify a core design principle:

> **Category-specific visual preferences are encoded as automated defaults,  
> but always remain subordinate to human editorial judgment.**

Crucially, it also explains *why some categories are inherently ambiguous* and cannot be resolved through simplistic rules, no matter how much research is applied.

---

## 2. The Core Problem

Products are not visually equivalent.

A hero image that works perfectly for one product category can be misleading or suboptimal for another. This is not a matter of taste or branding whim — it is a matter of **product function, anatomy, and user perception**.

However, some categories occupy **grey zones**, where:
- more than one visual logic can be valid
- context matters (e.g. browsing vs comparison)
- repetition changes what is useful to show

The Hero Image system therefore needs to:
- acknowledge real, logical category differences
- support informed automated defaults
- allow disagreement and nuance
- enable refinement through use rather than decree

---

## 3. Avoiding Two Failure Modes

This system deliberately avoids two extremes:

### ❌ Pure hard-coded rules
- brittle
- blind to context
- unable to express nuance
- prone to enforcement creep

### ❌ Purely subjective choice
- inconsistent
- unscalable
- undocumented
- opaque

Instead, the system adopts a **middle ground**:

> **Category-aware automation with explicit human override and iterative refinement.**

Automation provides informed defaults.  
Humans handle ambiguity.

---

## 4. Product Categories Impose Real Visual Constraints

Category differences are **not arbitrary preferences**.

They arise from the **physical relationship between the product and the human body**, or from the absence of that relationship altogether.

These constraints shape what information is visually useful in a hero image.

---

## 5. Category Examples

### 5.1 Tops (General Case)

**Functional interaction:**  
The garment interacts primarily with the **upper body**.

Key attributes are often clarified by:
- shoulder position
- chest orientation
- posture
- facial expression (confidence, sport context, lifestyle)

Showing the face often:
- humanises the product
- communicates intent (training vs lifestyle)
- anchors scale and proportion

**Automation implication:**  
Upper-body framing and facial context are often useful signals for ranking hero candidates.

---

### 5.2 Sports Bras (A Deliberately Ambiguous Case)

Sports bras are a **special case** that sits between clear categories.

They are:
- technically a top
- functionally closer to performance equipment
- often evaluated on fit, support, and construction rather than expression

This creates a legitimate ambiguity:

- **Including the face** can:
  - humanise the product
  - communicate athletic confidence
  - provide lifestyle or branding context

- **Excluding the face** can:
  - focus attention on construction and fit
  - reduce distraction
  - improve comparative evaluation between similar items

Even with deep research, there may be **no single correct answer**.

Instead, what tends to matter is **context and repetition**.

For example:
- When a user first encounters a group of sports bras, seeing a face *once* may help establish scale, confidence, and intent.
- When browsing multiple similar sports bras within the same filtered view, repeated facial imagery often adds little value.
- At that stage, close-up views of the garment itself (fit, straps, structure) matter more than expression.

This kind of nuance cannot be expressed as a simple rule like:
> “Sports bras should / should not show faces.”

**Automation implication:**  
Sports bras benefit from *soft defaults* rather than hard rules, and from human judgment informed by browsing context.

---

### 5.3 Leggings

**Functional interaction:**  
The garment’s function is concentrated in the **lower body**.

Primary attributes include:
- waist fit
- hip contour
- leg line
- fabric stretch and compression

Facial information is usually:
- irrelevant
- distracting
- wasteful of frame space

A strong leggings hero image often:
- crops above the waist or mid-torso
- centres the lower body
- prioritises symmetry and line over expression

**Automation implication:**  
Lower-body emphasis is a strong default signal, while facial prominence is usually down-weighted — but never forbidden.

---

### 5.4 Non-Modelled Products (e.g. Soccer Balls)

**Functional interaction:**  
There is **no human anatomy** involved.

Visual logic shifts entirely to:
- object isolation
- lighting
- texture
- brand mark legibility

Any heuristic related to faces or posture becomes meaningless.

**Automation implication:**  
Hero selection relies solely on object-centric signals.

---

## 6. Defaults Are Informed, Not Arbitrary

Where possible, category defaults should be:
- informed by research
- grounded in established visual conventions
- validated by observed outcomes

Defaults are preferable to ad hoc choice because they:
- improve consistency
- reduce cognitive load
- scale across large catalogues

However:

> **A well-researched default is still only a default.**

It must remain defeasible.

---

## 7. Overrides as a First-Class Mechanism

When an admin overrides a default:
- no error has occurred
- no rule has been violated
- no correction is implied

The override simply records that:
> “The general case did not apply here.”

This is how the system remains adaptable without becoming chaotic.

---

## 8. Collaborative Evolution Over Time

Because:
- rankings are visible
- reasoning is explainable
- overrides and rejections are recorded

…the system can evolve carefully.

Patterns may emerge such as:
- sports bras benefiting from mixed strategies
- leggings consistently favouring lower-body framing
- certain brands intentionally subverting defaults

Evolution is:
- gradual
- observable
- reversible
- grounded in use

Not ideology.

---

## 9. Why This Cannot Be Captured by Simple Rules

The sports bra example illustrates a broader truth:

> Some visual decisions depend not just on category,  
> but on **user context, repetition, and informational sufficiency**.

These factors cannot be captured by static rules alone.

They require:
- transparent automation
- human judgment
- iterative refinement

That is why the system is designed as a **managed process**, not a rules engine.

---

## 10. Form Follows Function (System-Wide)

The frontend experience appears:
- simple
- consistent
- intentional

That simplicity is achieved because complexity is handled upstream through:
- category-aware defaults
- visible reasoning
- human-in-the-loop decisions

The Candidate Images Panel is where this complexity is surfaced and managed — quietly, deliberately, and safely.

---

## 11. Guiding Principle

> **The system knows what usually works.  
> Humans decide what works here.**

Defaults assist.  
Overrides refine.  
The process evolves.

This principle governs all category-aware hero image selection.



