<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Locale.php
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

namespace PHPFusion;

/**
 * Class Locale
 *
 * Locale Handling
 * We set a new global.php when the class initialize automatically so this will only be loaded once
 * Implemented at core_functions_include.php
 *
 * @package PHPFusion
 */
class Locale {

    private static $locale_file = [];
    private static $locale = [];
    private static $instances = NULL;

    /**
     * Get locale instance by key
     *
     * @param string $key
     *
     * @return static
     */
    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }

    public static function getLoadedFiles() {
        return self::$locale_file;
    }

    /**
     * Includes a locale file and logs a trace
     *
     * @param string $filename
     */
    public static function loadLocaleFile($filename) {
        $locale = [];

        if (file_exists($filename)) {
            include $filename;
        }

        self::$locale += $locale;
        self::$locale_file[$filename] = debug_backtrace();
    }


    /**
     * Iinclude a new locale file
     *
     * @param string|array $include_file Can be an array or a string
     */
    public static function setLocale($include_file) {
        if (!empty($include_file)) {
            if (is_array($include_file)) {
                foreach ($include_file as $file) {
                    if (!isset(self::$locale_file[$file])) {
                        self::loadLocaleFile($file);
                    }
                }
            } else if (!isset(self::$locale_file[$include_file])) {
                self::loadLocaleFile($include_file);
            }
        }
    }

    /**
     * @param string $key
     *
     * @return array|mixed|string
     */
    public function getLocale($key = NULL) {
        return empty($key) ? self::$locale : (self::$locale[$key] ?? '');
    }

    /**
     * Returns a grammatical number word.
     *
     * @param int    $count Number of items.
     * @param string $words A string consisting of singular and plural delimited by a | symbol.
     * @param array  $options
     *
     * @return string
     */
    public static function formatWord($count, $words, $options = []) {
        $default_options = [
            'add_count'     => TRUE, // Show number.
            'html'          => FALSE, // Encase result with html_template, {%count%} {%result%} tags are used for placeholders for result replacements.
            'html_template' => "<span class='fusion_count'>{%count%}</span> <span class='fusion_word'>{%result%}</span>", // HTML template to be used for output.
            'language'      => LANGUAGE, // Current language.
        ];

        $options += $default_options;

        if (empty($count)) {
            $count = "0";
        }

        // Format the result
        switch ($options['language']) {
            case 'English':
            case 'Danish':
            case 'German':
            case 'Romanian':
                $form = $count == 1 ? 0 : 1;
                $words_array = explode("|", $words);
                $result = !empty($words_array[$form]) ? $words_array[$form] : $words_array[0];
                break;
            case 'Czech':
            case 'Slovak':
                if ($count == 1) {
                    $form = 0;
                } else if (in_array($count, [2, 3, 4])) {
                    $form = 1;
                } else {
                    $form = 2;
                }

                $words_array = explode("|", $words);
                $result = !empty($words_array[$form]) ? $words_array[$form] : $words_array[0];
                break;
            case 'Russian':
            case 'Ukranian':
                $fcount = $count % 100;
                $a = $fcount % 10;
                $b = floor($fcount / 10);
                $form = 2;

                if ($b != 1) {
                    if ($a == 1) {
                        $form = 0;
                    } else if ($a >= 2 && $a <= 4) {
                        $form = 1;
                    }
                }

                $words_array = explode("|", $words);
                $result = !empty($words_array[$form]) ? $words_array[$form] : $words_array[0];
                break;
            default: // never plural language - i.e. chinese is here
                $words_array = explode("|", $words);
                $result = $words_array[0];
        }

        if ($options['add_count']) {
            if ($options['html'] && !empty($options['html_template'])) {
                return strtr($options['html_template'],
                    [
                        "{%count%}"  => $count,
                        "{%result%}" => $result
                    ]
                );
            } else {
                return $count.' '.$result;
            }
        }

        return $result;
    }

    /**
     * Given English as base, find out the localized version
     *
     * @param string $country
     *
     * @return string
     */
    public static function translateCountryNames($country) {
        $translated_countries = [
            "China"           => "中国",
            "Czech Republic"  => "Česko",
            "Denmark"         => "Danmark",
            "Finland"         => "Suomi",
            "Germany"         => "Deutschland",
            "Hong Kong"       => "香港",
            "Hungary"         => "Magyarország",
            "Italy"           => "Italia",
            "Norway"          => "Norge",
            "Poland"          => "Polska",
            "Qazaqstan"       => "Казахстан",
            "Romania"         => "Rom&#226;nia",
            "Russia"          => "Россия",
            "Slovakia"        => "Slovensko",
            "Sweden"          => "Sverige",
            "Taiwan"          => "台湾",
            "The Netherlands" => "Nederland",
            "Ukraine"         => "Україна",
        ];

        if (!empty($translated_countries[$country])) {
            return $translated_countries[$country];
        } else {
            return $country;
        }
    }

    /**
     * Attempt to translate Locale Folder Name into Localized language
     * or return the locale folder name by default.
     *
     * If key is not set, return a full array
     *
     * @param string $key
     *
     * @return array|string
     */
    public static function translateLangNames($key = NULL) {
        $translated_langs = [
            "Chinese_Simplified"  => "中文-简体",
            "Chinese_Traditional" => "中文-繁体",
            "Czech"               => "Čeština",
            "Danish"              => "Dansk",
            "Dutch"               => "Nederlands",
            "English"             => "English",
            "French"              => "Francais",
            "German"              => "Deutsch",
            "Hungarian"           => "Magyar",
            "Italian"             => "Italiano",
            "Lithuanian"          => "Lietuvių",
            "Malay"               => "Melayu",
            "Norwegian"           => "Norsk",
            "Persian"             => "Persian",
            "Polish"              => "Polski",
            "Qazaq"               => "Qazaq",
            "Romanian"            => "Rom&#226;n&#259;",
            "Russian"             => "Русский",
            "Slovak"              => "Slovenčina",
            "Spanish"             => "Español",
            "Swedish"             => "Svenska",
            "Turkish"             => "Türkçe",
            "Ukrainian"           => "Українська",
        ];

        return $key === NULL ? $translated_langs : (isset($translated_langs[$key]) ? $translated_langs[$key] : $key);
    }

    /**
     * ISO-639 translator
     *
     * @param null $key
     * @param bool $iso_to_lang set false to translate iso-folder, default folder-iso
     *
     * @return array|int|string|null
     */
    public static function getIso($key = NULL, $iso_to_lang = TRUE) {
        /**
         * ISO 639-1 Language Codes
         * References :
         * 1. http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
         * 2. http://blog.xoundboy.com/?p=235
         * 3. https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
         */
        $language_codes = [
            'en' => 'English',
            'aa' => 'Afar',
            'ab' => 'Abkhazian',
            'af' => 'Afrikaans',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'as' => 'Assamese',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'ba' => 'Bashkir',
            'be' => 'Byelorussian',
            'bg' => 'Bulgarian',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bn' => 'Bengali/Bangla',
            'bo' => 'Tibetan',
            'br' => 'Breton',
            'ca' => 'Catalan',
            'co' => 'Corsican',
            'cs' => 'Czech',
            'cy' => 'Welsh',
            'da' => 'Danish',
            'de' => 'German',
            'dz' => 'Bhutani',
            'el' => 'Greek',
            'eo' => 'Esperanto',
            'es' => 'Spanish',
            'et' => 'Estonian',
            'eu' => 'Basque',
            'fa' => 'Persian',
            'fi' => 'Finnish',
            'fj' => 'Fiji',
            'fo' => 'Faeroese',
            'fr' => 'French',
            'fy' => 'Frisian',
            'ga' => 'Irish',
            'gd' => 'Scots/Gaelic',
            'gl' => 'Galician',
            'gn' => 'Guarani',
            'gu' => 'Gujarati',
            'ha' => 'Hausa',
            'hi' => 'Hindi',
            'hr' => 'Croatian',
            'hu' => 'Hungarian',
            'hy' => 'Armenian',
            'ia' => 'Interlingua',
            'ie' => 'Interlingue',
            'ik' => 'Inupiak',
            'in' => 'Indonesian',
            'is' => 'Icelandic',
            'it' => 'Italian',
            'iw' => 'Hebrew',
            'ja' => 'Japanese',
            'ji' => 'Yiddish',
            'jw' => 'Javanese',
            'ka' => 'Georgian',
            'kk' => 'Qazaq',
            'kl' => 'Greenlandic',
            'km' => 'Cambodian',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'ks' => 'Kashmiri',
            'ku' => 'Kurdish',
            'ky' => 'Kirghiz',
            'la' => 'Latin',
            'ln' => 'Lingala',
            'lo' => 'Laothian',
            'lt' => 'Lithuanian',
            'lv' => 'Latvian/Lettish',
            'mg' => 'Malagasy',
            'mi' => 'Maori',
            'mk' => 'Macedonian',
            'ml' => 'Malayalam',
            'mn' => 'Mongolian',
            'mo' => 'Moldavian',
            'mr' => 'Marathi',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'my' => 'Burmese',
            'na' => 'Nauru',
            'ne' => 'Nepali',
            'nl' => 'Dutch',
            'no' => 'Norwegian',
            'oc' => 'Occitan',
            'om' => '(Afan)/Oromoor/Oriya',
            'pa' => 'Punjabi',
            'pl' => 'Polish',
            'ps' => 'Pashto/Pushto',
            'pt' => 'Portuguese',
            'qu' => 'Quechua',
            'rm' => 'Rhaeto-Romance',
            'rn' => 'Kirundi',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'rw' => 'Kinyarwanda',
            'sa' => 'Sanskrit',
            'sd' => 'Sindhi',
            'sg' => 'Sangro',
            'sh' => 'Serbo-Croatian',
            'si' => 'Singhalese',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'so' => 'Somali',
            'sq' => 'Albanian',
            'sr' => 'Serbian',
            'ss' => 'Siswati',
            'st' => 'Sesotho',
            'su' => 'Sundanese',
            'sv' => 'Swedish',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'te' => 'Tegulu',
            'tg' => 'Tajik',
            'th' => 'Thai',
            'ti' => 'Tigrinya',
            'tk' => 'Turkmen',
            'tl' => 'Tagalog',
            'tn' => 'Setswana',
            'to' => 'Tonga',
            'tr' => 'Turkish',
            'ts' => 'Tsonga',
            'tt' => 'Tatar',
            'tw' => 'Twi',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'vo' => 'Volapuk',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yo' => 'Yoruba',
            'zh' => 'Chinese',
            'zu' => 'Zulu',
        ];

        $iso_codes = array_flip($language_codes);
        if ($iso_to_lang) {
            return $key === NULL ? $language_codes : (isset($language_codes[$key]) ? self::translateLangNames($language_codes[$key]) : NULL);
        }

        return $key === NULL ? array_flip($iso_codes) : (isset($iso_codes[$key]) ? $iso_codes[$key] : NULL);
    }

    /**
     * @param int    $count
     * @param string $words 'member|members';
     * @param array  $options
     *
     * @return string
     *
     * @deprecated use format_word()
     */
    public static function format_word($count, $words, $options = []) {
        return self::formatWord($count, $words, $options);
    }

    /**
     * Performs the language file checks to get the correct locale file for the current user
     *
     * This function will not check whether the locale file exists for debugging purposes.
     * Error codes must be generated in order to know when a locale file is missing.
     *
     * @param string $locale_file
     * @param string $locale_folder
     * @param bool   $localeset_folder
     * @param string $default_lang
     *
     * @return string
     */
    public function getInfLocaleFiles($locale_file, $locale_folder, $localeset_folder = TRUE, $default_lang = 'English') {
        // prune the locale folder and ensures the correct forumat is used
        $locale_folder = rtrim($locale_folder, '/').'/';
        $locale_set = rtrim(LOCALESET, '/');
        // this is when the infusion has multiple locale files, typical solution was to store the files in a localeset folder - /English/
        if ($localeset_folder) {
            $locale_path = $locale_folder.$default_lang.'/'.$locale_file;
            if (is_file($locale_folder.LOCALESET.$locale_file)) {
                $locale_path = $locale_folder.LOCALESET.$locale_file;
            }
            return $locale_path;
        }

        // when there are no folder, typical solution was to store the files in a single locale folder and have the file named as the language - English.php

        $locale_path = $locale_folder.$default_lang.'.php';
        if (is_file($locale_folder.$locale_set.'.php')) {
            $locale_path = $locale_folder.$locale_set.'.php';
        }

        return $locale_path;
    }
}
