<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: login.tpl.php
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

defined('IN_FUSION') || exit;

if (!function_exists("display_auth_form")) {
    function display_auth_form($info) {
        $locale = fusion_get_locale();
        ?>
        <style>
            #pin-field input {
                font-size: 150%;
                max-width: 50px;
                height: 50px;
                padding: .4rem;
                text-align: center;
            }
        </style>
        <div class="display-flex flex-row justify-center">
            <div class="spacer-lg">
                <?php
                opentable();
                ?>
                <div class="ui m-b-20">
                    <div class="m-b-20">
                        <h3 class="m-b-20"><?php echo $locale["global_115"] ?> 📬</h3>
                        <p class="word-break">
                            <?php echo $locale["global_116"] ?>
                        </p>
                        <p>
                            <strong>
                                <?php echo $info["email"] ?>
                            </strong>
                        </p>
                    </div>
                    <div class="text-left">
                        <?php echo $info["open_form"] ?>
                        <div class="spacer-sm">
                            <strong><?php echo sprintf($locale["global_117"], fusion_get_settings("auth_login_length")) ?></strong>
                        </div>
                        <?php echo $info["user_two_factor"] ?>
                        <div class="spacer-sm text-center">
                            <?php echo $info["resend_button"] ?>
                        </div>
                        <?php echo $info["close_form"] ?>
                    </div>
                    <div class="m-t-20 text-center">
                        <h5>
                            <a href="  <?php echo $info["restart_login_link"] ?>"><?php echo get_icon("reset", "m-r-5 fa-fw") ?>Restart Login</a>
                        </h5>
                    </div>
                    <div class="m-t-20 text-sm text-center">
                        <a class="strong" href="<?php echo BASEDIR.fusion_get_settings("opening_page") ?>">Back to <?php echo fusion_get_settings("sitename") ?></a>
                    </div>
                </div>
                <?php
                closetable();
                ?>
            </div>

        </div>
        <?php

    }
}


if (!function_exists("display_login_form")) {
    /**
     * Display Login form
     *
     * @param array $info
     */
    function display_login_form(array $info) {
        global $locale, $userdata, $aidlink;
        opentable($locale['global_100']);
        if (iMEMBER) {
            $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
            opentable($userdata['user_name']);
            echo "
                <div class='text-center'><br/>\n";
            echo "<a href='".BASEDIR."edit_profile.php' class='side'>".$locale['global_120']."</a><br/>\n";
            echo "<a href='".BASEDIR."messages.php' class='side'>".$locale['global_121']."</a><br/>\n";
            echo "<a href='".BASEDIR."members.php' class='side'>".$locale['global_122']."</a><br/>\n";
            if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
                echo "<a href='".ADMIN."index.php".$aidlink."' class='side'>".$locale['global_123']."</a><br/>\n";
            }
            echo "<a href='".BASEDIR."index.php?logout=yes' class='side'>".$locale['global_124']."</a>\n";
            if ($msg_count) {
                echo "<br/><br/>\n";
                echo "<strong><a href='".BASEDIR."messages.php' class='side'>".sprintf($locale['global_125'], $msg_count);
                echo ($msg_count == 1 ? $locale['global_126'] : $locale['global_127'])."</a></strong>\n";
            }
            echo "
</div>\n";
            closetable();
        } else {
            echo "
<div id='login_form' class='panel panel-default text-center text-dark'>\n";
            if (fusion_get_settings("sitebanner")) {
                echo "<a class='display-inline-block' href='".BASEDIR.fusion_get_settings("opening_page")."'><img class='img-responsive' src='".BASEDIR.fusion_get_settings("sitebanner")."' alt='".fusion_get_settings("sitename")."'/></a>\n";
            } else {
                echo "<a class='display-inline-block' href='".BASEDIR.fusion_get_settings("opening_page")."'>".fusion_get_settings("sitename")."</a>\n";
            }
            echo "
    <div class='panel-body text-center'>\n";
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
                echo "
        <hr/>
        ";
                foreach ($info['connect_buttons'] as $mhtml) {
                    echo $mhtml;
                }
            }
            echo "
    </div>
    \n";
            echo "
</div>\n";
        }
        closetable();
    }
}
