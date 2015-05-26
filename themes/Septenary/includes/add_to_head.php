<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: add_to_head.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig
| Site: http://www.phpfusionmods.co.uk
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

if (FUSION_SELF !== "maintenance.php" && FUSION_SELF !== "go.php") {
	add_to_head("<meta name='viewport' content='width=device-width, initial-scale=1'>
			<!--[if lt IE 8]>
			<div style=' clear: both; text-align:center; position: relative;'>
			<a href='http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode'>
			<img src='http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg' border='0' height='42' width='820' alt='You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today.' />
			</a>
			</div>
			<![endif]-->
			<!--[if lt IE 9]>
			<script src='".THEME."js/html5.js'></script>
			<script src='".THEME."js/css3-mediaqueries.js'></script>
			<![endif]-->");
}

