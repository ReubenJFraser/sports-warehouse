<?php
// db.php

// 1) Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'sportswh');
define('DB_USER', 'root');
define('DB_PASS', '');                 // empty string because root has no password

try {
    // 2) Create a PDO instance
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // If connection fails, show a user‐friendly message and exit
    echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
    exit;
}

