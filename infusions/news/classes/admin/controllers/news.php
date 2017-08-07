<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news.php
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

namespace PHPFusion\News;

class NewsAdmin extends NewsAdminModel {

    private static $instance = NULL;
    private static $locale = array();
    private $form_action = FUSION_REQUEST;
    private $news_data = array();

    public static function getInstance() {
        pageAccess('N');
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        self::$locale = self::get_newsAdminLocale();

        return self::$instance;
    }

    public function displayNewsAdmin() {
        if (isset($_POST['cancel'])) redirect(FUSION_SELF.fusion_get_aidlink());
        if (isset($_GET['ref']) && $_GET['ref'] == 'news_form') {
            $this->display_news_form();
        } else {
            $this->display_news_listing();
            $this->clear_unattached_image();
        }
    }

    /**
     * Displays News Form
     */
    public function display_news_form() {
        self::execute_NewsUpdate();
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
            $result = dbquery("SELECT * FROM ".DB_NEWS." WHERE news_id=:news_id", array(':news_id' => (isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'])));
            if (dbrows($result)) {
                $this->news_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        $this->default_news_data['news_name'] = fusion_get_userdata('user_id');
        $this->news_data['news_breaks'] = (fusion_get_settings("tinymce_enabled") ? 'n' : 'y');
        $this->news_data = $this->news_data + $this->default_news_data;
        self::newsContent_form();
    }

    private function execute_NewsUpdate() {

        if ((isset($_POST['save'])) or (isset($_POST['save_and_close'])) or (isset($_POST['preview'])) or (isset($_POST['del_photo']))) {

            $news_news = '';
            if ($_POST['news_news']) {
                $news_news = str_replace("src='".str_replace('../', '', IMAGES_N), "src='".IMAGES_N,
                    (fusion_get_settings('allow_php_exe') ? htmlspecialchars($_POST['news_news']) : $_POST['news_news']));
            }

            $news_extended = '';
            if ($_POST['news_extended']) {
                $news_extended = str_replace("src='".str_replace('../', '', IMAGES_N), "src='".IMAGES_N,
                    (fusion_get_settings('allow_php_exe') ? htmlspecialchars($_POST['news_extended']) : $_POST['news_extended']));
            }

            $this->news_data = array(
                'news_id'                  => form_sanitizer($_POST['news_id'], 0, 'news_id'),
                'news_subject'             => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
                'news_cat'                 => form_sanitizer($_POST['news_cat'], 0, 'news_cat'),
                'news_news'                => form_sanitizer($news_news, "", "news_news"),
                'news_extended'            => form_sanitizer($news_extended, "", "news_extended"),
                'news_keywords'            => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
                'news_datestamp'           => form_sanitizer($_POST['news_datestamp'], TIME, 'news_datestamp'),
                'news_start'               => form_sanitizer($_POST['news_start'], 0, 'news_start'),
                'news_end'                 => form_sanitizer($_POST['news_end'], 0, 'news_end'),
                'news_visibility'          => form_sanitizer($_POST['news_visibility'], 0, 'news_visibility'),
                'news_draft'               => form_sanitizer($_POST['news_draft'], 0, 'news_draft'),
                'news_sticky'              => isset($_POST['news_sticky']) ? "1" : "0",
                'news_name'                => form_sanitizer($_POST['news_name'], 0, 'news_name'),
                'news_allow_comments'      => isset($_POST['news_allow_comments']) ? "1" : "0",
                'news_allow_ratings'       => isset($_POST['news_allow_ratings']) ? "1" : "0",
                'news_language'            => form_sanitizer($_POST['news_language'], '', 'news_language'),
                'news_image_front_default' => 0,
                'news_image_align'         => form_sanitizer($_POST['news_image_align'], 'pull-left', 'news_image_align'),
            );

            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->news_data['news_breaks'] = isset($_POST['news_breaks']) ? "y" : "n";
            } else {
                $this->news_data['news_breaks'] = "n";
            }

            if (\defender::safe()) {

                if ($this->news_data['news_id']) {
                    // update news gallery default if exist
                    if (!empty($_POST['news_image_full_default'])) {
                        $this->news_data['news_image_full_default'] = form_sanitizer($_POST['news_image_full_default'], '', 'news_image_full_default');
                    }
                    if (!empty($_POST['news_image_front_default'])) {
                        $this->news_data['news_image_front_default'] = form_sanitizer($_POST['news_image_front_default'], '', 'news_image_front_default');
                    }
                    if (!empty($_POST['news_image_align'])) {
                        $this->news_data['news_image_align'] = form_sanitizer($_POST['news_image_align'], '', 'news_image_align');
                    }
                } else {

                    if (!empty($_FILES['featured_image'])) { // when files is uploaded.
                        $upload = form_sanitizer($_FILES['featured_image'], '', 'featured_image');
                        if (!empty($upload)) {
                            if (!$upload['error']) {
                                $data = array(
                                    'news_image_user'      => fusion_get_userdata('user_id'),
                                    'news_id'              => 0,
                                    'news_image'           => $upload['image_name'],
                                    'news_image_t1'        => $upload['thumb1_name'],
                                    'news_image_t2'        => $upload['thumb2_name'],
                                    'news_image_datestamp' => TIME
                                );
                                $photo_id = dbquery_insert(DB_NEWS_IMAGES, $data, 'save', ['keep_session' => TRUE]);
                                $this->news_data['news_image_full_default'] = $photo_id;
                                $this->news_data['news_image_front_default'] = $photo_id;
                            }
                        }
                    } else {
                        // load the photo
                        $photo_result = dbquery("SELECT news_image_id FROM ".DB_NEWS_IMAGES." WHERE news_id=0");
                        if (dbrows($photo_result)) {
                            $photo_data = dbarray($photo_result);
                            $this->news_data['news_image_full_default'] = $photo_data['news_image_id'];
                            $this->news_data['news_image_front_default'] = $photo_data['news_image_id'];
                        }
                    }
                    $this->news_data['news_image_align'] = form_sanitizer($_POST['news_image_align'], '', 'news_image_align');
                }

                if (isset($_POST['del_photo'])) {
                    $this->clear_unattached_image();
                } elseif (isset($_POST['preview'])) {
                    $preview = new News_Preview();
                    $preview->set_PreviewData($this->news_data);
                    $preview->display_preview();
                    if (isset($this->news_data['news_id'])) {
                        dbquery_insert(DB_NEWS, $this->news_data, 'update', ['keep_session' => TRUE]);
                    }
                } else {
                    // reset other sticky
                    if ($this->news_data['news_sticky'] == 1) {
                        dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
                    }
                    if (dbcount("('news_id')", DB_NEWS, "news_id='".$this->news_data['news_id']."'")) {
                        dbquery_insert(DB_NEWS, $this->news_data, 'update');
                        addNotice('success', self::$locale['news_0101']);
                    } else {
                        $this->data['news_name'] = fusion_get_userdata('user_id');
                        $this->news_data['news_id'] = dbquery_insert(DB_NEWS, $this->news_data, 'save');
                        // update the last uploaded image to the news.
                        $photo_result = dbquery("SELECT news_image_id FROM ".DB_NEWS_IMAGES." WHERE news_id=0 ORDER BY news_image_datestamp DESC LIMIT 1");
                        if (dbrows($photo_result)) {
                            $photo_data = dbarray($photo_result);
                            dbquery("UPDATE ".DB_NEWS_IMAGES." SET news_id=:news_id WHERE news_image_id=:news_image_id", [
                                ':news_image_id' => $photo_data['news_image_id'],
                                ':news_id'       => $this->news_data['news_id']
                            ]);
                        }
                        addNotice('success', self::$locale['news_0100']);
                    }
                    if (isset($_POST['save_and_close'])) {
                        redirect(clean_request("", array('ref', 'action', 'news_id'), FALSE));
                    } else {
                        redirect(clean_request('news_id='.$this->news_data['news_id'].'&action=edit&ref=news_form', array('ref'), FALSE));
                    }
                }
            }
        }
    }

    /**
     * Check any news image record with image id 0 and clear it.
     */
    private function clear_unattached_image() {
        if (dbcount("(news_image_id)", DB_NEWS_IMAGES, "news_id=0")) {
            $photo_result = dbquery("SELECT news_image_id, news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS_IMAGES." WHERE news_id=0");
            if (dbrows($photo_result)) {
                $photo_data = dbarray($photo_result);
                if (file_exists(IMAGES_N.$photo_data['news_image'])) unlink(IMAGES_N.$photo_data['news_image']);
                if (file_exists(IMAGES_N_T.$photo_data['news_image_t1'])) unlink(IMAGES_N_T.$photo_data['news_image_t1']);
                if (file_exists(IMAGES_N_T.$photo_data['news_image_t2'])) unlink(IMAGES_N_T.$photo_data['news_image_t2']);
                dbquery("DELETE FROM ".DB_NEWS_IMAGES." WHERE news_id=0 AND submit_id !=0");
            }
        }
    }

    private function newsContent_form() {
        $news_settings = self::get_news_settings();

        $news_cat_opts = [];
        $query = "SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : '')." ORDER BY news_cat_name";
        $result = dbquery($query);
        $news_cat_opts['0'] = self::$locale['news_0202'];
        if (dbrows($result)) {
            while ($odata = dbarray($result)) {
                $news_cat_opts[$odata['news_cat_id']] = $odata['news_cat_name'];
            }
        }

        $snippetSettings = array(
            'required'    => TRUE,
            'preview'     => TRUE,
            'html'        => TRUE,
            'path'        => [IMAGES, IMAGES_N, IMAGES_NC],
            'autosize'    => TRUE,
            'placeholder' => self::$locale['news_0203a'],
            'form_name'   => 'news_form',
            'wordcount'   => TRUE,
            'height'      => '200px',
            'file_filter' => explode(',', $news_settings['news_file_types']),
        );
        if (fusion_get_settings('tinymce_enabled')) {
            $snippetSettings = array('required' => TRUE, 'height' => '200px', 'type' => 'tinymce', 'tinymce' => 'advanced', 'file_filter' => explode(',', $news_settings['news_file_types']), 'path' => [IMAGES, IMAGES_N, IMAGES_NC]);
        }

        if (!fusion_get_settings('tinymce_enabled')) {
            $extendedSettings = array(
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => self::$locale['news_0005'],
                'form_name'   => 'news_form',
                'path'        => [IMAGES, IMAGES_N, IMAGES_NC],
                'wordcount'   => TRUE,
                'height'      => '300px',
                'file_filter' => explode(',', $news_settings['news_file_types']),
            );
        } else {
            $extendedSettings = array('type' => 'tinymce', 'tinymce' => 'advanced', 'height' => '300px', 'file_filter' => explode(',', $news_settings['news_file_types']), 'path' => [IMAGES, IMAGES_N, IMAGES_NC]);
        }
        echo openform('news_form', 'post', $this->form_action, ['enctype' => TRUE]);
        self::display_newsButtons('newsContent');
        echo form_hidden('news_id', "", $this->news_data['news_id']);
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_hidden('news_name', '', $this->news_data['news_name']);
                echo form_text('news_subject', self::$locale['news_0200'], $this->news_data['news_subject'],
                    array(
                        'required'   => 1,
                        'max_length' => 200,
                        'error_text' => self::$locale['news_0280'],
                        'class'      => 'form-group-lg'
                    )
                );
                echo form_textarea('news_news', self::$locale['news_0203'], $this->news_data['news_news'], $snippetSettings).
                    form_textarea('news_extended', self::$locale['news_0204'], $this->news_data['news_extended'], $extendedSettings);
                ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php
                openside(self::$locale['news_0255']);
                echo form_select('news_draft', self::$locale['news_0253'], $this->news_data['news_draft'],
                        array(
                            'inline'      => TRUE,
                            'inner_width' => '100%',
                            'options'     => array(
                                1 => self::$locale['draft'],
                                0 => self::$locale['publish']
                            )
                        )
                    ).
                    form_select_tree('news_cat', self::$locale['news_0201'], $this->news_data['news_cat'],
                        array(
                            'inner_width'  => '100%',
                            'inline'       => TRUE,
                            'parent_value' => self::$locale['news_0202'],
                            'query'        => (multilang_table('NS') ? "WHERE news_cat_language='".LANGUAGE."'" : '')
                        ),
                        DB_NEWS_CATS, 'news_cat_name', 'news_cat_id', 'news_cat_parent'
                    ).
                    form_select('news_visibility', self::$locale['news_0209'], $this->news_data['news_visibility'],
                        array(
                            'options'     => fusion_get_groups(),
                            'placeholder' => self::$locale['choose'],
                            'inner_width' => '100%',
                            'inline'      => TRUE,
                        )
                    );

                if (multilang_table('NS')) {
                    echo form_select('news_language', self::$locale['language'], $this->news_data['news_language'], array(
                        'options'     => fusion_get_enabled_languages(),
                        'placeholder' => self::$locale['choose'],
                        'inner_width' => '100%',
                        'inline'      => TRUE,
                    ));
                } else {
                    echo form_hidden('news_language', '', $this->news_data['news_language']);
                }
                echo form_datepicker('news_datestamp', self::$locale['news_0266'], $this->news_data['news_datestamp'],
                    array('inline' => TRUE, 'inner_width' => '100%'));
                closeside();

                if ($this->news_data['news_id']) {
                    $this->newsGallery();
                } else {

                    openside(self::$locale['news_0006']);

                    if (dbcount("(news_image_id)", DB_NEWS_IMAGES, "news_id=0 AND submit_id=0")) {
                        echo "<div class='list-group-item m-b-10'>\n";
                        echo "<img src='".IMAGES_N.dbresult(dbquery("SELECT news_image FROM ".DB_NEWS_IMAGES." WHERE news_id=0"), 0)."' class='img-responsive'>\n";
                        echo form_button('del_photo', self::$locale['news_0010'], self::$locale['news_0010'], ['class' => 'btn-danger btn-block spacer-xs']);
                        echo "</div>\n";
                    } else {
                        echo form_fileinput('featured_image', self::$locale['news_0011'], isset($_FILES['featured_image']['name']) ? $_FILES['featured_image']['name'] : '',
                            array(
                                'upload_path'      => IMAGES_N,
                                'max_width'        => $news_settings['news_photo_max_w'],
                                'max_height'       => $news_settings['news_photo_max_h'],
                                'max_byte'         => $news_settings['news_photo_max_b'],
                                'thumbnail'        => TRUE,
                                'thumbnail_w'      => $news_settings['news_thumb_w'],
                                'thumbnail_h'      => $news_settings['news_thumb_h'],
                                'thumbnail_folder' => 'thumbs',
                                'delete_original'  => 0,
                                'thumbnail2'       => TRUE,
                                'thumbnail2_w'     => $news_settings['news_photo_w'],
                                'thumbnail2_h'     => $news_settings['news_photo_h'],
                                'type'             => 'image',
                                'class'            => 'm-b-0',
                                'valid_ext'        => $news_settings['news_file_types'],
                                'template'         => 'thumbnail'
                            )
                        );
                    }
                    echo form_select('news_image_align', self::$locale['news_0218'], $this->news_data['news_image_align'], array(
                            'options'     => [
                                'pull-left'       => self::$locale['left'],
                                'news-img-center' => self::$locale['center'],
                                'pull-right'      => self::$locale['right']
                            ],
                            'inner_width' => '100%',
                            'inline'      => TRUE
                        )
                    );
                    closeside();
                }
                openside('');
                ?>
                <div class="row">
                    <div class="col-xs-12">
                        <?php
                        echo form_datepicker('news_start', self::$locale['news_0206'], $this->news_data['news_start'],
                            array(
                                'placeholder' => self::$locale['news_0208'],
                                'join_to_id'  => 'news_end',
                                'width'       => '100%',
                                'inner_width' => '100%'
                            )
                        );
                        ?>
                    </div>
                    <div class='col-xs-12'>
                        <?php
                        echo form_datepicker('news_end', self::$locale['news_0207'], $this->news_data['news_end'],
                            array(
                                'placeholder'  => self::$locale['news_0208'],
                                'join_from_id' => 'news_start',
                                'width'        => '100%',
                                'inner_width'  => '100%',

                            )
                        );
                        ?>
                    </div>
                </div>
                <?php
                closeside();

                openside('');
                echo form_checkbox('news_sticky', self::$locale['news_0211'], $this->news_data['news_sticky'],
                    array(
                        'class'         => 'm-b-5',
                        'reverse_label' => TRUE
                    )
                );
                if (fusion_get_settings("tinymce_enabled") != 1) {
                    echo form_checkbox('news_breaks', self::$locale['news_0212'], $this->news_data['news_breaks'],
                        array(
                            'value'         => 'y',
                            'class'         => 'm-b-5',
                            'reverse_label' => TRUE
                        )
                    );
                }
                echo form_checkbox('news_allow_comments', self::$locale['news_0213'], $this->news_data['news_allow_comments'],
                        array(
                            'reverse_label' => TRUE,
                            'class'         => 'm-b-5',
                            'ext_tip'       => (!fusion_get_settings('comments_enabled') ? "<div class='alert alert-warning'>".sprintf(self::$locale['news_0283'],
                                    self::$locale['comments'])."</div>" : "")
                        )
                    ).form_checkbox('news_allow_ratings', self::$locale['news_0214'], $this->news_data['news_allow_ratings'],
                        array(
                            'reverse_label' => TRUE,
                            'class'         => 'm-b-5',
                            'ext_tip'       => (!fusion_get_settings("comments_enabled") ? "<div class='alert alert-warning'>".sprintf(self::$locale['news_0283'],
                                    self::$locale['ratings']).'</div>' : '')
                        )
                    );
                closeside();

                openside(self::$locale['news_0205']);
                echo form_select('news_keywords', '', $this->news_data['news_keywords'],
                    array(
                        'max_length'  => 320,
                        'placeholder' => self::$locale['news_0205a'],
                        'width'       => '100%',
                        'inner_width' => '100%',
                        'error_text'  => self::$locale['news_0285'],
                        'tags'        => TRUE,
                        'multiple'    => TRUE
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
     *
     * @param $unique_id
     */
    private function display_newsButtons($unique_id) {
        echo "<div class='m-t-20'>\n";
        echo form_button('preview', self::$locale['preview'], self::$locale['preview'], ['class' => 'btn-default m-r-10', 'icon' => 'fa fa-eye']);
        echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel'],
            array('class' => 'btn-default m-r-10', 'input_id' => 'cancel-'.$unique_id, 'icon' => 'fa fa-times'));
        echo form_button('save', self::$locale['news_0241'], self::$locale['news_0241'],
            array('class' => 'btn-success', 'input_id' => 'save-'.$unique_id, 'icon' => 'fa fa-hdd-o'));
        echo form_button("save_and_close", self::$locale['save_and_close'], self::$locale['save_and_close'],
            array("class" => "btn-primary m-l-10", 'input_id' => 'save_and_close-'.$unique_id, 'icon' => 'fa fa-hdd-o'));
        echo "</div>";
        echo "<hr/>";
    }

    /**
     * Gallery Features
     */
    private function newsGallery() {

        $news_settings = self::get_news_settings();

        $default_fileinput_options = array(
            'upload_path'      => IMAGES_N,
            'max_width'        => $news_settings['news_photo_max_w'],
            'max_height'       => $news_settings['news_photo_max_h'],
            'max_byte'         => $news_settings['news_photo_max_b'],
            'thumbnail'        => TRUE,
            'thumbnail_w'      => $news_settings['news_thumb_w'],
            'thumbnail_h'      => $news_settings['news_thumb_h'],
            'thumbnail_folder' => 'thumbs',
            'delete_original'  => 0,
            'thumbnail2'       => TRUE,
            'thumbnail2_w'     => $news_settings['news_photo_w'],
            'thumbnail2_h'     => $news_settings['news_photo_h'],
            'type'             => 'image',
            'template'         => 'modern',
            'class'            => 'm-b-0',
            'valid_ext'        => $news_settings['news_file_types'],
            'multiple'         => TRUE,
            'max_count'        => 8
        );

        $alignOptions = array(
            'pull-left'       => self::$locale['left'],
            'news-img-center' => self::$locale['center'],
            'pull-right'      => self::$locale['right']
        );

        /**
         * Post Save
         */

        if (!empty($_FILES['news_image'])) { // when files is uploaded.
            $upload = form_sanitizer($_FILES['news_image'], '', 'news_image');
            $success_upload = 0;
            $failed_upload = 0;

            if (!empty($upload)) {
                $total_files_uploaded = count($upload);

                for ($i = 0; $i < $total_files_uploaded; $i++) {
                    $current_upload = $upload[$i];
                    //print_p($current_upload);
                    if (!$current_upload['error']) {
                        $data = array(
                            'news_image_user'      => fusion_get_userdata('user_id'),
                            'news_id'              => $this->news_data['news_id'],
                            'news_image'           => $current_upload['image_name'],
                            'news_image_t1'        => $current_upload['thumb1_name'],
                            'news_image_t2'        => $current_upload['thumb2_name'],
                            'news_image_datestamp' => TIME
                        );
                        dbquery_insert(DB_NEWS_IMAGES, $data, 'save');
                        $success_upload++;
                    } else {
                        $failed_upload++;
                    }
                }
                addNotice("success", sprintf(self::$locale['news_0268'], $success_upload));
                if ($failed_upload) {
                    addNotice("warning", sprintf(self::$locale['news_0269'], $failed_upload));
                }
                if (\defender::safe()) {
                    redirect(FUSION_REQUEST);
                }
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
                addNotice('success', self::$locale['news_0104']);
                redirect(FUSION_REQUEST);
            }
        }

        $photo_query = "SELECT * FROM ".DB_NEWS_IMAGES." WHERE news_id='".$this->news_data['news_id']."'";
        $photo_result = dbquery($photo_query);
        $news_photos = array();
        $news_photo_opts = array();
        if (dbrows($photo_result) > 0) {
            while ($photo_data = dbarray($photo_result)) {
                $news_photos[$photo_data['news_image_id']] = $photo_data;
                $news_photo_opts[$photo_data['news_image_id']] = $photo_data['news_image'];
            }
        }

        openside(self::$locale['news_0006']);
        echo form_button('image_gallery', self::$locale['news_0007'], 'image_gallery', array('type' => 'button', 'class' => 'btn-default', 'deactivate' => !$this->news_data['news_id'] ? TRUE : FALSE));
        if (!empty($news_photo_opts)) :
            ?>
            <hr/>
            <?php
            echo form_select('news_image_front_default', self::$locale['news_0011'], $this->news_data['news_image_front_default'],
                    array(
                        'allowclear'  => TRUE,
                        'placeholder' => self::$locale['news_0270'],
                        'inline'      => FALSE,
                        'inner_width' => '100%',
                        'options'     => $news_photo_opts
                    )
                ).
                form_select('news_image_full_default', self::$locale['news_0012'], $this->news_data['news_image_full_default'],
                    array(
                        'allowclear'  => TRUE,
                        'placeholder' => self::$locale['news_0270'],
                        'inline'      => FALSE,
                        'inner_width' => '100%',
                        'options'     => $news_photo_opts
                    )
                ).
                form_select('news_image_align', self::$locale['news_0218'], $this->news_data['news_image_align'], array("options" => $alignOptions, 'inline' => FALSE, 'inner_width' => '100%'));
        else:
                echo form_hidden('news_image_align', '', $this->news_data['news_image_align']);
        endif;
        closeside();

        ob_start();
        echo openmodal('image_gallery_modal', self::$locale['news_0006'], array('button_id' => 'image_gallery'));
        echo openform('gallery_form', 'POST', FUSION_REQUEST, array('enctype' => TRUE));

        // Two tabs
        $modal_tab['title'][] = self::$locale['news_0008'];
        $modal_tab['id'][] = 'news_upload_tab';
        $modal_tab['title'][] = self::$locale['news_0009'];
        $modal_tab['id'][] = 'news_media_tab';
        $modal_tab_active = tab_active($modal_tab, 0);
        echo opentab($modal_tab, $modal_tab_active, 'newsModalTab');
        echo opentabbody($modal_tab['title'][0], $modal_tab['id'][0], $modal_tab_active);
        ?>
        <div class="p-20">
            <div class="well">
                <?php
                echo form_fileinput('news_image[]', '', '', $default_fileinput_options);
                ?>
                <?php echo sprintf(self::$locale['news_0217'], parsebytesize($news_settings['news_photo_max_b'])); ?>
            </div>
            <?php echo form_button('upload_photo', self::$locale['news_0008'], 'upload', array('class' => 'btn-primary btn-lg')) ?>
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
                                        <?php echo form_button('delete_photo', self::$locale['news_0010'], $photo_data['news_image_id'],
                                            array(
                                                'input_id' => 'delete_photo_'.$photo_data['news_image_id'],
                                                'icon'     => 'fa fa-trash'
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
                    <div class="well text-center"><?php echo self::$locale['news_0267'] ?></div>
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
                addNotice("success", self::$locale['news_0101']);
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", self::$locale['news_0108']);
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['news_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Switch to post
        $sql_condition = "";
        $sql_params = array();
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
                $sql_condition .= " AND $key ".$values['operator']." :".$key;
                $sql_params[':'.$key] = ($values['operator'] == "LIKE" ? "%" : '').$values['input'].($values['operator'] == "LIKE" ? "%" : '');
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
        $news_query = "SELECT n.*, nc.*,
        IF(nc.news_cat_name !='', nc.news_cat_name, '".self::$locale['news_0202']."') 'news_cat_name',
        u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM ".DB_NEWS." n
        INNER JOIN ".DB_USERS." u on u.user_id=n.news_name
        LEFT JOIN ".DB_NEWS_CATS." nc ON nc.news_cat_id=n.news_cat
        WHERE news_language=:language $sql_condition
        GROUP BY n.news_id
        ORDER BY n.news_draft DESC, n.news_sticky DESC, n.news_datestamp DESC
        LIMIT $rowstart, $limit
        ";
        $sql_params[':language'] = LANGUAGE;
        $result2 = dbquery($news_query, $sql_params);
        $news_rows = dbrows($result2);

        $image_rows = array();
        $image_result = dbquery("SELECT news_id, count(news_image_id) 'image_count' FROM ".DB_NEWS_IMAGES." GROUP BY news_id ORDER BY news_id ASC");
        if (dbrows($image_result)) {
            while ($imgData = dbarray($image_result)) {
                $image_rows[$imgData['news_id']] = $imgData['image_count'];
            }
        }

        $comment_rows = array();
        $comment_result = dbquery("SELECT comment_item_id, count(comment_id) 'comment_count' FROM ".DB_COMMENTS." WHERE comment_type=:comment_type GROUP BY comment_item_id ORDER BY comment_item_id ASC", [':comment_type' => 'N']);
        if (dbrows($comment_result)) {
            while ($comData = dbarray($comment_result)) {
                $comment_rows[$comData['comment_item_id']] = $comData['comment_count'];
            }
        }

        ?>
        <div class="m-t-15">
            <?php
            echo openform("news_filter", "post", FUSION_REQUEST);
            echo "<div class='row clearfix'>\n";
            echo "<div class='col-xs-12 col-sm-12 col-md-8 pull-right text-right'>\n";
            echo "<a class='btn btn-success m-r-10' href='".clean_request("ref=news_form", array("ref"), FALSE)."'><i class='fa fa-plus fa-fw'></i> ".self::$locale['news_0002']."</a>";
            echo "<a class='btn btn-default m-r-10' onclick=\"run_admin('publish');\"><i class='fa fa-check fa-fw'></i> ".self::$locale['publish']."</a>";
            echo "<a class='btn btn-default m-r-10' onclick=\"run_admin('unpublish');\"><i class='fa fa-ban fa-fw'></i> ".self::$locale['unpublish']."</a>";
            echo "<a class='btn btn-default m-r-10' onclick=\"run_admin('sticky');\"><i class='fa fa-sticky-note fa-fw'></i> ".self::$locale['sticky']."</a>";
            echo "<a class='btn btn-default m-r-10' onclick=\"run_admin('unsticky');\"><i class='fa fa-sticky-note-o fa-fw'></i> ".self::$locale['unsticky']."</a>";
            echo "<a class='btn btn-danger m-r-10' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o fa-fw'></i> ".self::$locale['delete']."</a>";
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
                "news_text"       => !empty($_POST['news_text']) ? form_sanitizer($_POST['news_text'], "", "news_text") : "",
                "news_status"     => !empty($_POST['news_status']) ? form_sanitizer($_POST['news_status'], "", "news_status") : "",
                "news_category"   => !empty($_POST['news_category']) ? form_sanitizer($_POST['news_category'], "", "news_category") : "",
                "news_visibility" => !empty($_POST['news_visibility']) ? form_sanitizer($_POST['news_visibility'], "", "news_visibility") : "",
                "news_language"   => !empty($_POST['news_language']) ? form_sanitizer($_POST['news_language'], "", "news_language") : "",
                "news_author"     => !empty($_POST['news_author']) ? form_sanitizer($_POST['news_author'], "", "news_author") : "",
            );

            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                    break;
                }
            }
            echo "<div class='col-xs-12 col-sm-12 col-md-4'>\n";
            echo form_text('news_text', '', $filter_values['news_text'], array(
                'placeholder'       => self::$locale['news_0200'],
                'append_button'     => TRUE,
                'append_value'      => "<i class='fa fa-search'></i> ".self::$locale['search'],
                'append_form_value' => 'search_news',
                'inner_width'       => '250px',
            ));
            echo "</div>\n";
            echo "</div>\n";
            echo "<div class='row m-b-20'>\n";
            echo "<div class='col-xs-6 vt'>\n";
            echo "<a class='btn btn-sm ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>".self::$locale['news_0242']."
            <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
            echo form_button("news_clear", self::$locale['news_0243'], "clear", array('class' => 'btn-default btn-sm'));
            echo "</div>\n";
            echo "</div>\n";
            add_to_jquery("
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
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
                "allowclear" => TRUE, "placeholder" => "- ".self::$locale['news_0244']." -", "options" => array(
                    0 => self::$locale['news_0245'],
                    1 => self::$locale['draft'],
                    2 => self::$locale['sticky'],
                )
            ));
            echo "</div>\n";

            echo "<div class='display-inline-block'>\n";
            echo form_select("news_visibility", "", $filter_values['news_visibility'], array(
                "allowclear" => TRUE, "placeholder" => "- ".self::$locale['news_0246']." -", "options" => fusion_get_groups()
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $news_cats_opts = array(0 => self::$locale['news_0247']);
            $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ORDER BY news_cat_name ASC");
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $news_cats_opts[$data['news_cat_id']] = $data['news_cat_name'];
                }
            }
            echo form_select("news_category", "", $filter_values['news_category'], array("allowclear" => TRUE, "placeholder" => "- ".self::$locale['news_0248']." -", "options" => $news_cats_opts));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $language_opts = array(0 => self::$locale['news_0249']);
            $language_opts += fusion_get_enabled_languages();
            echo form_select("news_language", "", $filter_values['news_language'], array("allowclear" => TRUE, "placeholder" => "- ".self::$locale['news_0250']." -", "options" => $language_opts));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $author_opts = array(0 => self::$locale['news_0251']);
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
                    'allowclear'  => TRUE,
                    'placeholder' => '- '.self::$locale['news_0252'].' -',
                    'options'     => $author_opts
                )
            );
            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
            ?>
        </div>
        <hr/>
        <?php echo openform("news_table", "post", FUSION_REQUEST); ?>
        <?php echo form_hidden("table_action", "", ""); ?>

        <div class="display-block">
            <div class="display-inline-block m-l-10">
                <?php echo form_select('news_display', self::$locale['show'], $limit,
                    array(
                        'inner_width' => '100px',
                        'inline'      => TRUE,
                        'options'     => array(
                            5   => 5,
                            10  => 10,
                            16  => 16,
                            25  => 25,
                            50  => 50,
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

        <div class="table-responsive"><table class="table table-striped">
            <thead>
            <tr>
                <td></td>
                <td class="strong"><?php echo self::$locale['news_0200'] ?></td>
                <td class="strong min"><?php echo self::$locale['news_0201'] ?></td>
                <td class="strong min"><?php echo self::$locale['news_0209'] ?></td>
                <td class="strong min"><?php echo self::$locale['sticky'] ?></td>
                <td class="strong min"><?php echo self::$locale['draft'] ?></td>
                <td class="strong"><?php echo self::$locale['global_073'] ?></td>
                <td class="strong"><?php echo self::$locale['news_0009'] ?></td>
                <td class="strong"><?php echo self::$locale['news_0142'] ?></td>
                <td class="strong"><?php echo self::$locale['actions'] ?></td>
                <td class="strong min">ID</td>
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
                            <span class="badge"><?php echo $data['news_sticky'] ? self::$locale['yes'] : self::$locale['no'] ?></span>
                        </td>
                        <td>
                            <span class="badge"><?php echo $data['news_draft'] ? self::$locale['yes'] : self::$locale['no'] ?></span>
                        </td>
                        <td><?php echo format_word(isset($comment_rows[$data['news_id']]) ? $comment_rows[$data['news_id']] : 0, self::$locale['fmt_comment']) ?></td>
                        <td><?php echo format_word(isset($image_rows[$data['news_id']]) ? $image_rows[$data['news_id']] : 0, self::$locale['fmt_photo']) ?></td>
                        <td>
                            <div class="overflow-hide"><?php echo profile_link($data['user_id'], $data['user_name'],
                                    $data['user_status']) ?></div>
                        </td>
                        <td>
                            <a href="<?php echo $edit_link ?>"><?php echo self::$locale['edit'] ?></a> &middot;
                            <a href="<?php echo FUSION_SELF.fusion_get_aidlink()."&amp;action=delete&amp;news_id=".$data['news_id'] ?>"
                               onclick="return confirm('<?php echo self::$locale['news_0281']; ?>')">
                                <?php echo self::$locale['delete'] ?>
                            </a>
                        </td>
                        <td><?php echo $data['news_id'] ?></td>
                    </tr>
                    <?php
                endwhile;
            else: ?>
                <tr>
                    <td colspan="11" class="text-center"><strong><?php echo self::$locale['news_0109'] ?></strong></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table></div>
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
                    while ($data = dbarray($result)) {
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
                }

                dbquery("DELETE FROM ".DB_NEWS_IMAGES." WHERE news_id='$news_id'");
                dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='$news_id'");
                dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='$news_id' and comment_type='N'");
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$news_id' and rating_type='N'");
                dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='$news_id'");
                addNotice('success', self::$locale['news_0102']);

                redirect(FUSION_SELF.fusion_get_aidlink());
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
    }
}
