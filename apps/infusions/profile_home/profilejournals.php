<?php

namespace PHPFusion\Infusions\Profile_Home;

use PHPFusion\Template;
use PHPFusion\UserFields;


/**
 * Class ProfileHome
 */
class ProfileJournals {
    
    private $current_user_id = 0;
    
    private $profile_id = 0;
    
    private $profile_data = [];
    
    private $jcats = 'all';
    private $jcat = 0;
    
    /**
     * ProfileHome constructor.
     *
     * @param UserFields\Pages\ProfileOutput $user_fields
     */
    public function __construct( UserFields\Pages\ProfileOutput $user_fields ) {
        
        $this->current_user_id = fusion_get_userdata( 'user_id' );
        
        $this->profile_data = $user_fields->user_data;//fusion_get_user( $this->profile_id );
        
        $this->profile_id = $this->profile_data['user_id'];
        
        $this->jcats = get( 'jcats' );
        
    }
    
    
    /**
     * @return string
     * @throws \Exception
     */
    public function show() {
        $journal_id = get( 'journal', FILTER_VALIDATE_INT );
        if ( $journal_id ) {
            return $this->journalView( $journal_id );
        }
        
        return $this->journalHome();
    }
    
    private function incrementReads( $journal_id ) {
        $session_read = (int)session_get( [ 'read_journals', $journal_id ] );
        if ( !$session_read or $session_read <= TIME - 86400 ) {
            session_add( [ 'read_journals', $journal_id ], TIME );
            $sql = "UPDATE ".DB_PROFILE_JOURNALS." SET journal_reads=journal_reads+1 WHERE journal_id=:jid";
            $param = [ ':jid' => (int)$journal_id ];
            dbquery( $sql, $param );
        }
    }
    
    private function journalView( int $journal_id ) {
        
        $sql = "SELECT j.*, jc.journal_cat_id, jc.journal_cat_name, u.user_id, u.user_name, u.user_status, u.user_level, u.user_avatar
        FROM ".DB_PROFILE_JOURNALS." j
        INNER JOIN ".DB_PROFILE_JOURNAL_CATS." jc ON jc.journal_cat_id=j.journal_cat
        INNER JOIN ".DB_USERS." u ON u.user_id=j.journal_uid AND u.user_status=0
        WHERE journal_id=:jid AND journal_uid=:uid AND ".groupaccess( 'journal_visibility' )." AND ".groupaccess( 'jc.journal_cat_visibility' );
        
        $sql_param[':uid'] = $this->profile_id;
        $sql_param[':jid'] = $journal_id;
        
        $result = dbquery( $sql, $sql_param );
        if ( dbrows( $result ) ) {
            $data = dbarray( $result );
            
            $this->incrementReads( $data['journal_id'] );
            
            $tpl = Template::getInstance( 'journal' );
            $tpl->set_template( __DIR__.'/templates/journal_page.html' );
            $tpl->set_tag( 'journal_subject', $data['journal_subject'] );
            $tpl->set_tag( 'time', timer( $data['journal_datestamp'] ) );
            $tpl->set_tag( 'avatar', display_avatar( $data, '50px' ) );
            $tpl->set_tag( 'profile_link', profile_link( $data['user_id'], $data['user_name'], $data['user_status'] ) );
            $tpl->set_tag( 'datestamp', showdate( 'shortdate', $data['journal_datestamp'] ) );
            $tpl->set_tag( 'category', $data['journal_cat_name'] );
            $tpl->set_tag( 'category_link', BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals&amp;jcats=".$data['journal_cat_id'] );
            $tpl->set_tag( 'read_time', $this->readTimer( $data['journal_text'] ) );
            $tpl->set_tag( 'journal_text', parse_textarea( $data['journal_text'], TRUE, TRUE, TRUE, INFUSIONS.'profile_home/images/journals/', TRUE, TRUE ) );
            $tpl->set_tag( 'comments', $this->getComments( $data['journal_id'] ) );
            $tpl->set_tag( 'journal_reads', format_num( $data['journal_reads'] ) );
            $tpl->set_tag( 'journal_comments', format_num( dbcount( "(comment_id)", DB_COMMENTS, "comment_type=:type AND comment_item_id=:jid", [ ':jid' => $data['journal_id'], ':type' => 'JR' ] ) ) );
            $tpl->set_tag( 'journal_collections', format_num( dbcount( "(collection_id)", DB_PROFILE_JOURNAL_COLLECTIONS, "collection_item_id=:jid", [ ':jid' => $data['journal_id'] ] ) ) );
            $tpl->set_tag( 'comment_link', "#_commentsfrm" );
            
            $current_url = fusion_get_settings( 'siteurl' )."profile.php?lookup=$this->profile_id&amp;profile_page=journals&amp;journal=".$data['journal_id'];
            $media_url = "";
            //https://php-fusion.test/profile.php?lookup=16331&profile_page=journals&journal=2#
            $tpl->set_tag( 'fb_share_link', "https://www.facebook.com/sharer/sharer.php?u=".urlencode( $current_url ) );
            //print_P( html_entity_decode( "https://twitter.com/intent/tweet?url=http%3A%2F%2Ffav.me%2Fddkh1sj&text=Great%20Dog%20by%20hien81&original_referer=https%3A%2F%2Ftwitter.com%2Fshare%3Furl%3Dhttp%253A%252F%252Ffav.me%252Fddkh1sj%26text%3DGreat%2520Dog%2520by%2520hien81" ) );
            $tpl->set_tag( 'twitter_share_link', "https://twitter.com/intent/tweet?url=".urlencode( $current_url )."&amp;text=".$data['journal_subject']."&amp;original_referer=https://twitter.com/share??url=".urlencode( $current_url )."&amp;text=".$data['journal_text'] );
            //https://www.pinterest.com/pin/create/button/?url=http://fav.me/ddkh1sj&media=https://img00.deviantart.net/bb46/a/shared/poetry.jpg&description=Great Dog by hien81
            $tpl->set_tag( 'pinterest_share_link', "https://pinterest.com/pin/create/button?url=".urlencode( $current_url )."&amp;description=".$data['journal_subject']."&amp;media=".urlencode( $media_url ) );
            //https://www.tumblr.com/widgets/share/tool?canonicalUrl=http://fav.me/ddkh1sj&title=Great Dog by hien81&caption=Great Dog by hien81&posttype=link
            $tpl->set_tag( 'tumblr_share_link', "https://tumblr.com/widgets/share/tool?canonicalUrl=".urlencode( $current_url )."&amp;title=".$data['journal_subject']."&amp;caption=".$data['journal_subject']."&amp;posttype=link" );
            $tpl->set_tag( 'current_link', form_text( 'current_jr_url', 'Journal Page Url', $current_url ) );
            
            if ( iPROFILE or iADMIN && checkrights( 'M' ) ) {
                $tpl->set_block( 'journal_admin', [
                    'trash_link' => clean_request( 'action=delete', [ 'action' ], FALSE ),
                    'edit_link'  => clean_request( 'action=edit', [ 'action' ], FALSE ),
                ] );
            }
            
            //get journals
            $tpl->set_tag( 'recommended', $this->showJournals( $this->getRecommendedJournals(), __DIR__.'/templates/journal_recommended.html' ) );
            
            return $tpl->get_output();
            
        } else {
            // redirect back to profile journal page
            redirect( BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals" );
        }
        
        
    }
    
    private function getComments( int $journal_id ) {
        require_once INCLUDES.'comments_include.php';
        return showcomments( 'JR', DB_PROFILE_JOURNALS, 'journal_id', $journal_id, BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals&amp;journal=".$journal_id."&amp;", FALSE, '', FALSE );
        
    }
    
    private function readTimer( $text_length ) {
        $wpm_rate = 250;
        $words_found = str_word_count( $text_length, 0, '&' );
        $wpm_calc = $words_found ? $words_found / $wpm_rate : 0;
        if ( $wpm_calc < 1 ) {
            return 'less than 1 minute';
        }
        return format_word( $wpm_calc, 'minute|minutes' );
    }
    
    private function journalHome() {
        add_to_footer( "<script src='".INFUSIONS."profile_home/templates/js/ph.js'></script>" );
        $tpl = Template::getInstance( 'profile-home' );
        $tpl->set_template( __DIR__.'/templates/journals_layout.html' );
        $menu = $this->getMenuInfo();
        $tpl->set_tag( 'openpage', '' );
        $tpl->set_tag( 'closepage', '' );
        $tpl->set_tag( 'content', '<h3 class="text-center text-white spacer-md strong">There are no Journal Category defined.</h3>' );
        if ( !empty( $menu ) ) {
            $menu_active = tab_active( $menu, 'all', 'jcats' );
            $tpl->set_tag( 'openpage', opentab( $menu, $menu_active, 'jcatsMenu', TRUE, 'nav-pills nav-cats', 'jcats' ) );
            $tpl->set_tag( 'closepage', closetab() );
            $tpl->set_tag( 'content', $this->showJournals( $this->getCategoryJournals( (int)get( 'jcats', FILTER_VALIDATE_INT ) ) ) );
        }
        return $tpl->get_output();
    }
    
    private function showJournals( $journals, $template_path = __DIR__.'/templates/journal_item.html' ) {
        $tpl = Template::getInstance( 'journals' );
        $tpl->set_template( $template_path );
        if ( !empty( $journals ) ) {
            foreach ( $journals as $data ) {
                $wishCount = dbcount( "(collection_id)", DB_PROFILE_JOURNAL_COLLECTIONS, "collection_item_id=:iid", [ ':iid' => $data['journal_id'] ] );
                $collections = (int)$wishCount;
                $wishActive = dbcount( "(collection_id)", DB_PROFILE_JOURNAL_COLLECTIONS, "collection_item_id=:iid AND collection_user=:uid", [ ':iid' => $data['journal_id'], ':uid' => fusion_get_userdata( 'user_id' ) ] );
                $wish_icon = 'far fa-heart';
                $wish_active_class = '';
                if ( $wishActive ) {
                    $wish_icon = 'fas fa-heart';
                    $wish_active_class = ' active';
                }
                
                $item = [
                    //https://php-fusion.test/profile.php?lookup=16331&profile_page=journals
                    'avatar'          => display_avatar( $this->profile_data, '30px', '', FALSE ),
                    'link'            => BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page=journals&amp;journal='.$data['journal_id'],
                    'image'           => $this->getDefaultImage( $data['journal_subject'], '100%' ), //image
                    'title'           => $data['journal_subject'],
                    'description'     => trimlink( strip_tags( $data['journal_text'] ), 100 ),
                    'profile_link'    => "<a class='profile-link' href='".BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals'>".$this->profile_data['user_name']."</a>",
                    'ratings_text'    => $this->displayRatingsText( $data['rating_sum'], $data['rating_votes'] ), //profile_link
                    'collection_text' => '<a class="collection'.$wish_active_class.'" id="'.$data['journal_id'].'-colicom" href="#" onclick="javascript:event.preventDefault();addJournalWish('.$data['journal_id'].');"><i class="'.$wish_icon.' m-r-5"></i></a><span id="'.$data['journal_id'].'-colicount" class="colicom-count">'.$collections.'</span>',
                ];
                
                $tpl->set_block( 'item', $item );
            }
        } else {
            $tpl->set_block( 'no_item', [
                'link' => ( iPROFILE ? "<a class='submit-journal text-success small text-uppercase strong' href='".BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals&amp;action=new'>Submit a Journal</a>" : '' )
            ] );
        }
        
        return $tpl->get_output();
    }
    
    private function displayRatingsText( $ratingSum, $ratingVotes ) {
        $maxCount = 5;
        $value = 0;
        // the phpfusion max ratings is 5.
        if ( $ratingVotes ) {
            $value = $ratingSum / $ratingVotes;
        }
        $ratings = 0;
        if ( $value < $maxCount ) {
            $ratings = $maxCount - $value;
        }
        
        return '<i class="far fa-star"></i> '.$ratings;
    }
    
    /**
     * @param int $jcat_id
     *
     * @return array
     */
    private function getCategoryJournals( $jcat_id = 0 ) {
        static $journals = [];
        static $journal_cached = FALSE;
        
        if ( empty( $journals ) && !$journal_cached ) {
            $journal_cached = TRUE;
            $sql_cond = [];
            
            $sql_param[':uid'] = $this->profile_data['user_id'];
            
            if ( $total_rows = dbcount( "(journal_id)", DB_PROFILE_JOURNALS, 'journal_uid=:uid AND '.groupaccess( 'journal_visibility' ), $sql_param ) ) {
                
                if ( $this->jcats == 'draft' ) {
                    $sql_cond[] = 'journal_draft=1';
                }
                
                if ( $jcat_id ) {
                    $sql_cond[] = "journal_cat=:jid AND journal_draft=0";
                    $sql_param[':jid'] = $jcat_id;
                }
                
                $rowstart = get( 'rowstart', FILTER_VALIDATE_INT );
                $sql_param[':rowstart'] = (int)( $total_rows >= $rowstart ? $rowstart : 0 );
                $sql_cond = implode( ' AND ', $sql_cond );
                $sql_cond = $sql_cond ? " AND ".$sql_cond : '';
                
                $sql = "SELECT c.*, count(r.rating_id) 'rating_votes', sum(r.rating_vote) 'rating_sum'
                FROM ".DB_PROFILE_JOURNALS." c
                LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id=c.journal_id AND r.rating_type='JR'
                WHERE c.journal_uid=:uid AND ".groupaccess( 'c.journal_visibility' ).$sql_cond." GROUP BY c.journal_id ORDER BY c.journal_datestamp DESC LIMIT :rowstart, 24";
                
                
                $result = dbquery( $sql, $sql_param );
                
                while ( $data = dbarray( $result ) ) {
                    $journals[ $data['journal_id'] ] = $data;
                }
            }
        }
        
        return $journals;
        
    }
    
    /**
     * @return array
     */
    private function getRecommendedJournals() {
        static $r_journals = [];
        static $r_journal_cached = FALSE;
        
        if ( empty( $journals ) && !$r_journal_cached ) {
            $r_journal_cached = TRUE;
            $sql_param[':uid'] = $this->profile_data['user_id'];
            $sql_param[':tjid'] = get( 'journal', FILTER_VALIDATE_INT );
            if ( $sql_param[':tjid'] ) {
                if ( $total_rows = dbcount( "(journal_id)", DB_PROFILE_JOURNALS, 'journal_uid=:uid AND '.groupaccess( 'journal_visibility' ).' AND journal_id !=:tjid AND journal_draft=0 ', $sql_param ) ) {
                    
                    $r_rowstart = get( 'rowstart', FILTER_VALIDATE_INT );
                    $sql_param[':rowstart'] = (int)( $total_rows >= $r_rowstart ? $r_rowstart : 0 );
                    
                    $sql = "SELECT c.*, count(r.rating_id) 'rating_votes', sum(r.rating_vote) 'rating_sum'
                            FROM ".DB_PROFILE_JOURNALS." c
                            LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id=c.journal_id AND r.rating_type='JR'
                            WHERE c.journal_uid=:uid AND ".groupaccess( 'c.journal_visibility' )." AND c.journal_id !=:tjid AND c.journal_draft=0 GROUP BY c.journal_id ORDER BY c.journal_reads DESC LIMIT :rowstart, 3";
                    
                    $result = dbquery( $sql, $sql_param );
                    while ( $data = dbarray( $result ) ) {
                        $r_journals[ $data['journal_id'] ] = $data;
                    }
                }
            }
            
        }
        
        return $r_journals;
        
    }
    
    private function getMenuInfo() {
        // use a tab function?
        static $menu_list = [];
        
        $this->jcat = get( 'jcat' );
        
        if ( empty( $menu_list ) ) {
            
            $result = dbquery( "SELECT * FROM ".DB_PROFILE_JOURNAL_CATS." WHERE ".groupaccess( 'journal_cat_visibility' )." AND journal_cat_language=:lang ORDER BY journal_cat_name ASC", [ ':lang' => LANGUAGE ] );
            
            if ( dbrows( $result ) ) {
                // count articles
                $article_count = dbcount( "(journal_id)", DB_PROFILE_JOURNALS, 'journal_uid=:uid AND '.groupaccess( 'journal_visibility' ), [ ':uid' => $this->profile_data['user_id'] ] );
                $draft_count = dbcount( "(journal_id)", DB_PROFILE_JOURNALS, 'journal_uid=:uid AND '.groupaccess( 'journal_visibility' ).' AND journal_draft=1', [ ':uid' => $this->profile_data['user_id'] ] );
                
                //$count = 0;
                $menu_list['id'][] = 'all';
                $menu_list['title'][] = 'All <span class="m-l-5">'.format_num( $article_count ).'</span>';
                $menu_list['icon'][] = '';
                
                while ( $data = dbarray( $result ) ) {
                    
                    $article_count = dbcount( "(journal_id)", DB_PROFILE_JOURNALS, 'journal_uid=:uid AND '.groupaccess( 'journal_visibility' ).' AND journal_cat=:jid AND journal_draft=0', [ ':uid' => $this->profile_data['user_id'], ':jid' => $data['journal_cat_id'] ] );
                    
                    $menu_list['id'][] = $data['journal_cat_id'];
                    $menu_list['title'][] = $data['journal_cat_name'].' <span class="m-l-5">'.format_num( $article_count ).'</span>';
                    $menu_list['icon'][] = '';
                    
                }
                
                $menu_list['id'][] = 'draft';
                $menu_list['title'][] = 'Draft <span class="m-l-5">'.format_num( $draft_count ).'</span>';
                $menu_list['icon'][] = 'fad fa-eye-slash';
            }
        }
        
        if ( $this->jcats && !in_array( $this->jcats, $menu_list['id'] ) ) {
            add_notice( 'warning', "Journal Category not found" );
            redirect( BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals" );
        }
        
        return $menu_list;
    }
    
    /**
     * Generate a SVG image based on name.
     *
     * @param $item_name
     * @param $size
     *
     * @return string
     */
    public function getDefaultImage( $item_name, $size ) {
        $color = '161A1F';
        $first_char = substr( $item_name, 0, 1 );
        $first_char = ucfirst( $first_char );
        $second_char_find = explode( ' ', $item_name, 2 );
        if ( isset( $second_char_find[1] ) ) {
            $second_char = substr( $second_char_find[1], 0, 1 );
        }
        if ( isset( $second_char ) ) {
            $first_char = $first_char.strtolower( $second_char );
        } else {
            $first_char = substr( $item_name, 0, 2 );
        }
        return '
        <div class="display-block" style="margin:0;width:'.$size.';max-height:'.$size.';">
        <svg height="100%" viewBox="0 0 15 12" preserveAspectRatio="xMidYMin slice" fill-rule="evenodd">
        <linearGradient x1="87.8481761%" y1="16.3690766%" x2="45.4107524%" y2="71.4898596%" id="lit-gradient50022">
        <stop stop-color="#358CCB" offset="0%"></stop><stop stop-color="#3197EF" stop-opacity="0" offset="100%"></stop>
        </linearGradient>
        <rect fill="#'.$color.'" stroke-width="0" y="0" x="0" height="100%" width="100%"/>
        <text class="SUXNs" fill="url(#lit-gradient50022)" text-anchor="end" x="19" y="0" alignment-baseline="hanging">'.$first_char.'</text>
        </svg>
        </div>
        ';
    }
}
