<?php
define('SW_FRONTEND_VERSION', '2025-01-DEPLOY-' . substr(sha1(__FILE__), 0, 8));
header('X-SW-Frontend: ' . SW_FRONTEND_VERSION);

// ------------------------------------------------------------
// Debug bootstrap (early, safe)
// ------------------------------------------------------------
if (isset($_GET['sw_debug']) && $_GET['sw_debug'] !== '0') {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
  register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR], true)) {
      echo "<pre style='background:#fee;border:1px solid #f88;padding:12px'>
FATAL: {$e['message']}
File: {$e['file']} @ line {$e['line']}
</pre>";
    }
  });
}

// ------------------------------------------------------------
// Canonicalize index.php?section=men&gender=men → /men
// ------------------------------------------------------------
$reqPath   = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$base      = $scriptDir === '/' ? '' : $scriptDir;

if (preg_match('#^' . preg_quote($base, '#') . '/index\.php$#i', $reqPath)) {
  $section = $_GET['section'] ?? null;
  $gender  = $_GET['gender']  ?? null;

  $secLower = is_string($section) ? strtolower($section) : '';
  $genLower = is_string($gender)  ? strtolower($gender)  : '';
  $valid    = ['men','women','kids'];

  if ($secLower && $secLower === $genLower && in_array($secLower, $valid, true)) {
    $others = $_GET;
    unset($others['section'], $others['gender']);
    $qs   = http_build_query($others, '', '&', PHP_QUERY_RFC3986);
    $dest = ($base ?: '') . '/' . $secLower . ($qs ? ('?' . $qs) : '');
    header('Location: ' . $dest, true, 302);
    exit;
  }
}

// ------------------------------------------------------------
// Core includes
// ------------------------------------------------------------
require __DIR__ . '/db.php';
require_once __DIR__ . '/inc/color-groups.php';
require_once __DIR__ . '/inc/sort-normalize.php';

// ------------------------------------------------------------
// Debug + logging
// ------------------------------------------------------------
$SW_DEBUG = (
  (isset($_GET['SW_DEBUG']) && $_GET['SW_DEBUG'] === '1') ||
  (isset($_GET['debug'])    && $_GET['debug']    === '1')
);

if (!function_exists('sw_log')) {
  function sw_log($msg) {
    static $fp = null;
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    $file = $logDir . '/sw-debug.log';
    if ($fp === null) $fp = @fopen($file, 'ab');
    if ($fp) {
      fwrite($fp, '[' . date('Y-m-d H:i:s') . '] ' . print_r($msg, true) . "\n");
    }
  }
}

if ($SW_DEBUG) sw_log('bootstrap OK (index.php)');

// ------------------------------------------------------------
// Raw routing inputs
// ------------------------------------------------------------
$section    = $_GET['section'] ?? 'homepage';
$brand      = isset($_GET['brand'])      ? trim((string)$_GET['brand'])      : '';
$gender     = isset($_GET['gender'])     ? trim((string)$_GET['gender'])     : '';
$age_group  = isset($_GET['age_group'])  ? trim((string)$_GET['age_group'])  : '';
$size_type  = isset($_GET['size_type'])  ? trim((string)$_GET['size_type'])  : '';
$q          = isset($_GET['q'])          ? trim((string)$_GET['q'])          : '';
$sort       = sw_normalize_sort($_GET['sort'] ?? null);
$categoryID = filter_input(INPUT_GET, 'categoryID', FILTER_VALIDATE_INT) ?: null;

// ------------------------------------------------------------
// Canonical routing vocab (AUTHORITATIVE)
// ------------------------------------------------------------
$validGenders         = ['men','women','kids'];
$validCatalogSections = ['men','women','kids','plus_size'];
$validSections        = array_merge(['homepage','catalog'], $validCatalogSections);

// ------------------------------------------------------------
// Normalize + validate
// ------------------------------------------------------------
$sectionLower = strtolower($section);
$gender       = strtolower($gender);

// size_type — strict
if ($size_type !== '' && $size_type !== 'plus') {
  sw_log("Invalid size_type rejected: {$size_type}");
  $size_type = '';
}

// section — strict
if (!in_array($sectionLower, $validSections, true)) {
  sw_log("Invalid section rejected: {$sectionLower}");
  $sectionLower = 'homepage';
}

// gender inheritance (after section validation)
if (in_array($sectionLower, $validGenders, true) && !in_array($gender, $validGenders, true)) {
  $gender = $sectionLower;
}

// gender — strict
if ($gender !== '' && !in_array($gender, $validGenders, true)) {
  $gender = '';
}

// ------------------------------------------------------------
// Color filters
// ------------------------------------------------------------
$colors = array_values(array_filter(array_map(
  fn($c) => preg_replace('/[^a-z]/', '', strtolower((string)$c)),
  (array)($_GET['color'] ?? [])
)));

$multiOnly = isset($_GET['multi']) && in_array(strtolower((string)$_GET['multi']), ['1','true','yes','on'], true);

$group = '';
if (isset($_GET['group'])) {
  $group = preg_replace('/[^a-z]/','', strtolower((string)$_GET['group']));
  if (!in_array($group, array_keys(sw_color_groups()), true)) $group = '';
}

// ------------------------------------------------------------
// Hero / page key
// ------------------------------------------------------------
$page = $sectionLower;

function normalize_key(string $s): string {
  return strtolower(preg_replace('/[^a-z0-9]+/', '', $s));
}
if ($brand !== '') {
  $page = normalize_key($brand);
}

// ------------------------------------------------------------
// Catalog mode detection
// ------------------------------------------------------------
$isCatalog = (
  $sectionLower === 'catalog'
  || in_array($sectionLower, $validCatalogSections, true)
  || $brand !== ''
  || $gender !== ''
  || $age_group !== ''
  || $size_type !== ''
  || $q !== ''
  || $categoryID
  || !empty($colors)
  || !empty($group)
  || $multiOnly
);

// ------------------------------------------------------------
// Pagination
// ------------------------------------------------------------
$pageNum  = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 48;
$offset   = ($pageNum - 1) * $pageSize;

// ------------------------------------------------------------
// Debug snapshot
// ------------------------------------------------------------
if ($SW_DEBUG) {
  sw_log([
    'section'    => $sectionLower,
    'gender'     => $gender,
    'brand'      => $brand,
    'size_type'  => $size_type,
    'sort'       => $sort,
    'isCatalog'  => $isCatalog,
  ]);
}

// ------------------------------------------------------------
// Catalog query
// ------------------------------------------------------------
require __DIR__ . '/inc/catalog-query.php';

// ------------------------------------------------------------
// Site config / hero
// ------------------------------------------------------------
$config = include __DIR__ . '/inc/site-config.php';
if (!isset($config[$page])) $page = 'homepage';

$db = $pdo;

// ------------------------------------------------------------
// Category name
// ------------------------------------------------------------
$categoryNameForUI = '';
if ($categoryID) {
  $stmt = $pdo->prepare('SELECT categoryName FROM category WHERE categoryId = :cid');
  $stmt->execute([':cid' => $categoryID]);
  $categoryNameForUI = (string)($stmt->fetchColumn() ?: '');
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
require_once __DIR__ . '/inc/cards/utils.php';

// ------------------------------------------------------------
// Page title
// ------------------------------------------------------------
$titleParts = [];
if ($brand)             $titleParts[] = $brand;
if ($gender)            $titleParts[] = ucfirst($gender);
if ($size_type)         $titleParts[] = ucfirst($size_type);
if ($categoryNameForUI) $titleParts[] = $categoryNameForUI;
if ($q)                 $titleParts[] = "“$q”";

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
<?php include __DIR__ . '/inc/photoswipe-init.php'; ?>
</body>
</html>


