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

    private $jsonRows = [];

    private $rowsCount = 0;

    private $maxRows = 0;

    private $rowStart = 0;

    private $echo = TRUE;

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
        'image_field'              => '', // to delete (i.e. news_image)
        'file_field'               => '',  // to delete (i.e. news_attach)
        'file_folder'              => '', // to delete files from the folder, use param for string match
        'db'                       => [], // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
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
        'multilang' => FALSE,
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
        'date_col'                => '',  // set this value to sql column name to have date selector input filter
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
    public function __construct(TableSDK $api, $echo = TRUE) {

        if ($api instanceof TableSDK) {

            $this->api = $api;

            $this->echo = $echo;

            $this->setTableProperties();

            $this->setTableData();

            $this->setTableCols();

            $this->setQuickEdit();

            $this->setCustomSettings();

            if ($echo === TRUE) {

                echo $this->displayTable();

            }

        } else {

            echo 'Your current object requires the implementation of the \PHPFusion\Interfaces\TableSDK interface.';

        }

    }

    private function setCustomSettings() {
        // Post and set customization
        if (post('apply_custom')) {
            $column_cookie_val = sanitizer(['custom_column'], '', 'custom_column[]');
            if ($column_cookie_val) {
                setcookie($this->table_props['table_id'].'-cols', $column_cookie_val);
            }

            $limit_val = sanitizer('custom_limit', $this->query['limit']);
            if ($limit_val) {
                setcookie($this->table_props['table_id'].'-limit', $limit_val);
            }

            redirect(FUSION_REQUEST);
        }

    }

    /**
     * Set the table data configurations
     */
    private function setTableData() {

        $this->query = (array)$this->api->data() + $this->default_args;

        $php_limit_cookie = cookie($this->table_props['table_id'].'-limit');
        if ($php_limit_cookie && isnum($php_limit_cookie)) {

            $this->query['limit'] = $php_limit_cookie;
        }

    }

    /**
     * Set the table column data
     */
    private function setTableCols() {

        if (!empty($this->api->column())) {

            foreach ($this->api->column() as $key => $array) {

                $this->columns[$key] = $array + $this->default_column_options;

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
        if (empty($this->query_id) && $this->query['id']) {
            $this->query_id = str_replace('base.', '', $this->query['id']);
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

        if (post('search_cat_btn') && post('search_cat')) {
            $this->search_text = stripinput(post('search_cat'));
            redirect(clean_request('s='.$this->search_text, ['s'], FALSE));
        }

        if (get('s') && !empty($this->table_props['search_col'])) {
            $this->search_text = stripinput($_GET['s']);
            //@todo: support two or three columns
            $search = "WHERE ".$this->table_props['search_col']." LIKE ('%$this->search_text%')";
        }

        // Dropdown link filters redirect to $_GET
        if (post('table_filter') && !empty($this->table_props['dropdown_filters'])) {

            $redirect_param = [];
            $redirect_keys = [];

            foreach ($this->table_props['dropdown_filters'] as $key => $filter) {

                if ($filter['type'] == 'date') {
                    $date_filter = explode('_', post($key));
                    if (count($date_filter) == 2) {

                        $redirect_param[$key.'-date_year'] = $date_filter[0];

                        $redirect_param[$key.'-date_month'] = $date_filter[1];

                        $redirect_keys[] = $key.'-date_year';

                        $redirect_keys[] = $key.'-date_month';
                    }
                } else {

                    if (post($key)) {

                        $filter_val = array_map('strtolower', $filter['options']);
                        $val = stripinput(post($key));
                        if (isset($filter_val[$val])) {
                            $redirect_param[$key] = $filter_val[$val];
                        } else {
                            $redirect_param[$key] = $val;
                        }
                        $redirect_keys[] = $key;
                    }
                }
            }
            $redirect_request = clean_request(http_build_query($redirect_param), $redirect_keys, FALSE);
            redirect($redirect_request);
        }

        // generate filters conditions
        $filter_cond = [];
        if (!empty($this->table_props['link_filters'])) {
            foreach ($this->table_props['link_filters'] as $key => $filters) {

                if (!empty($_GET[$key])) { // we are getting unpublished
                    $filter_val = array_flip(array_map('strtolower', $filters['options']));
                    $get_val = strtolower(get($key));
                    if (isset($filter_val[$get_val])) {

                        if (strlen($filter_val[$get_val])) {
                            $filter_cond[] = "$key='".$filter_val[$get_val]."'";
                        }

                    }
                }
            }
        }

        // @todo: complete this part.
        // generate filters conditions for dropdown redirects.
        if (!empty($this->table_props['dropdown_filters'])) {
            foreach ($this->table_props['dropdown_filters'] as $key => $filters) {
                switch ($filters['type']) {
                    default:
                    case 'array':
                        // done.
                        if (isset($filters['options']) && isset($_GET[$key])) {
                            $filter_val = array_flip(array_map('strtolower', $filters['options']));
                            $get_val = strtolower($_GET[$key]);
                            if (isset($filter_val[$get_val])) {
                                $filter_cond[] = "$key='".$filter_val[$_GET[$key]]."'";
                            }
                        }
                        break;
                    case 'date':
                        if (isset($_GET[$key.'-date_month']) && isset($_GET[$key.'-date_year'])) {

                            $start_time = mktime('0', '0', '0', $_GET[$key.'-date_month'], 1, $_GET[$key.'-date_year']);

                            $end_time = mktime('0', '0', '0', $_GET[$key.'-date_month'] + 1, 1, $_GET[$key.'-date_year']) - (3600 * 24);

                            $filter_cond[] = $key." >= '".intval($start_time)."' AND $key <= '".intval($end_time)."'";

                        }
                        break;
                }
            }
        }

        if (!empty($filter_cond)) {
            $search = ($search ? $search." AND " : "WHERE ").implode(' AND ', $filter_cond);
        }

        // Automated Ordering
        $order_check = [
            'asc'  => 'asc',
            'desc' => 'desc'
        ];

        $orderby = '';

        $get_orderby = get('orderby');

        $get_order = get('order'); // asc or desc

        if ($get_orderby && $get_order && isset($order_check[$get_order]) && !empty($this->table_props['order_col'])) {

            $sort = $order_check[$get_order];

            $ordering = array_flip($this->table_props['order_col']);

            if (isset($ordering[$get_orderby])) {

                $orderby = $ordering[$get_orderby]." ".$sort.", ";
            }

        } else if (!empty($this->query['order'])) {

            $orderby = $this->query['order'].", ";
        }

        $conditions = (!empty($this->query['conditions']) ? "{CONDITIONS_PREFIX} ".$this->query['conditions'] : '');

        $count_conditions = strtr(
            $search.$conditions, [
                'WHERE'               => '',
                '{CONDITIONS_PREFIX}' => $search ? ' AND' : '',
            ]
        );

        $sql_table = "{TABLE} base {JOINS}";
        $sql_table = strtr($sql_table, [
            '{TABLE}' => $this->query['table'],
            '{JOINS}' => $this->query['joins'],
        ]);

        // quantify max rows
        $this->maxRows = dbcount("(".$this->query['id'].")", $sql_table, $count_conditions);

        if ($this->query['true_limit'] === TRUE) {
            $this->maxRows = $this->query['limit'];
        }

        $this->rowStart = get($this->default_args['rowstart'], FILTER_VALIDATE_INT);
        $this->rowStart = $this->rowStart && isnum($this->rowStart) && $this->rowStart <= $this->maxRows ? intval($this->rowStart) : 0;

        // Limiter will only work when there are no parent declared as the hierarchy requires full query and array chunks
        $sql_statement = /** @lang mysql */
            "SELECT base.* {SELECT} FROM {TABLE} {SEARCH} {CONDITIONS} {GROUP} ORDER BY {ORDER_BY} {ID} {ORDER_SORTING} {LIMIT}";

        if ($this->query['id'] && $this->query['parent']) {


            $sql_query = strtr(
                $sql_statement, [
                    '{TABLE}'         => $sql_table,
                    '{SELECT}'        => ($this->query['select'] ? ", ".$this->query['select'] : ''),
                    '{SEARCH}'        => $search,
                    '{CONDITIONS}'    => ($search && !empty($this->query['conditions']) ? strtr(
                        $conditions, ['{CONDITIONS_PREFIX}' => " AND"]
                    ) : strtr($conditions, ['{CONDITIONS_PREFIX}' => "WHERE"])),
                    '{GROUP}'         => ($this->query['group'] ? "GROUP BY ".$this->query['group']." " : ''),
                    '{ID}'            => $this->query['id'],
                    '{ORDER_SORTING}' => 'ASC',
                    '{ORDER_BY}'      => $orderby,
                    '{LIMIT}'         => ($this->query['true_limit'] === TRUE ? "LIMIT ".$this->query['limit'] : ''), // this limit is different
                ]
            );

            $data = dbquery_tree_full(
                $this->query['table'], $this->query['id'], $this->query['parent'], '', $sql_query
            );

            $index = tree_index($data);

            $data = $this->sortData($data, $index);

            if (!empty($data)) {
                ksort($data);
                $data = array_chunk($data, $this->query['limit']);
                if (!empty($data)) {
                    $chunk_count = 0;
                    // current rows is just 15;
                    $list = [];
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

            // The difference in limitations
            if ($this->query['true_limit'] === TRUE) {

                $limit = "LIMIT ".$this->query['limit'];

            } else {
                $limit = ($this->query['limit'] && isnum($this->query['limit']) ? "LIMIT ".$this->rowStart.", ".$this->query['limit'] : '');
            }

            $sql_query = strtr(
                $sql_statement, [
                    '{TABLE}'         => $sql_table,
                    '{SELECT}'        => ($this->query['select'] ? ", ".$this->query['select'] : ''),
                    '{SEARCH}'        => $search,
                    '{CONDITIONS}'    => ($search && !empty($this->query['conditions']) ? strtr(
                        $conditions, ['{CONDITIONS_PREFIX}' => " AND"]
                    ) : strtr($conditions, ['{CONDITIONS_PREFIX}' => "WHERE"])),
                    '{GROUP}'         => ($this->query['group'] ? "GROUP BY ".$this->query['group']." " : ''),
                    '{ID}'            => $this->query['id'],
                    '{ORDER_SORTING}' => 'ASC',
                    '{ORDER_BY}'      => $orderby,
                    '{LIMIT}'         => $limit,
                ]
            );


            $result = dbquery($sql_query);
            if ($this->rowsCount = dbrows($result)) {
                while ($rows = dbarray($result)) {
                    $data[$this->rowStart][$rows[$this->getIdCol()]] = $rows;

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
    private function sortData($categories, $index, $id = 0, $i = 1) {
        $data = &$data;
        if (!empty($categories[$id])) {
            foreach ($categories[$id] as $parentID => $keyData) {
                $keyData['level'] = get_depth($index, $keyData[$this->query['id']]);
                $keyData[$this->query['title']] = str_repeat(
                        '— ', $keyData['level'] - 1
                    )." ".$keyData[$this->query['title']];
                // 0 first
                $data[$i] = $keyData;
                $i++;
                if (isset($categories[$keyData[$this->query['id']]])) { // has child
                    $refs = self::sortData($categories, $index, $keyData[$this->query['id']], $i);
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
    private function filterDeleteRows() {
        // id
        if (isset($_POST['id']) && is_array($_POST['id'])) {
            $params = [];
            $data = [];
            foreach ($_POST['id'] as $val) {
                $continue = FALSE;

                if (isnum($val)) {
                    if ($this->query['parent']) {
                        // search for id
                        $self = dbquery(
                            "SELECT  ".$this->query['id'].",".$this->query['parent']." FROM ".$this->query['table']." WHERE ".$this->query['id']."=:id",
                            [':id' => $val]
                        );
                        if (dbrows($self)) {
                            $data = dbarray($self);
                            foreach ($data as $key => $value) {
                                $params[':'.$key] = $value;
                            }
                            //append all child to parentless
                            $child_result = dbquery(
                                "SELECT ".$this->query['id'].",".$this->query['parent']." FROM ".$this->query['table']." WHERE ".$this->query['parent']."=:id",
                                [':id' => $val]
                            );
                            if (dbrows($child_result)) {
                                while ($cdata = dbarray($child_result)) {
                                    // id
                                    $cdata[$this->query['parent']] = 0;
                                    if ($data[$this->query['parent']]) { // if I have a parent, change all my child to that parent
                                        $cdata[$this->query['parent']] = $data[$this->query['parent']];
                                    }
                                    dbquery_insert($this->query['table'], $cdata, 'update');
                                }
                            }

                            dbquery_insert($this->query['table'], $data, 'delete');
                            $continue = TRUE;
                        }
                    } else {

                        dbquery("DELETE FROM ".$this->query['table']." WHERE ".$this->getIdCol()."=:id", [':id' => $val]);
                        addNotice('success', $this->table_props['deleted_message']);
                        $continue = TRUE;
                    }
                }

                if ($continue && !empty($params) && !empty($data)) {
                    // delete all images - images is by "," delimiter in a single column
                    if ($this->query['image_field'] && $this->query['image_folder']) {
                        $this->query['image_folder'] = strtr($this->query['image_folder'], $params);

                        if (!empty($data[$this->query['image_field']])) {

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
                            if (dbcount(
                                "(".$table_prop['select'].")", $table_name,
                                $table_prop['group']."='".$data[$this->query['id']]."' $custom_condition"
                            )) {
                                dbquery(
                                    "DELETE FROM ".$table_name." WHERE ".$table_prop['group']."='".$data[$this->query['id']]."' $custom_condition"
                                );
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

            if (\Defender::safe()) {
                addNotice('success', $this->table_props['deleted_message']);
                redirect(FUSION_REQUEST);
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
        if (!empty($this->api->quickEdit())) {
            $array = [];
            foreach ($this->api->quickEdit() as $key => $fields) {
                $fields += $default_options;
                $array[$key] = $fields;
            }
            $this->quick_fields = $array;
        }
    }

    private $custom_columns_value = [];

    private $custom_columns_options = [];

    /**
     * Table individual user customization Menu
     *
     * @return string
     * @throws \ReflectionException
     */
    private function displayCustom() {

        $html = "<div id='".$this->table_props['table_id']."-custom-box' class='list-group-item spacer-sm' style='display:none;'>";
        $html .= "<div class='p-l-15 m-t-20 strong'>Columns</div>";
        // get all the data column.
        $html .= form_checkbox('custom_column[]', '', implode(',', $this->custom_columns_value), [
            'multiple'       => TRUE, 'inline' => TRUE,
            'inline_options' => TRUE,
            'options'        => $this->custom_columns_options
        ]);
        $html .= "<div class='p-l-15 m-b-10 strong'>Pagination</div>";
        $html .= form_text('custom_limit', 'Number of items per page:', $this->query['limit'], [
            'inline'      => TRUE,
            'inner_width' => '80px',
            'class'       => 'form-group-sm m-b-10',
            'deactivate'  => ($this->query['true_limit'] === TRUE ? TRUE : FALSE),
        ]);
        $html .= "<div class='p-l-15 m-b-20'>";
        $html .= form_button('apply_custom', 'Apply', 'apply_custom', ['class' => 'btn-primary']);
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function displayTable() {
        $html = '';
        $header = '';

        // custom column cookie value
        $php_column_cookie = cookie($this->table_props['table_id'].'-cols');

        if ($php_column_cookie) {

            $column_settings = explode(',', $php_column_cookie);

            $this->custom_columns_value = array_combine(array_values($column_settings), array_values($column_settings));

        }

        $cdata = $this->getData();

        $col_span = 1;

        $order_col = $this->table_props['order_col'];

        $key_delete = get($this->table_props['del_key'], FILTER_VALIDATE_INT); // the apply button

        // The bullk actions
        $table_action = post("table_action");
        if ($table_action) {
            $id = sanitizer(['id'], '', 'id[]');
            if ($id) {
                switch ($table_action) {
                    case 'bulk_delete': // delete
                        $this->filterDeleteRows();
                        break;
                    default:
                        $this->bulkActions();
                        break;
                }
            } else {
                addNotice('warning', 'No record has been selected for this table action.');
            }

        }

        // The delete of a row
        if ($key_delete && isnum($key_delete)) {

            $result = dbquery(
                "SELECT * FROM ".$this->query['table']." WHERE ".$this->query['id']."=:id",
                [':id' => intval($key_delete)]
            );

            if (dbrows($result)) {

                $del_data = dbarray($result);

                $params = [];

                foreach ($del_data as $key => $value) {
                    $params[':'.$key] = $value;
                }

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
                        if (dbcount(
                            "(".$table_prop['select'].")", $table_name,
                            $table_prop['group']."='".$del_data[$this->query['id']]."' $custom_condition"
                        )) {
                            dbquery(
                                "DELETE FROM ".$table_name." WHERE ".$table_prop['group']."='".$del_data[$this->query['id']]."' $custom_condition"
                            );
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

                redirect(clean_request('', ['del'], FALSE));
            }
        }

        // The saves of this quick fields.
        if (!empty($this->quick_fields)) {

            $save_quick = post('save_quick_editor');

            if ($save_quick) {
                $post_data[$this->query['id']] = sanitizer($this->query['id'], '', $this->query['id']);
                foreach ($this->quick_fields as $table_col => $table_fields) {
                    $post_data[$table_col] = sanitizer($table_col, '', $table_col);
                }

                if (\Defender::safe()) {
                    dbquery_insert($this->query['table'], $post_data, 'update', ['keep_session' => TRUE]);
                    redirect(FUSION_REQUEST);
                }
            }
        }

        if (is_array($this->table_props['order_col'])) {
            foreach ($this->table_props['order_col'] as $col_key => $col_name) {
                $current_order_col[$col_key] = $col_name;
            }
        } else {
            $current_order_col[$this->table_props['order_col']] = $order_col;
        }

        // TH header row
        foreach ($this->columns as $col_key => $col_data) {

            // get default custom column values
            if ($col_key !== $this->getIdCol()) {

                $this->custom_columns_options[$col_key] = $col_data['title'];

                //print_P($col_data);

                if (
                    $col_data['visibility'] === TRUE && empty($php_column_cookie) || isset($this->custom_columns_value[$col_key])
                ) {
                    $this->custom_columns_value[$col_key] = $col_key;
                }

            }

            // only id column and custom column value can be rendered
            if (isset($this->custom_columns_value[$col_key]) || $col_key == $this->getIdCol()) {

                $order_open = '';
                $order_close = '';
                $order_caret = [
                    'asc'  => 'order-caret-up',
                    'desc' => 'order-caret-down'
                ];

                if (!empty($current_order_col)) {
                    $order_values = array_flip($current_order_col);
                }

                if (!empty($current_order_col[$col_key])) { // turn on carets

                    $order = 'asc';

                    $order_class = $order_caret['asc'];

                    $order_value = $current_order_col[$col_key]; // title

                    $order = get("order") ? get('order') : $order; // asc or desc

                    $order_by = get("orderby");

                    $order_val = 'desc';

                    if ($order && $order_by) {

                        if (!empty($order_values[$order_by]) && $order_values[$order_by] == $col_key) {

                            $order_class = 'order-caret-up-current';

                            if ($order == 'desc') {

                                $order_class = 'order-caret-down-current';

                                $order_val = 'asc';
                            }
                        }
                    }

                    $order_open = "<a class='table-order $order_class' href='".clean_request("orderby=".$order_value."&order=".$order_val, ['order', 'orderby'], FALSE)."'>\n";

                    $order_close = "</a>\n";
                }

                $header .= "<th ".($col_data['title_class'] ? "class='".$col_data['title_class']."'" : "").">".$order_open.$col_data['title'].$order_close."</th>\n";

                $col_span++;
            }

        }

        // End Header TH

        /**
         * Link filters
         */
        $link_filters = [];
        $link_filter_keys = ['s'];

        // Lang Filters
        if (!empty($this->table_props['multilang_col'])) { // this one do as tab on top.
            $enabled_languages = fusion_get_enabled_languages();
            if (count($enabled_languages) > 0) {
                $_filter[] = '';
                foreach ($enabled_languages as $lang) {
                    $lang_val = strtolower($lang);
                    $active_class = (isset($_GET['language']) && $_GET['language'] == $lang_val) ? " class='strong'" : '';
                    $_filter[] = "<a href='".clean_request('language='.$lang_val, ['language'], FALSE)."'$active_class><small>$lang</small></a>\n | ";
                }
                $link_filters['language'] = " | <small>Language:</small> ".implode(' ', $_filter);
                array_push($link_filter_keys, 'language');
            }
        }

        // Calculate Link Filters
        if (!empty($this->table_props['link_filters'])) {
            foreach ($this->table_props['link_filters'] as $key => $filters) {
                $_filter = [];
                //print_p($filters);
                foreach ($filters['options'] as $filter) {
                    //print_p($filters);
                    $filter_val = strtolower($filter);
                    $active_class = (get($key) == $filter_val) ? " class='strong'" : '';
                    $_filter[] .= "<a href='".clean_request("$key=$filter_val", [$key], FALSE)."'$active_class><small>$filter</small></a>\n";
                }
                $link_filters[$key] = "<small>".$filters['title'].":</small> ".implode(' | ', $_filter);
                array_push($link_filter_keys, $key);
            }
        }

        // Calculate more $link_filter_keys from dropdown redirect
        if (!empty($this->table_props['dropdown_filters'])) {
            foreach ($this->table_props['dropdown_filters'] as $key => $filter) {
                if ($filter['type'] == 'date') {
                    array_push($link_filter_keys, $key.'-date_month');
                    array_push($link_filter_keys, $key.'-date_year');
                } else {
                    array_push($link_filter_keys, $key);
                }
            }

        }

        if (!empty($link_filters) || !empty($this->table_props['search_col'])) {

            // Render link filters
            $html .= "<div class='row'>\n";
            if (!empty($link_filters)) {
                $html .= "<div class='col-xs-12 col-sm-".($this->table_props['search_col'] ? 6 : 12)."'>\n";
                $active_class = (!in_array($_GET, $link_filter_keys)) ? " class='strong'" : '';
                $html .= "<div class='display-inline-block'><a href='".clean_request('', $link_filter_keys, FALSE)."'$active_class><small>All</small></a> ".implode('', $link_filters)."</div>\n";
                $html .= "</div>\n";
            }

            if (!empty($this->table_props['search_col'])) {
                $html .= "<div class='col-xs-12 col-sm-".($this->table_props['multilang_col'] || !empty($link_filters) ? 6 : 12)." text-right'>\n";
                $html .= "<div class='pull-right'>\n";
                $html .= openform('search_frm', 'post');
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
                $html .= "</div>\n"; // close pull-right
                $html .= "</div>\n"; // close text right
            }

            $html .= "</div>\n"; // close row
        }

        $start_row = '';
        $end_row = '';

        if ($this->table_props['show_count'] === TRUE || $this->quick_fields || !empty($this->table_props['date_col'])) {
            $start_row = "<div class='clearfix m-t-5 m-b-5' style='width:100%;'>\n";
            $end_row = "</div>\n"; // close clearfix
        }

        $html .= $start_row;

        // Show customization toggle
        $html .= "<small class='pull-right m-t-5 m-l-5'><a data-target='custom-box' class='pointer custom-table' data-table='".$this->table_props['table_id']."'>Customise Options <span class='caret'></span></a></small>";

        // Show row counter
        if ($this->table_props['show_count'] === TRUE) {
            $html .= "<small class='pull-right m-t-5'>".format_word($this->maxRows, "item|items", ['html' => FALSE, 'format_num' => TRUE])."</small>\n";
        }

        if ($this->table_props['delete_link'] === TRUE || $this->table_props['edit_link'] === TRUE || !empty($this->table_props['action_filters'])) {
            $html .= "<div class='display-inline-block m-r-15'>\n";

            $actions_options = [
                ''            => 'Bulk Actions',
                'bulk_delete' => 'Delete'
            ];

            if (!empty($this->table_props['action_filters'])) {
                array_push($actions_options, $this->table_props['action_filters']);
            }

            // print_P($this->table_props['action_filters']);
            // print_P($actions_options);

            $html .= openform('cat_table_frm', 'post', FUSION_REQUEST).form_select(
                    'filter_actions', '', '', [
                        'inline'           => TRUE,
                        'inner_width'      => '150px',
                        'class'            => 'm-0 display-inline-block pull-left',
                        'optgroup'         => TRUE,
                        'options'          => $actions_options,
                        'select2_disabled' => TRUE,
                        'stacked'          => form_button(
                                'apply_filter', 'Apply', 'apply_filter', ['class' => 'btn-sm btn-default']
                            ).form_hidden(
                                'filter_ids', '', ''
                            )
                    ]
                ).closeform();
            $html .= "</div>\n";
        }


        if (!empty($this->table_props['dropdown_filters'])) {
            $html .= "<div class='display-inline-block'>".openform('date_frm', 'post', FUSION_REQUEST);

            if (!empty($this->table_props['dropdown_filters'])) {
                foreach ($this->table_props['dropdown_filters'] as $column_name => $filter_prop) {
                    switch ($filter_prop['type']) {
                        default:
                        case 'array':
                            $html .= $this->columnFilter($column_name, $filter_prop);
                            break;
                        case 'date':
                            $html .= $this->dateFilter($column_name, $filter_prop);
                            break;
                    }
                }
            }

            $html .= form_button(
                'table_filter', 'Apply', 'table_filter', ['class' => 'btn-sm m-r-10']
            );

            $html .= closeform()."</div>";
        }

        $has_search = get('s');
        if ($has_search) {
            $html .= "<a class='btn btn-link btn-sm' href='".clean_request('', ['s'], FALSE)."'>Clear Search</a>";
        }

        $html .= $end_row;

        $check_all_col = '';
        $check_all_col_2 = '';

        if ($this->table_props['edit_link'] or $this->table_props['delete_link']) {
            $check_all_col = "<th class='min'>".form_checkbox('chk_all', '', '', ['class' => 'm-b-0'])."</th>\n";
            $check_all_col_2 = "<th>".form_checkbox('chk_all2', '', '', ['class' => 'm-b-0'])."</th>\n";
        }

        $validate_messages = [
            'bulk_delete' => 'Are you sure to delete all these records?'
        ];
        if (!empty($this->table_props['actions_filters_confirm'])) {
            if (is_array($this->table_props['actions_filters_confirm'])) {
                $validate_messages = $validate_messages + $this->table_props['actions_filters_confirm'];
            }
        }
        $validate_opts = json_encode($validate_messages);

        $html .= openform('table_frm', 'post');
        $html .= form_hidden('table_action', '', '');

        // Show custom table properties
        $html .= $this->displayCustom();

        $html .= "<table class='fusion-table ".($this->table_props['table_class'] ? ' '.$this->table_props['table_class'] : 'table')."'>\n";
        // head
        $html .= "<thead>\n";
        $html .= "<tr>\n$check_all_col\n".$header."\n</tr>\n";
        $html .= "</thead>\n";

        $html .= "<tbody>\n";
        // end of head
        if (!empty($cdata)) {

            foreach ($cdata[$this->rowStart] as $data) {

                $id = $data[$this->getIdCol()];
                // the edit link
                // key need to be dynamic as the del and edit may be present.
                $get_rid = [$this->table_props['edit_key'], $this->table_props['del_key'], 's'];
                // All the info available should be replaced.
                //print_p(array_values($this->columns));

                // Search Strings
                $search_str = [];
                foreach ($data as $key => $value) {
                    $search_str[":".$key] = $value;
                }

                $edit_uri = $this->table_props['edit_link_format'] ? strtr(
                    urldecode($this->table_props['edit_link_format'].$id), $search_str
                ) : clean_request($this->table_props['edit_key'].'='.$id, $get_rid, FALSE);

                $del_uri = $this->table_props['delete_link_format'] ? strtr(
                    urldecode($this->table_props['delete_link_format'].$id), $search_str
                ) : clean_request($this->table_props['del_key'].'='.$id, $get_rid, FALSE);

                $view_uri = $this->table_props['view_link_format'] ? strtr(
                    urldecode($this->table_props['view_link_format'].$id), $search_str
                ) : clean_request($this->table_props['view_key'].'='.$id, $get_rid, FALSE);

                // need to use table props instead.. 'delete_link' => true, or 'edit_link' => true,
                $checkbox_col = '';
                if ($this->table_props['edit_link'] or $this->table_props['delete_link']) {
                    $checkbox_col = "<th class='check-col'>\n".form_checkbox("id[]", '', '', ['value' => $id, 'class' => 'm-b-10'])."</th>\n";
                }

                $html .= "<tr id='entry-row-".$id."'>\n$checkbox_col\n";

                foreach ($this->columns as $col_key => $col_data) { // column configurations

                    if (isset($this->custom_columns_value[$col_key]) || $col_key == $this->getIdCol()) {

                        $edit_open = '';
                        $edit_close = '';
                        $edit_actions = '';
                        $value_class = [];
                        $data_replace = [];

                        if (!empty($data)) {
                            foreach (array_keys($data) as $keyname) {
                                $data_replace[':'.$keyname] = $data[$keyname];
                            }
                        }

                        // Column value
                        $value = (!empty($data[$col_key]) ? $data[$col_key] : $col_data['empty_value']);
                        // Strip all bbcode tags
                        if (!empty($value)) {
                            if (!empty($col_data['parse_value'])) {
                                $value = nl2br(parse_textarea($value));
                            }
                            $value = strip_bbcodes($value);
                            $value = strip_tags($value);
                        }

                        $value_class[] = ($col_data['value_class'] ?: '');

                        if ($col_data['edit_link'] || $col_data['delete_link'] || $col_data['view_link']) {

                            $value_class[] = "has-actions clearfix";
                            /*
                             * (Optional - sets the current column with the edit link, with quick edit fields)
                             * array("edit_link" => TRUE)
                             */
                            $edit_open = "<a href='$edit_uri' class='strong text-bigger'>\n";

                            $edit_close = "</a>\n";

                            $edit_actions = "<div class='tbl_actions'>\n";

                            $edit_actions .= "<span class='text-lighter'>ID: $id</span>\n";

                            $edit_actions .= ($col_data['edit_link'] ? "<span class='edit'><a href='$edit_uri'>Edit</a></span>\n" : '');

                            $edit_actions .= ((!empty($this->quick_fields)) ? "<span class='qedit'><a class='quick_edit pointer' data-toggle='quick_edit' data-value='".$id."'>Quick Edit</a></span>\n" : '');

                            $edit_actions .= ($col_data['delete_link'] ? "<span class='del'><a onclick='return confirm(\"Are you sure you want to delete this entry? All associated files and images will be permanently removed from the server.\");' href='$del_uri' class='text-danger'>Delete</a></span>\n" : '');

                            $edit_actions .= ($col_data['view_link'] ? "<span class='view'><a href='$view_uri'>View</a></span>\n" : '');

                            $edit_actions .= "</div>\n";

                            $edit_actions .= "<button type='button' class='toggle-row'></button>";

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

                            } else if (!empty($col_data['image_folder'])) {
                                $image_folder = strtr($col_data['image_folder'], $data_replace);
                            }

                            $default_image_path = ($col_data['default_image'] ?: IMAGES.'imagenotfound.jpg');

                            $value = "<img class='$class' style='$width' src='$default_image_path'/>\n";;

                            if (is_file($image_folder.$data[$col_key])) {
                                $value = "<img class='$class' style='$width' src='".$image_folder.$data[$col_key]."'/>\n";
                            }

                        }

                        else if ($col_data['multilang']) {

                            $value = UserFieldsQuantum::parse_label($data[$col_key]);

                        }

                        else if ($col_data['icon'] && !empty($data[$col_key])) {
                            /*
                             * array("icon" => TRUE)
                             */
                            $value = "<i class='".$data[$col_key]."'/></i>\n";

                        }

                        else if ($col_data['number']) {
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

                                $value = number_format(
                                    $value ?: '0',
                                    (!empty($col_data['delimiter']) ? $col_data['delimiter'] : 0),
                                    (!empty($col_data['decimal_point']) ? $col_data['decimal_point'] : '.'),
                                    (!empty($col_data['thousand_sep']) ? $col_data['thousand_sep'] : ',')
                                );

                                if (empty($value)) {
                                    $value = (!empty($col_data['delimiter']) ? "0".(!empty($col_data['thousand_sep']) ? $col_data['thousand_sep'] : ',').str_repeat(
                                            $col_data['delimiter'], "0"
                                        ) : "0");
                                    //print_P($col_key.' '.$value);
                                }

                            }

                            //print_P($value);

                        } else if ($col_data['user'] === TRUE) {
                            /*
                             * array("user" => TRUE);
                             */
                            $user = fusion_get_user($value);
                            $avatar = '';
                            if ($col_data['user_avatar'] === TRUE) {
                                $avatar = display_avatar($user, '32px', 'm-r-10');
                            }
                            $value = $avatar.profile_link($user['user_id'], $user['user_name'], $user['user_status']);

                        } else if ($col_data['options']) {
                            /*
                             * array("options" => array(1=>"Yes", 0=>"No"))
                             */
                            $value = $value ?: 0;
                            $value = $col_data['options'][$value];

                        } else if ($col_data['count']) {
                            /*
                             * array("count" => array("field"=>"column_name", "table" => DB_PREFIX, "conditions" => "column_name=matches"))
                             */
                            if (!empty($col_data['count']['field']) && !empty($col_data['count']['table']) && !empty($col_data['count']['conditions'])) {
                                $data_replace = [];
                                foreach (array_keys($data) as $keyname) {
                                    $data_replace[':'.$keyname] = $data[$keyname];
                                }
                                $col_data['count']['conditions'] = strtr($col_data['count']['conditions'], $data_replace);
                                $value = dbcount(
                                    "(".$col_data['count']['field'].")", $col_data['count']['table'],
                                    $col_data['count']['conditions']
                                );
                            }

                        } else if ($col_data['display'] && $col_data['display']['table'] && $col_data['display']['key'] && $col_data['display']['title']) {

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
                                    if ($col_data['debug'] === TRUE) {
                                        print_p($data_replace);
                                        print_p($col_data['display']['conditions']);
                                    }
                                    $col_data['display']['conditions'] = " WHERE ".strtr(
                                            $col_data['display']['conditions'], $data_replace
                                        );
                                } else {
                                    $col_data['display']['conditions'] = $default_condition;
                                }

                                if (empty($col_data['display']['order'])) {
                                    $col_data['display']['order'] = $default_order;
                                }

                                $formatted_value = [];
                                $sub_query = "SELECT ".$col_data['display']['key'].", ".$col_data['display']['title'].$col_data['display']['select']." FROM ".$col_data['display']['table']." ".$col_data['display']['conditions']." ".$col_data['display']['order']." ".(!empty($col_data['display']['limit']) ? $col_data['display']['limit'] : '');
                                if ($col_data['debug'] === TRUE) {
                                    print_p($sub_query);
                                    print_p($data_replace);
                                }

                                $sub_query = dbquery($sub_query);

                                if (dbrows($sub_query)) {

                                    while ($sub_data = dbarray($sub_query)) {

                                        $search_str = [
                                            ':key'   => $sub_data[$col_data['display']['key']],
                                            ':title' => $sub_data[$col_data['display']['title']]
                                        ];

                                        //print_p($sub_data);
                                        foreach ($data as $key => $value) {
                                            $search_str[":".$key] = $value;
                                        }

                                        // we need sub_data to support all previous columns
                                        $sub_data = array_merge($sub_data, $data);

                                        if ($col_data['debug'] === TRUE) {
                                            print_p($search_str);
                                            print_P($sub_data);
                                            print_p($col_data['display']['format_result']);
                                        }
                                        $formatted_value[] = (!empty($col_data['display']['format_result']) ? strtr(
                                            urldecode($col_data['display']['format_result']), $search_str
                                        ) : $sub_data[$col_data['display']['title']]);
                                    }

                                    $formatted_value = array_filter($formatted_value);

                                    if ($col_data['debug'] === TRUE) {
                                        print_p($formatted_value);
                                    }
                                } else {
                                    // No result
                                    $search_str = [];
                                    foreach ($data as $key => $value) {
                                        $search_str[":".$key] = $value;
                                    }
                                    $formatted_value = !empty($col_data['display']['no_result']) ? strtr(
                                        urldecode($col_data['display']['no_result']), $search_str
                                    ) : "";
                                }

                                if (!empty($formatted_value)) {
                                    if (is_array($formatted_value)) {
                                        $value = implode(', ', $formatted_value);
                                    } else {
                                        $value = $formatted_value;
                                    }
                                }
                            }

                        }

                        else if (!empty($col_data['date']) && !empty($value)) {

                            // only accepts timestamp
                            if (isnum($value)) {

                                //Format : array("date" => TRUE, "date_format" => "shortdate")

                                if (!empty($col_data['date_format'])) {

                                    $value = showdate($col_data['date_format'], $value);

                                } else {

                                    // if less than a day,
                                    if (TIME > $value && ((TIME - $value) <= 86400)) {
                                        $value = timer($value);
                                    } else {
                                        $value = showdate('shortdate', $value);
                                    }

                                }
                            }


                        }

                        else if (!empty($col_data['array']) && is_array($col_data['array'])) {
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
                                if (isset($col_data['callback'][2])) {
                                    require_once $col_data['callback'][2];
                                    unset($col_data['callback'][2]);
                                }
                                //print_p(get_called_class());
                                $object = new \ReflectionClass($col_data['callback'][0]);
                                $class = $object->newInstance();
                                $method = [
                                    $class,
                                    $col_data['callback'][1]
                                ];
                                if (is_callable($method)) {
                                    $value = call_user_func($method, $data_replace);
                                } else if ($col_data['debug']) {
                                    addNotice("danger", "Callback could not be made");
                                }
                            } else {
                                //print_p(get_defined_functions());
                                // If your file has a namespace, please add them in as prefix
                                // some_namespace/callback_function_name
                                if (is_callable($col_data['callback'])) {
                                    $value = $col_data['callback']($data_replace);
                                } else if ($col_data['debug']) {
                                    addNotice("danger", "Callback could not be made");
                                }
                            }
                        }

                        $html .= "<td ".($value_class ? "class='".implode(
                                    " ", $value_class
                                )."'" : '')." data-col='$col_key' data-colname='".$col_data['title']."' data-col-options='".\json_encode($col_data['options'])."'>".$edit_open."<span class='value'>".($value ?: '-')."</span>".$edit_close.$edit_actions."</td>\n";

                    }

                }
                $html .= "</tr>\n";

                // Quickfields
                if (!empty($this->quick_fields)) {
                    foreach ($this->quick_fields as $table_col => $table_fields) {
                        if (isset($data[$table_col])) {
                            $this->jsonRows[$table_col] = $data[$table_col]; // this is old data, and js will not replace this.
                        }
                    }

                    $html .= "<tr></tr>\n<tr id='form-row-".$id."' class='inline-edit-column' style='display:none'>\n";
                    $html .= "<td colspan='".($col_span + 1)."'>\n";
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

        if (!empty($this->quick_fields) && !empty($cdata)) {

            $html .= "<div id='qc-input' class='qc-input' style='display: none;'>\n";
            foreach ($this->quick_fields as $table_col => $table_fields) {
                $html .= "<div class='clearfix'><span class='tbl-qc-title'>".$table_fields['title']."</span>\n";
                $html .= $table_fields['function']($table_col, isset($table_fields['label']) ? $table_fields['label'] : '','', $table_fields); // you won't be able to use quick fields if you turn off jquery.
                $html .= "</div>\n";
            }
            $html .= form_hidden('qc_id', '');
            $html .= form_button('save_quick_editor', 'Update <span class="qc-spinner fa fa-clock-o fa-spin m-l-5" style="display:none;"></span>', 'save_quick_editor', ['class' => 'btn-primary pull-right']);
            $html .= form_button('cancel_quick_editor', 'Cancel', 'cancel');
            $html .= "</div>\n";
        }

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
            $html .= makepagenav($this->rowStart, $this->query['limit'], $this->maxRows, 3, clean_request('', [$this->default_args['rowstart']], FALSE).'&amp;');
            $html .= "</div>\n";
        }
        $html .= "</div>\n";

        add_to_footer("<script src='".INCLUDES."jquery/tables/tables.js'></script>");

        $table_js = "phpfusion_tables.init(
        '".json_encode($this->jsonRows)."', 
        '".$this->query['id']."', 
        '".$this->query['parent']."', 
        '".$this->query['table']."', 
        $validate_opts, 
        '".$this->table_props['ordering_col']."', 
        '".$this->table_props['multilang_col']."'
        );";

        add_to_jquery("$table_js");

        return (string)$html;

    }

    /**
     * @param $column_name
     * @param $filter_options
     *
     * @return string
     * @throws \ReflectionException
     */
    private function dateFilter($column_name, $filter_options) {

        $sql_conditions = '';

        if ($this->table_props['multilang_col'] || $this->query['conditions']) {

            $sql_conditions = "WHERE ";

            if ($this->table_props['multilang_col']) {
                $sql_conditions .= $this->table_props['multilang_col']."='".LANGUAGE."' AND ";
            }

            $sql_conditions .= $this->query['conditions'];
        }

        $sql_statement = /** @lang mysql */
            "SELECT YEAR(FROM_UNIXTIME({DATE_COLUMN})) 'table_year', MONTH(FROM_UNIXTIME({DATE_COLUMN})) 'table_month' FROM {TABLE} {CONDITIONS} GROUP BY table_year, table_month ORDER BY {DATE_COLUMN} DESC";
        $date_sql = strtr(
            $sql_statement, [
                '{DATE_COLUMN}' => $column_name,
                '{TABLE}'       => $this->query['table'],
                '{CONDITIONS}'  => $sql_conditions
            ]
        );

        $date_result = dbquery($date_sql);
        $date_rows = dbrows($date_result);
        if ($date_rows) {
            $options = [
                'na' => 'Select '.$filter_options['title']
            ];

            while ($date_data = dbarray($date_result)) {
                $month_locale = explode('|', fusion_get_locale('months'));
                $options[$date_data['table_year']."_".$date_data['table_month']] = $month_locale[$date_data['table_month']]." ".$date_data['table_year'];
            }

            $date_active = isset($_GET[$column_name.'-date_year']) && isset($_GET[$column_name.'-date_month']) ? $_GET[$column_name.'-date_year']."_".$_GET[$column_name.'-date_month'] : '';

            $html = "<div class='display-inline-block'>\n";
            $html .= form_select(
                $column_name, '', $date_active, [
                    //      'allowclear'       => FALSE,
                    'inner_width'      => '130px',
                    'class'            => 'm-0',
                    'options'          => $options,
                    //    'placeholder'      => 'All Dates',
                    'select2_disabled' => TRUE,
                ]
            );
            $html .= "</div>\n"; // close inline

            return $html;
        }
    }

    private function columnFilter($column_name, $filter) {

        $options = [
            'na' => $filter['title']
        ];

        $options += $filter['options'];

        $filter_val = array_flip(array_map('strtolower', $options));

        $value = '';

        if (isset($_GET[$column_name])) {
            $value = isset($filter_val[$_GET[$column_name]]) ? $filter_val[$_GET[$column_name]] : "";
        }

        // Output
        $html = "<div class='display-inline-block'>\n";
        $html .= form_select(
            $column_name, '', $value, [
                'inner_width'      => '160px',
                'class'            => 'm-0 display-inline-block pull-left',
                'options'          => $options,
                'select2_disabled' => TRUE,
                //        'allowclear'       => FALSE,
                //   'placeholder'      => 'All Dates',
            ]
        );
        $html .= "</div>\n"; // close inline

        return (string)$html;
    }

    private function bulkActions() {
        if (method_exists($this->api, 'bulkActions')) {
            $this->api->bulkActions();
        }
    }

}

require_once INCLUDES.'bbcode_include.php';
