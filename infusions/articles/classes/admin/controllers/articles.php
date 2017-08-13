<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/admin/controllers/articles.php
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

class ArticlesAdmin extends ArticlesAdminModel {

    private static $instance = NULL;
    private $locale = array();
    private $form_action = FUSION_REQUEST;
    private $articleSettings = array();
    private $article_data = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayArticlesAdmin() {
        pageAccess("A");
        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        $this->locale = self::get_articleAdminLocale();
        $this->articleSettings = self::get_article_settings();

        if (isset($_GET['ref']) && $_GET['ref'] == "article_form") {
            $this->display_article_form();
        } else {
            $this->display_article_listing();
        }
    }

    /**
     * Displays Articles Form
     */
    private function display_article_form() {

        // Delete Article
        self::execute_ArticlesDelete();

        // Update Article
        self::execute_ArticlesUpdate();

        /**
         * Global vars
         */
        if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
            $result = dbquery("SELECT * FROM ".DB_ARTICLES." WHERE article_id='".(isset($_POST['article_id']) ? $_POST['article_id'] : $_GET['article_id'])."'");
            if (dbrows($result)) {
                $this->article_data = dbarray($result);
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        // Data
        $this->article_data += $this->default_article_data;

        self::articleContent_form();
    }

    /**
     * Create or Update a Article
     */
    private function execute_ArticlesUpdate() {

        if ((isset($_POST['save'])) or (isset($_POST['save_and_close']))) {

            // Check posted Informations
            $article_snippet = "";
            if ($_POST['article_snippet']) {
                $article_snippet = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['article_snippet']) : $_POST['article_snippet']));
            }

            $article_article = "";
            if ($_POST['article_article']) {
                $article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['article_article']) : $_POST['article_article']));
            }

            $this->article_data = array(
                "article_id"             => form_sanitizer($_POST['article_id'], 0, "article_id"),
                "article_subject"        => form_sanitizer($_POST['article_subject'], "", "article_subject"),
                "article_cat"            => form_sanitizer($_POST['article_cat'], 0, "article_cat"),
                "article_snippet"        => form_sanitizer($article_snippet, "", "article_snippet"),
                "article_article"        => form_sanitizer($article_article, "", "article_article"),
                "article_keywords"       => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
                "article_datestamp"      => form_sanitizer($_POST['article_datestamp'], "", "article_datestamp"),
                "article_visibility"     => form_sanitizer($_POST['article_visibility'], 0, "article_visibility"),
                "article_draft"          => isset($_POST['article_draft']) ? "1" : "0",
                "article_allow_comments" => isset($_POST['article_allow_comments']) ? "1" : "0",
                "article_allow_ratings"  => isset($_POST['article_allow_ratings']) ? "1" : "0",
                "article_language"       => form_sanitizer($_POST['article_language'], LANGUAGE, "article_language"),
            );

            // Line Breaks
            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->article_data['article_breaks'] = isset($_POST['article_breaks']) ? "y" : "n";
            } else {
                $this->article_data['article_breaks'] = "n";
            }

            // Handle
            if (\defender::safe()) {
                // Update
                if (dbcount("('article_id')", DB_ARTICLES, "article_id='".$this->article_data['article_id']."'")) {
                    dbquery_insert(DB_ARTICLES, $this->article_data, "update");
                    addNotice("success", $this->locale['article_0031']);

                    // Create
                } else {
                    $this->article_data['article_name'] = fusion_get_userdata("user_id");
                    $this->article_data['article_id'] = dbquery_insert(DB_ARTICLES, $this->article_data, "save");
                    addNotice("success", $this->locale['article_0030']);
                }

                // Redirect
                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request("", array("ref", "action", "article_id"), FALSE));
                } else {
                    redirect(FUSION_REQUEST);
                }
            }
        }
    }

    /**
     * Display Form for Article
     */
    private function articleContent_form() {

        // Textarea Settings
        if (!fusion_get_settings("tinymce_enabled")) {
            $articleSnippetSettings = array(
                "required"    => true,
                "preview"     => true,
                "type"        => 'bbcode',
                "autosize"    => true,
                "placeholder" => $this->locale['article_0254'],
                "error_text"  => $this->locale['article_0271'],
                "form_name"   => "articleform",
                "wordcount"   => true,
                'path'        => array()
            );
            $articleExtendedSettings = array(
                "required"   => ($this->articleSettings['article_extended_required'] ? true : false), "preview" => true, "html" => true, "autosize" => true, "placeholder" => $this->locale['article_0253'],
                "error_text" => $this->locale['article_0272'], "form_name" => "articleform", "wordcount" => true,
                'path'       => [IMAGES, IMAGES_A]
            );
        } else {
            $articleSnippetSettings = array("required" => true, "type" => "tinymce", "tinymce" => "advanced", "error_text" => $this->locale['article_0271'], 'path' => [IMAGES, IMAGES_A]);
            $articleExtendedSettings = array("required" => ($this->articleSettings['article_extended_required'] ? true : false), "type" => "tinymce", "tinymce" => "advanced", "error_text" => $this->locale['article_0272'], 'path' => [IMAGES, IMAGES_A]);
        }

        // Start Form
        echo openform("articleform", "post", $this->form_action);
        self::display_articleButtons("formstart", true);
        echo form_hidden("article_id", "", $this->article_data['article_id']);
        echo form_text("article_subject", $this->locale['article_0100'], $this->article_data['article_subject'], array("required" => true, "max_length" => 200, "error_text" => $this->locale['article_0270']));
        echo form_select("article_keywords", $this->locale['article_0260'], $this->article_data['article_keywords'], array(
            "max_length" => 320, "placeholder" => $this->locale['article_0260a'], "width" => "100%", "inner_width" => "100%", "tags" => TRUE, "multiple" => TRUE
        ));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php
            openside($this->locale['article_0261']);
            echo form_select_tree("article_cat", $this->locale['article_0101'], $this->article_data['article_cat'], array(
                "required" => TRUE, "error_text" => $this->locale['article_0273'], "inner_width" => "100%", "inline" => TRUE, "parent_value" => $this->locale['choose'],
                "query"    => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
            ),
                DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent"
            );
            echo form_select("article_visibility", $this->locale['article_0106'], $this->article_data['article_visibility'], array(
                "options" => fusion_get_groups(), "placeholder" => $this->locale['choose'], "inner_width" => "100%", "inline" => TRUE,
            ));
            if (multilang_table("AR")) {
                echo form_select("article_language", $this->locale['language'], $this->article_data['article_language'], array(
                    "options" => fusion_get_enabled_languages(), "placeholder" => $this->locale['choose'], "inner_width" => "100%", "inline" => TRUE,
                ));
            } else {
                echo form_hidden("article_language", "", $this->article_data['article_language']);
            }
            echo form_datepicker("article_datestamp", $this->locale['article_0203'], $this->article_data['article_datestamp'], array(
                "inline" => TRUE, "inner_width" => "100%"
            ));
            closeside();
            ?>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php
            openside($this->locale['article_0262']);
            echo form_checkbox("article_draft", $this->locale['article_0256'], $this->article_data['article_draft'], array(
                "class" => "m-b-5", "reverse_label" => TRUE
            ));
            if (fusion_get_settings("tinymce_enabled") != 1) {
                echo form_checkbox("article_breaks", $this->locale['article_0257'], $this->article_data['article_breaks'], array(
                    "value" => "y", "class" => "m-b-5", "reverse_label" => TRUE
                ));
            }
            echo form_checkbox("article_allow_comments", $this->locale['article_0258'], $this->article_data['article_allow_comments'], array(
                "class"   => "m-b-5", "reverse_label" => TRUE,
                "ext_tip" => (!fusion_get_settings("comments_enabled") ? "<div class='alert alert-warning'>".sprintf($this->locale['article_0274'], $this->locale['comments'])."</div>" : "")
            ));
            echo form_checkbox("article_allow_ratings", $this->locale['article_0259'], $this->article_data['article_allow_ratings'], array(
                "class"   => "m-b-5", "reverse_label" => TRUE,
                "ext_tip" => (!fusion_get_settings("ratings_enabled") ? "<div class='alert alert-warning'>".sprintf($this->locale['article_0274'], $this->locale['ratings'])."</div>" : "")
            ));
            closeside();
            ?>
            </div>
        </div><?php

        echo form_textarea("article_snippet", $this->locale['article_0251'], $this->article_data['article_snippet'], $articleSnippetSettings);
        echo form_textarea("article_article", $this->locale['article_0252'], $this->article_data['article_article'], $articleExtendedSettings);

        self::display_articleButtons("formend", false);
        echo closeform();
    }

    /**
     * Generate sets of push buttons for article Content form
     *
     * @param      $unique_id
     * @param bool $breaker
     */
    private function display_articleButtons($unique_id, $breaker = true) {
        ?>
        <div class="m-t-20">
            <?php echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default m-r-10", "icon" => "fa fa-fw fa-times", "input-id" => "cancel-".$unique_id."")); ?>
            <?php echo form_button("save", $this->locale['save'], $this->locale['save'], array("class" => "btn-success m-r-10", "icon" => "fa fa-fw fa-hdd-o", "input-id" => "save-".$unique_id."")); ?>
            <?php echo form_button("save_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], array("class" => "btn-primary m-r-10", "icon" => "fa fa-fw fa-floppy-o", "input-id" => "save_and_close-".$unique_id."")); ?>
        </div>
        <?php if ($breaker) { ?>
            <hr/><?php } ?>
        <?php
    }

    /**
     * Displays Articles Listing
     */
    private function display_article_listing() {
        // Run functions
        $allowed_actions = array_flip(array("publish", "unpublish", "delete", "article_display"));

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = (isset($_POST['article_id'])) ? explode(",", form_sanitizer($_POST['article_id'], "", "article_id")) : "";
            if (!empty($input)) {
                foreach ($input as $article_id) {
                    // check input table
                    if (dbcount("('article_id')", DB_ARTICLES, "article_id='".intval($article_id)."'") && \defender::safe()) {

                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_ARTICLES." SET article_draft='0' WHERE article_id='".intval($article_id)."'");
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_ARTICLES." SET article_draft='1' WHERE article_id='".intval($article_id)."'");
                                break;
                            case "delete":
                                dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_id='".intval($article_id)."'");
                                dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".intval($article_id)."' and comment_type='A'");
                                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".intval($article_id)."' and rating_type='A'");
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice("success", $this->locale['article_0033']);
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", $this->locale['article_0034']);
            redirect(FUSION_REQUEST);
        }

        // Clear
        if (isset($_POST['article_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Search
        $sql_condition = multilang_table("AR") ? "article_language='".LANGUAGE."'" : "";
        $search_string = array();
        if (isset($_POST['p-submit-article_text'])) {
            $search_string['article_subject'] = array(
                "input" => form_sanitizer($_POST['article_text'], "", "article_text"), "operator" => "LIKE"
            );
        }

        if (!empty($_POST['article_status']) && isnum($_POST['article_status']) && $_POST['article_status'] == "1") {
            $search_string['article_draft'] = array("input" => 1, "operator" => "=");
        }

        if (!empty($_POST['article_visibility'])) {
            $search_string['article_visibility'] = array(
                "input" => form_sanitizer($_POST['article_visibility'], "", "article_visibility"), "operator" => "="
            );
        }

        if (!empty($_POST['article_category'])) {
            $search_string['article_cat'] = array(
                "input" => form_sanitizer($_POST['article_category'], "", "article_category"), "operator" => "="
            );
        }

        if (!empty($_POST['article_language'])) {
            $search_string['article_language'] = array(
                "input" => form_sanitizer($_POST['article_language'], "", "article_language"), "operator" => "="
            );
        }

        if (!empty($_POST['article_author'])) {
            $search_string['article_name'] = array(
                "input" => form_sanitizer($_POST['article_author'], "", "article_author"), "operator" => "="
            );
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition) $sql_condition .= " AND ";
                $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        $default_display = 16;
        $limit = $default_display;
        if ((!empty($_POST['article_display']) && isnum($_POST['article_display'])) || (!empty($_GET['article_display']) && isnum($_GET['article_display']))) {
            $limit = (!empty($_POST['article_display']) ? $_POST['article_display'] : $_GET['article_display']);
        }

        $max_rows = dbcount("(article_id)", DB_ARTICLES);
        $rowstart = 0;
        if (!isset($_POST['article_display'])) {
            $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
        }

        // Query
        $article_query = "
            SELECT
                a.*, ac.*,
                count(c.comment_id) 'comments_count',
                count(r.rating_id) 'ratings_count',
                u.user_id, u.user_name, u.user_status, u.user_avatar
            FROM ".DB_ARTICLES." a
            LEFT JOIN ".DB_ARTICLE_CATS." ac ON ac.article_cat_id=a.article_cat
            LEFT JOIN ".DB_COMMENTS." c ON c.comment_item_id=a.article_id AND c.comment_type='A'
            LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id=a.article_id AND r.rating_type='A'
            INNER JOIN ".DB_USERS." u on u.user_id=a.article_name
            ".($sql_condition ? " WHERE ".$sql_condition : "")."
            GROUP BY a.article_id
            ORDER BY article_draft DESC, article_datestamp DESC
            LIMIT $rowstart, $limit
        ";
        $result2 = dbquery($article_query);
        $article_rows = dbrows($result2);
        $article_cats = dbcount("(article_cat_id)", DB_ARTICLE_CATS, "");

        // Filters
        $filter_values = array(
            "article_text"       => !empty($_POST['article_text']) ? form_sanitizer($_POST['article_text'], "", "article_text") : "",
            "article_status"     => !empty($_POST['article_status']) ? form_sanitizer($_POST['article_status'], "", "article_status") : "",
            "article_category"   => !empty($_POST['article_category']) ? form_sanitizer($_POST['article_category'], "", "article_category") : "",
            "article_visibility" => !empty($_POST['article_visibility']) ? form_sanitizer($_POST['article_visibility'], "", "article_visibility") : "",
            "article_language"   => !empty($_POST['article_language']) ? form_sanitizer($_POST['article_language'], "", "article_language") : "",
            "article_author"     => !empty($_POST['article_author']) ? form_sanitizer($_POST['article_author'], "", "article_author") : "",
        );

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        ?>
        <div class="m-t-15">
            <?php echo openform("article_filter", "post", FUSION_REQUEST); ?>

            <!-- Display Buttons and Search -->
            <div class="clearfix">
                <div class="pull-right">
                    <?php if ($article_cats) { ?>
                        <a class="btn btn-success btn-sm m-r-10"
                           href="<?php echo clean_request("ref=article_form", array("ref"), false); ?>"><i
                                    class="fa fa-fw fa-plus"></i> <?php echo $this->locale['article_0002']; ?></a>
                    <?php } ?>
                    <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('publish');"><i
                                class="fa fa-fw fa-check"></i> <?php echo $this->locale['publish']; ?></a>
                    <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('unpublish');"><i
                                class="fa fa-fw fa-ban"></i> <?php echo $this->locale['unpublish']; ?></a>
                    <a class="btn btn-danger btn-sm m-r-10" onclick="run_admin('delete');"><i
                                class="fa fa-fw fa-trash-o"></i> <?php echo $this->locale['delete']; ?></a>
                </div>

                <div class="display-inline-block pull-left m-r-10" style="width: 300px;">
                    <?php echo form_text("article_text", "", $filter_values['article_text'], array(
                        "placeholder"       => $this->locale['article_0100'],
                        "append_button"     => TRUE,
                        "append_value"      => "<i class='fa fa-search'></i>",
                        "append_form_value" => "search_article",
                        "width"             => "250px",
                        "group_size"        => "sm"
                    )); ?>
                </div>

                <div class="display-inline-block" style="vertical-align: top;">
                    <a class="btn btn-sm m-r-15 <?php echo($filter_empty ? "btn-default" : "btn-info"); ?>"
                       id="toggle_options" href="#">
                        <?php echo $this->locale['article_0121']; ?>
                        <span id="filter_caret"
                              class="fa fa-fw <?php echo($filter_empty ? "fa-caret-down" : "fa-caret-up"); ?>"></span>
                    </a>
                    <?php echo form_button("article_clear", $this->locale['article_0122'], "clear", array("class" => "btn-default btn-sm")); ?>
                </div>
            </div>

            <!-- Display Filters -->
            <div id="article_filter_options"<?php echo($filter_empty ? " style='display: none;'" : ""); ?>>
                <div class="display-inline-block">
                    <?php
                    echo form_select("article_status", "", $filter_values['article_status'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['article_0123']." -", "options" => array(0 => $this->locale['article_0124'], 1 => $this->locale['draft'])
                    ));
                    ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    echo form_select("article_visibility", "", $filter_values['article_visibility'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['article_0125']." -", "options" => fusion_get_groups()
                    ));
                    ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    echo form_select_tree("article_category", "", $filter_values['article_category'], array(
                        "query"        => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : ""),
                        "parent_value" => $this->locale['article_0127'],
                        "placeholder"  => "- ".$this->locale['article_0126']." -",
                        "allowclear"   => TRUE
                    ), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");
                    ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    $language_opts = array(0 => $this->locale['article_0129']);
                    $language_opts += fusion_get_enabled_languages();
                    echo form_select("article_language", "", $filter_values['article_language'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['article_0128']." -", "options" => $language_opts
                    ));
                    ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    $author_opts = array(0 => $this->locale['article_0131']);
                    $result = dbquery("
                        SELECT n.article_name, u.user_id, u.user_name, u.user_status
                        FROM ".DB_ARTICLES." n
                        LEFT JOIN ".DB_USERS." u on n.article_name = u.user_id
                        GROUP BY u.user_id
                        ORDER BY user_name ASC
                    ");
                    if (dbrows($result) > 0) {
                        while ($data = dbarray($result)) {
                            $author_opts[$data['user_id']] = $data['user_name'];
                        }
                    }
                    echo form_select("article_author", "", $filter_values['article_author'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['article_0130']." -", "options" => $author_opts
                    ));
                    ?>
                </div>
            </div>

            <?php echo closeform(); ?>
        </div>

        <?php echo openform("article_table", "post", FUSION_REQUEST); ?>
        <?php echo form_hidden("table_action", "", ""); ?>

        <!-- Display Items -->
        <div class="display-block">
            <div class="display-inline-block">
                <?php
                echo form_select("article_display", $this->locale['article_0132'], $limit, array(
                    "width" => "100px", "options" => array(5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50, 100 => 100)
                ));
                ?>
            </div>
            <?php if ($max_rows > $article_rows) : ?>
                <div class="display-inline-block pull-right">
                    <?php echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.fusion_get_aidlink()."&article_display=$limit&amp;") ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Display Table -->
        <div class="table-responsive"><table class="table table-striped">
            <thead>
            <tr>
                <td></td>
                <td class="strong"><?php echo $this->locale['article_0100'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0101'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0102'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0103'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0104'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0105'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0106'] ?></td>
                <td class="strong"><?php echo $this->locale['language'] ?></td>
                <td class="strong"><?php echo $this->locale['article_0107'] ?></td>
            </tr>
            </thead>
            <tbody>
            <?php if (dbrows($result2) > 0) :
                while ($data = dbarray($result2)) : ?>
                    <?php
                    $cat_edit_link = clean_request("section=article_category&ref=article_cat_form&action=edit&cat_id=".$data['article_cat_id'], array("section", "ref", "action", "cat_id"), FALSE);
                    $edit_link = clean_request("section=article&ref=article_form&action=edit&article_id=".$data['article_id'], array("section", "ref", "action", "article_id"), FALSE);
                    $delete_link = clean_request("section=article&ref=article_form&action=delete&article_id=".$data['article_id'], array("section", "ref", "action", "article_id"), FALSE);
                    ?>
                    <tr data-id="<?php echo $data['article_id']; ?>">
                        <td><?php echo form_checkbox("article_id[]", "", "", array("value" => $data['article_id'], "class" => "m-0")) ?></td>
                        <td><span class="text-dark"><?php echo $data['article_subject']; ?></span></td>
                        <td>
                            <a class="text-dark" href="<?php echo $cat_edit_link ?>">
                                <?php echo $data['article_cat_name']; ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge"><?php echo $data['article_draft'] ? $this->locale['yes'] : $this->locale['no']; ?></span>
                        </td>
                        <td><?php echo($data['article_allow_comments'] ? format_word($data['comments_count'], $this->locale['fmt_comment']) : $this->locale['disable']); ?></td>
                        <td><?php echo($data['article_allow_ratings'] ? format_word($data['ratings_count'], $this->locale['fmt_rating']) : $this->locale['disable']); ?></td>
                        <td>
                            <div class="pull-left"><?php echo display_avatar($data, "20px", "", FALSE, "img-rounded m-r-5"); ?></div>
                            <div class="overflow-hide"><?php echo profile_link($data['user_id'], $data['user_name'], $data['user_status']); ?></div>
                        </td>
                        <td><span class="badge"><?php echo getgroupname($data['article_visibility']); ?></span></td>
                        <td><?php echo $data['article_language'] ?></td>
                        <td>
                            <a href="<?php echo $edit_link; ?>"
                               title="<?php echo $this->locale['edit']; ?>"><?php echo $this->locale['edit']; ?></a>&nbsp;|&nbsp;
                            <a href="<?php echo $delete_link; ?>" title="<?php echo $this->locale['delete']; ?>"
                               onclick="return confirm('<?php echo $this->locale['article_0111']; ?>')"><?php echo $this->locale['delete']; ?></a>
                        </td>
                    </tr>
                    <?php
                endwhile;
            else: ?>
                <tr>
                    <td colspan="10"
                        class="text-center"><?php echo($article_cats ? ($filter_empty ? $this->locale['article_0112'] : $this->locale['article_0113']) : $this->locale['article_0114']); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table></div>
        <?php
        closeform();

        // jQuery
        add_to_jquery("
            // Toggle Filters
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#article_filter_options').slideToggle();
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
            $('#article_status, #article_visibility, #article_category, #article_language, #article_author, #article_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");

        // Javascript
        add_to_footer("
            <script type='text/javascript'>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#article_table').submit();
                }
            </script>
        ");

    }

    // Articles Delete Function
    private function execute_ArticlesDelete() {

        if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['article_id']) && isnum($_GET['article_id'])) {
            $article_id = intval($_GET['article_id']);

            if (dbcount("(article_id)", DB_ARTICLES, "article_id='$article_id'")) {
                dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='$article_id' and comment_type='A'");
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$article_id' and rating_type='A'");
                dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_id='$article_id'");
                addNotice("success", $this->locale['article_0032']);
            }
            redirect(clean_request("", array("ref", "action", "cat_id"), FALSE));
        }
    }
}
