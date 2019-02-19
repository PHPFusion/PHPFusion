<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: language_bbcode_include.php
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

$language_opts = fusion_get_enabled_languages();
$enabled_languages = array_keys($language_opts);

foreach ($enabled_languages as $language) {

    if (LANGUAGE == $language) {
        $text = preg_replace('#\['.$language.'\](.*?)\[/'.$language.'\]#si', '\1', $text);
    } else {
        $text = preg_replace('#\['.$language.'\](.*?)\[/'.$language.'\]#si', '', $text);
    }

}
