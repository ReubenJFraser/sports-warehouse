# Stage 3L - Hero Selection Criteria Profiles

Date: 2026-05-12

Status: planning and documentation only. No criteria engine was implemented.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This preserves the governance principle:

Automation suggests. Manual Hero Manager selections win.

## Purpose

Stage 3L defines the hero-selection criteria profiles that can eventually guide AI-led candidate ranking and re-ranking.

A single universal image score is not sufficient because "best hero image" changes depending on product and editorial context.

A good hero image may depend on:

- product type
- garment or object region
- whether a model is present
- whether face inclusion is desirable
- whether the image is product-first or lifestyle/editorial
- whether the product is an object/accessory
- crop safety
- catalogue consistency
- brand presentation

Stage 3L does not change scoring, endpoints, UI, JSON, Python, database, authority logic, overrides, rejections, or generated diagnostic files.

## Stage 3L Status

- Stage 3L is planning/documentation only.
- No criteria engine is implemented.
- No endpoint contract is changed.
- No UI controls are added.
- No scoring formulas are changed.
- No database writes are introduced.

## Relationship To The Default Workflow

Criteria profiles fit the AI-led top-three workflow like this:

1. AI ranks candidates according to the active criteria profile.
2. The product list shows the top three candidates.
3. The administrator reviews the shortlist.
4. If the shortlist looks wrong, the administrator may:
   - manually override the selected hero
   - inspect all images
   - correct product/category metadata
   - adjust the active criteria profile

Profiles do not remove human authority. They make the AI ranking layer more explicit and easier to challenge.

## Recommendation Authority Vs Editorial Authority

### Recommendation Authority

The AI provides the default ranked shortlist according to the active criteria profile.

This means the system may say:

- this product appears to need `object_only`
- this sports bra appears to need `body_region_first`
- this tracksuit appears to need `full_outfit`
- these are the top three candidates under that profile

### Editorial Authority

The human administrator remains final authority.

The administrator can:

- accept the shortlist
- inspect all images
- manually select a different hero
- reject candidates
- correct metadata
- change criteria

Manual selection remains final.

## Criteria Dimensions

Profiles should be treated as combinations of dimensions, not arbitrary labels.

Suggested dimensions:

- `face_policy`: `avoid | optional | prefer | require`
- `subject_emphasis`: `product | body_region | full_outfit | object | lifestyle`
- `crop_policy`: `strict | balanced | editorial`
- `score_scope`: `within_product_type_only | criteria_profile_specific`
- `warning_tolerance`: `low | medium | high`
- `object_logic_required`: `true | false`
- `pose_logic_expected`: `true | false`
- `alpha_bounds_useful`: `true | false`

These dimensions should eventually guide scoring, explanation, warning interpretation, and UI wording.

## Proposed Initial Criteria Profiles

### `product_first`

Purpose:

Prioritize clear product visibility and catalogue consistency.

Suitable product types:

- simple apparel
- catalogue listing products
- products where garment clarity matters more than model expression

Visual priority:

- product/garment region visible
- safe crop
- strong fill
- minimal distraction
- consistent catalogue framing

Face handling:

- optional
- should not dominate unless relevant

Crop handling:

- strict to balanced
- avoid crops that hide the product

Expected diagnostic signals:

- reliable subject or object bounds
- crop safety
- useful ROI confidence
- low warning count

Likely ranking preferences:

- clear product visibility
- stable framing
- minimal body or face dominance unless it helps product clarity

When an administrator would choose it:

- default catalogue hero images
- product grid consistency
- ordinary product detail pages

Risks or cautions:

- may under-value expressive campaign images
- may under-value face-inclusive brand presentation

### `body_region_first`

Purpose:

Prioritize the relevant body/garment region.

Suitable product types:

- leggings
- shorts
- sports bras
- crop tops
- skirts
- skorts
- fitted activewear

Visual priority:

- relevant body region visible
- garment region not hidden
- crop supports product type
- body-region evidence is useful

Face handling:

- usually optional or de-prioritized

Crop handling:

- balanced
- crop should support the target body/garment region

Expected diagnostic signals:

- `body_region_band`
- `alpha_subject_bbox`
- pose/body-region evidence where available
- category-specific warnings when pose is missing

Likely ranking preferences:

- images that show the relevant product area clearly
- sports bra/crop images that make the upper-body garment readable
- leggings/shorts images that make lower-body garment shape readable

When an administrator would choose it:

- activewear where the product lives on a specific body region
- cases where full model presentation is less important than product fit/shape

Risks or cautions:

- current diagnostics do not perform true garment segmentation
- pose/body-region evidence must not be described as a garment mask
- missing pose may require manual review rather than automatic rejection

### `face_optional`

Purpose:

Allow face-inclusive and face-excluding images to compete without strongly rewarding or penalizing face presence.

Suitable product types:

- general modelled apparel
- mixed product/campaign photography
- cases where face presence is acceptable but not required

Visual priority:

- product visibility
- crop safety
- composition
- model presentation when useful

Face handling:

- neither required nor avoided

Crop handling:

- balanced

Expected diagnostic signals:

- face presence may be recorded, but should not dominate
- crop safety and ROI quality remain more important

Likely ranking preferences:

- well-composed images with strong product visibility
- face-inclusive images only when they do not weaken product clarity

When an administrator would choose it:

- default modelled-apparel review when there is no strong face policy

Risks or cautions:

- may not express a strong enough editorial preference when a campaign needs face-first presentation

### `face_preferred`

Purpose:

Prefer face-inclusive presentation when brand/lifestyle feel matters.

Suitable product types:

- campaign-style products
- lifestyle imagery
- products where model expression improves appeal

Visual priority:

- visible face if product visibility remains acceptable
- balanced model/product composition
- brand presentation

Face handling:

- prefer visible face
- do not allow face to dominate so much that product clarity is lost

Crop handling:

- balanced
- headroom and face visibility matter more than in product-first profiles

Expected diagnostic signals:

- face detected
- acceptable headroom
- crop safety
- product ROI still readable

Likely ranking preferences:

- modelled images with face visible and product still clear

When an administrator would choose it:

- brand pages
- collection pages
- products where model expression supports the visual story

Risks or cautions:

- should not become the default for product-first activewear unless intentionally chosen

### `face_required`

Purpose:

Require or strongly prefer a visible face.

Suitable product types:

- editorial/campaign display
- specific brand presentation choices
- administrator-driven exceptions

Visual priority:

- visible face
- safe headroom
- acceptable product visibility
- strong model presentation

Face handling:

- require or strongly prefer

Crop handling:

- balanced to editorial
- face crop and headroom should be treated as important

Expected diagnostic signals:

- face detected
- useful face visibility score
- headroom/crop safety

Likely ranking preferences:

- images where face and product both remain usable

When an administrator would choose it:

- specific campaign/editorial contexts
- when the administrator explicitly wants human connection in the hero

Risks or cautions:

- should not be default for product-first activewear
- can incorrectly penalize cropped product/editorial images that are valid under another profile

### `full_outfit`

Purpose:

Prioritize full-body or near-full-body outfit representation.

Suitable product types:

- tracksuits
- sets
- matching outfit bundles
- bodysuits
- playsuits
- hoodie and pants products
- crop top and leggings products

Visual priority:

- full silhouette
- outfit relationship visible
- model/body framing
- head/feet crop safety where relevant

Face handling:

- optional by default
- may become preferred for lifestyle presentation

Crop handling:

- strict to balanced
- crop should preserve the whole outfit relationship

Expected diagnostic signals:

- full-body ROI
- alpha subject bounds
- pose/body-region evidence where available

Likely ranking preferences:

- near-full-body images
- clear outfit pairing
- safe framing around the full silhouette

When an administrator would choose it:

- set/outfit products
- products where the relationship between pieces is the selling point

Risks or cautions:

- may under-rank detail crops that are useful but not suitable as primary hero images

### `object_only`

Purpose:

Use object/product bounds and avoid model/face/pose assumptions.

Suitable product types:

- shoes
- water bottles
- balls
- helmets
- backpacks
- gym bags
- boxing gloves
- accessories

Visual priority:

- object centered
- clean object bounds
- safe padding
- high object visibility
- no model requirement

Face handling:

- not required
- should not be considered

Crop handling:

- strict to balanced around object bounds

Expected diagnostic signals:

- `object_bbox`
- object-product classification
- no pose requirement
- no face requirement

Likely ranking preferences:

- clean product object images
- centered or well-balanced object framing
- safe padding

When an administrator would choose it:

- any object or accessory product

Risks or cautions:

- legacy headroom scoring is especially weak for object products
- object products should not be penalized for missing face or pose

### `campaign_lifestyle`

Purpose:

Prioritize brand/editorial effect over strict catalogue consistency.

Suitable product types:

- hero banners
- promotional campaigns
- visually expressive collection pages
- administrator-selected exceptions

Visual priority:

- composition
- brand feel
- model expression or scene context
- product visibility still acceptable, but not always dominant

Face handling:

- optional, preferred, or required depending on campaign intent

Crop handling:

- editorial
- more tolerance for expressive crops

Expected diagnostic signals:

- composition and visual quality indicators
- face/model signals when relevant
- warnings interpreted with higher editorial tolerance

Likely ranking preferences:

- images with stronger brand energy
- lifestyle images that still keep the product understandable

When an administrator would choose it:

- campaign rows
- editorial modules
- hero banners
- collection storytelling

Risks or cautions:

- less suitable for uniform catalogue grid hero images
- may need manual review more often

## Product-Type Default Mapping

Suggested defaults:

- sports bra: `body_region_first` or `product_first`, with `face_optional` by default
- bralette/crop: `body_region_first`, `face_optional`
- leggings/tights: `body_region_first`
- shorts/skorts/skirts: `body_region_first`
- hoodie/jacket/top: `product_first` or `face_optional`
- tracksuit/set/outfit: `full_outfit`
- bodysuit/playsuit: `full_outfit` or `body_region_first`, depending on product goal
- shoes/bottle/ball/helmet/bag/gloves: `object_only`
- campaign/editorial products: `campaign_lifestyle` or `face_preferred`

These defaults should be treated as starting assumptions. The administrator may override profile choice when the intended presentation differs.

## Sports Bra Face-Handling Example

Sports bras clarified why profiles are necessary.

Under a `body_region_first` or `product_first` profile:

- face may be de-prioritized
- upper-body garment visibility matters most
- cropped editorial/product images can still be valid
- missing face is not automatically a failure

Under a `face_preferred` or `face_required` profile:

- face-inclusive images are ranked higher
- headroom and face visibility matter more
- model presentation becomes part of the intended result

If the administrator wants a face-inclusive sports bra image and it is not in the top three, that does not automatically mean the AI failed.

It may mean:

- the AI misread the image, so diagnostics need improvement
- metadata/category is wrong, so product data needs correction
- the active profile is wrong, so criteria should change from `body_region_first` or `face_optional` to `face_preferred` or `face_required`

This distinction is central to the future Hero Manager workflow.

---

## Human Override Rationale And Criteria Exceptions

Manual hero selection is not only an override mechanism.

It is also a source of editorial evidence.

This section defines a future design direction only. It does not mean that override rationale fields, checkbox controls, storage, or reporting have been implemented.

When an administrator selects, keeps, or confirms a hero image that differs from the recommended shortlist, the system should eventually allow the administrator to record why the automatic criteria were overridden.

This should be captured as:

- structured checkbox reasons
- optional explanatory note
- product-specific saved rationale
- criteria profile active at the time
- selected hero image at the time of override
- future evidence for criteria refinement

The goal is not to force the administrator to invent a reason from scratch.

The goal is to make common override patterns visible over time.

### Why Structured Reasons Matter

Free-text notes are useful, but they are difficult to analyze consistently.

Structured checkbox reasons allow repeated editorial patterns to become visible.

For example, if many lower-body apparel products are overridden because the top-ranked candidate is rear-facing, that pattern can later inform a criteria-profile change.

Structured override reasons can help distinguish between:

1. the AI ranking was wrong
2. the active criteria profile was wrong
3. the criteria profile was technically reasonable, but editorial judgement overruled it for this product
4. the available image set contains no ideal hero image
5. the product metadata or category may need correction

This distinction matters because not every manual override means the ranking system failed.

Sometimes the system correctly follows the active criteria, but the criteria are incomplete for the real editorial situation.

### Proposed Structured Override Reasons

Possible checkbox categories:

- Top-ranked image is rear-facing / unsuitable angle
- Top-ranked image is side-facing / insufficiently clear
- Product is visible but presentation is not suitable for the primary hero image
- Product focus conflicts with editorial or brand presentation
- Full-body / model presentation preferred
- Face / model context needed despite lower product-region score
- Current criteria profile is probably wrong
- Product or category metadata may be wrong
- Diagnostics or ranking appear technically wrong
- No ideal image exists in the available image set
- Human editorial judgement overrides criteria for this product

These categories should be treated as an initial taxonomy.

They can be refined after real override patterns are observed.

### Optional Explanatory Note

A free-text note should remain available because structured categories cannot capture every editorial judgement.

The optional note should explain the specific product-level reasoning behind the override.

The note should not replace structured categories.

The intended model is:

- checkbox reasons for pattern analysis
- optional note for product-specific explanation

### Saved Override Rationale

A future implementation should save the structured reasons and optional note as part of the product's Hero Manager state.

A saved rationale record may eventually include:

- `itemId`
- selected hero image path
- active criteria profile at time of override
- selected checkbox reason codes
- optional free-text note
- timestamp
- administrator/user identifier if available
- whether the override suggests criteria refinement
- whether the issue appears to be image-set limitation, metadata problem, diagnostic problem, or editorial exception

This should not weaken manual hero authority.

Manual selection remains final.

The saved rationale simply makes the reason for that manual authority visible and useful.

### Example — Fitted Lower-Body Apparel / Booty Shorts

Fitted lower-body apparel shows why human override rationale is necessary.

A `body_region_first` profile may correctly prioritize images that focus more directly on the product region.

However, a technically product-focused image may still be unsuitable as a hero image if the angle, pose, or presentation creates the wrong editorial effect.

For example, in a booty shorts product, the top-ranked candidates may satisfy lower-body/product-region visibility better than the current hero image.

But the highest-ranked images may be rear-facing or awkwardly side-facing.

A human editor can judge that those images are not suitable as primary hero images, even if they technically satisfy the product-region criterion.

In that situation, the current hero image may be outside the top-three shortlist but still be the best available editorial choice.

### Example Selected Reasons

Example checkbox selections:

- [x] Top-ranked image is rear-facing / unsuitable angle
- [x] Top-ranked image is side-facing / insufficiently clear
- [x] Product is visible but presentation is not suitable for the primary hero image
- [x] Product focus conflicts with editorial or brand presentation
- [x] Full-body / model presentation preferred
- [x] No ideal image exists in the available image set
- [x] Human editorial judgement overrides criteria for this product

### Example Optional Note

Example optional note:

> The top-ranked candidates satisfy lower-body/product visibility better, but the rear-facing and side-facing compositions are unsuitable as hero images. The current image is outside the top-three shortlist but gives the best available overall product and brand presentation.

### Criteria Refinement Value

If similar override reasons recur across related products, those patterns should inform future criteria changes.

For example, if fitted lower-body apparel frequently receives the reason:

- Top-ranked image is rear-facing / unsuitable angle

then the `body_region_first` profile should not treat product-region visibility alone as sufficient.

It may need additional editorial suitability rules for:

- angle
- pose
- viewer perception
- brand-safe presentation
- front-facing or balanced model presentation
- whether product emphasis creates the wrong hero-image effect

This allows human override decisions to become structured feedback for future criteria refinement.

### Governance Boundary

Human override rationale does not mean automation becomes final authority.

It means the administrator's decision becomes more explainable.

The governance rule remains:

Automation suggests.

Manual curation decides.

Override rationale records why manual curation overruled the automated recommendation.

---

## Relationship To Existing Diagnostics

Stage 2D/3E diagnostics can support future criteria profiles, but they do not implement criteria-aware ranking yet.

Useful fields may include:

- `product_type`
- `path_classification_confidence`
- `roi_specificity`
- `roi_confidence`
- `roi.is_body_region_specific`
- `roi.is_garment_specific`
- warnings
- category-specific warnings
- `score.display_score: false` for first UI stages

Fields that may be useful later with caution:

- face detected
- pose detected
- crop safety score
- ROI fill score
- image quality score

These fields should remain hidden or summarized until UI wording is safe.

Diagnostics support profile-aware ranking and explanation, but do not yet provide final recommendation authority.

## What Current Data Cannot Yet Do

Current data does not yet support true criteria-aware re-ranking because:

- no `active_criteria_profile` exists in the endpoint
- no profile-specific scoring formula exists
- current candidate score is legacy Hero Manager scoring
- diagnostic score is category-scoped and not globally comparable
- face policy is not represented as an administrator-controlled setting
- product metadata does not yet define default profile per product type
- current diagnostics do not perform garment segmentation

The current top-three can only be a legacy-rank placeholder until criteria-aware ranking is designed.

## Future Data And Model Needs

Future criteria-aware ranking will need:

- `active_criteria_profile` per request, product, or product type
- default criteria profile by product type/category
- administrator override profile
- profile-specific ranking reason
- profile-specific warning interpretation
- top-three recommendation rank
- challenge/review state if needed later
- safe explanation wording for diagnostics

Future ranking should clearly distinguish:

- candidate rank under the current criteria profile
- legacy Hero Manager score
- diagnostic advisory score
- manual editorial override

## Future Endpoint Implications

Stage 3M should design a shortlist endpoint contract that can carry:

- `active_criteria_profile`
- `recommended_candidates`
- `all_candidates`
- `recommendation_rank`
- `recommendation_reason`
- `shortlist_basis`
- `recommendation_confidence`
- `criteria_profile_metadata`

The full contract should be designed in Stage 3M before implementation.

## Non-Goals

Stage 3L does not include:

- scoring implementation
- endpoint changes
- UI changes
- database migration
- criteria toggles
- automatic final hero replacement
- weakening manual override
- garment segmentation claims

## Recommended Stage 3M

Recommended next stage:

Stage 3M - Design shortlist endpoint contract.

Stage 3M should use the criteria profiles from Stage 3L to decide what the future endpoint should return before implementation.

Stage 3M should remain planning/documentation unless explicitly approved for code.

## Stage 3L Verdict

The Hero Manager needs criteria profiles before it can honestly claim criteria-aware AI ranking.

The initial profile set should be:

- `product_first`
- `body_region_first`
- `face_optional`
- `face_preferred`
- `face_required`
- `full_outfit`
- `object_only`
- `campaign_lifestyle`

These profiles should guide future shortlist contracts, ranking reasons, and eventually UI controls, while preserving manual editorial authority.
