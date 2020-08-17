<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: form_geomap.json.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| With Codes from PHP-Fusion Communities Authors
+--------------------------------------------------------+
| Registered in phpfusion.com under the username of:
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
require_once __DIR__.'/../../maincore.php';
require_once INCLUDES."geomap/geomap.inc.php";
header('Content-Type: application/json');
echo json_encode(state_search(get('id')));