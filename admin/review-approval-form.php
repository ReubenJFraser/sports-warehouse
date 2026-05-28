<?php
define('ADMIN_CONTEXT', true);

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';

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
    ['decision_id' => 'dec-001', 'itemId' => '156', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no'],
    ['decision_id' => 'dec-002', 'itemId' => '157', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no'],
    ['decision_id' => 'dec-003', 'itemId' => '158', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no'],
    ['decision_id' => 'dec-004', 'itemId' => '159', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes'],
    ['decision_id' => 'dec-005', 'itemId' => '160', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes'],
    ['decision_id' => 'dec-006', 'itemId' => '161', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no'],
    ['decision_id' => 'dec-007', 'itemId' => '162', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes'],
    ['decision_id' => 'dec-008', 'itemId' => '163', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no'],
    ['decision_id' => 'dec-009', 'itemId' => '164', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes'],
    ['decision_id' => 'dec-010', 'itemId' => '165', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'high', 'follow_up_required' => 'no'],
    ['decision_id' => 'dec-011', 'itemId' => '166', 'proposed_reviewer_decision' => 'accept', 'confidence_level' => 'medium', 'follow_up_required' => 'yes'],
];

$item184 = ['itemId' => '184', 'proposed_reviewer_decision' => 'approve selected source root'];

$suspiciousCases = [
    ['case_id' => 'suspicious-01', 'slug' => 'ryderwear_female_nkd_leggings_v_full_length_scrunch', 'proposed_reviewer_decision' => 'revise', 'confidence_level' => 'low', 'follow_up_required' => 'yes'],
    ['case_id' => 'suspicious-02', 'slug' => 'ryderwear_unisex_gym_bag_accessories', 'proposed_reviewer_decision' => 'defer', 'confidence_level' => 'low', 'follow_up_required' => 'yes'],
    ['case_id' => 'suspicious-03', 'slug' => 'ryderwear_female_nkd_shorts_v_scrunch', 'proposed_reviewer_decision' => 'revise', 'confidence_level' => 'low', 'follow_up_required' => 'yes'],
];

$batchPolicies = [
    ['policy_id' => 'approved_source_root_policy', 'label' => 'Approved source root policy'],
    ['policy_id' => 'deterministic_source_asset_id_policy', 'label' => 'Deterministic source_asset_id policy'],
    ['policy_id' => 'checksum_bytes_mime_normalization_policy', 'label' => 'Checksum/bytes/mime normalization policy'],
    ['policy_id' => 'provenance_note_policy', 'label' => 'Provenance_note policy'],
];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $existingRecord = is_file($recordAbsolutePath);
    $overwriteConfirmed = (string)($_POST['confirm_overwrite'] ?? '') === 'yes';

    if ($existingRecord && !$overwriteConfirmed) {
        $errors[] = 'An acceptance record already exists. Confirm overwrite to update the saved record.';
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
            'slug' => $case['slug'],
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
            $successMessage = 'Acceptance record saved.';
            $loadedRecord = $record;
        }
    }
}

admin_layout_start('Review Approval Form');
admin_page_header('Review Approval Form', 'Record human reviewer decisions for Ryderwear Batch 2.');
?>
<div class="admin-wrapper">
    <section class="context-panel">
        <p><strong>Workflow:</strong> <?= htmlspecialchars($allowedWorkflow['title']) ?> (<code><?= htmlspecialchars($allowedWorkflow['id']) ?></code>)</p>
        <p><strong>Warning:</strong> This form records human reviewer input only and does not generate downstream artifacts.</p>
        <p><strong>Record path:</strong> <code><?= htmlspecialchars($recordRelativePath) ?></code></p>
        <p><a href="review-approvals.php">Back to Review Approvals</a></p>
    </section>

    <?php if ($successMessage): ?><section class="context-panel"><p><strong><?= htmlspecialchars($successMessage) ?></strong> Saved to <code><?= htmlspecialchars($recordRelativePath) ?></code>.</p></section><?php endif; ?>
    <?php if ($errors): ?><section class="context-panel"><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></section><?php endif; ?>

    <form method="post" action="review-approval-form.php?workflow=<?= rawurlencode($allowedWorkflow['id']) ?>">
        <input type="hidden" name="workflow_id" value="<?= htmlspecialchars($allowedWorkflow['id']) ?>">

        <section class="context-panel">
            <h2>Split-destination decisions (dec-001 to dec-011)</h2>
            <?php foreach ($splitDecisions as $row): $id = $row['decision_id']; $saved = $loadedRecord['split_destination_decisions'] ?? []; $existing=[]; foreach($saved as $sv){if(($sv['decision_id']??'')===$id){$existing=$sv; break;}} ?>
                <fieldset><legend><?= htmlspecialchars($id) ?> / itemId <?= htmlspecialchars($row['itemId']) ?></legend>
                    <p>Proposed: <code><?= htmlspecialchars($row['proposed_reviewer_decision']) ?></code> · Confidence: <code><?= htmlspecialchars($row['confidence_level']) ?></code> · Follow-up required: <code><?= htmlspecialchars($row['follow_up_required']) ?></code></p>
                    <label>human_acceptance_status <select name="split[<?= htmlspecialchars($id) ?>][human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($existing['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
                    <label>human_final_decision <input type="text" name="split[<?= htmlspecialchars($id) ?>][human_final_decision]" value="<?= htmlspecialchars($existing['human_final_decision'] ?? '') ?>"></label>
                    <label>human_reviewer_notes <textarea name="split[<?= htmlspecialchars($id) ?>][human_reviewer_notes]"><?= htmlspecialchars($existing['human_reviewer_notes'] ?? '') ?></textarea></label>
                </fieldset>
            <?php endforeach; ?>
        </section>

        <section class="context-panel"><h2>ItemId 184 decision</h2>
            <?php $itemSaved = $loadedRecord['item_184_decision'] ?? []; ?>
            <p>Proposed decision: <code><?= htmlspecialchars($item184['proposed_reviewer_decision']) ?></code></p>
            <label>approved_source_root <input type="text" name="item_184[approved_source_root]" value="<?= htmlspecialchars($itemSaved['approved_source_root'] ?? '') ?>"></label>
            <label>human_acceptance_status <select name="item_184[human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($itemSaved['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
            <label>human_final_decision <input type="text" name="item_184[human_final_decision]" value="<?= htmlspecialchars($itemSaved['human_final_decision'] ?? '') ?>"></label>
            <label>human_reviewer_notes <textarea name="item_184[human_reviewer_notes]"><?= htmlspecialchars($itemSaved['human_reviewer_notes'] ?? '') ?></textarea></label>
        </section>

        <section class="context-panel"><h2>Suspicious/remap cases</h2>
            <?php foreach ($suspiciousCases as $case): $id=$case['case_id']; $saved = $loadedRecord['suspicious_remap_decisions'] ?? []; $existing=[]; foreach($saved as $sv){if(($sv['case_id']??'')===$id){$existing=$sv; break;}} ?>
                <fieldset><legend><?= htmlspecialchars($id) ?> / <?= htmlspecialchars($case['slug']) ?></legend>
                    <p>Proposed: <code><?= htmlspecialchars($case['proposed_reviewer_decision']) ?></code> · Confidence: <code><?= htmlspecialchars($case['confidence_level']) ?></code> · Follow-up required: <code><?= htmlspecialchars($case['follow_up_required']) ?></code></p>
                    <label>human_acceptance_status <select name="suspicious[<?= htmlspecialchars($id) ?>][human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($existing['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
                    <label>human_final_decision <input type="text" name="suspicious[<?= htmlspecialchars($id) ?>][human_final_decision]" value="<?= htmlspecialchars($existing['human_final_decision'] ?? '') ?>"></label>
                    <label>human_reviewer_notes <textarea name="suspicious[<?= htmlspecialchars($id) ?>][human_reviewer_notes]"><?= htmlspecialchars($existing['human_reviewer_notes'] ?? '') ?></textarea></label>
                </fieldset>
            <?php endforeach; ?>
        </section>

        <section class="context-panel"><h2>Batch-level policy decisions</h2>
            <?php foreach ($batchPolicies as $policy): $id=$policy['policy_id']; $saved = $loadedRecord['batch_policy_decisions'] ?? []; $existing=[]; foreach($saved as $sv){if(($sv['policy_id']??'')===$id){$existing=$sv; break;}} ?>
                <fieldset><legend><?= htmlspecialchars($policy['label']) ?></legend>
                    <label>human_acceptance_status <select name="policy[<?= htmlspecialchars($id) ?>][human_acceptance_status]"><?php foreach($allowedStatuses as $k=>$v): ?><option value="<?= htmlspecialchars($k) ?>" <?= (($existing['human_acceptance_status'] ?? '') === $k) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option><?php endforeach; ?></select></label>
                    <label>human_final_decision <input type="text" name="policy[<?= htmlspecialchars($id) ?>][human_final_decision]" value="<?= htmlspecialchars($existing['human_final_decision'] ?? '') ?>"></label>
                    <label>human_reviewer_notes <textarea name="policy[<?= htmlspecialchars($id) ?>][human_reviewer_notes]"><?= htmlspecialchars($existing['human_reviewer_notes'] ?? '') ?></textarea></label>
                </fieldset>
            <?php endforeach; ?>
            <?php if (is_file($recordAbsolutePath)): ?>
                <label><input type="checkbox" name="confirm_overwrite" value="yes"> Confirm overwrite existing acceptance record</label>
            <?php endif; ?>
            <p><button type="submit">Save acceptance record</button></p>
        </section>
    </form>
</div>
<?php admin_layout_end();
