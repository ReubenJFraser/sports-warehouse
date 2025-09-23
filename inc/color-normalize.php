<?php
/**
 * Map free-form or marketing names to our 16 canonical palette slugs.
 * Keep keys lowercase; values must be one of config['palette'].
 */
function sw_color_normalize(string $name): string {
  static $map = [
    // greys/whites/blacks
    'charcoal' => 'grey', 'graphite' => 'grey', 'slate' => 'grey',
    'ivory' => 'white', 'offwhite' => 'white', 'cream' => 'beige',
    // blues/greens
    'sky' => 'blue', 'royal' => 'blue', 'cobalt' => 'blue',
    'aqua' => 'teal', 'turquoise' => 'teal', 'mint' => 'green',
    'forest' => 'green', 'khaki' => 'olive',
    // warm tones
    'gold' => 'yellow', 'mustard' => 'yellow',
    'burgundy' => 'red', 'maroon' => 'red',
    'coral' => 'orange', 'tan' => 'beige', 'sand' => 'beige',
    // pinks/purples
    'fuchsia' => 'pink', 'magenta' => 'pink', 'lilac' => 'purple', 'violet' => 'purple',
  ];
  $k = strtolower(preg_replace('/[^a-z]/','', $name));
  return $map[$k] ?? $k; // if already canonical, pass through
}

/**
 * Clamp an arbitrary color slug to the nearest valid palette entry.
 * Use this for last-resort safety after sw_color_normalize().
 */
function sw_color_clamp(string $slug, array $palette): string {
  $slug = strtolower($slug);
  return in_array($slug, $palette, true) ? $slug : 'grey'; // default bucket
}



