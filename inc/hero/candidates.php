<?php

require_once __DIR__ . '/score.php';

/**
 * Enumerate and score all hero image candidates for an item.
 * Read-only. No writes. No recomputation side effects.
 */
function sw_enumerate_scored_candidates(PDO $pdo, int $itemId): array
{
    // Fetch item image sources
    $stmt = $pdo->prepare("
        SELECT
            chosen_image,
            chosen_ratio,
            thumbnails_json,
            hero_image
        FROM item
        WHERE itemId = ?
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        return ['item_id' => $itemId, 'candidates' => []];
    }

    // Fetch latest manual override (if any)
    $stmt = $pdo->prepare("
        SELECT chosen_image
        FROM hero_override
        WHERE itemId = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$itemId]);
    
    $override = $stmt->fetch(PDO::FETCH_ASSOC);

    // Enumerate candidates (same logic as recalc)
    $seen = [];
    $candidates = [];

    $addCandidate = function (string $path, string $source) use (&$seen, &$candidates) {
        $base = strtolower(basename($path));
        if ($base === '' || isset($seen[$base])) return;
        $seen[$base] = true;
        $candidates[] = [
            'basename' => $base,
            'path'     => $path,
            'source'   => $source
        ];
    };

    if (!empty($item['chosen_image'])) {
        $addCandidate($item['chosen_image'], 'chosen');
    }

    if (!empty($item['thumbnails_json'])) {
        foreach (explode(';', $item['thumbnails_json']) as $thumb) {
            $addCandidate(trim($thumb), 'thumbnail');
        }
    }

    if (!$candidates) {
        return ['item_id' => $itemId, 'candidates' => []];
    }

    // Fetch headroom analysis
    $basenames = array_column($candidates, 'basename');
    $in = implode(',', array_fill(0, count($basenames), '?'));

    $stmt = $pdo->prepare("
        SELECT image_basename, ratio, headroom_pct, focus_y_pct, crop_safe, face_count
        FROM image_headroom
        WHERE image_basename IN ($in)
    ");
    $stmt->execute($basenames);

    $hmap = [];
    foreach ($stmt as $row) {
        $hmap[$row['image_basename']] = $row;
    }

    // Fetch rejection counts
    $stmt = $pdo->prepare("
        SELECT rejected_image, COUNT(*) AS c
        FROM hero_rejections
        WHERE itemId = ?
        GROUP BY rejected_image
    ");
    $stmt->execute([$itemId]);

    $rej = [];
    foreach ($stmt as $row) {
        $rej[$row['rejected_image']] = (int)$row['c'];
    }
    
    // Score candidates
    foreach ($candidates as &$c) {
        $hr = $hmap[$c['basename']] ?? [
            'ratio'        => null,
            'headroom_pct' => null,
            'focus_y_pct'  => null,
            'crop_safe'    => null,
            'face_count'   => null
        ];

        $score = sw_score_candidate($hr);

        $ratio = $hr['ratio'] ?? ($c['source'] === 'chosen' ? (float)$item['chosen_ratio'] : null);
        $orientation = $ratio === null ? null :
            ($ratio > 1.05 ? 'landscape' : ($ratio < 0.95 ? 'portrait' : 'square'));

        $c['score'] = round($score, 2);
        $c['analysis'] = [
            'ratio'        => $ratio,
            'orientation'  => $orientation,
            'headroom_pct' => $hr['headroom_pct'],
            'focus_y_pct'  => $hr['focus_y_pct'],
            'crop_safe'    => $hr['crop_safe'],
            'face_count'   => $hr['face_count']
        ];

        $c['status'] = [
            'is_current_hero'    => ($item['hero_image'] === $c['path']),
            'is_manual_override' => (
                $override
                && $override['chosen_image'] === $c['path']
            ),
            'is_rejected'        => isset($rej[$c['basename']]),
            'rejection_count'    => $rej[$c['basename']] ?? 0
        ];
    }
    unset($c);

    // Sort and rank
    usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
    foreach ($candidates as $i => &$c) {
        $c['rank'] = $i + 1;
        $c['actions'] = [
            'can_select' => !$c['status']['is_current_hero'],
            'can_reject' => !$c['status']['is_rejected']
        ];
    }

    return ['item_id' => $itemId, 'candidates' => $candidates];
}



