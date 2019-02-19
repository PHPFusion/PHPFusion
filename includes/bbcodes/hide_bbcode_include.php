<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: hide_bbcode_include.php
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

if (iSUPERADMIN || iADMIN) {
    $text = preg_replace('#\[hide\](.*?)\[/hide\]#si',
        '<div class=\'quote\'><strong>'.$locale['bb_hide'].'</strong><br /><span style=\'color:red;font-weight:bold\'>\1</span></div>',
        $text);
} else {
    $text = preg_replace('#\[hide\](.*?)\[/hide\]#si', '', $text);
}
