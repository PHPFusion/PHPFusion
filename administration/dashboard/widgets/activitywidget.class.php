<?php

namespace PHPFusion\Administration\Dashboard\Widgets;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;

final class ActivityWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $activity = [];
        if (checkrights('C') && db_exists(DB_COMMENTS, FALSE)) {
            $result = dbquery('SELECT c.comment_datestamp AS datestamp, u.user_name FROM ' . DB_COMMENTS
                . ' c LEFT JOIN ' . DB_USERS . ' u ON u.user_id=c.comment_name ORDER BY c.comment_datestamp DESC LIMIT 5');
            while ($row = dbarray($result)) {
                $activity[] = $this->item($context->text('dashboard_activity_comment'), $row, 'comment');
            }
        }
        if (checkrights('C') && db_exists(DB_RATINGS, FALSE)) {
            $result = dbquery('SELECT r.rating_datestamp AS datestamp, u.user_name FROM ' . DB_RATINGS
                . ' r LEFT JOIN ' . DB_USERS . ' u ON u.user_id=r.rating_user ORDER BY r.rating_datestamp DESC LIMIT 5');
            while ($row = dbarray($result)) {
                $activity[] = $this->item($context->text('dashboard_activity_rating'), $row, 'star');
            }
        }
        if (checkrights('SU') && db_exists(DB_SUBMISSIONS, FALSE)) {
            $result = dbquery('SELECT s.submit_datestamp AS datestamp, u.user_name FROM ' . DB_SUBMISSIONS
                . ' s LEFT JOIN ' . DB_USERS . ' u ON u.user_id=s.submit_user ORDER BY s.submit_datestamp DESC LIMIT 5');
            while ($row = dbarray($result)) {
                $activity[] = $this->item($context->text('dashboard_activity_submission'), $row, 'submit');
            }
        }

        usort($activity, static fn(array $left, array $right): int => $right['datestamp'] <=> $left['datestamp']);
        $activity = array_slice($activity, 0, 8);
        if ($activity === []) {
            return '<div class="admin-dashboard-widget-empty">'
                . $context->escape($context->text('dashboard_activity_empty')) . '</div>';
        }

        $html = '<ol class="admin-dashboard-activity-list">';
        foreach ($activity as $item) {
            $html .= '<li><span class="admin-dashboard-activity-type" data-activity-type="'
                . $context->escape($item['type']) . '" aria-hidden="true"></span>'
                . '<span class="admin-dashboard-activity-copy"><strong>' . $context->escape($item['label'])
                . '</strong><small>' . $context->escape($item['user']) . '</small></span><time datetime="'
                . date('c', $item['datestamp']) . '">' . $context->escape(timer($item['datestamp'])) . '</time></li>';
        }

        return $html . '</ol>';
    }

    private function item(string $label, array $row, string $type): array
    {
        return [
            'label' => $label,
            'type' => $type,
            'user' => trim((string)($row['user_name'] ?? '')),
            'datestamp' => (int)($row['datestamp'] ?? 0),
        ];
    }
}
