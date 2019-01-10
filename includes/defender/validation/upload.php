<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/defender/validation/upload.php
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

/**
 * Class Upload
 * Handles file or image uploads validation
 */
class Upload extends \Defender\Validation {

    private function in_array_insensitive($needle, $haystack) {
        $needle = strtolower($needle);
        foreach ($haystack as $k => $v) {
            $haystack[$k] = strtolower($v);
        }

        return in_array($needle, $haystack);
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    protected function verify_file_upload() {
        $locale = fusion_get_locale();
        require_once INCLUDES."infusions_include.php";
        if (self::$inputConfig['multiple']) {
            if (!empty($_FILES[self::$inputConfig['input_name']]['name'])) {
                $upload = ['error' => 0];
                if (self::$inputConfig['max_count'] < count($_FILES[self::$inputConfig['input_name']]['name'])) {
                    \defender::stop();
                    $upload = ['error' => 1];
                    addNotice('danger', $locale['df_424']);
                    \defender::setInputError(self::$inputName);
                } else {

                    for ($i = 0; $i <= count($_FILES[self::$inputConfig['input_name']]['name']) - 1; $i++) {

                        if ((self::$inputConfig['max_count'] == $i)) {
                            break;
                        }

                        $source_file = self::$inputConfig['input_name'];
                        $target_file = $_FILES[self::$inputConfig['input_name']]['name'][$i];
                        $target_folder = self::$inputConfig['path'];
                        $valid_ext = self::$inputConfig['valid_ext'];
                        $max_size = self::$inputConfig['max_byte'];
                        $query = '';

                        if (is_uploaded_file($_FILES[$source_file]['tmp_name'][$i])) {
                            /*
                             * Preparation
                             */
                            if (stristr($valid_ext, ',')) {
                                $valid_ext = explode(",", $valid_ext);
                            } else if (stristr($valid_ext, '|')) {
                                $valid_ext = explode("|", $valid_ext);
                            } else {
                                \defender::stop();
                                addNotice('warning', 'Fusion Dynamics invalid accepted extension format. Please use either | or ,');
                            }
                            $file = $_FILES[$source_file];
                            $file_type = $file['type'][$i];
                            if ($target_file == "" || preg_match("/[^a-zA-Z0-9_-]/", $target_file)) {
                                $target_file = stripfilename(substr($file['name'][$i], 0,
                                    strrpos($file['name'][$i], ".")));
                            }
                            $file_ext = strtolower(strrchr($file['name'][$i], "."));
                            $file_dest = rtrim($target_folder, '/').'/';
                            $upload_file = [
                                "source_file"    => $source_file,
                                "source_size"    => $file['size'][$i],
                                "source_ext"     => $file_ext,
                                "target_file"    => $target_file.$file_ext,
                                "target_folder"  => $target_folder,
                                "valid_ext"      => $valid_ext,
                                "max_size"       => $max_size,
                                "query"          => $query,
                                "error"          => 0,
                                "replace_upload" => self::$inputConfig['replace_upload']
                            ];

                            if ($file['size'][$i] > $max_size) {
                                // Maximum file size exceeded
                                $upload['error'] = 1;
                            } else if (!$this->in_array_insensitive($file_ext, $valid_ext)) {
                                // Invalid file extension or mimetypes
                                $upload['error'] = 2;
                            } else if (fusion_get_settings('mime_check') && \Defender\ImageValidation::mime_check($file['tmp_name'][$i], $file_ext, $valid_ext) === FALSE) {
                                $upload['error'] = 4;
                            } else {
                                $target_file = (self::$inputConfig['replace_upload'] ? $target_file.$file_ext : filename_exists($file_dest, $target_file.$file_ext));
                                $upload_file['target_file'] = $target_file;
                                move_uploaded_file($file['tmp_name'][$i], $file_dest.$target_file);
                                if (function_exists("chmod")) {
                                    chmod($file_dest.$target_file, 0644);
                                }
                                if ($query && !dbquery($query)) {
                                    // Invalid query string
                                    $upload['error'] = 3;
                                    if (file_exists($file_dest.$target_file)) {
                                        unlink($file_dest.$target_file);
                                    }
                                }
                            }
                            if ($upload['error'] !== 0) {
                                if (file_exists($file_dest.$target_file.$file_ext)) {
                                    @unlink($file_dest.$target_file.$file_ext);
                                }
                            }
                            $upload['source_file'][$i] = $upload_file['source_file'];
                            $upload['source_size'][$i] = $upload_file['source_size'];
                            $upload['source_ext'][$i] = $upload_file['source_ext'];
                            $upload['target_file'][$i] = $upload_file['target_file'];
                            $upload['target_folder'][$i] = $upload_file['target_folder'];
                            $upload['valid_ext'][$i] = $upload_file['valid_ext'];
                            $upload['max_size'][$i] = $upload_file['max_size'];
                            $upload['query'][$i] = $upload_file['query'];
                            $upload['type'][$i] = $file_type;
                        } else {
                            // File not uploaded
                            $upload['error'] = ["error" => 4];
                        }
                        if ($upload['error'] !== 0) {
                            \defender::stop();
                            switch ($upload['error']) {
                                case 1: // Maximum file size exceeded
                                    addNotice('danger', sprintf($locale['df_416'], parsebytesize(self::$inputConfig['max_byte'])));
                                    \defender::setInputError(self::$inputName);
                                    break;
                                case 2: // Invalid File extensions
                                    addNotice('danger', sprintf($locale['df_417'], self::$inputConfig['valid_ext']));
                                    \defender::setInputError(self::$inputName);
                                    break;
                                case 3: // Invalid Query String
                                    addNotice('danger', $locale['df_422']);
                                    \defender::setInputError(self::$inputName);
                                    break;
                                case 4: // File not uploaded
                                    addNotice('danger', $locale['df_423']);
                                    \defender::setInputError(self::$inputName);
                                    break;
                            }
                        }
                    }
                }
                return $upload;
            } else {
                return [];
            }
        } else {
            if (!empty($_FILES[self::$inputConfig['input_name']]['name']) && is_uploaded_file($_FILES[self::$inputConfig['input_name']]['tmp_name']) && \defender::safe()) {
                $upload = upload_file(self::$inputConfig['input_name'], $_FILES[self::$inputConfig['input_name']]['name'], self::$inputConfig['path'], self::$inputConfig['valid_ext'], self::$inputConfig['max_byte'], "", self::$inputConfig['replace_upload']);
                if ($upload['error'] != 0) {
                    \defender::stop(); // return FALSE
                    switch ($upload['error']) {
                        case 1: // Maximum file size exceeded
                            addNotice('danger', sprintf($locale['df_416'], parsebytesize(self::$inputConfig['max_byte'])));
                            \defender::setInputError(self::$inputName);
                            break;
                        case 2: // Invalid File extensions
                            addNotice('danger', sprintf($locale['df_417'], self::$inputConfig['valid_ext']));
                            \defender::setInputError(self::$inputName);
                            break;
                        case 3: // Invalid Query String
                            addNotice('danger', $locale['df_422']);
                            \defender::setInputError(self::$inputName);
                            break;
                        case 4: // File not uploaded
                            addNotice('danger', $locale['df_423']);
                            \defender::setInputError(self::$inputName);
                            break;
                    }
                } else {
                    return $upload;
                }
            } else {
                return FALSE;
            }
        }
    }

    /**
     * Verify Image Upload
     *
     * @return array
     */
    protected function verify_image_upload() {

        $target_folder = self::$inputConfig['path'];
        $target_width = self::$inputConfig['max_width'];
        $target_height = self::$inputConfig['max_height'];
        $max_size = self::$inputConfig['max_byte'];
        $delete_original = self::$inputConfig['delete_original'];
        $thumb1 = self::$inputConfig['thumbnail'];
        $thumb2 = self::$inputConfig['thumbnail2'];
        $thumb1_ratio = self::$inputConfig['thumbnail_ratio']; // or 0 if error
        $thumb1_folder = self::$inputConfig['path'].self::$inputConfig['thumbnail_folder']."/";
        $thumb1_suffix = self::$inputConfig['thumbnail_suffix'];
        $thumb1_width = self::$inputConfig['thumbnail_w'];
        $thumb1_height = self::$inputConfig['thumbnail_h'];
        $thumb2_ratio = self::$inputConfig['thumbnail2_ratio']; // or 0 if error
        $thumb2_folder = self::$inputConfig['path'].self::$inputConfig['thumbnail_folder']."/";
        $thumb2_suffix =  self::$inputConfig['thumbnail2_suffix'];
        $thumb2_width = self::$inputConfig['thumbnail2_w'];
        $thumb2_height = self::$inputConfig['thumbnail2_h'];
        $allowed_extensions = self::$inputConfig['valid_ext'] ?: ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp'];
        $replace_upload = self::$inputConfig['replace_upload'];
        $query = "";

        if (self::$inputConfig['multiple']) {
            // Multiple
            if (!empty($_FILES[self::$inputConfig['input_name']]['name']) && is_uploaded_file($_FILES[self::$inputConfig['input_name']]['tmp_name'][0]) && \defender::safe()) {
                $result = [];
                for ($i = 0; $i <= count($_FILES[self::$inputConfig['input_name']]['name']) - 1; $i++) {
                    if (is_uploaded_file($_FILES[self::$inputConfig['input_name']]['tmp_name'][$i])) {
                        $image = $_FILES[self::$inputConfig['input_name']];
                        $target_name = $_FILES[self::$inputConfig['input_name']]['name'][$i];
                        if ($target_name != "" && !preg_match("/[^a-zA-Z0-9_-]/", $target_name)) {
                            $image_name = $target_name;
                        } else {
                            $image_name = stripfilename(substr($image['name'][$i], 0, strrpos($image['name'][$i], ".")));
                        }

                        $image_ext = strtolower(strrchr($image['name'][$i], "."));
                        $image_res = [];
                        if (filesize($image['tmp_name'][$i]) > 10 && @getimagesize($image['tmp_name'][$i])) {
                            $image_res = @getimagesize($image['tmp_name'][$i]);
                        }
                        $image_info = [
                            "image"         => FALSE,
                            "target_folder" => $target_folder,
                            "valid_ext"     => self::$inputConfig['valid_ext'],
                            "max_size"      => $max_size,
                            "image_name"    => $image_name.$image_ext,
                            "image_ext"     => $image_ext,
                            "image_size"    => $image['size'],
                            "image_width"   => !empty($image_res[0]) ? $image_res[0] : '',
                            "image_height"  => !empty($image_res[1]) ? $image_res[1] : '',
                            "thumb1"        => FALSE,
                            "thumb1_name"   => "",
                            "thumb2"        => FALSE,
                            "thumb2_name"   => "",
                            "error"         => 0,
                        ];
                        $filetype = FALSE;
                        if ($image_ext == ".gif") {
                            $filetype = 1;
                        } else if ($image_ext == ".jpg") {
                            $filetype = 2;
                        } else if ($image_ext == ".png") {
                            $filetype = 3;
                        } else if ($image_ext == '.svg') {
                            $xml = file_get_contents($image['tmp_name'][$i]);
                            $xmlget = simplexml_load_string($xml);
                            $xmlattributes = $xmlget->attributes();
                            $image_res = array(
                                0 => (string) $xmlattributes->width,
                                1 => (string) $xmlattributes->height
                            );
                            $filetype = 1;
                        }

                        if ($image['size'][$i] > $max_size) {
                            // Invalid file size
                            $image_info['error'] = 1;
                        } else if (!$filetype || !verify_image($image['tmp_name'][$i])) {
                            // Unsupported image type
                            $image_info['error'] = 2;
                        } else if (fusion_get_settings('mime_check') && \Defender\ImageValidation::mime_check($image['tmp_name'][$i], $image_ext, ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp']) === FALSE) {
                            $image_info['error'] = 5;
                        } else if ($image_res[0] > $target_width || $image_res[1] > $target_height) {
                            // Invalid image resolution
                            $image_info['error'] = 3;
                        } else {
                            if (!file_exists($target_folder)) {
                                mkdir($target_folder, 0755);
                            }
                            $image_name_full = (self::$inputConfig['replace_upload'] ? $image_name.$image_ext : filename_exists($target_folder, $image_name.$image_ext));
                            $image_name = substr($image_name_full, 0, strrpos($image_name_full, "."));
                            $image_info['image_name'] = $image_name_full;
                            $image_info['image'] = TRUE;
                            move_uploaded_file($image['tmp_name'][$i], $target_folder.$image_name_full);
                            if (function_exists("chmod")) {
                                chmod($target_folder.$image_name_full, 0755);
                            }
                            if ($query && !dbquery($query)) {
                                // Invalid query string
                                $image_info['error'] = 4;
                                if (file_exists($target_folder.$image_name_full)) {
                                    @unlink($target_folder.$image_name_full);
                                }
                            } else if ($thumb1 || $thumb2) {
                                require_once INCLUDES."photo_functions_include.php";
                                $noThumb = FALSE;
                                if ($thumb1) {
                                    if ($image_res[0] <= $thumb1_width && $image_res[1] <= $thumb1_height) {
                                        $noThumb = TRUE;
                                        $image_info['thumb1_name'] = $image_info['image_name'];
                                        $image_info['thumb1'] = TRUE;
                                    } else {
                                        if (!file_exists($thumb1_folder)) {
                                            mkdir($thumb1_folder, 0755, TRUE);
                                        }
                                        $image_name_t1 = (self::$inputConfig['replace_upload'] ? $image_name.$thumb1_suffix.$image_ext : filename_exists($thumb1_folder, $image_name.$thumb1_suffix.$image_ext));
                                        $image_info['thumb1_name'] = $image_name_t1;
                                        $image_info['thumb1'] = TRUE;
                                        if ($thumb1_ratio == 0) {
                                            createthumbnail($filetype, $target_folder.$image_name_full, $thumb1_folder.$image_name_t1, $thumb1_width, $thumb1_height);
                                        } else {
                                            createsquarethumbnail($filetype, $target_folder.$image_name_full, $thumb1_folder.$image_name_t1, $thumb1_width);
                                        }
                                    }
                                }
                                if ($thumb2) {
                                    if ($image_res[0] < $thumb2_width && $image_res[1] < $thumb2_height) {
                                        $noThumb = TRUE;
                                        $image_info['thumb2_name'] = $image_info['image_name'];
                                        $image_info['thumb2'] = TRUE;
                                    } else {
                                        if (!file_exists($thumb2_folder)) {
                                            mkdir($thumb2_folder, 0755, TRUE);
                                        }
                                        $image_name_t2 = (self::$inputConfig['replace_upload'] ? $image_name.$thumb2_suffix.$image_ext : filename_exists($thumb2_folder, $image_name.$thumb2_suffix.$image_ext));
                                        $image_info['thumb2_name'] = $image_name_t2;
                                        $image_info['thumb2'] = TRUE;
                                        if ($thumb2_ratio == 0) {
                                            createthumbnail($filetype, $target_folder.$image_name_full, $thumb2_folder.$image_name_t2, $thumb2_width, $thumb2_height);
                                        } else {
                                            createsquarethumbnail($filetype, $target_folder.$image_name_full, $thumb2_folder.$image_name_t2, $thumb2_width);
                                        }
                                    }
                                }
                                if ($delete_original && !$noThumb) {
                                    unlink($target_folder.$image_name_full);
                                    $image_info['image'] = FALSE;
                                }
                            }
                        }
                    } else {
                        $image_info = ["error" => 5];
                    }
                    if ($image_info['error'] != 0) {
                        $this->set_error_notice($image_info['error']);
                        $result[$i] = $image_info;
                    } else {
                        $result[$i] = $image_info;
                    }
                } // end for
                return $result;
            } else {
                return [];
            }
        } else {

            $source_image = self::$inputConfig['input_name'];
            $target_name =  $_FILES[self::$inputConfig['input_name']]['name'];
            $croppie = self::$inputConfig['croppie'];

            if ($croppie) {
                $upload['error'] = 5;
                $base64_image = form_sanitizer($_POST[self::$inputConfig['id']."-crop-base64"], "", self::$inputConfig['id']."-crop-base64");
                $base64_image = str_replace('data:image/png;base64,', '', $base64_image);
                $base64_image = str_replace(' ', '+', $base64_image);
                $base64_image = base64_decode($base64_image);

                $image_name = uniqid().'_'.TIME.'.png';
                $file = $target_folder.$image_name;
                $write_image = file_put_contents($file, $base64_image);

                if ($write_image) {
                    $image_size = 0;
                    list($width, $height, $type, $attr) = getimagesize($file);
                    $upload = [
                        'image'         => TRUE,
                        "target_folder" => $target_folder,
                        "valid_ext"     => $allowed_extensions,
                        "max_size"      => $max_size,
                        'image_name'    => $image_name,
                        'image_ext'     => '.png',
                        'image_size'    => $image_size,
                        'image_width'   => $width,
                        'image_height'  => $height,
                        'thumb1'        => FALSE,
                        'thumb1_name'   => '',
                        'thumb2'        => FALSE,
                        'thumb2_name'   => '',
                        'error'         => 0,
                        'query'         => $query,
                    ];

                    if ($query && !dbquery($query)) {
                        // Invalid query string
                        $upload['error'] = 4;
                        unlink($file);
                    }

                    return $upload;
                }

                // If fail.
                \defender::stop();
                $this->set_error_notice($upload['error']);
                return $upload; // return $upload['error'] only

            }


            if (!empty($_FILES[self::$inputConfig['input_name']]['name']) && is_uploaded_file($_FILES[self::$inputConfig['input_name']]['tmp_name']) && \defender::safe()) {

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
                        default:
                            $filetype = FALSE;
                    }

                    // need to run file_exist. @ supress will not work anymore.
                    if ($image['size']) {

                        $image_res = @getimagesize($image['tmp_name']);

                        $upload = [
                            'image'         => FALSE,
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
                            $upload['error'] = 1;
                        } else if (!verify_image($image['tmp_name'])) {
                            // Failed payload scan
                            $upload['error'] = 2;
                        } else if (fusion_get_settings('mime_check') && \Defender\ImageValidation::mime_check($image['tmp_name'], $image_ext, $allowed_extensions) === FALSE) {
                            // Failed extension checks
                            $upload['error'] = 5;
                        } else if ($image_res[0] > $target_width || $image_res[1] > $target_height) {
                            // Invalid image resolution
                            $upload['error'] = 3;
                        } else {

                            $upload['error'] = 0;

                            if (!file_exists($target_folder)) {
                                mkdir($target_folder, 0755);
                            }
                            $image_name_full = ($replace_upload ? $image_name.$image_ext : filename_exists($target_folder, $image_name.$image_ext));
                            $image_name = substr($image_name_full, 0, strrpos($image_name_full, "."));
                            $upload['image_name'] = $image_name_full;
                            $upload['image'] = TRUE;

                            move_uploaded_file($image['tmp_name'], $target_folder.$image_name_full);
                            if (function_exists("chmod")) {
                                chmod($target_folder.$image_name_full, 0755);
                            }

                            if ($query && !dbquery($query)) {
                                // Invalid query string
                                $upload['error'] = 4;
                                unlink($target_folder.$image_name_full);

                            } else if ($thumb1 || $thumb2) {

                                require_once INCLUDES."photo_functions_include.php";
                                $noThumb = FALSE;
                                if ($thumb1) {
                                    if ($image_res[0] <= $thumb1_width && $image_res[1] <= $thumb1_height) {
                                        $noThumb = TRUE;
                                        $upload['thumb1_name'] = $upload['image_name'];
                                        $upload['thumb1'] = FALSE;
                                    } else {
                                        if (!file_exists($thumb1_folder)) {
                                            mkdir($thumb1_folder, 0755, TRUE);
                                        }
                                        $image_name_t1 = filename_exists($thumb1_folder, $image_name.$thumb1_suffix.$image_ext);
                                        $upload['thumb1_name'] = $image_name_t1;
                                        $upload['thumb1'] = TRUE;
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
                                        $upload['thumb2_name'] = $upload['image_name'];
                                        $upload['thumb2'] = FALSE;
                                    } else {
                                        if (!file_exists($thumb2_folder)) {
                                            mkdir($thumb2_folder, 0755, TRUE);
                                        }
                                        $image_name_t2 = ($replace_upload ? $image_name.$thumb2_suffix.$image_ext : filename_exists($thumb2_folder, $image_name.$thumb2_suffix.$image_ext));
                                        $upload['thumb2_name'] = $image_name_t2;
                                        $upload['thumb2'] = TRUE;
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
                                    $upload['image'] = FALSE;
                                }
                            }
                        }

                    } else {
                        // The image is invalid
                        $upload['error'] = 2;
                    }
                } else {
                    // Image not uploaded
                    $upload['error'] = 5;
                }

                if ($upload['error'] != 0) {
                    \defender::stop();
                    $this->set_error_notice($upload['error']);
                    return $upload;
                } else {
                    return $upload;
                }
            } else {
                return [];
            }
        }
    }

    private function set_error_notice($error_code) {
        \defender::stop();
        $locale = fusion_get_locale();
        switch ($error_code) {
            case 1: // Invalid file size
                addNotice('danger', sprintf($locale['df_416'], parsebytesize(self::$inputConfig['max_byte'])));
                \defender::setInputError(self::$inputName);
                break;
            case 2: // Unsupported image type
                //addNotice('danger', $locale['df_423']);
                addNotice('danger', $locale['error_secure_file']);
                \defender::setInputError(self::$inputName);
                break;
            case 3: // Invalid image resolution
                addNotice('danger', sprintf($locale['df_421'], self::$inputConfig['max_width'], self::$inputConfig['max_height']));
                \defender::setInputError(self::$inputName);
                break;
            case 4: // Invalid query string
                addNotice('danger', $locale['df_422']);
                \defender::setInputError(self::$inputName);
                break;
            case 5: // Image not uploaded
                addNotice('danger', sprintf($locale['df_417'], self::$inputConfig['valid_ext']));
                \defender::setInputError(self::$inputName);
                break;
        }
    }
}

require_once(dirname(__FILE__).'/../../mimetypes_include.php');
require_once(dirname(__FILE__).'/../../infusions_include.php');
