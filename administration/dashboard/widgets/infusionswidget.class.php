<?php

namespace PHPFusion\Administration\Dashboard\Widgets;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;
use PHPFusion\Installer\Infusions;

final class InfusionsWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $result = dbquery('SELECT inf_title, inf_folder, inf_version FROM ' . DB_INFUSIONS . ' ORDER BY inf_title LIMIT 6');
        $items = [];
        while ($row = dbarray($result)) {
            $items[] = $row;
        }
        $total = (int)dbcount('(inf_id)', DB_INFUSIONS);
        $updates = (int)$context->remember('infusion_updates', static fn(): int => (int)Infusions::updateChecker());

        $html = '<div class="admin-dashboard-split-metrics"><div><strong>' . number_format($total) . '</strong><span>'
            . $context->escape($context->text('dashboard_infusions_installed')) . '</span></div><div><strong>'
            . number_format($updates) . '</strong><span>' . $context->escape($context->text('dashboard_infusions_updates')) . '</span></div></div>';
        if ($items !== []) {
            $html .= '<ul class="admin-dashboard-compact-list">';
            foreach ($items as $item) {
                $html .= '<li><span>' . $context->escape($item['inf_title'] ?: $item['inf_folder'])
                    . '</span><code>' . $context->escape($item['inf_version']) . '</code></li>';
            }
            $html .= '</ul>';
        }

        return $html . '<a class="admin-dashboard-link" href="'
            . $context->escape($context->adminUrl('infusions.php') . $context->aidLink()) . '">'
            . $context->escape($context->text('dashboard_manage_infusions')) . '</a>';
    }
}
