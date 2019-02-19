<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Location ajax parsing
| Filename: location.json.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'../../../maincore.php';

include INCLUDES."geomap/geomap.inc.php";

$q = isset($_GET['q']) ? $_GET['q'] : '';

$found = 0;

header('Content-Type: application/json');

foreach (array_keys($states) as $k) { // type the country then output full states
    if (preg_match('/^'.$q.'/', $k, $matches)) {
        $states_list = map_country($states, $k);
        echo json_encode($states_list);
        $found = 1;
    }
}

if (!$found) { // a longer version
    $region_list = map_region($states);
    if (array_key_exists($q, $region_list)) {
        echo json_encode($region_list[$q]);
    }
}
