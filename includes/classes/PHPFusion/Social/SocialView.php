<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SocialView.php
| Author: Core Development Team
+--------------------------------------------------------*/

namespace PHPFusion\Social;

class SocialView {

    public static function render(array $data): void {
        require_once THEMES.'templates/global/social.tpl.php';
        render_social($data);
    }
}
