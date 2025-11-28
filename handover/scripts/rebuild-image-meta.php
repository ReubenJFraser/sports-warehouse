<?php
// scripts/rebuild-image-meta.php
// Scans thumbnails_json/images for each item, computes width/height/ratio,
// and upserts into item_image_meta. Then (optionally) marks the best-fit 4:5.

require __DIR__ . '/../db.php'; // must provide $pdo (PDO MySQL)

// Tune these to your project
$projectRoot = realpath(__DIR__ . '/..');        // repo root
$imagesBase  = $projectRoot . '/images';         // disk base for /images/...

// 1) Pull all items
$stmt = $pdo->query("SELECT itemId, thumbnails_json, images FROM item");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Upsert prep
$upsert = $pdo->prepare("
  INSERT INTO item_image_meta (itemId, src, width, height, ratio, isPrimary)
  VALUES (:itemId, :src, :w, :h, :r, :p)
  ON DUPLICATE KEY UPDATE width=VALUES(width), height=VALUES(height), ratio=VALUES(ratio), isPrimary=VALUES(isPrimary)
");

// helper: turn web path (images/...) into disk path
function toDisk(string $rel, string $imagesBase): string {
  $rel = ltrim($rel, '/');
  if (stripos($rel, 'images/') !== 0) {
    // tolerate paths missing the leading "images/"
    $rel = 'images/' . $rel;
  }
  return rtrim($imagesBase, '/\\') . '/' . substr($rel, strlen('images/'));
}

// 3) Iterate items, gather image list
foreach ($items as $row) {
  $id = (int)$row['itemId'];
  $list = [];

  // thumbnails_json takes precedence (JSON array of strings)
  if (!empty($row['thumbnails_json'])) {
    $decoded = json_decode($row['thumbnails_json'], true);
    if (is_array($decoded)) {
      foreach ($decoded as $p) {
        if (is_string($p) && $p !== '') $list[] = $p;
      }
    }
  }

  // fallback: single images field (also accept semicolon-delimited)
  if (empty($list) && !empty($row['images'])) {
    $parts = preg_split('~[;,\s]+~', $row['images'], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $p) $list[] = $p;
  }

  if (!$list) {
    echo "⚠️  [{$id}] no images\n";
    continue;
  }

  // 4) Compute dims & upsert
  $i = 0;
  foreach ($list as $rel) {
    $disk = toDisk($rel, $imagesBase);
    if (!is_file($disk)) {
      echo "⚠️  [{$id}] missing file: {$rel}\n";
      continue;
    }
    $dims = @getimagesize($disk);
    if (!$dims || $dims[1] == 0) {
      echo "⚠️  [{$id}] getimagesize failed: {$rel}\n";
      continue;
    }
    [$w, $h] = $dims;
    $r = $w / $h;

    $upsert->execute([
      ':itemId' => $id,
      ':src'    => ltrim($rel, '/'),
      ':w'      => $w,
      ':h'      => $h,
      ':r'      => $r,
      ':p'      => ($i === 0) ? 1 : 0, // mark first as primary for now
    ]);
    $i++;
  }

  echo "✅  [{$id}] updated {$i} image(s)\n";
}

// 5) (Optional) choose best-fit to 4:5 and mark isPrimary accordingly (MySQL 8+)
$haveWinFns = true;
try {
  $pdo->query("SELECT ROW_NUMBER() OVER () AS x")->fetch();
} catch (Throwable $e) {
  $haveWinFns = false;
}

if ($haveWinFns) {
  // mark only the closest to 0.8 as primary
  $pdo->exec("UPDATE item_image_meta SET isPrimary = 0");
  $pdo->exec("
    WITH ranked AS (
      SELECT id, ROW_NUMBER() OVER (PARTITION BY itemId ORDER BY ABS(ratio - 0.8)) rn
      FROM item_image_meta
    )
    UPDATE item_image_meta iim
    JOIN ranked r ON r.id = iim.id
    SET iim.isPrimary = (r.rn = 1)
  ");
  echo "🎯 marked best-fit 4:5 as primary\n";
} else {
  // MySQL 5.7 fallback: use a temp table with the min distance per item
  $pdo->exec("UPDATE item_image_meta SET isPrimary = 0");
  $pdo->exec("
    CREATE TEMPORARY TABLE t_best
    SELECT m1.itemId, m1.id
    FROM item_image_meta m1
    JOIN (
      SELECT itemId, MIN(ABS(ratio - 0.8)) AS mind
      FROM item_image_meta
      GROUP BY itemId
    ) d ON d.itemId = m1.itemId
    WHERE ABS(m1.ratio - 0.8) = d.mind
  ");
  $pdo->exec("
    UPDATE item_image_meta m
    JOIN t_best b ON b.id = m.id
    SET m.isPrimary = 1
  ");
  echo "🎯 (5.7) marked best-fit 4:5 as primary\n";
}

echo "🎉 Done.\n";


