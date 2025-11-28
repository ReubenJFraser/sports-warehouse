<?php
// dbtest.php — quick MySQL connectivity check via PDO

$host = 'db';            // service name from docker-compose.yml
$port = 3306;            // internal MySQL port (not 3307)
$db   = 'sportswh';      // same as MYSQL_DATABASE in docker-compose.yml
$user = 'root';
$pass = 'password';      // same as MYSQL_ROOT_PASSWORD

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>✅ Database connection successful!</h1>";

    // Query the sample_products table
    $stmt = $pdo->query("SELECT id, name, price, created_at FROM sample_products");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        echo "<h2>Sample Products</h2>";
        echo "<ul>";
        foreach ($rows as $row) {
            echo "<li>";
            echo htmlspecialchars($row['id']) . ": ";
            echo htmlspecialchars($row['name']) . " - $";
            echo htmlspecialchars($row['price']) . " (created " . $row['created_at'] . ")";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No products found in sample_products table.</p>";
    }

} catch (PDOException $e) {
    echo "<h1>❌ Database connection failed</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}


