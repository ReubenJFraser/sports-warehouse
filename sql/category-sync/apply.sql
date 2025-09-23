-- category-sync / apply.sql
-- Purpose: Perform the actual update, safely, inside a transaction.

START TRANSACTION;

-- 1) Optional: quick preview of how many rows WOULD change
SELECT COUNT(*) AS would_change
FROM sportswh.item i
JOIN (
  SELECT i.itemId, MIN(ii.categoryId) AS import_cat
  FROM (
    SELECT
      it.itemId,
      LOWER(TRIM(it.brand)) AS b,
      REPLACE(REPLACE(REPLACE(REPLACE(
        LOWER(REGEXP_REPLACE(TRIM(it.itemName),'[^a-z0-9]+','')),
        'mens',''),'womens',''),'girls',''),'boys','') AS n
    FROM sportswh.item it
  ) i
  JOIN (
    SELECT
      LOWER(TRIM(im.brand)) AS b,
      im.categoryId,
      REPLACE(REPLACE(REPLACE(REPLACE(
        LOWER(REGEXP_REPLACE(TRIM(im.itemName),'[^a-z0-9]+','')),
        'mens',''),'womens',''),'girls',''),'boys','') AS n
    FROM sportswh.item_import im
  ) ii
    ON ii.b = i.b
   AND (
        ii.n = i.n
     OR REPLACE(ii.n,'scoop','') = i.n
     OR i.n LIKE CONCAT('%', ii.n, '%')
     OR ii.n LIKE CONCAT('%', i.n, '%')
   )
  GROUP BY i.itemId
) pick
  ON pick.itemId = i.itemId
WHERE COALESCE(i.categoryId,-1) <> COALESCE(pick.import_cat,-1);

-- 2) If the count above matches your expectation, run the UPDATE
UPDATE sportswh.item i
JOIN (
  SELECT i.itemId, MIN(ii.categoryId) AS import_cat
  FROM (
    SELECT
      it.itemId,
      LOWER(TRIM(it.brand)) AS b,
      REPLACE(REPLACE(REPLACE(REPLACE(
        LOWER(REGEXP_REPLACE(TRIM(it.itemName),'[^a-z0-9]+','')),
        'mens',''),'womens',''),'girls',''),'boys','') AS n
    FROM sportswh.item it
  ) i
  JOIN (
    SELECT
      LOWER(TRIM(im.brand)) AS b,
      im.categoryId,
      REPLACE(REPLACE(REPLACE(REPLACE(
        LOWER(REGEXP_REPLACE(TRIM(im.itemName),'[^a-z0-9]+','')),
        'mens',''),'womens',''),'girls',''),'boys','') AS n
    FROM sportswh.item_import im
  ) ii
    ON ii.b = i.b
   AND (
        ii.n = i.n
     OR REPLACE(ii.n,'scoop','') = i.n
     OR i.n LIKE CONCAT('%', ii.n, '%')
     OR ii.n LIKE CONCAT('%', i.n, '%')
   )
  GROUP BY i.itemId
) pick
  ON pick.itemId = i.itemId
SET i.categoryId = pick.import_cat
WHERE COALESCE(i.categoryId,-1) <> COALESCE(pick.import_cat,-1);

-- 3) Report how many rows were updated
SELECT ROW_COUNT() AS rows_updated;

-- 4) If everything looks correct, COMMIT; otherwise ROLLBACK;
-- COMMIT;
-- ROLLBACK;


