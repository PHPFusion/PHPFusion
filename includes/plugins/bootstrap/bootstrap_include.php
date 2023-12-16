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
 * @param        $part
 * @param string $version
 *
 * @return string
 */
function get_bootstrap( $part, $version = '3', $php = FALSE ) {

    static $framework_paths = [];

    if ( empty( $framework_paths ) ) {

        if ( $version < 3 ) {
            $version = 3;
        } else if ( $version > 5 ) {
            $version = 5;
        }
        $version = 'v' . $version;

        // Headers and footers
        require_once __DIR__ . '/' . $version . '/index.php';

        $_dir = __DIR__ . '/' . $version . '/';

        $framework_paths['php'] = [
            'showsublinks' => ['dir' => $_dir, 'file' => 'navbar.php'],
            'form_inputs'  => ['dir' => $_dir, 'file' => 'dynamics.php'],
            'collapse'     => ['dir' => $_dir, 'file' => 'collapse.php']
        ];

        $framework_paths['twig'] = [
            'showsublinks' => ['dir' => __DIR__ . '/' . $version . '/', 'file' => 'navbar.twig'],
            'form_inputs'  => ['dir' => __DIR__ . '/' . $version . '/', 'file' => 'dynamics.twig']
        ];

    }

    $_type = $php ? 'php' : 'twig';

    return $framework_paths[$_type][$part] ?? '';
}

if ( defined( 'BOOTSTRAP' ) ) {

    /**
     * Load bootstrap
     * BOOTSTRAP - version number
     */
    get_bootstrap( 'load', BOOTSTRAP );

    /**
     * @uses bootstrap_header()
     */
    fusion_add_hook( 'fusion_header_include', 'bootstrap_header' );

    /**
     * @uses bootstrap_footer()
     */
    fusion_add_hook( 'fusion_footer_include', 'bootstrap_footer' );


    /**
     * System template callback function
     *
     * @param $component
     * @param $info
     *
     * @return string
     */
    function fusion_get_template( $component, $info ) {

        if ( $path = get_bootstrap( $component ) ) {

            return fusion_render( $path['dir'], $path['file'], $info, defined( 'FUSION_TPL_DEBUG') );

        } else if ( $path = get_bootstrap( $component, 'auto', TRUE ) ) {

            require_once $path['dir'] . $path['file'];

            if ( $callback = call_user_func( $component, $info ) ) {
                return $callback;
            }
        }

        return 'This template ' . $component . ' is not supported';
    }
}

