<?php
/**
 * Read-only scoring for hero candidate inspection.
 * Inspired by image-picker logic, but softer and explainable.
 */
function sw_score_candidate(array $hr): float
{
    if (!$hr) return 0.0;

    $score = 0.0;

    // Faces matter, but don’t dominate
    if (!empty($hr['face_count'])) {
        $score += 20;
    }

    // Crop safety is important
    if (!empty($hr['crop_safe'])) {
        $score += 25;
    }

    // Reward headroom smoothly
    if ($hr['headroom_pct'] !== null) {
        $score += max(0, min(30, (float)$hr['headroom_pct']));
    }

    // Penalize too many faces
    if (!empty($hr['face_count'])) {
        $score -= 5 * max(0, ((int)$hr['face_count'] - 1));
    }

    return round($score, 2);
}



