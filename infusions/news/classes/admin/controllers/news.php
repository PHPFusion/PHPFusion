<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news.php
| Author: PHP-Fusion Development Team
| Version: 9.2 prototype
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\News;

class NewsAdmin extends NewsAdminModel {

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayNewsAdmin() {
        pageAccess("N");
        if (isset($_GET['ref']) && $_GET['ref'] == "news_form") {
            $this->display_news_form();
        } else {
            $this->display_news_listing();
        }
    }

    /**
     * Displays News Form
     */
    public function display_news_form() {

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        $formaction = FUSION_REQUEST;

        $locale = fusion_get_locale();

        $userdata = fusion_get_userdata();

        $news_settings = fusion_get_settings("news");

        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['news_id']) && isnum($_GET['news_id'])) ? TRUE : FALSE;

        add_breadcrumb(array('link' => '', 'title' => $edit ? $locale['news_0003'] : $locale['news_0002']));

        $data = array(
            'news_id' => 0,
            'news_draft' => 0,
            'news_sticky' => 0,
            'news_news' => '',
            'news_datestamp' => time(),
            'news_extended' => '',
            'news_keywords' => '',
            'news_breaks' => 'n',
            'news_allow_comments' => 1,
            'news_allow_ratings' => 1,
            'news_language' => LANGUAGE,
            'news_visibility' => 0,
            'news_subject' => '',
            'news_start' => '',
            'news_end' => '',
            'news_cat' => 0,
            'news_image' => '',
            'news_ialign' => 'pull-left',
        );

        if (fusion_get_settings("tinymce_enabled")) {
            $data['news_breaks'] = 'n';
        } else {
            $data['news_breaks'] = 'y';
        }

        if ((isset($_POST['save'])) or (isset($_POST['save_and_close']))) {

            $news_news = "";
            if ($_POST['news_news']) {
                $news_news = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N,
                    (fusion_get_settings('allow_php_exe') ? htmlspecialchars($_POST['news_news']) : stripslashes($_POST['news_news'])));
                $news_news = parse_textarea($news_news);
            }

            $news_extended = "";
            if ($_POST['news_extended']) {
                $news_extended = str_replace("src='".str_replace("../", "", IMAGES_N), "src='".IMAGES_N,
                    (fusion_get_settings('allow_php_exe') ? htmlspecialchars($_POST['news_extended']) : stripslashes($_POST['news_extended'])));
                $news_extended = parse_textarea($news_extended);
            }

            $data = array(
                'news_id' => form_sanitizer($_POST['news_id'], 0, 'news_id'),
                'news_subject' => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
                'news_cat' => form_sanitizer($_POST['news_cat'], 0, 'news_cat'),
                'news_news' => form_sanitizer($news_news, "", "news_news"),
                'news_extended' => form_sanitizer($news_extended, "", "news_extended"),
                'news_keywords' => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
                'news_datestamp' => form_sanitizer($_POST['news_datestamp'], '', 'news_datestamp'),
                'news_start' => form_sanitizer($_POST['news_start'], 0, 'news_start'),
                'news_end' => form_sanitizer($_POST['news_end'], 0, 'news_end'),
                'news_visibility' => form_sanitizer($_POST['news_visibility'], 0, 'news_visibility'),
                'news_draft' => isset($_POST['news_draft']) ? "1" : "0",
                'news_sticky' => isset($_POST['news_sticky']) ? "1" : "0",
                'news_allow_comments' => isset($_POST['news_allow_comments']) ? "1" : "0",
                'news_allow_ratings' => isset($_POST['news_allow_ratings']) ? "1" : "0",
                'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language'),
                'news_image' => "",
                'news_ialign' => "",
                'news_image_t1' => "",
                'news_image_t2' => "",
            );

            if (isset($_FILES['news_image'])) { // when files is uploaded.

                $upload = form_sanitizer($_FILES['news_image'], '', 'news_image');

                if (!empty($upload) && !$upload['error']) {
                    $data['news_image'] = $upload['image_name'];
                    $data['news_image_t1'] = $upload['thumb1_name'];
                    $data['news_image_t2'] = $upload['thumb2_name'];
                    $data['news_ialign'] = (isset($_POST['news_ialign']) ? form_sanitizer($_POST['news_ialign'],
                                                                                          "pull-left",
                                                                                          "news_ialign") : "pull-left");
                }
            } else { // when files not uploaded. but there should be exist check.
                $data['news_image'] = (isset($_POST['news_image']) ? $_POST['news_image'] : "");
                $data['news_image_t1'] = (isset($_POST['news_image_t1']) ? $_POST['news_image_t1'] : "");
                $data['news_image_t2'] = (isset($_POST['news_image_t2']) ? $_POST['news_image_t2'] : "");
                $data['news_ialign'] = (isset($_POST['news_ialign']) ? form_sanitizer($_POST['news_ialign'], "pull-left",
                                                                                      "news_ialign") : "pull-left");
            }

            if (fusion_get_settings('tinymce_enabled') != 1) {
                $data['news_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
            } else {
                $data['news_breaks'] = "n";
            }

            if ($data['news_sticky'] == "1") {
                $result = dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
            } // reset other sticky

            // delete image
            if (isset($_POST['del_image'])) {
                if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
                    unlink(IMAGES_N.$data['news_image']);
                }
                if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                    unlink(IMAGES_N_T.$data['news_image_t1']);
                }
                if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                    unlink(IMAGES_N_T.$data['news_image_t2']);
                }
                $data['news_image'] = "";
                $data['news_image_t1'] = "";
                $data['news_image_t2'] = "";
            }

            if (\defender::safe()) {

                if (dbcount("('news_id')", DB_NEWS, "news_id='".$data['news_id']."'")) {

                    dbquery_insert(DB_NEWS, $data, 'update');

                    addNotice('success', $locale['news_0101']);

                } else {

                    $data['news_name'] = $userdata['user_id'];

                    dbquery_insert(DB_NEWS, $data, 'save');

                    addNotice('success', $locale['news_0100']);

                }

                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request("", array("ref"), FALSE));
                } else {
                    redirect(FUSION_REQUEST);
                }

            }
        } elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
            $result = dbquery("SELECT * FROM ".DB_NEWS." WHERE news_id='".(isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])."'");
            if (dbrows($result)) {
                $data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
        $result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
        $news_cat_opts = array();
        $news_cat_opts['0'] = $locale['news_0202'];
        if (dbrows($result)) {
            while ($odata = dbarray($result)) {
                $news_cat_opts[$odata['news_cat_id']] = $odata['news_cat_name'];
            }
        }

        echo "<div class='m-t-20'>\n";
        $news_settings = get_settings("news");
        echo openform('news_form', 'post', $formaction, array('enctype' => 1));
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
        echo form_hidden('news_id', "", $data['news_id']);
        echo form_text('news_subject', $locale['news_0200'], $data['news_subject'], array(
            'required' => 1,
            'max_length' => 200,
            'error_text' => $locale['news_0250']
        ));
        echo form_select('news_keywords', $locale['news_0205'], $data['news_keywords'], array(
            "max_length" => 320,
            "placeholder" => $locale['news_0205a'],
            "width" => "100%",
            "error_text" => $locale['news_0255'],
            "tags" => TRUE,
            "multiple" => TRUE
        ));
        echo "<div class='pull-left m-r-10 display-inline-block'>\n";
        echo form_datepicker('news_start', $locale['news_0206'], $data['news_start'],
                             array(
                                 'placeholder' => $locale['news_0208'],
                                 "join_to_id" => "news_end"
                             )
        );

        echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
        echo form_datepicker('news_end', $locale['news_0207'], $data['news_end'],
                             array(
                                 'placeholder' => $locale['news_0208'],
                                 "join_from_id" => "news_start"
                             )
        );

        echo "</div>\n";
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
        openside('');
        echo form_select_tree("news_cat", $locale['news_0201'], $data['news_cat'], array(
            "width" => "100%",
            "inline" => TRUE,
            "parent_value" => $locale['news_0202'],
            "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
        ), DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");
        echo form_select('news_visibility', $locale['news_0209'], $data['news_visibility'], array(
            'options' => fusion_get_groups(),
            'placeholder' => $locale['choose'],
            'width' => '100%',
            "inline" => TRUE,
        ));
        if (multilang_table("NS")) {
            echo form_select('news_language', $locale['global_ML100'], $data['news_language'], array(
                'options' => fusion_get_enabled_languages(),
                'placeholder' => $locale['choose'],
                'width' => '100%',
                "inline" => TRUE,
            ));
        } else {
            echo form_hidden('news_language', '', $data['news_language']);
        }
        echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10'));
        echo form_button('save', $locale['news_0241'], $locale['news_0241'], array('class' => 'btn-success'));
        echo form_button("save_and_close", "Save and Close", "save_and_close", array("class" => "btn-primary m-l-10"));
        closeside();

        echo "</div>\n</div>\n";
        $snippetSettings = array(
            "required" => TRUE,
            "preview" => TRUE,
            "html" => TRUE,
            "autosize" => TRUE,
            "placeholder" => $locale['news_0203a'],
            "form_name" => "news_form"
        );
        if (fusion_get_settings("tinymce_enabled")) {
            $snippetSettings = array("required" => TRUE, "type" => "tinymce", "tinymce" => "advanced");
        }
        echo form_textarea('news_news', $locale['news_0203'], $data['news_news'], $snippetSettings);

        if (!fusion_get_settings("tinymce_enabled")) {
            $extendedSettings = array(
                "preview" => TRUE,
                "html" => TRUE,
                "autosize" => TRUE,
                "placeholder" => $locale['news_0203b'],
                "form_name" => "news_form"
            );
        } else {
            $extendedSettings = array("type" => "tinymce", "tinymce" => "advanced");
        }
        echo form_textarea('news_extended', $locale['news_0204'], $data['news_extended'], $extendedSettings);
// second row
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
        openside('');
        if ($data['news_image'] != "" && $data['news_image_t1'] != "") {
            $image_thumb = get_news_image_path($data['news_image'], $data['news_image_t1'], $data['news_image_t2']);
            if (!$image_thumb) {
                $image_thumb = IMAGES."imagenotfound70.jpg";
            }
            echo "<div class='row'>\n";
            echo "<div class='col-xs-12 col-sm-6'>\n";
            echo "<label><img class='img-responsive img-thumbnail' src='".$image_thumb."' alt='".$locale['news_0216']."' /><br />\n";
            echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['delete']."</label>\n";
            echo "</div>\n";
            echo "<div class='col-xs-12 col-sm-6'>\n";
            $alignOptions = array(
                'pull-left' => $locale['left'],
                'news-img-center' => $locale['center'],
                'pull-right' => $locale['right']
            );
            echo form_select('news_ialign', $locale['news_0218'], $data['news_ialign'], array(
                "options" => $alignOptions,
                "inline" => FALSE
            ));
            echo "</div>\n</div>\n";
            echo "<input type='hidden' name='news_image' value='".$data['news_image']."' />\n";
            echo "<input type='hidden' name='news_image_t1' value='".$data['news_image_t1']."' />\n";
            echo "<input type='hidden' name='news_image_t2' value='".$data['news_image_t2']."' />\n";
        } else {

            $file_input_options = array(
                'upload_path' => IMAGES_N,
                'max_width' => $news_settings['news_photo_max_w'],
                'max_height' => $news_settings['news_photo_max_h'],
                'max_byte' => $news_settings['news_photo_max_b'],
                // set thumbnail
                'thumbnail' => 1,
                'thumbnail_w' => $news_settings['news_thumb_w'],
                'thumbnail_h' => $news_settings['news_thumb_h'],
                'thumbnail_folder' => 'thumbs',
                'delete_original' => 0,
                // set thumbnail 2 settings
                'thumbnail2' => 1,
                'thumbnail2_w' => $news_settings['news_photo_w'],
                'thumbnail2_h' => $news_settings['news_photo_h'],
                'type' => 'image'
            );
            echo form_fileinput("news_image", $locale['news_0216'], "", $file_input_options);

            echo "<div class='small m-b-10'>".sprintf($locale['news_0217'],
                                                      parsebytesize($news_settings['news_photo_max_b']))."</div>\n";
            $alignOptions = array(
                'pull-left' => $locale['left'],
                'news-img-center' => $locale['center'],
                'pull-right' => $locale['right']
            );
            echo form_select('news_ialign', $locale['news_0218'], $data['news_ialign'], array("options" => $alignOptions));
        }
        closeside();
        openside('');
        echo "<label><input type='checkbox' name='news_draft' value='yes'".($data['news_draft'] ? "checked='checked'" : "")." /> ".$locale['news_0210']."</label><br />\n";
        echo "<label><input type='checkbox' name='news_sticky' value='yes'".($data['news_sticky'] ? "checked='checked'" : "")."  /> ".$locale['news_0211']."</label><br />\n";
        echo form_hidden('news_datestamp', '', $data['news_datestamp']);
        if (fusion_get_settings("tinymce_enabled") != 1) {
            echo "<label><input type='checkbox' name='line_breaks' value='yes'".($data['news_breaks'] ? "checked='checked'" : "")." /> ".$locale['news_0212']."</label><br />\n";
        }
        closeside();
        echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
        openside("");
        if (!fusion_get_settings("comments_enabled") || !fusion_get_settings("ratings_enabled")) {
            $sys = "";
            if (!fusion_get_settings("comments_enabled") && !fusion_get_settings("ratings_enabled")) {
                $sys = $locale['comments_ratings'];
            } elseif (!fusion_get_settings("comments_enabled")) {
                $sys = $locale['comments'];
            } else {
                $sys = $locale['ratings'];
            }
            echo "<div class='alert alert-warning'>".sprintf($locale['news_0253'], $sys)."</div>\n";
        }
        echo "<label><input type='checkbox' name='news_allow_comments' value='yes' onclick='SetRatings();'".($data['news_allow_comments'] ? "checked='checked'" : "")." /> ".$locale['news_0213']."</label><br/>";
        echo "<label><input type='checkbox' name='news_allow_ratings' value='yes'".($data['news_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['news_0214']."</label>";
        closeside();
        echo "</div>\n</div>\n";
        echo form_button('preview', $locale['news_0240'], $locale['news_0240'], array('class' => 'btn-default m-r-10'));
        echo form_button('save', $locale['news_0241'], $locale['news_0241'], array('class' => 'btn-success'));
        echo form_button("save_and_close", "Save and Close", "save_and_close", array("class" => "btn-primary m-l-10"));
        echo closeform();
        echo "</div>\n";
    }

    /**
     * Displays News Listing
     */
    private function display_news_listing() {

        $locale = self::get_newsAdminLocale();

        // Run functions
        $allowed_actions = array_flip(array("publish", "unpublish", "sticky", "unsticky", "delete"));

        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = (isset($_POST['news_id'])) ? explode(",", form_sanitizer($_POST['news_id'], "", "news_id")) : "";

            if (!empty($input)) {
                foreach ($input as $news_id) {
                    // check input table
                    if (dbcount("('news_id')", DB_NEWS, "news_id='".intval($news_id)."'") && \defender::safe()) {

                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_NEWS." SET news_draft='0' WHERE news_id='".intval($news_id)."'");
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_NEWS." SET news_draft='1' WHERE news_id='".intval($news_id)."'");
                                break;
                            case "sticky":
                                dbquery("UPDATE ".DB_NEWS." SET news_sticky='1' WHERE news_id='".intval($news_id)."'");
                                break;
                            case "unsticky":
                                dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_id='".intval($news_id)."'");
                                break;
                            case "delete":

                                $result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".intval($news_id)."'");
                                if (dbrows($result) > 0) {
                                    $photo = dbarray($result);
                                    if (!empty($photo['news_image']) && file_exists(IMAGES_N.$photo['news_image'])) {
                                        unlink(IMAGES_N.$photo['news_image']);
                                    }
                                    if (!empty($photo['news_image_t1']) && file_exists(IMAGES_N_T.$photo['news_image_t1'])) {
                                        unlink(IMAGES_N_T.$photo['news_image_t1']);
                                    }
                                    if (!empty($photo['news_image_t2']) && file_exists(IMAGES_N_T.$photo['news_image_t2'])) {
                                        unlink(IMAGES_N_T.$photo['news_image_t2']);
                                    }
                                    if (!empty($photo['news_image_t2']) && file_exists(IMAGES_N.$photo['news_image_t2'])) {
                                        unlink(IMAGES_N.$photo['news_image_t2']);
                                    }
                                }

                                dbquery("DELETE FROM  ".DB_NEWS." WHERE news_id='".intval($news_id)."'");
                                break;
                            default:
                                addNotice("warning", "News ID $news_id is not valid and update aborted");
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice("success", "News listing has been updated");
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", "No news item selected. Please check a news item and try again");
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['news_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Switch to post
        $sql_condition = "";
        $search_string = array();
        if (isset($_POST['p-submit-news_text'])) {
            $search_string['news_subject'] = array(
                "input" => form_sanitizer($_POST['news_text'], "", "news_text"), "operator" => "LIKE"
            );
        }

        if (!empty($_POST['news_status']) && isnum($_POST['news_status'])) {
            switch ($_POST['news_status']) {
                case 1: // is a draft
                    $search_string['news_draft'] = array("input" => 1, "operator" => "=");
                    break;
                case 2: // is a sticky
                    $search_string['news_sticky'] = array("input" => 1, "operator" => "=");
                    break;
            }
        }

        if (!empty($_POST['news_visibility'])) {
            $search_string['news_visibility'] = array(
                "input" => form_sanitizer($_POST['news_visibility'], "", "news_visibility"), "operator" => "="
            );
        }

        if (!empty($_POST['news_category'])) {
            $search_string['news_cat_id'] = array(
                "input" => form_sanitizer($_POST['news_category'], "", "news_category"), "operator" => "="
            );
        }

        if (!empty($_POST['news_language'])) {
            $search_string['news_language'] = array(
                "input" => form_sanitizer($_POST['news_language'], "", "news_language"), "operator" => "="
            );
        }

        if (!empty($_POST['news_author'])) {
            $search_string['news_name'] = array(
                "input" => form_sanitizer($_POST['news_author'], "", "news_author"), "operator" => "="
            );
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                $sql_condition .= " AND `$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        $result2 = dbquery("
	SELECT n.*, nc.*, IF(nc.news_cat_name !='', nc.news_cat_name, 'Uncategorized') 'news_cat_name',
	count('c.comment_id') 'comments_count',
	count('r.rating_id') 'ratings_count',
	u.user_id, u.user_name, u.user_status, u.user_avatar
	FROM ".DB_NEWS." n
	LEFT JOIN ".DB_NEWS_CATS." nc on nc.news_cat_id=n.news_cat
	LEFT JOIN ".DB_COMMENTS." c on c.comment_item_id= n.news_id AND c.comment_type='N'
	LEFT JOIN ".DB_RATINGS." r on r.rating_item_id= n.news_id AND r.rating_type='N'
	INNER JOIN ".DB_USERS." u on u.user_id= n.news_name
	WHERE ".(multilang_table("NS") ? "news_language='".LANGUAGE."'" : "")."
	$sql_condition
	GROUP BY n.news_id
	ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC
	");

        ?>

        <div class="m-t-15">
            <?php

            echo openform("news_filter", "post", FUSION_REQUEST);
            echo "<div class='clearfix'>\n";

            echo "<div class='pull-right'>\n";

            echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request("ref=news_form", array("ref"),
                                                                                 FALSE)."'>Add New</a>";

            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('publish');\"><i class='fa fa-check fa-fw'></i> ".$locale['publish']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unpublish');\"><i class='fa fa-ban fa-fw'></i> ".$locale['unpublish']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('sticky');\"><i class='fa fa-sticky-note fa-fw'></i> ".$locale['sticky']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unsticky');\"><i class='fa fa-sticky-note-o fa-fw'></i> ".$locale['unsticky']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o fa-fw'></i> ".$locale['delete']."</a>";
            echo "</div>\n";

            ?>
            <script>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#news_table').submit();
                }
            </script>

            <?php


            $filter_values = array(
                "news_text" => !empty($_POST['news_text']) ? form_sanitizer($_POST['news_text'], "", "news_text") : "",
                "news_status" => !empty($_POST['news_status']) ? form_sanitizer($_POST['news_status'], "",
                                                                                "news_status") : "",
                "news_category" => !empty($_POST['news_category']) ? form_sanitizer($_POST['news_category'], "",
                                                                                    "news_category") : "",
                "news_visibility" => !empty($_POST['news_visibility']) ? form_sanitizer($_POST['news_visibility'], "",
                                                                                        "news_visibility") : "",
                "news_language" => !empty($_POST['news_language']) ? form_sanitizer($_POST['news_language'], "",
                                                                                    "news_language") : "",
                "news_author" => !empty($_POST['news_author']) ? form_sanitizer($_POST['news_author'], "",
                                                                                "news_author") : "",
            );

            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                }
            }


            echo "<div class='display-inline-block pull-left m-r-10' style='width:300px;'>\n";
            echo form_text("news_text", "", $filter_values['news_text'], array(
                "placeholder" => "News Subject",
                "append_button" => TRUE,
                "append_value" => "<i class='fa fa-search'></i>",
                "append_form_value" => "search_news",
                "width" => "250px"
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>";
            echo "<a class='btn btn-sm ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>Search Options
        <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
            echo form_button("news_clear", "Clear", "clear");
            echo "</div>\n";
            echo "</div>\n";

            add_to_jquery("
        $('#toggle_options').bind('click', function(e) {
            $('#news_filter_options').slideToggle();
            var caret_status = $('#filter_caret').hasClass('fa-caret-down');
            if (caret_status == 1) {
                $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                $(this).removeClass('btn-default').addClass('btn-info');
            } else {
                $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                $(this).removeClass('btn-info').addClass('btn-default');
            }
        });

        // Select change
        $('#news_status, #news_visibility, #news_category, #news_language, #news_author').bind('change', function(e){
            $(this).closest('form').submit();
        });
        ");
            unset($filter_values['news_text']);

            echo "<div id='news_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("news_status", "", $filter_values['news_status'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Status -", "options" => array(
                    0 => "All Status",
                    1 => "Draft",
                    2 => "Sticky",
                )
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";
            echo form_select("news_visibility", "", $filter_values['news_visibility'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Access -", "options" => fusion_get_groups()
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";

            $news_cats_opts = array(0 => "All Categories");
            $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ORDER BY news_cat_name ASC");
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $news_cats_opts[$data['news_cat_id']] = $data['news_cat_name'];
                }
            }
            echo form_select("news_category", "", $filter_values['news_category'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Category -", "options" => $news_cats_opts
            ));

            echo "</div>\n";


            echo "<div class='display-inline-block'>\n";

            $language_opts = array(0 => "All Language");
            $language_opts += fusion_get_enabled_languages();
            echo form_select("news_language", "", $filter_values['news_language'], array(
                "allowclear" => TRUE, "placeholder" => "- Select Language -", "options" => $language_opts
            ));

            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";

            $author_opts = array(0 => "All Author");
            $result = dbquery("SELECT n.news_name, u.user_id, u.user_name, u.user_status
          FROM ".DB_NEWS." n
          LEFT JOIN ".DB_USERS." u on n.news_name = u.user_id
          GROUP BY u.user_id
          ORDER BY user_name ASC");
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $author_opts[$data['user_id']] = $data['user_name'];
                }
            }
            echo form_select("news_author", "", $filter_values['news_author'],
                             array("allowclear" => TRUE, "placeholder" => "- Select Author -", "options" => $author_opts));

            echo "</div>\n";

            echo "</div>\n";

            echo closeform();
            ?>
        </div>

        <?php echo openform("news_table", "post", FUSION_REQUEST); ?>
        <?php echo form_hidden("table_action", "", ""); ?>
        <table class="table table-responsive table-striped">
            <thead>
            <tr>
                <td></td>
                <td class="strong col-xs-4">News Subject</td>
                <td class="strong">News Category</td>
                <td class="strong">Access</td>
                <td class="strong">Sticky</td>
                <td class="strong">Draft</td>
                <td class="strong">Comments</td>
                <td class="strong">Ratings</td>
                <td class="strong">News Author</td>
                <td class="strong">Actions</td>
                <td class="strong">ID</td>
            </tr>
            </thead>
            <tbody>
            <?php if (dbrows($result2) > 0) :
                while ($data = dbarray($result2)) : ?>
                    <?php
                    $edit_link = FUSION_SELF.fusion_get_aidlink()."&amp;action=edit&amp;ref=news_form&amp;news_id=".$data['news_id'];
                    $cat_edit_link = FUSION_SELF.fusion_get_aidlink()."&amp;action=edit&amp;ref=news_category&amp;cat_id=".$data['news_cat_id'];
                    $image_thumb = $this->get_news_image_path($data['news_image'], $data['news_image_t1'], $data['news_image_t2']);
                    if (!$image_thumb) {
                        $image_thumb = IMAGES."imagenotfound70.jpg";
                    }
                    ?>
                    <tr>
                        <td><?php echo form_checkbox("news_id[]", "", "",
                                                     array("value" => $data['news_id'], "class" => 'm-0')) ?></td>
                        <td>
                            <a class="text-dark" href="<?php echo $edit_link ?>">
                                <?php echo $data['news_subject'] ?>
                            </a>
                        </td>
                        <td>
                            <a class="text-dark" href="<?php echo $cat_edit_link ?>">
                                <?php echo $data['news_cat_name'] ?>
                            </a>
                        </td>
                        <td>
                            <?php echo getgroupname($data['news_visibility']) ?>
                        </td>
                        <td>
                            <span class="badge"><?php echo $data['news_sticky'] ? $locale['yes'] : $locale['no'] ?></span>
                        </td>
                        <td>
                            <span class="badge"><?php echo $data['news_draft'] ? $locale['yes'] : $locale['no'] ?></span>
                        </td>

                        <td><?php echo $data['comments_count'] ?></td>
                        <td><?php echo $data['ratings_count'] ?></td>
                        <td>
                            <div class="pull-left"><?php echo display_avatar($data, "20px", "", FALSE,
                                                                             "img-rounded") ?></div>
                            <div class="overflow-hide"><?php echo profile_link($data['user_id'], $data['user_name'],
                                                                               $data['user_status']) ?></div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" href="<?php echo $edit_link ?>">
                                    <?php echo $locale['edit'] ?>
                                </a>
                                <a class="btn btn-xs btn-default"
                                   href="<?php echo FUSION_SELF.fusion_get_aidlink()."&amp;action=delete&amp;news_id=".$data['news_id'] ?>"
                                   onclick="return confirm('<?php echo $locale['news_0251']; ?>')">
                                    <?php echo $locale['delete'] ?>
                                </a>
                            </div>

                        </td>
                        <td><?php echo $data['news_id'] ?></td>
                    </tr>
                    <?php
                endwhile;
            else: ?>
                <tr>
                    <td colspan="10" class="text-center strong"><?php echo $locale['news_0254'] ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        closeform();

    }

}