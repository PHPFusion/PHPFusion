<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Template.inc
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
 * PHP-Fusion Template
 *
 * @package PHPFusion
 */
class Template {

    private static $instance = NULL;
    private $template = array();
    private $block = array();
    private $block_render = array();
    private $block_source = array();
    private $tag = array();
    private static $key = '';
    /**
     * @param $key
     *
     * @return object
     */
    public static function getInstance($key = 'Default') {
        if (empty(self::$instance[$key])) {
            self::$instance[$key] = new Static();
            self::$key = $key;
        }

        return (object)self::$instance[$key];
    }

    /**
     * @return array
     */
    public function get_block() {
        return $this->block;
    }

    /**
     * Define the instance to read a specific HTML file
     *
     * @param $template_src     Template File Source
     */
    public function set_template($template_src) {
        ob_start();
        include $template_src;
        $this->template = ob_get_clean();
    }

    /**
     * Sets to repeat on a subitem defined in the HTML markup  - {block_id.{<subitem_html>}}
     * This function adds a subitem for every set_block used
     *
     * @param       $block_id   The name of the unique block id
     * @param array $value      The replacements sets
     */
    public function set_block($block_id, array $value = array()) {
        $filtered_value = [];

        foreach ($value as $tag => $val) {
            $filtered_value['{%'.$tag.'%}'] = $val;
        }
        $this->block[$block_id][] = $filtered_value;
    }

    /**
     * Sets a tag to string conversion
     *
     * @param $html_tag     {%tag%} in html file is 'tag'
     * @param $value        the value of the string
     */
    public function set_tag($html_tag, $value) {
        $this->tag['{%'.$html_tag.'%}'] = $value;
    }

    /**
     * Renders the output
     * Any unused blocks will not be parsed and deleted. This is useful to remove a div wrapper if condition fails.
     *
     * @return string   The final HTML markup
     */
    public function get_output() {
        $this->template = trim($this->template);
        $block_pattern = '/\{([0-9a-zA-Z_]+)\.\{(.*?)\}\}/s'; // group 1 is id, group 2 is the child template
        preg_match_all($block_pattern, $this->template, $cache, PREG_SET_ORDER);
        // set to ID and Template
        if (!empty($cache)) {
            foreach ($cache as $preg_results) {
                if (!empty($preg_results[1]) && !empty($preg_results[2])) {
                    $this->block_render[$preg_results[1]] = $preg_results[2];
                    $this->block_source[$preg_results[1]] = $preg_results[0];
                }
            }
        }

        if (!empty($this->block)) {
            foreach ($this->block as $block_id => $blocks) {
                $block_results = '';
                if (isset($this->block_render[$block_id]) && isset($this->block_source[$block_id])) {
                    $block_tpl = $this->block_render[$block_id]; // found the corresponding block id.
                    foreach ($blocks as $block_values) {
                        $block_results .= strtr($block_tpl, $block_values)."\n";
                    }
                    // Mutate template by calculated block output
                    $this->template = strtr($this->template, [$this->block_source[$block_id] => $block_results]);
                    unset($this->block_source[$block_id]); // erase the block out of the array for cleaning up
                }
            }
        }

        // Look for any remaining matches, and clean up the template if this block id is unused or is blanked value.
        if (!empty($this->block_source)) {
            foreach ($this->block_source as $block_id => $block_results) {
                $this->template = strtr($this->template, [$this->block_source[$block_id] => '']);
                unset($this->block_source[$block_id]);
            }
        }

        // Direct replacement that doesn't require any conditional checks. This is 1 to 1 string to tag replacements.
        if (!empty($this->tag)) {
            foreach ($this->tag as $tag => $value) {
                if (is_string($tag) && (is_string($value) || isnum($value))) {
                    $this->template = strtr($this->template, [$tag => $value]);
                }
            }
        }


        unset($this->tag);
        unset($this->block);

        return (string)trim($this->template);
    }
}