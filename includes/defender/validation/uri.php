<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: includes/defender/validation/uri.php
| Author: Core Development Team
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
 * Class Uri
 * Validates URL Input
 */
class Uri extends \Defender\Validation {
    /**
     * Checks if is a valid URL
     * require path.
     * returns str the input or bool FALSE if check fails
     */
    protected function verify_URL() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            fusion_stop();
            \Defender::setInputError(self::$inputName);
        }

        if (self::$inputValue) {
            $url_parts = parse_url(self::$inputValue);
            $internal_url = fusion_get_settings('siteurl').self::$inputValue;
            if (!isset($url_parts['scheme']) && isset($url_parts['path'])) { // no http://
                // Check both remote and internal.
                $remote_url = 'http://'.self::$inputValue;
                if (self::validateURL($internal_url) !== FALSE) {
                    return $internal_url;
                } else if (self::validateURL($remote_url) !== FALSE) {
                    return $remote_url;
                }
            } else {
                $remote_url = self::$inputValue;
                if (self::validateURL($internal_url) !== FALSE) {
                    return self::$inputValue;
                } else if (self::validateURL($remote_url) !== FALSE) {
                    return self::$inputValue;
                }
            }
        }

        return FALSE;
    }


    /**
     * Validate URL
     *
     * @param $url
     *
     * @return bool
     */
    protected static function validateURL($url) {
        if (function_exists('curl_version')) {
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_NOBODY         => 1,
                CURLOPT_HEADER         => 0,
                CURLOPT_RETURNTRANSFER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
                //CURLOPT_SSL_VERIFYPEER => 0 // PHP 7.1
            ]);

            curl_exec($ch);

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $allowed_http = array_flip([301, 302, 200]);

            if (isset($allowed_http[$http_code])) {
                return $url;
            } else {
                return FALSE;
            }

            curl_close($ch);
        } else if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return $url;
        }

        return FALSE;
    }

    /**
     * Verify Paths within CMS
     *
     * @return string|null
     */
    public function verify_path() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            fusion_stop();
            \Defender::setInputError(self::$inputName);
        }
        if (file_exists(self::$inputConfig['path'].self::$inputValue) && is_file(self::$inputConfig['path'].self::$inputValue)) {
            return self::$inputValue;
        }

        return NULL;
    }
}
