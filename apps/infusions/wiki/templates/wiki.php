<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

define('ENTYPO', TRUE);

if (!function_exists('render_wiki')) {
    function render_wiki($info) {
        $locale = fusion_get_locale();

        add_to_head('<link rel="stylesheet" href="'.WIKI.'templates/html/wiki.min.css?v='.filemtime(WIKI.'templates/html/wiki.min.css').'"/>');
        add_to_jquery('$(".delete-link").bind("click",function(){return confirm("'.$locale['delete'].'?");});');
        hide_panels();

        $tpl = \PHPFusion\Template::getInstance('wiki');
        $tpl->set_template(__DIR__.'/html/wiki.html');
        $tpl->set_tag('wiki_class', '');
        $tpl->set_tag('breadcrumb', render_breadcrumbs());
        $tpl->set_tag('opentable', fusion_get_function('opentable', ''));
        $tpl->set_tag('closetable', fusion_get_function('closetable'));

        ob_start();
        if (isset($_GET['cat_id'])) {
            render_wiki_category_index($info);
            $id = 'cat_index';
            $title = $info['cat_name'];
        } else {
            render_wiki_index($info);
            $id = 'wiki_index';
            $title = '';
        }

        $content = ob_get_contents();
        ob_end_clean();

        $tpl->set_tag('id', $id);
        $tpl->set_tag('title', $title);
        $tpl->set_tag('content', $content);

        echo $tpl->get_output();
    }
}

if (!function_exists('render_wiki_index')) {
    function render_wiki_index($info) {
        $search_tpl = \PHPFusion\Template::getInstance('wiki_search');
        $search_tpl->set_template(__DIR__.'/html/search.html');
        $search_tpl->set_locale(fusion_get_locale());
        $wiki_search = $search_tpl->get_output();

        $tpl = \PHPFusion\Template::getInstance('wiki_index');
        $tpl->set_template(__DIR__.'/html/index.html');
        $tpl->set_locale(fusion_get_locale());

        $tpl->set_tag('wiki_search', $wiki_search);
        $tpl->set_tag('imgs', WIKI.'images/');
        $tpl->set_tag('changelog_link', WIKI.'index.php?page=changelog');

        if (!empty($info['categories'])) {
            foreach ($info['categories'] as $cat) {
                $tpl->set_block('categories', [
                    'name'        => $cat['wiki_cat_name'],
                    'link'        => WIKI.'index.php?cat_id='.$cat['wiki_cat_id'],
                    'description' => trim_text(strip_tags(html_entity_decode($cat['wiki_cat_description'])), 200)
                ]);
            }
        }

        $tpl->set_tag('no_cats', $info['no_cats']);

        if (!empty($info['latest_pages'])) {
            $i = 1;
            foreach ($info['latest_pages'] as $page) {
                $tpl->set_block('latest_pages', [
                    'name'     => $page['wiki_name'],
                    'link'     => WIKI.'documentation.php?page_id='.$page['wiki_id'],
                    'cat_name' => $page['wiki_cat_name'],
                    'cat_link' => WIKI.'index.php?cat_id='.$page['wiki_cat_id'],
                    'date'     => showdate('%d. %B %Y', $page['wiki_datestamp']),
                    'snippet'  => $page['wiki_description'],
                    'div'      => floor(count($info['latest_pages']) / 2) + 1 < $i ? '</div><div class="col-xs-12 col-sm-6">' : ''
                ]);

                $i++;
            }
        }

        $tpl->set_tag('no_pages', $info['no_pages']);

        echo $tpl->get_output();
    }
}

if (!function_exists('render_wiki_article')) {
    function render_wiki_article($info) {
        $locale = fusion_get_locale('', WIKI_LOCALE);
        $wiki_settings = get_settings('wiki');

        add_to_head('<link rel="stylesheet" href="'.WIKI.'templates/html/wiki.min.css?v='.filemtime(WIKI.'templates/html/wiki.min.css').'"/>');
        add_to_jquery('$(".delete-link").bind("click",function(){return confirm("'.$locale['delete'].'?");});');
        hide_panels();

        $tpl = \PHPFusion\Template::getInstance('wiki');
        $tpl->set_template(__DIR__.'/html/wiki.html');
        $tpl->set_tag('breadcrumb', render_breadcrumbs());
        $tpl->set_tag('opentable', fusion_get_function('opentable', ''));
        $tpl->set_tag('closetable', fusion_get_function('closetable'));

        $search_tpl = \PHPFusion\Template::getInstance('wiki_search');
        $search_tpl->set_template(__DIR__.'/html/search.html');
        $search_tpl->set_locale(fusion_get_locale());
        $wiki_search = $search_tpl->get_output();

        $page_tpl = \PHPFusion\Template::getInstance('wiki_pages');

        if ($info['wiki_type'] == 'page') {
            $page_tpl = \PHPFusion\Template::getInstance('wiki_article');
            $page_tpl->set_template(__DIR__.'/html/article.html');
            $page_tpl->set_locale(fusion_get_locale());

            $page_tpl->set_tag('navigation', fusion_get_function('render_wiki_navigation', TRUE, $info['wiki_cat'], $info['wiki_parent']));
            $page_tpl->set_tag('wiki_search', $wiki_search);
            $page_tpl->set_tag('article', $info['wiki_description']);
            $page_tpl->set_tag('admin_links', !empty($info['admin_link']) ? '<a href="'.$info['admin_link']['edit'].'" class="btn btn-default">'.$locale['edit'].'</a><a href="'.$info['admin_link']['delete'].'" class="btn btn-danger delete-link">'.$locale['delete'].'</a>' : '');

            $versions = '';
            if (!empty($info['wiki_versions'])) {
                $versions2 = str_replace('.', ',', $info['wiki_versions']);
                $result = dbquery("SELECT wiki_version_id, wiki_version from ".DB_WIKI_VERSIONS." WHERE wiki_version_id IN ($versions2)");

                if (dbrows($result) > 0) {
                    $i = 1;
                    while ($data = dbarray($result)) {
                        $versions .= '<span class="label label-primary m-r-5">'.$data['wiki_version'].'</span>';
                        $i++;
                    }
                }
            }

            $page_tpl->set_tag('versions', !empty($versions) ? $locale['wiki_304'].$versions : '');

            if (!empty($info['sections'])) {
                foreach ($info['sections'] as $key => $data) {
                    $page_tpl->set_block('sections', [
                        'id'          => $data['wiki_id'],
                        'name'        => $data['wiki_name'],
                        'content'     => parse_wiki_text($data['wiki_description'], $data['wiki_allow_markdown']),
                        'admin_links' => iADMIN && checkrights('WIKI') ? '<span class="pull-right"><a href="'.WIKI.'admin.php'.fusion_get_aidlink().'&ref=form&action=edit&wiki_id='.$data['wiki_id'].'">'.$locale['edit'].'</a> | <a href="'.WIKI.'admin.php'.fusion_get_aidlink().'&ref=form&action=delete&wiki_id='.$data['wiki_id'].'" class="text-danger delete-link">'.$locale['delete'].'</a></span>' : ''
                    ]);

                    $page_tpl->set_block('sections_nav', [
                        'id'     => $data['wiki_id'],
                        'name'   => $data['wiki_name']
                    ]);
                }
            }

            if (!empty($info['users'])) {
                foreach ($info['users'] as $user) {
                    $page_tpl->set_block('contributors', [
                        'user_name' => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                        'log'       => $user['action'].' <a href="'.$user['page_link'].'">'.$user['page_name'].'</a> '.$locale['wiki_316'].' '.showdate('%d/%m/%Y', $user['time'])
                    ]);
                }
            }

            if ($wiki_settings['wiki_allow_submission'] == TRUE) {
                $page_tpl->set_block('submission', [
                    'submit_link' => BASEDIR.'submit.php?stype=w'
                ]);
            }
        } else if ($info['wiki_type'] == 'index') {
            $page_tpl = \PHPFusion\Template::getInstance('wiki_cat_index');
            $page_tpl->set_template(__DIR__.'/html/cat_index.html');
            $page_tpl->set_locale(fusion_get_locale());

            $page_tpl->set_tag('navigation', fusion_get_function('render_wiki_navigation', TRUE, $info['wiki_cat']));
            $page_tpl->set_tag('wiki_search', $wiki_search);
            $page_tpl->set_tag('description', $info['wiki_description']);
            $page_tpl->set_tag('admin_links', !empty($info['admin_link']) ? '<div class="btn-group btn-group-sm"><a href="'.$info['admin_link']['edit'].'" class="btn btn-default">'.$locale['edit'].'</a><a href="'.$info['admin_link']['delete'].'" class="btn btn-danger delete-link">'.$locale['delete'].'</a></div>' : '');

            if (!empty($info['pages'])) {
                $count = 1;

                foreach ($info['pages'] as $key => $page) {
                    $page_tpl->set_block('pages', [
                        'name' => $page['wiki_name'],
                        'link' => WIKI.'documentation.php?page_id='.$page['wiki_id'],
                        'div'  => floor(count($info['pages']) / 2) + 1 < $count ? '</ul></div><div class="col-xs-12 col-sm-6"><ul class="list-style-none m-b-0">' : ''
                    ]);

                    $count++;
                }
            }

            $page_tpl->set_tag('no_pages', $info['no_pages']);
        }

        $content = $page_tpl->get_output();
        $tpl->set_tag('id', 'wikipage');
        $tpl->set_tag('title', $info['page_title']);
        $tpl->set_tag('content', $content);

        echo $tpl->get_output();
    }
}

if (!function_exists('render_wiki_category_index')) {
    function render_wiki_category_index($info) {
        $locale = fusion_get_locale('', WIKI_LOCALE);

        $search_tpl = \PHPFusion\Template::getInstance('wiki_search');
        $search_tpl->set_template(__DIR__.'/html/search.html');
        $search_tpl->set_locale(fusion_get_locale());
        $wiki_search = $search_tpl->get_output();

        $tpl = \PHPFusion\Template::getInstance('wiki_cat_index');
        $tpl->set_template(__DIR__.'/html/cat_index.html');

        $tpl->set_tag('wiki_search', $wiki_search);
        $tpl->set_tag('navigation', fusion_get_function('render_wiki_navigation', ''));
        $tpl->set_tag('description', $info['description']);
        $tpl->set_tag('admin_links', !empty($info['admin_link']) ? '<div><a href="'.$info['admin_link']['edit'].'">'.$locale['edit'].'</a> | <a href="'.$info['admin_link']['delete'].'" class="text-danger">'.$locale['delete'].'</a></div>' : '');

        if (!empty($info['pages'])) {
            $count = 1;
            foreach ($info['pages'] as $key => $page) {
                $tpl->set_block('pages', [
                    'name' => $page['wiki_name'],
                    'link' => WIKI.'documentation.php?page_id='.$page['wiki_id'],
                    'div'  => floor(count($info['pages']) / 2) + 1 < $count ? '</ul></div><div class="col-xs-12 col-sm-6"><ul class="list-style-none m-b-0">' : ''
                ]);

                $count++;
            }
        }

        $tpl->set_tag('no_pages', $info['no_pages']);

        echo $tpl->get_output();
    }
}

if (!function_exists('render_wiki_navigation')) {
    function render_wiki_navigation($pages_ = FALSE, $cat_id = 0, $wiki_parent = NULL) {
        $locale = fusion_get_locale();

        echo '<div class="m-b-10" style="font-size: 20px;"><i class="entypo entypo-documents"></i> '.$locale['wiki_317'].'</div>';

        $result_cats = dbquery("SELECT * FROM ".DB_WIKI_CATS." ".($pages_ == TRUE ? 'WHERE wiki_cat_id = '.intval($cat_id) : '')." ORDER BY wiki_cat_order");

        if (dbrows($result_cats) > 0) {
            $categories = [];


            while ($cat = dbarray($result_cats)) {
                $categories[$cat['wiki_cat_id']] = $cat;
            }

            if (isset($_GET['cat_id']) && $_GET['cat_id']) {
                $temp = array($_GET['cat_id'] => $categories[$_GET['cat_id']]);
                unset($categories[$_GET['cat_id']]);
                $categories = $temp + $categories;
            }

            foreach ($categories as $cat) {
                echo '<div class="list-group">';
                    $active = isset($_GET['cat_id']) && $_GET['cat_id'] == $cat['wiki_cat_id'] ? ' active' : '';
                    echo '<a class="list-group-item'.$active.'" href="'.WIKI.'index.php?cat_id='.$cat['wiki_cat_id'].'"><div class="list-group-item-heading m-b-0">'.$cat['wiki_cat_name'].'</div></a>';

                    $result_pages = dbquery(get_wiki_query(['condition' => "w.wiki_cat=:cat_id", 'order' => 'w.wiki_order']), [':cat_id' => $cat['wiki_cat_id']]);
                    $pages = [];

                    if (dbrows($result_pages) > 0) {
                        while ($page = dbarray($result_pages)) {
                            $parent_id = $page['wiki_parent'] === NULL ? 'NULL' : $page['wiki_parent'];
                            $pages['page'][$parent_id][$page['wiki_id']] = [
                                'name'   => $page['wiki_name'],
                                'link'   => WIKI.'documentation.php?page_id='.$page['wiki_id'],
                                'parent' => $page['wiki_parent'],
                                'type'   => $page['wiki_type'],
                                'id'     => $page['wiki_id']
                            ];
                        }

                        foreach ($pages['page'][0] as $id => $data) {
                            $active = isset($_GET['page_id']) && $_GET['page_id'] == $id ? ' active' : '';
                            echo '<a class="list-group-item'.$active.'" href="'.$data['link'].'">'.$data['name'].'</a>';

                            if ($id != 0 && $pages['page'] != 0 && ($wiki_parent == $id) || (isset($_GET['page_id']) && $_GET['page_id'] == $id)) {
                                foreach ($pages['page'] as $sub_pages_id => $sub_pages) {
                                    foreach ($sub_pages as $sub_page_id => $sub_page_data) {
                                        if (!empty($sub_page_data['parent']) && $sub_page_data['parent'] == $id) {
                                            $active = isset($_GET['page_id']) && $_GET['page_id'] == $sub_page_id ? ' active' : '';
                                            echo '<a class="p-l-15 list-group-item'.$active.'" href="'.$sub_page_data['link'].'"><i class="fas fa-level-down-alt sub-page-icon m-r-5"></i> '.$sub_page_data['name'].'</a>';
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        echo '<div class="list-group-item">'.$locale['wiki_301'].'</div>';
                    }

                echo '</div>';
            }
        } else {
            echo '<div class="list-group-item">'.$locale['wiki_039'].'</div>';
        }
    }
}

function hide_panels() {
    \PHPFusion\Panels::getInstance(TRUE)->hide_panel('RIGHT');
    \PHPFusion\Panels::getInstance(TRUE)->hide_panel('LEFT');
    \PHPFusion\Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
    \PHPFusion\Panels::getInstance(TRUE)->hide_panel('U_CENTER');
    \PHPFusion\Panels::getInstance(TRUE)->hide_panel('L_CENTER');
    \PHPFusion\Panels::getInstance(TRUE)->hide_panel('BL_CENTER');
}
