-- Local-only controlled inactive insert script for all remaining pending products
-- Target DB: sportswh (MySQL)
-- Scope: Insert remaining unreconciled pending rows from product_import_staging into item as inactive seed records.
-- IMPORTANT: Review preflight output before running INSERT.

USE sportswh;

-- =========================================================
-- 1) READ-ONLY PREFLIGHT CHECKS
-- =========================================================

-- 1a) Confirm pending source count by brand for rows eligible for insertion.
SELECT
    pis.brand,
    COUNT(*) AS pending_count
FROM product_import_staging pis
LEFT JOIN item i
    ON i.external_item_id = pis.model_id
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND i.itemId IS NULL
GROUP BY pis.brand
ORDER BY pis.brand;
-- Expected: Adidas=4, Ryderwear=58

-- 1b) Confirm total selected row count.
SELECT
    COUNT(*) AS total_pending_count
FROM product_import_staging pis
LEFT JOIN item i
    ON i.external_item_id = pis.model_id
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND i.itemId IS NULL;
-- Expected: 62

-- 1c) Confirm all selected rows have model_id.
SELECT
    pis.brand,
    pis.itemName,
    pis.model_id
FROM product_import_staging pis
LEFT JOIN item i
    ON i.external_item_id = pis.model_id
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND i.itemId IS NULL
  AND (pis.model_id IS NULL OR TRIM(pis.model_id) = '');
-- Expectation: 0 rows

-- 1d) Confirm no duplicate model_id values in selected batch.
SELECT
    selected.model_id,
    COUNT(*) AS duplicate_count
FROM (
    SELECT TRIM(pis.model_id) AS model_id
    FROM product_import_staging pis
    LEFT JOIN item i
        ON i.external_item_id = pis.model_id
    WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
      AND (pis.images IS NULL OR TRIM(pis.images) = '')
      AND i.itemId IS NULL
      AND pis.model_id IS NOT NULL
      AND TRIM(pis.model_id) <> ''
) selected
GROUP BY selected.model_id
HAVING COUNT(*) > 1;
-- Expectation: 0 rows

-- 1e) Confirm no selected model_id collides with item.external_item_id.
SELECT
    pis.brand,
    pis.itemName,
    pis.model_id,
    i.itemId AS colliding_itemId
FROM product_import_staging pis
JOIN item i
    ON i.external_item_id = pis.model_id
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '');
-- Expectation: 0 rows

-- 1f) Confirm no selected brand + itemName collides with existing item rows.
SELECT
    pis.brand,
    pis.itemName,
    i.itemId AS colliding_itemId
FROM product_import_staging pis
JOIN item i
    ON i.brand = pis.brand
   AND i.itemName = pis.itemName
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND NOT EXISTS (
      SELECT 1
      FROM item i2
      WHERE i2.external_item_id = pis.model_id
  );
-- Expectation: 0 rows

-- 1g) Confirm item.external_item_id type is VARCHAR(255).
SELECT
    c.TABLE_NAME,
    c.COLUMN_NAME,
    c.COLUMN_TYPE,
    c.IS_NULLABLE
FROM information_schema.COLUMNS c
WHERE c.TABLE_SCHEMA = DATABASE()
  AND c.TABLE_NAME = 'item'
  AND c.COLUMN_NAME = 'external_item_id';

-- 1h) Confirm current MAX(db_itemId).
SELECT MAX(db_itemId) AS current_max_db_itemId FROM item;
-- Expected from reconciliation notes: 58

-- 1i) Confirm target db_itemId range (59-120) is unused.
SELECT
    i.db_itemId,
    i.itemId,
    i.itemName,
    i.external_item_id
FROM item i
WHERE i.db_itemId BETWEEN 59 AND 120
ORDER BY i.db_itemId;
-- Expectation: 0 rows

-- 1j) Confirm categoryName values and CASE mapping coverage.
SELECT
    pis.categoryName,
    COUNT(*) AS category_count,
    CASE
        WHEN pis.categoryName = 'Tops' THEN 1
        WHEN pis.categoryName = 'Pants' THEN 2
        WHEN pis.categoryName = 'Skirts and Dresses' THEN 3
        WHEN pis.categoryName = 'Set' THEN 4
        WHEN pis.categoryName = 'Shoes' THEN 5
        WHEN pis.categoryName = 'Training Gear' THEN 6
        WHEN pis.categoryName = 'Water Sports' THEN 7
        WHEN pis.categoryName = 'Equipment' THEN 8
        ELSE 0
    END AS mapped_categoryId
FROM product_import_staging pis
LEFT JOIN item i
    ON i.external_item_id = pis.model_id
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND i.itemId IS NULL
GROUP BY pis.categoryName
ORDER BY pis.categoryName;

-- =========================================================
-- 2) BACKUP TABLE (GUARDED)
-- =========================================================

SET @remaining_pending_backup_exists := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'item_backup_before_remaining_pending_inactive_insert'
);

SELECT
    CASE
        WHEN @remaining_pending_backup_exists = 0 THEN 'OK_TO_RUN'
        ELSE 'STOP_BACKUP_TABLE_ALREADY_EXISTS'
    END AS remaining_pending_guard_status;

CREATE TABLE item_backup_before_remaining_pending_inactive_insert AS
SELECT *
FROM item
WHERE @remaining_pending_backup_exists = 0;

-- =========================================================
-- 3) CONTROLLED INSERT (62 ROWS, INACTIVE)
-- =========================================================

-- Stable deterministic ordering for db_itemId assignment:
-- ORDER BY brand ASC, itemName ASC, model_id ASC
-- db_itemId = 58 + ROW_NUMBER() in that ordering => 59 through 120.

INSERT INTO item (
    itemName,
    categoryId,
    categoryName,
    parentCategory,
    subcategory,
    price,
    salePrice,
    description,
    images,
    altText,
    ariaText,
    brand,
    featured,
    assignment_source,
    external_item_id,
    db_itemId,
    is_active
)
SELECT
    src.itemName,
    CASE
        WHEN src.categoryName = 'Tops' THEN 1
        WHEN src.categoryName = 'Pants' THEN 2
        WHEN src.categoryName = 'Skirts and Dresses' THEN 3
        WHEN src.categoryName = 'Set' THEN 4
        WHEN src.categoryName = 'Shoes' THEN 5
        WHEN src.categoryName = 'Training Gear' THEN 6
        WHEN src.categoryName = 'Water Sports' THEN 7
        WHEN src.categoryName = 'Equipment' THEN 8
        ELSE 0
    END AS categoryId,
    src.categoryName,
    src.subCategoryParent AS parentCategory,
    src.subCategory AS subcategory,
    NULLIF(TRIM(src.price), '') AS price,
    NULLIF(TRIM(src.salePrice), '') AS salePrice,
    NULLIF(TRIM(src.description), '') AS description,
    NULLIF(TRIM(src.images), '') AS images,
    NULLIF(TRIM(src.altText), '') AS altText,
    NULLIF(TRIM(src.ariaText), '') AS ariaText,
    src.brand,
    0 AS featured,
    'custom' AS assignment_source,
    src.model_id AS external_item_id,
    58 + src.row_num AS db_itemId,
    0 AS is_active
FROM (
    SELECT
        pis.itemName,
        pis.categoryName,
        pis.subCategoryParent,
        pis.subCategory,
        pis.price,
        pis.salePrice,
        pis.description,
        pis.images,
        pis.altText,
        pis.ariaText,
        pis.brand,
        TRIM(pis.model_id) AS model_id,
        ROW_NUMBER() OVER (
            ORDER BY pis.brand ASC, pis.itemName ASC, TRIM(pis.model_id) ASC
        ) AS row_num
    FROM product_import_staging pis
    LEFT JOIN item i
        ON i.external_item_id = pis.model_id
    WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
      AND (pis.images IS NULL OR TRIM(pis.images) = '')
      AND i.itemId IS NULL
      AND pis.model_id IS NOT NULL
      AND TRIM(pis.model_id) <> ''
) src
WHERE @remaining_pending_backup_exists = 0
  AND NOT EXISTS (
      SELECT 1
      FROM item existing_ext
      WHERE existing_ext.external_item_id = src.model_id
  )
ORDER BY src.row_num;

-- =========================================================
-- 4) POST-INSERT VERIFICATION
-- =========================================================

-- 4a) Show inserted rows.
SELECT
    i.itemId,
    i.db_itemId,
    i.brand,
    i.itemName,
    i.external_item_id,
    i.categoryId,
    i.categoryName,
    i.parentCategory,
    i.subcategory,
    i.is_active,
    i.featured
FROM item i
WHERE i.db_itemId BETWEEN 59 AND 120
ORDER BY i.db_itemId;

-- 4b) Confirm 62 rows inserted in target range.
SELECT COUNT(*) AS inserted_row_count
FROM item i
WHERE i.db_itemId BETWEEN 59 AND 120;
-- Expected: 62

-- 4c) Confirm item count increased by 62 (compare before/after manually from these two queries).
SELECT COUNT(*) AS item_count_after_insert FROM item;
SELECT COUNT(*) AS item_count_before_insert_snapshot FROM item_backup_before_remaining_pending_inactive_insert;
-- Expected: item_count_after_insert - item_count_before_insert_snapshot = 62

-- 4d) Confirm db_itemId range and uniqueness for inserted rows.
SELECT
    MIN(i.db_itemId) AS min_db_itemId,
    MAX(i.db_itemId) AS max_db_itemId,
    COUNT(*) AS row_count,
    COUNT(DISTINCT i.db_itemId) AS distinct_db_itemId_count
FROM item i
WHERE i.db_itemId BETWEEN 59 AND 120;
-- Expected: min=59, max=120, row_count=62, distinct_db_itemId_count=62

-- 4e) Confirm all inserted rows are inactive and not featured.
SELECT
    SUM(CASE WHEN i.is_active = 0 THEN 1 ELSE 0 END) AS inactive_rows,
    SUM(CASE WHEN i.featured = 0 THEN 1 ELSE 0 END) AS non_featured_rows,
    COUNT(*) AS total_rows
FROM item i
WHERE i.db_itemId BETWEEN 59 AND 120;
-- Expected: inactive_rows=62, non_featured_rows=62, total_rows=62

-- 4f) Confirm no duplicate external_item_id in entire item table.
SELECT
    i.external_item_id,
    COUNT(*) AS duplicate_count
FROM item i
WHERE i.external_item_id IS NOT NULL
  AND TRIM(i.external_item_id) <> ''
GROUP BY i.external_item_id
HAVING COUNT(*) > 1;
-- Expected: 0 rows

-- 4g) Confirm no duplicate db_itemId in entire item table.
SELECT
    i.db_itemId,
    COUNT(*) AS duplicate_count
FROM item i
WHERE i.db_itemId IS NOT NULL
GROUP BY i.db_itemId
HAVING COUNT(*) > 1;
-- Expected: 0 rows

-- =========================================================
-- 5) STAGING RECONCILIATION UPDATE (CONTROLLED)
-- =========================================================

UPDATE product_import_staging pis
JOIN item i
    ON i.external_item_id = pis.model_id
SET pis.db_itemId = i.db_itemId
WHERE i.db_itemId BETWEEN 59 AND 120
  AND i.assignment_source = 'custom'
  AND i.is_active = 0
  AND i.featured = 0
  AND pis.brand IN ('Adidas', 'Ryderwear')
  AND (pis.images IS NULL OR TRIM(pis.images) = '');

-- =========================================================
-- 6) POST-STAGING VERIFICATION
-- =========================================================

-- 6a) Confirm remaining unreconciled pending rows with no matching item are now zero.
SELECT
    pis.brand,
    COUNT(*) AS remaining_unreconciled_count
FROM product_import_staging pis
LEFT JOIN item i
    ON i.external_item_id = pis.model_id
WHERE (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '' OR CAST(TRIM(pis.db_itemId) AS UNSIGNED) = 0)
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND i.itemId IS NULL
GROUP BY pis.brand;
-- Expected: 0 rows (or no Adidas/Ryderwear rows)

-- 6b) Confirm staging db_itemId now matches item.db_itemId for inserted Adidas/Ryderwear rows.
SELECT
    pis.brand,
    pis.itemName,
    pis.model_id,
    pis.db_itemId AS staging_db_itemId,
    i.db_itemId AS item_db_itemId
FROM product_import_staging pis
JOIN item i
    ON i.external_item_id = pis.model_id
WHERE i.db_itemId BETWEEN 59 AND 120
  AND pis.brand IN ('Adidas', 'Ryderwear')
ORDER BY i.db_itemId;

-- 6c) Confirm images remain blank for the reconciled inserted set.
SELECT
    pis.brand,
    COUNT(*) AS non_blank_images_count
FROM product_import_staging pis
JOIN item i
    ON i.external_item_id = pis.model_id
WHERE i.db_itemId BETWEEN 59 AND 120
  AND (pis.images IS NOT NULL AND TRIM(pis.images) <> '')
GROUP BY pis.brand;
-- Expected: 0 rows

-- =========================================================
-- 7) ROLLBACK SQL (MANUAL ONLY - DO NOT AUTO-EXECUTE)
-- =========================================================

-- -- ROLLBACK STEP 1: Remove inserted rows for this script run.
-- DELETE FROM item
-- WHERE db_itemId BETWEEN 59 AND 120
--   AND assignment_source = 'custom'
--   AND is_active = 0
--   AND featured = 0;

-- -- ROLLBACK STEP 2: Restore item table from backup snapshot (alternative full restore).
-- -- TRUNCATE TABLE item;
-- -- INSERT INTO item SELECT * FROM item_backup_before_remaining_pending_inactive_insert;

-- -- ROLLBACK STEP 3: Only run if rolling back this inserted inactive batch (db_itemId 59-120).
-- UPDATE product_import_staging
-- SET db_itemId = NULL
-- WHERE db_itemId BETWEEN 59 AND 120
--   AND brand IN ('Adidas', 'Ryderwear')
--   AND (images IS NULL OR TRIM(images) = '');
