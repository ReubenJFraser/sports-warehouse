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

function sw_hero_rationale_fetch_active(PDO $pdo, int $itemId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT rationale_id, itemId, selected_hero_image, current_hero_image, active_criteria_profile, shortlist_basis, current_hero_rank, current_hero_outside_top_three, selected_reason_codes, optional_note, criteria_refinement_signal, image_set_limitation_signal, metadata_issue_signal, diagnostics_issue_signal, created_at, updated_at
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

function sw_hero_rationale_save(PDO $pdo, array $payload): int
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
            (itemId, selected_hero_image, current_hero_image, active_criteria_profile, shortlist_basis, current_hero_rank, current_hero_outside_top_three, selected_reason_codes, optional_note, criteria_refinement_signal, image_set_limitation_signal, metadata_issue_signal, diagnostics_issue_signal, is_active, created_at, updated_at)
            VALUES
            (:item_id, :selected_hero_image, :current_hero_image, :active_criteria_profile, :shortlist_basis, :current_hero_rank, :current_hero_outside_top_three, :selected_reason_codes, :optional_note, :criteria_refinement_signal, :image_set_limitation_signal, :metadata_issue_signal, :diagnostics_issue_signal, 1, :created_at, :updated_at)'
        );

        $insert->execute([
            ':item_id' => $itemId,
            ':selected_hero_image' => $selectedHeroImage,
            ':current_hero_image' => trim((string)($payload['current_hero_image'] ?? '')),
            ':active_criteria_profile' => trim((string)($payload['active_criteria_profile'] ?? '')),
            ':shortlist_basis' => trim((string)($payload['shortlist_basis'] ?? '')),
            ':current_hero_rank' => isset($payload['current_hero_rank']) && $payload['current_hero_rank'] !== '' ? (int)$payload['current_hero_rank'] : null,
            ':current_hero_outside_top_three' => !empty($payload['current_hero_outside_top_three']) ? 1 : 0,
            ':selected_reason_codes' => $encodedReasonCodes,
            ':optional_note' => trim((string)($payload['optional_note'] ?? '')),
            ':criteria_refinement_signal' => !empty($payload['criteria_refinement_signal']) ? 1 : 0,
            ':image_set_limitation_signal' => !empty($payload['image_set_limitation_signal']) ? 1 : 0,
            ':metadata_issue_signal' => !empty($payload['metadata_issue_signal']) ? 1 : 0,
            ':diagnostics_issue_signal' => !empty($payload['diagnostics_issue_signal']) ? 1 : 0,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $rationaleId = (int)$pdo->lastInsertId();
        $pdo->commit();
        return $rationaleId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
