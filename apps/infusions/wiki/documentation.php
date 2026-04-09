<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: documentation.php
| Author: RobiNN
+--------------------------------------------------------*/
require_once __DIR__.'/../../maincore.php';

if (!defined('WIKI_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';
require_once INCLUDES.'infusions_include.php';
require_once WIKI.'includes/functions.php';
require_once WIKI.'includes/OpenGraphWiki.php';
require_once WIKI.'templates/wiki.php';

$locale = fusion_get_locale('', WIKI_LOCALE);
$wiki_settings = get_settings('wiki');
$aidlink = fusion_get_aidlink();

set_title($locale['wiki_title']);

use \PHPFusion\BreadCrumbs;

BreadCrumbs::getInstance()->addBreadCrumb(['link' => WIKI, 'title' => $locale['wiki_title']]);

add_to_footer('<script src="'.WIKI.'includes/ajax/scripts.min.js?v='.filemtime(WIKI.'includes/ajax/scripts.min.js').'"></script>');

$info = [
    'no_pages' => ''
];

if (isset($_GET['page_id']) && isnum($_GET['page_id'])) {
    if (validate_wiki($_GET['page_id'])) {
        $data = dbarray(dbquery(get_wiki_query(['condition' => 'wiki_id=:wiki_id']), [':wiki_id' => intval($_GET['page_id'])]));

        $info['cat_name'] = $data['wiki_cat_name'];
        $info['page_title'] = $data['wiki_name'];
        $data['wiki_description'] = parse_wiki_text($data['wiki_description'], $data['wiki_allow_markdown']);

        $admin_link = [];
        if (iADMIN && checkrights('WIKI')) {
            $admin_link = [
                'edit'   => WIKI.'admin.php'.$aidlink.'&ref=form&action=edit&wiki_id='.$data['wiki_id'],
                'delete' => WIKI.'admin.php'.$aidlink.'&ref=form&action=delete&wiki_id='.$data['wiki_id']
            ];
        }

        $info['admin_link'] = $admin_link;

        $info['users'][] = [
            'user_id'     => $data['user_id'],
            'user_name'   => $data['user_name'],
            'user_status' => $data['user_status'],
            'action'      => $locale['wiki_302'],
            'page_link'   => WIKI.'documentation.php?page_id='.$data['wiki_id'].'#page'.$data['wiki_id'],
            'page_name'   => $data['wiki_name'],
            'time'        => $data['wiki_datestamp']
        ];

        if (!empty($data['wiki_edited']) && !empty($data['wiki_edited_datestamp'])) {
            $udata = fusion_get_user($data['wiki_edited']);
            $info['users'][] = [
                'user_id'     => $udata['user_id'],
                'user_name'   => $udata['user_name'],
                'user_status' => $udata['user_status'],
                'action'      => $locale['wiki_303'],
                'page_link'   => WIKI.'documentation.php?page_id='.$data['wiki_id'].'#page'.$data['wiki_id'],
                'page_name'   => $data['wiki_name'],
                'time'        => $data['wiki_edited_datestamp']
            ];
        }

        $sections = dbquery(get_wiki_query(['condition' => "w.wiki_parent=:page_id AND w.wiki_type = 'page'", 'order' => 'w.wiki_order']), [':page_id' => intval($_GET['page_id'])]);

        if (dbrows($sections) > 0) {
            while ($section = dbarray($sections)) {
                $info['sections'][] = $section;

                $info['users'][] = [
                    'user_id'     => $section['user_id'],
                    'user_name'   => $section['user_name'],
                    'user_status' => $section['user_status'],
                    'action'      => $locale['wiki_302'],
                    'page_link'   => WIKI.'documentation.php?page_id='.$data['wiki_id'].'#page'.$section['wiki_id'],
                    'page_name'   => $section['wiki_name'],
                    'time'        => $section['wiki_datestamp']
                ];

                if (!empty($section['wiki_edited']) && !empty($section['wiki_edited_datestamp'])) {
                    $udata = fusion_get_user($section['wiki_edited']);
                    $info['users'][] = [
                        'user_id'     => $udata['user_id'],
                        'user_name'   => $udata['user_name'],
                        'user_status' => $udata['user_status'],
                        'action'      => $locale['wiki_303'],
                        'page_link'   => WIKI.'documentation.php?page_id='.$section['wiki_id'].'#page'.$section['wiki_id'],
                        'page_name'   => $section['wiki_name'],
                        'time'        => $section['wiki_edited_datestamp']
                    ];
                }
            }
        }

        $info += $data;

        add_to_title(': '.$data['wiki_name']);

        $pages = dbquery(get_wiki_query(['condition' => "w.wiki_parent=:page_id"]), [':page_id' => intval($_GET['page_id'])]);

        if (dbrows($pages) > 0) {
            while ($page = dbarray($pages)) {
                $info['pages'][] = $page;
            }
        } else {
            $info['no_pages'] = $locale['wiki_301'];
        }

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => WIKI.'index.php?cat_id='.$data['wiki_cat_id'], 'title' => $data['wiki_cat_name']]);
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => WIKI.'documentation.php?page_id='.intval($_GET['page_id']), 'title' => $data['wiki_name']]);

        OpenGraphWiki::ogWiki($_GET['page_id']);

        render_wiki_article($info);
    } else {
        redirect(WIKI);
    }
} else {
    redirect(WIKI);
}

require_once THEMES.'templates/footer.php';
