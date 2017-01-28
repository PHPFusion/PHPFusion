<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/acp_theme.php
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

define('MATERIAL', THEMES.'admin_themes/Material/');
require_once INCLUDES.'theme_functions_include.php';
require_once MATERIAL.'theme_autoloader.php';
\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();
Material\Main::AddTo();

function render_admin_login() {
    Material\Main::Login();
}

function render_admin_panel() {
    Material\Main::AdminPanel();
}

function render_dashboard() {
    Material\Dashboard::RenderDashboard();
}

function render_admin_icon() {
    Material\Dashboard::AdminIcons();
}

function render_admin_dashboard() {
    Material\Dashboard::AdminDashboard();
}

function openside($title = FALSE, $class = NULL) {
    Material\Main::OpenSide($title, $class);
}

function closeside($title = FALSE) {
    Material\Main::CloseSide($title);
}

function opentable($title, $class = NULL) {
    Material\Main::OpenTable($title, $class);
}

function closetable() {
    Material\Main::CloseTable();
}

function replace_meta($output = '') {
    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#1e2c3c"/>', $output);
}

add_handler('replace_meta');
