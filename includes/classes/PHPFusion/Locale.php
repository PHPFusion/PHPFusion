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
    private static $translated_langs = [
        'aa' => 'Afar',
        'ab' => 'Abkhazian',
        'ae' => 'Avestan',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'አማርኛ',
        'an' => 'Aragonese',
        'ar' => 'العربية',
        'as' => 'অসমীয়া',
        'av' => 'Avaric',
        'ay' => 'Aymara',
        'az' => 'Azərbaycan dili',
        'ba' => 'Bashkir',
        'be' => 'беларуская',
        'bg' => 'български',
        'bi' => 'Bislama',
        'bm' => 'Bamanakan',
        'bn' => 'বাংলা',
        'bo' => 'བོད་སྐད་',
        'br' => 'Brezhoneg',
        'bs' => 'Bosanski',
        'ca' => 'Català',
        'ce' => 'нохчийн',
        'ch' => 'Chamorro',
        'co' => 'Corsican',
        'cr' => 'Cree',
        'cs' => 'Čeština',
        'cu' => 'Church Slavic',
        'cv' => 'Chuvash',
        'cy' => 'Cymraeg',
        'da' => 'Dansk',
        'de' => 'Deutsch',
        'dv' => 'Divehi',
        'dz' => 'རྫོང་ཁ',
        'ee' => 'Eʋegbe',
        'el' => 'Ελληνικά',
        'en' => 'English',
        'eo' => 'Esperanto',
        'es' => 'Español',
        'et' => 'Eesti',
        'eu' => 'Euskara',
        'fa' => 'فارسی',
        'ff' => 'Pulaar',
        'fi' => 'Suomi',
        'fj' => 'Fijian',
        'fo' => 'Føroyskt',
        'fr' => 'Français',
        'fy' => 'West-Frysk',
        'ga' => 'Gaeilge',
        'gd' => 'Gàidhlig',
        'gl' => 'Galego',
        'gn' => 'Guarani',
        'gu' => 'ગુજરાતી',
        'gv' => 'Gaelg',
        'ha' => 'Hausa',
        'he' => 'עברית',
        'hi' => 'हिन्दी',
        'ho' => 'Hiri Motu',
        'hr' => 'Hrvatski',
        'ht' => 'Haitian Creole',
        'hu' => 'Magyar',
        'hy' => 'հայերեն',
        'hz' => 'Herero',
        'ia' => 'Interlingua',
        'id' => 'Indonesia',
        'ie' => 'Interlingue',
        'ig' => 'Igbo',
        'ii' => 'ꆈꌠꉙ',
        'ik' => 'Inupiaq',
        'io' => 'Ido',
        'is' => 'íslenska',
        'it' => 'Italiano',
        'iu' => 'Inuktitut',
        'ja' => '日本語',
        'jv' => 'Javanese',
        'ka' => 'ქართული',
        'kg' => 'Kongo',
        'ki' => 'Gikuyu',
        'kj' => 'Kuanyama',
        'kk' => 'қазақ тілі',
        'kl' => 'Kalaallisut',
        'km' => 'ខ្មែរ',
        'kn' => 'ಕನ್ನಡ',
        'ko' => '한국어',
        'kr' => 'Kanuri',
        'ks' => 'کٲشُر',
        'ku' => 'Kurdish',
        'kv' => 'Komi',
        'kw' => 'Kernewek',
        'ky' => 'кыргызча',
        'la' => 'Latin',
        'lb' => 'Lëtzebuergesch',
        'lg' => 'Luganda',
        'li' => 'Limburgish',
        'ln' => 'Lingála',
        'lo' => 'ລາວ',
        'lt' => 'Lietuvių',
        'lu' => 'Tshiluba',
        'lv' => 'Latviešu',
        'mg' => 'Malagasy',
        'mh' => 'Marshallese',
        'mi' => 'Maori',
        'mk' => 'македонски',
        'ml' => 'മലയാളം',
        'mn' => 'монгол',
        'mr' => 'मराठी',
        'ms' => 'Bahasa Melayu',
        'mt' => 'Malti',
        'my' => 'ဗမာ',
        'na' => 'Nauru',
        'nb' => 'Norsk bokmål',
        'nd' => 'IsiNdebele',
        'ne' => 'नेपाली',
        'ng' => 'Ndonga',
        'nl' => 'Nederlands',
        'nn' => 'Nynorsk',
        'no' => 'Norsk',
        'nr' => 'South Ndebele',
        'nv' => 'Navajo',
        'ny' => 'Nyanja',
        'oc' => 'Occitan',
        'oj' => 'Ojibwa',
        'om' => 'Oromoo',
        'or' => 'ଓଡ଼ିଆ',
        'os' => 'ирон',
        'pa' => 'ਪੰਜਾਬੀ',
        'pi' => 'Pali',
        'pl' => 'Polski',
        'ps' => 'پښتو',
        'pt' => 'Português',
        'qu' => 'Runasimi',
        'rm' => 'Rumantsch',
        'rn' => 'Ikirundi',
        'ro' => 'Română',
        'ru' => 'Русский',
        'rw' => 'Kinyarwanda',
        'sa' => 'Sanskrit',
        'sc' => 'Sardinian',
        'sd' => 'Sindhi',
        'se' => 'Davvisámegiella',
        'sg' => 'Sängö',
        'si' => 'සිංහල',
        'sk' => 'Slovenčina',
        'sl' => 'Slovenščina',
        'sm' => 'Samoan',
        'sn' => 'ChiShona',
        'so' => 'Soomaali',
        'sq' => 'Shqip',
        'sr' => 'српски',
        'ss' => 'Swati',
        'st' => 'Southern Sotho',
        'su' => 'Sundanese',
        'sv' => 'Svenska',
        'sw' => 'Kiswahili',
        'ta' => 'தமிழ்',
        'te' => 'తెలుగు',
        'tg' => 'Tajik',
        'th' => 'ไทย',
        'ti' => 'ትግርኛ',
        'tk' => 'Turkmen',
        'tn' => 'Tswana',
        'to' => 'Lea fakatonga',
        'tr' => 'Türkçe',
        'ts' => 'Tsonga',
        'tt' => 'Tatar',
        'tw' => 'Twi',
        'ty' => 'Tahitian',
        'ug' => 'ئۇيغۇرچە',
        'uk' => 'Українська',
        'ur' => 'اردو',
        'uz' => 'O‘zbek',
        've' => 'Venda',
        'vi' => 'Tiếng Việt',
        'vo' => 'Volapük',
        'wa' => 'Walloon',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'ייִדיש',
        'yo' => 'Èdè Yorùbá',
        'za' => 'Zhuang',
        'zh' => '简体中文',
        'zu' => 'IsiZulu',
    ];
    /**
     * ISO 639-1 Language Codes
     * References :
     * 1. http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     * 2. http://blog.xoundboy.com/?p=235
     * 3. https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     */
    private static $language_codes = [
        'ab' => 'Abkhazian',
        'aa' => 'Afar',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'sq' => 'Albanian',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'an' => 'Aragonese',
        'hy' => 'Armenian',
        'as' => 'Assamese',
        'av' => 'Avaric',
        'ae' => 'Avestan',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'bm' => 'Bambara',
        'ba' => 'Bashkir',
        'eu' => 'Basque',
        'be' => 'Belarusian',
        'bn' => 'Bengali',
        'bh' => 'Bihari languages',
        'bi' => 'Bislama',
        'bs' => 'Bosnian',
        'br' => 'Breton',
        'bg' => 'Bulgarian',
        'my' => 'Burmese',
        'ca' => 'Catalan, Valencian',
        'km' => 'Central Khmer',
        'ch' => 'Chamorro',
        'ce' => 'Chechen',
        'ny' => 'Chichewa, Chewa, Nyanja',
        'zh' => 'Chinese',
        'cu' => 'Church Slavonic, Old Bulgarian, Old Church Slavonic',
        'cv' => 'Chuvash',
        'kw' => 'Cornish',
        'co' => 'Corsican',
        'cr' => 'Cree',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'dv' => 'Divehi, Dhivehi, Maldivian',
        'nl' => 'Dutch, Flemish',
        'dz' => 'Dzongkha',
        'en' => 'English',
        'eo' => 'Esperanto',
        'et' => 'Estonian',
        'ee' => 'Ewe',
        'fo' => 'Faroese',
        'fj' => 'Fijian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'ff' => 'Fulah',
        'gd' => 'Gaelic, Scottish Gaelic',
        'gl' => 'Galician',
        'lg' => 'Ganda',
        'ka' => 'Georgian',
        'de' => 'German',
        'ki' => 'Gikuyu, Kikuyu',
        'el' => 'Greek (Modern)',
        'kl' => 'Greenlandic, Kalaallisut',
        'gn' => 'Guarani',
        'gu' => 'Gujarati',
        'ht' => 'Haitian, Haitian Creole',
        'ha' => 'Hausa',
        'he' => 'Hebrew',
        'hz' => 'Herero',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'io' => 'Ido',
        'ig' => 'Igbo',
        'id' => 'Indonesian',
        'ia' => 'Interlingua (International Auxiliary Language Association)',
        'ie' => 'Interlingue',
        'iu' => 'Inuktitut',
        'ik' => 'Inupiaq',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'jv' => 'Javanese',
        'kn' => 'Kannada',
        'kr' => 'Kanuri',
        'ks' => 'Kashmiri',
        'kk' => 'Kazakh',
        'rw' => 'Kinyarwanda',
        'kv' => 'Komi',
        'kg' => 'Kongo',
        'ko' => 'Korean',
        'kj' => 'Kwanyama, Kuanyama',
        'ku' => 'Kurdish',
        'ky' => 'Kyrgyz',
        'lo' => 'Lao',
        'la' => 'Latin',
        'lv' => 'Latvian',
        'lb' => 'Letzeburgesch, Luxembourgish',
        'li' => 'Limburgish, Limburgan, Limburger',
        'ln' => 'Lingala',
        'lt' => 'Lithuanian',
        'lu' => 'Luba-Katanga',
        'mk' => 'Macedonian',
        'mg' => 'Malagasy',
        'ms' => 'Malay',
        'ml' => 'Malayalam',
        'mt' => 'Maltese',
        'gv' => 'Manx',
        'mi' => 'Maori',
        'mr' => 'Marathi',
        'mh' => 'Marshallese',
        'ro' => 'Moldovan, Moldavian, Romanian',
        'mn' => 'Mongolian',
        'na' => 'Nauru',
        'nv' => 'Navajo, Navaho',
        'nd' => 'Northern Ndebele',
        'ng' => 'Ndonga',
        'ne' => 'Nepali',
        'se' => 'Northern Sami',
        'no' => 'Norwegian',
        'nb' => 'Norwegian Bokmål',
        'nn' => 'Norwegian Nynorsk',
        'ii' => 'Nuosu, Sichuan Yi',
        'oc' => 'Occitan (post 1500)',
        'oj' => 'Ojibwa',
        'or' => 'Oriya',
        'om' => 'Oromo',
        'os' => 'Ossetian, Ossetic',
        'pi' => 'Pali',
        'pa' => 'Panjabi, Punjabi',
        'ps' => 'Pashto, Pushto',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'qu' => 'Quechua',
        'rm' => 'Romansh',
        'rn' => 'Rundi',
        'ru' => 'Russian',
        'sm' => 'Samoan',
        'sg' => 'Sango',
        'sa' => 'Sanskrit',
        'sc' => 'Sardinian',
        'sr' => 'Serbian',
        'sn' => 'Shona',
        'sd' => 'Sindhi',
        'si' => 'Sinhala, Sinhalese',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'so' => 'Somali',
        'st' => 'Sotho, Southern',
        'nr' => 'South Ndebele',
        'es' => 'Spanish, Castilian',
        'su' => 'Sundanese',
        'sw' => 'Swahili',
        'ss' => 'Swati',
        'sv' => 'Swedish',
        'tl' => 'Tagalog',
        'ty' => 'Tahitian',
        'tg' => 'Tajik',
        'ta' => 'Tamil',
        'tt' => 'Tatar',
        'te' => 'Telugu',
        'th' => 'Thai',
        'bo' => 'Tibetan',
        'ti' => 'Tigrinya',
        'to' => 'Tonga (Tonga Islands)',
        'ts' => 'Tsonga',
        'tn' => 'Tswana',
        'tr' => 'Turkish',
        'tk' => 'Turkmen',
        'tw' => 'Twi',
        'ug' => 'Uighur, Uyghur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        've' => 'Venda',
        'vi' => 'Vietnamese',
        'vo' => 'Volap_k',
        'wa' => 'Walloon',
        'cy' => 'Welsh',
        'fy' => 'Western Frisian',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'za' => 'Zhuang, Chuang',
        'zu' => 'Zulu'
    ];

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
     * Helper function to make a list of formatted language array for input fields
     *
     * @return array
     */
    public static function languageOptions() {
        $iso_codes = self::getIso();
        $list = [];
        foreach ($iso_codes as $_isocode => $standard_name) {
            if ($translated_name = self::translateLangNames($_isocode)) {
                if ($translated_name != $standard_name) {
                    $standard_name = $standard_name.' ('.$translated_name.')';
                }
            }
            $list[$_isocode] = $standard_name;
        }
        return $list;
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

        $iso_codes = array_flip(self::$language_codes);
        if ($iso_to_lang) {
            return $key === NULL ? self::$language_codes : (isset(self::$language_codes[$key]) ? self::translateLangNames($key) : NULL);
        }

        return $key === NULL ? array_flip($iso_codes) : ($iso_codes[$key] ?? NULL);
    }

    /**
     * Get the language name in current system locale
     *
     * In a Malay locale copy, "简体中文" is called "Bahasa Cina".
     * In event of default, using the locale folder name is even better than "简体中文"
     *
     * @param $language_pack - Locale folder
     */
    public static function getLangName($language_pack) {
        $key = get_language_code($language_pack);
        $locale = self::getLanguageLocale();
        return ($locale[$key] ?? str_replace("_", "", $language_pack));
    }

    /**
     * Localized language names in ISO 639-1 list
     * Attempt to translate Locale Folder Name into Localized language
     * or return the locale folder name by default.
     *
     * If key is not set, return a full array
     *
     * @param string|null $key
     *
     * @return array|string
     */
    public static function translateLangNames($key, $extended = FALSE) {
        $key = get_language_code($key);

        if ($extended == TRUE) {
            $_locale = self::getLanguageLocale();

            static $parsed;
            if (empty($parsed)) { // prevent appending multiple times with each method call
                foreach ($_locale as $short_code => $value) {
                    if (isset(self::$translated_langs[$short_code])) {
                        self::$translated_langs[$short_code] = self::$translated_langs[$short_code]." [$value]";
                    }
                }
                $parsed = TRUE;
            }
        }

        return self::$translated_langs[$key];
    }

    /**
     * Load default language locale file
     *
     * @return array
     */
    private static function getLanguageLocale() {
        $locale = [];
        include LOCALE.LOCALESET.'language.php';
        return $locale;
    }

    /**
     * Get system translated lang list
     *
     * @param null $key
     *
     * @return string|string[]|null
     */
    public static function getTranslatedLangs($key = NULL) {
        return $key === NULL ? self::$translated_langs : (self::$translated_langs[$key] ?? NULL);
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
     * @param string|null $key
     *
     * @return array|mixed|string
     */
    public function getLocale($key = NULL) {
        return empty($key) ? self::$locale : (self::$locale[$key] ?? '');
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
