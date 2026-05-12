<?php

function sw_infer_hero_criteria_profile(array $candidates, array $item = []): ?string
{
    foreach ($candidates as $candidate) {
        $productType = $candidate['diagnostics']['product_type'] ?? $item['product_type'] ?? null;
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

function sw_get_hero_criteria_profile_metadata(?string $profile): array
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

function sw_build_hero_shortlist_contract(int $itemId, array $candidates, array $item = [], array $options = []): array
{
    $shortlistBasis = is_string($options['shortlist_basis'] ?? null)
        ? $options['shortlist_basis']
        : 'legacy_rank_placeholder';

    $activeProfile = is_string($options['active_criteria_profile'] ?? null)
        ? $options['active_criteria_profile']
        : sw_infer_hero_criteria_profile($candidates, $item);

    $recommended = sw_select_recommended_hero_candidates($candidates, (int)($options['limit'] ?? 3));
    $recommendedPaths = [];
    $recommendedCandidates = [];

    foreach ($recommended as $index => $candidate) {
        $recommendationRank = $index + 1;
        $candidate = sw_enrich_hero_shortlist_candidate(
            $candidate,
            $recommendationRank,
            $activeProfile,
            $shortlistBasis
        );

        $recommendedCandidates[] = $candidate;
        $recommendedPaths[$candidate['path'] ?? ''] = $recommendationRank;
    }

    $allCandidates = [];
    foreach ($candidates as $candidate) {
        $path = (string)($candidate['path'] ?? '');
        $rank = $recommendedPaths[$path] ?? null;

        $allCandidates[] = sw_enrich_hero_shortlist_candidate(
            $candidate,
            $rank,
            $activeProfile,
            $shortlistBasis
        );
    }

    return [
        'item_id' => $itemId,
        'active_criteria_profile' => $activeProfile,
        'criteria_profile_metadata' => sw_get_hero_criteria_profile_metadata($activeProfile),
        'shortlist_basis' => $shortlistBasis,
        'shortlist_status' => sw_get_hero_shortlist_status($recommendedCandidates, $candidates),
        'current_hero' => sw_build_current_hero_summary($allCandidates, $recommendedCandidates),
        'recommended_candidates' => $recommendedCandidates,
        'all_candidates' => $allCandidates,
    ];
}

function sw_select_recommended_hero_candidates(array $candidates, int $limit = 3): array
{
    $recommended = [];

    foreach ($candidates as $candidate) {
        if (!empty($candidate['status']['is_rejected'])) {
            continue;
        }

        $recommended[] = $candidate;
        if (count($recommended) >= $limit) {
            break;
        }
    }

    return $recommended;
}

function sw_enrich_hero_shortlist_candidate(array $candidate, ?int $recommendationRank, ?string $activeCriteriaProfile, string $shortlistBasis): array
{
    $isRecommended = $recommendationRank !== null;

    $candidate['is_recommended_top_three'] = $isRecommended;
    $candidate['recommendation_rank'] = $recommendationRank;
    $candidate['recommendation_reason'] = $isRecommended
        ? sw_build_hero_recommendation_reason($candidate, $shortlistBasis)
        : null;
    $candidate['recommendation_confidence'] = $isRecommended
        ? sw_build_hero_recommendation_confidence($candidate, $shortlistBasis)
        : null;
    $candidate['shortlist_basis'] = $shortlistBasis;
    $candidate['shortlist_warning'] = sw_build_hero_shortlist_warning($shortlistBasis);
    $candidate['active_criteria_profile'] = $activeCriteriaProfile;

    return $candidate;
}

function sw_build_hero_recommendation_reason(array $candidate, string $shortlistBasis): string
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

function sw_build_hero_recommendation_confidence(array $candidate, string $shortlistBasis): string
{
    if ($shortlistBasis !== 'legacy_rank_placeholder') {
        return 'unavailable';
    }

    $diagnostics = $candidate['diagnostics'] ?? [];
    if (empty($diagnostics['available'])) {
        return 'low';
    }

    if (!empty($diagnostics['review']['needs_manual_review'])) {
        return 'low';
    }

    return 'medium';
}

function sw_build_current_hero_summary(array $candidates, array $recommendedCandidates): ?array
{
    $recommendedPaths = [];
    foreach ($recommendedCandidates as $candidate) {
        $recommendedPaths[$candidate['path'] ?? ''] = true;
    }

    foreach ($candidates as $candidate) {
        if (empty($candidate['status']['is_current_hero'])) {
            continue;
        }

        $path = (string)($candidate['path'] ?? '');
        $isInRecommended = isset($recommendedPaths[$path]);

        return [
            'path' => $candidate['path'] ?? null,
            'rank' => $candidate['rank'] ?? null,
            'is_in_recommended_candidates' => $isInRecommended,
            'current_hero_outside_top_three' => !$isInRecommended,
            'is_manual_override' => (bool)($candidate['status']['is_manual_override'] ?? false),
        ];
    }

    return null;
}

function sw_get_hero_shortlist_status(array $recommendedCandidates, array $allCandidates): string
{
    if (!$allCandidates || !$recommendedCandidates) {
        return 'unavailable';
    }

    return count($recommendedCandidates) < 3 ? 'partial' : 'ready';
}

function sw_build_hero_shortlist_warning(string $shortlistBasis): ?string
{
    if ($shortlistBasis === 'legacy_rank_placeholder') {
        return 'Temporary shortlist uses existing Hero Manager rank, not criteria-aware AI ranking.';
    }

    return null;
}
