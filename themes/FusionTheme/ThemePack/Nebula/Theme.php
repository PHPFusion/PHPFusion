<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /Nebula/Theme.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
define("THEME_BULLET", "<i class='fa fa-list'></i>");

/*
 * SiteLinks Documentation
 *
 * Developer's can now access and adjust the navigation bar in theme file through code now.
 * Theme.php is the most important file in PHP-Fusion because it loads before everything else.
 * i.e. core files, functions and always listens to theme.php's declarations.
 *
 * So in this file, let's inject the following -- (uncomment to test)
 *
 * Example:
 * if (FUSION_SELF == 'index.php') { // or not
 *      // These will add a link in the menu's right hand side as append. We need icons.
 *      \PHPFusion\SiteLinks::addOptionalMenuLink("cart_icon", "<i class='fa fa-shopping-bag'></i>", 0, '#', '', FALSE);
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(1, "Your bag is empty", 'cart_icon', '', '');
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(2, "Bag", 'cart_icon', '', '');
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(3, "Collections", 'cart_icon', '', '');
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(4, "Orders", 'cart_icon', '', '');
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(5, "Account", 'cart_icon', '', '');
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(6, "Sign In", 'cart_icon', '', '');
 * }
 *
 * Of course, alternative method is to add them via SQL to panels the normal method. HOWEVER,
 * this is the ONLY way to insert a calculated menu... Like:
 *
 * Idea Example:
 * if (db_exists(DB_NEWS)) {
 *      $news_megamenu = "<div class='row'>....</div>\n";
 *      \PHPFusion\SiteLinks::addOptionalMenuLink(6, $news_megamenu, 'find-news-id', '', '');
 * }
 */

if (iMEMBER) {
    \PHPFusion\SiteLinks::addOptionalMenuLink('userinfopanel', fusion_get_locale('logout'), 0, FUSION_SELF."?logout=yes", 'fa fa-sign-out fa-fw');
} else {
    \PHPFusion\SiteLinks::addOptionalMenuLink('userinfopanel', fusion_get_locale('login'), 0, BASEDIR."login.php", 'fa fa-sign-in fa-fw');
}


function render_page($license = FALSE) {
    new \ThemePack\Nebula\MainFrame($license);
}

function opentable($title = FALSE) {
    ThemePack\Nebula\Components::opentable($title);
}

function closetable() {
    ThemePack\Nebula\Components::closetable();
}

function openside($title) {
    ThemePack\Nebula\Components::openside($title);
}

function closeside() {
    ThemePack\Nebula\Components::closeside();
}

function display_loginform($info) {
    $theme = ThemeFactory\Core::getInstance();
    $theme->setParam('header', FALSE);
    $theme->setParam('footer', FALSE);
    ThemePack\Nebula\Templates\Login::login_form($info);
}

function display_registerform($info) {
    $theme = ThemeFactory\Core::getInstance();
    $theme->setParam('header', FALSE);
    $theme->setParam('footer', FALSE);
    ThemePack\Nebula\Templates\Login::register_form($info);
}

function display_home($info) {
    require_once(INFUSIONS.'home_panel/home_class.php');
    ThemePack\Nebula\Templates\Panels\HomePanel::display_page($info);
}

function display_page($info) {
    ThemePack\Nebula\Templates\Page::display_page($info);
}

/**
 * News - News Home
 * @param $info
 */
function display_main_news($info) {
    ThemePack\Nebula\Templates\News::display_news($info);
}

/**
 * News - Full News Page HTML
 * @param $info
 */
function render_news_item($info) {
    ThemePack\Nebula\Templates\News::render_news_item($info);
}

/**
 * News - News Item @ Home
 * @param $info
 */
function render_news($info) {
    ThemePack\Nebula\Templates\News::render_news($info);
}

function display_news_submissions_form(array $info = array()) {
    \PHPFusion\OutputHandler::addToHead("<link rel='stylesheet' href='".THEME."ThemePack/Nebula/css/submit.css' type='text/css' media='screen' />");
    \PHPFusion\Panels::getInstance()->hide_panel('LEFT');
    \PHPFusion\Panels::getInstance()->hide_panel('RIGHT');
    $theme = \ThemeFactory\Core::getInstance();
    $theme->setParam('body_class', 'fullform');
    $theme->setParam('body_container', FALSE);
    $theme->setParam('left', FALSE);
    $theme->setParam('right', FALSE);
    $theme->setParam('footer', FALSE);
    $theme->setParam('copyright', FALSE);

    return ThemePack\Nebula\Templates\Submissions::display_news_submissions_form($info);
}

/*
function render_comments($c_data, $c_info, $index = 0) {
    ThemePack\Nebula\Templates\Comment::getInstance()->display_comment($c_data, $c_info, $index);
}
*/