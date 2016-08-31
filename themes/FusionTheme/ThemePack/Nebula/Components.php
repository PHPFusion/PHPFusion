<?php

namespace ThemePack\Nebula;

class Components {

    public static function opentable($title = FALSE) {
        ?>
        <div class="openTable">
        <?php if ($title) : ?>
            <div class="title"><?php echo $title ?></div>
        <?php endif;
    }

    public static function closetable() {
        ?>
        </div>
        <?php
    }


    public static function openside($title = FALSE) {
        ?>
        <div class="openSide">
        <?php if ($title) : ?>
            <div class="title"><?php echo $title ?></div>
        <?php endif;
    }

    public static function closeside() {
        ?>
        </div>
        <?php
    }
}