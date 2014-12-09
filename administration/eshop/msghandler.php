<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: msghandler.php
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
require_once "../../maincore.php";
if (!iADMIN && checkrights("ESHP")){ die("Denied"); }
if (isset($_GET['id']) && !isnum($_GET['id'])) die("Denied");
if (isset($_GET['id'])) {
redirect($settings['siteurl']."administration/eshop.php".$aidlink."&amp;a_page=orders&amp;vieworder&amp;orderid=".$_GET['id']);
} else {
redirect($settings['siteurl']."administration/eshop.php".$aidlink."&amp;a_page=orders");
}
?>