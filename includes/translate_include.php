<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: translate_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

/**
 * Returns a string of quantitative sentence from an array
 *
 * @param array $words
 *
 * @return string - orange, banana and apples
 */
function format_sentence(array $words) {
    if (!empty($words)) {
        $string = $words[0];
        $array_count = count($words);
        if ($array_count > 1) {
            $partial = array_slice($words, 0, $array_count - 1);
            $string = implode(", ", $partial)." ".fusion_get_locale("and")." ".$words[$array_count - 1];
        }
        return $string;
    }
    return (string)"";
}
