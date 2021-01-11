<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: twig_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

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
        "grid_row"            => new TwigFunction("grid_row", function () {
            return call_user_func_array('grid_row', func_get_args());
        }),
        "grid_col"            => new TwigFunction("grid_col", function () {
            return call_user_func_array('grid_col', func_get_args());
        }),
        'print_p'             => new TwigFunction('print_p', function () {
            return call_user_func_array('print_p', func_get_args());
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
        'profile_link'        => new TwigFunction('profile_link', function () {
            return call_user_func_array('profile_link', func_get_args());
        }),
        'format_word'         => new TwigFunction('format_word', function () {
            return call_user_func_array('format_word', func_get_args());
        }),
        'countdown'           => new TwigFunction('countdown', function () {
            return call_user_func_array('countdown', func_get_args());
        }),
        'timer'               => new TwigFunction('timer', function () {
            return call_user_func_array('timer', func_get_args());
        }),
        'showdate'            => new TwigFunction('showdate', function () {
            return call_user_func_array('showdate', func_get_args());
        }),
        'whitespace'          => new TwigFunction('whitespace', function () {
            return call_user_func_array('whitespace', func_get_args());
        }),
        'add_to_jquery'       => new TwigFunction('add_to_jquery', function () {
            call_user_func_array('add_to_jquery', func_get_args());
        }),
        'add_to_footer'       => new TwigFunction('add_to_footer', function () {
            call_user_func_array('add_to_footer', func_get_args());
        }),
        'add_to_css'          => new TwigFunction('add_to_css', function () {
            call_user_func_array('add_to_css', func_get_args());
        }),
        'showcopyright'       => new TwigFunction('showcopyright', function () {
            return call_user_func_array('showcopyright', func_get_args());
        }),
        'get_image'           => new TwigFunction('get_image', function () {
            return strip_tags(call_user_func_array('get_image', func_get_args()));
        }),
        'lorem_ipsum'         => new TwigFunction('lorem_ipsum', function () {
            return strip_tags(call_user_func_array('lorem_ipsum', func_get_args()));
        }),
        "parse_textarea"      => new TwigFunction("parse_textarea", function () {
            return call_user_func_array("parse_textarea", func_get_args());
        })
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
 */
function fusion_render($dir_path = THEMES.'templates/', $file_path = '', array $info = [], $debug = FALSE) {
    $twig = twig_init($dir_path, $debug);
    // adding constants into Twig
    if ($fusion_constants = get_defined_constants()) {
        foreach ($fusion_constants as $key => $value) {
            $info[$key] = $value;
        }
    }
    // add locale into Twig
    $info["locale"] = fusion_get_locale();

    $settings['devmode'] = TRUE;
    if ($settings['devmode']) {
        $output = $twig->render($file_path, $info);
        //$output = trim(preg_replace('/\s\s+/', '', $output));
        return $output;
    }

    try {
        return $twig->render($file_path, $info);
    } catch (LoaderError $e) {
        setError(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
    } catch (RuntimeError $e) {
        setError(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
    } catch (SyntaxError $e) {
        setError(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    return NULL;
}

