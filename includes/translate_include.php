<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
	die("Denied");
	exit;
}
function translate_lang_names($language) {
	$translated_langs = array(
						"Chinese" => '中文',
						"Danish" => "Dansk",
						"Dutch" => "Dutch",
						"English" => "English",
						"French" => "Francais",
						"German" => "Deutsch",
						"Hungarian" => "Magyar",
						"Italian" => "Italiano",
						"Lithuanian" => "Lithuanian",
						"Malay"=>"Malay",
						"Persian" => "Persian",
						"Polish" => "Polski",
					  	"Russian" => "Русский",
						"Spanish" => "Espanol",
						"Swedish" => "Svenska",
						"Turkish" => "Türkiye",
						"Ukrainian" => "Українська",
						"Norwegian" => "Norsk"
						);
	if ($language != '') {
		if ($translated_langs[$language] != '') {
			return $translated_langs[$language];
		} else {
			return $language;
		}
	} else {
		return $language;
	}
}

function translate_country_names($country) {
	$translated_countries = array("Hungary" => "Magyarország", "Poland" => "Polska", "Italy" => "Italia",
								  "Germany" => "Deutchland", "Russia" => "Россия", "Ukraine" => "Україна");
	if ($translated_countries[$country] != '') {
		return $translated_countries[$country];
	} else {
		return $country;
	}
}

// select correct single and plural form for languages
function format_word($count, $words, $add_count = 1) {
	$lang_func_name = "format_word_".LANGUAGE;
	$result = "";
	if (function_exists($lang_func_name)) {
		$result = $lang_func_name($count, $words, $add_count);
	} else {
		$words_array = explode("|", $words);
		$result = $words_array[0];
		if ($add_count) {
			$result = $count." ".$result;
		}
	}
	return $result;
}

function format_word_Danish($count, $words, $add_count = 1) {
	$form = $count == 1 ? 0 : 1;

	$words_array = explode("|", $words);
	$result = $words_array[$form];
	if ($add_count) {
		$result = $count." ".$result;
	}

	return $result;
}

function format_word_English($count, $words, $add_count = 1) {
	$form = $count == 1 ? 0 : 1;

	$words_array = explode("|", $words);
	$result = $words_array[$form];
	if ($add_count) {
		$result = $count." ".$result;
	}

	return $result;
}

function format_word_Russian($count, $words, $add_count = 1) {
	$fcount = $count % 100;
	$a = $fcount % 10;
	$b = floor($fcount / 10);

	$form = 2; // second plural form

	if ($b != 1) { // count is not between 10 and 19
		if ($a == 1) {
			$form = 0; // single form
		} elseif ($a >= 2 && $a <= 4) {
			$form = 1; // first plural form
		}
	}

	$words_array = explode("|", $words);
	$result = $words_array[$form];
	if ($add_count) {
		$result = $count." ".$result;
	}

	return $result;
}

function format_word_Ukrainian($count, $words, $add_count = 1) {
	$fcount = $count % 100;
	$a = $fcount % 10;
	$b = floor($fcount / 10);

	$form = 2; // second plural form

	if ($b != 1) { // count is not between 10 and 19
		if ($a == 1) {
			$form = 0; // single form
		} elseif ($a >= 2 && $a <= 4) {
			$form = 1; // first plural form
		}
	}

	$words_array = explode("|", $words);
	$result = $words_array[$form];
	if ($add_count) {
		$result = $count." ".$result;
	}

	return $result;
}

