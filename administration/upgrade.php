<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
| Author: Nick Jones (Digitanium)
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
pageAccess("U");
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

$locale['enable_maint_warning'] = "Please put your website into Maintenance mode before continuing in order to avoid any issues that might occur during the upgrade proccess.<br />
					You can either go to <a target='_blank' href='settings_security.php".$aidlink."'>Security Settings</a> and enable it or click the button below.";
$locale['enable_maint'] = "Enable Maintenance";



add_breadcrumb(array('link' => ADMIN.'upgrade.php'.$aidlink, 'title' => $locale['400']));
opentable($locale['400']);
echo "<div style='text-align:center'><br />\n";
if (str_replace(".", "", $settings['version']) < "90001") { // 90001 for testing purposes
	echo openform('upgradeform', 'post', FUSION_SELF.$aidlink);
	$content = "";

	if (isset($_GET['upgrade_ok'])) {
		$content .= "<br />".$locale['502']."<br /><br />\n";
	} else {
		switch (filter_input(INPUT_POST, 'stage', FILTER_VALIDATE_INT) ? : 1) {
			case 1:
				// Check if maintainance mode is enabled
				if (fusion_get_settings("maintenance") == 0) {
					if (isset($_POST['enable_maintenance'])) {
						dbquery("UPDATE ".DB_SETTINGS." SET settings_value='1' WHERE settings_name='maintenance'");
						addNotice('success', 'Maintenance mode was enabled, you can now continue with the upgrade process');
						redirect(FUSION_SELF.$aidlink);
					}
					$content .= "<div class='well'>".$locale['enable_maint_warning']."</div>";
					$content .= "<input class='button btn btn-primary' type='submit' name='enable_maintenance' value='".$locale['enable_maint']."'>";
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
					$content .= "<div class='well'>\n";
					$content .= "Before continuing you need to insert into <strong>config.php</strong> the following lines:<br />\n";
					$content .= "<code class='panel panel-default display-inline-block' style='margin-top:10px; padding: 8px; text-align:left;'>\n";
					$content .= "\$pdo_enabled = \"0\"; <br />\n";
					$content .= "define(\"SECRET_KEY\", \"".$secret_key."\"); <br />\n";
					$content .= "define(\"SECRET_KEY_SALT\", \"".$secret_key_salt."\"); <br />\n";
					$content .= "</code><br />";
					$content .= "Please note that you need to change the <code>\$pdo_enabled = \"0\"</code> to <code>\$pdo_enabled = \"1\"</code> manually in order to enable PDO</div><br />\n";
				}
				else {
					$content .= sprintf($locale['500'], $locale['503'])."<br />\n".$locale['501']."<br /><br />\n";
					$content .= "<input type='hidden' name='stage' value='2'>\n";
					$content .= "<input type='submit' name='upgrade' value='".$locale['400']."' class='button btn btn-primary'><br /><br />\n";
				}
				break;
			case 2:
				// Due to the extreme list of file changes in 9, You should remove the whole directory of files from PHP-Fusion 7, save config.php and upload a new copy of 9.
				$old_files = array(INCLUDES."language/",);
				function rmdir_recursively($dir) {
					foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
						$path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
					}
					rmdir($dir);
					if (!file_exists($dir)) return TRUE;
					return FALSE;
				}

				$files_to_remove = "";
				$files_removed = "";
				foreach ($old_files as $key => $file) {
					if (file_exists($file)) {
						if (isset($_POST['remove_old_files']) && (is_dir($file) ? rmdir_recursively($file) : unlink($file))) {
							$files_removed .= "<li><span style='text-decoration: line-through'>".str_replace('../', '', $file)."</span> [ REMOVED ]</li>\n";
						} else {
							$files_to_remove .= "<li>".str_replace('../', '', $file)."</li>\n";
						}
					}
				}
				if (!empty($files_to_remove)) {
					$content .= "<div class='well'>\n";
					//$content .= "The following files and/or folders that are no longer used in this version will be removed:<br />\n";
					$content .= "Due to the extreme list of file changes in 9, You should remove the whole directory of files from PHP-Fusion 7, save config.php and upload a new copy of 9<br />\n";
					$content .= "<code class='panel panel-default display-inline-block' style='margin-top:10px; padding: 5px; text-align:left;'>\n";
					$content .= "<ol type='1' style='list-style: normal; padding-left: 24px'>\n";
					$content .= $files_to_remove.$files_removed;
					$content .= "</ol>\n";
					$content .= "</code><br />\n";
					$content .= "<input type='hidden' name='stage' value='2'>\n";
					if (isset($_POST['remove_old_files'])) {
						$content .= "Some files or folders could not be removed, please delete them manually or try again.<br />";
					}
					$content .= "<input type='submit' name='remove_old_files' value='Remove Files' class='button btn btn-primary'>\n";
					$content .= "</div>\n";
					break;
				} elseif (isset($_POST['remove_old_files'])) {
					addNotice('success', 'All unnecessary files were removed');
				}
			case 3:
				if (!isset($_POST['write_htaccess'])) {
					$content .= "<div class='well'>\n";
					$content .= "A new .htaccess file will be created with specific settings that are neccessary for PHP-Fusion to run properly<br />
				Please note that any changes previosuly made to .htaccess will be lost.";
					$content .= "</div>\n";
					$content .= "<input type='hidden' name='stage' value='3'>\n";
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
					// Write the contents to .htaccess
					$temp = fopen(BASEDIR.".htaccesss", "w");
					if (fwrite($temp, $htc)) {
						fclose($temp);
						addNotice('success', 'The contents of .htaccess were updated');
					}
					/* else {
										$content .= "<div class='well'>\n";
										$content .= "We weren't able to write the contents required to <strong>.htaccess</strong>,
										please open the file and paste the following contents:<br />";
										$content .= "<code class='panel panel-default display-inline-block' style='text-align: left; white-space: normal'>\n";
										$content .= nl2br(htmlentities($htc, ENT_QUOTES, 'UTF-8' ));
										$content .= "</code>\n";
										$content .= "</div>\n";
										break;
									}*/
				}
			case 4:
				$content .= "<div class='well'>\n";
				$content .= "<p>Several changes will be made to the database.</p>\n";
				$content .= "<div class='alert alert-warning'></i>We strongly recommend that you make a <a target='_blank' href='db_backup.php".$aidlink."'>Database Backup</a> before proceeding!</div>\n";
				$content .= "</div>\n";

				$disabled = FALSE; // true to disable.

				$content .= "<input type='hidden' name='stage' value='".($disabled ? 5 : 4)."'>\n";
				$content .= "<input type='submit' name='upgrade_database' value='Upgrade Database' class='button btn btn-primary'><br /><br />\n";

				if (!$disabled && isset($_POST['upgrade_database'])) {
					// Force the database to UTF-8 because we'll convert to it
					dbquery("SET NAMES 'utf8'");
					$result = dbquery("SHOW TABLES");
					while ($row = dbarray($result)) {
						foreach ($row as $key => $table) {
							dbquery("ALTER TABLE ".$table." CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci");
							$result2 = dbquery("SHOW COLUMNS FROM ".$table);
							// We must change all data like find/replace in columns of broken chars, this may differ for each locales.
							// Please help to complete this list if you know what´s missing with your locale set
							while ($column = dbarray($result2)) {
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

					if (fusion_get_settings("version") < 9.1) {
						// this is for next incoming patch.
					}

					if (fusion_get_settings("version") < 9) {
						/**
						 * Put everything less than 9.0 here.
						 */
						// New access rights need a larger table for users
						$result = dbquery("ALTER TABLE ".DB_USERS." CHANGE user_level user_level TINYINT(4) NOT NULL DEFAULT '-101'");
						// Modify All Users Level > 0
						$result = dbquery("SELECT user_id, user_level FROM ".DB_USERS."");
						if (dbrows($result) > 0) {
							while ($data = dbarray($result)) {
								if ($data['user_level']) { // will omit 0
									dbquery("UPDATE ".DB_USERS." SET user_level ='-".$data['user_level']."' WHERE user_id='".$data['user_id']."' ");
								}
							}
						}
						// Remove dropped rights, these settings have been moved to tabs and follow the Infusions rights
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS."");
						while ($data = dbarray($result)) {
							$new_rights = str_replace(".S13", "", $data['user_rights']);
							$new_rights = str_replace(".S8", "", $new_rights);
							$new_rights = str_replace(".S5", "", $new_rights);
							$new_rights = str_replace(".S11", "", $new_rights);
							dbquery("UPDATE ".DB_USERS." SET user_rights='".$new_rights."' WHERE user_id='".$data['user_id']."'");
						}

						// Change existing link_visibility to new access levels
						$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." CHANGE link_visibility link_visibility CHAR(4) NOT NULL DEFAULT ''");
						$link_result = dbquery("SELECT link_id, link_visibility FROM ".DB_SITE_LINKS."");
						if (dbrows($result) > 0) {
							while ($data = dbarray($result)) {
								if ($data['link_visibility']) {
									dbquery("UPDATE ".DB_SITE_LINKS." SET user_visibility ='-".$data['link_visibility']."' WHERE link_id='".$data['link_id']."' ");
								}
							}
						}

						// Create guest visitors language session tables
						$result = dbquery("
					CREATE TABLE ".DB_PREFIX."language_sessions (
					user_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
					user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."',
					user_datestamp INT(10) NOT NULL default '0'
					) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci;
					");
						// core
						$result = dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_language VARCHAR(255) NOT NULL DEFAULT '".$settings['locale']."' AFTER page_allow_ratings");
						$result = dbquery("ALTER TABLE ".DB_PANELS." ADD panel_languages VARCHAR(200) NOT NULL DEFAULT '.".$settings['locale']."' AFTER panel_restriction");
						$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."'");
						// infusions
						// Add language tables to Infusions and main content if it existed.
						$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER article_cat_access");
						$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER download_cat_access");
						$result = dbquery("ALTER TABLE ".DB_FAQ_CATS." ADD faq_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER faq_cat_description");
						$result = dbquery("ALTER TABLE ".DB_FORUM_RANKS." ADD rank_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER rank_apply");
						$result = dbquery("ALTER TABLE ".DB_FORUMS." ADD forum_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER forum_merge");
						$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_allow_ratings");
						$result = dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER news_cat_image");
						$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_language varchar(50) NOT NULL default '".$settings['locale']."' AFTER album_datestamp");
						$result = dbquery("ALTER TABLE ".DB_POLLS." ADD poll_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER poll_ended");
						$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER link_order");
						$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_language VARCHAR(50) NOT NULL DEFAULT '".$settings['locale']."' AFTER weblink_cat_access");

						// set the new version
						dbquery("UPDATE ".DB_SETTINGS." SET settings_value='9.00.00' WHERE settings_name='version'");
					}

					// Run these regardless of versions -- for now.

					// Option to align news images
					$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_ialign VARCHAR(15) NOT NULL DEFAULT '' AFTER news_image_t2");

					// Gallery v9 upgrade -- do not tackle migrate photos.
					$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER album_description");
					$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_image VARCHAR(200) NOT NULL DEFAULT '' AFTER album_keywords");
					$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_thumb1 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_image");
					$result = dbquery("ALTER TABLE ".DB_PHOTO_ALBUMS." ADD album_thumb2 VARCHAR(200) NOT NULL DEFAULT '' AFTER album_thumb1");

					// Option to use keywords in news
					$result = dbquery("ALTER TABLE ".DB_NEWS." ADD news_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER news_extended");
					// Option to use keywords in downloads
					$result = dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER download_description");
					// Option to use keywords in photos
					$result = dbquery("ALTER TABLE ".DB_PHOTOS." ADD photo_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER photo_description");
					// Option to use keywords in custom_pages
					$result = dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER page_content");
					$result = dbquery("ALTER TABLE ".DB_CUSTOM_PAGES." ADD page_link_cat MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT '0' AFTER page_id");
					// Option to use keywords in articles
					$result = dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_keywords VARCHAR(250) NOT NULL DEFAULT '' AFTER article_article");
					// Login methods
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('login_method', '0')");
					// Mime check for upload files
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('mime_check', '0')");
					// Delete user_offset field an replace it with user_timezone
					$result = dbquery("ALTER TABLE ".DB_USERS." ADD user_timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/London' AFTER user_offset");
					$result = dbquery("ALTER TABLE ".DB_USERS." DROP COLUMN user_offset");
					// Sub Categories for News
					$result = dbquery("ALTER TABLE ".DB_NEWS_CATS." ADD news_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER news_cat_id");
					// Insert new Blog settings if exists - The Blog is not installed by default in this upgrade, these setting are for users that previously had the Blog auto installed in early beta.
					if (db_exists(DB_BLOG)) {
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_image_readmore', '1', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_image_frontpage', '0', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_thumb_ratio', '0', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_image_link', '1', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_photo_w', '400', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_photo_h', '300', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_thumb_w', '100', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_thumb_h', '100', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_photo_max_w', '1800', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_photo_max_h', '1600', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_photo_max_b', '150000', 'blog')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('blog_pagination', '12', 'blog')");
					}
					// News Adjustments
					if (db_exists(DB_NEWS_CATS)) {
						// find if any news category admin link available and remove it. we basically merged NC+N admin.
						$ncArray = dbarray(dbquery("select admin_id, admin_rights from ".DB_ADMIN." WHERE admin_rights='NC'"));
						if (!empty($ncArray)) {
							dbquery_insert(DB_ADMIN, $ncArray, "delete");
						}
					}
					
					//Remove settings_ipp from the Administration
					$result = dbquery("DELETE FROM ".DB_PREFIX."admin WHERE admin_link='settings_ipp.php'");
					
					// Clear old settings if they are there regardless of current state
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_image_readmore'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_image_frontpage'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_thumb_ratio'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_image_link'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_photo_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_photo_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_thumb_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_thumb_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_photo_max_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_photo_max_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_photo_max_b'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='blog_pagination'");

					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='links_per_page'");

					// Insert new weblinks settings
					if (db_exists(DB_WEBLINKS)) {
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('links_per_page', '15', 'weblinks')");
					}

					// Insert new Download settings
					if (db_exists(DB_DOWNLOADS)) {
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_max_b', '512000', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_types', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_b', '150000', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_w', '1024', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screen_max_h', '768', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_screenshot', '1', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_w', '100', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_thumb_max_h', '100', 'downloads')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('download_pagination', '15', 'downloads')");
					}
					// Clear old settings if they are there regardless of current state
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_max_b'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_types'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_b'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screen_max_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_screenshot'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_thumb_max_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_thumb_max_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='download_pagination'");
					// Insert new Forum settings if exists
					if (db_exists(DB_FORUMS)) {
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ips', '-103', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax', '1000000', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachmax_count', '5', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_attachtypes', '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thread_notify', '1', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_ranks', '1', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_lock', '0', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_edit_timelimit', '0', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('popular_threads_timeframe', '604800', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_posts_reply', '1', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_last_post_avatar', '1', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_editpost_to_lastpost', '1', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('threads_per_page', '20', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('posts_per_page', '20', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('numofthreads', '16', 'forum')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('forum_rank_style', '0', 'forum')");
					}
					// Clear old settings if they are there regardless of current state
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_ips'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_attachmax'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_attachmax_count'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_attachtypes'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thread_notify'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_ranks'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_edit_lock'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_edit_timelimit'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='popular_threads_timeframe'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_last_posts_reply'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_last_post_avatar'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_editpost_to_lastpost'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='threads_per_page'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='posts_per_page'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='numofthreads'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='forum_rank_style'");
					// Insert new Gallery settings if exists
					if (db_exists(DB_PHOTO_ALBUMS)) {
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_w', '200', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumb_h', '200', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_w', '400', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_h', '400', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_w', '1800', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_h', '1600', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_max_b', '15120000', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumbs_per_row', '4', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('admin_thumbs_per_row', '6', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('thumbs_per_page', '12', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark', '1', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_image', 'infusions/gallery/albums/watermark.png', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text', '0', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color1', 'FF6600', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color2', 'FFFF00', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_text_color3', 'FFFFFF', 'gallery')");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('photo_watermark_save', '0', 'gallery')");
					}
					// Clear old settings if they are there regardless of current state
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thumb_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thumb_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_max_w'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_max_h'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_max_b'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='thumbs_per_row'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='admin_thumbs_per_row'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_image'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text_color1'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text_color2'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_text_color3'");
					$result = dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='photo_watermark_save'");
					// Moving access level from article categories to articles and create field for subcategories
					$result = dbquery("ALTER TABLE ".DB_ARTICLES." ADD article_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER article_datestamp");
					$result = dbquery("SELECT article_cat_id, article_cat_access FROM ".DB_ARTICLE_CATS);
					if (dbrows($result)) {
						while ($data = dbarray($result)) {
							$result1 = dbquery("UPDATE ".DB_ARTICLES." SET article_visibility='".$data['article_cat_access']."' WHERE article_cat='".$data['article_cat_id']."'");
						}
					}
					$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." DROP COLUMN article_cat_access");
					$result = dbquery("ALTER TABLE ".DB_ARTICLE_CATS." ADD article_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER article_cat_id");
					// Moving access level from downloads categories to downloads and create field for subcategories
					$result = dbquery("ALTER TABLE ".DB_DOWNLOADS." ADD download_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER download_datestamp");
					$result = dbquery("SELECT download_cat_id, download_cat_access FROM ".DB_DOWNLOAD_CATS);
					if (dbrows($result)) {
						while ($data = dbarray($result)) {
							$result1 = dbquery("UPDATE ".DB_DOWNLOADS." SET download_visibility='".$data['download_cat_access']."' WHERE download_cat='".$data['download_cat_id']."'");
						}
					}
					$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." DROP COLUMN download_cat_access");
					$result = dbquery("ALTER TABLE ".DB_DOWNLOAD_CATS." ADD download_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER download_cat_id");
					// Moving access level from weblinks categories to weblinks and create field for subcategories
					$result = dbquery("ALTER TABLE ".DB_WEBLINKS." ADD weblink_visibility CHAR(4) NOT NULL DEFAULT '0' AFTER weblink_datestamp");
					$result = dbquery("SELECT weblink_cat_id, weblink_cat_access FROM ".DB_WEBLINK_CATS);
					if (dbrows($result)) {
						while ($data = dbarray($result)) {
							$result1 = dbquery("UPDATE ".DB_WEBLINKS." SET weblink_visibility='".$data['weblink_cat_access']."' WHERE weblink_cat='".$data['weblink_cat_id']."'");
						}
					}
					$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." DROP COLUMN weblink_cat_access");
					$result = dbquery("ALTER TABLE ".DB_WEBLINK_CATS." ADD weblink_cat_parent MEDIUMINT(8) NOT NULL DEFAULT '0' AFTER weblink_cat_id");
					// Forum tables renaming
					$result = dbquery("RENAME TABLE `".DB_PREFIX."posts` TO `".DB_PREFIX."forum_posts`");
					$result = dbquery("RENAME TABLE `".DB_PREFIX."threads` TO `".DB_PREFIX."forum_threads`");
					$result = dbquery("RENAME TABLE `".DB_PREFIX."thread_notify` TO `".DB_PREFIX."forum_thread_notify`");
					// Site links new admin
					$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_cat MEDIUMINT(9) NOT NULL DEFAULT '0' AFTER link_id");
					$result = dbquery("ALTER TABLE ".DB_SITE_LINKS." ADD link_icon VARCHAR(100) NOT NULL DEFAULT '' AFTER link_url");
					// Enabled languages array
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('enabled_languages', '".$settings['locale']."')");
					// Language settings admin section
					$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'languages.gif', '".$locale['129c']."', 'settings_languages.php', '4')");
					if ($result) {
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
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
					$result = dbquery("INSERT INTO ".DB_PREFIX."mlt_tables (mlt_rights, mlt_title, mlt_status) VALUES ('ES', '".$locale['MLT015']."', '1')");
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
					// Insert shop settings if the old infusion exist
					if (db_exists(DB_ESHOP)) {
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ipn', '0', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cats', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cat_disp', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_nopp', '6', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_noppf', '9', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_target', '_self', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_folderlink', '0', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_selection', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cookies', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_bclines', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_icons', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_statustext', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_closesamelevel', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_inorder', '0', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_shopmode', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_returnpage', 'ordercompleted.php', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ppmail', '', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ipr', '3', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_ratios', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_h', '130', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_w', '100', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_h2', '180', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_idisp_w2', '250', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_catimg_w', '100', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_catimg_h', '100', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_w', '6400', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_h', '6400', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_b', '9999999', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_tw', '150', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_th', '100', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_t2w', '250', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_image_t2h', '250', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_buynow_color', 'blue', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_checkout_color', 'green', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cart_color', 'red', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_addtocart_color', 'magenta', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_info_color', 'orange', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_return_color', 'yellow', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_pretext', '0', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_pretext_w', '190px', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_listprice', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_currency', 'USD', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_shareing', '1', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_weightscale', 'KG', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_vat', '25', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_vat_default', '0', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_terms', '<h2> Ordering </h2><br />\r\nWhilst all efforts are made to ensure accuracy of description, specifications and pricing there may <br />be occasions where errors arise. Should such a situation occur [Company name] cannot accept your order. <br /> In the event of a mistake you will be contacted with a full explanation and a corrected offer. <br />The information displayed is considered as an invitation to treat not as a confirmed offer for sale. \r\nThe contract is confirmed upon supply of goods.\r\n<br /><br /><br />\r\n<h2>Delivery and Returns</h2><br />\r\n[Company name] returns policy has been set up to keep costs down and to make the process as easy for you as possible. You must contact us and be in receipt of a returns authorisation (RA) number before sending any item back. Any product without a RA number will not be refunded. <br /><br /><br />\r\n<h2> Exchange </h2><br />\r\n
					If when you receive your product(s), you are not completely satisfied you may return the items to us, within seven days of exchange or refund. Returns will take approximately 5 working days for the process once the goods have arrived. Items must be in original packaging, in all original boxes, packaging materials, manuals blank warranty cards and all accessories and documents provided by the manufacturer.<br /><br /><br />\r\n\r\nIf our labels are removed from the product â€“ the warranty becomes void.<br /><br /><br />\r\n\r\nWe strongly recommend that you fully insure your package that you are returning. We suggest the use of a carrier that can provide you with a proof of delivery. [Company name] will not be held responsible for items lost or damaged in transit.<br /><br /><br />\r\n\r\nAll shipping back to [Company name] is paid for by the customer. We are unable to refund you postal fees.<br /><br /><br />\r\n\r\nAny product returned found not to be defective can be refunded within the time stated above and will be subject to a 15% restocking fee to cover our administration costs. Goods found to be tampered with by the customer will not be replaced but returned at the customers expense. <br /><br /><br />\r\n\r\n If you are returning items for exchange please be aware that a second charge may apply. <br /><br /><br />\r\n\r\n<h2>Non-Returnable </h2><br />\r\n For reasons of hygiene and public health, refunds/exchanges are not available for used ......... (this does not apply to faulty goods â€“ faulty products will be exchanged like for like)<br /><br /><br />\r\n\r\nDiscounted or our end of line products can only be returned for repair no refunds of replacements will be made.<br /><br /><br />\r\n\r\n<h2> Incorrect/Damaged Goods </h2><br />\r\n\r\n We try very hard to ensure that you receive your order in pristine condition. If you do not receive your products ordered. Please contract us. In the unlikely event that the product arrives damaged or faulty, please contact [Company name] immediately, this will be given special priority and you can expect to receive the correct item within 72 hours. Any incorrect items received all delivery charges will be refunded back onto you credit/debit card.<br /><br /><br />\r\n\r\n<h2>Delivery service</h2><br />\r\nWe try to make the delivery process as simple as possible and our able to send your order either you home or to your place of work.<br /><br /><br />\r\n\r\nDelivery times are calculated in working days Monday to Friday. If you order after 4 pm the next working day will be considered the first working day for delivery. In case of bank holidays and over the Christmas period, please allow an extra two working days.<br /><br /><br />\r\n\r\nWe aim to deliver within 3 working days but sometimes due to high order volume certain in sales periods please allow 4 days before contacting us. We will attempt to email you if we become aware of an unexpected delay. <br /><br /><br />\r\n\r\nAll small orders are sent out via royal mail 1st packets post service, if your order is over Â£15.00 it will be sent out via royal mails recorded packet service, which will need a signature, if you are not present a card will be left to advise you to pick up your goods from the local sorting office.<br /><br /><br />\r\n\r\nEach item will be attempted to be delivered twice. Failed deliveries after this can be delivered at an extra cost to you or you can collect the package from your local post office collection point.<br /><br /><br />\r\n\r\n<h2>Export restrictions</h2><br /><br /><br />\r\n\r\nAt present [Company name] only sends goods within the [Country]. We plan to add exports to our services in the future. If however you have a special request please contact us your requirements.<br /><br /><br />\r\n\r\n<h2> Privacy Notice </h2><br />\r\n\r\nThis policy covers all users who register to use the website. It is not necessary to purchase anything in order to gain access to the searching facilities of the site.<br /><br /><br />\r\n\r\n<h2> Security </h2><br />\r\nWe have taken the appropriate measures to ensure that your personal information is not unlawfully processed. [Company name] uses industry standard practices to safeguard the confidentiality of your personal identifiable information, including â€˜firewallsâ€™ and secure socket layers. <br /><br /><br />\r\n\r\nDuring the payment process, we ask for personal information that both identifies you and enables us to communicate with you. <br /><br /><br />\r\n\r\nWe will use the information you provide only for the following purposes.<br /><br /><br />\r\n\r\n* To send you newsletters and details of offers and promotions in which we believe you will be interested. <br />\r\n* To improve the content design and layout of the website. <br />\r\n* To understand the interest and buying behavior of our registered users<br />\r\n* To perform other such general marketing and promotional focused on our products and activities. <br />\r\n\r\n<h2> Conditions Of Use </h2><br />\r\n[Company name] and its affiliates provide their services to you subject to the following conditions. If you visit our shop at [Company name] you accept these conditions. Please read them carefully, [Company name] controls and operates this site from its offices within the [Country]. The laws of [Country] relating to including the use of, this site and materials contained. <br /><br /><br />\r\n\r\nIf you choose to access from another country you do so on your own initiave and are responsible for compliance with applicable local lands. <br /><br /><br />\r\n\r\n<h2> Copyrights </h2><br />\r\nAll content includes on the site such as text, graphics logos button icons images audio clips digital downloads and software are all owned by [Company name] and are protected by international copyright laws. <br /><br /><br />\r\n\r\n<h2> License and Site Access </h2><br />\r\n[Company name] grants you a limited license to access and make personal use of this site. This license doses not include any resaleâ€™s of commercial use of this site or its contents any collection and use of any products any collection and use of any product listings descriptions or prices any derivative use of this site or its contents, any downloading or copying of account information. For the benefit of another merchant or any use of data mining, robots or similar data gathering and extraction tools.<br /><br /><br />\r\n\r\nThis site may not be reproduced duplicated copied sold â€“ resold or otherwise exploited for any commercial exploited without written consent of [Company name].<br /><br /><br />\r\n\r\n<h2> Product Descriptions </h2><br />\r\n[Company name] and its affiliates attempt to be as accurate as possible however we do not warrant that product descriptions or other content is accurate complete reliable, or error free.<br /><br /><br />\r\nFrom time to time there may be information on [Company name] that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing and availability.<br /><br /><br />\r\nWe reserve the right to correct ant errors inaccuracies or omissions and to change or update information at any time without prior notice. (Including after you have submitted your order) We apologies for any inconvenience this may cause you. <br /><br /><br />\r\n\r\n<h2> Prices </h2><br />\r\nPrices and availability of items are subject to change without notice the prices advertised on this site are for orders placed and include VAT and delivery.<br /><br /><br />\r\n<br /><br /><br />\r\nPlease review our other policies posted on this site. These policies also govern your visit to [Company name]', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_itembox_w', '200px', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_itembox_h', '300px', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_cipr', '3', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_newtime', '604800', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_freeshipsum', '0', 'eshop'");
						$result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('eshop_coupons', '0', 'eshop'");
						// Update tables from previous shop installs
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD comments char(1) NOT NULL default '' AFTER campaign");
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD ratings char(1) NOT NULL default '' AFTER comments");
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD linebreaks char(1) NOT NULL default '' AFTER ratings");
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD keywords varchar(255) NOT NULL default '' AFTER linebreaks");
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop ADD product_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."' AFTER keywords");
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop_cats ADD cat_order MEDIUMINT(8) UNSIGNED NOT NULL AFTER status");
						$result = dbquery("ALTER TABLE ".DB_PREFIX."eshop_cats ADD cat_languages VARCHAR(200) NOT NULL DEFAULT '".$settings['locale']."' AFTER cat_order");
						$result = dbquery("RENAME TABLE `".DB_PREFIX."eshop_cupons` TO `".DB_PREFIX."eshop_coupons`");
					}
					// Email templates admin section
					$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.gif', '".$locale['T001']."', 'email.php', '1')");
					if ($result) {
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
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
					// Create admin page for permalinks
					$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.gif', '".$locale['SEO']."', 'permalink.php', '3')");
					// Upgrade admin rights for permalink admin
					if ($result) {
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
						while ($data = dbarray($result)) {
							$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".PL' WHERE user_id='".$data['user_id']."'");
						}
					}
					// Install themes db.
					$result = dbquery("CREATE TABLE ".DB_PREFIX."theme (
									theme_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
									theme_name VARCHAR(50) NOT NULL,
									theme_title VARCHAR(50) NOT NULL,
									theme_file VARCHAR(200) NOT NULL,
									theme_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
									theme_user MEDIUMINT(8) UNSIGNED NOT NULL,
									theme_active TINYINT(1) UNSIGNED NOT NULL,
									theme_config TEXT NOT NULL,
									PRIMARY KEY (theme_id)
						) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
					// Insert theme global settings
					$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S3', 'rocket.gif', '".$locale['setup_3058']."', 'settings_theme.php', '4')");
					// Insert theme template settings
					$result = dbquery("INSERT INTO ".DB_PREFIX."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('TS', 'rocket.gif', '".$locale['setup_3056']."', 'theme.php', '3')");
					// Insert email template settings
					$result = dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.gif', '".$locale['T001']."', 'email.php', '1')");
					if ($result) {
						$result = dbquery("SELECT user_id, user_rights FROM ".DB_USERS." WHERE user_level='-103'");
						while ($data = dbarray($result)) {
							$result2 = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights'].".TS' WHERE user_id='".$data['user_id']."'");
						}
					}
					// Messages
					$result = dbquery("ALTER TABLE ".DB_PREFIX."messages ADD message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER message_from");
					// UF 2.00
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_parent MEDIUMINT(8) NOT NULL AFTER field_cat_name");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_db VARCHAR(200) NOT NULL AFTER field_parent");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_index VARCHAR(200) NOT NULL AFTER field_cat_db");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_field_cats ADD field_cat_class VARCHAR(50) NOT NULL AFTER field_cat_index");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_title VARCHAR(50) NOT NULL AFTER field_id");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_type VARCHAR(25) NOT NULL AFTER field_cat");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_default TEXT NOT NULL AFTER field_type");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_options TEXT NOT NULL AFTER field_default");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_error VARCHAR(50) NOT NULL AFTER field_options");
					$result = dbquery("ALTER TABLE ".DB_PREFIX."user_fields ADD field_config TEXT NOT NULL AFTER field_order");
					$result = dbquery("INSERT INTO ".DB_PREFIX."user_field_cats (field_cat_id, field_cat_name, field_cat_db, field_cat_index, field_cat_class, field_cat_page, field_cat_order) VALUES (5, 'Privacy', '', '', 'entypo shareable', 1, 5)");
					$result = dbquery("INSERT INTO ".DB_PREFIX."user_fields (field_id, field_name, field_cat, field_required, field_log, field_registration, field_order) VALUES ('', 'user_blacklist', '5', '0', '0', '0', '1'");
					// Add black list table
					$result = dbquery("ALTER TABLE ".DB_PREFIX."users ADD user_blacklist TEXT NOT NULL AFTER user_language");
					// Site settings for SEO / SEF
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('site_seo', '0')");
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('normalize_seo', '0')");
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('debug_seo', '0')");
					// Site settings panel exclusions for the new positons
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_aupper', '')");
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('exclude_blower', '')");
					// Admin Theme settings
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('admin_theme', 'Venus')");
					// Bootstrap settings
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('bootstrap', '1')");
					// Entypo settings
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('entypo', '1')");
					// Font-Awesome settings
					$result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('fontawesome', '1')");
					// Set a new default theme to prevent issues during upgrade
					$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='Septenary' WHERE settings_name='theme'");
					// User sig issue fix
					$result = dbquery("ALTER TABLE ".DB_PREFIX."users CHANGE user_sig user_sig VARCHAR(255) NOT NULL DEFAULT ''");
					// New access rights need a larger table for forum ranks
					$result = dbquery("ALTER TABLE ".DB_FORUM_RANKS." CHANGE rank_apply rank_apply TINYINT(4) NOT NULL DEFAULT '-101'");
					// Modify All Rank Levels
					$result = dbquery("SELECT rank_id, rank_apply FROM ".DB_FORUM_RANKS."");
					if (dbrows($result) > 0) {
						while ($data = dbarray($result)) {
							dbquery("UPDATE ".DB_FORUM_RANKS." SET rank_apply ='-".$data['rank_apply']."' WHERE rank_id='".$data['rank_id']."' ");
						}
					}
					addNotice('success', 'The database was upgraded');
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
