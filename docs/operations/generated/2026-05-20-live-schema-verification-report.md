# Live Schema Verification Report (Read-Only)

- Generated: 2026-05-20 06:06:25 UTC
- Source CSV: `docs/data/SportWarehouse_ProductDB.csv`
- Migration design reference: `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`
- DB status: connection failed (`SQLSTATE[HY000] [2002] Connection refused`)

## 1) Purpose and constraints
- This is a **read-only verification report**.
- Allowed inspection methods: `SELECT`, `SHOW`, `DESCRIBE`, `information_schema` reads only.
- No DB writes.
- No migrations.
- No `ALTER TABLE`.
- No repair SQL.
- No importer execution.
- No image edits.
- No Hero Manager / Hero Editor behavior changes.

## 2) Live table presence
- `item` exists: **no**
- `hero_override` exists: **no**

## 3) Live item column inventory
- `item` table not found, so item column inventory is unavailable.

## 4) Required field verification
- `item.db_itemId` exists: **no**
- `item.db_item_id` exists: **no**
- `item.model_id` exists: **no**
- `item.itemName_fully_derived` exists: **no**
- `item.item_name_fully_derived` exists: **no**
- `item.CropAllowed` exists: **no**
- `item.crop_allowed` exists: **no**
- `item.hero_image` exists: **no**
- `item.chosen_image` exists: **no**
- `item.thumbnails_json` exists: **no**
- `item.images` exists: **no**
- `item.videos` exists: **no**

## 5) Naming-drift / duplicate-column risk
| Field pair | Classification | Notes |
|---|---|---|
| `db_itemId` / `db_item_id` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `CropAllowed` / `crop_allowed` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `itemName_fully_derived` / `item_name_fully_derived` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `subCategory` / `subcategory` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `ageGroup` / `age_group` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `sizeType` / `size_type` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `fitStyle` / `fit_style` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |
| `activityTags` / `activity_tags` | alias/mapping required | Neither form exists; importer mapping needed if required by CSV. |

## 5A) Naming convention / duplicate-column governance
- This section treats camelCase/snake_case duplicates as a **naming-governance architecture decision**, not simple schema drift.
| Pair | camelCase in live `item` | snake_case in live `item` | CSV header form | Data occupancy/read-only comparison | Recommendation |
|---|---|---|---|---|---|
| `ageGroup` / `age_group` | no | no | camelCase (`ageGroup`) | DB unavailable or both columns not present for live read-only comparison. | manual decision required |
| `sizeType` / `size_type` | no | no | camelCase (`sizeType`) | DB unavailable or both columns not present for live read-only comparison. | manual decision required |
| `fitStyle` / `fit_style` | no | no | camelCase (`fitStyle`) | DB unavailable or both columns not present for live read-only comparison. | manual decision required |
| `activityTags` / `activity_tags` | no | no | camelCase (`activityTags`) | DB unavailable or both columns not present for live read-only comparison. | manual decision required |
| `CropAllowed` / `crop_allowed` | no | no | camelCase (`CropAllowed`) | DB unavailable or both columns not present for live read-only comparison. | manual decision required |

## 6) CSV-to-live schema comparison
| CSV column | Expected runtime decision | Exact live item column exists | Alias/mapped live item column exists | Recommended planning status |
|---|---|---|---|---|
| `brand` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `gender` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `itemName` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `itemName_fully_derived` | add new column or map alias | no | no | missing runtime candidate |
| `model_id` | add new column or verify existing first | no | no | missing runtime candidate |
| `product_domain` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `collection` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `model_family` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `subCategory` | map alias to subcategory | no | no | missing runtime candidate |
| `fabric` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `construction` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `seamless` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `scrunchFlag` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `invisibleFlag` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `neckline` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `strap_configuration` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `support_level` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `rise` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `length` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `variant` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `usage_category` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `usage_subtype` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `categoryName` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `parentCategory` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `ageGroup` | map alias to age_group | no | no | missing runtime candidate |
| `sizeType` | map alias to size_type | no | no | missing runtime candidate |
| `fitStyle` | map alias to fit_style | no | no | missing runtime candidate |
| `activityTags` | map alias to activity_tags | no | no | missing runtime candidate |
| `price` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `salePrice` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `description` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `featured` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `images` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `thumbnails_json` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `external_item_id` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `campaign_or_series` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `altText` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `ariaText` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `videoAltText` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `videos` | not explicitly specified in migration design | no | no | missing runtime candidate |
| `images2` | staging/import only | no | no | staging/import only |
| `CropAllowed` | verify-first naming decision | no | no | verify-first compatibility decision |
| `db_itemId` | verify live schema first; keep existing | no | no | verify-first compatibility decision |
| `assignment_source` | existing live column, but design classifies as staging/import-only; manual decision required before import allowlist | no | no | staging/import only |
| `_images_helper_normalize` | staging/import only | no | no | staging/import only |

## 7) Protected-field verification
- `item.hero_image` exists: **no**
- `item.chosen_image` exists: **no**
- `hero_override` table exists: **no**
- `hero_override.chosen_image` exists: **no**
- These fields must **not** be included in any future CSV overwrite allowlist.

## 8) Summary / next-step recommendation
- Live item column count: **0**
- CSV header count: **45**
- CSV columns already supported exactly: **0**
- CSV columns supported via alias: **0**
- CSV columns missing from live item and candidate runtime additions: **40**
- CSV columns classified as staging/import only: **3**
- Duplicate naming pairs detected: **5**
- Duplicate naming pairs with both columns present: **0**
- Duplicate naming pairs with value differences: **0**
- Verify-first compatibility decisions: **2**
- Manual naming-governance decisions required: **0**
- Recommendation: DB was unreachable, so manual live schema review is still required before drafting illustrative migration SQL.
