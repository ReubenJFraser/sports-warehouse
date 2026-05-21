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
    fwrite(STDOUT, "  - no CSV read (except first/header row when --check-csv-header is used)\n");
    fwrite(STDOUT, "  - no DB connection\n");
    fwrite(STDOUT, "  - no SQL execution\n");
    fwrite(STDOUT, "  - no report generation\n");
    fwrite(STDOUT, "  - no file writes\n");
};

$printStatus = static function (): void {
    fwrite(STDOUT, "Skeleton status:\n");
    fwrite(STDOUT, "- skeleton exists\n");
    fwrite(STDOUT, "- implementation not approved\n");
    fwrite(STDOUT, "- CSV header check implemented: yes\n");
    fwrite(STDOUT, "- Full CSV row reading implemented: no\n");
    fwrite(STDOUT, "- Database connection implemented: no\n");
    fwrite(STDOUT, "- Report generation implemented: no\n");
    fwrite(STDOUT, "- database connection not implemented\n");
    fwrite(STDOUT, "- SQL execution not implemented\n");
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

$printNoSideEffectSafety = static function (): void {
    fwrite(STDOUT, "No CSV was read.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
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

$recognizedArgs = ['--help', '--status', '--check-csv-header', '--dry-run'];
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

fwrite(STDERR, "Unsupported invocation. Use --help.\n");
exit(1);
