<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

use PHPFusion\Authenticate;

/**
 * Current microtime as float to calculate script start/end time
 * @deprecated since version 9.00, use microtime(TRUE) instead
 * @return float
 */
function get_microtime() {
	return microtime(TRUE);
}

/**
 * check multilang tables
 * @staticvar boolean[] $tables
 * @param string $table Table name
 * @return boolean
 */
function multilang_table($table) {
	static $tables = NULL;
	if ($tables === NULL) {
		$tables = array();
		$result = dbquery("SELECT mlt_rights FROM ".DB_LANGUAGE_TABLES." WHERE mlt_status='1'");
		while ($row = dbarraynum($result)) {
			$tables[$row[0]] = TRUE;
		}
	}
	return isset($tables[$table]);
}

/**
 * Check if a given language is valid or if exists
 * Checks whether a language can be found in enabled languages array
 * Can also be used to check whether a language actually exists
 * @param string $lang
 * @param bool   $file_check intended to be used when enabling languages in Admin Panel
 * @return bool
 */
function valid_language($lang, $file_check = FALSE) {
	$enabled_languages = fusion_get_enabled_languages();
	if (preg_match("/^([a-z0-9_-]){2,50}$/i", $lang) && ($file_check ? file_exists(LOCALE.$lang."/global.php") : in_array($lang, $enabled_languages))) {
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
	dbquery("DELETE FROM ".DB_LANGUAGE_SESSIONS." WHERE user_datestamp<'".(time()-(86400 * 60))."'");
	}
}

/**
 * Check if a given theme exists and is valid
 * @global string[] $settings
 * @param string    $theme
 * @return boolean
 */
function theme_exists($theme) {
	global $settings;
	if ($theme == "Default") {
		$theme = $settings['theme'];
	}
	return is_string($theme) and preg_match("/^([a-z0-9_-]){2,50}$/i", $theme) and file_exists(THEMES.$theme."/theme.php") and file_exists(THEMES.$theme."/styles.css");
}

/**
 * Set a valid theme
 * @global string[] $settings
 * @global array    $locale
 * @param string    $theme
 */
function set_theme($theme) {
	global $settings, $locale;
	if (defined("THEME")) {
		return;
	}
	if (theme_exists($theme)) {
		define("THEME", THEMES.($theme == "Default" ? $settings['theme'] : $theme)."/");
		return;
	}
	foreach (new GlobIterator(THEMES.'*') as $dir) {
		if ($dir->isDir() and theme_exists($dir->getBasename())) {
			define("THEME", $dir->getPathname()."/");
			return;
		}
	}
	// Don't stop if we are in admin panel since we use different themes now
	if (preg_match("/\/administration\//i", $_SERVER['PHP_SELF'])) { 
		addNotice('danger', "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n".$locale['global_301']);
	} else {
		echo "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n";
		echo $locale['global_301'];
		die();
	}
}

/**
 * Create a selection list of possible languages in list
 * @todo rename it from get_available_languages_list to a more proper name
 * @param string $selected_language
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
 * Create a selection list of possible languages in array
 * @todo rename it from get_available_languages_array to a more proper name
 * @param string[] $language_list
 * @return string
 */
function get_available_languages_array(array $language_list) {
	$enabled_languages = fusion_get_enabled_languages();
	$res = "";
	$template = "<input type='checkbox' value='%s' name='enabled_languages[]' %s> %s <br  />";
	foreach ($language_list as $language) {
		$ischecked = (in_array($language, $enabled_languages) ? "checked='checked'" : "");
		$label = str_replace('_', ' ', $language);
		$res .= sprintf($template, $language, $ischecked, $label);
	}
	return $res;
}

/**
 * Language switcher function
 */
function lang_switcher() {
global $settings;

$enabled_languages = array_keys(fusion_get_enabled_languages());
	if (count($enabled_languages) <= 1) {
		return;
	}
	
	$link_prefix = FUSION_REQUEST.(stristr(FUSION_REQUEST, '?') ? '&amp;' : "?").'lang=';
	$settings['site_seo'] ? $link_prefix = str_replace($settings['site_path'], "", $link_prefix) : $link_prefix = $link_prefix;
		
	foreach ($enabled_languages as $row => $language) {
		$lang_text = translate_lang_names($language);
		$icon = "<img class='display-block img-responsive' alt='".$language."' src='".LOCALE.$language."/".$language.".png' alt='' title='".$lang_text."' style='min-width:20px;'>";
		if ($language != LANGUAGE) {
			$icon = "<a class='side pull-left display-block' href='".$link_prefix.$language."'>".$icon."</a>\n ";
		}
		echo(($row > 0 and $row%4 === 0) ? '<br />' : '');
		echo "<div class='display-inline-block clearfix'>\n".$icon."</div>\n";
	}
}

/**
 * Set the admin password when needed
 * used at administration/login.php
 * @param string $password
 */
function set_admin_pass($password) {
	return Authenticate::setAdminCookie($password);
}

/**
 * Check if admin password matches userdata
 * @param string $password
 * @return boolean
 */
function check_admin_pass($password) {
	return Authenticate::validateAuthAdmin($password);
}

/**
 * Redirect browser using header or script function
 * @param string  $location Destination URL
 * @param boolean $script   TRUE if you want to redirect via javascript
 * @param boolean $debug 	TRUE if you want to see location line that redirect happens
 */
function redirect($location, $script = FALSE, $debug=false) {
	if (!$debug) {
		if (!$script) {
			header("Location: ".str_replace("&amp;", "&", $location));
			exit;
		} else {
			echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
			exit;
		}
	} else {
		debug_print_backtrace();
		echo 'redirected to '.$location;
	}


}

/**
 * Clean URL Function, prevents entities in server globals
 * @param string $url
 * @return string
 */
function cleanurl($url) {
	$bad_entities = array("&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*");
	$safe_entities = array("&amp;", "", "", "", "", "", "", "", "", "");
	return str_replace($bad_entities, $safe_entities, $url);
}

/**
 * Strip Input Function, prevents HTML in unwanted places
 * @param string|string[] $text
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
 * @param string $check_url
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
 * @param string $filename
 * @return string
 */
function stripfilename($filename) {
	$patterns = array('/\s+/' => '_',
		'/[^a-z0-9_-]|^\W/i' => '',
		'/([_-])\1+/' => '$1');
	return preg_replace(array_keys($patterns), $patterns, strtolower($filename)) ? : (string)time();
}

/**
 * Strip Slash Function, only stripslashes if magic_quotes_gpc is on
 * @param string $text
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
 * @param string $text
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
 * @param string $text
 * @return string
 */
function phpentities($text) {
	return str_replace('\\', '&#092;', htmlspecialchars($text, ENT_QUOTES));
}

/**
 * Trim a line of text to a preferred length
 * @param string $text
 * @param int    $length
 * @return string
 */
function trimlink($text, $length) {
	$dec = array("&", "\"", "'", "\\", '\"', "\'", "<", ">");
	$enc = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;");
	$text = str_replace($enc, $dec, $text);
	if (strlen($text) > $length) $text = mb_substr($text, 0, ($length-3), mb_detect_encoding($text))."...";
	$text = str_replace($dec, $enc, $text);
	return $text;
}

/**
 * Trim a text to a number of words
 * @param string $text
 * @param int    $limit  The number of words
 * @param string $suffix If $text is longer than $limit, $suffix will be appended.
 *                       Tip: You can pass an html link to the full content.
 * @return string
 */
function fusion_first_words($text, $limit, $suffix = '&hellip;') {
	return preg_replace('~^(\s*\w+'.str_repeat('\W+\w+', $limit-1).'(?(?=[?!:;.])
				[[:punct:]]\s*
		))\b(.+)$~isxu', '$1'.$suffix, $text);
}

/**
 * Pure trim function
 * @param string $str
 * @param int    $length
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
 * @param string|number $value   The first character must not be + nor -
 * @return boolean
 */
function isnum($value, $decimal = false) {
	$float = $decimal ? '(\.{0,1})[0-9]*' : '';
	return !is_array($value) and preg_match("/^[0-9]+".$float."$/", $value);
}

/**
 * Custom preg-match function
 * @param string $expression
 * @param string $value
 * @return boolean FALSE when $value is an array
 */
function preg_check($expression, $value) {
	return !is_array($value) and preg_match($expression, $value);
}

/**
 * @param string|array $request_addition - 'page=1&amp;ref=2' or array('page' => 1, 'ref' => 2)
 * @param array $filter_array - array('aid','page', ref')
 * @param bool $keep_filtered - true to keep filter, false to remove filter from FUSION_REQUEST
 *                                If remove is true, to remove everything and keep $requests_array and $request addition.
 *                                If remove is false, to keep everything else except $requests_array
 * @param string $separator
 * @return string
 */
function clean_request($request_addition = '', array $filter_array = array(), $keep_filtered = TRUE, $separator = '&') {
	$url = ((array) parse_url(htmlspecialchars_decode(FUSION_REQUEST))) + array(
			'path' => '',
			'query' => ''
		);
	$fusion_query = array();
	if ($url['query']) {
		parse_str($url['query'], $fusion_query); // this is original.
	}
	$fusion_query = $keep_filtered
		? // to remove everything except specified in $filter_array
		array_intersect_key($fusion_query, array_flip($filter_array))
		: // to keep everything except specified in $filter_array
		array_diff_key($fusion_query, array_flip($filter_array));

	if ($request_addition) {
		$request_addition_array = array();
		if (is_array($request_addition)) {
			$fusion_query = $request_addition + $fusion_query;
		} else {
			parse_str($request_addition, $request_addition_array);
			$fusion_query = $request_addition_array + $fusion_query;
		}
	}

	$prefix = $fusion_query ? '?' : '';
	$new_url = $url['path'].$prefix.http_build_query($fusion_query, null, $separator);
	return $new_url;
}

/**
 * Cache smileys mysql
 * @return array
 */
function cache_smileys() {
	static $smiley_cache = NULL;
	if ($smiley_cache === NULL) {
		$smiley_cache = array();
		$result = dbquery("SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS);
		while ($data = dbarray($result)) {
			$smiley_cache[] = array("smiley_code" => $data['smiley_code'],
				"smiley_image" => $data['smiley_image'],
				"smiley_text" => $data['smiley_text']);
		}
	}
	return $smiley_cache;
}

/**
 * Parse smiley bbcode
 * @param string $message
 * @return string
 */
function parsesmileys($message) {
	if (!preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message)) {
		foreach (cache_smileys() as $smiley) {
			$smiley_code = preg_quote($smiley['smiley_code'], '#');
			$smiley_image = "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' style='vertical-align:middle;' />";
			$message = preg_replace("#{$smiley_code}#si", $smiley_image, $message);
		}
	}
	return parseUser($message);
}

/**
 * Show smiley icons in comments, forum and other post pages
 * @param string $textarea The name of the textarea
 * @param string $form     The name of the form
 * @return string
 */
function displaysmileys($textarea, $form = "inputform") {
	$smileys = "";
	$i = 0;
	foreach (cache_smileys() as $smiley) {
		if ($i != 0 && ($i%10 == 0)) {
			$smileys .= "<br />\n";
		}
		$i++;
		$smileys .= "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' onclick=\"insertText('".$textarea."', '".$smiley['smiley_code']."', '".$form."');\" />\n";
	}
	return $smileys;
}

/**
 * Tag a user by simply just posting his name like @hien and if found, returns a tooltip.
 * @param string $user_name
 */
function parseUser($user_name) {
	if (!function_exists('replace_user')) {
		/**
		 * The callback function for parseUser()
		 * @global array $locale
		 * @param string $m The message
		 * @return string
		 */
		function replace_user($m) {
			global $locale;
			add_to_jquery("$('[data-toggle=\"user-tooltip\"]').popover();");
			$user = str_replace('@', '', $m[0]);
			$result = dbquery("SELECT user_id, user_name, user_level, user_status, user_avatar FROM ".DB_USERS." WHERE user_name='".$user."' or user_name='".ucwords($user)."' or user_name='".strtolower($user)."' AND user_status='0' LIMIT 1");
			if (dbrows($result) > 0) {
				$data = dbarray($result);
				$src = ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar'])) ? $src = IMAGES."avatars/".$data['user_avatar'] : IMAGES."avatars/noavatar50.png";
				$title = '<div class="user-tooltip">
				<div class="pull-left m-r-10"><img class="img-responsive" style="max-height:40px; max-width:40px;" src="'.$src.'"></div>
				<div class="overflow-hide">
				<a title="'.sprintf($locale['go_profile'], $data['user_name']).'" " class="strong text-bigger" href="'.BASEDIR.'profile.php?lookup='.$data['user_id'].'">'.$data['user_name'].'</a><br/>
				<span class="text-smaller">'.getuserlevel($data['user_level']).'</span>
				</div>';
				$content = '<a class="btn btn-sm btn-block btn-primary" href="'.BASEDIR.'messages.php?msg_send='.$data['user_id'].'">'.$locale['send_message'].'</a>';
				$html = "<a class='strong pointer' tabindex='0' role='user-profile' data-html='true' data-placement='top' data-toggle='user-tooltip' data-trigger='focus' title='".$title."' data-content='".$content."'>";
				$html .= $m[0];
				$html .= "</a>\n";
				return $html;
			}
		}
	}
	$user_regex = '@[-0-9A-Z_\.]{1,50}';
	$text = preg_replace_callback("#$user_regex#i", 'replace_user', $user_name);
	return $text;
}

/**
 * Cache bbcode mysql
 * @return array
 */
function cache_bbcode() {
	static $bbcode_cache = NULL;
	if ($bbcode_cache === NULL) {
		$bbcode_cache = array();
		$result = dbquery("SELECT bbcode_name FROM ".DB_BBCODES." ORDER BY bbcode_order ASC");
		while ($data = dbarray($result)) {
			$bbcode_cache[] = $data['bbcode_name'];
		}
	}
	return $bbcode_cache;
}

/**
 * Parse bbcode
 * @param string  $text
 * @param boolean $selected The names of the required bbcodes to parse, separated by "|"
 * @return string
 */
function parseubb($text, $selected = "") {
	$bbcode_cache = cache_bbcode();
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
	$text = descript($text, FALSE);
	return $text;
}

/**
 * Javascript email encoder by Tyler Akins
 * Create a "mailto" link for the email address
 * @param string $email
 * @param string $title   The text of the link
 * @param string $subject The subject of the message
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
 * @param string $text
 * @return string
 */
function formatcode($text) {
	$text = str_replace("  ", "&nbsp; ", $text);
	$text = str_replace("  ", " &nbsp;", $text);
	$text = str_replace("\t", "&nbsp; &nbsp;", $text);
	$text = preg_replace("/^ {1}/m", "&nbsp;", $text);
	return $text;
}

/**
 * Highlights given words in subject
 * @param string $word    The highlighted word
 * @param string $subject The source text
 * @return string
 */
function highlight_words($word, $subject) {
	for ($i = 0, $l = count($word); $i < $l; $i++) {
		$word[$i] = str_replace(array("\\",
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
									"_"), "", $word[$i]);
		if (!empty($word[$i])) {
			$subject = preg_replace("#($word[$i])(?![^<]*>)#i", "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px'>\${1}</span>", $subject);
		}
	}
	return $subject;
}

/**
 * This function sanitize news & article submissions
 * @param string  $text
 * @param boolean $striptags FALSE if you don't want to remove html tags. TRUE by default
 * @return string
 */
function descript($text, $striptags = TRUE) {
	// Convert problematic ascii characters to their true values
	$patterns = array('#(&\#x)([0-9A-F]+);*#si' => '',
		'#(<[^>]+[/\"\'\s])(onmouseover|onmousedown|onmouseup|onmouseout|onmousemove|onclick|ondblclick|onfocus|onload|xmlns)[^>]*>#iU' => '>',
		'#([a-z]*)=([\`\'\"]*)script:#iU' => '$1=$2nojscript...',
		'#([a-z]*)=([\`\'\"]*)javascript:#iU' => '$1=$2nojavascript...',
		'#([a-z]*)=([\'\"]*)vbscript:#iU' => '$1=$2novbscript...',
		'#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU' => "$1>",
		'#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU' => "$1>");
	foreach (array_merge(array('(', ')', ':'), range('A', 'Z'), range('a', 'z')) as $chr) {
		$patterns["#(&\#)(0*".ord($chr)."+);*#si"] = $chr;
	}
	if ($striptags) {
		do {
			$count = 0;
			$text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text, -1, $count);
		} while ($count);
	}
	return preg_replace(array_keys($patterns), $patterns, $text);
}

/**
 * Scan image files for malicious code
 * @param string $file
 * @return boolean
 */
function verify_image($file) {
	$txt = file_get_contents($file);
	$patterns = array('#\<\?php#i',
		'#&(quot|lt|gt|nbsp);#i',
		'#&\#x([0-9a-f]+);#i',
		'#&\#([0-9]+);#i',
		"#([a-z]*)=([\`\'\"]*)script:#iU",
		"#([a-z]*)=([\`\'\"]*)javascript:#iU",
		"#([a-z]*)=([\'\"]*)vbscript:#iU",
		"#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU",
		"#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU",
		"#</*(applet|link|style|script|iframe|frame|frameset)[^>]*>#i");
	foreach ($patterns as $pattern) {
		if (preg_match($pattern, $txt)) {
			return FALSE;
		}
	}
	return TRUE;
}

/**
 * Replace offensive words with the defined replacement word
 * @param string $text
 * @return string
 */
function censorwords($text) {
	$settings = fusion_get_settings();
	$settings['bad_words'] = trim($settings['bad_words']);
	if ($settings['bad_words_enabled'] == "1" && $settings['bad_words']) {
		$words = preg_replace("/\s+/", "|", $settings['bad_words']);
		$text = preg_replace("/".$words."/si", $settings['bad_word_replace'], $text);
	}
	return $text;
}

/**
 * Get a user level's name by the numeric code of level
 * @global array $locale
 * @param int    $userlevel
 * @return string
 */
function getuserlevel($userlevel) {
	global $locale;
	$userlevels = array(-101 => $locale['user1'],
		-102 => $locale['user2'],
		-103 => $locale['user3']);
	return isset($userlevels[$userlevel]) ? $userlevels[$userlevel] : NULL;
}

/**
 * Get a user status by the numeric code of the status
 * @global array $locale
 * @param int    $userstatus
 * @return string|NULL NULL if the status does not exist
 */
function getuserstatus($userstatus) {
	global $locale;
	return ($userstatus >= 0 and $userstatus <= 8) ? $locale['status'.$userstatus] : NULL;
}

/**
 * Check if Administrator has correct rights assigned
 * @param string $right The code of the right
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
 * @param int $group
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
 * Cache groups' data into an array
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
 * @global array $locale
 * @return array structure of elements: array($levelOrGroupid, $levelnameOrGroupname)
 */
function getusergroups() {
	global $locale;
	$groups_array = array(array("0", $locale['user0']),
		array("-101", $locale['user1']),
		array("-102", $locale['user2']),
		array("-103", $locale['user3']));
	$groups_cache = cache_groups();
	foreach ($groups_cache as $group) {
		array_push($groups_array, array($group['group_id'], $group['group_name']));
	}
	return $groups_array;
}

/**
 * Get the name of the access level or user group
 * @global array  $locale
 * @param int     $group_id
 * @param boolean $return_desc If TRUE, group_description will be returned instead of group_name
 * @return array
 */
function getgroupname($group_id, $return_desc = FALSE) {
	global $locale;
	$specials = array(0 => 'user0', -101 => 'user1', -102 => 'user2', -103 => 'user3');
	if (isset($specials[$group_id])) {
		return $locale[$specials[$group_id]];
	}
	$groups_cache = cache_groups();
	foreach ($groups_cache as $group) {
		if ($group_id == $group['group_id']) {
			return ($return_desc ? ($group['group_description'] ? : '-') : $group['group_name']);
		}
	}
	return $locale['user_na'];
}

/**
 * Get All Groups Arrays
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
	global $userdata;
	if (preg_match("(^\.{$field}$|\.{$field}\.|\.{$field}$)", $userdata['user_groups'])) {
		return true;
	}
	return false;
}

/**
 * Getting the access levels used when asking the database for data
 * @param string $field
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
 * @global string[] $userdata
 * @param strig     $field The name of the field
 * @return string It can return an empty condition!
 */
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

/**
 * check if user was blacklisted by a member
 * @global string[] $userdata
 * @param int       $user_id
 * @return boolean
 */
function user_blacklisted($user_id) {
	global $userdata;
	return in_array('user_blacklist', fieldgenerator(DB_USERS)) and in_array($user_id, explode('.', $userdata['user_blacklist']));
}

/**
 * Create a list of files or folders and store them in an array
 * @param string $folder
 * @param string $filter     The names of the filtered folder separated by "|"
 * @param string $sort       FALSE if you don't want to sort the result. TRUE by default
 * @param string $type       possible values: 'files' to list files, 'folders' to list folders
 * @param string $ext_filter file extensions separated by "|". Only when $type is 'files'
 * @return array
 */
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

/**
 * Create a selection list from an array created by makefilelist()
 * @param string[] $files
 * @param string   $selected
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
 * @global array $locale
 * @param int    $start       The number of the first listed item - $_GET['rowstart']
 * @param int    $count       The number of displayed items - current displayed item limit
 * @param int    $total       The number of all items - a dbcount of total
 * @param int    $range       The number of links before and after the current page
 * @param string $link        The base url before the appended part
 * @param string $getname     the variable name in the query string which stores
 *                            the number of the current page
 * @return boolean|string FALSE if $count is invalid
 */
function makepagenav($start, $count, $total, $range = 0, $link = "", $getname = "rowstart") {
	global $locale, $settings;

/* Bootstrap may be disabled in theme (see Gillette for example) without settings change in DB.
   In such case this function will not work properly.
   With this fix (used $settings instead fusion_get_settings) function will work.*/
	if (fusion_get_settings("bootstrap")) {
		$tpl_global = "<nav>%s<div class='btn-group'>\n%s</div></nav>\n";
		$tpl_currpage = "<a class='btn btn-sm btn-default active' href=''><strong>%d</strong></a>\n";
		$tpl_page = "<a class='btn btn-sm btn-default' data-value='%d' href='%s=%d'>%s</a>\n";
		$tpl_divider = "</div>\n<div class='btn-group'>";
		$tpl_firstpage = "<a class='btn btn-sm btn-default' data-value='0' href='%s=0'>1</a>\n";
		$tpl_lastpage = "<a class='btn btn-sm btn-default' data-value='%d' href='%s=%d'>%s</a>\n";
	} else {
		$tpl_global = "<div class='pagenav'>%s\n%s\n</div>\n";
		$tpl_currpage = "<span><strong>%d</strong></span>";
		$tpl_page = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>";
		$tpl_divider = "...";
		$tpl_firstpage = "<a class='pagenavlink' data-value='0' href='%s=0'>1</a>";
		$tpl_lastpage = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>\n";
	}
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
	$res = "";
	if ($idx_back >= 0) {
		if ($cur_page > ($range+1)) {
			$res .= sprintf($tpl_firstpage, $link.$getname);
			if ($cur_page != ($range+2)) {
				$res .= $tpl_divider;
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
			$res .= sprintf($tpl_currpage, $i);
		} else {
			$res .= sprintf($tpl_page, $offset_page, $link.$getname, $offset_page, $i);
		}
	}
	if ($idx_next < $total) {
		if ($cur_page < ($pg_cnt-$range)) {
			if ($cur_page != ($pg_cnt-$range-1)) {
				$res .= $tpl_divider;
			}
			$res .= sprintf($tpl_lastpage, ($pg_cnt-1)*$count, $link.$getname, ($pg_cnt-1)*$count, $pg_cnt);
		}
	}
	return sprintf($tpl_global, "<small class='m-r-10'>".$locale['global_092']." ".$cur_page.$locale['global_093'].$pg_cnt."</small> ", $res);
}

/**
 * Format the date & time accordingly
 * @global string[] $settings
 * @global string[] $userdata
 * @param string    $format shrtwdate, longdate, forumdate, newsdate or date pattern for the strftime
 * @param int       $val    unix timestamp
 * @return string
 */
function showdate($format, $val) {
	global $settings, $userdata;
	$tz_server = $settings['serveroffset'];
	if (isset($userdata['user_timezone'])) {
		$tz_client = $userdata['user_timezone'];
	} else {
		$tz_client = $settings['timeoffset'];
	}
	$server_dtz = new DateTimeZone($tz_server);
	$client_dtz = new DateTimeZone($tz_client);
	$server_dt = new DateTime("now", $server_dtz);
	$client_dt = new DateTime("now", $client_dtz);
	$offset = $client_dtz->getOffset($client_dt)-$server_dtz->getOffset($server_dt);
	if ($format == "shortdate" || $format == "longdate" || $format == "forumdate" || $format == "newsdate") {
		return strftime($settings[$format], $val+$offset);
	} else {
		return strftime($format, $val+$offset);
	}
}

/**
 * Translate bytes into kB, MB, GB or TB by CrappoMan, lelebart fix
 * @global array  $locale
 * @param int     $size   The number of bytes
 * @param int     $digits Precision
 * @param boolean $dir    TRUE if it is the size of a directory
 * @return string
 */
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

/**
 * User profile link
 * @global array    $locale
 * @global string[] $settings
 * @param int       $user_id
 * @param string    $user_name
 * @param int       $user_status
 * @param string    $class html class of link
 * @return string
 */
function profile_link($user_id, $user_name, $user_status, $class = "profile-link") {
	global $locale, $settings;
	$class = ($class ? " class='$class'" : "");
	if ((in_array($user_status, array(0,
				3,
				7)) || checkrights("M")) && (iMEMBER || $settings['hide_userprofiles'] == "0")
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
 * Formatted value of a variable to debug
 * @param mixed   $array
 * @param boolean $modal TRUE if you want to render it as a modal dialog
 */
function print_p($array, $modal = FALSE) {
	echo ($modal) ? openmodal('Debug', 'Debug') : '';
	echo "<pre style='white-space:pre-wrap !important;'>";
	echo htmlspecialchars(print_r($array, TRUE), ENT_QUOTES, 'utf-8');
	echo "</pre>";
	echo ($modal) ? closemodal() : '';
}

/**
 * Fetch the settings from the database
 * @param string $key The key of one setting
 * @return string[]|string Associative array of settings or one setting by key
 *                    if $key was given
 */
function fusion_get_settings($key = NULL) {
	// It is initialized only once because of 'static'
	static $settings = array();
	if (empty($settings) and defined('DB_SETTINGS') and dbconnection()) {
		$result = dbquery("SELECT * FROM ".DB_SETTINGS);
		while ($data = dbarray($result)) {
			$settings[$data['settings_name']] = $data['settings_value'];
		}
	}
	return $key === NULL ? $settings : (isset($settings[$key]) ? $settings[$key] : NULL);
}

/**
 * Get path of config.php
 * @param int $max_level
 * @return string|null The relative path of the base directory
 * or NULL if config.php was not found
 */
function fusion_get_relative_path_to_config($max_level = 7) {
	static $config_path = NULL;
	if ($config_path === NULL) {
		$basedir = "";
		$i = 0;
		while ($i <= $max_level and !file_exists($basedir."config.php")) {
			$basedir .= "../";
			$i++;
		}
		$config_path = file_exists($basedir."config.php") ? $basedir."config.php" : NULL;
	}
	return $config_path;
}

/**
 * Run the installer or halt the script
 */
function fusion_run_installer() {
	if (file_exists("install/index.php")) {
		redirect("install/index.php");
	} else {
		die("config.php nor setup.php files were found");
	}
}

/**
 * Detect whether the system is installed and return the config file path
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
 * @staticvar string[] $enabled_languages
 * @return string[]
 */
function fusion_get_enabled_languages() {
	static $enabled_languages = NULL;
	if ($enabled_languages === NULL) {
		$settings = fusion_get_settings();
		$values = explode('.', $settings['enabled_languages']);
		$enabled_languages = array_combine($values, $values);
	}
	return $enabled_languages;
}
