<?php

// the enabled languages in step 1. take it out. put it behind.
function get_core_data($locale) {

    $siteurl = rtrim(dirname(getCurrentURL()), '/').'/';
    $siteurl = str_replace('install/', '', $siteurl);
    $url = parse_url($siteurl);
    $table_settings['settings'] = [
        'insert' =>
            [
                'settings_name' => 'site_name',
                'settings_value' => 'PHP-Fusion Powered Website', // localize this
            ],
        [
            'settings_name' => 'siteurl',
            'settings_value' => $siteurl
        ],
        [
            'settings_name' => 'site_protocol',
            'settings_value' => $url['scheme']
        ],
        [
            'settings_name' => 'site_host',
            'settings_value' => $url['host'],
        ],
        [
            'settings_name' => 'site_port',
            'settings_value' => (isset($url['port']) ? $url['port'] : ''),
        ],
        [
            'settings_name' => 'site_path',
            'settings_value' => (isset($url['path']) ? $url['path'] : ''),
        ],
        [
            'settings_name' => 'site_seo',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'sitebanner',
            'settings_value' => 'images/php-fusion-logo.png'
        ],
        [
            'settings_name' => 'sitebanner1',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'sitebanner1',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'sitebanner2',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'siteemail',
            'settings_value' => ''
        ],//fill in
        [
            'settings_name' => 'siteusername',
            'settings_value' => ''
        ],// fill in
        [
            'settings_name' => 'siteintro',
            'settings_value' => '<div class="text-center">'.$locale['setup_3650'].'</div>'
        ],
        [
            'settings_name' => 'description',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'keywords',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'footer',
            'settings_value' => '<div class="text-center">Copyright &copy; '.@date("Y").'</div>'
        ],
        [
            'settings_name' => 'opening_page',
            'settings_value' => 'viewpage.php?page_id=1'
        ],
        [
            'settings_name' => 'locale',
            'settings_value' => isset($_GET['locale']) ? $_GET['locale'] : 'English'
        ],
        [
            'settings_name' => 'bootstrap',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'entypo',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'fontawesome',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'theme',
            'settings_value' => 'FusionTheme'
        ],
        [
            'settings_name' => 'admin_theme',
            'settings_value' => 'Artemis'
        ],
        [
            'settings_name' => 'default_search',
            'settings_value' => 'all'
        ],
        [
            'settings_name' => 'exclude_left',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'exclude_upper',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'exclude_lower',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'exclude_aupper',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'exclude_blower',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'exclude_right',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'shortdate',
            'settings_value' => $locale['setup_3700'],
        ],
        [
            'settings_name' => 'longdate',
            'settings_value' => $locale['setup_3701'],
        ],
        [
            'settings_name' => 'forumdate',
            'settings_value' => $locale['setup_3702'],
        ],
        [
            'settings_name' => 'newsdate',
            'settings_value' => $locale['setup_3703'],
        ],
        [
            'settings_name' => 'subheaderdate',
            'settings_value' => $locale['setup_3704']
        ],
        [
            'settings_name' => 'timeoffset',
            'settings_value' => 'Europe/London'
        ],
        [
            'settings_name' => 'serveroffset',
            'settings_value' => 'Europe/London',
        ],
        [
            'settings_name' => 'week_start',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'enable_registration',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'email_verification',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'admin_activation',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'display_validation',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'enable_deactivation',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'deactivation_period',
            'settings_value' => 365
        ],
        [
            'settings_name' => 'deactivation_response',
            'settings_value' => 14
        ],
        [
            'settings_name' => 'enable_terms',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'license_agreement',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'license_lastupdate',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'thumb_compression',
            'settings_value' => 'gd2'
        ],
        [
            'settings_name' => 'tinymce_enabled',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'smtp_host',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'smtp_port',
            'settings_value' => 25
        ],
        [
            'settings_name' => 'smtp_username',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'smtp_password',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'bad_words_enabled',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'bad_words',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'bad_word_replace',
            'settings_value' => '****'
        ],
        [
            'settings_name' => 'login_method',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'guestposts',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'comments_enabled',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'comments_jquery',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'ratings_enabled',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'hide_userprofiles',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'userthemes',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'flood_interval',
            'settings_value' => 15
        ],
        [
            'settings_name' => 'counter',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'version',
            'settings_value' => \PHPFusion\Installer\Install_Core::BUILD_VERSION
        ],
        [
            'settings_name' => 'maintenance',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'maintenance_message',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'links_per_page',
            'settings_value' => 8
        ],
        [
            'settings_name' => 'links_grouping',
            'settings_value' => 8
        ],
        [
            'settings_name' => 'comments_per_page',
            'settings_value' => 10
        ],
        [
            'settings_name' => 'comments_sorting',
            'settings_value' => 'ASC'
        ],
        [
            'settings_name' => 'comments_avatar',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'avatar_width',
            'settings_value' => 100
        ],
        [
            'settings_name' => 'avatar_height',
            'settings_value' => 100
        ],
        [
            'settings_name' => 'avatar_filesize',
            'settings_value' => 50000
        ],
        [
            'settings_name' => 'avatar_ratio',
            'settings_value' => 0
        ],

        [
            'settings_name' => 'cronjob_day',
            'settings_value' => NOW()
        ],
        [
            'settings_name' => 'cronjob_hour',
            'settings_value' => NOW()
        ],
        [
            'settings_name' => 'flood_autoban',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'visitorcounter_enabled',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'rendertime_enabled',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'maintenance_level',
            'settings_value' => USER_LEVEL_ADMIN
        ],
        [
            'settings_name' => 'deactivation_action',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'captcha',
            'settings_value' => 'grecaptcha'
        ],
        [
            'settings_name' => 'password_algorithm',
            'settings_value' => 'sha256'
        ],
        [
            'settings_name' => 'default_timezone',
            'settings_value' => 'Europe/London'
        ],
        [
            'settings_name' => 'userNameChange',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'recaptcha_public',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'recaptcha_private',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'recaptcha_theme',
            'settings_value' => 'light'
        ],
        [
            'settings_name' => 'recaptcha_type',
            'settings_value' => 'text'
        ],
        [
            'settings_name' => 'allow_php_exe',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'multiple_logins',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'smtp_auth',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'mime_check',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'normalize_seo',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'debug_seo',
            'settings_value' => 0
        ],
        [
            'settings_name' => 'privacy_policy',
            'settings_value' => ''
        ],
        [
            'settings_name' => 'create_og_tags',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'index_url_bbcode',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'index_url_userweb',
            'settings_value' => 1
        ],
        [
            'settings_name' => 'pm_inbox_limit',
            'settings_value' => 20
        ],
        [
            'settings_name' => 'pm_outbox_limit',
            'settings_value' => 20
        ],
        [
            'settings_name' => 'pm_archive_limit',
            'settings_value' => 20
        ],
        [
            'settings_name' => 'pm_email_notify',
            'settings_value' => 2
        ],
        [
            'settings_name' => 'save_sent',
            'settings_value' => 2
        ],
        [
            'settings_name' => 'enabled_languages',
            'settings_value' => $_GET['localeset']
            //(empty($_POST['enabled_languages']) ? stripinput($_POST['localeset']) : stripinput($_POST['enabled_languages']) ),
        ]
    ];
    $table_settings['mlt_tables'] = [
        'insert' =>
            [
                'mlt_rights' => 'SL',
                'mlt_title' => $locale['setup_3023'],
                'mlt_status' => 1
            ],
        [
            'mlt_rights' => 'CP',
            'mlt_title' => $locale['setup_3201'],
            'mlt_status' => 1
        ],
        [
            'mlt_rights' => 'ET',
            'mlt_title' => $locale['setup_3208'],
            'mlt_status' => 1
        ],
        [
            'mlt_rights' => 'PN',
            'mlt_title' => $locale['setup_3211'],
            'mlt_status' => 1
        ]
    ];


}


/*
 *
    $admin_sql = "INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ";
    $admin_sql .= implode(",\n", array(
        'AD', 'administrator.png', '".$locale['setup_3000']."', 'administrators.php', '2')",
        'APWR', 'adminpass.png', '".$locale['setup_3047']."', 'admin_reset.php', '2')",
        'SB', 'banner.png', '".$locale['setup_3003']."', 'banners.php', '3')",
        'BB', 'bbcodes.png', '".$locale['setup_3004']."', 'bbcodes.php', '3')",
        'B', 'blacklist.png', '".$locale['setup_3005']."', 'blacklist.php', '2')",
        'C', '', '".$locale['setup_3006']."', 'reserved', '2')",
        'CP', 'c-pages.png', '".$locale['setup_3007']."', 'custom_pages.php', '1')",
        'DB', 'db_backup.png', '".$locale['setup_3008']."', 'db_backup.php', '3')",
        'ERRO', 'errors.png', '".$locale['setup_3048']."', 'errors.php', '3')",
        'IM', 'images.png', '".$locale['setup_3013']."', 'images.php', '1')",
        'I', 'infusions.png', '".$locale['setup_3014']."', 'infusions.php', '3')",
        'IP', '', '".$locale['setup_3015']."', 'reserved', '3')",
        'M', 'members.png', '".$locale['setup_3016']."', 'members.php', '2')",
        'MI', 'migration.png', '".$locale['setup_3057']."', 'migrate.php', '2')",
        'P', 'panels.png', '".$locale['setup_3019']."', 'panels.php', '3')",
        'PL', 'permalink.png', '".$locale['setup_3052']."', 'permalink.php', '3')",
        'PI', 'phpinfo.png', '".$locale['setup_3021']."', 'phpinfo.php', '3')",
        'SL', 'sitelinks.png', '".$locale['setup_3023']."', 'site_links.php', '3')",
        'SM', 'smileys.png', '".$locale['setup_3024']."', 'smileys.php', '3')",
        'U', 'upgrade.png', '".$locale['setup_3026']."', 'upgrade.php', '3')",
        'TS', 'theme.png', '".$locale['setup_3056']."', 'theme.php', '3')",
        'UG', 'user_groups.png', '".$locale['setup_3027']."', 'user_groups.php', '2')",
        'S1', 'settings.png', '".$locale['setup_3030']."', 'settings_main.php', '4')",
        'S2', 'time.png', '".$locale['setup_3031']."', 'settings_time.php', '4')",
        'S3', 'theme_settings.png', '".$locale['setup_3058']."', 'settings_theme.php', '4')",
        'S4', 'registration.png', '".$locale['setup_3033']."', 'settings_registration.php', '4')",
        'S6', 'misc.png', '".$locale['setup_3035']."', 'settings_misc.php', '4')",
        'S7', 'pm.png', '".$locale['setup_3036']."', 'settings_messages.php', '4')",
        'S9', 'user_settings.png', '".$locale['setup_3041']."', 'settings_users.php', '4')",
        'S12', 'security.png', '".$locale['setup_3044']."', 'settings_security.php', '4')",
        'UF', 'user_fields.png', '".$locale['setup_3037']."', 'user_fields.php', '2')",
        'UL', 'user_log.png', '".$locale['setup_3049']."', 'user_log.php', '2')",
        'ROB', 'robots.png', '".$locale['setup_3050']."', 'robots.php', '3')",
        'MAIL', 'email.png', '".$locale['setup_3800']."', 'email.php', '3')",
        'LANG', 'language.png', '".$locale['setup_3051']."', 'settings_languages.php', '4')"
    ));
    if (!dbquery($admin_sql)) {
        $fail = TRUE;
    }

    // default theme
    if (!dbquery("INSERT INTO ".$db_prefix."settings_theme (settings_name, settings_value, settings_theme) VALUES ('theme_pack', 'Nebula', 'FusionTheme')")) {
        $fail = TRUE;
    };

    if (!dbquery("INSERT INTO ".$db_prefix."messages_options (user_id, pm_email_notify, pm_save_sent, pm_inbox, pm_savebox, pm_sentbox) VALUES ('0', '0', '1', '20', '20', '20')")) {
        $fail = TRUE;
    }

    $bbcodes_sql = "INSERT INTO ".$db_prefix."bbcodes (bbcode_name, bbcode_order) VALUES ";
    $bbcodes_sql .= implode(",\n", array(
        'smiley', '1')",
        'b', '2')",
        'i', '3')",
        'u', '4')",
        'url', '5')",
        'mail', '6')",
        'img', '7')",
        'center', '8')",
        'small', '9')",
        'code', '10')",
        'quote', '11')"
    ));

    if (!dbquery($bbcodes_sql)) {
        $fail = TRUE;
    }
    $smileys_sql = "INSERT INTO ".$db_prefix."smileys (smiley_code, smiley_image, smiley_text) VALUES ";
    $smileys_sql .= implode(",\n", array(
        ':)', 'smile.gif', '".$locale['setup_3620']."')",
        ';)', 'wink.gif', '".$locale['setup_3621']."')",
        ':(', 'sad.gif', '".$locale['setup_3622']."')",
        ':|', 'frown.gif', '".$locale['setup_3623']."')",
        ':o', 'shock.gif', '".$locale['setup_3624']."')",
        ':P', 'pfft.gif', '".$locale['setup_3625']."')",
        'B)', 'cool.gif', '".$locale['setup_3626']."')",
        ':D', 'grin.gif', '".$locale['setup_3627']."')",
        ':@', 'angry.gif', '".$locale['setup_3628']."')"
    ));
    if (!dbquery($smileys_sql)) {
        $fail = TRUE;
    }

    $panels_sql = "INSERT INTO ".$db_prefix."panels (panel_name,
	panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status,
	panel_url_list, panel_restriction) VALUES ";
    $panels_sql .= implode(",\n", array(
        '".$locale['setup_3400']."', 'css_navigation_panel', '', '1', '1', 'file', '0', '1', '1', '', '3')",
        'RSS Feeds', 'rss_feeds_panel', '', '1', '2', 'file', '0', '0', '1', 'home.php', '2')",
        '".$locale['setup_3401']."', 'online_users_panel', '', '1', '3', 'file', '0', '1', '1', '', '3')",
        '".$locale['setup_3404']."', 'welcome_message_panel', '', '2', '1', 'file', '0', '0', '1', 'home.php', '2')",
        '".$locale['setup_3406']."', 'user_info_panel', '', '4', '1', 'file', '0', '1', '1', '', '3')"
    ));
    if (!dbquery($panels_sql)) {
        $fail = TRUE;
    }

    // UF 1.02
    $ufc_sql = "INSERT INTO ".$db_prefix."user_field_cats (field_cat_id, field_cat_name, field_parent, field_cat_db, field_cat_index, field_cat_class, field_cat_order) VALUES ";
    $ufc_sql .= implode(",\n", array(
        1, '".$locale['setup_3640']."', 0, '', '', 'entypo user', 1)",
        2, '".$locale['setup_3641']."', 1, '', '', 'entypo user', 1)",
        3, '".$locale['setup_3642']."', 1, '', '', 'entypo user', 2)",
        4, '".$locale['setup_3643']."', 1, '', '', 'entypo user', 3)",
        5, '".$locale['setup_3644']."', 1, '', '', 'entypo user', 4)",
        6, '".$locale['setup_3645']."', 1, '', '', 'entypo shareable', 5)"
    ));
    if (!dbquery($ufc_sql)) {
        $fail = TRUE;
    }

    // Install UF Modules
    $uf_sql = "INSERT INTO ".$db_prefix."user_fields (field_name, field_title, field_cat, field_type, field_required, field_order, field_default, field_options, field_error, field_config) VALUES ";
    $uf_sql .= implode(",\n", array(
        'user_location', '".$locale['uf_location']."', '3', 'file', '0', '1', '', '', '', '')",
        'user_birthdate', '".$locale['uf_birthdate']."', '3', 'file', '0', '2', '1900-01-01', '', '', '')",
        'user_skype', '".$locale['uf_skype']."', '2', 'file', '0', '1', '', '', '', '')",
        'user_aim', '".$locale['uf_aim']."', '2', 'file', '0', '2', '', '', '', '')",
        'user_icq', '".$locale['uf_icq']."', '2', 'file', '0', '3', '', '', '', '')",
        'user_yahoo', '".$locale['uf_yahoo']."', '2', 'file', '0', '5', '', '', '', '')",
        'user_web', '".$locale['uf_web']."', '2', 'file', '0', '6', '', '', '', '')",
        'user_timezone', '".$locale['uf_timezone']."', '4', 'file', '0', '1', '', '', '', '')",
        'user_theme', '".$locale['uf_theme']."', '4', 'file', '0', '2', '', '', '', '')",
        'user_sig', '".$locale['uf_sig']."', '4', 'file', '0', '3', '', '', '', '')",
        'user_blacklist', '".$locale['uf_blacklist']."', '5', 'file', '0', '1', '', '', '', '')"
    ));
    if (!dbquery($uf_sql)) {
        $fail = TRUE;
    }

    $sl_sql = "INSERT INTO ".$db_prefix."site_links (link_name, link_cat, link_icon, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ";
    $sl_sql .= implode(",\n", array_map(function ($language) {
        $locale = array();
        include LOCALE.$language."/setup.php";

        return '".$locale['setup_3300']."', '0', '', 'index.php', '0', '2', '0', '1', '".$language."'),
				('".$locale['setup_3305']."', '0', '', 'contact.php', '0', '1', '0', '8', '".$language."'),
				('".$locale['setup_3309']."', '0', '', 'search.php', '0', '1', '0', '10', '".$language."'),
				('".$locale['setup_3315']."', '0', '', 'submissions.php', '-101', '1', '0', '10', '".$language."'),
				('---', '0', '', '---', '-101', '1', '0', '11', '".$language."')";
    }, explode('.', $enabled_languages)));
    if (!dbquery($sl_sql)) {
        $fail = TRUE;
    }
    $et_sql = "INSERT INTO ".$db_prefix."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ";
    $et_sql .= implode(",\n", array_map(function ($language) use ($username, $email) {
        include LOCALE.$language."/setup.php";
        return 'PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$username."', '".$email."', '".$language."'),
				('POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$username."', '".$email."', '".$language."'),
				('CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$username."', '".$email."', '".$language."')";
    }, explode('.', $enabled_languages)));
    if (!dbquery($et_sql)) {
        $fail = TRUE;
    }
 */