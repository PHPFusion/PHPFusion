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

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (Search_Engine::get_param('stype') == "members" || Search_Engine::get_param('stype') == "all") {

    $locale = fusion_get_locale('', LOCALE.LOCALESET.'search/members.php');

    // Default Values
    $item_count = "0 (".$locale['m403'].")<br />\n";

    $formatted_result = '';
    $rows = 0;

    if (!fusion_get_settings('hide_userprofiles') || iMEMBER) {

        $item_count = "0 ".$locale['m402']." ".$locale['522']."<br />\n";

        $rows = dbcount("(user_id)", DB_USERS, "user_status=:user_status AND user_name LIKE :stext",
                        array(
                            ':user_status'=>0,
                            ':stext'=>'%'.Search_Engine::get_param('stext').'%'
                        )
        );
        if ($rows != 0) {

            $item_count = "<a href='".FUSION_SELF."?stype=members&amp;stext=".Search_Engine::get_param('stext')."&amp;".Search_Engine::get_param('composevars')."'>".$rows." ".($rows == 1 ? $locale['m401'] : $locale['m402'])." ".$locale['522']."</a><br />\n";

            $order_by = array(
                '0' => ' DESC',
                '1' => ' ASC',
            );
            $sortby = !empty(Search_Engine::get_param('order')) ? 'ORDER BY user_name'.$order_by[Search_Engine::get_param('order')] : '';
            $limit = (Search_Engine::get_param('stype') != 'all' ? ' LIMIT '.Search_Engine::get_param('rowstart').',10' : '');
            $result = dbquery("SELECT user_id, user_name, user_status, user_level, user_avatar FROM ".DB_USERS."
            WHERE user_status=:user_status AND user_name LIKE :user_name ".$sortby.$limit
            , array(
                    ':user_status' => 0,
                    ':user_name' => '%'.Search_Engine::get_param('stext').'%'
                              ));
            /*
             * HTML
             */
            $search_result = "<!---members_search_results---><ul class='block spacer-xs'>\n";
            while ($data = dbarray($result)) {
                $search_result .= "<li>\n
                    <div class='clearfix'><div class='pull-left m-r-10'>".display_avatar($data, '70px', '', FALSE, '')."</div>
                    <div class='overflow-hide'>
                        <h4 class='m-0'>
                        ".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."
                        </h4>".getuserlevel($data['user_level'])."
                    </div></div>\n
                    </li>\n
                    ";
            }
            $search_result .= "</ul>\n<!---//members_search_results--->";

            $formatted_result = strtr(Search::render_search_item(), [
                '{%image%}' => ImageRepo::getImage('ac_M'),
                '{%icon_class%}' => 'fa fa-user-circle fa-lg fa-fw',
                '{%search_title%}' => "Members",
                '{%search_result%}' => $item_count,
                '{%search_content%}' => $search_result
            ]);
        }
    }
    Search_Engine::search_navigation($rows);
    Search_Engine::search_globalarray($formatted_result);
    Search_Engine::append_item_count($item_count);
}