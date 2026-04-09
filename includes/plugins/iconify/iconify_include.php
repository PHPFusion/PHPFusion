<?php
/**
 * Function based on iconify service
 * https://iconify.design/
 * https://icon-sets.iconify.design/
 * @param $icon
 * @param string $set
 * @param string $class
 * @return string
 */
function iconify($icon, $set = 'heroicons-outline', $class = 'text-dark') {
	return "<iconify-icon icon='{$set}:{$icon}' class='{$class}'></iconify-icon>";
}

function load_iconify() {
	echo '<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>';
}

/**
 * @uses load_iconify()
 */
fusion_add_hook( 'fusion_header_include', 'load_iconify' );