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

use PHPFusion\Quantum\QuantumFactory;
use PHPFusion\Userfields\UserFieldsForm;

class PrivacyForm extends UserFieldsForm {

    public function displayInputFields() {

        if (check_get( 'd' )) {

            return match (get( 'd' )) {
                default => [],
                'twostep' => $this->getTwoStep(),
                'records' => $this->getLogin(),
                'data' => $this->getLogs(),
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
     *
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

        $res = dbquery( "SELECT * FROM " . DB_USER_SESSIONS . " WHERE user_id=:uid ORDER BY user_logintime DESC", [':uid' => $this->userFields->userData['user_id']] );

        if (dbrows( $res )) {
            while ($rows = dbarray( $res )) {
                $info['user_logins'][$rows['user_session_id']] = $rows;
            }
        }

        return $info;
    }

    /**
     * User log information
     * @return array
     */
    private function getLogs() {

        $locale = fusion_get_locale();

        $field_names = [
            'user_name'           => $locale['u068'],
            'user_firstname'      => $locale['u010'],
            'user_lastname'       => $locale['u011'],
            'user_addname'        => $locale['u012'],
            'user_password'       => $locale['u133'],
            'user_admin_password' => $locale['u144a'],
            'user_phone'          => $locale['u013'],
            'user_email'          => $locale['u128'],
            'user_level'          => $locale['u063'],
        ];

        $res = dbquery( "SELECT field_title, field_name FROM " . DB_USER_FIELDS );
        if (dbrows( $res )) {
            while ($rows = dbarray( $res )) {
                $field_names[$rows['field_name']] = parse_label( $rows['field_title'] );
            }
        }

        $res = dbquery( "SELECT * FROM " . DB_USER_LOG . " WHERE userlog_user_id=:uid ORDER BY userlog_timestamp DESC", [':uid' => (int)$this->userFields->userData['user_id']] );
        if (dbrows( $res )) {
            while ($rows = dbarray( $res )) {

                $rows['title'] = $locale['u075'];

                if (isset( $field_names[$rows['userlog_field']] )) {
                    $log = sprintf( $locale['u076'], '<strong>'.$field_names[$rows['userlog_field']].'</strong>', $rows['userlog_value_old'], $rows['userlog_value_new'] );
                } else {
                    $log = sprintf( $locale['u077'], $rows['userlog_value_old'], $rows['userlog_value_new'] );
                }

                $rows['description'] = $log;

                $info['user_log'][$rows['userlog_id']] = $rows;
            }
        }

        return $info ?? [];
    }


}