<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Permalinks.php
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
 * Class Permalinks
 *
 * @package PHPFusion\Rewrite
 */
class Permalinks extends RewriteDriver {

    private static $permalink_instance = NULL;

    /**
     * Get the instance of the class
     *
     * @return static
     */
    public static function getPermalinkInstance() {
        if (self::$permalink_instance === NULL) {
            self::$permalink_instance = new static();
        }

        return self::$permalink_instance;
    }


    /**
     * Returns the Output
     * This function will first call the handleOutput() and then it will return the
     * modified Output for SEO.
     *
     * @param string $output
     *
     * @return string
     */
    public function getOutput($output) {
        $this->setOutput($output);
        $this->handleOutput();

        return $this->output;
    }

    /**
     * Main Function : Handles the Output
     * This function will Handle the output by calling several functions
     * which are used in this Class.
     */
    private function handleOutput() {
        // Buffers for Permalink - Using New Driver Pattern
        $this->handlePermalinkRequests();
        // Output and Redirect 301 if NON-SEO url found
        $this->replaceOutput();
        // Prepend all the File/Images/CSS/JS etc. Links with ROOT path
        /*
         * This does not apply to page that is not SEO enabled driver.
         * i.e. IN_PERMALINK is only defined when Browser URL is pretty url
         * The exception found is at Error.php because there is an error path handler that must work
         * for both SEO page and NON SEO page
         */
        if (defined('IN_PERMALINK')) {
            $file = Router::getRouterInstance()->getFilePath();
            if ($file !== 'error.php') {
                $this->appendRootAll();
            }
        }
    }

    /**
     * Do full replacement of the HTML output
     */
    private function replaceOutput() {
        // Pattern translation
        if (!empty($this->regex_statements['pattern'])) {
            foreach ($this->regex_statements['pattern'] as $rules) {
                foreach ($rules as $search => $replace) {
                    $this->output = preg_replace($search, $replace, $this->output);
                }
            }
            //print_p($this->output);
        }

        // Alias translation
        if (!empty($this->regex_statements['alias'])) {
            foreach ($this->regex_statements['alias'] as $rules) {
                $_patterns = flatten_array($rules);
                foreach ($_patterns as $search => $replace) {
                    $this->output = preg_replace($search, $replace, $this->output);
                }
            }

        }

        // Alias Redirecting
        if (!empty($this->regex_statements['alias_redirect'])) {
            foreach ($this->regex_statements['alias_redirect'] as $rules) {
                $_patterns = flatten_array($rules);
                foreach ($_patterns as $search => $replace) {
                    if (preg_match($search, PERMALINK_CURRENT_PATH, $matches)) {
                        $this->redirect301($replace);
                    }
                }
            }
        }
    }

    /**
     * Append File Root
     * Append the ROOT Dir Path to all relative links, which are from website
     * This function will append the root directory path for all links, which
     * are in website. (Not External HTTP links)
     */
    protected function appendRootAll() {
        if (preg_match("/(href|src| action)='((?!(htt|ft)p(s)?:\/\/)[^\']*)'/i", $this->output)) {
            $basedir = str_replace([".", "/"], ["\.", "\/"], BASEDIR);
            $basedir = preg_replace("~(href|src| action)=(\'|\")(".$basedir.")*([^(\'|\"):]*)(\'|\")~i", "$1=$2".ROOT."$3$4$5", $this->output);
            // Remove ../ before http://
            $loop = 7;
            for ($i = 1; $i <= $loop; $i++) {
                $basedir = str_replace(str_repeat('../', $i).'http://', 'http://', $basedir);
            }
            // Remove ../ before https://
            for ($i = 1; $i <= $loop; $i++) {
                $basedir = str_replace(str_repeat('../', $i).'https://', 'https://', $basedir);
            }
            $basedir = str_replace("..//", "../", $basedir);
            $this->output = $basedir;
        }
    }

    /**
     * Attempt to handle url routing
     *
     * @param string $content
     */
    public function handleUrlRouting($content) {
        $this->setOutput($content);
        $this->requesturi = PERMALINK_CURRENT_PATH;
        // Import the required Handlers
        $this->loadSqlDrivers();
        // Include the Rewrites
        $this->includeRewrite();
        // Read from DB
        $this->verifyHandlers();
        // Prepare the strings
        $this->importPatterns();
        // Prepare search strings against buffers and URI
        $this->prepareSearchRegex();
        // Redirect if something happens
        $this->handleNonSeoUrl();
    }

    /**
     * Import the Available Patterns from Database
     * This will Import all the available Patterns for the Handlers
     * from the Database and put it into $pattern_search and
     * $pattern_replace array.
     *
     * @access private
     */
    protected function importPatterns() {
        if (!empty($this->handlers)) {
            $types = [];
            foreach ($this->handlers as $value) {
                $types[] = "'".$value."'"; // When working on string, the values should be inside single quotes.
            }

            $types_str = implode(",", $types);
            $query = "SELECT r.rewrite_name, p.pattern_type, p.pattern_source, p.pattern_target, p.pattern_cat FROM ".DB_PERMALINK_METHOD." p INNER JOIN ".DB_PERMALINK_REWRITE." r WHERE r.rewrite_id=p.pattern_type AND r.rewrite_name IN(".$types_str.") ORDER BY p.pattern_type";
            $this->queries[] = $query;
            $result = dbquery($query);

            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    if ($data['pattern_cat'] == "normal") {
                        $this->pattern_search[$data['rewrite_name']][] = $data['pattern_target'];
                        $this->pattern_replace[$data['rewrite_name']][] = $data['pattern_source'];
                    } else if ($data['pattern_cat'] == "alias") {
                        $this->alias_pattern[$data['rewrite_name']][$data['pattern_source']] = $data['pattern_target'];
                    }
                }
            }

        }
    }
}
