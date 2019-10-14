<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 2.0.upgrade.inc
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
$inf_version = '2.0';

$check_faq = [
    'faq_order' => " ADD faq_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER faq_status",
];

foreach ($check_faq as $key => $value) {
    if (!column_exists(DB_FAQS, $key, FALSE)) {
        $inf_altertable[] = DB_FAQS.$value;
    }
}
