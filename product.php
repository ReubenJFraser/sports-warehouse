<?php
// product.php — Product detail page (hero-image version)
//
// Uses the precomputed hero fields:
//   - hero_image (primary)
//   - hero_ratio
//   - hero_orientation
//   - hero_score
//
// Additional gallery images come from thumbnails_json (semicolon list)
//
// Compatible with PhotoSwipe, lazy loading, horizontal centering, face-focus.

require __DIR__ . '/db.php';

/* -------------------------------------------------------
   1) Read product ID
--------------------------------------------------------*/
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  exit('Product not found');
}

/* Optional category context when arriving from category.php */
$categoryIdParam = isset($_GET['categoryID']) ? (int)$_GET['categoryID'] : 0;

/* -------------------------------------------------------
   2) Fetch product with hero fields
--------------------------------------------------------*/
$sql = "
  SELECT
    i.itemId,
    i.itemName,
    i.brand,
    i.price,
    i.salePrice,
    i.description,

    /* HERO FIELDS */
    i.hero_image,
    i.hero_ratio,
    i.hero_orientation,
    i.hero_score,

    /* Gallery extras */
    i.thumbnails_json,

    /* Category info */
    c.categoryId AS categoryId,
    c.categoryName
  FROM item i
  JOIN category c ON c.categoryId = i.categoryId
  WHERE i.itemId = :id
  LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
  http_response_code(404);
  exit('Product not found');
}

/* -------------------------------------------------------
   3) Determine category link
--------------------------------------------------------*/
$categoryIdForLinks = $categoryIdParam > 0
  ? $categoryIdParam
  : (int)($item['categoryId'] ?? 0);

$categoryHref = $categoryIdForLinks > 0
  ? 'category.php?categoryID=' . $categoryIdForLinks
  : 'category.php?category=' . urlencode($item['categoryName']);

/* -------------------------------------------------------
   4) Build hero-first gallery: hero_image + thumbnails_json
--------------------------------------------------------*/
$gallery = [];

/* Hero image always first */
if (!empty($item['hero_image'])) {
  $gallery[] = $item['hero_image'];
}

/* Add thumbnails */
if (!empty($item['thumbnails_json'])) {
  $parts = array_filter(array_map('trim', explode(';', $item['thumbnails_json'])));
  foreach ($parts as $p) {
    if ($p !== '' && !in_array($p, $gallery, true)) {
      // Normalise: if missing prefix, add images/
      if (!preg_match('~^images/~', $p)) {
        $p = 'images/' . $p;
      }
      $gallery[] = $p;
    }
  }
}

/* Fallback placeholder */
if (empty($gallery)) {
  $gallery[] = '/images/placeholders/product_missing.svg';
}

$mainImage = $gallery[0];

/* -------------------------------------------------------
   5) Pricing helpers
--------------------------------------------------------*/
$hasSale = ($item['salePrice'] !== null && $item['salePrice'] !== '');
$priceCurrent = $hasSale ? (float)$item['salePrice'] : (float)$item['price'];
$priceOriginal = (float)$item['price'];

/* -------------------------------------------------------
   6) Orientation class for styling
--------------------------------------------------------*/
$orientation = strtolower(trim((string)$item['hero_orientation']));
if (!in_array($orientation, ['portrait','landscape','square'], true)) {
  $orientation = 'portrait';
}
$orientationClass = 'is-' . $orientation;

/* -------------------------------------------------------
   7) PhotoSwipe display ratio (safe for PHP 8+)
--------------------------------------------------------*/
$ratio = $item['hero_ratio'] ?? null;

if (!is_numeric($ratio) || $ratio <= 0) {
  $ratio = match ($orientation) {
    'landscape' => 1.3,
    'square'    => 1.0,
    default     => 0.75,
  };
}

$pswpW = 1600;
$pswpH = (int)round($pswpW / max(0.05, $ratio));

/* -------------------------------------------------------
   8) Page title
--------------------------------------------------------*/
$pageTitle = $item['itemName'] . ' | Sports Warehouse';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/inc/head.php'; ?>
  <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>
<?php include __DIR__ . '/inc/header.php'; ?>

<main class="site-container">

  <!-- Breadcrumb -->
  <nav aria-label="Breadcrumb">
    <a href="index.php">Home</a> &raquo;
    <a href="<?= htmlspecialchars($categoryHref, ENT_QUOTES) ?>">
      <?= htmlspecialchars($item['categoryName']) ?>
    </a> &raquo;
    <span><?= htmlspecialchars($item['itemName']) ?></span>
  </nav>

  <div class="product-detail <?= $orientationClass ?>">

    <!-- ------------------ LEFT: Media ------------------ -->
    <div class="product-detail__image">

      <!-- Main image (PhotoSwipe trigger) -->
      <div class="image-frame" id="mainImageFrame">
        <a
          href="<?= htmlspecialchars($mainImage) ?>"
          data-pswp
          data-pswp-width="<?= $pswpW ?>"
          data-pswp-height="<?= $pswpH ?>"
        >
          <img
            id="mainImage"
            src="<?= htmlspecialchars($mainImage) ?>"
            alt="<?= htmlspecialchars($item['itemName']) ?>"
            loading="eager"
            class="main-hero-image"
          >
        </a>
      </div>

      <!-- Thumbnails -->
      <?php if (count($gallery) > 1): ?>
        <div class="thumbs" aria-label="Product image thumbnails">
          <?php foreach ($gallery as $idx => $imgPath): ?>
            <button
              class="thumb"
              type="button"
              data-full="<?= htmlspecialchars($imgPath) ?>"
              aria-label="View image <?= $idx + 1 ?>"
            >
              <img src="<?= htmlspecialchars($imgPath) ?>" alt="" loading="lazy">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Hidden PhotoSwipe anchors for the gallery -->
      <?php foreach ($gallery as $g): ?>
        <a
          href="<?= htmlspecialchars($g) ?>"
          data-pswp
          data-pswp-width="<?= $pswpW ?>"
          data-pswp-height="<?= $pswpH ?>"
          class="pswp-hidden"
          tabindex="-1"
          aria-hidden="true"
          style="display:none"
        ></a>
      <?php endforeach; ?>

    </div>

    <!-- ------------------ RIGHT: Info ------------------ -->
    <div class="product-detail__info">
      <h1><?= htmlspecialchars($item['itemName']) ?></h1>

      <?php if (!empty($item['brand'])): ?>
        <div class="product-brand"><?= htmlspecialchars($item['brand']) ?></div>
      <?php endif; ?>

      <p class="product-pricing">
        <?php if ($hasSale): ?>
          <span class="price-current">$<?= number_format($priceCurrent, 2) ?></span>
          <span class="price-original">$<?= number_format($priceOriginal, 2) ?></span>
        <?php else: ?>
          <span class="price-current">$<?= number_format($priceCurrent, 2) ?></span>
        <?php endif; ?>
      </p>

      <div class="product-description">
        <?= nl2br(htmlspecialchars($item['description'] ?? '')) ?>
      </div>
    </div>

  </div>
</main>

<?php include __DIR__ . '/inc/footer.php'; ?>

<script>
/* Simple thumbnail → main image swapper */
document.addEventListener('DOMContentLoaded', function () {
  var main = document.getElementById('mainImage');
  if (!main) return;

  document.querySelectorAll('.thumbs .thumb').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var full = this.getAttribute('data-full');
      if (full) {
        main.setAttribute('src', full);
        /* Update PhotoSwipe anchor too */
        const anchor = document.querySelector('#mainImageFrame a[data-pswp]');
        if (anchor) anchor.setAttribute('href', full);
      }
    });
  });
});
</script>

</body>
</html>





