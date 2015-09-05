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

add_breadcrumb(array('link' => ADMIN.'upgrade.php'.$aidlink, 'title' => $locale['400']));
opentable($locale['400']);
echo "<div style='text-align:center'><br />\n";
if (str_replace(".", "", fusion_get_settings("version")) < "90001") { // 90001 for testing purposes
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
				} else {
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
					$content .= "<ol type='1' style='padding-left: 24px'>\n";
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
					$htc .= "ErrorDocument 400 ".fusion_get_settings("site_path")."error.php?code=400".PHP_EOL;
					$htc .= "ErrorDocument 401 ".fusion_get_settings("site_path")."error.php?code=401".PHP_EOL;
					$htc .= "ErrorDocument 403 ".fusion_get_settings("site_path")."error.php?code=403".PHP_EOL;
					$htc .= "ErrorDocument 404 ".fusion_get_settings("site_path")."error.php?code=404".PHP_EOL;
					$htc .= "ErrorDocument 500 ".fusion_get_settings("site_path")."error.php?code=500".PHP_EOL;
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
					// @todo: upgrade package shall be rolled out automatically
					include "upgrade/upgrade-7.02-9.00.php";
					dbquery("UPDATE ".DB_SETTINGS." SET settings_value='9.00.00' WHERE settings_name='version'");
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