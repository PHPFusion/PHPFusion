<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 9.0.upgrade.inc
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
require_once dirname(__FILE__).'/upgrade_functions_include.php';

ini_set('display_errors', FALSE);
// [9.0][insertdbrow][12]
/*
 * Upgrade Database Collation
 * Force the database to UTF-8 because we'll convert to it
 */
dbquery("SET NAMES 'utf8'");
// Normalize the table (Heavy Process and May Time Out the Server)
$result = dbquery("SHOW TABLES");
while ($row = dbarray($result)) {
    foreach ($row as $key => $table) {
        $inf_altertable[] = $table." CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        $result2 = dbquery("SHOW COLUMNS FROM ".$table);
        // We must change all data like find/replace in columns of broken chars, this may differ for each locales.
        // Please help to complete this list if you know what´s missing with your locale set
        while ($column = dbarray($result2)) {
            if (column_exists($table, $column['Field'], FALSE)) {
                // Fix on user_offset column
                // Execution time can be more than 30 seconds ---- this is too slow.
                /* if ($column['Field'] !== 'user_offset') {
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'ÃŸ','ß')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ã¤','ä')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ã¼','ü')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ã¶','ö')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ã„','Ä')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ãœ','Ü')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ã–','Ö')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'â‚¬','€')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."` ,'Ã¥','Å')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'ð', 'ğ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'ý', 'ı')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'þ', 'ş')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ð', 'Ğ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ý', 'İ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Þ', 'Ş')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã‰','É')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'â€œ','\"')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'â€','\"')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã‡','Ç')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ãƒ','Ã')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã¥','Å')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã ','À')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ãº','ú')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'â€¢','-')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã˜','Ø')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ãµ','õ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã­','í')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã¢','â')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã£','ã')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ãª','ê')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã¡','á')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã©','é')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã³','ó')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'â€“','–')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã§','ç')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Âª','ª')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Âº','º')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, 'Ã ','à')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ccedil;','ç')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&atilde;','ã')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&aacute;','á')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&acirc;','â')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&eacute;','é')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&iacute;','í')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&otilde;','õ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&uacute;','ú')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ccedil;','ç')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Aacute;','Á')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Acirc;','Â')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Eacute;','É')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Iacute;','Í')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Otilde;','Õ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Uacute;','Ú')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Ccedil;','Ç')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Atilde;','Ã')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Agrave;','À')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Ecirc;','Ê')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Oacute;','Ó')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Ocirc;','Ô')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Uuml;','Ü')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&atilde;','ã')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&agrave;','à')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ecirc;','ê')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&oacute;','ó')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ocirc;','ô')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&uuml;','ü')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&amp;','&')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&gt;','>')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&lt;','<')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&circ;','ˆ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&tilde;','˜')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&uml;','¨')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&cute;','´')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&cedil;','¸')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&quot;','\"')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ldquo;','“')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&rdquo;','”')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&lsquo;','‘')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&rsquo;','’')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&lsaquo;','‹')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&rsaquo;','›')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&laquo;','«')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&raquo;','»')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ordm;','º')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ordf;','ª')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ndash;','–')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&mdash;','—')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&macr;','¯')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&hellip;','…')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&brvbar;','¦')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&bull;','•')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&para;','¶')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sect;','§')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sup1;','¹')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sup2;','²')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sup3;','³')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&frac12;','½')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&frac14;','¼')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&frac34;','¾')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&#8539;','⅛')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&#8540;','⅜')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&#8541;','⅝')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&#8542;','⅞')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&gt;','>')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&lt;','<')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&plusmn;','±')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&minus;','−')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&times;','×')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&divide;','÷')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&lowast;','∗')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&frasl;','⁄')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&permil;','‰')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&int;','∫')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sum;','∑')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&prod;','∏')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&radic;','√')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&infin;','∞')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&asymp;','≈')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&cong;','≅')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&prop;','∝')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&equiv;','≡')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ne;','≠')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&le;','≤')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ge;','≥')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&there4;','∴')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sdot;','⋅')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&middot;','·')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&part;','∂')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&image;','ℑ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&real;','ℜ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&prime;','′')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Prime;','″')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&deg;','°')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&ang;','∠')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&perp;','⊥')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&nabla;','∇')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&oplus;','⊕')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&otimes;','⊗')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&alefsym;','ℵ')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&oslash;','ø')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&Oslash;','Ø')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&isin;','∈')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&notin;','∉')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&cap;','∩')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&cup;','∪')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sub;','⊂')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sup;','⊃')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&sube;','⊆')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&supe;','⊇')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&exist;','∃')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&forall;','∀')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&empty;','∅')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&not;','¬')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&and;','∧')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&or;','∨')";
                    $inf_updatedbrow[] = $table." SET ".$column['Field']." = REPLACE(`".$column['Field']."`, '&crarr;','↵')";
                } */
            }
        }
    }
}

ini_set('display_errors', 'On');

/**
 * Package for All version of PHP-Fusion Below 9.00 (RC5)
 */
define('DB_ARTICLES', DB_PREFIX."articles");
define('DB_DOWNLOADS', DB_PREFIX."downloads");
define('DB_FAQS', DB_PREFIX."faqs");
define('DB_FORUMS', DB_PREFIX."forums");
define('DB_PHOTOS', DB_PREFIX."photos");
define('DB_PHOTO_ALBUMS', DB_PREFIX."photo_albums");
define('DB_NEWS', DB_PREFIX."news");
define('DB_WEBLINKS', DB_PREFIX."weblinks");
include LOCALE.LOCALESET."setup.php";
$settings = fusion_get_settings();

$del_table = array();
/*
if (db_exists(DB_PREFIX.'articles')) {
    if (empty(dbcount("(article_id)", DB_ARTICLES))) {
        $result = dbquery("DROP TABLE ".DB_ARTICLES);
        $result = dbquery("DROP TABLE ".DB_PREFIX.'article_cats');
        $del_table[] = 'articles.php';
        $del_table[] = 'article_cats.php';
        $del_array[] = 'articles.php';
        $del_array[] = 'submit.php?stype=a';
    }
}

if (db_exists(DB_PREFIX.'downloads')) {
    if (empty(dbcount("(download_id)", DB_DOWNLOADS))) {
        $result = dbquery("DROP TABLE ".DB_DOWNLOADS);
        $result = dbquery("DROP TABLE ".DB_PREFIX.'download_cats');
        $del_table[] = 'downloads.php';
        $del_table[] = 'download_cats';
        $del_table[] = 'settings_dl.php';
        $del_array[] = 'downloads.php';
        $del_array[] = 'submit.php?stype=d';
    }
}

if (db_exists(DB_PREFIX.'faqs')) {
    if (empty(dbcount("(faq_id)", DB_FAQS))) {
        $result = dbquery("DROP TABLE ".DB_FAQS);
        $result = dbquery("DROP TABLE ".DB_PREFIX.'faq_cats');
        $del_table[] = 'faq.php';
        $del_table[] = 'faq_cats.php';
        $del_array[] = 'faq.php';
    }
}

if (db_exists(DB_PREFIX.'forums')) {
    if (empty(dbcount("(forum_id)", DB_FORUMS))) {
        $result = dbquery("DROP TABLE ".DB_FORUMS);
        $result = dbquery("DROP TABLE ".DB_PREFIX.'forum_attachments');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'forum_polls');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'forum_poll_options');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'forum_poll_voters');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'forum_ranks');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'posts');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'threads');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'thread_notify');
        $del_table[] = 'forums.php';
        $del_table[] = 'forum_ranks.php';
        $del_table[] = 'forum/index.php';
        $del_array[] = 'forum/index.php';
    }
}

if (db_exists(DB_PREFIX.'photos')) {
    if (empty(dbcount("(photo_id)", DB_PHOTOS))) {
        $result = dbquery("DROP TABLE ".DB_PHOTOS);
    }
}

if (db_exists(DB_PREFIX.'photo_albums')) {
    if (empty(dbcount("(album_id)", DB_PHOTO_ALBUMS))) {
        $result = dbquery("DROP TABLE ".DB_PHOTO_ALBUMS);
        $del_table[] = 'photoalbums.php';
        $del_table[] = 'photogallery.php';
        $del_array[] = 'photogallery.php';
        $del_array[] = 'submit.php?stype=p';
    }
}

if (db_exists(DB_PREFIX.'news')) {
    if (empty(dbcount("(news_id)", DB_NEWS))) {
        $result = dbquery("DROP TABLE ".DB_NEWS);
        $result = dbquery("DROP TABLE ".DB_PREFIX.'news_cats');
        $del_table[] = 'news.php';
        $del_table[] = 'news_cats.php';
        $del_table[] = 'settings_news.php';
        $del_array[] = 'submit.php?stype=n';
    }
}

if (db_exists(DB_WEBLINKS)) {
    if (empty(dbcount("(weblink_id)", DB_WEBLINKS))) {
        $result = dbquery("DROP TABLE ".DB_WEBLINKS);
        $result = dbquery("DROP TABLE ".DB_PREFIX.'weblink_cats');
        $del_table[] = 'weblinks.php';
        $del_table[] = 'weblink_cats.php';
        $del_array[] = 'weblinks.php';
        $del_array[] = 'submit.php?stype=l';
    }
}

if (db_exists(DB_PREFIX.'polls')) {
    if (empty(dbcount("(poll_id)", DB_PREFIX.'polls'))) {
        $result = dbquery("DROP TABLE ".DB_PREFIX.'polls');
        $result = dbquery("DROP TABLE ".DB_PREFIX.'poll_votes');
        $del_table[] = 'polls.php';
    }
}
*/

if (db_exists(DB_PREFIX.'users')) {
    // Modify All Users Level > 0
    $result = dbquery("SELECT user_id, user_level FROM ".DB_USERS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if ($data['user_level']) { // will omit 0
                dbquery("UPDATE ".DB_USERS." SET user_level='-".$data['user_level']."' WHERE user_id='".$data['user_id']."'");
            }
        }
    }

    // Remove user_offset which had been replaced with user_timezone
    if (column_exists(DB_USERS, 'user_offset')) {
        $inf_altertable[] = DB_USERS." DROP COLUMN user_offset";
    }

}

if (db_exists(DB_EMAIL_TEMPLATES)) {
    // Upgrade Email templates
    $check_array = [
        'PM'      => DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('PM', 'html', '0', '".$locale['setup_3801']."', '".$locale['setup_3802']."', '".$locale['setup_3803']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."'",
        'POST'    => DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('POST', 'html', '0', '".$locale['setup_3804']."', '".$locale['setup_3805']."', '".$locale['setup_3806']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."'",
        'CONTACT' => DB_EMAIL_TEMPLATES." (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('CONTACT', 'html', '0', '".$locale['setup_3807']."', '".$locale['setup_3808']."', '".$locale['setup_3809']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."'"
    ];
    foreach ($check_array as $key => $value) {
        if (!dbcount("(template_key)", DB_EMAIL_TEMPLATES, "template_key='$key'")) {
            $inf_insertdbrow[] = $value;
        }
    }
}

// Perform data tally from 7.02.07
if (db_exists(DB_MESSAGES)) {

    $result = dbquery("SELECT * FROM ".DB_MESSAGES);
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $inf_updatedbrow[] = DB_MESSAGES." SET message_user = '".$data['message_to']."' WHERE message_id = '".$data['message_id']."'";
        }
    }

    // Remove dropped rights, these settings have been moved to tabs and follow the Infusions rights
    $result = dbquery("SELECT user_id, user_rights, user_level FROM ".DB_USERS);
    while ($data = dbarray($result)) {
        $new_rights = str_replace(".S13", "", $data['user_rights']);
        $new_rights = str_replace(".AC", "", $new_rights);
        $new_rights = str_replace(".DC", "", $new_rights);
        $new_rights = str_replace(".NC", "", $new_rights);
        $new_rights = str_replace(".WC", "", $new_rights);
        $new_rights = str_replace(".S5", "", $new_rights);
        $new_rights = str_replace(".S8", "", $new_rights);
        $new_rights = str_replace(".S11", "", $new_rights);
        $new_rights = str_replace(".SU", "", $new_rights);

        if ($data['user_level'] == USER_LEVEL_SUPER_ADMIN) {
            $check_array = [
                'PL'   => TRUE,
                'MI'   => TRUE,
                'LANG' => TRUE,
                'TS'   => TRUE,
                'MAIL' => TRUE,
            ];
            foreach ($check_array as $key => $value) {
                if (!stristr($new_rights, $key)) {
                    $new_rights = $new_rights.".$key";
                }
            }
        }

        $inf_updatedbrow[] = DB_USERS." SET user_rights='".$new_rights."', user_theme ='Default' WHERE user_id='".$data['user_id']."'";
    }
}

if (db_exists(DB_SETTINGS)) {
    $update_settings_tbl = [
        'theme'              => 'FusionTheme',
        'rendertime_enabled' => '0',
        'timeoffset'         => 'Europe/London',
        'serveroffset'       => 'Europe/London',
        'opening_page'       => 'home.php',
        'captcha'            => 'securimage3',
    ];
    foreach ($update_settings_tbl as $key => $value) {
        if (isset($settings[$key])) {
            $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";;
        }
    }

    // Insert rows to settings table
    $enabled_languages = implode('.', fusion_get_detected_languages());
    $insert_settings_tbl = [
        'enabled_languages' => $enabled_languages,
        'site_seo'          => 0,
        'normalize_seo'     => 0,
        'debug_seo'         => 0,
        'login_method'      => 0,
        'mime_check'        => 0,
        'exclude_aupper'    => '',
        'exclude_blower'    => '',
        'exclude_user1'     => '',
        'exclude_user2'     => '',
        'exclude_user3'     => '',
        'exclude_user4'     => '',
        'admin_theme'       => 'Artemis',
        'bootstrap'         => 1,
        'entypo'            => 1,
        'fontawesome'       => 1,
        'comments_jquery'   => 1,
        'allow_php_exe'     => 0,
        'privacy_policy'    => '',
        'create_og_tags'    => 1,
        'index_url_bbcode'  => 1,
        'index_url_userweb' => 1,
        'logoposition_xs'   => 'logo-xs-left',
        'logoposition_sm'   => 'logo-sm-left',
        'logoposition_md'   => 'logo-md-left',
        'logoposition_lg'   => 'logo-lg-left',
        'UserName_ban'       => '',
    ];
    foreach ($insert_settings_tbl as $key => $value) {
        if (!isset($settings[$key])) {
            $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
        }
    }
}

// Insert Multi Language rights and status
if (db_exists(DB_LANGUAGE_TABLES)) {
    $modules = [
        'AR' => db_exists(DB_ARTICLES),
        'CP' => TRUE,
        'BL' => db_exists(DB_PREFIX.'blog'),
        'DL' => db_exists(DB_DOWNLOADS),
        'FQ' => db_exists(DB_FAQS),
        'FO' => db_exists(DB_FORUMS),
        'FR' => db_exists(DB_FORUMS) ? TRUE : FALSE,
        'NS' => db_exists(DB_NEWS),
        'PG' => db_exists(DB_PHOTOS),
        'PO' => db_exists(DB_PREFIX.'polls'),
        'ET' => TRUE,
        'WL' => db_exists(DB_WEBLINKS),
        'SL' => true,
        'PN' => true,
        'PA' => db_exists(DB_PHOTOS) ? TRUE : FALSE,
    ];
    $mlt_array = [
        'AR' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('AR', '".$locale['setup_3200']."', '1')",  //Articles
        'CP' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('CP', '".$locale['setup_3201']."', '1')",  //Custom Pages
        'BL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('BL', '".$locale['setup_3213']."', '1')",  //Blog
        'DL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('DL', '".$locale['setup_3202']."', '1')",  //Downloads
        'FQ' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('FQ', '".$locale['setup_3203']."', '1')",  //Faq
        'FO' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('FO', '".$locale['setup_3204']."', '1')",  //Forum
        'FR' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('FR', '".$locale['setup_3212']."', '1')",  //Forum ranks
        'NS' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('NS', '".$locale['setup_3205']."', '1')",  //News
        'PG' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('PG', '".$locale['setup_3206']."', '1')",  //Gallery
        'PO' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('PO', '".$locale['setup_3207']."', '1')",  //Polls
        'ET' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('ET', '".$locale['setup_3208']."', '1')",  //Email Template
        'WL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('WL', '".$locale['setup_3209']."', '1')",  //Weblinks
        'SL' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('SL', '".$locale['setup_3210']."', '1')",  //Site Links
        'PN' => DB_LANGUAGE_TABLES." (mlt_rights, mlt_title, mlt_status) VALUES ('PN', '".$locale['setup_3211']."', '1')",  //Panels
    ];
    foreach ($mlt_array as $key => $value) {
        $count = dbcount("('mlt_rights')", DB_LANGUAGE_TABLES, "mlt_rights='$key'");
        $modul = $modules[$key];
        if (empty($count) && !empty($modul)) {
            $inf_insertdbrow[] = $value;
        }
    }
}

if (db_exists(DB_ADMIN)) {
    // Remove the deprecated administration links
    $del_table = [
        'settings_photo.php',
        'settings_forum.php',
        'user_field_cats.php',
        'settings_dl.php',
        'settings_ipp.php',
        'submissions.php'
    ];
    foreach ($del_table as $link) {
        dbquery("DELETE FROM ".DB_ADMIN." WHERE admin_link=:url", [':url' => $link]);
    }

    // Add new core administration links
    $check_array = [
        'LANG' => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('LANG', 'language.png', '".$locale['setup_3051']."', 'settings_languages.php', '4')",
        'PL'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('PL', 'permalink.png', '".$locale['setup_3052']."', 'permalink.php', '3')",
        'S3'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('S3', 'theme_settings.png', '".$locale['setup_3058']."', 'settings_theme.php', '4')",
        'TS'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('TS', 'theme.png', '".$locale['setup_3056']."', 'theme.php', '3')",
        'MI'   => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MI', 'migration.png', '".$locale['setup_3057']."', 'migrate.php', '2')",
        'MAIL' => DB_ADMIN." (admin_rights, admin_image, admin_title, admin_link, admin_page) VALUES ('MAIL', 'email.png', '".$locale['setup_3800']."', 'email.php', '3')",
    ];
    foreach ($check_array as $key => $value) {
        $count = dbcount("('admin_rights')", DB_ADMIN, "admin_rights='$key'");
        if (empty($count)) {
            $inf_insertdbrow[] = $value;
        }
    }

    // Update new infusion administration icons
    db_exists(DB_ARTICLES) ? $new_icon_array = ['A' => '../infusions/articles/articles.svg'] : '';
    db_exists(DB_DOWNLOADS) ? $new_icon_array = ['D' => '../infusions/download/download.svg'] : '';
    db_exists(DB_FAQS) ? $new_icon_array = ['FQ' => '../infusions/faq/faq.png'] : '';
    db_exists(DB_FORUMS) ? $new_icon_array = ['F' => '../infusions/forum/forums.svg'] : '';
    db_exists(DB_PHOTO_ALBUMS) ? $new_icon_array = ['PH' => '../infusions/gallery/gallery.svg'] : '';
    db_exists(DB_NEWS) ? $new_icon_array = ['N' => '../infusions/news/news.svg'] : '';
    db_exists(DB_WEBLINKS) ? $new_icon_array = ['W' => '../infusions/weblinks/weblink.svg'] : '';
    db_exists(DB_PREFIX.'polls') ? $new_icon_array = ['PO' => '../infusions/member_poll_panel/polls.png'] : '';
    db_exists(DB_PREFIX.'shoutbox') ? $new_icon_array = ['S' => '../infusions/shoutbox/shout.png'] : '';

    // Update new administration icons
    $new_icon_array = [
        'APWR' => 'adminpass.png',
        'AD'   => 'administrator.png',
        'SB'   => 'banner.png',
        'BB'   => 'bbcodes.png',
        'B'    => 'blacklist.png',
        'CP'   => 'c-pages.png',
        'DB'   => 'db_backup.png',
        'MAIL' => 'email.png',
        'ERRO' => 'errors.png',
        'IM'   => 'images.png',
        'I'    => 'infusions.png',
        'LANG' => 'language.png',
        'S1'   => 'settings.png',
        'M'    => 'members.png',
        'MI'   => 'migration.png',
        'S6'   => 'misc.png',
        'P'    => 'panels.png',
        'PL'   => 'permalink.png',
        'PI'   => 'phpinfo.png',
        'S7'   => 'pm.png',
        'S4'   => 'registration.png',
        'ROB'  => 'robots.png',
        'S12'  => 'security.png',
        'SL'   => 'sitelinks.png',
        'SM'   => 'smileys.png',
        'TS'   => 'theme.png',
        'S3'   => 'theme_settings.png',
        'S2'   => 'time.png',
        'U'    => 'upgrade.png',
        'UF'   => 'user_fields.png',
        'UG'   => 'user_groups.png',
        'UL'   => 'user_log.png',
        'S9'   => 'user_settings.png',
    ];
    foreach ($new_icon_array as $admin_rights => $icon_file) {
        $inf_updatedbrow[] = DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'";
    }
}

if (db_exists(DB_PREFIX.'site_links')) {

    $del_array[] = 'news_cats.php';
    if (!empty($del_array)) {
        foreach ($del_array as $key => $value) {
            $inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url ='$value'";
        }
    }
    // Sets all links to active
    if (column_exists(DB_SITE_LINKS, 'link_status', FALSE)) {
        $result = dbquery("SELECT link_id, link_visibility, link_status FROM ".DB_SITE_LINKS);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                if ($data['link_visibility']) {
                    $inf_updatedbrow[] = DB_SITE_LINKS." SET link_visibility ='-".$data['link_visibility']."', link_status ='1' WHERE link_id='".$data['link_id']."'";
                } else {
                    $inf_updatedbrow[] = DB_SITE_LINKS." SET link_status ='1' WHERE link_id='".$data['link_id']."'";

                }
            }
        }
    }
}

if (db_exists(DB_PREFIX.'panels')) {
    // Update panel table column
    if (!column_exists(DB_PANELS, 'panel_status', FALSE)) {
        $inf_altertable[] = DB_PANELS." ADD panel_status tinyint(1) NOT NULL DEFAULT '1' AFTER panel_display";
    }
    // Alter all panel settings
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='1'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='2'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='3'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='4'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='5'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_status='1', panel_restriction='3' WHERE panel_side='6'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_restriction='3', panel_status='1' WHERE panel_filename='forum_threads_list_panel'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_display='1', panel_restriction='3', panel_status='1' WHERE panel_filename='member_poll_panel'";
    $inf_updatedbrow[] = DB_PANELS." SET panel_languages='".$settings['locale']."'";

}

if (db_exists(DB_USER_FIELD_CATS)) {
    // Dump old categories
    //dbquery("TRUNCATE TABLE `".DB_PREFIX."user_field_cats`");
    if (!dbcount("(field_cat_name)", DB_USER_FIELD_CATS, "field_cat_name='".$locale['setup_3640']."'")) {
        $field_cat_id = dbresult(dbquery("SELECT MAX(field_cat_id) 'field_rows' FROM ".DB_USER_FIELD_CATS), 0) + 1;
        // Install a new default set of UF cats
        $ufc_sql = DB_USER_FIELD_CATS." (field_cat_id, field_cat_name, field_parent, field_cat_db, field_cat_index, field_cat_class, field_cat_order) VALUES ";
        $ufc_sql .= implode(",\n", array(
            "('".($field_cat_id++)."', '".$locale['setup_3640']."', 0, '', '', '', '1')",
            "('".($field_cat_id++)."', '".$locale['setup_3641']."', 1, '', '', '', '1')",
            "('".($field_cat_id++)."', '".$locale['setup_3642']."', 1, '', '', '', '2')",
            "('".($field_cat_id++)."', '".$locale['setup_3643']."', 1, '', '', '', '3')",
            "('".($field_cat_id++)."', '".$locale['setup_3644']."', 1, '', '', '', '4')",
        ));
        $inf_insertdbrow[] = $ufc_sql;
    }
}

if (db_exists(DB_USER_FIELDS)) {
// Install User Fields
    $previous_install = array();
    $uf_to_install = array();
    $result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $previous_install[$data['field_name']] = TRUE;
        }
    }
    if (!isset($previous_install['user_location'])) {
        $uf_to_install[] = "('user_location', '".$locale['uf_location']."', '3', 'file', '0', '1', '', '', '', '')";
    }
    if (!isset($previous_install['user_birthdate'])) {
        $uf_to_install[] = "('user_birthdate', '".$locale['uf_birthdate']."', '3', 'file', '0', '2', '1900-01-01', '', '', '')";
    }
    if (!isset($previous_install['user_skype'])) {
        $uf_to_install[] = "('user_skype', '".$locale['uf_skype']."', '2', 'file', '0', '1', '', '', '', '')";
    }
    if (!isset($previous_install['user_aim'])) {
        $uf_to_install[] = "('user_aim', '".$locale['uf_aim']."', '2', 'file', '0', '2', '', '', '', '')";
    }
    if (!isset($previous_install['user_icq'])) {
        $uf_to_install[] = "('user_icq', '".$locale['uf_icq']."', '2', 'file', '0', '3', '', '', '', '')";
    }
    if (!isset($previous_install['user_yahoo'])) {
        $uf_to_install[] = "('user_yahoo', '".$locale['uf_yahoo']."', '2', 'file', '0', '5', '', '', '', '')";
    }
    if (!isset($previous_install['user_web'])) {
        $uf_to_install[] = "('user_web', '".$locale['uf_web']."', '2', 'file', '0', '6', '', '', '', '')";
    }
    if (!isset($previous_install['user_theme'])) {
        $uf_to_install[] = "('user_theme', '".$locale['uf_theme']."', '4', 'file', '0', '2', '', '', '', '')";
    }
    if (!isset($previous_install['user_sig'])) {
        $uf_to_install[] = "('user_sig', '".$locale['uf_sig']."', '4', 'file', '0', '3', '', '', '', '')";
    }
    if (!empty($uf_to_install)) {
        $uf_sql = DB_USER_FIELDS." (field_name, field_title, field_cat, field_type, field_required, field_order, field_default, field_options, field_error, field_config) VALUES ";
        $uf_sql .= implode(",\n", $uf_to_install);
        $inf_insertdbrow[] = $uf_sql;
    }
}
if (db_exists(DB_INFUSIONS)) {
// The infusions table should not exist in any version prior to this:
    $check_infusions = [
        DB_ARTICLES          => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3002']."', 'articles', '0')",
            'check'  => 'articles'
        ],
        DB_DOWNLOADS         => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3010']."', 'downloads', '0')",
            'check'  => 'downloads'
        ],
        DB_FAQS              => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3011']."', 'faq', '0')",
            'check'  => 'faq'
        ],
        DB_FORUMS            => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3012']."', 'forum', '0')",
            'check'  => 'forum'
        ],
        DB_PHOTOS            => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3020']."', 'gallery', '0')",
            'check'  => 'gallery'
        ],
        DB_NEWS              => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3018']."', 'news', '0')",
            'check'  => 'news'
        ],
        DB_WEBLINKS          => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3029']."', 'weblinks', '0')",
            'check'  => 'weblinks'
        ],
        DB_PREFIX."shoutbox" => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3316']."', 'shoutbox_panel', '0')",
            'check'  => 'shoutbox_panel'
        ],
        DB_PREFIX."polls"    => [
            'insert' => DB_INFUSIONS." (inf_title, inf_folder, inf_version) VALUES ('".$locale['setup_3407']."', 'member_poll_panel', '0')",
            'check'  => 'member_poll_panel'
        ]
    ];
    foreach ($check_infusions as $table_name => $value) {
        if (!dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='".$value['check']."'") && db_exists($table_name)) {
            $inf_insertdbrow[] = $value['insert'];
        }
    }
}

/*
 * Downloads Migrate
 */
// Move all download images
if (file_exists(BASEDIR."downloads/images/")) {
    $attachment_files = makefilelist(BASEDIR."downloads/images/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(BASEDIR."downloads/images/".$file, INFUSIONS."downloads/images/".$file);
    }
}
// Move all downloads
if (file_exists(BASEDIR."downloads/")) {
    $attachment_files = makefilelist(BASEDIR."downloads/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(BASEDIR."downloads/".$file, INFUSIONS."downloads/files/".$file);
    }
    // Remove the whole old dir including rouge files
    rrmdir(BASEDIR."downloads");
}

/**
 * Forums Migrate
 */
// Move all forum attachments
if (file_exists(BASEDIR."forum/attachments/")) {
    $attachment_files = makefilelist(BASEDIR."forum/attachments/", ".|..|index.php", TRUE);
    foreach ($attachment_files as $file) {
        rename(BASEDIR."forum/attachments/".$file, INFUSIONS."forum/attachments/".$file);
    }
    // Remove the whole old dir including rouge files
    rrmdir(BASEDIR."forum/attachments");
}

/**
 * Gallery Migrate
 */
// Move all photos
if (db_exists(DB_PHOTO_ALBUMS)) {
    $result = dbquery("SELECT * FROM ".DB_PHOTO_ALBUMS);

    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            if (file_exists(IMAGES."photoalbum/".$data['album_thumb'])) {
                // Rename the album thumb here
                rename(IMAGES."photoalbum/".$data['album_thumb'], INFUSIONS."gallery/photos/".$data['album_thumb']);
                // Call the album directory rename function here
                move_photos($data['album_id']);
            }
        }
    }
}
//Remove the whole old photoalbum dir including rouge files
rrmdir(IMAGES."photoalbum");
