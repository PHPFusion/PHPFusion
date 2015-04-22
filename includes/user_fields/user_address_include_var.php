<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_address_include_var.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

// Version of the user fields api
$user_field_api_version = "1.02.00";
$user_field_name = $locale['uf_address'];
$user_field_desc = $locale['uf_address_desc'];
$user_field_dbname = "user_address";
$user_field_group = 1;
$user_field_dbinfo = "VARCHAR(200) NOT NULL DEFAULT ''";
?>