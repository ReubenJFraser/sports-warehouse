// inc/image-picker.php
<?php
require_once __DIR__.'/image-headroom.php';

/**
 * @param array $candidates Each: ['basename' => 'xxx.jpg', 'ratio' => 0.8, ...]
 * @param float $boxRatio   Container ratio (e.g., 0.8 for 4:5)
 * @param array $headroom   Map from basename => meta (from sw_fetch_headroom_map)
 * @return array [bestCandidate, debugScores]
 */
function sw_pick_best_image(array $candidates, float $boxRatio, array $headroom): array {
  $scored = [];
  foreach ($candidates as $c) {
    $bname = strtolower($c['basename']);
    $imgR  = isset($c['ratio']) ? (float)$c['ratio'] : ($headroom[$bname]['ratio'] ?? null);
    if (!$imgR || $imgR <= 0) continue;

    // 1) Aspect-ratio fit: 0..100 (100 = perfect)
    //   log-distance is more forgiving near 1:1
    $fit = max(0.0, 100.0 - 120.0 * abs(log($imgR / $boxRatio))); // tweak 120→140 if you want ratio to dominate more

    // 2) Headroom signals
    $meta = $headroom[$bname] ?? null;
    $penalty = 0.0; $bonus = 0.0;
    if ($meta) {
      $face   = $meta['face_count'];
      $safe   = $meta['crop_safe'];
      $hr     = $meta['headroom_pct'];

      if ($face === 0)        $penalty -= 8.0;     // mild: sometimes model is turned away
      if ($safe === 0)        $penalty -= 25.0;    // strong: head too close to top
      if (!is_null($hr)) {
        // sweet spot: 8–22% from top tends to keep heads intact in 4:5/2:3 boxes
        if ($hr >= 8.0 && $hr <= 22.0) $bonus += 10.0;
        // too tight: below 6%
        if ($hr < 6.0)                  $penalty -= 12.0;
      }
    }

    $score = $fit + $bonus + $penalty;
    $scored[] = ['candidate' => $c, 'score' => $score, 'fit' => $fit, 'bonus' => $bonus, 'penalty' => $penalty];
  }

  usort($scored, fn($a,$b) => $b['score'] <=> $a['score']);
  return [$scored[0]['candidate'] ?? null, $scored];
}


