<?php
// scripts/update-orientations.php
declare(strict_types=1);

require __DIR__ . '/../db.php';

const SW_ROOT = __DIR__ . '/../';
const TARGET = 0.80;
const BAND   = 0.04;

function classify_fit(float $r): string {
    if ($r >= TARGET - BAND && $r <= TARGET + BAND) return 'B';
    return ($r < TARGET - BAND) ? 'P' : 'L';
}

function infer_crop_allowed(array $itemRow): int {
    $name = strtolower(trim(($itemRow['itemName'] ?? '') . ' ' . ($itemRow['brand'] ?? '')));
    $cat  = strtolower(trim(($itemRow['categoryName'] ?? '') . ' ' . ($itemRow['parentCategory'] ?? '')));
    $hay  = $name . ' ' . $cat;

    $contain = [
        'water bottle','water bottles','bottle','bottles',
        'backpack','backpacks','bag','bags',
        'helmet','helmets',
        'ball','balls',
        'glove','gloves','boxing glove','boxing gloves',
        'equipment',
        'shoe','shoes','sneaker','sneakers','trainer','trainers','boot','boots','footwear',
    ];
    foreach ($contain as $kw) {
        if (strpos($hay, $kw) !== false) return 0;
    }
    return 1;
}

function load_overrides(string $csv): array {
    if (!is_file($csv)) return [];
    $fp = fopen($csv, 'r');
    if (!$fp) return [];

    $map = [];
    $header = fgetcsv($fp);
    $hasHeader = false;
    $idx = ['itemId'=>0,'imagePath'=>1,'ratio'=>2,'fit'=>3,'crop_allowed'=>4];

    if ($header) {
        $lower = array_map(fn($s)=>strtolower(trim((string)$s)), $header);
        if (in_array('itemid',$lower) || in_array('fit',$lower)) {
            $hasHeader = true;
            foreach ($lower as $i=>$col) {
                if (isset($idx[$col])) $idx[$col] = $i;
            }
        } else {
            // first line was actually data; rewind
            rewind($fp);
        }
    }

    while (($row = fgetcsv($fp)) !== false) {
        $id  = (int)($row[$idx['itemId']] ?? 0);
        if (!$id) continue;
        $fit = strtoupper(trim((string)($row[$idx['fit']] ?? '')));
        $crp = trim((string)($row[$idx['crop_allowed']] ?? ''));
        $out = [];
        if (in_array($fit, ['B','P','L'], true)) $out['orientation'] = $fit;
        if ($crp !== '') {
            $lc = strtolower($crp);
            $out['crop_allowed'] = ($lc === '1' || $lc === 'yes' || $lc === 'true') ? 1 : 0;
        }
        if ($out) $map[$id] = $out;
    }
    fclose($fp);
    return $map;
}

function best_ratio_from_item(array $item): ?float {
    $paths = [];
    $json = $item['thumbnails_json'] ?? null;
    if ($json) {
        $arr = json_decode($json, true);
        if (is_array($arr)) {
            foreach ($arr as $p) $paths[] = ltrim((string)$p, '/');
        }
    }
    if (empty($paths) && !empty($item['images'])) $paths[] = ltrim((string)$item['images'], '/');
    if (!$paths) return null;

    $best = null;
    foreach ($paths as $rel) {
        $abs = realpath(SW_ROOT . $rel);
        if (!$abs || !is_file($abs)) continue;
        $dim = @getimagesize($abs);
        if (!$dim || !$dim[1]) continue;
        $ratio = $dim[0] / $dim[1];
        $d = abs($ratio - TARGET);
        if ($best === null || $d < $best['d']) $best = ['r'=>$ratio,'d'=>$d];
    }
    return $best ? $best['r'] : null;
}

// Load CSV overrides if any
$overrides = load_overrides(__DIR__ . '/../item_orientations.csv');

$update = $pdo->prepare("
    UPDATE item
       SET orientation  = :o,
           crop_allowed = :c
     WHERE itemId       = :id
");

$stmt = $pdo->query("
    SELECT itemId, itemName, brand, categoryName, parentCategory, images, thumbnails_json
      FROM item
    ORDER BY itemId
");

$count = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = (int)$row['itemId'];

    // Orientation from CSV or from best image ratio
    if (isset($overrides[$id]['orientation'])) {
        $o = $overrides[$id]['orientation']; // B/P/L
    } else {
        $r = best_ratio_from_item($row);
        $o = $r !== null ? classify_fit($r) : 'B'; // default to best
    }

    // crop_allowed from CSV or heuristic
    $c = $overrides[$id]['crop_allowed'] ?? infer_crop_allowed($row);

    $update->execute([':o'=>$o, ':c'=>$c, ':id'=>$id]);
    echo "✔ item {$id}: orientation={$o}, crop_allowed={$c}\n";
    $count++;
}
echo "Done: {$count} items updated.\n";



