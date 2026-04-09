<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: functions.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

function get_wiki_query(array $filters = []) {
    return "SELECT w.*, wc.*, u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_joined
        FROM ".DB_WIKI." AS w
        LEFT JOIN ".DB_USERS." AS u ON w.wiki_user=u.user_id
        LEFT JOIN ".DB_WIKI_CATS." AS wc ON w.wiki_cat=wc.wiki_cat_id
        ".(multilang_table('WIKI') ? "WHERE ".in_group('w.wiki_language', LANGUAGE)." AND ".in_group('wc.wiki_cat_language', LANGUAGE)." AND " : 'WHERE ').groupaccess('w.wiki_access')."
        AND w.wiki_status=1 AND wc.wiki_cat_status=1 AND ".groupaccess('wc.wiki_cat_access')."
        ".(!empty($filters['condition']) ? ' AND '.$filters['condition'] : '')."
        GROUP BY w.wiki_id
        ".(!empty($filters['order']) ? 'ORDER BY '.$filters['order'] : '')."
        ".(!empty($filters['limit']) ? 'LIMIT '.$filters['limit'] : '')."
    ";
}

function validate_wiki($id) {
    if (isnum($id)) {
        if ($id < 1) {
            return 1;
        } else {
            return dbcount("('wiki_id')", DB_WIKI, "wiki_id='".intval($id)."'");
        }
    }

    return FALSE;
}

function validate_wiki_cat($id) {
    if (isnum($id)) {
        if ($id < 1) {
            return 1;
        } else {
            return dbcount("('wiki_cat_id')", DB_WIKI_CATS, "wiki_cat_id='".intval($id)."'");
        }
    }

    return FALSE;
}

function parse_wiki_text($text, $markdown) {
    if (!defined('PRISMJS')) {
        define('PRISMJS', TRUE);
        add_to_head('<link rel="stylesheet" href="'.INCLUDES.'bbcodes/code/prism.css">');
        add_to_footer('<script src="'.INCLUDES.'bbcodes/code/prism.js"></script>');
    }

    $text = html_entity_decode(html_entity_decode($text, ENT_QUOTES, fusion_get_locale('charset')));

    if ($markdown == TRUE) {
        require_once WIKI.'includes/Parsedown.php';
        $parsedown = new Parsedown;

        $text = stripslashes($text);
        $text = $parsedown->text($text);
        $text = htmlspecialchars_decode($text);
    } else {
        $text = stripslashes($text);
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8', FALSE);
        $text = parseubb($text, '', FALSE);
        $text = nl2br($text);
    }

    return $text;
}
