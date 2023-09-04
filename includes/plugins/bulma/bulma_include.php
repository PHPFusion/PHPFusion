<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bootstrap_include.php
| Author: meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Get bootstrap framework file paths
 *
 * @param $part
 * @param string $version
 *
 * @return string
 */
function get_bulma( $part, $version = '1' ) {
    static $framework_paths = [];

    if (empty( $framework_paths )) {

        $version = 'v' . $version;

        require_once __DIR__ . '/' . $version . '/index.php';

        $framework_paths = [
            'showsublinks' => ['dir' => __DIR__ . '/' . $version . '/', 'file' => 'navbar.twig'],
            'form_inputs'  => ['dir' => __DIR__ . '/' . $version . '/', 'file' => 'dynamics.twig']
        ];
    }

    return $framework_paths[$part] ?? '';

}


if (defined( 'BULMA' )) {

    /**
     * Load bootstrap
     * BOOTSTRAP - version number
     */
    get_bulma( 'load', BULMA );

    /**
     * @uses bootstrap_header()
     */
    fusion_add_hook( 'fusion_header_include', 'bulma_header' );

    /**
     * @uses bootstrap_footer()
     */
    fusion_add_hook( 'fusion_footer_include', 'bulma_footer' );

    /**
     * System template callback function
     * @param $component
     * @param $info
     *
     * @return string
     */
    function fusion_get_template( $component, $info ) {

        if ($path = get_bulma( $component )) {

            return fusion_render( $path['dir'], $path['file'], $info, iDEVELOPER );
        }

        return 'This template ' . $component . ' is not supported';
    }

}

