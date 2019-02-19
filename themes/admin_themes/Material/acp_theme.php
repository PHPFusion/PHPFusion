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
defined('IN_FUSION') || exit;

if (!defined('MDT_LOCALE')) {
    if (file_exists(THEMES.'admin_themes/Material/locale/'.LANGUAGE.'.php')) {
        define('MDT_LOCALE', THEMES.'admin_themes/Material/locale/'.LANGUAGE.'.php');
    } else {
        define('MDT_LOCALE', THEMES.'admin_themes/Material/locale/English.php');
    }
}

define('MDT', THEMES.'admin_themes/Material/');
require_once MDT.'acp_autoloader.php';

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

$toggled = (isset($_COOKIE['sidebar-toggled']) && $_COOKIE['sidebar-toggled'] == 1) ?' sidebar-toggled' : '';
$sm = (isset($_COOKIE['sidebar-sm']) && $_COOKIE['sidebar-sm'] == 1) ? ' sidebar-sm' : '';

if (isset($_COOKIE['sidebar-toggled']) || isset($_COOKIE['sidebar-sm'])) {
    define('THEME_BODY', '<body class="'.$toggled.$sm.'">');
}

function render_admin_panel() {
    new Material\AdminPanel();
}

function render_admin_login() {
    new Material\Login();
}

function render_admin_dashboard() {
    new Material\Dashboard();
}

function openside($title = FALSE, $class = NULL) {
    $html = '<div class="panel panel-default openside '.$class.'">';
    $html .= $title ? '<div class="panel-heading">'.$title.'</div>' : '';
    $html .= '<div class="panel-body">';

    echo $html;
}

function closeside($footer = FALSE) {
    $html = '</div>';
    $html .= $footer ? '<div class="panel-footer">'.$footer.'</div>' : '';
    $html .= '</div>';

    echo $html;
}

function opentable($title, $class = NULL) {
    $html = '<div class="panel opentable '.$class.'">';
    $html .= $title ? '<header><h3>'.$title.'</h3></header>' : '';
    $html .= '<div class="panel-body">';

    echo $html;
}

function closetable() {
    $html = '</div>';
    $html .= '</div>';

    echo $html;
}

\PHPFusion\OutputHandler::addHandler(function ($output = '') {
    $color = !check_admin_pass('') ? '2c3e50' : '243447';

    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});
