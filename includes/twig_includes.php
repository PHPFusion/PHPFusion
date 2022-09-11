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
use Twig\Markup;
use Twig\TwigFunction;

/**
 * Load Twig Template Engine
 *
 * @param array $config
 *
 * @return Environment
 */
function twig_init(array $config = []): Environment {

    $config += [
        'path'         => THEME.'twig',
        'debug'        => FALSE,
        'config_debug' => FALSE,
        'namespace'    => [] // documentation: https://symfony.com/doc/4.1/templating/namespaced_paths.html
    ];

    $loader = new FilesystemLoader($config['path']);

    if ($config['config_debug']) {
        print_p($config);
    }

    if ($config['namespace']) {
        foreach ($config['namespace'] as $path => $namespace) {
            try {
                $loader->addPath($path, $namespace);
            } catch (Exception $e) {
                set_error(E_COMPILE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }
    }
    $twig = new Environment($loader, [
        'cache' => BASEDIR.'cache/twig',
        'debug' => $config['debug']
    ]);


    if ($config['debug'] == TRUE) {
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
function fusion_render(string $dir_path = THEMES.'templates/', string $file_path = '', array $info = [], bool $debug = FALSE): string {

    $config = [
        'path'  => $dir_path,
        'debug' => $debug,
    ];

    if ($__config = twig_cache_config()) {
        $config += $__config;
    }

    $twig = twig_init($config);

    if ($__fn = twig_cache_functions()) {

        foreach ($__fn as $function_name => $twig_function) {

            if (function_exists($function_name)) {

                /**
                 * @uses schema
                 */
                $twig->addFunction($twig_function);
            }
        }
    }

    // adding constants into Twig
    if ($fusion_constants = get_defined_constants()) {
        foreach ($fusion_constants as $key => $value) {
            $info[$key] = $value;
        }
    }
    // add locale into Twig
    $info["locale"] = fusion_get_locale();

    try {

        return $twig->render($file_path, $info);

    } catch (LoaderError | SyntaxError | RuntimeError $e) {

        if (is_callable('set_error')) {
            set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
        } else {
            return $e->getMessage();
        }
    }

    return "Rendering has encountered an error.";
}

/**
 * Cached Functions Support
 *
 * @return array|false|mixed
 */
function twig_cache_functions() {

    static $__fn;

    $function_registers = [
        'openside'                     => ['type' => 'string'],
        'closeside'                    => ['type' => 'string'],
        'opentable'                    => ['type' => 'string'],
        'closetable'                   => ['type' => 'string'],
        'display_avatar'               => ['type' => 'string'],
        'profile_link'                 => ['type' => 'string'],
        'opentab'                      => ['type' => 'string'],
        'closetab'                     => ['type' => 'string'],
        'tab_active'                   => ['type' => 'string'],
        'opentabbody'                  => ['type' => 'string'],
        'closetabbody'                 => ['type' => 'string'],
        'format_word'                  => ['type' => 'string'],
        'format_num'                   => ['type' => 'string'],
        'countdown'                    => ['type' => 'string'],
        'showdate'                     => ['type' => 'string'],
        'showdatetime'                 => ['type' => 'string'],
        'timer'                        => ['type' => 'string'],
        'showcopyright'                => ['type' => 'string'],
        'showFooterErrors'             => ['type' => 'string'],
        'get_image'                    => ['type' => 'string'],
        'get_icon'                     => ['type' => 'string'],
        'lorem_ipsum'                  => ['type' => 'string'],
        'parse_textarea'               => ['type' => 'string'],
        'schema'                       => ['type' => 'string'],
        'whitespace'                   => ['type' => 'whitespace'],
        'print_p'                      => ['type' => 'void'],
        'fusion_get_userdata'          => ['type' => 'array'],
        'fusion_get_settings'          => ['type' => 'array'],
        'fusion_get_locale'            => ['type' => 'array'],
        'add_to_jquery'                => ['type' => 'outputhandler'],
        'add_to_footer'                => ['type' => 'outputhandler'],
        'add_to_css'                   => ['type' => 'outputhandler'],
        'get_settings'                 => ['type' => 'string'],
        'getuserlevel'                 => ['type' => 'string'],
        // languages
        'fusion_get_enabled_languages' => ['type' => 'array'],
        'fusion_get_language_switch'   => ['type' => 'array'],
        'translate_lang_names'         => ['type' => 'string']
    ];

    if (empty($__fn)) {

        $__fn = array_combine(

            array_keys($function_registers),

            array_map(function ($key, $param) {

                $param['key'] = $key;

                return new TwigFunction($key, function () use ($param) {

                    $_fn = call_user_func_array($param['key'], func_get_args());

                    if (isset($param['type'])) {

                        if ($param['type'] == 'string') {

                            $_fn = new Markup($_fn, 'UTF-8');
                        }
                    }

                    return $_fn;
                });

            },
                array_keys($function_registers),
                $function_registers)
        );

        // Add hook implementations later.
        // if ( $add_filter = fusion_filter_hook('fusion_twig_functions') ) {
        // }
    }

    return $__fn;
}

/**
 * Cached configurations
 *
 * @return array|mixed
 */
function twig_cache_config() {

    static $__fn;

    if (empty($__fn)) {
        if ($hook_fn = fusion_filter_hook('fusion_twig_config')) {
            $fs_result = [];
            foreach ($hook_fn as $hooks) {
                $fs_result = array_merge_recursive($hooks, $fs_result);
            }
            $__fn = $fs_result;
        }
    }

    return $__fn;
}
