<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/admin/controllers/weblinks_cat.php
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
namespace PHPFusion\Weblinks;

class WeblinksCategoryAdmin extends WeblinksAdminModel {
    private static $instance = NULL;
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayWeblinksAdmin() {
        pageAccess("W");
        $this->locale = self::get_WeblinkAdminLocale();

        // Cancel Form
        $cancel = filter_input(INPUT_GET, 'cancel', FILTER_DEFAULT);
        if (!empty($cancel)) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&section=weblinks_category");
        }

        $ref = filter_input(INPUT_GET, 'ref');
        if (!empty($ref) && $ref == "weblink_cat_form") {
            $this->display_weblinks_cat_form();
        } else {
            $this->display_weblinks_cat_listing();
        }
    }

    /**
     * Displays weblink Category Form
     */
    private function display_weblinks_cat_form() {
        // Empty
        $data = [
            'weblink_cat_id'          => 0,
            'weblink_cat_name'        => '',
            'weblink_cat_parent'      => 0,
            'weblink_cat_description' => '',
            'weblink_cat_status'      => 1,
            'weblink_cat_visibility'  => iGUEST,
            'weblink_cat_language'    => LANGUAGE,
        ];

        // Save
        $save_cat = filter_input(INPUT_POST, 'save_cat', FILTER_DEFAULT) || filter_input(INPUT_POST, 'save_cat_and_close', FILTER_DEFAULT);
        $action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT);
        $cat_id = filter_input(INPUT_GET, 'cat_id', FILTER_VALIDATE_INT);
        if (!empty($save_cat)) {
            // Description
            $cat_desc = "";
            $weblink_cat_description = filter_input(INPUT_POST, 'weblink_cat_description', FILTER_DEFAULT);
            if (!empty($weblink_cat_description)) {
                $cat_desc = (fusion_get_settings("allow_php_exe") ? htmlspecialchars($weblink_cat_description) : $weblink_cat_description);
            }
            // Check Fields
            $inputArray = [
                'weblink_cat_id'          => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat_id', FILTER_DEFAULT), '', 'weblink_cat_id'),
                'weblink_cat_name'        => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat_name', FILTER_DEFAULT), '', 'weblink_cat_name'),
                'weblink_cat_description' => form_sanitizer($cat_desc, '', 'weblink_cat_description'),
                'weblink_cat_parent'      => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat_parent', FILTER_VALIDATE_INT), 0, 'weblink_cat_parent'),
                'weblink_cat_visibility'  => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat_visibility', FILTER_VALIDATE_INT), 0, 'weblink_cat_visibility'),
                'weblink_cat_status'      => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat_status', FILTER_VALIDATE_INT), 0, 'weblink_cat_status'),
                'weblink_cat_language'    => form_sanitizer(filter_input(INPUT_POST, 'weblink_cat_language', FILTER_DEFAULT), LANGUAGE, 'weblink_cat_language')
            ];
            // Check Where Condition
            $categoryNameCheck = [
                'when_updating' => "weblink_cat_name='".$inputArray['weblink_cat_name']."' and weblink_cat_id !='".$inputArray['weblink_cat_id']."' ".(multilang_table("WL") ? "and ".in_group('weblink_cat_language', LANGUAGE) : ""),
                'when_saving'   => "weblink_cat_name='".$inputArray['weblink_cat_name']."' ".(multilang_table("WL") ? "and ".in_group('weblink_cat_language', LANGUAGE) : ""),
            ];

            // Save
            if (\defender::safe()) {
                // Update
                if (dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_id=:catid", [':catid' => $inputArray['weblink_cat_id']])) {
                    if (!dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, $categoryNameCheck['when_updating'])) {
                        dbquery_insert(DB_WEBLINK_CATS, $inputArray, 'update');
                        addNotice('success', $this->locale['WLS_0041']);
                    } else {
                        \defender::stop();
                        addNotice('danger', $this->locale['WLS_0321']);
                    }
                    // Insert
                } else {
                    if (!dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, $categoryNameCheck['when_saving'])) {
                        $inputArray['weblink_cat_id'] = dbquery_insert(DB_WEBLINK_CATS, $inputArray, 'save');
                        addNotice('success', $this->locale['WLS_0040']);
                    } else {
                        \defender::stop();
                        addNotice('danger', $this->locale['WLS_0321']);
                    }
                }
                if (\defender::safe()) {
                    if (isset($_POST['save_cat_and_close'])) {
                        redirect(clean_request('', ['action', 'ref'], FALSE));
                    } else {
                        redirect(clean_request('action=edit&cat_id='.$inputArray['weblink_cat_id'], ['action', 'weblink_id'], FALSE));
                    }
                }
            }

            $data = $inputArray;

            // Edit
        } else if ((!empty($action) && $action == "edit") && $cat_id) {
            $result = dbquery("SELECT * FROM ".DB_WEBLINK_CATS." ".(multilang_table("WL") ? "WHERE ".in_group('weblink_cat_language', LANGUAGE)." AND" : "WHERE")." weblink_cat_id='".(int)$cat_id."'");
            if (dbrows($result)) {
                $data = dbarray($result);
            } else {
                redirect(clean_request('', ['action'], FALSE));
            }

            // Delete
        } else if ((!empty($action) && $action == "delete") && $cat_id) {
            if (!dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat = :catid", [':catid' => (int)$cat_id]) && !dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_parent = :catparent", [':catparent' => (int)$cat_id])) {
                dbquery("DELETE FROM  ".DB_WEBLINK_CATS." WHERE weblink_cat_id = :catid", [':catid' => (int)$cat_id]);
                addNotice("success", $this->locale['WLS_0042']);
            } else {
                addNotice("warning", $this->locale['WLS_0043']);
                addNotice("warning", $this->locale['WLS_0044']);
            }
            redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
        }

        echo "<div class='m-t-20 m-b-20'>";
        echo openform("catform", "post", FUSION_REQUEST);
        echo "<div class='row'>";
        echo "<div class='col-xs-12 col-sm-8'>";
        echo form_hidden('weblink_cat_id', '', $data['weblink_cat_id']);
        echo form_text('weblink_cat_name', $this->locale['WLS_0100'], $data['weblink_cat_name'], [
            'required'   => TRUE,
            'error_text' => $this->locale['WLS_0320']
        ]);
        echo form_select_tree('weblink_cat_parent', $this->locale['WLS_0303'], $data['weblink_cat_parent'], [
            'disable_opts'  => $data['weblink_cat_id'],
            'hide_disabled' => TRUE,
            'query'         => (multilang_table("WL") ? "WHERE ".in_group('weblink_cat_language', LANGUAGE) : "")
        ], DB_WEBLINK_CATS, "weblink_cat_name", "weblink_cat_id", "weblink_cat_parent");
        echo form_textarea('weblink_cat_description', $this->locale['WLS_0254'], $data['weblink_cat_description'], [
            'autosize'  => TRUE,
            'type'      => 'bbcode',
            'form_name' => 'catform',
            'preview'   => TRUE,
        ]);
        echo "</div>";
        //<!-- Right Column -->
        echo "<div class='col-xs-12 col-sm-4'>";
        openside($this->locale['WLS_0260']);
        if (multilang_table("WL")) {
            echo form_select('weblink_cat_language[]', $this->locale['language'], $data['weblink_cat_language'], [
                'inner_width' => '100%',
                'options'     => fusion_get_enabled_languages(),
                'placeholder' => $this->locale['choose'],
                'multiple'    => TRUE,
                'delimeter'   => '.'
            ]);
        } else {
            echo form_hidden('weblink_cat_language', '', $data['weblink_cat_language']);
        }
        echo form_select('weblink_cat_visibility', $this->locale['WLS_0103'], $data['weblink_cat_visibility'], [
            'inner_width' => '100%',
            'options'     => fusion_get_groups(),
            'placeholder' => $this->locale['choose'],
        ]);

        echo form_select('weblink_cat_status', $this->locale['WLS_0102'], $data['weblink_cat_status'], [
            'inner_width' => '100%',
            'options'     => [1 => $this->locale['publish'], 0 => $this->locale['unpublish']],
            'placeholder' => $this->locale['choose'],
        ]);
        closeside();
        echo "</div>";
        echo "</div>";
        echo form_button('cancel', $this->locale['cancel'], $this->locale['cancel'], ['class' => 'btn-default', 'icon' => 'fa fa-fw fa-close']);
        echo form_button('save_cat', $this->locale['save'], $this->locale['save'], ['class' => 'btn-success m-l-10', 'icon' => 'fa fa-fw fa-hdd-o']);
        echo form_button('save_cat_and_close', $this->locale['save_and_close'], $this->locale['save_and_close'], ['class' => 'btn-primary m-l-10', 'icon' => 'fa fa-fw fa-floppy-o']);
        echo closeform();
        echo "</div>";
    }

    /**
     * Displays Articles Category Listing
     */
    private function display_weblinks_cat_listing() {
        // Run functions
        $allowed_actions = array_flip(['publish', 'unpublish', 'delete']);

        // Table Actions
        if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {

            $input = !empty($_POST['weblink_cat_id']) ? form_sanitizer($_POST['weblink_cat_id'], "", "weblink_cat_id") : "";
            if (!empty($input)) {
                $input = ($input ? explode(",", $input) : []);
                foreach ($input as $weblink_cat_id) {
                    // check input table
                    if (dbcount("('weblink_cat_id')", DB_WEBLINK_CATS,
                            "weblink_cat_id=:catid", [':catid' => (int)$weblink_cat_id]) && \defender::safe()
                    ) {
                        switch ($_POST['table_action']) {
                            case "publish":
                                dbquery("UPDATE ".DB_WEBLINK_CATS." SET weblink_cat_status=:status WHERE weblink_cat_id=:catid", [':status' => '1', ':catid' => (int)$weblink_cat_id]);
                                addNotice('success', $this->locale['WLS_0049']);
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_WEBLINK_CATS." SET weblink_cat_status=:status WHERE weblink_cat_id=:catid", [':status' => '0', ':catid' => (int)$weblink_cat_id]);
                                addNotice('warning', $this->locale['WLS_0050']);
                                break;
                            case "delete":
                                if (!dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat=:catid", [':catid' => (int)$weblink_cat_id]) && !dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_parent=:catparent", [':catparent' => (int)$weblink_cat_id])) {
                                    dbquery("DELETE FROM  ".DB_WEBLINK_CATS." WHERE weblink_cat_id=:catid", [':catid' => (int)$weblink_cat_id]);
                                    addNotice('warning', $this->locale['WLS_0042']);
                                } else {
                                    addNotice('warning', $this->locale['WLS_0046']);
                                    addNotice('warning', $this->locale['WLS_0044']);
                                }
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
                redirect(FUSION_REQUEST);
            } else {
                addNotice('warning', $this->locale['WLS_0048']);
                redirect(FUSION_REQUEST);
            }
        }

        // Clear
        $weblink_clear = filter_input(INPUT_POST, 'weblink_clear', FILTER_DEFAULT);
        if (!empty($weblink_clear)) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&amp;section=weblinks_category");
        }

        // Search
        $sql_condition = "";
        $search_string = [];
        $p_submit_weblink_cat_name = filter_input(INPUT_POST, 'p-submit-weblink_cat_name', FILTER_DEFAULT);
        $weblink_cat_name = filter_input(INPUT_POST, 'weblink_cat_name', FILTER_DEFAULT);
        if (!empty($p_submit_weblink_cat_name)) {
            $search_string['weblink_cat_name'] = [
                'input' => form_sanitizer($weblink_cat_name, '', 'weblink_cat_name'), 'operator' => "LIKE", 'option' => "AND"
            ];
        }

        $weblink_cat_status = filter_input(INPUT_POST, 'weblink_cat_status', FILTER_VALIDATE_INT);
        if (!empty($weblink_cat_status)) {
            $search_string['weblink_cat_status'] = [
                'input' => form_sanitizer($weblink_cat_status, '', 'weblink_cat_status') - 1, 'operator' => "="
            ];
        }

        $weblink_cat_visibility = filter_input(INPUT_POST, 'weblink_cat_visibility', FILTER_DEFAULT);
        if (!empty($_POST['weblink_cat_visibility'])) {
            $search_string['weblink_cat_visibility'] = [
                'input' => form_sanitizer($weblink_cat_visibility, '', 'weblink_cat_visibility'), 'operator' => "="
            ];
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                $sql_condition .= " ".$values['option']." `$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
            }
        }

        // Query
        $result = dbquery_tree_full(DB_WEBLINK_CATS, "weblink_cat_id", "weblink_cat_parent", "",
            "SELECT ac.*, COUNT(a.weblink_id) AS weblink_count
            FROM ".DB_WEBLINK_CATS." ac
            LEFT JOIN ".DB_WEBLINKS." AS a ON a.weblink_cat=ac.weblink_cat_id
            WHERE ".(multilang_table("WL") ? in_group('ac.weblink_cat_language', LANGUAGE) : "")."
            $sql_condition
            GROUP BY ac.weblink_cat_id
            ORDER BY ac.weblink_cat_parent ASC, ac.weblink_cat_id ASC"
        );

        // Filters
        $filter_values = [
            'weblink_cat_name'       => !empty($weblink_cat_name) ? form_sanitizer($weblink_cat_name, '', 'weblink_cat_name') : '',
            'weblink_cat_status'     => !empty($weblink_cat_status) ? form_sanitizer($weblink_cat_status, '', 'weblink_cat_status') : '',
            'weblink_cat_visibility' => !empty($weblink_cat_visibility) ? form_sanitizer($weblink_cat_visibility, '', 'weblink_cat_visibility') : ''
        ];

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }

        // Languages
        $language_opts = [0 => $this->locale['WLS_0129']];
        $language_opts += fusion_get_enabled_languages();
        ?>

        <!-- Display Search, Filters and Actions -->
        <div class="m-t-15">
            <?php echo openform("weblink_filter", "post", FUSION_REQUEST); ?>
            <div class="clearfix">

                <!-- Actions -->
                <div class="pull-right">
                    <a class="btn btn-success btn-sm" href="<?php echo clean_request("ref=weblink_cat_form", ["ref"], FALSE); ?>"><i class="fa fa-fw fa-plus"></i> <?php echo $this->locale['WLS_0005']; ?></a>
                    <button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin('publish', '#table_action', '#weblink_table');"><i class="fa fa-fw fa-check"></i> <?php echo $this->locale['publish']; ?></button>
                    <button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin('unpublish', '#table_action', '#weblink_table');"><i class="fa fa-fw fa-ban"></i> <?php echo $this->locale['unpublish']; ?></button>
                    <button type="button" class="hidden-xs btn btn-danger btn-sm m-l-5" onclick="run_admin('delete', '#table_action', '#weblink_table');"><i class="fa fa-fw fa-trash-o"></i> <?php echo $this->locale['delete']; ?></button>
                </div>

                <!-- Search -->
                <div class="display-inline-block pull-left m-r-10">
                    <?php echo form_text('weblink_cat_name', '', $filter_values['weblink_cat_name'], [
                        'placeholder'       => $this->locale['WLS_0100'],
                        'append_button'     => TRUE,
                        'append_value'      => "<i class='fa fa-fw fa-search'></i>",
                        'append_form_value' => 'search_weblink',
                        'width'             => '200px',
                        'group_size'        => 'sm'
                    ]); ?>
                </div>
                <div class="display-inline-block hidden-xs">
                    <a class="btn btn-sm m-r-10 <?php echo(!$filter_empty ? "btn-info" : "btn-default"); ?>" id="toggle_options" href="#">
                        <?php echo $this->locale['WLS_0121']; ?>
                        <span id="filter_caret" class="fa <?php echo(!$filter_empty ? "fa-caret-up" : "fa-caret-down"); ?>"></span>
                    </a>
                    <?php echo form_button('weblink_clear', $this->locale['WLS_0122'], 'clear', ['class' => 'btn-default btn-sm']); ?>
                </div>
            </div>

            <!-- Display Filters -->
            <div id="weblink_filter_options"<?php echo($filter_empty ? " style='display: none;'" : ""); ?>>
                <div class="display-inline-block">
                    <?php echo form_select('weblink_cat_status', '', $filter_values['weblink_cat_status'], [
                        'allowclear'  => TRUE,
                        'placeholder' => '- '.$this->locale['WLS_0123'].' -',
                        'options'     => [
                            '0' => $this->locale['WLS_0124'],
                            '2' => $this->locale['publish'],
                            '1' => $this->locale['unpublish']
                        ]
                    ]); ?>
                </div>
                <div class="display-inline-block">
                    <?php echo form_select('weblink_cat_visibility', '', $filter_values['weblink_cat_visibility'], [
                        'allowclear'  => TRUE,
                        'placeholder' => '-  '.$this->locale['WLS_0125'].' -',
                        'options'     => fusion_get_groups()
                    ]); ?>
                </div>
            </div>
            <?php echo closeform(); ?>
        </div>

        <?php echo openform('weblink_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action', '', '');
        $this->display_weblink_category($result);
        echo closeform();

        // Toogle Options
        add_to_jquery("
            // Toogle Options
            $('#toggle_options').bind('click', function(e) {
                e.preventDefault();
                $('#weblink_filter_options').slideToggle();
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
            $('#weblink_cat_status, #weblink_cat_visibility').bind('change', function(e){
                $(this).closest('form').submit();
            });
        ");

    }

    /**
     * Recursive function to display administration table
     *
     * @param     $data
     * @param int $id
     * @param int $level
     */
    private function display_weblink_category($data, $id = 0, $level = 0) {

        if (!$id) :
            ?>
            <div class="table-responsive"><table class="table table-hover">
            <thead>
            <tr>
                <th class="hidden-xs"></th>
                <th><?php echo $this->locale['WLS_0100'] ?></th>
                <th><?php echo $this->locale['WLS_0151'] ?></th>
                <th><?php echo $this->locale['WLS_0102'] ?></th>
                <th><?php echo $this->locale['WLS_0103'] ?></th>
                <th><?php echo $this->locale['WLS_0104'] ?></th>
            </tr>
            </thead>
            <tbody>
        <?php endif; ?>

        <?php if (!empty($data[$id])) : ?>
            <?php foreach ($data[$id] as $cat_id => $cdata) :
                $edit_link = clean_request("section=weblinks_category&ref=weblink_cat_form&action=edit&cat_id=".$cat_id, ['section', 'ref', 'action', 'cat_id'], FALSE);
                $delete_link = clean_request("section=weblinks_category&ref=weblink_cat_form&action=delete&cat_id=".$cat_id, ['section', 'ref', 'action', 'cat_id'], FALSE);
                ?>
                <tr data-id="<?php echo $cat_id; ?>" id="cat<?php echo $cat_id; ?>">
                    <td class="hidden-xs"><?php echo form_checkbox("weblink_cat_id[]", "", "", ["value" => $cat_id, "input_id" => "checkbox".$cat_id, "class" => "m-0"]);
                        add_to_jquery('$("#checkbox'.$cat_id.'").click(function() {
                        if ($(this).prop("checked")) {
                            $("#cat'.$cat_id.'").addClass("active");
                        } else {
                            $("#cat'.$cat_id.'").removeClass("active");
                        }
                    });');
                        ?></td>
                    <td><?php echo str_repeat("--", $level)." ".$cdata['weblink_cat_name']; ?></span></td>
                    <td><span class="badge"><?php echo format_word($cdata['weblink_count'], $this->locale['fmt_weblink']); ?></span></td>
                    <td><span class="badge"><?php echo($cdata['weblink_cat_status'] == 0 ? $this->locale['unpublish'] : $this->locale['publish']); ?></span></td>
                    <td><span class="badge"><?php echo getgroupname($cdata['weblink_cat_visibility']); ?></span></td>
                    <td>
                        <a href="<?php echo $edit_link; ?>" title="<?php echo $this->locale['edit']; ?>"><?php echo $this->locale['edit']; ?></a>&nbsp;|&nbsp;
                        <a href="<?php echo $delete_link; ?>" title="<?php echo $this->locale['delete']; ?>" onclick="return confirm('<?php echo $this->locale['WLS_0161']; ?>')"><?php echo $this->locale['delete']; ?></a>
                    </td>
                </tr>
                <?php
                if (isset($data[$cdata['weblink_cat_id']])) {
                    $this->display_weblink_category($data, $cdata['weblink_cat_id'], $level + 1);
                }
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center"><?php echo $this->locale['WLS_0162']; ?></td></tr>
        <?php endif; ?>

        <?php if (!$id) : ?>
            </tbody>
            </table></div>
        <?php endif;
    }
}
