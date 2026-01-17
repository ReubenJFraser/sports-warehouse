<?php
// /admin/inc/environment-status.php

if (!defined('ADMIN_CONTEXT')) {
    return;
}

/**
 * Read-only environment status for admin.
 * Requires: $pdo (PDO)
 */
function sw_environment_status(PDO $pdo): array
{
    $host = $_SERVER['HTTP_HOST'] ?? '(unknown)';

    $isLocal =
        str_contains($host, 'localhost') ||
        str_contains($host, '127.0.0.1') ||
        str_contains($host, '.test');

    $environment = $isLocal ? 'LOCAL' : 'PRODUCTION';

    $dbConnected = false;
    try {
        $stmt = $pdo->query('SELECT 1');
        $dbConnected = ($stmt !== false);
    } catch (Throwable $e) {
        $dbConnected = false;
    }

    return [
        'environment'  => $environment,
        'host'         => $host,
        'php_version'  => PHP_VERSION,
        'db_connected' => $dbConnected,
        'checked_at'   => date('Y-m-d H:i:s'),
    ];
}
