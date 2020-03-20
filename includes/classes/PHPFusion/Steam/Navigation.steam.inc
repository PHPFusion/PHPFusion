<?php

namespace PHPFusion\Steam;

use PHPFusion\Steam;

/**
 * Class Blocks
 * Render a cross framework HTML blocks.
 */
class Navigation {

    /**
     * Layout constructor.
     */
    public function __construct() {
        $steam = Steam::getInstance();
        require_once $steam->getBoilerPath().DIRECTORY_SEPARATOR.'navbar.php';
    }

    public function render($info) {
        return \Navbar::showSubLinks($info);
    }

}

