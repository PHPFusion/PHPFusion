<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Layout.steam.inc
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
namespace PHPFusion\Steam;
use PHPFusion\Steam;

/**
 * Class Blocks
 * Render a cross framework HTML blocks.
 */
class Layout {

    private $steam; // pass the steam here.

    /**
     * Layout constructor.
     */
    public function __construct() {
        $this->steam = Steam::getInstance();
        require_once $this->steam->getBoilerPath().DIRECTORY_SEPARATOR."grid.php";
    }

    public function getRowClass() {
        return (string) \Grid::getRowClass();
    }

    public function getColumnClass($percent = 100, $offset = FALSE) {
        return (string) \Grid::getColumnClass($percent, $offset);
    }

    public function getContainerClass() {
        return \Grid::getContainerClass();
    }

    public function deprecated_grid(array $options) {
        $default = array(
            array(
                'content' => '',
                'class' => '', // supports % or px
                'xs' => 12,
                'sm' => 12,
                'md' => 12,
                'lg' => 12,
            )
        );
        $options += $default;
        $tpl = \PHPFusion\Template::getInstance('grid');
        $tpl->set_template(THEMES.'boilerplates/'.$this->steam->get_boiler().'/html/grid.html');
        foreach($options as $grid) {
            if (empty($grid['class'])) {
                switch($this->steam->get_boiler()) {
                    case 'bootstrap3':
                        $grid['class'] = $this->get_bootstrap_class($grid);
                        break;
                    default:
                }
            }
            $tpl->set_block('column', $grid);
        }

        return (string)$this->steam->render($tpl);

    }

    public function deprecated_get_bootstrap_class($grid) {
        // use implode and run foreach.
        $array = array('xs', 'sm', 'md', 'lg');
        $class = array();
        foreach($array as $grid_size) {
            if (!empty($grid[ $grid_size ])) {
                $class[] = 'col-'.$grid_size.'-'.$grid[ $grid_size];
            }
        }
        return implode(' ', $class);
    }

}
