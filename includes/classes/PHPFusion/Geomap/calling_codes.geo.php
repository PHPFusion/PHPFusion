<?php
require_once __DIR__.'/../../../../maincore.php';

/**
 * $q - iso2 country code
 */
if (!empty($_REQUEST['q'])) {
    $_REQUEST['q'] = stripinput($_REQUEST['q']);
    $result = \PHPFusion\Geomap::get_CallingCodes($_REQUEST['q']);
    if (!empty($result)) {
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
