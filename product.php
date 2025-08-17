<?php
require __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  exit('Product not found');
}

$sql = "
  SELECT
    i.itemId,
    i.itemName,
    i.photo,
    i.price,
    i.salePrice,
    i.description,
    c.categoryName
  FROM item AS i
  JOIN category AS c ON i.categoryId = c.categoryId
  WHERE i.itemId = :id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (! $item) {
  http_response_code(404);
  exit('Product not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php // copy your <head>…</head> from index.php here ?>
  <title><?= htmlspecialchars($item['itemName']) ?> | Sports Warehouse</title>
</head>
<body>
  <?php include __DIR__ . '/inc/header.php'; ?>

  <main class="site-container">
    <nav aria-label="Breadcrumb">
      <a href="index.php">Home</a> &raquo;
      <a href="category.php?category=<?= urlencode($item['categoryName']) ?>">
        <?= htmlspecialchars($item['categoryName']) ?>
      </a> &raquo;
      <span><?= htmlspecialchars($item['itemName']) ?></span>
    </nav>

    <div class="product-detail">
      <div class="product-detail__image">
        <img
          src="images/products/<?= rawurlencode($item['photo']) ?>"
          alt="<?= htmlspecialchars($item['itemName']) ?>"
        >
      </div>
      <div class="product-detail__info">
        <h1><?= htmlspecialchars($item['itemName']) ?></h1>
        <p class="product-pricing">
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
        </p>
        <div class="product-description">
          <?= nl2br(htmlspecialchars($item['description'])) ?>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

