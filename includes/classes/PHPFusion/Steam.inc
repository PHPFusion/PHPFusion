<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Steam.inc
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
namespace PHPFusion;

/**
 * Class Steam
 * A class to compose, convert and compose boilerplate components.
 *
 * @package PHPFusion
 */
class Steam {

    private static $instance = NULL;

    private static $default_boilerplate = "bootstrap4";
    private static $boiler_files = [];
    private $boilers = ""; // Default boiler
    private $boiler_path = "";

    /**
     * Steam instance
     *
     * @return static|null
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
            self::$instance->setBoilerPlate(self::$default_boilerplate);
        }

        return self::$instance;
    }

    public function setBoilerPlate($boilerplate) {
        $this->boilers = $boilerplate;
        $this->boiler_path = THEMES."boilerplates".DIRECTORY_SEPARATOR.$boilerplate.DIRECTORY_SEPARATOR;
    }

    /**
     * Run the boiler
     */
    public function run() {
        $package_path = $this->getBoilerPath()."package.php";
        require_once $package_path;
        $this->setBoilerFiles();
    }

    /**
     * @return null|string
     */
    public function getBoilerPath() {
        return $this->boiler_path;
    }

    /**
     *
     */
    private function setBoilerFiles() {
        if (empty(self::$boiler_files)) {
            $package_files = fusion_filter_hook("fusion_boiler_paths");
            if (!empty($package_files[0])) {
                self::$boiler_files = $package_files[0];
            }
        }
    }

    /**
     * Get the current boiler
     *
     * @return string
     */
    public function get_boiler() {
        return $this->boilers;
    }

    /**
     * @param $command
     *
     * @return object
     */
    public function load($command) {
        $command_name = strtolower($command);
        $command_name = ucfirst($command_name);
        require_once(__DIR__.DIRECTORY_SEPARATOR."Steam".DIRECTORY_SEPARATOR.$command_name.".steam.inc");
        try {
            $input_class = new \ReflectionClass("PHPFusion\\Steam\\".$command_name);
            return (object)$input_class->newInstance($this);
        } catch (\ReflectionException $e) {
            set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), "Boilerplate Error");
        }
        return NULL;
    }

    /**
     * Render the output from the template
     *
     * @param Template $tpl
     *
     * @return string
     */

    public function render(Template $tpl) {
        return (string)$tpl->get_output();
    }

    /**
     * @return array
     */
    public function getBoilerFiles() {
        return self::$boiler_files;
    }

    private function __clone() {
    }

    private function __sleep() {
    }

    private function __wakeup() {
    }

}
