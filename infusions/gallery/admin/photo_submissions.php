<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/photo_submissions.php
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
pageAccess("PH");
if (fusion_get_settings("tinymce_enabled")) {
    echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
}
$locale = fusion_get_locale();

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
    if (isset($_POST['close'])) {
        redirect(clean_request("", array("submit_id"), FALSE));
    }
    if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {

        $result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_id='".intval($_GET['submit_id'])."'");

        if (dbrows($result)) {
            $data = dbarray($result);

            $callback_data = array(
                "photo_id"             => 0,
                "photo_title"          => form_sanitizer($_POST['photo_title'], "", "photo_title"),
                "album_id"             => form_sanitizer($_POST['album_id'], "", "album_id"),
                "photo_description"    => form_sanitizer($_POST['photo_description'], "", "photo_description"),
                "photo_keywords"       => form_sanitizer($_POST['photo_keywords'], "", "photo_keywords"),
                "photo_order"          => form_sanitizer($_POST['photo_order'], "", "photo_order"),
                "photo_datestamp"      => $data['submit_datestamp'],
                "photo_user"           => $data['submit_user'],
                "photo_allow_comments" => 0,
                "photo_allow_ratings"  => 0,
                "photo_views"          => 0,
                "photo_filename"       => form_sanitizer($_POST['photo_filename'], "", "photo_filename"),
                "photo_thumb1"         => isset($_POST['photo_thumb1']) ? form_sanitizer($_POST['photo_thumb1'], "", "photo_thumb1") : "",
                "photo_thumb2"         => isset($_POST['photo_thumb2']) ? form_sanitizer($_POST['photo_thumb2'], "", "photo_thumb2") : "",
            );

            if (defender::safe()) {

                $photo_name = $callback_data['photo_filename'];
                $thumb1_name = $callback_data['photo_thumb1'];
                $thumb2_name = $callback_data['photo_thumb2'];

                if (file_exists(INFUSIONS."gallery/submissions/".$photo_name) &&
                    !is_dir(INFUSIONS."gallery/submissions/".$photo_name)
                ) {
                    $callback_data['photo_filename'] = filename_exists(IMAGES_G, $photo_name);
                    copy(INFUSIONS."gallery/submissions/".$photo_name, IMAGES_G.$photo_name);
                    chmod(IMAGES_G.$photo_name, 0644);
                    unlink(INFUSIONS."gallery/submissions/".$photo_name);
                }

                if (file_exists(INFUSIONS."gallery/submissions/thumbs/".$thumb1_name) &&
                    !is_dir(INFUSIONS."gallery/submissions/thumbs/".$thumb1_name)
                ) {
                    $callback_data['photo_thumb1'] = filename_exists(IMAGES_G_T, $thumb1_name);
                    copy(INFUSIONS."gallery/submissions/thumbs/".$thumb1_name, IMAGES_G_T.$thumb1_name);
                    chmod(IMAGES_G_T.$thumb1_name, 0644);
                    unlink(INFUSIONS."gallery/submissions/thumbs/".$thumb1_name);
                }

                if (file_exists(INFUSIONS."gallery/submissions/thumbs/".$thumb2_name) &&
                    !is_dir(INFUSIONS."gallery/submissions/thumbs/".$thumb2_name)
                ) {
                    $callback_data['photo_thumb2'] = filename_exists(IMAGES_G_T, $thumb2_name);
                    copy(INFUSIONS."gallery/submissions/thumbs/".$thumb2_name, IMAGES_G_T.$thumb2_name);
                    chmod(IMAGES_G_T.$thumb2_name, 0644);
                    unlink(INFUSIONS."gallery/submissions/thumbs/".$thumb2_name);
                }

                dbquery_insert(DB_PHOTOS, $callback_data, "save");

                $result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
                addNotice("success", $locale['gallery_0160']);
                redirect(clean_request("", array("submit_id"), FALSE));

            }
        } else {
            redirect(clean_request("", array("submit_id"), FALSE));
        }
    } else {
        if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
            $result = dbquery("
            SELECT
            ts.submit_id, ts.submit_datestamp, ts.submit_criteria
            FROM ".DB_SUBMISSIONS." ts
            WHERE submit_type='p' and submit_id='".intval($_GET['submit_id'])."'
        ");
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                $criteriaArray = unserialize($data['submit_criteria']);
                purgeSubmissionsPhotoImage($criteriaArray);
                $result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($data['submit_id'])."'");
                addNotice("success", $locale['gallery_0161']);
            }
            redirect(clean_request("", array("submit_id"), FALSE));
        } else {

            $result = dbquery("SELECT
            ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='p' AND submit_id='".intval($_GET['submit_id'])."'
            ");

            if (dbrows($result) > 0) {
                $data = dbarray($result);
                $submit_criteria = unserialize($data['submit_criteria']);
                $callback_data = array(
                    "album_id"          => $submit_criteria['album_id'],
                    "photo_title"       => $submit_criteria['photo_title'],
                    "photo_keywords"    => $submit_criteria['photo_keywords'],
                    "photo_description" => parse_textarea($submit_criteria['photo_description']),
                    "photo_filename"    => $submit_criteria['photo_filename'],
                    "photo_thumb1"      => $submit_criteria['photo_thumb1'],
                    "photo_thumb2"      => $submit_criteria['photo_thumb2'],
                    "photo_datestamp"   => $data['submit_datestamp'],
                    "photo_user"        => $data['user_id'],
                    "photo_order"       => dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS), 0) + 1
                );
                add_to_title($locale['global_201'].$locale['gallery_0100'].$locale['global_200'].$callback_data['photo_title']."?");
                $l_image = "";
                $submissions_dir = INFUSIONS."gallery/submissions/";
                $submissions_dir_t = INFUSIONS."gallery/submissions/thumbs/";
                echo openform("publish_article", "post", FUSION_REQUEST);
                echo "<div class='well clearfix'>\n";
                echo "<div class='pull-left'>\n";
                echo display_avatar($data, "30px", "", "", "");
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo $locale['gallery_0156'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
                echo $locale['gallery_0157'].timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
                echo "</div>\n";
                echo "</div>\n";
                echo "<div class='row'>\n";
                echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
                echo form_hidden("photo_datestamp", "", $callback_data['photo_datestamp']);
                echo form_hidden("photo_user", "", $callback_data['photo_user']);
                echo form_text("photo_title", $locale['photo_0001'], $callback_data['photo_title'], array(
                    "required"    => TRUE,
                    "placeholder" => $locale['photo_0002'],
                    "inline"      => TRUE
                ));
                echo form_select('photo_keywords', $locale['album_0005'], $callback_data['photo_keywords'], array(
                    'placeholder' => $locale['album_0006'],
                    'inline'      => TRUE,
                    'multiple'    => TRUE,
                    "tags"        => TRUE,
                    'width'       => '100%',
                ));
                echo form_text('photo_order', $locale['photo_0013'], $callback_data['photo_order'], array(
                    "type"   => "number",
                    "inline" => TRUE,
                    "width"  => "100px"
                ));
                $snippetSettings = array(
                    "preview"     => TRUE,
                    "html"        => TRUE,
                    "autosize"    => TRUE,
                    "form_name"   => "inputform",
                    'placeholder' => $locale['photo_0009'],
                    "inline"      => TRUE,
                );
                if (fusion_get_settings("tinymce_enabled")) {
                    $snippetSettings = array("inline" => TRUE, "form_name" => "inputform", 'placeholder' => $locale['photo_0009'],);
                }
                echo form_textarea('photo_description', $locale['photo_0008'], $callback_data['photo_description'], $snippetSettings);
                echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";

                if ($callback_data['photo_filename'] || $callback_data['photo_thumb1']) {

                    echo "<div class='list-group-item m-t-0'>\n";
                    $image = "";
                    if ($callback_data['photo_filename'] && file_exists($submissions_dir.$callback_data['photo_filename'])) {
                        $image = thumbnail($submissions_dir.$callback_data['photo_filename'], $gll_settings['thumb_w']);
                        $l_image = $submissions_dir.$callback_data['photo_filename'];
                        echo form_hidden("photo_filename", "", $callback_data['photo_filename']);
                    }
                    if ($callback_data['photo_thumb2'] && file_exists($submissions_dir_t.$callback_data['photo_thumb2'])) {
                        $image = thumbnail($submissions_dir_t.$callback_data['photo_thumb2'], $gll_settings['thumb_w']);
                        echo form_hidden("photo_thumb2", "", $callback_data['photo_thumb2']);
                    }
                    if ($callback_data['photo_thumb1'] && file_exists($submissions_dir_t.$callback_data['photo_thumb1'])) {
                        $image = thumbnail($submissions_dir_t.$callback_data['photo_thumb1'], $gll_settings['thumb_w']);
                        echo form_hidden("photo_thumb1", "", $callback_data['photo_thumb1']);
                    }
                    echo $image;
                    echo "</div>\n";
                }
                openside("");
                echo form_select('album_id', $locale['photo_0003'], $callback_data['album_id'], array(
                    'options' => get_albumOpts(),
                    'inline'  => TRUE
                ));
                echo form_button('publish', $locale['gallery_0158'], $locale['gallery_0158'], array('class' => 'btn-primary m-r-10'));
                closeside();
                echo "</div></div>\n";
                echo form_button('close', $locale['close'], $locale['close'], array('class' => 'btn-default m-r-10'));
                echo form_button('publish', $locale['gallery_0158'], $locale['gallery_0158'], array('class' => 'btn-primary m-r-10'));
                echo form_button('delete', $locale['gallery_0159'], $locale['gallery_0159'], array('class' => 'btn-warning m-r-10'));
                echo closeform();
            }
        }
    }
} else {
    $result = dbquery("SELECT
            ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='p' order by submit_datestamp desc
            ");
    $rows = dbrows($result);
    if ($rows > 0) {
        echo "<div class='well'>".sprintf($locale['gallery_0151'], format_word($rows, $locale['fmt_submission']))."</div>\n";
        echo "<div class='table-responsive'><table class='table table-striped'>\n";
        echo "<tr>\n";
        echo "<th>".$locale['gallery_0152']."</th>\n<th>".$locale['gallery_0153']."</th><th>".$locale['gallery_0154']."</th><th>".$locale['gallery_0155']."</th>";
        echo "</tr>\n";
        echo "<tbody>\n";
        while ($data = dbarray($result)) {
            $submit_criteria = unserialize($data['submit_criteria']);
            echo "<tr>\n";
            echo "<td><a href='".clean_request("submit_id=".$data['submit_id'], array(
                    "section",
                    "aid"
                ), TRUE)."'>".$submit_criteria['photo_title']."</a></td>\n";
            echo "<td>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
            echo "<td>".timer($data['submit_datestamp'])."</td>\n";
            echo "<td>".$data['submit_id']."</td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>\n</table>\n</div>";
    } else {
        echo "<div class='well text-center m-t-20'>".$locale['gallery_0150']."</div>\n";
    }
}
