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
require_once __DIR__.'/../../../../maincore.php';
require_once THEMES.'templates/header.php';
require_once INFUSIONS.'news/templates/news.php';
require_once NEWS_CLASS.'/autoloader.php';
render_news_item(\PHPFusion\News\News_Preview::get_PreviewInfo());
require_once THEMES.'templates/footer.php';
