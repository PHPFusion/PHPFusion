<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: QuantumActions.php
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
namespace PHPFusion\UserFields\Quantum;

use SqlHandler;

abstract class QuantumActions extends SqlHandler {

    protected static $helper;
    protected $page_list = [];
    protected $cat_list = [];
    protected $field_cat_index = [];
    private $action;
    private $order = 0;
    private $parent_id = 0;
    private $cat_id = 0;
    private $field_id = 0;
    private $return_url = '';

    public function __construct() {

    }

    protected function doAction() {
        $this->action = get( 'action' );
        $this->order = get( 'order', FILTER_VALIDATE_INT );
        $this->parent_id = get( 'parent_id', FILTER_VALIDATE_INT );
        $this->cat_id = get( 'cat_id', FILTER_VALIDATE_INT );
        $this->field_id = get( 'field_id', FILTER_VALIDATE_INT );
        $this->return_url = FUSION_SELF.fusion_get_aidlink();

        // trigger all actions event
        $this->doCancelAction();
        $this->reorderFields();
        $this->removeCategory();

        if ( $this->action == 'field_delete' && $this->field_id ) {
            if ( $this->removeField( $this->field_id ) ) {
                redirect( $this->return_url );
            }

        }

    }

    protected function doCancelAction() {
        if ( post( 'cancel' ) ) {
            redirect( $this->return_url );
        }
    }

    /**
     * GET based
     * Reorder fields with link
     */
    protected function reorderFields() {

        if ( $this->action && $this->order && $this->parent_id ) {

            if ( $this->cat_id && ( $this->action == 'cmu' || $this->action == 'cmd' ) ) {

                //@todo: check if there could be 1 or more entries?
                $current_cat_id = dbresult( dbquery( "SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:pid AND field_cat_order=:order", [ ':pid' => (int)$this->parent_id, ':order' => (int)$this->order ] ), 0 );

                switch ( $this->action ) {
                    case 'cmu': // category move up

                        dbquery( "UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id=:cid", [ ':cid' => (int)$current_cat_id ] );
                        dbquery( "UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id=:cat_id", [ ':cat_id' => (int)$this->cat_id ] );

                        break;
                    case 'cmd': // category move down
                        dbquery( "UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order-1 WHERE field_cat_id=:cid", [ ':cid' => (int)$current_cat_id ] );
                        dbquery( "UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=field_cat_order+1 WHERE field_cat_id=:cat_id", [ ':cat_id' => (int)$this->cat_id ] );
                        break;
                }

                // reindex
                $res = dbquery( "SELECT field_cat_id FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:pid ORDER BY field_cat_order ASC", [ ':pid' => (int)$this->parent_id ] );
                if ( dbrows( $res ) ) {
                    $count = 1;
                    while ( $rows = dbarray( $res ) ) {
                        dbquery( "UPDATE ".DB_USER_FIELD_CATS." SET field_cat_order=:order WHERE field_cat_id=:id", [
                            ':order' => $count,
                            ':id'    => (int)$rows['field_cat_id']
                        ] );
                        $count++;
                    }
                }
                redirect( $this->return_url );

            } else if ( $this->field_id && ( $this->action == 'fmu' || $this->action == 'fmd' ) ) {

                $current_field_id = dbresult( dbquery( "SELECT field_id FROM ".DB_USER_FIELDS." WHERE field_cat=:pid AND field_order=:order", [ ':pid' => (int)$this->parent_id, ':order' => (int)$this->order ] ), 0 );

                switch ( $this->action ) {
                    case 'fmu': // field move up
                        dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id=:fid", [ ':fid' => (int)$current_field_id ] );
                        dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_id=:field_id", [ ':field_id' => (int)$this->field_id ] );
                        break;
                    default:
                    case 'fmd':
                        dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_id=:fid", [ ':fid' => (int)$current_field_id ] );
                        dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_id=:field_id", [ ':field_id' => (int)$this->field_id ] );
                        break;
                }

                $res = dbquery( "SELECT field_id, field_order FROM ".DB_USER_FIELDS." WHERE field_cat=:parent_id ORDER BY field_order ASC", [ ':parent_id' => (int)$this->parent_id ] );
                if ( dbrows( $res ) ) {
                    $order = 1;
                    while ( $rows = dbarray( $res ) ) {
                        dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=:order WHERE field_id=:id", [
                            ':order' => $order,
                            ':id'    => (int)$rows['field_id']
                        ] );
                        $order++;
                    }
                    redirect( $this->return_url );
                }
            }
        }
    }

    protected function removeCategory() {
        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();
        $this->debug = FALSE;
        $data = [];

        if ( $this->action == 'cat_delete' && $this->cat_id ) {
            $delete_cat = post( 'delete_cat' );
            $delete_subcat = post( 'delete_subcat' );
            $delete_field = (int)post( 'delete_field', FILTER_VALIDATE_INT );
            $move_subcat = (int)post( 'move_subcat', FILTER_VALIDATE_INT );
            $move_field = (int)post( 'move_field', FILTER_VALIDATE_INT );

            if ( $this->validate_fieldCat( $this->cat_id ) ) {
                if ( $delete_cat ) {
                    // do action of the interior form

                    // Get Root Node
                    $target_database = '';
                    $field_list = [];
                    if ( $delete_subcat || $delete_field ) {
                        // non root
                        $sql_statement = "SELECT uf.field_cat_id, root.field_cat_db FROM ".DB_USER_FIELD_CATS." uf LEFT JOIN ".DB_USER_FIELD_CATS." root ON uf.field_parent = root.field_cat_id WHERE uf.field_cat_id=:cid";
                        if ( in_array( $this->cat_id, $this->page_list ) ) {
                            // root
                            $sql_statement = "SELECT field_cat_id, field_parent, field_cat_db FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cid";
                        }
                        $result = dbquery( $sql_statement, [ ':cid' => (int)$this->cat_id ] );
                        if ( $result == NULL ) {
                            die( $locale['fields_0653'] );
                        }
                        if ( dbrows( $result ) ) {
                            $data += dbarray( $result );
                            $target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;
                            $field_list = fieldgenerator( $target_database );
                            if ( $this->debug ) {
                                print_p( $field_list );
                                print_p( $target_database );
                            }
                        }
                    }

                    if ( $delete_subcat ) {
                        $this->removeSubcategory( $target_database, $field_list );
                    } else if ( $move_subcat ) {
                        $this->moveSubcategory();
                    } else if ( $delete_field && $this->cat_id ) {
                        if ( $this->removeField( $delete_field ) ) {
                            redirect( $this->return_url );
                        }
                    } else if ( !$delete_field && $move_field ) {
                        $this->moveField();
                    }

                    $delete_cat_sql = "DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cat_id";
                    //  print_p( $delete_cat_sql );
                    dbquery( $delete_cat_sql, [ ':cat_id' => (int)$this->cat_id ] );
                    add_notice( 'success', fusion_get_locale( 'field_0200' ) );
                    redirect( $this->return_url );

                } else {
                    // displays interior form
                    // show interior form
                    $field_list = [];
                    $form_action = FUSION_SELF.$aidlink."&amp;action=cat_delete&amp;cat_id=".$this->cat_id;
                    $result = dbquery( "SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$this->cat_id."' OR field_cat_id='".get_hkey( DB_USER_FIELD_CATS,
                            "field_cat_id",
                            "field_parent",
                            $_GET['cat_id'] )."'" );
                    if ( dbrows( $result ) > 0 ) {
                        $data += dbarray( $result );
                        // get field list - populate child fields of a category.
                        $result = dbquery( "SELECT field_id, field_name, field_cat FROM ".DB_USER_FIELDS." WHERE field_cat='".intval( $_GET['cat_id'] )."'" );
                        if ( dbrows( $result ) > 0 ) {
                            // get field list.
                            while ( $data = dbarray( $result ) ) {
                                $field_list[ $data['field_cat'] ][ $data['field_id'] ] = $data['field_name'];
                            }
                        }
                        if ( isset( $this->page[ $data['field_parent'] ] ) or !empty( $field_list ) && $field_list[ $_GET['cat_id'] ] > 0 ) {
                            ob_start();
                            echo openmodal( "delete", $locale['fields_0313'], [
                                'class_dialog' => 'modal-lg modal-center',
                                'static'       => TRUE
                            ] );
                            echo openform( 'delete_cat_form', 'post', $form_action );

                            if ( isset( $this->page[ $this->cat_id ] ) ) {

                                echo "<div class='row'>\n";
                                echo "<div class='col-xs-12 col-sm-6'>\n<span class='strong'>".sprintf( $locale['fields_0600'], count( $this->page[ $_GET['cat_id'] ] ) )."</span><br/>\n";
                                echo "<div class='alert alert-info m-t-10'>\n";
                                echo "<ol style='list-style:inherit !important; margin-bottom:0;'>\n";
                                foreach ( $this->page[ $this->cat_id ] as $arr => $field_category ) {
                                    echo "<li style='list-style-type:decimal;'>".self::$helper->parseLabel( $field_category['field_cat_name'] )."</li>\n";
                                }
                                echo "</ol>\n";
                                echo "</div>\n";
                                echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                                $page_list = $this->page_list;
                                unset( $page_list[ $this->cat_id ] );
                                if ( count( $page_list ) > 0 ) {
                                    echo form_select( 'move_subcat', $locale['fields_0314'], '',
                                        [ "options" => $page_list ] );
                                }
                                echo form_checkbox( 'delete_subcat', $locale['fields_0315'],
                                    count( $page_list ) < 1 ? TRUE : FALSE );
                                echo "</div></div>";
                            }

                            if ( isset( $field_list[ $this->cat_id ] ) ) {
                                echo "<div class='row'>\n";
                                echo "<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n<span class='strong'>".sprintf( $locale['fields_0601'],
                                        count( $field_list[ $_GET['cat_id'] ] ) )."</span><br/>\n";
                                echo "<div class='well strong m-t-10'>\n";
                                foreach ( $field_list[ $_GET['cat_id'] ] as $arr => $field ) {
                                    echo "- ".$field."<br/>\n";
                                }
                                echo "</div>\n";
                                echo "</div>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
                                $exclude_list[] = $_GET['cat_id'];
                                foreach ( $this->page_list as $page_id => $page_name ) {
                                    $exclude_list[] = $page_id;
                                }
                                echo form_select_tree( 'move_field', $locale['fields_0316'], '', [
                                    'no_root'      => 1,
                                    'disable_opts' => $exclude_list
                                ], DB_USER_FIELD_CATS, 'field_cat_name', 'field_cat_id', 'field_parent' );

                                echo form_checkbox( 'delete_field', $locale['fields_0317'], '' );
                                echo "</div></div>";
                            }

                            echo form_button( 'delete_cat', $locale['fields_0313'], $locale['fields_0313'], [ 'class' => 'btn-danger btn-sm' ] );
                            echo form_button( 'cancel', $locale['cancel'], $locale['cancel'], [ 'class' => 'btn-default m-l-10 btn-sm' ] );
                            echo closeform();
                            echo closemodal();
                            add_to_footer( ob_get_contents() );
                            ob_end_clean();
                        }
                    } else {
                        if ( $this->debug ) {
                            add_notice( 'info', $locale['fields_0655'].'<br/>'.$locale['fields_0656'] );
                        } else {
                            redirect( FUSION_SELF.$aidlink );
                        }
                    }
                }
            }
        }
    }

    ### Formatters ###

    protected function validate_fieldCat( $field_cat_id ) {
        if ( isnum( $field_cat_id ) ) {
            return dbcount( "(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_id='".intval( $field_cat_id )."'" );
        }

        return FALSE;
    }

    protected function removeSubcategory( $target_db, $field_list ) {
        // When deletion of a master page and involving all subcategories
        //    print_p( $this->page[$this->cat_id] );
        // execute removal on child fields and cats
        foreach ( $this->page[ $this->cat_id ] as $arr => $field_category ) {
            $result = dbquery( "SELECT field_id, field_name FROM ".DB_USER_FIELDS." WHERE field_cat='".$field_category['field_cat_id']."'" ); // find all child > 1
            if ( dbrows( $result ) ) {

                while ( $data = dbarray( $result ) ) {
                    // remove column from db , and fields
                    if ( in_array( $data['field_name'], $field_list ) ) { // verify table integrity
                        //print_p( "DROP ".$data['field_name']." FROM ".$target_db );
                        //print_p( "DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS );
                        dbquery( "DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'" );
                        if ( !empty( $target_database ) && !empty( $data['field_name'] ) ) {
                            self::drop_column( $target_database, $data['field_name'] );
                        }
                    }
                    // remove category.
                    //print_p( "DELETE ".$field_category['field_cat_id']." FROM ".DB_USER_FIELD_CATS );
                    dbquery( "DELETE FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id='".$field_category['field_cat_id']."'" );
                } // end while

            }
        }
    }

    protected function moveSubcategory() {
        // When deletion to move subcategory
        foreach ( $this->page[ $this->cat_id ] as $arr => $field_category ) {
            $new_parent = sanitizer( 'move_subcat', 0, 'move_subcat' );
            //print_p( "MOVED ".$field_category['field_cat_id']." TO category ".$new_parent );
            //print_p( "DELETE ".$_GET['cat_id']." FROM ".DB_USER_FIELD_CATS );
            dbquery( "UPDATE ".DB_USER_FIELD_CATS." SET field_parent='".$new_parent."' WHERE field_cat_id='".$field_category['field_cat_id']."'" );
        }
    }

    //protected function removeField( $target_db, $field_list ) {
    //    $locale = fusion_get_locale();
    //    // Delete fields
    //    //print_p( $locale['fields_0655'] );
    //    // Delete Fields - Bug with Isset errors
    //    $result = dbquery( "SELECT field_id, field_name FROM ".DB_USER_FIELDS." WHERE field_cat='".intval( $_GET['cat_id'] )."'" );
    //    if ( dbrows( $result ) ) {
    //        while ( $data = dbarray( $result ) ) {
    //            if ( in_array( $data['field_name'], $field_list ) ) { // verify table integrity
    //                //print_p( "DROP ".$data['field_name']." FROM ".$target_db );
    //                //print_p( "DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS );
    //                $field_del_sql = "DELETE FROM ".DB_USER_FIELDS." WHERE field_id=:fid";
    //                $field_count = $this->validate_field( $data['field_id'] );
    //                if ( $field_count ) {
    //                    dbquery( $field_del_sql, [ ':fid' => (int)$data['field_id'] ] );
    //                }
    //                // drop a column
    //                if ( !empty( $target_db ) ) {
    //                    self::drop_column( $target_db, $data['field_name'] );
    //                }
    //
    //            }
    //        }
    //
    //        add_notice( 'success', $locale['field_0200'] );
    //        redirect( $this->return_url );
    //    }
    //}

    protected function validate_field( $field_id ) {
        if ( isnum( $field_id ) ) {
            return dbcount( "(field_id)", DB_USER_FIELDS, "field_id='".intval( $field_id )."'" );
        }

        return FALSE;
    }

    protected function moveField() {

        $new_parent = (int)sanitizer( 'move_field', 0, 'move_field' );

        $rows = dbcount( "(field_id)", DB_USER_FIELDS, "field_cat=:cat_id", [ ':cat_id' => (int)$this->cat_id ] );

        if ( $rows && $new_parent ) {

            dbquery( "UPDATE ".DB_USER_FIELDS." SET field_cat=:npid WHERE field_cat=:cat_id", [ ':cat_id' => (int)$this->cat_id, ':npid' => (int)$new_parent ] );
        }
    }

    /* Outputs Quantum Admin Button Sets */
    public function removeField( $field_id ) {

        if ( $this->validate_field( $field_id ) ) {

            $locale = fusion_get_locale();

            $result = dbquery( "SELECT field.field_id, field.field_cat, field.field_order, field.field_name, u.field_cat_id, u.field_parent, root.field_cat_db
                                    FROM ".DB_USER_FIELDS." field
                                    LEFT JOIN ".DB_USER_FIELD_CATS." u ON (field.field_cat=u.field_cat_id)
                                    LEFT JOIN ".DB_USER_FIELD_CATS." root ON (u.field_parent = root.field_cat_id)
                                    WHERE field_id=:field_id", [ ':field_id' => (int)$field_id ] );
            if ( dbrows( $result ) ) {

                $data = dbarray( $result );

                $target_database = $data['field_cat_db'] ? DB_PREFIX.$data['field_cat_db'] : DB_USERS;

                $field_list = fieldgenerator( $target_database );

                if ( in_array( $data['field_name'], $field_list ) ) {
                    // drop database
                    SqlHandler::drop_column( $target_database, $data['field_name'] );
                    // reorder the rest of the same cat minus 1
                    dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                    // remove the field entry
                    dbquery( "DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'" );

                } else {
                    // just delete the field
                    //print_p( "DELETE ".$data['field_id']." FROM ".DB_USER_FIELDS );
                    dbquery( "DELETE FROM ".DB_USER_FIELDS." WHERE field_id='".$data['field_id']."'" );
                }

                add_notice( 'success', $locale['field_0201'] );
                return TRUE;
            }

            //print_p( $locale['field_0202'] );
            add_notice( 'warning', $locale['field_0202'] );
            return FALSE;
        }
        return FALSE;

    }

    /**
     * Returns $this->page_list and $this->cat_list
     */
    public function loadUserFieldCats() {
        // Load Field Cats
        if ( empty( $this->page_list ) && empty( $this->cat_list ) ) {
            $result = dbquery( "SELECT * FROM ".DB_USER_FIELD_CATS." ORDER BY field_cat_order ASC" );
            if ( dbrows( $result ) > 0 ) {
                while ( $list_data = dbarray( $result ) ) {
                    if ( $list_data['field_parent'] != '0' ) {
                        $this->cat_list[ $list_data['field_cat_id'] ] = $list_data['field_cat_name'];
                    } else {
                        $this->page_list[ $list_data['field_cat_id'] ] = QuantumHelper::parseLabel( $list_data['field_cat_name'] );
                    }
                }
            }
        }

        if ( empty( $this->field_cat_index ) ) {
            $this->field_cat_index = dbquery_tree( DB_USER_FIELD_CATS, 'field_cat_id', 'field_parent' );
        }
    }

    /* The Current Stable PHP-Fusion Dynamics Module */
    /* Execution of delete fields */
    /**
     * Add Field into table
     *
     * @param        $data
     * @param string $type
     * @param string $table_name
     * @param array  $modules
     *
     * @return bool
     * @throws \Exception
     */
    public function updateFields( $data, $type = 'dynamics', $table_name = '', $modules = [] ) {
        $locale = fusion_get_locale();

        // Build a field Attr
        $field_attr = '';
        if ( $type == 'dynamics' ) {
            $field_attr = $this->dynamics_fieldinfo( $data['field_type'], $data['field_default'] );
        } else if ( $type == 'module' && !empty( $modules ) ) {
            $field_attr = $modules[ $data['field_name'] ]['user_field_dbinfo'];
        }

        // Field order check
        $max_order = dbresult( dbquery( "SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cid", [ ':cid' => $data['field_cat'] ] ), 0 ) + 1;
        if ( !$data['field_order'] || $data['field_order'] > $max_order ) {
            $data['field_order'] = $max_order;
        }

        if ( $this->validate_field( $data['field_id'] ) ) {
            // update
            // Alter DB_USER_FIELDS table - change and modify column.
            $field_query = "SELECT uf.*, cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                                    FROM ".DB_USER_FIELDS." uf
                                    LEFT JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = uf.field_cat)
                                    LEFT JOIN ".DB_USER_FIELD_CATS." root ON (cat.field_parent = root.field_cat_id)
                                    WHERE uf.field_id=:field_id";

            $field_param = [ ':field_id' => $data['field_id'] ];

            $e_result = dbquery( $field_query, $field_param ); // search old database.

            if ( dbrows( $e_result ) ) {

                // has an existing record
                $oldRows = dbarray( $e_result );

                $old_table = $oldRows['field_cat_db'] ? DB_PREFIX.$oldRows['field_cat_db'] : DB_USERS; // this was old database

                $old_table_columns = fieldgenerator( $old_table );

                // Get current updated field_cat - to compare new cat_db and old cat_db
                $new_result = dbquery( "SELECT cat.field_cat_id, cat.field_cat_name, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                                        FROM ".DB_USER_FIELD_CATS." cat
                                        LEFT JOIN ".DB_USER_FIELD_CATS." root ON cat.field_parent = root.field_cat_id
                                        WHERE cat.field_cat_id=:cid
                                        ", [ ':cid' => (int)$data['field_cat'] ] );

                $new_table = DB_USERS;
                if ( dbrows( $new_result ) ) {
                    $newRows = dbarray( $new_result );
                    $new_table = $newRows['field_cat_db'] ? DB_PREFIX.$newRows['field_cat_db'] : DB_USERS;
                }


                // Old Table Information
                //print_p( $locale['fields_0664'] );
                //print_p( $oldRows );
                //
                //// New Table Information
                //print_p( $locale['fields_0665'] );
                //print_p( $newRows );
                //
                //print_p( $data['field_cat'] );
                //print_p( $oldRows['field_cat'] );
                //

                if ( $data['field_cat'] != $oldRows['field_cat'] ) { // old and new mismatch - move to another category and possibly a new table.
                    // Fork #1 - Update on new table
                    //print_p( $locale['fields_0666'] );
                    /**
                     * Drop column on old table and Create column on new table
                     *
                     * @todo: Improvements: need to move the whole column along with data instead of just dropping and creating new
                     */
                    if ( $new_table != $old_table ) {
                        $new_table_columns = fieldgenerator( $new_table );
                        // DEBUG MODE
                        //if ( !in_array( $data['field_name'], $new_table_columns ) ) {
                        //    print_p( "Move ".$data['field_name']." from ".$old_table." to ".$new_table );
                        //    print_p( "Dropping column ".$oldRows['field_name']." on ".$old_table );
                        //    print_p( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                        //    // since change table. fix all which is greater than link order.
                        //    print_p( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'" );
                        //} else {
                        //    print_p( str_replace( '[OLD_TABLE]', $old_table, $locale['fields_0667'] ).$new_table );
                        //}
                        //
                        if ( !in_array( $data['field_name'], $new_table_columns ) ) {
                            // this is new database check, if not exist, then add the column
                            //self::add_column($new_table, $data['field_name'], $field_attr);
                            self::move_column( $old_table, $new_table, $data['field_name'] );
                            self::drop_column( $old_table, $oldRows['field_name'] );
                            if ( fusion_safe() ) {
                                // sort the fields. if 2, greater than 2 all +1 on the new category
                                dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                                // since change table. fix all which is greater than link order.
                                dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'" );
                            }
                        } else {
                            fusion_stop();
                            add_notice( "danger", str_replace( '[OLD_TABLE]', $old_table, $locale['fields_0667'] ).$new_table );
                        }

                    } else {
                        if ( fusion_safe() ) {
                            dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                            if ( dbcount( "(field_id)", DB_USER_FIELDS ) ) {
                                dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order >= '".$oldRows['field_order']."' AND field_cat='".$oldRows['field_cat']."'" );
                            }
                        }
                    }
                } else {
                    // same table.
                    // check if same title.
                    // if not same, change column name.
                    //print_p( $locale['fields_0668'] );
                    if ( $data['field_name'] != $oldRows['field_name'] ) {
                        // not same as old record on dbcolumn
                        // Check for possible duplicates in the new field name
                        if ( !in_array( $data['field_name'], $old_table_columns ) ) {
                            //print_p( str_replace( [ '[FIELD_NAME]', '[OLD_TABLE]', '[FIELD_NAME_]' ], [ $oldRows['field_name'], $old_table, $data['field_name'] ], $locale['fields_0669'] ).$field_attr );
                            self::rename_column( $old_table, $oldRows['field_name'], $data['field_name'], $field_attr );
                        } else {
                            fusion_stop();
                            add_notice( 'danger', sprintf( $locale['fields_0104'], "($new_table)" ) );
                        }
                    }

                    //print_p( "Old field order is ".$oldRows['field_order'] );
                    //print_p( "New field order is ".$data['field_order'] );
                    //if ( $data['field_order'] > $oldRows['field_order'] ) {
                    //    print_p( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > '".$oldRows['field_order']."' AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                    //} else {
                    //    print_p( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order < '".$oldRows['field_order']."' AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                    //}
                    if ( fusion_safe() ) {
                        // make ordering of the same table.
                        if ( $data['field_order'] > $oldRows['field_order'] ) {
                            dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order-1 WHERE field_order > ".$oldRows['field_order']." AND field_order <= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                        } else if ( $data['field_order'] < $oldRows['field_order'] ) {
                            dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order < ".$oldRows['field_order']." AND field_order >= '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                        }
                    }
                }

                //print_p( $data );
                if ( fusion_safe() ) {
                    dbquery_insert( DB_USER_FIELDS, $data, 'update' );
                    add_notice( 'success', $locale['field_0203'] );

                    return TRUE;
                }
            }

            fusion_stop();

            add_notice( 'danger', $locale['fields_0105'] );

            return FALSE;
        }

        $new_table = $this->getTableName( $table_name, $data['field_cat'] );

        if ( fusion_safe() ) {

            $field_arrays = fieldgenerator( $new_table );
            // Alter DB_USER_FIELDS table - add column.
            // Checking for database registered users.
            if ( !in_array( $data['field_name'], $field_arrays ) ) { // safe to execute alter.
                if ( !empty( $data['field_name'] ) && !empty( $field_attr ) ) {
                    //print_p("ALTER TABLE ".$new_table." ADD ".$data['field_name']." ".$field_attr);
                    self::add_column( $new_table, $data['field_name'], $field_attr );
                }
            } else {
                fusion_stop();
                add_notice( 'danger', $locale['fields_0106'] );
                return FALSE;
            }

            // ordering
            if ( fusion_safe() ) {
                dbquery( "UPDATE ".DB_USER_FIELDS." SET field_order=field_order+1 WHERE field_order > '".$data['field_order']."' AND field_cat='".$data['field_cat']."'" );
                dbquery_insert( DB_USER_FIELDS, $data, 'save' );
                add_notice( 'success', $locale['field_0204'] );
                return TRUE;
            }
            return FALSE;
        }

        return FALSE;
    }

    private function getTableName( $table_name = '', $cat_id ) {
        if ( $table_name ) {
            return $table_name;
        } else {
            $cresult = dbquery( "SELECT cat.field_cat_id, cat.field_parent, cat.field_cat_order, root.field_cat_db, root.field_cat_index
                            FROM ".DB_USER_FIELD_CATS." cat
                            LEFT JOIN ".DB_USER_FIELD_CATS." root ON cat.field_parent=root.field_cat_id
                            WHERE cat.field_cat_id=:cid", [ ':cid' => (int)$cat_id ] );

            if ( dbrows( $cresult ) ) {
                $cat_data = dbarray( $cresult );
                return $cat_data['field_cat_db'] ? DB_PREFIX.$cat_data['field_cat_db'] : DB_USERS;
            } else {
                fusion_stop();
                add_notice( 'danger', fusion_get_locale( 'fields_0107' ) );
            }
        }
        return FALSE;
    }

    /* Single array output match against $db - use get_structureData before to populate $fields */
    private function dynamics_fieldinfo( $type, $default_value ) {
        $info = [
            'textbox'     => "VARCHAR(70) NOT NULL DEFAULT '".$default_value."'",
            'select'      => "VARCHAR(70) NOT NULL DEFAULT '".$default_value."'",
            'textarea'    => "TEXT NOT NULL",
            'tags'        => "TEXT NOT NULL",
            'checkbox'    => "TINYINT(3) NOT NULL DEFAULT '".( isnum( $default_value ) ? $default_value : 0 )."'",
            'toggle'      => "TINYINT(3) NOT NULL DEFAULT '".( isnum( $default_value ) ? $default_value : 0 )."'",
            'datepicker'  => "INT(10) UNSIGNED NOT NULL DEFAULT '".( isnum( $default_value ) ? $default_value : 0 )."'",
            'location'    => "VARCHAR(70) NOT NULL DEFAULT '".$default_value."'",
            'colorpicker' => "VARCHAR(10) NOT NULL DEFAULT '".$default_value."'",
            'upload'      => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'hidden'      => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
            'address'     => "TEXT NOT NULL",
            'number'      => "INT(10) UNSIGNED NOT NULL DEFAULT '".( isnum( $default_value ) ? $default_value : 0 )."'",
            'email'       => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'url'         => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
        ];

        return $info[ $type ];
    }


    protected function saveFields( $field_type = '' ) {

        if ( post( 'save_field' ) ) {

            $locale = fusion_get_locale();

            $data = [
                'field_type'         => $field_type ?: get( 'add_field' ),
                'field_id'           => sanitizer( 'field_id', '0', 'field_id' ),
                'field_title'        => form_sanitizer( $_POST['field_title'], '', 'field_title', TRUE ),
                'field_name'         => sanitizer( 'field_name', '', 'field_name' ),
                'field_cat'          => sanitizer( 'field_cat', '0', 'field_cat' ),
                'field_options'      => sanitizer( 'field_options', '', 'field_options' ),
                'field_default'      => sanitizer( 'field_default', '', 'field_default' ),
                'field_error'        => sanitizer( 'field_error', '', 'field_error' ),
                'field_required'     => ( post( 'field_required' ) ? 1 : 0 ),
                'field_log'          => ( post( 'field_log' ) ? 1 : 0 ),
                'field_registration' => ( post( 'field_registration' ) ? 1 : 0 ),
                'field_order'        => sanitizer( 'field_order', '0', 'field_order' ),
                'field_section'      => 'public'
            ];

            $data['field_name'] = str_replace( ' ', '_', $data['field_name'] ); // make sure no space.

            if ( $data['field_type'] == 'upload' ) {

                $max_b = sanitizer( 'field_max_b', 150000, 'field_max_b' );

                $calc = sanitizer( 'field_calc', '', 'field_calc' );

                $data['field_upload_type'] = sanitizer( 'field_upload_type', '', 'field_upload_type' );

                $field_thumbnail = sanitizer( 'field_thumbnail', 0, 'field_thumbnail' );

                $field_thumbnail2 = sanitizer( 'field_thumbnail_2', 0, 'field_thumbnail_2' );

                $data['field_config'] = [
                    'field_upload_type'        => $data['field_upload_type'],
                    'field_max_b'              => $max_b && $calc ? $max_b * $calc : $data['field_max_b'],
                    'field_upload_path'        => sanitizer( 'field_upload_path', '', 'field_upload_path' ),
                    'field_valid_file_ext'     => ( $data['field_upload_type'] == 'file' ? sanitizer( 'field_valid_file_ext', '', 'field_valid_file_ext' ) : '' ),
                    'field_valid_image_ext'    => ( $data['field_upload_type'] == 'image' ? sanitizer( 'field_valid_image_ext', '', 'field_valid_image_ext' ) : '' ),
                    'field_image_max_w'        => ( $data['field_upload_type'] == 'image' ? sanitizer( 'field_image_max_w', '', 'field_image_max_w' ) : '' ),
                    'field_image_max_h'        => ( $data['field_upload_type'] == 'image' ? sanitizer( 'field_image_max_h', '', 'field_image_max_h' ) : '' ),
                    'field_thumbnail'          => $field_thumbnail,
                    'field_thumb_upload_path'  => ( $data['field_upload_type'] == 'image' && $field_thumbnail ? sanitizer( 'field_thumb_upload_path', '', 'field_thumb_upload_path' ) : '' ),
                    'field_thumb_w'            => ( $data['field_upload_type'] == 'image' && $field_thumbnail ? sanitizer( 'field_thumb_w', '', 'field_thumb_w' ) : '' ),
                    'field_thumb_h'            => ( $data['field_upload_type'] == 'image' && $field_thumbnail ? sanitizer( 'field_thumb_h', '', 'field_thumb_h' ) : '' ),
                    'field_thumbnail_2'        => $field_thumbnail2,
                    'field_thumb2_upload_path' => ( $data['field_upload_type'] == 'image' && $field_thumbnail2 ? sanitizer( 'field_thumb2_upload_path', '', 'field_thumb2_upload_path' ) : '' ),
                    'field_thumb2_w'           => ( $data['field_upload_type'] == 'image' && $field_thumbnail2 ? sanitizer( 'field_thumb2_w', '', 'field_thumb2_w' ) : '' ),
                    'field_thumb2_h'           => ( $data['field_upload_type'] == 'image' && $field_thumbnail2 ? sanitizer( 'field_thumb2_h', '', 'field_thumb2_h' ) : '' ),
                    'field_delete_original'    => ( post( 'field_delete_original' ) && $data['field_upload_type'] == 'image' ? 1 : 0 )
                ];

                if ( !in_array( $data['field_upload_type'], [ 'file', 'image' ] ) ) {
                    fusion_stop();
                    add_notice( 'danger', $locale['fields_0108'] );
                }
            }

            if ( !$data['field_order'] ) {
                $data['field_order'] = dbresult( dbquery( "SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cat_id", [ ':cat_id' => $data['field_cat'] ] ), 0 ) + 1;
            }

            if ( fusion_safe() ) {
                if ( !empty( $data['field_config'] ) ) {
                    $data['field_config'] = fusion_encode( $data['field_config'] );
                }
                // will redirect and refresh config
                if ( $this->updateFields( $data, 'dynamics' ) ) {
                    redirect( FUSION_SELF.fusion_get_aidlink() );
                }
            }
        }
    }

    protected function updateCategory() {
        if ( post( 'save_cat' ) ) {
            $aidlink = fusion_get_aidlink();
            $data = [
                'field_cat_id'    => sanitizer( 'field_cat_id', '', 'field_cat_id' ),
                'field_cat_name'  => sanitizer( [ 'field_cat_name' ], '', 'field_cat_name', TRUE ),
                'field_parent'    => sanitizer( 'field_parent', '', 'field_parent' ),
                'field_cat_order' => sanitizer( 'field_cat_order', '', 'field_cat_order' ),
                'field_cat_db'    => '',
                'field_cat_index' => '',
                'field_cat_class' => '',
            ];

            // only if root then need to sanitize
            $old_data = [ "field_cat_db" => "users" ];
            $result = dbquery( "SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_cat_id=:cid", [ ':cid' => $data['field_cat_id'] ] );
            if ( dbrows( $result ) ) {
                $old_data = dbarray( $result );
            }

            if ( !$data['field_parent'] ) {

                $data['field_cat_db'] = sanitizer( 'field_cat_db', 'users', 'field_cat_db' );
                $data['field_cat_index'] = sanitizer( 'field_cat_index', '', 'field_cat_index' );
                $data['field_cat_class'] = sanitizer( 'field_cat_class', '', 'field_cat_class' );
                // Improvised to code jquery chained selector
                if ( !column_exists( $data['field_cat_db'], $data['field_cat_index'] ) ) {
                    fusion_stop();
                    add_notice( "danger", "Your table must be a valid table. Your column must be a column of a user id in that table." );
                }
            }

            if ( !$data['field_cat_order'] ) {
                $data['field_cat_order'] = dbresult( dbquery( "SELECT MAX(field_cat_order) FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:pid", [ ':pid' => (int)$data['field_parent'] ] ), 0 ) + 1;
            }

            // shuffle between save and update
            if ( self::validate_fieldCat( $data['field_cat_id'] ) ) {

                //print_p( $this->locale['fields_0661'] );
                //print_p( $data );
                dbquery_order(
                    DB_USER_FIELD_CATS,
                    $data['field_cat_order'],
                    'field_cat_order',
                    $data['field_cat_id'],
                    'field_cat_id',
                    $data['field_parent'],
                    'field_parent',
                    FALSE,
                    FALSE,
                    'update'
                );

                if ( fusion_safe() ) {
                    // New page was set during category update
                    if ( !empty( $old_data['field_cat_db'] ) or $old_data['field_cat_db'] !== "users" ) {

                        if ( !empty( $old_data['field_cat_db'] ) && !empty( $old_data['field_cat_index'] ) ) {
                            // CONDITION: HAVE A PREVIOUS TABLE SET
                            if ( !empty( $data['field_cat_db'] ) ) {
                                // new demands a table insertion, checks if same or not.. if different.
                                if ( $data['field_cat_db'] !== $old_data['field_cat_db'] ) {
                                    // But the current table is different than the previous one
                                    // - build the new one, move the column, drop the old one.
                                    SqlHandler::build_table( $data['field_cat_db'], $data['field_cat_index'] );
                                    SqlHandler::transfer_table( $old_data['field_cat_db'], $data['field_cat_db'] );
                                    SqlHandler::drop_table( $old_data['field_cat_db'] );

                                } else {
                                    if ( $old_data['field_cat_index'] !== $data['field_cat_index'] ) {
                                        SqlHandler::rename_column( $data['field_cat_db'], $old_data['field_cat_index'], $data['field_cat_index'], "MEDIUMINT(8) NOT NULL DEFAULT '0'" );
                                    }
                                }
                            } else if ( empty( $data['field_cat_db'] ) ) {
                                SqlHandler::drop_table( $data['field_cat_db'] );
                            }

                        } else if ( !empty( $data['field_cat_index'] ) && !empty( $data['field_cat_db'] ) ) {
                            SqlHandler::build_table( $data['field_cat_db'], $data['field_cat_index'] );
                        }

                        dbquery_insert( DB_USER_FIELD_CATS, $data, 'update' );

                        add_notice( 'success', fusion_get_locale( 'field_0207' ) );
                    }

                    redirect( FUSION_SELF.$aidlink );
                }

            } else {

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

                if ( fusion_safe() ) {
                    //print_p( $this->locale['fields_0662'] );
                    //print_p( $data );
                    if ( !empty( $data['field_cat_index'] ) && !empty( $data['field_cat_db'] ) && $data['field_cat_db'] !== 'users' ) {
                        SqlHandler::build_table( $data['field_cat_db'], $data['field_cat_index'] );
                    }

                    dbquery_insert( DB_USER_FIELD_CATS, $data, 'save' );
                    add_notice( 'success', fusion_get_locale( 'field_0208' ) );
                    redirect( FUSION_SELF.$aidlink );
                }
            }

            return $data;
        }

        return FALSE;
    }


}
