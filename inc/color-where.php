<?php
// inc/color-where.php
// Shared WHERE builder for color filters.
// Returns [SQL fragment string (without leading AND), params array].
//
// $match: 'any' | 'all'
// $effectiveColors: array of canonical color slugs
// $multiOnly: bool — only show multi-color items (where secondary is set and differs)
// $alias: table alias for the items table (default 'i'), which must expose
//         color_primary/color_secondary columns.

function sw_where_colors(string $match, array $effectiveColors, bool $multiOnly = false, string $alias = 'i'): array {
  $colors = array_values(array_unique(array_map('strtolower', $effectiveColors)));
  $colors = array_filter($colors, fn($c) => $c !== '');

  if (empty($colors)) {
    return ['', []];
  }

  $cp = $alias . '.color_primary';
  $cs = $alias . '.color_secondary';

  $params = [];
  $expr   = '';

  if ($match === 'all') {
    // ---- ALL mode ----
    if (count($colors) === 1) {
      // Use two *distinct* placeholders even for one color to avoid reuse
      $p1 = ':all1p'; $p2 = ':all1s';
      $params[$p1] = $colors[0];
      $params[$p2] = $colors[0];
      $expr = "({$cp} = {$p1} OR {$cs} = {$p2})";
    } elseif (count($colors) === 2) {
      // (cp=a AND cs=b) OR (cp=b AND cs=a) — all placeholders unique
      $a_cp = ':all2_a_cp'; $a_cs = ':all2_a_cs';
      $b_cp = ':all2_b_cp'; $b_cs = ':all2_b_cs';
      $params[$a_cp] = $colors[0];
      $params[$a_cs] = $colors[1];
      $params[$b_cp] = $colors[1];
      $params[$b_cs] = $colors[0];
      $expr = "(({$cp} = {$a_cp} AND {$cs} = {$a_cs}) OR ({$cp} = {$b_cp} AND {$cs} = {$b_cs}))";
    } else {
      // Fallback with current schema (2 columns only):
      // require both columns to be in the set and differ. Use IN lists with *unique* placeholders.
      $plist = []; $slist = [];
      foreach ($colors as $i => $c) {
        $pp = ":allm_p_" . $i;
        $sp = ":allm_s_" . $i;
        $params[$pp] = $c;
        $params[$sp] = $c;
        $plist[] = $pp;
        $slist[] = $sp;
      }
      $expr = "({$cp} IN (" . implode(',', $plist) . ") AND {$cs} IN (" . implode(',', $slist) . ") AND {$cp} <> {$cs})";
    }
  } else {
    // ---- ANY mode ----
    // Build separate primary/secondary IN lists with *distinct* placeholders
    $plist = []; $slist = [];
    foreach ($colors as $i => $c) {
      $pp = ":any_p_" . $i; // primary placeholder
      $sp = ":any_s_" . $i; // secondary placeholder
      $params[$pp] = $c;
      $params[$sp] = $c;
      $plist[] = $pp;
      $slist[] = $sp;
    }
    $expr = "({$cp} IN (" . implode(',', $plist) . ") OR {$cs} IN (" . implode(',', $slist) . "))";
  }

  // Multi-only clause if requested
  if ($multiOnly) {
    $expr = "({$expr}) AND ({$cs} IS NOT NULL AND {$cs} <> '' AND {$cs} <> {$cp})";
  }

  return [$expr, $params];
}




