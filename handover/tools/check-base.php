<?php
require_once __DIR__ . '/../inc/url.php';
header('Content-Type: text/plain');
echo "sw_base() = " . sw_base() . PHP_EOL;
echo "APP_BASE  = " . (sw_env('APP_BASE', '(default /)')) . PHP_EOL;
echo "css url   = " . sw_url('css/main.css') . PHP_EOL;

