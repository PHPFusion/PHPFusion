<?php

function fusiontable_append_button($table_id, $html, $class = '.slot-2') {

    static $i;

    $script = "let {$table_id}_{$i}DOM = $('#{$table_id}_wrapper').find('." . ltrim($class, '.') . "');
    if ({$table_id}_{$i}DOM.length) {
        {$table_id}_{$i}DOM.append(" . json_encode($html) . ");
    }";

    add_to_jquery($script);
    ?>
    <?php

    $i++;
}

/**
 * @param $table_id     - table id
 * @param $filter_id    - the select id
 * @param $column_index - the column index number (0 based)
 */
function fusiontable_column_filter_script($table_id, $filter_id, $column_index) {

    static $i;
    $i = $i + 1;

    return "
       var table{$i}DOM = $('#{$table_id}').DataTable(); 
         $('#{$filter_id}').on('change', function () {
            var val = $(this).val();
                          
            if (val) {                                        
               table{$i}DOM.column({$column_index}).search('^'+val+'$', true, false).draw();
            } else {                         
                table{$i}DOM.column({$column_index}).search('').draw();
            }
        });
    ";
}

function fusion_table_toggle_td($column_name) {

    $script = "$(document).on('mouseenter', 'td.{$column_name}', function () {
        $(this).find('.action-bar').show();
    }).on('mouseleave', 'td.{$column_name}', function () {
        $(this).find('.action-bar').hide();
    });";
    add_to_jquery($script);
}

/**
 * Initiliazes Datatables
 *
 * @param string $table_id
 * @param array  $options
 *
 * Options for columns parameters (Example)
 *                          $options["columns"] = array(
 *                          array("data" => "column_1_name", "orderable"=>FALSE, "width"=>200, "class"=>"min"),
 *                          array("data" => "column_1_name")
 *                          )
 *
 *                          'orderable' - boolean (true/false)
 *                          'width' - width of column
 *                          'class' - class name,
 *                          'responsive' - boolean (true/false)
 *                          'className' -   'never' // hide on all devices
 *                                      -   'all' //show on all devices
 *                                      -   'not-mobile' // hide on mobile
 *
 * The response for the item must contains such:
 *  [
 *       "data" => array( 0 => array("column_1" => "data", "column_2" => "data"...), 1 => ... ),
 *       "recordsTotal" => $rows,
 *       "recordsFiltered" => $max_rows,
 *       "responsive" => TRUE
 *  ]
 *
 * Row Sorter
 * $options['columns'] must be defined. data must be as string?
 * $options['remote_file'] must be on string file path
 *
 * editor is - 'editor'
 *
 * @todo-meangczac https://www.mobilespoon.net/2019/11/design-ui-tables-20-rules-guide.html
 *                          Column Sort ON  - done
 *                          Column Resize ON    -done
 *                          Column Reorder ON   - done
 *
 * @return string
 */
function fusiontable($table_id, array $options = []) {

    $locale = fusion_get_locale();

    $table_id = str_replace([ "-", " " ], "_", $table_id);

    $js_event_function = "";

    $filters = "";

    $js_filter_function = "";

    $default_options = [
        'remote_file'         => '',
        'page_length'         => 0, // result length 0 for default 10
        'debug'               => FALSE,
        'reponse_debug'       => FALSE,
        // Documentation required for these.
        'retrieve'            => TRUE,
        'server_side'         => '',
        'processing'          => '',
        'ajax'                => FALSE,
        'ajax_debug'          => FALSE,
        'inner_class'         => '',
        'class'               => '',
        // filter input name on the page if extra filters are used
        'ajax_filters'        => [],
        // not functional yet
        'ajax_data'           => [],
        'order'               => [], // [0, 'desc'] // column 0 order desc - sets default ordering
        'state_save'          => FALSE, // utilizes localStorage to store latest state
        // documentation needed for columns
        'columns'             => NULL,
        'ordering'            => TRUE,
        'pagination'          => TRUE, //hides table navigation
        'hide_search_input'   => FALSE, // hides search input
        // Ui as aesthetics for maximum user experience
        'row_reorder'         => FALSE,
        'row_reorder_url'     => '',
        'row_reorder_success' => '',
        'row_reorder_failed'  => '',
        'col_resize'          => FALSE,
        'col_reorder'         => FALSE,
        'fixed_header'        => FALSE,
        'responsive'          => TRUE,
        'toolbar'             => FALSE,
        'buttons'             => FALSE,
        'unsortable'          => [],
        // locale
        'label_zero'          => $locale['zero_locale'],
        // custom jsscript append
        'js_script'           => '',
    ];

    $options += $default_options;

    // Map for file inclusion
    $plugin_registers = [
        'BOOTSTRAP5' => [
            'css' => [
                INCLUDES . 'jscripts/datatables2/bs5/datatables.min.css',
            ],
            'js'  => [
                INCLUDES . 'jscripts/datatables2/bs5/datatables.min.js',
            ]
        ],
        'BOOTSTRAP4' => [
            'css' => [
                INCLUDES . 'jscripts/datatables2/css/datatables.bs4.min.css'
            ],
            'js'  => [
                INCLUDES . 'jscripts/datatables2/js/datatables.bs4.min.js',
            ]
        ],
        'BOOTSTRAP'  => [
            'css' => [
                INCLUDES . 'jscripts/datatables2/css/datatables.bs3.min.css',
            ],
            'js'  => [
                INCLUDES . 'jscripts/datatables2/js/datatables.bs3.min.js',
            ]
        ],
        'default'    => [
            'css' => [
                INCLUDES . 'jscripts/datatables2/css/datatables.min.css',
            ],
            'js'  => [
                INCLUDES . 'jscripts/datatables2/js/datatables.min.js',
            ]
        ]
    ];

    $config = '';
    if ( $options['page_length'] && isnum($options['page_length']) ) {
        //        $options['datatable_config']['pageLength'] = (int)$options['page_length'];
        $config .= "'pageLength' : {$options['page_length']},";
    }
    // Build configurations

    if ( ! empty($options["order"]) ) {
        $config .= "'order' : [ " . json_encode($options["order"]) . " ],";
    }
    if ( $options['hide_search_input'] === TRUE ) {
        $config .= "'dom': '<\"top\">rt<\"bottom\"><\"clear\">',";
    }

    if ( $options['row_reorder'] === TRUE ) {
        fusion_load_script(INCLUDES . 'jquery/jquery-ui/jquery-ui.min.js');
        fusion_load_script(INCLUDES . 'jquery/jquery-ui/jquery-ui.css', 'css');
        //        $options['pagination'] = FALSE;

        $config .= "
        'info':false,
        'aaSorting': [[1, 'asc']],
        ";

        $options['js_script'] .= "
            let fixHelper = function(e, ui) {
                ui.children().each(function() { $(this).width($(this).width()); });
                return ui;
            };
    
            $('#{$table_id} tbody').sortable({
                helper: fixHelper,
                placeholder: 'state-highlight',
                scroll: true,
                axis: 'y',
                start: function(e, ui) {
                    // store subtree rows
                    var rowId = ui.item.data('id');
                    var subtree = $('#{$table_id} tbody tr').filter(function(){
                        return $(this).data('parent') == rowId;
                    });
                    ui.item.data('subtree', subtree);
                },
                stop: function(e, ui) {
                    var movedItem = ui.item;
                    var subtree = movedItem.data('subtree');
                    
                    // move subtree immediately after parent
                    if(subtree && subtree.length){
                        subtree.insertAfter(movedItem);
                    }
        
                    // build order array with parent-child awareness
                    var orderData = [];
                    $('#{$table_id} tbody tr').each(function(index){
                        orderData.push({
                            id: $(this).data('id'),
                            parent: $(this).data('parent'),
                            order: index + 1
                        });
                        $(this).find('.num').text(index + 1);
                    });
        
                    // send AJAX request
                    let formData = new FormData();
                    formData.append('fusion_token', '" . fusion_get_token($table_id . "_token", 10) . "');
                    formData.append('form_id', '" . $table_id . "_token');
                    formData.append('order', JSON.stringify(orderData));
        
                    fetch('" . $options['row_reorder_url'] . "', {
                        method: 'POST',
                        body: formData
                    }).then(resp => {
                        if(resp.ok){ add_notice('success', '" . $options['row_reorder_success'] . "'); }
                    }).catch(err => { add_notice('danger', '" . $options['row_reorder_failed'] . "'); });
                }
            }).disableSelection();";
        /*
         *  //headers: {
                        //'Content-Type': 'application/json',
                    //},
         */
        //        add_to_jquery("
        //
        //        // Sorting
        //        $('#test tbody > tr').sortable({
        //            update: function (e, ui) {
        //
        //
        //            }
        //        });
        //");
        ////alert(locale.error_preview + '\n' + locale.error_preview_text);
    }
    if ( $options['pagination'] === FALSE ) {
        $config .= "'paging' : false,";
    }

    if ( $options['buttons'] || $options['toolbar'] ) {

        $button_dom = $options['buttons'] ? ' B ' : ' ';
        $config .= "        
        'dom' : '<\'d-flex align-items-center justify-content-between py-3 mb-4 {$options['inner_class']}\'<\'d-flex gap-3\' f{$button_dom}<\'slot-1 ms-3 d-flex gap-3\'>><\'d-flex ms-auto\' l <\'slot-2 ms-3 d-flex gap-3\'>> >rt<\'dt-info py-3 bottom-pagination d-flex justify-content-between {$options['inner_class']}\' ip>',
        ";

        if ( $options['buttons'] ) {
            $config .= "
        buttons: [
        { extend: 'copy', className: 'btn' },
        { extend: 'excel', className: 'btn', title: '" . ( $options['table_title'] ?? 'Excel' ) . "', exportOptions: { columns: ':visible' } },
        { extend: 'pdf', className: 'btn', title: '" . ( $options['table_title'] ?? 'PDF' ) . "', exportOptions: { columns: ':visible' } },
        { extend: 'colvis', className: 'btn', text: 'Columns' }
        ],        
        ";
        }
    }


    //'infoEmpty': '" . $locale['empty_locale'] . "',
    $config .= "
    'language': {
        'processing': '" . $locale['processing_locale'] . "',
        'lengthMenu': '" . $locale['menu_locale'] . "',
        'zeroRecords': '" . $options['label_zero'] . "',
        'info': '" . $locale['result_locale'] . "',
        'infoFiltered': '" . $locale['filter_locale'] . "',
        'searchPlaceholder': '" . $locale['search_input_locale'] . "',
        'search': '" . $locale['search'] . "',
        'paginate': {
            'next': '" . $locale['next'] . "',
            'previous': '" . $locale['previous'] . "',
        },
    },";

    if ( $options['responsive'] ) {
        $add_responsive = "responsive: {
            details: {
                type: 'column', // can also be 'inline' or 'modal'
                target: 'tr'
            }
        },";
    }

    if ( ! empty($options['columns']) && empty($options['remote_file']) ) {
        $config .= "'columns': " . json_encode($options['columns'], JSON_UNESCAPED_SLASHES) . ",";
    }
    // Javascript Init
    $js_config_script = "
    {        
        " . ( $add_responsive ?? '' ) . "
        'searching' : true,
        'lengthMenu': [ [10, 25, 50, 100], [10, 25, 50, 100] ],
        'ordering' : " . ( $options["ordering"] ? "true" : "false" ) . ",
        'stateSave' : " . ( $options["state_save"] ? "true" : "false" ) . ",
        'autoWidth' : true,         
        $config
    }";

    // .table-tr-hover
    $options['js_script'] .= "     
    $('#{$table_id}').on('mouseenter', 'tr', function () {
        $(this).find('div[data-toggle=\"td-hover\"]').show();
    }).on('mouseleave', 'tr', function () {
        $(this).find('div[data-toggle=\"td-hover\"]').hide();
    });";

    // Ajax handling script
    if ( $options['remote_file'] ) {
        // Column is automated when it's ajax, it's going to read the json keys
        // Automate column data if not present
        if ( empty($options["columns"]) && preg_match("@^http(s)?://@i", $options["remote_file"]) ) {
            $file_output = fusion_get_contents($options['remote_file']);
            if ( ! empty($file_output) ) {
                if ( is_json($file_output) ) {
                    $output_array = json_decode($file_output, TRUE);
                    //print_P($output_array);
                    if ( $options['reponse_debug'] ) {
                        print_p($output_array);
                    }
                    // Column
                    if ( ! empty($output_array['data']) ) {
                        $output_data = $output_array["data"];
                        $output_reset = reset($output_data);
                        if ( is_array($output_reset) ) {
                            $column_key = array_keys($output_reset);
                        }
                        if ( ! empty($column_key) ) {
                            foreach ( $column_key as $column ) {
                                $options["columns"][] = [ 'data' => $column ];
                            }
                        }
                    }
                }
            }
            else {
                addnotice("danger", "Table columns could not be loaded automatically.");
            }
        }

        $js_config_script = "
        {
            'retrieve' : " . ( $options['retrieve'] ? "true" : "false" ) . ",
            'responsive' :" . ( $options["responsive"] ? "true" : "false" ) . ",
            'processing' : " . ( $options["processing"] ? "true" : "false" ) . ",
            'serverSide' : " . ( $options["server_side"] ? "true" : "false" ) . ",
            'serverMethod' : 'GET',
            'searching' : true,
            'ordering' : " . ( $options["ordering"] ? "true" : "false" ) . ",
            'stateSave' : " . ( $options["state_save"] ? "true" : "false" ) . ",
            'autoWidth' : true,
            'ajax' : {
                url : '" . $options['remote_file'] . "',
                <data_filters>
            },
            $config
            'columns' : " . json_encode($options['columns'], JSON_UNESCAPED_SLASHES) . "
        }";

        $fields_doms = [];
        if ( ! empty($options["ajax_filters"]) ) {
            foreach ( $options["ajax_filters"] as $field_id ) {
                $fields_doms[] = "#" . $field_id;
                $filters .= "data." . $field_id . "= $('#" . $field_id . "').val();";
            }
            $js_filter_function = "data: function(data) { $filters }";
            $js_event_function = "$('body').on('keyup change', '" . implode(', ', $fields_doms) . "', function(e) {
            " . $table_id . "Table.draw();
            });";
        }
        $js_config_script = str_replace("<data_filters>", $js_filter_function, $js_config_script);

    }

    // Enable column resizing
    if ( $options['col_resize'] ) {
        //        $_plugin_folder = INCLUDES . 'jquery/datatables/extensions/ColResize/';
        //        $files = [
        //            'all' => [
        //                'css' => [$_plugin_folder . 'css/datatables.colresize.min.css'],
        //                'js' => [$_plugin_folder . 'js/datatables.colresize.min.js']
        //            ]
        //        ];
        //
        //        $plugin_registers = array_merge_recursive($files, $plugin_registers);
        $options['js_script'] .= 'new $.fn.dataTable.ColResize(' . $table_id . 'Table, {
            isEnabled: true,
            hoverClass: \'dt-colresizable-hover\',
            hasBoundCheck: true,
            minBoundClass: \'dt-colresizable-bound-min\',
            maxBoundClass: \'dt-colresizable-bound-max\',
            isResizable: function(column) { return true; },
            onResize: function(column) {},
            onResizeEnd: function(column, columns) {},
            getMinWidthOf: function($thNode) {}
        });';
    }
    // Enable column reordering
    if ( $options['col_reorder'] ) {
        //        $_plugin_folder = INCLUDES . 'jquery/datatables/extensions/ColReorder/';
        //        $files = [
        //            'BOOTSTRAP4' => [
        //                'css' => [$_plugin_folder . 'css/colReorder.bootstrap4.min.css'],
        //                'js' => [$_plugin_folder . 'js/colReorder.bootstrap4.min.js'],
        //            ],
        //            'BOOTSTRAP' => [
        //                'css' => [$_plugin_folder . 'css/colReorder.bootstrap.min.css'],
        //                'js' => [$_plugin_folder . 'js/colReorder.bootstrap.min.js'],
        //            ],
        //            'default' => [
        //                'css' => [$_plugin_folder . 'css/colReorder.dataTables.min.css'],
        //            ],
        //            'all' => [
        //                'js' => [$_plugin_folder . 'js/dataTables.colReorder.min.js'],
        //            ],
        //        ];
        //        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        $options['js_script'] .= 'new $.fn.dataTable.ColReorder(' . $table_id . 'Table, {} );';
    }
    // Enable responsive design
    if ( $options['responsive'] ) {
        //        $_plugin_folder = INCLUDES . 'jquery/datatables/extensions/Responsive/';
        //        $files = [
        //            'BOOTSTRAP4' => [
        //                'css' => [$_plugin_folder . 'css/responsive.bootstrap4.min.css', $_plugin_folder . 'css/responsive.dataTables.min.css'],
        //                'js' => [$_plugin_folder . 'js/dataTables.responsive.min.js', $_plugin_folder . 'js/responsive.bootstrap4.min.js'],
        //            ],
        //            'BOOTSTRAP' => [
        //                'css' => [$_plugin_folder . 'css/responsive.bootstrap.min.css', $_plugin_folder . 'css/responsive.dataTables.min.css'],
        //                'js' => [
        //                    $_plugin_folder . 'js/dataTables.responsive.min.js',
        //                    $_plugin_folder . 'js/responsive.bootstrap.min.js'
        //                ],
        //            ],
        //            'default' => [
        //                'css' => [$_plugin_folder . 'css/responsive.dataTables.min.css'],
        //                'js' => [$_plugin_folder . 'js/dataTables.responsive.min.js'],
        //            ],
        //        ];
        //
        //        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        //        $options['js_script'] .= 'new $.fn.dataTable.Responsive(' . $table_id . 'Table);';
    }
    // Fixed header
    if ( $options['fixed_header'] ) {
        //        $_plugin_folder = INCLUDES . 'jquery/datatables/extensions/FixedHeader/';
        //        $files = [
        //            'BOOTSTRAP4' => [
        //                'css' => [$_plugin_folder . 'css/fixedHeader.bootstrap4.min.css'],
        //                'js' => [$_plugin_folder . 'js/dataTables.fixedHeader.min.js', $_plugin_folder . 'js/fixedHeader.bootstrap4.min.js'],
        //            ],
        //            'BOOTSTRAP' => [
        //                'css' => [$_plugin_folder . 'css/fixedHeader.bootstrap.min.css'],
        //                'js' => [$_plugin_folder . 'js/dataTables.fixedHeader.min.js', $_plugin_folder . 'js/fixedHeader.bootstrap.min.js'],
        //            ],
        //            'default' => [
        //                'css' => [$_plugin_folder . 'css/fixedHeader.dataTables.min.css'],
        //                'js' => [$_plugin_folder . 'js/dataTables.fixedHeader.min.js', $_plugin_folder . 'js/fixedHeader.dataTables.min.js']
        //            ]
        //        ];
        //        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        $options['js_script'] .= 'new $.fn.dataTable.FixedHeader(' . $table_id . 'Table);';
    }


    // Load file into cache and auto include them
    if ( $template = fusion_theme_framework() ) {

        if ( isset($plugin_registers[ $template ]) ) {
            if ( isset($plugin_registers[ $template ]['css']) ) {
                foreach ( $plugin_registers[ $template ]['css'] as $css_file ) {
                    fusion_load_script($css_file, 'css');
                }
            }
            if ( isset($plugin_registers[ $template ]['js']) ) {
                foreach ( $plugin_registers[ $template ]['js'] as $js_file ) {
                    fusion_load_script($js_file);
                }
            }
        }
        if ( isset($plugin_registers['all']) ) {
            if ( isset($plugin_registers['all']['css']) ) {
                foreach ( $plugin_registers['all']['css'] as $css_file ) {
                    fusion_load_script($css_file, 'css');
                }
            }
            if ( isset($plugin_registers['all']['js']) ) {
                foreach ( $plugin_registers['all']['js'] as $js_file ) {
                    fusion_load_script($js_file);
                }
            }
        }
    }

    $javascript = "let " . $table_id . "Table = $('#$table_id').DataTable($js_config_script);" . $options['js_script'] . "$js_event_function    
    ";

    if ( $options['debug'] ) {
        print_p($javascript);
    }

    add_to_jquery($javascript);

    return $table_id;
}

/**
 * Build and execute a DataTables-compatible query with filtering, searching, ordering, and pagination.
 *
 * This function is designed to work with PHPFusion's `dbquery` and `dbarray`, but can be adapted
 * to any PDO/MySQLi system. It takes a base SQL query (without WHERE, ORDER, LIMIT) and applies
 * DataTables parameters plus custom filters.
 *
 * @param string $baseSql The base SQL query (should SELECT from your dataset, with GROUP BY if needed).
 * @param array  $options Configuration options:
 *
 *   - 'columns_map' (array)
 *       Maps DataTables column index → SQL column name.
 *       Used for global search and ordering.
 *       Example:
 *       ```php
 *       'columns_map' => [
 *           'class_name'     => 'c.class_name',
 *           'class_capacity' => 'c.class_capacity',
 *           'class_status'   => 'c.class_status',
 *       ]
 *       ```
 *
 *   - 'search_map' (array)
 *       Optional SQL expressions used for global search instead of `columns_map`.
 *       Useful when displayed columns are composed from multiple fields or child records.
 *
 *   - 'sql_filter' (array)
 *       Defines custom filters based on request keys.
 *       Can be either a string (default `=`) or array with `column` and `operator`.
 *       Supported operators: `=`, `find_in_set`.
 *       Example:
 *       ```php
 *       'sql_filter' => [
 *           'program_filter' => [
 *               'column' => 'cal.calendar_program_id',
 *               'operator' => 'find_in_set'
 *           ],
 *           'level_filter' => 'cal.calendar_program_level'
 *       ]
 *       ```
 *
 *   - 'request' (string)
 *       Which PHP superglobal to read: `_REQUEST`, `_GET`, `_POST`. Default: `_REQUEST`.
 *
 *   - 'render' (array)
 *       Allows overriding field values before returning data (useful for embedding HTML).
 *       Keys = field name, Value = callback function.
 *       Example:
 *       ```php
 *       'render' => [
 *           'class_name' => function($row) {
 *               return $row['class_name'].' <a href="edit.php?id='.$row['class_id'].'">Edit</a>';
 *           }
 *       ]
 *       ```
 *
 *   - 'debug' (bool)
 *       Print SQL and params for debugging. Default: false.
 *
 * @return array Returns an array formatted for DataTables:
 *   [
 *     'draw'            => (int) DataTables draw counter,
 *     'recordsTotal'    => (int) total rows,
 *     'recordsFiltered' => (int) rows after filtering,
 *     'data'            => (array) result rows
 *   ]
 *
 * Example return:
 * ```php
 * [
 *   'draw' => 1,
 *   'recordsTotal' => 10,
 *   'recordsFiltered' => 3,
 *   'data' => [
 *       ['class_id' => 1, 'class_name' => 'Class A', ...],
 *       ['class_id' => 2, 'class_name' => 'Class B', ...],
 *   ]
 * ]
 * ```
 * Optimized build_fusiontable_query
 * Handles 30,000+ records with server-side pagination.
 */
function build_fusiontable_query(string $baseSql, array $options = [])
: array {

    $default_options = [
        'columns_map' => [],
        'search_map'  => [],
        'sql_filter'  => [],
        'request'     => '_REQUEST',
        'render'      => [],
        'debug'       => FALSE,
    ];
    $options += $default_options;

    $request = match ( $options['request'] ) {
        '_GET' => $_GET,
        '_POST' => $_POST,
        default => $_REQUEST,
    };

    $where = [];
    $params = [];

    // 1. Global Search Logic
    if ( ! empty($request['search']['value']) ) {
        $searchParts = [];
        $searchMap = !empty($options['search_map']) ? $options['search_map'] : $options['columns_map'];
        foreach ( $searchMap as $field => $column ) {
            // Skip non-searchable complex logic indexes
            if ( $field === 0 || $field === 10 || $field === 'occurrence' ) {
                continue;
            }

            $param = ":search_$field";
            $searchParts[] = "$column LIKE $param";
            $params[ $param ] = "%" . $request['search']['value'] . "%";
        }
        if ( $searchParts ) {
            $where[] = "(" . implode(" OR ", $searchParts) . ")";
        }
    }

    // 2. Custom SQL Filters (Hierarchy & Find-In-Set)
    foreach ( $options['sql_filter'] as $filterKey => $filterConf ) {
        if ( array_key_exists($filterKey, $request) && $request[ $filterKey ] !== '' && $request[ $filterKey ] !== NULL ) {
            $paramKey = ':' . $filterKey;
            $column = is_array($filterConf) ? $filterConf['column'] : $filterConf;
            $operator = is_array($filterConf) ? ( $filterConf['operator'] ?? '=' ) : '=';

            // Extract the raw column name (e.g., 'stream_id' from 't.stream_id')
            $colParts = explode('.', $column);
            $pureColumn = end($colParts);

            if ( ( $operator === 'hierarchy' || $operator === 'hierarchy_find_in_set' ) && is_array($filterConf) ) {
                $tableName = $filterConf['table'] ?? '';
                $pk = $filterConf['primary_key'] ?? ''; // e.g., topic_id
                $parentCol = $filterConf['parent_column'] ?? ''; // e.g., topic_parent

                if ( $tableName && $pk && $parentCol ) {
                    $matchFunc = ( $operator === 'hierarchy_find_in_set' )
                        ? "FIND_IN_SET($paramKey, sub.$pureColumn)"
                        : "sub.$pureColumn = $paramKey";

                    $directMatch = ( $operator === 'hierarchy_find_in_set' )
                        ? "FIND_IN_SET($paramKey, $column)"
                        : "$column = $paramKey";

                    // Logic: Match row OR show if row is a parent of a matching child
                    $where[] = "($directMatch OR t.$pk IN (
                        SELECT DISTINCT sub.$parentCol FROM $tableName sub WHERE $matchFunc
                    ))";
                }
            }
            else if ( $operator === 'find_in_set' ) {
                $where[] = "FIND_IN_SET($paramKey, $column)";
            }
            else {
                $where[] = "$column = $paramKey";
            }
            $params[ $paramKey ] = $request[ $filterKey ];
        }
    }

    // 3. Calculate Total Records
    $countSql = "SELECT COUNT(*) FROM (" . $baseSql . ") as _base";
    $recordsTotal = dbresult(dbquery($countSql), 0);

    // 4. Build Filtered SQL
    $sql = $baseSql;
    if ( $where ) {
        if ( stripos($sql, 'WHERE') !== FALSE ) {
            $sql .= " AND " . implode(" AND ", $where);
        }
        else {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
    }

    // 5. Calculate Filtered Count
    $countFilteredSql = "SELECT COUNT(*) FROM (" . $sql . ") as _filtered";
    $recordsFiltered = dbresult(dbquery($countFilteredSql, $params), 0);

    // 6. Apply Ordering (Handles Index 0 Hierarchy Logic)
    if ( isset($request['order']) && is_array($request['order']) ) {
        $orderParts = [];
        foreach ( $request['order'] as $order ) {
            $index = (int)$order['column'];
            $dir = strtoupper($order['dir'] === 'asc' ? 'ASC' : 'DESC');
            if ( isset($options['columns_map'][ $index ]) ) {
                $orderParts[] = $options['columns_map'][ $index ] . " " . $dir;

            }
        }
        if ( $orderParts ) {
            $sql .= " ORDER BY " . implode(", ", $orderParts);
        }
    }
    else if ( ! empty($options['order']) ) {
        $sql .= " ORDER BY " . $options['order'];
    }

    // 7. Apply Pagination
    $start = isset($request['start']) ? (int)$request['start'] : 0;
    $length = isset($request['length']) ? (int)$request['length'] : 10;
    if ( $length > 0 ) {
        $sql .= " LIMIT $start, $length";
    }

    if ( $options['debug'] ) {
        echo "DEBUG SQL: $sql <br> PARAMS: " . json_encode($params);
    }

    // 8. Fetch and Process
    $data = [];
    $res = dbquery($sql, $params);
    $occurrence = $start + 1;

    while ( $row = dbarray($res) ) {
        $row['occurrence'] = $occurrence;

        if ( ! empty($options['render']) ) {
            foreach ( $options['render'] as $field => $callback ) {
                if ( isset($row[ $field ]) || array_key_exists($field, $row) ) {
                    $row[ $field ] = $callback($row);
                }
            }
        }
        $data[] = $row;
        $occurrence++;
    }

    return [
        'draw'            => (int)( $request['draw'] ?? 0 ),
        'recordsTotal'    => (int)$recordsTotal,
        'recordsFiltered' => (int)$recordsFiltered,
        'data'            => $data,
    ];
}
