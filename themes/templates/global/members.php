<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
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
if (!function_exists('render_members')) {
    /**
     * Render the members list
     * @param $info - the data
     */
    function render_members($info) {

        $locale = fusion_get_locale('', LOCALE.LOCALESET."members.php");

        opentable("<i class='fa fa-fw fa-user m-r-10'></i>".$locale['MEMB_000']);
        echo $info['search_table'];
        echo "<hr />\n";
        echo "<div class='well text-center m-b-20'>\n";
        echo $info['search_form'];
        echo "</div>\n";

        echo "<table class='m-b-20' style='width:100%;'>\n";
        echo "<tr>\n";
        echo "<td class='p-10'>\n".$info['page_result']."</td>\n";
        echo "<td class='text-right p-10'>\n".$info['page_nav']."</td>\n";
        echo "</tr>\n</table>\n";

        echo "<hr/>\n";

        if (!empty($info['rows'])) {

            echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th class='col-xs-1'>".$locale['MEMB_001']."</th>\n";
            echo "<th class='col-xs-2'>".$locale['MEMB_002']."</th>\n";
            echo "<th class='col-xs-3'>".$locale['MEMB_003']."</th>\n";
            echo "<th class='col-xs-2'>".$locale['MEMB_004']."</th>\n";
            echo "<th class='col-xs-2'>".$locale['language']."</th>\n";
            echo "<th class='col-xs-1'>".$locale['status']."</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";

            if (!empty($info['member'])) {

                foreach ($info['member'] as $user_id => $members) {

                    $groups = "";
                    if (!empty($members['groups'])) {
                        foreach ($members['groups'] as $group_id => $groupData) {
                            if (!empty($groupData)) {
                                $groups .= "<a class='btn btn-default btn-sm' href='".$groupData['link']."'>".$groupData['title']."</a>\n";
                            }
                        }
                    }

                    echo "<td class='col-xs-1'>".$members['user_avatar']."</td>\n";
                    echo "<td class='col-xs-2'><span class='side'>".profile_link($members['user_id'], $members['user_name'],
                                                                                 $members['user_status'])."</span></td>\n";
                    echo "<td class='col-xs-3'>\n".($groups ? $groups : $members['default_group'])."</td>\n";
                    echo "<td class='col-xs-2'>".getuserlevel($members['user_level'])."</td>\n";
                    echo "<td class='col-xs-2'>".translate_lang_names($members['user_language'])."</td>\n";
                    echo "<td class='col-xs-1'>".getuserstatus($members['user_status'])."</td>\n</tr>\n";
                }
            }
            echo "</table>\n</div>";

            echo "<table class='m-b-20' style='width:100%;'>\n";
            echo "<tr>\n";
            echo "<td class='p-10'>\n".$info['page_result']."</td>\n";
            echo "<td class='text-right p-10'>\n".$info['page_nav']."</td>\n";
            echo "</tr>\n</table>\n";

            echo $info['search_table'];
        } else {
            echo "<div class='well text-center'>".$info['no_result']."</div>\n";
        }
        closetable();
    }
}
