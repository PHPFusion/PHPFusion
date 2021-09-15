<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Hooks.php
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
namespace PHPFusion;

/**
 * Class Hooks
 * Core class for storing, removing and run hook functions.
 *
 * @package PHPFusion
 */
final class Hooks {

    private $hooks = [];

    private static $instances = NULL;

    private $output = [];
    private $filter_name;
    /**
     * @var array
     */
    private $hook_args;

    /**
     * Get an instance by key
     *
     * @param string $key
     *
     * @return static
     */
    public static function get_instance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new Hooks;
        }

        return self::$instances[$key];
    }

    /**
     * Register a hook into the $instance
     *
     * @param string $filter_name   The name of the hook, this is your identifier.
     * @param string $function      The callback function to run when the filter runs.
     * @param int    $que           Optional, values 1-10, where 1 runs first and 10 runs last.
     * @param array  $default_args  Optional, the default state of parameter during adding hook.
     * @param int    $accepted_args Optional, the limitation of the hook parameters the hook can accept.
     *
     * @return bool
     */
    public function add_hook($filter_name, $function, $que, $default_args, $accepted_args) {

        $hooks = [

            'function'      => $function,
            'default_args'  => $default_args,
            'accepted_args' => $accepted_args,
            'que'           => $que,
        ];

        $this->hooks[$que][$filter_name][] = $hooks;
        if (count($this->hooks) > 1) {
            ksort($this->hooks, SORT_NUMERIC);
        }

        return TRUE;
    }

    /**
     * Remove all hooks from the $instance
     *
     * @param bool $que
     */
    public function remove_all_hook($que = FALSE) {

        if ($que === FALSE) {
            $this->hooks = [];

        } else if (isset($this->hooks[$que])) {
            unset($this->hooks[$que]);
        }

    }

    /**
     * Run the hooks by $filter_name and $args parameters
     * There will be no output. If you need an output, use filter hook.
     *
     * @param $filter_name
     *
     * @throws \Exception
     */
    public function apply_hook($filter_name) {

        $this->hook_args = func_get_args();

        $this->filter_name = $filter_name;

        $current_hook = $this->get_hook($filter_name);

        if (!empty($current_hook)) {

            foreach ($current_hook as $hook) {

                // prevent the current hook from being called twice, executed or not, else crash
                $this->remove_hook($filter_name, $hook['function'], $hook['que']);

                if (function_exists($hook['function'])) {

                    $args = (!empty($hook['default_args']) ? $hook['default_args'] : []);

                    $this->hook_args = $this->getFunctionArgs($args);

                    if ($hook['accepted_args'] && !empty($this->hook_args)) {
                        if ($hook['accepted_args'] !== count($this->hook_args)) {
                            throw new \Exception("Too many arguments during executing the $filter_name hook");
                        }
                    }

                    $output = call_user_func_array($hook['function'], $this->hook_args);

                    if (!empty($output)) {

                        $this->output[$filter_name][] = $output;

                    }
                }
            }

            if (!empty($this->get_hook($filter_name)))
                $this->apply_hook($filter_name, $this->hook_args);

        }
    }

    /**
     * Returns the hook by $filter_name and $function
     *
     * @param string $filter_name The name of the hook, this is your identifier.
     * @param string $function    The callback function to run when the filter runs.
     *
     * @return array
     */
    public function get_hook($filter_name, $function = '') {
        if (!empty($this->hooks)) {
            array_filter($this->hooks);
            foreach ($this->hooks as $hooks) {
                if (!empty($hooks)) {
                    if (isset($hooks[$filter_name])) {
                        if ($function == 'invalid_notices') {
                            return [];
                        }
                        if (!empty($function)) {
                            foreach ($hooks[$filter_name] as $hook) {
                                if ($hook['function'] === $function) {
                                    return $hook;
                                }
                            }
                            return [];
                        } else {
                            return (array)$hooks[$filter_name];
                        }
                    }
                }
            }
        }

        return [];

    }

    /**
     * Remove a specified hook from the $instance
     *
     * @param string $filter_name The name of the hook, this is your identifier.
     * @param string $function    The callback function to run when the filter runs.
     * @param int    $que
     *
     * @return bool
     */
    public function remove_hook($filter_name, $function, $que) {

        if ($function) {
            if (isset($this->hooks[$que][$filter_name])) {
                foreach ($this->hooks[$que][$filter_name] as $key => $hooks) {
                    if ($hooks['function'] == $function) {
                        unset($this->hooks[$que][$filter_name][$key]);
                        if (empty($this->hooks[$que][$filter_name]))
                            unset($this->hooks[$que][$filter_name]);
                        return TRUE;
                    }
                }
            }
        }

        unset($this->hooks[$que][$filter_name]);

        return TRUE;
    }

    /**
     * @param $default_args
     *
     * @return array
     */
    private function getFunctionArgs($default_args) {

        if (!empty($this->hook_args) && is_array($this->hook_args)) {

            foreach ($this->hook_args as $key => $value) {
                if ($value == $this->filter_name) {
                    unset($this->hook_args[$key]);
                }
            }

        }

        if (!empty($this->hook_args)) {
            return $this->hook_args;
        }

        return $default_args;
    }

    /**
     * Run the hook filter, can be used multiple times within a loop to get the parse.
     *
     * @param $filter_name
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function apply_hook_once($filter_name) {
        $function_args = func_get_args();
        $current_hook = $this->get_hook($filter_name);
        if (!empty($current_hook)) {
            foreach ($current_hook as $hook) {
                if (function_exists($hook['function'])) {
                    $args = (!empty($hook['default_args']) ? $hook['default_args'] : []);
                    if (count($function_args) > 1) {
                        unset($function_args[0]);
                        $args = $function_args;
                    }
                    if ($hook['accepted_args']) {

                        if ($hook['accepted_args'] < (count($function_args) - 1)) {
                            throw new \Exception("Too many arguments during executing the $filter_name hook");
                        }
                    }
                    $output = call_user_func_array($hook['function'], $args);

                    // remove the hook
                    $this->remove_hook($filter_name, $hook['function'], $hook['que']);

                    if (!empty($output)) {
                        return $output;
                    }
                }
            }
        }
        return '';
    }

    /**
     * Run the hook filter, can be used multiple times within a loop to get the parse.
     *
     * @param $filter_name
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function repeat_current_hook($filter_name) {
        $function_args = func_get_args();
        $current_hook = $this->get_hook($filter_name);
        if (!empty($current_hook)) {
            foreach ($current_hook as $hook) {
                if (function_exists($hook['function'])) {
                    $args = (!empty($hook['default_args']) ? $hook['default_args'] : []);
                    if (count($function_args) > 1) {
                        unset($function_args[0]);
                        $args = $function_args;
                    }
                    if ($hook['accepted_args']) {
                        if ($hook['accepted_args'] < (count($function_args) - 1)) {
                            throw new \Exception("Too many arguments during executing the $filter_name hook");
                        }
                    }
                    $output = call_user_func_array($hook['function'], $args);
                    // remove the hook
                    //$this->remove_hook( $filter_name, $hook['function'], $hook['que'] );
                    if (!empty($output)) {
                        return $output;
                    }
                }
            }
        }
        return '';
    }

    /**
     * Run the hooks by $filter_name and $args parameters
     * This filter must only run once in application.
     *
     * @param $filter_name
     *
     * @return array
     */
    public function filter_hook($filter_name) {

        $function_args = func_get_args();
        call_user_func_array([$this, 'apply_hook'], $function_args);

        if (!empty($this->output[$filter_name]) && is_array($this->output[$filter_name])) {
            return $this->output[$filter_name];
        }

        return [];
    }

    /**
     * @param $filter_name
     *
     * @return string
     */
    public function filter_hook_once($filter_name) {

        $output = call_user_func_array([$this, 'apply_hook_once'], func_get_args());

        return (string)$output;
    }

    /**
     * @param $filter_name
     *
     * @return string
     */
    public function repeat_hook_once($filter_name) {

        $output = call_user_func_array([$this, 'repeat_current_hook'], func_get_args());

        return (string)$output;
    }

    /**
     * Apply all hooks
     */
    public function apply_all_hook() {

        if (!empty($this->hooks)) {
            foreach ($this->hooks as $que => $funcs_) {
                if (!empty($funcs_['function']) && function_exists($funcs_['function'])) {

                    call_user_func_array($funcs_['function'], $funcs_['accepted_args']);
                    array_shift($this->hooks[$que]);
                }
            }
        }
    }

}
