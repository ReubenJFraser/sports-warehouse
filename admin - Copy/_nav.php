<?php
// -------------------------------------------------------------------------
// SPORTS WAREHOUSE — ADMIN NAVIGATION
// Builds grouped sidebar nav with active-state highlighting
// -------------------------------------------------------------------------

function admin_render_nav(): string
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

    // Convenience: normalise trailing slash
    $normalize = function (string $path): string {
        if ($path === '/') return $path;
        return rtrim($path, '/');
    };

    $currentPath = $normalize($currentPath);

    $sections = [
        'General' => [
            [
                'href'  => '/admin/index.php',
                'icon'  => 'fa-solid fa-gauge',
                'label' => 'Dashboard',
            ],
        ],
        'Hero Tools' => [
            [
                'href'  => '/admin/hero-manager.php',
                'icon'  => 'fa-solid fa-images',
                'label' => 'Hero Manager',
            ],
            [
                'href'  => '/admin/hero-edit.php',
                'icon'  => 'fa-solid fa-wand-magic-sparkles',
                'label' => 'Hero Editor',
            ],
        ],
        'Diagnostics' => [
            [
                'href'  => '/admin/debug/index.php',
                'icon'  => 'fa-solid fa-bug',
                'label' => 'Debug Tools',
            ],
            [
                'href'  => '/admin/debug/file-tree.php',
                'icon'  => 'fa-solid fa-folder-tree',
                'label' => 'File Tree',
            ],
            [
                'href'  => '/admin/debug/duplicate-trees.php',
                'icon'  => 'fa-solid fa-clone',
                'label' => 'Duplicate Site Trees',
            ],
            [
                "href"  => "/admin/debug/hero-analysis.php",
                "icon"  => "fa-solid fa-chart-column",
                "label" => "Hero Analysis"
            ],

        ],
    ];

    $html = '<nav class="admin-nav">';

    foreach ($sections as $groupLabel => $items) {
        $html .= '<div class="admin-nav-group">';
        $html .= '<div class="admin-nav-group-label">' . htmlspecialchars($groupLabel) . '</div>';

        foreach ($items as $item) {
            $hrefNorm   = $normalize($item['href']);
            $isActive   = ($hrefNorm === $currentPath);

            // Special-case: /admin/index.php also active on /admin/
            if (!$isActive && $hrefNorm === '/admin/index.php' && $currentPath === '/admin') {
                $isActive = true;
            }

            $class = 'admin-nav-item';
            if ($isActive) {
                $class .= ' is-active';
            }

            $icon  = htmlspecialchars($item['icon']);
            $href  = htmlspecialchars($item['href']);
            $label = htmlspecialchars($item['label']);

            $html .= <<<HTML
                <a href="{$href}" class="{$class}">
                    <i class="{$icon}"></i>
                    <span>{$label}</span>
                </a>
            HTML;
        }

        $html .= '</div>';
    }

    $html .= '</nav>';

    return $html;
}


