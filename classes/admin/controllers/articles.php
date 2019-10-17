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
    private $locale = [];
    private $form_action = FUSION_REQUEST;
    private $articleSettings = [];
    private $article_data = [];

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
            $result = dbquery("SELECT * FROM ".DB_ARTICLES." WHERE article_id=:articleid", [':articleid' => (isset($_POST['article_id']) ? $_POST['article_id'] : $_GET['article_id'])]);
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

    private function execute_ArticlesDelete() {
        if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['article_id']) && isnum($_GET['article_id'])) {
            $article_id = intval($_GET['article_id']);

            if (dbcount("(article_id)", DB_ARTICLES, "article_id=:articleid", [':articleid' => $article_id])) {
                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_item_id=:commentid AND comment_type=:commenttype", [':commentid' => $article_id, ':commenttype' => 'A']);
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id=:ratingid AND rating_type=:ratingtype", [':ratingid' => $article_id, ':ratingtype' => 'A']);
                dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_id=:articleid", [':articleid' => $article_id]);
                addNotice('success', $this->locale['article_0032']);
            }

            redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
        }
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

            $this->article_data = [
                'article_id'             => form_sanitizer($_POST['article_id'], 0, 'article_id'),
                'article_subject'        => form_sanitizer($_POST['article_subject'], '', 'article_subject'),
                'article_cat'            => form_sanitizer($_POST['article_cat'], 0, 'article_cat'),
                'article_snippet'        => form_sanitizer($article_snippet, '', 'article_snippet'),
                'article_article'        => form_sanitizer($article_article, '', 'article_article'),
                'article_keywords'       => form_sanitizer($_POST['article_keywords'], '', 'article_keywords'),
                'article_datestamp'      => form_sanitizer($_POST['article_datestamp'], '', 'article_datestamp'),
                'article_visibility'     => form_sanitizer($_POST['article_visibility'], 0, 'article_visibility'),
                'article_draft'          => isset($_POST['article_draft']) ? $_POST['article_draft'] : '0',
                'article_allow_comments' => isset($_POST['article_allow_comments']) ? $_POST['article_allow_comments'] : '0',
                'article_allow_ratings'  => isset($_POST['article_allow_ratings']) ? $_POST['article_allow_ratings'] : '0',
                'article_language'       => form_sanitizer($_POST['article_language'], LANGUAGE, 'article_language')
            ];

            // Line Breaks
            if (fusion_get_settings('tinymce_enabled') != 1) {
                $this->article_data['article_breaks'] = isset($_POST['article_breaks']) ? "y" : "n";
            } else {
                $this->article_data['article_breaks'] = "n";
            }

            // Handle
            if (\defender::safe()) {
                // Update
                if (dbcount("('article_id')", DB_ARTICLES, "article_id=:articleid", [':articleid' => $this->article_data['article_id']])) {
                    dbquery_insert(DB_ARTICLES, $this->article_data, 'update');
                    addNotice('success', $this->locale['article_0031']);

                    // Create
                } else {
                    $this->article_data['article_name'] = fusion_get_userdata('user_id');
                    $this->article_data['article_id'] = dbquery_insert(DB_ARTICLES, $this->article_data, 'save');
                    addNotice('success', $this->locale['article_0030']);
                }

                // Redirect
                if (isset($_POST['save_and_close'])) {
                    redirect(clean_request('', ['ref', 'action', 'article_id'], FALSE));
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
        if (!fusion_get_settings('tinymce_enabled')) {
            $articleSnippetSettings = [
                'required'    => TRUE,
                'preview'     => TRUE,
                'type'        => 'bbcode',
                'placeholder' => $this->locale['article_0254'],
                'error_text'  => $this->locale['article_0271'],
                'form_name'   => 'articleform',
                'wordcount'   => TRUE,
                'path'        => IMAGES_A,
                'rows'        => '20',
                'autosize'    => TRUE
            ];
            $articleExtendedSettings = [
                'required'    => ($this->articleSettings['article_extended_required'] ? TRUE : FALSE),
                'preview'     => TRUE,
                'html'        => TRUE,
                'placeholder' => $this->locale['article_0253'],
                'error_text'  => $this->locale['article_0272'],
                'form_name'   => 'articleform',
                'wordcount'   => TRUE,
                'path'        => IMAGES_A,
                'rows'        => '20',
                'autosize'    => TRUE
            ];
        } else {
            $articleSnippetSettings = [
                'required'   => TRUE,
                'type'       => 'bbcode',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['article_0271'],
                'path'       => IMAGES_A,
                'rows'       => '20',
            ];
            $articleExtendedSettings = [
                'required'   => ($this->articleSettings['article_extended_required'] ? TRUE : FALSE),
                'type'       => 'tinymce',
                'tinymce'    => 'advanced',
                'error_text' => $this->locale['article_0272'],
                'path'       => IMAGES_A,
                'rows'       => '20',
            ];
        }

        // Set Session Cache
        echo \PHPFusion\Admins::getInstance()->requestCache('articleform', 'A', $this->article_data['article_id'], [
            'article_subject' => $this->locale['article_0163'],
            'article_snippet' => $this->locale['article_0251'],
            'article_article' => $this->locale['article_0252']
        ]);

        // Start Form
        echo openform('articleform', 'post', $this->form_action, ['enctype' => TRUE]);
        echo "<div class='spacer-sm'>\n";
        self::display_articleButtons('formstart');
        echo "</div>\n";
        echo "<hr/>\n";
        echo form_hidden('article_id', '', $this->article_data['article_id']);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
        echo form_text('article_subject', '', $this->article_data['article_subject'], [
            'required'    => TRUE,
            'max_length'  => 200,
            'class'       => 'form-group-lg',
            'placeholder' => $this->locale['article_0163'],
            'error_text'  => $this->locale['article_0270']
        ]);
        add_to_head("<style>.panel-txtarea {border:0; padding-bottom:0;} .tab-content > .tab > .form-group { margin:0; }</style>");
        echo "<ul class='nav nav-tabs m-b-15 clearfix'>\n";
        echo "<li class='active'><a data-toggle='tab' href='#snippet'>".$this->locale['article_0251']."<span class='required'>&nbsp;*</span></a></li>";
        echo "<li><a data-toggle='tab' href='#extended'>".$this->locale['article_0252'].($this->articleSettings['article_extended_required'] ? "<span class='required'>&nbsp;*</span>" : '')."</a></li>";
        echo "</ul>\n";
        echo "<div class='tab-content p-0'>\n";
        echo "<div id='snippet' class='tab tab-pane fade in active p-0'>\n";
        echo form_textarea('article_snippet', '', $this->article_data['article_snippet'], $articleSnippetSettings);
        echo "</div>\n";
        echo "<div id='extended' class='tab tab-pane fade p-0'>\n";
        echo form_textarea('article_article', '', $this->article_data['article_article'], $articleExtendedSettings);
        echo "</div>\n";
        echo "</div>\n";

        echo "</div><div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
        openside($this->locale['article_0262']);
        echo form_select('article_draft', $this->locale['status'], $this->article_data['article_draft'], [
            'inline'      => TRUE,
            'inner_width' => '100%',
            'options'     => [
                1 => $this->locale['draft'],
                0 => $this->locale['publish']
            ]
        ]);
        echo form_select_tree('article_cat', $this->locale['article_0101'], $this->article_data['article_cat'], [
            'required'     => TRUE,
            'inline'       => TRUE,
            'error_text'   => $this->locale['article_0273'],
            'inner_width'  => '100%',
            'parent_value' => $this->locale['choose'],
            'query'        => (multilang_table("AR") ? "WHERE ".in_group('article_cat_language', LANGUAGE) : "")
        ],
            DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent"
        );
        echo form_select('article_visibility', $this->locale['article_0106'], $this->article_data['article_visibility'], [
            'options'     => fusion_get_groups(),
            'placeholder' => $this->locale['choose'],
            'inner_width' => '100%',
            'inline'      => TRUE
        ]);
        if (multilang_table("AR")) {
            echo form_select('article_language[]', $this->locale['language'], $this->article_data['article_language'], [
                'options'     => fusion_get_enabled_languages(),
                'placeholder' => $this->locale['choose'],
                'inner_width' => '100%',
                'inline'      => TRUE,
                'multiple'    => TRUE,
                'delimeter'   => '.'
            ]);
        } else {
            echo form_hidden('article_language', '', $this->article_data['article_language']);
        }
        echo form_datepicker('article_datestamp', $this->locale['article_0203'], $this->article_data['article_datestamp'], [
            'inline'      => TRUE,
            'inner_width' => '100%'
        ]);
        closeside();

        openside('');
        if (fusion_get_settings("tinymce_enabled") != 1) {
            echo form_checkbox('article_breaks', $this->locale['article_0257'], $this->article_data['article_breaks'], [
                'value'         => 'y',
                'reverse_label' => TRUE,
                'class'         => 'm-b-5'
            ]);
        }
        echo form_checkbox('article_allow_comments', $this->locale['article_0258'], $this->article_data['article_allow_comments'], [
            'reverse_label' => TRUE,
            'class'         => 'm-b-5',
            'ext_tip'       => (!fusion_get_settings("comments_enabled") ? "<div class='alert alert-warning'>".sprintf($this->locale['article_0274'], $this->locale['comments'])."</div>" : "")
        ]);
        echo form_checkbox('article_allow_ratings', $this->locale['article_0259'], $this->article_data['article_allow_ratings'], [
            'reverse_label' => TRUE,
            'class'         => 'm-b-5',
            'ext_tip'       => (!fusion_get_settings("ratings_enabled") ? "<div class='alert alert-warning'>".sprintf($this->locale['article_0274'], $this->locale['ratings'])."</div>" : "")
        ]);
        closeside();
        openside($this->locale['article_0260']);
        echo form_select('article_keywords', '', $this->article_data['article_keywords'], [
            'max_length'  => 320,
            'placeholder' => $this->locale['article_0260a'],
            'width'       => '100%',
            'inner_width' => '100%',
            'tags'        => TRUE,
            'multiple'    => TRUE
        ]);
        closeside();
        echo "</div>\n</div>\n";

        self::display_articleButtons("formend");
        echo closeform();
    }

    /**
     * Generate sets of push buttons for article Content form
     *
     * @param      $unique_id
     */
    private function display_articleButtons($unique_id) {
        echo form_button('cancel', $this->locale['cancel'], $this->locale['cancel'], [
            'class'    => 'btn-sm btn-default',
            'icon'     => 'fa fa-times',
            'input-id' => 'cancel-'.$unique_id
        ]);
        echo form_button('save', $this->locale['save'], $this->locale['save'], [
            'class'    => 'btn-sm btn-success',
            'icon'     => 'fa fa-hdd-o',
            'input-id' => 'save-'.$unique_id
        ]);
        echo form_button('save_and_close', $this->locale['save_and_close'], $this->locale['save_and_close'], [
            'class'    => 'btn-sm btn-primary',
            'icon'     => 'fa fa-floppy-o',
            'input-id' => 'save_and_close-'.$unique_id
        ]);
    }

    // Articles Delete Function

    /**
     * Displays Articles Listing
     */
    private function display_article_listing() {
        // Run functions
        $allowed_actions = array_flip(['publish', 'unpublish', 'delete', 'article_display']);

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = (isset($_POST['article_id'])) ? explode(",", form_sanitizer($_POST['article_id'], '', 'article_id')) : '';
            if (!empty($input)) {
                foreach ($input as $article_id) {
                    // check input table
                    if (dbcount("('article_id')", DB_ARTICLES, "article_id=:articleid", [':articleid' => intval($article_id)]) && \defender::safe()) {

                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_ARTICLES." SET article_draft=:draft WHERE article_id=:articleid", [':draft' => '0', ':articleid' => intval($article_id)]);
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_ARTICLES." SET article_draft=:draft WHERE article_id=:articleid", [':draft' => '1', ':articleid' => intval($article_id)]);
                                break;
                            case "delete":
                                dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_id=:articleid", [':articleid' => intval($article_id)]);
                                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_item_id=:commentid AND comment_type=:commenttype", [':commentid' => intval($article_id), ':commenttype' => 'A']);
                                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id=:ratingid AND rating_type=:ratingtype", [':ratingid' => intval($article_id), ':ratingtype' => 'A']);
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice('success', $this->locale['article_0033']);
                redirect(FUSION_REQUEST);
            }
            addNotice('warning', $this->locale['article_0034']);
            redirect(FUSION_REQUEST);
        }

        // Clear
        if (isset($_POST['article_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Search
        $sql_condition = multilang_table("AR") ? in_group('article_language', LANGUAGE) : "";
        $search_string = [];
        if (isset($_POST['p-submit-article_text'])) {
            $search_string['article_subject'] = [
                'input'    => form_sanitizer($_POST['article_text'], '', 'article_text'),
                'operator' => "LIKE"
            ];
        }

        if (!empty($_POST['article_status']) && isnum($_POST['article_status']) && $_POST['article_status'] == "1") {
            $search_string['article_draft'] = [
                'input'    => 1,
                'operator' => '='
            ];
        }

        if (!empty($_POST['article_visibility'])) {
            $search_string['article_visibility'] = [
                'input'    => form_sanitizer($_POST['article_visibility'], '', 'article_visibility'),
                'operator' => '='
            ];
        }

        if (!empty($_POST['article_category'])) {
            $search_string['article_cat'] = [
                'input'    => form_sanitizer($_POST['article_category'], '', 'article_category'),
                'operator' => "="
            ];
        }

        if (!empty($_POST['article_author'])) {
            $search_string['article_name'] = [
                'input'    => form_sanitizer($_POST['article_author'], '', 'article_author'),
                'operator' => "="
            ];
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition)
                    $sql_condition .= " AND ";
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
        $sql = "SELECT a.*, ac.*, u.user_id, u.user_name, u.user_status, u.user_avatar,
            (SELECT COUNT(ar.rating_vote) FROM ".DB_RATINGS." ar WHERE ar.rating_item_id = a.article_id AND ar.rating_type = 'A') AS ratings_count,
            (SELECT COUNT(ad.comment_id) FROM ".DB_COMMENTS." ad WHERE ad.comment_item_id = a.article_id AND ad.comment_type = 'A' AND ad.comment_hidden = '0') AS comments_count
            FROM ".DB_ARTICLES." a
            LEFT JOIN ".DB_ARTICLE_CATS." ac ON ac.article_cat_id=a.article_cat
            INNER JOIN ".DB_USERS." u ON u.user_id=a.article_name
            ".($sql_condition ? " WHERE ".$sql_condition : "")."
            GROUP BY a.article_id
            ORDER BY article_draft DESC, article_datestamp DESC
            LIMIT $rowstart, $limit
        ";
        $result2 = dbquery($sql);
        $article_rows = dbrows($result2);
        $article_cats = dbcount("(article_cat_id)", DB_ARTICLE_CATS, "");

        // Filters
        $filter_values = [
            'article_text'       => !empty($_POST['article_text']) ? form_sanitizer($_POST['article_text'], '', 'article_text') : '',
            'article_status'     => !empty($_POST['article_status']) ? form_sanitizer($_POST['article_status'], '', 'article_status') : '',
            'article_category'   => !empty($_POST['article_category']) ? form_sanitizer($_POST['article_category'], '', 'article_category') : '',
            'article_visibility' => !empty($_POST['article_visibility']) ? form_sanitizer($_POST['article_visibility'], '', 'article_visibility') : '',
            'article_author'     => !empty($_POST['article_author']) ? form_sanitizer($_POST['article_author'], '', 'article_author') : ''
        ];

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        ?>
        <div class="m-t-20 m-b-5">
            <?php echo openform("article_filter", "post", FUSION_REQUEST); ?>

            <!-- Display Buttons and Search -->
            <div class="clearfix">
                <div class="pull-right">
                    <?php if ($article_cats) { ?>
                        <a class="btn btn-sm btn-success" href="<?php echo clean_request("ref=article_form", ["ref"], FALSE); ?>"><i class="fa fa-plus"></i> <?php echo $this->locale['article_0002']; ?>
                        </a>
                    <?php } ?>
                    <button type="button" class="hidden-xs m-l-5 btn btn-sm btn-default" onclick="run_admin('publish', '#table_action', '#article_table');">
                        <i class="fa fa-check"></i> <?php echo $this->locale['publish']; ?></button>
                    <button type="button" class="hidden-xs m-l-5 btn btn-sm btn-default" onclick="run_admin('unpublish', '#table_action', '#article_table');">
                        <i class="fa fa-ban"></i> <?php echo $this->locale['unpublish']; ?></button>
                    <button type="button" class="hidden-xs m-l-5 btn btn-sm btn-danger" onclick="run_admin('delete', '#table_action', '#article_table');">
                        <i class="fa fa-trash-o"></i> <?php echo $this->locale['delete']; ?></button>
                </div>

                <div class="display-inline-block pull-left m-r-10">
                    <?php echo form_text('article_text', '', $filter_values['article_text'], [
                        'placeholder'       => $this->locale['article_0100'],
                        'append_button'     => TRUE,
                        'append_value'      => "<i class='fa fa-search'></i>",
                        'append_form_value' => 'search_article',
                        'width'             => '180px',
                        'group_size'        => 'sm'
                    ]); ?>
                </div>

                <div class="display-inline-block hidden-xs" style="vertical-align: top;">
                    <a class="btn btn-sm m-r-5 <?php echo($filter_empty ? "btn-default" : "btn-info"); ?>"
                       id="toggle_options" href="#">
                        <?php echo $this->locale['article_0121']; ?>
                        <span id="filter_caret"
                              class="fa fa-fw <?php echo($filter_empty ? "fa-caret-down" : "fa-caret-up"); ?>"></span>
                    </a>
                    <?php echo form_button('article_clear', $this->locale['article_0122'], 'clear', ['class' => 'btn-default btn-sm']); ?>
                </div>
            </div>

            <!-- Display Filters -->
            <div id="article_filter_options"<?php echo($filter_empty ? " style='display: none;'" : ""); ?>>
                <div class="display-inline-block">
                    <?php echo form_select('article_status', '', $filter_values['article_status'], [
                        'allowclear'  => TRUE,
                        'placeholder' => '- '.$this->locale['article_0123'].' -',
                        'options'     => [
                            0 => $this->locale['article_0124'],
                            1 => $this->locale['draft']
                        ]
                    ]); ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    echo form_select('article_visibility', '', $filter_values['article_visibility'], [
                        'allowclear'  => TRUE,
                        'placeholder' => '- '.$this->locale['article_0125'].' -',
                        'options'     => fusion_get_groups()
                    ]);
                    ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    echo form_select_tree('article_category', '', $filter_values['article_category'], [
                        'parent_value' => $this->locale['article_0127'],
                        'placeholder'  => '- '.$this->locale['article_0126'].' -',
                        'allowclear'   => TRUE,
                        'query'        => (multilang_table("AR") ? "WHERE ".in_group('article_cat_language', LANGUAGE) : "")
                    ], DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");
                    ?>
                </div>
                <div class="display-inline-block">
                    <?php
                    $author_opts = [0 => $this->locale['article_0131']];
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
                    echo form_select('article_author', '', $filter_values['article_author'], [
                        'allowclear'  => TRUE,
                        'placeholder' => '- '.$this->locale['article_0130'].' -',
                        'options'     => $author_opts
                    ]);
                    ?>
                </div>
            </div>

            <?php echo closeform(); ?>
        </div>

        <?php echo openform('article_table', 'post', FUSION_REQUEST); ?>
        <?php echo form_hidden('table_action', '', ''); ?>

        <!-- Display Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th class="hidden-xs"></th>
                    <th class="strong"><?php echo $this->locale['article_0100'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0101'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0102'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0103'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0104'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0105'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0106'] ?></th>
                    <th class="strong"><?php echo $this->locale['article_0107'] ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (dbrows($result2) > 0) :
                    while ($data = dbarray($result2)) : ?>
                        <?php
                        $cat_edit_link = clean_request("section=article_category&ref=article_cat_form&action=edit&cat_id=".$data['article_cat_id'], ['section', 'ref', 'action', 'cat_id'], FALSE);
                        $edit_link = clean_request("section=article&ref=article_form&action=edit&article_id=".$data['article_id'], ['section', 'ref', 'action', 'article_id'], FALSE);
                        $delete_link = clean_request("section=article&ref=article_form&action=delete&article_id=".$data['article_id'], ['section', 'ref', 'action', 'article_id'], FALSE);
                        ?>
                        <tr data-id="<?php echo $data['article_id']; ?>">
                            <td class="hidden-xs"><?php echo form_checkbox('article_id[]', '', '', ['value' => $data['article_id'], 'class' => 'm-0']) ?></td>
                            <td><span class="text-dark"><?php echo $data['article_subject']; ?></span></td>
                            <td>
                                <a class="text-dark" href="<?php echo $cat_edit_link ?>"><?php echo $data['article_cat_name']; ?></a>
                            </td>
                            <td>
                                <span class="badge"><?php echo $data['article_draft'] ? $this->locale['yes'] : $this->locale['no']; ?></span>
                            </td>
                            <td><?php echo($data['article_allow_comments'] ? format_word($data['comments_count'], $this->locale['fmt_comment']) : $this->locale['disable']); ?></td>
                            <td><?php echo($data['article_allow_ratings'] ? format_word($data['ratings_count'], $this->locale['fmt_rating']) : $this->locale['disable']); ?></td>
                            <td>
                                <div class="pull-left"><?php echo display_avatar($data, '20px', '', FALSE, 'img-rounded m-r-5'); ?></div>
                                <div class="overflow-hide"><?php echo profile_link($data['user_id'], $data['user_name'], $data['user_status']); ?></div>
                            </td>
                            <td><span class="badge"><?php echo getgroupname($data['article_visibility']); ?></span></td>
                            <td>
                                <a href="<?php echo $edit_link; ?>" title="<?php echo $this->locale['edit']; ?>"><?php echo $this->locale['edit']; ?></a>&nbsp;|&nbsp;
                                <a href="<?php echo $delete_link; ?>" title="<?php echo $this->locale['delete']; ?>" onclick="return confirm('<?php echo $this->locale['article_0111']; ?>')"><?php echo $this->locale['delete']; ?></a>
                            </td>
                        </tr>
                    <?php
                    endwhile;
                else: ?>
                    <tr>
                        <td colspan="9" class="text-center"><?php echo($article_cats ? ($filter_empty ? $this->locale['article_0112'] : $this->locale['article_0113']) : $this->locale['article_0114']); ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Display Items -->
        <div class="display-block">
            <label class="control-label display-inline-block m-r-10" for="s2id_autogen6"><?php echo $this->locale['article_0132']; ?></label>
            <div class="display-inline-block"><?php
                echo form_select('article_display', '', $limit, [
                    'options' => [5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50, 100 => 100]
                ]);
                ?></div>
            <?php if ($max_rows > $article_rows) : ?>
                <div class="display-inline-block pull-right">
                    <?php echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.fusion_get_aidlink()."&article_display=$limit&amp;") ?>
                </div>
            <?php endif; ?>
        </div>
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
            $('#article_status, #article_visibility, #article_category, #article_author, #article_display').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");
    }
}
