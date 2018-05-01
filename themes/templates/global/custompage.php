<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/custompage.php
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
if (!function_exists("display_page")) {
    function display_page($info) {
        echo render_breadcrumbs();
        opentable($info['title']);
        echo "<!--cp_idx-->\n";
        if (!empty($info['error'])) {
            echo "<div class='well text-center'>\n";
            echo $info['error'];
            echo "</div>\n";
        } else {
            echo $info['body'];
        }
        closetable();
    }
}

if (!function_exists("display_page_content")) {
    function display_page_content($info) {
        echo "<!--cp_idx-->\n";
        if (!empty($info['error'])) {
            echo "<div class='well text-center'>\n";
            echo $info['error'];
            echo "</div>\n";
        } else {
            echo $info['body'][$info['rowstart']];
            echo $info['pagenav'];
        }
    }
}
