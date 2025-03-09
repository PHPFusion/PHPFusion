<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: welcome_message_panel.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!function_exists('render_welcome_panel')) {
    function render_welcome_panel() {
        $locale = fusion_get_locale();

        opentable($locale['global_035']);
        echo html_entity_decode(stripslashes(nl2br(fusion_get_settings('siteintro'))), ENT_QUOTES);
        closetable();
    }
}

render_welcome_panel();
