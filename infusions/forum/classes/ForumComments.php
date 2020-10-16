<?php

use PHPFusion\Template;

function display_comments_form( $info ) {
    $tpl = Template::getInstance( 'comments-form' );
    $tpl->set_template( __DIR__.'/../templates/forum_threads_comments_form.html' );
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


function display_comments_ui( $info ) {
    $tpl = Template::getInstance( 'comments' );
    $tpl->set_template( __DIR__.'/../templates/forum_threads_comments_ui.html' );
    $tpl->set_tag( 'comment_title', $info['comment_title'] );
    $tpl->set_tag( 'comment_form_container_id', $info['comment_form_container_id'] );
    $tpl->set_tag( 'comment_count', $info['comment_count'] );
    $tpl->set_tag( 'comments_form', $info['comments_form'] );
    $tpl->set_tag( 'comment_container_id', $info['comment_container_id'] );
    $tpl->set_tag( 'comments_listing', $info['comments_listing'] );
    
    return $tpl->get_output();
}
