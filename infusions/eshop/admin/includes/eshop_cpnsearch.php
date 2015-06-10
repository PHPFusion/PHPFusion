<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_cpnsearch.php
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
	$q = isset($_POST['q']) && strlen($_POST['q'])>1 ? form_sanitizer($_POST['q'], '') : 0;
	$result = dbquery("SELECT * FROM ".DB_ESHOP_COUPONS." WHERE cuid='$q' ORDER BY cuid ASC LIMIT 50");

	if (dbrows($result)>0) {
		$i = 0;
		$coupon_value = array(
			'0' => $locale['no'],
			'1' => $locale['yes']
		);
		$coupon_type = array(
			'0'	=> $locale['ESHPCUPNS112'],
			'1' => $locale['ESHPCUPNS113']." (".fusion_get_settings('eshop_currency').")",
		);
		while ($data = dbarray($result)) {
			$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
			echo "<tr id='listItem_".$data['cuid']."' data-id='".$data['cuid']."' class='list-result ".$row_color."'>\n";
			echo "<td></td>\n";
			echo "<td>\n";
			echo "<strong>".$data['cuid']."</strong>\n";
			echo "<div class='actionbar text-smaller' id='coupon-".$data['cuid']."-actions'>
			<a href='".FUSION_ROOT."eshop.php".$aidlink."&amp;a_page=coupons&amp;section=couponform&amp;action=edit&amp;cuid=".$data['cuid']."'>".$locale['edit']."</a> |
			<a class='qedit pointer' data-id='".$data['cuid']."'>".$locale['qedit']."</a> |
			<a class='delete' href='".FUSION_ROOT."eshop.php".$aidlink."&amp;a_page=coupons&amp;action=delete&amp;cuid=".$data['cuid']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\">".$locale['delete']."</a>
			</div>\n";
			echo "</td>\n";
			echo "<td>".$data['cuname']."</td>";
			echo "<td>".$data['cuvalue']." ".$coupon_type[$data['cutype']]."</td>";
			echo "<td>".$coupon_status[$data['active']]."</td>";
			echo "<td>".showdate("forumdate", $data['custart'])."</td>";
			echo "<td>".showdate("forumdate", $data['cuend'])."</td>";
			echo "</tr>";
			$i++;
		}
		echo "<script>\n";
		echo "
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#coupon-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#coupon-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		$('.qedit').bind('click', function(e) {
			$.ajax({
				url: '".ADMIN."includes/eshop_coupon.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					$('#cuids').val(e.cuid);
					$('#cunames').val(e.cuname);
					$('#cuvalues').val(e.cuvalue);
					$('#actives').select2('val', e.active);
					$('#cutypes').select2('val', e.cutype);
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
		echo "<tr><td colspan='7' class='text-center'><div class='alert alert-warning m-t-10'>".$locale['ESHPCUPNS110']."</div></td></tr>\n";
	}
}
