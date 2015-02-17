<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_blog.php
| Author: Starefossen
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

if (!checkRights("S13") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}

if (isset($_POST['savesettings'])) {
	$error = 0;
	$blog_image_link = form_sanitizer($_POST['blog_image_link'], '0', 'blog_image_link');
	$blog_image_frontpage = form_sanitizer($_POST['blog_image_frontpage'], '0', 'blog_image_frontpage');
	$blog_image_readmore = form_sanitizer($_POST['blog_image_readmore'], '0', 'blog_image_readmore');
	$blog_thumb_ratio = form_sanitizer($_POST['blog_thumb_ratio'], '0', 'blog_thumb_ratio');
	$blog_thumb_w = form_sanitizer($_POST['blog_thumb_w'], '300', 'blog_thumb_w');
	$blog_thumb_h = form_sanitizer($_POST['blog_thumb_h'], '150', 'blog_thumb_h');
	$blog_photo_w = form_sanitizer($_POST['blog_photo_w'], '400', 'blog_photo_w');
	$blog_photo_h = form_sanitizer($_POST['blog_photo_h'], '300', 'blog_photo_h');
	$blog_photo_max_w = form_sanitizer($_POST['blog_photo_max_w'], '1800', 'blog_photo_max_w');
	$blog_photo_max_h = form_sanitizer($_POST['blog_photo_max_h'], '1600', 'blog_photo_max_h');
	$blog_photo_max_b = form_sanitizer($_POST['calc_b'], '150', 'calc_b')*form_sanitizer($_POST['calc_c'], '100000', 'calc_c');
	if (!defined('FUSION_NULL')) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_image_link' WHERE settings_name='blog_image_link'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_image_frontpage' WHERE settings_name='blog_image_frontpage'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_image_readmore' WHERE settings_name='blog_image_readmore'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_thumb_ratio' WHERE settings_name='blog_thumb_ratio'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_thumb_w' WHERE settings_name='blog_thumb_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_thumb_h' WHERE settings_name='blog_thumb_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_photo_w' WHERE settings_name='blog_photo_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_photo_h' WHERE settings_name='blog_photo_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_photo_max_w' WHERE settings_name='blog_photo_max_w'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_photo_max_h' WHERE settings_name='blog_photo_max_h'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$blog_photo_max_b' WHERE settings_name='blog_photo_max_b'");
		if (!$result) {
			$error = 1;
		}
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	}
}

$settings2 = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
	$settings2[$data['settings_name']] = $data['settings_value'];
}

opentable($locale['400']);
echo openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 1));
echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
$opts = array('0' => $locale['952'], '1' => $locale['953b']);
echo form_select($locale['951'], 'blog_image_link', 'blog_image_link', $opts, $settings2['blog_image_link']);
$cat_opts = array('0' => $locale['959'], '1' => $locale['960']);
echo form_select($locale['957'], 'blog_image_frontpage', 'blog_image_frontpage', $cat_opts, $settings2['blog_image_frontpage']);
echo form_select($locale['958'], 'blog_image_readmore', 'blog_image_readmore', $cat_opts, $settings2['blog_image_readmore']);
$opts = array('0' => $locale['955'], '1' => $locale['956']);
echo form_select($locale['954'], 'blog_thumb_ratio', 'blog_thumb_ratio', $opts, $settings2['blog_thumb_ratio']);
echo "<div class='clearfix'>\n";
echo "<label for='blog_thumb_w'>".$locale['601']."</label> <span class='required'>*</span>\n<br /><span class='small2'>".$locale['604']."</span><br/>\n";
echo form_text('', 'blog_thumb_w', 'blog_thumb_w', $settings2['blog_thumb_w'], array('class' => 'pull-left', 'max_length' => 3));
echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
echo form_text('', 'blog_thumb_h', 'blog_thumb_h', $settings2['blog_thumb_h'], array('class' => 'pull-left', 'max_length' => 3));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo "<label for='blog_photo_w'>".$locale['602']."</label> <span class='required'>*</span>\n<br /><span class='small2'>".$locale['604']."</span>\n<br/>\n";
echo form_text('', 'blog_photo_w', 'blog_photo_w', $settings2['blog_photo_w'], array('class' => 'pull-left', 'max_length' => 3));
echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
echo form_text('', 'blog_photo_h', 'blog_photo_h', $settings2['blog_photo_h'], array('class' => 'pull-left', 'max_length' => 3));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo "<label for='blog_photo_max_w'>".$locale['603']."</label> <span class='required'>*</span>\n<br /><span class='small2'>".$locale['604']."</span>\n<br/>\n";
echo form_text('', 'blog_photo_max_w', 'blog_photo_max_w', $settings2['blog_photo_max_w'], array('class' => 'pull-left', 'max_length' => 4));
echo "<i class='entypo icancel pull-left m-r-10 m-l-10 m-t-10'></i>\n";
echo form_text('', 'blog_photo_max_h', 'blog_photo_max_h', $settings2['blog_photo_max_h'], array('class' => 'pull-left', 'max_length' => 4));
echo "</div>\n";
echo "<div class='clearfix'>\n";
echo "<label for='calc_c'>".$locale['605']."</label> <span class='required'>*</span>\n<br/>\n";
function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}

$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
$calc_c = calculate_byte($settings2['blog_photo_max_b']);
$calc_b = $settings2['blog_photo_max_b']/$calc_c;
echo form_text('', 'calc_b', 'calc_b', $calc_b, array('required' => 1, 'number' => 1, 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => '3', 'class' => 'pull-left m-r-10'));
echo form_select('', 'calc_c', 'calc_c', $calc_opts, $calc_c, array('placeholder' => $locale['choose'], 'class' => 'pull-left', 'width' => '180px'));
echo "</div>\n";
echo "</div>\n</div>\n";
echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
echo closeform();
closetable();

require_once THEMES."templates/footer.php";
?>
