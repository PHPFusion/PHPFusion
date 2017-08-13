<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/admin/controllers/faq.inc
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
namespace PHPFusion\FAQ;

class FaqAdmin extends FaqAdminModel {

    private static $instance = NULL;
    private $locale = array();
    private $faq_data = array();
    private $form_action = FUSION_REQUEST;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayFaqAdmin() {
        pageAccess('FQ');
        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        $this->locale = self::get_faqAdminLocale();
        if (isset($_GET['ref'])) {
            switch ($_GET['ref']) {
                case 'faq_cat_form':
                    $this->display_faq_category_form();
                    break;
                case 'faq_form':
                    $this->display_faq_form();
                    break;
                default:
                    $this->display_faq_listing();
            }
        } else {
            $this->display_faq_listing();
        }

    }

    /**
     * Displays Faq Form
     */
    private function display_faq_form() {

        // Delete
        self::execute_Delete();

        // Update
        self::execute_Update();

        /**
         * Global vars
         */
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['faq_id']) && isnum($_POST['faq_id'])) || (isset($_GET['faq_id']) && isnum($_GET['faq_id']))) {

            $id = (!empty($_POST['faq_id']) ? $_POST['faq_id'] : $_GET['faq_id']);
            $criteria = array(
                'criteria' => "ac.*, u.user_id, u.user_name, u.user_status, u.user_avatar",
                'join'     => "LEFT JOIN ".DB_USERS." AS u ON u.user_id=ac.faq_name",
                'where'    => "ac.faq_id='$id'".(multilang_table("FQ") ? " AND ac.faq_language='".LANGUAGE."'" : ""),

            );
            $result = self::FaqData($criteria);
            if (dbrows($result) > 0) {
                $this->faq_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        } else {
            $this->faq_data = $this->default_data;
            $this->faq_data['faq_breaks'] = (fusion_get_settings('tinymce_enabled') ? 'n' : 'y');
        }
        self::faqContent_form();
    }

    private static function FaqData(array $filters = array()) {

        $result = dbquery("SELECT ".(!empty($filters['criteria']) ? $filters['criteria'] : "")."
            FROM ".DB_FAQS." ac
			".(!empty($filters['join']) ? $filters['join'] : "")."
            WHERE ".(!empty($filters['where']) ? $filters['where'] : "").
            (!empty($filters['sql_condition']) ? $filters['sql_condition'] : "")."
            GROUP BY ac.faq_id
            ORDER BY ac.faq_cat_id ASC, ac.faq_id ASC
            ".(!empty($filters['limit']) ? $filters['limit'] : "")."
        ");

        return $result;
    }

    /**
     * Create or Update
     */
    private function execute_Update() {

        if ((isset($_POST['save'])) or (isset($_POST['save_and_close']))) {

            // Check posted Informations
            $faq_answer = '';
            if ($_POST['faq_answer']) {
                $faq_answer = fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['faq_answer']) : stripslashes($_POST['faq_answer']);
            }

            $this->faq_data = array(
                'faq_id'         => form_sanitizer($_POST['faq_id'], 0, 'faq_id'),
                'faq_question'   => form_sanitizer($_POST['faq_question'], '', 'faq_question'),
                'faq_cat_id'     => isset($_POST['faq_cat_id']) ? form_sanitizer($_POST['faq_cat_id'], 0, 'faq_cat_id') : 0,
                'faq_answer'     => form_sanitizer($faq_answer, '', 'faq_answer'),
                'faq_datestamp'  => form_sanitizer($_POST['faq_datestamp'], '', 'faq_datestamp'),
                'faq_visibility' => form_sanitizer($_POST['faq_visibility'], 0, 'faq_visibility'),
                'faq_status'     => isset($_POST['faq_status']) ? '1' : '0',
                'faq_language'   => form_sanitizer($_POST['faq_language'], LANGUAGE, 'faq_language'),
            );

            // Line Breaks
            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->faq_data['faq_breaks'] = isset($_POST['faq_breaks']) ? "y" : "n";
            } else {
                $this->faq_data['faq_breaks'] = "n";
            }

            // Handle
            if (\defender::safe()) {
                // Update
                if (dbcount("(faq_id)", DB_FAQS, "faq_id='".$this->faq_data['faq_id']."'")) {
                    $this->faq_data['faq_datestamp'] = isset($_POST['update_datestamp']) ? time() : $this->faq_data['faq_datestamp'];
                    dbquery_insert(DB_FAQS, $this->faq_data, "update");
                    addNotice("success", $this->locale['faq_0031']);

                    // Create
                } else {
                    $this->faq_data['faq_name'] = fusion_get_userdata("user_id");
                    $this->faq_data['article_id'] = dbquery_insert(DB_FAQS, $this->faq_data, "save");
                    addNotice("success", $this->locale['faq_0030']);
                }

                // Redirect
                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request("", array("ref", "action", "faq_id"), FALSE));
                } else {
                    redirect(FUSION_REQUEST);
                }
            }
        }
    }

    /**
     * Display Form
     */
    private function faqContent_form() {

        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $faqExtendedSettings = array(
                'required'    => TRUE,
                'preview'     => TRUE,
                'html'        => TRUE,
                'autosize'    => TRUE,
                'placeholder' => $this->locale['faq_0253'],
                'error_text'  => $this->locale['faq_0271'],
                'form_name'   => 'faqform',
                'wordcount'   => TRUE
            );
        } else {
            $faqExtendedSettings = array(
                'required'   => TRUE,
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['faq_0271']
            );
        }

        // Start Form
        echo openform('faqform', 'post', $this->form_action, ['class' => 'spacer-sm']);
        echo form_hidden('faq_id', '', $this->faq_data['faq_id']);
        ?>

        <!-- Display Form -->
        <div class='row'>
            <!-- Display Left Column -->
            <div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>
                <?php
                echo form_text("faq_question", $this->locale['faq_0100'], $this->faq_data['faq_question'], array(
                    'required'   => TRUE,
                    'max_length' => 200,
                    'error_text' => $this->locale['faq_0271']
                ));
                echo form_textarea("faq_answer", $this->locale['faq_0251'], $this->faq_data['faq_answer'], $faqExtendedSettings);
                ?>
            </div>
            <!-- Display Right Column -->
            <div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>
                <?php
                openside($this->locale['faq_0259']);
                $options = [];
                $faq_result = dbquery("SELECT faq_cat_id, faq_cat_name FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name ASC");
                if (dbrows($faq_result)) {
                    $options[0] = $this->locale['faq_0010'];
                    while ($faq_data = dbarray($faq_result)) {
                        $options[$faq_data['faq_cat_id']] = $faq_data['faq_cat_name'];
                    }
                }
                echo form_select('faq_cat_id', $this->locale['faq_0252'], $this->faq_data['faq_cat_id'], array(
                    'inner_width' => '100%',
                    'inline'      => TRUE,
                    'options'     => $options,
                ));
                echo form_select('faq_visibility', $this->locale['faq_0106'], $this->faq_data['faq_visibility'], array(
                    'options'     => fusion_get_groups(),
                    'placeholder' => $this->locale['choose'],
                    'inner_width' => '100%',
                    'inline'      => TRUE,
                ));
                if (multilang_table('FQ')) {
                    echo form_select("faq_language", $this->locale['language'], $this->faq_data['faq_language'], array(
                        'options'     => fusion_get_enabled_languages(),
                        'placeholder' => $this->locale['choose'],
                        'inner_width' => '100%',
                        'inline'      => TRUE,
                    ));
                } else {
                    echo form_hidden('faq_language', '', $this->faq_data['faq_language']);
                }
                echo form_hidden('faq_datestamp', '', $this->faq_data['faq_datestamp']);
                if (!empty($_GET['action']) && $_GET['action'] == 'edit') {
                    echo form_checkbox('update_datestamp', $this->locale['faq_0257'], '');
                }
                closeside();
                openside($this->locale['faq_0258']);
                echo form_checkbox('faq_status', $this->locale['faq_0255'], $this->faq_data['faq_status'], array(
                    'class'         => 'm-b-5',
                    'reverse_label' => TRUE
                ));

                if (fusion_get_settings("tinymce_enabled") != 1) {
                    echo form_checkbox("faq_breaks", $this->locale['faq_0256'], $this->faq_data['faq_breaks'], array(
                        'value'         => 'y',
                        'class'         => 'm-b-5',
                        'reverse_label' => TRUE
                    ));
                }
                self::display_faqButtons('formend', false);
                closeside();
                ?>

            </div>
        </div>
        <?php
        self::display_faqButtons('formend', false);
        echo closeform();
    }

    private $cat_data = [
        'faq_cat_id'          => 0,
        'faq_cat_name'        => '',
        'faq_cat_description' => '',
        'faq_cat_language'    => LANGUAGE,
    ];

    private function display_faq_category_form() {

        if (isset($_POST['save_cat'])) {
            $this->cat_data = [
                'faq_cat_id'          => form_sanitizer($_POST['faq_cat_id'], '', 'faq_cat_id'),
                'faq_cat_name'        => form_sanitizer($_POST['faq_cat_name'], '', 'faq_cat_name'),
                'faq_cat_description' => form_sanitizer($_POST['faq_cat_description'], '', 'faq_cat_description'),
                'faq_cat_language'    => form_sanitizer($_POST['faq_cat_language'], '', 'faq_cat_language'),
            ];

            if (\defender::safe()) {
                if ($this->cat_data['faq_cat_id']) {
                    dbquery_insert(DB_FAQ_CATS, $this->cat_data, 'update');
                    addNotice('success', $this->locale['faq_0040']);
                } else {
                    if (!dbcount("(faq_cat_id)", DB_FAQ_CATS, "faq_cat_name=:faq_cat_name", [':faq_cat_name' => $this->cat_data['faq_cat_name']])) {
                        dbquery_insert(DB_FAQ_CATS, $this->cat_data, 'save');
                        addNotice('success', $this->locale['faq_0039']);
                    } else {
                        \defender::stop();
                        \defender::inputHasError('faq_cat_name');
                        addNotice('warning', $this->locale['faq_0042']);
                    }
                }
                redirect(clean_request('', ['ref', 'cat_id', 'action'], FALSE));
            }

        }

        if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
            $result = dbquery("SELECT * FROM ".DB_FAQ_CATS." WHERE faq_cat_id=:faq_cat_id", [':faq_cat_id' => $_GET['cat_id']]);
            if (dbrows($result) > 0) {
                $this->cat_data = dbarray($result);
            } else {
                redirect(clean_request('', ['ref'], FALSE));
            }
        }
        echo openform('add_faq_cat', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo form_hidden('faq_cat_id', '', $this->cat_data['faq_cat_id']);
        echo form_text('faq_cat_name', $this->locale['faq_0115'], $this->cat_data['faq_cat_name'], array('error_text' => $this->locale['460'], 'required' => TRUE, 'inline' => TRUE));
        echo form_textarea('faq_cat_description', $this->locale['faq_0116'], $this->cat_data['faq_cat_description'], ['autosize' => TRUE, 'inline' => TRUE]);
        if (multilang_table("FQ")) {
            echo form_select('faq_cat_language', $this->locale['faq_0117'], $this->cat_data['faq_cat_language'], array(
                'options'     => fusion_get_enabled_languages(),
                'inline'      => TRUE,
                'placeholder' => $this->locale['choose']));
        } else {
            echo form_hidden('cat_language', '', LANGUAGE);
        }
        echo form_button('save_cat', $this->locale['faq_0118'], $this->locale['faq_0118'], array('class' => 'btn-primary m-t-10'));
        echo closeform();
    }

    /**
     * Generate sets of push buttons for Content form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function display_faqButtons($unique_id, $breaker = true) {
        ?>
        <div class="m-t-20">
            <?php echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default", "icon" => "fa fa-fw fa-times", "input-id" => "cancel-".$unique_id."")); ?>
            <?php echo form_button("save", $this->locale['save'], $this->locale['save'], array("class" => "btn-success", "icon" => "fa fa-fw fa-hdd-o", "input-id" => "save-".$unique_id."")); ?>
            <?php echo form_button("save_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], array("class" => "btn-primary", "icon" => "fa fa-fw fa-floppy-o", "input-id" => "save_and_close-".$unique_id."")); ?>
        </div>
        <?php if ($breaker) { ?>
            <hr/><?php } ?>
        <?php
    }

    /**
     * Displays Listing
     */
    private function display_faq_listing() {
        // Run functions
        $allowed_actions = array_flip(array('publish', 'unpublish', 'delete', 'faq_display'));

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
            $input = (isset($_POST['faq_id'])) ? explode(",", form_sanitizer($_POST['faq_id'], "", "faq_id")) : "";
            if (!empty($input)) {
                foreach ($input as $faq_id) {
                    // check input table
                    if (dbcount("('faq_id')", DB_FAQS, "faq_id='".intval($faq_id)."'") && \defender::safe()) {
                        switch ($_POST['table_action']) {
                            case 'publish':
                                dbquery("UPDATE ".DB_FAQS." SET faq_status='1' WHERE faq_id='".intval($faq_id)."'");
                                addNotice("success", $this->locale['faq_0037']);
                                break;
                            case 'unpublish':
                                dbquery("UPDATE ".DB_FAQS." SET faq_status='0' WHERE faq_id='".intval($faq_id)."'");
                                addNotice("warning", $this->locale['faq_0038']);
                                break;
                            case 'delete':
                                if (!dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".$faq_id."'")) {
                                    dbquery("DELETE FROM  ".DB_FAQS." WHERE faq_id='".intval($faq_id)."'");
                                    addNotice("warning", $this->locale['faq_0032']);
                                } else {
                                    addNotice("warning", $this->locale['faq_0035']);
                                    addNotice("warning", $this->locale['faq_0036']);
                                }
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", $this->locale['faq_0034']);
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['edit_faq_cat']) && isset($_POST['faq_cat_id']) && isnum($_POST['faq_cat_id'])) {
            redirect(clean_request('cat_id='.$_POST['faq_cat_id'].'&action=edit&ref=faq_cat_form', ['action', 'cat_id', 'ref'], FALSE));
        }

        // delete category
        if (isset($_POST['delete_faq_cat']) && isset($_POST['faq_cat_id']) && isnum($_POST['faq_cat_id'])) {
            // move everything to uncategorized.
            dbquery("UPDATE ".DB_FAQS." SET faq_cat_id=:uncategorized WHERE faq_cat_id=:faq_cat_id", [':faq_cat_id' => $_POST['faq_cat_id'], ':uncategorized' => 0]);
            dbquery("DELETE FROM ".DB_FAQ_CATS." WHERE faq_cat_id=:faq_cat_id", [':faq_cat_id' => $_POST['faq_cat_id']]);
            addNotice('success', $this->locale['faq_0041']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Clear
        if (isset($_POST['faq_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Search
        $sql_condition = '';
        $search_string = array();
        if (isset($_POST['p-submit-faq_text'])) {
            $search_string['ac.faq_answer'] = array(
                'input' => form_sanitizer($_POST['faq_answer'], '', 'faq_answer'), 'operator' => 'LIKE'
            );
        }

        if (!empty($_POST['faq_status']) && isnum($_POST['faq_status']) && $_POST['faq_status'] == '1') {
            $search_string['ac.faq_status'] = array('input' => 0, 'operator' => '=');
        }

        if (!empty($_POST['faq_visibility'])) {
            $search_string['ac.faq_visibility'] = array(
                'input' => form_sanitizer($_POST['faq_visibility'], '', 'faq_visibility'), 'operator' => '='
            );
        }

        if (!empty($_POST['faq_language'])) {
            $search_string['ac.faq_language'] = array(
                'input' => form_sanitizer($_POST['faq_language'], '', 'faq_language'), 'operator' => '='
            );
        }

        if (!empty($_POST['faq_name'])) {
            $search_string['ac.faq_name'] = array(
                'input' => form_sanitizer($_POST['faq_name'], '', 'faq_name'), 'operator' => '='
            );
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                $sql_condition .= " AND $key".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        $default_display = 16;
        $limit = $default_display;
        if ((!empty($_POST['faq_display']) && isnum($_POST['faq_display'])) || (!empty($_GET['faq_display']) && isnum($_GET['faq_display']))) {
            $limit = (!empty($_POST['faq_display']) ? $_POST['faq_display'] : $_GET['faq_display']);
        }

        $rowstart = 0;
        $max_rows = dbcount("(faq_id)", DB_FAQS, (multilang_table("FQ") ? "faq_language='".LANGUAGE."'" : ""));
        if (!isset($_POST['faq_display'])) {
            $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
        }

        $criteria = array(
            'criteria' => "ac.*, IF(a.faq_cat_name != '', a.faq_cat_name, '".$this->locale['faq_0010']."') 'faq_cat_name', u.user_id, u.user_name, u.user_status, u.user_avatar",
            'join'     => "INNER JOIN ".DB_USERS." u ON u.user_id=ac.faq_name 
            LEFT JOIN ".DB_FAQ_CATS." a ON a.faq_cat_id=ac.faq_cat_id",
            'where'    => (multilang_table("FQ") ? "ac.faq_language='".LANGUAGE."'" : "").$sql_condition,
            //'sql_condition' => ,
            'limit'    => "LIMIT $rowstart, $limit"
        );

        $result = self::FaqData($criteria);
        // Query

        $info['limit'] = $limit;
        $info['rowstart'] = $rowstart;
        $info['max_rows'] = $max_rows;
        $info['faq_rows'] = dbrows($result);
        // Filters
        $filter_values = array(
            'faq_question'   => !empty($_POST['faq_question']) ? form_sanitizer($_POST['faq_question'], '', 'faq_question') : '',
            'faq_answer'     => !empty($_POST['faq_answer']) ? form_sanitizer($_POST['faq_answer'], '', 'faq_answer') : '',
            'faq_status'     => !empty($_POST['faq_status']) ? form_sanitizer($_POST['faq_status'], '', 'faq_status') : '',
            'faq_cat_id'     => !empty($_POST['faq_cat_id']) ? form_sanitizer($_POST['faq_cat_id'], '', 'faq_cat_id') : '',
            'faq_visibility' => !empty($_POST['faq_visibility']) ? form_sanitizer($_POST['faq_visibility'], '', 'faq_visibility') : '',
            'faq_language'   => !empty($_POST['faq_language']) ? form_sanitizer($_POST['faq_language'], '', 'faq_language') : '',
            'faq_name'       => !empty($_POST['faq_name']) ? form_sanitizer($_POST['faq_name'], '', 'faq_name') : '',
        );

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        echo "<div class='m-t-15'>\n";
        echo openform('faq_filter', 'post', FUSION_REQUEST);
        echo "<div class='clearfix'>\n";
        echo "<div class='pull-right'>\n";
        if ($result) {
            echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request('ref=faq_form', array('ref'), false)."'>
                        <i class='fa fa-fw fa-plus'></i>".$this->locale['faq_0003']."</a>\n";
        }
        echo "<a class='btn btn-primary btn-sm m-r-10' href='".clean_request('ref=faq_cat_form', ['ref'], FALSE)."'><i class='fa fa-plus fa-fw'></i> ".$this->locale['faq_0119']."</a>
            <a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin(' publish');\"><i class='fa fa-fw fa-check'></i>".$this->locale['publish']."</a>
            <a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin(' unpublish');\"><i class='fa fa-fw fa-ban'></i>".$this->locale['unpublish']."</a>
            <a class='btn btn-danger btn-sm m-r-10' onclick=\"run_admin(' delete');\"><i class='fa fa-fw fa-trash-o'></i>".$this->locale['delete']."</a>
        </div><div class='display-inline-block pull-left m-r-10' style='width: 300px;'>
        ".form_text('faq_answer', '', $filter_values['faq_answer'], array(
                'placeholder'       => $this->locale['faq_0120'],
                'append_button'     => TRUE,
                'append_value'      => '<i class=\'fa fa-search\'></i>',
                'append_form_value' => 'search_faq',
                'width'             => '250px',
                'inner_width'       => '250px',
                'group_size'        => 'sm'
            ))."
        </div>
        <div class='display-inline-block va'>
            <a class='btn btn-sm m-r-15 ".($filter_empty ? 'btn-default' : 'btn-info')."' id='toggle_options' href='#'>".$this->locale['faq_0120']."
                <span id='filter_caret' class='fa fa-fw ".($filter_empty ? 'fa-caret-down' : 'fa-caret-up')."'></span>
            </a>
            ".form_button('faq_clear', $this->locale['faq_0122'], 'clear', array('class' => 'btn-default btn-sm'))."
        </div>
        </div>
        <div id='faq_filter_options'".($filter_empty ? ' style=\'display: none;\'' : '').">
        <div class='display-inline-block'>
        ".form_select('faq_status', '', $filter_values['faq_status'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$this->locale['faq_0123'].' -',
                'options'     => array(0 => $this->locale['faq_0124'], 1 => $this->locale['draft'])
            ])."
        </div>
        <div class='display-inline-block'>
        ".form_select('faq_visibility', '', $filter_values['faq_visibility'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$this->locale['faq_0125'].' -',
                'options'     => fusion_get_groups()
            ])."
        </div>
        <div class='display-inline-block'>\n";
        $language_opts = array(0 => $this->locale['faq_0129']);
        $language_opts += fusion_get_enabled_languages();
        echo form_select('faq_language', '', $filter_values['faq_language'], [
            'allowclear'  => TRUE,
            'placeholder' => '- '.$this->locale['faq_0128'].' -',
            'options'     => $language_opts
        ]);
        echo "</div><div class='display-inline-block'>\n";
        $author_opts = array(0 => $this->locale['faq_0131']);
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
        echo form_select('faq_name', '', $filter_values['faq_name'], array(
            'allowclear'  => TRUE,
            'placeholder' => '- '.$this->locale['faq_0130'].' -',
            'options'     => $author_opts
        ));
        echo "</div>\n</div>\n";
        echo closeform();
        echo "</div>\n";

        echo openform('faq_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action', '', '');
        echo "<div class='display-block'>\n";

        // Category Management
        $cat_result = dbquery("SELECT * FROM ".DB_FAQ_CATS.(multilang_table('FQ') ? " WHERE faq_cat_language='".LANGUAGE."' " : '')."ORDER BY faq_cat_name ASC");
        if (dbrows($cat_result)) {
            echo "<div class='well'>\n";
            while ($cat_data = dbarray($cat_result)) {
                $cat_options[$cat_data['faq_cat_id']] = $cat_data['faq_cat_name'];
            }

                echo "<div class='row'>\n";
                    echo "<div class='col-xs-12 col-sm-6'>\n";
                    echo form_select('faq_cat_id', $this->locale['faq_0009'], '', array('inline' => TRUE, 'options' => $cat_options, 'class' => 'm-b-0'));
                    echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
                    echo form_button('edit_faq_cat', $this->locale['edit'], $this->locale['edit']);
                    echo form_button('delete_faq_cat', $this->locale['delete'], $this->locale['delete'], ['class' => 'btn-danger']);
                    echo "</div>\n";
                echo "</div>\n";
            echo "</div>\n";
        }

        echo "<div class='table-responsive'><table class='table table-hover table-striped'>
            <thead>
            <tr><th></th>
            <th>".$this->locale['faq_0100']."</td>
            <th>".$this->locale['faq_0102']."</th>
            <th>".$this->locale['faq_0252']."</th>
            <th>".$this->locale['faq_0105']."</th>
            <th>".$this->locale['faq_0106']."</th>
            <th>".$this->locale['language']."</th>
            <th>".$this->locale['faq_0107']."</th>
            </tr></thead>\n<tbody>\n";
        if (dbrows($result) > 0) {
            $_trash = ['section', 'ref', 'action', 'cat_id', 'faq_id', 'faq_cat_id'];
            while ($cdata = dbarray($result)) {

                $edit_link = clean_request("section=faq&ref=faq_form&action=edit&faq_id=".$cdata['faq_id'], $_trash, FALSE);
                $delete_link = clean_request("section=faq&amp;ref=faq_form&amp;action=delete&amp;faq_id=".$cdata['faq_id'], $_trash, FALSE);
                $cat_edit_link = clean_request('section=faq&ref=faq_cat_form&action=edit&cat_id='.$cdata['faq_cat_id'], $_trash, FALSE);
                echo "<tr data-id='".$cdata['faq_cat_id']."'>
                        <td>".form_checkbox("faq_id[]", "", "", array("value" => $cdata['faq_id'], "class" => "m-0"))."</td>
                        <td><a href='$edit_link'>".$cdata['faq_question']."</a></td>
                        <td>".($cdata['faq_status'] ? $this->locale['no'] : $this->locale['yes'])."</td>
                        <td>".($cdata['faq_cat_id'] ? "<a href='$cat_edit_link'>" : "").$cdata['faq_cat_name'].($cdata['faq_cat_id'] ? "</a>" : "")."</td>
                        <td>
                        <div class='pull-left'>".display_avatar($cdata, '20px', '', FALSE, 'img-rounded m-r-5')."</div>
                        <div class='overflow-hide'>".profile_link($cdata['user_id'], $cdata['user_name'], $cdata['user_status'])."</div>
                        </td>
                        <td><span class='badge'>".getgroupname($cdata['faq_visibility'])."</span></td>
                        <td>".translate_lang_names($cdata['faq_language'])."</td>
                        <td>
                        <a href='$edit_link' title='".$this->locale['edit']."'>".$this->locale['edit']."</a>&nbsp;&middot;&nbsp;
                        <a href='$delete_link' title='".$this->locale['delete']."' onclick=\"return confirm('".$this->locale['faq_0111']."')\">".$this->locale['delete']."</a>
                        </td></tr>\n";
            }
        } else {
            echo "<tr>
                    <td colspan='8' class='text-center'>
                        ".$this->locale['faq_0112']."
                    </td>
                </tr>
                ";
        }
        echo "</tbody>\n</table>\n</div>";
        echo "<div class='display-inline-block'>\n
        ".form_select('faq_display', $this->locale['faq_0132'], $info['limit'], array(
                'width'       => '70px',
                'inner_width' => '70px',
                'options'     => array(5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50)))."
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
            $('#faq_status, #faq_visibility, #faq_language, #faq_name, #faq_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");

        // Javascript
        add_to_footer("
            <script type='text/javascript'>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#faq_table').submit();
                }
            </script>
        ");
    }

    // Delete Function
    private function execute_Delete() {
        if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['faq_id']) && isnum($_GET['faq_id'])) {
            $faq_id = intval($_GET['faq_id']);
            if (dbcount("(faq_id)", DB_FAQS, "faq_id='$faq_id'") && !dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".$faq_id."'")) {
                dbquery("DELETE FROM  ".DB_FAQS." WHERE faq_id='".intval($faq_id)."'");
                addNotice("success", $this->locale['faq_0032']);
            } else {
                addNotice("warning", $this->locale['faq_0035']);
                addNotice("warning", $this->locale['faq_0036']);
            }
            redirect(clean_request("", array("ref", "action", "cat_id"), FALSE));
        }
    }

}
