<?php

class Checkbox extends \Defender\Validation {
    /**
     * Validate a checkbox
     * If field Value is multiple checkbox, post value must be an array
     * If field value is a radio, post value must not be an array
     * If field value is a number, post value must be a boolean 1 or 0
     */
    protected function verify_checked() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            \defender::stop();
            \defender::getInstance()->setInputError(self::$inputName);
        }
        if (is_array(self::$inputValue)) {
            $vars = array();
            foreach (self::$inputValue as $val) {
                $vars[] = stripinput($val);
            }
            $delimiter = (!empty(self::$inputConfig['delimiter'])) ? self::$inputConfig['delimiter'] : ",";
            $value = implode($delimiter, $vars);

            return $value;
        } elseif (!empty(self::$inputValue)) {
            if (isnum(self::$inputValue)) {
                if (self::$inputValue == 1) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return stripinput(self::$inputValue);
            }
        } else {
            return FALSE;
        }
    }
}