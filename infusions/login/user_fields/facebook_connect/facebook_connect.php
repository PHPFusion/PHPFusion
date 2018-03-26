<?php

/**
 * Class Facebook_Authenticate
 * Facebook web login handler
 */
class Facebook_Connect extends \PHPFusion\Infusions\Login\Login {

    /**
     * Configurable Parameters as of 25/3/2018
     * https://developers.facebook.com/docs/facebook-login/web#
     *
     * @var array
     */
    private $default_settings = [
        'app_id'               => '',
        'button_width'         => '',
        'button_size'          => '',
        'enable_friends_faces' => '',
        'enable_details'       => '',
        'max_photo_rows'       => '',
        'button_text'          => '',
        'enable_logout'        => '',
    ];
    private $settings = [];

    /**
     * Display the driver settings form
     */
    public function display_settings_form() {
        $locale = fusion_get_locale('', LOGIN_LOCALESET.'user_fb_connect.php');
        $this->settings += $this->load_driver_settings('user_fb_connect');
        $this->settings += $this->default_settings;

        if (isset($_POST['save_fb'])) {
            $this->settings = [
                'app_id'               => form_sanitizer($_POST['app_id'], '', 'app_id'),
                'button_width'         => form_sanitizer($_POST['button_width'], '', 'button_width'),
                'button_size'          => form_sanitizer($_POST['button_size'], '', 'button_size'),
                'enable_friends_faces' => (isset($_POST['enable_friends_faces']) ? '1' : '0'),
                'enable_details'       => (isset($_POST['enable_details']) ? '1' : '0'),
                'max_photo_rows'       => form_sanitizer($_POST['max_photo_rows'], '', 'max_photo_rows'),
                'button_text'          => form_sanitizer($_POST['button_text'], '', 'button_text'),
                'enable_logout'        => (isset($_POST['enable_logout']) ? '1' : '0')
            ];
            // Stored as an encrypted value to protect sensitive information.
            if ($this->update_driver_settings('user_fb_connect', $this->settings)) {
                redirect(FUSION_REQUEST);
            }
        }

        echo "<div class='well'>".$locale['uf_fb_connect_200']."</div>\n";
        echo openside("<h4><i class='fab fa-facebook-square fa-lg m-r-10'></i>".$locale['uf_fb_connect_201']."</h4>");
        echo openform('facebook_settings_frm', 'post', FUSION_REQUEST);
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('app_id', $locale['uf_fb_connect_202'], $this->settings['app_id'], ['required' => TRUE, 'placeholder' => $locale['uf_fb_connect_203']]);
        echo "</div>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo "<strong>".$locale['uf_fb_connect_204']."</strong>.<br/>".$locale['uf_fb_connect_205'];
        echo "</div>\n";
        echo "</div>\n";
        echo "<hr/>\n";
        echo form_para($locale['uf_fb_connect_206'], 'cfg');
        echo "<div class='row'>\n";
        echo "<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('button_width', $locale['uf_fb_connect_207'], $this->settings['button_width'], ['placeholder' => $locale['uf_fb_connect_208']]);
        echo form_select('button_size', $locale['uf_fb_connect_209'], $this->settings['button_size'], ['options' => [
            'small'  => $locale['uf_fb_connect_210'],
            'medium' => $locale['uf_fb_connect_211'],
            'large'  => $locale['uf_fb_connect_212']
        ]]);
        echo form_checkbox('enable_friends_faces', $locale['uf_fb_connect_213'], $this->settings['enable_friends_faces'], ['reverse_label' => TRUE, 'ext_tip' => $locale['uf_fb_connect_214']]);
        echo form_checkbox('enable_details', $locale['uf_fb_connect_215'], $this->settings['enable_details'], ['reverse_label' => TRUE]);
        echo "</div><div class='col-xs-12 col-sm-6'>\n";
        echo form_text('max_photo_rows', $locale['uf_fb_connect_216'], $this->settings['max_photo_rows'], ['placeholder' => $locale['uf_fb_connect_217']]);
        echo form_select('button_text', $locale['uf_fb_connect_218'], $this->settings['button_text'], ['options' => [
            'continue_with' => $locale['uf_fb_connect_219'],
            'login_with'    => $locale['uf_fb_connect_220']
        ]]);
        echo form_checkbox('enable_logout', $locale['uf_fb_connect_221'], $this->settings['enable_logout'], ['reverse_label' => TRUE]);
        echo "</div>\n</div>\n";
        echo form_button('save_fb', $locale['uf_fb_connect_222'], 'save_fb');
        echo closeform();
        echo closeside();
    }

    public function login_authenticate($user) {
        if (isset($user['user_gauth']) && !empty($user['user_gauth'])) { // check if there are any secret code.
            $_SESSION['secret_code'] = $user['user_gauth'];
            $_SESSION['uid'] = $user['user_id'];
            echo "<script>";
            //redirect(INFUSIONS.'login/user_fields/google_auth/authentication.php');
        }

        return NULL;
    }

    public function display_login() {
        // now lets read the sttings
        $fbSettings = $this->load_driver_settings('user_fb_connect');
        //print_p($fbSettings);
        if (!empty($fbSettings['app_id'])) {
            $locale_prefix = fusion_get_locale('xml_lang').'_'.fusion_get_locale('region');
            echo "<div id='fb-root'></div>

            <script>
            $.ajaxSetup({ cache: true });            
            // Load the Javascript SDK
            window.fbAsyncInit = function() {
                FB.init({
                  appId      : '".$fbSettings['app_id']."',
                  cookie     : true,  // enable cookies to allow the server to access 
                                      // the session
                  xfbml      : true,  // parse social plugins on this page
                  version    : 'v2.8' // use graph api version 2.8
                });
                // Now that we've initialized the JavaScript SDK, we call 
                // FB.getLoginStatus().  This function gets the state of the
                // person visiting this page and can return one of three states to
                // the callback you provide.  They can be:
                //
                // 1. Logged into your app ('connected')
                // 2. Logged into Facebook, but not your app ('not_authorized')
                // 3. Not logged into Facebook and can't tell if they are logged into
                //    your app or not.
                //
                // These three cases are handled in the callback function.            
                FB.getLoginStatus(function(response) {
                  statusChangeCallback(response);
                });            
              };
              
              // Load the SDK asynchronously
            (function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = 'https://connect.facebook.net/".$locale_prefix."/sdk.js#xfbml=1&version=v2.7';
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
              
              // This is called with the results from from FB.getLoginStatus().
              function statusChangeCallback(response) {
                console.log('statusChangeCallback');
                console.log(response);
                // The response object is returned with a status field that lets the
                // app know the current login status of the person.
                // Full docs on the response object can be found in the documentation
                // for FB.getLoginStatus().
                if (response.status === 'connected') {
                  // Logged into your app and Facebook.                  
                  LoginPHPFusion();
                } else {
                  // The person is not logged into your app or we are unable to tell.
                  //document.getElementById('status').innerHTML = 'Please log ' + 'into this app.';
                  console.log('we are not able to log into this app');
                }
              }

            // Here we run a very simple test of the Graph API after login is
              // successful.  See statusChangeCallback() for when this call is made.
              function LoginPHPFusion() {
                console.log('Welcome!  Fetching your information.... ');
                FB.api('/me', function(response) {                    
                  console.log('Successful login for: ' + response.name);
                  console.log(response);
                  //document.getElementById('status').innerHTML = 'Thanks for logging in, ' + response.name + '!';
                  
                });
              }              
            </script>";
            $button_data_settings = [
                'data-width'            => $fbSettings['button_width'] ?: "",
                'data-max-rows'         => $fbSettings['max_photo_rows'] ?: "1",
                'data-size'             => $fbSettings['button_size'] ?: "",
                'data-button-type'      => $fbSettings['button_text'] ?: 'continue_with',
                'data-show-faces'       => $fbSettings['enable_friends_faces'] ? 'true' : 'false',
                'data-auto-logout-link' => $fbSettings['enable_logout'] ? 'true' : 'false',
                'data-use-continue-as'  => $fbSettings['enable_details'] ? 'true' : 'false',
            ];
            $data_attr = implode(' ', array_map(
                function ($keys, $values) {
                    if ($values) {
                        return "$keys='$values'";
                    }
                }, array_keys($button_data_settings), array_values($button_data_settings)));

            return "<div class='fb-login-button' ".$data_attr."></div>";
        }

        return NULL;
    }

}

require_once __DIR__.'/Facebook/autoload.php';