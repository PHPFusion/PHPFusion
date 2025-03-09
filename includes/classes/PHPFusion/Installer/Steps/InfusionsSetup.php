<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: InfusionsSetup.php
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
namespace PHPFusion\Installer\Steps;

use PHPFusion\Installer\Infusions;
use PHPFusion\Installer\InstallCore;
use PHPFusion\Installer\Requirements;

class InfusionsSetup extends InstallCore {
    /**
     * @return string
     */
    public function view() {
        self::$connection = self::fusionGetConfig(BASEDIR.'config_temp.php');

        require_once(INCLUDES.'multisite_include.php');
        require_once(INCLUDES.'infusions_include.php');

        $validation = Requirements::getSystemValidation();

        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");

        $content = '';
        if (isset($validation[3])) {
            if ($this->tableCheck()) {
                /*
                 * Use DB superadmin password.
                 */
                $userdata = fusion_get_user(1);
                $settings = fusion_get_settings();

                // User level, Admin Rights & User Group definitions
                define("iGUEST", $userdata['user_level'] == USER_LEVEL_PUBLIC ? 1 : 0);
                if (!defined('iMEMBER')) {
                    define("iMEMBER", $userdata['user_level'] <= USER_LEVEL_MEMBER ? 1 : 0);
                }
                define("iADMIN", $userdata['user_level'] <= USER_LEVEL_ADMIN ? 1 : 0);
                define("iSUPERADMIN", $userdata['user_level'] == USER_LEVEL_SUPER_ADMIN ? 1 : 0);
                define("iUSER", $userdata['user_level']);
                define("iUSER_RIGHTS", $userdata['user_rights']);
                define("iUSER_GROUPS", substr($userdata['user_groups'], 1));
                // Get enabled language settings
                //$enabled_languages = array_keys(fusion_get_enabled_languages());
                // If language change is initiated and if the selected language is valid
                if (isset($_GET['lang']) && valid_language($_GET['lang'])) {
                    $lang = stripinput($_GET['lang']);
                    set_language($lang);
                    $redirectPath = clean_request("", ["lang"], FALSE);
                    redirect($redirectPath);
                }

                // Main language detection procedure
                if (iMEMBER && valid_language($userdata['user_language'])) {
                    if (!defined('LANGUAGE')) {
                        define("LANGUAGE", $userdata['user_language']);
                        define("LOCALESET", $userdata['user_language']."/");
                    }
                } else {
                    $data = dbarray(dbquery("SELECT * FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
                    if (!empty($data['user_language'])) {
                        if (!defined('LANGUAGE')) {
                            define("LANGUAGE", $data['user_language']);
                            define("LOCALESET", $data['user_language']."/");
                        }
                    }
                }
                // Check if definitions have been set, if not set the default language to system language
                if (!defined("LANGUAGE") && !defined('LOCALESET')) {
                    define("LANGUAGE", $settings['locale']);
                    define("LOCALESET", $settings['locale']."/");
                }

                add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['INF_412']."');});");

                $inf_core = Infusions::getInstance();
                $inf_core::loadConfiguration();
                if (($folder = filter_input(INPUT_POST, 'infuse'))) {
                    $inf_core->infuse($folder);
                } else if ($folder = filter_input(INPUT_POST, 'defuse')) {
                    $inf_core->defuse($folder);
                }
                $content = "";
                $temp = opendir(INFUSIONS);
                $infs = [];
                while ($folder = readdir($temp)) {
                    if (!in_array($folder, ["..", "."]) && ($inf = Infusions::loadInfusion($folder))) {
                        $infs[] = $inf;
                    }
                }
                closedir($temp);
                $content .= "<div>\n";
                $content .= rendernotices(getnotices());

                if ($infs) {
                    $content .= "<div class='list-group'>\n";
                    $content .= "<div class='list-group-item hidden-xs'>\n";
                    $content .= "<div class='row'>\n";
                    $content .= "<div class='hidden-xs col-sm-3 col-md-2 col-lg-2'>\n<strong>".$locale['INF_419']."</strong></div>\n";
                    $content .= "<div class='hidden-xs col-sm-6 col-md-4 col-lg-4'>\n<strong>".$locale['INF_400']."</strong></div>\n";
                    $content .= "<div class='hidden-xs col-sm-3 col-md-2 col-lg-2'>\n<strong>".$locale['INF_418']."</strong></div>\n";
                    $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['INF_420']."</strong></div>\n";
                    $content .= "<div class='hidden-xs hidden-sm hidden-md col-lg-3'>\n<strong>".$locale['INF_421']."</strong></div>\n";
                    $content .= "</div>\n</div>\n";

                    sort($infs);
                    foreach ($infs as $inf) {
                        $content .= "<div class='list-group-item'>\n";
                        $content .= "<div class='row'>\n";
                        $content .= "<div class='col-xs-12 col-sm-3 col-md-2 col-lg-2'>\n";
                        if ($inf['status'] > 0) {
                            if ($inf['status'] > 1) {
                                $content .= form_button('infuse', $locale['INF_416'], $inf['folder'],
                                    ['class' => 'btn-info m-t-5 btn-sm infuse', 'icon' => 'fa fa-cube']);
                            } else {
                                $content .= form_button('defuse', $locale['INF_411'], $inf['folder'],
                                    ['class' => 'btn-default btn-sm m-t-5 defuse', 'icon' => 'fa fa-trash']);
                            }
                        } else {
                            $content .= form_button('infuse', $locale['INF_401'], $inf['folder'],
                                ['class' => 'btn-primary btn-sm m-t-5 infuse', 'icon' => 'fa fa-magnet']);
                        }
                        $content .= "</div>\n";
                        $content .= "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-4'><strong>".$inf['name']."</strong><br/>".trimlink($inf['description'], 30)."</div>\n";
                        $content .= "<div class='hidden-xs col-sm-3 col-md-2 col-lg-2'>".($inf['status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['INF_415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['INF_414']."</label></h5>")."</div>\n";
                        $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".(!empty($inf['version']) ? $inf['version'] : '')."</div>\n";
                        $content .= "<div class='col-xs-12 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 col-lg-3'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".(!empty($inf['developer']) ? $inf['developer'] : $locale['INF_410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['INF_409']."</a>" : '')."</div>\n";
                        $content .= "</div>\n</div>\n";
                    }
                } else {
                    $content .= "<br /><p class='text-center'>".$locale['INF_417']."</p>\n";
                }
                $content .= "</div>\n</div>\n";
                self::$step = [
                    1 => [
                        'class' => 'pull-right btn-success',
                        'name'  => 'step',
                        'label' => self::$locale['setup_0120'],
                        'value' => self::STEP_EXIT
                    ],
                ];

            } else {
                redirect(FUSION_REQUEST);
            }


        }

        return $content;
    }
}
