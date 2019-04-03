<?php
// Pair to a geomap file
$all_countries = Geomap::get_Country();
$all_countries = array_flip($all_countries);
print_p($all_countries);
// Make a json with the following attributes
require_once INCLUDES.'geomap/geomap.inc.php';
foreach($states as $country_name => $original_state) {
    if (isset($all_countries[$country_name])) {
        $state_array[$all_countries[$country_name]] = $original_state;
    }
}
ksort($state_array);
$state_json = json_encode($state_array, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
print_p($state_json);