<?php

function sw_hero_rationale_allowed_reason_codes(): array
{
    return [
        'rear_facing_unsuitable_angle',
        'side_facing_insufficiently_clear',
        'product_visible_but_not_primary_hero_suitable',
        'product_focus_conflicts_with_editorial_presentation',
        'full_body_model_presentation_preferred',
        'face_or_model_context_needed',
        'criteria_profile_probably_wrong',
        'product_or_category_metadata_may_be_wrong',
        'diagnostics_or_ranking_appear_wrong',
        'no_ideal_image_exists',
        'human_editorial_judgement_override',
    ];
}

function sw_hero_rationale_parse_reason_codes($raw): array
{
    if (!is_string($raw) || trim($raw) === '') {
        return ['codes' => [], 'warning' => null];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['codes' => [], 'warning' => 'stored_reason_codes_invalid_json'];
    }

    $codes = [];
    foreach ($decoded as $code) {
        if (is_string($code) && trim($code) !== '') {
            $codes[] = trim($code);
        }
    }

    return ['codes' => array_values(array_unique($codes)), 'warning' => null];
}

function sw_hero_rationale_trimmed_nullable_string($value, int $maxLength = 1024): ?string
{
    if ($value === null) {
        return null;
    }

    if (!is_string($value) && !is_numeric($value)) {
        throw new InvalidArgumentException('Field must be a string');
    }

    $trimmed = trim((string)$value);
    if ($trimmed === '') {
        return null;
    }

    if (strlen($trimmed) > $maxLength) {
        throw new InvalidArgumentException('Field exceeds maximum length of ' . $maxLength);
    }

    return $trimmed;
}

function sw_hero_rationale_nullable_positive_int($value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_string($value)) {
        $value = trim($value);
    }

    if (!is_numeric($value) || (string)(int)$value !== (string)$value && !is_int($value)) {
        throw new InvalidArgumentException('Rank fields must be positive integers');
    }

    $intValue = (int)$value;
    if ($intValue <= 0) {
        throw new InvalidArgumentException('Rank fields must be positive integers');
    }

    return $intValue;
}

function sw_hero_rationale_nullable_decimal($value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_string($value)) {
        $value = trim($value);
    }

    if (!is_numeric($value)) {
        throw new InvalidArgumentException('Score fields must be numeric');
    }

    return (string)$value;
}

function sw_hero_rationale_nullable_json_text($value, string $fieldName): ?string
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_array($value) || is_object($value)) {
        $encoded = json_encode($value, JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new InvalidArgumentException($fieldName . ' must be valid JSON');
        }
        return $encoded;
    }

    if (!is_string($value)) {
        throw new InvalidArgumentException($fieldName . ' must be JSON string/array/object');
    }

    $trimmed = trim($value);
    if ($trimmed === '') {
        return null;
    }

    json_decode($trimmed, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException($fieldName . ' contains malformed JSON');
    }

    return $trimmed;
}

function sw_hero_rationale_fetch_active(PDO $pdo, int $itemId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT rationale_id, itemId, selected_hero_image, current_hero_image, active_criteria_profile, shortlist_basis, current_hero_rank, current_hero_outside_top_three, selected_reason_codes, optional_note, criteria_refinement_signal, image_set_limitation_signal, metadata_issue_signal, diagnostics_issue_signal, product_name_snapshot, brand_snapshot, selected_image_path, selected_image_rank_snapshot, selected_image_score_snapshot, selected_image_role, ranked_1_image_path_snapshot, ranked_1_image_score_snapshot, ranked_1_image_role, ranked_1_reason_snapshot, displaced_current_hero_path_snapshot, displaced_current_hero_rank_snapshot, displaced_current_hero_role, criteria_profile_snapshot, shortlist_basis_snapshot, decision_type, comparison_target_role, product_specific_reason_codes, cross_cutting_signal_codes, reviewer_note, counts_toward_criteria_refinement, data_quality_only, candidate_snapshot_json, reviewer_metadata_json, created_at, updated_at
         FROM hero_override_rationale
         WHERE itemId = :item_id AND is_active = 1
         ORDER BY rationale_id DESC
         LIMIT 1'
    );
    $stmt->execute([':item_id' => $itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $parsedCodes = sw_hero_rationale_parse_reason_codes($row['selected_reason_codes'] ?? null);
    $row['selected_reason_codes'] = $parsedCodes['codes'];
    $row['current_hero_rank'] = isset($row['current_hero_rank']) ? (int)$row['current_hero_rank'] : null;
    $row['current_hero_outside_top_three'] = (bool)($row['current_hero_outside_top_three'] ?? false);
    $row['criteria_refinement_signal'] = (bool)($row['criteria_refinement_signal'] ?? false);
    $row['image_set_limitation_signal'] = (bool)($row['image_set_limitation_signal'] ?? false);
    $row['metadata_issue_signal'] = (bool)($row['metadata_issue_signal'] ?? false);
    $row['diagnostics_issue_signal'] = (bool)($row['diagnostics_issue_signal'] ?? false);
    $row['selected_image_rank_snapshot'] = isset($row['selected_image_rank_snapshot']) ? (int)$row['selected_image_rank_snapshot'] : null;
    $row['displaced_current_hero_rank_snapshot'] = isset($row['displaced_current_hero_rank_snapshot']) ? (int)$row['displaced_current_hero_rank_snapshot'] : null;
    $row['counts_toward_criteria_refinement'] = (bool)($row['counts_toward_criteria_refinement'] ?? false);
    $row['data_quality_only'] = (bool)($row['data_quality_only'] ?? false);

    if ($parsedCodes['warning'] !== null) {
        $row['warning'] = $parsedCodes['warning'];
    }

    return $row;
}

function sw_hero_rationale_validate_reason_codes(array $reasonCodes): array
{
    $allowed = array_flip(sw_hero_rationale_allowed_reason_codes());
    $normalized = [];
    $invalid = [];

    foreach ($reasonCodes as $code) {
        if (!is_string($code) || trim($code) === '') {
            $invalid[] = $code;
            continue;
        }

        $value = trim($code);
        if (!isset($allowed[$value])) {
            $invalid[] = $value;
            continue;
        }

        $normalized[] = $value;
    }

    return [
        'codes' => array_values(array_unique($normalized)),
        'invalid' => $invalid,
    ];
}

function sw_hero_rationale_normalize_for_compare(array $row): array
{
    $reasonCodes = $row['selected_reason_codes'] ?? [];
    if (!is_array($reasonCodes)) {
        $parsed = sw_hero_rationale_parse_reason_codes((string)$reasonCodes);
        $reasonCodes = $parsed['codes'];
    }

    $normalizedCodes = [];
    foreach ($reasonCodes as $code) {
        if (is_string($code) && trim($code) !== '') {
            $normalizedCodes[] = trim($code);
        }
    }
    $normalizedCodes = array_values(array_unique($normalizedCodes));
    sort($normalizedCodes);

    return [
        'itemId' => (int)($row['itemId'] ?? 0),
        'selected_hero_image' => trim((string)($row['selected_hero_image'] ?? '')),
        'current_hero_image' => trim((string)($row['current_hero_image'] ?? '')),
        'active_criteria_profile' => trim((string)($row['active_criteria_profile'] ?? '')),
        'shortlist_basis' => trim((string)($row['shortlist_basis'] ?? '')),
        'current_hero_rank' => isset($row['current_hero_rank']) && $row['current_hero_rank'] !== '' ? (int)$row['current_hero_rank'] : null,
        'current_hero_outside_top_three' => !empty($row['current_hero_outside_top_three']),
        'selected_reason_codes' => $normalizedCodes,
        'optional_note' => trim((string)($row['optional_note'] ?? '')),
        'criteria_refinement_signal' => !empty($row['criteria_refinement_signal']),
        'image_set_limitation_signal' => !empty($row['image_set_limitation_signal']),
        'metadata_issue_signal' => !empty($row['metadata_issue_signal']),
        'diagnostics_issue_signal' => !empty($row['diagnostics_issue_signal']),
        'product_name_snapshot' => trim((string)($row['product_name_snapshot'] ?? '')),
        'brand_snapshot' => trim((string)($row['brand_snapshot'] ?? '')),
        'selected_image_path' => trim((string)($row['selected_image_path'] ?? '')),
        'selected_image_rank_snapshot' => isset($row['selected_image_rank_snapshot']) && $row['selected_image_rank_snapshot'] !== '' ? (int)$row['selected_image_rank_snapshot'] : null,
        'selected_image_score_snapshot' => isset($row['selected_image_score_snapshot']) && $row['selected_image_score_snapshot'] !== '' ? (string)$row['selected_image_score_snapshot'] : null,
        'selected_image_role' => trim((string)($row['selected_image_role'] ?? '')),
        'ranked_1_image_path_snapshot' => trim((string)($row['ranked_1_image_path_snapshot'] ?? '')),
        'ranked_1_image_score_snapshot' => isset($row['ranked_1_image_score_snapshot']) && $row['ranked_1_image_score_snapshot'] !== '' ? (string)$row['ranked_1_image_score_snapshot'] : null,
        'ranked_1_image_role' => trim((string)($row['ranked_1_image_role'] ?? '')),
        'ranked_1_reason_snapshot' => trim((string)($row['ranked_1_reason_snapshot'] ?? '')),
        'displaced_current_hero_path_snapshot' => trim((string)($row['displaced_current_hero_path_snapshot'] ?? '')),
        'displaced_current_hero_rank_snapshot' => isset($row['displaced_current_hero_rank_snapshot']) && $row['displaced_current_hero_rank_snapshot'] !== '' ? (int)$row['displaced_current_hero_rank_snapshot'] : null,
        'displaced_current_hero_role' => trim((string)($row['displaced_current_hero_role'] ?? '')),
        'criteria_profile_snapshot' => trim((string)($row['criteria_profile_snapshot'] ?? '')),
        'shortlist_basis_snapshot' => trim((string)($row['shortlist_basis_snapshot'] ?? '')),
        'decision_type' => trim((string)($row['decision_type'] ?? '')),
        'comparison_target_role' => trim((string)($row['comparison_target_role'] ?? '')),
        'product_specific_reason_codes' => trim((string)($row['product_specific_reason_codes'] ?? '')),
        'cross_cutting_signal_codes' => trim((string)($row['cross_cutting_signal_codes'] ?? '')),
        'reviewer_note' => trim((string)($row['reviewer_note'] ?? '')),
        'counts_toward_criteria_refinement' => !empty($row['counts_toward_criteria_refinement']),
        'data_quality_only' => !empty($row['data_quality_only']),
        'candidate_snapshot_json' => trim((string)($row['candidate_snapshot_json'] ?? '')),
        'reviewer_metadata_json' => trim((string)($row['reviewer_metadata_json'] ?? '')),
    ];
}

function sw_hero_rationale_save(PDO $pdo, array $payload): array
{
    $itemId = (int)($payload['itemId'] ?? 0);
    if ($itemId <= 0) {
        throw new InvalidArgumentException('Invalid itemId');
    }

    $selectedHeroImage = trim((string)($payload['selected_hero_image'] ?? ''));
    if ($selectedHeroImage === '') {
        throw new InvalidArgumentException('selected_hero_image is required');
    }

    $reasonCodes = $payload['selected_reason_codes'] ?? [];
    if (!is_array($reasonCodes)) {
        throw new InvalidArgumentException('selected_reason_codes must be an array');
    }

    $validation = sw_hero_rationale_validate_reason_codes($reasonCodes);
    if (!empty($validation['invalid'])) {
        throw new InvalidArgumentException('Unknown reason codes: ' . implode(', ', array_map('strval', $validation['invalid'])));
    }

    $decisionType = sw_hero_rationale_trimmed_nullable_string($payload['decision_type'] ?? null, 80);
    $comparisonTargetRole = sw_hero_rationale_trimmed_nullable_string($payload['comparison_target_role'] ?? null, 80);

    $knownDecisionTypes = [
        'accepted_top_candidate','corrected_old_stored_hero','manual_override_against_top_candidate','paired_product_differentiation','product_detail_closeup_preferred','model_personality_hero_preferred','campaign_background_context_preferred','missing_image_data_failure','temporary_best_available_image',
    ];
    if ($decisionType !== null && !preg_match('/^[a-z0-9_\-]+$/', $decisionType)) {
        throw new InvalidArgumentException('decision_type contains invalid characters');
    }

    $normalizedPayload = sw_hero_rationale_normalize_for_compare([
        'itemId' => $itemId,
        'selected_hero_image' => $selectedHeroImage,
        'current_hero_image' => $payload['current_hero_image'] ?? '',
        'active_criteria_profile' => $payload['active_criteria_profile'] ?? '',
        'shortlist_basis' => $payload['shortlist_basis'] ?? '',
        'current_hero_rank' => $payload['current_hero_rank'] ?? null,
        'current_hero_outside_top_three' => $payload['current_hero_outside_top_three'] ?? false,
        'selected_reason_codes' => $validation['codes'],
        'optional_note' => $payload['optional_note'] ?? '',
        'criteria_refinement_signal' => $payload['criteria_refinement_signal'] ?? false,
        'image_set_limitation_signal' => $payload['image_set_limitation_signal'] ?? false,
        'metadata_issue_signal' => $payload['metadata_issue_signal'] ?? false,
        'diagnostics_issue_signal' => $payload['diagnostics_issue_signal'] ?? false,
        'product_name_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['product_name_snapshot'] ?? null, 255) ?? '',
        'brand_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['brand_snapshot'] ?? null, 100) ?? '',
        'selected_image_path' => sw_hero_rationale_trimmed_nullable_string($payload['selected_image_path'] ?? null, 1024) ?? '',
        'selected_image_rank_snapshot' => sw_hero_rationale_nullable_positive_int($payload['selected_image_rank_snapshot'] ?? null),
        'selected_image_score_snapshot' => sw_hero_rationale_nullable_decimal($payload['selected_image_score_snapshot'] ?? null),
        'selected_image_role' => sw_hero_rationale_trimmed_nullable_string($payload['selected_image_role'] ?? null, 80) ?? '',
        'ranked_1_image_path_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['ranked_1_image_path_snapshot'] ?? null, 1024) ?? '',
        'ranked_1_image_score_snapshot' => sw_hero_rationale_nullable_decimal($payload['ranked_1_image_score_snapshot'] ?? null),
        'ranked_1_image_role' => sw_hero_rationale_trimmed_nullable_string($payload['ranked_1_image_role'] ?? null, 80) ?? '',
        'ranked_1_reason_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['ranked_1_reason_snapshot'] ?? null, 65535) ?? '',
        'displaced_current_hero_path_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['displaced_current_hero_path_snapshot'] ?? null, 1024) ?? '',
        'displaced_current_hero_rank_snapshot' => sw_hero_rationale_nullable_positive_int($payload['displaced_current_hero_rank_snapshot'] ?? null),
        'displaced_current_hero_role' => sw_hero_rationale_trimmed_nullable_string($payload['displaced_current_hero_role'] ?? null, 80) ?? '',
        'criteria_profile_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['criteria_profile_snapshot'] ?? null, 128) ?? '',
        'shortlist_basis_snapshot' => sw_hero_rationale_trimmed_nullable_string($payload['shortlist_basis_snapshot'] ?? null, 128) ?? '',
        'decision_type' => $decisionType ?? '',
        'comparison_target_role' => $comparisonTargetRole ?? '',
        'product_specific_reason_codes' => sw_hero_rationale_nullable_json_text($payload['product_specific_reason_codes'] ?? null, 'product_specific_reason_codes') ?? '',
        'cross_cutting_signal_codes' => sw_hero_rationale_nullable_json_text($payload['cross_cutting_signal_codes'] ?? null, 'cross_cutting_signal_codes') ?? '',
        'reviewer_note' => sw_hero_rationale_trimmed_nullable_string($payload['reviewer_note'] ?? null, 65535) ?? '',
        'counts_toward_criteria_refinement' => !empty($payload['counts_toward_criteria_refinement']),
        'data_quality_only' => !empty($payload['data_quality_only']),
        'candidate_snapshot_json' => sw_hero_rationale_nullable_json_text($payload['candidate_snapshot_json'] ?? null, 'candidate_snapshot_json') ?? '',
        'reviewer_metadata_json' => sw_hero_rationale_nullable_json_text($payload['reviewer_metadata_json'] ?? null, 'reviewer_metadata_json') ?? '',
    ]);

    $active = sw_hero_rationale_fetch_active($pdo, $itemId);
    if ($active) {
        $normalizedActive = sw_hero_rationale_normalize_for_compare($active);
        if ($normalizedActive === $normalizedPayload) {
            return [
                'rationale_id' => (int)$active['rationale_id'],
                'unchanged' => true,
            ];
        }
    }

    $encodedReasonCodes = json_encode($validation['codes'], JSON_UNESCAPED_SLASHES);
    if ($encodedReasonCodes === false) {
        throw new RuntimeException('Failed to encode selected_reason_codes');
    }

    $now = gmdate('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $supersede = $pdo->prepare(
            'UPDATE hero_override_rationale
             SET is_active = 0, superseded_at = :superseded_at, updated_at = :updated_at
             WHERE itemId = :item_id AND is_active = 1'
        );
        $supersede->execute([
            ':superseded_at' => $now,
            ':updated_at' => $now,
            ':item_id' => $itemId,
        ]);

        $insert = $pdo->prepare(
            'INSERT INTO hero_override_rationale
            (itemId, product_name_snapshot, brand_snapshot, selected_image_path, selected_image_rank_snapshot, selected_image_score_snapshot, selected_image_role, ranked_1_image_path_snapshot, ranked_1_image_score_snapshot, ranked_1_image_role, ranked_1_reason_snapshot, displaced_current_hero_path_snapshot, displaced_current_hero_rank_snapshot, displaced_current_hero_role, criteria_profile_snapshot, shortlist_basis_snapshot, decision_type, comparison_target_role, product_specific_reason_codes, cross_cutting_signal_codes, reviewer_note, counts_toward_criteria_refinement, data_quality_only, candidate_snapshot_json, reviewer_metadata_json, selected_hero_image, current_hero_image, active_criteria_profile, shortlist_basis, current_hero_rank, current_hero_outside_top_three, selected_reason_codes, optional_note, criteria_refinement_signal, image_set_limitation_signal, metadata_issue_signal, diagnostics_issue_signal, is_active, created_at, updated_at)
            VALUES
            (:item_id, :product_name_snapshot, :brand_snapshot, :selected_image_path, :selected_image_rank_snapshot, :selected_image_score_snapshot, :selected_image_role, :ranked_1_image_path_snapshot, :ranked_1_image_score_snapshot, :ranked_1_image_role, :ranked_1_reason_snapshot, :displaced_current_hero_path_snapshot, :displaced_current_hero_rank_snapshot, :displaced_current_hero_role, :criteria_profile_snapshot, :shortlist_basis_snapshot, :decision_type, :comparison_target_role, :product_specific_reason_codes, :cross_cutting_signal_codes, :reviewer_note, :counts_toward_criteria_refinement, :data_quality_only, :candidate_snapshot_json, :reviewer_metadata_json, :selected_hero_image, :current_hero_image, :active_criteria_profile, :shortlist_basis, :current_hero_rank, :current_hero_outside_top_three, :selected_reason_codes, :optional_note, :criteria_refinement_signal, :image_set_limitation_signal, :metadata_issue_signal, :diagnostics_issue_signal, 1, :created_at, :updated_at)'
        );

        $insert->execute([
            ':item_id' => $itemId,
            ':product_name_snapshot' => $normalizedPayload['product_name_snapshot'] !== '' ? $normalizedPayload['product_name_snapshot'] : null,
            ':brand_snapshot' => $normalizedPayload['brand_snapshot'] !== '' ? $normalizedPayload['brand_snapshot'] : null,
            ':selected_image_path' => $normalizedPayload['selected_image_path'] !== '' ? $normalizedPayload['selected_image_path'] : null,
            ':selected_image_rank_snapshot' => $normalizedPayload['selected_image_rank_snapshot'],
            ':selected_image_score_snapshot' => $normalizedPayload['selected_image_score_snapshot'],
            ':selected_image_role' => $normalizedPayload['selected_image_role'] !== '' ? $normalizedPayload['selected_image_role'] : null,
            ':ranked_1_image_path_snapshot' => $normalizedPayload['ranked_1_image_path_snapshot'] !== '' ? $normalizedPayload['ranked_1_image_path_snapshot'] : null,
            ':ranked_1_image_score_snapshot' => $normalizedPayload['ranked_1_image_score_snapshot'],
            ':ranked_1_image_role' => $normalizedPayload['ranked_1_image_role'] !== '' ? $normalizedPayload['ranked_1_image_role'] : null,
            ':ranked_1_reason_snapshot' => $normalizedPayload['ranked_1_reason_snapshot'] !== '' ? $normalizedPayload['ranked_1_reason_snapshot'] : null,
            ':displaced_current_hero_path_snapshot' => $normalizedPayload['displaced_current_hero_path_snapshot'] !== '' ? $normalizedPayload['displaced_current_hero_path_snapshot'] : null,
            ':displaced_current_hero_rank_snapshot' => $normalizedPayload['displaced_current_hero_rank_snapshot'],
            ':displaced_current_hero_role' => $normalizedPayload['displaced_current_hero_role'] !== '' ? $normalizedPayload['displaced_current_hero_role'] : null,
            ':criteria_profile_snapshot' => $normalizedPayload['criteria_profile_snapshot'] !== '' ? $normalizedPayload['criteria_profile_snapshot'] : null,
            ':shortlist_basis_snapshot' => $normalizedPayload['shortlist_basis_snapshot'] !== '' ? $normalizedPayload['shortlist_basis_snapshot'] : null,
            ':decision_type' => $normalizedPayload['decision_type'] !== '' ? $normalizedPayload['decision_type'] : null,
            ':comparison_target_role' => $normalizedPayload['comparison_target_role'] !== '' ? $normalizedPayload['comparison_target_role'] : null,
            ':product_specific_reason_codes' => $normalizedPayload['product_specific_reason_codes'] !== '' ? $normalizedPayload['product_specific_reason_codes'] : null,
            ':cross_cutting_signal_codes' => $normalizedPayload['cross_cutting_signal_codes'] !== '' ? $normalizedPayload['cross_cutting_signal_codes'] : null,
            ':reviewer_note' => $normalizedPayload['reviewer_note'] !== '' ? $normalizedPayload['reviewer_note'] : null,
            ':counts_toward_criteria_refinement' => $normalizedPayload['counts_toward_criteria_refinement'] ? 1 : 0,
            ':data_quality_only' => $normalizedPayload['data_quality_only'] ? 1 : 0,
            ':candidate_snapshot_json' => $normalizedPayload['candidate_snapshot_json'] !== '' ? $normalizedPayload['candidate_snapshot_json'] : null,
            ':reviewer_metadata_json' => $normalizedPayload['reviewer_metadata_json'] !== '' ? $normalizedPayload['reviewer_metadata_json'] : null,
            ':selected_hero_image' => $selectedHeroImage,
            ':current_hero_image' => $normalizedPayload['current_hero_image'],
            ':active_criteria_profile' => $normalizedPayload['active_criteria_profile'],
            ':shortlist_basis' => $normalizedPayload['shortlist_basis'],
            ':current_hero_rank' => $normalizedPayload['current_hero_rank'],
            ':current_hero_outside_top_three' => $normalizedPayload['current_hero_outside_top_three'] ? 1 : 0,
            ':selected_reason_codes' => $encodedReasonCodes,
            ':optional_note' => $normalizedPayload['optional_note'],
            ':criteria_refinement_signal' => $normalizedPayload['criteria_refinement_signal'] ? 1 : 0,
            ':image_set_limitation_signal' => $normalizedPayload['image_set_limitation_signal'] ? 1 : 0,
            ':metadata_issue_signal' => $normalizedPayload['metadata_issue_signal'] ? 1 : 0,
            ':diagnostics_issue_signal' => $normalizedPayload['diagnostics_issue_signal'] ? 1 : 0,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $rationaleId = (int)$pdo->lastInsertId();
        $pdo->commit();
        return [
            'rationale_id' => $rationaleId,
            'unchanged' => false,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
