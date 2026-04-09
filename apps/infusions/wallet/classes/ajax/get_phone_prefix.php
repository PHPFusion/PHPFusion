<?php

require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

function check_phone($value) {
    return \PHPFusion\Geomap::get_CallingCodes($value);
}

echo json_encode(['phone_prefix' => "+".check_phone(get('country'))]);
