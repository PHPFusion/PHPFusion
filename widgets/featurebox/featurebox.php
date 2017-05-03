<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Featurebox/featurebox.php
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

class featureboxWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {

    public function display_widget($colData) {
        $widget_locale = fusion_get_locale('', WIDGETS."/featurebox/locale/".LANGUAGE.".php");
        $boxData = \defender::unserialize($colData['page_content']);

        /**
         * Array
         * (
         * [box_link] => viewpage.php?page_id=1
         * [box_link_class] => btn btn-sm btn-default
         * [box_link_margin_top] => 15
         * [box_link_margin_bottom] => 20
         * )
         */
        // Box Style
        $box_style = !empty($boxData['box_icon_margin_top']) ? "margin-top: ".$boxData['box_icon_margin_top']."px; " : "";
        $box_style .= !empty($boxData['box_icon_margin_bottom']) ? "margin-bottom:".$boxData['box_icon_margin_bottom']."px; " : "";

        $icon_size = (!empty($boxData['box_icon_size'])) ? $boxData['box_icon_size'] : 30;
        $stacked_size = (!empty($boxData['box_stacked_icon_size'])) ? $boxData['box_stacked_icon_size'] : 60;

        $icon_style = 'font-size: '.$icon_size.'px;';
        $icon_style .= ($boxData['box_icon_color'] ? "color: ".$boxData['box_icon_color'] : "");
        $stacked_icon_style = 'font-size: '.$stacked_size.'px;';
        $stacked_icon_style .= ($boxData['box_stacked_icon_color'] ? "color: ".$boxData['box_stacked_icon_color'] : "");

        $box_link = "";
        if (!empty($boxData['box_link'])) {
            $box_link_class = (!empty($boxData['box_link_class']) ? "class='".$boxData['box_link_class']."' " : "");
            $box_margin = '';
            $box_margin .= (!empty($boxData['box_link_margin_top']) ? "margin-top: ".$boxData['box_link_margin_top']."px;" : "");
            $box_margin .= (!empty($boxData['box_link_margin_bottom']) ? "margin-bottom: ".$boxData['box_link_margin_bottom']."px;" : "");
            $box_margin .= (!empty($box_margin)) ? "style='$box_margin'" : "";
            $box_link = "<a $box_link_class href='".$boxData['box_link']."' $box_margin>".$widget_locale['FBW_0300']."</a>";
        }

        $icon = '';
        switch ($boxData['box_icon_type']) {
            case '1': // Icon Image
                $icon = "<img src='".IMAGES."".$boxData['box_icon_src']."' alt='".$boxData['box_title']."'>";
                break;
            default:
                if (!empty($boxData['box_stacked_icon_class'])) {
                    $icon .= "<span class='icon-stack' style='height:".$stacked_size."px'>\n";
                }
                $icon .= "<i class='icon ".$boxData['box_icon_class']."' style='$icon_style'></i>";
                if (!empty($boxData['box_stacked_icon_class'])) {
                    $icon .= "<i class='icon ".$boxData['box_stacked_icon_class']."' style='$stacked_icon_style'></i>";
                    $icon .= "</span>";
                }
                break;
        }
        ob_start();
        ?>
        <div class="text-center <?php echo $boxData['box_class'] ?>" style="padding:<?php echo $boxData['box_padding']."px" ?>">
            <div class="display-block clearfix" <?php echo($box_style ? "style='".$box_style."'" : '') ?>>
                <!--Icon-->
                <?php echo $icon ?>
                <!--//Icon-->
            </div>
            <h3><?php echo $boxData['box_title'] ?></h3>
            <p><?php echo nl2br($boxData['box_description']) ?></p>
            <?php echo $box_link ?>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return (string)$html;
    }

}