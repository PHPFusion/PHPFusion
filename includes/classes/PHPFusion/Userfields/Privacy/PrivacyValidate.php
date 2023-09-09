<?php
namespace PHPFusion\Userfields\Privacy;

use PHPFusion\Authenticate;
use PHPFusion\Userfields\UserFieldsValidate;

class PrivacyValidate extends UserFieldsValidate {

    public function validate() {

        $locale = fusion_get_locale();

        if (check_post( 'submit_2fa' )) {

            $pin = sanitizer( '2fa_code', '', '2fa_code' );

            if (fusion_safe()) {

                $res = dbquery( "SELECT user_2fa_pin FROM " . DB_USERS . " WHERE user_id=:uid", [':uid' => (int)$this->userData['user_id']] );

                if (dbrows( $res )) {
                    $rows = dbarray( $res );
                    if ($pin == $rows['user_2fa_pin']) {

                        $data = [
                            'user_id'   => (int)$this->userData['user_id'],
                            'user_auth' => 1,
                        ];
                        dbquery_insert( DB_USER_SETTINGS, $data, 'update', ['primary_key' => 'user_id', 'no_unique' => TRUE] );

                        addnotice( 'success', $locale['u610'] );

                        redirect( clean_request( '', ['auth'], FALSE ) );
                    }
                } else {
                    addnotice( 'danger', $locale['u611'] );
                }
            }

        }

        elseif (check_post( 'auth' ) && check_post( 'user_hash' )) {

            if ($this->userFieldsInput->getAccess()) {

                $settings = fusion_get_settings();

                $random_pin = Authenticate::generateOTP( $settings['auth_login_length'] );

                $auth_actiontime = time();

                dbquery( "UPDATE " . DB_USERS . " SET user_auth_pin=:pin, user_auth_actiontime=:time WHERE user_id=:uid", [
                    ':pin' => $random_pin,
                    'time' => $auth_actiontime,
                    ':uid' => $this->userFieldsInput->userData['user_id'],

                ] );

                // Attempt to send email
                if (!fusion_sendmail( 'L_2FA', $this->userFieldsInput->userData['user_name'], $this->userFieldsInput->userData['user_email'], [
                    'subject' => $locale['email_2fa_subject'],
                    'message' => $locale['email_2fa_message'],
                    'replace' => [
                        '[OTP]' => $random_pin
                    ]
                ] )) {

                    $message = strtr( $locale['u154'], [
                            '[LINK]'  => "<a href='" . BASEDIR . "contact.php'><strong>",
                            '[/LINK]' => "</strong></a>"
                        ]
                    );

                    addnotice( 'danger', $locale['u153'] . "<br />" . $message );
                }
            }
        }

        return FALSE;
    }

}