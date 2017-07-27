<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: functions.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
// Step 1 - determine the total side length
function total_side_span($value) {
    $count = 0;
    if (defined('LEFT') && LEFT) {
        $count = $count + $value;
    }
    if (defined('RIGHT') && RIGHT) {
        $count = $count + $value;
    }
    if ($count > 12) {
        $count = 12;
    }

    return $count;
}

// Step 2 - get the balance out of max 12 for center settings after deduction of total side_length
function center_grid_settings($side_grid_settings) {
    return array(
        'desktop_size' => (12 - total_side_span($side_grid_settings['desktop_size'])) > 0 ? 12 - total_side_span($side_grid_settings['desktop_size']) : 12,
        'laptop_size'  => (12 - total_side_span($side_grid_settings['laptop_size'])) > 0 ? 12 - total_side_span($side_grid_settings['laptop_size']) : 12,
        'tablet_size'  => (12 - total_side_span($side_grid_settings['tablet_size'])) > 0 ? 12 - total_side_span($side_grid_settings['tablet_size']) : 12,
        'phone_size'   => (12 - total_side_span($side_grid_settings['phone_size'])) > 0 ? 12 - total_side_span($side_grid_settings['phone_size']) : 12,
    );
}

// Step 3 - Output of various css grid class required
function html_prefix(array $array) {
    $array['phone_size'] = ($array['phone_size'] == 0) ? 'hidden-xs' : 'col-xs-'.$array['phone_size'];
    $array['tablet_size'] = ($array['tablet_size'] == 0) ? 'hidden-sm' : 'col-sm-'.$array['tablet_size'];
    $array['laptop_size'] = ($array['laptop_size'] == 0) ? 'hidden-md' : 'col-md-'.$array['laptop_size'];
    $array['desktop_size'] = ($array['desktop_size'] == 0) ? 'hidden-lg' : 'col-lg-'.$array['desktop_size'];

    return "".$array['phone_size']." ".$array['tablet_size']." ".$array['laptop_size']." ".$array['desktop_size']."";
}

// End of bootstrap calculate

// Standard open and close functions

function openside($title) {
    echo "<h4>$title</h4>\n";
    echo "<div class='list-group-item'>\n";
}

function closeside() {
    echo "</div>\n";
}

function opentable($title) {
    echo "<h3>$title</h3>\n";
}

function closetable() {
    echo " ";
}


