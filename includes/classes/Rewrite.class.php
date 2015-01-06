<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Rewrite.class.php
| Author: Ankur Thakur
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/*
| Rewrite API for PHP-Fusion
|
| This Rewrite API handles the Permalinks Requests
| and map them to suitable existing URLs in website.
|
| You can use this API to Add custom rules for handling
| requests.
*/

use PHPFusion\PermalinksDisplay;

class Rewrite {
	/*
	* Portion of the URL to match in the Regex
	* @data_type String
	* @access private
	*/
	private $requesturi = "";
	/*
	* Name of the php file which will be loaded
	* for the permalink.
	* example: news.php, articles.php
	* @data_type String
	* @access private
	*/
	private $pathtofile = "";
	/*
	* Array of Handlers
	* example: news, threads, articles
	* @data_type Array
	* @access private
	*/
	private $handlers = array();
	/*
	* Tags for the permalinks.
	* example: %thread_id%, %news_id%
	* @data_type Array
	* @access private
	*/
	private $rewrite_code = array();
	/*
	* Replacement for Tags for REGEX.
	* example: %thread_id% should be replaced with ([0-9]+)
	* @data_type Array
	* @access private
	*/
	private $rewrite_replace = array();
	/*
	* Permalink Patterns which will be searched
	* to match against current request.
	* @data_type Array
	* @access private
	*/
	private $pattern_search = array();
	/*
	* Target URLs to which permalink request
	* will be rewrited.
	* @data_type Array
	* @access private
	*/
	private $pattern_replace = array();
	/*
	* Array of Regular Expressions Patterns
	* which are made for matching.
	* @data_type Array
	* @access private
	*/
	private $patterns_regex = array();
	/*
	* Array of Pattern for Aliases
	* which are made for matching.
	* @data_type Array
	* @access private
	*/
	private $alias_pattern = array();
	/*
	* Array of Parameters with their
	* corresponding Tags.
	* example: thread_id => %thread_id%
	* @data_type Array
	* @access private
	*/
	private $parameters = array();
	/*
	* Array of Parameters with their
	* actual values.
	* example: thread_id => 1, rowstart => 20
	* @data_type Array
	* @access private
	*/
	private $get_parameters = array();
	/*
	* Array of DB Table Names
	* example: prefix_news, prefix_threads, prefix_articles
	* @data_type Array
	* @access private
	*/
	private $dbname = array();
	/*
	* Array of Unique IDs and its
	* corresponding Tags.
	* Example: news_id is Unique in DB_NEWS
	* and %news_id% is URL is to be treated as news_id
	* So, Array is: array("%news_id%" => "news_id")
	* @data_type Array
	* @access private
	*/
	private $dbid = array();
	/*
	* Array of Other Columns which
	* can be fetched and used in the
	* URL.
	* Example: If we want to including user_name
	* then Array will look like: array("%user_name%" => "user_name")
	* @data_type Array
	* @access private
	*/
	private $dbinfo = array();
	/*
	* Array of Data fetched from the DB Tables
	* It contains the Data in the structured form.
	* @data_type Array
	* @access private
	*/
	private $data_cache = array();
	/*
	* Array of Total Queries which were run.
	* @data_type Array
	* @access private
	*/
	private $queries = array();
	/*
	* Array of Warnings
	* @data_type Array
	* @access private
	*/
	private $warnings = array();
	/*
	* Debugging (Show Debug Info or not)
	* @data_type Boolean
	* @access public
	*/
	public $debug = true;
	/*
	* Constructor
	*
	* @access public
	*/
	public function __construct() {
		$this->requesturi = urldecode(PERMALINK_CURRENT_PATH);
		//$this->requesturi = PERMALINK_CURRENT_PATH;
	}

	/*
	* Clean the URI String for MATCH/AGAINST in MySQL
	*
	* This function will Clean the string and removes any unwanted characters from it.
	*
	* @access private
	*/
	private function cleanString($mystr = "") {
		$res = "";
		$search = array("&", "\"", "'", "\\", "\'", "<", ">");
		$res = str_replace($search, "", $mystr);
		return $res;
	}

	/*
	* Call all the functions to process rewrite detection and further actions.
	*
	* This will call all the other functions after all the included files have been included
	* and all the patterns have been made.
	*
	* @access public
	*/
	public function rewritePage() {
	global $settings;
		// Import the required Handlers
		$this->importHandlers();
		// Include the Rewrites
		$this->includeRewrite();
		// Import Patterns from DB
		$this->importPatterns();
		// Check if there is any Alias matching with current URI
		if (!$this->checkAlias()) {
			// Check if any Alias Pattern is matching with current URI
			if (!$this->checkAliasPatterns()) {
				// Else, do the normal pattern checking
				// Make Regular Expression of Patterns
				$this->makeRegex();
				// Check if any Pattern found
				$this->checkPattern();
				$this->validateURI();
			}
		}
		// If path to File is empty, set a warning
		if ($this->pathtofile == "") {
			$this->setWarning(6);
		}
		if ($settings['debug_seo'] == "1") {
		// If any Warnings to be shown, or in Debug mode
			if ($this->debug) {
				$this->displayWarnings();
			}
		}
	}

	/*
	* Import Handlers from Database
	*
	* This will import all the Enabled Handlers from the Database Table
	*
	* @access private
	*/
	private function importHandlers() {
		$query = "SELECT rewrite_name FROM ".DB_PERMALINK_REWRITE;
		$result = dbquery($query);
		$this->queries[] = $query;
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$this->AddRewrite($data['rewrite_name']);
			}
		}
	}

	/*
	* Add the rewrite include file to be included
	*
	* This will Add new rewrite include file to be included.
	*
	* @param string $include_prefix Prefix of the file to be included.
	* @access private
	*/
	private function AddRewrite($include_prefix) {
		// Include the include_rewrite_include.php file
		if ($include_prefix != "" && !in_array($include_prefix, $this->handlers)) {
			$this->handlers[] = $include_prefix;
		}
	}

	/*
	* Include the rewrite include file
	*
	* The include file will be included from
	* INCLUDES."rewrites/".PREFIX."_rewrite_include.php
	*
	* @access private
	*/
	private function includeRewrite() {
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

	/*
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

	/*
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

	private function getAliasURL($url, $php_url, $type) {
		$return_url = "";
		if (isset($this->alias_pattern) && array_key_exists($type, $this->alias_pattern) && is_array($this->alias_pattern[$type])) {
			//$match_found = false;
			foreach ($this->alias_pattern[$type] as $search => $replace) {
				$search = str_replace("%alias%", $url, $search);
				$replace = str_replace("%alias_target%", $php_url, $replace);
				if ($search == $this->requesturi) {
					$return_url = $replace;
				}
			}
			return $return_url;
		} else {
			return $php_url;
		}
	}

	/*
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
							$query = "SELECT * FROM ".DB_PERMALINK_ALIAS." WHERE alias_url='".$alias."' LIMIT 1";
							$result = dbquery($query);
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
			if ($match_found) {
				$this->setWarning(8, $alias); // Alias Pattern Found
				return TRUE;
			} else {
				$this->setWarning(2, $this->requesturi); // Alias Pattern Not Found
				return FALSE;
			}
		}
	}

	/*
	* Match all the REGEX Patterns against current Request
	*
	* This is the function which will match the current page Request
	* against all the Search patterns from the Rewrite Include files.
	*
	* @access private
	*/
	private function checkPattern() {
		$match_found = FALSE;
		if (is_array($this->pattern_search)) {
			foreach ($this->pattern_search as $type => $values) {
				if (is_array($this->pattern_search[$type])) {
					foreach ($this->pattern_search[$type] as $key => $search) {
						if (!$match_found && preg_match($this->patterns_regex[$type][$key], $this->requesturi, $matches)) {
							$url_info = $this->explodeURL($this->pattern_replace[$type][$key], "&amp;");
							// File Path (Example: news.php)
							$this->pathtofile = $url_info[0];
							if (isset($url_info[1])) {
								foreach ($url_info[1] as $paramkey => $paramval) {
									$this->parameters[$paramkey] = $paramval; // $this->parameters['thread_id'] = %id%
								}
							}
							// Search the Value of each Tags in the Pattern
							if (is_array($this->parameters)) {
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
							// Call the function to set server variables
							$this->setVariables();
							// Set $match_found to TRUE so that the Loop terminates
							$match_found = TRUE;
							break;
						}
					}
				}
				if ($match_found == TRUE) {
					$this->setWarning(9, $this->requesturi); // Regex pattern found
					return TRUE;
				}
			}
			if ($match_found == FALSE) {
				$this->setWarning(3, $this->requesturi); // Regex pattern not found
				return FALSE;
			}
		}
	}

	/*
	* Validate current URI
	*
	* This function will verifies if the current request is to a existing php file.
	* So we need to make a 301 Redirect to its respective permalink.
	*
	* @access private
	*/
	private function validateURI() {
		// Removes the Slash and Get the Last part of URL only
		$current_uri = $this->requesturi;
		$uri_match_found = FALSE;
		// Checking for Wrong Permalinks entered by User
		if (is_array($this->pattern_search)) {
			foreach ($this->pattern_search as $type => $values) {
				if (isset($this->dbid[$type])) {
					foreach ($values as $key => $search) {
						if (!$uri_match_found) {
							if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
								$search = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $search);
								$search = $this->cleanRegex($search);
								$search = "#^".$search."#";
								// If Current URI Matches with current Replace Pattern
								if (preg_match($search, $current_uri, $matches)) {
									$uri_match_found = TRUE;
									//foreach ($this->dbid[$type] as $tag=>$attr) {
									$tag = $this->getUniqueIDtag($type);
									$attr = $this->getUniqueIDfield($type);
									$clean_tag = str_replace("%", "", $tag); // Remove % for Searching the Tag
									// +1 because Array key starts from 0 and matches[0] gives the complete match
									$pos = $this->getTagPosition($this->pattern_search[$type][$key], $clean_tag);
									if ($pos != 0) {
										$unique_id_value = $matches[$pos]; // This is to remove duplicate matches
										$target_url = $this->pattern_search[$type][$key];
										// If the Pattern Info does not exist in Data Cache, then first of all, fetch it from DB
										if (!isset($this->data_cache[$type][$unique_id_value])) {
											$this->fetchDataID($type, $target_url, $unique_id_value);
										}
										// Replacing each Tag with its Database Value if any
										// Example: %thread_title% should be replaced with thread_subject
										if (isset($this->dbinfo[$type])) {
											foreach ($this->dbinfo[$type] as $other_tags => $other_attr) {
												if (strstr($target_url, $other_tags)) {
													$target_url = str_replace($other_tags, $this->data_cache[$type][$unique_id_value][$other_attr], $target_url);
												}
											}
										}
										// Replacing each of the Tag with its suitable match found on the Page
										$target_url = $this->replaceOtherTags($type, $this->pattern_search[$type][$key], $target_url, $matches, -1);
										$target_url = $this->cleanURL($target_url);
										// Now check if the CURRENT URI matches with actual URL, which it should be
										if (strcmp($target_url, $current_uri) != 0) {
											$this->mpRedirect($target_url);
										}
									}
									//}
								}
							}
						}
					}
				}
			}
		}
	}

	/*
	* mpRedirect : Moved Permanently Redirect
	*
	* This function will redirect to a URL by giving 301 HTTP status.
	*
	* @param string $target The Target URL
	* @access private
	*/
	private function mpRedirect($target) {
		global $settings;
		ob_get_contents();
		if (ob_get_length() !== FALSE) {
			ob_end_clean();
		}
		//$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		//$last = substr(strrchr($url, "/"), 1);
		//$url = str_replace($last, $target, $url);
		$url = $settings['siteurl'].$target;
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ".$url);
		exit();
	}

	/*
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

	/*
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
			$unique_col = $this->getUniqueIDfield($type); // The Unique Column name for WHERE condition
			$column_arr[] = $unique_col; // Also fetch the Unique_ID like news_id, thread_id
			$column_names = implode(",", $column_arr); // Array to String conversion for MySQL Query
			$dbname = $this->dbname[$type]; // Table Name in Database
			$fetch_query = "SELECT ".$column_names." FROM ".$dbname." WHERE ".$unique_col." IN(".$id.")"; // The Query
			$this->queries[] = $fetch_query;
			$result = dbquery($fetch_query); // Execute Query
			if (dbrows($result)) {
				while ($data = dbarray($result)) {
					foreach ($column_arr as $key => $col_name) {
						$this->CacheInsertDATA($type, $data[$unique_col], $col_name, $data[$col_name]);
					}
				}
			}
		}
	}

	/*
	* Inserts the Data into the DATA_Cache array
	*
	* This will Insert the Data fetched from the DB into the DATA_Cache array. The columns data will
	* be stored in form of array.
	* Example: [1] => Array(
							[news_id] => 1,
							[news_subject] => Hello. I am Ankur.
							)
	*
	* @param string $unique_id Represents the Unique ID, of the Info. (It is 1 in the above example)
	* @param string $column Column Name of the data (news_subject etc)
	* @param string $value Value of the Column or the Data to be stored
	* @param string $type Type or Handler name
	* @access private
	*/
	private function CacheInsertDATA($type, $unique_id, $column, $value) {
		if (!isset($this->data_cache[$type][$unique_id])) {
			$this->data_cache[$type][$unique_id][$column] = $value;
		}
	}

	/*
		* Cleans the URL
		*
		* Thanks to "THE PERFECT PHP CLEAN URL GENERATOR"(http://cubiq.org/the-perfect-php-clean-url-generator)
		*
		* This function will clean the URL by removing any unwanted characters from it and
		* only allowing alphanumeric and - in the URL.
		* This function can be customized according to your needs.
		*
		* @param string $string The URL String
		* @access private
		*/
	private function cleanURL($string, $delimiter = "-") {
		/* Alias of Static Function of PermalinksDisplay */
		require_once dirname(__FILE__)."/PermalinksDisplay.class.php";
		$string = PermalinksDisplay::cleanURL($string, $delimiter);
		return $string;
	}

	/*
	* Get the Tag of the Unique ID type
	*
	* Example: For news, unique ID should be news_id
	* So it will return %news_id% because of array("%%news_id" => "news_id")
	*
	* @param string $type Type or Handler name
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

	/*
	* Get the Field of the Unique ID type
	*
	* Example: For news, unique ID should be news_id
	* So it will return news_id because of array("%%news_id" => "news_id")
	*
	* @param string $type Type or Handler name
	* @access private
	*/
	private function getUniqueIDfield($type) {
		$field = "";
		if (isset($this->dbid[$type]) && is_array($this->dbid[$type])) {
			$res = array_values($this->dbid[$type]);
			$field = $res[0];
		}
		return $field;
	}

	/*
	* Adds the DB Table Name into the DB_Names array
	*
	* This will Add DB Table Names into the array, which are further used in MySQL Query.
	*
	* @param string $dbname Name of the Table
	* @param string $type Type or Handler name
	* @access private
	*/
	private function addDbname($dbname, $type) {
		$this->dbname[$type] = $dbname;
	}

	/*
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
	private function addDbid($dbid, $type) {
		$this->dbid[$type] = $dbid;
	}

	/*
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
	private function addDbinfo($dbinfo, $type) {
		$this->dbinfo[$type] = $dbinfo;
	}

	/*
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
	private function addRegexTag($regex, $type) {
		foreach ($regex as $reg_search => $reg_replace) {
			$this->rewrite_code[$type][] = $reg_search;
			$this->rewrite_replace[$type][] = $reg_replace;
		}
	}

	/*
	* Explodes a URL into Filename and Get Parameters
	*
	* This function will explode the URL into its Filename and Get Parameters
	* Example: viewthread.php?thread_id=1&amp;rowstart=20
	* then :
		array[0] => viewthread.php
		array[1] => array(
						[thread_id] => 1
						[rowstart] => 20
					)
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
			$get_params = array();
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

	/*
	* Calculates the Tag Position in a given pattern.
	*
	* This function will calculate the position of a given Tag in a given pattern.
	* Example: %id% is at 2 position in articles-%title%-%id%
	*
	* @param string $pattern The Pattern string in which particular Tag will be searched.
	* @param string $search The Tag which will be searched.
	* @access private
	*/
	private function getTagPosition($pattern, $search) {
		if (preg_match_all("#%([a-zA-Z0-9_]+)%#i", $pattern, $matches)) {
			$key = array_search($search, $matches[1]);
			return intval($key+1);
		} else {
			$this->setWarning(5, $search);
			return 0;
		}
	}

	/*
	* Builds the Regular Expressions Patterns
	*
	* This function will create the Regex patterns and will put the built patterns
	* in $patterns_regex array. This array will then used in preg_match function
	* to match against current request.
	* Note: Using ^ and $ made us to match exact string so that it doesn't match sub-patterns
	*
	* @access private
	*/
	private function makeRegex() {
		if (is_array($this->pattern_search)) {
			foreach ($this->pattern_search as $type => $values) {
				if (is_array($this->pattern_search[$type])) {
					foreach ($this->pattern_search[$type] as $key => $val) {
						$regex = $val;
						$regex = $this->cleanRegex($regex);
						if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
							$regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
						}
						$this->patterns_regex[$type][$key] = "/^".$regex."$/"; // This REGEX will make finding exact match rather than subpatterns.
					}
				}
			}
		}
	}

	/*
	* Builds the Regex pattern for a specific Type string
	*
	* This function will build the Regex pattern for a specific string, which is
	* passed to the function.
	*
	* @param string $pattern The String
	* @param string $type Type or Handler name
	* @access private
	*/
	private function makeSearchRegex($pattern, $type) {
		$regex = $pattern;
		if (isset($this->rewrite_code[$type]) && isset($this->rewrite_replace[$type])) {
			$regex = str_replace($this->rewrite_code[$type], $this->rewrite_replace[$type], $regex);
		}
		$regex = $this->cleanRegex($regex);
		$regex = "/^".$regex."$/";
		return $regex;
	}

	/*
	* Clean the REGEX by escaping some characters
	*
	* This function will escape some characters in the Regex expression
	*
	* @param string $regex The expression String
	* @access private
	*/
	private function cleanRegex($regex) {
		$regex = str_replace("#", "\#", $regex);
		$regex = str_replace("/", "\/", $regex);
		$regex = str_replace(".", "\.", $regex);
		$regex = str_replace("?", "\?", $regex);
		return $regex;
	}

	/*
	* Builds the $_GET parameters
	*
	* This function will build the GET parameters and also the Query String.
	*
	* @access private
	*/
	private function buildParams() {
		$total = count($this->get_parameters);
		$i = 1;
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

	/*
	* Set the PHP_SELF and SCRIPT_NAME to the suitable filepath.
	*
	* This function will set the values of PHP_SELF and SCRIPT_NAME to the suitable
	* file name. The filename will be searched in the $pattern_replace array.
	* The php filename is found before '?' in the pattern.
	*
	* @access private
	*/
	private function setservervars() {
		if (!empty($this->pathtofile)) {
			$_SERVER['PHP_SELF'] = preg_replace("/rewrite\.php/", $this->pathtofile, $_SERVER['PHP_SELF'], 1);
			$_SERVER['SCRIPT_NAME'] = preg_replace("/rewrite\.php/", $this->pathtofile, $_SERVER['SCRIPT_NAME'], 1);
		}
	}

	/*
	* Set the new QUERY_STRING
	*
	* This function will set the values of QUERY_STRING to new value
	* which is calculated in buildParams().
	*
	* @access private
	*/
	private function setquerystring() {
		if (!empty($_SERVER['QUERY_STRING'])) {
			$_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING']."&amp;".$this->buildParams();
		} else {
			$_SERVER['QUERY_STRING'] = $this->buildParams();
		}
	}

	/*
	* Call the Functions to Set GET Parameters and Query String
	*
	* This function will call the functions to set Server GET parameters
	* and the QUERY_STRING.
	*
	* @access private
	*/
	private function setVariables() {
		$this->setservervars();
		$this->setquerystring();
	}

	/*
	* Set Warnings
	*
	* This function will set Warnings. It will set them by Adding them into
	* the $this->warnings array.
	*
	* @param integer $code The Code Number of the Warning
	* @param string $info Any other Info to Show along with Warning
	* @access private
	*/
	private function setWarning($code, $info = "") {
		$info = ($info != "") ? $info." : " : "";
		$warnings = array(1 => "No matching Alias found.", 2 => "No matching Alias Pattern found.",
						  3 => "No matching Regex pattern found.", 4 => "Rewrite Include file not found.",
						  5 => "Tag not found in the pattern.", 6 => "File path is empty.", 7 => "Alias found.",
						  8 => "Alias Pattern found.", 9 => "Regex Pattern found.");
		if ($code <= 6) {
			$this->warnings[] = "<span style='color:#ff0000;'>".$info.$warnings[$code]."</span>";
		} else {
			$this->warnings[] = "<span style='color:#009900;'>".$info.$warnings[$code]."</span>";
		}
	}

	/*
	* Show Warnings or Debugging Information
	*
	* This function will show the Warnings or Debugging Information
	* if Warnings are enabled or if Debug Mode is enabled.
	*
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
			echo (intval($key)+1).". ".$val."<br />\n";
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

	/*
	* Returns the filename of the php file which will be included.
	*
	* This function will return the php filename which will be further included
	*
	* @access public
	*/
	public function getFilePath() {
		return $this->pathtofile;
	}
}

?>