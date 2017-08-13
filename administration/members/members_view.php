<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members/members_view.php
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
namespace Administration\Members;

class Members_View extends Members_Admin {

    public static function display_members() {
        return "<div class='clearfix'>
        <div class='pull-right'>{%action_button%}</div>
        <div class='pull-left'>{%filter_text%} {%filter_button%}</div>
        </div>
        <!----filter---->
        <div id='filter_panel' class='m-t-10' style='display:none'>
            <div class='list-group-item'>
                <div class='row'>
                    <div class='col-xs-3'><strong>".self::$locale['ME_560']."</strong></div>
                    <div class='col-xs-9'>{%filter_options%}{%filter_extras%}</div>
                </div>
            </div>
            <div class='list-group-item spacer-xs'>
                <div class='row'>
                    <div class='col-xs-3'><strong>".self::$locale['ME_561']."</strong></div>
                    <div class='col-xs-9'>{%filter_status%}</div>
                </div>
            </div>
            <br/>{%filter_apply_button%}
        </div>
        <!----//filter---->
        <hr/>
        <div class='clearfix spacer-xs'>{%page_count%}<div class='pull-right'>{%page_nav%}</div></div>
        <div id='user_action_bar' class='list-group-item spacer-sm p-10 text-center'>{%user_actions%}</div>
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
