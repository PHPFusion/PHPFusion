<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: poll.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('render_poll')) {
    function render_poll($info) {
        if (!empty($info['poll_table'])){
            openside($info['poll_tablename']);
            echo "<div class='row m-t-20'>\n";
            foreach ($info['poll_table'] as $key => $inf) {
                echo "<div class='col-xs-12 col-sm-12'>\n";
                    echo "<div class='panel panel-default'>\n";
                        echo "<div class='panel-heading text-center'>\n";
                            echo $inf['poll_title'];
                        echo "</div>\n";

                        echo "<div class='panel-body'>\n";
                            foreach ($inf['poll_option'] as $key => $inf_opt) {
                                echo $inf_opt;
                            }
                        echo "</div>\n";

                        echo "<div class='panel-footer text-center'>\n";
                            foreach ($inf['poll_foot'] as $key => $inf_opt) {
                               echo "<p class='text-center m-b-0'>".$inf_opt."</p>\n";
                            }
                        echo "</div>\n";
                    echo "</div>\n";
                echo "</div>\n";
            }

            echo !empty($info['poll_arch']) ? '<div class="text-center">'.$info['poll_arch'].'</div>' : "";

            echo "</div>\n";
            closeside();
        }
    }
}
