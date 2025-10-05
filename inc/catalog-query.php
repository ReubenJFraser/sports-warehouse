<?php
// inc/catalog-query.php
// Populates: $total, $items, $catalogQueryRan
// Requires (from index.php): $pdo, $isCatalog, $brand, $gender, $age_group, $size_type,
// $q, $categoryID, $colors, $multiOnly, $pageSize, $offset, $sort, $SW_DEBUG, $pageNum.

require_once __DIR__ . '/color-groups.php';   // sw_color_groups(), sw_effective_colors()
require_once __DIR__ . '/color-where.php';    // sw_where_colors()

// Headroom + PHP picker for post-query override
require_once __DIR__ . '/image-picker.php';    // sw_pick_best_image()
require_once __DIR__ . '/image-headroom.php';  // sw_fetch_headroom_map()

$total = 0;
$items = [];
$catalogQueryRan = false;

if (!$isCatalog) return;

// ---------------------------
// Helper: execute statement (normalize + bind types correctly)
// ---------------------------
if (!function_exists('sw_execute_with_params')) {
  /**
   * Execute statement with normalized named parameters.
   * - Strips leading ':' from keys.
   * - Filters to only placeholders that appear in the SQL.
   * - Binds INT for numeric placeholders (e.g., limit/offset/categoryId).
   * - Logs SQL + final bound params under SW_DEBUG.
   */
  function sw_execute_with_params(PDOStatement $stmt, string $sql, array $params, array $extra = []): void {
    global $SW_DEBUG;

    $sqlText = $stmt->queryString ?: $sql;
    $names = [];
    if (preg_match_all('/:([a-zA-Z0-9_]+)/', $sqlText, $m)) {
      $names = array_unique($m[1]);
    }
    $present = $names ? array_flip($names) : [];

    $norm = [];
    foreach ($params as $k => $v) { $norm[ltrim((string)$k, ':')] = $v; }
    foreach ($extra  as $k => $v) { $norm[ltrim((string)$k, ':')] = $v; }

    $bind = $present ? array_intersect_key($norm, $present) : [];

    // Debug log what we're about to bind
    if (!empty($SW_DEBUG) && function_exists('sw_log')) {
      sw_log(['SQL' => $sqlText, 'params' => $bind]);
    }

    // Bind with types where needed (INT for limit/offset/ids)
    foreach ($bind as $name => $value) {
      $param = ':' . $name;

      // Decide INT vs STR
      $isIntName = in_array($name, ['limit','offset','categoryId','page','per_page'], true);
      $isIntVal  = is_int($value) || (is_numeric($value) && (string)(int)$value === (string)$value);

      if ($isIntName || $isIntVal) {
        $stmt->bindValue($param, (int)$value, PDO::PARAM_INT);
      } else {
        $stmt->bindValue($param, $value, PDO::PARAM_STR);
      }
    }

    $stmt->execute();
  }
}


// ---------------------------
/** WHERE + params **/
// ---------------------------
$where  = [];
$params = [];

/** Optional dev filter: only items with an override row */
$overridesOnly = isset($_GET['overridesOnly']) && in_array(strtolower((string)$_GET['overridesOnly']), ['1','true','yes'], true);
if ($overridesOnly) {
  $where[] = '(o.orientation IS NOT NULL OR o.ratio IS NOT NULL OR o.image_basename IS NOT NULL)';
}

// Gender normalization → canonical 'women' | 'men' | 'kids'
if ($gender !== '') {
  $where[] = "(CASE
      WHEN LOWER(TRIM(i.gender)) IN ('women','womens','female','ladies')             THEN 'women'
      WHEN LOWER(TRIM(i.gender)) IN ('men','mens','male')                            THEN 'men'
      WHEN LOWER(TRIM(i.gender)) IN ('kids','kid','children','boys','girls','youth') THEN 'kids'
      ELSE ''
    END) = :gender";
  $params[':gender'] = $gender;
}

if ($brand !== '') {
  $where[] = "LOWER(i.brand) = LOWER(:brand)";
  $params[':brand'] = $brand;
}
if ($age_group !== '') {
  $where[] = "LOWER(i.age_group) = LOWER(:age_group)";
  $params[':age_group'] = $age_group;
}
if ($size_type !== '') {
  $where[] = "LOWER(i.size_type) = LOWER(:size_type)";
  $params[':size_type'] = $size_type;
}
if ($q !== '') {
  $where[] = "(i.itemName LIKE :q OR i.description LIKE :q OR i.categoryName LIKE :q)";
  $params[':q'] = '%'.$q.'%';
}
if (!is_null($categoryID)) {
  $where[] = "i.categoryId = :categoryId";
  $params[':categoryId'] = $categoryID;
}

/* =========================
   Color group + ANY/ALL
   ========================= */
$match = (isset($_GET['match']) && strtolower((string)$_GET['match']) === 'all') ? 'all' : 'any';
$groupParam = $_GET['group'] ?? null;
$groupList = [];
if (is_array($groupParam)) {
  foreach ($groupParam as $g) {
    $slug = preg_replace('/[^a-z]/', '', strtolower((string)$g));
    if ($slug !== '') $groupList[] = $slug;
  }
} elseif (is_string($groupParam) && $groupParam !== '') {
  $slug = preg_replace('/[^a-z]/', '', strtolower($groupParam));
  if ($slug !== '') $groupList[] = $slug;
}
$groupList = array_map(function ($g) { return $g === 'colors' ? 'color' : $g; }, $groupList);
$validGroups = array_keys(sw_color_groups());
$groupList = array_values(array_intersect($groupList, $validGroups));
$effectiveColors = sw_effective_colors($groupList, $colors ?? []);

if (!empty($SW_DEBUG) && function_exists('sw_log')) {
  sw_log(['match' => $match, 'group' => $groupList, 'colors' => ($colors ?? []), 'effectiveColors' => $effectiveColors]);
}
$hasExplicit = !empty($colors);
if ($match === 'all' && count($groupList) === 1 && !$hasExplicit) {
  $match = 'any';
}
$multiOnlyFlag = !empty($multiOnly);
list($colorFrag, $colorParams) = sw_where_colors($match, $effectiveColors, $multiOnlyFlag, 'i');
if ($colorFrag) {
  $where[] = $colorFrag;
  $params  = array_merge($params, $colorParams);
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

if (!empty($SW_DEBUG) && function_exists('sw_log')) {
  sw_log(['WHERE' => $whereSql, 'rawParams' => $params]);
}

// ---------------------------
/** Sorting **/
// ---------------------------
$sortMap = [
  'relevance'  => 'i.featured DESC, i.itemName ASC',
  'price_asc'  => 'COALESCE(i.salePrice, i.price) ASC,  i.itemName ASC',
  'price_desc' => 'COALESCE(i.salePrice, i.price) DESC, i.itemName ASC',
  'name_asc'   => 'i.itemName ASC',
  'name_desc'  => 'i.itemName DESC',
];
$orderBy = $sortMap[$sort] ?? $sortMap['relevance'];

// ---------------------------
/** COUNT **/
// ---------------------------
$sqlCount = "
  SELECT COUNT(DISTINCT i.itemId)
  FROM item i
  LEFT JOIN item_orientation_override o ON o.itemId = i.itemId
  LEFT JOIN category c ON c.categoryId = i.categoryId
  {$whereSql}
";
$stmt = $pdo->prepare($sqlCount);
sw_execute_with_params($stmt, $sqlCount, $params);
$total = (int)$stmt->fetchColumn();

// ---------------------------
/** PAGE (headroom-aware, SQL-side coarse pick + PHP override) **/
// ---------------------------
$sqlItems = "
  SELECT
    i.itemId, i.itemName, i.brand, i.price, i.salePrice, i.description,
    i.subcategory, i.parentCategory, i.categoryId,
    COALESCE(c.categoryName, i.categoryName) AS categoryName,
    i.orientation, i.altText, i.thumbnails_json, i.chosen_image, i.chosen_ratio,

    /* Derive bases once via expressions */
    SUBSTRING_INDEX(i.chosen_image, '/', -1) AS _chosen_base,
    SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1) AS _thumb1_base,

    /* ===========================================================
       SQL EFFECTIVE HERO (coarse):
       1) First thumbnail (in thumbnails_json order) where:
          - headroom.face_count >= 1
          - focus_y_pct <= 33.34 (face in upper third)
          - crop_safe = 1
          We return the token path (prefixed with images/ only if needed).
       2) Else stored chosen_image if crop_safe
       3) Else first-thumb if crop_safe
       4) Else stored chosen_image
       =========================================================== */
    CASE
      WHEN EXISTS (
        SELECT 1
        FROM (
          SELECT
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', n.n), ';', -1)) AS token,
            n.n AS pos
          FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
            UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
            UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
          ) AS n
          WHERE n.n <= 1 + LENGTH(i.thumbnails_json) - LENGTH(REPLACE(i.thumbnails_json, ';', ''))
        ) t
        JOIN image_headroom ihx
          ON ihx.image_basename = SUBSTRING_INDEX(t.token, '/', -1)
        WHERE ihx.face_count >= 1
          AND ihx.focus_y_pct IS NOT NULL AND ihx.focus_y_pct <= 33.34
          AND ihx.crop_safe = 1
        LIMIT 1
      )
      THEN (
        SELECT
          CASE
            WHEN LEFT(t2.token, 7) = 'images/' THEN t2.token
            ELSE CONCAT('images/', SUBSTRING_INDEX(t2.token, '/', -1))
          END
        FROM (
          SELECT
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', n.n), ';', -1)) AS token,
            n.n AS pos
          FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
            UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
            UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
          ) AS n
          WHERE n.n <= 1 + LENGTH(i.thumbnails_json) - LENGTH(REPLACE(i.thumbnails_json, ';', ''))
        ) t2
        JOIN image_headroom ih2
          ON ih2.image_basename = SUBSTRING_INDEX(t2.token, '/', -1)
        WHERE ih2.face_count >= 1
          AND ih2.focus_y_pct IS NOT NULL AND ih2.focus_y_pct <= 33.34
          AND ih2.crop_safe = 1
        ORDER BY t2.pos
        LIMIT 1
      )

      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
          AND ih.crop_safe = 1
      )
      THEN i.chosen_image

      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
          AND ih.crop_safe = 1
      )
      THEN CONCAT(
        'images/',
        SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
      )

      ELSE i.chosen_image
    END AS chosen_image_effective,

    /* Effective ratio aligned to the SAME SQL-chosen image */
    CASE
      WHEN EXISTS (
        SELECT 1
        FROM (
          SELECT
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', n.n), ';', -1)) AS token,
            n.n AS pos
          FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
            UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
            UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
          ) AS n
          WHERE n.n <= 1 + LENGTH(i.thumbnails_json) - LENGTH(REPLACE(i.thumbnails_json, ';', ''))
        ) t
        JOIN image_headroom ihx
          ON ihx.image_basename = SUBSTRING_INDEX(t.token, '/', -1)
        WHERE ihx.face_count >= 1
          AND ihx.focus_y_pct IS NOT NULL AND ihx.focus_y_pct <= 33.34
          AND ihx.crop_safe = 1
        LIMIT 1
      )
      THEN (
        SELECT ih3.ratio
        FROM (
          SELECT
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', n.n), ';', -1)) AS token,
            n.n AS pos
          FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
            UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
            UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
          ) AS n
          WHERE n.n <= 1 + LENGTH(i.thumbnails_json) - LENGTH(REPLACE(i.thumbnails_json, ';', ''))
        ) t3
        JOIN image_headroom ih3
          ON ih3.image_basename = SUBSTRING_INDEX(t3.token, '/', -1)
        WHERE ih3.face_count >= 1
          AND ih3.focus_y_pct IS NOT NULL AND ih3.focus_y_pct <= 33.34
          AND ih3.crop_safe = 1
        ORDER BY t3.pos
        LIMIT 1
      )

      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
          AND ih.crop_safe = 1
      )
      THEN (SELECT ih2.ratio FROM image_headroom ih2
            WHERE ih2.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
            LIMIT 1)

      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
          AND ih.crop_safe = 1
      )
      THEN (SELECT ih4.ratio FROM image_headroom ih4
            WHERE ih4.image_basename = SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
            LIMIT 1)

      ELSE i.chosen_ratio
    END AS chosen_ratio_effective,

    /* Effective focus_y aligned to the SAME SQL-chosen image */
    CASE
      WHEN EXISTS (
        SELECT 1
        FROM (
          SELECT
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', n.n), ';', -1)) AS token,
            n.n AS pos
          FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
            UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
            UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
          ) AS n
          WHERE n.n <= 1 + LENGTH(i.thumbnails_json) - LENGTH(REPLACE(i.thumbnails_json, ';', ''))
        ) t
        JOIN image_headroom ihx
          ON ihx.image_basename = SUBSTRING_INDEX(t.token, '/', -1)
        WHERE ihx.face_count >= 1
          AND ihx.focus_y_pct IS NOT NULL AND ihx.focus_y_pct <= 33.34
          AND ihx.crop_safe = 1
        LIMIT 1
      )
      THEN (
        SELECT ih5.focus_y_pct
        FROM (
          SELECT
            TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', n.n), ';', -1)) AS token,
            n.n AS pos
          FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
            UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
            UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
          ) AS n
          WHERE n.n <= 1 + LENGTH(i.thumbnails_json) - LENGTH(REPLACE(i.thumbnails_json, ';', ''))
        ) t5
        JOIN image_headroom ih5
          ON ih5.image_basename = SUBSTRING_INDEX(t5.token, '/', -1)
        WHERE ih5.face_count >= 1
          AND ih5.focus_y_pct IS NOT NULL AND ih5.focus_y_pct <= 33.34
          AND ih5.crop_safe = 1
        ORDER BY t5.pos
        LIMIT 1
      )

      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
          AND ih.crop_safe = 1
      )
      THEN (SELECT ih2.focus_y_pct FROM image_headroom ih2
            WHERE ih2.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
            LIMIT 1)

      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
          AND ih.crop_safe = 1
      )
      THEN (SELECT ih3.focus_y_pct FROM image_headroom ih3
            WHERE ih3.image_basename = SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
            LIMIT 1)

      ELSE (SELECT ih4.focus_y_pct FROM image_headroom ih4
            WHERE ih4.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
            LIMIT 1)
    END AS chosen_focus_y_effective,

    /* Debug: 1 when we did not keep stored chosen_image because it wasn't crop-safe,
       but we did find a safe first-thumb; else 0. If stored is empty, also 1. */
    CASE
      WHEN i.chosen_image IS NULL OR i.chosen_image = '' THEN 1
      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(i.chosen_image, '/', -1)
          AND ih.crop_safe = 1
      ) THEN 0
      WHEN EXISTS(
        SELECT 1 FROM image_headroom ih
        WHERE ih.image_basename = SUBSTRING_INDEX(SUBSTRING_INDEX(i.thumbnails_json, ';', 1), '/', -1)
          AND ih.crop_safe = 1
      ) THEN 1
      ELSE 0
    END AS used_fallback,

    /* Crop gating/debug for the immediate override-or-stored (not the effective) */
    (CASE
       WHEN h.face_count IS NULL THEN NULL
       WHEN h.face_count >= 1 AND h.headroom_pct >= 6.0 THEN 1
       ELSE 0
     END) AS crop_allowed,
    h.headroom_pct,
    h.focus_y_pct,

    o.orientation AS o_orientation, o.ratio AS o_ratio, o.image_basename AS o_image
  FROM item i
  LEFT JOIN item_orientation_override o ON o.itemId = i.itemId
  LEFT JOIN category c ON c.categoryId = i.categoryId
  /* Headroom for the immediate candidate shown first (override → stored); this is for debug flags */
  LEFT JOIN image_headroom h
    ON h.image_basename = COALESCE(
         o.image_basename,
         SUBSTRING_INDEX(i.chosen_image, '/', -1)
       )
  {$whereSql}
  ORDER BY {$orderBy}
  LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sqlItems);
sw_execute_with_params($stmt, $sqlItems, $params, [
  'limit'  => (int)$pageSize,
  'offset' => (int)$offset,
]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------------------------------------------
   PHP-side headroom-aware override (scans ALL candidates).
   Toggle with ?sw_effective=0 to disable.
-------------------------------------------------------------------*/
$useEffective = !isset($_GET['sw_effective']) || $_GET['sw_effective'] !== '0';
$BOX_RATIO = 0.8; // 4:5 target
if ($useEffective && !empty($items)) {
  $allBases = [];
  $__candidates = [];

  foreach ($items as $it) {
    $cand = [];

    // Stored chosen (if any)
    if (!empty($it['chosen_image'])) {
      $base = strtolower(substr($it['chosen_image'], strrpos($it['chosen_image'], '/') + 1));
      $cand[] = ['path' => $it['chosen_image'], 'basename' => $base, 'source' => 'stored'];
      $allBases[] = $base;
    }

    // All thumbnails from semicolon-separated list (not literal JSON)
    if (!empty($it['thumbnails_json'])) {
      $thumbs = array_filter(array_map('trim', explode(';', $it['thumbnails_json'])));
      foreach ($thumbs as $t) {
        // Keep directory if present in token; default to prefixing images/ when absent
        $p = (strpos($t, 'images/') === 0) ? $t : ('images/' . $t);
        $base = strtolower(substr($p, strrpos($p, '/') + 1));
        $cand[] = ['path' => $p, 'basename' => $base, 'source' => 'thumb'];
        $allBases[] = $base;
      }
    }

    $__candidates[] = $cand;
  }

  // Fetch headroom in one go for all candidates on this page
  $headroomMap = sw_fetch_headroom_map($pdo, $allBases);

  // Score and set effective fields per item
  foreach ($items as $idx => &$it) {
    $cands = $__candidates[$idx] ?? [];
    if (empty($cands)) continue;

    // Enrich with ratios when available from headroom table
    foreach ($cands as &$c) {
      $b = $c['basename'];
      if (!isset($c['ratio']) && isset($headroomMap[$b]['ratio']) && $headroomMap[$b]['ratio'] > 0) {
        $c['ratio'] = (float)$headroomMap[$b]['ratio'];
      }
    }
    unset($c);

    [$best, $scores] = sw_pick_best_image($cands, $BOX_RATIO, $headroomMap);

    if (!empty($best) && !empty($best['path'])) {
      $it['chosen_image_effective'] = $best['path'];
      $it['chosen_ratio_effective'] = isset($best['ratio']) && $best['ratio'] > 0
        ? (float)$best['ratio']
        : ($it['chosen_ratio_effective'] ?? null);

      // Optionally surface focus for front-end consumers
      $b = $best['basename'];
      if (isset($headroomMap[$b]['focus_y_pct'])) {
        $it['chosen_focus_y_effective'] = $headroomMap[$b]['focus_y_pct'];
      }

      // Debug breadcrumb when overriding SQL's fallback
      if (!empty($SW_DEBUG)) {
        $stored = $it['chosen_image'] ?? '';
        if ($stored !== '' && $stored !== $it['chosen_image_effective']) {
          error_log('[SW EFFECTIVE OVERRIDE] itemId=' . ($it['itemId'] ?? '?')
            . ' stored=' . $stored
            . ' effective=' . $it['chosen_image_effective']);
        }
      }
    }
  }
  unset($it);
}

$catalogQueryRan = true;

if (!empty($SW_DEBUG) && function_exists('sw_log')) {
  sw_log(['TOTAL' => $total, 'page' => $pageNum ?? 1, 'pageSize' => $pageSize]);
}





















