<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: search.php
| Author: RobiNN
+--------------------------------------------------------*/
require_once __DIR__.'/../../../../maincore.php';

$result_msg = [];

$search_string = (string)filter_input(INPUT_GET, 'searchstring');

if (!empty($search_string)) {
    if (strlen($search_string) >= 2) {
        $search_string = form_sanitizer($search_string);

        $dbresult = dbquery("SELECT w.wiki_id, w.wiki_cat, w.wiki_parent, w.wiki_name, w.wiki_description, w.wiki_type, w.wiki_status, w.wiki_access, w.wiki_language, wc.wiki_cat_id, wc.wiki_cat_name, wc.wiki_cat_status, wc.wiki_cat_access, wc.wiki_cat_language
            FROM ".DB_WIKI." AS w
            LEFT JOIN ".DB_WIKI_CATS." AS wc ON w.wiki_cat=wc.wiki_cat_id
            ".(multilang_table('WIKI') ? "WHERE ".in_group('w.wiki_language', LANGUAGE)." AND ".in_group('wc.wiki_cat_language', LANGUAGE)." AND " : 'WHERE ').groupaccess('w.wiki_access')."
            AND w.wiki_status=1 AND wc.wiki_cat_status=1 AND ".groupaccess('wc.wiki_cat_access')."
            AND w.wiki_type = 'page' AND w.wiki_name LIKE '%".$search_string."%' OR w.wiki_description LIKE '%".$search_string."%'
        ");

        if (dbrows($dbresult)) {
            while ($wdata = dbarray($dbresult)) {
                $result_msg[] = [
                    'title'     => $wdata['wiki_name'],
                    'link'      => WIKI.'documentation.php?page_id='.$wdata['wiki_id'],
                    'cat_title' => $wdata['wiki_cat_name'],
                    'cat_link'  => WIKI.'index.php?cat_id='.$wdata['wiki_cat_id'],
                ];
            }
        } else {
            $result_msg['status'] = 'No documentation found';
        }
    } else {
        $result_msg['status'] = 'Search string is too short. The string must have at least 2 chars.';
    }
}

header('Content-Type: application/json');

echo json_encode($result_msg);
