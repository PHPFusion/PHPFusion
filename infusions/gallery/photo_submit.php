<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery/photo_submit.php
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

if (file_exists(INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php")) {
    include INFUSIONS."gallery/locale/".LOCALESET."gallery_admin.php";
} else {
    include INFUSIONS."gallery/locale/English/gallery_admin.php";
}

add_to_title($locale['global_200'].$locale['gallery_0100']);
$gll_settings = get_settings("gallery");

opentable("<i class='fa fa-camera-retro m-r-5 fa-lg'></i> ".$locale['gallery_0100']);

if ($gll_settings['gallery_allow_submission']) {

    $criteriaArray = array(
        "album_id" => 0,
        "photo_title" => "",
        "photo_description" => "",
        "photo_filename" => "",
        "photo_thumb1" => "",
        "photo_thumb2" => "",
        "photo_keywords" => "",
    );

    if (isset($_POST['submit_photo'])) {
        $criteriaArray = array(
            "album_id" => form_sanitizer($_POST['album_id'], 0, "album_id"),
            "photo_title" => form_sanitizer($_POST['photo_title'], "", "photo_title"),
            "photo_keywords" => form_sanitizer($_POST['photo_keywords'], "", "photo_keywords"),
            "photo_description" => form_sanitizer($_POST['photo_description'], "", "photo_description"),
            "photo_filename" => "",
            "photo_thumb1" => "",
            "photo_thumb2" => "",
        );
        if (defender::safe()) {

            if (!empty($_FILES['photo_image']) && is_uploaded_file($_FILES['photo_image']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['photo_image'], "", "photo_image");
                if (empty($upload['error'])) {
                    $criteriaArray['photo_filename'] = $upload['image_name'];
                    $criteriaArray['photo_thumb1'] = $upload['thumb1_name'];
                    $criteriaArray['photo_thumb2'] = $upload['thumb2_name'];
                }
            } else {
                $defender->stop();
                $defender->setInputError("photo_image");
                addNotice("danger", $locale['photo_0014']);
            }

        }

        if (defender::safe()) {

            $inputArray = array(
                "submit_id" => 0,
                "submit_type" => "p",
                "submit_user" => fusion_get_userdata("user_id"),
                "submit_datestamp" => time(),
                "submit_criteria" => addslashes(serialize($criteriaArray))
            );
            dbquery_insert(DB_SUBMISSIONS, $inputArray, "save");
            addNotice("success", $locale['gallery_0101']);
            redirect(clean_request("submitted=p", array("stype"), TRUE));
        }
    }

    if (isset($_GET['submitted']) && $_GET['submitted'] == "p") {
        echo "<div class='well text-center'><p><strong>".$locale['gallery_0101']."</strong></p>";
        echo "<p><a href='submit.php?stype=p'>".$locale['gallery_0102']."</a></p>";
        echo "<p><a href='index.php'>".$locale['gallery_0113']."</a></p>\n";
        echo "</div>\n";
    } else {
        $result = dbquery("SELECT album_id, album_title FROM ".DB_PHOTO_ALBUMS." ".(multilang_table("PG") ? "WHERE album_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess("album_access")." ORDER BY album_title");
        if (dbrows($result) > 0) {
            $opts = array();
            while ($data = dbarray($result)) {
                $opts[$data['album_id']] = $data['album_title'];
            }
            echo openform('submit_form', 'post', BASEDIR."submit.php?stype=p", array("enctype" => TRUE));
            echo "<div class='alert alert-info m-b-20 submission-guidelines'>".$locale['gallery_0107']."</div>\n";
            echo form_select('album_id', $locale['gallery_0103'], '', array("options" => $opts, "inline" => TRUE));
            echo form_text('photo_title', $locale['gallery_0104'], '', array('required' => TRUE, "inline" => TRUE));
            echo form_select('photo_keywords', $locale['gallery_0105'], $data['photo_keywords'], array(
                'placeholder' => $locale['photo_0007'],
                'inline' => TRUE,
                'multiple' => TRUE,
                "tags" => TRUE,
                'width' => '100%',
            ));

            $textArea_opts = array(
                "required" => $gll_settings['gallery_extended_required'] ? TRUE : FALSE,
                "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "html",
                "tinymce" => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
                "autosize" => TRUE,
                "form_name" => "submit_form",
            );

            echo form_textarea('photo_description', $locale['gallery_0106'], '', $textArea_opts);

            echo form_fileinput('photo_image', $locale['gallery_0109'], '', array(
                "upload_path" => INFUSIONS."gallery/submissions/",
                "required" => TRUE,
                'thumbnail_folder' => 'thumbs',
                'thumbnail' => TRUE,
                'thumbnail_w' => $gll_settings['thumb_w'],
                'thumbnail_h' => $gll_settings['thumb_h'],
                'thumbnail_suffix' => '_t1',
                'thumbnail2' => TRUE,
                'thumbnail2_w' => $gll_settings['photo_w'],
                'thumbnail2_h' => $gll_settings['photo_h'],
                'thumbnail2_suffix' => '_t2',
                'max_width' => $gll_settings['photo_max_w'],
                'max_height' => $gll_settings['photo_max_h'],
                'max_byte' => $gll_settings['photo_max_b'],
                'delete_original' => FALSE,
                "multiple" => FALSE,
                "inline" => TRUE,
                "error_text" => $locale['gallery_0110'],
            ));
            echo "<div class='m-b-10 col-xs-12 col-sm-9 col-sm-offset-3'>".sprintf($locale['photo_0017'], parsebytesize($gll_settings['photo_max_b']),
                                                                                   str_replace(',', ' ', ".jpg,.gif,.png"),
                                                                                   $gll_settings['photo_max_w'],
                                                                                   $gll_settings['photo_max_h'])."</div>\n";
            echo form_button('submit_photo', $locale['gallery_0111'], $locale['gallery_0111'], array('class' => 'btn-success', 'icon' => 'fa fa-hdd-o'));
            echo closeform();
        } else {
            echo "<div class='well' style='text-align:center'><br />\n".$locale['gallery_0024']."<br /><br />\n</div>\n";
        }
    }
} else {
    echo "<div class='well text-center'>".$locale['gallery_0112']."</div>\n";
}
closetable();
