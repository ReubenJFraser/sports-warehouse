<?php
// -------------------------------------------------------------------------
// SPORTS WAREHOUSE — ADMIN NAVIGATION
// Builds grouped sidebar nav with active-state highlighting
// -------------------------------------------------------------------------

function admin_render_nav(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '';
    parse_str($query, $currentQueryParams);

    if (preg_match('#^(.*?/admin)(?:/|$)#', $path, $m)) {
        $adminBase = rtrim($m[1], '/');   // e.g. /sports-warehouse-home-page/admin OR /admin
    } else {
        $adminBase = '/admin';            // fallback
    }

    $normalize = function (string $path): string {
        return ($path === '/') ? $path : rtrim($path, '/');
    };

    $currentPath = $normalize($path);

    $sections = [
        'General' => [
            [
                'href'  => $adminBase . '/index.php',
                'icon'  => 'fa-solid fa-gauge',
                'label' => 'Dashboard',
            ],
        ],
        'Hero Tools' => [
            [
                'href'  => $adminBase . '/hero-manager.php',
                'icon'  => 'fa-solid fa-images',
                'label' => 'Hero Manager',
                'excludeQuery' => ['status' => 'inactive'],
            ],
            [
                'href'       => $adminBase . '/hero-manager.php?status=inactive',
                'icon'       => 'fa-solid fa-eye-slash',
                'label'      => 'Inactive Product Review',
                'matchQuery' => ['status' => 'inactive'],
            ],
            [
                'href'  => $adminBase . '/hero-edit.php',
                'icon'  => 'fa-solid fa-wand-magic-sparkles',
                'label' => 'Hero Editor',
            ],
            [
                'href'  => $adminBase . '/hero-rationale-report.php',
                'icon'  => 'fa-solid fa-chart-simple',
                'label' => 'Rationale Report',
            ],
        ],
        'Diagnostics' => [
            [
                'href'  => $adminBase . '/debug/index.php',
                'icon'  => 'fa-solid fa-bug',
                'label' => 'Debug Tools',
            ],
            [
                'href'  => $adminBase . '/debug/file-tree.php',
                'icon'  => 'fa-solid fa-folder-tree',
                'label' => 'File Tree',
            ],
            [
                'href'  => $adminBase . '/debug/duplicate-trees.php',
                'icon'  => 'fa-solid fa-clone',
                'label' => 'Duplicate Site Trees',
            ],
            [
                'href'  => $adminBase . '/debug/hero-analysis.php',
                'icon'  => 'fa-solid fa-chart-column',
                'label' => 'Hero Analysis'
            ],
            [
                'href'  => $adminBase . '/image-integrity.php',
                'icon'  => 'fa-solid fa-image',
                'label' => 'Image Integrity'
            ],
            [
                'href'      => $adminBase . '/sync-tool.php',
                'icon'      => 'fa-solid fa-shuffle',
                'label'     => 'Sync Tool',
                'localOnly' => true
            ],
            [
                'href'      => $adminBase . '/db-test.php',
                'icon'      => 'fa-solid fa-database',
                'label'     => 'DB Test',
                'localOnly' => false
            ],
        ],
    ];

    $html = '<nav class="admin-nav">';

    foreach ($sections as $groupLabel => $items) {
        $html .= '<div class="admin-nav-group">';
        $html .= '<div class="admin-nav-group-label">' . htmlspecialchars($groupLabel) . '</div>';

        foreach ($items as $item) {

            if (!empty($item['localOnly'])) {
                $host = $_SERVER['HTTP_HOST'] ?? '';
                if ($host !== 'localhost' && $host !== '127.0.0.1') {
                    continue;
                }
            }

            $hrefNorm = $normalize($item['href']);
            $isActive = ($hrefNorm === $currentPath);

            if ($isActive && !empty($item['matchQuery']) && is_array($item['matchQuery'])) {
                foreach ($item['matchQuery'] as $key => $expectedValue) {
                    $actualValue = isset($currentQueryParams[$key]) ? (string)$currentQueryParams[$key] : null;
                    if ((string)$expectedValue !== $actualValue) {
                        $isActive = false;
                        break;
                    }
                }
            }
            if ($isActive && !empty($item['excludeQuery']) && is_array($item['excludeQuery'])) {
                foreach ($item['excludeQuery'] as $key => $excludedValue) {
                    $actualValue = isset($currentQueryParams[$key]) ? (string)$currentQueryParams[$key] : null;
                    if ((string)$excludedValue === $actualValue) {
                        $isActive = false;
                        break;
                    }
                }
            }

            if (
                !$isActive &&
                $hrefNorm === $normalize($adminBase . '/index.php') &&
                $currentPath === $normalize($adminBase)
            ) {
                $isActive = true;
            }

            $class = 'admin-nav-item' . ($isActive ? ' is-active' : '');
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
