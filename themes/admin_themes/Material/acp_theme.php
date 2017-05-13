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

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();

function render_admin_panel() {
    new Material\AdminPanel();
}

function render_admin_login() {
    new Material\Login();
}

function render_admin_dashboard() {
    Material\Dashboard::AdminDashboard();
}

function openside($title = FALSE, $class = NULL) {
    Material\Components::OpenSide($title, $class);
}

function closeside($title = FALSE) {
    Material\Components::CloseSide($title);
}

function opentable($title, $class = NULL) {
    Material\Components::OpenTable($title, $class);
}

function closetable() {
    Material\Components::CloseTable();
}

function replace_meta($output = '') {
    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#243447"/>', $output);
}

add_handler('replace_meta');
