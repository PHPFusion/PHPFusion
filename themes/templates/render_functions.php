<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: render_functions.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\BreadCrumbs;

defined('IN_FUSION') || exit;

/**
 * Load a HTML template
 *
 * @param $source_file
 *
 * @return string
 */
function fusion_get_template($source_file) {
    ob_start();
    include $source_file;

    return ob_get_clean();
}

/**
 * Load any function
 *
 * @param $function
 *
 * @return mixed|string
 */
function fusion_get_function($function) {
    $function_args = func_get_args();
    if (count($function_args) > 1) {
        unset($function_args[0]);
    }
    // Attempt to check if this function prints anything
    ob_start();
    $func = call_user_func_array($function, $function_args);
    $content = ob_get_clean();
    // If it does not print return the function results
    if (empty($content)) {
        return $func;
    }

    return $content;
}

// Render breadcrumbs template
if (!function_exists("render_breadcrumbs")) {
    function render_breadcrumbs($key = 'default') {
        $breadcrumbs = BreadCrumbs::getInstance($key);
        $html = "<ol class='".$breadcrumbs->getCssClasses()."'>\n";
        foreach ($breadcrumbs->toArray() as $crumb) {
            $html .= "<li class='".$crumb['class']."'>";
            $html .= ($crumb['link']) ? "<a title='".$crumb['title']."' href='".$crumb['link']."'>".$crumb['title']."</a>" : $crumb['title'];
            $html .= "</li>\n";
        }
        $html .= "</ol>\n";

        return $html;
    }
}

if (!function_exists('render_favicons')) {
    function render_favicons($folder = '') {
        $folder = ($folder == '' ? IMAGES.'favicons/' : $folder);
        $html = '';
        // Generator - https://realfavicongenerator.net/
        if (is_dir($folder)) {
            $html .= '<link rel="apple-touch-icon" sizes="180x180" href="'.$folder.'apple-touch-icon.png">';
            $html .= '<link rel="icon" type="image/png" sizes="32x32" href="'.$folder.'favicon-32x32.png">';
            $html .= '<link rel="icon" type="image/png" sizes="16x16" href="'.$folder.'favicon-16x16.png">';
            $html .= '<link rel="manifest" href="'.$folder.'manifest.json">';
            $html .= '<link rel="mask-icon" href="'.$folder.'safari-pinned-tab.svg" color="#262626">';
            $html .= '<link rel="shortcut icon" href="'.$folder.'favicon.ico">';
            $html .= '<meta name="msapplication-TileColor" content="#262626">';
            $html .= '<meta name="msapplication-config" content="'.$folder.'browserconfig.xml">';
            $html .= '<meta name="theme-color" content="#ffffff">';
        }

        return $html;
    }
}

if (!function_exists('render_user_tags')) {

    /**
     * The callback function for fusion_parse_user()
     *
     * @param string $m The message
     * @param string $tooltip The tooltip string
     *
     * @return string
     */
    function render_user_tags($m, $tooltip) {
        $locale = fusion_get_locale();
        add_to_jquery("$('[data-toggle=\"user-tooltip\"]').popover();");
        $user = preg_replace('/[^A-Za-z0-9\-]/', '', $m[0]);
        $user = str_replace('@', '', $user);
        $result = dbquery("SELECT user_id, user_name, user_level, user_status, user_avatar
                FROM ".DB_USERS."
                WHERE (user_name=:user_00 OR user_name=:user_01 OR user_name=:user_02 OR user_name=:user_03) AND user_status='0'
                LIMIT 1
            ", [
            ':user_00' => $user,
            ':user_01' => ucwords($user),
            ':user_02' => strtoupper($user),
            ':user_03' => strtolower($user)
        ]);
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $avatar = !empty($data['user_avatar']) ? "<div class='pull-left m-r-10'>".display_avatar($data, '50px', '', FALSE, '')."</div>" : '';
            $title = "<div class='user-tooltip'>".$avatar."<div class='clearfix'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/><span class='user_level'>".getuserlevel($data['user_level'])."</span></div>";
            $content = $tooltip."<a class='btn btn-block btn-primary' href='".BASEDIR."messages.php?msg_send=".$data['user_id']."'>".$locale['send_message']."</a>";
            $html = '<a class="strong pointer" tabindex="0" role="button" data-html="true" data-trigger="focus" data-placement="top" data-toggle="user-tooltip" title="'.$title.'" data-content="'.$content.'">';
            $html .= "<span class='user-label'>".$m[0]."</span>";
            $html .= "</a>\n";
            return $html;
        }

        return $m[0];
    }
}
