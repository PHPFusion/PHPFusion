<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme.php
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
require_once THEMES.'templates/admin_header.php';
pageaccess('TS');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/theme.php');
$theme_admin = new \PHPFusion\Atom\Admin();

opentable($locale['theme_1000']);
switch (get('action')) {
    case "manage":
        if (check_get('theme')) {
            $theme_admin::displayThemeEditor(get('theme'));
        }
        break;
    default:
        $tabs['title'][] = $locale['theme_1010'];
        $tabs['id'][] = 'list';
        $tabs['icon'][] = '';

        $tabs['title'][] = $locale['theme_1011a'];
        $tabs['id'][] = 'admin_themes';
        $tabs['icon'][] = '';

        $allowed_sections = ['list', 'admin_themes'];
        $section = in_array(get('section'), $allowed_sections) ? get('section') : 'list';

        echo opentab($tabs, $section, 'theme_tab', TRUE);
        switch ($section) {
            case 'admin_themes':
                $theme_admin::adminThemesList();
                break;
            default:
                $theme_admin::displayThemeList();
                break;
        }
        echo closetab();
        break;
}
closetable();
require_once THEMES.'templates/footer.php';
