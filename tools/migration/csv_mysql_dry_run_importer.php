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
 * No CSV reads are performed by this skeleton.
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
    fwrite(STDOUT, "  - no CSV read\n");
    fwrite(STDOUT, "  - no DB connection\n");
    fwrite(STDOUT, "  - no SQL execution\n");
    fwrite(STDOUT, "  - no report generation\n");
    fwrite(STDOUT, "  - no file writes\n");
};

$printStatus = static function (): void {
    fwrite(STDOUT, "Skeleton status:\n");
    fwrite(STDOUT, "- skeleton exists\n");
    fwrite(STDOUT, "- implementation not approved\n");
    fwrite(STDOUT, "- CSV reading not implemented\n");
    fwrite(STDOUT, "- database connection not implemented\n");
    fwrite(STDOUT, "- SQL execution not implemented\n");
    fwrite(STDOUT, "- report generation not implemented\n");
    fwrite(STDOUT, "- protected fields remain excluded by governance\n");
    fwrite(STDOUT, "- deferred governance fields remain excluded\n");
    fwrite(STDOUT, "- write/execution flags remain unsupported\n");
};

if ($detectedWriteLikeFlags !== []) {
    fwrite(STDERR, "Write/execution flags are not supported by this skeleton: " . implode(', ', $detectedWriteLikeFlags) . "\n");
    exit(1);
}

if ($args === []) {
    fwrite(STDERR, "Importer skeleton only; implementation is not approved yet. Use --help or --status.\n");
    exit(1);
}

$recognizedArgs = ['--help', '--status', '--dry-run'];
$unknownArgs = array_values(array_diff($args, $recognizedArgs));

if ($unknownArgs !== []) {
    fwrite(STDERR, "Unsupported option(s): " . implode(', ', $unknownArgs) . ". Use --help.\n");
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
    fwrite(STDOUT, "No CSV was read.\n");
    fwrite(STDOUT, "No database connection was opened.\n");
    fwrite(STDOUT, "No SQL was executed.\n");
    fwrite(STDOUT, "No reports were generated.\n");
    fwrite(STDOUT, "No files were written.\n");
    exit(1);
}

fwrite(STDERR, "Unsupported invocation. Use --help.\n");
exit(1);
