<?php

defined('IN_FUSION') || exit;

use PHPFusion\Api\AdminDashboardEndpoint;

return [
    'admin.dashboard.widget' => [
        'handler' => [AdminDashboardEndpoint::class, 'widget'],
        'route' => '/v1/admin/dashboard/widget',
        'methods' => ['GET'],
        'aliases' => ['admin-dashboard-widget'],
        'channels' => ['http', 'direct'],
    ],
];
