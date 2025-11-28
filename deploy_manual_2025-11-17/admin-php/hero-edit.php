<?php
// /admin/hero-edit.php
// Mobile-first hero editor for a single item.

require __DIR__ . '/../db.php';
require __DIR__ . '/image-helper.php';

// ------------------------------------------------------------
// Helper: score an image
// ------------------------------------------------------------
if (!function_exists('sw_score_candidate')) {
    function sw_score_candidate(?array $h): float
    {
        if (!$h) return 5.0;

        $ratio     = isset($h['ratio']) ? (float)$h['ratio'] : 0.0;
        $headroom  = isset($h['headroom_pct']) ? (float)$h['headroom_pct'] : null;
        $focusY    = isset($h['focus_y_pct']) ? (float)$h['focus_y_pct'] : null;
        $cropSafe  = isset($h['crop_safe']) ? (int)$h['crop_safe'] : 0;
        $faceCount = isset($h['face_count']) ? (int)$h['face_count'] : 0;

        $score = 0.0;

        // Face + crop safety baseline
        if ($faceCount > 0 && $cropSafe === 1)      $score += 70;
        elseif ($faceCount > 0)                    $score += 45;
        elseif ($cropSafe === 1)                   $score += 30;
        else                                       $score += 10;

        // Ratio closeness to 4:5
        if ($ratio > 0) $score -= min(20.0, abs($ratio - 0.8) * 40.0);

        // Focus Y
        if ($focusY !== null) $score -= min(15.0, abs($focusY - 22.0) * 0.7);

        // Headroom
        if ($headroom !== null) {
            if ($headroom >= 6 && $headroom <= 20) $score += 5;
            elseif ($headroom < 3 || $headroom > 30) $score -= 3;
        }

        return $score;
    }
}

// ------------------------------------------------------------
// 1) Load item
// ------------------------------------------------------------
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($itemId <= 0) { http_response_code(404); exit("Invalid item ID"); }

$sql = "
    SELECT itemId, itemName, brand, chosen_image, thumbnails_json, chosen_ratio,
           hero_image, hero_score, hero_ratio, hero_orientation
    FROM item
    WHERE itemId = :id
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) { http_response_code(404); exit("Item not found"); }

// ------------------------------------------------------------
// 2) Build candidate list
// ------------------------------------------------------------
$candidates = [];
$seen = [];

function add_candidate(&$candidates, &$seen, $path, $source) {
    $path = trim($path);
    if ($path === '') return;
    if (isset($seen[$path])) return;

    $seen[$path] = true;
    $candidates[] = [
        'path'     => $path,
        'basename' => strtolower(substr($path, strrpos($path, '/') + 1)),
        'source'   => $source,
    ];
}

if (!empty($item['chosen_image'])) add_candidate($candidates, $seen, $item['chosen_image'], 'chosen');

if (!empty($item['thumbnails_json'])) {
    foreach (array_filter(array_map('trim', explode(';', $item['thumbnails_json']))) as $p) {
        add_candidate($candidates, $seen, $p, 'thumb');
    }
}

// ------------------------------------------------------------
// 3) Load headroom rows
// ------------------------------------------------------------
$bases = array_column($candidates, 'basename');
if (empty($bases)) $bases = [''];

$ph = [];
$params = [];
foreach ($bases as $i => $b) {
    $ph[] = ":b$i";
    $params[":b$i"] = $b;
}

$sqlH = "
    SELECT image_basename, ratio, headroom_pct, focus_y_pct, crop_safe, face_count
    FROM image_headroom
    WHERE image_basename IN (" . implode(',', $ph) . ")
";
$stmtH = $pdo->prepare($sqlH);
$stmtH->execute($params);
$rows = $stmtH->fetchAll(PDO::FETCH_ASSOC);

$headroom = [];
foreach ($rows as $h) {
    $headroom[strtolower($h['image_basename'])] = $h;
}

// ------------------------------------------------------------
// 4) Score candidates + detect best
// ------------------------------------------------------------
$best = null;
$bestScore = -INF;

foreach ($candidates as &$c) {
    $hr = $headroom[$c['basename']] ?? null;
    $score = sw_score_candidate($hr);
    $c['score'] = $score;
    $c['ratio'] = $hr['ratio'] ?? null;

    if ($score > $bestScore) {
        $bestScore = $score;
        $best = $c;
    }
}
unset($c);

// ------------------------------------------------------------
// 5) Handle POST actions
// ------------------------------------------------------------
$flash = '';
$flashType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['override'])) {
        $img = trim($_POST['override']);

        $sql = "
            INSERT INTO hero_override (itemId, image_path)
            VALUES (:id, :img)
            ON DUPLICATE KEY UPDATE image_path = VALUES(image_path)
        ";
        $pdo->prepare($sql)->execute([':id'=>$itemId, ':img'=>$img]);

        $flash = "Hero image overridden.";
        $flashType = 'success';
    }

    elseif (isset($_POST['reject'])) {
        $img = trim($_POST['reject']);
        $score = null;

        foreach ($candidates as $c) {
            if ($c['path'] === $img) {
                $score = $c['score'];
                break;
            }
        }

        $sql = "
            INSERT INTO hero_rejections (itemId, rejected_image, auto_score)
            VALUES (:id, :img, :s)
            ON DUPLICATE KEY UPDATE auto_score = VALUES(auto_score)
        ";
        $pdo->prepare($sql)->execute([':id'=>$itemId, ':img'=>$img, ':s'=>$score]);

        $flash = "Automatically selected image rejected.";
        $flashType = 'error';
    }

    elseif (isset($_POST['clear_override'])) {
        $pdo->prepare("DELETE FROM hero_override WHERE itemId = :id")
            ->execute([':id'=>$itemId]);

        $flash = "Override cleared.";
        $flashType = 'info';
    }
}

// ------------------------------------------------------------
// 6) Determine active hero
// ------------------------------------------------------------
$over = $pdo->prepare("SELECT image_path FROM hero_override WHERE itemId = :id");
$over->execute([':id'=>$itemId]);
$overridePath = $over->fetchColumn();

$activeHero = $overridePath ?: $item['hero_image'] ?: $item['chosen_image'];

// ------------------------------------------------------------
// 7) Page output
// ------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hero Editor – <?= htmlspecialchars($item['itemName']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet" href="/css/admin/hero.css">
<script src="/js/admin/hero.js" defer></script>

</head>

<body>

<div class="admin-container">

    <header class="admin-header">
        <div class="admin-title">Hero Editor – <?= htmlspecialchars($item['itemName']) ?></div>
        <div class="admin-subtitle">
            Select the best hero image for this item. You may override, reject the auto choice, or clear an override.
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="flash flash-<?= htmlspecialchars($flashType) ?>">
            <span class="flash-dot"></span>
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <h2 class="card-title">Active Hero Image</h2>
    <div class="card-imagebox">
        <?= admin_render_image($activeHero, "Active hero") ?>
    </div>

    <div style="margin-top: 14px;">
        <form method="post" style="display:inline;">
            <button class="btn-ghost" name="clear_override">Clear Override</button>
        </form>
        &nbsp;
        <a href="hero-manager.php" class="btn-ghost">Back to Manager</a>
    </div>

    <hr style="border-color:#2d3a55;margin:20px 0;">

    <h2 class="card-title">All Candidate Images</h2>

    <div class="card-grid">
    <?php foreach ($candidates as $c): ?>
        <div class="candidate-tile">

            <?= admin_render_thumbnail_safe($c['path'], $item['itemName'] . " candidate") ?>

            <div class="candidate-score">
                Score: <?= number_format($c['score'],1) ?><br>
                Ratio: <?= $c['ratio'] ? number_format($c['ratio'],3) : '—' ?>
            </div>

            <form method="post">
                <button class="btn-primary" name="override" value="<?= htmlspecialchars($c['path']) ?>">
                    Make Hero
                </button>
            </form>

            <?php if ($best && $best['path'] === $c['path']): ?>
            <form method="post">
                <button class="btn-danger" name="reject" value="<?= htmlspecialchars($c['path']) ?>">
                    Reject Auto Choice
                </button>
            </form>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
    </div>

</div>

</body>
</html>





