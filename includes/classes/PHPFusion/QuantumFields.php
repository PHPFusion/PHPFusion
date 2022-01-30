<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumFields.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Chris Smith <code+php@chris.cs278.org>,
| Co-Author: Frank Bültge <frank@bueltge.de>
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

use FusionTabs;
use SqlHandler;

/**
 * Class QuantumFields
 *
 * @package PHPFusion
 */
class QuantumFields extends SqlHandler {
    /**
     * Set the Quantum System Fields Page Title
     */
    protected $system_title = '';
    /**
     * Set the admin rights to Quantum Fields Admin
     *
     * @var string
     */
    protected $admin_rights = '';
    /**
     * Set the Database to install field structure records
     * Refer to v7.x User Fields Structrue
     *
     * @var string - category_db = DB_USER_FIELDS_CAT
     * @var string - field_db = DB_USER_FIELDS
     */
    protected $category_db = '';
    protected $field_db = '';
    /**
     * Set system API folder paths
     * Refer to v7.x User Fields API
     *
     * @var string - plugin_locale_folder (LOCALE.LOCALESET."user_fields/)
     * @var string - plugin_folder (INCLUDES."user_fields/")
     */
    protected $plugin_folder = NULL;
    protected $plugin_locale_folder = NULL;
    /**
     * Set as `display` to show array values output
     * Two methods - input or display
     *
     * @var string
     */
    protected $method = 'input';
    /**
     * feed $userData or $data here to append display_fields() values
     * use the setter function setCallbackData()
     *
     * @var array
     */
    protected $callback_data = [];
    // callback on the structure - use getters
    protected $fields = []; // maybe can mix with enabled_fields.
    protected $cat_list = [];
    // debug mode
    protected $debug = FALSE;
    protected $module_debug = FALSE;
    // System Internals
    private $input_page = 1;
    private $locale;
    private $page_list = [];
    private $page = [];
    private $enabled_fields = [];
    private $get_available_modules = [];
    private $available_field_info = [];
    private $user_field_dbinfo = '';
    /** Setters */
    private $field_data = [
        'add_module'         => '',
        'field_type'         => '',
        'field_id'           => 0,
        'field_title'        => '',
        'field_name'         => '',
        'field_cat'          => 0,
        'field_options'      => '',
        'field_default'      => '',
        'field_error'        => '',
        'field_registration' => 0,
        'field_log'          => 0,
        'field_required'     => 0,
        'field_order'        => 0,
    ];
    private $field_cat_data = [
        'field_cat_id'    => 0,
        'field_cat_name'  => '',
        'field_parent'    => 0,
        'field_cat_order' => 0,
        'field_cat_db'    => '',
        'field_cat_index' => '',
        'field_cat_class' => '',
    ];
    private $output_fields = [];

    ### Setters ###
    private $field_cat_index = [];

    public function __construct() {
        $this->locale = fusion_get_locale('', LOCALE.LOCALESET."admin/fields.php");
    }

    /**
     * @param array  $data
     * @param string $input_name
     *
     * @return array|bool|mixed|string|null
     */
    public static function fusionGetLocale($data, $input_name) {
        $language_opts = fusion_get_enabled_languages();

        if (isset($_POST[$input_name])) {
            return self::serializeFields($input_name);
        } else {
            if (isset($data[$input_name])) {
                if (self::isSerialized($data[$input_name])) {
                    return unserialize(stripslashes($data[$input_name]));
                } else {
                    $value = [];
                    foreach ($language_opts as $lang) {
                        $value[$lang] = $data[$input_name];
                    }

                    return $value;
                }
            } else {
                return NULL;
            }
        }
    }

    /**
     * Short serialization function.
     *
     * @param string $input_name
     *
     * @return bool|string
     */
    public static function serializeFields($input_name) {
        if (isset($_POST[$input_name])) {
            $field_var = [];
            foreach ($_POST[$input_name] as $language => $value) {
                $field_var[$language] = form_sanitizer($value);
            }

            return serialize($field_var);
        }

        return FALSE;
    }

    /**
     * @param string    $value
     * @param bool|null $result
     *
     * @return bool
     */
    public static function isSerialized($value, &$result = NULL) {
        // A bit of a give away this one
        if (!is_string($value)) {
            return FALSE;
        }
        // Serialized FALSE, return TRUE. unserialize() returns FALSE on an
        // invalid string, or it could return FALSE if the string is serialized
        // FALSE, eliminate that possibility.
        if ('b:0;' === $value) {
            $result = FALSE;

            return TRUE;
        }
        $length = strlen($value);
        $end = '';
        if (isset($value[0])) {
            switch ($value[0]) {
                case 's':
                    if ('"' !== $value[$length - 2]) {
                        return FALSE;
                    }
                    break;
                case 'b':
                case 'i':
                case 'd':
                    // This looks odd, but it is quicker than isset()ing
                    $end .= ';';
                case 'a':
                case 'O':
                    $end .= '}';
                    if (':' !== $value[1]) {
                        return FALSE;
                    }
                    switch ($value[2]) {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                            break;
                        default:
                            return FALSE;
                    }
                case 'N':
                    $end .= ';';
                    if ($value[$length - 1] !== $end[0]) {
                        return FALSE;
                    }
                    break;
                default:
                    return FALSE;
            }
        }

        if (($result = unserialize(stripslashes($value))) === FALSE) {
            $result = NULL;

            return FALSE;
        }

        return TRUE;
    }

    /**
     * `input` renders field.
     * `display` renders data
     *
     * @param string $method ('input' or 'display')
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * Set Quantum system locale
     *
     * @param mixed $locale
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * If modules are used, specify fields module path
     * API follows Version 7.00's User Fields module.
     *
     * @param mixed $plugin_folder_path
     */
    public function setPluginFolder($plugin_folder_path) {
        $this->plugin_folder = $plugin_folder_path;
    }

    /**
     * If modules are used, specify fields module locale libs folder path
     * API follows Version 7.00's User Fields Module.
     *
     * @param string $locale_folder_path
     */
    public function setPluginLocaleFolder($locale_folder_path) {
        $this->plugin_locale_folder = $locale_folder_path;
    }

    /**
     * Give your Quantum based system a name. Will add to breadcrumbs if available.
     *
     * @param string $system_title
     */
    public function setSystemTitle($system_title) {
        $this->system_title = $system_title;
    }

    ### Getters ####

    /**
     * Database Handler for Category Structuring
     * If it does not exist, quantum will automatically build a template onload.
     *
     * @param string $category_db
     */
    public function setCategoryDb($category_db) {
        $this->category_db = $category_db;
    }

    /**
     * Database Handler for Field Structuring
     * If it does not exist, quantum will automatically build a template onload.
     *
     * @param string $field_db
     */
    public function setFieldDb($field_db) {
        $this->field_db = $field_db;
    }

    /**
     * Additional data-id referencing.
     * $userdata for instance.
     *
     * @param array $callback_data
     */
    public function setCallbackData($callback_data) {
        $this->callback_data = $callback_data;
    }

    /**
     * The internal admin rights by a user to use this system.
     * if specified, to lock down to certain user rights.
     *
     * @param string $admin_rights
     */
    public function setAdminRights($admin_rights) {
        $this->admin_rights = $admin_rights;
    }

    ### Loaders ####
    /* Read into serialized field label and returns the value */

    /**
     * @return array
     */
    public function getCatList() {
        return $this->cat_list;
    }

    /**
     * Get results from running load_structure
     *
     * @param string $key
     *
     * @return array
     */
    public function getFields($key = NULL) {
        return (isset($this->fields[$key])) ? (array)$this->fields[$key] : $this->fields;
    }

    /**
     * UF Admin
     */
    public function displayQuantumAdmin() {
        pageaccess($this->admin_rights);
        define('IN_QUANTUM', TRUE);

        if ($this->system_title) {
            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $this->system_title]);
            add_to_title($this->system_title);
        }

        if ($this->method == 'input') {
            $this->loadFields(); // return fields
            $this->loadFieldCats(); // return cat
            $this->moveFields();
            $this->deleteCategory();
            $this->deleteFields();
            $this->getAvailableModules();
        }
        $this->view();
    }

    /**
     * Load fields
     */
    public function loadFields() {
        $this->page = dbquery_tree_full(
            $this->category_db,
            "field_cat_id",
            "field_parent",
            "ORDER BY field_cat_order ASC"
        );
        $result = dbquery("
        SELECT field.*,
        cat.field_cat_id, cat.field_cat_name, cat.field_parent, cat.field_cat_class,
        root.field_cat_id AS page_id, root.field_cat_name AS page_name, root.field_cat_db, root.field_cat_index FROM
        ".$this->field_db." field
        LEFT JOIN ".$this->category_db." cat ON (cat.field_cat_id = field.field_cat)
        LEFT JOIN ".$this->category_db." root ON (root.field_cat_id = cat.field_parent)
        ORDER BY cat.field_cat_order, field.field_order
        ");
        $max_rows = dbrows($result);
        if ($max_rows > 0) {
            while ($data = dbarray($result)) {
                $this->fields[$data['field_cat']][$data['field_id']] = $data;
            }
        }
    }

    /**
     * Returns $this->page_list and $this->cat_list
     */
    public function loadFieldCats() {
        // Load Field Cats
        if (empty($this->page_list) && empty($this->cat_list)) {
            $result = dbquery("SELECT * FROM ".$this->category_db." ORDER BY field_cat_order");
            if (dbrows($result) > 0) {
                while ($list_data = dbarray($result)) {
                    if ($list_data['field_parent'] != '0') {
                        $this->cat_list[$list_data['field_cat_id']] = $list_data['field_cat_name'];
                    } else {
                        $this->page_list[$list_data['field_cat_id']] = self::parseLabel($list_data['field_cat_name']);
                    }
                }
            }
        }
        if (empty($this->field_cat_index)) {
            $this->field_cat_index = dbquery_tree($this->category_db, 'field_cat_id', 'field_parent');
        }
    }

    /**
     * Parse the correct label language. Requires to be serialized $value.
     *
     * @param string $value Serialized
     *
     * @return string
     * NOTE: If your field does not parse properly, check your column length. Set it to TEXT NOT NULL.
     */
    public static function parseLabel($value) {
        if (self::isSerialized($value)) {
            $value = unserialize(stripslashes($value)); // if anyone can give me an @unserialize($value) withotu E_NOTICE. I'll drop is_serialized function.

            return (string)(isset($value[LANGUAGE])) ? $value[LANGUAGE] : '';
        } else {
            return (string)$value;
        }
    }

    /**
     * Move fields order - up and down
     */
    private function moveFields() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();

        if (isset($_GET['action']) && isset($_GET['order']) && isnum($_GET['order']) && isset($_GET['parent_id']) && isnum($_GET['parent_id'])) {
            if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && ($_GET['action'] == 'cmu' or $_GET['action'] == 'cmd')) {
                $data = [];
                $result = dbquery("SELECT field_cat_id FROM ".$this->category_db." WHERE field_parent='".intval($_GET['parent_id'])."' AND field_cat_order='".intval($_GET['order'])."'"); // more than 1.
                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                }
                if ($_GET['action'] == 'cmu') { // category move up.
                    if (!$this->debug && !empty($data['field_cat_id'])) {
                        dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$data['field_cat_id']."'");
                    }
                    if (!$this->debug) {
                        // sync the whole order
                        dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$_GET['cat_id']."'");
                    }
                } else if ($_GET['action'] == 'cmd') {
                    if (!$this->debug && !empty($data['field_cat_id'])) {
                        dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$data['field_cat_id']."'");
                    }
                    if (!$this->debug) {
                        dbquery("UPDATE ".$this->category_db." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$_GET['cat_id']."'");
                    }
                }
                if (!$this->debug) {
                    $res = dbquery("SELECT field_cat_id FROM ".$this->category_db." WHERE field_parent=:id ORDER BY field_cat_order", [':id' => intval($_GET['parent_id'])]);
                    if (dbrows($res)) {
                        $i = 1;
                        while ($rows = dbarray($res)) {
                            dbquery("UPDATE ".$this->category_db." SET field_cat_order=:order WHERE field_cat_id=:id", [
                                ':order' => $i,
                                ':id'    => intval($rows['field_cat_id'])
                            ]);
                            $i++;
                        }
                    }
                    redirect(FUSION_SELF.$aidlink);
                }
            } else if (isset($_GET['field_id']) && isnum($_GET['field_id']) && ($_GET['action'] == 'fmu' or $_GET['action'] == 'fmd')) {
                $data = [];
                $result = dbquery("SELECT field_id FROM ".$this->field_db." WHERE field_cat='".intval($_GET['parent_id'])."' AND field_order='".intval($_GET['order'])."'");
                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                }
                if ($_GET['action'] == 'fmu') { // field move up.
                    if (!$this->debug) {
                        if (!empty($data['field_id'])) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id='".$data['field_id']."'");
                        }
                        dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_id='".$_GET['field_id']."'");
                    } else {
                        print_p('Move Field ID '.$_GET['field_id'].' Up a slot and Field ID '.$data['field_id'].' down a slot.');
                    }
                } else if ($_GET['action'] == 'fmd') {
                    if (!$this->debug) {
                        if (!empty($data['field_id'])) {
                            dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_id='".$data['field_id']."'");
                        }
                        dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_id='".$_GET['field_id']."'");
                    } else {
                        print_p('Move Field ID '.$_GET['field_id'].' Down a slot and Field ID '.$data['field_id'].' up a slot.');
                    }
                }
                if (!$this->debug) {
                    $res = dbquery("SELECT field_id, field_order FROM ".$this->field_db." WHERE field_cat=:parent_id ORDER BY field_order", [':parent_id' => ($_GET['parent_id'])]);
                    if (dbrows($res)) {
                        $i = 1;
                        while ($rows = dbarray($res)) {
                            print_p($rows);
                            dbquery("UPDATE ".$this->field_db." SET field_order=:order WHERE field_id=:id", [
                                ':order' => $i,
                                ':id'    => intval($rows['field_id'])
                            ]);
                            $i++;
                        }
                    }
                    redirect(FUSION_SELF.$aidlink);
                }
            }
        }
    }

    /**
     * Delete category
     */
    private function deleteCategory() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();
        $this->debug = FALSE;
        $data = [];

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.$aidlink);
        }

        if (isset($_GET['action']) && $_GET['action'] == 'cat_delete' && isset($_GET['cat_id']) && $_GET['cat_id'] != 1 && self::validateFieldCat($_GET['cat_id'])) {

            // do action of the interior form
            if (isset($_POST['delete_cat'])) {

                // get root node
                $target_database = '';
                $field_list = [];

                if (isset($_POST['delete_subcat']) or isset($_POST['delete_field'])) {

                    if (in_array($_GET['cat_id'], $this->page_list)) {

                        // this is root.
                        $result = dbquery("
                                  SELECT field_cat_id, field_parent, field_cat_db FROM ".$this->category_db."
                                  WHERE field_cat_id='".intval($_GET['cat_id'])."'
                                  ");
                    } else {
                        $result = dbquery("
                        SELECT uf.field_cat_id, root.field_cat_db FROM ".$this->category_db." uf
                        LEFT JOIN ".$this->category_db." root ON uf.field_parent = root.field_cat_id
                        WHERE uf.field_cat_id='".intval($_GET['cat_id'])."'
                        ");
                    }

                    if ($result == NULL) {
                        die($this->locale['fields_0653']);
                    }

                    if (dbrows($result) > 0) {

                        $data += dbarray($result);

                        $target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;

                        $field_list = fieldgenerator($target_database);

                    }

                    if ($this->debug) {
                        print_p($field_list);
                        print_p($target_database);
                    }

                }

                if (isset($_POST['delete_subcat'])) {

                    // When deletion of a master page and involving all subcategories
                    if ($this->debug) {
                        print_p($this->page[$_GET['cat_id']]);
                    }

                    // execute removal on child fields and cats
                    foreach ($this->page[$_GET['cat_id']] as $field_category) {

                        $result = dbquery("
                                  SELECT field_id, field_name FROM ".$this->field_db."
                                  WHERE field_cat='".$field_category['field_cat_id']."'
                                  "); // find all child > 1

                        if (dbrows($result) > 0) {

                            while ($data = dbarray($result)) {
                                // remove column from db , and fields
                                if (in_array($data['field_name'], $field_list)) { // verify table integrity

                                    if ($this->debug) {
                                        print_p("DROP ".$data['field_name']." FROM ".$target_database);
                                        print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
                                    } else {

                                        dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");

                                        if (!empty($target_database) && !empty($data['field_name'])) {
                                            self::drop_column($target_database, $data['field_name']);
                                        }

                                    }

                                }

                                // remove category.
                                if ($this->debug) {
                                    print_p("DELETE ".$field_category['field_cat_id']." FROM ".$this->category_db);
                                } else {
                                    dbquery("DELETE FROM ".$this->category_db." WHERE field_cat_id='".$field_category['field_cat_id']."'");
                                }

                            } // end while

                        }
                    }

                } else if (isset($_POST['move_subcat']) && $_POST['move_subcat'] > 0) {

                    // When deletion to move subcategory
                    foreach ($this->page[$_GET['cat_id']] as $field_category) {
                        $new_parent = form_sanitizer($_POST['move_subcat'], 0, 'move_subcat');

                        if ($this->debug) {
                            print_p("MOVED ".$field_category['field_cat_id']." TO category ".$new_parent);
                            print_p("DELETE ".$_GET['cat_id']." FROM ".$this->category_db);
                        } else {

                            dbquery("UPDATE ".$this->category_db." SET field_parent='".$new_parent."' WHERE field_cat_id='".$field_category['field_cat_id']."'");
                        }
                    }

                } else if (isset($_POST['delete_field']) && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
                    // Delete fields
                    $this->debug = FALSE;
                    if ($this->debug) {
                        print_p('Cat ID was not found. Please check again.');
                    }
                    // Delete Fields - Bug with Isset errors
                    $result = dbquery("SELECT field_id, field_name FROM ".$this->field_db." WHERE field_cat='".intval($_GET['cat_id'])."'");
                    if (dbrows($result) > 0) {
                        while ($data = dbarray($result)) {
                            if (in_array($data['field_name'], $field_list)) { // verify table integrity
                                if ($this->debug) {
                                    print_p("DROP ".$data['field_name']." FROM ".$target_database);
                                    print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
                                } else {
                                    $field_del_sql = "DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'";
                                    $field_count = $this->validateField($data['field_id']);
                                    if ($field_count) {
                                        dbquery($field_del_sql);
                                    }
                                    // drop a column
                                    if (!empty($target_database)) {
                                        self::drop_column($target_database, $data['field_name']);
                                    }
                                }
                            }
                        }

                        addnotice('success', $this->locale['field_0200']);
                        redirect(FUSION_SELF.$aidlink);
                    }

                } // category deletion path 2
                else if (!isset($_POST['delete_field']) && isset($_POST['move_field']) && $_POST['move_field'] > 0) {
                    $rows = dbcount("(field_id)", $this->field_db, "field_cat='".intval($_GET['cat_id'])."'");
                    if ($rows) {
                        $new_parent = form_sanitizer($_POST['move_field'], 0, 'move_field');
                        dbquery("UPDATE ".$this->field_db." SET field_cat='".intval($new_parent)."' WHERE field_cat='".intval($_GET['cat_id'])."'");
                    }

                }
                // Delete the current category
                $delete_cat_sql = "DELETE FROM ".$this->category_db." WHERE field_cat_id='".intval($_GET['cat_id'])."'";
                if ($this->debug) {
                    print_p($delete_cat_sql);
                } else {
                    dbquery($delete_cat_sql);
                    addnotice('success', $this->locale['field_0200']);
                    redirect(FUSION_SELF.$aidlink);
                }
            } else {
                // show interior form
                $field_list = [];
                $form_action = FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$_GET['cat_id'];
                $result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."' OR field_cat_id='".get_hkey($this->category_db,
                        "field_cat_id",
                        "field_parent",
                        $_GET['cat_id'])."'");
                if (dbrows($result) > 0) {
                    $data += dbarray($result);
                    // get field list - populate child fields of a category.
                    $result = dbquery("SELECT field_id, field_name, field_cat FROM ".$this->field_db." WHERE field_cat='".intval($_GET['cat_id'])."'");
                    if (dbrows($result) > 0) {
                        // get field list.
                        while ($data = dbarray($result)) {
                            $field_list[$data['field_cat']][$data['field_id']] = $data['field_name'];
                        }
                    }
                    if ((isset($data['field_parent']) && isset($this->page[$data['field_parent']])) or (!empty($field_list) && $field_list[$_GET['cat_id']] > 0)) {
                        ob_start();
                        echo openmodal("delete", $this->locale['fields_0313'], [
                            'class'  => 'modal-lg modal-center',
                            'static' => TRUE
                        ]);
                        echo openform('delete_cat_form', 'post', $form_action);
                        if (isset($this->page[$_GET['cat_id']])) {
                            echo "<div class='row'>";
                            echo "<div class='col-xs-12 col-sm-6'><span class='strong'>".sprintf($this->locale['fields_0600'],
                                    count($this->page[$_GET['cat_id']]))."</span><br/>";
                            echo "<div class='alert alert-info m-t-10'>";
                            echo "<ol style='list-style:inherit !important; margin-bottom:0;'>";
                            foreach ($this->page[$_GET['cat_id']] as $field_category) {
                                echo "<li style='list-style-type:decimal;'>".self::parseLabel($field_category['field_cat_name'])."</li>";
                            }
                            echo "</ol>";
                            echo "</div>";
                            echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>";
                            $page_list = $this->page_list;
                            unset($page_list[$_GET['cat_id']]);
                            if (count($page_list) > 0) {
                                echo form_select('move_subcat', $this->locale['fields_0314'], '',
                                    ["options" => $page_list]);
                            }
                            echo form_checkbox('delete_subcat', $this->locale['fields_0315'],
                                count($page_list) < 1);
                            echo "</div></div>";
                        }
                        if (isset($field_list[$_GET['cat_id']])) {
                            echo "<div class='row'>";
                            echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'><span class='strong'>".sprintf($this->locale['fields_0601'],
                                    count($field_list[$_GET['cat_id']]))."</span><br/>";
                            echo "<div class='well strong m-t-10'>";
                            foreach ($field_list[$_GET['cat_id']] as $field) {
                                echo "- ".$field."<br/>";
                            }
                            echo "</div>";
                            echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>";
                            $exclude_list[] = $_GET['cat_id'];
                            foreach ($this->page_list as $page_id => $page_name) {
                                $exclude_list[] = $page_id;
                            }
                            echo form_select_tree('move_field', $this->locale['fields_0316'], '', [
                                'no_root'      => 1,
                                'disable_opts' => $exclude_list
                            ], $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');

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
                    if ($this->debug) {
                        addnotice('info', 'Cat ID was not found. Please check again.<br/>Category ID was not found. Please check again.');
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                }
            }
        }
    }

    /**
     * @param int $field_cat_id
     *
     * @return false|int
     */
    private function validateFieldCat($field_cat_id) {
        if (isnum($field_cat_id)) {
            return dbcount("(field_cat_id)", $this->category_db, "field_cat_id='".intval($field_cat_id)."'");
        }

        return FALSE;
    }

    /**
     * @param int $field_id
     *
     * @return false|int
     */
    private function validateField($field_id) {
        if (isnum($field_id)) {
            return dbcount("(field_id)", $this->field_db, "field_id='".intval($field_id)."'");
        }

        return FALSE;
    }

    /**
     * Delete fields
     */
    private function deleteFields() {
        $aidlink = fusion_get_aidlink();

        if (isset($_GET['action']) && $_GET['action'] == 'field_delete' && isset($_GET['field_id']) && self::validateField($_GET['field_id'])) {
            $result = dbquery("SELECT field.field_id, field.field_cat, field.field_order, field.field_name, u.field_cat_id, u.field_parent, root.field_cat_db
            FROM ".$this->field_db." field
            LEFT JOIN ".$this->category_db." u ON (field.field_cat=u.field_cat_id)
            LEFT JOIN ".$this->category_db." root ON (u.field_parent = root.field_cat_id)
            WHERE field_id='".intval($_GET['field_id'])."'
            ");
            if (dbrows($result) > 0) {

                $data = dbarray($result);
                $target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;
                $field_list = fieldgenerator($target_database);

                if (in_array($data['field_name'], $field_list)) {
                    // drop database
                    if (!$this->debug && !empty($target_database)) {
                        dbquery("ALTER TABLE ".$target_database." DROP ".$data['field_name']);
                        // reorder the rest of the same cat minus 1
                        dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
                    }

                } else {
                    // just delete the field
                    if (!$this->debug) {
                        dbquery("DELETE FROM ".$this->field_db." WHERE field_id='".$data['field_id']."'");
                    } else {
                        print_p("DELETE ".$data['field_id']." FROM ".$this->field_db);
                    }
                }

                if (!$this->debug) {
                    addnotice('success', $this->locale['field_0201']);
                    redirect(FUSION_SELF.$aidlink);
                }

            } else {

                if (!$this->debug) {
                    addnotice('warning', $this->locale['field_0202']);
                    redirect(FUSION_SELF.$aidlink);
                } else {

                    print_p($this->locale['field_0202']);

                }

            }
        }
    }

    /**
     * Get available modules
     */
    private function getAvailableModules() {
        $this->locale = fusion_get_locale();
        $result = dbquery("SELECT field_id, field_name, field_cat, field_required, field_log, field_registration, field_order, field_cat_name
                    FROM ".$this->field_db." tuf
                    INNER JOIN ".$this->category_db." tufc ON (tuf.field_cat = tufc.field_cat_id)
                    WHERE field_type = 'file'
                    ORDER BY field_cat_order, field_order");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $this->enabled_fields[] = $data['field_name'];
            }
        }
        $user_field_name = '';
        $user_field_desc = '';
        $plugin_folder = $this->plugin_folder;

        if (is_array($plugin_folder)) {

            foreach ($plugin_folder as $folder_name) {
                // dont use opendir
                $files = makefilelist($folder_name, '.|..|index.php', TRUE);

                if (!empty($files)) {

                    foreach ($files as $file) {

                        if (preg_match("/_var.php/i", $file)) {

                            $field_title = $this->filenameToTitle($file);

                            if (!in_array($field_title, $this->enabled_fields)) {

                                if ($this->module_debug) {
                                    print_p($field_title.' set for load.');
                                }

                                if (file_exists($this->plugin_locale_folder.$field_title.".php")) {
                                    $locale = fusion_get_locale('', $this->plugin_locale_folder.$field_title.".php");

                                    include $folder_name.$field_title."_include_var.php";

                                    $this->available_field_info[$field_title] = [
                                        'title'       => $user_field_name,
                                        'description' => $user_field_desc
                                    ];

                                    $this->get_available_modules[$field_title] = $user_field_name;

                                    if ($this->module_debug) {
                                        print_p($field_title.' loaded.');
                                    }

                                } else {
                                    addnotice('warning', $field_title.$this->locale['fields_0659']);
                                }

                            }

                            unset($field_name);
                        }
                    }
                } else {

                    $folders = makefilelist($folder_name, '.|..|index.php', TRUE, 'folders');

                    // there is afolders here
                    if (!empty($folders)) {

                        foreach ($folders as $folder) {

                            $locale_path = $folder_name.$folder.'/locale/'.LANGUAGE.'/';
                            $plugin_path = $folder_name.$folder.'/user_fields/';

                            if (is_dir($plugin_path)) {

                                $files = makefilelist($plugin_path, '.|..|index.php', TRUE);

                                foreach ($files as $file) {

                                    if (preg_match("/_var.php/i", $file)) {

                                        $field_title = $this->filenameToTitle($file);

                                        if (!in_array($field_title, $this->enabled_fields)) {

                                            if ($this->module_debug) {
                                                print_p($field_title.' set for load.');
                                            }

                                            if (is_file($locale_path.$field_title.".php")) {

                                                $locale = fusion_get_locale('', $locale_path.$field_title.".php");

                                                include($plugin_path.$field_title."_include_var.php");

                                                $this->available_field_info[$field_title] = [
                                                    'title'       => $user_field_name,
                                                    'description' => $user_field_desc
                                                ];

                                                $this->get_available_modules[$field_title] = $user_field_name;

                                                if ($this->module_debug) {
                                                    print_p($field_title.' loaded.');
                                                }

                                            } else {
                                                addnotice('warning', $field_title.$this->locale['fields_0659']);
                                            }
                                        }
                                        unset($field_name);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ($temp = opendir($plugin_folder)) {

                while (FALSE !== ($file = readdir($temp))) {

                    if (!in_array($file, ["..", ".", "index.php"]) && !is_dir($this->plugin_folder.$file)) {

                        if (preg_match("/_var.php/i", $file)) {

                            $field_title = $this->filenameToTitle($file);

                            if (!in_array($field_title, $this->enabled_fields)) {

                                if ($this->module_debug) {
                                    print_p($field_title.' set for load.');
                                }

                                if (file_exists($this->plugin_locale_folder.$field_title.".php")) {

                                    $locale = fusion_get_locale('', $this->plugin_locale_folder.$field_title.".php");

                                    include $this->plugin_folder.$field_title."_include_var.php";

                                    $this->available_field_info[$field_title] = [
                                        'title'       => $user_field_name,
                                        'description' => $user_field_desc
                                    ];

                                    $this->get_available_modules[$field_title] = $user_field_name;

                                    if ($this->module_debug) {
                                        print_p($field_title.' loaded.');
                                    }

                                } else {
                                    addnotice('warning', $field_title.$this->locale['fields_0659']);
                                }
                            }
                            unset($field_name);
                        }
                    }
                }
                closedir($temp);
            }
        }

    }

    /**
     * Get the field title from filename
     *
     * @param string $filename
     *
     * @return string
     */
    private function filenameToTitle($filename) {
        $field_name = explode("_", $filename);
        $field_title = "";
        for ($i = 0; $i <= count($field_name) - 3; $i++) {
            $field_title .= ($field_title) ? "_" : "";
            $field_title .= $field_name[$i];
        }

        return $field_title;
    }

    /**
     * UF admin UI
     */
    public function view() {
        $aidlink = fusion_get_aidlink();
        $active = '';
        if ($this->debug) {
            print_p($_POST);
        }
        opentable($this->system_title);
        echo "<div class='row'>";
        echo "<div class='col-xs-12 col-sm-7'>";
        openside('');
        if (!empty($this->page[0])) {
            $tab_title = [];
            foreach ($this->page[0] as $page_id => $page_data) {
                $tab_title['title'][$page_id] = self::parseLabel($page_data['field_cat_name']);
                $tab_title['id'][$page_id] = $page_id;
                $tab_title['icon'][$page_id] = $page_data['field_cat_class'];
                $active = ((isset($_GET['cat_id']) && $page_id == $_GET['cat_id']) ? $page_id : '');
            }

            if (check_get("field_id") || check_get("module_id")) {
                $_fields = flatten_array($this->fields);

                foreach ($_fields as $fData) {
                    $active = 0;
                    if ($field_id = get("field_id", FILTER_VALIDATE_INT)) {
                        if ($fData['field_id'] == $field_id) {
                            $fieldCat = $fData['field_cat'];
                            $active = get_root($this->field_cat_index, $fieldCat);
                            break;
                        }
                    } else if ($module_id = get("module_id", FILTER_VALIDATE_INT)) {
                        if ($fData['field_id'] == $module_id) {
                            $fieldCat = $fData['field_cat'];
                            $active = get_root($this->field_cat_index, $fieldCat);
                            break;
                        }
                    }
                }
            }

            reset($tab_title['title']);

            $default_active = key($tab_title['title']);

            // Tab
            $tab_active = FusionTabs::tabActive($tab_title, $active ?: $default_active);
            $tabs = new FusionTabs();
            $tabs->setRemember(TRUE);
            echo $tabs->openTab($tab_title, $tab_active, 'uftab', FALSE, FALSE, '', []);
            foreach ($this->page[0] as $page_id => $page_details) {
                echo $tabs->openTabBody($tab_title['id'][$page_id], $tab_active);
                // load all categories here.
                if ($this->debug) {
                    echo "<div class='m-t-20 text-dark'>";
                    if ($page_id == 1) {
                        echo sprintf($this->locale['fields_0100'], DB_USERS);
                    } else {
                        echo sprintf($this->locale['fields_0101'], $page_details['field_cat_db'], $page_details['field_cat_index']);
                    }
                    echo "</div>";
                }
                // Edit/Delete Category Administration
                echo "<div class='clearfix'>";
                echo "<div class='m-t-20 m-b-10 pull-right'>";
                echo "<div class='btn-group'>";
                echo "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$page_id."'>".$this->locale['fields_0308']."</a>";
                if ($page_id !== 1) {
                    echo "<a class='btn btn-danger' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$page_id."'>".$this->locale['fields_0313']."</a>";
                }
                echo "</div>";
                echo "</div>";
                echo "</div>";

                if (isset($this->page[$page_id])) {
                    echo "<div class='clearfix'>";
                    $i = 0;
                    $counter = count($this->page[$page_id]) - 1;
                    foreach ($this->page[$page_id] as $cat_id => $field_cat) {
                        // field category information
                        if ($this->debug) {
                            print_p($field_cat);
                        }
                        echo "<div class='well clearfix p-t-0 p-b-0'>";
                        echo "<h4 class='display-inline-block m-r-10'>".self::parseLabel($field_cat['field_cat_name'])."</h4>";
                        //echo form_para(self::parse_label($field_cat['field_cat_name']), $cat_id.'-'.self::parse_label($field_cat['field_cat_name']), 'profile_category_name display-inline-block m-r-15 pull-left');
                        echo "<div class='display-inline-block spacer-xs'>";
                        if ($i != 0) {
                            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=cmu&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".($field_cat['field_cat_order'] - 1)."'>".$this->locale['move_up']."</a> &middot; ";
                        } else {
                            echo "<span class='text-lighter'>".$this->locale['move_up']."</span> &middot; ";
                        }
                        if ($i !== $counter) {
                            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=cmd&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".($field_cat['field_cat_order'] + 1)."'>".$this->locale['move_down']."</a> &middot; ";
                        } else {
                            echo "<span class='text-lighter'>".$this->locale['move_down']."</span> &middot; ";
                        }
                        echo "<a href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$cat_id."'>".$this->locale['edit']."</a> &middot; ";
                        echo "<a class='text-danger' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$cat_id."'>".$this->locale['delete']."</a>";
                        echo "</div>";

                        if (isset($this->fields[$cat_id])) {
                            $k = 0;
                            $item_counter = count($this->fields[$cat_id]) - 1;
                            foreach ($this->fields[$cat_id] as $field_data) {
                                $start_edit = '';
                                $end_edit = '';
                                if ($this->debug) {
                                    print_p($field_data);
                                }
                                //Fields - Move down/Move Up - Edit - Delete
                                if (isset($_GET['module_id']) || isset($_GET['feed_id'])) {
                                    $item_id = isset($_GET['module_id']) ? $_GET['module_id'] : 0;
                                    if (!$item_id) {
                                        $item_id = isset($_GET['field_id']) ? $_GET['field_id'] : 0;
                                    }

                                    if ($item_id == $field_data['field_id']) {
                                        $start_edit = "<div class='alert alert-info'>";
                                        $end_edit = "</div>";
                                    }
                                }

                                echo $start_edit;
                                echo "<div class='clearfix'>";
                                echo "<div class='pull-right m-t-0 m-r-15'>";
                                if ($k != 0) {
                                    echo "<a href='".FUSION_SELF.$aidlink."&amp;action=fmu&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".($field_data['field_order'] - 1)."'>".$this->locale['move_up']."</a> &middot; ";
                                } else {
                                    echo "<span class='text-lighter'>".$this->locale['move_up']."</span> &middot; ";
                                }
                                if ($k !== $item_counter) {
                                    echo "<a href='".FUSION_SELF.$aidlink."&amp;action=fmd&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".($field_data['field_order'] + 1)."'>".$this->locale['move_down']."</a> &middot; ";
                                } else {
                                    echo "<span class='text-lighter'>".$this->locale['move_down']."</span> &middot; ";
                                }
                                if ($field_data['field_type'] == 'file') {
                                    echo "<a href='".FUSION_SELF.$aidlink."&amp;action=module_edit&amp;module_id=".$field_data['field_id']."'>".$this->locale['edit']."</a> &middot; ";
                                } else {
                                    echo "<a href='".FUSION_SELF.$aidlink."&amp;action=field_edit&amp;field_id=".$field_data['field_id']."'>".$this->locale['edit']."</a> &middot; ";
                                }
                                echo "<a class='text-danger' href='".FUSION_SELF.$aidlink."&amp;action=field_delete&amp;field_id=".$field_data['field_id']."'>".$this->locale['delete']."</a>";
                                echo "</div>";
                                echo "</div>";

                                $options = ['inline' => TRUE, 'show_title' => TRUE, 'hide_value' => TRUE];

                                if ($field_data['field_type'] == 'file') {
                                    $options += [
                                        'plugin_folder'        => $this->plugin_folder,
                                        'plugin_locale_folder' => $this->plugin_locale_folder,
                                    ];
                                }
                                echo $this->displayFields($field_data, $this->callback_data, $this->method, $options);

                                echo $end_edit;

                                $k++;
                            }
                        }
                        $i++;
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    // display no category
                    echo "<div class='m-t-20 well text-center'>".$this->locale['fields_0102'].self::parseLabel($page_details['field_cat_name'])."</div>";
                }
                echo $tabs->closeTabBody();
            }
            echo $tabs->closeTab();
        } else {
            echo "<div class='well text-center'>".$this->locale['fields_0103']."</div>";
        }
        closeside();
        echo "</div>";
        echo "<div class='col-xs-12 col-sm-5'>";
        openside('');
        $this->quantumAdminButtons();
        closeside();
        echo "</div></div>";
        closetable();
    }

    /**
     * Display fields for each fieldDB record entry
     *
     * @param array  $data   The array of the user field.
     * @param mixed  $callback_data
     * @param string $method Possible valie: input, display. In case of any other value the method return false.
     * @param array  $options
     *
     * @return array|bool|string False on failure, string if $method 'display' or array if $method is 'input'
     */
    public function displayFields(array $data, $callback_data, $method = 'input', array $options = []) {
        unset($callback_data['user_algo']);
        unset($callback_data['user_salt']);
        unset($callback_data['user_password']);
        unset($callback_data['user_admin_algo']);
        unset($callback_data['user_admin_salt']);
        unset($callback_data['user_admin_password']);

        $data += [
            'field_required' => TRUE,
            'field_error'    => '',
            'field_default'  => ''
        ];

        $default_options = [
            'hide_value'           => FALSE, // input value is not shown on fields render
            'encrypt'              => FALSE, // encrypt field names
            'show_title'           => $method == "input", // display field label
            'deactivate'           => FALSE, // disable fields
            'inline'               => FALSE, // sets the field inline
            'error_text'           => $data['field_error'], // sets the field error text
            'required'             => (bool)$data['field_required'], // input must be filled when validate
            'placeholder'          => $data['field_default'], // helper text in field value
            'plugin_folder'        => $this->plugin_folder, // The folder's path where the field's source files are
            'plugin_locale_folder' => $this->plugin_locale_folder, // The folder's path where the field's locale files are
            'debug'                => FALSE // Show some information to debug
        ];

        $options += $default_options;

        if (!$options['plugin_folder']) {
            $options['plugin_folder'] = $default_options['plugin_folder'];
        }

        if (!$options['plugin_locale_folder']) {
            $options['plugin_locale_folder'] = $default_options['plugin_locale_folder'];
        }

        $options['plugin_locale_folder'] = rtrim($options['plugin_locale_folder'], '/').'/';

        if (is_array($options['plugin_folder']) && !empty($options['plugin_folder'])) {
            $p_folder = $options['plugin_folder'];
            unset($options['plugin_folder']);

            foreach ($p_folder as $plugin_folder) {
                $plugin_folder = rtrim($plugin_folder, '/').'/';
                // Identify the correct folder for the current module.
                if (is_file($plugin_folder.$data['field_name'].'_include.php')) {
                    $options['plugin_folder'] = $plugin_folder;
                } else {
                    $folder_list = makefilelist($plugin_folder, '.|..|index.php', TRUE, 'folders');
                    if (!empty($folder_list)) {
                        // attempt to search.
                        foreach ($folder_list as $folder) {
                            if (is_file($plugin_folder.$folder.'/user_fields/'.$data['field_name'].'_include.php')) {
                                $options['plugin_folder'] = $plugin_folder.$folder.'/user_fields/';

                                if (is_dir($plugin_folder.$folder.'/locale/'.LANGUAGE.'/')) {
                                    $lang_dir = $plugin_folder.$folder.'/locale/'.LANGUAGE.'/';
                                } else {
                                    $lang_dir = $plugin_folder.$folder.'/locale/English/';
                                }

                                $options['plugin_locale_folder'] = $lang_dir;
                            }
                        }
                    }
                }
            }
        } else {
            $options['plugin_folder'] = rtrim($options['plugin_folder'], '/').'/';
        }

        // Sets callback data automatically.
        $option_list = $data['field_options'] ? explode(',', $data['field_options']) : [];

        // Format Callback Data
        $field_value = (isset($callback_data[$data['field_name']]) ? $callback_data[$data['field_name']] : "");
        if (isset($_POST[$data['field_name']]) && !$options['hide_value']) {
            //$field_value = form_sanitizer($_POST[$data['field_name']], '', $data['field_name']);
            $field_value = sanitizer($data['field_name'], "", $data['field_name']);
        } else if ($options['hide_value']) {
            $field_value = "";
        }

        $field_label = ($options['show_title'] ? self::parseLabel($data['field_title']) : "");

        switch ($data['field_type']) {
            case 'file':
                $user_data = $callback_data;
                $profile_method = $method;
                $locale = fusion_get_locale('', $options['plugin_locale_folder'].$data['field_name'].'.php');
                include (!empty($options['plugin_folder']) ? $options['plugin_folder'] : $this->plugin_folder).$data['field_name']."_include.php";

                if ($method == 'input') {
                    if (isset($user_fields)) {
                        return $user_fields;
                    }
                } else if ($method == 'display' && !empty($user_fields['value'])) {
                    return $user_fields;
                }
                unset($user_data);
                unset($profile_method);
                unset($locale);
                break;
            case 'textbox':
                if ($method == 'input') {
                    return form_text($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'number':
                if ($method == 'input') {
                    $options += ['type' => 'number'];

                    return form_text($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'url':
                if ($method == 'input') {
                    $options += ['type' => 'url'];

                    return form_text($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'email':
                if ($method == 'input') {
                    $options += ['type' => 'email'];

                    return form_text($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'select':
                if ($method == 'input') {
                    $options += ['options' => $option_list];
                    $options['keyflip'] = TRUE;
                    return form_select($data['field_name'], self::parseLabel($data['field_title']), $field_value, $options);

                } else if ($method == 'display' && $field_value) {
                    $options_value = explode(",", $data['field_options']);
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => !empty($options_value[$field_value]) ? $options_value[$field_value] : $field_value,
                    ];
                }
                break;
            case 'tags':
                if ($method == 'input') {
                    $options += ['options' => $option_list, 'tags' => TRUE, 'multiple' => TRUE, 'width' => '100%', 'inner_width' => '100%'];

                    return form_select($data['field_name'],
                        $options['show_title'] ? self::parseLabel($data['field_title']) : '',
                        $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'location':
                if ($method == 'input') {
                    $options += ['width' => '100%'];

                    return form_location($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'textarea':
                if ($method == 'input') {
                    return form_textarea($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'checkbox':
                if ($method == 'input') {
                    return form_checkbox($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'datepicker':
                if ($method == 'input') {

                    $options["date_format_php"] = "d-m-Y";
                    $options["date_format_js"] = "DD-MM-YYYY";

                    return form_datepicker($data['field_name'], $field_label, $field_value, $options);

                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => showdate('shortdate', $field_value)
                    ];
                }
                break;
            case 'colorpicker':
                if ($method == 'input') {
                    return form_colorpicker($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'upload':
                if ($method == 'input') {
                    return form_fileinput($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'hidden':
                if ($method == 'input') {
                    return form_hidden($data['field_name'], self::parseLabel($data['field_title']), $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'address':
                if ($method == 'input') {
                    return form_geo($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    if (!is_array($field_value)) {
                        $field_value = explode("|", $field_value);
                        if (count($field_value) === 6) {
                            list($address, $address2, $country, $region, $city, $postcode) = $field_value;
                            $addresses = [
                                [$address, $address2],
                                [$city, $region, $country],
                                [$postcode]
                            ];
                            foreach ($addresses as $adds) {
                                $values[] = implode(",", $adds);
                            }
                            $field_value = implode(",<br>", $values);
                        }
                    }

                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case "contact":
                if ($method == 'input') {
                    return form_contact($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => implode('|', $field_value)
                    ];
                }
                break;
            case 'toggle':
                $options['toggle'] = 1;
                if ($method == 'input') {
                    return form_checkbox($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parseLabel($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
        }

        return FALSE;
    }

    /**
     * Outputs Quantum Admin Button Sets
     */
    public function quantumAdminButtons() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();

        $tab_title['title'][] = $this->locale['fields_0300'];
        $tab_title['id'][] = 'dyn';
        $tab_title['icon'][] = '';

        if (!empty($this->cat_list)) {
            $tab_title['title'][] = $this->locale['fields_0301'];
            $tab_title['id'][] = 'mod';
            $tab_title['icon'][] = '';
        }

        // Extended Tabs
        // add category
        $tab_active = tab_active($tab_title, 0);
        if (isset($_POST['add_cat'])) {
            $tab_title['title'][] = $this->locale['fields_0305'];
            $tab_title['id'][] = 'add';
            $tab_title['icon'][] = '';
            $tab_active = (!empty($this->cat_list)) ? tab_active($tab_title, 2) : tab_active($tab_title, 1);
        } // add field
        else if (isset($_POST['add_field']) && in_array($_POST['add_field'], array_flip($this->getDynamicsType()))) {
            $tab_title['title'][] = $this->locale['fields_0306'];
            $tab_title['id'][] = 'add';
            $tab_title['icon'][] = '';
            $tab_active = tab_active($tab_title, 2);

        } else if (isset($_POST['add_module']) && in_array($_POST['add_module'], array_flip($this->get_available_modules))) {
            $tab_title['title'][] = $this->locale['fields_0307'];
            $tab_title['id'][] = 'add';
            $tab_title['icon'][] = '';
            $tab_active = tab_active($tab_title, 2);

        } else if (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
            $tab_title['title'][] = $this->locale['fields_0308'];
            $tab_title['id'][] = 'edit';
            $tab_title['icon'][] = '';
            $tab_active = (!empty($this->cat_list)) ? tab_active($tab_title, 2) : tab_active($tab_title, 1);

        } else if (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && isnum($_GET['field_id'])) {
            $tab_title['title'][] = $this->locale['fields_0309'];
            $tab_title['id'][] = 'edit';
            $tab_title['icon'][] = '';
            $tab_active = tab_active($tab_title, 2);

        } else if (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id'])) {
            $tab_title['title'][] = $this->locale['fields_0310'];
            $tab_title['id'][] = 'edit';
            $tab_title['icon'][] = '';
            $tab_active = tab_active($tab_title, 2);
        }

        $tabs = new FusionTabs();

        echo $tabs->openTab($tab_title, $tab_active, 'amd', FALSE, FALSE, 'action', []);
        echo $tabs->openTabBody($tab_title['id'][0], $tab_active);
        echo openform('addfield', 'post', FUSION_SELF.$aidlink);
        echo form_button('add_cat', $this->locale['fields_0311'], 'add_cat', [
            'class'    => 'm-t-20 m-b-20 btn-sm btn-primary btn-block',
            'icon'     => 'fa fa-plus-circle',
            'input_id' => 'add_new_cat'
        ]);

        if (!empty($this->cat_list)) {
            echo "<div class='row m-t-20'>";
            $field_type = $this->getDynamicsType();
            unset($field_type['file']);
            foreach ($field_type as $type => $name) {
                echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-b-20'>".form_button('add_field', $name, $type, ['input_id' => 'add_field-'.$type, 'class' => 'btn-block btn-sm btn-default'])."</div>";
            }
            echo "</div>";
        }

        echo closeform();
        echo $tabs->closeTabBody();

        if (!empty($this->cat_list)) {
            echo $tabs->openTabBody($tab_title['id'][1], $tab_active);
            // list down modules.
            echo openform('addmodule', 'post', FUSION_SELF.$aidlink, ['notice' => 0, 'max_tokens' => 1]);
            echo "<div class='m-t-20'>";
            if (!empty($this->available_field_info)) {
                foreach ($this->available_field_info as $title => $module_data) {
                    echo "<div class='list-group-item'>";
                    echo form_button('add_module', $this->locale['fields_0312'], $title, ['input_id' => 'add_module-'.$title, 'class' => 'btn-sm btn-default pull-right m-l-10']);
                    echo "<div class='overflow-hide'>";
                    echo "<span class='text-dark strong'>".$module_data['title']."</span><br/>";
                    echo "<span>".$module_data['description']."</span><br/>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='alert alert-info text-center m-b-20'>".$this->locale['fields_0660']."</div>";
            }
            echo "</div>";
            echo closeform();
            echo $tabs->closeTabBody();
        }

        if (isset($_POST['add_cat']) or (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            if (!empty($this->cat_list)) {
                echo $tabs->openTabBody($tab_title['id'][2], $tab_active);
            } else {
                echo $tabs->openTabBody($tab_title['id'][1], $tab_active);
            }
            echo "<div class='m-t-20'>";
            echo $this->quantumCategoryForm();
            echo "</div>";
            echo $tabs->closeTabBody();

        } else if (isset($_POST['add_field']) && in_array($_POST['add_field'], array_flip($this->getDynamicsType())) or (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && isnum($_GET['field_id']))) {
            echo $tabs->openTabBody($tab_title['id'][2], $tab_active);
            $this->quantumDynamicsForm();
            echo $tabs->closeTabBody();

        } else if (isset($_POST['add_module']) && in_array($_POST['add_module'], array_flip($this->get_available_modules)) or (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id']))) {
            echo $tabs->openTabBody($tab_title['id'][2], $tab_active);
            $this->displayModuleForm();
            echo $tabs->closeTabBody();

        }
        echo $tabs->closeTab();
    }

    /**
     * Array of available field types
     *
     * @return array
     */
    public function getDynamicsType() {
        return [
            'file'        => $this->locale['fields_0500'],
            'textbox'     => $this->locale['fields_0501'],
            'select'      => $this->locale['fields_0502'],
            'textarea'    => $this->locale['fields_0503'],
            'checkbox'    => $this->locale['fields_0504'],
            'toggle'      => $this->locale['fields_0505'],
            'datepicker'  => $this->locale['fields_0506'],
            'colorpicker' => $this->locale['fields_0507'],
            'upload'      => $this->locale['fields_0508'],
            'hidden'      => $this->locale['fields_0509'],
            'address'     => $this->locale['fields_0510'],
            'contact'     => $this->locale['fields_0516'],
            'tags'        => $this->locale['fields_0511'],
            'location'    => $this->locale['fields_0512'],
            'number'      => $this->locale['fields_0513'],
            'email'       => $this->locale['fields_0514'],
            'url'         => $this->locale['fields_0515'],
        ];
    }

    /**
     * Category Form
     *
     * @return string
     */
    public function quantumCategoryForm() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();

        add_to_jquery("
        $('#field_parent').val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()
        $('#field_parent').bind('change', function() {
        $(this).val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()
        });
        ");

        // Edit Callback
        if (isset($_GET['action']) && $_GET['action'] == 'cat_edit' && isset($_GET['cat_id']) && self::validateFieldCat($_GET['cat_id'])) {
            $result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$_GET['cat_id']."'");
            if (dbrows($result)) {
                $this->field_cat_data = dbarray($result);
            } else {
                addnotice('danger', $this->locale['field_0206']);
                redirect(FUSION_SELF.$aidlink);
            }
        }

        if (check_post("save_cat")) {

            $this->field_cat_data = [
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

            $result = dbquery("SELECT * FROM ".$this->category_db." WHERE field_cat_id='".$this->field_cat_data['field_cat_id']."'");

            if (dbrows($result) > 0) {
                $old_data = dbarray($result);
            }

            if ($this->field_cat_data['field_parent'] == 0) {
                $this->field_cat_data['field_cat_db'] = sanitizer('field_cat_db', 'users', 'field_cat_db');
                $this->field_cat_data['field_cat_index'] = sanitizer('field_cat_index', '', 'field_cat_index');
                $this->field_cat_data['field_cat_class'] = sanitizer('field_cat_class', '', 'field_cat_class');
                // Improvised to code jquery chained selector
                if (!column_exists($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index'])) {
                    fusion_stop($this->locale['fields_0671']);
                }
            }

            if ($this->field_cat_data['field_cat_order'] === 0) {

                $this->field_cat_data['field_cat_order'] = dbresult(dbquery("SELECT MAX(field_cat_order) FROM ".$this->category_db." WHERE field_parent='".$this->field_cat_data['field_parent']."'"), 0) + 1;
            }

            // shuffle between save and update
            if (self::validateFieldCat($this->field_cat_data['field_cat_id'])) {

                dbquery_order(
                    $this->category_db, $this->field_cat_data['field_cat_order'],
                    "field_cat_order",
                    $this->field_cat_data['field_cat_id'], 'field_cat_id',
                    $this->field_cat_data['field_parent'], 'field_parent', FALSE, FALSE
                );

                if (!$this->debug) {
                    if (fusion_safe()) {
                        if (!empty($old_data['field_cat_db']) or $old_data['field_cat_db'] !== "users") {

                            if (!empty($old_data['field_cat_db']) && !empty($old_data['field_cat_index'])) {
                                // CONDITION: HAVE A PREVIOUS TABLE SET
                                if (!empty($this->field_cat_data['field_cat_db'])) {
                                    // new demands a table insertion, checks if same or not. if different.
                                    if ($this->field_cat_data['field_cat_db'] !== $old_data['field_cat_db']) {
                                        // But the current table is different from the previous one
                                        // - build the new one, move the column, drop the old one.
                                        self::build_table($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index']);
                                        self::transfer_table($old_data['field_cat_db'], $this->field_cat_data['field_cat_db']);
                                        self::drop_table($old_data['field_cat_db']);

                                    } else {
                                        if ($old_data['field_cat_index'] !== $this->field_cat_data['field_cat_index']) {
                                            self::rename_column($this->field_cat_data['field_cat_db'], $old_data['field_cat_index'], $this->field_cat_data['field_cat_index'], "MEDIUMINT(8) NOT NULL DEFAULT '0'");
                                        }

                                    }

                                } else if (empty($this->field_cat_data['field_cat_db'])) {
                                    self::drop_table($this->field_cat_data['field_cat_db']);
                                }

                            } else if (!empty($this->field_cat_data['field_cat_index']) && !empty($this->field_cat_data['field_cat_db'])) {

                                self::build_table($this->field_cat_data['field_cat_db'],
                                    $this->field_cat_data['field_cat_index']);

                            }

                            dbquery_insert($this->category_db, $this->field_cat_data, 'update');

                            addnotice('success', $this->locale['field_0207']);
                        }
                        redirect(FUSION_SELF.$aidlink);
                    }

                } else {
                    print_p('Update Mode');
                    print_p($this->field_cat_data);
                }

            } else {

                dbquery_order(
                    $this->category_db,
                    $this->field_cat_data['field_cat_order'],
                    'field_cat_order',
                    $this->field_cat_data['field_cat_id'],
                    'field_cat_id',
                    $this->field_cat_data['field_parent'],
                    'field_parent',
                    TRUE,
                    'field_cat_name',
                    'save'
                );

                if (!$this->debug && fusion_safe()) {

                    if (!empty($this->field_cat_data['field_cat_index']) &&
                        !empty($this->field_cat_data['field_cat_db']) &&
                        $this->field_cat_data['field_cat_db'] !== 'users'
                    ) {

                        self::build_table($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index']);
                    }

                    dbquery_insert($this->category_db, $this->field_cat_data, 'save');

                    addnotice('success', $this->locale['field_0208']);

                    redirect(FUSION_SELF.$aidlink);

                } else {

                    if ($this->debug) {
                        print_p('Save Mode');
                        print_p($this->field_cat_data);
                    }
                }
            }
        }


        if (!$this->field_cat_data["field_cat_db"]) {
            $this->field_cat_data["field_cat_db"] = "users";
            $this->field_cat_data["field_cat_index"] = "user_id";
        }

        // exclusion list - unselectable
        $html = openform('cat_form', 'post', FUSION_REQUEST);
        $html .= form_button('save_cat', $this->locale['fields_0318'], 'save_cat', [
            'input_id' => 'save_cat2',
            'class'    => 'm-b-20 btn-primary'
        ]);

        $html .= self::quantumMultilocaleFields('field_cat_name', $this->locale['fields_0430'], $this->field_cat_data['field_cat_name'], ['required' => 1]);
        // cannot move if there are siblings
        $cat_id = (int)(get("cat_id", FILTER_VALIDATE_INT) ?: 0);

        if ($cat_id && isset($this->field_cat_index[$cat_id])) {
            $html .= form_hidden('field_parent', '', $this->field_cat_data['field_parent']);
        } else {
            $cat_list[] = $cat_id;
            if (!empty($this->cat_list)) {
                foreach ($this->cat_list as $id => $value) {
                    $cat_list[] = $id;
                }
            }
            $html .= form_select_tree('field_parent', $this->locale['fields_0431'], $this->field_cat_data['field_parent'],
                [
                    'parent_value' => $this->locale['fields_0432'],
                    'disable_opts' => $cat_list
                ], $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');
        }
        $html .= form_text('field_cat_order', $this->locale['fields_0433'], $this->field_cat_data['field_cat_order'],
            ['number' => 1]);
        $html .= form_hidden('field_cat_id', '', $this->field_cat_data['field_cat_id'], ['number' => 1]);
        $html .= form_hidden('add_cat', '', 'add_cat', ['input_id' => 'addnewcat']);
        // root settings
        $html .= "<div id='page_settings' class='list-group-item m-t-20'>";

        global $db_name;
        $tableList = [];
        $show_tables = dbquery("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA=:table_name", [":table_name" => stripinput($db_name)]);
        if (dbrows($show_tables)) {
            while ($schData = dbarray($show_tables)) {
                $table_name = str_replace(strtolower(DB_PREFIX), '', $schData['TABLE_NAME']);
                $tableList[$table_name] = $table_name;
            }
        }

        $html .= form_select('field_cat_db', sprintf($this->locale['fields_0434'], " db_prefix_ "),
            $this->field_cat_data['field_cat_db'], [
                'placeholder' => $this->locale['fields_0663'],
                "required"    => TRUE,
                "inline"      => FALSE,
                "deactivate"  => FALSE, //$this->field_cat_data['field_cat_db'],
                "ext_tip"     => $this->locale['fields_0111'],
                "options"     => $tableList
            ]);
        $html .= "<div class='text-smaller m-b-10'>".$this->locale['fields_0112']."</div>";
        $html .= form_text('field_cat_index', $this->locale['fields_0435'], $this->field_cat_data['field_cat_index'],
            [
                'placeholder' => 'user_id',
                "required"    => TRUE,
                "inline"      => FALSE
            ]);
        $html .= "<div class='text-smaller m-b-10'>".$this->locale['fields_0113']."</div>";
        $html .= form_text('field_cat_class', $this->locale['fields_0436'], $this->field_cat_data['field_cat_class'],
            [
                'placeholder' => $this->locale['fields_0437'],
                "inline"      => FALSE
            ]);
        $html .= form_hidden('add_cat', '', 'add_cat');
        $html .= "</div>";
        $html .= form_button('save_cat', $this->locale['fields_0318'], 'save_cat', ['class' => 'm-t-20 btn-primary']);
        $html .= closeform();

        return $html;
    }

    /**
     * Multiple locale fields input
     *
     * @param string $input_name
     * @param string $title
     * @param mixed  $input_value
     * @param array  $options
     *
     * @return string
     */
    public static function quantumMultilocaleFields($input_name, $title, $input_value, array $options = []) {
        $locale = fusion_get_locale();
        $html = '';
        $language_opts = fusion_get_enabled_languages();
        $input_value = self::isSerialized($input_value) ? unserialize(stripslashes($input_value)) : $input_value;
        $options += [
            'function'    => !empty($options['textarea']) && $options['textarea'] == 1 ? 'form_textarea' : 'form_text',
            // only 2 fields type need a multiple locale logically
            'required'    => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
            'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
            'deactivate'  => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
            'width'       => !empty($options['width']) ? $options['width'] : '100%',
            'class'       => !empty($options['class']) ? $options['class'] : '',
            'inline'      => !empty($options['inline']) ? $options['inline'] : '',
            'max_length'  => !empty($options['max_length']) ? $options['max_length'] : '200',
            'error_text'  => !empty($options['error_text']) ? $options['error_text'] : '',
            'safemode'    => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
            'icon'        => !empty($options['icon']) ? $options['icon'] : '',
            'input_id'    => !empty($options['input_id']) ? $options['input_id'] : $input_name,
        ];
        $required = $options['required'];
        $html .= "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $title ? 'row ' : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'>";
        $html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>" : '';
        $html .= ($options['inline'] && $title) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";
        $main_html = '';
        $sub_html = '';
        foreach ($language_opts as $lang => $langNames) {
            $options['field_title'] = $title." (".$langNames.")";
            $options['input_id'] = $input_name."-".$lang;
            if ($lang == LANGUAGE) {
                $options['required'] = $required;
                $options['prepend_value'] = $langNames;
                // Fix this
                $main_html .= $options['function']($input_name."[$lang]", "",
                    isset($input_value[$lang]) ? $input_value[$lang] : $input_value,
                    $options);
            } else {
                $options['required'] = 0;
                $options['prepend_value'] = $langNames;
                $sub_html .= $options['function']($input_name."[$lang]", "", isset($input_value[$lang]) ? $input_value[$lang] : '', $options);
            }
        }

        $html .= $main_html.$sub_html;

        if (count($language_opts) > 1) {
            $html .= "<div class='dropdown m-b-15'>";
            $html .= "<button id='lang_dropdown' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' class='dropdown-toggle btn btn-sm btn-default' type='button'>".$locale['add_language']." <span class='caret'></span></button>";
            $html .= "<ul class='dropdown-menu' aria-labelledby='lang_dropdown'>";
            foreach ($language_opts as $Lang => $LangName) {
                if ($Lang !== LANGUAGE) {
                    $html .= "<li><a data-add='$Lang' data-input='$input_name' data-locale='$LangName' class='pointer data-add'><i class='fa fa-plus-circle fa-fw'></i> $LangName</a></li>";
                    add_to_jquery("$('#".$input_name."-".$Lang."-field').hide();");
                }
            }
            $html .= "</ul>";
            $html .= "</div>";
            if (!defined('QUANTUM_MULTILOCALE_FIELDS')) {
                define('QUANTUM_MULTILOCALE_FIELDS', TRUE);
                add_to_jquery(/** @lang JavaScript */ "
                $('.data-add').on('click', function(e) {
                    var lang = $(this).data('add');
                    var langNames = $(this).data('locale');
                    var inputName = $(this).data('input');
                    var dom = $('#'+ inputName +'-' + lang + '-field');
                    if ( dom.is(':visible') ) {
                        dom.hide();
                        $(this).html('<i class=\"fa fa-plus-circle fa-fw\"></i>'+langNames);
                    } else {
                        dom.show();
                        $(this).html('<i class=\"fa fa-minus-circle fa-fw\"></i>'+langNames);
                    }
                    e.stopPropagation();
                });
                ");
            }
        }
        $html .= ($options['inline'] && $title) ? "</div>" : "";
        $html .= "</div>";

        return $html;
    }

    /**
     * The master form for Adding or Editing Dynamic Fields
     */
    private function quantumDynamicsForm() {
        $aidlink = fusion_get_aidlink();

        $this->field_data['field_config'] = [
            'field_max_b'              => 1000000,
            'field_upload_type'        => 'file',
            'field_upload_path'        => IMAGES,
            'field_valid_file_ext'     => '.zip,.rar,.doc,.xls,.csv,.jpg,.gif,.png,.bmp',
            'field_valid_image_ext'    => '.jpg,.gif,.png,.bmp',
            'field_image_max_w'        => 1920,
            'field_image_max_h'        => 1080,
            'field_thumbnail'          => 0,
            'field_thumbnail_2'        => 0,
            'field_thumb_upload_path'  => 'thumbs/',
            'field_thumb2_upload_path' => 'thumbs/',
            'field_thumb_w'            => 0,
            'field_thumb_h'            => 0,
            'field_thumb2_h'           => 1024,
            'field_thumb2_w'           => 768,
            'field_delete_original'    => 0
        ];

        $form_action = FUSION_SELF.$aidlink;
        if (isset($_GET['action']) && $_GET['action'] == 'field_edit' && isset($_GET['field_id']) && self::validateField($_GET['field_id'])) {
            $form_action .= "&amp;action=".$_GET['action']."&amp;field_id=".$_GET['field_id'];
            $result = dbquery("SELECT * FROM ".$this->field_db." WHERE field_id='".intval($_GET['field_id'])."'");
            if (dbrows($result) > 0) {
                $this->field_data = dbarray($result);
                if ($this->field_data['field_type'] == 'upload') {
                    $this->field_data['field_config'] = fusion_decode($this->field_data['field_config']);
                    if ($this->debug) {
                        print_p($this->field_data);
                    }
                }
            } else {
                if (!$this->debug) {
                    redirect(FUSION_SELF.$aidlink);
                }
            }
        }

        $this->field_data['field_type'] = isset($_POST['add_field']) ? form_sanitizer($_POST['add_field']) : $this->field_data['field_type'];

        if (check_post("save_field")) {
            $this->field_data = [
                'field_type'         => (post("add_field") ? sanitizer("add_field") : $this->field_data['field_type']),
                'field_id'           => sanitizer("field_id", '0', 'field_id'),
                'field_title'        => sanitizer(["field_title"], '', 'field_title', TRUE),
                'field_name'         => sanitizer("field_name", '', 'field_name'),
                'field_cat'          => sanitizer("field_cat", '0', 'field_cat'),
                'field_options'      => (post("field_options") ? sanitizer("field_options", '', 'field_options') : $this->field_data['field_options']),
                'field_default'      => (post("field_default") ? sanitizer("field_default", '', 'field_default') : $this->field_data['field_default']),
                'field_error'        => sanitizer("field_error", '', 'field_error'),
                'field_required'     => (int)check_post("field_required"),
                'field_log'          => (int)check_post("field_log"),
                'field_registration' => (int)check_post("field_registration"),
                'field_order'        => sanitizer("field_order", '0', 'field_order'),
                'field_config'       => ''
            ];

            $this->field_data['field_name'] = str_replace(' ', '_', $this->field_data['field_name']); // make sure no space.

            if ($this->field_data['field_type'] == 'upload') {

                $max_b = isset($_POST['field_max_b']) ? form_sanitizer($_POST['field_max_b'], '', 'field_max_b') : 150000;
                $calc = isset($_POST['field_calc']) ? form_sanitizer($_POST['field_calc'], '', 'field_calc') : 1;
                $this->field_data['field_upload_type'] = isset($_POST['field_upload_type']) ? form_sanitizer($_POST['field_upload_type'], '', 'field_upload_type') : '';
                if (!in_array($this->field_data['field_upload_type'], ['file', 'image'])) {

                    fusion_stop($this->locale['fields_0108']);

                } else {
                    $this->field_data['field_config'] = [
                        'field_upload_type'        => $this->field_data['field_upload_type'],
                        'field_max_b'              => isset($_POST['field_max_b']) && isset($_POST['field_calc']) ? $max_b * $calc : $this->field_data['field_max_b'],
                        'field_upload_path'        => isset($_POST['field_upload_path']) ? form_sanitizer($_POST['field_upload_path'], '', 'field_upload_path') : '',
                        'field_valid_file_ext'     => isset($_POST['field_valid_file_ext']) && $this->field_data['field_upload_type'] == 'file' ? form_sanitizer($_POST['field_valid_file_ext'], '', 'field_valid_file_ext') : '',
                        'field_valid_image_ext'    => isset($_POST['field_valid_image_ext']) && $this->field_data['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_valid_image_ext'], '', 'field_valid_image_ext') : '',
                        'field_image_max_w'        => isset($_POST['field_image_max_w']) && $this->field_data['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_w'], '', 'field_image_max_w') : '',
                        'field_image_max_h'        => isset($_POST['field_image_max_h']) && $this->field_data['field_upload_type'] == 'image' ? form_sanitizer($_POST['field_image_max_h'], '', 'field_image_max_h') : '',
                        'field_thumbnail'          => isset($_POST['field_thumbnail']) ? form_sanitizer($_POST['field_thumbnail'], 0, 'field_thumbnail') : '',
                        'field_thumb_upload_path'  => isset($_POST['field_thumb_upload_path']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail']) ? form_sanitizer($_POST['field_thumb_upload_path'], '', 'field_thumb_upload_path') : '',
                        'field_thumb_w'            => isset($_POST['field_thumb_w']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail']) ? form_sanitizer($_POST['field_thumb_w'], '', 'field_thumb_w') : '',
                        'field_thumb_h'            => isset($_POST['field_thumb_h']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail']) ? form_sanitizer($_POST['field_thumb_h'], '', 'field_thumb_h') : '',
                        'field_thumbnail_2'        => (isset($_POST['field_thumbnail_2']) ? 1 : isset($_POST['field_id'])) ? 0 : $this->field_data['field_thumbnail_2'],
                        'field_thumb2_upload_path' => isset($_POST['field_thumb2_upload_path']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail_2']) ? form_sanitizer($_POST['field_thumb2_upload_path'], '', 'field_thumb2_upload_path') : '',
                        'field_thumb2_w'           => isset($_POST['field_thumb2_w']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail_2']) ? form_sanitizer($_POST['field_thumb2_w'], '', 'field_thumb2_w') : '',
                        'field_thumb2_h'           => isset($_POST['field_thumb2_h']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail_2']) ? form_sanitizer($_POST['field_thumb2_h'], '', 'field_thumb2_h') : '',
                        'field_delete_original'    => isset($_POST['field_delete_original']) && $this->field_data['field_upload_type'] == 'image' ? 1 : 0
                    ];
                }
            }

            if (!$this->field_data['field_order']) {
                $this->field_data['field_order'] = dbresult(dbquery("SELECT MAX(field_order) FROM ".$this->field_db." WHERE field_cat=:cat_id", [':cat_id' => $this->field_data['field_cat']]), 0) + 1;
            }

            if (fusion_safe()) {
                if (!empty($this->field_data['field_config'])) {
                    $this->field_data['field_config'] = fusion_encode($this->field_data['field_config']);
                }
                $this->createFields($this->field_data); // will redirect and refresh config
            }
        }

        echo "<div class='m-t-20'>";
        echo openform('fieldform', 'post', $form_action);
        echo form_button('save_field', $this->locale['fields_0488'], 'save', [
            'input_id' => "save_field2",
            'class'    => 'btn-primary m-b-20'
        ]);
        $disable_opts = [];
        foreach ($this->page_list as $index => $v) {
            $disable_opts[] = $index;
        }
        // ok the value generated needs to be parsed by quantum
        echo form_select_tree('field_cat', $this->locale['fields_0450'], $this->field_data['field_cat'], [
            'no_root'      => TRUE,
            'width'        => '100%',
            'disable_opts' => $disable_opts
        ], $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');

        echo self::quantumMultilocaleFields('field_title', $this->locale['fields_0451'], $this->field_data['field_title'], ['required' => 1]);

        echo form_text('field_name', $this->locale['fields_0453'], $this->field_data['field_name'],
            [
                'placeholder' => $this->locale['fields_0454'],
                'required'    => TRUE
            ]
        );

        if ($this->field_data['field_type'] == 'select') {
            echo form_select('field_options', $this->locale['fields_0455'], $this->field_data['field_options'],
                [
                    'required'    => TRUE,
                    'tags'        => TRUE,
                    'multiple'    => TRUE,
                    'width'       => '100%',
                    'inner_width' => '100%'
                ]
            );
        }

        if ($this->field_data['field_type'] == 'upload') {

            require_once(INCLUDES.'mimetypes_include.php');
            $file_type_list = [];
            $file_image_list = [];
            foreach (mimetypes() as $file_ext => $occ) {
                if (!in_array($file_ext, array_flip(img_mimetypes()))) {
                    $file_type_list[] = '.'.$file_ext;
                }
            }
            foreach (img_mimetypes() as $file_ext => $occ) {
                $file_image_list[] = '.'.$file_ext;
            }
            $calc_opts = [
                1       => $this->locale['fields_0490'],
                1024    => $this->locale['fields_0491'],
                1048576 => $this->locale['fields_0492']
            ];
            $calc_c = calculate_byte($this->field_data['field_config']['field_max_b']);
            $calc_b = $this->field_data['field_config']['field_max_b'] / $calc_c;
            $file_upload_type = [
                'file'  => $this->locale['fields_0456'],
                'image' => $this->locale['fields_0489']
            ];
            echo form_select('field_upload_type', $this->locale['fields_0457'], $this->field_data['field_config']['field_upload_type'], ["options" => $file_upload_type]);
            echo form_text('field_upload_path', $this->locale['fields_0458'], $this->field_data['field_config']['field_upload_path'],
                [
                    'placeholder' => $this->locale['fields_0459'],
                    'required'    => TRUE
                ]
            );
            echo "<label for='field_max_b'>".$this->locale['fields_0460']."</label><br/>";
            echo "<div class='row'>";
            echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>";
            echo form_text('field_max_b', '', $calc_b, [
                'class'    => 'm-b-0',
                'type'     => 'number',
                'required' => TRUE
            ]);
            echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>";
            echo form_select('field_calc', '', $calc_c, ['options' => $calc_opts, 'width' => '100%']);
            echo "</div></div>";
            // File Type
            echo "<div id='file_type'>";
            echo form_select('field_valid_file_ext', $this->locale['fields_0461'], $this->field_data['field_config']['field_valid_file_ext'],
                [
                    'options'     => $file_type_list,
                    'multiple'    => TRUE,
                    'tags'        => TRUE,
                    'required'    => TRUE,
                    'width'       => '100%',
                    'inner_width' => '100%'
                ]);
            echo "</div>";
            // Image Type
            echo "<div id='image_type'>";
            echo form_select('field_valid_image_ext', $this->locale['fields_0462'], $this->field_data['field_config']['field_valid_image_ext'],
                [
                    'options'  => $file_image_list,
                    'multiple' => TRUE,
                    'tags'     => TRUE,
                    'required' => TRUE
                ]);
            echo "<label>".$this->locale['fields_0463']."</label><br/>";
            echo "<div class='row'>";
            echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>";
            echo form_text('field_image_max_w', $this->locale['fields_0464'], $this->field_data['field_config']['field_image_max_w'], [
                'number'      => 1,
                'placeholder' => $this->locale['fields_0466'],
                'required'    => 1
            ]);
            echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>";
            echo form_text('field_image_max_h', $this->locale['fields_0465'], $this->field_data['field_config']['field_image_max_h'], [
                'number'      => 1,
                'placeholder' => $this->locale['fields_0466'],
                'required'    => 1
            ]);
            echo "</div></div>";
            echo form_checkbox('field_thumbnail', $this->locale['fields_0467'], $this->field_data['field_config']['field_thumbnail'], ['reverse_label' => TRUE]);
            echo "<div id='field_t1'>";
            echo form_text('field_thumb_upload_path', $this->locale['fields_0468'],
                $this->field_data['field_config']['field_thumb_upload_path'], [
                    'placeholder' => $this->locale['fields_0469'],
                    'required'    => 1
                ]);
            echo "<label>".$this->locale['fields_0470']."</label><br/>";
            echo "<div class='row'>";
            echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>";
            echo form_text('field_thumb_w', $this->locale['fields_0471'], $this->field_data['field_config']['field_thumb_w'], [
                'number'      => 1,
                'placeholder' => $this->locale['fields_0466'],
                'required'    => 1
            ]);
            echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>";
            echo form_text('field_thumb_h', $this->locale['fields_0472'], $this->field_data['field_config']['field_thumb_h'], [
                'number'      => 1,
                'placeholder' => $this->locale['fields_0466'],
                'required'    => 1
            ]);
            echo "</div></div>";
            echo "</div>";
            echo form_checkbox('field_thumbnail_2', $this->locale['fields_0473'], $this->field_data['field_config']['field_thumbnail_2'], ['reverse_label' => TRUE]);
            echo "<div id='field_t2'>";
            echo form_text('field_thumb2_upload_path', $this->locale['fields_0474'], $this->field_data['field_config']['field_thumb2_upload_path'],
                [
                    'placeholder' => $this->locale['fields_0469'],
                    'required'    => TRUE,
                ]);
            echo "<label>".$this->locale['fields_0475']."</label><br/>";
            echo "<div class='row'>";
            echo "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>";
            echo form_text('field_thumb2_w', $this->locale['fields_0476'], $this->field_data['field_config']['field_thumb2_w'], [
                'type'        => 'number',
                'placeholder' => $this->locale['fields_0466'],
                'required'    => TRUE,
            ]);
            echo "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>";/**/
            echo form_text('field_thumb2_h', $this->locale['fields_0477'], $this->field_data['field_config']['field_thumb2_h'], [
                'type'        => 'number',
                'placeholder' => $this->locale['fields_0466'],
                'required'    => TRUE
            ]);
            echo "</div></div>";
            echo "</div>";
            echo form_checkbox('field_delete_original', $this->locale['fields_0478'], $this->field_data['field_config']['field_delete_original'], ['reverse_label' => TRUE]);
            echo "</div>";
            add_to_jquery("
            if ($('#field_upload_type').select2().val() == 'image') {
                $('#image_type').show();
                $('#file_type').hide();
            } else {
                $('#image_type').hide();
                $('#file_type').show();
            }
            $('#field_upload_type').bind('change', function() {
                if ($(this).select2().val() == 'image') {
                $('#image_type').show();
                $('#file_type').hide();
                } else {
                $('#image_type').hide();
                $('#file_type').show();
                }
            });
            // thumbnail
            $('#field_thumbnail').is(':checked') ? $('#field_t1').show() : $('#field_t1').hide();
            $('#field_thumbnail').bind('click', function() {
                $(this).is(':checked') ? $('#field_t1').show() : $('#field_t1').hide();
            });
            // thumbnail 2
            $('#field_thumbnail_2').is(':checked') ? $('#field_t2').show() : $('#field_t2').hide();
            $('#field_thumbnail_2').bind('click', function() {
                $(this).is(':checked') ? $('#field_t2').show() : $('#field_t2').hide();
            });
            ");
        } else {
            // @todo add config for textarea
            if ($this->field_data['field_type'] !== 'textarea') {
                echo form_text('field_default', $this->locale['fields_0480'], $this->field_data['field_default']);
            }
        }

        echo form_text('field_error', $this->locale['fields_0481'], $this->field_data['field_error']);
        echo form_checkbox('field_required', $this->locale['fields_0482'], $this->field_data['field_required'], ['reverse_label' => TRUE]);
        echo form_checkbox('field_log', $this->locale['fields_0483'], $this->field_data['field_log'], ['reverse_label' => TRUE]);
        echo form_text('field_order', $this->locale['fields_0484'], $this->field_data['field_order'],
            ['type' => 'number', 'inner_width' => '120px']);
        echo form_checkbox('field_registration', $this->locale['fields_0485'], $this->field_data['field_registration'], ['reverse_label' => TRUE]);
        echo form_hidden('add_field', '', $this->field_data['field_type']);
        echo form_hidden('field_id', '', $this->field_data['field_id']);
        echo form_button('save_field', $this->locale['fields_0488'], 'save', ['class' => 'btn-sm btn-primary']);
        echo closeform();
        echo "</div>";
    }

    /**
     * Field creation
     *
     * @param array  $data
     * @param string $type
     */
    private function createFields($data, $type = 'dynamics') {
        $aidlink = fusion_get_aidlink();

        // Build a field Attr
        $field_attr = '';
        if ($type == 'dynamics') {
            $field_attr = $this->dynamicsFieldInfo($data['field_type'], $data['field_default']);
        } else if ($type == 'module') {
            $field_attr = $this->user_field_dbinfo;
        }
        $max_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".$this->field_db." WHERE field_cat='".$data['field_cat']."'"),
                0) + 1;
        if ($data['field_order'] == 0 or $data['field_order'] > $max_order) {
            $data['field_order'] = $max_order;
        }

        if (self::validateField($data['field_id'])) {

            if ($this->debug) {
                print_p('Update Mode');
            }
            // update
            // Alter $this->field_db table - change and modify column.
            $field_query = "SELECT uf.*, cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                FROM ".$this->field_db." uf
                LEFT JOIN ".$this->category_db." cat ON (cat.field_cat_id = uf.field_cat)
                LEFT JOIN ".$this->category_db." root ON (cat.field_parent = root.field_cat_id)
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
                FROM ".$this->category_db." cat
                LEFT JOIN ".$this->category_db." root ON cat.field_parent = root.field_cat_id
                WHERE cat.field_cat_id='".intval($data['field_cat'])."'
                ");

                $newRows = [];
                if (dbrows($new_result) > 0) {
                    $newRows = dbarray($new_result);
                    $new_table = $newRows['field_cat_db'] ? DB_PREFIX.$newRows['field_cat_db'] : DB_USERS;
                } else {
                    $new_table = DB_USERS;
                }

                if ($this->debug) {
                    // Old Table Information
                    print_p('Old table information - ');
                    print_p($oldRows);

                    // New Table Information
                    print_p('New table information - ');
                    print_p($newRows);

                    print_p($data['field_cat']);
                    print_p($oldRows['field_cat']);
                }

                if ($data['field_cat'] != $oldRows['field_cat']) { // old and new mismatch - move to another category and possibly a new table.
                    // Fork #1 - Update on new table
                    if ($this->debug)
                        print_p('Fork No.1 - Update Field on a different table');
                    /**
                     * Drop column on old table and Create column on new table
                     *
                     * @todo: Improvements: need to move the whole column along with data instead of just dropping and creating new
                     */
                    if ($new_table != $old_table) {
                        $new_table_columns = fieldgenerator($new_table);
                        if (!$this->debug) {
                            if (!in_array($data['field_name'], $new_table_columns)) {
                                // this is new database check, if not exist, then add the column
                                //self::add_column($new_table, $data['field_name'], $field_attr);
                                self::move_column($old_table, $new_table, $data['field_name']);
                                self::drop_column($old_table, $oldRows['field_name']);
                                if (fusion_safe()) {
                                    // sort the fields. if 2, greater than 2 all +1 on the new category
                                    dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                                    // since change table. fix all which is greater than link order.
                                    dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                                }
                            } else {
                                fusion_stop('Column conflict. There are columns on '.$old_table.' existed in '.$new_table);
                            }
                        } else {
                            // DEBUG MODE
                            if (!in_array($data['field_name'], $new_table_columns)) {
                                print_p("Move ".$data['field_name']." from ".$old_table." to ".$new_table);
                                print_p("Dropping column ".$oldRows['field_name']." on ".$old_table);
                                print_p("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                                // since change table. fix all which is greater than link order.
                                print_p("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                            } else {
                                print_p('Column conflict. There are columns on '.$old_table.' existed in '.$new_table);
                            }
                        }
                    } else {
                        if (fusion_safe()) {
                            dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                            if (dbcount("(field_id)", $this->field_db)) {
                                dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                            }
                        }
                    }
                } else {
                    // same table.
                    // check if same title.
                    // if not same, change column name.
                    if ($this->debug) {
                        print_p('Fork No.2 - Update Field on the same table');
                    }

                    if ($data['field_name'] != $oldRows['field_name']) {
                        // not same as old record on dbcolumn
                        // Check for possible duplicates in the new field name
                        if (!in_array($data['field_name'], $old_table_columns)) {
                            if (!$this->debug) {
                                self::rename_column($old_table, $oldRows['field_name'], $data['field_name'], $field_attr);
                            } else {
                                print_p('Renaming column '.$oldRows['field_name'].' on '.$old_table.' to '.$data['field_name'].' with attributes of '.$field_attr);
                            }
                        } else {

                            fusion_stop(sprintf($this->locale['fields_0104'], "($new_table)"));
                        }
                    }
                    if (!$this->debug) {
                        if (fusion_safe()) {
                            // make ordering of the same table.
                            if ($data['field_order'] > $oldRows['field_order']) {
                                dbquery("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > ".$oldRows['field_order']." AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                            } else if ($data['field_order'] < $oldRows['field_order']) {
                                dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order < ".$oldRows['field_order']." AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                            }
                        }
                    } else {
                        print_p("Old field order is ".$oldRows['field_order']);
                        print_p("New field order is ".$data['field_order']);
                        if ($data['field_order'] > $oldRows['field_order']) {
                            print_p("UPDATE ".$this->field_db." SET field_order=field_order-1 WHERE field_order > '".$oldRows['field_order']."' AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        } else {
                            print_p("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order < '".$oldRows['field_order']."' AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        }
                    }
                }

                if (!$this->debug) {
                    if (fusion_safe()) {
                        dbquery_insert($this->field_db, $data, 'update');
                        addnotice('success', $this->locale['field_0203']);
                        redirect(FUSION_SELF.$aidlink);
                    }
                } else {
                    print_p($data);
                }

            } else {
                fusion_stop($this->locale['fields_0105']);
            }

        } else {
            if ($this->debug) {
                print_p('Save Mode');
            }
            // Alter $this->field_db table - add column.
            $cresult = dbquery("SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                                FROM ".$this->category_db." cat
                                LEFT JOIN ".$this->category_db." root ON (cat.field_parent = root.field_cat_id)
                                WHERE cat.field_cat_id='".$data['field_cat']."'");
            if (dbrows($cresult) > 0) {
                $cat_data = dbarray($cresult);
                $new_table = $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS;
                $field_arrays = fieldgenerator($new_table);
                if (!in_array($data['field_name'], $field_arrays)) { // safe to execute alter.
                    if (!$this->debug && !empty($data['field_name'])) {
                        self::add_column($new_table, $data['field_name'], $field_attr);
                    } else {
                        if ($this->debug) {
                            print_p("ALTER TABLE ".$new_table." ADD ".$data['field_name']." ".$field_attr);
                        }
                    }
                } else {
                    fusion_stop($this->locale['fields_0106']);
                }
                // ordering
                if (!$this->debug) {
                    if (fusion_safe()) {
                        dbquery("UPDATE ".$this->field_db." SET field_order=field_order+1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        dbquery_insert($this->field_db, $data, 'save');
                        addnotice('success', $this->locale['field_0204']);
                        redirect(FUSION_SELF.$aidlink);
                    }
                } else {
                    print_p($data);
                }
            } else {
                fusion_stop($this->locale['fields_0107']);
            }
        }
    }

    /**
     * Single array output match against $db - use get_structureData before to populate $fields
     */
    private function dynamicsFieldInfo($type, $default_value) {
        $info = [
            'textbox'     => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'select'      => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'textarea'    => "TEXT NOT NULL",
            'tags'        => "TEXT NOT NULL",
            'contact'     => "VARCHAR(100) NOT NULL DEFAULT '".$default_value."'",
            'checkbox'    => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'toggle'      => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'datepicker'  => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'location'    => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
            'colorpicker' => "VARCHAR(10) NOT NULL DEFAULT '".$default_value."'",
            'upload'      => "VARCHAR(100) NOT NULL DEFAULT '".$default_value."'",
            'hidden'      => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
            'address'     => "TEXT NOT NULL",
            'number'      => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'email'       => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'url'         => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
        ];

        return $info[$type];
    }

    /**
     * Modules Form
     */
    private function displayModuleForm() {
        $aidlink = fusion_get_aidlink();

        $form_action = FUSION_SELF.$aidlink;
        $user_field_name = '';
        $user_field_api_version = '';
        $user_field_desc = '';
        $user_field_dbname = '';
        $user_field_dbinfo = '';

        if ($this->module_debug == TRUE) {
            $this->debug = TRUE;
        }

        if (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id'])) {
            $form_action .= "&amp;action=".$_GET['action']."&amp;module_id=".$_GET['module_id'];
            $result = dbquery("SELECT * FROM ".$this->field_db." WHERE field_id=:mid", [':mid' => intval($_GET['module_id'])]);
            if (dbrows($result) > 0) {
                $this->field_data = dbarray($result);
                if ($this->debug) {
                    print_p('Old Data');
                    print_p($this->field_data);
                }
            } else {
                addnotice('info', $this->locale['field_0205']);
                redirect(FUSION_SELF.$aidlink);
            }
        }

        $this->field_data['add_module'] = isset($_POST['add_module']) ? stripinput($_POST['add_module']) : $this->field_data['field_name'];

        /*
         * Loads the relevant plugin files
         */
        $plugin_file_found = FALSE;
        $plugin_folder = '';
        $folder = '';
        if (is_array($this->plugin_folder) && !empty($this->plugin_folder)) {
            foreach ($this->plugin_folder as $plugin_folder) {
                $plugin_path = (rtrim($plugin_folder, '/').'/').$this->field_data['add_module'].'_include_var.php';
                $plugin_locale_path = (rtrim($this->plugin_locale_folder, '/').'/').$this->field_data['add_module'].'.php';

                if (is_file($plugin_locale_path) && is_file($plugin_path)) {

                    $plugin_file_found = TRUE;

                    $locale = fusion_get_locale('', $plugin_locale_path);
                    include($plugin_path);

                    $this->user_field_dbinfo = $user_field_dbinfo;

                    if (!isset($user_field_dbinfo)) {

                        addnotice('info', $this->locale['fields_0602']);

                    }

                    break;
                } else {
                    // Infusions
                    $folder_list = makefilelist($plugin_folder, '.|..|index.php', TRUE, 'folders');
                    if (!empty($folder_list)) {
                        // attempt to search.
                        foreach ($folder_list as $folder) {
                            if (file_exists($plugin_folder.$folder.'/user_fields/'.$this->field_data['add_module'].'_include.php') && file_exists($plugin_folder.$folder.'/locale/'.LANGUAGE.'/'.$this->field_data['add_module'].'.php')) {
                                $locale = fusion_get_locale('', $plugin_folder.$folder.'/locale/'.LANGUAGE.'/'.$this->field_data['add_module'].'.php');
                                include($plugin_folder.$folder.'/user_fields/'.$this->field_data['add_module'].'_include_var.php');
                                $this->user_field_dbinfo = $user_field_dbinfo;
                                if (!isset($user_field_dbinfo)) {
                                    addnotice('info', $this->locale['fields_0602']);
                                    redirect(FUSION_REQUEST);
                                }
                                $plugin_file_found = TRUE;
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            // When global plugin folder is not an array.
            $plugin_language_path = rtrim($plugin_folder, '/').'/'.$folder."/locale/".LANGUAGE."/".$this->field_data["add_module"].".php";
            $plugin_path = rtrim($plugin_folder, '/').'/'.$folder."/user_fields/".$this->field_data["add_module"]."_include_var.php";

            if (file_exists($plugin_language_path) && file_exists($plugin_path)) {
                $locale = fusion_get_locale('', $plugin_folder.$folder.'/locale/'.LANGUAGE.'/'.$this->field_data['add_module'].'.php');
                include($plugin_folder.$folder.'/user_fields/'.$this->field_data['add_module'].'_include_var.php');
                $this->user_field_dbinfo = $user_field_dbinfo;
                if (!isset($user_field_dbinfo)) {
                    addnotice('info', $this->locale['fields_0602']);
                    redirect(FUSION_REQUEST);
                }
                $plugin_file_found = TRUE;
            }
        }

        if ($plugin_file_found === FALSE) {
            fusion_stop($this->locale['fields_0109']);
        }

        /*
        if (file_exists($this->plugin_locale_folder.stripinput($this->field_data['add_module']).".php") && file_exists($this->plugin_folder.stripinput($this->field_data['add_module'])."_include_var.php")) {
            include $this->plugin_locale_folder.stripinput($this->field_data['add_module']).".php";
            include $this->plugin_folder.stripinput($this->field_data['add_module'])."_include_var.php";

            $this->user_field_dbinfo = $user_field_dbinfo;
            if (!isset($user_field_dbinfo)) {
                addNotice('info', $this->locale['fields_0602']);
                redirect(FUSION_REQUEST);
            }
        } else {
            fusion_stop();
            addNotice('danger', $this->locale['fields_0109']);
        }*/

        if (isset($_POST['enable'])) {
            $this->field_data = [
                'add_module'         => isset($_POST['add_module']) ? form_sanitizer($_POST['add_module']) : $this->field_data['field_name'],
                'field_type'         => 'file',
                'field_id'           => (isset($_POST['field_id']) ? form_sanitizer($_POST['field_id'], '', 'field_id') : isset($_GET['module_id']) && isnum($_GET['module_id'])) ? $_GET['module_id'] : 0,
                'field_title'        => form_sanitizer($_POST['field_title'], '', 'field_title'),
                'field_name'         => form_sanitizer($_POST['field_name'], '', 'field_name'),
                'field_cat'          => form_sanitizer($_POST['field_cat'], '', 'field_cat'),
                'field_default'      => form_sanitizer($_POST['field_default'], '', 'field_default'),
                'field_error'        => form_sanitizer($_POST['field_error'], '', 'field_error'),
                'field_required'     => isset($_POST['field_required']) ? 1 : 0,
                'field_registration' => isset($_POST['field_registration']) ? 1 : 0,
                'field_log'          => isset($_POST['field_log']) ? 1 : 0,
                'field_order'        => form_sanitizer($_POST['field_order'], '0', 'field_order'),
                'field_options'      => '',
                'field_config'       => ''
            ];
            $this->field_data['field_name'] = str_replace(' ', '_', $this->field_data['field_name']); // make sure no space.
            if (!$this->field_data['field_order']) {
                $this->field_data['field_order'] = dbresult(dbquery("SELECT MAX(field_order) FROM ".$this->field_db." WHERE field_cat=:cat_id", [':cat_id' => $this->field_data['field_cat']]), 0) + 1;
            }
            $this->createFields($this->field_data, 'module');
        }

        echo "<div class='m-t-20'>";
        echo openform('fieldform', 'post', $form_action);
        echo "<h4 class='m-b-5'>".$user_field_name."</h4>";
        if (!empty($user_field_desc)) {
            echo "<p>".$user_field_desc."</p>";
        }
        echo "<hr/>";
        echo "<div class='well'>";
        echo "<p class='strong'>".$this->locale['fields_0400']."</p>";
        echo "<span class='text-dark strong'>".$this->locale['fields_0401']."</span> ".(!empty($user_field_api_version) ? $user_field_api_version : $this->locale['fields_0402'])."<br/>";
        echo "<span class='text-dark strong'>".$this->locale['fields_0403']."</span>".(!empty($user_field_dbname) ? "<br/>".$user_field_dbname : '<br/>'.$this->locale['fields_0404'])."<br/>";
        echo "<span class='text-dark strong'>".$this->locale['fields_0405']."</span>".(!empty($user_field_dbinfo) ? "<br/>".$user_field_dbinfo : '<br/>'.$this->locale['fields_0406'])."<br/>";
        echo "<span class='text-dark strong'>".$this->locale['fields_0407']."</span>".(!empty($user_field_desc) ? "<br/>".$user_field_desc : '')."<br/>";
        echo "</div>";
        echo "<hr/>";

        echo form_select_tree('field_cat', $this->locale['fields_0410'], $this->field_data['field_cat'], [
            'no_root'      => 1,
            'disable_opts' => array_keys($this->page_list),
        ], $this->category_db, 'field_cat_name', 'field_cat_id', 'field_parent');

        echo form_text('field_order', $this->locale['fields_0414'], $this->field_data['field_order'], ['type' => 'number', 'inner_width' => '100px']);

        if (!empty($user_field_dbinfo)) {
            if (version_compare($user_field_api_version, "1.01.00", ">=")) {
                echo form_checkbox('field_required', $this->locale['fields_0411'], $this->field_data['field_required'], ['reverse_label' => TRUE]);
            }
            if (version_compare($user_field_api_version, "1.01.00", ">=")) {
                echo form_checkbox('field_log', $this->locale['fields_0412'], $this->field_data['field_log'], ['reverse_label' => TRUE]);
            }
            echo form_checkbox('field_registration', $this->locale['fields_0413'], $this->field_data['field_registration'], ['reverse_label' => TRUE]);
        }
        echo form_hidden('add_module', '', $this->field_data['add_module']);
        echo form_hidden('field_name', '', $user_field_dbname);
        echo form_hidden('field_title', '', $user_field_name);
        echo form_hidden('field_default', '', isset($user_field_default) ? $user_field_default : '');
        echo form_hidden('field_options', '', isset($user_field_options) ? $user_field_options : '');
        echo form_hidden('field_error', '', isset($user_field_error) ? $user_field_error : '');
        echo form_hidden('field_config', '', isset($user_field_config) ? $user_field_config : '');
        echo form_hidden('field_id', '', $this->field_data['field_id']);
        echo form_button('enable',
            ($this->field_data['field_id'] ? $this->locale['fields_0415'] : $this->locale['fields_0416']),
            ($this->field_data['field_id'] ? $this->locale['fields_0415'] : $this->locale['fields_0416']),
            ['class' => 'btn-primary']);
        echo closeform();
        echo "</div>";

    }

    /**
     * @param array $data
     */
    public function quantumInsert(array $data = []) {
        $quantum_fields = [];
        $infinity_ref = [];
        // bug fix: to get only the relevant fields on specific page.
        $field_list = flatten_array($this->fields);
        // to generate $infinity_ref and $quantum_fields as reference and validate the $_POST input value.
        foreach ($field_list as $field_data) {
            if ($field_data['field_parent'] == $this->input_page) {
                $target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : DB_USERS;
                $target_index = !empty($field_data['field_cat_index']) ? $field_data['field_cat_index'] : 'user_id';
                $index_value = isset($_POST[$target_index]) ? form_sanitizer($_POST[$target_index],
                    0) : $data[$target_index];
                // create reference array
                $infinity_ref[$target_database] = ['index' => $target_index, 'value' => $index_value];
                if (isset($_POST[$field_data['field_name']])) {
                    $quantum_fields[$target_database][$field_data['field_name']] = form_sanitizer($_POST[$field_data['field_name']],
                        $field_data['field_default'],
                        $field_data['field_name']);
                } else {
                    $quantum_fields[$target_database][$field_data['field_name']] = isset($data['field_name']) ? $data[$field_data['field_name']] : '';
                }
            }
        }
        if (!empty($quantum_fields)) {
            $temp_table = '';
            foreach ($quantum_fields as $_dbname => $_field_values) {
                $merged_data = [];
                $merged_data += $_field_values;
                $merged_data += $data; // appends all other necessary values to fill up the entire table values.
                if ($temp_table !== $_dbname) { // if $temp_table is different. check if table exist. run once if pass
                    $merged_data += [$infinity_ref[$_dbname]['index'] => $infinity_ref[$_dbname]['value']]; // Primary Key and Value.
                    // ensure nothing is missing. this might be overkill. I would shut it down if not neccessary to lighten the load by 1-2 uncessary query.
                    $result = dbquery("SELECT * FROM ".$_dbname." WHERE ".$infinity_ref[$_dbname]['index']." = '".$infinity_ref[$_dbname]['value']."'");
                    if (dbrows($result) > 0) {
                        $merged_data += dbarray($result);
                    }
                }
                dbquery_insert($_dbname, $merged_data, 'update');
            }
        }
    }

    /**
     * Return sanitized post values of input fields
     *
     * @param string $db
     * @param string $primary_key
     * @param bool   $callback_data
     *
     * @return array
     */
    public function returnFieldsInput($db, $primary_key, $callback_data = FALSE) {
        $output_fields = [];
        //print_P($this->field_cat_index);

        // default section id
        $indexes = array_reverse($this->field_cat_index[0]);
        $sec_id = array_pop($indexes);

        $cur_section = (isset($_GET['section']) && in_array($_GET['section'], array_values($this->field_cat_index[0])) ? (int)$_GET['section'] : $sec_id);
        // get current section categories
        $cur_cid = array_values($this->field_cat_index[$cur_section]);
        $fields = [];
        foreach ($cur_cid as $category_id) {
            if (isset($this->fields[$category_id])) {
                $fields[] = $this->fields[$category_id];
            }
        }

        // selected fields to push
        $field = flatten_array($fields);
        if ($callback_data == TRUE) {
            $output_fields[$db] = $this->callback_data;
        }

        foreach ($field as $field_data) {
            $target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;
            $col_name = !empty($field_data['field_cat_index']) ? $field_data['field_cat_index'] : $primary_key;
            // Find index primary key value
            $primaryKeyVal = isset($_POST[$col_name]) ? form_sanitizer($_POST[$col_name], $field_data['field_default'], $col_name) : '';
            if (!isset($output_fields[$target_database][$col_name])) {
                $output_fields[$target_database][$col_name] = $primaryKeyVal;
            }

            $output_fields[$target_database][$field_data['field_name']] = $field_data['field_default'];
            // Set input as default if posted but blank
            if (isset($_POST[$field_data["field_name"]])) {
                $output_fields[$target_database][$field_data['field_name']] = form_sanitizer($_POST[$field_data["field_name"]], $field_data['field_default'], $field_data['field_name']);
            }
        }

        $this->output_fields = $output_fields;

        return $this->output_fields;
    }

    /**
     * Logs the user actions
     *
     * @param string $db
     * @param string $primary_key
     */
    public function logUserAction($db, $primary_key) {
        if (fusion_safe()) {
            $field = flatten_array($this->fields);
            //$output_fields[$db] = $this->callback_data;
            foreach ($field as $field_data) {
                $target_db = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;
                $col_name = !empty($field_data['field_cat_index']) ? $field_data['field_cat_index'] : $primary_key;
                $index_value = isset($_POST[$col_name]) ? form_sanitizer($_POST[$col_name], 0) : '';
                $old_cache = isset($this->callback_data[$field_data['field_name']]) ? $this->callback_data[$field_data['field_name']] : '';
                $new_val = isset($this->output_fields[$target_db][$field_data['field_name']]) ? $this->output_fields[$target_db][$field_data['field_name']] : '';
                if ($field_data['field_log'] && $new_val && $new_val != $old_cache) {
                    save_user_log($index_value, $field_data['field_name'], $new_val, $old_cache);
                }
            }
        }
    }

    /**
     * Parse the correct label language. Requires to be serialized $value.
     *
     * @param string $value Serialized
     *
     * @return string
     * @deprecated use parseLabel()
     */
    public static function parse_label($value) {
        return self::parseLabel($value);
    }

    /**
     * Multiple locale fields input
     *
     * @param string $input_name
     * @param string $title
     * @param mixed  $input_value
     * @param array  $options
     *
     * @return string
     *
     * @deprecated use quantumMultilocaleFields()
     */
    public static function quantum_multilocale_fields($input_name, $title, $input_value, array $options = []) {
        return self::quantumMultilocaleFields($input_name, $title, $input_value, $options);
    }
}
