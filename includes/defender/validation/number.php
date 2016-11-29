<?php

class Number extends \Defender\Validation {

    /**
     * Checks if is a valid number
     * returns str the input or bool FALSE if check fails
     * TODO: support decimal
     */
    public function verify_number() {

        if (self::$inputConfig['required'] && (empty(self::$inputValue))) {
            \defender::stop();
            \defender::getInstance()->setInputError(self::$inputName);
        }

        if (is_array(self::$inputValue)) {
            $vars = array();
            foreach (self::$inputValue as $val) {
                if (!empty($val) && isnum($val, TRUE)) {
                    $vars[] = $val;
                }
            }
            $delimiter = (!empty(self::$inputConfig['delimiter'])) ? self::$inputConfig['delimiter'] : ",";
            $value = implode($delimiter, $vars);

            return $value; // empty str is returned if $vars ends up empty

        } elseif (empty(self::$inputValue) || isnum(self::$inputValue, TRUE)) {
            return self::$inputValue;
        } else {
            return FALSE;
        }

    }


}