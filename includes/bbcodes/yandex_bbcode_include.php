<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: yandex_bbcode_include.php
| Author: Rizado (Chubatyj Vitalij)
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
$text = preg_replace('#\[yandex\](.*?)\[/yandex\]#si',
                     '<img src=\'https://yastatic.net/www/_/x/Q/xk8YidkhGjIGOrFm_dL5781YA.svg\' width=\'28\' height=\'20\' alt=\'Yandex Search\' border=\'0\' style=\'vertical-align:middle;\'> <a href=\'https://yandex.ru/search/?lr=2&amp;noreask=1&amp;text=\1\' target=\'_blank\'>\1</a>',
                     $text);
