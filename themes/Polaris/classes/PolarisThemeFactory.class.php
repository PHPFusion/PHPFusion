<?php

use PHPFusion\Administration\Sitelinks;

fusion_load_script( INCLUDES . "fonts/PHPFusion/font.min.css?v2", "css" );

if ( !defined( 'POLARIS_LOCALE' ) ) {
    if (file_exists( THEMES . 'Polaris/locale/' . LANGUAGE . '.php' ) ) {
        define( 'POLARIS_LOCALE', THEMES . 'Polaris/locale/' . LANGUAGE . '.php' );
    } else {
        define( 'POLARIS_LOCALE', THEMES . 'Polaris/locale/English.php' );
    }
}

if (!defined('BOOTSTRAP')) {
    define('BOOTSTRAP', true);
}

if (!defined('FONTAWESOME')) {
    define('FONTAWESOME', true);
}


abstract class PolarisThemeFactory
{

    protected $is_layout_fluid = true;

    protected $is_menu_fluid = true;

    protected $theme_settings;

    protected $siteLinksOptions = [];
    protected $content;
    protected $left;
    protected $right;

    public function setLayoutClass()
    {
        $this->content = ['sm' => 12, 'md' => 12, 'lg' => 12];
        $this->left    = ['sm' => 3,  'md' => 2,  'lg' => 2];
        $this->right   = ['sm' => 3,  'md' => 2,  'lg' => 2];

        if (LEFT) {
            $this->content['sm'] = $this->content['sm'] - $this->left['sm'];
            $this->content['md'] = $this->content['md'] - $this->left['md'];
            $this->content['lg'] = $this->content['lg'] - $this->left['lg'];
        }

        if (RIGHT) {
            $this->content['sm'] = $this->content['sm'] - $this->right['sm'];
            $this->content['md'] = $this->content['md'] - $this->right['md'];
            $this->content['lg'] = $this->content['lg'] - $this->right['lg'];
        }
    }

    protected function getContentLayoutClass()
    {
        return 'col-xs-12 col-sm-' . $this->content['sm'] . ' col-md-' . $this->content['md'] . ' col-lg-' . $this->content['lg'];
    }

    protected function getLeftLayoutClass()
    {
        return 'col-xs-12 col-sm-' . $this->left['sm'] . ' col-md-' . $this->left['md'] . ' col-lg-' . $this->left['lg'];
    }

    protected function getRightLayoutClass()
    {
        return 'col-xs-12 col-sm-' . $this->right['sm'] . ' col-md-' . $this->right['md'] . ' col-lg-' . $this->right['lg'];
    }



    public function setLayoutBox($value)
    {
        $this->is_layout_fluid = $value;
    }
    public function setMenuFluid($value) {
        $this->is_menu_fluid = $value;
    }

    public function setSiteLinksOptions($id = 'main-menu', $nav_class = 'nav navbar-nav primary', $container = NULL, $container_fluid = NULL, $show_header = NULL)
    {
        $settings = fusion_get_settings();

        $default_options = [
            'container_fluid' => $this->is_menu_fluid, //!defined('CONTAINER_OFF'),
            'container' => !$this->is_menu_fluid,
            'show_header' => '<a class="navbar-brand" href="' . BASEDIR . $settings['opening_page'] . '"><img src="' . BASEDIR . $settings['sitebanner'] . '" alt="' . $settings['sitename'] . '" class="img-responsive"/></a>'
            //'html_pre_content' => mg_user_menu()
        ];

        $this->siteLinksOptions = [
            'id' => $id,
            'nav_class' => $nav_class,
            'container_fluid' => $container != NULL ? $container : $default_options['container_fluid'],
            'container' => $container != NULL ? $container : $default_options['container'],
            // if show header is not false, show default or custom header
            'show_header' => $show_header != NULL ? $show_header : $default_options['show_header'],
            /*
             'navbar_class'         => defined('BOOTSTRAP4') ? 'navbar-expand-lg navbar-light' : 'navbar-default',
            'additional_nav_class' => '',
            'item_class'           => defined('BOOTSTRAP4') ? 'nav-item' : '', // $class
            'locale'               => [],
            'separator'            => '', // $sep
            'links_per_page'       => '',
            'grouping'             => '',
            'show_banner'          => FALSE,
            'show_header'          => FALSE,
            'custom_header'        => '',
            'language_switcher'    => FALSE,
            'searchbar'            => FALSE,
            'search_icon'          => 'fa fa-search',
            'searchbar_btn_class'  => 'btn-primary',
            'caret_icon'           => defined('BOOTSTRAP4') ? '' : 'caret',
            'link_position'        => [2, 3],
            'html_pre_content'     => '',
            'html_content'         => '',
            'html_post_content'    => ''
            */
        ];
        //print_P($this->siteLinksOptions);
    }

    public function getLayoutClass()
    {
        if ($this->is_layout_fluid) {
            return 'container-fluid';
        } else {
            return 'container';
        }
    }

    public function getSiteLinksOptions($key = NULL)
    {
        if ($key !== NULL) {
            return isset($this->siteLinksOptions[$key]) ? $this->siteLinksOptions[$key] : '';
        }
        return $this->siteLinksOptions;
    }
}
