<?php
/**
 * Image Audit Tool — CLEAN CSV VERSION
 * -----------------------------------------------------
 * No warnings, no HTML, no noise.
 */

ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// --------------------------------------------------
// Root paths
// --------------------------------------------------
$root = dirname(__DIR__);
$imgRoot = $root . "/images/brands";

$results = [];
$allowedExt = ['png','jpg','jpeg','webp'];

// --------------------------------------------------
// Safe GD loader (no warnings ever)
// --------------------------------------------------
function safe_load_image($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    switch ($ext) {
        case 'png':
            return @imagecreatefrompng($path);
        case 'jpg':
        case 'jpeg':
            return @imagecreatefromjpeg($path);
        case 'webp':
            if (function_exists('imagecreatefromwebp')) {
                return @imagecreatefromwebp($path);
            }
            return null;
        default:
            return null;
    }
}

// --------------------------------------------------
// Alpha bounding box (safe)
// --------------------------------------------------
function compute_alpha_bbox($im) {
    if (!$im) return [0,0,0,0];

    $w = imagesx($im);
    $h = imagesy($im);

    $top = $h;
    $bottom = 0;
    $left = $w;
    $right = 0;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgba = @imagecolorat($im, $x, $y);
            $alpha = ($rgba & 0x7F000000) >> 24;
            if ($alpha < 120) {
                if ($x < $left) $left = $x;
                if ($x > $right) $right = $x;
                if ($y < $top) $top = $y;
                if ($y > $bottom) $bottom = $y;
            }
        }
    }

    if ($bottom === 0 && $top === $h) {
        return [0,0,0,0];
    }

    return [$left, $top, $right, $bottom];
}

// --------------------------------------------------
// Crude face detector
// --------------------------------------------------
function estimate_face_y($im) {
    if (!$im) return null;

    $w = imagesx($im);
    $h = imagesy($im);
    $bandBottom = intval($h * 0.40);

    $bestY = null;
    $bestScore = -INF;

    for ($y = 0; $y < $bandBottom; $y++) {
        $score = 0;
        for ($x = 0; $x < $w; $x++) {
            $rgba = @imagecolorat($im, $x, $y);
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;
            $score += ($r + $g + $b);
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestY = $y;
        }
    }

    return $bestY;
}

// --------------------------------------------------
// Scan directory
// --------------------------------------------------
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imgRoot));

foreach ($rii as $file) {
    if ($file->isDir()) continue;

    $path = $file->getPathname();
    $rel  = substr($path, strlen($root) + 1);

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) continue;

    $im = safe_load_image($path);
    if (!$im) continue; // skip unreadable/corrupt

    $w = @imagesx($im);
    $h = @imagesy($im);

    if (!$w || !$h) {
        imagedestroy($im);
        continue;
    }

    // Compute bounding box
    [$left, $top, $right, $bottom] = compute_alpha_bbox($im);

    $bboxWidth  = max(0, $right - $left);
    $bboxHeight = max(0, $bottom - $top);

    // Face position
    $faceY = estimate_face_y($im);

    $results[] = [
        'path' => $rel,
        'width' => $w,
        'height' => $h,
        'ratio' => $w > 0 ? number_format($w / $h, 4) : '',
        'bbox_left' => $left,
        'bbox_top' => $top,
        'bbox_right' => $right,
        'bbox_bottom' => $bottom,
        'bbox_width' => $bboxWidth,
        'bbox_height' => $bboxHeight,
        'face_y' => $faceY,
    ];

    imagedestroy($im);
}

// --------------------------------------------------
// Output CSV cleanly
// --------------------------------------------------
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="image_audit.csv"');

$out = fopen('php://output', 'w');

fputcsv($out, [
    'path','width','height','ratio',
    'bbox_left','bbox_top','bbox_right','bbox_bottom',
    'bbox_width','bbox_height','face_y'
]);

foreach ($results as $r) {
    fputcsv($out, $r);
}
fclose($out);
exit;





