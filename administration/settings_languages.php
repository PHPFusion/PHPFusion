<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_languages.php
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
require_once __DIR__.'/../maincore.php';
pageAccess("LANG");
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'setup.php']);

// Just follow the display of the current admin language.
if (!empty($locale['setup_3007'])) {
    dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3007']."' WHERE mlt_rights='CP'");
}
if (!empty($locale['setup_3210'])) {
    dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3210']."' WHERE mlt_rights='SL'");
}
if (!empty($locale['setup_3208'])) {
    dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3208']."' WHERE mlt_rights='ET'");
}
if (!empty($locale['setup_3211'])) {
    dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['setup_3211']."' WHERE mlt_rights='PN'");
}

$inf_result = dbquery("SELECT * FROM ".DB_INFUSIONS);
if (dbrows($inf_result) > 0) {
    while ($cdata = dbarray($inf_result)) {
        include INFUSIONS.$cdata['inf_folder']."/infusion.php"; // there is a system language inside. // cant read into system language.
        if (isset($inf_mlt) && is_array($inf_mlt)) {
            $inf_mlt = flatten_array($inf_mlt);
            if (!empty($inf_mlt['title']) && !empty($inf_mlt['rights'])) {
                dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$inf_mlt['title']."' WHERE mlt_rights='".$inf_mlt['rights']."'");
            } else {
                //$defender->stop();
                addNotice("danger",
                    "Error due to incomplete locale translations in infusions folder ".$cdata['inf_folder'].". This infusion does not have the localized title and change is aborted. Please translate setup.php.");
            }
        }
        unset($inf_mlt);
    }
}

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN."settings_languages.php".fusion_get_aidlink(), 'title' => $locale['682ML']]);

if (isset($_POST['savesettings'])) {
    $inputData = [
        "localeset"             => form_sanitizer($_POST['localeset'], fusion_get_settings('locale'), "localeset"),
        "old_localeset"         => form_sanitizer($_POST['old_localeset'], fusion_get_settings('locale'), "old_localeset"),
        "enabled_languages"     => isset($_POST['enabled_languages']) ? form_sanitizer($_POST['enabled_languages'], "",
            "enabled_languages") : fusion_get_settings('locale'),
        // returns Chinese_Simplified,English,Malay
        "old_enabled_languages" => form_sanitizer($_POST['old_enabled_languages'], "", "old_enabled_languages"),
        // returns Chinese_Simplified.English.Malay
    ];

    // format both to .
    if (empty($inputData['enabled_languages'])) {
        \defender::stop();
        addNotice("danger", "You need to enable at least one language");
    }

    if (defender::safe()) {

        $inArray_SQLCond = [
            "enabled_languages"     => str_replace(".", "','", $inputData['enabled_languages']),
            "old_enabled_languages" => str_replace(".", "','", $inputData['old_enabled_languages'])
        ];
        $core_SQLVal = [
            "enabled_languages"     => str_replace(",", ".", $inputData['enabled_languages']),
            "old_enabled_languages" => str_replace(",", ".", $inputData['old_enabled_languages'])
        ];

        $array = [
            "old_enabled_languages" => explode(".", $inputData['old_enabled_languages']),
            "enabled_languages"     => explode(",", $inputData['enabled_languages'])
        ];

        // update current system locale
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$inputData['localeset']."' WHERE settings_name='locale'"); // update on the new system locale.

        // Switch visible language to localeset
        if ($inputData['old_localeset'] !== $inputData['localeset']) {
            set_language($inputData['localeset']);
        }

        /**
         * Part II : Insert and Purge actions when add or drop languages
         */

        if ($inputData['old_enabled_languages'] != $inputData['enabled_languages']) { // language family have changed
            $added_language = array_diff($array['enabled_languages'], $array['old_enabled_languages']);
            $removed_language = array_diff($array['old_enabled_languages'], $array['enabled_languages']);

            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$core_SQLVal['enabled_languages']."' WHERE settings_name='enabled_languages'");
            dbquery("UPDATE ".DB_PANELS." SET panel_languages='".$core_SQLVal['enabled_languages']."'");
            dbquery("UPDATE ".DB_USERS." SET user_language='Default' WHERE user_language NOT IN ('".$inArray_SQLCond['enabled_languages']."')");

            if (!empty($added_language)) {
                foreach ($added_language as $language) {
                    include LOCALE.$language."/setup.php";
                    $settings = fusion_get_settings();

                    /**
                     * Email templates
                     */

                    $language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$language."'"));
                    if (is_null($language_exist['template_language'])) {
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                    }

                    /**
                     * Home Site Links
                     */

                    $home_links = [
                        ['link_name' => $locale['setup_3300'], 'link_cat' => '0', 'link_url' => 'index.php', 'link_visibility' => '0', 'link_position' => '2', 'link_order' => '1', 'link_language' => $language],
                        ['link_name' => $locale['setup_3305'], 'link_cat' => '0', 'link_url' => 'contact.php', 'link_visibility' => '0', 'link_position' => '3', 'link_order' => '8', 'link_language' => $language],
                        ['link_name' => $locale['setup_3309'], 'link_cat' => '0', 'link_url' => 'search.php', 'link_visibility' => '0', 'link_position' => '1', 'link_order' => '10', 'link_language' => $language],
                        ['link_name' => '---', 'link_cat' => '0', 'link_url' => '---', 'link_visibility' => '-101', 'link_position' => '1', 'link_order' => '11', 'link_language' => $language]
                    ];

                    foreach ($home_links as $link) {
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_cat, link_url, link_visibility, link_position, link_status, link_window, link_order, link_language) VALUES ('".$link['link_name']."', '".$link['link_cat']."', '".$link['link_url']."', '".$link['link_visibility']."', '".$link['link_position']."', 1, 0, '".$link['link_order']."', '".$link['link_language']."')");
                    }

                    /**
                     * Admin Links
                     */

                    $admin_links = [
                        ['admin_rights' => 'AD', 'admin_image' => 'administrator.png', 'admin_title' => $locale['setup_3000'], 'admin_link' => 'administrators.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'APWR', 'admin_image' => 'adminpass.png', 'admin_title' => $locale['setup_3047'], 'admin_link' => 'admin_reset.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'SB', 'admin_image' => 'banner.png', 'admin_title' => $locale['setup_3003'], 'admin_link' => 'banners.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'BB', 'admin_image' => 'bbcodes.png', 'admin_title' => $locale['setup_3004'], 'admin_link' => 'bbcodes.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'B', 'admin_image' => 'blacklist.png', 'admin_title' => $locale['setup_3005'], 'admin_link' => 'blacklist.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'C', 'admin_image' => 'comments.png', 'admin_title' => $locale['setup_3006'], 'admin_link' => 'comments.php', 'admin_page' => 1, 'admin_language' => $language],
                        ['admin_rights' => 'CP', 'admin_image' => 'c-pages.png', 'admin_title' => $locale['setup_3007'], 'admin_link' => 'custom_pages.php', 'admin_page' => 1, 'admin_language' => $language],
                        ['admin_rights' => 'DB', 'admin_image' => 'db_backup.png', 'admin_title' => $locale['setup_3008'], 'admin_link' => 'db_backup.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'ERRO', 'admin_image' => 'errors.png', 'admin_title' => $locale['setup_3048'], 'admin_link' => 'errors.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'IM', 'admin_image' => 'images.png', 'admin_title' => $locale['setup_3013'], 'admin_link' => 'images.php', 'admin_page' => 1, 'admin_language' => $language],
                        ['admin_rights' => 'I', 'admin_image' => 'infusions.png', 'admin_title' => $locale['setup_3014'], 'admin_link' => 'infusions.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'IP', 'admin_image' => '', 'admin_title' => $locale['setup_3015'], 'admin_link' => 'reserved', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'M', 'admin_image' => 'members.png', 'admin_title' => $locale['setup_3016'], 'admin_link' => 'members.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'MI', 'admin_image' => 'migration.png', 'admin_title' => $locale['setup_3057'], 'admin_link' => 'migrate.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'P', 'admin_image' => 'panels.png', 'admin_title' => $locale['setup_3019'], 'admin_link' => 'panels.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'PL', 'admin_image' => 'permalink.png', 'admin_title' => $locale['setup_3052'], 'admin_link' => 'permalink.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'PI', 'admin_image' => 'phpinfo.png', 'admin_title' => $locale['setup_3021'], 'admin_link' => 'phpinfo.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'SL', 'admin_image' => 'sitelinks.png', 'admin_title' => $locale['setup_3023'], 'admin_link' => 'site_links.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'SM', 'admin_image' => 'smileys.png', 'admin_title' => $locale['setup_3024'], 'admin_link' => 'smileys.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'U', 'admin_image' => 'upgrade.png', 'admin_title' => $locale['setup_3026'], 'admin_link' => 'upgrade.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'TS', 'admin_image' => 'theme.png', 'admin_title' => $locale['setup_3056'], 'admin_link' => 'theme.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'UG', 'admin_image' => 'user_groups.png', 'admin_title' => $locale['setup_3027'], 'admin_link' => 'user_groups.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'S1', 'admin_image' => 'settings.png', 'admin_title' => $locale['setup_3030'], 'admin_link' => 'settings_main.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S2', 'admin_image' => 'time.png', 'admin_title' => $locale['setup_3031'], 'admin_link' => 'settings_time.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S3', 'admin_image' => 'theme_settings.png', 'admin_title' => $locale['setup_3058'], 'admin_link' => 'settings_theme.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S4', 'admin_image' => 'registration.png', 'admin_title' => $locale['setup_3033'], 'admin_link' => 'settings_registration.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S6', 'admin_image' => 'misc.png', 'admin_title' => $locale['setup_3035'], 'admin_link' => 'settings_misc.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S7', 'admin_image' => 'pm.png', 'admin_title' => $locale['setup_3036'], 'admin_link' => 'settings_messages.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S9', 'admin_image' => 'user_settings.png', 'admin_title' => $locale['setup_3041'], 'admin_link' => 'settings_users.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S12', 'admin_image' => 'security.png', 'admin_title' => $locale['setup_3044'], 'admin_link' => 'settings_security.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'UF', 'admin_image' => 'user_fields.png', 'admin_title' => $locale['setup_3037'], 'admin_link' => 'user_fields.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'UL', 'admin_image' => 'user_log.png', 'admin_title' => $locale['setup_3049'], 'admin_link' => 'user_log.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'ROB', 'admin_image' => 'robots.png', 'admin_title' => $locale['setup_3050'], 'admin_link' => 'robots.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'MAIL', 'admin_image' => 'email.png', 'admin_title' => $locale['setup_3800'], 'admin_link' => 'email.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'LANG', 'admin_image' => 'language.png', 'admin_title' => $locale['setup_3051'], 'admin_link' => 'settings_languages.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'FM', 'admin_image' => 'file_manager.png', 'admin_title' => $locale['setup_3059'], 'admin_link' => 'file_manager.php', 'admin_page' => 1, 'admin_language' => $language]
                    ];

                    foreach ($admin_links as $link) {
                        dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('".$link['admin_rights']."', '".$link['admin_image']."', '".$link['admin_title']."', '".$link['admin_link']."', '".$link['admin_page']."', '".$link['admin_language']."')");
                    }

                }
            }

            if (!empty($removed_language)) {
                foreach ($removed_language as $language) {
                    // Remove any dropped language email templates
                    $email_language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$language."'"));
                    if (!empty($email_language_exist['template_language'])) {
                        dbquery("DELETE FROM ".DB_EMAIL_TEMPLATES." WHERE template_language = '".$language."'");
                    }

                    $admin_language_exist = dbarray(dbquery("SELECT admin_language FROM ".DB_ADMIN." WHERE admin_language ='".$language."'"));
                    if (!empty($admin_language_exist['admin_language'])) {
                        dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_language = '".$language."'");
                    }
                }
            }

            // Update all infusions and remove registered multilang table records

            $inf_result = dbquery("SELECT * FROM ".DB_INFUSIONS);

            $lang_cmd = [];

            if (dbrows($inf_result) > 0) {

                while ($cdata = dbarray($inf_result)) {

                    /**
                     * This will loop x amount of times of installed infusion.
                     */
                    $mlt_adminpanel = [];
                    $mlt_insertdbrow = [];
                    $mlt_deldbrow = [];

                    include INFUSIONS.$cdata['inf_folder']."/infusion.php";

                    if (!empty($added_language)) {
                        $last_id = 0;
                        foreach ($added_language as $language) {
                            if (isset($mlt_insertdbrow[$language])) {
                                $last_id = 0;
                                foreach ($mlt_insertdbrow[$language] as $sql) {
                                    if (stristr($sql, "{last_id}") && !empty($last_id)) {
                                        dbquery("INSERT INTO ".str_replace("{last_id}", $last_id, $sql));
                                    } else {
                                        dbquery("INSERT INTO ".$sql);
                                        $last_id = dblastid();
                                    }
                                }
                                unset($mlt_insertdbrow[$language]);
                            }

                            if (isset($mlt_adminpanel[$language])) {
                                foreach ($mlt_adminpanel[$language] as $adminpanel) {
                                    $link_prefix = (defined('ADMIN_PANEL') ? '' : '../').INFUSIONS.$cdata['inf_folder'].'/';
                                    $inf_admin_image = ($adminpanel['image'] ?: "infusion_panel.png");

                                    if (empty($adminpanel['page'])) {
                                        $item_page = 5;
                                    } else {
                                        $item_page = isnum($adminpanel['page']) ? $adminpanel['page'] : 5;
                                    }

                                    dbquery("INSERT INTO ".DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('".$adminpanel['rights']."', '".$link_prefix.$inf_admin_image."', '".$adminpanel['title']."', '".$link_prefix.$adminpanel['panel']."', '".$item_page."', '".$adminpanel['language']."')");
                                }
                            }
                        }
                    }

                    if (!empty($removed_language)) {
                        foreach ($removed_language as $language) {
                            $lang_cmd['delete'][] = DB_SITE_LINKS." WHERE link_url='index.php' AND link_language='".$language."'";

                            if (isset($mlt_deldbrow[$language])) {
                                foreach ($mlt_deldbrow[$language] as $sql) {
                                    $lang_cmd['delete'][] = $sql;
                                }
                                unset($mlt_deldbrow[$language]);
                            }
                        }
                    }
                } // endwhile infusions loop

                if (!empty($removed_language)) {
                    foreach ($removed_language as $language) {
                        dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_language='".$language."'");
                    }

                    $result = dbquery("SELECT link_id, link_language FROM ".DB_SITE_LINKS);
                    if (dbrows($result) > 0) {
                        while ($data = dbarray($result)) {
                            if (stristr($data['link_language'], ".")) {
                                $link_language = explode(".", $data['link_language']);
                                $link_language = array_flip($link_language);
                                foreach ($removed_language as $language) {
                                    if (isset($link_language[$language])) {
                                        unset($link_language[$language]);
                                    }
                                }
                                $link_language = array_flip($link_language);
                                $link_language = implode(".", $link_language);
                                $lang_cmd['update'][$data['link_id']] = DB_SITE_LINKS." SET link_language='".$link_language."' WHERE link_id='".$data['link_id']."'";

                            }
                        }
                    }
                }

                if (!empty($lang_cmd['update'])) {
                    foreach ($lang_cmd['update'] as $update_sql) {
                        dbquery("UPDATE ".$update_sql." ");
                    }
                }

            }
        }

        /**
         * Part III - Set Checkboxes for on and off of mlt handler
         */
        $ml_tables = "";
        if (isset($_POST['multilang_tables'])) {
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='0'");
            for ($i = 0; $i < count($_POST['multilang_tables']); $i++) {
                $ml_tables .= stripinput($_POST['multilang_tables'][$i]);
                if ($i != (count($_POST['multilang_tables']) - 1)) {
                    $ml_tables .= ".";
                }
            }
            $ml_tables = explode('.', $ml_tables);
            for ($i = 0; $i < count($ml_tables); $i++) {
                $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='1' WHERE mlt_rights='".$ml_tables[$i]."'");
            }
        }

        // reset back to current language
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
        $locale += fusion_get_locale('', LOCALE.LOCALESET.'setup.php');

        addNotice('success', $locale['900']);
        redirect(FUSION_SELF.$aidlink);

    } else {
        addNotice('success', $locale['901']);
    }
}

opentable($locale['682ML']);
echo "<div class='well'>".$locale['language_description']."</div>";
echo openform('settingsform', 'post', FUSION_SELF.$aidlink);
echo form_hidden('old_localeset', '', fusion_get_settings("locale"));
echo form_hidden('old_enabled_languages', '', fusion_get_settings("enabled_languages"));
echo form_select('localeset', $locale['417'], fusion_get_settings("locale"), [
    'options' => fusion_get_enabled_languages(),
    "inline"  => TRUE
]);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['684ML']."</strong>\n";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo form_lang_checkbox(makefilelist(LOCALE, ".|..", TRUE, "folders"));
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
echo "<div class='alert alert-info'>".$locale['685ML']."</div>";
echo "</div>\n";
echo "</div>\n";
echo "<div class='row m-t-20'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['668ML']."</strong><br />".$locale['669ML'];
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
$result = dbquery("SELECT * FROM ".DB_LANGUAGE_TABLES."");
while ($data = dbarray($result)) {
    echo "<input type='checkbox' value='".$data['mlt_rights']."' id='".$data['mlt_rights']."' name='multilang_tables[]'  ".($data['mlt_status'] == '1' ? "checked='checked'" : "")." /> <label for='".$data['mlt_rights']."' class='m-b-0'>".$data['mlt_title']."</label><br />";
}
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
echo "</div>\n";
echo "</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-primary']);
echo closeform();
closetable();
require_once THEMES.'templates/footer.php';


/**
 * Create Language Selector Checkboxes.
 *
 * @param string[] $language_list
 *
 * @return string
 */
function form_lang_checkbox(array $language_list) {
    $enabled_languages = fusion_get_enabled_languages();
    $res = "";
    foreach ($language_list as $language) {
        $deactivate = fusion_get_settings("locale") == $language ? TRUE : FALSE;

        $res .= form_checkbox("enabled_languages[]", translate_lang_names($language),
            (isset($enabled_languages[$language]) ? TRUE : FALSE),
            [
                "input_id"      => "langcheck-".$language,
                "value"         => $language,
                "class"         => "m-b-0",
                "reverse_label" => TRUE,
                "deactivate"    => $deactivate
            ]);
        if ($deactivate == TRUE) {
            $res .= form_hidden('enabled_languages[]', '', $language);
        }
    }

    return $res;
}
