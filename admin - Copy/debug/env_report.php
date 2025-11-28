<?php
header('Content-Type: text/plain');

$env = __DIR__ . '/../../.env';

echo "Active .env file: $env\n\n";

if (!file_exists($env)) {
    echo "No .env found.\n";
    exit;
}

foreach (file($env) as $line) {
    if (preg_match('/^(APP_|DB_|ENV_)/', $line)) {
        echo $line;
    }
}
