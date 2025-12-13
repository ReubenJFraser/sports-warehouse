<?php
// -------------------------------------------------------------------------
// SPORTS WAREHOUSE — ADMIN LAYOUT
// Sidebar + mobile drawer + page wrapper + header helpers
// Version: 2025-11-20
// -------------------------------------------------------------------------

require_once __DIR__ . '/_nav.php';

if (!function_exists('admin_layout_start')) {

    function admin_layout_start(string $pageTitle = 'Admin'): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($pageTitle) ?> — Admin</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- Core Admin Styles -->
            <link rel="stylesheet" href="../css/admin/core.css">
            <link rel="stylesheet" href="../css/admin/layout.css">
            <link rel="stylesheet" href="../css/admin/nav.css">
            <link rel="stylesheet" href="../css/admin/hero.css">

            <!-- Icons (Font Awesome) -->
            <link rel="stylesheet"
                  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        </head>
        <body>

        <!-- ---------------------------------------------------------
             MOBILE TOP BAR
        ---------------------------------------------------------- -->
        <header class="admin-topbar">
            <button class="admin-nav-toggle" id="navToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="admin-topbar-title">Admin Panel</span>
        </header>

        <!-- ---------------------------------------------------------
             SIDEBAR NAVIGATION
        ---------------------------------------------------------- -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-inner">
                <div class="admin-sidebar-logo">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Sports Warehouse</span>
                </div>

                <?= admin_render_nav(); ?>
            </div>
        </aside>

        <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

        <!-- ---------------------------------------------------------
             PAGE CONTENT WRAPPER
        ---------------------------------------------------------- -->
        <main class="admin-content">
        <?php
    }

    function admin_layout_end(): void
    {
        ?>
        </main>

        <script>
        /* ---------------------------------------------------------
           Mobile Drawer Toggle
        --------------------------------------------------------- */
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle  = document.getElementById('navToggle');

        if (toggle && sidebar && overlay) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });
        }
        </script>

        <script src="/js/admin/functions.js"></script>

        </body>
        </html>
        <?php
    }

    /**
     * Renders the standard page header block with optional breadcrumbs.
     *
     * $breadcrumbs example:
     * [
     *   ['label' => 'Admin', 'href' => '/admin/'],
     *   ['label' => 'Hero Tools', 'href' => '/admin/hero-manager.php'],
     *   ['label' => 'Hero Manager'] // last crumb, no href
     * ]
     */
    function admin_header(
        string $title,
        string $subtitle = '',
        ?array $breadcrumbs = null
    ): void {
        ?>
        <div class="admin-wrapper">

            <?php if (!empty($breadcrumbs)): ?>
                <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                    <?php
                    $lastIndex = count($breadcrumbs) - 1;
                    foreach ($breadcrumbs as $index => $crumb) {
                        $label = htmlspecialchars($crumb['label'] ?? '');
                        $href  = $crumb['href'] ?? null;

                        if ($href && $index !== $lastIndex) {
                            $href = htmlspecialchars($href);
                            echo '<a href="' . $href . '">' . $label . '</a>';
                        } else {
                            echo '<span>' . $label . '</span>';
                        }

                        if ($index !== $lastIndex) {
                            echo '<span class="admin-breadcrumb-separator">/</span>';
                        }
                    }
                    ?>
                </nav>
            <?php endif; ?>

            <header class="admin-header">
                <h1 class="admin-header-title"><?= htmlspecialchars($title) ?></h1>
                <?php if ($subtitle !== ''): ?>
                    <p class="admin-header-subtitle">
                        <?= $subtitle ?>
                    </p>
                <?php endif; ?>
            </header>
        <?php
        // NOTE: we intentionally do NOT close .admin-wrapper here.
        // The page can close it when it finishes its main content.
    }

}
?>

