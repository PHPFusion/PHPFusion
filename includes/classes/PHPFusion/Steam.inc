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

    private static $boiler_path = NULL; // Default boiler
    private static $boiler_files = [];
    private $boilers = 'bootstrap3';

    /**
     * Steam constructor.
     *
     * @param string $boilers
     */
    public function __construct() {
        $boilerplate = fusion_get_settings('boilerplate');
        $this->boilers = $boilerplate;
        self::$boiler_path = THEMES."boilerplates".DIRECTORY_SEPARATOR.$boilerplate.DIRECTORY_SEPARATOR;
    }

    public function setBoilerPlate($boilerplate) {
        $this->boilers = $boilerplate;
        self::$boiler_path = THEMES."boilerplates".DIRECTORY_SEPARATOR.$boilerplate.DIRECTORY_SEPARATOR;
    }

    /**
     * @return null|string
     */
    public function get_BoilerPath() {
        return self::$boiler_path;
    }

    /**
     * Override boilers
     *
     * @param $boilers
     */
    public function set_boiler($boilers) {
        $this->boilers = $boilers;
    }

    /**
     * Run the boiler
     */
    public function run() {
        require_once self::$boiler_path."package.php";

        $this->setBoilerFiles();
    }

    /**
     *
     */
    private function setBoilerFiles() {
        if (empty(self::$boiler_files)) {
            $package_files = fusion_filter_hook('fusion_boiler_paths');
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
     * @throws \ReflectionException
     */
    public function load($command) {
        $command_name = strtolower($command);
        $command_name = ucfirst($command_name);
        $file = __DIR__.DIRECTORY_SEPARATOR.'Steam'.DIRECTORY_SEPARATOR.$command_name.'.steam.inc';
        require_once $file;
        $input_class = new \ReflectionClass('PHPFusion\\Steam\\'.$command_name);

        return (object)$input_class->newInstance($this);
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

}
