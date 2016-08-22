<?php
namespace Nebula\Layouts;

class Compo {


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


}