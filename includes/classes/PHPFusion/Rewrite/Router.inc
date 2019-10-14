<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Router.inc
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

/**
 * Rewrite API for PHP-Fusion
 * This Rewrite API handles the Permalinks Requests
 * and map them to suitable existing URLs in website.
 * You can use this API to Add custom rules for handling requests
 */
class Router extends RewriteDriver {

    private static $router_instance = NULL;
    /**
     * True = SEF URL to FilePath
     * False = File Path to SEF URL
     *
     * @var bool
     */
    public $routing = TRUE;
    /**
     * Array of Parameters with their
     * corresponding Tags.
     * example: thread_id => %thread_id%
     *
     * @data_type Array
     * @access    private
     */
    //private $parameters = [];
    /**
     * Name of the php file which will be loaded
     * for the permalink.
     * example: news.php, articles.php
     *
     * @data_type String
     * @access    private
     */
    private $pathtofile = "";
    /**
     * Array of Parameters with their
     * actual values.
     * example: thread_id => 1, rowstart => 20
     *
     * @data_type Array
     * @access    private
     */
    private $get_parameters = [];

    public function __construct() {
        // Pretty URL
        $this->requesturi = rtrim(urldecode($this->removeParam(PERMALINK_CURRENT_PATH)), '/');
    }

    private function removeParam($urlValue) {
        $urlArray = explode('?', $urlValue);
        if (isset($urlArray)) {
            return $urlArray[0];
        }
        return $urlValue;
    }

    public static function getRouterInstance() {
        if (self::$router_instance === NULL) {
            self::$router_instance = new static();
        }

        return self::$router_instance;
    }

    public function get_FileParams() {
        return $this->get_parameters;
    }

    /**
     * @param mixed $pathtofile
     */
    public function setPathtofile($pathtofile) {
        $this->pathtofile = $pathtofile;
    }

    /**
     * @param mixed $get_parameters
     */
    public function setGetParameters($get_parameters) {
        $this->get_parameters = $get_parameters;
    }


    /**
     * Call all the functions to process rewrite detection and further actions.
     * This will call all the other functions after all the included files have been included
     * and all the patterns have been made.
     *
     * @access public
     */
    public function rewritePage() {
        // Import the required Handlers
        $this->loadSQLDrivers();
        // Include the Rewrites
        $this->includeRewrite();
        // Import Patterns from DB
        $this->importPatterns();
        // Prepare Search Strings
        $this->prepare_searchRegex();
        // Check if there is any Alias matching with current URL
        if (!$this->checkAlias()) {
            // Check if any Alias Pattern is matching with current URL
            if (!$this->checkAliasPatterns()) {
                // Check if any Pattern is matching with current URL
                $this->checkPattern();
            }
        }
        // If path to File is empty, set a warning
        if ($this->pathtofile == "") {
            $this->setWarning(6);
        }

        if (fusion_get_settings('debug_seo') && iSUPERADMIN && iADMIN) {
            $this->displayWarnings();
        }
    }

    /**
     * Import the Available Patterns from Database
     * This will Import all the available Patterns for the Handlers
     * from the Database and put it into $pattern_search and
     * $pattern_replace array.
     *
     * @access private
     */
    private function importPatterns() {
        if (!empty($this->handlers)) {
            $types = [];
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
                    } else if ($data['pattern_cat'] == "alias") {
                        $this->alias_pattern[$data['rewrite_name']][$data['pattern_source']] = $data['pattern_target'];
                    }
                }
            }
        }
    }

    /**
     * Checks if there is any matching Alias or not
     * This function will check if there is any matching Alias for the URI or not.
     *
     * @access private
     */
    private function checkAlias() {
        // Check if there is any Alias matching the current URI
        $query = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_url=:url LIMIT 1";
        $result = dbquery($query, [':url' => stripinput($this->requesturi)]);
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
     *
     * @param string $url     The Permalink URL (incomplete)
     * @param string $php_url The PHP URL (incomplete)
     * @param string $type    Type of Alias
     *
     * @access private
     * @return mixed|string
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
     * This function will explode the URL into its Filename and Get Parameters
     * Example: viewthread.php?thread_id=1&amp;rowstart=20
     * then :
     * array[0] => viewthread.php
     * array[1] => array(
     * [thread_id] => 1
     * [rowstart] => 20
     * )
     *
     * @param string $url             The URL
     * @param string $param_delimiter The Parameters to explode by
     *
     * @access private
     * @return array
     */
    private function explodeURL($url, $param_delimiter = "&amp;") {
        $url_info = [];
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
     *
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
    public function setservervars() {
        if (!empty($this->pathtofile)) {
            $_SERVER['PHP_SELF'] = preg_replace("/index\.php/", $this->pathtofile, $_SERVER['PHP_SELF'], 1);
            $_SERVER['SCRIPT_NAME'] = preg_replace("/index\.php/", $this->pathtofile, $_SERVER['SCRIPT_NAME'], 1);
        }
    }

    /**
     * Set the new QUERY_STRING
     * This function will set the values of QUERY_STRING to new value
     * which is calculated in buildParams().
     *
     * @access private
     */
    public function setquerystring() {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING']."&amp;".$this->buildParams();
        } else {
            $_SERVER['QUERY_STRING'] = $this->buildParams();
        }
    }

    /**
     * Builds the $_GET parameters
     * This function will build the GET parameters and also the Query String.
     *
     * @access private
     */
    private function buildParams() {
        $total = count($this->get_parameters);
        $i = 1;
        $query_str = "";
        foreach ($this->get_parameters as $key => $val) {

            $_GET[$key] = $val; // This is where $_GET is produced.

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
                    $search = $this->makeSearchRegex($search, $type);
                    $search = str_replace("%alias%", "(.*?)", $search);
                    if (preg_match($search, $this->requesturi, $matches)) {
                        $alias_pos = $this->getTagPosition($search_pattern, "%alias%");
                        if ($alias_pos != 0) {
                            // The Alias is Detected !
                            $alias = $matches[$alias_pos];
                            // Now search for this Alias in Database
                            $query = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_url=:url LIMIT 1";
                            $result = dbquery($query, [':url' => $alias]);
                            $this->queries[] = $query;
                            if (dbrows($result)) {
                                $aliasdata = dbarray($result);
                                // Replace the %alias_target% in the Replacement pattern
                                $replace = str_replace("%alias_target%", $aliasdata['alias_php_url'], $replace);
                                //$replace_with = $replace;
                                // Replacing Tags with their suitable matches
                                $replace = $this->replaceOtherTags($type, $search_pattern, $replace, $matches, -1);
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
     * This function will build the Regex pattern for a specific string, which is
     * passed to the function.
     *
     * @param string $pattern
     * @param string $type
     *
     * @return mixed|string
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

                    foreach ($RawSearchPatterns as $key => $search) {

                        if ($match_found == FALSE) {

                            if (isset($this->pattern_replace[$type][$key]) && isset($this->pattern_search[$type][$key])) {

                                $search_pattern = $this->pattern_search[$type][$key];
                                $replace_pattern = $this->pattern_replace[$type][$key];

                                if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
                                    $search = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $search);
                                }

                                $search = "~".$this->cleanRegex($search)."$~i";

                                if (preg_match($search, $this->requesturi, $matches)) {

                                    $url_info = $this->explodeURL($replace_pattern, "&amp;");

                                    $this->pathtofile = str_replace("../", "", $url_info[0]);

                                    preg_match_all($search, $this->requesturi, $url_matches, PREG_SET_ORDER);

                                    if (isset($url_info[1])) { // indicate has $_GET request
                                        /**
                                         * File path is in search pattern
                                         */
                                        preg_match_all("~%(.*?)%~i", $search_pattern, $tag_matches);

                                        $tag_values = [];

                                        if (!empty($tag_matches[0])) {

                                            $tagData = array_combine(range(1, count($tag_matches[0])), array_values($tag_matches[0]));

                                            $tagRequests = array_combine(range(1, count($tag_matches[0])), array_values($tag_matches[1]));

                                            foreach ($tagData as $tagKey => $tagVal) {
                                                $tag_values[$tagRequests[$tagKey]] = $matches[$tagKey];
                                            }

                                            $this->dataStatements[$type][] = $tag_values;

                                            $urlParams = array_combine(array_values($tagData), array_values($tag_values));
                                        }

                                        /**
                                         * Read the Request URL pattern
                                         */

                                        if (isset($url_info[1]) && !empty($urlParams)) {

                                            $parameters = [];

                                            foreach ($url_info[1] as $paramKey => $paramVal) {

                                                if (!$paramVal) {
                                                    $paramVal = TRUE;
                                                }

                                                $value = (isset($tag_values[$paramKey])) ? $tag_values[$paramKey] : $paramVal;

                                                // If key is not val, (i.e. such as post_id=%thread_id%) the below will find and insert value
                                                if (stristr($value, "%")) {
                                                    $value = (isset($urlParams[$value])) ? $urlParams[$value] : $paramVal;
                                                }

                                                $parameters[$paramKey] = $value;
                                            }

                                            $this->get_parameters = $parameters;

                                        }

                                        $this->setVariables();

                                        $this->setWarning(9, $this->requesturi); // Regex pattern found

                                        break;
                                    }
                                }
                            } else {
                                die("You do not have correct Permalink rules Driver. See that your driver is properly set!");
                            }
                        } else {
                            $this->setWarning(3, $this->requesturi); // Regex pattern not found
                        }
                    }
                }
            }
        }
    }

    /**
     * Show Warnings or Debugging Information
     * This function will show the Warnings or Debugging Information
     * if Warnings are enabled or if Debug Mode is enabled.
     *
     * @access private
     */
    private function displayWarnings() {
        echo "<div class='rewrites-queries' style='padding: 10px 10px 10px 10px; border: 1px double #000; background-color: #fbfbfb; line-height: 15px;'>\n";
        echo "<strong>Queries which were made for Rewriting:</strong><br /><br />\n";
        foreach ($this->queries as $key => $query) {
            echo $query.";<br />\n";
        }
        add_to_footer("<script type='text/javascript'>
        var el = document.getElementById('rewritestoggledebugdiv');
        
        if (el) {
            el.addEventListener('click', function() {
                var el2 = document.getElementById('rewritesdebuginfo');
                
                if (el2.style.display === 'none') {
                    el2.style.display = 'block';
                } else {
                    el2.style.display = 'none';
                }
            });
        }
        </script>");
        echo "<input type='button' class='btn btn-default btn-sm' value='Toggle Rewriting Debug Information' id='rewritestoggledebugdiv' />\n<br />";
        echo "Path to File: <strong>".$this->pathtofile."</strong><br />";
        echo "Request URI: <strong>".$this->requesturi."</strong><br />";
        echo "<div id='rewritesdebuginfo' style='display: ".($this->pathtofile != "" ? "none" : "block").";'>\n";
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
        echo "Module Property Information:<br />";
        foreach ($this->pattern_tables as $type => $val) {
            $propHTML = '';
            foreach ($val as $val_pattern => $arrayProp) {
                $propHTML .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- {$val_pattern} will be translated on ".$arrayProp['table']." WHERE ".$arrayProp['primary_key']." is equals to $val_pattern<br/>\n";
            }
            echo "&nbsp;&nbsp;&nbsp;&nbsp;".$type." rewrite module <br/>".$propHTML."<br />";
        }
        echo ");<br />\n";
        echo "<hr style='border-color:#000;' />\n";
        echo "Data Cache = Array (<br />";
        foreach ($this->dataStatements as $type => $info) {
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

    /**
     * Index Include File
     * Returns the filename of the php file which will be included.
     * If this file is blank, index.php will redirect to 404 error page
     *
     * @access public
     */
    public function getFilePath() {
        return $this->pathtofile;
    }

    /**
     * Get the Current URL
     *
     * @return string
     */
    public function getCurrentURL() {
        return $this->pathtofile.(!empty($this->get_parameters) ? '?'.http_build_query($this->get_parameters, '', '&amp;') : '');
    }
}
