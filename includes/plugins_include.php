<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: plugins_include.php
| Authors:  meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

if ($list = makefilelist( INCLUDES . 'plugins/', '.|..', FALSE, 'folders' )) {

    foreach ($list as $folders) {
        $plugin_folder = INCLUDES . 'plugins/' . $folders . '/';

        if (is_file( $plugin_folder . $folders . '_include.php' )) {
            require_once $plugin_folder . $folders . '_include.php';
            // load the
            if (isset( $plugin_files )) {
                foreach ($plugin_files as $files) {
                    require_once $plugin_folder . $files;
                }
            }
        }
    }
}

/**
 * When there are no plugins with get_template enabled during load, then revert system to load template for non-bootstrap version
 */

if (!function_exists( 'fusion_get_template' )) {
    /**
     * @param $component
     * @param $info
     *
     * @return false|mixed|string
     */
    function fusion_get_template( $component, $info ) {

        $default_path = THEMES . 'templates/utils/';

        if ($path = get_fusion_default_template( $component )) {

            /** @noinspection PhpIncludeInspection */
            require_once $default_path . $component['file'];

            return call_user_func( $path['arguments'], $info );
        }

        return 'This template ' . $component . ' is not supported';

    }

    /**
     * @param $component
     *
     * @return string|string[]
     */
    function get_fusion_default_template( $component ) {
        $component_files = [
            'showsublinks' => ['file' => 'navbar.php', 'arguments' => 'navbar_template']
        ];

        return $component_files[$component] ?? '';
    }
}

