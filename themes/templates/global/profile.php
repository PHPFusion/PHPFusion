<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: profile.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('display_register_form')) {
    /**
     * Registration Form Template
     * The tags {%xyz%} are default replacement that the core will perform
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     *
     * @return string
     */
    function display_register_form(array $info = array()) {
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        ?>
        <!---HTML---->
        {%tab_header%}
        <!--register_pre_idx-->
        <div id='register_form' class='row m-t-20'><div class='col-xs-12 col-sm-12'>
               {%open_form%}
               {%user_id%}
               {%user_name_field%}
               {%user_email_field%}
               {%user_hide_email_field%}
               {%user_avatar_field%}
               {%user_password_field%}
               {%user_admin_password_field%}
               {%custom_fields%}
               {%captcha_fields%}
               {%eula%}
               {%post_button%}
               {%close_form%}
       </div></div>
        <!--register_sub_idx-->
        {%tab_footer%}
        <!---//HTML---->
        <?php
    }
}

if (!function_exists('display_profile_form')) {
    /**
     * Edit Profile Form Template
     * The tags {%xyz%} are default replacement that the core will perform
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     *
     * @return string
     */
    function display_profile_form(array $info = array()) {
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        ?>
        <!--HTML-->
        {%tab_header%}
        <!--editprofile_pre_idx-->
        <div id='profile_form' class='row m-t-20'>
            <div class='col-xs-12 col-sm-12'>
                {%open_form%}
                {%user_id%}
                {%user_name_field%}
                {%user_email_field%}
                {%user_hide_email_field%}
                {%user_reputation_field%}
                {%user_avatar_field%}
                {%user_password_field%}
                {%user_admin_password_field%}
                {%custom_fields%}
                {%captcha_fields%}
                {%eula%}
                {%post_button%}
                {%close_form%}
            </div>
        </div>
        <!--editprofile_sub_idx-->
        {%tab_footer%}
        <!--//HTML-->
        <?php
    }
}

/**
 * Profile display view
 * $info (array) - prepared responsive fields
 * To get information of the current raw userData
 * Uncomment and include the 3 lines at bottom inside render_userprofile()
 * global $userFields; // profile object at profile.php
 * $current_user_info = $userFields->getUserData(); // returns array();
 * print_p($current_user_info); // debug print
 */
if (!function_exists('display_user_profile')) {
    function display_user_profile($info) {
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        ?>
        <!--userprofile_pre_idx-->
        <section id='user-profile' class='spacer-sm overflow-hide'>
            {%tab_header%}
            <div class='container spacer-sm'>
                <div class='clearfix p-15 p-t-0'>
                    <div class='pull-left m-r-10'>{%user_avatar%}</div>
                    <div class='overflow-hide'><h4 class='m-0'>{%user_name%}<br/><small>{%user_level%}</small></h4></div>
                </div>
                <div class='clearfix'>{%admin_buttons%}</div>
                <hr/>
                <div class='clearfix'>{%basic_info%}</div>
                <hr/>
                <div class='clearfix'>{%extended_info%}</div>
            	<div class='text-center'>{%buttons%}</div>
            </div>
            {%tab_footer%}
        </section>
        <!--userprofile_sub_idx-->
        <?php
    }
}
/*
 * User Fields wrapper styling
 */
if (!function_exists('display_user_field')) {
    function display_user_field($info) {
        ?>
        <div id='{%field_id%}' class='row spacer-xs'>
            <label class='col-xs-12 col-sm-3'><strong>{%field_title%}</strong></label>
        <div class='col-xs-12 col-sm-9'><span class='profile_text'>{%field_value%}</span></div>
        </div>
        <?php
    }
}
/*
 * User Fields wrapper container
 */
if (!function_exists('display_user_field_container')) {
    function display_user_field_container($info) {
        ?>
        <div>{%user_fields%}</div>
        <?php
    }
}
