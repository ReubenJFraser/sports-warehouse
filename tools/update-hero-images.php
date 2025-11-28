<?php
/**
 * Batch Update: Hero Image Selection
 * ----------------------------------
 * Reads image_audit.csv (from /tools/ folder).
 * For each row:
 *   - compute hero metrics
 *   - find matching item via chosen_image or thumbnails_json
 *   - update: hero_image, hero_score, hero_ratio, hero_orientation
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__);
$csvPath = $root . "/tools/image_audit.csv";

if (!file_exists($csvPath)) {
    die("CSV not found at: $csvPath");
}

// -----------------------------------------------
// DB connection
// -----------------------------------------------
$mysqli = new mysqli("localhost", "root", "", "sportswh");
if ($mysqli->connect_errno) {
    die("DB connection failed: " . $mysqli->connect_error);
}

// -----------------------------------------------
// Helper: compute orientation
// -----------------------------------------------
function orientation_class_from_ratio($ratio) {
    if ($ratio < 0.95) return 'P';  // portrait
    if ($ratio > 1.05) return 'L';  // landscape
    return 'S';                      // square
}

// -----------------------------------------------
// HERO SCORING FORMULA
// -----------------------------------------------
function compute_hero_score($r) {

    // Extract values
    $w       = (int)$r['width'];
    $h       = (int)$r['height'];
    $ratio   = $w > 0 ? ($w / $h) : 1;

    $left    = (int)$r['bbox_left'];
    $right   = (int)$r['bbox_right'];
    $top     = (int)$r['bbox_top'];
    $bottom  = (int)$r['bbox_bottom'];

    $faceY   = (int)$r['face_y'];

    $bboxWidth  = $right - $left;
    $bboxHeight = $bottom - $top;

    // --------------------------------------
    // 1) Horizontal centering (50%)
    // --------------------------------------
    $bboxCenterX   = ($left + $right) / 2.0;
    $imageCenterX  = $w / 2.0;
    $offset        = abs($bboxCenterX - $imageCenterX);

    $horizontal = max(0, 100 - ($offset * 1.8));

    // --------------------------------------
    // 2) Vertical face alignment (30%)
    // --------------------------------------
    if ($bboxHeight > 0) {
        $faceRel = ($faceY - $top) / $bboxHeight;
    } else {
        $faceRel = 0.22; // assume good if invalid
    }

    $idealMin = 0.18;
    $idealMax = 0.27;

    if ($faceRel >= $idealMin && $faceRel <= $idealMax) {
        $vertical = 100;
    } else {
        $dist = 0;
        if ($faceRel < $idealMin) $dist = $idealMin - $faceRel;
        else                       $dist = $faceRel - $idealMax;
        $vertical = max(0, 100 - ($dist * 300));
    }

    // --------------------------------------
    // 3) Ratio score (15%)
    // --------------------------------------
    if ($ratio < 0.95)      $ratioScore = 100;
    elseif ($ratio < 1.05)  $ratioScore = 85;
    else                    $ratioScore = 10;

    // --------------------------------------
    // 4) Coverage (5%)
    // --------------------------------------
    $coverage = ($bboxHeight > 0) ? ($bboxHeight / $h) : 0.85;

    $coverageScore = max(0, 100 - (abs($coverage - 0.85) * 220));

    // --------------------------------------
    // Weighted total
    // --------------------------------------
    $final =
          ($horizontal * 0.50)
        + ($vertical   * 0.30)
        + ($ratioScore * 0.15)
        + ($coverageScore * 0.05);

    return [$final, $ratio];
}

// -----------------------------------------------
// Load CSV
// -----------------------------------------------
$rows = array_map('str_getcsv', file($csvPath));
$header = array_shift($rows);

foreach ($rows as $r) {
    $row = array_combine($header, $r);
    $img = $row['path'];

    // Skip tiny / invalid entries
    if (!$img || !file_exists($root . '/' . $img)) continue;

    // Compute hero metrics
    list($score, $ratio) = compute_hero_score($row);
    $orientation = orientation_class_from_ratio($ratio);

    // ---------------------------------------
    // Find matching item
    // ---------------------------------------
    $stmt = $mysqli->prepare("
        SELECT itemId, itemName
        FROM item
        WHERE chosen_image = ?
           OR thumbnails_json LIKE CONCAT('%', ?, '%')
        LIMIT 1
    ");
    $stmt->bind_param("ss", $img, $img);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) continue;

    $item = $res->fetch_assoc();
    $itemId = $item['itemId'];

    // ---------------------------------------
    // Update item
    // ---------------------------------------
    $up = $mysqli->prepare("
        UPDATE item
        SET hero_image = ?,
            hero_score = ?,
            hero_ratio = ?,
            hero_orientation = ?
        WHERE itemId = ?
    ");

    $up->bind_param("sddsi",
        $img, $score, $ratio, $orientation, $itemId
    );
    $up->execute();
}

echo "<h1>Hero Image Update Complete</h1>";
echo "<p>All items with images now have hero_image, hero_score, hero_ratio, and hero_orientation updated.</p>";



