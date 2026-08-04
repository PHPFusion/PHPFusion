<?php

defined('IN_FUSION') || exit;

use PHPFusion\Administration\Dashboard\Widgets\ActivityWidget;
use PHPFusion\Administration\Dashboard\Widgets\AttentionWidget;
use PHPFusion\Administration\Dashboard\Widgets\ContentWidget;
use PHPFusion\Administration\Dashboard\Widgets\InfusionsWidget;
use PHPFusion\Administration\Dashboard\Widgets\MembersWidget;
use PHPFusion\Administration\Dashboard\Widgets\SystemHealthWidget;

return [
    'core.members' => [
        'class' => MembersWidget::class,
        'title_key' => 'dashboard_widget_members',
        'description_key' => 'dashboard_widget_members_description',
        'icon' => 'people',
        'default_visible' => TRUE,
        'order' => 10,
        'span' => ['sm' => 12, 'md' => 6, 'lg' => 3, 'xl' => 3],
        'right' => 'M',
    ],
    'core.content' => [
        'class' => ContentWidget::class,
        'title_key' => 'dashboard_widget_content',
        'description_key' => 'dashboard_widget_content_description',
        'icon' => 'collection',
        'default_visible' => TRUE,
        'order' => 20,
        'span' => ['sm' => 12, 'md' => 6, 'lg' => 3, 'xl' => 3],
        'rights' => ['N', 'A', 'BLOG', 'D', 'PH', 'F', 'W', 'CP'],
    ],
    'core.attention' => [
        'class' => AttentionWidget::class,
        'title_key' => 'dashboard_widget_attention',
        'description_key' => 'dashboard_widget_attention_description',
        'icon' => 'alert',
        'default_visible' => TRUE,
        'order' => 30,
        'span' => ['sm' => 12, 'md' => 6, 'lg' => 3, 'xl' => 3],
        'rights' => ['M', 'SU', 'I', 'ERRO'],
    ],
    'core.infusions' => [
        'class' => InfusionsWidget::class,
        'title_key' => 'dashboard_widget_infusions',
        'description_key' => 'dashboard_widget_infusions_description',
        'icon' => 'infusion',
        'default_visible' => TRUE,
        'order' => 40,
        'span' => ['sm' => 12, 'md' => 6, 'lg' => 3, 'xl' => 3],
        'right' => 'I',
    ],
    'core.activity' => [
        'class' => ActivityWidget::class,
        'title_key' => 'dashboard_widget_activity',
        'description_key' => 'dashboard_widget_activity_description',
        'icon' => 'pulse',
        'default_visible' => TRUE,
        'order' => 50,
        'span' => ['sm' => 12, 'md' => 12, 'lg' => 8, 'xl' => 8],
        'rights' => ['C', 'SU'],
    ],
    'core.system-health' => [
        'class' => SystemHealthWidget::class,
        'title_key' => 'dashboard_widget_health',
        'description_key' => 'dashboard_widget_health_description',
        'icon' => 'system',
        'default_visible' => TRUE,
        'order' => 60,
        'span' => ['sm' => 12, 'md' => 12, 'lg' => 4, 'xl' => 4],
        'super_admin' => TRUE,
    ],
];
