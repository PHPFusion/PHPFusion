<?php
namespace PHPFusion\Quantum;
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumCategoryInterface.php
| Author: PHPFusion Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class QuantumCategoryInterface
 * Performing administration actions on user field category
 *
 * @package PHPFusion\Quantum
 */
class QuantumCategoryInterface {

    private $page_list;

    private $page;

    private $locale = [];

    private $aidlink;

    public function __construct($page, $page_list) {

        $this->page_list = $page_list;

        $this->page = $page;

        $this->locale = fusion_get_locale();

        $this->aidlink = fusion_get_aidlink();
    }

    public function invokeListener() {
        $this->deleteCategory();
    }

    /**
     * Delete category
     */
    private function deleteCategory() {

        $action = get('action');

        $cid = get('cat_id', FILTER_VALIDATE_INT);

        $data = [];

        if ($action == 'cat_delete' && $cid != 1 && QuantumHelper::validateFieldCat($cid)) {

            // do action of the interior form
            if (check_post('delete_cat')) {

                $target_database = '';
                $field_list = [];

                if (check_post('delete_subcat') or check_post('delete_field')) {

                    if (in_array($cid, $this->page_list)) {

                        // this is root.
                        $result = dbquery("SELECT field_cat_id, field_parent, field_cat_db FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cid", [':cid' => $cid]);

                    } else {

                        $result = dbquery("SELECT uf.field_cat_id, root.field_cat_db
                        FROM ".DB_USER_FIELD_CATS." uf
                        LEFT JOIN ".DB_USER_FIELD_CATS." root ON uf.field_parent = root.field_cat_id
                        WHERE uf.field_cat_id=:cid", [':cid' => $cid]);
                    }

                    if ($result == NULL) {
                        die($this->locale['fields_0653']);
                    }

                    if (dbrows($result)) {

                        $data += dbarray($result);

                        $target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;

                        $field_list = fieldgenerator($target_database);

                    }

                    //if ($this->debug) {
                    //    print_p($field_list);
                    //    print_p($target_database);
                    //}
                }

                if (check_post('delete_subcat')) {

                    // When deletion of a master page and involving all subcategories
                    //if ($this->debug) {
                    //    print_p($this->page[$_GET['cat_id']]);
                    //}

                    // execute removal on child fields and cats
                    foreach ($this->page[$cid] as $field_category) {

                        $result = dbquery("SELECT field_id, field_name FROM ".DB_USER_FIELDS." WHERE field_cat='".$field_category['field_cat_id']."'"); // find all child > 1

                        if (dbrows($result)) {

                            while ($data = dbarray($result)) {
                                // remove column from db , and fields
                                if (in_array($data['field_name'], $field_list)) { // verify table integrity

                                    //if ($this->debug) {
                                    //    print_p("DROP ".$data['field_name']." FROM ".$target_database);
                                    //    print_p("DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS);
                                    //} else {
                                    dbquery("DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'");

                                    if (!empty($target_database) && !empty($data['field_name'])) {
                                        \SqlHandler::drop_column($target_database, $data['field_name']);
                                    }
                                    //}

                                }

                                // remove category.
                                //if ($this->debug) {
                                //    print_p("DELETE ".$field_category['field_cat_id']." FROM ".DB_USER_FIELD_CATS);
                                //} else {
                                dbquery("DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$field_category['field_cat_id']."'");
                                //}
                            } // end while

                        }
                    }

                } else if (check_post('move_subcat')) {

                    // When deletion to move subcategory
                    foreach ($this->page[$cid] as $field_category) {

                        if ($new_parent = sanitizer('move_subcat', 0, 'move_subcat')) {
                            //if ($this->debug) {
                            //    print_p("MOVED ".$field_category['field_cat_id']." TO category ".$new_parent);
                            //    print_p("DELETE ".$_GET['cat_id']." FROM ".DB_USER_FIELD_CATS);
                            //} else {
                            dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_parent='".$new_parent."' WHERE field_cat_id='".$field_category['field_cat_id']."'");
                            //}
                        }

                    }

                } else if (check_post('delete_field') && $cid) {
                    // Delete fields
                    //if ($this->debug) {
                    //    print_p('Cat ID was not found. Please check again.');
                    //}

                    // Delete Fields - Bug with Isset errors
                    $result = dbquery("SELECT field_id, field_name FROM ".DB_USER_FIELDS." WHERE field_cat=:cid", [':cid' => $cid]);
                    if (dbrows($result)) {

                        while ($data = dbarray($result)) {

                            if (in_array($data['field_name'], $field_list)) { // verify table integrity

                                //if ($this->debug) {
                                //    print_p("DROP ".$data['field_name']." FROM ".$target_database);
                                //    print_p("DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS);
                                //} else {
                                $field_del_sql = "DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'";

                                if ($field_count = QuantumHelper::validateField($data['field_id'])) {
                                    dbquery($field_del_sql);
                                }
                                // drop a column
                                if (!empty($target_database)) {
                                    \SqlHandler::drop_column($target_database, $data['field_name']);
                                }
                            }
                        }

                        addnotice('success', $this->locale['field_0200']);
                        redirect(FUSION_SELF.$this->aidlink);
                    }
                } // category deletion path 2
                else if (!check_post('delete_field') && post('move_field', FILTER_VALIDATE_INT)) {

                    if ($rows = dbcount("(field_id)", DB_USER_FIELDS, "field_cat=:cid", [':cid' => $cid])) {

                        if ($new_parent = sanitizer('move_field', 0, 'move_field')) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_cat=".$new_parent." WHERE field_cat=:cid", [':cid' => $cid]);
                        }
                    }
                }

                // Delete the current category
                dbquery("DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cid", [':cid' => $cid]);

                addnotice('success', $this->locale['field_0200']);
                redirect(FUSION_SELF.$this->aidlink);

            } else {

                // show interior form
                $field_list = [];
                $form_action = FUSION_SELF.$this->aidlink."&amp;action=cat_delete&amp;cat_id=".$cid;
                $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cid OR field_cat_id='".get_hkey(DB_USER_FIELD_CATS, "field_cat_id", "field_parent", $cid)."'", [':cid' => $cid]);

                if (dbrows($result)) {

                    $data += dbarray($result);

                    // get field list - populate child fields of a category.
                    $result = dbquery("SELECT field_id, field_name, field_cat FROM ".DB_USER_FIELDS." WHERE field_cat=:cid", [':cid' => $cid]);
                    if (dbrows($result)) {
                        // get field list.
                        while ($data = dbarray($result)) {
                            $field_list[$data['field_cat']][$data['field_id']] = $data['field_name'];
                        }
                    }

                    if ((isset($data['field_parent']) && isset($this->page[$data['field_parent']])) or (!empty($field_list) && $field_list[$cid] > 0)) {

                        ob_start();
                        echo openmodal("delete", $this->locale['fields_0313'], [
                            'class'  => 'modal-lg modal-center',
                            'static' => TRUE
                        ]);
                        echo openform('delete_cat_form', 'POST', $form_action);
                        if (isset($this->page[$cid])) {
                            echo "<div class='row'>";
                            echo "<div class='col-xs-12 col-sm-6'><span class='strong'>".sprintf($this->locale['fields_0600'], count($this->page[$_GET['cat_id']]))."</span><br/>";
                            echo "<div class='alert alert-info m-t-10'>";
                            echo "<ol style='list-style:inherit !important; margin-bottom:0;'>";
                            foreach ($this->page[$cid] as $field_category) {
                                echo "<li style='list-style-type:decimal;'>".QuantumHelper::parseLabel($field_category['field_cat_name'])."</li>";
                            }
                            echo "</ol>";
                            echo "</div>";
                            echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>";

                            $page_list = $this->page_list;
                            unset($page_list[$cid]);

                            if (count($page_list) > 0) {
                                echo form_select('move_subcat', $this->locale['fields_0314'], '', ["options" => $page_list]);
                            }
                            echo form_checkbox('delete_subcat', $this->locale['fields_0315'], count($page_list) < 1);
                            echo "</div></div>";
                        }

                        if (isset($field_list[$cid])) {
                            echo "<div class='row'>";
                            echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'><span class='strong'>".sprintf($this->locale['fields_0601'], count($field_list[$cid]))."</span><br/>";
                            echo "<div class='well strong m-t-10'>";
                            foreach ($field_list[$cid] as $field) {
                                echo "- ".$field."<br/>";
                            }
                            echo "</div>";
                            echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>";

                            $exclude_list[] = $cid;
                            foreach ($this->page_list as $page_id => $page_name) {
                                $exclude_list[] = $page_id;
                            }

                            echo form_select('move_field', $this->locale['fields_0316'], '', [
                                'no_root'      => 1,
                                'disable_opts' => $exclude_list,
                                'db'           => DB_USER_FIELD_CATS,
                                'title_col'    => 'field_cat_name',
                                'id_col'       => 'field_cat_id',
                                'cat_col'      => 'field_parent'
                            ]);

                            echo form_checkbox('delete_field', $this->locale['fields_0317'], '');
                            echo "</div></div>";
                        }
                        echo form_button('delete_cat', $this->locale['fields_0313'], $this->locale['fields_0313'],
                            ['class' => 'btn-danger btn-sm']);
                        echo form_button('cancel', $this->locale['cancel'], $this->locale['cancel'],
                            ['class' => 'btn-default m-l-10 btn-sm']);
                        echo closeform();
                        echo closemodal();
                        add_to_footer(ob_get_contents());
                        ob_end_clean();
                    }
                } else {
                    //if ($this->debug) {
                    //    addnotice('info', 'Cat ID was not found. Please check again.<br/>Category ID was not found. Please check again.');
                    //} else {
                    redirect(FUSION_SELF.$this->aidlink);
                    //}
                }
            }
        }
    }

    /**
     * Save category
     */
    public function saveCategory() {

        if (check_post("save_cat")) {

            $data = [
                'field_cat_id'    => sanitizer("field_cat_id", 0, "field_cat_id"),
                'field_cat_name'  => sanitizer(["field_cat_name"], "", "field_cat_name", TRUE),
                'field_parent'    => sanitizer('field_parent', 0, 'field_parent'),
                'field_cat_order' => sanitizer('field_cat_order', 0, 'field_cat_order'),
                'field_cat_db'    => '',
                'field_cat_index' => '',
                'field_cat_class' => '',
            ];
            // only if root then need to sanitize
            $old_data = [
                "field_cat_db" => "users"
            ];
            $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$data['field_cat_id']."'");
            if (dbrows($result)) {
                $old_data = dbarray($result);
            }

            if ($data['field_parent'] == 0) {

                $data['field_cat_db'] = sanitizer('field_cat_db', 'users', 'field_cat_db');
                $data['field_cat_index'] = sanitizer('field_cat_index', '', 'field_cat_index');
                $data['field_cat_class'] = sanitizer('field_cat_class', '', 'field_cat_class');

                // Improvised to code jquery chained selector
                if (!column_exists($data['field_cat_db'], $data['field_cat_index'])) {
                    fusion_stop($this->locale['fields_0671']);
                }
            }

            if ($data['field_cat_order'] === 0) {
                $data['field_cat_order'] = dbresult(dbquery("SELECT MAX(field_cat_order) FROM ".DB_USER_FIELD_CATS." WHERE field_parent='".$data['field_parent']."'"), 0) + 1;
            }

            if (QuantumHelper::validateFieldCat($data['field_cat_id'])) {
                // Update
                dbquery_order(
                    DB_USER_FIELD_CATS,
                    $data['field_cat_order'],
                    "field_cat_order",
                    $data['field_cat_id'],
                    'field_cat_id',
                    $data['field_parent'],
                    'field_parent',
                    FALSE,
                    FALSE
                );

                //if (!$this->debug) {
                if (fusion_safe()) {
                    if (!empty($old_data['field_cat_db']) or $old_data['field_cat_db'] !== "users") {

                        if (!empty($old_data['field_cat_db']) && !empty($old_data['field_cat_index'])) {
                            // CONDITION: HAVE A PREVIOUS TABLE SET
                            if (!empty($data['field_cat_db'])) {
                                // new demands a table insertion, checks if same or not. if different.
                                if ($data['field_cat_db'] !== $old_data['field_cat_db']) {
                                    // But the current table is different from the previous one
                                    // - build the new one, move the column, drop the old one.
                                    \SqlHandler::build_table($data['field_cat_db'], $data['field_cat_index']);
                                    \SqlHandler::transfer_table($old_data['field_cat_db'], $data['field_cat_db']);
                                    \SqlHandler::drop_table($old_data['field_cat_db']);

                                } else {

                                    if ($old_data['field_cat_index'] !== $data['field_cat_index']) {
                                        \SqlHandler::rename_column($data['field_cat_db'], $old_data['field_cat_index'], $data['field_cat_index'], "MEDIUMINT(8) NOT NULL DEFAULT '0'");
                                    }
                                }

                            } else if (empty($data['field_cat_db'])) {
                                \SqlHandler::drop_table($data['field_cat_db']);
                            }

                        } else if (!empty($data['field_cat_index']) && !empty($data['field_cat_db'])) {

                            \SqlHandler::build_table($data['field_cat_db'], $data['field_cat_index']);

                        }

                        dbquery_insert(DB_USER_FIELD_CATS, $data, 'update');

                        addnotice('success', $this->locale['field_0207']);
                    }
                    redirect(FUSION_SELF.$this->aidlink);
                }
                //else {
                //    print_p('Update Mode');
                //    print_p($data);
                //}

            } else {
                // Add new entry
                dbquery_order(
                    DB_USER_FIELD_CATS,
                    $data['field_cat_order'],
                    'field_cat_order',
                    $data['field_cat_id'],
                    'field_cat_id',
                    $data['field_parent'],
                    'field_parent',
                    TRUE,
                    'field_cat_name',
                    'save'
                );

                if (fusion_safe()) {

                    if (!empty($data['field_cat_index']) && !empty($data['field_cat_db']) && $data['field_cat_db'] !== 'users') {
                        \SqlHandler::build_table($data['field_cat_db'], $data['field_cat_index']);
                    }

                    dbquery_insert(DB_USER_FIELD_CATS, $data, 'save');
                    addnotice('success', $this->locale['field_0208']);
                    redirect(FUSION_SELF.$this->aidlink);
                }
                //else {
                //if ($this->debug) {
                //    print_p('Save Mode');
                //    print_p($data);
                //}
                //}
            }
        }
    }

}
