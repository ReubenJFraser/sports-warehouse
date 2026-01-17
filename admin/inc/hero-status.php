<?php
// /admin/inc/hero-status.php

if (!defined('ADMIN_CONTEXT')) {
    return;
}

/**
 * Read-only hero status for admin.
 * Requires: $pdo (PDO)
 */
function sw_hero_status(PDO $pdo): array
{
    $totalItems = (int)$pdo->query("
        SELECT COUNT(*) FROM item
    ")->fetchColumn();

    $itemsWithHero = (int)$pdo->query("
        SELECT COUNT(*)
        FROM item
        WHERE chosen_image IS NOT NULL
          AND chosen_image <> ''
    ")->fetchColumn();

    $itemsMissingHero = max(0, $totalItems - $itemsWithHero);

    $itemsWithOverride = (int)$pdo->query("
        SELECT COUNT(DISTINCT itemId)
        FROM item_orientation_override
    ")->fetchColumn();

    /*
        LEGACY HERO DEFINITION (LOCKED):

        - chosen_image present
        - hero_score IS NULL
        - NOT manually overridden
    */
    $legacyHeroItems = (int)$pdo->query("
        SELECT COUNT(*)
        FROM item i
        LEFT JOIN item_orientation_override o
               ON o.itemId = i.itemId
        WHERE i.chosen_image IS NOT NULL
          AND i.chosen_image <> ''
          AND i.hero_score IS NULL
          AND o.itemId IS NULL
    ")->fetchColumn();

    $coveragePct = $totalItems > 0
        ? round(($itemsWithHero / $totalItems) * 100, 1)
        : 0.0;

    return [
        'total_items'    => $totalItems,
        'with_hero'      => $itemsWithHero,
        'missing_hero'   => $itemsMissingHero,
        'with_override'  => $itemsWithOverride,
        'legacy_hero'    => $legacyHeroItems,
        'coverage_pct'   => $coveragePct,
    ];
}
