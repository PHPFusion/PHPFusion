<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminLTE/acp_theme.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

if (!defined('ALTE_LOCALE')) {
    if (file_exists(THEMES.'admin_themes/AdminLTE/locale/'.LANGUAGE.'.php')) {
        define('ALTE_LOCALE', THEMES.'admin_themes/AdminLTE/locale/'.LANGUAGE.'.php');
    } else {
        define('ALTE_LOCALE', THEMES.'admin_themes/AdminLTE/locale/English.php');
    }
}

define('ADMINLTE', THEMES.'admin_themes/AdminLTE/');
require_once INCLUDES.'theme_functions_include.php';
require_once ADMINLTE.'acp_autoloader.php';

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

define("IS_V9", (version_compare(fusion_get_settings('version'), '8.0', (strpos(fusion_get_settings('version'), '9.') === 0 ? '>' : '<'))) ? TRUE : FALSE);

if (IS_V9) {
    if (fusion_get_settings('version') != '9.0') {
        // v9.03
        if (!check_admin_pass('')) {
            define('THEME_BODY', '<body class="hold-transition lockscreen">');
        } else {
            define('THEME_BODY', '<body class="hold-transition skin-blue sidebar-mini">');
        }
    } else {
        // v9.0
        if (!check_admin_pass('')) {
            add_to_jquery('if (!$("body").hasClass("hold-transition") || !$("body").hasClass("lockscreen")) {$("body").addClass("hold-transition lockscreen");}');
        } else {
            add_to_jquery('if (!$("body").hasClass("hold-transition") || !$("body").hasClass("skin-blue")) {$("body").addClass("hold-transition skin-blue sidebar-mini");}');
        }

        \PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();
    }
} else {
    // v8
    if (!check_admin_pass('') && !stristr($_SERVER['PHP_SELF'], $settings['site_path'].'infusions')) {
        define('THEME_BODY', '<body class="hold-transition lockscreen">');
    } else {
        define('THEME_BODY', '<body class="hold-transition skin-blue sidebar-mini">');
    }

    \PHPFusion\OutputHandler::addHandler(function ($output = '') {
        return strtr($output, [
            'class=\'textbox' => 'class=\'textbox form-control m-t-5 m-b-5',
            'class="textbox'  => 'class="textbox form-control m-t-5 m-b-5',
            'class=\'button'  => 'class=\'button btn btn-default',
            'class="button'   => 'class="button btn btn-default'
        ]);
    });
}

function render_admin_panel() {
    new AdminLTE\AdminPanel();
}

function render_admin_login() {
    new AdminLTE\Login();
}

function render_admin_dashboard() {
    new AdminLTE\Dashboard();
}

function openside($title = FALSE, $class = NULL) {
    $html = '<div class="box box-widget '.$class.'">';
    $html .= $title ? '<div class="box-header with-border">'.$title.'</div>' : '';
    $html .= '<div class="box-body">';

    echo $html;
}

function closeside($footer = FALSE) {
    $html = '</div>';
    $html .= $footer ? '<div class="box-footer">'.$footer.'</div>' : '';
    $html .= '</div>';

    echo $html;
}

function opentable($title, $class = NULL, $bg = TRUE) {
    AdminLTE\AdminPanel::OpenTable($title, $class, $bg);
}

function closetable($bg = TRUE) {
    AdminLTE\AdminPanel::CloseTable($bg);
}

\PHPFusion\OutputHandler::addHandler(function ($output = '') {
    $color = !check_admin_pass('') ? 'd2d6de' : '3c8dbc';

    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});
