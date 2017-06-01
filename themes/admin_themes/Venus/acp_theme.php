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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

require_once INCLUDES."theme_functions_include.php";
require_once THEMES."admin_themes/Venus/includes/functions.php";

$settings['bootstrap'] = 1;
add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();

function render_admin_login() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();
    echo "<section class='login-bg'>\n";
    echo "<aside class='block-container'>\n";
    echo "<div class='block'>\n";
    echo "<div class='block-content clearfix' style='font-size:13px;'>\n";
    echo "<h6><strong>".$locale['280']."</strong></h6>\n";
    echo "<img src='".IMAGES."php-fusion-icon.png' class='pf-logo position-absolute' alt='PHP-Fusion'/>";
    echo "<p class='fusion-version text-right mid-opacity text-smaller'>".$locale['version'].fusion_get_settings('version')."</p>";
    echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";

    $form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;

    // Get all notices
    echo renderNotices(getNotices());

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
    echo "</aside>\n";
    echo "</section>\n";
}

function render_admin_panel() {
    $locale = fusion_get_locale();
    $userdata = fusion_get_userdata();
    $languages = fusion_get_enabled_languages();

    // Admin panel page
    ?>

    <div id="admin-panel" class="clearfix">
        <!---left side panel-->
        <div id="acp-left" class="pull-left affix" data-offset-top="0" data-offset-bottom="0">
            <div class="brand"></div>
            <div id="acp-left-menu">
                <div class="panel panel-default admin">
                    <div class="panel-body clearfix">
                        <div class="pull-left m-r-10"><?php echo display_avatar($userdata, '50px', '', FALSE, 'img-rounded'); ?></div>
                        <div class="overflow-hide">
                            <strong><?php echo $userdata['user_name']; ?></strong><br/>
                            <?php echo getuserlevel($userdata['user_level']); ?>
                        </div>
                    </div>
                </div>
                <?php echo \PHPFusion\Admins::getInstance()->vertical_admin_nav(); ?>
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
                <?php echo \PHPFusion\Admins::getInstance()->horizontal_admin_nav(TRUE); ?>
                <ul class="top-right-menu pull-right m-r-15">
                    <li class="dropdown">
                        <a class="dropdown-toggle pointer" data-toggle="dropdown">
                            <?php echo display_avatar($userdata, '25px', '', FALSE, 'img-circle')." ".$locale['logged']." <strong>".$userdata['user_name']."</strong>"; ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a class="display-block" href="<?php echo BASEDIR."edit_profile.php" ?>"><?php echo $locale['UM080'] ?></a></li>
                            <li><a class="display-block" href="<?php echo BASEDIR."profile.php?lookup=".$userdata['user_id'] ?>"><?php echo $locale['view']." ".$locale['profile'] ?></a></li>
                            <li class="divider"></li>
                            <li><a class="display-block" href="<?php echo FUSION_REQUEST."&amp;logout" ?>"><?php echo $locale['admin-logout'] ?></a></li>
                            <li><a class="display-block" href="<?php echo BASEDIR."index.php?logout=yes" ?>"><?php echo $locale['logout']; ?></a></li>
                        </ul>
                    </li>
                    <li><a title="<?php echo $locale['settings'] ?>" href="<?php echo ADMIN."settings_main.php".fusion_get_aidlink() ?>"><i class="fa fa-fw fa-cog"></i></a></li>
                    <li><a title="<?php echo fusion_get_settings('sitename') ?>" href="<?php echo BASEDIR."index.php" ?> "><i class="fa fa-fw fa-home"></i></a></li>
                    <li><a title="<?php echo $locale['message'] ?>" href="<?php echo BASEDIR."messages.php" ?>"><i class="fa fa-fw fa-envelope-o"></i></a></li>
                    <?php
                    if (count($languages) > 1) :
                        echo "<li class='dropdown'>";
                            echo "<a class='dropdown-toggle pointer' data-toggle='dropdown' title='".$locale['282']."'><i class='fa fa-fw fa-globe'></i> ".translate_lang_names(LANGUAGE)."<span class='caret'></span></a>\n";
                            echo "<ul class='dropdown-menu'>\n";
                            foreach ($languages as $language_folder => $language_name) {
                                echo "<li><a class='display-block' href='".clean_request("lang=".$language_folder, array("lang"), FALSE)."'><img class='m-r-5' src='".BASEDIR."locale/$language_folder/$language_folder-s.png'> $language_name</a></li>\n";
                            }
                            echo "</ul>\n";
                        echo "</li>\n";
                    endif;
                    ?>
                </ul>
            </nav>
        </header>

        <!---main panel-->
        <div id="acp-main">
            <aside id="acp-content" class="m-t-20">
                <?php
                echo render_breadcrumbs();
                echo renderNotices(getNotices());
                echo CONTENT;
                ?>
                <footer>
                    <?php
                    echo "Venus Admin Theme &copy; ".date("Y")." created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
                    echo showcopyright();
                    // Render time
                    if (fusion_get_settings('rendertime_enabled')) {
                        echo "<br /><br />";
                        // Make showing of queries and memory usage separate settings
                        echo showrendertime();
                        echo showMemoryUsage();
                    }
                    echo showFooterErrors();
                    ?>
                </footer>
            </aside>
        </div>
        <!---//main panel-->
    </div>

    <?php
    add_to_footer("<script src='".THEMES."admin_themes/Venus/includes/jquery.slimscroll.min.js'></script>");

    add_to_jquery("
        // Initialize slimscroll
        $('#adl').slimScroll({height: null});

        $('#toggle-canvas').on('click', function(e) {
            if ($('#admin-panel').hasClass('in')) {
                $('#admin-panel').removeClass('in');
                Cookies.set('".COOKIE_PREFIX."acp_sidemenu', 0);
            } else {
                $('#admin-panel').addClass('in');
                Cookies.set('".COOKIE_PREFIX."acp_sidemenu', 1);
            }
        });

        if (Cookies.get('".COOKIE_PREFIX."acp_sidemenu') !== undefined) {
            if (Cookies.get('".COOKIE_PREFIX."acp_sidemenu') == 1) {
                $('#admin-panel').addClass('in');
            }
        }
    ");
}
