<?php

$locale = fusion_get_locale("", LOCALE . LOCALESET . 'setup.php');

$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.11.00',
];

if (!db_exists(table: DB_PREFIX . 'site_link_menus', add_prefix: false)) {

    $inf_newtable[] =  DB_SITE_LINK_MENUS . " (
        menu_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        menu_alias VARCHAR(200) NOT NULL DEFAULT '',
        menu_name VARCHAR(100) NOT NULL DEFAULT '',
        menu_branding TINYINT(5) UNSIGNED NOT NULL DEFAULT '0',
        menu_header VARCHAR(200) NOT NULL DEFAULT '',
        menu_image VARCHAR(200) NOT NULL DEFAULT '',
        menu_visibility TINYINT(4) NOT NULL DEFAULT '0',
        menu_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        menu_grouping SMALLINT(3) UNSIGNED NOT NULL DEFAULT '5',
        menu_language VARCHAR(70) NOT NULL DEFAULT '" . ($localeset ?? LANGUAGE) . "',
        PRIMARY KEY (menu_id),
        KEY menu_visibility (menu_visibility)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

    // Navigation panel, Subheader
    $enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
    if (!empty($enabled_languages)) {
        foreach ($enabled_languages as $language) {
            $inf_insertdbrow[] = DB_SITE_LINK_MENUS . " (menu_id, menu_alias, menu_name, menu_branding, menu_header, menu_image, menu_visibility, menu_status, menu_grouping, menu_language) VALUES ('1', 'css-navigation', '" . $locale['setup_3220'] . "', 0, '".$settings['sitename']."', '".$settings['sitebanner']."', 0, '1', '5', '$language')";
            $inf_insertdbrow[] = DB_SITE_LINK_MENUS . " (menu_id, menu_alias, menu_name, menu_branding, menu_header, menu_image, menu_visibility, menu_status, menu_grouping, menu_language) VALUES ('2', 'subheader', '" . $locale['setup_3221'] . "', 2, '".$settings['sitename']."', '".$settings['sitebanner']."', 0, '1', '5', '$language')";
        }
    }
}

if (!column_exists(table: DB_SITE_LINKS, column: 'link_columns', add_prefix: false)) {
    $inf_altertable[] = DB_SITE_LINKS . " ADD COLUMN link_description VARCHAR(200) NOT NULL DEFAULT '' AFTER link_name";
    $inf_altertable[] = DB_SITE_LINKS . " ADD COLUMN link_columns TINYINT(5) UNSIGNED NOT NULL DEFAULT '1' AFTER link_status";
}

$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='links_grouping'";
$inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='links_per_page'";

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS . " SET settings_value='" . $value . "' WHERE settings_name='" . $key . "'";
    }
}
