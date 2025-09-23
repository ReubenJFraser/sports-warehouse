<?php
// scripts/generate-item-orientations.php
declare(strict_types=1);

require __DIR__ . '/../db.php';

const SW_ROOT = __DIR__ . '/../'; // project root
const TARGET = 0.80;               // 4:5
const BAND   = 0.04;               // tolerance: 0.76..0.84 → "B"

function classify_fit(float $r): string {
    if ($r >= TARGET - BAND && $r <= TARGET + BAND) return 'B';
    return ($r < TARGET - BAND) ? 'P' : 'L';
}

function best_image_and_ratio(array $item): ?array {
    $paths = [];

    // From thumbnails_json
    $json = $item['thumbnails_json'] ?? null;
    if ($json) {
        $arr = json_decode($json, true);
        if (is_array($arr)) {
            foreach ($arr as $p) {
                $paths[] = ltrim((string)$p, '/');
            }
        }
    }

    // Fallback to images
    if (empty($paths) && !empty($item['images'])) {
        $paths[] = ltrim((string)$item['images'], '/');
    }

    if (!$paths) return null;

    $candidates = [];
    foreach ($paths as $rel) {
        $abs = realpath(SW_ROOT . $rel);
        if (!$abs || !is_file($abs)) continue;
        $dim = @getimagesize($abs);
        if (!$dim || !$dim[1]) continue;
        $ratio = $dim[0] / $dim[1];
        $candidates[] = ['rel' => $rel, 'ratio' => $ratio];
    }
    if (!$candidates) return null;

    usort($candidates, fn($a,$b) => abs($a['ratio'] - TARGET) <=> abs($b['ratio'] - TARGET));
    return $candidates[0];
}

$fp = fopen(__DIR__ . '/../item_orientations.csv', 'w');
if (!$fp) {
    fwrite(STDERR, "Cannot write item_orientations.csv\n");
    exit(1);
}
fputcsv($fp, ['itemId','imagePath','ratio','fit','crop_allowed']); // header

$stmt = $pdo->query("SELECT itemId,itemName,brand,images,thumbnails_json FROM item ORDER BY itemId");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $best = best_image_and_ratio($row);
    if (!$best) {
        fputcsv($fp, [$row['itemId'], '', '', '', '']);
        continue;
    }
    $fit = classify_fit($best['ratio']);
    fputcsv($fp, [
        $row['itemId'],
        $best['rel'],
        number_format($best['ratio'], 5, '.', ''),
        $fit,
        '' // leave crop_allowed blank to infer; fill 0/1 manually for overrides
    ]);
}
fclose($fp);
echo "Wrote item_orientations.csv\n";


