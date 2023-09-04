<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusions_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

use Defender\ImageValidation;

/**
 * Get max rowstart from a query to prevent renumbering pagenav.
 *
 * @param string $key $_GET key
 * @param int    $max_limit
 *
 * @return int
 */
function get_rowstart($key, $max_limit) {
    $rowstart = get($key, FILTER_VALIDATE_INT);
    if ($rowstart <= $max_limit) {
        return (int)$rowstart;
    }
    return 0;
}

if (!function_exists('random_filename')) {
    /**
     * Generate random filename.
     * Protect filename from uploader by renaming file.
     *
     * @param string $filename The filename.
     *
     * @return string
     */
    function random_filename($filename) {
        $secret_rand = rand(1000000, 9999999);
        $ext = strrchr($filename, ".");

        return substr(md5($secret_rand), 8, 8).$ext;
    }
}

if (!function_exists('filename_exists')) {
    /**
     * Checks if the file exists inside the folder.
     * If not it will create a unique name for the file.
     *
     * @param string $directory The directory to check for the image.
     * @param string $file      The file in the directory you want to check.
     * @param array  $options   dateformat - d,m,y, php date format constant, hash - false to remove hash string
     *
     * @return string  New unique filepath
     */
    function filename_exists($directory, $file = '', $options = []) {
        $parts = pathinfo($directory.$file) + [
                'dirname'   => '',
                'basename'  => '',
                'extension' => '',
                'filename'  => ''
            ];
        if ($parts['extension']) {
            //check if filename starts with dot
            if ($parts['filename']) {
                $parts['extension'] = '.'.strtolower($parts['extension']);
            } else {
                $parts['filename'] = '.'.$parts['extension'];
                $parts['extension'] = '';
            }
            $parts['basename'] = $parts['filename'].$parts['extension'];
        }
        if (isset($options['dateformat']) && $options['dateFormat']) {
            $parts['dirname'] .= '/'.rtrim(date($options['dateFormat']) ?: '.', '/');
        }
        $hash = isset($options['hash']) && $options['hash'] ? '_'.substr(md5(uniqid()), 8) : '';
        //create directory folder if not exists - secondary to current intention.
        $dir = array_filter(explode('/', $directory));
        $parent_dir = '';
        foreach ($dir as $_dir) {
            if (!file_exists($parent_dir.$_dir)) {
                //print_p("Created ".$parent_dir.$_dir." at 0755 ");
                mkdir($parent_dir.$_dir, 0755, TRUE);
                if (!file_exists($parent_dir.$_dir."index.php")) {
                    //print_p("Created an index.php file in ".$parent_dir.$_dir." ");
                    fopen($parent_dir.$_dir.'/index.php', 'w');
                }
            }
            $parent_dir .= $_dir.'/';
        }
        if (!$file) {
            // if exists, return directory, if not those directoy have been created.
            return $directory;
        } else {
            $prefix = $parts['filename'].$hash;
            $new_file = $prefix.$parts['extension'];
            $i = 0;
            while (file_exists($directory.$new_file)) {
                $new_file = $prefix.'_'.++$i.$parts['extension'];
            }
        }

        return $new_file;
    }
}

if (!function_exists('set_setting')) {
    /**
     * Update a setting for the given infusion or create it if the setting does not exist.
     *
     * @param string $setting_name  The name of the setting, must be unique for each infusion.
     * @param string $setting_value The value of the setting.
     * @param string $setting_inf   The infusion name this setting belongs to.
     *
     * @return bool Returns true on successful update / insert or false on error.
     */
    function set_setting($setting_name, $setting_value, $setting_inf) {
        $return = TRUE;

        $bind = [
            ':settings_name' => $setting_name,
            ':settings_inf'  => $setting_inf
        ];

        $resultQuery = "SELECT settings_name
            FROM ".DB_SETTINGS_INF."
            WHERE settings_name=:settings_name AND settings_inf=:settings_inf
            ";

        $result = dbquery($resultQuery, $bind);

        $binds = [
            ':settings_name'  => $setting_name,
            ':settings_value' => $setting_value,
            ':settings_inf'   => $setting_inf
        ];
        if (dbrows($result)) {
            $up_result = dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value=:settings_value WHERE settings_name=:settings_name AND settings_inf=:settings_inf", $binds);
            if (!$up_result) {
                $return = FALSE;
            }
        } else {
            $in_result = dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES (:settings_name, :settings_value, :settings_inf)", $binds);
            if (!$in_result) {
                $return = FALSE;
            }
        }

        return $return;
    }
}

if (!function_exists('infusion_exists')) {
    /**
     * Check whether an infusion is installed or not from the infusions table.
     *
     * @param string $infusion_folder
     *
     * @return bool
     */
    function infusion_exists($infusion_folder) {
        // get the whole thing is faster maybe
        static $infusions_installed = [];
        if (empty($infusions_installed)) {
            $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $infusions_installed[$data['inf_folder']] = TRUE;
                }
            }
        }

        return isset($infusions_installed[$infusion_folder]);
    }
}

if (!function_exists('get_settings')) {
    /**
     * Get all the settings for the given infusion.
     *
     * @param string $settings_inf The infusion you'll get the settings for.
     * @param string $key          The key of one setting.
     *
     * @return mixed|null
     */
    function get_settings($settings_inf, $key = NULL) {
        static $settings_arr = [];
        if (empty($settings_arr) && defined('DB_SETTINGS_INF') && dbconnection() && db_exists('settings_inf')) {
            $result = dbquery("SELECT settings_name, settings_value, settings_inf FROM ".DB_SETTINGS_INF." ORDER BY settings_inf");
            while ($data = dbarray($result)) {
                $settings_arr[$data['settings_inf']][$data['settings_name']] = $data['settings_value'];
            }
        }
        if (empty($settings_arr[$settings_inf]))
            return NULL;

        return $key === NULL ? $settings_arr[$settings_inf] : (isset($settings_arr[$settings_inf][$key]) ? $settings_arr[$settings_inf][$key] : NULL);
    }
}


if (!function_exists('send_pm')) {
    /**
     * Sends a Private Message to specified user with email notification if the receiver has enabled it.
     *
     * @param int    $to       Recepient either group_id or user_id
     * @param int    $from     Sender's user id
     * @param string $subject  Message subject
     * @param string $message  Message body
     * @param string $smileys  Use smileys or not
     * @param bool   $to_group Set to true if sending to the entire user group's members
     */
    function send_pm($to, $from, $subject, $message, $smileys = "y", $to_group = FALSE) {
        PHPFusion\PrivateMessages::sendPm($to, $from, $subject, $message, $smileys, $to_group);
    }
}

if (!function_exists('upload_file')) {
    /**
     * File uploading.
     *
     * @param string $source_file    The key of the $_FILE which holds the uploaded file.
     * @param string $target_file    Name for the uploaded file, leave this blank to use the uploaded file's name.
     * @param string $target_folder  Folder the uploaded file will be moved to.
     * @param string $valid_ext      Valid file extensions for uploaded files.
     * @param int    $max_size       Maximum allowed file size.
     * @param string $query          DB Query when the file is uploaded.
     * @param false  $replace_upload Replace the file if exists in the target folder.
     *
     * @return array Array with information about the upload.
     */
    function upload_file($source_file, $target_file = "", $target_folder = DOWNLOADS, $valid_ext = ".zip,.rar,.tar,.bz2,.7z", $max_size = 15000, $query = "", $replace_upload = FALSE) {
        // only lower case accepted
        $valid_ext = strtolower($valid_ext);

        if (is_uploaded_file($_FILES[$source_file]['tmp_name'])) {

            if (stristr($valid_ext, ',')) {
                $valid_ext = explode(",", $valid_ext);
            } else if (stristr($valid_ext, '|')) {
                $valid_ext = explode("|", $valid_ext);
            } else {
                fusion_stop("Fusion Dynamics invalid accepted extension format. Please use either | or ,");
            }

            $file = $_FILES[$source_file];
            if ($target_file == "" || preg_match("/[^a-zA-Z0-9_-]/", $target_file)) {
                $target_file = stripfilename(substr($file['name'], 0, strrpos($file['name'], ".")));
            }

            $file_ext = strtolower(strrchr($file['name'], "."));
            //$file_dest = $target_folder;
            $upload_file = [
                "source_file"   => $source_file,
                "source_size"   => $file['size'],
                "source_ext"    => $file_ext,
                "target_file"   => $target_file.$file_ext,
                "target_folder" => $target_folder,
                "valid_ext"     => $valid_ext,
                "max_size"      => $max_size,
                "query"         => $query,
                "error"         => 0
            ];
            if ($file['size'] > $max_size) {
                // Maximum file size exceeded
                $upload_file['error'] = 1;
            } else if (empty($valid_ext) || !in_array($file_ext, $valid_ext)) {
                // Invalid file extension
                $upload_file['error'] = 2;
            } else if (fusion_get_settings('mime_check') && ImageValidation::mimeCheck($file['tmp_name'], $file_ext, $valid_ext) === FALSE) {
                $upload_file['error'] = 4;
            } else {
                $target_file = ($replace_upload ? $target_file.$file_ext : filename_exists($target_folder, $target_file.$file_ext));
                $upload_file['target_file'] = $target_file;
                move_uploaded_file($file['tmp_name'], $target_folder.$target_file);
                if (function_exists("chmod")) {
                    chmod($target_folder.$target_file, 0644);
                }
                if ($query && !dbquery($query)) {
                    // Invalid query string
                    $upload_file['error'] = 3;
                    if (file_exists($target_folder.$target_file)) {
                        @unlink($target_folder.$target_file);
                    }
                }
            }
        } else {
            // File not uploaded
            $upload_file = ["error" => 4];
        }

        return $upload_file;
    }
}

if (!function_exists('upload_image')) {
    /**
     * Image uploading.
     *
     * @param string $source_image       Key for the uploaded file in the $_FILES[] array.
     * @param string $target_name        Name of the uploaded image, leave this blank to use the original image name.
     * @param string $target_folder      The folder you are uploading the image to.
     * @param int    $target_width       Maximum allowed width of image in pixels.
     * @param int    $target_height      Maximum allowed height of image in pixels.
     * @param int    $max_size           Max size of image in bytes.
     * @param false  $delete_original    Set this to true if you wish the original image to be deleted after upload.
     * @param bool   $thumb1             Set this to true if you wish to generate a thumbnail number 1.
     * @param bool   $thumb2             Set this to true if you wish to generate a thumbnail number 2.
     * @param int    $thumb1_ratio       Image ratio for the first thumbnail. 0 means original image ratio, 1 means square image ratio.
     * @param string $thumb1_folder      Folder for the first thumbnail.
     * @param string $thumb1_suffix      Text which will be appended at the end of the image name of the first thumbnail.
     * @param int    $thumb1_width       Width of first thumbnail in pixels.
     * @param int    $thumb1_height      Height of first thumbnail in pixels.
     * @param int    $thumb2_ratio       Image ratio for the second thumbnail. 0 means original image ratio, 1 means square image ratio.
     * @param string $thumb2_folder      Folder for the second thumbnail.
     * @param string $thumb2_suffix      Text which will be appended at the end of the image name of the second thumbnail.
     * @param int    $thumb2_width       Width of second thumbnail in pixels.
     * @param int    $thumb2_height      Height of first thumbnail in pixels.
     * @param string $query              DB Query when the image is uploaded.
     * @param array  $allowed_extensions Allowed image extensions.
     * @param false  $replace_upload     Replace image if exists in the target folder.
     *
     * @return array Array with information about the upload.
     */
    function upload_image($source_image, $target_name = "", $target_folder = IMAGES, $target_width = 1800, $target_height = 1600, $max_size = 150000, $delete_original = FALSE, $thumb1 = TRUE, $thumb2 = TRUE, $thumb1_ratio = 0, $thumb1_folder = IMAGES, $thumb1_suffix = "_t1", $thumb1_width = 100, $thumb1_height = 100, $thumb2_ratio = 0, $thumb2_folder = IMAGES, $thumb2_suffix = "_t2", $thumb2_width = 400, $thumb2_height = 300, $query = "", array $allowed_extensions = ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp'], $replace_upload = FALSE) {
        $settings = fusion_get_settings();

        if (strlen($target_folder) > 0 && substr($target_folder, -1) !== '/') {
            $target_folder .= '/';
        }

        if (is_uploaded_file($_FILES[$source_image]['tmp_name'])) {

            $image = $_FILES[$source_image];

            if ($target_name != "" && !preg_match("/[^a-zA-Z0-9_-]/", $target_name)) {
                $image_name = $target_name;
            } else {
                $image_name = stripfilename(substr($image['name'], 0, strrpos($image['name'], ".")));
            }

            $image_ext = strtolower(strrchr($image['name'], "."));

            switch ($image_ext) {
                case '.gif':
                    $filetype = 1;
                    break;
                case '.jpg':
                    $filetype = 2;
                    break;
                case '.png':
                    $filetype = 3;
                    break;
                case '.webp':
                    $filetype = 4;
                    break;
                default:
                    $filetype = FALSE;
            }

            if ($image['size']) {

                if (ImageValidation::mimeCheck($image['tmp_name'], $image_ext, $allowed_extensions) === TRUE) {

                    $image_res = [0, 1];

                    if (getimagesize($image['tmp_name'])) {
                        $image_res = getimagesize($image['tmp_name']);
                    }

                    $image_info = [
                        "image"         => FALSE,
                        "target_folder" => $target_folder,
                        "valid_ext"     => $allowed_extensions,
                        "max_size"      => $max_size,
                        'image_name'    => $image_name.$image_ext,
                        'image_ext'     => $image_ext,
                        'image_size'    => $image['size'],
                        'image_width'   => $image_res[0],
                        'image_height'  => $image_res[1],
                        'thumb1'        => FALSE,
                        'thumb1_name'   => '',
                        'thumb2'        => FALSE,
                        'thumb2_name'   => '',
                        'error'         => 0,
                        'query'         => $query,
                    ];

                    if ($image['size'] > $max_size) {
                        // Invalid file size
                        $image_info['error'] = 1;
                    } else if ($settings['mime_check'] && !verify_image($image['tmp_name'])) {
                        // Failed payload scan
                        $image_info['error'] = 2;
                    } else if ($image_res[0] > $target_width || $image_res[1] > $target_height) {
                        // Invalid image resolution
                        $image_info['error'] = 3;
                    } else {
                        if (!file_exists($target_folder)) {
                            mkdir($target_folder, 0755);
                        }
                        $image_name_full = ($replace_upload ? $image_name.$image_ext : filename_exists($target_folder, $image_name.$image_ext));
                        $image_name = substr($image_name_full, 0, strrpos($image_name_full, "."));
                        $image_info['image_name'] = $image_name_full;
                        $image_info['image'] = TRUE;
                        move_uploaded_file($image['tmp_name'], $target_folder.$image_name_full);
                        if (function_exists("chmod")) {
                            chmod($target_folder.$image_name_full, 0755);
                        }

                        if ($query && !dbquery($query)) {
                            // Invalid query string
                            $image_info['error'] = 4;
                            unlink($target_folder.$image_name_full);
                        } else if ($thumb1 || $thumb2) {

                            require_once INCLUDES."photo_functions_include.php";

                            $noThumb = FALSE;

                            if ($thumb1) {

                                if ($image_res[0] <= $thumb1_width && $image_res[1] <= $thumb1_height) {

                                    $noThumb = TRUE;
                                    $image_info['thumb1_name'] = $image_info['image_name'];
                                    $image_info['thumb1'] = FALSE;

                                } else {

                                    if (!file_exists($thumb1_folder)) {
                                        mkdir($thumb1_folder, 0755, TRUE);
                                    }
                                    $image_name_t1 = filename_exists($thumb1_folder, $image_name.$thumb1_suffix.$image_ext);
                                    $image_info['thumb1_name'] = $image_name_t1;
                                    $image_info['thumb1'] = TRUE;
                                    if ($thumb1_ratio == 0) {
                                        createthumbnail($filetype, $target_folder.$image_name_full, $thumb1_folder.$image_name_t1, $thumb1_width,
                                            $thumb1_height);
                                    } else {
                                        createsquarethumbnail($filetype, $target_folder.$image_name_full, $thumb1_folder.$image_name_t1, $thumb1_width);
                                    }

                                }
                            }

                            if ($thumb2) {
                                if ($image_res[0] < $thumb2_width && $image_res[1] < $thumb2_height) {
                                    $noThumb = TRUE;
                                    $image_info['thumb2_name'] = $image_info['image_name'];
                                    $image_info['thumb2'] = FALSE;
                                } else {
                                    if (!file_exists($thumb2_folder)) {
                                        mkdir($thumb2_folder, 0755, TRUE);
                                    }
                                    $image_name_t2 = ($replace_upload ? $image_name.$thumb2_suffix.$image_ext : filename_exists($thumb2_folder, $image_name.$thumb2_suffix.$image_ext));
                                    $image_info['thumb2_name'] = $image_name_t2;
                                    $image_info['thumb2'] = TRUE;
                                    if ($thumb2_ratio == 0) {
                                        createthumbnail($filetype, $target_folder.$image_name_full, $thumb2_folder.$image_name_t2, $thumb2_width,
                                            $thumb2_height);
                                    } else {
                                        createsquarethumbnail($filetype, $target_folder.$image_name_full, $thumb2_folder.$image_name_t2, $thumb2_width);
                                    }
                                }
                            }
                            if ($delete_original && !$noThumb) {
                                if (file_exists($target_folder.$image_name_full)) {
                                    unlink($target_folder.$image_name_full);
                                }
                                $image_info['image'] = FALSE;
                            }
                        }
                    }
                } else {

                    // Invalid mime check
                    $image_info = ["error" => 5];
                }
            } else {
                // The image is invalid
                $image_info = ["error" => 2];
            }
        } else {
            // Image not uploaded
            $image_info = ["error" => 5];
        }

        return (array)$image_info;
    }
}

if (!function_exists('download_file')) {
    /**
     * Download file from server.
     *
     * @param string $file The path to file.
     */
    function download_file($file) {
        require_once INCLUDES."class.httpdownload.php";
        ob_end_clean();
        $object = new PHPFusion\httpdownload;
        $object->set_byfile($file);
        $object->use_resume = TRUE;
        $object->download();
        exit;
    }
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
function fusion_table($table_id, array $options = []) {
    $locale = fusion_get_locale();

    $table_id = str_replace(["-", " "], "_", $table_id);

    $js_event_function = "";
    $filters = "";
    $js_filter_function = "";

    $default_options = [
        'remote_file'         => '',
        'page_length'         => 0, // result length 0 for default 10
        'debug'               => FALSE,
        'reponse_debug'       => FALSE,
        // Documentation required for these.
        'server_side'         => '',
        'processing'          => '',
        'ajax'                => FALSE,
        'ajax_debug'          => FALSE,
        'responsive'          => TRUE,
        // filter input name on the page if extra filters are used
        'ajax_filters'        => [],
        // not functional yet
        'ajax_data'           => [],
        'order'               => [], // [0, 'desc'] // column 0 order desc - sets default ordering
        'state_save'          => TRUE, // utilizes localStorage to store latest state
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
        'col_resize'          => TRUE,
        'col_reorder'         => TRUE,
        'fixed_header'        => TRUE,
        // custom jsscript append
        'js_script'           => '',
    ];

    $options += $default_options;

    // Map for file inclusion
    $plugin_registers = [
        'BOOTSTRAP4' => [
            'css' => [
                INCLUDES.'jquery/datatables/css/dataTables.bootstrap4.min.css'
            ],
            'js'  => [
                INCLUDES.'jquery/datatables/js/jquery.dataTables.min.js',
                INCLUDES.'jquery/datatables/js/dataTables.bootstrap4.min.js',
            ]
        ],
        'BOOTSTRAP'  => [
            'css' => [
                INCLUDES.'jquery/datatables/css/dataTables.bootstrap.min.css',
            ],
            'js'  => [
                INCLUDES.'jquery/datatables/js/jquery.dataTables.min.js',
                INCLUDES.'jquery/datatables/js/dataTables.bootstrap.min.js',
            ]
        ],
        'default'    => [
            'css' => [
                INCLUDES.'jquery/datatables/css/jquery.dataTables.min.css',
            ],
            'js'  => [
                INCLUDES.'jquery/datatables/js/jquery.dataTables.min.js',
            ]
        ]
    ];

    if ($options['page_length'] && isnum($options['page_length'])) {
        $options['datatable_config']['pageLength'] = (int)$options['page_length'];
    }

    // Build configurations
    $config = "";
    if (!empty($options["order"])) {
        $config .= "'order' : [ ".json_encode($options["order"])." ],";
    }

    if ($options['hide_search_input'] === TRUE) {
        $config .= "'dom': '<\"top\">rt<\"bottom\"><\"clear\">',";
    }

    if ($options['row_reorder'] === TRUE) {

        fusion_load_script(INCLUDES.'jquery/jquery-ui/jquery-ui.min.js');
        fusion_load_script(INCLUDES.'jquery/jquery-ui/jquery-ui.css', 'css');

        $options['pagination'] = FALSE;

        $config .= "
        'info':false,
        'aaSorting': [[1, 'asc']],
        ";

        $options['js_script'] .= "

        let fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

         $('#".$table_id." tbody').sortable({
            helper: fixHelper,
            placeholder: 'state-highlight',
            connectWith: '.connected',
            scroll:true,
            axis: 'y',
            update: function(event, ui) {

                let tableElem = $(this).children('tr');
                let order_array = [];
                tableElem.each(function () {
                    order_array.push($(this).data('id'));
                });

                let formData = new FormData();
                formData.append('fusion_token', '".fusion_get_token($table_id."_token", 10)."');
                formData.append('form_id', '".$table_id."_token');
                formData.append('order', order_array);
                $(this).find('.num').each(function (i) {
                    $(this).text(i + 1);
                });

                fetch('".$options['row_reorder_url']."', {
                    method: 'POST',
                    mode: 'same-origin',
                    cache: 'force-cache',
                    //headers: {
                        //'Content-Type': 'application/json',
                    //},
                    referrerPolicy: 'origin',
                    body: formData
                }).then(function (response) {
                    console.log(response);
                    if (response.status === 200) {
                        add_notice('success', '".$options['row_reorder_success']."');
                    }
                }).catch(function (error) {
                    add_notice('danger', '".$options['row_reorder_failed']."');
                });

            }
        }).disableSelection();";

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

    if ($options['pagination'] === FALSE) {
        $config .= "'paging' : false,";
    }

    $config .= "'language': {
        'processing': '".$locale['processing_locale']."',
        'lengthMenu': '".$locale['menu_locale']."',
        'zeroRecords': '".$locale['zero_locale']."',
        'info': '".$locale['result_locale']."',
        'infoEmpty': '".$locale['empty_locale']."',
        'infoFiltered': '".$locale['filter_locale']."',
        'searchPlaceholder': '".$locale['search_input_locale']."',
        'search': '".$locale['search']."',
        'paginate': {
            'next': '".$locale['next']."',
            'previous': '".$locale['previous']."',
        },
    },";

    // Javascript Init
    $js_config_script = "
    {
        'responsive' :".($options["responsive"] ? "true" : "false").",
        'searching' : true,
        'ordering' : ".($options["ordering"] ? "true" : "false").",
        'stateSave' : ".($options["state_save"] ? "true" : "false").",
        'autoWidth' : true,
        $config
    }";

    $options['js_script'] .= $table_id.'Table.on("draw.dt", function() {
        var hoverable_elem = $("div[data-toggle=\"table-tr-hover\"]");
        hoverable_elem.hide();
        hoverable_elem.closest("tr").on("mouseenter", function(e) {
            $(this).find("div[data-toggle=\"table-tr-hover\"]").show();
        }).on("mouseleave", function(e) {
            $(this).find("div[data-toggle=\"table-tr-hover\"]").hide();
        });
    });';

    // Ajax handling script
    if ($options['remote_file']) {

        if (empty($options["columns"]) && preg_match("@^http(s)?://@i", $options["remote_file"])) {
            $file_output = fusion_get_contents($options['remote_file']);
            if (!empty($file_output)) {
                if (is_json($file_output)) {
                    $output_array = json_decode($file_output, TRUE);
                    //print_P($output_array);
                    if ($options['reponse_debug']) {
                        print_p($output_array);
                    }
                    // Column
                    if (!empty($output_array['data'])) {
                        $output_data = $output_array["data"];
                        $output_reset = reset($output_data);
                        if (is_array($output_reset)) {
                            $column_key = array_keys($output_reset);
                        }
                        if (!empty($column_key)) {
                            foreach ($column_key as $column) {
                                $options["columns"][] = ['data' => $column];
                            }
                        }
                    }
                }
            } else {
                addnotice("danger", "Table columns could not be loaded automatically.");
            }
        }

        $js_config_script = "
        {
            'responsive' :".($options["responsive"] ? "true" : "false").",
            'processing' : ".($options["processing"] ? "true" : "false").",
            'serverSide' : ".($options["server_side"] ? "true" : "false").",
            'serverMethod' : 'POST',
            'searching' : true,
            'ordering' : ".($options["ordering"] ? "true" : "false").",
            'stateSave' : ".($options["state_save"] ? "true" : "false").",
            'autoWidth' : true,
            'ajax' : {
                url : '".$options['remote_file']."',
                <data_filters>
            },
            $config
            'columns' : ".json_encode($options['columns'])."
        }";

        $fields_doms = [];
        if (!empty($options["ajax_filters"])) {

            foreach ($options["ajax_filters"] as $field_id) {
                $fields_doms[] = "#".$field_id;
                $filters .= "data.".$field_id."= $('#".$field_id."').val();";
            }
            $js_filter_function = "data: function(data) { $filters }";
            $js_event_function = "$('body').on('keyup change', '".implode(', ', $fields_doms)."', function(e) {
            ".$table_id."Table.draw();
            });";
        }

        $js_config_script = str_replace("<data_filters>", $js_filter_function, $js_config_script);
    }

    // Enable column resizing
    if ($options['col_resize']) {
        $_plugin_folder = INCLUDES.'jquery/datatables/extensions/ColResize/';
        $files = [
            'all' => [
                'css' => [$_plugin_folder.'css/datatables.colresize.min.css'],
                'js'  => [$_plugin_folder.'js/datatables.colresize.min.js']
            ]
        ];

        $plugin_registers = array_merge_recursive($files, $plugin_registers);

        $options['js_script'] .= 'new $.fn.dataTable.ColResize('.$table_id.'Table, {
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
    if ($options['col_reorder']) {
        $_plugin_folder = INCLUDES.'jquery/datatables/extensions/ColReorder/';
        $files = [
            'BOOTSTRAP4' => [
                'css' => [$_plugin_folder.'css/colReorder.bootstrap4.min.css'],
                'js'  => [$_plugin_folder.'js/colReorder.bootstrap4.min.js'],
            ],
            'BOOTSTRAP'  => [
                'css' => [$_plugin_folder.'css/colReorder.bootstrap.min.css'],
                'js'  => [$_plugin_folder.'js/colReorder.bootstrap.min.js'],
            ],
            'default'    => [
                'css' => [$_plugin_folder.'css/colReorder.dataTables.min.css'],
            ],
            'all'        => [
                'js' => [$_plugin_folder.'js/dataTables.colReorder.min.js'],
            ],
        ];
        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        $options['js_script'] .= 'new $.fn.dataTable.ColReorder('.$table_id.'Table, {} );';
    }

    // Enable responsive design
    if ($options['responsive']) {
        $_plugin_folder = INCLUDES.'jquery/datatables/extensions/Responsive/';
        $files = [
            'BOOTSTRAP4' => [
                'css' => [$_plugin_folder.'css/responsive.bootstrap4.min.css', $_plugin_folder.'css/responsive.dataTables.min.css'],
                'js'  => [$_plugin_folder.'js/dataTables.responsive.min.js', $_plugin_folder.'js/responsive.bootstrap4.min.js'],
            ],
            'BOOTSTRAP'  => [
                'css' => [$_plugin_folder.'css/responsive.bootstrap.min.css', $_plugin_folder.'css/responsive.dataTables.min.css'],
                'js'  => [$_plugin_folder.'js/dataTables.responsive.min.js', $_plugin_folder.'js/responsive.bootstrap.min.js'],
            ],
            'default'    => [
                'css' => [$_plugin_folder.'css/responsive.dataTables.min.css'],
                'js'  => [$_plugin_folder.'js/dataTables.responsive.min.js', $_plugin_folder.'js/dataTables.responsive.min.js'],
            ],
        ];

        $plugin_registers = array_merge_recursive($plugin_registers, $files);

        $options['js_script'] .= 'new $.fn.dataTable.Responsive('.$table_id.'Table);';
    }

    // Fixed header
    if ($options['fixed_header']) {
        $_plugin_folder = INCLUDES.'jquery/datatables/extensions/FixedHeader/';
        $files = [
            'BOOTSTRAP4' => [
                'css' => [$_plugin_folder.'css/fixedHeader.bootstrap4.min.css'],
                'js'  => [$_plugin_folder.'js/dataTables.fixedHeader.min.js', $_plugin_folder.'js/fixedHeader.bootstrap4.min.js'],
            ],
            'BOOTSTRAP'  => [
                'css' => [$_plugin_folder.'css/fixedHeader.bootstrap.min.css'],
                'js'  => [$_plugin_folder.'js/dataTables.fixedHeader.min.js', $_plugin_folder.'js/fixedHeader.bootstrap.min.js'],
            ],
            'default'    => [
                'css' => [$_plugin_folder.'css/fixedHeader.dataTables.min.css'],
                'js'  => [$_plugin_folder.'js/dataTables.fixedHeader.min.js', $_plugin_folder.'js/fixedHeader.dataTables.min.js']
            ]
        ];
        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        $options['js_script'] .= 'new $.fn.dataTable.FixedHeader('.$table_id.'Table);';
    }

    // Load file into cache and auto include them

    if ($template = fusion_theme_framework()) {

        if (isset($plugin_registers[$template])) {
            if (isset($plugin_registers[$template]['css'])) {
                foreach ($plugin_registers[$template]['css'] as $css_file) {
                    fusion_load_script($css_file, 'css');
                }
            }
            if (isset($plugin_registers[$template]['js'])) {

                foreach ($plugin_registers[$template]['js'] as $js_file) {
                    fusion_load_script($js_file);
                }
            }
        }
        if (isset($plugin_registers['all'])) {
            if (isset($plugin_registers['all']['css'])) {
                foreach ($plugin_registers['all']['css'] as $css_file) {
                    fusion_load_script($css_file, 'css');
                }
            }
            if (isset($plugin_registers['all']['js'])) {
                foreach ($plugin_registers['all']['js'] as $js_file) {
                    fusion_load_script($js_file);
                }
            }
        }
    }

    $javascript = "let ".$table_id."Table = $('#$table_id').DataTable($js_config_script);".$options['js_script']."$js_event_function";

    if ($options['debug']) {
        print_p($javascript);
    }
    add_to_jquery($javascript);

    return $table_id;
}

/**
 * @param $info
 *
 * @return mixed
 */
function u_load_check($info) {
    if (!defined('COPYRIGHT') || !stristr(COPYRIGHT, $info) && !defined('iDEVELOPER')) {
        echo '<div class="phpfusion-copyright" style="display:none;">'.showcopyright().'</div>';
    }

    return $info;
}