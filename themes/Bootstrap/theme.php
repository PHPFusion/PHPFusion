<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

define("THEME_BULLET", "&middot;");
require_once INCLUDES."theme_functions_include.php";
include "functions.php";

function render_page($license = FALSE) {

    $locale = fusion_get_locale();
    $userdata = fusion_get_userdata();
    $aidlink = fusion_get_aidlink();
    $settings = fusion_get_settings();

    // set variables
    $brand = "<a href='".BASEDIR.fusion_get_settings('opening_page')."'>\n";
    $brand .= $settings['sitebanner'] ? "<img title='".$settings['sitename']."' style='margin-left:-20px; width:100%; margin-top:-35px;' src='".BASEDIR.$settings['sitebanner']."' />" : $settings['sitename'];
    $brand .= "</a>\n";

    // set size - max of 12 min of 0
    $side_grid_settings = array(
        'desktop_size' => 2,
        'laptop_size'  => 3,
        'tablet_size'  => 3,
        'phone_size'   => 12,
    );

    // Render Theme
    echo "<div class='container p-t-20 p-b-20'>\n";
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <?php
            echo "<div class='display-inline-block m-t-20 m-l-20' style='max-width: 280px;'>";
            echo $brand;
            echo "</div>\n";
            ?>
        </div>
        <div class="col-xs-12 col-sm-8 text-right">
            <?php
            echo "<div class='display-inline-block pull-right m-l-10' style='width:30%;'>\n";
            echo openform('searchform', 'post', BASEDIR.'search.php?stype=all',
                array(
                    'class'      => 'm-b-10',
                    'remote_url' => fusion_get_settings('site_path')."search.php"
                )
            );
            echo form_text('stext', '', '', array(
                'placeholder'        => $locale['search'],
                'append_button'      => TRUE,
                'append_type'        => "submit",
                "append_form_value"  => 'search',
                "append_value"       => "<i class='fa fa-search'></i> ".$locale['search'],
                "append_button_name" => "search",
                'class'              => 'no-border m-b-0',
            ));
            echo closeform();
            echo "</div>\n";
            echo "<ul class='display-inline-block m-t-10'>\n";
            $language_opts = '';
            if (count(fusion_get_enabled_languages()) > 1) {
                $language_opts = "<li class='dropdown display-inline-block p-r-5'>\n";
                $language_opts .= "<a class='dropdown-toggle pointer' data-toggle='dropdown' title='".fusion_get_locale('UM101')."'><i class='fa fa-globe fa-lg'></i> ".translate_lang_names(LANGUAGE)." <span class='caret'></span></a>\n";
                $language_opts .= "<ul class='dropdown-menu' role='menu'>\n";
                $language_switch = fusion_get_language_switch();
                if (!empty($language_switch)) {
                    foreach ($language_switch as $folder => $langData) {
                        $language_opts .= "<li class='text-left'><a href='".$langData['language_link']."'>\n";
                        $language_opts .= "<img alt='".$langData['language_name']."' class='m-r-5' src='".$langData['language_icon_s']."'/>\n";
                        $language_opts .= $langData['language_name'];
                        $language_opts .= "</a></li>\n";
                    }
                }
                $language_opts .= "</ul>\n";
                $language_opts .= "</li>\n";
            }
            if (!iMEMBER) {
                echo "<li class='display-inline-block p-l-5 p-r-5'><a href='".BASEDIR."login.php'>".$locale['login']."</a></li>\n";
                if (fusion_get_settings("enable_registration")) {
                    echo "<li class='display-inline-block p-l-5 p-r-5'><a href='".BASEDIR."register.php'>".$locale['register']."</a></li>\n";
                }
                echo $language_opts;
            } else {
                if (iADMIN) {
                    echo "<li class='display-inline-block p-l-5 p-r-5'>\n<a href='".ADMIN.$aidlink."&amp;pagenum=0'>".$locale['global_123']."</a>\n</li>\n";
                }
                echo "<li class='display-inline-block p-l-5 p-r-5'>\n<a href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['profile']."</a>\n</li>\n";
                echo $language_opts;
                echo "<li class='display-inline-block p-l-5 p-r-5'>\n<a href='".BASEDIR."index.php?logout=yes'>".$locale['logout']."</a></li>\n";
            }

            echo "</ul>\n";
            ?>
        </div>
    </div>
    <?php

    echo showsublinks('', 'navbar-default', array('logo' => $brand, 'show_header' => TRUE))."\n";
    echo showbanners(1);
    // row 1 - go for max width
    if (defined('AU_CENTER') && AU_CENTER) {
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".AU_CENTER."</div>\n</div>";
    }
    // row 2 - fluid setitngs depending on panel appearances
    echo "<div class='row main-body'>\n";
    if (defined('LEFT') && LEFT) {
        echo "<div class='".html_prefix($side_grid_settings)." hidden-xs'>\n".LEFT."</div>\n";
    } // column left
    echo "<div class='".html_prefix(center_grid_settings($side_grid_settings))."'>\n";
    echo renderNotices(getNotices(array('all', FUSION_SELF)));
    echo U_CENTER.CONTENT.L_CENTER."</div>\n"; // column center
    if (defined('RIGHT') && RIGHT) {
        echo "<div class='".html_prefix($side_grid_settings)."'>\n".RIGHT."</div>\n";
    } // column right
    if (defined('LEFT') && LEFT) {
        echo "<div class='".html_prefix($side_grid_settings)." hidden-sm hidden-md hidden-lg'>\n".LEFT."</div>\n";
    } // column left
    echo "</div>\n";
    // row 3
    if (defined('BL_CENTER') && BL_CENTER) {
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".BL_CENTER."</div>\n</div>";
    }

    echo "<div class='row'>\n";
    echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
    echo defined('USER1') && USER1 ? USER1 : '';
    echo "</div>\n";

    echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
    echo defined('USER2') && USER2 ? USER2 : '';
    echo "</div>\n";

    echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
    echo defined('USER3') && USER3 ? USER3 : '';
    echo "</div>\n";

    echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
    echo defined('USER4') && USER4 ? USER4 : '';
    echo "</div>\n";
    echo "</div>\n";

    // footer
    echo "<hr>\n";
    echo showbanners(2);
    echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";
    echo "<span>".stripslashes(strip_tags($settings['footer']))."</span><br/>\n";
    echo "<span>".showcopyright()."</span><br/>\n";
    echo "<span>Bootstrap Theme by <a href='http://www.php-fusion.co.uk' target='_blank'>PHP-Fusion Inc</a></span><br/>\n";
    echo "<span>";
    if ($settings['visitorcounter_enabled']) {
        echo showcounter();
    }
    if ($settings['rendertime_enabled'] == '1' || $settings['rendertime_enabled'] == '2') {
        if ($settings['visitorcounter_enabled']) {
            echo " | ";
        }
        echo showrendertime();
    }
    echo "</span>\n";
    echo "</div>\n</div>\n";
    echo "</div>\n";
}
