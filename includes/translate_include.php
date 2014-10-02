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
if (!defined("IN_FUSION")) {
	die("Denied");
	exit;
}
function translate_lang_names($language) {
	$translated_langs = array("Danish" => "Dansk", "Dutch" => "Dutch", "English" => "English", "French" => "Francais",
							  "German" => "Deutsch", "Hungarian" => "Magyar", "Italian" => "Italiano",
							  "Lithuanian" => "Lithuanian", "Malay"=>"Malay", "Persian" => "Persian", "Polish" => "Polski",
							  "Russian" => "Ruski", "Spanish" => "Espanol", "Swedish" => "Svenska",
							  "Turkish" => "Türkiye", "Norwegian" => "Norsk",);
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
								  "Germany" => "Deutchland");
	if ($translated_countries[$country] != '') {
		return $translated_countries[$country];
	} else {
		return $country;
	}
}

?>