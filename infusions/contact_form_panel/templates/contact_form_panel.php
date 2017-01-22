<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: templates/contact_form_panel.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('render_contact_panel')) {
    function render_contact_panel(array $info = array()) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'contact.php');

        echo openside($locale['400']);
            echo '{%open_form%}';
            echo '{%mail_name_field%}';
            echo '{%email_field%}';
            echo '{%subject_field%}';
            echo '{%message_field%}';
            include INCLUDES.'captchas/'.fusion_get_settings('captcha').'/captcha_display.php';
            echo '{%captcha%}';
            echo '{%send_button%}';
            echo '{%close_form%}';
        echo closeside();
    }
}
