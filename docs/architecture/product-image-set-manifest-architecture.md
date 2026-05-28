# Product Image Set Manifest Architecture

## Purpose

Sports Warehouse product image workflows need a single, explicit record of which images belong to which products, variants, and gallery roles. The canonical product image set manifest makes product image sets first-class data objects instead of inferred side effects from scattered folders, filenames, CSVs, database fields, review records, copy reports, and storefront outputs.

The manifest is needed because folder names, original filenames, copy reports, platform payloads, and frontend image fields can describe useful evidence, but they are not reliable identity or approval records. They must not become the accidental source of truth for product galleries, import payloads, or published image URLs.

## Core principle

The canonical JSON manifest is the internal source of truth for product image sets. It owns the declared product-to-image relationships, source provenance, technical metadata, review status, and delivery derivation links.

The flat CSV mirror is a review, QA, and tooling mirror of the canonical JSON manifest. It exists for spreadsheet review, diff-friendly checks, and lightweight tooling, but it must be regenerated from the JSON manifest or reconciled back through controlled manifest update logic.

Copy plans, copy simulations, SQL/import payloads, admin views, storefront views, schema.org JSON-LD, and optional IIIF exports are derived outputs. These outputs must be generated from approved manifest rows and must not be treated as source truth.

## Entity model

### Product

A Product represents a Sports Warehouse catalog item that can own a product image set. It includes durable catalog identifiers such as `product_key`, `item_id`, `external_item_id`, `sku`, `gtin`, title, model linkage, variant grouping, review state, and associated images.

### ProductVariant

A ProductVariant represents a sellable or display-specific variation of a Product, such as color, size, fit, or model grouping. Variants can share an ImageSet or require variant-specific images. The manifest uses `variant_group` and related product identifiers to keep variant presentation explicit instead of inferred from filenames.

### ImageSet

An ImageSet is the ordered collection of ImageAsset records assigned to a Product or ProductVariant for a defined presentation purpose. A product gallery ImageSet may include primary, gallery, thumbnail, and zoom roles. The ImageSet is represented by the product-level `images` array plus role, sequence, and variant fields on each image row.

### ImageAsset

An ImageAsset represents one source image candidate or approved product image. It carries a stable `image_id`, relationship fields, source root reference, source relative path, technical metadata such as MIME type, dimensions, byte size, and `checksum_sha256`, and review fields such as `approval_status` and `review_decision_code`.

### SourceRoot

A SourceRoot declares an approved input boundary for candidate image discovery. It identifies the root label, root type, allowlist rule, root scope, exclusions, review status, and reviewer notes. Candidate rows may be created only from explicit allowlisted source roots.

### ReviewDecision

A ReviewDecision records the human or policy decision that changes or confirms the state of a Product, ImageAsset, or SourceRoot. It is represented by `approval_status`, `review_decision_code`, reviewer notes, and preserved decision history. Prior evidence records may inform decisions, but they do not replace the canonical manifest.

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

No downstream output should be generated from unapproved, deferred, rejected, or undiscovered rows.

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
- Keep banner and non_product assets out of product gallery exports by default.
