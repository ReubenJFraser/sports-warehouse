<?php
// inc/url.php
require_once __DIR__ . '/env.php';

if (!function_exists('sw_base')) {
  function sw_base(): string {
    $b = sw_env('APP_BASE','/');   // "/" on Hostinger, "/sports-warehouse-home-page" locally
    if ($b === '') $b = '/';
    return rtrim($b,'/') . '/';
  }
}
if (!function_exists('sw_url')) {
  function sw_url(string $p): string { return sw_base() . ltrim($p,'/'); }
}
