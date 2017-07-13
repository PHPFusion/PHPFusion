<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: home.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/maincore.php';
require_once THEMES."templates/header.php";

$locale = fusion_get_locale('', LOCALE.LOCALESET.'homepage.php');
add_to_title($locale['home']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['title' => $locale['home'], 'link' => BASEDIR.'home.php']);

require_once INFUSIONS.'home_panel/home_panel.php';

require_once THEMES."templates/footer.php";
