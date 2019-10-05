<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Core.php
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
namespace ThemeFactory;

use PHPFusion\SiteLinks;

class Core {
    /*
     * readme.md
     */
    public static $locale = [];
    protected static $options = [
        'header'                 => TRUE, // has header
        'header_content'         => '', // content in the header
        'header_container'       => TRUE,
        'headerBg'               => TRUE, // use header_background
        'headerBg_class'         => '', // use custom header background class
        'navbar_class'           => '',
        'navbar_container'       => TRUE,
        'navbar_language_switch' => TRUE,
        'navbar_searchbar'       => TRUE,
        'navbar_show_header'     => TRUE,
        'nav_offset'             => 60,
        'subheader'              => TRUE,
        'subheader_content'      => '',
        'subheader_container'    => TRUE,
        'subheader_class'        => '', // sets the class to the subheader
        'body_class'             => '', // set body section class
        'body_container'         => TRUE, // whether is a container or full grid
        'breadcrumbs'            => FALSE, // show breadcrumbs
        'right'                  => TRUE, // RIGHT
        'left'                   => TRUE, // LEFT
        'left_pre_content'       => '',
        'left_post_content'      => '',
        'user_1'                 => TRUE,
        'user_1_content'         => '',
        'user_1_container'       => TRUE,
        'user_2'                 => TRUE,
        'user_2_content'         => '',
        'user_2_container'       => TRUE,
        'user_3'                 => TRUE,
        'user_3_content'         => '',
        'user_3_container'       => TRUE,
        'user_4'                 => TRUE,
        'user_4_content'         => '',
        'user_4_container'       => TRUE,
        'top_1'                  => TRUE, //status
        'top_1_content'          => '',
        'top_1_container'        => TRUE,
        'bottom_1'               => TRUE, //status
        'bottom_1_content'       => '',
        'bottom_1_container'     => TRUE,
        'upper'                  => TRUE, //status
        'upper_content'          => '',
        'upper_container'        => TRUE,
        'u_center'               => TRUE,
        'u_center_content'       => '',
        'u_center_container'     => TRUE,
        'bl_center'              => TRUE,
        'bl_center_content'      => '',
        'bl_center_container'    => TRUE,
        'l_center'               => TRUE,
        'l_center_content'       => '',
        'l_center_container'     => TRUE,
        'footer'                 => TRUE, // has footer
        'footer_container'       => TRUE,
        'copyright'              => TRUE,
        'copyright_container'    => TRUE,
        'right_span'             => 3,
        'right_class'            => '',
        'left_span'              => 2,
        'left_class'             => '',
        'main_span'              => '',
        'main_class'             => '',
        'right_is_affix'         => FALSE, // @todo: auto affix
        'right_pre_content'      => '', // right side top content
        'right_post_content'     => '', // right side bottom content,
        'header_affix'           => FALSE,
    ];
    private static $instance = NULL;
    private static $module_instance = NULL;
    private static $module_list = [];
    /**
     * @var string
     */
    public $cssPath = '';
    /**
     * @var bool
     */
    public $devMode = TRUE;

    public function __construct() {
        if (file_exists(THEME.'locale/'.LANGUAGE.'.php')) {
            self::$locale = fusion_get_locale('', THEME.'locale/'.LANGUAGE.'.php');
        } else {
            self::$locale = fusion_get_locale('', THEME.'locale/English.php');
        }

        if (empty(self::$module_list)) {
            // Get Theme Factory Modules
            $ModuleType = makefilelist(THEME."themefactory/lib/modules/", ".|..|.htaccess|index.php|._DS_STORE|.tmp", TRUE, "folders");
            if (!empty($ModuleType)) {
                foreach ($ModuleType as $ModuleFolder) {
                    $Modules = makefilelist(THEME."themefactory/lib/modules/$ModuleFolder/", ".|..|.htaccess|index.php|._DS_STORE|.tmp");
                    if (!empty($Modules)) {
                        foreach ($Modules as $ModuleFile) {
                            self::$module_list[] = "$ModuleFolder\\".str_replace('.php', '', $ModuleFile);
                        }
                    }
                }
            }
        }

    }

    public static function DisplayCopyrightPanel() {

        $menu_config = [
            "id"                => "footer_1",
            "container"         => FALSE,
            'navbar_class'      => 'nav-stacked text-sm',
            'nav_class'         => "block strong",
            "language_switcher" => FALSE,
            "searchbar"         => FALSE,
            "caret_icon"        => "fa fa-angle-down",
            "show_banner"       => FALSE,
            "html_content"      => "",
            "grouping"          => fusion_get_settings("links_grouping"),
            "links_per_page"    => fusion_get_settings("links_per_page"),
            "show_header"       => FALSE,
            "link_position"     => 4,
            'responsive'        => FALSE,
        ];
        $menu_config2 = [
            'id'                => 'footer_3',
            'container'         => FALSE,
            'navbar_class'      => 'nav-stacked text-sm',
            'nav_class'         => "block strong",
            'language_switcher' => FALSE,
            'searchbar'         => FALSE,
            'caret_icon'        => 'fa fa-angle-down',
            'show_banner'       => FALSE,
            'html_content'      => "",
            'grouping'          => fusion_get_settings('links_grouping'),
            'links_per_page'    => fusion_get_settings('links_per_page'),
            'show_header'       => FALSE,
            'link_position'     => 5,
            'responsive'        => FALSE,
        ];

        $privacy_link = BASEDIR."legal/privacy.php";
        $tos_link = BASEDIR."legal/tos.php";
        $coc_link = BASEDIR."legal/coc.php";
        $licensing_link = BASEDIR."licensing/licensing.php";
        $chtml = "";
        if (fusion_get_settings('rendertime_enabled') == '1' || fusion_get_settings('rendertime_enabled') == '2') :
            $chtml .= showrendertime();
            $chtml .= showMemoryUsage();
        endif;
        if (fusion_get_settings('visitorcounter_enabled')) :
            $chtml .= "<br /> <small>".showcounter()."</small>";
        endif;
        $footer_errors = showFooterErrors();
        if (!empty($footer_errors)) :
            $chtml .= $footer_errors;
        endif;

        $html =
            "<div class='panel panel-default'>\n<div class='panel-body'>
            <div class='row'>
                <div class='col-xs-6'>                                
                ".SiteLinks::setSubLinks($menu_config)->showSubLinks()."                
            </div>
                <div class='col-xs-6'>
                ".SiteLinks::setSubLinks($menu_config2)->showSubLinks()."                
            </div>
            </div>
            <div class='text-center text-sm'>            
            <a class='strong' href='$privacy_link'>Privacy Policy</a> &middot;
            <a class='strong' href='$tos_link'>Terms of Service</a> &middot;
            <a class='strong' href='$coc_link'>Code of Conduct</a> &middot;
            <a class='strong' href='$licensing_link'>Licensing</a>
            <br/>
            Powered by PHP-Fusion Copyright © ".date('Y')." PHP-Fusion Inc. Published without warranties under <a href='https://www.php-fusion.co.uk/licensing/licensing.php?epal' target='blank' title='Enduser PHP-Fusion Addon License'>EPAL</a>            
        </div>
        </div>
        ".($chtml ? "<div class='panel-footer text-sm'>$chtml</div>" : "")."                 
        </div>";


        return $html;
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public static function setParam($prop, $value) {
        self::$options[$prop] = (is_bool($value)) ? $value : self::getParam($prop).$value;
    }

    protected static function set_body_span() {
        if (empty(self::getParam('main_span'))) {
            $full_sum = 12;
            $_right_status = (self::getParam('right') && defined('RIGHT') && (RIGHT or self::getParam('right_pre_content') or self::getParam('right_post_content'))) ? TRUE : FALSE;
            $_left_status = (self::getParam('left') && defined('LEFT') && (LEFT or self::getParam('left_pre_content') or self::getParam('left_post_content'))) ? TRUE : FALSE;
            if ($_right_status && $_left_status) {
                $full_sum = $full_sum - (self::getParam('right_span') + self::getParam('left_span'));
            } else {
                $full_sum = $full_sum - ($_left_status ? self::getParam('left_span') : $_right_status ? self::getParam('right_span') : 0);
            }
            self::replaceParam('main_span', $full_sum);
        }
    }

    public static function getParam($prop = FALSE) {
        if (isset(self::$options[$prop])) { // will return an error if $prop is not available
            return self::$options[$prop];
        } /*else {
            print_p($prop);
            debug_print_backtrace();
        }*/

        return NULL;
    }

    public static function replaceParam($prop, $value) {
        self::$options[$prop] = $value;
    }

    public function getThemePack() {

        static $theme_package = '';
        if (empty($theme_package)) {
            $theme_settings = get_theme_settings('FusionTheme');
            $theme_package = !empty($theme_settings['theme_pack']) ? $theme_settings['theme_pack'] : 'nebula';
            if (!defined('THEME_PACK')) {
                define("THEME_PACK", THEME."themepack/".$theme_package."/");
            }
        }

        return $theme_package;
    }

    public function load_themePack($themePack) {
        $path = THEME."themepack/".strtolower($themePack)."/theme.php";
        $css_path_dir = THEME.'themepack'.DIRECTORY_SEPARATOR.strtolower($themePack).DIRECTORY_SEPARATOR;
        $this->cssPath = $css_path_dir.'styles.css';
        $this->cssPath = $this->cssPath.'?v='.filemtime($this->cssPath);
        if (file_exists($css_path_dir.'styles.min.css') && $this->devMode === FALSE) {
            $this->cssPath = $css_path_dir.'styles.min.css';
            $this->cssPath = $this->cssPath.'?v='.filemtime($this->cssPath);
        }
        add_to_head("<link rel='stylesheet' href='$this->cssPath' type='text/css'/>");

        require_once $path;
    }

    /**
     * @param string $modules
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function get_Modules($modules = 'footer\\news') {
        if (!isset(self::$module_instance[$modules]) or self::$module_instance[$modules] === NULL) {
            if (!empty(self::$module_list)) {
                $module_ = array_flip(self::$module_list);
                if (isset($module_[$modules])) {
                    $namespace_ = "themefactory\\lib\\modules\\";
                    $module_ = new \ReflectionClass($namespace_.$modules);
                    self::$module_instance[$modules] = $module_->newInstance();
                }
            }
        }

        return self::$module_instance[$modules];
    }

    private function __clone() {
    }
}

require_once INCLUDES.'theme_functions_include.php';