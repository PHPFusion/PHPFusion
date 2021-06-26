<?php
defined('IN_FUSION')||exit;

/**
 * Used by form_geo dynamic UI to query states.
 */
function _get_states() {
    require_once INCLUDES.'ajax_include.php';
    require_once INCLUDES.'geomap/geo.states.php';
    $id = get('id');
    $states_array[] = ["id" => "Other", "text" => fusion_get_locale('other_states')];
    foreach ($states as $key => $value) {
        if ($id == $key) {
            if (!empty($value)) {
                foreach ($value as $name => $region) {
                    $states_array[] = ['id' => $region, 'text' => $region];
                }
            }
        }
    }

    header_content_type('json');
    echo json_encode($states_array);
}

fusion_add_hook('fusion_filters', '_get_states');
