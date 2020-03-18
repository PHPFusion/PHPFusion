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
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

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
     * @param string $m       The message
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
            $title = "<div class='user-tooltip'>".$avatar."<div class='tooltip-header overflow-hide'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/><span class='user-level'>".getuserlevel($data['user_level'])."</span></div>";
            $content = $tooltip."<a class='btn btn-block btn-primary' href='".BASEDIR."messages.php?msg_send=".$data['user_id']."'>".$locale['send_message']."</a>";
            $html = '<a class="strong pointer" tabindex="0" role="button" data-html="true" data-trigger="focus" data-placement="top" data-toggle="user-tooltip" title="'.$title.'" data-content="'.$content.'">';
            $html .= "<span class='user-label'>".$m[0]."</span>";
            $html .= "</a>\n";
            return $html;
        }

        return $m[0];
    }
}

/**
 * Load Twig Template Engine
 *
 * @param string $path
 * @param bool   $debug
 *
 * @return Environment
 */
function twig_init($path = THEME.'twig', $debug = FALSE) {
    $loader = new FilesystemLoader($path);

    $twig = new Environment($loader, [
        'cache' => BASEDIR.'cache/twig',
        'debug' => $debug
    ]);

    if ($debug == TRUE) {
        $twig->addExtension(new DebugExtension());
    }

    // {{ get_function('function_name', TRUE, arg1, arg2) }}
    $get_function = new TwigFunction('get_function', function ($function, $return = FALSE) {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);

        if ($return == TRUE) {
            return call_user_func_array($function, $args);
        } else {
            call_user_func_array($function, $args);
        }

        return NULL;
    });

    $twig->addFunction($get_function);

    // Evolve this further
    $twig_register_functions = [
        'openside'            => new TwigFunction('openside', function () {
            return call_user_func_array('openside', func_get_args());
        }),
        'closeside'           => new TwigFunction('closeside', function () {
            return call_user_func_array('closeside', func_get_args());
        }),
        'opentable'           => new TwigFunction('opentable', function () {
            return call_user_func_array('opentable', func_get_args());
        }),
        'closetable'          => new TwigFunction('closetable', function () {
            return call_user_func_array('closetable', func_get_args());
        }),
        'opensidex'           => new TwigFunction('opensidex', function () {
            return call_user_func_array('opensidex', func_get_args());
        }),
        'closesidex'          => new TwigFunction('closesidex', function () {
            return call_user_func_array('closesidex', func_get_args());
        }),
        'print_p'             => new TwigFunction('print_p', function () {
            return call_user_func_array('closesidex', func_get_args());
        }),
        'fusion_get_userdata' => new TwigFunction('fusion_get_userdata', function () {
            return call_user_func_array('fusion_get_userdata', func_get_args());
        }),
        'fusion_get_settings' => new TwigFunction('fusion_get_settings', function () {
            return call_user_func_array('fusion_get_settings', func_get_args());
        }),
        'fusion_get_locale'   => new TwigFunction('fusion_get_locale', function () {
            return call_user_func_array('fusion_get_locale', func_get_args());
        }),
        'display_avatar'      => new TwigFunction('display_avatar', function () {
            return call_user_func_array('display_avatar', func_get_args());
        }),

    ];

    foreach ($twig_register_functions as $key => $function) {
        if (function_exists($key)) {
            $twig->addFunction($function);
        }
    }

    return $twig;
}

/**
 * Function to render using twig normal output
 *
 * @param string $dir_path
 * @param string $file_path
 * @param array  $info
 * @param bool   $debug
 *
 * @return string
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function fusion_render($dir_path = THEMES.'templates/', $file_path = '', array $info = [], $debug = FALSE) {
    $twig = twig_init($dir_path, $debug);
    // adding constants into Twig
    if ($fusion_constants = get_defined_constants()) {
        foreach ($fusion_constants as $key => $value) {
            $info[$key] = $value;
        }
    }
    return $twig->render($file_path, $info);
}

// Add compatibility mode function
if (!function_exists('opensidex')) {
    /**
     * Template boiler using Bootstrap 3
     *
     * @param $title
     */
    function opensidex($value) {
        echo '<div class="sidex list-group">';
        echo '<div class="title list-group-item"><strong>'.$value.'</strong><span class="pull-right"><span class="caret"></span></span></div>';
        echo '<div class="body list-group-item">';

        if (!defined('sidex_js')) {
            define('sidex_js', TRUE);
            add_to_jquery(/** @lang JavaScript */ "
            $('body').on('click', '.sidex > .title', function(e) {
                let sidexBody = $(this).siblings('.body');
                sidexBody.toggleClass('display-none');
                if (sidexBody.is(':hidden')) {
                    $(this).closest('div').find('.pull-right').addClass('dropup');
                } else {
                    $(this).closest('div').find('.pull-right').removeClass('dropup');
                }
            });
            ");
        }
    }
}

if (!function_exists('closesidex')) {
    function closesidex($value = '') {
        echo '</div>';
        if ($value) {
            echo '<div class="list-group-item">'.$value.'</div>';
        }
        echo '</div>';
    }
}

if (!function_exists('tablebreak')) {
    echo "</div><div class='list-group-item'>";
}

if (!function_exists('openside')) {
    function openside($value = '') {
        echo '<div class="panel panel-default">';
        if ($value) {
            echo '<div class="panel-heading">'.$value.'</div>';
        }
        echo '<div class="panel-body">';
    }
}

if (!function_exists('closeside')) {
    function closeside($value = '') {
        if ($value) {
            echo '<div class="panel-footer">'.$value.'</div>';
        }
        echo '</div>';
    }
}

if (!function_exists('opentable')) {
    function opentable($value = '') {
        echo '<div class="table">';
        if ($value) {
            echo '<h3>'.$value.'</h3>';
        }
    }
}

if (!function_exists('closetable')) {
    function closetable($value = '') {
    }
}

