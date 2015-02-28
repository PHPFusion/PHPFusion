<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: phpinfo.php
| Author: Nick Jones (Digitanium)
| Co-Author: Tomasz Jankowski (jantom)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";

if (!checkrights("PI") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
require_once LOCALE.LOCALESET."admin/phpinfo.php";

if (!isset ($_GET['page']) || !isnum($_GET['page'])) {$_GET['page'] = 1;}

//Generating navigation
$navigation = "<table cellpadding='0' cellspacing='1' class='tbl-border' style='text-align:center;width:100%;margin-bottom:1em;'>\n<tr>\n";
$navigation .= "<td class='".($_GET['page']==1?"tbl1":"tbl2")."' style='width:25%'>".($_GET['page']==1?"<strong>":"")."<a href='".FUSION_SELF.$aidlink."&amp;page=1'>".$locale['401']."</a>".($_GET['page']==1?"</strong>":"")."</td>\n";
$navigation .= "<td class='".($_GET['page']==2?"tbl1":"tbl2")."' style='width:25%'>".($_GET['page']==2?"<strong>":"")."<a href='".FUSION_SELF.$aidlink."&amp;page=2'>".$locale['420']."</a>".($_GET['page']==2?"</strong>":"")."</td>\n";
$navigation .= "<td class='".($_GET['page']==3?"tbl1":"tbl2")."' style='width:25%'>".($_GET['page']==3?"<strong>":"")."<a href='".FUSION_SELF.$aidlink."&amp;page=3'>".$locale['440']."</a>".($_GET['page']==3?"</strong>":"")."</td>\n";
$navigation .= "<td class='".($_GET['page']==4?"tbl1":"tbl2")."' style='width:25%'>".($_GET['page']==4?"<strong>":"")."<a href='".FUSION_SELF.$aidlink."&amp;page=4'>".$locale['450']."</a>".($_GET['page']==4?"</strong>":"")."</td>\n";
$navigation .= "</tr></table>\n";
//General info
if ($_GET['page']==1) {
	$phpinfo = "<table cellpadding='0' cellspacing='1' class='tbl-border tab' style='width:100%;' id='folders'>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:20%'>".$locale['402']."</td><td class='tbl2' style='text-align:right'>".php_uname()."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:20%'>".$locale['403']."</td><td class='tbl1' style='text-align:right'>".$_SERVER['SERVER_SOFTWARE']."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:20%'>".$locale['404']."</td><td class='tbl2' style='text-align:right'>".phpversion()."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:20%'>".$locale['405']."</td><td class='tbl1' style='text-align:right'>".php_sapi_name()."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:20%'>".$locale['406']."</td><td class='tbl2' style='text-align:right'>".mysql_get_server_info()."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:20%'>".$locale['407']."</td><td class='tbl1' style='text-align:right'>".$settings['version']."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:20%'>".$locale['408']."</td><td class='tbl2' style='text-align:right'>".DB_PREFIX."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:20%'>".$locale['409']."</td><td class='tbl1' style='text-align:right'>".COOKIE_PREFIX."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:20%'>".$locale['410']."</td><td class='tbl1' style='text-align:right'>".stripinput($_SERVER['HTTP_USER_AGENT'])."</td></tr>\n";
	$phpinfo .= "</table>\n";
} else
//PHP settings
if ($_GET['page']==2) {
	//Check GD version
	if (function_exists('gd_info')) {
		$gd_ver = gd_info();
		preg_match('/[0-9]+.[0-9]+/', $gd_ver['GD Version'], $gd_ver);
	} else {
		$gd_ver = '';
	}
	$phpinfo = "<table cellpadding='0' cellspacing='1' class='tbl-border tab' style='width:100%;' id='folders'>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:50%'>".$locale['423']."</td><td class='tbl2' style='text-align:right'>".(ini_get('safe_mode')?$locale['421']:$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:50%'>".$locale['424']."</td><td class='tbl1' style='text-align:right'>".(ini_get('register_globals')?$locale['421']:$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:50%'>".$locale['425']." GD (".$locale['431'].")</td><td class='tbl2' style='text-align:right'>".(extension_loaded('gd')?$locale['421']." (".$gd_ver[0].")":$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:50%'>".$locale['425']." zlib</td><td class='tbl1' style='text-align:right'>".(extension_loaded('zlib')?$locale['421']:$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:50%'>".$locale['425']." Magic_quotes_gpc</td><td class='tbl2' style='text-align:right'>".(ini_get('magic_quotes_gpc')?$locale['421']:$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:50%'>".$locale['426']."</td><td class='tbl1' style='text-align:right'>".(ini_get('file_uploads')?$locale['421']." (".ini_get('upload_max_filesize')."B)":$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl2' style='width:50%'>".$locale['428']."</td><td class='tbl2' style='text-align:right'>".(ini_get('display_errors')?$locale['421']:$locale['422'])."</td></tr>\n";
	$phpinfo .= "<tr>\n<td class='tbl1' style='width:50%'>".$locale['429']."</td><td class='tbl1' style='text-align:right'>".(ini_get('disable_functions')?ini_get('disable_functions'):$locale['430'])."</td></tr>\n";
	$phpinfo .= "</table>\n";
} else
//folder permissions
if ($_GET['page']==3) {
	$status ='';
	$folders = array(
	//path => have to be writeable or not
	'administration/db_backups/' => true,
	'images/' => true,
	'images/imagelist.js' => true,
	'images/articles/' => true,
	'images/avatars/' => true,
	'images/news/' => true,
	'images/news/thumbs/' => true,
	'images/news_cats/' => true,
	'images/photoalbum/' => true,
	'images/photoalbum/submissions/' => true,
	'forum/attachments/' => true,
	'ftp_upload/' => true,
	'downloads/' => true,
	'downloads/images' => true,
	'robots.txt' => true,
	'config.php' => false
	);
	add_to_head("<style type='text/css'>.passed {color:green;} .failed {color:red; text-transform: uppercase; font-weight:bold;}</style>\n");
	//Check file/folder writeable
	$i = 0;
	foreach ($folders as $folder => $writeable) {
		$row_class = ($i%2 ? "tbl1" : "tbl2");
		$status .= "<tr>\n<td class='".$row_class."' style='width:50%'>".$folder."</td><td class='".$row_class."' style='text-align:right'>";

		if (is_writable(BASEDIR.$folder)==true) {
			$status .= "<span class='".($writeable==true?"passed":"failed")."'>".$locale['441']."</span>";
		} else {
			$status .= "<span class='".($writeable==true?"failed":"passed")."'>".$locale['442']."</span>";
		}
		$status .= " (".substr(sprintf('%o', fileperms(BASEDIR.$folder)), -4).")</td></tr>\n";
		$i++;
	}
	$phpinfo = "<table cellpadding='0' cellspacing='1' class='tbl-border tab' style='width:100%;' id='folders'>\n";
	$phpinfo .= $status;
	$phpinfo .= "</table>\n";
} else
//classic phpinfo
if ($_GET['page']==4) {
	if (!stristr(ini_get('disable_functions'),"phpinfo")) {
		//Generating new phpinfo style, compatible with PHP-Fusion styles
		ob_start();
		$phpinfo = phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();
		$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
		$phpinfo = preg_replace('%<h1.*>.*</h1>%', "<h3 class='tbl2'>$2</h3>", $phpinfo);
		$phpinfo = preg_replace('%<h2><a name="(.*)">(.*)</a></h2>%', "<h4 class='phpinfo forum-caption'>$2</h4>", $phpinfo);
		$phpinfo = preg_replace('%<h2>(.*)</h2>%', "<div class='forum-caption'>$1</div>", $phpinfo);
		$phpinfo = preg_replace('%<th colspan="2">(.*)</th>%', "<th colspan='2'><h5>$1</h5></th>", $phpinfo);
		$phpinfo = str_replace('<table border="0" cellpadding="3" width="600"', "<table class='tbl-border phpinfo_cont' style='width:100%;'", $phpinfo);
		$phpinfo = str_replace("<h3 class='tbl2'></h3>",'',$phpinfo);
		$phpinfo = str_replace('class="h"', "class='tbl2 center'", $phpinfo);
		$phpinfo = str_replace('class="e"', "class='tbl2'", $phpinfo);
		$phpinfo = str_replace('class="v"', "class='tbl1'", $phpinfo);
	} else {
		$phpinfo = "<div class='admin-message'>".$locale['451']."</div>\n";
	}
}

opentable($locale['400']);
echo $navigation;
echo $phpinfo;
closetable();

require_once THEMES."templates/footer.php";
?>