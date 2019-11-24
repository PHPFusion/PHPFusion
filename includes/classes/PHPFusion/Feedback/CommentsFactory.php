<?php
namespace PHPFusion\Feedback;

abstract class CommentsFactory {
    
    protected static $instance = NULL;
    
    /**
     * @var array
     * comment_item_type -
     * comment_db -
     * comment_item_id -
     * clink -
     * comment_allow_reply - enable or disable reply of others comments
     * comment_allow_post - enable or disable posting of comments
     * comment_allow_ratings - enable or disable ratings
     * comment_allow_vote - enable or disable voting
     * comment_once - each user can only comment once (replying a comment is unaffected)
     * comment_echo - to echo the output if true
     * comment_title - display the comment block title
     * comment_count - display the current comment count
     */
    protected $params = [
        'comment_user'                     => '',
        'comment_item_type'                => '',
        'comment_db'                       => '',
        'comment_col'                      => '',
        'comment_item_id'                  => '',
        'clink'                            => '',
        'comment_marker'                 => 0,
        'comment_allow_subject'          => TRUE,
        'comment_allow_reply'            => TRUE,
        'comment_allow_post'             => TRUE,
        'comment_allow_ratings'          => FALSE,
        'comment_allow_vote'             => TRUE,
        'comment_once'                   => FALSE,
        'comment_echo'                   => FALSE,
        'comment_title'                  => '',
        'comment_form_title'             => '',
        'comment_form_message'           => '',
        'comment_count'                  => TRUE,
        'comment_ui_template'            => 'display_comments_ui',
        'comment_template'               => 'display_comments_section',
        'comment_form_template'          => 'display_comments_form',
        'comment_bbcode'                 => TRUE,
        'comment_tinymce'                => FALSE,
        'comment_tinymce_skin'           => 'lightgray',
        'comment_custom_script'          => FALSE,
        'comment_post_callback_function' => '', // trigger custom functions during post comment event
        'comment_edit_callback_function'   => '',  // trigger custom functions during reply event
        'comment_delete_callback_function' => '' // trigger custom functions during delete event
    ];
    protected $userdata = [];
    protected $postLink = '';
    protected $jquery = FALSE;
    
    public function __construct() {
        $this->jquery = FALSE; //fusion_get_settings('comments_jquery') ? TRUE : FALSE;
        $this->userdata = fusion_get_userdata();
        $this->postLink = FUSION_SELF.( FUSION_QUERY ? "?".FUSION_QUERY : "" );
        $this->postLink = preg_replace( "^(&amp;|\?)c_action=(edit|delete)&amp;comment_id=\d*^", "", $this->postLink );
    }
    
    public function getCommentHash() {
        return sha1( $this->params['comment_item_type'].'.'.$this->params['comment_item_id'].'.'.time() );
    }
    
    public function getCCId() {
        return $this->params['comment_item_id'];
    }
    
    public function getCCType() {
        return $this->params['comment_item_type'];
    }
    
    /**
     * Get Comment Object Parameter
     *
     * @param null $key - null for all array
     *
     * @return null
     */
    public function getParams( $key = NULL ) {
        if ( $key !== NULL ) {
            return isset( $this->params[ $key ] ) ? $this->params[ $key ] : NULL;
        }
        
        return $this->params;
    }
    
    /**
     * Set Comment Object Parameters
     *
     * @param array $params
     */
    protected function setParams( array $params = [] ) {
        $this->params = $params;
    }
    
    /**
     * Replace Comment Object Parameter
     *
     * @param $param
     * @param $value
     */
    public function replaceParam( $param, $value ) {
        if ( isset( $this->params[ $param ] ) ) {
            $this->params[ $param ] = $value;
        }
    }
    
    
}
