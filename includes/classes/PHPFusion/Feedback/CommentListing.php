<?php
namespace PHPFusion\Feedback;

class CommentListing {
    
    private $comment;
    private $c_start = 0;
    private $can_post = FALSE;
    private $comments_per_page = 0;
    private $comments_avatar = FALSE;
    private $c_arr = [
        'c_con'  => [],
        'c_info' => [
            'c_makepagenav' => FALSE,
            'admin_link'    => FALSE
        ]
    ];
    private $comment_data = [];
    
    public function __construct( Comments $comment ) {
        $this->comment = $comment;
        $this->comments_per_page = fusion_get_settings( 'comments_per_page' );
        $this->can_post = ( iMEMBER || fusion_get_settings( 'guestposts' ) ? TRUE : FALSE );
        $this->comments_avatar = fusion_get_settings( 'comments_avatar' );
    }
    
    public function getInfo() {
        $info['comments'] = '<p class="text-center">'.fusion_get_locale( 'c101' ).'</p>';
        if ( $this->get_Comments() ) {
            $info = [
                'comment_count'   => ( $this->comment->getParams( 'comment_count' ) ? $this->c_arr['c_info']['comments_count'] : 0 ),
                'comment_ratings' => $this->getRatingsOutput(),
                'comments'        => $this->getCommentLists(),
                //'{%comments_form%}'   => $comments_form,
            ];
        }
        return $info;
    }
    
    // Build C_info and C_Con
    
    private function get_Comments() {
        
        $settings = fusion_get_settings();
        
        if ( $settings['comments_enabled'] ) {
            
            $this->setEmptyCommentData(); // $comment_data
            
            $locale = fusion_get_locale();
            
            $c_link = $this->comment->getParams( 'clink' ).( stristr( $this->comment->getParams( 'clink' ), '?' ) ? "&amp;" : '?' );
            
            $this->c_arr['c_info']['comments_count'] = format_word( 0, $locale['fmt_comment'] );
            
            $this->c_arr['c_info']['total_comments'] = dbcount( "('comment_id')", DB_COMMENTS, "comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_hidden=:comment_hidden",
                [
                    ':comment_item_id'   => $this->comment->getParams( 'comment_item_id' ),
                    ':comment_item_type' => $this->comment->getParams( 'comment_item_type' ),
                    ':comment_hidden'    => 0
                ]
            );
            
            $root_rows = (int)dbcount( "(comment_id)", DB_COMMENTS, "comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_cat=:zero AND comment_hidden=:zero2",
                [
                    ':comment_item_type' => $this->comment->getParams( 'comment_item_type' ),
                    ':comment_item_id'   => $this->comment->getParams( 'comment_item_id' ),
                    ':zero'              => 0,
                    ':zero2'             => 0,
                ] );
            
            if ( $root_rows ) {
                
                $c_start_key = 'c_start_'.$this->comment->getParams( 'comment_key' );
                
                $this->c_start = ( get( $c_start_key, FILTER_VALIDATE_INT ) ?: 0 );
                
                if ( fusion_get_settings( 'comments_sorting' ) == 'ASC' ) {
                    // Only applicable if sorting is Ascending. If descending, the default $c_start is always 0 as latest.
                    if ( !$this->c_start && $root_rows > $this->comments_per_page ) {
                        $this->c_start = ( ceil( $root_rows / $this->comments_per_page ) - 1 ) * $this->comments_per_page;
                    }
                }
                
                $comment_query = "SELECT tcm.*
                    FROM ".DB_COMMENTS." tcm
                    WHERE comment_item_id=:cid AND comment_type=:cit AND comment_hidden=:hide AND comment_cat = 0
                    ORDER BY comment_id ASC, comment_datestamp ".$settings['comments_sorting'].", comment_cat ASC LIMIT $this->c_start, $this->comments_per_page
                ";
                
                if ( $settings['ratings_enabled'] && $this->comment->getParams( 'comment_allow_ratings' ) ) {
                    $comment_query = "SELECT tcm.*, tcr.rating_vote 'ratings'
                    FROM ".DB_COMMENTS." tcm
                    LEFT JOIN ".DB_RATINGS." tcr ON tcr.rating_item_id=tcm.comment_item_id AND tcr.rating_type=tcm.comment_type
                    WHERE comment_item_id=:cid AND comment_type=:cit AND comment_hidden=:hide AND comment_cat=0
                    ORDER BY comment_id ASC, comment_datestamp ".$settings['comments_sorting'].", comment_cat ASC LIMIT $this->c_start, $this->comments_per_page
                    ";
                }
                
                $comment_bind = [
                    ':cid'  => $this->comment->getParams( 'comment_item_id' ),
                    ':cit'  => $this->comment->getParams( 'comment_item_type' ),
                    ':hide' => 0
                ];
                
                $query = dbquery( $comment_query, $comment_bind );
                
                if ( dbrows( $query ) ) {
                    if ( $root_rows > $this->comments_per_page ) {
                        // The $c_rows is different than
                        $this->c_arr['c_info']['c_makepagenav'] = makepagenav( $this->c_start, $this->comments_per_page, $root_rows, 3, $c_link, $c_start_key );
                    }
                    
                    if ( iADMIN && checkrights( 'C' ) ) {
                        $this->c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
                        $this->c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".fusion_get_aidlink()."&amp;ctype=".$this->comment->getParams( 'comment_item_type' )."&amp;comment_item_id=".$this->comment->getParams( 'comment_item_id' )."'>".$locale['c106']."</a>";
                    }
                    
                    $counter = ( $settings['comments_sorting'] == "ASC" ? $this->c_start + 1 : $root_rows - $this->c_start );
                    
                    while ( $row = dbarray( $query ) ) {
                        
                        // build C_con
                        $this->parse_comments_data( $row, $counter );
                        
                        $settings['comments_sorting'] == "ASC" ? $counter++ : $counter--;
                        
                    }
                    
                    $this->c_arr['c_info']['comments_per_page'] = $this->comments_per_page;
                    $this->c_arr['c_info']['comments_count'] = format_word( number_format( $this->c_arr['c_info']['total_comments'], 0 ), $locale['fmt_comment'] );
                }
            }
            
            return TRUE;
        }
        
        return FALSE;
    }
    
    /*
    * Parse Comment Results - build c_con
    */
    
    private function setEmptyCommentData() {
        $this->comment_data = [
            'comment_id'        => ( get( 'comment_id', FILTER_VALIDATE_INT ) ?: 0 ),
            'comment_name'      => '',
            'comment_subject'   => '',
            'comment_message'   => '',
            'comment_datestamp' => TIME,
            'comment_item_id'   => $this->comment->getParams( 'comment_item_id' ),
            'comment_type'      => $this->comment->getParams( 'comment_item_type' ),
            'comment_cat'       => 0,
            'comment_ip'        => USER_IP,
            'comment_ip_type'   => USER_IP_TYPE,
            'comment_hidden'    => 0,
        ];
    }
    
    private function parse_comments_data( $row, $i, $indent = 0 ) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        
        $garray = [];
        if ( !isnum( $row['comment_name'] ) ) {
            $garray = [
                'user_id'     => 0,
                'user_name'   => $row['comment_name'],
                'user_avatar' => '',
                'user_status' => 0,
            ];
        }
        
        $row = array_merge_recursive( $row, isnum( $row['comment_name'] ) ? fusion_get_user( $row['comment_name'] ) : $garray );
        
        $actions = [
            'edit_link'   => '',
            'delete_link' => '',
            'edit_dell'   => ''
        ];
        
        if ( ( iADMIN && checkrights( "C" ) ) || ( iMEMBER && $row['comment_name'] == $this->userdata['user_id'] && isset( $row['user_name'] ) ) ) {
            $edit_link = $this->comment->getParams( 'clink' )."&amp;c_action=edit&amp;comment_id=".$row['comment_id']."#edit_comment"; //clean_request('c_action=edit&comment_id='.$row['comment_id'], array('c_action', 'comment_id'),FALSE)."#edit_comment";
            $delete_link = $this->comment->getParams( 'clink' )."&amp;c_action=delete&amp;comment_id=".$row['comment_id']; //clean_request('c_action=delete&comment_id='.$row['comment_id'], array('c_action', 'comment_id'), FALSE);
            $data_api = \Defender::serialize( $this->comment->getParams() );
            $comment_actions = "
                            <!---comment_actions-->
                            <div class='btn-group'>
                                <a class='btn btn-xs btn-default edit-comment' data-id='".$row['comment_id']."' data-api='".$data_api."' href='$edit_link'>".$locale['edit']."</a>
                                <a class='btn btn-xs btn-danger delete-comment' data-id='".$row['comment_id']."' data-api='".$data_api."' data-type='".$this->comment->getParams( 'comment_item_type' )."' data-itemID='".$this->comment->getParams( 'comment_item_id' )."'  href='$delete_link' onclick=\"return confirm('".$locale['c110']."');\"><i class='fa fa-trash'></i> ".$locale['delete']."</a>
                            </div>
                            <!---//comment_actions-->
                            ";
            $actions = [
                "edit_link"   => [ 'link' => $edit_link, 'name' => $locale['edit'] ],
                "delete_link" => [ 'link' => $delete_link, 'name' => $locale['delete'] ],
                "edit_dell"   => $comment_actions
            ];
        }
        // Reply Form
        $reply_form = '';
        if ( $this->comment->getParams( 'comment_allow_reply' ) && ( get( 'comment_reply', FILTER_VALIDATE_INT ) == $row['comment_id'] ) && $this->can_post ) {
            
            $this->comment_data['comment_cat'] = $row['comment_id'];
    
            //if ( $this->jquery_enabled === TRUE ) {
            //    $reply_form .= "<div id='comments_reply_spinner-".$row['comment_id']."' class='spinner text-center m-b-20' style='display:none'><i class='fa fa-circle-o-notch fa-spin fa-3x'></i></div>";
            //    $reply_form .= "<div id='comments_reply_container-".$row['comment_id']."' class='comments_reply_container' ".( isset( $_GET['comment_reply'] ) && $_GET['comment_reply'] == $row['comment_id'] ? "" : "style='display:none;'" ).">";
            //}
            
            $_CAPTCHA_HTML = '';
            if ( iGUEST && ( !isset( $_CAPTCHA_HIDE_INPUT ) || ( isset( $_CAPTCHA_HIDE_INPUT ) && !$_CAPTCHA_HIDE_INPUT ) ) ) {
                $_CAPTCHA_HIDE_INPUT = FALSE;
                
                $_CAPTCHA_HTML .= '<div class="row">';
                $_CAPTCHA_HTML .= '<div class="col-xs-12 col-sm-8 col-md-6">';
                
                include INCLUDES.'captchas/'.fusion_get_settings( 'captcha' ).'/captcha_display.php';
                
                $_CAPTCHA_HTML .= display_captcha( [
                    'form_name'  => 'comments_reply_frm-'.$row['comment_id'],
                    'captcha_id' => 'reply_captcha_'.$this->comment->getParams( 'comment_key' ),
                    'input_id'   => 'reply_captcha_code_'.$this->comment->getParams( 'comment_key' ),
                    'image_id'   => 'reply_captcha_image_'.$this->comment->getParams( 'comment_key' )
                ] );
                
                $_CAPTCHA_HTML .= '</div>';
                $_CAPTCHA_HTML .= '<div class="col-xs-12 col-sm-4 col-md-6">';
                if ( !$_CAPTCHA_HIDE_INPUT ) {
                    $_CAPTCHA_HTML .= form_text( 'captcha_code', $locale['global_151'], '', [ 'required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_'.$this->comment->getParams( 'comment_key' ) ] );
                }
                $_CAPTCHA_HTML .= '</div>';
                $_CAPTCHA_HTML .= '</div>';
            }
    
            $reply_form .= openform( 'comments_reply_frm-'.$row['comment_id'], 'post', $this->comment->format_clink( $this->comment->getParams( 'clink' ) ), [
                    'class'      => 'comments_reply_form m-t-20 m-b-20',
                ]
            );
            
            ob_start();
            display_comments_reply_form();
            $reply_form .= strtr( ob_get_clean(), [
                '{%comment_name%}'    => ( iGUEST ? form_text( 'comment_name', fusion_get_locale( 'c104' ), $this->comment_data['comment_name'],
                    [
                        'max_length' => 30,
                        'input_id'   => 'comment_name-'.$row['comment_id'],
                        'form_name'  => 'comments_reply_frm-'.$row['comment_id']
                    ]
                ) : '' ),
                '{%comment_message%}' => form_textarea( "comment_message_reply", "", $this->comment_data['comment_message'],
                    [
                        "tinymce"   => "simple",
                        'autosize'  => TRUE,
                        "type"      => fusion_get_settings( "tinymce_enabled" ) ? "tinymce" : "bbcode",
                        //comments_reply_frm-1
                        "input_id"  => "comment_message-".$row['comment_id'],
                        'form_name' => 'comments_reply_frm-'.$this->comment_data['comment_cat'],
                        "required"  => TRUE
                    ] ),
                '{%comment_captcha%}' => $_CAPTCHA_HTML,
                '{%comment_post%}'    => form_button( 'post_comment', fusion_get_locale( 'c102' ), $row['comment_id'], [
                        'class'    => 'post_comment btn-success m-t-10',
                        'input_id' => 'post_comment-'.$row['comment_id']
                    ]
                )
            ] );
            
            $reply_form .= form_hidden( "comment_cat", "", $this->comment_data['comment_cat'], [ 'input_id' => 'comment_cat-'.$row['comment_id'] ] );
            $reply_form .= form_hidden( 'comment_item_type', '', $this->comment->getParams( 'comment_item_type' ), [ 'input_id' => 'comment_item_type-'.$row['comment_id'] ] );
            $reply_form .= form_hidden( 'comment_item_id', '', $this->comment->getParams( 'comment_item_id' ), [ 'input_id' => 'comment_item_id-'.$row['comment_id'] ] );
            //if ( $this->jquery_enabled ) {
            //    $reply_form .= form_hidden( "comment_key", '', $this->comment->getParams( 'comment_key' ), [ 'input_id' => 'comment_key-'.$row['comment_id'] ] );
            //    $reply_form .= form_hidden( 'comment_options', '', \Defender::serialize( $this->comment->getParams() ), [ 'input_id' => 'comment_options-'.$row['comment_id'] ] );
            //}
            $reply_form .= closeform();
            //if ( $this->jquery_enabled === TRUE ) {
            //    $reply_form .= "</div>";
            //}
            
        }
        
        // Basic numeric comment marker
        $comment_marker = $this->comment->getParams( 'comment_marker' );
        $comment_marker = $comment_marker ? $comment_marker.'.'.$i : $i;
        
        // formats $row
        $row = [
                "comment_id"        => $row['comment_id'],
                "comment_cat"       => $row['comment_cat'],
                "i"                 => $comment_marker,
                "user_avatar"       => isnum( $row['comment_name'] ) ? display_avatar( $row, '32px', '', FALSE, 'm-t-5' ) : display_avatar( [], '50px', '', FALSE, 'm-t-5' ),
                "user"              => [
                    "user_id"     => $row['user_id'],
                    "user_name"   => $row['user_name'],
                    "user_avatar" => $row['user_avatar'],
                    "status"      => $row['user_status'],
                ],
                "reply_link"        => ( $this->can_post ? $this->comment->format_clink( $this->comment->getParams( 'clink' ) ).'&amp;comment_reply='.$row['comment_id'].'#c'.$row['comment_id'] : '' ),
                "reply_form"        => $reply_form,
                'ratings'           => isset( $row['ratings'] ) ? $row['ratings'] : '',
                "comment_datestamp" => showdate( 'longdate', $row['comment_datestamp'] ),
                "comment_time"      => timer( $row['comment_datestamp'] ),
                "comment_subject"   => $row['comment_subject'],
                "comment_message"   => nl2br( parseubb( parsesmileys( $row['comment_message'] ) ) ),
                "comment_name"      => isnum( $row['comment_name'] ) ? profile_link( $row['comment_name'], $row['user_name'], $row['user_status'] ) : $row['comment_name']
            ] + $actions;
        
        // can limit and use a show more comments.
        $c_result = dbquery( "SELECT * FROM ".DB_COMMENTS." WHERE comment_cat=:comment_cat", [ ':comment_cat' => $row['comment_id'] ] );
        if ( dbrows( $c_result ) ) {
            $y = explode( '.', $i );
            $y[ $indent + 1 ] = !empty( $y[ $indent + 1 ] ) ? $y[ $indent + 1 ]++ : 1;
            $y = implode( '.', $y );
            
            while ( $c_rows = dbarray( $c_result ) ) {
                // sub replies
                $this->parse_comments_data( $c_rows, $y, $indent + 1 );
                
                $y = explode( '.', $y );
                $settings['comments_sorting'] == "ASC" ? $y[ $indent + 1 ]++ : $y[ $indent + 1 ]--;
                $y = implode( '.', $y );
            }
        }
        
        $id = $row['comment_id'];
        $parent_id = $row['comment_cat'] === NULL ? "0" : $row['comment_cat'];
        $data[ $id ] = $row;
        $this->c_arr['c_con'][ $parent_id ][ $id ] = $row;
    }
    
    private function getRatingsOutput() {
        
        if ( fusion_get_settings( 'ratings_enabled' ) && $this->comment->getParams( 'comment_allow_ratings' ) ) {
            
            if ( $this->getCommentRatings() ) {
                
                $locale = fusion_get_locale();
                $stars = '';
                $ratings = '';
                for ( $i = 1; $i <= $this->c_arr['c_info']['ratings_count']['avg']; $i++ ) {
                    $stars .= "<i class='fa fa-star text-warning fa-lg'></i>\n";
                }
                for ( $i = 5; $i >= 1; $i-- ) {
                    $ratings .= '<div>';
                    $bal = 5 - $i;
                    $ratings .= "<div class='display-inline-block m-r-5'>\n";
                    
                    for ( $x = 1; $x <= $i; $x++ ) {
                        $ratings .= "<i class='fa fa-star text-warning'></i>\n";
                    }
                    
                    for ( $b = 1; $b <= $bal; $b++ ) {
                        $ratings .= "<i class='fa fa-star-o text-lighter'></i>\n";
                    }
                    
                    $ratings .= "<span class='text-lighter m-l-5 m-r-5'>(".( $this->c_arr['c_info']['ratings_count'][ $i ] ?: 0 ).")</span>";
                    $ratings .= "</div>\n<div class='display-inline-block m-l-5' style='width:50%;'>\n";
                    $progress_num = $this->c_arr['c_info']['ratings_count'][ $i ] == 0 ? 0 : round( ( ( $this->c_arr['c_info']['ratings_count'][ $i ] / $this->c_arr['c_info']['ratings_count']['total'] ) * 100 ), 1 );
                    $ratings .= progress_bar( $progress_num, '', [ 'height' => '10px', 'hide_info' => TRUE, 'progress_class' => 'm-0' ] );
                    $ratings .= "</div>\n";
                    $ratings .= '</div>';
                }
                
                $info = [
                    'stars'          => $stars,
                    'reviews'        => format_word( $this->c_arr['c_info']['ratings_count']['total'], $locale['fmt_review'] ),
                    'ratings'        => $ratings,
                    'remove_ratings' => $this->c_arr['c_info']['ratings_remove_form'] ?: ''
                ];
                
                return display_comments_ratings( $info );
                
            }
        }
        
        return '';
    }
    
    private function getCommentRatings() {
        if ( $this->comment->getParams( 'comment_allow_ratings' ) ) {
            $r_query = "SELECT COUNT(rating_id) 'total', IF(avg(rating_vote), avg(rating_vote), 0) 'avg', SUM(IF(rating_vote='5', 1, 0)) '5', SUM(IF(rating_vote='4', 1, 0)) '4', SUM(IF(rating_vote='3', 1, 0)) '3', SUM(IF(rating_vote='2', 1, 0)) '2', SUM(IF(rating_vote='1', 1, 0)) '1'
                        FROM ".DB_RATINGS." WHERE rating_type=:rtype AND rating_item_id=:rid";
            $r_param = [ ':rtype' => $this->comment->getParams( 'comment_item_type' ), ':rid' => $this->comment->getParams( 'comment_item_id' ) ];
            $query = dbquery( $r_query, $r_param );
            $removal_form = '';
            if ( $this->comment->getParams( 'comment_allow_ratings' ) && !$this->comment->getParams( 'comment_allow_vote' ) ) {
                $locale = fusion_get_locale();
                $removal_form = openform( 'remove_ratings_frm', 'post', $this->comment->getParams( 'clink' ), [
                        'class'   => 'text-right',
                        'form_id' => $this->comment->getParams( 'comment_key' )."-remove_ratings_frm",
                    ]
                );
                $removal_form .= form_hidden( 'comment_type', '', $this->comment->getParams( 'comment_item_type' ) );
                $removal_form .= form_hidden( 'comment_item_id', '', $this->comment->getParams( 'comment_item_id' ) );
                $removal_form .= form_button( 'remove_ratings_vote', $locale['r102'], 'remove_ratings_vote', [ 'input_id' => $this->comment->getParams( 'comment_key' )."-remove_ratings_vote", 'class' => 'btn-default btn-rmRatings' ] );
                $removal_form .= closeform();
            }
            $this->c_arr['c_info']['ratings_count'] = dbarray( $query );
            $this->c_arr['c_info']['ratings_remove_form'] = $removal_form;
            
            return TRUE;
        }
        return FALSE;
    }
    
    private function getCommentLists() {
        // @bug: Split the array into page chunks. [0] for page 1, [1] for page 2
        // Display comments
        $info = [
            'comments_page'       => ( $this->c_arr['c_info']['c_makepagenav'] ? "<div class='text-left'>".$this->c_arr['c_info']['c_makepagenav']."</div>\n" : '' ),
            'comments_list'       => $this->displayComments( $this->c_arr['c_con'], 0, $this->comment->getParams() ),
            'comments_admin_link' => $this->c_arr['c_info']['admin_link']
        ];
        
        return display_comments_listing( $info );
    }
    
    /**
     * Comments Listing
     *
     * @param     $c_data
     * @param int $index
     * @param     $options
     *
     * @return string
     */
    private function displayComments( $c_data, $index = 0, $options ) {
        $locale = fusion_get_locale();
        $comments_html = '';
        //print_p(debug_backtrace());
        foreach ( $c_data[ $index ] as $comments_id => $data ) {
            $data['comment_ratings'] = '';
            if ( fusion_get_settings( 'ratings_enabled' ) && $this->comment->getParams( 'comment_allow_ratings' ) ) {
                $data['comment_ratings'] .= "<p class='ratings'>\n";
                $remainder = 5 - (int)$data['ratings'];
                for ( $i = 1; $i <= $data['ratings']; $i++ ) {
                    $data['comment_ratings'] .= "<i class='fa fa-star text-warning'></i>\n";
                }
                if ( $remainder ) {
                    for ( $i = 1; $i <= $remainder; $i++ ) {
                        $data['comment_ratings'] .= "<i class='fa fa-star-o text-lighter'></i>\n";
                    }
                }
                $data['comment_ratings'] .= "</p>\n";
            }
            $data_api = \Defender::encode( $options );
            $info = [
                'comment_id'           => 'c'.$data['comment_id'],
                'user_avatar'          => ( $this->comments_avatar ? $data['user_avatar'] : '' ),
                'user_name'            => $data['comment_name'],
                'comment_ratings'      => $data['comment_ratings'],
                'comment_subject'      => $data['comment_subject'],
                'comment_message'      => $data['comment_message'],
                'comment_date'         => $data['comment_datestamp'],
                'comment_reply_link'   => ( $data['reply_link'] ? "<a href='".$data['reply_link']."' class='comments-reply display-inline' data-id='$comments_id'>".$locale['c112']."</a>" : '' ),
                'comment_edit_link'    => ( $data['edit_link'] ? "<a href='".$data['edit_link']['link']."' class='edit-comment display-inline' data-id='".$data['comment_id']."' data-api='$data_api' data-key='".$this->comment->getParams( 'comment_key' )."'>".$data['edit_link']['name']."</a>" : '' ),
                'comment_delete_link'  => ( $data['delete_link'] ? "<a href='".$data['delete_link']['link']."' class='delete-comment display-inline' data-id='".$data['comment_id']."' data-api='$data_api' data-type='".$options['comment_item_type']."' data-item='".$options['comment_item_id']."' data-key='".$this->comment->getParams( 'comment_key' )."'>".$data['delete_link']['name']."</a>" : '' ),
                'comment_reply_form'   => ( $data['reply_form'] ?: '' ),
                'comment_sub_comments' => ( isset( $c_data[ $data['comment_id'] ] ) ? $this->displayComments( $c_data, $data['comment_id'], $options ) : '' ),
                'comment_marker'       => $data['i']
            ];
            $comments_html .= display_comments_list( $info );
        }
        
        return (string)$comments_html;
    }
    
}
