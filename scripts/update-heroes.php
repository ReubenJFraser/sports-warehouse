<?php
// scripts/update-heroes.php
// CLI: php scripts/update-heroes.php
// Web-safe (dev only): /scripts/update-heroes.php?confirm=1
require __DIR__ . '/../inc/env.php';          // your DB bootstrap (or inline pdo)
require __DIR__ . '/../inc/image-picker.php';
require __DIR__ . '/../inc/image-headroom.php';

$confirm = PHP_SAPI === 'cli' ? true : isset($_GET['confirm']);
if (!$confirm) {
  header('content-type: text/plain');
  echo "Dry-run. Add ?confirm=1 to persist.\n";
}

$BOX_RATIO = 0.8;
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sel = $pdo->query("SELECT itemId, itemName, chosen_image, chosen_ratio, thumbnails_json FROM item");
$upd = $pdo->prepare("UPDATE item SET chosen_image = ?, chosen_ratio = ? WHERE itemId = ?");

$batch = [];
while ($row = $sel->fetch(PDO::FETCH_ASSOC)) {
  $cands = [];

  if (!empty($row['chosen_image'])) {
    $p = $row['chosen_image'];
    $cands[] = ['path'=>$p, 'basename'=>strtolower(basename($p)), 'source'=>'stored'];
    $batch[] = strtolower(basename($p));
  }

  if (!empty($row['thumbnails_json'])) {
    foreach (array_filter(array_map('trim', explode(';', $row['thumbnails_json']))) as $t) {
      $p = (strpos($t, 'images/') === 0) ? $t : ('images/' . $t);
      $cands[] = ['path'=>$p, 'basename'=>strtolower(basename($p)), 'source'=>'thumb'];
      $batch[] = strtolower(basename($p));
    }
  }

  // fetch headroom per row (or batch pages if you prefer)
  $head = sw_fetch_headroom_map($pdo, $batch);
  [$best] = sw_pick_best_image($cands, $BOX_RATIO, $head);
  $batch = []; // reset

  if ($best) {
    if ($confirm) $upd->execute([$best['path'], $best['ratio'], $row['itemId']]);
    echo sprintf("[%s] %s -> %s (r=%.3f)\n",
      $row['itemId'], $row['itemName'], $best['path'], $best['ratio'] ?? -1);
  } else {
    echo sprintf("[%s] %s -> (no pick)\n", $row['itemId'], $row['itemName']);
  }
}

echo $confirm ? "Done (persisted).\n" : "Done (dry-run).\n";

