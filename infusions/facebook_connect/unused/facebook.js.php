<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: facebook/facebook.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class Facebook_Authenticate
 * Facebook web login handler
 */
class Facebook_JS {

    private static $locale = [];
    private static $site_settings = [];

    public function __construct() {
        self::$locale = fusion_get_locale();
        self::$site_settings = fusion_get_settings();
    }

    public static function get_locale() {
        return self::$locale;
    }

    /**
     * Configurable Parameters as of 25/3/2018
     * https://developers.facebook.com/docs/facebook-login/web#
     *
     * @var array
     */
    private $default_settings = [
        'app_id'               => '',
        'app_secret'           => '',
        "app_user_fields"      => "",
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
     * DEPRECATED FORM
     */
    public function display_settings_form() {

        $this->settings += $this->load_driver_settings('facebook');
        $this->settings += $this->default_settings;

        if (isset($_POST['save_fb'])) {
            $this->settings = [
                'app_id'               => form_sanitizer($_POST['app_id'], '', 'app_id'),
                'app_secret'           => form_sanitizer($_POST['app_secret'], '', 'app_secret'),
                'button_width'         => form_sanitizer($_POST['button_width'], '', 'button_width'),
                'button_size'          => form_sanitizer($_POST['button_size'], '', 'button_size'),
                'enable_friends_faces' => (isset($_POST['enable_friends_faces']) ? '1' : '0'),
                'enable_details'       => (isset($_POST['enable_details']) ? '1' : '0'),
                'max_photo_rows'       => form_sanitizer($_POST['max_photo_rows'], '', 'max_photo_rows'),
                'button_text'          => form_sanitizer($_POST['button_text'], '', 'button_text'),
                'enable_logout'        => (isset($_POST['enable_logout']) ? '1' : '0'),
            ];
            // Stored as an encrypted value to protect sensitive information.
            if ($this->update_driver_settings('facebook', $this->settings)) {
                redirect(FUSION_REQUEST);
            }
        }

        $html = "<div class='well'>".self::$locale['uf_fb_connect_200']."</div>\n";
        $html .= "<h4><i class='fab fa-facebook-square fa-lg m-r-10'></i>".self::$locale['uf_fb_connect_201']."</h4><hr/>";
        $html .= openform('facebook_settings_frm', 'post', FUSION_REQUEST);
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-6'>\n";
        $html .= form_text('app_id', self::$locale['uf_fb_connect_202'], $this->settings['app_id'], ['required' => TRUE, 'placeholder' => self::$locale['uf_fb_connect_204']]);
        $html .= form_text('app_secret', self::$locale['uf_fb_connect_203'], $this->settings['app_secret'], ['required' => TRUE]);
        $html .= "</div>\n";
        $html .= "<div class='col-xs-12 col-sm-6'>\n";
        $html .= "<strong>".self::$locale['uf_fb_connect_205']."</strong>.<br/>".self::$locale['uf_fb_connect_206'];
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= "<hr/>\n";
        $html .= form_para(self::$locale['uf_fb_connect_207'], 'cfg');
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-6'>\n";
        $html .= form_text('button_width', self::$locale['uf_fb_connect_208'], $this->settings['button_width'], ['placeholder' => self::$locale['uf_fb_connect_209']]);
        $html .= form_select('button_size', self::$locale['uf_fb_connect_210'], $this->settings['button_size'], ['options' => [
            'small'  => self::$locale['uf_fb_connect_211'],
            'medium' => self::$locale['uf_fb_connect_212'],
            'large'  => self::$locale['uf_fb_connect_213']
        ]]);
        $html .= form_checkbox('enable_friends_faces', self::$locale['uf_fb_connect_214'], $this->settings['enable_friends_faces'], ['reverse_label' => TRUE, 'ext_tip' => self::$locale['uf_fb_connect_215']]);
        $html .= form_checkbox('enable_details', self::$locale['uf_fb_connect_216'], $this->settings['enable_details'], ['reverse_label' => TRUE]);
        $html .= "</div><div class='col-xs-12 col-sm-6'>\n";
        $html .= form_text('max_photo_rows', self::$locale['uf_fb_connect_217'], $this->settings['max_photo_rows'], ['placeholder' => self::$locale['uf_fb_connect_218']]);
        $html .= form_select('button_text', self::$locale['uf_fb_connect_219'], $this->settings['button_text'], ['options' => [
            'continue_with' => self::$locale['uf_fb_connect_220'],
            'login_with'    => self::$locale['uf_fb_connect_221']
        ]]);
        $html .= form_checkbox('enable_logout', self::$locale['uf_fb_connect_222'], $this->settings['enable_logout'], ['reverse_label' => TRUE]);
        $html .= "</div>\n</div>\n<hr/>";
        $html .= form_button('save_fb', self::$locale['uf_fb_connect_223'], 'save_fb');
        $html .= closeform();

        return (string)$html;
    }

    /**
     * Verification page
     * @return string
     */
    public function display_verification() {
        if (isset($_REQUEST['code'])) {
            $locale = fusion_get_locale('', LOGIN_LOCALESET.'user_fb_connect.php');
            $code = \Defender::decrypt_string($_REQUEST['code'], SECRET_KEY_SALT);
            $token = json_decode($code, TRUE);
            $tpl = \PHPFusion\Template::getInstance('verify_facebook');
            $tpl->set_template(__DIR__.'/templates/connect_verify.html');
            $tpl->set_tag('title', $locale['uf_fb_connect_verify']);
            $tpl->set_tag('back_title', $locale['uf_fb_connect_406']);
            $tpl->set_tag('back_link', BASEDIR.'index.php');
            if (!empty($token['datestamp']) && !empty($token['user_id']) && !empty($token['email_address'])) {
                if (isnum($token['user_id'])) {
                    if (isnum($token['datestamp']) && $token['datestamp'] >= TIME - (3600 * 24 * 3)) {
                        $email_address = stripinput($token['email_address']);
                        $user_id = $token['user_id'];
                        $sql_cond = "user_facebook=:email AND user_id=:uid";
                        $sql_param = [
                            ':email' => "unverified:".$email_address,
                            ':uid'   => $user_id
                        ];
                        if (dbcount("(user_id)", DB_USERS, $sql_cond, $sql_param)) {
                            dbquery("UPDATE ".DB_USERS." SET user_facebook=:fem WHERE user_id=:uid", [
                                ":uid" => $user_id,
                                ":fem" => $email_address
                            ]);
                            $tpl->set_block('success', ['title' => $locale['uf_fb_connect_300'], 'description' => $locale['uf_fb_connect_301']]);
                        } else {
                            $tpl->set_block('error', ['title' => $locale['uf_fb_connect_302'], 'description' => $locale['uf_fb_connect_303']]);
                        }
                    } else {
                        $tpl->set_block('error', ['title' => $locale['uf_fb_connect_304'], 'description' => $locale['uf_fb_connect_305']]);
                    }
                } else {
                    $tpl->set_block('error', ['title' => $locale['uf_fb_connect_306'], 'description' => $locale['uf_fb_connect_307']]);
                }
            } else {
                $tpl->set_block('error', ['title' => $locale['uf_fb_connect_306'], 'description' => $locale['uf_fb_connect_307']]);
            }
            return (string) $tpl->get_output();
        }
    }

    /**
     * Displays the connector method on Account Settings Page
     */
    public function display_connector() {

        if (iMEMBER) {

            $field_value = fusion_get_userdata("user_facebook");
            if (empty($field_value) || stristr($field_value, "unverified:")) {
                // here do the API
                $fbSettings = $this->load_driver_settings('facebook');
                if (!empty($fbSettings['app_id'])) {
                    $app_info = $this->get_fb_app($fbSettings);
                    if (!empty($app_info) && is_object($app_info)) {
                        // Application have been verified by Facebook.
                        $app_id = $app_info->id;
                        $locale_prefix = self::$locale['xml_lang']."_".self::$locale['region'];
                        $redirect_link = FUSION_REQUEST;
                        echo "<div id='fb-root'></div>
                         <script>
                        $.ajaxSetup({ cache: true });
                        // Load the Javascript SDK
                        window.fbAsyncInit = function() {
                            FB.init({
                              appId      : '$app_id',
                              cookie     : true,  // enable cookies to allow the server to access
                              xfbml      : true,  // parse social plugins on this page
                              version    : 'v2.8' // use graph api version 2.8
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
                            //console.log('statusChangeCallback');
                            if (response.status === 'connected') {
                                //console.log('User is connected with $app_id');
                                // We will use this because this is the most comprehensive token authentication source provided by Facebook.
                                // Src official issued press statement method - https://www.facebook.com/FacebookforDevelopers/videos/10152795636318553/
                                //https://developers.facebook.com/docs/graph-api/reference/v2.12/debug_token
                                FB.api(
                                '/debug_token?input_token='+response.authResponse.accessToken,
                                function (response) {
                                    //console.log('Authenticating...');
                                    if (response && !response.error) {
                                        /* handle the result */
                                        if (response.data.is_valid === true && response.data.app_id === '$app_id') {
                                            // this means that the current access token is issued by PHP Fusion.
                                            // authenticate the user or register the user.
                                            //console.log('User has given permissions. Now authenticate.');
                                            facebookAuthentication();
                                        }
                                    } else {
                                        // we encountered an error.
                                        console.log('We have encountered an error and may not be able to proceed to log you in. User need to click button again.');
                                    }
                                }
                                );
                            } else {
                                console.log('User is not connected to Facebook.');
                            }
                          }

                          // Here we run a very simple test of the Graph API after login is
                          // successful.  See statusChangeCallback() for when this call is made.
                          function facebookAuthentication() {
                             //console.log('Welcome!  Fetching your information.... ');
                             FB.api('/me?fields=id,cover,name,first_name,last_name,locale,timezone,email', function(response) {
                                 response['skip_auth'] = false;
                                 //console.log(response);
                                var file_url = '".rtrim(fusion_get_settings('site_path'), '/')."/includes/login/facebook/facebook_auth.php';
                                 $.ajax({
                                       'url': file_url,
                                       'data': response,
                                       'dataType': 'json',
                                       'success': function(e) {
                                           //console.log('Getting Authentication Response...');
                                           //console.log(e);
                                           if (e.response) {
                                               //alert('$redirect_link');
                                               window.location = '$redirect_link';
                                           }
                                       },
                                       'error' : function(e) {
                                           console.log('Facebook Authentication Error');
                                       }
                                 });
                            });
                          }

                          // Check login function
                          function fusion_login() {
                             FB.getLoginStatus(function(response) {
                                statusChangeCallback(response);
                             });
                          }
                        </script>";
                        add_to_jquery("
                        $('#facebook_connect').bind('click', function(e) {
                            fusion_login();
                          });
                        ");

                        return form_button("facebook_connect", "Connect with your Facebook account", "facebook_connect");
                    }
                }

            } else {
                if (isset($_POST['facebook_disconnect'])) {
                    dbquery("UPDATE ".DB_USERS." SET user_facebook='' WHERE user_id=:uid", [":uid" => fusion_get_userdata("user_id")]);
                    add_notice("success", "Facebook has been disconnected from your account");
                    redirect(FUSION_REQUEST);
                }

                return openform('facebook_frm', 'post', FUSION_REQUEST).form_button("facebook_disconnect", "Disconnect your Facebook account", "facebook_disconnect").closeform();
            }

        }
    }

    public function login_authenticate($user) {
        if (isset($user['user_gauth']) && !empty($user['user_gauth'])) { // check if there are any secret code.
            $_SESSION['secret_code'] = $user['user_gauth'];
            $_SESSION['uid'] = $user['user_id'];
            //echo "<script>";
            //redirect(INFUSIONS.'login/user_fields/google_auth/authentication.php');
        }

        return NULL;
    }

    /**
     * Verify User Facebook Login Settings data with Facebook
     *
     * @param $fbSettings
     *
     * @return mixed
     */
    public function get_fb_app($fbSettings) {
        $url = "https://graph.facebook.com/app/?access_token=".$fbSettings['app_id']."|".$fbSettings['app_secret']."&client_credentials=client_credentials";
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // $output contains the output string
        $output = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);

        return json_decode($output);

    }

    /**
     * Display the Facebook Login Button
     * Using the Facebook JS SDK to prevent massive code library commits and syncs
     *
     * @param array $options
     *
     * @return null|string
     */
    public function display_login(array $options = []) {

        $default_options = [
            'skip_auth'          => FALSE,
            'display_connection' => FALSE,
            'redirect_link'      => '',
        ];

        $options += $default_options;

        $fbSettings = $this->load_driver_settings('facebook');
        if (!empty($fbSettings['app_id'])) {
            $app_info = $this->get_fb_app($fbSettings);
            if (!empty($app_info) && is_object($app_info)) {
                // Application have been verified by Facebook.
                $app_id = $app_info->id;
                $locale_prefix = self::$locale['xml_lang']."_".self::$locale['region'];
                /**
                 * Optional through globals.
                 * $_GET['rel'] - /{SITE_PATH}/redirect-file.php
                 */
                // FB connect should not redirect. Let auth handle it.
                $redirect_link = ($options['redirect_link'] ?: self::$site_settings['siteurl'].(isset($_GET['rel']) ? ltrim((str_replace(self::$site_settings['site_path'], '', substr($_GET['rel'], -1) !== '/' ? $_GET['rel'] : $_GET['rel'].'index.php')), '/') : 'index.php'));

                // Facebook Javascript SDK
                echo "<div id='fb-root'></div>
                <script>
                $.ajaxSetup({ cache: true });
                // Load the Javascript SDK
                window.fbAsyncInit = function() {
                    FB.init({
                      appId      : '$app_id',
                      cookie     : true,  // enable cookies to allow the server to access
                      xfbml      : true,  // parse social plugins on this page
                      version    : 'v2.8' // use graph api version 2.8
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
                    //console.log('statusChangeCallback');
                    if (response.status === 'connected') {
                        //console.log('User is connected with $app_id');
                        // We will use this because this is the most comprehensive token authentication source provided by Facebook.
                        // Src official issued press statement method - https://www.facebook.com/FacebookforDevelopers/videos/10152795636318553/
                        //https://developers.facebook.com/docs/graph-api/reference/v2.12/debug_token
                        FB.api(
                        '/debug_token?input_token='+response.authResponse.accessToken,
                        function (response) {
                            //console.log('Authenticating...');
                            if (response && !response.error) {
                                /* handle the result */
                                if (response.data.is_valid === true && response.data.app_id === '$app_id') {
                                    // this means that the current access token is issued by PHP Fusion.
                                    // authenticate the user or register the user.
                                    //console.log('User has given permissions. Now authenticate.');
                                    facebookAuthentication();
                                }
                            } else {
                                // we encountered an error.
                                console.log('We have encountered an error and may not be able to proceed to log you in. User need to click button again.');
                            }
                        }
                        );
                    } else {
                        console.log('User is not connected to Facebook.');
                    }
                  }

                  // Here we run a very simple test of the Graph API after login is
                  // successful.  See statusChangeCallback() for when this call is made.
                  function facebookAuthentication() {
                     //console.log('Welcome!  Fetching your information.... ');
                     FB.api('/me?fields=id,cover,name,first_name,last_name,locale,timezone,email', function(response) {
                         response['skip_auth'] = '".$options['skip_auth']."';
                         //console.log(response);
                        var file_url = '".rtrim(fusion_get_settings('site_path'), '/')."/includes/login/facebook/facebook_auth.php';
                         $.ajax({
                               'url': file_url,
                               'data': response,
                               'dataType': 'json',
                               'success': function(e) {
                                   //console.log('Getting Authentication Response...');
                                   //console.log(e);
                                   //console.log(e.response);
                                   if (e.response) {
                                       //alert('$redirect_link');
                                       window.location = '$redirect_link';
                                   }
                               },
                               'error' : function(e) {
                                   console.log('Facebook Authentication Error');
                               }
                         });
                    });
                  }

                  // Check login function
                  function fusion_login() {
                     FB.getLoginStatus(function(response) {
                        statusChangeCallback(response);
                     });
                  }

                </script>";

                $button_text = '';
                $button_size = '';
                if ($options['display_connection'] === TRUE) {
                    $button_text = $this->locale['uf_fb_connect_402'];
                    // Show whether user is connected to facebook or not.
                    if (!empty(fusion_get_userdata('user_facebook'))) {
                        $button_text = $this->locale['uf_fb_connect_403'];
                    }
                }

                $button_data_settings = [
                    'data-width'            => $fbSettings['button_width'] ?: "",
                    'data-max-rows'         => $fbSettings['max_photo_rows'] ?: "1",
                    'data-show-faces'       => $fbSettings['enable_friends_faces'] ? 'true' : 'false',
                    'data-auto-logout-link' => $fbSettings['enable_logout'] ? 'true' : 'false',
                    'data-scope'            => 'public_profile,email'
                ];
                $button_size = " size='large'";
                if (empty($button_text)) {
                    $button_data_settings['data-button-type'] = $fbSettings['button_text'] ?: 'continue_with';
                    $button_data_settings['data-use-continue-as'] = $fbSettings['enable_details'] ? 'true' : 'false';
                    $button_data_settings['data-size'] = $fbSettings['button_size'] ?: "medium";
                }
                $data_attr = implode(' ', array_map(
                    function ($keys, $values) {
                        if ($values) {
                            return "$keys='$values'";
                        }
                    }, array_keys($button_data_settings), array_values($button_data_settings)));

                return "<div class='fb-login-button' $data_attr.$button_size onlogin='fusion_login()'>$button_text</div>\n";
            }
        } else {
            add_notice("danger", "<strong>Warning: Facebook Plugin Error.</strong> Please configure APP ID and Secret Key", "all");
        }

        return NULL;

    }

}
