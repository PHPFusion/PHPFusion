<?php
require_once "../../maincore.php";
include LOCALE.LOCALESET."eshop.php";
$aid = isset($_POST['token']) ? explode('=', $_POST['token']) : '';
if (!empty($aid)) {
	$aid = $aid[1];
}
if (checkrights("ESHP") && defined("iAUTH") && $aid == iAUTH) {
	$search_text = isset($_POST['q']) && strlen($_POST['q'])>1 ? form_sanitizer($_POST['q'], '') : 0;
	$search_text=ltrim($search_text);
	$search_text=rtrim($search_text);
	$q = "";
	$kt = "";
	$val = "";
	$kt = explode(" ",$search_text);
	while(list($key,$val)=each($kt)){
		if($val<>" " and strlen($val) > 0){ $q.= " i.title like '%$val%' or i.artno like '%$val%' or i.sartno like '%$val%' or i.id like '%$val%' or cat.title like '%$val%' or";}
	}
	$q=substr($q,0,(strlen($q)-3));
	$result = dbquery("SELECT i.id, i.title, i.artno, i.sartno, i.status, i.access, i.iorder, i.product_languages, cat.title as cat_title
						FROM ".DB_ESHOP." i LEFT JOIN ".DB_ESHOP_CATS." cat on (i.cid=cat.cid)
	 					WHERE ".$q ." ORDER BY title ASC LIMIT 50");
	if (dbrows($result)>0) {

		$visibility_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$visibility_opts[$user_group[0]] = $user_group[1];
		}
		$availability = array(
			'0' => $locale['ESHPPRO145a'],
			'1' => $locale['ESHPPRO145b'],
		);

		while ($data = dbarray($result)) {
			$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
			echo "<tr id='listItem_".$data['id']."' data-id='".$data['id']."' class='list-result ".$row_color."'>\n";
			echo "<td></td>\n";
			echo "<td class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
			echo "<a class='text-dark' title='".$locale['edit']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$data['id']."'>".$data['title']."</a>";
			echo "<div class='actionbar text-smaller' id='product-".$data['id']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;section=itemform&amp;action=edit&amp;id=".$data['id']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['id']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=delete&amp;id=".$data['id']."' onclick=\"return confirm('".$locale['ESHPCATS134']."');\">".$locale['delete']."</a>
				";
			echo "</td>\n";
			echo "<td>".$data['cat_title']."</td>\n";
			echo "<td>".fusion_get_settings('eshop_currency')." ".number_format($data['price'], 2, '.', ',')."</td>\n";
			echo "<td>".$data['artno']."</td>\n";
			echo "<td>".$data['sartno']."</td>\n";
			echo "<td>\n";
			echo "<td>\n";
			echo ($i == 0) ? "" : "<a title='".$locale['ESHPCATS137']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=moveup&amp;cat=".$data['cid']."&amp;id=".$data['id']."'><i class='entypo up-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
			echo ($i == $rows-1) ? "" : "<a title='".$locale['ESHPCATS138']."' href='".FUSION_SELF.$aidlink."&amp;a_page=main&amp;action=movedown&amp;cat=".$data['cid']."&amp;id=".$data['id']."'><i class='entypo down-bold m-l-0 m-r-0' style='font-size:18px; padding:0; line-height:14px;'></i></a>";
			echo "</td>\n"; // move up and down.
			echo "<td>".$availability[$data['status']]."</td>\n";
			echo "<td>".$visibility_opts[$data['access']]."</td>\n";
			echo "<td>".$data['iorder']."</td>\n";
			echo "<td>".str_replace('.', ', ', $data['product_languages'])."</td>\n";
			echo "</tr>\n";
			$i++;
		}
		echo "<script>\n";
		echo "
		$('.actionbar').hide();
			$('tr').hover(
			function(e) { $('#product-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#product-'+ $(this).data('id') +'-actions').hide(); }
		);
		$('.qform').hide();
		$('.qedit').bind('click', function(e) {
			// ok now we need jquery, need some security at least.token for example. lets serialize.
			$.ajax({
				url: '".ADMIN."includes/eshop_products.php',
				dataType: 'json',
				type: 'post',
				data: { q: $(this).data('id'), token: '".$aidlink."' },
				success: function(e) {
					$('#cids').val(e.id);
					$('#titles').val(e.title);
					$('#artnos').val(e.artno);
					$('#sartnos').val(e.sartno);
					$('#prices').val(e.price);
					$('#xprices').val(e.xprice);
					$('#instocks').val(e.instock);
					$('#actives').select2('val', e.active);
					$('#statuss').select2('val', e.status);
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
		";
		echo "</script>\n";
	} else {
		echo "<tr>\n<td class='text-center' colspan='12'>".$locale['ESHPPRO177']."</td>\n</tr>\n";
	}
}
?>