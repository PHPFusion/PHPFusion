<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: contact.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."contact.php";

add_to_title($locale['global_200'].$locale['400']);

if (isset($_POST['sendmessage'])) {
	$error = "";
	$mailname = isset($_POST['mailname']) ? substr(stripinput(trim($_POST['mailname'])), 0, 50) : "";
	$email = isset($_POST['email']) ? substr(stripinput(trim($_POST['email'])), 0, 100) : "";
	$subject = isset($_POST['subject']) ? substr(str_replace(array("\r","\n","@"), "", descript(stripslash(trim($_POST['subject'])))), 0, 50) : "";
	$message = isset($_POST['message']) ? descript(stripslash(trim($_POST['message']))) : "";
	if ($mailname == "") {
		$error .= " <span class='alt'>".$locale['420']."</span><br />\n";
	}
	if ($email == "" || !preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
		$error .= " <span class='alt'>".$locale['421']."</span><br />\n";
	}
	if ($subject == "") {
		$error .= " <span class='alt'>".$locale['422']."</span><br />\n";
	}
	if ($message == "") {
		$error .= " <span class='alt'>".$locale['423']."</span><br />\n";
	}
	$_CAPTCHA_IS_VALID = false;
	include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
	if ($_CAPTCHA_IS_VALID == false) {
		$error .= " <span class='alt'>".$locale['424']."</span><br />\n";
	}
	if (!$error) {
		require_once INCLUDES."sendmail_include.php";
		if (!sendemail($settings['siteusername'],$settings['siteemail'],$mailname,$email,$subject,$message)) {
			$error .= " <span class='alt'>".$locale['425']."</span><br />\n";
		}
	}
	if ($error) {
		opentable($locale['400']);
		echo "<div style='text-align:center'><br />\n".$locale['442']."<br /><br />\n".$error."<br />\n".$locale['443']."</div><br />\n";
		closetable();
	} else {
		opentable($locale['400']);
		echo "<div style='text-align:center'><br />\n".$locale['440']."<br /><br />\n".$locale['441']."</div><br />\n";
		closetable();
	}
} else {
	opentable($locale['400']);
	echo $locale['401']."<br /><br />\n";
	echo "<form name='userform' method='post' action='".FUSION_SELF."'>\n";
	echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['402']."</td>\n";
	echo "<td class='tbl'><input type='text' name='mailname' maxlength='50' class='textbox' style='width: 200px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['403']."</td>\n";
	echo "<td class='tbl'><input type='text' name='email' maxlength='100' class='textbox' style='width: 200px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['404']."</td>\n";
	echo "<td class='tbl'><input type='text' name='subject' maxlength='50' class='textbox' style='width: 200px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['405']."</td>\n";
	echo "<td class='tbl'><textarea name='message' rows='10' class='textbox' cols='50'></textarea></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td width='100' class='tbl'>".$locale['407']."</td>\n";
	echo "<td class='tbl'>";
	include INCLUDES."captchas/".$settings['captcha']."/captcha_display.php";
	if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
		echo "</td>\n</tr>\n<tr>";
		echo "<td class='tbl'><label for='captcha_code'>".$locale['408']."</label></td>\n";
		echo "<td class='tbl'>";
		echo "<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />";
	}
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input type='submit' name='sendmessage' value='".$locale['406']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>