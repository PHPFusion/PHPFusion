<?php

$locale_file = INFUSIONS."google/locale/".LOCALE.".php";
if (file_exists(INFUSIONS."google/locale/".LANGUAGE.".php")) {
    $locale_file = INFUSIONS."google/locale/".LANGUAGE.".php";
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
