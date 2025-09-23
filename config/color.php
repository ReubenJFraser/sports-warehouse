<?php
// Canonical palette + thresholds used by scripts/color-tag-images.php and UI
return [
  'palette' => [
    'black','white','grey','navy','blue','teal','green','olive',
    'yellow','orange','red','pink','purple','brown','beige',
  ],
  'alpha_cutoff' => 96,  // ignore pixels below this opacity
  'p1_min'       => 65,  // primary % threshold for single-color
  'p2_min'       => 25,  // secondary % threshold for multi-color
  'downscale'    => 64,  // sampling resolution
];



