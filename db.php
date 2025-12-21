<?php
/**
 * Unified Database Loader
 * Uses environment variables for BOTH local and production.
 */

require_once __DIR__ . '/inc/env.php';

// Load DB settings from environment
$DB_HOST = sw_env('DB_HOST', '127.0.0.1');
$DB_NAME = sw_env('DB_NAME', 'sportswh');
$DB_USER = sw_env('DB_USER', 'root');
$DB_PASS = sw_env('DB_PASS', sw_env('DB_PASSWORD', ''));
$DB_CHAR = sw_env('DB_CHARSET', 'utf8mb4');
$DB_PORT = (int) sw_env('DB_PORT', '3306');

// Prepare DSN
$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset={$DB_CHAR}";

try {
    // Create PDO
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ]);

} catch (Throwable $e) {

    // Emit clear diagnostic message when debugging is enabled
    if (sw_env('SW_DEBUG', '0') == '1') {
        header('Content-Type: text/plain; charset=utf-8');
        echo "DATABASE CONNECTION FAILED\n";
        echo "Host: $DB_HOST\n";
        echo "DB:   $DB_NAME\n";
        echo "User: $DB_USER\n";
        echo "Port: $DB_PORT\n";
        echo "\nError message:\n" . $e->getMessage();
    } else {
        // Generic message in production
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Database connection failed.';
    }

    exit;
}

// Do NOT return — let $pdo exist in scope





