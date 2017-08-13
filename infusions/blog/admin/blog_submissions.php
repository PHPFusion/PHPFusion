<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/blog_submissions.php
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
echo '<div class="m-t-10">';
echo '<h2>'.$locale['blog_0131'].'</h2>';

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
    if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
        $result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_id='".$_GET['submit_id']."'");
        if (dbrows($result)) {
            $data = dbarray($result);
            $data = array(
                'blog_id' => 0,
                'blog_subject' => form_sanitizer($_POST['blog_subject'], '', 'blog_subject'),
                'blog_cat' => form_sanitizer($_POST['blog_cat'], 0, 'blog_cat'),
                'blog_name' => $data['user_id'],
                'blog_blog' => addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['blog_blog'])),
                'blog_extended' => addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['blog_extended'])),
                'blog_keywords' => form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords'),
                'blog_datestamp' => form_sanitizer($_POST['blog_datestamp'], time(), 'blog_datestamp'),
                'blog_start' => form_sanitizer($_POST['blog_start'], 0, 'blog_start'),
                'blog_end' => form_sanitizer($_POST['blog_end'], 0, 'blog_end'),
                'blog_visibility' => form_sanitizer($_POST['blog_visibility'], 0, 'blog_visibility'),
                'blog_draft' => isset($_POST['blog_draft']) ? "1" : "0",
                'blog_sticky' => isset($_POST['blog_sticky']) ? "1" : "0",
                'blog_allow_comments' => 0,
                'blog_allow_ratings' => 0,
                'blog_language' => form_sanitizer($_POST['blog_language'], '', 'blog_language')
            );
            if (isset($_FILES['blog_image'])) { // when files is uploaded.
                $upload = form_sanitizer($_FILES['blog_image'], '', 'blog_image');
                if (!empty($upload) && !$upload['error']) {
                    $data['blog_image'] = $upload['image_name'];
                    $data['blog_image_t1'] = $upload['thumb1_name'];
                    $data['blog_image_t2'] = $upload['thumb2_name'];
                    $data['blog_ialign'] = (isset($_POST['blog_ialign']) ? form_sanitizer($_POST['blog_ialign'], "pull-left",
                                                                                          "blog_ialign") : "pull-left");
                }
            } else { // when files not uploaded. but there should be exist check.
                $data['blog_image'] = (isset($_POST['blog_image']) ? $_POST['blog_image'] : "");
                $data['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
                $data['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
                $data['blog_ialign'] = (isset($_POST['blog_ialign']) ? form_sanitizer($_POST['blog_ialign'], "pull-left",
                                                                                      "blog_ialign") : "pull-left");
            }
            if (fusion_get_settings('tinymce_enabled') != 1) {
                $data['blog_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
            } else {
                $data['blog_breaks'] = "n";
            }
            if ($data['blog_sticky'] == "1") {
                $result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'");
            } // reset other sticky
            // delete image if checkbox ticked
            if (isset($_POST['del_image'])) {
                if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) {
                    unlink(IMAGES_B.$data['blog_image']);
                }
                if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) {
                    unlink(IMAGES_B_T.$data['blog_image_t1']);
                }
                if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) {
                    unlink(IMAGES_B_T.$data['blog_image_t2']);
                }
                $data['blog_image'] = "";
                $data['blog_image_t1'] = "";
                $data['blog_image_t2'] = "";
            }
            if (defender::safe()) {
                dbquery_insert(DB_BLOG, $data, "save");
                $result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".$_GET['submit_id']."'");
                if ($data['blog_draft']) {
                    addNotice("success", $locale['blog_0147']);
                } else {
                    addNotice("success", $locale['blog_0146']);
                }
                redirect(clean_request("", array("submit_id"), FALSE));
            }
        } else {
            redirect(clean_request("", array("submit_id"), FALSE));
        }
    } elseif (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
        $result = dbquery("
            SELECT
            ts.submit_datestamp, ts.submit_criteria
            FROM ".DB_SUBMISSIONS." ts
            WHERE submit_type='b' and submit_id='".intval($_GET['submit_id'])."'
        ");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $submit_criteria = unserialize($data['submit_criteria']);
            if (!empty($submit_criteria['blog_image']) && file_exists(IMAGES_B.$submit_criteria['blog_image'])) {
                unlink(IMAGES_B.$submit_criteria['blog_image']);
            }
            if (!empty($submit_criteria['blog_image_t1']) && file_exists(IMAGES_B_T.$submit_criteria['blog_image_t1'])) {
                unlink(IMAGES_B_T.$submit_criteria['blog_image_t1']);
            }
            if (!empty($submit_criteria['blog_image_t2']) && file_exists(IMAGES_B_T.$submit_criteria['blog_image_t2'])) {
                unlink(IMAGES_B_T.$submit_criteria['blog_image_t2']);
            }
            $result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
            addNotice("success", $locale['blog_0145']);
        }
        redirect(clean_request("", array("submit_id"), FALSE));
    } else {
        $result = dbquery("SELECT
            ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='b' order by submit_datestamp desc");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $submit_criteria = unserialize($data['submit_criteria']);
            $callback_data = array(
                "blog_start" => $data['submit_datestamp'],
                "blog_datestamp" => $data['submit_datestamp'],
                "blog_keywords" => $submit_criteria['blog_keywords'],
                "blog_visibility" => 0,
                "blog_image" => $submit_criteria['blog_image'],
                "blog_image_t1" => $submit_criteria['blog_image_t1'],
                "blog_image_t2" => $submit_criteria['blog_image_t2'],
                "blog_ialign" => $submit_criteria['blog_ialign'],
                "blog_end" => "",
                "blog_draft" => 0,
                "blog_sticky" => 0,
                "blog_language" => $submit_criteria['blog_language'],
                "blog_subject" => $submit_criteria['blog_subject'],
                "blog_cat" => $submit_criteria['blog_cat'],
                "blog_blog" => phpentities(stripslashes($submit_criteria['blog_blog'])),
                "blog_extended" => phpentities(stripslashes($submit_criteria['blog_body'])),
                "blog_breaks" => fusion_get_settings("tinyce_enabled") ? TRUE : FALSE,
            );
            add_to_title($locale['global_200'].$locale['global_201'].$callback_data['blog_subject']."?");
            if (isset($_POST['preview'])) {
                $blog_blog = "";
                if ($_POST['blog_blog']) {
                    $blog_blog = phpentities(stripslash($_POST['blog_blog']));
                    $blog_blog = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['blog_blog']));
                }
                $blog_extended = "";
                if ($_POST['blog_extended']) {
                    $blog_extended = phpentities(stripslash($_POST['blog_extended']));
                    $blog_extended = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, stripslash($_POST['blog_extended']));
                }
                $callback_data = array(
                    "blog_subject" => form_sanitizer($_POST['blog_subject'], '', 'blog_subject'),
                    "blog_cat" => isnum($_POST['blog_cat']) ? $_POST['blog_cat'] : 0,
                    "blog_language" => form_sanitizer($_POST['blog_language'], '', 'blog_language'),
                    "blog_blog" => form_sanitizer($blog_blog, "", "blog_blog"),
                    "blog_extended" => form_sanitizer($blog_extended, "", "blog_extended"),
                    "blog_keywords" => form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords'),
                    "blog_start" => (isset($_POST['blog_start']) && $_POST['blog_start']) ? $_POST['blog_start'] : '',
                    "blog_end" => (isset($_POST['blog_end']) && $_POST['blog_end']) ? $_POST['blog_end'] : '',
                    "blog_visibility" => isnum($_POST['blog_visibility']) ? $_POST['blog_visibility'] : "0",
                    "blog_draft" => isset($_POST['blog_draft']) ? TRUE : FALSE,
                    "blog_sticky" => isset($_POST['blog_sticky']) ? TRUE : FALSE,
                    "blog_datestamp" => $callback_data['blog_datestamp'], // pull from db.
                    "blog_ialign" => isset($_POST['blog_ialign']) ? $_POST['blog_ialign'] : '',
                    "blog_image" => isset($_POST['blog_image']) ? $_POST['blog_image'] : '',
                    "blog_image_t1" => isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "",
                    "blog_image_t2" => isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "",
                );
                $callback_data['blog_breaks'] = "";
                if (isset($_POST['blog_breaks'])) {
                    $callback_data['blog_breaks'] = TRUE;
                    $callback_data['blog_blog'] = nl2br($callback_data['blog_blog']);
                    if ($callback_data['blog_extended']) {
                        $callback_data['blog_extended'] = nl2br($callback_data['blog_extended']);
                    }
                }
                if (defender::safe()) {
                    echo openmodal('blog_preview', $locale['blog_0141']);
                    echo "<h3>".$callback_data['blog_subject']."</h3>\n";
                    echo $callback_data['blog_blog'];
                    echo "<hr/>\n";
                    if (isset($callback_data['blog_extended'])) {
                        echo $callback_data['blog_extended'];
                    }
                    echo closemodal();
                }
            }
            echo openform("publish_blog", "post", FUSION_REQUEST);
            echo "<div class='well clearfix'>\n";
            echo "<div class='pull-left'>\n";
            echo display_avatar($data, "30px", "", TRUE, "img-rounded m-r-5");
            echo "</div>\n";
            echo "<div class='overflow-hide'>\n";
            echo $locale['blog_0132'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
            echo $locale['blog_0148'].timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
            echo "</div>\n";
            echo "</div>\n";
            echo "<div class='row'>\n";
            echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
            echo form_text("blog_subject", $locale['blog_0422'], $callback_data['blog_subject'], array(
                "required" => TRUE,
                "inline" => FALSE
            ));
            echo form_select('blog_keywords', $locale['blog_0443'], $callback_data['blog_keywords'], array(
                "max_length" => 320,
                "placeholder" => $locale['blog_0444'],
                "width" => "100%",
                "error_text" => $locale['blog_0457'],
                "tags" => TRUE,
                "multiple" => TRUE
            ));
            echo "<div class='row m-0'>\n";
            echo "<div class='pull-left m-r-10 display-inline-block'>\n";
            echo form_datepicker('blog_start', $locale['blog_0427'], $callback_data['blog_start'], array('placeholder' => $locale['blog_0429'], 'width' => '250px'));
            echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
            echo form_datepicker('blog_end', $locale['blog_0428'], $callback_data['blog_end'], array('placeholder' => $locale['blog_0429'], 'width' => '250px'));
            echo "</div>\n</div>\n";
            openside('');
            if ($callback_data['blog_image'] != "" && $callback_data['blog_image_t1'] != "") {
                echo "<div class='row'>\n";
                echo "<div class='col-xs-12 col-sm-6'>\n";
                $image_thumb = get_blog_image_path($callback_data['blog_image'], $callback_data['blog_image_t1'], $callback_data['blog_image_t2']);
                //echo "<label><img class='img-responsive img-thumbnail' src='".$image_thumb."' alt='".$locale['blog_0216']."' /><br />\n";
                echo "<label>".thumbnail($image_thumb, '100px');
                echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['delete']."</label>\n";
                echo "</div>\n";
                echo "<div class='col-xs-12 col-sm-6'>\n";
                $alignOptions = array(
                    'pull-left' => $locale['left'],
                    'blog-img-center' => $locale['center'],
                    'pull-right' => $locale['right']
                );
                echo form_select('blog_ialign', $locale['blog_0442'], $callback_data['blog_ialign'], array(
                    "options" => $alignOptions,
                    "inline" => FALSE
                ));
                echo "</div>\n</div>\n";
                echo "<input type='hidden' name='blog_image' value='".$callback_data['blog_image']."' />\n";
                echo "<input type='hidden' name='blog_image_t1' value='".$callback_data['blog_image_t1']."' />\n";
                echo "<input type='hidden' name='blog_image_t2' value='".$callback_data['blog_image_t2']."' />\n";
            } else {
                $file_input_options = array(
                    'upload_path' => IMAGES_B,
                    'max_width' => $blog_settings['blog_photo_max_w'],
                    'max_height' => $blog_settings['blog_photo_max_h'],
                    'max_byte' => $blog_settings['blog_photo_max_b'], // set thumbnail
                    'thumbnail' => 1,
                    'thumbnail_w' => $blog_settings['blog_thumb_w'],
                    'thumbnail_h' => $blog_settings['blog_thumb_h'],
                    'thumbnail_folder' => 'thumbs',
                    'delete_original' => 0, // set thumbnail 2 settings
                    'thumbnail2' => 1,
                    'thumbnail2_w' => $blog_settings['blog_photo_w'],
                    'thumbnail2_h' => $blog_settings['blog_photo_h'],
                    'type' => 'image'
                );
                echo form_fileinput("blog_image", $locale['blog_0439'], "", $file_input_options);
                echo "<div class='small m-b-10'>".sprintf($locale['blog_0440'], parsebytesize($blog_settings['blog_photo_max_b']))."</div>\n";
                $alignOptions = array(
                    'pull-left' => $locale['left'],
                    'news-img-center' => $locale['center'],
                    'pull-right' => $locale['right']
                );
                echo form_select('blog_ialign', $locale['blog_0442'], $callback_data['blog_ialign'], array("options" => $alignOptions));
            }
            closeside();
            echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
            openside("");
            echo form_select_tree("blog_cat", $locale['blog_0423'], $callback_data['blog_cat'], array(
                "width" => "100%",
                "inline" => TRUE,
                "parent_value" => $locale['blog_0424'],
                "query" => (multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")
            ), DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
            echo form_select('blog_visibility', $locale['blog_0430'], $callback_data['blog_visibility'], array(
                'options' => fusion_get_groups(),
                'placeholder' => $locale['choose'],
                'width' => '100%',
                "inline" => TRUE,
            ));
            if (multilang_table("NS")) {
                echo form_select('blog_language', $locale['global_ML100'], $callback_data['blog_language'], array(
                    'options' => fusion_get_enabled_languages(),
                    'placeholder' => $locale['choose'],
                    'width' => '100%',
                    "inline" => TRUE,
                ));
            } else {
                echo form_hidden('blog_language', '', $callback_data['blog_language']);
            }
            echo form_hidden('blog_datestamp', '', $callback_data['blog_datestamp']);
            echo form_button('preview', $locale['blog_0141'], $locale['blog_0141'], array('class' => 'btn-default btn-sm m-r-10', 'icon' => 'fa fa-eye'));
            echo form_button('publish', $locale['blog_0134'], $locale['blog_0134'], array('class' => 'btn-success btn-sm', 'icon' => 'fa fa-hdd-o'));
            closeside();
            openside("");
            echo "<label><input type='checkbox' name='blog_draft' value='1' ".($callback_data['blog_draft'] ? "checked='checked'" : "")."/> ".$locale['blog_0431']."</label><br />\n";
            echo "<label><input type='checkbox' name='blog_sticky' value='1' ".($callback_data['blog_sticky'] ? "checked='checked'" : "")."/> ".$locale['blog_0432']."</label><br />\n";
            if (fusion_get_settings("tinymce_enabled") != 1) {
                echo "<label><input type='checkbox' name='blog_breaks' value='1' ".($callback_data['blog_breaks'] ? "checked='checked'" : "")."/> ".$locale['blog_0433']."</label><br />\n";
            }
            closeside();
            echo "</div></div>\n";
            $snippetSettings = array(
                "required" => TRUE,
                "preview" => TRUE,
                "html" => TRUE,
                "autosize" => TRUE,
                "placeholder" => $locale['blog_0425a'],
                "form_name" => "inputform"
            );
            if (fusion_get_settings("tinymce_enabled")) {
                $snippetSettings = array("required" => TRUE);
            }
            echo form_textarea('blog_blog', $locale['blog_0425'], $callback_data['blog_blog'], $snippetSettings);

            $extendedSettings = array();
            if (!fusion_get_settings("tinymce_enabled")) {
                $extendedSettings = array(
                    "preview" => TRUE,
                    "html" => TRUE,
                    "autosize" => TRUE,
                    "placeholder" => $locale['blog_0426b'],
                    "form_name" => "inputform"
                );
            }
            echo form_textarea('blog_extended', $locale['blog_0426'], $callback_data['blog_extended'], $extendedSettings);
            echo form_button('preview', $locale['blog_0141'], $locale['blog_0141'], array('class' => 'btn-default m-r-10', 'icon' => 'fa fa-eye'));
            echo form_button('publish', $locale['blog_0134'], $locale['blog_0134'], array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o'));
            echo form_button('delete', $locale['blog_0135'], $locale['blog_0135'], array('class' => 'btn-danger', 'icon' => 'fa fa-trash'));
            echo closeform();
        }
    }
} else {
    $result = dbquery("SELECT
            ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='b' order by submit_datestamp desc
            ");
    $rows = dbrows($result);
    if ($rows > 0) {
        echo "<div class='well'>".sprintf($locale['blog_0137'], format_word($rows, $locale['fmt_submission']))."</div>\n";
        echo "<div class='table-responsive'><table class='table table-striped'>\n";
            echo "<tr>\n";
                echo "<th>".$locale['blog_0144']."</th>\n";
                echo "<th>".$locale['blog_0142']."</th>\n";
                echo "<th>".$locale['blog_0143']."</th>\n";
                echo "<th>".$locale['blog_0136']."</th>\n";
            echo "</tr>\n";
            echo "<tbody>\n";
            while ($data = dbarray($result)) {
                $submit_criteria = unserialize($data['submit_criteria']);
                echo "<tr>\n";
                    echo "<td>".$data['submit_id']."</td>\n";
                    echo "<td>".display_avatar($data, '20px', '', TRUE, 'img-rounded m-r-5').profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
                    echo "<td>".timer($data['submit_datestamp'])."</td>\n";
                    echo "<td><a href='".clean_request("submit_id=".$data['submit_id'], array(
                            "section",
                            "aid"
                        ), TRUE)."'>".$submit_criteria['blog_subject']."</a></td>\n";
                echo "</tr>\n";
            }
            echo "</tbody>\n";
        echo "</table>\n</div>";
    } else {
        echo "<div class='well text-center m-t-20'>".$locale['blog_0130']."</div>\n";
    }
}

echo '</div>';
