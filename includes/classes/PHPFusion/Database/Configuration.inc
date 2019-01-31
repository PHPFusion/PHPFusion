<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Configuration.inc
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
namespace PHPFusion\Database;

use BadMethodCallException;

/**
 * @method string getHost()
 * @method string getDatabase()
 * @method string getUser()
 * @method string getPassword()
 * @method string getCharset()
 * @method string getDriver()
 */
class Configuration {
    private $configuration = [];

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = []) {
        $this->configuration = $configuration + [
                'host'     => '',
                'database' => '',
                'user'     => '',
                'password' => '',
                'charset'  => 'utf8mb4',
                'driver'   => DatabaseFactory::getDefaultDriver(),
                'debug'    => FALSE
            ];
        $this->configuration['driver'] = strtolower($this->configuration['driver']);
    }

    /**
     * @return bool
     */
    public function isDebug() {
        return (bool)$this->configuration['debug'];
    }

    public function __call($method, $arguments) {
        $method = strtolower($method);
        if (substr($method, 0, 3) !== 'get') {
            throw new BadMethodCallException(sprintf("This method does not exist: '%s' ", $method));
        }
        $index = substr($method, 3);
        if (!isset($this->configuration[$index])) {
            throw new BadMethodCallException(sprintf("This method does not exist: '%s' ", $method));
        }

        return $this->configuration[$index];
    }

    public function toArray() {
        return $this->configuration;
    }
}
