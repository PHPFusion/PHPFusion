<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: translate_include.php
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

/**
 * Returns a grammatical number word.
 *
 * @param int    $count Number of items.
 * @param string $words A string consisting of singular and plural delimited by a | symbol.
 * @param array  $options
 *
 * @return string
 */
function format_word($count, $words, $options = []) {
    return PHPFusion\Locale::formatWord($count, $words, $options);
}

/**
 * Translate locale folder name into localized language.
 *
 * @param string $language
 *
 * @return array|string
 */
function translate_lang_names($language) {
    return PHPFusion\Locale::translateLangNames($language);
}

/**
 * Given English as base, find out the localized version.
 *
 * @param $country
 *
 * @return string
 */
function translate_country_names($country) {
    return PHPFusion\Locale::translateCountryNames($country);
}

/**
 * Returns a string of quantitative sentence from an array.
 *
 * @param array $words
 *
 * @return string E.g. orange, banana and apples
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

    return NULL;
}
