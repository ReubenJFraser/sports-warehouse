<?php
require __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
if ($q === '') {
  header('Location: index.php');
  exit;
}

$sql = "
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
    i.orientation,
    i.altText,
    i.thumbnails_json,
    i.chosen_image,
    i.chosen_ratio,
    o.orientation    AS o_orientation,
    o.ratio          AS o_ratio,
    o.image_basename AS o_image
  FROM item AS i
  LEFT JOIN category AS c
    ON i.categoryId = c.categoryId
  LEFT JOIN item_orientation_override o
    ON o.itemId = i.itemId
  WHERE i.itemName LIKE :q
  ORDER BY i.itemName
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => "%{$q}%"]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Search “' . $q . '” | Sports Warehouse';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/inc/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/inc/header.php'; ?>

  <main class="site-container">
    <div class="pill pill--full">
      <h2>Search results for “<?= htmlspecialchars($q) ?>”</h2>
    </div>

    <?php include __DIR__ . '/inc/cards/product-grid.php'; ?>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>



