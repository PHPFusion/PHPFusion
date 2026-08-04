<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Project File: Location ajax parsing
| Filename: location.json.php
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
require_once __DIR__.'/../../../../maincore.php';

$states = [];
include INCLUDES."geomap/geo.countries.php";

$q = isset($_GET['q']) ? $_GET['q'] : '';

$found = 0;

header('Content-Type: application/json');

foreach($countries as $cca => $country_array) {
    
    $country_name = $country_array['name'];
    $country_code = $cca;

     if (preg_match('/^'.$q.'/', $country_name, $matches)) {

        //$country_id = $country_array['id'];    
        $country[] = ['id' => $cca, 'text' => $country_name, 'flag' => 'flag_'.str_replace(" ", "_",$country_name).'.png'];
     }
}

echo json_encode($country);
