<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /Nebula/Mainframe.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace ThemePack\Nebula;

use PHPFusion\SiteLinks;
use ThemeFactory\Core;
use ThemeFactory\Lib\Installer\HomeInstall;

/**
 * Nebula Theme Package
 * Class MainFrame
 *
 * @package ThemePack\Nebula
 */
class MainFrame extends Core {

    public function __construct($license = FALSE) {
        self::set_body_span();
        /**
         * First time installation on default install.
         * Install Page Composer Data Request
         */
        if (iSUPERADMIN) {
            $theme_settings = get_theme_settings('FusionTheme');
            if (!isset($theme_settings['home_installed'])) {

                if (file_exists(THEME.'themefactory/lib/installer/locale/'.LANGUAGE.'.php')) {
                    $qLocale = fusion_get_locale('', THEME.'themefactory/lib/installer/locale/'.LANGUAGE.'.php');
                } else {
                    $qLocale = fusion_get_locale('', THEME.'themefactory/lib/installer/locale/English.php');
                }

                if (isset($_POST['install_default_homepage'])) {
                    $val = stripinput($_POST['install_default_homepage']);
                    if ($val == 'yes') {
                        require_once dirname(__FILE__).'/../../themefactory/lib/installer/home.inc';
                        new HomeInstall();
                    }
                    $row = [
                        'settings_name'  => 'home_installed',
                        'settings_value' => $val,
                        'settings_theme' => 'FusionTheme'
                    ];
                    dbquery_insert(DB_SETTINGS_THEME, $row, 'save', ['primary_key' => 'settings_name']);
                    redirect(BASEDIR.'index.php');
                }
                $form = "<div class='container'>\n";
                $form .= openform('submit_installer', 'post', clean_request(), ['class' => 'm-t-10']);
                $form .= "<h4>".$qLocale['homeSetup_0200']."</h4>\n";
                $form .= form_button('install_default_homepage', $qLocale['homeSetup_0201'], 'yes', ['class' => 'btn-success']);
                $form .= form_button('install_default_homepage', $qLocale['homeSetup_0202'], 'no');
                $form .= closeform();
                $form .= "</div>\n";
                addNotice('warning', $form);
            }
        }

        if ($this->getParam('header') === TRUE) {
            $this->NebulaHeader();
            add_to_footer("<script src='".THEME."themefactory/lib/js/wow.min.js'></script>");
            add_to_footer("<script src='".THEME."themefactory/lib/js/jquery.nicescroll.min.js'></script>");
            add_to_jquery("
            $('.contentLeft').niceScroll({
                // touchbehavior: true,
                cursorborder: 'none',
                cursorwidth: '8px',
                background: '#fff',
                zindex: '999'
            });
            ");
        }
        echo "<section class='nebulaBody".($this->getParam('body_class') ? " ".$this->getParam('body_class') : "")."'>\n";
        $this->NebulaTop();
        $this->NebulaBody();
        if ($this->getParam('footer') === TRUE) {
            $this->NebulaFooter();
        }
        echo "</section>\n";
    }

    private function NebulaHeader() {
        echo renderNotices(getNotices(array('all', FUSION_SELF)));
        $defaultBg = ($this->getParam('headerBg') === TRUE ? " class=\"headerBg\"" : "");
        $headerBg = ($this->getParam('headerBg_class') ? " class=\"".$this->getParam('headerBg_class')."\"" : $defaultBg);
        echo "<header ".$headerBg.">\n";
        echo "<div class='headerInner'>\n";
        echo "<div class='container'>\n";
        echo "<div id='headerBar' class='row hidden-print hidden-xs'>\n";
        echo "<div class='col-xs-12 col-sm-3 center'>\n";
        showlogo();
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-9 center-y'>\n";
        echo "<div class='navbar-header navbar-right'>\n";
        echo "<ul class='navbar-nav list-style-none'>\n";
        if (iMEMBER) :
            $msg_count = dbcount("('message_id')", DB_MESSAGES, "message_to=:my_id AND message_read=:unread AND message_folder=:inbox", [':inbox' => 0, ':my_id' => fusion_get_userdata('user_id'), ':unread' => 0]);
            echo "<li><a href='".BASEDIR."messages.php'>".fusion_get_locale('global_121').($msg_count ? "<span class='badge m-l-5'>$msg_count</span>" : "")."</a></li>";
            if (iADMIN) :
                echo "<li><a href='".ADMIN."index.php".fusion_get_aidlink()."'>".fusion_get_locale('global_123')."</a></li>\n";
            endif;
            echo "<li><a href='".BASEDIR."members.php'>".fusion_get_locale('UM082')."</a></li>\n";
            echo "<li><a href='".FUSION_SELF."?logout=yes'>".fusion_get_locale('logout')."</a></li>\n";
        else:
            echo "<li><a href='".BASEDIR."register.php'>".fusion_get_locale('register')."</a></li>\n";
            echo "<li><a href='".BASEDIR."login.php'>".fusion_get_locale('login')."</a></li>\n";
        endif;
        echo "</ul>\n";
        echo "</div>\n"; // navbar-right
        echo "</div>\n"; // col-sm-9
        echo "</div>\n"; // row
        echo "</div>\n"; // container
        $menu_config = [
            'container'         => ($this->getParam('navbar_container') ?: FALSE),
            'navbar_class'      => ($this->getParam('navbar_class') ?: 'navbar-default'),
            'language_switcher' => ($this->getParam('navbar_language_switch') ?: FALSE),
            'searchbar'         => ($this->getParam('navbar_searchbar') ?: FALSE),
            'caret_icon'        => 'fa fa-angle-down',
            'show_banner'       => FALSE,
            'grouping'          => fusion_get_settings('links_grouping'),
            'links_per_page'    => fusion_get_settings('links_per_page'),
            'show_header'       => ($this->getParam('navbar_show_header') ?: FALSE)
        ];
        echo SiteLinks::setSubLinks($menu_config)->showSubLinks();
        add_to_jquery("
            $('#".SiteLinks::MenuDefaultID."').affix({
                offset: {
                    top: '".$this->getParam('nav_offset')."',
                    bottom: function () {
                        return (this.bottom = $('.footer').outerHeight(true))
                    }
                }
            })
        ");
        // AU_CENTER
        if ((defined('AU_CENTER') && AU_CENTER || ($this->getParam('upper_content')) && $this->getParam('upper'))) :
            echo "<div class='showcase'>\n";
            if ($this->getParam('upper_container')) {
                echo "<div class='container'>\n";
            }
            echo($this->getParam('upper_content') ?: "");
            echo AU_CENTER;
            if ($this->getParam('upper_container')) {
                echo "</div>\n";
            }
            echo "</div>\n";
        endif;
        echo "</div>\n";
        echo "</header>\n";
        if ($this->getParam('subheader_content') || $this->getParam('breadcrumbs') === TRUE) :
            echo "<div class='nebulaSubheader'>\n";
            echo "<div class='container'>\n";
            if ($this->getParam('subheader_content')) :
                echo "<h4 class='display-inline-block'>".$this->getParam('subheader_content')."</h4>\n";
            endif;
            if ($this->getParam('breadcrumbs') === TRUE) :
                echo render_breadcrumbs();
            endif;
            echo "</div>\n";
            echo "</div>\n";
        endif;

    }

    private function NebulaTop() {
        if ($this->getParam('top_1') && $this->getParam('top_1_content')) :
            echo "<section class='nebulaContentTop'>\n";
            if ($this->getParam('top_1_container')) :
                echo "<div class='container'>\n";
            endif;
            echo $this->getParam('top_1_content');
            if ($this->getParam('top_1_container')) :
                echo "</div>\n";
            endif;
            echo "</section>\n";
        endif;
        echo showbanners(1);
        $side_span = 3;
        $main_span = 12;
        if (defined('RIGHT') && RIGHT || $this->getParam('right_pre_content') || $this->getParam('right_post_content')) {
            $main_span = $main_span - $side_span;
        }
        if (defined('LEFT') && LEFT) :
            echo "<div class='nebulaCanvas off'>\n";
            echo "<a class='canvas-toggle' href='#' data-target='nebulaCanvas'><i class='fa fa-bars fa-lg'></i></a>\n";
            echo LEFT;
            echo "</div>\n";
            add_to_jquery("
                $('.canvas-toggle').bind('click',function(e){
                    e.preventDefault();
                    var target = $(this).data('target');
                    $('.'+target).toggleClass('off');
                });
            ");
        endif;
    }

    private function NebulaBody() {
        $side_span = 3;
        $main_span = 12;
        if (defined('RIGHT') && RIGHT || $this->getParam('right_pre_content') || $this->getParam('right_post_content')) {
            $main_span = $main_span - $side_span;
        }
        if ($this->getParam('body_container') == TRUE) :
            echo "<div class='container'>\n";
        endif;
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-$main_span'>\n";
        // U_CENTER
        if (defined('U_CENTER') && U_CENTER && $this->getParam('u_center')) :
            echo U_CENTER;
        endif;
        echo CONTENT;
        // L_CENTER
        if (defined('L_CENTER') && L_CENTER && $this->getParam('l_center')) :
            echo L_CENTER;
        endif;
        echo "</div>\n";
        if (defined('RIGHT') && $this->getParam('right') === TRUE && RIGHT || $this->getParam('right_pre_content') || $this->getParam('right_post_content')) :
            echo "<div class='col-xs-12 col-sm-".$side_span."'>\n";
            echo $this->getParam('right_pre_content').RIGHT.$this->getParam('right_post_content');
            echo "</div>\n";
        endif;

        echo "</div>\n";
        if ($this->getParam('body_container') === TRUE) :
            echo "</div>\n";
        endif;
    }

    private function NebulaFooter() {
        if ($this->getParam('bottom_1') && $this->getParam('bottom_1_content')) :
            echo "<section class='nebulaContentBottom'>\n"; //nebulaContentBottom
            if ($this->getParam('bottom_1_container') === TRUE) :
                echo "<div class='container'>\n";
            endif;
            echo $this->getParam('bottom_1_content');
            if ($this->getParam('bottom_1_container') === TRUE) :
                echo "</div>\n";
            endif;
            echo "</section>\n";
        endif;
        // BL CENTER
        if (defined('BL_CENTER') && BL_CENTER && $this->getParam('bl_center')) :
            echo "<section class='nebulaBottom'>\n";
            if ($this->getParam('bl_center_container') === TRUE) :
                echo "<div class='container'>\n";
            endif;
            echo BL_CENTER;
            if ($this->getParam('bl_center_container') === TRUE) :
                echo "</div>\n";
            endif;
            echo "</section>\n";
        endif;
        echo "<section class='nebulaFooter'>\n";
        echo "<div class='container'>\n";
        if (defined('USER1') && USER1 || defined('USER2') && USER2 || defined('USER3') && USER3 || defined('USER4') && USER4) :
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
        endif;
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
        echo "<div class='about_theme'>\n";
        echo "<div class='nebulaLogo' style='margin-bottom:30px;'>\n";
        echo "<div class='pull-left'><i class='fa fa-cloud' style='font-size:50px; margin-right:10px;'></i></div>\n";
        echo "<div class='overflow-hide'><h1 class='m-0 text-white'>Nebula</h1>\n";
        echo "</div>\n";
        echo "</div>\n";
        echo self::$locale['NB_000'];
        echo "</div>\n";
        echo SiteLinks::setSubLinks(
            [
                'id'            => 'footer_a',
                'link_position' => 4, // Insert as Custom ID #4
                'navbar_class'  => 'nav',
                'nav_class'     => 'nav nav-stacked',
                'responsive'    => FALSE,
            ]
        )->showSubLinks();
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
        // News Module
        $this->get_Modules('footer\\news');
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-4'>\n";
        //$this->get_Modules('footer\\contact');
        echo "<h4>".self::$locale['NB_001']."</h4>\n";
        echo "<p>".fusion_get_settings('description')."</p>\n";
        echo stripslashes(strip_tags(fusion_get_settings('footer')));
        echo "<p>".showcopyright()."</p>\n";
        echo "</div>\n";
        echo "<a href='#' id='top' class='pull-right'><i class='fa fa-chevron-up fa-3x'></i></a>\n";
        add_to_jquery('$("#top").on("click",function(e){e.preventDefault();$("html, body").animate({scrollTop:0},800);});');
        echo "</div>\n";
        echo "</div>\n";
        echo showbanners(2);
        echo "</section>\n";
        echo "<section class='nebulaCopyright'>\n";
        echo "<div class='container'>\n";
        echo "<div class='col-xs-12 col-sm-4'><h4 class='m-b-0'>Nebula Theme by <a href='https://www.php-fusion.co.uk/profile.php?lookup=16331' target='_blank'>PHP-Fusion Inc</a></h4>\n";
        if (fusion_get_settings('visitorcounter_enabled')) :
            echo "<small>".showcounter()."</small>\n";
        endif;
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-8'>\n";
        if (fusion_get_settings('rendertime_enabled') == '1' || fusion_get_settings('rendertime_enabled') == '2') :
            echo showrendertime();
            echo showMemoryUsage();
        endif;
        $footer_errors = showFooterErrors();
        if (!empty($footer_errors)) :
            echo "<p>\n";
            echo $footer_errors;
            echo "</p>\n";
        endif;
        echo "</div>\n";
        echo "</section>\n";
    }
}