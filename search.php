<?php
require __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
if ($q === '') {
  // Optionally, redirect back or show a message
  header('Location: index.php');
  exit;
}

$sql = "
  SELECT 
    i.itemId, i.itemName, i.photo, i.price, i.salePrice
  FROM item AS i
  WHERE i.itemName LIKE :q
  ORDER BY i.itemName
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['q' => "%{$q}%"]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php // copy your <head>…</head> from index.php here ?>
</head>
<body>
  <?php include __DIR__ . '/inc/header.php'; ?>

  <main class="site-container">
    <div class="pill pill--full">
      <h2>Search results for “<?= htmlspecialchars($q) ?>”</h2>
    </div>

    <?php 
      // reuse your grid template
      $items = $items; 
      include __DIR__ . '/inc/product-grid.php'; 
    ?>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>


