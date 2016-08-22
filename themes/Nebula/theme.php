<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Nebula/theme.php
| Author: PHP-Fusion Inc
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once INCLUDES."theme_functions_include.php";
require_once THEME."autoloader.php";

define("THEME_BULLET", "<i class='fa fa-list'></i>");

function render_page($license = FALSE) {
    \Nebula\NebulaTheme::getInstance()->render_page($license);
}

function opentable($title = FALSE) {
    \Nebula\Layouts\Compo::opentable($title);
}

function closetable() {
    \Nebula\Layouts\Compo::closetable();
}

function openside($title = FALSE) {
}

function closeside($title = FALSE) {

}

function display_loginform($info) {
    $theme = \Nebula\NebulaTheme::getInstance();
    $theme->setParam('header', FALSE);
    $theme->setParam('footer', FALSE);
    \Nebula\Templates\Login::login_form($info);
}

function display_registerform($info) {
    $theme = \Nebula\NebulaTheme::getInstance();
    $theme->setParam('header', FALSE);
    $theme->setParam('footer', FALSE);
    \Nebula\Templates\Login::register_form($info);
}

function display_home($info) {
    \Nebula\Templates\Panels\HomePanel::display_page($info);
}
