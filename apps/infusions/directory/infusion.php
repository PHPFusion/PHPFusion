<?php
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = 'Directory';
$inf_description = 'A Premium Directory System';
$inf_version = "1.2";
$inf_developer = 'Chan';
$inf_email = "chan@phpfusion.com";
$inf_weburl = "https://phpfusion.com";
$inf_folder = 'directory';
$inf_image = 'directory.png';
$inf_rights = 'DC';

// field ids
$inf_newtable[] = DB_LISTING_FIELDS." (
    field_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    form_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    field_type VARCHAR(200) NOT NULL DEFAULT '',
    field_options TEXT NOT NULL,
    PRIMARY KEY (field_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_LISTING_FORM." (
    form_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    form_title VARCHAR(20) NOT NULL DEFAULT '',
    PRIMARY KEY (form_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_LISTING." (
    list_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    cat_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    form_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    list_title VARCHAR(200) NOT NULL DEFAULT '',
    list_tagline VARCHAR(200) NOT NULL DEFAULT '',
    list_description TEXT NOT NULL,
    list_tags TEXT NOT NULL,
    list_datestamp INT(20) UNSIGNED NOT NULL DEFAULT '0',
    list_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    list_reads INT(10) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (list_id),
    KEY cat_id (cat_id),
    KEY form_id (form_id),
    KEY datestamp (list_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Attribute values for user items in custom fields
$inf_newtable[] = DB_LISTING_ATTR." (
    atid BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    field_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    value TEXT NOT NULL,
    PRIMARY KEY (atid),
    KEY id (id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Create tables
$inf_newtable[] = DB_LISTING_CAT." (
    cat_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    cat_parent BIGINT(8) UNSIGNED NOT NULL DEFAULT '0',
    cat_name VARCHAR(100) NOT NULL DEFAULT '',
    cat_description TEXT NOT NULL,
    cat_visibility CHAR(4) NOT NULL DEFAULT '0',
    cat_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (cat_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Create tags
$inf_newtable[] = DB_LISTING_TAGS." (
    tag_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    tag_name VARCHAR(100) NOT NULL DEFAULT '',
    tag_description TEXT NOT NULL,
    tag_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (tag_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$settings = [
    'pagination'         => 20,
    'allow_submission'   => 1,
    'allow_subscription' => 0
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    "title"  => 'Directory',
    "rights" => $inf_rights
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        if (file_exists(LOCALE.$language.'/setup.php')) {
            include LOCALE.$language.'/setup.php';
        } else {
            include LOCALE.'English/setup.php';
        }

        $mlt_adminpanel[$language][] = [
            "rights"   => "DC",
            "image"    => $inf_image,
            "title"    => 'Directory',
            "panel"    => "directory_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('Home', 'home.php', '0', '2', '0', '1', '1', '".LANGUAGE."')";
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('Explore', 'explore.php', '0', '2', '0', '1', '1', '".LANGUAGE."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='home.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='explore.php' AND link_language='".$language."'";
    }

} else {

    $inf_adminpanel[] = [
        "rights"   => "DC",
        "image"    => $inf_image,
        "title"    => 'Directory',
        "panel"    => "directory_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('Home', 'home.php', '0', '2', '0', '1', '1', '".LANGUAGE."')";
    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('Explore', 'explore.php', '0', '2', '0', '1', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_LISTING;
$inf_droptable[] = DB_LISTING_ATTR;
$inf_droptable[] = DB_LISTING_FORM;
$inf_droptable[] = DB_LISTING_FIELDS;
$inf_droptable[] = DB_LISTING_CAT;
$inf_droptable[] = DB_LISTING_TAGS;

$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='$inf_rights'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='home.php'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='explore'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='$inf_rights'";

$inf_delfiles[] = IMAGES_DC; //IMAGES_A;

