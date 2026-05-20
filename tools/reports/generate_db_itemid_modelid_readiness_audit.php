<?php
require_once __DIR__ . '/../../inc/env.php';

$csvPath = __DIR__ . '/../../docs/data/SportWarehouse_ProductDB.csv';
$outDir = __DIR__ . '/../../docs/operations/generated';
$outPath = $outDir . '/2026-05-18-db_itemid-model_id-readiness-audit.md';

if (!is_readable($csvPath)) {
    fwrite(STDERR, "CSV not readable: {$csvPath}\n");
    exit(1);
}

if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Unable to create output directory: {$outDir}\n");
    exit(1);
}

function norm(?string $v): string {
    return trim((string)$v);
}

$fh = fopen($csvPath, 'r');
$headers = fgetcsv($fh);
if ($headers === false) {
    fwrite(STDERR, "Failed to read CSV header.\n");
    exit(1);
}

$idx = array_flip($headers);
foreach (['db_itemId','model_id','itemName'] as $required) {
    if (!isset($idx[$required])) {
        fwrite(STDERR, "Missing required CSV column: {$required}\n");
        exit(1);
    }
}

$totalCsvRows = 0;
$csvDbNonblank = [];
$csvDbBlank = 0;
$csvModelNonblank = [];
$csvModelBlank = 0;

while (($row = fgetcsv($fh)) !== false) {
    $totalCsvRows++;
    $dbItemId = norm($row[$idx['db_itemId']] ?? '');
    $modelId = norm($row[$idx['model_id']] ?? '');

    if ($dbItemId === '') {
        $csvDbBlank++;
    } else {
        $csvDbNonblank[] = $dbItemId;
    }

    if ($modelId === '') {
        $csvModelBlank++;
    } else {
        $csvModelNonblank[] = $modelId;
    }
}
fclose($fh);

$csvDbCounts = array_count_values($csvDbNonblank);
$csvDbDupes = array_filter($csvDbCounts, fn($c) => $c > 1);
ksort($csvDbDupes, SORT_NATURAL);

$csvModelCounts = array_count_values($csvModelNonblank);
$csvModelDupes = array_filter($csvModelCounts, fn($c) => $c > 1);
ksort($csvModelDupes, SORT_NATURAL);

$dbHost = sw_env('DB_HOST', '127.0.0.1');
$dbName = sw_env('DB_NAME', 'sportswh');
$dbUser = sw_env('DB_USER', 'root');
$dbPass = sw_env('DB_PASS', '');

$dbError = null;
$hasActive = false;
$hasDbItemId = false;
$totalActive = 0;
$itemIds = [];

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $hasActive = (bool)$pdo->query("SHOW COLUMNS FROM item LIKE 'active'")->fetch(PDO::FETCH_ASSOC);
    $hasDbItemId = (bool)$pdo->query("SHOW COLUMNS FROM item LIKE 'db_itemId'")->fetch(PDO::FETCH_ASSOC);
    $activeWhere = $hasActive ? 'WHERE active = 1' : '';

    $totalActive = (int)$pdo->query("SELECT COUNT(*) FROM item {$activeWhere}")->fetchColumn();
    $itemIds = $pdo->query("SELECT itemId FROM item {$activeWhere} ORDER BY itemId ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
    $activeWhere = '';
}

$itemDbValues = [];
$itemDbNonblankCount = 0;
$itemDbBlankCount = $totalActive;
$itemDbDupes = [];

if ($hasDbItemId) {
    $itemDbValues = $pdo->query("SELECT TRIM(COALESCE(db_itemId,'')) AS db_itemId FROM item {$activeWhere} ORDER BY itemId ASC")
        ->fetchAll(PDO::FETCH_COLUMN);
    $itemDbNonblank = array_values(array_filter($itemDbValues, fn($v) => $v !== ''));
    $itemDbNonblankCount = count($itemDbNonblank);
    $itemDbBlankCount = $totalActive - $itemDbNonblankCount;
    $tmp = array_count_values($itemDbNonblank);
    $itemDbDupes = array_filter($tmp, fn($c) => $c > 1);
    ksort($itemDbDupes, SORT_NATURAL);
}

$csvDbUnique = array_values(array_unique($csvDbNonblank));
$csvSet = array_fill_keys($csvDbUnique, true);
$itemIdSet = array_fill_keys(array_map('strval', $itemIds), true);
$itemDbSet = $hasDbItemId ? array_fill_keys(array_values(array_unique(array_filter($itemDbValues, fn($v)=>$v!==''))), true) : [];

$csvMatchesItemId = [];
$csvMatchesDbItemId = [];
$csvNotFound = [];
foreach ($csvDbUnique as $v) {
    $hitItem = isset($itemIdSet[$v]);
    $hitDb = $hasDbItemId && isset($itemDbSet[$v]);
    if ($hitItem) $csvMatchesItemId[] = $v;
    if ($hitDb) $csvMatchesDbItemId[] = $v;
    if (!$hitItem && !$hitDb) $csvNotFound[] = $v;
}

$mysqlItemIdUnrepresented = [];
foreach (array_keys($itemIdSet) as $id) {
    if (!isset($csvSet[$id])) $mysqlItemIdUnrepresented[] = $id;
}

$mysqlDbItemIdUnrepresented = [];
if ($hasDbItemId) {
    foreach (array_keys($itemDbSet) as $id) {
        if (!isset($csvSet[$id])) $mysqlDbItemIdUnrepresented[] = $id;
    }
}

$linkedByDb = count($csvMatchesDbItemId);
$uniqueModelSet = array_filter($csvModelCounts, fn($c)=>$c===1);
$insertCandidates = 0;
$manualMapping = 0;

$fh = fopen($csvPath, 'r');
$headers = fgetcsv($fh);
while (($row = fgetcsv($fh)) !== false) {
    $dbItemId = norm($row[$idx['db_itemId']] ?? '');
    $modelId = norm($row[$idx['model_id']] ?? '');
    $needsManual = false;
    if ($dbItemId !== '' && !isset($itemIdSet[$dbItemId]) && !($hasDbItemId && isset($itemDbSet[$dbItemId]))) {
        $needsManual = true;
    }
    if ($modelId !== '' && (($csvModelCounts[$modelId] ?? 0) > 1)) {
        $needsManual = true;
    }
    if ($needsManual) {
        $manualMapping++;
    } elseif ($dbItemId === '' && $modelId !== '' && isset($uniqueModelSet[$modelId])) {
        $insertCandidates++;
    }
}
fclose($fh);

$md = [];
$md[] = '# db_itemId/model_id Readiness Audit (Read-Only)';
$md[] = '';
$md[] = '- Generated: ' . gmdate('Y-m-d H:i:s') . ' UTC';
$md[] = '- Source CSV: `docs/data/SportWarehouse_ProductDB.csv`';
$md[] = '- Scope: read-only audit (CSV + SELECT-only MySQL checks)';
if ($dbError !== null) {
    $md[] = '- DB status: connection failed (`' . str_replace('`', '\\`', $dbError) . '`)';
}
$md[] = '';
$md[] = '## 1) CSV db_itemId audit';
$md[] = "- Total CSV rows: **{$totalCsvRows}**";
$md[] = "- Rows with nonblank db_itemId: **" . count($csvDbNonblank) . "**";
$md[] = "- Rows with blank db_itemId: **{$csvDbBlank}**";
$md[] = "- Duplicate nonblank db_itemId values: **" . count($csvDbDupes) . "**";
$md[] = '- First 20 nonblank db_itemId values: `' . implode('`, `', array_slice($csvDbNonblank, 0, 20)) . '`';
$md[] = '';
$md[] = '## 2) Live MySQL item audit';
$md[] = "- Total active item rows: **{$totalActive}**" . ($hasActive ? ' (`active=1`)' : ' (no `active` column; all rows counted)');
$md[] = "- item.db_itemId exists: **" . ($hasDbItemId ? 'yes' : 'no') . '**';
$md[] = "- Rows with nonblank item.db_itemId: **{$itemDbNonblankCount}**";
$md[] = "- Rows with blank item.db_itemId: **{$itemDbBlankCount}**";
$md[] = "- Duplicate nonblank item.db_itemId values: **" . count($itemDbDupes) . "**";
$md[] = '- First 20 nonblank item.db_itemId values: `' . implode('`, `', array_slice(array_values(array_filter($itemDbValues, fn($v)=>$v!=='')), 0, 20)) . '`';
$md[] = '- First 20 itemId values: `' . implode('`, `', array_slice(array_map('strval', $itemIds), 0, 20)) . '`';
$md[] = '';
$md[] = '## 3) Cross-check';
$md[] = '- CSV db_itemId values matching MySQL itemId: **' . count($csvMatchesItemId) . '**';
$md[] = '- CSV db_itemId values matching MySQL db_itemId: **' . count($csvMatchesDbItemId) . '**';
$md[] = '- CSV db_itemId values not found in MySQL: **' . count($csvNotFound) . '**';
$md[] = '- MySQL itemId values not represented in CSV db_itemId: **' . count($mysqlItemIdUnrepresented) . '**';
$md[] = '- MySQL db_itemId values not represented in CSV db_itemId: **' . count($mysqlDbItemIdUnrepresented) . '**';
$md[] = '';
$md[] = '## 4) model_id audit';
$md[] = "- Total CSV rows: **{$totalCsvRows}**";
$md[] = "- Nonblank model_id rows: **" . count($csvModelNonblank) . "**";
$md[] = "- Blank model_id rows: **{$csvModelBlank}**";
$md[] = "- Duplicate model_id values: **" . count($csvModelDupes) . "**";
$md[] = '- Duplicate model_id list:';
foreach ($csvModelDupes as $v => $c) {
    $md[] = "  - `{$v}` × {$c}";
}
$md[] = '';
$md[] = '## 5) Classification';
$md[] = "- Existing rows confidently linked by db_itemId: **{$linkedByDb}**";
$md[] = "- Likely new insert candidates (blank db_itemId + unique nonblank model_id): **{$insertCandidates}**";
$md[] = "- Rows requiring manual mapping: **{$manualMapping}**";
$md[] = '';
$md[] = '## 6) Conclusion';
$md[] = "- `db_itemId` is usable for the **{$linkedByDb}** existing imported products matched on MySQL `db_itemId`.";
$md[] = "- **{$insertCandidates}** CSV rows with blank `db_itemId` are likely new insert candidates.";
$md[] = '- Duplicate `model_id` value `nike_female_leggings` requires manual review before import.';
$md[] = '';
$md[] = '## Appendix (samples)';
$md[] = '- Sample CSV db_itemId not found in MySQL (first 20): `' . implode('`, `', array_slice($csvNotFound, 0, 20)) . '`';
$md[] = '- Sample MySQL itemId values not in CSV db_itemId (first 20): `' . implode('`, `', array_slice($mysqlItemIdUnrepresented, 0, 20)) . '`';
$md[] = '- Sample MySQL db_itemId values not in CSV db_itemId (first 20): `' . implode('`, `', array_slice($mysqlDbItemIdUnrepresented, 0, 20)) . '`';

file_put_contents($outPath, implode("\n", $md) . "\n");
echo "Wrote {$outPath}\n";
