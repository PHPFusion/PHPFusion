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

class Locale {

    /*
     * Cache to omit inclusion if there is a repeated request
     */
    //private static $locale_cache = array();

    /**
     * Get Locale
     *
     * Fetch a given locale key
     *
     * @param null   $key - The key of one setting
     * @param string $include_file - The full path of the file which to be included
     * @return array|null
     */
    public static function getLocale($key = NULL, $include_file = "") {
        global $locale;
        $is_sanitized = TRUE;
        //if (!in_array($include_file, self::$locale_cache) && $include_file) {
        if ($include_file) {
            if (is_array($include_file)) {
                foreach ($include_file as $file) {
                    if (is_file($file)) {
                        //self::$locale_cache[] = $file;
                        include $file;
                    }
                }
            } else {
                if (is_file($include_file)) {
                    //self::$locale_cache[] = $include_file;
                    include $include_file;
                }
            }
        }

        if (!empty($locale) && $is_sanitized == TRUE) {
            return $key === NULL ? $locale : (isset($locale[$key]) ? $locale[$key] : $locale);
        }

        return NULL;
    }

    /**
     * @param       $count - integer
     * @param       $words - 'member|members';
     * @param array $options
     *              Indexes:
     *                  -   add_count ...... true/false (default true) to show integer
     *                  -   show_html ...... whether to output html as return
     *                  -   html_template ..... custom html template
     *                  -   language at use.
     * @return string
     */
    public static function format_word($count, $words, $options = array()) {

        $default_options = array(
            'add_count' => TRUE,
            'html' => FALSE,
            'html_template' => "<span class='fusion_count'>{%count%}</span> <span class='fusion_word'>{%result%}</span>",
            'language' => LANGUAGE,
        );

        $options += $default_options;

        if (empty($count)) {
            $count = "0";
        }

        // Format the result
        switch ($options['language']) {
            case 'English':
                $form = $count == 1 ? 0 : 1;
                $words_array = explode("|", $words);
                $result = $words_array[$form];
                break;
            case 'Danish':
                $form = $count == 1 ? 0 : 1;
                $words_array = explode("|", $words);
                $result = $words_array[$form];
                break;
            case 'Russian':
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
            default : // never plural language - i.e. chinese is here
                $words_array = explode("|", $words);
                $result = $words_array[0];
        }

        if ($options['add_count']) {
            if ($options['html'] && !empty($options['html_template'])) {
                return strtr($options['html_template'],
                             array(
                                 "{%count%}" => $count,
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
     * @param $country
     * @return mixed
     */
    public static function translate_country_names($country) {
        $translated_countries = array(
            "China" => "中国",
            "Czech republic" => "Česko",
            "Denmark" => "Danmark",
            "Finland" => "Suomi",
            "Hungary" => "Magyarország",
            "Hong Kong" => "香港",
            "The Netherlands" => "Nederland",
            "Norway" => "Norge",
            "Poland" => "Polska",
            "Italy" => "Italia",
            "Germany" => "Deutschland",
            "Russia" => "Россия",
            "Taiwan" => "台湾",
            "Ukraine" => "Україна",
            "Sweden" => "Sverige"
        );
        if ($translated_countries[$country] != '') {
            return $translated_countries[$country];
        } else {
            return $country;
        }
    }

    /**
     * Translate Locale Folder Name into Localized language
     * @param $language
     * @return mixed
     */
    public static function translate_lang_names($language) {
        //https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
        $translated_langs = array(
            "Chinese_Traditional" => "中文-繁体",
            "Chinese_Simplified" => "中文-简体",
            "Czech" => "Čeština",
            "Danish" => "Dansk",
            "Dutch" => "Nederlands",
            "English" => "English",
            "French" => "Francais",
            "German" => "Deutsch",
            "Hungarian" => "Magyar",
            "Italian" => "Italiano",
            "Lithuanian" => "Lietuvių",
            "Malay" => "Melayu",
            "Persian" => "Persian",
            "Polish" => "Polski",
            "Russian" => "Русский",
            "Spanish" => "Español",
            "Swedish" => "Svenska",
            "Turkish" => "Türkiye",
            "Ukrainian" => "Українська",
            "Norwegian" => "Norsk"
        );
        if (isset($translated_langs[$language])) {
            return $translated_langs[$language];
        }

        return $language;
    }
}
