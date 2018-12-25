<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maincore_mlang_functions.php
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

// Check multilang tables
function multilang_table($table) {

$result = dbquery("SELECT mlt_rights FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$table."' AND mlt_status='1' LIMIT 0,1");
$rows = dbrows($result);

if ($rows !=0) {
		return true;
	} else {
		return false;
	}
}

//   Check if a given language is valid or if exists
function valid_language($lang, $file_check = FALSE) {
	$enabled_languages = fusion_get_enabled_languages();
    if (preg_match("/^([a-z0-9_-]){2,50}$/i",
                   $lang) && ($file_check ? file_exists(LOCALE.$lang."/global.php") : isset($enabled_languages[$lang]))
    ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// Set the requested language
function set_language($lang) {
	global $userdata;
	if (iMEMBER) {
		dbquery("UPDATE ".DB_USERS." SET user_language='".$lang."' WHERE user_id='".$userdata['user_id']."'");
		$userdata['user_language'] = $lang;
	} else {
		$rows = dbrows(dbquery("SELECT user_language FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
		if ($rows != 0) {
			dbquery("UPDATE ".DB_LANGUAGE_SESSIONS." SET user_language='".$lang."', user_datestamp='".time()."' WHERE user_ip='".USER_IP."'");
		} else {
			dbquery("INSERT INTO ".DB_LANGUAGE_SESSIONS." (user_ip, user_language, user_datestamp) VALUES ('".USER_IP."', '".$lang."', '".time()."');");
		}
		// Sanitize guest sessions occasionally
		dbquery("DELETE FROM ".DB_LANGUAGE_SESSIONS." WHERE user_datestamp<'".(time()-(86400*60))."'");
	}
}

// Create a selection list of possible languages in list
function get_available_languages_list($selected_language = "") {
	global $enabled_languages;
	$res = "";
		for ($i = 0; $i < count($enabled_languages); $i++) {
			$sel = ($selected_language == $enabled_languages[$i] ? " selected='selected'" : "");
			$res .= "<option value='".$enabled_languages[$i]."'$sel>".$enabled_languages[$i]."</option>\n";
		}
	return $res;
}

// Create a selection list of possible languages in array 
function get_available_languages_array($language_list = "") {
	global $enabled_languages;
	$res = "";
		for ($i=0;$i<sizeof($language_list);$i++) {
		  echo "<input type='checkbox' value='".$language_list[$i]."' name='enabled_languages[]'  ".(in_array($language_list[$i], $enabled_languages)?"checked='checked'":"")."> ".str_replace('_', ' ', $language_list[$i])." <br  />";
		  }
	return $res;
}

// If language change is initiated and if the selected language exists, allowed by site
if (isset($_GET['lang']) && isset($_GET['lang']) != "" && preg_match("/^[\w-0-9a-zA-Z_]+$/", $_GET['lang']) && file_exists(LOCALE.$_GET['lang']."/global.php") && valid_language($_GET['lang'])) {
	$lang = stripinput($_GET['lang']);

	if (iMEMBER) {
		$result = dbquery("UPDATE ".DB_USERS." SET user_language='".$lang."' WHERE user_id='".$userdata['user_id']."'");
	} else {
		$result = dbquery("SELECT user_language FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'");
		$rows = dbrows($result);
	if ($rows != 0) {
		$result = dbquery("UPDATE ".DB_LANGUAGE_SESSIONS." SET user_language='".$lang."', user_datestamp='".time()."' WHERE user_ip='".USER_IP."'");
	} else {
		$result = dbquery("INSERT INTO ".DB_LANGUAGE_SESSIONS." (user_ip, user_language, user_datestamp) VALUES ('".USER_IP."', '".$lang."', '".time()."');");
	}
	
	// Sanitize guest sessions
		$result = dbquery("DELETE FROM ".DB_LANGUAGE_SESSIONS." WHERE user_datestamp<'".(time()-(86400 * 60))."'");
	}

	if(FUSION_QUERY != "") {
		if (stristr(FUSION_QUERY, '?')) {
			$this_redir = str_replace("?lang=".$lang, "", FUSION_QUERY);
		} elseif (stristr(FUSION_QUERY, '&amp;')) {
			$this_redir = str_replace("&amp;lang=".$lang, "", FUSION_QUERY);
		} elseif (stristr(FUSION_QUERY, '&')) {
			$this_redir = str_replace("&lang=".$lang, "", FUSION_QUERY);
		}
		
		if($this_redir != "") $this_redir = "?".$this_redir;
	} else {
		$this_redir = "";
	}
	redirect(FUSION_SELF.$this_redir);
}

// Language switcher function
function lang_switcher() {
	global $settings, $enabled_languages;
	
		if (preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
			$this_link = FUSION_REQUEST."&amp;lang="; 	   
			} else {
			if (stristr(FUSION_REQUEST, '?')) {
			$this_link = FUSION_REQUEST."&amp;lang=";		
			} else {
			$this_link = FUSION_REQUEST."?lang=";		
			}
		}
		
	if (sizeof($enabled_languages)>1) {
		
		// Load the language translation functions
		include_once INCLUDES."translate_include.php";

		if ($handle = opendir(LOCALE)) {
			/* This is the correct way to loop over the directory. */
			while (false !== ($file = readdir($handle))) { 
			  if ($file != "." && $file != ".." && $file != "/" && $file != "index.php") {
				 if (in_array($file, $enabled_languages)) {
					$img_files[]=$file;
				 }
			  }
			}
			closedir($handle); 
		}
		$row = 0;
		if (sizeof($img_files)>1) {
		   for ($i=0;$i<sizeof($img_files);$i++) {
			  if ($row==4) {
				 echo "<br />";
				 $row=0;
			  }
			  $row++;
			  $lang_text = translate_lang_names($img_files[$i]);
			  if ($img_files[$i] == LANGUAGE) {
				 echo "<img src='".LOCALE.$img_files[$i]."/".$img_files[$i].".png' alt='' title='".$lang_text."' style='border: 1px #000 solid'>\n ";
			  } else {
				 echo "<a class='side' href='".$this_link."".$img_files[$i]."'><img src='".LOCALE.$img_files[$i]."/".$img_files[$i].".png' alt='' title='".$lang_text."' style='border: none'></a>\n ";
			  }
			}
		  }	
		} 
}	

// Main language detection procedure
if (iMEMBER) {
	$result = dbquery("SELECT user_language FROM ".DB_USERS." WHERE user_id='".$userdata['user_id']."'");
	$rows = dbrows($result);
		if ($rows != 0) {
		   $data = dbarray($result);
		   define("LANGUAGE",$data['user_language']);    
		   define("LOCALESET",$data['user_language']."/");
		}
} else {
	$result = dbquery("SELECT * FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'");
	$rows = dbrows($result);
	if ($rows != 0) {
	   $data = dbarray($result);
	   define("LANGUAGE",$data['user_language']);    
	   define("LOCALESET",$data['user_language']."/");
	}	
}

function fusion_get_enabled_languages() {
    $settings = fusion_get_settings();
    static $enabled_languages = NULL;

	// Load the language translation functions
	include_once INCLUDES."translate_include.php";

    if ($enabled_languages === NULL) {
        if (isset($settings['enabled_languages'])) {
            $values = explode('.', $settings['enabled_languages']);
            foreach ($values as $language_name) {
                $enabled_languages[$language_name] = translate_lang_names($language_name);
            }
        }
    }
    return (array)$enabled_languages;
}

function clean_request($request_addition = '', array $filter_array = [], $keep_filtered = TRUE) {

    $fusion_query = [];

    if (fusion_get_settings("site_seo") && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
        global $filepath;

        $url['path'] = $filepath;
        if (!empty($_GET)) {
            $fusion_query = $_GET;
        }
    } else {

        $url = ((array)parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI']))) + [
                'path'  => '',
                'query' => ''
            ];

        if ($url['query']) {
            parse_str($url['query'], $fusion_query); // this is original.
        }
    }

    if ($keep_filtered) {
        $fusion_query = array_intersect_key($fusion_query, array_flip($filter_array));
    } else {
        $fusion_query = array_diff_key($fusion_query, array_flip($filter_array));
    }

    if ($request_addition) {

        $request_addition_array = [];

        if (is_array($request_addition)) {
            $fusion_query = $fusion_query + $request_addition;
        } else {
            parse_str($request_addition, $request_addition_array);
            $fusion_query = $fusion_query + $request_addition_array;
        }
    }

    $prefix = $fusion_query ? '?' : '';
    $query = $url['path'].$prefix.http_build_query($fusion_query, 'flags_', '&amp;');

    return (string)$query;
}
