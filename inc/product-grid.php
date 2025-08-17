<?php
// inc/product-grid.php
// expects: $items (array of item rows)

if (empty($items)) {
  echo '<p class="no-results">No products found.</p>';
  return;
}
?>
<div class="featured-products-grid">
  <?php foreach ($items as $item): ?>
    <a
      href="product.php?id=<?= htmlspecialchars($item['itemId']) ?>"
      class="product-card"
      data-orientation="<?= htmlspecialchars($item['orientation']) ?>"
      title="<?= htmlspecialchars($item['itemName']) ?>"
    >
      <img
        class="lazy"
        data-src="<?= htmlspecialchars($item['images']) ?>"
        alt="<?= htmlspecialchars($item['itemName']) ?>"
        width="300" height="400"
      >
      <noscript>
        <img
          src="<?= htmlspecialchars($item['images']) ?>"
          alt="<?= htmlspecialchars($item['itemName']) ?>"
          width="300" height="400"
        >
      </noscript>

      <!-- wrap pricing + name together -->
      <div class="product-info">
        <div class="product-pricing">
          <?php if ($item['salePrice'] !== null): ?>
            <span class="price-original">
              $<?= number_format($item['price'], 2) ?>
            </span>
            <span class="price-current">
              $<?= number_format($item['salePrice'], 2) ?>
            </span>
          <?php else: ?>
            <span class="price-current">
              $<?= number_format($item['price'], 2) ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="product-name">
          <?= htmlspecialchars($item['itemName']) ?>
        </div>
      </div>
      <!-- /product-info -->

    </a>
  <?php endforeach; ?>
</div>



