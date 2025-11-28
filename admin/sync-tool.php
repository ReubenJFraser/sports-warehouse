<?php
// admin/sync-tool.php
// Targeted Sync Tool — fully integrated into the admin layout

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../db.php'; // not strictly needed, but consistent with admin
// -----------------------------------------------------------------------------
// CONFIG
// -----------------------------------------------------------------------------
$localRoot  = 'C:/laragon/www/sports-warehouse-home-page';
$remoteRoot = '/home/u642727376/domains/sports-warehouse.reubenfraser.com/public_html';

$sshHost = '109.106.254.43';
$sshPort = 65002;
$sshUser = 'u642727376';
$sshPubKey  = 'C:/Users/rjfra/.ssh/hostinger_gha.pub';
$sshPrivKey = 'C:/Users/rjfra/.ssh/hostinger_gha';
$sshPassphrase = '';

$whitelistTop = [
    ''          => 'Project Root (PHP / config)',
    'admin'     => 'Admin',
    'css'       => 'CSS',
    'js'        => 'JavaScript',
    'inc'       => 'Includes',
    'scripts'   => 'Scripts',
    'config'    => 'Config'
];

$hardIgnorePatterns = [
    'images', 'uploads', 'wp-content', 'online_website', 'logs',
    '.git', '.github', '.venv', 'node_modules', '_local_backup_audit',
    'tests', 'src', 'db', 'docs', 'sql', '_image-system_review'
];

// -----------------------------------------------------------------------------
// HELPERS
// -----------------------------------------------------------------------------
function shouldIgnorePath(string $relPath, array $patterns): bool {
    $relPath = str_replace('\\', '/', $relPath);
    foreach ($patterns as $pat) {
        if ($pat && strpos($relPath, $pat) !== false) return true;
    }
    return false;
}

function listLocalFiles(string $baseDir, string $relBase, array $ignore): array {
    $result = [];
    $rootPath = rtrim($baseDir . '/' . $relBase, '/');

    if (!is_dir($rootPath)) return $result;

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it as $file) {
        if (!$file->isFile()) continue;

        $full = $file->getPathname();
        $rel  = ltrim(str_replace($baseDir, '', $full), DIRECTORY_SEPARATOR);
        $rel  = str_replace('\\', '/', $rel);

        if (shouldIgnorePath($rel, $ignore)) continue;

        $group = $relBase !== '' ? $relBase : explode('/', $rel, 2)[0];
        $result[$group][] = $rel;
    }

    foreach ($result as &$r) sort($r, SORT_NATURAL | SORT_FLAG_CASE);
    ksort($result, SORT_NATURAL | SORT_FLAG_CASE);
    return $result;
}

// -----------------------------------------------------------------------------
// PROCESS FORM
// -----------------------------------------------------------------------------
$comparisonResults = [];
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['paths'])) {
    $selected = array_unique(array_map('trim', $_POST['paths']));

    $ssh = @ssh2_connect($sshHost, $sshPort);
    if (!$ssh) {
        $errorMessage = 'SSH connection to remote host failed.';
    } elseif (!@ssh2_auth_pubkey_file($ssh, $sshUser, $sshPubKey, $sshPrivKey, $sshPassphrase)) {
        $errorMessage = 'SSH authentication failed.';
    } else {
        $sftp = @ssh2_sftp($ssh);
        if (!$sftp) {
            $errorMessage = 'Failed to initialise SFTP.';
        } else {
            foreach ($selected as $rel) {
                if ($rel === '' || shouldIgnorePath($rel, $hardIgnorePatterns)) continue;

                $localPath = $localRoot . '/' . $rel;
                $remoteUrl = 'ssh2.sftp://' . intval($sftp) . $remoteRoot . '/' . $rel;

                $localExists  = file_exists($localPath);
                $remoteExists = @file_exists($remoteUrl);

                $localTime  = $localExists  ? filemtime($localPath)  : null;
                $remoteTime = $remoteExists ? filemtime($remoteUrl) : null;

                if (!$localExists && !$remoteExists) {
                    $status = 'Missing (Both)';
                    $detail = 'File missing locally and on remote.';
                } elseif ($localExists && !$remoteExists) {
                    $status = 'Missing on Remote';
                    $detail = 'Local file not found on remote.';
                } elseif (!$localExists && $remoteExists) {
                    $status = 'Missing Locally';
                    $detail = 'Remote file not found locally.';
                } else {
                    $localHash = @md5_file($localPath);
                    $remoteHash = @md5_file($remoteUrl);
                    if ($localHash === false || $remoteHash === false) {
                        $status = 'Error';
                        $detail = 'Hash read failure.';
                    } elseif ($localHash === $remoteHash) {
                        $status = 'Same';
                        $detail = 'Files are identical.';
                    } else {
                        $status = 'Different';
                        $detail = 'Contents differ.';
                    }
                }

                $comparisonResults[] = [
                    'path'        => $rel,
                    'status'      => $status,
                    'detail'      => $detail,
                    'localTime'   => $localTime,
                    'remoteTime'  => $remoteTime,
                ];
            }
        }
    }
}

// -----------------------------------------------------------------------------
// Build file lists
// -----------------------------------------------------------------------------
$filesByTopFolder = [];
foreach ($whitelistTop as $folder => $label) {
    foreach (listLocalFiles($localRoot, $folder, $hardIgnorePatterns) as $group => $files) {
        $filesByTopFolder[$group] = ($filesByTopFolder[$group] ?? []);
        $filesByTopFolder[$group] = array_merge($filesByTopFolder[$group], $files);
    }
}
ksort($filesByTopFolder);

// -----------------------------------------------------------------------------
// ADMIN LAYOUT START
// -----------------------------------------------------------------------------
admin_layout_start("Targeted Sync Tool");

admin_header(
    "Targeted Sync Tool",
    "Compare selected development files between your localhost Laragon project and your Hostinger production site.",
    [
        ["label" => "Admin", "href" => "/admin/index.php"],
        ["label" => "Diagnostics", "href" => "/admin/debug/index.php"],
        ["label" => "Targeted Sync Tool"]
    ]
);
?>

<div class="admin-wrapper">

    <p class="subtitle" style="margin-bottom:12px;">
        Local root: <code><?= htmlspecialchars($localRoot) ?></code> <br>
        Remote root: <code><?= htmlspecialchars($remoteRoot) ?></code>
    </p>

    <div class="grid grid-2">

        <!-- LEFT PANEL -->
        <section class="card">
            <h2 style="margin-bottom:10px;">Select Files to Compare</h2>

            <form method="post">
                <div style="max-height:420px; overflow:auto; border:1px solid #333; padding:10px; border-radius:8px;">

                    <?php foreach ($filesByTopFolder as $group => $files): ?>
                        <div style="margin-top:10px;">
                            <strong><?= htmlspecialchars($group === '' ? '[root]' : $group) ?></strong>
                        </div>

                        <?php foreach ($files as $rel): ?>
                            <label style="display:flex; align-items:center; gap:6px; padding:2px 0;">
                                <input type="checkbox" name="paths[]" value="<?= htmlspecialchars($rel) ?>">
                                <span><?= htmlspecialchars($rel) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>

                <div class="mt-2" style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-ghost" onclick="toggleAll(true)">Select all</button>
                    <button type="button" class="btn btn-ghost" onclick="toggleAll(false)">Clear</button>
                    <button type="submit" class="btn btn-primary">Compare Selected</button>
                </div>

            </form>
        </section>

        <!-- RIGHT PANEL -->
        <section class="card">
            <h2 style="margin-bottom:10px;">Comparison Results</h2>

            <?php if ($errorMessage): ?>
                <div class="flash flash--error mb-2">
                    <span class="flash__pill"></span>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($comparisonResults): ?>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th>Status</th>
                            <th>Local mtime</th>
                            <th>Remote mtime</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comparisonResults as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['path']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td><?= $row['localTime']  ? date('Y-m-d H:i:s', $row['localTime'])  : '—' ?></td>
                                <td><?= $row['remoteTime'] ? date('Y-m-d H:i:s', $row['remoteTime']) : '—' ?></td>
                                <td><?= htmlspecialchars($row['detail']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <p>No comparison has been run yet.</p>
            <?php endif; ?>
        </section>

    </div>
</div>

<script>
function toggleAll(state) {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = state);
}
</script>

<?php admin_layout_end(); ?>




