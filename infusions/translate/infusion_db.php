<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/infusion_db.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Locales
if (!defined("TRANSLATE_LOCALE")) {
    if (file_exists(INFUSIONS."translate/locale/".LOCALESET."translate.php")) {
        define("TRANSLATE_LOCALE", INFUSIONS."translate/locale/".LOCALESET."translate.php");
    } else {
        define("TRANSLATE_LOCALE", INFUSIONS."translate/locale/English/translate.php");
    }
}

// Paths
if (!defined("TRANSLATE_CLASS")) {
    define("TRANSLATE_CLASS", INFUSIONS."translate/classes/");
}

if (!defined("DB_TRANSLATE")) define("DB_TRANSLATE", DB_PREFIX."translations");
if (!defined("DB_TRANSLATE_FILES")) define("DB_TRANSLATE_FILES", DB_PREFIX."translation_files");
if (!defined('DB_TRANSLATE_PACKAGE')) define('DB_TRANSLATE_PACKAGE', DB_PREFIX.'translation_package');

\PHPFusion\Admins::getInstance()->setSubmitType('t', fusion_get_locale('translate_0002', TRANSLATE_LOCALE));
\PHPFusion\Admins::getInstance()->setSubmitLink('t', INFUSIONS."translate/translate_admin.php".fusion_get_aidlink()."&amp;section=submissions&amp;submit_id=%s");
