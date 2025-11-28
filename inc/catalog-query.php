<?php
// inc/catalog-query.php
// Populates: $total, $items, $catalogQueryRan
// Requires (from index.php): $pdo, $isCatalog, $brand, $gender, $age_group,
// $size_type, $q, $categoryID, $colors, $multiOnly, $pageSize,
// $offset, $sort, $SW_DEBUG, $pageNum.

require_once __DIR__ . '/color-groups.php';
require_once __DIR__ . '/color-where.php';

$total = 0;
$items = [];
$catalogQueryRan = false;

if (!$isCatalog) return;

// ---------------------------
// Helper: execute statement
// ---------------------------
if (!function_exists('sw_execute_with_params')) {
    function sw_execute_with_params(PDOStatement $stmt, string $sql, array $params, array $extra = []): void {
        global $SW_DEBUG;

        $sqlText = $stmt->queryString ?: $sql;

        // Extract named parameters found in SQL
        $names = [];
        if (preg_match_all('/:([a-zA-Z0-9_]+)/', $sqlText, $m)) {
            $names = array_unique($m[1]);
        }
        $present = $names ? array_flip($names) : [];

        // Normalize + merge
        $norm = [];
        foreach ($params as $k => $v) $norm[ltrim((string)$k, ':')] = $v;
        foreach ($extra  as $k => $v) $norm[ltrim((string)$k, ':')] = $v;

        // Only bind those actually in SQL
        $bind = $present ? array_intersect_key($norm, $present) : [];

        if (!empty($SW_DEBUG) && function_exists('sw_log')) {
            sw_log(['SQL' => $sqlText, 'params' => $bind]);
        }

        foreach ($bind as $name => $value) {
            $param = ':' . $name;

            // Bind as INT if appropriate
            $isIntName = in_array($name, ['limit','offset','categoryId','page','per_page'], true);
            $isIntVal  = is_int($value) || ((string)(int)$value === (string)$value);

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
// WHERE conditions
// ---------------------------
$where  = [];
$params = [];

// Overrides-only filter (these still exist but only for testing)
$overridesOnly = isset($_GET['overridesOnly']) &&
    in_array(strtolower((string)$_GET['overridesOnly']), ['1','true','yes'], true);

if ($overridesOnly) {
    $where[] = '(o.orientation IS NOT NULL OR o.ratio IS NOT NULL OR o.image_basename IS NOT NULL)';
}

// Gender
if ($gender !== '') {
    $where[] = "(CASE
        WHEN LOWER(TRIM(i.gender)) IN ('women','womens','female','ladies')             THEN 'women'
        WHEN LOWER(TRIM(i.gender)) IN ('men','mens','male')                            THEN 'men'
        WHEN LOWER(TRIM(i.gender)) IN ('kids','kid','children','boys','girls','youth') THEN 'kids'
        ELSE ''
    END) = :gender";
    $params[':gender'] = $gender;
}

// Brand
if ($brand !== '') {
    $where[] = "LOWER(i.brand) = LOWER(:brand)";
    $params[':brand'] = $brand;
}

// Age group
if ($age_group !== '') {
    $where[] = "LOWER(i.age_group) = LOWER(:age_group)";
    $params[':age_group'] = $age_group;
}

// Size type
if ($size_type !== '') {
    $where[] = "LOWER(i.size_type) = LOWER(:size_type)";
    $params[':size_type'] = $size_type;
}

// Search
if ($q !== '') {
    $where[] = "(i.itemName LIKE :q OR i.description LIKE :q OR i.categoryName LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

// Category
if (!is_null($categoryID)) {
    $where[] = "i.categoryId = :categoryId";
    $params[':categoryId'] = $categoryID;
}

// ---------------------------
// Color filtering
// ---------------------------
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

$groupList = array_map(function ($g) {
    return $g === 'colors' ? 'color' : $g;
}, $groupList);

$validGroups = array_keys(sw_color_groups());
$groupList = array_values(array_intersect($groupList, $validGroups));

$effectiveColors = sw_effective_colors($groupList, $colors ?? []);
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

// ---------------------------
// Sorting
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
// COUNT
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
// ITEM LIST — HERO-BASED
// ---------------------------
$sqlItems = "
  SELECT
    i.itemId,
    i.itemName,
    i.brand,
    i.price,
    i.salePrice,
    i.description,
    i.subcategory,
    i.parentCategory,
    i.categoryId,
    COALESCE(c.categoryName, i.categoryName) AS categoryName,

    /* legacy orientation field (fallback only) */
    i.orientation,

    /* thumbnails (raw list) */
    i.thumbnails_json,

    /* NEW HERO FIELDS */
    i.hero_image,
    i.hero_ratio,
    i.hero_orientation,
    i.hero_score,

    /* Alt text */
    i.altText

  FROM item i
  LEFT JOIN category c ON c.categoryId = i.categoryId
  LEFT JOIN item_orientation_override o ON o.itemId = i.itemId

  {$whereSql}
  ORDER BY {$orderBy}
  LIMIT {$pageSize} OFFSET {$offset}
";

$stmt = $pdo->prepare($sqlItems);
sw_execute_with_params($stmt, $sqlItems, $params);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$catalogQueryRan = true;

if (!empty($SW_DEBUG) && function_exists('sw_log')) {
    sw_log(['TOTAL' => $total, 'page' => ($pageNum ?? 1), 'pageSize' => $pageSize]);
}




















