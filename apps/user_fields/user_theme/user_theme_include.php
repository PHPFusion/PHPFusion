<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_theme_include.php
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
$locale = fusion_get_locale();
$field_value = $field_value ?? '';
$profile_method = $profile_method ?? '';
$options = $options ?? [];

// Display user field input
if ($profile_method == "input") {

    if (fusion_get_settings('userthemes') == 1 || iADMIN) {

        function get_theme_options() {
            $theme_opts = cache_get('user_theme_options');
            if (empty($theme_files)) {
                $theme_opts = [];
                $theme_files = makefilelist(THEMES, ".|..|admin_themes|templates|.svn", TRUE, "folders");

                array_unshift($theme_files, "Default");
                foreach ($theme_files as $theme) {
                    $theme_opts[$theme] = $theme;
                }

                $theme_opts['Default'] = 'Default ('.fusion_get_settings('theme').')';

                cache_set('user_theme_options', $theme_opts, 3600);
            }
            return $theme_opts;
        }

        $options = [
                'options'        => get_theme_options(),
                'inline'         => FALSE,
                'callback_check' => 'theme_exists',
                'error_text'     => $locale['uf_theme_error'],
                'required'       => FALSE,
            ] + $options;

        $user_fields = form_select('user_theme', $locale['uf_theme'], $field_value, $options);
    }
    // Display in profile
} else if ($profile_method == "display") {
    // no to display
}
