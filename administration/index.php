<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: index.php
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

use PHPFusion\Admins;
use PHPFusion\Installer\Batch_Core;

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
require_once ADMIN.'/dashboard/AdminDashboard.php';

$admin_images = TRUE;
$admin = new AdminIndex();
list( $members, $articles, $blog, $download, $forum, $photos, $news, $weblinks, $comments_type, $submit_type, $submit_link, $submit_data, $link_type, $global_infusions, $global_comments, $global_ratings, $global_submissions, $admin_icons, $upgrade_info ) = $admin->getAdminGlobals();

render_admin_dashboard();

require_once THEMES.'templates/footer.php';

/**
 * Class AdminIndex
 */
class AdminIndex {
    
    private $admin_query = [];
    
    public function __construct() {
        $this->checkAuthorization();
        $this->getAdminQueries();
        // Set administration locale
        fusion_get_locale( '', LOCALE.LOCALESET.'admin/main.php' );
    }
    
    private function checkAuthorization() {
        $aidlink = get( 'aid' );
        if ( !iADMIN || fusion_get_userdata( 'user_rights' ) == "" || !defined( "iAUTH" ) || !$aidlink || $aidlink != iAUTH ) {
            redirect( "../index.php" );
        }
    }
    
    private function getAdminQueries() {
        $a_query = [];
        if ( defined( 'ARTICLES_EXIST' ) ) {
            $a_query['article'] = "(SELECT COUNT(article_id) FROM ".DB_PREFIX."articles) AS article_items,(SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='A') AS article_comments, (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='a') AS article_submissions";
        }
        
        if ( defined( 'BLOG_EXIST' ) ) {
            $a_query['blog'] = "(SELECT COUNT(blog_id) FROM ".DB_PREFIX."blog) AS blog_items,(SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='B') AS blog_comments, (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='b') AS blog_submissions";
        }
        
        if ( defined( 'DOWNLOADS_EXIST' ) ) {
            $a_query['downloads'] = "(SELECT COUNT(download_id) FROM ".DB_PREFIX."downloads) AS download_items,(SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='D') AS download_comments,(SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='d') AS download_submissions";
        }
        
        if ( defined( 'FORUM_EXIST' ) ) {
            $a_query['forum'] = "(SELECT COUNT(forum_id) FROM ".DB_PREFIX."forums) AS forums,(SELECT COUNT(thread_id) FROM ".DB_PREFIX."forum_threads) AS threads,(SELECT COUNT(post_id) FROM ".DB_PREFIX."forum_posts) AS posts,(SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_posts > '0') AS user_posts";
        }
        
        if ( defined( 'GALLERY_EXIST' ) ) {
            $a_query['gallery'] = "(SELECT COUNT(photo_id) FROM ".DB_PREFIX."photos) AS photo_items, (SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='P') AS photo_comments, (SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='p') AS photo_submissions";
        }
        
        if ( defined( 'NEWS_EXIST' ) ) {
            $a_query['news'] = "(SELECT COUNT(news_id) FROM ".DB_PREFIX."news) AS news_items,(SELECT COUNT(comment_id) FROM ".DB_COMMENTS." WHERE comment_type='N') AS news_comments,(SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='n') AS news_submissions";
        }
        
        if ( defined( 'WEBLINKS_EXIST' ) ) {
            $a_query['weblinks'] = "(SELECT COUNT(weblink_id) FROM ".DB_PREFIX."weblinks) AS weblink_items,(SELECT COUNT(submit_id) FROM ".DB_SUBMISSIONS." WHERE submit_type='l') AS weblink_submissions";
        }
        
        if ( fusion_get_settings( 'enable_deactivation' ) ) {
            $a_query['user'] = "(SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=8) AS members_inactive";
        }
        $conditional_query = implode( ',', $a_query );
        $conditional_query = ( $conditional_query ? $conditional_query.',' : '' );
        $query = "SELECT $conditional_query
            (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status<=1 OR user_status=3 OR user_status=5) AS members_registered,
            (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=2) AS members_unactivated,
            (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=4) AS members_security_ban,
            (SELECT COUNT(user_id) FROM ".DB_USERS." WHERE user_status=5) AS members_canceled
        ";
        
        $this->admin_query = dbarray( dbquery( $query ) );
    }
    
    // Private methods
    
    public function getAdminGlobals() {
        return [
            $this->getMembers(),
            $this->getArticles(),
            $this->getBlog(),
            $this->getDownloads(),
            $this->getForums(),
            $this->getGallery(),
            $this->getNews(),
            $this->getWeblinks(),
            $this->getCommentType(),
            $this->getSubmitType(),
            $this->getSubmitLink(),
            $this->getSubmitData(),
            $this->getLinkType(),
            $this->getInfusions(),
            $this->getLatestComments(),
            $this->getLatestRatings(),
            $this->getLatestSubmissions(),
            $this->getAdminIcons(),
            $this->getUpgrades(),
        ];
    }
    
    private function getMembers() {
        return [
            'registered'   => $this->admin_query['members_registered'],
            'unactivated'  => $this->admin_query['members_unactivated'],
            'security_ban' => $this->admin_query['members_security_ban'],
            'cancelled'    => $this->admin_query['members_canceled'],
            'inactive'     => fusion_get_settings( 'enable_deactivation' ) ? $this->admin_query['members_inactive'] : 0
        ];
    }
    
    private function getArticles() {
        $info = [
            'article' => '',
            'comment' => '',
            'submit'  => '',
        ];
        if ( defined( 'ARTICLES_EXIST' ) ) {
            $info['article'] = $this->admin_query['article_items'];
            $info['comment'] = $this->admin_query['article_comments'];
            $info['submit'] = $this->admin_query['article_submissions'];
        }
        return $info;
    }
    
    private function getBlog() {
        $info = [
            'blog'    => '',
            'comment' => '',
            'submit'  => '',
        ];
        if ( defined( 'BLOG_EXIST' ) ) {
            $info['blog'] = $this->admin_query['blog_items'];
            $info['comment'] = $this->admin_query['blog_comments'];
            $info['submit'] = $this->admin_query['blog_submissions'];
        }
        return $info;
    }
    
    private function getDownloads() {
        $info = [
            'download' => '',
            'comment'  => '',
            'submit'   => '',
        ];
        if ( defined( 'DOWNLOADS_EXIST' ) ) {
            $info['download'] = $this->admin_query['download_items'];
            $info['comment'] = $this->admin_query['download_comments'];
            $info['submit'] = $this->admin_query['download_submissions'];
        }
        return $info;
    }
    
    private function getForums() {
        $info = [
            'count'  => '',
            'thread' => '',
            'post'   => '',
            'users'  => '',
        ];
        if ( defined( 'FORUM_EXIST' ) ) {
            $info['count'] = $this->admin_query['forums'];
            $info['thread'] = $this->admin_query['threads'];
            $info['post'] = $this->admin_query['posts'];
            $info['users'] = $this->admin_query['user_posts'];
        }
        return $info;
    }
    
    private function getGallery() {
        $info = [
            'photo'   => '',
            'comment' => '',
            'submit'  => '',
        ];
        if ( defined( 'GALLERY_EXIST' ) ) {
            $info['photo'] = $this->admin_query['photo_items'];
            $info['comment'] = $this->admin_query['photo_comments'];
            $info['submit'] = $this->admin_query['photo_submissions'];
        }
        return $info;
    }
    
    private function getNews() {
        $info = [
            'news'    => '',
            'comment' => '',
            'submit'  => '',
        ];
        if ( defined( 'NEWS_EXIST' ) ) {
            $info['news'] = $this->admin_query['news_items'];
            $info['comment'] = $this->admin_query['news_comments'];
            $info['submit'] = $this->admin_query['news_submissions'];
        }
        return $info;
    }
    
    private function getWeblinks() {
        $info = [
            'weblink' => '',
            'submit'  => '',
        ];
        if ( defined( 'WEBLINKS_EXIST' ) ) {
            $info['weblink'] = $this->admin_query['weblink_items'];
            $info['submit'] = $this->admin_query['weblink_submissions'];
        }
        return $info;
    }
    
    private function getCommentType() {
        $locale = fusion_get_locale();
        $comments_type = [
            'C'  => $locale['272a'],
            'UP' => $locale['UP']
        ];
        $comments_type += Admins::getInstance()->getCommentType();
        return $comments_type;
    }
    
    private function getSubmitType() {
        $submit_type = [];
        $submit_type += Admins::getInstance()->getSubmitType();
        return $submit_type;
    }
    
    private function getSubmitLink() {
        $submit_link = [];
        $submit_link += Admins::getInstance()->getSubmitLink();
        return $submit_link;
    }
    
    private function getSubmitData() {
        $submit_data = [];
        $submit_data += Admins::getInstance()->getSubmitData();
        return $submit_data;
    }
    
    private function getLinkType() {
        $settings = fusion_get_settings();
        $link_type = [
            'C'  => $settings['siteurl']."viewpage.php?page_id=%s",
            'UP' => $settings['siteurl']."profile.php?lookup=%s"
        ];
        $link_type += Admins::getInstance()->getLinkType();
        return $link_type;
    }
    
    /**
     * Infusions count
     *
     * @return array
     */
    private function getInfusions() {
        
        $infusions_count = dbcount( "(inf_id)", DB_INFUSIONS );
        $global_infusions = [];
        if ( $infusions_count ) {
            $inf_result = dbquery( "SELECT * FROM ".DB_INFUSIONS." ORDER BY inf_id ASC" );
            while ( $_inf = dbarray( $inf_result ) ) {
                if ( file_exists( INFUSIONS.$_inf['inf_folder'] ) ) {
                    $global_infusions[ $_inf['inf_id'] ] = $_inf;
                }
            }
        }
        return $global_infusions;
    }
    
    private function getLatestComments() {
        // Latest Comments
        $global_comments = [
            'data' => [],
            'rows' => dbcount( "('comment_id')", DB_COMMENTS )
        ];
        
        $rowstart = get( 'c_rowstart', FILTER_VALIDATE_INT );
        $rowstart = (int)( $rowstart <= $global_comments['rows'] ? $rowstart : 0 );
        
        $comments_result = dbquery( "SELECT c.*, u.user_id, u.user_name, u.user_status, u.user_avatar FROM ".DB_COMMENTS." c INNER JOIN ".DB_USERS." u on u.user_id=c.comment_name ORDER BY comment_datestamp DESC LIMIT $rowstart, 5" );
        
        if ( dbrows( $comments_result ) ) {
            
            while ( $_comdata = dbarray( $comments_result ) ) {
                $global_comments['data'][] = $_comdata;
            }
            
            if ( $global_comments['rows'] > 10 ) {
                $global_comments['comments_nav'] = makepagenav( $rowstart, 10, $global_comments['rows'], 2, FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0&amp;', 'c_rowstart' );
            }
            
            return $global_comments;
        }
        
        $global_comments['nodata'] = fusion_get_locale( '254c' );
        
        return $global_comments;
    }
    
    private function getLatestRatings() {
        $global_ratings = [
            'data' => [],
            'rows' => dbcount( "('rating_id')", DB_RATINGS )
        ];
        $rowstart = get( 'r_rowstart', FILTER_VALIDATE_INT );
        $rowstart = (int)( $rowstart <= $global_ratings['rows'] ? $rowstart : 0 );
        
        $result = dbquery( "SELECT r.*, u.user_id, u.user_name, u.user_status, u.user_avatar FROM ".DB_RATINGS." r INNER JOIN ".DB_USERS." u on u.user_id=r.rating_user ORDER BY rating_datestamp DESC LIMIT $rowstart, 5" );
        if ( dbrows( $result ) ) {
            
            while ( $_ratdata = dbarray( $result ) ) {
                $global_ratings['data'][] = $_ratdata;
            }
            
            if ( $global_ratings['rows'] > 10 ) {
                $global_ratings['ratings_nav'] = makepagenav( $rowstart, 10, $global_ratings['rows'], 2, FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0&amp;', 'r_rowstart' );
            }
            
            return $global_ratings;
        }
        
        $global_ratings['nodata'] = fusion_get_locale( '254b' );
        
        return $global_ratings;
    }
    
    private function getLatestSubmissions() {
        // Latest Submissions
        $global_submissions = [
            'data' => [],
            'rows' => dbcount( "('submit_id')", DB_SUBMISSIONS ),
        ];
        $rowstart = get( 's_rowstart', FILTER_VALIDATE_INT );
        $rowstart = (int)( $rowstart <= $global_submissions['rows'] ? $rowstart : 0 );
        $result = dbquery( "SELECT s.*, u.user_id, u.user_name, u.user_status, u.user_avatar FROM ".DB_SUBMISSIONS." s INNER JOIN ".DB_USERS." u on u.user_id=s.submit_user ORDER BY submit_datestamp DESC LIMIT $rowstart, 5" );
        if ( dbrows( $result ) && checkrights( 'SU' ) ) {
            while ( $_subdata = dbarray( $result ) ) {
                $global_submissions['data'][] = $_subdata;
            }
            if ( $global_submissions['rows'] > 10 ) {
                $global_submissions['submissions_nav'] = makepagenav( $rowstart, 10, $global_submissions['rows'], 2, FUSION_SELF.fusion_get_aidlink().'&amp;pagenum=0&amp;', 's_rowstart' );
            }
            return $global_submissions;
        }
        $global_submissions['nodata'] = fusion_get_locale( '254a' );
        
        return $global_submissions;
    }
    
    private function getAdminIcons() {
        $pagenum = get( 'pagenum', FILTER_VALIDATE_INT );
        $pagenum = (int)( $pagenum ?: 0 );
        $icon_param = [ ':adminpage' => $pagenum, ':language' => LANGUAGE ];
        $admin_icons = [
            'data' => [],
            'rows' => dbcount( "(admin_id)", DB_ADMIN, "admin_page=:adminpage AND admin_language=:language ORDER BY admin_page DESC, admin_id ASC, admin_title ASC", $icon_param )
        ];
        if ( $admin_icons['rows'] ) {
            $result = dbquery( "SELECT * FROM ".DB_ADMIN." WHERE admin_page=:adminpage AND admin_language=:language ORDER BY admin_page DESC, admin_id ASC, admin_title ASC", $icon_param );
            while ( $_idata = dbarray( $result ) ) {
                if ( file_exists( ADMIN.$_idata['admin_link'] ) or file_exists( INFUSIONS.$_idata['admin_link'] ) ) {
                    if ( checkrights( $_idata['admin_rights'] ) && $_idata['admin_link'] != "reserved" ) {
                        // Current locale file have the admin title definitions paired by admin_rights.
                        if ( $_idata['admin_page'] !== 5 ) {
                            $_idata['admin_title'] = ( fusion_get_locale( $_idata['admin_rights'] ) ?: $_idata['admin_title'] );
                        }
                        $admin_icons['data'][] = $_idata;
                    }
                }
            }
            return $admin_icons;
        }
        return $admin_icons;
    }
    
    private function getUpgrades() {
        Batch_Core::getInstance()->batchInfusions( TRUE );
        return Batch_Core::getInstance()->getUpgradeNotice();
    }
    
}
