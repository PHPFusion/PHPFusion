<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.0.upgrade.inc
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
$locale = fusion_get_locale("", LOCALE.LOCALESET.'setup.php');
$settings = fusion_get_settings();

/*
 * Upgrade Database Collation
 * Force the database to UTF-8 because we'll convert to it
 */
dbquery("SET NAMES utf8mb4");

// Delete all old errors
dbquery("TRUNCATE TABLE ".DB_ERRORS);

if (!dbcount("(user_id)", DB_USERS, "user_level < '-100'")) {
    $inf_updatedbrow[] = DB_USERS." SET user_level='-103' WHERE user_level='103'";
    $inf_updatedbrow[] = DB_USERS." SET user_level='-102' WHERE user_level='102'";
    $inf_updatedbrow[] = DB_USERS." SET user_level='-101' WHERE user_level='101'";
}

// Remove dropped rights, these settings have been moved to tabs and follow the Infusions rights
$result = dbquery("SELECT user_id, user_rights, user_level FROM ".DB_USERS." WHERE user_rights !=''");
while ($data = dbarray($result)) {
    $current_rights = explode('.', $data['user_rights']);

    $remove_rights = ['AC', 'DC', 'FR', 'NC', 'S5', 'S8', 'S10', 'S11', 'S13', 'S14', 'SU', 'UFC', 'WC'];
    foreach ($remove_rights as $key => $remove) {
        if (($key = array_search($remove, $current_rights)) !== FALSE) {
            unset($current_rights[$key]);
        }
    }

    if ($data['user_level'] == '-103' || $data['user_level'] == '103') {
        $new_rights = ['PL', 'MI', 'LANG', 'TS', 'MAIL', 'FM'];
        foreach ($new_rights as $add) {
            if (!in_array($add, $current_rights, TRUE)) {
                $current_rights[] = $add;
            }
        }
    }

    $final_rights = implode('.', $current_rights);
    dbquery("UPDATE ".DB_USERS." SET user_rights='".$final_rights."' WHERE user_id='".$data['user_id']."'");
}

$del_table = [];
if (db_exists(DB_EMAIL_TEMPLATES)) {
    // Upgrade Email templates
    $check_array = [
        'PM'      => DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."'",
        'POST'    => DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."'",
        'CONTACT' => DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."'"
    ];

    foreach ($check_array as $key => $value) {
        if (!dbcount("(template_key)", DB_EMAIL_TEMPLATES, "template_key='$key'")) {
            $inf_insertdbrow[] = $value;
        }
    }

    // from v8
    $delete_tpl_array = [
        'ACTIVATION'
    ];

    foreach ($delete_tpl_array as $key) {
        dbquery("DELETE FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='".$key."'");
    }
}

if (db_exists(DB_SETTINGS)) {
    $siteurl = rtrim(dirname(get_current_url()), '/').'/';
    $siteurl = str_replace('install/', '', $siteurl);
    $url = parse_url($siteurl);

    $update_settings_tbl = [
        'theme'              => 'Magazine',
        'rendertime_enabled' => '0',
        'timeoffset'         => 'Europe/London',
        'opening_page'       => 'home.php',
        'captcha'            => 'securimage3',
        'admin_theme'        => 'AdminLTE',
        'siteurl'            => $siteurl,
        'site_protocol'      => $url['scheme'],
        'site_host'          => $url['host'],
        'site_port'          => (isset($url['port']) ? $url['port'] : ''),
        'site_path'          => (isset($url['path']) ? $url['path'] : ''),
    ];

    foreach ($update_settings_tbl as $key => $value) {
        if (isset($settings[$key])) {
            $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
        }
    }

    // Insert rows to settings table
    $insert_settings_tbl = [
        'enabled_languages' => !empty($settings['enabled_languages']) ? $settings['enabled_languages'] : $settings['locale'],
        'site_seo'          => 0,
        'normalize_seo'     => 0,
        'debug_seo'         => 0,
        'login_method'      => 0,
        'mime_check'        => 0,
        'exclude_aupper'    => '',
        'exclude_blower'    => '',
        'exclude_user1'     => '',
        'exclude_user2'     => '',
        'exclude_user3'     => '',
        'exclude_user4'     => '',
        'admin_theme'       => 'AdminLTE',
        'bootstrap'         => 1,
        'entypo'            => 0,
        'fontawesome'       => 1,
        'allow_php_exe'     => 0,
        'privacy_policy'    => '',
        'create_og_tags'    => 1,
        'index_url_bbcode'  => 1,
        'index_url_userweb' => 1,
        'link_bbcode'       => 0,
        'week_start'        => 0,
        'links_grouping'    => 0,
        'recaptcha_type'    => 'text',
        'pm_inbox_limit'    => 20,
        'pm_outbox_limit'   => 20,
        'pm_archive_limit'  => 20,
        'pm_email_notify'   => 2,
        'pm_save_sent'      => 2
    ];

    foreach ($insert_settings_tbl as $key => $value) {
        if (!isset($settings[$key])) {
            $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
        }
    }
}

// Insert Multi Language rights and status
if (db_exists(DB_LANGUAGE_TABLES)) {
    $modules = [
        'AR' => db_exists(DB_PREFIX."articles"),
        'CP' => TRUE,
        'BL' => db_exists(DB_PREFIX.'blog'),
        'DL' => db_exists(DB_PREFIX."downloads"),
        'FQ' => db_exists(DB_PREFIX."faqs"),
        'FO' => db_exists(DB_PREFIX."forums"),
        'FR' => db_exists(DB_PREFIX."forums"),
        'NS' => db_exists(DB_PREFIX."news"),
        'PG' => db_exists(DB_PREFIX."photos"),
        'PO' => db_exists(DB_PREFIX.'polls'),
        'ET' => TRUE,
        'WL' => db_exists(DB_PREFIX."weblinks"),
        'SL' => TRUE,
        'PN' => TRUE,
        'PA' => db_exists(DB_PREFIX."photos"),
    ];

    $mlt_array = [
        'AR' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('AR', '".$locale['setup_3200']."', '1')", // Articles
        'CP' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('CP', '".$locale['setup_3201']."', '1')", // Custom Pages
        'BL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('BL', '".$locale['setup_3213']."', '1')", // Blog
        'DL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('DL', '".$locale['setup_3202']."', '1')", // Downloads
        'FQ' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('FQ', '".$locale['setup_3203']."', '1')", // Faq
        'FO' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('FO', '".$locale['setup_3204']."', '1')", // Forum
        'FR' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('FR', '".$locale['setup_3212']."', '1')", // Forum ranks
        'NS' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('NS', '".$locale['setup_3205']."', '1')", // News
        'PG' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('PG', '".$locale['setup_3206']."', '1')", // Gallery
        'PO' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('PO', '".$locale['setup_3207']."', '1')", // Polls
        'ET' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('ET', '".$locale['setup_3208']."', '1')", // Email Template
        'WL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('WL', '".$locale['setup_3209']."', '1')", // Weblinks
        'SL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('SL', '".$locale['setup_3210']."', '1')", // Site Links
        'PN' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('PN', '".$locale['setup_3211']."', '1')", // Panels
    ];

    foreach ($mlt_array as $key => $value) {
        $count = dbcount("('mlt_rights')", DB_LANGUAGE_TABLES, "mlt_rights='$key'");
        $modul = $modules[$key];
        if (empty($count) && !empty($modul)) {
            $inf_insertdbrow[] = $value;
        }
    }
}

if (db_exists(DB_ADMIN)) {

    // Remove the deprecated administration links
    $del_table = [
        'S5'   => 'settings_photo.php',
        'S3'   => 'settings_forum.php',
        'UFC'  => 'user_field_cats.php',
        'S11'  => 'settings_dl.php',
        'S10'  => 'settings_ipp.php',
        'SU'   => 'submissions.php',
        'BLOG' => 'blog.php',
        'BLC'  => 'blog_cats.php',
        'S13'  => 'settings_blog.php',
    ];

    foreach ($del_table as $key => $del_link) {
        dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_link='".$del_link."'");
    }

    // Add new core administration links
    $check_array = [
        'LANG' => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'language.png', '".$locale['setup_3051']."', 'settings_languages.php', '4')",
        'PL'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.png', '".$locale['setup_3052']."', 'permalink.php', '3')",
        'S3'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S3', 'theme_settings.png', '".$locale['setup_3058']."', 'settings_theme.php', '4')",
        'TS'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('TS', 'theme.png', '".$locale['setup_3056']."', 'theme.php', '3')",
        'MI'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MI', 'migration.png', '".$locale['setup_3057']."', 'migrate.php', '2')",
        'MAIL' => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.png', '".$locale['setup_3800']."', 'email.php', '3')",
    ];

    foreach ($check_array as $key => $value) {
        $count = dbcount("('admin_rights')", DB_ADMIN, "admin_rights='$key'");
        if (empty($count)) {
            $inf_insertdbrow[] = $value;
        }
    }

    // Update new administration icons
    $new_icon_array = [
        'APWR' => 'adminpass.png',
        'AD'   => 'administrator.png',
        'SB'   => 'banner.png',
        'BB'   => 'bbcodes.png',
        'B'    => 'blacklist.png',
        'CP'   => 'c-pages.png',
        'DB'   => 'db_backup.png',
        'MAIL' => 'email.png',
        'ERRO' => 'errors.png',
        'IM'   => 'images.png',
        'I'    => 'infusions.png',
        'LANG' => 'language.png',
        'S1'   => 'settings.png',
        'M'    => 'members.png',
        'MI'   => 'migration.png',
        'S6'   => 'misc.png',
        'P'    => 'panels.png',
        'PL'   => 'permalink.png',
        'PI'   => 'serverinfo.png',
        'S7'   => 'pm.png',
        'S4'   => 'registration.png',
        'ROB'  => 'robots.png',
        'S12'  => 'security.png',
        'SL'   => 'sitelinks.png',
        'SM'   => 'smileys.png',
        'TS'   => 'theme.png',
        'S3'   => 'theme_settings.png',
        'S2'   => 'time.png',
        'U'    => 'upgrade.png',
        'UF'   => 'user_fields.png',
        'UG'   => 'user_groups.png',
        'UL'   => 'user_log.png',
        'S9'   => 'user_settings.png',
        'FM'   => 'file_manager.png',
    ];

    foreach ($new_icon_array as $admin_rights => $icon_file) {
        $inf_updatedbrow[] = DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'";
    }

    // Fix all admin images prior to v9
    $result = dbquery("SELECT admin_id, admin_image, admin_link FROM ".DB_ADMIN." WHERE admin_rights NOT IN ('".implode("','", array_keys($new_icon_array))."')");
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $admin_image = "../".str_replace("../", "", $data["admin_image"]);
            $inf_updatedbrow[] = DB_ADMIN." SET `admin_image`='".$admin_image."' WHERE `admin_id`='".$data["admin_id"]."'";
        }
    }

}

if (db_exists(DB_PREFIX.'custom_pages')) {
    if (!column_exists(DB_CUSTOM_PAGES, 'page_breaks', FALSE)) {
        $inf_altertable[] = DB_CUSTOM_PAGES." ADD page_breaks CHAR(1) NOT NULL DEFAULT '' AFTER page_status";
    }

    $inf_updatedbrow[] = DB_CUSTOM_PAGES." SET page_access='-103' WHERE page_access='103'";
    $inf_updatedbrow[] = DB_CUSTOM_PAGES." SET page_access='-102' WHERE page_access='102'";
    $inf_updatedbrow[] = DB_CUSTOM_PAGES." SET page_access='-101' WHERE page_access='101'";
    $inf_updatedbrow[] = DB_CUSTOM_PAGES." SET page_status=1";

    $dropcol_aaray = [
        'page_allow_comments',
        'page_allow_ratings'
    ];

    foreach ($dropcol_aaray as $key) {
        if (column_exists(DB_CUSTOM_PAGES, $key, FALSE)) {
            $inf_dropcol[] = ['table' => DB_CUSTOM_PAGES, 'column' => $key];
        }
    }
}

$result = dbquery("SELECT * FROM ".DB_CUSTOM_PAGES);
if (dbrows($result) > 0) {
    while ($data = dbarray($result)) {
        $inf_updatedbrow[] = DB_CUSTOM_PAGES." SET page_breaks='n', page_grid_id='".$data['page_id']."', page_content_id='".$data['page_id']."', page_left_panel=1, page_right_panel=1, page_header_panel=1, page_footer_panel=1, page_top_panel=1, page_bottom_panel=1, page_user=1 WHERE page_id='".$data['page_id']."'";

        $data_content = [
            'page_id'           => $data['page_id'],
            'page_grid_id'      => $data['page_id'],
            'page_content_id'   => $data['page_id'],
            'page_content_type' => 'content',
            'page_content'      => $data['page_content'],
            'page_options'      => '',
            'page_widget'       => ''
        ];

        dbquery_insert(DB_CUSTOM_PAGES_CONTENT, $data_content, 'save');

        $data_content_grid = [
            'page_id'                => $data['page_id'],
            'page_grid_id'           => $data['page_id'],
            'page_grid_column_count' => 1,
            'page_grid_html_id'      => $data['page_id'],
            'page_grid_class'        => ''
        ];

        dbquery_insert(DB_CUSTOM_PAGES_GRID, $data_content_grid, 'save');
    }
}

if (db_exists(DB_PREFIX.'site_links')) {
    $inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url ='news_cats.php'";
    $inf_updatedbrow[] = DB_SITE_LINKS." SET link_visibility='-103' WHERE link_visibility='103'";
    $inf_updatedbrow[] = DB_SITE_LINKS." SET link_visibility='-102' WHERE link_visibility='102'";
    $inf_updatedbrow[] = DB_SITE_LINKS." SET link_visibility='-101' WHERE link_visibility='101'";
    if (column_exists(DB_SITE_LINKS, 'link_status')) {
        $inf_updatedbrow[] = DB_SITE_LINKS." SET link_status ='1'";
    }
}

if (db_exists(DB_PREFIX.'panels')) {
    // Update panel table column
    if (!column_exists(DB_PANELS, 'panel_status', FALSE)) {
        $inf_altertable[] = DB_PANELS." ADD panel_status tinyint(1) NOT NULL DEFAULT '1' AFTER panel_display";
    }

    // Alter all panel settings
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='1'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='2'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='3'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='4'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='5'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='6'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_restriction='3', panel_status='1' WHERE panel_filename='forum_threads_list_panel'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_restriction='3', panel_status='1' WHERE panel_filename='member_poll_panel'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_languages='".$settings['locale']."'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_status='0' WHERE panel_type='php' AND panel_content LIKE 'openside(%'"; // disable old php panels
}

if (db_exists(DB_USER_FIELD_CATS)) {
    // Dump old categories
    dbquery("TRUNCATE TABLE ".DB_PREFIX."user_field_cats");
    if (!dbcount("(field_cat_name)", DB_USER_FIELD_CATS, "field_cat_name='".$locale['setup_3640']."'")) {
        $field_cat_id = dbresult(dbquery("SELECT MAX(field_cat_id) 'field_rows' FROM ".DB_USER_FIELD_CATS), 0) + 1;
        // Install a new default set of UF cats
        $ufc_sql = DB_USER_FIELD_CATS." (field_cat_id, field_cat_name, field_parent, field_cat_db, field_cat_index, field_cat_class, field_cat_order) VALUES ";
        $ufc_sql .= implode(",\n", [
            "('".($field_cat_id++)."', '".$locale['setup_3640']."', 0, '', '', '', '1')",
            "('".($field_cat_id++)."', '".$locale['setup_3641']."', 1, '', '', '', '1')",
            "('".($field_cat_id++)."', '".$locale['setup_3642']."', 1, '', '', '', '2')",
            "('".($field_cat_id++)."', '".$locale['setup_3643']."', 1, '', '', '', '3')",
            "('".($field_cat_id++)."', '".$locale['setup_3644']."', 1, '', '', '', '4')",
        ]);

        $inf_insertdbrow[] = $ufc_sql;

        if (column_exists(DB_USER_FIELD_CATS, 'field_cat_page', FALSE)) {
            $inf_altertable[] = DB_USER_FIELD_CATS." DROP field_cat_page";
        }
    }
}

if (db_exists(DB_USER_FIELDS)) {
    // Install User Fields
    $previous_install = [];
    $uf_to_install = [];
    $result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS);

    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $previous_install[$data['field_name']] = TRUE;
        }
    }

    if (column_exists('users', 'user_location')) {
        if (!isset($previous_install['user_location'])) {
            $uf_to_install[] = "('".$locale['uf_location']."', 'user_location', '3', 'file', '', '', '1', '', '')";
        }
    }

    if (column_exists('users', 'user_birthdate')) {
        if (!isset($previous_install['user_birthdate'])) {
            $uf_to_install[] = "('".$locale['uf_birthdate']."', 'user_birthdate', '3', 'file', '1900-01-01', '', '2', '', '')";
        }
    }

    if (column_exists('users', 'user_skype')) {
        if (!isset($previous_install['user_skype'])) {
            $uf_to_install[] = "('".$locale['uf_skype']."', 'user_skype', '2', 'file', '', '', '1', '', '')";
        }
    }

    if (column_exists('users', 'user_icq')) {
        if (!isset($previous_install['user_icq'])) {
            $uf_to_install[] = "('".$locale['uf_icq']."', 'user_icq', '2', 'file', '', '', '3', '', '')";
        }
    }

    if (column_exists('users', 'user_web')) {
        if (!isset($previous_install['user_web'])) {
            $uf_to_install[] = "('".$locale['uf_web']."', 'user_web', '2', 'file', '', '', '6', '', '')";
        }
    }

    if (column_exists('users', 'user_timezone')) {
        if (!isset($previous_install['user_timezone'])) {
            $uf_to_install[] = "('".$locale['uf_timezone']."', 'user_timezone', '4', 'file', '', '', '6', '', '')";
        }
    }

    if (column_exists('users', 'user_theme')) {
        $inf_updatedbrow[] = DB_USERS." SET user_theme='Default'";
        if (!isset($previous_install['user_theme'])) {
            $uf_to_install[] = "('".$locale['uf_theme']."', 'user_theme', '4', 'file', '', '', '2', '', '')";

        }
    }

    if (column_exists('users', 'user_sig')) {
        if (!isset($previous_install['user_sig'])) {
            $uf_to_install[] = "('".$locale['uf_sig']."', 'user_sig', '4', 'file', '', '', '3', '', '')";
        }
    }

    if (!empty($uf_to_install)) {
        $uf_sql = DB_USER_FIELDS." (field_title, field_name, field_cat, field_type, field_default, field_options, field_order, field_error, field_config) VALUES ";
        $uf_sql .= implode(",", $uf_to_install);

        $inf_insertdbrow[] = $uf_sql;
    }
}

if (column_exists('users', 'user_offset')) {
    $inf_dropcol[] = ['table' => DB_USERS, 'column' => 'user_offset'];
}

if (db_exists(DB_INFUSIONS)) {
    // The infusions table should not exist in any version prior to this:
    $check_infusions = [
        DB_PREFIX."articles"  => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3002']."', 'articles', '0')",
            'check'  => 'articles'
        ],
        DB_PREFIX."downloads" => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3010']."', 'downloads', '0')",
            'check'  => 'downloads'
        ],
        DB_PREFIX."faqs"      => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3011']."', 'faq', '0')",
            'check'  => 'faq'
        ],
        DB_PREFIX."forums"    => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3012']."', 'forum', '0')",
            'check'  => 'forum'
        ],
        DB_PREFIX."photos"    => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3020']."', 'gallery', '0')",
            'check'  => 'gallery'
        ],
        DB_PREFIX."news"      => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3018']."', 'news', '0')",
            'check'  => 'news'
        ],
        DB_PREFIX."weblinks"  => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3029']."', 'weblinks', '0')",
            'check'  => 'weblinks'
        ],
        DB_PREFIX."shoutbox"  => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3316']."', 'shoutbox_panel', '0')",
            'check'  => 'shoutbox_panel'
        ],
        DB_PREFIX."polls"     => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3407']."', 'member_poll_panel', '0')",
            'check'  => 'member_poll_panel'
        ]
    ];

    foreach ($check_infusions as $table_name => $value) {
        if (!dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='".$value['check']."'") && db_exists($table_name)) {
            $inf_insertdbrow[] = $value['insert'];
        }
    }
}

$result = dbquery("SELECT * FROM ".DB_SMILEYS);
if (dbrows($result)) {
    $arr_defaults = [
        'smile.gif' => 'smile.svg',
        'wink.gif'  => 'wink.svg',
        'sad.gif'   => 'sad.svg',
        'frown.gif' => 'frown.svg',
        'shock.gif' => 'shock.svg',
        'pfft.gif'  => 'pfft.svg',
        'cool.gif'  => 'cool.svg',
        'grin.gif'  => 'grin.svg',
        'angry.gif' => 'angry.svg',
        'like.gif'  => 'like.svg'
    ];

    while ($data = dbarray($result)) {
        $data['smiley_image'] = strtr($data['smiley_image'], $arr_defaults);
        dbquery_insert(DB_SMILEYS, $data, 'update', ['keep_session' => TRUE]);
    }
}
