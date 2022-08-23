<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_languages.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('LANG');

$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'setup.php']);
$aidlink = fusion_get_aidlink();

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
        include INFUSIONS.$cdata['inf_folder']."/infusion.php"; // there is a system language inside. // can't read into system language.
        if (isset($inf_mlt) && is_array($inf_mlt)) {
            $inf_mlt = flatten_array($inf_mlt);
            if (!empty($inf_mlt['title']) && !empty($inf_mlt['rights'])) {
                dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$inf_mlt['title']."' WHERE mlt_rights='".$inf_mlt['rights']."'");
            } else {
                //fusion_stop();
                addnotice("danger",
                    "Error due to incomplete locale translations in infusions folder ".$cdata['inf_folder'].". This infusion does not have the localized title and change is aborted. Please translate setup.php.");
            }
        }
        unset($inf_mlt);
    }
}

add_breadcrumb(['link' => ADMIN."settings_languages.php".fusion_get_aidlink(), 'title' => $locale['admins_682ML']]);

if (check_post('savesettings')) {
    $inputData = [
        "localeset"             => sanitizer('localeset', fusion_get_settings('locale'), "localeset"),
        "old_localeset"         => sanitizer('old_localeset', fusion_get_settings('locale'), "old_localeset"),
        "enabled_languages"     => sanitizer(['enabled_languages'], fusion_get_settings('locale'), "enabled_languages"),
        // returns Chinese_Simplified,English,Malay
        "old_enabled_languages" => sanitizer('old_enabled_languages', "", "old_enabled_languages"),
        // returns Chinese_Simplified.English.Malay
    ];

    // format both to .
    if (empty($inputData['enabled_languages'])) {
        fusion_stop("You need to enable at least one language");
    }

    if (fusion_safe()) {

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
                    if (empty($language_exist['template_language'])) {
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
                        ['link_name' => $locale['setup_3309'], 'link_cat' => '0', 'link_url' => 'search.php', 'link_visibility' => '0', 'link_position' => '1', 'link_order' => '10', 'link_language' => $language]
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
                        ['admin_rights' => 'PI', 'admin_image' => 'serverinfo.png', 'admin_title' => $locale['setup_3021'], 'admin_link' => 'serverinfo.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'SL', 'admin_image' => 'sitelinks.png', 'admin_title' => $locale['setup_3023'], 'admin_link' => 'site_links.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'SM', 'admin_image' => 'smileys.png', 'admin_title' => $locale['setup_3024'], 'admin_link' => 'smileys.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'U', 'admin_image' => 'upgrade.png', 'admin_title' => $locale['setup_3026'], 'admin_link' => 'upgrade.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'TS', 'admin_image' => 'theme.png', 'admin_title' => $locale['setup_3056'], 'admin_link' => 'theme.php', 'admin_page' => 3, 'admin_language' => $language],
                        ['admin_rights' => 'UG', 'admin_image' => 'user_groups.png', 'admin_title' => $locale['setup_3027'], 'admin_link' => 'user_groups.php', 'admin_page' => 2, 'admin_language' => $language],
                        ['admin_rights' => 'S1', 'admin_image' => 'settings.png', 'admin_title' => $locale['setup_3030'], 'admin_link' => 'settings_main.php', 'admin_page' => 4, 'admin_language' => $language],
                        ['admin_rights' => 'S2', 'admin_image' => 'time.png', 'admin_title' => $locale['setup_3031'], 'admin_link' => 'settings_time.php', 'admin_page' => 4, 'admin_language' => $language],
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

                    cdreset('adminpages');
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
        if (check_post(['multilang_tables'])) {
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='0'");
            for ($i = 0; $i < count(post(['multilang_tables'])); $i++) {
                $ml_tables .= stripinput(post(['multilang_tables'])[$i]);
                if ($i != (count(post(['multilang_tables'])) - 1)) {
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

        addnotice('success', $locale['admins_900']);
        redirect(FUSION_SELF.$aidlink);

    } else {
        addnotice('success', $locale['admins_901']);
    }
}

opentable($locale['admins_682ML']);
echo "<div class='well'>".$locale['admins_language_description']."</div>";
echo openform('settingsform', 'post');
echo form_hidden('old_localeset', '', fusion_get_settings("locale"));
echo form_hidden('old_enabled_languages', '', fusion_get_settings("enabled_languages"));
echo form_select('localeset', $locale['admins_417'], fusion_get_settings("locale"), [
    'options' => fusion_get_enabled_languages(),
    "inline"  => TRUE
]);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo "<strong>".$locale['admins_684ML']."</strong>\n";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-3'>\n";
echo form_lang_checkbox();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
echo "<div class='alert alert-info'>".$locale['admins_685ML']."</div>";
echo "</div>\n";
echo "</div>\n";
echo "<div class='row m-t-20 m-b-20'>\n";
echo "<div class='col-xs-12 col-sm-3 m-b-10'>\n";
echo "<strong>".$locale['admins_668ML']."</strong><br />".$locale['admins_669ML'];
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-6'>\n";
$result = dbquery("SELECT * FROM ".DB_LANGUAGE_TABLES."");
while ($data = dbarray($result)) {
    echo '<div class="checkbox-switch form-group m-b-0">
        <label class="control-label" data-checked="1" for="'.$data['mlt_rights'].'">'.$data['mlt_title'].'</label>
        <div class="pull-left m-r-10">
            <input id="'.$data['mlt_rights'].'" style="margin:0;" name="multilang_tables[]" value="'.$data['mlt_rights'].'" type="checkbox"'.($data['mlt_status'] == 1 ? ' checked' : '').'>
        </div>
    </div>';
}
echo "</div>\n";
echo "</div>\n";
echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
echo closeform();


if (check_post('download_lang')) {
    $update = new PHPFusion\Update();
    $update->downloadLanguage(post('lang_pack'));
    addnotice('success', sprintf($locale['admins_670a'], post('lang_pack')));
    redirect(FUSION_REQUEST);
}

echo openform('dllangs', 'post', FORM_REQUEST, ['class' => 'm-t-15']);
openside();
echo form_language_select('lang_pack', $locale['admins_670b'], '', [
    'inline' => TRUE
]);
echo form_button('download_lang', $locale['admins_670c'], 'download_lang');
closeside();
echo closeform();

closetable();
require_once THEMES.'templates/footer.php';

/**
 * Create language selector checkboxes.
 *
 * @return string
 */
function form_lang_checkbox() {
    $language_list = makefilelist(LOCALE, ".|..", TRUE, "folders");
    $enabled_languages = fusion_get_enabled_languages();
    $res = "";
    foreach ($language_list as $language) {
        $deactivate = fusion_get_settings("locale") == $language;

        $res .= form_checkbox("enabled_languages[]", translate_lang_names($language), (isset($enabled_languages[$language])), [
            "input_id"      => "langcheck-".$language,
            "value"         => $language,
            "class"         => "m-b-0",
            "reverse_label" => TRUE,
            "deactivate"    => $deactivate,
            'toggle'        => TRUE
        ]);
        if ($deactivate == TRUE) {
            $res .= form_hidden('enabled_languages[]', '', $language);
        }
    }

    return $res;
}

function form_language_select($input_name, $label = "", $input_value = FALSE, array $options = []) {
    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_value = clean_input_value($input_value);

    $default_options = [
        'required'          => FALSE,
        'regex'             => '',
        'input_id'          => $input_name,
        'placeholder'       => $locale['choose'],
        'deactivate'        => FALSE,
        'safemode'          => FALSE,
        'inner_width'       => '250px',
        'width'             => '',
        'keyflip'           => FALSE,
        'tags'              => FALSE,
        'jsonmode'          => FALSE,
        'chainable'         => FALSE,
        'max_select'        => 1,
        'error_text'        => '',
        'class'             => '',
        'stacked'           => '',
        'inline'            => FALSE,
        'tip'               => '',
        'ext_tip'           => '',
        'delimiter'         => ',',
        'callback_check'    => '',
        'file'              => '',
        'callback_function' => ''
    ];

    $options += $default_options;

    $options['input_id'] = trim($options['input_id'], "[]");

    $error_class = "";

    if (Defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        $new_error_text = Defender::getErrorText($input_name);
        if (!empty($new_error_text)) {
            $options['error_text'] = $new_error_text;
        }
        addnotice("danger", $options['error_text']);
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').$error_class.$options['class']."' style='width:".$options['width']."'>\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? 'col-xs-12 col-sm-12 col-md-3 col-lg-3' : '')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')."</label>\n" : '';
    $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
    $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width:".$options['inner_width']."'".($options['deactivate'] ? ' disabled' : '')."/>\n";
    if ($options['deactivate']) {
        $html .= form_hidden($input_name, '', $input_value, ["input_id" => $options['input_id']]);
    }

    $html .= $options['stacked'];
    $html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>".$options['ext_tip']."</i></div>" : "";
    $html .= \Defender::inputHasError($input_name) && !$options['inline'] ? "<br/>" : "";
    $html .= \Defender::inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

    $html .= $options['inline'] && $label ? "</div>\n" : '';

    $html .= "</div>\n";

    $root_prefix = fusion_get_settings("site_seo") == 1 ? fusion_get_settings('siteurl')."administration/" : ADMIN;
    $path = !empty($options['file']) ? $options['file'] : $root_prefix."includes/?api=available-languages";

    Defender::getInstance()->add_field_session([
        'input_name' => clean_input_name($input_name),
        'title'      => $title,
        'id'         => $options['input_id'],
        'type'       => 'dropdown',
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text']
    ]);

    add_to_jquery("$('#".$options['input_id']."').select2({
        placeholder: '".$options['placeholder']."',
        ajax: {
            url: '$path',
            dataType: 'json',
            data: function (term, page) {
                return {q: term};
            },
            results: function (data, page) {
                return {results: data};
            }
        },
        escapeMarkup: function(m) { return m; },
    });");

    load_select2_script();

    return $html;
}
