<?php
defined('IN_FUSION') || exit;

if (!defined("STF_LOCALE")) {
    if (file_exists(INFUSIONS."staff_application/locale/".LANGUAGE.".php")) {
        define("STF_LOCALE", INFUSIONS."staff_application/locale/".LANGUAGE.".php");
    } else {
        define("STF_LOCALE", INFUSIONS."staff_application/locale/English.php");
    }
}

if (!defined("DB_STF_APPLICATIONS")) {
    define("DB_STF_APPLICATIONS", DB_PREFIX."staff_applications");
}
