<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop.php
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../../maincore.php";
pageAccess('ESHP');
require_once THEMES."templates/admin_header.php";
include SHOP."locale/".LOCALESET."eshop.php";

require_once INCLUDES."infusions_include.php";
$eshop_settings = get_settings("eshop");

require_once SHOP."classes/Eshop.php";
require_once SHOP."classes/Admin/Banners.php";
require_once SHOP."classes/Admin/Customers.php";
require_once SHOP."classes/Admin/Orders.php";
require_once SHOP."classes/Admin/Main.php";
require_once SHOP."classes/Admin/ProductCategories.php";
require_once SHOP."classes/Admin/Products.php";
require_once SHOP."classes/Admin/Coupons.php";
require_once SHOP."classes/Admin/Payments.php";
require_once SHOP."classes/Admin/Shipping.php";

$eShop = new \PHPFusion\Eshop\Admin\Main();
$eShop->eshopAdmin();
require_once THEMES."templates/footer.php";
