<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_cat.php
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
namespace PHPFusion\News;

use PHPFusion\BreadCrumbs;

class NewsCategoryAdmin extends NewsAdminModel {
    private static $instance = NULL;
    private static $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayNewsAdmin() {
        pageAccess("N");
        self::$locale = self::get_newsAdminLocale();
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
            $result = dbcount("(news_cat)", DB_NEWS, "news_cat='".$_GET['cat_id']."'") || dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_parent='".$_GET['cat_id']."'");
            if (!empty($result)) {
                addNotice("success", self::$locale['news_0152'].self::$locale['news_0153']);
            } else {
                dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$_GET['cat_id']."'");
                addNotice("success", self::$locale['news_0154']);
            }
            // FUSION_REQUEST without the "action" gets
            redirect(clean_request("", ["action", "ref", "cat_id"], FALSE));
        }

        $data = [
            "news_cat_id"         => 0,
            "news_cat_name"       => "",
            "news_cat_hidden"     => [],
            "news_cat_parent"     => 0,
            "news_cat_image"      => "",
            "news_cat_draft"      => FALSE,
            "news_cat_visibility" => iGUEST,
            "news_cat_sticky"     => FALSE,
            "news_cat_featured"   => FALSE,
            "news_cat_language"   => LANGUAGE,
        ];

        $formAction = FUSION_REQUEST;

        $formTitle = self::$locale['news_0022'];

        // if edit, override $data
        if ((isset($_POST['save_cat'])) or (isset($_POST['save_cat_and_close']))) {
            $inputArray = [
                "news_cat_id"         => form_sanitizer($_POST['news_cat_id'], "", "news_cat_id"),
                "news_cat_name"       => form_sanitizer($_POST['news_cat_name'], "", "news_cat_name"),
                "news_cat_parent"     => form_sanitizer($_POST['news_cat_parent'], 0, "news_cat_parent"),
                "news_cat_visibility" => form_sanitizer($_POST['news_cat_visibility'], 0, "news_cat_visibility"),
                "news_cat_draft"      => isset($_POST['news_cat_draft']) ? 1 : 0,
                "news_cat_sticky"     => isset($_POST['news_cat_sticky']) ? 1 : 0,
                "news_cat_image"      => form_sanitizer($_POST['news_cat_image'], "", "news_cat_image"),
                "news_cat_language"   => form_sanitizer($_POST['news_cat_language'], LANGUAGE, "news_cat_language"),
            ];

            $categoryNameCheck = [
                "when_updating" => "news_cat_name='".$inputArray['news_cat_name']."' and news_cat_id !='".$inputArray['news_cat_id']."' ".(multilang_table("NS") ? "and ".in_group('news_cat_language', LANGUAGE) : ""),
                "when_saving"   => "news_cat_name='".$inputArray['news_cat_name']."' ".(multilang_table("NS") ? "and ".in_group('news_cat_language', LANGUAGE) : ""),
            ];

            if (\defender::safe()) {
                // check category name is unique when updating
                if (dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_id='".$inputArray['news_cat_id']."'")) {
                    if (!dbcount("(news_cat_id)", DB_NEWS_CATS, $categoryNameCheck['when_updating'])) {
                        dbquery_insert(DB_NEWS_CATS, $inputArray, "update");
                        addNotice("success", self::$locale['news_0151']);

                        if (isset($_POST['save_cat_and_close'])) {
                            redirect(clean_request("", ["action", "ref"], FALSE));
                        } else {
                            redirect(FUSION_REQUEST);
                        }

                    } else {
                        addNotice('danger', self::$locale['news_0352']);
                    }
                } else {
                    // check category name is unique when saving new
                    if (!dbcount("(news_cat_id)", DB_NEWS_CATS, $categoryNameCheck['when_saving'])) {
                        dbquery_insert(DB_NEWS_CATS, $inputArray, "save");
                        addNotice("success", self::$locale['news_0150']);

                        if (isset($_POST['save_cat_and_close'])) {
                            redirect(clean_request("", ["action", "ref"], FALSE));
                        } else {
                            redirect(FUSION_REQUEST);
                        }
                    } else {
                        addNotice('danger', self::$locale['news_0352']);
                    }
                }
            }

        } else if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $result = dbquery("SELECT * FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE ".in_group('news_cat_language', LANGUAGE)." AND" : "WHERE")." news_cat_id='".$_GET['cat_id']."'");
            if (dbrows($result)) {
                $data = dbarray($result);
                $data['news_cat_hidden'] = [$data['news_cat_id']];
                $formTitle = self::$locale['news_0021'];
            } else {
                // FUSION_REQUEST without the "action" gets
                redirect(clean_request("", ["action"], FALSE));
            }
        }
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $formTitle]);
        echo "<div class='m-t-20 m-b-20'>\n";
        echo openform("addcat", "post", $formAction);
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-8">
                <?php
                echo form_hidden("news_cat_id", "", $data['news_cat_id']);
                echo form_text("news_cat_name", self::$locale['news_0300'], $data['news_cat_name'], [
                    "required"   => TRUE,
                    "inline"     => TRUE,
                    "error_text" => self::$locale['news_0351']
                ]);
                echo form_select_tree("news_cat_parent", self::$locale['news_0305'], $data['news_cat_parent'], [
                    "inline"        => TRUE,
                    "disable_opts"  => $data['news_cat_hidden'],
                    "hide_disabled" => TRUE,
                    "query"         => (multilang_table("NS") ? "WHERE ".in_group('news_cat_language', LANGUAGE) : "")
                ], DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");

                echo form_select("news_cat_image", self::$locale['news_0301'], $data['news_cat_image'], [
                    "inline"  => TRUE,
                    "options" => $this->newsCatImageOpts(),
                ]);
                echo form_select('news_cat_visibility', self::$locale['news_0209'], $data['news_cat_visibility'], [
                    'options'     => fusion_get_groups(),
                    'placeholder' => self::$locale['choose'],
                    "inline"      => TRUE,
                ]);
                ?>
            </div>
            <div class="col-xs-12 col-sm-4">
                <?php
                if (multilang_table("NS")) {
                    echo form_select("news_cat_language[]", self::$locale['global_ML100'], $data['news_cat_language'], [
                        "inline"      => TRUE,
                        "options"     => fusion_get_enabled_languages(),
                        "placeholder" => self::$locale['choose'],
                        'multiple'    => TRUE,
                        'delimeter'   => '.'
                    ]);
                } else {
                    echo form_hidden("news_cat_language", "", $data['news_cat_language']);
                }
                openside("");
                echo form_checkbox("news_cat_draft", self::$locale['news_0306'], $data['news_cat_draft'], ["reverse_label" => TRUE]);
                echo form_checkbox("news_cat_sticky", self::$locale['news_0307'], $data['news_cat_sticky'],
                    ["reverse_label" => TRUE]);
                echo form_button("cancel", self::$locale['cancel'], self::$locale['cancel'], ["class" => "btn-default", 'icon' => 'fa fa-times']);
                echo form_button("save_cat", self::$locale['news_0302'], self::$locale['news_0302'], ["class" => "btn-success m-l-10", 'icon' => 'fa fa-hdd-o']);
                echo form_button("save_cat_and_close", self::$locale['save_and_close'], self::$locale['save_and_close'],
                    ["class" => "btn-primary", 'icon' => 'fa fa-hdd-o']);
                closeside();
                ?>
            </div>
        </div>
        <?php
        echo form_button("cancel", self::$locale['cancel'], self::$locale['cancel'], ["class" => "btn-default", 'icon' => 'fa fa-times']);
        echo form_button("save_cat", self::$locale['news_0302'], self::$locale['news_0302'], ["class" => "btn-success m-l-10", 'icon' => 'fa fa-hdd-o']);
        echo form_button("save_cat_and_close", self::$locale['save_and_close'], self::$locale['save_and_close'], ["input_id" => 's2', "class" => "btn-primary m-l-10", 'icon' => 'fa fa-hdd-o']);
        echo "</div>\n";
    }

    private function newsCatImageOpts() {
        $image_files = makefilelist(IMAGES_NC, ".|..|index.php", TRUE);
        $image_list = [];
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
        $allowed_actions = array_flip(["publish", "unpublish", "sticky", "unsticky", "delete"]);

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
                                    addNotice("warning", self::$locale['news_0153']);
                                }
                                break;
                            default:
                                // Not valid id
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                addNotice("success", self::$locale['news_0151']);
                redirect(FUSION_REQUEST);
            }
            addNotice("warning", self::$locale['news_0155']);
            redirect(FUSION_REQUEST);
        }

        if (isset($_POST['news_clear'])) {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }
        // Switch to post
        $sql_condition = "";
        $search_string = [];
        if (isset($_POST['p-submit-news_cat_name'])) {
            $search_string['news_cat_name'] = [
                "input" => form_sanitizer($_POST['news_cat_name'], "", "news_cat_name"), "operator" => "LIKE"
            ];
        }
        if (!empty($_POST['news_cat_status']) && isnum($_POST['news_cat_status'])) {
            switch ($_POST['news_cat_status']) {
                case 1: // is a draft
                    $search_string['news_cat_draft'] = ["input" => 1, "operator" => "="];
                    break;
                case 2: // is a sticky
                    $search_string['news_cat_sticky'] = ["input" => 1, "operator" => "="];
                    break;
            }
        }
        if (!empty($_POST['news_cat_visibility'])) {
            $search_string['news_cat_visibility'] = [
                "input" => form_sanitizer($_POST['news_cat_visibility'], "", "news_cat_visibility"), "operator" => "="
            ];
        }

        if (multilang_table("NS")) {
            $sql_condition = in_group('news_cat_language', LANGUAGE);
        }
        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition) $sql_condition .= " AND ";
                $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
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
                            ".($sql_condition ? " WHERE ".$sql_condition : "")."
                            GROUP BY news_cat_id
                            ORDER BY news_cat_parent ASC, news_cat_id ASC LIMIT ".intval($_GET['rowstart']).", 20"
        );
        ?>
        <div class="m-t-15">
            <?php
            echo openform("news_filter", "post", FUSION_REQUEST);
            echo "<div class='clearfix'>\n";

            echo "<div class='pull-right'>\n";
            echo "<a class='btn btn-success btn-sm' href='".clean_request("ref=news_cat_form", ["ref"], FALSE)."'><i class='fa fa-plus'></i> ".self::$locale['news_0022']."</a>";
            echo "<button type='button' class='hidden-xs btn m-l-5 btn-default btn-sm' onclick=\"run_admin('publish', '#table_action', '#news_table');\"><i class='fa fa-check'></i> ".self::$locale['publish']."</button>";
            echo "<button type='button' class='hidden-xs btn m-l-5 btn-default btn-sm' onclick=\"run_admin('unpublish', '#table_action', '#news_table');\"><i class='fa fa-ban'></i> ".self::$locale['unpublish']."</button>";
            echo "<button type='button' class='hidden-xs btn m-l-5 btn-default btn-sm' onclick=\"run_admin('sticky', '#table_action', '#news_table');\"><i class='fa fa-sticky-note'></i> ".self::$locale['sticky']."</button>";
            echo "<button type='button' class='hidden-xs btn m-l-5 btn-default btn-sm' onclick=\"run_admin('unsticky', '#table_action', '#news_table');\"><i class='fa fa-sticky-note-o'></i> ".self::$locale['unsticky']."</button>";
            echo "<button type='button' class='hidden-xs btn m-l-5 btn-danger btn-sm' onclick=\"run_admin('delete', '#table_action', '#news_table');\"><i class='fa fa-trash-o'></i> ".self::$locale['delete']."</button>";
            echo "</div>\n";

            $filter_values = [
                "news_cat_name"       => !empty($_POST['news_cat_name']) ? form_sanitizer($_POST['news_cat_name'], "", "news_cat_name") : "",
                "news_cat_status"     => !empty($_POST['news_cat_status']) ? form_sanitizer($_POST['news_cat_status'], "", "news_cat_status") : "",
                "news_cat_visibility" => !empty($_POST['news_cat_visibility']) ? form_sanitizer($_POST['news_cat_visibility'], "", "news_cat_visibility") : ""
            ];
            $filter_empty = TRUE;
            foreach ($filter_values as $val) {
                if ($val) {
                    $filter_empty = FALSE;
                }
            }
            echo "<div class='display-inline-block pull-left m-r-10'>\n";
            echo form_text("news_cat_name", '', $filter_values['news_cat_name'], [
                "placeholder"       => self::$locale['news_0300'],
                "append_button"     => TRUE,
                "append_value"      => "<i class='fa fa-search'></i> ".self::$locale['search'],
                "append_form_value" => "search_news",
                "width"             => "170px",
                'group_size'        => 'sm'
            ]);
            echo "</div>\n";
            echo "<div class='display-inline-block hidden-xs'>";
            echo "<a class='btn btn-sm".($filter_empty == FALSE ? " btn-info" : " btn-default'")."' id='toggle_options' href='#'>\n";
            echo self::$locale['news_0242']." <span id='filter_caret' class='fa ".($filter_empty == FALSE ? "fa-caret-up" : "fa-caret-down")."'></span>\n";
            echo "</a>\n";
            echo form_button("news_clear", self::$locale['news_0243'], "clear", ['class' => 'btn-default btn-sm']);
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
            $('#news_status, #news_visibility, #news_category, #news_author').bind('change', function(e){
                $(this).closest('form').submit();
            });
            ");
            unset($filter_values['news_text']);
            echo "<div id='news_filter_options'".($filter_empty == FALSE ? "" : " style='display:none;'").">\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("news_cat_status", "", $filter_values['news_cat_status'], [
                "allowclear" => TRUE, "placeholder" => "- ".self::$locale['news_0244']." -", "options" => [
                    0 => self::$locale['news_0245'],
                    1 => self::$locale['news_0215'],
                    2 => self::$locale['sticky'],
                ]
            ]);
            echo "</div>\n";
            echo "<div class='display-inline-block'>\n";
            echo form_select("news_cat_visibility", "", $filter_values['news_cat_visibility'], [
                "allowclear" => TRUE, "placeholder" => "-  ".self::$locale['news_0246']." -", "options" => fusion_get_groups()
            ]);
            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
            ?>
        </div>
        <?php echo openform("news_table", "post", FUSION_REQUEST);
        echo form_hidden("table_action", "", "");
        $this->display_news_category($result);
        echo closeform();
        echo "<div class='text-center'><a class='btn btn-primary' href='".ADMIN."images.php".fusion_get_aidlink()."&amp;ifolder=imagesnc'>".self::$locale['news_0304']."</a><br /><br />\n</div>\n";
    }

    /**
     * Recursive function to display administration table
     *
     * @param     $data
     * @param int $id
     */
    private function display_news_category($data, $id = 0) {

        if (!$id) :
            ?>
            <div class="table-responsive"><table class="table table-hover">
            <thead>
            <tr>
                <th class="hidden-xs"></th>
                <th class="col-xs-3"><?php echo self::$locale['news_0300'] ?></th>
                <th><?php echo self::$locale['news_0253'] ?></th>
                <th><?php echo self::$locale['news_0215'] ?></th>
                <th><?php echo self::$locale['sticky'] ?></th>
                <th><?php echo self::$locale['news_0209'] ?></th>
                <th><?php echo self::$locale['actions'] ?></th>
            </tr>
            </thead>
            <tbody>
        <?php endif;
        if (!empty($data[$id])) :
            foreach ($data[$id] as $cat_id => $cdata) :
                $edit_link = clean_request("section=news_category&ref=news_cat_form&action=edit&cat_id=".$cat_id, ["section", "ref", "action", "cat_id"], FALSE);
                $delete_link = clean_request("section=news_category&ref=news_cat_form&action=delete&cat_id=".$cat_id, ["section", "ref", "action", "cat_id"], FALSE);
                ?>
                <tr id="cat<?php echo $cat_id; ?>">
                    <td class="hidden-xs"><?php echo form_checkbox("news_cat_id[]", "", "", ["value" => $cat_id, "input_id" => "checkbox".$cat_id, "class" => "m-b-0"]);
                        add_to_jquery('$("#checkbox'.$cat_id.'").click(function() {
                        if ($(this).prop("checked")) {
                            $("#cat'.$cat_id.'").addClass("active");
                        } else {
                            $("#cat'.$cat_id.'").removeClass("active");
                        }
                    });');
                        ?></td>
                    <td><a class="text-dark" href="<?php echo $edit_link ?>"><img style="width:25px" class="display-inline-block m-r-15" src="<?php echo get_image("nc_".$cdata['news_cat_name']) ?>" alt="<?php echo $cdata['news_cat_name'] ?>"/><?php echo $cdata['news_cat_name'] ?></a></td>
                    <td>
                        <span class="badge"><?php echo sprintf(self::$locale['news_0254'], $cdata['news_published']) ?></span>
                        <span class="label label-default m-r-10"><i class="fa fa-star fa-fw"></i> <?php echo $cdata['news_draft'] ?></span>
                        <span class="label label-warning"><i class="fa fa-sticky-note-o fa-fw"></i> <?php echo $cdata['news_sticky'] ?></span>
                    </td>
                    <td><span class="badge"><?php echo $cdata['news_cat_draft'] ? self::$locale['yes'] : self::$locale['no'] ?></span></td>
                    <td><span class="badge"><?php echo $cdata['news_cat_sticky'] ? self::$locale['yes'] : self::$locale['no'] ?></span></td>
                    <td><span class="badge"><?php echo getgroupname($cdata['news_cat_visibility']) ?></span></td>
                    <td>
                        <div class="btn-group m-0">
                            <a class="btn btn-xs btn-default" href="<?php echo $edit_link ?>"><?php echo self::$locale['edit'] ?></a>
                            <a class="btn btn-xs btn-default" href="<?php echo $delete_link ?>" onclick="return confirm('<?php echo self::$locale['news_0282']; ?>')"><?php echo self::$locale['delete'] ?></a>
                        </div>
                    </td>
                </tr>
                <?php
                if (isset($data[$cdata['news_cat_id']])) {
                    $this->display_news_category($data, $cdata['news_cat_id']);
                }
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center"><?php echo self::$locale['news_0303'] ?></td></tr>
        <?php endif; ?>

        <?php if (!$id) : ?>
            </tbody>
            </table></div>
        <?php endif;
    }
}
