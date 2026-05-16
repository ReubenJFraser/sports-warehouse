<?php
// /admin/hero-edit.php
// Per-item hero editor with manual override + auto-choice rejection logging.

require __DIR__ . '/../db.php';
require __DIR__ . '/image-helper.php';
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../inc/hero/authority.php';

// --------------------------------------------------------
// Guard: require ?id=ITEM_ID
// --------------------------------------------------------
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($itemId <= 0) {
    admin_layout_start("Hero Editor");
    ?>
    <div class="admin-wrapper">
        <p class="flash flash--error">
            <span class="flash__pill"></span>
            <span>Missing or invalid <code>id</code> parameter.</span>
        </p>
    </div>
    <?php
    admin_layout_end();
    exit;
}

// --------------------------------------------------------
// Shared scoring helper (aligned with hero-manager.php)
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

        $ratio     = isset($h['ratio']) ? (float)$h['ratio'] : 0.0;
        $headroom  = isset($h['headroom_pct']) ? (float)$h['headroom_pct'] : null;
        $focusY    = isset($h['focus_y_pct']) ? (float)$h['focus_y_pct'] : null;
        $cropSafe  = isset($h['crop_safe']) ? (int)$h['crop_safe'] : 0;
        $faceCount = isset($h['face_count']) ? (int)$h['face_count'] : 0;

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
            $score -= min(20.0, $diff * 40.0);
        }

        // 3) Focus Y: prefer upper-to-middle band, say 12–32%
        if ($focusY !== null) {
            $ideal = 22.0;
            $dist  = abs($focusY - $ideal);
            $score -= min(15.0, $dist * 0.7);
        }

        // 4) Mild boost if headroom_pct in sane range (6–20)
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
// 1) Load item
// --------------------------------------------------------
$sql = "
    SELECT
        itemId,
        itemName,
        brand,
        chosen_image,
        thumbnails_json,
        chosen_ratio,
        hero_image,
        hero_score,
        hero_ratio,
        hero_orientation
    FROM item
    WHERE itemId = :id
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    admin_layout_start("Hero Editor");
    ?>
    <div class="admin-wrapper">
        <p class="flash flash--error">
            <span class="flash__pill"></span>
            <span>Item #<?= htmlspecialchars((string)$itemId) ?> not found.</span>
        </p>
    </div>
    <?php
    admin_layout_end();
    exit;
}

$itemName   = (string)$item['itemName'];
$brand      = (string)($item['brand'] ?? '');
$heroImage  = trim((string)($item['hero_image'] ?? ''));
$heroScore  = $item['hero_score'] !== null ? (float)$item['hero_score'] : null;
$heroRatio  = $item['hero_ratio'] !== null ? (float)$item['hero_ratio'] : null;
$heroOrient = strtoupper(trim((string)($item['hero_orientation'] ?? '')));
$chosen     = trim((string)($item['chosen_image'] ?? ''));
$thumbsRaw  = (string)($item['thumbnails_json'] ?? '');
$selectedFromQuery = trim((string)($_GET['select'] ?? ''));
$selectedFromQueryValid = false;

// --------------------------------------------------------
// 2) Build candidate list (chosen_image + thumbnails_json)
// --------------------------------------------------------
$candidates = [];
$seen       = [];

$addCandidate = function (string $path, string $source) use (&$candidates, &$seen) {
    $path = trim($path);
    if ($path === '') return;
    if (isset($seen[$path])) return;
    $seen[$path] = true;

    $basename = strtolower(substr($path, strrpos($path, '/') + 1));
    if ($basename === '') return;

    $candidates[] = [
        'path'     => $path,
        'basename' => $basename,
        'source'   => $source,
    ];
};

if ($chosen !== '') {
    $addCandidate($chosen, 'chosen');
}

if ($thumbsRaw !== '') {
    $parts = array_filter(array_map('trim', explode(';', $thumbsRaw)));
    foreach ($parts as $p) {
        $addCandidate($p, 'thumb');
    }
}

if ($selectedFromQuery !== '') {
    foreach ($candidates as $candidate) {
        if ((string)$candidate['path'] === $selectedFromQuery) {
            $selectedFromQueryValid = true;
            break;
        }
    }
}

// --------------------------------------------------------
// 3) Load headroom rows and score candidates
// --------------------------------------------------------
$headroomMap = [];

if (!empty($candidates)) {
    $bases = [];
    foreach ($candidates as $c) {
        $bases[$c['basename']] = true;
    }
    $bases = array_values($bases);

    if (!empty($bases)) {
        $placeholders = [];
        $params       = [];
        foreach ($bases as $i => $b) {
            $ph = ':b' . $i;
            $placeholders[] = $ph;
            $params[$ph]    = $b;
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
            WHERE image_basename IN (" . implode(',', $placeholders) . ")
        ";
        $stmtH = $pdo->prepare($sqlH);
        $stmtH->execute($params);
        $rows = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $h) {
            $key = strtolower($h['image_basename']);
            $headroomMap[$key] = $h;
        }
    }
}

// First pass: attach scores + ratios
foreach ($candidates as &$c) {
    $hr = $headroomMap[$c['basename']] ?? null;
    $score = sw_score_candidate($hr);

    $c['score'] = $score;
    $c['ratio'] = ($hr && isset($hr['ratio']) && $hr['ratio'] > 0)
        ? (float)$hr['ratio']
        : null;
}
unset($c);

// Find best auto candidate (for rejection logging)
$bestCandidate = null;
$bestScore     = -INF;
foreach ($candidates as $c) {
    if ($c['score'] > $bestScore) {
        $bestScore     = $c['score'];
        $bestCandidate = $c;
    }
}

// Sort candidates by score desc for display
usort($candidates, function ($a, $b) {
    return ($b['score'] <=> $a['score']);
});

// --------------------------------------------------------
// 4) Handle POST actions: override / reject / clear override
// --------------------------------------------------------
$flashMessage = '';
$flashType    = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action       = $_POST['action'] ?? '';
    $overridePath = trim((string)($_POST['override_image'] ?? ''));
    $rejectPath   = trim((string)($_POST['reject_image'] ?? ''));

    if ($action === 'save_override') {
        if ($overridePath === '') {
            $flashMessage = 'Please select a candidate tile before saving an override.';
            $flashType    = 'error';
        } else {

            // ============================================================
            // HERO AUTHORITY GUARD — MANUAL EDITORIAL
            // ============================================================
            if (!HeroAuthority::canWrite($item, HeroAuthority::SOURCE_MANUAL)) {
                throw new RuntimeException('Manual hero write rejected by authority guard');
            }

            $sql = "
                INSERT INTO hero_override (itemId, chosen_image)
                VALUES (:id, :img)
                ON DUPLICATE KEY UPDATE chosen_image = VALUES(chosen_image)
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id'  => $itemId,
                ':img' => $overridePath,
            ]);

            header('Location: hero-manager.php?hero_saved=1&item_id=' . $itemId);
            exit;
        }

    } elseif ($action === 'clear_override') {
        $sql = "DELETE FROM hero_override WHERE itemId = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $itemId]);

        $flashMessage = 'Override cleared for item #' . $itemId . '.';
        $flashType    = 'info';

    } elseif ($action === 'reject_auto') {
        // Preserve existing behaviour: log into hero_rejections with score
        $img   = $rejectPath;
        $score = null;

        if ($img !== '') {
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
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id'  => $itemId,
                ':img' => $img,
                ':s'   => $score,
            ]);

            $flashMessage = 'Automatically selected image rejected for item #' . $itemId . '.';
            $flashType    = 'error';
        } else {
            $flashMessage = 'No auto-selected image to reject.';
            $flashType    = 'error';
        }
    }
}

// --------------------------------------------------------
// 5) Determine active hero (override → hero_image → chosen_image)
// --------------------------------------------------------
// Read override again so page reflects latest POST state
$overStmt = $pdo->prepare("SELECT chosen_image FROM hero_override WHERE itemId = :id");
$overStmt->execute([':id' => $itemId]);
$override = trim((string)$overStmt->fetchColumn());

$effectiveOverride = $override;
if ($selectedFromQueryValid) {
    $effectiveOverride = $selectedFromQuery;
    if ($flashMessage === '') {
        $flashMessage = 'Candidate staged. Click “Save override” to apply this hero.';
        $flashType = 'info';
    }
} elseif ($selectedFromQuery !== '' && $flashMessage === '') {
    $flashMessage = 'Requested candidate was not found for this item.';
    $flashType = 'error';
}

$activeHero = $override ?: $heroImage ?: $chosen;

$normalizeHeroPath = static function (string $path): string {
    $normalized = trim($path);
    if ($normalized === '') {
        return '';
    }

    $normalized = preg_replace('#^[a-z]+://#i', '', $normalized);
    $normalized = preg_replace('#/{2,}#', '/', $normalized);

    return strtolower(rtrim($normalized, '/'));
};

$activeHeroNormalized = $normalizeHeroPath($activeHero);
$storedHeroNormalized = $normalizeHeroPath($heroImage);
$savedHeroMatchesActiveHero = ($activeHeroNormalized !== '' && $storedHeroNormalized !== '' && $activeHeroNormalized === $storedHeroNormalized);
$showSavedHeroComparisonCard = ($heroImage !== '' && !$savedHeroMatchesActiveHero);

// Label for stored hero orientation
$orientLabel = 'Portrait';
if ($heroOrient === 'L') {
    $orientLabel = 'Landscape';
} elseif ($heroOrient === 'S') {
    $orientLabel = 'Square';
}

// Best candidate path for "Reject auto choice" button
$bestPathForReject = $bestCandidate ? $bestCandidate['path'] : '';
$stagedLabel = $effectiveOverride !== '' ? $effectiveOverride : 'No candidate selected';
$effectiveOverrideNormalized = $normalizeHeroPath($effectiveOverride);
$savedOverrideNormalized = $normalizeHeroPath($override);
$selectedOverrideIsSaved = ($effectiveOverrideNormalized !== '' && $effectiveOverrideNormalized === $savedOverrideNormalized);
$stagedStatus = 'Select a candidate, then save override';
if ($effectiveOverride !== '') {
    $stagedStatus = $selectedOverrideIsSaved
        ? 'Saved as current override.'
        : 'Ready to save';
}
$activeHeroRankText = 'Current hero rank is unavailable.';
$activeCriteriaProfileText = '';
$rankingBasisText = 'Ranking basis is a temporary legacy ranking.';
$showHeroContextDiagnostic = false;
$heroContextText = '';

if ($showHeroContextDiagnostic) {
    $heroContextParts = [];

    if ($activeHero !== '') {
        $heroContextParts[] = 'Current hero in use';
    }
    if ($heroImage !== '') {
        $heroContextParts[] = 'Stored hero image available';
    }
    if ($override !== '') {
        $heroContextParts[] = 'Manual override currently saved';
    }
    if ($selectedFromQueryValid) {
        $heroContextParts[] = 'Review selection staged (not yet saved)';
    }

    $heroContextText = !empty($heroContextParts) ? implode(' · ', $heroContextParts) : '';
}
// --------------------------------------------------------
// 6) Render layout
// --------------------------------------------------------
admin_layout_start("Hero Editor");
?>
<link rel="stylesheet" href="/css/admin/hero.css">

<div class="admin-wrapper hero-admin">
    <header class="admin-header">
        <h1>Hero Editor</h1>
        <p class="subtitle">
            Item #<?= $itemId ?> —
            <strong><?= htmlspecialchars($itemName) ?></strong>
            <?php if ($brand !== ''): ?>
                <span style="color: var(--text-muted);"> · <?= htmlspecialchars($brand) ?></span>
            <?php endif; ?>
        </p>
    </header>

    <?php if ($flashMessage): ?>
        <div class="flash flash--<?= htmlspecialchars($flashType) ?>">
            <span class="flash__pill"></span>
            <span><?= $flashMessage ?></span>
        </div>
    <?php endif; ?>

    <section class="card mb-3 hero-diagnostics" data-shortlist-diagnostics data-item-id="<?= $itemId ?>">
        <h2 style="font-size:1.0rem;margin:0 0 8px;">Shortlist diagnostics</h2>
        <div class="hero-diagnostics__grid">
            <div class="hero-diagnostics__item">
                <div class="hero-diagnostics__label">Current hero rank</div>
                <div class="hero-diagnostics__value" data-diagnostic-rank><?= htmlspecialchars($activeHeroRankText) ?></div>
            </div>
            <div class="hero-diagnostics__item" data-diagnostic-context-item<?= $showHeroContextDiagnostic ? '' : ' hidden' ?>>
                <div class="hero-diagnostics__label">Hero context</div>
                <div class="hero-diagnostics__value" data-diagnostic-context><?= htmlspecialchars($heroContextText) ?></div>
            </div>
            <div class="hero-diagnostics__item">
                <div class="hero-diagnostics__label">Ranking basis</div>
                <div class="hero-diagnostics__value" data-diagnostic-basis><?= htmlspecialchars($rankingBasisText) ?></div>
            </div>
        </div>
        <div class="hero-diagnostics__hidden" hidden data-diagnostic-profile><?= htmlspecialchars($activeCriteriaProfileText) ?></div>
    </section>

    <!-- Current hero / override summary -->
    <section class="card mb-3">
        <h2 style="font-size:1.0rem;margin:0 0 10px;">Active hero state</h2>

        <div class="grid <?= $showSavedHeroComparisonCard ? 'grid-2' : 'grid-1' ?> hero-active-state-grid">
            <div>
                <div class="hero-slot__label hero-active-state__label">
                    <span>Current site hero</span>
                    <span class="hero-slot__tag">
                        <?= $activeHero !== '' ? 'In use' : 'Not set' ?>
                    </span>
                </div>
                <div class="hero-slot__imgwrap mt-1">
                    <?php if ($activeHero !== ''): ?>
                        <?= admin_render_thumbnail_safe($activeHero, "$itemName – active hero") ?>
                    <?php else: ?>
                        <span class="hero-slot__empty">
                            No hero frame selected; frontend will fall back to thumbnails/chosen image.
                        </span>
                    <?php endif; ?>
                </div>
                <div class="hero-slot__meta hero-active-state__meta">
                    <span>ratio:
                        <?php if ($heroRatio !== null): ?>
                            <?= number_format($heroRatio, 3) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </span>
                    <?php if ($heroOrient !== ''): ?>
                        <span><?= strtolower($orientLabel) ?></span>
                    <?php endif; ?>
                    <span>score:
                        <?php if ($heroScore !== null): ?>
                            <?= number_format($heroScore, 1) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <?php if ($showSavedHeroComparisonCard): ?>
                <div>
                    <div class="hero-slot__label hero-active-state__label">
                        <span>Saved DB hero</span>
                        <span class="hero-slot__tag">Differs</span>
                    </div>
                    <div class="hero-slot__imgwrap mt-1">
                        <?= admin_render_thumbnail_safe($heroImage, "$itemName – stored hero") ?>
                    </div>
                    <div class="hero-slot__meta hero-active-state__meta">
                        <span>Saved DB hero differs from current site hero.</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($savedHeroMatchesActiveHero): ?>
            <div class="hero-active-state__status">Saved DB hero matches current site hero.</div>
        <?php endif; ?>
        <div class="hero-card__actions hero-editor-actions mt-2">
            <div class="hero-editor-actions__row">
                <form method="post">
                    <button
                        type="submit"
                        name="action"
                        value="clear_override"
                        class="btn btn-ghost"
                    >
                        Clear override
                    </button>
                </form>

                <a href="hero-manager.php" class="btn btn-ghost">
                    Back to Hero Manager
                </a>
            </div>
        </div>
    </section>

    <!-- Candidate list + override / reject controls -->
    <form method="post" class="card hero-override-form" id="heroOverrideForm">
        <input type="hidden" name="override_image" id="overrideImageInput"
               value="<?= htmlspecialchars($effectiveOverride) ?>">
        <input type="hidden" name="reject_image" value="<?= htmlspecialchars($bestPathForReject) ?>">

        <section class="hero-override-summary hero-override-panel" data-override-summary>
            <h2>Selected override candidate</h2>
            <div class="hero-override-summary__body hero-override-panel__body">
                <div class="hero-override-summary__preview hero-override-panel__preview hero-slot__imgwrap" data-override-preview-wrap>
                    <?php if ($effectiveOverride !== ''): ?>
                        <?= admin_render_thumbnail_safe($effectiveOverride, "$itemName – staged override") ?>
                    <?php else: ?>
                        <span class="hero-slot__empty" data-override-preview-empty>No candidate selected</span>
                    <?php endif; ?>
                </div>
                <div class="hero-override-summary__meta hero-override-panel__details">
                    <div class="hero-override-summary__path" data-override-path><?= htmlspecialchars($stagedLabel) ?></div>
                    <div class="hero-override-summary__status" data-override-status><?= htmlspecialchars($stagedStatus) ?></div>
                    <div class="hero-override-summary__actions hero-override-panel__actions">
                        <button type="submit" name="action" value="save_override" class="btn btn-primary" data-save-override<?= ($effectiveOverride === "" || $selectedOverrideIsSaved) ? " disabled" : "" ?>>
                            Save override
                        </button>
                        <?php if ($bestPathForReject !== ''): ?>
                            <button type="submit" name="action" value="reject_auto" class="btn btn-danger">
                                Reject auto choice
                            </button>
                        <?php endif; ?>
                        <a href="hero-manager.php" class="btn btn-ghost">Back to Hero Manager</a>
                    </div>
                </div>
            </div>
        </section>

        <h2 class="hero-override-candidates-heading" style="font-size:1.0rem;margin:0 0 10px;">All candidate images</h2>

        <?php if (empty($candidates)): ?>
            <p class="hero-empty">
                No candidate images found from <code>chosen_image</code> or <code>thumbnails_json</code>.
            </p>
        <?php else: ?>
            <div class="card-grid card-grid--candidates">
                <?php foreach ($candidates as $cand):
                    $path       = $cand['path'];
                    $src        = $cand['source'];
                    $score      = $cand['score'];
                    $ratio      = $cand['ratio'];
                    $isSelected = ($effectiveOverride !== '' && $effectiveOverride === $path)
                        || ($effectiveOverride === '' && $heroImage !== '' && $heroImage === $path);
                    ?>
                    <article class="candidate-tile candidate<?= $isSelected ? ' candidate--selected' : '' ?>" data-candidate-card data-candidate-path="<?= htmlspecialchars($path) ?>" data-is-active-hero="<?= ($activeHero === $path) ? '1' : '0' ?>" data-is-stored-hero="<?= ($heroImage === $path) ? '1' : '0' ?>">
                        <div class="hero-slot__label">
                            <span><?= $src === 'chosen' ? 'Chosen image' : 'Thumbnail' ?></span>
                            <?php if ($activeHero === $path): ?><span class="hero-badge hero-badge--manual">Active hero</span><?php endif; ?>
                            <?php if ($heroImage === $path): ?><span class="hero-badge">Stored hero_image</span><?php endif; ?>
                            <span class="hero-slot__tag">
                                <?= $src === 'chosen' ? 'Primary' : 'Alt' ?>
                            </span>
                        </div>

                        <div class="card-imagebox mt-1">
                            <?= admin_render_thumbnail_safe($path, "$itemName – candidate") ?>
                        </div>

                        <div class="candidate-score">
                            Score: <?= number_format($score, 2) ?> (historical)
                            <span class="micro-label">Not current shortlist rank</span>
                            <div>Ratio: <?= $ratio !== null ? number_format($ratio, 3) : '—' ?></div>
                        </div>
                        
                        <button
                            type="button"
                            class="btn btn-ghost"
                            data-select-hero="<?= htmlspecialchars($path) ?>"
                        >
                            <span class="btn__dot"></span>
                            Use this hero
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </form>
</div>

<!-- Hero JS: fullscreen viewer + candidate selection -->
<script>
  window.BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/js/admin/hero.js"></script>

<?php
admin_layout_end();
