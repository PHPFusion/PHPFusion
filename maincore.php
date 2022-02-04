<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: maincore.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (preg_match("/maincore.php/i", $_SERVER['PHP_SELF'])) {
    die();
}

// Calculate script start/end time
function get_microtime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
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
while (!file_exists($folder_level."maincore.php")) {
    $folder_level .= "../";
    $i++;
    if ($i == 7) {
        die("maincore.php file not found");
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
define("IMAGES_B", IMAGES."blog/");
define("IMAGES_B_T", IMAGES."blog/thumbs/");
define("IMAGES_BC", IMAGES."blog_cats/");
define("RANKS", IMAGES."ranks/");
define("INCLUDES", BASEDIR."includes/");
define("LOCALE", BASEDIR."locale/");
define("FORUM", BASEDIR."forum/");
define("INFUSIONS", BASEDIR."infusions/");
define("PHOTOS", IMAGES."photoalbum/");
define("THEMES", BASEDIR."themes/");
define("DB_HANDLERS", BASEDIR."includes/db_handlers/");
define("FUSION_ROOT_DIR", dirname(__DIR__).'/');

if (file_exists(BASEDIR."config.php")) {
    require_once BASEDIR."config.php";
}

// If config.php is empty, activate setup.php script
if (!isset($db_name)) {
    redirect("setup.php");
}

require_once INCLUDES."multisite_include.php";

// Database driver selection
if (!empty($db_driver) && $db_driver == "mysqli") {
    require_once DB_HANDLERS."mysqli_functions_include.php";
} else {
    require_once DB_HANDLERS."pdo_functions_include.php";
}

// Establish mySQL database connection
if (!empty($db_host) && !empty($db_user) && !empty($db_name)) {
    $link = @dbconnect($db_host, $db_user, $db_pass, $db_name, !empty($db_port) ? $db_port : 3306);
    unset($db_host, $db_user, $db_pass, $db_port);
}

// Fetch the settings from the database
$settings = [];
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

header('X-Powered-By: PHPFusion'.(isset($settings['version']) ? ' '.$settings['version'] : ''));

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

// Force protocol change if https turned on main settings
if ($settings['site_protocol'] == 'https' && !isset($_SERVER['HTTPS'])) {
    $url = (array)parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI'])) + ['path' => '', 'query' => ''];
    $fusion_query = [];
    if ($url['query']) {
        parse_str($url['query'], $fusion_query); // this is original.
    }
    $prefix = !empty($fusion_query ? '?' : '');
    $site_path = $url['path'];
    if (!empty($url['path']) && strpos($url['path'], '/', 1)) {
        $site_path = ltrim($url['path'], '/');
    }
    if ($settings['site_path'] !== '/') {
        $site_path = str_replace($settings['site_path'], '', $url['path']);
    }
    $site_path = $settings['siteurl'].$site_path.$prefix.http_build_query($fusion_query, 'flags_', '&amp;');
    redirect($site_path);
}

// Redirect to correct path if there are double // in the current uri
if (substr_count($_SERVER['REQUEST_URI'], '//')) {
    $site_path = str_replace('/', '', $_SERVER['REQUEST_URI']);
    redirect(rtrim($settings['siteurl'], '/').'/'.$site_path);
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
$mysql_queries_time = [];
$smiley_cache = "";
$bbcode_cache = "";
$groups_cache = "";
$forum_rank_cache = "";
$forum_mod_rank_cache = "";
$locale = [];

// Calculate ROOT path for Permalinks
$current_path = html_entity_decode($_SERVER['REQUEST_URI']);
if (isset($settings['site_path']) && strcmp($settings['site_path'], "/") != 0) {
    $current_path = str_replace($settings['site_path'], '', $current_path);
} else {
    $current_path = ltrim($current_path, "/");
}

// for Permalinks include files.
define("PERMALINK_CURRENT_PATH", $current_path);
define('FORM_REQUEST', $settings['site_seo'] && defined('IN_PERMALINK') ? PERMALINK_CURRENT_PATH : FUSION_REQUEST);
//BREADCRUMB URL, INCLUDES PATH TO FILE AND FILENAME
//E.G. infusions/downloads/downloads.php OR VIEWPAGE.PHP
if (explode("?", PERMALINK_CURRENT_PATH)) {
    $filelink = explode("?", PERMALINK_CURRENT_PATH);
    define("FUSION_FILELINK", $filelink[0]);
} else {
    define("FUSION_FILELINK", PERMALINK_CURRENT_PATH);
}

$count = substr_count(PERMALINK_CURRENT_PATH, "/");
$root = "";
for ($i = 0; $i < $count; $i++) { // moved 0 to 1 will crash.
    $root .= "../";
}
define("ROOT", $root);

$root_count = $count - substr_count(BASEDIR, "/");
$fusion_root = '';
for ($i = 0; $i < $root_count; $i++) { // moved 0 to 1 will crash.
    $fusion_root .= "../";
}
define("FUSION_ROOT", $fusion_root);

// Calculate current true url
$script_url = explode("/", $_SERVER['PHP_SELF']);
$url_count = count($script_url);
$base_url_count = substr_count(BASEDIR, "/") + 1;
$current_page = "";
while ($base_url_count != 0) {
    $current = $url_count - $base_url_count;
    $current_page .= "/".$script_url[$current];
    $base_url_count--;
}

define("TRUE_PHP_SELF", $current_page);
define("START_PAGE", substr(preg_replace("#(&amp;|\?)(s_action=edit&amp;shout_id=)([0-9]+)#s", "", TRUE_PHP_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "")), 1));

// Autenticate user
require_once CLASSES."Authenticate.class.php";

// Log in user
if (isset($_POST['login']) && isset($_POST['user_name']) && isset($_POST['user_pass'])) {
    $auth = new Authenticate($_POST['user_name'], $_POST['user_pass'], (isset($_POST['remember_me'])));
    $userdata = $auth->getUserData();
    unset($auth, $_POST['user_name'], $_POST['user_pass']);
} else if (isset($_GET['logout']) && $_GET['logout'] == "yes") {
    $userdata = Authenticate::logOut();
    redirect(BASEDIR."index.php");
} else {
    $userdata = Authenticate::validateAuthUser();
}

// User level, Admin Rights & User Group definitions
define("iGUEST", $userdata['user_level'] == 0 ? 1 : 0);
define("iMEMBER", $userdata['user_level'] >= 101 ? 1 : 0);
define("iADMIN", $userdata['user_level'] >= 102 ? 1 : 0);
define("iSUPERADMIN", $userdata['user_level'] == 103 ? 1 : 0);
define("iUSER", $userdata['user_level']);
define("iUSER_RIGHTS", $userdata['user_rights']);
define("iUSER_GROUPS", substr($userdata['user_groups'], 1));

if ($settings['site_seo']) {
    require_once CLASSES."PHPFusion/Rewrite/RewriteDriver.php";
    require_once CLASSES."PHPFusion/Rewrite/Router.php";
    require_once CLASSES."PHPFusion/Rewrite/Permalinks.php";
}

// Language Engine
include BASEDIR."maincore_mlang_functions.php";

// Get enabled language settings
$language_opts = fusion_get_enabled_languages();
$enabled_languages = array_keys($language_opts);

// Main language detection procedure
if (iMEMBER && valid_language($userdata['user_language'])) {
    $current_lang = $userdata['user_language'];
} else {
    $data = dbarray(dbquery("SELECT * FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
    $current_lang = !empty($langData['user_language']) ? $data['user_language'] : $settings['locale'];
}

// Check if definitions have been set, if not set the default language to system language
if (!defined("LANGUAGE")) {
    define("LANGUAGE", $current_lang);
}

if (!defined("LOCALESET")) {
    define("LOCALESET", $current_lang."/");
}

// If language change is initiated and if the selected language is valid
if (isset($_GET['lang']) && valid_language($_GET['lang'])) {
    $lang = stripinput($_GET['lang']);
    set_language($lang);
    //    $redirectPath = clean_request("", array("lang"), FALSE);
    //    redirect($redirectPath);
} else {
    if (is_array($enabled_languages) && count($enabled_languages) > 1) {
        require __DIR__.'/maincore_mlang_hub.php';
    }
}

// IP address functions
include INCLUDES."ip_handling_include.php";

// Error Handling
require_once INCLUDES."error_handling_include.php";

// Load the Global language file
include LOCALE.LOCALESET."global.php";
$setlocale = empty($locale['setlocale']) ? 'en_GB' : $locale['setlocale'];
setlocale(LC_ALL, $setlocale.'.UTF-8');

if (iADMIN) {
    define("iAUTH", substr(md5($userdata['user_password'].USER_IP), 16, 16));
    $aidlink = "?aid=".iAUTH;
}

// PHPFusion user cookie functions
if (!isset($_COOKIE[COOKIE_PREFIX.'visited'])) {
    $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value=settings_value+1 WHERE settings_name='counter'");
    setcookie(COOKIE_PREFIX."visited", "yes", time() + 31536000, "/", "", "0");
}
$lastvisited = Authenticate::setLastVisitCookie();

require_once CLASSES."PHPFusion/OutputHandler.php";
$fusion_page_head_tags = &\PHPFusion\OutputHandler::$pageHeadTags;
$fusion_page_footer_tags = &\PHPFusion\OutputHandler::$pageFooterTags;
$fusion_jquery_tags = &\PHPFusion\OutputHandler::$jqueryTags;

// Set theme
set_theme($userdata['user_theme']);

// Check if a given theme exists and is valid
function theme_exists($theme) {
    $settings = fusion_get_settings();

    if ($theme == "Default") {
        $theme = $settings['theme'];
    }
    if (!file_exists(THEMES) || !is_dir(THEMES) || !is_string($theme) || !preg_match("/^([a-z0-9_-]){2,50}$/i", $theme) || !file_exists(THEMES.$theme)) {
        return FALSE;
    } else if (file_exists(THEMES.$theme."/theme.php") && file_exists(THEMES.$theme."/styles.css")) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Set a valid theme
function set_theme($theme) {
    global $locale;

    if (!defined("THEME")) {
        // If the theme is valid set it
        if (theme_exists($theme)) {
            define("THEME", THEMES.($theme == "Default" ? fusion_get_settings('theme') : $theme)."/");
            // The theme is invalid, search for a valid one inside themes folder and set it
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
        // If can't find and set any valid theme show a warning
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

function set_status_header($code = 200) {
    if (headers_sent()) {
        return FALSE;
    }

    $protocol = $_SERVER['SERVER_PROTOCOL'];

    if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
        $protocol = 'HTTP/1.0';
    }

    $desc = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended'
    ];

    $desc = isset($desc[$code]) ? $desc[$code] : '';

    header("$protocol $code $desc");

    return TRUE;
}

function redirect($location, $delay = FALSE, $script = FALSE, $code = 200) {
    if (!defined('STOP_REDIRECT')) {
        if (isnum($delay)) {
            $ref = "<meta http-equiv='refresh' content='$delay; url=".$location."' />";
            add_to_head($ref);
        } else {
            if ($script == FALSE && !headers_sent()) {
                set_status_header($code);
                header("Location: ".str_replace("&amp;", "&", $location));
                exit;
            } else {
                echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
                exit;
            }
        }
    } else {
        debug_print_backtrace();
        echo "redirected to ".$location;
    }
}

/*
function redirect($location, $script = false) {
    if (!$script) {
        header("Location: ".str_replace("&amp;", "&", $location));
        exit;
    } else {
        echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
        exit;
    }
}
*/
// Clean URL Function, prevents entities in server globals
function cleanurl($url) {
    $bad_entities = ["&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*"];
    $safe_entities = ["&amp;", "", "", "", "", "", "", "", "", ""];
    $url = str_replace($bad_entities, $safe_entities, $url);
    return $url;
}

// Strip Input Function, prevents HTML in unwanted places
function stripinput($text) {
    if (!is_array($text)) {
        $text = stripslash(trim($text));
        $text = preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", $text);
        $search = ["&", "\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;"];
        $replace = ["&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " "];
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
        $check_url = str_replace(["\"", "\'"], ["", ""], urldecode($check_url));
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
    $search = ["&", "\"", "'", "\\", "<", ">"];
    $replace = ["&amp;", "&quot;", "&#39;", "&#92;", "&lt;", "&gt;"];
    $text = str_replace($search, $replace, $text);
    return $text;
}

// Trim a line of text to a preferred length
function trimlink($text, $length) {
    if (strlen($text) > $length) {
        if (function_exists('mb_substr')) {
            $text = mb_substr($text, 0, ($length - 3), 'UTF-8')."...";
        } else {
            $text = substr($text, 0, ($length - 3))."...";
        }
    }
    return $text;
}

/**
 * Validate numeric input.
 *
 * @param mixed $value    The value to be checked.
 * @param bool  $decimal  Decimals.
 * @param bool  $negative Negative numbers.
 *
 * @return bool True if the value is a number.
 */
function isnum($value, $decimal = FALSE, $negative = FALSE) {
    if ($negative == TRUE) {
        return is_numeric($value);
    } else {
        $float = $decimal ? '(.{0,1})[0-9]*' : '';

        return !is_array($value) and preg_match("/^[0-9]+".$float."$/", $value);
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
    $smiley_cache = [];
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $smiley_cache[] = [
                "smiley_code"  => $data['smiley_code'],
                "smiley_image" => $data['smiley_image'],
                "smiley_text"  => $data['smiley_text']
            ];
        }
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
            if ($i != 0 && ($i % 10 == 0)) {
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
    $bbcode_cache = [];
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $bbcode_cache[] = $data['bbcode_name'];
        }
    }
    return $bbcode_cache;
}

// Parse bbcode
function parseubb($text, $selected = FALSE) {
    global $bbcode_cache;
    $sel_bbcodes = '';

    if (!$bbcode_cache) {
        cache_bbcode();
    }
    if (is_array($bbcode_cache) && count($bbcode_cache)) {
        if ($selected) {
            $sel_bbcodes = explode("|", $selected);
        }

        $bbcodes = [];
        foreach ($bbcode_cache as $bbcode) {
            $bbcodes[$bbcode] = $bbcode;
        }

        if (!empty($bbcodes['code'])) {
            $move_to_top = $bbcodes['code'];
            unset($bbcodes['code']);
            array_unshift($bbcodes, $move_to_top);
        }

        foreach ($bbcodes as $bbcode) {
            if ($selected && in_array($bbcode, $sel_bbcodes)) {
                if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
                    if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
                        include(LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
                    } else if (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
                        include(LOCALE."English/bbcodes/".$bbcode.".php");
                    }
                    include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
                }
            } else if (!$selected) {
                if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
                    if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
                        include(LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
                    } else if (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
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
// http://rumkin.com/tools/mailto_encoder/
function hide_email($email, $title = "", $subject = "") {
    if (strpos($email, "@")) {
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
    $text = str_replace("\t", "&nbsp; &nbsp;", $text);
    $text = str_replace(["[", "]"], ["&#91;", "&#93;"], $text);
    $text = preg_replace("/^ {1}/m", "&nbsp;", $text);
    return $text;
}

// Highlights given words in subject
// Don't forget to remove later
function highlight_words($word, $subject) {
    for ($i = 0, $l = count($word); $i < $l; $i++) {
        $word[$i] = str_replace(["\\", "+", "*", "?", "[", "^", "]", "$", "(", ")", "{", "}", "=", "!", "<", ">", "|", ":", "#", "-", "_"], "", $word[$i]);
        if (!empty($word[$i])) {
            $subject = preg_replace("#($word[$i])(?![^<]*>)#i", "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px'>\${1}</span>", $subject);
        }
    }
    return $subject;
}

/**
 * This function sanitize text
 *
 * @param string  $text
 * @param boolean $striptags FALSE if you don't want to remove html tags. TRUE by default
 * @param bool    $strip_scripts
 *
 * @return string|array
 */
function descript($text, $striptags = TRUE, $strip_scripts = TRUE) {
    if (is_array($text) || is_null($text)) {
        return $text;
    }

    // Convert problematic ascii characters to their true values
    $patterns = [
        '#(&\#x)([0-9A-F]+);*#si'                           => '',
        '#(/\bon\w+=\S+(?=.*>))#is'                         => '',
        '#([a-z]*)=([\`\'\"]*)script:#iU'                   => '$1=$2nojscript...',
        '#([a-z]*)=([\`\'\"]*)javascript:#iU'               => '$1=$2nojavascript...',
        '#([a-z]*)=([\'\"]*)vbscript:#iU'                   => '$1=$2novbscript...',
        '#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU' => "$1>",
        '#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU'  => "$1>"
    ];

    foreach (array_merge(['(', ')', ':'], range('A', 'Z'), range('a', 'z')) as $chr) {
        $patterns["#(&\#)(0*".ord($chr)."+);*#si"] = $chr;
    }

    if ($striptags) {
        do {
            $count = 0;
            //$iframe = !defined('ENABLE_IFRAME') ? 'embed|iframe|' : '';
            $iframe = '';
            $text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|object|'.$iframe.'frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text, -1, $count);
        } while ($count);
    }

    $text = preg_replace(array_keys($patterns), $patterns, $text);

    $preg_patterns = [
        // Fix &entity\n
        '!(&#0+[0-9]+)!'                                                                                                                                                                                => '$1;',
        '/(&#*\w+)[\x00-\x20]+;/u'                                                                                                                                                                      => '$1;>',
        '/(&#x*[0-9A-F]+);*/iu'                                                                                                                                                                         => '$1;',
        //any attribute starting with "on" or xml name space
        '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu'                                                                                                                                                => '$1>',
        //javascript: and VB script: protocols
        '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu' => '$1=$2nojavascript...',
        '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu'                                        => '$1=$2novbscript...',
        '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u'                                                                                                                         => '$1=$2nomozbinding...',
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i'                                                                                                           => '$1>',
        '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu'                                                  => '$1>',
        // namespace elements
        '#</*\w+:\w[^>]*+>#i'                                                                                                                                                                           => ''
    ];

    if ($strip_scripts) {
        $preg_patterns += [
            '#<script(.*?)>(.*?)</script>#is' => ''
        ];
    }

    foreach ($preg_patterns as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text);
    }

    return $text;
}

// Scan image files for malicious code
function verify_image($file) {
    $txt = file_get_contents($file);
    $image_safe = TRUE;
    if (preg_match('#<?php#i', $txt)) {
        $image_safe = FALSE;
    } //edit
    else if (preg_match('#&(quot|lt|gt|nbsp|<?php);#i', $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#&\#x([0-9a-f]+);#i", $txt)) {
        $image_safe = FALSE;
    } else if (preg_match('#&\#([0-9]+);#i', $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#([a-z]*)=([\`\'\"]*)script:#iU", $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#([a-z]*)=([\`\'\"]*)javascript:#iU", $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#([a-z]*)=([\'\"]*)vbscript:#iU", $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU", $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU", $txt)) {
        $image_safe = FALSE;
    } else if (preg_match("#</*(applet|link|style|script|iframe|frame|frameset)[^>]*>#i", $txt)) {
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
            if ($word_list[$i] != "")
                $text = preg_replace("/".$word_list[$i]."/si", $settings['bad_word_replace'], $text);
        }
    }
    return $text;
}

if ($settings['mime_check'] == 1) {
    if (isset($_FILES) && count($_FILES)) {
        require_once INCLUDES."mimetypes_include.php";
        $mime_types = (array)mimeTypes();
        foreach ($_FILES as $each) {
            if (isset($each['name']) && !empty($each['name']) && !empty($each['tmp_name'])) {
                if (is_array($each['name'])) {
                    for ($i = 0; $i < count($each['name']); $i++) {
                        $file_info = pathinfo($each['name'][$i]);
                        if (!empty($file_info['extension'])) {
                            $extension = strtolower($file_info['extension']);
                            if (isset($mime_types[$extension])) {
                                if (is_array($mime_types[$extension])) {
                                    $valid_mimetype = FALSE;
                                    foreach ($mime_types[$extension] as $each_mimetype) {
                                        if ($each_mimetype == $each['type'][$i]) {
                                            $valid_mimetype = TRUE;
                                            break;
                                        }
                                    }
                                    if (!$valid_mimetype) {
                                        die('Prevented an unwanted file upload attempt - 1! Unknown MIME Type '.$each['type'][$i]);
                                    }
                                    unset($valid_mimetype);
                                } else {
                                    if ($mime_types[$extension] !== $each['type'][$i]) {
                                        die('Prevented an unwanted file upload attempt - 2! Unknown MIME Type '.$each['type'][$i]);
                                    }
                                }
                            }
                            unset($file_info, $extension);
                        }
                    }
                } else {
                    $file_info = pathinfo($each['name']);
                    if (!empty($file_info['extension'])) {
                        $extension = strtolower($file_info['extension']);
                        if (isset($mime_types[$extension])) {
                            if (is_array($mime_types[$extension])) {
                                $valid_mimetype = FALSE;
                                foreach ($mime_types[$extension] as $each_mimetype) {
                                    if ($each_mimetype == $each['type']) {
                                        $valid_mimetype = TRUE;
                                        break;
                                    }
                                }
                                if (!$valid_mimetype) {
                                    die('Prevented an unwanted file upload attempt - 3! Unknown MIME Type '.$each['type']);
                                }
                                unset($valid_mimetype);
                            } else {
                                if ($mime_types[$extension] !== $each['type']) {
                                    die('Prevented an unwanted file upload attempt - 4! Unknown MIME Type '.$each['type']);
                                }
                            }
                        }
                        unset($file_info, $extension);
                    }
                }
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
    } else if ($userlevel == 102) {
        return $locale['user2'];
    } else if ($userlevel == 103) {
        return $locale['user3'];
    }

    return NULL;
}

// Display the user's status
function getuserstatus($userstatus) {
    global $locale;
    if ($userstatus == 0) {
        return $locale['status0'];
    } else if ($userstatus == 1) {
        return $locale['status1'];
    } else if ($userstatus == 2) {
        return $locale['status2'];
    } else if ($userstatus == 3) {
        return $locale['status3'];
    } else if ($userstatus == 4) {
        return $locale['status4'];
    } else if ($userstatus == 5) {
        return $locale['status5'];
    } else if ($userstatus == 6) {
        return $locale['status6'];
    } else if ($userstatus == 7) {
        return $locale['status7'];
    } else if ($userstatus == 8) {
        return $locale['status8'];
    }

    return NULL;
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
    } else if (iADMIN && ($group == "0" || $group == "101" || $group == "102")) {
        return TRUE;
    } else if (iMEMBER && ($group == "0" || $group == "101")) {
        return TRUE;
    } else if (iGUEST && $group == "0") {
        return TRUE;
    } else if (iMEMBER && $group && in_array($group, explode(".", iUSER_GROUPS))) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// Cache groups mysql
function cache_groups() {
    global $groups_cache;
    $result = dbquery("SELECT * FROM ".DB_USER_GROUPS." ORDER BY group_id ASC");
    $groups_cache = [];
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $groups_cache[] = $data;
        }
    }
    return $groups_cache;
}

// Compile access levels & user group array
function getusergroups() {
    global $locale, $groups_cache;
    $groups_array = [
        ["0", $locale['user0']],
        ["101", $locale['user1']],
        ["102", $locale['user2']],
        ["103", $locale['user3']]
    ];
    if (!$groups_cache) {
        cache_groups();
    }
    if (is_array($groups_cache) && count($groups_cache))
        foreach ($groups_cache as $group) {
            $groups_array[] = [$group['group_id'], $group['group_name']];
        }
    return $groups_array;
}

// Get the name of the access level or user group
function getgroupname($group_id, $return_desc = FALSE) {
    global $locale, $groups_cache;
    if ($group_id == "0") {
        return $locale['user0'];
    } else if ($group_id == "101") {
        return $locale['user1'];
        exit;
    } else if ($group_id == "102") {
        return $locale['user2'];
        exit;
    } else if ($group_id == "103") {
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
    $res = '';
    if (iGUEST) {
        return "$field = '0'";
    } else if (iSUPERADMIN) {
        return "1 = 1";
    } else if (iADMIN) {
        $res = "($field='0' OR $field='101' OR $field='102'";
    } else if (iMEMBER) {
        $res = "($field='0' OR $field='101'";
    }
    if (iUSER_GROUPS != "" && !iSUPERADMIN) {
        $res .= " OR $field='".str_replace(".", "' OR $field='", iUSER_GROUPS)."'";
    }
    $res .= ")";
    return $res;
}

// Create a list of files or folders and store them in an array
// You may filter out extensions by adding them to $extfilter as:
// $ext_filter = "gif|jpg"
function makefilelist($folder, $filter, $sort = TRUE, $type = "files", $ext_filter = "") {
    $res = [];
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
        } else if ($type == "folders" && !in_array($file, $filter)) {
            if (is_dir($folder.$file)) {
                $res[] = $file;
            }
        }
    }
    closedir($temp);
    if ($sort) {
        natsort($res);
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
    if (!preg_match("#[0-9]+#", $count) || $count == 0)
        return FALSE;

    $pg_cnt = ceil($total / $count);
    if ($pg_cnt <= 1) {
        return "";
    }

    $idx_back = $start - $count;
    $idx_next = $start + $count;
    $cur_page = ceil(($start + 1) / $count);

    $res = $locale['global_092']." ".$cur_page.$locale['global_093'].$pg_cnt.": ";
    if ($idx_back >= 0) {
        if ($cur_page > ($range + 1)) {
            $res .= "<a href='".$link.$getname."=0'>1</a>";
            if ($cur_page != ($range + 2)) {
                $res .= "...";
            }
        }
    }
    $idx_fst = max($cur_page - $range, 1);
    $idx_lst = min($cur_page + $range, $pg_cnt);
    if ($range == 0) {
        $idx_fst = 1;
        $idx_lst = $pg_cnt;
    }
    for ($i = $idx_fst; $i <= $idx_lst; $i++) {
        $offset_page = ($i - 1) * $count;
        if ($i == $cur_page) {
            $res .= "<span><strong>".$i."</strong></span>";
        } else {
            $res .= "<a href='".$link.$getname."=".$offset_page."'>".$i."</a>";
        }
    }
    if ($idx_next < $total) {
        if ($cur_page < ($pg_cnt - $range)) {
            if ($cur_page != ($pg_cnt - $range - 1)) {
                $res .= "...";
            }
            $res .= "<a href='".$link.$getname."=".($pg_cnt - 1) * $count."'>".$pg_cnt."</a>\n";
        }
    }

    return "<div class='pagenav'>\n".$res."</div>\n";
}

// Format the date & time accordingly
function showdate($format, $val) {
    global $settings, $userdata;

    if (isset($userdata['user_offset'])) {
        $offset = $userdata['user_offset'] + $settings['serveroffset'];
    } else {
        $offset = $settings['timeoffset'] + $settings['serveroffset'];
    }
    if ($format == "shortdate" || $format == "longdate" || $format == "forumdate" || $format == "newsdate") {
        return format_date($settings[$format], $val + ($offset * 3600));
    } else {
        return format_date($format, $val + ($offset * 3600));
    }
}

/**
 * Format date - replacement for strftime()
 *
 * @param string $format Dateformat
 * @param int    $time   Timestamp
 *
 * @return string
 */
function format_date($format, $time) {
    $format = str_replace(
        ['%a', '%A', '%d', '%e', '%u', '%w', '%W', '%b', '%h', '%B', '%m', '%y', '%Y', '%D', '%F', '%x', '%n', '%t', '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z', '%c', '%s', '%%'],
        ['D', 'l', 'd', 'j', 'N', 'w', 'W', 'M', 'M', 'F', 'm', 'y', 'Y', 'm/d/y', 'Y-m-d', 'm/d/y', "\n", "\t", 'H', 'G', 'h', 'g', 'i', 'A', 'a', 'h:i:s A', 'H:i', 's', 'H:i:s', 'H:i:s', 'O', 'T', 'D M j H:i:s Y', 'U', '%'],
        $format
    );

    $date = DateTimeImmutable::createFromFormat('U', $time);

    return $date->format($format);
}

// Translate bytes into kB, MB, GB or TB by CrappoMan, lelebart fix
function parsebytesize($size, $digits = 2, $dir = FALSE) {
    global $locale;
    $kb = 1024;
    $mb = 1024 * $kb;
    $gb = 1024 * $mb;
    $tb = 1024 * $gb;
    if (($size == 0) && ($dir)) {
        return $locale['global_460'];
    } else if ($size < $kb) {
        return $size.$locale['global_461'];
    } else if ($size < $mb) {
        return round($size / $kb, $digits).$locale['global_462'];
    } else if ($size < $gb) {
        return round($size / $mb, $digits).$locale['global_463'];
    } else if ($size < $tb) {
        return round($size / $gb, $digits).$locale['global_464'];
    } else {
        return round($size / $tb, $digits).$locale['global_465'];
    }
}

// User profile link
function profile_link($user_id, $user_name, $user_status, $class = "profile-link") {
    global $locale, $settings;

    $class = ($class ? " class='$class'" : "");

    if ((in_array($user_status, [0, 3, 7]) || checkrights("M")) && (iMEMBER || $settings['hide_userprofiles'] == "0")) {
        $link = "<a href='".BASEDIR."profile.php?lookup=".$user_id."'".$class.">".$user_name."</a>";
    } else if ($user_status == "5" || $user_status == "6") {
        $link = $locale['user_anonymous'];
    } else {
        $link = $user_name;
    }

    return $link;
}

// New 8.0
function write_file($file, $data, $flags = NULL) {
    $bytes = NULL;
    if ($flags === NULL) {
        $bytes = \file_put_contents($file, $data);
    } else {
        $bytes = \file_put_contents($file, $data, $flags);
    }
    if (function_exists('opcache_invalidate')) {
        \opcache_invalidate($file, TRUE);
    }

    return $bytes;
}

function fusion_get_settings($key = NULL) {
    static $settings = [];
    if (empty($settings) and defined('DB_SETTINGS') and dbconnection() && db_exists('settings')) {
        $result = dbquery("SELECT * FROM ".DB_SETTINGS);
        while ($data = dbarray($result)) {
            $settings[$data['settings_name']] = $data['settings_value'];
        }
    }

    return $key === NULL ? $settings : (isset($settings[$key]) ? $settings[$key] : NULL);
}

function fusion_get_locale($key = NULL, $include_file = "") {
    global $locale;

    $is_sanitized = TRUE;

    if ($include_file && file_exists($include_file)) {
        include $include_file;
    }

    if (!empty($locale) && $is_sanitized == TRUE) {
        return $key === NULL ? $locale : (isset($locale[$key]) ? $locale[$key] : $locale);
    }
    return NULL;
}

function print_p($array, $modal = FALSE) {
    echo ($modal) ? openmodal('Debug', 'Debug') : '';
    echo "<pre style='white-space:pre-wrap !important;'>";
    print_r($array);
    echo "</pre>";
    echo ($modal) ? closemodal() : '';
}

function fusion_get_aidlink() {
    $aidlink = '';
    if (iADMIN) {
        $aidlink = '?aid='.iAUTH;
    }
    return $aidlink;
}

function fusion_parse_user($user_name, $tooltip = "") {
    $user_regex = '@[-0-9A-Z_\.]{1,50}';
    return preg_replace_callback("#$user_regex#im", function ($user_name) use ($tooltip) {
        return render_user_tags($user_name, $tooltip);
    }, $user_name);
}

function fusion_get_userdata($key = NULL) {
    global $userdata;
    if (empty($userdata)) {
        $userdata = ["user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => 'Default'];
    }
    $userdata = $userdata + [
            "user_id"     => 0,
            "user_name"   => fusion_get_locale("user_guest"),
            "user_status" => 1,
            "user_level"  => 0,
            "user_rights" => "",
            "user_groups" => "",
            "user_theme"  => fusion_get_settings("theme"),
        ];

    return $key === NULL ? $userdata : (isset($userdata[$key]) ? $userdata[$key] : NULL);
}

function form_user_select($input_name, $label = "", $input_value = FALSE, array $options = []) {
    global $locale;
    if (!session_status() == PHP_SESSION_ACTIVE) {
        session_start();
    }

    // $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $default_options = [
        'required'       => FALSE,
        'regex'          => '',
        'input_id'       => $input_name,
        'placeholder'    => $locale['sel_user'],
        'deactivate'     => FALSE,
        'safemode'       => FALSE,
        'allowclear'     => FALSE,
        'multiple'       => FALSE,
        'inner_width'    => '100%',
        'width'          => '100%',
        'keyflip'        => FALSE,
        'tags'           => FALSE,
        'jsonmode'       => FALSE,
        'chainable'      => FALSE,
        'max_select'     => 1,
        'error_text'     => '',
        'class'          => '',
        'inline'         => FALSE,
        'tip'            => '',
        'ext_tip'        => '',
        'delimiter'      => ',',
        'callback_check' => '',
        'file'           => '',
        'allow_self'     => FALSE,
    ];

    $options += $default_options;
    $options['input_id'] = trim($options['input_id'], "[]");
    $allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true," : '';
    $length = "minimumInputLength: 1,";
    $error_class = "";

    $html = "<div id='".$options['input_id']."-field' class='form-group m-b-0 ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' style='width:".$options['width']."'>\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? 'col-xs-12 col-sm-3' : 'col-xs-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')."</label>\n" : '';
    $html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9" : "col-sm-12")."'>\n" : "";
    $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width:".$options['inner_width']."'".($options['deactivate'] ? ' disabled' : '')."/>\n";
    if ($options['deactivate']) {
        $html .= form_hidden($input_name, '', $input_value, ["input_id" => $options['input_id']]);
    }
    $html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>".$options['ext_tip']."</i></div>" : "";
    $html .= $options['inline'] ? "</div>\n" : '';

    $html .= "</div>\n";
    $root_prefix = fusion_get_settings("site_seo") == 1 ? fusion_get_settings('siteurl')."includes/" : INCLUDES;
    $root_img = fusion_get_settings("site_seo") == 1 && !defined('ADMIN_PANEL') ? fusion_get_settings('siteurl') : '';
    $path = !empty($options['file']) ? $options['file'] : $root_prefix."jscripts/select2/users.json.php".($options['allow_self'] ? "?allow_self=true" : "");

    if (!empty($input_value)) {
        $encoded = !empty($options['file']) ? $options['file'] : user_search($input_value);
    } else {
        $encoded = json_encode([]);
    }

    add_to_jquery("
        function avatar(item) {
            if(!item.id) {return item.text;}
            var avatar = item.avatar;
            var level = item.level;
            return '<table><tr><td style=\"\"><img style=\"height:25px;\" class=\"img-rounded\" src=\"".$root_img.IMAGES."avatars/' + avatar + '\"/></td><td style=\"padding-left:10px; padding-right:10px;\"><div><strong>' + item.text + '</strong></div>' + level + '</div></td></tr></table>';
        }
        $('#".$options['input_id']."').select2({
        $length
        multiple: true,
        maximumSelectionSize: ".$options['max_select'].",
        placeholder: '".$options['placeholder']."',
        ajax: {
        url: '$path',
        dataType: 'json',
        data: function (term, page) {
                return {q: term};
              },
              results: function (data, page) {
                //console.log(page);
                return {results: data};
              }
        },
        formatSelection: avatar,
        escapeMarkup: function(m) { return m; },
        formatResult: avatar,
        ".$allowclear."
        })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
    ");

    if (!defined("SELECT2")) {
        define("SELECT2", TRUE);
        add_to_head("<link href='".INCLUDES."jscripts/select2/select2.min.css' rel='stylesheet' />");
        add_to_footer("<script src='".INCLUDES."jscripts/select2/select2.min.js'></script>");

        $select2_locale_path = INCLUDES."jscripts/select2/select2_locale_".$locale['select2'].".js";
        if (file_exists($select2_locale_path)) {
            add_to_footer("<script src='$select2_locale_path'></script>");
        }
    }

    return $html;
}

/* Returns Json Encoded Object used in form_select_user */
function user_search($user_id) {
    $encoded = json_encode([]);
    $user_id = stripinput($user_id);
    $result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM ".DB_USERS." WHERE user_status='0' AND user_id='$user_id'");
    if (dbrows($result) > 0) {
        while ($udata = dbarray($result)) {
            $user_id = $udata['user_id'];
            $user_avatar = !empty($udata['user_avatar']) ? $udata['user_avatar'] : "noavatar50.png";
            $user_name = $udata['user_name'];
            $user_level = getuserlevel($udata['user_level']);
            $user_opts[] = [
                'id'     => "$user_id",
                'text'   => "$user_name",
                'avatar' => "$user_avatar",
                "level"  => "$user_level"
            ];
        }
        if (!isset($user_opts)) {
            $user_opts = [];
        }
        $encoded = json_encode($user_opts);
    }

    return $encoded;
}

function form_hidden($input_name, $label = "", $input_value = "", array $options = []) {
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $html = '';
    $default_options = [
        'input_id'    => $input_name,
        'show_title'  => FALSE,
        'width'       => '100%',
        'class'       => '',
        'inline'      => FALSE,
        'required'    => FALSE,
        'placeholder' => '',
        'deactivate'  => FALSE,
        'delimiter'   => ',',
        'error_text'  => '',
    ];
    $options += $default_options;

    if ($options['show_title']) {
        $html .= "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$options['class']." '>\n";
        $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$title.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."</label>\n" : '';
        $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
    }
    $html .= "<input type='hidden' name='$input_name' id='".$options['input_id']."' value='$input_value' ".($options['width'] ? "style='width:".$options['width']."'" : '')." ".($options['show_title'] ? "" : "readonly")." />\n";
    if ($options['show_title']) {
        $html .= "<div id='".$options['input_id']."-help'></div>";
        $html .= ($options['inline']) ? "</div>\n" : "";
        $html .= "</div>\n";
    }
    return $html;
}

/**
 * Pure trim function
 *
 * @param string $str
 * @param bool   $length
 *
 * @return string
 */
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

// Check for installed infusion
function infusion_exists($infusion_folder) {
    static $infusions_installed = [];
    if (empty($infusions_installed)) {
        $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $infusions_installed[$data['inf_folder']] = TRUE;
            }
        }
    }
    return isset($infusions_installed[$infusion_folder]);
}

function db_exists($table) {
    if (strpos($table, DB_PREFIX) === FALSE) {
        $table = DB_PREFIX.$table;
    }
    $query = dbquery("SHOW TABLES LIKE '$table'");

    return boolval(dbrows($query));
}

/**
 * MYSQL Show Columns Shorthand
 * Returns available columns in a table
 *
 * @param $db
 *
 * @return array
 */
function fieldgenerator($db) {
    static $col_names = [];

    if (empty($col_names[$db])) {
        $cresult = dbquery("SHOW COLUMNS FROM $db");
        $col_names = [];
        while ($cdata = dbarray($cresult)) {
            $col_names[$db][] = $cdata['Field'];
        }
    }

    return (array)$col_names[$db];
}

/**
 * Determine whether column exists in a table
 *
 * @param           $table
 * @param           $column
 * @param bool|TRUE $add_prefix
 *
 * @return bool
 */
function column_exists($table, $column, $add_prefix = TRUE) {

    static $table_config = [];

    if ($add_prefix === TRUE) {
        if (strpos($table, DB_PREFIX) === FALSE) {
            $table = DB_PREFIX.$table;
        }
    }

    if (empty($table_config[$table])) {
        $table_config[$table] = array_flip(fieldgenerator($table));
    }

    return (isset($table_config[$table][$column]));
}

/**
 * To flatten ANY multidimensional array
 * Best used to flatten any hierarchy array data
 *
 * @param $result
 *
 * @return mixed
 */
function flatten_array($result) {
    return call_user_func_array('array_merge', $result);
}

/**
 * Remove folder and all files/subdirectories
 *
 * @param string $dir
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir.'/'.$object) == 'dir') {
                    rrmdir($dir.'/'.$object);
                } else {
                    unlink($dir.'/'.$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * Get HTTP response code.
 *
 * @param string $url URL.
 *
 * @return false|string
 */
function get_http_response_code($url) {
    if (function_exists('curl_init')) {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return $http_code;
    } else {
        stream_context_set_default([
            'ssl' => [
                'verify_peer'      => FALSE,
                'verify_peer_name' => FALSE
            ],
        ]);

        $headers = @get_headers($url);
        return substr($headers[0], 9, 3);
    }
}

/**
 * cURL method to get any contents for Apache that does not support SSL for remote paths.
 *
 * @param string $url
 *
 * @return bool|string
 */
function fusion_get_contents($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
    } else {
        $data = @file_get_contents($url);
    }
    return $data;
}

include INCLUDES."system_images.php";

$inf_folder = makefilelist(INFUSIONS, '.|..|.htaccess|index.php|._DS_Store|.tmp', TRUE, 'folders');
if (!empty($inf_folder)) {
    foreach ($inf_folder as $folder) {
        $inf_include = INFUSIONS.$folder."/infusion_db.php";
        if (file_exists($inf_include)) {
            include $inf_include;
        }
    }
}

if (file_exists(INCLUDES.'custom_includes.php')) {
    require_once INCLUDES.'custom_includes.php';
}
