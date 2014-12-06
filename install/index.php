<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: install/index.php
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
require_once 'setup_includes.php';
define("FUSION_SELF", basename($_SERVER['PHP_SELF']));
define("IN_FUSION", TRUE);
define("BASEDIR", '../');
define("INCLUDES", BASEDIR."includes/");
define("LOCALE", BASEDIR."locale/");
define("IMAGES", BASEDIR."images/");
define("THEMES", BASEDIR."themes/");
define("USER_IP", $_SERVER['REMOTE_ADDR']);
if (!defined('DYNAMICS')) { define('DYNAMICS', INCLUDES."dynamics/"); }

//$siteurl = rtrim(dirname(getCurrentURL()), '/').'/';
//$url = parse_url($siteurl);
//var_dump($url);

if (isset($_POST['localeset']) && file_exists(LOCALE.$_POST['localeset']) && is_dir(LOCALE.$_POST['localeset'])) {
	include LOCALE.$_POST['localeset']."/setup.php";
	define("LOCALESET", $_POST['localeset']."/");
} else {
	$_POST['localeset'] = "English";
	define("LOCALESET", "English/");
	include LOCALE.LOCALESET."setup.php";
}

if (isset($_POST['step']) && $_POST['step'] == "8") {
	if (file_exists(BASEDIR.'config_temp.php')) {
		@rename(BASEDIR.'config_temp.php', BASEDIR.'config.php');
		@chmod(BASEDIR.'config.php', 0644);
	}
	redirect(BASEDIR.'index.php');
}

$locale_files = makefilelist("../locale/", ".svn|.|..", TRUE, "folders");
$settings['description'] = $locale['setup_title'];
$settings['keywords'] = "";
$settings['siteemail'] = '';
$settings['sitename'] = '';
$settings['siteusername'] = $locale['welcome_title'];
$settings['siteurl'] = FUSION_SELF;
require_once LOCALE.LOCALESET.'global.php';
require_once INCLUDES."output_handling_include.php";
include_once INCLUDES."dynamics/dynamics.inc.php";
require_once INCLUDES."sqlhandler.inc.php";
$dynamics = new dynamics();
$dynamics->boot();
$system_apps = array(
	// dbname to locale application title
	'articles' => $locale['articles']['title'],
	'blog' => $locale['blog']['title'],
	'downloads' => $locale['downloads']['title'],
	'eshop' => $locale['eshop']['title'],
	'faqs' => $locale['faqs']['title'],
	'forums' => $locale['forums']['title'],
	'news' => $locale['news']['title'],
	'photos' => $locale['photos']['title'],
	'polls' => $locale['polls']['title'],
	'weblinks' => $locale['weblinks']['title']
);

opensetup();

// Introduction
if (!isset($_POST['step']) || $_POST['step'] == "" || $_POST['step'] == "0") {

	if (file_exists(BASEDIR.'config.php')) {
		@rename(BASEDIR.'config.php', BASEDIR.'config_temp.php');
		@chmod(BASEDIR.'config_temp.php', 0755);
	}
	// temp is blank.
	if (file_exists(BASEDIR.'config_temp.php')) {
		include BASEDIR.'config_temp.php';
	}

	$settings = array();

	if (isset($db_prefix)) {
		if ($pdo_enabled == "1") {
			require_once INCLUDES."db_handlers/pdo_functions_include.php";
			$pdo = NULL;
			try {
				$pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name.";", $db_user, $db_pass, array(PDO::ATTR_EMULATE_PREPARES => FALSE,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$db_connect = $pdo;
				$db_select = "True";
			} catch (PDOException $e) {
				$db_connect = "False";
				$db_select = "False";
			}
		} else {
			require_once INCLUDES."db_handlers/mysql_functions_include.php";
			$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
			mysql_set_charset('utf8', $db_connect);
			$db_select = @mysql_select_db($db_name);
		}

		$result = dbquery("SELECT * FROM ".$db_prefix."settings");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$settings[$data['settings_name']] = $data['settings_value'];
			}
		}
	}

	if (isset($_POST['htaccess']) && isset($db_prefix) && !empty($settings)) {
		write_htaccess();
		redirect(FUSION_SELF);
	}

	if (isset($_POST['uninstall'])) {
		if (isset($db_prefix)) {
			include 'includes/core.setup.php';
			@unlink(BASEDIR.'config_temp.php');
			@unlink(BASEDIR.'config.php');
			redirect(FUSION_SELF);
		}
	}

	if (!file_exists(BASEDIR.'config.php') && !file_exists(BASEDIR.'config_temp.php') && !isset($_POST['uninstall'])) {
		$locale_list = makefileopts($locale_files, $_POST['localeset']);
		echo "<h4 class='strong'>".$locale['welcome_title']."/h4>\n";
		if (isset($_GET['error']) && $_GET['error'] == 'license') {
			echo "<div class='alert alert-danger'>".$locale['error_000']."</div>\n";
		} else {
			echo "<span>".$locale['welcome_desc']."</span>\n";
		}
		echo "<span class='display-block m-t-20 m-b-10 strong'>".$locale['010']."</span>\n";
		echo form_select('', 'localeset', 'localeset', array_combine($locale_files, $locale_files), '', array('placeholder' => $locale['choose']));
		echo "<div>".$locale['011']."</div>\n";
		echo "<hr>\n";
		echo form_checkbox($locale['terms'], 'license', 'license', '');
		echo "<hr>\n";
		echo "<input type='hidden' name='step' value='2' />\n";
		renderButton();

	} elseif (!isset($_POST['uninstall'])) {

		echo "<h4 class='strong'>".$locale['1001']."</h4>\n";
		echo "<span class='display-block m-t-20 m-b-10'>".$locale['1002']."</span>\n";
		echo "<div class='well'>\n";
		echo "<span class='strong display-inline-block m-b-10'>".$locale['1003']."</span><br/><p>".$locale['1004']." <span class='strong'>".$locale['1005']."</span></span></p>";
		echo form_button($locale['1006'], 'uninstall', 'uninstall', 'uninstall', array('class' => 'btn-danger btn-sm m-t-10'));
		echo "</div>\n";
		echo "<div class='well'>\n";
		echo "<span class='strong display-inline-block m-b-10'>".$locale['1007']."</span>\n<br/><p>".$locale['1008']."</p>";
		echo form_button($locale['1009'], 'step', 'step', '5', array('class' => 'btn-primary btn-sm m-r-10'));
		echo "</div>\n";
		echo "<div class='well'>\n";
		echo "<span class='strong display-inline-block m-b-10'>".$locale['1010']."</span>\n<br/><p>".$locale['1011']."</p>";
		echo form_button($locale['1012'], 'step', 'step', '6', array('class' => 'btn-primary btn-sm m-r-10'));
		echo "</div>\n";

		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";

		if (isset($db_prefix)) {
			echo "<div class='well'>\n";
			echo "<span class='strong display-inline-block m-b-10'>".$locale['1013']."</span>\n<br/><p>".$locale['1014']."</p>";
			echo form_button($locale['1015'], 'htaccess', 'htaccess', 'htaccess', array('class' => 'btn-primary btn-sm m-r-10'));
			echo "</div>\n";
		}

	}
}

// Step 2 - File and Folder Permissions
if (isset($_POST['step']) && $_POST['step'] == "2") {
	if (!isset($_POST['license'])) {
		redirect(FUSION_SELF."?error=license");
	}
	if (!file_exists(BASEDIR."config_temp.php")) {
		if (file_exists(BASEDIR."_config.php") && function_exists("rename")) {
			@rename(BASEDIR."_config.php", BASEDIR."config_temp.php");
		} else {
			$handle = fopen(BASEDIR."config_temp.php", "w");
			fclose($handle);
		}
	}
	$check_arr = array("administration/db_backups" => FALSE,
		"forum/attachments" => FALSE,
		"downloads" => FALSE,
		"downloads/images" => FALSE,
		"downloads/submissions/" => FALSE,
		"downloads/submissions/images" => FALSE,
		"ftp_upload" => FALSE,
		"images" => FALSE,
		"images/imagelist.js" => FALSE,
		"images/articles" => FALSE,
		"images/avatars" => FALSE,
		"images/news" => FALSE,
		"images/news/thumbs" => FALSE,
		"images/news_cats" => FALSE,
		"images/news" => FALSE,
		"images/blog/thumbs" => FALSE,
		"images/blog_cats" => FALSE,
		"images/photoalbum" => FALSE,
		"images/photoalbum/submissions" => FALSE,
		"config_temp.php" => FALSE,
		"robots.txt" => FALSE);
	$write_check = TRUE;
	$check_display = "";
	foreach ($check_arr as $key => $value) {
		if (file_exists($key) && is_writable(BASEDIR.$key)) {
			$check_arr[$key] = TRUE;
		} else {
			if (file_exists(BASEDIR.$key) && function_exists("chmod") && @chmod(BASEDIR.$key, 0777) && is_writable(BASEDIR.$key)) {
				$check_arr[$key] = TRUE;
			} else {
				$write_check = FALSE;
			}
		}
		$check_display .= "<tr>\n<td class='tbl1'>".$key."</td>\n";
		$check_display .= "<td class='tbl1' style='text-align:right'>".($check_arr[$key] == TRUE ? "<label class='label label-success'>".$locale['2001']."</label>" : "<label class='label label-warning'>".$locale['2002']."</label>")."</td>\n</tr>\n";
	}
	echo "<div class='m-b-20'><h4>".$locale['2007']."</h4> ".$locale['2003']."</div>\n";
	echo "<table class='table table-responsive'>\n".$check_display."\n</table><br /><br />\n";

	if ($write_check) {
		echo "<p><strong>".$locale['2004']."</strong></p>\n";
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		echo "<input type='hidden' name='step' value='3' />\n";
		renderButton();
	} else {
		echo "<p><strong>".$locale['022']."</strong></p>\n";
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		echo "<input type='hidden' name='step' value='2' />\n";
		echo "<br/><button type='submit' name='next' value='".$locale['2006']."' class='btn btn-md btn-primary'><i class='entypo cw'></i> ".$locale['2006']."</button>\n";
	}
}
// Step 3 - Database Settings
if (isset($_POST['step']) && $_POST['step'] == "3") {
	$db_prefix = "fusion".createRandomPrefix()."_";
	$cookie_prefix = "fusion".createRandomPrefix()."_";
	$db_host = (isset($_POST['db_host']) ? stripinput(trim($_POST['db_host'])) : "localhost");
	$db_user = (isset($_POST['db_user']) ? stripinput(trim($_POST['db_user'])) : "");
	$db_user = (isset($_POST['db_user']) ? stripinput(trim($_POST['db_user'])) : "");
	$db_name = (isset($_POST['db_name']) ? stripinput(trim($_POST['db_name'])) : "");
	$email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
	$username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
	$pdo_enabled = (isset($_POST['pdo_enabled']) ? stripinput(trim($_POST['pdo_enabled'])) : "");
	$db_prefix = (isset($_POST['db_prefix']) ? stripinput(trim($_POST['db_prefix'])) : $db_prefix);
	$db_error = (isset($_POST['db_error']) && isnum($_POST['db_error']) ? $_POST['db_error'] : "0");
	$field_class = array("", "", "", "", "");
	if ($db_error > "0") {
		$field_class[2] = " tbl-error";
		if ($db_error == 1) {
			$field_class[1] = " tbl-error";
			$field_class[2] = " tbl-error";
		} elseif ($db_error == 2) {
			$field_class[3] = " tbl-error";
		} elseif ($db_error == 3) {
			$field_class[4] = " tbl-error";
		} elseif ($db_error == 7) {
			if ($db_host == "") {
				$field_class[0] = " tbl-error";
			}
			if ($db_user == "") {
				$field_class[1] = " tbl-error";
			}
			if ($db_name == "") {
				$field_class[3] = " tbl-error";
			}
			if ($db_prefix == "") {
				$field_class[4] = " tbl-error";
			}
		}
	}

	echo "<div class='m-b-20'><h4>".$locale['3001']."</h4> ".$locale['3002']."</div>\n";

	echo "<table class='table table-responsive'>\n<tr>\n";
	echo "<td class='tbl1' style='text-align:left'>".$locale['031']."</td>\n";
	echo "<td class='tbl1'><input type='text' value='".$db_host."' name='db_host' class='form-control input-sm textbox".$field_class[0]."' style='width:200px' /></td>\n</tr>\n";
	echo "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['032']."</td>\n";
	echo "<td class='tbl1'><input type='text' value='".$db_user."' name='db_user' class='form-control input-sm textbox".$field_class[1]."' style='width:200px' /></td>\n</tr>\n";
	echo "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['033']."</td>\n";
	echo "<td class='tbl1'><input type='password' value='' name='db_pass' class='form-control input-sm textbox".$field_class[2]."' style='width:200px' /></td>\n</tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['034']."</td>\n";
	echo "<td class='tbl1'><input type='text' value='".$db_name."' name='db_name' class='form-control input-sm textbox".$field_class[3]."' style='width:200px' /></td>\n</tr>\n";
	echo "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['037']."</td>\n";
	// enable PDO
	echo "<td class='tbl1'>\n";
	if (!defined('PDO::ATTR_DRIVER_NAME')) {
		echo $locale['038'];
	} else {
		echo "<select name='pdo_enabled' class='form-control input-sm textbox' style='width:200px'>\n";
		echo "<option value='0' selected='selected'>".$locale['039']."</option>\n";
		echo "<option value='1'>".$locale['039b']."</option>\n";
		echo "</select>\n";
	}
	echo "</td>\n</tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['039n']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='text' name='username' value='".$username."' placeholder='Admin' maxlength='255' class='form-control input-sm textbox' style='width:200px' /></td></tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['066']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='text' name='email' value='".$email."' maxlength='100' class='form-control input-sm textbox' style='width:200px' /></td></tr>\n";
	echo "<tr><td class='tbl1'>".$locale['039c']."</td>\n";
	echo "<td class='tbl1'>\n";
	for ($i = 0; $i < sizeof($locale_files); $i++) {
		if (file_exists(BASEDIR.'locale/'.$locale_files[$i].'/setup.php')) {
			echo "<input type='checkbox' value='".$locale_files[$i]."' name='enabled_languages[]' class='m-r-10 textbox' ".($locale_files[$i] == $_POST['localeset'] ? "checked='checked'" : "")."> ".$locale_files[$i]."<br />\n";
		}
	}
	echo "</td></tr>\n";
	echo "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['035']."</td>\n";
	echo "<td class='tbl1'><input type='text' value='".$db_prefix."' name='db_prefix' class='form-control input-sm textbox".$field_class[4]."' style='width:200px' /></td>\n</tr>\n";
	echo "<tr>\n<td class='tbl1' style='text-align:left'>".$locale['036']."</td>\n";
	echo "<td class='tbl1'><input type='text' value='".$cookie_prefix."' name='cookie_prefix' class='form-control input-sm textbox' style='width:200px' /></td>\n</tr>\n";
	echo "</table>\n";
	echo "</td>\n</tr>\n<tr>\n<td class='tbl2' style='text-align:center'>\n";
	echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
	echo "<input type='hidden' name='step' value='4' />\n";
	renderButton();
}

// Step 4 - Config / Database Setup
if (isset($_POST['step']) && $_POST['step'] == "4") {
	// Generate All Core Tables - this includes settings and all its injections
	$db_host = (isset($_POST['db_host']) ? stripinput(trim($_POST['db_host'])) : "");
	$db_user = (isset($_POST['db_user']) ? stripinput(trim($_POST['db_user'])) : "");
	$db_pass = (isset($_POST['db_pass']) ? stripinput(trim($_POST['db_pass'])) : "");
	$db_name = (isset($_POST['db_name']) ? stripinput(trim($_POST['db_name'])) : "");
	$pdo_enabled = (isset($_POST['pdo_enabled']) ? stripinput(trim($_POST['pdo_enabled'])) : "");
	$db_prefix = (isset($_POST['db_prefix']) ? stripinput(trim($_POST['db_prefix'])) : "");
	$cookie_prefix = (isset($_POST['cookie_prefix']) ? stripinput(trim($_POST['cookie_prefix'])) : "fusion_");
	$email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
	$username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
	$enabled_languages = '';
	if (!empty($_POST['enabled_languages'])) {
		for ($i = 0; $i < sizeof($_POST['enabled_languages']); $i++) {
			$enabled_languages .= $_POST['enabled_languages'][$i].".";
		}
		$enabled_languages = substr($enabled_languages, 0, (strlen($enabled_languages)-1));
	} else {
		$enabled_languages = stripinput($_POST['localeset']);
	}
	if ($db_prefix != "") {
		$db_prefix_last = $db_prefix[strlen($db_prefix)-1];
		if ($db_prefix_last != "_") {
			$db_prefix = $db_prefix."_";
		}
	}
	if ($cookie_prefix != "") {
		$cookie_prefix_last = $cookie_prefix[strlen($cookie_prefix)-1];
		if ($cookie_prefix_last != "_") {
			$cookie_prefix = $cookie_prefix."_";
		}
	}
	$selected_langs = '';
	$secret_key = createRandomPrefix(32);
	$secret_key_salt = createRandomPrefix(32);
	if ($db_host != "" && $db_user != "" && $db_name != "" && $db_prefix != "") {
		if ($pdo_enabled == "1") {
			require_once INCLUDES."db_handlers/pdo_functions_include.php";
			$pdo = NULL;
			try {
				$pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name.";", $db_user, $db_pass, array(PDO::ATTR_EMULATE_PREPARES => FALSE,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$db_connect = $pdo;
				$db_select = "True";
			} catch (PDOException $e) {
				$db_connect = "False";
				$db_select = "False";
			}
		} else {
			require_once INCLUDES."db_handlers/mysql_functions_include.php";
			$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
			mysql_set_charset('utf8', $db_connect);
			$db_select = @mysql_select_db($db_name);
		}
		if ($db_connect) {
			if ($db_select) {
				if (dbrows(dbquery("SHOW TABLES LIKE '".str_replace("_", "\_", $db_prefix)."%'")) == "0") {
					$table_name = uniqid($db_prefix, FALSE);
					$can_write = TRUE;
					$result = dbquery("CREATE TABLE ".$table_name." (test_field VARCHAR(10) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci");
					if (!$result) {
						$can_write = FALSE;
					}
					$result = dbquery("DROP TABLE ".$table_name);
					if (!$result) {
						$can_write = FALSE;
					}
					if ($can_write) {
						// Write a Temporary Config File.
						$config = "<?php\n";
						$config .= "// database settings\n";
						$config .= "\$db_host = '".$db_host."';\n";
						$config .= "\$db_user = '".$db_user."';\n";
						$config .= "\$db_pass = '".$db_pass."';\n";
						$config .= "\$db_name = '".$db_name."';\n";
						$config .= "\$db_prefix = '".$db_prefix."';\n";
						$config .= "\$pdo_enabled = '".$pdo_enabled."';\n";
						$config .= "define(\"DB_PREFIX\", \"".$db_prefix."\");\n";
						$config .= "define(\"COOKIE_PREFIX\", \"".$cookie_prefix."\");\n";
						$config .= "define(\"SECRET_KEY\", \"".$secret_key."\");\n";
						$config .= "define(\"SECRET_KEY_SALT\", \"".$secret_key_salt."\");\n";
						$config .= "?>";
						$temp = fopen(BASEDIR.'config_temp.php', 'w');
						if (fwrite($temp, $config)) {
							fclose($temp);
							$fail = FALSE;
							if (!$result) {
								$fail = TRUE;
							}
							// install core tables fully injected.
							include 'includes/core.setup.php';
							if (!$fail) {
								echo "<i class='entypo check'></i> ".$locale['4001']."<br /><br />\n<i class='entypo check'></i> ";
								echo $locale['4002']."<br /><br />\n<i class='entypo check'></i> ";
								echo $locale['4003']."<br /><br />\n";
								$success = TRUE;
								$db_error = 6;
								// get settings for htaccess.
								$result = dbquery("SELECT * FROM ".$db_prefix."settings");
								if (dbrows($result)) {
									while ($data = dbarray($result)) {
										$settings[$data['settings_name']] = $data['settings_value'];
									}
								}
							} else {
								echo "<br />\n<i class='entypo check'></i> ".$locale['4001']."<br /><br />\n<i class='entypo check'></i> ";
								echo $locale['4002']."<br /><br />\n<i class='entypo icancel'></i> ";
								echo "<strong>".$locale['4004']."</strong> ".$locale['4009']."<br /><br />\n";
								$success = FALSE;
								$db_error = 0;
							}
						} else {
							echo "<br />\n".$locale['4001']."<br /><br />\n";
							echo "<strong>".$locale['4004']."</strong> ".$locale['4007']."<br />\n";
							echo "<span class='small'>".$locale['4008']."</span><br /><br />\n";
							$success = FALSE;
							$db_error = 5;
						}

						write_htaccess();

					} else {
						echo "<div class='alert alert-danger'>\n";
						echo $locale['4001']."<br /><br />\n";
						echo "<strong>".$locale['4004']."</strong> ".$locale['054']."<br />\n";
						echo "<span class='small'>".$locale['055']."</span><br /><br />\n";
						echo "</div>\n";
						$success = FALSE;
						$db_error = 4;
					}
				} else {
					echo "<div class='alert alert-danger'>\n";
					echo "<strong>".$locale['4004']."<strong> ".$locale['052']."<br />\n";
					echo "<span class='small'>".$locale['053']."</span><br /><br />\n";
					echo "</div>\n";
					$success = FALSE;
					$db_error = 3;
				}
			} else {
				echo "<div class='alert alert-danger'>\n";
				echo "<br />\n<strong>".$locale['4004']."<strong> ".$locale['050']."<br />\n";
				echo "<span class='small'>".$locale['051']."</span><br /><br />\n";
				echo "</div>\n";
				$success = FALSE;
				$db_error = 2;
			}
		} else {
			echo "<div class='alert alert-danger'>\n";
			echo "<strong>".$locale['4004']."<strong> ".$locale['4005']."<br />\n";
			echo "<span class='small'>".$locale['4006']."</span><br /><br />\n";
			echo "</div>\n";
			$success = FALSE;
			$db_error = 1;
		}
	} else {
		echo "<div class='alert alert-danger'>\n";
		echo "<strong>".$locale['4004']."<strong> ".$locale['056']."<br />\n";
		echo "".$locale['057']."<br /><br />\n";
		echo "</div>\n";
		$success = FALSE;
		$db_error = 7;
	}

	echo "</td>\n</tr>\n<tr>\n<td class='tbl2' style='text-align:center'>\n";
	echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
	echo "<input type='hidden' name='enabled_languages' value='".$selected_langs."' />\n";
	if ($success) {
		echo "<input type='hidden' name='step' value='5' />\n";
		renderButton();
	} else {
		echo "<input type='hidden' name='step' value='3' />\n";
		echo "<input type='hidden' name='db_host' value='".$db_host."' />\n";
		echo "<input type='hidden' name='db_user' value='".$db_user."' />\n";
		echo "<input type='hidden' name='db_name' value='".$db_name."' />\n";
		echo "<input type='hidden' name='db_prefix' value='".$db_prefix."' />\n";
		echo "<input type='hidden' name='db_error' value='".$db_error."' />\n";
		echo "<button type='submit' name='next' value='".$locale['008']."' class='btn btn-md btn-warning'><i class='entypo cw'></i> ".$locale['008']."</button>\n";
	}
}
// Step 5 - Configure Core System - $settings accessible - Requires Config_temp.php (Shut down site when upgrading).
if (isset($_POST['step']) && $_POST['step'] == '5') {
	$db_prefix = '';
	$pdo_enabled = '';
	$db_host = '';
	$db_name = '';
	$db_user = '';
	$db_pass = '';
	if (!isset($_POST['done'])) {
		// Load Config and SQL handler.
		if (file_exists(BASEDIR.'config_temp.php')) {
			include BASEDIR.'config_temp.php';
			if ($pdo_enabled == "1") {
				require_once INCLUDES."db_handlers/pdo_functions_include.php";
				$pdo = NULL;
				try {
					$pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name.";", $db_user, $db_pass, array(PDO::ATTR_EMULATE_PREPARES => FALSE,
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
					$db_connect = $pdo;
					$db_select = "True";
				} catch (PDOException $e) {
					$db_connect = "False";
					$db_select = "False";
				}
			} else {
				require_once INCLUDES."db_handlers/mysql_functions_include.php";
				$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
				mysql_set_charset('utf8', $db_connect);
				$db_select = @mysql_select_db($db_name);
			}
			$settings = array();
			$result = dbquery("SELECT * FROM ".$db_prefix."settings");
			if (dbrows($result)) {
				while ($data = dbarray($result)) {
					$settings[$data['settings_name']] = $data['settings_value'];
				}
			}
		} else {
			redirect(FUSION_SELF);
		}
		$fail = FALSE;
		$message = '';
		// Do installation
		if (isset($_POST['install'])) {
			$_apps = stripinput($_POST['install']);
			if (file_exists('includes/'.$_apps.'_setup.php')) {
				include 'includes/'.$_apps.'_setup.php';
				$message = "<div class='alert alert-success'><i class='entypo check'></i> ".$system_apps[$_apps]." system have been successfully installed.</div>";
				if ($fail) {
					$message = "<div class='alert alert-danger'><i class='entypo icancel'></i> ".$system_apps[$_apps]." system installation failed.</div>";
				}
			}
		}
		// Do uninstallation
		if (isset($_POST['uninstall'])) {
			$_apps = stripinput($_POST['uninstall']);
			if (file_exists('includes/'.$_apps.'_setup.php')) {
				include 'includes/'.$_apps.'_setup.php';
				$message = "<div class='alert alert-warning'><i class='entypo check'></i> ".$system_apps[$_apps]." system have been successfully removed.</div>";
				if ($fail) {
					$message = "<div class='alert alert-danger'><i class='entypo icancel'></i> ".$system_apps[$_apps]." system cannot be removed or failed.</div>";
				}
			}
		}
		foreach ($system_apps as $_apps_key => $_apps) {
			if (file_exists('includes/'.$_apps_key.'_setup.php')) {
				$installed = db_exists($db_prefix.$_apps_key);
				$apps_data = array('title' => $locale[$_apps_key]['title'],
					'description' => $locale[$_apps_key]['description'],
					'key' => $_apps_key);
				if ($installed) {
					$apps['1'][] = $apps_data;
				} else {
					$apps['0'][] = $apps_data;
				}
			}
		}
		echo "<div class='m-b-20'><h4>".$locale['5001']."</h4> ".$locale['5002']."</div>\n";
		echo $message;
		if (!empty($apps[1])) {
			foreach ($apps[1] as $k => $v) {
				echo "<hr class='m-t-5 m-b-5'/>\n";
				echo "<div class='row'>\n";
				echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n".ucwords($v['title']);
				echo "<div class='pull-right'>\n";
				echo form_button('Remove', 'uninstall', 'uninstall', $v['key'], array('class' => 'btn-xs btn-default',
					'icon' => 'entypo trash'));
				echo "</div>\n";
				echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>".$v['description']."";
				echo "</div>\n</div>\n";
			}
		}
		if (!empty($apps[0])) {
			foreach ($apps[0] as $k => $v) {
				echo "<hr class='m-t-5 m-b-5'/>\n";
				echo "<div class='row'>\n";
				echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n".ucwords($v['title']);
				echo "<div class='pull-right'>\n";
				echo form_button('Install', 'install', 'install', $v['key'], array('class' => 'btn-xs btn-default',
					'icon' => 'entypo publish'));
				echo "</div>\n";
				echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>".$v['description']."";
				echo "</div>\n</div>\n";
			}
		}
	} elseif (isset($_POST['done'])) {
		// system ready
		echo "<div class='m-b-20'><h4>".$locale['5003']."</h4> ".$locale['5004']."</div>\n";
	}

	if (isset($_POST['done'])) {
		echo "<div class='m-t-10'>\n";
		echo "<input type='hidden' name='step' value='6' />\n";
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		renderButton();
		echo "</div>\n";
	} else {
		echo "<div class='m-t-10'>\n";
		echo "<input type='hidden' name='step' value='5' />\n";
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		renderButton(2);
		echo "</div>\n";
	}
}
// Step 6 - Primary Admin Details
if (isset($_POST['step']) && $_POST['step'] == '6') {
	$iOWNER = 0;
	$username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
	$email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
	$error_pass = (isset($_POST['error_pass']) && isnum($_POST['error_pass']) ? $_POST['error_pass'] : "0");
	$error_name = (isset($_POST['error_name']) && isnum($_POST['error_name']) ? $_POST['error_name'] : "0");
	$error_mail = (isset($_POST['error_mail']) && isnum($_POST['error_mail']) ? $_POST['error_mail'] : "0");
	$field_class = array("", "", "", "", "", "");
	if ($error_pass == "1" || $error_name == "1" || $error_mail == "1") {
		$field_class = array("", " tbl-error", " tbl-error", " tbl-error", " tbl-error", "");
		if ($error_name == 1) {
			$field_class[0] = " tbl-error";
		}
		if ($error_mail == 1) {
			$field_class[5] = " tbl-error";
		}
	}
	// to scan whether User Acccount exists.
	if (file_exists(BASEDIR.'config.php') || file_exists(BASEDIR.'config_temp.php')) {
		if (file_exists(BASEDIR.'config.php')) { include BASEDIR.'config.php'; }
		elseif (file_exists(BASEDIR.'config_temp.php')) { include BASEDIR.'config_temp.php'; }

		if ($pdo_enabled == "1") {
			require_once INCLUDES."db_handlers/pdo_functions_include.php";
			$pdo = NULL;
			try {
				$pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name.";", $db_user, $db_pass, array(PDO::ATTR_EMULATE_PREPARES => FALSE,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$db_connect = $pdo;
				$db_select = "True";
			} catch (PDOException $e) {
				$db_connect = "False";
				$db_select = "False";
			}
		} else {
			require_once INCLUDES."db_handlers/mysql_functions_include.php";
			$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
			mysql_set_charset('utf8', $db_connect);
			$db_select = @mysql_select_db($db_name);
		}
		$settings = array();
		$result = dbquery("SELECT * FROM ".$db_prefix."settings");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$settings[$data['settings_name']] = $data['settings_value'];
			}
		}

		$iOWNER = dbcount("('user_id')", $db_prefix."users", "user_id='1'");

	} else {
		redirect(FUSION_SELF);
	}


	if ($iOWNER) {
		echo "<div class='m-b-20'><h4>".$locale['6003']."</h4> ".$locale['6004']."</div>\n";
		echo "<input type='hidden' name='transfer' value='1'>\n";
		// load authentication during post.
		// in development.
	} else {
		echo "<div class='m-b-20'><h4>".$locale['6001']."</h4> ".$locale['6002']."</div>\n";
	}

	echo "<table class='table table-responsive'>\n<tr>\n";
	echo "<td class='tbl1'>".$locale['061']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='text' name='username' value='".$username."' maxlength='30' class='form-control input-sm textbox".$field_class[0]."' style='width:200px' /></td></tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['066']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='text' name='email' value='".$email."' maxlength='100' class='form-control input-sm textbox' style='width:200px' /></td></tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['062']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='password' name='password1' maxlength='64' class='form-control input-sm textbox".$field_class[1]."' style='width:200px' /></td></tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['063']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='password' name='password2' maxlength='64' class='form-control input-sm textbox".$field_class[2]."' style='width:200px' /></td></tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['064']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='password' name='admin_password1' maxlength='64' class='form-control input-sm textbox".$field_class[3]."' style='width:200px' /></td></tr>\n";
	echo "<tr>\n<td class='tbl1'>".$locale['065']."</td>\n";
	echo "<td class='tbl1' style='text-align:right'><input type='password' name='admin_password2' maxlength='64' class='form-control input-sm textbox".$field_class[4]."' style='width:200px' /></td></tr>\n";
	echo "</table>\n";
	echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
	echo "<input type='hidden' name='enabled_languages' value='".$settings['enabled_languages']."' />\n";
	echo "<input type='hidden' name='step' value='7' />\n";
	renderButton();
}

// Step 7 - Final Settings
if (isset($_POST['step']) && $_POST['step'] == "7") {
	if (file_exists(BASEDIR.'config_temp.php')) {
		include BASEDIR.'config_temp.php';
		if ($pdo_enabled == "1") {
			require_once INCLUDES."db_handlers/pdo_functions_include.php";
			$pdo = NULL;
			try {
				$pdo = new PDO("mysql:host=".$db_host.";dbname=".$db_name.";", $db_user, $db_pass, array(PDO::ATTR_EMULATE_PREPARES => FALSE,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$db_connect = $pdo;
				$db_select = "True";
			} catch (PDOException $e) {
				$db_connect = "False";
				$db_select = "False";
			}
		} else {
			require_once INCLUDES."db_handlers/mysql_functions_include.php";
			$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
			mysql_set_charset('utf8', $db_connect);
			$db_select = @mysql_select_db($db_name);
		}
		$settings = array();
		$result = dbquery("SELECT * FROM ".$db_prefix."settings");
		if (dbrows($result)) {
			while ($data = dbarray($result)) {
				$settings[$data['settings_name']] = $data['settings_value'];
			}
		}
	} else {
		redirect(FUSION_SELF);
	}
	$error = "";
	$error_pass = "0";
	$error_name = "0";
	$error_mail = "0";
	$settings['password_algorithm'] = "sha256";
	$username = (isset($_POST['username']) ? stripinput(trim($_POST['username'])) : "");
	if ($username == "") {
		$error .= $locale['070b']."<br /><br />\n";
		$error_name = "1";
	} elseif (!preg_match("/^[-0-9A-Z_@\s]+$/i", $username)) {
		$error .= $locale['070']."<br /><br />\n";
		$error_name = "1";
	}
	require_once INCLUDES."/classes/PasswordAuth.class.php";
	$userPassword = "";
	$adminPassword = "";
	$userPass = new PasswordAuth();
	$userPass->inputNewPassword = (isset($_POST['password1']) ? stripinput(trim($_POST['password1'])) : "");
	$userPass->inputNewPassword2 = (isset($_POST['password2']) ? stripinput(trim($_POST['password2'])) : "");
	$returnValue = $userPass->isValidNewPassword();
	if ($returnValue == 0) {
		$userPassword = $userPass->getNewHash();
		$userSalt = $userPass->getNewSalt();
	} elseif ($returnValue == 2) {
		$error .= $locale['071']."<br /><br />\n";
		$error_pass = "1";
	} elseif ($returnValue == 3) {
		$error .= $locale['072']."<br /><br />\n";
	}
	$adminPass = new PasswordAuth();
	$adminPass->inputNewPassword = (isset($_POST['admin_password1']) ? stripinput(trim($_POST['admin_password1'])) : "");
	$adminPass->inputNewPassword2 = (isset($_POST['admin_password2']) ? stripinput(trim($_POST['admin_password2'])) : "");
	$returnValue = $adminPass->isValidNewPassword();
	if ($returnValue == 0) {
		$adminPassword = $adminPass->getNewHash();
		$adminSalt = $adminPass->getNewSalt();
	} elseif ($returnValue == 2) {
		$error .= $locale['073']."<br /><br />\n";
		$error_pass = "1";
	} elseif ($returnValue == 3) {
		$error .= $locale['075']."<br /><br />\n";
	}
	if ($userPass->inputNewPassword == $adminPass->inputNewPassword) {
		$error .= $locale['074']."<br /><br />\n";
		$error_pass = "1";
	}
	$email = (isset($_POST['email']) ? stripinput(trim($_POST['email'])) : "");
	if ($email == "") {
		$error .= $locale['076b']."<br /><br />\n";
		$error_mail = "1";
	} elseif (!preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
		$error .= $locale['076']."<br /><br />\n";
		$error_mail = "1";
	}
	$rows = dbrows(dbquery("SELECT user_id FROM ".$db_prefix."users"));
	if ($error == "") {
		if ($rows == 0) {
			// Create Super Admin with Full Modular Rights - We don't need to update Super Admin later.
			if (isset($_POST['transfer']) && $_POST['transfer'] == 1) {
				$result = dbquery("UPDATE ".$db_prefix."users user_name='".$username."', user_salt='".$userSalt."', user_password='".$userPassword."', user_admin_salt='".$adminSalt."', user_admin_password='".$adminPassword."'
				user_email='".$email."'	WHERE user_id='1'");
			} else {
				$result = dbquery("INSERT INTO ".$db_prefix."users (
				user_name, user_algo, user_salt, user_password, user_admin_algo, user_admin_salt, user_admin_password, user_email, user_hide_email, user_offset,
				user_avatar, user_posts, user_threads, user_joined, user_lastvisit, user_ip, user_rights,
				user_groups, user_level, user_status, user_theme, user_location, user_birthdate, user_aim,
				user_icq, user_yahoo, user_web, user_sig
				) VALUES (
				'".$username."', 'sha256', '".$userSalt."', '".$userPassword."', 'sha256', '".$adminSalt."', '".$adminPassword."',
				'".$email."', '1', '0', '',  '0', '0', '".time()."', '0', '0.0.0.0',
				'A.AC.AD.APWR.B.BB.BLOG.BLC.C.CP.DB.DC.D.ERRO.FQ.F.FR.IM.I.IP.M.MAIL.N.NC.P.PH.PI.PL.PO.ROB.SL.S1.S2.S3.S4.S5.S6.S7.S8.S9.S10.S11.S12.S13.SB.SM.SU.UF.UFC.UG.UL.U.W.WC.MAIL.LANG.ESHP',
				'', '103', '0', 'Default', '', '0000-00-00', '', '',  '', '', ''
				)");
			}
		}
		echo "<div class='m-b-20'><h4>".$locale['7001']."</h4> ".$locale['7002']."</div>\n";
		echo "<div class='m-b-10'>".$locale['7003']."</div>\n";
		echo "<div class='m-b-10'>".$locale['7004']."</div>\n";
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		echo "<input type='hidden' name='step' value='8' />\n";
		renderButton(1);

	} elseif ($rows == 0) {
		echo "<br />\n".$locale['077']."<br /><br />\n".$error;
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		echo "<input type='hidden' name='error_pass' value='".$error_pass."' />\n";
		echo "<input type='hidden' name='error_name' value='".$error_name."' />\n";
		echo "<input type='hidden' name='error_mail' value='".$error_mail."' />\n";
		echo "<input type='hidden' name='username' value='".$username."' />\n";
		echo "<input type='hidden' name='email' value='".$email."' />\n";
		echo "<input type='hidden' name='step' value='6' />\n";
		echo "<button type='submit' name='back' value=".$locale['008']."' class='btn btn-md btn-warning'><i class='entypo cw'></i> ".$locale['008']."</button>\n";
	} else {
		echo "<div class='m-b-20'><h4>".$locale['7001']."</h4> ".$locale['7002']."</div>\n";
		echo "<div class='m-b-10'>".$locale['7003']."</div>\n";
		echo "<div class='m-b-10'>".$locale['7004']."</div>\n";
		echo "<input type='hidden' name='localeset' value='".stripinput($_POST['localeset'])."' />\n";
		echo "<input type='hidden' name='step' value='8' />\n";
		renderButton(1);
	}
}

// Step 8 - ?
closesetup();

?>