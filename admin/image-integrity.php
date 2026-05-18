<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/image-helper.php';

$selectedBrand = trim((string)($_GET['brand'] ?? ''));
$selectedSeverity = trim((string)($_GET['severity'] ?? 'all'));
$selectedSource = trim((string)($_GET['source'] ?? 'all'));
$selectedStatus = trim((string)($_GET['status'] ?? 'all'));
$selectedRepairCategory = trim((string)($_GET['repair_category'] ?? 'all'));

$allowedSeverities = ['all', 'critical', 'warning', 'info'];
$allowedSources = ['all', 'chosen_image', 'hero_image', 'thumbnail_candidate', 'override_image'];
$allowedStatuses = ['all', 'exists', 'missing'];
$allowedRepairCategories = ['all', 'clean', 'optional_blank', 'missing_brands_segment', 'malformed_brands_path', 'missing_local_file', 'all_candidates_missing', 'media_video_reference', 'unknown_missing'];

if (!in_array($selectedSeverity, $allowedSeverities, true)) $selectedSeverity = 'all';
if (!in_array($selectedSource, $allowedSources, true)) $selectedSource = 'all';
if (!in_array($selectedStatus, $allowedStatuses, true)) $selectedStatus = 'all';
if (!in_array($selectedRepairCategory, $allowedRepairCategories, true)) $selectedRepairCategory = 'all';

$sql = "
    SELECT
        i.itemId,
        i.itemName,
        i.brand,
        i.chosen_image,
        i.hero_image,
        i.thumbnails_json,
        ho.chosen_image AS override_image
    FROM item i
    LEFT JOIN hero_override ho ON ho.itemId = i.itemId
    WHERE i.is_active = 1
    ORDER BY i.brand ASC, i.itemId ASC
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$allBrands = [];
$imageRefs = [];
$duplicateMap = [];
$duplicateUsageMap = [];
$itemEffectiveHeroRawMap = [];

$referenceBuilder = static function (array $item, string $source, string $rawPath, bool $isEffectiveHero) {
    $rawPath = trim($rawPath);
    $normalizedPath = $rawPath !== '' ? admin_normalize_image_url(str_replace('\\', '/', $rawPath)) : '';
    $pathForFs = '';
    if ($normalizedPath !== '') {
        $baseTrimmed = preg_replace('#^' . preg_quote(BASE_URL, '#') . '/?#', '', $normalizedPath);
        $pathForFs = ltrim((string)$baseTrimmed, '/');
    }
    $resolvedFsPath = $pathForFs !== '' ? admin_image_fs_path($pathForFs) : '';

    return [
        'itemId' => (int)$item['itemId'],
        'itemName' => (string)$item['itemName'],
        'brand' => (string)($item['brand'] ?? ''),
        'source' => $source,
        'raw_path' => $rawPath,
        'normalized_path' => $normalizedPath,
        'path_for_fs' => $pathForFs,
        'resolved_fs_path' => $resolvedFsPath,
        'is_effective_hero' => $isEffectiveHero,
    ];
};

foreach ($rows as $item) {
    $brand = trim((string)($item['brand'] ?? ''));
    if ($brand !== '') $allBrands[$brand] = true;

    $overrideImage = trim((string)($item['override_image'] ?? ''));
    $heroImage = trim((string)($item['hero_image'] ?? ''));
    $chosenImage = trim((string)($item['chosen_image'] ?? ''));
    $effectiveHero = $overrideImage !== '' ? $overrideImage : ($heroImage !== '' ? $heroImage : $chosenImage);
    $itemEffectiveHeroRawMap[(int)$item['itemId']] = ($effectiveHero !== '');

    $refsForItem = [];
    $refsForItem[] = $referenceBuilder($item, 'chosen_image', $chosenImage, $effectiveHero !== '' && $effectiveHero === $chosenImage);
    $refsForItem[] = $referenceBuilder($item, 'hero_image', $heroImage, $effectiveHero !== '' && $effectiveHero === $heroImage);
    $refsForItem[] = $referenceBuilder($item, 'override_image', $overrideImage, $effectiveHero !== '' && $effectiveHero === $overrideImage);

    $thumbs = array_filter(array_map('trim', explode(';', (string)($item['thumbnails_json'] ?? ''))), static fn($p) => $p !== '');
    if (empty($thumbs)) {
        $refsForItem[] = $referenceBuilder($item, 'thumbnail_candidate', '', false);
    } else {
        foreach ($thumbs as $thumbPath) {
            $refsForItem[] = $referenceBuilder($item, 'thumbnail_candidate', $thumbPath, false);
        }
    }

    foreach ($refsForItem as $ref) {
        $imageRefs[] = $ref;
        if ($ref['normalized_path'] !== '') {
            $key = strtolower($ref['normalized_path']);
            $duplicateMap[$key] = ($duplicateMap[$key] ?? 0) + 1;
            $itemKey = (string)$ref['itemId'];
            $sourceKey = (string)$ref['source'];
            $duplicateUsageMap[$key] = $duplicateUsageMap[$key] ?? ['items' => [], 'sources' => []];
            $duplicateUsageMap[$key]['items'][$itemKey] = true;
            $duplicateUsageMap[$key]['sources'][$sourceKey] = ($duplicateUsageMap[$key]['sources'][$sourceKey] ?? 0) + 1;
        }
    }
}

sort($allBrands);

$summary = [
    'active_products' => count($rows),
    'references_checked' => 0,
    'missing_references' => 0,
    'critical_issues' => 0,
    'warning_issues' => 0,
    'clean_references' => 0,
];

$finalRows = [];
$itemCandidateStats = [];

foreach ($imageRefs as $ref) {
    $raw = $ref['raw_path'];
    $normalized = $ref['normalized_path'];
    $pathForFs = $ref['path_for_fs'];

    $notes = [];
    $severity = 'info';
    $status = 'missing';
    $repairCategory = 'clean';
    $suggestedPath = '';
    $sqlPreview = '';

    $isBlank = ($raw === '');
    $hasBackslashes = strpos($raw, '\\') !== false;
    $isExternal = (bool)preg_match('#^https?://#i', $raw);
    $outsideExpectedRoot = false;
    $exists = false;

    $isVideo = !$isBlank && (bool)preg_match('/\.mp4($|\?)/i', $raw);

    if ($isBlank) {
        $repairCategory = 'optional_blank';
        $notes[] = ($ref['source'] === 'chosen_image' || $ref['source'] === 'override_image')
            ? 'Blank path (optional unless needed as effective hero).'
            : 'Blank path.';
        if ($ref['source'] === 'hero_image' || $ref['source'] === 'thumbnail_candidate') {
            $severity = 'warning';
        }
    }

    if ($isExternal) {
        $notes[] = 'External URL (not local image root).';
        $severity = 'critical';
    }

    if ($hasBackslashes) {
        $notes[] = 'Path contains backslashes.';
        if ($severity !== 'critical') $severity = 'warning';
    }

    if ($isVideo) {
        $notes[] = 'Media/video reference (.mp4), not a still-image asset.';
        if ($repairCategory === 'clean') $repairCategory = 'media_video_reference';
    }

    if (!$isBlank && !$isExternal) {
        if (!preg_match('#(^|/)images/#i', $pathForFs)) {
            $outsideExpectedRoot = true;
            $notes[] = 'Path appears outside expected images directory.';
            $severity = 'critical';
        }

        if ($pathForFs !== '') {
            $exists = admin_image_exists($pathForFs);
            $status = $exists ? 'exists' : 'missing';
        }

        if ($exists) {
            $notes[] = 'Local file exists.';
        } else {
            $repairCategory = 'missing_local_file';
            $notes[] = 'Local file missing.';

            $fsPath = $ref['resolved_fs_path'];
            $dir = $fsPath !== '' ? dirname($fsPath) : '';
            $baseNoExt = $fsPath !== '' ? pathinfo($fsPath, PATHINFO_FILENAME) : '';
            $extMismatch = false;

            if ($dir !== '' && $baseNoExt !== '' && is_dir($dir)) {
                foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
                    $candidate = $dir . '/' . $baseNoExt . '.' . $ext;
                    if (is_file($candidate)) {
                        $extMismatch = true;
                        $notes[] = 'Possible extension mismatch; found sibling file: ' . basename($candidate) . '.';
                        break;
                    }
                }
            }

            if ($extMismatch && $severity !== 'critical') {
                $severity = 'warning';
            }

            if (preg_match('#(^|/)images/[^/]+/#i', $pathForFs) && !preg_match('#(^|/)images/brands/#i', $pathForFs)) {
                $brandsCandidate = preg_replace('#(^|/)images/#i', '$0brands/', $pathForFs, 1);
                if ($brandsCandidate && admin_image_exists($brandsCandidate)) {
                    $notes[] = 'Possible missing /brands/ segment; alternate path exists.';
                    $suggestedPath = $brandsCandidate;
                    $repairCategory = 'missing_brands_segment';
                    $sqlPreview = "UPDATE item SET " . $ref['source'] . " = '" . str_replace("'", "''", $brandsCandidate) . "' WHERE itemId = " . (int)$ref['itemId'] . ";";
                    if ($severity !== 'critical') $severity = 'warning';
                }
            }

            if (
                $suggestedPath === ''
                && preg_match('#(^|/)images/brands([^/]+)/#i', $pathForFs, $badBrandMatch)
                && !preg_match('#(^|/)images/brands/#i', $pathForFs)
            ) {
                $fixedBrandPath = preg_replace('#(^|/)images/brands([^/]+)/#i', '$1images/brands/$2/', $pathForFs, 1);
                if ($fixedBrandPath && admin_image_exists($fixedBrandPath)) {
                    $notes[] = 'Malformed brands path; missing slash after /brands.';
                    $suggestedPath = $fixedBrandPath;
                    $repairCategory = 'malformed_brands_path';
                    $sqlPreview = "UPDATE item SET " . $ref['source'] . " = '" . str_replace("'", "''", $fixedBrandPath) . "' WHERE itemId = " . (int)$ref['itemId'] . ";";
                    if ($severity !== 'critical') $severity = 'warning';
                }
            }

            if ($repairCategory === 'missing_local_file' && !$isVideo && !$isExternal) {
                $repairCategory = 'unknown_missing';
            }
        }
    }

    $dupCount = $normalized !== '' ? (int)($duplicateMap[strtolower($normalized)] ?? 0) : 0;
    if ($dupCount > 1 && $normalized !== '') {
        $dupMeta = $duplicateUsageMap[strtolower($normalized)] ?? ['items' => [], 'sources' => []];
        $crossItemReuse = count($dupMeta['items']) > 1;
        $sameSourceRepeat = false;
        foreach (($dupMeta['sources'] ?? []) as $sourceCount) {
            if ((int)$sourceCount > 1) {
                $sameSourceRepeat = true;
                break;
            }
        }

        if ($crossItemReuse || $sameSourceRepeat) {
            $notes[] = 'Suspicious duplicate path reuse (' . $dupCount . ' uses).';
            if ($severity !== 'critical') $severity = 'warning';
        }
    }

    $itemId = $ref['itemId'];
    $itemCandidateStats[$itemId] = $itemCandidateStats[$itemId] ?? ['total' => 0, 'exists' => 0];
    if ($ref['source'] === 'thumbnail_candidate' || $ref['source'] === 'hero_image' || $ref['source'] === 'chosen_image' || $ref['source'] === 'override_image') {
        $itemCandidateStats[$itemId]['total']++;
        if ($status === 'exists') $itemCandidateStats[$itemId]['exists']++;
    }

    if ($ref['is_effective_hero'] && $status === 'missing') {
        $notes[] = 'Missing current effective hero image.';
        $severity = 'critical';
    }

    $row = $ref + [
        'status' => $status,
        'severity' => $severity,
        'repair_category' => $repairCategory,
        'suggested_path' => $suggestedPath,
        'sql_preview' => $sqlPreview,
        'notes' => implode(' ', $notes),
    ];

    if ($selectedBrand !== '' && strcasecmp($row['brand'], $selectedBrand) !== 0) continue;
    if ($selectedSeverity !== 'all' && $row['severity'] !== $selectedSeverity) continue;
    if ($selectedSource !== 'all' && $row['source'] !== $selectedSource) continue;
    if ($selectedStatus !== 'all' && $row['status'] !== $selectedStatus) continue;
    if ($selectedRepairCategory !== 'all' && $row['repair_category'] !== $selectedRepairCategory) continue;

    $finalRows[] = $row;

    $summary['references_checked']++;
    if ($row['status'] === 'missing') $summary['missing_references']++;
    if ($row['severity'] === 'critical') $summary['critical_issues']++;
    elseif ($row['severity'] === 'warning') $summary['warning_issues']++;
    else $summary['clean_references']++;
}

foreach ($itemCandidateStats as $itemId => $stats) {
    if (($itemEffectiveHeroRawMap[$itemId] ?? false) === false) {
        foreach ($finalRows as &$rowRef) {
            if ($rowRef['itemId'] === $itemId) {
                $rowRef['notes'] .= ($rowRef['notes'] !== '' ? ' ' : '') . 'Missing current effective hero image.';
                $rowRef['severity'] = 'critical';
            }
        }
        unset($rowRef);
    }

    if ($stats['total'] > 0 && $stats['exists'] === 0) {
        foreach ($finalRows as &$rowRef) {
            if ($rowRef['itemId'] === $itemId) {
                $rowRef['notes'] .= ($rowRef['notes'] !== '' ? ' ' : '') . 'All product candidate paths appear missing.';
                $rowRef['severity'] = 'critical';
                $rowRef['repair_category'] = 'all_candidates_missing';
            }
        }
        unset($rowRef);
    }
}

admin_layout_start('Image Integrity');
admin_header('Image Integrity Report', 'Read-only database-led audit of live image references for active products.', [
    ['label' => 'Admin', 'href' => './index.php'],
    ['label' => 'Diagnostics'],
    ['label' => 'Image Integrity'],
]);
?>
<div class="hero-admin image-integrity-report">
<section class="hero-report-section">
<div class="hero-report-grid">
<article class="hero-report-card"><h3>Active products checked</h3><p><?= (int)$summary['active_products'] ?></p></article>
<article class="hero-report-card"><h3>Image references checked</h3><p><?= (int)$summary['references_checked'] ?></p></article>
<article class="hero-report-card"><h3>Missing references</h3><p><?= (int)$summary['missing_references'] ?></p></article>
<article class="hero-report-card"><h3>Critical issues</h3><p><?= (int)$summary['critical_issues'] ?></p></article>
<article class="hero-report-card"><h3>Warning issues</h3><p><?= (int)$summary['warning_issues'] ?></p></article>
<article class="hero-report-card"><h3>Clean references</h3><p><?= (int)$summary['clean_references'] ?></p></article>
</div>
</section>

<section class="hero-report-section hero-report-filters">
<h2>Filters</h2>
<form method="get" class="hero-report-filter-form">
<label>Brand
<select name="brand">
<option value="">All</option>
<?php foreach ($allBrands as $brand): ?>
<option value="<?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>" <?= strcasecmp($selectedBrand, $brand) === 0 ? 'selected' : '' ?>><?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select></label>
<label>Severity
<select name="severity">
<?php foreach (['all','critical','warning','info'] as $sev): ?>
<option value="<?= $sev ?>" <?= $selectedSeverity === $sev ? 'selected' : '' ?>><?= ucfirst($sev) ?></option>
<?php endforeach; ?>
</select></label>
<label>Source
<select name="source">
<?php foreach (['all','chosen_image','hero_image','thumbnail_candidate','override_image'] as $src): ?>
<option value="<?= $src ?>" <?= $selectedSource === $src ? 'selected' : '' ?>><?= htmlspecialchars($src) ?></option>
<?php endforeach; ?>
</select></label>
<label>Status
<select name="status">
<?php foreach (['all','exists','missing'] as $st): ?>
<option value="<?= $st ?>" <?= $selectedStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
<?php endforeach; ?>
</select></label>
<label>Repair category
<select name="repair_category">
<?php foreach ($allowedRepairCategories as $cat): ?>
<option value="<?= $cat ?>" <?= $selectedRepairCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select></label>
<button type="submit" class="btn btn--small">Apply</button>
<a class="btn btn--small btn--ghost" href="./image-integrity.php">Reset</a>
</form>
</section>

<section class="hero-report-section">
<h2>Reference details</h2>
<div class="hero-report-table-wrap">
<table class="hero-report-table image-integrity-table">
<thead><tr>
<th>Item</th><th>Brand</th><th>Source</th><th>Raw path</th><th>Normalized</th><th>Filesystem path</th><th>Status</th><th>Severity</th><th>Repair category</th><th>Suggested path</th><th>SQL preview (read-only)</th><th>Notes</th>
</tr></thead>
<tbody>
<?php if (empty($finalRows)): ?>
<tr><td colspan="12" class="hero-report-muted">No references matched the current filters.</td></tr>
<?php else: foreach ($finalRows as $r): ?>
<tr>
<td>#<?= (int)$r['itemId'] ?><br><?= htmlspecialchars($r['itemName'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($r['brand'], ENT_QUOTES, 'UTF-8') ?></td>
<td><code><?= htmlspecialchars($r['source'], ENT_QUOTES, 'UTF-8') ?></code></td>
<td class="hero-report-path"><?= htmlspecialchars($r['raw_path'], ENT_QUOTES, 'UTF-8') ?></td>
<td class="hero-report-path"><?= htmlspecialchars($r['normalized_path'], ENT_QUOTES, 'UTF-8') ?></td>
<td class="hero-report-path"><?= htmlspecialchars($r['resolved_fs_path'], ENT_QUOTES, 'UTF-8') ?></td>
<td><span class="image-integrity-pill is-<?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
<td><span class="image-integrity-pill is-<?= htmlspecialchars($r['severity'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r['severity'], ENT_QUOTES, 'UTF-8') ?></span></td>
<td><code><?= htmlspecialchars($r['repair_category'], ENT_QUOTES, 'UTF-8') ?></code></td>
<td class="hero-report-path"><?= htmlspecialchars($r['suggested_path'], ENT_QUOTES, 'UTF-8') ?></td>
<td class="hero-report-path"><code><?= htmlspecialchars($r['sql_preview'], ENT_QUOTES, 'UTF-8') ?></code></td>
<td><?= htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</section>
</div>
<?php admin_layout_end();
