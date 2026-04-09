<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki/index.php
| Author: RobiNN
+--------------------------------------------------------*/
require_once __DIR__.'/../../maincore.php';

if (!defined('WIKI_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';
require_once WIKI.'includes/functions.php';
require_once WIKI.'templates/wiki.php';

$locale = fusion_get_locale('', WIKI_LOCALE);
$aidlink = fusion_get_aidlink();

set_title($locale['wiki_title']);

use \PHPFusion\BreadCrumbs;

BreadCrumbs::getInstance()->addBreadCrumb(['link' => WIKI, 'title' => $locale['wiki_title']]);

add_to_footer('<script src="'.WIKI.'includes/ajax/scripts.min.js?v='.filemtime(WIKI.'includes/ajax/scripts.min.js').'"></script>');

$info = [
    'no_pages' => ''
];

if (isset($_GET['cat_id'])) {
    if (validate_wiki_cat($_GET['cat_id'])) {
        require_once WIKI.'includes/OpenGraphWiki.php';

        $data = dbarray(dbquery("SELECT wiki_cat_id, wiki_cat_name, wiki_cat_description FROM ".DB_WIKI_CATS." WHERE ".(multilang_column('WIKI') ? in_group('wiki_cat_language', LANGUAGE)." AND " : '')." wiki_cat_id=:wiki_cat_id", [':wiki_cat_id' => intval($_GET['cat_id'])]));
        add_to_title(': '.$data['wiki_cat_name']);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => WIKI.'index.php?cat_id='.intval($_GET['cat_id']), 'title' => $data['wiki_cat_name']]);
        $info['cat_name'] = $data['wiki_cat_name'];
        $info['description'] = nl2br(parse_textarea($data['wiki_cat_description']));

        $admin_link = [];
        if (iADMIN && checkrights('WIKI')) {
            $admin_link = [
                'edit'   => WIKI.'admin.php'.$aidlink.'&section=categories&ref=wiki_cat_form&action=edit&cat_id='.$data['wiki_cat_id'],
                'delete' => WIKI.'admin.php'.$aidlink.'&section=categories&ref=wiki_cat_form&action=delete&cat_id='.$data['wiki_cat_id']
            ];
        }

        $info['admin_link'] = $admin_link;

        OpenGraphWiki::ogWikiCat($_GET['cat_id']);

        $pages = dbquery(get_wiki_query(['condition' => "w.wiki_cat=:cat_id AND w.wiki_parent=0", 'order' => 'w.wiki_order']), [':cat_id' => intval($_GET['cat_id'])]);

        if (dbrows($pages) > 0) {
            while ($page = dbarray($pages)) {
                $info['pages'][] = $page;
            }
        } else {
            $info['no_pages'] = $locale['wiki_301'];
        }

    } else {
        redirect(WIKI);
    }
} else {
    $info += [
        'no_cats'  => '',
        'no_pages' => ''
    ];

    $result_cats = dbquery("SELECT *
        FROM ".DB_WIKI_CATS."
        ORDER BY wiki_cat_order
    ");

    if (dbrows($result_cats) > 0) {
        while ($categorie = dbarray($result_cats)) {
            $info['categories'][] = $categorie;
        }
    } else {
        $info['no_cats'] = $locale['wiki_039'];
    }

    $result_pages = dbquery(get_wiki_query(['condition' => "w.wiki_type='page' AND w.wiki_parent=0", 'order' => 'w.wiki_datestamp DESC', 'limit' => 12]));

    if (dbrows($result_pages) > 0) {
        /*require_once WIKI.'includes/Parsedown.php';
        $parsedown = new Parsedown;*/

        while ($page = dbarray($result_pages)) {
            $page['wiki_description'] = trim_text(strip_tags(parse_textarea($page['wiki_description'], FALSE, TRUE)), 200);
            //$page['wiki_description'] = strip_tags($parsedown->text($page['wiki_description']));
            $page['wiki_description'] = strip_tags($page['wiki_description']);
            $info['latest_pages'][] = $page;
        }
    } else {
        $info['no_pages'] = $locale['wiki_301'];
    }
}

render_wiki($info);

require_once THEMES.'templates/footer.php';
