<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panels_updater.php
| Author: Hans Kristian Flaatten {Starefossen}
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/panels.php");
pageAccess("P");

if (isset($_GET['listItem']) && is_array($_GET['listItem'])) {
    $sql_side = "";
    if (isset($_GET['panel_side']) && isnum($_GET['panel_side'])) {
        $sql_side = ", panel_side='".$_GET['panel_side']."'";
    }
    foreach ($_GET['listItem'] as $position => $item) {
        if (isnum($position) && isnum($item)) {
            dbquery("UPDATE ".DB_PANELS." SET panel_order='".($position + 1)."'".$sql_side." WHERE panel_id='".$item."'");
        }
    }
    header("Content-Type: text/html; charset=".$locale['charset']."\n");
    echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['488']."</div></div>";
}
