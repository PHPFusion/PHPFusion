<?php
defined('IN_FUSION') || exit;

function directory_endpoints() {
    return [
        'item-add-network' => __DIR__.'/api/add-network.php',
        'item-add-hours'   => __DIR__.'/api/add-hours.php'
    ];
    
}

fusion_add_hook('fusion_register_hook_paths', 'directory_endpoints');
