<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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

        opentable("<i class='fa fa-fw fa-user m-r-10'></i>".$locale['400']);
        echo $info['search_table'];
        echo "<hr />\n";
        echo "<div class='well text-center m-b-20'>\n";
        echo $info['search_form'];
        echo "</div>\n";

        if (!empty($info['rows'])) {
            echo "<table class='table table-responsive table-hover'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th class='col-xs-1'>".$locale['411']."</th>\n";
            echo "<th class='col-xs-2'>".$locale['401']."</th>\n";
            echo "<th class='col-xs-3'>".$locale['405']."</th>\n";
            echo "<th class='col-xs-2'>".$locale['402']."</th>\n";
            echo "<th class='col-xs-2'>".$locale['410']."</th>\n";
            echo "<th class='col-xs-1'>".$locale['status']."</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";

            if (!empty($info['member'])) {

                foreach ($info['member'] as $user_id => $members) {

                    $groups = "";
                    if (!empty($members['user_groups'])) {
                        foreach ($members['user_groups'] as $group_id => $groupData) {
                            if (!empty($groupData)) {
                                $groups .= "<a class='btn btn-default btn-sm' href='".$groupData['link']."'>".$groupData['title']."</a>\n";
                            }
                        }
                    }

                    echo "<td class='col-xs-1'>".display_avatar($members, '50px', '', TRUE, 'img-rounded')."</td>\n";
                    echo "<td class='col-xs-2'><span class='side'>".profile_link($members['user_id'], $members['user_name'],
                                                                                 $members['user_status'])."</span></td>\n";
                    echo "<td class='col-xs-3'>\n".($groups ? $groups : $members['default_group'])."</td>\n";
                    echo "<td class='col-xs-2'>".getuserlevel($members['user_level'])."</td>\n";
                    echo "<td class='col-xs-2'>".$members['user_language']."</td>\n";
                    echo "<td class='col-xs-1'>".getuserstatus($members['user_status'])."</td>\n</tr>\n";
                }
            }
            echo "</table>\n";
            echo $info['page_nav'];
            echo "<hr/>\n";
            echo $info['search_table'];
        } else {
            echo "<div class='well text-center'>".$info['no_result']."</div>\n";
        }
        closetable();
    }
}