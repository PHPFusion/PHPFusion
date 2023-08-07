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

if (preg_match( "/maincore.php/i", $_SERVER['PHP_SELF'] )) {
    die();
}

if (!defined( 'IN_FUSION' )) {
    define( 'IN_FUSION', TRUE );
}

/**
 * Check maintenance mode.
 */
function check_maintenance_mode() {
    $file = __DIR__ . '/.maintenance';
    if (!file_exists( $file )) {
        return;
    }

    global $mt_mode_start;
    include_once $file;

    if ((time() - $mt_mode_start) >= 600) {
        return;
    }

    die( 'Shortly unavailable for scheduled maintenance. Please check again in a few minutes.' );
}

check_maintenance_mode();

require_once __DIR__ . '/includes/core_resources_include.php';

// Prevent any possible XSS attacks via $_GET.
if (stripget( $_GET )) {
    die( "Prevented an XSS attack through a GET variable!" );
}

// Establish mySQL database connection
if (!empty( $db_host ) && !empty( $db_user ) && !empty( $db_name )) {
    dbconnect( $db_host, $db_user, (!empty( $db_pass ) ? $db_pass : ''), $db_name, (!empty( $db_port ) ? $db_port : 3306) );
}

// Fetch the settings from the database
$settings = fusion_get_settings();

if (!empty( $settings['error_logging_enabled'] ) && $settings['error_logging_enabled'] == 1) {
    ini_set( 'display_errors', '1' );
} else {
    error_reporting( 0 );
}

// Settings dependent functions
date_default_timezone_set( 'UTC' );
ini_set( 'session.gc_probability', 1 );
ini_set( 'session.gc_divisor', 100 );
// Session lifetime. After this time stored data will be seen as 'garbage' and cleaned up by the garbage collection process.
ini_set( 'session.gc_maxlifetime', 172800 );  // 48 hours
// Session cookie lifetime
ini_set( 'session.cookie_lifetime', 172800 ); // 48 hours
// Prevent document expiry when user hits Back in browser
session_cache_limiter( 'private, must-revalidate' );
session_name( COOKIE_PREFIX . 'session' );
// Start DB session.
if (!empty( $settings['database_sessions'] ) && (!empty( $db_host ) && !empty( $db_user ) && !empty( $db_name ))) {
    // Establish secondary MySQL database connection for session caches
    $handler = \PHPFusion\Sessions::getInstance( COOKIE_PREFIX . 'session' )->setConfig(
        $db_host, $db_user, (!empty( $db_pass ) ? $db_pass : ''), $db_name, (!empty( $db_port ) ? $db_port : 3306)
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
unset( $db_host, $db_user, $db_pass );
@session_start();

if (empty( $settings )) {
    if (file_exists( BASEDIR . 'install.php' )) {
        if (file_exists( BASEDIR . 'config.php' )) {
            @rename( BASEDIR . 'config.php', BASEDIR . 'config_backup_' . time() . '.php' );
        }
        redirect( BASEDIR . 'install.php' );
    }
    die( "Website configurations do not exist, please check your config.php file or run install.php again." );
}

header( 'X-Powered-By: PHPFusion' . (isset( $settings['version'] ) ? ' ' . $settings['version'] : '') );

ob_start();

// Sanitise $_SERVER globals
$_SERVER['PHP_SELF'] = cleanurl( $_SERVER['PHP_SELF'] );
$_SERVER['QUERY_STRING'] = isset( $_SERVER['QUERY_STRING'] ) ? cleanurl( $_SERVER['QUERY_STRING'] ) : "";
$_SERVER['REQUEST_URI'] = isset( $_SERVER['REQUEST_URI'] ) ? cleanurl( $_SERVER['REQUEST_URI'] ) : "";
$PHP_SELF = cleanurl( $_SERVER['PHP_SELF'] );

// Redirects to the index if the URL is invalid (e.g. file.php/folder/)
if ($_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) {
    redirect( $settings['siteurl'] );
}

// Force protocol change if https turned on main settings
if ($settings['site_protocol'] == 'https' && (
    !(isset( $_SERVER['HTTPS'] ) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    )) {
    redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
}

// Redirect to correct path if there are double // in the current uri
if (substr_count( $_SERVER['REQUEST_URI'], '//' )) {
    $site_path = preg_replace( '/(\/+)/', '/', $_SERVER['REQUEST_URI'] );
    redirect( rtrim( $settings['siteurl'], '/' ) . $site_path );
}

define( "FUSION_QUERY", isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : "" );
define( "FUSION_SELF", basename( $_SERVER['PHP_SELF'] ) );
define( "FUSION_REQUEST", isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] );

// Calculate ROOT path for Permalinks
$current_path = html_entity_decode( $_SERVER['REQUEST_URI'] );
if (isset( $settings['site_path'] ) && strcmp( $settings['site_path'], "/" ) != 0) {
    $current_path = str_replace( $settings['site_path'], '', $current_path );
} else {
    $current_path = ltrim( $current_path, "/" );
}

// for Permalinks include files.
define( "PERMALINK_CURRENT_PATH", $current_path );
define( 'FORM_REQUEST', fusion_get_settings( 'site_seo' ) && defined( 'IN_PERMALINK' ) ? PERMALINK_CURRENT_PATH : FUSION_REQUEST );
//BREADCRUMB URL, INCLUDES PATH TO FILE AND FILENAME
//E.G. infusions/downloads/downloads.php OR VIEWPAGE.PHP
if (explode( "?", PERMALINK_CURRENT_PATH )) {
    $filelink = explode( "?", PERMALINK_CURRENT_PATH );
    define( "FUSION_FILELINK", $filelink[0] );
} else {
    define( "FUSION_FILELINK", PERMALINK_CURRENT_PATH );
}

$count = substr_count( PERMALINK_CURRENT_PATH, "/" );
$root = str_repeat( "../", $count );
define( "ROOT", $root );

$root_count = $count - substr_count( BASEDIR, "/" );
$fusion_root = str_repeat( "../", $root_count );
define( "FUSION_ROOT", $fusion_root );

// Calculate current true url
$script_url = explode( "/", $_SERVER['PHP_SELF'] );
$url_count = count( $script_url );
$base_url_count = substr_count( BASEDIR, "/" ) + 1;
$current_page = "";
while ($base_url_count != 0) {
    $current = $url_count - $base_url_count;
    $current_page .= "/" . $script_url[$current];
    $base_url_count--;
}

// Set TRUE_PHP_SELF and START_PAGE
define( "TRUE_PHP_SELF", $current_page );
define( "START_PAGE", substr( preg_replace(
    "#(&amp;|\?)(s_action=edit&amp;shout_id=)([0-9]+)#s", "",
    TRUE_PHP_SELF . (FUSION_QUERY ? "?" . FUSION_QUERY : "")
), 1 ) );

$userdata = fusion_set_user();

// User level, Admin Rights & User Group definitions
define( 'iGUEST', $userdata['user_level'] == USER_LEVEL_PUBLIC ? 1 : 0 );
define( 'iMEMBER', $userdata['user_level'] <= USER_LEVEL_MEMBER ? 1 : 0 );
define( 'iADMIN', $userdata['user_level'] <= USER_LEVEL_ADMIN ? 1 : 0 );
define( 'iSUPERADMIN', $userdata['user_level'] == USER_LEVEL_SUPER_ADMIN ? 1 : 0 );
define( 'iUSER', $userdata['user_level'] );
define( 'iUSER_RIGHTS', $userdata['user_rights'] );
define( 'iUSER_GROUPS', substr( $userdata['user_groups'], 1 ) );
define( 'iDEVELOPER', defined( 'DEVELOPER_MODE' ) && DEVELOPER_MODE && iADMIN );

// Main language detection procedure
static $current_user_language = [];
if (iMEMBER && valid_language( $userdata['user_language'] )) {
    $current_user_language = $userdata['user_language'];
} else {
    $langData = dbarray( dbquery( 'SELECT * FROM ' . DB_LANGUAGE_SESSIONS . ' WHERE user_ip=:ip', [':ip' => USER_IP] ) );
    $current_user_language = (!empty( $langData['user_language'] ) ? $langData['user_language'] : fusion_get_settings( 'locale' ));
}
$language_opts = fusion_get_enabled_languages( TRUE );
$enabled_languages = array_keys( $language_opts );

// If language change is initiated and if the selected language is valid
if (check_get( 'lang' ) && file_exists( LOCALE . get( 'lang' ) . "/global.php" ) && in_array( get( 'lang' ), $enabled_languages )) {
    $current_user_language = stripinput( get( 'lang' ) );
    set_language( $current_user_language );
} else {
    if (count( $enabled_languages ) > 1) {
        require __DIR__ . '/includes/core_mlang_hub_include.php';
    }
}

if (!defined( 'LANGUAGE' )) {
    define( 'LANGUAGE', $current_user_language );
}
if (!defined( 'LOCALESET' )) {
    define( 'LOCALESET', $current_user_language . '/' );
}

$locale = [];
\PHPFusion\Locale::setLocale( LOCALE . LOCALESET . 'global.php' );
$setlocale = empty( fusion_get_locale( 'setlocale' ) ) ? 'en_GB' : fusion_get_locale( 'setlocale' );
$win = explode( '_', $setlocale );
setlocale( LC_ALL, $setlocale . '.UTF-8', $win[0] );
//setlocale(LC_ALL, $setlocale.'.UTF-8');

// IP address functions
include INCLUDES . "ip_handling_include.php";

// Error Handling
require_once INCLUDES . "error_handling_include.php";

if (!defined( 'FUSION_ALLOW_REMOTE' )) {
    new Token();
}

Defender\ImageValidation::validateExtensions();

// Define aidlink
if (iADMIN) {
    //@todo: to remove this part for non-global approach
    define( "iAUTH", substr( md5( $userdata['user_password'] . USER_IP ), 16, 16 ) );
    $aidlink = fusion_get_aidlink();
    // Generate a session aid every turn
    $token_time = time();
    $algo = fusion_get_settings( 'password_algorithm' );
    $key = $userdata['user_id'] . $token_time . iAUTH . SECRET_KEY;
    $salt = md5( $userdata['user_admin_salt'] . SECRET_KEY_SALT );
    $_SESSION['aid'] = $userdata['user_id'] . "." . $token_time . "." . hash_hmac( $algo, $key, $salt );
}

// PHPFusion user cookie functions
Authenticate::setVisitorCounter();

// Set admin login procedures
Authenticate::setAdminLogin();

Dynamics::getInstance();

// Set theme
$_session_theme = session_get( COOKIE_PREFIX . 'theme' );
$theme_session = $_session_theme && theme_exists( $_session_theme ) ? $_session_theme : FALSE;

if ($_session_theme == fusion_get_settings( 'theme' )) {
    session_remove( COOKIE_PREFIX . 'theme' );
}

$theme = $theme_session !== FALSE ? $theme_session : (empty( $userdata['user_theme'] ) ? fusion_get_settings( 'theme' ) : $userdata['user_theme']);
set_theme( $theme );

// This can be converted into sessions
$result = dbquery( "SELECT inf_folder, inf_version FROM " . DB_INFUSIONS );
if (dbrows( $result )) {
    while ($data = dbarray( $result )) {
        if (file_exists( INFUSIONS . $data['inf_folder'] )) {
            define( strtoupper( $data['inf_folder'] ) . '_EXISTS', TRUE );
            define( strtoupper( $data['inf_folder'] ) . '_EXIST', TRUE ); // just in case
            define( strtoupper( $data['inf_folder'] ) . '_VERSION', $data['inf_version'] );
        }
    }
}

PHPFusion\Installer\Infusions::loadConfiguration();
