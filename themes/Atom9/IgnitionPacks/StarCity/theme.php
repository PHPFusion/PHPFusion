<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/IgnitionPacks/StarCity/theme.php
| Author: Frederick MC Chan (Chan)
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

function render_downloads($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\Downloads::render_downloads($info);
}

function display_home($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\Home::HomePanel($info);
}

function display_loginform($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\Login::LoginForm($info);
}

function display_register_form($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\Login::RegisterForm($info);
}

function display_main_news($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\News::display_main_news($info);
}

function render_news_item($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\News::render_news_item($info);
}

function display_inbox($info) {
    Atom9Theme\IgnitionPacks\StarCity\Templates\Messages::display_inbox($info);
}
