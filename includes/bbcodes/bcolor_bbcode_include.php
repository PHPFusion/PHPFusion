<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: bcolor_bbcode_include.php
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

$text = preg_replace('#\[bcolor=(black|blue|brown|cyan|gray|green|lime|maroon|navy|olive|orange|purple|red|silver|violet|white|yellow)\](.*?)\[/bcolor\]#si',
    '<span style=\'background-color:\1;padding:2px\'>\2</span>', $text);
$text = preg_replace('#\[bcolor=([\#a-f0-9]*?)\](.*?)\[/bcolor\]#si', '<span style=\'background-color:\1;padding:2px\'>\2</span>', $text);
