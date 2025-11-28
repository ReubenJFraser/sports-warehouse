<?php
require_once __DIR__ . '/inc/env.php';

$DB_HOST = sw_env('DB_HOST', '127.0.0.1');
$DB_NAME = sw_env('DB_NAME', 'sportswh');
$DB_USER = sw_env('DB_USER', 'root');
$DB_PASS = sw_env('DB_PASS', '');
$DB_CHAR = sw_env('DB_CHARSET', 'utf8mb4');
$DB_PORT = (int) sw_env('DB_PORT', '3306');

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset={$DB_CHAR}";

try {
  // NOTE:
  // - ERRMODE: throw exceptions for easier diagnosis
  // - DEFAULT_FETCH_MODE: assoc arrays
  // - EMULATE_PREPARES: true so placeholders in LIMIT/OFFSET are accepted;
  //   we still bind ints (PDO::PARAM_INT) in sw_execute_with_params.
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,   // <-- flipped from false to true
  ]);
} catch (Throwable $e) {
  // Be explicit about failure for HTTP context
  header('HTTP/1.1 500 Internal Server Error');
  echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
  exit;
}

return $pdo;




