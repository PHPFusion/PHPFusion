<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: theme.php
 * | Author:  Meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */
namespace PHPFusion\Userfields\Privacy;

use PHPFusion\Userfields\UserFieldsForm;

class PrivacyForm extends UserFieldsForm {

    public function displayInputFields() {

        if (check_get( 'd' )) {

            return match (get( 'd' )) {
                default => '',
                'twostep' => $this->getTwoStep(),
                'records' => $this->getLogin(),
            };
        }

        return [
            'twostep_url' => clean_request( 'd=twostep', ['d'], FALSE ),
            'records_url' => clean_request( 'd=records', ['d'], FALSE ),
            'data_url'    => clean_request( 'd=data', ['d'], FALSE ),
            'login_url'   => clean_request( 'd=login', ['d'], FALSE ),
        ];
    }

    /**
     * Activate two step verification
     * @return array
     */
    private function getTwoStep() {

        $locale = fusion_get_locale();

        return [
            'email_display' => $this->userFields->userData['user_email'],
            'user_code'     => form_text( '2fa_code', '', '', ['placeholder' => $locale['u608'], 'max_length' => 6, 'mask' => '9-9-9-9-9-9'] ),
            'get_auth'      => form_button( 'auth', $locale['u605'], $locale['u605'], ['class' => 'btn-primary'] ),
            'button'        => form_button( 'submit_2fa', $locale['submit'], $locale['submit'], ['class' => 'btn-primary'] ),
        ];
    }

    private function getLogin() {

        $res = dbquery("SELECT * FROM ".DB_USER_SESSIONS." WHERE user_id=:uid ORDER BY user_logintime DESC", [':uid'=> $this->userFields->userData['user_id']]);

        if (dbrows($res)) {
            while($rows = dbarray($res)) {
                $info['user_logins'][$rows['user_session_id']] = $rows;
            }
        }

        return $info;
    }

}