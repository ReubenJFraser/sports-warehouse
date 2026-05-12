# Stage 3J - AI-Led Top-Three Hero Candidate Workflow

Date: 2026-05-12

Status: planning and intent realignment only. No implementation was performed.

Core principle:

AI ranks by default. Human editors review exceptions, adjust criteria, or override.

This refines the existing governance principle:

Automation suggests. Manual Hero Manager selections win.

## Context

Earlier Stage 3 planning described the first UI direction as compact diagnostic badges. That was safe, but too conservative for the intended Hero Manager workflow.

The fuller intent is not simply to decorate candidate rows with diagnostic metadata. The product workflow should reduce manual image inspection by making AI ranking the normal operating layer.

Attached planning context reviewed:

`AI-Led Top-Three Hero Candidate Workflow - Stage Sequence`

The PDF confirms the revised direction: the Hero Manager should eventually become an AI-led top-three shortlisting workflow, with manual editorial authority preserved.

## Why Compact Badges Alone Are Insufficient

Compact badges are useful as supporting explanation, but they do not solve the central workflow problem.

The main problem is not that administrators lack metadata. The main problem is that administrators should not have to inspect every product image from scratch.

If the Hero Manager only adds badges to all existing candidate rows, it still asks the human editor to do too much visual sorting. That keeps the workflow image-by-image rather than product-by-product.

The better workflow is:

- AI ranks candidates first.
- The product list shows the strongest shortlist.
- The administrator scans products quickly.
- Full candidate review opens only when the shortlist looks wrong or incomplete.

Badges can still matter later, but they should explain the shortlist rather than become the main workflow.

## Intended Normal Workflow

The normal workflow should be:

1. The administrator opens the Hero Manager product list or a filtered product list.
2. Each product row shows the best three AI-ranked hero candidates.
3. The administrator scans across products quickly.
4. If one of the top three is acceptable, the administrator can confirm or leave the system-selected candidate.
5. The full image set remains hidden by default.
6. The administrator opens the full image set only when they want to challenge the shortlist.

This shifts the Hero Manager from a gallery inspection tool into an editorial review workflow.

The top-three shortlist should become the first visual surface. Full candidate browsing should become the exception path.

## Intended Challenge Workflow

The challenge workflow should be:

1. The administrator sees that the top three candidates do not match the intended hero direction.
2. They open the full candidate set.
3. They review ranking reasons and diagnostic context.
4. They may manually select a different image.
5. They may reject a candidate.
6. They may identify that the active criteria profile is wrong for the product type.
7. They may adjust criteria later when criteria/profile controls exist.

The challenge flow should not treat AI ranking as final authority. It should make the AI reasoning visible enough for the editor to decide whether the shortlist is valid.

## Recommendation Authority Vs Editorial Authority

There are two different kinds of authority.

### AI Default Recommendation Authority

The AI-led ranking system should own the default recommendation layer:

- It ranks candidate images.
- It proposes the top three.
- It reduces the first-pass decision workload.
- It surfaces likely hero options before the human opens the full image set.

This is recommendation authority, not final publishing authority.

### Human Final Editorial Authority

The human administrator remains the final authority:

- Manual selections win.
- Manual overrides remain authoritative.
- Rejections remain meaningful.
- Criteria can be challenged.
- The editor can choose an image outside the top three.

The system should make the likely choices easy, but it must never remove the human editor's ability to override.

## Why The Top Three Should Usually Be Enough

For most products, a good hero candidate should appear within the top three if:

- Product/category metadata is roughly correct.
- The candidate images are valid product images.
- The active criteria profile matches the intended presentation.
- The scoring pipeline is reading the image composition reasonably.

Showing three candidates is a useful balance:

- One candidate can feel too absolute.
- Three candidates provide choice without reintroducing full-gallery review.
- More than three risks turning the shortlist back into a browsing burden.

The top three should usually be enough to support quick editorial scanning while preserving room for judgment.

## When The Preferred Image Is Not In The Top Three

If the administrator's preferred image is not in the top three, that is useful diagnostic evidence.

It may mean:

- The AI misread the image.
- The product/category metadata is wrong.
- The active selection criteria do not match the administrator's intent.
- The product type needs a different criteria profile.
- The image set has unusual crops or presentation styles.
- A future rule needs adjustment.

This should not be treated as a simple failure. It should become feedback for improving criteria profiles and category interpretation.

## Example - Sports Bra Face Handling

Sports bra and crop products show why criteria profiles matter.

One valid strategy is product-first:

- Prioritize the garment.
- De-prioritize face.
- Accept cropped editorial images.
- Avoid penalizing missing face too strongly.

Another valid strategy is face-inclusive presentation:

- Prefer a visible model face.
- Support brand/storytelling presentation.
- Use face presence as part of the hero decision.

Neither strategy is universally correct. The intended presentation depends on the product, campaign, and merchandising goal.

Future criteria toggles should allow face handling to be configured as:

- Avoid face.
- Optional face.
- Prefer face.
- Require face.

This avoids pretending the AI has one correct answer for all product imagery.

## Future Criteria And Profile Concepts

Future Hero Manager criteria profiles may include:

- `product_first`
- `body_region_first`
- `face_optional`
- `face_preferred`
- `face_required`
- `full_outfit`
- `object_only`
- `campaign_lifestyle`

These profiles should guide how candidate ranking is interpreted and explained.

They should not weaken manual override authority.

## Relationship To Existing Diagnostics Work

The Stage 2D diagnostic JSON, Stage 3E adapter, and Stage 3H endpoint enrichment remain useful foundations.

However, their future role should be reframed:

- Diagnostics should help explain why candidates were shortlisted.
- Diagnostics should support challenge review.
- Diagnostics should reveal when metadata or criteria profiles may be wrong.
- Diagnostics should not be treated as final selection authority.

The current endpoint enrichment can support later shortlist planning, but the next stage must audit whether the current candidate data actually contains enough information to produce a reliable top-three shortlist contract.

## What Should Not Change Yet

Stage 3J does not change:

- Hero Manager UI.
- JavaScript.
- CSS.
- PHP endpoint behavior.
- Candidate scoring.
- Candidate ranking.
- Database writes.
- Automatic final hero replacement.
- Manual overrides.
- Rejections.
- Authority logic.
- JSON diagnostics.
- Python preprocessing.
- Import/update scripts.

No criteria toggles are created in this stage.

No top-three shortlist is implemented in this stage.

## Staged Roadmap After Stage 3J

### Stage 3K - Audit Current Data For Top-Three Shortlist Support

Audit whether the current candidate endpoint and diagnostics data are sufficient to support a top-three shortlist contract.

Key questions:

- Does the current endpoint already return ranked candidates in a stable order?
- Are at least three candidates available for typical products?
- Does `score` currently mean enough for shortlisting?
- Can diagnostics explain the top three without overclaiming?
- What happens when diagnostics are missing?
- Is current ranking based on the right formula for this future workflow?

### Stage 3L - Define Hero-Selection Criteria Profiles

Define criteria/profile concepts such as:

- `product_first`
- `body_region_first`
- `face_optional`
- `face_preferred`
- `face_required`
- `full_outfit`
- `object_only`
- `campaign_lifestyle`

This stage should decide wording and behavior before code.

### Stage 3M - Design Shortlist Endpoint Contract

Design the endpoint shape for a top-three shortlist.

Possible future output:

- Product ID.
- Current hero state.
- Top three candidates.
- Ranking reason summaries.
- Diagnostics availability.
- Criteria profile used.
- Challenge/full-set link or flag.

### Stage 3N - Implement Endpoint-Only Shortlist Metadata

Implement shortlist metadata in the endpoint only.

No UI rendering yet.

### Stage 3O - Plan Product-List UI For Top-Three Display

Plan how the Hero Manager product list should show the three candidates without overwhelming the row layout.

This should include empty/missing states and challenge affordances.

### Stage 3P - Implement Minimal Top-Three UI

Implement the smallest useful top-three UI.

Manual selection must remain final authority.

### Stage 3Q - Plan Challenge View

Plan the full image-set review experience:

- Open all images.
- Show ranking reasons.
- Allow manual selection.
- Allow rejection.
- Preserve existing override and rejection logic.

### Stage 3R - Plan Criteria Toggles

Plan future controls for selection intent:

- Face handling.
- Product-first vs model-first.
- Object-only products.
- Campaign/lifestyle presentation.
- Full outfit presentation.

Criteria toggles should be designed carefully before implementation.

## Recommended Next Stage

Recommended next stage:

Stage 3K - Audit whether the current candidate endpoint and diagnostics data are sufficient to support a top-three shortlist contract.

Stage 3K should still be audit/planning unless explicitly approved for implementation.

## Stage 3J Verdict

The Hero Manager diagnostics integration should be realigned away from "compact badges as the first product outcome" and toward an AI-led top-three shortlisting workflow.

The future UI should make AI ranking the default review layer while preserving manual editorial authority.

Compact badges may still be useful, but they should serve the shortlist and challenge workflow rather than define the workflow.
