<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: QuantumFields.inc
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

use PHPFusion\Interfaces\TableSDK;

class UserFieldsQuantum extends \SqlHandler {
    /**
     * Set the Quantum System Fields Page Title
     */
    protected $title = '';
    protected $is_admin_panel = FALSE;
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
     * @var string - plugin_locale_folder (LOCALE.LOCALESET."user_fields/")
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
    protected $dom_debug = FALSE;
    // System Internals
    private $input_page = 1;
    private $field_max_rows = 0;
    private $locale = [];
    private $page_list = [];
    protected $page = [];
    private $enabled_fields = [];
    private $modules = []; // replacement for $get_available_modules
    private $available_field_info = [];
    public $registration = FALSE;  // True for turning the output to show only registration fields
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
    // private $get_available_modules = [];
    // private $user_field_dbinfo = '';
    private $field_cat_index = [];
    private static $module_list = [];
    private static $module_list_var = [];

    ### Setters ###

    public static function fusion_getlocale($data, $input_name) {
        $language_opts = fusion_get_enabled_languages();

        if (isset($_POST[$input_name])) {
            return self::serialize_fields($input_name);
        } else {
            if (isset($data[$input_name])) {
                if (self::is_serialized($data[$input_name])) {
                    return unserialize($data[$input_name]);
                } else {
                    $value = "";
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
     * @param $input_name
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function serialize_fields($input_name) {
        if (isset($_POST[$input_name])) {
            $field_var = [];
            foreach ($_POST[$input_name] as $language => $value) {
                $field_var[$language] = form_sanitizer($value, '');
            }

            return serialize($field_var);
        }

        return FALSE;
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
     * @param $locale
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Give your Quantum based system a name. Will add to breadcrumbs if available.
     *
     * @param string $value
     */
    public function setTitle($value) {
        $this->title = $value;
    }

    ### Getters ####

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

    /**
     * @return array
     */
    public function getCatList() {
        return $this->cat_list;
    }

    /**
     * Get results from running load_structure
     *
     * @param null $key
     *
     * @return array
     */
    public function getFields($key = NULL) {
        return (isset($this->fields[$key])) ? (array)$this->fields[$key] : (array)$this->fields;
    }

    ### Loaders ####
    /* Read into serialized field label and returns the value */

    public function displayQuantumAdmin() {
        pageAccess($this->admin_rights);
        define('IN_QUANTUM', TRUE);
        $refs = get('ref');
        $refs = $refs && in_array($refs, ['preference', 'security']) ? $refs : 'public';
        $this->is_admin_panel = TRUE;

        $this->load_fields(); // return fields

        $this->cacheModuleFiles();
        $this->cacheModulePlugins();

        if ($this->title) {
            add_breadcrumb(['link' => ADMIN.'user_fields.php'.fusion_get_aidlink(), 'title' => $this->title]);
            add_to_title($this->title.' | ');
        }

        if ($this->method == 'input') {

            $this->load_field_cats(); // return cat

            $this->_move_fields();
            $this->_delete_category();
            $this->_delete_fields();
        }

        $this->view($refs);
    }

    /**
     * Type can be common, preference, security
     *
     * @param string $folder
     */
    public function load_fields($folder = 'public') {

        $this->plugin_folder = [INCLUDES.'user_fields/'.$folder.'/', INFUSIONS];

        $this->page = dbquery_tree_full(DB_USER_FIELD_CATS, "field_cat_id", "field_parent", "ORDER BY field_cat_order ASC");
        $param = [];
        $cond = '';

        if ($this->is_admin_panel === FALSE) {

            $section = get('section', FILTER_VALIDATE_INT);

            if (!$section || !isset($this->page[$section])) {
                $section = 1;
            }

            $param = [
                ':index00' => (int)$section,
                ':index01' => (int)$section,
            ];
            $cond = "WHERE (cat.field_cat_id=:index00 OR root.field_cat_id=:index01)";

            // for registration
            if ($this->registration === TRUE) {
                $cond .= ' AND field.field_registration=:fieldreg';
                $param += [':fieldreg' => 1];
            }

            switch ($folder) {
                default:
                case 'public':
                    $cond .= ' AND field_section=:type00';
                    $param += [':type00' => 'public'];
                    break;
                case 'security':
                    $cond .= ' AND field_section=:type00';
                    $param += [':type00' => 'security'];
                    break;
                case 'preference':
                    $cond .= ' AND field_section !=:type00';
                    $param += [':type00' => 'preference'];
                    break;
            }
        }

        $sql = "SELECT field.*, cat.field_cat_id, cat.field_cat_name, cat.field_parent, cat.field_cat_class, root.field_cat_id 'page_id', root.field_cat_name 'page_name', root.field_cat_db, root.field_cat_index
        FROM ".DB_USER_FIELDS." field
        LEFT JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
        LEFT JOIN ".DB_USER_FIELD_CATS." root ON (root.field_cat_id = cat.field_parent)
        $cond
        ORDER BY root.field_cat_order ASC, cat.field_cat_order ASC, field.field_order ASC
        ";

        $result = dbquery($sql, $param);
        if ($this->field_max_rows = dbrows($result)) {
            while ($data = dbarray($result)) {
                $this->fields[$data['field_cat']][$data['field_id']] = $data;
            }
        }
    }

    ### Validators ###
    /* Move Fields Order - Up and Down */

    /**
     * Returns $this->page_list and $this->cat_list
     */
    public function load_field_cats() {
        // Load Field Cats
        if (empty($this->page_list) && empty($this->cat_list)) {
            $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." ORDER BY field_cat_order ASC");
            if (dbrows($result) > 0) {
                while ($list_data = dbarray($result)) {
                    if ($list_data['field_parent'] != '0') {
                        $this->cat_list[$list_data['field_cat_id']] = $list_data['field_cat_name'];
                    } else {
                        $this->page_list[$list_data['field_cat_id']] = self::parse_label($list_data['field_cat_name']);
                    }
                }
            }
        }
        if (empty($this->field_cat_index)) {
            $this->field_cat_index = dbquery_tree(DB_USER_FIELD_CATS, 'field_cat_id', 'field_parent');
        }
    }


    /* Execution of delete fields */

    /**
     * Parse the correct label language. Requires serialized $value.
     *
     * @param $value - Serialized
     *
     * @return string
     *               NOTE: If your field does not parse properly, check your column length. Set it to TEXT NOT NULL.
     */
    public static function parse_label($value) {
        if (self::is_serialized($value)) {
            $value = @unserialize($value); // if anyone can give me a @unserialize($value) withotu E_NOTICE. I'll drop is_serialized function.

            return (string)(isset($value[LANGUAGE])) ? $value[LANGUAGE] : '';
        } else {
            return (string)$value;
        }
    }

    public static function is_serialized($value, &$result = NULL) {
        // Bit of a give away this one
        if (!is_string($value)) {
            return FALSE;
        }
        // Serialized FALSE, return TRUE. unserialize() returns FALSE on an
        // invalid string or it could return FALSE if the string is serialized
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
                case 'b':
                case 'i':
                case 'd':
                    // This looks odd but it is quicker than isset()ing
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
        if (($result = @unserialize($value)) === FALSE) {
            $result = NULL;

            return FALSE;
        }

        return TRUE;
    }

    private function _move_fields() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();

        if (isset($_GET['action']) && isset($_GET['order']) && isnum($_GET['order']) && isset($_GET['parent_id']) && isnum($_GET['parent_id'])) {
            if (isset($_GET['cat_id']) && isnum($_GET['cat_id']) && ($_GET['action'] == 'cmu' or $_GET['action'] == 'cmd')) {
                $data = [];
                $result = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent='".intval($_GET['parent_id'])."' AND field_cat_order='".intval($_GET['order'])."'"); // more than 1.
                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                }
                if ($_GET['action'] == 'cmu') { // category move up.
                    if (!$this->debug && !empty($data['field_cat_id'])) {
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$data['field_cat_id']."'");
                    }
                    if (!$this->debug) {
                        // sync the whole order
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$_GET['cat_id']."'");
                    }
                } else if ($_GET['action'] == 'cmd') {
                    if (!$this->debug && !empty($data['field_cat_id'])) {
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id='".$data['field_cat_id']."'");
                    }
                    if (!$this->debug) {
                        dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id='".$_GET['cat_id']."'");
                    }
                }
                if (!$this->debug) {
                    $res = dbquery("SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:id ORDER BY field_cat_order ASC", [':id' => intval($_GET['parent_id'])]);
                    if (dbrows($res)) {
                        $i = 1;
                        while ($rows = dbarray($res)) {
                            dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=:order WHERE field_cat_id=:id", [
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
                $result = dbquery("SELECT field_id FROM ".DB_USER_FIELDS." WHERE field_cat='".intval($_GET['parent_id'])."' AND field_order='".intval($_GET['order'])."'");
                if (dbrows($result) > 0) {
                    $data = dbarray($result);
                }
                if ($_GET['action'] == 'fmu') { // field move up.
                    if (!$this->debug) {
                        if (!empty($data['field_id'])) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id='".$data['field_id']."'");
                        }
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_id='".$_GET['field_id']."'");
                    } else {
                        print_p($this->locale['fields_0650'].$_GET['field_id'].str_replace('[FIELD_ID]', $data['field_id'], $this->locale['fields_0651']));
                    }
                } else if ($_GET['action'] == 'fmd') {
                    if (!$this->debug) {
                        if (!empty($data['field_id'])) {
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_id='".$data['field_id']."'");
                        }
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id='".$_GET['field_id']."'");
                    } else {
                        print_p($this->locale['fields_0650'].$_GET['field_id'].str_replace('[FIELD_ID]', $data['field_id'], $this->locale['fields_0652']));
                    }
                }
                if (!$this->debug) {
                    $res = dbquery("SELECT field_id, field_order FROM ".DB_USER_FIELDS." WHERE field_cat=:parent_id ORDER BY field_order ASC", [':parent_id' => ($_GET['parent_id'])]);
                    if (dbrows($res)) {
                        $i = 1;
                        while ($rows = dbarray($res)) {
                            print_p($rows);
                            dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=:order WHERE field_id=:id", [
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

    /* Outputs Quantum Admin Button Sets */
    private function _delete_category() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();
        $this->debug = FALSE;
        $data = [];

        if (isset($_POST['cancel'])) {
            redirect(FUSION_SELF.$aidlink);
        }

        if (isset($_GET['action']) && $_GET['action'] == 'cat_delete' && isset($_GET['cat_id']) && self::validate_fieldCat($_GET['cat_id'])) {

            // do action of the interior form
            if (isset($_POST['delete_cat'])) {

                // get root node
                $target_database = '';
                $field_list = [];

                if (isset($_POST['delete_subcat']) or isset($_POST['delete_field'])) {

                    if (in_array($_GET['cat_id'], $this->page_list)) {

                        // this is root.
                        $result = dbquery("
                                  SELECT field_cat_id, field_parent, field_cat_db FROM ".DB_USER_FIELD_CATS."
                                  WHERE field_cat_id='".intval($_GET['cat_id'])."'
                                  ");
                    } else {

                        // is is not a root.
                        $result = dbquery("
                        SELECT uf.field_cat_id, root.field_cat_db FROM ".DB_USER_FIELD_CATS." uf
                        LEFT JOIN ".DB_USER_FIELD_CATS." root ON uf.field_parent = root.field_cat_id
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
                    foreach ($this->page[$_GET['cat_id']] as $arr => $field_category) {

                        $result = dbquery("
                                  SELECT field_id, field_name FROM ".DB_USER_FIELDS."
                                  WHERE field_cat='".$field_category['field_cat_id']."'
                                  "); // find all child > 1

                        if (dbrows($result) > 0) {

                            while ($data = dbarray($result)) {
                                // remove column from db , and fields
                                if (in_array($data['field_name'], $field_list)) { // verify table integrity

                                    if ($this->debug) {
                                        print_p("DROP ".$data['field_name']." FROM ".$target_database);
                                        print_p("DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS);
                                    } else {

                                        dbquery("DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'");

                                        if (!empty($target_database) && !empty($data['field_name'])) {
                                            self::drop_column($target_database, $data['field_name']);
                                        }

                                    }

                                }

                                // remove category.
                                if ($this->debug) {
                                    print_p("DELETE ".$field_category['field_cat_id']." FROM ".DB_USER_FIELD_CATS);
                                } else {
                                    dbquery("DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$field_category['field_cat_id']."'");
                                }

                            } // end while

                        }
                    }

                } else if (isset($_POST['move_subcat']) && $_POST['move_subcat'] > 0) {

                    // When deletion to move subcategory
                    foreach ($this->page[$_GET['cat_id']] as $arr => $field_category) {
                        $new_parent = form_sanitizer($_POST['move_subcat'], 0, 'move_subcat');

                        if ($this->debug) {
                            print_p("MOVED ".$field_category['field_cat_id']." TO category ".$new_parent);
                            print_p("DELETE ".$_GET['cat_id']." FROM ".DB_USER_FIELD_CATS);
                        } else {

                            dbquery("UPDATE ".DB_USER_FIELD_CATS." SET field_parent='".$new_parent."' WHERE field_cat_id='".$field_category['field_cat_id']."'");
                        }
                    }

                } else if (isset($_POST['delete_field']) && isset($_GET['cat_id']) && isnum($_GET['cat_id'])) {
                    // Delete fields
                    $this->debug = FALSE;
                    if ($this->debug) {
                        print_p($this->locale['fields_0655']);
                    }
                    // Delete Fields - Bug with Isset errors
                    $result = dbquery("SELECT field_id, field_name FROM ".DB_USER_FIELDS." WHERE field_cat='".intval($_GET['cat_id'])."'");
                    if (dbrows($result) > 0) {
                        while ($data = dbarray($result)) {
                            if (in_array($data['field_name'], $field_list)) { // verify table integrity
                                if ($this->debug) {
                                    print_p("DROP ".$data['field_name']." FROM ".$target_database);
                                    print_p("DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS);
                                } else {
                                    $field_del_sql = "DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'";
                                    $field_count = $this->validate_field($data['field_id']);
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

                        addNotice('success', $this->locale['field_0200']);
                        redirect(FUSION_SELF.$aidlink);
                    }

                } // category deletion path 2
                else if (!isset($_POST['delete_field']) && isset($_POST['move_field']) && $_POST['move_field'] > 0) {
                    $rows = dbcount("(field_id)", DB_USER_FIELDS, "field_cat='".intval($_GET['cat_id'])."'");
                    if ($rows) {
                        $new_parent = form_sanitizer($_POST['move_field'], 0, 'move_field');
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_cat='".intval($new_parent)."' WHERE field_cat='".intval($_GET['cat_id'])."'");
                    }

                }
                // Delete the current category
                $delete_cat_sql = "DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".intval($_GET['cat_id'])."'";
                if ($this->debug) {
                    print_p($delete_cat_sql);
                } else {
                    dbquery($delete_cat_sql);
                    addNotice('success', $this->locale['field_0200']);
                    redirect(FUSION_SELF.$aidlink);
                }
            } else {
                // show interior form
                $field_list = [];
                $form_action = FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$_GET['cat_id'];
                $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$_GET['cat_id']."' OR field_cat_id='".get_hkey(DB_USER_FIELD_CATS,
                        "field_cat_id",
                        "field_parent",
                        $_GET['cat_id'])."'");
                if (dbrows($result) > 0) {
                    $data += dbarray($result);
                    // get field list - populate child fields of a category.
                    $result = dbquery("SELECT field_id, field_name, field_cat FROM ".DB_USER_FIELDS." WHERE field_cat='".intval($_GET['cat_id'])."'");
                    if (dbrows($result) > 0) {
                        // get field list.
                        while ($data = dbarray($result)) {
                            $field_list[$data['field_cat']][$data['field_id']] = $data['field_name'];
                        }
                    }
                    if (isset($this->page[$data['field_parent']]) or !empty($field_list) && $field_list[$_GET['cat_id']] > 0) {
                        ob_start();
                        echo openmodal("delete", $this->locale['fields_0313'], [
                            'class_dialog' => 'modal-lg modal-center',
                            'static'       => TRUE
                        ]);
                        echo openform('delete_cat_form', 'post', $form_action);
                        if (isset($this->page[$_GET['cat_id']])) {
                            echo "<div class='row'>\n";
                            echo "<div class='col-xs-12 col-sm-6'>\n<span class='strong'>".sprintf($this->locale['fields_0600'],
                                    count($this->page[$_GET['cat_id']]))."</span><br/>\n";
                            echo "<div class='alert alert-info m-t-10'>\n";
                            echo "<ol style='list-style:inherit !important; margin-bottom:0;'>\n";
                            foreach ($this->page[$_GET['cat_id']] as $arr => $field_category) {
                                echo "<li style='list-style-type:decimal;'>".self::parse_label($field_category['field_cat_name'])."</li>\n";
                            }
                            echo "</ol>\n";
                            echo "</div>\n";
                            echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                            $page_list = $this->page_list;
                            unset($page_list[$_GET['cat_id']]);
                            if (count($page_list) > 0) {
                                echo form_select('move_subcat', $this->locale['fields_0314'], '',
                                    ["options" => $page_list]);
                            }
                            echo form_checkbox('delete_subcat', $this->locale['fields_0315'],
                                count($page_list) < 1 ? TRUE : FALSE);
                            echo "</div></div>";
                        }
                        if (isset($field_list[$_GET['cat_id']])) {
                            echo "<div class='row'>\n";
                            echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n<span class='strong'>".sprintf($this->locale['fields_0601'],
                                    count($field_list[$_GET['cat_id']]))."</span><br/>\n";
                            echo "<div class='well strong m-t-10'>\n";
                            foreach ($field_list[$_GET['cat_id']] as $arr => $field) {
                                echo "- ".$field."<br/>\n";
                            }
                            echo "</div>\n";
                            echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                            $exclude_list[] = $_GET['cat_id'];
                            foreach ($this->page_list as $page_id => $page_name) {
                                $exclude_list[] = $page_id;
                            }
                            echo form_select_tree('move_field', $this->locale['fields_0316'], '', [
                                'no_root'      => 1,
                                'disable_opts' => $exclude_list
                            ], DB_USER_FIELD_CATS, 'field_cat_name', 'field_cat_id', 'field_parent');

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
                        addNotice('info', $this->locale['fields_0655'].'<br/>'.$this->locale['fields_0656']);
                    } else {
                        redirect(FUSION_SELF.$aidlink);
                    }
                }
            }
        }
    }

    ### Formatters ###
    private function validate_fieldCat($field_cat_id) {
        if (isnum($field_cat_id)) {
            return dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_id='".intval($field_cat_id)."'");
        }

        return FALSE;
    }

    private function validate_field($field_id) {
        if (isnum($field_id)) {
            return dbcount("(field_id)", DB_USER_FIELDS, "field_id='".intval($field_id)."'");
        }

        return FALSE;
    }

    /* The Current Stable PHP-Fusion Dynamics Module */
    private function _delete_fields() {
        $aidlink = fusion_get_aidlink();

        if (isset($_GET['action']) && $_GET['action'] == 'field_delete' && isset($_GET['field_id']) && self::validate_field($_GET['field_id'])) {
            $result = dbquery("SELECT field.field_id, field.field_cat, field.field_order, field.field_name, u.field_cat_id, u.field_parent, root.field_cat_db
            FROM ".DB_USER_FIELDS." field
            LEFT JOIN ".DB_USER_FIELD_CATS." u ON (field.field_cat=u.field_cat_id)
            LEFT JOIN ".DB_USER_FIELD_CATS." root ON (u.field_parent = root.field_cat_id)
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
                        dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        dbquery("DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'");
                    }

                } else {
                    // just delete the field
                    if (!$this->debug) {
                        dbquery("DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'");
                    } else {
                        print_p("DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS);
                    }
                }

                if (!$this->debug) {
                    addNotice('success', $this->locale['field_0201']);
                    redirect(FUSION_SELF.$aidlink);
                }

            } else {

                if (!$this->debug) {
                    addNotice('warning', $this->locale['field_0202']);
                    redirect(FUSION_SELF.$aidlink);
                } else {

                    print_p($this->locale['field_0202']);

                }

            }
        }
    }

    protected function cacheModulePlugins() {
        $this->locale = fusion_get_locale();
        $result = dbquery("SELECT field_id, field_name, field_cat, field_required, field_log, field_registration, field_order, field_type,
                    field_cat_name
                    FROM ".DB_USER_FIELDS." tuf
                    LEFT JOIN ".DB_USER_FIELD_CATS." tufc ON (tuf.field_cat = tufc.field_cat_id)
                    WHERE (field_type = 'file' OR field_cat=0)
                    ORDER BY field_cat_order ASC, field_order ASC");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $this->enabled_fields[] = $data['field_name'];
            }
        }

        if (!empty(self::$module_list_var)) {
            foreach(self::$module_list_var as $plugin_name => $plugin_var_file) {
                $user_field_name = '';
                $user_field_desc = '';
                $user_field_dbname = '';
                $user_field_group = '';
                $user_field_dbinfo = '';
                $user_field_author = '';
                $user_field_api_version = '';
                $user_field_image = '';

                require_once($plugin_var_file);

                $field_title = $plugin_name;
                if (!in_array($plugin_name, $this->enabled_fields)) {
                    $this->available_field_info[$plugin_name] = [
                        'title'       => $user_field_name,
                        'description' => $user_field_desc
                    ];
                }
                // API information
                $this->modules[$field_title] = [
                    'user_field_name'      => $user_field_name,
                    'user_field_dbname'    => $user_field_name,
                    'user_field_desc'      => $user_field_desc,
                    'user_field_dbname'    => $user_field_dbname,
                    'user_field_group'     => $user_field_group,
                    'user_field_dbinfo'    => $user_field_dbinfo,
                    'user_field_author'    => $user_field_author,
                    'user_field_version'   => $user_field_api_version,
                    'module_file_path'     => self::$module_list[$plugin_name],
                    'module_var_file_path' => $plugin_var_file,
                    'module_image' => $user_field_image,
                ];
                unset($field_name);
            }
        }
    }

    public function view($refs) {
        $action = get('action');
        switch ($refs) {
            default:
            case 'public':
                add_breadcrumb(['title' => 'Public Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public']);
                if ($action) {
                    switch ($action) {
                        case 'new':
                            $add_field = get('add_field');
                            $field_types = $this->get_dynamics_type();
                            if ($add_field && in_array($add_field, array_flip($field_types))) {
                                $title = "Add new ".$field_types[$add_field];
                                $modal = openmodal('dynamics', "<h4 class='m-0'>$title</h4>", ['static' => TRUE]);
                                $modal .= $this->quantum_dynamics_form();
                                $modal .= closemodal();
                                add_to_footer($modal);
                            }
                            $content = $this->view_modules('public');
                            break;
                        case 'field_edit':
                            $field_id = get('field_id', FILTER_VALIDATE_INT);
                            if ($field_id && isnum($field_id)) {
                                add_breadcrumb(['title' => 'Manage User Public Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public&amp;action=new']);
                                $title = "Edit User Fields";
                                $modal = openmodal('dynamics', "<h4 class='m-0'>$title</h4>", ['static' => TRUE]);
                                $modal .= $this->quantum_dynamics_form();
                                $modal .= closemodal();
                                add_to_footer($modal);
                            }
                            $content = $this->view_public_admin();
                            break;
                        case 'new_cat':
                        case 'cat_edit':
                            $title = "Add New Field Category";
                            $cat_id = get('cat_id', FILTER_VALIDATE_INT);
                            if ($cat_id) {
                                add_breadcrumb(['title' => 'Manage User Public Category', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public&amp;action='.$action]);
                                $title = "Edit Category";
                            }
                            $modal = openmodal('cat', "<h4 class='m-0'>$title</h4>", ['static' => TRUE]);
                            $modal .= $this->quantum_category_form();
                            $modal .= closemodal();
                            add_to_footer($modal);
                            $content = $this->view_public_admin();
                            break;
                        case 'module_edit':
                            // Module Edit
                            $module_id = get('module_id', FILTER_VALIDATE_INT);
                            if ($module_id) {
                                add_breadcrumb(['title' => 'Manage Module User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public&amp;action=module_edit&amp;module_id='.$module_id]);
                                $title = "Edit Module User Fields";
                                $modal = openmodal('cat', "<h4 class='m-0'>$title</h4>", ['static' => TRUE]);
                                $modal .= $this->display_module_form();
                                $modal .= closemodal();
                                add_to_footer($modal);
                            }
                            $content = $this->view_public_admin();
                            break;
                        default:
                            $content = $this->view_public_admin();
                    }
                } else {
                    $content = $this->view_public_admin();
                }

                break;

            case 'preference':

                add_breadcrumb(['title' => 'Preference User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preference']);
                if (isset($_GET['action']) && $_GET['action'] == 'new') {
                    add_breadcrumb(['title' => 'Manage Preference User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preference&amp;action=new']);
                    $content = $this->view_modules('preference');
                } else {
                    $content = $this->view_preference_admin();
                }
                break;

            case 'security':
                add_breadcrumb(['title' => 'Security User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security']);
                if (isset($_GET['action']) && $_GET['action'] == 'new') {
                    add_breadcrumb(['title' => 'Manage Security User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security&amp;action=new']);
                    $content = $this->view_modules('security');
                } else {
                    $content = $this->view_security_admin();
                }
                break;
        }

        opentable($this->title);
        //print_p($content);
        echo $content;
        closetable();
    }

    /**
     * Public Profile Field Admin
     *
     * @return string
     * @throws \ReflectionException
     */
    private function view_public_admin() {
        $aidlink = fusion_get_aidlink();

        $active = '';
        $new_button = "<a class='btn btn-default m-r-10' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public&amp;action=new'>Add New Field</a>";
        $new_button .= "<a class='btn btn-default' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public&amp;action=new_cat'>".$this->locale['fields_0311']."</a>";

        $html = "<h4 class='display-inline-block m-r-10'>Public User Fields</h4>\n$new_button<hr/>\n";

        $field_id = get('field_id', FILTER_VALIDATE_INT);

        $module_id = get('module_id', FILTER_VALIDATE_INT);

        $cat_id = get('cat_id', FILTER_VALIDATE_INT);

        if (!empty($this->page[0])) {
            $tab_title = [];
            foreach ($this->page[0] as $page_id => $page_data) {
                $tab_title['title'][$page_id] = self::parse_label($page_data['field_cat_name']);
                $tab_title['id'][$page_id] = $page_id;
                $tab_title['icon'][$page_id] = $page_data['field_cat_class'];
                $active = ($cat_id && $page_id == $cat_id) ? $page_id : '';
            }

            if ($field_id || $module_id) {
                $_fields = flatten_array($this->fields);

                foreach ($_fields as $fData) {
                    if ($field_id) {
                        if ($fData['field_id'] == $field_id) {
                            $fieldCat = $fData['field_cat'];
                            $active = isset($_GET['field_id']) ? get_root($this->field_cat_index, $fieldCat) : 0;
                            break;
                        }
                    } else if ($module_id) {
                        if ($fData['field_id'] == $module_id) {
                            $fieldCat = $fData['field_cat'];
                            $active = isset($_GET['module_id']) ? get_root($this->field_cat_index, $fieldCat) : 0;
                            break;
                        }
                    }
                }
            }
            reset($tab_title['title']);
            $default_active = key($tab_title['title']);

            // Tab
            $tab_active = tab_active($tab_title, ($active ? $active : $default_active));

            $html .= opentab($tab_title, $tab_active, 'uftab', FALSE, '', '', [], TRUE);

            foreach ($this->page[0] as $page_id => $page_details) {

                $html .= opentabbody($tab_title['title'][$page_id], $tab_title['id'][$page_id], $tab_active);

                // load all categories here.
                if ($this->debug) {
                    $html .= "<div class='m-t-20 text-dark'>\n";
                    if ($page_id == 1) {
                        $html .= sprintf($this->locale['fields_0100'], DB_USERS);
                    } else {
                        $html .= sprintf($this->locale['fields_0101'], $page_details['field_cat_db'], $page_details['field_cat_index']);
                    }
                    $html .= "</div>\n";
                }

                // Edit/Delete Category Administration
                $html .= "<div class='clearfix'>\n";
                $html .= "<div class='pull-right'>\n";
                $html .= "<div class='btn-group m-b-10 m-t-10'>\n";
                $html .= "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$page_id."'>".$this->locale['fields_0308']."</a>";
                $html .= "<a class='btn btn-danger' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$page_id."'>".$this->locale['fields_0313']."</a>";
                $html .= "</div>\n";
                $html .= "</div>\n";
                $html .= "</div>\n";

                if (isset($this->page[$page_id])) {
                    $html .= "<div class='clearfix'>\n";
                    $i = 0;
                    $counter = count($this->page[$page_id]) - 1;
                    foreach ($this->page[$page_id] as $cat_id => $field_cat) {
                        // field category information
                        $html .= "<div class='list-group'>\n";

                        if ($this->debug) {
                            print_p($field_cat);
                        }

                        $html .= "<div class='list-group-item'>\n";
                        $html .= "<h4 class='display-block m-0'>".self::parse_label($field_cat['field_cat_name'])."</h4>";
                        $html .= "<div class='display-block'>\n";
                        if ($i != 0) {
                            $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=cmu&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".($field_cat['field_cat_order'] - 1)."'>".$this->locale['move_up']."</a> &middot; ";
                        } else {
                            $html .= "<span class='text-lighter'>".$this->locale['move_up']."</span> &middot; ";
                        }
                        if ($i !== $counter) {
                            $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=cmd&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".($field_cat['field_cat_order'] + 1)."'>".$this->locale['move_down']."</a> &middot; ";
                        } else {
                            $html .= "<span class='text-lighter'>".$this->locale['move_down']."</span> &middot; ";
                        }
                        $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$cat_id."'>".$this->locale['edit']."</a> &middot; ";
                        $html .= "<a class='text-danger' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$cat_id."'>".$this->locale['delete']."</a>";
                        $html .= "</div>\n";

                        $html .= "</div>\n<div class='list-group-item'>\n";
                        // print all fields.
                        if (isset($this->fields[$cat_id])) {
                            $k = 0;
                            $item_counter = count($this->fields[$cat_id]) - 1;
                            foreach ($this->fields[$cat_id] as $arr => $field_data) {
                                //Fields - Move down/Move Up - Edit - Delete

                                $item_id = $module_id ? $module_id : 0;
                                if (!$item_id) {
                                    $item_id = $field_id ? $field_id : 0;
                                }

                                $html .= "<div class='row'>\n<div class='col-xs-12 col-sm-9'>\n";
                                $options = ['inline' => 1, 'show_title' => 1, 'hide_value' => 1];
                                if ($field_data['field_type'] == 'file') {
                                    $options += [
                                        'plugin_folder'        => $this->plugin_folder,
                                        'plugin_locale_folder' => $this->plugin_locale_folder,
                                    ];
                                }
                                $html .= $this->display_fields($field_data, $this->callback_data, $this->method, $options);
                                $html .= "</div><div class='col-xs-12 col-sm-3'>\n";
                                $html .= "<div class='text-right'>\n";
                                if ($k != 0) {
                                    $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=fmu&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".($field_data['field_order'] - 1)."'>".$this->locale['move_up']."</a> &middot; ";
                                } else {
                                    $html .= "<span class='text-lighter'>".$this->locale['move_up']."</span> &middot; ";
                                }
                                if ($k !== $item_counter) {
                                    $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=fmd&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".($field_data['field_order'] + 1)."'>".$this->locale['move_down']."</a> &middot; ";
                                } else {
                                    $html .= "<span class='text-lighter'>".$this->locale['move_down']."</span> &middot; ";
                                }
                                if ($field_data['field_type'] == 'file') {
                                    $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=module_edit&amp;module_id=".$field_data['field_id']."'>".$this->locale['edit']."</a> &middot; ";
                                } else {
                                    $html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=field_edit&amp;field_id=".$field_data['field_id']."'>".$this->locale['edit']."</a> &middot; ";
                                }
                                $html .= "<a class='text-danger' href='".FUSION_SELF.$aidlink."&amp;action=field_delete&amp;field_id=".$field_data['field_id']."'>".$this->locale['delete']."</a>";
                                $html .= "</div>\n";
                                $html .= "</div>\n</div>\n";
                                $k++;
                            }
                        }
                        $i++;
                        $html .= "</div>\n</div>\n";
                    }
                    $html .= "</div>\n";
                } else {
                    // display no category
                    $html .= "<div class='text-center'><h4>".$this->locale['fields_0102'].self::parse_label($page_details['field_cat_name'])."</h4></div>\n";
                }
                $html .= closetabbody();
            }
            $html .= closetab();
        } else {
            $html .= "<div class='well text-center'>".$this->locale['fields_0103']."</div>\n";
        }
        //$html .="</div>\n<div class='col-xs-12 col-sm-6 col-md-5 col-lg-4'>\n";
        //$html .= $this->quantum_admin_buttons();
        //$html .="</div>\n</div>\n";

        return (string)$html;
    }

    /**
     * @todo: increment rest api from marketplace.
     *
     * @param string $folder
     *
     * @return string
     * @throws \Exception
     */
    private function view_modules($folder = 'public') {

        $html = "<h4>Add User Fields</h4>\n";
        $aidlink = fusion_get_aidlink();

        if (!empty($this->cat_list)) {
            // The edit and add form will be in modal phase.
            if ($folder == 'public') {
                $html .= "<div class='list-group-item'>\n";
                $html .= "<div class='row m-t-20'>\n";
                $field_type = $this->get_dynamics_type();
                unset($field_type['file']);
                foreach ($field_type as $type => $name) {
                    $html .= "<div class='col-xs-6 col-sm-4 col-md-2 col-lg-2 p-b-20'>
                <a class='btn btn-block btn-default' href='".ADMIN."user_fields.php$aidlink&amp;ref=public&amp;action=new&amp;add_field=$type'>$name</a>
                </div>\n";
                }
                $html .= "</div>\n</div>\n<hr/>";
            }
        }

        // modules
        if (!empty($this->modules)) {

            $_user_field = stripinput(get('install'));
            if ($_user_field) {

                if ($folder == 'public') {

                    $modal = openmodal('modadd', "<h4 class='m-0'>Add User Fields Module</h4>");
                    $modal .= $this->display_module_form();
                    $modal .= closemodal();
                    add_to_footer($modal);

                } else {

                    if (isset($this->modules[$_user_field])) {
                        if (in_array($_user_field, array_keys($this->available_field_info))) {
                            $module_data = $this->modules[$_user_field];
                            $_fieldInstall = [
                                'field_id'           => 0,
                                'field_title'        => $module_data['user_field_name'],
                                'field_name'         => $module_data['user_field_dbname'],
                                'field_cat'          => 0,
                                'field_type'         => $folder,
                                'field_default'      => '',
                                'field_error'        => '',
                                'field_required'     => 0,
                                'field_log'          => 0,
                                'field_registration' => 0,
                                'field_order'        => 0,
                                'field_config'       => 0
                            ];
                            $_fieldInstall['field_order'] = dbresult(dbquery("SELECT COUNT(field_id) FROM ".DB_USER_FIELDS." WHERE field_type=:type AND field_cat=:cat_id",
                                    [':cat_id' => $this->field_data['field_cat'],
                                     ':type'   => $folder
                                    ]), 0) + 1;
                            $this->create_fields($_fieldInstall, 'module', DB_USERS);
                        }
                    }
                }
            }

            $html .= "<div class='row equal-height'>\n";
            foreach ($this->modules as $module_name => $module_data) {
                $button = "<a class='btn btn-default' href='".clean_request("install=".$module_name, ['install', 'uninstall'], FALSE)."'>Install</a>";
                if (!in_array($module_name, array_keys($this->available_field_info))) {
                    $button = "<a class='btn btn-danger' href='".clean_request("uninstall=".$module_name, ['install', 'uninstall'], FALSE)."'>Uninstall</a>";
                }
                $html .= "<div class='".grid_column_size(100,50,33,25)."'>\n";
                $html .= "<div class='list-group-item text-center'>";
                if ($module_data['module_image']) {
                    $html .= "<img src='".$module_data['module_image']."' class='icon img-responsive' style='margin:15px auto; max-height:48px;'>";
                }
                $html .= "<h4 class='strong'>".$module_data['user_field_name']."</h4>";
                $html .= "<div class='clearfix position-relative overflow-hide'>";
                $html .= $module_data['user_field_desc'];
                $html .= "<br/><small>Version: ".$module_data['user_field_version']."</small>";
                $html .= "</div>";
                $html .= "<div class='clearfix text-right'>";
                $html .= $button;
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
            }
            $html .= "</div>\n";
        } else {
            $html .= "<div>There are no user fields available</div>\n";
        }

        return $html;
    }

    private function view_preference_admin() {
        $new_button = "<a class='btn btn-default' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=preference&amp;action=new'>Add New</a>";
        $table = new Tables(new Preference(), FALSE);
        return $new_button.$table->displayTable();
    }

    private function view_security_admin() {
        $new_button = "<a class='btn btn-default' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=security&amp;action=new'>Add New</a>";
        $table = new Tables(new Security(), FALSE);
        return $new_button.$table->displayTable();
    }

    public function get_field_description($data) {
        $plugin_file = INCLUDES.'user_fields/'.$data[':field_type'].'/'.$data[':field_name'].'/'.$data[':field_name'].'_include_var.php';
        $user_field_desc = '';
        $user_field_author = '';
        $user_field_api_version = '';
        if (is_file($plugin_file)) {
            include $plugin_file;
        }

        return "<h4 class='m-0'>".$data[':field_title']."</h4>".$user_field_desc."<div class='m-0'>by <i>$user_field_author</i> | Version: $user_field_api_version</div>";

    }

    /**
     * Display fields for each fieldDB record entry
     *
     * @param array  $data   The array of the user field.
     * @param        $callback_data
     * @param string $method input or display. In case of any other value
     *                       the method return FALSE. See the description of return for more details.
     * @param array  $options
     *                       <ul>
     *                       <li><strong>deactivate</strong> (boolean): FALSE by default.
     *                       disable fields</li>
     *                       <li><strong>debug</strong> (bolean): FALSE by default.
     *                       Show some information to debug.</li>
     *                       <li><strong>encrypt</strong> (boolean): FALSE by default.
     *                       encrypt field names</li>
     *                       <li><strong>error_text</strong> (string): empty string by default.
     *                       sets the field error text</li>
     *                       <li><strong>hide_value</strong> (boolean): FALSE by default.
     *                       input value is not shown on fields render</li>
     *                       <li><strong>inline</strong> (boolean): FALSE by default.
     *                       sets the field inline</li>
     *                       <li><strong>required</strong> (boolean): FALSE by default.
     *                       input must be filled when validate</li>
     *                       <li><strong>show_title</strong> (boolean): FALSE by default.
     *                       display field label</li>
     *                       <li><strong>placeholder</strong> (string): empty string by default.
     *                       helper text in field value</li>
     *                       <li><strong>plugin_folder</strong> (string): INCLUDES.'user_fields/' by default
     *                       The folder's path where the field's source files are.</li>
     *                       <li><strong>plugin_locale_folder</strong> (string): LOCALE.LOCALESET.'/user_fields/' by
     *                       default. The folder's path where the field's locale files are.</li>
     *                       </ul>
     *
     * @return array|bool|string
     *                       <ul>
     *                       <li>FALSE on failure</li>
     *                       <li>string if $method 'display'</li>
     *                       <li>array if $method is 'input'</li>
     *                       </ul>
     * @throws \ReflectionException
     */
    public function display_fields(array $data, $callback_data, $method = 'input', array $options = []) {

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
            'hide_value'           => FALSE,
            'encrypt'              => FALSE,
            'show_title'           => $method == "input" ? TRUE : FALSE,
            'deactivate'           => FALSE,
            'inline'               => FALSE,
            'error_text'           => $data['field_error'],
            'required'             => (bool)$data['field_required'],
            'placeholder'          => $data['field_default'],
            'debug'                => FALSE
        ];

        $options += $default_options;

        // Sets callback data automatically.
        $option_list = $data['field_options'] ? explode(',', $data['field_options']) : [];

        // Format Callback Data
        $field_value = isset($callback_data[$data['field_name']]) ? $callback_data[$data['field_name']] : '';
        $field_post_value = post($data['field_name']);

        if ($field_post_value && !$options['hide_value']) {
            $field_value = $field_post_value;
        }

        if ($options['hide_value']) {
            $field_value = '';
        }

        $field_label = $options['show_title'] ? self::parse_label($data['field_title']).':' : '';

        switch ($data['field_type']) {

            case 'file':

                $user_data = $callback_data;

                $profile_method = $method;

                $plugin_file = $this->modules[$data['field_name']]['module_file_path'];

                if (file_exists($plugin_file)) {

                    include $plugin_file;

                    if ($method == 'input') {
                        if (isset($user_fields)) {
                            return $user_fields;
                        }
                    } else if ($method == 'display' && !empty($user_fields['value'])) {
                        return $user_fields;
                    }
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
                        'title' => self::parse_label($data['field_title']),
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
                        'title' => self::parse_label($data['field_title']),
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
                        'title' => self::parse_label($data['field_title']),
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
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'select':
                if ($method == 'input') {
                    $options += ['options' => $option_list];
                    $options['keyflip'] = TRUE;

                    return form_select($data['field_name'], self::parse_label($data['field_title']), $field_value, $options);

                } else if ($method == 'display' && $field_value) {
                    $options_value = explode(",", $data['field_options']);

                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => !empty($options_value[$field_value]) ? $options_value[$field_value] : $field_value,
                    ];
                }
                break;
            case 'tags':
                if ($method == 'input') {
                    $options += ['options' => $option_list, 'tags' => TRUE, 'multiple' => TRUE, 'width' => '100%', 'inner_width' => '100%'];

                    return form_select($data['field_name'],
                        $options['show_title'] ? self::parse_label($data['field_title']) : '',
                        $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
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
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'textarea':
                if ($method == 'input') {
                    return form_textarea($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'checkbox':
                if ($method == 'input') {
                    return form_checkbox($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'datepicker':
                if ($method == 'input') {
                    return form_datepicker($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => showdate('shortdate', $field_value)
                    ];
                }
                break;
            case 'colorpicker':
                if ($method == 'input') {
                    return form_colorpicker($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'upload':
                if ($method == 'input') {
                    return form_fileinput($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'hidden':
                if ($method == 'input') {
                    return form_hidden($data['field_name'], self::parse_label($data['field_title']), $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value
                    ];
                }
                break;
            case 'address':
                if ($method == 'input') {
                    return form_geo($data['field_name'], $field_label, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => implode('|', $field_value)
                    ];
                }
                break;
            case 'toggle':
                $options['toggle'] = 1;
                $options['toggle_text'] = [$this->locale['off'], $this->locale['on']];
                if ($method == 'input') {
                    return form_checkbox($data['field_name'], $field_label, $field_value, $options);
                } else if ($method == 'display' && $field_value) {
                    return [
                        'title' => self::parse_label($data['field_title']),
                        'value' => $field_value,
                    ];
                }
                break;
        }

        return FALSE;
    }


    protected function cacheModuleFiles() {
        if (empty(self::$module_list)) {
            foreach($this->plugin_folder as $plugin_folder) { // infusions/ user_fields.
                $plugin_folder = rtrim($plugin_folder, '/').'/';
                $folders = makefilelist($plugin_folder, '.|..|index.php', TRUE, 'folders');
                if (!empty($folders)) {
                    foreach($folders as $folder_name) {
                        $path = $plugin_folder.$folder_name.'/'.$folder_name.'_include.php';
                        $var_path = $plugin_folder.$folder_name.'/'.$folder_name.'_include_var.php';
                        if ($plugin_folder == INFUSIONS) {
                            $path = $plugin_folder.$folder_name.'/user_fields/'.$folder_name.'_include.php';
                            $var_path = $plugin_folder.$folder_name.'/user_fields/'.$folder_name.'_include_var.php';
                        }
                        if (file_exists($path) && file_exists($var_path)) {
                            // i also need var file
                            self::$module_list_var[$folder_name] = $var_path;
                            self::$module_list[$folder_name] = $path;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function get_dynamics_type() {

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
            'tags'        => $this->locale['fields_0511'],
            'location'    => $this->locale['fields_0512'],
            'number'      => $this->locale['fields_0513'],
            'email'       => $this->locale['fields_0514'],
            'url'         => $this->locale['fields_0515'],
        ];
    }

    /**
     * Category form input
     *
     * @return string
     * @throws \ReflectionException
     */
    public function quantum_category_form() {
        $aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale();
        $this->debug = FALSE;

        add_to_jquery("$('#field_parent').val() == '0' ? $('#page_settings').show() : $('#page_settings').hide(); $('#field_parent').bind('change', function() { $(this).val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()});");

        $action = get('action');
        $cat_id = get('cat_id', FILTER_VALIDATE_INT);

        if ($action == 'cat_edit' && $cat_id && self::validate_fieldCat($cat_id)) {

            $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='$cat_id'");
            if (dbrows($result)) {
                $this->field_cat_data = dbarray($result);
            } else {
                addNotice('danger', $this->locale['field_0206']);
                redirect(FUSION_SELF.$aidlink);
            }
        }

        if (post('save_cat')) {
            // print_p($_POST);
            $this->field_cat_data = [
                'field_cat_id'    => sanitizer('field_cat_id', '', 'field_cat_id'),
                'field_cat_name'  => sanitizer(['field_cat_name'], '', 'field_cat_name', TRUE),
                'field_parent'    => sanitizer('field_parent', '', 'field_parent'),
                'field_cat_order' => sanitizer('field_cat_order', '', 'field_cat_order'),
                'field_cat_db'    => '',
                'field_cat_index' => '',
                'field_cat_class' => '',
            ];
            // only if root then need to sanitize
            $old_data = [
                "field_cat_db" => "users"
            ];

            $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$this->field_cat_data['field_cat_id']."'");

            if (dbrows($result) > 0) {
                $old_data = dbarray($result);
            }

            if ($this->field_cat_data['field_parent'] == 0) {

                $this->field_cat_data['field_cat_db'] = form_sanitizer($_POST['field_cat_db'], 'users', 'field_cat_db');
                $this->field_cat_data['field_cat_index'] = form_sanitizer($_POST['field_cat_index'], '', 'field_cat_index');
                $this->field_cat_data['field_cat_class'] = form_sanitizer($_POST['field_cat_class'], '', 'field_cat_class');
                // Improvised to code jquery chained selector
                if (!column_exists($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index'])) {
                    \Defender::stop();
                    addNotice("danger", "Your table must be a valid table. Your column must be a column of a user id in that table.");
                }

            }

            if ($this->field_cat_data['field_cat_order'] == 0) {

                $this->field_cat_data['field_cat_order'] = dbresult(
                        dbquery("SELECT MAX(field_cat_order)
                                 FROM ".DB_USER_FIELD_CATS."
                                 WHERE field_parent='".$this->field_cat_data['field_parent']."'"
                        ), 0) + 1;
            }

            // shuffle between save and update
            if (self::validate_fieldCat($this->field_cat_data['field_cat_id'])) {

                dbquery_order(
                    DB_USER_FIELD_CATS, $this->field_cat_data['field_cat_order'],
                    'field_cat_order',
                    $this->field_cat_data['field_cat_id'], 'field_cat_id',
                    $this->field_cat_data['field_parent'], 'field_parent', FALSE, FALSE, 'update'
                );

                if (!$this->debug) {
                    if (\Defender::safe()) {
                        if (!empty($old_data['field_cat_db']) or $old_data['field_cat_db'] !== "users") {

                            if (!empty($old_data['field_cat_db']) && !empty($old_data['field_cat_index'])) {
                                // CONDITION: HAVE A PREVIOUS TABLE SET
                                if (!empty($this->field_cat_data['field_cat_db'])) {
                                    // new demands a table insertion, checks if same or not.. if different.
                                    if ($this->field_cat_data['field_cat_db'] !== $old_data['field_cat_db']) {
                                        // But the current table is different than the previous one
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

                            dbquery_insert(DB_USER_FIELD_CATS, $this->field_cat_data, 'update');

                            addNotice('success', $this->locale['field_0207']);
                        }
                        redirect(FUSION_SELF.$aidlink);
                    }

                } else {
                    print_p($this->locale['fields_0661']);
                    print_p($this->field_cat_data);
                }

            } else {

                dbquery_order(
                    DB_USER_FIELD_CATS,
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

                if (!$this->debug && \Defender::safe()) {

                    if (!empty($this->field_cat_data['field_cat_index']) &&
                        !empty($this->field_cat_data['field_cat_db']) &&
                        $this->field_cat_data['field_cat_db'] !== 'users'
                    ) {

                        self::build_table($this->field_cat_data['field_cat_db'], $this->field_cat_data['field_cat_index']);
                    }

                    dbquery_insert(DB_USER_FIELD_CATS, $this->field_cat_data, 'save');

                    addNotice('success', $this->locale['field_0208']);

                    redirect(FUSION_SELF.$aidlink);

                } else {

                    if ($this->debug) {
                        print_p($this->locale['fields_0662']);
                        print_p($this->field_cat_data);
                    }
                }
            }
        }

        $html = openform('cat_form', 'post');
        $html .= self::quantum_multilocale_fields('field_cat_name', $this->locale['fields_0430'], $this->field_cat_data['field_cat_name'], ['required' => 1]);
        // cannot move if there are siblings
        if ($cat_id && isset($this->field_cat_index[$cat_id])) {
            $html .= form_hidden('field_parent', '', $this->field_cat_data['field_parent']);
        } else {

            $cat_list[] = get('cat_id', FILTER_VALIDATE_INT);
            if (!empty($this->cat_list)) {
                foreach ($this->cat_list as $id => $value) {
                    $cat_list[] = $id;
                }
            }
            $html .= form_select_tree('field_parent', $this->locale['fields_0431'], $this->field_cat_data['field_parent'],
                [
                    'parent_value' => $this->locale['fields_0432'],
                    'disable_opts' => $cat_list
                ], DB_USER_FIELD_CATS, 'field_cat_name', 'field_cat_id', 'field_parent');
        }

        $html .= form_text('field_cat_order', $this->locale['fields_0433'], $this->field_cat_data['field_cat_order'], ['type' => 'number', 'inner_width' => '100px']);
        $html .= form_hidden('field_cat_id', '', $this->field_cat_data['field_cat_id'], ['number' => 1]);
        $html .= form_hidden('add_cat', '', 'add_cat');
        // root settings
        $html .= "<div id='page_settings' class='list-group-item m-t-20'>\n";

        global $db_name;
        $tableList = [];
        $show_tables = dbquery("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='".$db_name."'");
        if (dbrows($show_tables) > 0) {
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
                "deactivate"  => FALSE, //$this->field_cat_data['field_cat_db'] ? TRUE : FALSE,
                "ext_tip"     => $this->locale['fields_0111'],
                "options"     => $tableList
            ]);
        $html .= "<div class='text-smaller m-b-10'>".$this->locale['fields_0112']."</div>\n";
        $html .= form_text('field_cat_index', $this->locale['fields_0435'], $this->field_cat_data['field_cat_index'],
            [
                'placeholder' => 'user_id',
                "required"    => TRUE,
                "inline"      => FALSE
            ]);
        $html .= "<div class='text-smaller m-b-10'>".$this->locale['fields_0113']."</div>\n";
        $html .= form_text('field_cat_class', $this->locale['fields_0436'], $this->field_cat_data['field_cat_class'],
            [
                'placeholder' => $this->locale['fields_0437'],
                "inline"      => FALSE
            ]);
        $html .= form_hidden('add_cat', '', 'add_cat');
        $html .= "</div>\n";
        $html .= "<hr/>\n";
        $html .= form_button('save_cat', $this->locale['fields_0318'], 'save_cat', ['class' => 'm-r-10 btn-primary']);
        $html .= "<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public'>".$this->locale['cancel']."</a>";
        $html .= closeform();

        return (string)$html;
    }

    /**
     * Multiple locale fields input
     *
     * @param       $input_name
     * @param       $title
     * @param       $input_value
     * @param array $options
     *
     * @return string
     */
    public static function quantum_multilocale_fields($input_name, $title, $input_value, array $options = []) {
        $locale = fusion_get_locale();
        $html = '';
        $language_opts = fusion_get_enabled_languages();
        $input_value = self::is_serialized($input_value) ? unserialize($input_value) : $input_value;
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
        $html .= "<div id='".$options['input_id']."-field' class='form-group clearfix".($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'>\n";
        $html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
        $html .= ($options['inline']) ? "<div class='col-xs-12 ".($title ? "col-sm-9 col-md-9 col-lg-9 p-l-15" : "col-sm-12 col-md-12 col-lg-12")."'>\n" : "<div class='p-t-5 p-b-0'>\n";
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
                $sub_html .= $options['function']($input_name."[$lang]", "",
                    isset($input_value[$lang]) ? $input_value[$lang] : '', $options);
            }
        }

        $html .= $main_html.$sub_html;

        if (count($language_opts) > 1) {
            $html .= "<div class='dropdown m-b-15'>\n";
            $html .= "<button id='lang_dropdown' data-toggle='dropdown' class='dropdown-toggle btn btn-sm btn-default' type='button'>".$locale['add_language']." <span class='caret'></span></button>\n";
            $html .= "<ul class='dropdown-menu'>\n";
            foreach ($language_opts as $Lang => $LangName) {
                if ($Lang !== LANGUAGE) {
                    $html .= "<li><a data-add='$Lang' data-input='$input_name' data-locale='$LangName' class='pointer data-add'><i class='fa fa-plus-circle fa-fw'></i> $LangName</a></li>\n";
                    if ($Lang !== LANGUAGE) {
                        add_to_jquery("$('#".$input_name."-".$Lang."-field').hide();");
                    }
                }
            }
            $html .= "</ul>\n";
            $html .= "</div>\n";
            if (!defined('QUANTUM_MULTILOCALE_FIELDS')) {
                define('QUANTUM_MULTILOCALE_FIELDS', TRUE);
                add_to_jquery("
                $('.data-add').bind('click', function(e) {
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
        $html .= "</div>\n";
        $html .= "</div>\n";

        return $html;
    }

    /** The master form for Adding or Editing Dynamic Fields */
    private function quantum_dynamics_form() {
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

        $action = get('action');
        $field_id = get('field_id', FILTER_VALIDATE_INT);

        if ($action == 'field_edit' && $field_id) {
            if (self::validate_field($field_id)) {
                $result = dbquery("SELECT * FROM ".DB_USER_FIELDS." WHERE field_id='".intval($_GET['field_id'])."'");
                if (dbrows($result) > 0) {
                    $this->field_data = dbarray($result);
                    if ($this->field_data['field_type'] == 'upload') {
                        $this->field_data['field_config'] = \Defender::decode($this->field_data['field_config']);
                        if ($this->debug) {
                            print_p($this->field_data);
                        }
                    }
                } else {
                    if (!$this->debug) {
                        redirect(FUSION_SELF.$aidlink);
                    }
                }
            } else {
                if (!$this->debug) {
                    redirect(FUSION_SELF.$aidlink);
                }
            }
        }

        $this->field_data['field_type'] = isset($_POST['add_field']) ? form_sanitizer($_POST['add_field'], '') : $this->field_data['field_type'];

        if (post('save_field')) {

            $add_field = get('add_field');

            $this->field_data = [
                'field_type'         => $add_field ? $add_field : $this->field_data['field_type'],
                'field_id'           => sanitizer('field_id', '0', 'field_id'),
                'field_title'        => form_sanitizer($_POST['field_title'], '', 'field_title', 1),
                'field_name'         => sanitizer('field_name', '', 'field_name'),
                'field_cat'          => sanitizer('field_cat', '0', 'field_cat'),
                'field_options'      => isset($_POST['field_options']) ? sanitizer('field_options', '', 'field_options') : $this->field_data['field_options'],
                'field_default'      => isset($_POST['field_default']) ? sanitizer('field_default', '', 'field_default') : $this->field_data['field_default'],
                'field_error'        => sanitizer('field_error', '', 'field_error'),
                'field_required'     => isset($_POST['field_required']) ? 1 : 0,
                'field_log'          => isset($_POST['field_log']) ? 1 : 0,
                'field_registration' => isset($_POST['field_registration']) ? 1 : 0,
                'field_order'        => sanitizer('field_order', '0', 'field_order')
            ];

            $this->field_data['field_name'] = str_replace(' ', '_', $this->field_data['field_name']); // make sure no space.

            if ($this->field_data['field_type'] == 'upload') {

                $max_b = isset($_POST['field_max_b']) ? form_sanitizer($_POST['field_max_b'], '', 'field_max_b') : 150000;
                $calc = isset($_POST['field_calc']) ? form_sanitizer($_POST['field_calc'], '', 'field_calc') : 1;
                $this->field_data['field_upload_type'] = isset($_POST['field_upload_type']) ? form_sanitizer($_POST['field_upload_type'], '', 'field_upload_type') : '';
                if (!in_array($this->field_data['field_upload_type'], ['file', 'image'])) {
                    \Defender::stop();
                    addNotice('danger', $this->locale['fields_0108']);
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
                        'field_thumbnail_2'        => isset($_POST['field_thumbnail_2']) ? 1 : isset($_POST['field_id']) ? 0 : $this->field_data['field_thumbnail_2'],
                        'field_thumb2_upload_path' => isset($_POST['field_thumb2_upload_path']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail_2']) ? form_sanitizer($_POST['field_thumb2_upload_path'], '', 'field_thumb2_upload_path') : '',
                        'field_thumb2_w'           => isset($_POST['field_thumb2_w']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail_2']) ? form_sanitizer($_POST['field_thumb2_w'], '', 'field_thumb2_w') : '',
                        'field_thumb2_h'           => isset($_POST['field_thumb2_h']) && $this->field_data['field_upload_type'] == 'image' && isset($this->field_data['field_thumbnail_2']) ? form_sanitizer($_POST['field_thumb2_h'], '', 'field_thumb2_h') : '',
                        'field_delete_original'    => isset($_POST['field_delete_original']) && $this->field_data['field_upload_type'] == 'image' ? 1 : 0
                    ];
                }
            }

            if (!$this->field_data['field_order']) {
                $this->field_data['field_order'] = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cat_id", [':cat_id' => $this->field_data['field_cat']]), 0) + 1;
            }

            if (\Defender::safe()) {
                if (!empty($this->field_data['field_config'])) {
                    $this->field_data['field_config'] = \Defender::encode($this->field_data['field_config']);
                }

                $this->create_fields($this->field_data, 'dynamics'); // will redirect and refresh config
            }

        }

        $html = openform('fieldform', 'post');

        $disable_opts = [];
        foreach ($this->page_list as $index => $v) {
            $disable_opts[] = $index;
        }
        // ok the value generated needs to be parsed by quantum
        $html .= form_select_tree('field_cat', $this->locale['fields_0450'], $this->field_data['field_cat'], [
            'no_root'      => TRUE,
            'width'        => '100%',
            'disable_opts' => $disable_opts
        ], DB_USER_FIELD_CATS, 'field_cat_name', 'field_cat_id', 'field_parent');
        $html .= self::quantum_multilocale_fields('field_title', $this->locale['fields_0451'], $this->field_data['field_title'], ['required' => 1]);
        $html .= form_text('field_name', $this->locale['fields_0453'], $this->field_data['field_name'],
            [
                'placeholder' => $this->locale['fields_0454'],
                'required'    => TRUE
            ]
        );
        switch ($this->field_data['field_type']) {
            case 'select':
                $html .= form_select('field_options', $this->locale['fields_0455'], $this->field_data['field_options'],
                    [
                        'required'    => TRUE,
                        'tags'        => TRUE,
                        'multiple'    => TRUE,
                        'width'       => '100%',
                        'inner_width' => '100%'
                    ]
                );
                break;
            case 'upload':
                require_once(INCLUDES.'mimetypes_include.php');
                $file_type_list = [];
                $file_image_list = [];
                foreach (mimeTypes() as $file_ext => $occ) {
                    if (!in_array($file_ext, array_flip(img_mimeTypes()))) {
                        $file_type_list[] = '.'.$file_ext;
                    }
                }
                foreach (img_mimeTypes() as $file_ext => $occ) {
                    $file_image_list[] = '.'.$file_ext;
                }
                $calc_opts = [
                    1       => $this->locale['fields_0490'],
                    1024    => $this->locale['fields_0491'],
                    1048576 => $this->locale['fields_0492']
                ];
                $calc_c = $this->calculate_byte($this->field_data['field_config']['field_max_b']);
                $calc_b = $this->field_data['field_config']['field_max_b'] / $calc_c;
                $file_upload_type = [
                    'file'  => $this->locale['fields_0456'],
                    'image' => $this->locale['fields_0489']
                ];
                $html .= form_select('field_upload_type', $this->locale['fields_0457'], $this->field_data['field_config']['field_upload_type'], ["options" => $file_upload_type]);
                $html .= form_text('field_upload_path', $this->locale['fields_0458'], $this->field_data['field_config']['field_upload_path'],
                    [
                        'placeholder' => $this->locale['fields_0459'],
                        'required'    => TRUE
                    ]
                );
                $html .= "<label for='field_max_b'>".$this->locale['fields_0460']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text('field_max_b', '', $calc_b, [
                    'class'    => 'm-b-0',
                    'type'     => 'number',
                    'required' => TRUE
                ]);
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
                $html .= form_select('field_calc', '', $calc_c, ['options' => $calc_opts, 'width' => '100%']);
                $html .= "</div>\n</div>\n";
                // File Type
                $html .= "<div id='file_type'>\n";
                $html .= form_select('field_valid_file_ext', $this->locale['fields_0461'], $this->field_data['field_config']['field_valid_file_ext'],
                    [
                        'options'     => $file_type_list,
                        'multiple'    => TRUE,
                        'tags'        => TRUE,
                        'required'    => TRUE,
                        'width'       => '100%',
                        'inner_width' => '100%'
                    ]);
                $html .= "</div>\n";
                // Image Type
                $html .= "<div id='image_type'>\n";
                $html .= form_select('field_valid_image_ext', $this->locale['fields_0462'], $this->field_data['field_config']['field_valid_image_ext'],
                    [
                        'options'  => $file_image_list,
                        'multiple' => TRUE,
                        'tags'     => TRUE,
                        'required' => TRUE
                    ]);
                $html .= "<label>".$this->locale['fields_0463']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text('field_image_max_w', $this->locale['fields_0464'], $this->field_data['field_config']['field_image_max_w'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ]);
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
                $html .= form_text('field_image_max_h', $this->locale['fields_0465'], $this->field_data['field_config']['field_image_max_h'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ]);
                $html .= "</div>\n</div>\n";
                $html .= form_checkbox('field_thumbnail', $this->locale['fields_0467'], $this->field_data['field_config']['field_thumbnail'], ['reverse_label' => TRUE]);
                $html .= "<div id='field_t1'>\n";
                $html .= form_text('field_thumb_upload_path', $this->locale['fields_0468'],
                    $this->field_data['field_config']['field_thumb_upload_path'], [
                        'placeholder' => $this->locale['fields_0469'],
                        'required'    => 1
                    ]);
                $html .= "<label>".$this->locale['fields_0470']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text('field_thumb_w', $this->locale['fields_0471'], $this->field_data['field_config']['field_thumb_w'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ]);
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
                $html .= form_text('field_thumb_h', $this->locale['fields_0472'], $this->field_data['field_config']['field_thumb_h'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ]);
                $html .= "</div>\n</div>\n";
                $html .= "</div>\n";
                $html .= form_checkbox('field_thumbnail_2', $this->locale['fields_0473'], $this->field_data['field_config']['field_thumbnail_2'], ['reverse_label' => TRUE]);
                $html .= "<div id='field_t2'>\n";
                $html .= form_text('field_thumb2_upload_path', $this->locale['fields_0474'], $this->field_data['field_config']['field_thumb2_upload_path'],
                    [
                        'placeholder' => $this->locale['fields_0469'],
                        'required'    => TRUE,
                    ]);
                $html .= "<label>".$this->locale['fields_0475']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text('field_thumb2_w', $this->locale['fields_0476'], $this->field_data['field_config']['field_thumb2_w'], [
                    'type'        => 'number',
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => TRUE,
                ]);
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";/**/
                $html .= form_text('field_thumb2_h', $this->locale['fields_0477'], $this->field_data['field_config']['field_thumb2_h'], [
                    'type'        => 'number',
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => TRUE
                ]);
                $html .= "</div>\n</div>\n";
                $html .= "</div>\n";
                $html .= form_checkbox('field_delete_original', $this->locale['fields_0478'], $this->field_data['field_config']['field_delete_original'], ['reverse_label' => TRUE]);
                $html .= "</div>\n";
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
                break;
            default:
                if ($this->field_data['field_type'] !== 'textarea') {
                    $html .= form_text('field_default', $this->locale['fields_0480'], $this->field_data['field_default']);
                }
        }
        $html .= form_text('field_error', $this->locale['fields_0481'], $this->field_data['field_error']);
        $html .= form_checkbox('field_required', $this->locale['fields_0482'], $this->field_data['field_required'], ['reverse_label' => TRUE]);
        $html .= form_checkbox('field_log', $this->locale['fields_0483'], $this->field_data['field_log'], ['reverse_label' => TRUE]);
        $html .= form_text('field_order', $this->locale['fields_0484'], $this->field_data['field_order'], ['type' => 'number', 'inner_width' => '120px']);
        $html .= form_checkbox('field_registration', $this->locale['fields_0485'], $this->field_data['field_registration'], ['reverse_label' => TRUE]);
        $html .= form_hidden('add_field', '', $this->field_data['field_type']);
        $html .= form_hidden('field_id', '', $this->field_data['field_id']);
        $html .= "<hr/>\n";
        $html .= form_button('save_field', $this->locale['fields_0488'], 'save', ['class' => 'btn-sm btn-primary m-r-10']);
        $html .= "<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public'>".$this->locale['cancel']."</a>";
        $html .= closeform();

        return (string)$html;
    }

    /*
     * Calculation of Byte
     */
    private function calculate_byte($max_b) {
        $calc_opts = [
            1       => $this->locale['fields_0490'],
            1024    => $this->locale['fields_0491'],
            1048576 => $this->locale['fields_0492']
        ];
        foreach ($calc_opts as $byte => $val) {
            if ($max_b / $byte <= 999) {
                return $byte;
            }
        }

        return 1048576;
    }

    /**
     * Add Field into table
     *
     * @param        $data
     * @param string $type
     * @param string $table_name
     *
     * @throws \Exception
     */
    private function create_fields($data, $type = 'dynamics', $table_name = '') {
        $aidlink = fusion_get_aidlink();

        // Build a field Attr
        $field_attr = '';
        if ($type == 'dynamics') {
            $field_attr = $this->dynamics_fieldinfo($data['field_type'], $data['field_default']);

        } else if ($type == 'module') {
            $field_attr = $this->modules[$data['field_name']]['user_field_dbinfo'];
        }
        // Field order check
        $max_order = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cid", [':cid' => $data['field_cat']]), 0) + 1;
        if (!$data['field_order'] || $data['field_order'] > $max_order) {
            $data['field_order'] = $max_order;
        }

        if (self::validate_field($data['field_id'])) {
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

                $newRows = [];
                if (dbrows($new_result) > 0) {
                    $newRows = dbarray($new_result);
                    $new_table = $newRows['field_cat_db'] ? DB_PREFIX.$newRows['field_cat_db'] : DB_USERS;
                } else {
                    $new_table = DB_USERS;
                }

                if ($this->debug) {
                    // Old Table Information
                    print_p($this->locale['fields_0664']);
                    print_p($oldRows);

                    // New Table Information
                    print_p($this->locale['fields_0665']);
                    print_p($newRows);

                    print_p($data['field_cat']);
                    print_p($oldRows['field_cat']);
                }

                if ($data['field_cat'] != $oldRows['field_cat']) { // old and new mismatch - move to another category and possibly a new table.
                    // Fork #1 - Update on new table
                    if ($this->debug) {
                        print_p($this->locale['fields_0666']);
                    }

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
                                if (\Defender::safe()) {
                                    // sort the fields. if 2, greater than 2 all +1 on the new category
                                    dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                                    // since change table. fix all which is greater than link order.
                                    dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                                }
                            } else {
                                \Defender::stop();
                                addNotice("danger", str_replace('[OLD_TABLE]', $old_table, $this->locale['fields_0667']).$new_table);
                            }
                        } else {
                            // DEBUG MODE
                            if (!in_array($data['field_name'], $new_table_columns)) {
                                print_p("Move ".$data['field_name']." from ".$old_table." to ".$new_table);
                                print_p("Dropping column ".$oldRows['field_name']." on ".$old_table);
                                print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                                // since change table. fix all which is greater than link order.
                                print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'");
                            } else {
                                print_p(str_replace('[OLD_TABLE]', $old_table, $this->locale['fields_0667']).$new_table);
                            }
                        }
                    } else {
                        if (\Defender::safe()) {
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
                    if ($this->debug) {
                        print_p($this->locale['fields_0668']);
                    }

                    if ($data['field_name'] != $oldRows['field_name']) {
                        // not same as old record on dbcolumn
                        // Check for possible duplicates in the new field name
                        if (!in_array($data['field_name'], $old_table_columns)) {
                            if (!$this->debug) {
                                self::rename_column($old_table, $oldRows['field_name'], $data['field_name'], $field_attr);
                            } else {
                                print_p(str_replace(['[FIELD_NAME]', '[OLD_TABLE]', '[FIELD_NAME_]'], [$oldRows['field_name'], $old_table, $data['field_name']], $this->locale['fields_0669']).$field_attr);
                            }
                        } else {
                            \Defender::stop();
                            addNotice('danger', sprintf($this->locale['fields_0104'], "($new_table)"));
                        }
                    }
                    if (!$this->debug) {
                        if (\Defender::safe()) {
                            // make ordering of the same table.
                            if ($data['field_order'] > $oldRows['field_order']) {
                                dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > ".$oldRows['field_order']." AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                            } else if ($data['field_order'] < $oldRows['field_order']) {
                                dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order < ".$oldRows['field_order']." AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                            }
                        }
                    } else {
                        print_p("Old field order is ".$oldRows['field_order']);
                        print_p("New field order is ".$data['field_order']);
                        if ($data['field_order'] > $oldRows['field_order']) {
                            print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > '".$oldRows['field_order']."' AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        } else {
                            print_p("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order < '".$oldRows['field_order']."' AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                        }
                    }
                }

                if (!$this->debug) {
                    if (\Defender::safe()) {
                        dbquery_insert(DB_USER_FIELDS, $data, 'update');
                        addNotice('success', $this->locale['field_0203']);
                        redirect(FUSION_SELF.$aidlink);
                    }
                } else {
                    print_p($data);
                }

            } else {
                \Defender::stop();
                addNotice('danger', $this->locale['fields_0105']);
            }

        } else {

            // Add new column to table.
            if ($table_name) {
                $new_table = $table_name;
            } else {
                $new_table = '';
                $cresult = dbquery("SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                            FROM ".DB_USER_FIELD_CATS." cat
                            LEFT JOIN ".DB_USER_FIELD_CATS." root ON (cat.field_parent = root.field_cat_id)
                            WHERE cat.field_cat_id=:cid", [':cid' => $data['field_cat']]);
                if (dbrows($cresult)) {
                    $cat_data = dbarray($cresult);
                    $new_table = $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS;
                } else {
                    \Defender::stop();
                    addNotice('danger', $this->locale['fields_0107']);
                }
            }

            if (\Defender::safe()) {
                $field_arrays = fieldgenerator($new_table);
                // Alter DB_USER_FIELDS table - add column.
                // Checking for database registered users.
                if (!in_array($data['field_name'], $field_arrays)) { // safe to execute alter.
                    if (!empty($data['field_name']) && !empty($field_attr)) {
                        self::add_column($new_table, $data['field_name'], $field_attr);
                        //print_p("ALTER TABLE ".$new_table." ADD ".$data['field_name']." ".$field_attr);
                    }
                } else {
                    \Defender::stop();
                    addNotice('danger', $this->locale['fields_0106']);
                }
                // ordering
                if (\Defender::safe()) {
                    dbquery("UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'");
                    dbquery_insert(DB_USER_FIELDS, $data, 'save');
                    addNotice('success', $this->locale['field_0204']);
                    redirect(FUSION_SELF.$aidlink);
                }
            }
        }
    }

    /* Single array output match against $db - use get_structureData before to populate $fields */
    private function dynamics_fieldinfo($type, $default_value) {
        $info = [
            'textbox'     => "VARCHAR(70) NOT NULL DEFAULT '".$default_value."'",
            'select'      => "VARCHAR(70) NOT NULL DEFAULT '".$default_value."'",
            'textarea'    => "TEXT NOT NULL",
            'tags'        => "TEXT NOT NULL",
            'checkbox'    => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'toggle'      => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'datepicker'  => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'location'    => "VARCHAR(70) NOT NULL DEFAULT '".$default_value."'",
            'colorpicker' => "VARCHAR(10) NOT NULL DEFAULT '".$default_value."'",
            'upload'      => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'hidden'      => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
            'address'     => "TEXT NOT NULL",
            'number'      => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'email'       => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'url'         => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
        ];

        return $info[$type];
    }

    /** Modules Form */
    private function display_module_form() {
        $aidlink = fusion_get_aidlink();
        $field_data = [];

        if (isset($_GET['action']) && $_GET['action'] == 'module_edit' && isset($_GET['module_id']) && isnum($_GET['module_id'])) {
            $result = dbquery("SELECT * FROM ".DB_USER_FIELDS." WHERE field_id=:mid", [':mid' => intval($_GET['module_id'])]);
            if (dbrows($result)) {
                $field_data = dbarray($result);

                $_GET['install'] = $field_data['field_name'];

            } else {
                addNotice('info', $this->locale['field_0205']);
                redirect(FUSION_SELF.$aidlink.'&amp;ref='.$_GET['ref'].'&amp;action='.$_GET['action']);
            }
        }

        // is adding.
        $plugin = $this->modules;
        $install_plugin = get("install");
        $ref_module = get('ref');
        $module_id = get('module_id', FILTER_VALIDATE_INT);

        if (isset($plugin[$install_plugin])) {

            $plugin_data = $plugin[$install_plugin];

            if (post('enable')) {

                $accepted_type = [
                    'public'     => 'file',
                    'preference' => 'preference',
                    'security'   => 'security'
                ];

                $sections = [
                    'file'       => 'public',
                    'preference' => 'preference',
                    'security'   => 'security'
                ];

                if (isset($accepted_type[$ref_module])) {

                    $this->field_data = [
                        'add_module'         => sanitizer('add_module') ?: $this->field_data['field_name'],
                        'field_type'         => $accepted_type[$ref_module],
                        'field_id'           => sanitizer('field_id', '', 'field_id') ?: get('module_id', FILTER_VALIDATE_INT) ?: 0,
                        'field_title'        => sanitizer('field_title', '', 'field_title'),
                        'field_name'         => sanitizer('field_name', '', 'field_name'),
                        'field_cat'          => sanitizer("field_cat", '', 'field_cat'),
                        'field_default'      => sanitizer("field_default", '', 'field_default'),
                        'field_error'        => sanitizer("field_error", '', 'field_error'),
                        'field_required'     => post("field_required") ? 1 : 0,
                        'field_registration' => post("field_registration") ? 1 : 0,
                        'field_log'          => post("field_log") ? 1 : 0,
                        'field_order'        => sanitizer("field_order", '0', 'field_order'),
                        'field_section'      => $ref_module,
                    ];
                    $this->field_data['field_name'] = str_replace(' ', '_', $this->field_data['field_name']); // make sure no space.
                    if (!$this->field_data['field_order']) {
                        $this->field_data['field_order'] = dbresult(dbquery("SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cat_id", [':cat_id' => $this->field_data['field_cat']]), 0) + 1;
                    }

                    $this->create_fields($this->field_data, 'module');
                }
            }

            if (!empty($field_data)) {
                $this->field_data = $field_data;
            }

            $this->field_data['add_module'] = $this->field_data['field_name'];
            if (post('add_module')) {
                $this->field_data['add_module'] = sanitizer('add_module', '', 'add_module');
            }

            $html = openform('fieldform', 'post');
            $html .= "<div><strong>".$plugin_data['user_field_name']."</strong></div>\n";
            if (!empty($plugin_data['user_field_desc'])) {
                $html .= "<div class='spacer-xs m-t-0'>".$plugin_data['user_field_desc']."</div>";
            }
            $html .= "<div>\n";
            $html .= "<span class='m-b-10 strong'>".$this->locale['fields_0400']."</span><br/>\n<br/>\n";
            $html .= "<span class='strong'>".$this->locale['fields_0401']."</span> ".($plugin_data['user_field_version'] ?: $this->locale['fields_0402'])."<br/>\n";
            $html .= "<span class='strong'>".$this->locale['fields_0403']."</span> ".($plugin_data['user_field_dbname'] ?: $this->locale['fields_0404'])."<br/>\n";
            $html .= "<span class='strong'>".$this->locale['fields_0405']."</span> ".($plugin_data['user_field_dbinfo'] ?: $this->locale['fields_0406'])."<br/>\n";

            $html .= "</div>\n<hr/>\n";

            $html .= form_select_tree('field_cat', $this->locale['fields_0410'], $this->field_data['field_cat'], [
                'no_root'      => TRUE,
                'disable_opts' => array_keys($this->page_list),
            ], DB_USER_FIELD_CATS, 'field_cat_name', 'field_cat_id', 'field_parent');

            $html .= form_text('field_order', $this->locale['fields_0414'], $this->field_data['field_order'], ['type' => 'number', 'inner_width' => '100px']);

            if (!empty($plugin_data['user_field_dbinfo'])) {
                if (version_compare($plugin_data['user_field_version'], "1.01.00", ">=")) {
                    $html .= form_checkbox('field_required', $this->locale['fields_0411'], $this->field_data['field_required'], ['reverse_label' => TRUE]);
                }
                if (version_compare($plugin_data['user_field_version'], "1.01.00", ">=")) {
                    $html .= form_checkbox('field_log', $this->locale['fields_0412'], $this->field_data['field_log'], ['reverse_label' => TRUE]);
                }
                $html .= form_checkbox('field_registration', $this->locale['fields_0413'], $this->field_data['field_registration'], ['reverse_label' => TRUE]);
            }

            $html .= form_hidden('add_module', '', $this->field_data['add_module']);
            $html .= form_hidden('field_name', '', $plugin_data['user_field_dbname']);
            $html .= form_hidden('field_title', '', $plugin_data['user_field_name']);
            // Where is this coming from?
            $html .= form_hidden('field_default', '', isset($user_field_default) ? $user_field_default : '');
            $html .= form_hidden('field_options', '', isset($user_field_options) ? $user_field_options : '');
            $html .= form_hidden('field_error', '', isset($user_field_error) ? $user_field_error : '');
            $html .= form_hidden('field_config', '', isset($user_field_config) ? $user_field_config : '');

            $html .= form_hidden('field_id', '', $this->field_data['field_id']);

            $html .= "<hr/>\n";

            $html .= form_button('enable',
                ($this->field_data['field_id'] ? $this->locale['fields_0415'] : $this->locale['fields_0416']),
                ($this->field_data['field_id'] ? $this->locale['fields_0415'] : $this->locale['fields_0416']),
                ['class' => 'btn-default m-r-10']);


            if ($module_id) {
                $html .= "<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."'>".$this->locale['cancel']."</a>";
            } else {
                $html .= "<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public&amp;action=new'>".$this->locale['cancel']."</a>";
            }

            $html .= closeform();

            return (string)$html;

        } else {
            addNotice('danger', $this->locale['fields_0109']);
            redirect(ADMIN.'user_fields.php'.$aidlink.'&amp;ref=public&amp;action=new');
        }
    }

    public function quantum_insert(array $data = []) {
        $quantum_fields = [];
        $infinity_ref = [];
        // bug fix: to get only the relevant fields on specific page.
        $field_list = flatten_array($this->fields);
        // to generate $infinity_ref and $quantum_fields as reference and validate the $_POST input value.
        foreach ($field_list as $field_id => $field_data) {
            if ($field_data['field_parent'] == $this->input_page) {
                $target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : DB_USERS;
                $target_index = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : 'user_id';
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
     * @param            $db
     * @param            $primary_key
     * @param bool|FALSE $callback_data
     *
     * @return array
     * @throws \Exception
     */
    public function return_fields_input($db, $primary_key, $callback_data = FALSE) {
        $output_fields = [];

        $field = flatten_array($this->fields);
        if ($callback_data == TRUE) {
            $output_fields[$db] = $this->callback_data;
        }

        if (is_array($field)) {
            foreach ($field as $arr => $field_data) {
                $target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;
                $col_name = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : $primary_key;
                // Find index primary key value
                $primaryKeyVal = isset($_POST[$col_name]) ? form_sanitizer($_POST[$col_name], '', $col_name) : '';
                if (!isset($output_fields[$target_database][$col_name])) {
                    $output_fields[$target_database][$col_name] = $primaryKeyVal;
                }
                // Set input as default if posted but blank
                if (isset($_POST[$field_data['field_name']])) {
                    $output_fields[$target_database][$field_data['field_name']] =
                        form_sanitizer(
                            $_POST[$field_data['field_name']],
                            $field_data['field_default'],
                            $field_data['field_name']
                        );
                }
            }
        }
        $this->output_fields = $output_fields;

        return $this->output_fields;
    }

    /**
     * Logs the user actions
     *
     * @param $db
     * @param $primary_key
     *
     * @throws \Exception
     */
    public function log_user_action($db, $primary_key) {
        if (\Defender::safe()) {
            $output_fields = [];
            $field = flatten_array($this->fields);
            $output_fields[$db] = $this->callback_data;
            foreach ($field as $arr => $field_data) {
                $target_db = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;
                $col_name = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : $primary_key;
                $index_value = isset($_POST[$col_name]) ? form_sanitizer($_POST[$col_name], 0) : '';
                $old_cache = isset($this->callback_data[$field_data['field_name']]) ? $this->callback_data[$field_data['field_name']] : '';
                $new_val = isset($this->output_fields[$target_db][$field_data['field_name']]) ? $this->output_fields[$target_db][$field_data['field_name']] : '';
                if ($field_data['field_log'] && $new_val && $new_val != $old_cache) {
                    save_user_log($index_value, $field_data['field_name'], $new_val, $old_cache);
                }
            }
        }
    }

}

class Preference implements TableSDK {


    public function data() {
        return [
            'table'      => DB_USER_FIELDS,
            'id'         => 'field_id',
            'title'      => 'field_title',
            'conditions' => "field_type='preference'",
            'limit'      => 24,
        ];
    }

    public function properties() {
        return [
            'no_record'    => 'There are no Preference Field Plugins installed.',
            'search_label' => 'Search User Fields',
            'search_col'   => 'field_title',
            'order_col'    => ['field_title' => 'title', 'field_order' => 'order'],
        ];
    }

    public function column() {
        return [
            'field_title' => [
                'title'       => 'User Field',
                'title_class' => 'col-xs-11',
                'value_class' => 'no-break',
                'callback'    => ['PHPFusion\\UserFieldsQuantum', 'get_field_description', CLASSES.'PHPFusion/UserFieldsQuantum.inc'],
                'edit_link'   => FALSE,
                'delete_link' => FALSE,
            ],
            'field_order' => [
                'title'       => 'Order',
                'title_class' => 'col-xs-2',
            ],
            'field_id'    => [
                'title' => 'ID',
            ],
        ];
    }

    /**
     * @return array
     */
    public function quickEdit() {
        return [
            'field_title' => ['title' => 'Field Name', 'required' => TRUE],
            'field_order' => ['title' => 'Field Order', 'dynamics' => 'form_textarea'],
        ];
    }
}

class Security implements TableSDK {

    public function data() {
        return [
            'table'      => DB_USER_FIELDS,
            'id'         => 'field_id',
            'title'      => 'field_title',
            'conditions' => "field_type='security'",
            'limit'      => 24,
        ];

    }

    public function properties() {
        return [
            'no_record'    => 'There are no Security Field Plugins installed.',
            'search_label' => 'Search User Fields',
            'search_col'   => 'field_title',
            'order_col'    => ['field_title' => 'title', 'field_order' => 'order'],
        ];
    }

    public function column() {
        return [
            'field_title' => [
                'title'       => 'User Field',
                'title_class' => 'col-xs-11',
                'value_class' => 'no-break',
                'callback'    => ['PHPFusion\\UserFieldsQuantum', 'get_field_description', CLASSES.'PHPFusion/UserFieldsQuantum.inc'],
                'edit_link'   => FALSE,
                'delete_link' => FALSE,
            ],
            'field_order' => [
                'title'       => 'Order',
                'title_class' => 'col-xs-2',
            ],
            'field_id'    => [
                'title' => 'ID',
            ],
        ];
    }

    /**
     * Every row of the array is a field input.
     *
     * @return array
     */
    public function quickEdit() {
        return [
            'field_title' => ['title' => 'Field Name', 'required' => TRUE, 'function' => 'form_text'],
            'field_order' => ['title' => 'Field Order', 'function' => 'form_textarea'],
        ];
    }
}
