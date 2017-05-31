<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: includes/components.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer:
| Craig (http://www.phpfusionmods.co.uk),
| Chan (Lead developer of PHP-Fusion)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;
use PHPFusion\Rewrite\Router;

/**
 * Class SeptenaryComponents
 * Collections of 'functions_include.php'
 * @package Septenary
 */
class SeptenaryComponents {

    protected static $locale = array();
    private static $custom_header_html = "";

    /**
     * Legacy opentable function
     * @param $title
     */
    public static function opentable($title) {
        echo "<article><h2 class='m-t-0 m-b-0'>".$title."</h2><div class='content'>\n";
    }

    /**
     * Legacy closetable function
     */
    public static function closetable() {
        echo "</div></article>\n";
    }

    /**
     * Legacy Openside Function
     * @param            $title
     * @param bool|FALSE $collapse
     * @param string     $state
     */
    public static function openside($title, $collapse = FALSE, $state = "on") {
        global $panel_collapse;

        $boxname = str_replace(" ", "", $title);
        $boxname .= $boxname."-".str_shuffle(mt_rand(0, 30).time());

        $panel_collapse = $collapse;

        echo "<div class='heading'>\n";
        echo "<div style='margin-left: 10px;'>".$title."</div>\n";
        echo "</div>\n";
        if ($collapse == TRUE) {
            echo "<div class='pull-right' style='padding-top: 10px;'>".panelbutton($state, $boxname)."</div>\n";
        }
        echo "<div class='content'>\n";
        if ($collapse == TRUE) {
            echo panelstate($state, $boxname);
        }
    }

    public static function closeside() {
        global $panel_collapse;
        if ($panel_collapse == TRUE) {
            echo "</div>\n";
        }
        echo "</div>";
    }

    /**
     * Set current theme locale
     * @return array
     */
    public static function set_locale() {
        if (empty(self::$locale)) {
            $locale = array();
            if (file_exists(THEME."locale/".LANGUAGE.".php")) {
                include THEME."locale/".LANGUAGE.".php";
            } else {
                include THEME."locale/English.php";
            }
            self::$locale = $locale;
        }

        return self::$locale;
    }

    /**
     * Sets custom header html
     * @param $html
     */
    public static function set_header_html($html) {
        self::$custom_header_html = $html;
    }

    /**
     * Calculation of Bootstrap Grid Span
     * @param int $sm_default
     * @param int $md_default
     * @param int $lg_default
     * @return string
     */
    public static function col_span($sm_default = 3, $md_default = 3, $lg_default = 3) {

        $default_side_span_sm = $sm_default; // <---- change this to change the sidebar width on tablet
        $default_side_span_md = $md_default; //<--- change this to change the sidebar width on laptop
        $default_side_span_lg = $lg_default; // <---- change this to change the sidebar width on desktop
        $how_many_sides_are_visible = 0;

        if ((defined('LEFT') && !empty(LEFT)) || (defined('RIGHT') && !empty(RIGHT))) {
            $how_many_sides_are_visible++;
        }

        if ($how_many_sides_are_visible > 0) {
            $span = array(
                'col-xs-' => 12,
                'col-sm-' => 12 - ($how_many_sides_are_visible * $default_side_span_sm),
                'col-md-' => 12 - ($how_many_sides_are_visible * $default_side_span_md),
                'col-lg-' => 12 - ($how_many_sides_are_visible * $default_side_span_lg),
            );
        } else {
            $span = array(
                'col-xs-' => 12,
                'col-sm-' => 12,
                'col-md-' => 12,
                'col-lg-' => 12,
            );
        }
        $css = '';
        foreach ($span as $css_class => $css_value) {
            $css .= $css_class.$css_value." ";
        }

        return $css;
    }

    /**
     * Theme Output Replacement
     * @param $output
     * @return array
     */
    public static function theme_output($output) {

        $search = array(
            "@><img src='reply' alt='(.*?)' style='border:0px' />@si",
            "@><img src='newthread' alt='(.*?)' style='border:0px;?' />@si",
            "@><img src='web' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
            "@><img src='pm' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
            "@><img src='quote' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
            "@><img src='forum_edit' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
            "@<a href='".ADMIN."comments.php(.*?)&amp;ctype=(.*?)&amp;cid=(.*?)'>(.*?)</a>@si"
        );
        $replace = array(
            ' class="big button"><span class="reply-button icon"></span>$1',
            ' class="big button"><span class="newthread-button icon"></span>$1',
            ' class="button" rel="nofollow" title="$1"><span class="web-button icon"></span>Web',
            ' class="button" title="$1"><span class="pm-button icon"></span>PM',
            ' class="button" title="$1"><span class="quote-button icon"></span>$1',
            ' class="negative button" title="$1"><span class="edit-button icon"></span>$1',
            '<a href="'.ADMIN.'comments.php$1&amp;ctype=$2&amp;cid=$3" class="big button"><span class="settings-button icon"></span>$4</a>'
        );
        $output = preg_replace($search, $replace, $output);

        return $output;
    }

    /**
     * Septenary Header
     */
    public function displayHeader() {
        $aidlink = fusion_get_aidlink();
        $userdata = fusion_get_userdata();
        $locale = self::$locale;

        echo "<header id='top'>";
        echo "<div class='overlay'>\n";
        $this->open_grid('section-1', 1);
        echo "<div class='row hidden-xs'>\n";
        echo "<div id='logo' class='hidden-xs hidden-md col-lg-3 p-t-5 text-smaller'>\n</div>\n";
        echo "<div class='col-xs-12 col-md-9 col-lg-9 text-right clearfix'>\n";

        echo "<div class='display-inline-block' style='width:30%; float:right;'>\n";
        echo openform('searchform', 'post', BASEDIR.'search.php?stype=all',
                      array(
                          'class' => 'm-b-10',
                          'remote_url' => fusion_get_settings('site_path')."search.php"
                      )
        );
        echo form_text('stext', '', '', array(
            'placeholder' => $locale['sept_006'],
            'append_button' => TRUE,
            'append_type' => "submit",
            "append_form_value" => $locale['sept_006'],
            "append_value" => "<i class='fa fa-search'></i> ".$locale['sept_006'],
            "append_button_name" => "search",
            'class' => 'no-border m-b-0',
        ));
        echo closeform();
        echo "</div>\n";

        echo "<ul id='head_nav' class='display-inline-block'>\n";

        $language_opts = '';
        if (count(fusion_get_enabled_languages()) > 1) {

            $language_opts = "<li class='dropdown'>\n";
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
            echo "<li><a href='".BASEDIR."login.php'>".$locale['sept_001']."</a></li>\n";
            if (fusion_get_settings("enable_registration")) {
                echo "<li><a href='".BASEDIR."register.php'>".$locale['sept_002']."</a></li>\n";
            }
            echo $language_opts;
        } else {
            if (iADMIN) {
                echo "<li>\n<a href='".ADMIN.$aidlink."&amp;pagenum=0'>".$locale['sept_003']."</a>\n</li>\n";
            }
            echo "<li>\n<a href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>".$locale['sept_004']."</a>\n</li>\n";
            echo $language_opts;
            echo "<li>\n<a href='".BASEDIR."index.php?logout=yes'>".$locale['sept_005']."</a></li>\n";
        }

        echo "</ul>\n";
        echo "</div>\n";
        echo "</div>\n";
        $this->close_grid(1);

        $this->open_grid('section-2', 1);
        echo "<div class='header-nav'>\n";
        echo showsublinks('', 'navbar-default', ['show_header' => TRUE])."\n";
        echo "</div>\n";
        $this->close_grid();
        echo "</div>\n";

        $this->display_Showcase();
        echo "</header>\n";
    }

    /**
     * Open Section
     * @param            $class
     * @param bool|FALSE $box
     */
    public static function open_grid($class, $box = FALSE) {
        echo "<div class='".$class."'>\n";
        echo ($box) ? "<div class='container'>\n" : '';
    }

    /**
     * Close Section
     * @param bool|FALSE $box
     */
    public static function close_grid($box = FALSE) {
        echo "</div>\n";
        echo ($box) ? "</div>\n" : '';
    }

    /**
     * Display Showcase
     */
    protected function display_Showcase() {

        $settings = fusion_get_settings();
        $locale = self::$locale;

        $this->open_grid('section-showcase', 1);

        if (!empty(self::$custom_header_html)) {

            add_to_head('<style>.section-showcase > .container { background-color: #fff !important; color: #444; }</style>');

            echo self::$custom_header_html;

        } else {

            $file_path = str_replace(ltrim(fusion_get_settings('site_path'),'/'), '', preg_replace('/^\//', '', FUSION_REQUEST));
            if ($settings['site_seo'] && defined('IN_PERMALINK')) {
                require_once CLASSES.'PHPFusion/Rewrite/Router.inc';
                $file_path = Router::getRouterInstance()->getCurrentURL();
            }

            if ($settings['opening_page'] == $file_path) {
                echo "<div class='text-center logo'>\n";
                if ($settings['sitebanner']) {
                    echo "<a href='".BASEDIR."'><img class='img-responsive' src='".BASEDIR.$settings['sitebanner']."' alt='".$settings['sitename']."' style='border: 0;' /></a>\n";
                } else {
                    echo "<a href='".BASEDIR."'>".$settings['sitename']."</a>\n";
                }
                echo "</div>\n";
                echo "<h2 class='text-center text-uppercase' style='letter-spacing:10px; font-weight:300; font-size:36px;'>".$settings['sitename']."</h2>\n";
                //echo "<div class='text-center' style='font-size:19.5px; line-height:35px; font-weight:300; color:rgba(255,255,255,0.8'>".stripslashes($settings['siteintro'])."</div>\n";
                $modules = array(
                    DB_PREFIX.'news'      => infusion_exists('news'),
                    DB_PREFIX.'photos'    => infusion_exists('gallery'),
                    DB_PREFIX.'forums'    => infusion_exists('forum'),
                    DB_PREFIX.'downloads' => infusion_exists('downloads')
                );
                $sum = array_sum($modules);
                if ($sum) {
                    $size = 12 / $sum;
                    $sizeClasses = 'col-sm-'.$size.' col-md-'.$size.' col-lg-'.$size;
                    echo "<div class='section-2-row row'>\n";
                    if ($modules[DB_PREFIX.'news']) {
                        echo "<div class='$sizeClasses section-2-tab text-center'>\n";
                        echo "<a href='".INFUSIONS."news/news.php'>\n";
                        echo "<i class='fa fa-newspaper-o fa-2x'></i>\n";
                        echo "<h4>".$locale['sept_007']."</h4>";
                        echo "</a>\n";
                        echo "</div>\n";
                    }
                    if ($modules[DB_PREFIX.'photos']) {
                        echo "<div class='$sizeClasses section-2-tab text-center'>\n";
                        echo "<a href='".INFUSIONS."gallery/gallery.php'>\n";
                        echo "<i class='fa fa-camera-retro fa-2x'></i>\n";
                        echo "<h4>".$locale['sept_008']."</h4>";
                        echo "</a>\n";
                        echo "</div>\n";
                    }
                    if ($modules[DB_PREFIX.'forums']) {
                        echo "<div class='$sizeClasses section-2-tab text-center'>\n";
                        echo "<a href='".INFUSIONS."forum/index.php'>\n";
                        echo "<i class='fa fa-comments fa-2x'></i>\n";
                        echo "<h4>".$locale['sept_009']."</h4>";
                        echo "</a>\n";
                        echo "</div>\n";
                    }
                    if ($modules[DB_PREFIX.'downloads']) {
                        echo "<div class='$sizeClasses section-2-tab text-center'>\n";
                        echo "<a href='".INFUSIONS."downloads/downloads.php'>\n";
                        echo "<i class='fa fa-download fa-2x'></i>\n";
                        echo "<h4>".$locale['sept_010']."</h4>";
                        echo "</a>\n";
                        echo "</div>\n";
                    }
                    echo "</div>\n";
                }
            } else {

                // use SQL search for page title.
                $result = dbquery("SELECT link_name FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")."  link_url='".$file_path."'");
                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                    $link_name = $data['link_name'];
                } else {
                    $link_name = $settings['sitename'];
                }
                echo "<h2 class='septenary_showcase_title'>$link_name</h2>\n";
                add_to_head('<style>.heading h2 { display:none !important; } .footer {margin-top:0px;} .section-showcase { height:150px; }</style>');
            }

        }

        if (FUSION_SELF == 'login.php') {
            /* Custom Overrides CSS just for login */
            add_to_head('<style>.heading h2 { display:none !important; } .footer {margin-top:0px;} .section-showcase { height:594px; }</style>');
            echo CONTENT;
        }

        $this->close_grid(1);
        echo "</div>\n"; // .overlay

    }

    /**
     * Displays Septenary Footer
     */
    protected function displayFooter() {
        $locale = self::$locale;
        $settings = fusion_get_settings();

        $this->open_grid('footer', TRUE);

        echo "<div class='row m-b-20'>\n";
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

        echo "<div class='footer-row row'>\n";
        echo "<div class='hidden-xs col-sm-3 col-md-3 col-lg-3'>\n";
        echo "<img style='width:80%;' alt='".$locale['sept_011']."' class='img-responsive' src='".THEME."images/htmlcss.jpg' />";
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9 footer-right-col'>\n";
        echo "<div class='pull-right'>\n";
        echo "<a href='#top'><i style='font-size:50px;' class='fa fa-arrow-circle-o-up mid-opacity'></i></a>\n";
        echo "</div>\n";
        echo "<p class='text-left'>".stripslashes(strip_tags($settings['footer']))."</p>
	    <p>".showcopyright()."</p>
	    <p>Septenary Theme by <a href='https://www.php-fusion.co.uk/profile.php?lookup=3674' target='_blank'>Craig</a> and <a href='https://www.php-fusion.co.uk/profile.php?lookup=16331' target='_blank'>Chan</a></p>
	    <p>";
        if ($settings['visitorcounter_enabled']) {
            echo "<p>".showcounter()."</p>\n";
        }
        if ($settings['rendertime_enabled'] == '1' || $settings['rendertime_enabled'] == '2') {
            // Make showing of queries and memory usage separate settings
            echo showrendertime();
            echo showMemoryUsage();
        }
        $footer_errors = showFooterErrors();
        if (!empty($footer_errors)) {
            echo "<div>\n".showFooterErrors()."</div>\n";
        }

        echo "</p>\n";
        echo "</div>\n";
        echo "</div>\n";
        $this->close_grid(1);
    }

}
