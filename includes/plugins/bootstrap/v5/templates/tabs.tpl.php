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
function render_tabs(array $options = []): string
{
	$tab_config = $options['tabs'] ?? [];
	$tabs = [];
	$tab_active = $options['active'] ?? 0;
	foreach ($tab_config['title'] as $key => $val) {
		$tabs[] = [
			'title'  => $tab_config['title'][$key],
			'id'     => $tab_config['id'][$key],
			'icon'   => $tab_config['icon'][$key],
			// Set the first item as active by default, or leave as false
			'active' => $key === $tab_active
		];
	}
	
	$id = $options['id'] ?? 'custom-tabs'; // Fallback ID
	$part = $options['part'] ?? 'header';
	
	$class = $options['class'] ?? 'nav-pills'; // Default to pills or nav-tabs
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
				$isSelected = !empty($tb['active']) ? 'true' : 'false';
				$tabId = htmlspecialchars($tb['id'] ?? '');
				$tabTitle = $tb['title'] ?? 'NA';
				$tabIcon = !empty($tb['icon']) ? "<i class=\"{$tb['icon']} me-2\"></i>" : '';
				$tabLink = $tb['link'] ?? null;
				
				// Handle Dropdowns
				if (!empty($tb['dropdown']) && is_array($tb['dropdown'])) {
					$html .= "<li class=\"nav-item dropdown\" role=\"presentation\">
                                <a class=\"nav-link dropdown-toggle {$isActive}\" data-bs-toggle=\"dropdown\" href=\"#\" role=\"button\" aria-expanded=\"false\">
                                    {$tabIcon}{$tabTitle}
                                </a>
                                <ul class=\"dropdown-menu\">";
					foreach ($tb['dropdown'] as $dp) {
						if (!empty($dp['divider'])) {
							$html .= "<li><hr class=\"dropdown-divider\"></li>";
						} else {
							$dpClass = $dp['class'] ?? '';
							$dpLink = $dp['link'] ?? '#';
							$dpIcon = !empty($dp['icon']) ? "<i class=\"{$dp['icon']} me-2\"></i>" : '';
							$dpTitle = $dp['title'] ?? '';
							$html .= "<li><a class=\"dropdown-item {$dpClass}\" href=\"{$dpLink}\">{$dpIcon}{$dpTitle}</a></li>";
						}
					}
					$html .= "</ul></li>";
				}
				// Handle Standard Tabs
				else {
					$target = $tabLink ? "href=\"{$tabLink}\"" : "data-bs-toggle=\"tab\" data-bs-target=\"#{$tabId}\"";
					$role = $tabLink ? "" : "role=\"tab\"";
					
					$html .= "<li class=\"nav-item\" role=\"presentation\">
                                <button class=\"nav-link {$isActive}\" id=\"tab-{$tabId}\" {$target} type=\"button\" {$role} aria-controls=\"{$tabId}\" aria-selected=\"{$isSelected}\">
                                    {$tabIcon}{$tabTitle}
                                </button>
                              </li>";
				}
			}
			$html .= "</ul>";
			
			if ($wrapper) {
				$html .= "<div class=\"tab-content mt-3\" id=\"{$id}Content\">";
			}
			break;
		
		// --- Open body section (The individual Pane) ---
		case 'openbody':
			// Important: $id here should be the specific Tab ID passed in $options
			$active = !empty($options['active']) ? 'show active' : '';
			$html .= "<div class=\"tab-pane fade {$active}\" id=\"{$id}\" role=\"tabpanel\" aria-labelledby=\"tab-{$id}\">";
			break;
		
		// --- Close body ---
		case 'closebody':
			$html .= "</div>";
			break;
		
		// --- Footer ---
		case 'footer':
			if ($wrapper) {
				if ($tab_nav) {
					$html .= "<div class=\"d-flex justify-content-between mt-3\">
                                <button type=\"button\" class=\"btn btn-secondary btnPrevious\">{$locale['previous']}</button>
                                <button type=\"button\" class=\"btn btn-primary btnNext\">{$locale['next']}</button>
                              </div>";
				}
				$html .= "</div>"; // Close tab-content
			}
			break;
	}
	
	return $html;
}