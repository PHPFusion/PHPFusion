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

/**
 * Rewrite API for PHP-Fusion
 * This Rewrite API handles the Permalinks Requests
 * and map them to suitable existing URLs in website.
 * You can use this API to Add custom rules for handling requests
 */

class Router extends RewriteDriver {

    /**
     * Name of the php file which will be loaded
     * for the permalink.
     * example: news.php, articles.php
     * @data_type String
     * @access private
     */
    private $pathtofile = "";
    /**
     * Array of Parameters with their
     * corresponding Tags.
     * example: thread_id => %thread_id%
     * @data_type Array
     * @access private
     */
    private $parameters = array();
    /**
     * Array of Parameters with their
     * actual values.
     * example: thread_id => 1, rowstart => 20
     * @data_type Array
     * @access private
     */
    private $get_parameters = array();

    public function __construct() {
        // Pretty URL
        $this->requesturi = urldecode(PERMALINK_CURRENT_PATH);
    }

    /**
     * Call all the functions to process rewrite detection and further actions.
     * This will call all the other functions after all the included files have been included
     * and all the patterns have been made.
     * @access public
     */
    public function rewritePage() {
        // Import the required Handlers
        $this->importHandlers();
        // Include the Rewrites
        $this->includeRewrite();
        // Import Patterns from DB
        $this->importPatterns();

        // Check if there is any Alias matching with current URL
        if (!$this->checkAlias()) {
            // Check if any Alias Pattern is matching with current URL
            if (!$this->checkAliasPatterns()) {
                // Check if any Pattern is matching with current URL
                $this->checkPattern();
                $this->validateURI();
            }
        }
        // If path to File is empty, set a warning
        if ($this->pathtofile == "") {
            $this->setWarning(6);
        }
        if (fusion_get_settings("debug_seo") == 1) {
            $this->displayWarnings(); // If any Warnings to be shown, or in Debug mode
        }
    }

    /**
     * Index Include File
     *
     * Returns the filename of the php file which will be included.
     * If this file is blank, index.php will redirect to 404 error page
     * @access public
     */
    public function getFilePath() {
        return $this->pathtofile;
    }

    /**
     * Import the Available Patterns from Database
     *
     * This will Import all the available Patterns for the Handlers
     * from the Database and put it into $pattern_search and
     * $pattern_replace array.
     *
     * @access private
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

                        $this->pattern_search[$data['rewrite_name']][] = $data['pattern_source'];
                        $this->pattern_replace[$data['rewrite_name']][] = $data['pattern_target'];

                    } elseif ($data['pattern_cat'] == "alias") {

                        $this->alias_pattern[$data['rewrite_name']][$data['pattern_source']] = $data['pattern_target'];

                    }
                }
            }
        }
    }

    /**
     * Checks if there is any matching Alias or not
     *
     * This function will check if there is any matching Alias for the URI or not.
     *
     * @access private
     */
    private function checkAlias() {
        // Check if there is any Alias matching the current URI
        $query = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_url='".$this->requesturi."' LIMIT 1";

        $result = dbquery($query);

        $this->queries[] = $query;

        if (dbrows($result)) {
            $aliasdata = dbarray($result);
            // If Yes, then Exploded the corresponding php_url and render the page
            if ($aliasdata['alias_php_url'] != "") {

                $alias_url = $this->getAliasURL($aliasdata['alias_url'], $aliasdata['alias_php_url'], $aliasdata['alias_type']);

                $url_info = $this->explodeURL($alias_url, "&amp;");

                // File Path (Example: news.php)
                $this->pathtofile = $url_info[0];
                if (isset($url_info[1])) {
                    foreach ($url_info[1] as $paramkey => $paramval) {
                        $this->get_parameters[$paramkey] = $paramval; // $this->get_parameters['thread_id'] = 1
                    }
                }
                // Call the function to set server variables
                $this->setVariables();

                $this->setWarning(7, $this->requesturi); // Alias Found
                return TRUE;
            }
        } else {

            $this->setWarning(1, $this->requesturi); // Alias not found
            return FALSE;
        }
    }

    /**
     * Get Alias URL for Router
     * This function will return an Array of 2 elements for a specific Alias:
     * 1. The Permalink URL of Alias
     * 2. PHP URL of the Alias
     * @param string $url     The Permalink URL (incomplete)
     * @param string $php_url The PHP URL (incomplete)
     * @param string $type    Type of Alias
     * @access private
     */
    private function getAliasURL($url, $php_url, $type) {
        if (isset($this->alias_pattern) && isset($this->alias_pattern[$type]) && is_array($this->alias_pattern[$type])) {
            foreach ($this->alias_pattern[$type] as $search => $replace) {
                $search = str_replace("%alias%", $url, $search);
                $replace = str_replace("%alias_target%", $php_url, $replace);
                if ($search == $this->requesturi) {
                    return $replace;
                }
            }
        }
        return $php_url;
    }

    /**
     * Explodes a URL into Filename and Get Parameters
     *
     * This function will explode the URL into its Filename and Get Parameters
     * Example: viewthread.php?thread_id=1&amp;rowstart=20
     * then :
     * array[0] => viewthread.php
     * array[1] => array(
     * [thread_id] => 1
     * [rowstart] => 20
     * )
     *
     * @param string $url The URL
     * @param string $param_delimiter The Parameters to explode by
     * @access private
     */
    private function explodeURL($url, $param_delimiter = "&amp;") {
        $url_info = array();
        // Explode URL
        $pathinfo = explode("?", $url);
        // Save the File path in 1st position of array
        $url_info[0] = $pathinfo[0];
        if (isset($pathinfo[1])) {
            // Now calculate the query parameters
            $params = explode($param_delimiter, $pathinfo[1]); // 0=>thread_id=1, 1=>pid=25
            // Now again explode it with '='
            foreach ($params as $paramkey => $paramval) { // 0=>thread_id=1, 1=>pid=25
                // bug fix. sometimes is just ?create.
                if (strpos($paramval, '=')) {
                    $get_params = explode("=", $paramval); // thread_id => 1, pid => 25
                    $url_info[1][$get_params[0]] = $get_params[1];
                } else {
                    $url_info[1][$paramval] = ''; // void all values since there is no value.
                }
            }
        }

        return $url_info;
    }

    /**
     * Call the Functions to Set GET Parameters and Query String
     * This function will call the functions to set Server GET parameters
     * and the QUERY_STRING.
     * @access private
     */
    private function setVariables() {
        $this->setservervars();
        $this->setquerystring();
    }

    /**
     * Set the PHP_SELF and SCRIPT_NAME to the suitable filepath.
     * This function will set the values of PHP_SELF and SCRIPT_NAME to the suitable
     * file name. The filename will be searched in the $pattern_replace array.
     * The php filename is found before '?' in the pattern.
     *
     * @access private
     */
    private function setservervars() {
        if (!empty($this->pathtofile)) {
            $_SERVER['PHP_SELF'] = preg_replace("/index\.php/", $this->pathtofile, $_SERVER['PHP_SELF'], 1);
            $_SERVER['SCRIPT_NAME'] = preg_replace("/index\.php/", $this->pathtofile, $_SERVER['SCRIPT_NAME'], 1);
        }
    }

    /**
     * Set the new QUERY_STRING
     * This function will set the values of QUERY_STRING to new value
     * which is calculated in buildParams().
     * @access private
     */
    private function setquerystring() {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING']."&amp;".$this->buildParams();
        } else {
            $_SERVER['QUERY_STRING'] = $this->buildParams();
        }
    }

    /**
     * Builds the $_GET parameters
     * This function will build the GET parameters and also the Query String.
     * @access private
     */
    private function buildParams() {
        $total     = count($this->get_parameters);
        $i         = 1;
        $query_str = "";
        foreach ($this->get_parameters as $key => $val) {
            $_GET[$key] = $val;
            $query_str .= $key."=".$val;
            if ($i < $total) {
                $query_str .= "&";
            }
            $i++;
        }

        return $query_str;
    }

    /**
     * Checks if there is any matching Alias Pattern or not
     *
     * This function will check if there is any matching Alias Pattern for the URI or not.
     * Example: If a Thread request is like: "post-your-site-rowstart-20", then it will
     * try to find any pattern like: "post-your-site-rowstart-%thread_rowstart%"
     *
     * @access private
     */
    private function checkAliasPatterns() {
        if (is_array($this->alias_pattern)) {
            $match_found = FALSE;
            $alias = "";
            foreach ($this->alias_pattern as $type => $alias_patterns) {
                foreach ($alias_patterns as $search => $replace) {
                    $search_pattern = $search;
                    $search         = $this->makeSearchRegex($search, $type);
                    $search         = str_replace("%alias%", "(.*?)", $search);
                    if (preg_match($search, $this->requesturi, $matches)) {
                        $alias_pos = $this->getTagPosition($search_pattern, "%alias%");
                        if ($alias_pos != 0) {
                            // The Alias is Detected !
                            $alias = $matches[$alias_pos];
                            // Now search for this Alias in Database
                            $query           = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_url='".$alias."' LIMIT 1";
                            $result          = dbquery($query);
                            $this->queries[] = $query;
                            if (dbrows($result)) {
                                $aliasdata = dbarray($result);
                                // Replace the %alias_target% in the Replacement pattern
                                $replace = str_replace("%alias_target%", $aliasdata['alias_php_url'], $replace);
                                //$replace_with = $replace;
                                // Replacing Tags with their suitable matches
                                $replace  = $this->replaceOtherTags($type, $search_pattern, $replace, $matches, -1);
                                $url_info = $this->explodeURL($replace, "&amp;");
                                // File Path (Example: news.php)
                                $this->pathtofile = $url_info[0];
                                if (isset($url_info[1])) {
                                    foreach ($url_info[1] as $paramkey => $paramval) {
                                        $this->get_parameters[$paramkey] = $paramval; // $this->get_parameters['thread_id'] = 1
                                    }
                                }
                                // Call the function to set server variables
                                $this->setVariables();
                                $match_found = TRUE;
                                break;
                            }
                        }
                    }
                }
            }
            if ($match_found == TRUE) {
                $this->setWarning(8, $alias); // Alias Pattern Found
                return TRUE;
            } else {
                $this->setWarning(2, $this->requesturi); // Alias Pattern Not Found
                return FALSE;
            }
        }
        return FALSE;
    }

    /**
     * Builds the Regex pattern for a specific Type string
     *
     * This function will build the Regex pattern for a specific string, which is
     * passed to the function.
     *
     * @param string $pattern The String
     * @param string $type    Type or Handler name
     * @access private
     */
    protected function makeSearchRegex($pattern, $type) {
        $regex = $pattern;
        if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
            $regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
        }
        $regex = $this->cleanRegex($regex);
        $regex = "/^".$regex."$/";

        return $regex;
    }

    /**
     * Builds the Regular Expressions Patterns
     *
     * This function will create the Regex patterns and will put the built patterns
     * in $patterns_regex array. This array will then used in preg_match function
     * to match against current request.
     * Note: Using ^ and $ made us to match exact string so that it doesn't match sub-patterns
     *
     * @access private
     */
    private function checkPattern() {
        $match_found = FALSE;

        if (is_array($this->pattern_search)) {

            foreach ($this->pattern_search as $type => $RawSearchPatterns) {

                if (!empty($RawSearchPatterns) && is_array($RawSearchPatterns)) {

                    foreach ($RawSearchPatterns as $key => $val) { // is $search

                        if (isset($this->pattern_replace[$type][$key])) { // if replace value exist

                            $search  = $val;

                            if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
                                $search = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $search);
                            }

                            $search = $this->cleanRegex($search);

                            $current_pattern                   = "~".$search."$~i";

                            // Used by debugger only
                            $this->patterns_regex[$type][$key] = $current_pattern;

                            if (preg_match($current_pattern, $this->requesturi, $matches)) {

                                $replace = $this->pattern_replace[$type][$key];

                                $url_info = $this->explodeURL($replace, "&amp;");

                                // File Path (Example: news.php) and must not be nested
                                $this->pathtofile = str_replace("../", "", $url_info[0]);

                                if (isset($url_info[1])) {
                                    foreach ($url_info[1] as $paramkey => $paramval) {
                                        $this->parameters[$paramkey] = $paramval;
                                    }
                                }
                                // Search the Value of each Tags in the Pattern for setting Server Request URI
                                if (!empty($this->parameters) && is_array($this->parameters)) {
                                    foreach ($this->parameters as $param_name => $param_rep) {
                                        $clean_param_rep = str_replace("%", "", $param_rep); // Remove % for Searching the Tag
                                        // +1 because Array key starts from 0 and matches[0] gives the complete match
                                        $pos = $this->getTagPosition($this->pattern_search[$type][$key], $clean_param_rep);
                                        if ($pos != 0) {
                                            // If the Parameter is found in the Request, then Append it to GET Parameters
                                            $this->get_parameters[$param_name] = $matches[$pos];
                                        } else {
                                            // If it is normal GET Parameter which is not tag(Example: index.php?logout=yes)
                                            $this->get_parameters[$param_name] = $param_rep;
                                        }
                                    }
                                }

                                $this->setVariables();

                                if ($match_found == TRUE) {
                                    $this->setWarning(9, $this->requesturi); // Regex pattern found
                                    break;
                                } else {
                                    $this->setWarning(3, $this->requesturi); // Regex pattern not found
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Validate current URI
     * This function will verifies if the current request is to a existing php file.
     * So we need to make a 301 Redirect to its respective permalink.
     * @access private
     */
    private function validateURI() {
        // Removes the Slash and Get the Last part of URL only
        $uri_match_found = FALSE;
        // Checking for Wrong Permalinks entered by User
        if (is_array($this->pattern_search)) {
            foreach ($this->pattern_search as $type => $values) {
                if (isset($this->dbid[$type])) {
                    foreach ($values as $key => $search) {
                        if (!$uri_match_found) {
                            if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
                                $search = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type],
                                    $search);
                                $search = $this->cleanRegex($search);
                                $search = "~^".$search."$~";
                                // If Current URI Matches with current Replace Pattern
                                if (preg_match($search, $this->requesturi, $matches)) {
                                    $uri_match_found = TRUE;
                                    //foreach ($this->dbid[$type] as $tag=>$attr) {
                                    $tag       = $this->getUniqueIDtag($type);

                                    $attr      = $this->getUniqueIDfield($type);

                                    $clean_tag = str_replace("%", "", $tag); // Remove % for Searching the Tag

                                    // +1 because Array key starts from 0 and matches[0] gives the complete match
                                    $pos = $this->getTagPosition($this->pattern_search[$type][$key], $clean_tag);

                                    if ($pos != 0) {
                                        $unique_id_value = $matches[$pos]; // This is to remove duplicate matches
                                        $target_url      = $this->pattern_search[$type][$key];
                                        // If the Pattern Info does not exist in Data Cache, then first of all, fetch it from DB
                                        if (!isset($this->data_cache[$type][$unique_id_value])) {

                                            $this->fetchDataID($type, $target_url, $unique_id_value);

                                        }
                                        // Replacing each Tag with its Database Value if any
                                        // Example: %thread_title% should be replaced with thread_subject
                                        if (isset($this->dbinfo[$type])) {
                                            foreach ($this->dbinfo[$type] as $other_tags => $other_attr) {
                                                if (strstr($target_url, $other_tags)) {
                                                    $target_url = str_replace($other_tags,
                                                        $this->data_cache[$type][$unique_id_value][$other_attr],
                                                        $target_url);
                                                }
                                            }
                                        }
                                        // Replacing each of the Tag with its suitable match found on the Page
                                        $target_url = $this->replaceOtherTags($type, $this->pattern_search[$type][$key], $target_url, $matches, -1);

                                        $target_url = $this->cleanURL($target_url);

                                        // Now check if the CURRENT URI matches with actual URL, which it should be
                                        if (strcmp($target_url, PERMALINK_CURRENT_PATH) != 0) {

                                            $this->redirect_301($target_url);

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

    /**
     * Fetch Data for a specific Type, ID and Pattern
     *
     * This function will fetch specific data on the basis of the Pattern, Type
     * and the unique ID value.
     *
     * @param string $type    The Type of Pattern
     * @param string $pattern The Specific Pattern
     * @param string $id      Unique ID Value
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
            $fetch_query     = "SELECT ".$column_names." FROM ".$dbname." WHERE ".$unique_col." IN(".$id.")"; // The Query
            $this->queries[] = $fetch_query;
            $result          = dbquery($fetch_query); // Execute Query
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    foreach ($column_arr as $key => $col_name) {
                        $this->CacheInsertDATA($type, $data[$unique_col], $col_name, $data[$col_name]);
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
     * @param string $type      Type or Handler name
     * @access private
     */
    private function CacheInsertDATA($type, $unique_id, $column, $value) {
        if (!isset($this->data_cache[$type][$unique_id])) {
            $this->data_cache[$type][$unique_id][$column] = $value;
        }
    }

    /**
     * Show Warnings or Debugging Information
     * This function will show the Warnings or Debugging Information
     * if Warnings are enabled or if Debug Mode is enabled.
     * @access private
     */
    private function displayWarnings() {
        echo "\n<div class='rewrites-queries' style='padding: 10px 10px 10px 10px; border: 3px double #225500; background-color: #ffccaa; line-height: 15px;'>\n";
        echo "<strong>Queries which were made for Rewriting:</strong><br /><br />\n";
        foreach ($this->queries as $key => $query) {
            echo $query.";<br />\n";
        }
        echo "<script type='text/javascript'>
function rewritestoggledebugdiv() {
	$('#rewrites-debug-info').slideToggle('slow');
}
</script>\n";
        echo "<input type='button' value='Toggle Rewriting Debug Information' onclick='rewritestoggledebugdiv()' />\n<br />";
        echo "Path to File: <strong>".$this->pathtofile."</strong><br />";
        echo "Request URI: <strong>".$this->requesturi."</strong><br />";
        echo "<div id='rewrites-debug-info' style='display: ".($this->pathtofile != "" ? "none" : "block").";'>\n";
        foreach ($this->warnings as $key => $val) {
            echo (intval($key) + 1).". ".$val."<br />\n";
        }
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
        echo "<hr style='border-color:#000;' />\n";
        echo "\$_GET Parameters = Array (<br />";
        foreach ($this->get_parameters as $key => $val) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;[".$key."] => ".$val."<br />";
        }
        echo ");<br />\n";
        echo "</div>\n";
        echo "</div>\n";
    }

}