<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/news_preview.php
| Author: Frederick Chan MC
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Infusions\News\Classes\News_Preview;

require_once __DIR__.'/../../../../maincore.php';
require_once THEMES.'templates/header.php';
require_once INFUSIONS.'news/templates/news.php';
require_once NEWS_CLASS.'/autoloader.php';
$news_preview = new News_Preview();
render_news_item($news_preview->getPreviewInfo());
require_once THEMES.'templates/footer.php';
