<?php
/**
 * Unified Database Loader — Sports Warehouse
 *
 * CONTRACT:
 * - This file MUST be identical on local and production.
 * - Production secrets live OUTSIDE Git in private_html/db.production.php
 * - Local dev uses inc/env.php (.env or real env vars)
 * - No framework assumptions (plain PHP + PDO)
 * - Debug output must be opt-in and explicit
 */

// --------------------------------------------------
// 1) Detect Cloudways production config (preferred)
// --------------------------------------------------

$prodConfig = dirname(__DIR__) . '/private_html/db.production.php';

if (is_readable($prodConfig)) {
    // ---- Cloudways / production path ----
    $cfg = require $prodConfig;

    $DB_HOST = $cfg['host'];
    $DB_PORT = (int) ($cfg['port'] ?? 3306);
    $DB_NAME = $cfg['dbname'];
    $DB_USER = $cfg['user'];
    $DB_PASS = $cfg['password'];
    $DB_CHAR = $cfg['charset'] ?? 'utf8mb4';

} else {
    // --------------------------------------------------
    // 2) Local development fallback (Laragon, etc.)
    // --------------------------------------------------
    require_once __DIR__ . '/inc/env.php';

    $DB_HOST = sw_env('DB_HOST', '127.0.0.1');
    $DB_PORT = (int) sw_env('DB_PORT', '3306');
    $DB_NAME = sw_env('DB_NAME', 'sportswh');
    $DB_USER = sw_env('DB_USER', 'root');
    $DB_PASS = sw_env('DB_PASS', sw_env('DB_PASSWORD', ''));
    $DB_CHAR = sw_env('DB_CHARSET', 'utf8mb4');
}

// --------------------------------------------------
// 3) Create PDO connection
// --------------------------------------------------

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $DB_HOST,
    $DB_PORT,
    $DB_NAME,
    $DB_CHAR
);

try {
    $pdo = new PDO(
        $dsn,
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]
    );
} catch (Throwable $e) {

    // --------------------------------------------------
    // 4) Explicit, opt-in debug output only
    // --------------------------------------------------

    $debugEnabled =
        (!empty($_GET['sw_debug']) && $_GET['sw_debug'] !== '0') ||
        (function_exists('sw_env') && sw_env('SW_DEBUG', '0') === '1');

    if ($debugEnabled) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "DATABASE CONNECTION FAILED\n\n";
        echo "Host: {$DB_HOST}\n";
        echo "Port: {$DB_PORT}\n";
        echo "DB:   {$DB_NAME}\n";
        echo "User: {$DB_USER}\n\n";
        echo "Error:\n" . $e->getMessage();
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Database connection failed.';
    }

    exit;
}









