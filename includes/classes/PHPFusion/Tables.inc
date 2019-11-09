<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/PHPFusion/Tables.inc
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

namespace PHPFusion;

use PHPFusion\Interfaces\TableSDK;
use PHPFusion\Table\TableData;

/**
 * Class Tables
 *
 * Not to optimize before we have a stable model since it is harder to read.
 * Optimization - reduce if/else and use replace (strtr method)
 *              - reduce empty checks
 *
 * @package PHPFusion
 */
class Tables {
    
    public $query = [];
    
    private $jsonRows = [];
    
    private $rowsCount = 0;
    
    private $maxRows = 0;
    
    private $rowStart = 0;
    
    private $echo = TRUE;
    
    private $custom_columns_value = [];
    
    private $custom_columns_options = [];
    
    // Data source
    private $default_args = [
        'debug'                    => FALSE, // True to show the SQL query for the table.
        'table'                    => '',
        'id'                       => '', // if hierarchy
        'parent'                   => '', // if hierarchy
        'limit'                    => 24,
        'true_limit'               => FALSE, // if true, the limit is true limit (only limited results will display without page nav)
        'joins'                    => '',
        'select'                   => '',
        'conditions'               => '', // to match list to a condition. string value only
        'group'                    => '', // group by column
        'image_folder'             => '', // for deletion (i.e. IMAGES.'folder/') , use param for string match
        'image_field'              => '', // to delete (i.e. news_image,news_photo,news_photo_thumbs) or (i.e. news_image)
        'file_field'               => '',  // to delete (i.e. news_attach,news_attach2,news_attach3) or (i.e. news_attach)
        'file_folder'              => '', // to delete files from the folder, use param for string match
        'db'                       => [
            'select' => '', // ratings_id
            'group'  => '',  // ratings_item_id
            'custom' => '', // rating_type='N'
        ], // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
        'delete_function_callback' => '',
        'rowstart'                 => 'rowstart'
    ];
    
    private $default_column_options = [
        'title'         => '',
        'title_class'   => '',
        'value_class'   => '',
        'edit_link'     => FALSE,
        'delete_link'   => FALSE,
        'image'         => FALSE,
        'image_folder'  => '', // set image folder (method2)
        'default_image' => '',
        'image_width'   => '', // set image width
        'image_class'   => '', // set image class
        'icon'          => '',
        'empty_value'   => '',
        'count'         => [],
        'view_link'     => '',
        'display'       => [], // API for display
        'date'          => FALSE,
        'options'       => [],
        'user'          => FALSE,
        'user_avatar'   => FALSE, // show avatar
        'number'        => FALSE,
        'parse_value'   => FALSE,
        'multilang'     => FALSE,
        'format'        => FALSE, // for formatting using strtr
        'callback'      => '', // for formatting using function
        'visibility'    => TRUE, // customize column options TRUE for default visible.
        'debug'         => FALSE,
    ];
    
    // Properties
    private $default_prop_options = [
        'table_id'                => 'fusion_table',
        'table_class'             => '',
        'header_content'          => '',
        'no_record'               => 'There are no records',
        'search_label'            => 'Search',
        'search_placeholder'      => "Search",
        'ordering_col'            => '', // set this value to the sql column name to have the ordering during update
        'search_col'              => '', // set this value sql column name to have search input input filter
        'delete_link'             => TRUE,
        'edit_link'               => TRUE,
        'edit_link_format'        => '', // set this to format the edit link
        'delete_link_format'      => '', // set this to format the delete link
        'view_link_format'        => '', // set this to format the view link
        'edit_key'                => 'edit',
        'del_key'                 => 'del', // change this to invoke internal table delete function for custom delete link format
        'view_key'                => 'view',
        'order_col'               => [], // set this value to sql column name to have sorting column input filter
        'multilang_col'           => '', // set this value to have multilanguage column filter
        'updated_message'         => 'Entries have been updated', // set this value to have custom success message
        'deleted_message'         => 'Entries have been deleted', // set this value to have the custom delete message,
        'class'                   => '', // table class
        'show_count'              => TRUE, // show table item count
        'action_filters'          => [],
        'actions_filters_confirm' => [],
        'link_filters'            => [],
    ];
    
    private $columns = [];
    
    private $table_props = [];
    
    private $search_text = '';
    
    private $quick_fields = ''; // determine whether there are quick fields.
    
    private $api = NULL;
    
    /**
     * Tables constructor.
     *
     * @param TableSDK $api
     * @param bool     $echo
     *
     * @throws \ReflectionException
     */
    public function __construct( TableSDK $api, $echo = TRUE ) {
        
        if ( $api instanceof TableSDK ) {
            
            $this->api = $api;
            
            $this->echo = $echo;
            
            $this->setTableProperties();
            
            $this->setTableData();
            
            $this->setTableCols();
            
            $this->setQuickEdit();
            
            $this->setCustomSettings();
            
            if ( $echo === TRUE ) {
                
                echo $this->displayTable();
                
            }
            
        } else {
            
            echo 'Your current object requires the implementation of the \PHPFusion\Interfaces\TableSDK interface.';
            
        }
        
    }
    
    private function setCustomSettings() {
        // Post and set customization
        if ( post( 'apply_custom' ) ) {
            $column_cookie_val = sanitizer( [ 'custom_column' ], '', 'custom_column[]' );
            if ( $column_cookie_val ) {
                setcookie( $this->table_props['table_id'].'-cols', $column_cookie_val );
            }
            
            $limit_val = sanitizer( 'custom_limit', $this->query['limit'] );
            if ( $limit_val ) {
                setcookie( $this->table_props['table_id'].'-limit', $limit_val );
            }
            
            redirect( FUSION_REQUEST );
        }
        
    }
    
    /**
     * Set the table data configurations
     */
    private function setTableData() {
    
        $this->query = (array)$this->api->data() + $this->default_args;
    
        $php_limit_cookie = cookie( $this->table_props['table_id'].'-limit' );
        if ( $php_limit_cookie && isnum( $php_limit_cookie ) ) {
            
            $this->query['limit'] = $php_limit_cookie;
        }
    
    }
    
    /**
     * Set the table column data
     */
    private function setTableCols() {
    
        if ( !empty( $this->api->column() ) ) {
        
            foreach ( $this->api->column() as $key => $array ) {
            
                $this->columns[ $key ] = $array + $this->default_column_options;
                
            }
        }
    }
    
    /**
     * Set the presentation outlook of the table.
     */
    private function setTableProperties() {
    
        $this->table_props = $this->api->properties() + $this->default_prop_options;
    
    }
    
    /**
     * cannot use base.* prefix in PHP
     * Return clean ID column name
     */
    private $query_id = '';
    
    private function getIdCol() {
        if ( empty( $this->query_id ) && $this->query['id'] ) {
            $this->query_id = str_replace( 'base.', '', $this->query['id'] );
        }
        return $this->query_id;
    }
    
    /**
     * Callback
     *
     * @return array
     */
    private function getData() {
        $data = [];
        $search = '';
    
        if ( post( 'search_cat_btn' ) && post( 'search_cat' ) ) {
            $this->search_text = stripinput( post( 'search_cat' ) );
            redirect( clean_request( 's='.$this->search_text, [ 's' ], FALSE ) );
        }
    
        if ( get( 's' ) && !empty( $this->table_props['search_col'] ) ) {
            $this->search_text = stripinput( $_GET['s'] );
            //@todo: support two or three columns
            if ( is_array( $this->table_props['search_col'] ) ) {
                $var_output = [];
                foreach ( $this->table_props['search_col'] as $column_name ) {
                    $var_output[] = $column_name." LIKE '$this->search_text%'";
                }
                $search = "WHERE ".implode( ' OR ', $var_output );
            } else {
                $search = "WHERE ".$this->table_props['search_col']." LIKE '%$this->search_text%'";
            }
        
        }
    
        // Dropdown link filters redirect to $_GET
        if ( post( 'table_filter' ) && !empty( $this->table_props['dropdown_filters'] ) ) {
            
            $redirect_param = [];
            $redirect_keys = [];
        
            foreach ( $this->table_props['dropdown_filters'] as $key => $filter ) {
            
                if ( $filter['type'] == 'date' ) {
                
                    $date_filter = explode( '_', post( $key ) );
                
                    if ( count( $date_filter ) == 2 ) {
                    
                        $redirect_param[ $key.'-date_year' ] = $date_filter[0];
                    
                        $redirect_param[ $key.'-date_month' ] = $date_filter[1];
                        
                        $redirect_keys[] = $key.'-date_year';
                    
                        $redirect_keys[] = $key.'-date_month';
                    }
                
                } else {
                
                    if ( post( $key ) ) {
                    
                        $filter_val = array_map( 'strtolower', $filter['options'] );
                        $val = stripinput( post( $key ) );
                    
                        if ( isset( $filter_val[ $val ] ) ) {
                            $redirect_param[ $key ] = $filter_val[ $val ];
                        } else {
                            $redirect_param[ $key ] = $val;
                        }
                        $redirect_keys[] = $key;
                    }
                }
            }
        
            $redirect_request = clean_request( http_build_query( $redirect_param ), $redirect_keys, FALSE );
        
            redirect( $redirect_request );
        }
    
        // generate filters conditions
        $filter_cond = [];
        if ( !empty( $this->table_props['link_filters'] ) ) {
            foreach ( $this->table_props['link_filters'] as $key => $filters ) {
                if ( !empty( $_GET[ $key ] ) ) { // we are getting unpublished
                    $filter_val = array_flip( array_map( 'strtolower', $filters['options'] ) );
                    $get_val = strtolower( get( $key ) );
                    if ( isset( $filter_val[ $get_val ] ) ) {
                        if ( strlen( $filter_val[ $get_val ] ) ) {
                            $filter_cond[] = "$key='".$filter_val[ $get_val ]."'";
                        }
                    
                    }
                }
            }
        }
    
        // @todo: complete this part.
        // generate filters conditions for dropdown redirects.
        if ( !empty( $this->table_props['dropdown_filters'] ) ) {
        
            foreach ( $this->table_props['dropdown_filters'] as $key => $filters ) {
                $filter_keys = get( $key );
                switch ( $filters['type'] ) {
                    default:
                    case 'array':
                    case 'array_in':
                        // done.
                        if ( isset( $filters['options'] ) && isset( $filter_keys ) ) {
                            $filter_val = array_flip( array_map( 'strtolower', $filters['options'] ) );
                            $get_val = strtolower( $filter_keys );
                            if ( isset( $filter_val[ $get_val ] ) ) {
                                $condition = "$key='".$filter_val[ $get_val ]."'";
                                if ( $filters['type'] == 'array_in' ) {
                                    $condition = "$key IN (".$filter_val[ $get_val ].")";
                                }
                                $filter_cond[] = $condition;
                            }
                        }
                        break;
                    case 'date':
                        if ( isset( $_GET[ $key.'-date_month' ] ) && isset( $_GET[ $key.'-date_year' ] ) ) {
        
                            $start_time = mktime( '0', '0', '0', $_GET[ $key.'-date_month' ], 1, $_GET[ $key.'-date_year' ] );
        
                            $end_time = mktime( '0', '0', '0', $_GET[ $key.'-date_month' ] + 1, 1, $_GET[ $key.'-date_year' ] ) - ( 3600 * 24 );
        
                            $filter_cond[] = $key." >= '".intval( $start_time )."' AND $key <= '".intval( $end_time )."'";
                            
                        }
                        break;
                }
            }
        }
    
        if ( !empty( $filter_cond ) ) {
            $search = ( $search ? $search." AND " : "WHERE " ).implode( ' AND ', $filter_cond );
        }
        //print_P($search);
        
        // Automated Ordering
        $order_check = [
            'asc'  => 'asc',
            'desc' => 'desc'
        ];
    
        $orderby = '';
    
        $get_orderby = get( 'orderby' );
    
        $get_order = get( 'order' ); // asc or desc
    
        if ( $get_orderby && $get_order && isset( $order_check[ $get_order ] ) && !empty( $this->table_props['order_col'] ) ) {
        
            $sort = $order_check[ $get_order ];
        
            $ordering = array_flip( $this->table_props['order_col'] );
        
            if ( isset( $ordering[ $get_orderby ] ) ) {
            
                $orderby = $ordering[ $get_orderby ]." ".$sort.", ";
            }
        
        } else if ( !empty( $this->query['order'] ) ) {
            
            $orderby = $this->query['order'].", ";
        }
    
        $conditions = ( !empty( $this->query['conditions'] ) ? "{CONDITIONS_PREFIX} ".$this->query['conditions'] : '' );
        
        $count_conditions = strtr(
            $search.$conditions, [
                'WHERE'               => '',
                '{CONDITIONS_PREFIX}' => $search ? ' AND' : '',
            ]
        );
    
        $sql_table = "{TABLE} base {JOINS}";
        $sql_table = strtr( $sql_table, [
            '{TABLE}' => $this->query['table'],
            '{JOINS}' => $this->query['joins'],
        ] );
        
        // quantify max rows
        $this->maxRows = dbcount( "(".$this->query['id'].")", $sql_table, $count_conditions );
    
        if ( $this->query['true_limit'] === TRUE ) {
            $this->maxRows = $this->query['limit'];
        }
    
        $this->rowStart = get( $this->default_args['rowstart'], FILTER_VALIDATE_INT );
    
        $this->rowStart = $this->rowStart && isnum( $this->rowStart ) && $this->rowStart <= $this->maxRows ? intval( $this->rowStart ) : 0;
        
        // Limiter will only work when there are no parent declared as the hierarchy requires full query and array chunks
        $sql_statement = /** @lang mysql */
            "SELECT base.* {SELECT} FROM {TABLE} {SEARCH} {CONDITIONS} {GROUP} ORDER BY {ORDER_BY} {ID} {ORDER_SORTING} {LIMIT}";
    
        if ( $this->query['id'] && $this->query['parent'] ) {
            
            
            $sql_query = strtr(
                $sql_statement, [
                    '{TABLE}'         => $sql_table,
                    '{SELECT}'        => ( $this->query['select'] ? ", ".$this->query['select'] : '' ),
                    '{SEARCH}'        => $search,
                    '{CONDITIONS}'    => ( $search && !empty( $this->query['conditions'] ) ? strtr(
                        $conditions, [ '{CONDITIONS_PREFIX}' => " AND" ]
                    ) : strtr( $conditions, [ '{CONDITIONS_PREFIX}' => "WHERE" ] ) ),
                    '{GROUP}'         => ( $this->query['group'] ? "GROUP BY ".$this->query['group']." " : '' ),
                    '{ID}'            => $this->query['id'],
                    '{ORDER_SORTING}' => 'ASC',
                    '{ORDER_BY}'      => $orderby,
                    '{LIMIT}'         => ( $this->query['true_limit'] === TRUE ? "LIMIT ".$this->query['limit'] : '' ), // this limit is different
                ]
            );
        
            $data = dbquery_tree_full(
                $this->query['table'], $this->query['id'], $this->query['parent'], '', $sql_query
            );
        
            $index = tree_index( $data );
        
            $data = $this->sortData( $data, $index );
        
            if ( !empty( $data ) ) {
                ksort( $data );
                $data = array_chunk( $data, $this->query['limit'] );
                if ( !empty( $data ) ) {
                    $chunk_count = 0;
                    // current rows is just 15;
                    $list = [];
                    foreach ( $data as $row_keys => $rows ) {
                        $list[ $chunk_count ] = $rows;
                        $chunk_count = $chunk_count + $this->query['limit'];
                    }
                    $this->rowsCount = $this->query['limit'];
                    $data = $list;
                }
            }
        
            if ( $this->query['debug'] ) {
                print_p( 'SQL '.$sql_query.'<br/>Count '.$this->rowsCount, TRUE );
            }
        
        } else {
        
            // The difference in limitations
            $limit = ( $this->query['limit'] && isnum( $this->query['limit'] ) ? "LIMIT ".$this->rowStart.", ".$this->query['limit'] : '' );
            if ( $this->query['true_limit'] === TRUE ) {
                $limit = "LIMIT ".$this->query['limit'];
            }
        
            $conditions = ( $search && !empty( $this->query['conditions'] ) ? strtr( $conditions, [ '{CONDITIONS_PREFIX}' => " AND" ] ) : strtr( $conditions, [ '{CONDITIONS_PREFIX}' => "WHERE" ] ) );
            
            $sql_query = strtr(
                $sql_statement, [
                    '{TABLE}'         => $sql_table,
                    '{SELECT}'        => ( $this->query['select'] ? ", ".$this->query['select'] : '' ),
                    '{SEARCH}'        => $search,
                    '{CONDITIONS}'    => !$search ? $conditions : '',
                    '{GROUP}'         => ( $this->query['group'] ? "GROUP BY ".$this->query['group']." " : '' ),
                    '{ID}'            => $this->query['id'],
                    '{ORDER_SORTING}' => 'DESC',
                    '{ORDER_BY}'      => $orderby,
                    '{LIMIT}'         => $limit,
                ]
            );
        
            $result = dbquery( $sql_query );
            if ( $this->rowsCount = dbrows( $result ) ) {
                while ( $rows = dbarray( $result ) ) {
                    $data[ $this->rowStart ][ $rows[ $this->getIdCol() ] ] = $rows;
                }
            }
        
            if ( $this->query['debug'] ) {
                print_p( 'SQL '.$sql_query );
                print_p( 'Count '.$this->rowsCount );
            }
        
        }
    
        return (array)$data;
    }
    
    /**
     * Sort Callback Data
     *
     * @param     $categories
     * @param     $index
     * @param int $id
     * @param int $i
     *
     * @return array
     */
    private function sortData( $categories, $index, $id = 0, $i = 1 ) {
        $data = &$data;
        if ( !empty( $categories[ $id ] ) ) {
            foreach ( $categories[ $id ] as $parentID => $keyData ) {
                $keyData['level'] = get_depth( $index, $keyData[ $this->query['id'] ] );
                $keyData[ $this->query['title'] ] = str_repeat(
                        '— ', $keyData['level'] - 1
                    )." ".$keyData[ $this->query['title'] ];
                // 0 first
                $data[ $i ] = $keyData;
                $i++;
                if ( isset( $categories[ $keyData[ $this->query['id'] ] ] ) ) { // has child
                    $refs = self::sortData( $categories, $index, $keyData[ $this->query['id'] ], $i );
                    $data = array_replace( $refs, $data );
                    $i = $i + count( $refs );
                }
            }
        }
        return $data;
    }
    
    /**
     * Do delete record multiple checkboxes
     */
    private function filterDeleteRows() {
        $del_id = post_array( [ 'id' ] );
        if ( !empty( $del_id ) ) {
            $params = [];
            foreach ( $del_id as $val ) {
            
                if ( $val ) {
    
                    $result = dbquery( "SELECT * FROM ".$this->query['table']."  WHERE ".$this->getIdCol()."=:id", [ ':id' => $val ] );
                    if ( dbrows( $result ) ) {
                        $data = dbarray( $result );
                        foreach ( $data as $key => $value ) {
                            $params[ ':'.$key ] = $value;
                        }
                    
                        // delete principal
                        if ( $this->query['parent'] ) {
                            //append all child to parentless
                            $child_result = dbquery( "SELECT ".$this->query['id'].",".$this->query['parent']."   FROM ".$this->query['table']."  WHERE ".$this->query['parent']."=:id", [ ':id' => $val ] );
                            if ( dbrows( $child_result ) ) {
                                while ( $cdata = dbarray( $child_result ) ) {
                                    // id
                                    $cdata[ $this->query['parent'] ] = 0;
                                    if ( $data[ $this->query['parent'] ] ) { // if I have a parent, change all my child to that parent
                                        $cdata[ $this->query['parent'] ] = $data[ $this->query['parent'] ];
                                    }
                                    dbquery_insert( $this->query['table'], $cdata, 'update' );
                                }
                            }
                            dbquery_insert( $this->query['table'], $data, 'delete' );
                        } else {
                            dbquery( "DELETE FROM ".$this->query['table']."  WHERE ".$this->getIdCol()."=:id", [ ':id' => $val ] );
                        }
                    
                        if ( !empty( $params ) && !empty( $data ) ) {
                            
                            // This one looks buggy, because it is only for item with parent.
                            // delete all images - images is by "," delimiter in a single column
                            if ( $this->query['image_field'] && $this->query['image_folder'] ) {
                            
                                $this->query['image_folder'] = strtr( $this->query['image_folder'], $params );
                            
                                if ( !empty( $data[ $this->query['image_field'] ] ) ) {
                                    // image field can be multiple, -
                                    $list_images = explode( ',', $data[ $this->query['image_field'] ] );
                                
                                    foreach ( $list_images as $filename ) {
                                        if ( file_exists( $this->query['image_folder'].$filename ) ) {
                                            unlink( $this->query['image_folder'].$filename );
                                        }
                                    }
                                }
                            
                            }
                        
                            // delete all files - files is by "," delimiter in a single column
                            if ( $this->query['file_field'] && $this->query['file_folder'] ) {
                                $this->query['file_folder'] = strtr( $this->query['file_folder'], $params );
                                if ( $data[ $this->query['file_field'] ] ) {
                                    $list_images = explode( ',', $data[ $this->query['file_field'] ] );
                                    foreach ( $list_images as $filename ) {
                                        if ( file_exists( $this->query['file_folder'].$filename ) ) {
                                            unlink( $this->query['file_folder'].$filename );
                                        }
                                    }
                                }
                            }
                        
                            // execute sql delete using custom parameter
                            if ( !empty( $this->query['db']['select'] ) && !empty( $this->query['db']['group'] ) ) {
                                foreach ( $this->query['db'] as $table_name => $table_prop ) {
                                    $custom_condition = !empty( $table_prop['custom'] ) ? "AND ".$table_prop['custom'] : '';
                                    if ( dbcount( "(".$table_prop['select'].")", $table_name, $table_prop['group']."='".$data[ $this->query['id'] ]."' $custom_condition" ) ) {
                                        dbquery( "DELETE FROM ".$table_name." WHERE ".$table_prop['group']."='".$data[ $this->query['id'] ]."' $custom_condition" );
                                    }
                                }
                            }
                        
                            //function callback on deletion
                            if ( !empty( $this->query['del_function_callback'] ) ) {
                                $this->query['delete_function_callback']( $data );
                                if ( is_callable( $this->query['delete_function_callback'] ) ) {
                                    // use reflexion class
                                    if ( is_array( $this->query['delete_function_callback'] ) ) {
                                        // delete class callback
                                        if ( isset( $this->query['delete_function_callback'][3] ) ) {
                                            require_once $this->query['delete_function_callback'][3];
                                        }
                                        $class_name = $this->query['delete_function_callback'][0];
                                        $method = $this->query['delete_function_callback'][1];
                                        $obj = new \ReflectionClass( $class_name );
                                        $class = $obj->newInstance();
                                        if ( method_exists( $class, $method ) ) {
                                            $class->$method( $data );
                                        }
                                    } else {
                                        // delete function callback
                                        $this->query['delete_function_callback']( $data );
                                    }
                                }
                            }
                        }
                    
                        // run sdk delete cmd
                        $this->bulkDelete( $data );
                    }
                }
            }
        
            if ( fusion_safe() ) {
                if ( $this->table_props['deleted_message'] ) {
                    addNotice( 'success', $this->table_props['deleted_message'] );
                }
                redirect( FUSION_REQUEST );
            }
        
        }
    }
    
    /**
     * Quick Edit Form
     */
    public function setQuickEdit() {
        $default_options = [
            'title'    => '',
            'required' => FALSE,
            'dynamics' => 'form_text'
        ];
        if ( !empty( $this->api->quickEdit() ) ) {
            $array = [];
            foreach ( $this->api->quickEdit() as $key => $fields ) {
                $fields += $default_options;
                $array[ $key ] = $fields;
            }
            $this->quick_fields = $array;
        }
    }
    
    
    /**
     * Table individual user customization Menu
     *
     * @return string
     * @throws \ReflectionException
     */
    private function displayCustom() {
    
        $html = "<div id='".$this->table_props['table_id']."-custom-box' class='list-group-item m-b-15' style='display:none;'>";
        $html .= "<div class='p-l-15 strong'>Columns</div>";
        // get all the data column.
        $html .= "<div class='p-l-15'>";
        $html .= form_checkbox( 'custom_column[]', '', implode( ',', $this->custom_columns_value ), [
            'multiple'       => TRUE,
            'inline'         => FALSE,
            'inline_options' => TRUE,
            'options'        => $this->custom_columns_options
        ] );
        $html .= "</div>";
        //$html .= "<div class='p-l-15 m-b-10 strong'>Pagination</div>";
        $html .= form_text( 'custom_limit', 'Number of items per page:', $this->query['limit'], [
            'inline'      => TRUE,
            'inner_width' => '80px',
            'class'       => 'm-b-10',
            'deactivate'  => ( $this->query['true_limit'] === TRUE ? TRUE : FALSE ),
        ] );
        //$html .= "<div class='p-l-15 m-b-20'>";
        $html .= form_button( 'apply_custom', 'Apply Filter', 'apply_custom', [ 'class' => 'btn-primary m-l-15' ] );
        //$html .= "</div>";
        $html .= "</div>";
    
        return (string)$html;
    }
    
    /**
     * @return string
     * @throws \ReflectionException
     */
    public function displayTable() {
        $html = '';
        $header = '';
    
        // custom column cookie value
        $php_column_cookie = cookie( $this->table_props['table_id'].'-cols' );
    
        if ( $php_column_cookie ) {
        
            $column_settings = explode( ',', $php_column_cookie );
        
            $this->custom_columns_value = array_combine( array_values( $column_settings ), array_values( $column_settings ) );
            
        }
    
        $cdata = $this->getData();
    
        $col_span = 1;
    
        $order_col = $this->table_props['order_col'];
    
        $key_delete = get( $this->table_props['del_key'], FILTER_VALIDATE_INT ); // the apply button
        
        // The bullk actions
        $table_action = post( "table_action" );
        if ( $table_action ) {
            $id = sanitizer( [ 'id' ], '', 'id[]' );
            if ( $id ) {
                switch ( $table_action ) {
                    case 'bulk_delete': // delete
                        $this->filterDeleteRows();
                        break;
                    default:
                        $this->bulkActions();
                        break;
                }
            } else {
                addNotice( 'warning', 'No record has been selected for this table action.' );
            }
        }
    
        // The delete of a row
        if ( $key_delete && isnum( $key_delete ) ) {
        
            $result = dbquery( "SELECT * FROM ".$this->query['table']." WHERE ".$this->query['id']."=:id", [ ':id' => (int)$key_delete ] );
        
            if ( dbrows( $result ) ) {
            
                $del_data = dbarray( $result );
                
                $params = [];
                foreach ( $del_data as $key => $value ) {
                    $params[ ':'.$key ] = $value;
                }
            
                // Remove Image
                if ( $this->query['image_field'] && $this->query['image_folder'] ) {
                    $this->query['image_folder'] = strtr( $this->query['image_folder'], $params );
                    if ( !empty( $del_data[ $this->query['image_field'] ] ) ) {
                        // support column with ',' images list (i.e. the value is 'abc.png,bbb.jpg')
                        $list_images = explode( ',', $del_data[ $this->query['image_field'] ] );
                        foreach ( $list_images as $filename ) {
                            if ( file_exists( $this->query['image_folder'].$filename ) ) {
                                $file = $this->query['image_folder'].$filename;
                                if ( $this->query['debug'] ) {
                                    print_p( 'Remove '.$file );
                                } else {
                                    unlink( $file );
                                }
                            }
                        }
                    }
                }
            
                // Remove File
                if ( $this->query['file_field'] && $this->query['file_folder'] ) {
                    $this->query['file_folder'] = strtr( $this->query['file_folder'], $params );
                    if ( !empty( $del_data[ $this->query['file_field'] ] ) ) {
                        // support column with ',' files list (i.e. the value is 'abc.rar,bbb.zip')
                        $list_images = explode( ',', $del_data[ $this->query['file_field'] ] );
                        //print_p($this->query['file_folder'].$list_images[0]);
                        foreach ( $list_images as $filename ) {
                            if ( file_exists( $this->query['file_folder'].$filename ) ) {
                                $file = $this->query['file_folder'].$filename;
                                //print_p($file);
                                if ( $this->query['debug'] ) {
                                    print_p( 'Remove '.$file );
                                } else {
                                    unlink( $file );
                                }
                            }
                        }
                    }
                }
            
                // Custom function delete
                if ( !empty( $this->query['delete_function_callback'] ) ) {
                    if ( is_callable( $this->query['delete_function_callback'] ) ) {
                        $this->query['delete_function_callback']( $del_data );
                    } else {
                        addNotice( 'warning', 'Function '.$this->query['delete_function_callback'].' is not found' );
                    }
                } else {
                
                    // Custom delete
                    if ( !empty( $this->query['db'] ) && !empty( $table_prop['custom'] ) && !empty( $table_prop['group'] ) ) {
                        foreach ( $this->query['db'] as $table_name => $table_prop ) {
                            $custom_condition = ( !empty( $table_prop['custom'] ) ? 'AND '.$table_prop['custom'] : '' );
                            if ( dbcount( "(".$table_prop['select'].")", $table_name, $table_prop['group']."='".$del_data[ $this->query['id'] ]."' $custom_condition" ) ) {
                                $del_statement = "DELETE FROM ".$table_name." WHERE ".$table_prop['group']."='".$del_data[ $this->query['id'] ]."' $custom_condition";
                                if ( $this->query['debug'] ) {
                                    print_p( $del_statement );
                                } else {
                                    dbquery( $del_statement );
                                }
                            }
                        }
                    } else {
                        // this one is malfunctioning
                        dbquery_insert( $this->query['table'], $del_data, 'delete' );
                    }
                
                }
            
                $this->bulkDelete( $del_data );
            
                addNotice( 'success', $this->table_props['deleted_message'] );
                redirect( clean_request( '', [ $this->table_props['del_key'] ], FALSE ) );
            }
        }
    
        // The saves of this quick fields.
        if ( !empty( $this->quick_fields ) ) {
        
            $save_quick = post( 'save_quick_editor' );
        
            if ( $save_quick ) {
                $post_data[ $this->query['id'] ] = sanitizer( $this->query['id'], '', $this->query['id'] );
                foreach ( $this->quick_fields as $table_col => $table_fields ) {
                    $post_data[ $table_col ] = sanitizer( $table_col, '', $table_col );
                }
            
                if ( fusion_safe() ) {
                    dbquery_insert( $this->query['table'], $post_data, 'update', [ 'keep_session' => TRUE ] );
                    redirect( FUSION_REQUEST );
                }
            }
        }
    
        if ( is_array( $this->table_props['order_col'] ) ) {
            foreach ( $this->table_props['order_col'] as $col_key => $col_name ) {
                $current_order_col[ $col_key ] = $col_name;
            }
        } else {
            $current_order_col[ $this->table_props['order_col'] ] = $order_col;
        }
    
        // TH header row filter
        foreach ( $this->columns as $col_key => $col_data ) {
            
            // get default custom column values
            if ( $col_key !== $this->getIdCol() ) {
            
                $this->custom_columns_options[ $col_key ] = $col_data['title'];
                
                //print_P($col_data);
                if (
                    $col_data['visibility'] === TRUE && empty( $php_column_cookie ) || isset( $this->custom_columns_value[ $col_key ] )
                ) {
                    $this->custom_columns_value[ $col_key ] = $col_key;
                }
            
            }
        
            // only id column and custom column value can be rendered
            if ( isset( $this->custom_columns_value[ $col_key ] ) || $col_key == $this->getIdCol() ) {
                
                $order_open = '';
                $order_close = '';
                $order_caret = [
                    'asc'  => 'order-caret-up',
                    'desc' => 'order-caret-down'
                ];
            
                if ( !empty( $current_order_col ) ) {
                    $order_values = array_flip( $current_order_col );
                }
            
                if ( !empty( $current_order_col[ $col_key ] ) ) { // turn on carets
                    
                    $order = 'asc';
                
                    $order_class = $order_caret['asc'];
                
                    $order_value = $current_order_col[ $col_key ]; // title
                
                    $order = get( "order" ) ? get( 'order' ) : $order; // asc or desc
                
                    $order_by = get( "orderby" );
                    
                    $order_val = 'desc';
                
                    if ( $order && $order_by ) {
                    
                        if ( !empty( $order_values[ $order_by ] ) && $order_values[ $order_by ] == $col_key ) {
                            
                            $order_class = 'order-caret-up-current';
                        
                            if ( $order == 'desc' ) {
                                
                                $order_class = 'order-caret-down-current';
                            
                                $order_val = 'asc';
                            }
                        }
                    }
                
                    $order_open = "<a class='table-order $order_class' href='".clean_request( "orderby=".$order_value."&order=".$order_val, [ 'order', 'orderby' ], FALSE )."'>\n";
                    
                    $order_close = "</a>\n";
                }
            
                $header .= "<th ".( $col_data['title_class'] ? "class='".$col_data['title_class']."'" : "" ).">".$order_open.$col_data['title'].$order_close."</th>\n";
                
                $col_span++;
            }
        
        }
    
        // End Header TH
    
        /**
         * Link filters
         */
        $link_filters = [];
        $link_filter_keys = [ 's' ];
        
        // Lang Filters
        if ( !empty( $this->table_props['multilang_col'] ) ) { // this one do as tab on top.
            $enabled_languages = fusion_get_enabled_languages();
            $cur_language = get( 'language' );
            if ( count( $enabled_languages ) > 0 ) {
                $_filter[] = '';
                foreach ( $enabled_languages as $lang ) {
                    $lang_val = strtolower( $lang );
                    $active_class = ( $cur_language && $cur_language == $lang_val ) ? " class='strong'" : '';
                    $_filter[] = "<a href='".clean_request( 'language='.$lang_val, [ 'language' ], FALSE )."'$active_class><small>$lang</small></a>\n | ";
                }
                $link_filters['language'] = " | <small>Language:</small> ".implode( ' ', $_filter );
                array_push( $link_filter_keys, 'language' );
            }
        }
    
        // Calculate Link Filters
        if ( !empty( $this->table_props['link_filters'] ) ) {
            foreach ( $this->table_props['link_filters'] as $key => $filters ) {
                $_filter = [];
                foreach ( $filters['options'] as $filter_key => $filter ) {
                    // do a count if count options is assigned
                    $count_num = '';
                    if ( !empty( $filters['count'] ) ) {
                        $count_num = " (".dbcount( "(".$this->query['id'].")", $this->query['table'].' base', $key.'=:key', [ ':key' => $filter_key ] ).")";
                    }
                    $filter_val = strtolower( $filter );
                    $active_class = ( get( $key ) == $filter_val ) ? " class='strong'" : '';
                    $_filter[] .= "<a href='".clean_request( "$key=$filter_val", [ $key ], FALSE )."'$active_class><small>$filter</small></a>$count_num\n";
                }
                $link_filters[ $key ] = "<small>".$filters['title'].":</small> ".implode( ' | ', $_filter );
                array_push( $link_filter_keys, $key );
            }
        }
    
        // Calculate more $link_filter_keys from dropdown redirect
        if ( !empty( $this->table_props['dropdown_filters'] ) ) {
            foreach ( $this->table_props['dropdown_filters'] as $key => $filter ) {
                if ( $filter['type'] == 'date' ) {
                    array_push( $link_filter_keys, $key.'-date_month' );
                    array_push( $link_filter_keys, $key.'-date_year' );
                } else {
                    array_push( $link_filter_keys, $key );
                }
            }
        
        }
    
        if ( !empty( $link_filters ) || !empty( $this->table_props['search_col'] ) ) {
            
            // Render link filters
            $html .= "<div class='".grid_row()."'>\n";
            if ( !empty( $link_filters ) ) {
                $html .= "<div class='col-xs-12 col-sm-".( $this->table_props['search_col'] ? 6 : 12 )."'>\n";
                $active_class = ( !in_array( $_GET, $link_filter_keys ) ) ? " class='strong'" : '';
                $html .= "<div class='display-inline-block'><a href='".clean_request( '', $link_filter_keys, FALSE )."'$active_class><small>All</small></a> ".implode( '', $link_filters )."</div>\n";
                $html .= "</div>\n";
            }
        
            if ( !empty( $this->table_props['search_col'] ) ) {
                $html .= "<div class='col-xs-12 col-sm-".( $this->table_props['multilang_col'] || !empty( $link_filters ) ? 6 : 12 )." text-right'>\n";
                $html .= "<div class='pull-right'>\n";
                $html .= openform( 'search_frm', 'post' );
                $html .= form_text(
                    'search_cat', '', $this->search_text, [
                        'required'           => TRUE,
                        'class'              => 'm-b-0',
                        'group_size'         => 'sm',
                        'placeholder'        => $this->table_props['search_placeholder'],
                        'append'             => TRUE,
                        'append_value'       => $this->table_props['search_label'],
                        'append_form_value'  => 'search_cat',
                        'append_button_name' => 'search_cat_btn',
                        'append_button'      => TRUE,
                        'width'              => '350px',
                    ]
                );
                $html .= closeform();
                $html .= "</div>\n";
                $html .= "</div>\n";
            }
            $html .= "</div>\n";
        }
    
        $start_row = '';
        $end_row = '';
    
        if ( $this->table_props['show_count'] === TRUE || $this->quick_fields ) {
            $start_row = "<div style='display:flex; flex-direction:row; justify-content: flex-start; width:100%;'>\n";
            $end_row = "</div>\n";
        }
    
        $html .= $start_row;
    
    
        if ( $this->table_props['delete_link'] === TRUE || !empty( $this->table_props['action_filters'] ) ) {
            $html .= "<div class='display-inline-block pull-left m-r-15'>\n";
        
            $actions_options = [
                ''            => 'Bulk Actions',
                'bulk_delete' => 'Delete'
            ];
        
            if ( !empty( $this->table_props['action_filters'] ) ) {
                // 'text' (string), 'label' (string), 'children' (array)
                if ( isset( $this->table_props['action_filters']['children'] ) ) {
                    array_push( $actions_options, $this->table_props['action_filters'] );
                } else {
                    $actions_options = $actions_options + $this->table_props['action_filters'];
                }
            }
        
            $html .= '<div class="table-actions">'.openform( 'cat_table_frm', 'post', FORM_REQUEST ).
                form_select(
                    'filter_actions', '', '', [
                        'inline'      => TRUE,
                        'inner_width' => '200px',
                        'class'       => 'm-0 display-inline-block pull-left',
                        'optgroup'    => TRUE,
                        'options'     => $actions_options,
                        'stacked'     => form_button(
                                'apply_filter', 'Apply', 'apply_filter', [ 'class' => 'btn-sm btn-default' ]
                            ).form_hidden(
                                'filter_ids', '', ''
                            )
                    ]
                ).closeform().'</div>';
            $html .= "</div>\n";
        }
    
        if ( !empty( $this->table_props['dropdown_filters'] ) ) {
        
            $html .= "<div class='display-inline-block'>";
        
            $html .= openform( 'filterfrm', 'post', FUSION_REQUEST );
        
            if ( !empty( $this->table_props['dropdown_filters'] ) ) {
                foreach ( $this->table_props['dropdown_filters'] as $filter_name => $filter_prop ) {
                
                    switch ( $filter_prop['type'] ) {
                        default:
                        case 'array_in':
                        case 'array':
                            $html .= $this->columnFilter( $filter_name, $filter_prop );
                            break;
                        case 'date':
                            $html .= $this->dateFilter( $filter_name, $filter_prop );
                            break;
                    }
                }
            }
        
            $html .= form_button(
                'table_filter', 'Apply', 'table_filter', [ 'class' => 'btn-default btn-sm m-r-10' ]
            );
        
            $html .= closeform()."</div>";
        }
    
        $has_search = get( 's' );
        if ( $has_search ) {
            $html .= "<a class='btn btn-link btn-sm' href='".clean_request( '', [ 's' ], FALSE )."'>Clear Search</a>";
        }
    
        $html .= "<div style='margin-left:auto;'>";
        // Show customization toggle
        $html .= "<small class='m-t-5 m-l-5'><a data-target='custom-box' class='pointer custom-table' data-table='".$this->table_props['table_id']."'>Customise Options <span class='caret'></span></a></small>";
    
        // Show row counter
        if ( $this->table_props['show_count'] === TRUE ) {
            $html .= "<small class='m-t-5'>".format_word( $this->maxRows, "item|items", [ 'html' => FALSE, 'format_num' => TRUE ] )."</small>\n";
        }
        $html .= "</div>";
        
        $html .= $end_row;
    
        $check_all_col = '';
        $check_all_col_2 = '';
    
        if ( $this->table_props['edit_link'] or $this->table_props['delete_link'] ) {
            $check_all_col = "<th class='min'>".form_checkbox( 'chk_all', '', '', [ 'class' => 'm-b-0' ] )."</th>\n";
            $check_all_col_2 = "<th>".form_checkbox( 'chk_all2', '', '', [ 'class' => 'm-b-0' ] )."</th>\n";
        }
    
        $validate_messages = [
            'bulk_delete' => 'Are you sure to delete all these records?'
        ];
        if ( !empty( $this->table_props['actions_filters_confirm'] ) ) {
            if ( is_array( $this->table_props['actions_filters_confirm'] ) ) {
                $validate_messages = $validate_messages + $this->table_props['actions_filters_confirm'];
            }
        }
        $validate_opts = json_encode( $validate_messages );
    
        $html .= openform( 'table_frm', 'post', FORM_REQUEST );
        $html .= form_hidden( 'table_action', '', '' );
        
        // Show custom table properties
        $html .= $this->displayCustom();
    
        $html .= "<table class='fusion-table ".( $this->table_props['table_class'] ? ' '.$this->table_props['table_class'] : 'table' )."'>\n";
        // head
        $html .= "<thead>\n";
        $html .= "<tr>\n$check_all_col\n".$header."\n</tr>\n";
        $html .= "</thead>\n";
    
        $html .= "<tbody>\n";
        // end of head
        if ( !empty( $cdata ) ) {
        
            if ( $this->query['debug'] ) {
                print_p( $cdata );
            }
        
            foreach ( $cdata[ $this->rowStart ] as $data ) {
                $id = $data[ $this->getIdCol() ];
                // the edit link
                // key need to be dynamic as the del and edit may be present.
                $get_rid = [ $this->table_props['edit_key'], $this->table_props['del_key'], 's' ];
                // All the info available should be replaced.
                //print_p(array_values($this->columns));
            
                // Search Strings
                $search_str = [];
                foreach ( $data as $key => $value ) {
                    $search_str[ ":".$key ] = $value;
                }
            
                $edit_uri = $this->table_props['edit_link_format'] ? strtr(
                    urldecode( $this->table_props['edit_link_format'].$id ), $search_str
                ) : clean_request( $this->table_props['edit_key'].'='.$id, $get_rid, FALSE );
                
                $del_uri = $this->table_props['delete_link_format'] ? strtr(
                    urldecode( $this->table_props['delete_link_format'].$id ), $search_str
                ) : clean_request( $this->table_props['del_key'].'='.$id, $get_rid, FALSE );
                
                $view_uri = $this->table_props['view_link_format'] ? strtr(
                    urldecode( $this->table_props['view_link_format'].$id ), $search_str
                ) : clean_request( $this->table_props['view_key'].'='.$id, $get_rid, FALSE );
                
                // need to use table props instead.. 'delete_link' => true, or 'edit_link' => true,
                $checkbox_col = '';
                if ( $this->table_props['edit_link'] or $this->table_props['delete_link'] ) {
                    $checkbox_col = "<th class='check-col'>\n".form_checkbox( "id[]", '', '', [ 'value' => $id, 'class' => 'm-b-10' ] )."</th>\n";
                }
            
                $html .= "<tr id='entry-row-".$id."'>\n$checkbox_col\n";
            
                $tableData = new TableData( $this );
            
                foreach ( $this->columns as $col_key => $col_data ) { // column configurations
                
                    if ( isset( $this->custom_columns_value[ $col_key ] ) || $col_key == $this->getIdCol() ) {
                        
                        $edit_open = '';
                        $edit_close = '';
                        $edit_actions = '';
                        $value_class = [];
                        $data_replace = [];
                    
                        if ( !empty( $data ) ) {
                            foreach ( array_keys( $data ) as $keyname ) {
                                $data_replace[ ':'.$keyname ] = $data[ $keyname ];
                            }
                        }
                    
                        // Column value
                        $value = ( !empty( $data[ $col_key ] ) ? $data[ $col_key ] : $col_data['empty_value'] );
                        // Strip all bbcode tags
                        if ( !empty( $value ) ) {
                            if ( !empty( $col_data['parse_value'] ) ) {
                                $value = nl2br( parse_textarea( $value ) );
                            }
                            $value = strip_bbcodes( $value );
                            $value = strip_tags( $value );
                        }
                    
                        $value_class[] = ( $col_data['value_class'] ?: '' );
                    
                        if ( $col_data['edit_link'] || $col_data['delete_link'] || $col_data['view_link'] ) {
                            
                            $value_class[] = "has-actions clearfix";
                            /*
                             * (Optional - sets the current column with the edit link, with quick edit fields)
                             * array("edit_link" => TRUE)
                             */
                            $edit_open = "<a href='$edit_uri' class='strong text-bigger'>\n";
                        
                            $edit_close = "</a>\n";
                        
                            $edit_actions = "<div class='tbl_actions'>\n";
                        
                            $edit_actions .= "<span class='text-lighter'>ID: $id</span>\n";
                        
                            $edit_actions .= ( $col_data['edit_link'] ? "<span class='edit'><a href='$edit_uri'>Edit</a></span>\n" : '' );
                        
                            $edit_actions .= ( ( !empty( $this->quick_fields ) ) ? "<span class='qedit'><a class='quick_edit pointer' data-toggle='quick_edit' data-value='".$id."'>Quick Edit</a></span>\n" : '' );
                        
                            $edit_actions .= ( $col_data['delete_link'] ? "<span class='del'><a onclick='return confirm(\"Are you sure you want to delete this entry? All associated files and images will be permanently removed from the server.\");' href='$del_uri' class='text-danger'>Delete</a></span>\n" : '' );
                        
                            $edit_actions .= ( $col_data['view_link'] ? "<span class='view'><a href='$view_uri'>View</a></span>\n" : '' );
                            
                            $edit_actions .= "</div>\n";
                        
                            $edit_actions .= "<button type='button' class='toggle-row'></button>";
                        
                        }
                    
                        /*
                         * Types of display method supported
                         */
                        if ( $col_data['image'] ) {
                        
                            $value = $tableData->imageColumn( $col_key, $data, $col_data, $data_replace );
                        
                        } else if ( $col_data['multilang'] ) {
    
                            $value = fusion_parse_locale( $data[ $col_key ] );
                        
                        } else if ( $col_data['icon'] && !empty( $data[ $col_key ] ) ) {
                            /*
                             * array("icon" => TRUE)
                             */
                            $value = $tableData->iconColumn( $col_key, $data );
                        
                        } else if ( $col_data['number'] ) {
                        
                            $value = $tableData->numberColumn( $value, $col_key, $data, $col_data );
                        
                        } else if ( $col_data['user'] === TRUE ) {
                        
                            $value = $tableData->userColumn( $value, $col_data );
                        
                        } else if ( !empty( $col_data['options'] ) ) {
                        
                            $value = $tableData->optionsColumn( $value, $col_data );
                        
                        } else if ( $col_data['count'] ) {
                        
                            $value = $tableData->countColumn( $value, $data, $col_data );
                        
                        } else if ( $col_data['display'] && $col_data['display']['table'] && $col_data['display']['key'] && $col_data['display']['title'] ) {
                        
                            $value = $tableData->displayColumn( $value, $data, $col_key, $col_data, $data_replace );
                        
                        } else if ( !empty( $col_data['date'] ) && !empty( $value ) ) {
                        
                            $value = $tableData->dateColumn( $value, $col_data );
                        
                        } else if ( !empty( $col_data['array'] ) && is_array( $col_data['array'] ) ) {
                        
                            $value = $tableData->arrayColumn( $value, $col_data );
                            
                        }
                    
                        if ( !empty( $col_data['format'] ) ) {
                            $value = $tableData->formatColumn( $value, $col_key, $col_data, $data_replace );
                        }
                    
                        if ( !empty( $col_data['callback'] ) ) {
                            $value = $tableData->callbackColumn( $value, $col_key, $col_data, $data_replace );
                        }
                    
                        $html .= "<td ".( $value_class ? "class='".implode(
                                    " ", $value_class
                                )."'" : '' )." data-col='$col_key' data-colname='".$col_data['title']."' data-col-options='".\json_encode( $col_data['options'] )."'>".$edit_open."<span class='value'>".( $value ?: '-' )."</span>".$edit_close.$edit_actions."</td>\n";
                        
                    }
                
                }
                $html .= "</tr>\n";
            
                // Quickfields
                if ( !empty( $this->quick_fields ) ) {
                    foreach ( $this->quick_fields as $table_col => $table_fields ) {
                        if ( isset( $data[ $table_col ] ) ) {
                            $this->jsonRows[ $table_col ] = $data[ $table_col ]; // this is old data, and js will not replace this.
                        }
                    }
                
                    $html .= "<tr></tr>\n<tr id='form-row-".$id."' class='inline-edit-column' style='display:none'>\n";
                    $html .= "<td colspan='".( $col_span + 1 )."'>\n";
                    $html .= "</td>\n</tr>\n";
                }
            }
        
        } else {
        
            $html .= "<tr>\n<td colspan='$col_span'>\n".$this->table_props['no_record']."</td>\n</tr>\n";
        }
    
        $html .= "</tbody>\n";
        $html .= "<tfoot>\n";
        $html .= "<tr>$check_all_col_2\n".$header."\n</tr>\n";
        $html .= "</tfoot>\n";
        $html .= "</table>\n";
        $html .= closeform();
    
        if ( !empty( $this->quick_fields ) && !empty( $cdata ) ) {
            
            $html .= "<div id='qc-input' class='qc-input' style='display: none;'>\n";
            foreach ( $this->quick_fields as $table_col => $table_fields ) {
                $html .= "<div class='clearfix'><span class='tbl-qc-title'>".$table_fields['title']."</span>\n";
                $html .= $table_fields['function']( $table_col, isset( $table_fields['label'] ) ? $table_fields['label'] : '', '', $table_fields ); // you won't be able to use quick fields if you turn off jquery.
                $html .= "</div>\n";
            }
            $html .= form_hidden( 'qc_id', '' );
            $html .= form_button( 'save_quick_editor', 'Update <span class="qc-spinner fa fa-clock-o fa-spin m-l-5" style="display:none;"></span>', 'save_quick_editor', [ 'class' => 'btn-primary pull-right' ] );
            $html .= form_button( 'cancel_quick_editor', 'Cancel', 'cancel' );
            $html .= "</div>\n";
        }
    
        // total rows > current rows limited
        //print_P('Max rows'.$this->maxRows);
        //print_P('rows'.$this->rowsCount);
    
        $html .= "<div class='clearfix'>\n";
        if ( $this->maxRows > $this->rowsCount ) {
            /**
             * Making Page Navigation
             *
             * @param int     $start      The number of the first listed item - $_GET['rowstart']
             * @param int     $count      The number of displayed items - LIMIT on sql
             * @param int     $total      The number of all items - a dbcount of total
             * @param int     $range      The number of links before and after the current page
             * @param string  $link       The base url before the appended part
             * @param string  $getname    the variable name in the query string which stores
             *                            the number of the current page
             * @param boolean $button     Displays as button
             *
             * @return boolean|string FALSE if $count is invalid
             * @global array  $locale
             *
             */
            $html .= "<div class='pull-right m-t-20'>\n";
            $html .= makepagenav( $this->rowStart, $this->query['limit'], $this->maxRows, 3, clean_request( '', [ $this->default_args['rowstart'] ], FALSE ).'&amp;' );
            $html .= "</div>\n";
        }
        $html .= "</div>\n";
    
        add_to_footer( "<script src='".INCLUDES."jquery/tables/tables.js'></script>" );
        
        $table_js = "phpfusion_tables.init(
        '".json_encode( $this->jsonRows )."',
        '".$this->query['id']."',
        '".$this->query['parent']."',
        '".$this->query['table']."',
        $validate_opts,
        '".$this->table_props['ordering_col']."',
        '".$this->table_props['multilang_col']."'
        );";
    
        add_to_jquery( "$table_js" );
        
        return (string)$html;
    
    }
    
    /**
     * @param $column_name
     * @param $filter_options
     *
     * @return string
     * @throws \ReflectionException
     * @todo: bug, there is no way to clear filter options.
     */
    private function dateFilter( $column_name, $filter_options ) {
        
        $sql_conditions = '';
        
        if ( $this->table_props['multilang_col'] || $this->query['conditions'] ) {
            
            $sql_conditions = "WHERE ";
            
            if ( $this->table_props['multilang_col'] ) {
                $sql_conditions .= $this->table_props['multilang_col']."='".LANGUAGE."' AND ";
            }
            
            $sql_conditions .= $this->query['conditions'];
        }
        
        $sql_statement = /** @lang mysql */
            "SELECT YEAR(FROM_UNIXTIME({DATE_COLUMN})) 'table_year', MONTH(FROM_UNIXTIME({DATE_COLUMN})) 'table_month' FROM {TABLE} {JOINS} {CONDITIONS} GROUP BY table_year, table_month ORDER BY {DATE_COLUMN} DESC";
        $date_sql = strtr(
            $sql_statement, [
                '{DATE_COLUMN}' => $column_name,
                '{TABLE}'       => $this->query['table'].' base',
                '{JOINS}'       => $this->query['joins'] ? $this->query['joins'] : '',
                '{CONDITIONS}'  => $sql_conditions
            ]
        );
        
        $date_result = dbquery( $date_sql );
        $date_rows = dbrows( $date_result );
        if ( $date_rows ) {
            $options = [
                'na' => 'Select '.$filter_options['title']
            ];
            
            while ( $date_data = dbarray( $date_result ) ) {
                $month_locale = explode( '|', fusion_get_locale( 'months' ) );
                $options[ $date_data['table_year']."_".$date_data['table_month'] ] = $month_locale[ $date_data['table_month'] ]." ".$date_data['table_year'];
            }
            
            $date_active = isset( $_GET[ $column_name.'-date_year' ] ) && isset( $_GET[ $column_name.'-date_month' ] ) ? $_GET[ $column_name.'-date_year' ]."_".$_GET[ $column_name.'-date_month' ] : '';
            $html = form_select(
                $column_name, '', $date_active, [
                    'class'       => 'display-inline-block m-r-10',
                    //'allowclear'  => TRUE,
                    'inner_width' => '190px',
                    'width'       => '190px',
                    'options'     => $options,
                    //'placeholder' => 'All Dates',
                    //'select_alt' =>true,
                    //'select2_disabled' => TRUE,
                ]
            );
            
            return (string)$html;
        }
    }
    
    private function columnFilter( $column_name, $filter ) {
        
        $options = [
            'na' => $filter['title']
        ];
        
        $options += $filter['options'];
        
        $filter_val = array_flip( array_map( 'strtolower', $options ) );
        
        $value = '';
        
        $column_get = get( $column_name );
        if ( isset( $filter_val[ $column_get ] ) ) {
            $value = isset( $filter_val[ $column_get ] ) ? $filter_val[ $column_get ] : '';
        }
        
        // Output
        //$html = "<div class='display-inline-block'>\n";
        $html = form_select(
            $column_name, '', $value, [
                'inner_width' => '200px',
                'width'       => '200px',
                'class'       => 'display-inline-block m-r-10',
                'options'     => $options,
            ]
        );
        //$html .= "</div>\n"; // close inline
        
        return (string)$html;
    }
    
    /**
     * Provides a method for executing custom actions
     */
    private function bulkActions() {
        if ( method_exists( $this->api, 'bulkActions' ) ) {
            $this->api->bulkActions();
        }
    }
    
    /**
     * Provides a method for deleting
     *
     * @param $data
     */
    private function bulkDelete( $data ) {
        if ( method_exists( $this->api, 'bulkDelete' ) ) {
            $this->api->bulkDelete( $data );
        }
    }
}

require_once INCLUDES.'bbcode_include.php';
