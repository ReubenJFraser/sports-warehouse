<?php
require __DIR__ . '/db.php';

// 1) Figure out which “page” we’re rendering
//    e.g. ?section=men  or ?section=adidas
$page = $_GET['section'] ?? 'homepage';

// 2) Load your slides/videos config
$config = include __DIR__ . '/inc/site-config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sports Warehouse</title>

  <!-- Web fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css"/>

  <!-- Base variables & resets -->
  <link rel="stylesheet" href="css/base.css">
  <!-- Header-specific styles -->
  <link rel="stylesheet" href="css/header.css">
  <!-- Everything else: search row, hero, footer, etc. -->
  <link rel="stylesheet" href="css/main.css">
  <!-- Components & overrides last -->
  <link rel="stylesheet" href="css/components/button.css">
  <link rel="stylesheet" href="css/components/pill.css">
  <link rel="stylesheet" href="css/components/card.css">
  <link rel="stylesheet" href="css/components/card-layout.css">
  <link rel="stylesheet" href="css/components/card-responsiveness.css">
  <link rel="stylesheet" href="css/components/md3-overrides.css">

  <!-- Favicons -->
  <link rel="icon" type="image/x-icon" href="images/logos/sports-warehouse-favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="images/logos/sports-warehouse-icon-SW-alternative_favicon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="images/logos/sports-warehouse-icon-SW-recommended_apple_size.png">

  <!-- Theme color -->
  <meta name="theme-color" content="#ff690c">
</head>

<body>

  <?php include __DIR__ . '/inc/header.php'; ?>
  <?php include __DIR__ . '/inc/hero.php'; ?>

  <main class="site-container">
  <?php
  // Pull featured items from the database
  $stmt = $pdo->query("
    SELECT
      i.itemId,
      i.itemName,
      i.images,
      i.price,
      i.salePrice,
      i.description,
      i.orientation,
      c.categoryName
    FROM item AS i
    JOIN category AS c
      ON i.categoryId = c.categoryId
    WHERE i.featured = 1
    ORDER BY i.itemId
  ");
  $featuredItems = $stmt->fetchAll();
  // ─── pick the best thumbnail for each item ─────────────────
  require __DIR__ . '/inc/card-utils.php';
  $featuredItems = pickBestThumbs($featuredItems);
  ?>

  <!-- Featured Products Section -->
  <section class="featured-products">
    <div class="site-container">

      <!-- Orange title bar (reusable .section-featured style) -->
      <div class="pill pill--full">
        <h2>Featured Products</h2>
      </div>

      <!-- shared grid include -->
      <?php 
        $items = $featuredItems;
      ?>
      <?php include __DIR__ . '/inc/product-grid.php'; ?>

    </div> <!-- /.site-container -->
  </section>
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
</body>
</html>


