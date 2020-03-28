<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
| Author: PHP-Fusion Development Team
| Co Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Infusions\News\Classes\News;
use PHPFusion\OpenGraphNews;
require_once __DIR__.'/../../maincore.php';
if (!defined('NEWS_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES.'templates/header.php';
require_once INFUSIONS."news/templates/news.php";

if (check_get('readmore')) {
    if ($readmore = get('readmore', FILTER_VALIDATE_INT)) {
        $info = $this->set_NewsItemInfo($readmore);
        render_news_item($info);
        OpenGraphNews::ogNews($readmore);
    } else {
        redirect(INFUSIONS.'news/news.php');
    }
} else if (check_get('cat_id')) {
    // Category Result
    if ($cat_id = get('cat_id', FILTER_VALIDATE_INT)) {
        $info = $this->set_NewsCatInfo($cat_id);
        display_main_news($info);
        OpenGraphNews::ogNewsCat($cat_id);
    } else {
        redirect(INFUSIONS.'news/news.php');
    }
} else {
    // All Results
    $news = new News();
    display_main_news($news->getNewsInfo());
}

require_once THEMES.'templates/footer.php';
