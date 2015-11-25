<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Permalinks.php
| Author: Ankur Thakur
| Co-Author: Tak�cs �kos (Rimelek)
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
namespace PHPFusion\Rewrite\Old;

if (!defined("IN_FUSION")) { die("Access Denied"); }

class Permalinks {
    private static $instance = NULL;
    public $debug_regex = FALSE;
    private $output = "";
    private $handlers = array();
    private $rewrite_code = array();
    private $rewrite_replace = array();
    private $alias_pattern = array();
    private $pattern_search = array();
    private $pattern_replace = array();
    private $patterns_regex = array();
    private $dbname = array();
    private $dbid = array();
    private $dbinfo = array();
    private $data_cache = array();
    private $id_cache = array();
    private $queries = array();
    private $aliases = array();
    private $regex_statements = array();

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
     * Returns the Output
     * This function will first call the handleOutput() and then it will return the
     * modified Output for SEO.
     * @param string $ouput The Output
     * @access public
     */
    public function getOutput($output) {
        global $locale;
        $output = html_entity_decode($output, ENT_QUOTES, $locale['charset']);
        $output = str_replace("\"", "'", $output);
        $this->handleOutput($output);
        return $this->output;
    }

    /**
     * Main Function : Handles the Output
     * This function will Handle the output by calling several functions
     * which are used in this Class.
     * @param string $output The Output from the Fusion
     * @access private
     */
    private function handleOutput($ob_get_contents_from_footer_dot_php) {

        $settings     = \fusion_get_settings();
        $this->output = str_replace("&", "&amp;", $ob_get_contents_from_footer_dot_php);
        $this->verifyHandlers(); // Read from DB
        $this->includeHandlers(); // Include the files
        $this->importPatterns(); // Prepare the strings
        $this->prepareSourceRegex(); // Make Regex Patterns for the URL Patterns
        $this->prepareStatements(); // Read output once, sort them all
        $this->replace_output(); // Output and Redirect 301 if NON-SEO url found
        $this->appendRootAll(); // Prepend all the File/Images/CSS/JS etc Links with ROOT path
        // For Developer, to see what is happening behind
        if ($settings['debug_seo'] == "1") {
            $this->showQueries();
        }
    }

    /**
     * Verify Handlers
     *
     * This will verify all the added Handlers by checking if they are enabled
     * or not. The Disabled Handlers are removed from the List and only Enabled
     * Handlers are kept for working.
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
     * Include the Handlers
     * This function will include the neccessary files for the Handler and call
     * the functions to manipulate the information from the Handler files.
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
     * Adds the Regular Expression Patterns
     * This will Add Regular Expression patterns to the Regex Search Patterns
     * and also the Replacement patterns.
     */
    private function addRegexTag($regex, $type) {
        foreach ($regex as $reg_search => $reg_replace) {
            $this->rewrite_code[$type][] = $reg_search;
            $this->rewrite_replace[$type][] = $reg_replace;
        }
    }

    /**
     * Adds the DB Table Name into the DB_Names array
     * This will Add DB Table Names into the array, which are further used in MySQL Query.
     * @param string $dbname Name of the Table
     * @param string $type Type or Handler name
     * @access private
     */
    private function addDbname($dbname, $type) {
        $this->dbname[$type] = $dbname;
    }

    /**
     * Inserts the matched Unique ID info into ID_Cache Array
     * This will Insert the Unique IDs info into the ID_Cache Array which will be further used to distinguish
     * matches and items. These matches also helps in fetching info for different matches from DB.
     * Example: 1,2,3,8,9 as user_id or news_id
     * @param array $value Array of matches
     * @param string $type Type or Handler name
     */
    private function addDbid($dbid, $type) {
        $this->dbid[$type] = $dbid;
    }

    /**
     * Fetch Data for a specific Type, ID and Pattern
     * This function will fetch specific data on the basis of the Pattern, Type
     * and the unique ID value.
     * @param string $type    The Type of Pattern
     * @param string $pattern The Specific Pattern
     * @param string $id      Unique ID Value
     */
    private function addDbinfo($dbinfo, $type) {
        $this->dbinfo[$type] = $dbinfo;
    }

    private function importPatterns() {
        if (!empty($this->handlers)) {
            $types = array();
            foreach ($this->handlers as $key => $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }
            $types_str       = implode(",", $types);
            $query           = "SELECT r.rewrite_name, p.pattern_type, p.pattern_source, p.pattern_target, p.pattern_cat FROM
                      ".DB_PERMALINK_METHOD." p INNER JOIN ".DB_PERMALINK_REWRITE." r
                      WHERE r.rewrite_id=p.pattern_type AND r.rewrite_name IN(".$types_str.") ORDER BY p.pattern_type";
            $this->queries[] = $query;
            $result          = dbquery($query);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    if ($data['pattern_cat'] == "normal") {
                        $this->pattern_search[$data['rewrite_name']][]  = $data['pattern_target'];
                        $this->pattern_replace[$data['rewrite_name']][] = $data['pattern_source'];
                    } elseif ($data['pattern_cat'] == "alias") {
                        $this->alias_pattern[$data['rewrite_name']][$data['pattern_source']] = $data['pattern_target'];
                    }
                }
            }
        }
    }

    /**
     * Builds the Regex pattern for a specific Type string
     * This function will build the Regex pattern for a specific string, which is
     * passed to the function.
     * @param string $pattern The String
     * @param string $type    Type or Handler name
     * @access private
     */
    private function prepareSourceRegex() {
        if (is_array($this->pattern_search)) {
            foreach ($this->pattern_search as $type => $RawSearchPatterns) {
                if (!empty($RawSearchPatterns) && is_array($RawSearchPatterns)) {
                    foreach ($RawSearchPatterns as $key => $val) {
                        $regex = $val;
                        // need a tone.
                        $regex = $this->appendSearchPath($regex);
                        $regex = self::cleanRegex($regex);
                        // Rewrite Code is driver file $regex key
                        // Rewrite Replace is driver file $regex values
                        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
                            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
                        }
                        $regex                             = $this->wrapQuotes($regex);
                        $this->patterns_regex[$type][$key] = "~".$regex."~i";
                    }
                }
            }
        }
    }

    /**
     * Append the BASEDIR Path to Search String
     * This function will append the BASEDIR path to the Search pattern. This is
     * required in some cases like when we are on actual php script page and
     * Permalinks are ON.
     * @param string $str The String
     * @access private
     */
    private function appendSearchPath($str) {
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
     * This function will escape some characters in the Regex expression
     * @param string $regex The expression String
     */
    public static function cleanRegex($regex) {
        $regex = str_replace("/", "\/", $regex);
        $regex = str_replace("#", "\#", $regex);
        $regex = str_replace(".", "\.", $regex);
        $regex = str_replace("?", "\?", $regex);
        return $regex;
    }

    /**
     * Wrap a String with Single Quotes (')
     * This function will wrap a string passed with Single Quotes.
     * Example: mystring will become 'mystring'
     * @param string $str The String
     * @access private
     */
    public static function wrapQuotes($str) {
        $rep = $str;
        $rep = "'".$rep."'";
        return $rep;
    }

    private function do_output_lookup($searchVars, $field, $key) {
        if (preg_match($searchVars, $this->output)) {
            // Store all the matches into the $matches array
            preg_match_all($searchVars, $this->output, $matches); // 1 search string against output.
            // Returns the Tag from the Unique DBID by which the Pattern in recognized, i.e, %news_id%, %thread_id%
            $tag = $this->getUniqueIDtag($field);
            $attr = $this->getUniqueIDfield($field);
            $pos = $this->getTagPosition($this->pattern_search[$field][$key], $attr);
            if ($pos != 0) {
                $found_matches = array_unique($matches[$pos]); // This is to remove duplicate matches
                foreach ($found_matches as $match_key => $match) {
                    $replace = $this->pattern_replace[$field][$key]; // replace pattern
                    // Replacing each Tag with its Database Value if any
                    // Example: %thread_title% should be replaced with thread_subject
                    if (isset($this->dbinfo[$field])) {
                        // replace ids.
                        foreach ($this->dbinfo[$field] as $other_tags => $other_attr) {
                            if (strstr($replace, $other_tags)) {
                                $replace = str_replace($other_tags,
                                                       $this->data_cache[$field][$match][$other_attr],
                                                       $replace);
                            }
                        }
                    }
                    $search = str_replace($tag, $match, $this->pattern_search[$field][$key]);
                    // this might be the culprit in not making the navigation other tag work and replication.
                    $search = $this->replaceOtherTags($field, $this->pattern_replace[$field][$key], $search, $matches, $match_key); // BUG This will stop &amp; parsing ! Added: Replace Tags values in Search Pattern Also
                    $search = $this->makeSearchRegex($this->appendSearchPath($search), $field);
                    // Replacing each of the Tag with its suitable match found on the Page - Suitable becomes non-suitable if you put Pagenav inside a DBID Type.
                    // Every page nav becomes identical!
                    $replace = $this->replaceOtherTags($field, $this->pattern_search[$field][$key], $replace, $matches, $match_key);
                    $replace = self::cleanURL($replace);
                    $replace = $this->wrapQuotes($replace);
                    unset($found_matches);
                    $this->regex_statements['pattern'][$field][] = array($search => $replace);
                }
            } else {
                // outright replacement
                $replace = $this->wrapQuotes($this->pattern_replace[$field][$key]); // replace pattern
                $this->regex_statements['pattern'][$field][] = array($searchVars => $replace);
                //$this->regex_statements['failed_pattern'][$field][] = array("search" => $searchVars, "status"=>"Found but not included in Permalink logic");
            }
        } else {
            $this->regex_statements['failed_pattern'][$field][] = array("search" => $searchVars, "status"=>"Failed either - failed regex expression in driver or failed to find matching content");
        }
    }

    // Add to redirect search

    private function do_uri_lookup($field, $key) {

        if (isset($this->pattern_replace[$field][$key]) && $this->pattern_search[$field][$key]) {
            $searchVars = $this->pattern_search[$field][$key];
            $replace = $this->pattern_replace[$field][$key]; // need the one with &amp;
            if (isset($this->rewrite_code[$field]) && isset($this->rewrite_replace[$field])) {
                $searchVars = str_replace($this->rewrite_code[$field], $this->rewrite_replace[$field], $searchVars);
            }
            $searchVars = "~".self::cleanRegex($searchVars)."$~i";
            if (preg_match($searchVars, PERMALINK_CURRENT_PATH, $matches)) {
                // Store all the matches into the $matches array
                preg_match_all($searchVars, PERMALINK_CURRENT_PATH, $matches); // 1 search string against output.
                // Returns the Tag from the Unique DBID by which the Pattern in recognized, i.e, %news_id%, %thread_id%
                $tag = $this->getUniqueIDtag($field);
                $attr = $this->getUniqueIDfield($field);
                $pos = $this->getTagPosition($this->pattern_search[$field][$key], $attr);
                if ($pos != 0) {
                    $found_matches = array_unique($matches[$pos]); // This is to remove duplicate matches
                    foreach ($found_matches as $match_key => $match) {
                        $replace = $this->pattern_replace[$field][$key]; // replace pattern
                        // Replacing each Tag with its Database Value if any
                        // Example: %thread_title% should be replaced with thread_subject
                        if (isset($this->dbinfo[$field])) {
                            // replace ids.
                            foreach ($this->dbinfo[$field] as $other_tags => $other_attr) {
                                if (strstr($replace, $other_tags)) {
                                    $replace = str_replace($other_tags,
                                                           $this->data_cache[$field][$match][$other_attr],
                                                           $replace);
                                }
                            }
                        }
                        $search = str_replace($tag, $match, $this->pattern_search[$field][$key]);
                        // this might be the culprit in not making the navigation other tag work and replication.
                        $search = $this->replaceOtherTags($field, $this->pattern_replace[$field][$key], $search, $matches, $match_key); // BUG This will stop &amp; parsing ! Added: Replace Tags values in Search Pattern Also
                        // Replacing each of the Tag with its suitable match found on the Page - Suitable becomes non-suitable if you put Pagenav inside a DBID Type.
                        // Every page nav becomes identical!
                        $replace = $this->replaceOtherTags($field, $this->pattern_search[$field][$key], $replace, $matches, $match_key);
                        $replace = self::cleanURL($replace);
                        $this->redirect_301($replace);
                        //unset($found_matches);
                        //$this->regex_statements['pattern_uri_redirect'][$field][] = array($uri_search => $uri_replace);
                    }
                    // end position 0 (it means these were an arrays)
                } else {
                    $replace = self::cleanURL($replace);
                    $this->redirect_301($replace);
                    //$this->regex_statements['pattern_uri_redirect'][$field][] = array($searchVars => $replace);
                }
            }
        }
    }

    /**
     * Builds the Regular Expressions Patterns
     * This function will create the Regex patterns and will put the built patterns
     * in $patterns_regex array. This array will then used in preg_match function
     * to match against current request.
     * @access private
     */
    private function prepareStatements() {
        // Patterns
        self::getOtherTags();
        foreach($this->patterns_regex as $field => $searchRegex) { // rawsearchpatterns
            foreach($searchRegex as $key => $searchVars) { // searchvariables
                if (isset($this->dbid[$field])) {
                    $this->do_output_lookup($searchVars, $field, $key);
                } else {
                    $this->regex_statements['failed_pattern'][$field][] = array("status"=>"This ".$field." has been omitted entirely and short of development.");
                }
                $this->do_uri_lookup($field, $key);
            }
        }

        // Alias -- this is only activated after RC - Need new admin panel
        if (!empty($this->handlers)) {
            $fields = array();
            foreach ($this->handlers as $key => $value) {
                $fields[] = "'".$value."'";
            }
            $handlers = implode(",", $fields);
            $query = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_type IN(".$handlers.")";
            $this->queries[] = $query;
            $aliases = dbquery($query);
            if (dbrows($aliases)) {
                while ($alias = dbarray($aliases)) {
                    //$this->replaceAliasPatterns($data);
                    $alias_php_url = $this->getAliasURL($alias['alias_url'], $alias['alias_php_url'], $alias['alias_type']);
                    $field = $alias['alias_type'];

                    if (array_key_exists(1, $alias_php_url) && strcmp(PERMALINK_CURRENT_PATH, $alias_php_url[1]) == 0) {
                        $this->redirect_301($alias_php_url[0]);
                    }

                    // Check If there are any Alias Patterns defined for this Type or not
                    if (array_key_exists($field, $this->alias_pattern)) {

                        foreach ($this->alias_pattern[$field] as $replace => $search) {
                            // Secondly, Replace %alias_target% with Alias PHP URL
                            $search = str_replace("%alias_target%", $alias['alias_php_url'], $search);
                            $search_string = $search;

                            $alias_search = str_replace($this->rewrite_code[$field], $this->rewrite_replace[$field], $search_string);
                            $alias_search = self::cleanRegex($alias_search);
                            $alias_search = "~^".$alias_search."$";

                            // Now Replace Pattern Tags with suitable Regex Codes
                            $search = $this->makeSearchRegex($search, $field);

                            // If the Pattern is found in the Output
                            if (preg_match($search, $this->output)) {
                                // Search them all and put them in $matches
                                preg_match_all($search, $this->output, $matches);
                                // $matches[0] represents the Array of all the matches for this Pattern
                                foreach ($matches[0] as $count => $match) {
                                    // First of all, Replace %alias% with the actual Alias Name
                                    $replace_str = str_replace("%alias%", $alias['alias_url'], $replace);
                                    $match = self::cleanRegex($match);
                                    // Replace Tags with their suitable matches
                                    $replace_str = $this->replaceOtherTags($field, $search_string, $replace_str, $matches, $count);
                                    $replace_str = $this->cleanURL($replace_str);

                                    $this->regex_statements['alias'][$field][] = array($match => $replace_str);
                                    $this->regex_statements['alias_redirect'][$field][] = array($alias_search => $replace_str);
                                }
                            } else {
                                $this->regex_statements['failed_alias'][$field][] = array("search" => $search, "status"=>"failed");
                            }
                        }
                    }
                    $this->aliases[] = $alias;
                }
            }
        }
        if ($this->debug_regex) print_p($this->regex_statements);
    }

    /**
     * Get Other Tags
     * This function will Search for the matching patterns in the current output. If the
     * match(es) are found, it will find the correct tags required under the Rule.
     * Works with replaceOtherTags
     */
    private function getOtherTags() {

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
                                // +1 because Array key starts from 0 and matches[0] gives the complete match
                                // Get the position of that unique DBID from the pattern in order to get value from the $matches
                                $clean_tag = $this->getUniqueIDfield($type);
                                $pos       = $this->getTagPosition($this->pattern_search[$type][$key], $clean_tag);
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

        if (is_array($this->id_cache)) {
            foreach ($this->id_cache as $type => $id_val_arr) {
                if (is_array($this->id_cache[$type])) {
                    foreach ($this->id_cache[$type] as $field => $values) {
                        $this->id_cache[$type][$field] = array_unique($this->id_cache[$type][$field]);
                    }
                }
            }
        }

        if (!empty($this->id_cache)) {
            foreach ($this->id_cache as $type => $column_name) { // Example: news => news_id
                foreach ($column_name as $name => $items) { // Example: news_id => array(1,3,5,6,7)
                    // We will only fetch the Data which is in the pattern
                    // This is to Ignore fetching the data that we do not want
                    $column_arr = array();
                    foreach ($this->rewrite_code[$type] as $key => $tag) { // Example: news_id => array("%news_id%", "%news_title%")
                        foreach ($this->pattern_replace[$type] as $key1 => $pattern) {
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
                        $column_arr[]    = $name; // Also fetch the Unique_ID like news_id, thread_id
                        $column_names    = implode(",", $column_arr); // Array to String conversion for MySQL Query
                        $dbname          = $this->dbname[$type]; // Table Name in Database
                        $unique_col      = $name; // The Unique Column name for WHERE condition
                        $items           = array_unique($items); // Remove any duplicates from the Array
                        $ids_to_fetch    = implode(",", $items); // IDs to fetch data of
                        $fetch_query     = "SELECT ".$column_names." FROM ".$dbname." WHERE ".$unique_col.(count($items) > 1 ? " IN(".$ids_to_fetch.")" : "='".$ids_to_fetch."'"); // The Query
                        $result          = dbquery($fetch_query); // Execute Query
                        $this->queries[] = $fetch_query;
                        if (dbrows($result)) {
                            while ($data = dbarray($result)) {
                                foreach ($column_arr as $key => $col_name) {
                                    $this->data_cache[$type][$data[$unique_col]][$col_name] = $data[$col_name];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the Tag of the Unique ID type
     * Example: For news, unique ID should be news_id
     * So it will return %news_id% because of array("%%news_id" => "news_id")
     * @param string $type Type or Handler name
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
     * Calculates the Tag Position in a given pattern.
     * This function will calculate the position of a given Tag in a given pattern.
     * Example: %id% is at 2 position in articles-%title%-%id%
     * @param string $pattern The Pattern string in which particular Tag will be searched.
     * @param string $search The Tag which will be searched.
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
     * Get the Field of the Unique ID type
     * Example: For news, unique ID should be news_id
     * So it will return news_id because of array("%%news_id" => "news_id")
     * @param string $type Type or Handler name
     */
    private function CacheInsertID($type, $value) {
        $field = $this->getUniqueIDfield($type);
        $this->id_cache[$type][$field][] = $value;
    }

    /**
     * Remove Duplicates IDs from the ID Cache to minimize DB Query String
     * This will Remove duplicate entried for IDs from the ID Cache so that the
     * MySQL Query string is minimized and is not very much long in length.
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
     * Replace Other Tags in Pattern
     * This function will replace all the Tags in the Pattern with their suitable found
     * matches. All the Information is passed to the function and it will replace the
     * Tags with their respective matches.
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
        return $replace;
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
    private function makeSearchRegex($pattern, $type) {
        $regex = $pattern;
        $regex = self::cleanRegex($regex);
        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
        }
        $regex = $this->wrapQuotes($regex);
        $regex = "~".$regex."~i";
        return (string) $regex;
    }

    /**
     * Cleans the URL
     * This function will clean the URL by removing any unwanted characters from it and
     * only allowing alphanumeric and - in the URL.
     * This function can be customized according to your needs.
     * @param string $string    The URL String
     * @param string $delimiter The Delimiter to replace spaces
     * @access private
     */
    public static function cleanURL($string, $delimiter = "-") {
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
        return $string;
    }

    /**
     * Replaces special characters in a string with their "non-special" counterpart.
     * Useful for friendly URLs.
     * @access public
     * @param string
     * @return string
     */
    public static function normalize($string) {
        $table  = array(
            '&amp;' => 'and', '@' => 'at', '�' => 'c', '�' => 'r', '�' => 'a',
            '�'     => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'ae', '�' => 'c',
            '�'     => 'e', '�' => 'e', '�' => 'e', '�' => 'i', '�' => 'i', '�' => 'i',
            '�'     => 'i', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o',
            '�'     => 'o', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'y',
            '�'     => 'ss', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a',
            '�'     => 'ae', '�' => 'c', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e',
            '�'     => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'o', '�' => 'o',
            '�'     => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'u', '�' => 'u',
            '�'     => 'u', '�' => 'u', '�' => 'y', '�' => 'p', '�' => 'y', '?' => 'a',
            '?'     => 'a', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'c',
            '?'     => 'c', '?' => 'c', '?' => 'c', '?' => 'c', '?' => 'c', '?' => 'c',
            '?'     => 'c', '?' => 'd', '?' => 'd', '?' => 'd', '?' => 'd', '?' => 'e',
            '?'     => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e',
            '?'     => 'e', '?' => 'e', '?' => 'e', '?' => 'g', '?' => 'g', '?' => 'g',
            '?'     => 'g', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'g', '?' => 'h',
            '?'     => 'h', '?' => 'h', '?' => 'h', '?' => 'i', '?' => 'i', '?' => 'i',
            '?'     => 'i', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i',
            '?'     => 'i', '?' => 'ij', '?' => 'ij', '?' => 'j', '?' => 'j', '?' => 'k',
            '?'     => 'k', '?' => 'k', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l',
            '?'     => 'l', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l', '?' => 'l',
            '?'     => 'n', '?' => 'n', '?' => 'n', '?' => 'n', '?' => 'n', '?' => 'n',
            '?'     => 'n', '?' => 'n', '?' => 'n', '?' => 'o', '?' => 'o', '?' => 'o',
            '?'     => 'o', '?' => 'o', '?' => 'o', '�' => 'oe', '�' => 'oe', '?' => 'r',
            '?'     => 'r', '?' => 'r', '?' => 'r', '?' => 'r', '?' => 'r', '?' => 's',
            '?'     => 's', '?' => 's', '?' => 's', '?' => 's', '?' => 's', '�' => 's',
            '�'     => 's', '?' => 't', '?' => 't', '?' => 't', '?' => 't', '?' => 't',
            '?'     => 't', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u',
            '?'     => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u',
            '?'     => 'u', '?' => 'w', '?' => 'w', '?' => 'y', '?' => 'y', '�' => 'y',
            '?'     => 'z', '?' => 'z', '?' => 'z', '?' => 'z', '�' => 'z', '�' => 'z',
            '?'     => 'z', '?' => 'e', '�' => 'f', '?' => 'o', '?' => 'o', '?' => 'u',
            '?'     => 'u', '?' => 'a', '?' => 'a', '?' => 'i', '?' => 'i', '?' => 'o',
            '?'     => 'o', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u',
            '?'     => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'a',
            '?'     => 'a', '?' => 'ae', '?' => 'ae', '?' => 'o', '?' => 'o', '?' => 'e',
            '?'     => 'jo', '?' => 'e', '?' => 'i', '?' => 'i', '?' => 'a', '?' => 'b',
            '?'     => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'zh', '?' => 'z',
            '?'     => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n',
            '?'     => 'o', '?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u',
            '?'     => 'f', '?' => 'h', '?' => 'c', '?' => 'ch', '?' => 'sh', '?' => 'sch',
            '?'     => '-', '?' => 'y', '?' => '-', '?' => 'je', '?' => 'ju', '?' => 'ja',
            '?'     => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e',
            '?'     => 'zh', '?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l',
            '?'     => 'm', '?' => 'n', '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's',
            '?'     => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch',
            '?'     => 'sh', '?' => 'sch', '?' => '-', '?' => 'y', '?' => '-', '?' => 'je',
            '?'     => 'ju', '?' => 'ja', '?' => 'jo', '?' => 'e', '?' => 'i', '?' => 'i',
            '?'     => 'g', '?' => 'g', '?' => 'a', '?' => 'b', '?' => 'g', '?' => 'd',
            '?'     => 'h', '?' => 'v', '?' => 'z', '?' => 'h', '?' => 't', '?' => 'i',
            '?'     => 'k', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'm', '?' => 'n',
            '?'     => 'n', '?' => 's', '?' => 'e', '?' => 'p', '?' => 'p', '?' => 'C',
            '?'     => 'c', '?' => 'q', '?' => 'r', '?' => 'w', '?' => 't', '�' => 'tm',
        );
        $string = strtr($string, $table);
        return (string)$string;
    }

    /**
     * Get Alias URL
     * This function will return an Array of 2 elements for a specific Alias:
     * 1. The Permalink URL of Alias
     * 2. PHP URL of the Alias
     * @param string $url     The Permalink URL (incomplete)
     * @param string $php_url The PHP URL (incomplete)
     * @param string $type    Type of Alias
     * @access private
     */
    private function getAliasURL($url, $php_url, $type) {
        $return_url = array();
        // 1 => $search, 2 => $replace
        if (isset($this->alias_pattern[$type]) && is_array($this->alias_pattern[$type])) {
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
     * Redirect 301 : Moved Permanently Redirect
     * This function invoked to prevent of caching any kinds of Non SEO URL on render.
     * Let search engine mark as 301 permanently
     * @param string $target The Target URL
     * @access private
     */
    public static function redirect_301($target, $debug = FALSE) {
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
     * Do full replacement of the HTML output
     */
    private function replace_output() {
        // Pattern translation
        if (!empty($this->regex_statements['pattern'])) {
            foreach ($this->regex_statements['pattern'] as $handler => $rules) {
                $_patterns = flatten_array($rules);
                foreach ($_patterns as $search => $replace) {
                    $this->output = preg_replace($search, $replace, $this->output);
                }
            }
        }
        // Alias translation
        if (!empty($this->regex_statements['alias'])) {
            foreach ($this->regex_statements['alias'] as $handler => $rules) {
                $_patterns = flatten_array($rules);
                foreach ($_patterns as $search => $replace) {
                    $this->output = preg_replace($search, $replace, $this->output);
                }
            }
        }
        // Alias Redirecting
        if (!empty($this->regex_statements['alias_redirect'])) {
            foreach ($this->regex_statements['alias_redirect'] as $handler => $rules) {
                $_patterns = flatten_array($rules);
                foreach ($_patterns as $search => $replace) {
                    if (preg_match($search, PERMALINK_CURRENT_PATH, $matches)) {
                        $this->redirect_301($replace);
                    }
                }
            }
        }

        // Pattern Redirecting
        if (!empty($this->regex_statements['pattern_redirect'])) {
            foreach ($this->regex_statements['pattern_redirect'] as $handler => $rules) {
                $_patterns = flatten_array($rules);
                foreach ($_patterns as $search => $replace) {
                    if (preg_match($search, PERMALINK_CURRENT_PATH, $matches)) {
                        $this->redirect_301($replace);
                    }
                }
            }
        }
    }

    /**
     * Append the ROOT Dir Path to all relative links, which are from website
     * This function will append the root directory path for all links, which
     * are in website. (Not External HTTP links)
     */
    private function appendRootAll() {
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
     * Debug Function for Developers
     * Just a simple function for the developer to see, what is going in the background.
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
}