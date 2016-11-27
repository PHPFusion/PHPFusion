<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /Nebula/Templates/Page.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace ThemePack\Nebula\Templates;

use PHPFusion\Panels;
use ThemeFactory\Core;

class Page extends Core {

    public static function display_page($info) {

        /*FusionTheme Controller
         * This will tell the theme to hide all right panel
         * It was meant to be a designer feature.
         */
        //echo render_breadcrumbs();
        self::setParam('body_container', FALSE);
        if (isset($_GET['viewpage']) && $_GET['viewpage'] == 1) {
            self::setParam('headerBg', FALSE);
        }

        // Uncomment this to allow User Use Right Panel
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
		
        // cp_idx
        if (!empty($info['error'])) :
            echo "<div class='well text-center'>".$info['error']."</div>\n";
        else:
            echo $info['body'];
        endif;
        }
}