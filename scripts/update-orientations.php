<?php
// scripts/update-orientations.php

// 1️⃣ bootstrap your DB connection
require __DIR__ . '/../db.php';  // adjust path if needed

// 2️⃣ prepare an UPDATE statement
$update = $pdo->prepare("
    UPDATE item
       SET orientation = :orient
     WHERE itemId      = :id
");

// 3️⃣ fetch each item’s image path
$stmt = $pdo->query("SELECT itemId, images FROM item");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id  = $row['itemId'];
    $img = trim($row['images']);

    // — skip if there’s no path recorded
    if ($img === '') {
        echo "⚠️ [{$id}] no image path, skipping\n";
        continue;
    }

    // 4️⃣ resolve to filesystem
    $path = __DIR__ . "/../{$img}";

    // — skip if that path isn’t a regular file
    if (! is_file($path)) {
        echo "⚠️ [{$id}] not found or not a file: {$path}\n";
        continue;
    }

    // 5️⃣ read dimensions (silence warnings)
    $dims = @getimagesize($path);
    if (! $dims) {
        echo "⚠️ [{$id}] getimagesize failed: {$path}\n";
        continue;
    }
    list($w, $h) = $dims;

    // — skip zero-height
    if ($h === 0) {
        echo "⚠️ [{$id}] zero height\n";
        continue;
    }

    // 6️⃣ compute ratio & pick orientation
    $r = $w / $h;
    if    ($r <  0.85) $o = 'P';
    elseif($r >  1.15) $o = 'L';
    else               $o = 'S';

    // 7️⃣ write it back
    $update->execute([
      ':orient' => $o,
      ':id'     => $id,
    ]);

    echo "✅ [{$id}] {$w}×{$h} → ratio={$r} → {$o}\n";
}

echo "🎉 All done!\n";


