<?php

define("THEME_BULLET", "<i class='fa fa-list'></i>");

function render_page($license = FALSE) {
    new \ThemePack\Nebula\MainFrame($license = FALSE);
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

/*
function render_comments($c_data, $c_info, $index = 0) {
    ThemePack\Nebula\Templates\Comment::getInstance()->display_comment($c_data, $c_info, $index);
}
*/
function render_articles_main($info) {
    ThemePack\Nebula\Templates\Articles::render_articles_main($info);
}

function render_article($subject, $article, $info) {
    ThemePack\Nebula\Templates\Articles::render_article($subject, $article, $info);
}

function render_articles_category($info) {
    ThemePack\Nebula\Templates\Articles::render_articles_category($info);
}