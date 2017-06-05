<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: InfusionsSetup.inc
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

use PHPFusion\Installer\Infusion_Core;
use PHPFusion\Installer\Install_Core;
use PHPFusion\Installer\Requirements;

class InstallerInfusions extends Install_Core {

    public function __view() {

        if (!defined('IN_FUSION')) {
            define('IN_FUSION', TRUE);
        }

        self::$connection = self::fusion_get_config(BASEDIR.'config_temp.php');

        require_once(INCLUDES.'multisite_include.php');
        require_once(INCLUDES.'infusions_include.php');

        $validation = Requirements::get_system_validation();

        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");

        if (isset($validation[3])) {

            /*
             * Use DB superadmin password.
             */
            $userdata = fusion_get_user(1);
            $settings = fusion_get_settings();
            // User level, Admin Rights & User Group definitions
            define("iGUEST", $userdata['user_level'] == 0 ? 1 : 0);
            !defined('iMEMBER') ? define("iMEMBER", $userdata['user_level'] <= -101 ? 1 : 0) : "";
            define("iADMIN", $userdata['user_level'] <= -102 ? 1 : 0);
            define("iSUPERADMIN", $userdata['user_level'] == -103 ? 1 : 0);
            define("iUSER", $userdata['user_level']);
            define("iUSER_RIGHTS", $userdata['user_rights']);
            define("iUSER_GROUPS", substr($userdata['user_groups'], 1));
            // Get enabled language settings
            $enabled_languages = array_keys(fusion_get_enabled_languages());
            // If language change is initiated and if the selected language is valid
            if (isset($_GET['lang']) && valid_language($_GET['lang'])) {
                $lang = stripinput($_GET['lang']);
                set_language($lang);
                $redirectPath = clean_request("", array("lang"), FALSE);
                redirect($redirectPath);
            }
            // Main language detection procedure
            if (iMEMBER && valid_language($userdata['user_language'])) {
                define("LANGUAGE", $userdata['user_language']);
                define("LOCALESET", $userdata['user_language']."/");
            } else {
                $data = dbarray(dbquery("SELECT * FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
                if ($data['user_language']) {
                    define("LANGUAGE", $data['user_language']);
                    define("LOCALESET", $data['user_language']."/");
                }
            }
            // Check if definitions have been set, if not set the default language to system language
            if (!defined("LANGUAGE")) {
                define("LANGUAGE", $settings['locale']);
                define("LOCALESET", $settings['locale']."/");
            }

            Infusion_Core::getInstance()->load_Configuration();

            add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['412']."');});");

            if (($folder = filter_input(INPUT_POST, 'infuse'))) {
                Infusion_Core::getInstance()->infuse($folder);
            } elseif ($folder = filter_input(INPUT_POST, 'defuse')) {
                Infusion_Core::getInstance()->defuse($folder);
            }

            $content = "";
            $temp = opendir(INFUSIONS);
            $infs = array();
            while ($folder = readdir($temp)) {
                if (!in_array($folder, array("..", ".")) && ($inf = Infusion_Core::load_infusion($folder))) {
                    $infs[] = $inf;
                }
            }
            closedir($temp);
            $content .= "<div>\n";
            $content .= renderNotices(getNotices());

            if ($infs) {
                $content .= "<div class='list-group'>\n";
                $content .= "<div class='list-group-item hidden-xs'>\n";
                $content .= "<div class='row'>\n";
                $content .= "<div class='col-xs-3 col-sm-3 col-md-2 col-lg-2'>\n<strong>".$locale['419']."</strong></div>\n";
                $content .= "<div class='col-xs-7 col-sm-7 col-md-4 col-lg-4'>\n<strong>".$locale['400']."</strong></div>\n";
                $content .= "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2'>\n<strong>".$locale['418']."</strong></div>\n";
                $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
                $content .= "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0'>\n<strong>".$locale['421']."</strong></div>\n";
                $content .= "</div>\n</div>\n";

                foreach ($infs as $i => $inf) {
                    $content .= "<div class='list-group-item'>\n";
                    $content .= "<div class='row'>\n";
                    $content .= "<div class='col-xs-3 col-sm-3 col-md-2 col-lg-2'>\n";
                    if ($inf['status'] > 0) {
                        if ($inf['status'] > 1) {
                            $content .= form_button('infuse', $locale['416'], $inf['folder'],
                                array('class' => 'btn-info m-t-5 btn-sm infuse', 'icon' => 'fa fa-cube'));
                        } else {
                            $content .= form_button('defuse', $locale['411'], $inf['folder'],
                                array('class' => 'btn-default btn-sm m-t-5 defuse', 'icon' => 'fa fa-trash'));
                        }
                    } else {
                        $content .= form_button('infuse', $locale['401'], $inf['folder'],
                            array('class' => 'btn-primary btn-sm m-t-5 infuse', 'icon' => 'fa fa-magnet'));
                    }
                    $content .= "</div>\n";
                    $content .= "<div class='col-xs-6 col-sm-7 col-md-4 col-lg-4'><strong>".$inf['name']."</strong><br/>".trimlink($inf['description'], 30)."</div>\n";
                    $content .= "<div class='col-xs-3 col-sm-2 col-md-2 col-lg-2'>".($inf['status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
                    $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf['version'] ? $inf['version'] : '')."</div>\n";
                    $content .= "<div class='col-xs-9 col-xs-offset-3 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-1 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".($inf['developer'] ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '')."</div>\n";
                    $content .= "</div>\n</div>\n";
                }
            } else {
                $content .= "<br /><p class='text-center'>".$locale['417']."</p>\n";
            }
            $content .= "</div>\n</div>\n";
            self::$step = array(
                1 => array(
                    'class' => 'pull-right btn-success',
                    'name'  => 'step',
                    'label' => self::$locale['setup_0120'],
                    'value' => self::STEP_EXIT
                ),
            );
        }

        return $content;
    }

}
