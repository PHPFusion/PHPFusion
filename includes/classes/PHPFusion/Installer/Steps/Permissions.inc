<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Permissions.inc
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Installer\Steps;

use PHPFusion\Installer\Install_Core;
use PHPFusion\Installer\Requirements;

class InstallerPermissions extends Install_Core {

    public function __view() {

        $content = "<h4 class='title'>".self::$locale['setup_1106']."</h4>\n";
        $content .= "<hr/>\n";

        $content .= "<div class='table-responsive'><table class='table  table-hover'>\n";
        $system_health = 10;
        $system_requirements = Requirements::get_system_requirements();
        foreach ($system_requirements as $test) {
            $class = '';
            if (isset($test['severability'])) {
                $system_health = $system_health - intval($test['severability']);
                if ($test['severability'] > 5) {
                    $class = "alert";
                } else {
                    $class = "warning";
                }
            }
            $content .= "<tr ".($class ? "class='$class'" : '').">\n";
            $content .= "<td>\n<strong>".$test['title']."</strong></td>\n";
            $content .= "<td>\n";
            $content .= (isset($test['value']) ? $test['value'].'<br />' : '').(isset($test['description']) ? $test['description'] : '');
            if (isset($test['sub'])) {
                $warned_content = '';
                $show_sub = FALSE;
                foreach ($test['sub'] as $key => $value) {
                    if (isset($value['severability'])) {
                        $show_sub = TRUE;
                        $warned_content .= "<tr><td>$key</td><td>$value</td></tr>\n";
                    }
                }

                if ($show_sub === TRUE) {
                    $content .= "<div class='m-t-20'>\n";
                    $content .= "<table class='table'><tr><th>".self::$locale['setup_1090']."</th><th>".self::$locale['setup_1091']."</th></tr>";
                    $content .= $warned_content;
                    $content .= "</table>";
                    $content .= "</div>";
                }
            }
            $content .= "</td>\n";
            $content .= "</tr>\n";
        }
        $content .= "</table></div>\n<br />";
        // can proceed
        if ($system_health > 6) {
            self::$step = [
                1 => [
                    'name'  => 'step',
                    'label' => self::$locale['setup_0121'],
                    'value' => self::STEP_DB_SETTINGS_FORM
                ]
            ];
        } else {
            self::$step = [
                1 => [
                    'name'  => 'step',
                    'type'  => 'tryagain',
                    'label' => self::$locale['setup_0122'],
                    'value' => self::STEP_DB_SETTINGS_FORM
                ]
            ];
            $content .= form_hidden('license', '', '1');
        }

        return $content;
    }

}
