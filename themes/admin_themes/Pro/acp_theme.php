<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: acp_theme.php
| Author: RobiNN
| Version: 1.4.1
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
use PHPFusion\Pro\Classes\View\AdminComponents;
use PHPFusion\Pro\Classes\View\AdminPanel;
use PHPFusion\OutputHandler;

defined('IN_FUSION') || exit;

const BOOTSTRAP = 5;

define("ADMIN_THEME_LOCALE", admin_theme_locale());
define('THEME_BODY', admin_theme_body());

add_to_head('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">');
fusion_load_script('https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js');
fusion_load_script('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', 'css');

/* Gets admin theme locale file */
function admin_theme_locale(): string {
    $locale_file = THEMES.'admin_themes/Pro/locale/English.php';
    if (file_exists(THEMES.'admin_themes/Pro/locale/'.LANGUAGE.'.php')) {
        $locale_file = THEMES.'admin_themes/Pro/locale/'.LANGUAGE.'.php';
    }
    return $locale_file;
}

/* Gets admin theme body definition */
function admin_theme_body() {
    if (!check_admin_pass('')) {
        return '<body class="hold-transition lockscreen">';
    }
    return '<body class="hold-transition skin-blue sidebar-mini">';
}

require_once __DIR__.'/classes/view/admincomponents.class.php';
require_once __DIR__.'/classes/view/adminpanel.class.php';
require_once __DIR__.'/classes/adminhelper.class.php';

/* Admin Panel */
function render_admin_panel() {
    AdminPanel::getInstance()->viewTheme();
}

/* Admin Login */
function render_admin_login() {
    AdminPanel::getInstance()->viewLogin();
}
/* Dashboard */
function render_admin_dashboard() {
    AdminPanel::getInstance()->viewDashboard();
}

/* Side */
function openside($value = FALSE, $collapse = FALSE, $class = '') {

    (new AdminComponents())->openSide($value, $collapse, $class);
}

/* Closeside */
function closeside() {
    (new AdminComponents())->closeSide();
}

/* Table */
function opentable($title = NULL, $class = NULL, $bg = TRUE) {
    //Firstcamp\AdminPanel::openTable($title, $class, $bg);
}

function closetable($bg = TRUE) {
    //Firstcamp\AdminPanel::closeTable($bg);
}

function opengrid($value = 3, $class= NULL) {
    (new AdminComponents())->openGrid($value, $class);
}

function closegrid() {
    (new AdminComponents())->closeGrid();
}

OutputHandler::addHandler(function ($output = '') {
    $color = !check_admin_pass('') ? 'd2d6de' : '3c8dbc';
    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});

