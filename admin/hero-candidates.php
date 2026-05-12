<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../inc/hero/authority.php';
require_once __DIR__ . '/../inc/hero/candidates.php';
require_once __DIR__ . '/../inc/hero/diagnostics.php';

header('Content-Type: application/json');

$itemId = (int)($_GET['item_id'] ?? 0);
if ($itemId <= 0) {
    echo json_encode(['error' => 'Invalid item']);
    exit;
}

$result = sw_enumerate_scored_candidates($pdo, $itemId);

if (isset($result['candidates']) && is_array($result['candidates'])) {
    foreach ($result['candidates'] as &$candidate) {
        $candidate['diagnostics'] = sw_get_hero_diagnostic_for_image((string)($candidate['path'] ?? ''));
    }
    unset($candidate);
}

if ((int)($_GET['include_shortlist'] ?? 0) === 1) {
    echo json_encode(sw_build_hero_shortlist_response($result));
    exit;
}

echo json_encode($result);

function sw_build_hero_shortlist_response(array $result): array
{
    $candidates = is_array($result['candidates'] ?? null) ? $result['candidates'] : [];
    $activeProfile = sw_infer_hero_criteria_profile($candidates);
    $recommended = [];
    $recommendedPaths = [];

    foreach ($candidates as $candidate) {
        if (!empty($candidate['status']['is_rejected'])) {
            continue;
        }

        $recommendationRank = count($recommended) + 1;
        $candidate = sw_add_hero_shortlist_metadata(
            $candidate,
            true,
            $recommendationRank,
            $activeProfile
        );

        $recommended[] = $candidate;
        $recommendedPaths[$candidate['path'] ?? ''] = true;

        if (count($recommended) >= 3) {
            break;
        }
    }

    $allCandidates = [];
    foreach ($candidates as $candidate) {
        $path = (string)($candidate['path'] ?? '');
        $isRecommended = isset($recommendedPaths[$path]);
        $rank = null;

        if ($isRecommended) {
            foreach ($recommended as $recommendedCandidate) {
                if (($recommendedCandidate['path'] ?? '') === $path) {
                    $rank = $recommendedCandidate['recommendation_rank'] ?? null;
                    break;
                }
            }
        }

        $allCandidates[] = sw_add_hero_shortlist_metadata(
            $candidate,
            $isRecommended,
            $rank,
            $activeProfile
        );
    }

    return [
        'item_id' => $result['item_id'] ?? null,
        'active_criteria_profile' => $activeProfile,
        'criteria_profile_metadata' => sw_hero_criteria_profile_metadata($activeProfile),
        'shortlist_basis' => 'legacy_rank_placeholder',
        'shortlist_status' => sw_hero_shortlist_status($recommended, $candidates),
        'current_hero' => sw_find_current_hero_summary($allCandidates),
        'recommended_candidates' => $recommended,
        'all_candidates' => $allCandidates,
    ];
}

function sw_add_hero_shortlist_metadata(array $candidate, bool $isRecommended, ?int $recommendationRank, ?string $activeProfile): array
{
    $candidate['is_recommended_top_three'] = $isRecommended;
    $candidate['recommendation_rank'] = $recommendationRank;
    $candidate['recommendation_reason'] = $isRecommended
        ? sw_hero_recommendation_reason($candidate)
        : null;
    $candidate['recommendation_confidence'] = $isRecommended
        ? sw_hero_recommendation_confidence($candidate)
        : null;
    $candidate['shortlist_basis'] = 'legacy_rank_placeholder';
    $candidate['shortlist_warning'] = 'Temporary shortlist uses existing rank, not criteria-aware AI ranking.';
    $candidate['active_criteria_profile'] = $activeProfile;

    return $candidate;
}

function sw_infer_hero_criteria_profile(array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        $productType = $candidate['diagnostics']['product_type'] ?? null;
        if (!is_string($productType) || $productType === '') {
            continue;
        }

        return match ($productType) {
            'object' => 'object_only',
            'lower_body', 'sports_bra' => 'body_region_first',
            'upper_body' => 'product_first',
            'full_body' => 'full_outfit',
            default => null,
        };
    }

    return null;
}

function sw_hero_criteria_profile_metadata(?string $profile): array
{
    return match ($profile) {
        'object_only' => [
            'face_policy' => 'not_required',
            'subject_emphasis' => 'object',
            'crop_policy' => 'strict',
            'score_scope' => 'legacy_rank_placeholder',
            'pose_logic_expected' => false,
            'object_logic_required' => true,
        ],
        'body_region_first' => [
            'face_policy' => 'optional',
            'subject_emphasis' => 'body_region',
            'crop_policy' => 'balanced',
            'score_scope' => 'legacy_rank_placeholder',
            'pose_logic_expected' => true,
            'object_logic_required' => false,
        ],
        'product_first' => [
            'face_policy' => 'optional',
            'subject_emphasis' => 'product',
            'crop_policy' => 'balanced',
            'score_scope' => 'legacy_rank_placeholder',
            'pose_logic_expected' => true,
            'object_logic_required' => false,
        ],
        'full_outfit' => [
            'face_policy' => 'optional',
            'subject_emphasis' => 'full_outfit',
            'crop_policy' => 'balanced',
            'score_scope' => 'legacy_rank_placeholder',
            'pose_logic_expected' => true,
            'object_logic_required' => false,
        ],
        default => [],
    };
}

function sw_hero_recommendation_reason(array $candidate): string
{
    $diagnostics = $candidate['diagnostics'] ?? [];
    if (!empty($diagnostics['available'])) {
        $specificity = $diagnostics['roi']['specificity'] ?? null;
        $confidence = $diagnostics['roi']['confidence'] ?? null;
        if (is_string($specificity) && $specificity !== '' && is_string($confidence) && $confidence !== '') {
            return "Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics available: {$specificity} with {$confidence} ROI confidence.";
        }

        return 'Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics available.';
    }

    return 'Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics unavailable for this image.';
}

function sw_hero_recommendation_confidence(array $candidate): string
{
    $diagnostics = $candidate['diagnostics'] ?? [];
    if (empty($diagnostics['available'])) {
        return 'low';
    }

    if (!empty($diagnostics['review']['needs_manual_review'])) {
        return 'low';
    }

    return 'medium';
}

function sw_hero_shortlist_status(array $recommended, array $candidates): string
{
    if (!$candidates || !$recommended) {
        return 'unavailable';
    }

    return count($recommended) < 3 ? 'partial' : 'ready';
}

function sw_find_current_hero_summary(array $candidates): ?array
{
    foreach ($candidates as $candidate) {
        if (empty($candidate['status']['is_current_hero'])) {
            continue;
        }

        $isInRecommended = !empty($candidate['is_recommended_top_three']);

        return [
            'path' => $candidate['path'] ?? null,
            'rank' => $candidate['rank'] ?? null,
            'is_in_recommended_candidates' => $isInRecommended,
            'current_hero_outside_top_three' => !$isInRecommended,
        ];
    }

    return null;
}

