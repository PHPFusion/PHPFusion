<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: acp_theme.php
| Author: RobiNN
| Version: 1.5.2
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

if (!defined('ALTE_LOCALE')) {
    if (file_exists(THEMES.'admin_themes/AdminLTE/locale/'.LANGUAGE.'.php')) {
        define('ALTE_LOCALE', THEMES.'admin_themes/AdminLTE/locale/'.LANGUAGE.'.php');
    } else {
        define('ALTE_LOCALE', THEMES.'admin_themes/AdminLTE/locale/English.php');
    }
}

const ADMINLTE = THEMES.'admin_themes/AdminLTE/';
require_once ADMINLTE.'acp_autoloader.php';

const BOOTSTRAP = TRUE;
const FONTAWESOME = TRUE;

if (!check_admin_pass('')) {
    define('THEME_BODY', '<body class="hold-transition lockscreen">');
} else {
    define('THEME_BODY', '<body class="hold-transition skin-blue sidebar-mini">');
}

fusion_load_script(INCLUDES . "jquery/jquery.fusion-objects.js");

function render_admin_panel() {
    new AdminLTE\AdminPanel();
}

function render_admin_login() {
    new AdminLTE\Login();
}

function render_admin_dashboard() {
    new AdminLTE\Dashboard();
}

function openside($title = FALSE, $class = NULL) {
    $html = '<div class="box box-widget '.$class.'">';
    $html .= $title ? '<div class="box-header with-border">'.$title.'</div>' : '';
    $html .= '<div class="box-body">';

    echo $html;
}

function closeside($footer = FALSE) {
    $html = '</div>';
    $html .= $footer ? '<div class="box-footer">'.$footer.'</div>' : '';
    $html .= '</div>';

    echo $html;
}

function opentable($title = NULL, $class = NULL, $bg = TRUE) {
    AdminLTE\AdminPanel::openTable($title, $class, $bg);
}

function closetable($bg = TRUE) {
    AdminLTE\AdminPanel::closeTable($bg);
}

// Fusion OBjects UI Kits (Put here temporarily before moving this to a dedicated file)

/**
 * Open offcanvas dialog
 * @param [type] $id - The unique identifier for the offcanvas dialog
 * @param [type] $title - The title of the offcanvas dialog
 * @return void
 */
function opencanvas($id, $title = "")
{
    // Open the offcanvas structure with dynamic ID
    echo "<div class='offcanvas' id='$id'>";
    echo "<div class='offcanvas-content'>";

    // Title of the offcanvas (if provided)
    echo "<div class='offcanvas-header'>";
    if ($title) {
        echo "<h3>$title</h3>";
    }
    // Close button with data-pf-toggle attribute
    echo "<button class='close-btn' data-pf-toggle='$id'><i class='fa fa-times'></i></button>";
    echo "</div>";

    // Body of the offcanvas
    echo "<div class='offcanvas-body'>";
}

/**
 * Close offcanvas dialog
 * @param [type] $id - The unique identifier for the offcanvas dialog
 * @return void
 */
function closecanvas($id)
{
    // Close the offcanvas body and content
    echo "</div></div></div>";

    // Add the overlay that corresponds to the offcanvas ID
    echo "<div class='offcanvas-overlay' id='overlay-$id'></div>";
}

/**
 * Open swapbox
 * 
 * Usage:
 * ----
 * $id = 'swapbox';
 * <h4><a href="#" data-pf-toggle="swap" data-toggle-id="$id"><i class="fa fa-plus"></i> Swap</a></h4>                                    
 *  openswap(id: $id);
 *  echo 'Swap content is shown here';
 *  closeswap(id: $id);
 *
 * @param [type] $id - The unique identifier for the swap box 
 * @param [type] $title - The title link of the swap box 
 * @return void
 */
function openswap($id, $title) {
    echo "<div id='$id' class='swapbox'>";
    echo "<h4 class='swap-title display-block'><a href='#' data-pf-toggle='swap' data-toggle-id='$id' class='display-inline-block'>$title</a><h4>";
    echo "<div class='swap-box clearfix' style='display:none;'>";
}
/**
 * Close swapbox
 * @param [type] $id - The unique identifier for the swap box 
 * @return void
 */
function closeswap($id)
{
    echo "<a href='#' class='hide-swap bold pull-right text-smaller' data-pf-toggle='swap' data-toggle-id='$id'>Close</a></div>";
    echo "</div>";
}

add_handler(function ($output = '') {
    $color = !check_admin_pass('') ? 'd2d6de' : '3c8dbc';

    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});
