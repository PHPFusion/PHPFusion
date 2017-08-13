<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: articles/admin/controllers/articles_cat.php
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

class ArticlesCategoryAdmin extends ArticlesAdminModel {

    private static $instance = NULL;
    private $locale = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function displayArticlesAdmin() {
        pageAccess("A");
        $this->locale = self::get_articleAdminLocale();
        if (isset($_GET['ref']) && $_GET['ref'] == "article_cat_form") {
            $this->display_article_cat_form();
        } else {
            $this->display_article_cat_listing();
        }
    }

    /**
     * Displays Articles Category Form
     */
    private function display_article_cat_form() {

        // Cancel Form
        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&section=article_category");
        }

        // Empty
        $data = array(
            "article_cat_id" => 0,
            "article_cat_name" => "",
            "article_cat_parent" => 0,
            "article_cat_description" => "",
            "article_cat_status" => 1,
            "article_cat_visibility" => iGUEST,
            "article_cat_language" => LANGUAGE,
            "article_cat_hidden" => ""
        );

        // Form
        $formAction = FUSION_REQUEST;

        // Save
        if ((isset($_POST['save_cat'])) || (isset($_POST['save_cat_and_close']))) {
            // Description
            $cat_desc = "";
            if (isset($_POST['article_cat_description'])) {
                $cat_desc = (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['article_cat_description']) : stripslashes($_POST['article_cat_description']));
            }

            // Check Fields
            $inputArray = array(
                "article_cat_id" => form_sanitizer($_POST['article_cat_id'], "", "article_cat_id"),
                "article_cat_name" => form_sanitizer($_POST['article_cat_name'], "", "article_cat_name"),
                "article_cat_description" => form_sanitizer($cat_desc, "", "article_cat_description"),
                "article_cat_parent" => form_sanitizer($_POST['article_cat_parent'], 0, "article_cat_parent"),
                "article_cat_visibility" => form_sanitizer($_POST['article_cat_visibility'], 0, "article_cat_visibility"),
                "article_cat_status" => form_sanitizer($_POST['article_cat_status'], 0, "article_cat_status"),
                "article_cat_language" => form_sanitizer($_POST['article_cat_language'], LANGUAGE, "article_cat_language")
            );

            // Check Where Condition
            $categoryNameCheck = array(
                "when_updating" => "article_cat_name='".$inputArray['article_cat_name']."' and article_cat_id !='".$inputArray['article_cat_id']."' ".(multilang_table("AR") ? "and article_cat_language = '".LANGUAGE."'" : ""),
                "when_saving" => "article_cat_name='".$inputArray['article_cat_name']."' ".(multilang_table("AR") ? "and article_cat_language = '".LANGUAGE."'" : ""),
            );

            // Save
            if (\defender::safe()) {

                // Update
                if (dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_id='".$inputArray['article_cat_id']."'")) {
                    if (!dbcount("(article_cat_id)", DB_ARTICLE_CATS, $categoryNameCheck['when_updating'])) {
                        dbquery_insert(DB_ARTICLE_CATS, $inputArray, "update");
                        addNotice("success", $this->locale['article_0041']);

                        if (isset($_POST['save_cat_and_close'])) {
                            redirect(clean_request("", array("action", "ref"), FALSE));
                        } else {
                            redirect(FUSION_REQUEST);
                        }

                    } else {
                        addNotice("danger", $this->locale['article_0321']);
                    }

                // Insert
                } else {
                    if (!dbcount("(article_cat_id)", DB_ARTICLE_CATS, $categoryNameCheck['when_saving'])) {
                        dbquery_insert(DB_ARTICLE_CATS, $inputArray, "save");
                        addNotice("success", $this->locale['article_0040']);

                        if (isset($_POST['save_cat_and_close'])) {
                            redirect(clean_request("", array("action", "ref"), FALSE));
                        } else {
                            redirect(FUSION_REQUEST);
                        }
                    } else {
                        addNotice("danger", $this->locale['article_0321']);
                    }
                }
            }
            $data = $inputArray;

        // Edit
        } elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $result = dbquery("SELECT * FROM ".DB_ARTICLE_CATS." ".(multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."' AND" : "WHERE")." article_cat_id='".$_GET['cat_id']."'");
            if (dbrows($result)) {
                $data = dbarray($result);
            } else {
                redirect(clean_request("", array("action"), FALSE));
            }

        // Delete
        } elseif ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            if (!dbcount("(article_id)", DB_ARTICLES, "article_cat='".$_GET['cat_id']."'") && !dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_parent='".$_GET['cat_id']."'")) {
                dbquery("DELETE FROM  ".DB_ARTICLE_CATS." WHERE article_cat_id='".$_GET['cat_id']."'");
                addNotice("success", $this->locale['article_0042']);
            } else {
                addNotice("warning", $this->locale['article_0043']);
                addNotice("warning", $this->locale['article_0044']);
            }
            redirect(clean_request("", array("ref", "action", "cat_id"), FALSE));
        }

        // Form ?>
        <div class="m-t-20 m-b-20">
            <?php echo openform("catform", "post", $formAction); ?>
            <div class="row">

                <!-- Left Column -->
                <div class="col-xs-12 col-sm-8">
                    <?php
                    echo form_hidden("article_cat_id", "", $data['article_cat_id']);

                    echo form_text("article_cat_name", $this->locale['article_0150'], $data['article_cat_name'], array(
                        "required" => TRUE,
                        "inline" => TRUE,
                        "error_text" => $this->locale['article_0320']
                    ));

                    echo form_select_tree("article_cat_parent", $this->locale['article_0303'], $data['article_cat_parent'], array(
                        "inline" => TRUE,
                        "disable_opts" => $data['article_cat_id'],
                        "hide_disabled" => TRUE,
                        "query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
                    ), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");

                    echo form_textarea("article_cat_description", $this->locale['article_0304'], $data['article_cat_description'], array(
                        "required" => TRUE,
                        "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "html",
                        "tinymce" => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "",
                        "autosize" => TRUE,
                        "inline" => TRUE,
                        "preview" => TRUE,
                        "form_name" => "catform",
                        "error_text" => $this->locale['article_0322']
                    ));
                    ?>
                </div>

                <!-- Right Column -->
                <div class="col-xs-12 col-sm-4">
                    <?php
                    openside($this->locale['article_0261']);

                    if (multilang_table("AR")) {
                        echo form_select("article_cat_language", $this->locale['language'], $data['article_cat_language'], array(
                            "inline" => TRUE,
                            "options" => fusion_get_enabled_languages(),
                            "placeholder" => $this->locale['choose']
                        ));
                    } else {
                        echo form_hidden("article_cat_language", "", $data['article_cat_language']);
                    }

                    echo form_select("article_cat_visibility", $this->locale['article_0106'], $data['article_cat_visibility'], array(
                        "options" => fusion_get_groups(),
                        "placeholder" => $this->locale['choose'],
                        "inline" => TRUE,
                    ));

                    echo form_select("article_cat_status", $this->locale['article_0152'], $data['article_cat_status'], array(
                        "options" => array(0 => $this->locale['unpublish'], 1 => $this->locale['publish']),
                        "placeholder" => $this->locale['choose'],
                        "inline" => TRUE,
                    ));

                    echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default btn-sm", "icon" => "fa fa-fw fa-times"));
                    echo form_button("save_cat", $this->locale['save'], $this->locale['save'], array("class" => "btn-success btn-sm m-l-10", "icon" => "fa fa-fw fa-hdd-o"));
                    echo form_button("save_cat_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], array("class" => "btn-primary btn-sm m-l-10", "icon" => "fa fa-fw fa-floppy-o"));

                    closeside();
                    ?>
                </div>
            </div>
            <?php
            echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default", "icon" => "fa fa-fw fa-times"));
            echo form_button("save_cat", $this->locale['save'], $this->locale['save'], array("class" => "btn-success m-l-10", "icon" => "fa fa-fw fa-hdd-o"));
            echo form_button("save_cat_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'], array("class" => "btn-primary m-l-10", "icon" => "fa fa-fw fa-floppy-o"));
            echo closeform();
            ?>
        </div>
        <?php
    }

    /**
     * Displays Articles Category Listing
     */
    private function display_article_cat_listing() {

        // Run functions
        $allowed_actions = array_flip(array("publish", "unpublish", "delete"));

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = !empty($_POST['article_cat_id']) ? form_sanitizer($_POST['article_cat_id'], "", "article_cat_id") : "";
            if (!empty($input)) {
            $input = ($input ? explode(",", $input) : array());
                foreach ($input as $article_cat_id) {
                    // check input table
                    if (dbcount("('article_cat_id')", DB_ARTICLE_CATS,
                                "article_cat_id='".intval($article_cat_id)."'") && \defender::safe()
                    ) {
                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_ARTICLE_CATS." SET article_cat_status='1' WHERE article_cat_id='".intval($article_cat_id)."'");
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_ARTICLE_CATS." SET article_cat_status='0' WHERE article_cat_id='".intval($article_cat_id)."'");
                                break;
                            case "delete":
                                if (!dbcount("(article_id)", DB_ARTICLES, "article_cat='".$article_cat_id."'") && !dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_parent='".$article_cat_id."'")) {
                                    dbquery("DELETE FROM  ".DB_ARTICLE_CATS." WHERE article_cat_id='".intval($article_cat_id)."'");
                                } else {
                                    addNotice("warning", $this->locale['article_0046']);
                                    addNotice("warning", $this->locale['article_0044']);
                                }
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice("success", $this->locale['article_0045']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice("warning", $this->locale['article_0048']);
                redirect(FUSION_REQUEST);
            }
        }

        // Clear
        if (isset($_POST['article_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&amp;section=article_category");
        }

        // Search
        $sql_condition = multilang_table("AR") ? "ac.article_cat_language='".LANGUAGE."'" : "";
        $search_string = array();
        if (isset($_POST['p-submit-article_cat_name'])) {
            $search_string['article_cat_name'] = array(
                "input" => form_sanitizer($_POST['article_cat_name'], "", "article_cat_name"), "operator" => "LIKE"
            );
        }

        if (!empty($_POST['article_cat_status']) && isnum($_POST['article_cat_status'])) {
            switch ($_POST['article_cat_status']) {
                case 1: // published
                    $search_string['article_cat_status'] = array("input" => 1, "operator" => "=");
                    break;
                case 2: // unpublished
                    $search_string['article_cat_status'] = array("input" => 0, "operator" => "=");
                    break;
            }
        }

        if (!empty($_POST['article_cat_visibility'])) {
            $search_string['article_cat_visibility'] = array(
                "input" => form_sanitizer($_POST['article_cat_visibility'], "", "article_cat_visibility"), "operator" => "="
            );
        }

        if (!empty($_POST['article_cat_language'])) {
            $search_string['article_cat_language'] = array(
                "input" => form_sanitizer($_POST['article_cat_language'], "", "article_cat_language"), "operator" => "="
            );
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
	            if ($sql_condition) $sql_condition .= " AND ";
                $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        // Query
        $result = dbquery_tree_full(DB_ARTICLE_CATS, "article_cat_id", "article_cat_parent", "",
            "SELECT ac.*, COUNT(a.article_id) AS article_count
            FROM ".DB_ARTICLE_CATS." ac
            LEFT JOIN ".DB_ARTICLES." AS a ON a.article_cat=ac.article_cat_id
            ".($sql_condition ? " WHERE ".$sql_condition : "")."
            GROUP BY ac.article_cat_id
            ORDER BY ac.article_cat_parent ASC, ac.article_cat_id ASC"
        );

        // Filters
        $filter_values = array(
            "article_cat_name" => !empty($_POST['article_cat_name']) ? form_sanitizer($_POST['article_cat_name'], "", "article_cat_name") : "",
            "article_cat_status" => !empty($_POST['article_cat_status']) ? form_sanitizer($_POST['article_cat_status'], "", "article_cat_status") : "",
            "article_cat_visibility" => !empty($_POST['article_cat_visibility']) ? form_sanitizer($_POST['article_cat_visibility'], "", "article_cat_visibility") : "",
            "article_cat_language" => !empty($_POST['article_cat_language']) ? form_sanitizer($_POST['article_cat_language'], "", "article_cat_language") : ""
        );

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        // Languages
        $language_opts = array(0 => $this->locale['article_0129']);
        $language_opts += fusion_get_enabled_languages();
        ?>

        <!-- Display Search, Filters and Actions -->
        <div class="m-t-15">
            <?php echo openform("article_filter", "post", FUSION_REQUEST); ?>
            <div class="clearfix">

                <!-- Actions -->
                <div class="pull-right">
                    <a class="btn btn-success btn-sm m-r-10" href="<?php echo clean_request("ref=article_cat_form", array("ref"), false); ?>"><i class="fa fa-fw fa-plus"></i> <?php echo $this->locale['article_0005']; ?></a>
                  <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('publish');"><i class="fa fa-fw fa-check"></i> <?php echo $this -> locale['publish']; ?></a>
                  <a class="btn btn-default btn-sm m-r-10" onclick="run_admin('unpublish');"><i class="fa fa-fw fa-ban"></i> <?php echo $this -> locale['unpublish']; ?></a>
                  <a class="btn btn-danger btn-sm m-r-10" onclick="run_admin('delete');"><i class="fa fa-fw fa-trash-o"></i> <?php echo $this -> locale['delete']; ?></a>
                </div>

                <!-- Search -->
                <div class="display-inline-block pull-left m-r-10" style="width: 300px;">
                <?php echo form_text("article_cat_name", "", $filter_values['article_cat_name'], array(
                    "placeholder"       => $this->locale['article_0150'],
                    "append_button"     => TRUE,
                    "append_value"      => "<i class='fa fa-fw fa-search'></i>",
                    "append_form_value" => "search_article",
                    "width"             => "250px",
                    "group_size"        => "sm"
                )); ?>
                </div>
                <div class="display-inline-block">
                  <a class="btn btn-sm m-r-15 <?php echo (!$filter_empty ? "btn-info" : "btn-default"); ?>" id="toggle_options" href="#">
                      <?php echo $this->locale['article_0121']; ?>
                    <span id="filter_caret" class="fa <?php echo (!$filter_empty ? "fa-caret-up" : "fa-caret-down"); ?>"></span>
                  </a>
                    <?php echo form_button("article_clear", $this->locale['article_0122'], "clear", array('class' => 'btn-default btn-sm')); ?>
                </div>
            </div>

            <!-- Display Filters -->
            <div id="article_filter_options"<?php echo ($filter_empty ? " style='display: none;'" : ""); ?>>
                <div class="display-inline-block">
                    <?php echo form_select("article_cat_status", "", $filter_values['article_cat_status'], array(
                        "allowclear" => TRUE, "placeholder" => "- ".$this->locale['article_0123']." -", "options" => array(0 => $this->locale['article_0124'], 2 => $this->locale['unpublish'], 1 => $this->locale['publish'])
                    )); ?>
                </div>
                <div class="display-inline-block">
                    <?php echo form_select("article_cat_visibility", "", $filter_values['article_cat_visibility'], array(
                        "allowclear" => TRUE, "placeholder" => "-  ".$this->locale['article_0125']." -", "options" => fusion_get_groups()
                    )); ?>
                </div>
                <div class="display-inline-block">
                    <?php echo form_select("article_cat_language", "", $filter_values['article_cat_language'], array(
                        "allowclear" => TRUE, "placeholder" => "-  ".$this->locale['article_0128']." -", "options" => $language_opts
                    )); ?>
                </div>
            </div>
            <?php echo closeform(); ?>
        </div>

        <?php echo openform("article_table", "post", FUSION_REQUEST);
        echo form_hidden("table_action", "", "");
        $this->display_article_category($result);
        echo closeform();

        // Footer
        add_to_footer("
            <script>
                function run_admin(action) {
                    $('#table_action').val(action);
                    $('#article_table').submit();
                }
            </script>
        ");

        // Toogle Options
        add_to_jquery("
            // Toogle Options
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

            // Select change
            $('#article_cat_status, #article_cat_visibility, #article_cat_language').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");

    }

    /**
     * Recursive function to display administration table
     * @param     $data
     * @param int $id
     */
    private function display_article_category($data, $id = 0, $level = 0) {

        if (!$id) :
            ?>
            <div class="table-responsive"><table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th class="col-xs-4"><?php echo $this->locale['article_0150'] ?></th>
                <th><?php echo $this->locale['article_0001'] ?></th>
                <th><?php echo $this->locale['article_0152'] ?></th>
                <th><?php echo $this->locale['article_0106'] ?></th>
                <th><?php echo $this->locale['language'] ?></th>
                <th><?php echo $this->locale['article_0107'] ?></th>
            </tr>
            </thead>
            <tbody>
        <?php endif; ?>

        <?php if (!empty($data[$id])) : ?>
            <?php foreach ($data[$id] as $cat_id => $cdata) :
                $edit_link = clean_request("section=article_category&ref=article_cat_form&action=edit&cat_id=".$cat_id, array("section", "ref", "action", "cat_id"), FALSE);
                $delete_link = clean_request("section=article_category&ref=article_cat_form&action=delete&cat_id=".$cat_id, array("section", "ref", "action", "cat_id"), FALSE);
                ?>
                <tr data-id="<?php echo $cat_id; ?>" id="cat<?php echo $cat_id; ?>">
                    <td><?php echo form_checkbox("article_cat_id[]", "", "", array("value" => $cat_id, "input_id" => "checkbox".$cat_id, "class" => "m-b-0"));
                    add_to_jquery('$("#checkbox'.$cat_id.'").click(function() {
                        if ($(this).prop("checked")) {
                            $("#cat'.$cat_id.'").addClass("active");
                        } else {
                            $("#cat'.$cat_id.'").removeClass("active");
                        }
                    });');
                     ?></td>
                    <td><span class="text-dark"><?php echo str_repeat("&nbsp;&nbsp;", $level)." ".$cdata['article_cat_name']; ?></span></td>
                    <td><span class="badge"><?php echo format_word($cdata['article_count'], $this->locale['fmt_article']); ?></span></td>
                    <td><span class="badge"><?php echo ($cdata['article_cat_status'] == 1 ? $this->locale['publish'] : $this->locale['unpublish']); ?></span></td>
                    <td><span class="badge"><?php echo getgroupname($cdata['article_cat_visibility']); ?></span></td>
                    <td><?php echo translate_lang_names($cdata['article_cat_language']) ?></td>
                    <td>
                        <a href="<?php echo $edit_link; ?>" title="<?php echo $this->locale['edit']; ?>"><?php echo $this->locale['edit']; ?></a>&nbsp;|&nbsp;
                        <a href="<?php echo $delete_link; ?>" title="<?php echo $this->locale['delete']; ?>" onclick="return confirm('<?php echo $this->locale['article_0161']; ?>')"><?php echo $this->locale['delete']; ?></a>
                    </td>
                </tr>
                <?php
                if (isset($data[$cdata['article_cat_id']])) {
                    $this->display_article_category($data, $cdata['article_cat_id'], $level +1);
                }
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">
                    <?php echo $this->locale['article_0162']; ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!$id) : ?>
            </tbody>
            </table></div>
        <?php endif;
    }
}
