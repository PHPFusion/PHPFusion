<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme.php
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
defined('IN_FUSION') || exit;

require_once INCLUDES.'theme_functions_include.php';
require_once 'theme_autoloader.php';

define('THEME_BULLET', '&middot;');
define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

if (!defined('MG_LOCALE')) {
    if (file_exists(THEMES.'Magazine/locale/'.LANGUAGE.'.php')) {
        define('MG_LOCALE', THEMES.'Magazine/locale/'.LANGUAGE.'.php');
    } else {
        define('MG_LOCALE', THEMES.'Magazine/locale/English.php');
    }
}

function render_page() {
    new Magazine\Main();
}

function opentable($title = FALSE, $class = '') {
    echo '<div class="opentable">';
    echo $title ? '<div class="title">'.$title.'</div>' : '';
    echo '<div class="'.$class.'">';
}

function closetable() {
    echo '</div>';
    echo '</div>';
}

function openside($title = FALSE, $class = '') {
    echo '<div class="openside '.$class.'">';
    echo $title ? '<div class="title">'.$title.'</div>' : '';
}

function closeside() {
    echo '</div>';
}

function display_loginform($info) {
    Magazine\Templates\Auth::loginForm($info);
}

function display_register_form($info) {
    Magazine\Templates\Auth::registerForm($info);
}

function display_lostpassword($content) {
    Magazine\Templates\Auth::lostpassword($content);
}

function display_gateway($info) {
    Magazine\Templates\Auth::fusionGateway($info);
}

function render_main_blog($info) {
    Magazine\Templates\Blog::renderMainBlog($info);
}

function display_home($info) {
    Magazine\Templates\Home::displayHome($info);
}

function display_main_news($info) {
    Magazine\Templates\News::displayMainNews($info);
}

function render_news_item($info) {
    Magazine\Templates\News::renderNewsItem($info);
}
