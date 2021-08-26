<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: faq_submissions.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\FAQ;

class FaqSubmissionsAdmin extends FaqAdminModel {
    private static $instance = NULL;
    private static $defArray = [
        'faq_breaks'     => 'y',
        'faq_visibility' => 0,
        'faq_status'     => 1,
    ];
    private $locale;
    private $inputArray = [];

    public function __construct() {
        parent::__construct();

        $this->locale = self::getFaqAdminLocale();
    }

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Display Admin Area
     */
    public function displayFaqAdmin() {
        pageaccess("FQ");

        // Handle a Submission
        if (check_get('submit_id') && get('submit_id', FILTER_VALIDATE_INT) && dbcount("(submit_id)", DB_SUBMISSIONS, "submit_id=:submitid AND submit_type=:submittype", [':submitid' => get('submit_id'), ':submittype' => 'q'])) {
            $criteria = [
                'criteria'  => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
                'join'      => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
                'where'     => 's.submit_type=:submit_type AND s.submit_id=:submit_id',
                'wheredata' => [
                    ':submit_id'   => get('submit_id'),
                    ':submit_type' => 'q'
                ]
            ];
            $data = self::submitData($criteria);
            $data[0] += self::$defArray;
            $data[0] += \Defender::decode($data[0]['submit_criteria']);
            $this->inputArray = $data[0];
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

    private static function submitData(array $filters = []) {
        $query = "SELECT s.*".(!empty($filters['criteria']) ? $filters['criteria'] : "")."
                FROM ".DB_SUBMISSIONS." s
                ".(!empty($filters['join']) ? $filters['join'] : "")."
                WHERE ".(!empty($filters['where']) ? $filters['where'] : "")."
                ORDER BY s.submit_datestamp DESC
                ";

        $result = dbquery($query, $filters['wheredata']);

        $info = [];

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $info[] = $data;
            }
            return $info;
        }
        return FALSE;
    }

    private function handleDeleteSubmission() {
        if (check_post('delete_submission')) {
            dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => get('submit_id'), ':submittype' => 'q']);
            addnotice('success', $this->locale['faq_0062']);
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    }

    private function handlePostSubmission() {
        if (check_post('publish_submission') || check_post('preview_submission')) {
            // Check posted information
            $faq_answer = "";
            if (check_post('faq_answer')) {
                $faq_answer = stripslashes(post('faq_answer'));
            }

            $SaveinputArray = [
                'faq_question'   => sanitizer('faq_question', '', 'faq_question'),
                'faq_cat_id'     => sanitizer('faq_cat_id', 0, 'faq_cat_id'),
                'faq_visibility' => sanitizer('faq_visibility', 0, 'faq_visibility'),
                'faq_datestamp'  => sanitizer('faq_datestamp', time(), 'faq_datestamp'),
                'faq_name'       => sanitizer('faq_name', 0, 'faq_name'),
                'faq_answer'     => form_sanitizer($faq_answer, '', 'faq_answer'),
                'faq_status'     => sanitizer('faq_status', 0, 'faq_status'),
                'faq_breaks'     => 'n',
                'faq_language'   => sanitizer(['faq_language'], LANGUAGE, 'faq_language')
            ];

            // Line Breaks
            if (fusion_get_settings("tinymce_enabled") != 1) {
                $SaveinputArray['faq_breaks'] = check_post('faq_breaks') ? "y" : "n";
            }

            // Handle
            if (fusion_safe()) {

                // Publish Submission
                if (check_post('publish_submission')) {
                    dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id=:submitid AND submit_type=:submittype", [':submitid' => get('submit_id'), ':submittype' => 'q']);
                    dbquery_insert(DB_FAQS, $SaveinputArray, 'save');
                    addnotice('success', ($SaveinputArray['faq_status'] ? $this->locale['faq_0060'] : $this->locale['faq_0061']));
                    redirect(clean_request('', ['submit_id'], FALSE));
                }

                // Preview Submission
                if (check_post('preview_submission')) {
                    $footer = openmodal("faq_preview", "<i class='fa fa-eye fa-lg m-r-10'></i> ".$this->locale['preview'].": ".$SaveinputArray['faq_question']);
                    if ($SaveinputArray['faq_answer']) {
                        $footer .= "<hr class='m-t-20 m-b-20'>\n";
                        $footer .= parse_text($SaveinputArray['faq_answer'], [
                            'parse_smileys'        => FALSE,
                            'parse_bbcode'         => FALSE,
                            'default_image_folder' => NULL,
                            'add_line_breaks'      => $SaveinputArray['faq_breaks'] == 'y'
                        ]);
                    }
                    $footer .= closemodal();
                    add_to_footer($footer);
                }
            }
        }
    }

    /**
     * Display Form
     */
    private function displayForm() {
        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $faqExtendedSettings = [
                'required'    => TRUE,
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['faq_0253'],
                'error_text'  => $this->locale['faq_0270'],
                'form_name'   => 'faqform',
                'wordcount'   => TRUE
            ];
        } else {
            $faqExtendedSettings = [
                'required'   => TRUE,
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['faq_0270']
            ];
        }

        // Start Form
        echo openform('submissionform', 'post', FUSION_REQUEST);
        echo form_hidden('faq_name', '', $this->inputArray['user_id']);
        ?>
        <div class="well clearfix">
            <div class="pull-left">
                <?php echo display_avatar($this->inputArray, '30px', '', FALSE, 'img-rounded m-t-5 m-r-5'); ?>
            </div>
            <div class="overflow-hide">
                <?php
                $submissionUser = ($this->inputArray['user_name'] != $this->locale['user_na'] ? profile_link($this->inputArray['user_id'], $this->inputArray['user_name'], $this->inputArray['user_status']) : $this->locale['user_na']);
                $submissionDate = showdate("shortdate", $this->inputArray['submit_datestamp']);
                $submissionTime = timer($this->inputArray['submit_datestamp']);

                $replacements = ["{%SUBMISSION_AUTHOR%}" => $submissionUser, "{%SUBMISSION_DATE%}" => $submissionDate, "{%SUBMISSION_TIME%}" => $submissionTime];
                $submissionInfo = strtr($this->locale['faq_0350']."<br />".$this->locale['faq_0351'], $replacements);

                echo $submissionInfo;
                ?>
            </div>
        </div>

        <!-- Display Form -->
        <div class="row">

            <!-- Display Left Column -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8">
                <?php
                echo form_text('faq_question', $this->locale['faq_0100'], $this->inputArray['faq_question'], [
                    'required'   => TRUE,
                    'max_lenght' => 200,
                    'error_text' => $this->locale['faq_0270']
                ]);

                echo form_textarea('faq_answer', $this->locale['faq_0251'], $this->inputArray['faq_answer'], $faqExtendedSettings);
                ?>
            </div>

            <!-- Display Right Column -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <?php

                openside($this->locale['faq_0259']);
                $options = [];
                $faq_result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS.(multilang_table("FQ") ? " WHERE ".in_group('faq_cat_language', LANGUAGE) : "")." ORDER BY faq_cat_name ASC");
                if (dbrows($faq_result)) {
                    $options[0] = $this->locale['faq_0010'];
                    while ($faq_data = dbarray($faq_result)) {
                        $options[$faq_data['faq_cat_id']] = $faq_data['faq_cat_name'];
                    }
                }
                echo form_select('faq_cat_id', $this->locale['faq_0252'], $this->inputArray['faq_cat_id'], [
                    'inner_width' => '100%',
                    'options'     => $options
                ]);
                echo form_select('faq_visibility', $this->locale['faq_0106'], $this->inputArray['faq_visibility'], [
                    'placeholder' => $this->locale['choose'],
                    'inner_width' => '100%',
                    'options'     => fusion_get_groups()
                ]);

                if (multilang_table('FQ')) {
                    echo form_select('faq_language[]', $this->locale['language'], $this->inputArray['faq_language'], [
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'options'     => fusion_get_enabled_languages(),
                        'multiple'    => TRUE
                    ]);
                } else {
                    echo form_hidden('faq_language', "", $this->inputArray['faq_language']);
                }

                echo form_datepicker('faq_datestamp', $this->locale['faq_0203'], $this->inputArray['submit_datestamp']);

                closeside();

                openside($this->locale['faq_0259']);

                echo form_checkbox('faq_status', $this->locale['faq_0255'], $this->inputArray['faq_status'], [
                    'toggle' => TRUE
                ]);

                if (fusion_get_settings('tinymce_enabled') != 1) {
                    echo form_checkbox('faq_breaks', $this->locale['faq_0256'], $this->inputArray['faq_breaks'], [
                        'value'  => 'y',
                        'toggle' => TRUE
                    ]);
                }

                closeside();
                ?>

            </div>
        </div>
        <?php
        echo '<div class="m-t-20">';
        echo form_button('preview_submission', $this->locale['preview'], $this->locale['preview'], ['class' => 'btn-default', 'icon' => 'fa fa-fw fa-eye']);
        echo form_button('publish_submission', $this->locale['publish'], $this->locale['publish'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-fw fa-hdd-o']);
        echo form_button('delete_submission', $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-danger', 'icon' => 'fa fa-fw fa-trash']);
        echo '</div>';
        echo closeform();
    }

    /**
     * Display List with Submissions
     */
    private function displaySubmissionList() {
        $criteria = [
            'criteria'  => ", u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join'      => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=s.submit_user",
            'where'     => 's.submit_type=:submit_type',
            'wheredata' => [
                ':submit_type' => 'q'
            ]

        ];
        $data = self::submitData($criteria);

        if (!empty($data)) {
            echo "<div class='well'>".sprintf($this->locale['faq_0064'], format_word(count($data), $this->locale['fmt_submission']))."</div>\n";
            echo "<div class='table-responsive m-t-10'><table class='table table-striped'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th class='strong'>".$this->locale['faq_0200']."</th>\n";
            echo "<th class='strong col-xs-5'>".$this->locale['faq_0100']."</th>\n";
            echo "<th class='strong'>".$this->locale['faq_0202']."</th>\n";
            echo "<th class='strong'>".$this->locale['faq_0203']."</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";

            foreach ($data as $info) {
                $submitData = \Defender::decode($info['submit_criteria']);
                $submitUser = $this->locale['user_na'];
                if ($info['user_name']) {
                    $submitUser = display_avatar($info, '20px', '', TRUE, 'img-rounded m-r-5');
                    $submitUser .= profile_link($info['user_id'], $info['user_name'], $info['user_status']);
                }

                $reviewLink = clean_request('section=submissions&submit_id='.$info['submit_id'], ['section', 'ref', 'action', 'submit_id'], FALSE);

                echo "<tr>\n";
                echo "<td>".$info['submit_id']."</td>\n";
                echo "<td><a href='".$reviewLink."'>".$submitData['faq_question']."</a></td>\n";
                echo "<td>".$submitUser."</td>\n";
                echo "<td>".timer($info['submit_datestamp'])."</td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>\n";
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>".$this->locale['faq_0063']."</div>\n";
        }

    }
}
