<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_articles.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'../../../../maincore.php';

if (file_exists(INFUSIONS.'rss_feeds_panel/locale/'.LOCALESET.'rss.php')) {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/'.LOCALESET.'rss.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/English/rss.php');
}

require_once INFUSIONS.'rss_feeds_panel/RSS.php';

if (db_exists(DB_ARTICLES) && db_exists(DB_ARTICLE_CATS)) {
	$result = dbquery("SELECT ta.*,tac.* FROM ".DB_ARTICLES." ta
	INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
	".(multilang_table("AR")?" WHERE article_cat_language='".LANGUAGE."'":"")."
	ORDER BY article_datestamp DESC LIMIT 0,10");
	
    header('Content-Type: application/rss+xml; charset='.$locale['charset']);

    $rss = new RSS('articles', $settings['sitename'].' - '.$locale['rss_articles'].(multilang_table('AR') ? $locale['rss_in'].LANGUAGE : ''));

    if (dbrows($result) != 0) {
        while ($data = dbarray($result)) {
            $rss->AddItem($data['article_subject'], $settings['siteurl'].'articles.php?article_id='.$data['article_id'], $data['article_snippet']);
        }
    } else {
        $rss->AddItem($settings['sitename'].' - '.$locale['rss_articles'], $settings['siteurl'], $locale['rss_nodata']);
    }

    $rss->Write();
}
