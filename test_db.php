<?php
require 'db.php';

try {
    $stmt = $pdo->query('SELECT DATABASE() AS dbname');
    $row = $stmt->fetch();
    echo 'Connected to database: ' . htmlspecialchars($row['dbname']);
} catch (Exception $e) {
    echo 'Query failed: ' . htmlspecialchars($e->getMessage());
}
