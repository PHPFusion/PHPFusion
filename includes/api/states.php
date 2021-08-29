<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: states.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

/**
 * Get states
 *
 * Used by form_geo dynamic UI to query states.
 */
function _get_states() {
    require_once INCLUDES.'ajax_include.php';
    $states = [];
    require_once INCLUDES.'geomap/geo.states.php';
    $id = get('id');

    $states_array = [];
    $states += ['id' => 'Other', 'text' => fusion_get_locale('other_states')];

    foreach ($states as $key => $value) {
        if ($id == $key) {
            if (!empty($value)) {
                foreach ($value as $region) {
                    $states_array[] = ['id' => $region, 'text' => $region];
                }
            }
        }
    }

    header_content_type('json');
    echo json_encode($states_array);
}

/**
 * @uses _get_states()
 */
fusion_add_hook('fusion_filters', '_get_states');
