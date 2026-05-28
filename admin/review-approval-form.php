<?php
define('ADMIN_CONTEXT', true);

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/image-helper.php';

$allowedWorkflow = [
    'id' => 'ryderwear-batch-2',
    'title' => 'Ryderwear Batch 2',
    'batch_folder' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/',
    'record_file' => 'human_reviewer_acceptance_record.json',
];

$allowedStatuses = [
    '' => 'Not selected',
    'accept_proposed' => 'accept_proposed',
    'revise_proposed' => 'revise_proposed',
    'reject_proposed' => 'reject_proposed',
    'defer_decision' => 'defer_decision',
];

$splitDecisions = [
    [
        'decision_id' => 'dec-001', 'itemId' => '156', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no',
        'decision_summary' => 'Split destination to avoid a direct path collision in group-1 entries.',
        'evidence_summary' => 'Proposed reviewer decisions describe a duplicate destination conflict that was split with an explicit discriminator.',
        'unresolved_risk' => 'Destination token grammar still needs a final taxonomy sanity check.',
        'recommended_action' => 'Accept if token grammar is canonical; otherwise revise split token ordering before final acceptance.',
        'proposed_reviewer_notes' => 'Proposed split appears internally consistent and avoids shared destination.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-001)',
    ],
    [
        'decision_id' => 'dec-002', 'itemId' => '157', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no',
        'decision_summary' => 'Keep split intent but revise discriminator formatting for collision group-2.',
        'evidence_summary' => 'Proposed table flags lift path collision with a lift-2-0 discriminator needing normalization.',
        'unresolved_risk' => 'Non-canonical token format could drift from naming standards.',
        'recommended_action' => 'Revise split path jointly with dec-003 so both rows follow one canonical token scheme.',
        'proposed_reviewer_notes' => 'Normalize token style against approved naming standard and paired dec-003.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-002 and dec-003)',
    ],
    [
        'decision_id' => 'dec-003', 'itemId' => '158', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no',
        'decision_summary' => 'Keep split outcome but normalize long discriminator grammar for pair consistency.',
        'evidence_summary' => 'Pairwise collision context with dec-002 calls out naming consistency risk for long token branches.',
        'unresolved_risk' => 'Long-token branch may not align with approved destination grammar.',
        'recommended_action' => 'Revise split token format and confirm pair consistency with dec-002 before final acceptance.',
        'proposed_reviewer_notes' => 'Maintain split outcome; request normalized discriminator format to avoid drift.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-002 and dec-003)',
    ],
    [
        'decision_id' => 'dec-004', 'itemId' => '159', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes',
        'decision_summary' => 'Approve split because cut semantics are explicit enough for separate ownership.',
        'evidence_summary' => 'Table notes low-support vs bandeau separation as a clear discriminator for NKD staples collision.',
        'unresolved_risk' => 'Residual risk is low if bandeau token is canonical.',
        'recommended_action' => 'Accept proposed split after confirming canonical cut token.',
        'proposed_reviewer_notes' => 'Split aligns with modeled cut semantics and collision structure.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-004)',
    ],
    [
        'decision_id' => 'dec-005', 'itemId' => '160', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes',
        'decision_summary' => 'Approve split in same group as dec-004 with one-shoulder branch discriminator.',
        'evidence_summary' => 'Evidence uses model descriptor to separate one-shoulder branch from competing row.',
        'unresolved_risk' => 'One-shoulder token must match canonical taxonomy.',
        'recommended_action' => 'Accept with same token-family check used for dec-004.',
        'proposed_reviewer_notes' => 'Approve split while keeping token consistency check in reviewer notes.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-005)',
    ],
    [
        'decision_id' => 'dec-006', 'itemId' => '161', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no',
        'decision_summary' => 'Defer ownership outcome until provenance evidence confirms core vs embody ownership.',
        'evidence_summary' => 'Group-4 entry explicitly flags competing ownership and insufficient provenance proof.',
        'unresolved_risk' => 'Incorrect ownership assignment without deterministic evidence.',
        'recommended_action' => 'Defer or revise pending deterministic provenance check against paired row.',
        'proposed_reviewer_notes' => 'Defer until reviewer confirms ownership evidence between competing model semantics.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-006 and dec-007)',
    ],
    [
        'decision_id' => 'dec-007', 'itemId' => '162', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes',
        'decision_summary' => 'Keep existing destination owner only if reviewer documents provenance basis.',
        'evidence_summary' => 'Paired group-4 analysis recommends retaining existing owner with explicit provenance confirmation.',
        'unresolved_risk' => 'Retention may be wrong if evidence favors competing row.',
        'recommended_action' => 'Accept keep-existing-owner only with explicit provenance note.',
        'proposed_reviewer_notes' => 'Keep existing owner, but require reviewer note citing provenance basis.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-006 and dec-007)',
    ],
    [
        'decision_id' => 'dec-008', 'itemId' => '163', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no',
        'decision_summary' => 'Revise split path after pairwise semantic check for knot vs twist attribution.',
        'evidence_summary' => 'Group-5 table indicates branch confusion risk if knot/twist semantics are swapped.',
        'unresolved_risk' => 'Possible inversion with paired dec-009 branch attribution.',
        'recommended_action' => 'Revise and confirm pair semantics jointly with dec-009.',
        'proposed_reviewer_notes' => 'Keep split strategy but require paired semantic confirmation and canonical token format.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-008 and dec-009)',
    ],
    [
        'decision_id' => 'dec-009', 'itemId' => '164', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes',
        'decision_summary' => 'Maintain split strategy while validating twist branch attribution against paired row.',
        'evidence_summary' => 'Evidence says duplicate destination is resolved only if pairwise attribution is correct.',
        'unresolved_risk' => 'Branch inversion risk remains without joint review with dec-008.',
        'recommended_action' => 'Revise after pair validation and token normalization.',
        'proposed_reviewer_notes' => 'Require semantic confirmation and canonical format before final acceptance.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-008 and dec-009)',
    ],
    [
        'decision_id' => 'dec-010', 'itemId' => '165', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no',
        'decision_summary' => 'Revise compound split token grammar for paired group-6 consistency.',
        'evidence_summary' => 'Proposed table identifies grammar normalization need for scrunch-v-halter branch.',
        'unresolved_risk' => 'Compound token may break deterministic naming without normalization.',
        'recommended_action' => 'Revise grammar and confirm branch disambiguation against dec-011.',
        'proposed_reviewer_notes' => 'Taxonomy check needed for compound token grammar and paired branch alignment.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-010 and dec-011)',
    ],
    [
        'decision_id' => 'dec-011', 'itemId' => '166', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes',
        'decision_summary' => 'Approve split branch; keep paired normalization note for dec-010.',
        'evidence_summary' => 'Underwire-keyhole discriminator is clearer and lower-risk than paired branch.',
        'unresolved_risk' => 'Low residual risk if paired logic remains consistent.',
        'recommended_action' => 'Accept while recording dependency on dec-010 token normalization.',
        'proposed_reviewer_notes' => 'Approve split; require paired grammar consistency note for dec-010.',
        'source_artifact' => 'proposed_reviewer_decisions.md §2 (split decisions table, dec-011)',
    ],
];

$item184 = [
    'itemId' => '184',
    'decision_id' => 'dec-012',
    'proposed_reviewer_decision' => 'approve selected source root',
    'defer_explanation' => 'This row remains deferred because source/provenance evidence is still missing.',
    'evidence_needed' => 'Reviewer-approved source root scope, deterministic asset provenance links, and ownership justification against competing row(s).',
    'approved_source_root_requirement' => 'No final approval until approved_source_root is explicitly confirmed by the human reviewer.',
    'competing_risk' => 'Competing ownership/provenance claims in collision group-7 can mis-assign destination ownership if approved early.',
    'source_artifact' => 'proposed_reviewer_decisions.md §3 (itemId 184 / dec-012 deferred source verification)',
];

$suspiciousCases = [
    [
        'case_id' => 'suspicious-01', 'key' => 'ryderwear_female_nkd_leggings_v_full_length_scrunch', 'proposed_reviewer_decision' => 'revise', 'confidence_level' => 'low', 'follow_up_required' => 'yes',
        'current_signal' => 'suspicious_mapping_manual_review_required; blocked in publication gate',
        'why_suspicious' => 'Path and filename semantics suggest campaign/banner intent rather than product imagery.',
        'unresolved_risk' => 'High risk of assigning marketing imagery to product image fields.',
        'recommended_action' => 'Keep blocked and request provenance evidence proving product-safe intent before any remap approval.',
        'source_artifact' => 'proposed_reviewer_decisions.md §4 (suspicious/remap table, row 1)',
    ],
    [
        'case_id' => 'suspicious-02', 'key' => 'ryderwear_unisex_gym_bag_accessories', 'proposed_reviewer_decision' => 'defer', 'confidence_level' => 'low', 'follow_up_required' => 'yes',
        'current_signal' => 'suspicious_mapping_manual_review_required; blocked; review_existing_images_present',
        'why_suspicious' => 'Existing plan marks this as intentionally excluded, so remap could bypass policy.',
        'unresolved_risk' => 'An accidental exception could introduce non-approved remap behavior.',
        'recommended_action' => 'Leave as no-change unless reviewer explicitly approves exception with source evidence.',
        'source_artifact' => 'proposed_reviewer_decisions.md §4 (suspicious/remap table, row 2)',
    ],
    [
        'case_id' => 'suspicious-03', 'key' => 'ryderwear_female_nkd_shorts_v_scrunch', 'proposed_reviewer_decision' => 'revise', 'confidence_level' => 'low', 'follow_up_required' => 'yes',
        'current_signal' => 'pending_human_approval with path-model mismatch note',
        'why_suspicious' => 'Model/path semantic mismatch may indicate wrong mapping target.',
        'unresolved_risk' => 'Destination may be wrong without model-to-path verification.',
        'recommended_action' => 'Request visual and metadata verification before approving remap or no-change.',
        'source_artifact' => 'proposed_reviewer_decisions.md §4 (suspicious/remap table, row 3)',
    ],
];

$batchPolicies = [
    ['policy_id' => 'approved_source_root_policy', 'label' => 'Approved source root policy', 'controls' => 'Defines allowed source root scope for this batch.', 'why_it_matters' => 'Without it, ownership/provenance decisions are non-deterministic.', 'proposed_decision' => 'defer_policy', 'approval_prereq' => 'Human reviewer must explicitly approve approved_source_root scope first.', 'source_artifact' => 'proposed_reviewer_decisions.md §5 (batch-level policies)'],
    ['policy_id' => 'deterministic_source_asset_id_policy', 'label' => 'Deterministic source_asset_id policy', 'controls' => 'Locks canonical rules for deterministic source_asset_id generation.', 'why_it_matters' => 'Prevents duplicate or drifting IDs across reruns.', 'proposed_decision' => 'revise_policy', 'approval_prereq' => 'Canonical format must be frozen and documented by reviewer.', 'source_artifact' => 'proposed_reviewer_decisions.md §5 (batch-level policies)'],
    ['policy_id' => 'checksum_bytes_mime_normalization_policy', 'label' => 'Checksum/bytes/mime normalization policy', 'controls' => 'Defines normalization pipeline for checksum, byte count, and mime fields.', 'why_it_matters' => 'Inconsistent normalization can produce invalid evidence and unstable artifact outputs.', 'proposed_decision' => 'revise_policy', 'approval_prereq' => 'Toolchain details and one-pass normalization behavior must be explicitly frozen.', 'source_artifact' => 'proposed_reviewer_decisions.md §5 (batch-level policies)'],
    ['policy_id' => 'provenance_note_policy', 'label' => 'Provenance_note policy', 'controls' => 'Specifies required provenance note structure for reviewer-auditable decisions.', 'why_it_matters' => 'Creates a traceable rationale for acceptance/revision decisions.', 'proposed_decision' => 'approve_policy', 'approval_prereq' => 'Reviewer confirms guidance remains aligned with source-root finalization guardrails.', 'source_artifact' => 'proposed_reviewer_decisions.md §5 (batch-level policies)'],
];


function parse_csv_assoc(string $relativePath): array {
    $abs = __DIR__ . '/../' . $relativePath;
    if (!is_file($abs) || !is_readable($abs)) {
        return [];
    }

    $rows = [];
    $fh = fopen($abs, 'rb');
    if ($fh === false) {
        return [];
    }

    $header = fgetcsv($fh);
    if (!is_array($header)) {
        fclose($fh);
        return [];
    }

    while (($data = fgetcsv($fh)) !== false) {
        $rows[] = array_combine($header, array_pad($data, count($header), '')) ?: [];
    }

    fclose($fh);
    return $rows;
}

function split_image_list(string $value): array {
    $parts = array_values(array_filter(array_map('trim', explode(';', $value)), static fn($p) => $p !== ''));
    return array_values(array_unique($parts));
}

function render_image_evidence(string $label, array $paths, string $fallback): void {
    echo '<div class="evidence-block"><div><strong>' . htmlspecialchars($label) . '</strong></div>';
    if (!$paths) {
        echo '<p class="context-note">' . htmlspecialchars($fallback) . '</p></div>';
        return;
    }

    echo '<div class="evidence-thumb-grid"><div class="evidence-thumb-row" style="--thumb-count:' . count($paths) . ';">';
    foreach ($paths as $path) {
        $isPreviewable = str_starts_with($path, 'images/') && admin_image_exists($path);
        $compactPath = basename($path);
        echo '<div class="evidence-thumb-card">';
        if ($isPreviewable) {
            echo admin_render_thumbnail_safe($path, $label . ' image', ['class' => 'evidence-thumb-image']);
        } else {
            echo '<div class="context-note">Image preview unavailable from allowlisted artifact data.</div>';
        }
        echo '<code class="reference-path" title="' . htmlspecialchars($path) . '">' . htmlspecialchars($compactPath) . '</code>';
        echo '</div>';
    }
    echo '</div></div></div>';
}

function render_image_evidence_comparison(array $currentPaths, array $candidatePaths, string $fallback): void {
    render_image_evidence('Current', $currentPaths, $fallback);
    render_image_evidence('Candidate / Proposed', $candidatePaths, $fallback);
    if ($currentPaths !== [] && $currentPaths === $candidatePaths) {
        echo '<p class="context-note evidence-identical-note"><strong>Current and candidate/proposed image sets appear identical in the available evidence.</strong></p>';
    }
}

$batchBase = $allowedWorkflow['batch_folder'];
$productRows = parse_csv_assoc($batchBase . 'product_identity_snapshot.csv');
$imageRows = parse_csv_assoc($batchBase . 'image_field_update_plan.csv');
$collisionRows = parse_csv_assoc($batchBase . 'destination_collision_report.csv');

$productByDbItemId = [];
$productByModelId = [];
foreach ($productRows as $row) {
    $dbItem = trim((string)($row['db_itemId'] ?? ''));
    $model = trim((string)($row['model_id'] ?? ''));
    if ($dbItem !== '') {
        $productByDbItemId[$dbItem] = $row;
    }
    if ($model !== '') {
        $productByModelId[$model] = $row;
    }
}

$imageByModelId = [];
foreach ($imageRows as $row) {
    $model = trim((string)($row['model_id'] ?? ''));
    if ($model !== '') {
        $imageByModelId[$model] = $row;
    }
}

$collisionByItemId = [];
$collisionByModelId = [];
foreach ($collisionRows as $row) {
    $item = trim((string)($row['candidate_itemId'] ?? ''));
    $model = trim((string)($row['candidate_model_id'] ?? ''));
    if ($item !== '') {
        $collisionByItemId[$item][] = $row;
    }
    if ($model !== '') {
        $collisionByModelId[$model][] = $row;
    }
}
$workflowId = (string)($_GET['workflow'] ?? $_POST['workflow_id'] ?? '');
$recordRelativePath = $allowedWorkflow['batch_folder'] . $allowedWorkflow['record_file'];
$recordAbsolutePath = __DIR__ . '/../' . $recordRelativePath;

$errors = [];
$successMessage = null;
$loadedRecord = [];

if ($workflowId !== $allowedWorkflow['id']) {
    http_response_code(400);
    admin_layout_start('Review Approval Form');
    admin_page_header('Review Approval Form', 'Unknown workflow id was rejected.');
    echo '<div class="admin-wrapper"><section class="context-panel"><p><strong>Error:</strong> Only allowlisted workflow <code>' . htmlspecialchars($allowedWorkflow['id']) . '</code> is supported.</p><p><a href="review-approvals.php">Back to Review Approvals</a></p></section></div>';
    admin_layout_end();
    exit;
}

if (is_file($recordAbsolutePath)) {
    $raw = file_get_contents($recordAbsolutePath);
    if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $loadedRecord = $decoded;
        }
    }
}

function posted_status(array $allowedStatuses, string $value): string {
    return array_key_exists($value, $allowedStatuses) ? $value : '';
}

function find_saved_row(array $savedRows, string $lookupKey, string $lookupValue): array {
    foreach ($savedRows as $savedRow) {
        if (($savedRow[$lookupKey] ?? '') === $lookupValue) {
            return $savedRow;
        }
    }

    return [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $existingRecord = is_file($recordAbsolutePath);
    $overwriteConfirmed = (string)($_POST['confirm_overwrite'] ?? '') === 'yes';

    if ($existingRecord && !$overwriteConfirmed) {
        $errors[] = 'A saved acceptance record already exists. Confirm overwrite to update this draft record.';
    }

    $splitOut = [];
    foreach ($splitDecisions as $decision) {
        $id = $decision['decision_id'];
        $splitOut[] = [
            'decision_id' => $id,
            'itemId' => $decision['itemId'],
            'proposed_reviewer_decision' => $decision['proposed_reviewer_decision'],
            'confidence_level' => $decision['confidence_level'],
            'follow_up_required' => $decision['follow_up_required'],
            'human_acceptance_status' => posted_status($allowedStatuses, (string)($_POST['split'][$id]['human_acceptance_status'] ?? '')),
            'human_final_decision' => trim((string)($_POST['split'][$id]['human_final_decision'] ?? '')),
            'human_reviewer_notes' => trim((string)($_POST['split'][$id]['human_reviewer_notes'] ?? '')),
        ];
    }

    $item184Out = [
        'itemId' => $item184['itemId'],
        'proposed_reviewer_decision' => $item184['proposed_reviewer_decision'],
        'approved_source_root' => trim((string)($_POST['item_184']['approved_source_root'] ?? '')),
        'human_acceptance_status' => posted_status($allowedStatuses, (string)($_POST['item_184']['human_acceptance_status'] ?? '')),
        'human_final_decision' => trim((string)($_POST['item_184']['human_final_decision'] ?? '')),
        'human_reviewer_notes' => trim((string)($_POST['item_184']['human_reviewer_notes'] ?? '')),
    ];

    $suspiciousOut = [];
    foreach ($suspiciousCases as $case) {
        $id = $case['case_id'];
        $suspiciousOut[] = [
            'case_id' => $id,
            'slug' => $case['key'],
            'proposed_reviewer_decision' => $case['proposed_reviewer_decision'],
            'confidence_level' => $case['confidence_level'],
            'follow_up_required' => $case['follow_up_required'],
            'human_acceptance_status' => posted_status($allowedStatuses, (string)($_POST['suspicious'][$id]['human_acceptance_status'] ?? '')),
            'human_final_decision' => trim((string)($_POST['suspicious'][$id]['human_final_decision'] ?? '')),
            'human_reviewer_notes' => trim((string)($_POST['suspicious'][$id]['human_reviewer_notes'] ?? '')),
        ];
    }

    $policyOut = [];
    foreach ($batchPolicies as $policy) {
        $id = $policy['policy_id'];
        $policyOut[] = [
            'policy_id' => $id,
            'human_acceptance_status' => posted_status($allowedStatuses, (string)($_POST['policy'][$id]['human_acceptance_status'] ?? '')),
            'human_final_decision' => trim((string)($_POST['policy'][$id]['human_final_decision'] ?? '')),
            'human_reviewer_notes' => trim((string)($_POST['policy'][$id]['human_reviewer_notes'] ?? '')),
        ];
    }

    if (!$errors) {
        $record = [
            'workflow_id' => $allowedWorkflow['id'],
            'workflow_title' => $allowedWorkflow['title'],
            'submitted_at' => gmdate('c'),
            'record_type' => 'human_reviewer_acceptance_record',
            'split_destination_decisions' => $splitOut,
            'item_184_decision' => $item184Out,
            'suspicious_remap_decisions' => $suspiciousOut,
            'batch_policy_decisions' => $policyOut,
            'downstream_artifacts_blocked' => true,
        ];

        $encoded = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || file_put_contents($recordAbsolutePath, $encoded . PHP_EOL, LOCK_EX) === false) {
            $errors[] = 'Failed to save acceptance record.';
        } else {
            $successMessage = 'Acceptance record saved as a draft/in-progress reviewer record.';
            $loadedRecord = $record;
        }
    }
}

admin_layout_start('Review Approval Form');
admin_page_header('Review Approval Form', 'Record human reviewer decisions for Ryderwear Batch 2.');
?>
<div class="admin-wrapper review-approval-page">
    <style>
        .review-approval-page{max-width:none;}
        .evidence-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin:10px 0;}
        .evidence-card{background:#f8fafc;border:1px solid #dbe4ee;border-radius:8px;padding:10px;}
        .evidence-thumb-grid{margin-top:6px;overflow-x:auto;padding-bottom:4px;}
        .evidence-thumb-row{display:grid;grid-template-columns:repeat(var(--thumb-count, 1), minmax(94px, 1fr));gap:8px;min-width:100%;}
        .evidence-thumb-card{border:1px solid #dbe4ee;border-radius:8px;padding:6px;background:#fff;min-width:94px;}
        .evidence-thumb-image{display:block;width:100%;height:auto;aspect-ratio:1/1;object-fit:cover;border-radius:4px;}
        .reference-path{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:#f4f4f5;padding:4px;border-radius:4px;margin-top:6px;font-size:12px;}
        .evidence-identical-note{margin-top:8px;color:#334155;}
        @media (max-width: 1100px){.reference-path{font-size:11px;}}
        @media (max-width: 820px){
            .evidence-thumb-row{min-width:max-content;}
            .evidence-thumb-card{width:100px;}
        }
    </style>
    <section class="context-panel">
        <p><strong>Workflow:</strong> <?= htmlspecialchars($allowedWorkflow['title']) ?> (<code><?= htmlspecialchars($allowedWorkflow['id']) ?></code>)</p>
        <p><strong>Warning:</strong> This form records human reviewer input only, keeps downstream artifacts blocked, and does not imply final approval.</p>
        <p><strong>Record path:</strong> <code><?= htmlspecialchars($recordRelativePath) ?></code></p>
        <p><strong>Read-only context fields:</strong> Decision summary, evidence, risk, recommendation, and source reference are explanatory only.</p>
        <p><a href="review-approvals.php">Back to Review Approvals</a></p>
    </section>

    <?php if ($successMessage): ?><section class="context-panel"><p><strong><?= htmlspecialchars($successMessage) ?></strong> Saved to <code><?= htmlspecialchars($recordRelativePath) ?></code>.</p></section><?php endif; ?>
    <?php if ($errors): ?><section class="context-panel"><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></section><?php endif; ?>

    <form method="post" action="review-approval-form.php?workflow=<?= rawurlencode($allowedWorkflow['id']) ?>">
        <input type="hidden" name="workflow_id" value="<?= htmlspecialchars($allowedWorkflow['id']) ?>">

        <section class="context-panel">
            <h2>Split-destination decisions (dec-001 to dec-011)</h2>
            <?php foreach ($splitDecisions as $row): $id = $row['decision_id']; $existing = find_saved_row($loadedRecord['split_destination_decisions'] ?? [], 'decision_id', $id); ?>
                <fieldset>
                    <legend><?= htmlspecialchars($id) ?> / itemId <?= htmlspecialchars($row['itemId']) ?></legend>
                    <p><strong>Read-only evidence context</strong></p>
                    <p>Proposed reviewer decision: <code><?= htmlspecialchars($row['proposed_reviewer_decision']) ?></code> · Confidence level: <code><?= htmlspecialchars($row['confidence_level']) ?></code> · Follow-up required: <code><?= htmlspecialchars($row['follow_up_required']) ?></code></p>
                    <p><strong>Decision summary:</strong> <?= htmlspecialchars($row['decision_summary']) ?></p>
                    <p><strong>Evidence summary:</strong> <?= htmlspecialchars($row['evidence_summary']) ?></p>
                    <p><strong>Unresolved risk:</strong> <?= htmlspecialchars($row['unresolved_risk']) ?></p>
                    <p><strong>Recommended reviewer action:</strong> <?= htmlspecialchars($row['recommended_action']) ?></p>
                    <p><strong>Proposed reviewer notes:</strong> <?= htmlspecialchars($row['proposed_reviewer_notes']) ?></p>
                    <p><strong>Source artifact reference:</strong> <code><?= htmlspecialchars($row['source_artifact']) ?></code></p>
                    <?php $itemId=(string)$row['itemId']; $collisions=$collisionByItemId[$itemId] ?? []; ?>
                    <div class="evidence-grid"><div class="evidence-card"><strong>Decision ownership note</strong><p class="context-note">This decision is destination ownership/split review; image evidence is supplemental.</p></div>
                    <?php if (!$collisions): ?><div class="evidence-card"><p class="context-note">No allowlisted collision row matched this itemId.</p></div><?php endif; ?>
                    <?php foreach ($collisions as $collision): $model=(string)($collision['candidate_model_id'] ?? ''); $prod=$productByModelId[$model] ?? []; $img=$imageByModelId[$model] ?? []; $current=split_image_list((string)($img['gallery_paths_json'] ?? '')); $primary=(string)($img['primary_image_path'] ?? ''); if ($primary !== '' && !in_array($primary,$current,true)) { array_unshift($current,$primary);} $notes=(string)($collision['notes'] ?? ''); $proposed=''; foreach (explode('|',$notes) as $part){ if (str_starts_with($part,'proposed_destination=')){ $proposed=substr($part,21);} } ?>
                        <div class="evidence-card"><p><strong>Item context</strong></p><p>itemId <code><?= htmlspecialchars($itemId) ?></code> · model <code><?= htmlspecialchars($model) ?></code></p><p><?= htmlspecialchars((string)($prod['itemName'] ?? 'Name unavailable from allowlisted artifact data.')) ?></p><p class="context-note">Brand: <?= htmlspecialchars((string)($prod['brand'] ?? '')) ?> · Category: <?= htmlspecialchars((string)($prod['categoryName'] ?? '')) ?> · Gender: <?= htmlspecialchars((string)($prod['gender'] ?? '')) ?></p><p class="context-note">Current destination: <code><?= htmlspecialchars((string)($collision['destination_path'] ?? '')) ?></code></p><p class="context-note">Proposed destination: <code><?= htmlspecialchars($proposed !== '' ? $proposed : 'Not provided') ?></code></p><?php render_image_evidence('Current', $current, 'Image preview unavailable from allowlisted artifact data. Use source artifact path/reference for manual verification.'); ?></div>
                    <?php endforeach; ?></div>
                    <hr>
                    <p><strong>Reviewer input required</strong></p>
                    <label>human_acceptance_status <select name="split[<?= htmlspecialchars($id) ?>][human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($existing['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
                    <label>human_final_decision <input type="text" name="split[<?= htmlspecialchars($id) ?>][human_final_decision]" value="<?= htmlspecialchars($existing['human_final_decision'] ?? '') ?>"></label>
                    <label>human_reviewer_notes <textarea name="split[<?= htmlspecialchars($id) ?>][human_reviewer_notes]"><?= htmlspecialchars($existing['human_reviewer_notes'] ?? '') ?></textarea></label>
                </fieldset>
            <?php endforeach; ?>
        </section>

        <section class="context-panel">
            <h2>itemId 184 / dec-012 deferred source verification</h2>
            <?php $itemSaved = $loadedRecord['item_184_decision'] ?? []; ?>
            <p><strong>Read-only evidence context</strong></p>
            <p><strong>Proposed decision:</strong> <code><?= htmlspecialchars($item184['proposed_reviewer_decision']) ?></code></p>
            <p><strong>Deferred status explanation:</strong> <?= htmlspecialchars($item184['defer_explanation']) ?></p>
            <p><strong>Evidence needed to move forward:</strong> <?= htmlspecialchars($item184['evidence_needed']) ?></p>
            <p><strong>approved_source_root requirement:</strong> <?= htmlspecialchars($item184['approved_source_root_requirement']) ?></p>
            <p><strong>Competing ownership/provenance risk:</strong> <?= htmlspecialchars($item184['competing_risk']) ?></p>
            <p><strong>Source artifact reference:</strong> <code><?= htmlspecialchars($item184['source_artifact']) ?></code></p><p><strong>Deferred: source/provenance evidence required</strong> · Do not approve from visual evidence alone.</p><?php $deferredCollisions=$collisionByItemId['184'] ?? []; foreach ($deferredCollisions as $collision): $model=(string)($collision['candidate_model_id'] ?? ''); $prod=$productByModelId[$model] ?? []; $img=$imageByModelId[$model] ?? []; ?><div class="evidence-card"><p><strong>Item context</strong></p><p>itemId <code>184</code> · model <code><?= htmlspecialchars($model) ?></code></p><p><?= htmlspecialchars((string)($prod['itemName'] ?? 'Name unavailable from allowlisted artifact data.')) ?></p><?php render_image_evidence('Needs verification', split_image_list((string)($img['gallery_paths_json'] ?? '')), 'Image preview unavailable from allowlisted artifact data. Use source artifact path/reference for manual verification.'); ?></div><?php endforeach; ?>
            <hr>
            <p><strong>Reviewer input required</strong></p>
            <label>approved_source_root <input type="text" name="item_184[approved_source_root]" value="<?= htmlspecialchars($itemSaved['approved_source_root'] ?? '') ?>"></label>
            <label>human_acceptance_status <select name="item_184[human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($itemSaved['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
            <label>human_final_decision <input type="text" name="item_184[human_final_decision]" value="<?= htmlspecialchars($itemSaved['human_final_decision'] ?? '') ?>"></label>
            <label>human_reviewer_notes <textarea name="item_184[human_reviewer_notes]"><?= htmlspecialchars($itemSaved['human_reviewer_notes'] ?? '') ?></textarea></label>
        </section>

        <section class="context-panel">
            <h2>Suspicious/remap cases</h2>
            <?php foreach ($suspiciousCases as $case): $id=$case['case_id']; $existing = find_saved_row($loadedRecord['suspicious_remap_decisions'] ?? [], 'case_id', $id); ?>
                <fieldset><legend><?= htmlspecialchars($id) ?> / <?= htmlspecialchars($case['key']) ?></legend>
                    <p><strong>Read-only evidence context</strong></p>
                    <p><strong>Current signal/status:</strong> <?= htmlspecialchars($case['current_signal']) ?></p>
                    <p><strong>Why suspicious:</strong> <?= htmlspecialchars($case['why_suspicious']) ?></p>
                    <p><strong>Unresolved risk:</strong> <?= htmlspecialchars($case['unresolved_risk']) ?></p>
                    <p><strong>Recommended reviewer action:</strong> <?= htmlspecialchars($case['recommended_action']) ?></p>
                    <p><strong>Proposed decision:</strong> <code><?= htmlspecialchars($case['proposed_reviewer_decision']) ?></code> · Confidence level: <code><?= htmlspecialchars($case['confidence_level']) ?></code> · Follow-up required: <code><?= htmlspecialchars($case['follow_up_required']) ?></code></p>
                    <p><strong>Source artifact reference:</strong> <code><?= htmlspecialchars($case['source_artifact']) ?></code></p><?php $slug=(string)$case['key']; $img=$imageByModelId[$slug] ?? []; $current=split_image_list((string)($img['gallery_paths_json'] ?? '')); $candidate=split_image_list((string)($img['planned_images'] ?? '')); render_image_evidence_comparison($current, $candidate, 'Image preview unavailable from allowlisted artifact data. Use source artifact path/reference for manual verification.'); ?>
                    <hr>
                    <p><strong>Reviewer input required</strong></p>
                    <label>human_acceptance_status <select name="suspicious[<?= htmlspecialchars($id) ?>][human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($existing['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
                    <label>human_final_decision <input type="text" name="suspicious[<?= htmlspecialchars($id) ?>][human_final_decision]" value="<?= htmlspecialchars($existing['human_final_decision'] ?? '') ?>"></label>
                    <label>human_reviewer_notes <textarea name="suspicious[<?= htmlspecialchars($id) ?>][human_reviewer_notes]"><?= htmlspecialchars($existing['human_reviewer_notes'] ?? '') ?></textarea></label>
                </fieldset>
            <?php endforeach; ?>
        </section>

        <section class="context-panel"><h2>Batch-level policy decisions</h2>
            <?php foreach ($batchPolicies as $policy): $id=$policy['policy_id']; $existing = find_saved_row($loadedRecord['batch_policy_decisions'] ?? [], 'policy_id', $id); ?>
                <fieldset><legend><?= htmlspecialchars($policy['label']) ?></legend>
                    <p><strong>Read-only evidence context</strong></p>
                    <p><strong>What this policy controls:</strong> <?= htmlspecialchars($policy['controls']) ?></p>
                    <p><strong>Why it matters:</strong> <?= htmlspecialchars($policy['why_it_matters']) ?></p>
                    <p><strong>Proposed decision:</strong> <code><?= htmlspecialchars($policy['proposed_decision']) ?></code></p>
                    <p><strong>What must be true before approval:</strong> <?= htmlspecialchars($policy['approval_prereq']) ?></p>
                    <p><strong>Source artifact reference:</strong> <code><?= htmlspecialchars($policy['source_artifact']) ?></code></p>
                    <hr>
                    <p><strong>Reviewer input required</strong></p>
                    <label>human_acceptance_status <select name="policy[<?= htmlspecialchars($id) ?>][human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($existing['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
                    <label>human_final_decision <input type="text" name="policy[<?= htmlspecialchars($id) ?>][human_final_decision]" value="<?= htmlspecialchars($existing['human_final_decision'] ?? '') ?>"></label>
                    <label>human_reviewer_notes <textarea name="policy[<?= htmlspecialchars($id) ?>][human_reviewer_notes]"><?= htmlspecialchars($existing['human_reviewer_notes'] ?? '') ?></textarea></label>
                </fieldset>
            <?php endforeach; ?>
            <?php if (is_file($recordAbsolutePath)): ?>
                <label><input type="checkbox" name="confirm_overwrite" value="yes"> Confirm overwrite existing saved draft acceptance record</label>
            <?php endif; ?>
            <p><button type="submit">Save acceptance record</button></p>
        </section>
    </form>
</div>
<?php admin_layout_end();
