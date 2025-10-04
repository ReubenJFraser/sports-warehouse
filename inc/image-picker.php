<?php
// inc/image-picker.php
// Select the best "hero" image for a 4:5 card, enforcing BOTH:
//  - face present in upper third
//  - crop_safe
// Then use aspect-ratio closeness as a tie-breaker.
// If no face candidates exist, fall back to “closest ratio”.

/**
 * @param array $candidates  Each: ['path'=>string, 'basename'=>string, 'source'=>'stored|thumb', 'ratio'?=>float]
 * @param float $boxRatio    Target ratio for the card window (4:5 = 0.8)
 * @param array $headroomMap Map: basename => ['ratio'=>float,'face_count'=>int,'focus_y_pct'=>float,'headroom_pct'=>float,'crop_safe'=>int]
 *
 * @return array [bestCandidate|null, scored[]]
 *                bestCandidate has the same shape as input candidate and may include 'ratio'
 */
function sw_pick_best_image(array $candidates, float $boxRatio, array $headroomMap): array
{
  // Defensive defaults
  $boxRatio = max(0.01, (float)$boxRatio);

  // Score each candidate
  $scored = [];
  $seq = 0;
  foreach ($candidates as $c) {
    $seq++;

    $path = (string)($c['path'] ?? '');
    if ($path === '') continue;

    // Normalize and ensure we have a basename
    $basename = strtolower($c['basename'] ?? '');
    if ($basename === '') {
      $basename = strtolower(basename($path));
    }

    // Headroom metadata (if any)
    $meta      = $headroomMap[$basename] ?? [];
    $imgRatio  = isset($c['ratio']) ? (float)$c['ratio'] : (isset($meta['ratio']) ? (float)$meta['ratio'] : null);
    if (!$imgRatio || $imgRatio <= 0) {
      // If we truly don’t know the ratio, approximate with box ratio so it doesn't dominate sorting
      $imgRatio = $boxRatio;
    }

    $ratioPenalty = abs($imgRatio - $boxRatio); // smaller is better

    $faceCount  = isset($meta['face_count']) ? (int)$meta['face_count'] : 0;
    $hasFace    = $faceCount > 0;
    $focusY     = isset($meta['focus_y_pct']) ? (float)$meta['focus_y_pct'] : null;   // 0=top, 100=bottom
    $headroom   = isset($meta['headroom_pct']) ? (float)$meta['headroom_pct'] : null; // % of safe sky above
    $cropSafe   = isset($meta['crop_safe']) ? (int)$meta['crop_safe'] === 1 : false;

    $inUpperThird = $hasFace && $focusY !== null && $focusY <= 33.34;
    $enoughHead   = $headroom === null ? true : ($headroom >= 6.0);

    // Primary gate: only reward when ALL are true
    $primary = ($inUpperThird && $cropSafe && $enoughHead) ? 1 : 0;

    // Secondary quality: prefer faces, then crop-safe, then closer ratio
    $secondary = ($hasFace ? 1 : 0) + ($cropSafe ? 1 : 0) + (1 - min($ratioPenalty, 1.0));

    // Combined score: primary dominates; keep input order as last tie-break
    $score = $primary * 1000 + $secondary;

    $scored[] = [
      'score'        => $score,
      'ratioPenalty' => $ratioPenalty,
      'seq'          => $seq,
      'candidate'    => [
        'path'     => $path,
        'basename' => $basename,
        'source'   => $c['source'] ?? null,
        'ratio'    => $imgRatio,
      ],
    ];
  }

  if (!$scored) return [null, []];

  usort($scored, function ($a, $b) {
    if ($a['score'] !== $b['score'])       return $b['score']        <=> $a['score'];        // higher score first
    if ($a['ratioPenalty'] !== $b['ratioPenalty']) return $a['ratioPenalty'] <=> $b['ratioPenalty']; // closer ratio first
    return $a['seq'] <=> $b['seq']; // stable with original order
  });

  return [$scored[0]['candidate'], $scored];
}



