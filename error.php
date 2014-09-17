<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: errors.php
| Author: Joakim Falk (Domi)
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."error.php";
if (isset($_GET['code']) && $_GET['code'] == "401") {
	header("HTTP/1.1 401 Unauthorized");
	$text = $locale['err401'];
	$img = "401.png";
} elseif (isset($_GET['code']) && $_GET['code'] == "403") {
	header("HTTP/1.1 403 Forbidden");
	$text = $locale['err403'];
	$img = "403.png";
} elseif (isset($_GET['code']) && $_GET['code'] == "404") {
	header("HTTP/1.1 404 Not Found");
	$text = $locale['err404'];
	$img = "404.png";
} elseif (isset($_GET['code']) && $_GET['code'] == "500") {
	header("HTTP/1.1 500 Internal Server Error");
	$text = $locale['err500'];
	$img = "500.png";
} else {
	$text = $locale['errunk'];
	$img = "unknown.png";
}
opentable($text);
echo "<center>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='30%' align='center'><img src='".IMAGES."error/".$img."' alt='".$text."' border='0'></td>";
echo "<td style='font-size:16px;color:red' align='center'>".$text."</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='2' align='center'><b><a class='button' href='".BASEDIR."index.php'>".$locale['errret']."</a></b></td>";
echo "</tr>";
echo "</table>";
echo "</center>";
closetable();
require_once THEMES."templates/footer.php";
?>