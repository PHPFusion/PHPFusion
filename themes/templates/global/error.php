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
 * Default Error Template
 * Provides override methods for Theme Developers
 */
if (!function_exists("display_error_page")) {
    function display_error_page(array $info = []) {
        opentable('<i class=\'fa fa-warning fa-fw m-r-5 text-warning\'></i>{%title%}');
        ?>
        <div class='row spacer-sm'>
            <div class='col-xs-12 col-sm-3 text-center'>
                <img class='img-responsive' src='{%image_src%}' alt='{%title%}' border='0'/>
            </div>
            <div class='col-xs-12 col-sm-9'>
                <span class='va' style='height:160px'></span>
                <div class='va'>
                    <h4>{%error_code%}</h4>
                </div>
                <div>{%message%}</div>
                <div class='spacer-sm'><a class='button' href='{%back_link%}'>{%back_title%}</a></div>

            </div>
        </div>
        <?php
        closetable();
    }
}
