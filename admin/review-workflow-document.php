<?php
define('ADMIN_CONTEXT', true);

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';

$reviewWorkflows = [
    [
        'id' => 'ryderwear-batch-2',
        'name' => 'Ryderwear Batch 2',
        'batch_folder' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/',
        'documents' => [
            'human_reviewer_acceptance_worksheet.md',
            'proposed_reviewer_decisions.md',
            'approval_decision_readiness_review.md',
            'source_evidence_strategy.md',
        ],
    ],
];

$allowedDocuments = [];
foreach ($reviewWorkflows as $workflow) {
    if (empty($workflow['id']) || empty($workflow['batch_folder']) || empty($workflow['documents'])) {
        continue;
    }
    foreach ($workflow['documents'] as $document) {
        $allowedDocuments[$workflow['id']][$document] = [
            'workflow_name' => $workflow['name'],
            'relative_path' => $workflow['batch_folder'] . $document,
        ];
    }
}

$workflowId = (string)($_GET['workflow'] ?? '');
$documentId = (string)($_GET['doc'] ?? '');
$docMeta = $allowedDocuments[$workflowId][$documentId] ?? null;

$content = '';
$error = null;
$relativePath = '';
if ($docMeta) {
    $relativePath = $docMeta['relative_path'];
    $absolutePath = realpath(__DIR__ . '/../' . $relativePath);
    $repoRoot = realpath(__DIR__ . '/..');

    if (!$absolutePath || !$repoRoot || strpos($absolutePath, $repoRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($absolutePath)) {
        $error = 'Allowed document is currently unavailable on disk.';
    } else {
        $read = @file_get_contents($absolutePath);
        if ($read === false) {
            $error = 'Unable to read allowed document.';
        } else {
            $content = $read;
        }
    }
} else {
    $error = 'Document is not allowlisted for this workflow.';
}

admin_layout_start('Review Workflow Document');
admin_page_header('Review Workflow Document', 'Admin-only allowlisted workflow document viewer.');
?>
<div class="admin-wrapper">
    <section class="context-panel">
        <p><strong>Workflow:</strong> <?= htmlspecialchars($docMeta['workflow_name'] ?? 'Unknown') ?></p>
        <p><strong>Reference path:</strong> <code><?= htmlspecialchars($relativePath ?: ($workflowId . '/' . $documentId)) ?></code></p>
    </section>

    <section class="context-panel">
        <?php if ($error): ?>
            <p><strong>Unable to open document:</strong> <?= htmlspecialchars($error) ?></p>
        <?php else: ?>
            <pre style="white-space: pre-wrap; overflow-wrap: anywhere; margin: 0;"><?= htmlspecialchars($content) ?></pre>
        <?php endif; ?>
    </section>
</div>
<?php
admin_layout_end();
