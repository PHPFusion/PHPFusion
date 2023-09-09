<?php
namespace PHPFusion\Userfields\Accounts;

use PHPFusion\LegalDocs;
use PHPFusion\Userfields\UserFieldsForm;

/**
 * Class AccountsInput
 *
 * @package PHPFusion\Userfields\Accounts
 */
class AccountsForm extends UserFieldsForm {

    /**
     * User Name input field
     *
     * @return string
     */
    public function usernameInputField() {

        $locale = fusion_get_locale();

        if (iADMIN || $this->userFields->username_change) {

            return form_text( 'user_name', $locale['u127'], $this->userFields->userData['user_name'], [
                'max_length' => 30,
                'required'   => TRUE,
                'floating_label' => defined('FLOATING_LABEL'),
                'placeholder' => defined('FLOATING_LABEL') ? $locale['u127'] : '',
                'error_text' => $locale['u122'],
                'inline'     => $this->userFields->inputInline
            ] );
        }
        return form_hidden( "user_name", "", $this->userFields->userData["user_name"] );
    }

    /**
     * Shows password input field
     *
     * @return string
     */
    public function passwordInputField() {

        $locale = fusion_get_locale();

        $settings = fusion_get_settings();

        $password_strength[] = sprintf( $locale['u147'], (int)$settings['password_length'] );
        if ($settings['password_char'] or $settings['password_num'] or $settings['password_case']) {
            $strength_test = [];
            if ($settings['password_case']) {
                $strength_test[] = $locale['u147b'];
            }
            if ($settings['password_num']) {
                $strength_test[] = $locale['u147c'];
            }
            if ($settings['password_char']) {
                $strength_test[] = $locale['u147d'];
            }
            $password_strength[] = sprintf( $locale['u147a'], format_sentence( $strength_test ) );
        }
        $password_tip = format_sentence( $password_strength );


        if ($this->userFields->registration || $this->userFields->moderation) {

            return form_text( 'user_password1', $locale['u134a'], '', [
                    'type'              => 'password',
                    'autocomplete_off'  => TRUE,
                    'inline'            => $this->userFields->inputInline,
                    'max_length'        => 64,
                    'error_text'        => $locale['u134'] . $locale['u143a'],
                    'required'          => !$this->userFields->moderation,
                    'password_strength' => TRUE,
                    'ext_tip'           => $password_tip,
                    'class'             => 'm-b-15'
                ] ) .
                form_text( 'user_password2', $locale['u134b'], '', [
                    'type'             => 'password',
                    'autocomplete_off' => TRUE,
                    'inline'           => $this->userFields->inputInline,
                    'max_length'       => 64,
                    'error_text'       => $locale['u133'],
                    'required'         => !$this->userFields->moderation
                ] );
        }

        return
            form_text( 'user_password', $locale['u135a'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'inline'           => $this->userFields->inputInline,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] )
            . form_text( 'user_password1', $locale['u134'], '', [
                'type'              => 'password',
                'autocomplete_off'  => TRUE,
                'inline'            => $this->userFields->inputInline,
                'max_length'        => 64,
                'error_text'        => $locale['u133'],
                'tip'               => $locale['u147'],
                'password_strength' => TRUE,
                'class'             => 'm-b-15'
            ] )
            . form_text( 'user_password2', $locale['u134b'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'inline'           => $this->userFields->inputInline,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] )

            . form_hidden( 'user_hash', '', $this->userFields->userData['user_password'] );


    }

    /**
     * Admin Password - not available for everyone except edit profile.
     *
     * @return string
     */
    public function adminpasswordInputField() {
        $locale = fusion_get_locale();

        if (!$this->userFields->registration && iADMIN && !defined( 'ADMIN_PANEL' )) {

            //$this->userFields->info['user_admin_password'] = form_para( $locale['u131'], 'adm_password', 'profile_category_name' );

            if ($this->userFields->userData['user_admin_password']) {
                // This is for changing password

                return
                    form_text( 'user_admin_password', $locale['u144a'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => TRUE,
                            'inline'           => $this->userFields->inputInline,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136'],
                            'class'            => 'm-b-15'
                        ]
                    )
                    . form_text( 'user_admin_password1', $locale['u144'], '', [
                            'type'              => 'password',
                            'autocomplete_off'  => TRUE,
                            'inline'            => $this->userFields->inputInline,
                            'max_length'        => 64,
                            'error_text'        => $locale['u136'],
                            'tip'               => $locale['u147'],
                            'password_strength' => TRUE,
                            'class'             => 'm-b-15'
                        ]
                    )
                    . form_text( 'user_admin_password2', $locale['u145'], '', [

                            'type'             => 'password',
                            'autocomplete_off' => TRUE,
                            'inline'           => $this->userFields->inputInline,
                            'max_length'       => 64,
                            'error_text'       => $locale['u136'],
                            'class'            => 'm-b-15'
                        ]
                    );

            }

            // This is just setting new password off blank records
            return form_text( 'user_admin_password', $locale['u144'], '', [
                        'type'              => 'password',
                        'autocomplete_off'  => TRUE,
                        'password_strength' => TRUE,
                        'inline'            => $this->userFields->inputInline,
                        'max_length'        => 64,
                        'error_text'        => $locale['u136'],
                        'ext_tip'           => $locale['u147'],
                        'class'             => 'm-b-15'

                    ]
                ) .
                form_text( 'user_admin_password2', $locale['u145'], '', [
                        'type'             => 'password',
                        'autocomplete_off' => TRUE,
                        'inline'           => $this->userFields->inputInline,
                        'max_length'       => 64,
                        'error_text'       => $locale['u136'],
                        'class'            => 'm-b-15'
                    ]
                );
        }
        return '';
    }

    /**
     * Email input
     *
     * @return string
     */
    public function emailInputField() {
        $locale = fusion_get_locale();
        $ext_tip = '';
        if (!$this->userFields->registration) {
            $ext_tip = (iADMIN && checkrights( 'M' )) ? '' : $locale['u100'];
        }

        return form_text( 'user_email', $locale['u128'], $this->userFields->userData['user_email'], [
            'type'           => 'email',
            "required"       => TRUE,
            'inline'         => $this->userFields->inputInline,
            'floating_label' => defined( 'FLOATING_LABEL' ),
            'max_length'     => '100',
            'error_text'     => $locale['u126'],
            'ext_tip'        => $ext_tip,
            'placeholder'    => defined('FLOATING_LABEL') ? 'john.doe@mail.com' : '',
        ] );


        /*
        add_to_jquery("
        var current_email = $('#user_email').val();
        $('#user_email').on('input change propertyChange paste', function(e){
            if (current_email !== $(this).val()) {
                $('#user_password_verify-field').removeClass('display-none');
            } else {
            $('#user_password_verify-field').addClass('display-none');
            }
        });
        ");
        */

    }

    public function emailHideInputField() {
        $locale = fusion_get_locale();

        return form_checkbox( 'user_hide_email', $locale['u051'], $this->userFields->userData['user_hide_email'], [
            'inline'  => FALSE,
            'toggle'  => TRUE,
            'ext_tip' => $locale['u106']
        ] );
    }

    public function phoneHideInputField() {
        $locale = fusion_get_locale();

        return form_checkbox( 'user_hide_phone', $locale['u107'], $this->userFields->userData['user_hide_phone'], [
            'inline'  => FALSE,
            'toggle'  => TRUE,
            'ext_tip' => $locale['u108']
        ] );
    }


    /**
     * Avatar input
     *
     * @return string
     */
    public function avatarInput() {

        // Avatar Field
        if (!$this->userFields->registration) {
            $locale = fusion_get_locale();

            if (isset( $this->userFields->userData['user_avatar'] ) && $this->userFields->userData['user_avatar'] != "") {

                return "<div class='row'><div class='col-xs-12 col-sm-3'>
                        <strong>" . $locale['u185'] . "</strong></div>
                        <div class='col-xs-12 col-sm-9'>
                        <div class='p-l-10'>
                        <label for='user_avatar_upload'>" . display_avatar( $this->userFields->userData, '150px', '', FALSE, 'img-thumbnail' ) . "</label>
                        <br>
                        " . form_checkbox( "delAvatar", $locale['delete'], '', ['reverse_label' => TRUE] ) . "
                        </div>
                        </div></div>
                        ";
            }

            return form_fileinput( 'user_avatar', $locale['u185'], '', [
                'upload_path'     => IMAGES . "avatars/",
                'input_id'        => 'user_avatar_upload',
                'type'            => 'image',
                'max_byte'        => fusion_get_settings( 'avatar_filesize' ),
                'max_height'      => fusion_get_settings( 'avatar_width' ),
                'max_width'       => fusion_get_settings( 'avatar_height' ),
                'inline'          => $this->userFields->inputInline,
                'thumbnail'       => 0,
                "delete_original" => FALSE,
                'class'           => 'm-t-10 m-b-0',
                "error_text"      => $locale['u180'],
                "template"        => "modern",
                'ext_tip'         => sprintf( $locale['u184'], parsebytesize( fusion_get_settings( 'avatar_filesize' ) ), fusion_get_settings( 'avatar_width' ), fusion_get_settings( 'avatar_height' ) )
            ] );


        }
        return '';

    }

    /**
     * Display Captcha
     *
     * @return string
     */
    public function captchaInput() {

        $locale = fusion_get_locale();

        if ($this->userFields->displayValidation == 1 && $this->userFields->moderation == 0) {

            $_CAPTCHA_HIDE_INPUT = FALSE;

            include INCLUDES . "captchas/" . fusion_get_settings( "captcha" ) . "/captcha_display.php";

            $html = "<div class='form-group row'>";
            $html .= "<label for='captcha_code' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3'>" . $locale['u190'] . " <span class='required'>*</span></label>";
            $html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>";

            $html .= display_captcha( [
                'captcha_id' => 'captcha_userfields',
                'input_id'   => 'captcha_code_userfields',
                'image_id'   => 'captcha_image_userfields'
            ] );

            if ($_CAPTCHA_HIDE_INPUT === FALSE) {
                $html .= form_text( 'captcha_code', '', '', [
                    'inline'           => 1,
                    'required'         => 1,
                    'autocomplete_off' => TRUE,
                    'width'            => '200px',
                    'class'            => 'm-t-15',
                    'placeholder'      => $locale['u191']
                ] );
            }
            $html .= "</div></div>";
            return $html;
        }

        return '';
    }

    /**
     * Display Terms of Agreement Field
     *
     * @return string
     */
    public function termInput() {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale( '', [LOCALE . LOCALESET . 'policies.php'] );

        if ($this->userFields->displayTerms == 1) {

            if ($_policy = LegalDocs::getInstance()->getPolicies( 3 )) {

                if (isset( $_policy['ups'] )) {
                    $policies[] = '<a href="' . BASEDIR . 'legal.php?type=ups" target="_blank">' . $_policy['ups'] . '</a>';
                }

                if (isset( $_policy['pps'] )) {
                    $policies[] = '<a href="' . BASEDIR . 'legal.php?type=pps" target="_blank">' . $_policy['pps'] . '</a>';
                }

                if (isset( $_policy['cps'] )) {
                    $policies[] = '<a href="' . BASEDIR . 'legal.php?type=cps" target="_blank">' . $_policy['cps'] . '</a>';
                }
            }

            if (isset( $policies )) {
                add_to_jquery( "     
                
                let registerTermsFn = () => {
                    let btnDOM = $('button[name=\"" . $this->userFields->postName . "\"]');                                
                    if (btnDOM.length) {
                        btnDOM = $(btnDOM[0]);                
                        btnDOM.attr('disabled', true).addClass('disabled');            
                        $('#agreement').on('click', function() {                
                            if ($(this).is(':checked')) {
                                btnDOM.attr('disabled', false).removeClass('disabled');
                            } else {
                                btnDOM.attr('disabled', true).addClass('disabled');       
                            }        
                        });
                    }
                }
                
                registerTermsFn();                                    
                " );

                return form_checkbox( 'agreement', sprintf( strtr( $locale['u193'], ['[SITENAME]' => $settings['sitename']] ), format_sentence( $policies ) ), '', ["required" => TRUE, "reverse_label" => TRUE, 'inline' => FALSE] );;
            }

        }

        return '';
    }

    /**
     * @return string
     */
    public function renderButton() {

        $disabled = $this->userFields->displayTerms == 1;

        $this->userFields->options += $this->userFields->defaultInputOptions;

//        $html = (!$this->userFields->skipCurrentPass) ? form_hidden( 'user_hash', '', $this->userFields->userData['user_password'] ) : '';

        return
            form_hidden( $this->userFields->postName, '', 'submit' ) .
            form_button( $this->userFields->postName . '_btn', $this->userFields->postValue, 'submit', [
                    "deactivate" => $disabled,
                    "class"      => $this->userFields->options['btn_post_class'] ?? 'btn-primary'
                ]
            );

    }
}
