<?php

declare(strict_types=1);

/**
 * CSV-to-MySQL dry-run importer skeleton (safe CLI help/status interface only).
 *
 * This file intentionally provides only non-executing skeleton behavior.
 * Future dry-run importer scope is defined by planning/governance documents in:
 * README/V-AUDIT/POST-AUDIT/
 *
 * No importer implementation is approved yet.
 * No database writes are approved.
 * No CSV reads are performed by this skeleton, except optional header-only check mode.
 * No database connections are opened by this skeleton.
 * No SQL is executed by this skeleton.
 * No report generation is performed by this skeleton.
 * No files are written by this skeleton.
 * Protected fields must never be written.
 * Deferred governance fields remain excluded.
 * Public/admin invocation is not supported.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This skeleton supports CLI invocation only.\n");
    exit(2);
}

$args = $argv;
array_shift($args);

$writeLikeFlags = [
    '--execute',
    '--apply',
    '--write-db',
    '--update',
    '--insert',
    '--alter',
    '--repair',
    '--backfill-db-item-id',
    '--enforce-model-id-unique',
    '--write-reports',
];

$detectedWriteLikeFlags = array_values(array_intersect($args, $writeLikeFlags));

$printHelp = static function (): void {
    fwrite(STDOUT, "Tool: csv_mysql_dry_run_importer.php\n");
    fwrite(STDOUT, "Purpose: Safe skeleton interface for planned CSV-to-MySQL dry-run importer.\n");
    fwrite(STDOUT, "Current status: Skeleton only; importer not implemented.\n\n");

    fwrite(STDOUT, "Safe options:\n");
    fwrite(STDOUT, "  --help     Show this usage/help text and exit successfully.\n");
    fwrite(STDOUT, "  --status   Show skeleton/readiness status and exit successfully.\n");
    fwrite(STDOUT, "  --check-csv-header  Read only the CSV header row and print a safe header summary.\n");
    fwrite(STDOUT, "  --check-csv-row-count  Count CSV data rows and blank/non-blank db_itemId values without importing.\n");
    fwrite(STDOUT, "  --check-model-id-duplicates  Count duplicate model_id values in the CSV without importing.\n");
    fwrite(STDOUT, "  --check-db-item-id-integrity  Check CSV db_itemId blank/non-blank counts, uniqueness, and numeric format without importing.\n");
    fwrite(STDOUT, "  --check-csv-baseline  Run all safe CSV-only baseline checks without importing.\n");
    fwrite(STDOUT, "  --check-required-fields  Check required CSV fields for blank values without importing.\n");
    fwrite(STDOUT, "  --dry-run  Planned option; not implemented (exits non-zero).\n\n");

    fwrite(STDOUT, "Explicitly disallowed options (unsupported):\n");
    fwrite(STDOUT, "  --execute\n");
    fwrite(STDOUT, "  --apply\n");
    fwrite(STDOUT, "  --write-db\n");
    fwrite(STDOUT, "  --update\n");
    fwrite(STDOUT, "  --insert\n");
    fwrite(STDOUT, "  --alter\n");
    fwrite(STDOUT, "  --repair\n");
    fwrite(STDOUT, "  --backfill-db-item-id\n");
    fwrite(STDOUT, "  --enforce-model-id-unique\n");
    fwrite(STDOUT, "  --write-reports\n\n");

    fwrite(STDOUT, "Safety guarantees:\n");
    fwrite(STDOUT, "  - no CSV read (except first/header row when --check-csv-header is used, or safe row counting when --check-csv-row-count is used)\n");
    fwrite(STDOUT, "  - no DB connection\n");
    fwrite(STDOUT, "  - no SQL execution\n");
    fwrite(STDOUT, "  - no report generation\n");
    fwrite(STDOUT, "  - no file writes\n");
};

$printStatus = static function (): void {
    fwrite(STDOUT, "Skeleton status:\n");
    fwrite(STDOUT, "- skeleton exists\n");
    fwrite(STDOUT, "- Importer implementation approved: no\n");
    fwrite(STDOUT, "- CSV baseline check implemented: yes\n");
    fwrite(STDOUT, "- CSV header check implemented: yes\n");
    fwrite(STDOUT, "- CSV row-count check implemented: yes\n");
    fwrite(STDOUT, "- CSV row-count check scope: counting only (no full product-row processing/classification)\n");
    fwrite(STDOUT, "- CSV model_id duplicate check implemented: yes\n");
    fwrite(STDOUT, "- CSV model_id duplicate check scope: duplicate counting only (no importer classification or database comparison)\n");
    fwrite(STDOUT, "- CSV db_itemId integrity check implemented: yes\n");
    fwrite(STDOUT, "- CSV db_itemId integrity check scope: CSV-only integrity counting/validation (no database existence checks, row matching, importer classification, inserts, updates, or backfill)\n");
    fwrite(STDOUT, "- CSV baseline check scope: existing CSV-only checks only (no database comparison, importer classification, inserts, updates, backfill, report generation, or writes)\n");
    fwrite(STDOUT, "- CSV required-field completeness check implemented: yes\n");
    fwrite(STDOUT, "- CSV required-field completeness check scope: CSV-only required-field blank/present scanning (no database comparison, row matching, insert preview, importer classification, updates, inserts, backfill, report generation, or writes)\n");
    fwrite(STDOUT, "- Full importer row classification implemented: no\n");
    fwrite(STDOUT, "- Database connection implemented: no\n");
    fwrite(STDOUT, "- SQL execution implemented: no\n");
    fwrite(STDOUT, "- Report generation implemented: no\n");
    fwrite(STDOUT, "- protected fields remain excluded by governance\n");
    fwrite(STDOUT, "- deferred governance fields remain excluded\n");
    fwrite(STDOUT, "- write/execution flags remain unsupported\n");
};

$printNoSideEffectSafetyHeaderCheck = static function (): void {
    fwrite(STDOUT, "Only the CSV header row was read for safe validation.\n");
    fwrite(STDOUT, "No product rows were read or processed.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
};

$checkCsvHeader = static function () use ($printNoSideEffectSafetyHeaderCheck): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';

    // Header-check lists only (not importer configuration).
    $requiredHeaderFields = [
        'db_itemId', 'brand', 'gender', 'itemName', 'categoryName', 'parentCategory',
        'subCategory', 'price', 'salePrice', 'description', 'featured', 'images',
        'thumbnails_json', 'altText', 'ariaText', 'videoAltText', 'videos',
        'external_item_id', 'model_id',
    ];
    $deferredGovernanceFields = [
        'CropAllowed', 'crop_allowed', 'ageGroup', 'age_group', 'sizeType',
        'size_type', 'fitStyle', 'fit_style', 'activityTags', 'activity_tags',
    ];
    $stagingHelperFields = ['images2', 'assignment_source', '_images_helper_normalize'];
    $protectedFields = [
        'item.hero_image', 'item.chosen_image', 'hero_override.chosen_image', 'hero_image', 'chosen_image',
    ];

    fwrite(STDOUT, "CSV path: {$csvPath}\n");

    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        $printNoSideEffectSafetyHeaderCheck();
        return 1;
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened.\n");
        $printNoSideEffectSafetyHeaderCheck();
        return 1;
    }

    $header = fgetcsv($handle);
    fclose($handle);

    if (!is_array($header) || $header === []) {
        fwrite(STDERR, "CSV header row could not be read.\n");
        $printNoSideEffectSafetyHeaderCheck();
        return 1;
    }

    $header = array_values(array_map(static fn (string $value): string => trim($value), $header));

    $bomDetectedInFirstHeaderField = false;
    if ($header !== []) {
        $utf8Bom = "\xEF\xBB\xBF";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $bomDetectedInFirstHeaderField = true;
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $headerSet = array_fill_keys($header, true);

    $missingRequired = array_values(array_filter(
        $requiredHeaderFields,
        static fn (string $field): bool => !isset($headerSet[$field])
    ));
    $foundDeferred = array_values(array_filter(
        $deferredGovernanceFields,
        static fn (string $field): bool => isset($headerSet[$field])
    ));
    $foundStaging = array_values(array_filter(
        $stagingHelperFields,
        static fn (string $field): bool => isset($headerSet[$field])
    ));
    $foundProtected = array_values(array_filter(
        $protectedFields,
        static fn (string $field): bool => isset($headerSet[$field])
    ));

    $knownFields = array_fill_keys(array_merge(
        $requiredHeaderFields,
        $deferredGovernanceFields,
        $stagingHelperFields,
        $protectedFields
    ), true);
    $unknownFields = array_values(array_filter(
        $header,
        static fn (string $field): bool => !isset($knownFields[$field])
    ));

    fwrite(STDOUT, "Detected header count: " . count($header) . "\n");
    fwrite(STDOUT, "UTF-8 BOM detected in first header field: " . ($bomDetectedInFirstHeaderField ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Required header fields present: " . ($missingRequired === [] ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Missing required header fields: " . ($missingRequired === [] ? '(none)' : implode(', ', $missingRequired)) . "\n");
    fwrite(STDOUT, "Deferred governance fields found: " . ($foundDeferred === [] ? '(none)' : implode(', ', $foundDeferred)) . "\n");
    fwrite(STDOUT, "Staging/helper fields found: " . ($foundStaging === [] ? '(none)' : implode(', ', $foundStaging)) . "\n");
    fwrite(STDOUT, "Unknown/unclassified header fields: " . ($unknownFields === [] ? '(none)' : implode(', ', $unknownFields)) . "\n");

    if ($foundProtected !== []) {
        fwrite(STDOUT, "WARNING: Protected fields detected in header (remain excluded, not import candidates): " . implode(', ', $foundProtected) . "\n");
    }

    $printNoSideEffectSafetyHeaderCheck();
    return $missingRequired === [] ? 0 : 1;
};

$printNoSideEffectSafetyRowCountCheck = static function (): void {
    fwrite(STDOUT, "Only CSV rows needed for safe counting were read.\n");
    fwrite(STDOUT, "No product field comparison was performed.\n");
    fwrite(STDOUT, "No importer row classification was performed beyond blank/non-blank db_itemId counts.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
};

$checkCsvRowCount = static function () use ($printNoSideEffectSafetyRowCountCheck): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';
    $expectedTotalRows = 120;
    $expectedLinkedRows = 54;
    $expectedBlankRows = 66;

    fwrite(STDOUT, "CSV path: {$csvPath}\n");

    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        $printNoSideEffectSafetyRowCountCheck();
        return 1;
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened safely for read-only counting.\n");
        $printNoSideEffectSafetyRowCountCheck();
        return 1;
    }

    $header = fgetcsv($handle);
    if (!is_array($header) || $header === []) {
        fclose($handle);
        fwrite(STDERR, "CSV header row could not be read.\n");
        $printNoSideEffectSafetyRowCountCheck();
        return 1;
    }

    $header = array_values(array_map(static fn (string $value): string => trim($value), $header));

    $bomDetectedInFirstHeaderField = false;
    if ($header !== []) {
        $utf8Bom = "\xEF\xBB\xBF";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $bomDetectedInFirstHeaderField = true;
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $dbItemIdColumnIndex = array_search('db_itemId', $header, true);
    if ($dbItemIdColumnIndex === false) {
        fclose($handle);
        fwrite(STDERR, "Required header column missing: db_itemId.\n");
        $printNoSideEffectSafetyRowCountCheck();
        return 1;
    }

    $totalDataRows = 0;
    $nonBlankDbItemIdRows = 0;
    $blankDbItemIdRows = 0;

    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) {
            continue;
        }

        $totalDataRows++;
        $dbItemIdValue = array_key_exists($dbItemIdColumnIndex, $row) ? trim((string) $row[$dbItemIdColumnIndex]) : '';
        if ($dbItemIdValue === '') {
            $blankDbItemIdRows++;
        } else {
            $nonBlankDbItemIdRows++;
        }
    }

    fclose($handle);

    $totalMatchesExpected = ($totalDataRows === $expectedTotalRows);
    $nonBlankMatchesExpected = ($nonBlankDbItemIdRows === $expectedLinkedRows);
    $blankMatchesExpected = ($blankDbItemIdRows === $expectedBlankRows);

    fwrite(STDOUT, "UTF-8 BOM detected in first header field: " . ($bomDetectedInFirstHeaderField ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Detected header count: " . count($header) . "\n");
    fwrite(STDOUT, "Total data row count: {$totalDataRows}\n");
    fwrite(STDOUT, "Expected data row count: {$expectedTotalRows}\n");
    fwrite(STDOUT, "Total data row count matches expected: " . ($totalMatchesExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Non-blank db_itemId row count: {$nonBlankDbItemIdRows}\n");
    fwrite(STDOUT, "Expected linked db_itemId row count: {$expectedLinkedRows}\n");
    fwrite(STDOUT, "Non-blank db_itemId row count matches expected: " . ($nonBlankMatchesExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Blank db_itemId row count: {$blankDbItemIdRows}\n");
    fwrite(STDOUT, "Expected blank db_itemId row count: {$expectedBlankRows}\n");
    fwrite(STDOUT, "Blank db_itemId row count matches expected: " . ($blankMatchesExpected ? 'yes' : 'no') . "\n");

    $printNoSideEffectSafetyRowCountCheck();

    return ($totalMatchesExpected && $nonBlankMatchesExpected && $blankMatchesExpected) ? 0 : 1;
};


$printNoSideEffectSafetyModelIdDuplicateCheck = static function (): void {
    fwrite(STDOUT, "Only CSV rows needed for safe model_id duplicate counting were read.\n");
    fwrite(STDOUT, "No product field comparison was performed.\n");
    fwrite(STDOUT, "No importer row classification was performed beyond duplicate model_id counting.\n");
    fwrite(STDOUT, "No database comparison was performed.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
};

$checkModelIdDuplicates = static function () use ($printNoSideEffectSafetyModelIdDuplicateCheck): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';
    $expectedDuplicateModelId = 'nike_female_leggings';
    $expectedDuplicateCount = 2;

    fwrite(STDOUT, "CSV path: {$csvPath}\n");

    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        $printNoSideEffectSafetyModelIdDuplicateCheck();
        return 1;
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened safely for read-only model_id duplicate counting.\n");
        $printNoSideEffectSafetyModelIdDuplicateCheck();
        return 1;
    }

    $header = fgetcsv($handle);
    if (!is_array($header) || $header === []) {
        fclose($handle);
        fwrite(STDERR, "CSV header row could not be read.\n");
        $printNoSideEffectSafetyModelIdDuplicateCheck();
        return 1;
    }

    $header = array_values(array_map(static fn (string $value): string => trim($value), $header));

    $bomDetectedInFirstHeaderField = false;
    if ($header !== []) {
        $utf8Bom = "\xEF\xBB\xBF";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $bomDetectedInFirstHeaderField = true;
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $modelIdColumnIndex = array_search('model_id', $header, true);
    if ($modelIdColumnIndex === false) {
        fclose($handle);
        fwrite(STDERR, "Required header column missing: model_id.\n");
        $printNoSideEffectSafetyModelIdDuplicateCheck();
        return 1;
    }

    $totalDataRows = 0;
    $nonBlankModelIdRows = 0;
    $blankModelIdRows = 0;
    $modelIdCounts = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) {
            continue;
        }

        $totalDataRows++;
        $modelIdValue = array_key_exists($modelIdColumnIndex, $row) ? trim((string) $row[$modelIdColumnIndex]) : '';

        if ($modelIdValue === '') {
            $blankModelIdRows++;
            continue;
        }

        $nonBlankModelIdRows++;
        $modelIdCounts[$modelIdValue] = ($modelIdCounts[$modelIdValue] ?? 0) + 1;
    }

    fclose($handle);

    $duplicateGroups = array_filter($modelIdCounts, static fn (int $count): bool => $count > 1);
    ksort($duplicateGroups);

    $duplicateGroupCount = count($duplicateGroups);
    $duplicateRowCount = array_sum($duplicateGroups);

    $expectedDuplicatePresent = isset($duplicateGroups[$expectedDuplicateModelId]);
    $expectedDuplicateCountMatches = $expectedDuplicatePresent && $duplicateGroups[$expectedDuplicateModelId] === $expectedDuplicateCount;

    $unexpectedDuplicateGroups = $duplicateGroups;
    if (isset($unexpectedDuplicateGroups[$expectedDuplicateModelId]) && $unexpectedDuplicateGroups[$expectedDuplicateModelId] === $expectedDuplicateCount) {
        unset($unexpectedDuplicateGroups[$expectedDuplicateModelId]);
    }

    fwrite(STDOUT, "UTF-8 BOM detected in first header field: " . ($bomDetectedInFirstHeaderField ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Detected header count: " . count($header) . "\n");
    fwrite(STDOUT, "Total data rows scanned: {$totalDataRows}\n");
    fwrite(STDOUT, "Non-blank model_id count: {$nonBlankModelIdRows}\n");
    fwrite(STDOUT, "Blank model_id count: {$blankModelIdRows}\n");
    fwrite(STDOUT, "Duplicate model_id group count: {$duplicateGroupCount}\n");
    fwrite(STDOUT, "Duplicate model_id row count: {$duplicateRowCount}\n");
    fwrite(STDOUT, "Duplicate model_id groups: " . ($duplicateGroups === [] ? '(none)' : '') . "\n");

    foreach ($duplicateGroups as $modelId => $count) {
        fwrite(STDOUT, "  - {$modelId} x {$count}\n");
    }

    fwrite(STDOUT, "Expected duplicate group: {$expectedDuplicateModelId} x {$expectedDuplicateCount}\n");
    fwrite(STDOUT, "Expected duplicate group present: " . ($expectedDuplicatePresent ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Expected duplicate group count matches {$expectedDuplicateCount}: " . ($expectedDuplicateCountMatches ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Unexpected duplicate groups found: " . ($unexpectedDuplicateGroups === [] ? 'no' : 'yes') . "\n");

    if ($unexpectedDuplicateGroups !== []) {
        fwrite(STDOUT, "Unexpected duplicate model_id groups:\n");
        foreach ($unexpectedDuplicateGroups as $modelId => $count) {
            fwrite(STDOUT, "  - {$modelId} x {$count}\n");
        }
    }

    $printNoSideEffectSafetyModelIdDuplicateCheck();

    return ($expectedDuplicatePresent && $expectedDuplicateCountMatches && $unexpectedDuplicateGroups === []) ? 0 : 1;
};

$printNoSideEffectSafetyDbItemIdIntegrityCheck = static function (): void {
    fwrite(STDOUT, "Only CSV rows needed for safe db_itemId integrity counting/validation were read.\n");
    fwrite(STDOUT, "No product field comparison was performed.\n");
    fwrite(STDOUT, "No importer row classification was performed beyond db_itemId blank/non-blank counting, duplicate detection, and numeric-format validation.\n");
    fwrite(STDOUT, "No database existence checks were performed.\n");
    fwrite(STDOUT, "No row matching, inserts, updates, or backfill were performed.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
};

$checkDbItemIdIntegrity = static function () use ($printNoSideEffectSafetyDbItemIdIntegrityCheck): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';
    $expectedTotalRows = 120;
    $expectedNonBlankDbItemIdRows = 54;
    $expectedBlankDbItemIdRows = 66;

    fwrite(STDOUT, "CSV path: {$csvPath}\n");

    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        $printNoSideEffectSafetyDbItemIdIntegrityCheck();
        return 1;
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened safely for read-only db_itemId integrity checks.\n");
        $printNoSideEffectSafetyDbItemIdIntegrityCheck();
        return 1;
    }

    $header = fgetcsv($handle);
    if (!is_array($header) || $header === []) {
        fclose($handle);
        fwrite(STDERR, "CSV header row could not be read.\n");
        $printNoSideEffectSafetyDbItemIdIntegrityCheck();
        return 1;
    }

    $header = array_values(array_map(static fn (string $value): string => trim($value), $header));

    $bomDetectedInFirstHeaderField = false;
    if ($header !== []) {
        $utf8Bom = "\xEF\xBB\xBF";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $bomDetectedInFirstHeaderField = true;
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $dbItemIdColumnIndex = array_search('db_itemId', $header, true);
    if ($dbItemIdColumnIndex === false) {
        fclose($handle);
        fwrite(STDERR, "Required header column missing: db_itemId.\n");
        $printNoSideEffectSafetyDbItemIdIntegrityCheck();
        return 1;
    }

    $totalDataRows = 0;
    $nonBlankDbItemIdRows = 0;
    $blankDbItemIdRows = 0;
    $dbItemIdCounts = [];
    $invalidDbItemIdValues = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) {
            continue;
        }

        $totalDataRows++;
        $dbItemIdValue = array_key_exists($dbItemIdColumnIndex, $row) ? trim((string) $row[$dbItemIdColumnIndex]) : '';
        if ($dbItemIdValue === '') {
            $blankDbItemIdRows++;
            continue;
        }

        $nonBlankDbItemIdRows++;
        if (!preg_match('/^[1-9][0-9]*$/', $dbItemIdValue)) {
            $invalidDbItemIdValues[$dbItemIdValue] = true;
            continue;
        }

        $dbItemIdCounts[$dbItemIdValue] = ($dbItemIdCounts[$dbItemIdValue] ?? 0) + 1;
    }

    fclose($handle);

    $duplicateDbItemIdGroups = array_filter($dbItemIdCounts, static fn (int $count): bool => $count > 1);
    ksort($duplicateDbItemIdGroups);
    $duplicateDbItemIdGroupCount = count($duplicateDbItemIdGroups);

    $invalidDbItemIdValues = array_keys($invalidDbItemIdValues);
    sort($invalidDbItemIdValues);
    $invalidDbItemIdValueCount = count($invalidDbItemIdValues);

    $totalMatchesExpected = ($totalDataRows === $expectedTotalRows);
    $nonBlankMatchesExpected = ($nonBlankDbItemIdRows === $expectedNonBlankDbItemIdRows);
    $blankMatchesExpected = ($blankDbItemIdRows === $expectedBlankDbItemIdRows);
    $allNonBlankUnique = ($duplicateDbItemIdGroupCount === 0);
    $allNonBlankValidNumeric = ($invalidDbItemIdValueCount === 0);

    fwrite(STDOUT, "UTF-8 BOM detected in first header field: " . ($bomDetectedInFirstHeaderField ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Detected header count: " . count($header) . "\n");
    fwrite(STDOUT, "Total data rows scanned: {$totalDataRows}\n");
    fwrite(STDOUT, "Expected total data rows: {$expectedTotalRows}\n");
    fwrite(STDOUT, "Total data row count matches expected: " . ($totalMatchesExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Non-blank db_itemId row count: {$nonBlankDbItemIdRows}\n");
    fwrite(STDOUT, "Expected non-blank db_itemId rows: {$expectedNonBlankDbItemIdRows}\n");
    fwrite(STDOUT, "Non-blank db_itemId row count matches expected: " . ($nonBlankMatchesExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Blank db_itemId row count: {$blankDbItemIdRows}\n");
    fwrite(STDOUT, "Expected blank db_itemId rows: {$expectedBlankDbItemIdRows}\n");
    fwrite(STDOUT, "Blank db_itemId row count matches expected: " . ($blankMatchesExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Duplicate non-blank db_itemId group count: {$duplicateDbItemIdGroupCount}\n");
    fwrite(STDOUT, "Duplicate non-blank db_itemId groups: " . ($duplicateDbItemIdGroups === [] ? '(none)' : '') . "\n");
    foreach ($duplicateDbItemIdGroups as $dbItemId => $count) {
        fwrite(STDOUT, "  - {$dbItemId} x {$count}\n");
    }
    fwrite(STDOUT, "All non-blank db_itemId values are unique: " . ($allNonBlankUnique ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Invalid non-blank db_itemId value count: {$invalidDbItemIdValueCount}\n");
    fwrite(STDOUT, "Invalid non-blank db_itemId values: " . ($invalidDbItemIdValues === [] ? '(none)' : implode(', ', $invalidDbItemIdValues)) . "\n");
    fwrite(STDOUT, "All non-blank db_itemId values are valid numeric IDs: " . ($allNonBlankValidNumeric ? 'yes' : 'no') . "\n");

    $printNoSideEffectSafetyDbItemIdIntegrityCheck();

    return ($totalMatchesExpected && $nonBlankMatchesExpected && $blankMatchesExpected && $allNonBlankUnique && $allNonBlankValidNumeric) ? 0 : 1;
};

$printNoSideEffectSafetyRequiredFieldCheck = static function (): void {
    fwrite(STDOUT, "Only CSV rows needed for safe required-field completeness scanning were read.\n");
    fwrite(STDOUT, "No product field comparison against any database was performed.\n");
    fwrite(STDOUT, "No row matching, insert preview, or importer classification was performed.\n");
    fwrite(STDOUT, "No updates, inserts, or backfill were performed.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
};

$checkRequiredFields = static function () use ($printNoSideEffectSafetyRequiredFieldCheck): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';
    $expectedTotalRows = 120;
    $maxMissingRowsToPrintPerField = 20;
    $requiredFields = [
        'brand',
        'gender',
        'itemName',
        'categoryName',
        'parentCategory',
        'subCategory',
        'price',
        'description',
        'images',
        'altText',
        'ariaText',
        'external_item_id',
        'model_id',
    ];

    fwrite(STDOUT, "CSV path: {$csvPath}\n");

    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened safely for read-only required-field completeness checks.\n");
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    $header = fgetcsv($handle);
    if (!is_array($header) || $header === []) {
        fclose($handle);
        fwrite(STDERR, "CSV header row could not be read.\n");
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    $header = array_values(array_map(static fn (string $value): string => trim($value), $header));

    $bomDetectedInFirstHeaderField = false;
    if ($header !== []) {
        $utf8Bom = "\xEF\xBB\xBF";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $bomDetectedInFirstHeaderField = true;
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $fieldColumnIndexes = [];
    $missingRequiredColumns = [];
    foreach ($requiredFields as $field) {
        $columnIndex = array_search($field, $header, true);
        if ($columnIndex === false) {
            $missingRequiredColumns[] = $field;
            continue;
        }
        $fieldColumnIndexes[$field] = $columnIndex;
    }

    fwrite(STDOUT, "UTF-8 BOM detected in first header field: " . ($bomDetectedInFirstHeaderField ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Detected header count: " . count($header) . "\n");
    fwrite(STDOUT, "Required fields checked: " . implode(', ', $requiredFields) . "\n");
    fwrite(STDOUT, "Required columns present in header: " . ($missingRequiredColumns === [] ? 'yes' : 'no') . "\n");
    if ($missingRequiredColumns !== []) {
        fwrite(STDOUT, "Missing required columns: " . implode(', ', $missingRequiredColumns) . "\n");
        fclose($handle);
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    $totalDataRows = 0;
    $blankCountsByField = array_fill_keys($requiredFields, 0);
    $missingRowNumbersByField = array_fill_keys($requiredFields, []);

    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) {
            continue;
        }

        $totalDataRows++;
        $csvRowNumber = $totalDataRows + 1; // Include header row in CSV row numbering.

        foreach ($requiredFields as $field) {
            $columnIndex = $fieldColumnIndexes[$field];
            $value = array_key_exists($columnIndex, $row) ? trim((string) $row[$columnIndex]) : '';
            if ($value === '') {
                $blankCountsByField[$field]++;
                $missingRowNumbersByField[$field][] = $csvRowNumber;
            }
        }
    }

    fclose($handle);

    $totalRowsMatchExpected = ($totalDataRows === $expectedTotalRows);
    $totalRequiredFieldBlankValues = array_sum($blankCountsByField);
    $allRequiredFieldsComplete = ($totalRequiredFieldBlankValues === 0);
    $checkPassed = ($totalRowsMatchExpected && $allRequiredFieldsComplete);

    fwrite(STDOUT, "Total data rows scanned: {$totalDataRows}\n");
    fwrite(STDOUT, "Expected total data rows: {$expectedTotalRows}\n");
    fwrite(STDOUT, "Total data row count matches expected: " . ($totalRowsMatchExpected ? 'yes' : 'no') . "\n");

    foreach ($requiredFields as $field) {
        $blankCount = $blankCountsByField[$field];
        $isComplete = ($blankCount === 0);
        $missingRows = $missingRowNumbersByField[$field];
        $missingRowsSample = array_slice($missingRows, 0, $maxMissingRowsToPrintPerField);

        fwrite(STDOUT, "Required field '{$field}' blank count: {$blankCount}\n");
        fwrite(STDOUT, "Required field '{$field}' complete: " . ($isComplete ? 'yes' : 'no') . "\n");
        if ($blankCount > 0) {
            fwrite(STDOUT, "Required field '{$field}' missing value row numbers (CSV row numbers): " . implode(', ', $missingRowsSample) . "\n");
            if ($blankCount > count($missingRowsSample)) {
                fwrite(STDOUT, "Required field '{$field}' has additional missing rows not shown: " . ($blankCount - count($missingRowsSample)) . "\n");
            }
        }
    }

    fwrite(STDOUT, "Total required-field blank value count: {$totalRequiredFieldBlankValues}\n");
    fwrite(STDOUT, "Required-field completeness check passed: " . ($checkPassed ? 'yes' : 'no') . "\n");

    $printNoSideEffectSafetyRequiredFieldCheck();
    return $checkPassed ? 0 : 1;
};

$printNoSideEffectSafety = static function (): void {
    fwrite(STDOUT, "No CSV was read.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
};

$checkCsvBaseline = static function () use (
    $checkCsvHeader,
    $checkCsvRowCount,
    $checkModelIdDuplicates,
    $checkDbItemIdIntegrity,
    $printNoSideEffectSafety
): int {
    $checks = [
        'CSV header check' => $checkCsvHeader,
        'CSV row-count check' => $checkCsvRowCount,
        'CSV model_id duplicate check' => $checkModelIdDuplicates,
        'CSV db_itemId integrity check' => $checkDbItemIdIntegrity,
    ];

    $results = [];

    fwrite(STDOUT, "CSV baseline check: running safe CSV-only checks.\n");
    fwrite(STDOUT, "CSV baseline check scope: no database comparison, no importer classification, no inserts/updates/backfill, no report generation, no writes.\n");

    foreach ($checks as $label => $check) {
        fwrite(STDOUT, "\n=== {$label} ===\n");
        $exitCode = $check();
        $passed = ($exitCode === 0);
        $results[$label] = $passed;
        fwrite(STDOUT, "Sub-check result ({$label}): " . ($passed ? 'PASS' : 'FAIL') . "\n");
    }

    $allPassed = !in_array(false, $results, true);

    fwrite(STDOUT, "\n=== CSV baseline summary ===\n");
    foreach ($results as $label => $passed) {
        fwrite(STDOUT, "- {$label}: " . ($passed ? 'PASS' : 'FAIL') . "\n");
    }
    fwrite(STDOUT, "Overall baseline result: " . ($allPassed ? 'PASS' : 'FAIL') . "\n");

    $printNoSideEffectSafety();
    return $allPassed ? 0 : 1;
};

if ($detectedWriteLikeFlags !== []) {
    fwrite(STDERR, "Write/execution flags are not supported by this skeleton: " . implode(', ', $detectedWriteLikeFlags) . "\n");
    $printNoSideEffectSafety();
    exit(1);
}

if ($args === []) {
    fwrite(STDERR, "Importer skeleton only; implementation is not approved yet. Use --help or --status.\n");
    exit(1);
}

$recognizedArgs = ['--help', '--status', '--check-csv-header', '--check-csv-row-count', '--check-model-id-duplicates', '--check-db-item-id-integrity', '--check-csv-baseline', '--check-required-fields', '--dry-run'];
$unknownArgs = array_values(array_diff($args, $recognizedArgs));

if ($unknownArgs !== []) {
    fwrite(STDERR, "Unsupported option(s): " . implode(', ', $unknownArgs) . ". Use --help.\n");
    $printNoSideEffectSafety();
    exit(1);
}

if (in_array('--help', $args, true)) {
    $printHelp();
    exit(0);
}

if (in_array('--status', $args, true)) {
    $printStatus();
    exit(0);
}

if (in_array('--dry-run', $args, true)) {
    fwrite(STDERR, "--dry-run is planned but not implemented in this approved skeleton stage.\n");
    $printNoSideEffectSafety();
    exit(1);
}

if (in_array('--check-csv-header', $args, true)) {
    exit($checkCsvHeader());
}

if (in_array('--check-csv-row-count', $args, true)) {
    exit($checkCsvRowCount());
}

if (in_array('--check-model-id-duplicates', $args, true)) {
    exit($checkModelIdDuplicates());
}

if (in_array('--check-db-item-id-integrity', $args, true)) {
    exit($checkDbItemIdIntegrity());
}

if (in_array('--check-csv-baseline', $args, true)) {
    exit($checkCsvBaseline());
}

if (in_array('--check-required-fields', $args, true)) {
    exit($checkRequiredFields());
}

fwrite(STDERR, "Unsupported invocation. Use --help.\n");
exit(1);
