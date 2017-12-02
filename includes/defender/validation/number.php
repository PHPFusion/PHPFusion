<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/defender/validation/number.php
| Author: PHP-Fusion Development Team
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
 * Class Number
 * Validates Number Input
 */
class Number extends \Defender\Validation {

    /**
     * Checks if is a valid number
     * returns str the input or bool FALSE if check fails
     * TODO: support decimal
     */
    public function verify_number() {

        if (self::$inputConfig['required'] && (empty(self::$inputValue))) {
            \defender::stop();
            \defender::setInputError(self::$inputName);
        }

        if (is_array(self::$inputValue)) {
            $vars = [];
            foreach (self::$inputValue as $val) {
                if (!empty($val) && isnum($val, TRUE)) {
                    $vars[] = $val;
                }
            }
            $delimiter = (!empty(self::$inputConfig['delimiter'])) ? self::$inputConfig['delimiter'] : ",";
            $value = implode($delimiter, $vars);

            return $value; // empty str is returned if $vars ends up empty

        } else if (empty(self::$inputValue) || isnum(self::$inputValue, TRUE)) {
            return self::$inputValue;
        } else {
            return FALSE;
        }

    }
}
