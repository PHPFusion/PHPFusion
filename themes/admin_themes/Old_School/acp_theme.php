<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Old_School/acp_theme.php
| Author: PHP-Fusion Inc
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

require_once INCLUDES."theme_functions_include.php";
require_once THEMES."admin_themes/Old_School/includes/functions.php";
\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();
$settings['bootstrap'] = 1;

function render_admin_login() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();

    echo "<div id='wrapper'>\n";
    echo "<div class='container' style='margin-top:100px;'>\n";
    echo "<div class='block'>\n";
        echo "<div class='block-content clearfix' style='font-size:13px;'>\n";
        echo "<h6><strong>".$locale['280']."</strong></h6>\n";
        echo "<img src='".IMAGES."php-fusion-icon.png' class='pf-logo position-absolute' alt='PHP-Fusion'/>";
        echo "<p class='fusion-version text-right mid-opacity text-smaller'>".$locale['version'].fusion_get_settings('version')."</p>";
        echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";

        $form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;
        // Get all notices
        $notices = getNotices();
        echo renderNotices($notices);

        echo openform('admin-login-form', 'post', $form_action);

        openside('');
        echo "<div class='m-t-10 clearfix row'>\n";
        echo "<div class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
        echo "<div class='pull-right'>\n";
        echo display_avatar($userdata, '90px');
        echo "</div>\n";
        echo "</div>\n<div class='col-xs-9 col-sm-9 col-md-8 col-lg-7'>\n";
        echo "<div class='clearfix'>\n";

        add_to_head('<style>#admin_password-field .required {display:none}</style>');

        echo "<h5><strong>".$locale['welcome'].", ".$userdata['user_name']."</strong><br/>".getuserlevel($userdata['user_level'])."</h5>";

        echo form_text('admin_password', "", "", array(
            'callback_check' => 'check_admin_pass',
            'placeholder' => $locale['281'],
            'error_text' => $locale['global_182'],
            'autocomplete_off' => TRUE,
            'type' => 'password',
            'required' => TRUE,
        ));

        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n";
        closeside();

        echo form_button('admin_login', $locale['login'], $locale['login'], array('class' => 'btn-primary btn-block'));

        echo closeform();

        echo "</div>\n</div>\n"; // .col-*, .row
        echo "</div>\n"; // .block-content
    echo "</div>\n"; // .block
    echo "<div class='copyright-note clearfix m-t-10'>".showcopyright()."</div>\n";
    echo "</div></div>\n";
}

function render_admin_panel() {
    global $defender, $pages, $admin;
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();


    $languages = fusion_get_enabled_languages();

    echo "<div id='wrapper'>\n";
    echo "<div class='container'>\n";
    echo "<div class='body-wrap'>\n";
    echo "<div class='body-inner-wrap'>\n";

    // Admin panel page
    echo "<div id='admin-panel' class='clearfix in'>\n";

    // Top header section
    echo "<section id='acp-header' class='pull-left affix clearfix' data-offset-top='0' data-offset-bottom='0'>\n";

    // Top content sections navigation
    echo "<nav>\n";
    echo "<ul class='top-left-menu pull-left m-l-15'>\n";
    echo "<li><a title='".$locale['ac00']."' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'>".$locale['ac00']."</a></li>\n";
    echo "<li><a title='".$locale['ac01']."' href='".ADMIN."index.php".$aidlink."&amp;pagenum=1'>".$locale['ac01']."</a></li>\n";
    echo "<li><a title='".$locale['ac02']."' href='".ADMIN."index.php".$aidlink."&amp;pagenum=2'>".$locale['ac02']."</a></li>\n";
    echo "<li><a title='".$locale['ac03']."' href='".ADMIN."index.php".$aidlink."&amp;pagenum=3'>".$locale['ac03']."</a></li>\n";
    echo "<li><a title='".$locale['ac04']."' href='".ADMIN."index.php".$aidlink."&amp;pagenum=4'>".$locale['ac04']."</a></li>\n";
    echo "<li><a title='".$locale['ac05']."' href='".ADMIN."index.php".$aidlink."&amp;pagenum=5'>".$locale['ac05']."</a></li>\n";
    echo "</ul>\n";
    echo "</nav>\n";

    // Top navigation
    echo "<nav>\n";

    // Top right menu links
    echo "<ul class='top-right-menu pull-right m-r-15'>\n";
    echo "<li class='dropdown'>\n";
    echo "<a class='dropdown-toggle pointer' data-toggle='dropdown'>".display_avatar($userdata, '25px', '', '',
                                                                                     '')." ".$locale['logged']."<strong>".$userdata['user_name']."</strong> <span class='caret'></span>\n</a>\n";
    echo "<ul class='dropdown-menu' role='menu'>\n";
    echo "<li><a class='display-block' href='".BASEDIR."edit_profile.php'>".$locale['edit']." ".$locale['profile']."</a></li>\n";
    echo "<li><a class='display-block' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['view']." ".$locale['profile']."</a></li>\n";
    echo "<li class='divider'> </li>\n";
    echo "<li><a class='display-block' href='".FUSION_REQUEST."&amp;logout'>".$locale['admin-logout']."</a></li>\n";
    echo "<li><a class='display-block' href='".BASEDIR."index.php?logout=yes'>".$locale['logout']."</a></li>\n";
    echo "</ul>\n";
    echo "</li>\n";
    if (count($languages) > 1) {
        echo "<li class='dropdown'><a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['282']."'><i class='fa fa-globe fa-lg fa-fw'></i> ".translate_lang_names(LANGUAGE)."<span class='caret'></span></a>\n";
        echo "<ul class='dropdown-menu'>\n";
        foreach ($languages as $language_folder => $language_name) {
            echo "<li><a class='display-block' href='".clean_request("lang=".$language_folder, array("lang"),
                                                                     FALSE)."'><img class='m-r-5' src='".BASEDIR."locale/$language_folder/$language_folder-s.png'> $language_name</a></li>\n";
        }
        echo "</ul>\n";
        echo "</li>\n";
    }
    echo "</ul>\n"; // .top-right-menu
    echo "</nav>\n";
    echo "</section>\n";


    // Content section
    echo "<div class='content-wrapper display-block'>\n";

    // Main content wrapper
    echo "<div id='acp-content' class='m-t-20 col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";

    // Render breadcrumbs
    echo render_breadcrumbs();

    // Get and render notices
    $notices = getNotices();
    echo renderNotices($notices);

    // Render the content
    echo CONTENT;
    echo "</div>\n"; // #acp-content

    // Footer section
    echo "<footer class='m-l-20 display-inline-block m-t-20 m-b-20'>\n";

    // Copyright
    echo "Old_School Admin &copy; ".date("Y")." created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
    echo showcopyright();

    // Render time
    if (fusion_get_settings('rendertime_enabled')) {
        echo "<br /><br />";
        // Make showing of queries and memory usage separate settings
        echo showrendertime();
        echo showMemoryUsage();
    }
    echo "<hr />\n";
    echo showFooterErrors();
    echo "</footer>\n";
    echo "</div>\n"; // .acp-main
    echo "</div>\n"; // #admin-panel

    // Wrappers
    echo "</div></div></div></div>\n";
}
