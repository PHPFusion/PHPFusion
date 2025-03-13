<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Polaris/classes/PolarisThemeFactory.autoloader.php
| Author: Meangczac (Chan), Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

fusion_load_script(INCLUDES . "fonts/PHPFusion/font.min.css?v2", "css");

if (!defined('POLARIS_LOCALE')) {
    if (file_exists(THEMES . 'Polaris/locale/' . LANGUAGE . '.php')) {
        define('POLARIS_LOCALE', THEMES . 'Polaris/locale/' . LANGUAGE . '.php');
    } else {
        define('POLARIS_LOCALE', THEMES . 'Polaris/locale/English.php');
    }
}

if (!defined('BOOTSTRAP')) {
    define('BOOTSTRAP', true);
}

if (!defined('FONTAWESOME')) {
    define('FONTAWESOME', true);
}
const ARROW = '<span><svg viewBox="0 0 24 24" fill="none" class="dig-UIIcon dig-UIIcon--standard" width="24" height="24" role="presentation" focusable="false"><path d="M5 11.75h12m-5.25-6.5 6.25 6.5-6.25 6.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" vector-effect="non-scaling-stroke"></path></svg></span>';

/**
 * PolarisThemeFactory class
 * @package Polaris
 */
abstract class PolarisThemeFactory
{

    protected $is_layout_fluid = true;

    protected $is_menu_fluid = true;

    protected $theme_settings;

    protected $siteLinksOptions = [];

    protected $content;

    protected $left;

    protected $right;

    // Prohibits cloning of this class
    private function __clone() {}

    /**
     * Calculate the grid class based on panel contents based on Bootstrap by X
     */
    public function setLayoutClass()
    {
        $this->content = ['sm' => 12, 'md' => 12, 'lg' => 12];
        $this->left    = ['sm' => 3,  'md' => 2,  'lg' => 2];
        $this->right   = ['sm' => 3,  'md' => 2,  'lg' => 2];

        if (is_constant_set('LEFT')) {
            $this->content['sm'] = $this->content['sm'] - $this->left['sm'];
            $this->content['md'] = $this->content['md'] - $this->left['md'];
            $this->content['lg'] = $this->content['lg'] - $this->left['lg'];
        }

        if (is_constant_set('RIGHT')) {
            $this->content['sm'] = $this->content['sm'] - $this->right['sm'];
            $this->content['md'] = $this->content['md'] - $this->right['md'];
            $this->content['lg'] = $this->content['lg'] - $this->right['lg'];
        }
    }

    /**
     * Primary column
     * @return string
     */
    protected function getContentLayoutClass()
    {
        return 'polaris-col-m col-xs-12 col-sm-' . $this->content['sm'] . ' col-md-' . $this->content['md'] . ' col-lg-' . $this->content['lg'];
    }

    /**
     * Left column class
     * @return string
     */

    protected function getLeftLayoutClass()
    {
        return 'polaris-col-l col-xs-12 col-sm-' . $this->left['sm'] . ' col-md-' . $this->left['md'] . ' col-lg-' . $this->left['lg'];
    }

    /**
     * Right column class
     * @return string
     */
    protected function getRightLayoutClass()
    {
        return 'polaris-col-r col-xs-12 col-sm-' . $this->right['sm'] . ' col-md-' . $this->right['md'] . ' col-lg-' . $this->right['lg'];
    }

    /**
     * Layout box class
     * @return string
     */
    public function setLayoutBox($value)
    {
        $this->is_layout_fluid = $value;
    }

    /**
     * Menu fluid class
     */
    public function setMenuFluid($value)
    {
        $this->is_menu_fluid = $value;
    }

    /**
     * Site links options
     * @param string $id - id of the menu
     * @param string $nav_class - class of the navigation
     * @param string $container - container class
     * @param string $container_fluid - container fluid class
     * @param boolean $show_header - show header or not
     * @return void
     */
    public function setSiteLinksOptions(
        $id = 'main-menu',
        $nav_class = 'nav navbar-nav primary',
        $container = NULL,
        $container_fluid = NULL,
        $show_header = NULL,
        $searchbar = NULL,
        $searchbar_dropdown = NULL,
        $html_pre_content = NULL,
        $html_content = NULL,
        $html_post_content = NULL
    ) {
        $settings = fusion_get_settings();

        $default_options = [
            'container_fluid' => $this->is_menu_fluid, //!defined('CONTAINER_OFF'),
            'container' => !$this->is_menu_fluid,
            'show_header' => '<a class="navbar-brand" href="' . BASEDIR . $settings['opening_page'] . '"><img src="' . BASEDIR . $settings['sitebanner'] . '" alt="' . $settings['sitename'] . '" class="img-responsive"/></a>',
            'searchbar' => FALSE,
            'searchbar_dropdown' => TRUE,
            'html_post_content' => '',
            'html_content' => '',
            'html_post_content' => '',
            'html_pre_content' => '' //polaris_uip()
        ];

        $this->siteLinksOptions = [
            'id' => $id,
            'nav_class' => $nav_class,
            'container_fluid' => $container_fluid != NULL ? $container : $default_options['container_fluid'],
            'container' => $container != NULL ? $container : $default_options['container'],
            // if show header is not false, show default or custom header
            'show_header' => $show_header != NULL ? $show_header : $default_options['show_header'],
            'searchbar'            => $searchbar != NULL ? $searchbar : $default_options['searchbar'],
            'searchbar_dropdown' => $searchbar_dropdown != NULL ? $searchbar_dropdown : $default_options['searchbar_dropdown'],
            'html_post_content' => $html_post_content != NULL ? $html_post_content : $default_options['html_post_content'],
            'html_content' => $html_content != NULL ? $html_content : $default_options['html_content'],
            'html_pre_content' => $html_pre_content != NULL ? $html_pre_content : $default_options['html_pre_content']

            /*
             'navbar_class'         => defined('BOOTSTRAP4') ? 'navbar-expand-lg navbar-light' : 'navbar-default',
            'additional_nav_class' => '',
            'item_class'           => defined('BOOTSTRAP4') ? 'nav-item' : '', // $class
            'locale'               => [],
            'separator'            => '', // $sep
            'links_per_page'       => '',
            'grouping'             => '',
            'show_banner'          => FALSE,
            'custom_header'        => '',
            'language_switcher'    => FALSE,
            'searchbar'            => FALSE,
            'search_icon'          => 'fa fa-search',
            'searchbar_btn_class'  => 'btn-primary',
            'caret_icon'           => defined('BOOTSTRAP4') ? '' : 'caret',
            'link_position'        => [2, 3],
            */
        ];
    }

    /**
     * Get the layout class based on whether it's fluid or not.
     * @return string
     */
    public function getLayoutClass()
    {
        if ($this->is_layout_fluid) {
            return 'container-fluid';
        } else {
            return 'container';
        }
    }

    /**
     * Get the site links options.
     * @return array|string
     */
    public function getSiteLinksOptions($key = NULL)
    {
        if ($key !== NULL) {
            return isset($this->siteLinksOptions[$key]) ? $this->siteLinksOptions[$key] : '';
        }
        return $this->siteLinksOptions;
    }
}
