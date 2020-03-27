<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maincore.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Authenticate;

// Uncomment to see server errors without modifying php.ini
ini_set('display_errors', '1');

if (preg_match("/maincore.php/i", $_SERVER['PHP_SELF'])) {
    die();
}

if (!defined('IN_FUSION')) {
    define('IN_FUSION', TRUE);
}

require_once __DIR__.'/includes/core_resources_include.php';

// Prevent any possible XSS attacks via $_GET.
if (stripget($_GET)) {
    die("Prevented an XSS attack through a GET variable!");
}

// Establish mySQL database connection
dbconnect($db_host, $db_user, $db_pass, $db_name, !empty($db_port) ? $db_port : 3306);
// Fetch the settings from the database
$settings = fusion_get_settings();

// Settings dependent functions
date_default_timezone_set('UTC');
//date_default_timezone_set($settings['default_timezone']);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
// Session lifetime. After this time stored data will be seen as 'garbage' and cleaned up by the garbage collection process.
ini_set('session.gc_maxlifetime', 172800); // 48 hours
// Session cookie life time
ini_set('session.cookie_lifetime', 172800); // 48 hours
// Prevent document expiry when user hits Back in browser
session_cache_limiter('private, must-revalidate');
session_name(COOKIE_PREFIX.'session');
// Start DB session.
if (!empty($settings['database_sessions'])) {
    // Establish secondary mySQL database connection for session caches
    $handler = \PHPFusion\Sessions::getInstance(COOKIE_PREFIX.'session')->setConfig($db_host, $db_user, $db_pass, $db_name, !empty($db_port) ? $db_port : 3306);
    session_set_save_handler(
        [$handler, '_open'],
        [$handler, '_close'],
        [$handler, '_read'],
        [$handler, '_write'],
        [$handler, '_destroy'],
        [$handler, '_clean']
    );
}
unset($db_host, $db_user, $db_pass);
@session_start();

if (empty($settings)) {
    if (file_exists(BASEDIR.'install.php')) {
        if (file_exists(BASEDIR.'config.php')) {
            @rename(BASEDIR.'config.php', BASEDIR.'config_backup_'.TIME.'.php');
        }
        redirect(BASEDIR.'install.php');
    }
    die("Website configurations do not exist, please check your config.php file or run install.php again.");
}

header('X-Powered-By: PHP-Fusion'.(isset($settings['version']) ? ' '.$settings['version'] : ''));

//ob_start("ob_gzhandler"); // Uncomment this line and comment the one below to enable output compression.
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
    $url = ((array)parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI']))) + [
            'path'  => '',
            'query' => ''
        ];
    $fusion_query = [];
    if ($url['query']) {
        parse_str($url['query'], $fusion_query); // this is original.
    }
    $prefix = !empty($fusion_query ? '?' : '');
    $site_path = str_replace($settings['site_path'], '', $url['path']);
    $site_path = $settings['siteurl'].$site_path.$prefix.http_build_query($fusion_query, 'flags_', '&amp;');
    redirect($site_path);
}

define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");
define("FUSION_SELF", basename($_SERVER['PHP_SELF']));
define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);

// Variables initializing
$mysql_queries_count = 0;
$mysql_queries_time = [];
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
define('FORM_REQUEST', fusion_get_settings('site_seo') && defined('IN_PERMALINK') ? PERMALINK_CURRENT_PATH : FUSION_REQUEST);
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

// Set TRUE_PHP_SELF and START_PAGE
define("TRUE_PHP_SELF", $current_page);
define("START_PAGE", substr(preg_replace("#(&amp;|\?)(s_action=edit&amp;shout_id=)([0-9]+)#s", "", TRUE_PHP_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "")), 1));

/**
 * Login / Logout / Revalidate
 */
if (isset($_POST['login']) && isset($_POST['user_name']) && isset($_POST['user_pass'])) {
    if (\defender::safe()) {
        $auth = new Authenticate($_POST['user_name'], $_POST['user_pass'], (isset($_POST['remember_me']) ? TRUE : FALSE));
        $userdata = $auth->getUserData();
        unset($auth, $_POST['user_name'], $_POST['user_pass']);
        redirect(FUSION_REQUEST);
    }
} else if (isset($_GET['logout']) && $_GET['logout'] == "yes") {
    $userdata = Authenticate::logOut();
    $request = clean_request('', ['logout'], FALSE);
    redirect($request);
} else {
    $userdata = Authenticate::validateAuthUser();
}

// User level, Admin Rights & User Group definitions
define("iGUEST", $userdata['user_level'] == USER_LEVEL_PUBLIC ? 1 : 0);
define("iMEMBER", $userdata['user_level'] <= USER_LEVEL_MEMBER ? 1 : 0);
define("iADMIN", $userdata['user_level'] <= USER_LEVEL_ADMIN ? 1 : 0);
define("iSUPERADMIN", $userdata['user_level'] == USER_LEVEL_SUPER_ADMIN ? 1 : 0);
define("iUSER", $userdata['user_level']);
define("iUSER_RIGHTS", $userdata['user_rights']);
define("iUSER_GROUPS", substr($userdata['user_groups'], 1));

// Main language detection procedure
static $current_user_language = [];
if (iMEMBER && valid_language($userdata['user_language'])) {
    $current_user_language = $userdata['user_language'];
} else {
    $langData = dbarray(dbquery('SELECT * FROM '.DB_LANGUAGE_SESSIONS.' WHERE user_ip=:ip', [':ip' => USER_IP]));
    $current_user_language = (!empty($langData['user_language']) ? $langData['user_language']: fusion_get_settings('locale'));
}
$language_opts = fusion_get_enabled_languages();
$enabled_languages = array_keys($language_opts);

// If language change is initiated and if the selected language is valid
if (isset($_GET['lang']) && isset($_GET['lang']) != "" && file_exists(LOCALE.$_GET['lang']."/global.php") && in_array($_GET['lang'], $enabled_languages)) {
    $current_user_language = stripinput($_GET['lang']);
    set_language($current_user_language);
} else {
    if (count($enabled_languages) > 1) {
        require __DIR__.'/includes/core_mlang_hub_include.php';
    }
}

if (!defined('LANGUAGE'))
    define('LANGUAGE', $current_user_language);
if (!defined('LOCALESET'))
    define('LOCALESET', $current_user_language.'/');

\PHPFusion\Locale::setLocale(LOCALE.LOCALESET.'global.php');

// IP address functions
include INCLUDES."ip_handling_include.php";

// Error Handling
require_once INCLUDES."error_handling_include.php";

$defender = defender::getInstance();

if (!defined('FUSION_ALLOW_REMOTE')) {
    new \Defender\Token();
}

\Defender\ImageValidation::ValidateExtensions();

// Define aidlink
if (iADMIN) {
    //@todo: to remove this part for non-global approach
    define("iAUTH", substr(md5($userdata['user_password'].USER_IP), 16, 16));
    $aidlink = fusion_get_aidlink();
    // Generate a session aid every turn
    $token_time = TIME;
    $algo = fusion_get_settings('password_algorithm');
    $key = $userdata['user_id'].$token_time.iAUTH.SECRET_KEY;
    $salt = md5($userdata['user_admin_salt'].SECRET_KEY_SALT);
    $_SESSION['aid'] = $userdata['user_id'].".".$token_time.".".hash_hmac($algo, $key, $salt);
}

// PHP-Fusion user cookie functions
if (!isset($_COOKIE[COOKIE_PREFIX.'visited'])) {
    $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value=settings_value+1 WHERE settings_name='counter'");
    setcookie(COOKIE_PREFIX."visited", "yes", time() + 31536000, "/", "", "0");
}

//$lastvisited = Authenticate::setLastVisitCookie();
//define('LASTVISITED', Authenticate::setLastVisitCookie());

// Set admin login procedures
Authenticate::setAdminLogin();

$fusion_dynamics = Dynamics::getInstance();
$fusion_page_head_tags = &\PHPFusion\OutputHandler::$pageHeadTags;
$fusion_page_footer_tags = &\PHPFusion\OutputHandler::$pageFooterTags;
$fusion_jquery_tags = &\PHPFusion\OutputHandler::$jqueryTags;
$fusion_css_tags = &\PHPFusion\OutputHandler::$cssTags;

// Set theme using $_GET as well.
// Set theme
if ($userdata['user_level'] == USER_LEVEL_SUPER_ADMIN && isset($_GET['themes']) && theme_exists($_GET['themes'])) {
    $newUserTheme = [
        "user_id"    => $userdata['user_id'],
        "user_theme" => stripinput($_GET['themes']),
    ];
    dbquery_insert(DB_USERS, $newUserTheme, "update");
    redirect(clean_request("", ["themes"], FALSE));
}
set_theme(empty($userdata['user_theme']) ? fusion_get_settings("theme") : $userdata['user_theme']);

function convert_accented_characters($text) {
   $text = trim($text);
   $search = ['Ç','ç','Ğ','ğ','ı','i','İ','Ö','ö','Ş','ş','Ü','ü'];
   $replace = ['C','C','G','G','I','I','I','O','O','S','S','U','U'];
   $new_text = str_replace($search,$replace,$text);
   return $new_text;
}

$result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS);
if (dbrows($result)) {
    while ($data = dbarray($result)) {
        if (file_exists(INFUSIONS.$data['inf_folder'])) {
            if(!function_exists('mb_strtoupper')){
                define(strtoupper(convert_accented_characters($data['inf_folder'])).'_EXIST', TRUE);
                define(strtoupper(convert_accented_characters($data['inf_folder'])).'_EXISTS', TRUE);
            }else{
                define(mb_strtoupper($data['inf_folder']).'_EXIST', TRUE);
                define(mb_strtoupper($data['inf_folder']).'_EXISTS', TRUE);
            }
        }
    }
}

/**
 * Reduction of 0.04 seconds in performance.
 * We can use manually include the configuration if needed.
 */
\PHPFusion\Installer\Infusion_core::load_Configuration();
