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

use PHPFusion\Locale;

/**
 * Get locales.
 *
 * @param string $key The key of one locale
 * @param array|string $include_file The full path of the file which to be included.
 *
 * @return string|array Associative array of locales or one locale by key.
 */
function fusion_get_locale( $key = NULL, $include_file = '' ) {
    $locale = Locale::getInstance();
    if ($include_file) {
        $locale::setLocale( $include_file );
    }

    return $locale->getLocale( $key );
}

/**
 * Add a file into the locale cache
 *
 * @param $locale_file
 */
function fusion_set_locale( $locale_file ) {
    Locale::getInstance()::setLocale( $locale_file );
}

/**
 * Get the locale file name for infusions
 *
 * @param string $locale_file
 * @param string $locale_folder
 * @param bool $localeset_folder
 * @param string $default_lang
 *
 * @return string
 */
function fusion_get_inf_locale_path( $locale_file, $locale_folder, $localeset_folder = TRUE, $default_lang = 'English' ) {
    return Locale::getInstance()->getInfLocaleFiles( $locale_file, $locale_folder, $localeset_folder, $default_lang );
}

/**
 * Returns a grammatical number word.
 *
 * @param int $count Number of items.
 * @param string $words A string consisting of singular and plural delimited by a | symbol.
 * @param array $options
 *
 * @return string
 */
function format_word( $count, $words, $options = [] ) {
    return Locale::formatWord( $count, $words, $options );
}

/**
 * Translate locale folder name into localized language.
 *
 * @param string $language
 *
 * @return array|string
 */
function translate_lang_names( $language = NULL ) {
    return Locale::translateLangNames( $language );
}

/**
 * Given English as base, find out the localized version.
 *
 * @param $country
 *
 * @return string
 */
function translate_country_names( $country ) {
    return Locale::translateCountryNames( $country );
}

/**
 * Returns a string of quantitative sentence from an array.
 *
 * @param array $words
 *
 * @return string E.g. orange, banana and apples
 */
function format_sentence( array $words ) {
    if (!empty( $words )) {
        $string = $words[0];
        $array_count = count( $words );
        if ($array_count > 1) {
            $partial = array_slice( $words, 0, $array_count - 1 );
            $string = implode( ", ", $partial ) . " " . fusion_get_locale( 'and' ) . " " . $words[$array_count - 1];
        }
        return $string;
    }

    return NULL;
}

/**
 * Parsing the correct label language. Requires $value to be serialized value
 *
 * @param $str
 *
 * @return string
 * @todo: remove & refactor QuantumHelper::parseLabel()
 */
function parse_label( $str ) {
    return Locale::parseLabel( $str );
}