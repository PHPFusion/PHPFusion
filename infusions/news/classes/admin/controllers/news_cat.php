<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_cat.php
| Author: PHP-Fusion Development Team
| Version: 9.2 prototype
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

class NewsCategoryAdmin extends NewsAdminModel {

    private static $instance = NULL;
    private $locale = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function displayNewsAdmin() {
        pageAccess("N");
        $this->locale = self::get_newsAdminLocale();
        if (isset($_GET['ref']) && $_GET['ref'] == "news_cat_form") {
            $this->display_news_cat_form();
        } else {
            $this->display_news_cat_listing();
        }
    }

    /**
     * Displays News Category Form
     */
    private function display_news_cat_form() {

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&section=news_category");
        }
        /**
         * Delete category images
         */
        if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $result = dbcount("(news_cat)", DB_NEWS, "news_cat='".$_GET['cat_id']."'") || dbcount("(news_cat_id)",
                                                                                                  DB_NEWS_CATS,
                                                                                                  "news_cat_parent='".$_GET['cat_id']."'");
            if (!empty($result)) {
                addNotice("success", $this->locale['news_0152'].$this->locale['news_0153']);
            } else {
                dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$_GET['cat_id']."'");
                addNotice("success", $this->locale['news_0154']);
            }
            // FUSION_REQUEST without the "action" gets
            redirect(clean_request("", array("action"), FALSE));
        }

        $data = array(
            "news_cat_id" => 0,
            "news_cat_name" => "",
            "news_cat_hidden" => array(),
            "news_cat_parent" => 0,
            "news_cat_image" => "",
            "news_cat_draft" => FALSE,
            "news_cat_visibility" => iGUEST,
            "news_cat_sticky" => FALSE,
            "news_cat_featured" => FALSE,
            "news_cat_language" => LANGUAGE,
        );

        $formAction = FUSION_REQUEST;

        $formTitle = $this->locale['news_0022'];

        // if edit, override $data
        if ((isset($_POST['save_cat'])) or (isset($_POST['save_cat_and_close']))) {
            $inputArray = array(
                "news_cat_id" => form_sanitizer($_POST['news_cat_id'], "", "news_cat_id"),
                "news_cat_name" => form_sanitizer($_POST['news_cat_name'], "", "news_cat_name"),
                "news_cat_parent" => form_sanitizer($_POST['news_cat_parent'], 0, "news_cat_parent"),
                "news_cat_visibility" => form_sanitizer($_POST['news_cat_visibility'], 0, "news_cat_visibility"),
                "news_cat_draft" => isset($_POST['news_cat_draft']) ? 1 : 0,
                "news_cat_sticky" => isset($_POST['news_cat_sticky']) ? 1 : 0,
                "news_cat_image" => form_sanitizer($_POST['news_cat_image'], "", "news_cat_image"),
                "news_cat_language" => form_sanitizer($_POST['news_cat_language'], LANGUAGE, "news_cat_language"),
            );

            $categoryNameCheck = array(
                "when_updating" => "news_cat_name='".$inputArray['news_cat_name']."' and news_cat_id !='".$inputArray['news_cat_id']."' ".(multilang_table("NS") ? "and news_cat_language = '".LANGUAGE."'" : ""),
                "when_saving" => "news_cat_name='".$inputArray['news_cat_name']."' ".(multilang_table("NS") ? "and news_cat_language = '".LANGUAGE."'" : ""),
            );

            if (\defender::safe()) {
                // check category name is unique when updating
                if (dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_id='".$inputArray['news_cat_id']."'")) {
                    if (!dbcount("(news_cat_id)", DB_NEWS_CATS, $categoryNameCheck['when_updating'])) {
                        dbquery_insert(DB_NEWS_CATS, $inputArray, "update");
                        addNotice("success", $this->locale['news_0151']);

                        if (isset($_POST['save_cat_and_close'])) {
                            redirect(clean_request("", array("action", "ref"), FALSE));
                        } else {
                            redirect(FUSION_REQUEST);
                        }

                    } else {
                        addNotice('danger', $this->locale['news_0352']);
                    }
                } else {
                    // check category name is unique when saving new
                    if (!dbcount("(news_cat_id)", DB_NEWS_CATS, $categoryNameCheck['when_saving'])) {
                        dbquery_insert(DB_NEWS_CATS, $inputArray, "save");
                        addNotice("success", $this->locale['news_0150']);

                        if (isset($_POST['save_cat_and_close'])) {
                            redirect(clean_request("", array("action", "ref"), FALSE));
                        } else {
                            redirect(FUSION_REQUEST);
                        }
                    } else {
                        addNotice('danger', $this->locale['news_0352']);
                    }
                }
            }

        } elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."' AND" : "WHERE")." news_cat_id='".$_GET['cat_id']."'");
            if (dbrows($result)) {
                $data = dbarray($result);
                $data['news_cat_hidden'] = array($data['news_cat_id']);
                $formTitle = $this->locale['news_0021'];
            } else {
                // FUSION_REQUEST without the "action" gets
                redirect(clean_request("", array("action"), FALSE));
            }
        }

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $formTitle]);
        echo "<div class='m-t-20 m-b-20'>\n";
        echo openform("addcat", "post", $formAction);
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_hidden("news_cat_id", "", $data['news_cat_id']);
                echo form_text("news_cat_name", $this->locale['news_0300'], $data['news_cat_name'], array(
                    "required" => TRUE,
                    "inline" => TRUE,
                    "error_text" => $this->locale['news_0351']
                ));
                echo form_select_tree("news_cat_parent", $this->locale['news_0305'], $data['news_cat_parent'], array(
                    "inline" => TRUE,
                    "disable_opts" => $data['news_cat_hidden'],
                    "hide_disabled" => TRUE,
                    "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
                ), DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");

                echo form_select("news_cat_image", $this->locale['news_0301'], $data['news_cat_image'], array(
                    "inline" => TRUE,
                    "options" => $this->newsCatImageOpts(),
                ));
                echo form_select('news_cat_visibility', $this->locale['news_0209'], $data['news_cat_visibility'], array(
                    'options' => fusion_get_groups(),
                    'placeholder' => $this->locale['choose'],
                    "inline" => TRUE,
                ));
                ?>
            </div>
            <div class="col-xs-12 col-sm-4">
                <?php
                if (multilang_table("NS")) {
                    echo form_select("news_cat_language", $this->locale['global_ML100'], $data['news_cat_language'], array(
                        "inline" => TRUE,
                        "options" => fusion_get_enabled_languages(),
                        "placeholder" => $this->locale['choose']
                    ));
                } else {
                    echo form_hidden("news_cat_language", "", $data['news_cat_language']);
                }
                openside("");
                echo form_checkbox("news_cat_draft", $this->locale['news_0306'], $data['news_cat_draft'], array("reverse_label" => TRUE));
                echo form_checkbox("news_cat_sticky", $this->locale['news_0307'], $data['news_cat_sticky'],
                                   array("reverse_label" => TRUE));
                echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default", 'icon' => 'fa fa-times'));
                echo form_button("save_cat", $this->locale['news_0302'], $this->locale['news_0302'], array("class" => "btn-success m-l-10", 'icon' => 'fa fa-hdd-o'));
                echo form_button("save_cat_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'],
                                 array("class" => "btn-primary", 'icon' => 'fa fa-hdd-o'));
                closeside();
                ?>
            </div>
        </div>
        <?php
        echo form_button("cancel", $this->locale['cancel'], $this->locale['cancel'], array("class" => "btn-default", 'icon' => 'fa fa-times'));
        echo form_button("save_cat", $this->locale['news_0302'], $this->locale['news_0302'], array("class" => "btn-success m-l-10", 'icon' => 'fa fa-hdd-o'));
        echo form_button("save_cat_and_close", $this->locale['save_and_close'], $this->locale['save_and_close'],
                         array("input_id" => 's2', "class" => "btn-primary m-l-10", 'icon' => 'fa fa-hdd-o'));
        echo "</div>\n";
    }

    private function newsCatImageOpts() {
        $image_files = makefilelist(IMAGES_NC, ".|..|index.php", TRUE);
        $image_list = array();
        foreach ($image_files as $image) {
            $image_list[$image] = $image;
        }

        return $image_list;
    }

    /**
     * Displays News Category Listing
     */
    private function display_news_cat_listing() {

        $_GET['rowstart'] = isset(
            $_GET['rowstart']) && isnum($_GET['rowstart']) &&
        $_GET['rowstart'] <= dbcount("(news_cat_id)", DB_NEWS_CATS, ""
        ) ? intval($_GET['rowstart']) : 0;

        // Run functions
        $allowed_actions = array_flip(array("publish", "unpublish", "sticky", "unsticky", "delete"));

        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = explode(",", form_sanitizer($_POST['news_cat_id'], "", "news_cat_id"));
            if (!empty($input)) {
                foreach ($input as $news_cat_id) {
                    // check input table
                    if (dbcount("('news_cat_id')", DB_NEWS_CATS,
                                "news_cat_id='".intval($news_cat_id)."'") && \defender::safe()
                    ) {
                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_draft='0' WHERE news_cat_id='".intval($news_cat_id)."'");
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_draft='1' WHERE news_cat_id='".intval($news_cat_id)."'");
                                break;
                            case "sticky":
                                dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_sticky='1' WHERE news_cat_id='".intval($news_cat_id)."'");
                                break;
                            case "unsticky":
                                dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_sticky='0' WHERE news_cat_id='".intval($news_cat_id)."'");
                                break;
                            case "delete":
                                if (!dbcount("('news_id')", DB_NEWS, "news_cat='".$news_cat_id."'")) {
                                    $result = dbquery("SELECT news_cat_image FROM ".DB_NEWS_CATS." WHERE news_cat_id='".intval($news_cat_id)."'");
                                    if (dbrows($result) > 0) {
                                        $photo = dbarray($result);
                                        if (!empty($photo['news_cat_image']) && file_exists(IMAGES_NC.$photo['news_cat_image'])) {
                                            unlink(IMAGES_NC.$photo['news_cat_image']);
                                        }
                                    }
                                    dbquery("DELETE FROM  ".DB_NEWS_CATS." WHERE news_cat_id='".intval($news_cat_id)."'");
                                } else {
                                    addNotice("warning", $this->locale['news_0153']);
                                }
                                break;
                            default:
                                // Not valid id
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice("success", $this->locale['news_0151']);
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", $this->locale['news_0155']);
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['news_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        // Switch to post
        $sql_condition = "";
        $search_string = array();
        if (isset($_POST['p-submit-news_cat_name'])) {
            $search_string['news_cat_name'] = array(
                "input" => form_sanitizer($_POST['news_cat_name'], "", "news_cat_name"), "operator" => "LIKE"
            );
        }

        if (!empty($_POST['news_cat_status']) && isnum($_POST['news_cat_status'])) {
            switch ($_POST['news_cat_status']) {
                case 1: // is a draft
                    $search_string['news_cat_draft'] = array("input" => 1, "operator" => "=");
                    break;
                case 2: // is a sticky
                    $search_string['news_cat_sticky'] = array("input" => 1, "operator" => "=");
                    break;
            }
        }

        if (!empty($_POST['news_cat_visibility'])) {
            $search_string['news_cat_visibility'] = array(
                "input" => form_sanitizer($_POST['news_cat_visibility'], "", "news_cat_visibility"), "operator" => "="
            );
        }

        if (!empty($_POST['news_cat_language'])) {
            $search_string['news_cat_language'] = array(
                "input" => form_sanitizer($_POST['news_cat_language'], "", "news_cat_language"), "operator" => "="
            );
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                $sql_condition .= " AND `$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }


        $result = dbquery_tree_full(DB_NEWS_CATS, "news_cat_id", "news_cat_parent", "",
            // Replacement Query
                                    "SELECT nc.*,
                            count(n1.news_id) 'news_published',
                            count(n2.news_id) 'news_draft',
                            count(n3.news_id) 'news_sticky'
                            FROM ".DB_NEWS_CATS." nc
                            LEFT JOIN ".DB_NEWS." n1 ON n1.news_id=nc.news_cat_id AND n1.news_draft='0' AND (n1.news_start='0'|| n1.news_start<=NOW()) AND (n1.news_end='0'|| n1.news_end>=NOW())
                            LEFT JOIN ".DB_NEWS." n2 ON n2.news_id=nc.news_cat_id AND n2.news_draft='1'
                            LEFT JOIN ".DB_NEWS." n3 ON n2.news_id=nc.news_cat_id AND n3.news_sticky='1' AND (n3.news_start='0'|| n3.news_start<=NOW()) AND (n3.news_end='0'|| n3.news_end>=NOW())
                            WHERE ".(multilang_table("NS") ? "news_cat_language='".LANGUAGE."'" : "")."
                            $sql_condition
                            GROUP BY news_cat_id
                            ORDER BY news_cat_parent ASC, news_cat_id ASC LIMIT ".intval($_GET['rowstart']).", 20"
        );
        ?>
        <div class="m-t-15">
            <?php

            echo openform("news_filter", "post", FUSION_REQUEST);
            echo "<div class='clearfix'>\n";

            echo "<div class='pull-right'>\n";

            echo "<a class='btn btn-success btn-sm m-r-10' href='".clean_request("ref=news_cat_form", array("ref"),
                                                                                 FALSE)."'><i class='fa fa-plus fa-fw'></i> ".$this->locale['news_0022']."</a>";

            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('publish');\"><i class='fa fa-check fa-fw'></i> ".$this->locale['publish']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unpublish');\"><i class='fa fa-ban fa-fw'></i> ".$this->locale['unpublish']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('sticky');\"><i class='fa fa-sticky-note fa-fw'></i> ".$this->locale['sticky']."</a>";
            echo "<a class='btn btn-default btn-sm m-r-10' onclick=\"run_admin('unsticky');\"><i class='fa fa-sticky-note-o fa-fw'></i> ".$this->locale['unsticky']."</a>";
            echo "<a class='btn btn-danger btn-sm m-r-10' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o fa-fw'></i> ".$this->locale['delete']."</a>";
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
                "news_cat_name" => !empty($_POST['news_cat_name']) ? form_sanitizer($_POST['news_cat_name'], "",
                                                                                    "news_cat_name") : "",
                "news_cat_status" => !empty($_POST['news_cat_status']) ? form_sanitizer($_POST['news_cat_status'], "",
                                                                                        "news_cat_status") : "",
                "news_cat_visibility" => !empty($_POST['news_cat_visibility']) ? form_sanitizer($_POST['news_cat_visibility'],
                                                                                                "",
                                                                                                "news_cat_visibility") : "",
                "news_cat_language" => !empty($_POST['news_cat_language']) ? form_sanitizer($_POST['news_cat_language'], "",
                                                                                            "news_cat_language") : "",
            );

            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                }
            }
            echo "<div class='display-inline-block pull-left m-r-10' style='width:300px;'>\n";
            echo form_text("news_cat_name", "", $filter_values['news_cat_name'], array(
                "placeholder" => $this->locale['news_0300'],
                "append_button" => TRUE,
                "append_value" => "<i class='fa fa-search'></i>",
                "append_form_value" => "search_news",
                "width" => "250px",
                "group_size" => "sm"
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>";
            echo "<a class='btn btn-sm ".($filter_empty == FALSE ? "btn-info" : " btn-default'")."' id='toggle_options' href='#'>".$this->locale['news_0242']."
        <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span></a>\n";
            echo form_button("news_clear", $this->locale['news_0243'], "clear", array('class' => 'btn-default btn-sm'));
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
            $('#news_status, #news_visibility, #news_category, #news_language, #news_author').bind('change', function(e){
                $(this).closest('form').submit();
            });
            ");
            unset($filter_values['news_text']);
            echo "<div id='news_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("news_cat_status", "", $filter_values['news_cat_status'], array(
                "allowclear" => TRUE, "placeholder" => "- ".$this->locale['news_0244']." -", "options" => array(
                    0 => $this->locale['news_0245'],
                    1 => $this->locale['news_0215'],
                    2 => $this->locale['sticky'],
                )
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("news_cat_visibility", "", $filter_values['news_cat_visibility'], array(
                "allowclear" => TRUE, "placeholder" => "-  ".$this->locale['news_0246']." -", "options" => fusion_get_groups()
            ));
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            $language_opts = array(0 => $this->locale['news_0249']);
            $language_opts += fusion_get_enabled_languages();
            echo form_select("news_cat_language", "", $filter_values['news_cat_language'], array(
                "allowclear" => TRUE, "placeholder" => "-  ".$this->locale['news_0250']." -", "options" => $language_opts
            ));
            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
            ?>
        </div>

        <?php echo openform("news_table", "post", FUSION_REQUEST);
        echo form_hidden("table_action", "", "");
        $this->display_news_category($result);
        echo closeform();
        echo "<div class='text-center'><a class='btn btn-primary' href='".ADMIN."images.php".fusion_get_aidlink()."&amp;ifolder=imagesnc'>".$this->locale['news_0304']."</a><br /><br />\n</div>\n";

    }

    /**
     * Recursive function to display administration table
     * @param     $data
     * @param int $id
     */
    private function display_news_category($data, $id = 0) {

        if (!$id) :
            ?>
            <table class="table table-responsive table-striped">
            <thead>
            <tr>
                <th></th>
                <th class="col-xs-4"><?php echo $this->locale['news_0300'] ?></th>
                <th><?php echo $this->locale['news_0301'] ?></th>
                <th><?php echo $this->locale['news_0253'] ?></th>
                <th><?php echo $this->locale['news_0215'] ?></th>
                <th><?php echo $this->locale['sticky'] ?></th>
                <th><?php echo $this->locale['news_0209'] ?></th>
                <th><?php echo $this->locale['language'] ?></th>
                <th><?php echo $this->locale['actions'] ?></th>
                <th>ID</th>
            </tr>
            </thead>
            <tbody>
        <?php endif; ?>

        <?php if (!empty($data[$id])) : ?>

            <?php foreach ($data[$id] as $cat_id => $cdata) :

                $edit_link = clean_request("section=news_category&ref=news_cat_form&action=edit&cat_id=".$cat_id,
                                           array("section", "ref", "action", "cat_id"), FALSE);

                $delete_link = clean_request("section=news_category&ref=news_cat_form&action=delete&cat_id=".$cat_id,
                                             array("section", "ref", "action", "cat_id"), FALSE);

                ?>
                <tr>
                    <td><?php echo form_checkbox("news_cat_id[]", "", "",
                                                 array("value" => $cat_id, "class" => "m-b-0")) ?></td>
                    <td>
                        <a class="text-dark" href="<?php echo $edit_link ?>">
                            <?php echo $cdata['news_cat_name'] ?>
                        </a>
                    </td>
                    <td>
                        <img style="width:50px" src="<?php echo get_image("nc_".$cdata['news_cat_name']) ?>"
                             alt="<?php echo $cdata['news_cat_name'] ?>"/>
                    </td>
                    <td>
                        <span class="badge"><?php echo sprintf($this->locale['news_0254'], $cdata['news_published']) ?></span>
                        <span class="label label-default m-r-10">
                            <i class="fa fa-star fa-fw"></i> <?php echo $cdata['news_draft'] ?>
                        </span>
                        <span class="label label-warning">
                            <i class="fa fa-sticky-note-o fa-fw"></i> <?php echo $cdata['news_sticky'] ?>
                        </span>
                    </td>
                    <td><span class="badge"><?php echo $cdata['news_cat_draft'] ? $this->locale['yes'] : $this->locale['no'] ?></span>
                    </td>
                    <td><span class="badge"><?php echo $cdata['news_cat_sticky'] ? $this->locale['yes'] : $this->locale['no'] ?></span>
                    </td>
                    <td><span class="badge"><?php echo getgroupname($cdata['news_cat_visibility']) ?></span></td>
                    <td><?php echo $cdata['news_cat_language'] ?></td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-xs btn-default" href="<?php echo $edit_link ?>">
                                <?php echo $this->locale['edit'] ?>
                            </a>
                            <a class="btn btn-xs btn-default"
                               href="<?php echo $delete_link ?>"
                               onclick="return confirm('<?php echo $this->locale['news_0350']; ?>')">
                                <?php echo $this->locale['delete'] ?>
                            </a>
                        </div>
                    </td>
                    <td>
                        <?php echo $cdata['news_cat_id'] ?>
                    </td>
                </tr>
                <?php
                if (isset($data[$cdata['news_cat_id']])) {
                    $this->display_news_category($data, $cdata['news_cat_id']);
                }
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center">
                    <?php echo $this->locale['news_0303'] ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!$id) : ?>
            </tbody>
            </table>
        <?php endif;
    }

    private function getNewsCatPath($item_id) {
        $full_path = "";
        while ($item_id > 0) {
            $result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent FROM ".DB_NEWS_CATS." WHERE news_cat_id='$item_id'".(multilang_table("NS") ? " AND news_cat_language='".LANGUAGE."'" : ""));
            if (dbrows($result)) {
                $data = dbarray($result);
                if ($full_path) {
                    $full_path = " / ".$full_path;
                }
                $full_path = $data['news_cat_name'].$full_path;
                $item_id = $data['news_cat_parent'];
            }
        }

        return $full_path;
    }

}