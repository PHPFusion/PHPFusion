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

fusion_load_script( INCLUDES . "jquery/jquery.fusion-objects.js" );

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

// Set all the commonly used icons to cut dependency on plugins
set_image('ellipsis', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" role="img" class="icon fill-current">
            <circle cx="2" cy="8" r="1.5" fill="currentColor"></circle>
            <circle cx="14" cy="8" r="1.5" fill="currentColor"></circle>
            <circle cx="8" cy="8" r="1.5" fill="currentColor"></circle>
            </svg>');
set_image('delete', '<svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="currentColor" d="M11 1.75V3h2.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75ZM4.496 6.675l.66 6.6a.25.25 0 0 0 .249.225h5.19a.25.25 0 0 0 .249-.225l.66-6.6a.75.75 0 0 1 1.492.149l-.66 6.6A1.748 1.748 0 0 1 10.595 15h-5.19a1.75 1.75 0 0 1-1.741-1.575l-.66-6.6a.75.75 0 1 1 1.492-.15ZM6.5 1.75V3h3V1.75a.25.25 0 0 0-.25-.25h-2.5a.25.25 0 0 0-.25.25Z"/></svg>');
set_image('move', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M1.086 12L5.5 7.586L6.914 9l-2 2H11V4.914l-2 2L7.586 5.5L12 1.086L16.414 5.5L15 6.914l-2-2V11h6.086l-2-2L18.5 7.586L22.914 12L18.5 16.414L17.086 15l2-2H13v6.086l2-2l1.414 1.414L12 22.914L7.586 18.5L9 17.086l2 2V13H4.914l2 2L5.5 16.414L1.086 12Z"/></svg>');
set_image('publish', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"><path fill="currentColor" fill-rule="evenodd" d="M256 42.667c14.433 0 28.531 1.433 42.16 4.165L259.62 85.37q-1.805-.038-3.62-.038c-94.256 0-170.666 76.41-170.666 170.667c0 94.256 76.41 170.667 170.666 170.667c94.2-.14 170.526-76.468 170.667-170.666c0-28.37-6.922-55.122-19.169-78.661l31.332-31.33c19.364 32.118 30.504 69.753 30.504 109.99c-.176 117.749-95.586 213.158-213.334 213.334c-117.82 0-213.333-95.512-213.333-213.333C42.667 138.18 138.18 42.667 256 42.667m85.334-8.837l89.751 89.752l-30.17 30.17l-38.249-38.239l.001 140.487c0 45.7-35.925 83.01-81.074 85.229l-89.593.104v-42.666h85.334c22.493 0 40.92-17.406 42.55-39.483L320 256V115.513l-38.248 38.239l-30.17-30.17z"/></svg>');
set_image('unpublish', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="m20.475 23.3l-2.95-2.95q-1.2.8-2.587 1.225T12 22q-2.075 0-3.9-.788t-3.175-2.137q-1.35-1.35-2.137-3.175T2 12q0-1.55.425-2.938T3.65 6.476L.675 3.5L2.1 2.075l19.8 19.8l-1.425 1.425ZM12 20q1.125 0 2.138-.3t1.912-.825L12.175 15L10.6 16.6l-4.25-4.25l1.4-1.4l2.85 2.85l.175-.2l-5.65-5.65q-.525.9-.825 1.913T4 12q0 3.325 2.337 5.663T12 20Zm8.375-2.5L18.9 16.025q.525-.875.813-1.888T20 12q0-3.325-2.337-5.663T12 4q-1.125 0-2.138.288T7.976 5.1L6.5 3.625q1.2-.775 2.588-1.2T12 2q2.075 0 3.9.788t3.175 2.137q1.35 1.35 2.138 3.175T22 12q0 1.525-.425 2.913t-1.2 2.587Zm-5.325-5.35l-1.4-1.4l2.6-2.6l1.4 1.4l-2.6 2.6Zm-1.6-1.6ZM10.6 13.4Z"/></svg>');
set_image('add', '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="currentColor" d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0a6.5 6.5 0 0 0-13 0Zm7.25-3.25v2.5h2.5a.75.75 0 0 1 0 1.5h-2.5v2.5a.75.75 0 0 1-1.5 0v-2.5h-2.5a.75.75 0 0 1 0-1.5h2.5v-2.5a.75.75 0 0 1 1.5 0Z"/></svg>');