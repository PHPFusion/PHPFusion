<?php

namespace PHPFusion\Administration\Dashboard\Widgets;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;

final class ContentWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $sources = [
            ['flag' => 'NEWS_EXISTS', 'table' => DB_PREFIX . 'news', 'column' => 'news_id', 'label' => 'dashboard_content_news', 'right' => 'N'],
            ['flag' => 'ARTICLES_EXISTS', 'table' => DB_PREFIX . 'articles', 'column' => 'article_id', 'label' => 'dashboard_content_articles', 'right' => 'A'],
            ['flag' => 'BLOG_EXISTS', 'table' => DB_PREFIX . 'blog', 'column' => 'blog_id', 'label' => 'dashboard_content_blog', 'right' => 'BLOG'],
            ['flag' => 'DOWNLOADS_EXISTS', 'table' => DB_PREFIX . 'downloads', 'column' => 'download_id', 'label' => 'dashboard_content_downloads', 'right' => 'D'],
            ['flag' => 'GALLERY_EXISTS', 'table' => DB_PREFIX . 'photos', 'column' => 'photo_id', 'label' => 'dashboard_content_photos', 'right' => 'PH'],
            ['flag' => 'FORUM_EXISTS', 'table' => DB_PREFIX . 'forum_threads', 'column' => 'thread_id', 'label' => 'dashboard_content_threads', 'right' => 'F'],
            ['flag' => 'WEBLINKS_EXISTS', 'table' => DB_PREFIX . 'weblinks', 'column' => 'weblink_id', 'label' => 'dashboard_content_weblinks', 'right' => 'W'],
            ['flag' => '', 'table' => DB_CUSTOM_PAGES, 'column' => 'page_id', 'label' => 'dashboard_content_pages', 'right' => 'CP'],
        ];

        $items = [];
        $total = 0;
        foreach ($sources as $source) {
            if (($source['flag'] !== '' && !defined($source['flag'])) || !checkrights($source['right'])) {
                continue;
            }
            $table = $source['table'];
            if (!db_exists($table, FALSE)) {
                continue;
            }
            $count = (int)dbcount('(' . $source['column'] . ')', $table);
            $total += $count;
            $items[] = [$context->text($source['label']), $count];
        }

        if ($items === []) {
            return '<div class="admin-dashboard-widget-empty">'
                . $context->escape($context->text('dashboard_content_empty')) . '</div>';
        }

        $html = '<div class="admin-dashboard-primary-metric"><strong>' . number_format($total)
            . '</strong><span>' . $context->escape($context->text('dashboard_content_total')) . '</span></div>';
        $html .= '<dl class="admin-dashboard-metric-grid">';
        foreach ($items as [$label, $count]) {
            $html .= '<div><dt>' . $context->escape($label) . '</dt><dd>' . number_format($count) . '</dd></div>';
        }

        return $html . '</dl>';
    }
}
