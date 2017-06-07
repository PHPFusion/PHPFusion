<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/core_functions_include.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
use PHPFusion\Authenticate;
use PHPFusion\OutputHandler;

/**
 * Current microtime as float to calculate script start/end time
 *
 * @deprecated since version 9.00, use microtime(TRUE) instead
 * @return float
 */
function get_microtime() {
    return microtime(TRUE);
}

/**
 * Get currency symbol by using a 3-letter ISO 4217 currency code
 * Note that if INTL pecl package is not installed, signs will degrade to ISO4217 code itself
 *
 * @param      $iso         = 3-letter ISO 4217
 * @param bool $description - set to false for just symbol
 *
 * @return null
 */
function fusion_get_currency($iso = NULL, $description = TRUE) {
    static $__currency = array();
    if (empty($locale['charset'])) {
        // Do not use $__currency and $iso in these 2 files
        include LOCALE.LOCALESET."global.php";
        include LOCALE.LOCALESET."currency.php";
    }
    if (empty($__currency)) {
        // Euro Exceptions list
        $currency_exceptions = array(
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
            "LTL" => "EUR",
            "LUF" => "EUR",
            "LVL" => "EUR",
            "MCF" => "EUR",
            "MTL" => "EUR",
            "NLG" => "EUR",
            "PTE" => "EUR",
            "SIT" => "EUR",
            "SKK" => "EUR",
            "SML" => "EUR",
            "VAL" => "EUR",
            "DDM" => "EUR",
            "ESA" => "EUR",
            "ESB" => "EUR",
        );
        foreach (array_keys($locale['currency']) as $country_iso) {
            $c_iso = !empty($currency_exceptions[$country_iso]) ? $currency_exceptions[$country_iso] : $country_iso;
            $c_symbol = (!empty($locale['currency_symbol'][$c_iso]) ? html_entity_decode($locale['currency_symbol'][$c_iso], ENT_QUOTES, $locale['charset']) : $c_iso);
            $c_text = $locale['currency'][$c_iso];
            $__currency[$country_iso] = $description ? $c_text." ($c_symbol)" : $c_symbol;
        }
    }

    return $iso === NULL ? $__currency : (isset($currency_symbol[$iso]) ? $currency_symbol[$iso] : NULL);
}


/**
 * Check if a given theme exists and is valid
 *
 * @global string[] $settings
 *
 * @param string    $theme
 *
 * @return boolean
 */
function theme_exists($theme) {
    if ($theme == "Default") {
        $theme = fusion_get_settings('theme');
    }

    return is_string($theme) and preg_match("/^([a-z0-9_-]){2,50}$/i",
            $theme) and file_exists(THEMES.$theme."/theme.php") and file_exists(THEMES.$theme."/styles.css");
}

/**
 * Set a valid theme
 *
 * @global string[] $settings
 * @global array    $locale
 *
 * @param string    $theme
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

        addNotice('danger', "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n".$no_theme_message);

    } else {

        echo "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n";

        echo $no_theme_message;

        die();

    }
}

/**
 * Set the admin password when needed
 *
 * @param $password
 *
 * @return bool
 */
function set_admin_pass($password) {
    return Authenticate::setAdminCookie($password);
}

/**
 * Check if admin password matches userdata
 *
 * @param string $password
 *
 * @return boolean
 */
function check_admin_pass($password) {
    return Authenticate::validateAuthAdmin($password);
}

/**
 * Redirect browser using header or script function
 *
 * @param            $location - Desintation URL
 * @param bool|FALSE $delay    - meta refresh delay
 * @param bool|FALSE $script   - true if you want to redirect via javascript
 *
 * @define STOP_REDIRECT to prevent redirection
 */

function redirect($location, $delay = FALSE, $script = FALSE) {
    //define('STOP_REDIRECT', true);
    //$location = fusion_get_settings('site_seo') && defined('IN_PERMALINK') ? FUSION_ROOT.$location : $location;
    if (!defined('STOP_REDIRECT')) {
        if (isnum($delay)) {
            $ref = "<meta http-equiv='refresh' content='$delay; url=".$location."' />";
            add_to_head($ref);
        } else {
            if ($script == FALSE) {
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

/**
 * Clean URL Function, prevents entities in server globals
 *
 * @param string $url
 *
 * @return string
 */
function cleanurl($url) {
    $bad_entities = array("&", "\"", "'", '\"', "\'", "<", ">", "", "", "*");
    $safe_entities = array("&amp;", "", "", "", "", "", "", "", "", "");

    return str_replace($bad_entities, $safe_entities, $url);
}

/**
 * Strip Input Function, prevents HTML in unwanted places
 *
 * @param string|string[] $text
 *
 * @return string|string[]
 */
function stripinput($text) {
    if (!is_array($text)) {
        return str_replace('\\', '&#092;', htmlspecialchars(stripslash(trim($text)), ENT_QUOTES));
    }
    foreach ($text as $i => $item) {
        $text[$i] = stripinput($item);
    }

    return $text;
}

/**
 * Prevent any possible XSS attacks via $_GET
 *
 * @param string $check_url
 *
 * @return boolean TRUE if the URL is not secure
 */
function stripget($check_url) {
    if (!is_array($check_url)) {
        $check_url = str_replace(array("\"", "\'"), array("", ""), urldecode($check_url));

        return (bool)preg_match("/<[^<>]+>/i", $check_url);
    }
    foreach ($check_url as $value) {
        if (stripget($value)) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * Strip file name
 *
 * @param string $filename
 *
 * @return string
 */
function stripfilename($filename) {
    $patterns = array(
        '/\s+/'              => '_',
        '/[^a-z0-9_-]|^\W/i' => '',
        '/([_-])\1+/'        => '$1'
    );

    return preg_replace(array_keys($patterns), $patterns, strtolower($filename)) ?: (string)time();
}

/**
 * Strip Slash Function, only stripslashes if magic_quotes_gpc is on
 *
 * @param string $text
 *
 * @return string
 */
function stripslash($text) {
    if (QUOTES_GPC) {
        $text = stripslashes($text);
    }

    return $text;
}

/**
 * Add Slash Function, add correct number of slashes depending on quotes_gpc
 *
 * @param string $text
 *
 * @return string
 */
function addslash($text) {
    if (!QUOTES_GPC) {
        $text = addslashes(addslashes($text));
    } else {
        $text = addslashes($text);
    }

    return $text;
}

/**
 * htmlentities is too agressive so we use this function
 *
 * @param string $text
 *
 * @return string
 */
function phpentities($text) {
    return str_replace('\\', '&#092;', htmlspecialchars($text, ENT_QUOTES));
}

/**
 * Trim a line of text to a preferred length
 *
 * @param string $text
 * @param int    $length
 *
 * @return string
 */
function trimlink($text, $length) {
    if (strlen($text) > $length) {
        $text = mb_substr($text, 0, ($length - 3), mb_detect_encoding($text))."...";
    }

    return $text;
}

/**
 * Trim a text to a number of words
 *
 * @param string $text
 * @param int    $limit  The number of words
 * @param string $suffix If $text is longer than $limit, $suffix will be appended.
 *                       Tip: You can pass an html link to the full content.
 *
 * @return string
 */
function fusion_first_words($text, $limit, $suffix = '&hellip;') {
    $text = preg_replace('/[\r\n]+/', '', $text);
    return preg_replace('~^(\s*\w+'.str_repeat('\W+\w+', $limit - 1).'(?(?=[?!:;.])
				[[:punct:]]\s*
		))\b(.+)$~isxu', '$1'.$suffix, strip_tags($text));
}

/**
 * Pure trim function
 *
 * @param string $str
 * @param int    $length
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

/**
 * Validate numeric input
 * Note : Negative numbers are not numbers. Use is_numeric($value) instead.
 *
 * @param            $value
 * @param bool|FALSE $decimal
 *
 * @return bool
 */
function isnum($value, $decimal = FALSE) {
    $float = $decimal ? '(\.{0,1})[0-9]*' : '';

    return !is_array($value) and preg_match("/^[0-9]+".$float."$/", $value);
}

/**
 * Custom preg-match function
 *
 * @param string $expression
 * @param string $value
 *
 * @return boolean FALSE when $value is an array
 */
function preg_check($expression, $value) {
    return !is_array($value) and preg_match($expression, $value);
}

/**
 * Generate a clean Request URI
 *
 * @param string    $request_addition    - 'page=1&amp;ref=2' or array('page' => 1, 'ref' => 2)
 * @param array     $filter_array        - array('aid','page', ref')
 * @param bool|TRUE $keep_filtered       - true to keep filter, false to remove filter from FUSION_REQUEST
 *                                       If remove is true, to remove everything and keep $requests_array and $request
 *                                       addition. If remove is false, to keep everything else except $requests_array
 *
 * @return string
 */
function clean_request($request_addition = '', array $filter_array = array(), $keep_filtered = TRUE) {

    $fusion_query = array();

    if (fusion_get_settings("site_seo") && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
        global $filepath;

        $url['path'] = $filepath;
        if (!empty($_GET)) {
            $fusion_query = $_GET;
        }
    } else {

        $url = ((array)parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI']))) + array(
                'path'  => '',
                'query' => ''
            );

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

        $request_addition_array = array();

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

/**
 * Cache smileys mysql
 *
 * @return array
 */
function cache_smileys() {
    static $smiley_cache = NULL;
    if ($smiley_cache === NULL) {
        $smiley_cache = array();
        $result = dbquery("SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS);
        while ($data = dbarray($result)) {
            $smiley_cache[] = array(
                "smiley_code"  => $data['smiley_code'],
                "smiley_image" => $data['smiley_image'],
                "smiley_text"  => $data['smiley_text']
            );
        }
    }

    return $smiley_cache;
}

/**
 * Parse smiley bbcode
 *
 * @param string $message
 *
 * @return string
 */
function parsesmileys($message) {
    if (!preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message)) {
        foreach (cache_smileys() as $smiley) {
            $smiley_code = preg_quote($smiley['smiley_code'], '#');
            $smiley_image = get_image("smiley_".$smiley['smiley_text']);
            $smiley_image = "<img src='$smiley_image' alt='".$smiley['smiley_text']."' style='vertical-align:middle;' />";
            $message = preg_replace("#{$smiley_code}#s", $smiley_image, $message);
        }
    }
    return $message;
}

/**
 * Show smiley icons in comments, forum and other post pages
 *
 * @param string $textarea The name of the textarea
 * @param string $form     The name of the form
 *
 * @return string
 */
function displaysmileys($textarea, $form = "inputform") {
    $smileys = "";
    $i = 0;
    foreach (cache_smileys() as $smiley) {
        if ($i != 0 && ($i % 10 == 0)) {
            $smileys .= "<br />\n";
        }
        $i++;
        $smileys .= "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' onclick=\"insertText('".$textarea."', '".$smiley['smiley_code']."', '".$form."');\" />\n";
    }
    return $smileys;
}

/**
 * Tag a user by simply just posting his name like @Chan and if found, returns a tooltip.
 *
 * @param $user_name
 *
 * @return mixed
 */
function fusion_parse_user($user_name) {
    $user_regex = '@[-0-9A-Z_\.]{1,50}';
    $text = preg_replace_callback("#$user_regex#i", function ($user_name) {
        $user_name = preg_replace('/[^A-Za-z0-9\-]/', '', $user_name);
        return render_user_tags($user_name);
    }, $user_name);

    return $text;
}

/**
 * Cache all installed bbcode
 *
 * @return array
 */
function cache_bbcode() {
    static $bbcode_cache = array();
    if (empty($bbcode_cache)) {
        $bbcode_cache = array();
        $result = dbquery("SELECT bbcode_name FROM ".DB_BBCODES." ORDER BY bbcode_order ASC");
        while ($data = dbarray($result)) {
            $bbcode_cache[] = $data['bbcode_name'];
        }
    }

    return (array)$bbcode_cache;
}

/**
 * Parse and force image/ to IMAGES directory
 * Neutralize all image dir levels and convert image to pf image folder
 *
 * @param        $data
 * @param string $prefix_
 *
 * @return mixed
 */
function parse_imageDir($data, $prefix_ = "") {
    $str = str_replace("../", "", $data);

    return (string)$prefix_ ? str_replace("images/", $prefix_, $str) : str_replace("images/", IMAGES, $str);
}

/**
 * Interpret output to match input of textarea having both bbcode, html and tinymce buttons
 *
 * @param            $text
 * @param bool|TRUE  $smileys
 * @param bool|TRUE  $bbcode
 * @param bool|TRUE  $decode
 * @param string     $default_image_folder
 * @param bool|FALSE $add_line_breaks
 *
 * @return string
 */
function parse_textarea($text, $smileys = TRUE, $bbcode = TRUE, $decode = TRUE, $default_image_folder = IMAGES, $add_line_breaks = FALSE) {
    $text = $decode == TRUE ? html_entity_decode(stripslashes($text), ENT_QUOTES, fusion_get_locale('charset')) : $text;
    $text = $decode == TRUE ? html_entity_decode($text, ENT_QUOTES, fusion_get_locale('charset')) : $text; // decode for double encoding.
    $text = !empty($default_image_folder) ? parse_imageDir($text, $default_image_folder) : $text;
    $text = $smileys == TRUE ? parsesmileys($text) : $text;
    $text = $bbcode == TRUE ? parseubb($text) : $text;
    $text = fusion_parse_user($text);
    $text = $add_line_breaks ? nl2br($text) : $text;

    return (string)$text;
}

/**
 * Parse bbcode
 *
 * @param        $text
 * @param string $selected - The names of the required bbcodes to parse, separated by "|"
 *
 * @return string
 */
function parseubb($text, $selected = "") {
    $bbcode_cache = cache_bbcode();
    if ($selected) {
        $sel_bbcodes = explode("|", $selected);
    }
    foreach ($bbcode_cache as $bbcode) {
        if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE.LOCALESET."bbcodes/".$bbcode.".php";
        } elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
            $locale_file = LOCALE."English/bbcodes/".$bbcode.".php";
        }
        \PHPFusion\Locale::setLocale($locale_file);
    }
    $locale = fusion_get_locale();

    foreach ($bbcode_cache as $bbcode) {
        if ($selected && in_array($bbcode, $sel_bbcodes)) {
            if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
                include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
            }
        } elseif (!$selected) {
            if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
                include(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
            }
        }
    }

    $text = descript($text, FALSE);

    return $text;
}

/**
 * Javascript email encoder by Tyler Akins
 * Create a "mailto" link for the email address
 *
 * @param string $email
 * @param string $title   The text of the link
 * @param string $subject The subject of the message
 *
 * @return string
 */
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

/**
 * Format spaces and tabs in code bb tags
 *
 * @param string $text
 *
 * @return string
 */
function formatcode($text) {
    $text = str_replace(
        ["  ", "  ", "\t"],
        ["&nbsp; ", " &nbsp;", "&nbsp; &nbsp;"],
        $text
    );
    $text = preg_replace("/^ {1}/m", "&nbsp;", $text);

    return $text;
}

/**
 * Highlights given words in subject
 *
 * @param string $word    The highlighted word
 * @param string $subject The source text
 *
 * @return string
 */
function highlight_words($word, $subject) {
    for ($i = 0, $l = count($word); $i < $l; $i++) {
        $word[$i] = str_replace(array(
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
        ), "", $word[$i]);
        if (!empty($word[$i])) {
            $subject = preg_replace("#($word[$i])(?![^<]*>)#i",
                "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px'>\${1}</span>",
                $subject);
        }
    }

    return $subject;
}

/**
 * This function sanitize news & article submissions
 *
 * @param string  $text
 * @param boolean $striptags FALSE if you don't want to remove html tags. TRUE by default
 *
 * @return string
 */
function descript($text, $striptags = TRUE) {
    // Convert problematic ascii characters to their true values
    $patterns = array(
        '#(&\#x)([0-9A-F]+);*#si'                                                                                                       => '',
        '#(<[^>]+[/\"\'\s])(onmouseover|onmousedown|onmouseup|onmouseout|onmousemove|onclick|ondblclick|onfocus|onload|xmlns)[^>]*>#iU' => '>',
        '#([a-z]*)=([\`\'\"]*)script:#iU'                                                                                               => '$1=$2nojscript...',
        '#([a-z]*)=([\`\'\"]*)javascript:#iU'                                                                                           => '$1=$2nojavascript...',
        '#([a-z]*)=([\'\"]*)vbscript:#iU'                                                                                               => '$1=$2novbscript...',
        '#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU'                                                                             => "$1>",
        '#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU'                                                                              => "$1>"
    );
    foreach (array_merge(array('(', ')', ':'), range('A', 'Z'), range('a', 'z')) as $chr) {
        $patterns["#(&\#)(0*".ord($chr)."+);*#si"] = $chr;
    }
    if ($striptags) {
        do {
            $count = 0;
            $text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i',
                "", $text, -1, $count);
        } while ($count);
    }

    return preg_replace(array_keys($patterns), $patterns, $text);
}

/**
 * Scan image files for malicious code
 *
 * @param string $file
 *
 * @return boolean
 */
function verify_image($file) {
    $txt = file_get_contents($file);
    $patterns = array(
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
    );
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $txt)) {
            return FALSE;
        }
    }

    return TRUE;
}

/**
 * Replace offensive words with the defined replacement word
 *
 * @param string $text
 *
 * @return string
 */
function censorwords($text) {
    $settings['bad_words'] = trim(fusion_get_settings('bad_words'));
    $settings['bad_words_enabled'] = (boolean)fusion_get_settings('bad_words_enabled');
    $settings['bad_word_replace'] = fusion_get_settings('bad_word_replace');
    if ($settings['bad_words_enabled'] && $settings['bad_words']) {
        $words = preg_quote($settings['bad_words'], "/");
        $words = preg_replace("/\\s+/", "|", $words);
        $text = preg_replace("/".$words."/si", $settings['bad_word_replace'], $text);
    }

    return $text;
}

/**
 * Get a user level's name by the numeric code of level
 *
 * @global array $locale
 *
 * @param int    $userlevel
 *
 * @return string
 */
function getuserlevel($userlevel) {
    $locale = fusion_get_locale();
    $userlevels = array(
        -101 => $locale['user1'],
        -102 => $locale['user2'],
        -103 => $locale['user3']
    );

    return isset($userlevels[$userlevel]) ? $userlevels[$userlevel] : NULL;
}

/**
 * Get a user status by the numeric code of the status
 *
 * @global array $locale
 *
 * @param int    $userstatus
 *
 * @return string|NULL NULL if the status does not exist
 */
function getuserstatus($userstatus) {
    $locale = fusion_get_locale();

    return ($userstatus >= 0 and $userstatus <= 8) ? $locale['status'.$userstatus] : NULL;
}

/**
 * Check if Administrator has correct rights assigned
 *
 * @param string $right The code of the right
 *
 * @return boolean
 */
function checkrights($right) {
    if (iADMIN && in_array($right, explode(".", iUSER_RIGHTS))) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Function to redirect on invalid page access.
 *
 * @param      $rights
 * @param bool $debug
 */
function pageAccess($rights, $debug = FALSE) {
    $error = array();
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
 * Check if user is assigned to the specified user group
 *
 * @param int $group
 *
 * @return boolean
 */
function checkgroup($group) {
    if (iSUPERADMIN) {
        return TRUE;
    } elseif (iADMIN && ($group == "0" || $group == "-101" || $group == "-102")) {
        return TRUE;
    } elseif (iMEMBER && ($group == "0" || $group == "-101")) {
        return TRUE;
    } elseif (iGUEST && $group == "0") {
        return TRUE;
    } elseif (iMEMBER && $group && in_array($group, explode(".", iUSER_GROUPS))) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Check access given a user level and user group
 *
 * @param $group
 * @param $user_level
 * @param $user_groups
 *
 * @return bool
 */
function checkusergroup($group, $user_level, $user_groups) {
    if ($user_level == USER_LEVEL_SUPER_ADMIN) {
        return TRUE;
    } elseif ($user_level == USER_LEVEL_ADMIN && ($group == 0 || $group == '-101' || $group == '-102')) {
        return TRUE;
    } elseif ($user_level == USER_LEVEL_MEMBER && ($group == 0 || $group == '-101')) {
        return TRUE;
    } elseif ($user_level == USER_LEVEL_PUBLIC && $group == 0) {
        return TRUE;
    } elseif ($user_level == USER_LEVEL_MEMBER && $group && in_array($group, explode('.', $user_groups))) {
        return TRUE;
    }

    return FALSE;
}

/**
 * Cache groups' data into an array
 *
 * @return array
 */
function cache_groups() {
    static $groups_cache = NULL;
    if ($groups_cache === NULL) {
        $groups_cache = array();
        $result = dbquery("SELECT * FROM ".DB_USER_GROUPS." ORDER BY group_id ASC");
        while ($data = dbarray($result)) {
            $groups_cache[] = $data;
        }
    }

    return $groups_cache;
}

/**
 * Compile access levels & user group array
 *
 * @global array $locale
 * @return array structure of elements: array($levelOrGroupid, $levelnameOrGroupname, $levelGroupDescription,
 *               $levelGroupIcon)
 */
function getusergroups() {
    $locale = fusion_get_locale();
    $groups_array = array(
        array("0", $locale['user0'], $locale['user0'], 'fa fa-user'),
        array("-101", $locale['user1'], $locale['user1'], 'fa fa-user'),
        array("-102", $locale['user2'], $locale['user2'], 'fa fa-user'),
        array("-103", $locale['user3'], $locale['user3'], 'fa fa-user')
    );
    $groups_cache = cache_groups();
    foreach ($groups_cache as $group) {
        $group_icon = !empty($group['group_icon']) ? $group['group_icon'] : '';
        array_push($groups_array, array($group['group_id'], $group['group_name'], $group['group_description'], $group_icon));
    }

    return $groups_array;
}

/**
 * Get the name of the access level or user group
 *
 * @global array  $locale
 *
 * @param int     $group_id
 * @param boolean $return_desc If TRUE, group_description will be returned instead of group_name
 * @param boolean $return_icon If TRUE, group_icon will be returned instead of group_icon group_name
 *
 * @return array
 */
function getgroupname($group_id, $return_desc = FALSE, $return_icon = FALSE) {

    foreach (getusergroups() as $key => $group) {

        if ($group_id == $group[0]) {
            return ($return_desc ? ($group[2] ?: '-') : (!empty($group[3]) && $return_icon ? "<i class='".$group[3]."'></i> " : "").$group[1]);
        }
    }

    return FALSE;
}

/**
 * Get All Groups Arrays
 *
 * @return array
 */
function fusion_get_groups() {
    $visibility_opts = array();
    foreach (getusergroups() as $groups) {
        $visibility_opts[$groups[0]] = $groups[1];
    }

    return $visibility_opts;
}

/**
 * Getting the real users_group access.
 * Return true or false. (BOOLEAN)
 */
function users_groupaccess($field) {
    if (preg_match("(^\.{$field}$|\.{$field}\.|\.{$field}$)", fusion_get_userdata('user_groups'))) {
        return TRUE;
    }

    return FALSE;
}

/**
 * Getting the access levels used when asking the database for data
 *
 * @param string $field
 *
 * @return string The part of WHERE clause. Always returns a condition
 */
function groupaccess($field) {
    $res = '';
    if (iGUEST) {
        $res = $field." = ".USER_LEVEL_PUBLIC;
    } elseif (iSUPERADMIN) {
        $res = "1 = 1";
    } elseif (iADMIN) {
        $res = $field." in (".USER_LEVEL_PUBLIC.", ".USER_LEVEL_MEMBER.", ".USER_LEVEL_ADMIN.")";
    } elseif (iMEMBER) {
        $res = $field." in (".USER_LEVEL_PUBLIC.", ".USER_LEVEL_MEMBER.")";
    }
    if (iUSER_GROUPS != "" && !iSUPERADMIN) {
        $res = "(".$res." OR $field='".str_replace(".", "' OR $field='", iUSER_GROUPS)."')";
    }

    return $res;
}

/**
 * UF blacklist for SQL - same as groupaccess() but $field is the user_id column.
 *
 * @global string[] $userdata
 *
 * @param strig     $field The name of the field
 *
 * @return string It can return an empty condition!
 */
function blacklist($field) {
    $userdata = fusion_get_userdata('user_id');
    $blacklist = array();
    if (in_array('user_blacklist', fieldgenerator(DB_USERS))) {
        $result = dbquery("SELECT user_id, user_level FROM ".DB_USERS." WHERE user_blacklist REGEXP('^\\\.{$userdata['user_id']}$|\\\.{$userdata['user_id']}\\\.|\\\.{$userdata['user_id']}$')");
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                if ($data['user_level'] > -102) {
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

/**
 * check if user was blacklisted by a member
 *
 * @global string[] $userdata
 *
 * @param int       $user_id
 *
 * @return boolean
 */
function user_blacklisted($user_id) {

    return in_array('user_blacklist', fieldgenerator(DB_USERS)) and in_array($user_id, explode('.', fusion_get_userdata('user_blacklist')));
}

/**
 * Create a list of files or folders and store them in an array
 *
 * @param string  $folder
 * @param string  $filter     - The names of the filtered folder separated by "|"
 * @param boolean $sort       - FALSE if you don't want to sort the result. TRUE by default
 * @param string  $type       - possible values: 'files' to list files, 'folders' to list folders
 * @param string  $ext_filter - file extensions separated by "|". Only when $type is 'files'
 *
 * @return array
 */
function makefilelist($folder, $filter, $sort = TRUE, $type = "files", $ext_filter = "") {
    $res = array();
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
    } else {
        $error_log = debug_backtrace()[1];
        $function = (isset($error_log['class']) ? $error_log['class'] : '').(isset($error_log['type']) ? $error_log['type'] : '').(isset($error_log['function']) ? $error_log['function'] : '');
        $error_log = strtr(fusion_get_locale('err_103', LOCALE.LOCALESET.'errors.php'), [
            '{%folder%}'   => $folder,
            '{%function%}' => (!empty($function) ? '<code class=\'m-r-10\'>'.$function.'</code>' : '')
        ]);
        setError(2, $error_log, debug_backtrace()[1]['file'], debug_backtrace()[1]['line'], '');
    }

    return $res;
}

/**
 * Create a selection list from an array created by makefilelist()
 *
 * @param string[] $files
 * @param string   $selected
 *
 * @return string
 */
function makefileopts(array $files, $selected = "") {
    $res = "";
    foreach ($files as $file) {
        $sel = ($selected == $file ? " selected='selected'" : "");
        $res .= "<option value='".$file."'$sel>".$file."</option>\n";
    }

    return $res;
}

/**
 * Making Page Navigation
 *
 * @global array  $locale
 *
 * @param int     $start      The number of the first listed item - $_GET['rowstart']
 * @param int     $count      The number of displayed items - LIMIT on sql
 * @param int     $total      The number of all items - a dbcount of total
 * @param int     $range      The number of links before and after the current page
 * @param string  $link       The base url before the appended part
 * @param string  $getname    the variable name in the query string which stores
 *                            the number of the current page
 * @param boolean $button     Displays as button
 *
 * @return boolean|string FALSE if $count is invalid
 */
function makepagenav($start, $count, $total, $range = 0, $link = "", $getname = "rowstart", $button = FALSE) {

    $locale = fusion_get_locale();
    /* Bootstrap may be disabled in theme (see Gillette for example) without settings change in DB.
       In such case this function will not work properly.
       With this fix (used $settings instead fusion_get_settings) function will work.*/
    if (fusion_get_settings("bootstrap")) {
        $tpl_global = "<nav>%s<div class='btn-group'>\n%s</div></nav>\n";
        $tpl_currpage = "<a class='btn btn-sm btn-default active' href='%s=%d'><strong>%d</strong></a>\n";
        $tpl_page = "<a class='btn btn-sm btn-default' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_divider = "</div>\n<div class='btn-group'>";
        $tpl_firstpage = "<a class='btn btn-sm btn-default' data-value='0' href='%s=0'>1</a>\n";
        $tpl_lastpage = "<a class='btn btn-sm btn-default' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_button = "<a class='btn btn-primary btn-block btn-md' data-value='%d' href='%s=%d'>%s</a>\n";
    } else {
        $tpl_global = "<div class='pagenav'>%s\n%s\n</div>\n";
        $tpl_currpage = "<span><strong>%d</strong></span>";
        $tpl_page = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>";
        $tpl_divider = "...";
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
    $idx_back = $start - $count;
    $idx_next = $start + $count;
    if ($button == TRUE) {
        if ($idx_next >= $total) {
            return sprintf($tpl_button, 0, $link.$getname, 0, $locale['load_end']);
        } else {
            return sprintf($tpl_button, $idx_next, $link.$getname, $idx_next, $locale['load_more']);
        }
    }
    $cur_page = ceil(($start + 1) / $count);
    $res = "";
    if ($idx_back >= 0) {
        if ($cur_page > ($range + 1)) {
            $res .= sprintf($tpl_firstpage, $link.$getname);
            if ($cur_page != ($range + 2)) {
                $res .= $tpl_divider;
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

    return sprintf($tpl_global, "<small class='m-r-10'>".$locale['global_092']." ".$cur_page.$locale['global_093'].$pg_cnt."</small> ", $res);
}

/**
 * @param        $scroll_url    The ajax script that loads the content
 * @param int    $rowstart      Current rowstart - $_GET['rowstart']
 * @param int    $total_count   The total rows - dbrows($result);
 * @param string $getname       Default is 'rowstart'
 * @param string $additional_http_query '&section=some_section'
 *
 * @return string
 */
function infinite_scroll($scroll_url, $rowstart = 0, $total_count, $getname = 'rowstart', $additional_http_query = '') {
    $script = "<script>
    var count = $rowstart+1;
    $(window).scroll(function(){
      if ($(window).scrollTop() == ($(document).height() - $(window).height())) {
        if (count <= '$total_count') {
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
              data: 'action=infinite_scroll&$getname='+ pageNumber +'".($additional_http_query ? "&".$additional_http_query : '')."',
              success: function(html){
                  $('.infiniteLoader').hide();
                  $('#scroll_target').append(html);  // This will be the div where our content will be loaded
              }
          });
      return false;
    }
    </script>";
    add_to_jquery(str_replace(['<script>', '</script>'], '', $script));

    return "
    <div id='scroll_target'></div>
    <div class='infiniteLoader panel panel-default' style='display:none;'><div class='panel-body text-center'>Loading...</div></div>
    ";
}

/**
 * Hierarchy Page Breadcrumbs
 * This function generates breadcrumbs on all your category needs on $_GET['rownav'] as your cat_id
 *
 * @param $tree_index - dbquery_tree(DB_NEWS_CATS, "news_cat_id", "news_cat_parent")
 *                    / tree_index(dbquery_tree_full(DB_NEWS_CATS, "news_cat_id", "news_cat_parent"))
 * @param $tree_full  - dbquery_tree_full(DB_NEWS_CATS, "news_cat_id", "news_cat_parent");
 * @param $id_col     - "news_cat_id",
 * @param $title_col  - "news_cat_name",
 * @param $getname    - cat_id, download_cat_id, news_cat_id, i.e. $_GET['cat_id']
 */

function make_page_breadcrumbs($tree_index, $tree_full, $id_col, $title_col, $getname = "rownav") {

    $_GET[$getname] = !empty($_GET[$getname]) && isnum($_GET[$getname]) ? $_GET[$getname] : 0;

    // Recursive fatal protection
    if (!function_exists('breadcrumb_page_arrays')) {
        function breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, $id) {
            $crumb = &$crumb;
            if (isset($tree_index[get_parent($tree_index, $id)])) {
                $_name = get_parent_array($tree_full, $id);
                $crumb = array(
                    'link'  => isset($_name[$id_col]) ? clean_request($getname."=".$_name[$id_col], array("aid"), TRUE) : "",
                    'title' => isset($_name[$title_col]) ? \PHPFusion\QuantumFields::parse_label($_name[$title_col]) : "",
                );
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

    // then we make a infinity recursive function to loop/break it out.
    $crumb = breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, $_GET[$getname]);
    // then we sort in reverse.
    if (count($crumb['title']) > 1) {
        krsort($crumb['title']);
        krsort($crumb['link']);
    }
    if (count($crumb['title']) > 1) {
        foreach ($crumb['title'] as $i => $value) {
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'][$i], 'title' => $value]);
            if ($i == count($crumb['title']) - 1) {
                OutputHandler::addToTitle($GLOBALS['locale']['global_200'].$value);
                OutputHandler::addToMeta($value);
            }
        }
    } elseif (isset($crumb['title'])) {
        OutputHandler::addToTitle($GLOBALS['locale']['global_200'].$crumb['title']);
        OutputHandler::addToMeta($crumb['title']);
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
    }
}

/**
 * Format the date & time accordingly
 *
 * @global string[] $settings
 * @global string[] $userdata
 *
 * @param           $format     shrtwdate, longdate, forumdate, newsdate or date pattern for the strftime
 * @param           $val        unix timestamp
 * @param array     $options
 *
 * @return string
 */
function showdate($format, $val, $options = array()) {
    $userdata = fusion_get_userdata();
/*    $tz_server = fusion_get_settings("serveroffset");
	if (empty($tz_server)) $tz_server = 'Europe/London';*/
	if (isset($options['tz_override'])) {
		$tz_client = $options['tz_override'];
	} else {
        if (!empty($userdata['user_timezone'])) {
            $tz_client = $userdata['user_timezone'];
        } else {
            $tz_client = fusion_get_settings("timeoffset");
        }
	}
	if (empty($tz_client)) $tz_client = 'Europe/London';
//    $server_dtz = new DateTimeZone($tz_server);
    $client_dtz = new DateTimeZone($tz_client);
//    $server_dt = new DateTime("now", $server_dtz);
    $client_dt = new DateTime("now", $client_dtz);
    //$offset = $client_dtz->getOffset($client_dt) - $server_dtz->getOffset($server_dt);
    $offset = $client_dtz->getOffset($client_dt);

//    if ($format == "shortdate" || $format == "longdate" || $format == "forumdate" || $format == "newsdate") {
	if (in_array($format, array("shortdate", "longdate", "forumdate", "newsdate"))) {
        $format = fusion_get_settings($format);
        $offset = intval($val) + $offset;

        return strftime($format, $offset);
    } else {
        $offset = intval($val) + $offset;

        return strftime($format, $offset);
    }
}

/**
 * Translate bytes into kB, MB, GB or TB by CrappoMan, lelebart fix
 *
 * @global array  $locale
 *
 * @param int     $size   The number of bytes
 * @param int     $digits Precision
 * @param boolean $dir    TRUE if it is the size of a directory
 *
 * @return string
 */
function parsebytesize($size, $digits = 2, $dir = FALSE) {
    $locale = fusion_get_locale();

    $kb = 1024;
    $mb = 1024 * $kb;
    $gb = 1024 * $mb;
    $tb = 1024 * $gb;

    $size = (empty($size)) ? "0" : $size;

    if (($size == 0) && ($dir)) {
        return "0 ".$locale['global_460'];
    } elseif ($size < $kb) {
        return $size.$locale['global_461'];
    } elseif ($size < $mb) {
        return round($size / $kb, $digits).$locale['global_462'];
    } elseif ($size < $gb) {
        return round($size / $mb, $digits).$locale['global_463'];
    } elseif ($size < $tb) {
        return round($size / $gb, $digits).$locale['global_464'];
    } else {
        return round($size / $tb, $digits).$locale['global_465'];
    }
}

/**
 * User profile link
 *
 * @global array    $locale
 * @global string[] $settings
 *
 * @param int       $user_id
 * @param string    $user_name
 * @param int       $user_status
 * @param string    $class html class of link
 *
 * @return string
 */
function profile_link($user_id, $user_name, $user_status, $class = "profile-link", $display_link = TRUE) {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();
    $class = ($class ? " class='$class'" : "");
    if ((in_array($user_status, array(
                0,
                3,
                7
            )) || checkrights("M")) && (iMEMBER || $settings['hide_userprofiles'] == "0") && $display_link == TRUE
    ) {
        $link = "<a href='".BASEDIR."profile.php?lookup=".$user_id."'".$class.">".$user_name."</a>";
    } elseif ($user_status == "5" || $user_status == "6") {
        $link = $locale['user_anonymous'];
    } else {
        $link = $user_name;
    }

    return $link;
}

/**
 * Variable dump printer for debugging purposes
 *
 * @param      $array
 * @param bool $modal
 * @param bool $print
 *
 * @return string
 */
function print_p($array, $modal = FALSE, $print = TRUE) {
    ob_start();
    echo htmlspecialchars(print_r($array, TRUE), ENT_QUOTES, 'utf-8');
    $debug = ob_get_clean();
    if ($modal == TRUE) {
        $modal = openmodal('Debug', 'Debug');
        $modal .= "<pre style='white-space:pre-wrap !important;'>";
        $modal .= $debug;
        $modal .= "</pre>\n";
        $modal .= closemodal();
        PHPFusion\OutputHandler::addToFooter($modal);

        return FALSE;
    }
    if ($print == TRUE) {
        echo "<pre style='white-space:pre-wrap !important;'>";
        echo $debug;
        echo "</pre>\n";
    }

    return $debug;
}

/**
 * Fetch the settings from the database
 *
 * @param string $key The key of one setting
 *
 * @return string[]|string Associative array of settings or one setting by key
 *                    if $key was given
 */
function fusion_get_settings($key = NULL) {
    // It is initialized only once because of 'static'
    static $settings = array();
    if (empty($settings) and defined('DB_SETTINGS') and dbconnection() && db_exists('settings')) {
        $result = dbquery("SELECT * FROM ".DB_SETTINGS);
        while ($data = dbarray($result)) {
            $settings[$data['settings_name']] = $data['settings_value'];
        }
    }

    return $key === NULL ? $settings : (isset($settings[$key]) ? $settings[$key] : NULL);
}

/**
 * Get Locale
 *
 * Fetch a given locale key
 *
 * @param null   $key          - The key of one setting
 * @param string $include_file - The full path of the file which to be included, can be either string or array
 *
 * @return array|null
 */
function fusion_get_locale($key = NULL, $include_file = '') {
    $locale = \PHPFusion\Locale::__getInstance('Default');
    if ($include_file) {
        $locale::setLocale($include_file);
    }

    return $locale->getLocale($key);
}

/**
 * Fetches username
 *
 * @param $user_id
 *
 * @return string
 */
function fusion_get_username($user_id) {
    $result = NULL;
    $result = (dbresult(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='".intval($user_id)."'"), 0));

    return ($result !== NULL) ? $result : fusion_get_locale("na");
}

/**
 * Get a user own data
 *
 * @param $key - The column of one user information
 *
 * @return array|null
 */
function fusion_get_userdata($key = NULL) {
    global $userdata;
    if (empty($userdata)) {
        $userdata = array("user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => 'Default');
    }
    $userdata = $userdata + array(
            "user_id"     => 0,
            "user_name"   => fusion_get_locale("user_guest"),
            "user_status" => 1,
            "user_level"  => 0,
            "user_rights" => "",
            "user_groups" => "",
            "user_theme"  => fusion_get_settings("theme"),
    );

    return $key === NULL ? $userdata : (isset($userdata[$key]) ? $userdata[$key] : $userdata);
}

/**
 * Get any users data
 *
 * @param      $user_id - the user id
 * @param null $key     - the keys of your user id
 *
 * @return mixed
 */
function fusion_get_user($user_id, $key = NULL) {
    global $performance_test;

    static $user = array();
    if (!isset($user[$user_id]) && isnum($user_id)) {
        $user[$user_id] = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".intval($user_id)."'"));
        // check how many times this query is made with the same user.
        $performance_test = $performance_test + 1;
    }

    return $key === NULL ? $user[$user_id] : (isset($user[$user_id][$key]) ? $user[$user_id][$key] : '');
}

/**
 * Get Aidlink
 *
 * @return string
 */
function fusion_get_aidlink() {
    $aidlink = '';
    if (defined('iADMIN') && iADMIN && defined('iAUTH')) {
        $aidlink = '?aid='.iAUTH;
    }

    return (string)$aidlink;
}

/**
 * Fetch PM Settings
 *
 * @param      $user_id
 * @param null $key - user_inbox, user_outbox, user_archive, user_pm_email_notify, user_pm_save_sent
 *
 * @return array|bool|null
 */
function user_pm_settings($user_id, $key = NULL) {
    return \PHPFusion\PrivateMessages::get_pm_settings($user_id, $key);
}

/**
 * Run the installer or halt the script
 */
function fusion_run_installer() {
    if (file_exists("install.php")) {
        redirect("install.php");
    } else {
        die("No config.php or install.php files were found");
    }
}

/*-------------------------
 * Language Handling
 +-------------------------/

/**
 * Define Site Language
 * @param $lang
 */
function define_site_language($lang) {
    if (valid_language($lang)) {
        define('LANGUAGE', $lang);
        define('LOCALESET', $lang.'/');
    }
}

// Set the requested language
function set_language($lang) {
    global $userdata;

    if (valid_language($lang)) {
        if (iMEMBER) {
            dbquery("UPDATE ".DB_USERS." SET user_language='".$lang."' WHERE user_id='".$userdata['user_id']."'");
        } else {
            $rows = dbrows(dbquery("SELECT user_language FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
            if ($rows != 0) {
                dbquery("UPDATE ".DB_LANGUAGE_SESSIONS." SET user_language='".$lang."', user_datestamp='".time()."' WHERE user_ip='".USER_IP."'");
            } else {
                dbquery("INSERT INTO ".DB_LANGUAGE_SESSIONS." (user_ip, user_language, user_datestamp) VALUES ('".USER_IP."', '".$lang."', '".TIME."');");
            }
            // Sanitize guest sessions occasionally
            dbquery("DELETE FROM ".DB_LANGUAGE_SESSIONS." WHERE user_datestamp<'".(TIME - (86400 * 60))."'");
        }
    }
}

/**
 * Check if a given language is valid or if exists
 * Checks whether a language can be found in enabled languages array
 * Can also be used to check whether a language actually exists
 *
 * @param string $lang
 * @param bool   $file_check intended to be used when enabling languages in Admin Panel
 *
 * @return bool
 */
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

/**
 * Create a selection list of possible languages in list
 *
 * @todo rename it from get_available_languages_list to a more proper name
 *
 * @param string $selected_language
 *
 * @return string
 */
function get_available_languages_list($selected_language = "") {
    $enabled_languages = fusion_get_enabled_languages();
    $res = "";
    foreach ($enabled_languages as $language) {
        $sel = ($selected_language == $language ? " selected='selected'" : "");
        $label = str_replace('_', ' ', $language);
        $res .= "<option value='".$language."'$sel>".$label."</option>\n";
    }

    return $res;
}

/**
 * Get Language Switch Arrays
 *
 * @return array
 */
function fusion_get_language_switch() {
    static $language_switch = array();
    if (empty($language_link)) {
        $enabled_languages = fusion_get_enabled_languages();
        foreach ($enabled_languages as $language => $language_name) {
            $link = clean_request('lang='.$language, array('lang'), FALSE);
            $language_switch[$language] = array(
                "language_name"   => $language_name,
                "language_icon_s" => BASEDIR."locale/$language/$language-s.png",
                "language_icon"   => BASEDIR."locale/$language/$language.png",
                "language_link"   => $link,
            );
        }
    }

    return (array)$language_switch;
}

/**
 * Language switcher function
 *
 * @param bool|TRUE $icon
 */
function lang_switcher($icon = TRUE) {
    $locale = fusion_get_locale();
    $enabled_languages = fusion_get_enabled_languages();
    if (count($enabled_languages) <= 1) {
        return;
    }
    openside($locale['global_ML102']);
    echo "<h5><strong>".$locale['UM101']."</strong></h5>\n";
    if ($icon) {
        $language_switch = fusion_get_language_switch();
        if (!empty($language_switch)) {
            $row = 0;
            foreach ($language_switch as $folder => $langData) {
                $icon = "<img class='display-block img-responsive' alt='".$langData['language_name']."' src='".$langData['language_icon']."' title='".$langData['language_name']."' style='min-width:20px;'/>\n";
                if ($folder != LANGUAGE) {
                    $icon = "<a class='side pull-left display-block' href='".$langData['language_link']."'>".$icon."</a>\n ";
                }
                echo(($row > 0 and $row % 4 === 0) ? '<br />' : '');
                echo "<div class='display-inline-block clearfix'>\n".$icon."</div>\n";
                $row++;
            }
        }
    } else {
        include_once INCLUDES."translate_include.php";
        echo openform('lang_menu_form', 'post', FUSION_SELF);
        echo form_select('lang_menu', '', fusion_get_settings('locale'), array("options" => fusion_get_enabled_languages(), "width" => "100%"));
        echo closeform();
        add_to_jquery("
			function showflag(item){
				return '<div class=\"clearfix\" style=\"width:100%; padding-left:10px;\"><img style=\"height:20px; margin-top:3px !important;\" class=\"img-responsive pull-left\" src=\"".LOCALE."' + item.text + '/'+item.text + '-s.png\"/><span class=\"p-l-10\">'+ item.text +'</span></div>';
			}
			$('#lang_menu').select2({
			placeholder: '".$locale['global_ML103']."',
			formatSelection: showflag,
			escapeMarkup: function(m) { return m; },
			formatResult: showflag,
			}).bind('change', function(item) {
				window.location.href = '".FUSION_REQUEST."?lang='+$(this).val();
			});
		");
    }
    closeside();
}


/**
 * Detect whether the system is installed and return the config file path
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
 * Get the array of enabled languages
 *
 * @staticvar string[] $enabled_languages
 * @return string[]
 */
function fusion_get_enabled_languages() {
    $settings = fusion_get_settings();
    static $enabled_languages = NULL;

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

function fusion_get_detected_language() {
    static $detected_languages = NULL;
    if ($detected_languages === NULL) {
        $all_languages = makefilelist(LOCALE, ".svn|.|..", TRUE, "folders");
        foreach ($all_languages as $language_name) {
            $detected_languages[$language_name] = translate_lang_names($language_name);
        }
    }

    return (array)$detected_languages;
}

/**
 * Log user actions
 *
 * @param $user_id
 * @param $column_name - affected column
 * @param $new_value
 * @param $old_value
 *                     Note: Showing $action can be done using $locale in 9.1 via registration in a table
 */
function save_user_log($user_id, $column_name, $new_value, $old_value) {
    $data = array(
        "userlog_id"        => 0,
        "userlog_user_id"   => $user_id,
        "userlog_field"     => $column_name,
        "userlog_value_new" => $new_value,
        "userlog_value_old" => $old_value,
        "userlog_timestamp" => time(),
    );
    dbquery_insert(DB_USER_LOG, $data, "save", array("keep_session" => TRUE));
}

/**
 * Minify JS Code
 *
 * @param $code
 *
 * @return bool|string
 */
function jsminify($code) {
    $min = \PHPFusion\Minifier::minify($code, array('flaggedComments' => false));

    return $min;
}

/**
 * A wrapper function for file_put_contents with cache invalidation
 *
 * If opcache is enabled on the server, this function will write the file
 * as the original file_put_contents and invalidate the cache of the file.
 *
 * It is needed when you create a file dynamically and want to include it
 * before the cache is invalidated. Redirection does not matter.
 *
 * @todo Find a better place and/or name for this function
 *
 * @param string          $file file path
 * @param string|string[] $data
 * @param int             $flags
 *
 * @return int Number of written bytes
 */
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