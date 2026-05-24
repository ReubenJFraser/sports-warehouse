-- LOCAL-ONLY: Widen item.external_item_id to safely store product_import_staging.model_id values.
-- Context: Pending Ryderwear imports include model_id values longer than 64 chars.
-- IMPORTANT: Run manually in a local environment. Do NOT run automatically in app/runtime code.

-- =========================================================
-- 1) PREFLIGHT (READ-ONLY CHECKS)
-- =========================================================

-- 1a) Inspect current item table definition.
SHOW CREATE TABLE item;

-- 1b) Show current external_item_id column definition.
SELECT
  COLUMN_NAME,
  COLUMN_TYPE,
  IS_NULLABLE,
  COLUMN_DEFAULT,
  COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'item'
  AND COLUMN_NAME = 'external_item_id';

-- 1c) Show current indexes involving external_item_id.
SELECT
  INDEX_NAME,
  NON_UNIQUE,
  SEQ_IN_INDEX,
  COLUMN_NAME,
  COLLATION,
  SUB_PART
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'item'
  AND COLUMN_NAME = 'external_item_id'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 1d) Count pending staging rows where model_id exceeds 64 chars.
SELECT COUNT(*) AS pending_model_id_len_gt_64
FROM product_import_staging pis
WHERE CHAR_LENGTH(pis.model_id) > 64
  AND (
    pis.db_itemId IS NULL
    OR pis.db_itemId = ''
    OR pis.db_itemId = 0
  );

-- 1e) Show max pending model_id length.
SELECT COALESCE(MAX(CHAR_LENGTH(pis.model_id)), 0) AS max_pending_model_id_length
FROM product_import_staging pis
WHERE (
    pis.db_itemId IS NULL
    OR pis.db_itemId = ''
    OR pis.db_itemId = 0
  );

-- 1f) Confirm no duplicate model_id values among pending rows.
SELECT
  pis.model_id,
  COUNT(*) AS duplicate_count
FROM product_import_staging pis
WHERE (
    pis.db_itemId IS NULL
    OR pis.db_itemId = ''
    OR pis.db_itemId = 0
  )
GROUP BY pis.model_id
HAVING COUNT(*) > 1;

-- 1g) Confirm no pending model_id collides with existing item.external_item_id.
SELECT
  pis.model_id,
  i.id AS existing_item_id
FROM product_import_staging pis
INNER JOIN item i
  ON i.external_item_id = pis.model_id
WHERE (
    pis.db_itemId IS NULL
    OR pis.db_itemId = ''
    OR pis.db_itemId = 0
  );

-- =========================================================
-- 2) SCHEMA CHANGE
-- =========================================================

ALTER TABLE item
  MODIFY external_item_id VARCHAR(255) DEFAULT NULL;

-- =========================================================
-- 3) POST-CHANGE VERIFICATION
-- =========================================================

-- 3a) Confirm external_item_id is now VARCHAR(255).
SELECT
  COLUMN_NAME,
  COLUMN_TYPE,
  IS_NULLABLE,
  COLUMN_DEFAULT,
  COLLATION_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'item'
  AND COLUMN_NAME = 'external_item_id';

-- 3b) Confirm UNIQUE index on external_item_id still exists.
SELECT
  INDEX_NAME,
  NON_UNIQUE,
  SEQ_IN_INDEX,
  COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'item'
  AND COLUMN_NAME = 'external_item_id'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 3c) Re-run max pending model_id length check.
SELECT COALESCE(MAX(CHAR_LENGTH(pis.model_id)), 0) AS max_pending_model_id_length
FROM product_import_staging pis
WHERE (
    pis.db_itemId IS NULL
    OR pis.db_itemId = ''
    OR pis.db_itemId = 0
  );

-- 3d) Confirm pending model_id length now fits external_item_id capacity.
SELECT
  CASE
    WHEN COALESCE(MAX(CHAR_LENGTH(pis.model_id)), 0) <= 255 THEN 'OK'
    ELSE 'NOT_OK'
  END AS pending_model_id_fits_external_item_id,
  COALESCE(MAX(CHAR_LENGTH(pis.model_id)), 0) AS max_pending_model_id_length,
  255 AS external_item_id_capacity
FROM product_import_staging pis
WHERE (
    pis.db_itemId IS NULL
    OR pis.db_itemId = ''
    OR pis.db_itemId = 0
  );

-- =========================================================
-- 4) ROLLBACK (COMMENTED OUT; MANUAL USE ONLY)
-- =========================================================

-- SAFETY CHECK BEFORE ROLLBACK:
-- Only rollback to VARCHAR(64) if this returns 0.
-- SELECT COUNT(*) AS values_exceeding_64
-- FROM item
-- WHERE external_item_id IS NOT NULL
--   AND CHAR_LENGTH(external_item_id) > 64;

-- MANUAL ROLLBACK (DO NOT RUN UNLESS SAFETY CHECK PASSES):
-- ALTER TABLE item
--   MODIFY external_item_id VARCHAR(64) DEFAULT NULL;
