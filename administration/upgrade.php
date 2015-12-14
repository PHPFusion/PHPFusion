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

// Some new DB check functions provided in 9, it needs to be decleared here in the upgrade.

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
echo "<div style='text-align:center'><br />\n";
if (str_replace(".", "", $settings['version']) < "90001") { // 90001 for testing purposes
	echo "<form name='upgradeform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
	$content = "";
	if (isset($_GET['upgrade_ok'])) {
		$content .= "<br />The database was upgraded<br />Remove all your PHP-Fusion 7 Core files.\n";
		$content .= "<br />Remember to save all your images and forum attachments from the images/xxx folders and from forum/attachments, these need to be manually moved.\n";
		$content .= "<br />You will find the new folders under Infusions and the corresponding Infusion you need to re-populate with your files.\n";
	} else {
		switch (filter_input(INPUT_POST, 'stage', FILTER_VALIDATE_INT) ? : 1) {
			case 1:
				// Check if maintainance mode is enabled
				if ($settings['maintenance'] == 0) {
					if (isset($_POST['enable_maintenance'])) {
						dbquery("UPDATE ".DB_SETTINGS." SET settings_value='1' WHERE settings_name='maintenance'");
						echo "<div class='admin-message'>Maintenance mode was enabled, you can now continue with the upgrade process</div>";
						redirect(FUSION_SELF.$aidlink);
					}
					$content .= "<div class='admin-message'>Enable Maintenance before updating the site</div>";
					$content .= "<input class='button btn btn-primary' type='submit' name='enable_maintenance' value='Enable Maintenance'>";
				}
				// Check if $pdo_enabled, SECRET_KEY and SECRET_KEY_SALT are set
				// Check config file formatting.
					elseif (!isset($pdo_enabled) || !defined('SECRET_KEY') || !defined('SECRET_KEY_SALT')) {
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

					$content .= "<div style='width:850px; margin:15px auto;' class='tbl center'>\n";
					$content .= "Before we can continue you need to edit your <strong>config.php</strong>, insert the following 3 lines right after the line COOKIE_PREFIX : <br />\n";
					$content .= "<div class='tbl-border' style='margin-top:10px; padding: 5px; text-align:left;'>\n";
					$content .= "\$pdo_enabled = \"0\"; <br />\n";
					$content .= "define(\"SECRET_KEY\", \"".$secret_key."\"); <br />\n";
					$content .= "define(\"SECRET_KEY_SALT\", \"".$secret_key_salt."\"); <br />\n";
					$content .= "</div><br />";
					$content .= "Important : You need to change the \$pdo_enabled = \"0\" to \$pdo_enabled = \"1\" manually in order to enable PDO (Recommended).<br /><br />\n";
					$content .= "When you have inserted the above lines to your <strong>config.php</strong>, please push Next<br /><br />\n";
					$content .= "<strong>Warning</strong> : If you push Next without copying the above lines in grey to your <strong>config.php</strong> you will need to copy them again. <br /> For each failed refresh a new set will be created for you until your config have been updated as instructed.<br /><br />\n";
					$content .= "<input type='submit' name='refresh' value='Next' class='button btn btn-primary' style='margin: 0px auto;'></div><br /><br />\n";
					} else {
					$content .= "<div style='width:850px; margin:15px auto;' class='tbl center'>\n";
					$content .= "A new .htaccess file will be created with specific settings that are neccessary for PHP-Fusion to run properly<br />
					Please note that any changes previosuly made to .htaccess will be lost.";
					$content .= "</div>\n";
					$content .= "<input type='hidden' name='stage' value='2'>\n";
					$content .= "<input type='submit' name='write_htaccess' value='Continue' class='button btn btn-primary'><br /><br />\n";
					}
				break;
			case 2:
				if (!isset($_POST['write_htaccess'])) {
					$content .= "<div style='width:850px; margin:15px auto;' class='tbl center'>\n";
					$content .= "A new .htaccess file will be created with specific settings that are neccessary for PHP-Fusion to run properly<br />
				Please note that any changes previosuly made to .htaccess will be lost.";
					$content .= "</div>\n";
					$content .= "<input type='hidden' name='stage' value='2'>\n";
					$content .= "<input type='submit' name='write_htaccess' value='Continue' class='button btn btn-primary'><br /><br />\n";
					break;
				} else {
					// Wipe out all .htaccess rewrite rules and add error handler only
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
					$temp = fopen(BASEDIR.".htaccess", "w");
					if (fwrite($temp, $htc)) {
						fclose($temp);
						echo "<div class='admin-message'>The contents of .htaccess were updated</div>";
					}
				}
			case 3:
				$content .= "<div class='admin-message'>\n";
				$content .= "<p>Several changes will be made to the database.</p>\n";
				$content .= "<div class='alert alert-warning'></i>We strongly recommend that you make a <a target='_blank' href='db_backup.php".$aidlink."'>Database Backup</a> before proceeding!</div>\n";
				$content .= "</div>\n";
				$disabled = FALSE; // true to disable.
				$content .= "<input type='hidden' name='stage' value='".($disabled ? 4 : 3)."'>\n";
				$content .= "<input type='submit' name='upgrade_database' value='Upgrade Database' class='button btn btn-primary'><br /><br />\n";
				if (!$disabled && isset($_POST['upgrade_database'])) {
					// @todo: upgrade package shall be rolled out automatically
					include "upgrade/upgrade-7.02-9.00.php";
					dbquery("UPDATE ".DB_SETTINGS." SET settings_value='9.00.00' WHERE settings_name='version'");
					echo "<div class='admin-message'>The database was upgraded.</div>";
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