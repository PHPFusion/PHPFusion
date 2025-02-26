<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumFieldInterface.php
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
namespace PHPFusion\Quantum;

use PHPFusion\QuantumFields;

/**
 * Class QuantumFieldInterface
 * Performing administration actions on user fields
 *
 * @package PHPFusion\Quantum
 */
class QuantumFieldInterface {

    /**
     * @var
     */
    private $page_list;

    /**
     * @var array
     */
    private $page;
    /**
     * @var string
     */
    private $aidlink;

    /**
     * @var array
     */
    private $locale = [];

    public function __construct($page, $page_list) {

        $this->aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();

        /**
         * @todo - implementation of substituting query with $page and $page_list will increase system response time.
         */
        $this->page = $page;
        $this->page_list = $page_list;
    }

    /**
     * Load the listener scripts
     */
    public function invokeListener() {

        if (check_post('cancel')) {
            redirect(FUSION_SELF.$this->aidlink);
        }

        $this->moveFields();

        $this->deleteFields();

    }

    /**
     * Move fields, and category order via get requests - up and down
     */
    private function moveFields() {

        $action = get('action');
        $order = get('order', FILTER_VALIDATE_INT);
        $pid = get('parent_id', FILTER_VALIDATE_INT);
        $cid = get('cat_id', FILTER_VALIDATE_INT);
        $id = get('field_id', FILTER_VALIDATE_INT);

        if ($action && $order && $pid) {
            // moving category
            if ($cid && ($action == 'cmu' or $action == 'cmd')) {

                $data = [];
                $result = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:pid AND field_cat_order=:ord", [
                    ':pid' => (int)$pid,
                    ':ord' => (int)$order
                ]); // more than 1.

                if (dbrows($result)) {
                    $data = dbarray($result);
                }

                if ($action == 'cmu') { // category move up.
                    if (!empty($data['field_cat_id'])) {
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$data['field_cat_id']."'");
                    }

                    // sync the whole order
                    dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id=:cid", [':cid' => (int)$cid]);

                } else if ($action == 'cmd') {
                    if (!empty($data['field_cat_id'])) {
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$data['field_cat_id']."'");
                    }

                    dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id=:cid", [':cid' => (int)$cid]);
                }

                $res = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:id ORDER BY field_cat_order", [':id' => (int)$pid]);
                if (dbrows($res)) {
                    $i = 1;
                    while ($rows = dbarray($res)) {
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=:order WHERE field_cat_id=:id", [
                            ':order' => $i,
                            ':id'    => (int)$rows['field_cat_id']
                        ]);
                        $i++;
                    }
                }
                redirect(FUSION_SELF.$this->aidlink);
                //}
            } // moving fields
            else if ($id && ($action == 'fmu' or $action == 'fmd')) {
                print_p('yes');
                $data = [];
                $result = dbquery("SELECT field_id FROM ".DB_USER_FIELDS." WHERE field_cat=:pid AND field_order=:ord", [
                    ':pid' => $pid,
                    ':ord' => $order
                ]);
                if (dbrows($result)) {
                    $data = dbarray($result);
                }

                if ($action == 'fmu') { // field move up.

                    if (!empty($data['field_id'])) {
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id='".$data['field_id']."'");
                    }
                    dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_id=:id", [':id' => (int)$id]);

                    //    print_p('Move Field ID '.$_GET['field_id'].' Up a slot and Field ID '.$data['field_id'].' down a slot.');

                } else if ($action == 'fmd') {
                    //if (!$this->debug) {
                    if (!empty($data['field_id'])) {
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_id=:id", [':id' => (int)$data['field_id']]);
                    }
                    dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id=:id", [':id' => (int)$id]);

                    //    print_p('Move Field ID '.$_GET['field_id'].' Down a slot and Field ID '.$data['field_id'].' up a slot.');
                }

                $res = dbquery("SELECT field_id, field_order FROM ".DB_USER_FIELDS." WHERE field_cat=:parent_id ORDER BY field_order", [':parent_id' => (int)$pid]);

                if (dbrows($res)) {
                    $i = 1;
                    while ($rows = dbarray($res)) {

                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=:order WHERE field_id=:id", [
                            ':order' => $i,
                            ':id'    => (int)$rows['field_id']
                        ]);
                        $i++;
                    }
                }

                redirect(FUSION_SELF.$this->aidlink);
            }
        }
    }

    /**
     * Delete fields
     */
    private function deleteFields() {

        $aidlink = fusion_get_aidlink();
        $id = get('field_id', FILTER_VALIDATE_INT);
        $action = get('action');

        if ($action == 'field_delete' && $id) {

            $result = dbquery("SELECT field.field_id, field.field_cat, field.field_order, field.field_name, u.field_cat_id, u.field_parent, root.field_cat_db
            FROM ".DB_USER_FIELDS." field
            LEFT JOIN ".DB_USER_FIELD_CATS." u ON (field.field_cat=u.field_cat_id)
            LEFT JOIN ".DB_USER_FIELD_CATS." root ON (u.field_parent = root.field_cat_id)
            WHERE field_id=:id", [':id' => $id]);

            $message = $this->locale['field_0202'];
            $msg_status = 'warning';

            if (dbrows($result)) {

                $data = dbarray($result);
                $target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;
                $field_list = fieldgenerator($target_database);


                if (in_array($data['field_name'], $field_list)) {
                    // drop database
                    if (db_exists($target_database)) {

                        $msg_status = 'success';
                        $message = $this->locale['field_0201'];

                        //dbquery("ALTER TABLE ".$target_database." DROP ".$data['field_name']);
                        \SqlHandler::drop_column($target_database, $data['field_name']);
                        // reorder the rest of the same cat minus 1
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                    }
                }

                dbquery("DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'");
            }

            add_notice($msg_status, $message);
            redirect(FUSION_SELF.$aidlink);
        }
    }

    /**
     * Field creation
     *
     * @param        $data
     * @param        $dbinfo
     * @param string $type
     */
    public function createFields($data, $dbinfo, string $type = 'dynamics') {

        // Build a field Attr
        $field_attr = '';
        if ($type == 'dynamics') {
            $field_attr = QuantumHelper::dynamicsFieldInfo($data['field_type'], $data['field_default']);
        } else if ($type == 'module') {
            $field_attr = $dbinfo;
        }

        $max_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat='".(int)$data['field_cat']."'"), 0) + 1;
        if ($data['field_order'] == 0 or $data['field_order'] > $max_order) {
            $data['field_order'] = $max_order;
        }

        if (QuantumHelper::validateField($data['field_id'])) {

            //if ($this->debug) {
            //    print_p('Update Mode');
            //}
            // update
            // Alter DB_USER_FIELDS table - change and modify column.
            $field_query = "SELECT uf.*, cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                FROM ".DB_USER_FIELDS." uf
                LEFT JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = uf.field_cat)
                LEFT JOIN ".DB_USER_FIELD_CATS." root ON (cat.field_parent = root.field_cat_id)
                WHERE uf.field_id=:field_id";
            $field_param = [':field_id' => $data['field_id']];

            $old_record = dbquery($field_query, $field_param); // search old database.

            if (dbrows($old_record)) { // got old field cat

                $oldRows = dbarray($old_record);

                $old_table = $oldRows['field_cat_db'] ? DB_PREFIX.$oldRows['field_cat_db'] : DB_USERS; // this was old database

                $old_table_columns = fieldgenerator($old_table);

                // Get current updated field_cat - to compare new cat_db and old cat_db

                $new_result = dbquery("
                SELECT cat.field_cat_id, cat.field_cat_name, cat.field_parent, cat.field_cat_order,
                root.field_cat_db, root.field_cat_index
                FROM ".DB_USER_FIELD_CATS." cat
                LEFT JOIN ".DB_USER_FIELD_CATS." root ON cat.field_parent = root.field_cat_id
                WHERE cat.field_cat_id='".intval($data['field_cat'])."'
                ");
                $new_table = DB_USERS;
                $newRows = [];
                if (dbrows($new_result)) {
                    $newRows = dbarray($new_result);
                    $new_table = $newRows['field_cat_db'] ? DB_PREFIX.$newRows['field_cat_db'] : DB_USERS;
                }

                //if ($this->debug) {
                //     Old Table Information
                //print_p('Old table information - ');
                //print_p($oldRows);
                // New Table Information
                //print_p('New table information - ');
                //print_p($newRows);
                //print_p($data['field_cat']);
                //print_p($oldRows['field_cat']);
                //}

                if ($data['field_cat'] != $oldRows['field_cat']) { // old and new mismatch - move to another category and possibly a new table.
                    // Fork #1 - Update on new table
                    //if ($this->debug) {
                    //    print_p('Fork No.1 - Update Field on a different table');
                    //}
                    /**
                     * Drop column on old table and Create column on new table
                     *
                     * @todo: Improvements: need to move the whole column along with data instead of just dropping and creating new
                     */
                    if ($new_table != $old_table) {

                        $new_table_columns = fieldgenerator($new_table);
                        //if (!$this->debug) {
                        if (!in_array($data['field_name'], $new_table_columns)) {
                            // this is new database check, if not exist, then add the column
                            //self::add_column($new_table, $data['field_name'], $field_attr);
                            \SqlHandler::move_column($old_table, $new_table, $data['field_name']);
                            \SqlHandler::drop_column($old_table, $oldRows['field_name']);

                            if (fusion_safe()) {
                                // sort the fields. if 2, greater than 2 all +1 on the new category
                                dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                                // since change table. fix all which is greater than link order.
                                dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                            }
                        } else {
                            fusion_stop('Column conflict. There are columns on '.$old_table.' existed in '.$new_table);
                        }
                        //} else {
                        //DEBUG MODE
                        //if (!in_array($data['field_name'], $new_table_columns)) {
                        //    print_p("Move ".$data['field_name']." from ".$old_table." to ".$new_table);
                        //    print_p("Dropping column ".$oldRows['field_name']." on ".$old_table);
                        //    print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        //since change table.fix all which is greater than link order.
                        //print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                        //} else {
                        //    print_p('Column conflict. There are columns on '.$old_table.' existed in '.$new_table);
                        //}
                        //}
                    } else {
                        if (fusion_safe()) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                            if (dbcount("(field_id)", DB_USER_FIELDS)) {
                                dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                            }
                        }
                    }
                } else {
                    // same table.
                    // check if same title.
                    // if not same, change column name.
                    //if ($this->debug) {
                    //    print_p('Fork No.2 - Update Field on the same table');
                    //}
                    if ($data['field_name'] != $oldRows['field_name']) {
                        // not same as old record on dbcolumn
                        // Check for possible duplicates in the new field name
                        if (!in_array($data['field_name'], $old_table_columns)) {
                            //if (!$this->debug) {
                            \SqlHandler::rename_column($old_table, $oldRows['field_name'], $data['field_name'], $field_attr);
                            //} else {
                            //    print_p('Renaming column '.$oldRows['field_name'].' on '.$old_table.' to '.$data['field_name'].' with attributes of '.$field_attr);
                            //}
                        } else {
                            fusion_stop(sprintf($this->locale['fields_0104'], "($new_table)"));
                        }
                    }
                    //if (!$this->debug) {
                    if (fusion_safe()) {
                        // make ordering of the same table.
                        if ($data['field_order'] > $oldRows['field_order']) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > ".$oldRows['field_order']." AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        } else if ($data['field_order'] < $oldRows['field_order']) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order < ".$oldRows['field_order']." AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        }
                    }
                    //} else {
                    //    print_p("Old field order is ".$oldRows['field_order']);
                    //    print_p("New field order is ".$data['field_order']);
                    //    if ($data['field_order'] > $oldRows['field_order']) {
                    //        print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > '".$oldRows['field_order']."' AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                    //    } else {
                    //        print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order < '".$oldRows['field_order']."' AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                    //    }
                    //}
                }
                //if (!$this->debug) {
                if (fusion_safe()) {
                    dbquery_insert(DB_USER_FIELDS, $data, 'update');
                    addnotice('success', $this->locale['field_0203']);
                    redirect(FUSION_SELF.$this->aidlink);
                }
                //} else {
                //    print_p($data);
                //}
            } else {
                fusion_stop($this->locale['fields_0105']);
            }

        } else {
            //if ($this->debug) {
            //    print_p('Save Mode');
            //}
            // Alter DB_USER_FIELDS table - add column.
            $res = dbquery("SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                                FROM ".DB_USER_FIELD_CATS." cat
                                LEFT JOIN ".DB_USER_FIELD_CATS." root ON (cat.field_parent = root.field_cat_id)
                                WHERE cat.field_cat_id='".$data['field_cat']."'");
            if (dbrows($res)) {

                $cat_data = dbarray($res);
                $new_table = $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS;
                $field_arrays = fieldgenerator($new_table);

                if (!in_array($data['field_name'], $field_arrays)) { // safe to execute alter.
                    //if (!$this->debug && !empty($data['field_name'])) {
                    \SqlHandler::add_column($new_table, $data['field_name'], $field_attr);
                    //} else {
                    //    if ($this->debug) {
                    //        print_p("ALTER TABLE ".$new_table." ADD ".$data['field_name']." ".$field_attr);
                    //    }
                    //}
                } else {
                    fusion_stop($this->locale['fields_0106']);
                }
                // ordering
                //if (!$this->debug) {
                if (fusion_safe()) {
                    dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                    dbquery_insert(DB_USER_FIELDS, $data, 'save');
                    addnotice('success', $this->locale['field_0204']);
                    redirect(FUSION_SELF.$this->aidlink);
                }
                //} else {
                //    print_p($data);
                //}
            } else {
                fusion_stop($this->locale['fields_0107']);
            }
        }
    }

}
