<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: PHPFusion/Feedback/Comments.view.php
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
if (!function_exists('display_comments_ui')) {
    function display_comments_ui() {
        ?>
        <div id='comments'>
            <div id='{%comment_container_id%}' name='comments'>
                {%comments_listing%}
                <a id='{%comment_form_container_id%}'></a>
                {%comments_form%}
            </div>
        </div>
        <?php
    }
}
/**
 * Comments UI
 */
if (!function_exists("display_comments_section")) {
    /**
     * Show comments
     *
     * @param       $c_data
     * @param       $c_info
     * @param array $options
     *
     * @return string
     */
    function display_comments_section($c_data, $c_info, array $options = array()) {
        ?>
        <!---comments-->
        <div class='comments-panel'>
            <div class='comments-header'>
                {%comment_title%} {%comment_count%}
            </div>
            {%comment_ratings%}
            <div class='comments overflow-hide'>
                {%comments%}
            </div>
        </div><!---//comments-->
        <?php
    }
}

/**
 * Comment Wrapper {%comments%}
 */
if (!function_exists('display_comments_listing')) {
    function display_comments_listing() {
        ?>
        <ul class='comments clearfix'>
            {%comments_list%}
        </ul>
        <div class='clearfix'>
            <span class='pull-right'>{%comments_admin_link%}</span>
            <div class='overflow-hide'>
                {%comments_page%}
            </div>
        </div>
        <?php
    }
}

/**
 * Single Comment List {%comments_lists%}
 */
if (!function_exists('display_comments_list')) {
    function display_comments_list() {
        ?>
        <li id='{%comment_list_id%}' class='m-b-15'>
            <?php if (fusion_get_settings('comments_avatar')) : ?>
                <div class='pull-left text-center m-r-15'>{%user_avatar%}</div>
            <?php endif ?>
            <div class='overflow-hide'>
                <div class='comment_name display-inline-block m-r-10'>{%user_name%}
                    <small class='comment_date'>{%comment_date%}</small>
                </div>
                {%comment_ratings%}
                <p class='comment_title'>{%comment_title%}</p>
                <p class='comment_message'>{%comment_message%}</p>
                <div>{%comment_reply_link%} &middot; {%comment_edit_link%} &middot; {%comment_delete_link%}</div>
            </div>
            {%comment_reply_form%}
            <ul class='sub-comments'>
                {%comment_sub_comments%}
            </ul>
        </li>
        <?php
    }
}

/**
 * The comment reply form HTML
 */
if (!function_exists('display_comments_reply_form')) {
    function display_comments_reply_form() {
        ?>
        <div class='comments_reply_form'>
            {%comment_name%}
            {%comment_message%}
            {%comment_captcha%}
            {%comment_post%}
        </div>
        <?php
    }
}

if (!function_exists("display_comments_form")) {
    /**
     * Comment Form
     *
     * @param       $comment_type
     * @param       $clink
     * @param       $comment_item_id
     * @param       $_CAPTCHA_HIDE_INPUT
     * @param array $options
     *
     * @return string
     */
    function display_comments_form($comment_type, $clink, $comment_item_id, $_CAPTCHA_HIDE_INPUT, array $options = array()) {
        ?>
        <div class='comments-form-panel'>
            <div class='comments-form-header'><h4>{%comment_form_title%}</h4></div>
            <div class='comments-form'>
                <?php if (fusion_get_settings('comments_avatar')) : ?>
                    <div class='pull-left m-r-15'>
                        {%user_avatar%}
                    </div>
                <?php endif; ?>
                <div class='overflow-hide p-5'>
                    <a id='{%comment_form_id%}' name='edit_comment'></a>
                    {%comment_name_input%}
                    {%comment_subject_input%}
                    {%comments_ratings_input%}
                    {%comment_message_input%}
                    {%comments_captcha_input%}
                    {%comment_post%}
                </div>
            </div>
        </div>
        <?php
    }
}