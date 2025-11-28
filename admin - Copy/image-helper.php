<?php
/**
 * image-helper.php
 * --------------------------------------------------------------------
 * Admin-only helper functions for rendering images consistently.
 *
 * Features:
 *  - Escapes paths and alt text safely
 *  - Ensures consistency of "/images/…" prefix
 *  - Adds PhotoSwipe data attributes for fullscreen viewing
 *  - Provides a standard thumbnail renderer
 *  - Provides a SAFE thumbnail renderer with placeholder fallback
 *  - Resolves real filesystem paths (for debugging missing images)
 * --------------------------------------------------------------------
 */

/**
 * Normalizes a public URL:
 *   - Ensures no leading slashes
 *   - Ensures a single leading "/"
 */
if (!function_exists('admin_normalize_image_url')) {
    function admin_normalize_image_url(string $path): string
    {
        $p = trim($path);

        // remove leading slashes
        while (str_starts_with($p, '/')) {
            $p = substr($p, 1);
        }

        return '/' . $p;
    }
}

/**
 * Resolve absolute filesystem path
 */
if (!function_exists('admin_image_fs_path')) {
    function admin_image_fs_path(string $url): string
    {
        $root = dirname(__DIR__); // project root
        $clean = ltrim($url, '/');
        return $root . '/' . $clean;
    }
}

/**
 * Check file existence on disk
 */
if (!function_exists('admin_image_exists')) {
    function admin_image_exists(string $path): bool
    {
        $p = admin_image_fs_path($path);
        return is_file($p) && is_readable($p);
    }
}

/**
 * Attempt to read width/height from real image file
 * Falls back to 800×1000 (portrait 4:5)
 */
if (!function_exists('admin_image_dimensions')) {
    function admin_image_dimensions(string $path): array
    {
        $fs = admin_image_fs_path($path);

        if (is_file($fs)) {
            $info = @getimagesize($fs);
            if ($info && isset($info[0], $info[1])) {
                return [$info[0], $info[1]];
            }
        }

        // Fallback
        return [800, 1000];
    }
}

/**
 * Renders a PhotoSwipe-enabled image
 * (replaces old data-fullscreen)
 */
if (!function_exists('admin_render_image')) {
    function admin_render_image(string $path, string $alt = '', string $extra = ''): string
    {
        $url = admin_normalize_image_url($path);
        [$w, $h] = admin_image_dimensions($path);

        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

        return "
            <img 
                src=\"{$escapedUrl}\"
                alt=\"{$escapedAlt}\"
                data-pswp-src=\"{$escapedUrl}\"
                data-pswp-width=\"{$w}\"
                data-pswp-height=\"{$h}\"
                {$extra}
            >
        ";
    }
}

/**
 * Thumbnail renderer with optional extra attributes
 */
if (!function_exists('admin_render_thumbnail')) {
    function admin_render_thumbnail(string $path, string $alt = '', array $attrs = []): string
    {
        $url = admin_normalize_image_url($path);
        [$w, $h] = admin_image_dimensions($path);

        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

        // Start with default class
        $attrString = 'class="admin-thumb"';

        // Add any provided attributes
        foreach ($attrs as $key => $value) {
            $k = htmlspecialchars($key, ENT_QUOTES);
            $v = htmlspecialchars($value, ENT_QUOTES);
            $attrString .= " {$k}=\"{$v}\"";
        }

        return "
            <img 
                {$attrString}
                src=\"{$escapedUrl}\"
                alt=\"{$escapedAlt}\"
                data-pswp-src=\"{$escapedUrl}\"
                data-pswp-width=\"{$w}\"
                data-pswp-height=\"{$h}\"
            >
        ";
    }
}

/**
 * SAFE thumbnail renderer with placeholder fallback
 */
if (!function_exists('admin_render_thumbnail_safe')) {
    function admin_render_thumbnail_safe(string $path, string $alt = '', array $attrs = []): string
    {
        if (!admin_image_exists($path)) {
            $path = 'images/placeholders/missing_admin.png';
        }

        return admin_render_thumbnail($path, $alt, $attrs);
    }
}






