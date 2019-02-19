<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_members_include.php
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
namespace PHPFusion\Search;

use PHPFusion\ImageRepo;
use PHPFusion\Search;

defined('IN_FUSION') || exit;

if (Search_Engine::get_param('stype') == "members" || Search_Engine::get_param('stype') == "all") {

    $locale = fusion_get_locale('', LOCALE.LOCALESET.'search/members.php');

    // Default Values
    $item_count = "0 (".$locale['m403'].")<br />\n";

    $formatted_result = '';
    $rows = 0;

    if (!fusion_get_settings('hide_userprofiles') || iMEMBER) {

        $item_count = "0 ".$locale['m402']." ".$locale['522']."<br />\n";

        $rows = dbcount("(user_id)", DB_USERS, "user_status=:user_status AND user_name LIKE :stext",
            [
                ':user_status' => 0,
                ':stext'       => '%'.Search_Engine::get_param('stext').'%'
            ]
        );
        if ($rows != 0) {

            $item_count = "<a href='".BASEDIR."search.php?stype=members&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['m401'] : $locale['m402'])." ".$locale['522']."</a><br />\n";
            $order_by = [
                '0' => ' DESC',
                '1' => ' ASC',
            ];
            $sortby = !empty(Search_Engine::get_param('order')) ? 'ORDER BY user_name'.$order_by[Search_Engine::get_param('order')] : '';
            $limit = (Search_Engine::get_param('stype') != 'all' ? ' LIMIT '.Search_Engine::get_param('rowstart').',10' : '');
            $result = dbquery("SELECT user_id, user_name, user_status, user_level, user_avatar FROM ".DB_USERS."
            WHERE user_status=:user_status AND user_name LIKE :user_name ".$sortby.$limit
                , [
                    ':user_status' => 0,
                    ':user_name'   => '%'.Search_Engine::get_param('stext').'%'
                ]);
            /*
             * HTML
             */
            $search_result = '';
            while ($data = dbarray($result)) {
                $search_result .= strtr(Search::render_search_item(), [
                        '{%item_url%}'         => BASEDIR."profile.php?lookup=".$data['user_id'],
                        '{%item_image%}'       => display_avatar($data, '70px', '', FALSE, ''),
                        '{%item_title%}'       => $data['user_name'],
                        '{%item_description%}' => getuserlevel($data['user_level']),
                    ]
                );
            }

            $formatted_result = strtr(Search::render_search_item_wrapper(), [
                '{%image%}'          => "<img src='".ImageRepo::getimage('ac_M')."' alt='".$locale['user1']."' style='width:32px;'/>",
                '{%icon_class%}'     => 'fa fa-user-circle fa-lg fa-fw',
                '{%search_title%}'   => $locale['user1'],
                '{%search_result%}'  => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }
    }

    Search_Engine::search_navigation($rows);
    Search_Engine::search_globalarray($formatted_result);
    Search_Engine::append_item_count($item_count);
}
