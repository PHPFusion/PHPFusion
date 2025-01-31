<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: hooks_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Hooks;

/**
 * Add a hook
 *
 * @param string $name          The name of the hook, this is your identifier.
 * @param string $function      The callback function to run when the filter runs.
 * @param int    $que           Optional, values 1-10, where 1 runs first and 10 runs last.
 * @param array  $default_args  Optional, the default state of parameter during adding hook.
 * @param int    $accepted_args Optional, the limitation of the hook parameters the hook can accept.
 *
 * @return bool
 */
function fusion_add_hook($name, $function, $que = 10, $default_args = [], $accepted_args = 1) {
    // once you need to add hook, we'll poll the instance.
    return Hooks::get_instance($name)->add_hook($name, $function, $que, $default_args, $accepted_args);
}

/**
 * Checks if there is a hook by the $name and $function specified registered into the hook instance.
 *
 * @param string $name     The name of the hook, this is your identifier.
 * @param string $function It checks if function in that hook exists.
 *
 * @return bool
 */
function fusion_check_hook($name, $function) {
    $hook = Hooks::get_instance($name)->get_hook($name, $function);
    if (!empty($hook)) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Remove hook
 *
 * @param string $name     The name of the hook, this is your identifier.
 * @param string $function The callback function to run when the filter runs.
 * @param int    $que
 *
 * @return bool
 */
function fusion_remove_hook($name, $function = '', $que = 10) {
    return PHPFusion\Hooks::get_instance($name)->remove_hook($name, $function, $que);
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
        Hooks::get_instance($name),
        'apply_hook'
    ],
        $function_args);

    //return \PHPFusion\Hooks::get_instances($name)->apply_hook($name, isset($function_args[1]) ? $function_args[1] : NULL);
}

/**
 * This one will return output from running all available hooks added under the same namespace in one go and return array.
 *
 * @param $name
 *
 * @return array
 */
function fusion_filter_hook($name) {
    return call_user_func_array([Hooks::get_instance($name), 'filter_hook'], func_get_args());
}

/**
 * If hook add once, and only intended to be used once, use this function.
 *
 * @param $name
 *
 * @return mixed
 */
function fusion_filter_current_hook($name) {
    return call_user_func_array([Hooks::get_instance($name), 'filter_hook_once'], func_get_args());
}

/**
 * If hook add once, and intended to be used multiple times, use this function.
 *
 * @param $name
 *
 * @return mixed
 */
function fusion_repeat_current_hook($name) {
    return call_user_func_array([Hooks::get_instance($name), 'repeat_hook_once'], func_get_args());
}
