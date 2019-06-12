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
defined('IN_FUSION') || exit;

if (!function_exists('display_main_weblinks')) {
    function display_main_weblinks($info) {
        $html = \PHPFusion\Template::getInstance('main_weblinks');
        $html2 = \PHPFusion\Template::getInstance('weblink_subcats');
        $html->set_template(__DIR__.'/templates/main_weblinks.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['weblink_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));

        add_to_css('.sub-cats-icon {
            -webkit-transform: scaleX(-1) rotate(90deg);
            -ms-transform: scaleX(-1) rotate(90deg);
            transform: scaleX(-1) rotate(90deg);
        }');

        if (!empty($info['weblink_categories'])) {
            foreach ($info['weblink_categories'][0] as $cat_id => $cat_data) {
                if ($cat_id != 0 && $info['weblink_categories'] != 0) {
                    foreach ($info['weblink_categories'] as $sub_cats) {
                        foreach ($sub_cats as $sub_cat_data) {
                            if (!empty($sub_cat_data['weblink_cat_parent']) && $sub_cat_data['weblink_cat_parent'] == $cat_id) {
                                $html2->set_block('sub_categories', [
                                    'link'        => INFUSIONS."weblinks/weblinks.php?cat_id=".$sub_cat_data['weblink_cat_id'],
                                    'name'        => $sub_cat_data['weblink_cat_name'],
                                    'count'       => $sub_cat_data['weblink_count'],
                                    'description' => parse_textarea($sub_cat_data['weblink_cat_description'], TRUE, TRUE, FALSE, '', TRUE)
                                ]);
                            }
                        }
                    }
                }

                $html2->set_text('{sub_categories.{
                    <div class="clearfix">
                        <h4 class="m-b-5"><i class="fas fa-level-down-alt sub-cats-icon"></i> <a href="{%link%}">{%name%} ({%count%})</a></h4>
                        {%description%}
                    </div>
                }}');

                $sub_cats = $html2->get_output();

                $html->set_block('categories', [
                    'cat_id'          => $cat_data['weblink_cat_id'],
                    'cat_link'        => INFUSIONS."weblinks/weblinks.php?cat_id=".$cat_data['weblink_cat_id'],
                    'cat_name'        => $cat_data['weblink_cat_name'],
                    'cat_count'       => $cat_data['weblink_count'],
                    'cat_description' => parse_textarea($cat_data['weblink_cat_description'], TRUE, TRUE, FALSE, '', TRUE),
                    'sub_categories'  => $sub_cats
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

        foreach ($info['weblink_filter'] as $view_keys => $page_link) {
            $html->set_block('filter_item', [
                'active' => $page_link['active'],
                'link'   => $page_link['link'],
                'title'  => $page_link['name']
            ]);
        }

        if (!empty($info['weblink_items'])) {
            foreach ($info['weblink_items'] as $web_data) {
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
