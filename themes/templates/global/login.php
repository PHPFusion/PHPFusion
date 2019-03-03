<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: global/login.php
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
defined('IN_FUSION') || exit;

if (!function_exists("display_loginform")) {
    /**
     * Display Login form
     * @param array $info
     */
    function display_loginform(array $info) {
        global $locale, $userdata, $aidlink;
        opentable($locale['global_100']);
        if (iMEMBER) {
            $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
            opentable($userdata['user_name']);
            echo "<div class='text-center'><br />\n";
            echo THEME_BULLET." <a href='".BASEDIR."edit_profile.php' class='side'>".$locale['global_120']."</a><br />\n";
            echo THEME_BULLET." <a href='".BASEDIR."messages.php' class='side'>".$locale['global_121']."</a><br />\n";
            echo THEME_BULLET." <a href='".BASEDIR."members.php' class='side'>".$locale['global_122']."</a><br />\n";
            if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
                echo THEME_BULLET." <a href='".ADMIN."index.php".$aidlink."' class='side'>".$locale['global_123']."</a><br />\n";
            }
            echo THEME_BULLET." <a href='".BASEDIR."index.php?logout=yes' class='side'>".$locale['global_124']."</a>\n";
            if ($msg_count) {
                echo "<br /><br />\n";
                echo "<strong><a href='".BASEDIR."messages.php' class='side'>".sprintf($locale['global_125'], $msg_count);
                echo ($msg_count == 1 ? $locale['global_126'] : $locale['global_127'])."</a></strong>\n";
            }
            echo "</div>\n";
            closetable();
        } else {
            echo "<div id='login_form' class='panel panel-default text-center text-dark'>\n";
            if (fusion_get_settings("sitebanner")) {
                echo "<a class='display-inline-block' href='".BASEDIR.fusion_get_settings("opening_page")."'><img class='img-responsive' src='".BASEDIR.fusion_get_settings("sitebanner")."' alt='".fusion_get_settings("sitename")."'/></a>\n";
            } else {
                echo "<a class='display-inline-block' href='".BASEDIR.fusion_get_settings("opening_page")."'>".fusion_get_settings("sitename")."</a>\n";
            }
            echo "<div class='panel-body text-center'>\n";
            echo $info['open_form'];
            echo $info['user_name'];
            echo $info['user_pass'];
            echo $info['remember_me'];
            echo $info['login_button'];
            echo $info['registration_link']."<br/><br/>";
            echo $info['forgot_password_link']."<br/><br/>";
            echo $info['close_form'];
            // Facebook, Google Auth, etc.
            if (!empty($info['connect_buttons'])) {
                echo "<hr/>";
                foreach ($info['connect_buttons'] as $mhtml) {
                    echo $mhtml;
                }
            }
            echo "</div>\n";
            echo "</div>\n";
        }
        closetable();
    }
}
