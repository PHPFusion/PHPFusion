<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: blog_submit.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

require_once INFUSIONS.'blog/templates/blog.tpl.php';
$blog_settings = get_settings("blog");
$locale = fusion_get_locale('', BLOG_ADMIN_LOCALE);
$userdata = fusion_get_userdata();

add_to_title($locale['blog_0700']);
$criteriaArray = [];
if (iMEMBER && $blog_settings['blog_allow_submission'] && checkgroup($blog_settings['blog_submission_access'])) {
    $criteriaArray = [
        "blog_subject"  => "",
        "blog_cat"      => 0,
        "blog_blog"     => "",
        "blog_body"     => "",
        "blog_language" => LANGUAGE,
        "blog_keywords" => "",
        "blog_ialign"   => "",
    ];
    if (isset($_POST['submit_blog'])) {
        $blog_blog = "";
        if ($_POST['blog_blog']) {
            $blog_blog = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslashes($_POST['blog_blog']));
        }
        $blog_extended = "";
        if ($_POST['blog_body']) {
            $blog_extended = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslashes($_POST['blog_body']));
        }
        $criteriaArray = [
            "blog_subject"  => form_sanitizer($_POST['blog_subject'], "", "blog_subject"),
            "blog_cat"      => form_sanitizer($_POST['blog_cat'], "", "blog_cat"),
            "blog_blog"     => form_sanitizer($blog_blog, "", "blog_blog"),
            "blog_body"     => form_sanitizer($blog_extended, "", "blog_body"),
            "blog_language" => form_sanitizer($_POST['blog_language'], LANGUAGE, "blog_language"),
            "blog_keywords" => form_sanitizer($_POST['blog_keywords'], "", "blog_keywords"),
        ];
        if ($blog_settings['blog_allow_submission_files']) {
            if (isset($_FILES['blog_image'])) {
                $upload = form_sanitizer($_FILES['blog_image'], '', 'blog_image');
                if (!empty($upload)) {
                    $criteriaArray['blog_image'] = $upload['image_name'];
                    $criteriaArray['blog_image_t1'] = $upload['thumb1_name'];
                    $criteriaArray['blog_image_t2'] = $upload['thumb2_name'];
                } else {
                    $criteriaArray['blog_image'] = (isset($_POST['blog_image']) ? $_POST['blog_image'] : "");
                    $criteriaArray['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
                    $criteriaArray['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
                }
                $criteriaArray['blog_ialign'] = (isset($_POST['blog_ialign']) ? form_sanitizer($_POST['blog_ialign'], "pull-left", "blog_ialign") : "pull-left");
            }
        }
        if (fusion_safe()) {
            $inputArray = [
                "submit_type"      => "b",
                "submit_user"      => $userdata['user_id'],
                "submit_datestamp" => time(),
                "submit_criteria"  => addslashes(serialize($criteriaArray))
            ];
            dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
            addnotice("success", $locale['blog_0701']);
            redirect(clean_request("submitted=b", ["stype"]));
        }
    } else if (isset($_POST['preview_blog'])) {
        /* lost data after preview */
        $blog_blog = "";
        if ($_POST['blog_blog']) {
            $blog_blog = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslashes($_POST['blog_blog']));
        }
        $blog_body = "";
        if ($_POST['blog_body']) {
            $blog_body = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslashes($_POST['blog_body']));
        }
        $criteriaArray = [
            "blog_subject"  => form_sanitizer($_POST['blog_subject'], "", "blog_subject"),
            "blog_cat"      => form_sanitizer($_POST['blog_cat'], 0, "blog_cat"),
            "blog_keywords" => form_sanitizer($_POST['blog_keywords'], "", "blog_keywords"),
            "blog_blog"     => form_sanitizer($blog_blog, "", "blog_blog"),
            "blog_body"     => form_sanitizer($blog_body, "", "blog_body"),
            "blog_image"    => isset($_POST['blog_image']) ? $_POST['blog_image'] : '',
            "blog_image_t1" => isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "",
            "blog_image_t2" => isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "",
            "blog_ialign"   => (isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : "pull-left"),
            "blog_language" => form_sanitizer($_POST['blog_language'], LANGUAGE, "blog_language"),
        ];
    }
    $criteriaArray['submitted'] = FALSE;
    if (fusion_safe() && isset($_POST['preview_blog'])) {
        $footer = openmodal("blog_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$locale['preview'].": ".$criteriaArray['blog_subject']);
        $footer .= parse_text($criteriaArray['blog_blog'], [
            'parse_smileys'   => FALSE,
            'parse_bbcode'    => FALSE,
            'add_line_breaks' => TRUE
        ]);
        if ($criteriaArray['blog_body']) {
            $footer .= "<hr class='m-t-20 m-b-20'>\n";
            $footer .= parse_text($criteriaArray['blog_body'], [
                'parse_smileys'   => FALSE,
                'parse_bbcode'    => FALSE,
                'add_line_breaks' => TRUE
            ]);
        }
        $footer .= closemodal();
        add_to_footer($footer);
    }
}

display_blog_submit($criteriaArray);
