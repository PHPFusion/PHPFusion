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

        self::$connection = self::fusion_get_config(BASEDIR.'config_temp.php');

        require(INCLUDES.'multisite_include.php');

        $validation = Requirements::get_system_validation();

        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");

        if (isset($validation[3])) {

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
                            $content .= form_button('infuse', $locale['401'], $inf['folder'],
                                                    array('class' => 'btn-info m-t-5 infuse', 'icon' => 'entypo magnet'));
                        } else {
                            $content .= form_button('defuse', $locale['411'], $inf['folder'],
                                                    array('class' => 'btn-default btn-sm m-t-5 defuse', 'icon' => 'entypo trash'));
                        }
                    } else {
                        $content .= form_button('infuse', $locale['401'], $inf['folder'],
                                                array('class' => 'btn-primary btn-sm m-t-5 infuse', 'icon' => 'entypo install'));
                    }
                    $content .= "</div>\n";
                    $content .= "<div class='col-xs-6 col-sm-7 col-md-4 col-lg-4'><strong>".$inf['name']."</strong><br/>".trimlink($inf['description'],
                                                                                                                                   30)."</div>\n";
                    $content .= "<div class='col-xs-3 col-sm-2 col-md-2 col-lg-2'>".($inf['status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
                    $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf['version'] ? $inf['version'] : '')."</div>\n";
                    $content .= "<div class='col-xs-9 col-xs-offset-3 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-1 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".($inf['developer'] ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '')."</div>\n";
                    $content .= "</div>\n</div>\n";
                }
            } else {
                $content .= "<br /><p class='text-center'>".$locale['417']."</p>\n";
            }
            $content .= "</div>\n</div>\n";
            $content .= "<div class='well text-center m-t-10'>\n";
            $content .= "<a class='btn btn-block btn-primary' href='https://www.php-fusion.co.uk/infusions/addondb/directory.php' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
            $content .= "</div>\n";

            self::$step = array(
                1 => array(
                    'name' => 'step',
                    'label' => self::$locale['setup_0121'],
                    'value' => self::STEP_SETUP_COMPLETE
                ),
                2 => array(
                    'name' => 'step',
                    'class' => 'm-l-15 btn-default',
                    'label' => self::$locale['setup_0124'],
                    'value' => self::STEP_INTRO
                )
            );
        }

        return $content;
    }

}