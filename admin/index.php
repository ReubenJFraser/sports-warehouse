<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';  // for page headers + breadcrumbs

admin_layout_start("Dashboard");
admin_page_header("Dashboard", "Welcome to the Sports Warehouse admin console.");
?>

<div class="grid grid-3">

    <!-- Hero Manager -->
    <a href="/admin/hero-manager.php" class="card dashboard-card">
        <i class="fa-solid fa-images dashboard-icon"></i>
        <h2>Hero Manager</h2>
        <p>View hero images, scores, overrides, and regenerate hero frames.</p>
    </a>

    <!-- Hero Editor -->
    <a href="/admin/hero-edit.php" class="card dashboard-card">
        <i class="fa-solid fa-wand-magic-sparkles dashboard-icon"></i>
        <h2>Hero Editor</h2>
        <p>Inspect candidate frames and select the best hero image manually.</p>
    </a>

    <!-- Debug Tools -->
    <a href="/admin/debug/index.php" class="card dashboard-card">
        <i class="fa-solid fa-bug dashboard-icon"></i>
        <h2>Debug Tools</h2>
        <p>Run diagnostics, view environment info, detect duplicate site trees.</p>
    </a>

    <!-- File Browser -->
    <a href="/admin/debug/file-tree.php" class="card dashboard-card">
        <i class="fa-solid fa-folder-tree dashboard-icon"></i>
        <h2>File Browser</h2>
        <p>Explore the server filesystem for troubleshooting and cleanup.</p>
    </a>

    <!-- Duplicate Site Trees -->
    <a href="/admin/debug/duplicate-trees.php" class="card dashboard-card">
        <i class="fa-solid fa-clone dashboard-icon"></i>
        <h2>Duplicate Site Trees</h2>
        <p>Scan for redundant folder trees inside public_html.</p>
    </a>

    <!-- PHP Info -->
    <a href="/admin/debug/php-info.php" class="card dashboard-card">
        <i class="fa-solid fa-server dashboard-icon"></i>
        <h2>PHP Info</h2>
        <p>Inspect the PHP configuration used on the Hostinger server.</p>
    </a>

    <!-- Developer Functions -->
    <div class="dashboard-card dashboard-formcard">
        <i class="fa-solid fa-terminal dashboard-icon"></i>
        <h2>Developer Functions</h2>
        <p>Trigger deployment scripts directly from the admin panel.</p>

        <div class="split-button">
            <button class="main" onclick="runDeployDefault()">Deploy</button>
            <button class="menu" aria-label="More deploy options" aria-expanded="false">▼</button>

            <div class="dropdown">
                <button type="button" onclick="runDeployDefault()">Without Commit Message</button>
                <button type="button" onclick="promptCommit(this)">With Commit Message</button>
            </div>
        </div>
    </div>

</div>

<?php
admin_layout_end();
?>


