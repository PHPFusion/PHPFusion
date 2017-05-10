<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: includes/defender/validation/date.php
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
 * Class Date
 * Validates Date Input
 */
class Date extends \Defender\Validation {

    /**
     * Check and verify submitted date
     * If type is timestamp, it will return a Unix timestamp
     * If type is date, it will return a date
     *
     * @return int|string
     */
    public function verify_date() {
        $locale = fusion_get_locale();
        if (self::$inputValue && !empty(self::$inputConfig['date_format'])) {
            $date = new \DateTime();
            $date_format = $date->createFromFormat(self::$inputConfig['date_format'], self::$inputValue);
            $timestamp = '';
            if ($date_format instanceof \DateTime) {
                $timestamp = $date_format->getTimestamp();
            }

            $dateParams = getdate($timestamp);
            if (checkdate($dateParams['mon'], $dateParams['mday'], $dateParams['year'])) {
                switch (self::$inputConfig['type']) {
                    case "timestamp":
                        return $timestamp;
                        break;
                    case "date":
                        $date = (string)$dateParams['year']."-".$dateParams['mon']."-".$dateParams['mday'];
                        return $date;
                        break;
                }
            } else {
                \defender::stop();
                \defender::setInputError(self::$inputName);
                addNotice('info', sprintf($locale['df_404'], self::$inputConfig['title']));
            }
        }

        return (string)self::$inputDefault;
    }

}