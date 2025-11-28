<?php

// CONFIGURATION
$localRoot  = 'C:/laragon/www/sports-warehouse-home-page';
$remoteRoot = '/home/u642727376/domains/sports-warehouse.reubenfraser.com/public_html'; // Hostinger
$ignoreList = [
    '.git', '.venv', 'node_modules', 'handover', 'handover_swiper',
    '_backup_2025-09-26', 'sw_git_bak',

    // BIG PERFORMANCE WIN: completely skip large image/media trees
    // (You said local/remote images are identical and this is OK)
    'images/brands',
    'images/videos',
];

// MODE FLAGS
$isCsv = isset($_GET['download']) && $_GET['download'] === 'csv';
// Fix #1: only treat debug as ON when debug=1 explicitly
$debug = (isset($_GET['debug']) && $_GET['debug'] === '1');

// Optional: reduce risk of timeouts
@set_time_limit(0);

// -----------------------------------------------------------------------------
// Helper Functions
// -----------------------------------------------------------------------------

function shouldIgnore($path, $ignoreList) {
    // normalize just in case
    $path = str_replace('\\', '/', $path);

    // hard skip patterns
    foreach ($ignoreList as $pat) {
        if (strpos($path, $pat) !== false) return true;
    }

    // ignore logs
    if (fnmatch('*.log', $path)) return true;

    return false;
}

/**
 * Treat as "image / binary media" for Option C logic.
 * We skip content hashing for these when present on both sides,
 * but we *do* still show if they are missing on one side (unless directory
 * itself is ignored above).
 */
function isImageFile($path) {
    return (bool)preg_match(
        '/\.(png|jpe?g|gif|webp|svg|ico|bmp|tiff?|mp4|mov|webm)$/i',
        $path
    );
}

// Local scan
function scanLocal($dir, &$files, $ignoreList, $root, $debug = false) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . '/' . $item;
        $rel  = ltrim(str_replace($root, '', $full), '/');
        $rel  = str_replace('\\', '/', $rel); // Normalize

        if (shouldIgnore($rel, $ignoreList)) {
            if ($debug) {
                echo "<div style='color:orange;'>[Local] Ignored: " . htmlspecialchars($rel) . "</div>";
            }
            continue;
        }

        if (is_dir($full)) {
            if ($debug) {
                echo "<div style='color:purple;'>[Local] Dir: " . htmlspecialchars($rel) . "</div>";
            }
            scanLocal($full, $files, $ignoreList, $root, $debug);
        } else {
            if ($debug) {
                echo "<div style='color:green;'>[Local] File: " . htmlspecialchars($rel) . "</div>";
            }
            $files[$rel] = [
                'size'  => filesize($full),
                'mtime' => filemtime($full)
            ];
        }
    }
}

// Remote scan via SSH/SFTP
function scanRemote($sftp, $path, &$files, $ignoreList, $remoteRoot, $debug = false) {
    if ($debug) {
        echo "<div style='color:blue;'>[Remote] Opening dir: " . htmlspecialchars($path) . "</div>";
    }

    $dirHandle = @opendir("ssh2.sftp://{$sftp}{$path}");
    if (!$dirHandle) {
        if ($debug) {
            echo "<div style='color:red;'>[Remote] Failed to open: " . htmlspecialchars($path) . "</div>";
        }
        return;
    }

    while (($item = readdir($dirHandle)) !== false) {
        if ($item === '.' || $item === '..') continue;

        $full = "$path/$item";
        $rel  = ltrim(str_replace($remoteRoot, '', $full), '/');
        $rel  = str_replace('\\', '/', $rel); // Normalize

        if (shouldIgnore($rel, $ignoreList)) {
            if ($debug) {
                echo "<div style='color:orange;'>[Remote] Ignored: " . htmlspecialchars($rel) . "</div>";
            }
            continue;
        }

        $stat = @ssh2_sftp_stat($sftp, $full);
        if (!$stat) {
            if ($debug) {
                echo "<div style='color:red;'>[Remote] Failed stat: " . htmlspecialchars($full) . "</div>";
            }
            continue;
        }

        // Directory bit (0x4000)
        if (($stat['mode'] & 0x4000) === 0x4000) {
            if ($debug) {
                echo "<div style='color:purple;'>[Remote] Dir: " . htmlspecialchars($rel) . "</div>";
            }
            scanRemote($sftp, $full, $files, $ignoreList, $remoteRoot, $debug);
        } else {
            if ($debug) {
                echo "<div style='color:green;'>[Remote] File: " . htmlspecialchars($rel) . "</div>";
            }
            $files[$rel] = [
                'size'  => $stat['size'],
                'mtime' => $stat['mtime']
            ];
        }
    }

    closedir($dirHandle);
}

// -----------------------------------------------------------------------------
// SSH Login
// -----------------------------------------------------------------------------

$ssh = @ssh2_connect('109.106.254.43', 65002);
if (!$ssh) {
    die('SSH connection failed.');
}

$authOk = @ssh2_auth_pubkey_file(
    $ssh,
    'u642727376',
    'C:/Users/rjfra/.ssh/hostinger_gha.pub',
    'C:/Users/rjfra/.ssh/hostinger_gha',
    '' // blank passphrase
);

if (!$authOk) {
    die('SSH public key authentication failed.');
}

$sftp = @ssh2_sftp($ssh);
if (!$sftp) {
    die('Failed to initialize SFTP subsystem.');
}

// -----------------------------------------------------------------------------
// Collect file lists
// -----------------------------------------------------------------------------

$localFiles  = [];
$remoteFiles = [];

scanLocal($localRoot, $localFiles, $ignoreList, $localRoot, $debug);
scanRemote($sftp, $remoteRoot, $remoteFiles, $ignoreList, $remoteRoot, $debug);

// -----------------------------------------------------------------------------
// Compare results
// -----------------------------------------------------------------------------

$onlyLocal  = array_diff_key($localFiles, $remoteFiles);
$onlyRemote = array_diff_key($remoteFiles, $localFiles);
$common     = array_intersect_key($localFiles, $remoteFiles);

$diffFiles  = [];

// Option C: only hash **non-image** files that exist on both sides
foreach ($common as $path => $info) {
    if (isImageFile($path)) {
        // Present on both sides, but we trust images and skip hashing.
        continue;
    }

    $localPath  = $localRoot . '/' . $path;
    $remotePath = "ssh2.sftp://{$sftp}{$remoteRoot}/" . $path;

    if (!file_exists($localPath)) {
        // Shouldn't normally happen, but be defensive.
        $diffFiles[] = $path;
        continue;
    }
    if (!file_exists($remotePath)) {
        $diffFiles[] = $path;
        continue;
    }

    $localHash  = @md5_file($localPath);
    $remoteHash = @md5_file($remotePath);

    if ($localHash === false || $remoteHash === false) {
        // If hashing fails, treat as "different" so it doesn't silently pass.
        $diffFiles[] = $path;
        continue;
    }

    if ($localHash !== $remoteHash) {
        $diffFiles[] = $path;
    }
}

// Classification summary for chart
$typeCounts = [
    'Only in Local'     => count($onlyLocal),
    'Only in Remote'    => count($onlyRemote),
    'Different Content' => count($diffFiles)
];

// -----------------------------------------------------------------------------
// CSV Export (no HTML, no debug)
// -----------------------------------------------------------------------------

if ($isCsv) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sync_diff_report.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Path', 'Status', 'Local Time', 'Remote Time']);

    foreach ($onlyLocal as $path => $_) {
        $localTime = date('Y-m-d H:i:s', $localFiles[$path]['mtime']);
        fputcsv($out, [$path, 'Only in Local', $localTime, '']);
    }

    foreach ($onlyRemote as $path => $_) {
        $remoteTime = date('Y-m-d H:i:s', $remoteFiles[$path]['mtime']);
        fputcsv($out, [$path, 'Only in Remote', '', $remoteTime]);
    }

    foreach ($diffFiles as $path) {
        $localTime  = date('Y-m-d H:i:s', $localFiles[$path]['mtime']);
        $remoteTime = date('Y-m-d H:i:s', $remoteFiles[$path]['mtime']);
        fputcsv($out, [$path, 'Different Content', $localTime, $remoteTime]);
    }

    fclose($out);
    exit;
}

// -----------------------------------------------------------------------------
// HTML Output (summary + chart + verbose table)
// -----------------------------------------------------------------------------

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sports Warehouse – Sync Diff Report (No Images)</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #111;
            color: #eee;
            padding: 20px;
        }
        h1, h2 {
            color: #fff;
        }
        a {
            color: #4aa3ff;
        }
        table {
            border-collapse: collapse;
            margin-top: 16px;
            width: 100%;
            max-width: 1200px;
            background: #181818;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            font-size: 13px;
        }
        th {
            background: #222;
        }
        tr:nth-child(even) {
            background: #151515;
        }
        .status-local {
            color: #3498db;
        }
        .status-remote {
            color: #2ecc71;
        }
        .status-diff {
            color: #e74c3c;
        }
        .meta {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 10px;
        }
        #chart-wrapper {
            max-width: 600px;
            margin: 20px 0;
            background: #181818;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #333;
        }
        .debug-banner {
            background:#8b0000;
            color:#fff;
            padding:8px 10px;
            margin-bottom:10px;
            border-radius:4px;
            font-weight:bold;
        }
    </style>
</head>
<body>

<h1>Sync Diff Report (Code & Non-Image Files)</h1>

<?php if ($debug): ?>
<div class="debug-banner">DEBUG MODE ENABLED (streaming directory logs below the table)</div>
<?php endif; ?>

<div class="meta">
    Local root: <code><?php echo htmlspecialchars($localRoot); ?></code><br>
    Remote root: <code><?php echo htmlspecialchars($remoteRoot); ?></code><br>
    Local files found (excluding ignored paths): <?php echo count($localFiles); ?>,
    Remote files found (excluding ignored paths): <?php echo count($remoteFiles); ?><br>
    <a href="?download=csv">Download CSV</a>
    <?php if (!$debug): ?>
        &nbsp;|&nbsp; <a href="?debug=1">Enable debug output</a>
    <?php else: ?>
        &nbsp;|&nbsp; <strong>Debug mode enabled</strong>
    <?php endif; ?>
</div>

<h2>Summary of File Differences</h2>
<table>
    <tr><th>Type</th><th>Count</th></tr>
    <?php foreach ($typeCounts as $type => $count): ?>
        <tr>
            <td><?php echo htmlspecialchars($type); ?></td>
            <td><?php echo (int)$count; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<div id="chart-wrapper">
    <canvas id="diffChart" width="400" height="200"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('diffChart').getContext('2d');
    const labels = <?php echo json_encode(array_keys($typeCounts)); ?>;
    const dataValues = <?php echo json_encode(array_values($typeCounts)); ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'File Difference Counts',
                data: dataValues,
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'],
                borderColor: ['#2980b9', '#27ae60', '#c0392b'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: {
                legend: { display: true }
            }
        }
    });
});
</script>

<h2>Detailed Differences</h2>
<table>
    <tr>
        <th>Path</th>
        <th>Status</th>
        <th>Local Time</th>
        <th>Remote Time</th>
    </tr>

    <?php foreach ($onlyLocal as $path => $_): ?>
        <tr>
            <td><?php echo htmlspecialchars($path); ?></td>
            <td class="status-local">Only in Local</td>
            <td><?php echo date('Y-m-d H:i:s', $localFiles[$path]['mtime']); ?></td>
            <td></td>
        </tr>
    <?php endforeach; ?>

    <?php foreach ($onlyRemote as $path => $_): ?>
        <tr>
            <td><?php echo htmlspecialchars($path); ?></td>
            <td class="status-remote">Only in Remote</td>
            <td></td>
            <td><?php echo date('Y-m-d H:i:s', $remoteFiles[$path]['mtime']); ?></td>
        </tr>
    <?php endforeach; ?>

    <?php foreach ($diffFiles as $path): ?>
        <tr>
            <td><?php echo htmlspecialchars($path); ?></td>
            <td class="status-diff">Different Content</td>
            <td><?php echo date('Y-m-d H:i:s', $localFiles[$path]['mtime']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', $remoteFiles[$path]['mtime']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>






