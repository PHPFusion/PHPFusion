<?php

namespace PHPFusion\Administration\Dashboard\Widgets;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;

final class SystemHealthWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $maintenance = (int)$context->settings('maintenance') === 1;
        $errors = db_exists(DB_ERRORS, FALSE)
            ? (int)dbcount('(error_id)', DB_ERRORS, 'error_status=0')
            : 0;
        $items = [
            [$context->text('dashboard_health_cms'), (string)$context->settings('version')],
            [$context->text('dashboard_health_php'), PHP_VERSION],
            [$context->text('dashboard_health_maintenance'), $maintenance
                ? $context->text('dashboard_health_enabled')
                : $context->text('dashboard_health_disabled')],
            [$context->text('dashboard_health_open_errors'), number_format($errors)],
        ];

        $html = '<dl class="admin-dashboard-health-list">';
        foreach ($items as [$label, $value]) {
            $html .= '<div><dt>' . $context->escape($label) . '</dt><dd>' . $context->escape($value) . '</dd></div>';
        }

        return $html . '</dl><a class="admin-dashboard-link" href="'
            . $context->escape($context->adminUrl('serverinfo.php') . $context->aidLink()) . '">'
            . $context->escape($context->text('dashboard_health_details')) . '</a>';
    }
}
