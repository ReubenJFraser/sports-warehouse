<?php
// inc/cards/product-grid.php
// expects: $items (array of item rows)
//
// NOW USING HERO SYSTEM ONLY:
// - hero_image  (string, always correct)
// - hero_ratio  (float)
// - hero_orientation (P/S/L)
// - hero_score (diagnostic)
//
// Old fields (chosen_image, o_image, orientation, chosen_ratio, etc.)
// are no longer used for product-card rendering.
//
// Card behaviour:
// - Uses hero_image as the primary image.
// - Emits classes is-portrait / is-square / is-landscape based on hero_orientation.
// - Uses hero_ratio for PhotoSwipe sizing.
// - Uses cover/contain based on item type and crop_allowed flag.
// - Provides missing-image fallback and logs missing assets.
// - Preserves lazy-loading, gallery system, etc.


// ---------- minimal helpers (guarded) ----------
if (!function_exists('sw_public_path_from_url')) {
    function sw_public_path_from_url(string $url): string {
        $root  = rtrim(str_replace('\\','/', dirname(__DIR__, 2)), '/');
        $clean = ltrim($url, '/');
        return $root . '/' . $clean;
    }
}

if (!function_exists('sw_asset_exists')) {
    function sw_asset_exists(string $url): bool {
        if (preg_match('~^https?://~i', $url)) return true;
        $fs = sw_public_path_from_url($url);
        return is_file($fs) && is_readable($fs);
    }
}

if (!function_exists('infer_image_fit_mode')) {
    function infer_image_fit_mode(array $item): string {
        $fields = [
            strtolower(trim($item['subcategory']     ?? '')),
            strtolower(trim($item['parentCategory']  ?? '')),
            strtolower(trim($item['categoryName']    ?? '')),
            strtolower(trim($item['itemName']        ?? '')),
        ];
        $haystack = implode(' ', array_filter($fields));
        $containKeywords = [
            'water bottle','water bottles','bottle','bottles',
            'backpack','backpacks','bag','bags',
            'helmet','helmets',
            'ball','balls',
            'glove','gloves','boxing glove','boxing gloves',
            'equipment'
        ];
        foreach ($containKeywords as $kw) {
            if (strpos($haystack, $kw) !== false) return 'contain';
        }
        return 'cover';
    }
}


// ---------- GALLERY BUILDER (uses hero_image + thumbnails_json) ----------
if (!function_exists('sw_build_gallery_urls')) {
    function sw_build_gallery_urls(array $item): array {
        $urls = [];

        // hero_image ALWAYS first
        $hero = trim((string)($item['hero_image'] ?? ''));
        if ($hero !== '') $urls[] = $hero;

        // include all thumbnails_json images
        $thumbs = (string)($item['thumbnails_json'] ?? '');
        if ($thumbs !== '') {
            if (preg_match_all('~[A-Za-z0-9_\-./]+\.(?:png|jpe?g|webp)~', $thumbs, $m)) {
                foreach ($m[0] as $u) $urls[] = $u;
            }
            foreach (array_map('trim', explode(';', $thumbs)) as $u) {
                if ($u !== '') $urls[] = $u;
            }
        }

        // De-dupe in insertion order
        $seen = []; $out = [];
        foreach ($urls as $u) {
            $k = strtolower($u);
            if (!isset($seen[$k])) { $seen[$k] = true; $out[] = $u; }
        }
        return $out;
    }
}


// Optional helpers (defined elsewhere)
$has_build_data_images = function_exists('build_card_data_images');


// ---------- RENDER ----------
if (empty($items)) {
    echo '<p class="no-results">No products found.</p>';
    return;
}

$imgDebug = isset($_GET['imgdebug']) && $_GET['imgdebug'] !== '0';
?>
<div class="product-grid">
<?php foreach ($items as $item): ?>
    <?php
    // Fit mode
    $cropAllowedRaw = $item['crop_allowed'] ?? $item['cropAllowed'] ?? null;
    if ($cropAllowedRaw !== null && $cropAllowedRaw !== '') {
        $truthy  = in_array(strtolower((string)$cropAllowedRaw), ['1','true','yes','y'], true) || (int)$cropAllowedRaw === 1;
        $fitMode = $truthy ? 'cover' : 'contain';
    } else {
        $fitMode = infer_image_fit_mode($item);
    }
    $fitClass = $fitMode === 'cover' ? 'image-fit-cover' : 'image-fit-contain';


    // ============================================================
    // HERO IMAGE CORE
    // ============================================================
    $imgUrl = trim((string)($item['hero_image'] ?? ''));
    $imgAlt = $item['altText'] ?? ($item['itemName'] ?? 'Product');

    // Missing → placeholder & log
    $placeholder = false;
    $missingUrl  = '';
    if ($imgUrl === '' || !sw_asset_exists($imgUrl)) {
        $missingUrl = $imgUrl ?: '(empty)';
        $line = '[SW HERO MISSING] itemId=' . ($item['itemId'] ?? '?') . ' url=' . $missingUrl . PHP_EOL;
        error_log($line);
        $logDir  = rtrim(str_replace('\\','/', dirname(__DIR__, 2)), '/') . '/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
        $logFile = $logDir . '/missing-hero-images.log';
        @file_put_contents($logFile, $line, FILE_APPEND);
        $placeholder = true;
    }

    // Normalised orientation from hero_orientation
    $oriLetter = strtoupper(trim((string)($item['hero_orientation'] ?? 'P')));
    switch ($oriLetter) {
        case 'L': $orientation = 'landscape'; break;
        case 'S': $orientation = 'square';     break;
        default:  $orientation = 'portrait';   break;
    }
    $orientationClass = 'is-' . $orientation;


    // PhotoSwipe ratio
    $ratio = (float)($item['hero_ratio'] ?? 0.75);
    $pswpW = 1600;
    $pswpH = (int)round($pswpW / max(0.05, $ratio));


    // Gallery URLs (hero + extras)
    $galleryUrls = sw_build_gallery_urls($item);


    // IDs / names / prices
    $itemId   = htmlspecialchars((string)($item['itemId'] ?? ''), ENT_QUOTES, 'UTF-8');
    $itemName = htmlspecialchars((string)($item['itemName'] ?? 'Product'), ENT_QUOTES, 'UTF-8');

    $hasSale  = array_key_exists('salePrice', $item) && $item['salePrice'] !== null && $item['salePrice'] !== '';
    $priceVal = $item['price']     ?? null;
    $saleVal  = $item['salePrice'] ?? null;

    // Data payload
    $dataImages = $has_build_data_images ? build_card_data_images($item) : '';
    ?>

    <div
        class="product-card <?= $orientationClass ?>"
        data-pswp-gallery="item-<?= $itemId ?>"
        data-orientation="<?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>"
        data-fit="<?= $fitMode ?>"
        data-hero-score="<?= htmlspecialchars((string)($item['hero_score'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        data-images='<?= $dataImages ?>'
        title="<?= $itemName ?>"
    >
        <?php if ($placeholder): ?>

            <div
              class="card-media card-media--placeholder <?= $fitClass ?>"
              data-missing-url="<?= htmlspecialchars($missingUrl, ENT_QUOTES, 'UTF-8') ?>"
              aria-hidden="true"></div>

        <?php else: ?>

            <a
                href="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="card-media card-media-trigger <?= $fitClass ?>"
                data-pswp
                data-pswp-width="<?= $pswpW ?>"
                data-pswp-height="<?= $pswpH ?>"
                aria-label="Open product gallery for <?= $itemName ?>"
                <?= $imgDebug ? 'data-debug="1"' : '' ?>
            >
                <img
                    class="lazy"
                    data-src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>"
                    width="300" height="300"
                    loading="lazy" decoding="async"
                    onerror="this.onerror=null; this.src='/images/placeholders/product_missing.svg'; this.classList.add('is-placeholder');"
                >
            </a>

            <?php foreach ($galleryUrls as $u):
                if ($u === $imgUrl) continue; ?>
                <a
                  href="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>"
                  data-pswp
                  data-pswp-width="<?= $pswpW ?>"
                  data-pswp-height="<?= $pswpH ?>"
                  class="pswp-hidden"
                  style="display:none"
                  tabindex="-1"
                  aria-hidden="true"></a>
            <?php endforeach; ?>

            <noscript>
              <a
                href="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="card-media <?= $fitClass ?>"
                data-pswp
                data-pswp-width="<?= $pswpW ?>"
                data-pswp-height="<?= $pswpH ?>"
              >
                <img
                  src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                  alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>"
                  width="300" height="300"
                  onerror="this.onerror=null; this.src='/images/placeholders/product_missing.svg'; this.classList.add('is-placeholder');">
              </a>
            </noscript>

        <?php endif; ?>

        <a class="product-info" href="product.php?id=<?= $itemId ?>" title="<?= $itemName ?>">
            <div class="product-pricing">
                <?php if ($hasSale): ?>
                    <?php if ($priceVal !== null && $priceVal !== ''): ?>
                        <span class="price-original">$<?= number_format((float)$priceVal, 2) ?></span>
                    <?php endif; ?>
                    <span class="price-current">$<?= number_format((float)$saleVal, 2) ?></span>
                <?php else: ?>
                    <?php if ($priceVal !== null && $priceVal !== ''): ?>
                        <span class="price-current">$<?= number_format((float)$priceVal, 2) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="product-name"><?= $itemName ?></div>
        </a>
    </div>

<?php endforeach; ?>
</div>

















