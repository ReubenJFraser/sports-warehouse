<?php
// category.php

require_once __DIR__ . '/db.php';

// Allowlist of category names (as used in your DB)
$allowed = ['Shoes','Helmets','Pants','Tops','Balls','Equipment','Training Gear'];

// Read & validate input
$category = $_GET['category'] ?? '';
if (!in_array($category, $allowed, true)) {
  http_response_code(404);
  exit('Category not found.');
}

// Grid-friendly SELECT (matches fields expected by inc/cards/product-grid.php)
$sql = "
  SELECT
    i.itemId, i.itemName, i.brand, i.price, i.salePrice, i.description,
    i.subcategory, i.parentCategory, i.categoryId,
    COALESCE(c.categoryName, i.categoryName) AS categoryName,
    i.orientation, i.altText, i.thumbnails_json, i.chosen_image, i.chosen_ratio,
    o.orientation AS o_orientation, o.ratio AS o_ratio, o.image_basename AS o_image
  FROM item AS i
  JOIN category AS c ON i.categoryId = c.categoryId
  LEFT JOIN item_orientation_override o ON o.itemId = i.itemId
  WHERE c.categoryName = :category
  ORDER BY i.itemId
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':category' => $category]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optional: total for grids that check it
$total = count($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?= htmlspecialchars($category) ?> Products | Sports Warehouse</title>
  <?php require __DIR__ . '/inc/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/inc/header.php'; ?>

  <main class="site-container">
    <nav aria-label="Breadcrumb" style="margin:.5rem 0 1rem;">
      <a href="index.php">Home</a> &raquo;
      <span><?= htmlspecialchars($category) ?></span>
    </nav>

    <div class="pill pill--full">
      <h1><?= htmlspecialchars($category) ?></h1>
    </div>

    <?php
      // Shared card renderer (expects $items in scope)
      require_once __DIR__ . '/inc/cards/utils.php';
      include __DIR__ . '/inc/cards/product-grid.php';
    ?>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>


