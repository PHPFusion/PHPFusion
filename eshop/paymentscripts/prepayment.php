<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: prepayment.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

echo '<br /><table border="0" width="600" cellspacing="0" cellpadding="0" align="center"><tr>
<td bgcolor="#ADCBE7" align="left" height="12" width="3%"><font color="#FFFFFF" size="2"><b>
<img border="0" src="'.INFUSIONS.'eshop/img/paymenticon.png" width="25" height="12"></b></font></td>
<td bgcolor="#ADCBE7" align="left" height="12">
<font color="#FFFFFF" size="2"><b>'.$locale['ESHPPRP101'].'</b></font></td>
<td bgcolor="#ADCBE7" align="left" height="12"> </td></tr>
<tr><td height="22" bordercolor="#CEE4ED" bordercolorlight="#CEE4ED" bordercolordark="#CEE4ED" style="border-left-style: solid; border-left-width: 1px; border-right-style: none; border-right-width: medium; border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" width="18%" colspan="2">
<span><img border="0" src="'.INFUSIONS.'eshop/img/creditcard.jpg" width="194" height="156"><font size="3"><b><br /> </b></font></span><p align="center">
<img border="0" src="'.INFUSIONS.'eshop/img/yes.gif" width="63" height="56"><br />'.$locale['ESHPPRP102'].'<br />
<br /><br /><br /><br /> </td>
<td valign="top" height="22" bordercolor="#CEE4ED" bordercolorlight="#CEE4ED" bordercolordark="#CEE4ED" style="border-left-style: none; border-left-width: medium; border-right-style: solid; border-right-width: 1px; border-top-style: solid; border-top-width: 1px; border-bottom-style: solid; border-bottom-width: 1px" width="81%">span><br /><br />
<h2><font size="3"><b><br /> '.$locale['ESHPPRP103'].'</h2></b></font> <br /><br /><br /><br />
<p><font face="Verdana" size="2">'.$locale['ESHPPRP104'].'</font></p>
<p><font face="Verdana" size="2">'.$locale['ESHPPRP105'].''.$odata['oid'].' </font></p>
<p><font face="Verdana" size="2">'.$locale['ESHPPRP106'].'</font></p>
<p> </td></tr></table><p></FONT><br /></p>';

//clear the cart.
dbquery("DELETE FROM ".DB_ESHOP_CART." WHERE puid ='".$username."'");
?>