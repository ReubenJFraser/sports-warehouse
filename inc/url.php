<?php
// inc/url.php
require_once __DIR__ . '/env.php';

/**
 * sw_base()
 * Returns the base URL or base path for asset resolution.
 * Works correctly on Laragon, Cloudways, custom domains, and any shared hosting.
 */
if (!function_exists('sw_base')) {
  function sw_base(): string {

    // 1. Load from .env if present
    $envBase = sw_env('APP_BASE');
    if (!empty($envBase)) {
      return rtrim($envBase, '/') . '/';
    }

    // 2. Load APP_URL if defined (Cloudways or production)
    $envUrl = sw_env('APP_URL');
    if (!empty($envUrl)) {
      return rtrim($envUrl, '/') . '/';
    }

    // 3. Cloudways automatic detection
    if (isset($_SERVER['HTTP_HOST'])) {
      return 'https://' . $_SERVER['HTTP_HOST'] . '/';
    }

    // 4. Local fallback detection via script name
    if (isset($_SERVER['SCRIPT_NAME'])) {
      $path = dirname($_SERVER['SCRIPT_NAME']);
      if ($path !== '/' && $path !== '\\') {
        return rtrim($path, '/') . '/';
      }
    }

    // 5. Absolute fallback
    return '/';
  }
}

if (!function_exists('sw_url')) {
  function sw_url(string $p): string {
    return sw_base() . ltrim($p, '/');
  }
}


