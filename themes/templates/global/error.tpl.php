<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: error.tpl.php
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

/**
 * Default Error Template
 */
if (!function_exists("display_error_page")) {
    function display_error_page(array $info = []) {
        $locale = fusion_get_locale();

        PHPFusion\Panels::getInstance()->hideAll();

        echo '<div class="text-center">';
        opentable('<i class="fa fa-warning fa-fw m-r-5 text-warning"></i> '.$info['title']);
        echo '<h1 style="font-size:20rem;">'.$info['status'].'</h1>';
        echo '<h3>'.$locale['errmsg'].'</h3>';
        echo '<a href="'.$info['back']['url'].'">'.$info['back']['title'].'</a>';
        closetable();
        echo '</div>';
    }
}
