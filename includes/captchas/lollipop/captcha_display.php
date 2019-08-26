<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_display.php
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
defined('IN_FUSION') || exit;

$_CAPTCHA_HIDE_INPUT = TRUE;

// Display Capthca
if (!function_exists('display_captcha')) {
    function display_captcha($options = []) {
        $default_options = [
            'form_name'      => 'inputform',
            'inline_options' => TRUE,
            'inline'         => TRUE
        ];

        $options += $default_options;

        require_once INCLUDES.'captchas/lollipop/lollipop.php';

        $captcha = new Lollipop($options['form_name']);
        $html = form_checkbox('lollipop[]', $captcha->getQuestions(), '', [
            'options'        => $captcha->getAnswers(),
            'inline_options' => $options['inline_options'],
            'inline'         => $options['inline'],
        ]);

        return $html;
    }
}
