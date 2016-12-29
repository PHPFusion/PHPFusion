<?php
namespace Administration\Members;

class Members_View extends Members_Admin {

    public static function display_members() {
        return "<div class='clearfix'>
        <div class='pull-right'>{%action_button%}</div>
        <div class='pull-left'>{%filter_text%} {%filter_button%}</div>
        </div>
        <!----filter---->
        <div id='filter_panel' style='display:none'>            
            <div class='list-group-item'>
                <div class='row'>
                    <div class='col-xs-3'><strong>Display Results.</strong></div>
                    <div class='col-xs-9'>{%filter_options%}{%filter_extras%}</div>
                </div>
            </div>                        
            <div class='list-group-item spacer-xs'>
                <div class='row'>
                    <div class='col-xs-3'><strong>Display User With Status</strong></div>
                    <div class='col-xs-9'>{%filter_status%}</div>
                </div>
            </div>                                    
            <br/>{%filter_apply_button%}
        </div>
        <!----//filter---->
        <hr/>       
        <div class='clearfix spacer-xs'>{%page_count%}<div class='pull-right'>{%page_nav%}</div></div>
        <div id='user_action_bar' class='list-group-item spacer-sm p-5'>{%user_actions%}</div>
        <table id='user_table' class='table table-hover table-striped ".fusion_sort_table('user_table')."'>
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
        </table>
        ";
    }


}
require_once(THEMES.'templates/global/profile.php');