<?php
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';

function restore_google2fa() {
    $locale = fusion_get_locale('', [G2FA_LOCALE]);
    $restore_code = get('restore_code');
    $user_id = get('uid', FILTER_VALIDATE_INT);

    $secret = '';
    $secret_key = defined('SECRET_KEY') ? SECRET_KEY : "secret_key";
    $secret_key_salt = defined('SECRET_KEY_SALT') ? SECRET_KEY_SALT : "secret_salt";

    if ($restore_code && $user_id) {
        $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:uid", [':uid' => (int) $user_id]);
        if (dbrows($result)) {
            $user = dbarray($result);
            $salt = md5(isset($user['user_salt']) ? $user['user_salt'].$secret_key_salt : $secret_key_salt);
            if ($restore_code == $user['user_id'].hash_hmac($user['user_algo'], $user['user_id'].$secret.$secret_key, $salt)) {
                // restore the account
                dbquery("UPDATE ".DB_USERS." SET user_status=0 WHERE user_status=5 AND user_id=:uid", [':uid' => (int) $user_id]);
                add_notice("success", $locale['uf_gauth_130']);
            } else {
                add_notice("danger", $locale['uf_gauth_131']);
                redirect(BASEDIR.fusion_get_settings('opening_page'));
            }
        } else {
            add_notice("danger", $locale['uf_gauth_132']);
            redirect(BASEDIR.fusion_get_settings('opening_page'));
        }
    }
}



require_once THEMES.'templates/footer.php';