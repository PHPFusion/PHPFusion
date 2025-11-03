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
function get_bootstrap($part, $version = '3') {

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

        $_dir = __DIR__ . '/' . $version . '/templates/';

        $framework_paths['php'] = [
            /**
             * Menu
             * @uses render_navbar()
             */
            'showsublinks' => ['dir' => $_dir, 'file' => 'navbar.tpl.php', 'callback'=>'render_navbar'],
            /**
             * Dynamics UI
             * @uses render_dynamic_ui()
             */
            'form_inputs'  => ['dir' => $_dir, 'file' => 'dynamics-ui.tpl.php', 'callback'=>'render_dynamic_ui'],
            /**
             * Collapse UI
             * @uses render_collapse()
             */
            'collapse'     => ['dir' => $_dir, 'file' => 'collapse.tpl.php', 'callback'=>'render_collapse'],
            /**
             * Tabs UI
             * @uses render_tabs()
             */
            'tabs' => ['dir' => $_dir, 'file' => 'tabs.tpl.php', 'callback'=>'render_tabs'],
            /**
             * Modal UI
             * @uses render_modal()
             */
            'modal' =>  ['dir' => $_dir, 'file' => 'modal.tpl.php', 'callback'=>'render_modal'],
            /**
             * Notice UI
             * @uses render_notices()
             */
            'notices' => ['dir' => $_dir, 'file' => 'notices.tpl.php', 'callback'=>'render_notices'],
        ];
    }

    $_type = 'php';

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

            if (!empty($path['file'])) {

                require_once $path['dir'].$path['file'];
                return call_user_func($path['callback'], $info);

            } else {

                die($path['callback'].' is invalid');
            }

        } else if ( $path = get_bootstrap( $component, 'auto', TRUE ) ) {

            require_once $path['dir'] . $path['file'];

            if ( $callback = call_user_func( $component, $info ) ) {
                return $callback;
            }
        }

        return 'This template ' . $component . ' is not supported';
    }
}

fusion_tab();