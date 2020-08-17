<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

function translate_lang_names($language) {
    //https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
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

    if (isset($translated_langs[$language])) {
        return $translated_langs[$language];
    }
    return $language;
}

function translate_country_names($country) {
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

    if ($translated_countries[$country] != '') {
        return $translated_countries[$country];
    } else {
        return $country;
    }
}

function format_word($count, $words, $add_count = 1) {
    switch (LANGUAGE) {
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
                } else if ($a >= 2 && $a <= 4) {
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

    if ($add_count) {
        $result = "<span class='fusion_count'>$count</span> <span class='fusion_word'>".$result."</span>";
    }

    return $result;
}
