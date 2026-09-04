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


use Defender\Token;

use PHPFusion\Authenticate;


if (preg_match("/maincore.php/i", $_SERVER['PHP_SELF'])) {

    die();
}


if (!defined('IN_FUSION')) {

    define('IN_FUSION', TRUE);
}


/**
 * Check maintenance mode.
 */

function check_maintenance_mode() {

    $file = __DIR__.'/.maintenance';

    if (!file_exists($file)) {

        return;

    }


    global $mt_mode_start;

    include_once $file;


    if ((time() - $mt_mode_start) >= 600) {

        return;

    }


    die('Shortly unavailable for scheduled maintenance. Please check again in a few minutes.');

}

check_maintenance_mode();

require_once __DIR__.'/includes/core_resources_include.php';
require_once __DIR__.'/includes/classes/PHPFusion/Social/hooks.php';


// Prevent any possible XSS attacks via $_GET.

if (stripget($_GET)) {

    die("Prevented an XSS attack through a GET variable!");

}


// Establish mySQL database connection

if (!empty($db_host) && !empty($db_user) && !empty($db_name)) {

    dbconnect($db_host, $db_user, (!empty($db_pass) ? $db_pass : ''), $db_name, (!empty($db_port) ? $db_port : 3306));
}


// Fetch the settings from the database
$settings = fusion_get_settings();

// Keep CSRF tokens and sessions on the configured canonical host. Host-only
// session cookies issued on a www alias cannot validate forms on the apex host.
$canonical_url = parse_url((string)($settings['siteurl'] ?? ''));
$canonical_host = strtolower((string)($canonical_url['host'] ?? ''));
$request_host = strtolower((string)parse_url('http://'.($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));

if (
    $canonical_host !== ''
    && $request_host === 'www.'.$canonical_host
    && !headers_sent()
) {
    $canonical_scheme = (string)($canonical_url['scheme'] ?? ($settings['site_protocol'] ?? 'https'));
    $canonical_origin = $canonical_scheme.'://'.$canonical_host;
    if (!empty($canonical_url['port'])) {
        $canonical_origin .= ':'.(int)$canonical_url['port'];
    }

    $request_uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    if ($request_uri === '' || preg_match('/[\r\n]/', $request_uri)) {
        $request_uri = '/';
    }

    $request_method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $redirect_code = in_array($request_method, ['GET', 'HEAD'], TRUE) ? 301 : 303;
    redirect($canonical_origin.$request_uri, FALSE, FALSE, $redirect_code);
}

if (!empty($settings['error_logging_enabled']) && $settings['error_logging_enabled'] == 1) {

    ini_set('display_errors', '1');

} else {

    error_reporting(0);
}

// Settings dependent functions
date_default_timezone_set(fusion_get_settings('timeoffset') ?: 'UTC');
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Session lifetime. After this time stored data will be seen as 'garbage' and cleaned up by the garbage collection process.
ini_set('session.gc_maxlifetime', 172800); // 48 hours

// Session cookie lifetime
ini_set('session.cookie_lifetime', 172800); // 48 hours

// Prevent document expiry when user hits Back in browser

session_cache_limiter('private, must-revalidate');

session_name(COOKIE_PREFIX.'session');

// Start DB session.

if (!empty($settings['database_sessions']) && (!empty($db_host) && !empty($db_user) && !empty($db_name))) {

    // Establish secondary MySQL database connection for session caches

    $handler = \PHPFusion\Sessions::getInstance(COOKIE_PREFIX.'session')->setConfig(

        $db_host, $db_user, (!empty($db_pass) ? $db_pass : ''), $db_name, (!empty($db_port) ? $db_port : 3306)

    );

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
// Check if we actually have a session ID
$s_id = session_id();
if (empty($s_id)) {
    // FALLBACK: If sessions are broken, we use a temporary ID
    // based on IP + User Agent so we can still track the user today.
    $s_id = 'guest_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
}
define('SESSION_ID', $s_id);

if (empty($settings)) {
    if (file_exists(BASEDIR.'install.php')) {
        if (file_exists(BASEDIR.'config.php')) {
            @rename(BASEDIR.'config.php', BASEDIR.'config_backup_'.time().'.php');
        }

        redirect(BASEDIR.'install.php');
    }
    die("Website configurations do not exist, please check your config.php file or run install.php again.");
}


header('X-Powered-By: PHPFusion'.(isset($settings['version']) ? ' '.$settings['version'] : ''));


ob_start();


// Sanitise $_SERVER globals

$_SERVER['PHP_SELF'] = cleanurl($_SERVER['PHP_SELF']);

$_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']) ? cleanurl($_SERVER['QUERY_STRING']) : "";

$_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? cleanurl($_SERVER['REQUEST_URI']) : "";

$PHP_SELF = cleanurl($_SERVER['PHP_SELF']);


// Redirects to the index if the URL is invalid (e.g. file.php/folder/)

if ($_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) {

    redirect($settings['siteurl']);

}


// Force protocol change if https turned on main settings
if (!defined("DEVELOPMENT") && $settings['site_protocol'] == 'https' && (
    !(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    )) {
    redirect('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}


// Redirect to correct path if there are double // in the current uri

if (substr_count($_SERVER['REQUEST_URI'], '//')) {

    $site_path = preg_replace('/(\/+)/', '/', $_SERVER['REQUEST_URI']);

    redirect(rtrim($settings['siteurl'], '/').$site_path);

}


define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");

define("FUSION_SELF", basename($_SERVER['PHP_SELF']));

define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);

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

$root = str_repeat("../", $count);

define("ROOT", $root);


$root_count = $count - substr_count(BASEDIR, "/");

$fusion_root = str_repeat("../", $root_count);

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

define("START_PAGE", substr(preg_replace(

    "#(&amp;|\?)(s_action=edit&amp;shout_id=)([0-9]+)#s", "",

    TRUE_PHP_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "")

), 1));


/**
 * Login / Logout / Revalidate
 */
$userdata = [];

if (!defined('FUSION_API_REQUEST') && check_post('login') && check_post('user_name') && check_post('user_pass')) {

    if (fusion_safe()) {

        $username = post('user_name');
        $userpass = post('user_pass');
        $remember_me = check_post('remember_me');

        $auth = new Authenticate(BASEDIR.$settings['opening_page']);
        $auth->authenticate($username, $userpass, $remember_me);
    }

} else if (get('logout') === 'yes') {

    $userdata = Authenticate::logOut();

    $request = clean_request('', ['logout'], FALSE);

    redirect($request);

} else {

    $userdata = Authenticate::validateAuthUser();
}

// User level, Admin Rights & User Group definitions
define("iGUEST", !empty($userdata) && $userdata['user_level'] == USER_LEVEL_PUBLIC ? 1 : 0);
define("iMEMBER", !empty($userdata) && $userdata['user_level'] <= USER_LEVEL_MEMBER ? 1 : 0);
define("iADMIN", !empty($userdata) && $userdata['user_level'] <= USER_LEVEL_ADMIN ? 1 : 0);
define('iSTAFF', !empty($userdata) && !empty($userdata['staff_id']) ? 1 : 0);
define('iCLERK', !empty($userdata) && !empty($userdata['staff_role']) && in_array($userdata['staff_role'], ['clerk', 'administrator'])  ? 1 : 0);
define('iTEACHER', !empty($userdata) && !empty($userdata['staff_role']) &&  $userdata['staff_role'] == 'teacher' ? 1 : 0);
define('iSTUDENT', !empty($userdata) && $userdata['user_level'] == USER_LEVEL_MEMBER && !iCLERK && !iTEACHER ? 1 : 0);
define("iSUPERADMIN", !empty($userdata) && $userdata['user_level'] == USER_LEVEL_SUPER_ADMIN ? 1 : 0);
define("iUSER", !empty($userdata) && $userdata['user_level']);
define("iUSER_RIGHTS", !empty($userdata) && $userdata['user_rights'] ? $userdata['user_rights'] : '');
define("iUSER_GROUPS", !empty($userdata) && $userdata['user_groups'] ? substr($userdata['user_groups'], 1) : '');

// Authenticated pages can contain user-specific links and must never be cached by a CDN.
if (iMEMBER && !headers_sent()) {
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('CDN-Cache-Control: no-store');
    header('Cloudflare-CDN-Cache-Control: no-store');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Vary: Cookie', FALSE);
}

// Main language detection procedure
static $current_user_language = [];

if (iMEMBER && valid_language($userdata['user_language'])) {

    $current_user_language = $userdata['user_language'];

} else {

    $langData = dbarray(dbquery('SELECT * FROM '.DB_LANGUAGE_SESSIONS.' WHERE user_ip=:ip', [':ip' => USER_IP]));

    $current_user_language = (!empty($langData['user_language']) ? $langData['user_language'] : fusion_get_settings('locale'));

}

$language_opts = fusion_get_enabled_languages();

$enabled_languages = array_keys($language_opts);


// If language change is initiated and if the selected language is valid

if (check_get('lang') && file_exists(LOCALE.get('lang')."/global.php") && in_array(get('lang'), $enabled_languages)) {

    $current_user_language = stripinput(get('lang'));

    set_language($current_user_language);

} else {

    if (count($enabled_languages) > 1) {
        require __DIR__.'/includes/core_mlang_hub_include.php';
    }
}


if (!defined('LANGUAGE')) {
    define('LANGUAGE', $current_user_language);
}

if (!defined('LOCALESET')) {
    define('LOCALESET', $current_user_language.'/');
}


$locale = [];
$global_locale_file = LOCALE.LOCALESET.'global.php';
$global_locale_files = [];

if (is_file($global_locale_file)) {
    $global_locale_files[] = $global_locale_file;
}
if (realpath($global_locale_file) !== realpath(LOCALE.'English/global.php')) {
    $global_locale_files[] = LOCALE.'English/global.php';
}

\PHPFusion\Locale::setLocale($global_locale_files);

$setlocale = empty(fusion_get_locale('setlocale')) ? 'en_GB' : fusion_get_locale('setlocale');

$win = explode('_', $setlocale);

setlocale(LC_ALL, $setlocale.'.UTF-8', $win[0]);

//setlocale(LC_ALL, $setlocale.'.UTF-8');


// IP address functions

include INCLUDES."ip_handling_include.php";


// Error Handling
require_once INCLUDES."error_handling_include.php";

if (!defined('FUSION_ALLOW_REMOTE')) {
    new Token();
}

Defender\ImageValidation::validateExtensions();

// Define a stable, session-bound admin aidlink.
if (iADMIN) {

    $admin_aid_context = hash_hmac('sha256', implode("\0", [
        (string)$userdata['user_id'],
        (string)$userdata['user_password'],
        (string)$userdata['user_level'],
        (string)$userdata['user_rights'],
    ]), SECRET_KEY);

    $stored_admin_aid = $_SESSION['admin_aidlink'] ?? '';
    $stored_admin_aid_context = $_SESSION['admin_aidlink_context'] ?? '';
    $valid_admin_aid = is_string($stored_admin_aid)
        && strlen($stored_admin_aid) === 64
        && ctype_xdigit($stored_admin_aid);
    $valid_admin_context = is_string($stored_admin_aid_context)
        && hash_equals($admin_aid_context, $stored_admin_aid_context);

    if (!$valid_admin_aid || !$valid_admin_context) {
        $_SESSION['admin_aidlink'] = bin2hex(random_bytes(32));
        $_SESSION['admin_aidlink_context'] = $admin_aid_context;
    }

    define("iAUTH", $_SESSION['admin_aidlink']);

    $aidlink = fusion_get_aidlink();

    // Generate a session aid every turn

    $token_time = time();

    $algo = fusion_get_settings('password_algorithm');

    $key = $userdata['user_id'].$token_time.iAUTH.SECRET_KEY;

    $salt = md5($userdata['user_admin_salt'].SECRET_KEY_SALT);

    $_SESSION['aid'] = $userdata['user_id'].".".$token_time.".".hash_hmac($algo, $key, $salt);

    unset($admin_aid_context, $stored_admin_aid, $stored_admin_aid_context, $valid_admin_aid, $valid_admin_context);
}


if (!defined('FUSION_API_REQUEST')) {
    // Page-only activity and presentation setup. API endpoints load dynamic
    // form components through their endpoint bootstrap when required.
    Authenticate::setVisitorCounter();
    Authenticate::setAdminLogin();
    Dynamics::getInstance();

    $_session_theme = session_get(COOKIE_PREFIX.'theme');
    $theme_session = $_session_theme && theme_exists($_session_theme) ? $_session_theme : FALSE;

    if ($_session_theme == fusion_get_settings('theme')) {
        session_remove(COOKIE_PREFIX.'theme');
    }

    $theme = $theme_session !== FALSE
        ? $theme_session
        : (empty($userdata['user_theme']) ? fusion_get_settings('theme') : $userdata['user_theme']);

    set_theme($theme);
}

$result = cdquery('installed_infusions', "SELECT inf_folder, inf_version FROM ".DB_INFUSIONS);

if (cdrows($result)) {

    while ($data = cdarray($result)) {

        if (file_exists(INFUSIONS.$data['inf_folder'])) {

            define(strtoupper($data['inf_folder']).'_EXISTS', TRUE);

            define(strtoupper($data['inf_folder']).'_EXIST', TRUE); // just in case

            define(strtoupper($data['inf_folder']).'_VERSION', $data['inf_version']);
        }
    }
}


PHPFusion\Installer\Infusions::loadConfiguration();
