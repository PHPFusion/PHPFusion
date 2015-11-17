<?php
/**-------------------------------------------------------+
 * | PHP-Fusion Content Management System
* | Copyright (C) 2002 - 2011 Nick Jones
* | https://www.php-fusion.co.uk/
* +--------------------------------------------------------+
* | Filename: PermalinksDisplay.php
* | Author: Ankur Thakur
* | Co-Author: Takács Ákos (Rimelek)
* +--------------------------------------------------------+
* | This program is released as free software under the
* | Affero GPL license. You can redistribute it and/or
* | modify it under the terms of this license which you
* | can read by viewing the included agpl.txt or online
* | at www.gnu.org/licenses/agpl.html. Removal of this
* | copyright header is strictly prohibited without
* | written permission from the original author(s).
* +--------------------------------------------------------*/
namespace PHPFusion;
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

/**
| Permalinks API for PHP-Fusion
|
| This API handles with the Output of the default PHP-Fusion
| and returns a modified output with Links replaced with
| SEO Links or Permalinks.
|
 */
class PermalinksDisplay {

    /**
    * The Output
    * @data_type String
    * @access private
    */

    private static $instance = NULL;

    /**
    * Array of Handlers
    * example: news, threads, articles
    * @data_type Array
    * @access private
     */

    private $output = "";

    /**
    * Tags for the permalinks.
    * example: %thread_id%, %news_id%
    * @data_type Array
    * @access private
    */

    private $handlers = array();

    /**
    * Replacement for Tags for REGEX.
    * example: %thread_id% should be replaced with ([0-9]+)
    * @data_type Array
    * @access private
    */

    private $rewrite_code = array();

    /**
    * Array of Pattern for Aliases
    * which are made for matching.
    * @data_type Array
    * @access private
    */

    private $rewrite_replace = array();

    /**
    * Permalink Patterns which will be searched
    * to match against current request.
    * @data_type Array
    * @access private
    */

    private $alias_pattern = array();

    /**
    * Target URLs to which permalink request
    * will be rewrited.
    * @data_type Array
    * @access private
    */

    private $pattern_search = array();

    /**
    * Array of Regular Expressions Patterns
    * which are made for matching.
    * @data_type Array
    * @access private
    */

    private $pattern_replace = array();

    /**
    * Array of DB Table Names
    * example: prefix_news, prefix_threads, prefix_articles
    * @data_type Array
    * @access private
    */

    private $patterns_regex = array();

    /**
    * Array of Unique IDs and its
    * corresponding Tags.
    * Example: news_id is Unique in DB_NEWS
    * and %news_id% is URL is to be treated as news_id
    * So, Array is: array("%news_id%" => "news_id")
    * @data_type Array
    * @access private
    */

    private $dbname = array();

    /**
    * Array of Other Columns which
    * can be fetched and used in the
    * URL.
    * Example: If we want to including user_name
    * then Array will look like: array("%user_name%" => "user_name")
    * @data_type Array
    * @access private
    */

    private $dbid = array();

    /**
    * Array of Data fetched from the DB Tables
    * It contains the Data in the structured form.
    * @data_type Array
    * @access private
    */

    private $dbinfo = array();

    /**
    * Array of Unique IDs, of which the Data is to
    * be fetched.
    * Example: Fetch Data for user_id IN(1,3,4,9)
    * @data_type Array
    * @access private
    */

    private $data_cache = array();

    /**
    * Array of Total Queries which were run.
    * @data_type Array
    * @access private
    */

    private $id_cache = array();

    /**
    * Array of Aliases and their Info
    * which are retrieved from DB.
    * It is used further in 301 Redirect.
    * @data_type Array
    * @access private
    */

    private $queries = array();

    /**
    * Debug Enable or Not
    * @data_type Boolean
    * @access protected
    */

    private $aliases = array();

    /** for debugging purposes */

    private $pattern_replacement_results = array();
    private $alias_replacement_results = array();

    /**
     * Get the instance of the class
     *
     * @return static
     */

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
    * Main Function : Handles the Output
    *
    * This function will Handle the output by calling several functions
    * which are used in this Class.
    *
    * @param string $output The Output from the Fusion
    * @access private
    */

    public function AddHandler($handler) {
        if (!empty($handler) and !in_array($handler, $this->handlers)) {
            $this->handlers[] = $handler;
        }
    }

    /**
    * Adds the Handler in the Queue
    *
    * This will Add a Handler which is to be used. This function is called by the
    * add_seo_handler($name) defined in the output_handling_include.php
    * Example: AddHandler("news") will allow us to fetch information from
    * news_rewrite_include.php
    *
    * @param string $handler Name of Handler.
    * @access public
    */

    public function getOutput($output) {
        $this->handleOutput($output);

        return $this->output;
    }

    /**
    * Verify Handlers
    *
    * This will verify all the added Handlers by checking if they are enabled
    * or not. The Disabled Handlers are removed from the List and only Enabled
    * Handlers are kept for working.
    *
    * @access private
    */

    private function handleOutput($output) {
        $settings = \fusion_get_settings();

        // Sets the Output
        $this->output = $output;
        // verify Handlers
        $this->verifyHandlers();
        // Include the files for handlers
        $this->includeHandlers();
        // Import Patterns
        $this->importPatterns();
        // Make Regex Patterns for the URL Patterns
        $this->makeRegex();
        // Sniff for the matching patterns
        $this->sniffPatterns();
        $this->serializeIdCache();
        // Fetch the Data of the matched patterns from the Database
        $this->fetchData();
        // Replace the Alias
        $this->replaceAlias();
        // Replace the other URL Patterns
        $this->replacePatterns();
        // Prepend all the File/Images/CSS/JS etc Links with ROOT path
        $this->appendRootAll();
        // Check if the URI is a PHP File. So we need a 301 Redirect to the Permalink.
        $this->validateURI();
        // For Developer, to see what is happening behind
        if ($settings['debug_seo'] == "1") {
            $this->showQueries();
        }
    }

    /**
    * Include the Handlers
    *
    * This function will include the neccessary files for the Handler and call
    * the functions to manipulate the information from the Handler files.
    *
    * @access private
    */

    private function verifyHandlers() {
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
    * Adds the Regular Expression Patterns
    *
    * This will Add Regular Expression patterns to the Regex Search Patterns
    * and also the Replacement patterns.
    *
    * @access private
    */

    private function includeHandlers() {
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
                }
            }
        }
    }

    /**
    * Remove Duplicates IDs from the ID Cache to minimize DB Query String
    *
    * This will Remove duplicate entried for IDs from the ID Cache so that the
    * MySQL Query string is minimized and is not very much long in length.
    *
    * @access private
    */

    private function addRegexTag($regex, $type) {
        foreach ($regex as $reg_search => $reg_replace) {
            $this->rewrite_code[$type][] = $reg_search;
            $this->rewrite_replace[$type][] = $reg_replace;
        }
    }

    /**
    * Sniff : Search for the patterns in Output
    *
    * This function will Search for the matching patterns in the current output. If the
    * match(es) are found, it will put them into the ID_Cache Array through CacheInsertID()
    *
    * @access private
    */

    private function addDbname($dbname, $type) {
        $this->dbname[$type] = $dbname;
    }

    /**
    * Fetch : Fetch the Data for the matched IDs in the output
    *
    * This function will fetch the required data from the Database, for the matches found.
    * The Columns, which are to be fetched, are defined by developer in include files.
    * The Data will be stored in DATA_Cache Array through CacheInsertDATA()
    *
    * @access private
    */

    private function addDbid($dbid, $type) {
        $this->dbid[$type] = $dbid;
    }

    /**
    * Fetch Data for a specific Type, ID and Pattern
    *
    * This function will fetch specific data on the basis of the Pattern, Type
    * and the unique ID value.
    *
    * @param string $type The Type of Pattern
    * @param string $pattern The Specific Pattern
    * @param string $id Unique ID Value
    * @access private
    */

    private function addDbinfo($dbinfo, $type) {
        $this->dbinfo[$type] = $dbinfo;
    }

    /**
    * Replace : Replace the Patterns in the Output
    *
    * This function will replace the patterns in the current output with the required Replacement links.
    * MADMAN DEBUG MODE HERE - BUG KINGDOM.
    */

    private function importPatterns() {
        if (!empty($this->handlers)) {
            $types = array();
            foreach ($this->handlers as $key => $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }
            $types_str = implode(",", $types);
            $query = "SELECT r.rewrite_name, p.pattern_type, p.pattern_source, p.pattern_target, p.pattern_cat FROM ".DB_PERMALINK_METHOD." p INNER JOIN ".DB_PERMALINK_REWRITE." r WHERE r.rewrite_id=p.pattern_type AND r.rewrite_name IN(".$types_str.") ORDER BY p.pattern_type";
            $this->queries[] = $query;
            $result = dbquery($query);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    if ($data['pattern_cat'] == "normal") {
                        $this->pattern_search[$data['rewrite_name']][] = $data['pattern_target'];
                        $this->pattern_replace[$data['rewrite_name']][] = $data['pattern_source'];
                    } elseif ($data['pattern_cat'] == "alias") {
                        $this->alias_pattern[$data['rewrite_name']][$data['pattern_source']] = $data['pattern_target'];
                    }
                }
            }
        }
    }

    /**
    * Replace Alias : Replace with any Aliases in the Output
    *
    * This function will replace with Aliases found from the Database.
    *
    * @access private
    */

    private function makeRegex() {
        if (is_array($this->pattern_search)) {
            foreach ($this->pattern_search as $type => $values) {
                if (is_array($this->pattern_search[$type])) {
                    foreach ($this->pattern_search[$type] as $key => $val) {
                        $regex = $val;
                        $regex = $this->appendSearchPath($regex);
                        $regex = $this->cleanRegex($regex);
                        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
                            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
                        }
                        $regex = $this->wrapQuotes($regex);
                        $this->patterns_regex[$type][$key] = "~".$regex."~i";
                    }
                }
            }
        }
    }

    /**
    * Replace Alias Pattern : Replace with any Alias Pattern in the Output
    *
    * This function will replace with Alias Pattern if there is any found in the output.
    *
    * @param array $alias Data from the Database of a specific Alias
    * @access private
    */

    private function appendSearchPath($str) {
        $str = BASEDIR.$str;
        return $str;
    }

    /**
    * Replace Other Tags in Pattern
    *
    * This function will replace all the Tags in the Pattern with their suitable found
    * matches. All the Information is passed to the function and it will replace the
    * Tags with their respective matches.
    *
    * @param string $type Type of Pattern
    * @param string $search specific Search Pattern
    * @param string $replace specific Replace Pattern
    * @param array $matches Array of the Matches found for a specific pattern
    * @param string $matchkey A Unique matchkey for different matches found for same pattern
    * @access private
    */

    private function cleanRegex($regex) {
        $regex = str_replace("/", "\/", $regex);
        $regex = str_replace("#", "\#", $regex);
        $regex = str_replace(".", "\.", $regex);
        $regex = str_replace("?", "\?", $regex);

        return $regex;
    }

    /**
    * Validate current URI
    *
    * This function will verifies if the current request is to a existing php file.
    * So we need to make a 301 Redirect to its respective permalink.
    *
    * @access private
    */

    private function wrapQuotes($str) {
        $rep = $str;
        $rep = "'".$rep."'";

        return $rep;
    }

    /**
    * Get Alias URL
    *
    * This function will return an Array of 2 elements for a specific Alias:
    * 1. The Permalink URL of Alias
    * 2. PHP URL of the Alias
    *
    * @param string $url The Permalink URL (incomplete)
    * @param string $php_url The PHP URL (incomplete)
    * @param string $type Type of Alias
    * @access private
    */

    private function sniffPatterns() {
        if (is_array($this->patterns_regex)) {
            foreach ($this->patterns_regex as $type => $values) {
                if (is_array($this->patterns_regex[$type])) {
                    // $type refers to the Patterns type, i.e, news, threads, articles, etc
                    foreach ($this->patterns_regex[$type] as $key => $search) {
                        // As sniffPatterns is use to Detect ID to fetch Data from DB, so we will not use it for types who have no DB_ID
                        if (isset($this->dbid[$type])) {
                            // If current Pattern is found in the Output, then continue.
                            if (preg_match($search, $this->output)) {
                                // Store all the matches into the $matches array
                                preg_match_all($search, $this->output, $matches);
                                // Returns the Tag from the Unique DBID by which the Pattern in recognized, i.e, %news_id%, %thread_id%
                                $tag = $this->getUniqueIDtag($type);
                                $clean_tag = str_replace("%", "", $tag); // Remove % for Searching the Tag
                                // +1 because Array key starts from 0 and matches[0] gives the complete match
                                // Get the position of that unique DBID from the pattern in order to get value from the $matches
                                $pos = $this->getTagPosition($this->pattern_search[$type][$key], $clean_tag);
                                if ($pos != 0) {
                                    $found_matches = array_unique($matches[$pos]); // This is to remove duplicate matches
                                    // Each Match is Added into the Array
                                    // Example: $this->id_cache[news][news_id][] = $match;
                                    foreach ($found_matches as $mkey => $match) {
                                        $this->CacheInsertID($type, $match);
                                    }
                                    unset($found_matches);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
    * mpRedirect : Moved Permanently Redirect
    *
    * This function will redirect to a URL by giving 301 HTTP status.
    *
    * @param string $target The Target URL
    * @access private
    */

    private function getUniqueIDtag($type) {
        $tag = "";
        if (isset($this->dbid[$type]) && is_array($this->dbid[$type])) {
            $res = array_keys($this->dbid[$type]);
            $tag = $res[0];
        }

        return $tag;
    }

    /**
    * Adds the Regular Expression Tags
    *
    * This will Add Regex Tags, which will be replaced in the
    * search patterns.
    * Example: %news_id% could be replaced with ([0-9]+) as it must be a number.
    *
    * @param array $regex Array of Tags to be added.
    * @param string $type Type or Handler name
    * @access private
    */

    private function getTagPosition($pattern, $search) {
        if (preg_match_all("#%([a-zA-Z0-9_]+)%#i", $pattern, $matches)) {
            $key = array_search($search, $matches[1]);

            return intval($key + 1);
        } else {
            return 0;
        }
    }

    /**
    * Adds the DB Table Name into the DB_Names array
    *
    * This will Add DB Table Names into the array, which are further used in MySQL Query.
    *
    * @param string $dbname Name of the Table
    * @param string $type Type or Handler name
    * @access private
    */

    private function CacheInsertID($type, $value) {
        $field = $this->getUniqueIDfield($type);
        $this->id_cache[$type][$field][] = $value;
    }

    /**
     * Debug Function for Developers
     *
     * Just a simple function for the developer to see, what is going in the background.
     *
     * @access private
     */

    private function getUniqueIDfield($type) {
        $field = "";
        if (isset($this->dbid[$type]) && is_array($this->dbid[$type])) {
            $res   = array_values($this->dbid[$type]);
            $field = $res[0];
        }

        return $field;
    }

    /**
    * Adds the Unique ID information from the handler
    *
    * This will Add the Unique ID Info from the handler, which will be further used in WHERE condition
    * for MySQL Query.
    * Example: array("%news_id%" => "news_id")
    *
    * @param array $dbid Array of Info
    * @param string $type Type or Handler name
    * @access private
    */

    private function serializeIdCache() {
        if (is_array($this->id_cache)) {
            foreach ($this->id_cache as $type => $id_val_arr) {
                if (is_array($this->id_cache[$type])) {
                    foreach ($this->id_cache[$type] as $field => $values) {
                        $this->id_cache[$type][$field] = array_unique($this->id_cache[$type][$field]);
                    }
                }
            }
        }
    }

    /**
    * Adds the other Column names from the handler
    *
    * This will Add other column names, which will be fetched from DB, in the array. These columns will
    * be fetched further in MySQL Query.
    * Example: array("%news_title%" => "news_subject")
    *
    * @param array $dbinfo Array of Column Info
    * @param string $type Type or Handler name
    * @access private
    */

    private function fetchData() {
        if (!empty($this->id_cache)) {
            foreach ($this->id_cache as $type => $column_name) { // Example: news => news_id
                foreach ($column_name as $name => $items) { // Example: news_id => array(1,3,5,6,7)
                    // We will only fetch the Data which is in the pattern
                    // This is to Ignore fetching the data that we do not want
                    $column_arr = array();
                    foreach ($this->rewrite_code[$type] as $key => $tag) { // Example: news_id => array("%news_id%", "%news_title%")
                        foreach ($this->pattern_replace[$type] as $key => $pattern) {
                            // We check if the Tag exist in the Pattern
                            // if Yes, then Find the suitable Column_name in the DB for that Tag.
                            if (strstr($pattern, $tag)) {
                                if (isset($this->dbinfo[$type]) && array_key_exists($tag, $this->dbinfo[$type])) {
                                    if (!in_array($this->dbinfo[$type][$tag], $column_arr)) {
                                        $column_arr[] = $this->dbinfo[$type][$tag];
                                    }
                                }
                            }
                        }
                    }
                    // If there are any Columns to be fetch from Database
                    if (!empty($column_arr)) {
                        $column_arr[] = $name; // Also fetch the Unique_ID like news_id, thread_id
                        $column_names = implode(",", $column_arr); // Array to String conversion for MySQL Query
                        $dbname = $this->dbname[$type]; // Table Name in Database
                        $unique_col = $name; // The Unique Column name for WHERE condition
                        $items = array_unique($items); // Remove any duplicates from the Array
                        $ids_to_fetch = implode(",", $items); // IDs to fetch data of
                        $fetch_query = "SELECT ".$column_names." FROM ".$dbname." WHERE ".$unique_col.(count($items) > 1 ? " IN(".$ids_to_fetch.")" : "='".$ids_to_fetch."'"); // The Query
                        $result = dbquery($fetch_query); // Execute Query
                        $this->queries[] = $fetch_query;
                        if (dbrows($result)) {
                            while ($data = dbarray($result)) {
                                foreach ($column_arr as $key => $col_name) {
                                    $this->CacheInsertDATA($type, $data[$unique_col], $col_name, $data[$col_name]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the Output
     *
     * This function will first call the handleOutput() and then it will return the
    * modified Output for SEO.
     *
     * @param string $ouput The Output
     * @access public
     */

    private function CacheInsertDATA($type, $unique_id, $column, $value) {
        if (!isset($this->data_cache[$type][$unique_id])) {
            $this->data_cache[$type][$unique_id][$column] = $value;
        }
    }

    /**
     * Inserts the matched Unique ID info into ID_Cache Array
     *
     * This will Insert the Unique IDs info into the ID_Cache Array which will be further used to distinguish
     * matches and items. These matches also helps in fetching info for different matches from DB.
     * Example: 1,2,3,8,9 as user_id or news_id
     *
     * @param array $value Array of matches
    * @param string $type Type or Handler name
     * @access private
     */

    private function replaceAlias() {
        if (!empty($this->handlers)) {
            // Joining Handlers for Query
            $types = array();
            foreach ($this->handlers as $key => $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }
            $handlers        = implode(",", $types);
            $query           = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_type IN(".$handlers.")";
            $this->queries[] = $query;
            $aliases         = dbquery($query); // Execute Query
            if (dbrows($aliases)) {
                while ($data = dbarray($aliases)) {
                    // Replacing the current static Alias
                    /** $search = $data['alias_php_url'];
                    $search = $this->appendSearchPath($search);
                    $search = $this->makeSearchRegex($search, $data['alias_type']);
                    $replace = $data['alias_url'];
                    $replace = $this->wrapQuotes($replace); */

                    // Now replacing any patterns related to this Alias
                    $this->replaceAliasPatterns($data);
                    // We are replacing Alais after Alias pattern because patterns must be replaced first due to their High priority
                    //$this->output = preg_replace($search, $replace, $this->output);
                    $this->aliases[] = $data;
                }
            }
        }
    }

    /**
    * Get the Tag of the Unique ID type
    *
    * Example: For news, unique ID should be news_id
    * So it will return %news_id% because of array("%%news_id" => "news_id")
    *
    * @param string $type Type or Handler name
    * @access private
    */

    private function replaceAliasPatterns($alias) {
        // Set the Type
        $type = $alias['alias_type'];
        // Check If there are any Alias Patterns defined for this Type or not
        if (array_key_exists($type, $this->alias_pattern)) {
            foreach ($this->alias_pattern[$type] as $replace => $search) {
                // Secondly, Replace %alias_target% with Alias PHP URL
                $search = str_replace("%alias_target%", $alias['alias_php_url'], $search);
                $search_string = $search;
                // Now Replace Pattern Tags with suitable Regex Codes
                //$search = $this->makeSearchRegex($this->appendDirPath($search),$type);
                $search = $this->makeSearchRegex($search, $type);
                // If the Pattern is found in the Output
                if (preg_match($search, $this->output)) {
                    // Search them all and put them in $matches
                    preg_match_all($search, $this->output, $matches);
                    // $matches[0] represents the Array of all the matches for this Pattern
                    foreach ($matches[0] as $count => $match) {
                        // First of all, Replace %alias% with the actual Alias Name
                        $replace_str = str_replace("%alias%", $alias['alias_url'], $replace);
                        $match = $this->cleanRegex($match);
                        // Replace Tags with their suitable matches
                        $replace_str = $this->replaceOtherTags($type, $search_string, $replace_str, $matches, $count);
                        //echo $replace_str."<br />";
                        //echo $search."<br />";
                        //echo $match."<br />";
                        // Replacing the current match with suitable Replacement in Output
                        $this->output = preg_replace("~".$match."~i", $this->wrapQuotes($replace_str), $this->output);
                    }
                }
            }
        }
    }

    /**
     * Inserts the Data into the DATA_Cache array
     *
     * This will Insert the Data fetched from the DB into the DATA_Cache array. The columns data will
     * be stored in form of array.
     * Example: [1] => Array(
     * [news_id] => 1,
     * [news_subject] => Hello. I am Ankur.
     * )
     *
     * @param string $unique_id Represents the Unique ID, of the Info. (It is 1 in the above example)
     * @param string $column    Column Name of the data (news_subject etc)
     * @param string $value     Value of the Column or the Data to be stored
    * @param string $type Type or Handler name
     * @access private
     */

    private function makeSearchRegex($pattern, $type) {
        $regex = $pattern;
        $regex = $this->cleanRegex($regex);
        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
        }
        $regex = $this->wrapQuotes($regex);
        $regex = "~".$regex."~i";

        return $regex;
    }

    /**
     * Get the Field of the Unique ID type
     *
     * Example: For news, unique ID should be news_id
     * So it will return news_id because of array("%%news_id" => "news_id")
     *
     * @param string $type Type or Handler name
     * @access private
     */

    private function replaceOtherTags($type, $search, $replace, $matches, $matchkey) {
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

        //print_p($replace); // final output
        return $replace;
    }

    /**
    * Calculates the Tag Position in a given pattern.
     *
     * This function will calculate the position of a given Tag in a given pattern.
     * Example: %id% is at 2 position in articles-%title%-%id%
     *
     * @param string $pattern The Pattern string in which particular Tag will be searched.
     * @param string $search The Tag which will be searched.
     * @access private
     */

    private function replacePatterns() {
        if (is_array($this->pattern_search)) {
            foreach ($this->pattern_search as $type => $values) {
                if (is_array($this->patterns_regex[$type])) {
                    foreach ($this->patterns_regex[$type] as $key => $search) {
                        // If the Regex Pattern is found in the Output, then continue
                        if (preg_match($search, $this->output)) {
                            $this->pattern_replacement_results[] = array(
                                "search_regex" => $search,
                                "status"       => "Success",
                            );
                            // Store all the Matches in the $matches array
                            preg_match_all($search, $this->output, $matches);
                            // Replace the Unique ID Tag with the Regex Code
                            // Example: Replace %news_id% with ([0-9]+)
                            if (isset($this->dbid[$type])) {
                                //foreach ($this->dbid[$type] as $tag=>$attr) {
                                $tag       = $this->getUniqueIDtag($type);
                                $attr      = $this->getUniqueIDfield($type);
                                $clean_tag = str_replace("%", "", $tag); // Remove % for Searching the Tag
                                // +1 because Array key starts from 0 and matches[0] gives the complete match
                                $pos = $this->getTagPosition($this->pattern_search[$type][$key],
                                    $clean_tag); // Get Position of Unique Tag
                                if ($pos != 0) {
                                    $found_matches = $matches[$pos]; // This is to remove duplicate matches
                                    foreach ($found_matches as $matchkey => $match) {
                                        $replace = $this->pattern_replace[$type][$key]; // replace pattern
                                        // Replacing each Tag with its Database Value if any
                                        // Example: %thread_title% should be replaced with thread_subject
                                        if (isset($this->dbinfo[$type])) { // have output return from dbquery based on id : [%forum_name%] => forum_name
                                            foreach ($this->dbinfo[$type] as $other_tags => $other_attr) {
                                                if (strstr($replace, $other_tags)) {
                                                    $replace = str_replace($other_tags,
                                                        $this->data_cache[$type][$match][$other_attr],
                                                        $replace);
                                                }
                                            }
                                        }
                                        // Replacing each of the Tag with its suitable match found on the Page - Suitable becomes non-suitable if you put Pagenav inside a DBID Type.
                                        // Every page nav becomes identical!
                                        $replace = $this->replaceOtherTags($type, $this->pattern_search[$type][$key],
                                            $replace, $matches, $matchkey);
                                        $search  = str_replace($tag, $match, $this->pattern_search[$type][$key]);
                                        // this might be the culprit in not making the navigation other tag work and replication.
                                        $search = $this->replaceOtherTags($type, $this->pattern_replace[$type][$key], $search, $matches, $matchkey); // BUG This will stop &amp; parsing ! Added: Replace Tags values in Search Pattern Also
                                        $search = $this->makeSearchRegex($this->appendSearchPath($search), $type);
                                        //print_p($replace);
                                        $replace = self::cleanURL($replace);
                                        $replace = $this->wrapQuotes($replace);
                                        // REPLACE IN OUTPUT
                                        $this->output = preg_replace($search, $replace, $this->output);
                                    }
                                }
                                //}
                                // Also replace the Normal Page (Example: news.php --> news)
                                // If pattern contain No tags, then this will be executed by default
                                $this->output = preg_replace($search,
                                    $this->wrapQuotes($this->pattern_replace[$type][$key]),
                                    $this->output);
                            } else {
                                // If it is a Normal Pattern to Replace with corresponding matches
                                // then for each Match, replace the Tags with their suitable matches.
                                foreach ($matches[0] as $count => $match) {
                                    $match = $this->cleanRegex($match);
                                    // Replace Tags with their suitable matches
                                    $replace = $this->replaceOtherTags($type, $this->pattern_search[$type][$key],
                                        $this->pattern_replace[$type][$key], $matches,
                                        $count);
                                    // Replacing the current match with suitable Replacement in Output
                                    $this->output = preg_replace("~".$match."~i", $this->wrapQuotes($replace),
                                        $this->output);
                                }
                            }
                        } else {
                            $this->pattern_replacement_results[] = array(
                                "search_regex" => $search,
                                "status"       => "Failed",
                            );
                        }
                    }
                }
            }
        }
        print_p($this->pattern_replacement_results);
    }

    /**
     * Builds the Regular Expressions Patterns
     *
     * This function will create the Regex patterns and will put the built patterns
     * in $patterns_regex array. This array will then used in preg_match function
     * to match against current request.
     *
     * @access private
     */

    public static function cleanURL($string, $delimiter = "-") {
        $string = self::normalize($string);
        $string = preg_replace("/&([^;]+);/i", "", $string); // Remove all Special entities like ', &#copy;
        //$string = preg_replace("/[^+a-zA-Z0-9_.\/#|+ -\W]/i", "",$string); // # is allowed in some cases(like in threads for #post_10)
        $string = preg_replace("/[\s]+/i", $delimiter, $string); // Replace All <space> by Delimiter
        $string = preg_replace("/[\\".$delimiter."]+/i", $delimiter,
            $string); // Replace multiple occurences of Delimiter by 1 occurence only
        $string = strtolower(trim($string, "-"));

        return $string;
    }

    /**
     * Replaces special characters in a string with their "non-special" counterpart.
     *
     * Useful for friendly URLs.
     *
     * @access public
     * @param string
     * @return string
     */
    private function normalize($string) {

        if (fusion_get_settings("normalize_seo") == 1) {

            $table = array(
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
                'Ґ'     => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd', 'ה' => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i', 'ך' => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n', 'נ' => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C', 'צ' => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
            );

            $string = strtr($string, $table);
            if (function_exists('iconv')) {
                $string = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $string);
            }
        }

        return $string;
    }

    /**
     * Builds the Regex pattern for a specific Type string
     *
     * This function will build the Regex pattern for a specific string, which is
     * passed to the function.
     *
     * @param string $pattern The String
     * @param string $type Type or Handler name
     * @access private
     */

    private function appendRootAll() {
        global $settings;
        if (preg_match("/(href|src)='((?!(htt|ft)p(s)?:\/\/)[^\']*)'/i", $this->output)) {
            $basedir = str_replace(array(".", "/"), array("\.", "\/"), BASEDIR);
            $basedir = preg_replace("/(href|src)='(".$basedir.")*([^\':]*)'/i", "$1='".ROOT."$3'", $this->output);
            // Remove ../ before http://
            $loop = 7;
            for ($i = 1; $i <= $loop; $i++) {
                $basedir = str_replace(str_repeat('../', $i).'http://', 'http://', $basedir);
            }
            // Remove ../ before https://
            for ($i = 1; $i <= $loop; $i++) {
                $basedir = str_replace(str_repeat('../', $i).'https://', 'https://', $basedir);
            }
            $this->output = $basedir;
        }
    }

    /**
     * Clean the REGEX by escaping some characters
     *
     * This function will escape some characters in the Regex expression
     *
     * @param string $regex The expression String
     * @access private
     */

    private function validateURI() {
        $current_uri     = PERMALINK_CURRENT_PATH;
        $uri_match_found = FALSE;
        // Checking for Alias and its Patterns
        foreach ($this->aliases as $key => $alias) {
            if (!$uri_match_found) {
                $alias_php_url = $this->getAliasURL($alias['alias_url'], $alias['alias_php_url'], $alias['alias_type']);
                // Checking for Alias first
                if (array_key_exists(1, $alias_php_url) && strcmp($current_uri, $alias_php_url[1]) == 0) {
                    $uri_match_found = TRUE;
                    $this->mpRedirect($alias_php_url[0]);
                }
                // Checking for Alias Pattern
                $type = $alias['alias_type'];
                // Check If there are any Alias Patterns defined for this Type or not
                if (array_key_exists($type, $this->alias_pattern)) {
                    $target_url = "";
                    foreach ($this->alias_pattern[$type] as $replace => $search) {
                        // First of all, Replace %alias% with the actual Alias Name
                        $replace = str_replace("%alias%", $alias['alias_url'], $replace);
                        // Secondly, Replace %alias_target% with Alias PHP URL
                        $search        = str_replace("%alias_target%", $alias['alias_php_url'], $search);
                        $search_string = $search;
                        // Now Replace Pattern Tags with suitable Regex Codes
                        $search = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $search);
                        $search = $this->cleanRegex($search);
                        $search = "#^".$search."$";
                        // If the Pattern matches with URI
                        if (preg_match($search, $current_uri, $matches)) {
                            $target_url = $replace;
                            // Replace Tags with their suitable matches
                            $target_url      = $this->replaceOtherTags($type, $search_string, $target_url, $matches, -1);
                            $uri_match_found = TRUE;
                            break;
                        }
                    }
                    if ($uri_match_found) {
                        $this->mpRedirect(self::cleanURL($target_url));
                    }
                }
            }
        }
        // Checking for other patterns
        if (is_array($this->pattern_search)) {
            foreach ($this->pattern_search as $type => $values) {
                foreach ($values as $key => $search) {
                    if (!$uri_match_found) {
                        // If there are any Tags defined for the Type or not
                        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
                            $search = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $search);
                        }
                        $search = $this->cleanRegex($search);
                        $search = "~^".$search."$~";
                        // If the Regex Pattern matches with URI, then continue
                        if (preg_match($search, $current_uri, $matches)) {
                            $target_url = $this->pattern_replace[$type][$key];
                            // Replace the Unique ID Tag with the Regex Code
                            // Example: Replace %news_id% with ([0-9]+)
                            if (isset($this->dbid[$type])) {
                                //foreach ($this->dbid[$type] as $tag=>$attr) {
                                $tag       = $this->getUniqueIDtag($type);
                                $attr      = $this->getUniqueIDfield($type);
                                $clean_tag = str_replace("%", "", $tag); // Remove % for Searching the Tag
                                // +1 because Array key starts from 0 and matches[0] gives the complete match
                                $pos = $this->getTagPosition($this->pattern_search[$type][$key], $clean_tag);
                                if ($pos != 0) {
                                    $unique_id_value = $matches[$pos];
                                    // Replacing each Tag with its Database Value if any
                                    // Example: %thread_title% should be replaced with thread_subject
                                    foreach ($this->dbinfo[$type] as $other_tags => $other_attr) {
                                        if (strstr($target_url, $other_tags)) {
                                            $target_url = str_replace($other_tags,
                                                $this->data_cache[$type][$unique_id_value][$other_attr],
                                                $target_url);
                                        }
                                    }
                                }
                                //}
                            }
                            // Replacing each of the Tag with its suitable match found on the Page
                            $target_url      = $this->replaceOtherTags($type, $this->pattern_search[$type][$key],
                                $target_url, $matches, -1);
                            $uri_match_found = TRUE;
                            $this->mpRedirect(self::cleanURL($target_url));
                        }
                    }
                }
            }
        }
    }

    /**
     * Append the BASEDIR Path to Search String
     *
     * This function will append the BASEDIR path to the Search pattern. This is
     * required in some cases like when we are on actual php script page and
    * Permalinks are ON.
    *
    * @param string $str The String
     * @access private
     */

    private function getAliasURL($url, $php_url, $type) {
        $return_url = array(); // 1 => $search, 2 => $replace
        if (isset($this->alias_pattern[$type]) && (array_key_exists($type,
                $this->alias_pattern)) && is_array($this->alias_pattern[$type])
        ) {
            $match_found = FALSE;
            foreach ($this->alias_pattern[$type] as $search => $replace) {
                $search  = str_replace("%alias%", $url, $search);
                $replace = str_replace("%alias_target%", $php_url, $replace);
                if ($replace == PERMALINK_CURRENT_PATH) {
                    $return_url[] = $search;
                    $return_url[] = $replace;
                }
            }
        }

        return $return_url;
    }

    /**
     * Append the ROOT Dir Path to all relative links, which are from website
     *
     * This function will append the root directory path for all links, which
     * are in website. (Not External HTTP links)
     *
     * @access private
     */

    private function mpRedirect($target) {
        $settings = \fusion_get_settings();
        ob_get_contents();
        if (ob_get_length() !== FALSE) {
            ob_end_clean();
        }
        $url = $settings['siteurl'].$target;
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".$url);
        exit();
    }

    /**
     * Wrap a String with Single Quotes (')
     *
     * This function will wrap a string passed with Single Quotes.
     * Example: mystring will become 'mystring'
     *
     * @param string $str The String
     * @access private
     */

    private function showQueries() {
        if (!empty($this->queries)) {
            if (is_array($this->queries)) {
                ob_start();
                echo "\n<div class='permalinks-queries' style='padding: 10px 10px 10px 10px; border: 3px double #225500; background-color: #ccffaa; line-height: 15px;'>\n";
                echo "<strong>Queries which were made for Permalinks:</strong><br /><br />\n";
                foreach ($this->queries as $key => $query) {
                    echo $query.";<br />\n";
                }
                echo "<script type='text/javascript'>\n"
                    ."function toggledebugdiv() {\n"
                    ."	$('#permalink-debug-info').slideToggle('slow');\n"
                    ."}\n"
                    ."</script>\n";
                echo "<input type='button' value='Toggle Permalinks Debug Information' onclick='toggledebugdiv()' />\n";
                echo "<div id='permalink-debug-info' style='display: none;'>\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Handlers Stack = Array (<br />";
                foreach ($this->handlers as $key => $name) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$name."<br />";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Alias Patterns = Array (<br />";
                foreach ($this->alias_pattern as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Rewrite Codes = Array (<br />";
                foreach ($this->rewrite_code as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Rewrite Replace = Array (<br />";
                foreach ($this->rewrite_replace as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Pattern Search = Array (<br />";
                foreach ($this->pattern_search as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Pattern Replace = Array (<br />";
                foreach ($this->pattern_replace as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Pattern Regex = Array (<br />";
                foreach ($this->patterns_regex as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "DB Names = Array (<br />";
                foreach ($this->dbname as $type => $val) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => ".$val."<br />";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "DB ID = Array (<br />";
                foreach ($this->dbid as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "DB Info = Array (<br />";
                foreach ($this->dbinfo as $type => $tag) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($tag as $key => $val) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "ID Cache = Array (<br />";
                foreach ($this->id_cache as $type => $info) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($info as $id => $dbinfo) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$id."] => Array (<br />";
                        foreach ($dbinfo as $colname => $colvalue) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$colname."] => ".$colvalue."<br />";
                        }
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "<hr style='border-color:#000;' />\n";
                echo "Data Cache = Array (<br />";
                foreach ($this->data_cache as $type => $info) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$type."] => Array (<br />";
                    foreach ($info as $id => $dbinfo) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$id."] => Array (<br />";
                        foreach ($dbinfo as $colname => $colvalue) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[".$colname."] => ".$colvalue."<br />";
                        }
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;)<br />\n";
                }
                echo ");<br />\n";
                echo "</div>\n";
                echo "</div>\n";
                $queries_output = ob_get_contents();
                if (ob_get_length() !== FALSE) {
                    ob_end_clean();
                }
                $this->output = preg_replace("#<body>#", "<body>".$queries_output, $this->output);
            }
        }
    }

    /**
     * Cleans the URL
     *
     * Thanks to "THE PERFECT PHP CLEAN URL GENERATOR"(http://cubiq.org/the-perfect-php-clean-url-generator)
     *
     * This function will clean the URL by removing any unwanted characters from it and
     * only allowing alphanumeric and - in the URL.
     * This function can be customized according to your needs.
     *
     * @param string $string    The URL String
     * @param string $delimiter The Delimiter to replace spaces
     * @access private
     */

    private function fetchDataID($type, $pattern, $id) {
        $column_arr = array();
        foreach ($this->rewrite_code[$type] as $key => $tag) { // Example: news_id => array("%news_id%", "%news_title%")
            // We check if the Tag exist in the Pattern
            // if Yes, then Find the suitable Column_name in the DB for that Tag.
            if (strstr($pattern, $tag)) {
                if (isset($this->dbinfo[$type]) && array_key_exists($tag, $this->dbinfo[$type])) {
                    if (!in_array($this->dbinfo[$type][$tag], $column_arr)) {
                        $column_arr[] = $this->dbinfo[$type][$tag];
                    }
                }
            }
        }
        // If there are any Columns to be fetch from Database
        if (!empty($column_arr)) {
            $unique_col      = $this->getUniqueIDfield($type); // The Unique Column name for WHERE condition
            $column_arr[]    = $unique_col; // Also fetch the Unique_ID like news_id, thread_id
            $column_names    = implode(",", $column_arr); // Array to String conversion for MySQL Query
            $dbname          = $this->dbname[$type]; // Table Name in Database
            $fetch_query     = "SELECT ".$column_names." FROM ".$dbname." WHERE ".$unique_col."='".$id."'"; // The Query
            $result          = dbquery($fetch_query); // Execute Query
            $this->queries[] = $fetch_query;
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    foreach ($column_arr as $key => $col_name) {
                        $this->CacheInsertDATA($type, $data[$unique_col], $col_name, $data[$col_name]);
                    }
                }
            }
        }
    }

}
