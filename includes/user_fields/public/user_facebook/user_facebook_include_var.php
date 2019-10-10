<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_facebook_include_var.php
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
defined('IN_FUSION') || exit;
$locale = fusion_get_locale('', __DIR__.'/locale/'.LANGUAGE.'.php');
// Version of the user fields api
$user_field_api_version = "1.01.00";
$user_field_name = $locale['uf_facebook'];
$user_field_desc = $locale['uf_facebook_desc'];
$user_field_dbname = "user_facebook";
$user_field_group = 3;
$user_field_dbinfo = "VARCHAR(50) NOT NULL DEFAULT ''";
$user_field_author = 'PHP-Fusion Development Team';
$user_field_image = INCLUDES.'user_fields/public/user_facebook/images/facebook.svg';
