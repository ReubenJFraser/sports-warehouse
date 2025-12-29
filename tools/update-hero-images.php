<?php
/**
 * Batch Update: Hero Image Selection
 * ----------------------------------
 * Reads image_audit.csv (from /tools/ folder).
 * For each row:
 *   - normalize image path
 *   - compute hero metrics
 *   - find matching item via chosen_image or thumbnails_json
 *   - update: hero_image, hero_score, hero_ratio, hero_orientation
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --------------------------------------------------
// Unified DB loader (local + production safe)
// --------------------------------------------------
require_once __DIR__ . '/../db.php';

// --------------------------------------------------
// Paths
// --------------------------------------------------
$root    = dirname(__DIR__);
$csvPath = $root . '/tools/image_audit.csv';

if (!file_exists($csvPath)) {
    die("CSV not found at: {$csvPath}");
}

// --------------------------------------------------
// DB connection
// --------------------------------------------------
$mysqli = new mysqli(
    $DB_HOST,
    $DB_USER,
    $DB_PASS,
    $DB_NAME
);

if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}

// --------------------------------------------------
// DIAGNOSTIC BLOCK (permanent sanity check)
// --------------------------------------------------
header('Content-Type: text/plain; charset=utf-8');

$whoDb = $mysqli->query("SELECT DATABASE() AS db")->fetch_assoc()['db'] ?? '(unknown)';
$whoMe = $mysqli->query("SELECT USER() AS u")->fetch_assoc()['u'] ?? '(unknown)';
$whoHo = $mysqli->query("SELECT @@hostname AS h")->fetch_assoc()['h'] ?? '(unknown)';

echo "=== SW HERO UPDATE DIAGNOSTICS ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "DB: {$whoDb}\n";
echo "User: {$whoMe}\n";
echo "Host: {$whoHo}\n";
echo "CSV: {$csvPath}\n";
echo "CSV exists: " . (file_exists($csvPath) ? "YES" : "NO") . "\n";
echo "Project root: {$root}\n";
echo "==================================\n\n";

// --------------------------------------------------
// Helper: orientation from ratio
// --------------------------------------------------
function orientation_class_from_ratio(float $ratio): string
{
    if ($ratio < 0.95) return 'P'; // portrait
    if ($ratio > 1.05) return 'L'; // landscape
    return 'S';                    // square
}

// --------------------------------------------------
// HERO SCORING FORMULA
// --------------------------------------------------
function compute_hero_score(array $r): array
{
    $w = (int)$r['width'];
    $h = (int)$r['height'];
    $ratio = ($w > 0 && $h > 0) ? ($w / $h) : 1.0;

    $left   = (int)$r['bbox_left'];
    $right  = (int)$r['bbox_right'];
    $top    = (int)$r['bbox_top'];
    $bottom = (int)$r['bbox_bottom'];
    $faceY  = (int)$r['face_y'];

    $bboxHeight = $bottom - $top;

    // 1) Horizontal centering (50%)
    $bboxCenterX  = ($left + $right) / 2.0;
    $imageCenterX = $w / 2.0;
    $offset       = abs($bboxCenterX - $imageCenterX);
    $horizontal   = max(0, 100 - ($offset * 1.8));

    // 2) Vertical face alignment (30%)
    $faceRel = ($bboxHeight > 0) ? (($faceY - $top) / $bboxHeight) : 0.22;
    $idealMin = 0.18;
    $idealMax = 0.27;

    if ($faceRel >= $idealMin && $faceRel <= $idealMax) {
        $vertical = 100;
    } else {
        $dist = ($faceRel < $idealMin)
            ? ($idealMin - $faceRel)
            : ($faceRel - $idealMax);
        $vertical = max(0, 100 - ($dist * 300));
    }

    // 3) Ratio score (15%)
    if ($ratio < 0.95)      $ratioScore = 100;
    elseif ($ratio < 1.05)  $ratioScore = 85;
    else                    $ratioScore = 10;

    // 4) Coverage score (5%)
    $coverage = ($bboxHeight > 0 && $h > 0) ? ($bboxHeight / $h) : 0.85;
    $coverageScore = max(0, 100 - (abs($coverage - 0.85) * 220));

    $final =
        ($horizontal    * 0.50) +
        ($vertical      * 0.30) +
        ($ratioScore    * 0.15) +
        ($coverageScore * 0.05);

    return [$final, $ratio];
}

// --------------------------------------------------
// Load CSV
// --------------------------------------------------
$rows   = array_map('str_getcsv', file($csvPath));
$header = array_shift($rows);

// --------------------------------------------------
// Counters (end the guessing forever)
// --------------------------------------------------
$seen    = 0;
$diskOk  = 0;
$matched = 0;
$updated = 0;

// --------------------------------------------------
// Process rows
// --------------------------------------------------
foreach ($rows as $r) {
    $row = array_combine($header, $r);
    if (!$row) continue;

    $seen++;

    // Normalize CSV image path
    $img = trim((string)($row['path'] ?? ''));
    if ($img === '') continue;

    $img = str_replace('\\', '/', $img);
    $img = ltrim($img, '/');
    if (stripos($img, 'images/') !== 0) {
        $img = 'images/' . $img;
    }

    if (!is_file($root . '/' . $img)) continue;
    $diskOk++;

    // Compute hero metrics
    [$score, $ratio] = compute_hero_score($row);
    $orientation = orientation_class_from_ratio($ratio);

    // Find matching item
    $stmt = $mysqli->prepare("
        SELECT itemId
        FROM item
        WHERE chosen_image = ?
           OR thumbnails_json LIKE CONCAT('%', ?, '%')
        LIMIT 1
    ");
    $stmt->bind_param('ss', $img, $img);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) continue;
    $matched++;

    $item   = $res->fetch_assoc();
    $itemId = (int)$item['itemId'];

    // Update item
    $up = $mysqli->prepare("
        UPDATE item
        SET hero_image = ?,
            hero_score = ?,
            hero_ratio = ?,
            hero_orientation = ?
        WHERE itemId = ?
    ");
    $up->bind_param('sddsi', $img, $score, $ratio, $orientation, $itemId);
    $up->execute();

    if ($up->affected_rows > 0) {
        $updated++;
    }
}

// --------------------------------------------------
// Final summary
// --------------------------------------------------
echo "\n=== SUMMARY ===\n";
echo "Rows read: {$seen}\n";
echo "Disk OK: {$diskOk}\n";
echo "Matched items: {$matched}\n";
echo "Updated rows: {$updated}\n";
echo "=============\n";




