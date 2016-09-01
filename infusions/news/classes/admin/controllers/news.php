<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news.php
| Author: PHP-Fusion Development Team
| Version: 9.25 Build 3
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
    private $locale = array();
    private $form_action = FUSION_REQUEST;

    private $news_data = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function displayNewsAdmin() {
        pageAccess("N");
        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        $this->locale = self::get_newsAdminLocale();

        if (isset($_GET['ref']) && $_GET['ref'] == "news_form") {
            $this->display_news_form();
        } else {
            $this->display_news_listing();
        }
    }

    /**
     * Displays News Form
     */
    private function display_news_form() {

        self::execute_NewsUpdate();
        /**
         * Global vars
         */
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
            $result = dbquery("SELECT * FROM ".DB_NEWS." WHERE news_id='".(isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])."'");
            if (dbrows($result)) {
                $this->news_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        $this->news_data['news_breaks'] = (fusion_get_settings("tinymce_enabled") ? 'n' : 'y');
        $this->news_data += $this->default_news_data;
        self::newsContent_form();
    }

    private function execute_NewsUpdate() {
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

            $this->news_data = array(
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
                'news_draft' => form_sanitizer($_POST['news_draft'], 0, 'news_draft'),
                'news_sticky' => isset($_POST['news_sticky']) ? "1" : "0",
                'news_allow_comments' => isset($_POST['news_allow_comments']) ? "1" : "0",
                'news_allow_ratings' => isset($_POST['news_allow_ratings']) ? "1" : "0",
                'news_language' => form_sanitizer($_POST['news_language'], '', 'news_language'),
            );

            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->news_data['news_breaks'] = isset($_POST['news_breaks']) ? "y" : "n";
            } else {
                $this->news_data['news_breaks'] = "n";
            }

            if (\defender::safe()) {
                // reset other sticky
                if ($this->news_data['news_sticky'] == 1) {
                    dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
                }
                // update news gallery default if exist
                if (!empty($_POST['news_full_default']) && isnum($_POST['news_full_default'])) {
                    dbquery("UPDATE ".DB_NEWS_IMAGES." SET news_full_default=0 WHERE news_id='".$this->news_data['news_id']."'");
                    dbquery("UPDATE ".DB_NEWS_IMAGES." SET news_full_default=1 WHERE news_image_id='".intval($_POST['news_full_default'])."'");
                }
                if (!empty($_POST['news_front_default'])) {
                    dbquery("UPDATE ".DB_NEWS_IMAGES." SET news_front_default=0 WHERE news_id='".$this->news_data['news_id']."'");
                    dbquery("UPDATE ".DB_NEWS_IMAGES." SET news_front_default=1 WHERE news_image_id='".intval($_POST['news_front_default'])."'");
                }

                if (dbcount("('news_id')", DB_NEWS, "news_id='".$this->news_data['news_id']."'")) {
                    dbquery_insert(DB_NEWS, $this->news_data, 'update');
                    addNotice('success', $this->locale['news_0101']);
                } else {
                    $this->data['news_name'] = fusion_get_userdata('user_id');
                    dbquery_insert(DB_NEWS, $this->news_data, 'save');
                    addNotice('success', $this->locale['news_0100']);
                }
                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request("", array("ref"), FALSE));
                } else {
                    redirect(FUSION_REQUEST);
                }
            }
        }
    }

    private function newsContent_form() {

        $result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
        $news_cat_opts = array();
        $news_cat_opts['0'] = $this->locale['news_0202'];
        if (dbrows($result)) {
            while ($odata = dbarray($result)) {
                $news_cat_opts[$odata['news_cat_id']] = $odata['news_cat_name'];
            }
        }

        $snippetSettings = array(
            "required" => TRUE,
            "preview" => TRUE,
            "html" => TRUE,
            "autosize" => TRUE,
            "placeholder" => $this->locale['news_0203a'],
            "form_name" => "news_form"
        );
        if (fusion_get_settings("tinymce_enabled")) {
            $snippetSettings = array("required" => TRUE, "type" => "tinymce", "tinymce" => "advanced");
        }

        if (!fusion_get_settings("tinymce_enabled")) {
            $extendedSettings = array(
                "preview" => TRUE,
                "html" => TRUE,
                "autosize" => TRUE,
                "placeholder" => $this->locale['news_0203b'],
                "form_name" => "news_form"
            );
        } else {
            $extendedSettings = array("type" => "tinymce", "tinymce" => "advanced");
        }
        echo openform('news_form', 'post', $this->form_action);
        self::display_newsButtons('newsContent');
        echo form_hidden('news_id', "", $this->news_data['news_id']);
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_text('news_subject', $this->locale['news_0200'], $this->news_data['news_subject'],
                               array(
                                   'required' => 1,
                                   'max_length' => 200,
                                   'error_text' => $this->locale['news_0280'],
                                   'class' => 'form-group-lg'
                               )
                );
                echo form_textarea('news_news', $this->locale['news_0203'], $this->news_data['news_news'], $snippetSettings).
                    form_textarea('news_extended', $this->locale['news_0204'], $this->news_data['news_extended'], $extendedSettings);
                ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php
                openside($this->locale['news_0255']);
                echo form_select('news_draft', $this->locale['news_0253'], $this->news_data['news_draft'],
                                 array(
                                     'inline' => TRUE,
                                     'width' => '100%',
                                     'options' => array(
                                         1 => $this->locale['draft'],
                                         0 => $this->locale['publish']
                                     )
                                 )
                    ).
                    form_select_tree("news_cat", $this->locale['news_0201'], $this->news_data['news_cat'],
                                     array(
                                         "width" => "100%",
                                         "inline" => TRUE,
                                         "parent_value" => $this->locale['news_0202'],
                                         "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
                                     ),
                                     DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent"
                    ).
                    form_select('news_visibility', $this->locale['news_0209'], $this->news_data['news_visibility'],
                                array(
                                    'options' => fusion_get_groups(),
                                    'placeholder' => $this->locale['choose'],
                                    'width' => '100%',
                                    "inline" => TRUE,
                                )
                    );

                if (multilang_table("NS")) {
                    echo form_select('news_language', $this->locale['language'], $this->news_data['news_language'], array(
                        'options' => fusion_get_enabled_languages(),
                        'placeholder' => $this->locale['choose'],
                        'width' => '100%',
                        "inline" => TRUE,
                    ));
                } else {
                    echo form_hidden('news_language', '', $this->news_data['news_language']);
                }

                echo form_datepicker('news_datestamp', $this->locale['news_0266'], $this->news_data['news_datestamp'], array('inline' => TRUE));
                closeside();

                $this->newsGallery();

                openside('');
                ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-6">
                        <?php
                        echo form_datepicker('news_start', $this->locale['news_0206'], $this->news_data['news_start'],
                                             array(
                                                 'placeholder' => $this->locale['news_0208'],
                                                 "join_to_id" => "news_end",
                                                 'width' => '100%'
                                             )
                        );
                        ?>
                    </div>
                    <div class='col-xs-12 col-sm-6'>
                        <?php
                        echo form_datepicker('news_end', $this->locale['news_0207'], $this->news_data['news_end'],
                                             array(
                                                 'placeholder' => $this->locale['news_0208'],
                                                 "join_from_id" => "news_start",
                                                 'width' => '100%'
                                             )
                        );
                        ?>
                    </div>
                </div>
                <?php
                closeside();

                openside('');
                echo form_checkbox('news_sticky', $this->locale['news_0211'], $this->news_data['news_sticky'],
                                   array(
                                       'class' => 'm-b-5',
                                       'reverse_label' => TRUE
                                   )
                );
                if (fusion_get_settings("tinymce_enabled") != 1) {
                    echo form_checkbox('news_breaks', $this->locale['news_0212'], $this->news_data['news_breaks'],
                                       array(
                                           'value' => 'y',
                                           'class' => 'm-b-5',
                                           'reverse_label' => TRUE
                                       )
                    );
                }
                echo form_checkbox('news_allow_comments', $this->locale['news_0213'], $this->news_data['news_allow_comments'],
                                   array(
                                       'reverse_label' => TRUE,
                                       'class' => 'm-b-5',
                                       'ext_tip' => (!fusion_get_settings("comments_enabled") ? "<div class='alert alert-warning'>".sprintf($this->locale['news_0283'],
                                                                                                                                            $this->locale['comments'])."</div>" : "")
                                   )
                    ).form_checkbox('news_allow_ratings', $this->locale['news_0214'], $this->news_data['news_allow_ratings'],
                                    array(
                                        'reverse_label' => TRUE,
                                        'class' => 'm-b-5',
                                        'ext_tip' => (!fusion_get_settings("comments_enabled") ? "<div class='alert alert-warning'>".sprintf($this->locale['news_0283'],
                                                                                                                                             $this->locale['ratings'])."</div>" : "")
                                    )
                    );
                closeside();

                openside($this->locale['news_0205']);
                echo form_select('news_keywords', '', $this->news_data['news_keywords'],
                                 array(
                                     "max_length" => 320,
                                     "placeholder" => $this->locale['news_0205a'],
                                     "width" => "100%",
                                     "error_text" => $this->locale['news_0285'],
                                     "tags" => TRUE,
                                     "multiple" => TRUE
                                 )
                );
                closeside();
                ?>
            </div>
        </div>
        <?php
        self::display_newsButtons('content2');
        echo closeform();
    }

    /**
     * Generate sets of push buttons for news Content form
     * @param $unique_id
     */
    private function display_newsButtons($unique_id) {
        echo "<div class='m-t-20'>\n";
        echo form_button('cancel', $this->locale['cancel'], $this->locale['cancel'],
                         array('class' => 'btn-default m-r-10', 'input_id' => 'cancel-'.$unique_id));
        echo form_button('save', $this->locale['news_0241'], $this->locale['news_0241'],
                         array('class' => 'btn-success', 'input_id' => 'save-'.$unique_id));
        echo form_button("save_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'],
                         array("class" => "btn-primary m-l-10", 'input_id' => 'save_and_close-'.$unique_id));
        echo "</div>";
        echo "<hr/>";
    }

    /**
     * Gallery Features
     */
    private function newsGallery() {

        $news_settings = self::get_news_settings();

        $default_fileinput_options = array(
            'upload_path' => IMAGES_N,
            'max_width' => $news_settings['news_photo_max_w'],
            'max_height' => $news_settings['news_photo_max_h'],
            'max_byte' => $news_settings['news_photo_max_b'],
            'thumbnail' => TRUE,
            'thumbnail_w' => $news_settings['news_thumb_w'],
            'thumbnail_h' => $news_settings['news_thumb_h'],
            'thumbnail_folder' => 'thumbs',
            'delete_original' => 0,
            'thumbnail2' => TRUE,
            'thumbnail2_w' => $news_settings['news_photo_w'],
            'thumbnail2_h' => $news_settings['news_photo_h'],
            'type' => 'image',
            'template' => 'modern',
            'class' => 'm-b-0'
        );

        $alignOptions = array(
            'pull-left' => $this->locale['left'],
            'news-img-center' => $this->locale['center'],
            'pull-right' => $this->locale['right']
        );

        /**
         * Post Save
         */

        if (!empty($_FILES['news_image'])) { // when files is uploaded.
            $upload = form_sanitizer($_FILES['news_image'], '', 'news_image');
            if (!empty($upload) && !$upload['error']) {
                $data = array(
                    'news_image_user' => fusion_get_userdata('user_id'),
                    'news_id' => $this->news_data['news_id'],
                    'news_full_default' => '0',
                    'news_front_default' => '0',
                    'news_image_align' => form_sanitizer($_POST['news_image_align'], '', 'news_image_align'),
                    'news_image_datestamp' => TIME,
                    'news_image' => $upload['image_name'],
                    'news_image_t1' => $upload['thumb1_name'],
                    'news_image_t2' => $upload['thumb2_name']
                );
                dbquery_insert(DB_NEWS_IMAGES, $data, 'save');
                addNotice('success', $this->locale['news_0103']);
                redirect(FUSION_REQUEST);
            }
        }

        if (isset($_POST['delete_photo']) && isnum($_POST['delete_photo'])) {
            $photo_id = intval($_POST['delete_photo']);
            $photo_query = "SELECT news_image_id, news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS_IMAGES." WHERE news_image_id='".$photo_id."'";
            $photo_result = dbquery($photo_query);
            if (dbrows($photo_result)) {
                $data = dbarray($photo_result);
                if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
                    unlink(IMAGES_N.$data['news_image']);
                }
                if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                    unlink(IMAGES_N_T.$data['news_image_t1']);
                }
                if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                    unlink(IMAGES_N_T.$data['news_image_t2']);
                }
                dbquery_insert(DB_NEWS_IMAGES, $data, 'delete');
                addNotice('success', $this->locale['news_0104']);
                redirect(FUSION_REQUEST);
            }
        }


        $photo_query = "SELECT * FROM ".DB_NEWS_IMAGES." WHERE news_id='".$this->news_data['news_id']."'";
        $photo_result = dbquery($photo_query);
        $news_photos = array();
        $news_photo_opts = array();
        $default_photo_id = 0;
        $default_full_photo_id = 0;
        if (dbrows($photo_result) > 0) {
            while ($photo_data = dbarray($photo_result)) {
                $news_photos[$photo_data['news_image_id']] = $photo_data;
                $news_photo_opts[$photo_data['news_image_id']] = $photo_data['news_image'];
                if ($photo_data['news_front_default']) {
                    $default_photo_id = $photo_data['news_image_id'];
                }
                if ($photo_data['news_full_default']) {
                    $default_full_photo_id = $photo_data['news_image_id'];
                }

            }
        }


        openside($this->locale['news_0006']);

        echo form_button('image_gallery', $this->locale['news_0007'], 'image_gallery',
                         array('type' => 'button', 'class' => 'btn-default', 'deactivate' => $this->news_data['news_id'] ? FALSE : TRUE));

        if (!empty($news_photo_opts)) :
            ?>
            <hr/>
            <?php
            echo form_select('news_front_default', $this->locale['news_0011'], $default_full_photo_id,
                             array(
                                 'inline' => TRUE,
                                 'width' => '100%',
                                 'options' => $news_photo_opts
                             )
                ).
                form_select('news_full_default', $this->locale['news_0012'], $default_photo_id,
                            array(
                                'inline' => TRUE,
                                'width' => '100%',
                                'options' => $news_photo_opts
                            )
                );
        endif;
        closeside();

        ob_start();
        openside($this->locale['news_0006']);
        echo openmodal('image_gallery_modal', $this->locale['news_0006'], array('button_id' => 'image_gallery'));
        echo openform('gallery_form', 'POST', FUSION_REQUEST, array('enctype' => TRUE));

        // Two tabs
        $modal_tab['title'][] = $this->locale['news_0008'];
        $modal_tab['id'][] = 'news_upload_tab';
        $modal_tab['title'][] = $this->locale['news_0009'];
        $modal_tab['id'][] = 'news_media_tab';
        $modal_tab_active = tab_active($modal_tab, 0);
        echo opentab($modal_tab, $modal_tab_active, 'newsModalTab');
        echo opentabbody($modal_tab['title'][0], $modal_tab['id'][0], $modal_tab_active);
        ?>
        <div class="p-20">
            <div class="well">
                <div class="row">
                    <?php
                    echo form_fileinput('news_image', '', '', $default_fileinput_options);
                    ?>
                    <div class="col-xs-12 col-sm-6 col-md-7 col-lg-8">
                        <?php echo form_select('news_image_align', $this->locale['news_0218'], '',
                                               array("options" => $alignOptions, 'inline' => TRUE)); ?>
                    </div>
                </div>
                <?php echo sprintf($this->locale['news_0217'], parsebytesize($news_settings['news_photo_max_b'])); ?>
            </div>
            <?php echo form_button('upload_photo', $this->locale['news_0008'], 'upload', array('class' => 'btn-primary btn-lg')) ?>
        </div>
        <?php
        echo closetabbody();
        echo opentabbody($modal_tab['title'][1], $modal_tab['id'][1], $modal_tab_active);
        ?>
        <div class="p-20">
            <div class="row">
                <?php
                if (!empty($news_photos)) :
                    foreach ($news_photos as $photo_id => $photo_data) :
                        $image_path = self::get_news_image_path($photo_data['news_image'], $photo_data['news_image_t1'],
                                                                $photo_data['news_image_t2']);
                        ?>
                        <div class="pull-left m-r-10 m-l-10 text-center">
                            <div class="file-input">
                                <div class="panel panel-default">
                                    <div class="file-preview">
                                        <div class="file-preview-frame overflow-hide">
                                            <?php echo colorbox($image_path, $image_path); ?>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="padding: 3px 5px 15px">
                                        <p><?php echo trimlink($photo_data['news_image'], 15) ?></p>
                                        <?php echo form_button('delete_photo', $this->locale['news_0010'], $photo_data['news_image_id'],
                                                               array(
                                                                   'input_id' => 'delete_photo_'.$photo_data['news_image_id'],
                                                                   'icon' => 'fa fa-trash'
                                                               )
                                        ) ?>
                                    </div>
                                    <div class="panel-footer text-left text-lighter">
                                        <?php echo timer($photo_data['news_image_datestamp']) ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php
                    endforeach;
                else:
                    ?>
                    <div class="well text-center"><?php echo $this->locale['news_0267'] ?></div>
                    <?php
                endif; ?>
            </div>
        </div>

        <?php
        echo closetabbody();
        echo closetab();
        closeside();
        echo closeform();
        echo closemodal();
        $html = ob_get_contents();
        ob_end_clean();
        add_to_footer($html);
    }

    /**
     * Displays News Listing
     */
    private function display_news_listing() {

        self::execute_NewsDelete();
        // Run functions
        $allowed_actions = array_flip(array("publish", "unpublish", "sticky", "unsticky", "delete", "news_display"));

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
                                $result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS_IMAGES." WHERE news_id='".intval($news_id)."'");
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
                                dbquery("DELETE FROM  ".DB_NEWS_IMAGES." WHERE news_id='".intval($news_id)."'");
                                dbquery("DELETE FROM  ".DB_NEWS." WHERE news_id='".intval($news_id)."'");
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice("success", $this->locale['news_0101']);
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", $this->locale['news_0108']);
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

        $default_display = 16;
        $limit = $default_display;
        if ((!empty($_POST['news_display']) && isnum($_POST['news_display'])) || (!empty($_GET['news_display']) && isnum($_GET['news_display']))) {
            $limit = (!empty($_POST['news_display']) ? $_POST['news_display'] : $_GET['news_display']);
        }

        $max_rows = dbcount("(news_id)", DB_NEWS);
        $rowstart = 0;
        if (!isset($_POST['news_display'])) {
            $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
        }

        $news_query = "SELECT n.*, nc.*, IF(nc.news_cat_name !='', nc.news_cat_name, '".$this->locale['news_0202']."') 'news_cat_name',
                    count('c.comment_id') 'comments_count',
                    count(ni.news_image_id) 'image_count',
                    u.user_id, u.user_name, u.user_status, u.user_avatar
                    FROM ".DB_NEWS." n
                    LEFT JOIN ".DB_NEWS_CATS." nc ON nc.news_cat_id=n.news_cat
                    LEFT JOIN ".DB_COMMENTS." c ON c.comment_item_id=n.news_id AND c.comment_type='N'
                    LEFT JOIN ".DB_NEWS_IMAGES." ni USING (news_id)
                    INNER JOIN ".DB_USERS." u on u.user_id= n.news_name
                    WHERE ".(multilang_table("NS") ? "news_language='".LANGUAGE."'" : "")."
                    $sql_condition
                    GROUP BY n.news_id
                    ORDER BY news_draft DESC, news_sticky DESC, news_datestamp DESC
                    LIMIT $rowstart, $limit
                    ";

        $result2 = dbquery($news_query);
        $news_rows = dbrows($result2);
        ?>
        <div class="m-t-15">
            <?php
            echo openform("news_filter", "post", FUSION_REQUEST);
            echo "<div class='clearfix'>\n";

            echo "<div class='pull-right'>\n";
            echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request("ref=news_form", array("ref"),
                                                                                 FALSE)."'>".$this->locale['news_0002']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('publish');\"><i class='fa fa-check fa-fw'></i> ".$this->locale['publish']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unpublish');\"><i class='fa fa-ban fa-fw'></i> ".$this->locale['unpublish']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('sticky');\"><i class='fa fa-sticky-note fa-fw'></i> ".$this->locale['sticky']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unsticky');\"><i class='fa fa-sticky-note-o fa-fw'></i> ".$this->locale['unsticky']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o fa-fw'></i> ".$this->locale['delete']."</a>";
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
                "news_status" => !empty($_POST['news_status']) ? form_sanitizer($_POST['news_status'], "", "news_status") : "",
                "news_category" => !empty($_POST['news_category']) ? form_sanitizer($_POST['news_category'], "", "news_category") : "",
                "news_visibility" => !empty($_POST['news_visibility']) ? form_sanitizer($_POST['news_visibility'], "", "news_visibility") : "",
                "news_language" => !empty($_POST['news_language']) ? form_sanitizer($_POST['news_language'], "", "news_language") : "",
                "news_author" => !empty($_POST['news_author']) ? form_sanitizer($_POST['news_author'], "", "news_author") : "",
            );

            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                }
            }

            echo "<div class='display-inline-block pull-left m-r-10' style='width:300px;'>\n";
            echo form_text("news_text", "", $filter_values['news_text'], array(
                "placeholder" => $this->locale['news_0200'],
                "append_button" => TRUE,
                "append_value" => "<i class='fa fa-search'></i>",
                "append_form_value" => "search_news",
                "width" => "250px"
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block' style='vertical-align:top;'>\n";
            echo "<a class='btn btn-sm ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>".$this->locale['news_0242']."
            <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
            echo form_button("news_clear", $this->locale['news_0243'], "clear");
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
            $('#news_status, #news_visibility, #news_category, #news_language, #news_author, #news_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
            ");
            unset($filter_values['news_text']);

            echo "<div id='news_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("news_status", "", $filter_values['news_status'], array(
                "allowclear" => TRUE, "placeholder" => "- ".$this->locale['news_0244']." -", "options" => array(
                    0 => $this->locale['news_0245'],
                    1 => $this->locale['draft'],
                    2 => $this->locale['sticky'],
                )
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";
            echo form_select("news_visibility", "", $filter_values['news_visibility'], array(
                "allowclear" => TRUE, "placeholder" => "- ".$this->locale['news_0246']." -", "options" => fusion_get_groups()
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";

            $news_cats_opts = array(0 => $this->locale['news_0247']);
            $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ORDER BY news_cat_name ASC");
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $news_cats_opts[$data['news_cat_id']] = $data['news_cat_name'];
                }
            }
            echo form_select("news_category", "", $filter_values['news_category'], array(
                "allowclear" => TRUE, "placeholder" => "- ".$this->locale['news_0248']." -", "options" => $news_cats_opts
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $language_opts = array(0 => $this->locale['news_0249']);
            $language_opts += fusion_get_enabled_languages();
            echo form_select("news_language", "", $filter_values['news_language'], array(
                "allowclear" => TRUE, "placeholder" => "- ".$this->locale['news_0250']." -", "options" => $language_opts
            ));

            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";

            $author_opts = array(0 => $this->locale['news_0251']);
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
                             array(
                                 "allowclear" => TRUE,
                                 "placeholder" => "- ".$this->locale['news_0252']." -",
                                 "options" => $author_opts
                             )
            );
            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
            ?>
        </div>

        <?php echo openform("news_table", "post", FUSION_REQUEST); ?>
        <?php echo form_hidden("table_action", "", ""); ?>

        <div class="display-block">
            <div class="display-inline-block m-l-10">
                <?php echo form_select('news_display', $this->locale['show'], $limit,
                                       array(
                                           'width' => '100px',
                                           'inline' => TRUE,
                                           'options' => array(
                                               5 => 5,
                                               10 => 10,
                                               16 => 16,
                                               25 => 25,
                                               50 => 50,
                                               100 => 100
                                           ),
                                       )
                ); ?>
            </div>
            <?php if ($max_rows > $news_rows) : ?>
                <div class="display-inline-block pull-right">
                    <?php
                    echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.fusion_get_aidlink()."&news_display=$limit&amp;")
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <table class="table table-responsive table-striped">
            <thead>
            <tr>
                <td></td>
                <td class="strong col-xs-4"><?php echo $this->locale['news_0200'] ?></td>
                <td class="strong"><?php echo $this->locale['news_0201'] ?></td>
                <td class="strong"><?php echo $this->locale['news_0209'] ?></td>
                <td class="strong"><?php echo $this->locale['sticky'] ?></td>
                <td class="strong"><?php echo $this->locale['draft'] ?></td>
                <td class="strong"><?php echo $this->locale['global_073'] ?></td>
                <td class="strong"><?php echo $this->locale['news_0216'] ?></td>
                <td class="strong"><?php echo $this->locale['news_0142'] ?></td>
                <td class="strong"><?php echo $this->locale['actions'] ?></td>
                <td class="strong">ID</td>
            </tr>
            </thead>
            <tbody>
            <?php if (dbrows($result2) > 0) :
                while ($data = dbarray($result2)) : ?>
                    <?php
                    $edit_link = FUSION_SELF.fusion_get_aidlink()."&amp;action=edit&amp;ref=news_form&amp;news_id=".$data['news_id'];
                    $cat_edit_link = FUSION_SELF.fusion_get_aidlink()."&amp;action=edit&amp;ref=news_category&amp;cat_id=".$data['news_cat_id'];
                    ?>
                    <tr>
                        <td><?php echo form_checkbox("news_id[]", "", "", array("value" => $data['news_id'], "class" => 'm-0')) ?></td>
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
                            <span class="badge"><?php echo $data['news_sticky'] ? $this->locale['yes'] : $this->locale['no'] ?></span>
                        </td>
                        <td>
                            <span class="badge"><?php echo $data['news_draft'] ? $this->locale['yes'] : $this->locale['no'] ?></span>
                        </td>
                        <td><?php echo format_word($data['comments_count'], $this->locale['fmt_comment']) ?></td>
                        <td><?php echo format_word($data['image_count'], $this->locale['fmt_photo']) ?></td>
                        <td>
                            <div class="pull-left"><?php echo display_avatar($data, "20px", "", FALSE, "img-rounded") ?></div>
                            <div class="overflow-hide"><?php echo profile_link($data['user_id'], $data['user_name'],
                                                                               $data['user_status']) ?></div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" href="<?php echo $edit_link ?>">
                                    <?php echo $this->locale['edit'] ?>
                                </a>
                                <a class="btn btn-xs btn-default"
                                   href="<?php echo FUSION_SELF.fusion_get_aidlink()."&amp;action=delete&amp;news_id=".$data['news_id'] ?>"
                                   onclick="return confirm('<?php echo $this->locale['news_0281']; ?>')">
                                    <?php echo $this->locale['delete'] ?>
                                </a>
                            </div>

                        </td>
                        <td><?php echo $data['news_id'] ?></td>
                    </tr>
                    <?php
                endwhile;
            else: ?>
                <tr>
                    <td colspan="11" class="text-center"><strong><?php echo $this->locale['news_0109'] ?></strong></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        closeform();
    }

    // News Delete Function
    private function execute_NewsDelete() {

        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['news_id']) && isnum($_GET['news_id'])) {

            $news_id = intval($_GET['news_id']);

            if (dbcount("(news_id)", DB_NEWS, "news_id='$news_id'")) {

                $result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS_IMAGES." WHERE news_id='".intval($_GET['news_id'])."'");
                if (dbrows($result)) {
                    $data = dbarray($result);
                    if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
                        unlink(IMAGES_N.$data['news_image']);
                    }
                    if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                        unlink(IMAGES_N_T.$data['news_image_t1']);
                    }
                    if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                        unlink(IMAGES_N_T.$data['news_image_t2']);
                    }
                }

                dbquery("DELETE FROM ".DB_NEWS_IMAGES." WHERE news_id='$news_id'");
                dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='$news_id'");
                dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='$news_id' and comment_type='N'");
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$news_id' and rating_type='N'");
                dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='$news_id'");

                addNotice('success', $this->locale['news_0102']);
                redirect(FUSION_SELF.fusion_get_aidlink());
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
    }


}