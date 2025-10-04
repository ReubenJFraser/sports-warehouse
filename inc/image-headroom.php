<?php
function sw_fetch_headroom_map(PDO $pdo, array $basenames): array {
  if (empty($basenames)) return [];
  // unique & placeholders
  $basenames = array_values(array_unique(array_map('strval', $basenames)));
  $ph = implode(',', array_fill(0, count($basenames), '?'));
  $sql = "SELECT image_basename, face_count, headroom_pct, focus_y_pct, crop_safe, ratio
          FROM image_headroom WHERE image_basename IN ($ph)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($basenames);
  $out = [];
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $out[strtolower($r['image_basename'])] = [
      'face_count'   => is_null($r['face_count'])   ? null : (int)$r['face_count'],
      'headroom_pct' => is_null($r['headroom_pct']) ? null : (float)$r['headroom_pct'],
      'focus_y_pct'  => is_null($r['focus_y_pct'])  ? null : (float)$r['focus_y_pct'],
      'crop_safe'    => is_null($r['crop_safe'])    ? null : (int)$r['crop_safe'],
      'ratio'        => is_null($r['ratio'])        ? null : (float)$r['ratio'],
    ];
  }
  return $out;
}


