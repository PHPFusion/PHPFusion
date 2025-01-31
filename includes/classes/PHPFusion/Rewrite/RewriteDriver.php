<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: RewriteDriver.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Ankur Thakur
| Co-Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Rewrite;

abstract class RewriteDriver {

    protected $data_statements = [];

    /**
     * Array of Handlers
     * example: news, threads, articles
     *
     * @data_type Array
     * @access    protected
     */
    protected $handlers = [];

    /**
     * Array of Total Queries which were run.
     *
     * @data_type Array
     * @access    protected
     */
    protected $queries = [];

    /**
     * The site render HTML buffer which is to be scanned
     *
     * @data_type string
     * @access    protected
     */

    protected $output = "";

    /**
     * Tags for the permalinks.
     * example: %thread_id%, %news_id%
     *
     * @data_type Array
     * @access    protected
     */

    protected $rewrite_code = [];

    /**
     * Replacement for Tags for REGEX.
     * example: %thread_id% should be replaced with ([0-9]+)
     *
     * @data_type Array
     * @access    protected
     */
    protected $rewrite_replace = [];

    /**
     * Array of DB Table Names with Schema
     * example: prefix_news, prefix_threads, prefix_articles
     *
     * @data_type Array
     * @access    protected
     */
    protected $pattern_tables = [];

    /**
     * Array of Pattern for Aliases
     * which are made for matching.
     *
     * @data_type Array
     * @access    protected
     */
    protected $alias_pattern = [];

    /**
     * Permalink Patterns which will be searched
     * to match against current request.
     *
     * @data_type Array
     * @access    protected
     */
    protected $pattern_search = [];

    /**
     * Target URLs to which permalink request will be rewriten.
     *
     * @data_type Array
     * @access    protected
     */
    protected $pattern_replace = [];

    /**
     * Array of Regular Expressions Patterns
     * which are made for matching.
     *
     * @data_type Array
     * @access    protected
     */
    protected $patterns_regex = [];

    protected $aliases = [];

    /**
     * Statements are calculation results of Rewrite scan
     * We will have various types of regex statements
     * This is the result data of the entire permalink success/fails
     *
     * @var array
     */
    protected $regex_statements = [];

    /**
     * Portion of the URL to match in the Regex
     *
     * @data_type String
     * @access    protected
     */
    protected $requesturi = "";

    /**
     * Array of Warnings
     *
     * @data_type Array
     * @access    protected
     */
    protected $warnings = [];
    protected $buffer_search_regex = [];
    protected $path_search_regex = [];

    protected $dbid = [];

    /**
     * Import Handlers from Database
     * This will import all the Enabled Handlers from the Database Table
     *
     * @access protected
     */
    protected function loadSqlDrivers() {
        $query = "SELECT rewrite_name FROM ".DB_PERMALINK_REWRITE;
        $result = dbquery($query);
        $this->queries[] = $query;
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $this->addRewrite($data['rewrite_name']);
            }
        }
    }

    /**
     * Add the rewrite include file to be included
     * This will Add new rewrite include file to be included.
     *
     * @param string $include_prefix Prefix of the file to be included.
     *
     * @access protected
     */
    private function addRewrite($include_prefix) {
        if ($include_prefix != "" && !in_array($include_prefix, $this->handlers)) {
            $this->handlers[] = $include_prefix;
        }
    }

    /**
     * Include the rewrite include file
     * The include file will be included from
     * INCLUDES."rewrites/".PREFIX."_rewrite_include.php
     *
     * @access protected
     */
    protected function includeRewrite() {
        if (is_array($this->handlers) && !empty($this->handlers)) {
            foreach ($this->handlers as $name) {
                if (file_exists(BASEDIR."includes/rewrites/".$name."_rewrite_include.php")) {
                    // If the File is found, include it
                    include_once BASEDIR."includes/rewrites/".$name."_rewrite_include.php";
                    if (isset($regex) && is_array($regex)) {
                        $this->addRegexTag($regex, $name);
                        unset($regex);
                    }
                    // Load pattern tables into driver
                    if (isset($pattern_tables) && is_array($pattern_tables)) {
                        $this->pattern_tables[$name] = $pattern_tables;
                        unset($pattern_tables);
                    }
                } /*else {
                    $this->setWarning(4, BASEDIR."includes/rewrites/".$name."_rewrite_include.php");
                }*/

                $infusions = makefilelist(INFUSIONS, '.|..|index.php', TRUE, 'folders');
                foreach ($infusions as $inf_file) {
                    if (file_exists(INFUSIONS.$inf_file."/permalinks/".$name."_rewrite_include.php")) {
                        include_once INFUSIONS.$inf_file."/permalinks/".$name."_rewrite_include.php";
                        if (isset($regex) && is_array($regex)) {
                            $this->addRegexTag($regex, $name);
                            unset($regex);
                        }
                        // Load pattern tables into driver
                        if (isset($pattern_tables) && is_array($pattern_tables)) {
                            $this->pattern_tables[$name] = $pattern_tables;
                            unset($pattern_tables);
                        }
                    }/* else {
                        $this->setWarning(4, INFUSIONS.$inf_file."/permalinks/".$name."_rewrite_include.php");
                    }*/
                }
            }
        }
    }

    /**
     * Adds the Regular Expression Tags
     * This will Add Regex Tags, which will be replaced in the
     * search patterns.
     * Example: %news_id% could be replaced with ([0-9]+) as it must be a number.
     *
     * @param array  $regex  Array of Tags to be added.
     * @param string $driver Type or Handler name
     *
     * @access protected
     */
    protected function addRegexTag($regex, $driver) {
        foreach ($regex as $reg_search => $reg_replace) {
            $this->rewrite_code[$driver][] = $reg_search;
            $this->rewrite_replace[$driver][] = $reg_replace;
        }
    }

    /**
     * Verify Handlers
     * This will verify all the added Handlers by checking if they are enabled
     * or not. The Disabled Handlers are removed from the List and only Enabled
     * Handlers are kept for working.
     */
    protected function verifyHandlers() {
        if (!empty($this->handlers)) {
            $types = [];
            foreach ($this->handlers as $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }
            $types_str = implode(",", $types);
            $query = "SELECT rewrite_name FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name IN(".$types_str.")";
            $this->queries[] = $query;
            $result = dbquery($query);
            $types_enabled = [];
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $types_enabled[] = $data['rewrite_name'];
                }
            }
            // Compute the Intersection
            // This is because we want only those Handlers, which are Enabled on website by admin
            $this->handlers = array_intersect($this->handlers, $types_enabled);
        }
    }

    /**
     * Prepare search regex
     */
    protected function prepareSearchRegex() {
        /**
         * Buffer Regex on non SEF page will fail to match
         * Router must work to reroute to SEF page for Permalink to parse links.
         */
        foreach ($this->pattern_search as $driver => $RawSearchPatterns) {

            if (!empty($RawSearchPatterns) && is_array($RawSearchPatterns)) {

                foreach ($RawSearchPatterns as $key => $val) {

                    $buffer_regex = $this->appendSearchPath($val);

                    $buffer_regex = self::cleanRegex($buffer_regex);

                    if (isset($this->rewrite_code[$driver]) && isset($this->rewrite_replace[$driver])) {
                        $buffer_regex = str_replace($this->rewrite_code[$driver], $this->rewrite_replace[$driver],
                            $buffer_regex);
                    }

                    $search_regex = $this->cleanRegex($val);

                    if (isset($this->rewrite_code[$driver]) && isset($this->rewrite_replace[$driver])) {
                        $search_regex = str_replace($this->rewrite_code[$driver], $this->rewrite_replace[$driver], $search_regex);
                    }

                    $this->buffer_search_regex[$driver][$key] = $buffer_regex;

                    $this->path_search_regex[$driver][$key] = $search_regex;
                }

            }
        }
    }

    /**
     * Append the BASEDIR Path to Search String
     * This function will append the BASEDIR path to the Search pattern. This is
     * required in some cases like when we are on actual php script page and
     * Permalinks are ON.
     *
     * @param string $str The String
     *
     * @return string
     * @access protected
     */
    protected function appendSearchPath($str) {
        static $base_files = [];
        $basedir = BASEDIR;
        if (empty($base_files) && !empty($basedir)) {
            $base_files = makefilelist(BASEDIR, ".|..");
        }
        foreach ($base_files as $files) {
            if (stristr($str, $files)) {
                return $str;
            }
        }
        return BASEDIR.$str;
    }

    /**
     * Clean the REGEX by escaping some characters
     * This function will escape some characters in the Regex expression
     *
     * @param string $regex The expression String
     *
     * @return string
     */
    protected static function cleanRegex($regex) {
        $regex = str_replace("/", "\/", $regex);
        $regex = str_replace("#", "\#", $regex);
        $regex = str_replace(".", "\.", $regex);
        $regex = str_replace("?", "\?", $regex);

        return (string)$regex;
    }

    /**
     * Builds the Regular Expressions Patterns for Permalink Translations
     * This function reads HTML output buffer
     * This function will create the Regex patterns and will put the built patterns
     * in $patterns_regex array. This array will then be used in preg_match function
     * to match against current request.
     *
     * @access protected
     */
    protected function handlePermalinkRequests() {
        $loop_count = 0;
        /**
         * Generate Permalink Search and Replacements Requests Statements
         * Buffering requests for translations if search is found on HTML output
         * Cache each driver's search variables, replace variables and match variables
         * Opening Development towards a no ID SEF
         */
        foreach ($this->buffer_search_regex as $field => $searchRegex) {

            foreach ($searchRegex as $key => $search) {

                // search pattern must try twice. one with append and once without append.

                $search_pattern = $this->pattern_search[$field][$key];
                $replace_pattern = $this->pattern_replace[$field][$key];

                // Resets
                $tag_values = [];
                $tag_matches = [];
                $output_matches = [];
                $replace_matches = [];
                $statements = [];

                $root_prefix = $this->cleanRegex(ROOT);

                $search_str = $this->wrapQuotes($search);

                $root_search_str = $this->wrapQuotes($root_prefix.$search);

                $search_str2 = $this->wrapDoubleQuotes($search);

                $root_search_str2 = $this->wrapDoubleQuotes($root_prefix.$search);

                if (preg_match("~$search_str~i", $this->output)
                    or preg_match("~$root_search_str~i", $this->output)
                    or preg_match("~$search_str2~i", $this->output)
                    or preg_match("~$root_search_str2~i", $this->output)
                ) {

                    preg_match_all("~$search_str~i", $this->output, $output_matches, PREG_PATTERN_ORDER);

                    if (empty($output_matches[0])) {
                        preg_match_all("~$search_str2~i", $this->output, $output_matches, PREG_PATTERN_ORDER);
                    }

                    if (empty($output_matches[0])) {
                        preg_match_all("~$root_search_str~i", $this->output, $output_matches, PREG_PATTERN_ORDER);
                    }

                    if (empty($output_matches[0])) {
                        preg_match_all("~$root_search_str2~i", $this->output, $output_matches, PREG_PATTERN_ORDER);
                    }

                    preg_match_all("~%(.*?)%~i", $search_pattern, $tag_matches);

                    preg_match_all("~%(.*?)%~i", $replace_pattern, $replace_matches);

                    if (!empty($tag_matches[0])) {

                        $tagData = array_combine(range(1, count($tag_matches[0])), array_values($tag_matches[0]));

                        foreach ($tagData as $tagKey => $tagVal) {

                            $tag_values[$tagVal] = $output_matches[$tagKey];

                            if (isset($this->pattern_tables[$field][$tagVal])) {

                                $table_info = $this->pattern_tables[$field][$tagVal];

                                $table_columns = array_flip($table_info['columns']);

                                $request_column = array_intersect($replace_matches[0], $table_columns);

                                $search_value = array_unique($output_matches[$tagKey]);

                                if (!empty($request_column)) {
                                    $columns = [];
                                    foreach ($request_column as $column_tag) {
                                        $columns[$column_tag] = $table_info['columns'][$column_tag];
                                        $loop_count++;
                                    }

                                    $column_info = array_flip($columns);

                                    $sql = "SELECT ".$table_info['primary_key'].", ".implode(", ", $columns)." ";
                                    $sql .= "FROM ".$table_info['table'];
                                    $sql .= " WHERE ".(!empty($table_info['query']) ? $table_info['query']." AND " : "");
                                    $sql .= $table_info['primary_key']." IN (".implode(",", $search_value).")";

                                    $result = dbquery($sql);

                                    if (dbrows($result) > 0) {

                                        $other_values = [];
                                        $data_cache = [];

                                        while ($data = dbarray($result)) {

                                            $dataKey = $data[$table_info['primary_key']];

                                            unset($data[$table_info['primary_key']]);

                                            if (!empty($data)) { // Remove when debugging

                                                foreach ($data as $key => $value) {
                                                    $data_cache[$column_info[$key]] [$dataKey] = $value;
                                                }
                                            }
                                            $loop_count++;
                                        }

                                        if (!empty($data_cache)) { // Remove when debugging
                                            foreach ($data_cache as $key => $value) {
                                                for ($i = 0; $i < count($output_matches[0]); $i++) {
                                                    // @inspection for parsing error
                                                    $corresponding_value = (!empty($tag_values[$tagVal][$i]) ? $tag_values[$tagVal][$i] : "");
                                                    $other_values[$key][$i] = (!empty($value[$corresponding_value]) ? $value[$corresponding_value] : "");
                                                    $loop_count++;
                                                }
                                            }
                                        }

                                        $tag_values += $other_values;
                                    }
                                }
                            }

                            $loop_count++;
                        }
                    }

                    /**
                     * Generate Statements for each buffer matches
                     * First, we merge the basic ones that has without tags
                     * It is irrelevant whether these are from SQL or from Preg
                     */
                    for ($i = 0; $i < count($output_matches[0]); $i++) {
                        $statements[$i]["search"] = $search_pattern;
                        $statements[$i]["replace"] = $replace_pattern;
                        $loop_count++;
                    }

                    reset($tag_values);

                    foreach ($tag_values as $regexTag => $tag_replacement) {
                        for ($i = 0; $i < count($output_matches[0]); $i++) {

                            $statements[$i]["search"] = str_replace($regexTag, $tag_replacement[$i],
                                $statements[$i]["search"]);
                            $statements[$i]["replace"] = str_replace($regexTag, $tag_replacement[$i],
                                $statements[$i]["replace"]);
                            $loop_count++;
                        }
                        $loop_count++;
                    }

                    foreach ($statements as $value) {

                        $permalink_search = "~".$this->wrapQuotes($this->cleanRegex($this->appendSearchPath($value['search'])))."~i";

                        $permalink_search_doublequote = "~".$this->wrapDoubleQuotes($this->cleanRegex($this->appendSearchPath($value['search'])))."~i";

                        $permalink_root_search = "~".$this->wrapQuotes($this->cleanRegex(FUSION_ROOT.$this->appendSearchPath($value['search'])))."~i";

                        $permalink_root_search_doublequote = "~".$this->wrapDoubleQuotes($this->cleanRegex(FUSION_ROOT.$this->appendSearchPath($value['search'])))."~i";

                        $permalink_replace = $this->wrapQuotes($this->cleanUrl($value['replace']));

                        $permalink_replace_doublequote = $this->wrapDoubleQuotes($this->cleanUrl($value['replace']));

                        $permalink_root_replace = $this->wrapQuotes($this->cleanUrl(fusion_get_settings("site_path").$value['replace']));

                        $permalink_root_replace_doublequote = $this->wrapDoubleQuotes($this->cleanUrl(fusion_get_settings("site_path").$value['replace']));

                        $this->regex_statements['pattern'][$field][$permalink_search] = $permalink_replace;

                        $this->regex_statements['pattern'][$field][$permalink_root_search] = $permalink_root_replace;

                        $this->regex_statements['pattern'][$field][$permalink_search_doublequote] = $permalink_replace_doublequote;

                        $this->regex_statements['pattern'][$field][$permalink_root_search_doublequote] = $permalink_root_replace_doublequote;

                    }

                    /*$output_capture_buffer = [
                        "search"          => $search,
                        "output_matches"  => $output_matches[0],
                        "replace_matches" => $replace_matches[0],
                        "seach_pattern"   => $search_pattern,
                        "replace_patern"  => $replace_pattern,
                        "tag_values"      => $tag_values,
                        "statements"      => $statements,
                        "loop_counter"    => $loop_count,
                    ];*/

                } else {

                    preg_match_all("~$search_str~i", $this->output, $match);

                    $this->regex_statements['failed'][$field][] = [
                        "search"  => $search,
                        "status"  => "No matching content or failed regex matches",
                        "results" => $match
                    ];

                }

                //print_p($this->regex_statements);

            }
        }
    }

    /**
     * Wrap a String with Single Quotes (')
     * This function will wrap a string passed with Single Quotes.
     * Example: mystring will become 'mystring'
     *
     * @param string $str The String
     *
     * @access protected
     * @return string
     */
    protected static function wrapQuotes($str) {
        return "'".$str."'";
    }

    /**
     * Add compatibalities with double quotes HTML codes for Permalink
     *
     * @param $str
     *
     * @return string
     */
    protected static function wrapDoubleQuotes($str) {
        return "\"".$str."\"";
    }

    /**
     * Cleans the URL
     * This function will clean the URL by removing any unwanted characters from it and
     * only allowing alphanumeric and - in the URL.
     * This function can be customized according to your needs.
     *
     * @param string $string The URL String
     * @param string $delimiter
     *
     * @return string
     * @access protected
     */
    protected static function cleanUrl($string, $delimiter = "-") {
        if (fusion_get_settings("normalize_seo") == "1") {
            $string = normalize($string);
            if (function_exists('iconv')) {
                $string = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $string);
            }
        }

        /*
        * More simple normalize function for customization, use HTML for it to understand ASCII in some instances like Apostrophe in there.
        * Because we run mixed charset tables.
        * we could mb_string or iconv it, but we have the normalize function this is manual override for specific case scenarios.
        */

        $search = ["\"", "&#039;", "\\", "\'", "<", ">", "~", "!", "@", "$", "%", "^", "*", ";", ",", "[", "]", "{", "}", ".", "|"];
        $string = str_replace($search, "", $string);
        $string = str_replace("&amp;", "&", $string); // Must Support &
        $string = str_replace(" - ", "-", $string);
        $string = preg_replace("/[\s]+/i", $delimiter, $string); // Replace All <space> by Delimiter
        $string = trim($string, "-");
        $string = strtolower($string);

        return (string)$string;
    }

    /**
     * If Page is Not SEO, Redirect to SEO one
     */
    protected function handleNonSeoUrl() {
        $this->requesturi = stripinput($this->requesturi);
        $loop_count = 0;
        foreach ($this->path_search_regex as $field => $searchRegex) {

            foreach ($searchRegex as $key => $search) {

                $search_pattern = $this->pattern_search[$field][$key];
                $replace_pattern = $this->pattern_replace[$field][$key];

                // Resets
                $tag_values = [];
                $tag_matches = [];
                $output_matches = [];
                $replace_matches = [];
                $statements = [];

                if (preg_match("~$search$~i", $this->requesturi)
                    or (!empty($_POST) && preg_match("~".FUSION_ROOT.$search."~i", $this->requesturi))
                ) {

                    // enable for debug - print_p($search);
                    preg_match_all("~$search~i", $this->requesturi, $output_matches, PREG_PATTERN_ORDER);

                    if (empty($output_matches[0]) && !empty($_POST)) {
                        preg_match_all("~".FUSION_ROOT.$search."~i", $this->requesturi, $output_matches, PREG_PATTERN_ORDER);
                    }

                    preg_match_all("~%(.*?)%~i", $search_pattern, $tag_matches);

                    preg_match_all("~%(.*?)%~i", $replace_pattern, $replace_matches);

                    if (!empty($tag_matches[0])) {

                        $tagData = array_combine(range(1, count($tag_matches[0])), array_values($tag_matches[0]));

                        foreach ($tagData as $tagKey => $tagVal) {

                            $tag_values[$tagVal] = $output_matches[$tagKey];

                            if (isset($this->pattern_tables[$field][$tagVal])) {

                                $table_info = $this->pattern_tables[$field][$tagVal];

                                $table_columns = array_flip($table_info['columns']);

                                $request_column = array_intersect($replace_matches[0], $table_columns);

                                $search_value = array_unique($output_matches[$tagKey]);

                                if (!empty($request_column)) {
                                    $columns = [];
                                    foreach ($request_column as $column_tag) {
                                        $columns[$column_tag] = $table_info['columns'][$column_tag];
                                        $loop_count++;
                                    }

                                    $column_info = array_flip($columns);

                                    /**
                                     * Each SEF Rule Declared, You are going to spend 1 SQL query
                                     */
                                    $sql = "SELECT ".$table_info['primary_key'].", ".implode(", ", $columns)." ";
                                    $sql .= "FROM ".$table_info['table'];
                                    $sql .= " WHERE ".(!empty($table_info['query']) ? $table_info['query']." AND " : "");
                                    $sql .= $table_info['primary_key']." IN (".implode(",", $search_value).")";

                                    $result = dbquery($sql);

                                    if (dbrows($result) > 0) {
                                        $other_values = [];
                                        $data_cache = [];
                                        while ($data = dbarray($result)) {
                                            $dataKey = $data[$table_info['primary_key']];
                                            unset($data[$table_info['primary_key']]);
                                            foreach ($data as $key => $value) {
                                                $data_cache[$column_info[$key]] [$dataKey] = $value;
                                            }
                                            $loop_count++;
                                        }

                                        foreach ($data_cache as $key => $value) {
                                            for ($i = 0; $i < count($output_matches[0]); $i++) {
                                                $corresponding_value = $tag_values[$tagVal][$i];
                                                $other_values[$key][$i] = $value[$corresponding_value];
                                                $loop_count++;
                                            }
                                        }

                                        $tag_values += $other_values;
                                    }
                                }
                            }
                            $loop_count++;
                        }
                    }
                    /**
                     * Generate Statements for each buffer matches
                     * First, we merge the basic ones that has without tags
                     * It is irrelevant whether these are from SQL or from Preg
                     */
                    for ($i = 0; $i < count($output_matches[0]); $i++) {
                        $statements[$i]["search"] = $search_pattern;
                        $statements[$i]["replace"] = $replace_pattern;
                        $loop_count++;
                    }

                    reset($tag_values);

                    foreach ($tag_values as $regexTag => $tag_replacement) {
                        for ($i = 0; $i < count($output_matches[0]); $i++) {

                            $statements[$i]["search"] = str_replace($regexTag, $tag_replacement[$i],
                                $statements[$i]["search"]);
                            $statements[$i]["replace"] = str_replace($regexTag, $tag_replacement[$i],
                                $statements[$i]["replace"]);
                            $loop_count++;
                        }
                        $loop_count++;
                    }

                    // Change Redirect via Scan
                    if (isset($statements[0]['replace'])) {
                        $this->redirect301($this->cleanUrl($statements[0]['replace']));
                    }

                    /*$output_capture_buffer = [
                        "search"          => $search,
                        "output_matches"  => $output_matches[0],
                        "replace_matches" => $replace_matches[0],
                        "seach_pattern"   => $search_pattern,
                        "replace_patern"  => $replace_pattern,
                        "tag_values"      => $tag_values,
                        "statements"      => $statements,
                        "loop_counter"    => $loop_count,
                    ];*/

                } else {

                    preg_match_all("~$search~i", $this->output, $match);

                    $this->regex_statements['failed'][$field][] = [
                        "search"  => $search,
                        "status"  => "No matching content or failed regex matches",
                        "results" => $match
                    ];
                }
            }
        }
    }

    /**
     * Redirect 301 : Moved Permanently Redirect
     * This function invoked to prevent of caching any kinds of Non SEO URL on render.
     * Let search engine mark as 301 permanently
     *
     * @param string $target The target URL
     * @param bool   $debug
     *
     * @access protected
     */
    protected static function redirect301($target, $debug = FALSE) {
        if ($debug) {
            debug_print_backtrace();
        } else {
            ob_get_contents();
            if (ob_get_length() !== FALSE) {
                ob_end_clean();
            }
            $url = fusion_get_settings('siteurl').$target;
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$url);
        }
        exit();
    }

    /**
     * Replace Other Tags in Pattern
     * This function will replace all the Tags in the Pattern with their suitable found
     * matches. All the Information is passed to the function, and it will replace the
     * tags with their respective matches.
     *
     * @param string $type     Type of Pattern
     * @param string $search   specific Search Pattern
     * @param string $replace  specific Replace Pattern
     * @param array  $matches  Array of the Matches found for a specific pattern
     * @param string $matchkey A Unique matchkey for different matches found for same pattern
     *
     * @return string
     */
    protected function replaceOtherTags($type, $search, $replace, $matches, $matchkey) {
        if (isset($this->rewrite_code[$type])) {
            foreach ($this->rewrite_code[$type] as $other_tags) {
                if (strstr($replace, $other_tags)) {
                    $clean_tag = str_replace("%", "", $other_tags); // Remove % for Searching the Tag

                    // +1 because Array key starts from 0 and matches[0] gives the complete match
                    $tagpos = $this->getTagPosition($search, $clean_tag); // +2 because of %alias_target%

                    if ($tagpos != 0) {
                        $tag_matches = $matches[$tagpos]; // This is to remove duplicate matches
                        if ($matchkey != -1) {
                            $replace = str_replace($other_tags, $tag_matches[$matchkey], $replace);
                        } else {
                            $replace = str_replace($other_tags, $tag_matches, $replace);
                        }
                    }
                }
            }
        }

        return (string)$replace;
    }

    /**
     * Calculates the Tag Position in a given pattern.
     * This function will calculate the position of a given Tag in a given pattern.
     * Example: %id% is at 2 position in articles-%title%-%id%
     *
     * @param string $pattern The Pattern string in which particular Tag will be searched.
     * @param string $search  The Tag which will be searched.
     *
     * @access protected
     * @return int
     */
    protected function getTagPosition($pattern, $search) {
        if (preg_match_all("#%([a-zA-Z0-9_]+)%#i", $pattern, $matches)) {
            $key = array_search($search, $matches[1]);

            return intval($key + 1);
        } else {
            $this->setWarning(5, $search);

            return 0;
        }
    }

    /**
     * Set Warnings
     * This function will set Warnings. It will set them by Adding them into
     * the $this->warnings array.
     *
     * @param integer $code The Code Number of the Warning
     * @param string  $info Any other Info to Show along with Warning
     *
     * @access protected
     */
    protected function setWarning($code, $info = "") {
        $info = ($info != "") ? $info." : " : "";
        $warnings = [
            1 => "No matching Alias found.", 2 => "No matching Alias Pattern found.",
            3 => "No matching Regex pattern found.", 4 => "Rewrite Include file not found.",
            5 => "Tag not found in the pattern.", 6 => "File path is empty.", 7 => "Alias found.",
            8 => "Alias Pattern found.", 9 => "Regex Pattern found."
        ];
        if ($code <= 6) {
            $this->warnings[] = "<span style='color:#ff0000;'>".$info.$warnings[$code]."</span>";
        } else {
            $this->warnings[] = "<span style='color:#009900;'>".$info.$warnings[$code]."</span>";
        }
    }

    /**
     * Get the Tag of the Unique ID type
     * Example: For news, unique ID should be news_id
     * So it will return %news_id% because of array("%%news_id" => "news_id")
     *
     * @param string $type Type or Handler name
     *
     * @access       protected
     * @return string
     */
    protected function getUniqueIDtag($type) {
        $tag = "";
        if (isset($this->dbid[$type]) && is_array($this->dbid[$type])) {
            $res = array_keys($this->dbid[$type]);
            $tag = $res[0];
        }

        return (string)$tag;
    }

    /**
     * Adds the Regular Expression Tags -- for permalink search regex
     * This will Add Regex Tags, which will be replaced in the
     * search patterns.
     * Example: %news_id% could be replaced with ([0-9]+) as it must be a number.
     *
     * @param string $pattern Array of Tags to be added.
     * @param string $type    Type or Handler name
     *
     * @access protected
     * @return string
     */
    protected function makeSearchRegex($pattern, $type) {
        $regex = $pattern;

        $regex = $this->cleanRegex($regex);

        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
        }

        $regex = $this->wrapQuotes($regex);

        return "~".$regex."~i";
    }

    /**
     * Entrance to Permalink from PHP Buffer
     * This function prepares HTML codes to be preg_matched against Permalink Driver
     * Decode everything
     *
     * @param $output
     */
    protected function setOutput($output) {
        $this->output = $output;
    }

    /**
     * Clean the URI String for MATCH/AGAINST in MySQL
     * This function will Clean the string and removes any unwanted characters from it.
     *
     * @param $mystr - string
     *
     * @access protected
     * @return string
     */
    protected function cleanString($mystr = "") {
        $search = ["", "\"", "'", "\\", "\'", "<", ">"];
        return str_replace($search, "", $mystr);
    }

    /**
     * @param $string
     *
     * @return string
     * @deprecated
     */
    public static function normalize($string) {
        return normalize($string);
    }
}
