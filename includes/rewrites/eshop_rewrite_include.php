<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
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

$regex = array("%product%" => "([0-9]+)",
			   "%title%" => "([0-9a-zA-Z._\W]+)",
			   "%step%" => "([0-9]+)",
			   "%rowstart%" => "([0-9]+)",
			   "%c_start%" => "([0-9]+)",
			   "%category%" => "([0-9]+)");
				  
$pattern = array("shop" => "eshop.php",
 				 "shop/cart" => "eshop/cart.php",
				 "shop/checkout" => "eshop/checkout.php",
				 "shop/checkout-completed" => "eshop/checkedout.php",
				 "shop/%product%/%title%" => "eshop.php?product=%product%",
				 "shop/%product%/%title%#comments" => "eshop.php?product=%product%#comments",
				 "shop/%c_start%/%product%/%title%" => "eshop.php?product=%product%&amp;c_start=%c_start%");

$alias_pattern = array("shop/%alias%" => "%alias_target%",
					   "shop/%alias%#comments" => "%alias_target%#comments",
					   "shop/%alias%/%step%/%rowstart%" => "%alias_target%&amp;step=%step%&amp;rowstart=%rowstart%",
					   "shop/%alias%/%step%" => "%alias_target%&amp;step=%step%");

$dbname = DB_ESHOP;
$dbid = array("%product%" => "id");
$dbinfo = array("%title%" => "title");
?>