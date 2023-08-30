<?php
/**
 * Load icon package files
 */
function get_webicons() {
    if (is_array( WEBICON )) {
        foreach (WEBICON as $ico_set) {
            if (is_file( __DIR__ . '/' . $ico_set . '/index.php' )) {
                require_once __DIR__ . '/' . $ico_set . '/index.php';

                /**
                 * @uses register_fa5
                 * @uses register_fa6
                 * @uses register_bootstrap_icons
                 * @uses register_phpfusion_icons
                 */
                fusion_add_hook( 'fusion_header_include', 'register_' . str_replace('-', '_', $ico_set ));
            }
        }
    }
}

if (defined( 'WEBICON' )) {
    /**
     * Load bootstrap
     * BOOTSTRAP - version number
     */
    get_webicons();
}

//@todo register icons to image repo icons from theme