<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: panels_updater.php
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
require_once __DIR__.'/../maincore.php';
$locale = fusion_get_locale("", LOCALE.LOCALESET.'admin/panels.php');
pageaccess("P");

if (check_get(['listItem']) && get(['listItem'])) {
    $sql_side = "";
    if (check_get('panel_side') && get('panel_side', FILTER_SANITIZE_NUMBER_INT)) {
        $sql_side = ", panel_side='".get('panel_side')."'";
    }
    foreach (get(['listItem']) as $position => $item) {
        if (isnum($position) && isnum($item)) {
            dbquery("UPDATE ".DB_PANELS." SET panel_order='".($position + 1)."'".$sql_side." WHERE panel_id='".$item."'");
        }
    }

    header("Content-Type: text/html; charset=".$locale['charset']."\n");
    echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['PANEL_488']."</div></div>";
}
