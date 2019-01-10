<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Complete.inc
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
namespace PHPFusion\Steps;

use PHPFusion\Installer\Install_Core;

class InstallerComplete extends Install_Core {

    public function __view() {
        $content = "<h4 class='title'>".self::$locale['setup_1600']."</h4><p>".self::$locale['setup_1601']."</p>\n";
        $content .= "<div class='well'>".self::$locale['setup_1602']."</div>\n";
        $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1603']."</h4></div>\n";
        $content .= "<hr />\n";
        self::$step = [
            1 => [
                'name'  => 'step',
                'class' => 'btn-success',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_EXIT
            ]
        ];

        return $content;
    }
}
