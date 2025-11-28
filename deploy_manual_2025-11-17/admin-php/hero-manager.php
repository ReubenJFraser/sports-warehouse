<?php
// /admin/hero-manager.php
// Mobile-first admin list to view hero images, scores, overrides,
// and trigger per-item recalculation of hero_* fields.

require __DIR__ . '/../db.php';
require __DIR__ . '/image-helper.php';

// --------------------------------------------------------
// Helper: score an image candidate from image_headroom row
// --------------------------------------------------------
if (!function_exists('sw_score_candidate')) {
    /**
     * $h is an array from image_headroom:
     *  - ratio
     *  - headroom_pct
     *  - focus_y_pct
     *  - crop_safe
     *  - face_count
     */
    function sw_score_candidate(?array $h): float
    {
        if (!$h) {
            // No headroom data at all → very low confidence
            return 5.0;
        }

        $ratio       = isset($h['ratio']) ? (float)$h['ratio'] : 0.0;
        $headroom    = isset($h['headroom_pct']) ? (float)$h['headroom_pct'] : null;
        $focusY      = isset($h['focus_y_pct']) ? (float)$h['focus_y_pct'] : null;
        $cropSafe    = isset($h['crop_safe']) ? (int)$h['crop_safe'] : 0;
        $faceCount   = isset($h['face_count']) ? (int)$h['face_count'] : 0;

        $score = 0.0;

        // 1) Base score: faces + crop safety
        if ($faceCount > 0 && $cropSafe === 1) {
            $score += 70.0;
        } elseif ($faceCount > 0) {
            $score += 45.0;
        } elseif ($cropSafe === 1) {
            $score += 30.0;
        } else {
            $score += 10.0;
        }

        // 2) Aspect ratio closeness to 4:5 (0.8)
        if ($ratio > 0) {
            $target = 0.8;
            $diff   = abs($ratio - $target);
            // Gentle penalty: up to ~20 points for very wrong ratios
            $score -= min(20.0, $diff * 40.0);
        }

        // 3) Focus Y: prefer faces in upper-to-middle band, say 12–32%
        if ($focusY !== null) {
            $ideal = 22.0;  // sweet spot (around top third)
            $dist  = abs($focusY - $ideal);
            // Up to ~15 points penalty for big deviations
            $score -= min(15.0, $dist * 0.7);
        }

        // 4) Mild boost if headroom_pct is in a sane range (e.g. 6–20)
        if ($headroom !== null) {
            if ($headroom >= 6.0 && $headroom <= 20.0) {
                $score += 5.0;
            } elseif ($headroom < 3.0 || $headroom > 30.0) {
                $score -= 3.0;
            }
        }

        return $score;
    }
}

// --------------------------------------------------------
// Helper: recompute hero_* for a single item
// --------------------------------------------------------
if (!function_exists('sw_recalc_hero_for_item')) {
    function sw_recalc_hero_for_item(PDO $pdo, int $itemId): ?array
    {
        // 1) Fetch item with chosen_image + thumbnails_json + chosen_ratio (fallback ratio)
        $sql = "
            SELECT
                itemId,
                itemName,
                chosen_image,
                thumbnails_json,
                chosen_ratio
            FROM item
            WHERE itemId = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return null;
        }

        $candidates = [];
        $seenPaths  = [];

        // Helper to add candidate paths
        $addCandidate = function (string $path, string $source) use (&$candidates, &$seenPaths) {
            $path = trim($path);
            if ($path === '') return;
            // Normalize: ensure we always have something like "images/..." or full path
            // We keep the path as-is; the basename is what we match on.
            if (isset($seenPaths[$path])) return;
            $seenPaths[$path] = true;

            $basename = strtolower(substr($path, strrpos($path, '/') + 1));
            if ($basename === '') return;

            $candidates[] = [
                'path'     => $path,
                'basename' => $basename,
                'source'   => $source,
            ];
        };

        // a) Primary chosen_image (if any)
        if (!empty($item['chosen_image'])) {
            $addCandidate($item['chosen_image'], 'chosen');
        }

        // b) All thumbnails from semicolon-separated list
        if (!empty($item['thumbnails_json'])) {
            $parts = array_filter(array_map('trim', explode(';', $item['thumbnails_json'])));
            foreach ($parts as $p) {
                // Some tokens might be relative like "brands/..." → we keep as stored
                $addCandidate($p, 'thumb');
            }
        }

        if (empty($candidates)) {
            // No images -> nothing to do
            return null;
        }

        // 2) Fetch headroom rows for all candidate basenames
        $bases = [];
        foreach ($candidates as $c) {
            $bases[$c['basename']] = true;
        }
        $bases = array_keys($bases);

        if (empty($bases)) {
            return null;
        }

        $inPlaceholders = [];
        $params = [':id' => $itemId];
        foreach ($bases as $idx => $b) {
            $ph = ':b' . $idx;
            $inPlaceholders[] = $ph;
            $params[$ph] = $b;
        }

        $sqlH = "
            SELECT
                image_basename,
                ratio,
                headroom_pct,
                focus_y_pct,
                crop_safe,
                face_count
            FROM image_headroom
            WHERE image_basename IN (" . implode(',', $inPlaceholders) . ")
        ";
        $stmtH = $pdo->prepare($sqlH);
        $stmtH->execute(array_diff_key($params, [':id' => true]));
        $headroomRows = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        $headroomMap = [];
        foreach ($headroomRows as $h) {
            $key = strtolower($h['image_basename']);
            $headroomMap[$key] = $h;
        }

        // 3) Score each candidate
        $best   = null;
        $bestScore = -INF;

        foreach ($candidates as $c) {
            $b = $c['basename'];
            $hr = $headroomMap[$b] ?? null;
            $score = sw_score_candidate($hr);

            $ratio = null;
            if ($hr && isset($hr['ratio']) && $hr['ratio'] > 0) {
                $ratio = (float)$hr['ratio'];
            } elseif (!empty($item['chosen_ratio']) && $c['source'] === 'chosen') {
                // fall back to item-level chosen_ratio for the chosen image
                $ratio = (float)$item['chosen_ratio'];
            }

            $c['score'] = $score;
            $c['ratio'] = $ratio;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $c;
            }
        }

        if (!$best) {
            return null;
        }

        // 4) Derive hero_orientation from ratio
        $ratio = $best['ratio'] ?? null;
        $heroOrientation = 'P'; // default portrait
        if ($ratio !== null && $ratio > 0) {
            if (abs($ratio - 1.0) < 0.05) {
                $heroOrientation = 'S'; // square-ish
            } elseif ($ratio > 1.05) {
                $heroOrientation = 'L'; // wider than tall
            } else {
                $heroOrientation = 'P';
            }
        }

        // 5) Update item with new hero_* fields
        $sqlU = "
            UPDATE item
               SET hero_image       = :hero_image,
                   hero_score       = :hero_score,
                   hero_ratio       = :hero_ratio,
                   hero_orientation = :hero_orientation
             WHERE itemId = :id
            LIMIT 1
        ";
        $stmtU = $pdo->prepare($sqlU);
        $stmtU->execute([
            ':hero_image'       => $best['path'],
            ':hero_score'       => $best['score'],
            ':hero_ratio'       => $ratio,
            ':hero_orientation' => $heroOrientation,
            ':id'               => $itemId,
        ]);

        return [
            'itemId'          => $itemId,
            'itemName'        => $item['itemName'],
            'hero_image'      => $best['path'],
            'hero_score'      => $best['score'],
            'hero_ratio'      => $ratio,
            'hero_orientation'=> $heroOrientation,
        ];
    }
}

// --------------------------------------------------------
// Handle per-item recalc action (?recalc=ID)
// --------------------------------------------------------
$flashMessage = '';
$flashType    = 'info';

$recalcId = isset($_GET['recalc']) ? (int)$_GET['recalc'] : 0;
if ($recalcId > 0) {
    $res = sw_recalc_hero_for_item($pdo, $recalcId);
    if ($res) {
        $flashMessage = 'Recalculated hero for item #' . (int)$res['itemId'] . ' — “' . htmlspecialchars($res['itemName']) . '”.';
        $flashType    = 'success';
    } else {
        $flashMessage = 'Unable to recalculate hero for item #' . $recalcId . ' (no item or no candidates).';
        $flashType    = 'error';
    }
}

// --------------------------------------------------------
// Fetch items with hero + overrides + rejection counts
// --------------------------------------------------------
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
$stmt = $pdo->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Hero Manager | Sports Warehouse';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Minimal mobile-first admin styling -->
  <link rel="stylesheet" href="/css/admin/hero.css">
</head>
<body>
  <div class="hero-admin">
    <header class="hero-admin__header">
      <div class="hero-admin__title">Hero Image Manager</div>
      <div class="hero-admin__subtitle">
        Uses <strong>stored hero fields</strong> for product cards.
        Tap <strong>Recalculate</strong> on any item to regenerate its
        <code>hero_image</code>, <code>hero_score</code>, <code>hero_ratio</code>, and <code>hero_orientation</code>
        from <code>image_headroom</code>. Manual overrides (if set) are shown side-by-side.
      </div>
    </header>

    <?php if ($flashMessage): ?>
      <div class="flash flash--<?= htmlspecialchars($flashType) ?>">
        <div class="flash__pill"></div>
        <div><?= $flashMessage ?></div>
      </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
      <p class="hero-empty">No active items found.</p>
    <?php else: ?>
      <div class="hero-list">
        <?php foreach ($items as $row):
          $itemId   = (int)$row['itemId'];
          $itemName = (string)$row['itemName'];
          $brand    = (string)($row['brand'] ?? '');
          $autoImg  = trim((string)($row['auto_hero_image'] ?: $row['chosen_image'] ?: ''));
          $override = trim((string)($row['override_image'] ?? ''));
          $score    = $row['hero_score'] !== null ? (float)$row['hero_score'] : null;
          $ratio    = $row['hero_ratio'] !== null ? (float)$row['hero_ratio'] : null;
          $orient   = strtoupper(trim((string)($row['hero_orientation'] ?? '')));
          $rejects  = (int)($row['rejection_count'] ?? 0);

          $orientLabel = $orient === 'L' ? 'Landscape' : ($orient === 'S' ? 'Square' : 'Portrait');
        ?>
          <section class="hero-card">
            <div class="hero-card__left">
              <div class="hero-card__id">#<?= $itemId ?></div>
              <div class="hero-card__meta">
                <div class="hero-card__name"><?= htmlspecialchars($itemName) ?></div>
                <?php if ($brand !== ''): ?>
                  <div class="hero-card__brand"><?= htmlspecialchars($brand) ?></div>
                <?php endif; ?>

                <div class="hero-card__badges">
                  <?php if ($score !== null): ?>
                    <span class="hero-badge">
                      Score: <?= number_format($score, 1) ?>
                    </span>
                  <?php else: ?>
                    <span class="hero-badge">
                      No stored score
                    </span>
                  <?php endif; ?>

                  <?php if ($orient !== ''): ?>
                    <span class="hero-badge">
                      <?= $orientLabel ?>
                    </span>
                  <?php endif; ?>

                  <?php if ($override !== ''): ?>
                    <span class="hero-badge hero-badge--override">
                      Override active
                    </span>
                  <?php endif; ?>

                  <?php if ($rejects > 0): ?>
                    <span class="hero-badge hero-badge--rejections">
                      <?= $rejects ?> rejected
                    </span>
                  <?php endif; ?>

                  <?php if ($autoImg && !admin_image_exists($autoImg)): ?>
                    <span class="hero-badge hero-badge--missing">
                        Missing file (auto)
                    </span>
                  <?php endif; ?>

                  <?php if ($override && !admin_image_exists($override)): ?>
                    <span class="hero-badge hero-badge--missing">
                        Missing file (override)
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <?php if ($rejects > 0): ?>
              <div class="hero-card__rejected-row">
                <a
                  href="hero-edit.php?id=<?= $itemId ?>&focus=candidates"
                  class="hero-reject-link">
                  Rejected images: <?= $rejects ?> → review
                </a>
              </div>
            <?php endif; ?>

          <div class="hero-card__images">

            <!-- AUTO HERO SLOT -->
            <div class="hero-slot">
              <div class="hero-slot__label">
                <span>Auto hero</span>
                <span class="hero-slot__tag">
                  <?= $orientLabel ?>
                </span>
              </div>

            <div class="hero-slot__imgwrap">
              <?php if ($autoImg !== ''): ?>
                <?= admin_render_thumbnail_safe($autoImg, "$itemName – auto hero") ?>
              <?php else: ?>
                <span style="font-size:0.75rem;color:var(--text-muted);padding:6px;">
                  No auto hero (falls back to chosen image)
                </span>
              <?php endif; ?>
            </div>

            <div class="hero-slot__meta">
              <span>ratio:
                <?php if ($ratio !== null): ?>
                  <?= number_format($ratio, 3) ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </span>
              <span>score:
                <?php if ($score !== null): ?>
                  <?= number_format($score, 1) ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </span>
            </div>

            <div class="hero-slot__inspect">
              <a
                href="hero-edit.php?id=<?= $itemId ?>&focus=auto"
                class="hero-inspect-link">
                Inspect auto choice →
              </a>
            </div>
          </div>

          <!-- END AUTO HERO SLOT -->

          <!-- OVERRIDE SLOT -->
          <div class="hero-slot">
            <div class="hero-slot__label">
              <span>Override</span>
              <span class="hero-slot__tag">
                <?= $override !== '' ? 'Manual' : 'None' ?>
              </span>
            </div>

          <div class="hero-slot__imgwrap">
            <?php if ($override !== ''): ?>
              <?= admin_render_thumbnail_safe($override, "$itemName – manual override") ?>
            <?php else: ?>
              <span style="font-size:0.75rem;color:var(--text-muted);padding:6px;">
                No override set
              </span>
            <?php endif; ?>
          </div>

          <div class="hero-slot__meta">
            <span>rejected:</span>
            <span><?= $rejects ?></span>
          </div>
        </div>
        <!-- END OVERRIDE SLOT -->

      </div> <!-- END hero-card__images -->


        <div class="hero-card__actions">
          <a class="btn btn--primary" href="hero-edit.php?id=<?= $itemId ?>">
            <span class="btn__dot"></span>
            Edit hero
          </a>

          <a class="btn btn--ghost" href="hero-manager.php?recalc=<?= $itemId ?>">
            Recalculate
          </a>
        </div>

          </section>
        <?php endforeach; ?>
      </div>

      <p class="hero-footnote">
        Note: Product cards on the public site will use
        <code>hero_image</code> (if set), falling back to <code>chosen_image</code>
        when needed. Manual overrides in <code>hero_override</code> represent the
        final, human-chosen hero even if the auto score prefers a different frame.
      </p>
    <?php endif; ?>
  </div>
</body>
</html>


