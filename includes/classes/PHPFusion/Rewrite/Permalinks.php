<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Permalinks.php
| Author: Frederick MC Chan (Hien)
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

class Permalinks extends RewriteDriver {

    public $debug_regex = TRUE;

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

        // Import the required Handlers
        $this->loadSQLDrivers();

        // Include the Rewrites
        $this->includeRewrite();

        // Read from DB
        $this->verifyHandlers();

        // Include the files
        $this->includeHandlers();

        // Prepare the strings
        $this->importPatterns();

        // Prepare search strings against buffers and URI
        $this->prepare_searchRegex();

        $this->handle_non_seo_url();

        // Buffers for Permalink - Using New Driver Pattern
        $this->handle_permalink_requests();

        // Output and Redirect 301 if NON-SEO url found
        $this->replace_output();

        // Prepend all the File/Images/CSS/JS etc Links with ROOT path
        $this->appendRootAll();

        // For Developer, to see what is happening behind
        if ($settings['debug_seo'] == "1") {

        }
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
    protected function importPatterns() {
        if (!empty($this->handlers)) {
            $types = array();
            foreach ($this->handlers as $key => $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }

            $types_str       = implode(",", $types);
            $query           = "SELECT r.rewrite_name, p.pattern_type, p.pattern_source, p.pattern_target, p.pattern_cat FROM ".DB_PERMALINK_METHOD." p INNER JOIN ".DB_PERMALINK_REWRITE." r WHERE r.rewrite_id=p.pattern_type AND r.rewrite_name IN(".$types_str.") ORDER BY p.pattern_type";
            $this->queries[] = $query;
            $result          = dbquery($query);

            if (dbrows($result)>0) {
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
     * Do full replacement of the HTML output
     */
    private function replace_output() {
        // Pattern translation
        if (!empty($this->regex_statements['pattern'])) {
            foreach ($this->regex_statements['pattern'] as $handler => $rules) {
                foreach ($rules as $search => $replace) {
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
    }

    /**
     * Append File Root
     *
     * Append the ROOT Dir Path to all relative links, which are from website
     * This function will append the root directory path for all links, which
     * are in website. (Not External HTTP links)
     */
    protected function appendRootAll() {
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

    private function prepare_alias_lookup() {
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
                    $alias_php_url = $this->getAliasURL($alias['alias_url'], $alias['alias_php_url'],
                                                        $alias['alias_type']);
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

                            $alias_search = str_replace($this->rewrite_code[$field], $this->rewrite_replace[$field],
                                                        $search_string);
                            $alias_search = $this->cleanRegex($alias_search);
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
                                    $match = $this->cleanRegex($match);
                                    // Replace Tags with their suitable matches
                                    $replace_str = $this->replaceOtherTags($field, $search_string, $replace_str,
                                                                           $matches, $count);
                                    $replace_str = $this->cleanURL($replace_str);

                                    $this->regex_statements['alias'][$field][] = array($match => $replace_str);
                                    $this->regex_statements['alias_redirect'][$field][] = array($alias_search => $replace_str);
                                }
                            } else {
                                $this->regex_statements['failed_alias'][$field][] = array(
                                    "search" => $search, "status" => "failed"
                                );
                            }
                        }
                    }
                    $this->aliases[] = $alias;
                }
            }
        }
    }

    /**
     * Get Alias URL for Permalink
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
    private function getAliasURL($url, $php_url, $type) {
        $return_url = array();
        // 1 => $search, 2 => $replace
        if (isset($this->alias_pattern[$type]) && is_array($this->alias_pattern[$type])) {
            foreach ($this->alias_pattern[$type] as $search => $replace) {
                $search = str_replace("%alias%", $url, $search);
                $replace = str_replace("%alias_target%", $php_url, $replace);
                if ($replace == PERMALINK_CURRENT_PATH) {
                    $return_url[] = $search;
                    $return_url[] = $replace;
                }
            }
        }
        return $return_url;
    }
}