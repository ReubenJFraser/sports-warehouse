# Codex — Routing Invariants (Sports Warehouse Project)

## Non-Negotiable Routing Authority Rules

### 1. **Routing is a meaning system**
- URLs declare intent.
- PHP interprets intent.
- SQL executes intent.
- No layer may reinterpret meaning differently.

### 2. **Canonical routing values are authoritative**
- Only canonical values may be used internally.
- Aliases, variants, and heuristics are forbidden.
- If a value is not canonical, it must not be accepted.

### 3. **Read-only truth precedes routing action**
- Routing behavior must be provable by inspection.
- Codex must not change rewrites or parameter handling blindly.
- No assumptions about request state are allowed.

---

## Identity Rules (Critical)

### 4. **`gender`**
- Valid values are:
  - `men`
  - `women`
  - `kids`
- No other spellings or variants are permitted.

### 5. **`ageGroup`**
- Defined and owned by Excel.
- Valid value is:
  - `kids`
- There is exactly one age-group variant in the system.

### 6. **`size_type`**
- Valid value is:
  - `plus`
- No spaces, hyphens, or case variants are allowed.

### 7. **`section`**
- Represents the active **catalog segment**.
- Valid values are:
  - `men`
  - `women`
  - `kids`
  - `plus_size`
- `section` values must be explicit and canonical.
- `section` must not encode prose, UI labels, or inferred meaning.

---

## Rewrite Constraints

### 8. **Rewrite rules must fully declare intent**
- Every rewrite must explicitly set `section`.
- All implied filters (`gender`, `ageGroup`, `size_type`) must be set where relevant.
- Frontend code must not infer missing meaning.

### 9. **URL slugs are presentation-only**
- Slugs exist solely for human-friendly URLs.
- Slugs must always rewrite to canonical values.
- Slugs must never appear in PHP logic or SQL.

**Example:**
- `/plus-size` → `section=plus_size&size_type=plus`

---

## Catalog Mode Discipline

### 10. **The application is single-page with multiple modes**
- The site operates on a single physical entry point (`index.php`).
- Content changes are driven by routing state, not by page navigation.
- Catalog behavior is determined by `section` and filter parameters.

### 11. **Filters refine, they do not redefine**
- Filters (`brand`, `categoryID`, `size_type`, etc.) refine the active catalog segment.
- Filters must not change the semantic meaning of `section`.
- `section` must always remain explicit.

---

## Entry-Point Boundaries

### 12. **Product pages are separate lifecycles**
- `product.php` is a standalone entry point.
- It does not participate in catalog routing.
- Category or catalog context may be passed explicitly for navigation only.

---

## Routing Invariant — Rendering Helpers & Navigation State Contract

### Status: **Locked / Non-Negotiable**

This invariant governs how routing state is reflected in navigation markup and how rendering helpers must behave.

Any deviation is a **correctness bug**, not a stylistic concern.

---

### 13. **Rendering must be deterministic**
- Rendering output must be valid HTML by construction.
- Rendering must **not** rely on browser error recovery.
- Identical routing state must always produce identical markup.

Malformed or context-dependent markup is forbidden.

---

### 14. **Helpers must be composable and context-free**
Rendering helpers:
- Must not assume where or how they are embedded.
- Must not emit partial or malformed attributes.
- Must not break attribute boundaries they do not own.

Helpers may only return **self-contained, syntactically valid fragments** appropriate to their documented scope.

---

### 15. **Class helpers are class-only**
Helpers whose purpose is to determine active state:
- May return **only class-name fragments** (e.g. `' selected'`)
- Must not emit:
  - quotation marks
  - HTML attributes
  - ARIA attributes
  - attribute delimiters

**Canonical example:**
```php
return $isActive ? ' selected' : '';

