<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Polaris/classes/Polaris.autoloader.php
| Author: Meangczac (Chan), Core Development Team
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
 * Polaris Template Autoloader
 * Warnings: Untested with SEF URL
 */
$developer_settings = get_theme_settings('Polaris');

$polaris_template_debug = true;

$_main_templates_dir = TEMPLATES;

// Get the current URL path, removing the leading slash if present
$current_url = ltrim($_SERVER['PHP_SELF'], '/');

if (strpos($current_url, '/')) {
    // Break the URL into parts
    $current_folders = explode('/', $current_url);

    // Get the last element (which could be a filename)
    $last_arr = array_pop($current_folders);

    // Construct potential file paths
    $folder_path = $_main_templates_dir . implode('/', $current_folders) . '/';
    $exact_file_path = $folder_path . $last_arr; // In case it's a direct file

    // If the last segment doesn't have an extension, assume it's a folder and look for index.php
    if (!pathinfo($last_arr, PATHINFO_EXTENSION)) {
        $exact_file_path = $folder_path . $last_arr . '.php'; // Try last segment as a PHP file
        $index_file_path = $folder_path . $last_arr . '/index.php'; // Try index.php inside folder
    } else {
        $index_file_path = $folder_path . 'index.php'; // Default index.php in the folder
    }

    // Debug output
    // print_r($current_folders);
    // echo "Checking: $exact_file_path\n";
    // echo "Checking: $index_file_path\n";

    // Load the correct file if it exists
    if (file_exists($exact_file_path)) {
        
        include $exact_file_path;

    } elseif (isset($index_file_path) && file_exists($index_file_path)) {
        
        include $index_file_path;

    } else {
        
        if ($polaris_template_debug) {
            addnotice('danger', 'Template file not found.');
        }
               
    }

} else {
    
    $default_file = $_main_templates_dir . FUSION_SELF;
    
    if (file_exists($default_file)) {

        include $default_file;

    } else {

        if ($polaris_template_debug) {
            addnotice('danger', 'Template file not found.');
        }
               
    }
}
