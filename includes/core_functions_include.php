<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: core_functions_include.php
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
defined('IN_FUSION') || exit;

use Defender\Token;
use PHPFusion\Authenticate;
use PHPFusion\ImageRepo;
use PHPFusion\Minify\JS;
use PHPFusion\PrivateMessages;
use PHPFusion\QuantumFields;

/**
 * Get currency symbol by using a 3-letter ISO 4217 currency code
 * Note that if INTL pecl package is not installed, signs will degrade to ISO4217 code itself
 *
 * @param string $iso         3-letter ISO 4217
 * @param bool   $description Set to false for just symbol
 *
 * @return array|string Array of currencies or string with one currency.
 */
function fusion_get_currency($iso = NULL, $description = TRUE) {

    $locale = fusion_get_locale('', LOCALE.LOCALESET."currency.php");

    static $__currency = [];

    if (empty($__currency)) {
        // Euro Exceptions list
        $currency_exceptions = [
            "ADF" => "EUR",
            "ATS" => "EUR",
            "BEF" => "EUR",
            "CYP" => "EUR",
            "DEM" => "EUR",
            "EEK" => "EUR",
            "ESP" => "EUR",
            "FIM" => "EUR",
            "FRF" => "EUR",
            "GRD" => "EUR",
            "IEP" => "EUR",
            "ITL" => "EUR",
            "KZT" => "EUR",
            "LTL" => "EUR",
            "LUF" => "EUR",
            "LVL" => "EUR",
            "MCF" => "EUR",
            "MTL" => "EUR",
            "NLG" => "EUR",
            "PTE" => "EUR",
            "RUB" => "EUR",
            "SIT" => "EUR",
            "SKK" => "EUR",
            "SML" => "EUR",
            "VAL" => "EUR",
            "DDM" => "EUR",
            "ESA" => "EUR",
            "ESB" => "EUR",
        ];
        foreach (array_keys($locale['currency']) as $country_iso) {
            $c_iso = !empty($currency_exceptions[$country_iso]) ? $currency_exceptions[$country_iso] : $country_iso;
            $c_symbol = (!empty($locale['currency_symbol'][$c_iso]) ? html_entity_decode($locale['currency_symbol'][$c_iso], ENT_QUOTES, $locale['charset']) : $c_iso);
            $c_text = $locale['currency'][$c_iso];
            $__currency[$country_iso] = $description ? $c_text." ($c_symbol)" : $c_symbol;
        }
    }

    return $iso === NULL ? $__currency : (isset($__currency[$iso]) ? $__currency[$iso] : NULL);
}

/**
 * Check if a given theme exists and is valid.
 *
 * @param string $theme The theme folder you want to check.
 *
 * @return bool False if the theme does not exist and true if it does.
 */
function theme_exists($theme) {

    if ($theme == "Default") {
        $theme = fusion_get_settings('theme');
    }

    return is_string($theme) and
        preg_match("/^([a-z0-9_-]){2,50}$/i", $theme) and
        file_exists(THEMES.$theme."/theme.php") and
        file_exists(THEMES.$theme."/styles.css");
}

/**
 * Set a valid theme.
 *
 * @param string $theme The theme folder you want to set.
 */
function set_theme($theme) {

    $locale = fusion_get_locale();
    if (defined("THEME")) {
        return;
    }
    if (theme_exists($theme)) {
        define("THEME", THEMES.($theme == "Default" ? fusion_get_settings('theme') : $theme)."/");

        return;
    }
    foreach (new GlobIterator(THEMES.'*') as $dir) {
        if ($dir->isDir() and theme_exists($dir->getBasename())) {
            define("THEME", $dir->getPathname()."/");

            return;
        }
    }
    // Don't stop if we are in admin panel since we use different themes now
    $no_theme_message = str_replace("[SITE_EMAIL]", fusion_get_settings("siteemail"), $locale['global_301']);

    if (preg_match("/\/administration\//i", $_SERVER['PHP_SELF'])) {

        addnotice('danger', "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n".$no_theme_message);

    } else {

        echo "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n";

        echo $no_theme_message;

        die();

    }
}

/**
 * Set password of the currently logged in an administrator.
 *
 * @param string $password Any password.
 *
 * @return bool True if a password is set.
 */
function set_admin_pass($password) {

    return Authenticate::setAdminCookie($password);
}

/**
 * Check if admin password matches userdata.
 *
 * @param string $password Password.
 *
 * @return bool True if the password matches the user's admin password or if the admin's cookie or session is set and is valid.
 */
function check_admin_pass($password) {

    return Authenticate::validateAuthAdmin($password);
}

/**
 * Redirect to internal or external URL.
 *
 * @param string $location Desintation URL.
 * @param bool   $delay    meta refresh delay.
 * @param bool   $script   True if you want to redirect via javascript.
 * @param int    $code     HTTP status code to send.
 */
function redirect($location, $delay = FALSE, $script = FALSE, $code = 200) {

    if (!defined('STOP_REDIRECT')) {
        if (isnum($delay)) {
            $ref = "<meta http-equiv='refresh' content='$delay; url=".$location."' />";
            add_to_head($ref);
        } else {
            if ($script == FALSE && !headers_sent()) {
                set_status_header($code);
                header("Location: ".str_replace("&amp;", "&", $location));
            } else {
                echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
            }
            exit;
        }
    }
}

/**
 * Set HTTP status header.
 *
 * @param int $code Status header code.
 *
 * @return bool Whether header was sent.
 */
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

    $desc = !empty($desc[$code]) ? $desc[$code] : '';

    header("$protocol $code $desc");

    return TRUE;
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
 * Clean the URL and prevents entities in server globals.
 *
 * @param string $url URL.
 *
 * @return string $url clean and ready for use XHTML strict and without any dangerous code.
 */
function cleanurl($url) {

    $bad_entities = ["&", "\"", "'", '\"', "\'", "<", ">", "", "", "*"];
    $safe_entities = ["&amp;", "", "", "", "", "", "", "", "", ""];

    return str_replace($bad_entities, $safe_entities, $url);
}

/**
 * Prevents HTML in unwanted places
 *
 * @param string|array $text String or array to be stripped.
 *
 * @return array|string The given string decoded as non HTML text.
 */
function stripinput($text) {

    if (!is_array($text) && !is_null($text)) {
        return str_replace('\\', '&#092;', htmlspecialchars(stripslashes(trim($text)), ENT_QUOTES));
    }

    if (is_array($text) && !is_null($text)) {
        foreach ($text as $i => $item) {
            $text[$i] = stripinput($item);
        }
    }

    return $text;
}

/**
 * Prevent any possible XSS attacks via $_GET.
 *
 * @param array|string $check_url String or array to be stripped.
 *
 * @return bool True if the URL is not secure.
 */
function stripget($check_url) {

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

    return FALSE;
}

/**
 * Strips a given filename from any unwanted characters and symbols.
 *
 * @param string $filename Filename you want to strip. Remember to remove the file extension before parsing it through this function.
 *
 * @return string The filename stripped and ready for use.
 */
function stripfilename($filename) {

    $patterns = [
        '/\s+/'              => '_',
        '/[^a-z0-9_-]|^\W/i' => '',
        '/([_-])\1+/'        => '$1'
    ];

    return preg_replace(array_keys($patterns), $patterns, strtolower($filename)) ?: (string)time();
}

/**
 * Converts all applicable characters to HTML entities.
 * htmlentities is too agressive so we use this function.
 *
 * @param string $text The input string.
 *
 * @return string Encoded string.
 */
function phpentities($text) {

    return str_replace('\\', '&#092;', htmlspecialchars($text, ENT_QUOTES));
}

/**
 * Prevent strings from growing to long and breaking the layout.
 *
 * @param string $text   String to trim.
 * @param int    $length Max length of the string.
 *
 * @return string String trimmed to the given length.
 */
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
 * Trim a text to a number of words.
 *
 * @param string $text   String to trim.
 * @param int    $limit  The number of words.
 * @param string $suffix If $text is longer than $limit, $suffix will be appended.
 *
 * @return string String trimmed to the given length.
 */
function fusion_first_words($text, $limit, $suffix = '&hellip;') {

    $text = preg_replace('/[\r\n]+/', '', $text);

    return preg_replace('~^(\s*\w+'.str_repeat('\W+\w+', $limit - 1).'(?(?=[?!:;.])[[:punct:]]\s*))\b(.+)$~isxu', '$1'.$suffix, strip_tags($text));
}

/**
 * Pure trim function.
 *
 * @param string $str    String to trim.
 * @param int    $length The number of characters.
 *
 * @return string Trimmed text.
 */
function trim_text($str, $length = 300) {

    for ($i = $length; $i <= strlen($str); $i++) {
        $spacetest = substr("$str", $i, 1);
        if ($spacetest == " ") {
            $spaceok = substr("$str", 0, $i);

            return ($spaceok."...");
        }
    }

    return ($str);
}

/**
 * Replaces special characters in a string with their "non-special" counterpart.
 *
 * @param string $value String to normalize.
 *
 * @return string
 */
function normalize($value) {

    $table = [
        '&amp;' => 'and', '@' => 'at', '©' => 'c', '®' => 'r', 'À' => 'a', '(' => '', ')' => '', '.' => '',
        'Á'     => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae', 'Ç' => 'c',
        'È'     => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï'     => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø'     => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß'     => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ'     => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì'     => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ô'     => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
        'û'     => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a',
        'ā'     => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c',
        'ć'     => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c',
        'č'     => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e',
        'ē'     => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e',
        'ę'     => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g',
        'ğ'     => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h',
        'ĥ'     => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i',
        'ī'     => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i',
        'ı'     => 'i', 'Ĳ' => 'ij', 'ĳ' => 'ij', 'Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k',
        'ķ'     => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l',
        'Ľ'     => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
        'Ń'     => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n',
        'ŉ'     => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o',
        'ŏ'     => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe', 'œ' => 'oe', 'Ŕ' => 'r',
        'ŕ'     => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's',
        'ś'     => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's',
        'š'     => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't',
        'ŧ'     => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u',
        'ŭ'     => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u',
        'ų'     => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y',
        'Ź'     => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z',
        'ſ'     => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u',
        'ư'     => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o',
        'ǒ'     => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u',
        'ǘ'     => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a',
        'ǻ'     => 'a', 'Ǽ' => 'ae', 'ǽ' => 'ae', 'Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e',
        'Ё'     => 'jo', 'Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b',
        'В'     => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh', 'З' => 'z',
        'И'     => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
        'О'     => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u',
        'Ф'     => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sch',
        'Ъ'     => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je', 'Ю' => 'ju', 'Я' => 'ja',
        'а'     => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ж'     => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l',
        'м'     => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т'     => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш'     => 'sh', 'щ' => 'sch', 'ъ' => '-', 'ы' => 'y', 'ь' => '-', 'э' => 'je',
        'ю'     => 'ju', 'я' => 'ja', 'ё' => 'jo', 'є' => 'e', 'і' => 'i', 'ї' => 'i',
        'Ґ'     => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd',
        'ה'     => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i',
        'ך'     => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n',
        'נ'     => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C',
        'צ'     => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
        'ء'     => 'a', 'ا' => 'a', 'آ' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't',
        'ث'     => 's', 'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd',
        'ر'     => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ص' => 's', 'ض' => 'z',
        'ط'     => 't', 'ظ' => 'z', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ک' => 'k',
        'گ'     => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'و' => 'w', 'ه' => 'h', 'ی' => 'y ',
    ];

    return strtr($value, $table);
}

/**
 * Generate random string.
 *
 * @param int  $length       The length of the string.
 * @param bool $letters_only Only letters.
 *
 * @return string
 */
function random_string($length = 6, $letters_only = FALSE) {

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($letters_only) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }

    return $random_string;
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

/**
 * Custom preg_match function.
 *
 * @param string $expression The expression to search for.
 * @param mixed  $value      The input string.
 *
 * @return bool FALSE when $value is an array
 */
function preg_check($expression, $value) {

    return !is_array($value) and preg_match($expression, $value);
}

/**
 * Generate a clean Request URI.
 *
 * @param mixed $request_addition 'page=1&ref=2' or array('page' => 1, 'ref' => 2)
 * @param array $filter_array     array('aid','page', ref')
 * @param bool  $keep_filtered    True to keep filter, false to remove filter from FUSION_REQUEST.
 *                                If remove is true, to remove everything and keep $requests_array and $request
 *                                addition. If remove is false, to keep everything else except $requests_array
 *
 * @return string
 */
function clean_request($request_addition = '', $filter_array = [], $keep_filtered = TRUE) {

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

    return $url['path'].$prefix.http_build_query($fusion_query, 'flags_', '&amp;');
}

/**
 * Cache of all smileys from the database.
 *
 * @return array Array of all smileys.
 */
function cache_smileys() {

    return ImageRepo::cacheSmileys();
}

/**
 * Parse the smileys in string and display smiley codes as smiley images.
 *
 * @param string $message A string that should have parsed smileys.
 *
 * @return string String with parsed smiley codes as smiley images ready for display.
 */
function parsesmileys($message) {

    if (!preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php])#si", $message)) {
        foreach (cache_smileys() as $smiley) {
            $smiley_code = preg_quote($smiley['smiley_code'], '#');
            $smiley_image = get_image("smiley_".$smiley['smiley_text']);
            $smiley_image = "<img class='smiley' style='width:20px;height:20px;' src='$smiley_image' alt='".$smiley['smiley_text']."'>";
            $message = preg_replace("#$smiley_code#s", $smiley_image, $message);
        }
    }

    return $message;
}

/**
 * Show smiley's button which will insert the smileys to the given textarea and form.
 *
 * @param string $textarea The id of the textarea
 * @param string $form     The form id in which the textarea is located.
 *
 * @return string  Option for users to insert smileys in a post by displaying the smiley's button.
 */
function displaysmileys($textarea, $form = "inputform") {

    $smileys = "";
    $i = 0;
    foreach (cache_smileys() as $smiley) {
        if ($i != 0 && ($i % 10 == 0)) {
            $smileys .= "<br />\n";
        }
        $i++;
        $img = get_image("smiley_".$smiley['smiley_text']);
        $smileys .= "<img class='smiley m-2' style='width:20px;height:20px;' src='".$img."' alt='".$smiley['smiley_text']."' title='".$smiley['smiley_text']."' onclick=\"insertText('".$textarea."', '".$smiley['smiley_code']."', '".$form."');\">\n";
    }

    return $smileys;
}

/**
 * Tag a user by simply just posting his name like @Nick and if found, returns a tooltip.
 *
 * @param string $user_name @Nick
 * @param string $tooltip   Additional info e.g. ($userdata['user_lastvisit'] - 120 < time() ? 'Online' : 'Offline').
 *
 * @return string Tooltip with info.
 */
function fusion_parse_user($user_name, $tooltip = '') {
    return preg_replace_callback("/@([A-Za-z0-9\-_!.]+)/", function ($user_name) use ($tooltip) {
        $user = $user_name[1];
        $result = dbquery("SELECT *
            FROM ".DB_USERS."
            WHERE (user_name=:user_00 OR user_name=:user_01 OR user_name=:user_02 OR user_name=:user_03) AND user_status='0'
            LIMIT 1
        ", [
            ':user_00' => $user,
            ':user_01' => ucwords($user),
            ':user_02' => strtoupper($user),
            ':user_03' => strtolower($user)
        ]);
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            return render_user_tags($data, $tooltip);
        }

        return $user_name[0];
    }, $user_name);
}

/**
 * Cache of all installed BBCodes from the database.
 *
 * @return array Array of all BBCodes.
 */
function cache_bbcode() {

    static $bbcode_cache = [];
    if (empty($bbcode_cache)) {
        $bbcode_cache = [];
        $result = cdquery('bbcodes_cache', "SELECT bbcode_name FROM ".DB_BBCODES." ORDER BY bbcode_order");
        while ($data = cdarray($result)) {
            $bbcode_cache[] = $data['bbcode_name'];
        }
    }

    return $bbcode_cache;
}

/**
 * Parse and force image/ to own directory.
 * Neutralize all image dir levels and convert image to pf image folder
 *
 * @param string $data    String to parse.
 * @param string $prefix_ Image folder.
 *
 * @return string Parsed string.
 */
function parse_image_dir($data, $prefix_ = "") {

    $str = str_replace("../", "", $data);

    return (string)$prefix_ ? str_replace("images/", $prefix_, $str) : str_replace("images/", IMAGES, $str);
}

/**
 * Parse BBCodes, smileys and any special characters to HTML string.
 *
 * @param string $value   String with unparsed text.
 * @param array  $options Array of options.
 *
 * @return string
 */
function parse_text($value, $options = []) {
    $default_options = [
        'parse_smileys'        => TRUE, // Smiley parsing.
        'parse_bbcode'         => TRUE, // BBCode parsing.
        'decode'               => TRUE, // Decode HTML entities.
        'default_image_folder' => IMAGES, // Image folder for parse_image_dir().
        'add_line_breaks'      => FALSE, // Allows nl2br().
        'descript'             => TRUE, // Sanitize text.
        'parse_users'          => TRUE // Create user @tags.
    ];

    $options += $default_options;

    $charset = fusion_get_locale('charset');
    $value = stripslashes($value);
    if ($options['descript']) {
        $value = descript($value);
        $value = htmlspecialchars_decode($value);
    }
    if ($options['default_image_folder']) {
        $value = parse_image_dir($value, $options['default_image_folder']);
    }
    if ($options['parse_bbcode']) {
        $value = parseubb($value);
    }
    if ($options['parse_smileys']) {
        $value = parsesmileys($value);
    }
    if ($options['add_line_breaks']) {
        $value = nl2br($value);
    }
    if ($options['parse_users']) {
        $value = fusion_parse_user($value);
    }
    if ($options['decode']) {
        $value = html_entity_decode(html_entity_decode($value, ENT_QUOTES, $charset));
        $value = encode_code($value);
    }

    return (string)$value;
}

/**
 * Parse BBCodes in the given string.
 *
 * @param string $text     A string that contains the text to be parsed.
 * @param string $selected The names of the required bbcodes to parse, separated by |.
 * @param bool   $descript Sanitize text.
 *
 * @return string Parsed string.
 */
function parseubb($text, $selected = "", $descript = TRUE) {

    if ($descript) {
        $text = descript($text, FALSE);
    }

    $bbcode_cache = cache_bbcode();
    $bbcodes = [];
    foreach ($bbcode_cache as $bbcode) {
        $bbcodes[$bbcode] = $bbcode;
    }

    if (!empty($bbcodes['code'])) {
        $move_to_top = $bbcodes['code'];
        unset($bbcodes['code']);
        array_unshift($bbcodes, $move_to_top);
    }

    $sel_bbcodes = [];

    if ($selected) {
        $sel_bbcodes = explode("|", $selected);
    }
    foreach ($bbcodes as $bbcode) {
        $locale_file = '';
        if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE.LOCALESET."bbcodes/".$bbcode.".php";
        } else if (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE."English/bbcodes/".$bbcode.".php";
        }
        if ($locale_file) {
            \PHPFusion\Locale::setLocale($locale_file);
        }
    }

    $locale = fusion_get_locale();

    foreach ($bbcodes as $bbcode) {
        if ($selected && in_array($bbcode, $sel_bbcodes)) {
            if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
                include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
            }
        } else if (!$selected) {
            if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
                include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
            }
        }
    }

    // Added to fix code sniffer reported error
    unset($locale);

    return $text;
}

/**
 * Hide email from robots that have JavaScript disabled, as it requires JavaScript to view email.
 * Create a "mailto" link for the email address
 *
 * @param string $email   The email you want to hide from robots.
 * @param string $title   The text of the link.
 * @param string $subject A subject for a mail message if someone opens a link, and it opens in the mail client.
 *
 * @return string If browser has JavaScript enabled, email will be displayed correctly,
 *                otherwise, it will be hidden and difficult for a robot to decrypt.
 */
function hide_email($email, $title = "", $subject = "") {

    if (preg_match("/^[-0-9A-Z_.]{1,50}@([-0-9A-Z_.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
        $enc_email = '';
        $parts = explode("@", $email);
        $email = $parts[0].'@'.$parts[1];
        for ($i = 0; $i < strlen($email); $i++) {
            $enc_email .= '&#'.ord($email[$i]).';';
        }

        $MailLink = "<a href='mailto:".$enc_email;
        if ($subject != "") {
            $MailLink .= "?subject=".urlencode($subject);
        }
        $MailLink .= "'>".(!empty($title) ? $title : $enc_email)."</a>";

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

        $id = 'e'.rand(1, 99999999);

        $MailIndexes = str_replace("\\", "\\\\", $MailIndexes);
        $MailIndexes = str_replace("\"", "\\\"", $MailIndexes);
        $res = "<span id='".$id."'></span>";
        $res .= "<script type='text/javascript'>";
        $res .= "ML=\"".str_replace("<", "xxxx", $MailLettersEnc)."\";";
        $res .= "MI=\"".str_replace("<", "xxxx", $MailIndexes)."\";";
        $res .= "ML=ML.replace(/xxxx/g, '<');";
        $res .= "MI=MI.replace(/xxxx/g, '<');";
        $res .= "OT=\"\";";
        $res .= "for(j=0;j < MI.length;j++){";
        $res .= "OT+=ML.charAt(MI.charCodeAt(j)-48);";
        $res .= "}var e=document.getElementById('".$id."');e.innerHTML += OT;";
        $res .= "</script>";

        return $res;
    } else {
        return $email;
    }
}

/**
 * Encode and format code inside <code> tag.
 *
 * @param string $text String with code.
 *
 * @return string Encoded and formatted code.
 */
function encode_code($text) {

    preg_match_all("#<code>(.*?)</code>#is", $text, $codes);
    $replace = [];
    foreach ($codes[1] as $key => $codeblock) {
        $replace[$key] = htmlentities($codeblock, ENT_QUOTES, 'UTF-8', FALSE);
    }
    unset($key, $codeblock);

    if (!empty($codes[0])) {
        if (!defined('PRISMJS')) {
            define('PRISMJS', TRUE);
            add_to_head('<link rel="stylesheet" href="'.INCLUDES.'bbcodes/code/prism.css">');
            add_to_footer('<script src="'.INCLUDES.'bbcodes/code/prism.js"></script>');
        }
    }

    foreach ($codes[0] as $key => $replacer) {
        $code = str_replace('&lt;br /&gt;', '', $replace[$key]);
        $code = format_code($code);
        $text = str_replace($replacer, '<pre><code class="language-php">'.$code.'</code></pre>', $text);
    }
    unset($key, $replacer, $replace);

    return $text;
}

/**
 * Add correct amount of spaces and tabs inside code.
 *
 * @param string $code The code you want to format.
 *
 * @return string Formatted code.
 */
function format_code($code) {

    $code = htmlentities($code, ENT_QUOTES, 'UTF-8', FALSE);

    $code = str_replace(
        ["  ", "  ", "\t", "[", "]"],
        ["&nbsp; ", " &nbsp;", "&nbsp; &nbsp;", "&#91;", "&#93;"],
        $code
    );

    return preg_replace("/^ {1}/m", "&nbsp;", $code);
}

/**
 * Formats a number in a numeric acronym, and rounding.
 *
 * @param int    $value        Number to format.
 * @param int    $decimals     The number of decimals.
 * @param string $dec_point    Decimal point.
 * @param string $thousand_sep Thousands separator.
 * @param bool   $round        Round number.
 * @param bool   $acryonym     Acronym.
 *
 * @return string
 */
function format_num($value, $decimals = 0, $dec_point = ".", $thousand_sep = ",", $round = TRUE, $acryonym = TRUE) {

    $array = [
        13 => $acryonym ? "t" : "trillion",
        10 => $acryonym ? "b" : "billion",
        7  => $acryonym ? "m" : "million",
        4  => $acryonym ? "k" : "thousand"
    ];

    if (is_numeric($value)) {
        if ($round === TRUE) {
            foreach ($array as $length => $rounding) {
                if (strlen($value) >= $length) {
                    $power = pow(10, $length - 1);
                    if ($value > $power && $length > 4 && $decimals === NULL) {
                        $decimals = 2;
                    }

                    return number_format(($value / $power), $decimals, $dec_point, $thousand_sep).$rounding;
                }
            }
        }

        return number_format($value, $decimals, $dec_point, $thousand_sep);
    }

    return $value;
}

/**
 * Converts any formatted number back to float numbers in PHP
 *
 * @param string|int $value Formatted number.
 *
 * @return float
 */
function format_float($value) {

    return floatval(preg_replace('/[^\d.]/', '', $value));
}

/**
 * Highlights given words in string.
 *
 * @param array  $words   The words to highlight.
 * @param string $subject Text that contains a word (s) that should be highlighted.
 *
 * @return string Words highlighted in the string.
 */
function highlight_words($words, $subject) {

    for ($i = 0, $l = count($words); $i < $l; $i++) {
        $word[$i] = str_replace([
            "\\",
            "+",
            "*",
            "?",
            "[",
            "^",
            "]",
            "$",
            "(",
            ")",
            "{",
            "}",
            "=",
            "!",
            "<",
            ">",
            "|",
            ":",
            "#",
            "-",
            "_"
        ], "", $words[$i]);
        if (!empty($words[$i])) {
            $subject = preg_replace("#($words[$i])(?![^<]*>)#i",
                "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px;'>\${1}</span>",
                $subject);
        }
    }

    return $subject;
}

/**
 * Sanitize text and remove a potentially dangerous HTML and JavaScript.
 *
 * @param string $text          String to be sanitized.
 * @param bool   $strip_tags    Removes potentially dangerous HTML tags.
 * @param bool   $strip_scripts Removes <script> tags.
 *
 * @return string|array Sanitized and safe string.
 */
function descript($text, $strip_tags = TRUE, $strip_scripts = TRUE) {

    if (is_array($text) || is_null($text)) {
        return $text;
    }

    $text = html_entity_decode($text, ENT_QUOTES, fusion_get_locale('charset'));
    $text = preg_replace('/&([a-z0-9]+|#[0-9]{1,6}|#x[0-9a-f]{1,6});/i', '', $text);

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

    if ($strip_tags) {
        do {
            $count = 0;
            $text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|object|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text, -1, $count);
        } while ($count);
    }

    $text = preg_replace(array_keys($patterns), $patterns, $text);

    $preg_patterns = [
        // Fix &entity\n
        '!(&#0+[0-9]+)!'                                                                                                                                                                                => '$1;',
        '/(&#*\w+)[\x00-\x20]+;/u'                                                                                                                                                                      => '$1;>',
        '/(&#x*[0-9A-F]+);*/iu'                                                                                                                                                                         => '$1;',
        // Remove any attribute starting with "on" or xml name space
        '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu'                                                                                                                                                => '$1>',
        // Remove any xss injected without a closing tag
        '#(<[^>]+?\s*[\x00-\x20"\'\\\\\/])((?:on|xmlns)+[=\w\d()]*+)#iu'                                                                                                                                => '$1>',
        // javascript: and VB script: protocols
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

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', FALSE);
}

/**
 * Scan image files for malicious code.
 *
 * @param string $file Path to image.
 *
 * @return bool True or false, depending on whether the image is safe or not.
 */
function verify_image($file) {

    $txt = file_get_contents($file);
    $patterns = [
        '#\<\?php#i',
        '#&(quot|lt|gt|nbsp);#i',
        '#&\#x([0-9a-f]+);#i',
        '#&\#([0-9]+);#i',
        "#([a-z]*)=([\`\'\"]*)script:#iU",
        "#([a-z]*)=([\`\'\"]*)javascript:#iU",
        "#([a-z]*)=([\'\"]*)vbscript:#iU",
        "#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU",
        "#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU",
        "#</*(applet|link|style|script|iframe|frame|frameset)[^>]*>#i"
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $txt)) {
            return FALSE;
        }
    }

    return TRUE;
}

/**
 * Replace offensive words with the defined replacement word.
 * The list of offensive words and the replacement word are both defined in the Security Settings.
 *
 * @param string $text Text that should be censored.
 *
 * @return string Censored text.
 */
function censorwords($text) {
    $settings = fusion_get_settings();

    if ($settings['bad_words_enabled'] && !empty($settings['bad_words'])) {
        //$words = preg_quote(trim($settings['bad_words']), "/");
        //$words = preg_replace("/\\s+/", "|", $words);
        $words = str_replace("\r", "", $settings["bad_words"]);
        $words = str_replace("\n", "|", $words);
        $text = preg_replace("/".$words."/si", $settings['bad_word_replace'], $text);
    }

    return $text;
}

/**
 * Get a user level's name by the numeric code of level.
 *
 * @param int $userlevel Level code.
 *
 * @return string The name of the given user level, null if it does not exist.
 */
function getuserlevel($userlevel) {

    $locale = fusion_get_locale();
    $userlevels = [
        USER_LEVEL_MEMBER      => $locale['user1'],
        USER_LEVEL_ADMIN       => $locale['user2'],
        USER_LEVEL_SUPER_ADMIN => $locale['user3']
    ];

    return $userlevels[$userlevel] ?? NULL;
}

/**
 * Get a user status by the numeric code of status.
 *
 * @param int $userstatus Status code 0 - 8.
 * @param int $join_timestamp User lastvisit.
 *
 * @return string|null The name of the given user status, null if it does not exist.
 */
function getuserstatus($userstatus, $join_timestamp = 0) {

    $locale = fusion_get_locale();

    if ($join_timestamp) {
        return ($userstatus >= 0 and $userstatus <= 8) ? $locale['status'.$userstatus] : NULL;
    }

    return $locale['status_pending'];
}

/**
 * Check if an Administrator has the correct rights assigned.
 *
 * @param string $rights Rights you want to check for the administrator.
 *
 * @return bool True if the user is an Administrator with rights defined in $rights.
 */
function checkrights($rights) {
    if (iSUPERADMIN) {
        return TRUE;
    } else if (iADMIN && in_array($rights, explode(".", iUSER_RIGHTS))) {
        return TRUE;
    }
    return FALSE;

}

/**
 * Check the user has rights and redirect if the user does not have rights for the page.
 *
 * @param string $rights Rights you want to check for the administrator.
 * @param bool   $debug  For debugging purposes.
 */
function pageaccess($rights, $debug = FALSE) {

    $error = [];
    if ($debug) {
        print_p('Admin Panel mode');
    }
    if (!defined('iAUTH')) {
        $error[] = 'iAuth error';
    }
    if (!isset($_GET['aid'])) {
        $error[] = 'Aid link error';
    }
    if (iADMIN && !empty($_GET['aid'])) {
        if ($_GET['aid'] != iAUTH) {
            $error[] = 'Aidlink mismatch. '.iAUTH.' != '.$_GET['aid']."<br/>";
            $error[] .= USER_IP;
        }
    } else {
        $error[] = "You are logged out while accessing admin panel";
    }
    if (!checkrights($rights)) {
        $error[] = 'Checkrights Error';
    }
    if (!empty($error)) {
        if ($debug) {
            print_p($error);
        } else {
            redirect(BASEDIR);
        }
    }
}

/**
 * Check if user is assigned to the specified user group(s).
 *
 * @param int    $group The group number you want to check for the user.
 * @param string $delim Delimiter.
 *
 * @return bool True if the user is in the group.
 */
function checkgroup($group, $delim = ',') {

    if (strpos($group, $delim) !== FALSE) {
        foreach (explode($delim, $group) as $group_) {
            if (iSUPERADMIN) {
                return TRUE;
            } else if (iADMIN && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER || $group_ == USER_LEVEL_ADMIN)) {
                return TRUE;
            } else if (iMEMBER && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER)) {
                return TRUE;
            } else if (iGUEST && $group_ == 0) {
                return TRUE;
            } else if (iMEMBER && $group_ && in_array($group_, explode(".", iUSER_GROUPS))) {
                return TRUE;
            }
        }
    } else {
        if (iSUPERADMIN) {
            return TRUE;
        } else if (iADMIN && ($group == 0 || $group == USER_LEVEL_MEMBER || $group == USER_LEVEL_ADMIN)) {
            return TRUE;
        } else if (iMEMBER && ($group == 0 || $group == USER_LEVEL_MEMBER)) {
            return TRUE;
        } else if (iGUEST && $group == 0) {
            return TRUE;
        } else if (iMEMBER && $group && in_array($group, explode('.', iUSER_GROUPS))) {
            return TRUE;
        }
    }

    return NULL;
}

/**
 * Check if user is assigned to the specified user group(s) and has the required user level.
 *
 * @param int    $group       The group number(s) you want to check for the user.
 * @param int    $user_level  User level.
 * @param string $user_groups Assigned groups to the user.
 * @param string $delim       Delimiter.
 *
 * @return bool True if the user has access.
 */
function checkusergroup($group, $user_level, $user_groups, $delim = ',') {

    if (strpos($group, $delim) !== FALSE) {
        foreach (explode($delim, $group) as $group_) {
            if ($user_level == USER_LEVEL_SUPER_ADMIN) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_ADMIN && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER || $group_ == USER_LEVEL_ADMIN)) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_MEMBER && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER)) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_PUBLIC && $group_ == 0) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_MEMBER && $group_ && in_array($group_, explode('.', $user_groups))) {
                return TRUE;
            }
        }
    } else {
        if ($user_level == USER_LEVEL_SUPER_ADMIN) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_ADMIN && ($group == 0 || $group == USER_LEVEL_MEMBER || $group == USER_LEVEL_ADMIN)) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_MEMBER && ($group == 0 || $group == USER_LEVEL_MEMBER)) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_PUBLIC && $group == 0) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_MEMBER && $group && in_array($group, explode('.', $user_groups))) {
            return TRUE;
        }
    }

    return NULL;
}

/**
 * Cache of all user groups from the database.
 *
 * @return array Array of all user groups.
 */
function cache_groups() {
    static $groups_cache = NULL;
    if ($groups_cache === NULL) {
        $groups_cache = [];
        $result = dbquery("SELECT * FROM ".DB_USER_GROUPS." ORDER BY group_id");
        while ($data = dbarray($result)) {
            $groups_cache[$data["group_id"]] = $data;
        }
    }

    return $groups_cache;
}

/**
 * Gets all access levels and user groups and make one array out of them for easy access and usage.
 *
 * @return array  Array of all access levels and user groups.
 */
function getusergroups() {

    $locale = fusion_get_locale();
    $groups_array = [
        [USER_LEVEL_PUBLIC, $locale['user0'], $locale['user0'], 'fa fa-user'],
        [USER_LEVEL_MEMBER, $locale['user1'], $locale['user1'], 'fa fa-user'],
        [USER_LEVEL_ADMIN, $locale['user2'], $locale['user2'], 'fa fa-user'],
        [USER_LEVEL_SUPER_ADMIN, $locale['user3'], $locale['user3'], 'fa fa-user']
    ];
    $groups_cache = cache_groups();
    foreach ($groups_cache as $group) {
        $group_icon = !empty($group['group_icon']) ? $group['group_icon'] : '';
        $group_user_count = format_word($group['group_user_count'], $locale['fmt_user']);
        $groups_array[] = [$group['group_id'], $group['group_name'], $group['group_description'], $group_icon, $group_user_count];
    }

    return $groups_array;
}

/**
 * Get the name of the access level or user group.
 *
 * @param int  $group_id    The ID of the group or access level to which you want to get a name.
 * @param bool $return_desc If true, description will be returned instead of name.
 * @param bool $return_icon If true, icon will be returned next to name.
 *
 * @return string The name or icon or description of the given group, null if it does not exist.
 */
function getgroupname($group_id, $return_desc = FALSE, $return_icon = FALSE) {

    foreach (getusergroups() as $group) {
        if ($group_id == $group[0]) {
            return ($return_desc ? ($group[2] ?: '-') : (!empty($group[3]) && $return_icon ? "<i class='".$group[3]."'></i> " : "").$group[1]);
        }
    }

    return NULL;
}

/**
 * Gets array of all access levels and user groups.
 *
 * @param array $remove Array of groups you want to exclude from output.
 *
 * @return array Array of all access levels and user groups.
 */
function fusion_get_groups($remove = []) {
    $visibility_opts = [];
    $groups = array_diff_key(getusergroups(), array_flip($remove));

    foreach ($groups as $group) {
        $visibility_opts[$group[0]] = $group[1];
    }

    return $visibility_opts;
}

/**
 * Check if user has access to the group.
 *
 * @param int $group_id The ID of the group.
 *
 * @return bool True if the user has access.
 */
function users_groupaccess($group_id) {

    if (preg_match("(^\.$group_id$|\.$group_id\.|\.$group_id$)", fusion_get_userdata('user_groups'))) {
        return TRUE;
    }

    return FALSE;
}

/**
 * Getting the access levels used when asking the database for data.
 *
 * @param string $field MySQL's field from which you want to check access.
 * @param string $delim Delimiter.
 *
 * @return string The part of WHERE clause, always returns a condition.
 */
function groupaccess($field, $delim = ',') {

    $res = '';
    if (iGUEST) {
        $res = $field." in (".USER_LEVEL_PUBLIC.")";
    } else if (iSUPERADMIN) {
        $res = "1 = 1";
    } else if (iADMIN) {
        $res = $field." in (".USER_LEVEL_PUBLIC.", ".USER_LEVEL_MEMBER.", ".USER_LEVEL_ADMIN.")";
    } else if (iMEMBER) {
        $res = $field." in (".USER_LEVEL_PUBLIC.", ".USER_LEVEL_MEMBER.")";
    }

    if (iUSER_GROUPS != "" && !iSUPERADMIN) {
        $groups = explode('.', iUSER_GROUPS);
        $groups_ = [];
        foreach ($groups as $group) {
            $groups_[] = in_group($field, $group, $delim);
        }
        $group_sql = implode(' OR ', $groups_);
        $res = "(".$res." OR ".$group_sql.")";
    }

    return $res;
}

/**
 * Get the data of the access level or user group.
 *
 * @param int $group_id The ID of the group.
 *
 * @return array
 */
function getgroupdata($group_id) {

    foreach (getusergroups() as $group) {
        if ($group_id == $group[0]) {
            return $group;
        }
    }

    return NULL;
}

/**
 * UF blacklist for SQL - same as groupaccess() but $field is the user_id column.
 *
 * @param string $field The name of the field
 *
 * @return string SQL condition. It can return an empty condition if the user_blacklist field is not installed!
 */
function blacklist($field) {

    if (column_exists('users', 'user_blacklist')) {
        $user_id = fusion_get_userdata('user_id');
        if (!empty($user_id)) {
            $result = dbquery("SELECT user_id, user_level FROM ".DB_USERS." WHERE ".in_group('user_blacklist', $user_id));
            if (dbrows($result) > 0) {
                $i = 0;
                $sql = '';

                while ($data = dbarray($result)) {
                    $sql .= ($i > 0) ? "AND $field !='".$data['user_id']."'" : "($field !='".$data['user_id']."'";
                    $i++;
                }
                $sql .= $sql ? ")" : '1=1';

                return $sql;
            }
        }
    }

    return '';
}

/**
 * Check if user was blacklisted by a member.
 *
 * @param int  $user_id User ID.
 * @param bool $me      Set true to hide blocked user's content on your account.
 *
 * @return bool True if the user is blacklisted.
 */
function user_blacklisted($user_id, $me = FALSE) {

    if (column_exists('users', 'user_blacklist')) {
        $my_id = fusion_get_userdata('user_id');
        if ($me && !empty(fusion_get_userdata('user_blacklist'))) {
            $blacklist = explode(',', fusion_get_userdata('user_blacklist'));
            if (!empty($blacklist)) {
                foreach ($blacklist as $id) {
                    if ($id == $user_id) {
                        return TRUE;
                    }
                }
            }
        } else {
            $result = dbquery("SELECT user_id, user_level FROM ".DB_USERS." WHERE ".in_group('user_blacklist', $my_id));
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    if ($user_id == $data['user_id']) {
                        return TRUE;
                    }
                }
            }
        }
    }

    return FALSE;
}

/**
 * Create a list of files or folders and store them in an array.
 *
 * @param string $folder     Path to folder.
 * @param string $filter     The names of the filtered folders and files separated by |, false to use default filter.
 * @param bool   $sort       False if you don't want to sort the result.
 * @param string $type       Possible value: files, folders.
 * @param string $ext_filter File extensions separated by |, only when $type is 'files'.
 *
 * @return array Array of all items.
 */
function makefilelist($folder, $filter = "", $sort = TRUE, $type = "files", $ext_filter = "") {

    $res = [];

    $default_filters = '.|..|.htaccess|index.php|._DS_STORE|.tmp';
    if ($filter === FALSE) {
        $filter = $default_filters;
    }

    $filter = explode("|", $filter);
    if ($type == "files" && !empty($ext_filter)) {
        $ext_filter = explode("|", strtolower($ext_filter));
    }
    if (file_exists($folder)) {
        $temp = opendir($folder);
        while ($file = readdir($temp)) {
            if ($type == "files" && !in_array($file, $filter)) {
                if (!empty($ext_filter)) {
                    if (!in_array(substr(strtolower(stristr($file, '.')), +1), $ext_filter) && !is_dir($folder.$file)) {
                        $res[] = $file;
                    }
                } else {
                    if (is_file($folder.$file)) {
                        $res[] = $file;
                    }
                }
            } else if
            ($type == "folders" && !in_array($file, $filter)) {
                if (is_dir($folder.$file)) {
                    $res[] = $file;
                }
            }
        }
        closedir($temp);
        if ($sort) {
            natsort($res);
        }

    } else {
        $error_log = debug_backtrace()[1];
        $function = (isset($error_log['class']) ? $error_log['class'] : '').(isset($error_log['type']) ? $error_log['type'] : '').(isset($error_log['function']) ? $error_log['function'] : '');
        $error_log = strtr(fusion_get_locale('err_103', LOCALE.LOCALESET.'errors.php'), [
            '{%folder%}'   => $folder,
            '{%function%}' => (!empty($function) ? '<code class=\'m-r-10\'>'.$function.'</code>' : '')
        ]);
        set_error(2, $error_log, debug_backtrace()[1]['file'], debug_backtrace()[1]['line']);
    }

    return $res;
}

/**
 * Creates page navigation.
 *
 * @param int    $rowstart The number of the first listed item.
 * @param int    $count    The number of entries displayed on one page.
 * @param int    $total    The total entries which should be displayed.
 * @param int    $range    The number of page buttons displayed and the range of them.
 * @param string $link     The base url before the appended part.
 * @param string $getname  The name of the $_GET parameter that contains the start number.
 * @param bool   $button   Displays as button.
 *
 * @return string|bool HTML navigation. False if $count is invalid.
 */
function makepagenav($rowstart, $count, $total, $range = 3, $link = "", $getname = "rowstart", $button = FALSE) {

    $locale = fusion_get_locale();
    /* Bootstrap may be disabled in theme (see Gillette for example) without settings change in DB.
       In such case this function will not work properly.
       With this fix (used $settings instead fusion_get_settings) function will work.*/
    if (defined('BOOTSTRAP') && BOOTSTRAP == TRUE) {
        $tpl_global = "<nav class='pagination'><div class='pagination-row'>%s</div><div class='pagination-nav'><div class='btn-group'>\n%s</div></div></nav>\n";
        $tpl_currpage = "<a class='btn btn-default active' href='%s=%d'><strong>%d</strong></a>\n";
        $tpl_page = "<a class='btn btn-default' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_divider = "</div>\n<span>...</span>\n<div class='btn-group'>";
        $tpl_firstpage = "<a class='btn btn-default' data-value='0' href='%s=0'>1</a>\n";
        $tpl_lastpage = "<a class='btn btn-default' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_button = "<a class='btn btn-primary btn-block btn-md' data-value='%d' href='%s=%d'>%s</a>\n";
    } else {
        $tpl_global = "<div class='pagenav'>%s\n%s</div>\n";
        $tpl_currpage = "<a class='pagenavlink active' href='%s=%d'>%d</a>";
        $tpl_page = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>";
        $tpl_divider = "<span class='pagenavdivider'>...</span>";
        $tpl_firstpage = "<a class='pagenavlink' data-value='0' href='%s=0'>1</a>";
        $tpl_lastpage = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_button = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>\n";
    }

    if ($link == '') {
        $link = FUSION_SELF."?";
        if (fusion_get_settings("site_seo") && defined('IN_PERMALINK')) {
            global $filepath;
            $link = $filepath."?";
        }
    }
    if (!preg_match("#[0-9]+#", $count) || $count == 0) {
        return FALSE;
    }
    $pg_cnt = ceil($total / $count);
    if ($pg_cnt <= 1) {
        return "";
    }
    $idx_back = $rowstart - $count;
    $idx_next = $rowstart + $count;

    if ($button == TRUE) {
        if ($idx_next >= $total) {
            return sprintf($tpl_button, 0, $link.$getname, 0, $locale['load_end']);
        } else {
            return sprintf($tpl_button, $idx_next, $link.$getname, $idx_next, $locale['load_more']);
        }
    }

    $cur_page = ceil(($rowstart + 1) / $count);
    $idx_fst = max($cur_page - $range, 1);
    $idx_lst = min($cur_page + $range, $pg_cnt);

    if ($range == 0) {
        $idx_fst = 1;
        $idx_lst = $pg_cnt;
    }

    $res = '';

    if ($cur_page != $idx_fst) {
        $res .= sprintf($tpl_page, 0, $link.$getname, 0, get_icon('first').$locale['first']);
        $res .= sprintf($tpl_page, $idx_back, $link.$getname, $idx_back, get_icon('previous').$locale['previous']);
    }

    if ($idx_back >= 0) {
        if ($cur_page > ($range + 1)) {
            $res .= sprintf($tpl_firstpage, $link.$getname);
            if ($cur_page != ($range + 2)) {
                $res .= $tpl_divider;
            }
        }
    }

    for ($i = $idx_fst; $i <= $idx_lst; $i++) {
        $offset_page = ($i - 1) * $count;
        if ($i == $cur_page) {
            $res .= sprintf($tpl_currpage, $link.$getname, $offset_page, $i);
        } else {
            $res .= sprintf($tpl_page, $offset_page, $link.$getname, $offset_page, $i);
        }
    }

    if ($idx_next < $total) {
        if ($cur_page < ($pg_cnt - $range)) {
            if ($cur_page != ($pg_cnt - $range - 1)) {
                $res .= $tpl_divider;
            }

            $res .= sprintf($tpl_lastpage, ($pg_cnt - 1) * $count, $link.$getname, ($pg_cnt - 1) * $count, $pg_cnt);
        }
    }

    if ($cur_page != $idx_lst) {

        $res .= sprintf($tpl_page, $idx_next, $link.$getname, $idx_next, $locale['next'].get_icon('next'));
        $res .= sprintf($tpl_page, ($pg_cnt - 1) * $count, $link.$getname, ($pg_cnt - 1) * $count, $locale['last'].get_icon('last'));
    }

    // if there is a request, we can redirect
    if (check_post($getname.'_pg')) {
        if ($val = sanitizer($getname.'_pg', '', $getname.'_pg')) {
            redirect(clean_request($getname.'='.($val * $count - $count), [$getname], FALSE));
        } else {
            redirect(clean_request('', [$getname], FALSE));
        }
    }

    $cur_page_field = openform(random_string(5), 'POST', FORM_REQUEST, ['class' => 'display-inline-block']).form_text($getname.'_pg', '', $cur_page, ['inline' => TRUE, 'inner_class' => 'input-sm']).closeform();

    return sprintf($tpl_global, "<span>".$locale['global_092']."</span> ".$cur_page_field." ".$locale['global_093']." ".$pg_cnt, $res);
}

/**
 * Rowstart count.
 *
 * @param int $count The number of entries displayed on one page.
 * @param int $total The total entries which should be displayed.
 * @param int $range The number of page buttons displayed and the range of them.
 *
 * @return float
 */
function rowstart_count($total, $count, $range = 3) {

    if ($total > $count) {
        $cur_page = ceil(($total + 1) / $count);
        $pg_cnt = ceil($total / $count);
        if ($pg_cnt <= 1) {
            return 0;
        }
        $row = min($cur_page + $range, $pg_cnt);

        return ($row - 1) * $count;
    }

    return 0;
}

/**
 * Infinite scroll pagination.
 *
 * @param string $scroll_url The ajax script that loads the content.
 * @param int    $rowstart   The number of the first listed item.
 * @param int    $count      The number of entries displayed on one page.
 * @param string $getname    The name of the $_GET parameter that contains the start number.
 * @param string $http_query Additional http query.
 *
 * @return string
 */
function infinite_scroll($scroll_url, $rowstart, $count, $getname = 'rowstart', $http_query = '') {

    $locale = fusion_get_locale();

    add_to_jquery("
        var count = $rowstart+1;
        $(window).scroll(function(){
          if ($(window).scrollTop() == ($(document).height() - $(window).height())) {
            if (count <= '$count') {
                loadInfinityContent(count);
                count++;
            }
          }
        });
       function loadInfinityContent(pageNumber){
           $('.infiniteLoader').show('fast');
           $.ajax({
                  url: '$scroll_url',
                  type:'GET',
                  data: 'action=infinite_scroll&$getname='+ pageNumber +'".($http_query ? "&".$http_query : '')."',
                  success: function(html){
                      $('.infiniteLoader').hide();
                      $('#scroll_target').append(html);  // This will be the div where our content will be loaded
                  }
              });
          return false;
        }
    ");

    return "
    <div id='scroll_target'></div>
    <div class='infiniteLoader panel panel-default' style='display:none;'><div class='panel-body text-center'>".$locale['loading']."</div></div>
    ";
}

/**
 * Hierarchy Page Breadcrumbs, generates breadcrumbs on all your category needs.
 *
 * @param array  $tree_index dbquery_tree() or tree_index().
 * @param array  $tree_full  dbquery_tree_full().
 * @param string $id_col     The name of the category id column.
 * @param string $title_col  The name of the category nmae column.
 * @param string $getname    The name of the $_GET parameter.
 */
function make_page_breadcrumbs($tree_index, $tree_full, $id_col, $title_col, $getname = "rownav") {

    $_GET[$getname] = !empty($_GET[$getname]) && isnum($_GET[$getname]) ? $_GET[$getname] : 0;

    // Recursive fatal protection
    if (!function_exists('breadcrumb_page_arrays')) {
        function breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, $id) {

            $crumb = [];
            if (isset($tree_index[get_parent($tree_index, $id)])) {
                $_name = get_parent_array($tree_full, $id);
                $crumb = [
                    'link'  => isset($_name[$id_col]) ? clean_request($getname."=".$_name[$id_col], ["aid"]) : "",
                    'title' => isset($_name[$title_col]) ? QuantumFields::parseLabel($_name[$title_col]) : "",
                ];
                if (get_parent($tree_index, $id) == 0) {
                    return $crumb;
                }
                $crumb_1 = breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, get_parent($tree_index, $id));

                if (!empty($crumb_1)) {
                    $crumb = array_merge_recursive($crumb, $crumb_1);
                }

            }

            return $crumb;
        }
    }

    // then we make an infinity recursive function to loop/break it out.
    $crumb = breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, $_GET[$getname]);
    // then we sort in reverse.
    $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
    if ($title_count) {
        krsort($crumb['title']);
        krsort($crumb['link']);
    }
    if ($title_count) {
        foreach ($crumb['title'] as $i => $value) {
            add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $value]);
            if ($i == count($crumb['title']) - 1) {
                add_to_title($value);
                add_to_meta($value);
            }
        }
    } else if (isset($crumb['title'])) {
        add_to_title($crumb['title']);
        add_to_meta($crumb['title']);
        add_breadcrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
    }
}

/**
 * Format the date and time according to the site and user offset.
 *
 * @param string $format  Possible value: shortdate, longdate, forumdate, newsdate or date pattern for the strftime.
 * @param int    $val     Unix timestamp.
 * @param array  $options Possible options tz_override.
 *
 * @return string String formatted according to the given format string.
 *                Month and weekday names and other language dependent strings respect the current locale set.
 */
function showdate($format, $val, $options = []) {
    $userdata = fusion_get_userdata();

    if (isset($options['tz_override'])) {
        $tz_client = $options['tz_override'];
    } else {
        if (!empty($userdata['user_timezone'])) {
            $tz_client = $userdata['user_timezone'];
        } else {
            $tz_client = fusion_get_settings('timeoffset');
        }
    }

    if (empty($tz_client)) {
        $tz_client = 'Europe/London';
    }

    $offset = 0;

    try {
        $client_dtz = new DateTimeZone($tz_client);
        $client_dt = new DateTime('now', $client_dtz);
        $offset = (int)$client_dtz->getOffset($client_dt);
    } catch (Exception $e) {
        set_error(E_CORE_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    if (!empty($val)) {
        $offset = (int)$val + $offset;
        if (in_array($format, ['shortdate', 'longdate', 'forumdate', 'newsdate'])) {
            $format = fusion_get_settings($format);

            return format_date($format, $offset);
        }

        return format_date($format, $offset);

    }

    $format = fusion_get_settings($format);
    $offset = time() + $offset;

    return format_date($format, $offset);
}

/**
 * Format date - replacement for strftime()
 *
 * @param string $format Dateformat
 * @param int    $time   Timestamp
 *
 * @return string
 */
function format_date($format, $timestamp) {
    $locale = fusion_get_locale();
    $format = str_replace(
        ['%a', '%A', '%d', '%e', '%u', '%w', '%W', '%b', '%h', '%B', '%m', '%y', '%Y', '%D', '%F', '%x', '%n', '%t', '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z', '%c', '%s', '%%'],
        ['D', 'l', 'd', 'j', 'N', 'w', 'W', 'M', 'M', 'F', 'm', 'y', 'Y', 'm/d/y', 'Y-m-d', 'm/d/y', "\n", "\t", 'H', 'G', 'h', 'g', 'i', 'a', 'A', 'h:i:s A', 'H:i', 's', 'H:i:s', 'H:i:s', 'O', 'T', 'D M j H:i:s Y', 'U', '%'],
        $format
    );

    $format = preg_replace('/(?<!\\\\)r/', DATE_RFC2822, $format);
    $new_format = '';
    $format_length = strlen($format);
    $lcmonth = explode('|', $locale['months']);
    $lcweek = explode('|', $locale['weekdays']);
    $lcshort = explode('|', $locale['shortmonths']);
    $lcmerid = explode('|', $locale['meridiem']);

    $date = DateTimeImmutable::createFromFormat('U', $timestamp);

    for ($i = 0; $i < $format_length; $i ++) {
        switch ($format[$i]) {
            case 'D':
                $new_format .= addcslashes(substr($lcweek[$date->format('w')], 0, 2), '\\A..Za..z');
                break;
            case 'l':
                $new_format .= addcslashes($lcweek[$date->format('w')], '\\A..Za..z');
                break;
            case 'F':
                $new_format .= addcslashes($lcmonth[$date->format('n')], '\\A..Za..z');
                break;
            case 'M':
                $new_format .= addcslashes($lcshort[$date->format('n')], '\\A..Za..z');
                break;
            case 'a':
                $mofset = $offset->format('a') == 'am' ? 0 : 1;
                $new_format .= addcslashes($lcmerid[$mofset], '\\A..Za..z');
                break;
            case 'A':
                $mofset = $offset->format('A') == 'AM' ? 2 : 3;
                $new_format .= addcslashes($lcmerid[$mofset], '\\A..Za..z');
                break;
            case '\\':
                $new_format .= $format[$i];
                // If character follows a slash, we add it without translating.
                if ($i < $format_length) {
                    $new_format .= $format[++$i];
                }
                break;
            default:
                $new_format .= $format[$i];
                break;
        }
    }

    return $date->format($new_format);
}
/*function format_date($format, $time) {
    $format = str_replace(
        ['%a', '%A', '%d', '%e', '%u', '%w', '%W', '%b', '%h', '%B', '%m', '%y', '%Y', '%D', '%F', '%x', '%n', '%t', '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z', '%c', '%s', '%%'],
        ['D', 'l', 'd', 'j', 'N', 'w', 'W', 'M', 'M', 'F', 'm', 'y', 'Y', 'm/d/y', 'Y-m-d', 'm/d/y', "\n", "\t", 'H', 'G', 'h', 'g', 'i', 'A', 'a', 'h:i:s A', 'H:i', 's', 'H:i:s', 'H:i:s', 'O', 'T', 'D M j H:i:s Y', 'U', '%'],
        $format
    );

    $date = DateTimeImmutable::createFromFormat('U', $time);

    return $date->format($format);
}*/

/**
 * Translate bytes into kB, MB, GB or TB.
 *
 * @param int  $size     The number of bytes.
 * @param int  $decimals The number of decimals.
 * @param bool $dir      True if it is the size of a directory.
 *
 * @return string
 */
function parsebytesize($size, $decimals = 2, $dir = FALSE) {

    $locale = fusion_get_locale();

    $kb = 1024;
    $mb = 1024 * $kb;
    $gb = 1024 * $mb;
    $tb = 1024 * $gb;

    $size = (empty($size)) ? "0" : $size;

    if (($size == 0) && ($dir)) {
        return "0 ".$locale['global_460'];
    } else if ($size < $kb) {
        return $size.$locale['global_461'];
    } else if ($size < $mb) {
        return round($size / $kb, $decimals).'kB';
    } else if ($size < $gb) {
        return round($size / $mb, $decimals).'MB';
    } else if ($size < $tb) {
        return round($size / $gb, $decimals).'GB';
    } else {
        return round($size / $tb, $decimals).'TB';
    }
}

/**
 * Prints human-readable information about a variable.
 *
 * @param mixed $data  The expression to be printed.
 * @param bool  $modal Dump info in the modal.
 * @param bool  $print Dump info in <pre> tag.
 *
 * @return string The value of the variable.
 */
function print_p($data, $modal = FALSE, $print = TRUE) {

    ob_start();
    echo htmlspecialchars(print_r($data, TRUE), ENT_QUOTES, 'utf-8');
    $debug = ob_get_clean();
    if ($modal == TRUE) {
        $modal = openmodal('Debug', 'Debug');
        $modal .= "<pre class='printp' style='white-space:pre-wrap !important;'>";
        $modal .= $debug;
        $modal .= "</pre>\n";
        $modal .= closemodal();
        PHPFusion\OutputHandler::addToFooter($modal);

        return FALSE;
    }
    if ($print == TRUE) {
        echo "<pre class='printp' style='white-space:pre-wrap !important;'>";
        echo $debug;
        echo "</pre>\n";
    }

    return $debug;
}

/**
 * Fetch the settings from the database.
 *
 * @param string $key The key of one setting
 *
 * @return string[]|string Associative array of settings or one setting by key.
 */
function fusion_get_settings($key = NULL) {

    // It is initialized only once because of 'static'
    static $settings = [];
    if (empty($settings) and defined('DB_SETTINGS') and dbconnection() && db_exists('settings')) {
        $result = dbquery("SELECT * FROM ".DB_SETTINGS);
        while ($data = dbarray($result)) {
            $settings[$data['settings_name']] = $data['settings_value'];
        }
    }

    return $key === NULL ? $settings : ($settings[$key] ?? NULL);
}

/**
 * Fetch username by ID.
 *
 * @param int $user_id User ID.
 *
 * @return string Username.
 */
function fusion_get_username($user_id) {

    $result = (dbresult(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='".intval($user_id)."'"), 0));

    return ($result !== NULL) ? $result : fusion_get_locale("na");
}

/**
 * Fetch user data of the currently logged-in user from database.
 *
 * @param string $key The key of one column.
 *
 * @return string|array Associative array of all data or one column by key.
 */
function fusion_get_userdata($key = NULL) {

    global $userdata;
    if (empty($userdata)) {
        $userdata = ["user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => 'Default', "user_ip" => USER_IP];
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

/**
 * Get the data of any user by ID.
 *
 * @param int    $user_id The user ID.
 * @param string $key     The key of column.
 *
 * @return string|array Associative array of all data or one column by key.
 */
function fusion_get_user($user_id, $key = NULL) {

    static $user = [];
    if (!isset($user[$user_id]) && isnum($user_id)) {
        $user[$user_id] = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".intval($user_id)."'"));
    }
    if (!isset($user[$user_id])) {
        return NULL;
    }

    return $key === NULL ? $user[$user_id] : ($user[$user_id][$key] ?? NULL);
}

/**
 * Get Aidlink.
 *
 * @return string
 */
function fusion_get_aidlink() {

    $aidlink = '';
    if (defined('iADMIN') && iADMIN && defined('iAUTH')) {
        $aidlink = '?aid='.iAUTH;
    }

    return $aidlink;
}

/**
 * Get form tokens.
 *
 * @param string $form_id    Form ID.
 * @param int    $max_tokens Max tokens.
 *
 * @return string
 */
function fusion_get_token($form_id, $max_tokens = 5) {

    return Token::generate_token($form_id, $max_tokens);
}

/**
 * Fetch user PM settings.
 *
 * @param int    $user_id User ID.
 * @param string $key     user_inbox, user_outbox, user_archive, user_pm_email_notify, user_pm_save_sent
 *
 * @return array|string Associative array of all data or one column by key.
 */
function user_pm_settings($user_id, $key = NULL) {

    return PrivateMessages::getPmSettings($user_id, $key);
}

/**
 * Define constants for site language.
 *
 * @param string $lang The name of the language.
 */
function define_site_language($lang) {

    if (valid_language($lang)) {
        define('LANGUAGE', $lang);
        define('LOCALESET', $lang.'/');
    }
}

/**
 * Get the language package shortcode within global.php file
 *
 * @param $language_pack - // representation of folder name
 *
 * @return mixed
 */
function get_language_code($language_pack) {
    $locale = [];
    try {
        include LOCALE.$language_pack.'/global.php';
        return $locale['short_lang_name'] ?? $language_pack;
    } catch (Exception $e) {
        debug_print_backtrace();
        die('Stopping process');
    }
}

/**
 * Set the requested language.
 *
 * @param string $lang The name of the language.
 */
function set_language($lang) {

    $userdata = fusion_get_userdata();
    if (valid_language($lang)) {
        if (iMEMBER) {
            dbquery("UPDATE ".DB_USERS." SET user_language='".$lang."' WHERE user_id='".$userdata['user_id']."'");
        } else {
            $rows = dbrows(dbquery("SELECT user_language FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
            if ($rows != 0) {
                dbquery("UPDATE ".DB_LANGUAGE_SESSIONS." SET user_language='".$lang."', user_datestamp='".time()."' WHERE user_ip='".USER_IP."'");
            } else {
                dbquery("INSERT INTO ".DB_LANGUAGE_SESSIONS." (user_ip, user_language, user_datestamp) VALUES ('".USER_IP."', '".$lang."', '".time()."');");
            }
            // Sanitize guest sessions occasionally
            dbquery("DELETE FROM ".DB_LANGUAGE_SESSIONS." WHERE user_datestamp<'".(time() - (86400 * 60))."'");
        }
    }
}

/**
 * Check if a given language is valid or if exists.
 * Checks whether a language can be found in enabled languages array.
 * Can also be used to check whether a language actually exists.
 *
 * @param string $lang       The name of the language.
 * @param bool   $file_check Intended to be used when enabling languages in Admin Panel.
 *
 * @return bool
 */
function valid_language($lang, $file_check = FALSE) {

    $enabled_languages = fusion_get_enabled_languages(TRUE);
    if (preg_match("/^([a-z0-9_-]){2,50}$/i", $lang) &&
        ($file_check ? file_exists(LOCALE.$lang."/global.php") : isset($enabled_languages[$lang]))
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Check language folder name and file
 *
 * @param $lang
 *
 * @return bool
 */
function check_language($lang) {
    return preg_match("/^([a-z0-9_-]){2,50}$/i", $lang) && is_file(LOCALE.$lang."/global.php");

}

/**
 * Get language switch arrays.
 *
 * @return array
 */
function fusion_get_language_switch() {

    static $language_switch = [];
    if (empty($language_link)) {
        $enabled_languages = fusion_get_enabled_languages();
        foreach ($enabled_languages as $language => $language_name) {
            $link = clean_request('lang='.$language, ['lang'], FALSE);
            $language_switch[$language] = [
                "language_name"   => $language_name,
                "language_icon_s" => BASEDIR."locale/$language/$language-s.png",
                "language_icon"   => BASEDIR."locale/$language/$language.png",
                "language_link"   => $link,
            ];
        }
    }

    return $language_switch;
}

/**
 * Get the array of enabled languages.
 *
 * @return array
 */
function fusion_get_enabled_languages($skip_translate = FALSE) {

    $settings = fusion_get_settings();
    $enabled_languages = [];

    if (isset($settings['enabled_languages'])) {
        $values = explode('.', $settings['enabled_languages']);
        foreach ($values as $language_name) {
            $enabled_languages[$language_name] = $skip_translate ? $language_name : translate_lang_names($language_name, TRUE);
        }
    }
    return $enabled_languages;
}

/**
 * Get the array of detected languages.
 *
 * @return array
 */
function fusion_get_detected_languages() {

    static $detected_languages = NULL;
    if ($detected_languages === NULL) {
        $all_languages = makefilelist(LOCALE, ".svn|.|..", TRUE, "folders");
        foreach ($all_languages as $language_name) {
            $detected_languages[$language_name] = translate_lang_names($language_name);
        }
    }

    return $detected_languages;
}

/**
 * Run the installer or halt the script
 */
function fusion_run_installer() {

    if (is_file("install.php")) {
        redirect("install.php");
    } else {
        die("No config.php or install.php files were found");
    }
}

/**
 * Detect whether the system is installed and return the config file path.
 *
 * @return string
 */
function fusion_detect_installation() {

    $config_path = dirname(__DIR__).'/config.php';
    if (!is_file($config_path) or !filesize($config_path)) {
        fusion_run_installer();
    }

    return $config_path;
}

/**
 * Log user actions.
 *
 * @param int    $user_id     User ID.
 * @param string $column_name Affected column.
 * @param string $new_value   New value.
 * @param string $old_value   Old value.
 */
function save_user_log($user_id, $column_name, $new_value, $old_value) {

    $data = [
        "userlog_id"        => 0,
        "userlog_user_id"   => $user_id,
        "userlog_field"     => $column_name,
        "userlog_value_new" => $new_value,
        "userlog_value_old" => $old_value,
        "userlog_timestamp" => time(),
    ];
    dbquery_insert(DB_USER_LOG, $data, "save", ["keep_session" => TRUE]);
}

/**
 * Minify JS code.
 *
 * @param string $code Unminified code.
 *
 * @return string Minified code.
 */
function jsminify($code) {

    $minifier = new JS($code);

    return $minifier->minify();
}

/**
 * A wrapper function for file_put_contents with cache invalidation.
 * If opcache is enabled on the server, this function will write the file.
 * as the original file_put_contents and invalidate the cache of the file.
 * It is needed when you create a file dynamically and want to include it
 * before the cache is invalidated. Redirection does not matter.
 *
 * @param string       $file File path.
 * @param string|array $data The data to write.
 * @param int          $flags
 *
 * @return int Number of written bytes
 */
function write_file($file, $data, $flags = NULL) {

    if ($flags === NULL) {
        $bytes = file_put_contents($file, $data);
    } else {
        $bytes = file_put_contents($file, $data, $flags);
    }
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($file, TRUE);
    }

    return $bytes;
}

/**
 * Return the time in seconds
 *
 * @param $value
 * @param $denominator
 *
 * @return float|int|mixed
 */
function calculate_time($value, $denominator) {
    $multiplier = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'j' => 86400,
    ];
    if (isnum($value) && isset($multiplier[$denominator])) {
        return $value * $multiplier[$denominator];
    }
    return $value;
}


/**
 * Returns nearest data unit.
 *
 * @param int $total_bit Number of bytes.
 *
 * @return int
 */
function calculate_byte($total_bit) {
    $calc_opts = fusion_get_locale('admins_1020', LOCALE.LOCALESET."admin/settings.php");
    foreach ($calc_opts as $byte => $val) {
        if ($total_bit / $byte <= 999) {
            return (int)$byte;
        }
    }

    return 1048576;
}

/**
 * Convert B, KB, MB, GB, TB, PB to bytes
 *
 * @param $value
 *
 * @return array|float|int|string|string[]|null
 */
function parse_byte($value) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $number = substr($value, 0, -2);
    $suffix = strtoupper(substr($value, -2));

    //B or no suffix
    if (is_numeric(substr($suffix, 0, 1))) {
        return preg_replace('/[^\d]/', '', $value);
    }

    $exponent = array_flip($units)[$suffix] ?? NULL;
    if ($exponent === NULL) {
        return NULL;
    }

    return $number * (1024 ** $exponent);
}

/**
 * Recursively remove folder and all files/subdirectories.
 *
 * @param string $dir Path to the folder.
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
 * Alternative to rename() that works on Windows.
 *
 * @param string $origin The old name.
 * @param string $target The new name.
 */
function fusion_rename($origin, $target) {

    if ($origin != "." && $origin != ".." && !is_dir($origin)) {
        if (TRUE !== @rename($origin, $target)) {
            copy($origin, $target);
            unlink($origin);
        }
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
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // PHP 7.1
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
    } else {
        $data = @file_get_contents($url);
    }

    return $data;
}

/**
 * Checks whether a string is JSON or not.
 *
 * @param string $string The string to be checked.
 *
 * @return bool
 */
function is_json($string) {

    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Cached script loader.
 * This function will cache the path that has been added and avoid duplicates.
 *
 * @param string $file_path The source file.
 * @param string $file_type Possible value: script, css.
 * @param bool   $html      Return as html tags instead add to output handler.
 * @param bool   $cached    False to invalidate browser's cache.
 *
 * @return string|null
 */
function fusion_load_script($file_path, $file_type = "script", $html = FALSE, $cached = TRUE) {
    static $paths = [];

    $file_info = pathinfo($file_path);

    if (isset($file_info['dirname']) && isset($file_info['basename']) && isset($file_info['extension']) && isset($file_info['filename'])) {

        $mtime = 0;
        $file = $file_info['dirname'].'/'.$file_info['basename'];
        $min_file = $file_info['dirname'].'/'.$file_info['filename'].(!stristr($file_info['filename'], '.min') ? '.min.' : '.').$file_info['extension'];
        // do not inspect this file
        $return_file = $file;
        // inspect only on min file
        $siteurl = fusion_get_settings('siteurl') ?? $_SERVER['HTTP_HOST'];
        $m_min_file = str_replace($siteurl, BASEDIR, $min_file);

        if (is_file($m_min_file)) { // fixes https:// on local server
            $return_file = $m_min_file;
        } else if (is_file($min_file)) { // checks local server
            $return_file = $min_file;
        } else if (filter_var($min_file, FILTER_VALIDATE_DOMAIN)) { // checks remote server
            // this is very slow... over 10 seconds on some circumstance
            // if (fusion_get_contents($min_file)) {
            $return_file = $min_file;
            // }
        }

        if (is_file($return_file)) {
            $mtime = filemtime($return_file);
        }

        $file_path = $return_file."?v=".$mtime;
        if (!$cached) {
            $file_path = $return_file;
        }
    }

    if ($file_path && empty($paths[$file_path])) {

        $paths[$file_path] = $file_path;

        if ($file_type == "script") {

            $html_tag = "<script src='$file_path'></script>";
            if ($html === TRUE) {
                return $html_tag;
            }
            add_to_footer($html_tag);

        } else if ($file_type == "css") {
            $html_tag = "<link rel='stylesheet' href='$file_path' type='text/css' media='all'>";
            if ($html === TRUE) {
                return $html_tag;
            }
            add_to_head($html_tag);
        }
    }

    return NULL;
}

/**
 * Get max server upload limit.
 *
 * @return mixed
 */
function max_server_upload() {

    // select maximum upload size
    $max_upload = convert_to_bytes(ini_get('upload_max_filesize'));
    // select post limit
    $max_post = convert_to_bytes(ini_get('post_max_size'));
    // select memory limit
    $memory_limit = convert_to_bytes(ini_get('memory_limit'));

    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit);
}

/**
 * Convert to bytes.
 *
 * @param int|string $val
 *
 * @return int
 */
function convert_to_bytes($val) {

    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $kb = 1024;
    $mb = 1024 * $kb;
    $gb = 1024 * $mb;
    switch ($last) {
        case 'g':
            $val = (int)$val * $gb;
            break;
        case 'm':
            $val = (int)$val * $mb;
            break;
        case 'k':
            $val = (int)$val * $kb;
            break;
    }

    return (int)$val;
}

/**
 * Get current URL.
 *
 * @return string
 */
function get_current_url() {

    $s = (empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on")) ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $port = ($_SERVER["SERVER_PORT"] == "80" || ($_SERVER['SERVER_PORT'] == "443" && $s == "s")) ? "" : (":".$_SERVER["SERVER_PORT"]);

    return $protocol."://".$_SERVER['SERVER_NAME'].$port.
        (str_replace(basename(cleanurl($_SERVER['PHP_SELF'])), "", $_SERVER['REQUEST_URI']));
}

/**
 * @param $s1
 * @param $s2
 *
 * @return false|string
 */
function strleft($s1, $s2) {

    return substr($s1, 0, strpos($s1, $s2));
}

/**
 * Adds a whitespace if value is present.
 *
 * @param string $value
 *
 * @return string
 */
function whitespace($value) {

    if (!empty($value)) {
        return " ".$value;
    }

    return "";
}

/**
 * Send a cookie.
 *
 * @param string      $name     The name of the cookie.
 * @param string      $value    The value of the cookie.
 * @param int         $expires  The time the cookie expires.
 * @param string      $path     The path on the server in which the cookie will be available on.
 * @param string      $domain   The (sub)domain that the cookie is available to.
 * @param bool        $secure   Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using HTTPS.
 * @param bool        $httponly Whether the cookie will be made accessible only through the HTTP protocol.
 * @param string|null $samesite Whether the cookie will be available for cross-site requests. Possible value: none | lax | strict
 */
function fusion_set_cookie($name, $value, $expires, $path, $domain, $secure = FALSE, $httponly = FALSE, $samesite = NULL) {

    $samesite = in_array($samesite, ['lax', 'none', 'strict', NULL]) ? $samesite : NULL;

    if (PHP_VERSION_ID < 70300) {
        if (!headers_sent()) {
            if ($value !== '') {
                $expires = $expires !== 0 ? ' expires='.$expires.';' : '';
                $domain = $domain ? 'domain='.$domain.';' : '';
                $secure = $secure ? 'secure;' : '';
                $httponly = $httponly ? 'httponly;' : '';
                $samesite = $samesite !== NULL ? 'samesite='.$samesite : '';

                header("Set-Cookie: $name=$value; $expires path=$path; $domain $secure $httponly $samesite");
            } else {
                setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
            }
        } else {
            setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
        }
    } else {
        setcookie($name,
            $value,
            [
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);
    }
}

/**
 * Turn on/off maintenance mode.
 *
 * @param bool $maintenance Turn On/Off.
 *
 * @return bool
 */
function maintenance_mode($maintenance = TRUE) {

    $file = BASEDIR.'.maintenance';

    if ($maintenance) {
        if (!($fp = @fopen($file, 'w'))) {
            return FALSE;
        }

        @fwrite($fp, '<?php $mt_mode_start = '.time().'; ?>');
        @fclose($fp);
        @chmod($file, 0644);

        return is_readable($file);
    } else {
        if (file_exists($file)) {
            return @unlink($file);
        }

        return NULL;
    }
}

/**
 * Recursive in_array
 *
 * @param mixed $needle   The searched value.
 * @param array $haystack The array.
 * @param bool  $strict   If the third parameter strict is set to true then the in_array() function will also check the types of the needle in the haystack.
 *
 * @return bool
 */
function in_array_r($needle, $haystack, $strict = FALSE) {

    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * Check if current page is set as homepage.
 *
 * @return bool
 */
function is_homepage() {

    $settings = fusion_get_settings();

    if ($settings['site_seo']) {
        $params = http_build_query(\PHPFusion\Rewrite\Router::getRouterInstance()->getFileParams());
        $path = \PHPFusion\Rewrite\Router::getRouterInstance()->getFilePath();
        $file_path = '/'.(!empty($path) ? $path : PERMALINK_CURRENT_PATH).($params ? "?" : '').$params;
    } else {
        $file_path = '/'.PERMALINK_CURRENT_PATH;
    }

    return $settings['opening_page'] == 'index.php' && $file_path == '/' || $file_path == '/'.$settings['opening_page'];
}
