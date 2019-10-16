<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/admin/controllers/articles_submissions.php
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
namespace PHPFusion\Articles;

class ArticlesSubmissionsAdmin extends ArticlesAdminModel {
    private static $instance = NULL;
    private $inputArray = [];
    private $locale = [];
    private $articleSettings;
    private $dataUser = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Handle Preview and Publish of a Article Submission
     */
    private function handlePostSubmission() {
        if (isset($_POST['publish_submission']) || isset($_POST['preview_submission'])) {

            // Check posted Informations
            $article_snippet = "";
            if ($_POST['article_snippet']) {
                $article_snippet = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['article_snippet']) : stripslashes($_POST['article_snippet'])));
            }

            $article_article = "";
            if ($_POST['article_article']) {
                $article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['article_article']) : stripslashes($_POST['article_article'])));
            }

            $this->inputArray = [
                'article_subject'        => form_sanitizer($_POST['article_subject'], '', 'article_subject'),
                'article_cat'            => form_sanitizer($_POST['article_cat'], 0, 'article_cat'),
                'article_language'       => form_sanitizer($_POST['article_language'], LANGUAGE, 'article_language'),
                'article_visibility'     => form_sanitizer($_POST['article_visibility'], 0, 'article_visibility'),
                'article_datestamp'      => form_sanitizer($_POST['article_datestamp'], time(), 'article_datestamp'),
                'article_name'           => form_sanitizer($_POST['article_name'], 0, 'article_name'),
                'article_snippet'        => form_sanitizer($article_snippet, '', 'article_snippet'),
                'article_article'        => form_sanitizer($article_article, '', 'article_article'),
                'article_keywords'       => form_sanitizer($_POST['article_keywords'], '', 'article_keywords'),
                'article_draft'          => isset($_POST['article_draft']) ? $_POST['article_draft'] : '0',
                'article_allow_comments' => isset($_POST['article_allow_comments']) ? $_POST['article_allow_comments'] : '0',
                'article_allow_ratings'  => isset($_POST['article_allow_ratings']) ? $_POST['article_allow_ratings'] : '0'
            ];

            // Line Breaks
            if (fusion_get_settings("tinymce_enabled") != 1) {
                $this->inputArray['article_breaks'] = isset($_POST['article_breaks']) ? "y" : "n";
            } else {
                $this->inputArray['article_breaks'] = "n";
            }

            // Handle
            if (\defender::safe()) {

                // Publish Submission
                if (isset($_POST['publish_submission'])) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'a']);
                    dbquery_insert(DB_ARTICLES, $this->inputArray, 'save');
                    addNotice('success', (!$this->inputArray['article_draft'] ? $this->locale['article_0060'] : $this->locale['article_0061']));
                    redirect(clean_request('', ['submit_id'], FALSE));
                }

                // Preview Submission
                if (isset($_POST['preview_submission'])) {
                    $footer = openmodal("article_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$this->locale['preview'].": ".$this->inputArray['article_subject']);
                    echo($this->inputArray['article_breaks'] == "n" ? parse_textarea($this->inputArray['article_snippet']) : nl2br(parse_textarea($this->inputArray['article_snippet'])));
                    if ($this->inputArray['article_article']) {
                        $footer .= "<hr class='m-t-20 m-b-20'>\n";
                        $footer .= ($this->inputArray['article_breaks'] == "n" ? parse_textarea($this->inputArray['article_article']) : nl2br(parse_textarea($this->inputArray['article_article'])));
                    }
                    $footer .= closemodal();
                    add_to_footer($footer);
                }
            }
        }
    }

    /**
     * Delete a Article Submission
     */
    private function handleDeleteSubmission() {
        if (isset($_POST['delete_submission'])) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => $_GET['submit_id'], ':submittype' => 'a']);
            addNotice('success', $this->locale['article_0062']);
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    }

    /**
     * Get unserialize Datas for a Submission
     */
    private function unserializeData() {
        $result = dbquery("SELECT s.*
            FROM ".DB_SUBMISSIONS." AS s
            WHERE s.submit_id=:submitid
            LIMIT 0,1", [':submitid' => $_GET['submit_id']]
        );

        if (dbrows($result) > 0) {
            $data = dbarray($result);

            $submit_criteria = \defender::decode($data['submit_criteria']);
            $returnInformations = [
                'article_subject'        => $submit_criteria['article_subject'],
                'article_snippet'        => phpentities(stripslashes($submit_criteria['article_snippet'])),
                'article_article'        => phpentities(stripslashes($submit_criteria['article_article'])),
                'article_keywords'       => $submit_criteria['article_keywords'],
                'article_cat'            => $submit_criteria['article_cat'],
                'article_visibility'     => 0,
                'article_language'       => $submit_criteria['article_language'],
                'article_datestamp'      => $data['submit_datestamp'],
                'article_draft'          => 0,
                'article_breaks'         => fusion_get_settings('tinyce_enabled') ? TRUE : FALSE,
                'article_name'           => $data['submit_user'],
                'article_allow_comments' => 0,
                'article_allow_ratings'  => 0
            ];
            return $returnInformations;
        } else {
            redirect(clean_request('', [], FALSE));
            return NULL;
        }
    }

    /**
     * Display Form
     */
    private function displayForm() {
        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $articleSnippetSettings = [
                'required'    => TRUE,
                'preview'     => TRUE,
                'type'        => 'bbcode',
                'autosize'    => TRUE,
                'placeholder' => $this->locale['article_0254'],
                'error_text'  => $this->locale['article_0271'],
                'form_name'   => 'articleform',
                'wordcount'   => TRUE,
                'path'        => IMAGES_A
            ];
            $articleExtendedSettings = [
                'required'    => ($this->articleSettings['article_extended_required'] ? TRUE : FALSE),
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['article_0253'],
                'error_text'  => $this->locale['article_0272'],
                'form_name'   => 'articleform',
                'wordcount'   => TRUE,
                'path'        => IMAGES_A
            ];
        } else {
            $articleSnippetSettings = [
                'required'   => TRUE,
                'type'       => 'bbcode',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['article_0271'],
                'path'       => IMAGES_A
            ];
            $articleExtendedSettings = [
                'required'   => ($this->articleSettings['article_extended_required'] ? TRUE : FALSE),
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['article_0272'],
                'path'       => IMAGES_A
            ];
        }

        // Start Form
        echo openform('submissionform', 'post', FUSION_REQUEST);
        echo form_hidden('article_name', '', $this->inputArray['article_name']);
        ?>
        <div class="well clearfix m-t-15">
            <div class="pull-left">
                <?php echo display_avatar($this->dataUser, '30px', '', FALSE, 'img-rounded m-t-5 m-r-5'); ?>
            </div>
            <div class="overflow-hide">
                <?php
                $submissionUser = ($this->dataUser['user_name'] != $this->locale['user_na'] ? profile_link($this->dataUser['user_id'], $this->dataUser['user_name'], $this->dataUser['user_status']) : $this->locale['user_na']);
                $submissionDate = showdate("shortdate", $this->inputArray['article_datestamp']);
                $submissionTime = timer($this->inputArray['article_datestamp']);

                $replacements = ["{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime];
                $submissionInfo = strtr($this->locale['article_0350']."<br />".$this->locale['article_0351'], $replacements);

                echo $submissionInfo;
                ?>
            </div>
        </div>
        <?php self::displayFormButtons("formstart"); ?>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_text('article_subject', $this->locale['article_0100'], $this->inputArray['article_subject'], [
                    'required'   => TRUE,
                    'max_lenght' => 200,
                    'error_text' => $this->locale['article_0270']
                ]);

                echo form_select('article_keywords', $this->locale['article_0260'], $this->inputArray['article_keywords'], [
                    'max_length'  => 320,
                    'placeholder' => $this->locale['article_0260a'],
                    'width'       => '100%',
                    'inner_width' => '100%',
                    'tags'        => TRUE,
                    'multiple'    => TRUE
                ]);

                echo form_textarea('article_snippet', $this->locale['article_0251'], $this->inputArray['article_snippet'], $articleSnippetSettings);

                echo form_textarea('article_article', $this->locale['article_0252'], $this->inputArray['article_article'], $articleExtendedSettings);
                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['article_0261']);

            echo form_select('article_draft', $this->locale['status'], $this->inputArray['article_draft'], [
                'inline'      => TRUE,
                'inner_width' => '100%',
                'options'     => [
                    1 => $this->locale['draft'],
                    0 => $this->locale['publish']
                ]
            ]);
            echo form_select_tree('article_cat', $this->locale['article_0101'], $this->inputArray['article_cat'], [
                    'required'     => TRUE,
                    'error_text'   => $this->locale['article_0273'],
                    'inner_width'  => '100%',
                    'inline'       => TRUE,
                    'parent_value' => $this->locale['choose'],
                    'query'        => (multilang_table('AR') ? "WHERE ".in_group('article_cat_language', LANGUAGE) : '')
                ],
                    DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent"
                );

                echo form_select('article_visibility', $this->locale['article_0106'], $this->inputArray['article_visibility'], [
                    'inline'      => TRUE,
                    'placeholder' => $this->locale['choose'],
                    'inner_width' => '100%',
                    'options'     => fusion_get_groups()
                ]);

                if (multilang_table("AR")) {
                    echo form_select('article_language[]', $this->locale['language'], $this->inputArray['article_language'], [
                        'inline'      => TRUE,
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'options'     => fusion_get_enabled_languages(),
                        'multiple'    => TRUE,
                        'delimeter'   => '.'
                    ]);
                } else {
                    echo form_hidden('article_language', '', $this->inputArray['article_language']);
                }

                echo form_datepicker('article_datestamp', $this->locale['article_0203'], $this->inputArray['article_datestamp'], [
                    'inline'      => TRUE,
                    'inner_width' => '100%'
                ]);

                closeside();

                openside($this->locale['article_0262']);

                if (fusion_get_settings('tinymce_enabled') != 1) {
                    echo form_checkbox('article_breaks', $this->locale['article_0257'], $this->inputArray['article_breaks'], [
                        'value'         => 'y',
                        'reverse_label' => TRUE,
                        'class'         => 'm-b-5'
                    ]);
                }

                echo form_checkbox("article_allow_comments", $this->locale['article_0258'], $this->inputArray['article_allow_comments'], [
                    'class'         => 'm-b-5',
                    'reverse_label' => TRUE,
                    'ext_tip'       => (!fusion_get_settings('comments_enabled') ? "<div class='alert alert-warning'>".sprintf($this->locale['article_0274'], $this->locale['comments'])."</div>" : "")
                ]);

                echo form_checkbox('article_allow_ratings', $this->locale['article_0259'], $this->inputArray['article_allow_ratings'], [
                    'class'         => 'm-b-5',
                    'reverse_label' => TRUE,
                    'ext_tip'       => (!fusion_get_settings('ratings_enabled') ? "<div class='alert alert-warning'>".sprintf($this->locale['article_0274'], $this->locale['ratings'])."</div>" : "")
                ]);
                closeside();
                ?>

            </div>
        </div>
        <?php
        self::displayFormButtons("formend");
        echo closeform();
    }

    /**
     * Display Buttons for Form
     *
     * @param      $unique_id
     */
    private function displayFormButtons($unique_id) {
        echo form_button('preview_submission', $this->locale['preview'], $this->locale['preview'], [
            'class'    => 'btn-default btn-sm',
            'icon'     => 'fa fa-eye',
            'input-id' => 'preview_submission-'.$unique_id
        ]);
        echo form_button('publish_submission', $this->locale['article_0352'], $this->locale['article_0352'], [
            'class'    => 'btn-success btn-sm m-l-5',
            'icon'     => 'fa fa-hdd-o',
            'input-id' => 'publish_submission-'.$unique_id
        ]);
        echo form_button('delete_submission', $this->locale['delete'], $this->locale['delete'], [
            'class'    => 'btn-danger btn-sm m-l-5',
            'icon'     => 'fa fa-trash',
            'input-id' => 'delete_submission-'.$unique_id
        ]);
    }

    /**
     * Display List with Submissions
     */
    private function displaySubmissionList() {
        $result = dbquery("
            SELECT s.submit_id, s.submit_criteria, s.submit_datestamp, u.user_id, u.user_name, u.user_status, u.user_avatar
            FROM ".DB_SUBMISSIONS." AS s
            LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user
            WHERE s.submit_type='a'
            ORDER BY submit_datestamp DESC
        ");
        ?>

        <!-- Display Table -->
        <div class="table-responsive m-t-10">
            <table class="table table-striped">
                <thead>
                <tr>
                    <td class="strong"><?php echo $this->locale['article_0200']; ?></td>
                    <td class="strong col-xs-5"><?php echo $this->locale['article_0100'] ?></td>
                    <td class="strong"><?php echo $this->locale['article_0202'] ?></td>
                    <td class="strong"><?php echo $this->locale['article_0203'] ?></td>
                    <td class="strong"><?php echo $this->locale['article_0204'] ?></td>
                </tr>
                </thead>
                <tbody>
                <?php if (dbrows($result) > 0) :
                    while ($data = dbarray($result)) : ?>
                        <?php
                        $submitData = \defender::decode($data['submit_criteria']);
                        $submitUser = $this->locale['user_na'];
                        if ($data['user_name']) {
                            $submitUser = display_avatar($data, '20px', '', TRUE, 'img-rounded m-r-5');
                            $submitUser .= profile_link($data['user_id'], $data['user_name'], $data['user_status']);
                        }

                        $reviewLink = clean_request("section=submissions&submit_id=".$data['submit_id'], ['section', 'ref', 'action', 'submit_id'], FALSE);
                        ?>
                        <tr>
                            <td>#<?php echo $data['submit_id']; ?></td>
                            <td><span class="text-dark"><?php echo $submitData['article_subject']; ?></span></td>
                            <td><?php echo $submitUser; ?></td>
                            <td><?php echo timer($data['submit_datestamp']); ?></td>
                            <td>
                                <a href="<?php echo $reviewLink; ?>"
                                   title="<?php echo $this->locale['article_0205']; ?>"
                                   class="btn btn-default btn-sm"><i
                                            class="fa fa-fw fa-eye"></i> <?php echo $this->locale['article_0205']; ?>
                                </a>
                            </td>
                        </tr>
                    <?php
                    endwhile;
                else: ?>
                    <tr><td colspan="5" class="text-center"><?php echo $this->locale['article_0063']; ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Display Admin Area
     */
    public function displayArticlesAdmin() {
        pageAccess("A");

        $this->locale = self::get_articleAdminLocale();
        $this->articleSettings = self::get_article_settings();

        // Handle a Submission
        if (isset($_GET['submit_id']) && isNum($_GET['submit_id']) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id=".(int)$_GET['submit_id']." AND submit_type='a'")) {
            $this->inputArray = self::unserializeData();

            // Get Infos about Submissioner
            $resultUser = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id=:userid LIMIT 0,1", [':userid' => $this->inputArray['article_name']]);
            if (dbrows($resultUser) > 0) {
                $this->dataUser = dbarray($resultUser);
            } else {
                $this->dataUser = ['user_id' => $this->inputArray['article_name'], 'user_name' => $this->locale['user_na'], 'user_status' => 0, 'user_avatar' => FALSE];
            }

            // Delete, Publish, Preview
            self::handleDeleteSubmission();
            self::handlePostSubmission();

            // Display Form with Buttons
            self::displayForm();

            // Display List
        } else {
            self::displaySubmissionList();
        }
    }
}
