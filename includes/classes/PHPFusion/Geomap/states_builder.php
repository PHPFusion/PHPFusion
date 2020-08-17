<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: states_builder.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
// Pair to a geomap file
$all_countries = PHPFusion\Geomap::get_Country();
$all_countries = array_flip($all_countries);
print_p($all_countries);
// Make a json with the following attributes
require_once INCLUDES.'geomap/geomap.inc.php';
foreach($all_countries as $country_name => $original_state) {
    if (isset($all_countries[$country_name])) {
        $state_array[$all_countries[$country_name]] = $original_state;
    }
}
ksort($state_array);
$state_json = json_encode($state_array, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
