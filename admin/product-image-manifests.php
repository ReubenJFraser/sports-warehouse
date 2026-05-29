<?php
define('ADMIN_CONTEXT', true);

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/image-helper.php';

$manifestRegistry = [
    'ryderwear-batch2-v2' => [
        'key' => 'ryderwear-batch2-v2',
        'title' => 'Ryderwear Batch 2 candidate manifest v2',
        'manifest_path' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2.json',
        'flat_csv_path' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2_flat.csv',
        'gate_report_path' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_manifest_v2_gate_report.md',
        'manifest_report_path' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2_report.md',
        'flat_report_path' => 'docs/operations/generated/batches/ryderwear-batch2-2026-05-27-01/ryderwear_batch2_2026_05_27_candidate_product_image_set_manifest_v2_flat_report.md',
        'status' => 'active_candidate',
        'downstream_artifacts_unblocked' => 'no',
        'gate_conclusion' => 'manifest_v2_and_flat_mirror_review_gate_passed',
        'document_workflow' => 'ryderwear-batch-2',
    ],
];

function manifest_viewer_h($value): string
{
    if ($value === null) {
        return '';
    }

    if (is_bool($value)) {
        return $value ? 'yes' : 'no';
    }

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function manifest_viewer_repo_path(string $relativePath): ?string
{
    $repoRoot = realpath(__DIR__ . '/..');
    if ($repoRoot === false || $relativePath === '' || str_starts_with($relativePath, '/') || str_contains($relativePath, "\0")) {
        return null;
    }

    $absolutePath = realpath($repoRoot . DIRECTORY_SEPARATOR . $relativePath);
    if ($absolutePath === false || strpos($absolutePath, $repoRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($absolutePath) || !is_readable($absolutePath)) {
        return null;
    }

    return $absolutePath;
}

function manifest_viewer_read_json(string $relativePath, ?string &$error): array
{
    $absolutePath = manifest_viewer_repo_path($relativePath);
    if ($absolutePath === null) {
        $error = 'Allowlisted manifest file is unavailable.';
        return [];
    }

    $contents = file_get_contents($absolutePath);
    if ($contents === false) {
        $error = 'Unable to read allowlisted manifest file.';
        return [];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        $error = 'Allowlisted manifest JSON could not be decoded.';
        return [];
    }

    return $decoded;
}

function manifest_viewer_read_csv(string $relativePath, ?string &$error): array
{
    $absolutePath = manifest_viewer_repo_path($relativePath);
    if ($absolutePath === null) {
        $error = 'Allowlisted flat CSV mirror is unavailable.';
        return [];
    }

    $handle = fopen($absolutePath, 'rb');
    if ($handle === false) {
        $error = 'Unable to read allowlisted flat CSV mirror.';
        return [];
    }

    $headers = fgetcsv($handle);
    if (!is_array($headers)) {
        fclose($handle);
        $error = 'Allowlisted flat CSV mirror has no header row.';
        return [];
    }

    $rows = [];
    while (($row = fgetcsv($handle)) !== false) {
        $assoc = [];
        foreach ($headers as $index => $header) {
            $assoc[(string)$header] = $row[$index] ?? '';
        }
        $rows[] = $assoc;
    }
    fclose($handle);

    return ['headers' => $headers, 'rows' => $rows];
}

function manifest_viewer_gate_conclusion(string $relativePath, string $fallback): string
{
    $absolutePath = manifest_viewer_repo_path($relativePath);
    if ($absolutePath === null) {
        return $fallback;
    }

    $contents = file_get_contents($absolutePath);
    if ($contents === false) {
        return $fallback;
    }

    if (preg_match('/manifest_v2_and_flat_mirror_review_gate_passed/', $contents, $matches)) {
        return $matches[0];
    }

    return $fallback;
}

function manifest_viewer_image_count(array $products): int
{
    $count = 0;
    foreach ($products as $product) {
        $count += count(is_array($product['images'] ?? null) ? $product['images'] : []);
    }
    return $count;
}

function manifest_viewer_flat_csv_validation(array $csvData, int $manifestImageCount): array
{
    $headers = $csvData['headers'] ?? [];
    $rows = $csvData['rows'] ?? [];

    $deliveryCountZero = true;
    $deliveryStatusNotGenerated = true;
    foreach ($rows as $row) {
        if ((string)($row['delivery_count'] ?? '') !== '0') {
            $deliveryCountZero = false;
        }
        if ((string)($row['delivery_status'] ?? '') !== 'delivery_not_generated') {
            $deliveryStatusNotGenerated = false;
        }
    }

    return [
        'row_count' => count($rows),
        'row_count_matches_manifest' => count($rows) === $manifestImageCount,
        'product_key_absent' => !in_array('product_key', $headers, true),
        'external_item_id_absent' => !in_array('external_item_id', $headers, true),
        'delivery_count_zero' => $deliveryCountZero,
        'delivery_status_not_generated' => $deliveryStatusNotGenerated,
    ];
}

function manifest_viewer_safe_thumbnail(string $sourceRootScope, string $sourceRelpath, string $alt): string
{
    $sourceRelpath = trim(str_replace('\\', '/', $sourceRelpath));
    $sourceRootScope = trim(str_replace('\\', '/', $sourceRootScope), '/');
    $publicPath = $sourceRootScope . '/' . ltrim($sourceRelpath, '/');

    if ($sourceRelpath === '' || str_starts_with($sourceRelpath, '/') || preg_match('#(^|/)\.\.(/|$)#', $sourceRelpath)) {
        return '<span class="manifest-viewer-thumb-fallback">preview unavailable</span>';
    }

    $repoRoot = realpath(__DIR__ . '/..');
    $sourceRoot = $repoRoot ? realpath($repoRoot . DIRECTORY_SEPARATOR . $sourceRootScope) : false;
    $imagePath = ($repoRoot && $sourceRoot) ? realpath($repoRoot . DIRECTORY_SEPARATOR . $publicPath) : false;

    if (!$sourceRoot || !$imagePath || strpos($imagePath, $sourceRoot . DIRECTORY_SEPARATOR) !== 0 || !admin_image_exists($publicPath)) {
        return '<span class="manifest-viewer-thumb-fallback">preview unavailable</span>';
    }

    return admin_render_thumbnail($publicPath, $alt, ['class' => 'manifest-viewer-thumb']);
}

function manifest_viewer_document_link(array $package, string $pathKey, string $label): string
{
    $doc = basename((string)($package[$pathKey] ?? ''));
    $href = 'review-workflow-document.php?workflow=' . rawurlencode((string)$package['document_workflow']) . '&doc=' . rawurlencode($doc);
    return '<a class="manifest-viewer-doc-link" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
}

$manifestKey = (string)($_GET['manifest'] ?? 'ryderwear-batch2-v2');
$package = $manifestRegistry[$manifestKey] ?? null;
$error = null;
$manifest = [];
$csvData = ['headers' => [], 'rows' => []];
$gateConclusion = 'manifest_v2_and_flat_mirror_review_gate_passed';

if ($package === null) {
    $error = 'Unknown manifest key. This page only supports allowlisted manifest packages.';
} else {
    $manifestError = null;
    $csvError = null;
    $manifest = manifest_viewer_read_json($package['manifest_path'], $manifestError);
    $csvData = manifest_viewer_read_csv($package['flat_csv_path'], $csvError);
    $gateConclusion = manifest_viewer_gate_conclusion($package['gate_report_path'], $package['gate_conclusion']);
    $error = $manifestError ?: $csvError;
}

$products = is_array($manifest['products'] ?? null) ? $manifest['products'] : [];
$sourceRoot = is_array($manifest['source_roots'][0] ?? null) ? $manifest['source_roots'][0] : [];
$sourceRootScope = (string)($sourceRoot['root_scope'] ?? 'images/brands/ryderwear');
$imageRowCount = manifest_viewer_image_count($products);
$flatValidation = manifest_viewer_flat_csv_validation($csvData, $imageRowCount);

admin_layout_start('Product Image Manifests');
admin_page_header('Product Image Manifests', 'Read-only inspection of allowlisted product image manifest packages.');
?>
<style>
.admin-manifest-viewer-page .manifest-viewer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;margin:12px 0;}
.admin-manifest-viewer-page .manifest-viewer-card{border:1px solid rgba(148,163,184,.2);border-radius:10px;padding:10px;background:rgba(15,23,42,.45);}
.admin-manifest-viewer-page .manifest-viewer-label{display:block;color:var(--text-faint);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;}
.admin-manifest-viewer-page .manifest-viewer-value{color:var(--text-main);overflow-wrap:anywhere;}
.admin-manifest-viewer-page .manifest-viewer-source-truth{border-color:rgba(34,197,94,.45);background:rgba(22,101,52,.18);}
.admin-manifest-viewer-page .manifest-viewer-products{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;}
.admin-manifest-viewer-page .manifest-viewer-products dl{display:grid;grid-template-columns:130px 1fr;gap:5px 10px;margin:0;}
.admin-manifest-viewer-page .manifest-viewer-products dt{color:var(--text-faint);}
.admin-manifest-viewer-page .manifest-viewer-products dd{margin:0;color:var(--text-main);overflow-wrap:anywhere;}
.admin-manifest-viewer-page .manifest-viewer-table-wrap{overflow:auto;border:1px solid rgba(148,163,184,.2);border-radius:12px;margin-top:10px;}
.admin-manifest-viewer-page .manifest-viewer-table{width:100%;min-width:1280px;border-collapse:collapse;}
.admin-manifest-viewer-page .manifest-viewer-table th,.admin-manifest-viewer-page .manifest-viewer-table td{padding:8px;border-bottom:1px solid rgba(148,163,184,.16);text-align:left;vertical-align:top;font-size:.78rem;}
.admin-manifest-viewer-page .manifest-viewer-table th{background:rgba(15,23,42,.72);color:#d0dbff;position:sticky;top:0;}
.admin-manifest-viewer-page .manifest-viewer-thumb{width:72px;height:96px;object-fit:cover;border-radius:8px;border:1px solid rgba(148,163,184,.25);}
.admin-manifest-viewer-page .manifest-viewer-thumb-fallback{display:inline-flex;width:72px;min-height:54px;align-items:center;justify-content:center;text-align:center;border:1px dashed rgba(148,163,184,.35);border-radius:8px;color:var(--text-faint);font-size:.72rem;padding:5px;}
.admin-manifest-viewer-page .manifest-viewer-doc-links{display:flex;flex-wrap:wrap;gap:8px;}
.admin-manifest-viewer-page .manifest-viewer-doc-link{display:inline-flex;border:1px solid rgba(96,165,250,.35);border-radius:999px;padding:6px 10px;text-decoration:none;color:#bfdbfe;background:rgba(30,64,175,.16);}
.admin-manifest-viewer-page .manifest-viewer-status-ok{color:#86efac;}
.admin-manifest-viewer-page .manifest-viewer-status-blocked{color:#fca5a5;}
.admin-manifest-viewer-page code{white-space:normal;overflow-wrap:anywhere;}
</style>

<div class="admin-wrapper admin-manifest-viewer-page">
    <?php if ($error): ?>
        <section class="context-panel">
            <p><strong>Manifest package unavailable:</strong> <?= manifest_viewer_h($error) ?></p>
            <p>Only the fixed allowlist key <code>ryderwear-batch2-v2</code> is supported. No arbitrary file paths are accepted.</p>
        </section>
    <?php else: ?>
        <section class="context-panel manifest-viewer-source-truth">
            <p><strong>The v2 JSON is the source of truth. The flat CSV is a review/tooling mirror only.</strong></p>
            <p>This read-only viewer does not generate, copy, import, update, publish, approve, or unblock any downstream artifact.</p>
        </section>

        <section class="context-panel">
            <h2>Summary</h2>
            <div class="manifest-viewer-grid">
                <?php
                $summaryItems = [
                    'Manifest title' => $package['title'],
                    'Manifest ID' => $manifest['manifest_id'] ?? '',
                    'Manifest version' => $manifest['manifest_version'] ?? '',
                    'Batch ID' => $manifest['batch_id'] ?? '',
                    'Generated at' => $manifest['generated_at'] ?? '',
                    'Active status' => $package['status'],
                    'Product count' => count($products),
                    'Image row count' => $imageRowCount,
                    'SourceRoot ID' => $sourceRoot['source_root_id'] ?? '',
                    'Root scope' => $sourceRoot['root_scope'] ?? '',
                    'Flat CSV mirror status' => ($flatValidation['row_count_matches_manifest'] ? 'mirror row count matches manifest' : 'mirror row count mismatch'),
                    'Gate conclusion' => $gateConclusion,
                    'Downstream artifacts' => 'blocked',
                ];
                foreach ($summaryItems as $label => $value): ?>
                    <div class="manifest-viewer-card">
                        <span class="manifest-viewer-label"><?= manifest_viewer_h($label) ?></span>
                        <span class="manifest-viewer-value"><?= manifest_viewer_h($value) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="context-panel">
            <h2>Identity model</h2>
            <ul>
                <li><code>db_itemId</code> maps to <code>item_id</code>.</li>
                <li><code>item_id</code> is the manifest-normalized database item identity.</li>
                <li><code>model_id</code> is the canonical Sports Warehouse product/model slug.</li>
                <li><code>product_key</code> is omitted/deprecated.</li>
                <li><code>external_item_id</code> is omitted/deprecated.</li>
                <li>Downstream tools must not infer ProductDB identity from <code>product_key</code> or <code>external_item_id</code>.</li>
            </ul>
        </section>

        <section class="context-panel">
            <h2>Product summary</h2>
            <div class="manifest-viewer-products">
                <?php foreach ($products as $product): ?>
                    <article class="manifest-viewer-card">
                        <dl>
                            <dt>item_id</dt><dd><?= manifest_viewer_h($product['item_id'] ?? '') ?></dd>
                            <dt>model_id</dt><dd><code><?= manifest_viewer_h($product['model_id'] ?? '') ?></code></dd>
                            <dt>title</dt><dd><?= manifest_viewer_h($product['title'] ?? '') ?></dd>
                            <dt>variant_group</dt><dd><?= manifest_viewer_h($product['variant_group'] ?? '') ?></dd>
                            <dt>approval_status</dt><dd><?= manifest_viewer_h($product['approval_status'] ?? '') ?></dd>
                            <dt>review_decision_code</dt><dd><?= manifest_viewer_h($product['review_decision_code'] ?? '') ?></dd>
                            <dt>image count</dt><dd><?= manifest_viewer_h(count(is_array($product['images'] ?? null) ? $product['images'] : [])) ?></dd>
                            <dt>reviewer_notes</dt><dd><?= manifest_viewer_h($product['reviewer_notes'] ?? '') ?></dd>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="context-panel">
            <h2>ImageAsset rows</h2>
            <div class="manifest-viewer-table-wrap">
                <table class="manifest-viewer-table">
                    <thead>
                        <tr>
                            <th>thumbnail</th><th>item_id</th><th>model_id</th><th>sequence</th><th>role</th><th>image_variant_group</th><th>source_relpath</th><th>original_filename</th><th>mime_type</th><th>width_px</th><th>height_px</th><th>byte_size</th><th>checksum12</th><th>image_approval_status</th><th>image_review_decision_code</th><th>delivery_status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php foreach ((is_array($product['images'] ?? null) ? $product['images'] : []) as $image): ?>
                                <?php
                                $checksum = (string)($image['checksum_sha256'] ?? '');
                                $delivery = is_array($image['delivery'] ?? null) ? $image['delivery'] : [];
                                $deliveryStatus = count($delivery) === 0 ? 'delivery_not_generated' : 'delivery_present';
                                ?>
                                <tr>
                                    <td><?= manifest_viewer_safe_thumbnail($sourceRootScope, (string)($image['source_relpath'] ?? ''), (string)($image['original_filename'] ?? 'Manifest image')) ?></td>
                                    <td><?= manifest_viewer_h($product['item_id'] ?? '') ?></td>
                                    <td><code><?= manifest_viewer_h($product['model_id'] ?? '') ?></code></td>
                                    <td><?= manifest_viewer_h($image['sequence'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['role'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['variant_group'] ?? '') ?></td>
                                    <td><code><?= manifest_viewer_h($image['source_relpath'] ?? '') ?></code></td>
                                    <td><?= manifest_viewer_h($image['original_filename'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['mime_type'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['width_px'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['height_px'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['byte_size'] ?? '') ?></td>
                                    <td><code><?= manifest_viewer_h(substr($checksum, 0, 12)) ?></code></td>
                                    <td><?= manifest_viewer_h($image['approval_status'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($image['review_decision_code'] ?? '') ?></td>
                                    <td><?= manifest_viewer_h($deliveryStatus) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="context-panel">
            <h2>Flat CSV mirror validation</h2>
            <div class="manifest-viewer-grid">
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">CSV path</span><span class="manifest-viewer-value"><code><?= manifest_viewer_h($package['flat_csv_path']) ?></code></span></div>
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">row count</span><span class="manifest-viewer-value"><?= manifest_viewer_h($flatValidation['row_count']) ?></span></div>
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">matches manifest image rows</span><span class="manifest-viewer-value"><?= manifest_viewer_h($flatValidation['row_count_matches_manifest']) ?></span></div>
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">product_key absent</span><span class="manifest-viewer-value"><?= manifest_viewer_h($flatValidation['product_key_absent']) ?></span></div>
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">external_item_id absent</span><span class="manifest-viewer-value"><?= manifest_viewer_h($flatValidation['external_item_id_absent']) ?></span></div>
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">delivery_count is 0 for all rows</span><span class="manifest-viewer-value"><?= manifest_viewer_h($flatValidation['delivery_count_zero']) ?></span></div>
                <div class="manifest-viewer-card"><span class="manifest-viewer-label">delivery_status is delivery_not_generated for all rows</span><span class="manifest-viewer-value"><?= manifest_viewer_h($flatValidation['delivery_status_not_generated']) ?></span></div>
            </div>
        </section>

        <section class="context-panel">
            <h2>Gate status</h2>
            <p><strong><?= manifest_viewer_h($gateConclusion) ?></strong></p>
            <ul>
                <li>This viewer does not approve copy simulation.</li>
                <li>This viewer does not approve image copying.</li>
                <li>This viewer does not approve SQL/import payloads.</li>
                <li>This viewer does not approve ProductDB updates.</li>
                <li>This viewer does not approve storefront gallery changes.</li>
                <li>This viewer does not approve publication.</li>
                <li><strong>Downstream artifacts remain blocked.</strong></li>
            </ul>
        </section>

        <section class="context-panel">
            <h2>Source documents</h2>
            <p>These links use the existing allowlisted workflow document viewer; they do not expose arbitrary filesystem browsing.</p>
            <div class="manifest-viewer-doc-links">
                <?= manifest_viewer_document_link($package, 'manifest_path', 'v2 manifest JSON') ?>
                <?= manifest_viewer_document_link($package, 'flat_csv_path', 'v2 flat CSV mirror') ?>
                <?= manifest_viewer_document_link($package, 'gate_report_path', 'v2 gate report') ?>
                <?= manifest_viewer_document_link($package, 'manifest_report_path', 'v2 manifest report') ?>
                <?= manifest_viewer_document_link($package, 'flat_report_path', 'v2 flat mirror report') ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<?php
admin_layout_end();
