<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../inc/hero/authority.php';
require_once __DIR__ . '/../inc/hero/candidates.php';

header('Content-Type: application/json');

$itemId = (int)($_GET['item_id'] ?? 0);
if ($itemId <= 0) {
    echo json_encode(['error' => 'Invalid item']);
    exit;
}

echo json_encode(sw_enumerate_scored_candidates($pdo, $itemId));


