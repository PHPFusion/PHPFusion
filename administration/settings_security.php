<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_security.php
| Author: Paul Beuk (muscapaul)
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
if (!checkrights("S9") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
$available_captchas = array();
if ($temp = opendir(INCLUDES."captchas/")) {
	while (FALSE !== ($file = readdir($temp))) {
		if ($file != "." && $file != ".." && is_dir(INCLUDES."captchas/".$file)) {
			$available_captchas[$file] = $file;
		}
	}
}
if (isset($_POST['savesettings'])) {
	$error = 0;
	if (!defined('FUSION_NULL')) {
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['flood_interval']) ? $_POST['flood_interval'] : "15")."' WHERE settings_name='flood_interval'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['flood_autoban']) ? $_POST['flood_autoban'] : "1")."' WHERE settings_name='flood_autoban'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['maintenance_level']) ? $_POST['maintenance_level'] : "102")."' WHERE settings_name='maintenance_level'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['maintenance']) ? $_POST['maintenance'] : "0")."' WHERE settings_name='maintenance'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash(descript($_POST['maintenance_message']))."' WHERE settings_name='maintenance_message'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['bad_words_enabled']) ? $_POST['bad_words_enabled'] : "0")."' WHERE settings_name='bad_words_enabled'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['bad_words'])."' WHERE settings_name='bad_words'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['bad_word_replace'])."' WHERE settings_name='bad_word_replace'");
		if (!$result) {
			$error = 1;
		}
		if ($_POST['captcha'] == "recaptcha" && ($_POST['recaptcha_public'] == "" || $_POST['recaptcha_private'] == "")) {
			$error = 2;
		} else {
			$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['captcha'])."' WHERE settings_name='captcha'");
			if (!$result) {
				$error = 1;
			}
			$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['recaptcha_public'])."' WHERE settings_name='recaptcha_public'");
			if (!$result) {
				$error = 1;
			}
			$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['recaptcha_private'])."' WHERE settings_name='recaptcha_private'");
			if (!$result) {
				$error = 1;
			}
			$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".stripinput($_POST['recaptcha_theme'])."' WHERE settings_name='recaptcha_theme'");
			if (!$result) {
				$error = 1;
			}
		}
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	}
}
if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['696'];
	}
	if (isset($message)) {
		echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
	}
}
opentable($locale['683']);
openform('settingsform', 'settingsform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['692']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'>\n<label for='captcha'>".$locale['693']."</label><br />";
echo "</td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_select('', 'captcha', 'captcha', $available_captchas, $settings['captcha']);
echo "<div class='recaptcha_keys' ".($settings['captcha'] !== 'recaptcha' ? "class='display-none'" : '')." style='margin-top:5px;'>\n";
echo "<label class='m-t-10' for='recaptcha_public'>".$locale['694']."</label>\n";
echo form_text('', 'recaptcha_public', 'recaptcha_public', $settings['recaptcha_public']);
echo form_text($locale['695'], 'recaptcha_private', 'recaptcha_private', $settings['recaptcha_private']);
$theme_opts = array('red' => $locale['697r'], 'blackglass' => $locale['697b'], 'clean' => $locale['697c'],
					'white' => $locale['697w'],);
echo form_select($locale['697'], 'recaptcha_theme', 'recaptcha_theme', $theme_opts, $settings['recaptcha_theme']);
echo "</div>\n";
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['682']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='flood_interval'>".$locale['660']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_text('', 'flood_interval', 'flood_interval', $settings['flood_interval'], array('max_length' => 2));
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='flood_autoban'>".$locale['680']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$flood_opts = array('1' => $locale['502'], '0' => $locale['503']);
echo form_select('', 'flood_autoban', 'flood_autoban', $flood_opts, $settings['flood_autoban']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['687']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='bad_words_enabled'>".$locale['659']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$yes_no_array = array('1' => $locale['518'], '0' => $locale['519']);
echo form_select('', 'bad_words_enabled', 'bad_words_enabled', $yes_no_array, $settings['bad_words_enabled']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'><label for='bad_words'>".$locale['651']."</label><br /><span class='small2'>".$locale['652']."<br />".$locale['653']."</span></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_textarea('', 'bad_words', 'bad_words', $settings['bad_words']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='bad_word_replace'>".$locale['654']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_text('', 'bad_word_replace', 'bad_word_replace', $settings['bad_word_replace']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl2' align='center' colspan='2'><strong>".$locale['681']."</strong></td>\n";
echo "</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='maintenance_level'>".$locale['675']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$level_array = array('102' => $locale['676'], '103' => $locale['677'], '1' => $locale['678']);
echo form_select('', 'maintenance_level', 'maintenance_level', $level_array, $settings['maintenance_level']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='maintenance'>".$locale['657']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
$opts = array('1' => $locale['502'], '0' => $locale['503']);
echo form_select('', 'maintenance', 'maintenance', $opts, $settings['maintenance']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'><label for='maintenance_message'>".$locale['658']."</label></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo form_textarea('', 'maintenance_message', 'maintenance_message', $settings['maintenance_message']);
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo form_button($locale['750'], 'savesettings', 'savesettings', $locale['750'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</tbody>\n</table>\n";
echo closeform();
closetable();
add_to_jquery("
        var init_val = $('#captcha').select2().val();
        if (init_val !== 'recaptcha')  { $('.recaptcha_keys').hide(); }
        $('#captcha').bind('change', function() {
            var val = $(this).select2().val();
            if (val == 'recaptcha') { $('.recaptcha_keys').slideDown('slow'); } else { $('.recaptcha_keys').slideUp('slow'); }
        });
    ");
require_once THEMES."templates/footer.php";
?>