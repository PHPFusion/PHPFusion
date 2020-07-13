<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_delete_include.php
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
defined( 'IN_FUSION' ) || exit;

$locale = fusion_get_locale( '', __DIR__.'/locale/'.LANGUAGE.'.php' );

// Display user field input
if ( $profile_method == "input" ) {

    $user_fields = '';
    if ( defined( 'ADMIN_PANEL' ) ) {
        $user_fields = "<div class='well m-t-5 text-center'>".$locale['uf_delete']."</div>";
    }
    // Display in profile
} else if ( $profile_method == "display" ) {
    if ( !defined( 'ADMIN_PANEL' ) ) {

        if ( iMEMBER && post( 'delete_me' ) && fusion_get_userdata( 'user_id' ) == get( 'lookup' ) && !iSUPERADMIN ) {
            $data = fusion_get_userdata( 'user_id' );

            dbquery( "DELETE FROM ".DB_COMMENTS." WHERE comment_name = :cname", [ ':cname' => (int)$data ] );
            dbquery( "DELETE FROM ".DB_MESSAGES." WHERE message_to = :mto OR message_from = :mfrom", [ ':mto' => (int)$data, ':mfrom' => (int)$data ] );
            dbquery( "DELETE FROM ".DB_RATINGS." WHERE rating_user = :ratinguser", [ ':ratinguser' => (int)$data ] );
            dbquery( "DELETE FROM ".DB_SUSPENDS." WHERE suspended_user = :suspendeduser", [ ':suspendeduser' => (int)$data ] );
            dbquery( "DELETE FROM ".DB_SUBMISSIONS." WHERE submit_user = :submituser", [ ':submituser' => (int)$data ] );
            dbquery( "DELETE FROM ".DB_USER_LOG." WHERE userlog_user_id = :userid", [ ':userid' => (int)$data ] );
            if ( defined( 'ARTICLES_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_ARTICLES." WHERE article_name = :articlename", [ ':articlename' => (int)$data ] );
            }
            if ( defined( 'BLOG_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_BLOG." WHERE blog_name = :blogname", [ ':blogname' => (int)$data ] );
            }
            if ( defined( 'DOWNLOADS_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_DOWNLOADS." WHERE download_user = :downloaduser", [ ':downloaduser' => (int)$data ] );
            }
            if ( defined( 'FAQ_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_FAQS." WHERE faq_name = :faqname", [ ':faqname' => (int)$data ] );
            }
            if ( defined( 'GALLERY_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_PHOTOS." WHERE photo_user = :photouser", [ ':photouser' => (int)$data ] );
                dbquery( "DELETE FROM ".DB_PHOTO_ALBUMS." WHERE album_user = :album_user", [ ':album_user' => (int)$data ] );
            }
            if ( defined( 'NEWS_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_NEWS." WHERE news_name = :newsname", [ ':newsname' => (int)$data ] );
            }
            if ( defined( 'SHOUTBOX_PANEL_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_SHOUTBOX." WHERE shout_name = :shoutname", [ ':shoutname' => (int)$data ] );
            }
            if ( defined( 'MEMBER_POLL_PANEL_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_POLL_VOTES." WHERE vote_user = :voteuser", [ ':voteuser' => (int)$data ] );
            }
            if ( defined( 'FORUM_EXIST' ) ) {
                dbquery( "DELETE FROM ".DB_FORUM_POSTS." WHERE post_author = :postauthor", [ ':postauthor' => (int)$data ] );
                dbquery( "DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author = :threadauthor", [ ':threadauthor' => (int)$data ] );
                dbquery( "DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user = :notifyuser", [ ':notifyuser' => (int)$data ] );
                dbquery( "DELETE FROM ".DB_FORUM_USER_REP." WHERE user_id = :userid", [ ':userid' => (int)$data ] );
                dbquery( "DELETE FROM ".DB_FORUM_VOTES." WHERE vote_user = :voteuser", [ ':voteuser' => (int)$data ] );
                dbquery( "DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id = :voteuser", [ ':voteuser' => (int)$data ] );
            }
            dbquery( "DELETE FROM ".DB_USERS." WHERE user_id = :userid", [ ':userid' => (int)$data ] );

            add_notice( 'success', $locale['uf_delete_exit'] );
            redirect( 'index.php' );
        }

        if ( iMEMBER && ( fusion_get_userdata( 'user_id' ) == get( 'lookup' ) ) && !iSUPERADMIN ) {
            $action_url = FUSION_SELF.( FUSION_QUERY ? "?".FUSION_QUERY : "" );
            $ab = openform( 'delete_me', 'post', $action_url );
            $ab .= form_button( 'delete_me', $locale['uf_delete_del'], "delete_me" );
            $ab .= closeform();
            $user_fields = [
                'title' => $locale['uf_delete'],
                'value' => $ab
            ];
        }
    }
}
