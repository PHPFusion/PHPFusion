<?php


use Facebook\Exceptions\FacebookSDKException;
use PHPFusion\Infusions\Facebook\Facebook_Connect;

$locale_file = INFUSIONS."facebook/locale/English.php";
if (file_exists(INFUSIONS."facebook/locale/".LANGUAGE.".php")) {
    $locale_file = INFUSIONS."facebook/locale/".LANGUAGE.".php";
}

if (!defined('FBC_LOCALE')) {
    define('FBC_LOCALE', $locale_file);
}

if (infusion_exists('facebook_connect')) {

    if (!function_exists('display_facebook_button')) {
        function display_facebook_button(): string {
            fusion_load_script(INFUSIONS."facebook_connect/button.css", "css");
            $fb = new Facebook_Connect();
            $login_url = $fb->getLoginButtonUrl();
            return (string)'<a href="'.$login_url.'" class="btn btn-block btn-fb"><i class="fab fa-facebook-f fa-fw"></i>Login with Facebook</a>';
        }
    }

    function facebook_connect() {
        $fb = new Facebook_Connect();
        if (get('connect') == 'facebook') {
            try {
                $fb->doAuthenticate();
            } catch (FacebookSDKException $e) {
                set_error(E_USER_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }
    }

    fusion_add_hook('fusion_login_connectors', 'display_facebook_button');
    fusion_add_hook('fusion_login_connect', 'facebook_connect');

}
