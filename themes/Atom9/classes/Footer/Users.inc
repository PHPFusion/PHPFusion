<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom9/classes/Footer/Users.inc
| Author: Frederick MC Chan (Chan)
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Atom9Theme\Footer;

class Users {
    public static function Panel() {
        $locale = fusion_get_locale('', ATOM9_LOCALE);

        ob_start();

        echo '<h3>'.$locale['A9_012'].'</h3>';

        $result = dbquery("SELECT user_id, user_name, user_status, user_avatar
            FROM ".DB_USERS."
            WHERE user_status <= '1'
            ORDER BY user_joined DESC
            LIMIT 24
        ");

        if (dbrows($result) > 0) {
            echo '<div class="m-b-10 m-t-10">';
            while ($data = dbarray($result)) {
                echo display_avatar($data, '25px', '', TRUE, 'img-rounded m-3');
            }
            echo '</div>';

            echo '<a href="'.BASEDIR.'members.php" class="more-dark">'.$locale['A9_013'].'</a>';
        } else {
            echo $locale['A9_014'];
        }

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
