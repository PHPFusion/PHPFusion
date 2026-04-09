<?php
declare( strict_types=1 );
namespace PHPFusion\Infusions\Profile_Home\Panels\Posts;

use PHPFusion\Template;

class JournalPosts {
    
    private $profile_data = [];
    private $profile_id = 0;
    
    public function __construct( $data ) {
        $this->profile_data = $data;
        $this->profile_id = $data['user_id'];
    }
    
    public function viewPanel() {
        return $this->showJournals( $this->getLatestJournals() );
    }
    
    private function showJournals( $journals ) {
        $tpl = Template::getInstance( 'journals-post' );
        $tpl->set_template( __DIR__.'/item.html' );
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
                    'avatar'          => display_avatar( $this->profile_data, '30px', '', FALSE ),
                    'link'            => BASEDIR.'profile.php?lookup='.$this->profile_id.'&amp;profile_page=journals&amp;journal='.$data['journal_id'],
                    'image'           => $this->getDefaultImage( $data['journal_subject'], '100%' ), //image
                    'title'           => $data['journal_subject'],
                    'datestamp'       => showdate( 'shortdate', $data['journal_datestamp'] ),
                    'description'     => trimlink( strip_tags( $data['journal_text'] ), 100 ),
                    'profile_link'    => "<a class='profile-link' href='".BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals'>".$this->profile_data['user_name']."</a>",
                    'ratings_text'    => $this->displayRatingsText( $data['rating_sum'], $data['rating_votes'] ), //profile_link
                    'collection_text' => '<a class="collection'.$wish_active_class.'" id="'.$data['journal_id'].'-colicom" href="#" onclick="javascript:event.preventDefault();addJournalWish('.$data['journal_id'].');"><i class="'.$wish_icon.' m-r-5"></i></a><span id="'.$data['journal_id'].'-colicount" class="colicom-count">'.$collections.'</span>',
                ];
                
                $tpl->set_block( 'item', $item );
            }
        } else {
            $tpl->set_block( 'no_item', [
                'link' => BASEDIR."profile.php?lookup=$this->profile_id&amp;profile_page=journals"
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
    
    private function getLatestJournals() {
        $journals = [];
        $sql = "SELECT c.*, count(r.rating_id) 'rating_votes', sum(r.rating_vote) 'rating_sum'
                FROM ".DB_PROFILE_JOURNALS." c
                LEFT JOIN ".DB_RATINGS." r ON r.rating_item_id=c.journal_id AND r.rating_type='JR'
                WHERE c.journal_uid=:uid AND ".groupaccess( 'c.journal_visibility' )." GROUP BY c.journal_id ORDER BY c.journal_id DESC LIMIT 1";
        $sql_param[':uid'] = (int)$this->profile_id;
        $result = dbquery( $sql, $sql_param );
        if ( dbrows( $result ) ) {
            $data = dbarray( $result );
            $journals[ $data['journal_id'] ] = $data;
        }
        return $journals;
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
