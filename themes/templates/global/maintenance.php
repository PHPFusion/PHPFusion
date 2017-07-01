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
        echo "<section class='maintenance container'>\n";
        $notices = getNotices();
        if ($notices) {
            echo renderNotices($notices);
        }

        echo "<div class='m-t-20 jumbotron text-center'>\n";
        echo "<img src='".fusion_get_settings("sitebanner")."' alt='".fusion_get_settings("sitename")."'/>\n";
        echo "<h1><strong>".fusion_get_settings("sitename")."</strong></h1>\n";
        $message = fusion_get_settings("maintenance_message");
        if (!empty($message)) {
            echo "<h1 class='m-b-20'>".stripslashes(nl2br(fusion_get_settings("maintenance_message")))."</h1>\n";
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
            if (iADMIN) {
                $siteurl = fusion_get_settings('siteurl').fusion_get_settings('opening_page');
                echo '<a class="display-inline-block pull-left m-r-10" href="'.$siteurl.'">';
                echo '<i class="fa fa-home fa-fw"></i> '.fusion_get_locale('home');
                echo '</a>';
                echo '<a class="display-inline-block pull-left" href="'.ADMIN.'index.php'.fusion_get_aidlink().'">';
                echo '<i class="fa fa-dashboard fa-fw"></i> '.fusion_get_locale('global_123');
                echo '</a>';
            }

            echo '<a href="'.BASEDIR.'index.php?logout=yes" class="btn btn-primary">';
            echo '<i class="fa fa-sign-out"></i> '.fusion_get_locale('global_124');
            echo '</a>';
        }
        echo "</div>\n";
        echo "<div class='text-center'>\n";
        echo showcopyright();
        echo showcounter();
        echo showMemoryUsage();
        echo "</div>\n";
        echo "</section>\n";
    }
}
