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
if (!defined("IN_FUSION")) { die("Access Denied"); }

define('iMEMBER', false);
require_once __DIR__.'/../includes/autoloader.php';
require_once __DIR__.'/../includes/core_functions_include.php';
require_once __DIR__.'/../includes/core_constants_include.php';
require_once __DIR__."/../includes/sqlhandler.inc.php";
$fusion_page_head_tags = &\PHPFusion\OutputHandler::$pageHeadTags;
$fusion_page_footer_tags = &\PHPFusion\OutputHandler::$pageFooterTags;
$fusion_jquery_tags = &\PHPFusion\OutputHandler::$jqueryTags;
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
	echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
	echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
	echo "<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n";
	echo "<link href='".THEMES."templates/default.css' rel='stylesheet' />\n";
	echo "<link href='".INCLUDES."bootstrap/bootstrap.min.css' rel='stylesheet' />\n";
	echo "<link href='".INCLUDES."font/entypo/entypo.css' rel='stylesheet' />";
	echo $fusion_page_head_tags;
	echo "</head>\n<body>\n";
	echo "<div class='block-container'>\n";
	$form_action = FUSION_SELF."?localeset=".$_GET['localeset'];
	echo "<form name='setupform' method='post' action='$form_action'>\n";
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

/**
 * Render button with custom name and label
 * 
 * @param string $name
 * @param string $label 
 */
function renderButton($name, $label, $mode = 'next') {
	$icon = 'right-dir';
	$btnType = 'btn-primary';
	if ($mode === 'refresh') {
		$icon = 'cw';
	} elseif ($mode === 'tryagain') {
		$btnType = 'btn-warning';
		$icon = 'cw';
	}
	echo "<div class='text-right'>\n";
	echo "<button type='submit' name='".$name."' value='$label' class='btn $btnType m-t-20'><i class='entypo $icon'></i> $label</button>\n";
	echo "</div>\n";
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

// Generate a standard .htaccess file
function write_htaccess($site_path) {
	if (!file_exists(BASEDIR.'.htaccess')) {
		if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
			@rename(BASEDIR."_htaccess", ".htaccess");
		} else {
			touch(BASEDIR.".htaccess");
		}
	}
	$htc = "# Force utf-8 charset".PHP_EOL;
	$htc .= "AddDefaultCharset utf-8".PHP_EOL.PHP_EOL;
	$htc .= "# Security".PHP_EOL;
	$htc .= "ServerSignature Off".PHP_EOL.PHP_EOL;
	$htc .= "# Secure htaccess file".PHP_EOL;
	$htc .= "<Files .htaccess>".PHP_EOL;
	$htc .= "order allow,deny".PHP_EOL;
	$htc .= "deny from all".PHP_EOL;
	$htc .= "</Files>".PHP_EOL.PHP_EOL;
	$htc .= "# Protect config.php".PHP_EOL;
	$htc .= "<Files config.php>".PHP_EOL;
	$htc .= "order allow,deny".PHP_EOL;
	$htc .= "deny from all".PHP_EOL;
	$htc .= "</Files>".PHP_EOL.PHP_EOL;
	$htc .= "# Block Nasty Bots".PHP_EOL;
	$htc .= "<IfModule mod_setenvifno.c>".PHP_EOL;
	$htc .= "	SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT".PHP_EOL;
	$htc .= "	SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT".PHP_EOL;
	$htc .= "	Deny from env=HTTP_SAFE_BADBOT".PHP_EOL;
	$htc .= "</IfModule>".PHP_EOL.PHP_EOL;
	$htc .= "# Disable directory listing".PHP_EOL;
	$htc .= "Options -Indexes".PHP_EOL.PHP_EOL;
	$htc .= "ErrorDocument 400 ".$site_path."error.php?code=400".PHP_EOL;
	$htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
	$htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
	$htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;
	$htc .= "ErrorDocument 500 ".$site_path."error.php?code=500".PHP_EOL;
	file_put_contents(BASEDIR.".htaccess", $htc);
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
 * @param string $file file path
 * @param string|string[] $data
 * @param int $flags
 * @return int Number of written bytes
 */
function fusion_file_put_contents($file, $data, $flags = null) {
	$bytes = null;
	if ($flags === null) {
		$bytes = \file_put_contents($file, $data);
	} else {
		$bytes = \file_put_contents($file, $data, $flags);
	}
	if (function_exists('opcache_invalidate')) {
		\opcache_invalidate($file, TRUE);
	}
	return $bytes;
}

/**
 * @param string $folder
 * @return array
 */
function fusion_load_infusion($folder) {
	$infusion = array();
	$inf_title = "";
	$inf_description = "";
	$inf_version = "";
	$inf_developer = "";
	$inf_email = "";
	$inf_weburl = "";
	$inf_folder = "";
	$inf_newtable = array();
	$inf_insertdbrow = array();
	$inf_droptable = array();
	$inf_altertable = array();
	$inf_deldbrow = array();
	$inf_sitelink = array();
	$inf_adminpanel = array();
	$inf_mlt = array();
	if (is_dir(INFUSIONS.$folder) && file_exists(INFUSIONS.$folder."/infusion.php")) {
		include INFUSIONS.$folder."/infusion.php";
		$infusion = array(
			'name' => str_replace('_', ' ', $inf_title),
			'title' => $inf_title,
			'description' => $inf_description,
			'version' => $inf_version ? : 'beta',
			'developer' => $inf_developer ? : 'PHP-Fusion',
			'email' => $inf_email,
			'url' => $inf_weburl,
			'folder' => $inf_folder,
			'newtable' => $inf_newtable,
			'insertdbrow' => $inf_insertdbrow,
			'droptable' => $inf_droptable,
			'alerttable' => $inf_altertable,
			'deldbrow' => $inf_deldbrow,
			'sitelink' => $inf_sitelink,
			'adminpanel' => $inf_adminpanel,
			'mlt' => $inf_mlt,
		);
		$result = dbquery("SELECT inf_version FROM ".DB_INFUSIONS." WHERE inf_folder=:inf_folder", array(':inf_folder' => $folder));
		$infusion['status'] = dbrows($result)
			? (version_compare($infusion['version'], dbresult($result, 0), ">")
				? 2
				: 1)
			:  0;
	}
	return $infusion;
}