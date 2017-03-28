<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /Nebula/Components.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace ThemePack\Nebula;

class Components {

    public static function opentable($title = FALSE) {
        echo "<div class='openTable'>\n";
        if ($title) :
            echo "<div class='title'>".$title."</div>\n";
        endif;
    }

    public static function closetable() {
        echo "</div>\n";
    }

    public static function openside($title = FALSE) {
        echo "<div class='openSide'>\n";
        if ($title) :
            echo "<div class='title'>".$title."</div>\n";
        endif;
    }

    public static function closeside() {
        echo "</div>\n";
    }
}