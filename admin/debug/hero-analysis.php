<?php
// /admin/debug/hero-analysis.php
// Read-only dashboard of hero_* coverage and overrides.

require __DIR__ . '/../../db.php';
require __DIR__ . '/../image-helper.php';
require_once __DIR__ . '/../_layout.php';

// Totals
$totalSql = "
    SELECT
        COUNT(*)                                  AS total_items,
        SUM(CASE WHEN hero_image IS NULL OR hero_image = '' THEN 1 ELSE 0 END) AS no_hero_image,
        SUM(CASE WHEN hero_image IS NOT NULL AND hero_image <> '' THEN 1 ELSE 0 END) AS with_hero_image
    FROM item
    WHERE is_active = 1
";
$total = $pdo->query($totalSql)->fetch(PDO::FETCH_ASSOC) ?: [
    'total_items'     => 0,
    'no_hero_image'   => 0,
    'with_hero_image' => 0,
];

// Orientation breakdown
$orientSql = "
    SELECT hero_orientation, COUNT(*) AS cnt
    FROM item
    WHERE is_active = 1
    GROUP BY hero_orientation
";
$orientRows = $pdo->query($orientSql)->fetchAll(PDO::FETCH_ASSOC);

// Override counts
$overrideSql = "
    SELECT
        COUNT(*) AS override_count
    FROM hero_override
";
$overrideRow = $pdo->query($overrideSql)->fetch(PDO::FETCH_ASSOC) ?: ['override_count' => 0];

admin_layout_start("Hero Analysis");
?>
<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Hero Analysis</h1>
        <p class="subtitle">
            Read-only overview of <code>hero_*</code> coverage and manual overrides.
        </p>
    </header>

    <section class="grid grid-3 mb-3">
        <article class="card">
            <h2 style="font-size:0.95rem;margin:0 0 4px;">Active Items</h2>
            <p style="font-size:1.4rem;margin:0;">
                <?= (int)$total['total_items'] ?>
            </p>
        </article>

        <article class="card">
            <h2 style="font-size:0.95rem;margin:0 0 4px;">With hero_image</h2>
            <p style="font-size:1.4rem;margin:0;">
                <?= (int)$total['with_hero_image'] ?>
            </p>
            <p class="mt-1" style="font-size:0.8rem;color:var(--text-muted);">
                Items where <code>hero_image</code> is non-empty.
            </p>
        </article>

        <article class="card">
            <h2 style="font-size:0.95rem;margin:0 0 4px;">Missing hero_image</h2>
            <p style="font-size:1.4rem;margin:0;color:var(--danger);">
                <?= (int)$total['no_hero_image'] ?>
            </p>
            <p class="mt-1" style="font-size:0.8rem;color:var(--text-muted);">
                These will fall back to <code>chosen_image</code>.
            </p>
        </article>
    </section>

    <section class="card mb-3">
        <h2 style="font-size:0.95rem;margin:0 0 8px;">Orientation breakdown</h2>
        <?php if (empty($orientRows)): ?>
            <p class="hero-empty">No orientation data found.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                <thead>
                <tr style="text-align:left;color:var(--text-muted);">
                    <th style="padding:6px 4px;">Orientation</th>
                    <th style="padding:6px 4px;">Count</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orientRows as $row):
                    $o = strtoupper(trim((string)$row['hero_orientation']));
                    $label = $o === 'L' ? 'Landscape'
                        : ($o === 'S' ? 'Square'
                        : ($o === 'P' ? 'Portrait' : '(None)'));
                    ?>
                    <tr>
                        <td style="padding:6px 4px;"><?= htmlspecialchars($label) ?></td>
                        <td style="padding:6px 4px;"><?= (int)$row['cnt'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2 style="font-size:0.95rem;margin:0 0 8px;">Manual overrides</h2>
        <p style="font-size:1.3rem;margin:0;">
            <?= (int)$overrideRow['override_count'] ?> items with entries in <code>hero_override</code>.
        </p>
        <p class="mt-1" style="font-size:0.8rem;color:var(--text-muted);">
            Overrides always win visually over stored auto hero frames, regardless of score.
        </p>
    </section>
</div>
<?php
admin_layout_end();
