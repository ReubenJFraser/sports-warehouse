<?php
// inc/cards/utils.php
// Usage: require __DIR__ . '/inc/cards/utils.php';
// $featuredItems = pickBestThumbs($featuredItems)
//
// NEW: safe URL join to avoid "images/brandsunderarmour/..." style mistakes.
// Example: sw_join_url('images/brands', $brandSlug, $genderSlug, $folder, $fileName)

if (!function_exists('sw_join_url')) {
  function sw_join_url(...$parts): string {
    $trimmed = array_map(fn($p) => trim((string)$p, '/'), $parts);
    return implode('/', array_filter($trimmed, 'strlen'));
  }
}

/**
 * Orientation from ratio helper (for carousel bucketing, etc.)
 * portrait | square | landscape
 */
if (!function_exists('sw_orientation_from_ratio')) {
  function sw_orientation_from_ratio(?float $r): string {
    if (!$r || $r <= 0) return 'portrait';
    if (abs($r - 1.0) < 0.06) return 'square';
    return ($r > 1.06) ? 'landscape' : 'portrait';
  }
}

/**
 * Small helper to read image dims from disk safely.
 * Returns ['w'=>int,'h'=>int,'ratio'=>float] or null.
 */
if (!function_exists('sw_img_dims_from_disk')) {
  function sw_img_dims_from_disk(string $abs): ?array {
    if (!is_file($abs) || !is_readable($abs)) return null;
    $dim = @getimagesize($abs);
    if (!$dim || $dim[0] <= 0 || $dim[1] <= 0) return null;
    $w = (int)$dim[0];
    $h = (int)$dim[1];
    return ['w' => $w, 'h' => $h, 'ratio' => ($h > 0 ? $w / $h : 0.0)];
  }
}

/**
 * Pick best thumbnail for each item.
 *
 * For each item expects either:
 *  - thumbnails_json (JSON array of relative paths like "images/.../foo.png")
 *  - OR a legacy semicolon list like "images/a.jpg;images/b.jpg"
 *  - orientation (one of 'B','P','S','L') — if not present, infer from first thumb
 *
 * This returns the same array with:
 *  - 'chosen_image' set (relative path)
 *  - 'chosen_ratio' set (float, width/height)
 *  - also sets 'images' to chosen_image so existing templates work
 */
function pickBestThumbs(array $items, string $projectRoot = null): array {
    // project root = two levels up from /inc/cards/ → project/
    $projectRoot = $projectRoot ?? realpath(dirname(__DIR__, 2));

    // center/target ratios for buckets - tune these if you want different thresholds
    // Note: 'B' (best-fit to 4:5) aliases to 'S' here so both target 0.8 by default.
    $targets = [
        'B' => 0.8,   // best-fit (4:5)
        'P' => 0.6,   // portrait target (~3:5 => 0.6)
        'S' => 0.8,   // “square/standard card” → aim at 4:5 card window
        'L' => 1.33,  // landscape (~4:3)
    ];

    foreach ($items as &$item) {
        $thumbs = [];

        // Normalize thumbnails list: accept JSON array OR semicolon list
        $raw = $item['thumbnails_json'] ?? null;
        $list = [];
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $list = array_values(array_filter(array_map('strval', $decoded)));
            } else {
                // legacy semicolon string
                $list = array_values(array_filter(array_map('trim', explode(';', (string)$raw))));
            }
        }

        // Build candidate list with dims
        foreach ($list as $p) {
            // allow relative paths either starting with images/ or /images
            $rel = ltrim($p, '/');
            if (strpos($rel, 'images/') !== 0) {
                // if the token is just "brand/foo.jpg", prefix images/
                $rel = 'images/' . $rel;
            }
            $abs = $projectRoot . '/' . $rel;
            $dims = sw_img_dims_from_disk($abs);
            if ($dims) {
                $thumbs[] = [
                  'path'   => $rel,
                  'ratio'  => $dims['ratio'],
                  'w'      => $dims['w'],
                  'h'      => $dims['h'],
                  'orient' => sw_orientation_from_ratio($dims['ratio']),
                ];
            }
        }

        // fallback: if no thumbnails OR none exist, try the existing images field
        if (empty($thumbs) && !empty($item['images'])) {
            $rel = ltrim((string)$item['images'], '/');
            if (strpos($rel, 'images/') !== 0) {
                $rel = 'images/' . $rel;
            }
            $abs = $projectRoot . '/' . $rel;
            $dims = sw_img_dims_from_disk($abs);
            if ($dims) {
                $thumbs[] = [
                  'path'   => $rel,
                  'ratio'  => $dims['ratio'],
                  'w'      => $dims['w'],
                  'h'      => $dims['h'],
                  'orient' => sw_orientation_from_ratio($dims['ratio']),
                ];
            }
        }

        // if still empty, set chosen_image null and continue
        if (empty($thumbs)) {
            $item['chosen_image'] = $item['images'] ?? null;
            $item['chosen_ratio'] = null;
            continue;
        }

        // ensure orientation key exists
        $orient = strtoupper($item['orientation'] ?? '');
        if (!in_array($orient, ['B','P','S','L'], true)) {
            // infer from first thumb
            $r = $thumbs[0]['ratio'];
            $orient = ($r < 0.85) ? 'P' : (($r > 1.15) ? 'L' : 'B'); // default to best/standard
            $item['orientation'] = $orient;
        }

        $target = $targets[$orient] ?? 0.8;

        // sort thumbs by closeness to target ratio
        usort($thumbs, function($a, $b) use ($target) {
            return abs($a['ratio'] - $target) <=> abs($b['ratio'] - $target);
        });

        // choose the closest
        $chosen = $thumbs[0];

        $item['chosen_image'] = $chosen['path'];
        $item['chosen_ratio'] = $chosen['ratio'];

        // keep backwards compatibility: set images to chosen_image so product-grid.php continues to work
        $item['images'] = $chosen['path'];
    }
    unset($item);
    return $items;
}

// -----------------------------------------------------------------------------
// Tiny gallery helpers (Filesystem MVP)
// -----------------------------------------------------------------------------

// Adjust these to match your repo.
// NOTE: utils.php lives in /inc/cards/, so project root is dirname(__DIR__, 2)
const SW_IMG_PUBLIC_BASE = '/images';                          // URL base
const SW_IMG_DISK_BASE   = __DIR__ . '/../../images';          // disk base = <project>/images

/**
 * Resolve the on-disk image directory for a product.
 * Change this to match your structure, e.g. /images/brands/{brand}/{category}/{sku}/
 */
function sw_resolve_image_dir(array $item): string {
    // Prefer an explicit field if you have it:
    if (!empty($item['image_dir'])) {
        // Expecting a web-relative path that begins at /images (we'll join it next)
        $dir = '/' . ltrim($item['image_dir'], '/');
        // normalize to start under /brands/... if a brand/sku was not handed
        return $dir;
    }
    // Otherwise build from fields you have. Adjust these keys to your schema.
    $brand = isset($item['brand']) ? strtolower(preg_replace('~\s+~', '-', $item['brand'])) : 'generic';
    $sku   = isset($item['sku'])   ? strtolower($item['sku']) : (isset($item['slug']) ? strtolower($item['slug']) : 'unknown');

    // Example path: /brands/{brand}/{sku}  (leading slash preserved)
    return '/' . sw_join_url('brands', $brand, $sku);
}

/**
 * Convert a disk path (/.../images/...) to a public URL (/images/...)
 */
function sw_public_url_from_disk(string $diskPath): string {
    $diskPath = str_replace('\\', '/', $diskPath);
    // Find the /images segment and make it web-relative
    $pos = strpos($diskPath, '/images/');
    if ($pos === false) {
        // Fallback: strip disk base if present
        $rel = str_replace(str_replace('\\','/', SW_IMG_DISK_BASE), '', $diskPath);
        return rtrim(SW_IMG_PUBLIC_BASE, '/') . '/' . ltrim($rel, '/');
    }
    return substr($diskPath, $pos);
}

/**
 * Discover ordered images for a product from the filesystem.
 * Returns an array of objects with src, alt, w, h, ratio, orientation (primary first).
 */
function sw_discover_images_fs(array $item): array {
    $relDir  = sw_resolve_image_dir($item);                  // e.g. /brands/adidas/abc123
    $diskDir = rtrim(str_replace('\\','/', SW_IMG_DISK_BASE), '/') . $relDir; // e.g. /.../images/brands/adidas/abc123

    // Collect allowed extensions
    $patterns = ['*.png','*.jpg','*.jpeg','*.webp'];
    $paths = [];
    foreach ($patterns as $pat) {
        foreach (glob($diskDir . '/' . $pat) as $p) {
            $paths[] = $p;
        }
    }
    if (!$paths) return [];

    // Natural sort so 01.png < 02.png < 10.png
    natsort($paths);
    $paths = array_values($paths);

    // Build alt base once
    $altBase = trim(($item['brand'] ?? '') . ' ' . ($item['itemName'] ?? 'Product'));
    $out = [];
    foreach ($paths as $p) {
        $dims   = sw_img_dims_from_disk($p); // may be null if unreadable
        $ratio  = $dims['ratio'] ?? null;
        $orient = sw_orientation_from_ratio($ratio);

        $file = pathinfo($p, PATHINFO_FILENAME);
        $alt  = $altBase !== '' ? ($altBase . ' – ' . strtoupper($file)) : strtoupper($file);

        $out[] = [
            'src'         => sw_public_url_from_disk($p),
            'alt'         => $alt,
            'w'           => $dims['w']   ?? null,
            'h'           => $dims['h']   ?? null,
            'ratio'       => $ratio,
            'orientation' => $orient,
        ];
    }
    return $out;
}

/**
 * Feature flag: choose FS MVP today; DB path later.
 */
function get_product_images(array $item): array {
    // If you later add a DB path, switch here:
    // return sw_fetch_images_db($item['id']); // same shape as FS
    return sw_discover_images_fs($item);
}

/**
 * Primary image = first item
 */
function get_primary_image(array $item): ?array {
    $all = get_product_images($item);
    return $all[0] ?? null;
}

/**
 * JSON to drop into a data attribute on the card anchor/button.
 * (Includes w, h, ratio, orientation when available.)
 */
function build_card_data_images(array $item): string {
    $arr = get_product_images($item);
    return htmlspecialchars(json_encode($arr, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
}

// -----------------------------------------------------------------------------
// Image-fit helpers
// -----------------------------------------------------------------------------

/**
 * Return true if the item explicitly allows cropping (via DB field),
 * false if explicitly disallowed, or null if unspecified.
 *
 * Accepts any of: 1/0, '1'/'0', 'yes'/'no', 'true'/'false', 'y'/'n'.
 */
function sw_get_crop_allowed($value): ?bool {
    if ($value === null || $value === '') return null;
    $s = strtolower((string)$value);
    if (in_array($s, ['1', 'true', 'yes', 'y'], true))  return true;
    if (in_array($s, ['0', 'false', 'no', 'n'], true)) return false;
    // Numeric fallthrough
    if (is_numeric($value)) return ((int)$value) === 1;
    return null;
}

/**
 * Infer whether we should "contain" (no crop) or "cover" (allow crop)
 * when the CropAllowed field is not set.
 *
 * Heuristic: gear-like items (bottles, backpacks, helmets, balls, gloves, equipment, shoes)
 * render better fully visible inside a 4:5 frame => "contain".
 * Apparel may crop slightly => "cover".
 *
 * Returns 'contain' or 'cover'.
 */
function sw_infer_image_fit_mode(array $item): string {
    $fields = [
        strtolower(trim($item['subcategory']    ?? '')),
        strtolower(trim($item['parentCategory'] ?? '')),
        strtolower(trim($item['categoryName']   ?? '')),
        strtolower(trim($item['itemName']       ?? '')),
    ];
    $haystack = ' ' . implode(' ', array_filter($fields)) . ' ';

    $containKeywords = [
        'water bottle','water bottles','bottle','bottles',
        'backpack','backpacks','bag','bags',
        'helmet','helmets',
        'ball','balls',
        'glove','gloves','boxing glove','boxing gloves',
        'equipment',
        'shoe','shoes','sneaker','sneakers','trainer','trainers','boot','boots','footwear',
    ];
    foreach ($containKeywords as $kw) {
        if (strpos($haystack, $kw) !== false) {
            return 'contain';
        }
    }
    return 'cover';
}

/**
 * Public helper for templates:
 * Returns the class name to apply on the image wrapper:
 *   - 'image-fit-cover'   (crop allowed; CSS object-fit: cover)
 *   - 'image-fit-contain' (no crop; CSS object-fit: contain)
 *
 * Precedence:
 *   1) Use DB field cropAllowed/CropAllowed if present.
 *   2) Otherwise, sw_infer_image_fit_mode() heuristic.
 */
function getImageFitClass(array $item): string {
    $explicit = null;
    if (array_key_exists('cropAllowed', $item)) {
        $explicit = sw_get_crop_allowed($item['cropAllowed']);
    } elseif (array_key_exists('CropAllowed', $item)) {
        $explicit = sw_get_crop_allowed($item['CropAllowed']);
    }

    if ($explicit === true)  return 'image-fit-cover';
    if ($explicit === false) return 'image-fit-contain';

    $mode = sw_infer_image_fit_mode($item);
    return $mode === 'cover' ? 'image-fit-cover' : 'image-fit-contain';
}








