<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_panel/news_panel.php
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."/../../maincore.php";
if (!db_exists(DB_NEWS)) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES."templates/header.php";
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."news/infusion_db.php";
require_once NEWS_CLASS.'autoloader.php';
require_once INFUSIONS."news/templates/news.php";
$news_locale = fusion_get_locale(NEWS_LOCALE);
// Fetch the News Item through the News Server
$method = \PHPFusion\News\NewsServer::news();
$info = $method->get_NewsItem();
// Create a master template
$html = \PHPFusion\Template::getInstance('news_panel');
$html->set_template(INFUSIONS.'news_panel/templates/news_panel.html');

if (!empty($info['news_items'])) {
    foreach($info['news_items'] as $news_id => $newsData) {
        // Create a child item template
        $chtml =  \PHPFusion\Template::getInstance('news_panel_item');
        $chtml->set_template(INFUSIONS.'news_panel/templates/news_panel_item.html');
        $chtml->set_tag('news_title', $newsData['news_subject']);
        $chtml->set_tag('news_snippet', $newsData['news_news']);
        $chtml->set_tag('news_link', $newsData['news_link']);
        $chtml->set_tag('news_image_url', $newsData['news_image_optimized']);
        // Administration Actions
        if (iADMIN && checkrights('N')) {
            if (!empty($newsData['news_admin_actions']['edit']['link']) &&
                !empty($newsData['news_admin_actions']['edit']['title']) &&
                !empty($newsData['news_admin_actions']['delete']['link']) &&
                !empty($newsData['news_admin_actions']['delete']['title'])
            ) {
                $admin_options = array(
                    'edit_link' => $newsData['news_admin_actions']['edit']['link'],
                    'edit_title' => $newsData['news_admin_actions']['edit']['title'],
                    'delete_link' => $newsData['news_admin_actions']['delete']['link'],
                    'delete_title' => $newsData['news_admin_actions']['delete']['title']
                );
                $chtml->set_block('admin_actions', $admin_options);
            }
        }
        $current_child_html = $chtml->get_output();
        $html->set_block('news_item_block', ['news_block'=>$current_child_html]);
    }
}
echo $html->get_output();
require_once THEMES."templates/footer.php";