<?php

$locale_file = include INCLUDES."user_fields/security/user_google2fa/locale/English.php";
if (file_exists(INCLUDES."user_fields/security/user_google2fa/locale/".LANGUAGE.".php")) {
    $locale_file = INCLUDES."user_fields/security/user_google2fa/locale/".LANGUAGE.".php";
}
define('G2FA_LOCALE', $locale_file);

if (infusion_exists('google_2fa')) {
    function google_2fa() {
        if (iMEMBER) {
            require_once INFUSIONS.'google_2fa/google_2fa.php';
            $g2fa = new GoogleAuthenticator();
            return $g2fa->displayAuthenticator();
        }
    }

    fusion_add_hook('fusion_login_connect', 'google_2fa');
}
