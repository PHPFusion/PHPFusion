<?php

class Uri extends \Defender\Validation {
    /**
     * Checks if is a valid URL
     * require path.
     * returns str the input or bool FALSE if check fails
     */
    protected function verify_URL() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            \defender::stop();
            \defender::getInstance()->setInputError(self::$inputName);
        }
        if (self::$inputValue) {
            $url_parts = parse_url(self::$inputValue);
            $internal_url = fusion_get_settings('siteurl').self::$inputValue;
            if (!isset($url_parts['scheme']) && isset($url_parts['path'])) { // no http://
                // Check both remote and internal.
                $remote_url = 'http://'.self::$inputValue;
                if (self::validateURL($internal_url) !== FALSE) {
                    return $internal_url;
                } elseif (self::validateURL($remote_url) !== FALSE) {
                    return $remote_url;
                }
            } else {
                $remote_url = self::$inputValue;
                if (self::validateURL($internal_url) !== FALSE) {
                    return self::$inputValue;
                } elseif (self::validateURL($remote_url) !== FALSE) {
                    return self::$inputValue;
                }
            }

            return FALSE;
        }
    }


    /**
     * Validate URL
     * @param $url
     * @return bool
     */
    protected static function validateURL($url) {
        if (function_exists('curl_version')) {
            $fp = curl_init($url);
            curl_setopt($fp, CURLOPT_TIMEOUT, 20);
            curl_setopt($fp, CURLOPT_FAILONERROR, TRUE);
            curl_setopt($fp, CURLOPT_REFERER, $url);
            curl_setopt($fp, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($fp, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
            curl_setopt($fp, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_exec($fp);
            if (curl_errno($fp) != 0) {
                curl_close($fp);
                return FALSE;
            } else {
                curl_close($fp);
                return $url;
            }
        } elseif (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === FALSE) {
            return FALSE;
        }

        return FALSE;
    }


    /**
     * Verify Paths within CMS
     * @return bool|string
     */
    public function verify_path() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            \defender::stop();
            \defender::getInstance()->setInputError(self::$inputName);
        }
        if (file_exists(self::$inputConfig['path'].self::$inputValue) && is_file(self::$inputConfig['path'].self::$inputValue)) {
            return self::$inputValue;
        }

        return FALSE;
    }

}