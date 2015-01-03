<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maincore.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
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

// Define script start time
define("START_TIME", microtime(TRUE));
define("IN_FUSION", TRUE);

require __DIR__.'/includes/core_resources_include.php';

// Prevent any possible XSS attacks via $_GET.
if (stripget($_GET)) {
	die("Prevented a XSS attack through a GET variable!");
}
// Detect whether the system is installed and load config.php if it is.
require_once fusion_detect_installation();

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
$settings = fusion_get_settings();

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
	redirect(FUSION_REQUEST);
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

// Get enabled language settings
$enabled_languages = explode('.', $settings['enabled_languages']);
foreach ($enabled_languages as $_lang) {
	$language_opts[$_lang] = $_lang;
}

// If language change is initiated and if the selected language is valid
if (isset($_GET['lang']) && valid_language($_GET['lang'])) {
	$lang = stripinput($_GET['lang']);
	if (iMEMBER) {
		dbquery("UPDATE ".DB_USERS." SET user_language='".$lang."' WHERE user_id='".$userdata['user_id']."'");
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
		$this_redir = '';
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

// Main language detection procedure
if (iMEMBER && valid_language($userdata['user_language'])) {
	define("LANGUAGE", $userdata['user_language']);
	define("LOCALESET", $userdata['user_language']."/");
} elseif (isset($_COOKIE['guest_language']) && valid_language($_COOKIE['guest_language'])) {
	define("LANGUAGE", $_COOKIE['guest_language']);
	define("LOCALESET", $_COOKIE['guest_language']."/");
} else {
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
if (!isset($userdata['user_theme']) || !$userdata['user_theme']) {
	set_theme($settings['theme']);
} else {
	set_theme($userdata['user_theme']);
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

require_once INCLUDES."translate_include.php";
require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."notify/notify.inc.php";
require_once INCLUDES."sqlhandler.inc.php";
require_once INCLUDES."defender.inc.php";
$defender = new defender;
$defender->debug_notice = false; // turn this off after beta.
$defender->sniff_token();
$defender->debug_notice = false; // turn this off after beta.
require_once INCLUDES."dynamics/dynamics.inc.php";
$dynamic = new dynamics();
$dynamic->boot();

include INCLUDES."system_images.php";
?>
