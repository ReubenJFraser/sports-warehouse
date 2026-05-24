-- Local-only controlled inactive pilot insert script
-- Target DB: sportswh (MySQL)
-- Scope: Insert only 4 Ryderwear Contour pending rows from product_import_staging into item as inactive records
-- IMPORTANT: Review preflight checks before running INSERT.

USE sportswh;

-- =========================================================
-- 1) READ-ONLY PREFLIGHT CHECKS
-- =========================================================

-- 1a) Confirm the 4 target staging rows exist
SELECT
    pis.brand,
    pis.itemName,
    pis.categoryName,
    pis.subCategory,
    pis.subCategoryParent,
    pis.model_id,
    pis.db_itemId,
    pis.images
FROM product_import_staging pis
WHERE pis.brand = 'Ryderwear'
  AND pis.itemName IN (
      'Contour Halter Sports Bra',
      'Contour Seamless High-Waisted Leggings',
      'Contour Seamless High-Waisted Shorts',
      'Contour Track Pants'
  )
ORDER BY FIELD(
    pis.itemName,
    'Contour Halter Sports Bra',
    'Contour Seamless High-Waisted Leggings',
    'Contour Seamless High-Waisted Shorts',
    'Contour Track Pants'
);

-- 1b) Confirm all 4 have model_id
SELECT
    pis.itemName,
    pis.model_id,
    CASE
        WHEN pis.model_id IS NULL OR TRIM(pis.model_id) = '' THEN 'MISSING_MODEL_ID'
        ELSE 'OK'
    END AS model_id_status
FROM product_import_staging pis
WHERE pis.brand = 'Ryderwear'
  AND pis.itemName IN (
      'Contour Halter Sports Bra',
      'Contour Seamless High-Waisted Leggings',
      'Contour Seamless High-Waisted Shorts',
      'Contour Track Pants'
  )
ORDER BY FIELD(
    pis.itemName,
    'Contour Halter Sports Bra',
    'Contour Seamless High-Waisted Leggings',
    'Contour Seamless High-Waisted Shorts',
    'Contour Track Pants'
);

-- 1c) Confirm no model_id collides with item.external_item_id
SELECT
    pis.itemName,
    pis.model_id,
    i.itemId AS colliding_itemId,
    i.external_item_id AS colliding_external_item_id
FROM product_import_staging pis
JOIN item i
    ON i.external_item_id = pis.model_id
WHERE pis.brand = 'Ryderwear'
  AND pis.itemName IN (
      'Contour Halter Sports Bra',
      'Contour Seamless High-Waisted Leggings',
      'Contour Seamless High-Waisted Shorts',
      'Contour Track Pants'
  );
-- Expectation: 0 rows

-- 1d) Confirm no brand + itemName collision with existing item rows
SELECT
    pis.brand,
    pis.itemName,
    i.itemId AS colliding_itemId,
    i.brand AS existing_brand,
    i.itemName AS existing_itemName
FROM product_import_staging pis
JOIN item i
    ON i.brand = pis.brand
   AND i.itemName = pis.itemName
WHERE pis.brand = 'Ryderwear'
  AND pis.itemName IN (
      'Contour Halter Sports Bra',
      'Contour Seamless High-Waisted Leggings',
      'Contour Seamless High-Waisted Shorts',
      'Contour Track Pants'
  );
-- Expectation: 0 rows

-- 1e) Confirm current MAX(db_itemId)
SELECT MAX(db_itemId) AS current_max_db_itemId FROM item;
-- Expectation from current reconciliation notes: 54

-- 1f) Confirm categoryId mapping for Tops/Pants
SELECT c.categoryId, c.categoryName
FROM category c
WHERE c.categoryName IN ('Tops', 'Pants')
ORDER BY c.categoryId;
-- Expected: Tops=1, Pants=2


-- =========================================================
-- 2) BACKUP TABLE (GUARDED)
-- =========================================================

-- Guard: fail-safe check to ensure backup table does not already exist
SET @ryderwear_contour_backup_exists := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'item_backup_before_ryderwear_contour_pilot'
);

-- Guard status: proceed only when status is OK_TO_RUN
SELECT
    CASE
        WHEN @ryderwear_contour_backup_exists = 0 THEN 'OK_TO_RUN'
        ELSE 'STOP_BACKUP_TABLE_ALREADY_EXISTS'
    END AS ryderwear_contour_pilot_guard_status;

-- Create one-time backup snapshot only when guard allows run
CREATE TABLE item_backup_before_ryderwear_contour_pilot AS
SELECT *
FROM item
WHERE @ryderwear_contour_backup_exists = 0;


-- =========================================================
-- 3) CONTROLLED INSERT (4 ROWS ONLY, INACTIVE)
-- =========================================================

-- Prerequisite: item.external_item_id must already be widened to VARCHAR(255)
-- using docs/operations/generated/sql/widen-item-external-item-id-for-model-id.sql.
-- Stable deterministic ordering for db_itemId assignment:
--   55 -> Contour Halter Sports Bra
--   56 -> Contour Seamless High-Waisted Leggings
--   57 -> Contour Seamless High-Waisted Shorts
--   58 -> Contour Track Pants

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
    pis.itemName,
    CASE
        WHEN pis.categoryName = 'Tops' THEN 1
        WHEN pis.categoryName = 'Pants' THEN 2
        WHEN pis.categoryName = 'Set' THEN 4
        WHEN pis.categoryName = 'Equipment' THEN 8
        ELSE 0
    END AS categoryId,
    pis.categoryName,
    pis.subCategoryParent AS parentCategory,
    pis.subCategory AS subcategory,
    NULLIF(TRIM(pis.price), '') AS price,
    NULLIF(TRIM(pis.salePrice), '') AS salePrice,
    NULLIF(TRIM(pis.description), '') AS description,
    NULLIF(TRIM(pis.images), '') AS images,
    NULLIF(TRIM(pis.altText), '') AS altText,
    NULLIF(TRIM(pis.ariaText), '') AS ariaText,
    pis.brand,
    0 AS featured,
    'custom' AS assignment_source,
    pis.model_id AS external_item_id,
    CASE pis.itemName
        WHEN 'Contour Halter Sports Bra' THEN 55
        WHEN 'Contour Seamless High-Waisted Leggings' THEN 56
        WHEN 'Contour Seamless High-Waisted Shorts' THEN 57
        WHEN 'Contour Track Pants' THEN 58
    END AS db_itemId,
    0 AS is_active
FROM product_import_staging pis
WHERE pis.brand = 'Ryderwear'
  AND pis.itemName IN (
      'Contour Halter Sports Bra',
      'Contour Seamless High-Waisted Leggings',
      'Contour Seamless High-Waisted Shorts',
      'Contour Track Pants'
  )
  AND (pis.db_itemId IS NULL OR TRIM(pis.db_itemId) = '')
  AND (pis.images IS NULL OR TRIM(pis.images) = '')
  AND pis.model_id IS NOT NULL
  AND TRIM(pis.model_id) <> ''
  AND @ryderwear_contour_backup_exists = 0
ORDER BY FIELD(
    pis.itemName,
    'Contour Halter Sports Bra',
    'Contour Seamless High-Waisted Leggings',
    'Contour Seamless High-Waisted Shorts',
    'Contour Track Pants'
);


-- =========================================================
-- 4) POST-INSERT VERIFICATION CHECKS
-- =========================================================

-- 4a) Show the 4 inserted rows
SELECT
    i.itemId,
    i.db_itemId,
    i.external_item_id,
    i.brand,
    i.itemName,
    i.categoryId,
    i.categoryName,
    i.parentCategory,
    i.subcategory,
    i.featured,
    i.assignment_source,
    i.is_active
FROM item i
WHERE i.external_item_id IN (
    SELECT pis.model_id
    FROM product_import_staging pis
    WHERE pis.brand = 'Ryderwear'
      AND pis.itemName IN (
          'Contour Halter Sports Bra',
          'Contour Seamless High-Waisted Leggings',
          'Contour Seamless High-Waisted Shorts',
          'Contour Track Pants'
      )
)
ORDER BY i.db_itemId;

-- 4b) Confirm all inserted pilot rows are inactive
SELECT
    i.itemName,
    i.db_itemId,
    i.is_active
FROM item i
WHERE i.db_itemId BETWEEN 55 AND 58
ORDER BY i.db_itemId;
-- Expectation: all is_active = 0

-- 4c) Confirm db_itemId 55-58 are present for this pilot
SELECT
    i.db_itemId,
    i.itemName,
    i.external_item_id
FROM item i
WHERE i.db_itemId BETWEEN 55 AND 58
ORDER BY i.db_itemId;

-- 4d) Confirm external_item_id populated from model_id
SELECT
    i.itemName,
    i.external_item_id,
    pis.model_id,
    CASE
        WHEN i.external_item_id = pis.model_id THEN 'MATCH'
        ELSE 'MISMATCH'
    END AS external_id_match_status
FROM item i
JOIN product_import_staging pis
    ON pis.model_id = i.external_item_id
WHERE pis.brand = 'Ryderwear'
  AND pis.itemName IN (
      'Contour Halter Sports Bra',
      'Contour Seamless High-Waisted Leggings',
      'Contour Seamless High-Waisted Shorts',
      'Contour Track Pants'
  )
ORDER BY i.db_itemId;

-- 4e) Confirm item count increased by 4 (vs backup snapshot)
SELECT
    (SELECT COUNT(*) FROM item_backup_before_ryderwear_contour_pilot) AS item_count_before,
    (SELECT COUNT(*) FROM item) AS item_count_after,
    (SELECT COUNT(*) FROM item) - (SELECT COUNT(*) FROM item_backup_before_ryderwear_contour_pilot) AS item_count_delta;
-- Expectation: item_count_delta = 4

-- 4f) Confirm no duplicate external_item_id
SELECT
    i.external_item_id,
    COUNT(*) AS duplicate_count
FROM item i
WHERE i.external_item_id IS NOT NULL
GROUP BY i.external_item_id
HAVING COUNT(*) > 1;
-- Expectation: 0 rows


-- =========================================================
-- 5) ROLLBACK SQL (COMMENTED OUT - DO NOT AUTO-EXECUTE)
-- =========================================================

-- Option A: Targeted rollback by pilot external_item_id/model_id
-- DELETE FROM item
-- WHERE external_item_id IN (
--     SELECT pis.model_id
--     FROM product_import_staging pis
--     WHERE pis.brand = 'Ryderwear'
--       AND pis.itemName IN (
--           'Contour Halter Sports Bra',
--           'Contour Seamless High-Waisted Leggings',
--           'Contour Seamless High-Waisted Shorts',
--           'Contour Track Pants'
--       )
-- );

-- Option B: Full restore from backup snapshot
-- TRUNCATE TABLE item;
-- INSERT INTO item SELECT * FROM item_backup_before_ryderwear_contour_pilot;
