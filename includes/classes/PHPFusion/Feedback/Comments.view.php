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

use PHPFusion\Template;

if ( !function_exists( 'display_comments_ui' ) ) {
    function display_comments_ui( $info ) {
        $tpl = Template::getInstance( 'comments' );
        $tpl->set_template( __DIR__.'/comments.html' );
        $tpl->set_tag( 'comment_title', $info['comment_title'] );
        $tpl->set_tag( 'comment_form_container_id', $info['comment_form_container_id'] );
        $tpl->set_tag( 'comment_count', $info['comment_count'] );
        $tpl->set_tag( 'comments_form', $info['comments_form'] );
        $tpl->set_tag( 'comment_container_id', $info['comment_container_id'] );
        $tpl->set_tag( 'comments_listing', $info['comments_listing'] );
        
        return $tpl->get_output();
    }
}
/**
 * Comments UI
 */
if ( !function_exists( 'display_comments_section' ) ) {
    
    function display_comments_section( $info ) {
        $tpl = Template::getInstance( 'comments-section' );
        $tpl->set_template( __DIR__.'/comments-section.html' );
        $tpl->set_tag( 'comment_ratings', $info['comment_ratings'] );
        $tpl->set_tag( 'comments', $info['comments'] );
        
        return $tpl->get_output();
    }
}

/**
 * Comment Wrapper {%comments%}
 */
if ( !function_exists( 'display_comments_listing' ) ) {
    function display_comments_listing( $info ) {
        $tpl = Template::getInstance( 'comments-list' );
        $tpl->set_template( __DIR__.'/comments-list.html' );
        $tpl->set_tag( 'comments_list', $info['comments_list'] );
        $tpl->set_tag( 'comments_page', $info['comments_page'] );
        $tpl->set_tag( 'comments_admin_link', $info['comments_admin_link'] );
        
        return $tpl->get_output();
    }
}

/**
 * Single Comment List {%comments_lists%}
 */
if ( !function_exists( 'display_comments_list' ) ) {
    
    /**
     * @param array $info
     *
     * @return string
     */
    function display_comments_list( $info = [] ) {
        $tpl = Template::getInstance( 'comments-list-item' );
        $tpl->set_template( __DIR__.'/comment-list-item.html' );
        $tpl->set_tag( 'comment_id', $info['comment_id'] );
        $tpl->set_tag( 'user_name', $info['user_name'] );
        $tpl->set_tag( 'comment_ratings', $info['comment_ratings'] );
        $tpl->set_tag( 'comment_message', $info['comment_message'] );
        $tpl->set_tag( 'comment_date', $info['comment_date'] );
        $tpl->set_tag( 'comment_reply_form', $info['comment_reply_form'] );
        if ( $info['user_avatar'] )
            $tpl->set_block( 'comment_avatar', [ 'avatar' => $info['user_avatar'] ] );
        if ( $info['comment_subject'] )
            $tpl->set_block( 'comment_subject', [ 'subject' => $info['comment_subject'] ] );
        //comment_action
        if ( $info['comment_reply_link'] )
            $tpl->set_block( 'comment_action', [ 'link' => $info['comment_reply_link'] ] );
        if ( $info['comment_edit_link'] )
            $tpl->set_block( 'comment_action', [ 'link' => $info['comment_edit_link'] ] );
        if ( $info['comment_delete_link'] )
            $tpl->set_block( 'comment_action', [ 'link' => $info['comment_delete_link'] ] );
        if ( $info['comment_reply_form'] )
            $tpl->set_block( 'comment_reply_form', [ 'reply_form' => $info['comment_reply_form'] ] );
        if ( $info['comment_sub_comments'] )
            $tpl->set_block( 'comment_sub_comments', [ 'sub_comments' => $info['comment_sub_comments'] ] );
        
        return $tpl->get_output();
    }
}

/**
 * The comment reply form HTML
 */
if ( !function_exists( 'display_comments_reply_form' ) ) {
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

if ( !function_exists( 'display_comments_form' ) ) {
    /**
     * Comment Form
     *
     * @param $info
     *
     * @return string
     */
    function display_comments_form( $info ) {
        $tpl = Template::getInstance( 'comments-form' );
        $tpl->set_template( __DIR__.'/comments-form.html' );
        $tpl->set_tag( 'openform', $info['openform'] );
        $tpl->set_tag( 'closeform', $info['closeform'] );
        $tpl->set_tag( 'comment_form_title', $info['title'] );
        $tpl->set_tag( 'comment_name_input', $info['name_input'] );
        $tpl->set_tag( 'comment_subject_input', $info['subject_input'] );
        $tpl->set_tag( 'comments_ratings_input', $info['ratings_input'] );
        $tpl->set_tag( 'comment_message_input', $info['comment_input'] );
        $tpl->set_tag( 'comments_captcha_input', $info['captcha_input'] );
        $tpl->set_tag( 'comment_post', $info['post_button'] );
        if ( fusion_get_settings( 'comments_avatar' ) ) {
            $tpl->set_block( 'avatar', [ 'user_avatar' => $info['user_avatar'] ] );
        }
        
        return $tpl->get_output();
    }
}

if ( !function_exists( 'display_comments_ratings' ) ) {
    function display_comments_ratings( $info = [] ) {
        $tpl = Template::getInstance( 'ratings' );
        $tpl->set_template( __DIR__.'/ratings.html' );
        $tpl->set_tag( 'stars', $info['stars'] );
        $tpl->set_tag( 'reviews', $info['reviews'] );
        $tpl->set_tag( 'ratings', $info['ratings'] );
        $tpl->set_tag( 'ratings_remove_button', $info['remove_ratings'] );
        return $tpl->get_output();
    }
}
