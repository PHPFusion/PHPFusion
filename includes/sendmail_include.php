<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: sendmail_include.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
/**
 * Send Email via PHPMailer Class
 *
 * @param        $toname
 * @param        $toemail
 * @param        $fromname
 * @param        $fromemail
 * @param        $subject
 * @param        $message
 * @param string $type
 * @param string $cc
 * @param string $bcc
 *
 * @return bool
 */
function sendemail($toname, $toemail, $fromname, $fromemail, $subject, $message, $type = "plain", $cc = "", $bcc = "") {
    $settings = fusion_get_settings();
    $locale = fusion_get_locale();
    require_once CLASSES."PHPMailer/PHPMailerAutoload.php";
    $mail = new PHPMailer();
    if (file_exists(CLASSES."PHPMailer/language/phpmailer.lang-".$locale['phpmailer'].".php")) {
        $mail->SetLanguage($locale['phpmailer'], CLASSES."PHPMailer/language/");
    } else {
        $mail->SetLanguage("en", CLASSES."PHPMailer/language/");
    }
    if (!$settings['smtp_host']) {
        $mail->IsMAIL();
    } else {
        $mail->IsSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->Port = $settings['smtp_port'];
        $mail->SMTPAuth = $settings['smtp_auth'] ? TRUE : FALSE;
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
        $mail->IsHTML(FALSE);
    } else {
        $mail->IsHTML(TRUE);
    }
    $mail->Subject = $subject;
    $mail->Body = $message;
    if (!$mail->Send()) {
        $mail->ErrorInfo;
        $mail->ClearAllRecipients();
        $mail->ClearReplyTos();

        return FALSE;
    } else {
        $mail->ClearAllRecipients();
        $mail->ClearReplyTos();

        return TRUE;
    }
}

/**
 * Template
 *
 * @param        $template_key
 * @param        $subject
 * @param        $message
 * @param        $user
 * @param        $receiver
 * @param string $thread_url
 * @param        $toemail
 * @param string $sender
 * @param string $fromemail
 *
 * @return bool
 */
function sendemail_template($template_key, $subject, $message, $user, $receiver, $thread_url = "", $toemail, $sender = "", $fromemail = "") {

    $settings = fusion_get_settings();

    $data = dbarray(dbquery("SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='".$template_key."' LIMIT 1"));
    $message_subject = $data['template_subject'];
    $message_content = $data['template_content'];
    $template_format = $data['template_format'];
    $sender_name = ($sender != "" ? $sender : $data['template_sender_name']);
    $sender_email = ($fromemail != "" ? $fromemail : $data['template_sender_email']);
    $subject_search_replace = array(
        "[SUBJECT]"  => $subject, "[SITENAME]" => $settings['sitename'],
        "[SITEURL]"  => $settings['siteurl'], "[USER]" => $user, "[SENDER]" => $sender_name,
        "[RECEIVER]" => $receiver
    );
    $message_search_replace = array(
        "[SUBJECT]"    => $subject, "[SITENAME]" => $settings['sitename'],
        "[SITEURL]"    => $settings['siteurl'], "[MESSAGE]" => $message, "[USER]" => $user,
        "[SENDER]"     => $sender_name, "[RECEIVER]" => $receiver,
        "[THREAD_URL]" => $thread_url
    );
    foreach ($subject_search_replace as $search => $replace) {
        $message_subject = str_replace($search, $replace, $message_subject);
    }
    foreach ($message_search_replace as $search => $replace) {
        $message_content = str_replace($search, $replace, $message_content);
    }
    if ($template_format == "html") {
        $message_content = nl2br($message_content);
    }
    if (sendemail($receiver, $toemail, $sender_name, $sender_email, $message_subject, $message_content, $template_format)) {
        return TRUE;
    } else {
        return FALSE;
    }
}