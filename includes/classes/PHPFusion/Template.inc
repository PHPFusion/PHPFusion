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

    const DefaultID = 'Default';

    private static $instance = NULL;
    private $template = '';
    private $locale = [];
    private $block = [];
    private $block_render = [];
    private $block_source = [];
    private $tag = [];
    private static $key = '';
    private static $locale_list = [];
    private $raw_block = [];
    private $raw_tag = [];
    private static $block_count = [];

    private function __construct() {
    }

    private function __clone() {
    }

    /**
     * @param $key
     *
     * @return static
     */
    public static function getInstance($key = self::DefaultID) {
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new static();
        }
        self::$key = $key;

        return self::$instance[$key];
    }

    public static function set_key($key) {
        self::$key = $key;
    }

    /**
     * Set all files to be used in a template.
     * There is a caveat that file names cannot be same or else it will be overridden
     *
     * @param array $folder_arr
     */
    public function set_file(array $folder_arr = []) {
        if (!empty($folder_arr)) {
            foreach ($folder_arr as $folder_path) {
                if (is_dir($folder_path)) {
                    $files = makefilelist($folder_path, '.|..|._DS_Store');
                    $folder = makefilelist($folder_path, '.|..|._DS_Store', TRUE, 'folders');
                    if (!empty($files)) {
                        foreach ($files as $filename) {
                            $this->set_tag($filename, $folder_path.$filename);
                        }
                    }
                    if (!empty($folder)) {
                        $folderdir = [];
                        foreach ($folder as $foldername) {
                            $folderdir[] = $folder_path.$foldername.'/';
                        }
                        $this->set_file($folderdir);
                    }
                }
            }
        }
    }


    /**
     * @return array
     */
    public function get_block() {
        return $this->block;
    }

    private static $registered_templates = [];

    public function register_template($template_file_path) {
        self::$registered_templates[self::$key] = $template_file_path;
    }

    /**
     * Define the instance to read a specific HTML file
     *
     * @param string $template_file_path Template File Source
     */
    public function set_template($template_file_path) {
        if (isset(self::$registered_templates[self::$key])) {
            $template_file_path = self::$registered_templates[self::$key];
        }
        ob_start();
        @include $template_file_path;
        $this->template = ob_get_clean();
    }

    /**
     * Defines the instance to read a specific text
     *
     * @param $text     string
     */
    public function set_text($text) {
        $this->template = $text;
    }

    /**
     * Defines the instance to replace locales
     *
     * @param array $locales
     */
    public function set_locale(array $locales = []) {
        $this->locale = $locales;
    }

    /**
     * Sets to repeat on a subitem defined in the HTML markup  - {block_id.{<subitem_html>}}
     * This function adds a subitem for every set_block used
     *
     * @param string $block_id The name of the unique block id
     * @param array  $value    The replacements sets
     */
    public function set_block($block_id, array $value = []) {
        $filtered_value = [];
        foreach ($value as $tag => $val) {
            $filtered_value['{%'.$tag.'%}'] = $val;
        }
        $this->block[$block_id][] = $filtered_value;
        $this->raw_block[$block_id][] = $value;
    }

    /**
     * Fetches an entire block of tags
     *
     * Every time this function is used with a block_id specified, it will traverse to the second count to mimic the set_block.
     *
     * @param null $block_id
     * @param null $html_tag
     *
     * @return array|mixed|null
     */

    public function fetch_block($block_id = NULL, $html_tag = NULL) {
        $block_count = '';

        if ($block_id) {
            self::$block_count[$block_id] = 0;
            $block_count = self::$block_count[$block_id];
            self::$block_count[$block_id]++;
        }

        if ($block_id != NULL) {
            // block id is not null.
            if (isset($this->raw_block[$block_id][$block_count])) {
                if ($html_tag != NULL) {
                    if (isset($this->raw_block[$block_id][$block_count][$html_tag])) {
                        return $this->raw_block[$block_id][$block_count][$html_tag];
                    }
                    return NULL;
                } else {
                    if (isset($this->raw_block[$block_id][$block_count])) {
                        return $this->raw_block[$block_id][$block_count];
                    }
                    return NULL;
                }
            }
        } else {
            return $this->raw_block;
        }

        return NULL;
    }

    /**
     * Sets a tag to string conversion
     *
     * @param string $html_tag {%tag%} in html file is 'tag'
     * @param string $value    the value of the string
     */
    public function set_tag($html_tag, $value) {
        $this->tag['{%'.$html_tag.'%}'] = $value;
        $this->raw_tag[$html_tag] = $value;
    }

    /**
     * Fetches a tag
     *
     * @param null $html_tag
     *
     * @return array|mixed|null
     */
    public function fetch_tag($html_tag = NULL) {
        return $html_tag === NULL ? $this->raw_tag : (isset($this->raw_tag[$html_tag]) ? $this->raw_tag[$html_tag] : NULL);
    }

    /**
     * Locale Replacement with Template Macro
     * Macro Pattern - {[locale_keys]}
     * Recursively parse array into an array
     *
     * @param $array
     *
     * @return array
     */
    private function assign_template_locales($array) {
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    self::$locale_list = array_merge(self::$locale_list, $this->assign_template_locales($value));
                } else {
                    self::$locale_list["{[$key]}"] = $value ? nl2br($value) : "";
                }
            }
        }

        return (array)self::$locale_list;
    }

    /**
     * Renders the output
     * Any unused blocks will not be parsed and deleted. This is useful to remove a div wrapper if condition fails.
     *
     * @return string   The final HTML markup
     */
    public function get_output() {
        $this->template = trim($this->template);

        // Locale replacements
        if (!empty($this->locale)) {
            if ($this->assign_template_locales($this->locale)) {
                $this->template = strtr($this->template, self::$locale_list);
            }
        }

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
