<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: administration/dashboard/ratings/ratings.php
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

/**
 * Ratings Admin Dashboard Widget
 * Widget to display comments latest activity
 * @return string
 */
function display_ratings_widget() {
    global $global_ratings, $link_type, $comments_type;
    $locale = fusion_get_locale();

    $tpl = \PHPFusion\Template::getInstance('ratings-widget');
    $tpl->set_locale($locale);
    $tpl->set_template(__DIR__.'/ratings.html');

    if (count($global_ratings['data']) > 0) {
        foreach ($global_ratings['data'] as $i => $ratings_data) {
            $ratings_url = isset($link_type[$ratings_data['rating_type']]) ? "<a href='".sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id'])."'>{%item%}</a>\n" : "{%item%}";
            $ratings_item = isset($comments_type[$ratings_data['rating_type']]) ? $comments_type[$ratings_data['rating_type']] : $locale['ratings'];
            $info['border_style'] = ($i > 0 ? "style='border-top:1px solid #ddd;'" : '');
            $info['avatar'] = display_avatar($ratings_data, "50px", '', '', '');
            $info['profile_link'] = profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status']);
            $info['name'] = strtr($ratings_url, ['{%item%}' => $ratings_item]);
            $info['ratings'] = str_repeat("<i class='fa fa-star fa-fw'></i>", $ratings_data['rating_vote']);
            $info['date'] = timer($ratings_data['rating_datestamp']);
            $tpl->set_block('li', $info);
        }
        if (isset($global_ratings['ratings_nav'])) {
            $tpl->set_block('li_nav', ['ratings_nav' => $global_ratings['ratings_nav']]);
        }
    } else {
        $info = [
            'text' => $global_ratings['nodata'],
        ];
        $tpl->set_block('li_na', $info);
    }

    $content = fusion_get_function("open_sidex", $locale['278']." - ".format_num($global_ratings['rows']));
    $content .= $tpl->get_output();
    $content .= fusion_get_function("close_sidex");

    return (string)$content;
}
