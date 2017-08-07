<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_submissions.php
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

class NewsSubmissionsAdmin extends NewsAdminModel {

    private static $instance = NULL;
    private static $locale = [];
    private $news_data = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayNewsAdmin() {

        pageAccess('N');

        self::$locale = self::get_newsAdminLocale();

        if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {

            // Publish the Submissions
            if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) || isset($_POST['preview'])) {

                $select = "SELECT ts.*, tu.user_id FROM ".DB_SUBMISSIONS." ts INNER JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id WHERE submit_id=:submit_id";
                $bind = [
                    ':submit_id' => $_GET['submit_id']
                ];

                $result = dbquery($select, $bind);

                if (dbrows($result)) {

                    $data = dbarray($result);

                    $news_news = '';
                    if ($_POST['news_news']) {
                        $news_news = str_replace("src='".str_replace('../', '', IMAGES_N), "src='".IMAGES_N,
                            (fusion_get_settings('allow_php_exe') ? htmlspecialchars($_POST['news_news']) : stripslashes($_POST['news_news'])));
                    }

                    $news_extended = '';
                    if ($_POST['news_extended']) {
                        $news_extended = str_replace("src='".str_replace('../', '', IMAGES_N), "src='".IMAGES_N,
                            (fusion_get_settings('allow_php_exe') ? htmlspecialchars($_POST['news_extended']) : stripslashes($_POST['news_extended'])));
                    }

                    $this->news_data = array(
                        'news_id'                  => 0,
                        'news_subject'             => form_sanitizer($_POST['news_subject'], '', 'news_subject'),
                        'news_cat'                 => form_sanitizer($_POST['news_cat'], 0, 'news_cat'),
                        'news_news'                => form_sanitizer($news_news, "", "news_news"),
                        'news_extended'            => form_sanitizer($news_extended, "", "news_extended"),
                        'news_keywords'            => form_sanitizer($_POST['news_keywords'], '', 'news_keywords'),
                        'news_datestamp'           => form_sanitizer($_POST['news_datestamp'], '', 'news_datestamp'),
                        'news_start'               => form_sanitizer($_POST['news_start'], 0, 'news_start'),
                        'news_end'                 => form_sanitizer($_POST['news_end'], 0, 'news_end'),
                        'news_visibility'          => form_sanitizer($_POST['news_visibility'], 0, 'news_visibility'),
                        'news_draft'               => form_sanitizer($_POST['news_draft'], 0, 'news_draft'),
                        'news_sticky'              => isset($_POST['news_sticky']) ? 1 : 0,
                        'news_name'                => $data['user_id'],
                        'news_allow_comments'      => isset($_POST['news_allow_comments']) ? 1 : 0,
                        'news_allow_ratings'       => isset($_POST['news_allow_ratings']) ? 1 : 0,
                        'news_language'            => form_sanitizer($_POST['news_language'], '', 'news_language'),
                        'news_image_full_default'  => '',
                        'news_image_front_default' => '',
                        'news_image_align'         => '',
                    );

                    if (fusion_get_settings('tinymce_enabled') != 1) {
                        $this->news_data['news_breaks'] = isset($_POST['news_breaks']) ? "y" : "n";
                    } else {
                        $this->news_data['news_breaks'] = "n";
                    }

                    if (\defender::safe()) {

                        if (!empty($_FILES['featured_image'])) { // when files is uploaded.
                            $upload = form_sanitizer($_FILES['featured_image'], '', 'featured_image');
                            if (!empty($upload)) {
                                if (!$upload['error']) {
                                    $data = array(
                                        'news_image_user'      => fusion_get_userdata('user_id'),
                                        'submit_id'            => $_GET['submit_id'],
                                        'news_image'           => $upload['image_name'],
                                        'news_image_t1'        => $upload['thumb1_name'],
                                        'news_image_t2'        => $upload['thumb2_name'],
                                        'news_image_datestamp' => TIME
                                    );
                                    $photo_id = dbquery_insert(DB_NEWS_IMAGES, $data, 'save', ['keep_session' => TRUE]);
                                    $this->news_data['news_image_full_default'] = $photo_id;
                                    $this->news_data['news_image_front_default'] = $photo_id;
                                    $this->news_data['news_image_align'] = form_sanitizer($_POST['news_image_align'], '', 'news_image_align');
                                }
                            }
                        } else {

                            if (!empty($_POST['news_image_full_default'])) {
                                $this->news_data['news_image_full_default'] = form_sanitizer($_POST['news_image_full_default'], '', 'news_image_full_default');
                            }

                            if (!empty($_POST['news_image_front_default'])) {
                                $this->news_data['news_image_front_default'] = form_sanitizer($_POST['news_image_front_default'], '', 'news_image_front_default');
                            }
                            if (!empty($_POST['news_image_align'])) {
                                $this->news_data['news_image_align'] = form_sanitizer($_POST['news_image_align'], '', 'news_image_align');
                            }
                        }


                        if (isset($_POST['preview'])) {

                            $preview = new News_Preview();
                            $preview->set_PreviewData($this->news_data);
                            $preview->display_preview();

                            dbquery("UPDATE ".DB_SUBMISSIONS." SET submit_criteria=:config WHERE submit_id=:submit_id", [
                                ':config'    => \defender::encode($this->news_data),
                                ':submit_id' => $_GET['submit_id']
                            ]);

                        } else {

                            if ($this->news_data['news_sticky'] == 1) {
                                dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
                            }

                            $news_id = dbquery_insert(DB_NEWS, $this->news_data, 'save');
                            // Move all news image from submit id to news id
                            if ($this->news_data['news_image_full_default']) {
                                dbquery("UPDATE ".DB_NEWS_IMAGES." SET news_id=:news_id, submit_id=:zero WHERE submit_id=:submit_id", [':news_id' => $news_id, ':zero' => 0, ':submit_id' => $_GET['submit_id']]);
                            }
                            // delete the submissions
                            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submit_id", [':submit_id' => $_GET['submit_id']]);

                            if ($this->news_data['news_draft']) {
                                addNotice('success', self::$locale['news_0147']);
                            } else {
                                addNotice('success', self::$locale['news_0146']);
                            }

                            redirect(clean_request('', array('submit_id'), FALSE));
                        }
                    }
                } else {
                    redirect(clean_request('', array('submit_id'), FALSE));
                }

            } elseif (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {

                $bind = [':submit_id' => $_GET['submit_id']];
                $result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS_IMAGES." WHERE submit_id=:submit_id", $bind);
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        if (file_exists(IMAGES_N.$data['news_image'])) unlink(IMAGES_N.$data['news_image']);
                        if (file_exists(IMAGES_N_T.$data['news_image_t1'])) unlink(IMAGES_N_T.$data['news_image_t1']);
                        if (file_exists(IMAGES_N_T.$data['news_image_t2'])) unlink(IMAGES_N_T.$data['news_image_t2']);
                    }
                }
                dbquery("DELETE FROM ".DB_NEWS_IMAGES." WHERE submit_id=:submit_id", $bind);
                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submit_id", $bind);
                addNotice("success", self::$locale['news_0145']);
                redirect(clean_request("", array("submit_id"), FALSE));
            }

            $submit_query = "SELECT ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
                        FROM ".DB_SUBMISSIONS." ts
                        LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
                        WHERE submit_type='n' ORDER BY submit_datestamp DESC";

            $result = dbquery($submit_query);

            $default_criteria = [
                'submit_id'                => 0,
                'submit_datestamp'         => '',
                'submit_keywords'          => '',
                'news_image_full_default'  => '',
                'news_image_front_default' => '',
                'news_language'            => '',
                'news_subject'             => '',
                'news_image_align'         => '',
                'news_cat'                 => '',
                'news_news'                => '',
                'news_extended'            => '',
            ];

            if (dbrows($result) > 0) {

                $data = dbarray($result);

                $submit_criteria = \defender::decode($data['submit_criteria']);
                $submit_criteria += $default_criteria;

                $this->news_data = array(
                    'submit_id'                => $data['submit_id'],
                    'news_start'               => $data['submit_datestamp'],
                    'news_datestamp'           => $data['submit_datestamp'],
                    'news_keywords'            => $submit_criteria['news_keywords'],
                    'news_visibility'          => 0,
                    'news_image_full_default'  => $submit_criteria['news_image_full_default'],
                    'news_image_front_default' => $submit_criteria['news_image_front_default'],
                    'news_image_align'         => $submit_criteria['news_image_align'],
                    'news_end'                 => '',
                    'news_draft'               => 0,
                    'news_sticky'              => 0,
                    'news_language'            => $submit_criteria['news_language'],
                    'news_subject'             => $submit_criteria['news_subject'],
                    'news_cat'                 => $submit_criteria['news_cat'],
                    'news_news'                => phpentities(stripslashes($submit_criteria['news_news'])),
                    'news_extended'            => phpentities(stripslashes($submit_criteria['news_extended'])),
                    'news_breaks'              => fusion_get_settings('tinyce_enabled') ? TRUE : FALSE,
                    'news_name'                => $data['user_id'],
                    'news_allow_comments'      => 0,
                    'news_allow_ratings'       => 0,
                );

                add_to_title(self::$locale['global_200'].self::$locale['global_201'].$this->news_data['news_subject']."?");

                echo openform('publish_news', 'post', FUSION_REQUEST);
                echo "<div class='spacer-sm'>\n";
                echo form_button('preview', self::$locale['news_0141'], self::$locale['news_0141'], array('class' => 'btn-default m-r-10', 'icon' => 'fa fa-eye'));
                echo form_button('publish', self::$locale['news_0134'], self::$locale['news_0134'], array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o'));
                echo form_button('delete', self::$locale['news_0135'], self::$locale['news_0135'], array('class' => 'btn-danger', 'icon' => 'fa fa-trash'));
                echo "</div>\n";

                echo "<div class='well clearfix'>\n";
                echo "<div class='pull-left m-r-10'>\n";
                echo display_avatar($data, '40px', '', TRUE, '');
                echo "</div>\n";
                echo "<div class='overflow-hide'>\n";
                echo self::$locale['news_0132'].profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
                echo self::$locale['global_049']." ".timer($data['submit_datestamp'])." - ".showdate("shortdate", $data['submit_datestamp']);
                echo "</div>\n";
                echo "</div>\n";

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
                );
                if (fusion_get_settings('tinymce_enabled')) {
                    $snippetSettings = array('required' => TRUE, 'height' => '200px', 'type' => 'tinymce', 'tinymce' => 'advanced', 'path' => [IMAGES, IMAGES_N, IMAGES_NC]);
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
                    );
                } else {
                    $extendedSettings = array('type' => 'tinymce', 'tinymce' => 'advanced', 'height' => '300px');
                }
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

                        echo form_datepicker('news_datestamp', self::$locale['news_0266'], $this->news_data['news_datestamp'], array('inline' => TRUE, 'inner_width' => '100%'));
                        closeside();

                        if ($this->news_data['news_image_full_default']) {
                            // can i delete this image and replace with another image? yes. i can. but just 1, you cannot manage gallery
                            // this is because the news_id is not present at this moment.
                            $this->newsGallery();
                        } else {
                            openside(self::$locale['news_0006']);
                            $news_settings = self::get_news_settings();
                            echo form_fileinput('featured_image', self::$locale['news_0011'], '',
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
                                    'template'         => 'thumbnail'
                                )
                            );
                            echo form_select('news_image_align', self::$locale['news_0218'], '', array(
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
                echo closeform();
            }

        } else {
            $result = dbquery("SELECT
            ts.submit_id, ts.submit_datestamp, ts.submit_criteria, tu.user_id, tu.user_name, tu.user_avatar, tu.user_status
            FROM ".DB_SUBMISSIONS." ts
            LEFT JOIN ".DB_USERS." tu ON ts.submit_user=tu.user_id
            WHERE submit_type='n' order by submit_datestamp desc
            ");
            $rows = dbrows($result);
            if ($rows > 0) {
                echo "<div class='well'>".sprintf(self::$locale['news_0137'], format_word($rows, self::$locale['fmt_submission']))."</div>\n";
                echo "<div class='table-responsive'><table class='table table-striped'>\n";
                echo "<thead>\n";
                echo "<tr>\n";
                echo "<th>".self::$locale['news_0144']."</th>\n";
                echo "<th>".self::$locale['news_0136']."</th>\n";
                echo "<th>".self::$locale['news_0142']."</th>\n";
                echo "<th>".self::$locale['news_0143']."</th>\n";
                echo "</tr>\n";
                echo "</thead>\n";
                echo "<tbody>\n";
                while ($data = dbarray($result)) {
                    $submit_criteria = \defender::decode($data['submit_criteria']);
                    echo "<tr>\n";
                    echo "<td>".$data['submit_id']."</td>\n";
                    echo "<td><a href='".clean_request("submit_id=".$data['submit_id'], ['section', 'aid'], TRUE)."'>".$submit_criteria['news_subject']."</a></td>\n";
                    echo "<td>".display_avatar($data, '20px', '', TRUE, 'img-rounded m-r-5').profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</td>\n";
                    echo "<td>".timer($data['submit_datestamp'])."</td>\n";

                    echo "</tr>\n";
                }
                echo "</tbody>\n";
                echo "</table>\n</div>";
            } else {
                echo "<div class='well text-center m-t-20'>".self::$locale['news_0130']."</div>\n";
            }
        }
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
            'multiple'         => TRUE
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
                            'submit_id'            => $this->news_data['submit_id'],
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

        $photo_query = "SELECT * FROM ".DB_NEWS_IMAGES." WHERE submit_id=:submit_id";
        $photo_bind = [
            ':submit_id' => $this->news_data['submit_id']
        ];
        $photo_result = dbquery($photo_query, $photo_bind);
        $news_photos = array();
        $news_photo_opts = array();
        if (dbrows($photo_result) > 0) {
            while ($photo_data = dbarray($photo_result)) {
                $news_photos[$photo_data['news_image_id']] = $photo_data;
                $news_photo_opts[$photo_data['news_image_id']] = $photo_data['news_image'];
            }
        }
        openside(self::$locale['news_0006']);
        echo form_button('image_gallery', self::$locale['news_0007'], 'image_gallery', array('type' => 'button', 'class' => 'btn-default'));
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
                form_select('news_image_align', self::$locale['news_0218'], '', array("options" => $alignOptions, 'inline' => FALSE, 'inner_width' => '100%'));
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

}