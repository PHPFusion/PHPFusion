<?php

namespace PHPFusion\Administration\Dashboard\Widgets;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;
use PHPFusion\Installer\Infusions;

final class AttentionWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $items = [];
        if (checkrights('M')) {
            $items[] = [
                'label' => $context->text('dashboard_attention_activations'),
                'value' => (int)dbcount('(user_id)', DB_USERS, 'user_status=2'),
                'href' => $context->adminUrl('members.php') . $context->aidLink() . '&status=2',
                'tone' => 'warning',
            ];
        }
        if (checkrights('SU')) {
            $items[] = [
                'label' => $context->text('dashboard_attention_submissions'),
                'value' => (int)dbcount('(submit_id)', DB_SUBMISSIONS),
                'href' => $context->adminUrl('submissions.php') . $context->aidLink(),
                'tone' => 'info',
            ];
        }
        if (checkrights('I')) {
            $items[] = [
                'label' => $context->text('dashboard_attention_updates'),
                'value' => (int)$context->remember('infusion_updates', static fn(): int => (int)Infusions::updateChecker()),
                'href' => $context->adminUrl('infusions.php') . $context->aidLink(),
                'tone' => 'success',
            ];
        }
        if (checkrights('ERRO') && db_exists(DB_ERRORS, FALSE)) {
            $items[] = [
                'label' => $context->text('dashboard_attention_errors'),
                'value' => (int)dbcount('(error_id)', DB_ERRORS, 'error_status=0'),
                'href' => $context->adminUrl('errors.php') . $context->aidLink(),
                'tone' => 'danger',
            ];
        }

        if ($items === []) {
            return '<div class="admin-dashboard-widget-empty">'
                . $context->escape($context->text('dashboard_attention_clear')) . '</div>';
        }

        $html = '<div class="admin-dashboard-attention-list">';
        foreach ($items as $item) {
            $html .= '<a href="' . $context->escape($item['href']) . '"><span class="admin-dashboard-status admin-dashboard-status--'
                . $context->escape($item['tone']) . '"></span><span>' . $context->escape($item['label'])
                . '</span><strong>' . number_format($item['value']) . '</strong></a>';
        }

        return $html . '</div>';
    }
}
