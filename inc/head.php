<?php
// inc/head.php
require_once __DIR__ . '/url.php';

// Allow page-specific title set as $pageTitle (fallback to site name)
$__title = $pageTitle ?? 'Sports Warehouse';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<base href="<?= htmlspecialchars(sw_base(), ENT_QUOTES) ?>">
<title><?= htmlspecialchars($__title) ?></title>

<!-- Web fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/photoswipe@5/dist/photoswipe.css">

<!-- Shared CSS stack -->
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/components/button.css">
<link rel="stylesheet" href="css/components/pill.css">
<link rel="stylesheet" href="css/components/cards/base.css">
<link rel="stylesheet" href="css/components/cards/layout.css">
<link rel="stylesheet" href="css/components/cards/responsiveness.css">
<link rel="stylesheet" href="css/components/filter.css">
<link rel="stylesheet" href="css/components/md3-overrides.css">

<!-- Product page styles (safe to include everywhere; only apply on product page classes) -->
<link rel="stylesheet" href="css/pages/product.css">

<!-- Favicons -->
<link rel="icon" type="image/x-icon" href="images/logos/sports-warehouse-favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="images/logos/sports-warehouse-icon-SW-alternative_favicon.png">
<link rel="apple-touch-icon" sizes="180x180" href="images/logos/sports-warehouse-icon-SW-recommended_apple_size.png">

<meta name="theme-color" content="#ff690c">
