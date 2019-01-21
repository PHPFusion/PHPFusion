<?php
/**
 * @param     $name             The name of the hook, this is your identifier
 * @param     $function         The callback function to run when the filter runs
 * @param int $que              Optional, values 1-10, where 1 runs first and 10 runs last
 * @param int $accepted_args    Optional, the number of arguments the function will accept, Default is 1.
 *
 * @return bool
 */
function fusion_add_hook($name, $function, $que = 10, $accepted_args = 1) {
    // once you need to add hook, we'll poll the instance.
    return \PHPFusion\Hooks::get_instances($name)->add_hook($name, $function, $que, $accepted_args);
}

/**
 * Checks if there is a hook by the $name and $function specified registered into the hook instance
 *
 * @param $name                 The name of the hook, this is your identifier
 * @param $function             The callback function to run when the filter runs
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
 * @param     $name             The name of the hook, this is your identifier
 * @param     $function          The callback function to run when the filter runs
 * @param int $que
 *
 * @return bool
 */
function fusion_remove_hook($name, $function, $que = 10) {
    return PHPFusion\Hooks::get_instances($name)->remove_hook($name, $function, $que);
}

/**
 * @param        $name
 * @param string $arg
 *
 * @return array
 */
function fusion_apply_hook($name, $arg = '') {
    return \PHPFusion\Hooks::get_instances($name)->apply_hook($name, $arg);
}

/**
 * @param $name
 *
 * @return mixed
 */
function fusion_filter_hook($name) {
    return \PHPFusion\Hooks::get_instances($name)->filter_hook($name);
}