# Live Schema Verification Report (Read-Only)

- Generated: 2026-05-20 06:39:35 UTC
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
| `brand` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `gender` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `itemName` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `itemName_fully_derived` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `model_id` | missing in some live environments; candidate runtime column addition with verify-first gating | no | no | missing runtime candidate |
| `product_domain` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `collection` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `model_family` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `subCategory` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `fabric` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `construction` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `seamless` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `scrunchFlag` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `invisibleFlag` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `neckline` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `strap_configuration` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `support_level` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `rise` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `length` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `variant` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `usage_category` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `usage_subtype` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `categoryName` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `parentCategory` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `ageGroup` | prefer CSV/camelCase; snake_case only compatibility alias pending approved rollout | no | no | missing runtime candidate |
| `sizeType` | prefer CSV/camelCase; snake_case only compatibility alias pending approved rollout | no | no | missing runtime candidate |
| `fitStyle` | prefer CSV/camelCase; snake_case only compatibility alias pending approved rollout | no | no | missing runtime candidate |
| `activityTags` | prefer CSV/camelCase; snake_case only compatibility alias pending approved rollout | no | no | missing runtime candidate |
| `price` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `salePrice` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `description` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `featured` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `images` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `thumbnails_json` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `external_item_id` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `campaign_or_series` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `altText` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `ariaText` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `videoAltText` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `videos` | candidate runtime field from CSV source; missing in live item. | no | no | missing runtime candidate |
| `images2` | staging/import-only by design; do not add to runtime item unless later re-scoped. | no | no | staging/import only |
| `CropAllowed` | manual governance pending; values differ in live data | no | no | verify-first compatibility decision |
| `db_itemId` | keep current live db_itemId linkage field | no | no | verify-first compatibility decision |
| `assignment_source` | existing live column, but design classifies as staging/import-only; manual decision required before import allowlist | no | no | staging/import only |
| `_images_helper_normalize` | staging/import-only by design; do not add to runtime item unless later re-scoped. | no | no | staging/import only |

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
