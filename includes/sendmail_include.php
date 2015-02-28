<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sendmail_include.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

function sendemail($toname, $toemail, $fromname, $fromemail, $subject, $message, $type = "plain", $cc = "", $bcc = "") {

	global $settings, $locale;
	
	require_once INCLUDES."class.phpmailer.php";
	
	$mail = new PHPMailer();
	if (file_exists(INCLUDES."language/phpmailer.lang-".$locale['phpmailer'].".php")) {
		$mail->SetLanguage($locale['phpmailer'], INCLUDES."language/");
	} else {
		$mail->SetLanguage("en", INCLUDES."language/");
	}

	if (!$settings['smtp_host']) {
		$mail->IsMAIL();
	} else {
		$mail->IsSMTP();
		$mail->Host = $settings['smtp_host'];
		$mail->Port = $settings['smtp_port'];
		$mail->SMTPAuth = $settings['smtp_auth'] ? true : false;
		$mail->Username = $settings['smtp_username'];
		$mail->Password = $settings['smtp_password'];
	}
	
	$mail->CharSet = $locale['charset'];
	$mail->From = $fromemail;
	$mail->FromName = $fromname;
	$mail->AddAddress($toemail, $toname);
	$mail->AddReplyTo($fromemail, $fromname);
	if ($cc) { 
		$cc = explode(", ", $cc);
		foreach ($cc as $ccaddress) {
			$mail->AddCC($ccaddress);
		}
	}
	if ($bcc) {
		$bcc = explode(", ", $bcc);
		foreach ($bcc as $bccaddress) {
			$mail->AddBCC($bccaddress);
		}
	}
	if ($type == "plain") {
		$mail->IsHTML(false);
	} else {
		$mail->IsHTML(true);
	}
	
	$mail->Subject = $subject;
	$mail->Body = $message;
	
	if(!$mail->Send()) {
		$mail->ErrorInfo;
		$mail->ClearAllRecipients();
		$mail->ClearReplyTos();
		return false;
	} else {
		$mail->ClearAllRecipients(); 
		$mail->ClearReplyTos();
		return true;
	}

}
?>
