<?php

use PHPFusion\Geomap;

defined('IN_FUSION')||exit;

function fetch_calling_codes() {
    if ($q = get('q')) {
        echo (new Geomap())->callingCodes($q);
    }
}

fusion_add_hook('fusion_filters', 'fetch_calling_codes');
