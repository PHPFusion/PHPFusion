<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Block/block.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

class blockWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {

    public function display_widget($colData) {
        $blockData = \defender::unserialize($colData['page_content']);
        $block_margin = !empty($blockData['block_margin']) ? "margin:".$blockData['block_margin'].";" : "";
        $block_padding = !empty($blockData['block_padding']) ? "padding:".$blockData['block_padding'].";" : "";
        $block_style = ((!empty($block_margin) || !empty($block_padding)) ? " style=\"$block_margin $block_padding\"" : "");
        $block_class = ((!empty($blockData['block_class']) || !empty($blockData['block_align'])) ? " class=\"".$blockData['block_class']." ".$blockData['block_align']."\"" : "");
        ob_start();
        ?>
        <div<?php echo $block_class.$block_style ?>>
            <h3><?php echo $blockData['block_title'] ?></h3>

            <p><?php echo nl2br(parse_textarea($blockData['block_description'])) ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

}
