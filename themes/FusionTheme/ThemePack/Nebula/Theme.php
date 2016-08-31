<?php
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
    $theme = \Nebula\NebulaTheme::getInstance();
    $theme->setParam('header', FALSE);
    $theme->setParam('footer', FALSE);
    ThemePack\Nebula\Templates\Login::register_form($info);
}

function display_home($info) {
    require_once INFUSIONS."home_panel/home_class.php";
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