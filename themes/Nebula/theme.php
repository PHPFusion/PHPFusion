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
    \Nebula\Layouts\Compo::openside($title);
}

function closeside($title = FALSE) {
    \Nebula\Layouts\Compo::closeside();
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
    require_once INFUSIONS."home_panel/home_class.php";
    \Nebula\Templates\Panels\HomePanel::display_page($info);
}

function display_page($info) {
    \Nebula\Templates\Page::display_page($info);
}

/**
 * News - News Home
 * @param $info
 */
function display_main_news($info) {
    \Nebula\Templates\News::display_news($info);
}

/**
 * News - Full News Page HTML
 * @param $info
 */
function render_news_item($info) {
    \Nebula\Templates\News::render_news_item($info);
}

/**
 * News - News Item @ Home
 * @param $info
 */
function render_news($info) {
    \Nebula\Templates\News::render_news($info);
}