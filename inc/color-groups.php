<?php
// inc/color-groups.php
// Canonical grouping for the 16-color palette
function sw_color_groups(): array {
  return [
    'dark'  => ['black','grey'],
    'light' => ['white','beige','brown'],
    'color' => ['red','orange','yellow','green','teal','blue','navy','purple','pink'],
  ];
}

function sw_group_label(string $group): string {
  return [
    'dark'  => 'Dark Neutrals',
    'light' => 'Light Neutrals',
    'color' => 'Colors',
  ][$group] ?? ucfirst($group);
}

/** Merge selected groups + specific swatches into an effective color set */
function sw_effective_colors(array $groups, array $swatches): array {
  $groups = array_values(array_intersect(array_map('strtolower',$groups), array_keys(sw_color_groups())));
  $groupColors = [];
  foreach ($groups as $g) $groupColors = array_merge($groupColors, sw_color_groups()[$g]);
  $all = array_map('strtolower', array_merge($groupColors, $swatches));
  return array_values(array_unique($all));
}





