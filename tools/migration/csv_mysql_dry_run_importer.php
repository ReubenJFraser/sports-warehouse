<?php

declare(strict_types=1);

/**
 * CSV-to-MySQL dry-run importer placeholder (non-executing skeleton only).
 *
 * This file is intentionally a non-executing placeholder.
 * Future dry-run importer scope is defined by planning/governance documents in:
 * README/V-AUDIT/POST-AUDIT/
 *
 * No importer implementation is approved yet.
 * No database writes are approved.
 * No CSV reads are performed by this skeleton.
 * No report generation is performed by this skeleton.
 * Protected fields must never be written.
 * Deferred governance fields remain excluded.
 * Public/admin invocation is not supported.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This placeholder supports CLI invocation only.\n");
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

if ($detectedWriteLikeFlags !== []) {
    fwrite(STDERR, "Write/execution flags are not supported by this skeleton: " . implode(', ', $detectedWriteLikeFlags) . "\n");
}

fwrite(STDOUT, "CSV-to-MySQL dry-run importer skeleton exists, but implementation is not yet approved.\n");
fwrite(STDOUT, "No CSV was read.\n");
fwrite(STDOUT, "No database connection was opened.\n");
fwrite(STDOUT, "No SQL was executed.\n");
fwrite(STDOUT, "No reports were generated.\n");

exit(1);
