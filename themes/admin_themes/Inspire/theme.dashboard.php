<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.dashboard.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Inspire;

/**
 * Class adminDashboard
 *
 * @package Genesis\Viewer
 */
class Dashboard extends Helper {

    public function adminDashboard() {
        // all these shouldn't be in the theme.
        $dashboard = new \PHPFusion\Administration\Dashboard();
        $dashboard->showWidget();
    }

    public function adminIcons() {
        global $admin_icons, $admin_images;

        $aidlink = self::get_aidlink();
        $locale = fusion_get_locale();
        //add_to_head('<link href="'.THEME.'templates/css/autogrid.css" rel="stylesheet" />');
        opentable($locale['admin_apps']);
        echo "<div class='row'>\n";
        if (count($admin_icons['data']) > 0) {
            foreach ($admin_icons['data'] as $i => $data) {
                echo "<div class='display-table col-xs-6 col-sm-3 col-md-2' style='height:140px;'>\n";
                if ($admin_images) {
                    echo "<div class='panel-body align-middle text-center' style='width:100%;'>\n";
                    echo "<a href='".$data['admin_link'].$aidlink."'><img style='max-width:48px;' src='".get_image("ac_".$data['admin_rights'])."' alt='".$data['admin_title']."'/>\n</a>\n";
                    echo "<div class='overflow-hide'>\n";
                    echo "<a class='icon_title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>\n";
                    echo "</div>\n";
                    echo "</div>\n";
                } else {
                    echo "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
                }
                echo "</div>\n";
            }
        }
        echo "</div>\n";
        closetable();
    }

}
