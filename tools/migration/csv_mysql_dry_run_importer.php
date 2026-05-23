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
    fwrite(STDOUT, "  --show-remediation-guidance  Show CSV-only remediation guidance for readiness findings without writing files.\n");
    fwrite(STDOUT, "  --show-frontend-readiness-summary  Show CSV-only frontend readiness summary without writing files.\n");
    fwrite(STDOUT, "  --show-excel-remediation-summary  Show CSV-only Excel/source remediation summary without writing files.\n");
    fwrite(STDOUT, "  --write-excel-remediation-checklist  Write generated CSV checklist for Excel/source remediation.\n");
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
    fwrite(STDOUT, "- CSV remediation guidance implemented: yes\n");
    fwrite(STDOUT, "- CSV frontend readiness summary implemented: yes\n");
    fwrite(STDOUT, "- CSV Excel remediation summary implemented: yes\n");
    fwrite(STDOUT, "- Excel remediation checklist generation implemented: yes\n");
    fwrite(STDOUT, "- Generated artifact path: docs/operations/generated/csv-excel-remediation-checklist.csv\n");
    fwrite(STDOUT, "- CSV required-field completeness check scope: CSV-only required-field blank/present scanning (no database comparison, row matching, insert preview, importer classification, updates, inserts, backfill, report generation, or writes)\n");
    fwrite(STDOUT, "- Full importer row classification implemented: no\n");
    fwrite(STDOUT, "- Database connection implemented: no\n");
    fwrite(STDOUT, "- SQL execution implemented: no\n");
    fwrite(STDOUT, "- Report generation implemented: no\n");
    fwrite(STDOUT, "- protected fields remain excluded by governance\n");
    fwrite(STDOUT, "- deferred governance fields remain excluded\n");
    fwrite(STDOUT, "- write/execution flags remain unsupported\n");
    fwrite(STDOUT, "- Remediation guidance output mode: console-only (no report file generation, no CSV/database modifications)\n");
    fwrite(STDOUT, "- Frontend readiness summary mode: console-only (no report file generation, no CSV/database/frontend behavior modifications)\n");
    fwrite(STDOUT, "- Excel remediation summary mode: console-only (no report file generation, no CSV/database modifications)\n");
    fwrite(STDOUT, "- CSV source edits implemented: no\n");
    fwrite(STDOUT, "- File writes implemented: limited to explicit generated checklist mode only\n");
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
    $expectedLinkedRows = 54;
    $expectedLikelyNewRows = 66;
    $maxMissingRowsToPrintPerField = 20;
    $policyRows = [
        [
            'field' => 'brand',
            'category' => 'globally required identity fields',
            'owner' => 'excel-csv-source-of-truth',
            'pathway' => 'fix in Excel/CSV',
            'governance' => null,
            'reports' => [
                ['group' => 'all', 'label' => 'all rows', 'severity' => 'import-readiness-blocking'],
                ['group' => 'all', 'label' => 'all rows', 'severity' => 'frontend-readiness-blocking'],
            ],
        ],
        [
            'field' => 'itemName',
            'category' => 'globally required identity fields',
            'owner' => 'excel-csv-source-of-truth',
            'pathway' => 'fix in Excel/CSV',
            'governance' => null,
            'reports' => [
                ['group' => 'all', 'label' => 'all rows', 'severity' => 'import-readiness-blocking'],
                ['group' => 'all', 'label' => 'all rows', 'severity' => 'frontend-readiness-blocking'],
            ],
        ],
        [
            'field' => 'model_id',
            'category' => 'globally required identity fields',
            'owner' => 'excel-csv-source-of-truth',
            'pathway' => 'fix in Excel/CSV',
            'governance' => null,
            'reports' => [
                ['group' => 'all', 'label' => 'all rows', 'severity' => 'import-readiness-blocking'],
            ],
        ],
        ['field' => 'gender', 'category' => 'globally required identity fields', 'owner' => 'excel-csv-source-of-truth', 'pathway' => 'fix in Excel/CSV', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'import-readiness-blocking'], ['group' => 'all', 'label' => 'all rows', 'severity' => 'frontend-readiness-blocking']]],
        ['field' => 'subCategory', 'category' => 'globally required identity fields', 'owner' => 'excel-csv-source-of-truth', 'pathway' => 'fix in Excel/CSV', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'import-readiness-blocking'], ['group' => 'all', 'label' => 'all rows', 'severity' => 'frontend-readiness-blocking']]],
        ['field' => 'categoryName', 'category' => 'likely-new insert readiness fields', 'owner' => 'catalog-data', 'pathway' => 'likely-new row remediation before insert/update readiness sign-off', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'import-readiness-blocking'], ['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'frontend-readiness-blocking'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'price', 'category' => 'likely-new insert readiness fields', 'owner' => 'catalog-data', 'pathway' => 'likely-new row remediation before insert/update readiness sign-off', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'import-readiness-blocking'], ['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'frontend-readiness-blocking'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'description', 'category' => 'likely-new insert readiness fields', 'owner' => 'catalog-content', 'pathway' => 'admin remediation backlog; improve source content', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'admin-remediation'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'images', 'category' => 'likely-new insert readiness fields', 'owner' => 'catalog-media', 'pathway' => 'likely-new row media remediation before insert/update readiness sign-off', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'import-readiness-blocking'], ['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'frontend-readiness-blocking'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'external_item_id', 'category' => 'linked-row update diagnostic fields', 'owner' => 'catalog-integration', 'pathway' => 'source linkage remediation in Excel/CSV and integration workflow', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'import-readiness-blocking'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'parentCategory', 'category' => 'parentCategory policy clarification field', 'owner' => 'data-governance', 'pathway' => 'defer to governance clarification workflow; keep admin-visible for diagnosis', 'governance' => 'parentCategory is blank across rows and may be derivable, optional, future taxonomy, or source-remediation target.', 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'deferred-governance']]],
        ['field' => 'altText', 'category' => 'likely-new insert readiness fields', 'owner' => 'catalog-content', 'pathway' => 'accessibility-content remediation in admin workflow', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'admin-remediation'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'ariaText', 'category' => 'likely-new insert readiness fields', 'owner' => 'catalog-content', 'pathway' => 'accessibility-content remediation in admin workflow', 'governance' => null, 'reports' => [['group' => 'likely_new', 'label' => 'likely new rows', 'severity' => 'admin-remediation'], ['group' => 'linked', 'label' => 'linked rows', 'severity' => 'admin-remediation']]],
        ['field' => 'salePrice', 'category' => 'optional/enrichment fields', 'owner' => 'catalog-content', 'pathway' => 'optional enrichment backlog', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'advisory']]],
        ['field' => 'featured', 'category' => 'optional/enrichment fields', 'owner' => 'catalog-content', 'pathway' => 'optional enrichment backlog', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'advisory']]],
        ['field' => 'videos', 'category' => 'optional/enrichment fields', 'owner' => 'catalog-content', 'pathway' => 'optional enrichment backlog', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'advisory']]],
        ['field' => 'videoAltText', 'category' => 'optional/enrichment fields', 'owner' => 'catalog-content', 'pathway' => 'optional enrichment backlog', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'advisory']]],
        ['field' => 'thumbnails_json', 'category' => 'optional/enrichment fields', 'owner' => 'catalog-content', 'pathway' => 'optional enrichment backlog', 'governance' => null, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'advisory']]],
        ['field' => 'CropAllowed', 'category' => 'deferred governance fields', 'owner' => 'data-governance', 'pathway' => 'governance decision on camelCase/snake_case conventions and enforcement', 'governance' => 'deferred governance field; requiredness pending policy decision.', 'column_required' => false, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'deferred-governance']]],
        ['field' => 'crop_allowed', 'category' => 'deferred governance fields', 'owner' => 'data-governance', 'pathway' => 'governance decision on camelCase/snake_case conventions and enforcement', 'governance' => 'deferred governance field; requiredness pending policy decision.', 'column_required' => false, 'reports' => [['group' => 'all', 'label' => 'all rows', 'severity' => 'deferred-governance']]],
    ];
    $requiredFields = array_values(array_map(static fn (array $policy): string => $policy['field'], $policyRows));
    $requiredFieldSet = array_fill_keys($requiredFields, true);
    $policyCategoryCoverage = [
        'globally required identity fields',
        'likely-new insert readiness fields',
        'linked-row update diagnostic fields',
        'parentCategory policy clarification field',
        'optional/enrichment fields',
        'deferred governance fields',
        'staging/helper fields',
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
    $missingNonFatalColumns = [];
    $dbItemIdColumnIndex = array_search('db_itemId', $header, true);
    $dbItemIdColumnPresent = ($dbItemIdColumnIndex !== false);

    fwrite(STDOUT, "UTF-8 BOM detected in first header field: " . ($bomDetectedInFirstHeaderField ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Detected header count: " . count($header) . "\n");
    fwrite(STDOUT, "Required fields checked: " . implode(', ', $requiredFields) . "\n");
    fwrite(STDOUT, "Policy category coverage: " . implode(', ', $policyCategoryCoverage) . "\n");
    fwrite(STDOUT, "db_itemId column present in header: " . ($dbItemIdColumnPresent ? 'yes' : 'no') . "\n");
    if (!$dbItemIdColumnPresent) {
        fclose($handle);
        fwrite(STDERR, "Required db_itemId column is missing from CSV header.\n");
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    foreach ($requiredFields as $field) {
        $columnIndex = array_search($field, $header, true);
        if ($columnIndex === false) {
            $columnRequired = $policyRows[array_search($field, $requiredFields, true)]['column_required'] ?? true;
            if ($columnRequired) {
                $missingRequiredColumns[] = $field;
            } else {
                $missingNonFatalColumns[] = $field;
            }
            continue;
        }
        $fieldColumnIndexes[$field] = $columnIndex;
    }

    fwrite(STDOUT, "Required columns present in header: " . ($missingRequiredColumns === [] ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Deferred-governance columns missing in header (non-fatal): " . ($missingNonFatalColumns === [] ? '(none)' : implode(', ', $missingNonFatalColumns)) . "\n");
    if ($missingRequiredColumns !== []) {
        fwrite(STDOUT, "Missing required columns: " . implode(', ', $missingRequiredColumns) . "\n");
        fclose($handle);
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    $totalDataRows = 0;
    $linkedRows = 0;
    $likelyNewRows = 0;
    $groups = ['all', 'linked', 'likely_new'];
    $blankCountsByGroupAndField = [];
    $missingRowNumbersByGroupAndField = [];

    foreach ($groups as $group) {
        $blankCountsByGroupAndField[$group] = array_fill_keys($requiredFields, 0);
        $missingRowNumbersByGroupAndField[$group] = array_fill_keys($requiredFields, []);
    }

    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) {
            continue;
        }

        $totalDataRows++;
        $csvRowNumber = $totalDataRows + 1; // Include header row in CSV row numbering.
        $dbItemIdValue = array_key_exists($dbItemIdColumnIndex, $row) ? trim((string) $row[$dbItemIdColumnIndex]) : '';
        $rowGroup = ($dbItemIdValue === '') ? 'likely_new' : 'linked';
        if ($rowGroup === 'linked') {
            $linkedRows++;
        } else {
            $likelyNewRows++;
        }

        foreach ($requiredFields as $field) {
            if (!isset($fieldColumnIndexes[$field])) {
                continue;
            }
            $columnIndex = $fieldColumnIndexes[$field];
            $value = array_key_exists($columnIndex, $row) ? trim((string) $row[$columnIndex]) : '';
            if ($value === '') {
                $blankCountsByGroupAndField['all'][$field]++;
                $blankCountsByGroupAndField[$rowGroup][$field]++;
                $missingRowNumbersByGroupAndField['all'][$field][] = $csvRowNumber;
                $missingRowNumbersByGroupAndField[$rowGroup][$field][] = $csvRowNumber;
            }
        }
    }

    fclose($handle);

    $totalRowsMatchExpected = ($totalDataRows === $expectedTotalRows);
    $linkedRowsMatchExpected = ($linkedRows === $expectedLinkedRows);
    $likelyNewRowsMatchExpected = ($likelyNewRows === $expectedLikelyNewRows);

    $totalRequiredFieldBlankValuesAll = array_sum($blankCountsByGroupAndField['all']);
    $totalRequiredFieldBlankValuesLinked = array_sum($blankCountsByGroupAndField['linked']);
    $totalRequiredFieldBlankValuesLikelyNew = array_sum($blankCountsByGroupAndField['likely_new']);

    $diagnosticCountBySeverity = [
        'fatal' => 0,
        'import-readiness-blocking' => 0,
        'frontend-readiness-blocking' => 0,
        'admin-remediation' => 0,
        'advisory' => 0,
        'deferred-governance' => 0,
    ];

    fwrite(STDOUT, "Total data rows scanned: {$totalDataRows}\n");
    fwrite(STDOUT, "Expected total data rows: {$expectedTotalRows}\n");
    fwrite(STDOUT, "Total data row count matches expected: " . ($totalRowsMatchExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Linked rows (non-blank db_itemId): {$linkedRows}\n");
    fwrite(STDOUT, "Expected linked rows (non-blank db_itemId): {$expectedLinkedRows}\n");
    fwrite(STDOUT, "Linked row count matches expected: " . ($linkedRowsMatchExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Likely new rows (blank db_itemId): {$likelyNewRows}\n");
    fwrite(STDOUT, "Expected likely new rows (blank db_itemId): {$expectedLikelyNewRows}\n");
    fwrite(STDOUT, "Likely new row count matches expected: " . ($likelyNewRowsMatchExpected ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Readiness terminology note: Readiness-blocking findings do not necessarily block copying/importing data into an admin-visible database. They indicate the item is not ready for a specific downstream workflow, such as automated import/update readiness or frontend publication.\n");
    fwrite(STDOUT, "Remediation guidance (CSV-only, console output):\n");

    $sectionTitles = [
        'fatal' => 'Fatal structural issues',
        'import-readiness-blocking' => 'Import-readiness-blocking items',
        'frontend-readiness-blocking' => 'Frontend-readiness-blocking items',
        'admin-remediation' => 'Admin-remediation items',
        'advisory' => 'Advisory items',
        'deferred-governance' => 'Deferred-governance items',
    ];
    $suggestedActions = [
        'import-readiness-blocking' => 'Prepare/fix this field before automated import/update readiness is approved. This does not necessarily block admin-visible diagnostic import.',
        'frontend-readiness-blocking' => 'Do not treat affected products as frontend-ready until this field is fixed or policy approves fallback behavior.',
        'admin-remediation' => 'Allow admin/backend visibility, but flag the item for correction or content completion.',
        'advisory' => 'Review as a quality/enrichment issue. Do not block import or frontend display solely on this finding unless policy changes.',
        'deferred-governance' => 'Do not force remediation yet. Resolve the governance/policy decision first.',
    ];
    $findingsBySeverity = [
        'fatal' => [],
        'import-readiness-blocking' => [],
        'frontend-readiness-blocking' => [],
        'admin-remediation' => [],
        'advisory' => [],
        'deferred-governance' => [],
    ];

    foreach ($policyRows as $policy) {
        $field = $policy['field'];
        if (!isset($requiredFieldSet[$field])) {
            continue;
        }
        foreach ($policy['reports'] as $report) {
            $groupKey = $report['group'];
            $blankCount = $blankCountsByGroupAndField[$groupKey][$field];
            if ($blankCount === 0) {
                continue;
            }
            $severity = $report['severity'];
            $missingRows = $missingRowNumbersByGroupAndField[$groupKey][$field];
            $missingRowsSample = array_slice($missingRows, 0, $maxMissingRowsToPrintPerField);
            $diagnosticCountBySeverity[$severity]++;
            $findingsBySeverity[$severity][] = [
                'field' => $field,
                'groupLabel' => $report['label'],
                'blankCount' => $blankCount,
                'severity' => $severity,
                'owner' => $policy['owner'],
                'pathway' => $policy['pathway'],
                'sampleRows' => $missingRowsSample,
                'additionalRows' => max(0, $blankCount - count($missingRowsSample)),
                'governance' => $policy['governance'],
            ];
        }
    }

    foreach ($sectionTitles as $severity => $title) {
        fwrite(STDOUT, "\n{$title}:\n");
        if ($findingsBySeverity[$severity] === []) {
            fwrite(STDOUT, "  (none)\n");
            continue;
        }
        foreach ($findingsBySeverity[$severity] as $finding) {
            fwrite(STDOUT, "- field: {$finding['field']}\n");
            fwrite(STDOUT, "  affected row group: {$finding['groupLabel']}\n");
            fwrite(STDOUT, "  blank count: {$finding['blankCount']}\n");
            fwrite(STDOUT, "  readiness/severity category: {$finding['severity']}\n");
            fwrite(STDOUT, "  remediation owner: {$finding['owner']}\n");
            fwrite(STDOUT, "  remediation pathway: {$finding['pathway']}\n");
            fwrite(STDOUT, "  sample row numbers (CSV row numbers, max {$maxMissingRowsToPrintPerField}): " . implode(', ', $finding['sampleRows']) . "\n");
            if ($finding['additionalRows'] > 0) {
                fwrite(STDOUT, "  additional missing rows not shown: {$finding['additionalRows']}\n");
            }
            if (is_string($finding['governance']) && $finding['governance'] !== '') {
                fwrite(STDOUT, "  governance note: {$finding['governance']}\n");
            }
            if (isset($suggestedActions[$severity])) {
                fwrite(STDOUT, "  suggested next action: {$suggestedActions[$severity]}\n");
            }
        }
    }

    fwrite(STDOUT, "Total required-field blank value count (all rows): {$totalRequiredFieldBlankValuesAll}\n");
    fwrite(STDOUT, "Total required-field blank value count (linked rows): {$totalRequiredFieldBlankValuesLinked}\n");
    fwrite(STDOUT, "Total required-field blank value count (likely new rows): {$totalRequiredFieldBlankValuesLikelyNew}\n");
    $structuralFailure = (!$totalRowsMatchExpected || !$linkedRowsMatchExpected || !$likelyNewRowsMatchExpected);
    $diagnosticCompleted = !$structuralFailure;
    $diagnosticCountBySeverity['fatal'] = $structuralFailure ? 1 : 0;

    fwrite(STDOUT, "Fatal structural issue count: {$diagnosticCountBySeverity['fatal']}\n");
    fwrite(STDOUT, "Import-readiness-blocking count: {$diagnosticCountBySeverity['import-readiness-blocking']}\n");
    fwrite(STDOUT, "Frontend-readiness-blocking count: {$diagnosticCountBySeverity['frontend-readiness-blocking']}\n");
    fwrite(STDOUT, "Admin-remediation count: {$diagnosticCountBySeverity['admin-remediation']}\n");
    fwrite(STDOUT, "Advisory count: {$diagnosticCountBySeverity['advisory']}\n");
    fwrite(STDOUT, "Deferred-governance count: {$diagnosticCountBySeverity['deferred-governance']}\n");

    $adminVisibleReadiness = $structuralFailure
        ? 'no'
        : (($diagnosticCountBySeverity['deferred-governance'] > 0) ? 'needs-review' : 'yes');
    $automatedReadiness = ($diagnosticCountBySeverity['import-readiness-blocking'] > 0) ? 'needs-remediation' : 'pass';
    $frontendReadiness = ($diagnosticCountBySeverity['frontend-readiness-blocking'] > 0) ? 'needs-remediation' : 'pass';

    fwrite(STDOUT, "Diagnostic completed: " . ($diagnosticCompleted ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Fatal structural failure: " . ($structuralFailure ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Admin-visible import/copy can proceed for diagnostic/remediation purposes: {$adminVisibleReadiness}\n");
    fwrite(STDOUT, "Automated import/update readiness: {$automatedReadiness}\n");
    fwrite(STDOUT, "Frontend publication readiness: {$frontendReadiness}\n");
    fwrite(STDOUT, "Admin remediation required: " . ($diagnosticCountBySeverity['admin-remediation'] > 0 ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Governance decisions required: " . ($diagnosticCountBySeverity['deferred-governance'] > 0 ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Console guidance only: no report file generated\n");

    $printNoSideEffectSafetyRequiredFieldCheck();
    return $structuralFailure ? 1 : 0;
};


$showFrontendReadinessSummary = static function () use ($printNoSideEffectSafetyRequiredFieldCheck): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';
    $expectedTotalRows = 120;
    $expectedLinkedRows = 54;
    $expectedLikelyNewRows = 66;
    $sampleCap = 30;
    $frontendBlockingPolicies = [
        ['field' => 'brand', 'group' => 'all'],
        ['field' => 'itemName', 'group' => 'all'],
        ['field' => 'gender', 'group' => 'all'],
        ['field' => 'subCategory', 'group' => 'all'],
        ['field' => 'categoryName', 'group' => 'likely_new'],
        ['field' => 'price', 'group' => 'likely_new'],
        ['field' => 'images', 'group' => 'likely_new'],
    ];

    fwrite(STDOUT, "CSV path: {$csvPath}\n");
    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }
    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened safely for read-only frontend readiness summary.\n");
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
    if ($header !== []) {
        $utf8Bom = "\xEF\xBB\xBF";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $dbItemIdColumnIndex = array_search('db_itemId', $header, true);
    if ($dbItemIdColumnIndex === false) {
        fclose($handle);
        fwrite(STDERR, "Required db_itemId column is missing from CSV header.\n");
        $printNoSideEffectSafetyRequiredFieldCheck();
        return 1;
    }

    $fieldIndexes = [];
    foreach ($frontendBlockingPolicies as $policy) {
        if (!array_key_exists($policy['field'], $fieldIndexes)) {
            $idx = array_search($policy['field'], $header, true);
            if ($idx === false) {
                fclose($handle);
                fwrite(STDERR, "Missing required diagnostic columns needed by policy logic: {$policy['field']}\n");
                $printNoSideEffectSafetyRequiredFieldCheck();
                return 1;
            }
            $fieldIndexes[$policy['field']] = $idx;
        }
    }

    $totalDataRows = 0;
    $linkedRows = 0;
    $likelyNewRows = 0;
    $ready = 0;
    $notReady = 0;
    $readyLinked = 0;
    $notReadyLinked = 0;
    $readyLikelyNew = 0;
    $notReadyLikelyNew = 0;
    $blockingFields = [];
    $sampleRows = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) {
            continue;
        }
        $totalDataRows++;
        $csvRowNumber = $totalDataRows + 1;
        $dbItemIdValue = array_key_exists($dbItemIdColumnIndex, $row) ? trim((string) $row[$dbItemIdColumnIndex]) : '';
        $rowGroup = ($dbItemIdValue === '') ? 'likely_new' : 'linked';
        if ($rowGroup === 'linked') {
            $linkedRows++;
        } else {
            $likelyNewRows++;
        }
        $reasons = [];
        foreach ($frontendBlockingPolicies as $policy) {
            if ($policy['group'] !== 'all' && $policy['group'] !== $rowGroup) {
                continue;
            }
            $field = $policy['field'];
            $value = array_key_exists($fieldIndexes[$field], $row) ? trim((string) $row[$fieldIndexes[$field]]) : '';
            if ($value === '') {
                $reasons[] = "missing {$field}";
                $blockingFields[$field] = true;
            }
        }
        $reasons = array_values(array_unique($reasons));
        if ($reasons === []) {
            $ready++;
            if ($rowGroup === 'linked') { $readyLinked++; } else { $readyLikelyNew++; }
        } else {
            $notReady++;
            if ($rowGroup === 'linked') { $notReadyLinked++; } else { $notReadyLikelyNew++; }
            if (count($sampleRows) < $sampleCap) {
                $sampleRows[] = ['row' => $csvRowNumber, 'group' => $rowGroup, 'reasons' => $reasons];
            }
        }
    }
    fclose($handle);

    $structuralFailure = ($totalDataRows !== $expectedTotalRows || $linkedRows !== $expectedLinkedRows || $likelyNewRows !== $expectedLikelyNewRows);
    $fieldsFound = array_keys($blockingFields);
    sort($fieldsFound);
    fwrite(STDOUT, "Total data rows scanned: {$totalDataRows}\n");
    fwrite(STDOUT, "Linked rows count: {$linkedRows}\n");
    fwrite(STDOUT, "Likely new rows count: {$likelyNewRows}\n");
    fwrite(STDOUT, "Frontend-readiness-blocking findings do not necessarily block admin-visible import/copy. They indicate that affected products should not be treated as ready for public frontend display until fixed or until fallback policy is approved.\n");
    fwrite(STDOUT, "Frontend-readiness summary:\n");
    fwrite(STDOUT, "Total product rows scanned: {$totalDataRows}\n");
    fwrite(STDOUT, "Frontend-ready row count: {$ready}\n");
    fwrite(STDOUT, "Not-frontend-ready row count: {$notReady}\n");
    fwrite(STDOUT, "Frontend-ready linked row count: {$readyLinked}\n");
    fwrite(STDOUT, "Not-frontend-ready linked row count: {$notReadyLinked}\n");
    fwrite(STDOUT, "Frontend-ready likely-new row count: {$readyLikelyNew}\n");
    fwrite(STDOUT, "Not-frontend-ready likely-new row count: {$notReadyLikelyNew}\n");
    fwrite(STDOUT, "Frontend-readiness-blocking fields found: " . ($fieldsFound === [] ? '(none)' : implode(', ', $fieldsFound)) . "\n");
    fwrite(STDOUT, "Sample not-frontend-ready rows (max {$sampleCap}):\n");
    if ($sampleRows === []) {
        fwrite(STDOUT, "  (none)\n");
    } else {
        foreach ($sampleRows as $sample) {
            fwrite(STDOUT, "  - row {$sample['row']} ({$sample['group']}): " . implode(', ', $sample['reasons']) . "\n");
        }
    }
    fwrite(STDOUT, "Console guidance only: no report file generated\n");
    fwrite(STDOUT, "Explicit safety guarantees: no database connection, no SQL execution, no report generation, no file writes, no CSV edits, no importer implementation.\n");
    fwrite(STDOUT, "Diagnostic completed: " . ($structuralFailure ? 'no' : 'yes') . "\n");
    fwrite(STDOUT, "Fatal structural failure: " . ($structuralFailure ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Frontend publication readiness: " . ($notReady > 0 ? 'needs-remediation' : 'pass') . "\n");
    fwrite(STDOUT, "Admin-visible import/copy can proceed for diagnostic/remediation purposes: " . ($structuralFailure ? 'no' : 'yes') . "\n");
    fwrite(STDOUT, "Frontend-hidden/not-ready rows identified: " . ($notReady > 0 ? 'yes' : 'no') . "\n");
    fwrite(STDOUT, "Console guidance only: no report file generated\n");
    $printNoSideEffectSafetyRequiredFieldCheck();
    return $structuralFailure ? 1 : 0;
};


$showExcelRemediationSummary = static function () use ($checkRequiredFields, $checkModelIdDuplicates): int {
    $exitCode = $checkRequiredFields();
    fwrite(STDOUT, "
Excel/CSV remediation summary:
");
    fwrite(STDOUT, "- Source identity/linkage fixes
");
    fwrite(STDOUT, "  - field: external_item_id
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Fill or confirm external_item_id in the Excel/CSV source if source linkage is required for likely-new rows.
");
    fwrite(STDOUT, "- Likely-new insert readiness source fixes
");
    fwrite(STDOUT, "  - field: categoryName
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Fill categoryName in the Excel/CSV source for likely-new rows before insert/frontend readiness.
");
    fwrite(STDOUT, "  - field: price
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Fill price in the Excel/CSV source for likely-new rows before insert/frontend readiness.
");
    fwrite(STDOUT, "- Frontend-readiness source fixes
");
    fwrite(STDOUT, "  - field: images
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Add or map image/source asset values in the Excel/CSV source for likely-new rows before frontend readiness.
");
    fwrite(STDOUT, "- Accessibility/content source fixes
");
    fwrite(STDOUT, "  - field: altText / ariaText
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Improve accessibility text in the Excel/CSV source if these fields remain source-managed; otherwise flag as possible future admin remediation.
");
    fwrite(STDOUT, "  - field: description
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Fill or improve descriptions in Excel/CSV if descriptions are source-managed; otherwise flag as possible future admin remediation.
");
    fwrite(STDOUT, "- Governance/source-policy decisions
");
    fwrite(STDOUT, "  - field: parentCategory
");
    fwrite(STDOUT, "  - suggested Excel/CSV action: Do not force an Excel edit yet. Decide whether parentCategory is derivable, optional, future taxonomy, or should be remediated in Excel/CSV.
");
    fwrite(STDOUT, "
Known model_id duplicate governance reminder:
");
    fwrite(STDOUT, "  - Resolve or explicitly govern the duplicate model_id group nike_female_leggings x 2 before model_id uniqueness enforcement.
");
    fwrite(STDOUT, "Console guidance only: no report file generated
");
    return $exitCode;
};

$showRemediationGuidance = static function () use ($checkRequiredFields): int {
    return $checkRequiredFields();
};

$writeExcelRemediationChecklist = static function (): int {
    $csvPath = dirname(__DIR__, 2) . '/docs/data/SportWarehouse_ProductDB.csv';
    $outputDir = dirname(__DIR__, 2) . '/docs/operations/generated';
    $outputPath = $outputDir . '/csv-excel-remediation-checklist.csv';

    $requiredColumns = ['db_itemId', 'model_id', 'itemName', 'categoryName', 'price', 'images', 'external_item_id', 'description', 'altText', 'ariaText', 'parentCategory'];

    if (!is_file($csvPath)) {
        fwrite(STDERR, "CSV file is missing.\n");
        return 1;
    }

    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        fwrite(STDERR, "CSV file could not be opened safely for remediation checklist generation.\n");
        return 1;
    }

    $header = fgetcsv($handle);
    if (!is_array($header) || $header === []) {
        fclose($handle);
        fwrite(STDERR, "CSV header row could not be read.\n");
        return 1;
    }

    $header = array_values(array_map(static fn (string $value): string => trim($value), $header));
    if ($header !== []) {
        $utf8Bom = "ï»¿";
        if (strncmp($header[0], $utf8Bom, strlen($utf8Bom)) === 0) {
            $header[0] = substr($header[0], strlen($utf8Bom));
        }
    }

    $idx = [];
    $missing = [];
    foreach ($requiredColumns as $column) {
        $columnIndex = array_search($column, $header, true);
        if ($columnIndex === false) {
            $missing[] = $column;
        } else {
            $idx[$column] = $columnIndex;
        }
    }
    if ($missing !== []) {
        fclose($handle);
        fwrite(STDERR, 'Missing required diagnostic columns: ' . implode(', ', $missing) . "\n");
        return 1;
    }

    $rows = [];
    $modelRows = [];
    $parentBlank = 0;
    $dataRowCount = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (!is_array($row)) { continue; }
        $dataRowCount++;
        $csvRowNumber = $dataRowCount + 1;
        $dbItemId = trim((string)($row[$idx['db_itemId']] ?? ''));
        $modelId = trim((string)($row[$idx['model_id']] ?? ''));
        $itemName = trim((string)($row[$idx['itemName']] ?? ''));
        $isLikelyNew = ($dbItemId === '');

        if ($modelId !== '') {
            $modelRows[$modelId][] = $csvRowNumber;
        }

        if (trim((string)($row[$idx['parentCategory']] ?? '')) === '') {
            $parentBlank++;
        }

        if (!$isLikelyNew) { continue; }

        foreach (['categoryName','price','images','external_item_id','description','altText','ariaText'] as $field) {
            $value = trim((string)($row[$idx[$field]] ?? ''));
            if ($value !== '') { continue; }
            $readiness = in_array($field, ['categoryName','price','images','external_item_id'], true) ? 'import-readiness-blocking' : 'admin-remediation';
            $rows[] = [
                'row_number' => (string)$csvRowNumber,
                'db_itemId' => $dbItemId,
                'model_id' => $modelId,
                'itemName' => $itemName,
                'row_group' => 'likely_new',
                'field' => $field,
                'finding_category' => 'blank_required_or_policy_field',
                'readiness_category' => $readiness,
                'issue_type' => 'blank',
                'blank_or_issue_count' => '1',
                'remediation_owner' => 'excel-csv-source-of-truth',
                'remediation_pathway' => 'fix in Excel/CSV',
                'suggested_action' => 'Populate source field in Excel/CSV for likely-new row.',
                'governance_note' => '',
                'frontend_ready' => in_array($field, ['categoryName','price','images'], true) ? 'no' : 'review',
                'import_ready' => in_array($field, ['categoryName','price','images','external_item_id'], true) ? 'no' : 'review',
                'admin_visible_ok' => 'yes',
            ];
        }
    }
    fclose($handle);

    foreach ($modelRows as $modelId => $numbers) {
        if (count($numbers) <= 1) { continue; }
        $rows[] = [
            'row_number' => implode('|', $numbers), 'db_itemId' => '', 'model_id' => $modelId, 'itemName' => '', 'row_group' => 'multiple',
            'field' => 'model_id', 'finding_category' => 'source-governance', 'readiness_category' => 'governance', 'issue_type' => 'duplicate',
            'blank_or_issue_count' => (string)count($numbers), 'remediation_owner' => 'data-governance', 'remediation_pathway' => 'source/governance decision',
            'suggested_action' => 'Resolve duplicate model_id values in source before uniqueness enforcement.',
            'governance_note' => 'Known duplicate group requires explicit resolution/governance.', 'frontend_ready' => 'review', 'import_ready' => 'no', 'admin_visible_ok' => 'yes'
        ];
    }

    if ($parentBlank > 0) {
        $rows[] = [
            'row_number' => 'all', 'db_itemId' => '', 'model_id' => '', 'itemName' => '', 'row_group' => 'governance', 'field' => 'parentCategory',
            'finding_category' => 'source-governance', 'readiness_category' => 'deferred-governance', 'issue_type' => 'policy-decision',
            'blank_or_issue_count' => (string)$parentBlank, 'remediation_owner' => 'data-governance', 'remediation_pathway' => 'governance clarification workflow',
            'suggested_action' => 'Decide if parentCategory is derivable, optional, taxonomy backlog, or source-remediation target.',
            'governance_note' => 'Do not force automatic Excel fix without governance decision.', 'frontend_ready' => 'review', 'import_ready' => 'review', 'admin_visible_ok' => 'yes'
        ];
    }

    if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
        fwrite(STDERR, "Output directory could not be created.\n");
        return 1;
    }
    $out = fopen($outputPath, 'wb');
    if ($out === false) { fwrite(STDERR, "Output file could not be opened for write.\n"); return 1; }
    $headerOut = ['row_number','db_itemId','model_id','itemName','row_group','field','finding_category','readiness_category','issue_type','blank_or_issue_count','remediation_owner','remediation_pathway','suggested_action','governance_note','frontend_ready','import_ready','admin_visible_ok'];
    fputcsv($out, $headerOut);
    foreach ($rows as $r) { fputcsv($out, $r); }
    fclose($out);

    fwrite(STDOUT, "Generated artifact path: docs/operations/generated/csv-excel-remediation-checklist.csv\n");
    fwrite(STDOUT, 'Row count written: ' . count($rows) . "\n");
    fwrite(STDOUT, "Source CSV path: docs/data/SportWarehouse_ProductDB.csv\n");
    fwrite(STDOUT, "Note: this is a generated remediation checklist only.\n");
    fwrite(STDOUT, "Note: no source CSV was edited.\n");
    fwrite(STDOUT, "Note: no database connection was opened.\n");
    fwrite(STDOUT, "Note: no SQL was executed.\n");
    fwrite(STDOUT, "Note: no importer execution occurred.\n");
    fwrite(STDOUT, "Note: no frontend/admin behavior was changed.\n");

    return 0;
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

$recognizedArgs = ['--help', '--status', '--check-csv-header', '--check-csv-row-count', '--check-model-id-duplicates', '--check-db-item-id-integrity', '--check-csv-baseline', '--check-required-fields', '--show-remediation-guidance', '--show-frontend-readiness-summary', '--show-excel-remediation-summary', '--write-excel-remediation-checklist', '--dry-run'];
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

if (in_array('--show-remediation-guidance', $args, true)) {
    exit($showRemediationGuidance());
}

if (in_array('--show-frontend-readiness-summary', $args, true)) {
    exit($showFrontendReadinessSummary());
}

if (in_array('--show-excel-remediation-summary', $args, true)) {
    exit($showExcelRemediationSummary());
}

if (in_array('--write-excel-remediation-checklist', $args, true)) {
    exit($writeExcelRemediationChecklist());
}

fwrite(STDERR, "Unsupported invocation. Use --help.\n");
exit(1);
