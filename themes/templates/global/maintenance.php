<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/maintenance.php
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

/**
 * Displays the maintenance page
 * @param array $info - Form fields
 */
if (!function_exists("display_maintenance")) {
    function display_maintenance(array $info) {
        $settings = fusion_get_settings();

        echo "<section class='maintenance container'>\n";
        $notices = getNotices();
        if ($notices) {
            echo renderNotices($notices);
        }

        echo "<div class='m-t-20 jumbotron text-center'>\n";
        echo "<img class='img-responsive center-x' src='".$settings['sitebanner']."' alt='".$settings['sitename']."'/>\n";
        echo "<h3><b>".$settings['sitename']."</b></h3>\n";

        if (!empty($settings['maintenance_message'])) {
            echo parse_textarea($settings['maintenance_message'], TRUE, FALSE, TRUE, IMAGES, TRUE);
        }

        if (!empty($info)) {
            echo "<hr/>\n";
            echo "<div class='well clearfix m-t-20 p-20 p-b-0'>\n";
            echo $info['open_form'];
            echo "<div class='col-xs-12 col-sm-4'>".$info['user_name']."</div>\n";
            echo "<div class='col-xs-12 col-sm-4'>".$info['user_pass']."</div>\n";
            echo "<div class='col-xs-12 col-sm-4'>".$info['login_button']."</div>\n";
            echo "</div>\n";
            echo $info['close_form'];
        } else {
            echo '<div><a href="'.BASEDIR.'index.php?logout=yes" class="btn btn-primary"><i class="fa fa-sign-out"></i> '.fusion_get_locale('global_124').'</a></div>';
            if (iADMIN) {
                $siteurl = $settings['siteurl'].$settings['opening_page'];
                echo '<a class="m-r-10" href="'.$siteurl.'"><i class="fa fa-home fa-fw"></i> '.fusion_get_locale('home').'</a>';
                echo '<a href="'.ADMIN.'index.php'.fusion_get_aidlink().'"><i class="fa fa-dashboard fa-fw"></i> '.fusion_get_locale('global_123').'</a>';
            }
        }
        echo "</div>\n";
        echo "<div class='text-center'>\n";
        echo showcopyright();
        echo '<div>'.showcounter().'</div>';
        echo "</div>\n";
        echo "</section>\n";
    }
}
