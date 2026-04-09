<?php
// Settings page

use PHPFusion\Admins;

defined('IN_FUSION') || exit;

$contents = [
    'view'        => 'pf_view',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'title'       => 'Settings',
    'description' => '',
];

/**
 * @param $icon_class
 * @param $color
 *
 * @return mixed
 */
function add_icon_color($icon_class, $color) {

    add_to_css('.' . $icon_class . ' { background:' . $color . '!important;transition: all 0.3s cubic-bezier(0.1, 0.7, 1, 1 )} a.pf-settings-group:hover > .' . $icon_class . ' {filter: brightness(50%);}');

    return $icon_class;
}

/**
 * Renders an admin settings group link item.
 * * @param array $page The page data array from $admin_pages
 * @return string The formatted HTML
 */
function render_admin_item(array $page): string {
	
	// 2. Build the icon wrapper with dynamic coloring
	$icon_class = add_icon_color($page['admin_rights'] . '-icon', $page['admin_icon_color'] ?? '');
	$icon_html = iconify($page['admin_icon'], class:'admin-ico');
	
	// 3. Assemble the component
	$html  = '<a href="' . ($page['admin_link'] ?? '#') . '" class="pf-settings-group">';
	$html .= '    <span class="' . $icon_class . '">';
	$html .= '        ' . $icon_html;
	$html .= '    </span>';
	$html .= '    <div>';
	$html .= '        <div class="fs-5 fw-semibold ms-0">' . ($page['admin_title'] ?? '') . '</div>';
	$html .= '        <p class="fs-6">' . ($page['admin_description'] ?? '') . '</p>';
	$html .= '    </div>';
	$html .= '</a>';
	
	return $html;
}

function pf_view() {

    // get the admin class and get the icons for the page
    $admin_pages = ( new Admins() )->getAdminPages();

    echo '<h6>System Settings</h6>';
    opengrid(3);
    if ( ! empty($admin_pages[4]) ) {
        foreach ( $admin_pages[4] as $page ) {
			echo render_admin_item($page);
        }
    }
    closegrid();
    echo '<h6>System Preferences</h6>';
    opengrid(3);
    if ( ! empty($admin_pages[3]) ) {
        foreach ( $admin_pages[3] as $page ) {
			echo render_admin_item($page);
        }
    }
    closegrid();
    echo '<h6>Membership</h6>';
    opengrid(3);
    if ( ! empty($admin_pages[2]) ) {
        foreach ( $admin_pages[2] as $page ) {
			echo render_admin_item($page);
        }
    }
    closegrid();
    echo '<h6>PHPFusion</h6>';
    opengrid(3);
    if ( ! empty($admin_pages[6]) ) {
        foreach ( $admin_pages[6] as $page ) {
			echo render_admin_item($page);
        }
    }
    closegrid();
}
