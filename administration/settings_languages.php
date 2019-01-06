<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_languages.php

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
        echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
    }
}
$locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (isset($_POST['savesettings'])) {
    $error = 0;
    $localeset = stripinput($_POST['localeset']);
    $old_localeset = stripinput($_POST['old_localeset']);
    $old_enabled_languages = stripinput($_POST['old_enabled_languages']);
    $ml_tables = "";
    $enabled_languages = "";
    if (!defined('FUSION_NULL')) {
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='$localeset' WHERE settings_name='locale'");
        if (!$result) {
            $error = 1;
        }
        $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='0'");
        for ($i = 0; $i < count($_POST['multilang_tables']); $i++) {
            $ml_tables .= stripinput($_POST['multilang_tables'][$i]);
            if ($i != (count($_POST['multilang_tables']) - 1))
                $ml_tables .= ".";
        }
        $ml_tables = explode('.', $ml_tables);
        for ($i = 0; $i < sizeof($ml_tables); $i++) {
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_status='1' WHERE mlt_rights='".$ml_tables[$i]."'");
        }
        if (!$result) {
            $error = 1;
        }
        for ($i = 0; $i < count($_POST['enabled_languages']); $i++) {
            $enabled_languages .= stripinput($_POST['enabled_languages'][$i]);
            if ($i != (count($_POST['enabled_languages']) - 1))
                $enabled_languages .= ".";
        }
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$enabled_languages."' WHERE settings_name='enabled_languages'");
        if (!$result) {
            $error = 1;
        }
        unset($settings);
        $settings = [];
        $result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS);
        while ($data = dbarray($result)) {
            $settings[$data['settings_name']] = $data['settings_value'];
        }
        if (($settings['enabled_languages'] != $_POST['old_enabled_languages']) && !$error) {
            //Give the Administration new locale based on site�s main locale settings
            $enabled_languages = explode('.', $settings['enabled_languages']);
            $old_enabled_languages = explode('.', $old_enabled_languages);
            //Remove language from guest user settings
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $result = dbquery("DELETE FROM ".DB_LANGUAGE_SESSIONS." WHERE user_language !='".$enabled_languages[$i]."'");
            }
            //Sanitize users languages
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_language = '".$settings['locale']."' WHERE user_language !='".$enabled_languages[$i]."'");
            }
            //Sanitize and update panel languages
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $panel_langs .= $settings['enabled_languages'].($i < (sizeof($settings['enabled_languages']) - 1) ? "." : "");
            }
            if (sizeof($enabled_languages) > 1) {
                $result = dbquery("UPDATE ".DB_PANELS." SET panel_languages='".$panel_langs."'");
            } else {
                $result = dbquery("UPDATE ".DB_PANELS." SET panel_languages='".$settings['locale']."'");
            }
            //Sanitize news_cat_languages
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $result = dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_language !='".$enabled_languages[$i]."' AND news_cat_language !='".$settings['locale']."'");
            }
            //Sanitize site links_languages
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_language !='".$enabled_languages[$i]."' AND link_language !='".$settings['locale']."'");
            }
            //Sanitize the email templates languages
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $result = dbquery("DELETE FROM ".DB_EMAIL_TEMPLATES." WHERE template_language !='".$enabled_languages[$i]."' AND template_language !='".$settings['locale']."'");
            }
            //Sanitize forum rank languages
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                $result = dbquery("DELETE FROM ".DB_FORUM_RANKS." WHERE rank_language !='".$enabled_languages[$i]."' AND rank_language !='".$settings['locale']."'");
            }
            //update news cats with a new language if we have it
            if (!empty($settings['enabled_languages'])) {
                for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                    $language_exist = dbarray(dbquery("SELECT news_cat_language FROM ".DB_NEWS_CATS." WHERE news_cat_language ='".$enabled_languages[$i]."'"));
                    if (is_null($language_exist['news_cat_language'])) {
                        include LOCALE."".$enabled_languages[$i]."/setup.php";
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['180']."', 'bugs.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['181']."', 'downloads.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['182']."', 'games.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['183']."', 'graphics.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['184']."', 'hardware.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['185']."', 'journal.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['186']."', 'members.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['187']."', 'mods.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['188']."', 'movies.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['189']."', 'network.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['190']."', 'news.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['191']."', 'php-fusion.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['192']."', 'security.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['193']."', 'software.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['194']."', 'themes.gif', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('".$locale['195']."', 'windows.gif', '".$enabled_languages[$i]."')");
                    }
                }
            }
            //update site links with a new language if we have it
            if (!empty($settings['enabled_languages'])) {
                for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                    $language_exist = dbarray(dbquery("SELECT link_language FROM ".DB_SITE_LINKS." WHERE link_language ='".$enabled_languages[$i]."'"));
                    if (is_null($language_exist['link_language'])) {
                        include LOCALE."".$enabled_languages[$i]."/setup.php";
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['130']."', 'index.php', '0', '2', '0', '1', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['131']."', 'articles.php', '0', '2', '0', '2', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['132']."', 'downloads.php', '0', '2', '0', '3', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['133']."', 'faq.php', '0', '1', '0', '4', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['134']."', 'forum/index.php', '0', '2', '0', '5', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['136']."', 'news_cats.php', '0', '2', '0', '7', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['137']."', 'weblinks.php', '0', '2', '0', '6', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['135']."', 'contact.php', '0', '1', '0', '8', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['138']."', 'photogallery.php', '0', '1', '0', '9', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['139']."', 'search.php', '0', '1', '0', '10', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('---', '---', '101', '1', '0', '11', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['140']."', 'submit.php?stype=l', '101', '1', '0', '12', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['141']."', 'submit.php?stype=n', '101', '1', '0', '13', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['142']."', 'submit.php?stype=a', '101', '1', '0', '14', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['143']."', 'submit.php?stype=p', '101', '1', '0', '15', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_language) VALUES ('".$locale['144']."', 'submit.php?stype=d', '101', '1', '0', '16', '".$enabled_languages[$i]."')");
                    }
                }
            }
            //Update the email template system locales
            if (!empty($settings['enabled_languages'])) {
                for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                    $language_exist = dbarray(dbquery("SELECT template_language FROM ".DB_EMAIL_TEMPLATES." WHERE template_language ='".$enabled_languages[$i]."'"));
                    if (is_null($language_exist['template_language'])) {
                        include LOCALE."".$enabled_languages[$i]."/setup.php";
                        $result = dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'PM', 'html', '0', '".$locale['T101']."', '".$locale['T102']."', '".$locale['T103']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'POST', 'html', '0', '".$locale['T201']."', '".$locale['T202']."', '".$locale['T203']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_EMAIL_TEMPLATES." (template_id, template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('', 'CONTACT', 'html', '0', '".$locale['T301']."', '".$locale['T302']."', '".$locale['T303']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$enabled_languages[$i]."')");
                    }
                }
            }
            //Update the forum ranks locales
            if (!empty($settings['enabled_languages'])) {
                for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                    $language_exist = dbarray(dbquery("SELECT rank_language FROM ".DB_FORUM_RANKS." WHERE rank_language ='".$enabled_languages[$i]."'"));
                    if (is_null($language_exist['rank_language'])) {
                        include LOCALE."".$enabled_languages[$i]."/setup.php";
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['200']."', 'rank_super_admin.png', 0, '1', 103, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['201']."', 'rank_admin.png', 0, '1', 102, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['202']."', 'rank_mod.png', 0, '1', 104, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['203']."', 'rank0.png', 0, '0', 101, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['204']."', 'rank1.png', 10, '0', 101, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['205']."', 'rank2.png', 50, '0', 101, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['206']."', 'rank3.png', 200, '0', 101, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['207']."', 'rank4.png', 500, '0', 101, '".$enabled_languages[$i]."')");
                        $result = dbquery("INSERT INTO ".DB_FORUM_RANKS." VALUES ('', '".$locale['208']."', 'rank5.png', 1000, '0', 101, '".$enabled_languages[$i]."')");
                    }
                }
            }
        }

        if (!empty($settings['enabled_languages'])) {
            for ($i = 0; $i < sizeof($enabled_languages); $i++) {
                //If the system base language changes, replace Admin's locale
                include LOCALE.$enabled_languages[$i]."/admin/main.php";
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('AD', 'admins.png', '".$locale['080']."', 'administrators.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('APWR', 'admin_pass.png', '".$locale['128']."', 'admin_reset.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('AC', 'article_cats.png', '".$locale['081']."', 'article_cats.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('A', 'articles.png', '".$locale['082']."', 'articles.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('SB', 'banners.png', '".$locale['083']."', 'banners.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('BB', 'bbcodes.png', '".$locale['084']."', 'bbcodes.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('B', 'blacklist.png', '".$locale['085']."', 'blacklist.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('C', '', '".$locale['086']."', 'reserved', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('CP', 'c-pages.png', '".$locale['087']."', 'custom_pages.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('DB', 'db_backup.png', '".$locale['088']."', 'db_backup.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('DC', 'dl_cats.png', '".$locale['089']."', 'download_cats.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('D', 'dl.png', '".$locale['090']."', 'downloads.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('ERRO', 'errors.png', '".$locale['129']."', 'errors.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('FQ', 'faq.png', '".$locale['091']."', 'faq.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('F', 'forums.png', '".$locale['092']."', 'forums.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('IM', 'images.png', '".$locale['093']."', 'images.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('FM', 'file_manager.png', '".$locale['130d']."', 'file_manager.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('I', 'infusions.png', '".$locale['094']."', 'infusions.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('IP', '', '".$locale['095']."', 'reserved', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('M', 'members.png', '".$locale['096']."', 'members.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('NC', 'news_cats.png', '".$locale['097']."', 'news_cats.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('N', 'news.png', '".$locale['098']."', 'news.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('BLC', 'blog_cats.png', '".$locale['130a']."', 'blog_cats.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('BLOG', 'blog.png', '".$locale['130b']."', 'blog.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S13', 'settings_blog.png', '".$locale['130c']."', 'settings_blog.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('P', 'panels.png', '".$locale['099']."', 'panels.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('PH', 'photoalbums.png', '".$locale['100']."', 'photoalbums.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('PI', 'phpinfo.png', '".$locale['101']."', 'phpinfo.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('PO', 'polls.png', '".$locale['102']."', 'polls.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('SL', 'site_links.png', '".$locale['104']."', 'site_links.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('SM', 'smileys.png', '".$locale['105']."', 'smileys.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('SU', 'submissions.png', '".$locale['106']."', 'submissions.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('PL', 'permalinks.png', '".$locale['129d']."', 'permalinks.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('U', 'upgrade.png', '".$locale['107']."', 'upgrade.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('UG', 'user_groups.png', '".$locale['108']."', 'user_groups.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('WC', 'wl_cats.png', '".$locale['109']."', 'weblink_cats.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('W', 'wl.png', '".$locale['110']."', 'weblinks.php', '1', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S1', 'settings.png', '".$locale['111']."', 'settings_main.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S2', 'settings_time.png', '".$locale['112']."', 'settings_time.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S3', 'settings_forum.png', '".$locale['113']."', 'settings_forum.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S4', 'registration.png', '".$locale['114']."', 'settings_registration.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S5', 'photoalbums.png', '".$locale['115']."', 'settings_photo.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S6', 'settings_misc.png', '".$locale['116']."', 'settings_misc.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S7', 'settings_pm.png', '".$locale['117']."', 'settings_messages.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S8', 'settings_news.png', '".$locale['121']."', 'settings_news.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S9', 'settings_users.png', '".$locale['122']."', 'settings_users.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S10', 'settings_ipp.png', '".$locale['124']."', 'settings_ipp.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S11', 'settings_dl.png', '".$locale['123']."', 'settings_dl.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S12', 'security.png', '".$locale['125']."', 'settings_security.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('UF', 'user_fields.png', '".$locale['118']."', 'user_fields.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('FR', 'forum_ranks.png', '".$locale['119']."', 'forum_ranks.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('UFC', 'user_fields_cats.png', '".$locale['120']."', 'user_field_cats.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('UL', 'user_log.png', '".$locale['129a']."', 'user_log.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('ROB', 'robots.png', '".$locale['129b']."', 'robots.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('MAIL', 'email.png', '".$locale['T001']."', 'email.php', '3', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('LANG', 'languages.png', '".$locale['129c']."', 'settings_languages.php', '4', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('MI', 'migration.png', '".$locale['129e']."', 'migrate.php', '2', '".$enabled_languages[$i]."')");
                $result = dbquery("INSERT INTO ".$db_prefix."admin (admin_rights, admin_image, admin_title, admin_link, admin_page, admin_language) VALUES ('S14', 'settings_theme.png', '".$locale['129f']."', 'settings_theme.php', '4', '".$enabled_languages[$i]."')");
            }
        }

        if (($localeset != $old_localeset) && !$error) {
            include LOCALE.$localeset."/setup.php";
            //update default News cats with the set language
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['180']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='bugs.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['181']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='downloads.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['182']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='games.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['183']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='graphics.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['184']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='hardware.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['185']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='journal.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['186']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='members.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['187']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='mods.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['188']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='movies.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['189']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='network.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['190']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='news.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['191']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='php-fusion.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['192']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='security.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['193']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='software.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['194']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='themes.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$locale['195']."' WHERE news_cat_language='".$old_localeset."' AND news_cat_image='windows.gif'");
            $result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_language='".$localeset."' WHERE news_cat_language='".$old_localeset."'");
            //update default site links with the set language
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['130']."' WHERE link_language='".$old_localeset."' AND link_url='index.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['131']."' WHERE link_language='".$old_localeset."' AND link_url='articles.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['132']."' WHERE link_language='".$old_localeset."' AND link_url='downloads.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['133']."' WHERE link_language='".$old_localeset."' AND link_url='faq.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['134']."' WHERE link_language='".$old_localeset."' AND link_url='forum/index.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['135']."' WHERE link_language='".$old_localeset."' AND link_url='news_cats.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['136']."' WHERE link_language='".$old_localeset."' AND link_url='weblinks.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['137']."' WHERE link_language='".$old_localeset."' AND link_url='contact.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['138']."' WHERE link_language='".$old_localeset."' AND link_url='photogallery.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['139']."' WHERE link_language='".$old_localeset."' AND link_url='search.php'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['140']."' WHERE link_language='".$old_localeset."' AND link_url='submit.php?stype=l'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['141']."' WHERE link_language='".$old_localeset."' AND link_url='submit.php?stype=n'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['142']."' WHERE link_language='".$old_localeset."' AND link_url='submit.php?stype=a'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['143']."' WHERE link_language='".$old_localeset."' AND link_url='submit.php?stype=p'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_name='".$locale['144']."' WHERE link_language='".$old_localeset."' AND link_url='submit.php?stype=d'");
            $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_language='".$localeset."' WHERE link_language='".$old_localeset."'");
            //update multilanguage tables with a new language if we have it
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT001']."' WHERE mlt_rights='AR'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT002']."' WHERE mlt_rights='CP'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT003']."' WHERE mlt_rights='DL'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT004']."' WHERE mlt_rights='FQ'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT005']."' WHERE mlt_rights='FO'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT006']."' WHERE mlt_rights='NS'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT007']."' WHERE mlt_rights='PG'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT008']."' WHERE mlt_rights='PO'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT009']."' WHERE mlt_rights='SB'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT010']."' WHERE mlt_rights='WL'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT011']."' WHERE mlt_rights='SL'");
            if (!$result) {
                $error = 1;
            }
            $result = dbquery("UPDATE ".DB_LANGUAGE_TABLES." SET mlt_title='".$locale['MLT012']."' WHERE mlt_rights='PN'");
            if (!$result) {
                $error = 1;
            }
        }
        redirect(FUSION_SELF.$aidlink."&error=".$error);
    }
}
$settings2 = [];
$result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS);
while ($data = dbarray($result)) {
    $settings2[$data['settings_name']] = $data['settings_value'];
}
opentable($locale['682ML']);
echo "<form name='settingsform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo "<table class='table table-responsive center'>\n<tbody>\n<tr>\n";
echo "<td width='50%' class='tbl'><label for='localeset'>".$locale['417']."<label> <span class='required'>*</span></td>\n";
echo "<td width='50%' class='tbl'><select name='localeset' class='textbox'>\n";
echo makefileopts($locale_files, $settings2['locale'])."\n";
echo "</select></td>\n";
$locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
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
echo "<input type='hidden' name='old_localeset' value='".$settings2['locale']."' />\n";
echo "<input type='hidden' name='old_enabled_languages' value='".$settings['enabled_languages']."' />\n";
echo "<input type='submit' name='savesettings' value='".$locale['750']."' class='button' /></td>\n";
echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
closetable();
require_once THEMES."templates/footer.php";
