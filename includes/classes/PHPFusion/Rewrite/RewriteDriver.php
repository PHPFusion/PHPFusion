<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: RewriteDriver.php
| Author: Ankur Thakur
| Co-Author: Takács Ákos (Rimelek)
| Co-Author: Frederick MC Chan (Hien)
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

    protected static $instance = NULL;

    /**
     * Array of Handlers
     * example: news, threads, articles
     * @data_type Array
     * @access protected
     */
    protected $handlers = array();

    /**
     * Array of Total Queries which were run.
     * @data_type Array
     * @access protected
     */
    protected $queries = array();

    /**
     * The site render HTML buffer which is to be scanned
     * @data_type string
     * @access protected
     */

    protected $output = "";

    /**
     * Tags for the permalinks.
     * example: %thread_id%, %news_id%
     * @data_type Array
     * @access protected
     */

    protected $rewrite_code = array();

    /**
     * Replacement for Tags for REGEX.
     * example: %thread_id% should be replaced with ([0-9]+)
     * @data_type Array
     * @access protected
     */
    protected $rewrite_replace = array();

    /**
     * Array of DB Table Names
     * example: prefix_news, prefix_threads, prefix_articles
     * @data_type Array
     * @access protected
     */
    protected $dbname = array();

    /**
     * Array of Unique IDs and its
     * corresponding Tags.
     * Example: news_id is Unique in DB_NEWS
     * and %news_id% is URL is to be treated as news_id
     * So, Array is: array("%news_id%" => "news_id")
     * @data_type Array
     * @access protected
     */
    protected $dbid = array();

    /**
     * Array of Other Columns which
     * can be fetched and used in the
     * URL.
     * Example: If we want to including user_name
     * then Array will look like: array("%user_name%" => "user_name")
     * @data_type Array
     * @access protected
     */
    protected $dbinfo = array();

    /**
     * Array of Pattern for Aliases
     * which are made for matching.
     * @data_type Array
     * @access protected
     */
    protected $alias_pattern = array();

    /**
     * Permalink Patterns which will be searched
     * to match against current request.
     * @data_type Array
     * @access protected
     */
    protected $pattern_search = array();

    /**
     * Target URLs to which permalink request
     * will be rewrited.
     * @data_type Array
     * @access protected
     */
    protected $pattern_replace = array();

    /**
     * Array of Data fetched from the DB Tables
     * It contains the Data in the structured form.
     * @data_type Array
     * @access protected
     */
    protected $data_cache = array();

    /**
     * The Unique ID parameter of the driver file
     * @var array
     */
    protected $id_cache = array();

    /**
     * Array of Regular Expressions Patterns
     * which are made for matching.
     * @data_type Array
     * @access protected
     */
    protected $patterns_regex = array();

    protected $aliases = array();

    /**
     * Statements are calculation results of Rewrite scan
     * We will have various types of regex statements
     * This is the results data of the entire permalink success/fails
     * @var array
     */
    protected $regex_statements = array();

    /**
     * Portion of the URL to match in the Regex
     * @data_type String
     * @access protected
     */
    protected $requesturi = "";

    /**
     * Array of Warnings
     * @data_type Array
     * @access protected
     */
    protected $warnings = array();


    /**
     * Get the instance of the class
     * @return static
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Adds the Handler in the Queue
     * This will Add a Handler which is to be used. This function is called by the
     * add_seo_handler($name) defined in the output_handling_include.php
     * Example: AddHandler("news") will allow us to fetch information from
     * news_rewrite_include.php
     * @param string $handler Name of Handler.
     * @access public
     */
    public function AddHandler($handler) {
        if (!empty($handler) and !in_array($handler, $this->handlers)) {
            $this->handlers[] = $handler;
        }
    }

    /**
     * Import Handlers from Database
     *
     * This will import all the Enabled Handlers from the Database Table
     *
     * @access protected
     */
    protected function importHandlers() {
        $query = "SELECT rewrite_name FROM ".DB_PERMALINK_REWRITE;
        $result = dbquery($query);
        $this->queries[] = $query;
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $this->AddRewrite($data['rewrite_name']);
            }
        }
    }

    /**
     * Include the rewrite include file
     *
     * The include file will be included from
     * INCLUDES."rewrites/".PREFIX."_rewrite_include.php
     *
     * @access protected
     */
    protected function includeRewrite() {
        if (!empty($this->handlers)) {
            foreach ($this->handlers as $key => $name) {
                if (file_exists(BASEDIR."includes/rewrites/".$name."_rewrite_include.php")) {
                    // If the File is found, include it
                    include_once BASEDIR."includes/rewrites/".$name."_rewrite_include.php";
                    if (isset($regex) && is_array($regex)) {
                        $this->addRegexTag($regex, $name);
                        unset($regex);
                    }
                    if (isset($dbname)) {
                        $this->addDbname($dbname, $name);
                        unset($dbname);
                    }
                    if (isset($dbid) && is_array($dbid)) {
                        $this->addDbid($dbid, $name);
                        unset($dbid);
                    }
                    if (isset($dbinfo) && is_array($dbinfo)) {
                        $this->addDbinfo($dbinfo, $name);
                        unset($dbinfo);
                    }
                } else {
                    $this->setWarning(4, $name."_rewrite_include.php");
                }
            }
        }
    }

    /**
     * Add the rewrite include file to be included
     *
     * This will Add new rewrite include file to be included.
     *
     * @param string $include_prefix Prefix of the file to be included.
     * @access protected
     */
    private function AddRewrite($include_prefix) {
        // Include the include_rewrite_include.php file
        if ($include_prefix != "" && !in_array($include_prefix, $this->handlers)) {
            $this->handlers[] = $include_prefix;
        }
    }

    /**
     * Verify Handlers
     *
     * This will verify all the added Handlers by checking if they are enabled
     * or not. The Disabled Handlers are removed from the List and only Enabled
     * Handlers are kept for working.
     */
    protected function verifyHandlers() {
        if (!empty($this->handlers)) {
            $types = array();
            foreach ($this->handlers as $key => $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }
            $types_str = implode(",", $types);
            $query = "SELECT rewrite_name FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name IN(".$types_str.")";
            $this->queries[] = $query;
            $result = dbquery($query);
            $types_enabled = array();
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
     * Include the Handlers
     *
     * This function will include the neccessary files for the Handler and call
     * the functions to manipulate the information from the Handler files.
     */
    protected function includeHandlers() {
        if (is_array($this->handlers) && !empty($this->handlers)) {
            foreach ($this->handlers as $key => $name) {
                if (file_exists(BASEDIR."includes/rewrites/".$name."_rewrite_include.php")) {
                    // If the File is found, include it
                    include BASEDIR."includes/rewrites/".$name."_rewrite_include.php";
                    if (isset($regex) && is_array($regex)) {
                        $this->addRegexTag($regex, $name);
                        unset($regex);
                    }
                    if (isset($dbname)) {
                        $this->addDbname($dbname, $name);
                        unset($dbname);
                    }
                    if (isset($dbid) && is_array($dbid)) {
                        $this->addDbid($dbid, $name);
                        unset($dbid);
                    }
                    if (isset($dbinfo) && is_array($dbinfo)) {
                        $this->addDbinfo($dbinfo, $name);
                        unset($dbinfo);
                    }
                } else {
                    $this->setWarning(4, $name."_rewrite_include.php");
                }
            }
        }
    }

    /**
     * Adds the DB Table Name into the DB_Names array
     *
     * This will Add DB Table Names into the array, which are further used in MySQL Query.
     *
     * @param string $dbname Name of the Table
     * @param string $type Type or Handler name
     * @access protected
     */
    protected function addDbname($dbname, $type) {
        $this->dbname[$type] = $dbname;
    }

    /**
     * Adds the Unique ID information from the handler
     *
     * This will Add the Unique ID Info from the handler, which will be further used in WHERE condition
     * for MySQL Query.
     * Example: array("%news_id%" => "news_id")
     *
     * @param array  $dbid Array of Info
     * @param string $type Type or Handler name
     * @access protected
     */
    protected function addDbid($dbid, $type) {
        $this->dbid[$type] = $dbid;
    }


    /**
     * Adds the other Column names from the handler
     *
     * This will Add other column names, which will be fetched from DB, in the array. These columns will
     * be fetched further in MySQL Query.
     * Example: array("%news_title%" => "news_subject")
     *
     * @param array  $dbinfo Array of Column Info
     * @param string $type Type or Handler name
     * @access protected
     */
    protected function addDbinfo($dbinfo, $type) {
        $this->dbinfo[$type] = $dbinfo;
    }

    /**
     * Adds the Regular Expression Tags
     *
     * This will Add Regex Tags, which will be replaced in the
     * search patterns.
     * Example: %news_id% could be replaced with ([0-9]+) as it must be a number.
     *
     * @param array  $regex Array of Tags to be added.
     * @param string $type Type or Handler name
     * @access protected
     */
    protected function addRegexTag($regex, $type) {
        foreach ($regex as $reg_search => $reg_replace) {
            $this->rewrite_code[$type][] = $reg_search;
            $this->rewrite_replace[$type][] = $reg_replace;
        }
    }

    /**
     * Set Warnings
     *
     * This function will set Warnings. It will set them by Adding them into
     * the $this->warnings array.
     *
     * @param integer $code The Code Number of the Warning
     * @param string  $info Any other Info to Show along with Warning
     * @access protected
     */
    protected function setWarning($code, $info = "") {
        $info = ($info != "") ? $info." : " : "";
        $warnings = array(
            1 => "No matching Alias found.", 2 => "No matching Alias Pattern found.",
            3 => "No matching Regex pattern found.", 4 => "Rewrite Include file not found.",
            5 => "Tag not found in the pattern.", 6 => "File path is empty.", 7 => "Alias found.",
            8 => "Alias Pattern found.", 9 => "Regex Pattern found."
        );
        if ($code <= 6) {
            $this->warnings[] = "<span style='color:#ff0000;'>".$info.$warnings[$code]."</span>";
        } else {
            $this->warnings[] = "<span style='color:#009900;'>".$info.$warnings[$code]."</span>";
        }
    }



    /**
     * Get the Field of the Unique ID type
     *
     * Example: For news, unique ID should be news_id
     * So it will return news_id because of array("%%news_id" => "news_id")
     *
     * @param string $type Type or Handler name
     * @access protected
     */
    protected function getUniqueIDfield($type) {
        $field = "";
        if (isset($this->dbid[$type]) && is_array($this->dbid[$type])) {
            $res   = array_values($this->dbid[$type]); // keys or values????
            $field = $res[0];
        }

        return $field;
    }


    /**
     * Calculates the Tag Position in a given pattern.
     *
     * This function will calculate the position of a given Tag in a given pattern.
     * Example: %id% is at 2 position in articles-%title%-%id%
     *
     * @param string $pattern The Pattern string in which particular Tag will be searched.
     * @param string $search  The Tag which will be searched.
     * @access protected
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
     * Get Other Tags
     * This function will Search for the matching patterns in the current output. If the
     * match(es) are found, it will find the correct tags required under the Rule.
     * Works with replaceOtherTags
     */
    protected function getOtherTags() {
        if (is_array($this->patterns_regex)) {
            foreach ($this->patterns_regex as $handler => $values) {
                if (is_array($this->patterns_regex[$handler])) {

                    // $handler refers to the Patterns type, i.e, news, threads, articles, etc

                    foreach ($this->patterns_regex[$handler] as $key => $search) {
                        // As sniffPatterns is use to Detect ID to fetch Data from DB, so we will not use it for types who have no DB_ID
                        if (isset($this->dbid[$handler])) {
                            // If current Pattern is found in the Output, then continue.
                            if (preg_match($search, $this->output)) {
                                // Store all the matches into the $matches array
                                preg_match_all($search, $this->output, $matches);
                                //@todo: Develop this to return array to decrease driver files - 'blog_id', and 'blog_cat_id'
                                $clean_tag = $this->getUniqueIDfield($handler); // "blog_id"

                                // +1 because Array key starts from 0 and matches[0] gives the complete match
                                // Get the position of that unique DBID from the pattern in order to get value from the $matches
                                $pos       = $this->getTagPosition($this->pattern_search[$handler][$key], $clean_tag);
                                if ($pos != 0) {

                                    //$found_matches = $matches[$pos];
                                    $found_matches = array_unique($matches[$pos]); // This is to remove duplicate matches

                                    // Each Match is Added into the Array
                                    // Example: $this->id_cache[news][news_id][] = $match;
                                    foreach ($found_matches as $mkey => $match) {

                                        $this->CacheInsertID($handler, $match);

                                    }

                                    unset($found_matches);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($this->id_cache)) {
            /**
             * Query Reduction
             */
            if (is_array($this->id_cache)) {
                foreach ($this->id_cache as $handler => $id_val_arr) {
                    if (is_array($this->id_cache[$handler])) {
                        foreach ($this->id_cache[$handler] as $field => $values) {
                            $this->id_cache[$handler][$field] = array_unique($this->id_cache[$handler][$field]);
                        }
                    }
                }
            }

            $loop_count = 0;
            /**
             * Blog Loop - 122 loops  - just to get what is %blog_subject%,
             */
            foreach ($this->id_cache as $handler => $column_name) { // Example: news => news_id
                foreach ($column_name as $name => $items) { // Example: news_id => array(1,3,5,6,7)
                    // We will only fetch the Data which is in the pattern
                    // This is to Ignore fetching the data that we do not want
                    $column_arr = array();
                    /*
                     * var Rewrite_code
                     * [blog] => Array
                                (
                                    [0] => %blog_id% (tag)
                                    [1] => %blog_title%
                                    [2] => %blog_step%
                                    [3] => %blog_rowstart%
                                    [4] => %c_start%
                                    [5] => %blog_year%
                                    [6] => %blog_month%
                                    [7] => %author%
                                    [8] => %type%
                                )
                     */
                    foreach ($this->rewrite_code[$handler] as $key => $tag) { // Example: news_id => array("%news_id%", "%news_title%")
                        /**
                         * var Pattern_replace
                         * Array
                        (
                            [blog] => Array
                            (
                                [0] => blogs/%c_start%/%blog_id%/%blog_title% (pattern)
                                [1] => /php-fusion/blogs/%blog_id%/%blog_title%
                                [2] => blogs/%blog_id%/%blog_title%#comments
                                [3] => blogs/archive/%blog_year%/%blog_month%
                                [4] => blogs
                                [5] => blogs/most-commented
                                [6] => print/%type%/%blog_id%/%blog_title%
                                [7] => blogs/%blog_id%/%blog_title%#ratings
                                [8] => blogs/author/%author%
                                [9] => blogs/%blog_id%/%blog_title%
                                [10] => blogs/most-rated
                                [11] => blogs/most-recent
                            )
                        )
                         */
                        foreach ($this->pattern_replace[$handler] as $key1 => $pattern) {
                            // We check if the Tag exist in the Pattern
                            // if Yes, then Find the suitable Column_name in the DB for that Tag.
                            //print_p("$pattern, $tag");
                            // pattern -- blogs/archive/%blog_year%/%blog_month%, tag -- %blog_title%
                            if (strstr($pattern, $tag)) {
                                if (isset($this->dbinfo[$handler]) && array_key_exists($tag, $this->dbinfo[$handler])) {

                                    if (!in_array($this->dbinfo[$handler][$tag], $column_arr)) {

                                        $column_arr[] = $this->dbinfo[$handler][$tag];
                                    }
                                }
                            }
                            $loop_count++;
                        }
                        $loop_count++;
                    }

                    //print_p($column_arr);

                    // If there are any Columns to be fetch from Database
                    if (!empty($column_arr)) {
                        $column_arr[]    = $name; // Also fetch the Unique_ID like news_id, thread_id
                        $column_names    = implode(",", $column_arr); // Array to String conversion for MySQL Query
                        $dbname          = $this->dbname[$handler]; // Table Name in Database
                        $unique_col      = $name; // The Unique Column name for WHERE condition
                        $items           = array_unique($items); // Remove any duplicates from the Array
                        $ids_to_fetch    = implode(",", $items); // IDs to fetch data of
                        $fetch_query     = "SELECT ".$column_names." FROM ".$dbname." WHERE ".$unique_col.(count($items) > 1 ? " IN(".$ids_to_fetch.")" : "='".$ids_to_fetch."'"); // The Query
                        $result          = dbquery($fetch_query); // Execute Query
                        $this->queries[] = $fetch_query;
                        if (dbrows($result)) {
                            while ($data = dbarray($result)) {
                                foreach ($column_arr as $key => $col_name) {
                                    $this->data_cache[$handler][$data[$unique_col]][$col_name] = $data[$col_name];
                                    $loop_count++;
                                }
                                $loop_count++;
                            }
                        }
                    }
                    $loop_count++;
                }
                $loop_count++;
            }
            print_p($loop_count);
        }
    }


    /**
     * Replace Other Tags in Pattern
     *
     * This function will replace all the Tags in the Pattern with their suitable found
     * matches. All the Information is passed to the function and it will replace the
     * Tags with their respective matches.
     * @param string $type     Type of Pattern
     * @param string $search   specific Search Pattern
     * @param string $replace  specific Replace Pattern
     * @param array  $matches  Array of the Matches found for a specific pattern
     * @param string $matchkey A Unique matchkey for different matches found for same pattern
     */
    protected function replaceOtherTags($type, $search, $replace, $matches, $matchkey) {
        if (isset($this->rewrite_code[$type])) {
            foreach ($this->rewrite_code[$type] as $other_tags_keys => $other_tags) {
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
        return $replace;
    }

    /**
     * Append the BASEDIR Path to Search String
     *
     * This function will append the BASEDIR path to the Search pattern. This is
     * required in some cases like when we are on actual php script page and
     * Permalinks are ON.
     * @param string $str The String
     * @access protected
     */
    protected function appendSearchPath($str) {
        static $base_files = array();
        if (empty($base_files)) {

            $base_files = makefilelist(BASEDIR, ".|..");

        }
        foreach ($base_files as $files) {
            if (stristr($str, $files)) {
                return $str;
            }
        }
        $str = BASEDIR.$str;
        return $str;
    }



    /**
     * Clean the REGEX by escaping some characters
     *
     * This function will escape some characters in the Regex expression
     * @param string $regex The expression String
     */
    protected static function cleanRegex($regex) {
        $regex = str_replace("/", "\/", $regex);
        $regex = str_replace("#", "\#", $regex);
        $regex = str_replace(".", "\.", $regex);
        $regex = str_replace("?", "\?", $regex);
        return (string) $regex;
    }


    /**
     * Wrap a String with Single Quotes (')
     * This function will wrap a string passed with Single Quotes.
     * Example: mystring will become 'mystring'
     * @param string $str The String
     * @access protected
     */
    protected static function wrapQuotes($str) {
        $rep = $str;
        $rep = "'".$rep."'";
        return (string) $rep;
    }



    /**
     * Get the Field of the Unique ID type
     * Example: For news, unique ID should be news_id
     * So it will return news_id because of array("%%news_id" => "news_id")
     * @param string $type Type or Handler name
     */
    protected function CacheInsertID($type, $value) {
        $field = $this->getUniqueIDfield($type);

        $this->id_cache[$type][$field][] = $value;
    }


    /**
     * Get the Tag of the Unique ID type
     *
     * Example: For news, unique ID should be news_id
     * So it will return %news_id% because of array("%%news_id" => "news_id")
     *
     * @param string $type Type or Handler name
     * @access protected
     * @todo: Roadmap 9.1 to have this read seperately
     */
    protected function getUniqueIDtag($type) {
        $tag = "";
        if (isset($this->dbid[$type]) && is_array($this->dbid[$type])) {
            $res = array_keys($this->dbid[$type]);
            $tag = $res[0];
        }
        return (string) $tag;
    }

    /**
     * Adds the Regular Expression Tags -- for permalink search regex
     *
     * This will Add Regex Tags, which will be replaced in the
     * search patterns.
     * Example: %news_id% could be replaced with ([0-9]+) as it must be a number.
     *
     * @param array $regex Array of Tags to be added.
     * @param string $type Type or Handler name
     * @access protected
     */
    protected function makeSearchRegex($pattern, $type) {
        $regex = $pattern;

        $regex = $this->cleanRegex($regex);

        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
        }

        $regex = $this->wrapQuotes($regex);

        $regex = "~".$regex."~i";

        return (string) $regex;
    }

    /**
     * Cleans the URL
     *
     * This function will clean the URL by removing any unwanted characters from it and
     * only allowing alphanumeric and - in the URL.
     * This function can be customized according to your needs.
     *
     * @param string $string The URL String
     * @access protected
     */
    protected static function cleanURL($string, $delimiter = "-") {
        if (fusion_get_settings("normalize_seo") == "1") {
            $string = self::normalize($string);
            if (function_exists('iconv')) {
                $string = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $string);
            }
        }
        $string = preg_replace("/&([^;]+);/i", "", $string); // Remove all Special entities like ', &#copy;

        //$string = preg_replace("/[^+a-zA-Z0-9_.\/#|+ -\W]/i", "",$string); // # is allowed in some cases(like in threads for #post_10)

        $string = preg_replace("/[\s]+/i", $delimiter, $string); // Replace All <space> by Delimiter

        $string = preg_replace("/[\\".$delimiter."]+/i", $delimiter, $string); // Replace multiple occurences of Delimiter by 1 occurence only

        $string = trim($string, "-");

        return (string) $string;
    }

    /**
     * Clean the URI String for MATCH/AGAINST in MySQL
     *
     * This function will Clean the string and removes any unwanted characters from it.
     * @access protected
     */
    private function cleanString($mystr = "") {
        $search = array("&", "\"", "'", "\\", "\'", "<", ">");
        $res    = str_replace($search, "", $mystr);

        return $res;
    }

    /**
     * Replaces special characters in a string with their "non-special" counterpart.
     * Useful for friendly URLs.
     * @access public
     * @param string
     * @return string
     */
    protected static function normalize($string) {
        $table  = array(
            '&amp;' => 'and', '@' => 'at', '©' => 'c', '®' => 'r', 'À' => 'a',
            'Á'     => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae', 'Ç' => 'c',
            'È'     => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
            'Ï'     => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
            'Ø'     => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
            'ß'     => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
            'æ'     => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì'     => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
            'ô'     => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
            'û'     => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a',
            'ā'     => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c',
            'ć'     => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c',
            'č'     => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e',
            'ē'     => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e',
            'ę'     => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g',
            'ğ'     => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h',
            'ĥ'     => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i',
            'ī'     => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i',
            'ı'     => 'i', 'Ĳ' => 'ij', 'ĳ' => 'ij', 'Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k',
            'ķ'     => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l',
            'Ľ'     => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
            'Ń'     => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n',
            'ŉ'     => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o',
            'ŏ'     => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe', 'œ' => 'oe', 'Ŕ' => 'r',
            'ŕ'     => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's',
            'ś'     => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's',
            'š'     => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't',
            'ŧ'     => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u',
            'ŭ'     => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u',
            'ų'     => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y',
            'Ź'     => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z',
            'ſ'     => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u',
            'ư'     => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o',
            'ǒ'     => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u',
            'ǘ'     => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a',
            'ǻ'     => 'a', 'Ǽ' => 'ae', 'ǽ' => 'ae', 'Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e',
            'Ё'     => 'jo', 'Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b',
            'В'     => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh', 'З' => 'z',
            'И'     => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
            'О'     => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u',
            'Ф'     => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sch',
            'Ъ'     => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je', 'Ю' => 'ju', 'Я' => 'ja',
            'а'     => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ж'     => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l',
            'м'     => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
            'т'     => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш'     => 'sh', 'щ' => 'sch', 'ъ' => '-', 'ы' => 'y', 'ь' => '-', 'э' => 'je',
            'ю'     => 'ju', 'я' => 'ja', 'ё' => 'jo', 'є' => 'e', 'і' => 'i', 'ї' => 'i',
            'Ґ'     => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd',
            'ה'     => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i',
            'ך'     => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n',
            'נ'     => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C',
            'צ'     => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
        );
        $string = strtr($string, $table);
        return (string)$string;
    }

    /**
     * Redirect 301 : Moved Permanently Redirect
     * This function invoked to prevent of caching any kinds of Non SEO URL on render.
     * Let search engine mark as 301 permanently
     * @param string $target The Target URL
     * @access protected
     */
    protected static function redirect_301($target, $debug = FALSE) {
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
}