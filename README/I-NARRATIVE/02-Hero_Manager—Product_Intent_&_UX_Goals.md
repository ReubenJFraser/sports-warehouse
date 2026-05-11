# Hero Manager — Product Intent & UX Goals

## Status
Authoritative intent document  
Audience: Internal (developer, future self, AI assistants)  
Scope: Hero image selection, review, and feedback tooling  
Non-goal: Enforcement, blocking, or policy-driven restriction

---

## 1. Purpose of This Document

This document defines the **core intent, goals, and boundaries** of the Hero Manager.

It exists to prevent misinterpretation of the system’s purpose and to ensure that
future development (human or AI-assisted) remains aligned with the original,
practical problem being solved.

This is **not** a governance or enforcement document.  
It is a **product intent and UX goals document**.

If there is ever ambiguity about “what this system is supposed to do,”  
this document is the first reference point.

---

## 2. The Core Problem Being Solved

Each product item typically has **multiple images** (often 5–8).

Exactly **one image must be selected** as the *hero image* for that item.

The challenges are:

- Selecting the “best” hero image is subjective
- Manual selection does not scale
- Fully automated selection is imperfect
- Some items currently fail to display a hero image at all, for unclear reasons

The Hero Manager exists to solve this **practical, visual, and editorial problem**.

---

## 3. Design Philosophy (High-Level)

The Hero Manager follows a **human-in-the-loop** design philosophy:

- Automation makes the *first suggestion*
- Humans retain the *final decision*
- Disagreement is treated as **feedback**, not failure
- Visibility and explainability are more important than rigidity

The system should:
- assist judgment
- surface information
- record human preference
- improve over time through observed patterns

The system should **not**:
- block actions
- enforce rules
- override human intent
- silently constrain choices

---

## 4. What “Automation” Means in This System

Automation in the Hero Manager means:

- Collecting all candidate images for an item
- Analysing them (e.g. orientation, headroom, faces)
- Scoring and ranking them from most to least suitable
- Selecting a default hero image based on that ranking

Automation **suggests**.  
Automation does **not decide**.

Automation is expected to be:
- imperfect
- explainable
- corrigible by humans

---

## 5. What the Hero Manager UI Is Meant to Provide

From a UX perspective, the Hero Manager should allow a human to:

### 5.1 See the Current State
- Which image is currently set as the hero
- Whether that hero was automatically selected or manually overridden
- Whether any images are missing or invalid

### 5.2 Compare Alternatives
- See *all available candidate images* for the item
- See them **ranked from most suitable to least suitable**
- Visually compare candidates side-by-side or in a clear order

### 5.3 Understand “Why”
- See indicators that explain *why* one image ranked higher than another
  (e.g. score, orientation, headroom, face detection)
- Understand the system’s reasoning well enough to disagree with it

### 5.4 Act Easily
- ❌ Reject the currently selected hero image
- ✅ Select a different candidate as the hero
- Make changes with minimal friction

The UI should feel like **curation**, not configuration.

---

## 6. Human Overrides and Feedback

Human intervention is not an edge case — it is a **first-class feature**.

When a human:
- rejects an automatically selected hero image, or
- selects a different image as the hero

That action should be:
- recorded
- auditable
- preserved as authoritative for that item

These actions represent **editorial judgment**, not error correction.

---

## 7. Why Rejections Are Recorded

Rejections are recorded **not to punish automation**, but to learn from it.

Over time, recorded rejections can answer questions such as:

- Are certain product categories (e.g. leggings, sports bras) consistently mis-ranked?
- Are certain visual features over-weighted or under-weighted?
- Does automation perform better for some brands or image styles than others?

This feedback loop exists to **improve ranking quality**, not to restrict choice.

---

## 8. Debugging and Diagnostics (Critical Secondary Goal)

A major current use of the Hero Manager is **diagnostic**.

Specifically:
- Some product items do not display a hero image at all
- The reasons are not always obvious

The Hero Manager must make it possible to determine:
- whether a hero image exists
- whether it was written successfully
- whether it was blocked by authority logic
- whether image paths are missing or invalid
- whether candidate data is incomplete

Visibility into “why something failed” is as important as selecting heroes.

---

## 9. What the Hero Manager Explicitly Does *Not* Do

To avoid future drift, it is important to state explicit non-goals.

The Hero Manager does **not**:

- enforce image quality rules
- block saving or publishing items
- prevent humans from making “bad” choices
- encode rigid policy thresholds
- replace editorial judgment
- operate autonomously without human review

Any future feature that removes human discretion is **out of scope** unless this document is explicitly revised.

---

## 10. Relationship to Other Documents

Other documents (authority contracts, audit notes, diagnostics, etc.) exist to:
- support safety
- ensure clarity
- prevent accidental damage

They are **supporting infrastructure**, not the product itself.

If any document suggests that the goal of the Hero Manager is enforcement or restriction,
that document should be revised to align with this one.

---

## 11. Definition of “Success”

From a product and UX perspective, the Hero Manager is successful when:

- Every item reliably displays a hero image
- The chosen hero is easy to understand and easy to change
- Humans trust the automation *without being constrained by it*
- Disagreements improve the system over time
- Debugging missing or incorrect heroes is straightforward
- The tool feels helpful, not adversarial

---

## 12. Guiding Principle

> **The Hero Manager exists to help humans choose better hero images,  
> not to tell them what they are allowed to choose.**

This principle overrides all secondary concerns.

---



