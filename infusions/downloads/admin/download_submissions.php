<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/download_submissions.php
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

$locale = fusion_get_locale();

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
    if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
        $result = dbquery("SELECT ts.*, tu.user_id, tu.user_name FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_id='".$_GET['submit_id']."'");
        if (dbrows($result)) {
            $callback_data = dbarray($result);
            $callback_data = array(
                "download_id" => 0,
                "download_user" => $callback_data['submit_user'],
                "download_title" => form_sanitizer($_POST['download_title'], '', 'download_title'),
                "download_description" => form_sanitizer($_POST['download_description'], '', 'download_description'),
                "download_description_short" => form_sanitizer($_POST['download_description_short'], '', 'download_description_short'),
                "download_cat" => form_sanitizer($_POST['download_cat'], 0, 'download_cat'),
                "download_homepage" => form_sanitizer($_POST['download_homepage'], '', 'download_homepage'),
                "download_license" => form_sanitizer($_POST['download_license'], '', 'download_license'),
                "download_copyright" => form_sanitizer($_POST['download_copyright'], '', 'download_copyright'),
                "download_os" => form_sanitizer($_POST['download_os'], '', 'download_os'),
                "download_version" => form_sanitizer($_POST['download_version'], '', 'download_version'),
                "download_file" => form_sanitizer($_POST['download_file'], '', 'download_file'),
                "download_url" => form_sanitizer($_POST['download_url'], '', 'download_url'),
                "download_filesize" => form_sanitizer($_POST['download_filesize'], '', 'download_filesize'),
                "download_image" => form_sanitizer($_POST['download_image'], '', 'download_image'),
                "download_image_thumb" => form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb'),
                "download_allow_comments" => isset($_POST['download_allow_comments']) ? TRUE : FALSE,
                "download_allow_ratings" => isset($_POST['download_allow_ratings']) ? TRUE : FALSE,
                "download_visibility" => form_sanitizer($_POST['download_visibility'], '', 'download_visibility'),
                "download_keywords" => form_sanitizer($_POST['download_keywords'], '', 'download_keywords'),
                "download_datestamp" => $callback_data['submit_datestamp'],
            );
            if (defender::safe()) {
                // move files
                if (!empty($callback_data['download_file']) && file_exists(DOWNLOADS."/submissions/".$callback_data['download_file'])) {
                    $dest = DOWNLOADS."files/";
                    $temp_file = $callback_data['download_file'];
                    $callback_data['download_file'] = filename_exists($dest, $callback_data['download_file']);
                    copy(DOWNLOADS."submissions/".$temp_file, $dest.$callback_data['download_file']);
                    chmod($dest.$callback_data['download_file'], 0644);
                    unlink(DOWNLOADS."submissions/".$temp_file);
                }
                // move images
                if (!empty($callback_data['download_image']) && file_exists(DOWNLOADS."/submissions/images/".$callback_data['download_image'])) {
                    $dest = DOWNLOADS."images/";
                    $temp_file = $callback_data['download_image'];
                    $callback_data['download_image'] = filename_exists($dest, $callback_data['download_image']);
                    copy(DOWNLOADS."submissions/images/".$temp_file, $dest.$callback_data['download_image']);
                    chmod($dest.$callback_data['download_image'], 0644);
                    unlink(DOWNLOADS."submissions/images/".$temp_file);
                }
                // move thumbnail
                if (!empty($callback_data['download_image_thumb']) && file_exists(DOWNLOADS."/submissions/images/".$callback_data['download_image_thumb'])) {
                    $dest = DOWNLOADS."images/";
                    $temp_file = $callback_data['download_image_thumb'];
                    $callback_data['download_image_thumb'] = filename_exists($dest, $callback_data['download_image_thumb']);
                    copy(DOWNLOADS."submissions/images/".$temp_file, $dest.$callback_data['download_image_thumb']);
                    chmod($dest.$callback_data['download_image_thumb'], 0644);
                    unlink(DOWNLOADS."submissions/images/".$temp_file);
                }
                dbquery_insert(DB_DOWNLOADS, $callback_data, "save");
                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
                addNotice("success", $locale['download_0063']);
                redirect(clean_request("", array("submit_id"), FALSE));
            }
        } else {
            redirect(clean_request("", array("submit_id"), FALSE));
        }
    } else {
        if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
            $result = dbquery("
            SELECT ts.submit_id, ts.submit_datestamp, ts.submit_criteria
            FROM ".DB_SUBMISSIONS." ts
            WHERE submit_type='d' and submit_id='".intval($_GET['submit_id'])."'
        ");
            if (dbrows($result) > 0) {
                $callback_data = dbarray($result);
                // delete all the relevant files
                $delCriteria = unserialize($callback_data['submit_criteria']);
                if (!empty($delCriteria['download_image']) && file_exists(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image'])) {
                    unlink(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image']);
                }
                if (!empty($delCriteria['download_image_thumb']) && file_exists(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image_thumb'])) {
                    unlink(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_image_thumb']);
                }
                if (!empty($delCriteria['download_file']) && file_exists(INFUSIONS."downloads/submisisons/images/".$delCriteria['download_file'])) {
                    unlink(INFUSIONS."downloads/submisisons/".$delCriteria['download_file']);
                }
                $result = dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($callback_data['submit_id'])."'");
                addNotice("success", $locale['download_0062']);
            }
            redirect(clean_request("", array("submit_id"), FALSE));
        } else {
            $result = dbquery("SELECT ts.submit_id,
            ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='d' AND submit_id='".$_GET['submit_id']."'
            ");
            if (dbrows($result) > 0) {
                $data = dbarray($result);
                $submit_criteria = unserialize($data['submit_criteria']);
                $callback_data = array(
                    "download_title" => $submit_criteria['download_title'],
                    "download_keywords" => $submit_criteria['download_keywords'],
                    "download_description" => $submit_criteria['download_description'],
                    "download_description_short" => $submit_criteria['download_description_short'],
                    "download_cat" => $submit_criteria['download_cat'],
                    "download_homepage" => $submit_criteria['download_homepage'],
                    "download_license" => $submit_criteria['download_license'],
                    "download_copyright" => $submit_criteria['download_copyright'],
                    "download_os" => $submit_criteria['download_os'],
                    "download_version" => $submit_criteria['download_version'],
                    "download_file" => $submit_criteria['download_file'],
                    "download_url" => $submit_criteria['download_url'],
                    "download_filesize" => ($submit_criteria['download_file']) ? $submit_criteria['download_filesize'] : 0,
                    "download_image" => $submit_criteria['download_image'],
                    "download_image_thumb" => $submit_criteria['download_image_thumb'],
                    // default to none
                    "download_id" => 0,
                    "download_allow_comments" => TRUE,
                    "download_allow_ratings" => TRUE,
                    "download_visibility" => iGUEST,
                    "download_datestamp" => $data['submit_datestamp'],
                );
                add_to_title($locale['global_200'].$locale['global_201'].$callback_data['download_title']."?");
                echo openform("publish_download", "post", FUSION_REQUEST);
                echo "<div class='well clearfix'>\n";
                echo "<div class='pull-left'>\n";
                echo display_avatar($callback_data, "30px", "", FALSE, "img-rounded m-r-5");
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo $locale['download_0056'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
                echo $locale['download_0057'].timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
                echo "</div>\n";
                echo "</div>\n";
                echo "<div class='row'>\n";
                echo "<div class='col-xs-12 col-sm-8'>\n";
                openside('');
                echo form_hidden('submit_id', '', $data['submit_id']);
                echo form_hidden('download_datestamp', '', $callback_data['download_datestamp']);
                echo form_text('download_title', $locale['download_0200'], $callback_data['download_title'], array(
                    'required' => TRUE,
                    "inline" => TRUE,
                    'error_text' => $locale['download_0110']
                ));
                echo form_select('download_keywords', $locale['download_0203'], $callback_data['download_keywords'], array(
                    "placeholder" => $locale['download_0203a'],
                    'max_length' => 320,
                    "inline" => TRUE,
                    'width' => '100%',
                    'tags' => 1,
                    'multiple' => 1
                ));
                echo form_textarea('download_description_short', $locale['download_0202'], $callback_data['download_description_short'], array(
                    'required' => TRUE,
                    "inline" => TRUE,
                    'error_text' => $locale['download_0112'],
                    'maxlength' => '255',
                    'autosize' => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
                ));
                closeside();
                echo "<div class='well'>\n";
                echo $locale['download_0204'];
                echo "</div>\n";
                echo form_textarea('download_description', $locale['download_0202a'], $callback_data['download_description'], array(
                    "no_resize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
                    "form_name" => "inputform",
                    "html" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
                    "autosize" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
                    "preview" => fusion_get_settings("tinymce_enabled") ? FALSE : TRUE,
                    "placeholder" => $locale['download_0201']
                ));
                echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
                // start package
                echo "<div class='well clearfix'>\n";
                if ($dl_settings['download_screenshot'] && !empty($callback_data['download_image']) && !empty($callback_data['download_image_thumb'])) {
                    echo "<div class='pull-left m-r-10'>\n";
                    echo thumbnail(DOWNLOADS."submissions/images/".$callback_data['download_image_thumb'], '80px');
                    echo form_hidden('download_image', '', $callback_data['download_image']);
                    echo form_hidden('download_image_thumb', '', $callback_data['download_image_thumb']);
                    echo "</div>\n";
                }
                echo "<div class='overflow-hide p-l-10'>\n";
                if (!empty($callback_data['download_file'])) {
                    echo "<p><strong>".$locale['download_0214']."</strong></p>\n";
                    echo "<a class='btn btn-default' href='".DOWNLOADS."submissions/".$callback_data['download_file']."'>
                ".$locale['download_0226']."</a>\n";
                    echo form_hidden('download_file', '', $callback_data['download_file']);
                    echo form_hidden("download_url", "", "");
                } else {
                    echo "<p><strong>".$locale['download_0215']."</strong></p>\n";
                    echo form_text('download_url', '', $callback_data['download_url']);
                    echo form_hidden("download_file", "", "");
                }
                echo "</div>\n";
                echo "</div>\n";
                // end package
                openside();
                if (fusion_get_settings('comments_enabled') == "0" || fusion_get_settings('ratings_enabled') == "0") {
                    $sys = "";
                    if (fusion_get_settings('comments_enabled') == "0" && fusion_get_settings('ratings_enabled') == "0") {
                        $sys = $locale['comments_ratings'];
                    } elseif (fusion_get_settings('comments_enabled') == "0") {
                        $sys = $locale['comments'];
                    } else {
                        $sys = $locale['ratings'];
                    }
                    echo "<div class='well'>".sprintf($locale['download_0256'], $sys)."</div>\n";
                }
                echo form_select_tree("download_cat", $locale['download_0207'], $callback_data['download_cat'], array(
                    "no_root" => 1,
                    "placeholder" => $locale['choose'],
                    'width' => '100%',
                    "query" => (multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")
                ), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
                echo form_select('download_visibility', $locale['download_0205'], $callback_data['download_visibility'], array(
                    'options' => fusion_get_groups(),
                    'placeholder' => $locale['choose'],
                    'width' => '100%'
                ));
                echo form_button('publish', $locale['download_0061'], $locale['download_0061'], array('class' => 'btn-success btn-sm', 'icon' => 'fa fa-hdd-o'));
                closeside();
                openside('');
                echo form_checkbox('download_allow_comments', $locale['download_0223'], $callback_data['download_allow_comments'],
                                   array('class' => 'm-b-0'));
                echo form_checkbox('download_allow_ratings', $locale['download_0224'], $callback_data['download_allow_ratings'],
                                   array('class' => 'm-b-0'));
                if (isset($_GET['action']) && $_GET['action'] == "edit") {
                    echo form_checkbox('update_datestamp', $locale['download_0213'], '', array('class' => 'm-b-0'));
                }
                closeside();
                openside();
                echo form_text('download_license', $locale['download_0208'], $callback_data['download_license'], array('inline' => 1));
                echo form_text('download_copyright', $locale['download_0222'], $callback_data['download_copyright'], array('inline' => 1));
                echo form_text('download_os', $locale['download_0209'], $callback_data['download_os'], array('inline' => 1));
                echo form_text('download_version', $locale['download_0210'], $callback_data['download_version'], array('inline' => 1));
                echo form_text('download_homepage', $locale['download_0221'], $callback_data['download_homepage'], array('inline' => 1));
                echo form_text('download_filesize', $locale['download_0211'], $callback_data['download_filesize'], array('inline' => 1));
                closeside();
                echo "</div>\n</div>\n"; // end row.
                echo form_button('publish', $locale['download_0061'], $locale['download_0061'], array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o'));
                echo form_button('delete', $locale['download_0060'], $locale['download_0060'], array('class' => 'btn-danger', 'icon' => 'fa fa-trash'));
                echo closeform();
            }
        }
    }
} else {
    $result = dbquery("SELECT
            ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='d'
            ORDER BY submit_datestamp DESC
            ");
    $rows = dbrows($result);
    if ($rows > 0) {
        echo "<div class='well'>".sprintf($locale['download_0051'], format_word($rows, $locale['fmt_submission']))."</div>\n";
        echo "<div class='table-responsive'><table class='table table-striped'>\n";
            echo "<tr>\n";
                echo "<th>".$locale['download_0055']."</th>\n";
                echo "<th>".$locale['download_0053']."</th>\n";
                echo "<th>".$locale['download_0054']."</th>\n";
                echo "<th>".$locale['download_0052']."</th>\n";
            echo "</tr>\n";
            echo "<tbody>\n";
            while ($callback_data = dbarray($result)) {
                $submit_criteria = unserialize($callback_data['submit_criteria']);
                echo "<tr>\n";
                echo "<td>".$callback_data['submit_id']."</td>\n";
                echo "<td>".display_avatar($callback_data, '20px', '', TRUE, 'img-rounded m-r-5').profile_link($callback_data['user_id'], $callback_data['user_name'], $callback_data['user_status'])."</td>\n";
                echo "<td>".timer($callback_data['submit_datestamp'])."</td>\n";
                echo "<td><a href='".clean_request("submit_id=".$callback_data['submit_id'], array(
                        "section",
                        "aid"
                    ), TRUE)."'>".$submit_criteria['download_title']."</a></td>\n";
                echo "</tr>\n";
            }
            echo "</tbody>\n";
        echo "</table>\n</div>";
    } else {
        echo "<div class='well text-center m-t-20'>".$locale['download_0050']."</div>\n";
    }
}
