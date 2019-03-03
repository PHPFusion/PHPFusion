<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/templates.php
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

if (!function_exists('display_main_faq')) {
    function display_main_faq($info) {
        $html = \PHPFusion\Template::getInstance('main_faq');
        $html->set_template(__DIR__.'/templates/main_faq.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['faq_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);

        if (!empty($info['faq_categories'])) {
            foreach ($info['faq_categories'] as $cat_data) {
                $html->set_block('categories', [
                    'faq_cat_id'          => $cat_data['faq_cat_id'],
                    'faq_cat_link'        => $cat_data['faq_cat_link'],
                    'faq_cat_name'        => $cat_data['faq_cat_name'],
                    'faq_cat_description' => $cat_data['faq_cat_description']
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('faq_0112a')]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('render_faq_item')) {
    function render_faq_item($info) {
        $html = \PHPFusion\Template::getInstance('faq_item');
        $html->set_template(__DIR__.'/templates/faq_info.html');
        $html->set_tag('breadcrumb', render_breadcrumbs());
        $html->set_tag('opentable', fusion_get_function('opentable', $info['faq_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        $html->set_tag('cat_locale', $info['cat_locale']);
        $html->set_tag('cat_top', $info['cat_top']);
        $html->set_tag('faq_get_name', $info['faq_get_name']);

        if (!empty($info['faq_items'])) {
            add_to_jquery('$(".top").on("click",function(e){e.preventDefault();$("html, body").animate({scrollTop:0},100);});');
            foreach ($info['faq_items'] as $faq_data) {
                $html->set_block('faq', [
                    'faq_id'       => $faq_data['faq_id'],
                    'faq_question' => $faq_data['faq_question'],
                    'faq_answer'   => $faq_data['faq_answer'],
                    'print_link'   => "<a href='".$faq_data['print']['link']."' target='_blank' title='".$faq_data['print']['title']."'><i class='fa fa-fw fa-print'></i></a>",
                    'edit_link'    => !empty($faq_data['edit']['link']) ? "<a href='".$faq_data['edit']['link']."' title='".$faq_data['edit']['title']."'><i class='fa fa-fw fa-pencil m-l-10'></i></a>" : '',
                    'delete_link'  => !empty($faq_data['delete']['link']) ? "<a href='".$faq_data['delete']['link']."' title='".$faq_data['delete']['title']."'><i class='fa fa-fw fa-trash m-l-10'></i></a>" : ''
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => fusion_get_locale('faq_0112')]);
        }

        echo $html->get_output();
    }
}

if (!function_exists('display_faq_submissions')) {
    function display_faq_submissions($info) {
        $html = \PHPFusion\Template::getInstance('faq_submissions');
        $html->set_template(__DIR__.'/templates/faq_submissions.html');
        $html->set_tag('opentable', fusion_get_function('opentable', $info['faq_tablename']));
        $html->set_tag('closetable', fusion_get_function('closetable'));
        if (!empty($info['item'])) {
            $html->set_block('faq_submit', [
                'guidelines'   => $info['item']['guidelines'],
                'openform'     => $info['item']['openform'],
                'closeform'    => closeform(),
                'faq_question' => $info['item']['faq_question'],
                'faq_answer'   => $info['item']['faq_answer'],
                'faq_cat_id'   => $info['item']['faq_cat_id'],
                'faq_language' => $info['item']['faq_language'],
                'faq_submit'   => $info['item']['faq_submit']
            ]);
        }

        if (!empty($info['confirm'])) {
            $html->set_block('faq_confirm_submit', [
                'title'       => $info['confirm']['title'],
                'submit_link' => $info['confirm']['submit_link'],
                'index_link'  => $info['confirm']['index_link']
            ]);
        }

        if (!empty($info['no_submissions'])) {
            $html->set_block('faq_no_submit', ['text' => $info['no_submissions']]);
        }

        echo $html->get_output();
    }
}
