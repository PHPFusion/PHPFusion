<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: includes/defender/filters.php
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

use PHPFusion\Defender\Svg\Sanitizer;

/**
 * Filter SVG and sanitize all XSS vectors
 * @param $value
 *
 * @return false|string
 */
function filter_svg( $value ) {

    $sanitizer = new Sanitizer();
    $sanitizer->removeRemoteReferences( TRUE );

    return $sanitizer->sanitize( $value );
}


