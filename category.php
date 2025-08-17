<?php
// category.php

// 1) pull in your existing PDO connection
require_once __DIR__ . '/db.php';

// 2) define which categories we allow
$allowed = ['Shoes','Helmets','Pants','Tops','Balls','Equipment','Training Gear'];

// 3) grab & validate the URL param
$category = $_GET['category'] ?? '';
if (! in_array($category, $allowed, true)) {
    // invalid → show 404 or custom message
    http_response_code(404);
    die('Category not found.');
}

// 4) prepare & execute your JOIN query
$sql = "
  SELECT
    i.itemId,
    i.itemName,
    i.photo,
    i.price,
    i.salePrice
  FROM item AS i
  JOIN category AS c
    ON i.categoryId = c.categoryId
  WHERE c.categoryName = :category
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['category' => $category]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) render the page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($category) ?> Products</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <?php include __DIR__ . '/inc/header.php'; ?>

  <main class="site-container">
    <h1><?= htmlspecialchars($category) ?></h1>
    <div class="products-grid">
      <?php foreach ($items as $item): ?>
        <a href="product.php?id=<?= $item['itemId'] ?>" class="product-card">
          <img src="images/products/<?= htmlspecialchars($item['photo']) ?>"
               alt="<?= htmlspecialchars($item['itemName']) ?>">
          <div class="product-pricing">
            <?php if ($item['salePrice'] !== null): ?>
              <span class="price-original">
                $<?= number_format($item['price'],2) ?>
              </span>
              <span class="price-current">
                $<?= number_format($item['salePrice'],2) ?>
              </span>
            <?php else: ?>
              <span class="price-current">
                $<?= number_format($item['price'],2) ?>
              </span>
            <?php endif; ?>
          </div>
          <div class="product-name">
            <?= htmlspecialchars($item['itemName']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>


