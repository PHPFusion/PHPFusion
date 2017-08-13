<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /themes/templates/global/groups.php
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

if (!function_exists('render_user_group')) {
    /**
     * Display user groups
     * @param $info - fetch from UserGroups method setGroupInfo($group_id)
     */
    function render_user_group($info) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."user_fields.php");
        opentable($locale['u057']);
        echo "<div class='text-center well'>";
        echo "<h4>".(!empty($info['group_icon']) ? "<i class='".$info['group_icon']."'></i> " : "").(!empty($info['group_name']) ? $info['group_name'] : '')." ".format_word($info['total_rows'], $locale['fmt_member'])."</h4>\n";
        echo "</div>\n";
        $sort_plugin = fusion_sort_table('groupTbl');
        echo "<div class='table-responsive'><table id='groupTbl' class='table table-hover $sort_plugin'>\n";
        echo "<tr>\n";
        echo "<th class='col-xs-1'>".$locale['u062']."</th>\n";
        echo "<th class='col-xs-1'>".$locale['u113']."</th>\n";
        echo "<th class='col-xs-1'>".$locale['u114']."</th>\n";
        echo "<th class='col-xs-1'>".$locale['u115']."</th>\n";
        echo "<th class='col-xs-1'>".$locale['status']."</th>\n";
        echo "</tr>\n";
        if (!empty($info['group_members'])) {
            foreach($info['group_members'] as $member_id => $mData) {
                echo "<tr>\n";
                echo "<td class='col-xs-1'>".display_avatar($mData, '50px', '', FALSE, 'img-rounded')."</td>\n";
                echo "<td>".profile_link($mData['user_id'], $mData['user_name'], $mData['user_status'])."</td>\n";
                echo "<td class='col-xs-1'>".getuserlevel($mData['user_level'])."</td>\n";
                echo "<td class='col-xs-1'>".translate_lang_names($mData['user_language'])."</td>\n";
                echo "<td class='col-xs-1'>".getuserstatus($mData['user_status'])."</td>\n";
                echo "</tr>\n";
            }
        } else {
            echo "<tr>\n";
            echo "<td colspan='5'>".$locale['u116']."</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n</div>";
        echo $info['total_rows'] > $info['rows'] ? "<div class='pull-right m-r-10'>".makepagenav($_GET['rowstart'], $info['rows'], $info['total_rows'], 3, FUSION_SELF."?group_id=".$info['group_id']."&amp;")."</div>\n" : "";
        closetable();
    }
}
