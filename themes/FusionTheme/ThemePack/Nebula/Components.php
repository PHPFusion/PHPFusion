<?php
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