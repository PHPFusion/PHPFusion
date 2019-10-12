<?php
namespace PHPFusion\Infusions\Facebook_Connect;

use Facebook\Facebook;
use PHPFusion\Authenticate;
use PHPFusion\PasswordAuth;

require_once __DIR__.'/../../maincore.php';
require_once INFUSIONS.'facebook_connect/class/autoload.php';

/**
 * Class Facebook_Connect
 *
 * @package PHPFusion\Infusions\Facebook_Connect
 */
class Facebook_Connect {

    private $settings = [
        'fb_app_id' => 0,
        'fb_secret' => '',
    ];
    private $fb = NULL;
    private $fb_uid = 0;
    private $section = 0;
    private $helper = NULL;

    /**
     * Facebook_Connect constructor.
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function __construct() {
        $this->settings = get_settings('facebook_connect');
        if ($this->settings['fb_app_id'] && $this->settings['fb_secret']) {

        $this->fb = new Facebook([
            'app_id'                => $this->settings['fb_app_id'],
            'app_secret'            => $this->settings['fb_secret'],
            'default_graph_version' => 'v2.10',
            //'persistent_data_handler' => 'session',
        ]);
            $this->fb_uid = fusion_get_userdata('user_facebook');
            $section = get('section');
            if ($section) {
                $this->section = $section;
            }

            if (iMEMBER) {
                if (!$this->fb_uid && session_get('facebook_access_token')) {
                    session_remove('facebook_access_token');
                    redirect(FUSION_REQUEST);
                }
            } else {
                session_remove('facebook_access_token');
            }

            $this->helper = $this->fb->getRedirectLoginHelper();

            if (get('state')) {
                $this->helper->getPersistentDataHandler()->set('state', get('state'));
            }
        }

    }

    /**
     * Disconnect a user from the system
     */
    private function disconnectUser() {

        if (fusion_get_userdata('user_password')) {
            dbquery("UPDATE ".DB_USERS." SET user_facebook='' WHERE user_id=:uid", [':uid' => (int)fusion_get_userdata('user_id')]);
            session_remove('facebook_access_token');
            // unset($_SESSION['facebook_access_token']);
            addNotice('success', 'Facebook has been disconnected.');
            redirect(BASEDIR.'edit_profile.php?section='.$this->section);
        } else {
            addNotice('danger', 'You cannot disconnect from Facebook until you have set your password.');
        }
    }

    /**
     * Connect a fb user to the system
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    private function connectUser() {
        $user = fusion_get_userdata();

        try {
            $this->fb->setDefaultAccessToken(session_get('facebook_access_token'));
            $profileRequest = $this->fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,picture');
            $fbUserProfile = $profileRequest->getGraphNode()->asArray();
        } catch (FacebookResponseException $e) {
            addNotice('danger', $e->getMessage());
            echo 'Graph returned an error: '.$e->getMessage();
            session_destroy();
            // Redirect user back to app login page
            //header("Location: ./");
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }

        //print_P($fbUserProfile);

        // Insert or update user data to the database
        $fbUserData = [
            'oauth_uid'  => $fbUserProfile['id'],
            'first_name' => $fbUserProfile['first_name'],
            'last_name'  => $fbUserProfile['last_name'],
            'picture'    => $fbUserProfile['picture']['url'],
            'email'      => $fbUserProfile['email'],
            //'link'       => $fbUserProfile['link']
        ];

        if (isset($user['user_firstname'])) { // checks if user firstname module is installed.
            if (empty($user['user_firstname'])) {
                $userProp['user_firstname'] = $fbUserData['first_name'];
            }
        }
        if (isset($user['user_lastname'])) { // checks if user lastname module is installed.
            if (empty($user['user_lastname'])) {
                $userProp['user_lastname'] = $fbUserData['last_name'];
            }
        }

        if (empty($user['user_avatar'])) {
            $dir = IMAGES."avatars/";
            $img = md5(TIME).'.jpg';
            $img = filename_exists($dir, $img);

            $url = $fbUserData['picture'];
            $ch = curl_init($url);
            $fp = fopen($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$img, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            $userProp['user_avatar'] = $img;
        }

        if (empty($user['user_facebook'])) {
            $userProp['user_facebook'] = $fbUserData['oauth_uid'];
        }


        if (isset($userProp)) {

            if (iMEMBER && $user['user_id']) {
                // Connecting from User Profile Edit.
                $userProp['user_id'] = $user['user_id'];
                if (!dbcount("(user_id)", DB_USERS, 'user_facebook=:fb_uid', [':fb_uid' => $userProp['user_facebook']])) {
                    // check FB
                    // make sure $userProp doesn't contain any email address.
                    dbquery_insert(DB_USERS, $userProp, 'update');
                    addNotice('success', 'Your Facebook account is now connected');
                } else {
                    addNotice('danger', 'This Facebook account was found belonging to another account and cannot be used.');
                }
                redirect(BASEDIR.'edit_profile.php?section='.$this->section);
            } else {
                // check if can login or not.
                if (dbcount("(user_id)", DB_USERS, "user_facebook=:fb_id", [':fb_id' => $userProp['user_facebook']])) {
                    // existing users
                    $opening_page = fusion_get_settings('opening_page');
                    $user_id = dbresult(dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_facebook=:fb_uid", [':fb_uid' => $userProp['user_facebook']]), 0);
                    $login = Authenticate::loginUser($user_id);
                    if ($login) {
                        redirect(BASEDIR.$opening_page);
                    }
                } else {
                    // new users
                    $redirect_to_register = FALSE;
                    if ($redirect_to_register) {
                        // go to registration form with prefill fields.
                        addNotice('warning', "Work in Progress");
                    } else {
                        $this->doRegistration($userProp, $fbUserProfile);
                    }
                }
            }
        }
    }


    private function doRegistration($userProp, $fbUserProfile) {

        $email_verification = fusion_get_settings('email_verification');

        $admin_activation = fusion_get_settings('admin_activation');

        $opening_page = fusion_get_settings('opening_page');

        $userProp['user_email'] = $fbUserProfile['email'];

        $is_new_email = $this->validateNewEmail($userProp['user_email']);

        if (\Defender::safe()) {

            if ($is_new_email) {

                $userProp['user_name'] = $this->validateNewUserName($fbUserProfile['first_name']);

                $new_user_info = [
                    'user_name'      => $userProp['user_name'],
                    'user_firstname' => $fbUserProfile['first_name'],
                    'user_lastname'  => $fbUserProfile['last_name'],
                    'user_email'     => $userProp['user_email'],
                    'user_datestamp' => TIME,
                    'user_ip'        => USER_IP,
                    'user_ip_type'   => USER_IP_TYPE,
                    'user_language'  => LANGUAGE,
                    'user_status'    => $admin_activation ? 2 : 0,
                    'user_salt'      => PasswordAuth::getNewRandomSalt(),
                    'user_algo'      => fusion_get_settings('password_algorithm'),
                    'user_facebook'  => $fbUserProfile['id']
                ];

                if ($email_verification) {
                    // do a registration.
                    mt_srand((double)microtime() * 1000000);
                    $salt = "";
                    for ($i = 0; $i <= 10; $i++) {
                        $salt .= chr(rand(97, 122));
                    }
                    $user_code = md5($userProp['user_email'].$salt);
                    $new_user_info['user_info'] = base64_encode(serialize($new_user_info));
                    $new_user_info['user_code'] = $user_code;
                    dbquery_insert(DB_NEW_USERS, $new_user_info, 'save', ['key' => 'primary_key', 'no_unique' => TRUE]);
                    $this->sendFbEmail($userProp['user_firstname'], $userProp['user_email']);
                    exit;
                } else {
                    // Save the user information
                    $new_user_info['user_id'] = 0;
                    $userProp['user_id'] = dbquery_insert(DB_USERS, $new_user_info, 'save');
                }

                if ($admin_activation) {
                    addNotice('success', 'Your account need to be activated by the administrator before you can login.');

                } else if (!empty($userProp['user_id']) && !empty($userProp['user_salt'])) {
                    // log the user in.
                    addNotice('success', 'A new account has been created for you. You are now login with your Facebook ID.', BASEDIR.$opening_page);
                    $login = Authenticate::loginUser($userProp['user_id']);
                    if ($login) {
                        redirect(BASEDIR.$opening_page);
                    }
                }
            } else {

                // A user found with that id
                $user_id = dbresult(dbquery("SELECT user_id FROM ".DB_USERS." WHERE user_email=:email AND user_facebook =''", [':email' => $userProp['user_email']]), 0);
                if ($user_id) {
                    dbquery("UPDATE ".DB_USERS." SET user_facebook=:fb_uid WHERE user_id=:uid", [
                        ':uid'    => (int)$user_id,
                        ':fb_uid' => $userProp['user_facebook']
                    ]);
                    $login = Authenticate::loginUser($user_id);
                    if ($login) {
                        redirect(BASEDIR.$opening_page);
                    }
                } else {
                    addNotice('danger', 'Login failed. Your Facebook email address is associated with another account.');
                }
            }
        }
    }

    private function validateNewUserName($name) {
        if (dbcount("(user_id)", DB_USERS, 'user_name=:uname', [':uname' => stripinput($name)])) {
            return $this->validateUserName($name.rand(1, 100));
        }
        return $name;
    }

    private function validateNewEmail($email) {
        if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,6}$/i", $email)) {
            if (dbcount("(blacklist_id)", DB_BLACKLIST,
                ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')",
                [
                    ':email' => $email
                ])) {
                \Defender::stop('This email address has been blacklisted.');
            } else {
                $email_active = dbcount("(user_id)", DB_USERS, "user_email=:u_email", [':u_email' => $email]);
                $email_inactive = dbcount("(user_code)", DB_NEW_USERS, "user_email=:u_email", [':u_email' => $email]);
                if ($email_active == 0 && $email_inactive == 0) {
                    return TRUE;
                }
            }
        } else {
            \Defender::stop('Email address is not a valid email address.');
        }

        return FALSE;
    }

    private function sendFbEmail($first_name, $email) {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', [LOCALE.LOCALESET.'user_fields.php']);

        require_once INCLUDES."sendmail_include.php";
        mt_srand((double)microtime() * 1000000);
        $salt = "";
        for ($i = 0; $i <= 10; $i++) {
            $salt .= chr(rand(97, 122));
        }
        $user_code = md5($email.$salt);
        $email_verify_link = $settings['siteurl']."register.php?code=".$user_code;
        $mailbody = str_replace("[EMAIL_VERIFY_LINK]", $email_verify_link, $locale['u203']);
        $mailbody = str_replace("[SITENAME]", $settings['sitename'], $mailbody);
        $mailbody = str_replace("[SITEUSERNAME]", $settings['siteusername'], $mailbody);
        $mailbody = str_replace("[USER_NAME]", $first_name, $mailbody);
        $mailSubject = str_replace("[SITENAME]", $settings['sitename'], $locale['u202']);
        sendemail($first_name, $email, $settings['siteusername'], $settings['siteemail'], $mailSubject, $mailbody);
        addNotice('success', strtr($locale['u200'], ['(%s)' => $email]));
        redirect(BASEDIR.'login.php');
    }

    /**
     * Authenticate a fb user
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function doAuthenticate() {
        $accessToken = session_get('facebook_access_token');
        if (!$accessToken) {
            try {
                // always cannot get access token when disconnecting.
                $accessToken = $this->helper->getAccessToken();

            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                //} catch (\Exception $e) {
                // When Graph returns an error
                echo 'Graph returned an error: '.$e->getMessage();
                exit;
                //} catch (\Exception $e) {
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: '.$e->getMessage();
                exit;
            }

            if (isset($accessToken)) {
                // Put short-lived access token in session
                session_add('facebook_access_token', (string)$accessToken);
                // OAuth 2.0 client handler helps to manage access tokens
                $oAuth2Client = $this->fb->getOAuth2Client();
                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken(session_get('facebook_access_token'));
                session_add('facebook_access_token', (string)$longLivedAccessToken);
                // Set default access token to be used in script
                $this->fb->setDefaultAccessToken(session_get('facebook_access_token'));
            }

        } else {

            if ($this->helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: ".$this->helper->getError()."\n";
                echo "Error Code: ".$this->helper->getErrorCode()."\n";
                echo "Error Reason: ".$this->helper->getErrorReason()."\n";
                echo "Error Description: ".$this->helper->getErrorDescription()."\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        $this->connectUser();
    }

    public function displayField($field_value, array $options = []) {
        $locale = fusion_get_locale(NULL, FBC_LOCALE);

        if (get('code')) {
            $this->doAuthenticate();
        } else if (get('fb_disconnect') == 'true') {
            $this->disconnectUser();
        }

        $icon = "<img src='".INFUSIONS."facebook_connect/facebook.svg' title='Facebook' alt='Facebook'/>";
        $text = $locale['fbc_0106'];
        if ($field_value) {
            $text = $locale['fbc_0107'];
        }
        $login_url = $this->getConnectButtonUrl();

        return '<div class="social-connector m-b-15 '.grid_column_size(100).'">
        <span class="pull-right">
        <a class="btn btn-primary btn-rounded'.(!$login_url ? ' disabled' : '').'" href="'.$login_url.'">'.$text.'</a>
        </span>
        <h5 class="display-inline text-dark strong"><span class="control-label display-inline">'.$icon.'</span>'.$locale['fbc_0105'].'</h5>
        </div>';
    }

    private function getConnectButtonUrl() {
        if ($this->fb_uid) {
            return clean_request('fb_disconnect=true', ['fb_disconnect'], FALSE);
        }
        if ($this->helper) {
            $permissions = ['email']; // Optional permissions
            return $this->helper->getLoginUrl(fusion_get_settings('siteurl').'edit_profile.php?section='.$this->section, $permissions);
        }
        return NULL;
    }

    public function getLoginButtonUrl() {
        $permissions = ['email']; // Optional permissions
        if ($this->helper) {
            return $this->helper->getLoginUrl(fusion_get_settings('siteurl').'login.php?connect=facebook', $permissions);
        }
        return NULL;
    }

    function displaySettingsAdmin() {
        $locale = fusion_get_locale(NULL, FBC_LOCALE);
        $settings = get_settings('facebook_connect');

        if (post('save')) {
            $settings = [
                'fb_app_id' => sanitizer('fb_app_id', '', 'fb_app_id'),
                'fb_secret' => sanitizer('fb_secret', '', 'fb_secret'),
            ];
            if (\Defender::safe()) {
                dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value=:s001 WHERE settings_name=:s001a AND settings_inf=:s001b", [':s001' => $settings['fb_app_id'], ':s001a' => 'fb_app_id', ':s001b' => 'facebook_connect']);
                dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value=:s001 WHERE settings_name=:s001a AND settings_inf=:s001b", [':s001' => $settings['fb_secret'], ':s001a' => 'fb_secret', ':s001b' => 'facebook_connect']);
                addNotice('success', $locale['fbc_0104']);
                redirect(FUSION_REQUEST);
            }
        }

        add_breadcrumb(['link' => INFUSIONS.'facebook_connect/admin.php', 'title' => $locale['fbc_0103']]);
        opentable($locale['fbc_0103']);
        echo openform('fbcSettings', 'post');
        echo '
        <div class="'.grid_row().'">
        <div class="'.grid_column_size(100, 100, 70, 50).'">';
        echo form_text('fb_app_id', $locale['fbc_0100'], $settings['fb_app_id'], ['required' => TRUE]);
        echo form_text('fb_secret', $locale['fbc_0102'], $settings['fb_secret'], ['required' => TRUE]);
        echo '
        <hr/>
        ';
        echo form_button('save', $locale['update'], 'save', ['class' => 'btn-primary']);
        echo '
    </div>
</div>';
        echo closeform();
        closetable();
    }

}