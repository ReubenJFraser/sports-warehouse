<?php
header('Content-Type: text/plain; charset=utf-8');

echo "php_sapi_name():\n";
var_dump(php_sapi_name());

echo "\nopen_basedir:\n";
var_dump(ini_get('open_basedir'));

echo "\nHOME via getenv():\n";
var_dump(getenv('HOME'));

echo "\nHOME via \$_SERVER:\n";
var_dump($_SERVER['HOME'] ?? null);

echo "\nReadable /home/master/env:\n";
var_dump(is_readable('/home/master/env'));

echo "\nReadable app-local env (public_html/env):\n";
var_dump(is_readable(__DIR__ . '/../../env'));

echo "\npdo_mysql loaded:\n";
var_dump(extension_loaded('pdo_mysql'));


