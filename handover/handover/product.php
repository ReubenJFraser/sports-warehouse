<?php
// products.php — Product detail page (updated to use chosen_image / thumbnails_json)
// - Uses `chosen_image` (from DB) as the primary image
// - Falls back to first path in `thumbnails_json` (semicolon-separated) if needed
// - Builds a simple gallery from `thumbnails_json`
// - Keeps existing category breadcrumb structure
// - NEW: reads ?categoryID=... and uses it in the breadcrumb link

require __DIR__ . '/db.php';

// 1) Validate & read id
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  exit('Product not found');
}

// 1a) NEW: read optional categoryID for context (e.g., when user navigated from a category page)
$categoryIdParam = isset($_GET['categoryID']) ? (int)$_GET['categoryID'] : 0;

// 2) Fetch product (no 'photo' column anymore; we use chosen_image / thumbnails_json)
$sql = "
  SELECT
    i.itemId,
    i.itemName,
    i.brand,
    i.price,
    i.salePrice,
    i.description,
    i.chosen_image,
    i.chosen_ratio,
    i.thumbnails_json,
    c.categoryId   AS categoryId,   -- NEW: include DB category id
    c.categoryName
  FROM item AS i
  JOIN category AS c ON i.categoryId = c.categoryId
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

// Decide which category id to use for links (prefer query param if present)
$categoryIdForLinks = $categoryIdParam > 0 ? $categoryIdParam : (int)($item['categoryId'] ?? 0);

// Build the breadcrumb category URL: prefer ID, else fall back to name (for legacy links)
$categoryHref = $categoryIdForLinks > 0
  ? 'category.php?categoryID=' . $categoryIdForLinks
  : 'category.php?category=' . urlencode($item['categoryName']);

// 3) Build image list
$images = [];

// Prefer chosen_image if present
if (!empty($item['chosen_image'])) {
  $images[] = $item['chosen_image'];
}

// Parse thumbnails_json if present (your data is a semicolon-separated list of paths)
if (!empty($item['thumbnails_json'])) {
  // Split by ';' and normalize
  $parts = array_map('trim', explode(';', $item['thumbnails_json']));
  foreach ($parts as $p) {
    if ($p !== '' && !in_array($p, $images, true)) {
      $images[] = $p;
    }
  }
}

// If still no images, use a placeholder
if (empty($images)) {
  $images[] = '/images/placeholder.png';
}

// Main image is the first one
$mainImage = $images[0];

// 4) Pricing helpers
$hasSale = isset($item['salePrice']) && $item['salePrice'] !== '' && $item['salePrice'] !== null;
$priceCurrent = $hasSale ? (float)$item['salePrice'] : (float)$item['price'];
$priceOriginal = (float)$item['price'];

// 5) Title helper
$pageTitle = $item['itemName'] . ' | Sports Warehouse';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/inc/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/inc/header.php'; ?>

  <main class="site-container">
    <nav aria-label="Breadcrumb">
      <a href="index.php">Home</a> &raquo;
      <a href="<?= htmlspecialchars($categoryHref, ENT_QUOTES) ?>">
        <?= htmlspecialchars($item['categoryName']) ?>
      </a> &raquo;
      <span><?= htmlspecialchars($item['itemName']) ?></span>
    </nav>

    <div class="product-detail">
      <!-- Left: main image + thumbs -->
      <div class="product-detail__image">
        <div class="image-frame" id="mainImageFrame">
          <img
            id="mainImage"
            src="<?= htmlspecialchars($mainImage) ?>"
            alt="<?= htmlspecialchars($item['itemName']) ?>"
            loading="eager"
          >
        </div>

        <?php if (count($images) > 1): ?>
          <div class="thumbs" aria-label="Product image thumbnails">
            <?php foreach ($images as $idx => $imgPath): ?>
              <button class="thumb" type="button" data-full="<?= htmlspecialchars($imgPath) ?>" aria-label="View image <?= $idx + 1 ?>">
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="" loading="lazy">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right: info -->
      <div class="product-detail__info">
        <h1><?= htmlspecialchars($item['itemName']) ?></h1>
        <?php if (!empty($item['brand'])): ?>
          <div class="product-brand"><?= htmlspecialchars($item['brand']) ?></div>
        <?php endif; ?>

        <p class="product-pricing">
          <?php if ($hasSale): ?>
            <span class="price-current">
              $<?= number_format($priceCurrent, 2) ?>
            </span>
            <span class="price-original">
              $<?= number_format($priceOriginal, 2) ?>
            </span>
          <?php else: ?>
            <span class="price-current">
              $<?= number_format($priceCurrent, 2) ?>
            </span>
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
    // Simple thumbnail → main image swapper (no framework needed)
    document.addEventListener('DOMContentLoaded', function () {
      var main = document.getElementById('mainImage');
      if (!main) return;

      document.querySelectorAll('.thumbs .thumb').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var full = this.getAttribute('data-full');
          if (full) {
            main.setAttribute('src', full);
          }
        });
      });
    });
  </script>
</body>
</html>



