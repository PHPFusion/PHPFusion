<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_languages.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
if (!checkrights("LANG") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {redirect("../index.php");}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/settings.php";
if (isset($_GET['error']) && isnum($_GET['error']) && !isset($message)) {
    if ($_GET['error'] == 0) {
        $message = $locale['900'];
    } else if ($_GET['error'] == 1) {
        $message = $locale['901'];
    }
    // Can replace all error=0
    if (isset($message)) {
        echo "<div id='close-message'><div class='admin-message alert alert-info'>".$message."</div></div>\n";
    }
}

$locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (isset($_POST['savesettings'])) {
    $inputData = [
        "localeset"             => stripinput($_POST['localeset']),
        "old_localeset"         => stripinput($_POST['old_localeset']),
        "enabled_languages"     => isset($_POST['enabled_languages']) ? stripinput(implode('.', $_POST['enabled_languages'])) : fusion_get_settings('locale'),
        "old_enabled_languages" => stripinput($_POST['old_enabled_languages'])
    ];

    if (!defined('FUSION_NULL')) {
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
            "enabled_languages"     => explode(".", $inputData['enabled_languages'])
        ];

        // update current system locale
        dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$inputData['localeset']."' WHERE settings_name='locale'"); // update on the new system locale.

        // Switch visible language to localeset
        if ($inputData['old_localeset'] !== $inputData['localeset']) {
            set_language($inputData['localeset']);
        }

        if ($inputData['old_enabled_languages'] != $inputData['enabled_languages']) { // language family have changed
            $added_language = array_diff($array['enabled_languages'], $array['old_enabled_languages']);
            $removed_language = array_diff($array['old_enabled_languages'], $array['enabled_languages']);

            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$core_SQLVal['enabled_languages']."' WHERE settings_name='enabled_languages'");
            dbquery("UPDATE ".DB_PANELS." SET panel_languages='".$core_SQLVal['enabled_languages']."'");
            dbquery("UPDATE ".DB_USERS." SET user_language='Default' WHERE user_language NOT IN ('".$inArray_SQLCond['enabled_languages']."')");

            if (!empty($added_language) && is_array($added_language)) {
                foreach ($added_language as $language) {
                    include LOCALE.$language."/setup.php";
                    $settings = fusion_get_settings();

                    // Email templates
                    $language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$language."'"));
                    if (is_null($language_exist['template_language'])) {
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('PM', 'html', '0', '".$locale['T101']."', '".$locale['T102']."', '".$locale['T103']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('POST', 'html', '0', '".$locale['T201']."', '".$locale['T202']."', '".$locale['T203']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('CONTACT', 'html', '0', '".$locale['T301']."', '".$locale['T302']."', '".$locale['T303']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                        dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('ACTIVATION', 'html', '0', '".$locale['T304']."', '".$locale['T305']."', '".$locale['T306']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$language."')");
                    }

                    // Home Site Links
                    $language_exist = dbarray(dbquery("SELECT link_language FROM ".DB_SITE_LINKS." WHERE link_language ='".$language."'"));
                    if (is_null($language_exist['link_language'])) {
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['130']."', 'index.php', '0', '2', '0', '1', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['131']."', 'articles.php', '0', '2', '0', '2', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['132']."', 'downloads.php', '0', '2', '0', '3', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['133']."', 'faq.php', '0', '1', '0', '4', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['134']."', 'forum/index.php', '0', '2', '0', '5', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['136']."', 'news_cats.php', '0', '2', '0', '7', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['137']."', 'weblinks.php', '0', '2', '0', '6', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['135']."', 'contact.php', '0', '1', '0', '8', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['138']."', 'photogallery.php', '0', '1', '0', '9', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['139']."', 'search.php', '0', '1', '0', '10', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('---', '---', '101', '1', '0', '11', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['140']."', 'submit.php?stype=l', '101', '1', '0', '12', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['141']."', 'submit.php?stype=n', '101', '1', '0', '13', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['142']."', 'submit.php?stype=a', '101', '1', '0', '14', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['143']."', 'submit.php?stype=p', '101', '1', '0', '15', '".$language."')");
                        dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['144']."', 'submit.php?stype=d', '101', '1', '0', '16', '".$language."')");
                    }

                    // News Cats
                    $language_exist = dbarray(dbquery("SELECT news_cat_language FROM ".DB_NEWS_CATS." WHERE news_cat_language ='".$language."'"));
                    if (is_null($language_exist['news_cat_language'])) {
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['190']."', 'news.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$language."')");
                    }

                    // Blog Cats
                    $language_exist = dbarray(dbquery("SELECT blog_cat_language FROM ".DB_BLOG_CATS." WHERE blog_cat_language ='".$language."'"));
                    if (is_null($language_exist['blog_cat_language'])) {
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['190']."', 'news.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$language."')");
                        dbquery("INSERT INTO ".DB_BLOG_CATS." (blog_cat_name, blog_cat_image, blog_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$language."')");
                    }

                    // Forum ranks
                    $language_exist = dbarray(dbquery("SELECT rank_language FROM ".DB_FORUM_RANKS." WHERE rank_language ='".$language."'"));
                    if (is_null($language_exist['rank_language'])) {
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['200']."', 'rank_super_admin.png', 0, '1', 103, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['201']."', 'rank_admin.png', 0, '1', 102, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['202']."', 'rank_mod.png', 0, '1', 104, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['203']."', 'rank0.png', 0, '0', 101, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['204']."', 'rank1.png', 10, '0', 101, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['205']."', 'rank2.png', 50, '0', 101, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['206']."', 'rank3.png', 200, '0', 101, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['207']."', 'rank4.png', 500, '0', 101, '".$language."')");
                        dbquery("INSERT INTO ".DB_FORUM_RANKS." (rank_title, rank_image, rank_posts, rank_type, rank_apply, rank_language) VALUES ('".$locale['208']."', 'rank5.png', 1000, '0', 101, '".$language."')");
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

                    $home_language_exist = dbarray(dbquery("SELECT link_language FROM ".DB_SITE_LINKS." WHERE link_language ='".$language."'"));
                    if (!empty($home_language_exist['link_language'])) {
                        dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_language = '".$language."'");
                    }

                    $news_language_exist = dbarray(dbquery("SELECT news_cat_language FROM ".DB_NEWS_CATS." WHERE news_cat_language ='".$language."'"));
                    if (!empty($news_language_exist['news_cat_language'])) {
                        dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_language = '".$language."'");
                    }

                    $blog_language_exist = dbarray(dbquery("SELECT blog_cat_language FROM ".DB_BLOG_CATS." WHERE blog_cat_language ='".$language."'"));
                    if (!empty($blog_language_exist['blog_cat_language'])) {
                        dbquery("DELETE FROM ".DB_BLOG_CATS." WHERE blog_cat_language = '".$language."'");
                    }

                    $ranks_language_exist = dbarray(dbquery("SELECT rank_language FROM ".DB_FORUM_RANKS." WHERE rank_language ='".$language."'"));
                    if (!empty($ranks_language_exist['rank_language'])) {
                        dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_language = '".$language."'");
                    }
                }
            }

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
                    dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='1' WHERE mlt_rights='".$ml_tables[$i]."'");
                }
            }
        }

        redirect(FUSION_SELF.$aidlink.'&amp;error=0');
    }
}

$settings2 = [];
$result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
    $settings2[$data['settings_name']] = $data['settings_value'];
}
opentable($locale['682ML']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<input type='hidden' name='old_localeset' value='".$settings['locale']."' />\n";
echo "<input type='hidden' name='old_enabled_languages' value='".$settings['enabled_languages']."' />\n";
echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='localeset'>".$locale['417']."<label> <span class='required'>*</span></td>\n";
echo "<td width='50%' class='tbl'><select name='localeset' class='textbox'>\n";
echo makefileopts($locale_files, $settings['locale'])."\n";
echo "</select></td>\n";
echo "</tr>\n<tr>\n";
echo "<td valign='top' width='50%' class='tbl'><strong>".$locale['684ML']."</strong><br /><span class='small2'>".$locale['685ML']."</span></td>\n";
echo "<td width='50%' class='tbl'>\n";
echo get_available_languages_array($locale_files);
echo "</td>\n</tr>\n";
echo "<td valign='top' width='50%' class='tbl'><strong>".$locale['668ML']."</strong><br /><span class='small2'>".$locale['669ML']."</span></td>\n";
echo "<td width='50%' class='tbl'>\n";
$result = dbquery("SELECT * FROM ".DB_LANGUAGE_TABLES."");
while ($data = dbarray($result)) {
    echo "<input type='checkbox' value='".$data['mlt_rights']."' name='multilang_tables[]'  ".($data['mlt_status'] == '1' ? "checked='checked'" : "")." /> ".$data['mlt_title']." <br />";
}
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
closetable();
require_once THEMES."templates/footer.php";
