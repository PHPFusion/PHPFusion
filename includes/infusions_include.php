<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: infusions_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

function get_rowstart($key, $max_limit) {
    $rowstart = get($key, FILTER_VALIDATE_INT);
    if ($rowstart <= $max_limit) {
        return (int)$rowstart;
    }
    return (int)0;
}

// Protect filename from uploader by renaming file.
if (!function_exists('random_filename')) {
    function random_filename($filename) {
        $secret_rand = rand(1000000, 9999999);
        $ext = strrchr($filename, ".");

        return substr(md5($secret_rand), 8, 8).$ext;
    }
}

if (!function_exists('filename_exists')) {
    /**
     * Creates an unique filename if file already exists
     *
     * @param        $directory
     * @param string $file path of file if basename is empty. Otherwise path of parent directory
     * @param bool   $options
     *
     * @return string  New unique filepath
     * @options array -
     *                           $options['dateformat'] d,m,y, php date format constant
     *                           $options['hash'] '0' by default, '1' to add hash string
     */
    function filename_exists($directory, $file = '', $options = FALSE) {
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
            // if exist, return directory, if not those directoy have been created.
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
    // Sets the value of a setting in the settings_inf table
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
     * Check whether an infusion is installed or not from the infusions table
     *
     * @param $infusion_folder
     *
     * @return bool
     */
    function infusion_exists($infusion_folder) {
        // get the whole thing is faster maybe
        static $infusions_installed = array();
        if (empty($infusions_installed)) {
            $result = dbquery("SELECT inf_folder FROM ".DB_INFUSIONS);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $infusions_installed[$data['inf_folder']] = TRUE;
                }
            }
        }

        return (boolean)(isset($infusions_installed[$infusion_folder])) ? TRUE : FALSE;

    }
}

/**
 * Get the settings for the infusion from the settings_inf table
 *
 * @param      $settings_inf
 * @param null $key
 *
 * @return mixed|null
 */
if (!function_exists('get_settings')) {
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
     * Send PM to a user or group
     *
     * @param        $to - Recepient Either group_id or user_id
     * @param        $from - Sender's user id
     * @param        $subject - Message subject
     * @param        $message - Message body
     * @param string $smileys - use smileys or not
     * @param bool   $to_group - set to true if sending to the entire user group's members
     */
    function send_pm($to, $from, $subject, $message, $smileys = "y", $to_group = FALSE) {
        \PHPFusion\PrivateMessages::send_pm($to, $from, $subject, $message, $smileys, $to_group);
    }
}

// Upload file function
if (!function_exists('upload_file')) {

    function upload_file($source_file, $target_file = "", $target_folder = DOWNLOADS, $valid_ext = ".zip,.rar,.tar,.bz2,.7z", $max_size = "15000", $query = "", $replace_upload = FALSE) {
        if (is_uploaded_file($_FILES[$source_file]['tmp_name'])) {

            if (stristr($valid_ext, ',')) {
                $valid_ext = explode(",", $valid_ext);
            } else if (stristr($valid_ext, '|')) {
                $valid_ext = explode("|", $valid_ext);
            } else {
                \Defender::stop();
                add_notice('warning', 'Fusion Dynamics invalid accepted extension format. Please use either | or ,');
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
            } else if (fusion_get_settings('mime_check') && \Defender\ImageValidation::mime_check($file['tmp_name'], $file_ext, $valid_ext) === FALSE) {
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

// Upload image function
if (!function_exists('upload_image')) {

    function upload_image($source_image, $target_name = "", $target_folder = IMAGES, $target_width = "1800", $target_height = "1600", $max_size = "150000", $delete_original = FALSE, $thumb1 = TRUE, $thumb2 = TRUE, $thumb1_ratio = 0, $thumb1_folder = IMAGES, $thumb1_suffix = "_t1", $thumb1_width = "100", $thumb1_height = "100", $thumb2_ratio = 0, $thumb2_folder = IMAGES, $thumb2_suffix = "_t2", $thumb2_width = "400", $thumb2_height = "300", $query = "", array $allowed_extensions = ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp'], $replace_upload = FALSE) {

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

                if (\Defender\ImageValidation::mime_check($image['tmp_name'], $image_ext, $allowed_extensions) === TRUE) {

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

// Download file from server
if (!function_exists('download_file')) {
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
