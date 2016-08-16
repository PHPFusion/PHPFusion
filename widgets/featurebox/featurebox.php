<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
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

    }


}
