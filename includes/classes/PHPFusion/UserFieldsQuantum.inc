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

use PHPFusion\UserFields\Quantum\DisplayFields;
use PHPFusion\UserFields\Quantum\QuantumActions;
use PHPFusion\UserFields\Quantum\QuantumHelper;
use PHPFusion\UserFields\Quantum\QuantumModules;
use PHPFusion\UserFields\Quantum\QuantumModulesView;
use PHPFusion\UserFields\Quantum\Table\Preference;
use PHPFusion\UserFields\Quantum\Table\Security;

class UserFieldsQuantum extends QuantumActions {
    /**
     * Set the Quantum System Fields Page Title
     */
    protected $title = '';
    public $is_admin_panel = FALSE;
    protected static $helper;
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
    public $plugin_folder = NULL;
    protected $plugin_locale_folder = NULL;
    /**
     * Set as `display` to show array values output
     * Two methods - input or display
     *
     * @var string
     */
    public $method = 'input';
    /**
     * feed $userData or $data here to append display_fields() values
     * use the setter function setCallbackData()
     *
     * @var array
     */
    protected $callback_data = [];
    // callback on the structure - use getters
    protected $fields = []; // maybe can mix with enabled_fields.

    // debug mode
    protected $debug = FALSE;

    // System Internals
    private $input_page = 1;
    private $field_max_rows = 0;
    private $locale = [];

    protected $page = [];
    private $enabled_fields = [];

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

    private static $available_field_info = [];

    private static $module_list_var = [];

    private static $modules = [];

    public function __construct() {
        self::$helper = new QuantumHelper();
    }

    /**
     * Returns all the installed modules
     *
     * @return array
     */
    public function getModules() {
        return self::$modules;
    }

    /**
     * Returns only ROOT category
     *
     * @return array
     */
    public function getPageList() {
        return $this->page_list;
    }

    /**
     * Returns modules not installed within the system
     *
     * @return array
     */
    public function getAvailableFieldInfo() {
        return self::$available_field_info;
    }

    /**
     * Load all the modules variables
     */
    protected function cacheModulePlugins() {
        $quantum = new QuantumModules( $this );
        self::$module_list_var = $quantum->getModulesList();
        self::$available_field_info = $quantum->getAvailableModulesInfo();
        self::$modules = $quantum->getLoadedModules();
    }

    /**
     * `input` renders field.
     * `display` renders data
     *
     * @param string $method ('input' or 'display')
     */
    public function setMethod( $method ) {
        $this->method = $method;
    }

    /**
     * Set Quantum system locale
     *
     * @param $locale
     */
    public function setLocale( $locale ) {
        $this->locale = $locale;
    }

    /**
     * Give your Quantum based system a name. Will add to breadcrumbs if available.
     *
     * @param string $value
     */
    public function setTitle( $value ) {
        $this->title = $value;
    }

    ### Getters ####

    /**
     * Additional data-id referencing.
     * $userdata for instance.
     *
     * @param array $callback_data
     */
    public function setCallbackData( $callback_data ) {
        $this->callback_data = $callback_data;
    }

    /**
     * The internal admin rights by a user to use this system.
     * if specified, to lock down to certain user rights.
     *
     * @param string $admin_rights
     */
    public function setAdminRights( $admin_rights ) {
        $this->admin_rights = $admin_rights;
    }

    /**
     * Returns ALL Categories
     *
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
    public function getFields( $key = NULL ) {
        return ( isset( $this->fields[ $key ] ) ) ? (array)$this->fields[ $key ] : (array)$this->fields;
    }

    ### Loaders ####
    /* Read into serialized field label and returns the value */

    public function displayQuantumAdmin() {
        define( 'IN_QUANTUM', TRUE );

        pageAccess( $this->admin_rights );

        $this->loadUserFields(); // return fields

        $this->cacheModulePlugins();

        $refs = get( 'ref' );

        $refs = $refs && in_array( $refs, [ 'preferences', 'security' ] ) ? $refs : 'public';

        $this->is_admin_panel = TRUE;

        if ( $this->title ) {
            add_breadcrumb( [ 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink(), 'title' => $this->title ] );
            add_to_title( $this->title.' | ' );
        }

        if ( $this->method == 'input' ) {

            $this->loadUserFieldCats(); // return cat

            $this->doAction();
        }

        $this->view( $refs );
    }

    private function view( $refs ) {
        $action = get( 'action' );
        $quantum_modules = new QuantumModulesView( $this );
        switch ( $refs ) {
            default:
            case 'public':
                add_breadcrumb( [ 'title' => 'Public Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public' ] );
                $content = $this->viewPublicAdmin();
                if ( $action ) {
                    switch ( $action ) {
                        case 'new':
                            $add_field = get( 'add_field' );
                            $field_types = $this->get_dynamics_type();
                            if ( $add_field && in_array( $add_field, array_flip( $field_types ) ) ) {
                                $title = "Add new ".$field_types[ $add_field ];
                                $modal = openmodal( 'dynamics', "<h4 class='m-0'>$title</h4>", [ 'static' => TRUE ] );
                                $modal .= $this->quantum_dynamics_form();
                                $modal .= closemodal();
                                add_to_footer( $modal );
                            }
                            $content = $quantum_modules->viewModules( 'public' );

                            break;
                        case 'field_edit':
                            $field_id = get( 'field_id', FILTER_VALIDATE_INT );
                            if ( $field_id && isnum( $field_id ) ) {
                                add_breadcrumb( [ 'title' => 'Manage User Public Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public&amp;action=new' ] );
                                $title = "Edit User Fields";
                                $modal = openmodal( 'dynamics', "<h4 class='m-0'>$title</h4>", [ 'static' => TRUE ] );
                                $modal .= $this->quantum_dynamics_form();
                                $modal .= closemodal();
                                add_to_footer( $modal );
                            }
                            break;
                        case 'new_cat':
                        case 'cat_edit':
                            $title = "Add New Field Category";
                            $cat_id = get( 'cat_id', FILTER_VALIDATE_INT );
                            if ( $cat_id ) {
                                add_breadcrumb( [ 'title' => 'Manage User Public Category', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public&amp;action='.$action ] );
                                $title = "Edit Category";
                            }
                            $modal = openmodal( 'cat', "<h4 class='m-0'>$title</h4>", [ 'static' => TRUE ] );
                        $modal .= $this->quantumCategoryForm();
                            $modal .= closemodal();
                            add_to_footer( $modal );
                            break;
                        case 'module_edit':
                            // Module Edit
                            $module_id = get( 'module_id', FILTER_VALIDATE_INT );
                            if ( $module_id ) {
                                add_breadcrumb( [ 'title' => 'Manage Module User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=public&amp;action=module_edit&amp;module_id='.$module_id ] );
                                $title = "Edit Module User Fields";
                                $modal = openmodal( 'cat', "<h4 class='m-0'>$title</h4>", [ 'static' => TRUE ] );
                                $modal .= $quantum_modules->viewModulesForm();
                                $modal .= closemodal();
                                add_to_footer( $modal );
                            }
                            break;
                    }
                }
                break;
            case 'preferences':
                add_breadcrumb( [ 'title' => 'Preference User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preferences' ] );
                $content = $this->viewPreferenceAdmin();
                if ( $action == 'new' ) {
                    add_breadcrumb( [ 'title' => 'Manage Preference User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=preferences&amp;action=new' ] );

                    $content = $quantum_modules->viewModules( 'preferences' );
                }
                break;
            case 'security':
                add_breadcrumb( [ 'title' => 'Security User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security' ] );
                $content = $this->viewSecurityAdmin();
                if ( $action == 'new' ) {
                    add_breadcrumb( [ 'title' => 'Manage Security User Fields', 'link' => ADMIN.'user_fields.php'.fusion_get_aidlink().'&amp;ref=security&amp;action=new' ] );

                    $content = $quantum_modules->viewModules( 'security' );
                }
                break;
        }

        opentable( $this->title );
        echo $content;
        closetable();
    }


    public function setPluginFolder( $folder ) {
        if ( !$folder ) {
            $folder = get( 'section' );
            if ( defined( 'IN_QUANTUM' ) ) {
                $folder = get( 'ref' );
            }
        }
        $folder = $folder && in_array( $folder, [ 'preferences', 'security' ] ) ? $folder : 'public';
        $this->plugin_folder = [ INCLUDES.'user_fields/'.$folder.'/', INFUSIONS ];

        return $folder;
    }

    /**
     * Type can be common, preference, security
     *
     * @param string $folder
     */
    public function loadUserFields( $folder = '' ) {

        $folder = $this->setPluginFolder( $folder );

        $this->page = dbquery_tree_full( DB_USER_FIELD_CATS, "field_cat_id", "field_parent", "ORDER BY field_cat_order ASC" );

        $param = [];
        $cond = '';
        if ( $folder === 'public' ) {
            // Public conditions
            $section = get( 'section', FILTER_VALIDATE_INT );
            if ( !$section || !isset( $this->page[ $section ] ) ) {
                $section = 1;
            }
            $param = [
                ':index00' => (int)$section,
                ':index01' => (int)$section,
            ];
            $cond = "WHERE (cat.field_cat_id=:index00 OR root.field_cat_id=:index01)";
        }
        // for registration
        if ( $this->registration === TRUE ) {
            $cond .= ' AND field.field_registration=:fieldreg';
            $param += [ ':fieldreg' => 1 ];
        }
        switch ( $folder ) {
            default:
            case 'public':
                $cond .= ( defined( 'ADMIN_PANEL' ) ? ' OR' : ' AND' ).' field_section=:type00';
                $param += [ ':type00' => 'public' ];
                break;
            case 'security':
                $cond .= ' AND field_section=:type00';
                $param += [ ':type00' => 'security' ];
                break;
            case 'preferences':
                $cond .= ' WHERE field_section=:type00';
                $param += [ ':type00' => 'preferences' ];
                break;
        }

        $sql = "SELECT field.*, cat.field_cat_id, cat.field_cat_name, cat.field_parent, cat.field_cat_class, root.field_cat_id 'page_id', root.field_cat_name 'page_name', root.field_cat_db, root.field_cat_index
        FROM ".DB_USER_FIELDS." field
        LEFT JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
        LEFT JOIN ".DB_USER_FIELD_CATS." root ON (root.field_cat_id = cat.field_parent)
        $cond
        ORDER BY root.field_cat_order ASC, cat.field_cat_order ASC, field.field_order ASC
        ";

        $result = dbquery( $sql, $param );

        if ( $this->field_max_rows = dbrows( $result ) ) {
            while ( $data = dbarray( $result ) ) {
                $this->fields[ $data['field_cat'] ][ $data['field_id'] ] = $data;
            }
        }
    }

    /**
     * Logs the user actions
     *
     * @param $db
     * @param $primary_key
     *
     * @throws \Exception
     */
    public function log_user_action( $db, $primary_key ) {
        if ( fusion_safe() ) {
            $output_fields = [];
            $field = flatten_array( $this->fields );
            $output_fields[ $db ] = $this->callback_data;
            foreach ( $field as $arr => $field_data ) {
                $target_db = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;
                $col_name = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : $primary_key;
                $index_value = isset( $_POST[ $col_name ] ) ? form_sanitizer( $_POST[ $col_name ], 0 ) : '';
                $old_cache = isset( $this->callback_data[ $field_data['field_name'] ] ) ? $this->callback_data[ $field_data['field_name'] ] : '';
                $new_val = isset( $this->output_fields[ $target_db ][ $field_data['field_name'] ] ) ? $this->output_fields[ $target_db ][ $field_data['field_name'] ] : '';
                if ( $field_data['field_log'] && $new_val && $new_val != $old_cache ) {
                    save_user_log( $index_value, $field_data['field_name'], $new_val, $old_cache );
                }
            }
        }
    }

    public function getEnabledFields() {
        if ( empty( $this->enabled_fields ) ) {
            $result = dbquery( "SELECT field_id, field_name, field_cat, field_required, field_log, field_registration, field_order, field_type, field_cat_name
                    FROM ".DB_USER_FIELDS." tuf
                    LEFT JOIN ".DB_USER_FIELD_CATS." tufc ON tuf.field_cat = tufc.field_cat_id
                    WHERE (field_type = 'file' OR field_cat=0)
                    ORDER BY field_cat_order ASC, field_order ASC" );
            if ( dbrows( $result ) ) {
                while ( $data = dbarray( $result ) ) {
                    $this->enabled_fields[] = $data['field_name'];
                }
            }
        }

        return $this->enabled_fields;
    }

    /**
     * Public Profile Field Admin
     *
     * @return string
     * @throws \ReflectionException
     */
    private function viewPublicAdmin() {
        $aidlink = fusion_get_aidlink();
        $this->callback_data = fusion_get_userdata();
        $active = '';
        $new_button = "<a class='btn btn-default m-r-10' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public&amp;action=new'>Add New Field</a>";
        $new_button .= "<a class='btn btn-default' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public&amp;action=new_cat'>".$this->locale['fields_0311']."</a>";

        $html = "<h4 class='display-inline-block m-r-10'>Public User Fields</h4>\n$new_button<hr/>\n";

        $current_field_id = get( 'field_id', FILTER_VALIDATE_INT );
        $current_module_id = get( 'module_id', FILTER_VALIDATE_INT );
        $cat_id = get( 'cat_id', FILTER_VALIDATE_INT );

        if ( !empty( $this->page[0] ) ) {
            $tab_title = [];
            foreach ( $this->page[0] as $page_id => $page_data ) {
                $tab_title['title'][ $page_id ] = fusion_parse_locale( $page_data['field_cat_name'] );
                $tab_title['id'][ $page_id ] = $page_id;
                $tab_title['icon'][ $page_id ] = $page_data['field_cat_class'];
                $active = ( $cat_id && $page_id == $cat_id ) ? $page_id : '';
            }

            if ( $current_field_id || $current_module_id ) {
                $_fields = flatten_array( $this->fields );

                foreach ( $_fields as $fData ) {
                    if ( $current_field_id ) {
                        if ( $fData['field_id'] == $current_field_id ) {
                            $fieldCat = $fData['field_cat'];
                            $active = ( $current_field_id ? get_root( $this->field_cat_index, $fieldCat ) : 0 );
                            break;
                        }
                    } else if ( $current_module_id ) {
                        if ( $fData['field_id'] == $current_module_id ) {
                            $fieldCat = $fData['field_cat'];
                            $active = ( $current_module_id ? get_root( $this->field_cat_index, $fieldCat ) : 0 );
                            break;
                        }
                    }
                }
            }
            reset( $tab_title['title'] );
            $default_active = key( $tab_title['title'] );

            // Tab
            $tab_active = tab_active( $tab_title, ( $active ? $active : $default_active ) );
            $html .= opentab( $tab_title, $tab_active, 'uftab', FALSE, '', '', [], TRUE );

            foreach ( $this->page[0] as $page_id => $page_details ) {

                $html .= opentabbody( $tab_title['title'][ $page_id ], $tab_title['id'][ $page_id ], $tab_active );
                // load all categories here.
                //if ( $this->debug ) {
                //    $html .= "<div class='m-t-20 text-dark'>\n";
                //    if ( $page_id == 1 ) {
                //        $html .= sprintf( $this->locale['fields_0100'], DB_USERS );
                //    } else {
                //        $html .= sprintf( $this->locale['fields_0101'], $page_details['field_cat_db'], $page_details['field_cat_index'] );
                //    }
                //    $html .= "</div>\n";
                //}

                // Edit/Delete Category Administration
                $html .= "<div class='clearfix'>\n";
                $html .= "<div class='pull-right'>\n";
                $html .= "<div class='btn-group m-b-10 m-t-10'>\n";
                $html .= "<a class='btn btn-default' href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$page_id."'>".$this->locale['fields_0308']."</a>";
                $html .= "<a class='btn btn-danger' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$page_id."'>".$this->locale['fields_0313']."</a>";
                $html .= "</div>\n";
                $html .= "</div>\n";
                $html .= "</div>\n";

                $cat_html = "<div class='text-center'><h4>".$this->locale['fields_0102'].fusion_parse_locale( $page_details['field_cat_name'] )."</h4></div>\n";
                if ( isset( $this->page[ $page_id ] ) ) {
                    $rowcount = 0;
                    $counter = count( $this->page[ $page_id ] ) - 1;
                    $cat_html = "<div class='clearfix'>\n";
                    foreach ( $this->page[ $page_id ] as $cat_id => $field_cat ) {
                        $move_cat_link = "<span class='text-lighter'>".$this->locale['move_up']."</span> &middot; ";
                        if ( $rowcount != 0 ) {
                            $move_cat_link = "<a href='".FUSION_SELF.$aidlink."&amp;action=cmu&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".( $field_cat['field_cat_order'] - 1 )."'>".$this->locale['move_up']."</a> &middot; ";
                        }
                        $move_cat_link2 = "<span class='text-lighter'>".$this->locale['move_down']."</span> &middot; ";
                        if ( $rowcount !== $counter ) {
                            $move_cat_link2 = "<a href='".FUSION_SELF.$aidlink."&amp;action=cmd&amp;cat_id=".$cat_id."&amp;parent_id=".$field_cat['field_parent']."&amp;order=".( $field_cat['field_cat_order'] + 1 )."'>".$this->locale['move_down']."</a> &middot; ";
                        }
                        // field category information
                        //print_P($field_cat);
                        $cat_html .= "<div class='list-group'>\n";
                        $cat_html .= "<div class='list-group-item'>\n";
                        $cat_html .= "<h4 class='display-block m-0'>".fusion_parse_locale( $field_cat['field_cat_name'] )."</h4>";
                        $cat_html .= "<div class='display-block'>\n";
                        $cat_html .= $move_cat_link.$move_cat_link2;
                        $cat_html .= "<a href='".FUSION_SELF.$aidlink."&amp;action=cat_edit&amp;cat_id=".$cat_id."'>".$this->locale['edit']."</a> &middot; ";
                        $cat_html .= "<a class='text-danger' href='".FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$cat_id."'>".$this->locale['delete']."</a>";
                        $cat_html .= "</div>\n";
                        $cat_html .= "</div>\n<div class='list-group-item'>\n";

                        // print all fields.
                        $field_html = "There are no fields";
                        if ( isset( $this->fields[ $cat_id ] ) ) {
                            $k = 0;
                            $field_html = '';
                            $item_counter = count( $this->fields[ $cat_id ] ) - 1;

                            foreach ( $this->fields[ $cat_id ] as $arr => $field_data ) {

                                $field_output = $this->displayFields( $field_data, $this->callback_data, $this->method, [ 'inline' => TRUE, 'show_title' => 1, 'hide_value' => 1 ] );

                                $move_link = "<span class='text-lighter'>".$this->locale['move_up']."</span> &middot; ";
                                if ( $k != 0 ) {
                                    $move_link = "<a href='".FUSION_SELF.$aidlink."&amp;action=fmu&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".( $field_data['field_order'] - 1 )."'>".$this->locale['move_up']."</a> &middot; ";
                                }
                                $move_link_2 = "<span class='text-lighter'>".$this->locale['move_down']."</span> &middot; ";
                                if ( $k !== $item_counter ) {
                                    $move_link_2 = "<a href='".FUSION_SELF.$aidlink."&amp;action=fmd&amp;parent_id=".$field_data['field_cat']."&amp;field_id=".$field_data['field_id']."&amp;order=".( $field_data['field_order'] + 1 )."'>".$this->locale['move_down']."</a> &middot; ";
                                }
                                $edit_link = "<a href='".FUSION_SELF.$aidlink."&amp;action=field_edit&amp;field_id=".$field_data['field_id']."&amp;ref=public'>".$this->locale['edit']."</a> &middot; ";
                                if ( $field_data['field_type'] == 'file' ) {
                                    $edit_link = "<a href='".FUSION_SELF.$aidlink."&amp;action=module_edit&amp;module_id=".$field_data['field_id']."&amp;ref=public'>".$this->locale['edit']."</a> &middot; ";
                                }

                                //Fields - Move down/Move Up - Edit - Delete
                                //$item_id = $current_module_id ?: 0;
                                //if ( !$item_id ) {
                                //    $item_id = $current_field_id ?: 0;
                                //}

                                $field_html .= "<div class='".grid_row()."'>\n<div class='".grid_column_size( 100, 70 )."'>\n";
                                $field_html .= $field_output;
                                $field_html .= "</div><div class='".grid_column_size( 100, 30 )."'>\n";
                                $field_html .= "<div class='text-right'>\n";
                                $field_html .= $move_link.$move_link_2.$edit_link;
                                $field_html .= "<a class='text-danger' href='".FUSION_SELF.$aidlink."&amp;action=field_delete&amp;field_id=".$field_data['field_id']."&amp;ref=public'>".$this->locale['delete']."</a>";
                                $field_html .= "</div>\n";
                                $field_html .= "</div>\n</div>\n";
                                $k++;
                            }
                        }
                        $cat_html .= $field_html;
                        $cat_html .= "</div>\n</div>\n";
                        $rowcount++;
                    }
                    $cat_html .= "</div>\n";
                }

                $html .= $cat_html;
                $html .= closetabbody();
            }
            $html .= closetab();
        } else {
            $html .= "<div class='well text-center'>".$this->locale['fields_0103']."</div>\n";
        }

        return (string)$html;
    }

    private function viewPreferenceAdmin() {
        $new_button = "<a class='btn btn-default' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=preferences&amp;action=new'>Add New</a>";
        $table = new Tables( new Preference(), FALSE );
        return $new_button.$table->displayTable();
    }

    private function viewSecurityAdmin() {
        $new_button = "<a class='btn btn-default' href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=security&amp;action=new'>Add New</a>";
        $table = new Tables( new Security(), FALSE );
        return $new_button.$table->displayTable();
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
    public function displayFields( array $data, $callback_data, $method = 'input', array $options = [] ) {
        $fields = new DisplayFields( $this );
        if ( $data['field_type'] == 'file' && !empty( self::$module_list_var[ $data['field_name'] ]['include'] ) ) {
            $options['field_file'] = self::$module_list_var[ $data['field_name'] ]['include'];
        }
        return $fields->displayFields( $data, $callback_data, $method, $options );
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
    private function quantumCategoryForm() {
        $aidlink = fusion_get_aidlink();
        add_to_jquery( "$('#field_parent').val() == '0' ? $('#page_settings').show() : $('#page_settings').hide(); $('#field_parent').bind('change', function() { $(this).val() == '0' ? $('#page_settings').show() : $('#page_settings').hide()});" );

        $action = get( 'action' );
        $cat_id = get( 'cat_id', FILTER_VALIDATE_INT );

        if ( $action == 'cat_edit' && $cat_id && self::validate_fieldCat( $cat_id ) ) {

            $result = dbquery( "SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cid", [ ':cid' => (int)$cat_id ] );
            if ( dbrows( $result ) ) {
                $this->field_cat_data = dbarray( $result );
            } else {
                addNotice( 'danger', $this->locale['field_0206'] );
                redirect( FUSION_SELF.$aidlink );
            }
        }

        if ( $data = $this->updateCategory() ) {
            $this->field_cat_data = $data;
        }

        $html = openform( 'quantumCategoryFrm', 'post' );
        $html .= self::quantum_multilocale_fields( 'field_cat_name', $this->locale['fields_0430'], $this->field_cat_data['field_cat_name'], [ 'required' => 1 ] );
        // cannot move if there are siblings
        if ( $cat_id && isset( $this->field_cat_index[ $cat_id ] ) ) {
            $html .= form_hidden( 'field_parent', '', $this->field_cat_data['field_parent'] );
        } else {

            $cat_list[] = get( 'cat_id', FILTER_VALIDATE_INT );
            if ( !empty( $this->cat_list ) ) {
                foreach ( $this->cat_list as $id => $value ) {
                    $cat_list[] = $id;
                }
            }
            $html .= form_select( 'field_parent', $this->locale['fields_0431'], $this->field_cat_data['field_parent'],
                [
                    'db'           => DB_USER_FIELD_CATS,
                    'id_col'       => 'field_cat_id',
                    'cat_col'      => 'field_parent',
                    'title_col'    => 'field_cat_name',
                    'parent_value' => $this->locale['fields_0432'],
                    'optgroup'     => TRUE,
                    'disable_opts' => $cat_list,
                    'select_alt'   => TRUE,
                ] );
        }

        $html .= form_text( 'field_cat_order', $this->locale['fields_0433'], $this->field_cat_data['field_cat_order'], [ 'type' => 'number', 'inner_width' => '100px' ] );
        $html .= form_hidden( 'field_cat_id', '', $this->field_cat_data['field_cat_id'], [ 'number' => 1 ] );
        $html .= form_hidden( 'add_cat', '', 'add_cat' );
        // root settings
        $html .= "<div id='page_settings' class='m-t-20'>\n";

        global $db_name;
        $tableList = [];
        $show_tables = dbquery( "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='".$db_name."'" );
        if ( dbrows( $show_tables ) > 0 ) {
            while ( $schData = dbarray( $show_tables ) ) {
                $table_name = str_replace( DB_PREFIX, '', $schData['TABLE_NAME'] );
                $tableList[ $table_name ] = $table_name;
            }
        }

        $html .= '<div class="m-b-20">'.$this->locale['fields_0111'].'<br/><small class="m-b-20">'.$this->locale['fields_0112'].'</small></div>';

        $html .= form_select( 'field_cat_db', sprintf( $this->locale['fields_0434'], " db_prefix_ " ),
            $this->field_cat_data['field_cat_db'], [
                'placeholder' => $this->locale['fields_0663'],
                "inline"      => FALSE,
                //"deactivate"  => FALSE, //$this->field_cat_data['field_cat_db'] ? TRUE : FALSE,
                'select_alt'  => TRUE,
                "options"     => $tableList
            ] );
        $html .= "<div class='text-smaller m-b-10'>".$this->locale['fields_0113']."</div>\n";

        $html .= form_text( 'field_cat_index', $this->locale['fields_0435'], $this->field_cat_data['field_cat_index'],
            [
                'placeholder' => 'user_id',
                "required"    => FALSE,
                "inline"      => FALSE
            ] );

        $html .= form_text( 'field_cat_class', $this->locale['fields_0436'], $this->field_cat_data['field_cat_class'],
            [
                'placeholder' => $this->locale['fields_0437'],
                "inline"      => FALSE
            ] );
        $html .= form_hidden( 'add_cat', '', 'add_cat' );
        $html .= "</div>\n";
        $html .= "<hr/>\n";
        $html .= form_button( 'save_cat', $this->locale['fields_0318'], 'save_cat', [ 'class' => 'm-r-10 btn-primary' ] );
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
    public static function quantum_multilocale_fields( $input_name, $title, $input_value, array $options = [] ) {
        $locale = fusion_get_locale();
        $html = '';
        $language_opts = fusion_get_enabled_languages();
        $input_value = QuantumHelper::isSerialized( $input_value ) ? unserialize( $input_value ) : $input_value;
        $options += [
            'function'    => !empty( $options['textarea'] ) && $options['textarea'] == 1 ? 'form_textarea' : 'form_text',
            // only 2 fields type need a multiple locale logically
            'required'    => !empty( $options['required'] ) && $options['required'] == 1 ? '1' : '0',
            'placeholder' => !empty( $options['placeholder'] ) ? $options['placeholder'] : '',
            'deactivate'  => !empty( $options['deactivate'] ) && $options['deactivate'] == 1 ? '1' : '0',
            'width'       => !empty( $options['width'] ) ? $options['width'] : '100%',
            'class'       => !empty( $options['class'] ) ? $options['class'] : '',
            'inline'      => !empty( $options['inline'] ) ? $options['inline'] : '',
            'max_length'  => !empty( $options['max_length'] ) ? $options['max_length'] : '200',
            'error_text'  => !empty( $options['error_text'] ) ? $options['error_text'] : '',
            'safemode'    => !empty( $options['safemode'] ) && $options['safemode'] == 1 ? '1' : '0',
            'icon'        => !empty( $options['icon'] ) ? $options['icon'] : '',
            'input_id'    => !empty( $options['input_id'] ) ? $options['input_id'] : $input_name,
        ];
        $required = $options['required'];
        $html .= "<div id='".$options['input_id']."-field' class='form-group clearfix".( $options['class'] ? ' '.$options['class'] : '' ).( $options['icon'] ? ' has-feedback' : '' )."'>\n";
        $html .= ( $title ) ? "<label class='control-label ".( $options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '' )."'>$title ".( $options['required'] == 1 ? "<span class='required'>*</span>" : '' )."</label>\n" : '';
        $html .= ( $options['inline'] ) ? "<div class='col-xs-12 ".( $title ? "col-sm-9 col-md-9 col-lg-9 p-l-15" : "col-sm-12 col-md-12 col-lg-12" )."'>\n" : "<div class='p-t-5 p-b-0'>\n";
        $main_html = '';
        $sub_html = '';
        foreach ( $language_opts as $lang => $langNames ) {
            $options['field_title'] = $title." (".$langNames.")";
            $options['input_id'] = $input_name."-".$lang;
            if ( $lang == LANGUAGE ) {
                $options['required'] = $required;
                $options['prepend_value'] = $langNames;
                // Fix this
                $main_html .= $options['function']( $input_name."[$lang]", "",
                    isset( $input_value[ $lang ] ) ? $input_value[ $lang ] : $input_value,
                    $options );
            } else {
                $options['required'] = 0;
                $options['prepend_value'] = $langNames;
                $sub_html .= $options['function']( $input_name."[$lang]", "",
                    isset( $input_value[ $lang ] ) ? $input_value[ $lang ] : '', $options );
            }
        }

        $html .= $main_html.$sub_html;

        if ( count( $language_opts ) > 1 ) {
            $html .= "<div class='dropdown m-b-15'>\n";
            $html .= "<button id='lang_dropdown' data-toggle='dropdown' class='dropdown-toggle btn btn-sm btn-default' type='button'>".$locale['add_language']." <span class='caret'></span></button>\n";
            $html .= "<ul class='dropdown-menu'>\n";
            foreach ( $language_opts as $Lang => $LangName ) {
                if ( $Lang !== LANGUAGE ) {
                    $html .= "<li><a data-add='$Lang' data-input='$input_name' data-locale='$LangName' class='pointer data-add'><i class='fa fa-plus-circle fa-fw'></i> $LangName</a></li>\n";
                    if ( $Lang !== LANGUAGE ) {
                        add_to_jquery( "$('#".$input_name."-".$Lang."-field').hide();" );
                    }
                }
            }
            $html .= "</ul>\n";
            $html .= "</div>\n";
            if ( !defined( 'QUANTUM_MULTILOCALE_FIELDS' ) ) {
                define( 'QUANTUM_MULTILOCALE_FIELDS', TRUE );
                add_to_jquery( "
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
                " );
            }
        }
        $html .= "</div>\n";
        $html .= "</div>\n";

        return $html;
    }

    /** The master form for Adding or Editing Dynamic Fields */
    private function quantum_dynamics_form() {

        $aidlink = fusion_get_aidlink();
        $action = get( 'action' );
        $field_id = get( 'field_id', FILTER_VALIDATE_INT );

        $this->field_data['field_type'] = get( 'add_field' );
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
        if ( $action == 'field_edit' && $field_id ) {
            if ( $this->validate_field( $field_id ) ) {
                $this->field_data = dbarray( dbquery( "SELECT * FROM ".DB_USER_FIELDS." WHERE field_id=:fid", [ ':fid' => (int)$field_id ] ) );
                if ( $this->field_data['field_type'] == 'upload' ) {
                    $this->field_data['field_config'] = fusion_decode( $this->field_data['field_config'] );
                    //print_p( $this->field_data );
                }
            } else {
                redirect( FUSION_SELF.$aidlink );
            }
        }

        //if ( post( 'save_field' ) ) {
        //
        //    $add_field = get( 'add_field' );
        //
        //    $this->field_data = [
        //        'field_type'         => $add_field ? $add_field : $this->field_data['field_type'],
        //        'field_id'           => sanitizer( 'field_id', '0', 'field_id' ),
        //        'field_title'        => form_sanitizer( $_POST['field_title'], '', 'field_title', TRUE ),
        //        'field_name'         => sanitizer( 'field_name', '', 'field_name' ),
        //        'field_cat'          => sanitizer( 'field_cat', '0', 'field_cat' ),
        //        'field_options'      => sanitizer( 'field_options', '', 'field_options' ),
        //        'field_default'      => sanitizer( 'field_default', '', 'field_default' ),
        //        'field_error'        => sanitizer( 'field_error', '', 'field_error' ),
        //        'field_required'     => isset( $_POST['field_required'] ) ? 1 : 0,
        //        'field_log'          => isset( $_POST['field_log'] ) ? 1 : 0,
        //        'field_registration' => isset( $_POST['field_registration'] ) ? 1 : 0,
        //        'field_order'        => sanitizer( 'field_order', '0', 'field_order' ),
        //        'field_section'      => 'public'
        //    ];
        //
        //    $this->field_data['field_name'] = str_replace( ' ', '_', $this->field_data['field_name'] ); // make sure no space.
        //
        //    if ( $this->field_data['field_type'] == 'upload' ) {
        //
        //        $max_b = isset( $_POST['field_max_b'] ) ? form_sanitizer( $_POST['field_max_b'], '', 'field_max_b' ) : 150000;
        //        $calc = isset( $_POST['field_calc'] ) ? form_sanitizer( $_POST['field_calc'], '', 'field_calc' ) : 1;
        //        $this->field_data['field_upload_type'] = isset( $_POST['field_upload_type'] ) ? form_sanitizer( $_POST['field_upload_type'], '', 'field_upload_type' ) : '';
        //        if ( !in_array( $this->field_data['field_upload_type'], [ 'file', 'image' ] ) ) {
        //            fusion_stop();
        //            addNotice( 'danger', $this->locale['fields_0108'] );
        //        } else {
        //            $this->field_data['field_config'] = [
        //                'field_upload_type'        => $this->field_data['field_upload_type'],
        //                'field_max_b'              => isset( $_POST['field_max_b'] ) && isset( $_POST['field_calc'] ) ? $max_b * $calc : $this->field_data['field_max_b'],
        //                'field_upload_path'        => isset( $_POST['field_upload_path'] ) ? form_sanitizer( $_POST['field_upload_path'], '', 'field_upload_path' ) : '',
        //                'field_valid_file_ext'     => isset( $_POST['field_valid_file_ext'] ) && $this->field_data['field_upload_type'] == 'file' ? form_sanitizer( $_POST['field_valid_file_ext'], '', 'field_valid_file_ext' ) : '',
        //                'field_valid_image_ext'    => isset( $_POST['field_valid_image_ext'] ) && $this->field_data['field_upload_type'] == 'image' ? form_sanitizer( $_POST['field_valid_image_ext'], '', 'field_valid_image_ext' ) : '',
        //                'field_image_max_w'        => isset( $_POST['field_image_max_w'] ) && $this->field_data['field_upload_type'] == 'image' ? form_sanitizer( $_POST['field_image_max_w'], '', 'field_image_max_w' ) : '',
        //                'field_image_max_h'        => isset( $_POST['field_image_max_h'] ) && $this->field_data['field_upload_type'] == 'image' ? form_sanitizer( $_POST['field_image_max_h'], '', 'field_image_max_h' ) : '',
        //                'field_thumbnail'          => isset( $_POST['field_thumbnail'] ) ? form_sanitizer( $_POST['field_thumbnail'], 0, 'field_thumbnail' ) : '',
        //                'field_thumb_upload_path'  => isset( $_POST['field_thumb_upload_path'] ) && $this->field_data['field_upload_type'] == 'image' && isset( $this->field_data['field_thumbnail'] ) ? form_sanitizer( $_POST['field_thumb_upload_path'], '', 'field_thumb_upload_path' ) : '',
        //                'field_thumb_w'            => isset( $_POST['field_thumb_w'] ) && $this->field_data['field_upload_type'] == 'image' && isset( $this->field_data['field_thumbnail'] ) ? form_sanitizer( $_POST['field_thumb_w'], '', 'field_thumb_w' ) : '',
        //                'field_thumb_h'            => isset( $_POST['field_thumb_h'] ) && $this->field_data['field_upload_type'] == 'image' && isset( $this->field_data['field_thumbnail'] ) ? form_sanitizer( $_POST['field_thumb_h'], '', 'field_thumb_h' ) : '',
        //                'field_thumbnail_2'        => isset( $_POST['field_thumbnail_2'] ) ? 1 : isset( $_POST['field_id'] ) ? 0 : $this->field_data['field_thumbnail_2'],
        //                'field_thumb2_upload_path' => isset( $_POST['field_thumb2_upload_path'] ) && $this->field_data['field_upload_type'] == 'image' && isset( $this->field_data['field_thumbnail_2'] ) ? form_sanitizer( $_POST['field_thumb2_upload_path'], '', 'field_thumb2_upload_path' ) : '',
        //                'field_thumb2_w'           => isset( $_POST['field_thumb2_w'] ) && $this->field_data['field_upload_type'] == 'image' && isset( $this->field_data['field_thumbnail_2'] ) ? form_sanitizer( $_POST['field_thumb2_w'], '', 'field_thumb2_w' ) : '',
        //                'field_thumb2_h'           => isset( $_POST['field_thumb2_h'] ) && $this->field_data['field_upload_type'] == 'image' && isset( $this->field_data['field_thumbnail_2'] ) ? form_sanitizer( $_POST['field_thumb2_h'], '', 'field_thumb2_h' ) : '',
        //                'field_delete_original'    => isset( $_POST['field_delete_original'] ) && $this->field_data['field_upload_type'] == 'image' ? 1 : 0
        //            ];
        //        }
        //    }
        //
        //    if ( !$this->field_data['field_order'] ) {
        //        $this->field_data['field_order'] = dbresult( dbquery( "SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cat_id", [ ':cat_id' => $this->field_data['field_cat'] ] ), 0 ) + 1;
        //    }
        //
        //    if ( fusion_safe() ) {
        //        if ( !empty( $this->field_data['field_config'] ) ) {
        //            $this->field_data['field_config'] = fusion_encode( $this->field_data['field_config'] );
        //
        //        }
        //
        //        $this->updateFields( $this->field_data, 'dynamics' ); // will redirect and refresh config
        //    }
        //
        //}

        if ( $data = $this->saveFields( $this->field_data['field_type'] ) ) {
            $this->field_data = $data;
        }

        $html = openform( 'fieldform', 'post' );

        $disable_opts = [];
        foreach ( $this->page_list as $index => $v ) {
            $disable_opts[] = $index;
        }
        // ok the value generated needs to be parsed by quantum
        //$html .= form_select_tree( 'field_cat', $this->locale['fields_0450'], $this->field_data['field_cat'], [
        //    'no_root'      => TRUE,
        //    'width'        => '100%',
        //    'disable_opts' => $disable_opts
        //], DB_USER_FIELD_CATS, 'field_cat_name', 'field_cat_id', 'field_parent' );
        //
        $html .= form_select( 'field_cat', $this->locale['fields_0410'], $this->field_data['field_cat'], [
            'no_root'      => TRUE,
            'db'           => DB_USER_FIELD_CATS,
            'disable_opts' => array_keys( $this->page_list ),
            'id_col'       => 'field_cat_id',
            'cat_col'      => 'field_parent',
            'title_col'    => 'field_cat_name',
            'optgroup'     => TRUE,
            'select_alt'   => TRUE,
            'parse_label'  => TRUE,
        ] );

        $html .= self::quantum_multilocale_fields( 'field_title', $this->locale['fields_0451'], $this->field_data['field_title'], [ 'required' => 1 ] );

        $html .= form_text( 'field_name', $this->locale['fields_0453'], $this->field_data['field_name'],
            [
                'placeholder' => $this->locale['fields_0454'],
                'required'    => TRUE
            ]
        );
        switch ( $this->field_data['field_type'] ) {
            case 'select':
                $html .= form_select( 'field_options', $this->locale['fields_0455'], $this->field_data['field_options'],
                    [
                        'required'    => TRUE,
                        'width'       => '100%',
                        'inner_width' => '100%',
                        'select_alt'  => TRUE,
                        'tags'        => TRUE,
                    ]
                );
                break;
            case 'upload':
                require_once( INCLUDES.'mimetypes_include.php' );
                $file_type_list = [];
                $file_image_list = [];
                foreach ( mimeTypes() as $file_ext => $occ ) {
                    if ( !in_array( $file_ext, array_flip( img_mimeTypes() ) ) ) {
                        $file_type_list[] = '.'.$file_ext;
                    }
                }
                foreach ( img_mimeTypes() as $file_ext => $occ ) {
                    $file_image_list[] = '.'.$file_ext;
                }
                $calc_opts = [
                    1       => $this->locale['fields_0490'],
                    1024    => $this->locale['fields_0491'],
                    1048576 => $this->locale['fields_0492']
                ];
                $calc_c = $this->calculate_byte( $this->field_data['field_config']['field_max_b'] );
                $calc_b = $this->field_data['field_config']['field_max_b'] / $calc_c;
                $file_upload_type = [
                    'file'  => $this->locale['fields_0456'],
                    'image' => $this->locale['fields_0489']
                ];
                $html .= form_select( 'field_upload_type', $this->locale['fields_0457'], $this->field_data['field_config']['field_upload_type'], [ "options" => $file_upload_type ] );
                $html .= form_text( 'field_upload_path', $this->locale['fields_0458'], $this->field_data['field_config']['field_upload_path'],
                    [
                        'placeholder' => $this->locale['fields_0459'],
                        'required'    => TRUE
                    ]
                );
                $html .= "<label for='field_max_b'>".$this->locale['fields_0460']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text( 'field_max_b', '', $calc_b, [
                    'class'    => 'm-b-0',
                    'type'     => 'number',
                    'required' => TRUE
                ] );
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
                $html .= form_select( 'field_calc', '', $calc_c, [ 'options' => $calc_opts, 'width' => '100%' ] );
                $html .= "</div>\n</div>\n";
                // File Type
                $html .= "<div id='file_type'>\n";
                $html .= form_select( 'field_valid_file_ext', $this->locale['fields_0461'], $this->field_data['field_config']['field_valid_file_ext'],
                    [
                        'options'     => $file_type_list,
                        'multiple'    => TRUE,
                        'tags'        => TRUE,
                        'required'    => TRUE,
                        'width'       => '100%',
                        'inner_width' => '100%'
                    ] );
                $html .= "</div>\n";
                // Image Type
                $html .= "<div id='image_type'>\n";
                $html .= form_select( 'field_valid_image_ext', $this->locale['fields_0462'], $this->field_data['field_config']['field_valid_image_ext'],
                    [
                        'options'  => $file_image_list,
                        'multiple' => TRUE,
                        'tags'     => TRUE,
                        'required' => TRUE
                    ] );
                $html .= "<label>".$this->locale['fields_0463']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text( 'field_image_max_w', $this->locale['fields_0464'], $this->field_data['field_config']['field_image_max_w'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ] );
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
                $html .= form_text( 'field_image_max_h', $this->locale['fields_0465'], $this->field_data['field_config']['field_image_max_h'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ] );
                $html .= "</div>\n</div>\n";
                $html .= form_checkbox( 'field_thumbnail', $this->locale['fields_0467'], $this->field_data['field_config']['field_thumbnail'], [ 'reverse_label' => TRUE ] );
                $html .= "<div id='field_t1'>\n";
                $html .= form_text( 'field_thumb_upload_path', $this->locale['fields_0468'],
                    $this->field_data['field_config']['field_thumb_upload_path'], [
                        'placeholder' => $this->locale['fields_0469'],
                        'required'    => 1
                    ] );
                $html .= "<label>".$this->locale['fields_0470']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text( 'field_thumb_w', $this->locale['fields_0471'], $this->field_data['field_config']['field_thumb_w'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ] );
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";
                $html .= form_text( 'field_thumb_h', $this->locale['fields_0472'], $this->field_data['field_config']['field_thumb_h'], [
                    'number'      => 1,
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => 1
                ] );
                $html .= "</div>\n</div>\n";
                $html .= "</div>\n";
                $html .= form_checkbox( 'field_thumbnail_2', $this->locale['fields_0473'], $this->field_data['field_config']['field_thumbnail_2'], [ 'reverse_label' => TRUE ] );
                $html .= "<div id='field_t2'>\n";
                $html .= form_text( 'field_thumb2_upload_path', $this->locale['fields_0474'], $this->field_data['field_config']['field_thumb2_upload_path'],
                    [
                        'placeholder' => $this->locale['fields_0469'],
                        'required'    => TRUE,
                    ] );
                $html .= "<label>".$this->locale['fields_0475']."</label>\n<br/>";
                $html .= "<div class='row'>\n";
                $html .= "<div class='col-xs-6 col-sm-6 col-md-6 col-lg-6'>\n";
                $html .= form_text( 'field_thumb2_w', $this->locale['fields_0476'], $this->field_data['field_config']['field_thumb2_w'], [
                    'type'        => 'number',
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => TRUE,
                ] );
                $html .= "</div><div class='col-xs-6 col-sm-6 col-md-6 col-lg-6 p-l-0'>\n";/**/
                $html .= form_text( 'field_thumb2_h', $this->locale['fields_0477'], $this->field_data['field_config']['field_thumb2_h'], [
                    'type'        => 'number',
                    'placeholder' => $this->locale['fields_0466'],
                    'required'    => TRUE
                ] );
                $html .= "</div>\n</div>\n";
                $html .= "</div>\n";
                $html .= form_checkbox( 'field_delete_original', $this->locale['fields_0478'], $this->field_data['field_config']['field_delete_original'], [ 'reverse_label' => TRUE ] );
                $html .= "</div>\n";
                add_to_jquery( "
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
                " );
                break;
            default:
                if ( $this->field_data['field_type'] !== 'textarea' ) {
                    $html .= form_text( 'field_default', $this->locale['fields_0480'], $this->field_data['field_default'] );
                }
        }
        $html .= form_text( 'field_error', $this->locale['fields_0481'], $this->field_data['field_error'] );
        $html .= form_checkbox( 'field_required', $this->locale['fields_0482'], $this->field_data['field_required'], [ 'reverse_label' => TRUE ] );
        $html .= form_checkbox( 'field_log', $this->locale['fields_0483'], $this->field_data['field_log'], [ 'reverse_label' => TRUE ] );
        $html .= form_text( 'field_order', $this->locale['fields_0484'], $this->field_data['field_order'], [ 'type' => 'number', 'inner_width' => '120px' ] );
        $html .= form_checkbox( 'field_registration', $this->locale['fields_0485'], $this->field_data['field_registration'], [ 'reverse_label' => TRUE ] );
        $html .= form_hidden( 'add_field', '', $this->field_data['field_type'] );
        $html .= form_hidden( 'field_id', '', $this->field_data['field_id'] );
        $html .= "<hr/>\n";
        $html .= form_button( 'save_field', $this->locale['fields_0488'], 'save', [ 'class' => 'btn-sm btn-primary m-r-10' ] );
        $html .= "<a href='".ADMIN."user_fields.php".fusion_get_aidlink()."&amp;ref=public'>".$this->locale['cancel']."</a>";
        $html .= closeform();

        return (string)$html;
    }

    /*
     * Calculation of Byte
     */
    private function calculate_byte( $max_b ) {
        $calc_opts = [
            1       => $this->locale['fields_0490'],
            1024    => $this->locale['fields_0491'],
            1048576 => $this->locale['fields_0492']
        ];
        foreach ( $calc_opts as $byte => $val ) {
            if ( $max_b / $byte <= 999 ) {
                return $byte;
            }
        }

        return 1048576;
    }


    public function quantum_insert( array $data = [] ) {
        $quantum_fields = [];
        $infinity_ref = [];
        // bug fix: to get only the relevant fields on specific page.
        $field_list = flatten_array( $this->fields );
        // to generate $infinity_ref and $quantum_fields as reference and validate the $_POST input value.
        foreach ( $field_list as $field_id => $field_data ) {
            if ( $field_data['field_parent'] == $this->input_page ) {
                $target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : DB_USERS;
                $target_index = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : 'user_id';
                $index_value = isset( $_POST[ $target_index ] ) ? form_sanitizer( $_POST[ $target_index ],
                    0 ) : $data[ $target_index ];
                // create reference array
                $infinity_ref[ $target_database ] = [ 'index' => $target_index, 'value' => $index_value ];
                if ( isset( $_POST[ $field_data['field_name'] ] ) ) {
                    $quantum_fields[ $target_database ][ $field_data['field_name'] ] = form_sanitizer( $_POST[ $field_data['field_name'] ],
                        $field_data['field_default'],
                        $field_data['field_name'] );
                } else {
                    $quantum_fields[ $target_database ][ $field_data['field_name'] ] = isset( $data['field_name'] ) ? $data[ $field_data['field_name'] ] : '';
                }
            }
        }
        if ( !empty( $quantum_fields ) ) {
            $temp_table = '';
            foreach ( $quantum_fields as $_dbname => $_field_values ) {
                $merged_data = [];
                $merged_data += $_field_values;
                $merged_data += $data; // appends all other necessary values to fill up the entire table values.
                if ( $temp_table !== $_dbname ) { // if $temp_table is different. check if table exist. run once if pass
                    $merged_data += [ $infinity_ref[ $_dbname ]['index'] => $infinity_ref[ $_dbname ]['value'] ]; // Primary Key and Value.
                    // ensure nothing is missing. this might be overkill. I would shut it down if not neccessary to lighten the load by 1-2 uncessary query.
                    $result = dbquery( "SELECT * FROM ".$_dbname." WHERE ".$infinity_ref[ $_dbname ]['index']." = '".$infinity_ref[ $_dbname ]['value']."'" );
                    if ( dbrows( $result ) > 0 ) {
                        $merged_data += dbarray( $result );
                    }
                }
                dbquery_insert( $_dbname, $merged_data, 'update' );
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
    public function sanitizeUserFields( $db, $primary_key, $callback_data = FALSE ) {
        $output_fields = [];

        if ( !empty( $this->fields ) ) {
            $field = flatten_array( $this->fields );

            if ( $callback_data == TRUE ) {
                $output_fields[ $db ] = $this->callback_data;
            }

            $default['user_id'] = 0;
            $this->callback_data += $default;

            foreach ( $field as $arr => $field_data ) {

                $target_database = $field_data['field_cat_db'] ? DB_PREFIX.$field_data['field_cat_db'] : $db;

                $col_name = $field_data['field_cat_index'] ? $field_data['field_cat_index'] : $primary_key;
                // Automate index value
                $output_fields[ $target_database ][ $col_name ] = $this->callback_data['user_id'];

                $output_fields[ $target_database ][ $field_data['field_name'] ] = sanitizer( $field_data['field_name'], $field_data['field_default'], $field_data['field_name'] );
            }
        }

        $this->output_fields = $output_fields;

        return $this->output_fields;
    }

    public function getUserFieldDescription( $data ) {

        $user_field_api_version = '';
        $user_field_image = '';
        $user_field_name = '';
        $user_field_desc = '';
        $user_field_author = '';
        if ( isset( self::$module_list_var[ $data[':field_name'] ]['var'] ) ) {
            if ( is_file( self::$module_list_var[ $data[':field_name'] ]['var'] ) ) {
                include self::$module_list_var[ $data[':field_name'] ]['var'];
            }
        }

        return "<strong class='m-0'>".$data[':field_title']."</strong><br/>".$user_field_desc."<div class='m-0'>by <i>$user_field_author</i> | Version: $user_field_api_version</div>";

    }

}

