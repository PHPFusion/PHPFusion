<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: groups.tpl.php
| Author: Core Development Team
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
        echo "<h4>".(!empty($info['group_icon']) ? "<i class='".$info['group_icon']."'></i> " : "").(!empty($info['group_name']) ? $info['group_name'] : '')." (".format_word($info['total_rows'], $locale['fmt_user']).")</h4>\n";
        echo '<p>'.$info['group_description'].'</p>';
        echo "</div>\n";
        $sort_plugin = fusion_sort_table('groupTbl');
        echo "<div class='table-responsive'><table id='groupTbl' class='table table-hover $sort_plugin'>\n";
        echo "<tr>\n";
        echo "<th class='col-xs-1'>".$locale['u062']."</th>\n";
        echo "<th class='col-xs-1'>".$locale['u113']."</th>\n";
        echo "<th class='col-xs-1'>".$locale['u114']."</th>\n";
        if (count(fusion_get_enabled_languages()) > 1) {
            echo "<th class='col-xs-1'>".$locale['u115']."</th>\n";
        }
        echo "<th class='col-xs-1'>".$locale['status']."</th>\n";
        echo "</tr>\n";
        if (!empty($info['group_members'])) {
            foreach ($info['group_members'] as $mData) {
                echo "<tr>\n";
                echo "<td class='col-xs-1'>".display_avatar($mData, '50px', '', FALSE, 'img-rounded')."</td>\n";
                echo "<td>".profile_link($mData['user_id'], $mData['user_name'], $mData['user_status'])."</td>\n";
                echo "<td class='col-xs-1'>".getuserlevel($mData['user_level'])."</td>\n";
                if (count(fusion_get_enabled_languages()) > 1) {
                    echo "<td class='col-xs-1'>".translate_lang_names($mData['user_language'])."</td>\n";
                }
                echo "<td class='col-xs-1'>".getuserstatus($mData['user_status'], $mData['user_lastvisit'])."</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n</div>";
        } else {
            echo "<div class='well text-center'>".$locale['u116']."</div>";
        }
        echo $info['group_pagenav'] ? "<div class='pull-right m-r-10'>".$info['group_pagenav']."</div>\n" : "";
        closetable();
    }
}
