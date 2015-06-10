<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_customersearch.php
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
// ADMIN does not append subfolder!
$str = pathinfo($_SERVER['PHP_SELF']);
$exp = array_filter(explode('/', $str['dirname']));


if (checkrights("ESHP") && defined("iAUTH") && $aid == iAUTH) {
	$q = isset($_POST['q']) && strlen($_POST['q'])>1 ? form_sanitizer($_POST['q'], '') : 0;
	$result = dbquery("SELECT c.*, u.user_id, u.user_name, u.user_status
				FROM ".DB_ESHOP_CUSTOMERS." c
				INNER JOIN ".DB_USERS." u on c.cuid = u.user_id
				WHERE c.cuid='$q' or c.cfirstname LIKE '%".$q."%' or c.clastname LIKE '%".$q."%' or u.user_name LIKE '%".$q."%'
				 ORDER BY cfirstname ASC LIMIT 50");
	if (dbrows($result)>0) {
		$i = 0;
		while ($data = dbarray($result)) {
			$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
			echo "<tr id='listItem_".$data['cuid']."' data-id='".$data['cuid']."' class='list-result ".$row_color."'>\n";
			echo "<td></td>\n";
			echo "<td>\n";
			echo "<strong>".$data['cfirstname']." ".$data['clastname']."</strong>\n";
			echo "<div class='actionbar text-smaller' id='customer-".$data['cuid']."-actions'>
				<a href='".FUSION_ROOT.$aidlink."&amp;a_page=customers&amp;section=customerform&amp;action=edit&amp;cuid=".$data['cuid']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['cuid']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_ROOT.$aidlink."&amp;a_page=customers&amp;action=delete&amp;cuid=".$data['cuid']."' onclick=\"return confirm('".$locale['ESHP213']."');\">".$locale['delete']."</a>
				</div>\n";
			echo "</td>\n";
			echo "<td>".$data['cemail']."</td>";
			echo "<td><a title='Send Private Message' id='send_pm'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</a></td>";
			echo "<td>".$data['cphone']."</td>";
			echo "<td>".$data['cfax']."</td>";
			echo "</tr>";
			$i++;
		}

		echo "<script>\n";
		echo "
		$('.actionbar').hide();
			$('tr').hover(
				function(e) { $('#customer-'+ $(this).data('id') +'-actions').show(); },
				function(e) { $('#customer-'+ $(this).data('id') +'-actions').hide(); }
			);
			$('.qform').hide();
			$('.qedit').bind('click', function(e) {
				$.ajax({
					url: '".SHOP."admin/includes/eshop_customers.php',
					dataType: 'json',
					type: 'post',
					data: { q: $(this).data('id'), token: '".$aidlink."' },
					success: function(e) {
						$('#cuids').val(e.cuid);
						$('#cphone').val(e.cphone);
						$('#cfax').val(e.cfax);
						$('#qaddress-street').val(e.caddress);
						$('#qaddress-street2').val(e.caddress2);
						$('#qaddress-country').select2('val', e.ccountry);
						$('#qaddress-state').select2('val', e.cregion);
						$('#qaddress-city').val(e.ccity);
						$('#qaddress-postcode').val(e.cpostcode);
					},
					error : function(e) {
						console.log(e);
					}
				});
				$('.qform').show();
				$('.list-result').hide();
			});
			$('#cancel').bind('click', function(e) {
				$('.qform').hide();
				$('.list-result').show();
			});
		</script>\n";
	} else {
		echo "<tr><td colspan='7' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPCHK154']."</div></td></tr>\n";
	}
}
