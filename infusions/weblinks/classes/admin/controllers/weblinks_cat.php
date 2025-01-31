<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: weblinks_cat.php
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
        pageaccess("W");
        $this->locale = self::getWeblinkAdminLocale();

        // Cancel Form
        if (check_get('cancel')) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&section=weblinks_category");
        }

        if (check_get('ref') && get('ref') == "weblink_cat_form") {
            $this->displayWeblinksCatForm();
        } else {
            $this->displayWeblinksCatListing();
        }
    }

    /**
     * Displays weblinks Category Form
     */
    private function displayWeblinksCatForm() {
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
        $action = get('action', FILTER_DEFAULT);
        $cat_id = get('cat_id', FILTER_VALIDATE_INT);
        if (check_post('save_cat') || check_post('save_cat_and_close')) {
            // Check Fields
            $inputArray = [
                'weblink_cat_id'          => sanitizer('weblink_cat_id', 0, 'weblink_cat_id'),
                'weblink_cat_name'        => sanitizer('weblink_cat_name', '', 'weblink_cat_name'),
                'weblink_cat_description' => sanitizer('weblink_cat_description', '', 'weblink_cat_description'),
                'weblink_cat_parent'      => sanitizer('weblink_cat_parent', 0, 'weblink_cat_parent'),
                'weblink_cat_visibility'  => sanitizer(['weblink_cat_visibility'], 0, 'weblink_cat_visibility'),
                'weblink_cat_status'      => sanitizer('weblink_cat_status', 0, 'weblink_cat_status'),
                'weblink_cat_language'    => sanitizer(['weblink_cat_language'], LANGUAGE, 'weblink_cat_language')
            ];
            // Check Where Condition
            $categoryNameCheck = [
                'when_updating' => "weblink_cat_name='".$inputArray['weblink_cat_name']."' and weblink_cat_id !='".$inputArray['weblink_cat_id']."' ".(multilang_table("WL") ? "and ".in_group('weblink_cat_language', LANGUAGE) : ""),
                'when_saving'   => "weblink_cat_name='".$inputArray['weblink_cat_name']."' ".(multilang_table("WL") ? "and ".in_group('weblink_cat_language', LANGUAGE) : ""),
            ];

            // Save
            if (fusion_safe()) {
                // Update
                if (dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_id=:catid", [':catid' => $inputArray['weblink_cat_id']])) {
                    if (!dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, $categoryNameCheck['when_updating'])) {
                        dbquery_insert(DB_WEBLINK_CATS, $inputArray, 'update');
                        addnotice('success', $this->locale['WLS_0041']);
                    } else {
                        fusion_stop();
                        addnotice('danger', $this->locale['WLS_0321']);
                    }
                    // Insert
                } else {
                    if (!dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, $categoryNameCheck['when_saving'])) {
                        $inputArray['weblink_cat_id'] = dbquery_insert(DB_WEBLINK_CATS, $inputArray, 'save');
                        addnotice('success', $this->locale['WLS_0040']);
                    } else {
                        fusion_stop();
                        addnotice('danger', $this->locale['WLS_0321']);
                    }
                }
                if (fusion_safe()) {
                    if (check_post('save_cat_and_close')) {
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
                addnotice("success", $this->locale['WLS_0042']);
            } else {
                addnotice("warning", $this->locale['WLS_0043']);
                addnotice("warning", $this->locale['WLS_0044']);
            }
            redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
        }

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
            'bbcode'    => TRUE,
            'form_name' => 'catform',
            'preview'   => TRUE
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
                'multiple'    => TRUE
            ]);
        } else {
            echo form_hidden('weblink_cat_language', '', $data['weblink_cat_language']);
        }
        echo form_select('weblink_cat_visibility[]', $this->locale['WLS_0103'], $data['weblink_cat_visibility'], [
            'inner_width' => '100%',
            'options'     => fusion_get_groups(),
            'placeholder' => $this->locale['choose'],
            'multiple'    => TRUE
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
    }

    /**
     * Displays weblinks Category Listing
     */
    private function displayWeblinksCatListing() {
        // Run functions
        $allowed_actions = array_flip(['publish', 'unpublish', 'delete']);
        $table_action = post('table_action');
        // Table Actions
        if (check_post('table_action') && isset($allowed_actions[$table_action])) {

            $input = check_post('weblink_cat_id') ? form_sanitizer($_POST['weblink_cat_id'], 0, "weblink_cat_id") : "";
            if (!empty($input)) {
                $input = ($input ? explode(",", $input) : []);
                foreach ($input as $weblink_cat_id) {
                    // check input table
                    if (dbcount("('weblink_cat_id')", DB_WEBLINK_CATS,
                            "weblink_cat_id=:catid", [':catid' => (int)$weblink_cat_id]) && fusion_safe()
                    ) {
                        switch ($table_action) {
                            case "publish":
                                dbquery("UPDATE ".DB_WEBLINK_CATS." SET weblink_cat_status=:status WHERE weblink_cat_id=:catid", [':status' => '1', ':catid' => (int)$weblink_cat_id]);
                                addnotice('success', $this->locale['WLS_0049']);
                                break;
                            case "unpublish":
                                dbquery("UPDATE ".DB_WEBLINK_CATS." SET weblink_cat_status=:status WHERE weblink_cat_id=:catid", [':status' => '0', ':catid' => (int)$weblink_cat_id]);
                                addnotice('success', $this->locale['WLS_0050']);
                                break;
                            case "delete":
                                if (!dbcount("(weblink_id)", DB_WEBLINKS, "weblink_cat=:catid", [':catid' => (int)$weblink_cat_id]) && !dbcount("(weblink_cat_id)", DB_WEBLINK_CATS, "weblink_cat_parent=:catparent", [':catparent' => (int)$weblink_cat_id])) {
                                    dbquery("DELETE FROM  ".DB_WEBLINK_CATS." WHERE weblink_cat_id=:catid", [':catid' => (int)$weblink_cat_id]);
                                    addnotice('success', $this->locale['WLS_0042']);
                                } else {
                                    addnotice('warning', $this->locale['WLS_0046']);
                                    addnotice('warning', $this->locale['WLS_0044']);
                                }
                                break;
                            default:
                                redirect(FUSION_REQUEST);
                        }
                    }
                }
            } else {
                addnotice('warning', $this->locale['WLS_0048']);
            }
            redirect(FUSION_REQUEST);
        }

        // Clear
        if (check_post('weblink_clear')) {
            redirect(FUSION_SELF.fusion_get_aidlink()."&amp;section=weblinks_category");
        }

        // Search
        $sql_condition = multilang_table("WL") ? in_group('ac.weblink_cat_language', LANGUAGE) : "";
        $search_string = [];
        if (check_post('p-submit-weblink_cat_name')) {
            $search_string['weblink_cat_name'] = [
                'input' => sanitizer('weblink_cat_name', '', 'weblink_cat_name'), 'operator' => "LIKE", 'option' => "AND"
            ];
        }
        $weblink_cat_status = post('weblink_cat_status');
        if (!empty($weblink_cat_status)) {
            $search_string['weblink_cat_status'] = [
                'input' => sanitizer('weblink_cat_status', 0, 'weblink_cat_status') - 1, 'operator' => "="
            ];
        }
        $weblink_cat_visibility = post('weblink_cat_visibility');
        if (!empty($weblink_cat_visibility)) {
            $search_string['weblink_cat_visibility'] = [
                'input' => sanitizer('weblink_cat_visibility', '', 'weblink_cat_visibility'), 'operator' => "="
            ];
        }

        if (!empty($search_string)) {
            foreach ($search_string as $key => $values) {
                if ($sql_condition) {
                    $sql_condition .= "AND ";
                    $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "' ");
                }
            }
        }

        // Query
        $result = dbquery_tree_full(DB_WEBLINK_CATS, "weblink_cat_id", "weblink_cat_parent", "",
            "SELECT ac.*, COUNT(a.weblink_id) AS weblink_count
            FROM ".DB_WEBLINK_CATS." ac
            LEFT JOIN ".DB_WEBLINKS." AS a ON a.weblink_cat=ac.weblink_cat_id
            ".($sql_condition ? " WHERE ".$sql_condition : "")."
            GROUP BY ac.weblink_cat_id
            ORDER BY ac.weblink_cat_parent ASC, ac.weblink_cat_id ASC"
        );

        // Filters
        $filter_values = [
            'weblink_cat_name'       => !empty(post('weblink_cat_name')) ? sanitizer('weblink_cat_name', '', 'weblink_cat_name') : '',
            'weblink_cat_status'     => !empty($weblink_cat_status) ? sanitizer('weblink_cat_status', 0, 'weblink_cat_status') : '',
            'weblink_cat_visibility' => !empty($weblink_cat_visibility) ? sanitizer('weblink_cat_visibility', 0, 'weblink_cat_visibility') : ''
        ];

        $filter_empty = TRUE;
        foreach ($filter_values as $val) {
            if ($val) {
                $filter_empty = FALSE;
            }
        }
        ?>

        <!-- Display Search, Filters and Actions -->
        <?php echo openform("weblink_filter", "post", FUSION_REQUEST); ?>
        <div class="clearfix">

            <!-- Actions -->
            <div class="pull-right">
                <a class="btn btn-success btn-sm" href="<?php echo clean_request("ref=weblink_cat_form", ["ref"], FALSE); ?>"><i class="fa fa-fw fa-plus"></i> <?php echo $this->locale['WLS_0005']; ?>
                </a>
                <button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin('publish', '#table_action', '#weblink_table');">
                    <i class="fa fa-fw fa-check"></i> <?php echo $this->locale['publish']; ?></button>
                <button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin('unpublish', '#table_action', '#weblink_table');">
                    <i class="fa fa-fw fa-ban"></i> <?php echo $this->locale['unpublish']; ?></button>
                <button type="button" class="hidden-xs btn btn-danger btn-sm m-l-5" onclick="run_admin('delete', '#table_action', '#weblink_table');">
                    <i class="fa fa-fw fa-trash-o"></i> <?php echo $this->locale['delete']; ?></button>
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
        <?php echo closeform();

        echo openform('weblink_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action');
        $this->displayWeblinkCategory($result);
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
    private function displayWeblinkCategory($data, $id = 0, $level = 0) {

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
                    <td>
                        <span class="badge"><?php echo format_word($cdata['weblink_count'], $this->locale['fmt_weblink']); ?></span>
                    </td>
                    <td>
                        <span class="badge"><?php echo($cdata['weblink_cat_status'] == 0 ? $this->locale['unpublish'] : $this->locale['publish']); ?></span>
                    </td>
                    <td><span class="badge"><?php echo getgroupname($cdata['weblink_cat_visibility']); ?></span></td>
                    <td>
                        <a href="<?php echo $edit_link; ?>" title="<?php echo $this->locale['edit']; ?>"><?php echo $this->locale['edit']; ?></a>&nbsp;|&nbsp;
                        <a href="<?php echo $delete_link; ?>" title="<?php echo $this->locale['delete']; ?>" onclick="return confirm('<?php echo $this->locale['WLS_0161']; ?>')"><?php echo $this->locale['delete']; ?></a>
                    </td>
                </tr>
                <?php
                if (isset($data[$cdata['weblink_cat_id']])) {
                    $this->displayWeblinkCategory($data, $cdata['weblink_cat_id'], $level + 1);
                }
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center"><?php echo $this->locale['WLS_0162']; ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!$id) : ?>
            </tbody>
            </table></div>
        <?php endif;
    }
}
