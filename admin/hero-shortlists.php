<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../inc/hero/candidates.php';
require_once __DIR__ . '/../inc/hero/diagnostics.php';
require_once __DIR__ . '/../inc/hero/shortlist.php';

header('Content-Type: application/json');

$limit = (int)($_GET['limit'] ?? 25);
$limit = max(1, min(100, $limit));
$offset = (int)($_GET['offset'] ?? 0);
$offset = max(0, $offset);
$brand = trim((string)($_GET['brand'] ?? ''));
$section = trim((string)($_GET['section'] ?? ''));
$q = trim((string)($_GET['q'] ?? ''));
$status = strtolower(trim((string)($_GET['status'] ?? 'active')));
$allowedStatuses = ['active', 'inactive', 'all'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'active';
}

$where = [];
if ($status === 'active') {
    $where[] = 'i.is_active = 1';
} elseif ($status === 'inactive') {
    $where[] = 'i.is_active = 0';
}
$params = [];

if ($brand !== '') {
    $where[] = 'LOWER(i.brand) = LOWER(:brand)';
    $params[':brand'] = $brand;
}

if ($section !== '') {
    // Conservative section matching because schema uses category hierarchy fields.
    $where[] = '(LOWER(COALESCE(i.parentCategory, \"\")) = LOWER(:section_exact) OR LOWER(COALESCE(i.subcategory, \"\")) = LOWER(:section_exact) OR LOWER(COALESCE(i.categoryName, \"\")) = LOWER(:section_exact))';
    $params[':section_exact'] = $section;
}

if ($q !== '') {
    $where[] = '(i.itemName LIKE :q OR i.brand LIKE :q OR i.description LIKE :q OR i.categoryName LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "
    SELECT
        i.itemId,
        i.itemName,
        i.brand
    FROM item i
    {$whereSql}
    ORDER BY i.brand ASC, i.itemName ASC, i.itemId ASC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $name => $value) {
    $stmt->bindValue($name, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$products = [];
$readyCount = 0;
$partialCount = 0;
$unavailableCount = 0;
$legacyPlaceholderCount = 0;

foreach ($items as $item) {
    $itemId = (int)($item['itemId'] ?? 0);
    if ($itemId <= 0) {
        continue;
    }

    $result = sw_enumerate_scored_candidates($pdo, $itemId);
    $candidates = $result['candidates'] ?? [];

    if (is_array($candidates)) {
        foreach ($candidates as &$candidate) {
            $candidate['diagnostics'] = sw_get_hero_diagnostic_for_image((string)($candidate['path'] ?? ''));
        }
        unset($candidate);
    }

    $shortlist = sw_build_hero_shortlist_contract($itemId, $candidates);

    $productRecord = [
        'item_id' => $itemId,
        'item_name' => $item['itemName'] ?? null,
        'brand' => $item['brand'] ?? null,
        'active_criteria_profile' => $shortlist['active_criteria_profile'] ?? null,
        'criteria_profile_metadata' => $shortlist['criteria_profile_metadata'] ?? [],
        'shortlist_basis' => $shortlist['shortlist_basis'] ?? 'legacy_rank_placeholder',
        'shortlist_status' => $shortlist['shortlist_status'] ?? 'unavailable',
        'current_hero' => $shortlist['current_hero'] ?? null,
        'recommended_candidates' => $shortlist['recommended_candidates'] ?? [],
        'all_candidates' => $shortlist['all_candidates'] ?? [],
        'candidate_count' => is_array($candidates) ? count($candidates) : 0,
        'challenge_endpoint' => 'admin/hero-candidates.php?item_id=' . $itemId . '&include_shortlist=1',
    ];

    if (($productRecord['shortlist_basis'] ?? '') === 'legacy_rank_placeholder') {
        $legacyPlaceholderCount++;
    }

    switch ($productRecord['shortlist_status']) {
        case 'ready':
            $readyCount++;
            break;
        case 'partial':
            $partialCount++;
            break;
        default:
            $unavailableCount++;
            break;
    }

    $products[] = $productRecord;
}

echo json_encode([
    'status' => 'ready',
    'shortlist_basis' => 'legacy_rank_placeholder',
    'active_scope' => [
        'section' => $section !== '' ? $section : null,
        'brand' => $brand !== '' ? $brand : null,
        'category' => null,
        'limit' => $limit,
        'offset' => $offset,
        'status' => $status,
    ],
    'products' => $products,
    'summary' => [
        'product_count' => count($products),
        'ready_count' => $readyCount,
        'partial_count' => $partialCount,
        'unavailable_count' => $unavailableCount,
        'legacy_placeholder_count' => $legacyPlaceholderCount,
    ],
], JSON_UNESCAPED_SLASHES);
