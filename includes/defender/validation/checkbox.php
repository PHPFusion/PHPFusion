<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: includes/defender/validation/checkbox.php
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

use Defender\Validation;

/**
 * Class Checkbox
 * Validates Checkbox Input
 */
class Checkbox extends Validation {
    /**
     * Validate a checkbox
     * If field Value is multiple checkbox, post value must be an array
     * If field value is a radio, post value must not be an array
     * If field value is a number, post value must be a boolean 1 or 0
     */
    protected function verify_checked() {
        if (self::$inputConfig['required'] && !self::$inputValue) {
            fusion_stop();
            \Defender::getInstance()->setInputError( self::$inputName );
        }
        if (is_array( self::$inputValue )) {
            $vars = [];

            foreach (self::$inputValue as $key => $val) {
                if ($key === 0 && empty( $val )) {
                    $vars[] = self::$inputDefault;
                } elseif ($val) {
                    $vars[] = stripinput( $val );
                }
            }

            $delimiter = (!empty( self::$inputConfig['delimiter'] )) ? self::$inputConfig['delimiter'] : ",";

            return implode( $delimiter, $vars );

        } else if (self::$inputValue !== NULL) {
            if (isnum( self::$inputValue )) {
                if (self::$inputValue == 1) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return stripinput( self::$inputValue );
            }
        } else {
            return FALSE;
        }
    }
}
