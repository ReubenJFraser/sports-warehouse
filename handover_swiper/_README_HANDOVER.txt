Handover pack for Swiper inline slider + PhotoSwipe

Contains:
- index.php (script includes)
- inc/head.php (we'll add Swiper CSS here)
- inc/photoswipe-init.php (per-card gallery already)
- inc/cards/product-grid.php (we'll wrap slides for Swiper)
- js/card-slider.js (placeholder; to be implemented)
- card-related CSS (to style the inline slider/focus state)

Excluded: images/, videos/, db.php, .venv/, node_modules, exports/backups, etc.

Next steps (in new chat):
1) Add Swiper CSS (CDN) to inc/head.php.
2) Add Swiper ESM init in js/card-slider.js; lazy-init per visible card.
3) Update inc/cards/product-grid.php HTML to output .swiper/.swiper-wrapper/.swiper-slide around the card images.
4) Wire Zoom button to PhotoSwipe: open at the current Swiper slide index.
5) Add .card--focus styles (white bg + padding + rounded + shadow, hide title/price).
