# db_itemId/model_id Readiness Audit (Read-Only)

- Generated: 2026-05-18 14:01:29 UTC
- Source CSV: `docs/data/SportWarehouse_ProductDB.csv`
- Scope: read-only audit (CSV + SELECT-only MySQL checks)
- DB status: connection failed (`SQLSTATE[HY000] [2002] Connection refused`)

## 1) CSV db_itemId audit
- Total CSV rows: **120**
- Rows with nonblank db_itemId: **54**
- Rows with blank db_itemId: **66**
- Duplicate nonblank db_itemId values: **0**
- First 20 nonblank db_itemId values: `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`, `11`, `12`, `13`, `14`, `15`, `16`, `17`, `18`, `19`, `20`

## 2) Live MySQL item audit
- Total active item rows: **0** (no `active` column; all rows counted)
- item.db_itemId exists: **no**
- Rows with nonblank item.db_itemId: **0**
- Rows with blank item.db_itemId: **0**
- Duplicate nonblank item.db_itemId values: **0**
- First 20 nonblank item.db_itemId values: ``
- First 20 itemId values: ``

## 3) Cross-check
- CSV db_itemId values matching MySQL itemId: **0**
- CSV db_itemId values matching MySQL db_itemId: **0**
- CSV db_itemId values not found in MySQL: **54**
- MySQL itemId/db_itemId values not represented in CSV: **0**

## 4) model_id audit
- Total CSV rows: **120**
- Nonblank model_id rows: **120**
- Blank model_id rows: **0**
- Duplicate model_id values: **1**
- Duplicate model_id list:
  - `nike_female_leggings` × 2

## 5) Classification
- Existing rows confidently linked by db_itemId: **0**
- Likely new insert candidates (blank db_itemId + unique nonblank model_id): **66**
- Rows requiring manual mapping: **54**

## Appendix (samples)
- Sample CSV db_itemId not found in MySQL (first 20): `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`, `11`, `12`, `13`, `14`, `15`, `16`, `17`, `18`, `19`, `20`
- Sample MySQL values not in CSV (first 20): ``
