<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../inc/hero/rationale.php';

$filterItemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$filterReasonCode = trim((string)($_GET['reason_code'] ?? ''));
$filterCriteriaProfile = trim((string)($_GET['criteria_profile'] ?? ''));
$filterSignal = trim((string)($_GET['signal'] ?? ''));

$signalMap = [
    'criteria_refinement_signal' => 'Criteria review signal',
    'image_set_limitation_signal' => 'Image-set limitation',
    'metadata_issue_signal' => 'Metadata/category issue',
    'diagnostics_issue_signal' => 'Diagnostics/ranking issue',
];

$sql = "
    SELECT
        hor.rationale_id,
        hor.itemId,
        hor.selected_hero_image,
        hor.current_hero_image,
        hor.active_criteria_profile,
        hor.shortlist_basis,
        hor.current_hero_rank,
        hor.current_hero_outside_top_three,
        hor.selected_reason_codes,
        hor.optional_note,
        hor.criteria_refinement_signal,
        hor.image_set_limitation_signal,
        hor.metadata_issue_signal,
        hor.diagnostics_issue_signal,
        hor.created_at,
        hor.updated_at,
        i.itemName,
        i.brand,
        i.categoryName,
        i.subcategory,
        i.parentCategory
    FROM hero_override_rationale hor
    LEFT JOIN item i ON i.itemId = hor.itemId
    WHERE hor.is_active = 1
    ORDER BY hor.updated_at DESC, hor.rationale_id DESC
";

$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$reasonLabels = [
    'rear_facing_unsuitable_angle' => 'Rear-facing / unsuitable angle',
    'side_facing_insufficiently_clear' => 'Side-facing / insufficiently clear',
    'product_visible_but_not_primary_hero_suitable' => 'Product visible but not primary hero suitable',
    'product_focus_conflicts_with_editorial_presentation' => 'Product focus conflicts with editorial presentation',
    'full_body_model_presentation_preferred' => 'Full-body model presentation preferred',
    'face_or_model_context_needed' => 'Face or model context needed',
    'criteria_profile_probably_wrong' => 'Criteria profile probably wrong',
    'product_or_category_metadata_may_be_wrong' => 'Product/category metadata may be wrong',
    'diagnostics_or_ranking_appear_wrong' => 'Diagnostics/ranking appear wrong',
    'no_ideal_image_exists' => 'No ideal image exists',
    'human_editorial_judgement_override' => 'Human editorial judgement override',
];

$allowedCodes = sw_hero_rationale_allowed_reason_codes();
foreach ($allowedCodes as $code) {
    if (!isset($reasonLabels[$code])) {
        $reasonLabels[$code] = ucwords(str_replace('_', ' ', $code));
    }
}

$filteredRows = [];
$reasonCounts = [];
$criteriaCounts = [];
$signalCounts = array_fill_keys(array_keys($signalMap), 0);
$invalidReasonJsonCount = 0;

foreach ($rows as $row) {
    $parsed = sw_hero_rationale_parse_reason_codes($row['selected_reason_codes'] ?? null);
    $codes = $parsed['codes'];
    $row['parsed_reason_codes'] = $codes;
    $row['has_reason_warning'] = $parsed['warning'] !== null;

    if ($row['has_reason_warning']) {
        $invalidReasonJsonCount++;
    }

    if ($filterItemId > 0 && (int)$row['itemId'] !== $filterItemId) {
        continue;
    }

    if ($filterCriteriaProfile !== '' && strcasecmp((string)($row['active_criteria_profile'] ?? ''), $filterCriteriaProfile) !== 0) {
        continue;
    }

    if ($filterReasonCode !== '' && !in_array($filterReasonCode, $codes, true)) {
        continue;
    }

    if ($filterSignal !== '') {
        if (!isset($signalMap[$filterSignal])) {
            continue;
        }
        if (empty($row[$filterSignal])) {
            continue;
        }
    }

    $filteredRows[] = $row;

    foreach ($codes as $code) {
        $reasonCounts[$code] = ($reasonCounts[$code] ?? 0) + 1;
    }

    $profile = trim((string)($row['active_criteria_profile'] ?? ''));
    if ($profile !== '') {
        $criteriaCounts[$profile] = ($criteriaCounts[$profile] ?? 0) + 1;
    }

    foreach (array_keys($signalMap) as $signalKey) {
        if (!empty($row[$signalKey])) {
            $signalCounts[$signalKey]++;
        }
    }
}

arsort($reasonCounts);
arsort($criteriaCounts);

$activeRationaleCount = count($filteredRows);
$productCount = count(array_unique(array_map(static fn($r) => (int)$r['itemId'], $filteredRows)));
$topReasonCode = array_key_first($reasonCounts);
$topCriteriaProfile = array_key_first($criteriaCounts);

admin_layout_start('Hero Rationale Report');
admin_header(
    'Hero Rationale Pattern Report',
    'Read-only review evidence from saved override rationale. Automation suggests; manual curation decides.',
    [
        ['label' => 'Admin', 'href' => './index.php'],
        ['label' => 'Hero Tools', 'href' => './hero-manager.php'],
        ['label' => 'Rationale Report'],
    ]
);
?>

<div class="hero-admin hero-rationale-report">
    <section class="hero-report-section">
        <div class="hero-report-grid">
            <article class="hero-report-card"><h3>Active rationales</h3><p><?= $activeRationaleCount ?></p></article>
            <article class="hero-report-card"><h3>Products with rationale</h3><p><?= $productCount ?></p></article>
            <article class="hero-report-card"><h3>Criteria review signals</h3><p><?= (int)$signalCounts['criteria_refinement_signal'] ?></p></article>
            <article class="hero-report-card"><h3>Image-set limitation signals</h3><p><?= (int)$signalCounts['image_set_limitation_signal'] ?></p></article>
            <article class="hero-report-card"><h3>Metadata/category issue signals</h3><p><?= (int)$signalCounts['metadata_issue_signal'] ?></p></article>
            <article class="hero-report-card"><h3>Diagnostics/ranking issue signals</h3><p><?= (int)$signalCounts['diagnostics_issue_signal'] ?></p></article>
            <article class="hero-report-card"><h3>Most common reason code</h3><p><?= $topReasonCode ? htmlspecialchars($reasonLabels[$topReasonCode] ?? $topReasonCode) : '—' ?></p></article>
            <article class="hero-report-card"><h3>Most common criteria profile</h3><p><?= $topCriteriaProfile ? htmlspecialchars($topCriteriaProfile) : '—' ?></p></article>
        </div>
    </section>

    <section class="hero-report-section hero-report-filters">
        <h2>Filters</h2>
        <form method="get" class="hero-report-filter-form">
            <label>Item ID <input type="number" name="item_id" value="<?= $filterItemId > 0 ? $filterItemId : '' ?>"></label>
            <label>Reason code <input type="text" name="reason_code" value="<?= htmlspecialchars($filterReasonCode) ?>" placeholder="e.g. no_ideal_image_exists"></label>
            <label>Criteria profile <input type="text" name="criteria_profile" value="<?= htmlspecialchars($filterCriteriaProfile) ?>" placeholder="e.g. apparel_default"></label>
            <label>Signal
                <select name="signal">
                    <option value="">All</option>
                    <?php foreach ($signalMap as $signalKey => $signalLabel): ?>
                        <option value="<?= htmlspecialchars($signalKey) ?>" <?= $filterSignal === $signalKey ? 'selected' : '' ?>><?= htmlspecialchars($signalLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn btn--small">Apply</button>
            <a class="btn btn--small btn--ghost" href="./hero-rationale-report.php">Reset</a>
        </form>
    </section>

    <section class="hero-report-section">
        <h2>Reason-code frequency</h2>
        <?php if ($activeRationaleCount === 0): ?>
            <p class="hero-empty">No active hero override rationales have been saved yet.</p>
        <?php elseif (empty($reasonCounts)): ?>
            <p class="hero-empty">No reason codes found in active rationale records.</p>
        <?php else: ?>
            <div class="hero-report-table-wrap">
                <table class="hero-report-table">
                    <thead><tr><th>Reason label</th><th>Reason code</th><th>Count</th><th>% of active rationales</th></tr></thead>
                    <tbody>
                    <?php foreach ($reasonCounts as $code => $count): $pct = $activeRationaleCount > 0 ? round(($count / $activeRationaleCount) * 100, 1) : 0; ?>
                        <tr>
                            <td><?= htmlspecialchars($reasonLabels[$code] ?? $code) ?></td>
                            <td><code><?= htmlspecialchars($code) ?></code></td>
                            <td><?= (int)$count ?></td>
                            <td><?= $pct ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="hero-report-section">
        <h2>Signal breakdown</h2>
        <div class="hero-report-signal-list">
            <?php foreach ($signalMap as $signalKey => $signalLabel): ?>
                <div class="hero-report-signal-item"><span><?= htmlspecialchars($signalLabel) ?></span><strong><?= (int)$signalCounts[$signalKey] ?></strong></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hero-report-section">
        <h2>Active rationale records</h2>
        <?php if ($invalidReasonJsonCount > 0): ?>
            <p class="hero-admin__subtitle">Warning: <?= (int)$invalidReasonJsonCount ?> record(s) had malformed selected_reason_codes JSON and were treated as empty lists.</p>
        <?php endif; ?>

        <?php if ($activeRationaleCount === 0): ?>
            <p class="hero-empty">No active hero override rationales have been saved yet.</p>
        <?php else: ?>
            <div class="hero-report-table-wrap">
                <table class="hero-report-table">
                    <thead>
                        <tr>
                            <th>Item</th><th>Product</th><th>Category</th><th>Selected hero image</th><th>Criteria profile</th><th>Reasons</th><th>Signals</th><th>Note</th><th>Updated</th><th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredRows as $row): ?>
                            <tr>
                                <td>#<?= (int)$row['itemId'] ?></td>
                                <td>
                                    <div><?= htmlspecialchars((string)($row['itemName'] ?? 'Unknown item')) ?></div>
                                    <div class="hero-report-muted"><?= htmlspecialchars((string)($row['brand'] ?? '')) ?></div>
                                </td>
                                <td class="hero-report-muted"><?= htmlspecialchars(trim((string)($row['parentCategory'] ?? '')) . ' / ' . trim((string)($row['subcategory'] ?? ''))) ?></td>
                                <td class="hero-report-path" title="<?= htmlspecialchars((string)($row['selected_hero_image'] ?? '')) ?>"><?= htmlspecialchars((string)($row['selected_hero_image'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($row['active_criteria_profile'] ?? '—')) ?></td>
                                <td><?= count($row['parsed_reason_codes']) ?><?= $row['has_reason_warning'] ? ' <span class="hero-badge hero-badge--missing">Invalid JSON</span>' : '' ?></td>
                                <td>
                                    <?php foreach ($signalMap as $signalKey => $signalLabel): ?>
                                        <?php if (!empty($row[$signalKey])): ?>
                                            <span class="hero-badge"><?= htmlspecialchars($signalLabel) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td class="hero-report-muted"><?= htmlspecialchars(mb_strimwidth(trim((string)($row['optional_note'] ?? '')), 0, 96, '…')) ?></td>
                                <td class="hero-report-muted">
                                    <div>Created: <?= htmlspecialchars((string)$row['created_at']) ?></div>
                                    <div>Updated: <?= htmlspecialchars((string)$row['updated_at']) ?></div>
                                </td>
                                <td><a class="btn btn--small" href="./hero-manager.php?item=<?= (int)$row['itemId'] ?>">Hero Manager</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

</div>
<?php admin_layout_end(); ?>
