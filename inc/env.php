<?php
// inc/env.php — cross-platform environment loader

if (!function_exists('sw_env')) {
  function sw_env(string $key, $default = null) {
    static $loaded = false, $vars = [];

    // 1) Real environment always wins
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    if (isset($_ENV[$key]))    return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];

    // 2) Load local .env (Laragon, GitHub dev, manual config)
    $localEnvFile = __DIR__ . '/../.env';
    if (!$loaded && is_readable($localEnvFile)) {
      foreach (file($localEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $val] = array_map('trim', explode('=', $line, 2));
        $val = trim($val, " \t\"'");
        $vars[$k] = $val;

        putenv("$k=$val");
        $_ENV[$k] = $_SERVER[$k] = $val;
      }
      $loaded = true;
    }

    // 3) Cloudways environment file (/home/xxxxxx/env)
    if (!$loaded) {
      $home = $_SERVER['HOME'] ?? null;
      if ($home) {
        $cloudwaysFile = $home . '/env';
        if (is_readable($cloudwaysFile)) {
          foreach (file($cloudwaysFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
            [$k, $val] = array_map('trim', explode('=', $line, 2));
            $val = trim($val, " \t\"'");
            $vars[$k] = $val;

            putenv("$k=$val");
            $_ENV[$k] = $_SERVER[$k] = $val;
          }
        }
      }
      $loaded = true;
    }

    // 4) Return value or default
    return $vars[$key] ?? $default;
  }
}



