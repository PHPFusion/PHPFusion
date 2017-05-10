<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_submit.php
| Author: PHP-Fusion Development Team
| Version: 1.12
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
include INFUSIONS.'news/templates/submissions.php';

$news_settings = get_settings('news');
$locale = fusion_get_locale('', [NEWS_ADMIN_LOCALE]);

if (iMEMBER && $news_settings['news_allow_submission']) {

    $criteriaArray = [
        'news_subject'            => '',
        'news_cat'                => 0,
        'news_news'               => '',
        'news_extended'           => '',
        'news_language'           => LANGUAGE,
        'news_keywords'           => '',
        'news_image_full_default' => 0,
        'news_image_font_default' => 0,
        'news_image_align'        => '',
    ];

    if (isset($_POST['submit_news'])) {

        $criteriaArray = array(
            'news_subject'  => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
            'news_cat'      => form_sanitizer($_POST['news_cat'], '', 'news_cat'),
            'news_news'     => form_sanitizer($_POST['news_news'], '', 'news_news'),
            'news_extended' => form_sanitizer($_POST['news_extended'], '', 'news_extended'),
            'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language'),
            'news_keywords' => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
        );

        if (!empty($_FILES['news_image']) && $news_settings['news_allow_submission_files']) {

            $upload = form_sanitizer($_FILES['news_image'], '', 'news_image');
            if (!empty($upload)) {
                if (!$upload['error']) {
                    $data = array(
                        'news_image_user'      => fusion_get_userdata('user_id'),
                        'submit_id'            => dbnextid(DB_SUBMISSIONS),
                        'news_image'           => $upload['image_name'],
                        'news_image_t1'        => $upload['thumb1_name'],
                        'news_image_t2'        => $upload['thumb2_name'],
                        'news_image_datestamp' => TIME
                    );
                    $criteriaArray['news_image_align'] = form_sanitizer($_POST['news_image_align'], '', 'news_image_align');

                    $photo_id = dbquery_insert(DB_NEWS_IMAGES, $data, 'save');
                    $criteriaArray['news_image_full_default'] = $photo_id;
                    $criteriaArray['news_image_front_default'] = $photo_id;

                }
            }
        }

        if (\defender::safe()) {

            $inputArray = array(
                'submit_type'      => 'n',
                'submit_user'      => fusion_get_userdata('user_id'),
                'submit_datestamp' => TIME,
                'submit_criteria'  => \defender::encode($criteriaArray)
            );

            dbquery_insert(DB_SUBMISSIONS, $inputArray, 'save');
            addNotice('success', $locale['news_0701']);
            redirect(clean_request('submitted=n', array('submitted', 'stype'), TRUE));
        }
    }

	if (isset($_POST['preview_news'])) {
            $criteriaArray = array(
            'news_subject'  => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
            'news_cat'      => form_sanitizer($_POST['news_cat'], '', 'news_cat'),
            'news_news'     => form_sanitizer($_POST['news_news'], '', 'news_news'),
            'news_extended' => form_sanitizer($_POST['news_extended'], '', 'news_extended'),
            'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language'),
            'news_keywords' => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
            'news_image_align' => !empty($_POST['news_image_align']) ? form_sanitizer($_POST['news_image_align'], '', 'news_image_align') : "",
            );
            if (\defender::safe() && isset($_POST['preview_news'])) {
                $footer = openmodal("news_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$locale['preview'].": ".$criteriaArray['news_subject']);
                $footer .= nl2br(parse_textarea($criteriaArray['news_news']));
                if ($criteriaArray['news_extended']) {
                    $footer .= "<hr class='m-t-20 m-b-20'>\n";
                    $footer .= nl2br(parse_textarea($criteriaArray['news_extended']));
                }
                $footer .= closemodal();
                add_to_footer($footer);
            }
        }

    if (isset($_GET['submitted']) && $_GET['submitted'] == "n") {

        add_to_title($locale['global_200'].$locale['news_0400']);
        echo strtr(display_news_confirm_submissions(), [
            '{%title%}'       => $locale['news_0400'],
            '{%message%}'     => $locale['news_0701'],
            '{%submit_link%}' => "<a href='".BASEDIR."submit.php?stype=n'>".$locale['news_0702']."</a>",
            '{%index_link%}'  => "<a href='".BASEDIR."index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['news_0704'])."</a>",
        ]);

    } else {

        add_to_title($locale['global_200'].$locale['news_0400']);

        $info = [
            'guidelines'             => str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['news_0703']),
            'news_subject_field'     => form_text('news_subject', $locale['news_0200'], $criteriaArray['news_subject'], array('required' => TRUE, 'inline' => TRUE)),
            'news_language_field'    => (multilang_table('NS') ? form_select('news_language', $locale['global_ML100'], $criteriaArray['news_language'],
                [
                    'options'     => fusion_get_enabled_languages(),
                    'placeholder' => $locale['choose'],
                    'width'       => '250px',
                    'inline'      => TRUE,
                ]) : form_hidden('news_language', '', $criteriaArray['news_language'])),
            'news_keywords_field'    => form_select('news_keywords', $locale['news_0205'], $criteriaArray['news_keywords'], array('max_length' => 320, 'inline' => TRUE, 'placeholder' => $locale['news_0205a'], 'width' => '100%', 'inner_width' => '100%', 'error_text' => $locale['news_0255'], 'tags' => TRUE, 'multiple' => TRUE)),
            'news_cat_field'         => form_select_tree('news_cat', $locale['news_0201'], $criteriaArray['news_cat'],
                [
                    'width'        => '250px', 'inline' => TRUE,
                    'parent_value' => $locale['news_0202'],
                    "query"        => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
                ],
                DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent"
            ),
            'news_image_field'       => ($news_settings['news_allow_submission_files'] ? form_fileinput('news_image', $locale['news_0009'], '',
                [
                    'upload_path'      => IMAGES_N,
                    'max_width'        => $news_settings['news_photo_max_w'],
                    'max_height'       => $news_settings['news_photo_max_h'],
                    'max_byte'         => $news_settings['news_photo_max_b'],
                    // set thumbnail
                    'thumbnail'        => 1,
                    'thumbnail_w'      => $news_settings['news_thumb_w'],
                    'thumbnail_h'      => $news_settings['news_thumb_h'],
                    'thumbnail_folder' => 'thumbs',
                    'delete_original'  => 0,
                    // set thumbnail 2 settings
                    'thumbnail2'       => 1,
                    'thumbnail2_w'     => $news_settings['news_photo_w'],
                    'thumbnail2_h'     => $news_settings['news_photo_h'],
                    'type'             => 'image',
                    'inline'           => TRUE,
                    'ext_tip'          => sprintf($locale['news_0217'], parsebytesize($news_settings['news_photo_max_b'])),
                ]
            ) : ''),
            'news_image_align_field' => ($news_settings['news_allow_submission_files'] ? form_select('news_image_align', $locale['news_0218'], $criteriaArray['news_image_align'],
                [
                    'options' => array(
                        'pull-left'       => $locale['left'],
                        'news-img-center' => $locale['center'],
                        'pull-right'      => $locale['right']
                    ),
                    'inline'  => TRUE
                ]
            ) : ''),
            'news_news_field'        => form_textarea('news_news', $locale['news_0203'], $criteriaArray['news_news'],
                [
                    'required'  => TRUE,
                    'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
                    'tinymce'   => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
                    'autosize'  => TRUE,
                    'form_name' => 'submit_form',
                ]
            ),
            'news_body_field'        => form_textarea('news_extended', $locale['news_0005'], $criteriaArray['news_extended'],
                [
                    'required'  => $news_settings['news_extended_required'] ? TRUE : FALSE,
                    'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
                    'tinymce'   => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
                    'autosize'  => TRUE,
                    'form_name' => 'submit_form',
                ]
            ),
            'news_submit'            => form_button('submit_news', $locale['news_0700'], $locale['news_0700'], array('class' => 'btn-primary m-r-10', 'icon' => 'fa fa-hdd-o')),
            'preview_news'           => (fusion_get_settings('site_seo') ? '' : form_button('preview_news', $locale['news_0141'], $locale['news_0141'], array('icon' => 'fa fa-eye'))),
            'criteria_array'         => $criteriaArray,
        ];

        echo openform('submit_form', 'post', BASEDIR."submit.php?stype=n", array("enctype" => $news_settings['news_allow_submission_files'] ? TRUE : FALSE));

        echo strtr(display_news_submissions_form($info), [
            '{%title%}'                  => $locale['news_0400'],
            '{%guidelines%}'             => $info['guidelines'],
            '{%news_subject_field%}'     => $info['news_subject_field'],
            '{%news_language_field%}'    => $info['news_language_field'],
            '{%news_keywords_field%}'    => $info['news_keywords_field'],
            '{%news_cat_field%}'         => $info['news_cat_field'],
            '{%news_image_field%}'       => $info['news_image_field'],
            '{%news_image_align_field%}' => $info['news_image_align_field'],
            '{%news_news_field%}'        => $info['news_news_field'],
            '{%news_body_field%}'        => $info['news_body_field'],
            '{%news_submit%}'            => $info['news_submit'],
            '{%preview_news%}'           => $info['preview_news'],

        ]);
        echo closeform();
    }
} else {
    echo strtr(display_news_no_submissions(), [
        '{%title%' => $locale['news_0400'],
        '{%text%}' => $locale['news_0138']
    ]);
}
