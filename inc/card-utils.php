<?php
// inc/card-utils.php
// Usage: require __DIR__ . '/inc/card-utils.php';
// $featuredItems = pickBestThumbs($featuredItems);

/**
 * Pick best thumbnail for each item.
 *
 * For each item expects:
 *  - thumbnails_json (JSON array of relative paths like "images/..../foo.png")
 *  - orientation (one of 'P','S','L') — if not present, we will attempt to infer from first thumb
 *
 * This returns the same array with:
 *  - 'chosen_image' set (relative path)
 *  - 'chosen_ratio' set (float, width/height)
 *  - also sets 'images' to chosen_image so existing templates work
 */
function pickBestThumbs(array $items, string $projectRoot = null): array {
    $projectRoot = $projectRoot ?? realpath(__DIR__ . '/../'); // default project root
    // center/target ratios for buckets - tune these if you want different thresholds
    $targets = [
        'P' => 0.6,   // portrait target (e.g. ~3:5 => 0.6)
        'S' => 1.0,   // square
        'L' => 1.33,  // landscape (~4:3)
    ];

    foreach ($items as &$item) {
        $thumbs = [];
        $json = $item['thumbnails_json'] ?? null;
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $p) {
                    // normalize path: allow relative paths either starting with images/ or /images
                    $rel = ltrim($p, '/');
                    $abs = $projectRoot . '/' . $rel;
                    if (file_exists($abs)) {
                        $dim = @getimagesize($abs);
                        if ($dim && $dim[1] > 0) {
                            $ratio = $dim[0] / $dim[1];
                            $thumbs[] = ['path' => $rel, 'ratio' => $ratio, 'w' => $dim[0], 'h' => $dim[1]];
                        }
                    }
                }
            }
        }

        // fallback: if no thumbnails_json or none exist, try the existing images field
        if (empty($thumbs) && !empty($item['images'])) {
            $rel = ltrim($item['images'], '/');
            $abs = $projectRoot . '/' . $rel;
            if (file_exists($abs)) {
                $dim = @getimagesize($abs);
                if ($dim && $dim[1] > 0) {
                    $ratio = $dim[0] / $dim[1];
                    $thumbs[] = ['path' => $rel, 'ratio' => $ratio, 'w' => $dim[0], 'h' => $dim[1]];
                }
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
        if (!in_array($orient, ['P','S','L'])) {
            // infer from median of first thumb
            $r = $thumbs[0]['ratio'];
            $orient = ($r < 0.85) ? 'P' : (($r > 1.15) ? 'L' : 'S');
            $item['orientation'] = $orient;
        }

        $target = $targets[$orient] ?? 1.0;

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


