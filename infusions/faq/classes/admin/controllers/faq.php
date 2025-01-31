<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: faq.php
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

class FaqAdmin extends FaqAdminModel {
    private static $instance = NULL;
    private $locale = [];
    private $faq_data = [];
    private $form_action = FUSION_REQUEST;
    private $cat_data = [
        'faq_cat_id'          => 0,
        'faq_cat_name'        => '',
        'faq_cat_description' => '',
        'faq_cat_language'    => LANGUAGE,
    ];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayFaqAdmin() {
        pageaccess('FQ');
        if (check_post('cancel')) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        $this->locale = self::getFaqAdminLocale();
        if (check_get('ref')) {
            switch (get('ref')) {
                case 'faq_cat_form':
                    $this->displayFaqCategoryForm();
                    break;
                case 'faq_form':
                    $this->displayfaqForm();
                    break;
                default:
                    $this->displayFaqListing();
            }
        } else {
            $this->displayFaqListing();
        }

    }

    private function displayFaqCategoryForm() {
        if (check_post('save_cat')) {
            $this->cat_data = [
                'faq_cat_id'          => sanitizer('faq_cat_id', 0, 'faq_cat_id'),
                'faq_cat_name'        => sanitizer('faq_cat_name', '', 'faq_cat_name'),
                'faq_cat_description' => sanitizer('faq_cat_description', '', 'faq_cat_description'),
                'faq_cat_language'    => sanitizer(['faq_cat_language'], LANGUAGE, 'faq_cat_language'),
            ];

            if (fusion_safe()) {
                if ($this->cat_data['faq_cat_id']) {
                    dbquery_insert(DB_FAQ_CATS, $this->cat_data, 'update');
                    addnotice('success', $this->locale['faq_0040']);
                } else {
                    if (!dbcount("(faq_cat_id)", DB_FAQ_CATS, "faq_cat_name=:faq_cat_name", [':faq_cat_name' => $this->cat_data['faq_cat_name']])) {
                        dbquery_insert(DB_FAQ_CATS, $this->cat_data, 'save');
                        addnotice('success', $this->locale['faq_0039']);
                    } else {
                        fusion_stop();
                        \Defender::inputHasError('faq_cat_name');
                        addnotice('warning', $this->locale['faq_0042']);
                    }
                }
                redirect(clean_request('', ['ref', 'cat_id', 'action'], FALSE));
            }

        }

        if (check_get('cat_id') && get('cat_id', FILTER_VALIDATE_INT) && check_get('action') && get('action') == 'edit') {
            $result = dbquery("SELECT * FROM ".DB_FAQ_CATS." WHERE faq_cat_id=:faq_cat_id", [':faq_cat_id' => get('cat_id')]);
            if (dbrows($result) > 0) {
                $this->cat_data = dbarray($result);
            } else {
                redirect(clean_request('', ['ref'], FALSE));
            }
        }
        echo openform('add_faq_cat', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo form_hidden('faq_cat_id', '', $this->cat_data['faq_cat_id']);
        echo form_text('faq_cat_name', $this->locale['faq_0115'], $this->cat_data['faq_cat_name'], ['required' => TRUE, 'inline' => TRUE]);
        echo form_textarea('faq_cat_description', $this->locale['faq_0116'], $this->cat_data['faq_cat_description'], ['autosize' => TRUE, 'inline' => TRUE]);
        if (multilang_table("FQ")) {
            echo form_select('faq_cat_language[]', $this->locale['faq_0117'], $this->cat_data['faq_cat_language'], [
                'options'     => fusion_get_enabled_languages(),
                'inline'      => TRUE,
                'placeholder' => $this->locale['choose'],
                'multiple'    => TRUE
            ]);
        } else {
            echo form_hidden('cat_language', '', LANGUAGE);
        }
        echo form_button('save_cat', $this->locale['faq_0118'], $this->locale['faq_0118'], ['class' => 'btn-primary m-t-10']);
        echo closeform();
    }

    /**
     * Displays Faq Form
     */
    private function displayfaqForm() {
        $default_data = [
            'faq_id'         => 0,
            'faq_cat_id'     => 0,
            'faq_question'   => '',
            'faq_answer'     => '',
            'faq_datestamp'  => time(),
            'faq_name'       => 0,
            'faq_breaks'     => 'n',
            'faq_visibility' => 0,
            'faq_status'     => 1,
            'faq_language'   => LANGUAGE
        ];

        // Delete
        self::executeDelete();

        // Update
        self::executeUpdate();

        /**
         * Global vars
         */
        if ((check_get('action') && get('action') == "edit") && (check_post('faq_id') && post('faq_id', FILTER_VALIDATE_INT)) || (check_get('faq_id') && get('faq_id', FILTER_VALIDATE_INT))) {
            $id = (check_post('faq_id') ? post('faq_id') : get('faq_id'));
            $criteria = [
                'criteria' => "ac.*, u.user_id, u.user_name, u.user_status, u.user_avatar",
                'join'     => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=ac.faq_name",
                'where'    => "ac.faq_id='$id'".(multilang_table("FQ") ? " AND ".in_group('ac.faq_language', LANGUAGE) : ""),
            ];
            $result = self::faqData($criteria);
            if (dbrows($result) > 0) {
                $this->faq_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        } else {
            $this->faq_data = $default_data;
            $this->faq_data['faq_breaks'] = (fusion_get_settings('tinymce_enabled') ? 'n' : 'y');
        }
        self::faqContentForm();
    }

    private function executeDelete() {
        if (check_get('action') && get('action') == "delete" && check_get('faq_id') && get('faq_id', FILTER_VALIDATE_INT)) {
            $faq_id = (int)get('faq_id');

            dbquery("DELETE FROM  ".DB_FAQS." WHERE faq_id=:faqid", [':faqid' => $faq_id]);
            addnotice('success', $this->locale['faq_0032']);

            redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
        }
    }

    /**
     * Create or Update
     */
    private function executeUpdate() {
        if ((check_post('save')) or (check_post('save_and_close'))) {

            $this->faq_data = [
                'faq_id'         => sanitizer('faq_id', 0, 'faq_id'),
                'faq_question'   => sanitizer('faq_question', '', 'faq_question'),
                'faq_cat_id'     => sanitizer('faq_cat_id', 0, 'faq_cat_id'),
                'faq_answer'     => form_sanitizer(addslashes($_POST['faq_answer']), '', 'faq_answer'),
                'faq_datestamp'  => sanitizer('faq_datestamp', '', 'faq_datestamp'),
                'faq_visibility' => sanitizer('faq_visibility', 0, 'faq_visibility'),
                'faq_status'     => sanitizer('faq_status', 0, 'faq_status'),
                'faq_breaks'     => "n",
                'faq_language'   => sanitizer(['faq_language'], LANGUAGE, 'faq_language'),
            ];

            // Line Breaks
            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->faq_data['faq_breaks'] = check_post('faq_breaks') ? "y" : "n";
            }

            // Handle
            if (fusion_safe()) {
                // Update
                if (dbcount("(faq_id)", DB_FAQS, "faq_id='".$this->faq_data['faq_id']."'")) {
                    $this->faq_data['faq_datestamp'] = check_post('update_datestamp') ? time() : $this->faq_data['faq_datestamp'];
                    dbquery_insert(DB_FAQS, $this->faq_data, 'update');
                    addnotice('success', $this->locale['faq_0031']);

                    // Create
                } else {
                    $this->faq_data['faq_name'] = fusion_get_userdata('user_id');
                    $this->faq_data['faq_id'] = dbquery_insert(DB_FAQS, $this->faq_data, 'save');
                    addnotice('success', $this->locale['faq_0030']);
                }

                // Redirect
                if (check_post('save_and_close')) {
                    redirect(clean_request('', ['ref', 'action', 'faq_id'], FALSE));
                } else {
                    redirect(FUSION_REQUEST);
                }
            }
        }
    }

    private static function faqData(array $filters = []) {
        return dbquery("SELECT ".(!empty($filters['criteria']) ? $filters['criteria'] : "")."
            FROM ".DB_FAQS." ac
            ".(!empty($filters['join']) ? $filters['join'] : "")."
            WHERE ".(!empty($filters['where']) ? $filters['where'] : "").
            (!empty($filters['sql_condition']) ? $filters['sql_condition'] : "")."
            GROUP BY ac.faq_id
            ORDER BY ac.faq_datestamp DESC
            ".(!empty($filters['limit']) ? $filters['limit'] : "")."
        ");
    }

    /**
     * Display Form
     */
    private function faqContentForm() {
        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $faqExtendedSettings = [
                'required'    => TRUE,
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['faq_0253'],
                'error_text'  => $this->locale['faq_0271'],
                'form_name'   => 'faqform',
                'wordcount'   => TRUE
            ];
        } else {
            $faqExtendedSettings = [
                'required'   => TRUE,
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['faq_0271']
            ];
        }

        // Start Form
        echo openform('faqform', 'post', $this->form_action);
        echo form_hidden('faq_id', '', $this->faq_data['faq_id']);
        ?>

        <!-- Display Form -->
        <div class='row'>
            <!-- Display Left Column -->
            <div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>
                <?php
                echo form_text('faq_question', $this->locale['faq_0100'], $this->faq_data['faq_question'], [
                    'required'   => TRUE,
                    'max_length' => 200,
                    'error_text' => $this->locale['faq_0271']
                ]);
                echo form_textarea('faq_answer', $this->locale['faq_0251'], $this->faq_data['faq_answer'], $faqExtendedSettings);
                ?>
            </div>
            <!-- Display Right Column -->
            <div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>
                <?php
                openside($this->locale['faq_0259']);
                $options = [];
                $faq_result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name ASC");
                if (dbrows($faq_result)) {
                    while ($faq_data = dbarray($faq_result)) {
                        $options[$faq_data['faq_cat_id']] = $faq_data['faq_cat_name'];
                    }
                }
                echo form_select('faq_cat_id', $this->locale['faq_0252'], $this->faq_data['faq_cat_id'], [
                    'inner_width' => '100%',
                    'options'     => $options,
                    'required'    => TRUE
                ]);

                echo form_select('faq_visibility[]', $this->locale['faq_0106'], $this->faq_data['faq_visibility'], [
                    'options'     => fusion_get_groups(),
                    'placeholder' => $this->locale['choose'],
                    'inner_width' => '100%',
                    'multiple'    => TRUE,
                ]);
                if (multilang_table('FQ')) {
                    echo form_select("faq_language[]", $this->locale['language'], $this->faq_data['faq_language'], [
                        'options'     => fusion_get_enabled_languages(),
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'multiple'    => TRUE
                    ]);
                } else {
                    echo form_hidden('faq_language', '', $this->faq_data['faq_language']);
                }
                echo form_hidden('faq_datestamp', '', $this->faq_data['faq_datestamp']);
                if (check_get('action') && get('action') == 'edit') {
                    echo form_checkbox('update_datestamp', $this->locale['faq_0257'], '', [
                        'toggle' => TRUE
                    ]);
                }
                closeside();
                openside($this->locale['faq_0258']);
                echo form_checkbox('faq_status', $this->locale['faq_0255'], $this->faq_data['faq_status'], [
                    'class'  => 'm-b-5',
                    'toggle' => TRUE
                ]);

                if (fusion_get_settings("tinymce_enabled") != 1) {
                    echo form_checkbox('faq_breaks', $this->locale['faq_0256'], $this->faq_data['faq_breaks'], [
                        'value'  => 'y',
                        'class'  => 'm-b-5',
                        'toggle' => TRUE
                    ]);
                }
                closeside();
                ?>

            </div>
        </div>
        <?php
        echo '<div class="m-t-20">';
        echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], ["class" => "btn-default btn-sm", "icon" => "fa fa-times"]);
        echo form_button("save", $this->locale['save'], $this->locale['save'], ["class" => "btn-success btn-sm m-l-5", "icon" => "fa fa-hdd-o"]);
        echo form_button("save_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], ["class" => "btn-primary btn-sm m-l-5", "icon" => "fa fa-floppy-o"]);
        echo '</div>';
        echo closeform();
    }

    /**
     * Displays Listing
     */
    private function displayFaqListing() {
        // Run functions
        $allowed_actions = array_flip(['publish', 'unpublish', 'delete', 'faq_display']);
        $table_action = post('table_action');

        // Table Actions
        if (check_post('table_action') && isset($allowed_actions[$table_action])) {
            $input = (check_post('faq_id')) ? explode(",", form_sanitizer($_POST['faq_id'], 0, 'faq_id')) : "";
            if (!empty($input)) {
                foreach ($input as $faq_id) {
                    // check input table
                    if (dbcount("('faq_id')", DB_FAQS, "faq_id=:faqid", [':faqid' => (int)$faq_id]) && fusion_safe()) {
                        switch ($_POST['table_action']) {
                            case 'publish':
                                dbquery("UPDATE ".DB_FAQS." SET faq_status=:status WHERE faq_id=:faqid", ['status' => '1', ':faqid' => (int)$faq_id]);
                                addnotice("success", $this->locale['faq_0037']);
                                break;
                            case 'unpublish':
                                dbquery("UPDATE ".DB_FAQS." SET faq_status=:status WHERE faq_id=:faqid", ['status' => '0', ':faqid' => (int)$faq_id]);
                                addnotice("warning", $this->locale['faq_0038']);
                                break;
                            case 'delete':
                                dbquery("DELETE FROM  ".DB_FAQS." WHERE faq_id=:faqid", [':faqid' => (int)$faq_id]);
                                addnotice('success', $this->locale['faq_0032']);
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                redirect(FUSION_REQUEST);
            }
            addnotice('warning', $this->locale['faq_0034']);
            redirect(FUSION_REQUEST);
        }

        if (check_post('edit_faq_cat') && check_post('faq_cat_id') && post('faq_cat_id', FILTER_VALIDATE_INT)) {
            redirect(clean_request('cat_id='.post('faq_cat_id').'&action=edit&ref=faq_cat_form', ['action', 'cat_id', 'ref'], FALSE));
        }

        // delete category
        if (check_post('delete_faq_cat') && check_post('faq_cat_id') && post('faq_cat_id', FILTER_VALIDATE_INT)) {
            $faq_cat_id = post('faq_cat_id');
            // move everything to uncategorized.
            if (dbcount("(faq_id)", DB_FAQS, "faq_cat_id=:faqcatid", [':faqcatid' => (int)$faq_cat_id]) == 0) {
                dbquery("UPDATE ".DB_FAQS." SET faq_cat_id=:uncategorized WHERE faq_cat_id=:faq_cat_id", [':faq_cat_id' => (int)$faq_cat_id, ':uncategorized' => 0]);
                dbquery("DELETE FROM ".DB_FAQ_CATS." WHERE faq_cat_id=:faq_cat_id", [':faq_cat_id' => (int)$faq_cat_id]);
                addnotice('success', $this->locale['faq_0041']);
            } else {
                addnotice('warning', $this->locale['faq_0035']);
                addnotice('warning', $this->locale['faq_0036']);
            }

            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Clear
        if (check_post('faq_clear')) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Search
        $search_string = [];
        $sql_condition = multilang_table("FQ") ? in_group('faq_language', LANGUAGE) : "";
        if (check_post('p-submit-faq_answer')) {
            $search_string['faq_answer'] = [
                'input' => sanitizer('faq_answer', '', 'faq_answer'), 'operator' => 'LIKE'
            ];
        }

        $faq_status = post('faq_status');
        if (!empty($faq_status) && $faq_status == '1') {
            $search_string['faq_status'] = ['input' => 0, 'operator' => '='];
        }

        $faq_visibility = post('faq_visibility');
        if (!empty($faq_visibility)) {
            $search_string['faq_visibility'] = [
                'input' => sanitizer('faq_visibility', '', 'faq_visibility'), 'operator' => '='
            ];
        }

        $faq_name = post('faq_name');
        if (!empty($faq_name)) {
            $search_string['faq_name'] = [
                'input' => sanitizer('faq_name', '', 'faq_name'), 'operator' => '='
            ];
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition) {
                    $sql_condition .= " AND ";
                }
                $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        $limit = 16;
        $post_faq = post('faq_display');
        $get_faq = get('faq_display');
        if ((!empty($post_faq) && isnum($post_faq)) || (!empty($get_faq) && isnum($get_faq))) {
            $limit = (!empty($post_faq) ? $post_faq : $get_faq);
        }

        $rowstart = 0;
        $max_rows = dbcount("(faq_id)", DB_FAQS, (multilang_table("FQ") ? in_group('faq_language', LANGUAGE) : ""));
        if (!isset($_POST['faq_display'])) {
            $rowstart = get_rowstart("rowstart", $max_rows);
        }

        $criteria = [
            'criteria' => "ac.*, IF(a.faq_cat_name != '', a.faq_cat_name, '".$this->locale['faq_0010']."') 'faq_cat_name', u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join'     => "INNER JOIN ".DB_USERS." u ON u.user_id=ac.faq_name
            LEFT JOIN ".DB_FAQ_CATS." a ON a.faq_cat_id=ac.faq_cat_id",
            'where'    => $sql_condition,
            'limit'    => "LIMIT $rowstart, $limit"
        ];

        $result = self::faqData($criteria);
        // Query
        $info['limit'] = $limit;
        $info['rowstart'] = $rowstart;
        $info['max_rows'] = $max_rows;
        $info['faq_rows'] = dbrows($result);
        // Filters
        $filter_values = [
            'faq_question'   => !empty($_POST['faq_question']) ? form_sanitizer($_POST['faq_question'], '', 'faq_question') : '',
            'faq_answer'     => check_post('faq_answer') ? sanitizer('faq_answer', '', 'faq_answer') : '',
            'faq_status'     => !empty($faq_status) ? sanitizer('faq_status', 0, 'faq_status') : '',
            'faq_cat_id'     => !empty($_POST['faq_cat_id']) ? form_sanitizer($_POST['faq_cat_id'], 0, 'faq_cat_id') : '',
            'faq_visibility' => !empty($faq_visibility) ? sanitizer('faq_visibility', 0, 'faq_visibility') : '',
            'faq_name'       => !empty($faq_name) ? sanitizer('faq_name', '', 'faq_name') : '',
        ];

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        $faq_cats = dbcount("(faq_cat_id)", DB_FAQ_CATS);

        echo openform('faq_filter', 'post', FUSION_REQUEST);
        echo "<div class='clearfix'>\n";
        echo "<div class='pull-right'>\n";
        if ($faq_cats) {
            echo "<a class='btn btn-success btn-sm' href='".clean_request('ref=faq_form', ['ref'], FALSE)."'><i class='fa fa-plus'></i> ".$this->locale['faq_0003']."</a>\n";
        }
        echo "<a class='m-l-5 btn btn-primary btn-sm' href='".clean_request('ref=faq_cat_form', ['ref'], FALSE)."'><i class='fa fa-plus'></i> ".$this->locale['faq_0119']."</a>
            <a class='m-l-5 btn btn-default btn-sm hidden-xs' onclick=\"run_admin('publish', '#table_action', '#faq_table');\"><i class='fa fa-check'></i> ".$this->locale['publish']."</a>
            <a class='m-l-5 btn btn-default btn-sm hidden-xs' onclick=\"run_admin('unpublish', '#table_action', '#faq_table');\"><i class='fa fa-ban'></i> ".$this->locale['unpublish']."</a>
            <a class='m-l-5 btn btn-danger btn-sm hidden-xs' onclick=\"run_admin('delete', '#table_action', '#faq_table');\"><i class='fa fa-trash-o'></i> ".$this->locale['delete']."</a>
        </div><div class='display-inline-block pull-left m-r-10'>
        ".form_text('faq_answer', '', $filter_values['faq_answer'], [
                'placeholder'       => $this->locale['faq_0120'],
                'append_button'     => TRUE,
                'append_value'      => '<i class=\'fa fa-search\'></i>',
                'append_form_value' => 'search_faq',
                'width'             => '160px',
                'group_size'        => 'sm'
            ])."
        </div>
        <div class='display-inline-block va hidden-xs'>
            <a class='btn btn-sm m-r-15 ".($filter_empty ? 'btn-default' : 'btn-info')."' id='toggle_options' href='#'>".$this->locale['faq_0120']."
                <span id='filter_caret' class='fa fa-fw ".($filter_empty ? 'fa-caret-down' : 'fa-caret-up')."'></span>
            </a>
            ".form_button('faq_clear', $this->locale['faq_0122'], 'clear', ['class' => 'btn-default btn-sm'])."
        </div>
        </div>
        <div id='faq_filter_options' ".($filter_empty ? ' style=\'display: none;\'' : '').">
        <div class='display-inline-block'>
        ".form_select('faq_status', '', $filter_values['faq_status'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$this->locale['faq_0123'].' -',
                'options'     => [0 => $this->locale['faq_0124'], 1 => $this->locale['draft']]
            ])."
        </div>
        <div class='display-inline-block'>
        ".form_select('faq_visibility', '', $filter_values['faq_visibility'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$this->locale['faq_0125'].' -',
                'options'     => fusion_get_groups()
            ])."
        </div><div class='display-inline-block'>\n";
        $author_opts = [0 => $this->locale['faq_0131']];
        $result0 = dbquery('
            SELECT n.faq_name, u.user_id, u.user_name, u.user_status
            FROM '.DB_FAQS.' n
            LEFT JOIN '.DB_USERS.' u on n.faq_name = u.user_id
            GROUP BY u.user_id
            ORDER BY user_name ASC
        ');
        if (dbrows($result0) > 0) {
            while ($data = dbarray($result0)) {
                $author_opts[$data['user_id']] = $data['user_name'];
            }
        }
        echo form_select('faq_name', '', $filter_values['faq_name'], [
            'allowclear'  => TRUE,
            'placeholder' => '- '.$this->locale['faq_0130'].' -',
            'options'     => $author_opts
        ]);
        echo "</div>\n</div>\n";
        echo closeform();

        echo openform('faq_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action');
        echo "<div class='display-block'>\n";

        // Category Management
        $cat_options = [];
        $cat_result = dbquery("SELECT * FROM ".DB_FAQ_CATS.(multilang_table('FQ') ? " WHERE ".in_group('faq_cat_language', LANGUAGE)." " : '')."ORDER BY faq_cat_name ASC");
        if (dbrows($cat_result)) {
            echo "<div class='well'>\n";
            while ($cat_data = dbarray($cat_result)) {
                $cat_options[$cat_data['faq_cat_id']] = $cat_data['faq_cat_name'];
            }

            echo "<div class='row'>\n";
            echo "<div class='col-xs-12 col-sm-6'>\n";
            echo form_select('faq_cat_id', $this->locale['faq_0009'], '', ['inline' => TRUE, 'options' => $cat_options, 'class' => 'm-b-0']);
            echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
            echo form_button('edit_faq_cat', $this->locale['edit'], $this->locale['edit'], ['class' => 'btn-default btn-sm']);
            echo form_button('delete_faq_cat', $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-danger btn-sm']);
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
        }

        echo "<div class='table-responsive'><table class='table table-hover table-striped'>
            <thead>
            <tr>
            <th class='hidden-xs'></th>
            <th>".$this->locale['faq_0100']."</td>
            <th>".$this->locale['faq_0102']."</th>
            <th>".$this->locale['faq_0252']."</th>
            <th>".$this->locale['faq_0105']."</th>
            <th>".$this->locale['faq_0106']."</th>
            <th>".$this->locale['faq_0107']."</th>
            </tr></thead>\n<tbody>\n";
        if (dbrows($result) > 0) {
            $_trash = ['section', 'ref', 'action', 'cat_id', 'faq_id', 'faq_cat_id'];
            while ($cdata = dbarray($result)) {

                $edit_link = clean_request("section=faq&ref=faq_form&action=edit&faq_id=".$cdata['faq_id'], $_trash, FALSE);
                $delete_link = clean_request("section=faq&ref=faq_form&action=delete&faq_id=".$cdata['faq_id'], $_trash, FALSE);
                $cat_edit_link = clean_request('section=faq&ref=faq_cat_form&action=edit&cat_id='.$cdata['faq_cat_id'], $_trash, FALSE);
                echo "<tr data-id='".$cdata['faq_cat_id']."'>
                        <td class='hidden-xs'>".form_checkbox("faq_id[]", "", "", ['input_id' => 'faq'.$cdata['faq_id'], "value" => $cdata['faq_id'], "class" => "m-0"])."</td>
                        <td><a href='$edit_link'>".$cdata['faq_question']."</a></td>
                        <td>".($cdata['faq_status'] ? $this->locale['no'] : $this->locale['yes'])."</td>
                        <td>".($cdata['faq_cat_id'] ? "<a href='$cat_edit_link'>" : "").$cdata['faq_cat_name'].($cdata['faq_cat_id'] ? "</a>" : "")."</td>
                        <td>
                        <div class='pull-left'>".display_avatar($cdata, '20px', '', FALSE, 'img-rounded m-r-5')."</div>
                        <div class='overflow-hide'>".profile_link($cdata['user_id'], $cdata['user_name'], $cdata['user_status'])."</div>
                        </td>
                        <td><span class='badge'>".getgroupname($cdata['faq_visibility'])."</span></td>
                        <td>
                        <a href='$edit_link' title='".$this->locale['edit']."'>".$this->locale['edit']."</a>&nbsp;&middot;&nbsp;
                        <a href='$delete_link' title='".$this->locale['delete']."' onclick=\"return confirm('".$this->locale['faq_0111']."')\">".$this->locale['delete']."</a>
                        </td></tr>\n";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>".($faq_cats ? $this->locale['faq_0112'] : $this->locale['faq_0114'])."</td></tr>";
        }
        echo "</tbody>\n</table>\n</div>";
        echo "<div class='display-inline-block'>\n
        ".form_select('faq_display', $this->locale['faq_0132'], $info['limit'], [
                'width'       => '70px',
                'inner_width' => '70px',
                'options'     => [5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50]])."
        </div>";
        if ($info['max_rows'] > $info['faq_rows']) {
            echo "<div class='display-inline-block pull-right'>".makepagenav($info['rowstart'], $info['limit'], $info['max_rows'], 3, FUSION_SELF.fusion_get_aidlink().'&amp;faq_display='.$info['limit'].'&amp;')."</div>";
        }
        echo "</div>\n";
        echo closeform();

        // jQuery
        add_to_jquery("
            // Toggle Filters
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#faq_filter_options').slideToggle();
                var caret_status = $('#filter_caret').hasClass('fa-caret-down');
                if (caret_status == 1) {
                    $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $(this).removeClass('btn-default').addClass('btn-info');
                } else {
                    $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $(this).removeClass('btn-info').addClass('btn-default');
                }
            });
            // Select Change
            $('#faq_status, #faq_visibility, #faq_name, #faq_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");

    }
}
