<?php
use PHPFusion\PasswordAuth;

(defined("IN_FUSION") || exit);

/**
 * Autofix password from user table
 * Fill the password in plain text into 'user_password' column and leave the user_salt and user_algo empty
 */
function auto_fix_passwords() {
    /** @var $result - checks for any entries with no user_salt generated */
    $result = dbquery("SELECT user_id, user_password, user_name FROM ".DB_USERS." WHERE user_salt = '' AND user_algo =''");
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $new_user_pass = $data['user_password'];
            /** Generate the hash using database user_password */
            $passAuth = new PasswordAuth();
            $passAuth->inputNewPassword = $new_user_pass;
            $passAuth->inputNewPassword2 = $new_user_pass;
            if ($passAuth->isValidNewPassword() === 0) {
                $new_algo = $passAuth->getNewAlgo(); // ok
                $new_salt = $passAuth->getNewSalt();
                $new_pass = $passAuth->getNewHash();
                /** Revalidate the hash */
                $validate = new PasswordAuth();
                $validate->currentAlgo = $new_algo;
                $validate->currentSalt = $new_salt;
                $validate->inputPassword = $data['user_password'];
                $validate->currentPasswordHash = $new_pass;
                if ($validate->isValidCurrentPassword()) {
                    $param = array(
                        ":ipass" => $new_pass,
                        ":ialgo" => $new_algo,
                        ":isalt" => $new_salt,
                        ":iuid"  => $data['user_id']
                    );
                    dbquery("UPDATE ".DB_USERS." SET `user_password`=:ipass, `user_algo`=:ialgo, `user_salt`=:isalt WHERE `user_id`=:iuid", $param);
                }
            }
        }
    }
}
