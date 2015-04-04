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
pageAccess('S9');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
add_to_breadcrumbs(array('link'=>ADMIN."settings_security.php".$aidlink, 'title'=>$locale['security_settings']));
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
		$privacy_policy = addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['privacy_policy']));
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['flood_interval']) ? $_POST['flood_interval'] : "15")."' WHERE settings_name='flood_interval'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(isnum($_POST['flood_autoban']) ? $_POST['flood_autoban'] : "1")."' WHERE settings_name='flood_autoban'");
		if (!$result) {
			$error = 1;
		}
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".(is_numeric($_POST['maintenance_level']) ? $_POST['maintenance_level'] : "102")."' WHERE settings_name='maintenance_level'");
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
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$privacy_policy' WHERE settings_name='privacy_policy'");
		if (!$result) {
			$error = 1;
		}
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	}
}
opentable($locale['683']);

if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
	if ($_GET['error'] == 0) {
		$message = $locale['900'];
	} elseif ($_GET['error'] == 1) {
		$message = $locale['901'];
	} elseif ($_GET['error'] == 2) {
		$message = $locale['696'];
	}
	if (isset($message)) {
		echo admin_message($message);
	}
}
echo "<div class='well'>".$locale['security_description']."</div>\n";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));

echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo form_select($locale['693'], 'captcha', 'captcha', $available_captchas, $settings['captcha'], array('inline'=>1));
echo "<div class='recaptcha_keys' ".($settings['captcha'] !== 'recaptcha' ? "class='display-none'" : '')." style='margin-top:5px;'>\n";
echo "<label class='m-t-10' for='recaptcha_public'>".$locale['694']."</label>\n";
echo form_text('recaptcha_public', '', $settings['recaptcha_public']);
echo form_text('recaptcha_private', $locale['695'], $settings['recaptcha_private']);
$theme_opts = array('red' => $locale['697r'], 'blackglass' => $locale['697b'], 'clean' => $locale['697c'], 'white' => $locale['697w'],);
echo form_select($locale['697'], 'recaptcha_theme', 'recaptcha_theme', $theme_opts, $settings['recaptcha_theme']);
echo "</div>\n";
closeside();
openside('');
$level_array = array(USER_LEVEL_ADMIN => $locale['676'], USER_LEVEL_SUPER_ADMIN => $locale['677'], USER_LEVEL_MEMBER => $locale['678']);
echo form_select($locale['675'], 'maintenance_level', 'maintenance_level', $level_array, $settings['maintenance_level'], array('inline'=>1, 'width'=>'100%'));
$opts = array('1' => $locale['502'], '0' => $locale['503']);
echo form_select($locale['657'], 'maintenance', 'maintenance', $opts, $settings['maintenance'], array('inline'=>1, 'width'=>'100%'));
echo form_textarea('maintenance_message', $locale['658'], $settings['maintenance_message'], array('autosize'=>1));
closeside();
openside('');
echo form_textarea('privacy_policy', $locale['820'], $settings['privacy_policy'], array('autosize'=>1, 'form_name'=>'settingsform', 'html'=>!$settings['tinymce_enabled'] ? 1 : 0));
closeside();
echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
$flood_opts = array('1' => $locale['502'], '0' => $locale['503']);
echo form_text('flood_interval', $locale['660'], $settings['flood_interval'], array('max_length' => 2));
echo form_select($locale['680'], 'flood_autoban', 'flood_autoban', $flood_opts, $settings['flood_autoban'], array('width'=>'100%'));
closeside();
openside('');
$yes_no_array = array('1' => $locale['518'], '0' => $locale['519']);
echo form_select($locale['659'], 'bad_words_enabled', 'bad_words_enabled', $yes_no_array, $settings['bad_words_enabled']);
echo form_text('bad_word_replace', $locale['654'], $settings['bad_word_replace']);
echo form_textarea('bad_words', $locale['651'], $settings['bad_words'], array('placeholder'=>$locale['652'], 'autosize'=>1));
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success'));
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