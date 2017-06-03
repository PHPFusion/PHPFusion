<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/download_submit.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$dl_settings = get_settings("downloads");

$locale = fusion_get_locale('', DOWNLOAD_ADMIN_LOCALE);

add_to_title($locale['global_200'].$locale['download_0041']);

opentable("<i class='fa fa-download fa-lg fa-fw'></i>".$locale['download_0041']);
if (iMEMBER && $dl_settings['download_allow_submission']) {
    $criteriaArray = array(
        "download_title" => "",
        "download_cat" => 0,
        "download_keywords" => "",
        "download_description_short" => "",
        "download_description" => "",
        "download_url" => "",
        "download_license" => "",
        "download_os" => "",
        "download_version" => "",
        "download_homepage" => "",
        "download_copyright" => "",
    );
    if (isset($_POST['submit_download'])) {
        $criteriaArray = array(
            'download_title' => form_sanitizer($_POST['download_title'], '', 'download_title'),
            'download_keywords' => form_sanitizer($_POST['download_keywords'], '', 'download_keywords'),
            'download_description' => form_sanitizer($_POST['download_description'], '', 'download_description'),
            'download_description_short' => form_sanitizer($_POST['download_description_short'], '', 'download_description_short'),
            'download_cat' => form_sanitizer($_POST['download_cat'], '0', 'download_cat'),
            'download_homepage' => form_sanitizer($_POST['download_homepage'], '', 'download_homepage'),
            'download_license' => form_sanitizer($_POST['download_license'], '', 'download_license'),
            'download_copyright' => form_sanitizer($_POST['download_copyright'], '', 'download_copyright'),
            'download_os' => form_sanitizer($_POST['download_os'], '', 'download_os'),
            'download_version' => form_sanitizer($_POST['download_version'], '', 'download_version'),
            'download_file' => '',
            'download_url' => '',
            'download_image' => '',
            'download_image_thumb' => ''
        );
        /**
         * Download File Section
         */
        if ($defender::safe() && !empty($_FILES['download_file']['name']) && is_uploaded_file($_FILES['download_file']['tmp_name'])) {

            $upload = form_sanitizer($_FILES['download_file'], '', 'download_file');
            $criteriaArray['download_filesize'] = parsebytesize($_FILES['download_file']['size']);

            if (empty($upload['error']) && !empty($_FILES['download_file']['size'])) {
                // might be image, might be file
                if (!empty($upload['image_name'])) {
                    $criteriaArray['download_file'] = $upload['image_name'];
                } elseif (!empty($upload['target_file'])) {
                    $criteriaArray['download_file'] = $upload['target_file'];
                } else {
                    \defender::stop();
                    addNotice('warning', $locale['download_0113']);
                }
            }
            unset($upload);
        } elseif (!empty($_POST['download_url']) && empty($data['download_file'])) {
            $criteriaArray['download_url'] = form_sanitizer($_POST['download_url'], '', 'download_url');
        } elseif (empty($data['download_file']) && empty($data['download_url'])) {
            $defender->stop();
            addNotice('danger', $locale['download_0111']);
        }
        // Screenshot submissions
        if ($defender::safe() && !empty($_FILES['download_image']['name']) && is_uploaded_file($_FILES['download_image']['tmp_name'])) {
            $upload = form_sanitizer($_FILES['download_image'], '', 'download_image');
            if (empty($upload['error'])) {
                $criteriaArray['download_image'] = $upload['image_name'];
                $criteriaArray['download_image_thumb'] = $upload['thumb1_name'];
                unset($upload);
            }
        } else {
            if ($dl_settings['download_screenshot_required']) {
                $defender->stop();
                $defender->setInputError("download_image");
            }
        }
        if (defender::safe()) {
            $inputArray = array(
                'submit_type' => 'd',
                'submit_user' => $userdata['user_id'],
                'submit_datestamp' => time(),
                'submit_criteria' => serialize($criteriaArray)
            );
            dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
            addNotice("success", $locale['download_0042']);
            redirect(clean_request("submitted=d", array("stype"), TRUE));
        }
    }
    if (isset($_GET['submitted']) && $_GET['submitted'] == "d") {
        echo "<div class='well text-center'><p><strong>".$locale['download_0042']."</strong></p>";
        echo "<p><a href='submit.php?stype=d'>".$locale['download_0043']."</a></p>";
        echo "<p><a href='index.php'>".str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['download_0039'])."</a></p>\n";
        echo "</div>\n";
    } else {
        /**
         * The form
         */
        // must have category
        if (dbcount("(download_cat_id)", DB_DOWNLOAD_CATS, multilang_table("DL") ? "download_cat_language='".LANGUAGE."'" : "")) {
            echo "<div class='panel panel-default tbl-border'>\n<div class='panel-body'>\n";
            echo "<div class='alert alert-info m-b-20 submission-guidelines'>".str_replace("[SITENAME]", fusion_get_settings("sitename"),
                                                                                           $locale['download_0044'])."</div>\n";
            echo openform('submit_form', 'post', BASEDIR."submit.php?stype=d", array('enctype' => TRUE));
            echo form_text('download_title', $locale['download_0200'], $criteriaArray['download_title'], array(
                'required' => TRUE,
                "inline" => TRUE,
                'error_text' => $locale['download_0110']
            ));
            echo form_select_tree("download_cat", $locale['download_0207'], $criteriaArray['download_cat'], array(
                "inline" => TRUE,
                "no_root" => TRUE,
                "placeholder" => $locale['choose'],
                "query" => (multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")
            ), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
            echo form_select('download_keywords', $locale['download_0203'], $criteriaArray['download_keywords'], array(
                "placeholder" => $locale['download_0203a'],
                'max_length' => 320,
                "inline" => TRUE,
                'width' => '100%',
                'tags' => 1,
                'multiple' => 1
            ));

            $textArea_opts = array(
                "required" => TRUE,
                "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode",
                "tinymce" => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
                "autosize" => TRUE,
                "error_text" => $locale['download_0112'],
                "form_name" => "submit_form",
            );

            echo form_textarea('download_description_short', $locale['download_0202'], $criteriaArray['download_description_short'], $textArea_opts);

            $textArea_opts['required'] = $dl_settings['download_extended_required'] ? TRUE : FALSE;
            $textArea_opts['error_text'] = $locale['download_0201'];

            echo form_textarea('download_description', $locale['download_0202a'], $criteriaArray['download_description'], $textArea_opts);

            echo "<div class='row m-l-0 m-r-0 m-b-20'>\n";
            echo "<div class='col-xs-12 col-sm-3 p-l-0'>\n&nbsp;";
            echo "</div>\n";
            echo "<div class='col-xs-12 col-sm-9 p-r-0'>\n";
            $tab_title['title'][] = "1 -".$locale['download_0214'];
            $tab_title['id'][] = 'dlf';
            $tab_title['icon'][] = 'fa fa-file-zip-o fa-fw';
            $tab_title['title'][] = "2 -".$locale['download_0215'];
            $tab_title['id'][] = 'dll';
            $tab_title['icon'][] = 'fa fa-plug fa-fw';
            $tab_active = tab_active($tab_title, 0);
            echo "<div class='list-group-item'>\n";
            echo "<div class='well'>\n";
            echo "<strong>".$locale['download_0204']."</strong>\n";
            echo "</div>\n";
            echo opentab($tab_title, $tab_active, 'downloadtab');
            echo opentabbody($tab_title['title'][0], 'dlf', $tab_active);
            $file_options = array(
                "class" => "m-10 p-10",
                "inline" => TRUE,
                "required" => TRUE,
                "upload_path" => DOWNLOADS."submissions/",
                "max_byte" => $dl_settings['download_max_b'],
                'valid_ext' => $dl_settings['download_types'],
                'error_text' => $locale['download_0115'],
                "width" => "100%",
                "thumbnail" => FALSE,
                "thumbnail2" => FALSE,
                "type" => "object",
                "preview_off" => TRUE,
            );
            echo form_fileinput('download_file', $locale['download_0214'], '', $file_options);
            echo "<div class='text-right'>\n<small>\n";
            echo sprintf($locale['download_0218'], parsebytesize($dl_settings['download_max_b']),
                         str_replace(',', ' ', $dl_settings['download_types']))."<br />\n";
            echo "</small>\n</div>\n";
            echo closetabbody();
            echo opentabbody($tab_title['title'][1], 'dll', $tab_active);
            echo form_text('download_url', $locale['download_0206'], "", array(
                "class" => "m-10 p-10",
                "error_text" => $locale['download_0116'],
                "inline" => TRUE,
                "required" => TRUE,
                "placeholder" => "http://"
            ));
            echo closetabbody();
            echo closetab();
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
            if ($dl_settings['download_screenshot']) {
                $screenshot_options = array(
                    "inline" => TRUE,
                    "upload_path" => DOWNLOADS."submissions/images/",
                    "required" => $dl_settings['download_screenshot_required'] ? TRUE : FALSE,
                    "max_width" => $dl_settings['download_screen_max_w'],
                    "max_height" => $dl_settings['download_screen_max_h'],
                    "max_byte" => $dl_settings['download_screen_max_b'],
                    "type" => "image",
                    "delete_original" => FALSE,
                    "thumbnail_folder" => "",
                    "thumbnail" => TRUE,
                    "thumbnail_suffix" => "_thumb",
                    "thumbnail_w" => $dl_settings['download_thumb_max_w'],
                    "thumbnail_h" => $dl_settings['download_thumb_max_h'],
                    "thumbnail2" => 0,
                    "error_text" => $locale['download_0114'],
                    "template" => "modern"
                );
                echo form_fileinput('download_image', $locale['download_0220'], '', $screenshot_options);
            }

            echo "<div class='text-right m-b-10'>\n<small>\n";

            echo sprintf($locale['download_0219'],
                         parsebytesize($dl_settings['download_screen_max_b']),
                         str_replace(',', ' ', ".jpg,.gif,.png"),
                         $dl_settings['download_screen_max_w'],
                         $dl_settings['download_screen_max_h'])."\n";
            echo "</small>\n</div>\n";

            echo form_text('download_license', $locale['download_0208'], $criteriaArray['download_license'], array("inline" => TRUE));

            echo form_text('download_os', $locale['download_0209'], $criteriaArray['download_os'], array("inline" => TRUE));

            echo form_text('download_version', $locale['download_0210'], $criteriaArray['download_version'], array("inline" => TRUE));

            echo form_text('download_homepage', $locale['download_0221'], $criteriaArray['download_homepage'], array("inline" => TRUE));

            echo form_text('download_copyright', $locale['download_0222'], $criteriaArray['download_copyright'], array("inline" => TRUE));

            echo form_hidden('calc_upload', '', '1');

            echo "</div>\n</div>\n";

            echo form_button('submit_download', $locale['download_0041'], $locale['download_0041'], array('class' => 'btn-success', 'icon' => 'fa fa-hdd-o'));

            echo closeform();

        } else {
            echo "<div class='well text-center'>".$locale['download_0249']."<br /><br />\n</div>\n";
        }
    }
} else {
    echo "<div class='well text-center'>".$locale['download_0040']."</div>\n";
}
closetable();
