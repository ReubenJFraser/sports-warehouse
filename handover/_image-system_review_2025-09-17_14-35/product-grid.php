<?php
// inc/cards/product-grid.php
// expects: $items (array of item rows)
//
// Behaviors:
// - Prefer overrides (o_image, o_ratio, o_orientation) from the LEFT JOIN.
// - Else use headroom-aware chosen_image_effective / chosen_ratio_effective (from catalog-query).
// - Else fall back to chosen_image → first token in thumbnails_json → optional legacy helper.
// - Compute effective orientation and emit classes is-portrait / is-landscape / is-square.
// - Pick a fit mode (cover/contain) and keep your lazy-loading + data-images hooks.
// - If the chosen image is missing, log it and render the SVG placeholder box instead.

// ---------- minimal helpers (guarded) ----------
if (!function_exists('sw_public_path_from_url')) {
  function sw_public_path_from_url(string $url): string {
    // project root = two levels up from /inc/cards/ → project/
    $root  = rtrim(str_replace('\\','/', dirname(__DIR__, 2)), '/'); // e.g. C:/laragon/www/sports-warehouse-home-page
    $clean = ltrim($url, '/');
    return $root . '/' . $clean;
  }
}
if (!function_exists('sw_asset_exists')) {
  function sw_asset_exists(string $url): bool {
    // Skip remote URLs (can’t stat); treat as present.
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

if (!function_exists('pick_primary_image_src')) {
  /**
   * Picks the best display image URL for a card.
   * Priority:
   *   1) Override image (o_image), if present
   *   2) chosen_image_effective (headroom-aware)
   *   3) chosen_image (legacy stored)
   *   4) First token in thumbnails_json
   *   5) get_primary_image($item) fallback (if provided elsewhere)
   */
  function pick_primary_image_src(array $item, bool $has_get_primary_image) : array {
    $overrideImg = trim((string)($item['o_image'] ?? ''));
    if ($overrideImg !== '') {
      return ['src' => $overrideImg, 'source' => 'override'];
    }

    $eff = trim((string)($item['chosen_image_effective'] ?? ''));
    if ($eff !== '') {
      return ['src' => $eff, 'source' => 'effective'];
    }

    $stored = trim((string)($item['chosen_image'] ?? ''));
    if ($stored !== '') {
      return ['src' => $stored, 'source' => 'stored'];
    }

    $thumbs = (string)($item['thumbnails_json'] ?? '');
    if ($thumbs !== '') {
      if (preg_match('~([A-Za-z0-9_\-./]+\.(?:png|jpe?g|webp))~', $thumbs, $m)) {
        return ['src' => $m[1], 'source' => 'thumbs_regex'];
      }
      $parts = array_map('trim', explode(';', $thumbs));
      foreach ($parts as $p) {
        if ($p !== '') return ['src' => $p, 'source' => 'thumbs_split'];
      }
    }

    if ($has_get_primary_image && function_exists('get_primary_image')) {
      $primary = get_primary_image($item); // ['src','alt'] or null
      if (!empty($primary['src'])) {
        return ['src' => $primary['src'], 'source' => 'legacy_helper', 'alt' => ($primary['alt'] ?? '')];
      }
    }

    return ['src' => '', 'source' => 'none'];
  }
}

if (!function_exists('resolve_orientation')) {
  /**
   * Computes orientation from (in order):
   *   1) o_orientation (explicit)
   *   2) o_ratio
   *   3) chosen_ratio_effective (headroom-aware)
   *   4) chosen_ratio (legacy stored)
   *   5) orientation (legacy text)
   * Defaults to portrait.
   */
  function resolve_orientation(array $item): string {
    $o = strtolower(trim((string)($item['o_orientation'] ?? '')));
    if (in_array($o, ['portrait','landscape','square'], true)) return $o;

    $r = $item['o_ratio'] ?? null;
    if ($r !== null && is_numeric($r)) {
      $r = (float)$r;
      if (abs($r - 1.0) < 0.02) return 'square';
      return ($r > 1.02) ? 'landscape' : 'portrait';
    }

    $cre = $item['chosen_ratio_effective'] ?? null;
    if ($cre !== null && is_numeric($cre)) {
      $cre = (float)$cre;
      if (abs($cre - 1.0) < 0.02) return 'square';
      return ($cre > 1.02) ? 'landscape' : 'portrait';
    }

    $cr = $item['chosen_ratio'] ?? null;
    if ($cr !== null && is_numeric($cr)) {
      $cr = (float)$cr;
      if (abs($cr - 1.0) < 0.02) return 'square';
      return ($cr > 1.02) ? 'landscape' : 'portrait';
    }

    $legacy = strtolower(trim((string)($item['orientation'] ?? '')));
    if (in_array($legacy, ['portrait','landscape','square'], true)) return $legacy;

    return 'portrait';
  }
}

// Optional helpers (defined elsewhere)
$has_build_data_images = function_exists('build_card_data_images');
$has_get_primary_image = function_exists('get_primary_image');

// ---------- render ----------
if (empty($items)) {
  echo '<p class="no-results">No products found.</p>';
  return;
}
?>
<div class="product-grid">
  <?php foreach ($items as $item): ?>
    <?php
      // Fit mode (accept both crop_allowed and cropAllowed)
      $cropAllowedRaw = $item['crop_allowed'] ?? $item['cropAllowed'] ?? null;
      if ($cropAllowedRaw !== null && $cropAllowedRaw !== '') {
        $truthy  = in_array(strtolower((string)$cropAllowedRaw), ['1','true','yes','y'], true) || (int)$cropAllowedRaw === 1;
        $fitMode = $truthy ? 'cover' : 'contain';
      } else {
        $fitMode = infer_image_fit_mode($item);
      }
      $fitClass = $fitMode === 'cover' ? 'image-fit-cover' : 'image-fit-contain';

      // Choose primary image URL with headroom-aware effective fields
      $pick = pick_primary_image_src($item, $has_get_primary_image);
      $imgUrl = $pick['src'];
      $imgSrcSource = $pick['source'];
      $imgAltFromHelper = $pick['alt'] ?? null;

      // Placeholder fallback + log (project-local + server) and expose original bad URL
      $placeholder = false;
      $missingUrl  = '';
      if ($imgUrl === '' || !sw_asset_exists($imgUrl)) {
        $missingUrl = $imgUrl ?: '(empty)';
        $line = '[SW MISSING IMG] itemId=' . ($item['itemId'] ?? '?') . ' url=' . $missingUrl . PHP_EOL;

        // Server log (Apache/PHP)
        error_log($line);

        // Project-local log (robust: normalize path + ensure dir + append)
        $logDir  = rtrim(str_replace('\\','/', dirname(__DIR__, 2)), '/') . '/logs';
        if (!is_dir($logDir)) {
          @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/missing-images.log';
        if (@file_put_contents($logFile, $line, FILE_APPEND) === false) {
          // Fallback to server log so we don't lose the signal
          error_log('[SW MISSING IMG][FALLBACK] ' . trim($line));
        }

        $placeholder = true;
        // CSS provides the SVG via .card-media--placeholder, so we don't output <img>.
      }

      // Alt text
      $imgAlt = $item['altText'] ?? ($imgAltFromHelper ?? ($item['itemName'] ?? 'Product'));

      // Data payload for any modal/gallery
      $dataImages = $has_build_data_images ? build_card_data_images($item) : '';

      // Orientation & classes
      $orientation      = resolve_orientation($item);
      $orientationClass = 'is-' . $orientation;

      // IDs / names
      $itemId   = htmlspecialchars((string)($item['itemId'] ?? ''), ENT_QUOTES, 'UTF-8');
      $itemName = htmlspecialchars((string)($item['itemName'] ?? 'Product'), ENT_QUOTES, 'UTF-8');

      // Price handling
      $hasSale  = array_key_exists('salePrice', $item) && $item['salePrice'] !== null && $item['salePrice'] !== '';
      $priceVal = $item['price']     ?? null;
      $saleVal  = $item['salePrice'] ?? null;

      // Debug flags from query
      $usedFallback = (int)($item['used_fallback'] ?? 0);
      $chosenStored = trim((string)($item['chosen_image'] ?? ''));
      $chosenEff    = trim((string)($item['chosen_image_effective'] ?? ''));

      // -------- Face-focus CSS variable (cover only) --------
      // Prefer chosen_focus_y_effective from the query; fall back to h.focus_y_pct (joined) if present.
      $focusRaw = null;
      if (isset($item['chosen_focus_y_effective']) && $item['chosen_focus_y_effective'] !== null && $item['chosen_focus_y_effective'] !== '') {
        $focusRaw = (float)$item['chosen_focus_y_effective'];
      } elseif (isset($item['focus_y_pct']) && $item['focus_y_pct'] !== null && $item['focus_y_pct'] !== '') {
        $focusRaw = (float)$item['focus_y_pct'];
      }

      $styleObjPosAttr = '';
      $dataFocusAttr   = '';
      if ($fitMode === 'cover' && $focusRaw !== null) {
        // clamp to safe window the CSS expects (8–35%)
        $focusClamped = max(8.0, min(35.0, $focusRaw));
        $styleObjPosAttr = ' style="--objpos-y: ' . $focusClamped . '%;"';
        $dataFocusAttr   = ' data-focus-y="' . htmlspecialchars((string)$focusClamped, ENT_QUOTES, 'UTF-8') . '"';
      }
    ?>

    <div
      class="product-card <?= $orientationClass ?>"
      data-orientation="<?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>"
      data-images='<?= $dataImages ?>'
      data-fit="<?= $fitMode ?>"
      data-src-source="<?= htmlspecialchars($imgSrcSource, ENT_QUOTES, 'UTF-8') ?>"
      data-used-fallback="<?= $usedFallback ?>"
      data-chosen-stored="<?= htmlspecialchars($chosenStored, ENT_QUOTES, 'UTF-8') ?>"
      data-chosen-effective="<?= htmlspecialchars($chosenEff, ENT_QUOTES, 'UTF-8') ?>"
      <?= $dataFocusAttr ?>
      title="<?= $itemName ?>"
    >
      <?php if ($placeholder): ?>
        <!-- Missing image → styled SVG placeholder box -->
        <div
          class="card-media card-media--placeholder <?= $fitClass ?>"
          data-missing-url="<?= htmlspecialchars($missingUrl, ENT_QUOTES, 'UTF-8') ?>"
          aria-hidden="true"></div>
      <?php else: ?>
        <!-- Image area: fixed-aspect frame; inner button can open a gallery -->
        <button
          type="button"
          class="card-media card-media-trigger <?= $fitClass ?>"
          aria-controls="product-modal"
          aria-expanded="false"
          aria-label="Open product gallery for <?= $itemName ?>"
        >
          <img
            class="lazy"
            data-src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>"
            width="300" height="300" loading="lazy" decoding="async"<?= $styleObjPosAttr ?>>
        </button>

        <noscript>
          <div class="card-media <?= $fitClass ?>">
            <img
              src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
              alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>"
              width="300" height="300"<?= $styleObjPosAttr ?>>
          </div>
        </noscript>
      <?php endif; ?>

      <!-- Product info: navigates to detail page -->
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















