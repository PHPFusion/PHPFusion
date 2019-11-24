<?php
namespace PHPFusion\Feedback;

/**
 * Class CommentsForm
 *
 * @package PHPFusion\Feedback
 */
class CommentsForm {
    
    private $comment;
    private $c_action = '';
    private $c_id = 0;
    private $c_link = '';
    private $can_post = FALSE;
    private $data = [
        'comment_id'      => 0,
        'comment_cat'     => 0,
        'comment_subject' => '',
        'comment_message' => '',
    ];
    
    private $hash = '';
    private $cc_id = '';
    private $cc_type = '';
    
    /**
     * CommentsForm constructor.
     *
     * @param Comments $comment
     */
    public function __construct( Comments $comment ) {
        $this->comment = $comment;
        $this->c_action = get( 'c_action' );
        $this->c_id = get( 'comment_id', FILTER_VALIDATE_INT );
        $this->c_link = $this->comment->format_clink( $comment->getParams( 'clink' ) );
        $this->can_post = ( iMEMBER || ( fusion_get_settings( 'guestposts' ) && !iMEMBER ) ? TRUE : FALSE );
        $this->can_post = ( $comment->getParams( 'comment_allow_post' ) ? TRUE : FALSE );
        
        // Inherited parameters
        $this->hash = $this->comment->getCommentHash();
        $this->cc_type = $this->comment->getCCType();
        $this->cc_id = $this->comment->getCCId();
    }
    
    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getInfo() {
        $locale = fusion_get_locale();
        if ( $this->can_post ) {
            if ( $this->getComments() ) {
                return [
                    'openform'      => openform( 'inputform', 'post', $this->c_link, [ 'form_id' => "$this->hash-inputform" ] ).
                        form_hidden( 'comment_id', '', '', [ 'input_id' => "$this->hash-comment_id" ] ).
                        form_hidden( 'comment_cat', '', $this->data['comment_cat'], [ 'input_id' => "$this->hash-comment_cat" ] ).
                        form_hidden( 'comment_item_id', '', $this->cc_id, [ 'input_id' => "$this->hash-comment_item_id" ] ).
                        form_hidden( 'comment_item_type', '', $this->cc_type, [ 'input_id' => "$this->hash-comment_item_type" ] ),
                    //$comments_form .= form_hidden('comment_key', '', $this->getParams('comment_key'), ['input_id' => $this->getParams('comment_key').'-comment_key']);
                    //$comments_form .= form_hidden('comment_options', '', \Defender::serialize($this->getParams()), array('input_id' => $this->getParams('comment_key').'-comment_options'));
                    'ratings_input' => $this->displayRatingsInput(),
                    'captcha_input' => $this->displayCatpchaInput(),
                    'title'         => $this->displayTitle(),
                    'subject_input' => $this->displaySubjectInput(),
                    'name_input'    => $this->displayNameInput(),
                    'user_avatar'   => display_avatar( fusion_get_userdata(), '50px', '', ( iMEMBER ? TRUE : FALSE ) ),
                    'comment_input' => form_textarea( ( $this->data['comment_cat'] ? 'comment_message_reply' : 'comment_message' ), '', $this->data['comment_message'],
                        [
                            'input_id'     => "$this->hash-comment_message",
                            'required'     => TRUE,
                            'autosize'     => TRUE,
                            'form_name'    => 'inputform',
                            'wordcount'    => FALSE,
                            'placeholder'  => $locale['c108'],
                            'type'         => ( $this->comment->getParams( 'comment_bbcode' ) ? 'bbcode' : ( $this->comment->getParams( 'comment_tinymce' ) ? 'tinymce' : 'text' ) ),
                            "tinymce"      => "simple",
                            'tinymce_skin' => $this->comment->getParams( 'comment_tinymce_skin' )
                        ]
                    ),
                    'post_button'   => form_button( 'post_comment',
                        ( $this->data['comment_id'] ? $locale['c103'] : $locale['c102'] ),
                        ( $this->data['comment_id'] ? $locale['c103'] : $locale['c102'] ),
                        [ 'class'    => 'btn-primary post_comment',
                          'input_id' => "$this->hash-post_comment"
                        ]
                    ),
                    'closeform'     => closeform()
                ];
            }
        }

        return [
            'message' => ( $this->comment->getParams( 'comment_form_message' ) ?: $locale['c105'] )
        ];
    }
    
    /**
     * @return bool
     */
    private function getComments() {
        if ( $this->c_action == 'edit' && !empty( $this->c_id ) ) {
            $sql_query = "SELECT tcm.* FROM ".DB_COMMENTS." tcm WHERE comment_id=:comment_id AND comment_item_id=:comment_item_id AND comment_type=:comment_type AND comment_hidden=:comment_hidden";
            $sql_param = [
                ':comment_id'      => (int)$this->c_id,
                ':comment_item_id' => (int)$this->cc_id,
                ':comment_type'    => $this->cc_type,
                ':comment_hidden'  => 0,
            ];
            $result = dbquery( $sql_query, $sql_param );
            if ( dbrows( $result ) ) {
                $this->data = dbarray( $result );
                // verified cc_id
                $this->cc_id = $this->data['comment_item_id'];
                $this->cc_type = $this->data['comment_item_type'];
                
                if ( ( iADMIN && checkrights( "C" ) ) || ( iMEMBER && $this->data['comment_name'] == fusion_get_userdata( 'user_id' ) && isset( $this->data['user_name'] ) ) ) {
                    // Current new clink.
                    $this->c_link = $this->comment->format_clink( $this->getParams( 'clink' )."&amp;c_action=edit&amp;comment_id=".$this->data['comment_id'] );
                }
                return TRUE;
            } else {
                redirect( clean_request( '', [ 'c_action', 'comment_id' ], FALSE ) );
            }
        }
        return TRUE;
    }
    
    /**
     * @return string
     * @throws \ReflectionException
     */
    private function displayRatingsInput() {
        // Ratings selector
        if ( fusion_get_settings( 'ratings_enabled' ) && $this->comment->getParams( 'comment_allow_ratings' ) && $this->comment->getParams( 'comment_allow_vote' ) ) {
            $locale = fusion_get_locale();
            return form_select( 'comment_rating', $locale['r106'], '',
                [
                    'input_id' => $this->getParams( 'comment_key' ).'-comment_rating',
                    'options'  => [
                        5 => $locale['r120'],
                        4 => $locale['r121'],
                        3 => $locale['r122'],
                        2 => $locale['r123'],
                        1 => $locale['r124']
                    ]
                ]
            );
        }
        return '';
    }
    
    /**
     * @return string
     * @throws \ReflectionException
     */
    private function displayCatpchaInput() {
        // Captcha for Guest
        if ( iGUEST && $this->can_post && ( !isset( $_CAPTCHA_HIDE_INPUT ) || ( isset( $_CAPTCHA_HIDE_INPUT ) && !$_CAPTCHA_HIDE_INPUT ) ) ) {
            $locale = fusion_get_locale();
            include INCLUDES.'captchas/'.fusion_get_settings( 'captcha' ).'/captcha_display.php';
            $_CAPTCHA_HIDE_INPUT = FALSE;
            $_CAPTCHA_INPUT = '<div class="row">';
            $_CAPTCHA_INPUT .= '<div class="col-xs-12 col-sm-8 col-md-6">';
            $_CAPTCHA_INPUT .= display_captcha( [
                'form_name'  => 'inputform',
                'captcha_id' => 'captcha_'.$this->hash,
                'input_id'   => 'captcha_code_'.$this->hash,
                'image_id'   => 'captcha_image_'.$this->hash
            ] );
            $_CAPTCHA_INPUT .= '</div>';
            $_CAPTCHA_INPUT .= '<div class="col-xs-12 col-sm-4 col-md-6">';
            if ( !$_CAPTCHA_HIDE_INPUT ) {
                $_CAPTCHA_INPUT .= form_text( 'captcha_code', $locale['global_151'], '', [ 'required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => "captcha_code_$this->hash" ] );
            }
            $_CAPTCHA_INPUT .= '</div>';
            $_CAPTCHA_INPUT .= '</div>';
            
            return $_CAPTCHA_INPUT;
        }
        return '';
    }
    
    /**
     * @return mixed
     */
    private function displayTitle() {
        $locale = fusion_get_locale();
        return ( $this->comment->getParams( 'comment_form_title' ) ?: $locale['c111'] );
    }
    
    /**
     * @return mixed|string
     * @throws \ReflectionException
     */
    private function displaySubjectInput() {
        $locale = fusion_get_locale();
        return ( $this->comment->getParams( 'comment_allow_subject' ) ? form_text( 'comment_subject', $locale['c113'], $this->data['comment_subject'], [ 'required' => TRUE, 'input_id' => "$this->hash-comment_subject" ] ) : '' );
    }
    
    /**
     * @return mixed|string
     * @throws \ReflectionException
     */
    private function displayNameInput() {
        $locale = fusion_get_locale();
        return ( iGUEST ? form_text( 'comment_name', $locale['c104'], '', [ 'max_length' => 30, 'required' => TRUE, 'input_id' => "$this->hash-comment_name" ] ) : '' );
    }
    
}
