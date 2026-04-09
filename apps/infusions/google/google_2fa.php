<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/google_auth/google_auth.php
| Author: PHP-Fusion Development Team
| Co-Author: Michael Kliewe
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class GoogleAuthenticator
 * PHP Class for handling Google Authenticator 2-factor authentication
 * this service may face end of life.
 */
class GoogleAuthenticator {

    protected $_codeLength = 6;

    /**
     * Displays the authenticator
     *
     * @return null
     */
    public function displayAuthenticator() {
        if (iMEMBER) { // from login that you have successfully attempted login.
            $code = session_get('google_secret_code');
            $user_id = session_get('google_uid');
            $google_authorization = session_get('google_2fa_auth');

            if (!$code && !$user_id) {
                $code = fusion_get_userdata('user_google2fa');
                $user_id = fusion_get_userdata('user_id');
                if ($code) { // already set up google2fa
                    session_add('google_secret_code', $code);
                    session_add('google_uid', $user_id);
                }
            }
            // if no google_code authorization token
            if ($code && !$google_authorization) {
                if (FUSION_SELF !== 'two-factor-authentication.php') {
                    redirect(INFUSIONS.'google_2fa/two-factor-authentication.php');
                }
            }
        }
    }

    private function setUp2FA($user_id, $field_value) {
        $locale = fusion_get_locale('', [G2FA_LOCALE]);
        if (post('authenticate') && post('enable_2step', FILTER_VALIDATE_INT) == 1) {
            $google = new GoogleAuthenticator();
            $gCode = sanitizer('g_code', '', 'g_code');
            $secret = sanitizer('secret', '', 'secret');
            $checkResult = $google->verifyCode($secret, $gCode, 2);    // 2 = 2*30sec clock tolerance

            if ($checkResult && fusion_safe()) {
                // successful paired
                $user = [
                    'user_id'        => (int)$user_id,
                    'user_google2fa' => (string)$secret,
                ];
                dbquery_insert(DB_USERS, $user, 'update');
                add_notice('success', $locale['uf_gauth_140']);
                redirect(FUSION_REQUEST);

            } else {
                add_notice('danger', $locale['uf_gauth_141']);
                redirect(FUSION_REQUEST);
            }
        }

        if (post('deactivate')) {
            $google = new GoogleAuthenticator();
            $gCode = sanitizer('g_code', '', 'g_code');

            $checkResult = $google->verifyCode($field_value, $gCode, 2);    // 2 = 2*30sec clock tolerance
            if ($checkResult && fusion_safe()) {
                // successful paired
                $user = [
                    'user_id'        => $user_id,
                    'user_google2fa' => '',
                ];
                dbquery_insert(DB_USERS, $user, 'update');
                add_notice('success', $locale['uf_gauth_142']);
                redirect(FUSION_REQUEST);
            } else {
                // unsuccessful. try again.
                add_notice('danger', $locale['uf_gauth_143']);
                redirect(FUSION_REQUEST);
            }
        }

    }

    /**
     * User configuration field.
     */
    public function displayConnector() {

        $locale = fusion_get_locale('', [G2FA_LOCALE]);
        $settings = fusion_get_settings();
        $user_data = fusion_get_userdata();

        $this->setUp2FA($user_data['user_id'], $user_data['user_google2fa']);

        $tpl = \PHPFusion\Template::getInstance('gauth');
        $tpl->set_template(__DIR__.'/templates/create.html');
        $tpl->set_tag('title', $locale['uf_gauth_108']);
        $tpl->set_tag('description', str_replace('{SITE_NAME}', $settings['sitename'], $locale['uf_gauth_111']));

        if (!empty($user_data['user_google2fa'])) {
            // Reset options
            $tpl->set_block('current_block', [
                'title'       => $locale['uf_gauth_112'],
                'description' => $locale['uf_gauth_113'],
                'detail'      => $locale['uf_gauth_114'],
                'text_input'  => form_text('g_code', $locale['uf_gauth_103'], '', ['type' => 'password', 'required' => TRUE, 'placeholder' => $locale['uf_gauth_105']]),
                'button'      => form_button('deactivate', $locale['uf_gauth_107'], $locale['uf_gauth_107'], ['class' => 'btn-primary'])
            ]);

            return $tpl->get_output();

        } else {

            $account_name = $user_data['user_email'];
            $site_name = $settings['sitename'];
            $secret = $this->createSecret();
            $qrCodeUrl = $this->getQRCodeGoogleUrl($account_name, $secret, $site_name);

            $tpl->set_block('new_block', [
                'title'        => $locale['uf_gauth_115'],
                'subtitle'     => $locale['uf_gauth_116'],
                'i_title'      => $locale['uf_gauth_150'],
                'i_subtitle'   => $locale['uf_gauth_151'],
                'd_name'       => $locale['uf_gauth_152'],
                'd_key'        => $locale['uf_gauth_153'],
                'i_detail'     => $locale['uf_gauth_154'],
                'i_text'       => $locale['uf_gauth_155'],
                'm_title'      => $locale['uf_gauth_156'],
                'm_text_1'     => $locale['uf_gauth_157'],
                'm_text_2'     => $locale['uf_gauth_158'],
                'm_text_3'     => $locale['uf_gauth_159'],
                'm_text_4'     => $locale['uf_gauth_160'],
                'm_text_5'     => $locale['uf_gauth_161'],
                'm_text_6'     => $locale['uf_gauth_162'],
                'radio'        => form_checkbox('enable_2step', $locale['uf_gauth_108'], '', [
                        'options' => [
                            0 => $locale['uf_gauth_109'],
                            1 => $locale['uf_gauth_110']
                        ],
                        'type'    => 'radio']).form_hidden('secret', '', $secret),
                'account_name' => $account_name,
                'key'          => $secret,
                'image_src'    => $qrCodeUrl,
                'text_input'   => form_text('g_code', $locale['uf_gauth_103'], '', ['type' => 'password', 'required' => TRUE, 'class'=>'form-group-lg', 'placeholder' => $locale['uf_gauth_105']]),
                'button'       => form_button('authenticate', $locale['uf_gauth_106'], $locale['uf_gauth_106'], ['class' => 'btn-primary'])
            ]);

            add_to_jquery("
            $('input[name=\"enable_2step\"]').bind('change', function(e) {
                if ($(this).val() == 1) {
                    $('#gauth_setup_form').slideDown();
                } else {
                    $('#gauth_setup_form').slideUp();
                }
            });
            ");

            return (string)$tpl->get_output();
        }
    }

    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength
     *
     * @return string
     * @throws \Exception
     */
    public function createSecret($secretLength = 16) {
        $validChars = $this->_getBase32LookupTable();
        // Valid secret lengths are 80 to 640 bits
        if ($secretLength < 16 || $secretLength > 128) {
            throw new Exception('Bad secret length');
        }
        $secret = '';
        $rnd = FALSE;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } else if (function_exists('mcrypt_create_iv')) {
            $rnd = mcrypt_create_iv($secretLength, MCRYPT_DEV_URANDOM);
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if (!$cryptoStrong) {
                $rnd = FALSE;
            }
        }
        if ($rnd !== FALSE) {
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new Exception('No source of secure random');
        }

        return $secret;
    }

    /**
     * Calculate the code, with given secret and point in time.
     *
     * @param string   $secret
     * @param int|null $timeSlice
     *
     * @return string
     */
    public function getCode($secret, $timeSlice = NULL) {
        if ($timeSlice === NULL) {
            $timeSlice = floor(time() / 30);
        }
        $secretkey = $this->_base32Decode($secret);
        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, TRUE);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);
        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, $this->_codeLength);

        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR-Code URL for image, from google charts.
     *
     * @param string $name
     * @param string $secret
     * @param string $title
     * @param array  $params
     *
     * @return string
     */
    public function getQRCodeGoogleUrl($name, $secret, $title = NULL, $params = []) {
        $width = !empty($params['width']) && (int)$params['width'] > 0 ? (int)$params['width'] : 200;
        $height = !empty($params['height']) && (int)$params['height'] > 0 ? (int)$params['height'] : 200;
        $level = !empty($params['level']) && array_search($params['level'], ['L', 'M', 'Q', 'H']) !== FALSE ? $params['level'] : 'M';
        $urlencoded = urlencode('otpauth://totp/'.$name.'?secret='.$secret.'');
        if (isset($title)) {
            $urlencoded .= urlencode('&issuer='.urlencode($title));
        }

        return 'https://chart.googleapis.com/chart?chs='.$width.'x'.$height.'&chld='.$level.'|0&cht=qr&chl='.$urlencoded.'';
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now.
     *
     * @param string   $secret
     * @param string   $code
     * @param int      $discrepancy      This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @param int|null $currentTimeSlice time slice if we want use other that time()
     *
     * @return bool
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = NULL) {
        if ($currentTimeSlice === NULL) {
            $currentTimeSlice = floor(time() / 30);
        }
        if (strlen($code) != 6) {
            return FALSE;
        }
        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($this->timingSafeEquals($calculatedCode, $code)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Set the code length, should be >=6.
     *
     * @param int $length
     *
     * @return GoogleAuthenticator
     */
    public function setCodeLength($length) {
        $this->_codeLength = $length;

        return $this;
    }

    /**
     * Helper class to decode base32.
     *
     * @param $secret
     *
     * @return bool|string
     */
    protected function _base32Decode($secret) {
        if (empty($secret)) {
            return '';
        }
        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues)) {
            return FALSE;
        }
        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])
            ) {
                return FALSE;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return FALSE;
            }
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32.
     *
     * @return array
     */
    protected function _getBase32LookupTable() {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  // padding char
        ];
    }

    /**
     * A timing safe equals comparison
     * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
     *
     * @param string $safeString The internal (safe) value to be checked
     * @param string $userString The user submitted (unsafe) value
     *
     * @return bool True if the two strings are identical
     */
    private function timingSafeEquals($safeString, $userString) {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);
        if ($userLen != $safeLen) {
            return FALSE;
        }
        $result = 0;
        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }
}
