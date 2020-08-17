<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: captcha_display.php
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

if (!function_exists('display_captcha')) {
    function display_captcha($options = []) {
        $default_options = [
            'show_text_input'   => FALSE,
            'input_name'        => 'captcha_code',
            'show_audio_button' => FALSE
        ];

        $options += $default_options;

        require_once 'securimage.php';
        ob_start();

        echo '<div class="clearfix m-b-15">';
        echo Securimage::getCaptchaHtml($options);
        echo '</div>';

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
