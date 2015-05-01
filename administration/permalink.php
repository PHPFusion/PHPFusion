<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: permalinks.php
| Author: Ankur Thakur
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

pageAccess('PL');

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
include LOCALE.LOCALESET."admin/permalinks.php";

$locale['rewrite_disabled'] = "It looks like <strong>mod_rewrite</strong> is not enabled on your host. Enabling SEF urls might break your website. Please contact your hosting provider about enabling <strong>mod_rewrite</strong> on your host.";
if (!MOD_REWRITE) {
	addNotice('danger', "<i class='fa fa-lg fa-warning m-r-10'></i>".$locale['rewrite_disabled']);
}

$settings_seo = array(
	'site_seo'		=> fusion_get_settings('site_seo'),
	'normalize_seo'	=> fusion_get_settings('normalize_seo'),
	'debug_seo'		=> fusion_get_settings('debug_seo'),
	);

// TODO: Check if we can or did write .htaccess file before saving settings to DB
if (isset($_POST['savesettings'])) {

	foreach ($settings_seo as $key => $value) {
		$settings_seo[$key] = form_sanitizer($settings_seo[$key], $settings_seo[$key], $key);
		// No need to check for FUSION_NULL here because we have only checkboxes
		dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$settings_seo[$key]."' WHERE settings_name='".$key."'");
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

	if ($settings_seo['site_seo'] == 1) {
		// Rewrite urls settings
		$htc .= "Options +SymLinksIfOwnerMatch".PHP_EOL;
		$htc .= "<IfModule mod_rewrite.c>".PHP_EOL;
		$htc .= "	# Let PHP know mod_rewrite is enabled".PHP_EOL;
		$htc .= "	<IfModule mod_env.c>".PHP_EOL;
		$htc .= "		SetEnv MOD_REWRITE On".PHP_EOL;
		$htc .= "	</IfModule>".PHP_EOL;
		$htc .= "	RewriteEngine On".PHP_EOL;
		$htc .= "	RewriteBase ".$settings['site_path'].PHP_EOL;
		$htc .= "	# Fix Apache internal dummy connections from breaking [(site_url)] cache".PHP_EOL;
		$htc .= "	RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]".PHP_EOL;
		$htc .= "	RewriteRule .* - [F,L]".PHP_EOL;
		$htc .= "	# Exclude /assets and /manager directories and images from rewrite rules".PHP_EOL;
		$htc .= "	RewriteRule ^(administration|themes)/*$ - [L]".PHP_EOL;
		$htc .= "	RewriteCond %{REQUEST_FILENAME} !-f".PHP_EOL;
		$htc .= "	RewriteCond %{REQUEST_FILENAME} !-d".PHP_EOL;
		$htc .= "	RewriteCond %{REQUEST_FILENAME} !-l".PHP_EOL;
		$htc .= "	RewriteCond %{REQUEST_URI} !^/(administration|config|rewrite.php)".PHP_EOL;
		$htc .= "	RewriteRule ^(.*?)$ rewrite.php [L]".PHP_EOL;
		$htc .= "</IfModule>".PHP_EOL;
	} else {
		// Error pages
		$htc .= "ErrorDocument 400 ".$settings['site_path']."error.php?code=400".PHP_EOL;
		$htc .= "ErrorDocument 401 ".$settings['site_path']."error.php?code=401".PHP_EOL;
		$htc .= "ErrorDocument 403 ".$settings['site_path']."error.php?code=403".PHP_EOL;
		$htc .= "ErrorDocument 404 ".$settings['site_path']."error.php?code=404".PHP_EOL;
		$htc .= "ErrorDocument 500 ".$settings['site_path']."error.php?code=500".PHP_EOL;
	}

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
	}

	if (!defined('FUSION_NULL')) {
		// Everything went as expected
		addNotice("success", "<i class='fa fa-lg fa-check-square-o m-r-10'></i>".$locale['900']);
		redirect(FUSION_SELF.$aidlink);
	}
}

echo openform('settingsseo', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 2));
echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
$locale['seo_htc_warning'] = 'Please note that if you change any of these settings the content of <strong>.htaccess</strong> will be overwritten and any changes previously done to this file will be lost.'; // to be moved
echo "<div class='admin-message alert alert-info'><i class='fa fa-lg fa-exclamation-circle m-r-10'></i>".$locale['seo_htc_warning']."</div>";
$opts = array('0' => $locale['no'], '1' => $locale['yes']);
echo form_checkbox('site_seo', $locale['438'], $settings_seo['site_seo'], array('toggle' => 1, 'inline' => 1));
echo form_checkbox('normalize_seo', $locale['439'], $settings_seo['normalize_seo'], array('toggle' => 1, 'child_of' => 'site_seo', 'inline' => 1));
echo form_checkbox('debug_seo', $locale['440'], $settings_seo['debug_seo'], array('toggle' => 1, 'child_of' => 'site_seo', 'inline' => 1));
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-primary','inline' => 1));
echo "</div></div>\n";
echo closeform();

if (isset($_POST['savepermalinks'])) {
	$error = 0;
	if (isset($_POST['permalink']) && is_array($_POST['permalink'])) {
		$permalinks = stripinput($_POST['permalink']);
		foreach ($permalinks as $key => $value) {
			$result = dbquery("UPDATE ".DB_PERMALINK_METHOD." SET pattern_source='".$value."' WHERE pattern_id='".$key."'");
			if (!$result) {
				$error = 1;
			}
		}
	} else {
		$error = 1;
	}
	if ($error == 0) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['421']."</div></div>\n";
	} elseif ($error == 1) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['420']."</div></div>\n";
	}
}
if (isset($_GET['edit']) && file_exists(INCLUDES."rewrites/".stripinput($_GET['edit'])."_rewrite_include.php")) {
	$rewrite_name = stripinput($_GET['edit']);
	include INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";
	if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
		include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
	}
	if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
		include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
	}
	$rows = dbcount("(rewrite_id)", DB_PERMALINK_REWRITE, "rewrite_name='".$rewrite_name."'");
	if ($rows > 0) {
		$result = dbquery("SELECT p.* FROM ".DB_PERMALINK_REWRITE." r INNER JOIN ".DB_PERMALINK_METHOD." p ON r.rewrite_id=p.pattern_type WHERE r.rewrite_name='".$rewrite_name."'");
		if (dbrows($result)) {
			opentable(sprintf($locale['405'], $permalink_name));
			echo openform('editpatterns', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
			echo "<table cellpadding='0' cellspacing='1' width='100%' class='table table-responsive tbl-border center'>\n";
			if (isset($permalink_tags_desc) && is_array($permalink_tags_desc)) {
				echo "<tr>\n";
				echo "<td class='tbl2' style='white-space:nowrap'><strong>".$locale['406']."</strong></td>\n";
				echo "<td class='tbl2' style='white-space:nowrap'><strong>".$locale['407']."</strong></td>\n";
				echo "</tr>\n";
				foreach ($permalink_tags_desc as $tag => $desc) {
					echo "<tr>\n";
					echo "<td class='tbl1' style='white-space:nowrap'>".$tag."</td>\n";
					echo "<td class='tbl1' style='white-space:nowrap'>".$desc."</td>\n";
					echo "</tr>\n";
				}
			}
			echo "<tr>\n";
			echo "<td class='tbl2'><strong>".$locale['408']."</strong></td>\n";
			echo "<td class='tbl2'><strong>".$locale['409']."</strong></td>\n";
			echo "</tr>\n";
			$i = 1;
			while ($data = dbarray($result)) {
				echo "<tr>\n";
				echo "<td class='tbl1' style='white-space:nowrap'>".sprintf($locale['410'], $i)."</td>\n";
				echo "<td class='tbl1' style='white-space:nowrap'><input type='text' class='textbox' value='".$data['pattern_source']."' name='permalink[".$data['pattern_id']."]' style='width: 500px;' />\n";
				add_to_head("<style type='text/css'>
                    .redtxt {
                        color: #ff0000;
                    }
                    </style>");
				$source = preg_replace("/%(.*?)%/i", "<span class='redtxt'>%$1%</span>", $data['pattern_source']);
				$target = preg_replace("/%(.*?)%/i", "<span class='redtxt'>%$1%</span>", $data['pattern_target']);
				echo "<br /><br />(".$source.")\n";
				echo "<br />(".$target.")</td>\n";
				echo "</tr>\n";
				$i++;
			}
			echo "<tr>\n";
			echo "<td class='tbl2'></td>\n";
			echo "<td class='tbl2'><input type='submit' value='".$locale['413']."' class='button' name='savepermalinks' /></td>\n";
			echo "</tr>\n";
			echo "</tbody>\n</table>\n";
			echo closeform();
			closetable();
		} else {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".sprintf($locale['422'], $permalink_name)."</div></div>\n";
		}
	} else {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['423']."</div></div>\n";
	}
} elseif (isset($_GET['enable']) && file_exists(INCLUDES."rewrites/".stripinput($_GET['enable'])."_rewrite_include.php")) {
	$rewrite_name = stripinput($_GET['enable']);
	include INCLUDES."rewrites/".$rewrite_name."_rewrite_include.php";
	if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
		include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
	}
	if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
		include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
	}
	$rows = dbcount("(rewrite_id)", DB_PERMALINK_REWRITE, "rewrite_name='".$rewrite_name."'");
	// If the Rewrite doesn't already exist
	if ($rows == 0) {
		$error = 0;
		$result = dbquery("INSERT INTO ".DB_PERMALINK_REWRITE." (rewrite_name) VALUES ('".$rewrite_name."')");
		if (!$result) {
			$error = 1;
		}
		$last_insert_id = dblastid();
		if (isset($pattern) && is_array($pattern)) {
			foreach ($pattern as $source => $target) {
				$result = dbquery("INSERT INTO ".DB_PERMALINK_METHOD." (pattern_type, pattern_source, pattern_target, pattern_cat) VALUES ('".$last_insert_id."', '".$source."', '".$target."', 'normal')");
				if (!$result) {
					$error = 1;
				}
			}
		}
		if (isset($alias_pattern) && is_array($alias_pattern)) {
			foreach ($alias_pattern as $source => $target) {
				$result = dbquery("INSERT INTO ".DB_PERMALINK_METHOD." (pattern_type, pattern_source, pattern_target, pattern_cat) VALUES ('".$last_insert_id."', '".$source."', '".$target."', 'alias')");
				if (!$result) {
					$error = 1;
				}
			}
		}
		if ($error == 0) {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".sprintf($locale['424'], $permalink_name)."</div></div>\n";
		} elseif ($error == 1) {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['420']."</div></div>\n";
		}
	} else {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".sprintf($locale['425'], $permalink_name)."</div></div>\n";
	}
	redirect(FUSION_SELF.$aidlink."&amp;error=0");
} elseif (isset($_GET['disable'])) {
	$rewrite_name = stripinput($_GET['disable']);
	if (file_exists(LOCALE.LOCALESET."permalinks/".$rewrite_name.".php")) {
		include LOCALE.LOCALESET."permalinks/".$rewrite_name.".php";
	}
	if (file_exists(INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php")) {
		include INCLUDES."rewrites/".$rewrite_name."_rewrite_info.php";
	}
	$permalink_name = isset($permalink_name) ? $permalink_name : "";
	// Delete Data
	$rewrite_id = dbarray(dbquery("SELECT rewrite_id FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_name='".$rewrite_name."' LIMIT 1"));
	$result = dbquery("DELETE FROM ".DB_PERMALINK_REWRITE." WHERE rewrite_id=".$rewrite_id['rewrite_id']);
	$result = dbquery("DELETE FROM ".DB_PERMALINK_METHOD." WHERE pattern_type=".$rewrite_id['rewrite_id']);
	if ($result) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".sprintf($locale['426'], $permalink_name)."</div></div>\n";
	}
	redirect(FUSION_SELF.$aidlink."&amp;error=0");
}
$available_rewrites = array();
$enabled_rewrites = array();
if ($temp = opendir(INCLUDES."rewrites/")) {
	while (FALSE !== ($file = readdir($temp))) {
		if (!in_array($file, array("..", ".", "index.php")) && !is_dir(INCLUDES."rewrites/".$file)) {
			if (preg_match("/_rewrite_include\.php$/i", $file)) {
				$rewrite_name = str_replace("_rewrite_include.php", "", $file);
				$available_rewrites[] = $rewrite_name;
				unset($rewrite_name);
			}
		}
	}
	closedir($temp);
}
sort($available_rewrites);
opentable($locale['400']);
echo "<table cellpadding='0' width='100%' class='table table-responsive tbl-border center'>\n<tbody>\n<tr>\n";
$result = dbquery("SELECT * FROM ".DB_PERMALINK_REWRITE." ORDER BY rewrite_name ASC");
if (dbrows($result)) {
	echo "<tr>\n";
	echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['402']."</strong></td>\n";
	echo "<td class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></td>\n";
	echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></td>\n";
	echo "</tr>\n";
	while ($data = dbarray($result)) {
		$enabled_rewrites[] = $data['rewrite_name'];
		echo "<tr>\n";
		if (!file_exists(INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_include.php") || !file_exists(INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_info.php") || !file_exists(LOCALE.LOCALESET."permalinks/".$data['rewrite_name'].".php")) {
			echo "<td colspan='2' class='tbl1'><span style='font-weight:bold;'>".$locale['411'].":</span> ".sprintf($locale['412'], $data['rewrite_name'])."</td>\n";
		} else {
			include LOCALE.LOCALESET."permalinks/".$data['rewrite_name'].".php";
			include INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_include.php";
			include INCLUDES."rewrites/".$data['rewrite_name']."_rewrite_info.php";
			echo "<td width='1%' class='tbl1'>".$permalink_name."</td>\n";
			echo "<td class='tbl1'>".$permalink_desc."</td>\n";
		}
		echo "<td class='tbl1' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;edit=".$data['rewrite_name']."'>".$locale['404c']."</a> - <a onclick=\"return confirm('".$locale['414']."');\" href='".FUSION_SELF.$aidlink."&amp;disable=".$data['rewrite_name']."'>".$locale['404b']."</a></td>\n";
		echo "</tr>\n";
	}
} else {
	echo "<td align='center' class='tbl1'>".$locale['427']."</td>\n</tr>\n";
}
echo "</tbody>\n</table>\n";
closetable();
opentable($locale['401']);
echo "<table cellpadding='0' width='100%' class='table table-responsive tbl-border center'>\n<tbody>\n<tr>\n";
if (count($available_rewrites) != count($enabled_rewrites)) {
	echo "<tr>\n";
	echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['402']."</strong></td>\n";
	echo "<td class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></td>\n";
	echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></td>\n";
	echo "</tr>\n";
	$k = 0;
	foreach ($available_rewrites as $available_rewrite) {
		if (!in_array($available_rewrite, $enabled_rewrites)) {
			if (file_exists(INCLUDES."rewrites/".$available_rewrite."_rewrite_info.php") && file_exists(LOCALE.LOCALESET."permalinks/".$available_rewrite.".php")) {
				include LOCALE.LOCALESET."permalinks/".$available_rewrite.".php";
				include INCLUDES."rewrites/".$available_rewrite."_rewrite_info.php";
				echo "<tr>\n";
				echo "<td width='1%' class='tbl1' style='white-space:nowrap'>".$permalink_name."</td>\n";
				echo "<td class='tbl1' style='white-space:nowrap'>".$permalink_desc."</td>\n";
				echo "<td width='1%' class='tbl1' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;enable=".$available_rewrite."'>".$locale['404a']."</td>\n";
				echo "</tr>\n";
			}
		}
	}
}
echo "</tbody>\n</table>\n";
closetable();
require_once THEMES."templates/footer.php";
?>