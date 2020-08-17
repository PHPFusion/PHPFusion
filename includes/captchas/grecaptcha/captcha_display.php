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

$_CAPTCHA_HIDE_INPUT = TRUE;

if (!function_exists('display_captcha')) {
    function display_captcha($options = []) {
        $settings = fusion_get_settings();

        $default_options = [
            'captcha_id' => 'g-recaptcha'
        ];

        $options += $default_options;

        add_to_footer('<script src="https://www.google.com/recaptcha/api.js?hl='.fusion_get_locale('xml_lang').'" async defer></script>');
        add_to_head('<style type="text/css">.g-recaptcha{position:relative;padding-top:0;margin-bottom:10px;overflow:hidden}.g-recaptcha>iframe{position:absolute;top:0;left:0;width:100%;height:100%}</style>');

        return '<div class="g-recaptcha" id="'.$options['captcha_id'].'" data-type="'.$settings['recaptcha_type'].'" data-theme="'.$settings['recaptcha_theme'].'" data-sitekey="'.$settings['recaptcha_public'].'"></div>';
    }
}
