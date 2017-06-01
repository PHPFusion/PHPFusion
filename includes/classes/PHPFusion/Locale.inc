<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Locale.inc
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
    private static $instance = NULL;

    /**
     * Ensure that the class is only used once
     *
     * @param $key
     *
     * @return mixed
     */
    public static function __getInstance($key) {
        if (self::$instance[$key] === NULL) {
            self::$instance[$key] = new Static();
        }

        return self::$instance[$key];
    }

    /**
     * Includes a locale file and logs a trace
     *
     * @param $filename
     */
    public static function load_locale_file($filename) {
        $locale = array();
        self::$locale_file[$filename] = debug_backtrace();
        include_once $filename;
        self::$locale += $locale;
    }

    public static function get_loaded_files() {
        return self::$locale_file;
    }

    /**
     * Iinclude a new locale file
     *
     * @param $include_file     Can be an array or a string
     */
    public static function setLocale($include_file) {
        if (!empty($include_file)) {
            if (is_array($include_file)) {
                foreach ($include_file as $file) {
                    if (!isset(self::$locale_file[$file])) {
                        self::load_locale_file($file);
                    }
                }
            } elseif (!isset(self::$locale_file[$include_file])) {
                self::load_locale_file($include_file);
            }
        }
    }

    public function getLocale($key = NULL) {
        //self::$use_count++;
        //print_p(microtime(TRUE)-START_TIME);
        //print_p(self::$use_count);
        return empty($key) ? self::$locale : (isset(self::$locale[$key]) ? self::$locale[$key] : '');
    }

    /**
     * @param       $count - integer
     * @param       $words - 'member|members';
     * @param array $options
     *                     Indexes:
     *                     -   add_count ...... true/false (default true) to show integer
     *                     -   html ...... whether to output html as return
     *                     -   html_template ..... custom html template
     *                     -   language at use.
     *
     * @return string
     */
    public static function format_word($count, $words, $options = array()) {

        $default_options = array(
            'add_count'     => TRUE,
            'html'          => FALSE,
            'html_template' => "<span class='fusion_count'>{%count%}</span> <span class='fusion_word'>{%result%}</span>",
            'language'      => LANGUAGE,
        );

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
                $result = $words_array[$form];
                break;
            case 'Czech':
            case 'Slovak':
            case 'Russian':
            case 'Ukranian':
                $fcount = $count % 100;
                $a = $fcount % 10;
                $b = floor($fcount / 10);
                $form = 2;

                if ($b != 1) {
                    if ($a == 1) {
                        $form = 0;
                    } elseif ($a >= 2 && $a <= 4) {
                        $form = 1;
                    }
                }

                $words_array = explode("|", $words);
                $result = $words_array[$form];
                break;
            default: // never plural language - i.e. chinese is here
                $words_array = explode("|", $words);
                $result = $words_array[0];
        }

        if ($options['add_count']) {
            if ($options['html'] && !empty($options['html_template'])) {
                return strtr($options['html_template'],
                    array(
                        "{%count%}"  => $count,
                        "{%result%}" => $result
                    )
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
     * @param $country
     *
     * @return mixed
     */
    public static function translate_country_names($country) {
        $translated_countries = array(
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
            "Romania"         => "Rom&#226;nia",
            "Russia"          => "Россия",
            "Slovakia"        => "Slovensko",
            "Sweden"          => "Sverige",
            "Taiwan"          => "台湾",
            "The Netherlands" => "Nederland",
            "Ukraine"         => "Україна",
        );

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
     * @param null $key
     *
     * @return array|mixed|null
     */
    public static function translate_lang_names($key = NULL) {
        $translated_langs = array(
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
            "Romanian"            => "Rom&#226;n&#259;",
            "Russian"             => "Русский",
            "Slovak"              => "Slovenčina",
            "Spanish"             => "Español",
            "Swedish"             => "Svenska",
            "Turkish"             => "Türkçe",
            "Ukrainian"           => "Українська",
        );

        return $key === NULL ? $translated_langs : (isset($translated_langs[$key]) ? $translated_langs[$key] : $key);
    }

    /**
     * ISO-639 translator
     *
     * @param null $key
     * @param bool $iso_to_lang set false to translate iso-folder, default folder-iso
     *
     * @return array|mixed|null
     */
    public static function get_iso($key = NULL, $iso_to_lang = TRUE) {
        /**
         * ISO 639-1 Language Codes
         * References :
         * 1. http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
         * 2. http://blog.xoundboy.com/?p=235
         * 3. https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
         */
        $language_codes = array(
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
            'kk' => 'Kazakh',
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
        );

        $iso_codes = array_flip($language_codes);
        if ($iso_to_lang) {
            return $key === NULL ? $language_codes : (isset($language_codes[$key]) ? self::translate_lang_names($language_codes[$key]) : NULL);
        }

        return $key === NULL ? array_flip($iso_codes) : (isset($iso_codes[$key]) ? $iso_codes[$key] : NULL);
    }
}
