<?php
header('Content-Type: text/plain');

echo "=== ENV ===\n";
foreach ($_ENV as $k => $v) {
    if (stripos($k, 'db') !== false || stripos($k, 'mysql') !== false) {
        echo "$k=$v\n";
    }
}

echo "\n=== SERVER ===\n";
foreach ($_SERVER as $k => $v) {
    if (stripos($k, 'db') !== false || stripos($k, 'mysql') !== false) {
        echo "$k=$v\n";
    }
}

echo "\n=== getenv() ===\n";
foreach (['DB_HOST','DB_NAME','DB_USER','DB_USERNAME','DB_PASSWORD','MYSQL_HOST','MYSQL_DATABASE','MYSQL_USER','MYSQL_PASSWORD'] as $k) {
    $v = getenv($k);
    if ($v !== false) {
        echo "$k=$v\n";
    }
}



