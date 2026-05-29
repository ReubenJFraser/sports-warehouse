# Product Image Set Manifest Schema Foundation

This document defines the initial schema foundation for the Sports Warehouse canonical product image set manifest. It is documentation and template guidance only. It does not generate a live Ryderwear manifest.

Schema v1 uses embedded fields for several logical entities named in the architecture. ProductVariant is represented mainly through `variant_group` and normalized product identifiers. ImageSet is represented through `products[].images[]` plus image role, sequence, and variant fields. ReviewDecision is represented through `approval_status`, `review_decision_code`, reviewer notes, and related gate or report records. These are complete schema v1 representations, not missing top-level arrays. Future schema versions may normalize ProductVariant, ImageSet, or ReviewDecision into separate top-level collections if needed.

## Manifest-level fields

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `manifest_version` | string | Yes | Schema version for the canonical JSON manifest, such as `1.0.0-draft`. |
| `manifest_id` | string | Yes | Stable ID for this manifest document. |
| `generated_at` | string | Yes | ISO 8601 timestamp for when this manifest draft or committed version was generated. |
| `batch_id` | string | Yes | Batch or workflow identifier, such as a controlled review batch name. |
| `source_roots` | array of SourceRoot | Yes | Declared and reviewed input roots. Product-gallery roots may exclude banner or marketing paths while separate marketing roots can preserve rejected or non_product evidence rows. |
| `products` | array of Product | Yes | Product records and their associated image assets. |

## SourceRoot fields

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `source_root_id` | string | Yes | Stable ID for a declared source root. |
| `root_label` | string | Yes | Human-readable source root label. |
| `root_type` | source_root_type enum | Yes | Type of source root. |
| `allowlist_rule_id` | string | Yes | ID of the policy or approval rule that allowed the root. |
| `root_scope` | string | Yes | Approved path, archive scope, DAM collection, or other source boundary. |
| `exclusions` | array of strings | Yes | Explicit excluded relative paths, path patterns, or policy notes. |
| `approval_status` | approval_status enum | Yes | Review state for this source root. |
| `reviewer_notes` | string | No | Reviewer notes for the root approval decision. |

## Product fields

Sports Warehouse project-specific candidate manifest Product fields are:

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `item_id` | integer or null | No | Manifest-normalized name for the Excel/ProductDB `db_itemId` database item identity. Populate from `db_itemId` when a unique reliable ProductDB/CSV match exists. This may be null only when no reliable ProductDB identity can be established. |
| `model_id` | string | Yes | Canonical Sports Warehouse product/model slug. Required for Sports Warehouse product image set manifests. |
| `sku` | string or null | No | Product or variant SKU when available. |
| `gtin` | string or null | No | GTIN, UPC, or EAN when available. |
| `title` | string | Yes | Product title. |
| `variant_group` | string or null | No | Variant group such as color or style. |
| `approval_status` | approval_status enum | Yes | Product-level approval_status for the overall product image set state. This can remain `proposed` while individual image rows are approved if other rows are deferred, rejected, banner, non_product, or unresolved. |
| `review_decision_code` | review_decision_code enum | Yes | Review decision for the product row or image set. |
| `reviewer_notes` | string | No | Human reviewer notes for the product row or image set. |
| `images` | array of ImageAsset | Yes | Image assets associated with this product. |

`product_key` is deprecated and omitted for Sports Warehouse candidate manifests when it would duplicate `model_id`. `external_item_id` is deprecated and omitted because it duplicated `model_id` in this project. If a future external, supplier, or platform identifier is needed, introduce a new precisely named field such as `supplier_item_id`, `supplier_sku`, `platform_item_id`, or `source_system_item_id` rather than restoring `external_item_id`.

## ImageAsset fields

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `image_id` | string | Yes | Stable image asset ID, separate from product identity and delivery URL. |
| `sequence` | integer | Yes | Product image ordering value within role or gallery context. |
| `role` | role enum | Yes | Image role such as primary, gallery, thumbnail, zoom, banner, or non_product. |
| `variant_group` | string | No | Variant group this image applies to. |
| `source_root_id` | string | Yes | Reference to a declared SourceRoot. |
| `source_relpath` | string | Yes | Path relative to the declared source root. |
| `original_filename` | string | Yes | Original filename as found under the source root. |
| `mime_type` | string | Yes | Validated MIME type, such as `image/jpeg` or `image/png`. |
| `width_px` | integer | Yes | Validated width in pixels. |
| `height_px` | integer | Yes | Validated height in pixels. |
| `byte_size` | integer | Yes | Validated file size in bytes. |
| `checksum_sha256` | string | Yes | SHA-256 checksum as 64 lowercase hex characters. |
| `digital_source_type` | digital_source_type enum | Yes | Source or derivation category for the image. |
| `derived_from_image_id` | string or null | No | Parent image ID when this image is derived from another ImageAsset. |
| `products_shown` | array of strings | Yes | Product `model_id` values or `item_id` values visibly shown in the image. |
| `approval_status` | approval_status enum | Yes | Image-level approval_status for this individual image row. |
| `review_decision_code` | review_decision_code enum | Yes | Review decision for the image row. |
| `reviewer_notes` | string | No | Human reviewer notes. |
| `delivery` | array of DeliveryAsset | Yes | Derived delivery assets generated from this image. |

## DeliveryAsset fields

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `delivery_id` | string | Yes | Stable ID for this delivery asset. |
| `role` | role enum | Yes | Delivery role such as thumbnail, gallery, zoom, or hero. |
| `destination_relpath` | string | Yes | Relative destination path for copied or generated output. |
| `url` | string | No | Published or planned URL. This is not image identity. |
| `width_px` | integer | Yes | Delivery width in pixels. |
| `height_px` | integer | Yes | Delivery height in pixels. |
| `mime_type` | string | Yes | Delivery MIME type. |
| `derived_from_image_id` | string | Yes | Source ImageAsset ID used to generate the delivery asset. |
| `generated_by` | string | Yes | Tool, workflow, or job ID that generated this delivery asset. |


## Product-level and image-level approval

Product-level approval_status describes the overall image set state for the product. Image-level approval_status describes the review state of a single ImageAsset row. A product or ImageSet may remain `proposed` with `review_decision_code` set to `not_reviewed` while some individual image rows are `approved`, especially when the set still contains deferred, rejected, banner, non_product, or unresolved rows.

Product-level approval_status is not sufficient by itself for product gallery publication. Exporters must evaluate each ImageAsset row and include only approved rows with product-safe roles unless a future policy explicitly permits partial publication.

## CSV mirror flattening rule

The v1 flat CSV mirror, including `product-image-set-manifest-flat.example.csv`, is one row per ImageAsset. It may include zero or one primary delivery projection for convenience. If an ImageAsset has multiple DeliveryAsset entries in JSON, the CSV mirror either flattens the primary or preferred delivery asset or leaves delivery fields blank. The CSV mirror must not be mistaken for a complete delivery graph.

A future `product-image-set-delivery-flat.csv` mirror may be introduced as one row per DeliveryAsset if delivery asset review, diffing, or export validation requires it. Do not create that separate delivery CSV for schema v1 unless a future architecture change requires it.

## Controlled enums

### approval_status

- `proposed`
- `approved`
- `deferred`
- `rejected`
- `superseded`

### review_decision_code

- `accept_proposed`
- `accept_modified`
- `defer_decision`
- `reject_non_product`
- `reject_banner`
- `not_reviewed`

### role

- `primary`
- `gallery`
- `thumbnail`
- `zoom`
- `hero`
- `swatch`
- `banner`
- `non_product`

### source_root_type

- `local_filesystem`
- `repository_path`
- `exported_archive`
- `external_dam`
- `unknown`

### digital_source_type

- `native_digital_capture`
- `derived_internal_crop`
- `derived_internal_export`
- `supplier_asset`
- `marketing_banner`
- `unknown`

## Validation rules

- `image_id` must be unique across the manifest.
- `source_root_id` on each ImageAsset must reference a declared source root.
- `checksum_sha256` must match 64 lowercase hex characters when present.
- Relative paths must not escape the declared source root. Paths containing `..`, absolute path prefixes, drive letters, or URL schemes are invalid for `source_relpath`.
- No approved product image set should have duplicate `sequence` values for the same `model_id` and `variant_group` unless explicitly allowed by a future exception field.
- Product gallery exporters must include only approved image rows with product-safe roles such as `primary`, `gallery`, `thumbnail`, `zoom`, `hero`, or `swatch`.
- Product gallery exporters must exclude `banner` and `non_product` rows by default.
- Deferred rows must not be copied, imported, or published.
- Rejected `banner` and `non_product` rows may remain in the manifest as evidence or history but must not be treated as gallery assets.
- Product-level approval_status cannot be treated as sufficient if individual image rows are deferred or rejected, unless a future policy explicitly permits partial publication.
- Generated IDs, enum values, and CSV headers must be ASCII-safe.
- Delivery URLs must not be used as identity keys.
- The flat CSV mirror must preserve `image_id`, `checksum_sha256`, `approval_status`, and `review_decision_code` so review changes can be reconciled safely.
