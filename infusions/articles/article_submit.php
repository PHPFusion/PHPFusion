<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: article_submit.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Settings
$articleSettings = get_settings("article");

// Locale
$locale = fusion_get_locale('', ARTICLE_ADMIN_LOCALE);

opentable("<i class='fa fa-file-text-o fa-lg m-r-10'></i> ".$locale['article_0900']);
add_to_title($locale['global_200'].$locale['article_0900']);


if (dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_status='1' AND ".groupaccess("article_cat_visibility")."")) {
    if (iMEMBER && $articleSettings['article_allow_submission']) {
        $criteriaArray = array(
            "article_subject" => "",
            "article_keywords" => "",
            "article_cat" => 0,
            "article_language" => LANGUAGE,
            "article_snippet" => "",
            "article_article" => ""
        );

        if (isset($_POST['cancel_article'])) {
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['submit_article']) || isset($_POST['preview_article'])) {
            // Check posted Informations
            $article_snippet = "";
            if ($_POST['article_snippet']) {
                $article_snippet = parse_textarea($_POST['article_snippet']);
            }

            $article_article = "";
            if ($_POST['article_article']) {
                $article_article = parse_textarea($_POST['article_article']);
            }

            $criteriaArray = array(
                "article_subject" => form_sanitizer($_POST['article_subject'], "", "article_subject"),
                "article_cat" => form_sanitizer($_POST['article_cat'], 0, "article_cat"),
                "article_snippet" => form_sanitizer($article_snippet, "", "article_snippet"),
                "article_article" => form_sanitizer($article_article, "", "article_article"),
                "article_keywords" => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
                "article_language" => form_sanitizer($_POST['article_language'], LANGUAGE, "article_language"),
            );

            // Save
            if (defender::safe() && isset($_POST['submit_article'])) {
                $inputArray = array(
                    "submit_type" => "a", "submit_user" => $userdata['user_id'], "submit_datestamp" => time(),
                    "submit_criteria" => addslashes(serialize($criteriaArray))
                );
                dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
                addNotice("success", $locale['article_0910']);
                redirect(clean_request("submitted=A", array("stype"), TRUE));
            }

            // Display
            if (defender::safe() && isset($_POST['preview_article'])) {
                $footer = openmodal("article_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$locale['preview'].": ".$criteriaArray['article_subject']);
                $footer .= nl2br(parse_textarea($article_snippet));
                if ($criteriaArray['article_article']) {
                    $footer .= "<hr class='m-t-20 m-b-20'>\n";
                    $footer .= nl2br(parse_textarea($article_article));
                }
                $footer .= closemodal();
                add_to_footer($footer);
            }
        }

        // Display Success Message
        if (isset($_GET['submitted']) && $_GET['submitted'] == "A") {
            echo '<div class="well text-center text-strong">';
                echo '<p>'.$locale['article_0911'].'</p>';
                echo '<p><a href="'.BASEDIR.'submit.php?stype=a" title="'.$locale['article_0912'].'">'.$locale['article_0912'].'</a></p>';
                echo '<p><a href="'.BASEDIR.'index.php" title="'.str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['article_0913']).'">';
                    echo str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['article_0913']);
                echo '</a></p>';
            echo '</div>';

        // Display Preview and Form
        } else {
            echo '<div class="alert alert-info m-b-20 submission-guidelines">';
                echo '<p>'.str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['article_0920']).'</p>';
            echo '</div>';

            // Textarea Settings
            if (!fusion_get_settings("tinymce_enabled")) {
                $articleSnippetSettings = array(
                    "required"   => true, "preview" => true, "html" => true, "autosize" => true, "placeholder" => $locale['article_0254'],
                    "error_text" => $locale['article_0271'], "form_name" => "submissionform", "wordcount" => true
                );
                $articleExtendedSettings = array(
                    "required"   => ($articleSettings['article_extended_required'] ? true : false), "preview" => true, "html" => true, "autosize" => true, "placeholder" => $locale['article_0253'],
                    "error_text" => $locale['article_0272'], "form_name" => "submissionform", "wordcount" => true
                );
            } else {
                $articleSnippetSettings  = array("required" => true, "type" => "tinymce", "tinymce" => "advanced", "error_text" => $locale['article_0271']);
                $articleExtendedSettings = array("required" => ($articleSettings['article_extended_required'] ? true : false), "type" => "tinymce", "tinymce" => "advanced", "error_text" => $locale['article_0272']);
            }

            echo openform("submissionform", "post", BASEDIR."submit.php?stype=a");

            echo form_text("article_subject", $locale['article_0100'], $criteriaArray['article_subject'], array(
                "required" => true, "max_lenght" => 200, "error_text" => $locale['article_0270']
            ));

            echo form_select("article_keywords", $locale['article_0260'], $criteriaArray['article_keywords'], array(
                "max_length" => 320, "placeholder" => $locale['article_0260a'], "width" => "100%", "inner_width" => "100%", "tags" => TRUE, "multiple" => TRUE
            ));

            echo form_textarea("article_snippet", $locale['article_0251'], $criteriaArray['article_snippet'], $articleSnippetSettings);

            echo form_textarea("article_article", $locale['article_0252'], $criteriaArray['article_article'], $articleExtendedSettings);

            echo form_select_tree("article_cat", $locale['article_0101'], $criteriaArray['article_cat'], array(
                    "required" => TRUE, "error_text" => $locale['article_0273'], "inner_width" => "100%", "inline" => TRUE, "parent_value" => $locale['choose'],
                    "query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
                ),
                DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent"
            );

            if (multilang_table("AR")) {
                echo form_select("article_language", $locale['language'], $criteriaArray['article_language'], array(
                    "options" => fusion_get_enabled_languages(), "placeholder" => $locale['choose'], "inner_width" => "100%", "inline" => TRUE,
                ));
            } else {
                echo form_hidden("article_language", "", $criteriaArray['article_language']);
            }

            echo '<div class="m-t-20">';
                echo form_button("cancel_article", $locale['cancel'], $locale['cancel'], array("class" => "btn-default m-r-10", "icon" => "fa fa-fw fa-times"));
                echo form_button("submit_article", $locale['article_0900'], $locale['article_0900'], array("class" => "btn-success m-r-10", "icon" => "fa fa-fw fa-hdd-o"));
                echo form_button("preview_article", $locale['preview'], $locale['preview'], array("class" => "btn-primary m-r-10", "icon" => "fa fa-fw fa-eye"));
            echo '</div>';

            echo closeform();
        }

    } elseif (!iMEMBER && $articleSettings['article_allow_submission']) {
        echo '<div class="well text-center"><p>'.$locale['article_0921'].'</p></div>';
    } else {
        echo '<div class="well text-center"><p>'.$locale['article_0922'].'</p></div>';
    }
} else {
    echo '<div class="well text-center"><p>'.$locale['article_0923'].'</p></div>';
}

closetable();
