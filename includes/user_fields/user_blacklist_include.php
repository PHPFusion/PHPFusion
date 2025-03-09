<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_blacklist_include.php
| Author: Chan (Frederick MC Chan)
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

// Display user field input
if ($profile_method == "input") {
    $options = [
            'inline'     => TRUE,
            'error_text' => $locale['uf_blacklist_error'],
            'multiple'   => TRUE,
            'ext_tip'    => $locale['uf_blacklist_message'],
            'max_select' => FALSE
        ] + $options;
    $user_fields = form_user_select('user_blacklist', $locale['uf_blacklist'], $field_value, $options);
}
