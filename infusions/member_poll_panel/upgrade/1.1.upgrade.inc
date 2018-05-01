<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 1.1.upgrade.inc
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
$inf_version = '1.1';

if (column_exists(DB_POLLS, 'poll_opt_0') && column_exists(DB_POLLS, 'poll_opt')) {
    $result = dbquery("SELECT * FROM ".DB_POLLS);
    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $val = [];
            for ($i = 0; $i < 10; $i++) {
                $val[$i] = $data['poll_opt_'.$i];
            }
            $poll_option = array_filter($val);

            dbquery("UPDATE ".DB_POLLS." SET poll_opt='".serialize($poll_option)."' WHERE poll_id='".$data['poll_id']."'");
        }
    }
}

if (column_exists(DB_POLLS, 'poll_opt_0')) {
    $inf_altertable[] = DB_POLLS." DROP poll_opt_0, DROP poll_opt_1, DROP poll_opt_2, DROP poll_opt_3, DROP poll_opt_4, DROP poll_opt_5, DROP poll_opt_6, DROP poll_opt_7, DROP poll_opt_8, DROP poll_opt_9";
}
