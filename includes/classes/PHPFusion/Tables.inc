<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/PHPFusion/Tables.inc
| Author: PHP-Fusion Inc
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

    private $query = [];

    private $default_args = array(
        'debug'        => FALSE, // True to show the SQL query for the table.
        'table'        => '',
        'id'           => '', // if hierarchy
        'parent'       => '', // if hierarchy
        'limit'        => 24,
        'true_limit'   => FALSE, // if true, the limit is true limit (only limited results will display without page nav)
        'joins'        => '',
        'select'       => '',
        'conditions'   => '', // to match list to a condition. string value only
        'group'        => '', // group by column
        'image_folder' => '', // for deletion (i.e. IMAGES.'folder/') , use param for string match
        'image_field'  => '', // to delete (i.e. news_image)
        'file_field'   => '',  // to delete (i.e. news_attach)
        'file_folder'  => '', // to delete files from the folder, use param for string match
        'db'           => array(), // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
        'delete_function_callback' => '',
    );

    private $columns = array();
    private $table_props = array();
    private $search_text = '';
    private $quick_fields = ''; // determine whether there are quick fields.

    /**
     * Define the table scope parameters
     * See default args
     *
     * @param $args
     *
     * table            DB_CLASS_CATS, // the database name
     * id               id column
     * parent           parent column
     * title            title column
     * limit            show limit
     * image_folder     the image folder path
     * image_field      the image field name
     */
    public function set_table_query($args) {
        $this->query = $args + $this->default_args;
    }

    /**
     * $default_array - API documentation
     *
     * 'display' => array(
     * 'field' => 'cat_id', // table primary key
     * 'table' => DB_CLASS_CATS, // table name
     * 'conditions' => "cat_id IN (:list_cat)", // query condition
     * 'title' => 'cat_title' // table title key,
     * 'order' => 'cat_id ASC', // sort by
     * 'format_result' => "<a href='link-to-url=:field'>:title</a>" // available macros :field and :title
     * )
     *
     *
     * @param $arrays
     */
    public function set_table_columns($arrays) {

        $default_array = array(
            'title'       => '',
            'title_class' => '',
            'value_class' => '',
            'edit_link'   => FALSE,
            'delete_link' => FALSE,
            'image'         => FALSE,
            'image_folder'  => '', // set image folder (method2)
            'default_image' => '',
            'image_width'   => '', // set image width
            'image_class'   => '', // set image class
            'icon'        => '',
            'empty_value' => '',
            'count'       => array(),
            'view_link'   => '',
            'display'     => array(), // API for display
            'date'        => FALSE,
            'options'     => array(),
            'user'        => FALSE,
            'user_avatar' => FALSE, // show avatar
            'number' => FALSE,
            'format'   => FALSE, // for formatting using strtr
            'callback' => '', // for formatting using function
            'debug' => FALSE,
        );

        if (!empty($arrays)) {
            foreach ($arrays as $key => $array) {
                $this->columns[$key] = $array + $default_array;
            }
        }
    }

    /**
     * @param $arrays
     */
    public function set_table_prop($arrays) {
        $default_prop = array(
            'table_class'        => '',
            'header_content'     => '',
            'no_record'          => 'There are no records',
            'search_label'       => 'Search',
            'search_placeholder' => "Search",
            'search_col'         => '', // set this value sql column name to have search input input filter
            'edit_link_format'   => '', // set this to format the edit link
            'delete_link_format' => '', // set this to format the delete link
            'edit_key'           => 'edit',
            'del_key'            => 'del', // change this to invoke internal table delete function for custom delete link format
            'date_col'           => '',  // set this value to sql column name to have date selector input filter
            'order_col'          => '', // set this value to sql column name to have sorting column input filter
            'multilang_col'      => '', // set this value to have multilanguage column filter
            'updated_message'    => 'Entries have been updated', // set this value to have custom success message
            'deleted_message'    => 'Entries have been deleted', // set this value to have the custom delete message,
            'class'              => '', // table class
            'show_count'         => true // show table item count
        );
        $this->table_props = $arrays + $default_prop;
    }

    private $rowsCount = 0;
    private $maxRows = 0;

    /**
     * Callback
     *
     * @return array
     */
    private function get_data() {
        $data = array();
        $search = '';

        if (isset($_POST['search_cat_btn']) && isset($_POST['search_cat'])) {
            $this->search_text = stripinput($_POST['search_cat']);
            redirect(clean_request('s='.$this->search_text, ['s'], FALSE));
        }
        if (isset($_GET['s']) && !empty($this->table_props['search_col'])) {
            $this->search_text = stripinput($_GET['s']);
            //@todo: support two or three columns
            $search = "WHERE ".$this->table_props['search_col']." LIKE ('%$this->search_text%')";
        }
        if (isset($_POST['filter_date'])) {
            $date_filter = explode('_', stripinput($_POST['filter_date']));
            if (count($date_filter) == 2) {
                redirect(clean_request('date_year='.$date_filter[0].'&date_month='.$date_filter[1], ['date_year', 'date_month'], FALSE));
            } else {
                redirect(clean_request('', ['date_year', 'date_month'], FALSE));
            }
        }

        if ($this->table_props['date_col'] && isset($_GET['date_month']) && isset($_GET['date_year'])) {
            $start_time = mktime('0', '0', '0', $_GET['date_month'], 1, $_GET['date_year']);
            $end_time = mktime('0', '0', '0', $_GET['date_month'] + 1, 1, $_GET['date_year']) - (3600 * 24);
            $search = $search ? $search." AND " : "WHERE ";
            $search .= $this->table_props['date_col']." >= '".intval($start_time)."' AND ".$this->table_props['date_col']." <= '".intval($end_time)."'";
        }

        // Automated Ordering
        $order_check = array(
            'asc'  => 'asc',
            'desc' => 'desc'
        );

        $orderby = '';
        if (isset($_GET['orderby']) && isset($_GET['order']) && isset($order_check[$_GET['order']]) && !empty($this->table_props['order_col'])) {
            $sort = $order_check[$_GET['order']];
            $ordering = array_flip($this->table_props['order_col']);
            if (isset($ordering[$_GET['orderby']])) {
                $orderby = $ordering[$_GET['orderby']]." ".$sort.", ";
            }
        } elseif (!empty($this->query['order'])) {
            $orderby = $this->query['order'].", ";
        }

        $conditions = (!empty($this->query['conditions']) ? "{CONDITIONS_PREFIX} ".$this->query['conditions'] : '');
        $count_conditions = strtr($search.$conditions, [
            'WHERE'               => '',
            '{CONDITIONS_PREFIX}' => $search ? ' AND' : '',
        ]);

        if ($this->query['true_limit'] === true) {
            $this->maxRows = $this->query['limit'];
        } else {
            $this->maxRows = dbcount("(".$this->query['id'].")", $this->query['table'], $count_conditions);
        }

        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->maxRows) ? intval($_GET['rowstart']) : 0;

        // Limiter will only work when there are no parent declared as the hierarchy requires full query and array chunks
        if ($this->query['id'] && $this->query['parent']) {

            $sql_statement = "SELECT base.* {SELECT} FROM {TABLE} base {JOINS} {SEARCH} {CONDITIONS} {GROUP} ORDER BY {ORDER_BY} {ID} {ORDER_SORTING} {LIMIT}";
            $sql_query = strtr($sql_statement, [
                '{TABLE}'         => $this->query['table'],
                '{SELECT}'        => ($this->query['select'] ? ", ".$this->query['select'] : ''),
                '{SEARCH}'        => $search,
                '{JOINS}'         => $this->query['joins'],
                '{CONDITIONS}'    => ($search && !empty($this->query['conditions']) ? strtr($conditions, ['{CONDITIONS_PREFIX}' => " AND"]) : strtr($conditions, ['{CONDITIONS_PREFIX}' => "WHERE"])),
                '{GROUP}'         => ($this->query['group'] ? "GROUP BY ".$this->query['group']." " : ''),
                '{ID}'            => $this->query['id'],
                '{ORDER_SORTING}' => 'ASC',
                '{ORDER_BY}'      => $orderby,
                '{LIMIT}'         => ($this->query['true_limit'] === TRUE ? "LIMIT ".$this->query['limit'] : ''), // this limit is different
            ]);

            $data = dbquery_tree_full($this->query['table'], $this->query['id'], $this->query['parent'], '', $sql_query);
            $index = tree_index($data);

            $data = $this->sort_hierarchy_data($data, $index);

            if (!empty($data)) {
                ksort($data);
                $data = array_chunk($data, $this->query['limit']);
                if (!empty($data)) {
                    $chunk_count = 0;
                    // current rows is just 15;
                    foreach ($data as $row_keys => $rows) {
                        $list[$chunk_count] = $rows;
                        $chunk_count = $chunk_count + $this->query['limit'];
                    }
                    $this->rowsCount = $this->query['limit'];
                    $data = $list;
                }
            }
            if ($this->query['debug']) {
                print_p('SQL '.$sql_query.'<br/>Count '.$this->rowsCount, TRUE);
            }

        } else {

            if ($this->query['true_limit'] === true) {
                $limit = "LIMIT ".$this->query['limit'];
            } else {
                $limit = ($this->query['limit'] && isnum($this->query['limit']) ? "LIMIT ".$_GET['rowstart'].", ".$this->query['limit'] : '');
            }

            $sql_statement = "SELECT base.* {SELECT} FROM {TABLE} base {JOINS} {SEARCH} {CONDITIONS} {GROUP} ORDER BY {ORDER_BY} {ID} {ORDER_SORTING} {LIMIT}";
            $sql_query = strtr($sql_statement, [
                '{TABLE}'         => $this->query['table'],
                '{SELECT}'        => ($this->query['select'] ? ", ".$this->query['select'] : ''),
                '{SEARCH}'        => $search,
                '{JOINS}'         => $this->query['joins'],
                '{CONDITIONS}'    => ($search && !empty($this->query['conditions']) ? strtr($conditions, ['{CONDITIONS_PREFIX}' => " AND"]) : strtr($conditions, ['{CONDITIONS_PREFIX}' => "WHERE"])),
                '{GROUP}'         => ($this->query['group'] ? "GROUP BY ".$this->query['group']." " : ''),
                '{ID}'            => $this->query['id'],
                '{ORDER_SORTING}' => 'ASC',
                '{ORDER_BY}'      => $orderby,
                '{LIMIT}'         => $limit,
            ]);

            $result = dbquery($sql_query);
            if ($this->rowsCount = dbrows($result)) {
                while ($rows = dbarray($result)) {
                    $data[$_GET['rowstart']][$rows[$this->query['id']]] = $rows;

                }
            }
            if ($this->query['debug']) {
                print_p('SQL '.$sql_query);
                print_p('Count '.$this->rowsCount);
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
    private function sort_hierarchy_data($categories, $index, $id = 0, $i = 1) {
        $data = &$data;
        if (!empty($categories[$id])) {
            foreach ($categories[$id] as $parentID => $keyData) {
                $keyData['level'] = get_depth($index, $keyData[$this->query['id']]);
                $keyData[$this->query['title']] = str_repeat('— ', $keyData['level'] - 1)." ".$keyData[$this->query['title']];
                // 0 first
                $data[$i] = $keyData;
                $i++;
                if (isset($categories[$keyData[$this->query['id']]])) { // has child
                    $refs = self::sort_hierarchy_data($categories, $index, $keyData[$this->query['id']], $i);
                    $data = array_replace($refs, $data);
                    $i = $i + count($refs);
                }
            }
        }

        return $data;
    }

    /**
     * Do delete record multiple checkboxes
     */
    private function filter_delete_entries() {
        // id
        if (isset($_POST['id']) && is_array($_POST['id'])) {
            foreach ($_POST['id'] as $val) {
                $continue = FALSE;
                if (isnum($val)) {
                    if ($this->query['parent']) {
                        // search for id
                        $self = dbquery("SELECT  ".$this->query['id'].",".$this->query['parent']." FROM ".$this->query['table']." WHERE ".$this->query['id']."=:id", [':id' => $val]);
                        if (dbrows($self)) {
                            $data = dbarray($self);
                            foreach ($data as $key => $value) {
                                $params[':'.$key] = $value;
                            }
                            //append all child to parentless
                            $child_result = dbquery("SELECT ".$this->query['id'].",".$this->query['parent']." FROM ".$this->query['table']." WHERE ".$this->query['parent']."=:id", [':id' => $val]);
                            if (dbrows($child_result)) {
                                while ($cdata = dbarray($child_result)) {
                                    // id
                                    $cdata[$this->query['parent']] = 0;
                                    if ($data[$this->query['parent']]) { // if I have a parent, change all my child to that parent
                                        $cdata[$this->query['parent']] = $data[$this->query['parent']];
                                    }
                                    //dbquery_insert($this->query['table'], $cdata, 'update');
                                }
                            }
                            addNotice('success', $this->table_props['deleted_message']);
                            //dbquery_insert($this->query['table'], $data, 'delete');
                            $continue = TRUE;
                        }
                    } else {
                        addNotice('success', $this->table_props['deleted_message']);
                        //dbquery("DELETE FROM ".$this->query['table']." WHERE ".$this->query['id']."=:id", [':id' => $val]);
                        $continue = TRUE;
                    }
                }

                if ($continue) {
                    // delete all images - images is by "," delimiter in a single column
                    if ($this->query['image_field'] && $this->query['image_folder']) {
                        $this->query['image_folder'] = strtr($this->query['image_folder'], $params);
                        if ($data[$this->query['image_field']]) {
                            $list_images = explode(',', $data[$this->query['image_field']]);
                            foreach ($list_images as $filename) {
                                if (file_exists($this->query['image_folder'].$filename)) {
                                    unlink($this->query['image_folder'].$filename);
                                }
                            }
                        }
                    }
                    // delete all files - files is by "," delimiter in a single column
                    if ($this->query['file_field'] && $this->query['file_folder']) {
                        $this->query['file_folder'] = strtr($this->query['file_folder'], $params);
                        if ($data[$this->query['file_field']]) {
                            $list_images = explode(',', $data[$this->query['file_field']]);
                            foreach ($list_images as $filename) {
                                if (file_exists($this->query['file_folder'].$filename)) {
                                    unlink($this->query['file_folder'].$filename);
                                }
                            }
                        }
                    }
                    // other to delete
                    // ratings, reviews, comments, photos
                    if (!empty($this->query['db'])) {
                        foreach ($this->query['db'] as $table_name => $table_prop) {
                            $custom_condition = !empty($table_prop['custom']) ? "AND ".$table_prop['custom'] : "";
                            if (dbcount("(".$table_prop['select'].")", $table_name, $table_prop['group']."='".$data[$this->query['id']]."' $custom_condition")) {
                                dbquery("DELETE FROM ".$table_name." WHERE ".$table_prop['group']."='".$data[$this->query['id']]."' $custom_condition");
                            }
                        }
                    }
                    //function callback
                    if (!empty($this->query['del_function_callback'])) {
                        $this->query['delete_function_callback']($data);
                        if (is_callable($this->query['delete_function_callback'])) {
                            $this->query['delete_function_callback']($data);
                        }
                    }
                }

            }
            redirect(FUSION_REQUEST);
        }
    }

    public function set_quick_edit_fields($array) {
        $default_options = array(
            'title'    => '',
            'required' => FALSE,
            'dynamics' => 'form_text'
        );
        if (!empty($array)) {
            foreach ($array as $key => $fields) {
                $fields += $default_options;
                $array[$key] = $fields;
            }
            $this->quick_fields = $array;
        }
    }

    public function display_table($print = FALSE) {
        $html = '';
        $cdata = $this->get_data();

        $col_span = count($this->columns) + 1;
        $header = '';
        $order_col = $this->table_props['order_col'];
        if (isset($_POST['filter_actions'])) {
            switch ($_POST['filter_actions']) {
                case 1: // delete
                    $this->filter_delete_entries();
                    break;
                default:
            }
        }

        if (isset($_GET[$this->table_props['del_key']]) && isnum($_GET[$this->table_props['del_key']])) {
            $result = dbquery("SELECT * FROM ".$this->query['table']." WHERE ".$this->query['id']."=:id", [':id' => intval($_GET[$this->table_props['del_key']])]);
            if (dbrows($result)) {
                $del_data = dbarray($result);
                foreach ($del_data as $key => $value) {
                    $params[':'.$key] = $value;
                }
                //print_p($params);

                // Remove Image
                if ($this->query['image_field'] && $this->query['image_folder']) {

                    $this->query['image_folder'] = strtr($this->query['image_folder'], $params);

                    if (!empty($del_data[$this->query['image_field']])) {
                        // support column with ',' images list (i.e. the value is 'abc.png,bbb.jpg')
                        $list_images = explode(',', $del_data[$this->query['image_field']]);
                        foreach ($list_images as $filename) {
                            if (file_exists($this->query['image_folder'].$filename)) {
                                $file = $this->query['image_folder'].$filename;
                                //print_p($file);
                                unlink($file);
                            }
                        }

                    }
                }

                // Remove File
                if ($this->query['file_field'] && $this->query['file_folder']) {

                    $this->query['file_folder'] = strtr($this->query['file_folder'], $params);

                    if (!empty($del_data[$this->query['file_field']])) {
                        // support column with ',' files list (i.e. the value is 'abc.rar,bbb.zip')
                        $list_images = explode(',', $del_data[$this->query['file_field']]);
                        //print_p($this->query['file_folder'].$list_images[0]);
                        foreach ($list_images as $filename) {
                            if (file_exists($this->query['file_folder'].$filename)) {
                                $file = $this->query['file_folder'].$filename;
                                //print_p($file);
                                unlink($file);
                            }
                        }

                    }
                }

                /*
                 * Custom delete
                 */
                if (!empty($this->query['db'])) {
                    foreach ($this->query['db'] as $table_name => $table_prop) {
                        $custom_condition = (!empty($table_prop['custom']) ? 'AND '.$table_prop['custom'] : '');
                        if (dbcount("(".$table_prop['select'].")", $table_name, $table_prop['group']."='".$del_data[$this->query['id']]."' $custom_condition")) {
                            dbquery("DELETE FROM ".$table_name." WHERE ".$table_prop['group']."='".$del_data[$this->query['id']]."' $custom_condition");
                        }
                    }
                }

                if (!empty($this->query['delete_function_callback'])) {
                    if (is_callable($this->query['delete_function_callback'])) {
                        $this->query['delete_function_callback']($del_data);
                    }
                }

                dbquery_insert($this->query['table'], $del_data, 'delete');
                addNotice('success', $this->table_props['deleted_message']);
                redirect(clean_request('', array('del'), FALSE));
            }
        }

        if (!empty($this->quick_fields)) {
            if (!empty($_POST['save_quick_editor'])) {
                $post_data[$this->query['id']] = form_sanitizer($_POST[$this->query['id']], '', $this->query['id']);
                foreach ($this->quick_fields as $table_col => $table_fields) {
                    $post_data[$table_col] = form_sanitizer($_POST[$table_col], '', $table_col);
                }
                if (\Defender::safe()) {
                    dbquery_insert($this->query['table'], $post_data, 'update', ['keep_session' => true]);
                }
                redirect(FUSION_REQUEST);
            }
        }


        if (is_array($this->table_props['order_col'])) {
            foreach ($this->table_props['order_col'] as $col_key => $col_name) {
                $current_order_col[$col_key] = $col_name;
            }
        } else {
            $current_order_col[$this->table_props['order_col']] = $order_col;
        }

        foreach ($this->columns as $col_key => $col_data) {
            $order_open = '';
            $order_close = '';
            $order_caret = array(
                'asc'  => 'order-caret-up',
                'desc' => 'order-caret-down'
            );
            $order_values = array_flip($current_order_col);
            if (isset($current_order_col[$col_key])) { // turn on carets
                $order = 'asc';
                $order_class = $order_caret['asc'];
                $order_value = $current_order_col[$col_key]; // title
                if (isset($_GET['order']) && isset($_GET['orderby'])) {
                    $order = $_GET['order'];
                    if (isset($_GET['orderby']) && isset($order_values[$_GET['orderby']]) && $order_values[$_GET['orderby']] == $col_key) {
                        $order_class = 'order-caret-up-current';
                        $order = 'desc';
                        if ($_GET['order'] == 'desc') {
                            $order_class = 'order-caret-down-current';
                            $order = 'asc';
                        }
                    }
                }
                $order_open = "<a class='$order_class' href='".clean_request("orderby=".$order_value."&order=".$order, ['order', 'orderby'], FALSE)."'>\n";
                $order_close = "</a>\n";
            }
            $header .= "<th ".($col_data['title_class'] ? "class='".$col_data['title_class']."'" : "").">".$order_open.$col_data['title'].$order_close."</th>\n";
        }
        if (!empty($this->table_props['header_content']) || !empty($this->table_props['search_col'])) {
            $html .= "<div class='row'>\n";
        }

        if (!empty($this->table_props['header_content'])) {
            $html .= "<div class='col-xs-12 col-sm-".($this->table_props['search_col'] ? 6 : 12)."'>\n";
            $html .= $this->table_props['header_content'];
            $html .= "</div>\n";
        }

        if (!empty($this->table_props['search_col'])) {
            $html .= "<div class='col-xs-12 col-sm-".($this->table_props['header_content'] ? 6 : 12)." text-right'>\n";
            $html .= "<div class='pull-right'>\n";
            $html .= openform('search_frm', 'post', FUSION_REQUEST);
            $html .= form_text('search_cat', '', $this->search_text, [
                'required'           => TRUE,
                'class'              => 'm-b-0',
                'placeholder'        => $this->table_props['search_placeholder'],
                'append'             => TRUE,
                'append_value'       => $this->table_props['search_label'],
                'append_form_value'  => 'search_cat',
                'append_button_name' => 'search_cat_btn',
                'append_button'      => TRUE,
                'width' => '350px',
            ]);
            $html .= closeform();
            $html .= "</div>\n"; // close pull-right
            $html .= "</div>\n"; // close text right
        }

        if (!empty($this->table_props['header_content']) || !empty($this->table_props['search_col'])) {
            $html .= "</div>\n"; // close row
            $html .= "<hr/>\n";
        }

        $start_row = '';
        $end_row = '';
        if ($this->table_props['show_count'] === true or $this->quick_fields or !empty($this->table_props['date_col'])) {
            $start_row = "<div class='clearfix' style='width:100%;'>\n";
            $end_row = "</div>\n"; // close clearfix
        }
        $html .= $start_row;
        if ($this->table_props['show_count'] === true) {
            $html .= "<div class='pull-right m-b-10'>".format_word($this->maxRows, "item|items")."</div>";
        }
        if (!empty($this->quick_fields)) {
            $html .= "<div class='display-inline-block m-r-15'>\n";
            $html .= openform('cat_table_frm', 'post', FUSION_REQUEST).form_select('filter_actions', '', '', [
                    'inner_width' => '250px',
                    'options'     => [
                        1 => 'Delete',
                    ],
                    'allowclear'  => TRUE,
                    'placeholder' => 'Bulk Actions',
                    'stacked'     => form_button('apply_filter', 'Apply', 'apply_filter').form_hidden('filter_ids', '', '')
                ]).closeform();
            $html .= "</div>\n";
        }

        // date filters
        if (!empty($this->table_props['date_col'])) {
            $sql_conditions = '';
            if ($this->table_props['multilang_col'] || $this->query['conditions']) {
                $sql_conditions = "WHERE ";
                if ($this->table_props['multilang_col']) {
                    $sql_conditions .= $this->table_props['multilang_col']."='".LANGUAGE."' AND ";
                }
                $sql_conditions .= $this->query['conditions'];
            }

            $sql_statement = "SELECT YEAR(FROM_UNIXTIME({DATE_COLUMN})) 'table_year', MONTH(FROM_UNIXTIME({DATE_COLUMN})) 'table_month' FROM {TABLE} {CONDITIONS} GROUP BY table_year, table_month ORDER BY {DATE_COLUMN} DESC";

            $date_sql = strtr($sql_statement, [
                '{DATE_COLUMN}' => $this->table_props['date_col'],
                '{TABLE}'       => $this->query['table'],
                '{CONDITIONS}'  => $sql_conditions
            ]);

            $date_result = dbquery($date_sql);
            if (dbrows($date_result)) {
                $date_options = [];
                while ($date_data = dbarray($date_result)) {
                    $month_locale = explode('|', fusion_get_locale('months'));
                    $date_options[$date_data['table_year']."_".$date_data['table_month']] = $month_locale[$date_data['table_month']]." ".$date_data['table_year'];
                }
                $date_active = isset($_GET['date_year']) && isset($_GET['date_month']) ? $_GET['date_year']."_".$_GET['date_month'] : "";

                $html .= "<div class='display-inline-block'>\n";
                $html .= openform('date_frm', 'post', FUSION_REQUEST).form_select('filter_date', '', $date_active, [
                        'allowclear'  => TRUE,
                        'inner_width' => '200px',
                        'options'     => $date_options,
                        'placeholder' => 'All Dates',
                        'stacked'     => form_button('date_filter', 'Filter', 'date_filter', ['class'=>'btn-default m-r-10'])
                    ]).closeform();
                $html .= "</div>\n"; // close inline
            }
        }
        if (isset($_GET['s'])) {
            $html .= "<a href='".clean_request('', ['s'], FALSE)."'>Clear Search</a>";
        }
        $html .= $end_row;

        $check_all_col = "";
        $check_all_col_2 = "";
        if (!empty($this->quick_fields)) {
            $check_all_col = "<th class='min'>".form_checkbox('chk_all', '', '', ['class' => 'm-b-0'])."</th>\n";
            $check_all_col_2 = "<th>".form_checkbox('chk_all2', '', '', ['class' => 'm-b-0'])."</th>\n";
        }
        $html .= "<table class='".($this->table_props['table_class'] ? ' '.$this->table_props['table_class'] : 'table')."'>\n";
        // head
        $html .= "<thead>\n";
        $html .= "<tr>\n$check_all_col\n".$header."\n</tr>\n";
        $html .= "</thead>\n";

        $html .= "<tbody>\n";
        // end of head

        if (!empty($cdata)) {

            foreach ($cdata[$_GET['rowstart']] as $data) {

                $id = $data[$this->query['id']];
                // the edit link
                // key need to be dynamic as the del and edit may be present.
                $get_rid = [$this->table_props['edit_key'], $this->table_props['del_key']];
                // All the info available should be replaced.
                //print_p(array_values($this->columns));

                // Search Strings
                $search_str = array();
                foreach ($data as $key => $value) {
                    $search_str[":".$key] = $value;
                }

                $edit_uri = $this->table_props['edit_link_format'] ? strtr(urldecode($this->table_props['edit_link_format'].$id), $search_str) : clean_request($this->table_props['edit_key'].'='.$id, $get_rid, FALSE);
                $del_uri = $this->table_props['delete_link_format'] ? strtr(urldecode($this->table_props['delete_link_format'].$id), $search_str) : clean_request($this->table_props['del_key'].'='.$id, $get_rid, FALSE);

                $checkbox_col = '';
                if (!empty($this->quick_fields)) {
                    $checkbox_col = "<td>\n".form_checkbox("id[]", '', '', ['value' => $id, 'class' => 'm-b-10'])."</td>\n";
                }

                $html .= "<tr id='entry-".$id."'>\n$checkbox_col\n";
                foreach ($this->columns as $col_key => $col_data) { // column configurations
                    $edit_open = '';
                    $edit_close = '';
                    $edit_actions = '';
                    $value_class = '';
                    $data_replace = array();
                    if (!empty($data)) {
                        foreach (array_keys($data) as $keyname) {
                            $data_replace[':'.$keyname] = $data[$keyname];
                        }
                    }
                    $value = (!empty($data[$col_key]) ? $data[$col_key] : $col_data['empty_value']);

                    if ($col_data['edit_link'] || $col_data['delete_link']) {

                        /*
                         * (Optional - sets the current column with the edit link, with quick edit fields)
                         * array("edit_link" => TRUE)
                         */
                        $edit_open = "<a href='$edit_uri' class='strong'>\n";
                        $edit_close = "</a>\n";
                        $edit_actions = "<div class='tbl_actions'>\n";
                        $edit_actions .= ($col_data['edit_link'] ? "<span class='edit'><a href='$edit_uri'>Edit</a></span>\n" : '');
                        $edit_actions .= ((!empty($this->quick_fields)) ? "<span class='qedit'><a class='quick_edit pointer' data-toggle='quick_edit' data-value='".$id."'>Quick Edit</a></span>\n" : '');
                        $edit_actions .= ($col_data['delete_link'] ? "<span class='del'><a onclick='return confirm(\"Are you sure you want to delete this entry? All associated files and images will be permanently removed from the server.\");' href='$del_uri' class='text-danger'>Delete</a></span>\n" : '');
                        $edit_actions .= ($col_data['view_link'] ? "<span class='view'><a href='".$col_data['view_link'].$id."'>View</a></span>\n" : '');
                        $edit_actions .= "</div>\n";

                    }

                    if ($col_data['value_class']) {
                        /*
                         * Class of the value. I.e. "strong, small"
                         * array("value_class"=> "small");
                         */
                        $value_class = "class='".$col_data['value_class']."'";
                    }

                    /*
                     * Types of display method supported
                     */
                    if ($col_data['image']) {
                        /*
                         * array("image"=>TRUE);
                         *
                         */
                        $width = (!empty($col_data['image_width']) ? $col_data['image_width']."; max-width: ".$col_data['image_width'] : 'max-width:150px;');
                        $class = (!empty($col_data['image_class']) ? $col_data['image_class'] : 'img-responsive');

                        $image_folder = '';
                        if (!empty($this->query['image_folder'])) {
                            $image_folder = strtr($this->query['image_folder'], $data_replace);

                        } elseif (!empty($col_data['image_folder'])) {
                            $image_folder = strtr($col_data['image_folder'], $data_replace);
                        }

                        $default_image_path = ($col_data['default_image'] ?: IMAGES.'imagenotfound.jpg');

                        $value = "<img class='$class' style='$width' src='$default_image_path'/>\n";;
                        if (is_file($image_folder.$data[$col_key])) {
                            $value = "<img class='$class' style='$width' src='".$image_folder.$data[$col_key]."'/>\n";
                        }

                    } elseif ($col_data['icon'] && !empty($data[$col_key])) {
                        /*
                         * array("icon" => TRUE)
                         */
                        $value = "<i class='".$data[$col_key]."'/></i>\n";

                    } elseif ($col_data['number']) {
                        /*
                         * array("number" => true, "delimiter" => "2", "decimal_point" => ".", "thousand_sep" => ",")
                         */
                        if (!empty($data[$col_key]) && isnum($data[$col_key])) {
                            $value = number_format(
                                $data[$col_key],
                                (!empty($col_data['delimiter']) ? $col_data['delimiter'] : 0),
                                (!empty($col_data['decimal_point']) ? $col_data['decimal_point'] : '.'),
                                (!empty($col_data['thousand_sep']) ? $col_data['thousand_sep'] : ',')
                            );
                        } else {

                            $value = number_format($value ?: '0',
                                (!empty($col_data['delimiter']) ? $col_data['delimiter'] : 0),
                                (!empty($col_data['decimal_point']) ? $col_data['decimal_point'] : '.'),
                                (!empty($col_data['thousand_sep']) ? $col_data['thousand_sep'] : ',')
                            );

                            if (empty($value)) {
                                $value = (!empty($col_data['delimiter']) ? "0".(!empty($col_data['thousand_sep']) ? $col_data['thousand_sep'] : ',').str_repeat($col_data['delimiter'], "0") : "0");
                                //print_P($col_key.' '.$value);
                            }

                        }

                        //print_P($value);

                    } elseif ($col_data['user'] === true) {
                        /*
                         * array("user" => TRUE);
                         */
                        $user = fusion_get_user($value);
                        $avatar = '';
                        if ($col_data['user_avatar'] === true) {
                            $avatar = display_avatar($user, '30px', 'm-r-5');
                        }
                        $value = $avatar.profile_link($user['user_id'], $user['user_name'], $user['user_status']);

                    } elseif ($col_data['options']) {
                        /*
                         * array("options" => array(1=>"Yes", 0=>"No"))
                         */
                        $value = $value ?: 0;
                        $value = $col_data['options'][$value];

                    } elseif ($col_data['count']) {
                        /*
                         * array("count" => array("field"=>"column_name", "table" => DB_PREFIX, "conditions" => "column_name=matches"))
                         */
                        if (!empty($col_data['count']['field']) && !empty($col_data['count']['table']) && !empty($col_data['count']['conditions'])) {
                            $data_replace = array();
                            foreach (array_keys($data) as $keyname) {
                                $data_replace[':'.$keyname] = $data[$keyname];
                            }
                            $col_data['count']['conditions'] = strtr($col_data['count']['conditions'], $data_replace);
                            $value = dbcount("(".$col_data['count']['field'].")", $col_data['count']['table'], $col_data['count']['conditions']);
                        }

                    } elseif ($col_data['display'] && $col_data['display']['table'] && $col_data['display']['key'] && $col_data['display']['title']) {

                        /*
                         * array('display' => array(
                                          'key'        => 'feature_id',
                                          'table'      => DB_CLASS_FEATURE,
                                          'conditions' => "feature_id IN (:cat_feature)",
                                          'title'      => 'feature_title',
                                          'format_result' => "<a href='".INFUSIONS."farlayne/administration/listings/features.php?".fusion_get_aidlink()."&amp;edit=:key'>:title</a>",
                                      )
                        )
                         */
                        $value = '';
                        if (!empty($data[$col_key])) {

                            $default_select = '';
                            $default_condition = '';
                            $default_order = " ORDER BY ".$col_data['display']['key']." ASC";

                            if (!empty($col_data['display']['select'])) {
                                $col_data['display']['select'] = ", ".$col_data['display']['select'];
                            } else {
                                $col_data['display']['select'] = $default_select;
                            }

                            if (!empty($col_data['display']['conditions'])) {
                                if ($col_data['debug'] === true) {
                                    print_p($data_replace);
                                    print_p($col_data['display']['conditions']);
                                }
                                $col_data['display']['conditions'] = " WHERE ".strtr($col_data['display']['conditions'], $data_replace);
                            } else {
                                $col_data['display']['conditions'] = $default_condition;
                            }

                            if (empty($col_data['display']['order'])) {
                                $col_data['display']['order'] = $default_order;
                            }

                            $formatted_value = [];
                            $sub_query = "SELECT ".$col_data['display']['key'].", ".$col_data['display']['title'].$col_data['display']['select']." FROM ".$col_data['display']['table']." ".$col_data['display']['conditions']." ".$col_data['display']['order']." ".(!empty($col_data['display']['limit']) ? $col_data['display']['limit'] : '');
                            if ($col_data['debug'] === true) {
                                print_p($sub_query);
                                print_p($data_replace);
                            }

                            $sub_query = dbquery($sub_query);
                            if (dbrows($sub_query)) {

                                while ($sub_data = dbarray($sub_query)) {

                                    $search_str = array(
                                        ':key'   => $sub_data[$col_data['display']['key']],
                                        ':title' => $sub_data[$col_data['display']['title']]
                                    );
                                    //print_p($sub_data);
                                    foreach ($data as $key => $value) {
                                        $search_str[":".$key] = $value;
                                    }
                                    // we need sub_data to support all previous columns
                                    $sub_data = array_merge($sub_data, $data);
                                    if ($col_data['debug'] === true) {
                                        print_p($search_str);
                                        print_P($sub_data);
                                        print_p($col_data['display']['format_result']);
                                    }
                                    $formatted_value[] = (!empty($col_data['display']['format_result']) ? strtr(urldecode($col_data['display']['format_result']), $search_str) : $sub_data[$col_data['display']['title']]);
                                }

                                $formatted_value = array_filter($formatted_value);

                                if ($col_data['debug'] === true) {
                                    print_p($formatted_value);
                                }
                            } else {
                                // No result
                                $search_str = array();
                                foreach ($data as $key => $value) {
                                    $search_str[":".$key] = $value;
                                }
                                $formatted_value = !empty($col_data['display']['no_result']) ? strtr(urldecode($col_data['display']['no_result']), $search_str) : "";
                            }

                            if (is_array($formatted_value)) {
                                $value = implode(', ', $formatted_value);
                            } else {
                                $value = $formatted_value;
                            }

                        }

                    } elseif (!empty($col_data['date'])) {
                        /*
                         * array("date" => TRUE, "date_format" => "shortdate")
                         */
                        $date_format = (!empty($col_data['date_format']) ? $col_data['date_format'] : "shortdate");
                        $value = showdate($date_format, $value);

                    }

                    elseif (!empty($col_data['array']) && is_array($col_data['array'])) {
                        /*
                         * Similar to options
                         * array("array" => array(1=>"Yes", 0=>"No"))
                         */
                        $value = isset($col_data['array'][$value]) ? $col_data['array'][$value] : $col_data['array'][0];

                    }

                    if (!empty($col_data['format'])) {
                        /*
                         * Formats the value into a specific format
                         * Usage: array("format" => ":column_name item(s)")
                         * array("format"      => "<a href='".ROADMAP."?id=:item_id'>:item_summary</a>")
                         * */

                        if ($col_data['debug']) {
                            print_p($data_replace);
                            print_p($value);
                        }
                        if (!empty($value)) {
                            $data_replace[':'.$col_key] = $value;
                        }

                        $value = strtr($col_data['format'], $data_replace);
                    }

                    if (!empty($col_data['callback'])) {
                        /*
                         * Run your own callback function with the current column data
                         * Usage on Class Callback: $options['callback'] = array("object_class", "custom_function", "the-path-to-your-class");
                         * Usage on Function Callback: $options['callback'] = "custom_function";
                         */
                        if (!empty($value)) {
                            $data_replace[':'.$col_key] = $value;
                        }

                        if (is_array($col_data['callback'])) {
                            // head to new reflexion class
                            // for reflection to work you need to push in your PSR4 autoloader file.
                            require_once $col_data['callback'][2];
                            unset($col_data['callback'][2]);
                            //print_p(get_called_class());
                            $object = new \ReflectionClass($col_data['callback'][0]);
                            $class = $object->newInstance();
                            $method = array(
                                $class,
                                $col_data['callback'][1]
                            );
                            if (is_callable($method)) {
                                $value = call_user_func($method, $data_replace);
                            } elseif ($col_data['debug']) {
                                addNotice("danger", "Callback could not be made");
                            }
                        } else {
                            //print_p(get_defined_functions());
                            // If your file has a namespace, please add them in as prefix
                            // some_namespace/callback_function_name
                            if (is_callable($col_data['callback'])) {
                                $value = $col_data['callback']($data_replace);
                            } elseif ($col_data['debug']) {
                                addNotice("danger", "Callback could not be made");
                            }
                        }

                    }

                    $html .= "<td $value_class>".$edit_open.$value.$edit_close.$edit_actions."</td>\n";
                }
                $html .= "</tr>\n";

                // Quickfields
                if (!empty($this->quick_fields)) {
                    $html .= "<tr></tr><tr id='form-".$id."' style='display:none'>\n<td colspan='$col_span'>\n";
                    $html .= openform('table_frm', 'post', FUSION_REQUEST);
                    foreach ($this->quick_fields as $table_col => $table_fields) {
                        if (!isset($table_fields['input_id'])) {
                            $table_fields['input_id'] = $table_col.'_'.$id;
                        }
                        //print_p($table_fields['title'].','.$data[$table_col]);
                        //print_p($table_fields);
                        $html .= $table_fields['dynamics']($table_col, $table_fields['title'], $data[$table_col], $table_fields);
                    }
                    $html .= form_hidden($this->query['id'], '', $id);
                    $html .= form_button('save_quick_editor', 'Update', 'save_quick_editor', ['class' => 'btn-default pull-right', 'input_id' => 'save_quick_editor_'.$id]);
                    $html .= form_button('cancel_quick_editor', 'Cancel', $id, ['input_id' => 'cancel_quick_editor_'.$id]);
                    $html .= closeform();
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
        // total rows > current rows limited
        //print_P('Max rows'.$this->maxRows);
        //print_P('rows'.$this->rowsCount);

        $html .= "<div class='clearfix'>\n";
        if ($this->maxRows > $this->rowsCount) {
            /**
             * Making Page Navigation
             *
             * @global array  $locale
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
             */
            $html .= "<div class='pull-right m-t-20'>\n";
            $html .= makepagenav($_GET['rowstart'], $this->query['limit'], $this->maxRows, 3, clean_request('', ['rowstart'], FALSE).'&amp;');
            $html .= "</div>\n";
        }
        $html .= "</div>\n";

        if (!empty($this->quick_fields)) {
            echo closeform();
            add_to_jquery("
            $('#chk_all, #chk_all2').bind('click', function(e) {
                val = $(this).is(':checked') ? 1 : 0;    
                setChecked('table_frm', 'id[]', val);
                setChecked('table_frm', 'chk_all', val);
                setChecked('table_frm', 'chk_all2', val);
            });        
            $('.quick_edit').bind('click', function(e) {
                e.preventDefault();
                var val = $(this).data('value');
                if (val) {
                    $('#form-'+val).show();
                    $('#entry-'+val).hide();
                }
            });
            $('button[name*=\"cancel_quick_editor\"]').bind('click', function(e) {
                e.preventDefault();
                var val = $(this).val();                
                if (val) {
                    $('#form-'+val).hide();
                    $('#entry-'+val).show();
                }                                
            });
            ");
        }

        if ($print === TRUE) {
            echo $html;
        } else {
            return $html;
        }

    }
}