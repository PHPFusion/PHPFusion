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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

define('MATERIAL', THEMES.'admin_themes/Material/');
require_once INCLUDES."theme_functions_include.php";
require_once ADMIN."navigation.php";

spl_autoload_register(function() {
	require_once MATERIAL.'classes/Dashboard.php';
	require_once MATERIAL.'classes/Material.php';
});

Material::AddTo();

function render_admin_login() {
	Material::Login();
}

function render_admin_panel() {
	Material::AdminPanel();
}

function render_dashboard() {
	Material::RenderDashboard();
}

function render_admin_icon() {
	Material::AdminIcons();
}

function render_admin_dashboard() {
	Material::AdminDashboard();
}

function openside($title = FALSE, $class = NULL) {
	Material::OpenSide($title, $class);
}

function closeside($title = FALSE) {
	Material::CloseSide($title);
}

function opentable($title, $class = NULL) {
	Material::OpenTable($title, $class);
}

function closetable() {
	Material::CloseTable();
}
