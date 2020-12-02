<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: members_view.php
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
namespace Administration\Members;

class Members_View {

    public static function display_members() {
        return "<div class='display-block clearfix'>
        <div class='pull-right'>{%action_button%}</div>
        <div class='pull-left'><div style='display:inline-block'>{%filter_text%}</div> {%filter_button%}</div>
        </div>

        <div class='collapse m-t-10' id='filterpanel' style='width: 100%'>
            <div class='list-grup'>
                <div class='list-group-item'>
                    <div><strong>{[ME_560]}</strong><br>{%filter_options%}{%filter_extras%}</div>
                </div>
                <div class='list-group-item'>
                    <div><strong>{[ME_561]}</strong><br>{%filter_status%}</div>
                </div>
            </div>
            <br/>{%filter_apply_button%}
        </div>
        <hr>
        <div class='clearfix spacer-xs'>{%page_count%}<div class='pull-right'>{%page_nav%}</div></div>
        <div id='user_action_bar' class='panel panel-default panel-body spacer-sm p-10'>{%user_actions%}</div>
        <div class='table-responsive'><table id='user_table' class='table table-hover table-striped ".fusion_sort_table('user_table')."'>
            <thead>
                {%list_head%}
                {%list_column%}
            </thead>
            <tbody>
                {%list_result%}
            </tbody>
            <tfoot>
            {%list_footer%}
            </tfoot>
        </table></div>
        ";
    }

}

require_once(THEMES.'templates/global/profile.php');
