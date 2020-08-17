<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Blocks.steam.inc
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
class Blocks {

    private $steam; // pass the steam here.

    /**
     * Blocks constructor.
     */
    public function __construct() {
        $this->steam = Steam::getInstance();
    }

    public function box(array $options) {

        $default = [
            'class'   => '',
            'title'       => '',
            'meta'        => '',
            'image_src'   => '',
            'description' => '',
            'footer'      => ''
        ];

        $options += $default;

        $path = $this->steam->getBoilerPath()."html".DIRECTORY_SEPARATOR."panel.html";

        $tpl = \PHPFusion\Template::getInstance('box');

        $tpl->set_template($path);

        $tpl->set_tag('class', $options['class'] ? ' '.$options['class'] : '');

        if ($options['title']) {
            $tpl->set_block('title', $options);
        }

        if ($options['meta']) {
            $tpl->set_block('meta', $options);
        }

        if ($options['image_src']) {
            $tpl->set_block('image_src', $options);
        }

        if ($options['description']) {
            $tpl->set_block('description', $options);
        }

        if ($options['footer']) {
            $tpl->set_block('footer', $options);
        }

        return (string) $this->steam->render($tpl);
    }

    public function code($title, $value, $save = '') {
        $path = $this->steam->getBoilerPath()."html".DIRECTORY_SEPARATOR."code.html";
        $tpl = \PHPFusion\Template::getInstance('box');
        $tpl->set_template($path);
        $tpl->set_tag('title', $title);
        $tpl->set_tag('code', $value);
        if ($save) {
            $tpl->set_block('save_link', ['link'=>$save]);
        }

        return (string) $this->steam->render($tpl);
    }

    public function openside($title = '') {
        return fusion_get_function('openside', $title);
    }

    public function closeside() {
        return fusion_get_function('closeside');
    }

    public function opentable($title = '') {
        return fusion_get_function('opentable', $title);
    }

    public function closetable() {
        return fusion_get_function('closetable');
    }


}
