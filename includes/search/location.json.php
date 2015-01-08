<?php
/*-------------------------------------------------------+
| Guildsquare
| Copyright (C) 2014 - 2014 Guildsquare
| http://www.guildsquare.com/
+--------------------------------------------------------+
| Project File: Location ajax parsing
| Filename: location.json.php
+--------------------------------------------------------*/

require_once dirname(__FILE__).'../../../maincore.php';
if (!defined("IN_FUSION")) {die("Access Denied");}
include INCLUDES."geomap/geomap.inc.php";
$q = $_GET['q'];
$found = 0;
foreach(array_keys($states) as $k) { // type the country then output full states
	if (preg_match('/^'.$q.'/', $k, $matches)) {
		$states_list = map_country($states, $k);
		//print_p($states_list);
		echo json_encode($states_list);
		$found = 1;
	}
}

if (!$found) { // a longer version
	$region_list = map_region($states);
	if (array_key_exists($q, $region_list)) {
		//print_p($region_list[$q]);
		echo json_encode($region_list[$q]);
	}
}



?>