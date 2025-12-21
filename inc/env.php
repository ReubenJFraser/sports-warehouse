<?php
// inc/env.php — cross-platform environment loader (CLI + Cloudways PHP-FPM safe)

if (!function_exists('sw_env')) {
  function sw_env(string $key, $default = null) {
    static $loaded = false;
    static $vars = [];

    /**
     * 0) Cloudways PHP-FPM injected variables
     * These exist for WEB requests but NOT CLI.
     */
    $cloudwaysMap = [
      'DB_HOST' => 'DB_HOST',
      'DB_NAME' => 'DB_DATABASE',
      'DB_USER' => 'DB_USERNAME',
      'DB_PASS' => 'DB_PASSWORD',
    ];

    if (isset($cloudwaysMap[$key])) {
      $cw = getenv($cloudwaysMap[$key]);
      if ($cw !== false && $cw !== '') {
        return $cw;
      }
    }

    /**
     * 1) Real environment always wins
     */
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    if (isset($_ENV[$key]))    return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];

    /**
     * Load env files only once
     */
    if (!$loaded) {

      /**
       * 2) Local .env (Laragon / dev)
       */
      $localEnvFile = __DIR__ . '/../.env';
      if (is_readable($localEnvFile)) {
        foreach (file($localEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
          if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
          [$k, $val] = array_map('trim', explode('=', $line, 2));
          $val = trim($val, " \t\"'");
          $vars[$k] = $val;
          putenv("$k=$val");
          $_ENV[$k] = $_SERVER[$k] = $val;
        }
      }

      /**
       * 3) Cloudways CLI env file (~/env)
       * Not available to PHP-FPM, CLI only
       */
      $home = $_SERVER['HOME'] ?? getenv('HOME');
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

    /**
     * 4) Return loaded value or default
     */
    return $vars[$key] ?? $default;
  }
}



