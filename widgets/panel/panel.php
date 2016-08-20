<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Panel/panel.php
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

class panelWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {

    public function display_widget($colData) {
        $panelData = \defender::unserialize($colData['page_content']);
        if (!empty($panelData['panel_include'])) {
            return $this->display_Panel($panelData['panel_include']);
        }
    }

    private function display_Panel($panel_id) {
        $html = "";
        $panels = \PHPFusion\Panels::cachePanels();
        if (!empty($panels)) {
            $panels = flatten_array($panels);
            foreach ($panels as $panelData) {
                if ($panelData['panel_id'] == $panel_id) {
                    ob_start();
                    if ($panelData['panel_type'] == "file") {
                        if (file_exists(INFUSIONS.$panelData['panel_filename']."/".$panelData['panel_filename'].".php")) {
                            include INFUSIONS.$panelData['panel_filename']."/".$panelData['panel_filename'].".php";
                        }
                    } else {
                        if (fusion_get_settings("allow_php_exe")) {
                            eval(stripslashes($panelData['panel_content']));
                        } else {
                            echo parse_textarea($panelData['panel_content']);
                        }
                    }
                    $html = ob_get_contents();
                    ob_end_clean();

                    return $html;
                }
            }
        }

        return $html;
    }
}