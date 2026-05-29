# Product Image Set Manifest Architecture

## Purpose

Sports Warehouse product image workflows need a single, explicit record of which images belong to which products, variants, and gallery roles. The canonical product image set manifest makes product image sets first-class data objects instead of inferred side effects from scattered folders, filenames, CSVs, database fields, review records, copy reports, and storefront outputs.

The manifest is needed because folder names, original filenames, copy reports, platform payloads, and frontend image fields can describe useful evidence, but they are not reliable identity or approval records. They must not become the accidental source of truth for product galleries, import payloads, or published image URLs.

## Core principle

The canonical JSON manifest is the internal source of truth for product image sets. It owns the declared product-to-image relationships, source provenance, technical metadata, review status, and delivery derivation links.

The flat CSV mirror is a review, QA, and tooling mirror of the canonical JSON manifest. In schema v1, the flat CSV mirror is one row per ImageAsset. It may include zero or one primary delivery projection for convenience, but it is not a complete delivery graph. If an image has multiple DeliveryAsset records, the CSV mirror either flattens the primary or preferred delivery asset or leaves delivery fields blank. A future `product-image-set-delivery-flat.csv` mirror may be added as one row per DeliveryAsset if delivery-specific review needs require it. The CSV mirror must be regenerated from the JSON manifest or reconciled back through controlled manifest update logic.

Copy plans, copy simulations, SQL/import payloads, admin views, storefront views, schema.org JSON-LD, and optional IIIF exports are derived outputs. These outputs must be generated from approved manifest rows and must not be treated as source truth.

## Entity model

### Product

A Product represents a Sports Warehouse catalog item that can own a product image set. It includes durable catalog identifiers such as `product_key`, `item_id`, `external_item_id`, `sku`, `gtin`, title, model linkage, variant grouping, review state, and associated images.

### ProductVariant

A ProductVariant represents a sellable or display-specific variation of a Product, such as color, size, fit, or model grouping. Variants can share an ImageSet or require variant-specific images. In schema v1, ProductVariant is a logical entity represented through embedded fields rather than a separate top-level collection. The manifest uses `variant_group` and related product identifiers to keep variant presentation explicit instead of inferred from filenames.

### ImageSet

An ImageSet is the ordered collection of ImageAsset records assigned to a Product or ProductVariant for a defined presentation purpose. A product gallery ImageSet may include primary, gallery, thumbnail, and zoom roles. In schema v1, ImageSet is a logical entity represented through embedded fields rather than a separate top-level collection. The ImageSet is represented by the product-level `images` array plus role, sequence, and variant fields on each image row.

### ImageAsset

An ImageAsset represents one source image candidate or approved product image. It carries a stable `image_id`, relationship fields, source root reference, source relative path, technical metadata such as MIME type, dimensions, byte size, and `checksum_sha256`, and review fields such as `approval_status` and `review_decision_code`.

### SourceRoot

A SourceRoot declares an approved input boundary for candidate image discovery. It identifies the root label, root type, allowlist rule, root scope, exclusions, review status, and reviewer notes. Candidate rows may be created only from explicit allowlisted source roots.

### ReviewDecision

A ReviewDecision records the human or policy decision that changes or confirms the state of a Product, ImageAsset, or SourceRoot. In schema v1, ReviewDecision is a logical entity represented through embedded fields rather than a separate top-level collection. It is represented by `approval_status`, `review_decision_code`, reviewer notes, and preserved decision history. Prior evidence records may inform decisions, but they do not replace the canonical manifest.


### Logical entities in schema v1

ProductVariant, ImageSet, and ReviewDecision are logical entities in this architecture. Schema v1 intentionally represents them through embedded fields instead of separate top-level arrays: ProductVariant through `variant_group` and related product identifiers, ImageSet through `products[].images[]` plus image role, sequence, and variant fields, and ReviewDecision through `approval_status`, `review_decision_code`, reviewer notes, gate records, and report records. This embedded representation is complete for schema v1 and should not be treated as a gap merely because those logical entities are not normalized into top-level collections. Future schema versions may normalize ProductVariant, ImageSet, or ReviewDecision into separate top-level collections if operational scale, review history, or variant-specific publishing rules require it.

### DeliveryAsset

A DeliveryAsset represents a generated or published output derived from an ImageAsset. It can include destination relative path, URL, dimensions, MIME type, source derivation, and generator name. Delivery URLs are derived locations, not image identity.

## Workflow sequence

Safe operation must happen in this order:

1. Declare source roots and allowlists.
2. Create candidate manifest rows from explicit roots only.
3. Enrich candidate rows with technical metadata and stable IDs.
4. Merge prior review decisions from controlled evidence inputs.
5. Run human review so reviewers approve, modify, reject, or defer rows.
6. Commit the canonical manifest.
7. Generate copy plan and simulation from approved rows only.
8. Copy approved assets.
9. Generate platform import payloads and storefront views.
10. Publish derived URLs and markup.

No downstream output should be generated from unapproved, deferred, rejected, or undiscovered rows. Product gallery exporters must include only approved image rows with product-safe roles such as `primary`, `gallery`, `thumbnail`, `zoom`, `hero`, or `swatch`, and must exclude `banner` and `non_product` rows by default.

## Review and gating model

`approval_status` values define the current state of a manifest object:

- `proposed`: Candidate row exists and awaits review.
- `approved`: Row is approved for the next gated step.
- `deferred`: Row requires later research or policy review.
- `rejected`: Row is not approved for product image use.
- `superseded`: Row has been replaced by a newer manifest object or decision.

`review_decision_code` values record the review action or reason:

- `accept_proposed`: Reviewer accepted the proposed row as-is.
- `accept_modified`: Reviewer accepted the row after controlled edits.
- `defer_decision`: Reviewer deferred a decision.
- `reject_non_product`: Reviewer rejected a non-product image.
- `reject_banner`: Reviewer rejected a banner or marketing asset for product gallery use.
- `not_reviewed`: Row has not yet been reviewed.

Product-level approval_status describes the overall product image set state. Image-level approval_status describes an individual image row. A product image set may remain `proposed` with `not_reviewed` while some individual image rows are `approved`, especially when the set still includes deferred, rejected, banner, non_product, or unresolved rows.

Banner and non-product assets must not enter product gallery exports unless explicitly routed through a separate workflow designed for marketing, editorial, or site banner assets. Deferred rows must not be copied, imported, or published.

## Relationship to current Ryderwear Batch 2 workflow

This architecture extends the current Ryderwear review-gate process by giving future image decisions a durable canonical data model. Current review evidence can help seed or validate decisions, but the canonical manifest becomes the committed state for product image relationships after a controlled generation task.

The current `human_reviewer_acceptance_record.json` and `review_decision_gate_report.md` are evidence inputs, not replacements for the canonical manifest. They can be referenced when merging prior decisions, but they should not be treated as live product gallery source truth.

A future controlled task may generate a Ryderwear candidate manifest only after source-root and policy approval. That task must declare allowed source roots, avoid broad folder scans, preserve review evidence, and keep generated candidates gated until human approval.

## Downstream outputs

After approval, the canonical manifest can later generate:

- `source_asset_inventory.csv` for declared source root inventory and metadata review.
- `copy_simulation.csv` or equivalent copy simulation output before any file movement.
- Image copy plans for approved source assets only.
- Product image import payloads for database or platform loading.
- Admin review views that display product, image, source, and decision context.
- Frontend product galleries driven by approved image roles and sequences.
- Optional IIIF exports for interoperable image delivery and metadata.
- Optional schema.org/ImageObject JSON-LD for structured product image markup.

These outputs remain derived artifacts. If a derived output conflicts with the canonical JSON manifest, the manifest is authoritative unless a controlled manifest correction is committed.

## Safety rules

- Do not perform broad folder scans.
- Use explicit allowlisted source roots only.
- Use SHA-256, recorded as `checksum_sha256`, for asset identity and fixity.
- Keep `image_id` separate from product identity, SKU identity, delivery filename, and URL identity.
- Validate MIME type, dimensions, byte size, and checksum before approval or copy.
- Preserve review decision history and reviewer notes.
- Do not let delivery URLs become image identity.
- Keep generated IDs, enum values, and CSV headers ASCII-safe.
- Product gallery exporters must include only approved image rows with product-safe roles such as `primary`, `gallery`, `thumbnail`, `zoom`, `hero`, or `swatch`.
- Product gallery exporters must exclude banner and non_product rows by default.
- Deferred rows must not be copied, imported, or published.
- Rejected banner and non_product rows may remain in the manifest as evidence or history but must not be treated as gallery assets.
- Product-level approval_status cannot be treated as sufficient for publication if individual image rows are deferred or rejected, unless a future policy explicitly permits partial publication.
