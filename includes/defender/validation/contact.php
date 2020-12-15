<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: contact.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined("IN_FUSION") || exit;

use Defender\Validation;

/**
 * Class Number
 * Validates Number Input
 */
class Contact extends Validation {

    /**
     * Checks if is a valid number
     * returns str the input or bool FALSE if check fails
     */
    public function verify_contact() {

        if (self::$inputConfig['required'] && (empty(self::$inputValue))) {
            fusion_stop();
            defender::setInputError(self::$inputName);
        }

        if (is_array(self::$inputValue) && count(self::$inputValue)) {
            $vars = [];
            foreach (self::$inputValue as $index => $val) {
                if (!empty($val)) {
                    // Get prefix value.
                    if ($index === 0) {
                        if (!$calling_codes = calling_codes($val)) {
                            return FALSE;
                        }
                    } else if ($index === 1 && !isnum($val)) {
                        return FALSE;
                    }
                    $vars[] = $val;
                } else {
                    return FALSE;
                }
            }

            $delimiter = (!empty(self::$inputConfig['delimiter'])) ? self::$inputConfig['delimiter'] : ",";

            return implode($delimiter, $vars); // empty str is returned if $vars ends up empty

        } else {
            return FALSE;
        }

    }
}
