<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: panel.php
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

    public function displayInfo($colData) {
        $panelData = \Defender::unserialize($colData['page_content']);

        return fusion_get_locale('PW_0222', WIDGETS."panel/locale/".LANGUAGE.".php").": ".$panelData['panel_include'];
    }

    public function displayWidget($columnData) {
        $panelData = \Defender::unserialize($columnData['page_content']);
        $panelPath = INFUSIONS.$panelData['panel_include']."/".$panelData['panel_include'].".php";
        if (!empty($panelData['panel_include']) && file_exists($panelPath)) {
            ob_start();
            require_once $panelPath;
            return ob_get_clean();
        }

        return NULL;
    }

}
