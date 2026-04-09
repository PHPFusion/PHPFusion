<?php

$locale = fusion_get_locale( "", PROFILE_HOME_LOCALE );

$inf_title = $locale['ph_0001'];
$inf_description = $locale['ph_0002'];
$inf_version = "1.00";
$inf_developer = "Chan (deviance)";
$inf_email = "chan@php-fusion.co.uk";
$inf_weburl = "http://www.php-fusion.co.uk";
$inf_folder = "profile_home";
$inf_image = "home.png";
$inf_rights = 'PFHP';

$inf_adminpanel[] = [
    'title'  => $locale['ph_0001'],
    'image'  => $inf_image,
    'panel'  => 'administration.php',
    'rights' => $inf_rights,
    'page'   => 1
];

$inf_newtable[] = DB_PROFILE_PANEL." (
    panel_id        BIGINT(20)      UNSIGNED NOT NULL AUTO_INCREMENT,
    panel_user      BIGINT(20)      UNSIGNED NOT NULL DEFAULT '0',
    panel_name      VARCHAR(100)    NOT NULL DEFAULT '0',
    panel_position  SMALLINT(5)     UNSIGNED NOT NULL DEFAULT '1',
    panel_order     SMALLINT(5)     UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (panel_id),
	KEY panel_user (panel_user)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


// Create tables
$inf_newtable[] = DB_PROFILE_JOURNALS." (
    journal_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    journal_subject TEXT NOT NULL,
    journal_cat BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    journal_text TEXT NOT NULL,
    journal_keywords VARCHAR(250) NOT NULL DEFAULT '',
    journal_uid BIGINT(20) UNSIGNED NOT NULL DEFAULT '1',
    journal_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    journal_start INT(10) UNSIGNED NOT NULL DEFAULT '0',
    journal_end INT(10) UNSIGNED NOT NULL DEFAULT '0',
    journal_visibility TINYINT(4) NOT NULL DEFAULT '0',
    journal_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
    journal_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    journal_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    journal_cover_image VARCHAR(200) NOT NULL DEFAULT '',
    journal_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (journal_id),
    KEY journal_datestamp (journal_datestamp),
    KEY journal_cat (journal_cat),
    KEY journal_uid (journal_uid),
    KEY journal_reads (journal_reads),
    KEY journal_language (journal_language)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_PROFILE_JOURNAL_CATS." (
    journal_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    journal_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    journal_cat_name TEXT NOT NULL,
    journal_cat_visibility TINYINT(4) NOT NULL DEFAULT '0',
    journal_cat_draft TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    journal_cat_sticky TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    journal_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (journal_cat_id),
    KEY journal_cat_parent (journal_cat_parent),
    KEY journal_cat_language (journal_cat_language)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_PROFILE_JOURNAL_COLLECTIONS." (
    collection_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    collection_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    collection_item_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    collection_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    primary key (collection_id),
    key collection_id (collection_id),
    key collection_item_id (collection_item_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";


$inf_droptable = DB_PROFILE_PANEL;
$inf_droptable = DB_PROFILE_JOURNAL_CATS;
$inf_droptable = DB_PROFILE_JOURNALS;
