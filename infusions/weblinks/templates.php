<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/templates.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('display_main_weblinks')) {
    function display_main_weblinks($info) {
        $html = \PHPFusion\Template::getInstance('main_weblinks');
        $html->set_template(__DIR__.'/templates/main_weblinks.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['weblink_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));

        if (!empty($info['weblink_categories'])) {
            foreach ($info['weblink_categories'] as $cat_id => $cat_data) {
                $html->set_block('categories', [
                    'cat_id'          => $cat_data['cat_id'],
                    'cat_link'        => $cat_data['link'],
                    'cat_name'        => $cat_data['name'],
                    'cat_count'       => $cat_data['count'],
                    'cat_description' => $cat_data['description']
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('web_0062')]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('display_weblinks_item')) {
    function display_weblinks_item($info) {
        $html = \PHPFusion\Template::getInstance('weblinks_item');
        $html->set_template(__DIR__.'/templates/weblinks_info.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['weblink_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_block('pagenav', ['pagenav' => $info['pagenav']]);
        $html->set_block('pagenav2', ['pagenav' => $info['pagenav']]);

        $i = 0;
        foreach ($info['weblink_filter'] as $view_keys => $page_link) {
            $html->set_block('filter_item', [
                'active' => ((!isset($_GET['type']) && (!$i)) || (isset($_GET['type']) && $_GET['type'] === $view_keys) ? "text-dark strong" : ''),
                'link'   => $page_link['link'],
                'title'  => $page_link['name']
            ]);
            $i++;
        }

        foreach ($info['navbar'] as $view_keys => $navbar_link) {
            $html->set_block('navbar_item', [
                'links' => $navbar_link['links']
            ]);
        }

        $html->set_tag('weblinks_span', $info['span']);

        if (!empty($info['weblink_items'])) {
            foreach ($info['weblink_items'] as $id => $web_data) {
                $html->set_block('weblink', [
                    'weblink_id'          => $web_data['weblink_id'],
                    'weblink_link'        => $web_data['weblinks_url'],
                    'weblink_name'        => $web_data['weblink_name'],
                    'weblink_description' => htmlspecialchars_decode($web_data['weblink_description']),
                    'weblink_cat_link'    => $web_data['weblinks_cat_url'],
                    'weblink_cat_name'    => $web_data['weblink_cat_name'],
                    'weblink_count'       => $web_data['weblink_count'],
                    'weblink_datestamp'   => showdate('shortdate', $web_data['weblink_datestamp']),
                    'admin_edit_link'     => !empty($web_data['admin_actions']) ? "<a href='".$web_data['admin_actions']['edit']['link']."' title='".$web_data['admin_actions']['edit']['title']."'><i class='fa fa-fw fa-pencil m-l-10'></i></a>" : '',
                    'admin_delete_link'   => !empty($web_data['admin_actions']) ? "<a href='".$web_data['admin_actions']['delete']['link']."' title='".$web_data['admin_actions']['delete']['title']."'><i class='fa fa-fw fa-trash m-l-10'></i></a>" : '',
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('web_0062')]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('display_weblink_submissions')) {
    function display_weblink_submissions($info) {
        $html = \PHPFusion\Template::getInstance('weblink_submissions');
        $html->set_template(__DIR__.'/templates/weblinks_submissions.html');
        $html->set_tag('opentable', fusion_get_function('opentable', $info['weblink_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        if (!empty($info['item'])) {
            $html->set_block('weblink_submit', [
                'guidelines'          => $info['item']['guidelines'],
                'openform'            => $info['item']['openform'],
                'closeform'           => closeform(),
                'weblink_cat'         => $info['item']['weblink_cat'],
                'weblink_name'        => $info['item']['weblink_name'],
                'weblink_url'         => $info['item']['weblink_url'],
                'weblink_language'    => $info['item']['weblink_language'],
                'weblink_description' => $info['item']['weblink_description'],
                'weblink_submit'      => $info['item']['weblink_submit']
            ]);
        }

        if (!empty($info['confirm'])) {
            $html->set_block('weblink_confirm_submit', [
                'title'       => $info['confirm']['title'],
                'submit_link' => $info['confirm']['submit_link'],
                'index_link'  => $info['confirm']['index_link']
            ]);
        }

        if (!empty($info['no_submissions'])) {
            $html->set_block('weblink_no_submit', ['text' => $info['no_submissions']]);
        }

        echo $html->get_output();
    }
}
