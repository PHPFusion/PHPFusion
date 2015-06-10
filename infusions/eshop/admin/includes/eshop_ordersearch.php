<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_ordersearch.php
| Author: Joakim Falk (Domi)
| Co-Author: Frederick MC Chan (hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../../../maincore.php";
include SHOP."locale/".LOCALESET."eshop.php";

$aid = isset($_POST['token']) ? explode('=', $_POST['token']) : '';
if (!empty($aid)) {
	$aid = $aid[1];
}
if (checkrights("ESHP") && defined("iAUTH") && $aid == iAUTH) {
	$q = isset($_POST['q']) && strlen($_POST['q'])>0 ? form_sanitizer($_POST['q'], '') : 0;
	echo $q;
	$result = dbquery("SELECT * FROM ".DB_ESHOP_ORDERS." WHERE oid='".$q."' OR oname LIKE '".$q."%'
					  ORDER BY ouid DESC, oname ASC LIMIT 50");
	if (dbrows($result)>0) {
		while ($data = dbarray($result)) {
			$usercheck = dbquery("SELECT user_id FROM  ".DB_USERS." WHERE user_id = '".$data['ouid']."'");
			$urows = dbrows($usercheck);
			if ($urows != 0) {
				$customer = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=customers&amp;section=customerform&amp;action=edit&amp;cuid=".$data['ouid']."'>".$data['oname']."</a>";
			} else {
				$customer = $data['oname'];
			}
			
			<tr>
				<td><?php echo $data['oid'] ?></td>
				<td><?php echo $customer ?></td>
				<td><?php echo $data['oemail'] ?></td>
				<td><?php echo $data['payment_method'] ?></td>
				<td><?php echo $data['delivery_method'] ?></td>
				<td><?php echo number_format($data['ototal'],2)." ".fusion_get_settings('eshop_currency') ?></td>
				<td><?php echo $data['opaid'] ? $locale['ESHP309'] : $locale['ESHP320'] ?></td>
				<td><?php echo $data['ocompleted'] ? $locale['ESHP321b'] : $locale['ESHP321'] ?></td>
				<td>
					<div class='btn-group'>
						<a class='btn button btn-sm btn-default' href='<?php echo ADMIN."eshop.php".$aidlink."&amp;a_page=orders&amp;section=orders&amp;action=vieworder&amp;orderid=".$data['oid'] ?>'><?php echo $locale['ESHP322'] ?></a>
						<a class='btn button btn-sm btn-default' href='<?php echo ADMIN."eshop/printorder.php".$aidlink."&amp;orderid=".$data['oid'] // we can execute modal here ?>'><?php echo $locale['ESHP314'] ?></a>
						<a class='btn button btn-sm btn-danger' onClick='confirmdelete();' href='<?php echo FUSION_SELF.$aidlink."&amp;a_page=orders&amp;section=orders&amp;action=delete&amp;orderid=".$data['oid'] ?>'><?php echo $locale['delete'] ?></a>
					</div>
				</td>
			</tr>
		<?php
		}
	} else {
		
		<tr><td colspan='8' class='text-center'><?php echo $locale['ESHP325'] ?></td></tr>
		<?php
	}
}
