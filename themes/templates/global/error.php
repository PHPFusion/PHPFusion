<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: error.php
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
 * Error template
 */
if (!function_exists("display_error_page")) {
    function display_error_page($data) {
        $locale = fusion_get_locale();
        $text = $data['title'];
        $image = $data['image'];
        opentable($text);
        echo "<table class='table table-responsive' width='100%' style='text-center'>\n";
        echo "<tr>\n";
        echo "<td width='30%' align='center'><img class='img-responsive' src='".$image."' alt='".$text."' border='0'></td>\n";
        echo "<td style='font-size:16px;color:red' align='center'>".$text."</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
        echo "<td colspan='2' align='center'><b><a class='button' href='".BASEDIR."index.php'>".$locale['errret']."</a></b></td>\n";
        echo "</tr>\n";
        echo "</table>\n";
        closetable();
    }
}
