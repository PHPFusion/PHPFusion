<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: hooks_include.php
| Author: Frederick MC Chan (Deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Add a hook
 *
 * @param string $name          The name of the hook, this is your identifier
 * @param string $function      The callback function to run when the filter runs
 * @param int    $que           Optional, values 1-10, where 1 runs first and 10 runs last
 * @param array  $default_args  Optional, the default state of parameter during adding hook
 * @param int    $accepted_args Optional, the limitation of the hook parameters the hook can accept.
 *
 * @return bool
 */
function fusion_add_hook($name, $function, $que = 10, $default_args = [], $accepted_args = 1) {
    // once you need to add hook, we'll poll the instance.
    return \PHPFusion\Hooks::get_instances($name)->add_hook($name, $function, $que, $default_args, $accepted_args);
}

/**
 * Checks if there is a hook by the $name and $function specified registered into the hook instance
 *
 * @param string $name     The name of the hook, this is your identifier
 * @param string $function The callback function to run when the filter runs
 *
 * @return bool
 */
function fusion_check_hook($name, $function) {
    $hook = \PHPFusion\Hooks::get_instances($name)->get_hook($name, $function);
    if (!empty($hook)) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Remove hook
 *
 * @param string $name     The name of the hook, this is your identifier
 * @param string $function The callback function to run when the filter runs
 * @param int    $que
 *
 * @return bool
 */
function fusion_remove_hook($name, $function, $que = 10) {
    return PHPFusion\Hooks::get_instances($name)->remove_hook($name, $function, $que);
}

/**
 * Run the hooks without any output
 *
 * @param $name
 *
 * @return mixed
 */
function fusion_apply_hook($name) {

    $function_args = func_get_args();

    return call_user_func_array([
        \PHPFusion\Hooks::get_instances($name),
        'apply_hook'
    ],
        $function_args);

    //return \PHPFusion\Hooks::get_instances($name)->apply_hook($name, isset($function_args[1]) ? $function_args[1] : NULL);
}

/**
 * This one will return output from running the hooks.
 *
 * @param $name
 *
 * @return mixed
 */
function fusion_filter_hook($name) {
    $function_args = func_get_args();
    // Flatten each function args.
    /*print_P($function_args);
    // Each function arguments can only have a set of array values for proper callback
    if (count($function_args) > 1) {
        for($i = 1; $i < count($function_args); $i++) {
            if (is_array($function_args[$i])) {
                print_P($function_args[$i]);
                //$function_args[$i] = flatten_array($function_args[$i]);
                print_p($function_args[$i]);
            }
        }
    }*/

    return call_user_func_array([
        \PHPFusion\Hooks::get_instances($name),
        'filter_hook'
    ],
        $function_args);

    //return \PHPFusion\Hooks::get_instances($name)->filter_hook($name, isset($function_args[1]) ? $function_args[1] : NULL);
}
