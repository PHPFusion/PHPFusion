<?php

class Date extends \Defender\Validation {

    /**
     * Check and verify submitted date
     * If type is timestamp, it will return a Unix timestamp
     * If type is date, it will return a date
     * @return int|string
     */
    public function verify_date() {
        $locale = fusion_get_locale();
        if (self::$inputValue) {

            $dateParams = strtotime(self::$inputValue);
            $dateParams = getdate($dateParams);

            if (checkdate($dateParams['mon'], $dateParams['mday'], $dateParams['year'])) {

                switch (self::$inputConfig['type']) {
                    case "timestamp":

                        $secured = (int)mktime($dateParams['hours'],
                            $dateParams['minutes'],
                            $dateParams['seconds'],
                            $dateParams['mon'],
                            $dateParams['mday'],
                            $dateParams['year']
                        );

                        return $secured;

                        break;
                    case "date":

                        return (string)$dateParams['year']."-".$dateParams['mon']."-".$dateParams['mday'];
                        break;
                }

            } else {
                \defender::stop();
                \defender::getInstance()->setInputError(self::$inputName);
                addNotice('info', sprintf($locale['df_404'], self::$inputConfig['title']));
            }
        }

        return (string)$this->field_default;
    }
    
}