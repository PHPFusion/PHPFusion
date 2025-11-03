<?php
/*
 * Usage:
 *
 * $tabs = [
    [
        'id' => 'home',
        'title' => 'Home',
        'icon' => '<i class="bi bi-house"></i> ',
        'active' => true
    ],
    [
        'id' => 'profile',
        'title' => 'Profile',
        'icon' => '<i class="bi bi-person"></i> ',
    ],
    [
        'id' => 'settings',
        'title' => 'Settings',
        'dropdown' => [
            ['title' => 'Option 1', 'link' => '#opt1'],
            ['divider' => true],
            ['title' => 'Option 2', 'link' => '#opt2'],
        ]
    ]
];
 * echo render_bs5_tabs($tabs, 'mainTabs', 'header', ['wrapper' => true]);
echo render_bs5_tabs([], 'home', 'openbody', ['active' => true]);
echo '<p>Tab content for Home...</p>';
echo render_bs5_tabs([], 'home', 'closebody');
echo render_bs5_tabs($tabs, 'mainTabs', 'footer', ['wrapper' => true]);
 */

/**
 * Render Bootstrap 5 tabs (with optional dropdowns and wrappers)
 *
 * @param array $tabs Array of tab definitions
 * @param string $id HTML ID for the tab group
 * @param string $part One of: header | openbody | closebody | footer
 * @param array $options Optional keys:
 *                       - 'class' (string) extra classes for <ul>
 *                       - 'wrapper' (bool) wrap content in .tab-content
 *                       - 'tab_nav' (bool) show next/previous buttons
 *                       - 'locale' (array) translation for buttons
 * @return string HTML output
 */
function render_tabs(array $tabs, string $id, string $part, array $options = []): string
{
    $class = $options['class'] ?? 'nav-tabs';
    $wrapper = $options['wrapper'] ?? false;
    $tab_nav = $options['tab_nav'] ?? false;
    $locale = $options['locale'] ?? ['previous' => 'Previous', 'next' => 'Next'];

    $html = '';

    switch ($part) {
        // --- Tabs header ---
        case 'header':
            $html .= "<ul class=\"nav {$class}\" id=\"{$id}\" role=\"tablist\">";
            foreach ($tabs as $tb) {
                $isActive = !empty($tb['active']) ? 'active' : '';
                $tabId = htmlspecialchars($tb['id']);
                $tabTitle = $tb['title'] ?? '';
                $tabIcon = $tb['icon'] ?? '';
                $tabLink = $tb['link'] ?? null;

                if (!empty($tb['dropdown']) && is_array($tb['dropdown'])) {
                    $html .= "<li class=\"nav-item\" role=\"presentation\">
                                <div class=\"btn-group\">
                                    <button type=\"button\" class=\"nav-link dropdown-toggle {$isActive}\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                        {$tabIcon}{$tabTitle}
                                    </button>
                                    <ul class=\"dropdown-menu\">";
                    foreach ($tb['dropdown'] as $dp) {
                        if (!empty($dp['divider'])) {
                            $html .= "<li><hr class=\"dropdown-divider\"></li>";
                        } else {
                            $dpClass = $dp['class'] ?? '';
                            $dpLink = $dp['link'] ?? '#';
                            $dpIcon = $dp['icon'] ?? '';
                            $dpTitle = $dp['title'] ?? '';
                            $html .= "<li><a class=\"dropdown-item {$dpClass}\" href=\"{$dpLink}\">{$dpIcon}{$dpTitle}</a></li>";
                        }
                    }
                    $html .= "</ul></div></li>";
                } else {
                    $action = $tabLink
                        ? "href=\"{$tabLink}\""
                        : "data-bs-toggle=\"tab\" data-bs-target=\"#{$tabId}\"";
                    $html .= "<li class=\"nav-item\" role=\"presentation\">
                                <button class=\"nav-link {$isActive}\" id=\"tab-{$tabId}\" {$action} type=\"button\" role=\"tab\" aria-controls=\"{$tabId}\" aria-selected=\"" . (!empty($tb['active']) ? 'true' : 'false') . "\">
                                    {$tabIcon}{$tabTitle}
                                </button>
                              </li>";
                }
            }
            $html .= "</ul>";

            if ($wrapper) {
                $html .= "<div class=\"tab-content\" id=\"{$id}Content\">";
            }
            break;

        // --- Open body section ---
        case 'openbody':
            $active = !empty($options['active']) ? 'show active' : '';
            $html .= "<div class=\"tab-pane fade {$active}\" id=\"{$id}\" role=\"tabpanel\">";
            break;

        // --- Close body ---
        case 'closebody':
            $html .= "</div>";
            break;

        // --- Footer ---
        case 'footer':
            if ($wrapper) {
                if ($tab_nav) {
                    $html .= "<div class=\"clearfix mt-3\">
                                <a class=\"btn btn-warning btnPrevious me-2\">{$locale['previous']}</a>
                                <a class=\"btn btn-warning btnNext float-end\">{$locale['next']}</a>
                              </div>";
                }
                $html .= "</div>";
            }
            break;
    }

    return $html;
}
