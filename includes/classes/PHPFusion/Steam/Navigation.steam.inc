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
        require_once $steam->getBoilerPath().'navbar.php';
    }

    public function render($info) {
        $nav = new \Navbar();
        return $nav->showSubLinks($info);
    }

    public function search($info) {
        $nav = new \Navbar();
        return $nav->getSearch($info);
    }

}

