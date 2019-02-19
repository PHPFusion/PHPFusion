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
defined('IN_FUSION') || exit;

require_once THEMES."admin_themes/Old_School/includes/functions.php";

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

function render_admin_login() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();

    $html = "<div id='wrapper'>\n";
    $html .= "<div class='container' style='margin-top:100px;'>\n";
    $html .= "<div class='block'>\n";
        $html .= "<div class='block-content clearfix' style='font-size:13px;'>\n";
        $html .= "<h6><strong>".$locale['280']."</strong></h6>\n";
        $html .= "<img src='".IMAGES."php-fusion-icon.png' class='pf-logo position-absolute' alt='PHP-Fusion'/>";
        $html .= "<p class='fusion-version text-right mid-opacity text-smaller'>".$locale['version'].fusion_get_settings('version')."</p>";
        $html .= "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";

        // Get all notices
        $html .= renderNotices(getNotices());
        $form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;
        $html .= openform('admin-login-form', 'post', $form_action);

        $html .= fusion_get_function('openside', '');
        $html .= "<div class='m-t-10 clearfix row'>\n";
        $html .= "<div class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
        $html .= "<div class='pull-right'>\n";
        $html .= display_avatar($userdata, '90px');
        $html .= "</div>\n";
        $html .= "</div>\n<div class='col-xs-9 col-sm-9 col-md-8 col-lg-7'>\n";
        $html .= "<div class='clearfix'>\n";

        add_to_head('<style>#admin_password-field .required {display:none}</style>');

        $html .= "<h5><strong>".$locale['welcome'].", ".$userdata['user_name']."</strong><br/>".getuserlevel($userdata['user_level'])."</h5>";

        $html .= form_text('admin_password', "", "", array(
            'callback_check' => 'check_admin_pass',
            'placeholder' => $locale['281'],
            'error_text' => $locale['global_182'],
            'autocomplete_off' => TRUE,
            'type' => 'password',
            'required' => TRUE,
        ));

        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= fusion_get_function('closeside', '');

        $html .= form_button('admin_login', $locale['login'], $locale['login'], array('class' => 'btn-primary btn-block'));

        $html .= closeform();

        $html .= "</div>\n</div>\n"; // .col-*, .row
        $html .= "</div>\n"; // .block-content
    $html .= "</div>\n"; // .block
    $html .= "<div class='copyright-note clearfix m-t-10'>".showcopyright()."</div>\n";
    $html .= "</div></div>\n";

    echo $html;
}

function render_admin_panel() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();
    $languages = fusion_get_enabled_languages();

    $html = "<div id='wrapper'>\n";
    $html .= "<div class='container'>\n";
    $html .= "<div class='body-wrap'>\n";
    $html .= "<div class='body-inner-wrap'>\n";

    // Admin panel page
    $html .= "<div id='admin-panel' class='clearfix in'>\n";

    // Top header section
    $html .= "<section data-offset-top='0' data-offset-bottom='0'>\n";
        $html .= '<nav id="acp-header" class="navbar navbar-default m-r-15 m-l-15">';
            $html .= '<div class="container-fluid">';
                $html .= '<div class="navbar-header">';
                    $html .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu" aria-expanded="false">';
                        $html .= '<span class="sr-only">Toggle navigation</span>';
                        $html .= '<span class="icon-bar"></span>';
                        $html .= '<span class="icon-bar"></span>';
                        $html .= '<span class="icon-bar"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div class="collapse navbar-collapse" id="main-menu">';
                    $html .= '<ul class="nav navbar-nav">';
                        $sections = \PHPFusion\Admins::getInstance()->getAdminSections();
                        if (!empty($sections)) {
                            $i = 0;

                            foreach ($sections as $section_name) {
                                $active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && \PHPFusion\Admins::getInstance()->_isActive() == $i) ? ' class="active"' : '';
                                $html .= '<li'.$active.'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'">'.$section_name.'</a></li>';
                                $i++;
                            }
                        }
                    $html .= '</ul>';

                    $html .= '<ul class="nav navbar-nav navbar-right">';
                        $html .= "<li class='dropdown'>\n";
                            $html .= "<a class='dropdown-toggle pointer' data-toggle='dropdown'>".display_avatar($userdata, '18px', '', '', 'img-rounded')." ".$locale['logged']."<strong>".$userdata['user_name']."</strong> <span class='caret'></span>\n</a>\n";
                            $html .= "<ul class='dropdown-menu' role='menu'>\n";
                            $html .= "<li><a class='display-block' href='".BASEDIR."edit_profile.php'>".$locale['edit']." ".$locale['profile']."</a></li>\n";
                            $html .= "<li><a class='display-block' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['view']." ".$locale['profile']."</a></li>\n";
                            $html .= "<li class='divider'> </li>\n";
                            $html .= "<li><a class='display-block' href='".FUSION_REQUEST."&amp;logout'>".$locale['admin-logout']."</a></li>\n";
                            $html .= "<li><a class='display-block' href='".BASEDIR."index.php?logout=yes'>".$locale['logout']."</a></li>\n";
                            $html .= "</ul>\n";
                            $html .= "</li>\n";
                            if (count($languages) > 1) {
                                $html .= "<li class='dropdown'><a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['282']."'><i class='fa fa-globe fa-lg fa-fw'></i> ".translate_lang_names(LANGUAGE)."<span class='caret'></span></a>\n";
                                $html .= "<ul class='dropdown-menu'>\n";
                                foreach ($languages as $language_folder => $language_name) {
                                    $html .= "<li><a class='display-block' href='".clean_request("lang=".$language_folder, array("lang"),FALSE)."'><img class='m-r-5' alt='$language_name' src='".BASEDIR."locale/$language_folder/$language_folder-s.png'> $language_name</a></li>\n";
                                }
                                $html .= "</ul>\n";
                            $html .= "</li>\n";
                        }
                    $html .= '</ul>';
                $html .= '</div>'; // .navbar-collapse
            $html .= '</div>'; // .container-fluid
        $html .= '</nav>';
    $html .= "</section>\n";

    // Content section
    $html .= "<div class='content-wrapper display-block'>\n";

    // Main content wrapper
    $html .= "<div id='acp-content' class='m-t-20 col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";

    // Render breadcrumbs
    $html .= render_breadcrumbs();

    // Get and render notices
    $html .= renderNotices(getNotices());

    // Render the content
    $html .= CONTENT;
    $html .= "<hr />\n";
    $html .= "</div>\n"; // #acp-content

    // Footer section
    $html .= "<footer class='display-inline-block m-t-20'>\n";

    // Copyright
    $html .= "Old_School Admin &copy; ".date("Y")." Created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
    $html .= showcopyright();

    // Render time
    if (fusion_get_settings('rendertime_enabled')) {
        $html .= "<br /><br />";
        // Make showing of queries and memory usage separate settings
        $html .= showrendertime();
        $html .= showMemoryUsage();
    }

    $html .= showFooterErrors();
    $html .= "</footer>\n";
    $html .= "</div>\n"; // .acp-main
    $html .= "</div>\n"; // #admin-panel

    // Wrappers
    $html .= "</div></div></div></div>\n";

    echo $html;
}
