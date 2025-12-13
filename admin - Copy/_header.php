<?php

// -------------------------------------------------------------
// Page Header Component
// Provides: title, subtitle, and optional breadcrumbs
// -------------------------------------------------------------

function admin_page_header(string $title, string $subtitle = "", array $breadcrumbs = []) {
    ?>
    <div class="admin-page-header">

        <?php if (!empty($breadcrumbs)): ?>
            <div class="admin-breadcrumbs">
                <i class="fa-solid fa-house-chimney"></i>
                <a href="/admin/index.php">Admin</a>
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <span class="sep">›</span>
                    <span><?= htmlspecialchars($crumb) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h1><?= htmlspecialchars($title) ?></h1>

        <?php if ($subtitle): ?>
            <p class="admin-page-subtitle"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php
}
?>
