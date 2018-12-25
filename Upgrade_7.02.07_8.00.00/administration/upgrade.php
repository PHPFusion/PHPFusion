<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
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
require_once "../maincore.php";
if (!checkrights("U") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
if (file_exists(LOCALE.LOCALESET."admin/upgrade.php")) {
	include LOCALE.LOCALESET."admin/upgrade.php";
} else {
	include LOCALE."English/admin/upgrade.php";
}
if (file_exists(LOCALE.LOCALESET."setup.php")) {
	include LOCALE.LOCALESET."setup.php";
} else {
	include LOCALE."English/setup.php";
}

// Some new DB check functions provided in 8, it needs to be decleared here in the upgrade.

if (!function_exists('fieldgenerator')) {
	function fieldgenerator($db) {
		$cresult = dbquery("SHOW COLUMNS FROM $db");
		$col_names = array();
		while ($cdata = dbarray($cresult)) {
			$col_names[] = $cdata['Field'];
		}
		return (array) $col_names;
	}
}

opentable($locale['400']);

echo "<div style='text-align:center' class='text-center' ><br />\n";

if (str_replace(".", "", $settings['version']) < "80000") {
	echo "<form name='upgradeform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
	$content = "";
	if ($settings['maintenance'] == 0) {
		if (isset($_POST['enable_maintenance'])) {
			dbquery("UPDATE ".DB_SETTINGS." SET settings_value='1' WHERE settings_name='maintenance'");
			redirect(FUSION_SELF.$aidlink);
			}
		$content .= "<div class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
		$content .= "<div class='alert alert-warning'>Enable Maintenance before updating the site</div>";
		$content .= "<input class='button btn btn-primary pull-right' type='submit' name='enable_maintenance' value='Enable Maintenance'>";
		$content .= "</div>\n";
	} elseif (isset($_GET['upgrade_ok'])) {
		$content .= "<div class='alert alert-success'>The database upgrade has been completed, you can now copy the files from your PHP-Fusion 8 archive.</div>\n";
	} else {
		switch (filter_input(INPUT_POST, 'stage', FILTER_VALIDATE_INT) ? : 1) {
			case 1:
			$content .= "<div class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
			$content .= "This upgrade procedure can be very demanding depending on how much content your have. <br />Make sure you have a complete backup of your system before you continue<br />";
			$content .= "When the upgrade script is completed you will be redirected back to your maintenance.php page.<br />";
			$content .= "Please see the readme for further instructions in stage 2 once you are there.<br />";
			$content .= "<br /><br /><input type='hidden' name='stage' value='2'>\n";
			$content .= "<input type='submit' name='next' value='Next' class='button btn btn-primary pull-right'><br /><br />\n";
			$content .= "</div>";
			break;
			case 2:
				// Check if $db_driver, SECRET_KEY and SECRET_KEY_SALT are set
					if (!isset($db_driver) || !defined('SECRET_KEY') || !defined('SECRET_KEY_SALT')) {
					// Generate random token keys
					function createRandomToken($length = 32) {
						$chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
						$count = array((strlen($chars[0])-1), (strlen($chars[1])-1));
						$key = "";
						for ($i = 0; $i < $length; $i++) {
							$type = mt_rand(0, 1);
							$key .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
						}
						return $key;
					}

					$secret_key = createRandomToken();
					$secret_key_salt = createRandomToken();

					$content .= "<div class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
					$content .= "Before we can continue you need to edit your <strong>config.php</strong>, insert the following 3 lines right after the line COOKIE_PREFIX : <br />\n";
					$content .= "<code class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
					$content .= "\$db_driver = \"pdo\"; <br />\n";
					$content .= "define(\"SECRET_KEY\", \"".$secret_key."\"); <br />\n";
					$content .= "define(\"SECRET_KEY_SALT\", \"".$secret_key_salt."\"); <br />\n";
					$content .= "</code><br />";
					$content .= "When you have inserted the above lines to your <strong>config.php</strong>, please push Next<br /><br />\n";
					$content .= "<strong>Warning</strong> : If you push Next without copying the above lines in grey to your <strong>config.php</strong> you will need to copy them again. <br /> For each failed refresh a new set will be created for you until your config have been updated as instructed.<br /><br />\n";
					$content .= "<input type='hidden' name='stage' value='2'>\n";
					$content .= "<input type='submit' name='refresh' value='Next' class='button btn btn-primary pull-right' style='margin: 0px auto;'></div><br /><br />\n";
					$content .= "</div>";
					} else {
					$content .= "<div class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
					$content .= "A new .htaccess file will be created with specific settings that are neccessary for PHP-Fusion to run properly<br />
								Please note that any changes previosuly made to .htaccess will be lost.";
					$content .= "<input type='hidden' name='stage' value='3'>\n";
					$content .= "<br /><br /><input type='submit' name='write_htaccess' value='Next' class='button btn btn-primary pull-right'><br /><br />\n";
					$content .= "</div>\n";
					}
				break;
			case 3:
				if (!isset($_POST['write_htaccess'])) {
					$content .= "<div class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
					$content .= "A new .htaccess file will be created with specific settings that are neccessary for PHP-Fusion to run properly<br />
								 Please note that any changes previosuly made to .htaccess will be lost.";
					$content .= "<input type='hidden' name='stage' value='3'>\n";
					$content .= "<br /><input type='submit' name='write_htaccess' value='Continue' class='button btn btn-primary pull-right'><br /><br />\n";
					$content .= "</div>\n";
					break;
				} else {
					// create a new .htaccess file
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
					$htc .= "ErrorDocument 400 ".$settings['site_path']."error.php?code=400".PHP_EOL;
					$htc .= "ErrorDocument 401 ".$settings['site_path']."error.php?code=401".PHP_EOL;
					$htc .= "ErrorDocument 403 ".$settings['site_path']."error.php?code=403".PHP_EOL;
					$htc .= "ErrorDocument 404 ".$settings['site_path']."error.php?code=404".PHP_EOL;
					$htc .= "ErrorDocument 500 ".$settings['site_path']."error.php?code=500".PHP_EOL;
					// Create the .htaccess file
					if (!file_exists(BASEDIR.".htaccess")) {
						if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
							@rename(BASEDIR."_htaccess", BASEDIR.".htaccess");
						} else {
							touch(BASEDIR.".htaccess");
						}
					}
					// Write the contents to .htaccess
					$temp = fopen(BASEDIR.".htaccess", "w");
					if (fwrite($temp, $htc)) {
						fclose($temp);
						echo "<div class='alert alert-success'>The contents of .htaccess were updated</div>";
					}
				}
			case 4:
				$content .= "<div class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
				$content .= "<p class='p-15'>Several changes will be made to the database. <br /> 
				If this procedure timeout or crash you need to manually disable the UTF-8 conversion script or if you can, raise the time of allowed PHP and SQL execution.<br /> 
				You disable the UTF-8 char conversion function by opening /administration/upgrade.php, line 135 change disabled = FALSE to disabled = TRUE</p>\n";
				$content .= "<div class='alert alert-warning'></i>We strongly recommend that you make a <a target='_blank' href='db_backup.php".$aidlink."'>Database Backup</a> before proceeding!</div>\n";
				$disabled = FALSE; // true to disable.
				$content .= "<input type='hidden' name='stage' value='".($disabled ? 5 : 4)."'>\n";
				$content .= "<input type='submit' name='upgrade_database' value='Upgrade Database' class='button btn btn-primary pull-right'><br /><br />\n";
				$content .= "</div>\n";
				if (!$disabled && isset($_POST['upgrade_database'])) {
					
					
		// Force the database to UTF-8 because we'll convert to it
				dbquery("SET NAMES 'utf8'");
		 // If you have a large database this might be hard to run.
			$result = dbquery("SHOW TABLES");
				while($row = dbarray($result)) {
				  foreach ($row as $key => $table) {
					dbquery("ALTER TABLE " . $table . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci");
					$result2 = dbquery("SHOW COLUMNS FROM ".$table);
					// We must change all data like find/replace in columns of broken chars, this may differ for each locales, please complete this list if you know what´s missing.
						while($column = dbarray($result2)) {
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¥','Å')"); 
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¤','Ä')"); 
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field']." ,'Ã¶','Ö')"); 
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'ð', 'ğ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'ý', 'ı')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'þ', 'ş')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ð', 'Ğ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ý', 'İ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Þ', 'Ş')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã‰','É')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€œ','\"')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€','\"')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã‡','Ç')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãƒ','Ã')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¥','Å')"); 
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¤','Ä')"); 
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¶','Ö')"); 
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã ','À')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãº','ú')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€¢','-')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã˜','Ø')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãµ','õ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã­','í')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¢','â')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã£','ã')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ãª','ê')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã¡','á')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã©','é')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã³','ó')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'â€“','–')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã§','ç')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Âª','ª')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Âº','º')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", 'Ã ','à')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ccedil;','ç')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&atilde;','ã')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&aacute;','á')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&acirc;','â')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&eacute;','é')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&iacute;','í')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&otilde;','õ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&uacute;','ú')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ccedil;','ç')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Aacute;','Á')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Acirc;','Â')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Eacute;','É')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Iacute;','Í')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Otilde;','Õ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Uacute;','Ú')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Ccedil;','Ç')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Atilde;','Ã')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Agrave;','À')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Ecirc;','Ê')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Oacute;','Ó')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Ocirc;','Ô')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Uuml;','Ü')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&atilde;','ã')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&agrave;','à')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ecirc;','ê')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&oacute;','ó')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ocirc;','ô')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&uuml;','ü')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&amp;','&')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&gt;','>')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lt;','<')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&circ;','ˆ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&tilde;','˜')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&uml;','¨')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cute;','´')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cedil;','¸')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&quot;','\"')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ldquo;','“')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&rdquo;','”')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lsquo;','‘')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&rsquo;','’')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lsaquo;','‹')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&rsaquo;','›')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&laquo;','«')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&raquo;','»')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ordm;','º')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ordf;','ª')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ndash;','–')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&mdash;','—')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&macr;','¯')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&hellip;','…')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&brvbar;','¦')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&bull;','•')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&para;','¶')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sect;','§')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup1;','¹')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup2;','²')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup3;','³')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frac12;','½')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frac14;','¼')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frac34;','¾')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8539;','⅛')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8540;','⅜')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8541;','⅝')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&#8542;','⅞')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&gt;','>')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lt;','<')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&plusmn;','±')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&minus;','−')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&times;','×')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&divide;','÷')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&lowast;','∗')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&frasl;','⁄')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&permil;','‰')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&int;','∫')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sum;','∑')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&prod;','∏')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&radic;','√')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&infin;','∞')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&asymp;','≈')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cong;','≅')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&prop;','∝')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&equiv;','≡')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ne;','≠')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&le;','≤')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ge;','≥')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&there4;','∴')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sdot;','⋅')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&middot;','·')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&part;','∂')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&image;','ℑ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&real;','ℜ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&prime;','′')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Prime;','″')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&deg;','°')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&ang;','∠')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&perp;','⊥')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&nabla;','∇')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&oplus;','⊕')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&otimes;','⊗')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&alefsym;','ℵ')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&oslash;','ø')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&Oslash;','Ø')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&isin;','∈')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&notin;','∉')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cap;','∩')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&cup;','∪')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sub;','⊂')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sup;','⊃')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&sube;','⊆')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&supe;','⊇')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&exist;','∃')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&forall;','∀')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&empty;','∅')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&not;','¬')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&and;','∧')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&or;','∨')");
							dbquery("UPDATE ".$table." SET ".$column['Field']." = REPLACE(".$column['Field'].", '&crarr;','↵')");
					}
				  }
				}
				
				// Create guests language session tables
				$result = dbquery("CREATE TABLE ".DB_PREFIX."language_sessions (
				user_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
				user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
				user_datestamp INT(10) NOT NULL default '0'   
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci;");

				// Add language tables to infusions and main content
				$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER article_cat_access");
				$result = dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_language VARCHAR(255) NOT NULL DEFAULT '".$settings['locale']."' AFTER page_allow_ratings");
				$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER download_cat_access");
				$result = dbquery("ALTER TABLE ".DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER faq_cat_description");
				$result = dbquery("ALTER TABLE ".DB_FORUM_RANKS." ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER rank_apply");
				$result = dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER forum_merge");
				$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_allow_ratings");
				$result = dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_cat_image");
				$result = dbquery("ALTER TABLE ".DB_PANELS." ADD panel_languages VARCHAR(200) NOT NULL DEFAULT '.".$settings['locale']."' AFTER panel_restriction");
				$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_language varchar(50) NOT NULL default '".$settings['locale']."' AFTER album_datestamp");
				$result = dbquery("ALTER TABLE ".DB_POLLS." ADD poll_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER poll_ended");
				$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER link_order");
				$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."'");
				$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER weblink_cat_access");
				
				// Blog settings
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_image_readmore', '0')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_image_frontpage', '0')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_thumb_ratio', '0')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_image_link', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_w', '400')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_h', '300')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_thumb_w', '100')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_thumb_h', '100')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_max_w', '4800')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_max_h', '4600')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blog_photo_max_b', '9990000')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('blogperpage', '12')");
				
				// Enabled languages array
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('enabled_languages', '".$settings['locale']."')");
				
				// Language settings admin section
				$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'languages.png', '".$locale['129c']."', 'settings_languages.php', '4')");
				
				// Update admin rights
				if ($result) {
					$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
					while ($data = dbarray($result)) {
						$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".LANG' WHERE user_id='".$data['user_id']."'");
					}
				}
				
				// Create multilang tables
				$result = dbquery("CREATE TABLE ".DB_PREFIX."mlt_tables (
				mlt_rights CHAR(4) NOT NULL DEFAULT '',
				mlt_title VARCHAR(50) NOT NULL DEFAULT '',
				mlt_status VARCHAR(50) NOT NULL DEFAULT '',
				PRIMARY KEY (mlt_rights)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
				
				// Add Multilang table rights and status
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('AR', '".$locale['MLT001']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('CP', '".$locale['MLT002']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('BL', '".$locale['MLT014']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('DL', '".$locale['MLT003']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FQ', '".$locale['MLT004']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FO', '".$locale['MLT005']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('FR', '".$locale['MLT013']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('NS', '".$locale['MLT006']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PG', '".$locale['MLT007']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PO', '".$locale['MLT008']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ET', '".$locale['MLT009']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('WL', '".$locale['MLT010']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('SL', '".$locale['MLT011']."', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('PN', '".$locale['MLT012']."', '1')");
				
				// RSS Panel
				$result = dbquery("INSERT INTO ".DB_PREFIX."panels (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list) VALUES ('".$locale['168']."', 'rss_feeds_panel', '', '1', '2', 'file', '0', '0', '1', '')");

				// Blog archives Panel
				$result = dbquery("INSERT INTO ".DB_PREFIX."panels (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_url_list) VALUES ('".$locale['169']."', 'blog_archive_panel', '', '1', '6', 'file', '0', '1', '1', '0', '0', '')");

				// Blog admin sections
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLC', 'blog_cats.png', '".$locale['130a']."', 'blog_cats.php', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('BLOG', 'blog.png', '".$locale['130b']."', 'blog.php', '1')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S13', 'settings_blog.png', '".$locale['130b']."', 'settings_blog.php', '4')");
				
				// Blog link
				$result = dbquery("INSERT INTO ".DB_PREFIX."site_links (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['130b']."', 'blog.php', '0', '2', '0', '3', '".$settings['locale']."')");
				// Admin rights
				if ($result) {
					$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
					while ($data = dbarray($result)) {
						$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".BLOG.BLC.S13' WHERE user_id='".$data['user_id']."'");
					}
				}
		
							$result = dbquery("DROP TABLE IF EXISTS ".DB_PREFIX."blog");
							$result = dbquery("CREATE TABLE ".DB_PREFIX."blog (
							blog_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							blog_subject VARCHAR(200) NOT NULL DEFAULT '',
							blog_image VARCHAR(100) NOT NULL DEFAULT '',
							blog_image_t1 VARCHAR(100) NOT NULL DEFAULT '',
							blog_image_t2 VARCHAR(100) NOT NULL DEFAULT '',
							blog_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
							blog_blog TEXT NOT NULL,
							blog_extended TEXT NOT NULL,
							blog_breaks CHAR(1) NOT NULL DEFAULT '',
							blog_name MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '1',
							blog_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
							blog_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
							blog_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
							blog_visibility TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
							blog_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
							blog_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
							blog_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
							blog_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
							blog_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
							blog_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
							PRIMARY KEY (blog_id),
							KEY blog_datestamp (blog_datestamp),
							KEY blog_reads (blog_reads)
							) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci;");
							if (!$result) {
								$fail = TRUE;
							}
							
							$result = dbquery("DROP TABLE IF EXISTS ".DB_PREFIX."blog_cats");
							$result = dbquery("CREATE TABLE ".DB_PREFIX."blog_cats (
							blog_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
							blog_cat_name VARCHAR(100) NOT NULL DEFAULT '',
							blog_cat_image VARCHAR(100) NOT NULL DEFAULT '',
							blog_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
							PRIMARY KEY (blog_cat_id)
							) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci;");
							if (!$result) {
								$fail = TRUE;
							}
							
				// Populate Blog categoires
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['190']."', 'news.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$settings['locale']."')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."blog_cats (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$settings['locale']."')");
							
				// Email templates admin section
				$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.png', '".$locale['T001']."', 'email.php', '1')");
				
				// Admin rights
				if ($result) {
					$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
					while ($data = dbarray($result)) {
						$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".MAIL' WHERE user_id='".$data['user_id']."'");
					}
				}
				$result = dbquery("CREATE TABLE ".DB_PREFIX."email_templates (
					template_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
					template_key VARCHAR(10) NOT NULL,
					template_format VARCHAR(10) NOT NULL,
					template_active TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
					template_name VARCHAR(300) NOT NULL,
					template_subject TEXT NOT NULL,
					template_content TEXT NOT NULL,
					template_sender_name VARCHAR(30) NOT NULL,
					template_sender_email VARCHAR(100) NOT NULL,			
					template_language VARCHAR(50) NOT NULL,
					PRIMARY KEY (template_id)
				) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
				if ($result) {
					$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'PM', 'html', '0', '".$locale['T101']."', '".$locale['T102']."', '".$locale['T103']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
					$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'POST', 'html', '0', '".$locale['T201']."', '".$locale['T202']."', '".$locale['T203']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
					$result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'CONTACT', 'html', '0', '".$locale['T301']."', '".$locale['T302']."', '".$locale['T303']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");
				}
								
				// SEO tables.
				$result = dbquery("CREATE TABLE ".DB_PREFIX."permalinks_alias (
									alias_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									alias_url VARCHAR(200) NOT NULL DEFAULT '',
									alias_php_url VARCHAR(200) NOT NULL DEFAULT '',
									alias_type VARCHAR(10) NOT NULL DEFAULT '',
									alias_item_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
									PRIMARY KEY (alias_id),
									KEY alias_id (alias_id)
									) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
				$result = dbquery("CREATE TABLE ".DB_PREFIX."permalinks_method (
									pattern_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									pattern_type INT(5) UNSIGNED NOT NULL,
									pattern_source VARCHAR(200) NOT NULL DEFAULT '',
									pattern_target VARCHAR(200) NOT NULL DEFAULT '',
									pattern_cat VARCHAR(10) NOT NULL DEFAULT '',
									PRIMARY KEY (pattern_id)
									) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
				$result = dbquery("CREATE TABLE ".DB_PREFIX."permalinks_rewrites (
									rewrite_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									rewrite_name VARCHAR(50) NOT NULL DEFAULT '',
									PRIMARY KEY (rewrite_id)
									) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
									
				// create admin page for permalinks
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalinks.png', '".$locale['129d']."', 'permalinks.php', '3')");
				
				// upgrade admin rights for permalink admin
				if ($result) {
					$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
					while ($data = dbarray($result)) {
						$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".PL' WHERE user_id='".$data['user_id']."'");
					}
				}

				// site settings for SEO / SEF
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('site_seo', '0')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('normalize_seo', '0')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('debug_seo', '0')");

				// Add file manager to admin
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('FM', 'file_manager.png', '".$locale['130d']."', 'file_manager.php', '1')");
				
				// Add theme settings to admin
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S14', 'settings_theme.png', '".$locale['129f']."', 'settings_theme.php', '4')");
				
				// Add migration tool to admin
				$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MI', 'migration.png', '".$locale['129e']."', 'migrate.php', '2')");
				
				// Update admin rights for migration tool, file manager & theme settings.
				if ($result) {
					$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='103'");
					while ($data = dbarray($result)) {
						$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".S14.MI.FM' WHERE user_id='".$data['user_id']."'");
					}
				}
				
				//Forum's items per page
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('posts_per_page', '20')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('threads_per_page', '20')");

				//Last Post Avatar setting
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('forum_last_post_avatar', '1')");

				// site settings panel exclusions for the new positons
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_aupper', '')");
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_blower', '')");
								
				// Bootstrap, on by default even tho often defined in theme level. 
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('bootstrap', '1')");

				// Entypo, off by default defined on theme level
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('entypo', '0')");
				
				// Entypo, off by default defined on theme level
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('fontawesome', '0')");
				
				// Admin Theme
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('admin_theme', 'Venus')");

				// Set a new default theme for display
				$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Atom-X8' WHERE settings_name='theme'");
				
				// User sig issue
				$result = dbquery("ALTER TABLE ".DB_PREFIX."users CHANGE user_sig user_sig VARCHAR(255) NOT NULL DEFAULT ''");
				
				// Login method feature
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('login_method', '0')");
				
				// Mime check option for upload files
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('mime_check', '0')");
				
				// Gateway check for registration
				$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('gateway', '1')");
				
				// Update admin icons
				$new_icon_array = array(
				"APWR" => "admin_pass.png",
				"AD" => "admins.png",
				"A" => "articles.png",
				"AC" => "article_cats.png",
				"SB" => "banners.png",
				"BB" => "bbcodes.png",
				"B" => "blacklist.png",
				"CP" => "c-pages.png",
				"DB" => "db_backup.png",
				"D" => "dl.png",
				"DC" => "dl_cats.png",
				"ERRO" => "errors.png",
				"FQ" => "faq.png",
				"F" => "forums.png",
				"PH" => "photoalbums.png",
				"IM" => "images.png",
				"I" => "infusions.png",
				"S1" => "settings.png",
				"M" => "members.png",
				"MI" => "migration.png",
				"S6" => "settings_misc.png",
				"N" => "news.png",
				"NC" => "news_cats.png",
				"P" => "panels.png",
				"PL" => "permalinks.png",
				"PI" => "phpinfo.png",
				"PO" => "polls.png",
				"S7" => "settings_pm.png",
				"S4" => "registration.png",
				"ROB" => "robots.png",
				"S12" => "security.png",
				"SL" => "site_links.png",
				"SM" => "smileys.png",
				"TS" => "theme.png",
				"S3" => "settings_forum.png",
				"S2" => "settings_time.png",
				"U" => "upgrade.png",
				"UF" => "user_fields.png",
				"UG" => "user_groups.png",
				"UL" => "user_log.png",
				"S9" => "settings_users.png",
				"W" => "wl.png",
				"WC" => "wl_cats.png",
				"SU" => "submissions.png",
				"FR" => "forum_ranks.png",
				"UFC" => "user_fields_cats.png",
				"S11" => "settings_dl.png",
				"S10" => "settings_ipp.png",
				"S8" => "settings_news.png",
				"S5" => "photoalbums.png",
				);
			
				foreach($new_icon_array as $admin_rights => $icon_file) {
					dbquery("UPDATE ".DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'");
				}
					
				// Update user field cats
				$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_db VARCHAR(100) NOT NULL AFTER field_cat_name");
				$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_index VARCHAR(200) NOT NULL AFTER field_cat_db");
				$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_class VARCHAR(50) NOT NULL AFTER field_cat_index");
				$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_page SMALLINT(1) NOT NULL AFTER field_cat_class");

				//Set the new version
				dbquery("UPDATE ".DB_SETTINGS." SET settings_value='8.00.00' WHERE settings_name='version'");
						
				redirect(FUSION_SELF.$aidlink."&amp;upgrade_ok");
			}
			break;
		}
	}
	echo $content;
	echo "</form>";
} else {
	echo "<br />".$locale['401']."<br /><br />\n";
}
echo "</div>\n";
closetable();
require_once THEMES."templates/footer.php";