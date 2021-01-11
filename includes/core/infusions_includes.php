<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusions_includes.php
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

use Defender\ImageValidation;

/**
 * @param $key
 * @param $value
 */
function add_notice($key, $value) {
    addNotice($key, $value);
}

/**
 * @param array $options
 * @param array $replacements
 *
 * @return array
 */
function get_status_opts(array $options = [], array $replacements = []) {
    $locale = fusion_get_locale();
    $default_statuses = [
        0 => $locale["disable"],
        1 => $locale["enable"],
    ];
    $options += $default_statuses;
    if (!empty($replacements)) {
        $options = $replacements;
    }
    return $options;
}


/**
 * Generates a HTML link with given options.
 *
 * @param       $link
 * @param       $title
 * @param array $options
 *
 * @return string
 */
function format_link($link, $title = "", $options = []) {

    $default_options = [
        "no_index" => FALSE,
        "new_tab"  => FALSE,
        "class"    => "",
        "onclick"  => "",
    ];

    $options += $default_options;

    $index_comps = array_flip(["%1", "%2", "%3", "%4"]);
    if ($options["no_index"] === TRUE) {
        $index_comps = [
            "%1" => "<!--noindex-->",
            "%2" => " rel='rel='nofollow noopener noreferrer'",
            "%3" => "<!--/noindex-->",
            "%4" => "",
            "%5" => "",
            "%6" => "",
        ];
    }
    if ($options["new_tab"])
        $index_comps["%4"] = "target='_blank'";

    if ($options["class"]) {
        $index_comps["%5"] = $options["class"];
    }
    if ($options["onclick"]) {
        $index_comps["%6"] = "onclick='".$options["onclick"]."'";
    }

    $link_comps = "%1<a href='$link' title='$title'%2%4>$title</a>%3";

    return strtr($link_comps, $index_comps);
}

/**
 * @param int    $user_id
 * @param string $class
 *
 * @return string
 */
function user_link(int $user_id, string $class = "profile-link") {
    static $user_link;

    if (empty($user_link[$user_id]) && isnum($user_id)) {

        $user_name = "N/A";

        if ($user = fusion_get_user($user_id)) {
            $user_name = $user["user_name"];
            if (!empty($user["user_displayname"])) {
                if (!empty($user["user_firstname"])) {
                    $user_name = $user["user_firstname"];
                }
                if (!empty($user["user_lastname"])) {
                    $user_name .= whitespace($user["user_lastname"]);
                }
            }
        }

        $user_link[$user_id] = "<a href='".BASEDIR."profile.php?lookup=".$user_id."' class='$class'>".$user_name."</a>";
    }

    return (isset($user_link[$user_id]) ? $user_link[$user_id] : "");
}

/**
 * @param        $value
 * @param string $currency
 * @param string $locale
 *
 * @return string
 */
function format_currency($value, $currency = "USD", $locale = "en_US") {
    $fmt = numfmt_create($locale, NumberFormatter::CURRENCY);
    $currency = fusion_get_currency($currency);
    return numfmt_format_currency($fmt, $value, $currency);
}


// Upload image function
if (!function_exists('upload_image_alt')) {
    /**
     * @param        $source_image
     * @param string $target_name
     * @param string $target_folder
     * @param string $target_width
     * @param string $target_height
     * @param string $max_size
     * @param bool   $delete_original
     * @param bool   $thumb1
     * @param bool   $thumb2
     * @param int    $thumb1_ratio
     * @param string $thumb1_folder
     * @param string $thumb1_suffix
     * @param string $thumb1_width
     * @param string $thumb1_height
     * @param int    $thumb2_ratio
     * @param string $thumb2_folder
     * @param string $thumb2_suffix
     * @param string $thumb2_width
     * @param string $thumb2_height
     * @param string $query
     * @param array  $allowed_extensions
     * @param bool   $replace_upload
     * @param string $croppie_input
     *
     * @return array
     */
    function upload_image_alt($source_image, $target_name = "", $target_folder = IMAGES, $target_width = "1800", $target_height = "1600", $max_size = "150000", $delete_original = FALSE, $thumb1 = TRUE, $thumb2 = TRUE, $thumb1_ratio = 0, $thumb1_folder = IMAGES, $thumb1_suffix = "_t1", $thumb1_width = "100", $thumb1_height = "100", $thumb2_ratio = 0, $thumb2_folder = IMAGES, $thumb2_suffix = "_t2", $thumb2_width = "400", $thumb2_height = "300", $query = "", array $allowed_extensions = ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp'], $replace_upload = FALSE, $croppie_input = "") {
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
                case ".gif":
                    $filetype = 1;
                    break;
                case ".jpg":
                    $filetype = 2;
                    break;
                case ".png":
                    $filetype = 3;
                    break;
                case ".svg":
                    $filetype = 4;
                    break;
                default:
                    $filetype = FALSE;
            }

            if ($image['size']) {

                if ($settings["mime_check"] && ImageValidation::mime_check($image['tmp_name'], $image_ext, $allowed_extensions) === TRUE) {

                    $image_res = [0, 1];
                    $imagewidth = 0;
                    $imageheight = 0;
                    $imageType = "";
                    if (getimagesize($image['tmp_name'])) {
                        $image_res = getimagesize($image['tmp_name']);
                        list($imagewidth, $imageheight, $imageType) = $image_res;
                    }

                    $image_info = [
                        "image"         => FALSE,
                        "target_folder" => $target_folder,
                        "valid_ext"     => $allowed_extensions,
                        "max_size"      => $max_size,
                        "image_name"    => $image_name.$image_ext,
                        "image_ext"     => $image_ext,
                        "image_size"    => $image["size"],
                        "image_width"   => $image_res[0],
                        "image_height"  => $image_res[1],
                        "thumb1"        => FALSE,
                        "thumb1_name"   => "",
                        "thumb2"        => FALSE,
                        "thumb2_name"   => "",
                        "error"         => 0,
                        "query"         => $query,
                    ];

                    if ($image["size"] > $max_size) {
                        // Invalid file size
                        $image_info["error"] = 1;
                    } else if (!$filetype || $settings["mime_check"] && !verify_image($image["tmp_name"])) {
                        // Unsupported image type
                        $image_info["error"] = 2;
                    } else if ($image_res[0] > $target_width || $image_res[1] > $target_height) {
                        // Invalid image resolution
                        $image_info["error"] = 3;
                    } else {
                        if (!file_exists($target_folder)) {
                            mkdir($target_folder, 0755);
                        }

                        $image_name_full = ($replace_upload ? $image_name.$image_ext : filename_exists($target_folder, $image_name.$image_ext));

                        $image_name = substr($image_name_full, 0, strrpos($image_name_full, "."));
                        $image_info['image_name'] = $image_name_full;
                        $image_info['image'] = TRUE;

                        if ($croppie_input && $imageType) {

                            $image = post($croppie_input);
                            $image_array_1 = explode(";", $image);
                            $image_array_2 = explode(",", $image_array_1[1]);
                            $data = base64_decode($image_array_2[1]);
                            file_put_contents($target_folder.$image_name_full, $data);

                        } else {

                            move_uploaded_file($image['tmp_name'], $target_folder.$image_name_full);
                            if (function_exists("chmod")) {
                                chmod($target_folder.$image_name_full, 0755);
                            }

                            if ($query && !dbquery($query)) {

                                // Invalid query string
                                $image_info['error'] = 4;
                                if (file_exists($target_folder.$image_name_full)) {
                                    unlink($target_folder.$image_name_full);
                                }

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
                                        $image_name_t1 = ($replace_upload ? $image_name.$thumb1_suffix.$image_ext : filename_exists($thumb1_folder, $image_name.$thumb1_suffix.$image_ext));
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
                                        $image_info['thumb2'] = FALSE;
                                    } else {
                                        if (!file_exists($thumb2_folder)) {
                                            mkdir($thumb2_folder, 0755, TRUE);
                                        }
                                        $image_name_t2 = ($replace_upload ? $image_name.$thumb2_suffix.$image_ext : filename_exists($thumb2_folder, $image_name.$thumb2_suffix.$image_ext));
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
                                    if (file_exists($target_folder.$image_name_full)) {
                                        unlink($target_folder.$image_name_full);
                                    }
                                    $image_info['image'] = FALSE;
                                }
                            }
                        }

                    }
                } else {
                    // Invalid mime check
                    $image_info = ["error" => 5];
                }
            } else {
                // The image is invalid - size error
                $image_info = ["error" => 2];
            }
        } else {
            // Image not uploaded
            $image_info = ["error" => 6];
        }

        return (array)$image_info;
    }
}
