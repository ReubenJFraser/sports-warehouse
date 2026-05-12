<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../inc/hero/authority.php';
require_once __DIR__ . '/../inc/hero/candidates.php';
require_once __DIR__ . '/../inc/hero/diagnostics.php';
require_once __DIR__ . '/../inc/hero/shortlist.php';

header('Content-Type: application/json');

$itemId = (int)($_GET['item_id'] ?? 0);
if ($itemId <= 0) {
    echo json_encode(['error' => 'Invalid item']);
    exit;
}

$result = sw_enumerate_scored_candidates($pdo, $itemId);

if (isset($result['candidates']) && is_array($result['candidates'])) {
    foreach ($result['candidates'] as &$candidate) {
        $candidate['diagnostics'] = sw_get_hero_diagnostic_for_image((string)($candidate['path'] ?? ''));
    }
    unset($candidate);
}

if ((int)($_GET['include_shortlist'] ?? 0) === 1) {
    echo json_encode(sw_build_hero_shortlist_contract($itemId, $result['candidates'] ?? []));
    exit;
}

echo json_encode($result);
