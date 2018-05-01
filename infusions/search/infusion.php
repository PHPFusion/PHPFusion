<?php

/***
 * PHP-Fusion.co.uk Typeahead Development
 * EPAL
 * To detect and store user searches and store searches
 */

$locale = fusion_get_locale('', SEARCH_LOCALE);
$inf_title = $locale['SC_title'];
$inf_description = $locale['SC_desc'];
$inf_version = "1.00";
$inf_developer = "PHP-Fusion Inc";
$inf_email = "";
$inf_weburl = "http://php-fusion.co.uk/";
$inf_folder = "search";
$inf_image = 'search.svg';

// logs what has been searched and recall
$inf_newtable[] = DB_SEARCH." (
search_id MEDIUMINT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
search_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
search_keywords VARCHAR(255) NOT NULL DEFAULT '',
search_callback_type VARCHAR(20) NOT NULL DEFAULT '',
search_callback_data TEXT NOT NULL,
search_type CHAR(5) NOT NULL DEFAULT '',
search_method CHAR(5) NOT NULL DEFAULT '',
search_forum_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
search_datelimit INT(10) UNSIGNED NOT NULL DEFAULT '0',
search_fields SMALLINT(5) NOT NULL,
search_sort VARCHAR(20) NOT NULL DEFAULT '',
search_order SMALLINT(10) NOT NULL,
search_chars SMALLINT(10) NOT NULL,
search_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
search_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
search_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
search_ip VARCHAR(45) NOT NULL DEFAULT '',
search_ip_type TINYINT(1) UNSIGNED NOT NULL DEFAULT '4',
PRIMARY KEY (search_id),
KEY search_keywords (search_user, search_keywords, search_callback_type, search_language)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_droptable[] = DB_SEARCH;