# Stage 3AS — Future Rationale Model and Product-Specific Reason Taxonomy

## 1) Purpose
This specification checkpoint captures lessons from the manual Hero Manager / Hero Editor audit and defines a next-generation rationale model for later implementation stages.

Stage 3AS is documentation-only. It does not change runtime behavior.

## 2) Core problem discovered
The manual audit confirmed a conceptual ambiguity in the current rationale form. Current checkbox-only rationale entries can blur multiple distinct questions:

- Why was the existing/current hero weak?
- Why was the selected replacement image better?
- Why should the system top-ranked candidate be challenged?
- Was the issue actually an image integrity failure (missing/broken assets)?
- Should this record contribute to future criteria refinement?

### Specific ambiguity points
- Selected image is not explicitly stored as part of rationale meaning.
- Rejected/displaced image is not explicitly stored.
- Rejected image role is not explicit.
- Selected image rank and rejected image rank are not explicit.
- Checkboxes can be misread as describing a different image than the final selected hero.
- Missing-image records can pollute editorial criteria data if mixed with valid comparison decisions.
- Accepted-top-candidate cases should not be treated the same as manual overrides against the top candidate.

## 3) Current rationale data reliability
Current audit-era rationale records remain useful, but they are mixed-purpose records rather than clean training/evaluation data.

Recommended conceptual reliability groupings:

1. **High-value criteria-refinement records**  
   Clear image-vs-image editorial decisions with coherent reasoning and intact candidate sets.

2. **Useful editorial notes / selection corrections**  
   Operationally useful records where the final choice is sensible, but checkbox semantics may be partly ambiguous.

3. **Corrected old stored hero artifacts**  
   Records primarily fixing historical stored hero inconsistencies rather than evaluating ranking quality.

4. **Missing-image / data-quality records**  
   Records that diagnose asset failure or candidate integrity problems; useful for data ops, not direct criteria learning.

5. **Suspect checkbox records**  
   Records where box labels likely describe rejected/current images, not the selected hero (or vice versa), reducing analytic reliability.

## 4) Recommended future rationale record fields
Future rationale records should be decision-first and image-comparison explicit. Recommended fields:

- Item ID
- Product name
- Brand
- Selected image path
- Selected image rank (if known)
- Rejected/displaced image path
- Rejected/displaced image rank (if known)
- Rejected image role
- Decision type
- Product category / criteria profile
- Reviewer note
- Product-specific reason codes
- Cross-cutting signal codes
- Whether record counts toward criteria refinement
- Whether record is data-quality-only
- Timestamp and reviewer metadata (where already available or future-compatible)

## 5) Rejected image role taxonomy
Define rejected/displaced image role explicitly using one of:

- `existing_current_hero`
- `computed_baseline`
- `top_ranked_candidate`
- `top_three_candidate`
- `outside_shortlist_candidate`
- `missing_broken_image`
- `product_only_artifact`
- `historical_stored_hero`
- `temporary_fallback_image`

## 6) Decision type taxonomy
Each future rationale record should include a primary `decision_type`.

### a) `accepted_top_candidate`
- **Meaning:** Reviewer confirms the system’s top candidate is appropriate.
- **Criteria refinement:** Usually **yes** (especially when paired with coherent reasons).
- **Audit example:** #80 Adidas Hyperglam set (accepted top candidate / selection correction).

### b) `corrected_old_stored_hero`
- **Meaning:** Selection primarily corrects stale or inconsistent stored hero state.
- **Criteria refinement:** Usually **no** (or low weight); mostly operational cleanup.
- **Audit example:** Cases where stored hero artifact was corrected to current computed best.

### c) `manual_override_against_top_candidate`
- **Meaning:** Reviewer selects an image different from rank #1 based on editorial judgment.
- **Criteria refinement:** Often **yes**, if image set is healthy and rationale is clear.
- **Audit example:** General override cases where top-ranked candidate is intentionally challenged.

### d) `paired_product_differentiation`
- **Meaning:** Hero selected to avoid near-duplicate imagery across closely related products.
- **Criteria refinement:** **Yes** (valuable for collection/pairing-aware strategy).
- **Audit examples:**
  - #109 Stax Nandex Venus Skirt.
  - #111 Stax Nandex Adira Crop (top-focused hero to avoid duplicating skirt image).

### e) `product_detail_closeup_preferred`
- **Meaning:** Tight crop/detail view is preferred because it better communicates defining product features.
- **Criteria refinement:** **Yes**, category-dependent.
- **Audit example:** #94 Nike Pro Mesh 3 Inch Shorts.

### f) `model_personality_hero_preferred`
- **Meaning:** Model expression/posture/personality context is key to hero effectiveness.
- **Criteria refinement:** **Yes**, when category supports this cue.
- **Audit example:** #120 Under Armour Wordmark Strappy Sports Bra.

### g) `campaign_background_context_preferred`
- **Meaning:** Environmental/campaign background improves product narrative and click appeal.
- **Criteria refinement:** **Yes**, with guardrails to avoid overfitting to background.
- **Audit example:** #121 Wilson Crewneck Sweatshirt.

### h) `missing_image_data_failure`
- **Meaning:** Selection outcome is constrained by broken/missing image assets or invalid candidate paths.
- **Criteria refinement:** **No**; data quality bucket.
- **Audit examples:** #103 / #114 / #115 / #116 / #118 / #119.
- **Special note:** #119 Storm Vanish Track Pants represents a complete image-set failure.

### i) `temporary_best_available_image`
- **Meaning:** Reviewer chooses the least-bad available option pending data repair.
- **Criteria refinement:** Usually **no** (or explicitly excluded).
- **Audit example:** Cases where only fallback-quality assets were available.

## 7) Product-specific checkbox taxonomy
Generic cross-category checkboxes should remain available, but future UI/storage should support product-specific reason groups selected by product category / criteria profile.

### A) Sports bras / crop tops
Possible reasons:
- front product presentation preferred
- back-strap detail belongs in supporting image
- back-strap detail is defining feature
- close crop over-emphasises bust / too narrow for hero
- model face and posture needed for hero context
- paired product uses full-body image
- product needs differentiation from matching skirt/shorts
- inclusive model representation preferred

### B) Shorts / tights
Possible reasons:
- waistband / logo / mesh detail is defining feature
- close product-region image preferred
- full outfit context preferred
- sport-use context preferred
- lower-body crop too impersonal for hero
- rear view useful as support, not hero
- pocket / hem / fabric detail needs visibility

### C) Jackets / tops
Possible reasons:
- layering shown clearly
- open-zip styling preferred
- sleeve / fabric detail is defining feature
- background supports product colour/design
- static product pose weaker than styled outfit

### D) Full outfits / sets
Possible reasons:
- full outfit presentation required
- close crop hides too much of the set
- component product should use a different hero
- outfit context more important than isolated garment detail
- paired product already uses similar image

### E) Product-only / mannequin / flat-lay images
Possible reasons:
- product-only image useful as supporting detail
- lacks fit/model context for hero
- appropriate only when no model image exists
- product-only image should not outrank strong worn image

### F) Missing image / broken asset cases
Possible reasons:
- current hero image missing
- top candidate image missing
- multiple candidates missing
- all candidates missing
- stored hero path appears invalid
- candidate set unreliable
- exclude from criteria refinement

## 8) Cross-cutting signal taxonomy
Signals applicable across categories:

- `criteria_review_signal`
- `image_set_limitation`
- `metadata_category_issue`
- `diagnostics_ranking_issue`
- `exclude_from_criteria_refinement`
- `use_for_criteria_refinement`
- `collection_context_signal`
- `product_pairing_signal`
- `data_quality_blocker`

## 9) How taxonomy should evolve
Taxonomy should evolve deliberately, not chaotically.

Recommended evolution process:

1. Store decision type plus product/category context on every record.
2. Periodically review repeated reviewer notes.
3. Identify repeated rationale patterns by category.
4. Promote repeated stable patterns into product-specific checkbox codes.
5. Version the rationale taxonomy so older records remain interpretable.
6. Avoid silently changing meaning of existing reason codes.

## 10) Future implementation stages
This document defines implementation direction only.

- **Stage 3AT:** Add decision-type and image-comparison fields to rationale storage.
- **Stage 3AU:** Add image integrity report.
- **Stage 3AV:** Add product-specific rationale UI / dynamic checkbox groups.
- **Stage 3AW:** Criteria refinement pipeline using only high-value rationale records.

Stage 3AS is the specification checkpoint that precedes these implementation stages.

## 11) Non-goals for this stage
Explicit non-goals for Stage 3AS:

- no schema changes
- no migrations
- no PHP runtime changes
- no JS behavior changes
- no CSS changes
- no scoring/ranking changes
- no public frontend changes
- no browser verification claims

## 12) Acceptance criteria
This specification is complete when it:

- clearly explains why the current rationale process was conceptually flawed
- preserves the value of audit work without over-trusting checkbox data
- defines a decision-type-first rationale model
- defines a rejected-image role taxonomy
- defines product-specific checkbox groups
- distinguishes criteria-refinement records from data-quality records
- provides a practical roadmap for later implementation stages
