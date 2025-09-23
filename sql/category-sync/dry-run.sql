-- category-sync / dry-run.sql
-- Purpose: Preview what WOULD change before we update anything.

-- Tip: run this connected to the sportswh schema in DBeaver (no transaction needed).
-- If you want to be extra cautious, you can wrap in START TRANSACTION; ... ROLLBACK;

SELECT 
  i.itemId,
  i.brand,
  i.itemName,
  i.categoryId        AS current_cat,
  pick.import_cat     AS new_cat
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
WHERE COALESCE(i.categoryId,-1) <> COALESCE(pick.import_cat,-1)
ORDER BY i.itemId;

