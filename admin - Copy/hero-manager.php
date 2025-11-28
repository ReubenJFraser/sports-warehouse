<?php
// /admin/hero-manager.php
// View hero images, scores, overrides, and allow recalculation.

require __DIR__ . '/../db.php';
require __DIR__ . '/image-helper.php';

// Admin Layout Wrapper
require_once __DIR__ . '/_layout.php';

/* ============================================================
   1. SCORING LOGIC — UNCHANGED
   ============================================================ */
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

        if ($faceCount > 0 && $cropSafe === 1) {
            $score += 70.0;
        } elseif ($faceCount > 0) {
            $score += 45.0;
        } elseif ($cropSafe === 1) {
            $score += 30.0;
        } else {
            $score += 10.0;
        }

        if ($ratio > 0) {
            $diff = abs($ratio - 0.8);
            $score -= min(20.0, $diff * 40.0);
        }

        if ($focusY !== null) {
            $dist = abs($focusY - 22.0);
            $score -= min(15.0, $dist * 0.7);
        }

        if ($headroom !== null) {
            if ($headroom >= 6 && $headroom <= 20) {
                $score += 5.0;
            } elseif ($headroom < 3 || $headroom > 30) {
                $score -= 3.0;
            }
        }

        return $score;
    }
}

/* ============================================================
   2. RE-CALC HERO FIELDS — UNCHANGED
   ============================================================ */
if (!function_exists('sw_recalc_hero_for_item')) {
    function sw_recalc_hero_for_item(PDO $pdo, int $itemId): ?array
    {
        $sql = "
            SELECT itemId, itemName, chosen_image, thumbnails_json, chosen_ratio, brand
            FROM item
            WHERE itemId = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) return null;

        $candidates = [];
        $seen = [];

        $add = function(string $path, string $source) use (&$candidates, &$seen) {
            $path = trim($path);
            if ($path === '' || isset($seen[$path])) return;
            $seen[$path] = true;

            $basename = strtolower(substr($path, strrpos($path, '/') + 1));
            if ($basename === '') return;

            $candidates[] = [
                'path'     => $path,
                'basename' => $basename,
                'source'   => $source,
            ];
        };

        if (!empty($item['chosen_image'])) {
            $add($item['chosen_image'], 'chosen');
        }

        if (!empty($item['thumbnails_json'])) {
            foreach (array_filter(array_map('trim', explode(';', $item['thumbnails_json']))) as $p) {
                $add($p, 'thumb');
            }
        }

        if (empty($candidates)) return null;

        $bases = array_unique(array_column($candidates, 'basename'));

        $placeholders = [];
        $params = [];
        foreach ($bases as $i => $b) {
            $ph = ":b$i";
            $placeholders[] = $ph;
            $params[$ph] = $b;
        }

        $sqlH = "
            SELECT image_basename, ratio, headroom_pct, focus_y_pct, crop_safe, face_count
            FROM image_headroom
            WHERE image_basename IN (" . implode(',', $placeholders) . ")
        ";
        $stmtH = $pdo->prepare($sqlH);
        $stmtH->execute($params);

        $hrows = $stmtH->fetchAll(PDO::FETCH_ASSOC);
        $hmap = [];
        foreach ($hrows as $h) {
            $hmap[strtolower($h['image_basename'])] = $h;
        }

        $best = null;
        $bestScore = -INF;

        foreach ($candidates as $c) {
            $hr = $hmap[$c['basename']] ?? null;
            $score = sw_score_candidate($hr);

            $ratio = null;
            if ($hr && $hr['ratio'] > 0) {
                $ratio = (float)$hr['ratio'];
            } elseif (!empty($item['chosen_ratio']) && $c['source'] === 'chosen') {
                $ratio = (float)$item['chosen_ratio'];
            }

            $c['score'] = $score;
            $c['ratio'] = $ratio;

            if ($score > $bestScore) {
                $best = $c;
                $bestScore = $score;
            }
        }

        if (!$best) return null;

        $ratio = $best['ratio'] ?? null;
        $orient = 'P';
        if ($ratio !== null && $ratio > 0) {
            if (abs($ratio - 1.0) < 0.05) $orient = 'S';
            elseif ($ratio > 1.05) $orient = 'L';
        }

        $sqlU = "
            UPDATE item
            SET hero_image = :hero_image,
                hero_score = :hero_score,
                hero_ratio = :hero_ratio,
                hero_orientation = :hero_orientation
            WHERE itemId = :id
            LIMIT 1
        ";
        $pdo->prepare($sqlU)->execute([
            ':hero_image'       => $best['path'],
            ':hero_score'       => $best['score'],
            ':hero_ratio'       => $ratio,
            ':hero_orientation' => $orient,
            ':id'               => $itemId,
        ]);

        return [
            'itemId'           => $itemId,
            'itemName'         => $item['itemName'],
            'brand'            => $item['brand'] ?? '',
            'hero_image'       => $best['path'],
            'hero_score'       => $best['score'],
            'hero_ratio'       => $ratio,
            'hero_orientation' => $orient,
        ];
    }
}

/* ============================================================
   3. HANDLE RECALC ACTION
   ============================================================ */
$flashMessage = '';
$flashType = 'info';

if (!empty($_GET['recalc'])) {
    $id = (int)$_GET['recalc'];
    $res = sw_recalc_hero_for_item($pdo, $id);

    if ($res) {
        $flashMessage = 'Recalculated hero for item #' . $res['itemId'] .
                        ' — “' . htmlspecialchars($res['itemName']) . '”.';
        $flashType = 'success';
    } else {
        $flashMessage = "Unable to recalculate hero for item #$id.";
        $flashType = 'error';
    }
}

/* ============================================================
   4. FETCH ITEMS
   ============================================================ */
$sql = "
  SELECT
    i.itemId,
    i.itemName,
    i.brand,
    COALESCE(i.hero_image, i.chosen_image) AS auto_hero_image,
    i.hero_score,
    i.hero_ratio,
    i.hero_orientation,
    i.chosen_image,
    ho.chosen_image AS override_image,
    (SELECT COUNT(*) FROM hero_rejections r WHERE r.itemId = i.itemId) AS rejection_count
  FROM item i
  LEFT JOIN hero_override ho ON ho.itemId = i.itemId
  WHERE i.is_active = 1
  ORDER BY i.brand, i.itemName
";

$items = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   5. LAYOUT START
   ============================================================ */
admin_layout_start("Hero Manager");
?>

<link rel="stylesheet" href="/css/admin/hero.css">

<!-- ============================================================
     Breadcrumbs
     ============================================================ -->
<nav class="admin-breadcrumbs">
    <a href="/admin/index.php">Admin</a>
    <span>/</span>
    <a href="/admin/hero-manager.php">Hero Tools</a>
    <span>/</span>
    <strong>Hero Manager</strong>
</nav>

<div class="admin-wrapper hero-admin">

    <!-- ========================================================
         Header
         ======================================================== -->
    <header class="admin-header hero-admin__header">
        <h1><i class="fa-solid fa-images"></i> Hero Manager</h1>
        <p class="subtitle">
            Review all computed hero fields and regenerate them when required.
        </p>

        <button type="button" class="btn btn-ghost hero-info-toggle" data-target="heroInfoPanel">
            <span class="btn__dot"></span> Show details
        </button>
    </header>

    <!-- ========================================================
         Collapsible Info Panel
         ======================================================== -->
    <section id="heroInfoPanel" class="panel hero-info-panel is-collapsed">
        <p>
            This tool manages <code>hero_image</code>, <code>hero_score</code>,
            <code>hero_ratio</code>, and <code>hero_orientation</code>. Cards use
            <code>hero_image</code> unless a manual override exists.
        </p>

        <p><strong>Recalculation process:</strong></p>
        <ul>
            <li>Collect candidate images.</li>
            <li>Fetch headroom analysis.</li>
            <li>Score all candidates.</li>
            <li>Write best match to <code>item</code>.</li>
        </ul>
    </section>

    <!-- ========================================================
         Flash Message
         ======================================================== -->
    <?php if ($flashMessage): ?>
        <div class="flash flash--<?= htmlspecialchars($flashType) ?> mb-3">
            <div class="flash__pill"></div>
            <div><?= $flashMessage ?></div>
        </div>
    <?php endif; ?>

    <!-- ========================================================
         Item List
         ======================================================== -->
    <?php if (empty($items)): ?>
        <p class="hero-empty">No active items found.</p>

    <?php else: ?>
        <div class="hero-list">

            <?php foreach ($items as $row):
                $itemId = (int)$row['itemId'];
                $itemName = $row['itemName'];
                $brand = $row['brand'] ?? '';
                $autoImg = $row['auto_hero_image'] ?: $row['chosen_image'];
                $override = $row['override_image'] ?? '';
                $score = $row['hero_score'];
                $ratio = $row['hero_ratio'];
                $orient = strtoupper($row['hero_orientation'] ?? '');
                $rejects = (int)$row['rejection_count'];

                $orientLabel = $orient === 'L' ? 'Landscape'
                              : ($orient === 'S' ? 'Square' : 'Portrait');
            ?>

            <section class="hero-card">
                <div class="hero-card__left">
                    <div class="hero-card__id">#<?= $itemId ?></div>

                    <div class="hero-card__meta">
                        <div class="hero-card__name"><?= htmlspecialchars($itemName) ?></div>

                        <?php if ($brand): ?>
                            <div class="hero-card__brand"><?= htmlspecialchars($brand) ?></div>
                        <?php endif; ?>

                        <!-- badges -->
                        <div class="hero-card__badges">

                            <span class="hero-badge">
                                <?= $score !== null ? "Score: " . number_format($score, 1) : "No score" ?>
                            </span>

                            <span class="hero-badge"><?= $orientLabel ?></span>

                            <?php if ($override): ?>
                                <span class="hero-badge hero-badge--override">Override active</span>
                            <?php endif; ?>

                            <?php if ($rejects > 0): ?>
                                <span class="hero-badge hero-badge--rejections"><?= $rejects ?> rejected</span>
                            <?php endif; ?>

                            <?php if ($autoImg && !admin_image_exists($autoImg)): ?>
                                <span class="hero-badge hero-badge--missing">Missing (auto)</span>
                            <?php endif; ?>

                            <?php if ($override && !admin_image_exists($override)): ?>
                                <span class="hero-badge hero-badge--missing">Missing (override)</span>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="hero-card__images">

                    <!-- Auto hero -->
                    <div class="hero-slot">
                        <div class="hero-slot__label">
                            <span>Auto hero</span>
                            <span class="hero-slot__tag"><?= $orientLabel ?></span>
                        </div>

                        <div class="hero-slot__imgwrap">
                            <?php if ($autoImg): ?>
                                <?= admin_render_thumbnail_safe($autoImg, "$itemName – auto hero") ?>
                            <?php else: ?>
                                <span class="hero-slot__empty">No auto hero</span>
                            <?php endif; ?>
                        </div>

                        <div class="hero-slot__meta">
                            <span>ratio: <?= $ratio !== null ? number_format($ratio, 3) : '—' ?></span>
                            <span>score: <?= $score !== null ? number_format($score, 1) : '—' ?></span>
                        </div>
                    </div>

                    <!-- Override -->
                    <div class="hero-slot">
                        <div class="hero-slot__label">
                            <span>Override</span>
                            <span class="hero-slot__tag"><?= $override ? "Manual" : "None" ?></span>
                        </div>

                        <div class="hero-slot__imgwrap">
                            <?php if ($override): ?>
                                <?= admin_render_thumbnail_safe($override, "$itemName – override") ?>
                            <?php else: ?>
                                <span class="hero-slot__empty">No override set</span>
                            <?php endif; ?>
                        </div>

                        <div class="hero-slot__meta">
                            <span>rejected:</span>
                            <span><?= $rejects ?></span>
                        </div>
                    </div>

                </div>

                <!-- Buttons -->
                <div class="hero-card__actions">
                    <a class="btn btn-primary" href="hero-edit.php?id=<?= $itemId ?>">
                        <span class="btn__dot"></span> Edit hero
                    </a>

                    <a class="btn btn-ghost" href="hero-manager.php?recalc=<?= $itemId ?>">
                        Recalculate
                    </a>
                </div>

            </section>

            <?php endforeach; ?>
        </div>

        <p class="hero-footnote">
            Public product cards use <code>hero_image</code> if set. If not, they fall back
            to <code>chosen_image</code>. Manual overrides always win visually.
        </p>

    <?php endif; ?>
</div>

<script src="/js/admin/hero.js"></script>

<?php admin_layout_end();




