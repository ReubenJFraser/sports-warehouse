<?php
define('ADMIN_CONTEXT', true);

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';

$reviewWorkflowStatuses = [
    'preparing' => 'Preparing',
    'awaiting_human_acceptance' => 'Awaiting human reviewer acceptance',
    'accepted' => 'Accepted',
    'blocked' => 'Blocked',
    'archived' => 'Archived',
];

$reviewWorkflows = [
    [
        'id' => 'ryderwear-batch-2',
        'name' => 'Ryderwear Batch 2',
        'status' => 'awaiting_human_acceptance',
        'batch_folder' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/',
        'primary_worksheet' => 'human_reviewer_acceptance_worksheet.md',
        'acceptance_record' => 'human_reviewer_acceptance_record.json',
        'documents' => [
            'human_reviewer_acceptance_worksheet.md',
            'proposed_reviewer_decisions.md',
            'approval_decision_readiness_review.md',
            'source_evidence_strategy.md',
            'human_reviewer_acceptance_record.json',
        ],
        'is_current' => true,
    ],
    [
        'id' => 'future-batch-review',
        'name' => 'Future batch review',
        'status' => 'not_configured',
        'is_current' => false,
    ],
    [
        'id' => 'other-review-workflow',
        'name' => 'Other review workflow',
        'status' => 'not_configured',
        'is_current' => false,
    ],
];

$workflowLinks = [];
foreach ($reviewWorkflows as $workflow) {
    if (empty($workflow['batch_folder']) || empty($workflow['documents']) || empty($workflow['id'])) {
        continue;
    }

    foreach ($workflow['documents'] as $document) {
        $workflowLinks[$workflow['id']][$document] = 'review-workflow-document.php?workflow=' . rawurlencode($workflow['id']) . '&doc=' . rawurlencode($document);
    }
}

$currentWorkflow = null;
foreach ($reviewWorkflows as $workflow) {
    if (!empty($workflow['is_current'])) {
        $currentWorkflow = $workflow;
        break;
    }
}

admin_layout_start('Review Approvals');
admin_page_header('Review Approvals', 'Track human reviewer acceptance workflows before downstream artifacts are generated.');
?>

<div class="admin-wrapper">
    <section class="context-panel">
        <p><strong>Review approval workflows</strong> are used after preliminary evidence, readiness, and proposed-decision documents have been prepared.</p>
        <p>A workflow becomes ready for human review when it has a human reviewer acceptance worksheet.</p>
        <p>Downstream artifacts remain blocked until the reviewer accepts, revises, rejects, or defers the decisions.</p>
    </section>

    <section class="context-panel">
        <p><strong>Current review workflow</strong></p>
        <select aria-label="Current review workflow" disabled>
            <?php foreach ($reviewWorkflows as $workflow): ?>
                <?php
                $isCurrent = !empty($workflow['is_current']);
                $isConfigured = isset($workflow['status']) && $workflow['status'] !== 'not_configured';
                $optionLabel = $workflow['name'] . ' - ';
                $optionLabel .= $isConfigured
                    ? ($reviewWorkflowStatuses[$workflow['status']] ?? ucfirst(str_replace('_', ' ', $workflow['status'])))
                    : 'Not configured';
                ?>
                <option <?= $isCurrent ? 'selected' : '' ?> <?= $isConfigured ? '' : 'disabled' ?>>
                    <?= htmlspecialchars($optionLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="context-note">Workflow selection is explicitly tracked in this page configuration; active workflow is not inferred from folder scan or last-modified time.</p>
        <p class="context-note">Only one workflow is currently configured.</p>
    </section>

    <?php if ($currentWorkflow): ?>
        <section class="context-panel">
            <p><strong>Active workflow:</strong> <?= htmlspecialchars($currentWorkflow['name']) ?></p>
            <?php
            $primaryDoc = $currentWorkflow['primary_worksheet'] ?? '';
            $primaryDocHref = $workflowLinks[$currentWorkflow['id']][$primaryDoc] ?? null;
            ?>
            <p><strong>Status:</strong> <span class="badge badge-accent"><?= htmlspecialchars($reviewWorkflowStatuses[$currentWorkflow['status']] ?? $currentWorkflow['status']) ?></span></p>
            <p><strong>Batch folder:</strong> <code><?= htmlspecialchars($currentWorkflow['batch_folder']) ?></code> <span class="context-note">(Reference path)</span></p>
            <p><strong>Primary reviewer worksheet:</strong>
                <?php if ($primaryDocHref): ?>
                    <a href="<?= htmlspecialchars($primaryDocHref) ?>"><code><?= htmlspecialchars($primaryDoc) ?></code></a>
                    <span aria-hidden="true">·</span>
                    <a href="<?= htmlspecialchars($primaryDocHref) ?>">Open acceptance worksheet</a>
                <?php else: ?>
                    <code><?= htmlspecialchars($primaryDoc) ?></code>
                <?php endif; ?>
            </p>


            <?php
            $fillableHref = 'review-approval-form.php?workflow=' . rawurlencode($currentWorkflow['id']);
            $recordFile = $currentWorkflow['acceptance_record'] ?? '';
            $recordRelativePath = ($currentWorkflow['batch_folder'] ?? '') . $recordFile;
            $recordAbsolutePath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $recordRelativePath);
            $recordExists = $recordFile !== '' && is_file($recordAbsolutePath);
            $recordHref = $recordExists ? ($workflowLinks[$currentWorkflow['id']][$recordFile] ?? ('review-workflow-document.php?workflow=' . rawurlencode($currentWorkflow['id']) . '&doc=' . rawurlencode($recordFile))) : null;
            $recordStateLabel = 'Saved draft/incomplete record';
            if ($recordExists) {
                $recordRaw = file_get_contents($recordAbsolutePath);
                $recordDecoded = is_string($recordRaw) ? json_decode($recordRaw, true) : null;
                if (is_array($recordDecoded)) {
                    $sections = ['split_destination_decisions', 'item_184_decision', 'suspicious_remap_decisions', 'batch_policy_decisions'];
                    $hasAnyHumanInput = false;
                    foreach ($sections as $sectionKey) {
                        $sectionValue = $recordDecoded[$sectionKey] ?? [];
                        if (is_array($sectionValue)) {
                            $rows = isset($sectionValue['human_acceptance_status']) ? [$sectionValue] : $sectionValue;
                            foreach ($rows as $row) {
                                if (!is_array($row)) {
                                    continue;
                                }
                                if (($row['human_acceptance_status'] ?? '') !== '' || trim((string)($row['human_final_decision'] ?? '')) !== '' || trim((string)($row['human_reviewer_notes'] ?? '')) !== '') {
                                    $hasAnyHumanInput = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if ($hasAnyHumanInput) {
                        $recordStateLabel = 'Saved acceptance record (in progress; not final approval)';
                    }
                }
            }
            ?>
            <p><a href="<?= htmlspecialchars($primaryDocHref ?? '#') ?>">View worksheet</a> <span aria-hidden="true">·</span> <a href="<?= htmlspecialchars($fillableHref) ?>">Fill acceptance form</a></p>
            <p class="context-note">View worksheet opens the read-only worksheet document. Fill acceptance form opens the admin form for recording reviewer decisions.</p>
            <?php if ($recordExists): ?>
                <p><strong><?= htmlspecialchars($recordStateLabel) ?></strong></p>
                <p><a href="<?= htmlspecialchars($recordHref) ?>">View saved acceptance record</a></p>
            <?php endif; ?>

            <p><strong>Open workflow documents:</strong></p>
            <ul>
                <?php foreach ($currentWorkflow['documents'] as $document): ?>
                    <?php $docHref = $workflowLinks[$currentWorkflow['id']][$document] ?? null; ?>
                    <li>
                        <?php if ($docHref): ?>
                            <a href="<?= htmlspecialchars($docHref) ?>"><code><?= htmlspecialchars($currentWorkflow['batch_folder'] . $document) ?></code></a>
                        <?php else: ?>
                            <code><?= htmlspecialchars($currentWorkflow['batch_folder'] . $document) ?></code>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="context-note">Only workflow-registered documents are linked here to avoid unrestricted filesystem browsing.</p>
        </section>
    <?php endif; ?>

    <section class="context-panel">
        <p><strong>Guardrail:</strong> Blocked downstream artifacts remain blocked until human acceptance and policy/source-root decisions are recorded:</p>
        <ul>
            <li><code>source_asset_inventory.csv</code></li>
            <li><code>suspicious_mapping_report.csv</code></li>
            <li><code>copy_simulation.csv</code></li>
        </ul>
    </section>
</div>

<?php
admin_layout_end();
