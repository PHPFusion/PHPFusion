<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: file.php
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

class fileWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {
    public function displayInfo($colData) {
        $data = \Defender::unserialize($colData['page_content']);
        return $data['file_url'];
    }

    public function displayWidget($columnData) {
        $fileData = \Defender::unserialize($columnData['page_content']);

        $url = str_replace(fusion_get_settings('siteurl'), '', $fileData['file_url']);
        ob_start();
        if (file_exists($url)) {
            include $url;
        } else {
            echo "<div class='alert alert-warning'>".fusion_get_locale('f0105', WIDGETS.'file/locale/'.LANGUAGE.'.php')."</div>\n";
        }

        return ob_get_clean();
    }

}
