<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: mail_bbcode_include.php
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
defined('IN_FUSION') || exit;

if (!function_exists('replace_mail')) {
    function replace_mail($m) {
        $mail = !empty($m['mail']) ? $m['mail'] : (!empty($m['mail2']) ? $m['mail2'] : $m[0]);
        $subject = !empty($m['subject']) ? $m['subject'] : '';
        $title = !empty($m['title']) ? $m['title'] : $mail;

        return hide_email($mail, $title, $subject);
    }
}

$mail_regex = '[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}';
$text = preg_replace_callback('
~\[mail
(=
    (?P<mail>'.$mail_regex.')
    (;(?P<subject>.*?))?
)?
\]
(?(?='.$mail_regex.') # if followed by
    (?P<mail2>'.$mail_regex.') # then
    |
    (?P<title>.*?)? # else
)
\[\/mail\]
~ix'
    , 'replace_mail', $text);
