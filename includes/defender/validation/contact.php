<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: contact.php
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
            Defender::setInputError(self::$inputName);
            return FALSE;
        }

        $prefix = sanitizer(self::$inputName."_prefix");

        if (calling_codes($prefix)) {

            if (self::$inputValue && isnum(self::$inputValue)) {

                return "$prefix|".self::$inputValue;

            }
            // else
            if (self::$inputConfig["required"]) {
                fusion_stop();

                Defender::setInputError(self::$inputName."_prefix");

                Defender::setInputError(self::$inputName);

                return FALSE;
            }

        } else {

            if (self::$inputConfig["required"] && (empty($prefix))) {

                fusion_stop();

                Defender::setInputError(self::$inputName."_prefix");

                return FALSE;
            }
            // if not required
        }
        return "";

    }
}
