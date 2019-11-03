<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Infusions\Forum\Classes\Forum;

use PHPFusion\Infusions\Forum\Classes\Forum_Moderator;
use PHPFusion\Infusions\Forum\Classes\Forum_Server;

class viewForum extends Forum_Server {
    
    private $forum_class = NULL;
    private $forum_info = [];
    
    
    public function __construct( Forum $forum_class ) {
        $this->forum_class = $forum_class;
        $this->forum_info = $this->forum_class->getForumInfo();
    }
    
    /**
     * @return array
     */
    public function getForumViewInfo() {
        
        $userdata = fusion_get_userdata();
        
        $locale = fusion_get_locale();
        
        $forum_settings = self::get_forum_settings();
        
        $result = dbquery( "SELECT * FROM ".DB_FORUMS." WHERE forum_id=:fid", [ ':fid' => (int)$this->forum_info['forum_id'] ] );
        
        if ( dbrows( $result ) ) {
            
            require_once INCLUDES."mimetypes_include.php";
            
            $this->forum_info['forums'] = $this->forum_class::get_forums( $this->forum_info['forum_id'] );
            
            $this->addForumBreadcrumb( $this->forum_info['forum_index'], $this->forum_info['forum_id'] );
            
            $data = dbarray( $result );
            
            // This is the thread filtration pattern, and therefore should go to the thread class , not the forum class.
            $this->forum_info['filter'] = self::filter()->get_FilterInfo();
            
            $this->forum_info = array_merge( $this->forum_info, $data );
            
            Forum_Moderator::setForumMods( $this->forum_info );
            
            $permissions = $this->forum_class->setForumPermission( $this->forum_info );
            
            $this->forum_info = array_merge( $this->forum_info, $permissions );
            
            $this->forum_info['forum_moderators'] = display_forum_mods( $this->forum_info['forum_mods'] );
            
            $this->forum_info['thread_count'] = dbcount( "(thread_id)", DB_FORUM_THREADS, "forum_id=:forum_id", [ ':forum_id' => $this->forum_info['forum_id'] ] );
            
            $this->forum_info['forum_threadcount_word'] = format_word( $this->forum_info['thread_count'], $locale['fmt_thread'] );
            
            $this->forum_info['post_count'] = dbcount( "(post_id)", DB_FORUM_POSTS, "forum_id=:forum_id", [ ':forum_id' => $this->forum_info['forum_id'] ] );
            
            $this->forum_info['forum_postcount_word'] = format_word( $this->forum_info['post_count'], $locale['fmt_post'] );
            
            if ( !empty( $forum_data['forum_description'] ) ) {
                set_meta( 'description', $forum_data['forum_description'] );
            }
            
            if ( !empty( $forum_data['forum_meta'] ) ) {
                set_meta( 'keywords', $forum_data['forum_meta'] );
            }
            
            // Generate New thread link
            if ( $this->forum_class->getForumPermission( "can_post" ) && $this->forum_info['forum_type'] > 1 ) {
                $this->forum_info['new_thread_link'] = [
                    'link'  => FORUM."newthread.php?forum_id=".$this->forum_info['forum_id'],
                    'title' => $this->forum_info['forum_type'] == 4 ? $locale['forum_0058'] : $locale['forum_0057'],
                ];
            }
            
            // Forum Page link
            $this->forum_info['forum_page_link']['content'] = [
                'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$this->forum_info['forum_id'],
                'title' => $locale['forum_0015']
            ];
            
            $this->forum_info['forum_page_link']['activity'] = [
                'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$this->forum_info['forum_id']."&amp;view=activity",
                'title' => $locale['forum_0016']
            ];
            
            if ( $this->forum_info['forum_users'] ) {
                $this->forum_info['forum_page_link']['people'] = [
                    'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$this->forum_info['forum_id']."&amp;view=people",
                    'title' => $locale['forum_0017']
                ];
            }
            
            $this->forum_info['subforum_count'] = dbcount( "(forum_id)", DB_FORUMS, 'forum_cat=:forum_id', [ ':forum_id' => $this->forum_info['forum_id'] ] );
            
            if ( $this->forum_info['subforum_count'] ) {
                $this->forum_info['forum_page_link']['subforums'] = [
                    'link'  => FORUM.'index.php?viewforum&amp;forum_id='.$this->forum_info['forum_id'].'&amp;view=subforums',
                    'title' => $locale['forum_0351'],
                ];
            }
            
            // This count has been taking quite some resource
            $view_get = get( 'view' );
            
            $this->forum_info['view_get'] = ( $view_get && in_array( $view_get, [ 'subforums', 'gallery', 'people', 'activity' ] ) ? $view_get : 'default' );
            
            switch ( $this->forum_info['view_get'] ) {
                case 'subforums':
                    // Get Subforum data
                    if ( $this->forum_info['subforum_count'] ) {
                        
                        $select_column = "SELECT * FROM ".DB_FORUMS;
                        $select_cond = ( multilang_table( "FO" ) ? " WHERE ".in_group( 'forum_language', LANGUAGE )." AND " : " WHERE " )." ".groupaccess( 'forum_access' )." AND forum_cat=:forum_id";
                        $child_sql = $select_column.$select_cond;
                        $child_param = [
                            ':forum_id' => $this->forum_info['forum_id'],
                        ];
                        
                        $subforum_result = dbquery( $child_sql, $child_param );
                        
                        $refs = [];
                        
                        $list = [];
                        
                        // define what a row is
                        $row_array = [
                            'forum_new_status'       => '',
                            'last_post'              => '',
                            'forum_icon'             => '',
                            'forum_icon_lg'          => '',
                            'forum_moderators'       => '',
                            'forum_link'             => [
                                'link'  => '',
                                'title' => ''
                            ],
                            'forum_description'      => '',
                            'forum_postcount_word'   => '',
                            'forum_threadcount_word' => '',
                        ];
                        if ( dbrows( $subforum_result ) ) {
                            
                            while ( $row = dbarray( $subforum_result ) and checkgroup( $row['forum_access'] ) ) {
                                
                                // Calculate Forum New Status
                                $newStatus = "";
                                $forum_match = "\|".$row['forum_lastpost']."\|".$row['forum_id'];
                                $last_visited = ( isset( $userdata['user_lastvisit'] ) && isnum( $userdata['user_lastvisit'] ) ) ? $userdata['user_lastvisit'] : time();
                                if ( $row['forum_lastpost'] > $last_visited ) {
                                    if ( iMEMBER && ( $row['forum_lastuser'] !== $userdata['user_id'] || !preg_match( "({$forum_match}\.|{$forum_match}$)", $userdata['user_threads'] ) ) ) {
                                        $newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::getForumIcons( 'new' )."'></i></span>";
                                    }
                                }
                                
                                //Default system icons - why do i need this? Why not let themers decide?
                                switch ( $row['forum_type'] ) {
                                    case '1':
                                        $forum_icon = "<i class='".self::getForumIcons( 'forum' )." fa-fw'></i>";
                                        $forum_icon_lg = "<i class='".self::getForumIcons( 'forum' )." fa-3x fa-fw'></i>";
                                        break;
                                    case '2':
                                        $forum_icon = "<i class='".self::getForumIcons( 'thread' )." fa-fw'></i>";
                                        $forum_icon_lg = "<i class='".self::getForumIcons( 'thread' )." fa-3x fa-fw'></i>";
                                        break;
                                    case '3':
                                        $forum_icon = "<i class='".self::getForumIcons( 'link' )." fa-fw'></i>";
                                        $forum_icon_lg = "<i class='".self::getForumIcons( 'link' )." fa-3x fa-fw'></i>";
                                        break;
                                    case '4':
                                        $forum_icon = "<i class='".self::getForumIcons( 'question' )." fa-fw'></i>";
                                        $forum_icon_lg = "<i class='".self::getForumIcons( 'question' )." fa-3x fa-fw'></i>";
                                        break;
                                    default:
                                        $forum_icon = "";
                                        $forum_icon_lg = "";
                                }
                                
                                // Calculate lastpost information
                                $lastPostInfo = [
                                    'avatar'       => '',
                                    'avatar_src'   => '',
                                    'message'      => '',
                                    'profile_link' => '',
                                    'time'         => '',
                                    'date'         => '',
                                    'thread_link'  => '',
                                    'post_link'    => '',
                                ];
                                
                                if ( $forum_settings['forum_show_lastpost'] ) {
                                    if ( !empty( $row['forum_lastpostid'] ) ) {
                                        
                                        // as first_post_datestamp
                                        $last_post_sql = "SELECT post_message FROM ".DB_FORUMS." WHERE forum_id=:forum_id ORDER BY post_datestamp DESC";
                                        $last_post_param = [ ':forum_id' => $row['forum_lastpostid'] ];
                                        $post_result = dbquery( $last_post_sql, $last_post_param );
                                        if ( dbrows( $post_result ) ) {
                                            
                                            // Get the current forum last user
                                            $last_user = fusion_get_user( $forum_data['forum_lastuser'] );
                                            $post_data = dbarray( $post_result );
                                            
                                            $last_post = [
                                                'avatar'       => '',
                                                'avatar_src'   => $last_user['user_avatar'] && file_exists( IMAGES.'avatars/'.$last_user['user_avatar'] ) && !is_dir( IMAGES.'avatars/'.$last_user['user_avatar'] ) ? IMAGES.'avatars/'.$last_user['user_avatar'] : '',
                                                'message'      => trim_text( parseubb( parsesmileys( $post_data['post_message'] ) ), 100 ),
                                                'profile_link' => profile_link( $last_user['forum_lastuser'], $last_user['user_name'], $last_user['user_status'] ),
                                                'time'         => timer( $row['forum_lastpost'] ),
                                                'date'         => showdate( "forumdate", $row['forum_lastpost'] ),
                                                'thread_link'  => INFUSIONS."forum/viewthread.php?thread_id=".$row['thread_id'],
                                                'post_link'    => INFUSIONS."forum/viewthread.php?thread_id=".$row['thread_id']."&amp;pid=".$row['thread_lastpostid']."#post_".$row['thread_lastpostid'],
                                            ];
                                            if ( $forum_settings['forum_last_post_avatar'] ) {
                                                $last_post['avatar'] = display_avatar( $last_user, '30px', '', '', 'img-rounded' );
                                            }
                                            $lastPostInfo = $last_post;
                                        }
                                    }
                                }
                                
                                $row['forum_postcount'] = dbcount( "(post_id)", DB_FORUM_POSTS, "forum_id=:forum_id", [ ':forum_id' => $row['forum_id'] ] );
                                $row['forum_threadcount'] = dbcount( "(thread_id)", DB_FORUM_THREADS, "forum_id=:forum_id", [ ':forum_id' => $row['forum_id'] ] );
                                
                                $_row = array_merge( $row_array, $row, [
                                    "forum_type"             => $row['forum_type'],
                                    "forum_moderators"       => Forum_Moderator::parse_forum_mods( $row['forum_mods'] ), //// display forum moderators per forum.
                                    "forum_new_status"       => $newStatus,
                                    "forum_link"             => [
                                        "link"  => FORUM."index.php?viewforum&amp;forum_id=".$row['forum_id'],
                                        "title" => $row['forum_name']
                                    ],
                                    "forum_description"      => nl2br( parseubb( $row['forum_description'] ) ), // current forum description
                                    // @this need a count
                                    "forum_postcount_word"   => format_word( $row['forum_postcount'], $locale['fmt_post'] ), // current forum post count
                                    // @this need a count
                                    "forum_threadcount_word" => format_word( $row['forum_threadcount'], $locale['fmt_thread'] ), // thread in the current forum
                                    "last_post"              => $lastPostInfo, // last post information
                                    "forum_icon"             => $forum_icon, // normal icon
                                    "forum_icon_lg"          => $forum_icon_lg, // big icon.
                                    "forum_image"            => ( $row['forum_image'] && file_exists( FORUM."images/".$row['forum_image'] ) ) ? $row['forum_image'] : '',
                                ] );
                                
                                // child hierarchy data.
                                $thisref = &$refs[ $_row['forum_id'] ];
                                $thisref = $_row;
                                if ( $_row['forum_cat'] == $this->forum_info['forum_id'] ) {
                                    //$this->forum_info['item'][$_row['forum_id']] = &$thisref; // will push main item out.
                                    $list[ $_row['forum_id'] ] = &$thisref;
                                } else {
                                    $refs[ $_row['forum_cat'] ]['child'][ $_row['forum_id'] ] = &$thisref;
                                }
                            }
                            
                            $this->forum_info['item'][ $this->forum_info['forum_id'] ]['child'] = $list;
                        }
                    }
                    break;
                case 'gallery':
                    // Under Development for Forum 3.0
                case 'people':
                    // Under Development
                    $this->forum_info['item'] = [];
                    $this->forum_info['pagenav'] = '';
                    if ( $this->forum_info['thread_count'] ) {
                        $sql_select = DB_USERS." u INNER JOIN ".DB_FORUM_POSTS." p ON p.post_author=u.user_id";
                        $sql_cond = "p.forum_id=:forum_id";
                        $sql_param = [
                            ':forum_id' => $this->forum_info['forum_id']
                        ];
                        $this->forum_info['max_user_count'] = dbcount( "(user_id)", $sql_select, $sql_cond, $sql_param );
                        $_GET['rowstart'] = ( isset( $_GET['rowstart'] ) ) && $_GET['rowstart'] <= $this->forum_info['max_user_count'] ? $_GET['rowstart'] : 0;
                        
                        $query = "SELECT u.user_id, u.user_name, u.user_status, u.user_avatar, p.post_id, p.post_datestamp, t.thread_id, t.thread_subject, t.forum_id
                            FROM $sql_select INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id=p.thread_id AND t.forum_id=p.forum_id WHERE $sql_cond GROUP BY u.user_id ORDER BY u.user_name ASC, p.post_datestamp DESC LIMIT ".$_GET['rowstart'].", ".$this->forum_info['posts_per_page']."";
                        
                        $result = dbquery( $query, $sql_param );
                        $rows = dbrows( $result );
                        if ( $rows ) {
                            if ( $this->forum_info['max_user_count'] > $this->forum_info['posts_per_page'] ) {
                                $this->forum_info['pagenav'] = makepagenav( $_GET['rowstart'], $rows, $this->forum_info['max_user_count'], 3, FORUM.'index.php?viewforum&amp;forum_id='.$this->forum_info['forum_id'].'&amp;view=people&amp;' );
                            }
                            while ( $data = dbarray( $result ) ) {
                                $data['thread_link'] = [
                                    'link'  => FORUM.'viewthread.php?thread_id='.$data['thread_id'].'&amp;pid='.$data['post_id'].'#post_'.$data['post_id'],
                                    'title' => $data['thread_subject']
                                ];
                                $this->forum_info['item'][ $data['user_id'] ] = $data;
                                //print_p($data);
                            }
                        }
                    }
                    //Benchmark results - 0.32s
                    break;
                case 'activity':
                    // Fetch latest activity in this forum sort by the latest posts.
                    $this->forum_info['item'] = [];
                    $this->forum_info['pagenav'] = '';
                    if ( $this->forum_info['thread_count'] ) {
                        $sql_select = DB_FORUM_POSTS." p INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND p.forum_id=t.forum_id";
                        $sql_cond = "p.forum_id=:forum_id";
                        $sql_param = [
                            ':forum_id' => $this->forum_info['forum_id']
                        ];
                        $this->forum_info['max_post_count'] = dbcount( "(post_id)", $sql_select, $sql_cond, $sql_param );
                        $_GET['rowstart'] = ( isset( $_GET['rowstart'] ) && $_GET['rowstart'] <= $this->forum_info['max_post_count'] ? $_GET['rowstart'] : 0 );
                        $query = "SELECT p.*, t.thread_id, t.thread_subject FROM $sql_select WHERE $sql_cond ORDER BY p.post_datestamp DESC LIMIT ".$_GET['rowstart'].", ".$this->forum_info['posts_per_page']."";
                        // Make var for Limits
                        $result = dbquery( $query, $sql_param );
                        $rows = dbrows( $result );
                        if ( $rows ) {
                            if ( $this->forum_info['max_post_count'] > $rows ) {
                                $this->forum_info['pagenav'] = makepagenav( $_GET['rowstart'], $rows, $this->forum_info['max_post_count'], 3, FORUM.'index.php?viewforum&amp;forum_id='.$this->forum_info['forum_id'].'&amp;view=activity&amp;' );
                            }
                            $i = 0;
                            while ( $data = dbarray( $result ) ) {
                                $user = fusion_get_user( $data['post_author'] );
                                $data['post_author'] = [
                                    'user_id'     => $user['user_id'],
                                    'user_name'   => $user['user_name'],
                                    'user_status' => $user['user_status'],
                                    'user_level'  => getuserlevel( $user['user_level'] ),
                                    'user_avatar' => $user['user_avatar']
                                ];
                                $data['thread_link'] = [
                                    'link'  => FORUM.'viewthread.php?thread_id='.$data['thread_id'].'&amp;pid='.$data['post_id'].'#post_'.$data['post_id'],
                                    'title' => $data['thread_subject']
                                ];
                                if ( !$i ) {
                                    $this->forum_info['last_activity'] = [
                                        'time'    => $data['post_datestamp'],
                                        'subject' => $data['thread_subject'],
                                        'link'    => $data['thread_link']['link'],
                                        'title'   => $data['thread_link']['title'],
                                        'user'    => $data['post_author']
                                    ];
                                }
                                $this->forum_info['item'][ $data['post_id'] ] = $data;
                                $i++;
                            }
                        }
                    }
                    // Benchmarking results:
                    //logs at 0.32s render speed for 203 posts, 0.28s for 151 post (consumes between 0.00137s - 0.00185s per posts)
                    //showBenchmark(TRUE);
                    break;
                default:
                    // Make a new template, use Jquery to cut out loading time.
                    $filter_sql = self::filter()->get_filterSQL();
                    
                    $thread_info = self::thread( FALSE )->getThreadInfo( $this->forum_info['forum_id'], $filter_sql );
                    
                    $this->forum_info = array_merge_recursive( $this->forum_info, $thread_info );
            }
            
        } else {
            redirect( FORUM.'index.php' );
        }
        
        return (array)$this->forum_info;
    }
    
    
}
