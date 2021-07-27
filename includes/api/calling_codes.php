<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: calling_codes.php
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
defined('IN_FUSION') || exit;

use PHPFusion\Geomap;

/**
 * Fetch calling codes
 */
function fetch_calling_codes() {
    if ($q = get('q')) {
        echo (new Geomap())->callingCodes($q);
    }
}

/**
 * @uses fetch_calling_codes()
 */
fusion_add_hook('fusion_filters', 'fetch_calling_codes');
