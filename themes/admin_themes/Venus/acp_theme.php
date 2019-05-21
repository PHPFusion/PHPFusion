<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Venus/acp_theme.php
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

require_once THEMES."admin_themes/Venus/includes/functions.php";

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

function render_admin_panel() {
    $locale = fusion_get_locale();
    $userdata = fusion_get_userdata();
    $languages = fusion_get_enabled_languages();

    // Admin panel page
    $html = '<div id="admin-panel" class="clearfix">
        <!---left side panel-->
        <div id="acp-left" class="pull-left affix" data-offset-top="0" data-offset-bottom="0">
            <div class="brand"></div>
            <div id="acp-left-menu">
                <div class="panel panel-default admin">
                    <div class="panel-body clearfix">
                        <div class="pull-left m-r-10">'.display_avatar($userdata, '50px', '', FALSE, 'img-rounded').'</div>
                        <div class="overflow-hide">
                            <strong>'.$userdata['user_name'].'</strong><br/>
                            '.getuserlevel($userdata['user_level']).'
                        </div>
                    </div>
                </div>
                 '.\PHPFusion\Admins::getInstance()->vertical_admin_nav().'
            </div>
        </div>
        <!---//left side panel-->

        <header id="acp-header" class="pull-left affix clearfix">
            <nav>
                <ul class="venus-toggler">
                    <li>
                        <a id="toggle-canvas" class="pointer"><i class="fa fa-fw fa-bars"></i></a>
                    </li>
                </ul>
                <div class="hidden-md">'.\PHPFusion\Admins::getInstance()->horizontal_admin_nav(TRUE).'</div>
                <ul class="top-right-menu pull-right m-r-15">
                    <li class="dropdown">
                        <a class="dropdown-toggle pointer" data-toggle="dropdown">
                            '.display_avatar($userdata, '25px', '', FALSE, 'img-circle').' <span class="hidden-xs">'.$locale['logged'].' <strong>'.$userdata['user_name'].'</strong></span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a class="display-block" href="'.BASEDIR.'edit_profile.php">'.$locale['UM080'].'</a></li>
                            <li><a class="display-block" href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'">'.$locale['view']." ".$locale['profile'].'</a></li>
                            <li class="divider"></li>
                            <li><a class="display-block" href="'.FUSION_REQUEST.'&amp;logout">'.$locale['admin-logout'].'</a></li>
                            <li><a class="display-block" href="'.BASEDIR.'index.php?logout=yes">'.$locale['logout'].'</a></li>
                        </ul>
                    </li>
                    <li><a title="'.$locale['settings'].'" href="'.ADMIN.'settings_main.php'.fusion_get_aidlink().'"><i class="fa fa-fw fa-cog"></i></a></li>
                    <li><a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php"><i class="fa fa-fw fa-envelope-o"></i></a></li>';

                    if (count($languages) > 1) :
                        $html .= "<li class='dropdown'>";
                            $html .= "<a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['282']."'><i class='fa fa-fw fa-globe'></i> ".translate_lang_names(LANGUAGE)."<span class='caret'></span></a>\n";
                            $html .= "<ul class='dropdown-menu'>\n";
                            foreach ($languages as $language_folder => $language_name) {
                                $html .= "<li><a class='display-block' href='".clean_request("lang=".$language_folder, array("lang"), FALSE)."'><img class='m-r-5' alt='$language_name' src='".BASEDIR."locale/$language_folder/$language_folder-s.png'> $language_name</a></li>\n";
                            }
                            $html .= "</ul>\n";
                        $html .= "</li>\n";
                    endif;

                    $html .=' <li><a title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-fw fa-home"></i></a></li>
                </ul>
            </nav>
        </header>

        <!---main panel-->
        <div id="acp-main">
            <aside id="acp-content">
                <div class="panel panel-default">
                    <div class="panel-body">';
                        $html .= render_breadcrumbs();
                        $html .= renderNotices(getNotices());
                        $html .= CONTENT;

                    $html .= '</div>
                </div>
                <footer>';
                    $html .= "Venus Admin Theme &copy; ".date("Y")." created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
                    $html .= showcopyright();
                    // Render time
                    if (fusion_get_settings('rendertime_enabled')) {
                        $html .= "<br /><br />";
                        // Make showing of queries and memory usage separate settings
                        $html .= showrendertime();
                        $html .= showMemoryUsage();
                    }
                    $html .= showFooterErrors();

                $html .= '</footer>
            </aside>
        </div>
        <!---//main panel-->
    </div>';


    add_to_footer("<script src='".THEMES."admin_themes/Venus/includes/jquery.slimscroll.min.js'></script>");

    add_to_jquery("
        // Initialize slimscroll
        $('#adl').slimScroll({height: 'auto'});

        $('#toggle-canvas').on('click', function(e) {
            if ($('#admin-panel').hasClass('in')) {
                $('#admin-panel').removeClass('in');
                localStorage.setItem('".COOKIE_PREFIX."acp_sidemenu', 0);
            } else {
                $('#admin-panel').addClass('in');
                localStorage.setItem('".COOKIE_PREFIX."acp_sidemenu', 1);
            }
        });

        if (localStorage.getItem('".COOKIE_PREFIX."acp_sidemenu') !== undefined) {
            if (localStorage.getItem('".COOKIE_PREFIX."acp_sidemenu') == 1) {
                $('#admin-panel').addClass('in');
            }
        }
    ");

    echo $html;
}

function render_admin_login() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();

    $html = "<section class='login-bg'>\n";
    $html .= "<aside class='block-container'>\n";
    $html .= "<div class='block'>\n";
    $html .= "<div class='block-content clearfix' style='font-size:13px;'>\n";
    $html .= "<h6><strong>".$locale['280']."</strong></h6>\n";
    $html .= "<img src='".IMAGES."php-fusion-icon.png' class='pf-logo position-absolute' alt='PHP-Fusion'/>";
    $html .= "<p class='fusion-version text-right mid-opacity text-smaller'>".$locale['version'].fusion_get_settings('version')."</p>";
    $html .= "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";

    $form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;

    // Get all notices
    $html .= renderNotices(getNotices());

    $html .= openform('admin-login-form', 'post', $form_action);

    $html .= fusion_get_function('openside', '');

    $html .= "<div class='m-t-10 clearfix row'>\n";
    $html .= "<div class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
    $html .= "<div class='pull-right'>\n";
    $html .= display_avatar($userdata, '90px');
    $html .= "</div>\n";
    $html .= "</div>\n<div class='col-xs-9 col-sm-9 col-md-8 col-lg-7'>\n";
    $html .= "<div class='clearfix'>\n";

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
    $html .= "</aside>\n";
    $html .= "</section>\n";

    echo $html;
}

function openside($title = FALSE, $class = FALSE) {
    $html = "<div class='panel panel-default $class'>";
    $html .= ($title) ? "<div class='panel-heading'>$title</div>" : '';
    $html .= "<div class='panel-body'>";

    echo $html;
}

function closeside($title = FALSE) {
    $html = "</div>";
    $html .= ($title) ? "<div class='panel-footer'>$title</div>" : '';
    $html .= "</div>";

    echo $html;
}

function opentable($title, $class = FALSE) {
    $html = "<div class='panel-default $class' style='border:none; box-shadow:none'><div class='panel-body p-t-20 p-l-0 p-r-0'>";
    $html .= "<h3>".$title."</h3>";

    echo $html;
}

function closetable() {
    $html = "</div></div>";
    echo $html;
}
