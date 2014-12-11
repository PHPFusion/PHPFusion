<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: printorder.php
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
if (!checkrights("ESHP") || !defined("iAUTH") || $_GET['aid'] != iAUTH) { die("Denied"); }
if (isset($_GET['orderid']) && !isnum($_GET['orderid'])) die("Denied");
require_once THEMES."templates/admin_header.php";

echo "<link rel='stylesheet' href='".THEMES."templates/global/eshop.css' type='text/css' />";

echo "<style>
/* Fieldset & Legend styles */	

fieldset { 
border: 1px solid #d9deeb !important; 
width:99% !important;
margin: 0 auto !important;
padding:0px !important;
margin-top:5px !important;
margin-bottom: 10px !important;

/* -- CSS3 - define rounded corners -- */	
-webkit-border-radius: 10px; 
-moz-border-radius: 10px;  
border-radius: 10px; 
}


legend {
  display: block !important;
  padding: 0 !important;
  line-height: 16px !important;
  color: #333 !important;
  border: 1px solid #d9deeb !important;
  background-color:#F7F7F7 !important;
  margin-left:20px !important;
  font-size:12px !important;
  width:95% !important;
  margin-bottom: 5px !important;
  -webkit-border-bottom-right-radius: 5px; 
  -webkit-border-bottom-left-radius: 5px;
  -moz-border-radius-bottomright: 5px;
  -moz-border-radius-bottomleft: 5px;
  border-bottom-right-radius: 5px;
  border-bottom-left-radius: 5px;
  -webkit-border-top-left-radius: 5px;
  -webkit-border-top-right-radius: 5px;
  -moz-border-radius-topleft: 5px;
  -moz-border-radius-topright: 5px;
  border-top-left-radius: 5px;
  border-top-right-radius: 5px;
}
</style>";


include LOCALE.LOCALESET."eshop.php";

$odata = dbarray(dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE oid='".$_GET['orderid']."' LIMIT 0,1"));
if ($odata) {
	opentable("".$locale['ESHP306']." ".$odata['oid']." ".$locale['ESHP307']." ".$odata['oname']."");
	echo $odata['oorder'];
	closetable();
	} else {
	echo "<div class='admin-message' align='center' style='margin-top:5px;'>".$locale['ESHP315']."</div>\n";
}

echo '<body onload="window.print(); parent.$.fn.colorbox.close();">';
?>