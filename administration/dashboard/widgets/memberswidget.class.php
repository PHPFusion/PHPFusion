<?php

namespace PHPFusion\Administration\Dashboard\Widgets;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;

final class MembersWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $data = dbarray(dbquery(
            'SELECT COUNT(user_id) AS total,'
            . ' SUM(CASE WHEN user_status IN (0,1,3) THEN 1 ELSE 0 END) AS active,'
            . ' SUM(CASE WHEN user_status=2 THEN 1 ELSE 0 END) AS pending,'
            . ' SUM(CASE WHEN user_status=4 THEN 1 ELSE 0 END) AS security,'
            . ' SUM(CASE WHEN user_status=5 THEN 1 ELSE 0 END) AS cancelled,'
            . ' SUM(CASE WHEN user_status=8 THEN 1 ELSE 0 END) AS inactive'
            . ' FROM ' . DB_USERS
        ));

        $metrics = [
            'active' => 'dashboard_members_active',
            'pending' => 'dashboard_members_pending',
            'security' => 'dashboard_members_security',
            'inactive' => 'dashboard_members_inactive',
        ];
        $html = '<div class="admin-dashboard-primary-metric"><strong>'
            . number_format((int)($data['total'] ?? 0)) . '</strong><span>'
            . $context->escape($context->text('dashboard_members_total')) . '</span></div>';
        $html .= '<dl class="admin-dashboard-metric-grid">';
        foreach ($metrics as $key => $label) {
            $html .= '<div><dt>' . $context->escape($context->text($label)) . '</dt><dd>'
                . number_format((int)($data[$key] ?? 0)) . '</dd></div>';
        }

        return $html . '</dl><a class="admin-dashboard-link" href="'
            . $context->escape($context->adminUrl('members.php') . $context->aidLink()) . '">'
            . $context->escape($context->text('dashboard_manage_members')) . '</a>';
    }
}
