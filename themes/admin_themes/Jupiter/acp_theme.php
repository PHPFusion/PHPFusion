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
use PHPFusion\Jupiter\Classes\View\AdminComponents;
use PHPFusion\Jupiter\Classes\View\AdminPanel;
use PHPFusion\OutputHandler;

defined('IN_FUSION') || exit;

const UI_FRAMEWORK = 'tailwind';
define('ADMIN_CSS_FILE', 'acp_styles.min.css?v='.filemtime(__DIR__.'/acp_styles.min.css'));

require_once BASEDIR.'assets/libs/iconify/iconify_include.php';

define("ADMIN_THEME_LOCALE", admin_theme_locale());
define('THEME_BODY', admin_theme_body());

fusion_load_script(INCLUDES . "jquery/jquery.fusion-objects.js");
//<!-- Libs JS -->
fusion_load_script(BASEDIR . 'assets/libs/simplebar/dist/simplebar.min.js', 'js');

/* Gets admin theme locale file */
function admin_theme_locale(): string {
    $locale_file = THEMES.'admin_themes/Jupiter/locale/'.LANGUAGE.'.php';
    if (!file_exists($locale_file)) {
        $locale_file = LOCALE.LOCALESET.'admin/main.php';
    }

    return $locale_file;
}

/* Gets admin theme body definition */
function admin_theme_body() {
    if (!check_admin_pass('')) {
        return '<body class="hold-transition lockscreen jupiter-admin-login" data-bs-theme="dark">';
    }
    return '<body class="hold-transition jupiter-admin-shell" data-bs-theme="dark">';
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
    $color = '090d10';
    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});
