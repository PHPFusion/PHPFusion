<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Search_Model.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Search;
/**
 * Class Search_Model
 *
 * @package PHPFusion\Search
 */
abstract class SearchModel {
    public static $locale = [];
    protected static $available_modules = [];
    protected static $form_config = [];
    protected static $search_result_array = [];
    protected static $site_search_count = 0;
    protected static $navigation_result = '';
    protected static $items_count = '';
    protected static $global_string_count = 0;
    protected static $memory_limit = 0;

    // Query Formatting
    protected static $memory_exhausted = FALSE;
    protected static $fields_count = 0;
    protected static $swords = [];
    protected static $c_swords = 0;
    protected static $i_swords = 0;
    protected static $swords_keys_for_query = [];
    protected static $swords_values_for_query = [];
    protected static $conditions = [];

    /*
     * Default values
     */
    protected static $fieldsvar = '';
    protected static $rowstart = 0;
    protected static $search_text = '';
    protected static $search_method = 'OR';
    protected static $search_date_limit = 0;
    protected static $search_fields = 2;
    protected static $search_sort = 'datestamp';
    protected static $search_order = 0;
    protected static $search_chars = 100;
    protected static $forum_id = 0;
    protected static $search_type = 'all';
    protected static $search_param = [];
    protected static $composevars = '';
    private static $search_index = 0;
    private static $search_mod;

    protected function __construct() {
    }

    public static function append_item_count($value) {
        self::$items_count .= $value;
    }

    public static function search_striphtmlbbcodes($text) {
        $text = preg_replace("[\[(.*?)\]]", "", $text);
        return preg_replace("<\<(.*?)\>>", "", $text);
    }

    public static function search_textfrag($text) {
        if (empty($text)) {
            return NULL;
        }

        if (Search_Engine::get_param('chars') != 0) {
            if (function_exists('mb_substr')) {
                $text = nl2br(stripslashes(mb_substr($text, 0, Search_Engine::get_param('chars'), 'UTF-8')."..."));
            } else {
                $text = nl2br(stripslashes(substr($text, 0, Search_Engine::get_param('chars'))."..."));
            }
        } else {
            $text = nl2br(stripslashes($text));
        }

        return $text;
    }

    public static function search_stringscount($text) {
        $count = 0;
        $c_swords = self::$c_swords;
        for ($i = 0; $i < $c_swords; $i++) {
            $count += substr_count(strtolower($text), strtolower(self::$swords[$i]));
        }

        return $count;
    }

    public static function search_column($field, $field_module) {
        if (self::$search_mod == $field_module) {
            self::$search_index++;
        } else {
            self::$search_mod = $field_module;
            self::$search_index = 0;
        }
        $last_sword_index = self::$c_swords - 1;
        for ($i = 0; $i < self::$c_swords; $i++) {
            if (isset(self::$swords_keys_for_query[$i])) {
                $sword_var = self::$swords_keys_for_query[$i];
                self::$conditions[$field_module][$field][] = $field." LIKE $sword_var".($i < $last_sword_index ? ' '.Search_Engine::get_param('method').' ' : '');
            }
        }
    }

    public static function search_conditions($field_module) {
        // the conditions are imposition and must reset.
        if (!empty(self::$conditions[$field_module])) {
            return "(".implode(' || ', array_map(function ($field_var) {
                    return implode('', $field_var);
                }, self::$conditions[$field_module])).")";
        } else {
            return NULL;
        }
    }

    public static function search_navigation($rows) {
        self::$site_search_count += $rows;
        $navigation_result = makepagenav(Search_Engine::get_param('rowstart'), 10, (self::$site_search_count > 100 || self::search_globalarray("") ? 100 : self::$site_search_count), 3, BASEDIR."search.php?stype=".Search_Engine::get_param('stype')."&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars'));
        self::$navigation_result = $navigation_result;
    }

    public static function search_globalarray($search_result) {
        if (!empty($search_result)) {
            self::$global_string_count += strlen($search_result);
            if (self::$memory_limit > self::$global_string_count) {
                self::$search_result_array[] = $search_result;
                self::$memory_exhausted = FALSE;
            } else {
                self::$memory_exhausted = TRUE;
            }
        }

        return self::$memory_exhausted;
    }

    protected function init() {

        $search_modules = self::cache_modules();

        // Formats POST
        if ($rowstart = get("rowstart", FILTER_VALIDATE_INT)) {
            self::$rowstart = $rowstart;
        }

        // Formats Search Method
        if ($search_method = $this->searchRequest("method")) {
            if (in_array($search_method, ["OR", "AND"])) {
                self::$search_method = $search_method;
            }
        }
        // Formats sText
        if ($search_text = $this->searchRequest("stext")) {
            self::$search_text = urlencode($search_text);
        }

        // Formats search date limit
        if ($datelimit = $this->searchRequest("datelimit")) {
            self::$search_date_limit = $datelimit;
        }

        // Fields
        if ($search_fields = $this->searchRequest("fields", FILTER_VALIDATE_INT)) {
            self::$search_fields = $search_fields;
        }

        // Sorting
        if ($search_sort = $this->searchRequest("sort")) {
            if (in_array($search_sort, ["datestamp", "subject", "author"])) {
                self::$search_sort = $search_sort;
            }
        }

        // Orders
        if ($search_order = $this->searchRequest("order", FILTER_VALIDATE_INT)) {
            self::$search_order = $search_order;
        }
        // Characters
        if ($search_chars = $this->searchRequest("chars", FILTER_VALIDATE_INT)) {
            self::$search_chars = ($search_chars > 100 ? 100 : $search_chars);
        }

        // Forum ID
        if ($forum_id = $this->searchRequest("forum_id", FILTER_VALIDATE_INT)) {
            self::$forum_id = $forum_id;
        }

        // Search type
        self::$search_type = lcfirst(str_replace('.php', '', fusion_get_settings('default_search')));
        if ($search_type = $this->searchRequest("stype")) {
            if (in_array($search_type, $search_modules)) {
                self::$search_type = lcfirst($search_type);
            }
        }

        self::$form_config = self::load_search_modules();

        // Memory Limits
        $memory_limit = floatval(ini_get('memory_limit')); // remove units
        $memory_limit = strpos(ini_get('memory_limit'), 'G') !== FALSE ? $memory_limit * 1024 : $memory_limit;
        $memory_limit = $memory_limit * 1024 * 1024;
        self::$memory_limit = $memory_limit - ceil($memory_limit / 4);
    }

    protected function cache_modules() {
        if (empty(self::$available_modules)) {
            self::$available_modules = ['0' => 'all'];
            $search_deffiles = [];
            $search_includefiles = makefilelist(INCLUDES."search/", '.|..|index.php|location.json.php|users.json.php|.DS_Store', TRUE);
            $search_infusionfiles = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");

            if (!empty($search_infusionfiles)) {
                foreach ($search_infusionfiles as $files_to_check) {
                    if (is_dir(INFUSIONS.$files_to_check.'/search/')) {
                        $search_checkfiles = makefilelist(INFUSIONS.$files_to_check.'/search/', ".|..|index.php", TRUE);
                        $search_deffiles = array_merge($search_deffiles, $search_checkfiles);
                    }
                }
            }

            $search_files = array_merge($search_includefiles, $search_deffiles);
            foreach ($search_files as $file_to_check) {
                if (preg_match("/include_button.php/i", $file_to_check)) {
                    $file_name = str_replace('_include_button.php', '', $file_to_check);
                    $file_name = str_replace('search_', '', $file_name);
                    self::$available_modules[] = $file_name;
                }
            }
        }

        return self::$available_modules;
    }

    /**
     * @param string $key
     * @param int    $flags
     *
     * @return string
     */
    function searchRequest($key, $flags = FILTER_DEFAULT) {
        $methods = ["post", "get"];
        foreach ($methods as $method) {
            if ($value = $method($key, $flags)) {
                return $value;
            }
        }
        return "";
    }

    public function load_search_modules() {
        $radio_button = [];
        $form_elements = [];
        if (!empty(self::$available_modules)) {
            foreach (self::$available_modules as $module_name) {
                if ($module_name !== 'all') {
                    if (file_exists(INCLUDES."search/search_".$module_name."_include_button.php")) {
                        include_once(INCLUDES."search/search_".$module_name."_include_button.php");
                    }

                    $infusions = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
                    if (!empty($infusions)) {
                        foreach ($infusions as $infusions_to_check) {
                            if (is_dir(INFUSIONS.$infusions_to_check.'/search/')) {
                                $search_files = makefilelist(INFUSIONS.$infusions_to_check.'/search/', ".|..|index.php", TRUE);

                                if (!empty($search_files)) {
                                    foreach ($search_files as $file_to_check) {
                                        if (preg_match("/_include_button\.php$/i", $file_to_check)) {
                                            if (file_exists(INFUSIONS.$infusions_to_check."/search/".$file_to_check)) {
                                                include_once(INFUSIONS.$infusions_to_check."/search/".$file_to_check);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            }
        }
        sort($radio_button);
        self::$form_config = [
            'form_elements' => $form_elements,
            'radio_button'  => $radio_button,
        ];

        return self::$form_config;
    }
}
