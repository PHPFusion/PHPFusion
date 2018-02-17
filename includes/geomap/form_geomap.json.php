<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_geomap.json.php
| Author : Frederick MC Chan
+--------------------------------------------------------+
| With Codes from PHP-Fusion Communities Authors
+--------------------------------------------------------+
| Registered in php-fusion.co.uk under the username of:
| Falk (Sweden) + Rest not covered by community,
| Basti (Germany), Thomas-SVK (Slovakia),
| afoster (USA), Kamillo (Poland), Dimki (Greece),
| Creatium (Lithuania), douwe_yntema (Netherlands),
| JoiNNN (Romania), EphyxHU (Hungary), afaaro (Somalia),
| Jikaka (Russia)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function root_level() {
    $folder_level = "";
    $i = 0;
    while (!file_exists($folder_level."config.php")) {
        $folder_level .= "../";
        $i++;
        if ($i == 8) {
            die("config.php file not found");
        }
    }
    return $folder_level;
}

$states = [];

$level = root_level();

require_once $level."maincore.php";
require_once INCLUDES."geomap/geomap.inc.php";

$id = (isset($_GET['id']) && ($_GET['id'])) ? form_sanitizer($_GET['id'], "") : '';

$states_array[] = ["id" => "Other", "text" => fusion_get_locale('other_states')];

foreach ($states as $key => $value) {
    if ($id == $key) {
        if (!empty($value)) {
            $array = [];
            $rows = count($value);
            $i = 0;

            foreach ($value as $name => $region) {
                $states_array[] = ['id' => "".$region."", 'text' => "".$region.""];
            }
        }
    }
}

header('Content-Type: application/json');

echo json_encode($states_array);
