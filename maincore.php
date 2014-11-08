<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maincore.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (preg_match("/maincore.php/i", $_SERVER['PHP_SELF'])) { die(); }

// Calculate script start/end time
function get_microtime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec+(float)$sec);
}

// Define script start time
define("START_TIME", get_microtime());
define("IN_FUSION", TRUE);

// Prevent any possible XSS attacks via $_GET.
if (stripget($_GET)) {
	die("Prevented a XSS attack through a GET variable!");
}

// Locate config.php and set the basedir path
$folder_level = "";
$i = 0;
while (!file_exists($folder_level."config.php")) {
	$folder_level .= "../";
	$i++;
	if ($i == 7) {
		if (file_exists("setup.php")) {
			redirect("setup.php");
		} else {
			die("config.php nor setup.php files where found");
		}
	}
}

// Path definitions
define("BASEDIR", $folder_level);
define("ADMIN", BASEDIR."administration/");
define("CLASSES", BASEDIR."includes/classes/");
define("DOWNLOADS", BASEDIR."downloads/");
define("IMAGES", BASEDIR."images/");
define("IMAGES_A", IMAGES."articles/");
define("IMAGES_N", IMAGES."news/");
define("IMAGES_N_T", IMAGES."news/thumbs/");
define("IMAGES_NC", IMAGES."news_cats/");
define("RANKS", IMAGES."ranks/");
define("INCLUDES", BASEDIR."includes/");
define("LOCALE", BASEDIR."locale/");
define("FORUM", BASEDIR."forum/");
define("INFUSIONS", BASEDIR."infusions/");
define("PHOTOS", IMAGES."photoalbum/");
define("THEMES", BASEDIR."themes/");
define("DB_HANDLERS", BASEDIR."includes/db_handlers/");

require_once BASEDIR."config.php";
// If config.php is empty, activate setup.php script
if (!isset($db_name)) {
	redirect("setup.php");
}

require_once INCLUDES."multisite_include.php";

// Select database handler
if ($pdo_enabled == "1") {
	require_once DB_HANDLERS."pdo_functions_include.php";
} else {
	require_once DB_HANDLERS."mysql_functions_include.php";
}

// Establish mySQL database connection
$link = dbconnect($db_host, $db_user, $db_pass, $db_name);
unset($db_host, $db_user, $db_pass);

// Fetch the settings from the database
$settings = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$settings[$data['settings_name']] = $data['settings_value'];
	}
} else {
	die("Settings do not exist, please check your config.php file or run setup.php again.");
}

// Settings dependent functions
date_default_timezone_set($settings['default_timezone']);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
// Session lifetime. After this time stored data will be seen as 'garbage' and cleaned up by the garbage collection process.
ini_set('session.gc_maxlifetime', 172800); // 48 hours
// Session cookie life time
ini_set('session.cookie_lifetime', 172800); // 48 hours
// Prevent document expiry when user hits Back in browser
session_cache_limiter('private, must-revalidate');
session_name(COOKIE_PREFIX.'session');
session_start();
//ob_start("ob_gzhandler"); //Uncomment this line and comment the one below to enable output compression.
ob_start();

// Sanitise $_SERVER globals
$_SERVER['PHP_SELF'] = cleanurl($_SERVER['PHP_SELF']);
$_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']) ? cleanurl($_SERVER['QUERY_STRING']) : "";
$_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? cleanurl($_SERVER['REQUEST_URI']) : "";
$PHP_SELF = cleanurl($_SERVER['PHP_SELF']);

// Redirects to the index if the URL is invalid (eg. file.php/folder/)
if ($_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) {
	redirect($settings['siteurl']);
}

// Disable FUSION_SELF and FUSION_QUERY in SEO mode.
if (!defined("IN_PERMALINK")) {
	define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");
	define("FUSION_SELF", basename($_SERVER['PHP_SELF']));
	define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);
}
define("FUSION_IP", $_SERVER['REMOTE_ADDR']);
define("QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));
define("USER_IP", $_SERVER['REMOTE_ADDR']);

// Variables initializing
$mysql_queries_count = 0;
$mysql_queries_time = array();
$smiley_cache = "";
$bbcode_cache = "";
$groups_cache = "";
$forum_rank_cache = "";
$forum_mod_rank_cache = "";
$locale = array();
$breadcrumbs = array();

// Calculate ROOT path for Permalinks
$current_path = $_SERVER['REQUEST_URI'];
if (isset($settings['site_path']) && strcmp($settings['site_path'], "/") != 0) {
	$current_path = str_replace($settings['site_path'], "", $current_path);
} else {
	$current_path = ltrim($current_path, "/");
}

// for Permalinks include files.
define("PERMALINK_CURRENT_PATH", $current_path);
$count = substr_count(PERMALINK_CURRENT_PATH, "/");
$root = "";
for ($i = 0; $i < $count; $i++) { // moved 0 to 1 will crash.
	$root .= "../";
}
define("ROOT", $root);

$root_count = $count-substr_count(BASEDIR, "/");
$fusion_root = '';
for ($i = 0; $i < $root_count; $i++) { // moved 0 to 1 will crash.
	$fusion_root .= "../";
}
define("FUSION_ROOT", $fusion_root);


// Calculate current true url
$script_url = explode("/", $_SERVER['PHP_SELF']);
$url_count = count($script_url);
$base_url_count = substr_count(BASEDIR, "/")+1;
$current_page = "";
while ($base_url_count != 0) {
	$current = $url_count-$base_url_count;
	$current_page .= "/".$script_url[$current];
	$base_url_count--;
}

// Disable TRUE_PHP_SELF and START_PAGE
if (!defined("IN_PERMALINK")) {
	define("TRUE_PHP_SELF", $current_page);
	define("START_PAGE", substr(preg_replace("#(&amp;|\?)(s_action=edit&amp;shout_id=)([0-9]+)#s", "", TRUE_PHP_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "")), 1));
}

// Autenticate user
require_once CLASSES."Authenticate.class.php";

// Log in user
if (isset($_POST['login']) && isset($_POST['user_name']) && isset($_POST['user_pass'])) {
	$auth = new Authenticate($_POST['user_name'], $_POST['user_pass'], (isset($_POST['remember_me']) ? TRUE : FALSE));
	$userdata = $auth->getUserData();
	unset($auth, $_POST['user_name'], $_POST['user_pass']);
} elseif (isset($_GET['logout']) && $_GET['logout'] == "yes") {
	$userdata = Authenticate::logOut();
	redirect(BASEDIR."index.php");
} else {
	$userdata = Authenticate::validateAuthUser(); // ok userdata never add _1.
}

// User level, Admin Rights & User Group definitions
define("iGUEST", $userdata['user_level'] == 0 ? 1 : 0);
define("iMEMBER", $userdata['user_level'] >= 101 ? 1 : 0);
define("iADMIN", $userdata['user_level'] >= 102 ? 1 : 0);
define("iSUPERADMIN", $userdata['user_level'] == 103 ? 1 : 0);
define("iUSER", $userdata['user_level']);
define("iUSER_RIGHTS", $userdata['user_rights']);
define("iUSER_GROUPS", substr($userdata['user_groups'], 1));

// check multilang tables
function multilang_table($table) {
	$result = dbquery("SELECT mlt_rights FROM ".DB_LANGUAGE_TABLES." WHERE mlt_rights='".$table."' AND mlt_status='1' LIMIT 0,1");
	$rows = dbrows($result);
	if ($rows != 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// Get enabled language settings
$enabled_languages = explode('.', $settings['enabled_languages']);

foreach ($enabled_languages as $_lang) {
	$language_opts[$_lang] = $_lang;
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
	for ($i = 0; $i < sizeof($language_list); $i++) {
		echo "<input type='checkbox' value='".$language_list[$i]."' name='enabled_languages[]'  ".(in_array($language_list[$i], $enabled_languages) ? "checked='checked'" : "")."> ".str_replace('_', ' ', $language_list[$i])." <br  />";
	}
	return $res;
}

// If language change is initiated and if the selected language exists, allowed by site
if (isset($_GET['lang']) && isset($_GET['lang']) != "" && preg_match("/^[\w-0-9a-zA-Z_]+$/", $_GET['lang']) && file_exists(LOCALE.$_GET['lang']."/global.php") && in_array($_GET['lang'], $enabled_languages)) {
	$lang = stripinput($_GET['lang']);
	if (iMEMBER) {
		$result = dbquery("UPDATE ".DB_USERS." SET user_language='".$lang."' WHERE user_id='".$userdata['user_id']."'");
	} else {
	$cookieName = "guest_language";
	$cookieContent = $lang;
	$cookieExpiration = time()+86400*60; 
	$cookiePath = COOKIE_PATH;
	$cookieDomain = COOKIE_DOMAIN;
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
	setcookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, $cookieDomain, FALSE, FALSE);
	} else {
	setcookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, $cookieDomain, FALSE);
	}
}

// Redirect handler to keep position upon lang switch
if (FUSION_QUERY != "") {
	if (stristr(FUSION_QUERY, '?')) {
		$this_redir = str_replace("?lang=".$lang, "", FUSION_QUERY);
	} elseif (stristr(FUSION_QUERY, '&amp;')) {
		$this_redir = str_replace("&amp;lang=".$lang, "", FUSION_QUERY);
	} elseif (stristr(FUSION_QUERY, '&')) {
		$this_redir = str_replace("&lang=".$lang, "", FUSION_QUERY);
	}
	if ($this_redir != "") $this_redir = "?".$this_redir;
} else {
	$this_redir = "";
}
	redirect(FUSION_SELF.$this_redir);
}

// Language switcher function
function lang_switcher() {
	global $settings, $enabled_languages;
	if (sizeof($enabled_languages) > 1) {
		if (preg_match('/administration/i', $_SERVER['PHP_SELF'])) {
			$this_link = FUSION_REQUEST."&amp;lang=";
		} else {
			if (stristr(FUSION_REQUEST, '?')) {
				$this_link = FUSION_REQUEST."&amp;lang=";
			} else {
				$this_link = FUSION_REQUEST."?lang=";
			}
		}
		if (sizeof($enabled_languages) > 1) {
			include_once INCLUDES."translate_include.php";
			if ($handle = opendir(LOCALE)) {
				/* This is the correct way to loop over the directory. */
				while (FALSE !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "/" && $file != "index.php") {
						if (in_array($file, $enabled_languages)) {
							$img_files[] = $file;
						}
					}
				}
				closedir($handle);
			}
			$row = 0;
			if (sizeof($img_files) > 1) {
				for ($i = 0; $i < sizeof($img_files); $i++) {
					if ($row == 4) {
						echo "<br />";
						$row = 0;
					}
					$row++;
					$lang_text = translate_lang_names($img_files[$i]);
					echo "<div class='lang_selector display-inline-block clearfix'>\n";
					if ($img_files[$i] == LANGUAGE) {
						echo "<img class='display-block img-responsive' src='".LOCALE.$img_files[$i]."/".$img_files[$i].".png' alt='' title='".$lang_text."' style='min-width:20px;'>\n ";
					} else {
						echo "<a class='side pull-left display-block' style='max-width:25px;' href='".$this_link."".$img_files[$i]."'><img src='".LOCALE.$img_files[$i]."/".$img_files[$i].".png' alt='' title='".$lang_text."' style='min-width:20px;'></a>\n ";
					}
					echo "</div>\n";
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
		define("LANGUAGE", $data['user_language']);
		define("LOCALESET", $data['user_language']."/");
	}
} else {
if (isset($_COOKIE['guest_language']) && $_COOKIE['guest_language'] != '' && preg_match("/^[0-9a-zA-Z_]+$/", $_COOKIE['guest_language'])) {
	define("LANGUAGE", $_COOKIE['guest_language']);
	define("LOCALESET", $_COOKIE['guest_language']."/");
	}
}

// No definitions have been set, set default language to system language
if (!defined("LANGUAGE")) {
	define ("LANGUAGE", $settings['locale']);
	define ("LOCALESET", $settings['locale']."/");
}

// IP address functions
include INCLUDES."ip_handling_include.php";

// Error Handling
require_once INCLUDES."error_handling_include.php";

// Redirects to the index if the URL is invalid (eg. file.php/folder/)
if ($_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) {
	redirect($settings['siteurl']);
}

// Load the Global language file
include LOCALE.LOCALESET."global.php";

if (iADMIN) {
	define("iAUTH", substr(md5($userdata['user_password'].USER_IP), 16, 16));
	$aidlink = "?aid=".iAUTH;
}

// PHP-Fusion user cookie functions
if (!isset($_COOKIE[COOKIE_PREFIX.'visited'])) {
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value=settings_value+1 WHERE settings_name='counter'");
	setcookie(COOKIE_PREFIX."visited", "yes", time()+31536000, "/", "", "0");
}
$lastvisited = Authenticate::setLastVisitCookie();

// Set theme
set_theme($userdata['user_theme']);

// Check if a given theme exists and is valid
function theme_exists($theme) {
	global $settings;
	if ($theme == "Default") {
		$theme = $settings['theme'];
	}
	if (!file_exists(THEMES) || !is_dir(THEMES) || !is_string($theme) || !preg_match("/^([a-z0-9_-]){2,50}$/i", $theme) || !file_exists(THEMES.$theme)) {
		return FALSE;
	} elseif (file_exists(THEMES.$theme."/theme.php") && file_exists(THEMES.$theme."/styles.css")) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// Set a valid theme
function set_theme($theme) {
	global $settings, $locale;
	if (!defined("THEME")) {
		if (theme_exists($theme)) {
			define("THEME", THEMES.($theme == "Default" ? $settings['theme'] : $theme)."/");
		} else {
			$dh = opendir(THEMES);
			while (FALSE !== ($entry = readdir($dh))) {
				if ($entry != "." && $entry != ".." && is_dir(THEMES.$entry)) {
					if (theme_exists($entry)) {
						define("THEME", THEMES.$entry."/");
						break;
					}
				}
			}
			closedir($dh);
		}
		if (!defined("THEME")) {
			echo "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n";
			echo $locale['global_301'];
			die();
		}
	}
}

// Set the admin password when needed
function set_admin_pass($password) {
	Authenticate::setAdminCookie($password);
}

// Check if admin password matches userdata
function check_admin_pass($password) {
	return Authenticate::validateAuthAdmin($password);
}

// Redirect browser using header or script function
function redirect($location, $script = FALSE) {
	if (!$script) {
		header("Location: ".str_replace("&amp;", "&", $location));
		exit;
	} else {
		echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
		exit;
	}
}

// Clean URL Function, prevents entities in server globals
function cleanurl($url) {
	$bad_entities = array("&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*");
	$safe_entities = array("&amp;", "", "", "", "", "", "", "", "", "");
	$url = str_replace($bad_entities, $safe_entities, $url);
	return $url;
}

// Strip Input Function, prevents HTML in unwanted places
function stripinput($text) {
	if (!is_array($text)) {
		$text = stripslash(trim($text));
		$text = preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", $text);
		$search = array("&", "\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;");
		$replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
		$text = str_replace($search, $replace, $text);
	} else {
		foreach ($text as $key => $value) {
			$text[$key] = stripinput($value);
		}
	}
	return $text;
}

// Prevent any possible XSS attacks via $_GET.
function stripget($check_url) {
	$return = FALSE;
	if (is_array($check_url)) {
		foreach ($check_url as $value) {
			if (stripget($value) == TRUE) {
				return TRUE;
			}
		}
	} else {
		$check_url = str_replace(array("\"", "\'"), array("", ""), urldecode($check_url));
		if (preg_match("/<[^<>]+>/i", $check_url)) {
			return TRUE;
		}
	}
	return $return;
}

// Strip file name
function stripfilename($filename) {
	$filename = strtolower(str_replace(" ", "_", $filename));
	$filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $filename);
	$filename = preg_replace("/^\W/", "", $filename);
	$filename = preg_replace('/([_-])\1+/', '$1', $filename);
	if ($filename == "") {
		$filename = time();
	}
	return $filename;
}

// Strip Slash Function, only stripslashes if magic_quotes_gpc is on
function stripslash($text) {
	if (QUOTES_GPC) {
		$text = stripslashes($text);
	}
	return $text;
}

// Add Slash Function, add correct number of slashes depending on quotes_gpc
function addslash($text) {
	if (!QUOTES_GPC) {
		$text = addslashes(addslashes($text));
	} else {
		$text = addslashes($text);
	}
	return $text;
}

// htmlentities is too agressive so we use this function
function phpentities($text) {
	$search = array("&", "\"", "'", "\\", "<", ">");
	$replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&lt;", "&gt;");
	$text = str_replace($search, $replace, $text);
	return $text;
}

// Trim a line of text to a preferred length
function trimlink($text, $length) {
   $dec = array("&", "\"", "'", "\\", '\"', "\'", "<", ">");
   $enc = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;");
   $text = str_replace($enc, $dec, $text);
   if (strlen($text) > $length) $text = mb_substr($text,0,($length-3),mb_detect_encoding($text))."...";
   $text = str_replace($dec, $enc, $text);
   return $text;
}
// Trim a paragraph to a word length.
function trim_word($text, $limit) {
	if (str_word_count($text, 0) > $limit) {
		$words = str_word_count($text, 2);
		$pos = array_keys($words);
		// Position at which this word begins + it's own length
		$rpos = $pos[$limit-1] + strlen($words[$pos[$limit-1]]);

		$text = substr($text, 0, $rpos) . '&hellip;';
	}
	return $text;
}

// Pure trim function
function trim_text($str, $length = FALSE) {
	$length = (isset($length) && (!empty($length))) ? stripinput($length) : "300";
	$startfrom = $length;
	for ($i = $startfrom; $i <= strlen($str); $i++) {
		$spacetest = substr("$str", $i, 1);
		if ($spacetest == " ") {
			$spaceok = substr("$str", 0, $i);
			return ($spaceok."...");
			break;
		}
	}
	return ($str);
}

// Validate numeric input
function isnum($value, $decimal=false) {
	if (!is_array($value)) {
		if($decimal==true) return (preg_match("/^[0-9]+(\.{0,1})[0-9]*$/", $value));
		return (preg_match("/^[0-9]+$/", $value));
	} else {
		return false;
	}
}


// Custom preg-match function
function preg_check($expression, $value) {
	if (!is_array($value)) {
		return preg_match($expression, $value);
	} else {
		return FALSE;
	}
}

// Cache smileys mysql
function cache_smileys() {
	global $smiley_cache;
	$result = dbquery("SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS);
	if (dbrows($result)) {
		$smiley_cache = array();
		while ($data = dbarray($result)) {
			$smiley_cache[] = array("smiley_code" => $data['smiley_code'], "smiley_image" => $data['smiley_image'],
									"smiley_text" => $data['smiley_text']);
		}
	} else {
		$smiley_cache = array();
	}
	return $smiley_cache;
}

// Parse smiley bbcode
function parsesmileys($message) {
	global $smiley_cache;
	if (!preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message)) {
		if (!$smiley_cache) {
			cache_smileys();
		}
		if (is_array($smiley_cache) && count($smiley_cache)) {
			foreach ($smiley_cache as $smiley) {
				$smiley_code = preg_quote($smiley['smiley_code'], '#');
				$smiley_image = "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' style='vertical-align:middle;' />";
				$message = preg_replace("#{$smiley_code}#si", $smiley_image, $message);
			}
		}
	}
	return $message;
}

// Show smiley icons in comments, forum and other post pages
function displaysmileys($textarea, $form = "inputform") {
	global $smiley_cache;
	$smileys = "";
	$i = 0;
	if (!$smiley_cache) {
		cache_smileys();
	}
	if (is_array($smiley_cache) && count($smiley_cache)) {
		foreach ($smiley_cache as $smiley) {
			if ($i != 0 && ($i%10 == 0)) {
				$smileys .= "<br />\n";
				$i++;
			}
			$smileys .= "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' onclick=\"insertText('".$textarea."', '".$smiley['smiley_code']."', '".$form."');\" />\n";
		}
	}
	return $smileys;
}

// Cache bbcode mysql
function cache_bbcode() {
	global $bbcode_cache;
	$result = dbquery("SELECT bbcode_name FROM ".DB_BBCODES." ORDER BY bbcode_order ASC");
	if (dbrows($result)) {
		$bbcode_cache = array();
		while ($data = dbarray($result)) {
			$bbcode_cache[] = $data['bbcode_name'];
		}
	} else {
		$bbcode_cache = array();
	}
	return $bbcode_cache;
}

// Parse bbcode
function parseubb($text, $selected = FALSE) {
	global $bbcode_cache;
	if (!$bbcode_cache) {
		cache_bbcode();
	}
	if (is_array($bbcode_cache) && count($bbcode_cache)) {
		if ($selected) {
			$sel_bbcodes = explode("|", $selected);
		}
		foreach ($bbcode_cache as $bbcode) {
			if ($selected && in_array($bbcode, $sel_bbcodes)) {
				if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
					if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
						include(LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
					} elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
						include(LOCALE."English/bbcodes/".$bbcode.".php");
					}
					include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
				}
			} elseif (!$selected) {
				if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
					if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
						include(LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
					} elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
						include(LOCALE."English/bbcodes/".$bbcode.".php");
					}
					include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
				}
			}
		}
	}
	$text = descript($text, FALSE);
	return $text;
}

// Javascript email encoder by Tyler Akins
function hide_email($email, $title = "", $subject = "") {
	if (preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
		$parts = explode("@", $email);
		$MailLink = "<a href='mailto:".$parts[0]."@".$parts[1];
		if ($subject != "") {
			$MailLink .= "?subject=".urlencode($subject);
		}
		$MailLink .= "'>".($title ? $title : $parts[0]."@".$parts[1])."</a>";
		$MailLetters = "";
		for ($i = 0; $i < strlen($MailLink); $i++) {
			$l = substr($MailLink, $i, 1);
			if (strpos($MailLetters, $l) === FALSE) {
				$p = rand(0, strlen($MailLetters));
				$MailLetters = substr($MailLetters, 0, $p).$l.substr($MailLetters, $p, strlen($MailLetters));
			}
		}
		$MailLettersEnc = str_replace("\\", "\\\\", $MailLetters);
		$MailLettersEnc = str_replace("\"", "\\\"", $MailLettersEnc);
		$MailIndexes = "";
		for ($i = 0; $i < strlen($MailLink); $i++) {
			$index = strpos($MailLetters, substr($MailLink, $i, 1));
			$index += 48;
			$MailIndexes .= chr($index);
		}
		$MailIndexes = str_replace("\\", "\\\\", $MailIndexes);
		$MailIndexes = str_replace("\"", "\\\"", $MailIndexes);
		$res = "<script type='text/javascript'>";
		$res .= "/*<![CDATA[*/";
		$res .= "ML=\"".str_replace("<", "xxxx", $MailLettersEnc)."\";";
		$res .= "MI=\"".str_replace("<", "xxxx", $MailIndexes)."\";";
		$res .= "ML=ML.replace(/xxxx/g, '<');";
		$res .= "MI=MI.replace(/xxxx/g, '<');";
		$res .= "OT=\"\";";
		$res .= "for(j=0;j < MI.length;j++){";
		$res .= "OT+=ML.charAt(MI.charCodeAt(j)-48);";
		$res .= "}document.write(OT);";
		$res .= "/*]]>*/";
		$res .= "</script>";
		return $res;
	} else {
		return $email;
	}
}

// Format spaces and tabs in code bb tags
function formatcode($text) {
	$text = str_replace("  ", "&nbsp; ", $text);
	$text = str_replace("  ", " &nbsp;", $text);
	$text = str_replace("\t", "&nbsp; &nbsp;", $text);
	$text = preg_replace("/^ {1}/m", "&nbsp;", $text);
	return $text;
}

// Highlights given words in subject
function highlight_words($word, $subject) {
	for ($i = 0, $l = count($word); $i < $l; $i++) {
		$word[$i] = str_replace(array("\\", "+", "*", "?", "[", "^", "]", "$", "(", ")", "{", "}", "=", "!", "<", ">",
									  "|", ":", "#", "-", "_"), "", $word[$i]);
		if (!empty($word[$i])) {
			$subject = preg_replace("#($word[$i])(?![^<]*>)#i", "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px'>\${1}</span>", $subject);
		}
	}
	return $subject;
}

// This function sanitize news & article submissions
function descript($text, $striptags = TRUE) {
	// Convert problematic ascii characters to their true values
	$search = array("40", "41", "58", "65", "66", "67", "68", "69", "70", "71", "72", "73", "74", "75", "76", "77",
					"78", "79", "80", "81", "82", "83", "84", "85", "86", "87", "88", "89", "90", "97", "98", "99",
					"100", "101", "102", "103", "104", "105", "106", "107", "108", "109", "110", "111", "112", "113",
					"114", "115", "116", "117", "118", "119", "120", "121", "122");
	$replace = array("(", ")", ":", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q",
					 "r", "s", "t", "u", "v", "w", "x", "y", "z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
					 "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
	$entities = count($search);
	for ($i = 0; $i < $entities; $i++) {
		$text = preg_replace("#(&\#)(0*".$search[$i]."+);*#si", $replace[$i], $text);
	}
	$text = preg_replace('#(&\#x)([0-9A-F]+);*#si', "", $text);
	$text = preg_replace('#(<[^>]+[/\"\'\s])(onmouseover|onmousedown|onmouseup|onmouseout|onmousemove|onclick|ondblclick|onfocus|onload|xmlns)[^>]*>#iU', ">", $text);
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)script:#iU', '$1=$2nojscript...', $text);
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)javascript:#iU', '$1=$2nojavascript...', $text);
	$text = preg_replace('#([a-z]*)=([\'\"]*)vbscript:#iU', '$1=$2novbscript...', $text);
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU', "$1>", $text);
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU', "$1>", $text);
	if ($striptags) {
		do {
			$thistext = $text;
			$text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text);
		} while ($thistext != $text);
	}
	return $text;
}

// Scan image files for malicious code
function verify_image($file) {
	$txt = file_get_contents($file);
	$image_safe = TRUE;
	if (preg_match('#<?php#i', $txt)) {
		$image_safe = FALSE;
	} 
	elseif (preg_match('#&(quot|lt|gt|nbsp|<?php);#i', $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#&\#x([0-9a-f]+);#i", $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match('#&\#([0-9]+);#i', $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#([a-z]*)=([\`\'\"]*)script:#iU", $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#([a-z]*)=([\`\'\"]*)javascript:#iU", $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#([a-z]*)=([\'\"]*)vbscript:#iU", $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU", $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU", $txt)) {
		$image_safe = FALSE;
	} elseif (preg_match("#</*(applet|link|style|script|iframe|frame|frameset)[^>]*>#i", $txt)) {
		$image_safe = FALSE;
	}
	return $image_safe;
}

// Replace offensive words with the defined replacement word
function censorwords($text) {
	global $settings;
	if ($settings['bad_words_enabled'] == "1" && $settings['bad_words'] != "") {
		$word_list = explode("\r\n", $settings['bad_words']);
		for ($i = 0; $i < count($word_list); $i++) {
			if ($word_list[$i] != "") $text = preg_replace("/".$word_list[$i]."/si", $settings['bad_word_replace'], $text);
		}
	}
	return $text;
}

// Check file types of the uploaded file with known mime types list to prevent uploading unwanted files if enabled
if ($settings['mime_check'] == "1") {
	if (isset($_FILES) && count($_FILES)) {
		require_once INCLUDES."mimetypes_include.php";
		$mime_types = mimeTypes();
		foreach ($_FILES as $each) {
			if (isset($each['name']) && strlen($each['tmp_name'])) {
				$file_info = pathinfo($each['name']);
				$extension = $file_info['extension'];
				if (array_key_exists($extension, $mime_types)) {
					if (is_array($mime_types[$extension])) {
						$valid_mimetype = FALSE;
						foreach ($mime_types[$extension] as $each_mimetype) {
							if ($each_mimetype == $each['type']) {
								$valid_mimetype = TRUE;
								break;
							}
						}
						if (!$valid_mimetype) {
							die('Prevented an unwanted file upload attempt!');
						}
						unset($valid_mimetype);
					} else {
						if ($mime_types[$extension] != $each['type']) {
							die('Prevented an unwanted file upload attempt!');
						}
					}
				} 
				unset($file_info, $extension);
			}
		}
		unset($mime_types);
	}
}

// Display the user's level
function getuserlevel($userlevel) {
	global $locale;
	if ($userlevel == 101) {
		return $locale['user1'];
	} elseif ($userlevel == 102) {
		return $locale['user2'];
	} elseif ($userlevel == 103) {
		return $locale['user3'];
	}
}

// Display the user's status
function getuserstatus($userstatus) {
	global $locale;
	if ($userstatus == 0) {
		return $locale['status0'];
	} elseif ($userstatus == 1) {
		return $locale['status1'];
	} elseif ($userstatus == 2) {
		return $locale['status2'];
	} elseif ($userstatus == 3) {
		return $locale['status3'];
	} elseif ($userstatus == 4) {
		return $locale['status4'];
	} elseif ($userstatus == 5) {
		return $locale['status5'];
	} elseif ($userstatus == 6) {
		return $locale['status6'];
	} elseif ($userstatus == 7) {
		return $locale['status7'];
	} elseif ($userstatus == 8) {
		return $locale['status8'];
	}
}

// Check if Administrator has correct rights assigned
function checkrights($right) {
	if (iADMIN && in_array($right, explode(".", iUSER_RIGHTS))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function checkAdminPageAccess($right) {
	if (!checkrights($right) || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
		return FALSE;
	} else {
		return TRUE;
	}
}

// Check if user is assigned to the specified user group
function checkgroup($group) {
	if (iSUPERADMIN) {
		return TRUE;
	} elseif (iADMIN && ($group == "0" || $group == "101" || $group == "102")) {
		return TRUE;
	} elseif (iMEMBER && ($group == "0" || $group == "101")) {
		return TRUE;
	} elseif (iGUEST && $group == "0") {
		return TRUE;
	} elseif (iMEMBER && $group && in_array($group, explode(".", iUSER_GROUPS))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// Cache groups mysql
function cache_groups() {
	global $groups_cache;
	$result = dbquery("SELECT * FROM ".DB_USER_GROUPS." ORDER BY group_id ASC");
	if (dbrows($result)) {
		$groups_cache = array();
		while ($data = dbarray($result)) {
			$groups_cache[] = $data;
		}
	} else {
		$groups_cache = array();
	}
}

// Compile access levels & user group array
function getusergroups() {
	global $locale, $groups_cache;
	$groups_array = array(array("0", $locale['user0']), array("101", $locale['user1']), array("102", $locale['user2']),
						  array("103", $locale['user3']));
	if (!$groups_cache) {
		cache_groups();
	}
	if (is_array($groups_cache) && count($groups_cache)) {
		foreach ($groups_cache as $group) {
			array_push($groups_array, array($group['group_id'], $group['group_name']));
		}
	}
	return $groups_array;
}

// Get the name of the access level or user group
function getgroupname($group_id, $return_desc = FALSE) {
	global $locale, $groups_cache;
	if ($group_id == "0") {
		return $locale['user0'];
	} elseif ($group_id == "101") {
		return $locale['user1'];
		exit;
	} elseif ($group_id == "102") {
		return $locale['user2'];
		exit;
	} elseif ($group_id == "103") {
		return $locale['user3'];
		exit;
	} else {
		if (!$groups_cache) {
			cache_groups();
		}
		if (is_array($groups_cache) && count($groups_cache)) {
			foreach ($groups_cache as $group) {
				if ($group_id == $group['group_id']) {
					return ($return_desc ? ($group['group_description'] ? $group['group_description'] : '-') : $group['group_name']);
					exit;
				}
			}
		}
	}
	return $locale['user_na'];
}

// Getting the access levels used when asking the database for data
function groupaccess($field) {
	if (iGUEST) {
		return "$field = '0'";
	} elseif (iSUPERADMIN) {
		return "1 = 1";
	} elseif (iADMIN) {
		$res = "($field='0' OR $field='101' OR $field='102'";
	} elseif (iMEMBER) {
		$res = "($field='0' OR $field='101'";
	}
	if (iUSER_GROUPS != "" && !iSUPERADMIN) {
		$res .= " OR $field='".str_replace(".", "' OR $field='", iUSER_GROUPS)."'";
	}
	$res .= ")";
	return $res;
}

// UF blacklist for SQL - same as groupaccess() but $field is the user_id column.
function blacklist($field) {
	global $userdata;
	$blacklist = array();
	if (in_array('user_blacklist', fieldgenerator(DB_USERS))) {
		$result = dbquery("SELECT user_id, user_level FROM ".DB_USERS." WHERE user_blacklist REGEXP('^\\\.{$userdata['user_id']}$|\\\.{$userdata['user_id']}\\\.|\\\.{$userdata['user_id']}$')");
		if (dbrows($result) > 0) {
			while ($data = dbarray($result)) {
				if ($data['user_level'] < 102) {
					$blacklist[] = $data['user_id']; // all users to filter
				}
			}
		}
		$i = 0;
		$sql = '';
		foreach ($blacklist as $id) {
			$sql .= ($i > 0) ? "AND $field !='$id'" : "($field !='$id'";
			$i++;
		}
		$sql .= $sql ? ")" : ' 1=1 ';
		return "$sql";
	} else {
		return "";
	}
}

// check if user was blacklisted by a member
function user_blacklisted($user_id) {
	global $userdata;
	if (in_array('user_blacklist', fieldgenerator(DB_USERS))) {
		$user_blacklist = explode('.', $userdata['user_blacklist']);
		if (in_array($user_id, $user_blacklist)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

// Create a list of files or folders and store them in an array
function makefilelist($folder, $filter, $sort = TRUE, $type = "files", $ext_filter = "") {
	$res = array();
	$filter = explode("|", $filter);
	if ($type == "files" && !empty($ext_filter)) {
		$ext_filter = explode("|", strtolower($ext_filter));
	}
	$temp = opendir($folder);
	while ($file = readdir($temp)) {
		if ($type == "files" && !in_array($file, $filter)) {
			if (!empty($ext_filter)) {
				if (!in_array(substr(strtolower(stristr($file, '.')), +1), $ext_filter) && !is_dir($folder.$file)) {
					$res[] = $file;
				}
			} else {
				if (!is_dir($folder.$file)) {
					$res[] = $file;
				}
			}
		} elseif ($type == "folders" && !in_array($file, $filter)) {
			if (is_dir($folder.$file)) {
				$res[] = $file;
			}
		}
	}
	closedir($temp);
	if ($sort) {
		sort($res);
	}
	return $res;
}

// Create a selection list from an array created by makefilelist()
function makefileopts($files, $selected = "") {
	$res = "";
	for ($i = 0; $i < count($files); $i++) {
		$sel = ($selected == $files[$i] ? " selected='selected'" : "");
		$res .= "<option value='".$files[$i]."'$sel>".$files[$i]."</option>\n";
	}
	return $res;
}

// Making Page Navigation
function makepagenav($start, $count, $total, $range = 0, $link = "", $getname = "rowstart") {
	global $locale;
	if ($link == "") {
		$link = FUSION_SELF."?";
	}
	if (!preg_match("#[0-9]+#", $count) || $count == 0) return FALSE;
	$pg_cnt = ceil($total/$count);
	if ($pg_cnt <= 1) {
		return "";
	}
	$idx_back = $start-$count;
	$idx_next = $start+$count;
	$cur_page = ceil(($start+1)/$count);
	$res = $locale['global_092']." ".$cur_page.$locale['global_093'].$pg_cnt.": ";
	if ($idx_back >= 0) {
		if ($cur_page > ($range+1)) {
			$res .= "<a class='pagenavlink' data-value='0' href='".$link.$getname."=0'>1</a>";
			if ($cur_page != ($range+2)) {
				$res .= "...";
			}
		}
	}
	$idx_fst = max($cur_page-$range, 1);
	$idx_lst = min($cur_page+$range, $pg_cnt);
	if ($range == 0) {
		$idx_fst = 1;
		$idx_lst = $pg_cnt;
	}
	for ($i = $idx_fst; $i <= $idx_lst; $i++) {
		$offset_page = ($i-1)*$count;
		if ($i == $cur_page) {
			$res .= "<span><strong>".$i."</strong></span>";
		} else {
			$res .= "<a class='pagenavlink' data-value='$offset_page' href='".$link.$getname."=".$offset_page."'>".$i."</a>";
		}
	}
	if ($idx_next < $total) {
		if ($cur_page < ($pg_cnt-$range)) {
			if ($cur_page != ($pg_cnt-$range-1)) {
				$res .= "...";
			}
			$res .= "<a class='pagenavlink' data-value='".($pg_cnt-1)*$count."' href='".$link.$getname."=".($pg_cnt-1)*$count."'>".$pg_cnt."</a>\n";
		}
	}
	return "<div class='pagenav'>\n".$res."</div>\n";
}

// Format the date & time accordingly
function showdate($format, $val) {
	global $settings, $userdata;
	if (isset($userdata['user_offset'])) {
		$offset = $userdata['user_offset']+$settings['serveroffset'];
	} else {
		$offset = $settings['timeoffset']+$settings['serveroffset'];
	}
	if ($format == "shortdate" || $format == "longdate" || $format == "forumdate" || $format == "newsdate") {
		return strftime($settings[$format], $val+($offset*3600));
	} else {
		return strftime($format, $val+($offset*3600));
	}
}

// Translate bytes into kB, MB, GB or TB by CrappoMan, lelebart fix
function parsebytesize($size, $digits = 2, $dir = FALSE) {
	global $locale;
	$kb = 1024;
	$mb = 1024*$kb;
	$gb = 1024*$mb;
	$tb = 1024*$gb;
	if (($size == 0) && ($dir)) {
		return $locale['global_460'];
	} elseif ($size < $kb) {
		return $size.$locale['global_461'];
	} elseif ($size < $mb) {
		return round($size/$kb, $digits).$locale['global_462'];
	} elseif ($size < $gb) {
		return round($size/$mb, $digits).$locale['global_463'];
	} elseif ($size < $tb) {
		return round($size/$gb, $digits).$locale['global_464'];
	} else {
		return round($size/$tb, $digits).$locale['global_465'];
	}
}

// User profile link
function profile_link($user_id, $user_name, $user_status, $class = "profile-link") {
	global $locale, $settings;
	$class = ($class ? " class='$class'" : "");
	if ((in_array($user_status, array(0, 3,
									  7)) || checkrights("M")) && (iMEMBER || $settings['hide_userprofiles'] == "0")
	) {
		$link = "<a href='".BASEDIR."profile.php?lookup=".$user_id."'".$class.">".ucwords($user_name)."</a>";
	} elseif ($user_status == "5" || $user_status == "6") {
		$link = $locale['user_anonymous'];
	} else {
		$link = $user_name;
	}
	return $link;
}

require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."notify/notify.inc.php";
require_once INCLUDES."sqlhandler.inc.php";

require_once INCLUDES."defender.inc.php";
$defender = new defender;
$defender->debug_notice = true; // turn this off after beta.
$defender->sniff_token();
$defender->debug_notice = false; // turn this off after beta.
require_once INCLUDES."dynamics/dynamics.inc.php";
$dynamic = new dynamics();
$dynamic->boot();

function print_p($array, $modal = FALSE) {
	echo ($modal) ? openmodal('Debug', 'Debug') : '';
	echo "<pre style='white-space:pre-wrap !important;'>";
	echo htmlspecialchars(print_r($array, TRUE), ENT_QUOTES, 'utf-8');
	echo "</pre>";
	echo ($modal) ? closemodal() : '';
}

include INCLUDES."system_images.php";
?>