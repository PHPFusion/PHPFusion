<?php

$locale_file = include INFUSIONS."facebook_connect/locale/English.php";
if (file_exists(INFUSIONS."facebook_connect/locale/".LANGUAGE.".php")) {
    $locale_file = INFUSIONS."facebook_connect/locale/".LANGUAGE.".php";
}
if (!defined('FBC_LOCALE')) {
    define('FBC_LOCALE', $locale_file);
}

if (infusion_exists('facebook_connect')) {

    if (!function_exists('display_facebook_button')) {
        function display_facebook_button() {
            $locale = fusion_get_locale('', [FBC_LOCALE]);
            $fb = new \PHPFusion\Infusions\Facebook_Connect\Facebook_Connect();
            $login_url = $fb->getLoginButtonUrl();
            add_to_head('<link rel="stylesheet" href="'.INFUSIONS.'facebook_connect/button.css">');
            return (string)'<a href="'.$login_url.'" class="btn btn-block btn-fb"><i class="fab fa-facebook-f fa-fw"></i>Login with Facebook</a>';
        }
    }

    function facebook_connect() {
        $fb = new \PHPFusion\Infusions\Facebook_Connect\Facebook_Connect();
        if (get('connect') == 'facebook') {
            $fb->doAuthenticate();
        }
    }

    fusion_add_hook('fusion_login_connectors', 'display_facebook_button');
    fusion_add_hook('fusion_login_connect', 'facebook_connect');

}