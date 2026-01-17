<?php
// ============================================================
// Admin Dashboard — Phase 6A
// Orientation & Visibility Only
// ============================================================

define('ADMIN_CONTEXT', true);

require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/db.php'; // provides $pdo

// ------------------------------------------------------------
// Admin diagnostic helpers (read-only)
// ------------------------------------------------------------
require_once __DIR__ . '/inc/environment-status.php';
require_once __DIR__ . '/inc/hero-status.php';

// ------------------------------------------------------------
// Fetch status blocks (single source of truth)
// ------------------------------------------------------------
$envStatus  = sw_environment_status($pdo);
$heroStatus = sw_hero_status($pdo);

// ------------------------------------------------------------
// Render
// ------------------------------------------------------------
admin_layout_start("Dashboard");
admin_page_header("Dashboard", "System overview and operational controls.");
?>

<!-- =========================================================
     SYSTEM STATUS
     Orientation / visibility only (Phase 6A)
========================================================= -->
<section class="dashboard-section">
    <h2>System Status</h2>

    <ul class="status-list">
        <li>
            <strong>Environment:</strong>
            <?= htmlspecialchars($envStatus['environment']) ?>
        </li>

        <li>
            <strong>Host:</strong>
            <?= htmlspecialchars($envStatus['host']) ?>
        </li>

        <li>
            <strong>PHP version:</strong>
            <?= htmlspecialchars($envStatus['php_version']) ?>
        </li>

        <li>
            <strong>Database:</strong>
            <?= $envStatus['db_connected']
                ? '<span class="ok">Connected</span>'
                : '<span class="warn">Not connected</span>' ?>
        </li>

        <li>
            <strong>Status checked:</strong>
            <?= htmlspecialchars($envStatus['checked_at']) ?>
        </li>

        <li><hr></li>

        <li>
            <strong>Catalog items:</strong>
            <?= number_format($heroStatus['total_items']) ?>
        </li>

        <li>
            <strong>Hero coverage:</strong>
            <?= number_format($heroStatus['with_hero']) ?>
            /
            <?= number_format($heroStatus['total_items']) ?>
            (<?= $heroStatus['coverage_pct'] ?>%)
        </li>

        <li>
            <strong>Missing hero images:</strong>
            <?= number_format($heroStatus['missing_hero']) ?>
        </li>

        <li>
            <strong>Overrides present:</strong>
            <?= number_format($heroStatus['with_override']) ?>
        </li>

        <li>
            <strong>Legacy hero values:</strong>
            <?= number_format($heroStatus['legacy_hero']) ?>
        </li>
    </ul>

    <p class="muted">
        This dashboard reports current system and catalog state only.
        No enforcement, recomputation, or automation occurs here.
        Sidebar navigation remains authoritative for workflows.
    </p>
</section>

<!-- =========================================================
     SYSTEM & ENVIRONMENT
     Dashboard-only operational tools
========================================================= -->
<section class="dashboard-section">
    <h2>System &amp; Environment</h2>

    <div class="grid grid-3">

        <!-- PHP Info -->
        <a href="/admin/debug/php-info.php" class="card dashboard-card">
            <i class="fa-solid fa-server dashboard-icon"></i>
            <h3>PHP Info</h3>
            <p>Inspect the PHP configuration used on the server.</p>
        </a>

        <!-- Developer Functions / Deploy -->
        <div class="card dashboard-card dashboard-formcard">
            <i class="fa-solid fa-terminal dashboard-icon"></i>
            <h3>Developer Functions</h3>
            <p>Trigger deployment scripts directly from the admin panel.</p>

            <div class="split-button">
                <button class="main" onclick="runDeployDefault()">Deploy</button>
                <button class="menu"
                        aria-label="More deploy options"
                        aria-expanded="false">▼</button>

                <div class="dropdown">
                    <button type="button" onclick="runDeployDefault()">
                        Without Commit Message
                    </button>
                    <button type="button" onclick="promptCommit(this)">
                        With Commit Message
                    </button>
                </div>
            </div>
        </div>

    </div>
</section>

<?php
admin_layout_end();
?>
