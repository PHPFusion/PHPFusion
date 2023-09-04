<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bulma/v09.4/index.php
| Author: meangczac (Chan)
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
 * Bulma header
 * @param string $custom_file
 */
function bulma_header( $custom_file = '' ) {

    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<link rel="stylesheet" href="' . INCLUDES . 'plugins/bulma/v1/css/bulma.min.css" defer>';
    //    if (file_exists( $custom_file )) {
    //        echo '<link rel="stylesheet" href="' . $custom_file . '">';
    //    } else {
    //        echo '<link rel="stylesheet" href="' . INCLUDES . 'plugins/bootstrap/v3/css/bootstrap-submenu.min.css" defer>';
    //    }
    if (fusion_get_locale( 'text-direction' ) === 'rtl') {
        echo '<link rel="stylesheet" href="' . INCLUDES . 'plugins/bulma/v1/css/bulma-rtl.min.css" defer>';
    }
}

/**
 * Bulma footer hook
 */
function bulma_footer() {
}
