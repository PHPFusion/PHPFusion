<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: administration/dashboard/ratings/ratings.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
 *
 * @return string
 */
function display_ratings_widget() {
    global $global_ratings, $link_type, $comments_type;
    $locale = fusion_get_locale();

    if (count($global_ratings['data']) > 0) {
        foreach ($global_ratings['data'] as $i => $ratings_data) {
            $ratings_url = isset($link_type[$ratings_data['rating_type']]) ? "<a href='".sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id'])."'>{%item%}</a>\n" : "{%item%}";
            $ratings_item = isset($comments_type[$ratings_data['rating_type']]) ? $comments_type[$ratings_data['rating_type']] : $locale['ratings'];
            $info['rating_items'][] = [
                'id'           => $ratings_data['rating_id'],
                'avatar'       => display_avatar($ratings_data, "50px", '', '', 'img-rounded'),
                'profile_link' => profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status']),
                'name'         => strtr($ratings_url, ['{%item%}' => $ratings_item]),
                'ratings'      => str_repeat("<i class='fa fa-star fa-fw'></i>", $ratings_data['rating_vote']),
                'date'         => timer($ratings_data['rating_datestamp'])
            ];
        }
        //if (isset($global_ratings['ratings_nav'])) {
        //    $tpl->set_block('li_nav', ['ratings_nav' => $global_ratings['ratings_nav']]);
        //}
    } else {
        $info['no_ratings'] = $global_ratings['nodata'];
    }

    $info['title'] = $locale['278']." - ".format_num($global_ratings['rows']);

    return fusion_render(ADMIN.'dashboard/ratings', 'ratings.twig', $info, TRUE);
}
