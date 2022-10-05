<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: sendmail_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

/**
 * Automatic consolidator to be used to send_email within PHPFusion CMS
 *
 * @param       $template_key
 * @param       $recipient_name
 * @param       $recipient_email
 * @param       $subject
 * @param       $body
 * @param array $options
 *
 * @return bool
 */
function fusion_sendmail($template_key, $recipient_name, $recipient_email, $subject, $body, array $options = []) {

    $options += [
        'user_name'    => $recipient_name, // any given username
        'sender_name'  => fusion_get_settings('sitename'),
        'sender_email' => fusion_get_settings('siteemail'),
        'replace'      => [],
    ];

    try {
        $result = dbquery("SELECT template_key, template_active FROM ".DB_EMAIL_TEMPLATES." WHERE template_key=:key LIMIT 1", [
            ':key' => $template_key
        ]);
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($data['template_active'] == "1") {
                print_p('ahoy');
                return sendmail_template($template_key, $recipient_name, $recipient_email, $options['sender_name'], $options['sender_email'], $options['replace']);
            }
        }

        if (!empty($options['replace'])) {
            $subject = strtr($subject, $options['replace']);
            $body = strtr($body, $options['replace']);
        }

        return sendemail($recipient_name, $recipient_email, $options['sender_name'], $options['sender_email'], $subject, $body);
    } catch (Exception $e) {
        set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
    }
    return FALSE;
}

if (!function_exists('sendemail')) {
    /**
     * Send email via PHPMailer Class
     *
     * @param string $toname    The name of the receiver.
     * @param string $toemail   The mail of the receiver.
     * @param string $fromname  Sender's name.
     * @param string $fromemail Sender's email.
     * @param string $subject   Email subject.
     * @param string $message   Email message.
     * @param string $type      Text type. Possible value: text, html.
     * @param string $cc        Carbon copy, whom do you want to send copies of this mail to.
     * @param string $bcc       Blind carbon copy, this receiver will not be able to see from
     *                          whom this mail has been sent to others than the receiver.
     *
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    function sendemail($toname, $toemail, $fromname, $fromemail, $subject, $message, $type = "html", $cc = "", $bcc = "") {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();

        require_once CLASSES.'PHPMailer/PHPMailer.php';
        require_once CLASSES.'PHPMailer/Exception.php';
        require_once CLASSES.'PHPMailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();

        if (file_exists(LOCALE.LOCALESET."includes/classes/PHPMailer/language/phpmailer.lang-".$locale['phpmailer'].".php")) {
            $mail->setLanguage($locale['phpmailer'], LOCALE.LOCALESET."includes/classes/PHPMailer/language/");
        } else {
            $mail->setLanguage("en", LOCALE.LOCALESET."includes/classes/PHPMailer/language/");
        }
        if (!$settings['smtp_host']) {
            $mail->isMAIL();
        } else {
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->Port = $settings['smtp_port'];
            $mail->SMTPAuth = $settings['smtp_auth'];
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
        }
        $mail->CharSet = $locale['charset'];
        $mail->From = $fromemail;
        $mail->FromName = $fromname;
        $mail->addAddress($toemail, $toname);
        $mail->addReplyTo($fromemail, $fromname);
        if ($cc) {
            $cc = explode(", ", $cc);
            foreach ($cc as $ccaddress) {
                $mail->addCC($ccaddress);
            }
        }
        if ($bcc) {
            $bcc = explode(", ", $bcc);
            foreach ($bcc as $bccaddress) {
                $mail->addBCC($bccaddress);
            }
        }
        if ($type == "plain") {
            $mail->isHTML(FALSE);
        } else {
            $mail->isHTML(TRUE);
        }
        $mail->Subject = $subject;
        $mail->Body = $message;
        if (!$mail->send()) {
            $mail->clearAllRecipients();
            $mail->clearReplyTos();

            return FALSE;
        } else {
            $mail->clearAllRecipients();
            $mail->clearReplyTos();

            return TRUE;
        }
    }
}

if (!function_exists('sendmail_template')) {
    /**
     * @param        $template_key
     * @param        $name
     * @param        $email
     * @param string $sender_name
     * @param string $sender_email
     * @param array  $replace
     *
     * @return bool
     */
    function sendmail_template($template_key, $name, $email, string $sender_name = "", string $sender_email = "", array $replace = []) {
        $settings = fusion_get_settings();
        $data = dbarray(dbquery("SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_key=:template AND template_language=:lang LIMIT 1", [':template' => $template_key, ':lang' => LANGUAGE]));

        $replace += [
            "[SITENAME]" => $settings['sitename'],
            "[SITEURL]"  => $settings['siteurl'],
            "[SENDER]"   => $sender_name,
            "[RECEIVER]" => $name,
        ];

        $message_subject = strtr($data['template_subject'], $replace);
        $message_content = strtr($data['template_content'], $replace);
        $template_format = $data['template_format'];
        $sender_name = ($_sender ?? $data['template_sender_name']);
        $sender_email = ($_sender_email ?? $data['template_sender_email']);

        if ($template_format == "html") {
            $message_content = nl2br(html_entity_decode($message_content));
        }

        if (sendemail($name, $email, $sender_name, $sender_email, $message_subject, $message_content, $template_format)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}


if (!function_exists('sendemail_template')) {

    // We need to refactor the parameters
    /**
     * Send email with template
     *
     * @param string $template_key  Template key.
     * @param string $subject       Email subject.
     * @param string $message       Email message.
     * @param string $user          Username.
     * @param string $receiver      The name of the receiver.
     * @param string $thread_url    Forum thread url.
     * @param string $toemail       The mail of the receiver.
     * @param string $_sender       Sender's name (optional)
     * @param string $_sender_email Sender's email. (optional)
     *
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    function sendemail_template($template_key, $subject, $message, $user, $receiver, $thread_url, $toemail, $_sender = "", $_sender_email = "") {
        $settings = fusion_get_settings();

        $data = dbarray(dbquery("SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='".$template_key."' LIMIT 1"));
        $message_subject = $data['template_subject'];
        $message_content = $data['template_content'];
        $template_format = $data['template_format'];
        $sender_name = ($_sender ?? $data['template_sender_name']);
        $sender_email = ($_sender_email ?? $data['template_sender_email']);
        // da fuck is this limitations?

        $subject_search_replace = [
            "[SUBJECT]"  => $subject,
            "[SITENAME]" => $settings['sitename'],
            "[SITEURL]"  => $settings['siteurl'],
            "[USER]"     => $user,
            "[SENDER]"   => $sender_name,
            "[RECEIVER]" => $receiver
        ];
        $message_search_replace = [
            "[SUBJECT]"    => $subject,
            "[SITENAME]"   => $settings['sitename'],
            "[SITEURL]"    => $settings['siteurl'],
            "[MESSAGE]"    => $message,
            "[USER]"       => $user,
            "[SENDER]"     => $sender_name,
            "[RECEIVER]"   => $receiver,
            "[THREAD_URL]" => $thread_url
        ];
        foreach ($subject_search_replace as $search => $replace) {
            $message_subject = str_replace($search, $replace, $message_subject);
        }
        foreach ($message_search_replace as $search => $replace) {
            $message_content = str_replace($search, $replace, $message_content);
        }
        if ($template_format == "html") {
            $message_content = nl2br(html_entity_decode($message_content));
        }
        if (sendemail($receiver, $toemail, $sender_name, $sender_email, $message_subject, $message_content, $template_format)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
