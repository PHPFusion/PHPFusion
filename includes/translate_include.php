<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: translate_include.php
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function format_word($count, $words, $options = []) {
    return PHPFusion\Locale::format_word($count, $words, $options);
}

function translate_lang_names($language) {
    return PHPFusion\Locale::translate_lang_names($language);
}

function translate_country_names($country) {
    return PHPFusion\Locale::translate_country_names($country);
}
