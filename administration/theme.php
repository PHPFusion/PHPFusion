<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('TS');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/theme.php');
$theme_admin = new \PHPFusion\Atom\Admin();

opentable($locale['theme_1000']);
switch (get('action')) {
    case "manage":
        if (check_get('theme')) {
            $theme_admin::display_theme_editor(get('theme'));
        }
        break;
    default:
        $tabs['title'] = [$locale['theme_1010'], $locale['theme_1011']];
        $tabs['id'] = ["list", "upload"];
        $active_set = post('upload') ? 1 : 0;
        $active_tab = tab_active($tabs, $active_set);
        echo opentab($tabs, $active_tab, 'theme_tab');
        echo opentabbody($tabs['title'][0], $tabs['id'][0], $active_tab);
        $theme_admin::display_theme_list();
        echo closetabbody();
        echo opentabbody($tabs['title'][1], $tabs['id'][1], $active_tab);
        $theme_admin::theme_uploader();
        echo closetabbody();
        echo closetab();
        break;
}
closetable();
require_once THEMES.'templates/footer.php';
