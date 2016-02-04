<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/home.php
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
 * Default template for custom page
 * @param $info
 */
if (!function_exists("render_custompage")) {
    function render_custompage($info) {

        opentable($info['title']);
        echo "<!--cp_idx-->\n";
        if (!empty($info['error'])) {
            echo "<div class='well text-center'>\n";
            echo $info['error'];
            echo "</div>\n";
        } else {
            echo $info['body'][$_GET['rowstart']];
        }
        closetable();

        if (!empty($info['pagenav'])) {
            echo "<div class='display-block text-center m-t-5'>\n";
            echo $info['pagenav'];
            echo "</div>\n";
        }

        echo "<!--cp_sub_idx-->\n";
        echo $info['show_comments'];
        echo $info['show_ratings'];
    }
}