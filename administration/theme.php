<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Frederick MC Chan (Chan)
| Co-Author: PHP-Fusion Development Team
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
pageAccess('S1');
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/theme.php');
$theme_admin = new \PHPFusion\Atom\Admin();

opentable($locale['theme_1000']);
switch ($_GET['action']) {
    case "manage":
        if (isset($_GET['theme'])) {
            echo "<div class='m-t-20'>\n";
            $theme_admin::display_theme_editor($_GET['theme']);
            echo "</div>\n";
        }
        break;
    default:
        $tab_title['title'] = array($locale['theme_1010'], $locale['theme_1011']);
        $tab_title['id'] = array("list", "upload");
        $active_set = isset($_POST['upload']) ? 1 : 0;
        $active_tab = tab_active($tab_title, $active_set);
        echo opentab($tab_title, $active_tab, 'theme_tab');
        echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $active_tab);
        echo "<div class='m-t-20'>\n";
        $theme_admin::display_theme_list();
        echo "</div>\n";
        echo closetabbody();
        echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $active_tab);
        echo "<div class='m-t-20'>\n";
        $theme_admin::theme_uploader();
        echo "</div>\n";
        echo closetabbody();
        echo closetab();
        break;
}
closetable();
require_once THEMES."templates/footer.php";
