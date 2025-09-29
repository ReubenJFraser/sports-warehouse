<?php
// --- Canonicalize index.php?section=men&gender=men → /men (etc.) ---
// Only triggers when the *requested URL* was .../index.php, so no loops with /men.
$reqPath   = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'); // e.g. /sports-warehouse-home-page
$base      = $scriptDir === '/' ? '' : $scriptDir; // normalize

if (preg_match('#^' . preg_quote($base, '#') . '/index\.php$#i', $reqPath)) {
  // ↓↓↓ CHANGED: make the comparison case-insensitive
  $section = $_GET['section'] ?? null;
  $gender  = $_GET['gender']  ?? null;

  $secLower = is_string($section) ? strtolower($section) : '';
  $genLower = is_string($gender)  ? strtolower($gender)  : '';
  $valid    = ['men','women','kids'];

  if ($secLower && $secLower === $genLower && in_array($secLower, $valid, true)) {
    // Preserve any *other* query params, drop section/gender (they’re encoded in the path)
    $others = $_GET;
    unset($others['section'], $others['gender']);
    // RFC3986 encoding avoids spaces becoming '+'
    $qs   = http_build_query($others, '', '&', PHP_QUERY_RFC3986);
    $dest = ($base ?: '') . '/' . $secLower . ($qs ? ('?' . $qs) : '');

    header('Location: ' . $dest, true, 302);
    exit;
  }
}

// Safe to load the rest of your app after the redirect guard.
require __DIR__ . '/db.php';
require_once __DIR__ . '/inc/color-groups.php'; // <-- NEW: shared color group helpers

// index.php — unified homepage + filterable catalog (uses existing hero/config files)
//
// Behavior:
// - Homepage: shows hero + Featured Products.
// - Catalog mode: triggered by any of these GET params (or section=catalog):
//     brand, gender, age_group, size_type, q, categoryID (+ colors & multi)
//   Renders product cards via inc/product-grid.php using chosen_image.
//
// Deep links you can use in existing UI (brand logos/menu/footer):
//   index.php?brand=Adidas
//   index.php?brand=Adidas&gender=women
//   index.php?categoryID=7
//   index.php?q=tee

// ---------------------------
// Router / flags
// ---------------------------

// --- Debug toggle (URL: ?SW_DEBUG=1) ---
// Back-compat: ?debug=1 still works but maps to SW_DEBUG internally.
$SW_DEBUG = (
  (isset($_GET['SW_DEBUG']) && $_GET['SW_DEBUG'] === '1') ||
  (isset($_GET['debug'])    && $_GET['debug']    === '1')
);

// --- Minimal logger ---
if (!function_exists('sw_log')) {
  function sw_log($msg) {
    static $fp = null;
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    $file = $logDir . '/sw-debug.log';
    if ($fp === null) { $fp = @fopen($file, 'ab'); }
    if ($fp) {
      $ts = date('Y-m-d H:i:s');
      if (!is_string($msg)) { $msg = print_r($msg, true); }
      fwrite($fp, "[$ts] $msg\n");
    } else {
      error_log(is_string($msg) ? $msg : print_r($msg, true));
    }
  }
}

// Back-compat alias: remove after migrating all references
$DEBUG = $SW_DEBUG;

// Breadcrumb to confirm logger boots
if ($SW_DEBUG) sw_log('bootstrap OK (index.php)');

$section    = $_GET['section'] ?? 'homepage';

// --- TEMP: disable videos sitewide while we focus on product cards ---
$VIDEOS_ENABLED = isset($_GET['videos'])
  ? in_array(strtolower($_GET['videos']), ['1','on','true','yes'], true)
  : false; // default OFF for now

// Catalog filters (links will set these)
$brand      = isset($_GET['brand'])      ? trim((string)$_GET['brand'])      : '';
$gender     = isset($_GET['gender'])     ? trim((string)$_GET['gender'])     : '';
$age_group  = isset($_GET['age_group'])  ? trim((string)$_GET['age_group'])  : '';
$size_type  = isset($_GET['size_type'])  ? trim((string)$_GET['size_type'])  : '';
$q          = isset($_GET['q'])          ? trim((string)$_GET['q'])          : '';
$sort       = isset($_GET['sort'])       ? trim((string)$_GET['sort'])       : 'relevance'; // optional
$categoryID = filter_input(INPUT_GET, 'categoryID', FILTER_VALIDATE_INT) ?: null;

// Defensive normalization for gender (+ support pretty /men|/women|/kids even if rewrite missed gender)
$validGenders = ['men','women','kids'];
$gender = strtolower($gender);
$sectionLower = strtolower($section);

// If section itself is a gender and gender param is empty/invalid, inherit it
if (in_array($sectionLower, $validGenders, true) && !in_array($gender, $validGenders, true)) {
  $gender = $sectionLower;
}
// If gender provided but invalid, drop it
if ($gender !== '' && !in_array($gender, $validGenders, true)) {
  $gender = '';
}

/* Color filters */
$colors = array_values(array_filter(array_map(
  fn($c) => preg_replace('/[^a-z]/', '', strtolower((string)$c)),
  (array)($_GET['color'] ?? [])
)));
$multiOnly = isset($_GET['multi']) && in_array(strtolower((string)$_GET['multi']), ['1','true','yes','on'], true);

/* NEW: Color group (dark|light|color) */
$group = '';
if (isset($_GET['group'])) {
  $group = preg_replace('/[^a-z]/','', strtolower((string)$_GET['group']));
  $validGroups = array_keys(sw_color_groups());
  if (!in_array($group, $validGroups, true)) $group = '';
}

// hero.php expects $page
$page = $_GET['section'] ?? 'homepage';

// Normalize brand → $page key for hero/config (e.g., "Under Armour" => "underarmour")
function normalize_key(string $s): string {
  return strtolower(preg_replace('/[^a-z0-9]+/', '', $s));
}
if ($brand !== '') {
  $page = normalize_key($brand);
}

// Enter catalog mode if any filter is present, section=catalog, or section is a gender
$isCatalog = ($sectionLower === 'catalog')
          || in_array($sectionLower, $validGenders, true)
          || ($brand !== '' || $gender !== '' || $age_group !== '' || $size_type !== '' || $q !== '' || $categoryID
              || !empty($colors) || !empty($group) || $multiOnly);

// ---------------------------
// Pagination numbers (used by catalog query)
// ---------------------------
$pageNum  = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 48;
$offset   = ($pageNum - 1) * $pageSize;

// Optional: initial debug snapshot (logs only; do not echo here)
if ($SW_DEBUG) {
  error_log('[SW DEBUG] Incoming filters snapshot: ' . json_encode([
    'section'    => $section,
    'gender'     => $gender,
    'brand'      => $brand,
    'age_group'  => $age_group,
    'size_type'  => $size_type,
    'q'          => $q,
    'categoryID' => $categoryID,
    'group'      => $group,     // <-- NEW
    'colors'     => $colors,
    'multiOnly'  => $multiOnly,
    'isCatalog'  => $isCatalog,
  ]));
}

// Run the unified catalog query (populates $total, $items, $catalogQueryRan)
require __DIR__ . '/inc/catalog-query.php';

// NOTE: DO NOT echo or include the grid here; render later inside the template.

// ---------------------------
// Site config (hero, etc.)
// ---------------------------

// 2) Load your slides/videos config
$config = include __DIR__ . '/inc/site-config.php';

// If the computed $page has no config, fall back to homepage for hero
if (!isset($config[$page])) {
  $page = 'homepage';
}

// (compat) inc/hero.php uses $db->query(...); alias it to $pdo
$db = $pdo;

// ---------------------------
// Optional: resolve category name for UI (breadcrumb/title)
// ---------------------------
$categoryNameForUI = '';
if ($categoryID) {
  $stmtCat = $pdo->prepare('SELECT categoryName FROM category WHERE categoryId = :cid');
  $stmtCat->execute([':cid' => $categoryID]);
  $categoryNameForUI = (string)($stmtCat->fetchColumn() ?: '');
}

// Load optional helpers used by product-grid (data-images, legacy primary image)
require_once __DIR__ . '/inc/cards/utils.php';

// ---------------------------
// Page title
// ---------------------------
$titleParts = [];
if ($brand)               $titleParts[] = $brand;
if ($gender)              $titleParts[] = ucfirst($gender);
if ($age_group)           $titleParts[] = ucfirst($age_group);
if ($size_type)           $titleParts[] = ucfirst($size_type);
if ($categoryNameForUI)   $titleParts[] = $categoryNameForUI;
if ($q)                   $titleParts[] = "“$q”";
$pageTitle = $isCatalog
  ? (($titleParts ? implode(' • ', $titleParts).' | ' : '').'Browse Products | Sports Warehouse')
  : 'Sports Warehouse';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/inc/head.php'; ?>
</head>

<?php
  // layout switch (default flex; honor ?layout=grid)
  $layoutParam = $_GET['layout'] ?? '';
  $initialLayoutClass = ($layoutParam === 'grid') ? 'cards-grid' : 'cards-flex';
?>
<body class="<?= htmlspecialchars($initialLayoutClass, ENT_QUOTES, 'UTF-8') ?>">

  <?php include __DIR__ . '/inc/header.php'; ?>
  <?php include __DIR__ . '/inc/hero.php'; ?>

  <main class="site-container">
    <?php if ($isCatalog): ?>
      <!-- Filter UI: trigger + bottom sheet -->
      <?php include __DIR__ . '/inc/filter-ui.php'; ?>
      <?php include __DIR__ . '/inc/filters/color-facets.php'; ?>

      <nav aria-label="Breadcrumb">
        <a href="index.php">Home</a> &raquo;
        <span>Browse</span>
        <?php if ($brand): ?>
          &raquo; <span><?= htmlspecialchars($brand) ?></span>
        <?php endif; ?>
        <?php if ($categoryNameForUI): ?>
          &raquo; <span><?= htmlspecialchars($categoryNameForUI) ?></span>
        <?php endif; ?>
      </nav>

      <!-- Clear / active filters -->
      <div class="catalog-controls" style="display:flex;gap:.5rem;align-items:center;margin:.75rem 0 1rem">
        <?php
          $clearUrl = 'index.php?section=catalog';

          // Helper to drop a single query key and rebuild the URL
          if (!function_exists('sw_url_without')) {
            function sw_url_without($key) {
              $q = $_GET;
              unset($q[$key]);
              return 'index.php' . (empty($q) ? '' : ('?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986)));
            }
          }

          // Helper to drop a single value from a multi-value query param (e.g. color[]=red)
          if (!function_exists('sw_url_without_value')) {
            function sw_url_without_value(string $key, string $val) {
              $q = $_GET;
              $arr = (array)($q[$key] ?? []);
              $arr = array_values(array_filter($arr, fn($v) => strtolower((string)$v) !== strtolower((string)$val)));
              if (empty($arr)) {
                unset($q[$key]);
              } else {
                $q[$key] = $arr;
              }
              return 'index.php' . (empty($q) ? '' : ('?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986)));
            }
          }
        ?>

        <?php if ($gender):     ?><a class="chip" href="<?= htmlspecialchars(sw_url_without('gender')) ?>">Gender: <?= htmlspecialchars($gender) ?> ✕</a><?php endif; ?>
        <?php if ($brand):      ?><a class="chip" href="<?= htmlspecialchars(sw_url_without('brand')) ?>">Brand: <?= htmlspecialchars($brand) ?> ✕</a><?php endif; ?>
        <?php if ($age_group):  ?><a class="chip" href="<?= htmlspecialchars(sw_url_without('age_group')) ?>">Age: <?= htmlspecialchars($age_group) ?> ✕</a><?php endif; ?>
        <?php if ($size_type):  ?><a class="chip" href="<?= htmlspecialchars(sw_url_without('size_type')) ?>">Type: <?= htmlspecialchars($size_type) ?> ✕</a><?php endif; ?>
        <?php if ($q):          ?><a class="chip" href="<?= htmlspecialchars(sw_url_without('q')) ?>">Search: “<?= htmlspecialchars($q) ?>” ✕</a><?php endif; ?>
        <?php if ($categoryID): ?><a class="chip" href="<?= htmlspecialchars(sw_url_without('categoryID')) ?>">Category ✕</a><?php endif; ?>

        <?php if (!empty($group)): ?>
          <a class="chip" href="<?= htmlspecialchars(sw_url_without('group')) ?>">
            <?= htmlspecialchars(sw_group_label($group)) ?> ✕
          </a>
        <?php endif; ?>

        <?php foreach ($colors as $c): ?>
          <a class="chip" href="<?= htmlspecialchars(sw_url_without_value('color', $c)) ?>">
            Color: <?= htmlspecialchars(ucfirst($c)) ?> ✕
          </a>
        <?php endforeach; ?>

        <?php if ($multiOnly): ?>
          <a class="chip" href="<?= htmlspecialchars(sw_url_without('multi')) ?>">Multi-color ✕</a>
        <?php endif; ?>

        <?php if ($gender || $brand || $age_group || $size_type || $q || $categoryID || !empty($group) || !empty($colors) || $multiOnly || $sectionLower !== 'catalog'): ?>
          <a class="btn-reset" href="<?= htmlspecialchars($clearUrl) ?>">Clear all</a>
        <?php endif; ?>
      </div>
      <!-- /Clear / active filters -->

      <!-- Cards -->
      <?php include __DIR__ . '/inc/cards/product-grid.php'; ?>

      <!-- Pager -->
      <?php if ($total > $pageSize):
        $totalPages = (int)ceil($total / $pageSize);
        $qs = $_GET; // keep existing filters while paging
      ?>
        <nav class="pager" aria-label="Pagination" style="display:flex;gap:10px;justify-content:center;margin:18px 0 8px;">
          <?php if ($pageNum > 1):
            $qs['page'] = $pageNum - 1;
            // ↓↓↓ CHANGED: escape href with RFC3986 query
            $prevHref = 'index.php?' . http_build_query($qs, '', '&', PHP_QUERY_RFC3986);
          ?>
            <a href="<?= htmlspecialchars($prevHref, ENT_QUOTES, 'UTF-8') ?>">&laquo; Prev</a>
          <?php else: ?>
            <span class="disabled">&laquo; Prev</span>
          <?php endif; ?>

          <span class="current">Page <?= $pageNum ?> / <?= $totalPages ?></span>

          <?php if ($pageNum < $totalPages):
            $qs['page'] = $pageNum + 1;
            // ↓↓↓ CHANGED: escape href with RFC3986 query
            $nextHref = 'index.php?' . http_build_query($qs, '', '&', PHP_QUERY_RFC3986);
          ?>
            <a href="<?= htmlspecialchars($nextHref, ENT_QUOTES, 'UTF-8') ?>">Next &raquo;</a>
          <?php else: ?>
            <span class="disabled">Next &raquo;</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

    <?php else: ?>
      <!-- Homepage: Featured Products -->
      <section class="featured-products">
        <div class="site-container">
          <div class="pill pill--full">
            <h2>Featured Products</h2>
          </div>

          <?php
            // Fetch featured items for the homepage
            $stmt = $pdo->query("
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
                i.categoryName,
                i.orientation,
                i.altText,
                i.thumbnails_json,
                i.chosen_image,
                i.chosen_ratio,
                o.orientation    AS o_orientation,
                o.ratio          AS o_ratio,
                o.image_basename AS o_image
              FROM item AS i
              LEFT JOIN item_orientation_override o
                   ON o.itemId = i.itemId
              WHERE i.featured = 1
              ORDER BY i.itemId
            ");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>

          <?php include __DIR__ . '/inc/cards/product-grid.php'; ?>
        </div>
      </section>
    <?php endif; ?>
  </main> <!-- /.site-container -->

  <?php include __DIR__ . '/inc/footer.php'; ?>

  <!-- ================= SCRIPTS AT THE BOTTOM ================= -->
  <!-- 1) Justified-Layout grid algorithm (Flickr) -->
  <script src="https://unpkg.com/justified-layout/dist/justified-layout.min.js"></script>
  <!-- 2) Core UI & orientation tracking -->
  <script type="module" src="js/site-ui.js"></script>
  <!-- 3) Lazy-load off‐screen images -->
  <script type="module" src="js/image-lazy.js"></script>
  <!-- 4) Dark-mode toggle -->
  <script type="module" src="js/dark-mode.js"></script>
  <!-- 5) open/close/apply filter -->
  <script type="module" src="js/filter-ui.js"></script>

  <?php if (!empty($SW_DEBUG)): ?>
  <!-- Dev-only layout toggle -->
  <button id="layoutToggle" type="button" class="dev-layout-toggle" aria-pressed="false" title="Toggle Card Layout (Flex/Grid)">
    Grid: Off
  </button>

  <script type="module">
    (function () {
      const key   = 'swLayoutMode';               // 'grid' | 'flex'
      const body  = document.body;
      const btn   = document.getElementById('layoutToggle');
      const qs    = new URLSearchParams(window.location.search);

      function isGrid(){ return body.classList.contains('cards-grid'); }
      function apply(mode){
        const grid = (mode === 'grid');
        body.classList.toggle('cards-grid', grid);
        body.classList.toggle('cards-flex', !grid);
        if (btn) {
          btn.setAttribute('aria-pressed', String(grid));
          btn.textContent = 'Grid: ' + (grid ? 'On' : 'Off');
        }
      }

      // URL (?layout) > localStorage > current body class
      let mode = qs.get('layout');
      if (mode !== 'grid' && mode !== 'flex') {
        mode = localStorage.getItem(key) || (isGrid() ? 'grid' : 'flex');
      }
      apply(mode);
      if (qs.has('layout')) localStorage.setItem(key, mode);

      if (btn) {
        btn.addEventListener('click', () => {
          mode = isGrid() ? 'flex' : 'grid';
          localStorage.setItem(key, mode);
          apply(mode);
        });
      }
    })();
  </script>
  <?php endif; ?>

</body>
</html>






<?php /* deploy 2025-09-29T21:33:51 */ ?>

<?php /* deploy 2025-09-29T21:48:40 */ ?>

<!-- deploy 2025-09-29T22:16:38 -->

<!-- gha-deploy 2025-09-29T23:38:01 -->
