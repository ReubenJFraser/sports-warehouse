# Product Image Set Manifest Schema Foundation

This document defines the initial schema foundation for the Sports Warehouse canonical product image set manifest. It is documentation and template guidance only. It does not generate a live Ryderwear manifest.

## Manifest-level fields

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `manifest_version` | string | Yes | Schema version for the canonical JSON manifest, such as `1.0.0-draft`. |
| `manifest_id` | string | Yes | Stable ID for this manifest document. |
| `generated_at` | string | Yes | ISO 8601 timestamp for when this manifest draft or committed version was generated. |
| `batch_id` | string | Yes | Batch or workflow identifier, such as a controlled review batch name. |
| `source_roots` | array of SourceRoot | Yes | Declared and reviewed input roots. |
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

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `product_key` | string | Yes | Stable manifest key for the product. |
| `item_id` | string | No | Sports Warehouse internal item ID when available. |
| `external_item_id` | string | No | External catalog or supplier item ID when available. |
| `sku` | string | No | Product or variant SKU. |
| `gtin` | string | No | GTIN, UPC, or EAN when available. |
| `title` | string | Yes | Product title. |
| `model_id` | string | No | Model grouping identifier used by Sports Warehouse workflows. |
| `variant_group` | string | No | Variant group such as color or style. |
| `approval_status` | approval_status enum | Yes | Review state for the product image set. |
| `review_decision_code` | review_decision_code enum | Yes | Review decision for the product row or image set. |
| `images` | array of ImageAsset | Yes | Image assets associated with this product. |

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
| `products_shown` | array of strings | Yes | Product keys or item IDs visibly shown in the image. |
| `approval_status` | approval_status enum | Yes | Review state for the image row. |
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
- No approved product image set should have duplicate `sequence` values for the same `product_key` unless explicitly allowed by a future exception field.
- `banner` and `non_product` roles must be excluded from product gallery exports by default.
- Deferred rows must not be copied, imported, or published.
- Generated IDs, enum values, and CSV headers must be ASCII-safe.
- Delivery URLs must not be used as identity keys.
- The flat CSV mirror must preserve `image_id`, `checksum_sha256`, `approval_status`, and `review_decision_code` so review changes can be reconciled safely.
