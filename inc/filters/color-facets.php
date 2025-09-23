<?php
// inc/filters/color-facets.php
// Faceted counts for colors (and color groups), kept in lock-step with catalog results.

require_once dirname(__DIR__) . '/color-groups.php'; // sw_color_groups(), sw_effective_colors()
require_once dirname(__DIR__) . '/color-where.php';   // sw_where_colors()

// Expect $pdo and the same request params already in scope (or read from $_GET safely)
$brand      = isset($_GET['brand'])      ? trim((string)$_GET['brand'])      : '';
$gender     = isset($_GET['gender'])     ? trim((string)$_GET['gender'])     : '';
$age_group  = isset($_GET['age_group'])  ? trim((string)$_GET['age_group'])  : '';
$size_type  = isset($_GET['size_type'])  ? trim((string)$_GET['size_type'])  : '';
$q          = isset($_GET['q'])          ? trim((string)$_GET['q'])          : '';
$categoryID = filter_input(INPUT_GET, 'categoryID', FILTER_VALIDATE_INT) ?: null;

// Selected colors (explicit) and group selection
// NOTE: if your UI uses a different query name (e.g., 'colors' vs 'color[]'), adjust here.
$explicitColors = [];
if (isset($_GET['color'])) {
  $raw = (array)$_GET['color'];
  foreach ($raw as $c) {
    $c = strtolower(preg_replace('/[^a-z]/', '', (string)$c));
    if ($c !== '') $explicitColors[] = $c;
  }
}

$groupParam = $_GET['group'] ?? '';
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
// Alias simple plurals (e.g., group=colors → color)
$groupList = array_map(function ($g) { return $g === 'colors' ? 'color' : $g; }, $groupList);

// Validate against known groups
$validGroups = array_keys(sw_color_groups());
$groupList = array_values(array_intersect($groupList, $validGroups));

// Match mode (if present)
$match = (isset($_GET['match']) && strtolower((string)$_GET['match']) === 'all') ? 'all' : 'any';

// Multi-only toggle (if present)
$multiOnlyFlag = !empty($_GET['multi']);

// Effective colors (selected)
$effectiveColors = sw_effective_colors($groupList, $explicitColors);

// ---------------------------
// Build WHERE (same as catalog, then optionally add color fragment)
// ---------------------------
$where  = [];
$params = [];

// Gender normalization → canonical 'women' | 'men' | 'kids'
if ($gender !== '') {
  $where[] = "(CASE
      WHEN LOWER(TRIM(i.gender)) IN ('women','womens','female','ladies')             THEN 'women'
      WHEN LOWER(TRIM(i.gender)) IN ('men','mens','male')                            THEN 'men'
      WHEN LOWER(TRIM(i.gender)) IN ('kids','kid','children','boys','girls','youth') THEN 'kids'
      ELSE ''
    END) = :gender";
  $params[':gender'] = strtolower($gender);
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

// If only one group is selected and no explicit swatches, treat ALL as ANY for facet parity
$hasExplicit = !empty($explicitColors);
if ($match === 'all' && count($groupList) === 1 && !$hasExplicit) {
  $match = 'any';
}

// Apply color filters using the SAME helper so counts == grid totals
list($colorFrag, $colorParams) = sw_where_colors($match, $effectiveColors, $multiOnlyFlag, 'i');
if ($colorFrag) {
  $where[] = $colorFrag;
  $params  = array_merge($params, $colorParams);
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

// ---------------------------
// Compute per-color counts from the filtered item set
// ---------------------------
//
// We build a derived table 't' that emits one row per color (primary + secondary) for items matching the WHERE.
$sqlFacet = "
  WITH filtered AS (
    SELECT i.itemId, LOWER(i.color_primary) AS cp, LOWER(i.color_secondary) AS cs
    FROM item i
    {$whereSql}
  ),
  exploded AS (
    SELECT cp AS color FROM filtered WHERE cp IS NOT NULL AND cp <> ''
    UNION ALL
    SELECT cs AS color FROM filtered WHERE cs IS NOT NULL AND cs <> ''
  )
  SELECT color, COUNT(*) AS cnt
  FROM exploded
  GROUP BY color
";
$stmt = $pdo->prepare($sqlFacet);

// Reuse the same normalized binding approach
if (!function_exists('sw_execute_with_params')) {
  function sw_execute_with_params(PDOStatement $stmt, string $sql, array $params, array $extra = []): void {
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
    $stmt->execute($bind);
  }
}

sw_execute_with_params($stmt, $sqlFacet, $params);
$facetRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$counts = [];
foreach ($facetRows as $row) {
  $c = (string)$row['color'];
  $counts[$c] = (int)$row['cnt'];
}

// Group counts based on canonical grouping
$groups = sw_color_groups();
$groupCounts = [
  'dark'  => array_sum(array_intersect_key($counts, array_flip($groups['dark']))),
  'light' => array_sum(array_intersect_key($counts, array_flip($groups['light']))),
  'color' => array_sum(array_intersect_key($counts, array_flip($groups['color']))),
];

// UI state
$selectedColors = $explicitColors;
$selectedGroup  = '';
if (!empty($groupList)) {
  // If only one group selected, reflect it
  if (count($groupList) === 1) $selectedGroup = $groupList[0];
}
?>
<form class="filters filters--colors" method="get">
  <input type="hidden" name="section" value="catalog">
  <?php if ($gender !== ''): ?>
    <input type="hidden" name="gender" value="<?= htmlspecialchars(strtolower($gender), ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <?php if ($brand !== ''): ?>
    <input type="hidden" name="brand" value="<?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <?php if ($age_group !== ''): ?>
    <input type="hidden" name="age_group" value="<?= htmlspecialchars($age_group, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <?php if ($size_type !== ''): ?>
    <input type="hidden" name="size_type" value="<?= htmlspecialchars($size_type, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <?php if (!is_null($categoryID)): ?>
    <input type="hidden" name="categoryID" value="<?= (int)$categoryID ?>">
  <?php endif; ?>
  <?php if ($q !== ''): ?>
    <input type="hidden" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>

  <!-- Group selector -->
  <fieldset class="filter-color-groups">
    <legend>Color Group</legend>
    <div class="color-group-tiles">
      <?php foreach (['dark','light','color'] as $g): ?>
        <label class="group-tile group-<?= $g ?>" title="<?= htmlspecialchars(sw_group_label($g)) ?>">
          <input type="radio" name="group" value="<?= $g ?>" <?= $selectedGroup===$g?'checked':'' ?>>
          <span class="tile-visual"></span>
          <span class="tile-label">
            <?= htmlspecialchars(sw_group_label($g)) ?>
            <small>(<?= (int)($groupCounts[$g] ?? 0) ?>)</small>
          </span>
        </label>
      <?php endforeach; ?>
      <label class="group-tile group-none" title="No group (all colors)">
        <input type="radio" name="group" value="" <?= $selectedGroup===''?'checked':'' ?>>
        <span class="tile-visual"></span>
        <span class="tile-label">All Colors</span>
      </label>
    </div>
  </fieldset>

  <!-- Match ANY/ALL -->
  <fieldset class="filter-match">
    <legend>Match</legend>
    <label><input type="radio" name="match" value="any" <?= $match==='any'?'checked':'' ?>> Any</label>
    <label><input type="radio" name="match" value="all" <?= $match==='all'?'checked':'' ?>> All</label>
  </fieldset>

  <!-- Specific swatches (drill-down) -->
  <details class="filter-specific" <?= empty($selectedColors) ? '' : 'open' ?>>
    <summary>Choose specific colors</summary>
    <div class="color-chips">
      <?php
      // Sort chips by count desc, then name asc for consistency
      $chipColors = array_keys($counts);
      usort($chipColors, function($a,$b) use($counts){
        $da = $counts[$a] ?? 0; $db = $counts[$b] ?? 0;
        if ($da === $db) return strcmp($a,$b);
        return ($db <=> $da);
      });
      foreach ($chipColors as $c):
        $cnt = (int)($counts[$c] ?? 0);
      ?>
        <label class="color-chip">
          <input type="checkbox" name="color[]" value="<?= htmlspecialchars($c) ?>"
                 <?= in_array($c,$selectedColors,true) ? 'checked' : '' ?>>
          <span class="swatch swatch-<?= htmlspecialchars($c) ?>" role="img" aria-label="<?= htmlspecialchars(ucfirst($c)) ?>"></span>
          <span class="name"><?= ucfirst($c) ?> <small>(<?= $cnt ?>)</small></span>
        </label>
      <?php endforeach; ?>
    </div>
  </details>

  <div class="filters-actions">
    <button type="submit">Apply</button>
    <a class="link-reset" href="?section=catalog">Reset</a>
  </div>
</form>




