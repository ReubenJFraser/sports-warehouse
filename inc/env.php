<?php
// inc/env.php — simplified loader for Cloudways

if (!function_exists('sw_env')) {
  function sw_env(string $key, $default = null) {
    static $loaded = false, $vars = [];

    // 1) Real environment always wins
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    if (isset($_ENV[$key]))    return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];

    // 2) Load ~/env file once (Cloudways convention)
    if (!$loaded) {
      $file = $_SERVER['HOME'] . '/env';
      if (is_readable($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
          if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
          [$k, $val] = array_map('trim', explode('=', $line, 2));
          $val = trim($val, " \t\"'");
          $vars[$k] = $val;

          // Also expose to environment
          putenv("$k=$val");
          $_ENV[$k] = $_SERVER[$k] = $val;
        }
      }
      $loaded = true;
    }

    // 3) Return from cache or default
    return $vars[$key] ?? $default;
  }
}


