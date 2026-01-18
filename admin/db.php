<?php
// admin/db.php
// Admin database bootstrap — connection only.

require_once __DIR__ . '/../db.php';

// Expect $pdo to be defined by root db.php
if (!isset($pdo) || !($pdo instanceof PDO)) {
    throw new RuntimeException('Admin DB bootstrap failed: $pdo not available.');
}

