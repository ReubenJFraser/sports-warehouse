<?php
// scripts/import_orientations.php — itemId-based overrides
declare(strict_types=1);

$pdo = require __DIR__ . '/../db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* --- quick connectivity probe (TEMPORARY; remove after testing) --- */
$stmt = $pdo->query('SELECT DATABASE() AS db');
$cur  = $stmt->fetch();
fwrite(STDOUT, "Connected to DB: ".($cur['db'] ?? '?')."\n");
/* --- end probe --- */

$csvPath = $argv[1] ?? '';
if (!$csvPath || !is_readable($csvPath)) {
  fwrite(STDERR, "CSV not readable: $csvPath\n");
  exit(1);
}

function parse_ratio_text(?string $s): ?float {
  if (!$s) return null;
  $s = trim($s);
  if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*[:\/]\s*(\d+(?:\.\d+)?)\s*$/', $s, $m)) {
    $w = (float)$m[1]; $h = (float)$m[2];
    if ($w > 0 && $h > 0) return $w / $h;
  }
  if (is_numeric($s)) return (float)$s;
  return null;
}

function normalize_code(?string $code): ?string {
  $c = strtoupper(trim((string)$code));
  if ($c === 'S') return 'square';
  if ($c === 'P') return 'portrait';
  if ($c === 'L') return 'landscape';
  return null;
}

$pdo->beginTransaction();

$up = $pdo->prepare("
  INSERT INTO item_orientation_override
    (itemId, image_basename, orientation, ratio, ratio_text)
  VALUES
    (:itemId, :image, :orientation, :ratio, :ratio_text)
  ON DUPLICATE KEY UPDATE
    image_basename = VALUES(image_basename),
    orientation    = VALUES(orientation),
    ratio          = VALUES(ratio),
    ratio_text     = VALUES(ratio_text)
");

$inserted = 0; $updated = 0; $unchanged = 0; $skipped = 0;

if (($fh = fopen($csvPath, 'r')) === false) {
  fwrite(STDERR, "Cannot open CSV: $csvPath\n");
  exit(1);
}

$header = fgetcsv($fh);
if (!$header) { fwrite(STDERR, "Empty CSV\n"); exit(1); }

// Detect headered vs legacy 3-col
$lowerHeader = array_map(fn($h) => strtolower(trim((string)$h)), $header);
$hasHeaders = in_array('itemid', $lowerHeader, true);

if ($hasHeaders) {
  // ---------- Headered mode ----------
  $idx = array_change_key_case(array_flip($header), CASE_LOWER);

  while (($row = fgetcsv($fh)) !== false) {
    $itemId = (int)($row[$idx['itemid']] ?? 0);
    if ($itemId <= 0) { $skipped++; continue; }

    $image = trim((string)($row[$idx['image_basename']] ?? ''));
    $ori   = strtolower(trim((string)($row[$idx['orientation']] ?? '')));
    $rtxt  = trim((string)($row[$idx['ratio_text']] ?? ($row[$idx['ratio']] ?? '')));
    $ratio = parse_ratio_text($rtxt);

    if ($ori === '' && $ratio !== null) {
      if (abs($ratio - 1.0) < 0.02) $ori = 'square';
      elseif ($ratio > 1.02)       $ori = 'landscape';
      else                         $ori = 'portrait';
    }
    if (!in_array($ori, ['portrait','landscape','square'], true)) $ori = null;

    $up->execute([
      ':itemId'      => $itemId,
      ':image'       => $image ?: null,
      ':orientation' => $ori,
      ':ratio'       => $ratio,
      ':ratio_text'  => $rtxt ?: null,
    ]);

    $rc = $up->rowCount();
    if ($rc === 1) $inserted++;
    elseif ($rc === 2) $updated++;
    else $unchanged++;
  }
} else {
  // ---------- Legacy 3-column mode: id, image_path, code(S|P|L) ----------
  do {
    $id    = trim((string)($header[0] ?? '')); // first row was already read
    $image = trim((string)($header[1] ?? ''));
    $code  = trim((string)($header[2] ?? ''));

    $itemId = ctype_digit($id) ? (int)$id : 0;
    $ori    = normalize_code($code);

    if ($itemId > 0 && $ori !== null) {
      $up->execute([
        ':itemId'      => $itemId,
        ':image'       => $image ?: null,
        ':orientation' => $ori,
        ':ratio'       => null,
        ':ratio_text'  => null,
      ]);
      $rc = $up->rowCount();
      if ($rc === 1) $inserted++;
      elseif ($rc === 2) $updated++;
      else $unchanged++;
    } else {
      $skipped++;
    }
  } while (($header = fgetcsv($fh)) !== false);
}

fclose($fh);
$pdo->commit();

fwrite(STDOUT, "Import complete. Inserted: $inserted, Updated: $updated, Unchanged: $unchanged, Skipped: $skipped\n");




