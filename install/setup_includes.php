<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: install/setup_includes.php
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

/**
 * Sets the client character set.
 * Note: This function requires MySQL 5.0.7 or later.
 * @see http://www.php.net/mysql-set-charset
 * @param string   $charset - A valid character set name
 * @param resource $link_identifier The MySQL connection
 * @return TRUE on success or FALSE on failure
 */
if (function_exists('mysql_set_charset') === FALSE) {
	function mysql_set_charset($charset, $link_identifier = NULL) {
		if ($link_identifier == NULL) {
			return mysql_query('SET CHARACTER SET "'.$charset.'"');
		} else {
			return mysql_query('SET CHARACTER SET "'.$charset.'"', $link_identifier);
		}
	}
}
// Start of template
function opensetup() {
	global $locale, $fusion_page_head_tags;
	echo "<!DOCTYPE html>\n";
	echo "<head>\n";
	echo "<title>".$locale['setup_0000']."</title>\n";
	echo "<meta charset='".$locale['setup_0012']."' />";
	echo "<link rel='shortcut icon' href='".IMAGES."favicon.ico' type='image/x-icon' />";
	echo "<link rel='stylesheet' href='".THEMES."templates/setup_styles.css' type='text/css' />\n";
	echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
	echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
	echo "<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n";
	echo "<link href='".THEMES."templates/default.css' rel='stylesheet' />\n";
	echo "<link href='".INCLUDES."bootstrap/bootstrap.min.css' rel='stylesheet' />\n";
	echo "<link href='".INCLUDES."font/entypo/entypo.css' rel='stylesheet' />";
	echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
	echo $fusion_page_head_tags;
	echo "</head>\n<body>\n";
	echo "<div class='block-container'>\n";
	echo "<form name='setupform' method='post' action='".FUSION_SELF."'>\n";
	echo "<div class='block'>\n";
	echo "<div class='block-content'>\n";
		echo "<h6><strong>".$locale['setup_0000']."</strong></h6>\n";
		echo "<img class='pf-logo position-absolute' alt='PHP-Fusion' src='".IMAGES."php-fusion-icon.png'/>";
		echo "<p class='text-right mid-opacity'>Version ".$locale['setup_0010']."</p>";

		echo "<div class='row'>\n";
			echo "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>\n";
			$steps = array('1' => $locale['setup_0101'],
		'2' => $locale['setup_0102'],
		'3' => $locale['setup_0103'],
		'4' => $locale['setup_0104'],
		'5' => $locale['setup_0105'],
		'6' => $locale['setup_0106'],
		'7' => $locale['setup_0107']);
			echo "<div class='list-group'>\n";
				foreach ($steps as $arr => $value) {
		if ($arr == 1) {
			$active = (!isset($_POST['step']) || isset($_POST['step']) && $_POST['step'] == $arr) ? 1 : 0;
		} else {
			$active = isset($_POST['step']) && $_POST['step'] == $arr ? 1 : 0;
		}
		echo "<div class='list-group-item ".($active ? 'active' : '')."' style='border:0px;'>".$value."</div>\n";
	}
			echo "</div>\n";
			echo "</div>\n";
			echo "<div class='col-xs-8 col-sm-8 col-md-8 col-lg-8'>\n";
}
// End template
function closesetup() {
	global $fusion_page_footer_tags, $fusion_jquery_tags;
	echo "</div>\n</div>\n"; // end col-8 & row
	echo "</div>\n"; // end block-content
	echo "</div>\n"; // end block
	echo "</form>\n";
	echo "</div>\n";
	echo $fusion_page_footer_tags;
	if (!empty($fusion_jquery_tags)) {
		echo "<script type=\"text/javascript\">\n$(function() {\n";
		echo $fusion_jquery_tags;
		echo "});\n</script>\n";
	}
	echo "</body>\n";
	echo "</html>\n";
}
// Next button
function renderButton($int = FALSE) {
	global $locale;
	$btn_name = 'next';
	if ($int == 1) {
		$locale = $locale['setup_0123'];
	} elseif ($int == '2') {
		$locale = $locale['setup_0120'];
		$btn_name = 'done';
	} else {
		$locale = $locale['setup_0121'];
	}
	echo "<div class='text-right'>\n";
	echo "<button type='submit' name='".$btn_name."' value='$locale' class='btn btn-primary'><i class='entypo right-dir'></i> $locale</button>\n";
	echo "</div>\n";
}

// Redirect Function
function redirect($location, $script = FALSE) {
	if (!$script) {
		header("Location: ".str_replace("&amp;", "&", $location));
		exit;
	} else {
		echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
		exit;
	}
}

// Generate a random string
function createRandomPrefix($length = 5) {
	$chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
	$count = array((strlen($chars[0])-1), (strlen($chars[1])-1));
	$prefix = "";
	for ($i = 0; $i < $length; $i++) {
		$type = mt_rand(0, 1);
		$prefix .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
	}
	return $prefix;
}

// Calculate script start/end time
function get_microtime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec+(float)$sec);
}

// Strip Input Function, prevents HTML in unwanted places
function stripinput($text) {
	if (ini_get('magic_quotes_gpc')) $text = stripslashes($text);
	$search = array("\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;");
	$replace = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
	$text = str_replace($search, $replace, $text);
	return $text;
}

// Validate numeric input
function isnum($value) {
	if (!is_array($value)) {
		return (preg_match("/^[0-9]+$/", $value));
	} else {
		return FALSE;
	}
}

// Create a list of files or folders and store them in an array
function makefilelist($folder, $filter, $sort = TRUE, $type = "files") {
	$res = array();
	$filter = explode("|", $filter);
	$temp = opendir($folder);
	while ($file = readdir($temp)) {
		if ($type == "files" && !in_array($file, $filter)) {
			if (!is_dir($folder.$file)) $res[] = $file;
		} elseif ($type == "folders" && !in_array($file, $filter)) {
			if (is_dir($folder.$file)) $res[] = $file;
		}
	}
	closedir($temp);
	if ($sort) sort($res);
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

// Clean URL Function, prevents entities in server globals
function cleanurl($url) {
	$bad_entities = array("&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*");
	$safe_entities = array("&amp;", "", "", "", "", "", "", "", "", "");
	$url = str_replace($bad_entities, $safe_entities, $url);
	return $url;
}

// Get Current URL
function getCurrentURL() {
	$s = empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.(str_replace(basename(cleanurl($_SERVER['PHP_SELF'])), "", $_SERVER['REQUEST_URI']));
}

function strleft($s1, $s2) {
	return substr($s1, 0, strpos($s1, $s2));
}


//if (isset($db_connect) && $db_connect != false) { mysql_close($db_connect); }
// needed in global locale.. @todo: remove function use in LANGUAGE files..
function hide_email() { }

// Generate a standard .htaccess file
function write_htaccess() {
	global $settings;

	if (!file_exists(BASEDIR.'.htaccess')) {
		if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
			@rename(BASEDIR."_htaccess", ".htaccess");
		} else {
			// create a file.
			$handle = fopen(BASEDIR.".htaccess", "w");
			fclose($handle);
		}
	}
	$htc = "#Force utf-8 charset\r\n";
	$htc .= "AddDefaultCharset utf-8\r\n";
	$htc .= "#Security\r\n";
	$htc .= "ServerSignature Off\r\n";
	$htc .= "#secure htaccess file\r\n";
	$htc .= "<Files .htaccess>\r\n";
	$htc .= "order allow,deny\r\n";
	$htc .= "deny from all\r\n";
	$htc .= "</Files>\r\n";
	$htc .= "#protect config.php\r\n";
	$htc .= "<Files config.php>\r\n";
	$htc .= "order allow,deny\r\n";
	$htc .= "deny from all\r\n";
	$htc .= "</Files>\r\n";
	$htc .= "#Block Nasty Bots\r\n";
	$htc .= "SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT\r\n";
	$htc .= "SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT\r\n";
	$htc .= "Deny from env=HTTP_SAFE_BADBOT\r\n";
	$htc .= "#Disable directory listing\r\n";
	$htc .= "Options -Indexes\r\n";
	$htc .= "ErrorDocument 400 ".$settings['site_path']."error.php?code=400\r\n";
	$htc .= "ErrorDocument 401 ".$settings['site_path']."error.php?code=401\r\n";
	$htc .= "ErrorDocument 403 ".$settings['site_path']."error.php?code=403\r\n";
	$htc .= "ErrorDocument 404 ".$settings['site_path']."error.php?code=404\r\n";
	$htc .= "ErrorDocument 500 ".$settings['site_path']."error.php?code=500\r\n";
	$temp = fopen(BASEDIR.".htaccess", "w");
	if (fwrite($temp, $htc)) {
		fclose($temp);
	}
}
?>