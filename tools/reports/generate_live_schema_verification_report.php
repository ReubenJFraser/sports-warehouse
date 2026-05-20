<?php
require_once __DIR__ . '/../../inc/env.php';

$csvPath = __DIR__ . '/../../docs/data/SportWarehouse_ProductDB.csv';
$designPath = __DIR__ . '/../../README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md';
$outDir = __DIR__ . '/../../docs/operations/generated';
$outPath = $outDir . '/2026-05-20-live-schema-verification-report.md';

if (!is_readable($csvPath)) {
    fwrite(STDERR, "CSV not readable: {$csvPath}\n");
    exit(1);
}

if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Unable to create output directory: {$outDir}\n");
    exit(1);
}

$fh = fopen($csvPath, 'r');
if ($fh === false) {
    fwrite(STDERR, "Failed to open CSV: {$csvPath}\n");
    exit(1);
}
$headers = fgetcsv($fh);
fclose($fh);
if ($headers === false) {
    fwrite(STDERR, "Failed to read CSV headers.\n");
    exit(1);
}
if (isset($headers[0])) {
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$headers[0]);
}
$headers = array_map(static fn($h) => trim((string)$h), $headers);

$runtimeDecisions = [
    'db_itemId' => 'verify live schema first; keep existing',
    'model_id' => 'add new column or verify existing first',
    'itemName_fully_derived' => 'add new column or map alias',
    'subCategory' => 'map alias to subcategory',
    'ageGroup' => 'map alias to age_group',
    'sizeType' => 'map alias to size_type',
    'fitStyle' => 'map alias to fit_style',
    'activityTags' => 'map alias to activity_tags',
    'CropAllowed' => 'verify-first naming decision',
    'images2' => 'staging/import only',
    'assignment_source' => 'staging/import only',
    '_images_helper_normalize' => 'staging/import only',
];

$aliasPairs = [
    'db_itemId' => 'db_item_id',
    'CropAllowed' => 'crop_allowed',
    'itemName_fully_derived' => 'item_name_fully_derived',
    'subCategory' => 'subcategory',
    'ageGroup' => 'age_group',
    'sizeType' => 'size_type',
    'fitStyle' => 'fit_style',
    'activityTags' => 'activity_tags',
    'scrunchFlag' => 'scrunch_flag',
    'invisibleFlag' => 'invisible_flag',
];

$stagingOnly = ['images2', 'assignment_source', '_images_helper_normalize'];
$verifyFirst = ['CropAllowed', 'db_itemId'];

$duplicateGovernancePairs = [
    ['camel' => 'ageGroup', 'snake' => 'age_group'],
    ['camel' => 'sizeType', 'snake' => 'size_type'],
    ['camel' => 'fitStyle', 'snake' => 'fit_style'],
    ['camel' => 'activityTags', 'snake' => 'activity_tags'],
    ['camel' => 'CropAllowed', 'snake' => 'crop_allowed'],
];

$dbHost = sw_env('DB_HOST', '127.0.0.1');
$dbName = sw_env('DB_NAME', 'sportswh');
$dbUser = sw_env('DB_USER', 'root');
$dbPass = sw_env('DB_PASS', '');

$dbError = null;
$tableExists = ['item' => false, 'hero_override' => false];
$itemColumns = [];
$heroOverrideColumns = [];
$duplicatePairStats = [];

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $tableStmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table_name');
    foreach (array_keys($tableExists) as $table) {
        $tableStmt->execute(['schema' => $dbName, 'table_name' => $table]);
        $tableExists[$table] = ((int)$tableStmt->fetchColumn()) > 0;
    }

    if ($tableExists['item']) {
        $stmt = $pdo->prepare(
            'SELECT c.COLUMN_NAME, c.COLUMN_TYPE, c.IS_NULLABLE, c.COLUMN_DEFAULT, c.COLUMN_KEY, c.EXTRA
             FROM information_schema.columns c
             WHERE c.table_schema = :schema AND c.table_name = :table_name
             ORDER BY c.ORDINAL_POSITION'
        );
        $stmt->execute(['schema' => $dbName, 'table_name' => 'item']);
        $itemColumns = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($duplicateGovernancePairs as $pair) {
            $camel = $pair['camel'];
            $snake = $pair['snake'];
            $quotedCamel = '`' . str_replace('`', '``', $camel) . '`';
            $quotedSnake = '`' . str_replace('`', '``', $snake) . '`';
            $sql = "SELECT
                    COUNT(*) AS total_rows,
                    SUM(CASE WHEN (NULLIF(TRIM(COALESCE({$quotedCamel}, '')), '') IS NULL) AND (NULLIF(TRIM(COALESCE({$quotedSnake}, '')), '') IS NULL) THEN 1 ELSE 0 END) AS both_blank,
                    SUM(CASE WHEN (NULLIF(TRIM(COALESCE({$quotedCamel}, '')), '') IS NOT NULL) AND (NULLIF(TRIM(COALESCE({$quotedSnake}, '')), '') IS NULL) THEN 1 ELSE 0 END) AS only_camel,
                    SUM(CASE WHEN (NULLIF(TRIM(COALESCE({$quotedCamel}, '')), '') IS NULL) AND (NULLIF(TRIM(COALESCE({$quotedSnake}, '')), '') IS NOT NULL) THEN 1 ELSE 0 END) AS only_snake,
                    SUM(CASE WHEN (NULLIF(TRIM(COALESCE({$quotedCamel}, '')), '') IS NOT NULL) AND (NULLIF(TRIM(COALESCE({$quotedSnake}, '')), '') IS NOT NULL) AND (TRIM(CAST({$quotedCamel} AS CHAR)) = TRIM(CAST({$quotedSnake} AS CHAR))) THEN 1 ELSE 0 END) AS both_same,
                    SUM(CASE WHEN (NULLIF(TRIM(COALESCE({$quotedCamel}, '')), '') IS NOT NULL) AND (NULLIF(TRIM(COALESCE({$quotedSnake}, '')), '') IS NOT NULL) AND (TRIM(CAST({$quotedCamel} AS CHAR)) <> TRIM(CAST({$quotedSnake} AS CHAR))) THEN 1 ELSE 0 END) AS both_diff
                FROM item";
            $pairStmt = $pdo->query($sql);
            $stats = $pairStmt ? ($pairStmt->fetch(PDO::FETCH_ASSOC) ?: []) : [];

            $diffSql = "SELECT itemId, itemName, CAST({$quotedCamel} AS CHAR) AS camel_value, CAST({$quotedSnake} AS CHAR) AS snake_value
                        FROM item
                        WHERE NULLIF(TRIM(COALESCE({$quotedCamel}, '')), '') IS NOT NULL
                          AND NULLIF(TRIM(COALESCE({$quotedSnake}, '')), '') IS NOT NULL
                          AND TRIM(CAST({$quotedCamel} AS CHAR)) <> TRIM(CAST({$quotedSnake} AS CHAR))
                        ORDER BY itemId ASC
                        LIMIT 10";
            $diffStmt = $pdo->query($diffSql);
            $diffExamples = $diffStmt ? ($diffStmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

            $duplicatePairStats[$camel . '|' . $snake] = [
                'total_rows' => (int)($stats['total_rows'] ?? 0),
                'both_blank' => (int)($stats['both_blank'] ?? 0),
                'only_camel' => (int)($stats['only_camel'] ?? 0),
                'only_snake' => (int)($stats['only_snake'] ?? 0),
                'both_same' => (int)($stats['both_same'] ?? 0),
                'both_diff' => (int)($stats['both_diff'] ?? 0),
                'diff_examples' => $diffExamples,
            ];
        }
    }

    if ($tableExists['hero_override']) {
        $stmt = $pdo->prepare(
            'SELECT c.COLUMN_NAME, c.COLUMN_TYPE, c.IS_NULLABLE, c.COLUMN_DEFAULT, c.COLUMN_KEY, c.EXTRA
             FROM information_schema.columns c
             WHERE c.table_schema = :schema AND c.table_name = :table_name
             ORDER BY c.ORDINAL_POSITION'
        );
        $stmt->execute(['schema' => $dbName, 'table_name' => 'hero_override']);
        $heroOverrideColumns = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$itemColumnNames = array_map(static fn($c) => (string)$c['COLUMN_NAME'], $itemColumns);
$itemColumnSet = array_fill_keys($itemColumnNames, true);

$requiredFields = [
    'db_itemId','db_item_id','model_id','itemName_fully_derived','item_name_fully_derived',
    'CropAllowed','crop_allowed','hero_image','chosen_image','thumbnails_json','images','videos'
];

$driftPairs = [
    ['db_itemId','db_item_id'],
    ['CropAllowed','crop_allowed'],
    ['itemName_fully_derived','item_name_fully_derived'],
    ['subCategory','subcategory'],
    ['ageGroup','age_group'],
    ['sizeType','size_type'],
    ['fitStyle','fit_style'],
    ['activityTags','activity_tags'],
];

$csvExact = 0;
$csvAlias = 0;
$csvDuplicateRisk = 0;
$csvMissingRuntime = 0;
$csvStaging = 0;
$csvVerifyFirst = 0;
$comparisonRows = [];

foreach ($headers as $csvCol) {
    $exact = isset($itemColumnSet[$csvCol]);
    $aliasTarget = $aliasPairs[$csvCol] ?? null;
    $aliasFound = $aliasTarget !== null && isset($itemColumnSet[$aliasTarget]);

    $status = 'missing runtime candidate';
    if (in_array($csvCol, $stagingOnly, true)) {
        $status = 'staging/import only';
        $csvStaging++;
    } elseif (in_array($csvCol, $verifyFirst, true)) {
        $status = 'verify-first compatibility decision';
        $csvVerifyFirst++;
    } elseif ($exact) {
        if ($aliasFound && in_array($csvCol, array_column($duplicateGovernancePairs, 'camel'), true)) {
            $status = 'supported, but duplicate naming decision required';
            $csvDuplicateRisk++;
        } else {
            $status = 'already supported';
            $csvExact++;
        }
    } elseif ($aliasFound) {
        $status = 'supported via alias';
        $csvAlias++;
    } else {
        $csvMissingRuntime++;
    }

    $comparisonRows[] = [
        'csv' => $csvCol,
        'decision' => $csvCol === 'assignment_source'
            ? 'existing live column, but design classifies as staging/import-only; manual decision required before import allowlist'
            : ($runtimeDecisions[$csvCol] ?? 'not explicitly specified in migration design'),
        'exact' => $exact ? 'yes' : 'no',
        'alias' => $aliasFound ? ('yes (`' . $aliasTarget . '`)') : 'no',
        'status' => $status,
    ];
}

$heroOverrideColumnSet = array_fill_keys(array_map(static fn($c) => (string)$c['COLUMN_NAME'], $heroOverrideColumns), true);

$md = [];
$md[] = '# Live Schema Verification Report (Read-Only)';
$md[] = '';
$md[] = '- Generated: ' . gmdate('Y-m-d H:i:s') . ' UTC';
$md[] = '- Source CSV: `docs/data/SportWarehouse_ProductDB.csv`';
$md[] = '- Migration design reference: `README/V-AUDIT/POST-AUDIT/2026-05-20-MySQL-Schema-Migration-Design-No-Execution.md`';
if (!is_readable($designPath)) {
    $md[] = '- Warning: migration design file was not readable at generation time.';
}
if ($dbError !== null) {
    $md[] = '- DB status: connection failed (`' . str_replace('`', '\\`', $dbError) . '`)';
}
$md[] = '';
$md[] = '## 1) Purpose and constraints';
$md[] = '- This is a **read-only verification report**.';
$md[] = '- Allowed inspection methods: `SELECT`, `SHOW`, `DESCRIBE`, `information_schema` reads only.';
$md[] = '- No DB writes.';
$md[] = '- No migrations.';
$md[] = '- No `ALTER TABLE`.';
$md[] = '- No repair SQL.';
$md[] = '- No importer execution.';
$md[] = '- No image edits.';
$md[] = '- No Hero Manager / Hero Editor behavior changes.';
$md[] = '';
$md[] = '## 2) Live table presence';
foreach ($tableExists as $t => $exists) {
    $md[] = '- `' . $t . '` exists: **' . ($exists ? 'yes' : 'no') . '**';
}
$md[] = '';
$md[] = '## 3) Live item column inventory';
if (!$tableExists['item']) {
    $md[] = '- `item` table not found, so item column inventory is unavailable.';
} else {
    $md[] = '| Column | Data type | Nullable | Default | Key | Extra |';
    $md[] = '|---|---|---|---|---|---|';
    foreach ($itemColumns as $col) {
        $defaultValue = $col['COLUMN_DEFAULT'];
        if ($defaultValue === null) {
            $defaultValue = 'NULL';
        } elseif ($defaultValue === '') {
            $defaultValue = "''";
        }
        $md[] = sprintf(
            '| `%s` | `%s` | `%s` | `%s` | `%s` | `%s` |',
            $col['COLUMN_NAME'],
            $col['COLUMN_TYPE'],
            $col['IS_NULLABLE'],
            str_replace('|', '\\|', (string)$defaultValue),
            $col['COLUMN_KEY'] !== '' ? $col['COLUMN_KEY'] : '-',
            $col['EXTRA'] !== '' ? $col['EXTRA'] : '-'
        );
    }
}
$md[] = '';
$md[] = '## 4) Required field verification';
foreach ($requiredFields as $field) {
    $md[] = '- `item.' . $field . '` exists: **' . (isset($itemColumnSet[$field]) ? 'yes' : 'no') . '**';
}
$md[] = '';
$md[] = '## 5) Naming-drift / duplicate-column risk';
$md[] = '| Field pair | Classification | Notes |';
$md[] = '|---|---|---|';
foreach ($driftPairs as [$a, $b]) {
    $hasA = isset($itemColumnSet[$a]);
    $hasB = isset($itemColumnSet[$b]);
    if ($hasA && $hasB) {
        $class = 'duplicate-risk requires manual decision';
        $notes = 'Both forms exist in live schema.';
    } elseif ($hasA || $hasB) {
        $class = 'safe existing canonical';
        $notes = 'Only one form exists (`' . ($hasA ? $a : $b) . '`).';
    } elseif (isset($aliasPairs[$a]) || isset($aliasPairs[$b])) {
        $class = 'alias/mapping required';
        $notes = 'Neither form exists; importer mapping needed if required by CSV.';
    } else {
        $class = 'missing candidate for migration design';
        $notes = 'Neither form exists.';
    }
    $md[] = '| `' . $a . '` / `' . $b . '` | ' . $class . ' | ' . $notes . ' |';
}
$md[] = '';
$md[] = '## 5A) Naming convention / duplicate-column governance';
$md[] = '- This section treats camelCase/snake_case duplicates as a **naming-governance architecture decision**, not simple schema drift.';
$md[] = '| Pair | camelCase in live `item` | snake_case in live `item` | CSV header form | Data occupancy/read-only comparison | Recommendation |';
$md[] = '|---|---|---|---|---|---|';
foreach ($duplicateGovernancePairs as $pair) {
    $camel = $pair['camel'];
    $snake = $pair['snake'];
    $hasCamel = isset($itemColumnSet[$camel]);
    $hasSnake = isset($itemColumnSet[$snake]);
    $csvForm = in_array($camel, $headers, true) ? ('camelCase (`' . $camel . '`)') : (in_array($snake, $headers, true) ? ('snake_case (`' . $snake . '`)') : 'not present in CSV headers');

    $comparison = 'DB unavailable or both columns not present for live read-only comparison.';
    $recommendation = 'manual decision required';

    if ($dbError === null && $tableExists['item'] && $hasCamel && $hasSnake) {
        $key = $camel . '|' . $snake;
        $stats = $duplicatePairStats[$key] ?? null;
        if ($stats !== null) {
            if ($stats['both_diff'] > 0) {
                $relationship = 'both columns contain differing values';
                $recommendation = 'manual decision required';
            } elseif ($stats['only_snake'] > 0 && $stats['only_camel'] === 0) {
                $relationship = 'snake_case mostly populated, camelCase mostly blank';
                $recommendation = 'prefer runtime snake_case';
            } elseif ($stats['only_camel'] > 0 && $stats['only_snake'] === 0) {
                $relationship = 'camelCase mostly populated, snake_case mostly blank';
                $recommendation = 'prefer CSV camelCase';
            } elseif ($stats['both_same'] > 0 && $stats['only_camel'] === 0 && $stats['only_snake'] === 0) {
                $relationship = 'values identical where populated';
                $recommendation = 'keep existing temporarily with alias mapping';
            } else {
                $relationship = 'mixed occupancy across both naming forms';
                $recommendation = 'manual decision required';
            }
            $comparison = sprintf(
                'rows=%d; both blank=%d; only camel=%d; only snake=%d; both same=%d; both different=%d; %s',
                $stats['total_rows'],
                $stats['both_blank'],
                $stats['only_camel'],
                $stats['only_snake'],
                $stats['both_same'],
                $stats['both_diff'],
                $relationship
            );
        }
    }

    $md[] = '| `' . $camel . '` / `' . $snake . '` | ' . ($hasCamel ? 'yes' : 'no') . ' | ' . ($hasSnake ? 'yes' : 'no') . ' | ' . $csvForm . ' | ' . $comparison . ' | ' . $recommendation . ' |';

    if ($dbError === null && $tableExists['item'] && $hasCamel && $hasSnake) {
        $key = $camel . '|' . $snake;
        $stats = $duplicatePairStats[$key] ?? null;
        if ($stats !== null && $stats['both_diff'] > 0) {
            $md[] = '';
            $md[] = '- First 10 differing examples for `' . $camel . '` vs `' . $snake . '`: ';
            $md[] = '  - Columns: `itemId`, `itemName`, camelCase value, snake_case value';
            foreach ($stats['diff_examples'] as $ex) {
                $md[] = '  - `itemId=' . (string)$ex['itemId'] . '`, `itemName=' . str_replace('`', '\`', (string)$ex['itemName']) . '`, camel=`' . str_replace('`', '\`', (string)$ex['camel_value']) . '`, snake=`' . str_replace('`', '\`', (string)$ex['snake_value']) . '`';
            }
        }
    }
}
$md[] = '';
$md[] = '## 6) CSV-to-live schema comparison';
$md[] = '| CSV column | Expected runtime decision | Exact live item column exists | Alias/mapped live item column exists | Recommended planning status |';
$md[] = '|---|---|---|---|---|';
foreach ($comparisonRows as $row) {
    $md[] = '| `' . $row['csv'] . '` | ' . $row['decision'] . ' | ' . $row['exact'] . ' | ' . $row['alias'] . ' | ' . $row['status'] . ' |';
}
$md[] = '';
$md[] = '## 7) Protected-field verification';
$md[] = '- `item.hero_image` exists: **' . (isset($itemColumnSet['hero_image']) ? 'yes' : 'no') . '**';
$md[] = '- `item.chosen_image` exists: **' . (isset($itemColumnSet['chosen_image']) ? 'yes' : 'no') . '**';
$md[] = '- `hero_override` table exists: **' . ($tableExists['hero_override'] ? 'yes' : 'no') . '**';
$md[] = '- `hero_override.chosen_image` exists: **' . (isset($heroOverrideColumnSet['chosen_image']) ? 'yes' : 'no') . '**';
$md[] = '- These fields must **not** be included in any future CSV overwrite allowlist.';
$md[] = '';
$md[] = '## 8) Summary / next-step recommendation';
$md[] = '- Live item column count: **' . count($itemColumns) . '**';
$md[] = '- CSV header count: **' . count($headers) . '**';
$md[] = '- CSV columns already supported exactly: **' . $csvExact . '**';
$md[] = '- CSV columns supported via alias: **' . $csvAlias . '**';
$md[] = '- CSV columns missing from live item and candidate runtime additions: **' . $csvMissingRuntime . '**';
$md[] = '- CSV columns classified as staging/import only: **' . $csvStaging . '**';
$duplicateDetected = count($duplicateGovernancePairs);
$duplicateBothPresent = 0;
$duplicateWithDiffs = 0;
foreach ($duplicateGovernancePairs as $pair) {
    $camel = $pair['camel'];
    $snake = $pair['snake'];
    if (isset($itemColumnSet[$camel]) && isset($itemColumnSet[$snake])) {
        $duplicateBothPresent++;
        $stats = $duplicatePairStats[$camel . '|' . $snake] ?? null;
        if ($stats !== null && $stats['both_diff'] > 0) {
            $duplicateWithDiffs++;
        }
    }
}
$manualNamingGovernance = $duplicateBothPresent;
$md[] = '- Duplicate naming pairs detected: **' . $duplicateDetected . '**';
$md[] = '- Duplicate naming pairs with both columns present: **' . $duplicateBothPresent . '**';
$md[] = '- Duplicate naming pairs with value differences: **' . $duplicateWithDiffs . '**';
$md[] = '- Verify-first compatibility decisions: **' . $csvVerifyFirst . '**';
$md[] = '- Manual naming-governance decisions required: **' . $manualNamingGovernance . '**';
if ($dbError !== null) {
    $md[] = '- Recommendation: DB was unreachable, so manual live schema review is still required before drafting illustrative migration SQL.';
} elseif (!$tableExists['item']) {
    $md[] = '- Recommendation: `item` table missing in live DB context; manual schema review is required before drafting illustrative migration SQL.';
} else {
    $md[] = '- Recommendation: live schema verification is complete for this snapshot; proceed to drafting **illustrative** migration SQL only after manual review of duplicate-risk decisions.';
}

$mdText = implode("\n", $md) . "\n";
if (file_put_contents($outPath, $mdText) === false) {
    fwrite(STDERR, "Failed writing report: {$outPath}\n");
    exit(1);
}

echo "Generated report: {$outPath}\n";
